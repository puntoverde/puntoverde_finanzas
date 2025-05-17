<?php
namespace App\Entity;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model 
{
    protected $table = 'documento';
    protected $primaryKey = 'cve_documento';
    public $timestamps = false;
    
    public function setDocumentoAttribute($value)
    {
        $this->attributes['documento'] = strtoupper($value);
    }
}