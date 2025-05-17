<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
use App\Entity\Accionista;

class Descuento extends Model 
{
    protected $table = 'descuento';
    protected $primaryKey = 'iddescuento';
    public $timestamps = false; 
}