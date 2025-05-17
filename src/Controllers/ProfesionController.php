<?php

namespace App\Controllers;
use App\DAO\ProfesionDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ProfesionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getProfesiones(Request $req){
        return ProfesionDAO::getProfesiones();
    }

   
    
}
