<?php

$router->group(['prefix'=>'producto'],function() use($router){
    $router->get('','ProductoController@getProductos');
    $router->get('/search','ProductoController@getProductosByParameters');
    $router->get('/{id:[0-9]+}','ProductoController@findProducto');
    $router->post('','ProductoController@insertProducto');
    $router->put('/{id}','ProductoController@updateProducto');
    $router->post('/upload-foto','ProductoController@uploadFoto');
    $router->get('/foto','ProductoController@getViewFoto');
    $router->get('/detalle-nombre','ProductoController@getDetalleFormaNombre');
});