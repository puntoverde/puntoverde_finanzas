<?php
namespace App\DAO;
use App\Entity\Accionista;
use App\Entity\Persona;
use App\Entity\Colonia;
use App\Entity\Direccion;
use App\Entity\Accion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AlmacenDAO {

    public function __construct(){}
    /**
     * 
     */
    public static function crearCorteAlmacen($p)
    {   

        /*

        SELECT 
            almacen_entrada.id_producto,
            (sum(almacen_entrada.cantidad)/if(COUNT(almacen_salida.id_almacen_entrada)=0,1,COUNT(almacen_salida.id_almacen_entrada))-ifnull(sum(almacen_salida.cantidad),0)) AS cantidad_existencia
        FROM almacen_entrada 
        LEFT JOIN almacen_salida ON almacen_entrada.id_almacen_entrada=almacen_salida.id_almacen_entrada AND  convert(almacen_salida.fecha_salida ,date)<='2023-10-28'
        GROUP BY almacen_entrada.id_producto

        */

       return  DB::transaction(function()use($p){
            
            //validar que no sean del mismo dia
            $exist=DB::table('corte_almacen')->where('fecha_corte', $p->fecha)->exists();

            // if($exist)
            // {   
            //     return 1;
            // }
           
            $id_almacen_corte=DB::table("corte_almacen")->insertGetId([
            "persona_corte"=>$p->cve_persona,
            "fecha_corte"=>$p->fecha,
            "hora_corte"=>$p->hora,
            "estatus"=>1,
        ]);
            
            $historico=DB::table("almacen_entrada")
            ->leftJoin("almacen_salida",function($join){ 
                $join->on("almacen_entrada.id_almacen_entrada","almacen_salida.id_almacen_entrada")
                ->whereRaw("CONVERT(almacen_salida.fecha_salida ,date)<= ?",['2023-10-28']);
            })
            ->select("almacen_entrada.id_producto")
            ->selectRaw("(SUM(almacen_entrada.cantidad)/ IF(COUNT(almacen_salida.id_almacen_entrada)=0,1,COUNT(almacen_salida.id_almacen_entrada)) - IFNULL(SUM(almacen_salida.cantidad),0)) AS cantidad_existencia")
            ->selectRaw("? AS id_historico",[$id_almacen_corte])
            ->groupBy("almacen_entrada.id_producto")
            ->get();

            $historico_map=$historico->map(function($i){return ["id_corte_almacen"=>$i->id_historico,"id_producto"=>$i->id_producto,"cantidad"=>$i->cantidad_existencia];})->toArray();        

            DB::table("corte_almacen_detalle")->insert($historico_map);


            return $historico;

            });
                
        

        
    }
    public static function getCortesAlmacen()
    {   
        /*
        SELECT fecha_corte,hora_corte,nombre,apellido_paterno,apellido_materno,corte_almacen.estatus FROM corte_almacen
        INNER JOIN  persona ON corte_almacen.persona_corte=persona.cve_persona
        */
        
        return DB::table("corte_almacen")
        ->join("persona" , "corte_almacen.persona_corte","persona.cve_persona")
        ->select("id_corte_almacen","fecha_corte","hora_corte","nombre","apellido_paterno","apellido_materno","corte_almacen.estatus")
        ->get();

    }

    public static function getDetalleCorteAlmacen($id_corte)
    {
        /*
            SELECT 
                producto_pv.id_producto_pv,
                producto_pv.clave,
                producto_pv.nombre,
                producto_pv.descripcion,
                producto_pv.tipo,
                marca_productos_pv.nombre AS marca_name,
                producto_pv.modelo,
                categoria_producto_pv.nombre AS categoria_name,
                subcategoria_producto_pv.nombre AS subcategoria_name,
                unidad_medido_producto_pv.nombre AS unidad_medida_compra,
                corte_almacen_detalle.cantidad
            FROM corte_almacen_detalle
            INNER JOIN  producto_pv ON corte_almacen_detalle.id_producto=producto_pv.id_producto_pv
            INNER JOIN  subcategoria_producto_pv ON producto_pv.id_subcategoria=subcategoria_producto_pv.id_subcategoria_producto_pv
            INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv=categoria_producto_pv.id_categoria_pv
            INNER JOIN marca_productos_pv ON producto_pv.id_marca=marca_productos_pv.id_marca_productos_pv
            INNER JOIN unidad_medido_producto_pv ON  producto_pv.id_unidad_medida_compra=unidad_medido_producto_pv.id_unidad_medida_producto_pv
            WHERE corte_almacen_detalle.id_corte_almacen=1
        */

        return DB::table("corte_almacen_detalle")
        ->JOIN("producto_pv" , "corte_almacen_detalle.id_producto","producto_pv.id_producto_pv")
        ->JOIN("subcategoria_producto_pv" , "producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
        ->JOIN("categoria_producto_pv" , "subcategoria_producto_pv.id_categoria_pv","categoria_producto_pv.id_categoria_pv")
        ->JOIN("marca_productos_pv" , "producto_pv.id_marca","marca_productos_pv.id_marca_productos_pv")
        ->JOIN("unidad_medido_producto_pv" ,  "producto_pv.id_unidad_medida_compra","unidad_medido_producto_pv.id_unidad_medida_producto_pv")
        ->select(
                "producto_pv.id_producto_pv",
                "producto_pv.clave",
                "producto_pv.nombre",
                "producto_pv.descripcion",
                "producto_pv.tipo",
                "marca_productos_pv.nombre AS marca_name",
                "producto_pv.modelo",
                "categoria_producto_pv.nombre AS categoria_name",
                "subcategoria_producto_pv.nombre AS subcategoria_name",
                "unidad_medido_producto_pv.nombre AS unidad_medida_compra",
                "corte_almacen_detalle.cantidad"
        )
        ->where("corte_almacen_detalle.id_corte_almacen",$id_corte)
        ->get();

    }

   
}