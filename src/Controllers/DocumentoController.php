<?php

namespace App\Controllers;
use App\DAO\DocumentoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class DocumentoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getDocumentos(Request $req){
        return DocumentoDAO::getDocumentos((object)$req->all());
    }

    public function getDocumento($id){
        return DocumentoDAO::getDocumento($id);
    }

    public function setDocumento(Request $req){
        return DocumentoDAO::setDocumento((object)$req->all());
    }

    public function updateDocumento($id,Request $req){
        return DocumentoDAO::updateDocumento($id,(object)$req->all());
    }

    public function deleteDocumento($id){
        return DocumentoDAO::deleteDocumentos($id);
    }
    
}
