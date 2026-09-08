<?php

namespace App\Services;

use App\Models\BackupCloudConfig;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupCloudService
{
    public function upload(string $filePath, ?BackupCloudConfig $config = null): bool
    {
        if (!File::exists($filePath)) {
            throw new RuntimeException('File not found: ' . $filePath);
        }

        $config = $config ?? BackupCloudConfig::where('is_active', true)->first();

        if (!$config) {
            throw new RuntimeException('No active cloud configuration found.');
        }

        switch ($config->provider) {
            case 's3':
                return $this->uploadToS3($filePath, $config);

            case 'google_drive':
                return $this->uploadToGoogleDrive($filePath, $config);

            case 'dropbox':
                return $this->uploadToDropbox($filePath, $config);

            default:
                throw new RuntimeException('Unsupported cloud provider: ' . $config->provider);
        }
    }

    private function uploadToS3(string $filePath, BackupCloudConfig $config): bool
    {
        if (!class_exists(\Aws\S3\S3Client::class)) {
            throw new RuntimeException('AWS SDK not installed. Run: composer require aws/aws-sdk-php');
        }

        $s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $config->region,
            'credentials' => $config->credentials,
            'endpoint' => $config->endpoint,
            'use_path_style_endpoint' => $config->use_path_style,
        ]);

        $key = ($config->path ? rtrim($config->path, '/') . '/' : '') . basename($filePath);

        $result = $s3->putObject([
            'Bucket' => $config->bucket,
            'Key' => $key,
            'SourceFile' => $filePath,
            'ACL' => 'private',
        ]);

        return isset($result['ObjectURL']);
    }

    private function uploadToGoogleDrive(string $filePath, BackupCloudConfig $config): bool
    {
        if (!class_exists(\Google\Client::class)) {
            throw new RuntimeException('Google API Client not installed. Run: composer require google/apiclient');
        }

        $client = new \Google\Client();
        $client->setAuthConfig($config->credentials);
        $client->addScope(\Google\Service\Drive::DRIVE);
        $client->setAccessType('offline');

        if (!empty($config->path)) {
            $client->setPrompt('select_account consent');
        }

        $service = new \Google\Service\Drive($client);

        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name' => basename($filePath),
            'parents' => $config->path ? [$config->path] : [],
        ]);

        $content = File::get($filePath);

        $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        return true;
    }

    private function uploadToDropbox(string $filePath, BackupCloudConfig $config): bool
    {
        if (!class_exists(\Kunal\Dropbox\Dropbox::class) && !class_exists(\Spatie\Dropbox\Client::class)) {
            throw new RuntimeException('Dropbox SDK not installed. Run: composer require spatie/dropbox-driver');
        }

        $client = new \Spatie\Dropbox\Client($config->credentials);

        $dropboxPath = ($config->path ? rtrim($config->path, '/') . '/' : '') . basename($filePath);

        $content = File::get($filePath);

        $client->upload($dropboxPath, $content);

        return true;
    }

    public function download(string $filename, BackupCloudConfig $config): string
    {
        $localPath = storage_path('app/backup/' . $filename);

        if (File::exists($localPath)) {
            return $localPath;
        }

        switch ($config->provider) {
            case 's3':
                $s3 = new \Aws\S3\S3Client([
                    'version' => 'latest',
                    'region' => $config->region,
                    'credentials' => $config->credentials,
                ]);

                $key = ($config->path ? rtrim($config->path, '/') . '/' : '') . $filename;
                $result = $s3->getObject([
                    'Bucket' => $config->bucket,
                    'Key' => $key,
                ]);

                File::put($localPath, $result['Body']);

                return $localPath;

            default:
                throw new RuntimeException('Download not supported for provider: ' . $config->provider);
        }
    }
}
