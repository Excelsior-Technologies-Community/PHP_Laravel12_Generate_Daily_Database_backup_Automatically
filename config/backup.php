<?php

return [
    'default' => env('BACKUP_DRIVER', 'local'),

    'drivers' => [
        'local' => [
            'path' => storage_path('app/backup'),
            'keep_files' => true,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('BACKUP_AWS_ACCESS_KEY_ID'),
            'secret' => env('BACKUP_AWS_SECRET_ACCESS_KEY'),
            'region' => env('BACKUP_AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('BACKUP_AWS_BUCKET'),
            'path' => env('BACKUP_AWS_PATH', 'backups'),
        ],

        'google_drive' => [
            'driver' => 'google',
            'client_id' => env('BACKUP_GOOGLE_CLIENT_ID'),
            'client_secret' => env('BACKUP_GOOGLE_CLIENT_SECRET'),
            'refresh_token' => env('BACKUP_GOOGLE_REFRESH_TOKEN'),
            'folder_id' => env('BACKUP_GOOGLE_FOLDER_ID'),
        ],

        'dropbox' => [
            'driver' => 'dropbox',
            'access_token' => env('BACKUP_DROPBOX_ACCESS_TOKEN'),
            'path' => env('BACKUP_DROPBOX_PATH', '/backups'),
        ],
    ],

    'compression' => [
        'enabled' => env('BACKUP_COMPRESSION_ENABLED', false),
        'type' => env('BACKUP_COMPRESSION_TYPE', 'gzip'),
        'level' => (int) env('BACKUP_COMPRESSION_LEVEL', 6),
    ],

    'encryption' => [
        'enabled' => env('BACKUP_ENCRYPTION_ENABLED', false),
        'driver' => env('BACKUP_ENCRYPTION_DRIVER', 'openssl'),
        'cipher' => env('BACKUP_ENCRYPTION_CIPHER', 'AES-256-CBC'),
        'key' => env('BACKUP_ENCRYPTION_KEY'),
    ],

    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', false),
        'channels' => [
            'email' => env('BACKUP_NOTIFY_EMAIL', false),
            'slack' => env('BACKUP_NOTIFY_SLACK', false),
            'discord' => env('BACKUP_NOTIFY_DISCORD', false),
            'telegram' => env('BACKUP_NOTIFY_TELEGRAM', false),
        ],
        'recipients' => explode(',', env('BACKUP_NOTIFY_RECIPIENTS', '')),
    ],

    'webhooks' => [
        'slack' => env('BACKUP_WEBHOOK_SLACK'),
        'discord' => env('BACKUP_WEBHOOK_DISCORD'),
        'telegram' => env('BACKUP_WEBHOOK_TELEGRAM_BOT_TOKEN'),
        'telegram_chat_id' => env('BACKUP_WEBHOOK_TELEGRAM_CHAT_ID'),
    ],

    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', true),
        'default_cron' => env('BACKUP_SCHEDULE_DEFAULT_CRON', '0 0 * * *'),
        'timezone' => env('BACKUP_SCHEDULE_TIMEZONE', 'UTC'),
    ],

    'retention' => [
        'days' => (int) env('BACKUP_RETENTION_DAYS', 7),
        'auto_cleanup' => env('BACKUP_RETENTION_AUTO_CLEANUP', true),
    ],

    'verification' => [
        'auto_verify' => env('BACKUP_AUTO_VERIFY', true),
        'checksum_algorithm' => env('BACKUP_CHECKSUM_ALGORITHM', 'sha256'),
    ],

    'duplicates' => [
        'detection_enabled' => env('BACKUP_DUPLICATE_DETECTION', true),
        'strategy' => env('BACKUP_DUPLICATE_STRATEGY', 'skip'),
    ],

    'storage_alerts' => [
        'enabled' => env('BACKUP_STORAGE_ALERTS_ENABLED', true),
        'warning_threshold_mb' => (int) env('BACKUP_STORAGE_WARNING_THRESHOLD_MB', 1024),
        'critical_threshold_mb' => (int) env('BACKUP_STORAGE_CRITICAL_THRESHOLD_MB', 5120),
    ],
];
