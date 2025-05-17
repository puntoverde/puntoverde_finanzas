<?php

$router->group(['prefix'=>'almacen'],function() use($router){
    
    $router->get('/corte','AlmacenController@getCortesAlmacen');
    $router->post('/corte','AlmacenController@crearCorteAlmacen');
    $router->get('/corte-detalle/{id}','AlmacenController@getDetalleCorteAlmacen');

    // $router->post('','AlmacenController@getAcciones');
    // $router->get('/{id}','AlmacenController@getAccionById');
    // $router->put('/{id}','AlmacenController@updateAccion');
});