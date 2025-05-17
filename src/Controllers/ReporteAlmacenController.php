<?php

namespace App\Controllers;
use Illuminate\Http\Request;
use App\DAO\ReporteAlmacenDAO;



use Laravel\Lumen\Routing\Controller;

class ReporteAlmacenController extends Controller
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
    public function reporteAlmacenEntrada(Request $req)
    {       
        return ReporteAlmacenDAO::reporteAlmacenEntrada((object)$req->all());
    }   

    public function reporteAlmacenRequisicion(Request $req)
    {
        return ReporteAlmacenDAO::reporteAlmacenRequisicion((object)$req->all());
    }
    

    public function reportePedidoAlmacen(Request $req)
    {
        
        return ReporteAlmacenDAO::reportePedidoAlmacen((object)$req->all());
    }

   //se agrega almacen salida
    public function reporteAlmacenSalida(Request $req)
    {
        return ReporteAlmacenDAO::reporteAlmacenSalida((object)$req->all());
    }
  

}
