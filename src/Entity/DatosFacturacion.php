<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;

class DatosFacturacion extends Model 
{
    protected $table = 'datos_facturacion';
    protected $primaryKey = 'id_datos_facturacion';
    public $timestamps = false;

    public function persona()
    {
        return $this->belongsTo(Persona::class,'cve_persona');
    }

    public function setRfcAttribute($value)
    {
        $this->attributes['rfc'] = mb_strtoupper($value, 'UTF-8');
    }

    public function setRazonSocialAttribute($value)
    {
        $this->attributes['razon_social'] =  mb_strtoupper($value, 'UTF-8');
    }

    public function setCorreoAttribute($value)
    {
        $this->attributes['correo'] =  strtolower($value);
    }

    public function setCalleAttribute($value)
    {
        $this->attributes['calle'] =  mb_strtoupper($value, 'UTF-8');
    }

    public function setColoniaAttribute($value)
    {
        $this->attributes['colonia'] =  mb_strtoupper($value, 'UTF-8');
    }

    public function setMunicipioAttribute($value)
    {
        $this->attributes['municipio'] =  mb_strtoupper($value, 'UTF-8');
    }

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] =  mb_strtoupper($value, 'UTF-8');
    }

    public function setPaisAttribute($value)
    {
        $this->attributes['pais'] =  mb_strtoupper($value, 'UTF-8');
    }

}