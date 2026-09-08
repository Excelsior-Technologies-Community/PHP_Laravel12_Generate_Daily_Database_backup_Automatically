<?php

namespace App\Http\Controllers;

use App\Models\BackupSchedule;
use App\Services\BackupSchedulerService;
use Illuminate\Http\Request;

class BackupScheduleController extends Controller
{
    public function __construct(
        protected BackupSchedulerService $scheduler
    ) {}

    public function index()
    {
        $schedules = BackupSchedule::orderBy('name')->get();

        return view('backup-schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('backup-schedules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:backup_schedules',
            'description' => 'nullable|string',
            'cron_expression' => 'required|string|max:255',
            'timezone' => 'required|string|max:100',
            'databases' => 'nullable|array',
            'compression_type' => 'nullable|string|max:50',
            'encryption_type' => 'nullable|string|max:50',
            'auto_upload' => 'boolean',
            'cloud_provider' => 'nullable|string|max:50',
            'cloud_config' => 'nullable|array',
            'notification_channels' => 'nullable|array',
            'webhook_urls' => 'nullable|array',
            'retention_days' => 'integer|min:1',
            'auto_cleanup' => 'boolean',
            'auto_verify' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $this->scheduler->createSchedule($validated);

        return redirect()
            ->route('backup-schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    public function edit(BackupSchedule $backupSchedule)
    {
        return view('backup-schedules.edit', compact('backupSchedule'));
    }

    public function update(Request $request, BackupSchedule $backupSchedule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:backup_schedules,slug,' . $backupSchedule->id,
            'description' => 'nullable|string',
            'cron_expression' => 'required|string|max:255',
            'timezone' => 'required|string|max:100',
            'databases' => 'nullable|array',
            'compression_type' => 'nullable|string|max:50',
            'encryption_type' => 'nullable|string|max:50',
            'auto_upload' => 'boolean',
            'cloud_provider' => 'nullable|string|max:50',
            'cloud_config' => 'nullable|array',
            'notification_channels' => 'nullable|array',
            'webhook_urls' => 'nullable|array',
            'retention_days' => 'integer|min:1',
            'auto_cleanup' => 'boolean',
            'auto_verify' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $backupSchedule->update($validated);

        return redirect()
            ->route('backup-schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(BackupSchedule $backupSchedule)
    {
        $backupSchedule->delete();

        return redirect()
            ->route('backup-schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }
}
