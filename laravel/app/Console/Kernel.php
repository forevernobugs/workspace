<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $tasks = TaskContainer::SUPPORTED_TASK;
        if (empty($tasks)) {
            Log::error('Error on getting tasks,container returns false');
            return;
        }

        foreach ($tasks as $taskName) {
            if (class_exists($taskName)) {
                $task = new $taskName();

                if ($task instanceof BaseTask) {
                    //任务启用时采取执行此任务
                    if ($task->getEnable() === false) {
                        continue;
                    }
                    $schedule->call(function () use ($task) {
                        $task->startTask([]);
                    })->cron($task->getCronPlan())->name($task->getDesc())->withoutOverlapping();
                }
            }
        }
    }
}
