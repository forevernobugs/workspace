<?php
namespace App\Http\Controllers\System;

use App\Http\Controllers\LoginRequireController;
use App\Models\QueueModel\TaskQueueRecord;
use App\Models\System\SystemMonitor;

class SystemMonitorController extends LoginRequireController
{
    /**
     * 系统日志
     */
    public function searchSystemLog()
    {
        $filePath = base_path('storage/logs/lumen.log');
        $logData = [];
        $lineCount = $this->getInputWithDefault('line_count', 100);
        $lineCount = is_numeric($lineCount) ? $lineCount : 100;

        $page = $this->getInputWithDefault('page', 0);
        $page = is_numeric($page) ? $page  + 1 : 1;

        if (file_exists($filePath)) {
            $totalLine = hLineCount($filePath);

            $startLine = $totalLine - $page * $lineCount + 1;

            $endLine = $startLine + $lineCount - 1;
            $startLine = $startLine < 0 ? 0 : $startLine;

            #echo $startLine, ' ',$endLine,'--';
            #echo $lineCount, ' ',$lineBefore;

            $logData = gGetFileLine($filePath, $startLine, $endLine, true);
        }
        
        return hSucceed('OK', ['logs'=>$logData]);
    }

    /**
     * 登录日志
     */
    public function loginLogList(SystemMonitor $monitor)
    {
        checkLogic(isset($this->input['user_id']), '当前没有用户!');
        $logList = $monitor->getLoginLogList($this->input);

        foreach ($logList['list'] as &$log) {
            $log['login_succeed'] = $log['login_succeed'] == 1 ? '成功' : '失败';
        }

        $title = [
            'id' => 'ID',
            'login_name' => '用户',
            'ip_address' => '登录IP',
            'login_time' => '登录时间',
            'login_succeed' => '是否成功',
            'remark' => '备注'
        ];
        
        return $this->returnList('加载成功', $logList, $title, '用户登录日志');        
    }

    /**
     * 操作日志
     */
    public function operationLogList(SystemMonitor $monitor)
    {
        checkLogic(isset($this->input['user_id']), '当前没有用户!');
        $operationList = $monitor->getOperationLogList($this->input);
        
        foreach ($operationList['list'] as &$log) {
            switch ($log['operation_type']) {
                case 'insert':
                    $log['operation_type'] = '新增';
                    break;
                case 'delete':
                    $log['operation_type'] = '删除';
                    break;
                case 'update':
                    $log['operation_type'] = '更新';
                    break;
                case 'select':
                    $log['operation_type'] = '查询';
                    break;
                default:
                    $log['operation_type'] .= '不明操作';
                    break;
            }
        }

        $title = [
            'id' => 'ID',
            'ogname' => '对应仓库',
            'user_name' => '用户名',
            'operation_type' => '操作类型',
            'operation' => '操作内容',
            'opreation_time' => '操作时间',
            'operation_level' => '时间级别'
        ];

        return $this->returnList('加载成功', $operationList, $title, '用户操作日志');        
    }

    /**
     * 获取队列列表
     */
    public function getTaskList(TaskQueueRecord $model){
        return hSucceed('', $model->getTaskList($this->input));
    }

    /**
     * 队列详情
     */
    public function taskDetail(TaskQueueRecord $model){
        checkLogic(isset($this->input['id']), '请传入ID');
        $item = $model->get_one($this->input);
        if (empty($item)) {
            return hError('未找到');
        }

        return hSucceed('', ['item'=>$item]);
    }

    /**
     * 重启队列
     */
    public function redoTask(TaskQueueRecord $model){
        $params = $this->input;
        checkLogic(isset($params['id']), '请传入taskId');
        $ids = $params['id'];
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $msg = '';
        $userName = $this->getUserName($this->user_id);
        foreach ($ids as $id) {
            //记录重启日志过程
            $result = $model->reScheduleTask($id, $userName);

            if ($result !== true) {
                $msg .= $id.$result.";";
            }
        }
        if (empty($msg)) {
            return hSucceed('重启成功,共'.count($ids));
        }

        return hError('存在重启失败的任务：'.$msg);
    }

    /**
     * 修改队列参数
     */
    public function abandonTask(TaskQueueRecord $model){
        checkLogic(isset($this->input['id']), '请传入ID');
        $ids = $this->input['id'];
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $model->abandonTask($ids, $this->user_id);
        return hSucceed('选中任务已废弃');
    }

    /**
     * 修改队列参数
     */
    public function updateTaskPara(TaskQueueRecord $model){
        checkLogic(isset($this->input['id']), '请传入taskId');
        checkLogic(isset($this->input['para']), '请传入新参数');

        $model->updateTaskPara($this->input);
        return hSucceed('更新成功');
    }
}

