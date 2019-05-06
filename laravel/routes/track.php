<?php

Route::group(['middleware' => ['widget']], function ($router) {
    $router->post('track/box_track', 'Track\TrackController@boxTrack');   //获取车辆列表
    $router->post('track/product_track', 'Track\TrackController@productTrack');   //车辆编辑页面
    $router->post('track/box_detail', 'Track\TrackController@boxDetail');   //查看箱子详情
    $router->post('track/product_box_detail', 'Track\TrackController@productBoxDetail');   //查看箱子详情
});