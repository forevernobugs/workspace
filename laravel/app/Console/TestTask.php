<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 21:36
 */

namespace App\Console;

class TestTask extends BaseTask
{

    /**
     * 子类定义调度任务名称
     * return __CLASS__;
     * @return mixed
     */
    protected function defineName()
    {
        // TODO: Implement defineName() method.
    }

    /**
     * 子类定义调度任务描述
     * @return mixed
     */
    protected function defineDesc()
    {
        // TODO: Implement defineDesc() method.
    }

    /**
     * 每个任务必须指定执行计划
     * @return string
     */
    public function getCronPlan()
    {
        // TODO: Implement getCronPlan() method.
    }

    /**
     * 必须实现，正真的任务代码
     * @param $para
     * @return mixed
     */
    protected function doTask($para)
    {
        // TODO: Implement doTask() method.
    }

    /**
     * 强制要求实现错误方法
     * @param $exception
     */
    protected function onException($exception)
    {
        // TODO: Implement onException() method.
    }
}
