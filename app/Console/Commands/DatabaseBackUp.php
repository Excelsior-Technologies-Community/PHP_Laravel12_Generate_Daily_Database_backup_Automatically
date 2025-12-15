<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackUp extends Command
{
    /**
     * Command name used in terminal
     *
     * Example:
     * php artisan database:backup
     */
    protected $signature = 'database:backup';

    /**
     * Command description
     */
    protected $description = 'Create daily database backup';

    /**
     * Execute the console command
     */
    public function handle()
{
    // Backup file name
    $filename = 'backup-' . now()->format('Y-m-d') . '.sql';

    // Backup directory
    $backupPath = storage_path('app/backup');

    // Create directory if not exists
    if (!is_dir($backupPath)) {
        mkdir($backupPath, 0755, true);
    }

    // CORRECT mysqldump path from your XAMPP 8.2
    $mysqldumpPath = 'C:\\xampp8.2\\mysql\\bin\\mysqldump.exe';

    // Build command (Windows compatible)
    $command = "\"{$mysqldumpPath}\" --user=" . env('DB_USERNAME') .
               " --password=" . env('DB_PASSWORD') .
               " --host=" . env('DB_HOST') .
               " " . env('DB_DATABASE') .
               " > \"{$backupPath}\\{$filename}\"";

    // Execute command
    exec($command, $output, $result);

    if ($result === 0) {
        $this->info('Database backup created successfully!');
    } else {
        $this->error('Database backup failed!');
    }
}
}
