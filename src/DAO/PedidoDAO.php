<?php

namespace App\DAO;

use App\Entity\producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PedidoDAO
{

    public function __construct()
    {
    }
    /**
     * 
     */
    public static function getPedidos()
    {
        try {
            return DB::table("pedido_almacen_pv")
                ->join("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
                ->join("persona", "pedido_almacen_pv.id_persona_pedido", "persona.cve_persona")
                ->select("pedido_almacen_pv.id_pedido_almacen_pv", "pedido_almacen_pv.id_proveedor", "pedido_almacen_pv.folio", "pedido_almacen_pv.fecha_pedido", "pedido_almacen_pv.estatus", "proveedor_pv.nombre_comercial", "persona.nombre", "persona.apellido_paterno", "persona.apellido_materno")
                ->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function crearPedido($p)
    {

    

        return DB::transaction(function () use ($p) {

            $folio = DB::table("pedido_almacen_pv")->max("folio");

            $id = DB::table("pedido_almacen_pv")
                ->insertGetId([
                    "folio" => ($folio ?? 0) + 1,
                    "id_persona_pedido" => $p->id_persona_pedido,
                    "id_proveedor" => $p->id_proveedor,
                    "fecha_pedido" => Carbon::now(),
                    "estatus" => 1
                ]);

            $productos_=collect($p->productos)->map(function($i)use($p,$id){return ["id_requisicion_producto_pv"=>$i,"id_proveedor"=>$p->id_proveedor,"id_pedido_almacen_pv"=>$id];})->all();

            DB::table("pedido_almacen_producto_pv")->insert($productos_);


            return $id;
        });
    }

    public static function getProductosPedido($id)
    {
        
        /*
            SELECT 
		        pedido_almacen_producto_pv.id_pedido_almacen_producto_pv, 
		        producto_pv.clave, 
		        producto_pv.nombre AS nombre_producto, 
		        producto_pv.descripcion AS descripcion_producto, 
		        producto_pv.modelo,
		        marca_productos_pv.nombre AS nombre_marca, 
		        marca_productos_pv.descripcion AS descripcion_marca, 
		        subcategoria_producto_pv.nombre AS nombre_subcategoria, 
		        subcategoria_producto_pv.descripcion AS descripcion_subcategoria, 
		        categoria_producto_pv.nombre AS nombre_categoria, 
		        categoria_producto_pv.descripcion AS descripcion_categoria, 
		        unidad_medido_producto_pv.categoria AS categoria_unidad_medida_producto, 
		        unidad_medido_producto_pv.nombre AS nombre_unidad_medida_producto, 
		        unidad_medido_producto_pv.descripcion AS descripcion_unidad_medida_producto, 
		        unidad_medido_producto_compra.categoria AS categoria_unidad_medida_compra, 
		        unidad_medido_producto_compra.nombre AS nombre_unidad_medida_compra, 
		        unidad_medido_producto_compra.descripcion AS descripcion_unidad_medida_compra, 
		        requisicion_producto_pv.cantidad, 
		        requisicion_producto_pv.observaciones, 
		        requisicion_pv.folio AS folio_requisicion, 
		        pedido_almacen_producto_pv.estatus,
		        requisicion_producto_pv.id_marca
            FROM pedido_almacen_producto_pv
            INNER JOIN requisicion_producto_pv ON pedido_almacen_producto_pv.id_requisicion_producto_pv = requisicion_producto_pv.id_requisicion_producto_pv
            INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv = producto_pv.id_producto_pv
            INNER JOIN requisicion_pv ON requisicion_producto_pv.id_requisicion_pv = requisicion_pv.id_requisicion_pv
            LEFT JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria = subcategoria_producto_pv.id_subcategoria_producto_pv
            LEFT JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv = categoria_producto_pv.id_categoria_pv
            LEFT JOIN marca_productos_pv ON requisicion_producto_pv.id_marca = marca_productos_pv.id_marca_productos_pv
            INNER JOIN unidad_medido_producto_pv ON producto_pv.id_unidad_medida = unidad_medido_producto_pv.id_unidad_medida_producto_pv
            INNER JOIN producto_presentacion_pv ON requisicion_producto_pv.id_producto_presentacion= producto_presentacion_pv.id_producto_presentacion_pv
            INNER JOIN unidad_medido_producto_pv AS unidad_medido_producto_compra ON producto_presentacion_pv.unidad_medida = unidad_medido_producto_compra.id_unidad_medida_producto_pv
            WHERE pedido_almacen_producto_pv.id_pedido_almacen_pv = 1
           
        */

        try {
            return DB::table("pedido_almacen_producto_pv")
                ->join("requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv", "requisicion_producto_pv.id_requisicion_producto_pv")
                ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
                ->join("requisicion_pv", "requisicion_producto_pv.id_requisicion_pv", "requisicion_pv.id_requisicion_pv")
                ->leftJoin("subcategoria_producto_pv", "producto_pv.id_subcategoria", "subcategoria_producto_pv.id_subcategoria_producto_pv")
                ->leftJoin("categoria_producto_pv", "subcategoria_producto_pv.id_categoria_pv", "categoria_producto_pv.id_categoria_pv")
                ->leftJoin("marca_productos_pv", "requisicion_producto_pv.id_marca", "marca_productos_pv.id_marca_productos_pv")
                ->join("unidad_medido_producto_pv", "producto_pv.id_unidad_medida", "unidad_medido_producto_pv.id_unidad_medida_producto_pv")
                ->join("producto_presentacion_pv" , "requisicion_producto_pv.id_producto_presentacion", "producto_presentacion_pv.id_producto_presentacion_pv")
                ->join("unidad_medido_producto_pv AS unidad_medido_producto_compra", "producto_presentacion_pv.unidad_medida", "unidad_medido_producto_compra.id_unidad_medida_producto_pv")
                ->where("pedido_almacen_producto_pv.id_pedido_almacen_pv", $id)
                ->select(
                    "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv",
                    "producto_pv.clave",
                    // "producto_pv.nombre AS nombre_producto",
                    "producto_pv.descripcion AS descripcion_producto",
                    "producto_pv.modelo",
                    "marca_productos_pv.nombre As nombre_marca",
                    "marca_productos_pv.descripcion As descripcion_marca",
                    "subcategoria_producto_pv.nombre As nombre_subcategoria",
                    "subcategoria_producto_pv.descripcion As descripcion_subcategoria",
                    "categoria_producto_pv.nombre As nombre_categoria",
                    "categoria_producto_pv.descripcion As descripcion_categoria",
                    "unidad_medido_producto_pv.categoria As categoria_unidad_medida_producto",
                    "unidad_medido_producto_pv.nombre As nombre_unidad_medida_producto",
                    "unidad_medido_producto_pv.descripcion As descripcion_unidad_medida_producto",
                    "unidad_medido_producto_compra.categoria As categoria_unidad_medida_compra",
                    "unidad_medido_producto_compra.nombre As nombre_unidad_medida_compra",
                    "unidad_medido_producto_compra.descripcion As descripcion_unidad_medida_compra",
                    "requisicion_producto_pv.cantidad",
                    "requisicion_producto_pv.observaciones",
                    "requisicion_pv.folio AS folio_requisicion",
                    "pedido_almacen_producto_pv.estatus"
                )
                // ->selectRaw("JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE','$.MATERIAL','$.MEDIDA1','$.MEDIDA2','$.FORMA','$.OTROS') AS nombre_producto")
                ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre_producto")
                ->get();
        } catch (\Exception $e) {
            return $e;
        }
    }


    public static function findProductoDisponiblesRequisicion($id)
    {
        /* 
            SELECT 
                    requisicion_pv.folio, 
                    requisicion_pv.id_requisicion_pv, 
                    requisicion_producto_pv.id_requisicion_producto_pv, 
                    producto_pv.clave, 
                    producto_pv.nombre, 
                    producto_pv.descripcion, 
                    IF(proveedor_categoria.id_proveedor_categoria IS NULL,0,1) AS for_categoria, 
                    IF(proveedor_producto.id_proveedor_producto IS NULL,0,1) AS for_producto
            FROM producto_pv
            INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria = subcategoria_producto_pv.id_subcategoria_producto_pv
            INNER JOIN requisicion_producto_pv ON producto_pv.id_producto_pv = requisicion_producto_pv.id_producto_pv
            INNER JOIN requisicion_pv ON requisicion_producto_pv.id_requisicion_pv = requisicion_pv.id_requisicion_pv
            LEFT JOIN proveedor_categoria ON subcategoria_producto_pv.id_categoria_pv = proveedor_categoria.id_categoria_producto AND proveedor_categoria.id_proveedor = 2
            LEFT JOIN proveedor_producto ON producto_pv.id_producto_pv = proveedor_producto.id_producto_pv AND proveedor_producto.id_proveedor = 2
            LEFT JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv = pedido_almacen_producto_pv.id_requisicion_producto_pv
            WHERE (proveedor_categoria.id_proveedor_categoria IS NOT NULL OR proveedor_producto.id_proveedor_producto IS NOT NULL) AND (pedido_almacen_producto_pv.id_pedido_almacen_producto_pv IS NULL OR (pedido_almacen_producto_pv.estatus = 0 AND (
            
            SELECT 
                COUNT(*)
            FROM pedido_almacen_producto_pv AS pap
            WHERE pap.id_requisicion_producto_pv = pedido_almacen_producto_pv.id_requisicion_producto_pv AND pap.estatus = 1) = 0)) AND requisicion_pv.estatus = 3 AND requisicion_producto_pv.estatus_revision = 1
            GROUP BY requisicion_producto_pv.id_requisicion_producto_pv
         */        

        return DB::table("producto_pv")
            ->join("subcategoria_producto_pv", "producto_pv.id_subcategoria", "subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->join("requisicion_producto_pv", "producto_pv.id_producto_pv", "requisicion_producto_pv.id_producto_pv")
            ->join("requisicion_pv", "requisicion_producto_pv.id_requisicion_pv", "requisicion_pv.id_requisicion_pv")
            ->leftJoin("proveedor_categoria", function ($join) use ($id) {
                $join->on("subcategoria_producto_pv.id_categoria_pv", "proveedor_categoria.id_categoria_producto")->where("proveedor_categoria.id_proveedor", $id);
            })
            ->leftJoin("proveedor_producto", function ($join) use ($id) {
                $join->on("producto_pv.id_producto_pv", "proveedor_producto.id_producto_pv")->where("proveedor_producto.id_proveedor", $id);
            })
            ->leftJoin("pedido_almacen_producto_pv", "requisicion_producto_pv.id_requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv")
            ->where(function ($query) {
                $query->whereNotNull("proveedor_categoria.id_proveedor_categoria")->orWhereNotNull("proveedor_producto.id_proveedor_producto");
            })
            ->where(function ($query) {
                $query->whereNull("pedido_almacen_producto_pv.id_pedido_almacen_producto_pv")
                    ->orWhere(function ($query2) {
                        $query2->where("pedido_almacen_producto_pv.estatus", 0)
                            ->where(function ($query3) {
                                $query3->select(DB::raw("COUNT(*)"))->from("pedido_almacen_producto_pv  AS pap")->whereColumn("pap.id_requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv")->where("pap.estatus", 1);
                            }, '0');
                    });
            })
            ->where("requisicion_pv.estatus", 3)
            ->where("requisicion_producto_pv.estatus_revision", 1)
            ->where("requisicion_producto_pv.estatus_confirmacion", 1)
            ->select(
                "requisicion_pv.folio",
                "requisicion_pv.id_requisicion_pv",
                "requisicion_producto_pv.id_requisicion_producto_pv",
                "producto_pv.clave",
                // "producto_pv.nombre",
                "producto_pv.descripcion"
            )
            ->selectRaw("IF(proveedor_categoria.id_proveedor_categoria IS NULL,0,1) AS for_categoria")
            ->selectRaw("IF(proveedor_producto.id_proveedor_producto IS NULL,0,1) AS for_producto")
            // ->selectRaw("JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE','$.MATERIAL','$.MEDIDA1','$.MEDIDA2','$.FORMA','$.OTROS') AS nombre")
            ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre")
            ->groupBy("requisicion_producto_pv.id_requisicion_producto_pv")
            ->get();
    }



    public static function agregarProducto($id, $id_producto)
    {
        try {
            $exist = DB::table("pedido_almacen_pv")->where("id_pedido_almacen_pv", $id)->where("estatus", 1)->exists();            
            if (!$exist) return 0;
            else {
                $id_provedor=DB::table("pedido_almacen_pv")->where("id_pedido_almacen_pv", $id)->where("estatus", 1)->value("id_proveedor");
                return DB::table("pedido_almacen_producto_pv")->insertGetId(["id_pedido_almacen_pv" => $id, "id_requisicion_producto_pv" => $id_producto,"id_proveedor"=>$id_provedor]);
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    //se elimina el producto del pedido...
    public static function eliminarProducto($id)
    {
        try {
            // si el pedido ya existe una revision
            $existe = DB::table("pedido_almacen_revision_pv")->where("id_pedido_almacen_producto", $id)->exists();
            if ($existe) return 2; //si tiene revision regresa 2            
            return DB::table("pedido_almacen_producto_pv")->where("id_pedido_almacen_producto_pv", $id)->update(["estatus" => 0]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function pedidosSinRevisar()
    {

        return DB::table("pedido_almacen_pv")
            ->JOIN("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
            ->JOIN("colonia", "proveedor_pv.id_colonia", "colonia.cve_colonia")
            ->select(
                "pedido_almacen_pv.id_pedido_almacen_pv",
                "pedido_almacen_pv.folio",
                "pedido_almacen_pv.fecha_pedido",
                "proveedor_pv.codigo",
                "proveedor_pv.nombre_comercial",
                "proveedor_pv.rfc",
                "proveedor_pv.calle",
                "proveedor_pv.n_ext",
                "proveedor_pv.n_int",
                "proveedor_pv.cp",
                "colonia.nombre"
            )
            ->where("pedido_almacen_pv.estatus", 1)
            ->get();
    }


    public static function pedidoRevision($id)
    {

        // dd($id);
        /*
            SELECT 
	                pedido_almacen_pv.id_pedido_almacen_pv, 
	                pedido_almacen_producto_pv.id_pedido_almacen_producto_pv, 
	                pedido_almacen_pv.folio, 
	                pedido_almacen_pv.fecha_pedido, 
	                proveedor_pv.nombre_comercial, 
	                producto_pv.clave, 
	                producto_pv.nombre AS nombre_producto, 
	                producto_pv.descripcion, 
	                #marca_productos_pv.nombre AS marca, 
	                producto_pv.modelo, 
	                requisicion_producto_pv.cantidad,
                    CONCAT_WS(' ',producto_presentacion_pv.cantidad,unidad_medido_producto_pv.nombre) AS presentacion_compra, 
	                #medida_compra.nombre AS medida_compra_producto, 
                    #producto_pv.tamano, 
	                #medida_producto.nombre AS medida_producto_, 
	                #producto_pv.piezas_contenido, 
	                IFNULL(pedido_almacen_revision_pv.id_pedido_almacen_revision_pv,0) AS agregado_revision, 
	                IFNULL(pedido_almacen_revision_pv.estatus,0) AS estatus_revision, 
	                IFNULL(pedido_almacen_revision_pv.cantidad,requisicion_producto_pv.cantidad) AS cantidad_entregada, 
	                IFNULL(pedido_almacen_revision_pv.costo,0) AS costo_producto, 
	                IFNULL(pedido_almacen_revision_pv.descuento,0) AS descuento, 
	                IFNULL(pedido_almacen_revision_pv.descuento_porcentaje,0) AS descuento_porcentaje, 
	                IFNULL(pedido_almacen_revision_pv.descripcion,'') AS descripcion_revision, 
	                IFNULL(pedido_almacen_revision_cambio_producto_pv.id_producto_pv,0) AS sustitucion_producto, 
	                IFNULL(producto_cambio.clave,'') AS clave_producto_sustituye, 
	                IFNULL(producto_cambio.nombre,'') AS nombre_producto_sustituye, 
	                IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,0) AS cantidad_producto_sustituye, 
	                IFNULL(pedido_almacen_revision_cambio_producto_pv.costo,0) AS costo_producto_sustituye
            FROM pedido_almacen_pv
            INNER JOIN proveedor_pv ON pedido_almacen_pv.id_proveedor = proveedor_pv.id_proveedor
            INNER JOIN pedido_almacen_producto_pv ON pedido_almacen_pv.id_pedido_almacen_pv = pedido_almacen_producto_pv.id_pedido_almacen_pv
            INNER JOIN requisicion_producto_pv ON pedido_almacen_producto_pv.id_requisicion_producto_pv = requisicion_producto_pv.id_requisicion_producto_pv
            INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv = producto_pv.id_producto_pv
            INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria = subcategoria_producto_pv.id_subcategoria_producto_pv
            INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv = categoria_producto_pv.id_categoria_pv
            
            left JOIN producto_marca_pv ON producto_pv.id_producto_pv=producto_marca_pv.id_producto_pv
            left JOIN marca_productos_pv ON producto_marca_pv.id_marca_productos_pv = marca_productos_pv.id_marca_productos_pv            
            
            LEFT JOIN producto_presentacion_pv ON requisicion_producto_pv.id_producto_presentacion=producto_presentacion_pv.id_producto_presentacion_pv
            LEFT JOIN unidad_medido_producto_pv ON producto_presentacion_pv.unidad_medida=unidad_medido_producto_pv.id_unidad_medida_producto_pv
            
            #INNER JOIN unidad_medido_producto_pv AS medida_compra ON producto_pv.id_unidad_medida_compra = medida_compra.id_unidad_medida_producto_pv
            #INNER JOIN unidad_medido_producto_pv AS medida_producto ON producto_pv.id_unidad_medida_producto = medida_producto.id_unidad_medida_producto_pv
            LEFT JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv = pedido_almacen_revision_pv.id_pedido_almacen_producto
            LEFT JOIN pedido_almacen_revision_cambio_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_revision_pv = pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv
            LEFT JOIN producto_pv AS producto_cambio ON pedido_almacen_revision_cambio_producto_pv.id_producto_pv = producto_cambio.id_producto_pv
            WHERE pedido_almacen_pv.id_pedido_almacen_pv = 1 AND pedido_almacen_producto_pv.estatus = 1
        */

        return DB::table("pedido_almacen_pv")
            ->join("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
            ->join("pedido_almacen_producto_pv", "pedido_almacen_pv.id_pedido_almacen_pv", "pedido_almacen_producto_pv.id_pedido_almacen_pv")
            ->join("requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv", "requisicion_producto_pv.id_requisicion_producto_pv")
            ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
            ->join("subcategoria_producto_pv", "producto_pv.id_subcategoria", "subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->join("categoria_producto_pv", "subcategoria_producto_pv.id_categoria_pv", "categoria_producto_pv.id_categoria_pv")

            ->leftJoin("marca_productos_pv" , "requisicion_producto_pv.id_marca" , "marca_productos_pv.id_marca_productos_pv")
            
            ->leftjoin("producto_presentacion_pv" , "requisicion_producto_pv.id_producto_presentacion","producto_presentacion_pv.id_producto_presentacion_pv")
            ->leftjoin("unidad_medido_producto_pv" , "producto_presentacion_pv.unidad_medida","unidad_medido_producto_pv.id_unidad_medida_producto_pv")
            // ->join("unidad_medido_producto_pv AS medida_compra", "producto_pv.id_unidad_medida_compra", "medida_compra.id_unidad_medida_producto_pv")
            // ->join("unidad_medido_producto_pv AS medida_producto", "producto_pv.id_unidad_medida_producto", "medida_producto.id_unidad_medida_producto_pv")
            ->leftJoin("pedido_almacen_revision_pv", "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_producto")
            ->leftJoin("pedido_almacen_revision_cambio_producto_pv",  "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv", "pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
            ->leftJoin("producto_pv AS producto_cambio", "pedido_almacen_revision_cambio_producto_pv.id_producto_pv", "producto_cambio.id_producto_pv")
            ->select(
                "pedido_almacen_pv.id_pedido_almacen_pv",
                "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv",
                "pedido_almacen_pv.folio",
                "pedido_almacen_pv.fecha_pedido",
                "proveedor_pv.nombre_comercial",
                "producto_pv.clave",
                // "producto_pv.nombre AS nombre_producto",
                "producto_pv.descripcion",
                "marca_productos_pv.nombre AS marca",
                "producto_pv.modelo",
                "requisicion_producto_pv.id_requisicion_producto_pv"
                //"requisicion_producto_pv.cantidad",
                // "medida_compra.nombre AS medida_compra_producto",
                //                                                                                                                                                                                                                                                                                                                                          "producto_pv.tamano",
                //"medida_producto.nombre AS medida_producto_",
                //"producto_pv.piezas_contenido"
            )
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.id_pedido_almacen_revision_pv,0) AS agregado_revision")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.estatus,0) AS estatus_revision")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.cantidad,requisicion_producto_pv.cantidad) AS cantidad_entregada")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.costo,0) AS costo_producto")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.descuento,0) AS descuento")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.descuento_porcentaje,0) AS descuento_porcentaje")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.descripcion,'') AS descripcion_revision")
            ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.id_producto_pv,0) AS sustitucion_producto")
            ->selectRaw("IFNULL(producto_cambio.clave,'') AS clave_producto_sustituye")
            ->selectRaw("IFNULL(producto_cambio.nombre,'') AS nombre_producto_sustituye")
            ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,0) AS cantidad_producto_sustituye")
            ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.costo,0) AS costo_producto_sustituye")
            // ->selectRaw("JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE','$.MATERIAL','$.MEDIDA1','$.MEDIDA2','$.FORMA','$.OTROS') AS nombre_producto")
            ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre_producto")
            ->selectRaw("CONCAT_WS(' ',producto_presentacion_pv.cantidad,unidad_medido_producto_pv.nombre) AS presentacion_compra")
            ->where("pedido_almacen_pv.id_pedido_almacen_pv", $id)
            ->where("pedido_almacen_producto_pv.estatus", 1)
            ->get();
    }

    public static function aceptarProductoPedido($p)
    {
        return  DB::table("pedido_almacen_revision_pv")->insertGetId(
            [
                "id_pedido_almacen_producto" => $p->id,
                "descripcion" => $p->descripcion,
                "cantidad" => $p->cantidad,
                "costo" => $p->costo,
                "descuento" => $p->descuento,
                "descuento_porcentaje" => $p->descuento_porcentaje,
            ]
        );
    }

    public static function rechazarProductoPedido($p)
    {
        return  DB::table("pedido_almacen_revision_pv")->insertGetId(
            [
                "id_pedido_almacen_producto" => $p->id,
                "descripcion" => $p->descripcion,
                "cantidad" => $p->cantidad,
                "costo" => $p->costo,
                "estatus" => 0
            ]
        );
    }

    public static function cambioProductoPedido($p)
    {

        return DB::transaction(function () use ($p) {


            $revision = DB::table("pedido_almacen_revision_pv")->insertGetId(
                [
                    "id_pedido_almacen_producto" => $p->id,
                    "descripcion" => $p->descripcion ?? '',
                    "cantidad" => $p->cantidad ?? 0,
                    "costo" => $p->costo ?? 0,
                    "descuento" => $p->descuento ?? 0,
                    "descuento_porcentaje" => $p->descuento_porcentaje ?? 0,
                    "estatus" => 2 //el 2 indica cambio...
                ]
            );

            DB::table("pedido_almacen_revision_cambio_producto_pv")
                ->insertGetId([
                    "id_pedido_almacen_revision_pv" => $revision,
                    "id_producto_pv" => $p->id_producto,
                    "costo" => $p->costo_producto,
                    "cantidad" => $p->cantidad_producto,
                ]);

            return $revision;
        });
    }

    public static function cancelarPedido($id)
    {
       return  DB::transaction(function () use ($id) {

            $update_pedido=DB::table("pedido_almacen_pv")->where("id_pedido_almacen_pv", $id)->update(["estatus" => 0]);

            // DB::table("pedido_almacen_producto_pv")->where("id_pedido_almacen_producto_pv", $id)->update(["estatus" => 0]);
            $update_pedido_producto=DB::table("pedido_almacen_producto_pv")->where("id_pedido_almacen_pv", $id)->update(["estatus" => 0]);

            return ["pedido"=>$update_pedido,"producto_pedido"=>$update_pedido_producto];
        });
    }


    public static function finalizarRevisionPedido($id_pedido, $p)
    {

        return DB::transaction(function () use ($id_pedido, $p) {


            //verifica si el pedido ya es completamente revisado
            $flag = DB::table("pedido_almacen_producto_pv")
                ->leftJoin("pedido_almacen_revision_pv", "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_producto")
                ->where("pedido_almacen_producto_pv.id_pedido_almacen_pv", $id_pedido)
                ->where("pedido_almacen_producto_pv.estatus", 1)
                ->selectRaw("SUM(DISTINCT IF(pedido_almacen_revision_pv.id_pedido_almacen_revision_pv IS NOT NULL,1,2)) AS flag")
                ->value("flag");
                // ->toSql();

                



            //si es uno es que todo ya esta revisado...
            if ($flag == 1) {


                $flag = DB::table("pedido_almacen_pv")->where("id_pedido_almacen_pv", $id_pedido)->update(
                    [
                        "estatus" => 2,
                        "subtotal" => $p->subtotal,
                        "iva" => $p->iva,
                        "total" => $p->total,
                        "ieps" => $p->ieps
                    ]
                );

                

                /*
                   
                
                */


                //se buscan todos los productos ya revisados y aceptados del pedido y que no esten en almacen
                $productos = DB::table("pedido_almacen_producto_pv")
                    ->join("requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv", "requisicion_producto_pv.id_requisicion_producto_pv")
                    ->join("pedido_almacen_revision_pv", "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_producto")
                    ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
                    ->join("producto_presentacion_pv", "requisicion_producto_pv.id_producto_presentacion", "producto_presentacion_pv.id_producto_presentacion_pv")
                    ->join("unidad_medido_producto_pv AS unidad_medida_compra", "producto_presentacion_pv.unidad_medida", "unidad_medida_compra.id_unidad_medida_producto_pv")
                    ->leftJoin("pedido_almacen_revision_cambio_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv", "pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
                    ->leftJoin("almacen_entrada", "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv", "almacen_entrada.id_pedido_almacen_revision")
                    ->leftJoin("producto_pv AS producto_cambio", "pedido_almacen_revision_cambio_producto_pv.id_producto_pv", "producto_cambio.id_producto_pv")
                    ->leftJoin("producto_presentacion_pv AS presentacion_cambio", "pedido_almacen_revision_cambio_producto_pv.id_presentacion_producto", "presentacion_cambio.id_producto_presentacion_pv")
                    ->leftJoin("unidad_medido_producto_pv AS unidad_medida_compra_cambio", "presentacion_cambio.unidad_medida", "unidad_medida_compra_cambio.id_unidad_medida_producto_pv")
                    ->where("pedido_almacen_producto_pv.id_pedido_almacen_pv", $id_pedido)
                    ->whereNull("almacen_entrada.id_almacen_entrada")
                    ->whereIn("pedido_almacen_revision_pv.estatus",[1,2])
                    ->whereRaw("IFNULL(producto_cambio.tipo,producto_pv.tipo)=1")
                    ->select("pedido_almacen_revision_pv.id_pedido_almacen_revision_pv AS id_pedido_almacen_revision")
                    ->selectRaw("IFNULL(producto_cambio.id_producto_pv,producto_pv.id_producto_pv) AS id_producto")
                    // ->selectRaw("IFNULL(producto_cambio.nombre,producto_pv.nombre) AS producto_nombre")
                    // ->selectRaw("IFNULL(producto_cambio.nombre,JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE','$.MATERIAL','$.MEDIDA1','$.MEDIDA2','$.FORMA','$.OTROS')) AS producto_nombre")
                    ->selectRaw("IFNULL(unidad_medida_compra_cambio.nombre,unidad_medida_compra.nombre) AS unidad_compra")
                    ->selectRaw("ifnull(unidad_medida_compra_cambio.categoria,unidad_medida_compra.categoria) AS categoria_compra")
                    ->selectRaw("IFNULL(presentacion_cambio.cantidad,producto_presentacion_pv.cantidad) AS piezas_contenido")
                    ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,pedido_almacen_revision_pv.cantidad) AS cantidad")
                    ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS producto_nombre")
                    ->get();

                    // dd($productos);
            

                if ($productos->count() > 0) {

                    //obtiene los id de los productosque se van a ingresar
                    $only_id_producto = $productos->unique("id_producto")->map(function ($i) {
                        return $i->id_producto;
                    })->toArray();
                    //se obtiene los productos que ya existen en bd
                    $ya_existen = DB::table("almacen_producto_pv")->whereIn("id_producto_pv", $only_id_producto)->get("id_producto_pv")->map(function ($i) {
                        return $i->id_producto_pv;
                    });
                    //quita de los id productos 
                    $no_existe = collect($only_id_producto)->diff($ya_existen)->map(function ($i) {
                        return ["id_almacen_pv" => 1, "id_producto_pv" => $i];
                    })->toArray();

                    //ingresa a almacen la relacion con el producto o productos que aun no estan ligados
                    DB::table("almacen_producto_pv")->insert($no_existe);

                    //se mapea para que conincidan con las columnas de la tabla de entradas
                    $productos_ = $productos->map(function ($item) {
                        return [
                            "id_pedido_almacen_revision" => $item->id_pedido_almacen_revision,
                            "id_producto" => $item->id_producto,
                            "cantidad" => $item->cantidad,
                            "fecha" => Carbon::now()->format("Y-m-d"),
                            "categoria" => $item->categoria_compra,
                            // "piezas_contenido" => $item->piezas_contenido
                            "piezas_contenido" => 100
                        ];
                    });



                    //son los productos que solo se pidio una unidad
                    $productos_unidad = $productos_->where("cantidad", 1)->map(function ($i) {
                        return [
                            "id_pedido_almacen_revision" => $i["id_pedido_almacen_revision"],
                            "id_producto" => $i["id_producto"],
                            "cantidad" => $i["cantidad"],
                            "fecha" => $i["fecha"]
                        ];
                    });

                    //son los productos que se piden mas de una unidad pero su numero de piezas es 0 o mayor a 1 
                    $productos_not_mas_unidad = $productos_->where("cantidad", ">", 1)->where("piezas_contenido", "!=", 1)->map(function ($i) {
                        return [
                            "id_pedido_almacen_revision" => $i["id_pedido_almacen_revision"],
                            "id_producto" => $i["id_producto"],
                            "cantidad" => $i["cantidad"],
                            "fecha" => $i["fecha"]
                        ];
                    });;
                    //son los productos que se tienen mas de una unidad
                    $productos_mas_unidad = $productos_->where("cantidad", ">", 1)->where("categoria", "unidad")->where("piezas_contenido", 1);

                    //se crea coleccion que despues se llenara segun las veces que se aya un producto o mas
                    $collection_mas_productos = collect();

                    $productos_mas_unidad->each(function ($item) use ($collection_mas_productos) {
                        for ($i = 0; $i < $item["cantidad"]; $i++) {
                            $item_temp = [
                                "id_pedido_almacen_revision" => $item["id_pedido_almacen_revision"],
                                "id_producto" => $item["id_producto"],
                                "cantidad" => 1,
                                "fecha" => Carbon::now()->format("Y-m-d"),
                            ];
                            $collection_mas_productos->push($item_temp);
                        }
                    });

                    //se concatenan el array de una unidad con la nueva que salio de n productos(cantidad)
                    $productos_entrada_almacen = $productos_unidad->concat($productos_not_mas_unidad)->concat($collection_mas_productos)->toArray();

                    //se guardan los productos
                    $flag_prod = DB::table("almacen_entrada")->insert($productos_entrada_almacen);

                    //se acytualiza el pedido a estatus 3 que es que ya estan en almacen los productos
                    if ($flag_prod) {
                        DB::table("pedido_almacen_pv")->where("id_pedido_almacen_pv", $id_pedido)->update(["estatus" => 3]);
                    }
                }
            } //if indica que el pedido esta revisado todo
            
            return $flag;
        });
    }


    public static function agregarNotaPedido($id_pedido, $nota, $nota_file)
    {
        return DB::table("pedido_almacen_notas_pv")->insertGetId([
            "id_pedido_almacen_pv" => $id_pedido,
            "nota" => $nota,
            "nota_file" => $nota_file,
            "estatus" => 1,
        ]);
    }

    public static function agregarFacturaPdfXml($id_pedido, $factura_pdf/*,$facturaXml*/)
    {
        return DB::table("pedido_almacen_pv")->where("id_pedido_almacen_pv", $id_pedido)->update([
            "orden_compra" => $factura_pdf,
            // "orden_compra_xml"=>$facturaXml,        
        ]);
    }

    public static function cambiarProveedor($id_pedido_producto,$id_proveedor){
        return DB::table("pedido_almacen_producto_pv")
        ->where("id_pedido_almacen_producto_pv",$id_pedido_producto)
        ->update([
            "id_proveedor"=>$id_proveedor
        ]);
    }


    public static function detalleProductosLibresParaPedido()
    {
        // SELECT 
        //     categoria_producto_pv.id_categoria_pv,
        //     categoria_producto_pv.nombre,
	    //     COUNT(categoria_producto_pv.id_categoria_pv) total_libres
        // FROM `producto_pv`
        // INNER JOIN `subcategoria_producto_pv` ON `producto_pv`.`id_subcategoria` = `subcategoria_producto_pv`.`id_subcategoria_producto_pv`
        // INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv=categoria_producto_pv.id_categoria_pv
        // INNER JOIN `requisicion_producto_pv` ON `producto_pv`.`id_producto_pv` = `requisicion_producto_pv`.`id_producto_pv`
        // INNER JOIN `requisicion_pv` ON `requisicion_producto_pv`.`id_requisicion_pv` = `requisicion_pv`.`id_requisicion_pv`
        // LEFT JOIN `pedido_almacen_producto_pv` ON `requisicion_producto_pv`.`id_requisicion_producto_pv` = `pedido_almacen_producto_pv`.`id_requisicion_producto_pv`
        // WHERE pedido_almacen_producto_pv.id_pedido_almacen_producto_pv IS NULL AND  requisicion_producto_pv.estatus_revision=1 and requisicion_producto_pv.estatus_confirmacion=1 
        // GROUP BY categoria_producto_pv.id_categoria_pv

        return DB::table("producto_pv")
        ->join("subcategoria_producto_pv" , "producto_pv.id_subcategoria" , "subcategoria_producto_pv.id_subcategoria_producto_pv")
        ->join("categoria_producto_pv" , "subcategoria_producto_pv.id_categoria_pv","categoria_producto_pv.id_categoria_pv")
        ->join("requisicion_producto_pv" , "producto_pv.id_producto_pv" , "requisicion_producto_pv.id_producto_pv")
        ->join("requisicion_pv" , "requisicion_producto_pv.id_requisicion_pv" , "requisicion_pv.id_requisicion_pv")
        ->leftJoin("pedido_almacen_producto_pv" , "requisicion_producto_pv.id_requisicion_producto_pv" , "pedido_almacen_producto_pv.id_requisicion_producto_pv")
        ->whereNull("pedido_almacen_producto_pv.id_pedido_almacen_producto_pv")
        ->where("requisicion_producto_pv.estatus_revision",1)
        ->where("requisicion_producto_pv.estatus_confirmacion",1)
        ->groupBy("categoria_producto_pv.id_categoria_pv")
        ->select("categoria_producto_pv.id_categoria_pv","categoria_producto_pv.nombre",DB::raw( "COUNT(categoria_producto_pv.id_categoria_pv) total_libres"))
        ->get();

    }


    public static function cambiarMarcaProductoPedidoRevision($id_requisicion_producto_pv,$id_marca)
    {
  
        return DB::table("requisicion_producto_pv")->where("id_requisicion_producto_pv",$id_requisicion_producto_pv)->update(["id_marca"=>$id_marca]);

    }


}
