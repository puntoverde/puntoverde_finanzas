<?php
namespace App\DAO;
use App\Entity\Documentacion;
use App\Entity\Ficha;
use Illuminate\Support\Facades\DB;

class DocumentacionDAO
{
    public function __construct(){
    }

       //TODO: setDocumetacion
       public static function setDocumetacionEntregado($p){      
        $docu = (object)$p;
        $documento = Documentacion::find($docu->id_documentacion);
        $ficha = Ficha::find($docu->id_ficha);//->documentacion_entregar()->save($documento, ['entregado'=>$docu->entregado]);
       $existe =  DB::table('ad_ficha_documentacion')
              ->where('id_ficha',  $ficha->id_ficha)
              ->where('id_documentacion', $documento->id_documentacion)->exists();

        if(!$existe){
            $id = DB::table('ad_ficha_documentacion')->insertGetId(
                ['id_ficha' => $ficha->id_ficha, 
                'id_documentacion' => $documento->id_documentacion, 
                'entregado'=>$docu->entregado]);

            return  $id;
        }else{
            DB::table('ad_ficha_documentacion')
            ->where('id_ficha',  $ficha->id_ficha)
            ->where('id_documentacion', $documento->id_documentacion)
            ->update(['entregado'=>$docu->entregado]);
        }
        
        return true;
    }

    //NOTE: updateDocumentacion
    public static function updateDocumentacion($p,  $id){      
        $docu = $p;
        
         DB::transaction(function () use($docu) {
            
   
        });

        return true;
    }


    
    //TODO: getFamiliaresInformacion
    public static function getDocumentacionInformacion($id_grado){
 
        $query = DB::table("ad_documentacion")
        ->join("ad_documentacion_grado", "ad_documentacion.id_documentacion", "ad_documentacion_grado.id_documentacion")
        ->leftjoin("ad_ficha_documentacion", "ad_ficha_documentacion.id_documentacion", "ad_documentacion.id_documentacion")
        ->where ("ad_documentacion.activo", 1 )->where ("ad_documentacion_grado.activo", 1 )->where("ad_documentacion_grado.id_grado", $id_grado);
        $query->select("ad_documentacion.id_documentacion", "ad_documentacion.nombre",  "ad_documentacion_grado.id_grado", "ad_documentacion_grado.original", "ad_documentacion_grado.copia")
        ->addSelect("ad_ficha_documentacion.id_ficha_documentacion", "ad_ficha_documentacion.entregado");

        $familiares = $query->get();

        return $familiares;
    }
}