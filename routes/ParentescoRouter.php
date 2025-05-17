<?php

$router->group(['prefix'=>'parentescos'],function() use($router){
    $router->get('','ParentescoController@getParentescos');
});