<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\BackupTemplate;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupDuplicateService
{
    public function isDuplicate(string $filePath, string $filename): bool
    {
        $checksum = hash_file('sha256', $filePath);

        if (!$checksum) {
            return false;
        }

        return BackupLog::where('checksum', $checksum)
            ->where('filename', '!=', $filename)
            ->where('status', 'success')
            ->exists();
    }

    public function handleDuplicate(string $filePath, string $filename, string $strategy = 'skip'): ?string
    {
        $isDuplicate = $this->isDuplicate($filePath, $filename);

        if (!$isDuplicate) {
            return null;
        }

        $original = BackupLog::where('checksum', hash_file('sha256', $filePath))
            ->where('filename', '!=', $filename)
            ->where('status', 'success')
            ->latest()
            ->first();

        switch ($strategy) {
            case 'skip':
                return 'skip';

            case 'rename':
                $newFilename = $this->getUniqueFilename($filename);
                return $newFilename;

            case 'overwrite':
                if ($original) {
                    File::delete(storage_path('app/backup/' . $original->filename));
                    $original->delete();
                }
                return $filename;

            default:
                return 'skip';
        }
    }

    private function getUniqueFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        $counter = 1;
        $newFilename = $filename;

        while (File::exists(storage_path('app/backup/' . $newFilename))) {
            $newFilename = $basename . '-' . $counter . ($extension ? '.' . $extension : '');
            $counter++;
        }

        return $newFilename;
    }
}
