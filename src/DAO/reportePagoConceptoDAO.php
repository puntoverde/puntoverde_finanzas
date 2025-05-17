<?php
/*
 * Titulo			: comando_caja.php
 * Descripción		: Comandos de la tabla pagos
 * Compañía			: Universidad Tecnológica de León
 * Fecha de creación: 07-Marzo-2018
 * Desarrollador	: Daniel Rios Flores
 * Versión			: 1.0
 * ID Requerimiento	: 
 */
namespace App\DAO;

use Illuminate\Support\Facades\DB;
use \PDO;

class reportePagoConceptoDAO
{
	private $sql;
	private $sta;
	
    function __construct(){}

        /*
     * Guardar registro
     */
    public static function eliminarPago($p)
    {
        
        // $Conexion2 = Conexion2::getInstance()->obtenerConexion2();
        $Conexion2 = DB::connection()->getPdo();
            
        $Conexion2->beginTransaction();
        try {
            //insert en nueva tabla para tener una bitacora
            $sql = "INSERT INTO 
            pago_cancelado(folio,accion,fecha_pago,cajero,descuento,total)
            SELECT folio,?,fecha_hora_cobro,persona_cobra,descuento,total  
            FROM pago WHERE idpago=?";
            $sta = $Conexion2->prepare($sql);
            $sta->bindValue(1,$p->accion_text, PDO::PARAM_STR);
            $sta->bindValue(2,$p->idpago, PDO::PARAM_INT);
            $sta->execute();
            $pago_eliminado = $Conexion2->lastInsertId();

            //elimina el pago
            $sql = "DELETE FROM pago WHERE idpago=?";
            $sta = $Conexion2->prepare($sql);   
            $sta->bindValue(1,$p->idpago, PDO::PARAM_INT);
            $sta->execute();
            
            //eliminea  las formas de pago 
            $sql = "DELETE FROM forma_pago WHERE idpago=?";
            $sta = $Conexion2->prepare($sql);   
            $sta->bindValue(1,$p->idpago, PDO::PARAM_INT);
            $sta->execute();

            //cancelar cargo
            $sql = "INSERT INTO 
            cancelar_cargo(cve_cancelar_cargo,cve_accion,cve_cuota,cve_persona,concepto,total,subtotal,iva,cantidad,periodo,responsable_carga,responsable_cancelar,fecha_cargo,recargo,motivo_cancelacion,idpago_cancelado)
            SELECT cve_cargo,cve_accion,cve_cuota,cve_persona,concepto,total,subtotal,iva,cantidad,periodo,responsable_carga,0,fecha_cargo,recargo,'Se cancela Pago',?
            FROM cargo WHERE idpago=?";
            $sta = $Conexion2->prepare($sql);
            $sta->bindValue(1,$pago_eliminado,PDO::PARAM_INT);
            $sta->bindValue(2,$p->idpago, PDO::PARAM_INT);            
            $sta->execute();
            
            //inserta las formas de pago de el pago ejemplo efectivo y tarjeta credito 
            $sql = "DELETE FROM cargo WHERE idpago=?";
            $sta = $Conexion2->prepare($sql);   
            $sta->bindValue(1,$p->idpago, PDO::PARAM_INT);
            $sta->execute();

            $Conexion2->commit();
            return 1;
        }catch (PDOException $e){
            echo $e;
            $Conexion2->rollBack ();            
            return 0;
        }
        finally{$Conexion2=null;}
    }


    public static function consultarPagos($p)
    {
        try {

            $query=DB::table("pago")
            ->join("cargo","pago.idpago","cargo.idpago")
            ->leftJoin("descuento","cargo.cve_cargo","descuento.cve_cargo")
            ->join("cuota","cargo.cve_cuota","cuota.cve_cuota")
            ->join("acciones","cargo.cve_accion","acciones.cve_accion")
            ->join("persona AS cajero" ,"cajero.cve_persona","pago.persona_cobra")
            ->join("persona AS socio" ,"socio.cve_persona","cargo.cve_persona")
            ->select("cuota.numero_cuota",
            "cargo.concepto",
            "cargo.periodo",
            "cargo.subtotal",
            "cargo.iva",
            "cargo.cantidad",
            "cargo.recargo",
            "descuento.monto",
            "folio",
            "fecha_hora_cobro",
            "pago.descuento",
            "cargo.cve_cargo")
            ->selectRaw("CONCAT(numero_accion,CASE clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion")
            ->selectRaw("CONCAT_WS(' ',socio.nombre,socio.apellido_paterno,socio.apellido_materno) AS sociox")
            ->selectRaw("(cargo.total*cargo.cantidad) AS total")
            ->selectRaw("CONCAT_WS(' ',cajero.nombre,cajero.apellido_paterno,cajero.apellido_materno) AS cajerox");


            if($p->numero_accion??false) {$query->where("numero_accion",$p->numero_accion)->where("clasificacion",$p->clasificacion);}
            if($p->cve_cuota??false){$query->where("cargo.cve_cuota",$p->cve_cuota);}
            if($p->periodo??false){$query->where("periodo",$p->periodo);}
			if($p->cajero??false){$query->where("pago.persona_cobra",$p->cajero);}
            if($p->fecha_inicio??false){$query->whereRaw("fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin]);}


  
            $query2=DB::table("pago")
            ->join("forma_pago","pago.idpago","forma_pago.idpago")
            ->whereRaw("pago.fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio??'2000-01-01',$p->fecha_fin??'2000-01-01'])
            ->groupBy("forma_pago.clave")
            ->select("forma_pago.clave",DB::raw("SUM(forma_pago.monto) AS monto"));
            if($p->cve_persona??false){$query2->where("pago.persona_cobra",$p->cve_persona);}

			
            return ["conceptos"=>$query->get(),"forma_pago"=>$query2->get()];
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }

    public static function consultarConceptosCargados()
    {
        try {
            $datos=DB::table("pago")
            ->join("cargo","pago.idpago","cargo.idpago")
            ->join("cuota","cargo.cve_cuota","cuota.cve_cuota")
            ->groupBy("cuota.cve_cuota")
            ->orderBy("cuota.cve_cuota")
            ->orderBy("cuota.cuota")
            ->select("cuota.cve_cuota","cuota.descripcion")
            ->get();
            return $datos;
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }

    public static function consultarCargosPagos($p)
    {
        try {        
            // $Conexion2 = DB::connection()->getPdo();

            $datos=DB::table("pago")
            ->join("cargo","pago.idpago","cargo.idpago")
            ->leftJoin("descuento","cargo.cve_cargo","descuento.cve_cargo")
            ->join("cuota","cargo.cve_cuota","cuota.cve_cuota")
            ->join("acciones","cargo.cve_accion","acciones.cve_accion")
            ->join("persona AS cajero" ,"cajero.cve_persona","pago.persona_cobra")
            ->join("persona AS socio" ,"socio.cve_persona","cargo.cve_persona")
            ->select("cuota.numero_cuota",
            "cargo.concepto",
            "cargo.total AS monto_cargo",
            "cargo.periodo",
            "pago.subtotal",
            "pago.iva",
            "cargo.cantidad",
            "cargo.recargo",
            "descuento.monto",
            "folio",
            "fecha_hora_cobro",
            "pago.total",
            "pago.descuento",
            "cargo.cve_cargo")
            ->selectRaw("CONCAT(numero_accion,CASE clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion")
            ->selectRaw("CONCAT_WS(' ',socio.nombre,socio.apellido_paterno,socio.apellido_materno) AS sociox")
            ->selectRaw("CONCAT_WS(' ',cajero.nombre,cajero.apellido_paterno,cajero.apellido_materno) AS cajerox");

            if($p->numero_accion??false) {$datos->where("numero_accion",$p->numero_accion)->where("clasificacion",$p->clasificacion);}
            if($p->cve_cuota??false){$datos->where("cargo.cve_cuota",$p->cve_cuota);}
            if($p->periodo??false){$datos->where("periodo",$p->periodo);}
			// if($p->cajero??false){$datos->where("pago.persona_cobra",$p->cajero);}
            if($p->fecha_inicio??false){
                $datos->whereRaw("fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin])
                ->orWhere(function($where)use($p){
                    $where->whereNull("fecha_hora_cobro")
                    ->whereRaw("fecha_cargo BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin]);
                });
            }

            return $datos->get();
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }


    public static function consultarPagosConceptosCajera($p)
    {
        try {
            $concepto=DB::table("pago")
            ->join("cargo","pago.idpago","cargo.idpago")
            ->leftJoin("descuento","cargo.cve_cargo","descuento.cve_cargo")
            ->join("cuota","cargo.cve_cuota","cuota.cve_cuota")
            ->groupBy("cuota.cve_cuota")
            ->whereRaw("pago.fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin])
            ->select("cuota.numero_cuota AS cve_cuota","cuota.descripcion")
            ->selectRaw("COUNT(cuota.cve_cuota) AS cantidad")
            ->selectRaw("ROUND(((SUM(cargo.total*cargo.cantidad)-SUM(IFNULL(descuento.monto,0)))/116)*100,2)  AS subtotal")
            ->selectRaw("ROUND((((SUM(cargo.total*cargo.cantidad)-SUM(IFNULL(descuento.monto,0)))/116)*100)*.16,2)  AS iva")
            ->selectRaw("SUM(cargo.total*cargo.cantidad) AS monto")
            ->selectRaw("SUM(IFNULL(descuento.monto,0)) AS descuento")
            ->selectRaw("SUM(cargo.total*cargo.cantidad)-SUM(IFNULL(descuento.monto,0)) AS total");
            if($p->cve_persona??false)$concepto->where("pago.persona_cobra",$p->cve_persona);


            $forma_pago=DB::table("pago")
            ->join("forma_pago","pago.idpago","forma_pago.idpago")
            ->whereRaw("pago.fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin])
            ->groupBy("forma_pago.clave")
            ->select("forma_pago.clave",DB::raw("SUM(forma_pago.monto) AS monto"));
            if($p->cve_persona??false){$forma_pago->where("pago.persona_cobra",$p->cve_persona);}


            return ["conceptos"=>$concepto->get(),"forma_pago"=>$forma_pago->get()];
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }

	
	public static function consultarDescuentos($p)
    {
        try {
            
           $descuentos= DB::table("pago")
            ->join("cargo","pago.idpago","cargo.idpago")
            ->join("descuento","cargo.cve_cargo","descuento.cve_cargo")
            ->join("persona AS cajero" ,"cajero.cve_persona","pago.persona_cobra")
            ->join("persona AS socio" ,"socio.cve_persona","cargo.cve_persona")
            ->join("persona AS autoriza" ,"autoriza.cve_persona","descuento.persona_otorga")
            ->whereRaw("pago.fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin])
            ->select("cargo.periodo","cargo.concepto","cargo.cantidad","cargo.total AS cargo","descuento.monto","descuento.descripcion","pago.folio","pago.fecha_hora_cobro")
            ->selectRaw("CONCAT_WS(socio.nombre,socio.apellido_materno,socio.apellido_materno) AS usuario")
            ->selectRaw("CONCAT_WS(autoriza.nombre,autoriza.apellido_paterno,autoriza.apellido_materno) AS autorizo")
            ->selectRaw("((cargo.total*cargo.cantidad)-descuento.monto) total")
            ->selectRaw("CONCAT_WS(cajero.nombre,cajero.apellido_paterno,cajero.apellido_materno) AS cajero")
            ->get();

            return ["conceptos"=>$descuentos];
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }

    public static function consultarCajero()
    {
        try {
            $cajero=DB::table("pago")
            ->join("persona" ,"persona.cve_persona","pago.persona_cobra")
            ->groupBy("persona.cve_persona")
            ->select("cve_persona","nombre","apellido_paterno","apellido_materno")
            ->get();
            return $cajero;
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }
	
	
	public static function GenerarExcelExportarFacturacion($p)
    {
        try {
            // $Conexion2 = Conexion2::getInstance()->obtenerConexion2();
            $Conexion2 = DB::connection()->getPdo();
            $SQL = "SELECT 
                        pago.folio,
                        cargo.total,
                        cargo.periodo,
                        pago.fecha_hora_cobro,
                        CONCAT(acciones.numero_accion,CASE acciones.clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS acciones,
                        GROUP_CONCAT(forma_pago.clave ORDER BY forma_pago.monto DESC) AS forma_pago,
                        facturav2.uso_cfdi,
                        cargo.cantidad,
                        cargo.cve_cuota,
                        cargo.concepto,
                        IFNULL(descuento.monto,0) AS descuento,
                        facturav2.razon_social,
                        facturav2.rfc,
                        facturav2.calle,
                        facturav2.num_ext,
                        facturav2.num_int,
                        facturav2.colonia,
                        facturav2.cp,
                        facturav2.municipio,
                        facturav2.estado,
                        facturav2.pais
                    FROM facturav2
                    INNER JOIN cargo USING(idpago)
                    INNER JOIN pago USING(idpago)
                    LEFT JOIN descuento USING(cve_cargo)
                    INNER JOIN acciones USING(cve_accion)
                    INNER JOIN forma_pago using(idpago) WHERE 1=1 ";

		if($p->fecha_inicio!=''){$SQL.="AND fecha_hora_cobro BETWEEN '".$p->fecha_inicio."' AND '".$p->fecha_fin."'";}
		
		$SQL.=" GROUP BY cargo.cve_cargo";             
                        
            $sta = $Conexion2->prepare($SQL);
            $sta->execute();
            $data = $sta->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }


    public static function consultarPagosDeporte($p)
    {
        try {

            $query=DB::table("pago")
            ->join("cargo","pago.idpago","cargo.idpago")
            ->leftJoin("descuento","cargo.cve_cargo","descuento.cve_cargo")
            ->join("cuota","cargo.cve_cuota","cuota.cve_cuota")
            ->join("acciones","cargo.cve_accion","acciones.cve_accion")
            ->join("persona AS cajero" ,"cajero.cve_persona","pago.persona_cobra")
            ->join("persona AS socio" ,"socio.cve_persona","cargo.cve_persona")
            ->whereIn("cargo.cve_cuota",[300,83,151,312])
            ->select("cuota.numero_cuota",
            "cargo.concepto",
            "cargo.periodo",
            "cargo.subtotal",
            "cargo.iva",
            "cargo.cantidad",
            "cargo.recargo",
            "descuento.monto",
            "folio",
            "fecha_hora_cobro",
            "pago.descuento",
            "cargo.cve_cargo")
            ->selectRaw("CONCAT(numero_accion,CASE clasificacion WHEN 1 THEN 'A' WHEN 2 THEN 'B' WHEN 3 THEN 'C' ELSE '' END) AS accion")
            ->selectRaw("CONCAT_WS(' ',socio.nombre,socio.apellido_paterno,socio.apellido_materno) AS sociox")
            ->selectRaw("(cargo.total*cargo.cantidad) AS total")
            ->selectRaw("CONCAT_WS(' ',cajero.nombre,cajero.apellido_paterno,cajero.apellido_materno) AS cajerox");


            if($p->numero_accion??false) {$query->where("numero_accion",$p->numero_accion)->where("clasificacion",$p->clasificacion);}
            if($p->cve_cuota??false){$query->where("cargo.cve_cuota",$p->cve_cuota);}           
            if($p->fecha_inicio??false){$query->whereRaw("fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin]);}


  
            $query2=DB::table("pago")
            ->join("forma_pago","pago.idpago","forma_pago.idpago")
            ->whereRaw("pago.fecha_hora_cobro BETWEEN ? AND ?",[$p->fecha_inicio??'2000-01-01',$p->fecha_fin??'2000-01-01'])
            ->groupBy("forma_pago.clave")
            ->select("forma_pago.clave",DB::raw("SUM(forma_pago.monto) AS monto"));
            if($p->cve_persona??false){$query2->where("pago.persona_cobra",$p->cve_persona);}

			
            return ["conceptos"=>$query->get(),"forma_pago"=>$query2->get()];
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }


    public static function consultarConceptosCargadosDeportes()
    {
        try {
            $datos=DB::table("pago")
            ->join("cargo","pago.idpago","cargo.idpago")
            ->join("cuota","cargo.cve_cuota","cuota.cve_cuota")
            ->whereIn("cargo.cve_cuota",[300,83,151,312])
            ->groupBy("cuota.cve_cuota")
            ->orderBy("cuota.cuota")
            ->select("cuota.cve_cuota","cuota.descripcion")
            ->get();
            return $datos;
        }catch (Exception $e){
            return [];
        }
        finally{$Conexion2=null;}
    }

}
?>