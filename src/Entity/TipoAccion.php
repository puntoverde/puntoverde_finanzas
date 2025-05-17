<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class TipoAccion extends Model 
{
    protected $table = 'tipo_accion';
    protected $primaryKey = 'cve_tipo_accion';
    public $timestamps = false;    
}