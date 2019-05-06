<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/21
 * Time: 13:58
 */

/**
 * 无需认证的请求路由
 */
$router->post('open/do_login', 'Open\LoginController@login'); // 发起登录
$router->post('open/upload', 'NologinController@upload'); //上传
$router->get('open/export', 'NologinController@export'); //导出excel
$router->post('open/setpwd', 'NologinController@setpwd');
$router->post('open/do_setpwd', 'NologinController@do_setpwd');
$router->post('open/check_user', 'NologinController@check_user');
$router->post('open/do_check_user', 'NologinController@do_check_user');
