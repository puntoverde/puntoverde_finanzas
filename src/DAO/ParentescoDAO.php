<?php
namespace App\DAO;
use App\Entity\Parentesco;
use Illuminate\Support\Facades\DB;


class ParentescoDAO {

    public function __construct(){}

    public static function getParentescos(){
         return Parentesco::all();
   }

}