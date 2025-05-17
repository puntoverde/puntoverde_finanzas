<?php

namespace App\DAO;

use App\Entity\producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProveedorDAO
{

    public function __construct()
    {
    }
    /**
     * metodos
     */
    public static function createProveedor($p)
    {
        return DB::table("proveedor_pv")->insertGetId([
            "codigo" => $p->codigo,
            "nombre_comercial" => $p->nombre_comercial,
            "rfc" => $p->rfc,
            "razon_social" => $p->razon_social,
            "moneda_proveedor" => $p->moneda_proveedor ?? '',
            "telefono_proveedor" => $p->telefono_proveedor ?? '',
            "calle" => $p->calle ?? '',
            "n_ext" => $p->n_ext ?? '',
            "n_int" => $p->n_int ?? '',
            "cp" => $p->cp ?? '',
            "id_colonia" => $p->id_colonia ?? 0,
            "persona_contacto" => $p->persona_contacto ?? '',
            "correo_contacto" => $p->correo_contacto ?? '',
            "whatsapp_contacto" => $p->whatsapp_contacto ?? '',
            "estatus" => 1
        ]);
    }

    public static function updateProveedor($id, $p)
    {
        return DB::table("proveedor_pv")->where("id_proveedor", $id)
            ->update([
            "codigo" => $p->codigo,
            "nombre_comercial" => $p->nombre_comercial,
            "rfc" => $p->rfc,
            "razon_social" => $p->razon_social,
            "moneda_proveedor" => $p->moneda_proveedor ?? '',
            "telefono_proveedor" => $p->telefono_proveedor ?? '',
            "calle" => $p->calle ?? '',
            "n_ext" => $p->n_ext ?? '',
            "n_int" => $p->n_int ?? '',
            "cp" => $p->cp ?? '',
            "id_colonia" => $p->id_colonia ?? 0,
            "persona_contacto" => $p->persona_contacto ?? '',
            "correo_contacto" => $p->correo_contacto ?? '',
            "whatsapp_contacto" => $p->whatsapp_contacto ?? ''
            ]);
    }

    public static function getProveedores($p)
    {

        try {

            $query = DB::table("proveedor_pv")
                ->select(
                "proveedor_pv.id_proveedor",
                "proveedor_pv.codigo",
                "proveedor_pv.nombre_comercial",
                "proveedor_pv.razon_social",
                "proveedor_pv.rfc",
                "proveedor_pv.moneda_proveedor",
                "proveedor_pv.telefono_proveedor",
                "proveedor_pv.calle",
                "proveedor_pv.n_ext",
                "proveedor_pv.n_int",
                "proveedor_pv.cp",
                "proveedor_pv.id_colonia",
                "proveedor_pv.persona_contacto",
                "proveedor_pv.correo_contacto",
                "proveedor_pv.whatsapp_contacto",
                "proveedor_pv.estatus", "colonia.nombre AS colonia_name", "municipio.nombre AS municipio_name", "estado.nombre AS estado_name")
                ->leftJoin("colonia", "proveedor_pv.id_colonia", "colonia.cve_colonia")
                ->leftJoin("municipio", "colonia.cve_municipio", "municipio.cve_municipio")
                ->leftJoin("estado", "municipio.cve_estado", "estado.cve_estado");

            if ($p->codigo ?? false) {
                $query->where("proveedor_pv.codigo", $p->codigo);
            }

            if ($p->nombre_comercial ?? false) {
                $query->where("proveedor_pv.nombre_comercial", $p->nombre_comercial);
            }

            if ($p->razon_social ?? false) {
                $query->where("proveedor_pv.razon_social", $p->razon_social);
            }

            if ($p->rfc ?? false) {
                $query->where("proveedor_pv.rfc", $p->rfc);
            }

            return $query->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getProveedorById($id)
    {

        try {

            $query = DB::table("proveedor_pv")
                ->select(
                "proveedor_pv.id_proveedor",
                "proveedor_pv.codigo",
                "proveedor_pv.nombre_comercial",
                "proveedor_pv.razon_social",
                "proveedor_pv.rfc",
                "proveedor_pv.moneda_proveedor",
                "proveedor_pv.telefono_proveedor",
                "proveedor_pv.calle",
                "proveedor_pv.n_ext",
                "proveedor_pv.n_int",
                "proveedor_pv.cp",
                "proveedor_pv.id_colonia",
                "proveedor_pv.persona_contacto",
                "proveedor_pv.correo_contacto",
                "proveedor_pv.whatsapp_contacto",
                "proveedor_pv.estatus",
                "colonia.nombre AS colonia_name", "municipio.nombre AS municipio_name", "estado.nombre AS estado_name")
                ->leftJoin("colonia", "proveedor_pv.id_colonia", "colonia.cve_colonia")
                ->leftJoin("municipio", "colonia.cve_municipio", "municipio.cve_municipio")
                ->leftJoin("estado", "municipio.cve_estado", "estado.cve_estado")
                ->where("proveedor_pv.id_proveedor",$id);

            return $query->first();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getProveedorByParameters($param)
    {


        try {
            $query = DB::table("proveedor_pv")->select(
                "proveedor_pv.id_proveedor",
                "proveedor_pv.codigo",
                "proveedor_pv.nombre_comercial"
            );

            $query->whereRaw("LOWER(proveedor_pv.nombre_comercial) LIKE ?", ["%" . strtolower($param) . "%"]);

            // var_dump($query->toSql());

            return $query->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getCategoriasProvedor($id_proveedor)
    {

        try {

            return DB::table("categoria_producto_pv")
                ->leftJoin("proveedor_categoria", function ($join) use ($id_proveedor) {
                    $join->on("categoria_producto_pv.id_categoria_pv", "proveedor_categoria.id_categoria_producto")
                        ->where("proveedor_categoria.id_proveedor", $id_proveedor);
                })
                ->select(
                    "categoria_producto_pv.id_categoria_pv",
                    "categoria_producto_pv.nombre",
                    "categoria_producto_pv.descripcion",
                    DB::raw("IFNULL(proveedor_categoria.id_proveedor_categoria,0) AS agregado")
                )
                ->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getProductoCategoriaProveedor($id_proveedor, $id_categoria)
    {

        try {

            // return DB::table("producto_pv")
            // ->join("subcategoria_producto_pv","producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
            // ->join("categoria_producto_pv","subcategoria_producto_pv.id_categoria_pv","categoria_producto_pv.id_categoria_pv")        
            // ->join("proveedor_categoria",function($join) use($id_proveedor){
            //     $join->on("subcategoria_producto_pv.id_categoria_pv","proveedor_categoria.id_categoria_producto")
            //     ->where("proveedor_categoria.id_proveedor",$id_proveedor);
            // })
            // ->leftJoin("proveedor_producto",function($join) use($id_proveedor){
            //     $join->on("producto_pv.id_producto_pv","proveedor_producto.id_producto_pv")
            //     ->where("proveedor_producto.id_proveedor",$id_proveedor);
            // })
            // ->select(
            //     "categoria_producto_pv.id_categoria_pv",
            //     "categoria_producto_pv.nombre",
            //     "producto_pv.id_producto_pv",
            //     "producto_pv.clave",
            //     "producto_pv.nombre",
            //     "producto_pv.descripcion",
            //     DB::raw("IFNULL(proveedor_producto.id_proveedor_producto,0) AS agregado"))
            // ->toSql();   

            //             SELECT producto_pv.id_producto_pv,producto_pv.nombre,producto_pv.clave,subcategoria_producto_pv.nombre AS subcategoria from subcategoria_producto_pv
            // INNER JOIN producto_pv ON subcategoria_producto_pv.id_subcategoria_producto_pv=producto_pv.id_subcategoria
            // WHERE subcategoria_producto_pv.id_categoria_pv=2

            return DB::table("subcategoria_producto_pv")
                ->join("producto_pv", "subcategoria_producto_pv.id_subcategoria_producto_pv", "producto_pv.id_subcategoria")
                ->leftJoin("proveedor_producto", function ($join) use ($id_proveedor) {
                    $join->on("producto_pv.id_producto_pv", "proveedor_producto.id_producto_pv")->where("proveedor_producto.id_proveedor", $id_proveedor);
                })
                ->where("subcategoria_producto_pv.id_categoria_pv", $id_categoria)
                ->select(
                    "producto_pv.id_producto_pv",
                    // "producto_pv.nombre",
                    "producto_pv.clave",
                    "subcategoria_producto_pv.nombre AS subcategoria",
                    "producto_pv.descripcion"
                )
                ->selectRaw("IFNULL(proveedor_producto.id_proveedor_producto,0) AS agregado")
                ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre")
                ->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function addCategoria($id, $data)
    {
        try{
        return DB::transaction(function () use ($id, $data) {

            $data_insert = collect($data)->map(function ($item, $index) use ($id) {
                return ["id_proveedor" => $id, "id_categoria_producto" => $item];
            });

            DB::table("proveedor_categoria")->where("id_proveedor", $id)->delete();

            DB::table("proveedor_categoria")->insert($data_insert->toArray());
            return 1;
        });
    }
    catch(\Exception $e)
    {
        return 0;
    }
    }

    public static function addProductos($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {

                $data_insert = collect($data)->map(function ($item, $index) use ($id) {
                    return ["id_proveedor" => $id, "id_producto_pv" => $item];
                });

                DB::table("proveedor_producto")->where("id_proveedor", $id)->delete();

                DB::table("proveedor_producto")->insert($data_insert->toArray());
                return 1;
            });
        } catch (\Exception $e) {
            return 0;
        }
    }
}
