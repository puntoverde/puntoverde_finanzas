<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;

class TipoOrdenTrabajo extends Model 
{
    protected $table = 'tipo_orden_trabajo';
    protected $primaryKey = 'id_tipo_orden_trabajo';
    public $timestamps = false;
 

}