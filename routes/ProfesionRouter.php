<?php

$router->group(['prefix'=>'profesiones'],function() use($router){
    $router->get('','ProfesionController@getProfesiones');
});