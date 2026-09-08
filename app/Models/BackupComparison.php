<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupComparison extends Model
{
    protected $fillable = [
        'backup_a_filename',
        'backup_b_filename',
        'result',
        'compared_by',
        'compared_at',
    ];

    protected $casts = [
        'result' => 'array',
        'compared_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'compared_by');
    }
}
