<?php

namespace App\Controllers;
use App\DAO\AlmacenDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class AlmacenController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function crearCorteAlmacen(Request $req){
        
        return AlmacenDAO::crearCorteAlmacen((object)$req->all());
    }
    public function getCortesAlmacen(){
        return AlmacenDAO::getCortesAlmacen();
    }

    public function getDetalleCorteAlmacen($id)
    {
        return AlmacenDAO::getDetalleCorteAlmacen($id);
    }
    
      
}
