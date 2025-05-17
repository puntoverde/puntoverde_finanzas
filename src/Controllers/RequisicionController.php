<?php

namespace App\Controllers;

use App\DAO\RequisicionDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class RequisicionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getRequisiciones(Request $req){
        $reglas = ["id_persona" => "required"];
        $this->validate($req, $reglas);
        return RequisicionDAO::getRequisiciones($req->input("id_persona",0),(object)$req->all());
    }

    public function getProductos()
    {
        return RequisicionDAO::getProductos();
    }

    public function findProducto($id)
    {
        return RequisicionDAO::findProducto($id);
    }

    public function crearRequisicion(Request $req)
    {
        $reglas = [
            "solicita" => "required", 
            "revisa" => "required",            
            "autoriza" => "required", 
            "productos" => "required",            
        ];        

        $this->validate($req, $reglas);
        return RequisicionDAO::crearRequisicion((object)$req->all());
    }

    public function getDepartamentoAndColaboradores(Request $req){
        $reglas = ["id_persona" => "required"];
        $this->validate($req, $reglas);
        $data=RequisicionDAO::getDepartamentoAndColaboradores($req->input("id_persona"));
      if($data){
        return $data; 
      }
      else {return ["departamento"=>["id_departamento"=>0,"nombre"=>"NA","encargado"=>null],"colaboradores"=>[]];}
    }  

    public function getDetalleRequisicion($id)
    {
        return RequisicionDAO::getDetalleRequisicion($id);
    }
    public function getRevisionRequisicion($id)
    {
        return RequisicionDAO::getRevisionRequisicion($id);
    }

    public function rechazarProductoRequisicionRevision($id)
    {
        return RequisicionDAO::rechazarProductoRequisicionRevision($id);
    }

    public function rechazarProductoRequisicionAprobacion($id){
  return RequisicionDAO::rechazarProductoRequisicionAprobacion($id);
    }

    public function terminarRevision($id)
    {
        return RequisicionDAO::terminarRevision($id);
    }

    public function terminarAprovacion($id)
    {
        return RequisicionDAO::terminarAprobacion($id);
    }

    public function getAprobarRequisicion($id)
    {
        return RequisicionDAO::getAprobarRequisicion($id);
    }

    public function cancelarRequisicion($id){
         return RequisicionDAO::cancelarRequisicion($id);
    }

    public function getPersonaSolicitaRevisaAprueba($id)
    {
        return RequisicionDAO::getPersonaSolicitaRevisaAprueba($id);
    }

    public function agregarEvidenciaProductoServicio(Request $req)
    {
        $name=time();
        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $temp = explode(".", $file->getClientOriginalName());
            $directorio = '../upload/';
            $filename = $name. '.jpeg';
            if ($file->isValid()) {
                try {
                    $file->move($directorio, $filename);
                    return $filename;
                } catch (\Exception $e) {
                    return $e;
                }
            } else return 'ocurrio un error con la foto ';
        } else {
            return 'no existe el Documento..';
        }
    }


    public function getPresentacionesProducto($id)
    {
        return RequisicionDAO::getPresentacionesProducto($id);
    }
    
    public function getMarcaAsignar($id)
    {
        return RequisicionDAO::getMarcaAsignar($id);
    }

    public function getAllRequisicionByColaborador(Request $req)
    {
        $cve_persona=$req->input("cve_persona");
        return RequisicionDAO::getAllRequisicionByColaborador($cve_persona);
    }

    public function getDetalleRequisicionExistente($id)
    {
        return RequisicionDAO::getDetalleRequisicionExistente($id);
    }

    public function createRequisicionLigada($id)
    {        
        return RequisicionDAO::createRequisicionLigada($id);
    }
    
}
