<?php

namespace App\DAO;

use App\Entity\producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;

class AlmacenEntradasDAO
{

    public function __construct()
    {
    }
    /**
     * 
     * sonlos pedidos que aun no han ingresado al almacen...
     */
    public static function getPedidosRalizadosSinAlmacen()
    {
        try {
           return DB::table("pedido_almacen_pv")
           ->join("proveedor_pv","pedido_almacen_pv.id_proveedor","proveedor_pv.id_proveedor")
           ->select("pedido_almacen_pv.id_pedido_almacen_pv","pedido_almacen_pv.folio","pedido_almacen_pv.fecha_pedido","pedido_almacen_pv.fecha_revision","proveedor_pv.nombre_comercial")
           ->where("pedido_almacen_pv.estatus",2)
           ->get();           
        } catch (\Exception $e) {
            return $e;
        }
    }



    public static function getProductosAlmacenById($id_producto)
    {
        /*SELECT 
                producto_pv.id_producto_pv,
                almacen_entrada.cantidad AS cantidad_total,
                ROUND(ifnull(SUM(almacen_salida.cantidad),0),4) AS cantidad_salida,
                ROUND((almacen_entrada.cantidad)-IFNULL(SUM(almacen_salida.cantidad),0),4) AS cantidad_restante,
                IF(producto_pv.piezas_contenido=0,1,producto_pv.piezas_contenido)*almacen_entrada.cantidad AS piezas_totales,
                ROUND(ifnull(SUM(almacen_salida.piezas),0),4) AS piezas_salida,
                IF(producto_pv.piezas_contenido=0,1,producto_pv.piezas_contenido)*almacen_entrada.cantidad - IFNULL(SUM(almacen_salida.piezas),0) AS piezas_restante
                FROM almacen_entrada 
                INNER JOIN producto_pv ON almacen_entrada.id_producto=producto_pv.id_producto_pv
                INNER JOIN unidad_medido_producto_pv ON producto_pv.id_unidad_medida_compra=unidad_medido_producto_pv.id_unidad_medida_producto_pv
                LEFT JOIN almacen_salida ON almacen_entrada.id_almacen_entrada=almacen_salida.id_almacen_entrada
                WHERE producto_pv.id_producto_pv=3
                GROUP BY almacen_entrada.id_almacen_entrada
                HAVING cantidad_restante>0 and piezas_restante>0*/
                ;
        return DB::table("almacen_entrada")
            ->join("producto_pv" , "almacen_entrada.id_producto","producto_pv.id_producto_pv")
            ->join("unidad_medido_producto_pv" , "producto_pv.id_unidad_medida_compra","unidad_medido_producto_pv.id_unidad_medida_producto_pv")
            ->leftJoin("almacen_salida" ,"almacen_entrada.id_almacen_entrada","almacen_salida.id_almacen_entrada")
            ->where("producto_pv.id_producto_pv", $id_producto)
            ->select("almacen_entrada.id_almacen_entrada","producto_pv.id_producto_pv","almacen_entrada.cantidad AS cantidad_total")
            ->selectRaw("ROUND(ifnull(SUM(almacen_salida.cantidad),0),4) AS cantidad_salida")
            ->selectRaw("ROUND((almacen_entrada.cantidad)-IFNULL(SUM(almacen_salida.cantidad),0),4) AS cantidad_restante")
            ->selectRaw("IF(producto_pv.piezas_contenido=0,1,producto_pv.piezas_contenido)*almacen_entrada.cantidad AS piezas_totales")
            ->selectRaw("ROUND(ifnull(SUM(almacen_salida.piezas),0),4) AS piezas_salida")
            ->selectRaw("IF(producto_pv.piezas_contenido=0,1,producto_pv.piezas_contenido)*almacen_entrada.cantidad - IFNULL(SUM(almacen_salida.piezas),0) AS piezas_restante")
            ->selectRaw("IF(producto_pv.piezas_contenido=0,1,producto_pv.piezas_contenido) AS piezas_contenido")
            ->groupBy(["almacen_entrada.id_almacen_entrada"])
            ->having("cantidad_restante",">",0)->having("piezas_restante",">",0)
            ->get();
    }

    public static function saveEntradaAlmacenPedido($id_pedido)
    {
       DB::transaction(function()use($id_pedido){

        //se buscan todos los productos ya revisados y aceptados del pedido y que no esten en almacen
        $productos=DB::table("pedido_almacen_producto_pv")
        ->join("requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv","requisicion_producto_pv.id_requisicion_producto_pv")
        ->join("pedido_almacen_revision_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")
        ->leftJoin("pedido_almacen_revision_cambio_producto_pv" , "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv","pedido_almacen_revision_cambio_producto_pv.id_pedido_almacen_revision_pv")
        ->leftJoin("almacen_entrada" , "pedido_almacen_revision_pv.id_pedido_almacen_revision_pv","almacen_entrada.id_pedido_almacen_revision")
        ->join("producto_pv" , "requisicion_producto_pv.id_producto_pv","producto_pv.id_producto_pv")
        ->leftJoin("producto_pv AS producto_cambio" , "pedido_almacen_revision_cambio_producto_pv.id_producto_pv","producto_cambio.id_producto_pv")
        ->join("unidad_medido_producto_pv AS unidad_medida_compra" , "producto_pv.id_unidad_medida_compra","unidad_medida_compra.id_unidad_medida_producto_pv")
        ->join("unidad_medido_producto_pv AS unidad_medida_producto" , "producto_pv.id_unidad_medida_producto","unidad_medida_producto.id_unidad_medida_producto_pv")
        ->leftJoin("unidad_medido_producto_pv AS unidad_medida_compra_cambio" , "producto_cambio.id_unidad_medida_compra","unidad_medida_compra_cambio.id_unidad_medida_producto_pv")
        ->leftJoin("unidad_medido_producto_pv AS unidad_medida_producto_cambio" , "producto_cambio.id_unidad_medida_producto","unidad_medida_producto_cambio.id_unidad_medida_producto_pv")
        ->where("pedido_almacen_producto_pv.id_pedido_almacen_pv",$id_pedido)
        ->whereNull("almacen_entrada.id_almacen_entrada")
        ->select("pedido_almacen_revision_pv.id_pedido_almacen_revision_pv AS id_pedido_almacen_revision")
        ->selectRaw("IFNULL(producto_cambio.id_producto_pv,producto_pv.id_producto_pv) AS id_producto")
        ->selectRaw("IFNULL(producto_cambio.nombre,producto_pv.nombre) AS producto_nombre")
        ->selectRaw("IFNULL(producto_cambio.id_unidad_medida_compra,producto_pv.id_unidad_medida_compra) AS id_unidad_compra")
        ->selectRaw("IFNULL(unidad_medida_compra_cambio.nombre,unidad_medida_compra.nombre) AS unidad_compra")
        ->selectRaw("ifnull(unidad_medida_compra_cambio.categoria,unidad_medida_compra.categoria) AS categoria_compra")
        ->selectRaw("IFNULL(producto_cambio.id_unidad_medida_producto,producto_pv.id_unidad_medida_producto) AS id_unidad_producto")
        ->selectRaw("IFNULL(unidad_medida_producto_cambio.nombre,unidad_medida_producto.nombre) AS unidad_producto")
        ->selectRaw("ifnull(unidad_medida_producto_cambio.categoria,unidad_medida_producto.categoria) AS categoria_producto")
        ->selectRaw("IFNULL(producto_cambio.piezas_contenido,producto_pv.piezas_contenido) AS piezas_contenido")
        ->selectRaw("IFNULL(producto_cambio.tamano,producto_pv.tamano) AS tamano")
        ->selectRaw("IFNULL(pedido_almacen_revision_cambio_producto_pv.cantidad,pedido_almacen_revision_pv.cantidad) AS cantidad")
        ->get();


        if($productos->count()>0)
        {

            //obtiene los id de los productosque se van a ingresar
            $only_id_producto=$productos->unique("id_producto")->map(function($i){return $i->id_producto;})->toArray();
            //se obtiene los productos que ya existen en bd
            $ya_existen=DB::table("almacen_producto_pv")->whereIn("id_producto_pv",$only_id_producto)->get("id_producto_pv")->map(function($i){return $i->id_producto_pv;});
            //quita de los id productos 
            $no_existe=collect($only_id_producto)->diff($ya_existen)->map(function($i){return ["id_almacen_pv"=>1,"id_producto_pv"=>$i];})->toArray();

            //ingresa a almacen la relacion con el producto o productos que aun no estan ligados
            DB::table("almacen_producto_pv")->insert($no_existe);        

            //se mapea para que conincidan con las columnas de la tabla de entradas
            $productos_=$productos->map(function($item){
                return[
                "id_pedido_almacen_revision"=>$item->id_pedido_almacen_revision,
                "id_producto"=>$item->id_producto,
                "cantidad"=>$item->cantidad,
                "fecha"=>Carbon::now()->format("Y-m-d"),
                "categoria"=>$item->categoria_compra,
                "piezas_contenido"=>$item->piezas_contenido
                ];});     
                
                
            
            //son los productos que solo se pidio una unidad
            $productos_unidad=$productos_->where("cantidad",1)->map(function($i){                
                return [
                    "id_pedido_almacen_revision"=>$i["id_pedido_almacen_revision"],
                    "id_producto"=>$i["id_producto"],
                    "cantidad"=>$i["cantidad"],
                    "fecha"=>$i["fecha"]
                ];
            });
          
            //son los productos que se piden mas de una unidad pero su numero de piezas es 0 o mayor a 1 
            $productos_not_mas_unidad=$productos_->where("cantidad",">",1)->where("piezas_contenido","!=",1)->map(function($i){                
                return [
                    "id_pedido_almacen_revision"=>$i["id_pedido_almacen_revision"],
                    "id_producto"=>$i["id_producto"],
                    "cantidad"=>$i["cantidad"],
                    "fecha"=>$i["fecha"]
                ];
            });;
            //son los productos que se tienen mas de una unidad
            $productos_mas_unidad=$productos_->where("cantidad",">",1)->where("categoria","unidad")->where("piezas_contenido",1);
             
            //se crea coleccion que despues se llenara segun las veces que se aya un producto o mas
            $collection_mas_productos=collect();
            
            $productos_mas_unidad->each(function($item)use($collection_mas_productos){                   
                for($i=0;$i<$item["cantidad"];$i++)
                {
                    $item_temp=[
                        "id_pedido_almacen_revision"=>$item["id_pedido_almacen_revision"],
                        "id_producto"=>$item["id_producto"],
                        "cantidad"=>1,
                        "fecha"=>Carbon::now()->format("Y-m-d"),
                    ];
                    $collection_mas_productos->push($item_temp);
                }
            });          
            
           //se concatenan el array de una unidad con la nueva que salio de n productos(cantidad)
           $productos_entrada_almacen=$productos_unidad->concat($productos_not_mas_unidad)->concat($collection_mas_productos)->toArray();                   

           //se guardan los productos
           $flag_prod=DB::table("almacen_entrada")->insert($productos_entrada_almacen);

           //se acytualiza el pedido a estatus 3 que es que ya estan en almacen los productos
           if($flag_prod){
               DB::table("pedido_almacen_pv")->where("id_pedido_almacen_pv",$id_pedido)->update(["estatus"=>3]);
           }

        }

       });
    }


    public static function getProductosAlmacen($p)
    {
        /*      SELECT 
                producto_pv.id_producto_pv,
                producto_pv.clave,
                producto_pv.nombre,
                producto_pv.descripcion,
                categoria_producto_pv.nombre AS categoria,
                subcategoria_producto_pv.nombre AS subcategoria,
                marca_productos_pv.nombre AS marca
                FROM almacen_producto_pv
                INNER JOIN producto_pv ON almacen_producto_pv.id_producto_pv=producto_pv.id_producto_pv
                INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria=subcategoria_producto_pv.id_subcategoria_producto_pv
                INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv=categoria_producto_pv.id_categoria_pv
                INNER JOIN marca_productos_pv ON producto_pv.id_marca=marca_productos_pv.id_marca_productos_pv
                WHERE LOWER(producto_pv.nombre) LIKE '%bere%' AND LOWER(producto_pv.clave) LIKE '%sku%' AND categoria_producto_pv.id_categoria_pv=2 AND producto_pv.id_subcategoria=9 AND producto_pv.id_marca=7
         */

         $query=DB::table("almacen_producto_pv")
         ->join("producto_pv" , "almacen_producto_pv.id_producto_pv","producto_pv.id_producto_pv")
         ->join("subcategoria_producto_pv" ,"producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
         ->join("categoria_producto_pv" , "subcategoria_producto_pv.id_categoria_pv","categoria_producto_pv.id_categoria_pv")
         ->join("marca_productos_pv" , "producto_pv.id_marca","marca_productos_pv.id_marca_productos_pv")
         ->select(
                "producto_pv.id_producto_pv",
                "producto_pv.clave",
                "producto_pv.nombre",
                "producto_pv.descripcion",
                "categoria_producto_pv.nombre AS categoria",
                "subcategoria_producto_pv.nombre AS subcategoria",
                "marca_productos_pv.nombre AS marca"
         );
         

         if($p->clave??false)
         {
            $query->whereRaw("LOWER(producto_pv.clave) LIKE", ["'%".$p->clave."%'"]);
         }

         if($p->nombre??false)
         {
            $query->whereRaw("LOWER(producto_pv.nombre) LIKE", ["'%".$p->nombre."%'"]);
         }

         if($p->categoria??false)
         {
            $query->where("categoria_producto_pv.id_categoria_pv",$p->categoria);
         }

         if($p->subcategoria??false)
         {
            $query->where("producto_pv.id_subcategoria",$p->subcategoria);
         }

         if($p->marca??false)
         {
            $query->where("producto_pv.id_marca",$p->marca);
         }

         return $query->get();
    }


    public static function saveSalidaAlmacen($id_almacen_entrada,$p)
    {

        /*         
            SELECT almacen_entrada.cantidad-SUM(ifnull(almacen_salida.cantidad,0)) AS cantidad_stok,producto_pv.piezas_contenido-SUM(ifnull(almacen_salida.piezas,0)) AS piezas_stock 
            FROM almacen_entrada 
            INNER JOIN producto_pv ON almacen_entrada.id_producto=producto_pv.id_producto_pv
            left JOIN almacen_salida ON almacen_entrada.id_almacen_entrada=almacen_salida.id_almacen_entrada
            WHERE almacen_entrada.id_almacen_entrada=23
            GROUP BY almacen_entrada.id_almacen_entrada;
        */

        $stock_existencia=DB::table("almacen_entrada")
        ->join("producto_pv","almacen_entrada.id_producto","producto_pv.id_producto_pv")
        ->leftJoin("almacen_salida","almacen_entrada.id_almacen_entrada","almacen_salida.id_almacen_entrada")
        ->where("almacen_entrada.id_almacen_entrada",$id_almacen_entrada)
        ->groupBy("almacen_entrada.id_almacen_entrada")
        ->selectRaw("IF(producto_pv.piezas_contenido=0,1,producto_pv.piezas_contenido) AS piezas_contenido")
        ->selectRaw("almacen_entrada.cantidad-ROUND(SUM(IFNULL(almacen_salida.cantidad,0)),2) AS cantidad_stok")
        ->selectRaw("(almacen_entrada.cantidad*producto_pv.piezas_contenido)-SUM(IFNULL(almacen_salida.piezas,0)) AS piezas_stock")
        ->first();

        

        if($stock_existencia)
        {
            $flag=false;
            
            $cantidad_salida=0;
            $piezas_salida=0;

            if(($p->cantidad??false)==true &&  ($p->cantidad<=$stock_existencia->cantidad_stok)){                                
               $cantidad_salida=$p->cantidad;
               $piezas_salida=$p->cantidad*$stock_existencia->piezas_contenido;
               $flag=true;
            }

            else if(($p->piezas??false)==true && ($p->piezas<=$stock_existencia->piezas_stock)){
                $cantidad_salida=$p->piezas/$stock_existencia->piezas_contenido;
                $piezas_salida=$p->piezas;
                $flag=true;
            }

            else{
                return ["code"=>0,"message"=>"Seleccione una opcion"];
                $flag=false;
            }


            if($flag)
            {                            

                $id_=DB::table("almacen_salida")->insertGetId([
                  "id_almacen_entrada"=>$id_almacen_entrada,
                  "id_persona_autoriza"=>0,
                  "id_persona_recibe"=>0,
                  "cantidad"=>$cantidad_salida,
                  "piezas"=>$piezas_salida,
                  "id_espacio_fisico"=>0
                ]);

                return ["code"=>$id_,"message"=>"Salida exitosa"];

            }


        }

        else{
            return ["code"=>0,"message"=>"Stock sin existencia..."];
        }
        
    }
}