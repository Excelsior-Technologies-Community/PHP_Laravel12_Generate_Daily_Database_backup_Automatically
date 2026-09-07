<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupHealthController extends Controller
{
    public function index()
    {
        $backupPath = storage_path('app/backup');

        if (!File::exists($backupPath)) {
            File::makeDirectory(
                $backupPath,
                0755,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalBackups = BackupLog::where(
            'status',
            'success'
        )->count();

        $successfulBackups = BackupLog::where(
            'status',
            'success'
        )->count();

        $failedBackups = BackupLog::where(
            'status',
            'failed'
        )->count();

        $latestSuccessfulBackup = BackupLog::where(
            'status',
            'success'
        )
            ->latest()
            ->first();

        $latestFailedBackup = BackupLog::where(
            'status',
            'failed'
        )
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Determine Overall Health
        |--------------------------------------------------------------------------
        */

        $healthStatus = 'critical';

        $healthMessage = 'No successful database backup has been recorded yet.';

        $healthClass = 'danger';

        $ageHours = null;

        if ($latestSuccessfulBackup) {
            $ageHours = now()->diffInHours(
                $latestSuccessfulBackup->created_at
            );

            $maxAgeHours = (int) env(
                'BACKUP_HEALTH_MAX_AGE_HOURS',
                26
            );

            if ($ageHours <= $maxAgeHours) {
                $healthStatus = 'healthy';

                $healthMessage =
                    'The latest database backup is recent and healthy.';

                $healthClass = 'success';
            } elseif (
                $ageHours <= ($maxAgeHours * 2)
            ) {
                $healthStatus = 'warning';

                $healthMessage =
                    'The latest database backup is older than expected.';

                $healthClass = 'warning';
            } else {
                $healthStatus = 'critical';

                $healthMessage =
                    'The database backup is significantly overdue.';

                $healthClass = 'danger';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Existing Backup Files
        |--------------------------------------------------------------------------
        */

        $backupFiles = collect(File::files($backupPath))
            ->filter(function ($file) {
                return strtolower(
                    $file->getExtension()
                ) === 'sql';
            })
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            })
            ->values()
            ->map(function ($file) {
                $filename = $file->getFilename();

                $log = BackupLog::where(
                    'filename',
                    $filename
                )
                    ->where(
                        'status',
                        'success'
                    )
                    ->latest()
                    ->first();

                $actualChecksum = hash_file(
                    'sha256',
                    $file->getPathname()
                );

                $checksumMatches = $log &&
                    $log->checksum === $actualChecksum;

                $isNonEmpty = $file->getSize() > 0;

                if (
                    $isNonEmpty &&
                    $checksumMatches
                ) {
                    $status = 'healthy';
                } elseif ($isNonEmpty) {
                    $status = 'warning';
                } else {
                    $status = 'critical';
                }

                return [
                    'filename' => $filename,
                    'size' => $this->formatFileSize(
                        $file->getSize()
                    ),
                    'size_bytes' => $file->getSize(),
                    'created_at' => Carbon::createFromTimestamp(
                        $file->getMTime()
                    ),
                    'checksum' => $actualChecksum,
                    'stored_checksum' => $log?->checksum,
                    'checksum_matches' => $checksumMatches,
                    'status' => $status,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Recent Logs
        |--------------------------------------------------------------------------
        */

        $recentLogs = BackupLog::latest()
            ->take(10)
            ->get();

        return view(
            'backup-health.index',
            compact(
                'totalBackups',
                'successfulBackups',
                'failedBackups',
                'latestSuccessfulBackup',
                'latestFailedBackup',
                'healthStatus',
                'healthMessage',
                'healthClass',
                'ageHours',
                'backupFiles',
                'recentLogs'
            )
        );
    }

    private function formatFileSize(
        int $bytes
    ): string {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $units = [
            'Bytes',
            'KB',
            'MB',
            'GB',
            'TB',
        ];

        $index = floor(
            log($bytes, 1024)
        );

        return round(
            $bytes / pow(1024, $index),
            2
        ) . ' ' . $units[$index];
    }
}