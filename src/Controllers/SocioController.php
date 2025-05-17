<?php

namespace App\Controllers;

use App\DAO\SocioDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class SocioController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getSociosChange()
    {
        return SocioDAO::getSociosCambio();
    }

    public function getSociosByAccion(Request $req)
    {
        return SocioDAO::getSociosByAccion($req->input('cve_accion'));
    }

    public function getSocio($id)
    {
        return SocioDAO::getSocioById($id);
    }

    public function setSocio(Request $req)
    {
        $reglas = ["nombre" => "required", "fecha_nacimiento" => "required",  "posicion" => "required", "cve_accion" => "required", "cve_profesion" => "required", "cve_parentesco" => "required"];

        $this->validate($req, $reglas);
        return SocioDAO::insertSocio((object)$req->all());
    }


    public function updateSocio($id, Request $req)
    {
        $reglas = ["nombre" => "required", "fecha_nacimiento" => "required", "cve_profesion" => "required", "cve_parentesco" => "required", "cve_persona" => "required", "cve_direccion" => "required"];

        $this->validate($req, $reglas);
        return SocioDAO::updateSocio($id, (object)$req->all());
    }

    public function getPocisionesSocios(Request $req)
    {
        return SocioDAO::getPosicionesByAccion($req->input('cve_accion'));
    }

    public function getPosicionesByAccionAndClasificacion(Request $req)
    {
        return SocioDAO::getPosicionesByAccionAndClasificacion((object)$req->all());
    }

    public function bajaSocio($id)
    {
        try {
            SocioDAO::bajaSocio($id);
            return response(null, 204);
        } catch (\Exception $e) {
            return response(null, 500);
        }
    }

    public function updateSocioParams($id, Request $req)
    {
        return SocioDAO::updateParams($id, (object)$req->all());
    }

    public function getDocumentos($id)
    {
        return SocioDAO::getDocumentos($id);
    }
   
    public function uploadFile(Request $req)
    {
        if ($req->hasFile('documento')) {
            $file = $req->file('documento');
            $temp = explode(".", $file->getClientOriginalName());
            $directorio='../portafolio/';
            $filename = round(microtime(true)) . '.' . end($temp);
            if ($file->isValid()) {
             try{$file->move($directorio,$filename);
            return $filename;
            }
             catch(\Exception $e){return $e;}
            }
            else return 'no cargo bien ';
        }
        else {
            return 'no existe el Documento..';
        }
        
    }

    public function saveDocumento($id,Request $req){
        return SocioDAO::setDocumento($id,(object)$req->all());
    }

    public function deleteDocumento($id,Request $req){
        SocioDAO::deleteDocumento($id,$req->input('cve_documento'));
        return response(null, 204);
    }

    public function getDocumentoFile(Request $req)
    {    $file=$req->input('documento');
         return file_get_contents("../portafolio/$file");
    }

    public function uploadFoto(Request $req)
    {
        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $temp = explode(".", $file->getClientOriginalName());
            $directorio='../upload/';
            $filename = $req->input('cve_socio') . '.jpeg';
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
    
    
}