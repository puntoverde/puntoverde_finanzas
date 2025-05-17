<?php
namespace App\DAO;
use App\Entity\Contacto;
use Illuminate\Support\Facades\DB;
use App\Entity\EncuestaColoniaOtra;

class ContactoDAO
{
    public function __construct(){
    }

    /**
     * NOTE: INSERTAR CONTACTO
     * 
     */
    public static function setContacto($p){
        $datos = (object) $p;

        return  DB::transaction(function () use ($datos) {

        //NOTE: CREAR CONTACTO
            $contacto = new Contacto();
            $contacto->nombre =  strtoupper (trim($datos->contacto_nombre));
            $contacto->correo = $datos->correo_contacto;
            $contacto->telefono = str_replace(array( '(', ')','-' ), '',  $datos->telefono_contacto); 
            $contacto->celular = str_replace(array( '(', ')','-' ), '',  $datos->celular_contacto);
            $contacto->save();
        
            
            return  $contacto->id_contacto;
                
        });
        return false;
    }

    /**
     * NOTE: ACTUALIZAR CONTACTO
     * 
     */
    public static function updateContacto($id, $p){
        $datos = (object) $p;

        return  DB::transaction(function () use ($id, $datos) {

        //NOTE: CREAR CONTACTO
            $contacto = Contacto::find($id);
            $contacto->nombre =  strtoupper (trim($datos->contacto_nombre));
            $contacto->correo = $datos->correo_contacto;
            $contacto->telefono = str_replace(array( '(', ')','-' ), '',  $datos->telefono_contacto); 
            $contacto->celular = str_replace(array( '(', ')','-' ), '',  $datos->celular_contacto);
            $contacto->save();
        
            return  $contacto->id_contacto;
                
        });
        return false;
    }

    public static function getContacto($id_ficha){
        $query = DB::table("ad_ficha")
        ->join("ad_contacto","ad_ficha.id_contacto","ad_contacto.id_contacto")
        ->where ("ad_ficha.id_ficha", $id_ficha );

        $query->select("ad_contacto.id_contacto", "ad_contacto.nombre", "ad_contacto.correo", "ad_contacto.telefono", "ad_contacto.celular");
        
        $contacto= $query->first();
     
        return $contacto;
    }
}