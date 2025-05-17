<?php

$router->group(["prefix"=>"reporte-inscripcion"],function() use ($router){

   
      //get datos view en tablas
      $router->get('','ReporteInscripcionesController@getDatosInscripciones');
      $router->get('/cuotas','ReporteInscripcionesController@getCuotasInscripcion');
     
      //excel
      $router->get('/nomina/excel','ReporteInscripcionesController@getXLSNomina');
      
});
