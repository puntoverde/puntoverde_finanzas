<?php

namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;
use App\Entity\SubCategoriaProductos;
use App\Entity\UnidadMedida;
use App\Entity\MarcaProducto;
use App\Entity\TipoProducto;
use App\Entity\ProductoPresentacion;


class Producto extends Model 
{
    protected $table = 'producto_pv';
    protected $primaryKey = 'id_producto_pv';
    // public $timestamps = false;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';

    public function setClaveAttribute($value)
    {
        $this->attributes['clave'] = strtoupper($value);
    }

    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper($value);
    }

    public function setDescripcionAttribute($value)
    {
        $this->attributes['descripcion'] = strtoupper($value);
    }

    public function setModeloAttribute($value)
    {
        $this->attributes['modelo'] = $value?strtoupper($value):null;
    }

    public function subCategoria()
    {
        return $this->belongsTo(SubCategoriaProductos::class,"id_subcategoria");
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class,"id_unidad_medida");
    }

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class,"id_producto_tipo");
    }

    public function marcasProducto()
    {
        return $this->belongsToMany(MarcaProducto::class, 'producto_marca_pv','id_producto_pv','id_marca_productos_pv');
    }

    public function presentaciones()
    {
        //es el id_producto_pv que esta en la tabla de presentaciones
        return $this->hasMany(ProductoPresentacion::class,"id_producto_pv");
    }

}