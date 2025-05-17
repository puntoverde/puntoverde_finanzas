<?php

$router->group(["prefix"=>"sat-xml"],function() use ($router){

      //subir xml
      $router->post('','SatXMLController@leerXML');
      $router->post('/nomina','SatXMLController@leerXMLNomina');
      $router->post('/emitido','SatXMLController@leerEmitidoXML');

      //get datos view en tablas
      $router->get('','SatXMLController@getDataXML');
      $router->get('/conceptos/{id}','SatXMLController@getConceptosXML');

      $router->get('/complementos','SatXMLController@getDataXMLComplementarios');
      $router->get('/conceptos/{id}/complementos','SatXMLController@getConceptosXMLComplementarios');

      $router->get('/nomina','SatXMLController@getDataNominaXML');
      $router->get('/nomina/extras/{id}','SatXMLController@getDatosNominaExtraXML');
      
      $router->get('/emitido','SatXMLController@getDatosEmitidoXML');
      $router->get('/emitido/conceptos/{id}','SatXMLController@getEmitidoConceptosXML');


      //excel
      $router->get('/nomina/excel','SatXMLController@getXLSNomina');

      $router->get('/excel','SatXMLController@getXLSFactura');

      $router->get('/complementos/excel','SatXMLController@getXLSFacturaComplementos');
      
      $router->get('/emitido/excel','SatXMLController@getXLSFacturaEmitida');

      $router->put('/recibida/{uuid}','SatXMLController@cancelarDocumento');
      $router->put('/complemento/{uuid}','SatXMLController@cancelarDocumentoComplemento');
      $router->put('/nomina/{uuid}','SatXMLController@cancelarDocumentoNomina');
      $router->put('/emitido/{uuid}','SatXMLController@cancelarDocumentoEmitido');

      
});
