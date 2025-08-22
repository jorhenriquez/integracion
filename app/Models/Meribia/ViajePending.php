<?php

namespace App\Models\Meribia;

use Illuminate\Database\Eloquent\Model;

class ViajePending extends Model
{
    protected $connection = 'meribia';
    protected $table = 'VIAJES_PENDING'; // Es una vista
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
