<?php

namespace App\Libraries;

class SecurityNotificationService
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Send notification for suspicious security activity.
     */
    public function notifySuspiciousActivity(string $message, array $context = []): bool
    {
        $recipients = $this->resolveRecipients();
        $payload = [
            'message' => $message,
            'context' => $context,
            'occurred_at' => date('Y-m-d H:i:s'),
        ];

        $sent = false;
        if ($recipients !== []) {
            $sent = $this->sendEmail($message, $payload, $recipients);
        }

        $this->db->table('audit_logs')->insert([
            'user_id' => isset($context['user_id']) && is_numeric((string) $context['user_id']) ? (int) $context['user_id'] : null,
            'action' => 'security_alert_notification',
            'resource_type' => 'security',
            'resource_id' => null,
            'ip_address' => isset($context['ip_address']) ? (string) $context['ip_address'] : null,
            'user_agent' => isset($context['user_agent']) ? (string) $context['user_agent'] : null,
            'details' => json_encode([
                'delivery' => $sent ? 'email_sent' : 'email_not_sent',
                'recipients' => $recipients,
                'payload' => $payload,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'status' => $sent ? 'success' : 'warning',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $sent;
    }

    /**
     * @return string[]
     */
    private function resolveRecipients(): array
    {
        $configured = (string) env('SECURITY_ALERT_RECIPIENTS', '');
        if ($configured === '') {
            $configured = (string) env('security.alert_recipients', '');
        }

        if ($configured === '') {
            $configured = (string) (config('Email')->recipients ?? '');
        }

        $parts = preg_split('/[;,]+/', $configured) ?: [];
        $normalized = array_values(array_filter(array_map(static function (string $recipient): string {
            return trim($recipient);
        }, $parts), static function (string $recipient): bool {
            return $recipient !== '' && filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false;
        }));

        return array_values(array_unique($normalized));
    }

    /**
     * @param string[] $recipients
     */
    private function sendEmail(string $subjectMessage, array $payload, array $recipients): bool
    {
        $email = service('email');
        $config = config('Email');
        $fromEmail = $config->fromEmail !== '' ? $config->fromEmail : 'noreply@localhost';
        $fromName = $config->fromName !== '' ? $config->fromName : 'VapeShop Security';

        $email->clear(true);
        $email->setFrom($fromEmail, $fromName);
        $email->setTo($recipients);
        $email->setSubject('[VapeShop Security Alert] Suspicious Activity Detected');
        $email->setMessage(
            "Security alert:\n{$subjectMessage}\n\nContext:\n" .
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        if (!$email->send(false)) {
            log_message('error', 'Failed to send security alert notification email: {debug}', [
                'debug' => $email->printDebugger(['headers']),
            ]);
            return false;
        }

        return true;
    }
}

