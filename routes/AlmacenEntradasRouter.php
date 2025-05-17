<?php

$router->group(['prefix'=>'almacen-entrada'],function() use($router){
    $router->get('/pedidos-libres','AlmacenEntradasController@getPedidosRalizadosSinAlmacen');
    $router->get('/producto-existencia/{id:[0-9]+}','AlmacenEntradasController@getProductosAlmacenById');
    $router->get('/productos-almacen','AlmacenEntradasController@getProductosAlmacen');
    $router->post('/{id:[0-9]+}','AlmacenEntradasController@saveEntradaAlmacenPedido');
    $router->post('/salida/{id:[0-9]+}','AlmacenEntradasController@saveSalidaAlmacen');
});