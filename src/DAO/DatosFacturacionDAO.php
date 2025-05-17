<?php

namespace App\DAO;

use App\Entity\Locker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DatosFacturacionDAO
{

   public function __construct()
   {
   }

   public static function getDatosFacturacion($id)
   {
    return DB::table("datos_facturacion")
    ->leftJoin("regimen_fiscal","datos_facturacion.regimen_fiscal","regimen_fiscal.clave_sat")
    ->where('cve_persona',$id)->get();
   }

   
   public static function getSocios($p)
   {
          /**
             SELECT 
             persona.cve_persona,acciones.cve_accion,
             CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS rentador,
             CONCAT(acciones.numero_accion, CASE
             acciones.clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion_propietario
              FROM socios 
             INNER JOIN persona ON socios.cve_persona=persona.cve_persona
             INNER JOIN acciones ON socios.cve_accion=acciones.cve_accion
             WHERE socios.estatus=1 AND socios.cve_accion IS NOT NULL AND acciones.estatus IN(1,2)
             AND CONCAT_WS('
             ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE '%?%' 
             AND acciones.numero_accion = 175 AND acciones.clasificacion = 0
           */

       $socios=DB::table('socios')
       ->join('persona','socios.cve_persona','persona.cve_persona')
       ->join('acciones' , 'socios.cve_accion','acciones.cve_accion')
       ->join('parentescos','socios.cve_parentesco','parentescos.cve_parentesco')
       ->select('persona.cve_persona','acciones.cve_accion')
       ->addSelect('parentescos.nombre AS parentesco','socios.foto')
       ->addSelect('persona.nombre','persona.apellido_paterno AS paterno','persona.apellido_materno AS materno')
       ->addSelect(DB::raw("TIMESTAMPDIFF(YEAR,fecha_nacimiento,CURDATE()) AS edad"))
       ->addSelect(DB::raw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS socio"))
       ->addSelect(DB::raw("CONCAT(acciones.numero_accion, CASE
       acciones.clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion"))
       ->where('socios.estatus',1)
       ->whereNotNull('socios.cve_accion')
       ->whereIn('acciones.estatus', [1, 2])
       ->where('acciones.numero_accion',$p->numero_accion)
       ->where('acciones.clasificacion',$p->clasificacion);

       $dueno=DB::table('dueno')
       ->join('persona','dueno.cve_persona','persona.cve_persona')
       ->join('acciones' , 'dueno.cve_dueno','acciones.cve_dueno')
       ->select('persona.cve_persona','acciones.cve_accion')
       ->addSelect('persona.nombre','persona.apellido_paterno AS paterno','persona.apellido_materno AS materno')
       ->addSelect(DB::raw("'dueño' AS parentesco"),DB::raw('1 AS foto'))
       ->addSelect(DB::raw("TIMESTAMPDIFF(YEAR,fecha_nacimiento,CURDATE()) AS edad"))
       ->addSelect(DB::raw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS socio"))
       ->addSelect(DB::raw("CONCAT(acciones.numero_accion, CASE
       acciones.clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion"))
       ->where('dueno.estatus',1)
       ->whereIn('acciones.estatus', [1, 2])
       ->where('acciones.numero_accion',$p->numero_accion)
       ->where('acciones.clasificacion',$p->clasificacion);
       return $socios->union($dueno)->get();
   }

   public static function createDatosFacturacion($id,$p)
   {
      return DB::transaction(function() use($id,$p){
         $cve_datos_facturacion = DB::table('datos_facturacion')
         ->insertGetId([
            'cve_persona' => $id, 
            'regimen_fiscal'=>$p->regimen_fiscal,
            'razon_social' => mb_strtoupper($p->razon_social, 'UTF-8'),
            'rfc'=>mb_strtoupper($p->rfc, 'UTF-8'),
            'correo'=>mb_strtolower($p->correo, 'UTF-8'),
            'cp'=>$p->cp,
            'calle'=>mb_strtoupper($p->calle, 'UTF-8'),
            'num_ext'=>mb_strtoupper($p->num_ext, 'UTF-8'),
            'num_int'=>mb_strtoupper($p->num_int, 'UTF-8'),
            'colonia'=>mb_strtoupper($p->colonia, 'UTF-8'),
            'municipio'=>mb_strtoupper($p->municipio, 'UTF-8'),
            'estado'=>mb_strtoupper($p->estado, 'UTF-8'),
            'pais'=>mb_strtoupper($p->pais, 'UTF-8'),
            'estatus'=>1]
        );
         return $cve_datos_facturacion;
      });
   }

   public static function updateDatosFacturacion($id,$p)
   {
     $rowAffect=DB::table("datos_facturacion")
     ->where("id_datos_facturacion",$id)
     ->update([
      'razon_social' => mb_strtoupper($p->razon_social),
      'rfc'=>mb_strtoupper($p->rfc),
      'correo'=>mb_strtolower($p->correo),
      'cp'=>$p->cp,
      'calle'=>mb_strtoupper($p->calle),
      'num_ext'=>mb_strtoupper($p->num_ext),
      'num_int'=>mb_strtoupper($p->num_int),
      'colonia'=>mb_strtoupper($p->colonia),
      'municipio'=>mb_strtoupper($p->municipio),
      'estado'=>mb_strtoupper($p->estado),
      'pais'=>mb_strtoupper($p->pais),
      'estatus'=>1,
      'regimen_fiscal'=>$p->regimen_fiscal
     ]);

     if(!$rowAffect) return 0;
     else return $rowAffect;
   } 

   
   public static function bajaDatosFacturacion($id,$p)
   {
      DB::table("datos_facturacion")->where("id_datos_facturacion",$id)->update(["estatus"=>0]);
   } 

   
   public static function verificarDatosFacturacion($id,$p)
   {
      DB::table("datos_facturacion")->where("id_datos_facturacion",$id)->update(["estatus"=>1]);
   } 

   public static function getRegimenFiscal()
   {
      return DB::table("regimen_fiscal")->get();
   }
   
}
