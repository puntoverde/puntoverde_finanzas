<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class Parentesco extends Model 
{
    protected $table = 'parentescos';
    protected $primaryKey = 'cve_parentesco';
    public $timestamps = false;
}