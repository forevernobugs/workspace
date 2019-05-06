<?php
namespace App\Models\QueueModel;


use App\Jobs\QueueObjectCreator;
use App\Models\BaseModel;
use App\Models\Logs\BusinessLog;
use App\Models\DBHelper;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Jobs\JobHelper;
use App\Jobs\OrderJobs\TestJob;


class TaskQueueRecord extends BaseModel
{
    protected $table = 't_task_queue';
    public $timestamps = false;
    protected $logType = 'TaskQueueRecord';

    const STATUS_MAP = [
        0=>'未执行',
        1=>'执行中',
        2=>'已执行',
        3=>'执行失败',
        4=>'紧急异常',
        -1=>'参数错误',
        -2=>'手动停止'
    ];

    public function get_one($params = [])
    {
        if(isset($params['id'])){
            return $item = $this->where(['id' => $params['id']])->first()->toArray();
        }
        return [];
    }

    public function getTaskList($params = [])
    {
        $condition = [];
        if (isset($params['job_name']) && !empty($params['job_name'])) {
            $condition = [
                'job_name' => ['LIKE', "%{$params['job_name']}%"]
            ];
        }

        if(isset($params['job_status']) && is_numeric($params['job_status']))
        {
            $condition['job_status'] = $params['job_status'];
        }

        if(isset($params['id']) && is_numeric($params['id']))
        {
            $condition['id'] = $params['id'];
        }
        if(isset($params['para']) && !empty($params['para']))
        {
            $condition['para'] = ['LIKE', "%{$params['para']}%"];
        }

        if(isset($params['return_msg']) && !empty($params['return_msg']))
        {
            $condition['return_msg'] = ['LIKE', "%{$params['return_msg']}%"];
        }
        $result = json_decode(DBHelper::table($this->table)->build_where($condition)
            ->select('id','job_name','job_status','created','started','return_msg','finished','times')
            ->paginate($params['pagesize'])->toJson(), true);
        $data = $result['data'];
        unset($result['data']);
        return ['list' => $data, 'page' => $result,'job_status'=>self::STATUS_MAP];
    }

    public function do_save($params = [])
    {
        // TODO: Implement do_save() method.
    }

    public function do_delete($params = [])
    {
        // TODO: Implement do_delete() method.
    }


    public function reScheduleTask($taskId, $userName = ''){
        $task = TaskQueueRecord::find($taskId);
        if(!in_array($task->status, [0, 3, 4])){
            //return '仅出错的任务允许重启';
        }

        if($task != null){
            $result = JobHelper::dispatchJob($task->job_name, [], $task->id);
            $businessLog = new BusinessLog();
            $businessLog->saveLog($userName, '重启了队列','RESCHEDULE',$task->id);
            return $result;
        }else{
            return '任务未找到';
        }
    }

    /**
     * 批量废弃任务，直接将特定状态下的任务设置为已经执行
     * @param $taskIds
     * @param $userId
     * @throws \Exception
     */
    public function abandonTask($taskIds, $userId)
    {
        $userInfo = User::getUserDetail($userId);

        DB::beginTransaction();

        try {
            DB::table($this->table)
                ->whereIn('job_status', [-1, 0, 1, 3])
                ->whereIn('id', $taskIds)->update([
                    'job_status'=>2,
                    'return_msg'=>DB::raw("concat(return_msg, '手动停止')")
                ]);

            $businessLog = new BusinessLog();
            $businessLog->saveLog(
                $userInfo->login_name,
                $userInfo->login_name.'废弃了一下任务:'.implode(',', $taskIds),
                'abandon_task',
                0
            );

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * 修改队列参数
     */
    public function updateTaskPara(array $para){
        $id = $para['id'];
        $userInfo = User::getUserDetail($para['user_id']);
        $para = $para['para'];
        $info = DB::table('t_task_queue')->where('id', $id)->select('job_status', 'para')->first();
        checkLogic(!is_null($info), '任务未找到');
        checkLogic(in_array($info->job_status, [-1, 0, 1, 2, 3]), '状态不允许更改');

         DB::beginTransaction();
        try {
            DB::table('t_task_queue')->where('id', $id)->update([
                'para'=>$para
            ]);

            $businessLog = new BusinessLog();
            $businessLog->saveLog(
                $this->table,
                $userInfo->login_name.'更改了队列参数【'.$info->para.'】->【'.$para.'】',
                'UPDATE_PARA',
                $id
            );
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }

    }
}