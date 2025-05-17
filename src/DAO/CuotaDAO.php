<?php
namespace App\DAO;
use App\Entity\Cuota;
use Illuminate\Support\Facades\DB;


class CuotaDAO {

    public function __construct(){}

    public static function getCuotas($p){

     try {
        return Cuota::select("cve_cuota","numero_cuota","producto_servicio","cuota","precio","opcion_iva","iva","tipo_cuota","genero_aplica","edad_aplica","obligatoria","estatus")->get();        
     } catch (\Exception $e) {
        return $e;
     }

   }

   public static function getCuotaById($id){
    return Cuota::leftJoin('cuota_accion','cuota.cve_cuota','cuota_accion.cve_cuota')
    ->leftJoin('cuota_parentesco','cuota.cve_cuota','cuota_parentesco.cve_cuota')
    ->select('cuota.cve_cuota','numero_cuota','producto_servicio','tipo_cuota','cuota','precio','opcion_iva','iva')
    ->addSelect('genero_aplica','edad_aplica','obligatoria_acceso','carga_automatica','ciclo','tipo_ciclo','mes_ciclo')
    ->addSelect('dia_ciclo','limite_pago','veces_aplicar','aplicar_recargo','recargo_siguiente_mes')
    ->addSelect('recargo_unico','cantidad_recargo','recargo_cada','fecha_entrar_vigor','estatus','editable')
    ->addSelect(DB::raw("GROUP_CONCAT(cuota_accion.cve_tipo_accion) AS membresias"))
    ->addSelect(DB::raw("GROUP_CONCAT(cuota_parentesco.cve_parentesco) AS parentescos"))
    ->where('cuota.cve_cuota',$id)
    ->first();
 }

   public static function createCuota($p)
   {
       return DB::transaction(function () use ($p) {

      $cuota= new Cuota();
      $cuota->producto_servicio=$p->clave;
      $cuota->numero_cuota=$p->numero_cuota;
      $cuota->tipo_cuota=$p->tipo_cuota;
      $cuota->cuota=$p->cuota;
      $cuota->precio=$p->importe;
      $cuota->opcion_iva=$p->opcion_iva;
      $cuota->iva=.16;
      $cuota->genero_aplica=$p->genero_aplica;
      $cuota->edad_aplica=$p->edad_aplica;
      $cuota->obligatoria_acceso=$p->acceso;
      $cuota->carga_automatica=$p->carga_automatica;
      $cuota->ciclo=$p->ciclo;
      $cuota->tipo_ciclo=$p->tipo_ciclo;
      $cuota->mes_ciclo=$p->tipo_ciclo;
      $cuota->dia_ciclo=$p->tipo_ciclo;
      $cuota->limite_pago=$p->limite_pago;
      $cuota->veces_aplicar=$p->veces_aplica;
      $cuota->aplicar_recargo=$p->recargo_aplica;
      $cuota->recargo_unico=$p->recargo_unico;
      $cuota->cantidad_recargo=$p->recargo_cantidad;
      $cuota->recargo_cada=$p->recargo_cada;
      $cuota->fecha_entrar_vigor=$p->fecha_vigor;
      $cuota->estatus=1;
      $cuota->editable=$p->editable;
      $cuota->recargo_siguiente_mes=$p->mes_siguiente;
      $cuota->save();

      $cuota->acciones()->attach($p->membresias);
      $cuota->parentescos()->attach($p->parentescos);

      return $cuota->cve_cuota;
   });
      
   }

   public static function updateCuota($id,$p){
      
      $cuota= Cuota::find($id);
      $cuota->producto_servicio=$p->clave;
      $cuota->numero_cuota=$p->numero_cuota;
      $cuota->tipo_cuota=$p->tipo_cuota;
      $cuota->cuota=$p->cuota;
      $cuota->precio=$p->importe;
      $cuota->opcion_iva=$p->opcion_iva;
      $cuota->iva=.16;
      $cuota->genero_aplica=$p->genero_aplica;
      $cuota->edad_aplica=$p->edad_aplica;
      $cuota->obligatoria_acceso=$p->acceso;
      $cuota->carga_automatica=$p->carga_automatica;
      $cuota->ciclo=$p->ciclo;
      $cuota->tipo_ciclo=$p->tipo_ciclo;
      $cuota->mes_ciclo=$p->tipo_ciclo;
      $cuota->dia_ciclo=$p->tipo_ciclo;
      $cuota->limite_pago=$p->limite_pago;
      $cuota->veces_aplicar=$p->veces_aplica;
      $cuota->aplicar_recargo=$p->recargo_aplica;
      $cuota->recargo_unico=$p->recargo_unico;
      $cuota->cantidad_recargo=$p->recargo_cantidad;
      $cuota->recargo_cada=$p->recargo_cada;
      $cuota->fecha_entrar_vigor=$p->fecha_vigor;
      $cuota->estatus=1;
      $cuota->editable=$p->editable;
      $cuota->save();
      
      $cuota->acciones()->sync($p->membresias);
      $cuota->parentescos()->sync($p->parentescos);      
      
      return $cuota->cve_cuota;
   }

   public static function deleteCuota($id){
      $cuota= Cuota::find($id);
      $cuota->estatus=0;
      $cuota->save();
   }
}