<?php

namespace App\DAO;

use App\Entity\producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EspacioFisicoProductoDAO
{

    public function __construct()
    {
    }
    /**
     * 
     */
    public static function getProductosDisponibles($id_pedido)
    {


        try {

           DB::table("orden_compra_pv")
           ->join("pedido_almacen_producto_pv" , "orden_compra_pv.id_pedido_almacen_pv","pedido_almacen_producto_pv.id_pedido_almacen_pv")
           ->join("pedido_almacen_revision_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")
           ->leftJoin("pedido_almacen_revision_cambio_producto_pv" ,  "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv","pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
           ->join("requisicion_producto_pv" , "pedido_almacen_producto_pv.id_requisicion_producto_pv","requisicion_producto_pv.id_requisicion_producto_pv")
           ->join("producto_pv" , "requisicion_producto_pv.id_producto_pv","producto_pv.id_producto_pv")
           ->leftJoin("producto_pv AS producto_cambio" , "pedido_almacen_revision_cambio_producto_pv.id_producto_pv","producto_cambio.id_producto_pv")
           ->leftJoin("espacio_fisico_producto" , "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv","espacio_fisico_producto.id_pedido_almacen_revision_pv")
           ->whereNull("espacio_fisico_producto.id_espacio_fisico_producto")
           ->select("pedido_almacen_revision_pv.id_pedido_almacen_revision_pv")
           ->selectRaw("IFNULL(producto_cambio.nombre,producto_pv.nombre) AS producto_name")
           ->get();
           
        } catch (\Exception $e) {

            return $e;
        }
    }



    public static function getProductosActivosAsignados()
    {
        return DB::table("pedido_almacen_pv")
            ->join("proveedor_pv", "pedido_almacen_pv.id_proveedor", "proveedor_pv.id_proveedor")
            ->where("pedido_almacen_pv.estatus", 2)
            ->select("pedido_almacen_pv.id_pedido_almacen_pv", "nombre_comercial", "folio", "fecha_pedido", "fecha_revision", "total")
            ->get();
    }

    public static function saveAsignacionProductoEspacioFisico($p)
    {
        return DB::table("espacio_fisico_producto")
            ->insertGetId([
                "id_espacio_fisico" => $p->id_pedido_almacen_pv,
                "id_pedido_almacen_revision_pv" => $p->observaciones,
                "estatus" => $p->elaboro                
            ]);
    }
}
