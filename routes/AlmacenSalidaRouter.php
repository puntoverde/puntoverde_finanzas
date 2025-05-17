<?php

$router->group(['prefix'=>'almacen-salida'],function() use($router){
    // $router->get('/pedidos-libres','AlmacenSalidaController@getProductoSalida');
    // $router->get('/producto-existencia/{id:[0-9]+}','AlmacenSalidaController@realizarSalidaProducto');

    $router->get('/pedidos-libres','AlmacenSalidaController@getPedidosRalizadosSinAlmacen');
    $router->get('/producto-existencia/{id:[0-9]+}','AlmacenSalidaController@getProductosAlmacenById');
    $router->get('/productos-almacen','AlmacenSalidaController@getProductosAlmacen');
    $router->post('/{id:[0-9]+}','AlmacenSalidaController@saveEntradaAlmacenPedido');
    $router->post('/salida/{id:[0-9]+}','AlmacenSalidaController@saveSalidaAlmacen');
});