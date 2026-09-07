<?php

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