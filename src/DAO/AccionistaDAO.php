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

class AccionistaDAO {

    public function __construct(){}
    /**
     * 
     */
    public static function insertAccionista($p)
    {   
        

       return DB::transaction(function () use ($p){

        $colonia=Colonia::find($p->cve_colonia);

        $persona=new Persona();
        $persona->nombre=$p->nombre;
        $persona->apellido_paterno=$p->apellido_paterno;
        $persona->apellido_materno=$p->apellido_materno;
        $persona->sexo=$p->sexo;
        $persona->fecha_nacimiento=$p->fecha_nacimiento;
        $persona->cve_pais=$p->cve_pais;
        $persona->curp=$p->curp;
        $persona->rfc=$p->rfc;
        $persona->estado_civil=$p->estado_civil;
        $persona->estatus=1;
        $persona->save();

        $direccion=new Direccion();
        $direccion->calle=$p->calle;
        $direccion->numero_exterior=$p->numero_exterior;
        $direccion->numero_interior=$p->numero_interior;
        $direccion->colonia()->associate($colonia);
        $direccion->save();
      
        $accionista=new Accionista();
        $accionista->celular=$p->celular;
        $accionista->telefono=$p->telefono;
        $accionista->expediente=$p->expediente;
        $accionista->estatus=1;
        
        $accionista->persona()->associate($persona);
        $accionista->direccion()->associate($direccion);
        
        $accionista->save();

        $accion=Accion::find($p->cve_accion);
        $accion->fecha_adquisicion=$p->fecha_adquisicion;
        $accion->accionista()->associate($accionista);
        $accion->save();

        return $accionista->cve_dueno;

        });


    }

    public static function updateAccionista($id,$p){
       
        return DB::transaction(function () use ($id,$p){

            $colonia=Colonia::find($p->cve_colonia);

            $accionista=Accionista::find($id);
            $accionista->celular=$p->celular;
            $accionista->telefono=$p->telefono;
            $accionista->expediente=$p->expediente;
            $accionista->estatus=1;
           

            $persona=Persona::find($accionista->cve_persona);
            $persona->nombre=$p->nombre;
            $persona->apellido_paterno=$p->apellido_paterno;
            $persona->apellido_materno=$p->apellido_materno;
            $persona->sexo=$p->sexo;
            $persona->fecha_nacimiento=$p->fecha_nacimiento;
            $persona->cve_pais=$p->cve_pais;
            $persona->curp=$p->curp;
            $persona->rfc=$p->rfc;
            $persona->estado_civil=$p->estado_civil;
            $persona->estatus=1;
            $persona->save();
    
    
    
            try{
                $direccion=Direccion::findOrFail($accionista->cve_direccion);
                $direccion->calle=$p->calle;
                $direccion->numero_exterior=$p->numero_exterior;
                $direccion->numero_interior=$p->numero_interior;
                $direccion->colonia()->associate($colonia);
                $direccion->save();
            }
            catch(ModelNotFoundException $e){
                    $direccion=new Direccion();
                    $direccion->calle=$p->calle;
                    $direccion->numero_exterior=$p->numero_exterior;
                    $direccion->numero_interior=$p->numero_interior;
                    $direccion->colonia()->associate($colonia);
                    $direccion->save();
                    $accionista->direccion()->associate($direccion);
            }
            $accionista->save(); 
            
            $accion = Accion::find($p->cve_accion);
            $accion->fecha_adquisicion=$p->fecha_adquisicion;
            $accion->save();
            

            return 1;

        });

    }

    public static function findAccionista($id){
      
        $accionista=Accionista::join('acciones','dueno.cve_dueno','acciones.cve_dueno')
        ->join('persona','persona.cve_persona','dueno.cve_persona')
        ->leftJoin('direccion' , 'direccion.cve_direccion','dueno.cve_direccion')
        ->leftJoin('colonia' , 'direccion.cve_colonia' , 'colonia.cve_colonia')
        ->leftJoin('municipio' ,'municipio.cve_municipio', 'colonia.cve_municipio')
        ->leftJoin('estado' , 'estado.cve_estado', 'municipio.cve_estado')
        ->select('dueno.cve_dueno','dueno.cve_persona','dueno.celular','dueno.telefono','dueno.rfc')
        ->addSelect(DB::raw('IFNULL(dueno.cve_direccion,0) AS cve_direccion'))
        ->addSelect('persona.nombre','persona.apellido_paterno','persona.apellido_materno',DB::raw('CONVERT(persona.sexo, SIGNED) AS sexo'),'persona.fecha_nacimiento','persona.cve_pais','persona.curp','persona.rfc')
        ->addSelect('direccion.cve_colonia','direccion.calle','direccion.numero_exterior','direccion.numero_interior')
        ->addSelect('colonia.cve_municipio','colonia.nombre as colonia','colonia.tipo','colonia.cp')
        ->addSelect('acciones.cve_accion','acciones.numero_accion','acciones.clasificacion','acciones.cve_tipo_accion','acciones.fecha_alta','acciones.fecha_baja','acciones.fecha_adquisicion')
        ->addSelect('municipio.nombre as municipio','estado.nombre as estado','dueno.foto')
        ->where('dueno.cve_dueno',$id);
        return $accionista->first();      
    }

    public static function getAccionistas()
    {
        try {
            return Accionista::leftJoin('acciones','dueno.cve_dueno','acciones.cve_dueno')
            ->join('persona','dueno.cve_persona','persona.cve_persona')
            ->groupBy('dueno.cve_dueno')
            ->orderBy('persona.nombre')
            ->orderBy('dueno.cve_dueno')
            ->select('dueno.cve_dueno AS id',DB::raw("CONCAT(persona.nombre, ' ', persona.apellido_paterno,' ',persona.apellido_materno) AS nombre"))
            ->get();            
        } catch (\Exception $e) {            
            return [];
        }
    }

    public static function CambiarDueno($p)
    {        
        try {
            $accion=Accion::find($p->cve_accion);
            $accion->cve_dueno=$p->cve_dueno;
            $accion->fecha_adquisicion=Carbon::now();
            $accion->save();
        } catch (\Exception $e) {return false;} 
    }

    public static function addFoto($id,$foto){
         $accionista=Accionista::find($id);
         $accionista->foto=$foto;
         $accionista->save();
         return 1;
    }
}