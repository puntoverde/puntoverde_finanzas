<?php
namespace App\DAO;
use App\Entity\MarcaProducto;
use Illuminate\Support\Facades\DB;

class MarcaProductoDAO {

    public function __construct(){}
    /**
     * 
     */
    public static function insertUnidadMedida($p)
    {   
        

       return DB::transaction(function () use ($p){

        $categoria=new MarcaProducto();
        $categoria->nombre=$p->nombre;
        $categoria->descripcion=$p->descripcion;       
        $categoria->save();

        return $categoria->id_categoria;

        });


    }

    public static function updateCategoria($id,$p){
       
        return DB::transaction(function () use ($id,$p){

        $categoria=MarcaProducto::find($id);
        $categoria->nombre=$p->nombre;
        $categoria->descripcion=$p->descripcion;       
        $categoria->save();

        return $categoria->id_categoria;

        });

    }

    public static function findCategoria($id){
      
        return MarcaProducto::find($id);
    }

    public static function getMarcaProducto()
    {
        try {
            return MarcaProducto::where("estatus",1)->get();           
        } catch (\Exception $e) {            
            return [];
        }
    }


    public static function getMarcaByNombre($name)
    {
        return MarcaProducto::where("nombre","LIKE","%$name%")->get();
    }




}