<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class Colonia extends Model 
{
    protected $table = 'colonia';
    protected $primaryKey = 'cve_colonia';
    public $timestamps = false;
}