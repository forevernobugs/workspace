<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/10
 * Time: 16:00
 */
//不使用MobileApi 中间件
$router->group(['namespace' => 'Api'], function () use ($router) {
    $router->post('order_info/save_line_info', 'OrderInfoController@saveOrderLine'); // 保存运货单线路
    $router->post('order_info/save_consign_order', 'OrderInfoController@saveConsignOrder'); // 保存装车单
    $router->post('order_info/remove_delivery', 'OrderInfoController@removeDelivery'); // 从装车单中移除一张运货单
    $router->post('order_info/cancel_consign_order', 'OrderInfoController@cancelConsignOrder'); // 取消装车单
    $router->post('order_info/update_consign_car', 'OrderInfoController@updateConsignCar'); // 取消装车单
    $router->post('order_info/set_order_urgent', 'OrderInfoController@setOrderUrgent'); // 设置加急单
    $router->post('order_info/set_consign_urgent', 'OrderInfoController@setConsignUrgent'); // 设置加急单(排车)

    $router->post('order_info/save_agency_order', 'OrderInfoController@saveAgencyOrder'); // 省代出库





    $router->post('consign_fencing/insert_consign_fencing', 'ConsignFencingController@insertConsignFencing'); // 新增线路
    $router->post('consign_fencing/update_consign_fencing', 'ConsignFencingController@updateConsignFencing'); // 修改线路
    $router->post('consign_fencing/delete_consign_fencting', 'ConsignFencingController@deleteConsignFencting'); // 删除线路
    $router->post('consign_fencing/check_back_consign', 'ConsignFencingController@checkBackConsign'); // 验车
    $router->post('consign_fencing/consign_info', 'ConsignFencingController@consignInfo'); // 分仓验车装车单详情
    $router->post('consign_fencing/product_info', 'ConsignFencingController@productInfo'); // 分仓验车查看商品详情
    $router->post('consign_fencing/chang_consign_status', 'ConsignFencingController@changConsignStatus'); // CRM提交验车时调用
    // $router->post('consign_fencing/pull_check_list', 'ConsignFencingController@pullCheckList'); // CRM提交验车时调用
    $router->post('consign_fencing/get_product_info_detail', 'ConsignFencingController@getProductInfoDetail'); // CRM提交验车时调用
    // $router->post('consign_fencing/pull_check_list', 'ConsignFencingController@pullCheckList'); // CRM提交验车时调用  (禁用)
    $router->post('consign_fencing/get_check_wayill_info', 'ConsignFencingController@getCheckWayillInfo'); // 实时获取mp装车单的异常数据不插入tms
    $router->post('consign_fencing/get_check_consign_wayill_info', 'ConsignFencingController@getCheckConsignWayillInfo'); //保存装车单最后一次异常数据 插入tms
    $router->post('consign_fencing/get_check_consign_info_detail', 'ConsignFencingController@getCheckConsignInfoDetail'); //数据整理
    $router->post('consign_fencing/set_depart', 'ConsignFencingController@setDepart'); //设置已发车
    $router->post('consign_fencing/cancel_agency_order', 'ConsignFencingController@cancelAgencyOrder'); //三方出库取消

    $router->post('warehouse/save_warehouse_info', 'WarehouseController@saveWarehouseInfo'); // 添加仓库
    $router->post('warehouse/get_warehouse_collection_area_by_dealer_id', 'WarehouseController@getWarehouseCollectionAreaBydealerId'); // 根据省市区获取集货区

});


$router->get('/log/log', 'TestController@getLog');