<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'payload',
        'processed',
        'received_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'received_at' => 'datetime'
    ];
}