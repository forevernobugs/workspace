<?php

Route::group(['middleware' => ['widget']], function ($router) {
    //获取集货单列表
    $router->post(
        'centerwarehouse/goods_collection_list',
        'CenterWarehouse\GoodsCollectionController@GoodsCollectionList'
    );
    //获取集货单详情
    $router->post(
        'centerwarehouse/goods_collection_detail_list',
        'CenterWarehouse\GoodsCollectionController@GoodsCollectionDetailList'
    );
    //获取装车单列表
    $router->post(
        'centerwarehouse/dispense_order_list',
        'CenterWarehouse\DispenseOrderController@DispenseOrderList'
    );
    //获取装车单详情
    $router->post(
        'centerwarehouse/dispense_order_detail_list',
        'CenterWarehouse\DispenseOrderController@DispenseOrderDetailList'
    );
});