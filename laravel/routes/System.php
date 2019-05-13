<?php 

//加载挂件
Route::group(['middleware' => ['widget']], function ($router) {
    $router->post('system/menu', 'System\SystemController@menu');
    $router->post('system/user_role', 'System\SystemController@userRole');
    $router->post('system/user_list', 'System\SystemController@userList');
    $router->post('system/structure_list', 'System\SystemController@structureList');
});

// 不加载挂件
$router->post('system/menu_edit', 'System\SystemController@menuEdit');
$router->post('system/menu_save', 'System\SystemController@menuSave');
$router->post('system/assign_menu', 'System\SystemController@assignMenu');
$router->post('system/assign_menu_save', 'System\SystemController@assignMenuSave');
$router->post('system/user_edit', 'System\SystemController@userEdit');
$router->post('system/user_save', 'System\SystemController@userSave');
$router->post('system/update_password', 'System\SystemController@updatePassword');
$router->post('system/structure_edit', 'System\SystemController@structureEdit');
$router->post('system/structure_save', 'System\SystemController@structureSave');
