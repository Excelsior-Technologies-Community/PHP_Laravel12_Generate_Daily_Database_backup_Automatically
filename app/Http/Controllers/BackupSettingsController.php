<?php

namespace App\Http\Controllers;

use App\Models\BackupNotification;
use App\Models\BackupSetting;
use App\Models\BackupCloudConfig;
use App\Services\BackupSchedulerService;
use Illuminate\Http\Request;

class BackupSettingsController extends Controller
{
    public function __construct(
        protected BackupSchedulerService $scheduler
    ) {}

    public function index()
    {
        $settings = BackupSetting::orderBy('group')->orderBy('key')->get();
        $notifications = BackupNotification::orderBy('channel')->get();
        $cloudConfigs = BackupCloudConfig::orderBy('provider')->get();

        return view('backup-settings.index', compact('settings', 'notifications', 'cloudConfigs'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'nullable|array',
            'notifications' => 'nullable|array',
            'cloud_configs' => 'nullable|array',
        ]);

        if (!empty($validated['settings'] ?? [])) {
            foreach ($validated['settings'] as $key => $value) {
                $this->scheduler->setSetting($key, $value);
            }
        }

        return redirect()
            ->route('backup-settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function storeNotification(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'config' => 'nullable|array',
            'recipients' => 'nullable|array',
            'events' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        BackupNotification::create($validated);

        return redirect()
            ->route('backup-settings.index')
            ->with('success', 'Notification channel added successfully.');
    }

    public function updateNotification(Request $request, BackupNotification $backupNotification)
    {
        $validated = $request->validate([
            'channel' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'config' => 'nullable|array',
            'recipients' => 'nullable|array',
            'events' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $backupNotification->update($validated);

        return redirect()
            ->route('backup-settings.index')
            ->with('success', 'Notification updated successfully.');
    }

    public function destroyNotification(BackupNotification $backupNotification)
    {
        $backupNotification->delete();

        return redirect()
            ->route('backup-settings.index')
            ->with('success', 'Notification deleted successfully.');
    }

    public function storeCloudConfig(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'credentials' => 'nullable|array',
            'bucket' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'path' => 'nullable|string|max:500',
            'endpoint' => 'nullable|string|max:500',
            'use_path_style' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        BackupCloudConfig::create($validated);

        return redirect()
            ->route('backup-settings.index')
            ->with('success', 'Cloud configuration added successfully.');
    }

    public function updateCloudConfig(Request $request, BackupCloudConfig $backupCloudConfig)
    {
        $validated = $request->validate([
            'provider' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'credentials' => 'nullable|array',
            'bucket' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'path' => 'nullable|string|max:500',
            'endpoint' => 'nullable|string|max:500',
            'use_path_style' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $backupCloudConfig->update($validated);

        return redirect()
            ->route('backup-settings.index')
            ->with('success', 'Cloud configuration updated successfully.');
    }

    public function destroyCloudConfig(BackupCloudConfig $backupCloudConfig)
    {
        $backupCloudConfig->delete();

        return redirect()
            ->route('backup-settings.index')
            ->with('success', 'Cloud configuration deleted successfully.');
    }
}
