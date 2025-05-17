<?php

namespace App\DAO;

use App\Entity\producto;
use Carbon\Carbon;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\DB;
use App\Entity\SubCategoriaProductos;
use App\Entity\UnidadMedida;
use App\Entity\TipoProducto;
use App\Entity\ProductoPresentacion;

class ProductoDAO
{

    public function __construct()
    {
    }
    /**
     * metodos
     */
    public static function insertProducto($p)
    {        

        $name_full=["nombre"=>$p->nombre,"material"=>$p->material,"medida1"=>$p->medida1,"medida2"=>$p->medida2,"forma"=>$p->forma,"tipo_cuerda"=>$p->tipo_cuerda,"otros"=>$p->otros];



        return DB::transaction(function () use ($p,$name_full) {

            $folio=DB::table("producto_pv")->max("id_producto_pv");
            //se buscan las llaves foraneas 
            $subcategoria=SubCategoriaProductos::find($p->id_subcategoria);
            $unidad_medida=UnidadMedida::find($p->id_unidad_medida);
            $tipo_producto=TipoProducto::find($p->id_tipo_producto);

            $producto = new producto();
            $producto->tipo = $p->tipo;//es servivico o producto
            $producto->subCategoria()->associate($subcategoria);
            $producto->unidadMedida()->associate($unidad_medida);
            $producto->tipoProducto()->associate($tipo_producto);
            $producto->nombre = json_encode($name_full);
            $producto->clave = "$p->clave".($folio+1);
            $producto->descripcion = $p->descripcion;
            $producto->modelo = $p->modelo??null;
            $producto->foto = $p->foto??null;
            $producto->observacion_requisicion = $p->observacion_requisicion??null;
            $producto->estatus = 1;
            if($p->id_subsubcategoria??false)$producto->id_subsubcategoria=$p->id_subsubcategoria;

            // $producto->new_name=json_encode($name_full);

            //se guarda el producto
            $producto->save();

            //se transforman en ProductoPresentacion
            $presentaciones=collect($p->presentaciones??null)->map(function($i){return new ProductoPresentacion($i);});

            if($presentaciones->count()>0){
                //se guardan las presentaciones de los productos
                $producto->presentaciones()->saveMany($presentaciones->all());
            }
            else{
                $producto->presentaciones()->save(new ProductoPresentacion(["unidad_medida"=>$p->id_unidad_medida,"cantidad"=>1]));
            }
            //se guardan las marcas de los productos
            $producto->marcasProducto()->attach($p->marcas??null);

            return $producto->id_producto_pv;
        });
    }

    public static function updateProducto($id, $p)
    {

        return DB::transaction(function () use ($id, $p) {

            $producto = producto::find($id);
            $producto->id_subcategoria = $p->id_subcategoria;
            $producto->id_unidad_medida_compra = $p->id_unidad_medida_compra;
            $producto->nombre = $p->nombre;
            $producto->clave = $p->clave;
            $producto->descripcion = $p->descripcion;
            $producto->modelo = $p->modelo;
            $producto->foto = $p->foto;
            $producto->id_marca = $p->id_marca;
            $producto->tamano = $p->tamano;
            $producto->id_unidad_medida_producto = $p->id_unidad_medida_producto;
            $producto->piezas_contenido = $p->piezas_contenido;
            $producto->minimo_stock = $p->minimo_stock;
            $producto->maximo_stock = $p->maximo_stock;
            $producto->observacion_requisicion = $p->observacion_requisicion??null;
            $producto->estatus = 1;
            $producto->save();

            return $producto->id_producto;


            return 1;
        });
    }

    public static function findProducto($id)
    {

        /*
            SELECT 
		        producto_pv.id_producto_pv, 
		        producto_pv.clave, 
		        producto_pv.nombre, 
		        producto_pv.descripcion, 
		        categoria_producto_pv.nombre AS categoria, 
		        subcategoria_producto_pv.nombre AS subcategoria, 
		        subsubcategoria_producto_pv.nombre AS subsubcategoria, 
		        unidad_medido_producto_pv.nombre AS medida_compra, 
		        producto_pv.modelo, 
		        producto_pv.foto,
		        producto_pv.tipo,
		        GROUP_CONCAT(marca_productos_pv.nombre) AS marcas
            FROM producto_pv
            INNER JOIN unidad_medido_producto_pv ON producto_pv.id_unidad_medida = unidad_medido_producto_pv.id_unidad_medida_producto_pv
            INNER JOIN subcategoria_producto_pv ON producto_pv.id_subcategoria = subcategoria_producto_pv.id_subcategoria_producto_pv
            INNER JOIN categoria_producto_pv ON subcategoria_producto_pv.id_categoria_pv = categoria_producto_pv.id_categoria_pv
            LEFT JOIN subsubcategoria_producto_pv ON producto_pv.id_subsubcategoria= subsubcategoria_producto_pv.id_subcategoria_producto_pv
            LEFT JOIN producto_marca_pv ON producto_pv.id_producto_pv=producto_marca_pv.id_producto_pv
            LEFT JOIN marca_productos_pv ON producto_marca_pv.id_marca_productos_pv=marca_productos_pv.id_marca_productos_pv
            WHERE producto_pv.id_producto_pv = 19
            GROUP BY producto_pv.id_producto_pv;
        */

        return Producto::join("unidad_medido_producto_pv", "producto_pv.id_unidad_medida", "unidad_medido_producto_pv.id_unidad_medida_producto_pv")        
        ->join("subcategoria_producto_pv", "producto_pv.id_subcategoria", "subcategoria_producto_pv.id_subcategoria_producto_pv")
        ->join("categoria_producto_pv", "subcategoria_producto_pv.id_categoria_pv", "categoria_producto_pv.id_categoria_pv")
        ->leftJoin("subsubcategoria_producto_pv", "producto_pv.id_subsubcategoria", "subsubcategoria_producto_pv.id_subcategoria_producto_pv")
        ->leftJoin("producto_marca_pv", "producto_pv.id_producto_pv", "producto_marca_pv.id_producto_pv")
        ->leftJoin("marca_productos_pv", "producto_marca_pv.id_marca_productos_pv", "marca_productos_pv.id_marca_productos_pv")
        ->select(
            "producto_pv.id_producto_pv",
            "producto_pv.clave",
            // "producto_pv.nombre",
            // DB::raw("CONCAT_WS(' ',producto_pv.nombre->>\"$.NOMBRE\",producto_pv.nombre->>\"$.MATERIAL\",producto_pv.nombre->>\"$.MEDIDA1\",producto_pv.nombre->>\"$.MEDIDA2\",producto_pv.nombre->>\"$.FORMA\",producto_pv.nombre->>\"$.TIPO_CUERDA\",producto_pv.nombre->>\"$.OTROS\") AS nombre"),
            "producto_pv.descripcion",
            "categoria_producto_pv.nombre AS categoria",
            "subcategoria_producto_pv.nombre AS subcategoria",
            "subsubcategoria_producto_pv.nombre AS subsubcategoria",
            "unidad_medido_producto_pv.nombre AS medida_compra",
            "producto_pv.modelo",
            "producto_pv.foto",
            "producto_pv.observacion_requisicion"
        )
        ->selectRaw("GROUP_CONCAT(marca_productos_pv.nombre) AS marcas")
        ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre")
        ->where("producto_pv.id_producto_pv",$id)
        ->groupBy("producto_pv.id_producto_pv")
        ->first();
    }

    public static function getProductos($p)
    {
       
        try {            
            $query= Producto::join("unidad_medido_producto_pv", "producto_pv.id_unidad_medida", "unidad_medido_producto_pv.id_unidad_medida_producto_pv")
                ->leftJoin("subcategoria_producto_pv","producto_pv.id_subcategoria","subcategoria_producto_pv.id_subcategoria_producto_pv")
                ->leftJoin("categoria_producto_pv","subcategoria_producto_pv.id_categoria_pv","categoria_producto_pv.id_categoria_pv")
                ->leftJoin("producto_marca_pv","producto_pv.id_producto_pv","producto_marca_pv.id_producto_pv")
                ->leftJoin("marca_productos_pv", "producto_marca_pv.id_marca_productos_pv", "marca_productos_pv.id_marca_productos_pv")                
                ->leftJoin("producto_presentacion_pv", "producto_pv.id_producto_pv", "producto_presentacion_pv.id_producto_pv")
                ->leftJoin("unidad_medido_producto_pv AS medida_presentacion", "producto_presentacion_pv.unidad_medida", "medida_presentacion.id_unidad_medida_producto_pv")

                ->select(
                    "producto_pv.tipo",
                    "unidad_medido_producto_pv.nombre AS medida_producto",
                    // "producto_pv.nombre",                    
                    "producto_pv.clave",
                    "producto_pv.modelo",
                    "categoria_producto_pv.nombre AS categoria_name",
                    "subcategoria_producto_pv.nombre AS subcategoria_name",
                    "producto_pv.estatus"                    
                )
                ->selectRaw("GROUP_CONCAT(DISTINCT(CONCAT_WS('|',medida_presentacion.nombre,producto_presentacion_pv.cantidad))) AS presentaciones")
                ->selectRaw("GROUP_CONCAT(DISTINCT(marca_productos_pv.nombre)) AS marcas")
                // ->selectRaw("JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE','$.MATERIAL','$.MEDIDA1','$.MEDIDA2','$.FORMA','$.OTROS') AS nombre");
                ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre");

                $query->groupBy("producto_pv.id_producto_pv");

                if($p->clave??false)
                {
                    $query->where("producto_pv.clave",$p->clave);
                }

                if(is_numeric($p->tipo??false))
                {
                    $query->where("producto_pv.tipo",$p->tipo);
                }

                if($p->nombre??false)
                {                
                    $query->whereRaw("LOWER(producto_pv.nombre) LIKE ?", ["%".strtolower($p->nombre)."%"]);
                }
                
                if($p->categoria??false)
                {
                    $query->where("categoria_producto_pv.id_categoria_pv",$p->categoria);
                }

                if($p->subcategoria??false)
                {
                    $query->where("subcategoria_producto_pv.id_subcategoria_producto_pv",$p->subcategoria);
                }

                if($p->marca??false)
                {
                    $query->where("marca_productos_pv.id_marca_productos_pv",$p->marca);
                }
                
                if($p->unidad_compra??false)
                {
                    $query->where("medida_compra.id_unidad_medida_producto_pv",$p->unidad_compra);
                }               

                return $query->get();    

            
        } catch (\Exception $e) {
            return ($e);      
            return [];
        }
    }

    public static function getProductosByParameters($param)
    {        
        try {
            $query= Producto::select(
                    "producto_pv.id_producto_pv",
                    "producto_pv.clave"
                    // DB::raw("CONCAT_WS(' ',nombre->>\"$.NOMBRE\",nombre->>\"$.MATERIAL\",nombre->>\"$.MEDIDA1\",nombre->>\"$.MEDIDA2\",nombre->>\"$.FORMA\",nombre->>\"$.TIPO_CUERDA\",nombre->>\"$.OTROS\") AS nombre")                    
                    // "producto_pv.nombre"
                )->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre");

                $query->whereRaw("LOWER(producto_pv.nombre) LIKE ?",["%".strtolower($param)."%"]);

                $query->orWhere("producto_pv.clave",$param);
                
                // $query->where("producto_pv.nombre","LIKE","%".$param."%");                

                return $query->get();
        } catch (\Exception $e) {
            return $e;
            // return [];
        }
    }

    public static function deleteProducto()
    {
        DB::transaction(function(){
         
            //debo buscar en una requisicion si ya esta agregado el producto si es asi no se podra eliminar

        });
    }

    public static function getAlmacen()
    {
        try {
            return DB::table("pv_almacen")->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    //buscar como seforma elnombre como ejemplo nombre/mateial/medida1/medida2 etc
    public static function getDetalleFormaNombre($nombre)
    {
        // dd($nombre);
        /**SELECT JSON_EXTRACT(nombre,'$.nombre','$.material','$.medida1','$.medida2','$.forma','$.tipo_cuerda','$.otros') AS name FROM producto_pv WHERE UPPER(JSON_EXTRACT(nombre,'$.nombre')) LIKE UPPER('%clavo%') */
        return DB::table("producto_pv")
        ->whereRaw("UPPER(JSON_EXTRACT(nombre,'$.NOMBRE')) LIKE UPPER(?)",["%".$nombre."%"])
        ->selectRaw("JSON_EXTRACT(nombre,'$.NOMBRE','$.MATERIAL','$.MEDIDA1','$.MEDIDA2','$.FORMA','$.TIPO_CUERDA','$.OTROS') AS name")
        ->value("name");
    }
}
