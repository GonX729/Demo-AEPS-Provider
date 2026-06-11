<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'service', 'direction', 'endpoint', 'method', 'reference',
        'request_payload', 'response_payload', 'status_code',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
    ];
}
