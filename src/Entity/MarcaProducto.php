<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class MarcaProducto extends Model 
{
    protected $table = 'marca_productos_pv';
    protected $primaryKey = 'id_marca_productos_pv';
    public $timestamps = false;
    

    // public function persona()
    // {
    //     return $this->belongsTo(Persona::class,'cve_persona');
    // }

    // public function direccion()
    // {
    //     return $this->belongsTo(Direccion::class,'cve_direccion');
    // }
}