<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 8/31/17
 * Time: 12:54 PM
 */
namespace App\Console;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

abstract class BaseTask
{
    const TABLE_NAME = 't_task_info';

    public function justForTest()
    {
        return $this->doTask([]);
    }

    /**
     * 子类定义调度任务名称
     * return __CLASS__;
     * @return mixed
     */
    abstract protected function defineName();
    /**
     * 子类定义调度任务描述
     * @return mixed
     */
    abstract protected function defineDesc();

    /**
     * 每个任务必须指定执行计划
     * @return string
     */
    abstract public function getCronPlan();

    protected $beforeTaskResult;
    protected $taskResult;

    /**
     * 开始任务
     * @param array $para
     */
    public function startTask($para = [])
    {
        $startTime = time();
        try {
            DB::table(self::TABLE_NAME)->where('task_name', $this->getName())->increment('total_time');

            DB::table(self::TABLE_NAME)->where('task_name', $this->getName())->update(['status' => 1,'last_time'=>hdate()]);
            $this->beforeTaskResult = $this->beforeTask($para);

            $this->taskResult = $this->doTask($para);

            $this->afterTask($para);

            DB::table(self::TABLE_NAME)->where('task_name', $this->getName())->update(['status'=>0]);

            DB::table('ruigu_task_history')->insert([
                'task_name'=> $this->getName(),
                'start_time'=>$startTime,
                'end_time'=>time(),
                'result'=>0,
                'message'=>is_string($this->taskResult) ? $this->taskResult : '未返回string'
            ]);
        } catch (\Exception $ex) {
            try {
                $this->onException($ex);

                $exString = hFormatException($ex);
                Log::error($exString);
                DB::table(self::TABLE_NAME)->where('task_name', $this->getName())->increment('error_time');
                DB::table(self::TABLE_NAME)->where('task_name', $this->getName())->update([
                    'last_exception_time'=>hdate(),
                    'last_exception'=>$exString,
                    'status'=>0
                ]);
                DB::table('ruigu_task_history')->insert([
                    'task_name'=> $this->getName(),
                    'start_time'=>$startTime,
                    'end_time'=>time(),
                    'result'=>1,
                    'message'=>'失败：'.$exString
                ]);
            } catch (\Exception $e) {
                $exString = hFormatException($ex);
                Log::error('Task execute failed and log error too:'. $exString);
                DB::table(self::TABLE_NAME)->where('task_name', $this->getName())->update(['status'=>0]);
            }
        }
    }

    public function getName()
    {
        return $this->defineName();
    }

    public function getDesc()
    {
        return $this->defineDesc();
    }

    public function getEnable()
    {
        $checkEnabled = DB::table(self::TABLE_NAME)->where('task_name', $this->getName())->value('enabled');
        if ($checkEnabled === null) {
            DB::table(self::TABLE_NAME)->insert([
                'task_name'=>$this->getName(),
                'desc'=>$this->getDesc(),
                'cron_text'=>$this->getCronPlan(),
                'enabled'=>0]);
            return false;
        }
        return $checkEnabled == 1;
    }

    /**
     * 任务开始执行前先执行，可留空
     * @param $para
     * @return mixed
     */
    protected function beforeTask($para)
    {
    }

    /**
     * 必须实现，正真的任务代码
     * @param $para
     * @return mixed
     */
    abstract protected function doTask($para);

    /**
     * 任务开始执行前先执行，可留空
     * @param $para
     * @return mixed
     */
    protected function afterTask($para)
    {
    }

    /**
     * 强制要求实现错误方法
     * @param $exception
     */
    abstract protected function onException($exception);

    public static function getShortClassName($nameString)
    {
        if (empty($nameString)) {
            return '';
        }
        $arr = explode('\\', $nameString);
        if (empty($arr)) {
            return '';
        }
        return $arr[count($arr) - 1];
    }

    /**
     * 发送邮件
     * @param $subject
     * @param $mailBody
     * @param array $receiver
     * @param array $cc
     */
    public function sendMailNotification($subject, $mailBody, array $receiver, array $cc = [])
    {
        try {
            $cc[] = 'xiangbohua@ruigushop.com';
            if (empty($receiver)) {
                Log::error('邮件提醒没有收货人');
                return;
            }

            if (empty($subject) && empty($mailBody)) {
                Log::error('邮件提醒没有主题也有没有内容');
                return;
            }

            $msg = Mail::raw($mailBody, function ($message) use ($subject, $receiver) {
                $subject = 'ERP系统提醒：仓库事务导致商品可用库存小于0';
                $message->from('system@ruigushop.com', 'system');
                $message->to($receiver)->subject($subject);
                $message->cc('xiangbohua@ruigushop.com')->subject($subject.' 抄送');
                // 在邮件中上传附件
            });

            if (1 != $msg) {
                Log::error('定时任务执行中尝试发送邮件，但是没有成功，'. $this->getName().' '.$this->getDesc());
            }
        } catch (\Exception $ex) {
            Log::error(hFormatException($ex));
            Log::error('定时任务执行中尝试发送邮件时发生未知错误，'. $this->getName().' '.$this->getDesc());
        }
    }

    public static function toggleTaskEnableState($para)
    {
        $taskName = $para['task_name'];
        $taskInfo = DB::table(self::TABLE_NAME)->where('task_name', $taskName)->first();

        $userName = User::getLoginName($para['user_id']);

        if ($taskInfo === null) {
            return '任务未找到';
        }

        $enabled = $taskInfo->enabled > 0 ? 0 : 1;
        DB::table(self::TABLE_NAME)->where('task_name', $taskName)->update([
            'enabled'=>$enabled
        ]);
        $log = [
            'relatedTableName' => 'ruigu_task_info',
            'relatedid' => $taskInfo->id,
            'createdOn' => time(),
            'createdBy' => $userName,
            'type' => 'UPDATE',
            'tag' => '',
            'logMessage' => '更改调度任务状态到'.($enabled == 0 ? '停止': '运行')
        ];
        DB::table('ruigu_business_log')->insert($log);

        return true;
    }
}
