<?php

$router->group(['prefix'=>'acciones'],function() use($router){
    $router->get('','AccionController@getAcciones');
    $router->get('/{id}','AccionController@getAccionById');
    $router->put('/{id}','AccionController@updateAccion');
});