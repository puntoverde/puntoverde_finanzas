<?php
$router->group(['prefix'=>'descuentos'],function() use($router){
    
    $router->get('/cargos','DescuentoController@getCargos');
    
    $router->get('/cuotas-obligatorias','DescuentoController@cuotasObligatorias');
    
    $router->get('/socio-aplica','DescuentoController@sociosAplicaCuota');
    
    $router->get('/dueno-aplica','DescuentoController@duenoAplicaCuota');

    $router->post('','DescuentoController@aplicarDescuento');
    
    $router->post('/programados','DescuentoController@DescuentosProgramados');

    //$router->get('/{id:[0-9]+}','AccionistaController@getAccionista');
});