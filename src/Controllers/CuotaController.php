<?php

namespace App\Controllers;
use App\DAO\CuotaDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Validation\Rule;

class CuotaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getCuotas(Request $req){
        return CuotaDAO::getCuotas((object)$req->all());
    }

    public function getCuotaById($id){
        return CuotaDAO::getCuotaById($id);
    }

    public function createCuota(Request $req){
        $reglas = [
        "clave"=>"required|integer",
        "numero_cuota"=>"required|integer", 
        "cuota"=>"required", 
        "importe"=>"required|numeric", 
        "opcion_iva"=>["required","integer",Rule::in([1,2])],
        "tipo_cuota"=>["required","integer",Rule::in([1,2])], 
        "parentescos"=>["array",Rule::requiredIf(function()use($req){return $req->has("tipo_cuota") && $req->input("tipo_cuota")==1;})], 
        "parentescos.*"=>"integer",
        "membresias"=>["array",Rule::requiredIf(function()use($req){return $req->has("tipo_cuota") && $req->input("tipo_cuota")==2;})],
        "membresias.*"=>"integer",
        "genero_aplica"=>["required",Rule::in(['H','M','A'])],
        "edad_aplica"=>"required|integer", 
        "acceso"=>["required","integer",Rule::in([0,1])], 
        "limite_pago"=>"required|integer", 
        "veces_aplica"=>"required|integer",
        "carga_automatica"=>["required","integer",Rule::in([0,1])], 
        "ciclo"=>["integer","min:$req->input('carga_automatica')",Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})], 
        "tipo_ciclo"=>["integer",Rule::in([0,1,2,3]),Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})], 
        "fecha_vigor"=>["date",Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})],
        "recargo_aplica"=>["required","integer",Rule::in([0,1])], 
        "recargo_unico"=>["integer",Rule::in([0,1]),Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})], 
        "recargo_cantidad"=>["numeric",Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})], 
        "recargo_cada"=>["integer",Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})],
        "editable"=>["required","integer",Rule::in([0,1])],
        "mes_siguiente"=>["integer",Rule::in([0,1]),Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})]];
        $this->validate($req, $reglas);
        return CuotaDAO::createCuota((object)$req->all());
    }

    public function updateCuota($id,Request $req){
        $reglas = [
        "clave"=>"required|integer", 
        "numero_cuota"=>"required|integer",
        "cuota"=>"required", 
        "importe"=>"required|numeric", 
        "opcion_iva"=>["required","integer",Rule::in([1,2])],
        "tipo_cuota"=>["required","integer",Rule::in([1,2])], 
        "parentescos"=>["array",Rule::requiredIf(function()use($req){return $req->has("tipo_cuota") && $req->input("tipo_cuota")==1;})], 
        "parentescos.*"=>"integer",
        "membresias"=>["array",Rule::requiredIf(function()use($req){return $req->has("tipo_cuota") && $req->input("tipo_cuota")==2;})],
        "membresias.*"=>"integer",
        "genero_aplica"=>["required",Rule::in(['H','M','A'])],
        "edad_aplica"=>"required|integer", 
        "acceso"=>["required","integer",Rule::in([0,1])], 
        "limite_pago"=>"required|integer", 
        "veces_aplica"=>"required|integer",
        "carga_automatica"=>["required","integer",Rule::in([0,1])], 
        // "ciclo"=>["integer","min:1",Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})], 
        // "tipo_ciclo"=>["integer",Rule::in([0,1,2,3]),Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})], 
        "ciclo"=>["integer","min:$req->input('carga_automatica')",Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})], 
        "tipo_ciclo"=>["integer",Rule::in([0,1,2,3]),Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})], 
        "fecha_vigor"=>["date",Rule::requiredIf(function()use($req){return $req->has("carga_automatica") && $req->input("carga_automatica")==1;})],
        "recargo_aplica"=>["required","integer",Rule::in([0,1])], 
        "recargo_unico"=>["integer",Rule::in([0,1]),Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})], 
        "recargo_cantidad"=>["numeric",Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})], 
        "recargo_cada"=>["integer",Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})],
        "editable"=>["required","integer",Rule::in([0,1])],
        "mes_siguiente"=>["integer",Rule::in([0,1]),Rule::requiredIf(function()use($req){return $req->has("recargo_aplica") && $req->input("recargo_aplica")==1;})]];
        $this->validate($req, $reglas);
        return CuotaDAO::updateCuota($id,(object)$req->all());
    }

    public function deleteCuota($id){
        return CuotaDAO::deleteCuota($id);
    }
    
}
