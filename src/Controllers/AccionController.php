<?php

namespace App\Controllers;
use App\DAO\AccionDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class AccionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getAcciones(Request $req){
        return AccionDAO::getAcciones((object)$req->all());
    }

    public function getAccionById($id){
        return AccionDAO::getAccionById($id);
    }

    public function updateAccion($id,Request $req){
        return AccionDAO::updateAccion($id,(object)$req->all());
    }
    
}
