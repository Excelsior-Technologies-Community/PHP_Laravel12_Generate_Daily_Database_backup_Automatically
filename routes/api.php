<?php

use App\Http\Controllers\Api\BackupApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('backups')->group(function () {
    Route::get('/', [BackupApiController::class, 'index']);
    Route::post('/', [BackupApiController::class, 'store']);
    Route::get('/{filename}', [BackupApiController::class, 'show']);
    Route::delete('/{filename}', [BackupApiController::class, 'destroy']);
    Route::get('/{filename}/download', [BackupApiController::class, 'download']);
    Route::post('/{filename}/restore', [BackupApiController::class, 'restore']);

    Route::get('/templates', [BackupApiController::class, 'templates']);
    Route::get('/schedules', [BackupApiController::class, 'schedules']);
    Route::get('/health', [BackupApiController::class, 'health']);
});
