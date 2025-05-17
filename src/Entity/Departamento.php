<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model 
{
    protected $table = 'rh_departamento';
    protected $primaryKey = 'id_departamento';
    public $timestamps = false; 

}