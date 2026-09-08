<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\BackupSchedule;
use App\Models\BackupSetting;
use App\Models\BackupTemplate;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupSchedulerService
{
    public function getSchedules()
    {
        return BackupSchedule::orderBy('name')->get();
    }

    public function createSchedule(array $data): BackupSchedule
    {
        $data['is_active'] = $data['is_active'] ?? true;

        return BackupSchedule::create($data);
    }

    public function updateSchedule(BackupSchedule $schedule, array $data): BackupSchedule
    {
        $schedule->update($data);

        return $schedule;
    }

    public function deleteSchedule(BackupSchedule $schedule): void
    {
        $schedule->delete();
    }

    public function getTemplates()
    {
        return BackupTemplate::orderBy('name')->get();
    }

    public function createTemplate(array $data): BackupTemplate
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;

        return BackupTemplate::create($data);
    }

    public function updateTemplate(BackupTemplate $template, array $data): BackupTemplate
    {
        $template->update($data);

        return $template;
    }

    public function deleteTemplate(BackupTemplate $template): void
    {
        $template->delete();
    }

    public function getSettings(string $group = 'general')
    {
        return BackupSetting::where('group', $group)->get();
    }

    public function setSetting(string $key, $value, string $type = 'string', string $group = 'general', ?string $description = null): BackupSetting
    {
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        } elseif ($type === 'integer') {
            $value = (string) $value;
        }

        $setting = BackupSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        return $setting;
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = BackupSetting::where('key', $key)->first();

        if ($setting) {
            return $setting->typed_value ?? $default;
        }

        return config('backup.' . strtolower($key), $default);
    }
}
