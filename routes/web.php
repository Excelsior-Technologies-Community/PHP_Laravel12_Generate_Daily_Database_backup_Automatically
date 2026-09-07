<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\BackupHealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('backups.index');
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

Route::post('/backups/create', [
    BackupController::class,
    'create',
])->name('backups.create');

Route::get('/backups/{filename}/download', [
    BackupController::class,
    'download',
])->name('backups.download');

Route::delete('/backups/{filename}', [
    BackupController::class,
    'destroy',
])->name('backups.destroy');

/*
|--------------------------------------------------------------------------
| Backup Health Monitoring
|--------------------------------------------------------------------------
*/

Route::get('/backup-health', [
    BackupHealthController::class,
    'index',
])->name('backup-health.index');