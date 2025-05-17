<?php

namespace App\Controllers;

use App\DAO\UnidadMedidaProductoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class UnidadMedidaProductoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getUnidadMedida()
    {
        return UnidadMedidaProductoDAO::getUnidadMedida();
    }

    public function findCategoria($id)
    {
        return UnidadMedidaProductoDAO::findCategoria($id);
    }

    public function insertCategoria(Request $req)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        return UnidadMedidaProductoDAO::insertUnidadMedida((object)$req->all());
    }


    public function updateCategoria($id, Request $req)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        return UnidadMedidaProductoDAO::updateCategoria($id, (object)$req->all());
    }


}
