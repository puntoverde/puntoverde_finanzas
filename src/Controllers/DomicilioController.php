<?php

namespace App\Controllers;

use App\DAO\DomicilioDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class DomicilioController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getDomicilioByCP(Request $req){
        return DomicilioDAO::getDomicilioByCP($req->input('cp'));
    }

    public function getNacionalidad(){
        return DomicilioDAO::getNacionalidad();
    }
}