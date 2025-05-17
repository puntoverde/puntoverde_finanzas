<?php

$router->group(['prefix'=>'reporte-producto'],function() use($router){
    $router->get('','ReporteProductoController@reporteProductoRequisicion');
    $router->get('/revision','ReporteProductoController@reporteProductoRequisicionRevision');
    $router->get('/cuadricula','ReporteProductoController@reporteProductoCuadricula');
    $router->get('/cuadricula-detalle','ReporteProductoController@reporteProductoCuadriculaDetalle');
});