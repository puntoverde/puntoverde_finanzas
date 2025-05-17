<?php

namespace App\DAO;

use App\Entity\producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EdificioDAO
{

    public function __construct()
    {
    }
    /**
     * 
     */
    public static function getEdificios()
    {
        try {
            return DB::table("edificios")                
                ->select("edificios.cve_edificio", "edificios.nombre")
                ->where("cve_edificio",">",3000)
                ->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getEspacioFisicoByEdificio($id_edificio)
    {
        try{
            return  DB::table("espacio_fisico")->where("id_edificio",$id_edificio)->select("id_espacio_fisico","nombre")->get();
            
        }
        catch(\Exception $e){

        }
    }


    public static function getEspacioFisicoFull()
    {
        try{
            // return  DB::table("espacio_fisico")->where("id_edificio",$id_edificio)->select("id_espacio_fisico","nombre")->get();
            return  DB::table("espacio_fisico")->select("id_espacio_fisico","nombre")->get();
        }
        catch(\Exception $e){

        }
    }


}
