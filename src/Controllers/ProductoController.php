<?php

namespace App\Controllers;

use App\DAO\ProductoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ProductoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getProductosByParameters(Request $req)
    {
        return ProductoDAO::getProductosByParameters($req->input("search"));
    }

    public function getProductos(Request $req)
    {
        return ProductoDAO::getProductos((object)$req->all());
    }

    public function findProducto($id)
    {
        return ProductoDAO::findProducto($id);
    }

    public function insertProducto(Request $req)
    {
        $reglas = [
            "id_subcategoria" => "required", 
            "id_unidad_medida" => "required", 
            "id_tipo_producto" => "required", 
            "nombre" => "required",
            "clave" => "required", 
            "descripcion" => "required",
            "tipo" => "required",
            "presentaciones"=>"array",
            "presentaciones.*.unidad_medida"=>"required",
            "presentaciones.*.cantidad"=>"required",
            "marcas"=>"array",
            "marcas.*"=>"integer",
        ];        
        $this->validate($req, $reglas);
        return ProductoDAO::insertProducto((object)$req->all());
    }


    public function updateProducto($id, Request $req)
    {
        $reglas = ["id_almacen" => "required", "clave" => "required", "codigo_barra" => "required", "nombre" => "required","descripcion" => "required","tipo_producto" => "required","id_categoria" => "required"];
        $this->validate($req, $reglas);
        return ProductoDAO::updateProducto($id, (object)$req->all());
    }


    public function uploadFoto(Request $req)
    {
        $name=time();
        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $temp = explode(".", $file->getClientOriginalName());
            $directorio = '../upload/';
            $filename = $name. '.jpeg';
            if ($file->isValid()) {
                try {
                    $file->move($directorio, $filename);
                    return $filename;
                } catch (\Exception $e) {
                    return $e;
                }
            } else return 'ocurrio un error con la foto ';
        } else {
            return 'no existe el Documento..';
        }
    }


    public function getViewFoto(Request $req)
    {    $foto=$req->input('foto');
         $img=file_get_contents("../upload/$foto");
         return response($img)->header('Content-type','image/png');
    }


    public function getDetalleFormaNombre(Request $req)
    {
       $nombre=$req->input("nombre");
       return ProductoDAO::getDetalleFormaNombre($nombre);
    }
    
}
