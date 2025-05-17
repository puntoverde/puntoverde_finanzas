<?php

$router->group(['prefix'=>'categoria-producto'],function() use($router){

    //categorias
    $router->get('','CategoriaController@getCategorias');
    $router->get('/{id:[0-9]+}','CategoriaController@findCategoria');
    $router->post('','CategoriaController@insertCategoria');
    $router->put('/{id:[0-9]+}','CategoriaController@updateCategoria');

    //sub-categoria
    $router->get('/sub/{id:[0-9]+}','CategoriaController@getSubCategoria');
    $router->post('/sub/{id:[0-9]+}','CategoriaController@insertSubCategoria');
    
    //sub-sub-categoria
    $router->get('/subsub/{id:[0-9]+}','CategoriaController@getSubSubCategoria');
    $router->post('/subsub/{id:[0-9]+}','CategoriaController@insertSubSubCategoria');
});