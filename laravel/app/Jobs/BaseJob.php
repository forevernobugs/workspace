<?php

namespace App\Jobs;

use App\Exceptions\KnownLogicException;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 30/08/2017
 * Time: 11:54
 */
abstract class BaseJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $mailReminder = 'xiangbohua@ruigushop.com';
    const TABLE_NAME = 't_task_queue';
    protected $queueName;
    protected $para;
    protected $taskId;

    private $isRedoProcess = false;
    private $currentTime = 0;
    /**
     * 系统计划支持3个队列，希望按照任务预计耗时长度放到相似的队列中去
     * 这里的优先级并不指队列执行时系统分配资源的优先级
     * 仅仅起到将十分耗时的任务放到不同队列中去，
     * 避免因为一个任务耗时过长导致所有队列任务等待太久时间
     * 需要服务器配置支持
     * 支持的值：0  1  //2
     * 默认为0
     * @var int
     */
    protected $priority = 0;

    public function getPriority()
    {
        return $this->priority;
    }

    public function justForTest()
    {
        return $this->realHandler();
    }

    public function getId()
    {
        return $this->taskId;
    }

    /**
     * @return bool 指明当前队列是否属于重启过程
     */
    public function isRedoProcess()
    {
        return $this->isRedoProcess;
    }

    /**
     * 当任务handler方法结束之后执行的代码
     * 此方法不支持抛出错误，并且父类不提供事务
     * 适合执行没有错误抛出的、结束之后的简单代码
     * @param $batchTaskCommited
     */
    protected function afterTaskDone($batchTaskCommited)
    {
    }

    /**
     * 队列人去在开启的时候是否延时执行，返回大于0的整数，
     * 队列任务可以根据业务类型甚至具体业务参数决定是否需要延时执行
     * 需要延时执行的返回正整数表示延时的秒数
     * @return int
     */
    public function getDelayedSeconds()
    {
        return 0;
    }

    /**
     * 创建任务实例
     * @param $para
     * @param int $redoTaskId 如果传入ID则表示当前过程属于重启Task，此时不再向数据库插入数据，而是将数据库para取出
     * @throws Exception
     * @internal param string $tag
     */
    public function __construct($para, $redoTaskId = 0)
    {
        try {
            $this->queueName = $this->getQueueName();
            if (empty($this->queueName)) {
                throw  new \Exception('Queue name must be define!');
            }
            if ($redoTaskId === 0) {
                $this->para = $para;
                $this->taskId = DB::table(self::TABLE_NAME)->insertGetId([
                    'created'=>time(),
                    'job_name'=>$this->queueName,
                    'finished'=>0,
                    'job_status'=>0,
                    'para'=>is_string($para) ? json_encode($para) : json_encode($para)
                ]);
            } else {
                $this->isRedoProcess = true;
                if (!$this->allowRedo()) {
                    throw new KnownLogicException('This task was not allowed redo!!!');
                }

                $paraFromDb = DB::table('t_task_queue')->where('id', $redoTaskId)->select('para', 'times')->get();

                if (empty($paraFromDb) || !isset($paraFromDb[0]->para)) {
                    throw new KnownLogicException('Trying to redo task but task not found with giving task id: '. $redoTaskId);
                }
                $this->para = $paraFromDb[0]->para;
                $this->currentTime = $paraFromDb[0]->times;
                $this->taskId = $redoTaskId;
            }

            if (empty($this->taskId)) {
                throw new KnownLogicException('Task id not created');
            }
        } catch (\Exception $e) {
            $stamp = hGetMillisecond();
            Log::error('QUEUE ADD ERROR stamp'.$stamp.',job_name:'.$this->queueName.' para:'.json_encode($para).' errormsg'.hFormatException($e));
            throw new \Exception('Unknown error. error use this stamp to find error message in log file:'. $stamp);
        }
    }

    /**
     * 执行任务
     *
     * @param
     * @return void
     */
    public function handle()
    {
        $startTime = time();
        $checkStatus = DB::table('t_task_queue')->where('id', $this->getId())->value('job_status');

        if ($checkStatus == -2) {
            $this->markStatus(4, '手动停止队列执行，防止队列无法停止');
            return ;
        }

        try {
            DB::table(self::TABLE_NAME)->where('id', $this->taskId)->update(['job_status'=>1,'started'=>time()]);
        } catch (Exception $e) {
            Log::error('QUEUE MARK DOING STATUS ERROR stamp,job_name:'.$this->queueName.' para:'.json_encode($this->para).' errormsg'.hFormatException($e));
            return;
        }
        try {
            $result = $this->realHandler();

            if (false !== $result) {
                $this->handleAfterTask(true);

                //queue execute succeed
                $return_msg = '0';
                if ($result !== null) {
                    try {
                        $return_msg = ''.$result;
                    } catch (Exception $ee) {
                        $return_msg = '0';
                    }
                }
                $this->markStatus(2, $return_msg);
            } else {
                $this->handleAfterTask(false);
                //queue execute failed
                $this->markStatus(3, '未发生异常，但是handler返回false');
                Log::error('QUEUE EXECUTE FAILED, RealHandler returns:', $result);
            }
        } catch (Exception $ex) {
            $this->delete();//如果报错直接从队列移除
            $this->handleAfterTask(false);

            $msg = $msg = $ex->getMessage();
            $this->markStatus(3, $msg);
            $string = hFormatException($ex);
            Log::error('QUEUE  EXECUTE EXCEPTION, Exception message:'. $string);
        } finally {
            $end = time();
            Log::info('队列任务结束,ID '.$this->getId() .' '.hdate($startTime).'结束'.hdate($end).' 耗时'.($end - $startTime));
        }
    }

    private function handleAfterTask($succeed)
    {
        try {
            $this->afterTaskDone($succeed);
        } catch (\Exception $ex) {
            Log::error('afterTaskDone方法抛出错误');
        }
    }
    private function markStatus($status, $return_msg = '')
    {
        $data = ['job_status'=>$status, 'return_msg'=>$return_msg];
        if ($status == 2) {
            $data['finished'] = time();
            $data['times'] = $this->currentTime + $this->attempts();
        }
        if ($status == 3) {
            $data['times'] = $this->currentTime + $this->attempts();
        }
        DB::table(self::TABLE_NAME)->where('id', $this->taskId)->update($data);
    }

    /**
     * 此方法返回flag指明当前任务类型是否允许手动重启
     * 返回true时表示次任务允许重启，返回string时表示不允许重启的原因
     * @return bool/string 结果
     */
    public function allowRedo()
    {
        return true;
    }

    /**
     * get the queue name
     * to enable auto restart failed task do not change anything but write those:
     * return __CLASS__;
     * @return mixed
     */
    abstract protected function getQueueName();

    /**
     * Task实际执行的代码，
     * @return mixed 返回false表示任务失败， 返回字符串时将会被记录在return_msg中
     */
    abstract protected function realHandler();

    /**
     * 添加队列之前验证数据是否正确，本方法接收$this->para
     * @param  array $itemData 本次需要检查的数据
     * @return null /bool 返回null时表示可以执行队列，返回非null将视为数据验证失败
     */
    abstract protected function checkDataError($itemData);

    public function checkData()
    {
        if (is_array($this->para)) {
            if ($this->isMultipleMode()) {
                foreach ($this->para as $p) {
                    return $this->checkDataError($p);
                }
                return null;
            } else {
                $checkMsg = $this->checkDataError($this->para);
                return $checkMsg;
            }
        } else {
            return $this->checkDataError($this->para);
        }
    }

    protected function isMultipleMode()
    {
        return count($this->para) !== count($this->para, 1);
    }


    public function getName()
    {
        return $this->getQueueName();
    }

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
     * 标记此次队列数据不满足要求，将状态改为-1
     */
    public function markAsCheckError($errorMsg = '')
    {
        $this->markStatus(-1, $errorMsg);
    }

    protected function applyNewPara()
    {
        try {
            return DB::table(self::TABLE_NAME)->where('id', $this->taskId)->value('para');
        } catch (\Exception $ex) {
            Log::error('任务重启时重新获取数据失败，停止执行此任务');
            $this->markStatus(4, '任务重启时重新获取数据失败，停止执行此任务，需要手动检查');
            return null;
        }
    }

    /**
     * 此方法当redoMark = 1 时调用
     * 此方法提供给子类重写
     * 当队列任务需要手动重启时调用此方法，重新恢复现场
     * 将para转换成子类任务能够识别的对象
     * 调用此方法时para已经有值，仅需执行转换操作，重新赋值给$this->para即可
     * 父类不进行任何操作
     */
    public function recoverData()
    {
        //默认情况下参数将为数组，特殊情况下可能需要反序列化
        $this->para = object_to_array(json_decode($this->para));
    }

    /**
     * 校验参数是否完整
     * @param $para
     * @param $paraName
     * @param string $errorMessage
     * @throws JobParaNotSetException
     * @throws Exception
     */
    protected function checkParaIsset($para, $paraName, $errorMessage = '')
    {
        if (is_string($paraName)) {
            if (!isset($para[$paraName])) {
                throw new JobParaNotSetException(
                    $paraName,
                    '参数未设置'. (empty($errorMessage) ? $paraName : $errorMessage)
                );
            }
        } else {
            throw new \Exception('参数名必须为数组');
        }
    }

    /**
     * 批量校验参数是否设置
     * @param $para
     * @param $checkInfo
     * @throws JobParaNotSetException
     */
    protected function batchCheckParaIsset($para, $checkInfo)
    {
        foreach ($checkInfo as $paraName => $message) {
            $this->checkParaIsset($para, $paraName, $message);
        }
    }
}
