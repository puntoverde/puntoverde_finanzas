<?php
namespace App\DAO;
use App\Entity\Profesion;
use Illuminate\Support\Facades\DB;


class ProfesionDAO {

    public function __construct(){}

    public static function getProfesiones(){
         return Profesion::all();
   }

}