<?php

$router->group(['prefix'=>'orden-compra'],function() use($router){
    $router->get('/{id:[0-9]+}','OrdenCompraController@getOrdenCompra');
    $router->get('/pedidos-revisados','OrdenCompraController@getPedidosRevisados');
    $router->post('','OrdenCompraController@saveOrdenCompra');
});