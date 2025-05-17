<?php

namespace App\Controllers;
use App\DAO\ParentescoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ParentescoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){}

    public function getParentescos(Request $req){
        return ParentescoDAO::getParentescos();
    }

   
    
}
