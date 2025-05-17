<?php

namespace App\Controllers;
use Illuminate\Http\Request;
use App\DAO\ReporteProductoDAO;



use Laravel\Lumen\Routing\Controller;

class ReporteProductoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    //consultas para mostrar en tablas
    public function reporteProductoRequisicion(Request $req)
    {              
        return ReporteProductoDAO::reporteProductoRequisicion((object)$req->all());
    }   

    public function reporteProductoRequisicionRevision(Request $req)
    {
        return ReporteProductoDAO::reporteProductoRequisicionRevision((object)$req->all());
    }

    public function reporteProductoCuadricula(Request $req)
    {
        return ReporteProductoDAO::reporteProductoCuadricula((object)$req->all());
    }
    
    public function reporteProductoCuadriculaDetalle(Request $req)
    {
        return ReporteProductoDAO::reporteProductoCuadriculaDetalle((object)$req->all());
    }

   

}
