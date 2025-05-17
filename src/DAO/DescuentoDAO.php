<?php
namespace App\DAO;
use App\Entity\Accion;
use Illuminate\Support\Facades\DB;


class DescuentoDAO {

    public function __construct(){}

    public static function getCargos($p){
        $accion=DB::table("acciones")->where("numero_accion",$p->numero_accion)->where("clasificacion",$p->clasificacion)->value("cve_accion");
        $cargos=DB::table("cargo")
        ->join('acciones','cargo.cve_accion','acciones.cve_accion')
        ->join('persona','cargo.cve_persona','persona.cve_persona')
        ->leftJoin("descuento","cargo.cve_cargo","descuento.cve_cargo")
        ->select('cargo.cve_cargo','persona.cve_persona')
        ->SelectRaw("IFNULL(iddescuento,0) AS iddescuento")
        ->addSelect('concepto','cargo.total', 'periodo', 'fecha_cargo','cargo.estatus') 
        ->addSelect(DB::raw('IFNULL(monto,0) AS descuento'))
        ->addSelect(DB::raw("CONCAT_WS(' ',nombre,apellido_paterno,apellido_materno) AS persona"))
        ->whereNull("cargo.idpago")
        ->where("acciones.numero_accion",$p->numero_accion)
        ->where("acciones.clasificacion",$p->clasificacion)
        ->get();

        return ["cve_accion"=>$accion,"cargos"=>$cargos];
   }

   public static function cuotasObligatorias(){
      /**
       SELECT 
         cuota.cve_cuota,
         descripcion,
         tipo_cuota,
         IFNULL(GROUP_CONCAT(DISTINCT cve_parentesco),0) parentescos, 
         IFNULL(GROUP_CONCAT(DISTINCT cve_tipo_accion),0) membresias,
         cantidad 
         FROM cuota
         LEFT JOIN cuota_accion ON cuota.cve_cuota=cuota_accion.cve_cuota
         LEFT JOIN cuota_parentesco ON cuota.cve_cuota=cuota_parentesco.cve_cuota
         WHERE obligatoria=1 GROUP BY cve_cuota
       */
      return DB::table("cuota")
        ->leftJoin('cuota_accion','cuota.cve_cuota','cuota_accion.cve_cuota')
        ->leftJoin('cuota_parentesco','cuota.cve_cuota','cuota_parentesco.cve_cuota')        
        ->select('cuota.cve_cuota','descripcion','tipo_cuota','cantidad','edad_aplica')
        ->addSelect(DB::raw('IFNULL(GROUP_CONCAT(DISTINCT cve_parentesco),0) parentescos'))
        ->addSelect(DB::raw("IFNULL(GROUP_CONCAT(DISTINCT cve_tipo_accion),0) membresias"))
        ->where("obligatoria",1)
        ->groupBy("cve_cuota")
        ->get();
 }

   public static function sociosAplicaCuota($p){
      /**
       SELECT 
         cve_socio,
         persona.cve_persona,
         nombre,
         apellido_paterno,
         apellido_materno,
         sexo,
         fecha_nacimiento,
         cve_parentesco, 
         TIMESTAMPDIFF(YEAR,persona.fecha_nacimiento, NOW()) AS edad,
         IFNULL(GROUP_CONCAT(descuento_programado.periodo),'') AS periodos,
         IFNULL(descuento_programado.monto,0) AS monto
         FROM socios
         INNER JOIN persona on socios.cve_persona=persona.cve_persona
         LEFT JOIN descuento_programado on socios.cve_persona=descuento_programado.cve_persona
         WHERE socios.cve_accion=471 AND 
         TIMESTAMPDIFF(YEAR,persona.fecha_nacimiento, NOW())>= 24 AND cve_parentesco IN(3)
         GROUP BY persona.cve_persona
         ORDER BY descuento_programado.periodo
       */
      return DB::table("socios")
        ->join('persona','socios.cve_persona','persona.cve_persona')
        ->leftJoin('descuento_programado','socios.cve_persona','descuento_programado.cve_persona')        
        ->select('cve_socio','persona.cve_persona','nombre','apellido_paterno','apellido_materno')
        ->addSelect('sexo','fecha_nacimiento','cve_parentesco')
        ->addSelect(DB::raw('TIMESTAMPDIFF(YEAR,persona.fecha_nacimiento, NOW()) AS edad'))
        ->addSelect(DB::raw("IFNULL(GROUP_CONCAT(descuento_programado.periodo),'') AS periodos"))
        ->addSelect(DB::raw("IFNULL(descuento_programado.monto,0) AS monto"))
        ->where("socios.cve_accion",$p->cve_accion)
        ->whereIn('cve_parentesco',$p->parentesco)
        ->whereRaw('TIMESTAMPDIFF(YEAR,persona.fecha_nacimiento, NOW())>= ?',[$p->edad])
        ->groupBy("persona.cve_persona")
        ->orderBy("descuento_programado.periodo")
        ->get();

   }

   public static function duenoAplicaCuota($p){
      /**
       SELECT 
       dueno.cve_dueno,
	    persona.cve_persona,
	    nombre,
	    apellido_paterno,
	    apellido_materno, 
	    IFNULL(GROUP_CONCAT(descuento_programado.periodo),'') AS periodos,
       IFNULL(descuento_programado.monto,0) AS monto
       FROM dueno
       INNER JOIN persona ON dueno.cve_persona=persona.cve_persona
       INNER JOIN acciones on dueno.cve_dueno=acciones.cve_dueno
       LEFT JOIN descuento_programado ON dueno.cve_persona=descuento_programado.cve_persona
       WHERE acciones.cve_accion=1 AND cve_tipo_accion IN(2,3);
       */
      return DB::table("dueno")
        ->join('persona','dueno.cve_persona','persona.cve_persona')
        ->join('acciones','dueno.cve_dueno','acciones.cve_dueno')
        ->leftJoin('descuento_programado','dueno.cve_persona','descuento_programado.cve_persona')        
        ->select('dueno.cve_dueno','persona.cve_persona','nombre','apellido_paterno','apellido_materno')
        ->addSelect(DB::raw("IFNULL(GROUP_CONCAT(descuento_programado.periodo),'') AS periodos"))
        ->addSelect(DB::raw("IFNULL(descuento_programado.monto,0) AS monto"))
        ->where("acciones.cve_accion",$p->cve_accion)
        ->whereIn('cve_tipo_accion',$p->membresias)
        ->first();
   }

   public static function aplicarDescuento($p)
    {
       /**
        * 
         ejemplo de como calcula el descuento cargos de prueba por 2000 , 3000 y 500 un total de 5500
         los 5500 que son la suma de los montos de los cargos forma mi 100%

         sigue sacar los porcentajes de de cada cantidad del cargo 
         2,000 por 100 igual a 200,000 se divide entre 5500 que es el 100% y el resultado es 36.36 ose el (36.36%)
         3,000 por 100 igual a 300,000 se divide entre 5500 que es el 100% y el resultado es 54.55 ose el (54.55%)
         500 por 100 igual a 50,000 se divide entre 5500 que es el 100% y el resultado es 9.09 ose el (9.09%)

         siguiente paso es tomar la cantidad que se descontara para dividirla en los cargos segun el porcentaje 
         se tomara que la cantidad para este ejemplo es de 1,500 (total descuento)
         el primer porcentaje de 2,000 es el 36.36%
           - 1500 por 36.36 resulta en 54,540 y esto se divide entre 100 dando el descuento de 545.5
         el segundo porcentaje de 3,000 es el 54.55%
           - 1500 por 54.55 resulta en 81,825 y esto se divide entre 100 dando el descuento de 818.25
         el tercer porcentaje de 500 es el 9.09%
           - 1500 por 9.09 resulta en 13,635 y esto se divide entre 100 dando el descuento de 136.35

         esos son los descuentos se suman para ver si juntos dan 1500 
         545.5+818.25+136.35=1500.1 se pasa por una decima por los decimales....

        */
      
        return DB::transaction(function() use($p){
          
         //se mapea para sacar un array solo de los montos
         $map_cargos=array_map(function($item){return $item["monto"];},$p->cargos);
         //se suman los montos dando el total ejemplo 5500 este seria el 100%
         $total=array_reduce($map_cargos,function($red,$val){return floatval($red)+floatval($val);},0);
         
         if($total>=floatval($p->total_descuento)){

         // se saca el porcentaje de cada cargo 
         $descuentos=array_map(function($item)use($total,$p)
         {  $porcentaje=(floatval($item["monto"])*100)/$total;
            $descuento=round((floatval($p->total_descuento)*$porcentaje)/100,2);
            return ["cve_cargo"=>$item["cve_cargo"],"monto"=>$descuento,"persona_otorga"=>$p->responsable,"descripcion"=>$p->descripcion,"fecha_aplicacion"=>(new \DateTime())->setTimezone(new \DateTimeZone('America/Mexico_City'))->format("Y-m-d H:i:s")];
         },$p->cargos);

          //se mapea para sacar solo el monto del descuento ya asignado
          $map_cargos_desc=array_map(function($item){return $item["monto"];},$descuentos);
          //se suman los montos dando el total ejemplo 5500 este seria el 100%
          $total_desc=array_reduce($map_cargos_desc,function($red,$val){return floatval($red)+floatval($val);},0);

          //si la suma de los descuentos es mayo a el descuento se restara lo restante de algun caqrgo 
          if($total_desc>$p->total_descuento)
          {
            $descuentos[0]["monto"]=$descuentos[0]["monto"]-round($total_desc-$p->total_descuento,2);
          }
          else if($total_desc<$p->total_descuento)
          {
            $descuentos[0]["monto"]=$descuentos[0]["monto"]+round($p->total_descuento-$total_desc,2);
          }
          
          return DB::table("descuento")->insert($descuentos);

        }//fin se comparar la suma de los cargos y el total enviado 

        else {
          return ["Error"=>"el total de los cargos es (".$total.") y esta siempre debe de ser igual o mayor que el monto del descuento (".$p->total_descuento.")"];
        }

    });
   
   }


   public static function DescuentosProgramados($p)
   {

      return DB::transaction(function() use($p){
         
         $descuentos=array_map(function($periodo) use($p){
            return ["cve_accion"=>$p->cve_accion,"cve_persona"=>$p->cve_persona,"cve_cuota"=>$p->cve_cuota,"cve_persona_aplica"=>$p->cve_persona_aplica,"monto"=>$p->monto,"periodo"=>$periodo,"fecha_aplicacion"=>(new \DateTime())->setTimezone(new \DateTimeZone('America/Mexico_City'))->format("Y-m-d H:i:s")];
         },$p->periodos);

         return DB::table("descuento_programado")->insert($descuentos);
      });

       }

}