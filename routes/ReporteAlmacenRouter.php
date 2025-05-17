<?php

$router->group(["prefix"=>"reporte-almacen"],function() use ($router){

      //get datos view en tablas
      $router->get('/entrada','ReporteAlmacenController@reporteAlmacenEntrada');
      $router->get('/requisicion','ReporteAlmacenController@reporteAlmacenRequisicion');
      $router->get('/pedido','ReporteAlmacenController@reportePedidoAlmacen');
      //se agrega lo de salida
      $router->get('/salida','ReporteAlmacenController@reporteAlmacenSalida');
      
});
