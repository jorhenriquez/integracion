<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'payload',
        'processed',
        'observaciones',
        'estado',
        'ip',
        'origen',
        'referer',
        'user_agent',
        'received_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'received_at' => 'datetime'
    ];
}