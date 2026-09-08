<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupNotification extends Model
{
    protected $fillable = [
        'channel',
        'name',
        'config',
        'recipients',
        'events',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'recipients' => 'array',
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    public function backupLogs(): HasMany
    {
        return $this->hasMany(BackupLog::class);
    }
}
