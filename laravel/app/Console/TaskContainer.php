<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 8/31/17
 * Time: 12:57 PM
 */
namespace App\Console;

class TaskContainer
{
    /**
     * 支持的调度任务
     * 任务名称(类名) => 调度频率（有效的cron文本）
     */
    const SUPPORTED_TASK = [
        TestTask::class,

    ];
}
