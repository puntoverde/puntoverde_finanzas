<?php

$router->group(['prefix'=>'marca-producto'],function() use($router){

    $router->get('','MarcaProductoController@getMarcaProducto');
    $router->get('/{id:[0-9]+}','MarcaProductoController@findCategoria');
    $router->post('','MarcaProductoController@insertCategoria');
    $router->put('/{id:[0-9]+}','MarcaProductoController@updateCategoria');

    $router->get('/sub/{id:[0-9]+}','MarcaProductoController@getSubCategoria');
    
    $router->get('/find-name','MarcaProductoController@getMarcaByNombre');
});