<?php

namespace App\Controllers;
use App\DAO\AccionistaDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class AccionistaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getAccionistas(){
        return AccionistaDAO::getAccionistas();
    }

    public function getAccionista($id){
        return AccionistaDAO::findAccionista($id);
    }
    
    public function setAccionista(Request $req){
        $reglas = ["nombre"=>"required", "fecha_nacimiento"=>"required", "curp"=>"required", "rfc"=>"required"];
        
        $this->validate($req, $reglas);
        return AccionistaDAO::insertAccionista((object)$req->all());
    }
    
    
        public function updateAccionista($id,Request $req){
            $reglas = ["nombre"=>"required", "fecha_nacimiento"=>"required", "curp"=>"required", "rfc"=>"required"];
            
            $this->validate($req, $reglas);
            return AccionistaDAO::updateAccionista($id,(object)$req->all());
        }

        public function accionistaChange(Request $req){
            $reglas = ["cve_dueno"=>"required", "cve_accion"=>"required"];            
            $this->validate($req, $reglas);
            return AccionistaDAO::CambiarDueno((object)$req->all());
        }

        public function uploadFoto(Request $req)
    {
        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $temp = explode(".", $file->getClientOriginalName());
            $directorio='../upload/';
            $filename = $req->input('cve_dueno') . '.jpeg';
            if ($file->isValid()) {
             try{$file->move($directorio,$filename);
            return $filename;
            }
             catch(\Exception $e){return $e;}
            }
            else return 'ocurrio un error con la foto ';
        }
        else {
            return 'no existe el Documento..';
        }
        
    }

    public function getViewFoto(Request $req)
    {    $foto=$req->input('foto');
         $img=file_get_contents("../upload/$foto");
         return response($img)->header('Content-type','image/png');
    }

    public function addFoto($id,Request $req)
    {   $foto=$req->input('foto');
        return AccionistaDAO::addFoto($id,$foto);
    }
}
