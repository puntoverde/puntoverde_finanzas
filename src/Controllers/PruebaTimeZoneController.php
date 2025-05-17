<?php

namespace App\Controllers;

use App\DAO\PruebaTimeZoneDAO;
use Laravel\Lumen\Routing\Controller;

class PruebaTimeZoneController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function insertDates(){
        return PruebaTimeZoneDAO::insertDates();
    }
}