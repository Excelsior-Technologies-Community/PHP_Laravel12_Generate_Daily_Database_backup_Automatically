<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\BackupRestoration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class BackupRestoreService
{
    public function restore(string $filename, ?string $targetDatabase = null): BackupRestoration
    {
        $restoration = BackupRestoration::create([
            'backup_filename' => $filename,
            'source_database' => env('DB_DATABASE'),
            'target_database' => $targetDatabase ?? env('DB_DATABASE'),
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        try {
            $backupPath = storage_path('app/backup/' . $filename);

            if (!File::exists($backupPath)) {
                throw new \RuntimeException('Backup file not found: ' . $filename);
            }

            $database = $targetDatabase ?? env('DB_DATABASE');
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '3306');
            $username = env('DB_USERNAME', 'root');
            $password = env('DB_PASSWORD', '');

            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'gz') {
                $command = 'gunzip < "' . $backupPath . '" | mysql'
                    . ' --host="' . $host . '"'
                    . ' --port="' . $port . '"'
                    . ' --user="' . $username . '"'
                    . ' --password="' . $password . '"'
                    . ' "' . $database . '"';
            } else {
                $command = 'mysql'
                    . ' --host="' . $host . '"'
                    . ' --port="' . $port . '"'
                    . ' --user="' . $username . '"'
                    . ' --password="' . $password . '"'
                    . ' "' . $database . '"'
                    . ' < "' . $backupPath . '"';
            }

            exec($command . ' 2>&1', $output, $result);

            if ($result !== 0) {
                $message = 'Database restore command failed: ' . implode(PHP_EOL, $output);

                $restoration->update([
                    'status' => 'failed',
                    'error_message' => $message,
                    'completed_at' => now(),
                ]);

                return $restoration;
            }

            $restoration->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return $restoration;
        } catch (Throwable $e) {
            $restoration->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return $restoration;
        }
    }
}
