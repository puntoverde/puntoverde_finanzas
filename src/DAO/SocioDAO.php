<?php
namespace App\DAO;
use App\Entity\Socio;
use App\Entity\Persona;
use App\Entity\Colonia;
use App\Entity\Direccion;
use App\Entity\Accion;
use App\Entity\Profesion;
use App\Entity\Parentesco;
use Illuminate\Support\Facades\DB;

class SocioDAO {

    public function __construct(){}
    /**
     * 
     */
    
    public static function getSociosCambio(){
        $socios=Socio::leftJoin('acciones','socios.cve_accion','acciones.cve_accion')
        ->join('persona','socios.cve_persona','persona.cve_persona')
        ->where('persona.estatus',1)
        ->groupBy('socios.cve_socio')
        ->orderBy('persona.nombre')
        ->orderBy('socios.cve_socio')
        ->select('socios.cve_socio AS id',DB::raw("CONCAT(persona.nombre, ' ', persona.apellido_paterno,' ',persona.apellido_materno) As nombre"));
        return $socios->get();      
    }

    public static function insertSocio($p)
    {   
        

       return DB::transaction(function () use ($p){

        $colonia=Colonia::find($p->cve_colonia);
        $profesion=Profesion::find($p->cve_profesion);
        $parentesco=Parentesco::find($p->cve_parentesco);
        $accion=Accion::find($p->cve_accion);

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
      
        $socio=new Socio();
        $socio->posicion=$p->posicion;
        $socio->celular=$p->celular;
        $socio->telefono=$p->telefono;
        $socio->correo_electronico=$p->correo;
        $socio->facebook=$p->facebook;
        $socio->instagram=$p->instagram;
        $socio->twiter=$p->twiter;
        $socio->grado_estudio=$p->grado_estudio;
        $socio->institucion_escolar=$p->institucion_escolar;
        $socio->institucion_laboral=$p->institucion_laboral;
        $socio->puesto_ejerce=$p->puesto_ejerce;
        $socio->experiencia=$p->experiencia;
        $socio->giro_institucion=$p->giro_institucion;
        $socio->estado_accion=$p->estado_accion;
        $socio->fecha_alta=$p->fecha_alta;
        $socio->fecha_ingreso_club=$p->fecha_ingreso_club;
        $socio->estatus=1;
        
        $socio->persona()->associate($persona);
        $socio->direccion()->associate($direccion);
        $socio->profesion()->associate($profesion);
        $socio->parentesco()->associate($parentesco);
        $socio->accion()->associate($accion);
        
        $socio->save();        

        return 1;

        });


    }

    public static function updateSocio($id,$p){
       
        return DB::transaction(function () use ($id,$p){

            $colonia=Colonia::find($p->cve_colonia);
            $profesion=Profesion::find($p->cve_profesion);
            $parentesco=Parentesco::find($p->cve_parentesco);
    
            $persona=Persona::find($p->cve_persona);
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
    
            $direccion=Direccion::find($p->cve_direccion);
            $direccion->calle=$p->calle;
            $direccion->numero_exterior=$p->numero_exterior;
            $direccion->numero_interior=$p->numero_interior;
            $direccion->colonia()->associate($colonia);
            $direccion->save();
          
            $socio=Socio::find($id);
            //$socio->posicion=$p->posicion;
            $socio->celular=$p->celular;
            $socio->telefono=$p->telefono;
            $socio->correo_electronico=$p->correo;
            $socio->facebook=$p->facebook;
            $socio->instagram=$p->instagram;
            $socio->twiter=$p->twiter;
            $socio->grado_estudio=$p->grado_estudio;
            $socio->institucion_escolar=$p->institucion_escolar;
            $socio->institucion_laboral=$p->institucion_laboral;
            $socio->puesto_ejerce=$p->puesto_ejerce;
            $socio->experiencia=$p->experiencia;
            $socio->giro_institucion=$p->giro_institucion;
            $socio->estado_accion=$p->estado_accion;
            $socio->fecha_alta=$p->fecha_alta;
            $socio->fecha_ingreso_club=$p->fecha_ingreso_club;
            $socio->estatus=1;
            
            $socio->profesion()->associate($profesion);
            $socio->parentesco()->associate($parentesco);
            
            $socio->save();        
    
            return 1;
    
            });

    }

    public static function getSocioById($id){
        $socios=Socio::join('persona','socios.cve_persona','persona.cve_persona')
        ->join('acciones' , 'socios.cve_accion','acciones.cve_accion')
        ->join('profesion' , 'socios.cve_profesion','profesion.cve_profesion')
        ->join('parentescos' , 'socios.cve_parentesco','parentescos.cve_parentesco')
        ->leftJoin('direccion' , 'socios.cve_direccion','direccion.cve_direccion')
        ->leftJoin('colonia' , 'direccion.cve_colonia' , 'colonia.cve_colonia')
        ->leftJoin('municipio' ,'municipio.cve_municipio', 'colonia.cve_municipio')
        ->leftJoin('estado' , 'estado.cve_estado', 'municipio.cve_estado')
        ->select('socios.cve_socio','socios.cve_persona','socios.celular','socios.telefono','socios.correo_electronico','socios.fecha_alta','persona.estado_civil')
        ->addSelect('socios.facebook','socios.instagram','socios.twiter','socios.experiencia','socios.giro_institucion',DB::raw('CONVERT(socios.estado_accion, SIGNED) as estado_accion')) 
        ->addSelect('socios.grado_estudio','socios.institucion_escolar','socios.institucion_laboral','socios.puesto_ejerce','socios.fecha_ingreso_club')
        ->addSelect(DB::raw('IFNULL(socios.cve_direccion,0) AS cve_direccion'))
        ->addSelect('persona.nombre','persona.apellido_paterno','persona.apellido_materno',DB::raw('CONVERT(persona.sexo, SIGNED) AS sexo'),'persona.fecha_nacimiento','persona.cve_pais','persona.curp','persona.rfc')
        ->addSelect('direccion.cve_colonia','direccion.calle','direccion.numero_exterior','direccion.numero_interior')
        ->addSelect('colonia.cve_municipio','colonia.nombre as colonia','colonia.tipo','colonia.cp')
        ->addSelect('municipio.nombre as municipio','estado.nombre as estado')
        ->addSelect('socios.posicion','acciones.numero_accion','parentescos.cve_parentesco','profesion.cve_profesion')
        ->addSelect(DB::raw('TIMESTAMPDIFF(YEAR,persona.fecha_nacimiento,CURDATE()) AS edad'))
        ->where('socios.cve_socio',$id);
        return $socios->first();      
    }

    public static function getSociosByAccion($cve_accion){
       $socios=Socio::join('persona','socios.cve_persona','persona.cve_persona')
        ->join('acciones' , 'socios.cve_accion','acciones.cve_accion')
        ->join('parentescos' , 'socios.cve_parentesco','parentescos.cve_parentesco')
        ->select('socios.cve_socio','socios.cve_persona','socios.celular','socios.telefono')
        ->addSelect('persona.nombre','persona.apellido_paterno','persona.apellido_materno','persona.fecha_nacimiento','persona.curp','persona.rfc')
        ->addSelect('socios.posicion','acciones.numero_accion','acciones.clasificacion','parentescos.cve_parentesco','parentescos.nombre AS parentesco')
        ->addSelect(DB::raw('TIMESTAMPDIFF(YEAR,persona.fecha_nacimiento,CURDATE()) AS edad'))
        ->addSelect('socios.acceso_sin_huella','socios.bloqueo_temporal','socios.observaciones','socios.cve_accion','socios.foto')
        ->where('socios.cve_accion',$cve_accion??0) 
        ->orderBy('socios.posicion');
        return $socios->get();  
    }

    public static function getPosicionesByAccion($cve_accion)
    {
      return Socio::where('cve_accion',$cve_accion??0)
      ->where('estatus',1)
      ->select('posicion')
      ->orderBy('posicion','asc')
      ->get();
    }

    public static function getPosicionesByAccionAndClasificacion($p)
    {
      return Socio::join('acciones','socios.cve_accion','acciones.cve_accion')
      ->where('numero_accion',$p->numero_accion)
      ->where('clasificacion',$p->clasificacion)
      ->select('acciones.cve_accion','socios.posicion','acciones.estatus')
      ->orderBy('posicion','asc')
      ->get();
    }

    public static function bajaSocio($id){
        $socio=Socio::find($id);
        $socio->cve_accion=null;
        $socio->save();
    }
    
    public static function updateParams($id,$p){
        try{
        $socio=Socio::find($id);
        if($p->acceso_huella??false)$socio->acceso_sin_huella=$p->acceso_huella;
        if($p->bloqueo_temporal??false)$socio->bloqueo_temporal=$p->bloqueo_temporal;
        if($p->observaciones??false)$socio->observaciones=$p->observaciones;
        if($p->cve_accion??false)$socio->cve_accion=$p->cve_accion;
        if(is_int($p->posicion??false))$socio->posicion=$p->posicion;
        if($p->foto??false)$socio->foto=$p->foto;     
        $socio->save();
        return 1;
        }
        catch(\Exception $e){return 0;}
    }

    public static function getDocumentos($id){
        return DB::table('documento_socio')
        ->rightJoin('documento',function($join) use($id){
         $join->on('documento_socio.cve_documento','documento.cve_documento')->where('cve_socio',$id);})        
        ->select('documento.cve_documento','documento_socio.cve_documento_socio')
        ->addSelect('documento.documento','documento.tipo','documento_socio.ruta','documento_socio.estatusDocumento')
        ->get();
    }

    public static function setDocumento($id,$p){        
        $socio=Socio::find($id);
        $socio->documentos()->detach($p->cve_documento);
        $socio->documentos()->attach($p->cve_documento,['ruta'=>$p->documento,'estatusDocumento'=>1,'nombre'=>'nom']);
        return $socio->documentos()->wherePivot('cve_documento',$p->cve_documento)->first()->pivot->cve_documento_socio;
    }

    public static function deleteDocumento($id,$cve_documento){        
        $socio=Socio::find($id);
        $socio->documentos()->detach($cve_documento);
    }
}