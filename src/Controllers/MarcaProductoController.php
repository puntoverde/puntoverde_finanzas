<?php

namespace App\Controllers;

use App\DAO\MarcaProductoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class MarcaProductoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getMarcaProducto()
    {
        return MarcaProductoDAO::getMarcaProducto();
    }

    public function findCategoria($id)
    {
        return MarcaProductoDAO::findCategoria($id);
    }

    public function insertCategoria(Request $req)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        return MarcaProductoDAO::insertUnidadMedida((object)$req->all());
    }


    public function updateCategoria($id, Request $req)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        return MarcaProductoDAO::updateCategoria($id, (object)$req->all());
    }

    public function getMarcaByNombre(Request $req)
    {
        return MarcaProductoDAO::getMarcaByNombre($req->input("nombre"));
    }

}
