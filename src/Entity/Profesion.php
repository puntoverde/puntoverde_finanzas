<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class Profesion extends Model 
{
    protected $table = 'profesion';
    protected $primaryKey = 'cve_profesion';
    public $timestamps = false;
}