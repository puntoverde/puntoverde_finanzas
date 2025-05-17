<?php

$router->group(['prefix'=>'requisicion'],function() use($router){

    $router->get('/solicita-revisa-aprueba/{id:[0-9]+}','RequisicionController@getPersonaSolicitaRevisaAprueba');
    
    $router->get('/all','RequisicionController@getRequisiciones');

    $router->get('','RequisicionController@getProductos');
    
    $router->get('/departamento-colaborador','RequisicionController@getDepartamentoAndColaboradores');
    
    $router->get('/{id:[0-9]+}','RequisicionController@findProducto');
    
    $router->post('','RequisicionController@crearRequisicion');

    $router->get('/detalle/{id:[0-9]+}','RequisicionController@getDetalleRequisicion');    
    
    $router->get('/revision/{id:[0-9]+}','RequisicionController@getRevisionRequisicion');
    
    $router->put('/revision/producto/{id:[0-9]+}','RequisicionController@rechazarProductoRequisicionRevision');
    
    $router->put('/revision/finalizar/{id:[0-9]+}','RequisicionController@terminarRevision');
    
    $router->put('/revision/cancelar/{id:[0-9]+}','RequisicionController@cancelarRequisicion');
    
    $router->get('/aprobar/{id:[0-9]+}','RequisicionController@getAprobarRequisicion');
    
    $router->put('/aprobar/producto/{id:[0-9]+}','RequisicionController@rechazarProductoRequisicionAprobacion');
    
    $router->put('/aprobar/finalizar/{id:[0-9]+}','RequisicionController@terminarAprovacion');
       
    $router->put('/aprobar/cancelar/{id:[0-9]+}','RequisicionController@cancelarRequisicion');
    
   
    
    // $router->get('/solicita-revisa-aprueba/{id:[0-9]+}','RequisicionController@getPersonaSolicitaRevisaAprueba');
    
    $router->get('/presentacion-productos/{id:[0-9]+}','RequisicionController@getPresentacionesProducto');
    
    $router->get('/marca-asignar/{id:[0-9]+}','RequisicionController@getMarcaAsignar');
    
    
    $router->get('/all-detalle','RequisicionController@getAllRequisicionByColaborador');
    
    $router->get('/{id:[0-9]+}/existente-detalle','RequisicionController@getDetalleRequisicionExistente');
    
    $router->post('/{id:[0-9]+}/existente-create','RequisicionController@createRequisicionLigada');
});