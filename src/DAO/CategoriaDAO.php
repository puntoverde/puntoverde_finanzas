<?php
namespace App\DAO;
use App\Entity\CategoriaProductos;
use App\Entity\SubCategoriaProductos;
use App\Entity\SubSubCategoriaProductos;
use Illuminate\Support\Facades\DB;

class CategoriaDAO {

    public function __construct(){}
    /**
     * 
     */
    public static function insertCategoria($p)
    {          

        $categoria=new CategoriaProductos();
        $categoria->nombre=$p->nombre;
        $categoria->descripcion=$p->descripcion;       
        $categoria->save();

        return $categoria->id_categoria_pv;

    }
    
    public static function updateCategoria($id,$p){
       
        return DB::transaction(function () use ($id,$p){

        $categoria=CategoriaProductos::find($id);
        $categoria->nombre=$p->nombre;
        $categoria->descripcion=$p->descripcion;       
        $categoria->save();

        return $categoria->id_categoria;

        });

    }

    public static function findCategoria($id){
      
        return CategoriaProductos::find($id);
    }

    public static function getCategorias()
    {
        try {
            return CategoriaProductos::orderBy('nombre')->get();           
        } catch (\Exception $e) {            
            return [];
        }
    }


    /////******************/////////////

    public static function getSubCategoria($id){

        try {
            return SubCategoriaProductos::where("id_categoria_pv",$id)->orderBy('nombre')->get();           
        } catch (\Exception $e) {            
            return [];
        }
        
    }

    public static function insertSubCategoria($id,$nombre,$descripcion)
    {   
          
        return DB::table("subcategoria_producto_pv")->insertGetId([
            "id_categoria_pv"=>$id,
            "nombre"=>$nombre,
            "descripcion"=>$descripcion,
            "estatus"=>1
        ]);

    }




    public static function getSubSubCategoria($id){

        try {
            return SubSubCategoriaProductos::where("id_subcategoria_producto_pv",$id)->orderBy('nombre')->get();           
        } catch (\Exception $e) {            
            return [];
        }
        
    }


    public static function insertSubSubCategoria($id,$nombre,$descripcion)
    {   
          
        return DB::table("subsubcategoria_producto_pv")->insertGetId([
            "id_subcategoria_producto_pv"=>$id,
            "nombre"=>$nombre,
            "descripcion"=>$descripcion,
            "estatus"=>1
        ]);

    }


}