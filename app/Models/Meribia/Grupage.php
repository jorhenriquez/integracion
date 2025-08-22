<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;

class Grupage extends Model
{
    protected $connection = 'meribia';
    protected $table = 'GRUPAGE';
    protected $primaryKey = 'ID'; // Ajusta si tu PK es diferente
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];
}
