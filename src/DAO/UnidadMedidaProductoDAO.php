<?php
namespace App\DAO;
use App\Entity\UnidadMedida;
use Illuminate\Support\Facades\DB;

class UnidadMedidaProductoDAO {

    public function __construct(){}
    /**
     * 
     */
    public static function insertUnidadMedida($p)
    {   
        

       return DB::transaction(function () use ($p){

        $categoria=new UnidadMedida();
        $categoria->nombre=$p->nombre;
        $categoria->descripcion=$p->descripcion;       
        $categoria->save();

        return $categoria->id_categoria;

        });


    }

    public static function updateCategoria($id,$p){
       
        return DB::transaction(function () use ($id,$p){

        $categoria=UnidadMedida::find($id);
        $categoria->nombre=$p->nombre;
        $categoria->descripcion=$p->descripcion;       
        $categoria->save();

        return $categoria->id_categoria;

        });

    }

    public static function findCategoria($id){
      
        return UnidadMedida::find($id);
    }

    public static function getUnidadMedida()
    {
        try {
            return UnidadMedida::where("estatus",1)->get();           
        } catch (\Exception $e) {            
            return [];
        }
    }




}