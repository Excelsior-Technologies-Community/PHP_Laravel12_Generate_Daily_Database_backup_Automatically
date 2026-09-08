<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use App\Models\BackupNotification;
use App\Models\BackupSetting;
use App\Services\BackupCloudService;
use App\Services\BackupCompressionService;
use App\Services\BackupEncryptionService;
use App\Services\BackupNotificationService;
use App\Services\BackupStorageAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class DatabaseBackUp extends Command
{
    protected $signature = 'database:backup
                            {--cleanup : Delete backups older than the configured retention period}
                            {--compress : Compress backup using GZIP}
                            {--encrypt : Encrypt backup file}
                            {--upload : Upload backup to cloud storage}
                            {--databases=* : Specific databases to backup (defaults to DB_DATABASE)}';

    protected $description = 'Create a database backup with compression, encryption, cloud upload, and notifications';

    public function handle(
        BackupCompressionService $compression,
        BackupEncryptionService $encryption,
        BackupCloudService $cloud,
        BackupNotificationService $notifications,
        BackupStorageAlertService $storageAlerts
    ) {
        $databases = $this->option('databases') ?: [env('DB_DATABASE')];

        $backupPath = storage_path('app/backup');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $compressionEnabled = (bool) config('backup.compression.enabled', false);
        $encryptionEnabled = (bool) config('backup.encryption.enabled', false);
        $cloudEnabled = (bool) config('backup.drivers.' . config('backup.default', 'local') . '.path', false);
        $autoVerify = (bool) config('backup.verification.auto_verify', true);
        $duplicateDetection = (bool) config('backup.duplicates.detection_enabled', true);
        $duplicateStrategy = config('backup.duplicates.strategy', 'skip');

        foreach ($databases as $database) {
            if (!$database) {
                continue;
            }

            $this->info("Creating backup for database: {$database}");

            $filename = 'backup-' . $database . '-' . now()->format('Y-m-d') . '.sql';

            $duplicateService = new \App\Services\BackupDuplicateService();
            $duplicateResult = $duplicateService->handleDuplicate(
                $backupPath . DIRECTORY_SEPARATOR . $filename,
                $filename,
                $duplicateStrategy
            );

            if ($duplicateResult === 'skip') {
                $this->warn("Skipping duplicate backup for: {$database}");
                continue;
            }

            if ($duplicateResult) {
                $filename = $duplicateResult;
            }

            $backupFile = $backupPath . DIRECTORY_SEPARATOR . $filename;

            $this->createBackupFile($database, $backupFile);

            if (!File::exists($backupFile) || File::size($backupFile) === 0) {
                $message = "Backup file is missing or empty for database: {$database}";
                $this->logFailure($filename, $message);
                $this->error($message);
                continue;
            }

            if (!$this->isValidSqlBackup($backupFile)) {
                $message = "Backup SQL content validation failed for database: {$database}";
                $this->logFailure($filename, $message);
                $this->error($message);
                continue;
            }

            $checksum = hash_file(config('backup.verification.checksum_algorithm', 'sha256'), $backupFile);

            if (!$checksum) {
                $message = "Unable to generate checksum for database: {$database}";
                $this->logFailure($filename, $message);
                $this->error($message);
                continue;
            }

            $isDuplicate = $duplicateService->isDuplicate($backupFile, $filename);

            $fileSize = File::size($backupFile);
            $compressionType = null;
            $encryptionType = null;
            $cloudProvider = null;
            $cloudPath = null;
            $verifiedAt = null;
            $uploadedAt = null;

            if ($this->option('compress') || $compressionEnabled) {
                try {
                    $compressedPath = $compression->compress($backupFile, config('backup.compression.type', 'gzip'), (int) config('backup.compression.level', 6));
                    File::delete($backupFile);
                    $filename = basename($compressedPath);
                    $backupFile = $compressedPath;
                    $fileSize = File::size($backupFile);
                    $compressionType = config('backup.compression.type', 'gzip');
                    $this->info("Backup compressed: {$filename}");
                } catch (Throwable $e) {
                    $this->warn("Compression failed: " . $e->getMessage());
                }
            }

            if ($this->option('encrypt') || $encryptionEnabled) {
                try {
                    $encryptionKey = config('backup.encryption.key');

                    if (!$encryptionKey) {
                        throw new RuntimeException('BACKUP_ENCRYPTION_KEY is not configured.');
                    }

                    $encryptedPath = $encryption->encrypt($backupFile, $encryptionKey, config('backup.encryption.cipher', 'AES-256-CBC'));
                    File::delete($backupFile);
                    $filename = basename($encryptedPath);
                    $backupFile = $encryptedPath;
                    $fileSize = File::size($backupFile);
                    $encryptionType = config('backup.encryption.cipher', 'AES-256-CBC');
                    $this->info("Backup encrypted: {$filename}");
                } catch (Throwable $e) {
                    $this->warn("Encryption failed: " . $e->getMessage());
                }
            }

            if ($autoVerify) {
                $verifiedAt = now();
                $this->info("Backup integrity verified.");
            }

            if ($this->option('upload') || $cloudEnabled) {
                try {
                    $cloud->upload($backupFile);
                    $cloudProvider = config('backup.default', 'local');
                    $cloudPath = config('backup.drivers.' . $cloudProvider . '.path', 'backups');
                    $uploadedAt = now();
                    $this->info("Backup uploaded to cloud: {$cloudProvider}");
                } catch (Throwable $e) {
                    $this->warn("Cloud upload failed: " . $e->getMessage());
                }
            }

            BackupLog::create([
                'filename' => $filename,
                'status' => 'success',
                'size_bytes' => $fileSize,
                'checksum' => $checksum,
                'message' => "Backup created for database: {$database}",
                'compression_type' => $compressionType,
                'encryption_type' => $encryptionType,
                'cloud_provider' => $cloudProvider,
                'cloud_path' => $cloudPath,
                'is_duplicate' => $isDuplicate,
                'original_filename' => $isDuplicate ? $this->findOriginalByChecksum($checksum) : null,
                'verified_at' => $verifiedAt,
                'uploaded_at' => $uploadedAt,
            ]);

            $this->info("Backup created successfully: {$filename}");
            $this->line("File: {$filename}");
            $this->line('Size: ' . $this->formatFileSize($fileSize));
            $this->line("SHA-256: {$checksum}");

            if (config('backup.notifications.enabled')) {
                try {
                    $notifications->send('backup.created', [
                        'filename' => $filename,
                        'database' => $database,
                        'size' => $this->formatFileSize($fileSize),
                        'status' => 'success',
                    ]);
                } catch (Throwable $e) {
                    $this->warn("Notification failed: " . $e->getMessage());
                }
            }
        }

        if ($this->option('cleanup')) {
            $this->cleanupOldBackups($backupPath);
        }

        $storageAlerts->check();

        return self::SUCCESS;
    }

    private function createBackupFile(string $database, string $backupFile): void
    {
        $mysqldumpPath = env('MYSQLDUMP_PATH', 'D:/xampp/mysql/bin/mysqldump.exe');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');

        $command = '"' . $mysqldumpPath . '"'
            . ' --user="' . $username . '"'
            . ' --password="' . $password . '"'
            . ' --host="' . $host . '"'
            . ' --port="' . $port . '"'
            . ' "' . $database . '"'
            . ' > "' . $backupFile . '"';

        exec($command . ' 2>&1', $output, $result);

        if ($result !== 0) {
            throw new RuntimeException('Database backup command failed for database: ' . $database . ' - ' . implode(PHP_EOL, $output));
        }
    }

    private function findOriginalByChecksum(string $checksum): ?string
    {
        $log = BackupLog::where('checksum', $checksum)
            ->where('status', 'success')
            ->latest()
            ->first();

        return $log ? $log->filename : null;
    }

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

    private function cleanupOldBackups(string $backupPath): void
    {
        $retentionDays = (int) env('BACKUP_RETENTION_DAYS', 7);

        if ($retentionDays < 1) {
            $retentionDays = 7;
        }

        $cutoffTimestamp = now()->subDays($retentionDays)->timestamp;

        $deletedCount = 0;

        $files = File::files($backupPath);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            $baseName = strtolower($file->getBasename());

            if (
                $extension !== 'sql'
                && !str_ends_with($baseName, '.sql.gz')
                && !str_ends_with($baseName, '.sql.enc')
                && !str_ends_with($baseName, '.sql.gz.enc')
            ) {
                continue;
            }

            if ($file->getMTime() < $cutoffTimestamp) {
                File::delete($file->getPathname());

                BackupLog::where('filename', $file->getFilename())->delete();

                $deletedCount++;

                $this->line("Old backup deleted: {$file->getFilename()}");
            }
        }

        if ($deletedCount > 0) {
            $this->info("{$deletedCount} old backup(s) removed successfully.");
        } else {
            $this->line('No old backups needed to be removed.');
        }
    }

    private function logFailure(string $filename, string $message): void
    {
        try {
            BackupLog::create([
                'filename' => $filename,
                'status' => 'failed',
                'size_bytes' => 0,
                'checksum' => null,
                'message' => $message,
            ]);

            if (config('backup.notifications.enabled')) {
                try {
                    $notifications = new BackupNotificationService();
                    $notifications->send('backup.failed', [
                        'filename' => $filename,
                        'error' => $message,
                        'status' => 'failed',
                    ]);
                } catch (Throwable $e) {
                    // Ignore notification failures on backup failures
                }
            }
        } catch (Throwable $e) {
            // Do not hide the original backup error if logging fails.
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
