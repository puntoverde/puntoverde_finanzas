<?php

namespace App\Controllers;
use App\DAO\DatosFacturacionDAO;
use App\Entity\DatosFacturacion;
use App\Entity\Locker;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class DatosFacturacionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getDatosFacturacion($id){        
        return DatosFacturacionDAO::getDatosFacturacion($id);
    }

    public function getSocios(Request $req){
        return DatosFacturacionDAO::getSocios((object)$req->all());
    }

    public function createDatosFacturacion($id,Request $req){      
        $reglas = [
            "regimen_fiscal"=>"required|numeric",
            "razon_social"=>"required", 
            "rfc"=>"required|min:12|max:13", 
            "correo"=>"required|email", 
            "cp"=>"required|digits:5",
            "calle"=>"required",
            "num_ext"=>"required",             
            "colonia"=>"required", 
            "municipio"=>"required",
            "estado"=>"required",
            "pais"=>"required",
        ];
        $this->validate($req, $reglas);
       return DatosFacturacionDAO::createDatosFacturacion($id,(object)$req->all());
    }

    public function updateDatosFacturacion($id,Request $req){
        $reglas = [
            "regimen_fiscal"=>"required|numeric",
            "razon_social"=>"required", 
            "rfc"=>"required|min:12|max:13", 
            "correo"=>"required|email", 
            "cp"=>"required|digits:5",
            "calle"=>"required",
            "num_ext"=>"required",             
            "colonia"=>"required", 
            "municipio"=>"required",
            "estado"=>"required",
            "pais"=>"required"
        ];
            $this->validate($req, $reglas);
       return DatosFacturacionDAO::updateDatosFacturacion($id,(object)$req->all());
    }

    public function bajaDatosFacturacion($id,Request $req){
        return DatosFacturacionDAO::bajaDatosFacturacion($id,(object)$req->all());
     }

    public function verificarDatosFacturacion()
    {
       return json_encode(DatosFacturacionDAO::verificarDatosFacturacion(2,1));
    }

    public function getRegimenFiscal()
    {
        return DatosFacturacionDAO::getRegimenFiscal();
    }
    
}
