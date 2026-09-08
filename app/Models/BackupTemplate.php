<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BackupTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'databases',
        'compression_type',
        'encryption_type',
        'auto_upload',
        'cloud_provider',
        'cloud_config',
        'notification_channels',
        'webhook_urls',
        'retention_days',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'databases' => 'array',
        'cloud_config' => 'array',
        'notification_channels' => 'array',
        'webhook_urls' => 'array',
        'auto_upload' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function backupLogs(): HasMany
    {
        return $this->hasMany(BackupLog::class);
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(BackupSchedule::class);
    }
}
