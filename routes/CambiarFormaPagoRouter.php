<?php

$router->group(['prefix'=>'cambiar-forma-pago'],function() use($router){
    $router->get('','CambiarFormaPagoController@consultarCargo');
    $router->post('','CambiarFormaPagoController@eliminarPago');
    $router->get('/pagos/{id:[0-9]+}','CambiarFormaPagoController@getFormasPagoAsignada');
    $router->get('/forma-pago','CambiarFormaPagoController@getFormasPago');
    $router->put('/forma-pago/{id:[0-9]+}','CambiarFormaPagoController@updateFormapago');
    $router->put('/monto/{id:[0-9]+}','CambiarFormaPagoController@updateMonto');
    $router->post('/forma-pago/{id:[0-9]+}','CambiarFormaPagoController@addFormaPago');
    $router->delete('/forma-pago/{id:[0-9]+}','CambiarFormaPagoController@deleteFormaPago');
    $router->put('/cargo-monto/{id:[0-9]+}','CambiarFormaPagoController@updateMontoCargo');
    $router->get('/cuotas','CambiarFormaPagoController@getCuotas');
    $router->put('/cuotas/{id:[0-9]+}','CambiarFormaPagoController@updateCuota');
    
    $router->get('/cajeros','CambiarFormaPagoController@getCajeros');
    $router->put('/cajero/{id:[0-9]+}','CambiarFormaPagoController@updateCajero');
    $router->put('/fecha/{id:[0-9]+}','CambiarFormaPagoController@updateFecha');
});