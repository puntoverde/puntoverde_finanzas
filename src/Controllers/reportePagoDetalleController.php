<?php

namespace App\Controllers;
use App\DAO\reportePagoDetalleDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class reportePagoDetalleController extends Controller
{

    public function __construct(){}

    public function consultarPagos(Request $req){		
		return reportePagoDetalleDAO::consultarPagos((object) $req->all());
		
    }
	
	public function consultarCajera(){		
		return reportePagoDetalleDAO::consultarCajero();
		
    }
	

}

