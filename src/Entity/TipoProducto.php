<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Persona;
use App\Entity\Direccion;

class TipoProducto extends Model 
{
    protected $table = 'producto_tipo_pv';
    protected $primaryKey = 'id_producto_tipo_pv';
    public $timestamps = false;
}