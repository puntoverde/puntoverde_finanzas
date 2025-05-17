<?php
namespace App\DAO;
use App\Entity\Cuota;
use App\Entity\DatosFacturacion;
use App\Entity\Persona;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class PagosDAO {

    public function __construct(){}

    public static function getPagos($p){
     
     try {        
      ini_set('max_execution_time', 180);
        $pagos=DB::table('pago')
        ->join('persona','pago.persona_cobra','persona.cve_persona')
        ->join('cargo','pago.idpago','cargo.idpago')
        ->join('acciones','cargo.cve_accion','acciones.cve_accion')
        ->join('forma_pago','forma_pago.idpago','pago.idpago')
        ->join('forma_pago_sat','forma_pago_sat.clave','forma_pago.clave')
        ->Leftjoin('factura','pago.idpago','factura.idpago')
        ->select('pago.idpago','pago.folio','pago.subtotal','pago.descuento','pago.total','fecha_hora_cobro')
        ->addSelect(DB::raw('IFNULL(factura.uuid,0) AS uuid'),DB::raw('IFNULL(factura.folio_compaq,0) AS folio_compaq'))
        ->addSelect(DB::raw('IFNULL(estado_compaq,0) AS estado_compaq'),DB::raw('IFNULL(codigo_error_compaq,404) AS codigo_error_compaq'),'mensaje_error_compaq')
        ->addSelect(DB::raw("CONCAT(numero_accion,CASE clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion"))
        ->addSelect('acciones.cve_accion','forma_pago.clave AS forma_pago','forma_pago_sat.forma_pago AS forma_pago_text')
        ->addSelect(DB::raw("CONCAT_WS(' ',nombre,apellido_paterno,apellido_materno) AS nombre"))
        ->addSelect(DB::raw("IFNULL(factura.idfactura,0) factura_id"),DB::raw("IF(factura.id_datos_facturacion=0,1,0) AS global"))
        ->addSelect(DB::raw("COUNT(DISTINCT factura.idfactura) AS segmentada"))
        ->groupBy('pago.idpago')
        ->orderBy('pago.idpago')
        ->orderByRaw('MAX(forma_pago.monto)');

        if($p->numero_accion ?? false)
        {
          $pagos->where('acciones.numero_accion',$p->numero_accion);
        }

        if($p->clasificacion ?? false)
        {
          $pagos->where('acciones.clasificacion',$p->clasificacion);
        }

        if($p->uuid ?? false)
        {
          $pagos->where('factura.uuid',$p->uuid);
        }
        
        if(($p->fecha_inicio ?? false) && $p->fecha_fin ?? false)
       {
          $pagos->whereRaw('CONVERT(fecha_hora_cobro,DATE) BETWEEN ? AND ?',[$p->fecha_inicio,$p->fecha_fin]);
       }
      
      //  var_dump($pagos->toSql());
        return $pagos->get(); 

     } catch (\Exception $e) {
        return $e;
     }

   }

   public static function getCargosPago($id){
      return DB::table('cargo')
      ->join('cuota','cargo.cve_cuota','cuota.cve_cuota')
      ->leftJoin('descuento','cargo.cve_cargo','descuento.cve_cargo')
      ->where('idpago',$id)
      ->select('cuota.producto_servicio','concepto','periodo','cargo.cantidad','subtotal','total')
      ->selectRaw('IFNULL(monto,0) AS descuento')
      ->get();
   }


   public static function getDatosFactura($id){
      $factura=DB::table('factura')
      ->join('datos_facturacion','factura.id_datos_facturacion','datos_facturacion.id_datos_facturacion')
      ->join('forma_pago_sat','factura.forma_pago','forma_pago_sat.clave')
      ->where('idpago',$id)
      ->select('rfc','razon_social','correo','uso_cfdi','forma_pago_sat.forma_pago','metodo_pago','uuid')
      ->addSelect('cp','calle','colonia','num_ext','num_int','municipio','estado','pais','datos_facturacion.estatus');
      return $factura->get();
   }


   public static function getListaDatosFacturacion($p){
      $socios_factura= DB::table('datos_facturacion')
      ->join('socios','datos_facturacion.cve_persona','socios.cve_persona')
      ->where('cve_accion',$p->cve_accion)
      ->select('id_datos_facturacion','datos_facturacion.cve_persona','rfc','razon_social','correo','cp','calle','num_ext','num_int','colonia','municipio','estado','pais','datos_facturacion.estatus','regimen_fiscal');

      $duenos_factura= DB::table('datos_facturacion')
      ->join('dueno','datos_facturacion.cve_persona','dueno.cve_persona')
      ->join('acciones','dueno.cve_dueno','acciones.cve_dueno')
      ->where('cve_accion',$p->cve_accion)
      ->select('id_datos_facturacion','datos_facturacion.cve_persona','datos_facturacion.rfc','razon_social','correo','cp','calle','num_ext','num_int','colonia','municipio','estado','pais','datos_facturacion.estatus','regimen_fiscal');

      return $socios_factura->union($duenos_factura)->get();
   }

   public static function createDatosFactura($p)
   {
       return DB::transaction(function () use ($p) {

      $persona=Persona::find($p->cve_persona);
      $datosFacturacion= new DatosFacturacion();
      $datosFacturacion->rfc=$p->rfc;
      $datosFacturacion->razon_social=$p->razon_social;
      $datosFacturacion->correo=$p->correo;
      $datosFacturacion->cp=$p->cp;
      $datosFacturacion->calle=$p->calle;
      $datosFacturacion->num_ext=$p->num_ext;
      $datosFacturacion->num_int=$p->num_int;
      $datosFacturacion->colonia=$p->colonia;
      $datosFacturacion->municipio=$p->municipio;
      $datosFacturacion->estado=$p->estado;
      $datosFacturacion->pais=$p->pais;   
      $datosFacturacion->persona()->associate($persona);
      $datosFacturacion->save();


      $cve_accion=DB::table("socios")->where("cve_persona",$p->cve_persona)->limit(1)->value("cve_accion");  

      $id_factura=DB::table('factura')
      ->insertGetId([
                    "idpago"=>$p->idpago,
                    "id_datos_facturacion"=>$datosFacturacion->id_datos_facturacion,
                    "metodo_pago"=>$p->metodo_pago,
                    "uso_cfdi"=>$p->uso_cfdi,
                    "forma_pago"=>$p->forma_pago,
                    "cve_accion"=>$cve_accion                   
                    ]);

      return $id_factura;
   });
}

 public static function updateDatosFactura($id,$p)
   {
      return DB::transaction(function () use ($id,$p) {

      $persona=Persona::find($p->cve_persona);
      $datosFacturacion=DatosFacturacion::find($id);
      $datosFacturacion->rfc=$p->rfc;
      $datosFacturacion->razon_social=$p->razon_social;
      $datosFacturacion->correo=$p->correo;
      $datosFacturacion->cp=$p->cp;
      $datosFacturacion->calle=$p->calle;
      $datosFacturacion->num_ext=$p->num_ext;
      $datosFacturacion->num_int=$p->num_int;
      $datosFacturacion->colonia=$p->colonia;
      $datosFacturacion->municipio=$p->municipio;
      $datosFacturacion->estado=$p->estado;
      $datosFacturacion->pais=$p->pais;   
      $datosFacturacion->persona()->associate($persona);
      $datosFacturacion->save();

      $id_factura=DB::table('factura')
      ->insertGetId([
                    "idpago"=>$p->idpago,
                    "id_datos_facturacion"=>$datosFacturacion->id_datos_facturacion,
                    "metodo_pago"=>$p->metodo_pago,
                    "uso_cfdi"=>$p->uso_cfdi,
                    "forma_pago"=>$p->forma_pago                    
                    ]);

      return $id_factura;
   });
}

public static function createFactura($p)
   {     
      
      //obtiene accion
      $cve_accion=DB::table("datos_facturacion")
      ->leftJoin("socios","datos_facturacion.cve_persona","socios.cve_persona")
      ->leftJoin("dueno","datos_facturacion.cve_persona","dueno.cve_persona")
      ->leftJoin("acciones","dueno.cve_dueno","acciones.cve_dueno")
      ->where("datos_facturacion.id_datos_facturacion",$p->id_datos_facturacion)
      ->selectRaw("IFNULL(socios.cve_accion,acciones.cve_accion) AS cve_accion")
      ->limit(1)
      ->value("cve_accion");    

      $id_factura=DB::table('factura')
      ->insertGetId([
                    "idpago"=>$p->idpago,
                    "id_datos_facturacion"=>$p->id_datos_facturacion,
                    "metodo_pago"=>$p->metodo_pago,
                    "uso_cfdi"=>$p->uso_cfdi,
                    "forma_pago"=>$p->forma_pago,
                    "cve_accion"=>$cve_accion,
                    "fecha_factura"=>Carbon::now("America/Mexico_City")                                     
                    ]);

      return $id_factura;
 
}

public static function updateFactura($idfactura,$p)
   {
      $affected = DB::table('factura')
              ->where('idfactura', $idfactura)
              ->update([
              "uuid" => $p->uuid,
              "estado_compaq"=>$p->estado,
              "folio_compaq"=>$p->folio,
              "codigo_error_compaq"=>$p->IError,
              "mensaje_error_compaq"=>$p->IErrorMessage]);

      return $affected;
 
}

public static function updateFacturaEstado($idfactura,$p)
   {
      $affected = DB::table('factura')
              ->where('idfactura', $idfactura)
              ->update([
              "estado_compaq"=>$p->estado,
              "codigo_error_compaq"=>$p->IError,
              "mensaje_error_compaq"=>$p->IErrorMessage]);

      return $affected;
 
}

public static function getFormaPago()
{
   return DB::table('forma_pago_sat')
   ->where("estatus",1)
   ->select("clave","forma_pago")
   ->get();
} 

public static function getUsoCfdi()
{
   return DB::table('uso_cfdi_sat')
   ->join("regimen_fiscal_uso_cfdi","regimen_fiscal_uso_cfdi.uso_cfdi","uso_cfdi_sat.clave")
   ->select("clave","descripcion",DB::raw("GROUP_CONCAT(regimen_fiscal_uso_cfdi.regimen_fiscal ORDER BY regimen_fiscal_uso_cfdi.regimen_fiscal) AS regimenes"))
   ->groupBy("uso_cfdi_sat.clave")
   ->get();   
} 

public static function getSociosAccion($p)
{
  $socios =DB::table('socios')
      ->join('persona','socios.cve_persona','persona.cve_persona')
      ->where('socios.estatus',1)
      ->where('cve_accion',$p->cve_accion)
      ->select('cve_socio','persona.cve_persona')
      ->selectRaw("CONCAT_WS(' ',nombre,apellido_paterno,apellido_materno) AS nombre")
      ->addSelect(DB::raw("1 AS tipo"));      

  $dueno =DB::table('dueno')
      ->join('persona','dueno.cve_persona','persona.cve_persona')
      ->join('acciones','dueno.cve_dueno','acciones.cve_dueno')
      ->where('dueno.estatus',1)
      ->where('acciones.cve_accion',$p->cve_accion)
      ->select('dueno.cve_dueno','persona.cve_persona')
      ->selectRaw("CONCAT_WS(' ',nombre,apellido_paterno,apellido_materno) AS nombre")
      ->addSelect(DB::raw("0 AS tipo"));

   return $socios->union($dueno)->get();
} 

public static function eliminarFactura($id,$p)
{
   DB::transaction(function() use($id,$p){
      DB::statement("INSERT INTO cancelar_factura(idfactura,idpago,id_datos_facturacion,metodo_pago,uso_cfdi,forma_pago,uuid,estado_compaq,folio_compaq,codigo_error_compaq,mensaje_error_compaq,cve_accion,fecha_factura,motivo,cve_persona) 
      SELECT idfactura,idpago,id_datos_facturacion,metodo_pago,uso_cfdi,forma_pago,uuid,estado_compaq,folio_compaq,codigo_error_compaq,mensaje_error_compaq,cve_accion,fecha_factura,:motivo,:cve_persona FROM factura WHERE idfactura=:id",["id"=>$id,"motivo"=>$p->motivo,"cve_persona"=>$p->idpersona]);
      DB::table("factura")->where("idfactura",$id)->delete();
   });
}

}