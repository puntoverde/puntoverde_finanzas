<?php

namespace App\Controllers;
use App\DAO\CambiarFormaPagoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use App\Util\ApiResponse;

class CambiarFormaPagoController extends Controller
{
    use ApiResponse;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function eliminarPago(Request $req){
        return CambiarFormaPagoDAO::eliminarPago((object)$req->all());
    }

    public function consultarCargo(Request $req){
        return $this->responseJson(CambiarFormaPagoDAO::consultarCargo((object)$req->all()));
        // return CambiarFormaPagoDAO::consultarCargo((object)$req->all());
    }

    public function getFormasPagoAsignada($id){
        return CambiarFormaPagoDAO::getFormasPagoAsignada($id);
    }

    public function getFormasPago(){
        return CambiarFormaPagoDAO::getFormasPago();
    }

    public function updateFormapago($id,Request $req){
        $clave=$req->input("clave");
        $persona=$req->input("persona");
        return CambiarFormaPagoDAO::updateFormapago($id,$clave,$persona);
    }

    public function updateMonto($id,Request $req){
        $monto=$req->input("monto");
        $persona=$req->input("persona");
        return CambiarFormaPagoDAO::updateMonto($id,$monto,$persona);
    }

    public function addFormaPago($id){
        return CambiarFormaPagoDAO::addFormaPago($id);
    }

    public function deleteFormaPago($id){
        return CambiarFormaPagoDAO::deleteFormaPago($id);
    }

    public function updateMontoCargo($id,Request $req){
        $monto=$req->input("monto");
        $persona=$req->input("persona");
        return CambiarFormaPagoDAO::updateMontoCargo($id,$monto,$persona);
    }

    public function getCuotas(Request $req){
        return CambiarFormaPagoDAO::getCuotas((object)$req->all());
    }

    public function updateCuota($id,Request $req){
        return CambiarFormaPagoDAO::updateCuota($id,(object)$req->all());
    }

    public function getCajeros(){
        return $this->responseJson(CambiarFormaPagoDAO::getCajeros());
    }
    public function updateCajero($id,Request $req){
        $cajero=$req->input("cajero");
        $persona=$req->input("persona");
        return CambiarFormaPagoDAO::updateCajero($id,$cajero,$persona);
    }
    public function updateFecha($id,Request $req){
        $fecha=$req->input("fecha");
        $persona=$req->input("persona");
        return CambiarFormaPagoDAO::updateFecha($id,$fecha,$persona);
    }

    
}
