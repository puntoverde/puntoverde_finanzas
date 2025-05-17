<?php
$router->group(['prefix'=>'accionistas'],function() use($router){
    
    $router->get('','AccionistaController@getAccionistas');

    $router->get('/{id:[0-9]+}','AccionistaController@getAccionista');

    $router->post('','AccionistaController@setAccionista');

    $router->put('/{id:[0-9]+}','AccionistaController@updateAccionista');

    $router->put('/cambio','AccionistaController@accionistaChange');

    $router->post('/upload-foto','AccionistaController@uploadFoto');

    $router->get('/foto','AccionistaController@getViewFoto');

    $router->put('/{id:[0-9]+}/foto','AccionistaController@addFoto');
});