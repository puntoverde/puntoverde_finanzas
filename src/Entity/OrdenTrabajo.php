<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Departamento;
use App\Entity\Colaborador;
use App\Entity\TipoOrdenTrabajo;
use App\Entity\ClasificacionOrdenTrabajo;

class OrdenTrabajo extends Model 
{
    protected $table = 'orden_trabajo';
    protected $primaryKey = 'id_orden_trabajo';
    public $timestamps = false;

    public function departamento()
    {
        return $this->belongsTo(Departamento::class,'id_departamento');
    }

    public function departamento_dirigido()
    {
        return $this->belongsTo(Departamento::class,'id_departamento_dirigido');
    }
    
    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class,'id_colaborador');
    }

    public function tipo_orden_trabajo()
    {
        return $this->belongsTo(TipoOrdenTrabajo::class,'id_tipo_orden_trabajo');
    }
    
    public function clasificacion_orden_trabajo()
    {
        return $this->belongsTo(ClasificacionOrdenTrabajo::class,'id_clasificacion_orden_trabajo');
    }
   

}