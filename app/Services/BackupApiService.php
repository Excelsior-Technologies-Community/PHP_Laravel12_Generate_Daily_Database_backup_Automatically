<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\BackupTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class BackupApiService
{
    public function listBackups()
    {
        return BackupLog::orderByDesc('created_at')->get();
    }

    public function getBackup(string $filename)
    {
        $log = BackupLog::where('filename', $filename)->firstOrFail();

        return $log;
    }

    public function createBackup(array $options = []): BackupLog
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('database:backup');

        if ($exitCode !== 0) {
            throw new RuntimeException('Backup creation failed.');
        }

        $filename = 'backup-' . now()->format('Y-m-d') . '.sql';

        return BackupLog::latest()->first();
    }

    public function deleteBackup(string $filename): void
    {
        $log = BackupLog::where('filename', $filename)->firstOrFail();

        \Illuminate\Support\Facades\File::delete(storage_path('app/backup/' . $filename));

        $log->delete();
    }

    public function downloadBackup(string $filename)
    {
        $this->getBackup($filename);

        $path = storage_path('app/backup/' . $filename);

        return response()->download($path);
    }

    public function restoreBackup(string $filename, ?string $targetDatabase = null)
    {
        $service = new BackupRestoreService();

        return $service->restore($filename, $targetDatabase);
    }

    public function getTemplates()
    {
        return BackupTemplate::where('is_active', true)->get();
    }

    public function getSchedules()
    {
        return BackupSchedule::where('is_active', true)->get();
    }

    public function getHealth()
    {
        $total = BackupLog::count();
        $success = BackupLog::where('status', 'success')->count();
        $failed = BackupLog::where('status', 'failed')->count();

        $latest = BackupLog::orderByDesc('created_at')->first();

        return [
            'total_backups' => $total,
            'successful_backups' => $success,
            'failed_backups' => $failed,
            'latest_backup' => $latest ? [
                'filename' => $latest->filename,
                'created_at' => $latest->created_at,
                'status' => $latest->status,
            ] : null,
        ];
    }
}
