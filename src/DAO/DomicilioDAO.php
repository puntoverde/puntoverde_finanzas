<?php
namespace App\DAO;
use Illuminate\Support\Facades\DB;


class DomicilioDAO {
    
    public static function getDomicilioByCP($cp)
    {

        /*SELECT colonia.cve_colonia,colonia.nombre,colonia.cp,municipio.nombre AS municipio,estado.nombre AS estado 
        FROM colonia
INNER JOIN municipio ON colonia.cve_municipio=municipio.cve_municipio
INNER JOIN estado ON municipio.cve_estado=estado.cve_estado
WHERE cp=37549*/
        return DB::table('colonia')
        ->join('municipio','colonia.cve_municipio','municipio.cve_municipio')
        ->join('estado','municipio.cve_estado','estado.cve_estado')
        ->where('colonia.cp',$cp)
        ->orderBy('colonia.nombre')
        ->select('colonia.cve_colonia','colonia.nombre','colonia.cp','municipio.nombre AS municipio','estado.nombre AS estado')
        ->get();
    }


    public static function getNacionalidad()
    {
        return DB::table('pais')
        ->where('estatus',1)
        ->orderBy('nombre')
        ->select('cve_pais','nombre')
        ->get();
    }


}
