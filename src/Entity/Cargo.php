<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model 
{
    protected $table = 'cargo';
    protected $primaryKey = 'cve_cargo';
    public $timestamps = false;
}