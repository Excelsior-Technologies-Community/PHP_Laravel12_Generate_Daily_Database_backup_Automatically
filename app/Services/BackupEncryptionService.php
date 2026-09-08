<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupEncryptionService
{
    public function encrypt(string $filePath, string $key, string $cipher = 'AES-256-CBC'): string
    {
        if (!File::exists($filePath)) {
            throw new RuntimeException('File not found: ' . $filePath);
        }

        $iv = random_bytes(openssl_cipher_iv_length($cipher));
        $data = File::get($filePath);
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new RuntimeException('Encryption failed for: ' . $filePath);
        }

        $outputPath = $filePath . '.enc';

        $payload = base64_encode($iv) . '::' . base64_encode($encrypted);

        File::put($outputPath, $payload);

        return $outputPath;
    }

    public function decrypt(string $encryptedPath, string $key, string $cipher = 'AES-256-CBC'): string
    {
        if (!File::exists($encryptedPath)) {
            throw new RuntimeException('Encrypted file not found: ' . $encryptedPath);
        }

        $payload = File::get($encryptedPath);
        $parts = explode('::', $payload, 2);

        if (count($parts) !== 2) {
            throw new RuntimeException('Invalid encrypted file format.');
        }

        [$ivBase64, $encryptedBase64] = $parts;
        $iv = base64_decode($ivBase64);
        $encrypted = base64_decode($encryptedBase64);

        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new RuntimeException('Decryption failed. Invalid key or corrupted file.');
        }

        $outputPath = preg_replace('/\.enc$/', '', $encryptedPath);

        File::put($outputPath, $decrypted);

        return $outputPath;
    }
}
