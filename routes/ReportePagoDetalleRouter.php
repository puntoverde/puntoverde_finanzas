<?php

$router->group(['prefix'=>'reporte-pago-detalle'],function() use($router){
    $router->get('','reportePagoDetalleController@consultarPagos');
	$router->get('/cajera','reportePagoDetalleController@consultarCajera');
    
});