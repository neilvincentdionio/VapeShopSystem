<?php

namespace App\Libraries;

class SecurityAuditService
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * @return array<string, mixed>
     */
    public function generateAuditReport(int $hours = 24): array
    {
        $hours = max(1, min(24 * 30, $hours));
        $summary = $this->getSummary($hours);
        $alerts = $this->getSuspiciousAlerts($hours);

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'window_hours' => $hours,
            'summary' => $summary,
            'alerts' => $alerts,
            'recommendations' => $this->buildRecommendations($summary, $alerts),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSuspiciousAlerts(int $hours = 24): array
    {
        $hours = max(1, min(24 * 30, $hours));
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        $alerts = [];

        $ipRows = $this->db->table('login_attempts')
            ->select('ip_address, COUNT(*) AS failed_count, MAX(attempt_time) AS last_seen')
            ->where('success', 0)
            ->where('attempt_time >=', $since)
            ->groupBy('ip_address')
            ->having('COUNT(*) >=', 5)
            ->orderBy('failed_count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        foreach ($ipRows as $row) {
            $alerts[] = [
                'severity' => ((int) $row['failed_count'] >= 10) ? 'high' : 'medium',
                'type' => 'FAILED_LOGIN_IP',
                'message' => "IP {$row['ip_address']} has {$row['failed_count']} failed logins.",
                'last_seen' => $row['last_seen'],
            ];
        }

        $emailRows = $this->db->table('login_attempts')
            ->select('email, COUNT(*) AS failed_count, MAX(attempt_time) AS last_seen')
            ->where('success', 0)
            ->where('attempt_time >=', $since)
            ->groupBy('email')
            ->having('COUNT(*) >=', 5)
            ->orderBy('failed_count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        foreach ($emailRows as $row) {
            $alerts[] = [
                'severity' => ((int) $row['failed_count'] >= 10) ? 'high' : 'medium',
                'type' => 'FAILED_LOGIN_ACCOUNT',
                'message' => "{$row['email']} has {$row['failed_count']} failed logins.",
                'last_seen' => $row['last_seen'],
            ];
        }

        $warningRows = $this->db->table('activity_logs')
            ->select('COUNT(*) AS warning_count, MAX(created_at) AS last_seen')
            ->where('status', 'warning')
            ->where('created_at >=', $since)
            ->get()
            ->getRowArray();

        $warningCount = (int) ($warningRows['warning_count'] ?? 0);
        if ($warningCount > 0) {
            $alerts[] = [
                'severity' => $warningCount >= 10 ? 'high' : 'medium',
                'type' => 'SECURITY_WARNING_EVENTS',
                'message' => "Detected {$warningCount} warning-level security events.",
                'last_seen' => $warningRows['last_seen'] ?? null,
            ];
        }

        usort($alerts, static function (array $a, array $b): int {
            $rank = ['high' => 3, 'medium' => 2, 'low' => 1];
            return ($rank[$b['severity']] ?? 0) <=> ($rank[$a['severity']] ?? 0);
        });

        return $alerts;
    }

    /**
     * @return array<string, int>
     */
    private function getSummary(int $hours): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        $totalAttempts = (int) $this->db->table('login_attempts')
            ->where('attempt_time >=', $since)
            ->countAllResults();

        $failedAttempts = (int) $this->db->table('login_attempts')
            ->where('success', 0)
            ->where('attempt_time >=', $since)
            ->countAllResults();

        $suspiciousIps = (int) count(
            $this->db->table('login_attempts')
                ->select('ip_address')
                ->where('success', 0)
                ->where('attempt_time >=', $since)
                ->groupBy('ip_address')
                ->having('COUNT(*) >=', 5)
                ->get()
                ->getResultArray()
        );

        $suspiciousAccounts = (int) count(
            $this->db->table('login_attempts')
                ->select('email')
                ->where('success', 0)
                ->where('attempt_time >=', $since)
                ->groupBy('email')
                ->having('COUNT(*) >=', 5)
                ->get()
                ->getResultArray()
        );

        $warningEvents = (int) $this->db->table('activity_logs')
            ->where('status', 'warning')
            ->where('created_at >=', $since)
            ->countAllResults();

        return [
            'total_login_attempts' => $totalAttempts,
            'failed_login_attempts' => $failedAttempts,
            'suspicious_ips' => $suspiciousIps,
            'suspicious_accounts' => $suspiciousAccounts,
            'warning_events' => $warningEvents,
        ];
    }

    /**
     * @param array<string, int> $summary
     * @param array<int, array<string, mixed>> $alerts
     * @return array<int, string>
     */
    private function buildRecommendations(array $summary, array $alerts): array
    {
        $recommendations = [];

        if (($summary['failed_login_attempts'] ?? 0) >= 10) {
            $recommendations[] = 'Increase monitoring on login endpoints and review repeated failed attempts.';
        }

        if (($summary['suspicious_ips'] ?? 0) > 0) {
            $recommendations[] = 'Block or throttle suspicious IP addresses at the firewall/reverse-proxy layer.';
        }

        if (($summary['suspicious_accounts'] ?? 0) > 0) {
            $recommendations[] = 'Require additional verification for targeted accounts and notify affected users.';
        }

        if (($summary['warning_events'] ?? 0) > 0 || count($alerts) > 0) {
            $recommendations[] = 'Review warning-level logs in Activity Logs and investigate top offenders.';
        }

        if ($recommendations === []) {
            $recommendations[] = 'No immediate security anomalies detected in the selected report window.';
        }

        return $recommendations;
    }
}

