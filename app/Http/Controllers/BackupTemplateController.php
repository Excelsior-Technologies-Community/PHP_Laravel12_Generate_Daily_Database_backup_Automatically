<?php

namespace App\Http\Controllers;

use App\Models\BackupTemplate;
use App\Services\BackupSchedulerService;
use Illuminate\Http\Request;

class BackupTemplateController extends Controller
{
    public function __construct(
        protected BackupSchedulerService $scheduler
    ) {}

    public function index()
    {
        $templates = BackupTemplate::orderBy('name')->get();

        return view('backup-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('backup-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:backup_templates',
            'description' => 'nullable|string',
            'databases' => 'nullable|array',
            'compression_type' => 'nullable|string|max:50',
            'encryption_type' => 'nullable|string|max:50',
            'auto_upload' => 'boolean',
            'cloud_provider' => 'nullable|string|max:50',
            'cloud_config' => 'nullable|array',
            'notification_channels' => 'nullable|array',
            'webhook_urls' => 'nullable|array',
            'retention_days' => 'integer|min:1',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_default'] = $validated['is_default'] ?? false;

        if ($validated['is_default']) {
            BackupTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        $template = $this->scheduler->createTemplate($validated);

        return redirect()
            ->route('backup-templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function edit(BackupTemplate $backupTemplate)
    {
        return view('backup-templates.edit', compact('backupTemplate'));
    }

    public function update(Request $request, BackupTemplate $backupTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:backup_templates,slug,' . $backupTemplate->id,
            'description' => 'nullable|string',
            'databases' => 'nullable|array',
            'compression_type' => 'nullable|string|max:50',
            'encryption_type' => 'nullable|string|max:50',
            'auto_upload' => 'boolean',
            'cloud_provider' => 'nullable|string|max:50',
            'cloud_config' => 'nullable|array',
            'notification_channels' => 'nullable|array',
            'webhook_urls' => 'nullable|array',
            'retention_days' => 'integer|min:1',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_default'] = $validated['is_default'] ?? false;

        if ($validated['is_default']) {
            BackupTemplate::where('is_default', true)->where('id', '!=', $backupTemplate->id)->update(['is_default' => false]);
        }

        $backupTemplate->update($validated);

        return redirect()
            ->route('backup-templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(BackupTemplate $backupTemplate)
    {
        $backupTemplate->delete();

        return redirect()
            ->route('backup-templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
