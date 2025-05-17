<?php

$router->group(['prefix'=>'reporte-pago-concepto'],function() use($router){
    $router->get('/eliminar','reportePagoConceptoController@eliminarPago');
	$router->get('/pagos','reportePagoConceptoController@consultarPagos');
	$router->get('/conceptos','reportePagoConceptoController@consultarConceptosCargados');
	$router->get('/cargos-pagos','reportePagoConceptoController@consultarCargosPagos');
	$router->get('/cajero','reportePagoConceptoController@consultarCajero');
	$router->get('/cajero-pagos','reportePagoConceptoController@consultarPagosConceptosCajera');
	$router->get('/descuentos','reportePagoConceptoController@consultarDescuentos');
	$router->get('/exportar','reportePagoConceptoController@GenerarExcelExportarFacturacion');    

	$router->get('/pagos-deporte','reportePagoConceptoController@consultarPagosDeporte');
	$router->get('/conceptos-deporte','reportePagoConceptoController@consultarConceptosCargadosDeportes');
});