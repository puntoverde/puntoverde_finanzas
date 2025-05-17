<?php

namespace App\DAO;

use App\Entity\producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdenCompraDAO
{

    public function __construct()
    {
    }
    /**
     * 
     */
    public static function getOrdenCompra($id_pedido)
    {

        try {

            $datos_pedido = DB::table("pedido_almacen_pv")
                ->join("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
                ->leftJoin("orden_compra_pv", "pedido_almacen_pv.id_pedido_almacen_pv", "orden_compra_pv.id_pedido_almacen_pv")
                ->where("pedido_almacen_pv.id_pedido_almacen_pv", $id_pedido)
                ->select(
                    "proveedor_pv.nombre_comercial",
                    "pedido_almacen_pv.fecha_pedido",
                    "pedido_almacen_pv.fecha_revision",
                    "pedido_almacen_pv.folio",
                    "pedido_almacen_pv.ieps",
                    "pedido_almacen_pv.iva",
                    "pedido_almacen_pv.subtotal",
                    "pedido_almacen_pv.total"
                )
                ->selectRaw("IFNULL(orden_compra_pv.id_orden_compra,0) AS is_orden")
                ->selectRaw("IFNULL(orden_compra_pv.area_solicitante,0) AS area_solicita")
                ->selectRaw("IFNULL(orden_compra_pv.observaciones,'') AS observacion")
                // ->selectRaw("IFNULL(orden_compra_pv.pago,0) AS pago")
                // ->selectRaw("IFNULL(orden_compra_pv.concepto_pago,'')AS concepto")
                ->selectRaw("IFNULL(orden_compra_pv.elaboro,0) AS elaboro")
                ->selectRaw("IFNULL(orden_compra_pv.reviso,0) AS revision")
                ->selectRaw("IFNULL(orden_compra_pv.autorizo,0) AS autorizo")
                ->selectRaw("IFNULL(orden_compra_pv.reviza_finanzas,0) AS reviza_finanzas")
                ->selectRaw("IFNULL(orden_compra_pv.autoriza_finanza,0) AS autoriza_finanzas")
                ->selectRaw("IFNULL(orden_compra_pv.autoriza_finanzas_dos,0) AS autoriza_finanzas_dos")
                ->first();

            
                /*
                    SELECT 
		                pedido_almacen_producto_pv.id_pedido_almacen_pv, 
		                pedido_almacen_revision_pv.id_pedido_almacen_producto, 
		                pedido_almacen_revision_pv.estatus, 
		                producto_cambio.id_producto_pv, 
		                pedido_almacen_revision_pv.descripcion AS descripcion_revision, 
		                IFNULL(producto_cambio.id_producto_pv,
		                producto_pv.id_producto_pv) AS id_producto_pv, 
		                IFNULL(producto_cambio.clave,producto_pv.clave) AS clave, 
		                IFNULL(producto_cambio.nombre,producto_pv.nombre) AS nombre, 
		                IFNULL(producto_cambio.descripcion,producto_pv.descripcion) AS descripcion, 
		                IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,pedido_almacen_revision_pv.cantidad) AS cantidad, 
		                IFNULL(pedido_almacen_revision_cambio_producto_pv.costo,pedido_almacen_revision_pv.costo) AS costo
                    FROM pedido_almacen_revision_pv
                    LEFT JOIN pedido_almacen_revision_cambio_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_revision_pv = pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv
                    LEFT JOIN producto_pv AS producto_cambio ON pedido_almacen_revision_cambio_producto_pv.id_producto_pv = producto_cambio.id_producto_pv
                    LEFT JOIN unidad_medido_producto_pv AS unidad_producto ON producto_cambio.id_unidad_medida = unidad_producto.id_unidad_medida_producto_pv
                    INNER JOIN pedido_almacen_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_producto = pedido_almacen_producto_pv.id_pedido_almacen_producto_pv
                    INNER JOIN requisicion_producto_pv ON pedido_almacen_producto_pv.id_requisicion_producto_pv = requisicion_producto_pv.id_requisicion_producto_pv
                    INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv = producto_pv.id_producto_pv
                    INNER JOIN unidad_medido_producto_pv ON producto_pv.id_unidad_medida = unidad_medido_producto_pv.id_unidad_medida_producto_pv
                    WHERE pedido_almacen_producto_pv.id_pedido_almacen_pv = 1
                */


            $productos = DB::table("pedido_almacen_revision_pv")
                ->leftJoin("pedido_almacen_revision_cambio_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv", "pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
                ->leftJoin("producto_pv AS producto_cambio", "pedido_almacen_revision_cambio_producto_pv.id_producto_pv", "producto_cambio.id_producto_pv")
                ->leftJoin("unidad_medido_producto_pv AS unidad_producto", "producto_cambio.id_unidad_medida", "unidad_producto.id_unidad_medida_producto_pv")
                ->join("pedido_almacen_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_producto", "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv")
                ->join("requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv", "requisicion_producto_pv.id_requisicion_producto_pv")
                ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
                ->join("unidad_medido_producto_pv", "producto_pv.id_unidad_medida", "unidad_medido_producto_pv.id_unidad_medida_producto_pv")
                ->where("pedido_almacen_producto_pv.id_pedido_almacen_pv", $id_pedido)
                ->select(
                    "pedido_almacen_producto_pv.id_pedido_almacen_pv",
                    "pedido_almacen_revision_pv.id_pedido_almacen_producto",
                    "pedido_almacen_revision_pv.estatus",
                    "producto_cambio.id_producto_pv",
                    "pedido_almacen_revision_pv.descripcion AS descripcion_revision"
                )
                ->selectRaw("IFNULL(producto_cambio.id_producto_pv,producto_pv.id_producto_pv) AS id_producto_pv")
                ->selectRaw("IFNULL(producto_cambio.clave,producto_pv.clave) AS clave")
                // ->selectRaw("IFNULL(producto_cambio.nombre,producto_pv.nombre) AS nombre")
                ->selectRaw("IFNULL(producto_cambio.descripcion,producto_pv.descripcion) AS descripcion")
                ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,pedido_almacen_revision_pv.cantidad) AS cantidad")
                // ->selectRaw("IFNULL(unidad_producto.nombre,unidad_medido_producto_pv.nombre) AS unidad")
                ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.costo,pedido_almacen_revision_pv.costo) AS costo")
                // ->selectRaw("IFNULL(producto_cambio.nombre,JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE','$.MATERIAL','$.MEDIDA1','$.MEDIDA2','$.FORMA','$.OTROS')) AS nombre")
                ->selectRaw("IFNULL(producto_cambio.nombre,CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                )) AS nombre_producto")
                ->get();

                // dd($productos);


            $firmas = DB::table("orden_compra_configuracion_pv")
                ->join("persona AS persona_elabora", "orden_compra_configuracion_pv.elaboro", "persona_elabora.cve_persona")
                ->join("persona AS persona_reviso", "orden_compra_configuracion_pv.reviso", "persona_reviso.cve_persona")
                ->join("persona AS persona_autorizo", "orden_compra_configuracion_pv.autorizo", "persona_autorizo.cve_persona")
                ->join("persona AS persona_finanzas", "orden_compra_configuracion_pv.reviso_finanzas", "persona_finanzas.cve_persona")
                ->join("persona AS persona_firma_uno", "orden_compra_configuracion_pv.firma_autorizada_1", "persona_firma_uno.cve_persona")
                ->join("persona AS persona_firma_dos", "orden_compra_configuracion_pv.firma_autorizada_2", "persona_firma_dos.cve_persona")
                ->select(
                    "orden_compra_configuracion_pv.elaboro",
                    "orden_compra_configuracion_pv.reviso",
                    "orden_compra_configuracion_pv.autorizo",
                    "orden_compra_configuracion_pv.reviso_finanzas",
                    "orden_compra_configuracion_pv.firma_autorizada_1",
                    "orden_compra_configuracion_pv.firma_autorizada_2"
                )
                // ->selectRaw("CONCAT_WS(' ',persona_elabora.nombre,persona_elabora.apellido_paterno,persona_elabora.apellido_materno) AS elabora_")
                ->selectRaw("CONCAT_WS(' ',persona_elabora.nombre,persona_elabora.apellido_paterno) AS elabora_")
                // ->selectRaw("CONCAT_WS(' ',persona_reviso.nombre,persona_reviso.apellido_paterno,persona_reviso.apellido_materno) AS reviso_")
                ->selectRaw("CONCAT_WS(' ',persona_reviso.nombre,persona_reviso.apellido_paterno) AS reviso_")
                // ->selectRaw("CONCAT_WS(' ',persona_autorizo.nombre,persona_autorizo.apellido_paterno,persona_autorizo.apellido_materno) AS autorizo_")
                ->selectRaw("CONCAT_WS(' ',persona_autorizo.nombre,persona_autorizo.apellido_paterno) AS autorizo_")
                // ->selectRaw("CONCAT_WS(' ',persona_finanzas.nombre,persona_finanzas.apellido_paterno,persona_finanzas.apellido_materno) AS finanzas_")
                ->selectRaw("CONCAT_WS(' ',persona_finanzas.nombre,persona_finanzas.apellido_paterno) AS finanzas_")
                // ->selectRaw("CONCAT_WS(' ',persona_firma_uno.nombre,persona_firma_uno.apellido_paterno,persona_firma_uno.apellido_materno) AS firma_uno_")
                ->selectRaw("CONCAT_WS(' ',persona_firma_uno.nombre,persona_firma_uno.apellido_paterno) AS firma_uno_")
                // ->selectRaw("CONCAT_WS(' ',persona_firma_dos.nombre,persona_firma_dos.apellido_paterno,persona_firma_dos.apellido_materno) AS firma_dos_")
                ->selectRaw("CONCAT_WS(' ',persona_firma_dos.nombre,persona_firma_dos.apellido_paterno) AS firma_dos_")
                ->first();

            return ["datos_pedido" => $datos_pedido, "productos" => $productos, "firmas" => $firmas];
        } catch (\Exception $e) {

            return $e;
        }
    }



    public static function getPedidosRevisados()
    {
        return DB::table("pedido_almacen_pv")
            ->join("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
            ->where("pedido_almacen_pv.estatus", 3)
            ->select("pedido_almacen_pv.id_pedido_almacen_pv", "nombre_comercial", "folio", "fecha_pedido", "fecha_revision", "total")
            ->toSql();
    }

    public static function saveOrdenCompra($p)
    {
        return DB::table("orden_compra_pv")
            ->insertGetId([
                "id_pedido_almacen_pv" => $p->id_pedido_almacen_pv,
                // "area_solicitante" => $p->area_solicitante,
                "observaciones" => $p->observaciones,
                "elaboro" => $p->elaboro,
                "reviso" => $p->reviso,
                "autorizo" => $p->autorizo,                
                "reviza_finanzas" => $p->reviza_finanzas,
                "autoriza_finanza" => $p->autoriza_finanza,
                "autoriza_finanzas_dos" => $p->autoriza_finanzas_dos
                
            ]);
    }
}
