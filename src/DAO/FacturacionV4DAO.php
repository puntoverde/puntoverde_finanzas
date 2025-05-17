<?php

namespace App\DAO;

use App\Entity\Persona;
use App\Entity\DatosFacturacion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class FacturacionV4DAO
{

   public function __construct()
   {
   }

   public static function createFactura($id_accion,$p,$contpaq)
   {
      
      try{
      $id_factura=DB::table('factura')
      ->insertGetId([
                    "idpago"=>$p->idpago,
                    "id_datos_facturacion"=>$p->id_dato_factura,
                    "metodo_pago"=>"PUE",
                    "uso_cfdi"=>$p->usoCFDI,
                    "forma_pago"=>$p->metodoPago,//credito, debito etc
                    "cve_accion"=>$id_accion,
                    "fecha_factura"=>Carbon::now("America/Mexico_City"),
                    "uuid"=>$contpaq->uuid??"-",
                    "estado_compaq"=>$contpaq->estado??0,
                    "folio_compaq"=>$contpaq->folio??"0",
                    "codigo_error_compaq"=>$contpaq->IError??0,
                    "mensaje_error_compaq"=>$contpaq->IErrorMessage??"es null"
                    ]); 
     
      return $id_factura;
                  }
                  catch(\Exception $e)
                  {
                   echo $e->getMessage();
                  }
   }

   public static function createFacturaFail($id_accion,$p,$contpaq)
   {
      
      try{
      $id_factura=DB::table('factura_fail')
      ->insertGetId([
                    "idpago"=>$p->idpago,
                    "id_datos_facturacion"=>$p->id_dato_factura,
                    "metodo_pago"=>"PUE",
                    "uso_cfdi"=>$p->usoCFDI,
                    "forma_pago"=>$p->metodoPago,//credito, debito etc
                    "cve_accion"=>$id_accion,
                    "fecha_factura"=>Carbon::now("America/Mexico_City"),
                    "uuid"=>$contpaq->uuid??"-",
                    "estado_compaq"=>$contpaq->estado??0,
                    "folio_compaq"=>$contpaq->folio??"0",
                    "codigo_error_compaq"=>$contpaq->IError??0,
                    "mensaje_error_compaq"=>$contpaq->IErrorMessage??"es null"
                    ]); 
     
      return $id_factura;
                  }
                  catch(\Exception $e)
                  {
                   echo $e->getMessage();
                  }
   }

   public static function createDatosFactura($id,$p)
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

   public static function createDatosFacturacion($cliente,$domicilio,$pertenece)
   {
         try{
         $persona=Persona::find($pertenece);
         $datosFacturacion= new DatosFacturacion();
         $datosFacturacion->rfc=$cliente->rfc;
         $datosFacturacion->razon_social=$cliente->razonSocial;
         $datosFacturacion->correo=$cliente->correo;
         $datosFacturacion->regimen_fiscal=$cliente->regimenFiscal;
         $datosFacturacion->cp=$domicilio->cp;
         $datosFacturacion->calle=$domicilio->calle;
         $datosFacturacion->num_ext=$domicilio->numExt;
         $datosFacturacion->num_int=$domicilio->numInt;
         $datosFacturacion->colonia=$domicilio->colonia;
         $datosFacturacion->municipio=$domicilio->municipio;
         $datosFacturacion->estado=$domicilio->estado;
         $datosFacturacion->pais=$domicilio->pais;   
         $datosFacturacion->persona()->associate($persona);
         $datosFacturacion->save();
   
         return $datosFacturacion->id_datos_facturacion;
         }
         catch(\Exception $e)
         {
            echo $e->getMessage();
            return 0;
         }
   }

   public static function updateDatosFacturacion($id,$cliente,$domicilio,$pertenece)
   {

      try{
         $persona=Persona::find($pertenece);
         $datosFacturacion= DatosFacturacion::find($id);
         $datosFacturacion->rfc=$cliente->rfc;
         $datosFacturacion->razon_social=$cliente->razonSocial;
         $datosFacturacion->correo=$cliente->correo;
         $datosFacturacion->regimen_fiscal=$cliente->regimenFiscal;
         $datosFacturacion->cp=$domicilio->cp;
         $datosFacturacion->calle=$domicilio->calle;
         $datosFacturacion->num_ext=$domicilio->numExt;
         $datosFacturacion->num_int=$domicilio->numInt;
         $datosFacturacion->colonia=$domicilio->colonia;
         $datosFacturacion->municipio=$domicilio->municipio;
         $datosFacturacion->estado=$domicilio->estado;
         $datosFacturacion->pais=$domicilio->pais;   
         $datosFacturacion->persona()->associate($persona);
         $datosFacturacion->save();
   
         return $datosFacturacion->id_datos_facturacion;
         }
         catch(\Exception $e)
         {
            echo $e->getMessage();
            return 0;
         }


   //   $rowAffect=DB::table("datos_facturacion")
   //   ->where("id_datos_facturacion",$id)
   //   ->update([
   //    'razon_social' => mb_strtoupper($p->razon_social),
   //    'rfc'=>mb_strtoupper($p->rfc),
   //    'correo'=>mb_strtolower($p->correo),
   //    'cp'=>$p->cp,
   //    'calle'=>mb_strtoupper($p->calle),
   //    'num_ext'=>mb_strtoupper($p->num_ext),
   //    'num_int'=>mb_strtoupper($p->num_int),
   //    'colonia'=>mb_strtoupper($p->colonia),
   //    'municipio'=>mb_strtoupper($p->municipio),
   //    'estado'=>mb_strtoupper($p->estado),
   //    'pais'=>mb_strtoupper($p->pais),
   //    'estatus'=>1,
   //    'regimen_fiscal'=>$p->regimen_fiscal
   //   ]);

   //   if(!$rowAffect) return 0;
   //   else return $rowAffect;
   } 

   
   public static function bajaDatosFacturacion($id,$p)
   {
      DB::table("datos_facturacion")->where("id_datos_facturacion",$id)->update(["estatus"=>0]);
   }  
   
   public static function getSociosPG($id)
   {

       $socios=DB::table('socios')
       ->join('persona','socios.cve_persona','persona.cve_persona')
       ->addSelect(DB::raw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS socio"))
       ->where('socios.estatus',1)
       ->where('socios.cve_accion',$id);

       $dueno=DB::table('dueno')
       ->join('persona','dueno.cve_persona','persona.cve_persona')
       ->join('acciones' , 'dueno.cve_dueno','acciones.cve_dueno')
       ->addSelect(DB::raw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS socio"))
       ->where('dueno.estatus',1)
       ->whereIn('acciones.estatus', [1, 2])
       ->where('acciones.cve_accion',$id);

       return $socios->union($dueno)->distinct("socio")->get();
   }
   
}
