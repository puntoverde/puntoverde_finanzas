<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Colonia;

class Direccion extends Model 
{
    protected $table = 'direccion';
    protected $primaryKey = 'cve_direccion';
    public $timestamps = false;

    public function setCalleAttribute($value)
    {
        $this->attributes['calle'] = strtoupper($value);
    }

    public function setNumeroExteriorAttribute($value)
    {
        $this->attributes['numero_exterior'] = strtoupper($value);
    }

    public function setNumeroInteriorAttribute($value)
    {
        $this->attributes['numero_interior'] = strtoupper($value);
    }

    public function accionista()
    {
        return $this->hasOne(Direccion::class,'cve_direccion','cve_direccion');
    }

    public function colonia()
    {
        return $this->belongsTo(Colonia::class,'cve_colonia');
    }

}