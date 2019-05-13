<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 21:44
 */

//不使用MobileApi 中间件
$router->group(['prefix' => 'api/v1.0','namespace' => 'Mobile\V1_0'], function () use ($router) {
    $router->post('login/login', 'LoginController@login'); // 发起登录

    /************************************集货相关*****************************************/
    $router->post('goods_collection/get_collection_list', 'GoodsCollectionController@getCollectionList');//集货单列表
    $router->post('goods_collection/get_collection_detail', 'GoodsCollectionController@getCollectionDetail');//集货单详情
    $router->post('goods_collection/add_collection', 'GoodsCollectionController@addCollection');//新建集货单
    $router->post('goods_collection/collection_finish', 'GoodsCollectionController@collectionFinish');//集货完成
    $router->post('goods_collection/get_collection_item', 'GoodsCollectionController@getCollectionItem');//集货完成
    $router->post('goods_collection/get_collection_order_on', 'GoodsCollectionController@getCollectionOrderOn');//获取集货号
    $router->post('goods_collection/get_warehousename_by_collectioncode', 'GoodsCollectionController@getWarehouseNameByCollectionCode');//获取集货线路

    /*************************************用户相关****************************************/
    $router->post('user/get_user_detail', 'UserController@getUserDetail');//获取用户详细信息
    
    /*************************************出库入库****************************************/
    $router->post('dispense/verify_dispense_order', 'DispenseController@verifyDispenseOrder');//扫描入库
    $router->post('dispense/verify_agent_platform_order', 'DispenseController@verifyAgentPlatformOrder');//扫描入库
    $router->post('dispense/get_dispense_detail', 'DispenseController@getDispenseDetail');//获取出库单详情
    $router->post('dispense/get_dispense_order_list', 'DispenseController@getDispenseOrderList');//获取出库列表
    $router->post('dispense/save_dispense_order', 'DispenseController@saveDispenseOrder');//保存出库单
    $router->post('dispense/dispense_finish', 'DispenseController@dispenseFinish');//完成出库
    $router->post('dispense/get_dryline_list', 'DispenseController@getDrylineList');//干线报表
    $router->post('dispense/get_warehouse_list', 'DispenseController@getWarehouseList');//分仓报表
    $router->post('dispense/get_order_address_by_order', 'DispenseController@getOrderAddressByOrder');//分仓报表

    /*************************************分仓入库***************************************/
    $router->post('dispense/get_box_detail', 'DispenseController@getBoxDetail');//入库明细
    $router->post('dispense/car_arrive', 'DispenseController@carArrive');//扫描封车确认到达

    $router->post('agency/get_agency_info', 'AgencyOrderController@getAgencyInfo');//省代分仓到达
    $router->post('agency/get_supplier_list', 'AgencyOrderController@getSupplierList');//获取所有供应商
    $router->post('agency/verify_agent_order', 'AgencyOrderController@verifyAgentOrder');//省代收货
    $router->post('agency/rollback_agency_order', 'AgencyOrderController@rollbackAgencyOrder');//省代退回
    $router->post('agency/save_bind_number', 'AgencyOrderController@saveBindNumber');//修改分仓集货号
    $router->post('agency/save_agency_over', 'AgencyOrderController@saveAgencyOver');//收货结束
    $router->post('agency/get_want_send_express', 'AgencyOrderController@getWantSendExpress');//过去要发快递单据
    $router->post('agency/print_express_documents', 'AgencyOrderController@printExpressDocuments');//打印快递面单
    $router->post('agency/over_express_print', 'AgencyOrderController@overExpressPrint');//打印结束
    $router->post('agency/get_express_hand_out', 'AgencyOrderController@getExpressHandOut');//获取即将出库的快递单
    $router->post('agency/verify_express_out', 'AgencyOrderController@verifyExpressOut');//出库交接
    $router->post('agency/finish_express_out', 'AgencyOrderController@finishExpressOut');//出库交接完成
    $router->post('agency/sacn_express_box', 'AgencyOrderController@sacnExpressBox');//快递箱号扫描
    $router->post('agency/hand_pull_sm_order', 'AgencyOrderController@handPullSmOrder');//手工推单
    $router->post('agency/distribute_now', 'AgencyOrderController@distributeNow');//请求TS立即推单  
    $router->post('agency/get_afters_info_check_by_number', 'AgencyOrderController@getAftersInfoCheckByNumber');//根据快递单号。客户手机号 服务单号 获取验货信息
    $router->post('agency/update_afters_info', 'AgencyOrderController@updateAftersInfo');//更新redis中的验货信息

    /*************************************仓库*******************************************/
    $router->post('warehouse/get_warehouse_all', 'WarehouseController@getWarehouseAll');//获取所有仓库

    /*************************************分仓发货*******************************************/
    $router->post('consign/get_consign_list', 'ConsignOrderController@listConsignOrder');//转车单列表
    $router->post('consign/get_consign_detail', 'ConsignOrderController@getConsignOrderDetail');//获取装车单明细
    $router->post('consign/verify_waybill_box', 'ConsignOrderController@verifyWaybillBox');//扫码箱码装车
    $router->post('consign/finish_consign_order', 'ConsignOrderController@finishLoadConsign');//完成装车
    $router->post('consign/consign_box_check', 'ConsignOrderController@consignBoxCheck');//分仓出库箱子验证
    $router->post('consign_order/verify_box_back', 'ConsignOrderController@verifyBoxBack');//扫描箱号获取改送信息

    /*************************************物流*******************************************/
    $router->post('transport/get_transport_all', 'TransportController@getTransportAll');//获取物流承运公司

    /*************************************验车*******************************************/
    $router->post('consign_order/consign_order_list', 'ConsignOrderController@consignOrderList');//获取验车单列表
    $router->post('consign_order/consign_info', 'ConsignOrderController@consignInfo');//获取验车单详情
    $router->post('consign_order/get_check_consign_info', 'ConsignOrderController@getCheckConsignInfo');//获取验车单详情
    $router->post('consign_order/product_info', 'ConsignOrderController@productInfo');//查看商品详情
    $router->post('consign_order/verify_back', 'ConsignOrderController@verifyBack');//获取验车扫描信息
    $router->post('consign_order/quick_check_consign','ConsignOrderController@quickCheckConsign');  //快速验车
    $router->post('consign_order/check_consign_data','ConsignOrderController@checkConsignData');    //核实验车数据
    $router->post('consign_order/check_consign_finish','ConsignOrderController@checkConsignFinish'); //完成验车
    $router->post('consign_order/chang_consign_status','ConsignOrderController@changConsignStatus'); //申请验车
    $router->post('consign_order/scan_code_check_waybill','ConsignOrderController@scanCodeCheckWaybill'); //扫码验车
    $router->post('consign_order/get_waybill_detail','ConsignOrderController@getWaybillDetail'); //运输详情

    /*************************************其他*******************************************/
    $router->post('goods_collection/check_box', 'GoodsCollectionController@checkBox');//箱子验证
    $router->post('dispense/in_check_box', 'DispenseController@inCheckBox');//入库箱子验证
    $router->post('warehouse/get_transport_info', 'WarehouseController@getTransportInfo');//根据仓库code 获取司机信息
});
