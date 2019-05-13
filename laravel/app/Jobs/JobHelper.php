<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class JobHelper
{

    /**
     * @param string $queueFullName 类全名
     * @param array $para 参数
     * @param int $redoTaskId  如果传入的时ID
     * @return string /QueueJobBase 队列任务对象，创建失败则返回原因
     */
    public static function createJob($queueFullName, $para, $redoTaskId = 0)
    {
        if (class_exists($queueFullName)) {
            try {
                $queueObject = new $queueFullName($para, $redoTaskId);
                if ($queueObject instanceof  BaseJob) {
                    //将不同类型的任务放到相同的队列名称中去，
                    //2017-09-20 更新：放入不同队列会导致无法取出
                    #return $queueObject;
                    //2017-09-26 更新：放入不同的队列时需要在命令之后增加 --queue=QUEUE_NAME 参数从指定队列中取出任务
                    //等到服务器配置成功之后开启此代码
                    $priority = $queueObject->getPriority();
                    $delaySeconds = $queueObject->getDelayedSeconds();
                    if (is_numeric($delaySeconds) || $delaySeconds > 0) {
                        $delaySeconds = round($delaySeconds);
                    } else {
                        $delaySeconds = null;
                    }
                    switch ($priority) {
                        case 1:
                            $queueObject->onQueue('more_long');
                            break;
                        case 2:
                            $queueObject->onQueue('most_long');
                            break;
                    }
                    if (!empty($delaySeconds)) {
                        $queueObject->delay($delaySeconds);
                    }

                    return $queueObject;
                } else {
                    return '创建对象失败，对象不为QueueJobBase';
                }
            } catch (\Exception $ex) {
                Log::error('创建队列对象失败.'. hFormatException($ex));
                return $ex->getMessage();
            }
        } else {
            Log::error('任务定义未找到.'. $queueFullName);
            return '任务定义未找到 '. $queueFullName;
        }
    }


    public static function createJobWithAlias($queueCode, $para, $redoTaskId = 0)
    {
        $fullName = JobContainer::getClassFullName($queueCode);
        if (empty($fullName)) {
            return '任务未找到';
        }

        return self::createJob($fullName, $para, $redoTaskId);
    }

    /**
     * @param string $queueName 任务名称代码，供外接调用时使用code，防止内部类名变化造成接口更改
     * @param array $queuePara 队列参数
     * @param int $redoTaskId  传入id则表示需要重启指定ID的task
     * @return bool|string 成功则true表示添加队列任务成功，失败测返回string表示失败原因
     * @internal param int $isRedo 是否使用现有数据重启代码
     */
    public static function dispatchJobWithAlias($queueName, $queuePara, $redoTaskId = 0)
    {
        $queueName = strtolower($queueName);
        $queueObject = self::CreateJobWithAlias($queueName, $queuePara, $redoTaskId);

        if ($queueObject instanceof  BaseJob) {
            return self::dispatchJobInternal($queueObject);
        }
        //创建Task 对象失败返回原因
        return  $queueObject;
    }

    public static function dispatchJob($fullName, $queuePara, $redoTaskId = 0)
    {
        $queueObject = self::createJob($fullName, $queuePara, $redoTaskId);
        if ($queueObject instanceof  BaseJob) {
            return self::dispatchJobInternal($queueObject);
        }
        return $queueObject;
    }

    /**
     * 分发队列任务
     * @param BaseJob $queueObject
     * @return bool|string
     */
    private static function dispatchJobInternal(BaseJob $queueObject)
    {
        if ($queueObject instanceof  BaseJob) {
            try {
                //手动重启任务过程中，先执行一次数据转换
                if ($queueObject->isRedoProcess()) {
                    try {
                        $queueObject->recoverData();
                    } catch (\Exception $ex) {
                        Log::error('手动重启任务过程中，转换数据失败'. $queueObject->getName(). '  ID:'.$queueObject->getId());
                        hFormatException($ex);
                        return '手动重启任务过程中，转换数据失败';
                    }
                }

                $checkResult = $queueObject->checkData();
                if ($checkResult !== null) {
                    $queueObject->markAsCheckError($checkResult);
                    return '失败！数据不满足要求:'.$checkResult;
                }
                dispatch($queueObject);
                return true;
            } catch (\Exception $ex) {
                $timeStamp = hGetMillisecond();
                $errorString = hFormatException($ex, false);
                Log::error('Method dispatch error:task id '.$queueObject->getId().
                    ' stamp:'.$timeStamp. ' '.$errorString);
                return '分发队列失败，Dispatch方法抛出异常,stamp:'.$timeStamp.$ex->getMessage();
            }
        } else {
            return '添加队列失败：'. json_encode($queueObject);
        }
    }
}
