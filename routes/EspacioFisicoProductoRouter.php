<?php

$router->group(['prefix'=>'espacio-fisico-producto'],function() use($router){
    $router->get('/{id:[0-9]+}','EspacioFisicoProductoController@getProductosDisponibles');
    $router->get('/pedidos-revisados','EspacioFisicoProductoController@getProductosActivosAsignados');
    $router->post('','EspacioFisicoProductoController@saveAsignacionProductoEspacioFisico');
});