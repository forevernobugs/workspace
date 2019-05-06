<?php

Route::group(['middleware' => ['widget']], function ($router) {
    $router->post('transport/car_list', 'Transport\TransportController@carList');   //获取车辆列表
    $router->post('transport/car_edit', 'Transport\TransportController@carEdit');   //车辆编辑页面
    $router->post('transport/car_update', 'Transport\TransportController@carUpdate');  //车辆更新
    $router->post('transport/car_save', 'Transport\TransportController@carSave');  //车辆添加

    $router->post('transport/driver_list', 'Transport\TransportController@driverList');   //获取司机列表
    $router->post('transport/driver_edit', 'Transport\TransportController@driverEdit');   //司机编辑
    $router->post('transport/driver_save', 'Transport\TransportController@driverSave');   //司机存储

    $router->post('transport/supplier_list', 'Transport\TransportController@supplierList');  //获取物流供应商列表
    $router->post('transport/supplier_edit', 'Transport\TransportController@supplierEdit');  //物流供应商编辑页面
    $router->post('transport/supplier_update', 'Transport\TransportController@supplierUpdate');  //物流供应商更新
    $router->post('transport/supplier_save', 'Transport\TransportController@supplierSave');  //物流供应商添加

    $router->post('transport/fencing_list', 'Transport\ConsignFencingController@fencingList');  //围栏线路信息列表
    $router->post('transport/statistical_info', 'Transport\TransportController@statisticalInfo');  //获取运输统计信息
});
