<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;


class ReporteProductoDAO
{

    public function __construct()
    {
    }


    //obtener los datos
    public static function reporteProductoRequisicion($p)
    {
        /* 
                SELECT 
                    producto_pv.clave,
                    producto_pv.nombre,
                    COUNT(requisicion_producto_pv.id_requisicion_producto_pv) AS cantidad_requisiciones,
                    SUM(requisicion_producto_pv.cantidad) AS cantidad_comprada,
                    MAX(requisicion_pv.fecha_solicitud) AS fecha_ultima_compra,
                    (SELECT req_sec.cantidad FROM requisicion_producto_pv AS req_sec WHERE req_sec.id_requisicion_producto_pv=max(requisicion_producto_pv.id_requisicion_producto_pv) LIMIT 1) AS ultima_cantidad_compra
                FROM producto_pv
                INNER JOIN requisicion_producto_pv ON producto_pv.id_producto_pv=requisicion_producto_pv.id_producto_pv 
                INNER JOIN requisicion_pv ON requisicion_producto_pv.id_requisicion_pv=requisicion_pv.id_requisicion_pv
                WHERE requisicion_pv.fecha_solicitud BETWEEN '2023-08-23' AND '2023-09-12'
                GROUP BY producto_pv.id_producto_pv
        */

        $query = DB::table("producto_pv")
            ->join("requisicion_producto_pv" , "producto_pv.id_producto_pv","requisicion_producto_pv.id_producto_pv")            
            ->join("requisicion_pv" , "requisicion_producto_pv.id_requisicion_pv","requisicion_pv.id_requisicion_pv")            
            ->select("producto_pv.clave"
            // ,"producto_pv.nombre"
            )
            ->selectRaw("COUNT(requisicion_producto_pv.id_requisicion_producto_pv) AS cantidad_requisiciones")
            ->selectRaw("SUM(requisicion_producto_pv.cantidad) AS cantidad_comprada")
            ->selectRaw("MAX(requisicion_pv.fecha_solicitud) AS fecha_ultima_compra")
            ->selectRaw("(SELECT req_sec.cantidad FROM requisicion_producto_pv AS req_sec WHERE req_sec.id_requisicion_producto_pv=max(requisicion_producto_pv.id_requisicion_producto_pv) LIMIT 1) AS ultima_cantidad_compra")
            ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre")
            
            ->groupBy("producto_pv.id_producto_pv");         

        if ($p->fecha_inicio ?? false) {
            $query->whereRaw("requisicion_pv.fecha_solicitud BETWEEN ? AND ?", [$p->fecha_inicio, $p->fecha_fin]);
        }       

        return $query->get();
    }

    public static function reporteProductoRequisicionRevision($p)
    {
        /*
            SELECT 
            	producto_pv.id_producto_pv,
            	producto_pv.nombre,
            	categoria_producto_pv.nombre AS categoria_name,
            	subcategoria_producto_pv.nombre AS subcategoria_name,
            	SUM(requisicion_producto_pv.cantidad) AS cantidad_pedida,
            	IFNULL(SUM(pedido_almacen_revision_pv.cantidad),0) AS cantidad_recibida
                #SUM(pedido_almacen_revision_cambio_producto_pv.cantidad)
            FROM requisicion_producto_pv
            INNER JOIN requisicion_pv ON requisicion_producto_pv.id_requisicion_pv=requisicion_pv.id_requisicion_pv
            INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv=producto_pv.id_producto_pv
            INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria=subcategoria_producto_pv.id_subcategoria_producto_pv
            INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv=categoria_producto_pv.id_categoria_pv
            left JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv AND pedido_almacen_producto_pv.estatus=1
            left JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto
            LEFT JOIN pedido_almacen_revision_cambio_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_revision_pv=pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv
            WHERE requisicion_pv.fecha_solicitud BETWEEN '2023-09-01' AND '2023-11-16' AND categoria_producto_pv.id_categoria_pv=1
            GROUP BY producto_pv.id_producto_pv
        */

        $query = DB::table("requisicion_producto_pv")
            ->join("requisicion_pv" , "requisicion_producto_pv.id_requisicion_pv","requisicion_pv.id_requisicion_pv")
            ->join("producto_pv" , "requisicion_producto_pv.id_producto_pv","producto_pv.id_producto_pv")
            ->join("subcategoria_producto_pv" , "producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->join("categoria_producto_pv" , "subcategoria_producto_pv.id_categoria_pv","categoria_producto_pv.id_categoria_pv")
            ->leftJoin("pedido_almacen_producto_pv",function($join){ $join->on("requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")->where("pedido_almacen_producto_pv.estatus",1);})
            ->leftJoin("pedido_almacen_revision_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")
            ->leftJoin("pedido_almacen_revision_cambio_producto_pv" , "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv","pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
           
            ->select(
                "producto_pv.clave",
            	// "producto_pv.nombre",
            	"categoria_producto_pv.nombre AS categoria_name",
            	"subcategoria_producto_pv.nombre AS subcategoria_name"        	
            )
            ->selectRaw("SUM(requisicion_producto_pv.cantidad) AS cantidad_pedida")
            ->selectRaw("IFNULL(SUM(pedido_almacen_revision_pv.cantidad),0) AS cantidad_recibida")
            ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre")
            ->groupBy("producto_pv.id_producto_pv");
        
        if ($p->fecha_inicio ?? false) {
            $query->whereRaw("requisicion_pv.fecha_solicitud BETWEEN ? AND ?", [$p->fecha_inicio, $p->fecha_fin]);
        }

        if ($p->categoria ?? false) {
            $query->where("categoria_producto_pv.id_categoria_pv", $p->categoria);
        }
       
        return $query->get();
    }  
    
    public static function reporteProductoCuadricula($p)
    {
      /*
            //esta es los nombres de los productos y su id
            SELECT 
	            producto_pv.id_producto_pv,
	            producto_pv.nombre 
            FROM producto_pv
            INNER JOIN requisicion_producto_pv ON producto_pv.id_producto_pv=requisicion_producto_pv.id_producto_pv
            INNER JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv
            INNER JOIN pedido_almacen_pv ON pedido_almacen_producto_pv.id_pedido_almacen_pv=pedido_almacen_pv.id_pedido_almacen_pv
            INNER JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto AND pedido_almacen_revision_pv.estatus=1
            GROUP BY producto_pv.id_producto_pv

            UNION

            SELECT 
	            pedido_almacen_revision_cambio_producto_pv.id_producto_pv,
	            producto_pv2.nombre
            FROM producto_pv
            INNER JOIN requisicion_producto_pv ON producto_pv.id_producto_pv=requisicion_producto_pv.id_producto_pv
            INNER JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv
            INNER JOIN pedido_almacen_pv ON pedido_almacen_producto_pv.id_pedido_almacen_pv=pedido_almacen_pv.id_pedido_almacen_pv
            INNER JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto AND pedido_almacen_revision_pv.estatus=2
			INNER JOIN pedido_almacen_revision_cambio_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_revision_pv=pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv     
			INNER JOIN producto_pv AS producto_pv2 ON pedido_almacen_revision_cambio_producto_pv.id_producto_pv=producto_pv2.id_producto_pv
            GROUP BY producto_pv.id_producto_pv



       
            //esta es el mes enero 
            SELECT 
	                producto_pv.id_producto_pv, 
	                fecha_revision,
	                MONTH(fecha_revision) AS mes,
                    SUM(pedido_almacen_revision_pv.cantidad) AS cantidad_comprada,
	                AVG(pedido_almacen_revision_pv.costo) AS promedio 
            FROM producto_pv
            INNER JOIN requisicion_producto_pv ON producto_pv.id_producto_pv=requisicion_producto_pv.id_producto_pv
            INNER JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv
            INNER JOIN pedido_almacen_pv ON pedido_almacen_producto_pv.id_pedido_almacen_pv=pedido_almacen_pv.id_pedido_almacen_pv
            INNER JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto AND pedido_almacen_revision_pv.estatus=1
            WHERE YEAR(fecha_revision)=2023
            GROUP BY producto_pv.id_producto_pv,MONTH(fecha_revision);

            UNION 

            SELECT 
	                pedido_almacen_revision_cambio_producto_pv.id_producto_pv, 
	                fecha_revision,
	                MONTH(fecha_revision),
	                SUM(pedido_almacen_revision_cambio_producto_pv.cantidad),
	                AVG(pedido_almacen_revision_cambio_producto_pv.costo)
            FROM producto_pv
            INNER JOIN requisicion_producto_pv ON producto_pv.id_producto_pv=requisicion_producto_pv.id_producto_pv
            INNER JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv
            INNER JOIN pedido_almacen_pv ON pedido_almacen_producto_pv.id_pedido_almacen_pv=pedido_almacen_pv.id_pedido_almacen_pv
            INNER JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto AND pedido_almacen_revision_pv.estatus=2
            INNER JOIN pedido_almacen_revision_cambio_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_revision_pv=pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv
            WHERE YEAR(fecha_revision)=2023
            GROUP BY producto_pv.id_producto_pv,MONTH(fecha_revision);

        
        */

        $name_products=DB::table("producto_pv")
            ->join("requisicion_producto_pv" , "producto_pv.id_producto_pv","requisicion_producto_pv.id_producto_pv")
            ->join("pedido_almacen_producto_pv" , "requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")
            ->join("pedido_almacen_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_pv","pedido_almacen_pv.id_pedido_almacen_pv")
            ->join("pedido_almacen_revision_pv" ,function($join){$join->on("pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")->where("pedido_almacen_revision_pv.estatus",1);})
            ->join("subcategoria_producto_pv","producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->select( "producto_pv.id_producto_pv"
            // ,"producto_pv.nombre"
            )
            ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre")
            
           
            ->groupBy("producto_pv.id_producto_pv");
        

        $name_products_cambio=DB::table("producto_pv")
            ->join("requisicion_producto_pv" , "producto_pv.id_producto_pv","requisicion_producto_pv.id_producto_pv")
            ->join("pedido_almacen_producto_pv" , "requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")
            ->join("pedido_almacen_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_pv","pedido_almacen_pv.id_pedido_almacen_pv")
            ->join("pedido_almacen_revision_pv" ,function($join){$join->on("pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")->where("pedido_almacen_revision_pv.estatus",2);})
            ->join("pedido_almacen_revision_cambio_producto_pv" , "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv","pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
            ->join("producto_pv AS producto_pv2" , "pedido_almacen_revision_cambio_producto_pv.id_producto_pv","producto_pv2.id_producto_pv")
            ->join("subcategoria_producto_pv","producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->select( "pedido_almacen_revision_cambio_producto_pv.id_producto_pv","producto_pv2.nombre")
            
           
            ->groupBy("producto_pv.id_producto_pv");

           

       
        
        $ene=DB::table("producto_pv")
         ->join("requisicion_producto_pv" , "producto_pv.id_producto_pv","requisicion_producto_pv.id_producto_pv")
         ->join("pedido_almacen_producto_pv" , "requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")
         ->join("pedido_almacen_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_pv","pedido_almacen_pv.id_pedido_almacen_pv")
         ->join("pedido_almacen_revision_pv" ,function($join){
            $join->on("pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")
            ->where("pedido_almacen_revision_pv.estatus",1);
        })
        ->join("subcategoria_producto_pv","producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
         ->select("producto_pv.id_producto_pv","fecha_revision")
         ->selectRaw("MONTH(fecha_revision) AS mes")
         ->selectRaw("SUM(pedido_almacen_revision_pv.cantidad) AS cantidad_comprada")
         ->selectRaw("AVG(pedido_almacen_revision_pv.costo) AS promedio")
         ->selectRaw("COUNT(producto_pv.id_producto_pv) AS veces_comprado")
         ->whereRaw("YEAR(fecha_revision) = ?",[$p->annio])
         ->groupBy("producto_pv.id_producto_pv")
         ->groupByRaw("MONTH(fecha_revision)");

         $ene_cambio=DB::table("producto_pv")
         ->join("requisicion_producto_pv" , "producto_pv.id_producto_pv","requisicion_producto_pv.id_producto_pv")
         ->join("pedido_almacen_producto_pv" , "requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")
         ->join("pedido_almacen_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_pv","pedido_almacen_pv.id_pedido_almacen_pv")
         ->join("pedido_almacen_revision_pv" ,function($join){
            $join->on("pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")
            ->where("pedido_almacen_revision_pv.estatus",2);
        })
        ->join("pedido_almacen_revision_cambio_producto_pv" , "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv","pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
        ->join("subcategoria_producto_pv","producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
         ->select("pedido_almacen_revision_cambio_producto_pv.id_producto_pv","fecha_revision")
         ->selectRaw("MONTH(fecha_revision) AS mes")
         ->selectRaw("SUM(pedido_almacen_revision_cambio_producto_pv.cantidad) AS cantidad_comprada")
         ->selectRaw("AVG(pedido_almacen_revision_cambio_producto_pv.costo) AS promedio")
         ->selectRaw("COUNT(producto_pv.id_producto_pv) AS veces_comprado")
         ->whereRaw("YEAR(fecha_revision) = ?",[$p->annio])
         ->groupBy("producto_pv.id_producto_pv")
         ->groupByRaw("MONTH(fecha_revision)");



         if($p->categoria??false)
         {
             $name_products->where("subcategoria_producto_pv.id_categoria_pv",$p->categoria);
             $name_products_cambio->where("subcategoria_producto_pv.id_categoria_pv",$p->categoria);
             $ene->where("subcategoria_producto_pv.id_categoria_pv",$p->categoria);
             $ene_cambio->where("subcategoria_producto_pv.id_categoria_pv",$p->categoria);
         }

         if($p->subcategoria??false)
         {
          $name_products->where("producto_pv.id_subcategoria",$p->subcategoria);
          $name_products_cambio->where("producto_pv.id_subcategoria",$p->subcategoria);
          $ene->where("producto_pv.id_subcategoria",$p->subcategoria);
          $ene_cambio->where("producto_pv.id_subcategoria",$p->subcategoria);
         }

            

        
   return ["productos"=>$name_products->union($name_products_cambio)->get(),"meses"=>$ene->union($ene_cambio)->get()];
        
       
    }

    public static function reporteProductoCuadriculaDetalle($p)
    {
        /*
            SELECT pedido_almacen_pv.fecha_revision,pedido_almacen_revision_pv.costo FROM producto_pv 
            INNER JOIN requisicion_producto_pv ON producto_pv.id_producto_pv=requisicion_producto_pv.id_producto_pv
            INNER JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv
            INNER JOIN pedido_almacen_pv ON pedido_almacen_producto_pv.id_pedido_almacen_pv=pedido_almacen_pv.id_pedido_almacen_pv
            INNER JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto AND pedido_almacen_revision_pv.estatus=1
            WHERE producto_pv.id_producto_pv=3 AND pedido_almacen_pv.fecha_revision BETWEEN '2023-09-01' AND '2023-09-21'

            UNION 

            SELECT pedido_almacen_pv.fecha_revision,pedido_almacen_revision_pv.costo FROM producto_pv 
            INNER JOIN  pedido_almacen_revision_cambio_producto_pv ON producto_pv.id_producto_pv=pedido_almacen_revision_cambio_producto_pv.id_producto_pv
            INNER JOIN pedido_almacen_revision_pv ON pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv=pedido_almacen_revision_pv.id_pedido_almacen_revision_pv
            INNER JOIN pedido_almacen_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_producto=pedido_almacen_producto_pv.id_pedido_almacen_producto_pv
            INNER JOIN pedido_almacen_pv ON pedido_almacen_producto_pv.id_pedido_almacen_pv=pedido_almacen_pv.id_pedido_almacen_pv
            WHERE producto_pv.id_producto_pv=3 AND pedido_almacen_pv.fecha_revision BETWEEN '2023-09-01' AND '2023-09-21'
          
         */

         try{

            $recibido_correcto=DB::table("producto_pv")
            ->join("requisicion_producto_pv" , "producto_pv.id_producto_pv","requisicion_producto_pv.id_producto_pv")
            ->join("pedido_almacen_producto_pv" , "requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")
            ->join("pedido_almacen_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_pv","pedido_almacen_pv.id_pedido_almacen_pv")
            ->join("pedido_almacen_revision_pv",
                function($join){ 
                    $join->on("pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")
                    ->where("pedido_almacen_revision_pv.estatus",1);})
            ->where("producto_pv.id_producto_pv",$p->id_producto)
            ->whereRaw("pedido_almacen_pv.fecha_revision BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin])
            ->select("pedido_almacen_pv.fecha_revision","pedido_almacen_revision_pv.costo");

            $recibido_cambio=DB::table("producto_pv")
            ->join("pedido_almacen_revision_cambio_producto_pv" , "producto_pv.id_producto_pv","pedido_almacen_revision_cambio_producto_pv.id_producto_pv")
            ->join("pedido_almacen_revision_pv" , "pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv","pedido_almacen_revision_pv.id_pedido_almacen_revision_pv")
            ->join("pedido_almacen_producto_pv" , "pedido_almacen_revision_pv.id_pedido_almacen_producto","pedido_almacen_producto_pv.id_pedido_almacen_producto_pv")
            ->join("pedido_almacen_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_pv","pedido_almacen_pv.id_pedido_almacen_pv")
            ->where("producto_pv.id_producto_pv",$p->id_producto)
            ->whereRaw("pedido_almacen_pv.fecha_revision BETWEEN ? AND ?",[$p->fecha_inicio,$p->fecha_fin])
            ->select("pedido_almacen_pv.fecha_revision","pedido_almacen_revision_cambio_producto_pv.costo");

            return $recibido_correcto->union($recibido_cambio)->get();
            

         }
         catch(\Exception $e)
         {

         }

    }



}
