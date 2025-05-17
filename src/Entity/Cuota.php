<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\TipoAccion;
use App\Entity\Parentesco;

class Cuota extends Model 
{
    protected $table = 'cuota';
    protected $primaryKey = 'cve_cuota';
    public $timestamps = false;

    public function setCuotaAttribute($value)
    {
        $this->attributes['cuota'] = strtoupper($value);
    }

    public function acciones()
    {
        return $this->belongsToMany(TipoAccion::class,"cuota_accion","cve_cuota","cve_tipo_accion");
    }

    public function parentescos()
    {
        return $this->belongsToMany(Parentesco::class,"cuota_parentesco","cve_cuota","cve_parentesco");
    }

}