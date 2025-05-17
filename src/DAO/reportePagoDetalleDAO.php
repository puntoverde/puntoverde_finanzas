<?php

namespace App\DAO;

use App\Entity\Accion;
use Illuminate\Support\Facades\DB;
use \PDO;



class reportePagoDetalleDAO
{
	private $sql;
	private $sta;
	
    function __construct(){}

 

    public static function consultarPagos($p)
    {
        try {
            $Conexion2 = DB::connection()->getPdo();
            $SQL = "select 
					concat(acciones.numero_accion,case acciones.clasificacion when 1 then 'A' when 2 then 'B' when 3 then 'C' else '' end) as accion,
					pago.folio,
					factura.folio_compaq,
					pago.total, pago.subtotal, pago.iva,pago.descuento,
					factura.metodo_pago,factura.uso_cfdi,factura.uuid,
					forma_pago_sat.forma_pago,
					datos_facturacion.rfc, datos_facturacion.correo,    
					concat_ws(' ',persona.nombre, persona.apellido_paterno, persona.apellido_materno) as cobra,
					pago.fecha_hora_cobro
				from pago
					inner join cargo on cargo.idpago = pago.idpago
					inner join factura on pago.idpago = factura.idpago    
					inner join forma_pago_sat on factura.forma_pago = forma_pago_sat.clave
					inner join datos_facturacion on datos_facturacion.id_datos_facturacion = factura.id_datos_facturacion
					inner join acciones on cargo.cve_accion = acciones.cve_accion
					inner join persona on pago.persona_cobra = persona.cve_persona
				WHERE 1 = 1";                        

                        if($p->numero_accion??false) {$SQL=$SQL. " AND numero_accion=".$p->numero_accion." AND clasificacion=".$p->clasificacion;}
                        if($p->cajero??false){$SQL=$SQL." AND pago.persona_cobra='".$p->cajero."'";}
                        if($p->fecha_inicio??false){$SQL=$SQL." AND CONVERT(fecha_hora_cobro,DATE) BETWEEN '".$p->fecha_inicio."' AND '".$p->fecha_fin."'";}
						
				$SQL=$SQL." GROUP BY pago.folio ORDER BY pago.folio;";
				
                        
            $sta = $Conexion2->prepare($SQL);
            $sta->execute();
            $datos = $sta->fetchAll(PDO::FETCH_ASSOC);
			
			
			return $datos;
			
			
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }

    

    public static function consultarCajero()
    {
        try {
            $Conexion2 = DB::connection()->getPdo();
            $SQL = "SELECT cve_persona, nombre,apellido_paterno,apellido_materno FROM pago  
			INNER JOIN persona ON(persona.cve_persona = pago.persona_cobra) GROUP BY persona.cve_persona ";             
                        
            $sta = $Conexion2->prepare($SQL);
            $sta->execute();
            $cajero = $sta->fetchAll(PDO::FETCH_ASSOC);

            return $cajero;
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }
	
	
	


}
?>