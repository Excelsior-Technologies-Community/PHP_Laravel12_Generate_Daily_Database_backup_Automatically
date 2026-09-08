<?php

use App\Services\BackupStorageAlertService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Default Laravel Command
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automated Database Backup
|--------------------------------------------------------------------------
|
| Create a daily database backup and automatically remove
| backups older than BACKUP_RETENTION_DAYS.
|
*/

Schedule::command('database:backup --cleanup')->daily();

/*
|--------------------------------------------------------------------------
| Custom Schedules from Database
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    $activeSchedules = \App\Models\BackupSchedule::where('is_active', true)->get();

    foreach ($activeSchedules as $schedule) {
        try {
            $options = [];

            if ($schedule->compression_type) {
                $options[] = '--compress';
            }

            if ($schedule->encryption_type) {
                $options[] = '--encrypt';
            }

            if ($schedule->auto_upload) {
                $options[] = '--upload';
            }

            if (!empty($schedule->databases)) {
                foreach ($schedule->databases as $database) {
                    Artisan::call('database:backup --databases=' . $database . ' ' . implode(' ', $options));
                }
            } else {
                Artisan::call('database:backup ' . implode(' ', $options));
            }

            app(BackupStorageAlertService::class)->check();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Scheduled backup failed: ' . $e->getMessage());
        }
    }
})->cron(config('backup.schedule.default_cron', '0 0 * * *'))
->timezone(config('backup.schedule.timezone', 'UTC'));