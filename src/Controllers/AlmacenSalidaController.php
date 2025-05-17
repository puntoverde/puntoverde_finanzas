<?php

namespace App\Controllers;

use App\DAO\AlmacenSalidaDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class AlmacenSalidaController extends Controller
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
        return AlmacenSalidaDAO::getPedidosRalizadosSinAlmacen();
    }
    
    public function getProductosAlmacenById($id)
    {
        return AlmacenSalidaDAO::getProductosAlmacenById($id);
    }

    public function saveEntradaAlmacenPedido($id)
    {
        return AlmacenSalidaDAO::saveEntradaAlmacenPedido($id);
    }

    public function getProductosAlmacen(Request $req)
    {
        return AlmacenSalidaDAO::getProductosAlmacen((object)$req->all());
    }

    public function saveSalidaAlmacen(Request $req,$id)
    {
        return AlmacenSalidaDAO::saveSalidaAlmacen($id,(object)$req->all());
    }

   
    
}
