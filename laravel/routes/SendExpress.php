<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/18
 * Time: 17:18
 */

Route::group(['middleware' => ['widget'],'namespace' => 'SendExpress'], function ($router) {
    //获取快递单号信息列表页
    $router->post('send_express/get_send_express_list','SendExpressController@GetSendExpressList');

});