<?php

$router->group(['prefix'=>'pago'],function() use($router){
    $router->get('','PagoController@getPagos');
    $router->get('/{id}/cargos','PagoController@getCargosPago');
    $router->get('/{id}/factura','PagoController@getDatosFactura');
    $router->get('/datos-factura','PagoController@getListaDatosFacturacion');
    
    $router->post('','PagoController@setDatoFacturacion');
    $router->post('/cfactura','PagoController@setFactura');
    $router->post('/factura-publico-general','PagoController@setFacturaPublicoGeneral');
    $router->put('/{id:[0-9]+}/update','PagoController@updateDatoFacturacion');
    $router->put('/{id}','PagoController@updateFactura');
    $router->put('/{id}/update-factura','PagoController@updateFacturaComplete');


    $router->get('/forma-pago','PagoController@getFormaPago');
    $router->get('/uso-cfdi','PagoController@getUsoCfdi');
    $router->get('/socios-accion','PagoController@getSociosAccion');
    
    $router->delete('/{id}','PagoController@eliminarFactura');
    
    // $router->get('/vista','PagoController@getVista');
   
});