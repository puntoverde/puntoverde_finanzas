<?php

$router->group(['prefix'=>'edificio'],function() use($router){
    $router->get('','EdificioController@getEdificios');
    $router->get('/{id:[0-9]+}/espacios','EdificioController@getEspacioFisicoByEdificio');
    $router->get('/espacios','EdificioController@getEspacioFisicoFull');
});