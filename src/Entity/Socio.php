<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Profesion;
use App\Entity\Parentesco;
use App\Entity\Accion;
use App\Entity\Documento;

class Socio extends Model 
{
    protected $table = 'socios';
    protected $primaryKey = 'cve_socio';
    public $timestamps = false;


    public function setFacebookAttribute($value)
    {
        $this->attributes['facebook'] = strtoupper($value);
    }
    public function setInstagramAttribute($value)
    {
        $this->attributes['instagram'] = strtoupper($value);
    }
    public function setTwiterAttribute($value)
    {
        $this->attributes['twiter'] = strtoupper($value);
    }
    public function setGradoEstudioAttribute($value)
    {
        $this->attributes['grado_estudio'] = strtoupper($value);
    }

    public function setInstitucionEscolarAttribute($value)
    {
        $this->attributes['institucion_escolar'] = strtoupper($value);
    }

    public function setInstitucionLaboralAttribute($value)
    {
        $this->attributes['institucion_laboral'] = strtoupper($value);
    }

    public function setGiroInstitucionAttribute($value)
    {
        $this->attributes['giro_institucion'] = strtoupper($value);
    }

    public function setPuestoEjerceAttribute($value)
    {
        $this->attributes['puesto_ejerce'] = strtoupper($value);
    }

    public function setExperienciaAttribute($value)
    {
        $this->attributes['experiencia'] = strtoupper($value);
    }
    
    /**relaciones */

    public function persona()
    {
        return $this->belongsTo(Persona::class,'cve_persona');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class,'cve_direccion');
    }

    public function profesion()
    {
        return $this->belongsTo(Profesion::class,'cve_profesion');
    }

    public function parentesco()
    {
        return $this->belongsTo(Parentesco::class,'cve_parentesco');
    }

    public function accion()
    {
        return $this->belongsTo(Accion::class,'cve_accion');
    }

    public function documentos()
    {
        return $this->belongsToMany(Documento::class,'documento_socio','cve_socio','cve_documento')->withPivot('cve_documento_socio','ruta', 'estatusDocumento','nombre');
    }


}