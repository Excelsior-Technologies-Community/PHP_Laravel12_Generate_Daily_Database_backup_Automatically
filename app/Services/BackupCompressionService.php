<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupCompressionService
{
    public function compress(string $sourcePath, string $algorithm = 'gzip', int $level = 6): string
    {
        if (!File::exists($sourcePath)) {
            throw new RuntimeException('Source file not found: ' . $sourcePath);
        }

        $outputPath = $sourcePath . '.gz';

        $data = File::get($sourcePath);
        $compressed = gzencode($data, $level, FORCE_GZIP);

        if ($compressed === false) {
            throw new RuntimeException('Compression failed for: ' . $sourcePath);
        }

        File::put($outputPath, $compressed);

        return $outputPath;
    }

    public function decompress(string $compressedPath): string
    {
        if (!File::exists($compressedPath)) {
            throw new RuntimeException('Compressed file not found: ' . $compressedPath);
        }

        $data = File::get($compressedPath);
        $decompressed = gzdecode($data);

        if ($decompressed === false) {
            throw new RuntimeException('Decompression failed for: ' . $compressedPath);
        }

        $outputPath = preg_replace('/\.gz$/', '', $compressedPath);

        File::put($outputPath, $decompressed);

        return $outputPath;
    }

    public function getCompressionRatio(string $originalPath, string $compressedPath): float
    {
        $originalSize = File::size($originalPath);
        $compressedSize = File::size($compressedPath);

        if ($originalSize === 0) {
            return 0.0;
        }

        return round((1 - $compressedSize / $originalSize) * 100, 2);
    }
}
