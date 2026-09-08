<?php

namespace App\Services;

use App\Models\BackupComparison;
use App\Models\BackupLog;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupComparisonService
{
    public function compare(string $filenameA, string $filenameB, ?int $userId = null): BackupComparison
    {
        $pathA = storage_path('app/backup/' . $filenameA);
        $pathB = storage_path('app/backup/' . $filenameB);

        if (!File::exists($pathA)) {
            throw new RuntimeException('Backup file not found: ' . $filenameA);
        }

        if (!File::exists($pathB)) {
            throw new RuntimeException('Backup file not found: ' . $filenameB);
        }

        $contentA = File::get($pathA);
        $contentB = File::get($pathB);

        $linesA = $this->parseSqlLines($contentA);
        $linesB = $this->parseSqlLines($contentB);

        $tablesA = $this->extractTables($contentA);
        $tablesB = $this->extractTables($contentB);

        $result = [
            'file_a' => [
                'filename' => $filenameA,
                'size_bytes' => strlen($contentA),
                'tables_count' => count($tablesA),
                'lines_count' => count($linesA),
            ],
            'file_b' => [
                'filename' => $filenameB,
                'size_bytes' => strlen($contentB),
                'tables_count' => count($tablesB),
                'lines_count' => count($linesB),
            ],
            'diff' => [
                'size_diff_bytes' => strlen($contentA) - strlen($contentB),
                'tables_added' => array_values(array_diff($tablesB, $tablesA)),
                'tables_removed' => array_values(array_diff($tablesA, $tablesB)),
                'lines_diff' => count($linesA) - count($linesB),
            ],
            'checksums' => [
                'file_a' => hash_file('sha256', $pathA),
                'file_b' => hash_file('sha256', $pathB),
            ],
            'are_identical' => $contentA === $contentB,
        ];

        return BackupComparison::create([
            'backup_a_filename' => $filenameA,
            'backup_b_filename' => $filenameB,
            'result' => $result,
            'compared_by' => $userId,
            'compared_at' => now(),
        ]);
    }

    private function parseSqlLines(string $content): array
    {
        return array_filter(
            explode("\n", $content),
            fn($line) => trim($line) !== '' && !str_starts_with(trim($line), '--')
        );
    }

    private function extractTables(string $content): array
    {
        preg_match_all('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $content, $matches);

        return array_unique(array_map('strtolower', $matches[1] ?? []));
    }
}
