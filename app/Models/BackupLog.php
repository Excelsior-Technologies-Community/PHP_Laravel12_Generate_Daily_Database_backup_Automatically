<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    protected $fillable = [
        'filename',
        'status',
        'size_bytes',
        'checksum',
        'message',
        'compression_type',
        'encryption_type',
        'cloud_provider',
        'cloud_path',
        'is_duplicate',
        'original_filename',
        'template_id',
        'database_connection_id',
        'verified_at',
        'uploaded_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'is_duplicate' => 'boolean',
        'verified_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'cloud_config' => 'array',
        'notification_channels' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(BackupTemplate::class, 'template_id');
    }
}