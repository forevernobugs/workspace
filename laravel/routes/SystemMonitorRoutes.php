<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 27/09/2017
 * Time: 18:51
 */

Route::group(['middleware' => ['widget', 'additional']], function ($router) {
    $router->post('system/sys_log', 'System\SystemMonitorController@searchSystemLog');
    $router->post('system/login_log', 'System\SystemMonitorController@loginLogList');
    $router->post('system/operation_log', 'System\SystemMonitorController@operationLogList');
    
    $router->post('system/get_task_list', 'System\SystemMonitorController@getTaskList');
    $router->post('system/abandon_task', 'System\SystemMonitorController@abandonTask');
    $router->post('system/task_detail', 'System\SystemMonitorController@taskDetail');
    $router->post('system/redo_task', 'System\SystemMonitorController@redoTask');
    $router->post('system/update_task_para', 'System\SystemMonitorController@updateTaskPara');
});
