<?php

namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;


class Persona extends Model 
{
    protected $table = 'persona';
    protected $primaryKey = 'cve_persona';
    public $timestamps = false;

    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper($value);
    }

    public function setApellidoPaternoAttribute($value)
    {
        $this->attributes['apellido_paterno'] = strtoupper($value);
    }

    public function setApellidoMaternoAttribute($value)
    {
        $this->attributes['apellido_materno'] = strtoupper($value);
    }

    public function setCurpAttribute($value)
    {
        $this->attributes['curp'] = strtoupper($value);
    }

    public function setRfcAttribute($value)
    {
        $this->attributes['rfc'] = strtoupper($value);
    }

    public function accionista()
    {
        return $this->hasOne(Accionista::class,'cve_persona','cve_persona');
    }

}