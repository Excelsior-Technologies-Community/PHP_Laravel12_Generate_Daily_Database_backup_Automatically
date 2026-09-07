<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $fillable = [
        'filename',
        'status',
        'size_bytes',
        'checksum',
        'message',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];
}