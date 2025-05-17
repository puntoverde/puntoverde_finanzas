<?php

$router->group(['prefix'=>'cuotas'],function() use($router){
    $router->get('','CuotaController@getCuotas');
    $router->get('/{id}','CuotaController@getCuotaById');
    $router->post('','CuotaController@createCuota');
    $router->put('/{id}','CuotaController@updateCuota');
    $router->delete('/{id}','CuotaController@deleteCuota');
});