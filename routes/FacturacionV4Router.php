<?php

$router->group(['prefix'=>'facturacion-v4'],function() use($router){

    $router->get('/{id:[0-9]+}','DatosFacturacionController@getDatosFacturacion');
    $router->get('/socios','DatosFacturacionController@getSocios');
    
    $router->post('/factura/{id}','FacturacionV4Controller@createFactura');
    $router->post('/datos-factura/{id}','FacturacionV4Controller@createDatosFactura');
    $router->post('/publico-general','FacturacionV4Controller@createFacturaPublicoGeneral');
    
    $router->put('/{id:[0-9]+}','FacturacionV4Controller@updateDatosFacturacion');
    $router->delete('/{id:[0-9]+}','DatosFacturacionController@bajaDatosFacturacion');
    $router->put('/cuota','DatosFacturacionController@verificarDatosFacturacion');
    $router->get('/regimen-fiscales','DatosFacturacionController@getRegimenFiscal');
    
    $router->get('/socios-pg/{id:[0-9]+}','FacturacionV4Controller@getSociosPG');

});