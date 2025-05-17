<?php

namespace App\Controllers;

use App\DAO\CategoriaDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class CategoriaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getCategorias()
    {
        return CategoriaDAO::getCategorias();
    }

    public function findCategoria($id)
    {
        return CategoriaDAO::findCategoria($id);
    }

    public function insertCategoria(Request $req)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        return CategoriaDAO::insertCategoria((object)$req->all());
    }
    
    public function insertSubCategoria(Request $req,$id)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        $nombre=$req->input("nombre");
        $descripcion=$req->input("descripcion");
        return CategoriaDAO::insertSubCategoria($id,$nombre,$descripcion);
    }
    
    public function insertSubSubCategoria(Request $req,$id)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        $nombre=$req->input("nombre");
        $descripcion=$req->input("descripcion");
        return CategoriaDAO::insertSubSubCategoria($id,$nombre,$descripcion);
    }


    public function updateCategoria($id, Request $req)
    {
        $reglas = ["nombre" => "required",  "descripcion" => "required"];
        $this->validate($req, $reglas);
        return CategoriaDAO::updateCategoria($id, (object)$req->all());
    }


    public function getSubCategoria($id)
    {
        return CategoriaDAO::getSubCategoria($id);        
    }

    public function getSubSubCategoria($id)
    {
        return CategoriaDAO::getSubSubCategoria($id);        
    }
}
