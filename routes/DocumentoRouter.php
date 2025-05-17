<?php

$router->group(['prefix'=>'documentos'],function() use($router){
    
    $router->get('','DocumentoController@getDocumentos');

    $router->get('/{id}','DocumentoController@getDocumento');

    $router->post('','DocumentoController@setDocumento');

    $router->put('/{id}','DocumentoController@updateDocumento');

    $router->delete('/{id}','DocumentoController@deleteDocumento');

});