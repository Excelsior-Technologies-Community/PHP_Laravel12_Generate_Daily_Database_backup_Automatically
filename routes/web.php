<?php

use App\Http\Controllers\Api\BackupApiController;
use App\Http\Controllers\BackupComparisonController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BackupHealthController;
use App\Http\Controllers\BackupScheduleController;
use App\Http\Controllers\BackupSettingsController;
use App\Http\Controllers\BackupTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('backups.index');
});

Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');

Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');

Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');

Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');

Route::post('/backups/{filename}/upload', [BackupController::class, 'uploadToCloud'])->name('backups.upload');

Route::get('/backups/{filename}/verify', [BackupController::class, 'verify'])->name('backups.verify');

Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

Route::delete('/backups/bulk-delete', [BackupController::class, 'bulkDestroy'])->name('backups.bulk-delete');

Route::post('/backups/cleanup', [BackupController::class, 'cleanup'])->name('backups.cleanup');

Route::get('/backups/export-logs', [BackupController::class, 'exportLogs'])->name('backups.export-logs');

Route::get('/backup-templates', [BackupTemplateController::class, 'index'])->name('backup-templates.index');
Route::get('/backup-templates/create', [BackupTemplateController::class, 'create'])->name('backup-templates.create');
Route::post('/backup-templates', [BackupTemplateController::class, 'store'])->name('backup-templates.store');
Route::get('/backup-templates/{backupTemplate}/edit', [BackupTemplateController::class, 'edit'])->name('backup-templates.edit');
Route::put('/backup-templates/{backupTemplate}', [BackupTemplateController::class, 'update'])->name('backup-templates.update');
Route::delete('/backup-templates/{backupTemplate}', [BackupTemplateController::class, 'destroy'])->name('backup-templates.destroy');

Route::get('/backup-schedules', [BackupScheduleController::class, 'index'])->name('backup-schedules.index');
Route::get('/backup-schedules/create', [BackupScheduleController::class, 'create'])->name('backup-schedules.create');
Route::post('/backup-schedules', [BackupScheduleController::class, 'store'])->name('backup-schedules.store');
Route::get('/backup-schedules/{backupSchedule}/edit', [BackupScheduleController::class, 'edit'])->name('backup-schedules.edit');
Route::put('/backup-schedules/{backupSchedule}', [BackupScheduleController::class, 'update'])->name('backup-schedules.update');
Route::delete('/backup-schedules/{backupSchedule}', [BackupScheduleController::class, 'destroy'])->name('backup-schedules.destroy');

Route::get('/backup-comparisons', [BackupComparisonController::class, 'index'])->name('backup-comparisons.index');
Route::post('/backup-comparisons/compare', [BackupComparisonController::class, 'compare'])->name('backup-comparisons.compare');

Route::get('/backup-settings', [BackupSettingsController::class, 'index'])->name('backup-settings.index');
Route::put('/backup-settings', [BackupSettingsController::class, 'update'])->name('backup-settings.update');
Route::post('/backup-settings/notifications', [BackupSettingsController::class, 'storeNotification'])->name('backup-settings.notifications.store');
Route::put('/backup-settings/notifications/{backupNotification}', [BackupSettingsController::class, 'updateNotification'])->name('backup-settings.notifications.update');
Route::delete('/backup-settings/notifications/{backupNotification}', [BackupSettingsController::class, 'destroyNotification'])->name('backup-settings.notifications.destroy');
Route::post('/backup-settings/cloud-configs', [BackupSettingsController::class, 'storeCloudConfig'])->name('backup-settings.cloud-configs.store');
Route::put('/backup-settings/cloud-configs/{backupCloudConfig}', [BackupSettingsController::class, 'updateCloudConfig'])->name('backup-settings.cloud-configs.update');
Route::delete('/backup-settings/cloud-configs/{backupCloudConfig}', [BackupSettingsController::class, 'destroyCloudConfig'])->name('backup-settings.cloud-configs.destroy');

Route::get('/backup-health', [BackupHealthController::class, 'index'])->name('backup-health.index');
