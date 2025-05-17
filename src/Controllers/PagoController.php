<?php

namespace App\Controllers;
use App\DAO\CuotaDAO;
use App\DAO\PagosDAO;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Validation\Rule;

class PagoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getPagos(Request $req){
        // $this->validate($req,["fecha_inicio"=>"required|date|before_or_equal:fecha_fin","fecha_fin"=>"required|date|after_or_equal:fecha_inicio"]);
        return PagosDAO::getPagos((object)$req->all());
    }
    
    public function getCargosPago($id){
        return PagosDAO::getCargosPago($id);
    }

    public function getDatosFactura($id){
        return response()->json(PagosDAO::getDatosFactura($id));
    }

    public function getListaDatosFacturacion(Request $req){        
        return PagosDAO::getListaDatosFacturacion((object) $req->all());
    }

    public function setDatoFacturacion(Request $req)
    {
        
        #region validacion
        $this->validate($req,[
                              "codigo_cliente"=>"required",
                              "cve_persona"=>"required|numeric",
                            //   "rfc"=>"required|size:13",
                              "rfc"=>"required",
                              "razon_social"=>"required",
                              "correo"=>"required|email",
                              "cp"=>"required|size:5",
                              "calle"=>"required",
                              "num_ext"=>"required",
                              "colonia"=>"required",
                              "municipio"=>"required",
                              "estado"=>"required",
                              "pais"=>"required",
                              "idpago"=>"required",
                              "metodo_pago"=>"required",
                              "uso_cfdi"=>"required",
                              "forma_pago"=>"required",                              
                              "cargos"=>"required|array",
                              "observaciones"=>"required",
                              "fecha_pago"=>"required",
                              "folio_pv"=>"required"
                              ]);
        #endregion

        $data_factura=(object)$req->all();

        // $idfactura = PagosDAO::createDatosFactura($data_factura);

        // if($idfactura>0)
        // {
        //     try{
        //     $cliente=new Client(['base_uri' => 'http://192.168.1.102:8015/api/']);
        //     $data=['json'=>[
        //             "codigo_cliente"=>$data_factura->codigo_cliente,
        //             "razon_social"=>mb_strtoupper($data_factura->razon_social, 'UTF-8'),
        //             "rfc"=>mb_strtoupper($data_factura->rfc, 'UTF-8'),
        //             "correo"=>$data_factura->correo,
        //             "cp"=>$data_factura->cp,
        //             "calle"=>mb_strtoupper($data_factura->calle, 'UTF-8'),
        //             "num_ext"=>mb_strtoupper($data_factura->num_ext, 'UTF-8'),
        //             "num_int"=>mb_strtoupper($data_factura->num_int, 'UTF-8'),
        //             "colonia"=>mb_strtoupper($data_factura->colonia, 'UTF-8'),
        //             "municipio"=>mb_strtoupper($data_factura->municipio, 'UTF-8'),
        //             "estado"=>mb_strtoupper($data_factura->estado, 'UTF-8'),
        //             "pais"=>mb_strtoupper($data_factura->pais, 'UTF-8'),
        //             "Documentos"=>$data_factura->cargos,                    
        //             "forma_pago"=>$data_factura->forma_pago,
        //             "uso_cfdi"=>$data_factura->uso_cfdi,
        //             "metodo_pago"=>$data_factura->metodo_pago,
        //             "observaciones"=>$data_factura->observaciones,
        //             "fecha_pago"=>$data_factura->fecha_pago,
        //             "folio_pv"=>$data_factura->folio_pv
        //         ]];
        //     $responseG=$cliente->request('POST','factura/'.$data_factura->codigo_cliente,$data);
        //     $datos_factura=json_decode($responseG->getBody()->getContents());


        //      PagosDAO::updateFactura($idfactura,(object)$datos_factura);
        //      return ["idfactura"=>$idfactura,"factura_comercial"=>$datos_factura];
        //     }
        //     catch(\Exception $e){
        //      $facturafail=["folio"=>"0","uuid"=>"","estado"=>"0","IError"=>"500","IErrorMessage"=>$e->getMessage()];
        //      PagosDAO::updateFactura($idfactura,(object)$facturafail);
        //      return ["idfactura"=>$idfactura,"factura_comercial"=>$facturafail];
        //     }

        // }
        // else{
        //     $facturafail=["folio"=>"0","uuid"=>"","estado"=>"0","IError"=>"500","IErrorMessage"=>"fallo en guardar datos nuevos de facturacion..."];
        //     PagosDAO::updateFactura($idfactura,(object)$facturafail);
        //     return ["idfactura"=>0,"factura_comercial"=>$facturafail];

        // }
    }
    
    public function updateDatoFacturacion($id,Request $req)
    {
        #region validacion
        $this->validate($req,[
                              "codigo_cliente"=>"required",
                              "cve_persona"=>"required|numeric",
                            //   "rfc"=>"required|size:13",
                              "rfc"=>"required",
                              "razon_social"=>"required",
                              "correo"=>"required|email",
                              "cp"=>"required|size:5",
                              "calle"=>"required",
                              "num_ext"=>"required",
                              "colonia"=>"required",
                              "municipio"=>"required",
                              "estado"=>"required",
                              "pais"=>"required",
                              "idpago"=>"required",
                              "metodo_pago"=>"required",
                              "uso_cfdi"=>"required",
                              "forma_pago"=>"required",                              
                              "cargos"=>"required|array",
                              "observaciones"=>"required",
                              "fecha_pago"=>"required",
                              "folio_pv"=>"required"
                              ]);
        #endregion

        $data_factura=(object)$req->all();

        $idfactura = PagosDAO::updateDatosFactura($id,$data_factura);

        if($idfactura>0)
        {
            try{
            $cliente=new Client(['base_uri' => 'http://192.168.1.1222:8015/api/']);
            $data=['json'=>[
                    "codigo_cliente"=>$data_factura->codigo_cliente,
                    "razon_social"=>mb_strtoupper($data_factura->razon_social, 'UTF-8'),
                    "rfc"=>mb_strtoupper($data_factura->rfc, 'UTF-8'),
                    "correo"=>$data_factura->correo,
                    "cp"=>$data_factura->cp,
                    "calle"=>mb_strtoupper($data_factura->calle, 'UTF-8'),
                    "num_ext"=>mb_strtoupper($data_factura->num_ext, 'UTF-8'),
                    "num_int"=>mb_strtoupper($data_factura->num_int, 'UTF-8'),
                    "colonia"=>mb_strtoupper($data_factura->colonia, 'UTF-8'),
                    "municipio"=>mb_strtoupper($data_factura->municipio, 'UTF-8'),
                    "estado"=>mb_strtoupper($data_factura->estado, 'UTF-8'),
                    "pais"=>mb_strtoupper($data_factura->pais, 'UTF-8'),
                    "Documentos"=>$data_factura->cargos,                    
                    "forma_pago"=>$data_factura->forma_pago,
                    "uso_cfdi"=>$data_factura->uso_cfdi,
                    "metodo_pago"=>$data_factura->metodo_pago,
                    "observaciones"=>$data_factura->observaciones,
                    "fecha_pago"=>$data_factura->fecha_pago,
                    "folio_pv"=>$data_factura->folio_pv
                ]];
            $responseG=$cliente->request('POST','factura/'.$data_factura->codigo_cliente,$data);
            $datos_factura=json_decode($responseG->getBody()->getContents());


             PagosDAO::updateFactura($idfactura,(object)$datos_factura);
             return ["idfactura"=>$idfactura,"factura_comercial"=>$datos_factura];
            }
            catch(\Exception $e){
             $facturafail=["folio"=>"0","uuid"=>"","estado"=>"0","IError"=>"500","IErrorMessage"=>$e->getMessage()];
             PagosDAO::updateFactura($idfactura,(object)$facturafail);
             return ["idfactura"=>$idfactura,"factura_comercial"=>$facturafail];
            }

        }
        else{
            $facturafail=["folio"=>"0","uuid"=>"","estado"=>"0","IError"=>"500","IErrorMessage"=>"fallo en guardar datos nuevos de facturacion..."];
            PagosDAO::updateFactura($idfactura,(object)$facturafail);
            return ["idfactura"=>0,"factura_comercial"=>$facturafail];

        }
    }

    public function setFactura(Request $req)
    {
        
        $this->validate($req,[
            "id_datos_facturacion"=>"required|numeric",
            "codigo_cliente"=>"required",            
            "rfc"=>"required",
            "razon_social"=>"required",
            "correo"=>"required|email",
            "cp"=>"required|size:5",
            "calle"=>"required",
            "num_ext"=>"required",
            "colonia"=>"required",
            "municipio"=>"required",
            "estado"=>"required",
            "pais"=>"required",
            "idpago"=>"required",
            "metodo_pago"=>"required",
            "uso_cfdi"=>"required",
            "forma_pago"=>"required",                   
            "cargos"=>"required|array",
            "observaciones"=>"required",
            "fecha_pago"=>"required",
            "folio_pv"=>"required"
            ]);

            $data_factura=(object)$req->all();

            $idfactura = PagosDAO::createFactura($data_factura);


        if($idfactura>0)
        {
            try{
            $cliente=new Client(['base_uri' => 'http://192.168.1.1222:8015/api/']);
            $data=['json'=>[
                    "codigo_cliente"=>$data_factura->codigo_cliente,
                    "razon_social"=>mb_strtoupper($data_factura->razon_social, 'UTF-8'),
                    "rfc"=>mb_strtoupper($data_factura->rfc, 'UTF-8'),
                    "correo"=>$data_factura->correo,
                    "cp"=>$data_factura->cp,
                    "calle"=>mb_strtoupper($data_factura->calle, 'UTF-8'),
                    "num_ext"=>mb_strtoupper($data_factura->num_ext, 'UTF-8'),
                    "num_int"=>mb_strtoupper($data_factura->num_int, 'UTF-8'),
                    "colonia"=>mb_strtoupper($data_factura->colonia, 'UTF-8'),
                    "municipio"=>mb_strtoupper($data_factura->municipio, 'UTF-8'),
                    "estado"=>mb_strtoupper($data_factura->estado, 'UTF-8'),
                    "pais"=>mb_strtoupper($data_factura->pais, 'UTF-8'),
                    "Documentos"=>$data_factura->cargos,                   
                    "forma_pago"=>$data_factura->forma_pago,
                    "uso_cfdi"=>$data_factura->uso_cfdi,
                    "metodo_pago"=>$data_factura->metodo_pago,
                    "observaciones"=>$data_factura->observaciones,
                    "fecha_pago"=>$data_factura->fecha_pago,
                    "folio_pv"=>$data_factura->folio_pv
                ]];
            // $responseG=$cliente->request('POST','factura/'.$data_factura->codigo_cliente,$data);
            $responseG=$cliente->request('POST','factura/'.$data_factura->codigo_cliente,$data);
            $datos_factura=json_decode($responseG->getBody()->getContents());

            PagosDAO::updateFactura($idfactura,$datos_factura);            

            return ["idfactura"=>$idfactura,"factura_comercial"=>$datos_factura];;

            }
            catch(\Exception $e){
                $facturafail=["folio"=>"0","uuid"=>"","estado"=>"0","IError"=>"500","IErrorMessage"=>$e->getMessage()];
                PagosDAO::updateFactura($idfactura,(object)$facturafail); 
                return ["idfactura"=>$idfactura,"factura_comercial"=>$facturafail];
            }
        }
    }
   
    public function updateFactura(Request $req,$id)
    {
        return PagosDAO::updateFacturaEstado($id,(object)$req->all());
    }

    public function updateFacturaComplete(Request $req,$id)
    {
        return PagosDAO::updateFactura($id,(object)$req->all());
    }

    public function getFormaPago()
    {
        return PagosDAO::getFormaPago();
    }

    public function getUsoCfdi()
    {
        return PagosDAO::getUsoCfdi();
    }

    public function getSociosAccion(Request $req)
    {
        $this->validate($req,["cve_accion"=>"required"]);
        return PagosDAO::getSociosAccion((object)$req->all());
    }


    //**publico en general */

    public function setFacturaPublicoGeneral(Request $req)
    {
        $this->validate($req,[
            "idpago"=>"required",
            "metodo_pago"=>"required",
            "uso_cfdi"=>"required",
            "forma_pago"=>"required",                         
            "cargos"=>"required|array",
            "observaciones"=>"required",
            "fecha_pago"=>"required",
            "folio_pv"=>"required"
            ]);

            $data_factura=(object)$req->all();

            // return $data_factura->observaciones;

            $idfactura = PagosDAO::createFactura($data_factura);


        if($idfactura>0)
        {
            try{
            $cliente=new Client(['base_uri' => 'http://192.168.1.1222:8015/api/']);
            $data=['json'=>[
                    "Documentos"=>$data_factura->cargos,                                   
                    "forma_pago"=>$data_factura->forma_pago,
                    "uso_cfdi"=>$data_factura->uso_cfdi,
                    "metodo_pago"=>$data_factura->metodo_pago,
                    "observaciones"=>$data_factura->observaciones,
                    "fecha_pago"=>$data_factura->fecha_pago,
                    "folio_pv"=>$data_factura->folio_pv
                ]];
            $responseG=$cliente->request('POST','factura_publico_general',$data);
            $datos_factura=json_decode($responseG->getBody()->getContents());

            PagosDAO::updateFactura($idfactura,$datos_factura);            

            return ["idfactura"=>$idfactura,"factura_comercial"=>$datos_factura];;

            }
            catch(\Exception $e){
                $facturafail=["folio"=>"0","uuid"=>"","estado"=>"0","IError"=>"500","IErrorMessage"=>$e->getMessage()];
                PagosDAO::updateFactura($idfactura,(object)$facturafail); 
                return ["idfactura"=>0,"factura_comercial"=>$facturafail];
            }
        }
    }

    
    public function eliminarFactura(Request $req,$id)
    {        
        //var_dump($req->all());
        PagosDAO::eliminarFactura($id,(object)$req->all());
        return response(null,204);
    }

    public function getVista(){
       return view('welcome');
    }

    
}
