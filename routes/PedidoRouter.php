<?php

$router->group(['prefix'=>'pedido'],function() use($router){
    
    $router->get('','PedidoController@getPedidos');
    
    $router->post('','PedidoController@crearPedido');
    
    $router->get('/{id:[0-9]+}/producto','PedidoController@getProductosPedido');
    
    $router->get('/producto-requisicion-proveedor/{id:[0-9]+}','PedidoController@findProductoDisponiblesRequisicion');
    
    $router->post('/producto-requisicion-proveedor/{id:[0-9]+}','PedidoController@agregarProducto');

    $router->put('/eliminar-producto/{id:[0-9]+}','PedidoController@eliminarProducto');
    
 
    
   //esta es la revision del pedido cuando llegan los productos antes de la orden de compra

    $router->get('/sin-revisar','PedidoController@pedidosSinRevisar');
    
    $router->get('/revision/{id:[0-9]+}','PedidoController@pedidoRevision');

    $router->post('/revision/aceptar-producto','PedidoController@aceptarProductoPedido');
    
    $router->post('/revision/rechazar-producto','PedidoController@rechazarProductoPedido');
    
    $router->post('/revision/cambio-producto','PedidoController@cambioProductoPedido');
    
    $router->put('/revision/cancelar/{id:[0-9]+}','PedidoController@cancelarPedido');

    $router->put('/revision/{id:[0-9]+}/finalizar','PedidoController@finalizarRevisionPedido');

    $router->post('/revision/{id:[0-9]+}/nota','PedidoController@agregarNotaPedido');

    $router->post('/revision/{id:[0-9]+}/factura','PedidoController@agregarFacturaPdfXml');

    $router->put('/revision/proveedor','PedidoController@cambiarProveedor');
    
    
    $router->get('/detalle-productos-libres','PedidoController@detalleProductosLibresParaPedido');
    
    
    $router->put('/cambio-marca','PedidoController@cambiarMarcaProductoPedidoRevision');

});