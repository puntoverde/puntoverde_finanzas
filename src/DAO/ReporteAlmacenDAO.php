<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;


class ReporteAlmacenDAO
{

    public function __construct()
    {
    }


    //obtener los datos
    public static function reporteAlmacenEntrada($p)
    {
        /* 
                SELECT 
                requisicion_pv.folio,
                requisicion_pv.fecha_solicitud,
                IFNULL(colaborador.nomina,'-') AS nomina,
                CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS solicita,
                CONCAT(area_rh.nombre,'/',rh_departamento.nombre) AS area_departamento,
                concat(espacio_fisico.nombre,'/',edificios.nombre) AS area_destino,
                IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,pedido_almacen_revision_pv.cantidad) AS cantidad,
                IFNULL(pedido_almacen_revision_cambio_producto_pv.costo,pedido_almacen_revision_pv.costo) AS costo,
                pedido_almacen_revision_pv.descuento,
                almacen_entrada.fecha AS  fecha_ingreso,
                pedido_almacen_pv.orden_compra AS factura, 
                IFNULL(producto_cambio.clave,producto_pv.clave) AS producto_clave,
                IFNULL(producto_cambio.nombre,producto_pv.nombre) AS producto_servicio,
                IFNULL(categoria_cambio.nombre,categoria_producto_pv.nombre) AS categoria,
                IFNULL(subcategoria_cambio.nombre,subcategoria_producto_pv.nombre) AS subcategoria,
                proveedor_pv.nombre_comercial

                FROM almacen_entrada
                INNER JOIN pedido_almacen_revision_pv ON almacen_entrada.id_pedido_almacen_revision=pedido_almacen_revision_pv.id_pedido_almacen_revision_pv
                LEFT JOIN pedido_almacen_revision_cambio_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_revision_pv=pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv
                INNER JOIN pedido_almacen_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_producto=pedido_almacen_producto_pv.id_pedido_almacen_producto_pv
                INNER JOIN pedido_almacen_pv ON pedido_almacen_producto_pv.id_pedido_almacen_pv=pedido_almacen_pv.id_pedido_almacen_pv
                INNER JOIN requisicion_producto_pv ON pedido_almacen_producto_pv.id_requisicion_producto_pv=requisicion_producto_pv.id_requisicion_producto_pv
                INNER JOIN requisicion_pv ON requisicion_producto_pv.id_requisicion_pv=requisicion_pv.id_requisicion_pv
                INNER JOIN persona ON requisicion_pv.id_persona_solicita=persona.cve_persona
                LEFT JOIN colaborador ON persona.cve_persona=colaborador.cve_persona
                LEFT JOIN rh_departamento ON colaborador.id_departamento=rh_departamento.id_departamento
                LEFT JOIN area_rh ON rh_departamento.id_area_rh=area_rh.id_area_rh
                INNER JOIN espacio_fisico ON requisicion_producto_pv.id_espacio_fisico=espacio_fisico.id_espacio_fisico
                INNER JOIN  edificios ON  espacio_fisico.id_edificio=edificios.cve_edificio
                INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv=producto_pv.id_producto_pv
                INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria=subcategoria_producto_pv.id_subcategoria_producto_pv
                INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv=categoria_producto_pv.id_categoria_pv
                LEFT JOIN producto_pv AS producto_cambio ON pedido_almacen_revision_cambio_producto_pv.id_producto_pv=producto_cambio.id_producto_pv
                LEFT JOIN subcategoria_producto_pv AS subcategoria_cambio ON producto_cambio.id_subcategoria=subcategoria_cambio.id_subcategoria_producto_pv
                LEFT JOIN categoria_producto_pv AS categoria_cambio ON subcategoria_cambio.id_categoria_pv=categoria_cambio.id_categoria_pv
                INNER JOIN proveedor_pv ON pedido_almacen_pv.id_proveedor=proveedor_pv.id_proveedor
        */

        $query = DB::table("almacen_entrada")
            ->join("pedido_almacen_revision_pv", "almacen_entrada.id_pedido_almacen_revision", "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv")
            ->leftJoin("pedido_almacen_revision_cambio_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv", "pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
            ->join("pedido_almacen_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_producto", "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv")
            ->join("pedido_almacen_pv", "pedido_almacen_producto_pv.id_pedido_almacen_pv", "pedido_almacen_pv.id_pedido_almacen_pv")
            ->join("requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv", "requisicion_producto_pv.id_requisicion_producto_pv")
            ->join("requisicion_pv", "requisicion_producto_pv.id_requisicion_pv", "requisicion_pv.id_requisicion_pv")
            ->join("persona", "requisicion_pv.id_persona_solicita", "persona.cve_persona")
            ->leftJoin("colaborador", "persona.cve_persona", "colaborador.cve_persona")
            ->leftJoin("rh_departamento", "colaborador.id_departamento", "rh_departamento.id_departamento")
            ->leftJoin("area_rh", "rh_departamento.id_departamento", "area_rh.id_departamento")
            ->join("espacio_fisico", "requisicion_producto_pv.id_espacio_fisico", "espacio_fisico.id_espacio_fisico")
            ->join("edificios",  "espacio_fisico.id_edificio", "edificios.cve_edificio")
            ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
            ->join("subcategoria_producto_pv", "producto_pv.id_subcategoria", "subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->join("categoria_producto_pv", "subcategoria_producto_pv.id_categoria_pv", "categoria_producto_pv.id_categoria_pv")
            ->leftJoin("producto_pv AS producto_cambio", "pedido_almacen_revision_cambio_producto_pv.id_producto_pv", "producto_cambio.id_producto_pv")
            ->leftJoin("subcategoria_producto_pv AS subcategoria_cambio", "producto_cambio.id_subcategoria", "subcategoria_cambio.id_subcategoria_producto_pv")
            ->leftJoin("categoria_producto_pv AS categoria_cambio", "subcategoria_cambio.id_categoria_pv", "categoria_cambio.id_categoria_pv")
            ->join("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
            ->select("requisicion_pv.folio", "requisicion_pv.fecha_solicitud", "pedido_almacen_revision_pv.descuento", "almacen_entrada.fecha AS  fecha_ingreso", "pedido_almacen_pv.orden_compra AS factura", "proveedor_pv.nombre_comercial")
            ->selectRaw("IFNULL(colaborador.nomina,'-') AS nomina")
            ->selectRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS solicita")
            ->selectRaw("CONCAT(area_rh.nombre,'/',rh_departamento.nombre) AS area_departamento")
            ->selectRaw("CONCAT(espacio_fisico.nombre,'/',edificios.nombre) AS area_destino")
            ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,pedido_almacen_revision_pv.cantidad) AS cantidad")
            ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.costo,pedido_almacen_revision_pv.costo) AS costo")
            ->selectRaw("IFNULL(producto_cambio.clave,producto_pv.clave) AS producto_clave")
            // ->selectRaw("IFNULL(producto_cambio.nombre,producto_pv.nombre) AS producto_servicio")
            ->selectRaw("IFNULL(categoria_cambio.nombre,categoria_producto_pv.nombre) AS categoria")
            ->selectRaw("IFNULL(subcategoria_cambio.nombre,subcategoria_producto_pv.nombre) AS subcategoria")
            ->selectRaw("IFNULL(producto_cambio.nombre,CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                )) AS producto_servicio");

        if ($p->fecha_inicio ?? false) {
            $query->whereRaw("almacen_entrada.fecha BETWEEN ? AND ?", [$p->fecha_inicio, $p->fecha_fin]);
        }
        if ($p->proveedor ?? false) {
            $query->whereRaw("proveedor_pv.nombre_comercial LIKE ?", ["%$p->proveedor%"]);
        }
        if ($p->categoria ?? false) {
            $query->where("categoria_producto_pv.id_categoria_pv", $p->categoria);
        }
        if ($p->categoria ?? false) {
            $query->where("subcategoria_producto_pv.id_subcategoria_producto_pv", $p->subcategoria);
        }
        if ($p->clave ?? false) {
            $query->whereRaw("IFNULL(producto_cambio.clave,producto_pv.clave)", $p->clave);
        }
        if ($p->solicita ?? false) {
            $query->whereRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE ?", ["%$p->solicita%"]);
        }
        if ($p->area ?? false) {
            $query->where("area_rh.id_area_rh", $p->area);
        }
        if ($p->area_destino ?? false) {
            $query->where("espacio_fisico.id_espacio_fisico", $p->area_destino);
        }

        return $query->get();
    }

    public static function reporteAlmacenRequisicion($p)
    {
    
        /*
                SELECT 
	                requisicion_pv.id_requisicion_pv, 
	                requisicion_pv.folio, 
	                requisicion_pv.fecha_solicitud, 
	                producto_pv.clave, 
	                requisicion_producto_pv.cantidad, 
	                categoria_producto_pv.id_categoria_pv, 
	                categoria_producto_pv.nombre AS categoria, 
	                subcategoria_producto_pv.nombre AS subcategoria, 
	                requisicion_producto_pv.estatus_revision, 
	                requisicion_producto_pv.estatus_confirmacion, 
	                espacio_fisico.id_espacio_fisico, 
	                espacio_fisico.nombre AS area_destino, 
	                rh_departamento.id_departamento, 
	                rh_departamento.nombre AS area_solicitante, 
	                CONCAT_WS(' ',persona_solicita.nombre,persona_solicita.apellido_paterno,persona_solicita.apellido_materno) AS solicita, 
	                CONCAT_WS(' ',persona_revisa.nombre,persona_revisa.apellido_paterno,persona_revisa.apellido_materno) AS revisa, 
	                CONCAT_WS(' ',persona_autoriza.nombre,persona_autoriza.apellido_paterno,persona_autoriza.apellido_materno) AS autorizo, 
	                CONCAT_WS(' ', 
	                	NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
	                	NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
	                	NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''), 
	                	NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
	                	NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''), 
	                	NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''), 
	                	NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')) AS producto
                FROM requisicion_pv
                INNER JOIN requisicion_producto_pv ON requisicion_pv.id_requisicion_pv = requisicion_producto_pv.id_requisicion_pv
                INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv = producto_pv.id_producto_pv
                LEFT JOIN producto_marca_pv ON producto_pv.id_producto_pv=producto_marca_pv.id_producto_pv
                LEFT JOIN marca_productos_pv ON producto_marca_pv.id_marca_productos_pv = marca_productos_pv.id_marca_productos_pv
                INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria = subcategoria_producto_pv.id_subcategoria_producto_pv
                INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv = categoria_producto_pv.id_categoria_pv
                INNER JOIN colaborador AS colaborador_solicita ON requisicion_pv.id_colaborador_solicita=colaborador_solicita.id_colaborador
                INNER JOIN persona AS persona_solicita ON colaborador_solicita.cve_persona = persona_solicita.cve_persona
                INNER JOIN colaborador AS colaborador_revisa ON requisicion_pv.id_colaborador_revisa=colaborador_revisa.id_colaborador
                INNER JOIN persona AS persona_revisa ON colaborador_revisa.cve_persona = persona_revisa.cve_persona
                INNER JOIN colaborador AS colaborador_autoriza ON requisicion_pv.id_colaborador_autorizo=colaborador_autoriza.id_colaborador
                INNER JOIN persona AS persona_autoriza ON colaborador_autoriza.cve_persona = persona_autoriza.cve_persona
                left JOIN espacio_fisico ON requisicion_producto_pv.id_espacio_fisico = espacio_fisico.id_espacio_fisico
                LEFT JOIN area_rh ON colaborador_solicita.id_area=area_rh.id_area_rh
                LEFT JOIN rh_departamento ON area_rh.id_departamento = rh_departamento.id_departamento
                WHERE requisicion_pv.fecha_solicitud BETWEEN '2024-03-01' AND '2024-03-13'
             */

        $query = DB::table("requisicion_pv")
            ->join("requisicion_producto_pv" , "requisicion_pv.id_requisicion_pv" , "requisicion_producto_pv.id_requisicion_pv")
            ->join("producto_pv" , "requisicion_producto_pv.id_producto_pv" , "producto_pv.id_producto_pv")
            ->leftJoin("producto_marca_pv" , "producto_pv.id_producto_pv","producto_marca_pv.id_producto_pv")
            ->leftJoin("marca_productos_pv" , "producto_marca_pv.id_marca_productos_pv" , "marca_productos_pv.id_marca_productos_pv")
            ->join("subcategoria_producto_pv" , "producto_pv.id_subcategoria" , "subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->join("categoria_producto_pv" , "subcategoria_producto_pv.id_categoria_pv" , "categoria_producto_pv.id_categoria_pv")
            ->join("colaborador AS colaborador_solicita" , "requisicion_pv.id_colaborador_solicita","colaborador_solicita.id_colaborador")
            ->join("persona AS persona_solicita" , "colaborador_solicita.cve_persona" , "persona_solicita.cve_persona")
            ->join("colaborador AS colaborador_revisa" , "requisicion_pv.id_colaborador_revisa","colaborador_revisa.id_colaborador")
            ->join("persona AS persona_revisa" , "colaborador_revisa.cve_persona" , "persona_revisa.cve_persona")
            ->join("colaborador AS colaborador_autoriza" , "requisicion_pv.id_colaborador_autorizo","colaborador_autoriza.id_colaborador")
            ->join("persona AS persona_autoriza" , "colaborador_autoriza.cve_persona" , "persona_autoriza.cve_persona")
            ->leftJoin("espacio_fisico" , "requisicion_producto_pv.id_espacio_fisico" , "espacio_fisico.id_espacio_fisico")
            ->leftJoin("area_rh" , "colaborador_solicita.id_area","area_rh.id_area_rh")
            ->leftJoin("rh_departamento" , "area_rh.id_departamento" , "rh_departamento.id_departamento")
            ->select(
                "requisicion_pv.id_requisicion_pv", 
	                "requisicion_pv.folio", 
	                "requisicion_pv.fecha_solicitud", 
	                "producto_pv.clave", 
	                "requisicion_producto_pv.cantidad", 
	                "categoria_producto_pv.id_categoria_pv", 
	                "categoria_producto_pv.nombre AS categoria", 
	                "subcategoria_producto_pv.nombre AS subcategoria", 
	                "requisicion_producto_pv.estatus_revision", 
	                "requisicion_producto_pv.estatus_confirmacion", 
	                "espacio_fisico.id_espacio_fisico", 
	                "espacio_fisico.nombre AS area_destino", 
	                "rh_departamento.id_departamento", 
	                "rh_departamento.nombre AS area_solicitante"
            )
            ->selectRaw("CONCAT_WS(' ',persona_solicita.nombre,persona_solicita.apellido_paterno,persona_solicita.apellido_materno) AS solicita")
            ->selectRaw("CONCAT_WS(' ',persona_revisa.nombre,persona_revisa.apellido_paterno,persona_revisa.apellido_materno) AS revisa")
            ->selectRaw("CONCAT_WS(' ',persona_autoriza.nombre,persona_autoriza.apellido_paterno,persona_autoriza.apellido_materno) AS autorizo")
            ->selectRaw("CONCAT_WS(' ', 
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''), 
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''), 
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''), 
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')) AS producto");

        if ($p->folio ?? false) {
            $query->where("requisicion_pv.folio", $p->folio);
        }
        if ($p->fecha_inicio ?? false) {
            $query->whereRaw("requisicion_pv.fecha_solicitud BETWEEN ? AND ?", [$p->fecha_inicio, $p->fecha_fin]);
        }

        if ($p->area_elabora ?? false) {
            $query->where("rh_departamento.id_departamento", $p->area_elabora);
        }
        if ($p->clave ?? false) {
            $query->where("producto_pv.clave", $p->clave);
        }

        if ($p->producto ?? false) {
            $query->where("producto_pv.id_producto_pv", $p->producto);
        }

        if ($p->categoria ?? false) {
            $query->where("categoria_producto_pv.id_categoria_pv", $p->categoria);
        }

        if ($p->subcategoria ?? false) {
            $query->where("subcategoria_producto_pv.id_subcategoria_producto_pv", $p->subcategoria);
        }
        return $query->get();
    }

    public static function reportePedidoAlmacen($p)
    {
        /*
            SELECT 
	            pedido_almacen_pv.id_pedido_almacen_pv,
	            pedido_almacen_pv.folio,
	            pedido_almacen_pv.id_proveedor,
	            pedido_almacen_pv.fecha_pedido,
	            IFNULL(pedido_almacen_pv.fecha_revision,'') AS fecha_revision,
	            pedido_almacen_pv.estatus,
	            pedido_almacen_producto_pv.estatus,
	            IFNULL(pedido_almacen_revision_pv.cantidad,0) AS cantidad,
	            IFNULL(pedido_almacen_revision_pv.costo,0) AS costo,
	            IFNULL(pedido_almacen_revision_pv.descuento,0) AS descuento,
	            requisicion_producto_pv.cantidad,
	            producto_pv.clave,
	            producto_pv.nombre,
	            producto_pv.tipo,
	            categoria_producto_pv.nombre,
	            subcategoria_producto_pv.nombre,
	            proveedor_pv.nombre_comercial
            FROM pedido_almacen_pv
            INNER JOIN pedido_almacen_producto_pv ON pedido_almacen_pv.id_pedido_almacen_pv=pedido_almacen_producto_pv.id_pedido_almacen_pv
            LEFT JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto
            LEFT JOIN pedido_almacen_revision_cambio_producto_pv ON pedido_almacen_revision_pv.id_pedido_almacen_revision_pv=pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv
            INNER JOIN requisicion_producto_pv ON pedido_almacen_producto_pv.id_requisicion_producto_pv=requisicion_producto_pv.id_requisicion_producto_pv
            INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv=producto_pv.id_producto_pv
            INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria=subcategoria_producto_pv.id_subcategoria_producto_pv
            INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv=categoria_producto_pv.id_categoria_pv
            INNER JOIN proveedor_pv ON pedido_almacen_pv.id_proveedor=proveedor_pv.id_proveedor
            LEFT JOIN producto_pv AS producto_cambio ON pedido_almacen_revision_cambio_producto_pv.id_producto_pv=producto_cambio.id_producto_pv
            LEFT JOIN subcategoria_producto_pv AS subcategoria_cambio_pv ON producto_cambio.id_subcategoria=subcategoria_cambio_pv.id_subcategoria_producto_pv
            LEFT JOIN categoria_producto_pv AS categoria_cambio_pv ON subcategoria_cambio_pv.id_categoria_pv=categoria_cambio_pv.id_categoria_pv
            WHERE pedido_almacen_producto_pv.estatus=1
            ORDER BY pedido_almacen_pv.id_pedido_almacen_pv
         */

        $query = DB::table("pedido_almacen_pv")
            ->join("pedido_almacen_producto_pv", "pedido_almacen_pv.id_pedido_almacen_pv", "pedido_almacen_producto_pv.id_pedido_almacen_pv")
            ->leftJoin("pedido_almacen_revision_pv", "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_producto")
            ->leftJoin("pedido_almacen_revision_cambio_producto_pv", "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv", "pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
            ->join("requisicion_producto_pv", "pedido_almacen_producto_pv.id_requisicion_producto_pv", "requisicion_producto_pv.id_requisicion_producto_pv")
            ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
            ->join("subcategoria_producto_pv", "producto_pv.id_subcategoria", "subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->join("categoria_producto_pv", "subcategoria_producto_pv.id_categoria_pv", "categoria_producto_pv.id_categoria_pv")
            ->join("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
            ->leftJoin("proveedor_pv AS proveedor_cambio", "pedido_almacen_producto_pv.id_proveedor", "proveedor_cambio.id_proveedor")
            ->leftJoin("producto_pv AS producto_cambio", "pedido_almacen_revision_cambio_producto_pv.id_producto_pv", "producto_cambio.id_producto_pv")
            ->leftJoin("subcategoria_producto_pv AS subcategoria_cambio_pv", "producto_cambio.id_subcategoria", "subcategoria_cambio_pv.id_subcategoria_producto_pv")
            ->leftJoin("categoria_producto_pv AS categoria_cambio_pv", "subcategoria_cambio_pv.id_categoria_pv", "categoria_cambio_pv.id_categoria_pv")
            ->select(
                "pedido_almacen_pv.id_pedido_almacen_pv",
                "pedido_almacen_pv.folio",
                "pedido_almacen_pv.id_proveedor",
                "pedido_almacen_pv.fecha_pedido",
                "pedido_almacen_pv.estatus",
                "pedido_almacen_producto_pv.estatus AS estatus_producto",
                "requisicion_producto_pv.cantidad AS cantidad_solicitada",
                //"producto_pv.clave",
                //"producto_pv.nombre AS producto_name",
                //"producto_pv.tipo",
                //"categoria_producto_pv.nombre AS categoria",
                //"subcategoria_producto_pv.nombre AS subcategoria",
                // "proveedor_pv.nombre_comercial"
            )
            ->selectRaw("IF(producto_cambio.clave IS NULL,producto_pv.clave,CONCAT(producto_pv.clave,'|',producto_cambio.clave)) AS clave")
            // ->selectRaw("IF(producto_cambio.nombre IS NULL,producto_pv.nombre,CONCAT(producto_pv.nombre,'|',producto_cambio.nombre)) AS producto_name")
            ->selectRaw("IF(producto_cambio.tipo IS NULL,producto_pv.tipo,CONCAT(producto_pv.tipo,'|',producto_cambio.tipo)) AS tipo")
            ->selectRaw("IF(categoria_cambio_pv.nombre IS NULL, categoria_producto_pv.nombre,CONCAT( categoria_producto_pv.nombre,'|',categoria_cambio_pv.nombre)) AS categoria")
            ->selectRaw("IF(subcategoria_cambio_pv.nombre IS NULL, subcategoria_producto_pv.nombre,CONCAT(subcategoria_producto_pv.nombre,'|',subcategoria_cambio_pv.nombre)) AS subcategoria")
            ->selectRaw("IFNULL(pedido_almacen_pv.fecha_revision,'') AS fecha_revision")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.cantidad,0) AS cantidad_entregada")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.costo,0) AS costo")
            ->selectRaw("IFNULL(pedido_almacen_revision_pv.descuento,0) AS descuento")
            ->selectRaw("IF(pedido_almacen_revision_cambio_producto_pv.id_producto_pv IS NULL,0,1) cambio")
            ->selectRaw("IFNULL(categoria_cambio_pv.id_categoria_pv, categoria_producto_pv.id_categoria_pv) AS id_categoria_pv")
            ->selectRaw("IFNULL( pedido_almacen_revision_pv.id_pedido_almacen_revision_pv, 0) AS id_pedido_almacen_revision_pv")
            ->selectRaw("IF(proveedor_cambio.nombre_comercial IS NULL, proveedor_pv.nombre_comercial,CONCAT(proveedor_pv.nombre_comercial,' / ',proveedor_cambio.nombre_comercial)) AS nombre_comercial")
            ->selectRaw("IF(producto_cambio.nombre IS NULL,CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
            ),CONCAT(CONCAT_WS(' ',
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
        ),'|',producto_cambio.nombre)) AS producto_name")
            ->where("pedido_almacen_producto_pv.estatus", 1)
            ->orderBy("pedido_almacen_pv.id_pedido_almacen_pv");

        if ($p->folio ?? false) {
            $query->where("pedido_almacen_pv.folio", $p->folio);
        }

        if ($p->fecha_inicio ?? false) {
            $query->whereRaw("pedido_almacen_pv.fecha_pedido BETWEEN ? AND ?", [$p->fecha_inicio, $p->fecha_fin]);
        }
        if ($p->proveedor ?? false) {
            $query->where("proveedor_pv.id_proveedor", $p->proveedor);
        }
        if ($p->categoria ?? false) {
            $query->whereRaw("IFNULL(categoria_cambio_pv.id_categoria_pv,categoria_producto_pv.id_categoria_pv) = ?", [$p->categoria]);
        }
        if ($p->subcategoria ?? false) {
            $query->whereRaw("IFNULL(subcategoria_cambio_pv.id_subcategoria_producto_pv,subcategoria_producto_pv.id_subcategoria_producto_pv) = ?", [$p->subcategoria]);
        }
        if ($p->clave ?? false) {
            $query->whereRaw("IFNULL(producto_cambio.clave,producto_pv.clave)=?", [$p->clave]);
        }
        if ($p->solicita ?? false) {
            $query->whereRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE ?", ["%$p->solicita%"]);
        }

        return $query->get();
    }

    public static function reporteAlmacenSalida($p)
    {
        
        /*
                SELECT 
                        almacen_salida.id_almacen_salida,
                        almacen_salida.fecha_salida,
                        almacen_salida.id_persona_recibe,
                        IFNULL(persona.nombre,'') AS nombre,
                        IFNULL(persona.apellido_paterno,'') AS apellido_paterno,
                        IFNULL(persona.apellido_materno,'') AS apellido_materno,
                        producto_pv.clave,
                        producto_pv.nombre AS producto_name,
                        categoria_producto_pv.nombre AS categoria_name,
                        subcategoria_producto_pv.nombre AS subcategoria_name,
                        almacen_salida.cantidad,
                        almacen_salida.piezas,
                        almacen_salida.id_espacio_fisico,
                        IFNULL(espacio_fisico.nombre,'') AS espacio_fisico_name
                FROM almacen_salida
                INNER JOIN  almacen_entrada ON almacen_salida.id_almacen_entrada=almacen_entrada.id_almacen_entrada
                INNER JOIN producto_pv ON almacen_entrada.id_producto=producto_pv.id_producto_pv
                LEFT JOIN persona ON almacen_salida.id_persona_recibe=persona.cve_persona
                LEFT JOIN espacio_fisico ON almacen_salida.id_espacio_fisico=espacio_fisico.id_espacio_fisico
                LEFT JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria=subcategoria_producto_pv.id_subcategoria_producto_pv
                LEFT JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv=categoria_producto_pv.id_categoria_pv
             */

        $query = DB::table("almacen_salida")
            ->join("almacen_entrada" , "almacen_salida.id_almacen_entrada","almacen_entrada.id_almacen_entrada")
            ->join("producto_pv" , "almacen_entrada.id_producto","producto_pv.id_producto_pv")
            ->leftJoin("persona" , "almacen_salida.id_persona_recibe","persona.cve_persona")
            ->leftJoin("espacio_fisico" , "almacen_salida.id_espacio_fisico","espacio_fisico.id_espacio_fisico")
            ->leftJoin("subcategoria_producto_pv" , "producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
            ->leftJoin("categoria_producto_pv" , "subcategoria_producto_pv.id_categoria_pv","categoria_producto_pv.id_categoria_pv")
            ->select(
                    "almacen_salida.id_almacen_salida",
                    "almacen_salida.fecha_salida",
                    "almacen_salida.id_persona_recibe",
                    "producto_pv.clave",
                    // "producto_pv.nombre AS producto_name",
                    "almacen_salida.cantidad",
                    "almacen_salida.piezas",
                    "almacen_salida.id_espacio_fisico",
                    "categoria_producto_pv.nombre AS categoria_name",
                    "subcategoria_producto_pv.nombre AS subcategoria_name",
            )
            ->selectRaw("IFNULL(persona.nombre,'') AS nombre")
            ->selectRaw("IFNULL(persona.apellido_paterno,'') AS apellido_paterno")
            ->selectRaw("IFNULL(persona.apellido_materno,'') AS apellido_materno")
            ->selectRaw("IFNULL(espacio_fisico.nombre,'') AS espacio_fisico_name")
            ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS producto_name");

       
        if ($p->fecha_inicio ?? false) {
            $query->whereRaw("almacen_salida.fecha_salida BETWEEN ? AND ?", [$p->fecha_inicio, $p->fecha_fin]);
        }

        if ($p->area_elabora ?? false) {
            $query->where("rh_departamento.id_departamento", $p->area_elabora);
        }
        if ($p->clave ?? false) {
            $query->where("producto_pv.clave", $p->clave);
        }

        if ($p->producto ?? false) {
            $query->where("producto_pv.id_producto_pv", $p->producto);
        }

        if ($p->categoria ?? false) {
            $query->where("categoria_producto_pv.id_categoria_pv", $p->categoria);
        }

        if ($p->subcategoria ?? false) {
            $query->where("subcategoria_producto_pv.id_subcategoria_producto_pv", $p->subcategoria);
        }
        return $query->get();
    }


}
