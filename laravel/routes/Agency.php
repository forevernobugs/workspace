<?php

Route::group(['middleware' => ['widget'],'namespace' => 'Agency'], function ($router) {
	$router->post('agency/get_agency_list','AgencyController@getAgencyList');//代理商发货日
	$router->post('agency/save_out_time_day','AgencyController@saveOutTimeDay');//修改发货日
	$router->post('agency/get_agency_confirm_out_list','AgencyController@getAgencyConfirmOutList');//省代出库确认列表
	$router->post('agency/save_out_agency_info','AgencyController@saveOutAgencyInfo');//修改出库信息
	$router->post('agency/confirm_agency_out','AgencyController@confirmAgencyOut');//确认出库
	$router->post('agency/print_confirm_out','AgencyController@printConfirmOut');//打印确认出库
	$router->post('agency/save_after_express_info','AgencyController@saveAfterExpressInfo');//分仓售后直发确认
	$router->post('agency/save_difference_reasion','AgencyController@saveDifferenceReasion');//修改差异原因
	$router->post('agency/save_recive_express_after','AgencyController@saveReceiveExpressAfter');//分仓直发确认收货
	$router->post('agency/save_recive_express_after_error_exprot','AgencyController@saveReceiveExpressAfterErrorExport');//分仓直发失败导出
    $router->post('agency/confirm_goods_list','AgencyController@confirmGoodsList');//确认出库列表
});