<?php

$router->group(['prefix'=>'cancelar-pago'],function() use($router){
    $router->get('','CancelarPagoController@consultarCargo');
    $router->post('','CancelarPagoController@eliminarPago');
});