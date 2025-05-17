<?php
/*
 * Titulo			: controlador_caja.php
 * Descripción		: Controlador PHP del procesos Caja
 * Compañía			: Universidad Tecnológica de León
 * Fecha de creación: 07-Marzo-2018
 * Desarrollador	: Daniel Rios Flores
 * Versión			: 1.0
 * ID Requerimiento	: 
 */

namespace App\Controllers;
use App\DAO\reportePagoConceptoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class reportePagoConceptoController extends Controller
{

       /*
              $accion = $_REQUEST['accion'] ?? 0;

       //datos de cargo
       $objeto = new Entidad();
              $objeto->numero_accion = $_REQUEST['numero_accion']?? '';
              $objeto->clasificacion = $_REQUEST['clasificacion']?? '';
              $objeto->cve_cuota = $_REQUEST['cve_cuota']?? 0;
              $objeto->fecha_inicio=$_REQUEST['fecha_inicio']??'';
              $objeto->fecha_fin=$_REQUEST['fecha_fin']??'';
              $objeto->periodo = $_REQUEST['periodo']?? '';
              $objeto->cajero = $_REQUEST['cajero']?? '';

              $objeto->cve_persona = $_REQUEST['cve_persona']?? '';

              if($objeto->numero_accion=='')$objeto->clasificacion='';
       */

    public function __construct(){}

    public function eliminarPago(Request $req){		
		return reportePagoConceptoDAO::eliminarPago((object) $req->all());
		
    }
	
	public function consultarPagos(Request $req){		
		return reportePagoConceptoDAO::consultarPagos((object) $req->all());
		
    }

    public function consultarConceptosCargados(){		
       return reportePagoConceptoDAO::consultarConceptosCargados();
       
}

public function consultarCargosPagos(Request $req){		
       return reportePagoConceptoDAO::consultarCargosPagos((object) $req->all());
       
}

public function consultarCajero(){		
       return reportePagoConceptoDAO::consultarCajero();
       
}

public function consultarPagosConceptosCajera(Request $req){		
       return reportePagoConceptoDAO::consultarPagosConceptosCajera((object) $req->all());
       
}

public function consultarDescuentos(Request $req){		
       return reportePagoConceptoDAO::consultarDescuentos((object) $req->all());
       
}
public function GenerarExcelExportarFacturacion(Request $req){		
       return reportePagoConceptoDAO::GenerarExcelExportarFacturacion((object) $req->all());
       
}


public function consultarPagosDeporte(Request $req){		
       return reportePagoConceptoDAO::consultarPagosDeporte((object) $req->all());
       
}

public function consultarConceptosCargadosDeportes(){		
       return reportePagoConceptoDAO::consultarConceptosCargadosDeportes();
       
}
	

}


// $reportePagoConceptoDAO = new reportePagoConceptoDAO();







// try {
//     switch ($accion) {

//         case 1:
//                echo json_encode($reportePagoConceptoDAO->eliminarPago($objeto));
//                exit();
//                break;
       //  case 2:
       //         echo json_encode($reportePagoConceptoDAO->consultarPagos($objeto));
       //         exit();
       //         break; 
       //  case 3:
       //         echo json_encode($reportePagoConceptoDAO->consultarConceptosCargados());
       //         exit();
       //         break;
       //  case 4:
       //         echo json_encode($reportePagoConceptoDAO->consultarCargosPagos($objeto));
       //         exit();
       //         break; 
       //  case 5:
       //         echo json_encode($reportePagoConceptoDAO->consultarCajero());
       //         exit();
       //         break;                          
       //  case 6:
              //  echo json_encode($reportePagoConceptoDAO->consultarPagosConceptosCajera($objeto));
              //  exit();
              //  break;
	//     case 7:
       //         echo json_encode($reportePagoConceptoDAO->consultarDescuentos($objeto));
       //         exit();
       //         break;
// 	    case 8://gerenera el reporte de facturacion......
//                echo json_encode($reportePagoConceptoDAO->GenerarExcelExportarFacturacion($objeto));
//                exit();
//                break;
//         default : 
//         	echo 0;
//             break;
//     }
// } catch (Exception $exc) {
//     $response["status"] = "Exception";
//     $response["message_ex"] = $exc->getMessage();
//    	$response["trace"] = $exc->getTraceAsString();
// }
