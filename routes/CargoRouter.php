<?php
$router->group(['prefix'=>'cargo'],function() use($router){
    
    $router->get('','CargoController@getCargo');

    $router->delete('/{cve}','CargoController@getDeleteCargo');

    $router->get('/reporte','CargoController@getDeleteCargoReporte');

    //obtiene el estatus de la accion que pertenece el cargo
    $router->get('/{id:[0-9]+}/estatus','CargoController@getEstatusAccionByCargo');
    

    
});

