<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    protected $connection = 'meribia';
    protected $table = 'CARGA';
    protected $primaryKey = 'CODIGO';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];
}
