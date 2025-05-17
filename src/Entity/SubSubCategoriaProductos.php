<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class SubSubCategoriaProductos extends Model 
{
    protected $table = 'subsubcategoria_producto_pv';
    protected $primaryKey = 'id_subsubcategoria_producto_pv';
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