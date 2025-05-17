<?php
namespace App\DAO;
use App\Entity\TipoAccion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class PruebaTimeZoneDAO {
    
    public static function insertDates()
    {
        $date=date('Y-m-d h:i:s', time());        
        DB::table("prueba_timezone")->insert(["fecha_curdate"=>DB::raw("CURDATE()"),"fecha_now"=>DB::raw("NOW()"),"fecha_carbon"=>Carbon::now(),"fecha_nativa_php"=>$date,"back"=>"Finanzas"]);
        return 1;
    }
}
