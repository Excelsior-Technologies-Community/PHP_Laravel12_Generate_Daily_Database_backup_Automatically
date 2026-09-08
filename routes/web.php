<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\BackupHealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(
        'backups.index'
    );
});

/*
|--------------------------------------------------------------------------
| Backup Management
|--------------------------------------------------------------------------
*/

Route::get('/backups', [
    BackupController::class,
    'index',
])->name('backups.index');

/*
|--------------------------------------------------------------------------
| Create Backup
|--------------------------------------------------------------------------
*/

Route::post('/backups/create', [
    BackupController::class,
    'create',
])->name('backups.create');

/*
|--------------------------------------------------------------------------
| Cleanup Old Backups
|--------------------------------------------------------------------------
*/

Route::post('/backups/cleanup', [
    BackupController::class,
    'cleanup',
])->name('backups.cleanup');

/*
|--------------------------------------------------------------------------
| Export Logs
|--------------------------------------------------------------------------
*/

Route::get('/backups/export-logs', [
    BackupController::class,
    'exportLogs',
])->name('backups.export-logs');

/*
|--------------------------------------------------------------------------
| Bulk Delete
|--------------------------------------------------------------------------
*/

Route::delete('/backups/bulk-delete', [
    BackupController::class,
    'bulkDestroy',
])->name('backups.bulk-delete');

/*
|--------------------------------------------------------------------------
| Download
|--------------------------------------------------------------------------
*/

Route::get('/backups/{filename}/download', [
    BackupController::class,
    'download',
])->name('backups.download');

/*
|--------------------------------------------------------------------------
| Verify Integrity
|--------------------------------------------------------------------------
*/

Route::get('/backups/{filename}/verify', [
    BackupController::class,
    'verify',
])->name('backups.verify');

/*
|--------------------------------------------------------------------------
| Delete Single Backup
|--------------------------------------------------------------------------
*/

Route::delete('/backups/{filename}', [
    BackupController::class,
    'destroy',
])->name('backups.destroy');

/*
|--------------------------------------------------------------------------
| Backup Health
|--------------------------------------------------------------------------
*/

Route::get('/backup-health', [
    BackupHealthController::class,
    'index',
])->name('backup-health.index');
