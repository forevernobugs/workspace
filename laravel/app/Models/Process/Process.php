<?php

namespace App\Models\Process;

use App\Models\BaseModel;
use App\Models\Permission\Role;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 17:21
 */
class Process extends BaseModel
{
    protected $table = 't_process';
    //审批状态
    const STATUS_MAPPING = [
        '1' => '未处理',
        '2' => '审核中',
        '3' => '已通过',
        '-1' => '已拒绝',
        '-2' => '异常',
        '-3' => '作废'
    ];

    //定义是否启用
    const ENABLED_ON = 1;  //启用
    const ENABLED_OFF = 0;  //不启用

    //流程处理范围
    const PASS_TYPE_USER = 1;  //用户
    const PASS_TYPE_GROUP = 2;  //用户组
    const PASS_TYPE_COMPANY = 3;  //组织架构

    public function getList()
    {

    }

    public function saveProcess($params)
    {
        $data = array(
            'process_name' => $params['process_name'],
            'description' => $params['description'],
            //'department' => $params['department'],
            'enabled' => isset($params['enabled']) ? $params['enabled'] : 0,
            'handle_action' => $params['handle_action'],
            'message_method' => $params['message_method'],
            'self_cancel' => $params['self_cancel']
        );
        DB::beginTransaction();
        try{
            //id为假新增，反之修改
            if (empty($params['id'])) {
                $data['createdDate'] = hdate();
                $data['createdUser'] = User::getLoginName($params['user_id']);
                $id = DB::table($this->table)->insertGetId($data);
                //记录业务日志
                $this->businessLog($this->getLoginNameByUserId($params['user_id']), '新增流程配置，' . '流程名称：' . $params['process_name'] . ',处理方法'.$params['handle_action'].',消息方法'.$params['message_method'], 'PROCESS_INSERT', $id);
            } else {
                DB::table($this->table)->where('id', $params['id'])->update($data);
                //记录业务日志
                $this->businessLog($this->getLoginNameByUserId($params['user_id']), '修改流程配置，' . '流程名称：' . $params['process_name'] . ',处理方法'.$params['handle_action'].',消息方法'.$params['message_method'], 'PROCESS_UPDADED', $params['id']);
            }

            //修改审批流配置和request表相关数据
            $this->updateProcessFlow($params);

            DB::commit();

        } catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    private function updateProcessFlow($params)
    {
        $this->where('process_name', $params['process_name'])->delete();

        if(isset($params['process_id'][0]) && false === empty($params['process_id'][0])){
            $this->saveFlow($params);
        }
        DB::table('t_process_request')
            ->where('process_name', $params['process_name'])
            ->whereIn('process_result', [ProcessFlow::STATUS_UNTREATED, ProcessFlow::STATUS_REVIEW])
            ->update(['process_result' => ProcessFlow::STATUS_EXCEPT]);
    }

    /**
     * 用户待办事项列表
     *
     * @param array $params 请求参数
     * @return mixed[array|boolean]
     */
    public function getUserTask($params=[])
    {
        $flowModel = DB::table('ruigu_business_process_flow as rbpf')
            ->join('ruigu_business_process_request as rbpr', function($join) {
                $join->on('rbpf.process_name', '=', 'rbpr.process_name')
                    ->on('rbpf.flow_index', '=', DB::raw('rbpr.passed_flow +1'));
            });

        $flowModel->where('rbpf.is_enable', '=', self::ENABLED_ON);
        $flowModel->whereIn('rbpr.process_result', [1, 2]);

        if (isset($params['title']) && false === empty($params['title'])) {
            $flowModel->where('rbpr.title', 'like', '%' . $params['title'] . '%');
        }
        if (isset($params['message']) && false === empty($params['message'])) {
            $flowModel->where('rbpr.message', 'like', '%' . $params['message'] . '%');
        }
        if (isset($params['reason']) && false === empty($params['reason'])) {
            $flowModel->where('rbpr.reason', 'like', '%' . $params['reason'] . '%');
        }
        if (isset($params['process_name']) && false === empty($params['process_name'])) {
            $flowModel->where('rbpr.process_name', '=', $params['process_name']);
        }
        if (isset($params['time_start']) && false === empty($params['time_start'])) {
            $flowModel->where('rbpr.createdDate', '>=', $params['time_start']);
        }
        if (isset($params['time_end']) && false === empty($params['time_end'])) {
            $flowModel->where('rbpr.createdDate', '<=', $params['time_end']);
        }

        $userInfo = User::getUserInfo($params['user_id']);
        //拼接sql
        $sql = '(';
        $sql .= '(find_in_set(' . $params['user_id'] . ', rbpf.auditor) AND rbpf.pass_type='. self::PASS_TYPE_USER .')';
        //获取组织架构id
        $positionId = $userInfo->org_id;
        if (!empty($positionId)) {
            $sql .= ' OR (find_in_set(' . $positionId . ',rbpf.auditor) AND rbpf.pass_type='. self::PASS_TYPE_COMPANY .')';
        }
        //获取组id
        $groupIds = Role::getUserRoles($params['user_id']);
        if (!empty($groupIds)) {
            foreach ($groupIds as $key=>$value) {
                $sql .= ' OR (find_in_set(' . $value . ',rbpf.auditor) AND rbpf.pass_type='. self::PASS_TYPE_GROUP .')';
            }
        }
        $sql .= ')';

        $flowModel->whereRaw($sql);

        $data = $flowModel->paginate($params['pagesize']);
        $dataArray = object_to_array($data);
        return ['list' => $dataArray['data'], 'page' => $dataArray];
    }

    /**
     * 获取用户历史事项
     * @param array $params
     * @return array
     */
    public function getUserTaskHistory($params=[])
    {
        $model = DB::table('ruigu_business_process_request as rbpr')
            ->select(['rbpr.*', 'rbpt.*', 'eu.username'])
            ->leftJoin('ruigu_business_process_trace as rbpt', 'rbpr.id', '=', 'rbpt.requestid')
            ->leftJoin('erp_user as eu', 'eu.login_name', '=', 'rbpt.createdUser');

        if (isset($params['process_result']) && false === empty($params['process_result'])) {
            $model->where('rbpr.process_result', '=', $params['process_result']);
        } else{
            $model->whereNotIn('rbpr.process_result', [1, 2]);
        }
        if (isset($params['title']) && false === empty($params['title'])) {
            $model->where('rbpr.title', 'like', '%' . $params['title'] . '%');
        }
        if (isset($params['message']) && false === empty($params['message'])) {
            $model->where('rbpt.message', 'like', '%' . $params['message'] . '%');
        }
        if (isset($params['reason']) && false === empty($params['reason'])) {
            $model->where('rbpr.reason', 'like', '%' . $params['reason'] . '%');
        }
        if (isset($params['time_start']) && false === empty($params['time_start'])) {
            $model->where('rbpr.createdDate', '>=', $params['time_start']);
        }
        if (isset($params['time_end']) && false === empty($params['time_end'])) {
            $model->where('rbpr.createdDate', '<=', $params['time_end']);
        }
        if (isset($params['process_name']) && false === empty($params['process_name'])) {
            $model->where('rbpr.process_name', '=', $params['process_name']);
        }

        $data = $model->paginate($params['pagesize'])->toArray();
        $list = $data['data'];
        unset($data['data']);
        return ['list' => object_to_array($list), 'page' => $data];
    }

    #regiron 返回待办事项的详细情况
    public function getDetailUrl($para)
    {
        checkLogic(isset($para['id']), '请传入ID');

        $requestData = DB::table('t_process_request')
            ->where('id', $para['id'])
            ->first(['process_name', 'pdata']);
        checkLogic(
            in_array($requestData->process_name, self::DETAIL_PAGE_RESOLVER),
            '此流程尚未配置详情页');

        $method = self::DETAIL_PAGE_RESOLVER[$requestData->process_name];
        checkLogic(is_callable($this->$method), '此流程的详情页配置不正确');

        $url = $this->$method(json_decode($requestData->pdata));
        return hSucceed('获取成功', ['url'=>$url]);
    }

    const DETAIL_PAGE_RESOLVER = [
        'PurchasingPayApply'=>'pay'
    ];
    #endregion

}
