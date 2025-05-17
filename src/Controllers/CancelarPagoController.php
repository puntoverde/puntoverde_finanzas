<?php

namespace App\Controllers;
use App\DAO\CancelarPagoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class CancelarPagoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function eliminarPago(Request $req){
        return CancelarPagoDAO::eliminarPago((object)$req->all());
    }

    public function consultarCargo(Request $req){
        return CancelarPagoDAO::consultarCargo((object)$req->all());
    }

    
}
