<?php

namespace App\Commands;

use App\Libraries\SecurityAuditService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateSecurityAuditReport extends BaseCommand
{
    protected $group = 'Security';
    protected $name = 'security:report';
    protected $description = 'Generate a periodic security audit report (JSON).';
    protected $usage = 'security:report [hours]';
    protected $arguments = [
        'hours' => 'Optional lookback window in hours (default: 24).',
    ];

    public function run(array $params)
    {
        $hours = isset($params[0]) ? (int) $params[0] : 24;
        $hours = max(1, min(24 * 30, $hours));

        $service = new SecurityAuditService();
        $report = $service->generateAuditReport($hours);

        $directory = WRITEPATH . 'reports/security';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            CLI::error('Failed to create report directory: ' . $directory);
            return;
        }

        $filename = 'security_audit_report_' . date('Ymd_His') . '.json';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        $json = json_encode($report, JSON_PRETTY_PRINT);
        if ($json === false || file_put_contents($path, $json) === false) {
            CLI::error('Failed to write security report: ' . $path);
            return;
        }

        CLI::write('Security audit report generated successfully.', 'green');
        CLI::write('Path: ' . $path, 'yellow');
        CLI::write('Window: ' . $hours . ' hour(s)', 'yellow');
        CLI::write('Alerts: ' . count($report['alerts'] ?? []), 'yellow');
    }
}

