<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComunaCatalog extends Model
{
    protected $table = 'comuna_catalog';
    protected $primaryKey = 'codcd';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['codcd','nomcd','nif'];
}
