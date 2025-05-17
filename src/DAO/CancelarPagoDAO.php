<?php

namespace App\DAO;

use App\Entity\Accion;
use Illuminate\Support\Facades\DB;


class CancelarPagoDAO
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
         
         DB::statement("INSERT INTO cancelar_factura(idfactura,idpago,id_datos_facturacion,metodo_pago,uso_cfdi,forma_pago,uuid,estado_compaq,folio_compaq,codigo_error_compaq,mensaje_error_compaq,fecha_factura,motivo,cve_persona) 
         SELECT idfactura,idpago,id_datos_facturacion,metodo_pago,uso_cfdi,forma_pago,uuid,estado_compaq,folio_compaq,codigo_error_compaq,mensaje_error_compaq,fecha_factura,'Cancelado Automatico Por Cancelar Pago' AS motivo,? AS cve_persona FROM factura WHERE factura.idpago=?",[$p->cve_persona,$p->idpago]);

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
      ->leftJoin("descuento","cargo.cve_cargo","descuento.cve_cargo")
      ->join("acciones","cargo.cve_accion","acciones.cve_accion")
      ->join("persona","cargo.cve_persona","persona.cve_persona")
      ->join("persona AS cajero","pago.persona_cobra","cajero.cve_persona")
      ->select("pago.idpago","folio","fecha_hora_cobro","pago.descuento","pago.total","acciones.cve_accion")
      ->addSelect(DB::raw("CONCAT(numero_accion,CASE clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion"))
      ->addSelect(DB::raw("CONCAT_WS(' ',cajero.nombre,cajero.apellido_paterno,cajero.apellido_materno) AS cajero"))
      ->addSelect(DB::raw("GROUP_CONCAT(CONCAT_WS(',',cve_cuota,concepto,periodo,cargo.total,IFNULL(descuento.monto,0)) SEPARATOR '--') AS cargos"));      
      
      if($p->numero_accion??false && $p->clasificacion??false)$cargos->where("numero_accion",$p->numero_accion)->where("clasificacion",$p->clasificacion);
      if($p->folio??false)$cargos->where("folio",$p->folio);
      if($p->dia??false)$cargos->whereRaw("CAST(fecha_hora_cobro AS DATE)=?",[$p->dia]);

      $cargos->groupBy("folio");

      return $cargos->get();
                               
   }

  
}
