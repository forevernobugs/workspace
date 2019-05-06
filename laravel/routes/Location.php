<?php

$router->post('location/get_provinces', 'LocationController@getProviences');
$router->post('location/get_regions', 'LocationController@getRegions');
$router->post('location/get_city', 'LocationController@getCity');