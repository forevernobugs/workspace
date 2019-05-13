<?php

Route::group(['middleware' => ['widget']], function ($router) {
    $router->post('warehouse/warehouse_list', 'Warehouse\WarehouseController@warehouseList');   //获取仓库列表
    $router->post('warehouse/warehouse_detail', 'Warehouse\WarehouseController@warehouseDetail');   //获取仓库详情
    $router->post('warehouse/warehouse_edit', 'Warehouse\WarehouseController@warehouseEdit');   //仓库编辑页面
    $router->post('warehouse/warehouse_update', 'Warehouse\WarehouseController@warehouseUpdate');   //仓库信息更新
    $router->post('warehouse/warehouse_save', 'Warehouse\WarehouseController@warehouseSave');   //仓库信息存储

    $router->post('warehouse/parameter_edit', 'Warehouse\WarehouseParameterController@parameterEdit');   //仓库参数编辑
    $router->post('warehouse/parameter_save', 'Warehouse\WarehouseParameterController@parameterSave');   //仓库参数存储
    $router->post('warehouse/parameter_list', 'Warehouse\WarehouseParameterController@parameterList');   //仓库参数列表
    $router->post('warehouse/parameter_update', 'Warehouse\WarehouseParameterController@parameterUpdate');   //仓库参数更新    
    $router->post('warehouse/parameter_delete', 'Warehouse\WarehouseParameterController@parameterDelete');   //仓库参数更新    
    $router->post('warehouse/get_parameter_value', 'Warehouse\WarehouseParameterController@getParameterValue');   //仓库参数更新    
});
