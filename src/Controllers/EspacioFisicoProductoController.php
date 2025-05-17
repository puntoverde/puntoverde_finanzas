<?php

namespace App\Controllers;

use App\DAO\EspacioFisicoProductoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class EspacioFisicoProductoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getProductosDisponibles($id)
    {
        return EspacioFisicoProductoDAO::getProductosDisponibles($id);
    }
    
    public function getProductosActivosAsignados()
    {
        return EspacioFisicoProductoDAO::getProductosActivosAsignados();
    }

    public function saveAsignacionProductoEspacioFisico(Request $req)
    {
        return EspacioFisicoProductoDAO::saveAsignacionProductoEspacioFisico((object)$req->all());
    }
    
}
