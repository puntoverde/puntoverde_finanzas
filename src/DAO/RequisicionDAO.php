<?php

namespace App\DAO;

use App\Entity\producto;
use App\Entity\Requisicion;
use App\Entity\Colaborador;
use App\Entity\ProductoPresentacion;
use App\Entity\MarcaProducto;
// use Carbon\Carbon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RequisicionDAO
{

    public function __construct()
    {
    }
    /**
     * 
     */

    public static function getRequisiciones($id, $p)
    {

      
        /* 
            SELECT 
				colaborador.id_colaborador,
				requisicion_pv.id_requisicion_pv,
                requisicion_pv.folio,
                requisicion_pv.fecha_solicitud,
                requisicion_pv.estatus,
                persona.nombre,
                persona.apellido_paterno,
                persona.apellido_materno,
                requisicion_pv.id_colaborador_solicita=161 AS solicita,
                requisicion_pv.id_colaborador_revisa=161 AS revisa,
                requisicion_pv.id_colaborador_autorizo=161 AS autoriza
            FROM requisicion_pv 
            INNER JOIN colaborador ON colaborador.id_colaborador = requisicion_pv.id_colaborador_solicita
            INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
            WHERE (requisicion_pv.id_colaborador_solicita=161 OR requisicion_pv.id_colaborador_revisa=161 OR requisicion_pv.id_colaborador_autorizo=161)
        
        */
        $id_colaborador=Colaborador::where("cve_persona",$id)->value("id_colaborador");  

        $query = Requisicion::join("colaborador" , "colaborador.id_colaborador" , "requisicion_pv.id_colaborador_solicita")
            ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
            ->where(function ($where) use ($id_colaborador) {
                $where->where("requisicion_pv.id_colaborador_solicita", $id_colaborador)
                    ->orWhere("requisicion_pv.id_colaborador_revisa", $id_colaborador)
                    ->orWhere("requisicion_pv.id_colaborador_autorizo", $id_colaborador);
            })
            ->select(
                "requisicion_pv.id_requisicion_pv",
                "requisicion_pv.folio",
                "requisicion_pv.fecha_solicitud",
                "requisicion_pv.estatus",
                "persona.nombre",
                "persona.apellido_paterno",
                "persona.apellido_materno"
            )
            ->selectRaw("requisicion_pv.id_colaborador_solicita=? AS solicita", [$id_colaborador])
            ->selectRaw("requisicion_pv.id_colaborador_revisa=? AS revisa", [$id_colaborador])
            ->selectRaw("requisicion_pv.id_colaborador_autorizo=? AS autoriza", [$id_colaborador]);


        if ($p->folio ?? false) {
            $query->where("requisicion_pv.folio", $p->folio);
        }
        if (($p->fecha_solicitud_inicio ?? false) == true && ($p->fecha_solicitud_fin ?? false) == false) {
            $query->where("requisicion_pv.fecha_solicitud", ">=", $p->fecha_solicitud_inicio);
        }
        if (($p->fecha_solicitud_inicio ?? false) == false && ($p->fecha_solicitud_fin ?? false) == true) {
            $query->where("requisicion_pv.fecha_solicitud", "<=", $p->fecha_solicitud_fin);
        }

        if (($p->fecha_solicitud_inicio ?? false) == true && ($p->fecha_solicitud_fin ?? false) == true) {
            $query->whereRaw("requisicion_pv.fecha_solicitud BETWEEN ? AND ?", [$p->fecha_solicitud_inicio, $p->fecha_solicitud_fin]);
        }

        if ($p->solicito ?? false) {
            $query->whereRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE ?", ['%' . $p->solicito . '%']);
        }
        if (is_numeric($p->estatus ?? false)) {
            $query->where("requisicion_pv.estatus", $p->estatus);
        }

        return $query->get();
    }


    public static function crearRequisicion($p)
    {
        return DB::transaction(function () use ($p) {
           
            $folio=Requisicion::max("folio");
            
            $requisicion =new Requisicion();
            $requisicion->folio=$folio + 1;
            $requisicion->fecha_solicitud=Carbon::now();
            $requisicion->id_colaborador_solicita= $p->solicita;
            $requisicion->id_colaborador_revisa=$p->revisa;
            $requisicion->id_colaborador_autorizo=$p->autoriza;
            $requisicion->id_orden_trabajo_actividad=$p->id_actividad??null;
            $requisicion->estatus=1;
            $requisicion->save();

            $new_data_=collect($p->productos)->keyBy('id')->map(function($i){          
                return [
                    "id_producto_presentacion"=>$i["presentacion"]??null,
                    "id_espacio_fisico"=>$i["espacio_fisico"]??null,
                    "id_marca"=>$i["marca"]??null,
                    "cantidad"=>$i["cantidad"]??null,
                    "observaciones"=>$i["observacion"]??null,
                ];
            })->all();
                      
            $requisicion->productos()->attach($new_data_);           


            if($p->id_requisicion_existe??false)
            {
                DB::table("requisicion_anidadas")->insert(["id_requisicion"=>$p->id_requisicion_existe,"id_requisicion_child"=>$requisicion->id_requisicion_pv]);
            }

            return $folio + 1;


        });
    }

  

    public static function getDepartamentoAndColaboradores($id_persona)
    {

        $departamento = DB::Table("colaborador")
            ->join("area_rh","colaborador.id_area","area_rh.id_area_rh")
            ->join("rh_departamento", "area_rh.id_departamento", "rh_departamento.id_departamento")
            ->where("colaborador.cve_persona", $id_persona)
            ->select("rh_departamento.id_departamento","area_rh.id_area_rh","rh_departamento.nombre", "rh_departamento.jefe_departamento AS encargado")
            ->first();

        if ($departamento) {

            $colaboradores = DB::table("colaborador")
                ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
                ->where("colaborador.id_area", $departamento->id_area_rh)
                ->select("colaborador.id_colaborador","persona.cve_persona", "persona.nombre", "persona.apellido_paterno", "persona.apellido_materno")
                ->get();

            return ["departamento" => $departamento, "colaboradores" => $colaboradores];
        } else return null;
    }

    public static function getPersonaSolicitaRevisaAprueba($id)
    {

        
        //es para guardar la persona revisa en base a la persona solicita segun esta sea jefe de area o no.
        $persona_revisa = null;

        // //se trae a la persona que solicita la requisicion...
        /*
            SELECT 
                colaborador.id_colaborador, 
	            persona.nombre, 
	            persona.apellido_paterno, 
	            persona.apellido_materno, 
	            rh_departamento.id_departamento, 
	            IF(rh_departamento.jefe_departamento=colaborador.id_colaborador,1,0) AS is_jefe_area,
	            IFNULL(colaborador.nivel_firma,0) AS nivel_firma
            FROM colaborador
            INNER JOIN persona ON colaborador.cve_persona = persona.cve_persona
            INNER JOIN area_rh ON colaborador.id_area = area_rh.id_area_rh
            INNER JOIN rh_departamento ON area_rh.id_departamento = rh_departamento.id_departamento
            WHERE persona.cve_persona = 24763 AND (colaborador.nivel_firma NOT IN(1,2) OR colaborador.nivel_firma IS NULL)
        */


        $persona_solicita = DB::table("colaborador")
            ->join("persona","colaborador.cve_persona","persona.cve_persona")
            ->join("area_rh", "colaborador.id_area", "area_rh.id_area_rh")
            ->join("rh_departamento", "area_rh.id_departamento", "rh_departamento.id_departamento")            
            ->where("persona.cve_persona", $id)
            ->where(function($query){$query->whereNotIn("colaborador.nivel_firma",[1,2])->orWhereNull("colaborador.nivel_firma");})
            ->select("colaborador.id_colaborador", "persona.nombre", "persona.apellido_paterno", "persona.apellido_materno","rh_departamento.id_departamento")
            ->selectRaw("IF(rh_departamento.jefe_departamento=colaborador.id_colaborador,1,0) AS is_jefe_area")
            ->selectRaw("IFNULL(colaborador.nivel_firma,0) AS nivel_firma")
            ->first();            
                  

        //si la persona que solicita es jefe de area y nivel firma es 0 se busca el area con nivel 2(el dos es el repsonsable que en este momento es alvaro))
        if ($persona_solicita->is_jefe_area > 0 && $persona_solicita->nivel_firma==0) {
            $persona_revisa = DB::table("colaborador")
                ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
                ->where("colaborador.nivel_firma", 2)
                ->select("colaborador.id_colaborador", "persona.nombre", "persona.apellido_paterno", "persona.apellido_materno")
                ->first();               
        }        
        else { //en caso contrario se busca el jefe de departamento al que el solicita esta registrado ejemplo (leo solicita no es jefe departamento, se busca a javier como jefe de departamento) nota se usa el id_departamento del solicitante           
            $persona_revisa = DB::table("rh_departamento")
                ->join("colaborador", "rh_departamento.jefe_departamento", "colaborador.id_colaborador")
                ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
                ->where("rh_departamento.id_departamento", $persona_solicita->id_departamento)
                ->select("colaborador.id_colaborador", "persona.nombre", "persona.apellido_paterno", "persona.apellido_materno")
                ->first();               
        }

        $persona_aprueba = DB::table("colaborador")
            ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
            ->where("colaborador.nivel_firma", 1)
            ->select("colaborador.id_colaborador", "persona.nombre", "persona.apellido_paterno", "persona.apellido_materno")
            ->first();                     

        return ["solicita" => $persona_solicita, "revisa" => $persona_revisa, "aprueba" => $persona_aprueba];
    }

    public static function findProducto($id)
    {
        return producto::select("id_producto_pv","id_subcategoria","id_subsubcategoria","id_unidad_medida","id_producto_tipo","clave","descripcion","modelo","foto","tipo","estatus","creation_date","updated_date")
        ->selectRaw("CONCAT_WS(' ',
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
            ) AS nombre")->first();
    }

    public static function getProductos()
    {
        try {
            return Producto::select("id_producto_pv","id_subcategoria","id_subsubcategoria","id_unidad_medida","id_producto_tipo","clave","descripcion","modelo","foto","tipo","estatus","creation_date","updated_date")
            ->selectRaw("CONCAT_WS(' ',
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                ) AS nombre")->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function agregarProducto()
    {
        try {
            return DB::table("requisicion_producto_pv")
            ->insertGetId(["id_requisicion_pv" => 1, "id_producto_pv" => 1, "cantidad" => 1, "observaciones" => ""]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function getDetalleRequisicion($id)
    {

        return Requisicion::
            join("requisicion_producto_pv", "requisicion_pv.id_requisicion_pv", "requisicion_producto_pv.id_requisicion_pv")
            ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
            ->join("unidad_medido_producto_pv", "producto_pv.id_unidad_medida", "unidad_medido_producto_pv.id_unidad_medida_producto_pv")
            ->leftJoin("producto_presentacion_pv","requisicion_producto_pv.id_producto_presentacion","producto_presentacion_pv.id_producto_presentacion_pv")
            ->leftJoin("unidad_medido_producto_pv AS unidad_presentacion", "producto_presentacion_pv.unidad_medida", "unidad_presentacion.id_unidad_medida_producto_pv")
            ->leftJoin("marca_productos_pv", "requisicion_producto_pv.id_marca", "marca_productos_pv.id_marca_productos_pv")
            ->select(
                "producto_pv.clave",
                // "producto_pv.nombre",
                "requisicion_producto_pv.cantidad",
                // "medida_compra.nombre AS m_compra",
                // "producto_pv.id_unidad_medida_compra",
                // "producto_pv.tamano",
                // "medida_producto.nombre AS m_producto",
                // "producto_pv.id_unidad_medida_producto",
                "unidad_medido_producto_pv.nombre AS unidad_producto",
                "producto_presentacion_pv.id_producto_presentacion_pv AS id_presentacion",
                "producto_presentacion_pv.cantidad AS cantidad_presentacion",
                "unidad_presentacion.nombre AS m_presentacion",
                "marca_productos_pv.nombre AS marca",
                "requisicion_producto_pv.estatus_revision",
                "requisicion_producto_pv.estatus_confirmacion",
                "requisicion_pv.estatus",
                "requisicion_producto_pv.observaciones"
            )
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
            ->where("requisicion_producto_pv.id_requisicion_pv", $id)
            ->get();
    }

    public static function getRevisionRequisicion($id)
    {
        return Requisicion::join("requisicion_producto_pv", "requisicion_pv.id_requisicion_pv", "requisicion_producto_pv.id_requisicion_pv")
            ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
            ->join("unidad_medido_producto_pv", "producto_pv.id_unidad_medida", "unidad_medido_producto_pv.id_unidad_medida_producto_pv")
            ->leftJoin("producto_presentacion_pv","requisicion_producto_pv.id_producto_presentacion","producto_presentacion_pv.id_producto_presentacion_pv")
            ->leftJoin("unidad_medido_producto_pv AS unidad_presentacion", "producto_presentacion_pv.unidad_medida", "unidad_presentacion.id_unidad_medida_producto_pv")
            ->leftJoin("marca_productos_pv", "requisicion_producto_pv.id_marca", "marca_productos_pv.id_marca_productos_pv")
            ->select(
                "requisicion_producto_pv.id_requisicion_producto_pv",
                "producto_pv.clave",
                // "producto_pv.nombre",
                "requisicion_producto_pv.cantidad",
                // "medida_compra.nombre AS m_compra",
                // "producto_pv.id_unidad_medida_compra",
                // "producto_pv.tamano",
                // "medida_producto.nombre AS m_producto",
                "unidad_medido_producto_pv.nombre AS unidad_producto",
                "producto_presentacion_pv.id_producto_presentacion_pv AS id_presentacion",
                "producto_presentacion_pv.cantidad AS cantidad_presentacion",
                "unidad_presentacion.nombre AS m_presentacion",
                "marca_productos_pv.nombre AS marca",
                "requisicion_producto_pv.estatus_revision",
                "requisicion_producto_pv.estatus_confirmacion",
                "requisicion_pv.estatus",
                "requisicion_producto_pv.observaciones"
            )
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
            ->where("requisicion_producto_pv.id_requisicion_pv", $id)
            ->where("requisicion_pv.estatus",1)
            ->get();
    }

    public static function rechazarProductoRequisicionRevision($id)
    {
        return DB::table("requisicion_producto_pv")->where("id_requisicion_producto_pv", $id)->update(["estatus_revision" => 0]);
    }

    public static function terminarRevision($id)
    {
        $flag = DB::table("requisicion_producto_pv")->where("id_requisicion_pv", $id)->where("estatus_revision", 1)->count();
        //flag indica que almenos hay algun producto habilitado
        if ($flag > 0) {
            return DB::table("requisicion_pv")->where("id_requisicion_pv", $id)->update(["estatus" => 2]);
        } else { //se cancela por que no existe algun producto activo
            return DB::table("requisicion_pv")->where("id_requisicion_pv", $id)->update(["estatus" => 0]);
        }
    }

    public static function getAprobarRequisicion($id)
    {
        return Requisicion::join("requisicion_producto_pv", "requisicion_pv.id_requisicion_pv", "requisicion_producto_pv.id_requisicion_pv")
            ->join("producto_pv", "requisicion_producto_pv.id_producto_pv", "producto_pv.id_producto_pv")
            ->join("unidad_medido_producto_pv", "producto_pv.id_unidad_medida", "unidad_medido_producto_pv.id_unidad_medida_producto_pv")
            ->leftJoin("producto_presentacion_pv","requisicion_producto_pv.id_producto_presentacion","producto_presentacion_pv.id_producto_presentacion_pv")
            ->leftJoin("unidad_medido_producto_pv AS unidad_presentacion", "producto_presentacion_pv.unidad_medida", "unidad_presentacion.id_unidad_medida_producto_pv")
            ->leftJoin("marca_productos_pv", "requisicion_producto_pv.id_marca", "marca_productos_pv.id_marca_productos_pv")
            ->select(
                "requisicion_producto_pv.id_requisicion_producto_pv",
                "producto_pv.clave",
                // "producto_pv.nombre",
                "requisicion_producto_pv.cantidad",
                // "medida_compra.nombre AS m_compra",
                // "producto_pv.id_unidad_medida_compra",
                // "producto_pv.tamano",
                // "medida_producto.nombre AS m_producto",
                "unidad_medido_producto_pv.nombre AS unidad_producto",
                "producto_presentacion_pv.id_producto_presentacion_pv AS id_presentacion",
                "producto_presentacion_pv.cantidad AS cantidad_presentacion",
                "unidad_presentacion.nombre AS m_presentacion",
                "marca_productos_pv.nombre AS marca",
                "requisicion_producto_pv.estatus_revision",
                "requisicion_producto_pv.estatus_confirmacion",
                "requisicion_pv.estatus",
                "requisicion_producto_pv.observaciones"
            )
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
            ->where("requisicion_producto_pv.id_requisicion_pv", $id)
            ->where("requisicion_producto_pv.estatus_revision",1)//solo productos validados
            ->where("requisicion_pv.estatus",2)//y que la requisisicon este en revisada
            ->get();
    }

    public static function rechazarProductoRequisicionAprobacion($id)
    {
        return DB::table("requisicion_producto_pv")->where("id_requisicion_producto_pv", $id)->update(["estatus_confirmacion" => 0]);
    }

    public static function terminarAprobacion($id)
    {
        $flag = DB::table("requisicion_producto_pv")->where("id_requisicion_pv", $id)->where("estatus_revision", 1)->where("estatus_confirmacion",1)->count();

        if ($flag > 0) {
            return DB::table("requisicion_pv")->where("id_requisicion_pv", $id)->update(["estatus" => 3]);
        } else { //se cancela por que no existe algun producto activo
            return DB::table("requisicion_pv")->where("id_requisicion_pv", $id)->update(["estatus" => 0]);
        }
    }

    public static function cancelarRequisicion($id)
    {
        return Requisicion::where("id_requisicion_pv", $id)->whereIn("estatus", [1, 2])->update(["estatus" => 0]);
    }

    public static function agregarEvidenciaProductoServicio($p)
    {
        DB::table("requisicion_producto_servicio_evidencia_pv")->insertGetId([
            "id_requisicion_producto" => $p->id_requisicion_producto,
            "descripcion" => $p->descripcion,
            "imagen_evidencia" => $p->imagen_evidencia
        ]);
    }

    public static function getPresentacionesProducto($id)
    {
        return ProductoPresentacion::join("unidad_medido_producto_pv","producto_presentacion_pv.unidad_medida","unidad_medido_producto_pv.id_unidad_medida_producto_pv")
        ->where("producto_presentacion_pv.id_producto_pv",$id)
        ->select("producto_presentacion_pv.id_producto_presentacion_pv","producto_presentacion_pv.cantidad","unidad_medido_producto_pv.nombre")
        ->get();
    }

    public static function getMarcaAsignar($id)
    {
        return MarcaProducto::join("producto_marca_pv","marca_productos_pv.id_marca_productos_pv","producto_marca_pv.id_marca_productos_pv")
        ->where("producto_marca_pv.id_producto_pv",$id)
        ->select("marca_productos_pv.id_marca_productos_pv","marca_productos_pv.nombre")
        ->get();
    }


    public static function getAllRequisicionByColaborador($cve_persona)
    {

        /*
        
                SELECT 
	                requisicion_pv.id_requisicion_pv,
	                requisicion_pv.folio,
	                requisicion_pv.fecha_solicitud,
	                requisicion_pv.estatus,
	                GROUP_CONCAT(requisicion_producto_pv.observaciones) AS observaciones
	
                FROM requisicion_pv 
                INNER JOIN requisicion_producto_pv ON requisicion_pv.id_requisicion_pv=requisicion_producto_pv.id_requisicion_pv
                INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv=producto_pv.id_producto_pv
                INNER JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv
                INNER JOIN pedido_almacen_revision_pv ON pedido_almacen_producto_pv.id_pedido_almacen_producto_pv=pedido_almacen_revision_pv.id_pedido_almacen_producto
                WHERE requisicion_pv.id_colaborador_solicita=40 GROUP BY requisicion_pv.id_requisicion_pv;

        */
        
        $id_colaborador=Colaborador::where("cve_persona",$cve_persona)->value("id_colaborador"); 
        // dd($id_colaborador); 


        $requisiciones= DB::table("requisicion_pv")
        ->join("requisicion_producto_pv" , "requisicion_pv.id_requisicion_pv","requisicion_producto_pv.id_requisicion_pv")
        ->join("producto_pv" , "requisicion_producto_pv.id_producto_pv","producto_pv.id_producto_pv")
        // ->join("pedido_almacen_producto_pv" , "requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")
        // ->join("pedido_almacen_revision_pv" , "pedido_almacen_producto_pv.id_pedido_almacen_producto_pv","pedido_almacen_revision_pv.id_pedido_almacen_producto")
        ->select(
            "requisicion_pv.id_requisicion_pv",
            "requisicion_pv.folio",
            "requisicion_pv.fecha_solicitud",
            "requisicion_pv.estatus")
        ->selectRaw("GROUP_CONCAT(requisicion_producto_pv.observaciones) AS observaciones")
        
        ->where("requisicion_pv.id_colaborador_solicita",$id_colaborador)
        ->where("requisicion_pv.estatus",3)
        ->groupBy("requisicion_pv.id_requisicion_pv")
        ->get();
        
        return $requisiciones;
    }

    public static function getDetalleRequisicionExistente($id){

        /*
                SELECT 
	                requisicion_pv.id_requisicion_pv,
	                requisicion_pv.folio,
	                requisicion_pv.fecha_solicitud,
	                requisicion_pv.estatus,
	                persona.nombre,
	                persona.apellido_paterno,
	                persona.apellido_materno,
	                persona_revisa.nombre,
	                persona_revisa.apellido_paterno,
	                persona_revisa.apellido_materno,
	                persona_autoriza.nombre,
	                persona_autoriza.apellido_paterno,
	                persona_autoriza.apellido_materno,
	                GROUP_CONCAT(requisicion_anidadas.id_requisicion_child) AS ligado
                FROM requisicion_pv 
                INNER JOIN colaborador ON requisicion_pv.id_colaborador_solicita=colaborador.id_colaborador
                INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
                INNER JOIN colaborador AS colaborador_revisa ON requisicion_pv.id_colaborador_revisa=colaborador_revisa.id_colaborador
                INNER JOIN persona AS persona_revisa ON colaborador_revisa.cve_persona=persona_revisa.cve_persona
                INNER JOIN colaborador AS colaborador_autoriza ON requisicion_pv.id_colaborador_autorizo=colaborador_autoriza.id_colaborador
                INNER JOIN persona AS persona_autoriza ON colaborador_autoriza.cve_persona=persona_autoriza.cve_persona
                LEFT JOIN requisicion_anidadas ON requisicion_pv.id_requisicion_pv=requisicion_anidadas.id_requisicion
                WHERE requisicion_pv.id_requisicion_pv=3 GROUP BY requisicion_pv.id_requisicion_pv;
        */

        /*
            SELECT  
	            requisicion_producto_pv.id_requisicion_producto_pv,
	            requisicion_producto_pv.cantidad,
	            requisicion_producto_pv.observaciones,
	            CONCAT_WS(' ',
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.NOMBRE')),''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MATERIAL')),''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA1')),''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.MEDIDA2')),''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.FORMA')),''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.TIPO_CUERDA')),''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(producto_pv.nombre,'$.OTROS')),'')
                            ) AS nombre,
	            producto_pv.clave,
	            producto_pv.descripcion,
	            marca_productos_pv.nombre AS marca,
	            producto_pv.modelo
            FROM requisicion_producto_pv 
            INNER JOIN producto_pv ON requisicion_producto_pv.id_producto_pv=producto_pv.id_producto_pv
            INNER JOIN producto_presentacion_pv ON requisicion_producto_pv.id_producto_presentacion=producto_presentacion_pv.id_producto_presentacion_pv
            INNER JOIN marca_productos_pv ON requisicion_producto_pv.id_marca=marca_productos_pv.id_marca_productos_pv
            INNER JOIN unidad_medido_producto_pv ON producto_presentacion_pv.unidad_medida=unidad_medido_producto_pv.id_unidad_medida_producto_pv
            INNER JOIN pedido_almacen_producto_pv ON requisicion_producto_pv.id_requisicion_producto_pv=pedido_almacen_producto_pv.id_requisicion_producto_pv AND pedido_almacen_producto_pv.estatus=1
            WHERE requisicion_producto_pv.id_requisicion_pv=3 AND requisicion_producto_pv.estatus_revision=1 AND requisicion_producto_pv.estatus_confirmacion=1
        */


        $requisicion=DB::table("requisicion_pv")
                    ->join("colaborador" , "requisicion_pv.id_colaborador_solicita","colaborador.id_colaborador")
                    ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
                    ->join("colaborador AS colaborador_revisa" , "requisicion_pv.id_colaborador_revisa","colaborador_revisa.id_colaborador")
                    ->join("persona AS persona_revisa" , "colaborador_revisa.cve_persona","persona_revisa.cve_persona")
                    ->join("colaborador AS colaborador_autoriza" , "requisicion_pv.id_colaborador_autorizo","colaborador_autoriza.id_colaborador")
                    ->join("persona AS persona_autoriza" , "colaborador_autoriza.cve_persona","persona_autoriza.cve_persona")
                    ->leftJoin("requisicion_anidadas" , "requisicion_pv.id_requisicion_pv","requisicion_anidadas.id_requisicion")
                    ->groupBy("requisicion_pv.id_requisicion_pv")
                    ->where("requisicion_pv.id_requisicion_pv",$id)
                    ->select(
                            "requisicion_pv.id_requisicion_pv",
	                        "requisicion_pv.folio",
	                        "requisicion_pv.fecha_solicitud",
	                        "requisicion_pv.estatus",
	                        "persona.nombre AS nombre_solicita",
	                        "persona.apellido_paterno AS paterno_solicita",
	                        "persona.apellido_materno AS materno_solicita",
	                        "persona_revisa.nombre AS nombre_revisa",
	                        "persona_revisa.apellido_paterno AS paterno_revisa",
	                        "persona_revisa.apellido_materno AS materno_revisa",
	                        "persona_autoriza.nombre AS nombre_autoriza",
	                        "persona_autoriza.apellido_paterno AS paterno_autoriza",
	                        "persona_autoriza.apellido_materno AS materno_autoriza"
                    )
                    ->selectRaw("GROUP_CONCAT(requisicion_anidadas.id_requisicion_child) AS ligado")
                    ->first();
        
        
        
        $requisicion_productos=DB::table("requisicion_producto_pv")
                            ->join("producto_pv" , "requisicion_producto_pv.id_producto_pv","producto_pv.id_producto_pv")
                            ->join("producto_presentacion_pv" , "requisicion_producto_pv.id_producto_presentacion","producto_presentacion_pv.id_producto_presentacion_pv")
                            ->leftJoin("marca_productos_pv" , "requisicion_producto_pv.id_marca","marca_productos_pv.id_marca_productos_pv")
                            ->join("unidad_medido_producto_pv" , "producto_presentacion_pv.unidad_medida","unidad_medido_producto_pv.id_unidad_medida_producto_pv")
                            // ->join("pedido_almacen_producto_pv",function($join){ $join->on("requisicion_producto_pv.id_requisicion_producto_pv","pedido_almacen_producto_pv.id_requisicion_producto_pv")->where("pedido_almacen_producto_pv.estatus",1);})
                            ->where("requisicion_producto_pv.id_requisicion_pv",$id)
                            ->where("requisicion_producto_pv.estatus_revision",1)
                            ->where("requisicion_producto_pv.estatus_confirmacion",1)
                            ->select("requisicion_producto_pv.id_requisicion_producto_pv",
                                     "requisicion_producto_pv.cantidad",
                                     "requisicion_producto_pv.observaciones",
                                     "producto_pv.clave",
	                                 "producto_pv.descripcion",
	                                 "marca_productos_pv.nombre AS marca",
	                                 "producto_pv.modelo")
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

        return ["requisicion"=>$requisicion,"requisicion_productos"=>$requisicion_productos];


    }

    public static function createRequisicionLigada($id)
    {

        // return DB::transaction(function()use($id){
            
        //     $folio=DB::table("requisicion_pv")->max("folio");            

        //     $requisicion_datos=DB::table("requisicion_pv")->select('id_colaborador_solicita', 'id_colaborador_revisa', 'id_colaborador_autorizo')->where('id_requisicion_pv',$id)->first();       

        //     $id_requisicion_new=DB::table("requisicion_pv")->insertGetId(
        //         [
        //             'id_colaborador_solicita'=>$requisicion_datos->id_colaborador_solicita, 
        //             'id_colaborador_revisa'=>$requisicion_datos->id_colaborador_revisa, 
        //             'id_colaborador_autorizo'=>$requisicion_datos->id_colaborador_autorizo, 
        //             'folio'=>$folio+1,
        //             'fecha_solicitud'=>Carbon::now(),
        //             'estatus'=>1
        //         ]);
        
            
        //     DB::table("requisicion_anidadas")->insert(["id_requisicion"=>$id,"id_requisicion_child"=>$id_requisicion_new]);

        //     DB::table('requisicion_producto_pv')->insertUsing(
        //         ['id_requisicion_pv', 'id_producto_pv', 'id_producto_presentacion', 'id_espacio_fisico','id_marca','cantidad','observaciones','estatus_revision','estatus_confirmacion'],
        //         DB::table('requisicion_producto_pv')->select(DB::raw("$id_requisicion_new AS id_requisicion_pv"), 'id_producto_pv', 'id_producto_presentacion', 'id_espacio_fisico','id_marca','cantidad','observaciones',DB::raw("1 AS estatus_revision"),DB::raw('1 AS estatus_confirmacion'))->where('id_requisicion_pv',$id)
        //     );
        //     return $requisicion_datos;
        // });



    }

}
