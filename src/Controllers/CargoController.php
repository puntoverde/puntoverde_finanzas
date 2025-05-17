<?php
namespace App\Controllers;
use App\DAO\CargoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class CargoController extends Controller
{

    public function __construct(){}

    public function getCargo(Request $p){
        $this->validate($p,["numero_accion"=>"required|numeric","clasificacion"=>"required|numeric"]); 
        return CargoDAO::Cargo((object)$p->all());
    }

    public function getDeleteCargo($cve, Request $p){
        $this->validate($p,["responsable_cancela"=>"required|numeric","motivo_cancelacion"=>"required","activa_accion"=>"required"]);
        return CargoDAO::deleteCargo($cve, (object)$p->all());
    }

    public function getDeleteCargoReporte (Request $p){
        $this->validate($p,["fecha_inicio"=>"required|date","fecha_fin"=>"required|date|after_or_equal:fecha_inicio"]);
        return CargoDAO::cargoReporte((object)$p->all());
    }


    public function getEstatusAccionByCargo($id){
        return CargoDAO::getEstatusAccionByCargo($id);
    }

}


