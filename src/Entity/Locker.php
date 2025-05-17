<?php

namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Persona;


class Locker extends Model 
{
    protected $table = 'locker';
    protected $primaryKey = 'cve_locker';
    public $timestamps = false;

    public function propietario()
    {
        //Persona sabe que su id es cve_persona y propietario es la fk de lockers 
        return $this->belongsTo(Persona::class,'propietario');
    }

    public function rentador()
    {
        //Persona sabe que su id es cve_persona y rentador es la fk de lockers
        return $this->belongsTo(Persona::class,'rentador');
    }

    public function rentadores(){/**es para el historico */

        return $this->belongsToMany(Persona::class,'asignacion_locker','cve_locker','cve_persona')
         ->withPivot('cve_cargo','fecha_incio','fecha_fin','tipo','estatus');
    
    }
}