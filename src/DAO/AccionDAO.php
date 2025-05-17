<?php
namespace App\DAO;
use App\Entity\Accion;
use Illuminate\Support\Facades\DB;


class AccionDAO {

    public function __construct(){}

    public static function getAcciones($p){

        $acciones= DB::table("acciones AS acc")
        ->leftJoin("tipo_accion AS tac","acc.cve_tipo_accion","tac.cve_tipo_accion")
        ->SelectRaw("CONCAT(acc.numero_accion,'-',acc.clasificacion) AS nom_completo")
        ->addSelect('acc.cve_accion','acc.numero_accion', 'acc.clasificacion', 'acc.estatus','tac.nombre as tipo_accion') 
        ->addSelect('acc.cve_tipo_accion', 'acc.fecha_alta','acc.fecha_adquisicion', 'acc.fecha_baja', 'cve_dueno');
            
        if($p->numero_accion ?? false){ $acciones->where("acc.numero_accion",$p->numero_accion);}

        if($p->clasificacion ?? false ){ $acciones->where("acc.clasificacion",$p->clasificacion);}

        if($p->tipo_accion ?? false){$acciones->where("acc.cve_tipo_accion",$p->tipo_accion);}

        if($p->estatus ?? false){$acciones->where("acc.estatus",$p->cve_tipo_accion);}

        return $acciones->get();

   }

   public static function getAccionById($id){
    return Accion::find($id);
 }

   public static function updateAccion($id,$p){
      
      $accion=Accion::find($id);
      if(is_bool($p->estatus??'x'))$accion->estatus=$p->estatus?1:2;
      if($p->cve_tipo_accion??false)$accion->cve_tipo_accion=$p->cve_tipo_accion;
      if($p->fecha_adquisicion??false)$accion->fecha_adquisicion=$p->fecha_adquisicion;
      $accion->save();

      return 1;

   }
}