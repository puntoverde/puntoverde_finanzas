<?php

namespace App\Controllers;
use App\DAO\EdificioDAO;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Validation\Rule;

class EdificioController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getEdificios(){        
        return EdificioDAO::getEdificios();
    }
    
    public function getEspacioFisicoByEdificio($id){
        return EdificioDAO::getEspacioFisicoByEdificio($id);
    }
   
    public function getEspacioFisicoFull(){
        return EdificioDAO::getEspacioFisicoFull();
    }

    

    
}
