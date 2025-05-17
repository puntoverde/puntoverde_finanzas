<?php

$router->group(['prefix'=>'unidad-medida-producto'],function() use($router){

    $router->get('','UnidadMedidaProductoController@getUnidadMedida');
    $router->get('/{id:[0-9]+}','UnidadMedidaProductoController@findCategoria');
    $router->post('','UnidadMedidaProductoController@insertCategoria');
    $router->put('/{id:[0-9]+}','UnidadMedidaProductoController@updateCategoria');

    $router->get('/sub/{id:[0-9]+}','UnidadMedidaProductoController@getSubCategoria');
});