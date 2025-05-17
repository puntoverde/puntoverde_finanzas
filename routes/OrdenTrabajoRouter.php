<?php

$router->group(['prefix'=>'orden-trabajo'],function() use($router){
    $router->get('','OrdenTrabajoController@getAllOrdenesTrabajo');
    $router->get('/{id:[0-9]+}','OrdenTrabajoController@getOrdenTrabajoById');
    $router->post('','OrdenTrabajoController@createOrdenTrabajo');
    $router->put('/{id:[0-9]+}','OrdenTrabajoController@updateOrdenTrabajo');
    $router->get('/departamento/{id:[0-9]+}','OrdenTrabajoController@getDepartamentoColaborador');
    $router->get('/departamentos','OrdenTrabajoController@getDepartamentosDisponibles');    
    $router->put('/cancel-rechazar/{id:[0-9]+}','OrdenTrabajoController@updateCancelarRechazar'); 
    $router->put('/iniciar/{id:[0-9]+}','OrdenTrabajoController@iniciarOrdenTrabajo'); 
    
    
    $router->get('/{id:[0-9]+}/actividad','OrdenTrabajoController@getActividadOrdenTrabajo'); 
    $router->get('/actividad/{id:[0-9]+}','OrdenTrabajoController@getActividadOrdenByIdTrabajo'); 
    $router->post('/{id:[0-9]+}/actividad','OrdenTrabajoController@createActividadOrdenTrabajo'); 
    $router->delete('/actividad/{id:[0-9]+}','OrdenTrabajoController@deleteActividadOrdenTrabajo'); 
    $router->put('/actividad/{id:[0-9]+}/terminar','OrdenTrabajoController@terminarActividadOrdenTrabajo'); 
    
    $router->get('/reporte-departamentos','OrdenTrabajoController@reporteOrdenTrabajoDepartamentos'); 
    $router->get('/reporte[/{id:[0-9]+}]','OrdenTrabajoController@reporteOrdenTrabajo'); 
    $router->get('/reporte/{id:[0-9]+}/actividades','OrdenTrabajoController@OrdenTrabajoActividades'); 
    
    $router->get('/tipos','OrdenTrabajoController@getTipoOrdenTrabajo'); 
    $router->get('/tipos-actividad','OrdenTrabajoController@getTipoOrdenTrabajoActividad'); 
    
    
    $router->get('/actividades-revision','OrdenTrabajoController@getActividadesByDepartamento'); 
    $router->get('/actividades-revision-pendientes','OrdenTrabajoController@getFechasActividadesPendientes'); 
    $router->put('/actividades-revision-finalizar','OrdenTrabajoController@terminarActividadByDepartamento'); 
    $router->get('/actividades-revision-reporte','OrdenTrabajoController@geetActividadesReporte'); 
    
    $router->post('/actividades-revision-observacion','OrdenTrabajoController@createObservacionActividad'); 
    
    $router->get('/reporte-socios','OrdenTrabajoController@getReporteOrdenesTrabajoSocios'); 
    $router->get('/reporte-interno','OrdenTrabajoController@getReporteOrdenesTrabajoInterno'); 
    
    $router->get('/clasificacion','OrdenTrabajoController@getClasificacionOrdenTrabajo'); 
    
    $router->post('/upload-foto','OrdenTrabajoController@guardarCancelarFoto'); 
    $router->put('/cancelar-foto','OrdenTrabajoController@CancelarFoto'); 
    $router->get('/foto','OrdenTrabajoController@getViewFotoOrdenTrabajo'); 
    
});