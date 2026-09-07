<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class DatabaseBackUp extends Command
{
    protected $signature = 'database:backup
                            {--cleanup : Delete backups older than the configured retention period}';

    protected $description = 'Create a database backup, verify its integrity, and optionally clean up old backups';

    public function handle()
    {
        $filename = 'backup-' . now()->format('Y-m-d') . '.sql';

        $backupPath = storage_path('app/backup');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $mysqldumpPath = env(
            'MYSQLDUMP_PATH',
            'D:/xampp/mysql/bin/mysqldump.exe'
        );

        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $database = env('DB_DATABASE');

        if (!$database) {
            $message = 'Database name is missing from .env file.';

            $this->logFailure($filename, $message);

            $this->error($message);

            return self::FAILURE;
        }

        if (!File::exists($mysqldumpPath)) {
            $message = "mysqldump.exe was not found at: {$mysqldumpPath}";

            $this->logFailure($filename, $message);

            $this->error($message);

            $this->line(
                'Please update MYSQLDUMP_PATH in your .env file.'
            );

            return self::FAILURE;
        }

        $backupFile = $backupPath . DIRECTORY_SEPARATOR . $filename;

        /*
        |--------------------------------------------------------------------------
        | Create Database Backup
        |--------------------------------------------------------------------------
        */

        $command = '"' . $mysqldumpPath . '"'
            . ' --user="' . $username . '"'
            . ' --password="' . $password . '"'
            . ' --host="' . $host . '"'
            . ' --port="' . $port . '"'
            . ' "' . $database . '"'
            . ' > "' . $backupFile . '"';

        $output = [];
        $result = 0;

        exec($command . ' 2>&1', $output, $result);

        if ($result !== 0) {
            $message = 'Database backup command failed.';

            if (!empty($output)) {
                $message .= ' ' . implode(PHP_EOL, $output);
            }

            $this->logFailure($filename, $message);

            $this->error('Database backup failed!');

            if (!empty($output)) {
                $this->error(implode(PHP_EOL, $output));
            }

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Backup File
        |--------------------------------------------------------------------------
        */

        if (
            !File::exists($backupFile) ||
            File::size($backupFile) === 0
        ) {
            $message = 'Backup file is missing or empty after the backup command.';

            $this->logFailure($filename, $message);

            $this->error($message);

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Verify SQL Content
        |--------------------------------------------------------------------------
        */

        if (!$this->isValidSqlBackup($backupFile)) {
            $message = 'Backup file was created but SQL content validation failed.';

            $this->logFailure($filename, $message);

            $this->error($message);

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Generate SHA-256 Checksum
        |--------------------------------------------------------------------------
        */

        $checksum = hash_file('sha256', $backupFile);

        if (!$checksum) {
            $message = 'Unable to generate SHA-256 checksum for the backup.';

            $this->logFailure($filename, $message);

            $this->error($message);

            return self::FAILURE;
        }

        $fileSize = File::size($backupFile);

        /*
        |--------------------------------------------------------------------------
        | Save Successful Backup Log
        |--------------------------------------------------------------------------
        */

        BackupLog::create([
            'filename' => $filename,
            'status' => 'success',
            'size_bytes' => $fileSize,
            'checksum' => $checksum,
            'message' => 'Backup created and integrity verified successfully.',
        ]);

        $this->info('Database backup created successfully!');
        $this->line("File: {$filename}");
        $this->line(
            'Size: ' . $this->formatFileSize($fileSize)
        );
        $this->line(
            "SHA-256: {$checksum}"
        );

        /*
        |--------------------------------------------------------------------------
        | Cleanup Old Backups
        |--------------------------------------------------------------------------
        */

        if ($this->option('cleanup')) {
            $this->cleanupOldBackups($backupPath);
        }

        return self::SUCCESS;
    }

    /**
     * Validate that the generated file appears to contain SQL backup data.
     */
    private function isValidSqlBackup(string $backupFile): bool
    {
        $handle = fopen($backupFile, 'rb');

        if (!$handle) {
            return false;
        }

        $content = '';

        while (!feof($handle) && strlen($content) < 1024 * 1024) {
            $content .= fread($handle, 8192);
        }

        fclose($handle);

        $content = strtolower($content);

        $sqlMarkers = [
            'create table',
            'insert into',
            'drop table',
            'database',
            '-- mysql dump',
            'mysqldump',
        ];

        foreach ($sqlMarkers as $marker) {
            if (str_contains($content, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove backups older than the configured retention period.
     */
    private function cleanupOldBackups(string $backupPath): void
    {
        $retentionDays = (int) env(
            'BACKUP_RETENTION_DAYS',
            7
        );

        if ($retentionDays < 1) {
            $retentionDays = 7;
        }

        $cutoffTimestamp = now()
            ->subDays($retentionDays)
            ->timestamp;

        $deletedCount = 0;

        $files = File::files($backupPath);

        foreach ($files as $file) {
            if (
                strtolower($file->getExtension()) !== 'sql'
            ) {
                continue;
            }

            if ($file->getMTime() < $cutoffTimestamp) {
                File::delete($file->getPathname());

                $deletedCount++;

                $this->line(
                    "Old backup deleted: {$file->getFilename()}"
                );
            }
        }

        if ($deletedCount > 0) {
            $this->info(
                "{$deletedCount} old backup(s) removed successfully."
            );
        } else {
            $this->line(
                'No old backups needed to be removed.'
            );
        }
    }

    /**
     * Record failed backup.
     */
    private function logFailure(
        string $filename,
        string $message
    ): void {
        try {
            BackupLog::create([
                'filename' => $filename,
                'status' => 'failed',
                'size_bytes' => 0,
                'checksum' => null,
                'message' => $message,
            ]);
        } catch (Throwable $e) {
            /*
             * Do not hide the original backup error if logging fails.
             */
        }
    }

    /**
     * Format bytes into a readable size.
     */
    private function formatFileSize(int $bytes): string
    {
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

        $index = floor(log($bytes, 1024));

        return round(
            $bytes / pow(1024, $index),
            2
        ) . ' ' . $units[$index];
    }
}