<?php

namespace App\Controllers;

use App\DAO\OrdenCompraDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class OrdenCompraController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getOrdenCompra($id)
    {
        return OrdenCompraDAO::getOrdenCompra($id);
    }
    
    public function getPedidosRevisados()
    {
        return OrdenCompraDAO::getPedidosRevisados();
    }

    public function saveOrdenCompra(Request $req)
    {
        return OrdenCompraDAO::saveOrdenCompra((object)$req->all());
    }
    
}
