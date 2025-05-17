<?php

$router->group(['prefix'=>'socios'],function() use($router){
    
    
    $router->get('/cambio','SocioController@getSociosChange');

    $router->get('','SocioController@getSociosByAccion');

    $router->get('/{id:[0-9]+}','SocioController@getSocio');

    $router->post('','SocioController@setSocio');

    $router->put('/{id:[0-9]+}','SocioController@updateSocio');

    $router->get('/posiciones','SocioController@getPocisionesSocios');

    $router->get('/posiciones_acciones','SocioController@getPosicionesByAccionAndClasificacion');

    $router->delete('/{id}','SocioController@bajaSocio');

    $router->put('/{id:[0-9]+}/params','SocioController@updateSocioParams');

    $router->get('/{id:[0-9]+}/documentos','SocioController@getDocumentos');

    $router->post('/upload-file','SocioController@uploadFile');

    $router->post('/{id:[0-9]+}/documento','SocioController@saveDocumento');

    $router->delete('/{id:[0-9]+}/documento','SocioController@deleteDocumento');

    $router->get('/{id:[0-9]+}/documento-file','SocioController@getDocumentoFile');

    $router->post('/upload-foto','SocioController@uploadFoto');

    $router->get('/foto','SocioController@getViewFoto');

        
});