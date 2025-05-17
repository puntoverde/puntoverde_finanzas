<?php

namespace App\Controllers;

use App\DAO\OrdenCompraDAO;
use App\DAO\OrdenTrabajoDAO;
use App\Entity\OrdenTrabajo;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class OrdenTrabajoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getAllOrdenesTrabajo(Request $req)
    {
        return OrdenTrabajoDAO::getAllOrdenesTrabajo((object)$req->all());
    }

    public function getOrdenTrabajoById($id)
    {
        return OrdenTrabajoDAO::getOrdenTrabajoById($id);
    }


    public function createOrdenTrabajo(Request $req)
    {
        $reglas = [
            "id_departamento" => "required",
            "departamento_dirigido" => "required",
            "id_persona" => "required",
            "nombre_evento" => "required",
            "descripcion" => "required",
            // "fecha_inicio_evento" => "required|date",
            // "fecha_fin_evento" => "required|date",
            "id_tipo_orden_trabajo" => "required"
        ];

        $this->validate($req, $reglas);
        return OrdenTrabajoDAO::createOrdenTrabajo((object)$req->all());
    }

    public function updateOrdenTrabajo(Request $req, $id)
    {
        $reglas = [
            "departamento_dirigido" => "required",
            "nombre_evento" => "required",
            "descripcion" => "required",
            // "fecha_inicio_evento" => "required|date",
            // "fecha_fin_evento" => "required|date",
            "id_tipo_orden_trabajo" => "required"
        ];

        $this->validate($req, $reglas);
        return OrdenTrabajoDAO::updateOrdenTrabajo($id, (object)$req->all());
    }

    public function getDepartamentoColaborador($id)
    {
        return response()->json(OrdenTrabajoDAO::getDepartamentoColaborador($id));
    }

    public function getDepartamentosDisponibles()
    {
        return OrdenTrabajoDAO::getDepartamentosDisponibles();
    }

    public function updateCancelarRechazar(Request $req, $id)
    {
        $this->validate($req, ["estatus" => "required"]);
        return OrdenTrabajoDAO::updateCancelarRechazar($id, $req->input("estatus"));
    }
    public function iniciarOrdenTrabajo(Request $req, $id)
    {
        $this->validate($req, ["id_colaborador" => "required"]);
        return OrdenTrabajoDAO::iniciarOrdenTrabajo($id, $req->input("id_colaborador"));
    }


    public function getActividadOrdenTrabajo($id)
    {
        return OrdenTrabajoDAO::getActividadOrdenTrabajo($id);
    }


    public function getActividadOrdenByIdTrabajo($id)
    {
        return response()->json(OrdenTrabajoDAO::getActividadOrdenByIdTrabajo($id));
    }
    public function createActividadOrdenTrabajo(Request $req, $id)
    {
        $reglas = [
            // "id_orden_trabajo" => "required", 
            "responsable" => "required",
            "actividad" => "required",
            "tipo_actividad" => "required",
            "fecha_planeada" => "required"
        ];

        $this->validate($req, $reglas);
        return OrdenTrabajoDAO::createActividadOrdenTrabajo($id, (object)$req->all());
    }
    public function deleteActividadOrdenTrabajo($id)
    {
        return OrdenTrabajoDAO::deleteActividadOrdenTrabajo($id);
    }

    public function terminarActividadOrdenTrabajo($id)
    {
        return OrdenTrabajoDAO::terminarActividadOrdenTrabajo($id);
    }


    public function reporteOrdenTrabajoDepartamentos()
    {
        return OrdenTrabajoDAO::reporteOrdenTrabajoDepartamentos();
    }

    public function reporteOrdenTrabajo($id = 'all', Request $req)
    {
        return OrdenTrabajoDAO::reporteOrdenTrabajo($id, $req->input("folio"), $req->input("cve_socio"));
    }

    public function OrdenTrabajoActividades($id)
    {
        return OrdenTrabajoDAO::OrdenTrabajoActividades($id);
    }

    public function getTipoOrdenTrabajo()
    {
        return OrdenTrabajoDAO::getTipoOrdenTrabajo();
    }

    public function getTipoOrdenTrabajoActividad()
    {
        return OrdenTrabajoDAO::getTipoOrdenTrabajoActividad();
    }


    public function getActividadesByDepartamento(Request $req)
    {
        return OrdenTrabajoDAO::getActividadesByDepartamento($req->input('cve_persona'), $req->input('fecha'), $req->input('responsable'));
    }
    public function getFechasActividadesPendientes(Request $req)
    {
        return OrdenTrabajoDAO::getFechasActividadesPendientes($req->input('cve_persona'), $req->input('responsable'));
    }
    public function terminarActividadByDepartamento(Request $req)
    {
        return OrdenTrabajoDAO::terminarActividadByDepartamento($req->input('id_actividad'), $req->input('fecha'), $req->input('observacion'));
    }

    public function geetActividadesReporte(Request $req)
    {
        $fecha_inicio = $req->input('fecha_inicio');
        $fecha_fin = $req->input('fecha_fin');
        $responsable = $req->input('responsable');
        $tipo = $req->input('tipo');
        $departamento = $req->input('departamento');
        return OrdenTrabajoDAO::geetActividadesReporte($fecha_inicio, $fecha_fin, $responsable, $tipo, $departamento);
    }

    public function  createObservacionActividad(Request $req)
    {

        return OrdenTrabajoDAO::createObservacionActividad($req);
    }

    public function getReporteOrdenesTrabajoSocios(Request $req)
    {
        $folio = $req->input("folio");
        $estatus = $req->input("estatus");
        return OrdenTrabajoDAO::getReporteOrdenesTrabajoSocios($folio, $estatus);
    }

    public function getReporteOrdenesTrabajoInterno(Request $req)
    {
        $folio = $req->input("folio");
        $estatus = $req->input("estatus");
        return OrdenTrabajoDAO::getReporteOrdenesTrabajoInterno($folio, $estatus);
    }

    public function getClasificacionOrdenTrabajo(Request $req)
    {
        $id_departamento = $req->input("id_departamento");
        return OrdenTrabajoDAO::getClasificacionOrdenTrabajo($id_departamento);
    }


    public function guardarCancelarFoto(Request $req)
    {

        if ($req->hasFile('foto')) {
            $file = $req->file('foto');
            $temp = explode(".", $file->getClientOriginalName());
            $directorio = '../upload/';
            $filename = round(microtime(true)) . '.' . end($temp);
            if ($file->isValid()) {
                try {
                    $file->move($directorio, $filename);
                    return OrdenTrabajoDAO::guardarCancelarFoto($req->input("id_orden_trabajo"), $filename, $req->input('id_foto'));
                } catch (\Exception $e) {
                    return $e;
                }
            } else return 'no cargo bien ';
        } else {
            return 'no existe el Documento..';
        }
    }



    public function CancelarFoto(Request $req)
    {

        try {

            return OrdenTrabajoDAO::guardarCancelarFoto($req->input("id_orden_trabajo"), null, $req->input('id_foto'));
        } catch (\Exception $e) {
            return $e;
        }
    }



    public function getViewFotoOrdenTrabajo(Request $req)
    {
        $foto = $req->input('foto');
        $img = file_get_contents("../upload/$foto");
        return response($img)->header('Content-type', 'image/png');
    }
}
