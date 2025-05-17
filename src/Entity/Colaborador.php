<?php

namespace App\Entity;
use App\Entity\Usuario;
use Illuminate\Database\Eloquent\Model;


class Colaborador extends Model 
{
    protected $table = 'colaborador';
    protected $primaryKey = 'id_colaborador';
    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('activo', 1);
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class,'sg_usuario_colaborador','id_usuario','id_colaborador')->withPivot('cuenta');
    }

}