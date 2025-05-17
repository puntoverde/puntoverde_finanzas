<?php

$router->group(['prefix'=>'proveedor'],function() use($router){
    $router->get('','ProveedorController@getProveedores');
    $router->get('/{id:[0-9]+}','ProveedorController@getProveedorById');
    $router->get('/categorias/{id:[0-9]+}','ProveedorController@getCategoriasProvedor');
    $router->get('/search','ProveedorController@getProveedorByParameters');
    $router->get('/productos-categorias/{id:[0-9]+}/{id_prod:[0-9+]}','ProveedorController@getProductoCategoriaProveedor');
    $router->post('','ProveedorController@createProveedor');
    $router->put('/{id:[0-9]+}','ProveedorController@updateProveedor');
    $router->post('/{id:[0-9]+}/categoria','ProveedorController@addCategoria');
    $router->post('/{id:[0-9]+}/producto','ProveedorController@addProductos');
});