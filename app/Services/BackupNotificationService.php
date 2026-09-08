<?php

namespace App\Services;

use App\Models\BackupNotification;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BackupNotificationService
{
    public function send(string $event, array $data = []): void
    {
        $notifications = BackupNotification::where('is_active', true)
            ->get()
            ->filter(function ($notification) use ($event) {
                return in_array($event, $notification->events ?? []);
            });

        foreach ($notifications as $notification) {
            try {
                $this->dispatch($notification, $event, $data);
            } catch (Throwable $e) {
                Log::error('Failed to send backup notification via ' . $notification->channel . ': ' . $e->getMessage());
            }
        }
    }

    private function dispatch(BackupNotification $notification, string $event, array $data): void
    {
        switch ($notification->channel) {
            case 'email':
                $this->sendEmail($notification, $event, $data);
                break;

            case 'slack':
                $this->sendSlack($notification, $event, $data);
                break;

            case 'discord':
                $this->sendDiscord($notification, $event, $data);
                break;

            case 'telegram':
                $this->sendTelegram($notification, $event, $data);
                break;

            default:
                throw new RuntimeException('Unsupported notification channel: ' . $notification->channel);
        }
    }

    private function sendEmail(BackupNotification $notification, string $event, array $data): void
    {
        $recipients = $notification->recipients ?? [];

        if (empty($recipients)) {
            return;
        }

        $subject = $this->getSubject($event, $data);
        $body = $this->getBody($event, $data);

        foreach ($recipients as $recipient) {
            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient)
                    ->subject($subject);
            });
        }
    }

    private function sendSlack(BackupNotification $notification, string $event, array $data): void
    {
        $webhook = config('backup.webhooks.slack');

        if (!$webhook) {
            return;
        }

        $payload = [
            'text' => $this->getSubject($event, $data),
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => $this->getBody($event, $data),
                    ],
                ],
            ],
        ];

        $this->postJson($webhook, $payload);
    }

    private function sendDiscord(BackupNotification $notification, string $event, array $data): void
    {
        $webhook = config('backup.webhooks.discord');

        if (!$webhook) {
            return;
        }

        $color = $event === 'backup.failed' ? 15158332 : 3066993;

        $payload = [
            'embeds' => [
                [
                    'title' => $this->getSubject($event, $data),
                    'description' => $this->getBody($event, $data),
                    'color' => $color,
                ],
            ],
        ];

        $this->postJson($webhook, $payload);
    }

    private function sendTelegram(BackupNotification $notification, string $event, array $data): void
    {
        $botToken = config('backup.webhooks.telegram');
        $chatId = config('backup.webhooks.telegram_chat_id');

        if (!$botToken || !$chatId) {
            return;
        }

        $text = $this->getSubject($event, $data) . "\n\n" . $this->getBody($event, $data);

        file_get_contents("https://api.telegram.org/bot{$botToken}/sendMessage?" . http_build_query([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]));
    }

    private function postJson(string $url, array $payload): void
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function getSubject(string $event, array $data): string
    {
        return match ($event) {
            'backup.created' => 'Backup Created Successfully',
            'backup.failed' => 'Backup Failed',
            'backup.uploaded' => 'Backup Uploaded to Cloud',
            'storage.warning' => 'Storage Warning',
            'storage.critical' => 'Storage Critical',
            default => 'Backup Notification',
        };
    }

    private function getBody(string $event, array $data): string
    {
        $lines = [];

        foreach ($data as $key => $value) {
            $lines[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }

        return implode("\n", $lines);
    }
}
