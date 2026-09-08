<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupSchedule extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'cron_expression',
        'timezone',
        'databases',
        'compression_type',
        'encryption_type',
        'auto_upload',
        'cloud_provider',
        'cloud_config',
        'notification_channels',
        'webhook_urls',
        'retention_days',
        'auto_cleanup',
        'auto_verify',
        'is_active',
        'template_id',
    ];

    protected $casts = [
        'databases' => 'array',
        'cloud_config' => 'array',
        'notification_channels' => 'array',
        'webhook_urls' => 'array',
        'auto_upload' => 'boolean',
        'auto_cleanup' => 'boolean',
        'auto_verify' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(BackupTemplate::class);
    }

    public function backupLogs(): HasMany
    {
        return $this->hasMany(BackupLog::class);
    }
}
