<?php

namespace App\Controllers;

use XMLElementIterator;
use \XMLWriter;
use \XMLReader;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
// use Symfony\Component\HttpFoundation\StreamedResponse;
use App\DAO\SatXMLDAO;
use Exception;

use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;



use Laravel\Lumen\Routing\Controller;

class SatXMLController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    //lectura de los xml para insertar
    public function leerXML(Request $req)
    {

        $filesXML = $req->file("filexml");        
        $list_xml = collect();
        $conceptos = collect();
        $list_nomina = collect();

        $list_xml_complemento = collect();
        $conceptos_complemento = collect();

        $pagos_complemento = collect();
        $docs_relacionado = collect();

        foreach ($filesXML as $fileXML) {

            $factura = collect();

            $uuid = $fileXML->getClientOriginalName();

            $complementaria = "";

            $xml = simplexml_load_file($fileXML);
            $ns = $xml->getNamespaces(true);

            // $xml->registerXPathNamespace("cfdi",$ns["cfdi"]);
            // $xml->registerXPathNamespace("tfd",$ns["tfd"]);
            // $xml->registerXPathNamespace("pago",$ns["pago10"]);
            // $xml->registerXPathNamespace("pago10","http://www.sat.gob.mx/Pagos");
            // $xml->registerXPathNamespace("pago20","http://www.sat.gob.mx/Pagos20");

            foreach ($ns as $key => $value) {
                
                if (preg_match('/pago/i', $key))
                    $xml->registerXPathNamespace("pago", (string)$value);
                else if (preg_match('/nomina/i', $key))
                    $xml->registerXPathNamespace("nomina", (string)$value);
                else
                    $xml->registerXPathNamespace((string)$key, (string)$value);
            }




            foreach ($xml->attributes() as $comprobante) {                
                $factura->put("file_name", $uuid);
                $factura->put(strtolower($comprobante->getName() . "_comprobante"), (string)$comprobante);
                if ($comprobante->getName() == "TipoDeComprobante") {
                    $complementaria = (string)$comprobante;
                }
            }


            $Emisor = $xml->xpath('//cfdi:Comprobante//cfdi:Emisor');
            foreach ($Emisor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_emisor"), (string)$attr);
            }
        

            $Receptor = $xml->xpath('//cfdi:Comprobante//cfdi:Receptor');
            foreach ($Receptor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_receptor"), (string)$attr);
            }

            $Receptor = $xml->xpath('//cfdi:Comprobante//tfd:TimbreFiscalDigital ');
            foreach ($Receptor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_timbre"), (string)$attr);
            }

            if ($complementaria == "P") {

                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $ConceptoComplemento) {
                    $concepto_complemento = collect(["uuid" => $uuid]);
                    foreach ($ConceptoComplemento->attributes() as $concep) {
                        $concepto_complemento->put(strtolower($concep->getName()), (string)$concep);
                    }

                    $conceptos_complemento->push($concepto_complemento->only([
                        "uuid",
                        "cantidad",
                        "claveprodserv",
                        "claveunidad",
                        "descripcion",
                        "importe",
                        "noidentificacion",
                        "objetoimp",
                        "unidad",
                        "valorunitario",
                        "base_impuestos",
                        "importe_impuestos",
                        "impuesto_impuestos",
                        "tasaocuota_impuestos",
                        "tipofactor_impuestos",
                    ])->all());
                }

                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//pago:Pago') as $Pago) {
                    $pago_complemento = collect(["uuid" => $uuid]);
                    foreach ($Pago->attributes() as $pago_attr) {
                        $pago_complemento->put(strtolower($pago_attr->getName()), (string)$pago_attr);
                    }

                    $pagos_complemento->push($pago_complemento->only([
                        "uuid",
                        "fechapago",
                        "formadepagop",
                        "monedap",
                        "monto",
                        "numoperacion",
                        "rfcemisorctaord",
                        "nombancoordext",
                        "ctaordenante",
                        "rfcemisorctaben",
                        "ctabeneficiario",
                        "tipocambiop"
                    ])->all());
                }


                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//pago:Pago//pago:DoctoRelacionado') as $Docto) {
                    $doc_relacionado = collect(["uuid" => $uuid]);
                    foreach ($Docto->attributes() as $docto_attr) {
                        $doc_relacionado->put(strtolower($docto_attr->getName()), (string)$docto_attr);
                    }
                    $docs_relacionado->push($doc_relacionado->only([
                        "uuid",
                        "equivalenciadr",
                        "folio",
                        "iddocumento",
                        "imppagado",
                        "impsaldoant",
                        "impsaldoinsoluto",
                        "monedadr",
                        "numparcialidad",
                        "objetoimpdr",
                        "serie",
                        "metododepagodr"
                    ])->all());
                }
            } //fin if de complementaria
            else if ($complementaria == "N") {

                $obj_nomina = collect();

                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina') as $Nomina) {
                    $nomina_nomina = collect(["uuid" => $uuid]);
                    foreach ($Nomina->attributes() as $nomina_attr) {
                        $nomina_nomina->put(strtolower($nomina_attr->getName()), (string)$nomina_attr);
                    }

                    $obj_nomina->put("nomina", $nomina_nomina);
                }

                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Emisor') as $NominaEmisor) {
                    $nomina_emisor = collect(["uuid" => $uuid]);
                    foreach ($NominaEmisor->attributes() as $nomina_emisor_attr) {
                        $nomina_emisor->put(strtolower($nomina_emisor_attr->getName()), (string)$nomina_emisor_attr);
                    }
                    $obj_nomina->put("emisor", $nomina_emisor);
                }

                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Receptor') as $NominaReceptor) {
                    $nomina_receptor = collect(["uuid" => $uuid]);
                    foreach ($NominaReceptor->attributes() as $nomina_receptor_attr) {
                        $nomina_receptor->put(strtolower($nomina_receptor_attr->getName()), (string)$nomina_receptor_attr);
                    }
                    $obj_nomina->put("receptor", $nomina_receptor);
                }

                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Percepciones') as $Percepciones) {
                    $nomina_percepciones = collect(["uuid" => $uuid]);
                    foreach ($Percepciones->attributes() as $nomina_percepciones_attr) {
                        $nomina_percepciones->put(strtolower($nomina_percepciones_attr->getName()), (string)$nomina_percepciones_attr);
                    }
                    $obj_nomina->put("percepciones", $nomina_percepciones);
                }

                $array_percepcion = collect();
                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Percepcion') as $Percepcion) {
                    $nomina_percepcion = collect(["uuid" => $uuid]);
                    foreach ($Percepcion->attributes() as $nomina_percepcion_attr) {
                        $nomina_percepcion->put(strtolower($nomina_percepcion_attr->getName()), (string)$nomina_percepcion_attr);
                    }
                    $array_percepcion->push($nomina_percepcion);
                }
                $obj_nomina->put("percepcion", $array_percepcion);


                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Deducciones') as $Deducciones) {
                    $nomina_deducciones = collect(["uuid" => $uuid]);
                    foreach ($Deducciones->attributes() as $nomina_deducciones_attr) {
                        $nomina_deducciones->put(strtolower($nomina_deducciones_attr->getName()), (string)$nomina_deducciones_attr);
                    }
                    $obj_nomina->put("deducciones", $nomina_deducciones);
                }

                $array_deduccion = collect();
                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Deduccion') as $Deduccion) {
                    $nomina_deduccion = collect(["uuid" => $uuid]);
                    foreach ($Deduccion->attributes() as $nomina_deduccion_attr) {
                        $nomina_deduccion->put(strtolower($nomina_deduccion_attr->getName()), (string)$nomina_deduccion_attr);
                    }
                    $array_deduccion->push($nomina_deduccion);
                }
                $obj_nomina->put("deduccion", $array_deduccion);

                $array_otro_pago = collect();
                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:OtroPago') as $OtroPago) {
                    $nomina_otro_pago = collect(["uuidxxx" => $uuid]);
                    foreach ($OtroPago->attributes() as $nomina_otro_pago_attr) {
                        $nomina_otro_pago->put(strtolower($nomina_otro_pago_attr->getName()), (string)$nomina_otro_pago_attr);
                    }
                    $array_otro_pago->push($nomina_otro_pago);
                }
                $obj_nomina->put("otro_pago", $array_otro_pago);

                $array_subsidio = collect();
                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:SubsidioAlEmpleo') as $Subsidio) {
                    $nomina_subsidio = collect(["uuidxxx" => $uuid]);
                    foreach ($Subsidio->attributes() as $nomina_subsidio_attr) {
                        $nomina_subsidio->put(strtolower($nomina_subsidio_attr->getName()), (string)$nomina_subsidio_attr);
                    }
                    $array_subsidio->push($nomina_subsidio);
                }
                $obj_nomina->put("subsidio", $array_subsidio);


                $list_nomina->push($obj_nomina);
            } else {


                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {
                    $concepto = collect(["uuid" => $uuid]);
                    foreach ($Concepto->attributes() as $concep) {
                        $concepto->put(strtolower($concep->getName()), (string)$concep);

                        $impuesto = $Concepto->children("cfdi", true);
                        if ($impuesto->getName() == "Impuestos") {
                            $traslados = $impuesto->children("cfdi", true);
                            if ($traslados->getName() == "Traslados") {
                                $traslado = $traslados->children("cfdi", true);
                                if ($traslado->getName() == "Traslado") {
                                    foreach ($traslado->attributes() as $atribut) {
                                        $concepto->put(strtolower($atribut->getName()) . "_impuestos", (string)$atribut);
                                    }
                                }
                            }
                        }
                    }

                    $conceptos->push($concepto->only([
                        "uuid",
                        "cantidad",
                        "claveprodserv",
                        "claveunidad",
                        "descripcion",
                        "importe",
                        "noidentificacion",
                        "objetoimp",
                        "unidad",
                        "valorunitario",
                        "base_impuestos",
                        "importe_impuestos",
                        "impuesto_impuestos",
                        "tasaocuota_impuestos",
                        "tipofactor_impuestos",
                    ])->all());
                }
            } //else de normales
           
            $factura_final = $factura
                ->only([
                    "file_name",
                    "exportacion_comprobante",
                    "fecha_comprobante",
                    "folio_comprobante",
                    "formapago_comprobante",
                    "lugarexpedicion_comprobante",
                    "metodopago_comprobante",
                    "moneda_comprobante",
                    "nocertificado_comprobante",
                    "serie_comprobante",
                    "lugarexpedicion",
                    "subtotal_comprobante",
                    "tipodecomprobante_comprobante",
                    "total_comprobante",
                    "version_comprobante",

                    "nombre_emisor",
                    "regimenfiscal_emisor",
                    "rfc_emisor",

                    "domiciliofiscalreceptor_receptor",
                    "nombre_receptor",
                    "regimenfiscalreceptor_receptor",
                    "rfc_receptor",
                    "usocfdi_receptor",

                    "version_timbre",
                    "uuid_timbre",
                    "fechatimbrado_timbre",
                    "rfcprovcertif_timbre",
                    "nocertificadosat_timbre"
                ])
                ->all();
          

            

            if ($complementaria == "P") $list_xml_complemento->push($factura_final);
            else $list_xml->push($factura_final);

            

        } //fin foreach de files   
        
        // var_dump($list_nomina);

        $list_xml_map = $list_xml->map(function ($i) {
            $i = (object)$i;
            return [
                "file_name" => $i->file_name,
                "exportacion_comprobante" => $i->exportacion_comprobante ?? '',
                "fecha_comprobante" => $i->fecha_comprobante ?? '',
                "folio_comprobante" => $i->folio_comprobante ?? '',
                "formapago_comprobante" => $i->formapago_comprobante ?? '',
                "lugarexpedicion_comprobante" => $i->lugarexpedicion_comprobante ?? '',
                "metodopago_comprobante" => $i->metodopago_comprobante ?? '',
                "moneda_comprobante" => $i->moneda_comprobante ?? '',
                "nocertificado_comprobante" => $i->nocertificado_comprobante ?? '',
                "serie_comprobante" => $i->serie_comprobante ?? '',
                "lugarexpedicion" => $i->lugarexpedicion ?? '',
                "subtotal_comprobante" => $i->subtotal_comprobante ?? '',
                "tipodecomprobante_comprobante" => $i->tipodecomprobante_comprobante ?? '',
                "total_comprobante" => $i->total_comprobante ?? '',
                "version_comprobante" => $i->version_comprobante ?? '',

                "nombre_emisor" => $i->nombre_emisor ?? '',
                "regimenfiscal_emisor" => $i->regimenfiscal_emisor ?? '',
                "rfc_emisor" => $i->rfc_emisor ?? '',

                "domiciliofiscalreceptor_receptor" => $i->domiciliofiscalreceptor_receptor ?? '',
                "nombre_receptor" => $i->nombre_receptor ?? '',
                "regimenfiscalreceptor_receptor" => $i->regimenfiscalreceptor_receptor ?? '',
                "rfc_receptor" => $i->rfc_receptor ?? '',
                "usocfdi_receptor" => $i->usocfdi_receptor ?? '',

                "version_timbre" => $i->version_timbre ?? '',
                "uuid_timbre" => $i->uuid_timbre ?? '',
                "fechatimbrado_timbre" => $i->fechatimbrado_timbre ?? '',
                "rfcprovcertif_timbre" => $i->rfcprovcertif_timbre ?? '',
                "nocertificadosat_timbre" => $i->nocertificadosat_timbre ?? ''
            ];
        });

       

        $list_xml_map_complemento = $list_xml_complemento->map(function ($i) {
            $i = (object)$i;
            return [
                "file_name" => $i->file_name,
                "exportacion_comprobante" => $i->exportacion_comprobante ?? '',
                "fecha_comprobante" => $i->fecha_comprobante ?? '',
                "folio_comprobante" => $i->folio_comprobante ?? '',
                "formapago_comprobante" => $i->formapago_comprobante ?? '',
                "lugarexpedicion_comprobante" => $i->lugarexpedicion_comprobante ?? '',
                "metodopago_comprobante" => $i->metodopago_comprobante ?? '',
                "moneda_comprobante" => $i->moneda_comprobante ?? '',
                "nocertificado_comprobante" => $i->nocertificado_comprobante ?? '',
                "serie_comprobante" => $i->serie_comprobante ?? '',
                "lugarexpedicion" => $i->lugarexpedicion ?? '',
                "subtotal_comprobante" => $i->subtotal_comprobante ?? '',
                "tipodecomprobante_comprobante" => $i->tipodecomprobante_comprobante ?? '',
                "total_comprobante" => $i->total_comprobante ?? '',
                "version_comprobante" => $i->version_comprobante ?? '',

                "nombre_emisor" => $i->nombre_emisor ?? '',
                "regimenfiscal_emisor" => $i->regimenfiscal_emisor ?? '',
                "rfc_emisor" => $i->rfc_emisor ?? '',

                "domiciliofiscalreceptor_receptor" => $i->domiciliofiscalreceptor_receptor ?? '',
                "nombre_receptor" => $i->nombre_receptor ?? '',
                "regimenfiscalreceptor_receptor" => $i->regimenfiscalreceptor_receptor ?? '',
                "rfc_receptor" => $i->rfc_receptor ?? '',
                "usocfdi_receptor" => $i->usocfdi_receptor ?? '',

                "version_timbre" => $i->version_timbre ?? '',
                "uuid_timbre" => $i->uuid_timbre ?? '',
                "fechatimbrado_timbre" => $i->fechatimbrado_timbre ?? '',
                "rfcprovcertif_timbre" => $i->rfcprovcertif_timbre ?? '',
                "nocertificadosat_timbre" => $i->nocertificadosat_timbre ?? ''
            ];
        });

        $conceptos_map = $conceptos->map(function ($i) {
            $i = (object)$i;
            return [
                "uuid" => $i->uuid ?? '',
                "cantidad" => $i->cantidad ?? '',
                "claveprodserv" => $i->claveprodserv ?? '',
                "claveunidad" => $i->claveunidad ?? '',
                "descripcion" => $i->descripcion ?? '',
                "importe" => $i->importe ?? '',
                "noidentificacion" => $i->noidentificacion ?? '',
                "objetoimp" => $i->objetoimp ?? '',
                "unidad" => $i->unidad ?? '',
                "valorunitario" => $i->valorunitario ?? '',
                "base_impuestos" => $i->base_impuestos ?? '',
                "importe_impuestos" => $i->importe_impuestos ?? '',
                "impuesto_impuestos" => $i->impuesto_impuestos ?? '',
                "tasaocuota_impuestos" => $i->tasaocuota_impuestos ?? '',
                "tipofactor_impuestos" => $i->tipofactor_impuestos ?? ''
            ];
        });

        

        $conceptos_map_complemento = $conceptos_complemento->map(function ($i) {
            $i = (object)$i;
            return [
                "uuid" => $i->uuid ?? '',
                "cantidad" => $i->cantidad ?? '',
                "claveprodserv" => $i->claveprodserv ?? '',
                "claveunidad" => $i->claveunidad ?? '',
                "descripcion" => $i->descripcion ?? '',
                "importe" => $i->importe ?? '',
                "noidentificacion" => $i->noidentificacion ?? '',
                "objetoimp" => $i->objetoimp ?? '',
                "unidad" => $i->unidad ?? '',
                "valorunitario" => $i->valorunitario ?? '',
                "base_impuestos" => $i->base_impuestos ?? '',
                "importe_impuestos" => $i->importe_impuestos ?? '',
                "impuesto_impuestos" => $i->impuesto_impuestos ?? '',
                "tasaocuota_impuestos" => $i->tasaocuota_impuestos ?? '',
                "tipofactor_impuestos" => $i->tipofactor_impuestos ?? ''
            ];
        });

        $pagos_complemento_map = $pagos_complemento->map(function ($i) {
            $i = (object)$i;
            return [
                "uuid" => $i->uuid ?? '',
                "fechapago" => $i->fechapago ?? '',
                "formadepagop" => $i->formadepagop ?? '',
                "monedap" => $i->monedap ?? '',
                "monto" => $i->monto ?? '',
                "numoperacion" => $i->numoperacion ?? '',
                "rfcemisorctaord" => $i->rfcemisorctaord ?? '',
                "nombancoordext" => $i->nombancoordext ?? '',
                "ctaordenante" => $i->ctaordenante ?? '',
                "rfcemisorctaben" => $i->rfcemisorctaben ?? '',
                "ctabeneficiario" => $i->ctabeneficiario ?? '',
                "tipocambiop" => $i->tipocambiop ?? '',
            ];
        });


        $docs_relacionado_map = $docs_relacionado->map(function ($i) {
            $i = (object)$i;
            return [
                "uuid" => $i->uuid ?? '',
                "equivalenciadr" => $i->equivalenciadr ?? '',
                "folio" => $i->folio ?? '',
                "iddocumento" => $i->iddocumento ?? '',
                "imppagado" => $i->imppagado ?? '',
                "impsaldoant" => $i->impsaldoant ?? '',
                "impsaldoinsoluto" => $i->impsaldoinsoluto ?? '',
                "monedadr" => $i->monedadr ?? '',
                "numparcialidad" => $i->numparcialidad ?? '',
                "objetoimpdr" => $i->objetoimpdr ?? '',
                "serie" => $i->serie ?? '',
                "metododepagodr" => $i->metododepagodr ?? '',
            ];
        });


       $data_insert_complemento=SatXMLDAO::validarDuplicadoComplemento($list_xml_map_complemento,$conceptos_map_complemento,$pagos_complemento_map,$docs_relacionado_map);
       SatXMLDAO::createDatosComplementarios($data_insert_complemento["doc"], $data_insert_complemento["concep"], $data_insert_complemento["pagos"], $data_insert_complemento["doc_rel"]);

       $data_insert=SatXMLDAO::validarDuplicado($list_xml_map,$conceptos_map);          
       SatXMLDAO::createDatos($data_insert["doc"],$data_insert["concep"]);                
       return 1;
    }

    public function leerXMLNomina(Request $req)
    {

        $filesXML = $req->file("filexml");
        $list_nomina = collect();


        foreach ($filesXML as $fileXML) {

            $factura = collect();

            $uuid = $fileXML->getClientOriginalName();

            $complementaria = "";

            $xml = simplexml_load_file($fileXML);
            $ns = $xml->getNamespaces(true);


            foreach ($ns as $key => $value) {

                if (preg_match('/nomina/i', $key))
                    $xml->registerXPathNamespace("nomina", (string)$value);
                else
                    $xml->registerXPathNamespace((string)$key, (string)$value);
            }

            foreach ($xml->attributes() as $comprobante) {
                $factura->put("file_name", $uuid);
                $factura->put(strtolower($comprobante->getName() . "_comprobante"), (string)$comprobante);
                if ($comprobante->getName() == "TipoDeComprobante") {
                    $complementaria = (string)$comprobante;
                }
            }

            $Emisor = $xml->xpath('//cfdi:Comprobante//cfdi:Emisor');
            foreach ($Emisor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_emisor"), (string)$attr);
            }


            $Receptor = $xml->xpath('//cfdi:Comprobante//cfdi:Receptor');
            foreach ($Receptor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_receptor"), (string)$attr);
            }

            $Receptor = $xml->xpath('//cfdi:Comprobante//tfd:TimbreFiscalDigital ');
            foreach ($Receptor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_timbre"), (string)$attr);
            }

            $obj_nomina = collect();

            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina') as $Nomina) {
                $nomina_nomina = collect(["uuid" => $uuid]);
                foreach ($Nomina->attributes() as $nomina_attr) {
                    $nomina_nomina->put(strtolower($nomina_attr->getName()), (string)$nomina_attr);
                    $factura->put(strtolower($nomina_attr->getName()."_nomina"), (string)$nomina_attr);
                }

                $obj_nomina->put("nomina", $nomina_nomina);
            }

            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Emisor') as $NominaEmisor) {
                $nomina_emisor = collect(["uuid" => $uuid]);
                foreach ($NominaEmisor->attributes() as $nomina_emisor_attr) {
                    $nomina_emisor->put(strtolower($nomina_emisor_attr->getName()), (string)$nomina_emisor_attr);
                    $factura->put(strtolower($nomina_attr->getName()."_nomina_emisor"), (string)$nomina_attr);
                }
                $obj_nomina->put("emisor", $nomina_emisor);
            }

            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Receptor') as $NominaReceptor) {
                $nomina_receptor = collect(["uuid" => $uuid]);
                foreach ($NominaReceptor->attributes() as $nomina_receptor_attr) {
                    $nomina_receptor->put(strtolower($nomina_receptor_attr->getName()), (string)$nomina_receptor_attr);
                    $factura->put(strtolower($nomina_attr->getName()."_nomina_receptor"), (string)$nomina_attr);
                }
                $obj_nomina->put("receptor", $nomina_receptor);
            }

            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Percepciones') as $Percepciones) {
                $nomina_percepciones = collect(["uuid" => $uuid]);
                foreach ($Percepciones->attributes() as $nomina_percepciones_attr) {
                    $nomina_percepciones->put(strtolower($nomina_percepciones_attr->getName()), (string)$nomina_percepciones_attr);
                    $factura->put(strtolower($nomina_attr->getName()."_nomina_percepciones"), (string)$nomina_attr);
                }
                $obj_nomina->put("percepciones", $nomina_percepciones);
            }

            $array_percepcion = collect();
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Percepcion') as $Percepcion) {
                $nomina_percepcion = collect(["uuid" => $uuid,"tipo"=>"percepcion"]);
                foreach ($Percepcion->attributes() as $nomina_percepcion_attr) {
                    $nomina_percepcion->put(strtolower($nomina_percepcion_attr->getName()), (string)$nomina_percepcion_attr);
                }
                $array_percepcion->push($nomina_percepcion);
            }
            $obj_nomina->put("percepcion", $array_percepcion);
            $factura->put("percepcion", $array_percepcion);


            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Deducciones') as $Deducciones) {
                $nomina_deducciones = collect(["uuid" => $uuid]);
                foreach ($Deducciones->attributes() as $nomina_deducciones_attr) {
                    $nomina_deducciones->put(strtolower($nomina_deducciones_attr->getName()), (string)$nomina_deducciones_attr);
                    $factura->put(strtolower($nomina_attr->getName()."_nomina_deducciones"), (string)$nomina_attr);
                }
                $obj_nomina->put("deducciones", $nomina_deducciones);
            }

            $array_deduccion = collect();
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Deduccion') as $Deduccion) {
                $nomina_deduccion = collect(["uuid" => $uuid,"tipo"=>"deduccion"]);
                foreach ($Deduccion->attributes() as $nomina_deduccion_attr) {
                    $nomina_deduccion->put(strtolower($nomina_deduccion_attr->getName()), (string)$nomina_deduccion_attr);
                }
                $array_deduccion->push($nomina_deduccion);
            }
            $obj_nomina->put("deduccion", $array_deduccion);
            $factura->put("deduccion", $array_deduccion);

            $array_otro_pago = collect();
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:OtroPago') as $OtroPago) {
                $nomina_otro_pago = collect(["uuid" => $uuid,"tipo"=>"otro_pago"]);
                foreach ($OtroPago->attributes() as $nomina_otro_pago_attr) {
                    $nomina_otro_pago->put(strtolower($nomina_otro_pago_attr->getName()), (string)$nomina_otro_pago_attr);
                }
                $array_otro_pago->push($nomina_otro_pago);
            }
            $obj_nomina->put("otro_pago", $array_otro_pago);
            $factura->put("otro_pago", $array_otro_pago);

            $array_subsidio = collect();
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:SubsidioAlEmpleo') as $Subsidio) {
                $nomina_subsidio = collect(["uuid" => $uuid, "tipo"=>"subsidio"]);
                foreach ($Subsidio->attributes() as $nomina_subsidio_attr) {
                    $nomina_subsidio->put(strtolower($nomina_subsidio_attr->getName()), (string)$nomina_subsidio_attr);
                }
                $array_subsidio->push($nomina_subsidio);
            }

            $obj_nomina->put("subsidio", $array_subsidio);
            $factura->put("subsidio", $array_subsidio);        

            $list_nomina->push($factura->all());
        } //fin foreach de files   

       

        $list_nomina_map = $list_nomina->map(function ($i) {
            $i = (object)$i;
            return [
                "file_name"=>$i->file_name??'',
                "exportacion_comprobante"=>$i->exportacion_comprobante??'',
                "fecha_comprobante"=>$i->fecha_comprobante??'',
                "folio_comprobante"=>$i->folio_comprobante??'',
                "formapago_comprobante"=>$i->formapago_comprobante??'',
                "lugarexpedicion_comprobante"=>$i->lugarexpedicion_comprobante??'',
                "metodopago_comprobante"=>$i->metodopago_comprobante??'',
                "moneda_comprobante"=>$i->moneda_comprobante??'',
                "nocertificado_comprobante"=>$i->nocertificado_comprobante??'',
                "serie_comprobante"=>$i->serie_comprobante??'',
                "lugarexpedicion"=>$i->lugarexpedicion??'',
                "subtotal_comprobante"=>$i->subtotal_comprobante??'',
                "tipodecomprobante_comprobante"=>$i->tipodecomprobante_comprobante??'',
                "total_comprobante"=>$i->total_comprobante??'',
                "version_comprobante"=>$i->version_comprobante??'',

                "nombre_emisor"=>$i->nombre_emisor??'',
                "regimenfiscal_emisor"=>$i->regimenfiscal_emisor??'',
                "rfc_emisor"=>$i->rfc_emisor??'',

                "domiciliofiscalreceptor_receptor"=>$i->domiciliofiscalreceptor_receptor??'',
                "nombre_receptor"=>$i->nombre_receptor??'',
                "regimenfiscalreceptor_receptor"=>$i->regimenfiscalreceptor_receptor??'',
                "rfc_receptor"=>$i->rfc_receptor??'',
                "usocfdi_receptor"=>$i->usocfdi_receptor??'',

                "version_timbre"=>$i->version_timbre??'',
                "uuid_timbre"=>$i->uuid_timbre??'',
                "fechatimbrado_timbre"=>$i->fechatimbrado_timbre??'',
                "rfcprovcertif_timbre"=>$i->rfcprovcertif_timbre??'',
                "nocertificadosat_timbre"=>$i->nocertificadosat_timbre??'',

                "version_nomina"=>$i->version_nomina??'',
                "tiponomina_nomina"=>$i->tiponomina_nomina??'',
                "fechapago_nomina"=>$i->fechapago_nomina??'',
                "fechainicialpago_nomina"=>$i->fechainicialpago_nomina??'',
                "fechafinalpago_nomina"=>$i->fechafinalpago_nomina??'',
                "numdiaspagados_nomina"=>$i->numdiaspagados_nomina??'',
                "totalpercepciones_nomina"=>$i->totalpercepciones_nomina??'',
                "totaldeducciones_nomina"=>$i->totaldeducciones_nomina??'',
                "totalotrospagos_nomina"=>$i->totalotrospagos_nomina??'',
                "totalotrospagos_nomina_emisor"=>$i->totalotrospagos_nomina_emisor??'',
                "totalotrospagos_nomina_receptor"=>$i->totalotrospagos_nomina_receptor??'',
                "totalotrospagos_nomina_percepciones"=>$i->totalotrospagos_nomina_percepciones??'',
                "totaldeducciones_nomina_deducciones"=>$i->totaldeducciones_nomina_deducciones??'',
            ];

        });

        $Percepciones_map=$list_nomina->filter(function($i){return collect($i)->has("percepcion");})
        ->map(function($i){return $i["percepcion"]->toArray();})
        ->flatten(1)
        ->map(function($i){
            $i = (object)$i;
            return [
                "uuid" =>$i->uuid??'',
                "tipo" =>$i->tipo??'',
                "tipopercepcion" => $i->tipopercepcion??'',
                "clave" => $i->clave??'',
                "concepto" => $i->concepto??'',
                "importegravado" => $i->importegravado??'',
                "importeexento" => $i->importeexento??'',                
            ];
        })
        ->toArray();
        

        $Deducciones_map=$list_nomina->filter(function($i){return collect($i)->has("deduccion");})
        ->map(function($i){return $i["deduccion"]->toArray();})
        ->flatten(1)
        ->map(function($i){
            $i = (object)$i;
            return [
                "uuid" =>$i->uuid??'',
                "tipo" =>$i->tipo??'',
                "tipodeduccion" => $i->tipodeduccion??'',
                "clave" => $i->clave??'',
                "concepto" => $i->concepto??'',
                "importe" => $i->importe??''
            ];
        })
        ->toArray();

        $OtrosPagos_map=$list_nomina->filter(function($i){return collect($i)->has("otro_pago");})
        ->map(function($i){return $i["otro_pago"]->toArray();})
        ->flatten(1)
        ->map(function($i){
            $i = (object)$i;
            return [
                "uuid" =>$i->uuid??'',
                "tipo" =>$i->tipo??'',
                "tipootropago" => $i->tipootropago??'',
                "clave" => $i->clave??'',
                "concepto" => $i->concepto??'',
                "importe" => $i->importe??''
            ];
        })
        ->toArray();

        $SubSidios_map=$list_nomina->filter(function($i){return collect($i)->has("subsidio");})
        ->map(function($i){return $i["subsidio"]->toArray();})
        ->flatten(1)
        ->map(function($i){
            $i = (object)$i;
            return [
                "uuid" =>$i->uuid??'',
                "tipo" =>$i->tipo??'',
                "subsidiocausado" => $i->subsidiocausado??''
            ];
        })
        ->toArray();

        $extrasNomina=collect()
        ->concat($Percepciones_map)
        ->concat($Deducciones_map)
        ->concat($OtrosPagos_map)
        ->concat($SubSidios_map)
        ->map(function($i){
            $i=(object)$i;
            return  [
                "uuid" =>$i->uuid??'',
                "tipo" =>$i->tipo??'',
                "tipopercepcion" => $i->tipopercepcion??'',
                "tipodeduccion" => $i->tipodeduccion??'',
                "tipootropago" => $i->tipootropago??'',
                "clave" => $i->clave??'',
                "concepto" => $i->concepto??'',
                "importe" => $i->importe??'',
                "importegravado" => $i->importegravado??'',
                "importeexento" => $i->importeexento??'',                
                "subsidiocausado" => $i->subsidiocausado??'',                
            ];
        });
        // ->toArray();

        $data_insert=SatXMLDAO::validarDuplicadoNomina($list_nomina_map,$extrasNomina);   
        SatXMLDAO::createDatosNomina($data_insert["doc"], $data_insert["extras"]);
        return 1;


    }

    public function leerEmitidoXML(Request $req)
    {

        $filesXML = $req->file("filexml");
        $list_xml = collect();
        $conceptos = collect();
        $tipoComprobante="";
        $rfcEmite="";

        foreach ($filesXML as $fileXML) {

            $factura = collect();

            $uuid = $fileXML->getClientOriginalName();            

            $xml = simplexml_load_file($fileXML);
            $ns = $xml->getNamespaces(true);

            foreach ($ns as $key => $value) {
                $xml->registerXPathNamespace((string)$key, (string)$value);                
            }

            //datos del comprobante de aqui tomo el tipo para saber si es nomina, ingresos o complementaria
            foreach ($xml->attributes() as $comprobante) {
                $factura->put("file_name", $uuid);
                $factura->put(strtolower($comprobante->getName() . "_comprobante"), (string)$comprobante);
                if ($comprobante->getName() == "TipoDeComprobante") {
                    $tipoComprobante = (string)$comprobante;
                }                
            }


            //datos del emisor
            $Emisor = $xml->xpath('//cfdi:Comprobante//cfdi:Emisor');
            foreach ($Emisor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_emisor"), (string)$attr);
                if ($attr->getName() == "Rfc") {
                    $rfcEmite = (string)$attr;
                }
            }           

            //datos del receptor
            $Receptor = $xml->xpath('//cfdi:Comprobante//cfdi:Receptor');
            foreach ($Receptor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_receptor"), (string)$attr);
            }

            //datos timbre fiscal
            $Receptor = $xml->xpath('//cfdi:Comprobante//tfd:TimbreFiscalDigital ');
            foreach ($Receptor[0]->attributes() as $attr) {
                $factura->put(strtolower($attr->getName() . "_timbre"), (string)$attr);
            }

            //se obtiene los conceptos de la factura
            foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {
                    $concepto = collect(["uuid" => $uuid]);
                    foreach ($Concepto->attributes() as $concep) {
                        $concepto->put(strtolower($concep->getName()), (string)$concep);

                        $impuesto = $Concepto->children("cfdi", true);
                        if ($impuesto->getName() == "Impuestos") {
                            $traslados = $impuesto->children("cfdi", true);
                            if ($traslados->getName() == "Traslados") {
                                $traslado = $traslados->children("cfdi", true);
                                if ($traslado->getName() == "Traslado") {
                                    foreach ($traslado->attributes() as $atribut) {
                                        $concepto->put(strtolower($atribut->getName()) . "_impuestos", (string)$atribut);
                                    }
                                }
                            }
                        }
                    }

                    $conceptos->push($concepto->all());
                }
           
                //se agregan mientras el documento sea ingresos y sea emitido por punto verde
                if($tipoComprobante=="I" && $rfcEmite=="PVL810617EC3")
                {
                    $list_xml->push($factura);               
                }
        } //fin foreach de files   
    
      
        $list_xml_map = $list_xml->map(function ($i) {           
            $i = (object)$i->toArray();
           
            return [
                "file_name" => $i->file_name??'',
                "exportacion_comprobante" => $i->exportacion_comprobante ?? '',
                "fecha_comprobante" => $i->fecha_comprobante ?? '',
                "folio_comprobante" => $i->folio_comprobante ?? '',
                "formapago_comprobante" => $i->formapago_comprobante ?? '',
                "lugarexpedicion_comprobante" => $i->lugarexpedicion_comprobante ?? '',
                "metodopago_comprobante" => $i->metodopago_comprobante ?? '',
                "moneda_comprobante" => $i->moneda_comprobante ?? '',
                "nocertificado_comprobante" => $i->nocertificado_comprobante ?? '',
                "serie_comprobante" => $i->serie_comprobante ?? '',
                "lugarexpedicion" => $i->lugarexpedicion ?? '',
                "subtotal_comprobante" => $i->subtotal_comprobante ?? '',
                "tipodecomprobante_comprobante" => $i->tipodecomprobante_comprobante ?? '',
                "total_comprobante" => $i->total_comprobante ?? '',
                "version_comprobante" => $i->version_comprobante ?? '',

                "nombre_emisor" => $i->nombre_emisor ?? '',
                "regimenfiscal_emisor" => $i->regimenfiscal_emisor ?? '',
                "rfc_emisor" => $i->rfc_emisor ?? '',

                "domiciliofiscalreceptor_receptor" => $i->domiciliofiscalreceptor_receptor ?? '',
                "nombre_receptor" => $i->nombre_receptor ?? '',
                "regimenfiscalreceptor_receptor" => $i->regimenfiscalreceptor_receptor ?? '',
                "rfc_receptor" => $i->rfc_receptor ?? '',
                "usocfdi_receptor" => $i->usocfdi_receptor ?? '',

                "version_timbre" => $i->version_timbre ?? '',
                "uuid_timbre" => $i->uuid_timbre ?? '',
                "fechatimbrado_timbre" => $i->fechatimbrado_timbre ?? '',
                "rfcprovcertif_timbre" => $i->rfcprovcertif_timbre ?? '',
                "nocertificadosat_timbre" => $i->nocertificadosat_timbre ?? ''
            ];
        });
        

        $conceptos_map = $conceptos->map(function ($i) {
            $i = (object)$i;
            return [
                "uuid" => $i->uuid ?? '',
                "cantidad" => $i->cantidad ?? '',
                "claveprodserv" => $i->claveprodserv ?? '',
                "claveunidad" => $i->claveunidad ?? '',
                "descripcion" => $i->descripcion ?? '',
                "importe" => $i->importe ?? '',
                "noidentificacion" => $i->noidentificacion ?? '',
                "objetoimp" => $i->objetoimp ?? '',
                "unidad" => $i->unidad ?? '',
                "valorunitario" => $i->valorunitario ?? '',
                "base_impuestos" => $i->base_impuestos ?? '',
                "importe_impuestos" => $i->importe_impuestos ?? '',
                "impuesto_impuestos" => $i->impuesto_impuestos ?? '',
                "tasaocuota_impuestos" => $i->tasaocuota_impuestos ?? '',
                "tipofactor_impuestos" => $i->tipofactor_impuestos ?? ''
            ];
        });       
        
        $data_insert=SatXMLDAO::validarDuplicadoEmitido($list_xml_map,$conceptos_map);     
        SatXMLDAO::createDatosEmitidos($data_insert["doc"], $data_insert["concep"]);
        return 1;
    }

    //consultas para mostrar en tablas
    public function getDataXML(Request $req)
    {
        return SatXMLDAO::getDatosXML((object) $req->all());
    }

    public function getDataXMLComplementarios(Request $req)
    {
        return SatXMLDAO::getDatosXMLComplementarios((object)$req->all());
    }

    public function getConceptosXML($id)
    {
        return SatXMLDAO::getConceptosXML($id);
    }

    public function getConceptosXMLComplementarios($id)
    {
        return SatXMLDAO::getConceptosXMLComplementarios($id);
    }

    public function getDataNominaXML(Request $req)
    {
        return SatXMLDAO::getDatosNominaXML((object) $req->all());
    }

    public function getDatosNominaExtraXML(Request $req,$id)
    {
        return SatXMLDAO::getDatosNominaExtraXML($id);
    }
    
    
    public function getDatosEmitidoXML(Request $req)
    {
        return SatXMLDAO::getDatosEmitidoXML((object) $req->all());
    }

    public function getEmitidoConceptosXML($id)
    {
        return SatXMLDAO::getEmitidoConceptosXML($id);
    }

    //creacion de excel

    public function getXLSNomina(Request $req)
    {
        $data_nomina = SatXMLDAO::getNominaXML((object)$req->all());

        //se gregan los demas campos
        $data_nomina["body"]->each(function ($i) use ($data_nomina) {
            $data_nomina["headers"]->each(function ($j) use ($i, $data_nomina) {

                $data_temp = $data_nomina["body_extra"]->first(function ($k) use ($i, $j) {
                    return $k->uuid == $i->file_name && $k->tipo == $j->tipo &&  $k->concepto == $j->concepto;
                });
                $i->{$j->concepto} = $data_temp->importe ?? "";
            });
        });


        //crea hoja de calculo
        $spreadsheet = new Spreadsheet();

        //asigna los metadatos
        $spreadsheet->getProperties()
            ->setCreator("Sistemas PV")
            ->setLastModifiedBy("PV")
            ->setTitle("Template")
            ->setSubject("template")
            ->setDescription("es un template para realizar creacion reporte de nomina")
            ->setKeywords("1")
            ->setCategory("2");


        //obtiene la hoja con la que se tarabajara
        $sheet = $spreadsheet->getActiveSheet();
        //define t5 celdas se usan como emcabezados
        $sheet->setCellValue('A1', 'Serie');
        $sheet->setCellValue('B1', 'Folio');
        $sheet->setCellValue('C1', 'Forma Pago');
        $sheet->setCellValue('D1', 'Lugar Expedicion');
        $sheet->setCellValue('E1', 'Metodo Pago');
        $sheet->setCellValue('F1', 'Moneda');
        $sheet->setCellValue('G1', 'Subtotal');
        $sheet->setCellValue('H1', 'Total');
        $sheet->setCellValue('I1', 'Tipo Comprobante');
        $sheet->setCellValue('J1', 'Version');
        $sheet->setCellValue('K1', 'Nombre Emisor');
        $sheet->setCellValue('L1', 'Regimen Fiscal Emisor');
        $sheet->setCellValue('M1', 'Rfc Emisor');
        $sheet->setCellValue('N1', 'Domicilio Receptor');
        $sheet->setCellValue('O1', 'Nombre Receptor');
        $sheet->setCellValue('P1', 'Regimen Fiscal Receptor');
        $sheet->setCellValue('Q1', 'Rfc Receptor');
        $sheet->setCellValue('R1', 'Uso Cfdi Receptor');
        $sheet->setCellValue('S1', 'UUID');
        $sheet->setCellValue('T1', 'Fecha Comprobante');
        $sheet->setCellValue('U1', 'Fecha Timbre');
        $sheet->setCellValue('V1', 'Tipo Nomina');
        $sheet->setCellValue('W1', 'Fecha Pago');
        $sheet->setCellValue('X1', 'Fecha Inicial Pago');
        $sheet->setCellValue('Y1', 'Fecha Final Pago');
        // $sheet->setCellValue('E1', 'Numero dias Pagados');
        // $sheet->setCellValue('E1', 'total Percepciones');
        // $sheet->setCellValue('E1', 'Total Deducciones');
        // $sheet->setCellValue('E1', 'Total Otros Pagos');

        //se oculta la columan A, por que se encuentran los datos de los id de alumnos
        /////$sheet->getColumnDimension('A')->setVisible(false);
        //se oculta la fila o row 1, en ella se encuentran los datos de las preguntas
        // $sheet->getRowDimension('1')->setVisible(false);
        //se ahbilita la seguridad en el documento, falta especificar el nivel de proteccion
        $sheet->getProtection()->setSheet(true);


        //llenado de los datos medinate fromArray

        // dd($data_nomina["headers"]->map(function($i){return $i->concepto;})->toArray());
        //cabecera dinamica
        $sheet->fromArray($data_nomina["headers"]->map(function ($i) {
            return $i->concepto_header;
        })->toArray(), NULL, 'Z1');

        $sheet->fromArray($data_nomina["body"]->map(function ($i) {
            return collect($i)->flatten(2);
        })->toArray(), NULL, 'A2');

        //$sheet->fromArray([1, 2, 3, 4], NULL, 'A3');


        //obtiene el ultimo row insertado ejempolo 4
        $hRow = $sheet->getHighestRow();
        //obtiene la ultima columan insertada ejemplo Z
        $hColumn = $sheet->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn++;

        //agregar estilos(background) en un rango de celdas de la B2:Z2 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);


        //ajusta el tamañp de columan deade la B hasta la ultima insertada
        for ($col = 'A'; $col != $hColumn; ++$col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }



        //remover seguridad en celdas de preguntas 
        for ($row = 3; $row <= $hRow; ++$row) {
            //primer for lee las filas 1,2,3,4 etc

            for ($col2 = 'F'; $col2 != $hColumn; ++$col2) {
                //segundo for lee las columnas desde la F hasta la ultima insertada

                //se asigna el estilo para remover seguridad en celda
                $sheet->getCell($col2 . $row)->getStyle()->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
            }
        }


        $writer = new Xlsx($spreadsheet);

        // $writer->save('reporte.xlsx');
       // $img = file_get_contents("reporte.xlsx");

       $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
       $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
       $writer->save($tempFile);

       $stream = fopen($tempFile, 'r+');
        
        return response(fread($stream,(int)fstat($stream)['size']))->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function getXLSFactura(Request $req)
    {
        $data_factura = SatXMLDAO::getDataExcelFactura((object)$req->all()); 
               

        //crea hoja de calculo
        $spreadsheet = new Spreadsheet();

        //asigna los metadatos
        $spreadsheet->getProperties()
            ->setCreator("Sistemas PV")
            ->setLastModifiedBy("PV")
            ->setTitle("Template")
            ->setSubject("template")
            ->setDescription("es un template para realizar creacion reporte de facturas recibidas")
            ->setKeywords("1")
            ->setCategory("2");


        //obtiene la hoja con la que se tarabajara
        $sheet = $spreadsheet->getActiveSheet();
        //define t5 celdas se usan como emcabezados
        $sheet->setCellValue('A1', 'Serie');
        $sheet->setCellValue('B1', 'Folio');
        $sheet->setCellValue('C1', 'Forma Pago');
        $sheet->setCellValue('D1', 'Lugar Expedicion');
        $sheet->setCellValue('E1', 'Metodo Pago');
        $sheet->setCellValue('F1', 'Moneda');        
        $sheet->setCellValue('G1', 'Subtotal');
        $sheet->setCellValue('H1', 'Total');
        $sheet->setCellValue('I1', 'Tipo Comprobante');
        $sheet->setCellValue('J1', 'Version');
        $sheet->setCellValue('K1', 'Nombre Emisor');
        $sheet->setCellValue('L1', 'Regimen Fiscal Emisor');
        $sheet->setCellValue('M1', 'Rfc Emisor');
        $sheet->setCellValue('N1', 'Domicilio Receptor');
        $sheet->setCellValue('O1', 'Nombre Receptor');
        $sheet->setCellValue('P1', 'Regimen Fiscal Receptor');
        $sheet->setCellValue('Q1', 'Rfc Receptor');
        $sheet->setCellValue('R1', 'Uso Cfdi Receptor');
        $sheet->setCellValue('S1', 'UUID');
        $sheet->setCellValue('T1', 'Fecha Comprobante');
        $sheet->setCellValue('U1', 'Fecha Timbre');
       
        $sheet->getProtection()->setSheet(true);


      

       
        // $sheet->fromArray($data_factura["headers"]->map(function ($i) {
        //     return $i->concepto_header;
        // })->toArray(), NULL, 'Z1');

        $sheet->fromArray($data_factura["factura"]->map(function ($i) {            
            return collect($i)->except("file_name")->flatten(2);
        })->toArray(), NULL, 'A2');

       


        //obtiene el ultimo row insertado ejempolo 4
        $hRow = $sheet->getHighestRow();
        //obtiene la ultima columan insertada ejemplo Z
        $hColumn = $sheet->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn++;

        //agregar estilos(background) en un rango de celdas de la B2:Z2 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);


        //ajusta el tamañp de columan deade la B hasta la ultima insertada
        for ($col = 'A'; $col != $hColumn; ++$col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->createSheet();

        $sheet2=$spreadsheet->setActiveSheetIndex(1);
        $sheet2->setCellValue('A1', 'Clave producto');
        $sheet2->setCellValue('B1', 'Clave unidad');
        $sheet2->setCellValue('C1', 'Descripcion');
        $sheet2->setCellValue('D1', 'Unidad');
        $sheet2->setCellValue('E1', 'Cantidad');
        $sheet2->setCellValue('F1', 'Valor unitario');
        $sheet2->setCellValue('G1', 'Base impuesto');
        $sheet2->setCellValue('H1', 'Importe impuesto');
        $sheet2->setCellValue('I1', 'Tasa impuesto');
        $sheet2->setCellValue('J1', 'Importe');


        $hColumn2 = $sheet2->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn2++;

        $sheet2->getStyle("A1:" . $sheet2->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet2->getStyle("A1:" . $sheet2->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);

            for ($col = 'A'; $col != $hColumn2; ++$col) {
                $sheet2->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet2->fromArray($data_factura["conceptos"]->map(function ($i) {            
                return collect($i)->flatten(2);
            })->toArray(), NULL, 'A2');
    
            $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        // $writer->save('reporte_factura.xlsx');
        // $img = file_get_contents("reporte_factura.xlsx");
        
        // return response($img)->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
       $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
       $writer->save($tempFile);

       $stream = fopen($tempFile, 'r+');
        
        return response(fread($stream,(int)fstat($stream)['size']))->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function getXLSFacturaComplementos(Request $req)
    {
        $data_factura_complemento = SatXMLDAO::getDataExcelFacturaComplementos((object)$req->all());


        //crea hoja de calculo
        $spreadsheet = new Spreadsheet();

        //asigna los metadatos
        $spreadsheet->getProperties()
            ->setCreator("Sistemas PV")
            ->setLastModifiedBy("PV")
            ->setTitle("Template")
            ->setSubject("template")
            ->setDescription("es un template para realizar creacion reporte de facturas conplementaria")
            ->setKeywords("1")
            ->setCategory("2");


        //obtiene la hoja con la que se tarabajara
        $sheet = $spreadsheet->getActiveSheet();
        //define t5 celdas se usan como emcabezados
        $sheet->setCellValue('A1', 'Serie');
        $sheet->setCellValue('B1', 'Folio');
        $sheet->setCellValue('C1', 'Forma Pago');
        $sheet->setCellValue('D1', 'Lugar Expedicion');
        $sheet->setCellValue('E1', 'Metodo Pago');
        $sheet->setCellValue('F1', 'Moneda');
        $sheet->setCellValue('G1', 'Subtotal');
        $sheet->setCellValue('H1', 'Total');
        $sheet->setCellValue('I1', 'Tipo Comprobante');
        $sheet->setCellValue('J1', 'Version');
        $sheet->setCellValue('K1', 'Nombre Emisor');
        $sheet->setCellValue('L1', 'Regimen Fiscal Emisor');
        $sheet->setCellValue('M1', 'Rfc Emisor');
        $sheet->setCellValue('N1', 'Domicilio Receptor');
        $sheet->setCellValue('O1', 'Nombre Receptor');
        $sheet->setCellValue('P1', 'Regimen Fiscal Receptor');
        $sheet->setCellValue('Q1', 'Rfc Receptor');
        $sheet->setCellValue('R1', 'Uso Cfdi Receptor');
        $sheet->setCellValue('S1', 'UUID');
        $sheet->setCellValue('T1', 'Fecha Comprobante');
        $sheet->setCellValue('U1', 'Fecha Timbre');

        $sheet->getProtection()->setSheet(true);


        $sheet->fromArray($data_factura_complemento["factura"]->map(function ($i) {
            return collect($i)->except("file_name")->flatten(2);
        })->toArray(), NULL, 'A2');

    

        //obtiene el ultimo row insertado ejempolo 4
        $hRow = $sheet->getHighestRow();
        //obtiene la ultima columan insertada ejemplo Z
        $hColumn = $sheet->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn++;

        //agregar estilos(background) en un rango de celdas de la B2:Z2 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);


        //ajusta el tamañp de columan deade la B hasta la ultima insertada
        for ($col = 'A'; $col != $hColumn; ++$col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }


        $spreadsheet->createSheet();

        $sheet2=$spreadsheet->setActiveSheetIndex(1);
        $sheet2->setCellValue('A1', 'Clave producto');
        $sheet2->setCellValue('B1', 'Clave unidad');
        $sheet2->setCellValue('C1', 'Descripcion');
        $sheet2->setCellValue('D1', 'Unidad');
        $sheet2->setCellValue('E1', 'Cantidad');
        $sheet2->setCellValue('F1', 'Valor unitario');
        $sheet2->setCellValue('G1', 'Base impuesto');
        $sheet2->setCellValue('H1', 'Importe impuesto');
        $sheet2->setCellValue('I1', 'Tasa impuesto');
        $sheet2->setCellValue('J1', 'Importe');


        $hColumn2 = $sheet2->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn2++;

        $sheet2->getStyle("A1:" . $sheet2->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet2->getStyle("A1:" . $sheet2->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);

            for ($col = 'A'; $col != $hColumn2; ++$col) {
                $sheet2->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet2->fromArray($data_factura_complemento["conceptos"]->map(function ($i) {            
                return collect($i)->flatten(2);
            })->toArray(), NULL, 'A2');
    
            $spreadsheet->setActiveSheetIndex(0);


        $writer = new Xlsx($spreadsheet);

        // $writer->save('reporte_factura_complemento.xlsx');
        // $img = file_get_contents("reporte_factura_complemento.xlsx");
        // // dd($img);
        // return response($img)->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
       $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
       $writer->save($tempFile);

       $stream = fopen($tempFile, 'r+');
        
        return response(fread($stream,(int)fstat($stream)['size']))->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function getXLSFacturaEmitida(Request $req)
    {
        $data_emitida = SatXMLDAO::getDataExcelFacturaEmitida((object)$req->all());

        //se gregan los demas campos
        // $data_emitida->each(function ($i) use ($data_emitida) {
        //     $data_emitida["headers"]->each(function ($j) use ($i, $data_emitida) {

        //         $data_temp = $data_emitida["body_extra"]->first(function ($k) use ($i, $j) {
        //             return $k->uuid == $i->file_name && $k->tipo == $j->tipo &&  $k->concepto == $j->concepto;
        //         });
        //         $i->{$j->concepto} = $data_temp->importe ?? "";
        //     });
        // });


        //crea hoja de calculo
        $spreadsheet = new Spreadsheet();

        //asigna los metadatos
        $spreadsheet->getProperties()
            ->setCreator("Sistemas PV")
            ->setLastModifiedBy("PV")
            ->setTitle("Template")
            ->setSubject("template")
            ->setDescription("es un template para realizar creacion reporte de nomina")
            ->setKeywords("1")
            ->setCategory("2");


        //obtiene la hoja con la que se tarabajara
        $sheet = $spreadsheet->getActiveSheet();
        //define t5 celdas se usan como emcabezados
        $sheet->setCellValue('A1', 'Serie');
        $sheet->setCellValue('B1', 'Folio');
        $sheet->setCellValue('C1', 'Forma Pago');
        $sheet->setCellValue('D1', 'Lugar Expedicion');
        $sheet->setCellValue('E1', 'Metodo Pago');
        $sheet->setCellValue('F1', 'Moneda');
        $sheet->setCellValue('G1', 'Subtotal');
        $sheet->setCellValue('H1', 'Total');
        $sheet->setCellValue('I1', 'Tipo Comprobante');
        $sheet->setCellValue('J1', 'Version');
        $sheet->setCellValue('K1', 'Nombre Emisor');
        $sheet->setCellValue('L1', 'Regimen Fiscal Emisor');
        $sheet->setCellValue('M1', 'Rfc Emisor');
        $sheet->setCellValue('N1', 'Domicilio Receptor');
        $sheet->setCellValue('O1', 'Nombre Receptor');
        $sheet->setCellValue('P1', 'Regimen Fiscal Receptor');
        $sheet->setCellValue('Q1', 'Rfc Receptor');
        $sheet->setCellValue('R1', 'Uso Cfdi Receptor');
        $sheet->setCellValue('S1', 'UUID');
        $sheet->setCellValue('T1', 'Fecha Comprobante');
        $sheet->setCellValue('U1', 'Fecha Timbre');

        //se oculta la columan A, por que se encuentran los datos de los id de alumnos
        /////$sheet->getColumnDimension('A')->setVisible(false);
        //se oculta la fila o row 1, en ella se encuentran los datos de las preguntas
        // $sheet->getRowDimension('1')->setVisible(false);
        //se ahbilita la seguridad en el documento, falta especificar el nivel de proteccion
        $sheet->getProtection()->setSheet(true);


        //llenado de los datos medinate fromArray

        // dd($data_nomina["headers"]->map(function($i){return $i->concepto;})->toArray());
        //cabecera dinamica
        // $sheet->fromArray($data_nomina["headers"]->map(function ($i) {
        //     return $i->concepto_header;
        // })->toArray(), NULL, 'Z1');

        $sheet->fromArray($data_emitida["factura"]->map(function ($i) {
            return collect($i)->except("file_name")->flatten(2);
        })->toArray(), NULL, 'A2');

        //$sheet->fromArray([1, 2, 3, 4], NULL, 'A3');


        //obtiene el ultimo row insertado ejempolo 4
        $hRow = $sheet->getHighestRow();
        //obtiene la ultima columan insertada ejemplo Z
        $hColumn = $sheet->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn++;

        //agregar estilos(background) en un rango de celdas de la B2:Z2 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);


        //ajusta el tamañp de columan deade la B hasta la ultima insertada
        for ($col = 'A'; $col != $hColumn; ++$col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }


        $spreadsheet->createSheet();

        $sheet2=$spreadsheet->setActiveSheetIndex(1);
        $sheet2->setCellValue('A1', 'Clave producto');
        $sheet2->setCellValue('B1', 'Clave unidad');
        $sheet2->setCellValue('C1', 'Descripcion');
        $sheet2->setCellValue('D1', 'Unidad');
        $sheet2->setCellValue('E1', 'Cantidad');
        $sheet2->setCellValue('F1', 'Valor unitario');
        $sheet2->setCellValue('G1', 'Base impuesto');
        $sheet2->setCellValue('H1', 'Importe impuesto');
        $sheet2->setCellValue('I1', 'Tasa impuesto');
        $sheet2->setCellValue('J1', 'Importe');


        $hColumn2 = $sheet2->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn2++;

        $sheet2->getStyle("A1:" . $sheet2->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet2->getStyle("A1:" . $sheet2->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);

            for ($col = 'A'; $col != $hColumn2; ++$col) {
                $sheet2->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet2->fromArray($data_emitida["conceptos"]->map(function ($i) {            
                return collect($i)->flatten(2);
            })->toArray(), NULL, 'A2');
    
            $spreadsheet->setActiveSheetIndex(0);


        $writer = new Xlsx($spreadsheet);

        // $writer->save('reporte.xlsx');
        // $img = file_get_contents("reporte.xlsx");
        // // dd($img);
        // return response($img)->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
       $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
       $writer->save($tempFile);

       $stream = fopen($tempFile, 'r+');
        
        return response(fread($stream,(int)fstat($stream)['size']))->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }


    public function cancelarDocumento($uuid)
    {
          SatXMLDAO::cancelarDocumento($uuid);
    }

    public function cancelarDocumentoComplemento($uuid)
    {
        SatXMLDAO::cancelarDocumentoComplemento($uuid);
    }

    public function cancelarDocumentoNomina($uuid)
    {
        SatXMLDAO::cancelarDocumentoNomina($uuid);
    }

    public function cancelarDocumentoEmitido($uuid)
    {
        SatXMLDAO::cancelarDocumentoEmitido($uuid);
    }
    


}
