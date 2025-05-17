<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CambiarFormaPagoDAO
{

   public function __construct()
   {
   }

   public static function eliminarPago($p)
   {

      return DB::transaction(function () use ($p){

          //obitien todos los id de los cargos que se pagaron los concatena en una cadena separada por coma 
         $cargos = DB::table("cargo")->selectRaw("CONCAT(cve_cargo) AS cargos")->where("idpago", $p->idpago)->groupBy("cargo.idpago")->value("cargos");     
         
         //se obtiene la accion de los cargos para guardarla en en pago cancelado
         $cve_accion=DB::table("cargo")->select("cve_accion")->where("idpago", $p->idpago)->groupBy("cargo.idpago")->value("cve_accion");
         
         $formas_pago=DB::table("forma_pago")
                 ->join("forma_pago_sat","forma_pago.clave","forma_pago_sat.clave")
                 ->where("idpago",$p->idpago)
                 ->selectRaw("GROUP_CONCAT(CONCAT(forma_pago_sat.forma_pago,':',forma_pago.monto)) AS pagos")
                 ->groupBy("idpago")
                 ->value("pagos");
         
         DB::statement("INSERT INTO cancelar_factura VALUES(idfactura,idpago,id_datos_facturacion,metodo_pago,uso_cfdi,forma_pago,uuid,estado_compaq,folio_compaq,codigo_error_compaq,mensaje_error_compaq,fecha_factura,motivo,cve_persona) SELECT idfactura,idpago,id_datos_facturacion,metodo_pago,uso_cfdi,forma_pago,uuid,estado_compaq,folio_compaq,codigo_error_compaq,mensaje_error_compaq,fecha_factura,'Cancelado Automatico Por Cancelar Pago',? FROM factura WHERE factura.idpago=?",[$p->cve_persona,$p->idpago]);

         //se inserta en la tabla de cancelar pagos
         DB::insert("INSERT INTO 
        cancelar_pago(idcancelar_pago,folio,persona_cobra,fecha_hora_cobro,subtotal,iva,total,descuento,recargo,cargos,usuario_cancela,motivo,cve_accion,formas_de_pago)
        SELECT idpago,folio,persona_cobra,fecha_hora_cobro,subtotal,iva,total,descuento,recargo,?,?,?,?,?
        FROM pago WHERE idpago=?", [$cargos, $p->cve_persona, $p->motivo,$cve_accion,$formas_pago,$p->idpago]);
                          
         //se elimina las forma de pago ligadas al pago 
         DB::table("forma_pago")->where("idpago", $p->idpago)->delete();
         
         //se elimina factura si es que el pago la tuviese
         DB::table("factura")->where("idpago", $p->idpago)->delete();                  
      
         //se elimina el pago
         DB::table("pago")->where("idpago", $p->idpago)->delete();
      
         //se liberan los cargos ligados al pago recien eliminado
         DB::table("cargo")->where("idpago", $p->idpago)->update(["idpago" => NULL]);

      
         return 1;

});
   }

   public static function consultarCargo($p)
   {

      $cargos=DB::table("pago")
      ->join("cargo","pago.idpago","cargo.idpago")
      ->join("cuota","cargo.cve_cuota","cuota.cve_cuota")
      ->leftJoin("descuento","cargo.cve_cargo","descuento.cve_cargo")
      ->join("acciones","cargo.cve_accion","acciones.cve_accion")
      ->join("persona","cargo.cve_persona","persona.cve_persona")
      ->join("persona AS cajero","pago.persona_cobra","cajero.cve_persona")
      ->select("pago.idpago","folio","fecha_hora_cobro","pago.descuento","pago.total","acciones.cve_accion")
      ->selectRaw("CONCAT(numero_accion,CASE clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion")
      ->selectRaw("CONCAT_WS(' ',cajero.nombre,cajero.apellido_paterno,cajero.apellido_materno) AS cajero")
      ->selectRaw("GROUP_CONCAT(CONCAT_WS(',',cargo.cve_cargo,cuota.cve_cuota,producto_servicio,concepto,periodo,cargo.total,IFNULL(descuento.monto,0)) SEPARATOR '--') AS cargos");      
      
      if($p->numero_accion??false && $p->clasificacion??false)$cargos->where("numero_accion",$p->numero_accion)->where("clasificacion",$p->clasificacion);
      if($p->folio??false)$cargos->where("folio",$p->folio);
      if($p->dia??false)$cargos->whereRaw("CAST(fecha_hora_cobro AS DATE)=?",[$p->dia]);

      $cargos->groupBy("folio");

      return $cargos->get();
                               
   }

   public static function getFormasPagoAsignada($id)
   {
       return DB::table("forma_pago")
       ->leftJoin("forma_pago_sat","forma_pago.clave","forma_pago_sat.clave")
       ->where("idpago",$id)
       ->select("idforma_pago","idpago","monto","forma_pago.clave","forma_pago_sat.forma_pago","banco","numero_cheque")
       ->get();
   }

   public static function getFormasPago()
   {
       return DB::table("forma_pago_sat")
       ->select("forma_pago_sat.clave","forma_pago_sat.forma_pago","forma_pago_sat.icono")
       ->where("estatus",1)
       ->get();
   }


   public static function updateFormapago($id_forma,$clave,$persona)
   {

      return DB::transaction(function()use($id_forma,$clave,$persona){
         
         $id_pago=DB::table("forma_pago")->where("idforma_pago",$id_forma)->value("idpago");
         $select_pago=DB::table("pago")->where("idpago",$id_pago)->select("persona_cobra","fecha_hora_cobro","total")->first();
         $select_cargos=DB::table("cargo")->where("idpago",$id_pago)->get()->toJson();
         $select_forma_pago=DB::table("forma_pago")->where("idpago",$id_pago)->get()->toJson();
         
         $update_ok=DB::table("forma_pago")->where("idforma_pago",$id_forma)->update(["clave"=>$clave]);

         DB::table("pago_historico")->insert([
            "id_pago_historico"=>$id_pago,
            "cajero"=>$select_pago->persona_cobra,
            "fecha_pago"=>$select_pago->fecha_hora_cobro,
            "monto"=>$select_pago->total,
            "cargos"=>$select_cargos,
            "fomas_pago"=>$select_forma_pago,
            "movimiento"=>"cambio forma pago",
            "persona_cambio"=>$persona,
            "fecha_cambio"=>Carbon::now()]);

         return $update_ok;

      });
   }
   public static function updateMonto($id_forma,$monto,$persona)
   {
      return DB::transaction(function()use($id_forma,$monto,$persona){
         
         $id_pago=DB::table("forma_pago")->where("idforma_pago",$id_forma)->value("idpago");
         $select_pago=DB::table("pago")->where("idpago",$id_pago)->select("persona_cobra","fecha_hora_cobro","total")->first();
         $select_cargos=DB::table("cargo")->where("idpago",$id_pago)->get()->toJson();
         $select_forma_pago=DB::table("forma_pago")->where("idpago",$id_pago)->get()->toJson();
         
         $update_ok=DB::table("forma_pago")->where("idforma_pago",$id_forma)->update(["monto"=>$monto]);

         DB::table("pago_historico")->insert([
            "id_pago_historico"=>$id_pago,
            "cajero"=>$select_pago->persona_cobra,
            "fecha_pago"=>$select_pago->fecha_hora_cobro,
            "monto"=>$select_pago->total,
            "cargos"=>$select_cargos,
            "fomas_pago"=>$select_forma_pago,
            "movimiento"=>"cambio monto de forma pago",
            "persona_cambio"=>$persona,
            "fecha_cambio"=>Carbon::now()]);

         return $update_ok;

      });
   }

   public static function addFormaPago($id)
   {
     return DB::table("forma_pago")->insertGetId(["idpago"=>$id]);
   }

   public static function deleteFormaPago($id)
   {
      return DB::table("forma_pago")->where("idforma_pago",$id)->delete();
   }

   public static function updateMontoCargo($id_cargo,$monto,$persona)
   {
     
      // return DB::table("cargo")->where("cve_cargo",$id)->update(["total"=>$p->monto,"subtotal"=>$subtotal,"iva"=>$iva]);

      return DB::transaction(function()use($id_cargo,$monto,$persona){
         $subtotal=($monto/116)*100;
         $iva=$subtotal*.16;
         
         $id_pago=DB::table("cargo")->where("cve_cargo",$id_cargo)->value("idpago");
         $select_pago=DB::table("pago")->where("idpago",$id_pago)->select("persona_cobra","fecha_hora_cobro","total")->first();
         $select_cargos=DB::table("cargo")->where("idpago",$id_pago)->get()->toJson();
         $select_forma_pago=DB::table("forma_pago")->where("idpago",$id_pago)->get()->toJson();
         
         $update_ok=DB::table("cargo")->where("cve_cargo",$id_cargo)->update(["total"=>$monto,"subtotal"=>$subtotal,"iva"=>$iva]);

         DB::table("pago_historico")->insert([
            "id_pago_historico"=>$id_pago,
            "cajero"=>$select_pago->persona_cobra,
            "fecha_pago"=>$select_pago->fecha_hora_cobro,
            "monto"=>$select_pago->total,
            "cargos"=>$select_cargos,
            "fomas_pago"=>$select_forma_pago,
            "movimiento"=>"cambio monto de un cargo",
            "persona_cambio"=>$persona,
            "fecha_cambio"=>Carbon::now()]);

         return $update_ok;

      });
   }

   public static function getCuotas($p)
   {
      return DB::table("cuota")->where("producto_servicio",$p->numero_cuota)->select("cve_cuota","producto_servicio","descripcion")->get();
   }

   public static function updateCuota($id,$p)
   {
      return DB::table("cargo")->where("cve_cargo",$id)->update(["cve_cuota"=>$p->cve_cuota]);
   }

   public static function getCajeros()
   {
      /*
         SELECT 
            persona.cve_persona,
            persona.nombre,
            persona.apellido_paterno,
            persona.apellido_materno 
         FROM  colaborador
         INNER JOIN  persona ON colaborador.cve_persona=persona.cve_persona
         WHERE colaborador.id_area IN(3,4)
      */
      return DB::table("colaborador")
      ->join("persona","colaborador.cve_persona","persona.cve_persona")
      ->whereIn("colaborador.id_area",[3,4])
      ->select(
         "persona.cve_persona",
         "persona.nombre",
         "persona.apellido_paterno",
         "persona.apellido_materno")
      ->get();
   }
   
   public static function updateCajero($id,$cajero,$persona)
   {
      return DB::transaction(function()use($id,$cajero,$persona){
         
         $select_pago=DB::table("pago")->where("idpago",$id)->select("persona_cobra","fecha_hora_cobro","total")->first();
         $select_cargos=DB::table("cargo")->where("idpago",$id)->get()->toJson();
         $select_forma_pago=DB::table("forma_pago")->where("idpago",$id)->get()->toJson();
         
         $update_ok= DB::table("pago")->where("idpago",$id)->update(["persona_cobra"=>$cajero]);

         DB::table("pago_historico")->insert([
            "id_pago_historico"=>$id,
            "cajero"=>$select_pago->persona_cobra,
            "fecha_pago"=>$select_pago->fecha_hora_cobro,
            "monto"=>$select_pago->total,
            "cargos"=>$select_cargos,
            "fomas_pago"=>$select_forma_pago,
            "movimiento"=>"cambio de cajero",
            "persona_cambio"=>$persona,
            "fecha_cambio"=>Carbon::now()]);

         return $update_ok;

      });
   }
   
   public static function updateFecha($id,$fecha,$persona)
   {

      return DB::transaction(function()use($id,$fecha,$persona){
         
         $select_pago=DB::table("pago")->where("idpago",$id)->select("persona_cobra","fecha_hora_cobro","total")->first();
         $select_cargos=DB::table("cargo")->where("idpago",$id)->get()->toJson();
         $select_forma_pago=DB::table("forma_pago")->where("idpago",$id)->get()->toJson();
         
         $update_ok= DB::table("pago")->where("idpago",$id)->update(["fecha_hora_cobro"=>$fecha]);

         DB::table("pago_historico")->insert([
            "id_pago_historico"=>$id,
            "cajero"=>$select_pago->persona_cobra,
            "fecha_pago"=>$select_pago->fecha_hora_cobro,
            "monto"=>$select_pago->total,
            "cargos"=>$select_cargos,
            "fomas_pago"=>$select_forma_pago,
            "movimiento"=>"cambio de fecha",
            "persona_cambio"=>$persona,
            "fecha_cambio"=>Carbon::now()]);

         return $update_ok;

      });
   }

  
}
