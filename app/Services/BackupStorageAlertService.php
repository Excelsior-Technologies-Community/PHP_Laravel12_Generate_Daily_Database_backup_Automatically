<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\BackupNotification;
use App\Models\BackupSetting;
use App\Models\BackupSchedule;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupStorageAlertService
{
    public function __construct(
        protected BackupNotificationService $notifications
    ) {}

    public function check(): void
    {
        if (!config('backup.storage_alerts.enabled')) {
            return;
        }

        $backupPath = storage_path('app/backup');

        if (!File::exists($backupPath)) {
            return;
        }

        $totalSize = $this->getDirectorySize($backupPath);
        $totalSizeMB = $totalSize / 1024 / 1024;

        $warningThreshold = (float) config('backup.storage_alerts.warning_threshold_mb', 1024);
        $criticalThreshold = (float) config('backup.storage_alerts.critical_threshold_mb', 5120);

        if ($totalSizeMB >= $criticalThreshold) {
            $this->notifications->send('storage.critical', [
                'total_size_mb' => round($totalSizeMB, 2),
                'threshold_mb' => $criticalThreshold,
                'backup_count' => $this->countBackups($backupPath),
            ]);
        } elseif ($totalSizeMB >= $warningThreshold) {
            $this->notifications->send('storage.warning', [
                'total_size_mb' => round($totalSizeMB, 2),
                'threshold_mb' => $warningThreshold,
                'backup_count' => $this->countBackups($backupPath),
            ]);
        }
    }

    private function getDirectorySize(string $directory): int
    {
        $size = 0;

        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    private function countBackups(string $directory): int
    {
        return count(File::files($directory));
    }
}
