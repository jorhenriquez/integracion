<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
    protected $connection = 'meribia';
    protected $table = 'VIAJES';
    protected $primaryKey = 'CODIGO'; // Ajusta si tu PK es diferente
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];
}
