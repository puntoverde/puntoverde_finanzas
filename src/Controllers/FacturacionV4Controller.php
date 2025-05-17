<?php

namespace App\Controllers;
use App\DAO\FacturacionV4DAO;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class FacturacionV4Controller extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function createFactura($id,Request $req){
        
        $reglas = [
            "accion"=>"required",
            "idpago"=>"required",
            "id_dato_factura"=>"required",            

            "cliente.rfc"=>"required|min:12|max:13", 
            "cliente.razonSocial"=>"required", 
            "cliente.curp"=>"required", 
            "cliente.correo"=>"required|email", 
            "cliente.metodoPago"=>"required", 
            "cliente.usoCFDI"=>"required", 
            "cliente.regimenFiscal"=>"required", 

            "domicilio.calle"=>"required",
            "domicilio.numExt"=>"required",             
            "domicilio.colonia"=>"required", 
            "domicilio.cp"=>"required|digits:5",            
            "domicilio.municipio"=>"required",
            "domicilio.estado"=>"required",
            "domicilio.pais"=>"required",

            "movimientos"=>"required|array",
            "fecha_pago"=>"required",
            "folio_pv"=>"required",
            "observaciones"=>"required",
        ];
        $this->validate($req, $reglas);
        
        try{
        $base_uri_compac=env('API_URL_COMPAC', 'NA');
        $clienteHttp=new Client(['base_uri' => $base_uri_compac,'timeout'=>600]);
        $dataSend=['json'=>$req->all()];

        $response=$clienteHttp->request('POST','factura/'.$id,$dataSend);

        $response_data=json_decode($response->getBody()->getContents());
        
        $data_req=$req->only(['accion','idpago','id_dato_factura','cliente.metodoPago','cliente.usoCFDI']);
        $data_req=collect($data_req)->put("metodoPago",$data_req["cliente"]["metodoPago"]);
        $data_req=collect($data_req)->put("usoCFDI",$data_req["cliente"]["usoCFDI"])->toArray();     
        
        if($response_data->IError==0)        
            $id_factura=FacturacionV4DAO::createFactura($req->input("accion"),(object)$data_req,$response_data);
        else 
            $id_factura=FacturacionV4DAO::createFacturaFail($req->input("accion"),(object)$data_req,$response_data);

        return response()->json(["id_factura"=>$id_factura,"factura_res"=>$response_data]);
        
        }
        catch(\Exception $e)
        {
        echo $e->getMessage();
        return response("Error no es posible generar factura...",500);
        }


       //return FacturacionV4DAO::createDatosFacturacion($id,(object)$req->all());
    }

    //crea datos de facturacion y factura
    public function createDatosFactura($id,Request $req)
    {
        $reglas = [
            "accion"=>"required",
            "idpago"=>"required",
            "pertenece"=>"required",        

            "cliente.rfc"=>"required|min:12|max:13", 
            "cliente.razonSocial"=>"required", 
            "cliente.curp"=>"required", 
            "cliente.correo"=>"required|email", 
            "cliente.metodoPago"=>"required", 
            "cliente.usoCFDI"=>"required", 
            "cliente.regimenFiscal"=>"required", 

            "domicilio.calle"=>"required",
            "domicilio.numExt"=>"required",             
            "domicilio.colonia"=>"required", 
            "domicilio.cp"=>"required|digits:5",            
            "domicilio.municipio"=>"required",
            "domicilio.estado"=>"required",
            "domicilio.pais"=>"required",

            "movimientos"=>"required|array",
            "fecha_pago"=>"required",
            "folio_pv"=>"required",
            "observaciones"=>"required",
        ];

        $this->validate($req, $reglas);
        
        $id_datos_facturacion=0;
        try{
        $cliente=(object)$req->input('cliente');
        $domicilio=(object)$req->input('domicilio');
        $pertenece=$req->input('pertenece');
        
        $id_datos_facturacion=FacturacionV4DAO::createDatosFacturacion($cliente,$domicilio,$pertenece);                

        if($id_datos_facturacion>0)
        {
            $base_uri_compac=env('API_URL_COMPAC', 'NA');
            $clienteHttp=new Client(['base_uri' => $base_uri_compac,'timeout'=>600]);
            $dataSend=['json'=>$req->all()];

            $response=$clienteHttp->request('POST','factura/'.$id,$dataSend);

            $response_data=json_decode($response->getBody()->getContents());
        
            $data_req=$req->only(['accion','idpago','cliente.metodoPago','cliente.usoCFDI']);
            $data_req=collect($data_req)->put("metodoPago",$data_req["cliente"]["metodoPago"]);
            $data_req=collect($data_req)->put("usoCFDI",$data_req["cliente"]["usoCFDI"])->toArray();

            
            $data_req["id_dato_factura"]=$id_datos_facturacion;

            if($response_data->IError==0)        
            $id_factura=FacturacionV4DAO::createFactura($req->input("accion"),(object)$data_req,$response_data);
        else 
            $id_factura=FacturacionV4DAO::createFacturaFail($req->input("accion"),(object)$data_req,$response_data);
           
            return response()->json(["id_factura"=>$id_factura,"factura_res"=>$response_data]);
        }
          
          }
          catch(\Exception $e)
          {      
            echo $e->getMessage();     
            return response("Error no es posible generar factura...",500);
          }




    }

    public function createFacturaPublicoGeneral(Request $req)
    {       
           
        $reglas = [
            "accion"=>"required",
            "idpago"=>"required",
            "id_dato_factura"=>"required", 
            
            "metodoPago"=>"required",

            "movimientos"=>"required|array",
            "fecha_pago"=>"required",
            "folio_pv"=>"required",
            "observaciones"=>"required",

            "razonSocial"=>"required"
        ];
        $this->validate($req, $reglas);
      
        try{
        $base_uri_compac=env('API_URL_COMPAC', 'NA');
        $clienteHttp=new Client(['base_uri' => $base_uri_compac,'timeout'=>600]);
        $dataSend=['json'=>$req->all()];

        $response=$clienteHttp->request('POST','factura_publico_general',$dataSend);

        $response_data=json_decode($response->getBody()->getContents());
        
        $data_req=(object)$req->only(['accion','idpago','id_dato_factura','metodoPago']);
        $data_req->usoCFDI="S01";//nuevo para la factura v4

       
        if($response_data->IError==0)        
            $id_factura=FacturacionV4DAO::createFactura($req->input("accion"),$data_req,$response_data);
        else 
            $id_factura=FacturacionV4DAO::createFacturaFail($req->input("accion"),$data_req,$response_data);        
        return response()->json(["id_factura"=>$id_factura,"factura_res"=>$response_data]);

        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return response($e->getMessage(),500);
        }

    }

    public function updateDatosFacturacion($id,Request $req){     
         

        $reglas = [
            "accion"=>"required",
            "idpago"=>"required",
            "pertenece"=>"required",        

            "cliente.rfc"=>"required|min:12|max:13", 
            "cliente.razonSocial"=>"required", 
            "cliente.curp"=>"required", 
            "cliente.correo"=>"required|email", 
            "cliente.metodoPago"=>"required", 
            "cliente.usoCFDI"=>"required", 
            "cliente.regimenFiscal"=>"required", 

            "domicilio.calle"=>"required",
            "domicilio.numExt"=>"required",             
            "domicilio.colonia"=>"required", 
            "domicilio.cp"=>"required|digits:5",            
            "domicilio.municipio"=>"required",
            "domicilio.estado"=>"required",
            "domicilio.pais"=>"required",

            "movimientos"=>"required|array",
            "fecha_pago"=>"required",
            "folio_pv"=>"required",
            "observaciones"=>"required",
        ];

            $this->validate($req, $reglas);
            try{
        $cliente=(object)$req->input('cliente');
        $domicilio=(object)$req->input('domicilio');
        $pertenece=$req->input('pertenece');
            
       $id_datos_fact=FacturacionV4DAO::updateDatosFacturacion($id,$cliente,$domicilio,$pertenece);

       if($id_datos_fact>0)
       {
           $base_uri_compac=env('API_URL_COMPAC', 'NA');
           $clienteHttp=new Client(['base_uri' => $base_uri_compac,'timeout'=>600]);
           $dataSend=['json'=>$req->all()];

           $response=$clienteHttp->request('POST','factura/'.$id,$dataSend);

           $response_data=json_decode($response->getBody()->getContents());
       
           $data_req=$req->only(['accion','idpago','cliente.metodoPago','cliente.usoCFDI']);                     
           
           $data_req["id_dato_factura"]=$id_datos_fact;

           
            if($response_data->IError==0)        
                $id_factura=FacturacionV4DAO::createFactura($req->input("accion"),(object)$data_req,$response_data);
            else 
                $id_factura=FacturacionV4DAO::createFacturaFail($req->input("accion"),(object)$data_req,$response_data);
           return response()->json(["id_factura"=>$id_factura,"factura_res"=>$response_data]);

        }
           }
        catch(\Exception $e)
        {         
            echo $e->getMessage();  
          return response("Error no es posible generar factura...",500);
        }

       }
    

    
       public function bajaDatosFacturacion($id,Request $req){
        return FacturacionV4DAO::bajaDatosFacturacion($id,(object)$req->all());
     }

    

     public function getSociosPG($id){
        return FacturacionV4DAO::getSociosPG($id);
    }
    



    }
