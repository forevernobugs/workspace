<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 09/05/2017
 * Time: 19:53
 */

namespace App\Models\Process;

use App\Common\Result;
use App\Events\BusinessProcessEvent;
use App\Exceptions\KnownLogicException;
use App\Models\ProcessRequest;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class ProcessHelper
{
    private $flowPara = [];
    private $currentUserId;
    private $userInfo = [];
    private $businessInfo = [];
    private $businessName;
    const MESSAGE_TYPE = 'USER_HAS_NEW_REQUEST';
    /**
     * ProcessHelper constructor.
     * @param $userId   操作用户ID
     */
    public function __construct($userId)
    {
        $this->currentUserId = $userId;
    }

    public function getBusinessName()
    {
        return $this->businessName;
    }

    private function getBusiness()
    {
        if ($this->businessInfo == []) {
            $this->businessInfo = DB::table('ruigu_business_process')->where('process_name', $this->getBusinessName())->first();
        }
        return $this->businessInfo;
    }

    /**
     * 获取当前用户信息
     * @return array 用户信息
     */
    private function getUserInfo()
    {
        if ($this->userInfo == []) {
            $this->userInfo = User::find($this->currentUserId);
        }

        return ['login_name'=>$this->userInfo->login_name, 'username'=>$this->userInfo->username];
    }

    /**
     * 开启一个新流程
     * @param $businessName 当前流程名
     * @param array $flowPara 开启参数
     * @param string $reason 开启流程原因
     * @param \Closure|null $afterStarted 流程成功开启之后的执行操作
     * @return Result 流程发起结果
     */
    public function startNewProcess($businessName, array $flowPara, $reason = '', \Closure $afterStarted = null)
    {
        $this->flowPara = $flowPara;
        $this->businessName = $businessName;

        $business = DB::table('ruigu_business_process')->where('process_name', $this->getBusinessName())->first();
        if (empty($business)) {
            return Result::failure('流程不存在');
        }
        if ($business->enabled != 1) {
            return Result::failure('流程还未配置完成');
        }

        $isSameOnProcess = $this->isSameOnProcess();

        if ($isSameOnProcess > 0) {
            return Result::failure('已经有相同审批正在审核中,请等待审核，或者取消前一次申批，然后重新申请');
        }

        $flows = $this->getFlows();

        $flowStartWith = $this->initFlowIndex($this->currentUserId);
        //判断流程是否需要进入流程，最后一个判断处理多级审核时，最后一级审核人直接发起流程，此时也应该直接通过流程
        if (count($flows) == 0 || $flowStartWith < 0 || count($flows) == $flowStartWith) {
            $result = $this->handRequest($this->flowPara, 'PASS', $reason);
            return $result;
        } else {
            DB::beginTransaction();
            try {
                $newRequest = new ProcessRequest();
                $newRequest->process_name = $this->businessName;
                $newRequest->message = $this->getRequestMessage();
                $newRequest->title = $this->getBusiness()->description;
                $newRequest->process_result = 1;
                $newRequest->createdDate = date("Y-m-d H:i:s");
                $newRequest->createdUser = $this->getUserInfo()['login_name'];
                $newRequest->passed_flow = $flowStartWith;
                $newRequest->reason = $reason;
                $newRequest->detail_page = $this->getBusiness()->detail_page; //增加查看详情界面
                $newRequest->pdata = json_encode($this->flowPara);

                $newRequest->save();
                if ($afterStarted != null) {
                    $afterStarted($flowPara);
                }

                $this->saveBusinessTrace($newRequest->id, '发起了审批，'.$reason);
                //$type, $related_no, $user_ids, $admin_groups
                //TODO 尝试发送消息给下一个审批人员

                //$nextFlow = $this->getFlowInfoByIndex($flowStartWith + 1);
                //$this->distributeMessage($nextFlow, $newRequest->getKey(),'您收到一条'.$this->getUserInfo()['username'].'发起的新审批');

                DB::commit();
                return Result::succeed('申请成功');
            } catch (\Exception $ex) {
                DB::rollback();
                return Result::failure('申请失败'. $ex->getMessage());
            }
        }
    }

    private function distributeMessage($nextFlowInfo, $requestId, $message)
    {
        if (empty($nextFlowInfo)) {
            return;
        }

        $auditor = explode(',', $nextFlowInfo->auditor);
        if (count($auditor) <= 0) {
            return;
        }

        $receiver = [];
        $processUserInfo = ProcessRequest::getFlowAuthorInfo($nextFlowInfo);

        foreach ($processUserInfo as $uu) {
            if (!empty($uu->email)) {
                $receiver[] = $uu->email;
            }
        }
        hSentMailFast($message, $receiver, 'ERP流程审核通知');
    }

    /**
     * @param int $userId 发起用户ID
     * @param $businessName string 申请名称
     * @param array $flowPara 审批参数
     * @param string $reason 发起审批原因
     * @param \Closure|null $afterStarted 流程成功开启之后的执行操作
     * @return Result 流程开始帮助方法
     */
    public static function startNewProcessStatic($userId, $businessName, array $flowPara, $reason = '', \Closure $afterStarted = null)
    {
        $business = new ProcessHelper($userId);

        return $business->startNewProcess($businessName, $flowPara, $reason, $afterStarted);
    }

    /**
     * 通过一条请求
     * @param int $requestId 当前请求
     * @param string $reason 通过备注
     * @return Result 处理结果
     */
    public function confirmRequest($requestId, $reason = '')
    {
        $request = $this->getRequest($requestId);
        if ($request == null) {
            return Result::failure('未找到申请！');
        }
        $this->businessName = $request->process_name;
        $this->flowPara = json_decode($request->pdata, true);

        //判断流程是否启用  判断请求是否找到  判断当前请求状态
        if (!in_array($request->process_result, [1,2])) {
            return Result::failure('流程已经审批或者已经作废');
        }

        //检查审核权限
        $canHandle = $this->canHandleCurrentFlow($request);

        if ($canHandle < 0) {
            return Result::failure('您没有权限处理此审批');
        }

        DB::beginTransaction();
        try {
            //当流程进入下一个审批时禁用当前分发给用户的消息
            MessageModel::_disable(self::MESSAGE_TYPE, $request->id);
            //最终通过审核
            if ($request->passed_flow == count($this->getFlows()) -1) {
                $result = $this->requestPassed($request, $reason);
                if ($result->succeed > 0) {
                    $this->saveBusinessTrace($request->id, '同意并最终通过了审核,备注：'.$reason);
                    DB::commit();
                    return Result::succeed('审核成功，审批已通过');
                } else {
                    DB::rollback();
                    return Result::failure('审批失败'. $result->msg);
                }
            } else {
                $this->moveNextProcess($request);
                $this->saveBusinessTrace($request->id, '同意了审核,备注：'.$reason);
                DB::commit();
                return Result::succeed('审核成功');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(hFormatException($e));
            return Result::failure('出现异常:'.$e->getMessage());
        }
    }

    /**
     * 拒绝一条请求
     * @param $requestId
     * @param string $reason 拒绝备注
     * @return Result
     * @internal param ProcessRequest $request = $this->getRequest($requestId); 当前请求
     */
    public function rejectRequest($requestId, $reason = '')
    {
        $request = $this->getRequest($requestId);
        if ($request == null) {
            return Result::failure('未找到申请！');
        }
        $this->businessName = $request->process_name;
        $this->flowPara = json_decode($request->pdata, true);

        if (empty($reason)) {
            return Result::failure('拒绝申请请填写理由');
        }

        //判断流程是否启用  判断请求是否找到  判断当前请求状态
        if (!in_array($request->process_result, [1,2])) {
            return Result::failure('流程已经审批或者已经作废');
        }

        //检查审核权限
        $canHandle = $this->canHandleCurrentFlow($request);
        if ($canHandle < 0) {
            return Result::failure('您没有权限处理此审批');
        }

        DB::beginTransaction();
        try {
            //当流程拒接时禁用当前分发给用户的消息
            MessageModel::_disable(self::MESSAGE_TYPE, $request->id);
            $this->requestRejected($request, $reason);
            $this->saveBusinessTrace($request->id, '拒绝了审批,备注：'.$reason);
            DB::commit();
            return Result::succeed('处理成功');
        } catch (\Exception $e) {
            DB::rollback();
            return Result::failure('处理失败，出现异常:'.$e->getMessage());
        }
    }

    /**
     * 由发起人取消自己发起的请求
     * @param $requestId 当前处理的请求ID
     * @param string $reason 取消备注
     * @return Result
     * @internal param ProcessRequest $request 当前请求
     */
    public function cancelRequest($requestId, $reason = '')
    {
        $request = $this->getRequest($requestId);
        if ($request == null) {
            return Result::failure('未找到申请！');
        }

        $this->businessName = $request->process_name;
        $this->flowPara = json_decode($request->pdata, true);

        if ($request->createdUser != $this->getUserInfo()['login_name']) {
            return Result::failure('只能撤销自己发起的审批');
        }

        if (!in_array($request->process_result, [1,2])) {
            return Result::failure('流程已经结束，无法撤销');
        }

        $selfCancel = DB::table('ruigu_business_process')->where('process_name', $this->businessName)->value('self_cancel');

        //已经完结的申请不会到达此处
        if ($selfCancel == 1) {
            //任意取消的模式，流程未结束可以直接取消
            return $this->doCancelRequest($request, $reason);
        } elseif ($selfCancel == 2) {
            //没有人审核的情况下可以直接取消
            if ($request->process_result == 1) {
                //没人审核
                return $this->doCancelRequest($request, $reason);
            } else {
                //已经有人审核了
                if ($request->canceling == 0) {
                    $this->markRequestCanceling($requestId);
                    return Result::failure('流程无法直接撤销，已申请撤销，请等待审核');
                } elseif ($request->canceling == 1) {
                    return Result::failure('流程无法直接撤销，已申请撤销，请等待审核');
                } elseif ($request->canceling == 2) {
                    return $this->doCancelRequest($request, $reason);
                } else {
                    return Result::failure('请求配置不正确');
                }
            }
        } elseif ($selfCancel == 3) {
            //提交之后无法直接撤销
            if ($request->canceling == 0) {
                $this->markRequestCanceling($requestId);
                return Result::failure('流程无法直接撤销，已申请撤销，请等待审核');
            } elseif ($request->canceling == 1) {
                return Result::failure('流程无法直接撤销，已申请撤销，请等待审核');
            } elseif ($request->canceling == 2) {
                return $this->doCancelRequest($request, $reason);
            } else {
                return Result::failure('请求配置不正确');
            }
        } else {
            return Result::failure('流程配置不正确,无法识别的取消模式');
        }
    }

    private function markRequestCanceling($requestId)
    {
        DB::beginTransaction();
        try {
            $request = ProcessRequest::find($requestId);

            $flow = DB::table('ruigu_business_process_flow')
                ->where('process_name', $request->process_name)
                ->where('flow_index', $request->passed_flow + 1)
                ->first();

            DB::table('ruigu_business_process_request')->where('id', $requestId)->update(['canceling'=>1]);
            $this->saveBusinessTrace($requestId, '申请撤销');
            $this->distributeMessage($flow, $request->id, '申请人申请撤销主题为：['.$request->message.']的申请');
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * @param object $request 撤销申请
     * @param string $reason 撤销原因
     * @return Result 撤销结果
     */
    private function doCancelRequest($request, $reason)
    {
        DB::beginTransaction();
        try {
            $result = $this->handRequest($this->flowPara, 'CANCEL', $reason);

            if ($result->succeed > 0) {
                $request->process_result = -3;
                $request->modifiedDate = date("Y-m-d H:i:s");
                $request->modifiedUser = $this->getUserInfo()['login_name'];
                $request->save();
                $this->saveBusinessTrace($request->id, '取消了申请,备注：'.$reason);
                //当流程取消时禁用当前分发给用户的消息
                MessageModel::_disable(self::MESSAGE_TYPE, $request->id);
                DB::commit();

                return Result::succeed('取消成功');
            } else {
                DB::rollback();
                return Result::failure('取消失败：'. $result->msg);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Result::failure('取消时出现异常：'. $e->getMessage());
        }
    }

    /**
     * 同意撤销请求
     * @param $requestId
     * @return Result
     */
    public function processCancelRequest($requestId)
    {
        $request = $this->getRequest($requestId);
        if ($request == null) {
            return Result::failure('未找到申请！');
        }
        //审批状态:1，未处理，2审核中 3已通过，-1已拒绝,-2异常，-3作废
        if (!in_array($request->process_result, [1,2])) {
            return Result::failure('申请状态不正确');
        }

        $this->businessName = $request->process_name;

        $canHandle = $this->canHandleCurrentFlow($request);

        if ($canHandle < 0) {
            return Result::failure('您没有权限处理此审批');
        }

        if ($request->canceling == 1) {
            DB::beginTransaction();
            try {
                $request->canceling = 2;
                $request->save();
                $this->saveBusinessTrace($requestId, '同意撤销');

                $userInfo = User::where('login_name', $request->createdUser)->first();
                if (!empty($userInfo) && !empty($userInfo->email)) {
                    hSentMailFast(
                        '您申请撤销主题为：['.$request->message.']的请求已经通过，您现在可以撤销该申请！处理人：'.$this->getUserInfo()['username'],
                        $userInfo->email,
                        'ERP提醒 撤销申请已通过'
                    );
                }

                DB::commit();
                return Result::succeed('处理完成');
            } catch (\Exception $ex) {
                DB::rollback();
                hFormatException($ex, true);
                return Result::failure('出现未知错误，请联系管理员');
            }
        } else {
            return Result::failure('未申请撤销货已同意撤销');
        }
    }

    /**
     * @param ProcessRequest $request
     * @param string $reason
     * @return mixed
     */
    private function requestPassed(ProcessRequest $request, $reason = '')
    {
        //调用具体流程类中定义的代码，处理数据
        $result = $this->handRequest($this->flowPara, 'PASS', $reason);

        if ($result->succeed > 0) {
            $request->process_result = 3;
            $request->final_confirmd_user = $this->getUserInfo()['login_name'];
            $request->final_confirmd_time = date('Y-m-d H:i:s');
            $request->passed_flow = count($this->getFlows());
            $request->modifiedDate = date('Y-m-d H:i:s');
            $request->modifiedUser = $this->getUserInfo()['login_name'];
            $request->save();

            event(new BusinessProcessEvent($request, 'PASS'));
        }
        return $result;
    }

    /**
     * @param ProcessRequest $request
     * @return mixed
     */
    private function moveNextProcess(ProcessRequest $request)
    {
        $request->process_result = 2;
        $request->final_confirmd_user = $this->getUserInfo()['login_name'];
        $request->final_confirmd_time = date('Y-m-d H:i:s');
        $request->passed_flow = $request->passed_flow + 1;
        $request->modifiedDate = date('Y-m-d H:i:s');
        $request->modifiedUser = $this->getUserInfo()['login_name'];
        $request->save();

        //$this->distributeMessage($this->getFlowInfoByIndex($request->passed_flow),$request->id,'');
    }

    private function requestRejected(ProcessRequest $request, $reason = '')
    {
        //调用具体流程类中定义的代码，处理数据
        $result = $this->handRequest($this->flowPara, 'REJECT', $reason);
        if ($result->succeed > 0) {
            $request->process_result = -1;
            $request->final_confirmd_user = $this->getUserInfo()['login_name'];
            $request->passed_flow = $request->passed_flow + 1;
            $request->final_confirmd_time = date('Y-m-d H:i:s');
            $request->modifiedDate = date('Y-m-d H:i:s');
            $request->modifiedUser = $this->getUserInfo()['login_name'];
            $request->save();
            event(new BusinessProcessEvent($request, 'REJECT'));
        }
        return $result;
    }

    /**
     * 获取指定流程下面的审批步骤
     */
    private function getFlows()
    {
        //TODO 获取指定流程下面的审批步骤
        return ProcessFlow::getFlowsByBusiness($this->getBusinessName());
    }

    /**
     * 记录请求日志
     */
    private function saveBusinessTrace($requestId, $message)
    {
        $trace = new ProcessTrace();
        $trace->createdUser = $this->getUserInfo()['login_name'];
        $trace->requestId = $requestId;
        $trace->message = $message;
        $trace->save();
    }

    /**
     * @param array $para
     * @param $type
     * @param $remark
     * @return mixed 流程审核通过之后执行的操作
     */
    private function handRequest(array $para, $type, $remark)
    {
        try {
            $method = $this->getMethodInfo($this->getBusiness()->handle_action);

            if (is_array($method) && count($method) == 2) {
                call_user_func($method, $this->getUserInfo(), $para, $type, $remark);
                return Result::succeed('执行成功');
            }
            return Result::failure('流程配置不正确');
        } catch (KnownLogicException $ex) {
            Log::error(hFormatException($ex));
            return Result::failure('处理失败' .$ex->getMessage());
        } catch (\Exception $ex) {
            Log::error(hFormatException($ex));
            return Result::failure('流程配置不正确' .$ex->getMessage());
        }
    }

    /**
     * @return mixed 获取消息，根据当前流程定义以及当前流程参数返回消息，此消息将在审批流各处展现
     */
    private function getRequestMessage()
    {
        if (count($this->getBusiness()) > 0 && !empty($this->getBusiness()->message_method)) {
            $methodInfo = $this->getMethodInfo($this->getBusiness()->message_method);
            if (is_array($methodInfo) && count($methodInfo) == 2) {
                try {
                    $message = call_user_func($methodInfo, $this->getUserInfo(), $this->flowPara);
                    return $message;
                } catch (\Exception $ex) {
                    //详细信息异常仅显示前100个字符
                    return '详细信息获取异常：'. substr($ex->getMessage(), 0, 100);
                }
            } else {
                return '无法获取详细信息:无法找到指定方法'.$this->getBusiness()->message_method;
            }
        } else {
            return '未无法获取详细信息:未配置方法';
        }
    }
    #endregion

    /**
     * @param $methodPath 传入方法路径：例如App\Http\Controllers\TestController\getMessage
     * @return array 返回带命名空间类名，和方法名称 ['App\Http\Controllers\TestController', 'getMessage']
     * @throws \Exception  未能正确的获取方法
     */
    private function getMethodInfo($methodPath)
    {
        $posi = strripos($methodPath, '\\');
        if ($posi == 0) {
            throw new \Exception('方法格式不正确');
        }

        $className = substr($methodPath, 0, $posi);
        $method = substr($methodPath, $posi + 1, strlen($methodPath));

        //识别App\Http\Controllers前缀
        if (hStrStartWith($className, 'App\Http\Controllers') >= 0) {
            return array($className, $method);
        }
        return array('App\Http\Controllers\\'. $className, $method);
    }

    #region 校验方法

    /**
     * 根据当前传入的参数，检查指定流程是否具有正在审批的请求，如果有则不允许发起新请求
     * @return int -1表示没有， 1表示存在
     */
    private function isSameOnProcess()
    {
        $existed = DB::table('ruigu_business_process_request')->where(['process_name'=>$this->getBusinessName(), 'pdata'=>json_encode($this->flowPara)])
            ->where(function ($query) {
                $query->where('process_result', '=', '1')
                    ->orWhere(function ($query) {
                        $query->where('process_result', '2');
                    });
            })->get();
        if (count($existed) > 0) {
            return 1;
        }
        return -1;
    }


    /**
     * @param ProcessRequest $request 当前请求
     * @return int|mixed 是否具有权限
     */
    private function canHandleCurrentFlow(ProcessRequest $request)
    {
        $flows = $this->getFlows();

        foreach ($flows as $flow) {
            if ($flow->flow_index == $request->passed_flow + 1) {
                //获取审批流的审批
                $hasAuthed = ProcessFlowAuthorizer::
                    CheckAuthorization($flow->pass_type, $this->currentUserId, explode(',', $flow->auditor));
                return $hasAuthed;
            }
        }
        return -1;
    }


    /**
     * 当开始流程时根据流程配置初始化流程index
     * 例如一个流程审批流是：主管->经理->CEO，当经理申请此流程时，应直接跳过主管审核这一步
     * @param int $userId 操作用户ID
     * @return int 正确的流程ID
     */
    private function initFlowIndex($userId)
    {
        $resultIndex = 0;
        $flows = $this->getFlows();
        $maxFlowIndex = 0;
        foreach ($flows as $flow) {
            $maxFlowIndex = $flow->flow_index;
            $auditor = explode(',', $flow->auditor);
            $hasAuth = ProcessFlowAuthorizer::CheckAuthorization($flow->pass_type, $userId, $auditor);
            if ($hasAuth > 0) {
                $resultIndex = $flow->flow_index;
            }
        }
        if ($maxFlowIndex == $resultIndex) {
            return -1;
        }
        return $resultIndex;
    }

    /**
     * @param $requestId 请求ID
     */
    private function getRequest($requestId)
    {
        $request = ProcessRequest::find($requestId);

        return $request;
    }

    private function getFlowInfoByIndex($idnex)
    {
        $flows = $this->getFlows();
        foreach ($flows as $flow) {
            if ($flow->flow_index == $idnex) {
                return $flow;
            }
        }
        return null;
    }


    #endregion
}
