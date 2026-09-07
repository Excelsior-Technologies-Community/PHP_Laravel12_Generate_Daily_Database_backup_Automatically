<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    /**
     * Display backup management dashboard.
     */
    public function index()
    {
        $backupPath = storage_path('app/backup');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $files = collect(File::files($backupPath))
            ->filter(function ($file) {
                return strtolower($file->getExtension()) === 'sql';
            })
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            })
            ->values();

        $backups = $files->map(function ($file) {
            return [
                'name' => $file->getFilename(),
                'size' => $this->formatFileSize($file->getSize()),
                'size_bytes' => $file->getSize(),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'created_date' => date('d M Y', $file->getMTime()),
                'created_time' => date('h:i A', $file->getMTime()),
                'age_days' => now()->diffInDays(
                    \Carbon\Carbon::createFromTimestamp($file->getMTime())
                ),
            ];
        });

        $totalBackups = $backups->count();

        $totalSize = $files->sum(function ($file) {
            return $file->getSize();
        });

        return view('backups.index', [
            'backups' => $backups,
            'totalBackups' => $totalBackups,
            'totalSize' => $this->formatFileSize($totalSize),
            'retentionDays' => (int) env('BACKUP_RETENTION_DAYS', 7),
        ]);
    }

    /**
     * Create a new database backup manually.
     */
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
            ->with('error', 'Database backup failed. Please check the Laravel logs.');
    }

    /**
     * Download a backup file.
     */
    public function download(string $filename): BinaryFileResponse
    {
        $filename = basename($filename);

        if (!str_ends_with(strtolower($filename), '.sql')) {
            abort(404);
        }

        $backupPath = storage_path('app/backup/' . $filename);

        if (!File::exists($backupPath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($backupPath, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Delete a backup file.
     */
    public function destroy(string $filename)
    {
        $filename = basename($filename);

        if (!str_ends_with(strtolower($filename), '.sql')) {
            abort(404);
        }

        $backupPath = storage_path('app/backup/' . $filename);

        if (!File::exists($backupPath)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Backup file not found.');
        }

        File::delete($backupPath);

        return redirect()
            ->route('backups.index')
            ->with('success', "Backup '{$filename}' deleted successfully.");
    }

    /**
     * Format bytes into readable size.
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

        $index = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $index), 2)
            . ' '
            . $units[$index];
    }
}