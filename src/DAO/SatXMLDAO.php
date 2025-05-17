<?php
namespace App\DAO;
use Illuminate\Support\Facades\DB;


class SatXMLDAO {

    public function __construct(){}

  //guardar datos de los xmls
   public static function createDatos($p,$o){

      DB::table("factura_xml")->insert($p);
      DB::table("factura_xml_conceptos")->insert($o);
      
   }

   public static function createDatosComplementarios($p,$o,$i,$d){
        
      DB::table("factura_xml_complemento")->insert($p);
      DB::table("factura_xml_conceptos_complemento")->insert($o);
      DB::table("factura_xml_pagos")->insert($i);
      DB::table("factura_xml_docto")->insert($d);
      
   }

   
   public static function createDatosNomina($p,$o){
      
  
      DB::table("factura_xml_nomina")->insert($p);
      DB::table("factura_xml_nomina_extras")->insert($o);
      
   }

   public static function createDatosEmitidos($p,$o){
        
      DB::table("factura_xml_emitido")->insert($p);
      DB::table("factura_xml_conceptos_emitido")->insert($o);
      
   }


   //obtener los datos
   public static function getDatosXML($p)
   {      
      $data= DB::table("factura_xml")->where("estatus",1);

      if($p->rfc??false)$data->where("rfc_emisor",$p->rfc);
      if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
      if($p->fecha_incio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);      
      
      return $data->get();
   }

   public static function getDatosXMLComplementarios($p)
   {      
      $data= DB::table("factura_xml_complemento")->where("estatus",1);

      if($p->rfc??false)$data->where("rfc_emisor",$p->rfc);
      if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
      if($p->fecha_incio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);
      return $data->get();
   }

   public static function getConceptosXML($id)
   {
      return DB::table("factura_xml_conceptos")->where("estatus",1)->where("uuid",$id)->get();
   }

   public static function getConceptosXMLComplementarios($id)
   {
      return DB::table("factura_xml_conceptos_complemento")->where("estatus",1)->where("uuid",$id)->get();
   }

   public static function getDatosNominaXML($p)
   {      
      $data= DB::table("factura_xml_nomina")->where("estatus",1);

      if($p->rfc??false)$data->where("rfc_emisor",$p->rfc);
      if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
      if($p->fecha_incio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);      
      
      return $data->get();
   }

   public static function getDatosNominaExtraXML($id)
   {   
      return DB::table("factura_xml_nomina_extras")
      ->select(DB::raw("CASE tipo WHEN 'percepcion' THEN tipopercepcion WHEN 'deduccion' THEN tipodeduccion WHEN 'otro_pago' THEN tipootropago ELSE '' END AS clave_tipo")
      ,"tipo","clave","concepto",
      DB::raw("CASE tipo WHEN 'percepcion' THEN if(importegravado='0.00',importeexento,importegravado) WHEN 'deduccion' THEN importe WHEN 'otro_pago' THEN importe WHEN 'subsidio' THEN subsidiocausado ELSE '' END AS importe"))
      ->where("uuid",$id)->where("estatus",1)->get();
   }

   public static function getDatosEmitidoXML($p)
   {      
      $data= DB::table("factura_xml_emitido")->where("estatus",1);

      if($p->rfc??false)$data->where("rfc_emisor",$p->rfc);
      if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
      if($p->fecha_incio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);      
      
      return $data->get();
   }

   public static function getEmitidoConceptosXML($id)
   {
      return DB::table("factura_xml_conceptos_emitido")->where("uuid",$id)->where("estatus",1)->get();
   }

    //metodos get datos generar excel
   public static function getNominaXML($p)
   {
      
      $lst_tipo=DB::table("factura_xml_nomina_extras")
      ->select("tipo",DB::raw("IF(concepto='',tipo,concepto) AS concepto"),DB::raw("CONCAT_WS(' ',tipo,concepto) AS concepto_header"))
      ->groupBy(["tipo","concepto"])
      ->where("estatus",1)
      ->get();

      $lst_nomina=DB::table("factura_xml_nomina")
      ->select("serie_comprobante","folio_comprobante","formapago_comprobante",
      "lugarexpedicion","metodopago_comprobante","totalotrospagos_nomina",
      "subtotal_comprobante","total_comprobante","tiponomina_nomina",
      "version_comprobante","nombre_emisor","regimenfiscal_emisor","rfc_emisor",
      "domiciliofiscalreceptor_receptor","nombre_receptor","regimenfiscalreceptor_receptor",
      "rfc_receptor","usocfdi_receptor","uuid_timbre","fecha_comprobante","fechatimbrado_timbre",
      "tiponomina_nomina","fechapago_nomina","fechainicialpago_nomina","fechafinalpago_nomina","file_name");

      $lst_nomina->where("estatus",1);

      if($p->rfc??false)$lst_nomina->where("rfc_emisor",$p->rfc);
      if($p->razonsocial??false)$lst_nomina->where("nombre_emisor",$p->razonsocial);
      if($p->fecha_incio??false && $p->fecha_fin??false)$lst_nomina->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);
     
      
      $uuid_in=$lst_nomina->get()->map(function($i){return $i->file_name;})->toArray();

      $lst_nomina_extra=DB::table("factura_xml_nomina_extras")
      ->select(
         "uuid",
         "tipo",
         "clave",
         DB::raw("IF(concepto='',tipo,concepto) AS concepto"),
         DB::raw("CASE tipo WHEN 'percepcion' THEN tipopercepcion WHEN 'deduccion' THEN tipodeduccion WHEN 'otro_pago' THEN tipootropago ELSE '' END AS clave_tipo"),
         DB::raw("CASE tipo WHEN 'percepcion' THEN if(importegravado='0.00',importeexento,importegravado) WHEN 'deduccion' THEN importe WHEN 'otro_pago' THEN importe WHEN 'subsidio' THEN subsidiocausado ELSE '' END AS importe"))
      ->whereIn("uuid",$uuid_in)->where("estatus",1)->get();      

      return ["headers"=>$lst_tipo,"body"=>$lst_nomina->get(),"body_extra"=>$lst_nomina_extra];

   }

   public static function getDataExcelFactura($p)
   {
     $data= DB::table("factura_xml")->select("serie_comprobante","folio_comprobante","formapago_comprobante","lugarexpedicion_comprobante","metodopago_comprobante","moneda_comprobante",
     "subtotal_comprobante","total_comprobante","tipodecomprobante_comprobante","version_comprobante","nombre_emisor","regimenfiscal_emisor",
     "rfc_emisor","domiciliofiscalreceptor_receptor","nombre_receptor","regimenfiscalreceptor_receptor","rfc_receptor","usocfdi_receptor","uuid_timbre",
     "fecha_comprobante","fechatimbrado_timbre","file_name");

     $data->where("estatus",1);

     if($p->rfc??false)$data->where("rfc_emisor",$p->rfc);
     if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
     if($p->fecha_incio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);

     $uuid_in=$data->get()->map(function($i){return $i->file_name;})->toArray();

     $conceptos_data=DB::table("factura_xml_conceptos")->select(
      "claveprodserv","claveunidad","descripcion","unidad","cantidad","valorunitario","base_impuestos","importe_impuestos","tasaocuota_impuestos","importe"
     )->whereIn("uuid",$uuid_in)->where("estatus",1);
     
   
   
     return ["factura"=>$data->get(),"conceptos"=>$conceptos_data->get()];
   }


   public static function getDataExcelFacturaComplementos($p)
   {
      $data= DB::table("factura_xml_complemento")->select("serie_comprobante","folio_comprobante","formapago_comprobante","lugarexpedicion_comprobante","metodopago_comprobante","moneda_comprobante",
      "subtotal_comprobante","total_comprobante","tipodecomprobante_comprobante","version_comprobante","nombre_emisor","regimenfiscal_emisor",
      "rfc_emisor","domiciliofiscalreceptor_receptor","nombre_receptor","regimenfiscalreceptor_receptor","rfc_receptor","usocfdi_receptor","uuid_timbre",
      "fecha_comprobante","fechatimbrado_timbre","file_name");

      $data->where("estatus",1);

     if($p->rfc??false)$data->where("rfc_emisor",$p->rfc);
     if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
     if($p->fecha_incio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);

     
     $uuid_in=$data->get()->map(function($i){return $i->file_name;})->toArray();

     $conceptos_data=DB::table("factura_xml_conceptos_complemento")->select(
      "claveprodserv","claveunidad","descripcion","unidad","cantidad","valorunitario","base_impuestos","importe_impuestos","tasaocuota_impuestos","importe"
     )->whereIn("uuid",$uuid_in)->where("estatus",1);   


     return ["factura"=>$data->get(),"conceptos"=>$conceptos_data->get()];
   }

   public static function getDataExcelFacturaEmitida($p)
   {
     $data= DB::table("factura_xml_emitido")->select("serie_comprobante","folio_comprobante","formapago_comprobante","lugarexpedicion_comprobante","metodopago_comprobante","moneda_comprobante",
     "subtotal_comprobante","total_comprobante","tipodecomprobante_comprobante","version_comprobante","nombre_emisor","regimenfiscal_emisor",
     "rfc_emisor","domiciliofiscalreceptor_receptor","nombre_receptor","regimenfiscalreceptor_receptor","rfc_receptor","usocfdi_receptor","uuid_timbre",
     "fecha_comprobante","fechatimbrado_timbre","file_name");

     $data->where("estatus",1);

     if($p->rfc??false)$data->where("rfc_emisor",$p->rfc);
     if($p->razonsocial??false)$data->where("nombre_emisor",$p->razonsocial);
     if($p->fecha_incio??false && $p->fecha_fin??false)$data->whereRaw("CONVERT(fecha_comprobante,DATE) BETWEEN ? AND ?",[$p->fecha_incio,$p->fecha_fin]);

     $uuid_in=$data->get()->map(function($i){return $i->file_name;})->toArray();

     $conceptos_data=DB::table("factura_xml_conceptos_emitido")->select(
      "claveprodserv","claveunidad","descripcion","unidad","cantidad","valorunitario","base_impuestos","importe_impuestos","tasaocuota_impuestos","importe"
     )->whereIn("uuid",$uuid_in)->where("estatus",1); 

     return ["factura"=>$data->get(),"conceptos"=>$conceptos_data->get()];
   }

   public static function cancelarDocumento($file_name)
   {
     DB::table("factura_xml")->where("file_name",$file_name)->update(["estatus"=>0]);
     DB::table("factura_xml_conceptos")->where("uuid",$file_name)->update(["estatus"=>0]);
   }

   public static function cancelarDocumentoComplemento($file_name)
   {
     DB::table("factura_xml_complemento")->where("file_name",$file_name)->update(["estatus"=>0]);
     DB::table("factura_xml_conceptos_complemento")->where("uuid",$file_name)->update(["estatus"=>0]);
     DB::table("factura_xml_pagos")->where("uuid",$file_name)->update(["estatus"=>0]);
     DB::table("factura_xml_docto")->where("uuid",$file_name)->update(["estatus"=>0]);
   }

   public static function cancelarDocumentoNomina($file_name)
   {
     DB::table("factura_xml_nomina")->where("file_name",$file_name)->update(["estatus"=>0]);
     DB::table("factura_xml_nomina_extras")->where("uuid",$file_name)->update(["estatus"=>0]);
   }

   public static function cancelarDocumentoEmitido($file_name)
   {
      dd($file_name);
     DB::table("factura_xml_emitido")->where("file_name",$file_name)->update(["estatus"=>0]);
     DB::table("factura_xml_conceptos_emitido")->where("uuid",$file_name)->update(["estatus"=>0]);
   }

   public static function validarDuplicado($data,$conceptos){

       $data_in=$data->map(function($i){return $i["file_name"];})->toArray();
       $duplicados= DB::table("factura_xml")->select("file_name")->whereIn("file_name",$data_in)->groupBy("file_name")->get()->pluck("file_name")->toArray();
       $insertar=$data->where("file_name")->whereNotIn("file_name",$duplicados);
       $insertar_whereIn=$insertar->map(function($i){return $i["file_name"];})->toArray();
       $conceptos_insert=$conceptos->whereIn("uuid",$insertar_whereIn);
       return ["doc"=>$insertar->toArray(),"concep"=>$conceptos_insert->toArray()];
   }

   public static function validarDuplicadoComplemento($data,$conceptos,$pagos,$doc_relac){

      $data_in=$data->map(function($i){return $i["file_name"];})->toArray();
      
      $duplicados= DB::table("factura_xml_complemento")->select("file_name")->whereIn("file_name",$data_in)->groupBy("file_name")->get()->pluck("file_name")->toArray();
      
      $insertar=$data->where("file_name")->whereNotIn("file_name",$duplicados);
      
      $insertar_whereIn=$insertar->map(function($i){return $i["file_name"];})->toArray();
      
      $conceptos_insert=$conceptos->whereIn("uuid",$insertar_whereIn);
      $pagos_insert=$pagos->whereIn("uuid",$insertar_whereIn);
      $doc_relac_insert=$doc_relac->whereIn("uuid",$insertar_whereIn);

      return ["doc"=>$insertar->toArray(),"concep"=>$conceptos_insert->toArray(),"pagos"=>$pagos_insert->toArray(),"doc_rel"=>$doc_relac_insert->toArray()];
  }

  public static function validarDuplicadoNomina($data,$conceptos){

   // dd($conceptos);

   $data_in=$data->map(function($i){return $i["file_name"];})->toArray();
   $duplicados= DB::table("factura_xml_nomina")->select("file_name")->whereIn("file_name",$data_in)->groupBy("file_name")->get()->pluck("file_name")->toArray();
   $insertar=$data->where("file_name")->whereNotIn("file_name",$duplicados);
   $insertar_whereIn=$insertar->map(function($i){return $i["file_name"];})->toArray();
   $conceptos_insert=$conceptos->whereIn("uuid",$insertar_whereIn);
   return ["doc"=>$insertar->toArray(),"extras"=>$conceptos_insert->toArray()];
}

public static function validarDuplicadoEmitido($data,$conceptos){

   $data_in=$data->map(function($i){return $i["file_name"];})->toArray();
   $duplicados= DB::table("factura_xml_emitido")->select("file_name")->whereIn("file_name",$data_in)->groupBy("file_name")->get()->pluck("file_name")->toArray();
   $insertar=$data->where("file_name")->whereNotIn("file_name",$duplicados);
   $insertar_whereIn=$insertar->map(function($i){return $i["file_name"];})->toArray();
   $conceptos_insert=$conceptos->whereIn("uuid",$insertar_whereIn);
   return ["doc"=>$insertar->toArray(),"concep"=>$conceptos_insert->toArray()];
}

}