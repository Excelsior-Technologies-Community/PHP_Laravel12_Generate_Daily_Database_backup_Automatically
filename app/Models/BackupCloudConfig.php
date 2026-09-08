<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupCloudConfig extends Model
{
    protected $fillable = [
        'provider',
        'name',
        'credentials',
        'bucket',
        'region',
        'path',
        'endpoint',
        'use_path_style',
        'is_active',
    ];

    protected $casts = [
        'credentials' => 'array',
        'use_path_style' => 'boolean',
        'is_active' => 'boolean',
    ];
}
