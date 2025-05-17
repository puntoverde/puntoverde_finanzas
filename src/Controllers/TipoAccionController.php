<?php

namespace App\Controllers;

use App\DAO\TipoAccionDAO;
use Laravel\Lumen\Routing\Controller;

class TipoAccionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getTipoAccion(){
        return TipoAccionDAO::getTipoAccion();
    }
}