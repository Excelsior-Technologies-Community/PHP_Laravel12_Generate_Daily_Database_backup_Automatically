<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\BackupRestoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $backupPath = storage_path('app/backup');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'newest');

        $allowedSorts = [
            'newest',
            'oldest',
            'largest',
            'smallest',
            'name_asc',
            'name_desc',
        ];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'newest';
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $files = collect(File::files($backupPath))
            ->filter(function ($file) {
                $ext = strtolower($file->getExtension());
                $name = strtolower($file->getFilename());

                return $ext === 'sql'
                    || str_ends_with($name, '.sql.gz')
                    || str_ends_with($name, '.sql.enc')
                    || str_ends_with($name, '.sql.gz.enc');
            });

        if ($search !== '') {
            $files = $files->filter(function ($file) use ($search) {
                return str_contains(
                    strtolower($file->getFilename()),
                    strtolower($search)
                );
            });
        }

        if ($dateFrom) {
            try {
                $fromTimestamp = Carbon::parse($dateFrom)->startOfDay()->timestamp;
                $files = $files->filter(function ($file) use ($fromTimestamp) {
                    return $file->getMTime() >= $fromTimestamp;
                });
            } catch (\Throwable $e) {
                // Ignore invalid date.
            }
        }

        if ($dateTo) {
            try {
                $toTimestamp = Carbon::parse($dateTo)->endOfDay()->timestamp;
                $files = $files->filter(function ($file) use ($toTimestamp) {
                    return $file->getMTime() <= $toTimestamp;
                });
            } catch (\Throwable $e) {
                // Ignore invalid date.
            }
        }

        switch ($sort) {
            case 'oldest':
                $files = $files->sortBy(fn($file) => $file->getMTime());
                break;

            case 'largest':
                $files = $files->sortByDesc(fn($file) => $file->getSize());
                break;

            case 'smallest':
                $files = $files->sortBy(fn($file) => $file->getSize());
                break;

            case 'name_asc':
                $files = $files->sortBy(fn($file) => strtolower($file->getFilename()));
                break;

            case 'name_desc':
                $files = $files->sortByDesc(fn($file) => strtolower($file->getFilename()));
                break;

            default:
                $files = $files->sortByDesc(fn($file) => $file->getMTime());
                break;
        }

        $files = $files->values();

        $allFiles = collect(File::files($backupPath))
            ->filter(function ($file) {
                $ext = strtolower($file->getExtension());
                $name = strtolower($file->getFilename());

                return $ext === 'sql'
                    || str_ends_with($name, '.sql.gz')
                    || str_ends_with($name, '.sql.enc')
                    || str_ends_with($name, '.sql.gz.enc');
            });

        $totalBackups = $allFiles->count();

        $totalSizeBytes = $allFiles->sum(fn($file) => $file->getSize());

        $latestBackup = $allFiles->sortByDesc(fn($file) => $file->getMTime())->first();
        $oldestBackup = $allFiles->sortBy(fn($file) => $file->getMTime())->first();

        $perPage = 5;
        $currentPage = max(1, (int) $request->get('page', 1));
        $totalFiltered = $files->count();

        $paginatedFiles = $files->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $backups = $paginatedFiles->map(function ($file) {
            return [
                'name' => $file->getFilename(),
                'size' => $this->formatFileSize($file->getSize()),
                'size_bytes' => $file->getSize(),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'created_date' => date('d M Y', $file->getMTime()),
                'created_time' => date('h:i A', $file->getMTime()),
                'age_days' => now()->diffInDays(Carbon::createFromTimestamp($file->getMTime())),
            ];
        });

        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $backups,
            $totalFiltered,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $successfulLogs = BackupLog::where('status', 'success')->count();
        $failedLogs = BackupLog::where('status', 'failed')->count();

        return view('backups.index', [
            'backups' => $pagination,
            'totalBackups' => $totalBackups,
            'totalSize' => $this->formatFileSize($totalSizeBytes),
            'retentionDays' => max(1, (int) env('BACKUP_RETENTION_DAYS', 7)),
            'latestBackup' => $latestBackup,
            'oldestBackup' => $oldestBackup,
            'successfulLogs' => $successfulLogs,
            'failedLogs' => $failedLogs,
            'search' => $search,
            'sort' => $sort,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function create()
    {
        $exitCode = Artisan::call('database:backup');

        if ($exitCode === 0) {
            return redirect()
                ->route('backups.index')
                ->with('success', 'Database backup created successfully!');
        }

        return redirect()
            ->route('backups.index')
            ->with('error', 'Database backup failed. Please check the logs.');
    }

    public function download(string $filename): BinaryFileResponse
    {
        $filename = basename($filename);

        $backupPath = storage_path('app/backup/' . $filename);

        if (!File::exists($backupPath)) {
            abort(404, 'Backup file not found.');
        }

        $mimeType = 'application/octet-stream';

        if (str_ends_with(strtolower($filename), '.sql')) {
            $mimeType = 'application/sql';
        } elseif (str_ends_with(strtolower($filename), '.gz')) {
            $mimeType = 'application/gzip';
        }

        return response()->download($backupPath, $filename, ['Content-Type' => $mimeType]);
    }

    public function destroy(string $filename)
    {
        $filename = basename($filename);

        $backupPath = storage_path('app/backup/' . $filename);

        if (!File::exists($backupPath)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Backup file not found.');
        }

        File::delete($backupPath);

        BackupLog::where('filename', $filename)->delete();

        return redirect()
            ->route('backups.index')
            ->with('success', "Backup '{$filename}' deleted successfully.");
    }

    public function bulkDestroy(Request $request)
    {
        $filenames = $request->input('filenames', []);

        if (!is_array($filenames)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Invalid backup selection.');
        }

        $deletedCount = 0;
        $backupPath = storage_path('app/backup');

        foreach ($filenames as $filename) {
            $filename = basename($filename);
            $filePath = $backupPath . DIRECTORY_SEPARATOR . $filename;

            if (File::exists($filePath)) {
                File::delete($filePath);
                BackupLog::where('filename', $filename)->delete();
                $deletedCount++;
            }
        }

        if ($deletedCount === 0) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'No backups were deleted.');
        }

        return redirect()
            ->route('backups.index')
            ->with('success', "{$deletedCount} backup(s) deleted successfully.");
    }

    public function verify(string $filename)
    {
        $filename = basename($filename);

        $filePath = storage_path('app/backup/' . $filename);

        if (!File::exists($filePath)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Backup file not found.');
        }

        if (File::size($filePath) === 0) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Backup file is empty.');
        }

        $actualChecksum = hash_file('sha256', $filePath);

        $log = BackupLog::where('filename', $filename)
            ->where('status', 'success')
            ->latest()
            ->first();

        if (!$log) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'No successful checksum record found for this backup.');
        }

        if (hash_equals($log->checksum, $actualChecksum)) {
            $log->update(['verified_at' => now()]);

            return redirect()
                ->route('backups.index')
                ->with('success', "Integrity verified successfully for '{$filename}'.");
        }

        return redirect()
            ->route('backups.index')
            ->with('error', "Integrity verification failed for '{$filename}'. The file may have been modified.");
    }

    public function cleanup()
    {
        $backupPath = storage_path('app/backup');

        if (!File::exists($backupPath)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Backup directory does not exist.');
        }

        $retentionDays = max(1, (int) env('BACKUP_RETENTION_DAYS', 7));
        $cutoffTimestamp = now()->subDays($retentionDays)->timestamp;
        $deletedCount = 0;

        $files = File::files($backupPath);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            $basename = strtolower($file->getBasename());

            if (
                $extension !== 'sql'
                && !str_ends_with($basename, '.sql.gz')
                && !str_ends_with($basename, '.sql.enc')
                && !str_ends_with($basename, '.sql.gz.enc')
            ) {
                continue;
            }

            if ($file->getMTime() < $cutoffTimestamp) {
                File::delete($file->getPathname());
                BackupLog::where('filename', $file->getFilename())->delete();
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            return redirect()
                ->route('backups.index')
                ->with('success', "{$deletedCount} old backup(s) cleaned successfully.");
        }

        return redirect()
            ->route('backups.index')
            ->with('success', 'No old backups needed to be removed.');
    }

    public function exportLogs(): StreamedResponse
    {
        $filename = 'backup-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';
        $logs = BackupLog::latest()->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Filename',
                'Status',
                'Size Bytes',
                'Checksum',
                'Compression',
                'Encryption',
                'Cloud Provider',
                'Is Duplicate',
                'Message',
                'Created At',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->filename,
                    $log->status,
                    $log->size_bytes,
                    $log->checksum,
                    $log->compression_type,
                    $log->encryption_type,
                    $log->cloud_provider,
                    $log->is_duplicate ? 'Yes' : 'No',
                    $log->message,
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function restore(Request $request)
    {
        $filename = basename($request->input('filename'));

        $backupPath = storage_path('app/backup/' . $filename);

        if (!File::exists($backupPath)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Backup file not found.');
        }

        $service = new BackupRestoreService();
        $restoration = $service->restore($filename, $request->input('target_database'));

        if ($restoration->status === 'completed') {
            return redirect()
                ->route('backups.index')
                ->with('success', "Database restored successfully from '{$filename}'.");
        }

        return redirect()
            ->route('backups.index')
            ->with('error', "Database restore failed: " . $restoration->error_message);
    }

    public function uploadToCloud(Request $request)
    {
        $filename = basename($request->input('filename'));

        $backupPath = storage_path('app/backup/' . $filename);

        if (!File::exists($backupPath)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Backup file not found.');
        }

        try {
            $cloud = new BackupCloudService();
            $cloud->upload($backupPath);

            BackupLog::where('filename', $filename)->update([
                'cloud_provider' => config('backup.default', 'local'),
                'cloud_path' => config('backup.drivers.' . config('backup.default', 'local') . '.path', 'backups'),
                'uploaded_at' => now(),
            ]);

            return redirect()
                ->route('backups.index')
                ->with('success', "Backup '{$filename}' uploaded to cloud successfully.");
        } catch (Throwable $e) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Cloud upload failed: ' . $e->getMessage());
        }
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $index = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $index), 2) . ' ' . $units[$index];
    }
}

