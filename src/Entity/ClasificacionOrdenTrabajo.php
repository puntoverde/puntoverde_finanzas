<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;

class ClasificacionOrdenTrabajo extends Model 
{
    protected $table = 'orden_trabajo_clasificacion';
    protected $primaryKey = 'id_orden_trabajo_clasificacion';
    public $timestamps = false;
 

}