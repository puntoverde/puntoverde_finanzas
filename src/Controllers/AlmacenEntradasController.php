<?php

namespace App\Controllers;

use App\DAO\AlmacenEntradasDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class AlmacenEntradasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getPedidosRalizadosSinAlmacen()
    {
        return AlmacenEntradasDAO::getPedidosRalizadosSinAlmacen();
    }
    
    public function getProductosAlmacenById($id)
    {
        return AlmacenEntradasDAO::getProductosAlmacenById($id);
    }

    public function saveEntradaAlmacenPedido($id)
    {
        return AlmacenEntradasDAO::saveEntradaAlmacenPedido($id);
    }

    public function getProductosAlmacen(Request $req)
    {
        return AlmacenEntradasDAO::getProductosAlmacen((object)$req->all());
    }

    public function saveSalidaAlmacen(Request $req,$id)
    {
        return AlmacenEntradasDAO::saveSalidaAlmacen($id,(object)$req->all());
    }
    
}
