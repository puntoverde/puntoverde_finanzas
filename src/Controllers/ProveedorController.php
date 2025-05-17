<?php

namespace App\Controllers;

use App\DAO\ProveedorDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ProveedorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    public function createProveedor(Request $req)
    {
        $reglas=[
                "codigo" => "required", 
                "nombre_comercial" => "required",
                "rfc" => "required", 
                "moneda_proveedor" => "required",
                // "telefono_proveedor" => "required",
                // "calle" => "required",
                // "n_ext" => "required",
                // "cp" => "required",
                // "id_colonia" => "required",
                // "persona_contacto" => "required",
                // "correo_contacto" => "required",
                // "whatsapp_contacto" => "required",
        ];
        
        $this->validate($req,$reglas);

       return ProveedorDAO::createProveedor((object)$req->all());
    }
    public function updateProveedor(Request $req,$id)
    {
        $reglas=[
                "codigo" => "required", 
                "nombre_comercial" => "required",
                "rfc" => "required", 
                "moneda_proveedor" => "required",
                // "telefono_proveedor" => "required",
                // "calle" => "required",
                // "n_ext" => "required",
                // "cp" => "required",
                // "id_colonia" => "required",
                // "persona_contacto" => "required",
                // "correo_contacto" => "required",
                // "whatsapp_contacto" => "required",
        ];
        
        $this->validate($req,$reglas);

       return ProveedorDAO::updateProveedor($id,(object)$req->all());
    }
 

    public function getProveedores(Request $req)
    {
        return ProveedorDAO::getProveedores((object)$req->all());
    }
    
    
    public function getProveedorById($id)
    {
        return response()->json(ProveedorDAO::getProveedorById($id));
    }
    

    public function getProveedorByParameters(Request $req)
    {
        return ProveedorDAO::getProveedorByParameters($req->input("search"));
    }


    public function getCategoriasProvedor($id)
    {
        return ProveedorDAO::getCategoriasProvedor($id);
    }
    
    
    public function getProductoCategoriaProveedor($id,$id_prod)
    {
        return ProveedorDAO::getProductoCategoriaProveedor($id,$id_prod);
    }

    public function addCategoria(Request $req,$id)
    {
        return ProveedorDAO::addCategoria($id,$req->all());
    }

    public function addProductos(Request $req,$id)
    {
        return ProveedorDAO::addProductos($id,$req->all());
    }

    
}
