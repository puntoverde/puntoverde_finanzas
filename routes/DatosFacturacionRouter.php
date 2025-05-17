<?php

$router->group(['prefix'=>'datos-facturacion'],function() use($router){
    $router->get('/{id:[0-9]+}','DatosFacturacionController@getDatosFacturacion');
    $router->get('/socios','DatosFacturacionController@getSocios');
    $router->post('/{id:[0-9]+}','DatosFacturacionController@createDatosFacturacion');
    $router->put('/{id:[0-9]+}','DatosFacturacionController@updateDatosFacturacion');
    $router->delete('/{id:[0-9]+}','DatosFacturacionController@bajaDatosFacturacion');
    $router->put('/cuota','DatosFacturacionController@verificarDatosFacturacion');
    $router->get('/regimen-fiscales','DatosFacturacionController@getRegimenFiscal');
});