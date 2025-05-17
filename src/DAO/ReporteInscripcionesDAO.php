<?php
namespace App\DAO;
use Illuminate\Support\Facades\DB;


class ReporteInscripcionesDAO {

    public function __construct(){}


   //obtener los datos
   public static function getDatosInscripciones($p)
   {      
      $data= DB::table("cuota_inscripciones")
      ->join("cargo" , "cuota_inscripciones.id_cargo","cargo.cve_cargo")
      ->leftJoin("pago" , "cargo.idpago","pago.idpago")
      ->join("acciones" , "cargo.cve_accion","acciones.cve_accion")
      ->join("persona" , "cargo.cve_persona","persona.cve_persona")
      ->select("cuota_inscripciones.id_inscripcion",DB::raw("CONCAT_WS(' ',cuota_inscripciones.nombre,cuota_inscripciones.paterno,cuota_inscripciones.materno) AS nombre"))
      ->addSelect(DB::raw("IF(cuota_inscripciones.genero=1,'Hombre','Mujer') AS genero"),"cuota_inscripciones.edad","cuota_inscripciones.concepto")
      ->addSelect(DB::raw("CONCAT(acciones.numero_accion,case acciones.clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion"))
      ->addSelect("cargo.periodo","cargo.concepto AS concepto_actual","cargo.fecha_cargo")
      ->addSelect("pago.folio","pago.fecha_hora_cobro",DB::raw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS usuario"));
      // ->whereRaw(DB::raw("CONVERT(cargo.fecha_cargo,DATE) BETWEEN ? AND ?"),['2019-09-01','2022-09-30'])
      // ->where("cargo.cve_cuota",1);

      if($p->cuota??false)$data->where("cargo.cve_cuota",$p->cuota);
      // if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
      if($p->fecha_inicio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(cargo.fecha_cargo,DATE) BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin]);            

      return $data->get();
   }

   public static function getCuotasInscripcion()
   {
      return DB::table("cuota")->select("cve_cuota","cuota")->where("is_inscripcion",1)->get();
   }
 
}