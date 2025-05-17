<?php

namespace App\DAO;

use App\Entity\OrdenTrabajo;
use App\Entity\Departamento;
use App\Entity\Colaborador;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class OrdenTrabajoDAO
{

   public function __construct()
   {
   }

   public static function getAllOrdenesTrabajo($p)
   {

      $data = DB::table("colaborador")
         ->join("area_rh", "colaborador.id_area", "area_rh.id_area_rh")
         ->where("cve_persona", $p->id_persona)
         ->select("area_rh.id_departamento", "colaborador.id_colaborador")
         ->first();

      if ($data) {

         $ordenes_trabajo = OrdenTrabajo::join("rh_departamento", "orden_trabajo.id_departamento", "rh_departamento.id_departamento")
            ->join("colaborador", "orden_trabajo.id_colaborador", "colaborador.id_colaborador")
            ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
            ->leftJoin("tipo_orden_trabajo","orden_trabajo.id_tipo_orden_trabajo","tipo_orden_trabajo.id_tipo_orden_trabajo")
            ->leftJoin("orden_trabajo_clasificacion","orden_trabajo.id_clasificacion_orden_trabajo","orden_trabajo_clasificacion.id_orden_trabajo_clasificacion")
            ->leftJoin("orden_trabajo_actividad","orden_trabajo.id_orden_trabajo","orden_trabajo_actividad.id_orden_trabajo")
            ->leftJoin("orden_trabajo_actividad_observacion","orden_trabajo_actividad.id_orden_trabajo_actividad","orden_trabajo_actividad_observacion.id_orden_trabajo_actividad")
            ->where("orden_trabajo.id_colaborador", $data->id_colaborador)->orWhere("orden_trabajo.id_departamento_dirigido", $data->id_departamento)
            ->select(
               "orden_trabajo.id_orden_trabajo",
               "orden_trabajo.nombre_evento",
               "orden_trabajo.descripcion",
               "orden_trabajo.fecha_registro",
               "orden_trabajo.fecha_inicio_evento",
               "orden_trabajo.fecha_fin_evento",
               "orden_trabajo.estatus",
               "persona.nombre",
               "persona.apellido_paterno",
               "persona.apellido_materno",
               "tipo_orden_trabajo.tipo_orden_trabajo",
               "orden_trabajo_clasificacion.nombre AS clasificacion",
               "orden_trabajo.imagen_evidencia_1",
               "orden_trabajo.imagen_evidencia_2",
               "orden_trabajo.imagen_evidencia_3"
            )
            ->selectRaw("IF(orden_trabajo.id_colaborador=?,1,0) AS genero", [$data->id_colaborador])
            ->selectRaw("IF(orden_trabajo.id_departamento_dirigido=?,1,0) AS atiende", [$data->id_departamento])
            ->selectRaw("COUNT(orden_trabajo_actividad_observacion.id_orden_trabajo_actividad) AS observaciones")
            ->groupBy("orden_trabajo.id_orden_trabajo")
            ->orderBy("orden_trabajo.id_orden_trabajo","DESC")
            ->get();

         return $ordenes_trabajo;
      } else {
         return [];
      }
   }

   public static function getOrdenTrabajoById($id)
   {
      $orden_trabajo = OrdenTrabajo::join("rh_departamento", "orden_trabajo.id_departamento", "rh_departamento.id_departamento")
         ->join("colaborador", "orden_trabajo.id_colaborador", "colaborador.id_colaborador")
         ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
         ->leftJoin("tipo_orden_trabajo","orden_trabajo.id_tipo_orden_trabajo","tipo_orden_trabajo.id_tipo_orden_trabajo")
         ->leftJoin("orden_trabajo_clasificacion","orden_trabajo.id_clasificacion_orden_trabajo","orden_trabajo_clasificacion.id_orden_trabajo_clasificacion")
         ->where("orden_trabajo.id_orden_trabajo", $id)
         ->select(
            "id_departamento_dirigido",
            "orden_trabajo.nombre_evento",
            "orden_trabajo.descripcion",
            "orden_trabajo.fecha_registro",
            "orden_trabajo.fecha_inicio_evento",
            "orden_trabajo.fecha_fin_evento",
            "orden_trabajo.estatus",
            "persona.nombre",
            "persona.apellido_paterno",
            "persona.apellido_materno",
            "tipo_orden_trabajo.id_tipo_orden_trabajo",
            "orden_trabajo_clasificacion.id_orden_trabajo_clasificacion"
         )
         ->first();

      return $orden_trabajo;
   }


   public static function createOrdenTrabajo($p)
   {

      //esta linea busca el id colaborador con el cve_persona 
      $id_colaborador = DB::table("colaborador")->where("cve_persona", $p->id_persona)->value("id_colaborador");

      $departamento = Departamento::find($p->id_departamento);
      $colaborador = Colaborador::find($id_colaborador);

      $orden_trabajo = new OrdenTrabajo();
      $orden_trabajo->departamento()->associate($departamento);
      $orden_trabajo->departamento_dirigido()->associate($p->departamento_dirigido);
      $orden_trabajo->colaborador()->associate($colaborador);
      $orden_trabajo->tipo_orden_trabajo()->associate($p->id_tipo_orden_trabajo);
      $orden_trabajo->clasificacion_orden_trabajo()->associate($p->id_clasificacion_orden_trabajo);
      $orden_trabajo->folio = $p->folio??null;
      $orden_trabajo->nombre_evento = $p->nombre_evento;
      $orden_trabajo->descripcion = $p->descripcion;
      $orden_trabajo->fecha_registro = Carbon::now()->format("Y-m-d H:i:s");
      $orden_trabajo->fecha_inicio_evento = $p->fecha_inicio_evento??null;
      $orden_trabajo->fecha_fin_evento = $p->fecha_fin_evento??null;
      $orden_trabajo->estatus = 0;
      $orden_trabajo->cve_socio = $p->cve_socio??null;

      $ok = $orden_trabajo->save();

      return $ok;
   }


   public static function updateOrdenTrabajo($id, $p)
   {


      $orden_trabajo = OrdenTrabajo::find($id);
      $orden_trabajo->departamento_dirigido()->associate($p->departamento_dirigido);
      $orden_trabajo->tipo_orden_trabajo()->associate($p->id_tipo_orden_trabajo);
      $orden_trabajo->folio = $p->folio??null;
      $orden_trabajo->nombre_evento = $p->nombre_evento;
      $orden_trabajo->descripcion = $p->descripcion;
      $orden_trabajo->fecha_inicio_evento = $p->fecha_inicio_evento??null;
      $orden_trabajo->fecha_fin_evento = $p->fecha_fin_evento??null;
      $orden_trabajo->cve_socio = $p->cve_socio??null;
      $ok = $orden_trabajo->save();

      return $ok;
   }

   public static function getDepartamentoColaborador($id)
   {
      try {         

         $departamento = DB::table("rh_departamento")
            ->join("area_rh", "rh_departamento.id_departamento", "area_rh.id_departamento")
            ->join("colaborador", "area_rh.id_area_rh", "colaborador.id_area")
            ->where("colaborador.cve_persona", $id)
            ->select("rh_departamento.id_departamento", "rh_departamento.nombre")
            ->first();
         return $departamento;
      } catch (\Exception $e) {
         return $e;
      }
   }


   public static function getDepartamentosDisponibles()
   {
      try {

         $departamentos = DB::table("rh_departamento")
            ->select("rh_departamento.id_departamento", "rh_departamento.nombre")
            ->get();
         return $departamentos;
      } catch (\Exception $e) {
         return $e;
      }
   }

   public static function updateCancelarRechazar($id, $estatus)
   {
      $orden_trabajo = OrdenTrabajo::find($id);
      $orden_trabajo->estatus = $estatus;
      $ok = $orden_trabajo->save();
      return $ok;
   }
   
   
   public static function iniciarOrdenTrabajo($id, $id_colaborador)
   {
      $orden_trabajo = OrdenTrabajo::find($id);
      $orden_trabajo->responsable_orden_trabajo = $id_colaborador;
      $orden_trabajo->estatus = 1;
      $ok = $orden_trabajo->save();
      return $ok;
   }



   public static function getActividadOrdenTrabajo($id_orden)
   {
     
      $orden_trabajo = DB::table("orden_trabajo_actividad")
      ->join("colaborador","orden_trabajo_actividad.responsable","colaborador.id_colaborador")
      ->join("persona","colaborador.cve_persona","persona.cve_persona")
      ->leftJoin("orden_trabajo_actividad_observacion","orden_trabajo_actividad.id_orden_trabajo_actividad","orden_trabajo_actividad_observacion.id_orden_trabajo_actividad")
      ->where("orden_trabajo_actividad.id_orden_trabajo",$id_orden)
      ->select("orden_trabajo_actividad.id_orden_trabajo_actividad","orden_trabajo_actividad.actividad","orden_trabajo_actividad.estatus","persona.nombre","persona.apellido_paterno","persona.apellido_materno")
      ->selectRaw("COUNT(orden_trabajo_actividad_observacion.id_orden_trabajo_actividad) AS observaciones")
      ->groupBy("orden_trabajo_actividad.id_orden_trabajo_actividad")
      ->get();
      return $orden_trabajo;
   }

   public static function getActividadOrdenByIdTrabajo($id)
   {
      $orden_trabajo = DB::table("orden_trabajo_actividad")
         ->where("orden_trabajo_actividad.id_orden_trabajo_actividad",$id)         
         ->first();

      return $orden_trabajo;
   }

   public static function createActividadOrdenTrabajo($id,$p)
   {

      $ok=DB::table("orden_trabajo_actividad")->insertGetId([
         "id_orden_trabajo"=>$id,
         "responsable"=>$p->responsable,
         "actividad"=>$p->actividad,
         "id_orden_trabajo_tipo_actividad"=>$p->tipo_actividad,
         "fecha_planeada"=>$p->fecha_planeada
      ]);

      return $ok;
   }

   public static function deleteActividadOrdenTrabajo($id)
   {
      $orden_trabajo = DB::table("orden_trabajo_actividad")->where("orden_trabajo_actividad.id_orden_trabajo_actividad",$id)->delete();

      return $orden_trabajo;
   }
   
   public static function terminarActividadOrdenTrabajo($id)
   {
      $orden_trabajo = DB::table("orden_trabajo_actividad")->where("orden_trabajo_actividad.id_orden_trabajo_actividad",$id)->update(["estatus"=>1]);

      $id_orden_trabajo=DB::table("orden_trabajo_actividad")->where("orden_trabajo_actividad.id_orden_trabajo_actividad",$id)->value("id_orden_trabajo");
      
      $terminadas_todas_actividades=DB::table("orden_trabajo_actividad")->where("id_orden_trabajo",$id_orden_trabajo)->where("estatus",0)->count();

      if($terminadas_todas_actividades==0)
      {
         DB::table("orden_trabajo")->where("orden_trabajo.id_orden_trabajo",$id_orden_trabajo)->update(["estatus"=>2]);
      }

      return $orden_trabajo;
   }

   public static function reporteOrdenTrabajoDepartamentos()
   {
      $orden_trabajo = DB::table("rh_departamento")->select("id_departamento","nombre")->orderBy("rh_departamento.nombre","asc")->get();

      return $orden_trabajo;
   }

   public static function reporteOrdenTrabajo($id,$folio,$cve_socio)
   {
  
      /*
         SELECT 
	         orden_trabajo.id_orden_trabajo,
	         orden_trabajo.nombre_evento,
	         orden_trabajo.descripcion,
	         persona.nombre,
	         persona.apellido_paterno,
	         persona.apellido_materno,
	         rh_departamento.nombre AS departamento_solicito,
	         rh_departamento_dirigido.nombre AS departamento_atiende,
	         orden_trabajo.fecha_registro,
	         orden_trabajo.fecha_inicio_evento,
	         orden_trabajo.fecha_fin_evento,
	         orden_trabajo.estatus,
	         COUNT(if(orden_trabajo_actividad.estatus=1,1,NULL)) AS actividad_completadas,
	         COUNT(orden_trabajo_actividad.estatus) AS actividad_totales
         FROM orden_trabajo
         INNER JOIN rh_departamento ON orden_trabajo.id_departamento=rh_departamento.id_departamento
         INNER JOIN rh_departamento AS rh_departamento_dirigido ON orden_trabajo.id_departamento_dirigido=rh_departamento_dirigido.id_departamento
         LEFT JOIN colaborador ON orden_trabajo.responsable_orden_trabajo=colaborador.id_colaborador
         LEFT JOIN  persona ON colaborador.cve_persona=persona.cve_persona
         left JOIN orden_trabajo_actividad ON orden_trabajo.id_orden_trabajo=orden_trabajo_actividad.id_orden_trabajo
         WHERE orden_trabajo.id_departamento=9 OR orden_trabajo.id_departamento_dirigido=9 GROUP BY orden_trabajo.id_orden_trabajo;
      */

      $orden_trabajo = DB::table("orden_trabajo")
      ->join("rh_departamento" , "orden_trabajo.id_departamento","rh_departamento.id_departamento")
      ->join("rh_departamento AS rh_departamento_dirigido", "orden_trabajo.id_departamento_dirigido","rh_departamento_dirigido.id_departamento")
      ->leftJoin("colaborador" , "orden_trabajo.responsable_orden_trabajo","colaborador.id_colaborador")
      ->leftJoin("persona" , "colaborador.cve_persona","persona.cve_persona")
      ->leftJoin("orden_trabajo_actividad" , "orden_trabajo.id_orden_trabajo","orden_trabajo_actividad.id_orden_trabajo")
      ->groupBy("orden_trabajo.id_orden_trabajo")
      ->select(
            "orden_trabajo.id_orden_trabajo",
	         "orden_trabajo.nombre_evento",
	         "orden_trabajo.descripcion",
	         "persona.nombre",
	         "persona.apellido_paterno",
	         "persona.apellido_materno",
	         "rh_departamento.nombre AS departamento_solicito",
	         "rh_departamento_dirigido.nombre AS departamento_atiende",
	         "orden_trabajo.fecha_registro",
	         "orden_trabajo.fecha_inicio_evento",
	         "orden_trabajo.fecha_fin_evento",
	         "orden_trabajo.estatus"
      )	         
      ->selectRaw("COUNT(IF(orden_trabajo_actividad.estatus=1,1,NULL)) AS actividad_completadas")
      ->selectRaw("COUNT(orden_trabajo_actividad.estatus) AS actividad_totales");

      if($id!=='all')
      {

         $orden_trabajo->where("orden_trabajo.id_departamento",$id)
         ->orWhere("orden_trabajo.id_departamento_dirigido",$id);
      }

      if($folio??false)
      {
         $orden_trabajo->where("orden_trabajo.folio",$folio);
      }
      
      if($cve_socio??false)
      {
         $orden_trabajo->where("orden_trabajo.cve_socio",$cve_socio);
      }

      return $orden_trabajo->get();

      
   }


   public static function OrdenTrabajoActividades($id)
   {
    
      $orden_trabajo = DB::table("orden_trabajo_actividad")
      ->join("colaborador" , "orden_trabajo_actividad.responsable","colaborador.id_colaborador")
      ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
      ->where("orden_trabajo_actividad.id_orden_trabajo",$id)
      ->select(
            "orden_trabajo_actividad.actividad",
            "orden_trabajo_actividad.estatus",
            "persona.nombre",
            "persona.apellido_paterno",
            "persona.apellido_materno" 
      )
      ->get();

      return $orden_trabajo;
   }

   public static function getTipoOrdenTrabajo()
   {
      try{
         return  DB::table("tipo_orden_trabajo")->where("estatus",1)->select("id_tipo_orden_trabajo","tipo_orden_trabajo")->get();

      }
      catch(\Error $e){
         dd($e);
          return [];
      }

      
   }
  
   public static function getTipoOrdenTrabajoActividad()
   {      
      
      try{
         return DB::table("orden_trabajo_tipo_actividad")->where("estatus",1)->select("id_orden_trabajo_actividad","nombre")->get();
      }
      catch(\Error $e){
          return [];
      }
   }

   public static function getActividadesByDepartamento($cve_persona,$fecha,$responsable)
   {
      /*
         SELECT orden_trabajo_actividad.id_orden_trabajo_actividad ,orden_trabajo_actividad.actividad,orden_trabajo_tipo_actividad.nombre,orden_trabajo_actividad.fecha_planeada, persona.nombre,persona.apellido_paterno,persona.apellido_materno 
         FROM orden_trabajo 
         INNER JOIN orden_trabajo_actividad ON orden_trabajo.id_orden_trabajo=orden_trabajo_actividad.id_orden_trabajo
         INNER JOIN orden_trabajo_tipo_actividad ON orden_trabajo_actividad.id_orden_trabajo_tipo_actividad=orden_trabajo_tipo_actividad.id_orden_trabajo_actividad
         INNER JOIN colaborador ON orden_trabajo_actividad.responsable=colaborador.id_colaborador
         INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
         WHERE orden_trabajo.id_departamento_dirigido=9 AND orden_trabajo_actividad.estatus=0
      */

      $id_departamento=DB::table("colaborador")->join("area_rh" , "colaborador.id_area","area_rh.id_area_rh")->where("colaborador.cve_persona",$cve_persona)->value("area_rh.id_departamento");

      $query=DB::table("orden_trabajo")      
      ->join("orden_trabajo_actividad" , "orden_trabajo.id_orden_trabajo","orden_trabajo_actividad.id_orden_trabajo")
      ->join("orden_trabajo_tipo_actividad" , "orden_trabajo_actividad.id_orden_trabajo_tipo_actividad","orden_trabajo_tipo_actividad.id_orden_trabajo_actividad")
      ->join("colaborador" , "orden_trabajo_actividad.responsable","colaborador.id_colaborador")
      ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
      ->where("orden_trabajo.id_departamento_dirigido",$id_departamento)
      // ->where("orden_trabajo_actividad.estatus",0)
      ->where("orden_trabajo_actividad.fecha_planeada",$fecha)
      ->select(
         "orden_trabajo_actividad.id_orden_trabajo_actividad",
         "orden_trabajo_actividad.actividad",
         "orden_trabajo_tipo_actividad.nombre AS tipo_actividad",
         "orden_trabajo_actividad.fecha_planeada",
         "orden_trabajo_actividad.fecha_termino",
         "orden_trabajo_actividad.observacion",
         "orden_trabajo_actividad.estatus",
         "persona.nombre",
         "persona.apellido_paterno",
         "persona.apellido_materno");

       if($responsable??false)
       {
         $query->where("orden_trabajo_actividad.responsable",$responsable);
       }

       return $query->get();

   }

   public static function getFechasActividadesPendientes($cve_persona,$responsable)
   {

      /*
         SELECT orden_trabajo_actividad.fecha_planeada 
         FROM orden_trabajo
         INNER JOIN orden_trabajo_actividad ON orden_trabajo.id_orden_trabajo=orden_trabajo_actividad.id_orden_trabajo
         WHERE orden_trabajo.id_departamento_dirigido=9 AND orden_trabajo_actividad.estatus=0 AND orden_trabajo_actividad.fecha_planeada < CURDATE() AND orden_trabajo_actividad.responsable=40
         GROUP BY orden_trabajo_actividad.fecha_planeada;
      */

      $id_departamento=DB::table("colaborador")->join("area_rh" , "colaborador.id_area","area_rh.id_area_rh")->where("colaborador.cve_persona",$cve_persona)->value("area_rh.id_departamento");
      
      $query=DB::table("orden_trabajo")
      ->join("orden_trabajo_actividad","orden_trabajo.id_orden_trabajo","orden_trabajo_actividad.id_orden_trabajo")
      ->where("orden_trabajo.id_departamento_dirigido",$id_departamento)
      ->where("orden_trabajo_actividad.estatus",0)
      ->whereRaw("orden_trabajo_actividad.fecha_planeada < CURDATE()")
      ->select("orden_trabajo_actividad.fecha_planeada");

      if($responsable??false)
       {
         $query->where("orden_trabajo_actividad.responsable",$responsable);
       }

       return $query->get();

   }


public static function terminarActividadByDepartamento($id_actividad,$fecha,$observacion)
   {

      return DB::table("orden_trabajo_actividad")->where("id_orden_trabajo_actividad",$id_actividad)->update(["fecha_termino"=>$fecha,"observacion"=>$observacion,"estatus"=>1]);
      // $orden_trabajo = DB::table("orden_trabajo_actividad")->where("orden_trabajo_actividad.id_orden_trabajo_actividad",$id)->update(["estatus"=>1]);

      // $id_orden_trabajo=DB::table("orden_trabajo_actividad")->where("orden_trabajo_actividad.id_orden_trabajo_actividad",$id)->value("id_orden_trabajo");
      
      // $terminadas_todas_actividades=DB::table("orden_trabajo_actividad")->where("id_orden_trabajo",$id_orden_trabajo)->where("estatus",0)->count();

      // if($terminadas_todas_actividades==0)
      // {
      //    DB::table("orden_trabajo")->where("orden_trabajo.id_orden_trabajo",$id_orden_trabajo)->update(["estatus"=>2]);
      // }

      // return $orden_trabajo;
   }



   public static function geetActividadesReporte($fecha_inicio,$fecha_fin,$responsable,$tipo,$departamento)
{
   

   /*
      SELECT 
	         orden_trabajo_actividad.id_orden_trabajo_actividad ,
	         orden_trabajo_actividad.actividad,
	         orden_trabajo_tipo_actividad.nombre,
	         orden_trabajo_actividad.fecha_planeada, 
	         persona.nombre,
	         persona.apellido_paterno,
	         persona.apellido_materno,
	         orden_trabajo_actividad.fecha_termino,
	         orden_trabajo_actividad.observacion,
	         orden_trabajo_actividad.estatus
      FROM orden_trabajo 
      INNER JOIN orden_trabajo_actividad ON orden_trabajo.id_orden_trabajo=orden_trabajo_actividad.id_orden_trabajo
      INNER JOIN orden_trabajo_tipo_actividad ON orden_trabajo_actividad.id_orden_trabajo_tipo_actividad=orden_trabajo_tipo_actividad.id_orden_trabajo_actividad
      INNER JOIN colaborador ON orden_trabajo_actividad.responsable=colaborador.id_colaborador
      INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
      WHERE orden_trabajo_actividad.fecha_planeada BETWEEN '2024-06-01' AND '2024-06-30' AND orden_trabajo_actividad.responsable=42 AND orden_trabajo_actividad.id_orden_trabajo_tipo_actividad=2
   */

   $query=DB::table("orden_trabajo")
   ->join("orden_trabajo_actividad" , "orden_trabajo.id_orden_trabajo","orden_trabajo_actividad.id_orden_trabajo")
   ->join("orden_trabajo_tipo_actividad" , "orden_trabajo_actividad.id_orden_trabajo_tipo_actividad","orden_trabajo_tipo_actividad.id_orden_trabajo_actividad")
   ->join("colaborador" , "orden_trabajo_actividad.responsable","colaborador.id_colaborador")
   ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
   ->select(
      "orden_trabajo_actividad.id_orden_trabajo_actividad",
      "orden_trabajo_actividad.actividad",
      "orden_trabajo_tipo_actividad.nombre AS tipo_actividad",
      "orden_trabajo_actividad.fecha_planeada", 
      "persona.nombre",
      "persona.apellido_paterno",
      "persona.apellido_materno",
      "orden_trabajo_actividad.fecha_termino",
      "orden_trabajo_actividad.observacion",
      "orden_trabajo_actividad.estatus");

   if($fecha_inicio?? false && $fecha_fin??false)
   {
      $query->whereRaw("orden_trabajo_actividad.fecha_planeada BETWEEN ? AND ?",[$fecha_inicio,$fecha_fin]);
   }

   if($responsable??false)
   {
      $query->where("orden_trabajo_actividad.responsable",$responsable);
   }
   if($tipo??false)
   {
      $query->where("orden_trabajo_actividad.id_orden_trabajo_tipo_actividad",$tipo);
   }

   return $query->get();

}



public static function  createObservacionActividad($p)
{


   return DB::table("orden_trabajo_actividad_observacion")->insertGetId(["id_orden_trabajo_actividad"=>$p->id_orden_trabajo_actividad,"responsable"=>$p->responsable,"fecha"=>Carbon::now(),"observacion"=>$p->observacion]);


}


public static function getReporteOrdenesTrabajoSocios($folio,$estatus)
{

   /*
      SELECT 
            orden_trabajo.folio,
            rh_departamento.nombre AS departamento,
            orden_trabajo.nombre_evento,
            persona.nombre,
            persona.apellido_paterno,
            persona.apellido_materno,
            orden_trabajo.fecha_registro,
            COUNT(orden_trabajo_actividad.id_orden_trabajo_actividad) AS actividades,
            COUNT(IF(orden_trabajo_actividad.estatus=0,1,NULL)) AS actividades_pendiente,
            COUNT(IF(orden_trabajo_actividad.estatus=1,1,NULL)) AS actividades_finalizada
      FROM orden_trabajo
      LEFT JOIN orden_trabajo_actividad ON orden_trabajo.id_orden_trabajo=orden_trabajo_actividad.id_orden_trabajo
      INNER JOIN rh_departamento ON orden_trabajo.id_departamento_dirigido=rh_departamento.id_departamento
      INNER JOIN socios ON orden_trabajo.cve_socio=socios.cve_socio
      INNER JOIN persona ON socios.cve_persona=persona.cve_persona
      INNER JOIN acciones ON socios.cve_accion=acciones.cve_accion
      GROUP BY orden_trabajo.id_orden_trabajo
   */


   $query=DB::table("orden_trabajo")
   ->leftJoin("orden_trabajo_actividad" , "orden_trabajo.id_orden_trabajo","orden_trabajo_actividad.id_orden_trabajo")
   ->leftJoin("rh_departamento" , "orden_trabajo.id_departamento_dirigido","rh_departamento.id_departamento")
   ->leftJoin("socios" , "orden_trabajo.cve_socio","socios.cve_socio")
   ->leftJoin("persona" , "socios.cve_persona","persona.cve_persona")
   ->leftJoin("acciones" , "socios.cve_accion","acciones.cve_accion")
   ->select(
            "orden_trabajo.folio",
            "rh_departamento.nombre AS departamento",
            "orden_trabajo.nombre_evento",
            "persona.nombre",
            "persona.apellido_paterno",
            "persona.apellido_materno",
            "orden_trabajo.fecha_registro"
   )
   ->selectRaw("COUNT(orden_trabajo_actividad.id_orden_trabajo_actividad) AS actividades")
   ->selectRaw("COUNT(IF(orden_trabajo_actividad.estatus=0,1,NULL)) AS actividades_pendiente")
   ->selectRaw("COUNT(IF(orden_trabajo_actividad.estatus=1,1,NULL)) AS actividades_finalizada")
   ->selectRaw("CONCAT(acciones.numero_accion,CASE clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion_socio")
   ->groupBy("orden_trabajo.id_orden_trabajo");

   if($folio??false)
   {
      $query->where("orden_trabajo.folio",$folio);
   }
   if($estatus??false)
   {
      $query->where("orden_trabajo.estatus",$estatus);
   }


   return $query->get();

}


public static function getReporteOrdenesTrabajoInterno($folio,$estatus)
{

   /*
      SELECT 
            orden_trabajo.folio,
            rh_departamento.nombre AS departamento,
            orden_trabajo.nombre_evento,
            persona.nombre,
            persona.apellido_paterno,
            persona.apellido_materno,
            orden_trabajo.fecha_registro,
            COUNT(orden_trabajo_actividad.id_orden_trabajo_actividad) AS actividades,
            COUNT(IF(orden_trabajo_actividad.estatus=0,1,NULL)) AS actividades_pendiente,
            COUNT(IF(orden_trabajo_actividad.estatus=1,1,NULL)) AS actividades_finalizada
      FROM orden_trabajo
      LEFT JOIN orden_trabajo_actividad ON orden_trabajo.id_orden_trabajo=orden_trabajo_actividad.id_orden_trabajo
      INNER JOIN rh_departamento ON orden_trabajo.id_departamento_dirigido=rh_departamento.id_departamento
      INNER JOIN socios ON orden_trabajo.cve_socio=socios.cve_socio
      INNER JOIN persona ON socios.cve_persona=persona.cve_persona
      INNER JOIN acciones ON socios.cve_accion=acciones.cve_accion
      GROUP BY orden_trabajo.id_orden_trabajo
   */


   $query=DB::table("orden_trabajo")
   ->leftJoin("orden_trabajo_actividad" , "orden_trabajo.id_orden_trabajo","orden_trabajo_actividad.id_orden_trabajo")
   ->leftJoin("rh_departamento" , "orden_trabajo.id_departamento_dirigido","rh_departamento.id_departamento")   
   ->leftJoin("colaborador","orden_trabajo.id_colaborador","colaborador.id_colaborador")
   ->leftJoin("persona" , "colaborador.cve_persona","persona.cve_persona")   
   ->select(
            "rh_departamento.nombre AS departamento",
            "orden_trabajo.nombre_evento",
            "persona.nombre",
            "persona.apellido_paterno",
            "persona.apellido_materno",
            "orden_trabajo.fecha_registro"
   )
   ->selectRaw("COUNT(orden_trabajo_actividad.id_orden_trabajo_actividad) AS actividades")
   ->selectRaw("COUNT(IF(orden_trabajo_actividad.estatus=0,1,NULL)) AS actividades_pendiente")
   ->selectRaw("COUNT(IF(orden_trabajo_actividad.estatus=1,1,NULL)) AS actividades_finalizada")
   ->whereNull("orden_trabajo.folio")
   ->groupBy("orden_trabajo.id_orden_trabajo");

   if($folio??false)
   {
      $query->where("orden_trabajo.folio",$folio);
   }
   if($estatus??false)
   {
      $query->where("orden_trabajo.estatus",$estatus);
   }


   return $query->get();

}


public static function getClasificacionOrdenTrabajo($id_departamento)
{

 
   return DB::table("orden_trabajo_clasificacion")
   ->join("orden_trabajo_clasificacion_departamento" , "orden_trabajo_clasificacion.id_orden_trabajo_clasificacion","orden_trabajo_clasificacion_departamento.id_orden_trabajo_clasificacion")
   ->where("orden_trabajo_clasificacion_departamento.id_departamento",$id_departamento)
   ->where("orden_trabajo_clasificacion.estatus",1)
   ->select("orden_trabajo_clasificacion.id_orden_trabajo_clasificacion","orden_trabajo_clasificacion.nombre")
   ->get();
}


public static function guardarCancelarFoto($id, $foto,$id_foto)
   {

      $orden_trabajo = OrdenTrabajo::find($id);
      if($id_foto==1)
      {
         $orden_trabajo->imagen_evidencia_1 = $foto??null;
      }
      else if($id_foto==2)
      {
         $orden_trabajo->imagen_evidencia_2 = $foto??null;
      }
      else {
         $orden_trabajo->imagen_evidencia_3 = $foto??null;
      }
      $ok = $orden_trabajo->save();

      return $ok;
   }

}



