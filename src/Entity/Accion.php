<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;

class Accion extends Model 
{
    protected $table = 'acciones';
    protected $primaryKey = 'cve_accion';
    public $timestamps = false;

    public function accionista()
    {
        return $this->belongsTo(Accionista::class,'cve_dueno');
    }   

}