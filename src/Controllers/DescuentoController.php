<?php

namespace App\Controllers;
use App\DAO\DescuentoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class DescuentoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getCargos(Request $req){
        $reglas = [ 
            "numero_accion"=>"required|integer", 
            "clasificacion"=>"required|integer"];
            $this->validate($req, $reglas);
        return DescuentoDAO::getCargos((object)$req->all());
    }

    public function cuotasObligatorias(){
        return DescuentoDAO::cuotasObligatorias();
    }

    public function sociosAplicaCuota(Request $req){
        $reglas = [ 
            "parentesco"=>"required|array", 
            "parentesco.*"=>"integer",
            "cve_accion"=>"required|integer",
            "edad"=>"required|integer"];
            $this->validate($req, $reglas);
        return DescuentoDAO::sociosAplicaCuota((object)$req->all());
    }

    public function duenoAplicaCuota(Request $req){
        $reglas = [ 
            "membresias"=>"required|array", 
            "membresias.*"=>"integer",
            "cve_accion"=>"required|integer"];
            $this->validate($req, $reglas);
        return response()->json(DescuentoDAO::duenoAplicaCuota((object)$req->all()));
    }

    public function aplicarDescuento(Request $req){
        $reglas = [ 
            "cargos"=>"required|array", 
            "cargos.*.cve_cargo"=>"integer",
            "cargos.*.monto"=>"numeric",
            "total_descuento"=>"required|numeric",
            "responsable"=>"required|integer", 
            "descripcion"=>"required"];
            $this->validate($req, $reglas);
        return DescuentoDAO::aplicarDescuento((object)$req->all());
    }

    public function DescuentosProgramados(Request $req){
        $reglas = [ 
            "cve_accion"=>"required|integer",
            "cve_persona"=>"required|integer", 
            "cve_cuota"=>"required|integer", 
            "cve_persona_aplica"=>"required|integer", 
            "monto"=>"required|numeric",
            "periodos"=>"required|array", 
            "periodos.*"=>"string"];
            $this->validate($req, $reglas);
        return DescuentoDAO::DescuentosProgramados((object)$req->all());
    }
    
}
