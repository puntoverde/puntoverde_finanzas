<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Profesion;
use App\Entity\Parentesco;
use App\Entity\Accion;
use App\Entity\Documento;
use App\Entity\Producto;

class Requisicion extends Model 
{
    protected $table = 'requisicion_pv';
    protected $primaryKey = 'id_requisicion_pv';
    public $timestamps = false;
   
    /**relaciones */

    public function persona_solicita()
    {
        return $this->belongsTo(Persona::class,'id_persona_solicita');
    }
    
    public function persona_revisa()
    {
        return $this->belongsTo(Persona::class,'id_persona_revisa');
    }
    
    public function persona_autoriza()
    {
        return $this->belongsTo(Persona::class,'id_persona_autorizo');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class,'requisicion_producto_pv','id_requisicion_pv','id_producto_pv')
        ->withPivot('id_producto_presentacion','id_espacio_fisico','id_marca','cantidad', 'observaciones','estatus_revision','estatus_confirmacion');
    }


}