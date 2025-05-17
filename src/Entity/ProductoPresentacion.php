<?php

namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;
use App\Entity\SubCategoriaProductos;
use App\Entity\UnidadMedida;
use App\Entity\MarcaProducto;
use App\Entity\TipoProducto;


class ProductoPresentacion extends Model 
{
    protected $table = 'producto_presentacion_pv';
    protected $primaryKey = 'id_producto_presentacion_pv';
    public $timestamps = false;

    protected $fillable = ['unidad_medida','cantidad'];
    
}