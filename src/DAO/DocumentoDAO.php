<?php

namespace App\DAO;

use App\Entity\Documento;
use Illuminate\Support\Facades\DB;


class DocumentoDAO
{

    public function __construct()
    {
    }

    public static function getDocumentos()
    {
        return Documento::all();
    }

    public static function getDocumento($id)
    {
        return Documento::where('cve_documento',$id)
        ->select('documento',DB::raw('CAST(tipo AS UNSIGNED) AS tipo'))
        ->first();
    }

    public static function setDocumento($p)
    {
        $documento = new Documento();
        $documento->documento = $p->documento;
        $documento->tipo = $p->tipo;
        $documento->estatus = 1;
        $documento->save();
    }

    public static function updateDocumento($id, $p)
    {
        $documento = Documento::find($id);
        $documento->documento = $p->documento;
        $documento->tipo = $p->tipo;
        //$documento->estatus = $p->estatus;
        $documento->save();
    }

    public static function deleteDocumentos($id)
    {
        $documento = Documento::find($id);
        $documento->estatus = 0;
        $documento->save();
    }
}
