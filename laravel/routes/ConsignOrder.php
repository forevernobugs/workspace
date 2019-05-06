<?php

Route::group(['middleware' => ['widget'],'namespace' => 'ConsignOrder'], function ($router) {
    //获取分仓排车信息
    $router->post('consign_order/get_box_info','ConsignOrderController@getBoxInfo');
    $router->post('consign_order/get_dispense_list','ConsignOrderController@getDispenseList');	//分仓收货单信息
    $router->post('consign_order/get_dispense_detail','ConsignOrderController@getDispenseDetail');	//分仓收货单详情
    $router->post('consign_order/get_consign_list','ConsignOrderController@getConsignList');	//分仓发货列表
    $router->post('consign_order/get_consign_detail','ConsignOrderController@getConsignDetail');	//分仓发货详情
    $router->post('consign_order/get_statistical_info','ConsignOrderController@getStatisticalInfo');	//分仓统计信息
    $router->post('consign_order/get_dispense_transport_detail','ConsignOrderController@getDispenseTransportDetail');	//分仓运输统计收货详情
    $router->post('consign_order/get_consign_transport_detail','ConsignOrderController@getConsignTransportDetail');	//分仓运输统计发货详情
    $router->post('consign_order/consign_dirver_list','ConsignOrderController@consignDirverList');  //分仓司机交接表
    $router->post('consign_order/ab_normal_list','ConsignOrderController@abNormalList');    //箱子异常日志

    $router->post('consign_order/check_consign_list','ConsignOrderController@checkConsignList');    //验车列表
    $router->post('consign_order/check_consign_detail','ConsignOrderController@checkConsignDetail');    //验车详情
    $router->post('consign_order/check_consign_data','ConsignOrderController@checkConsignData');    //处理异常
    $router->post('consign_order/do_consign_data','ConsignOrderController@doConsignData');  //调整
    $router->post('consign_order/check_normal_finish','ConsignOrderController@checkNormalFinish');  //异常处理完成
    $router->post('consign_order/lose_consign_product','ConsignOrderController@loseConsignProduct');    //缺失的商品
    $router->post('consign_order/product_replenish','ConsignOrderController@productReplenish'); //缺失的商品补货
    $router->post('consign_order/product_compensate','ConsignOrderController@productCompensate');   //缺失的商品赔付
    $router->post('consign_order/get_product_info_detail','ConsignOrderController@getProductInfoDetail');	//缺失的商品赔付
});