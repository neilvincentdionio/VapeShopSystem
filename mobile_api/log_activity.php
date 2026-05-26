<?php
declare(strict_types=1);

/**
 * Log an activity via CodeIgniter ActivityLogger (encrypted storage).
 */
function mobile_log_activity(
    ?int $userId,
    string $action,
    string $actionType,
    ?array $details = null,
    string $status = 'success'
): void {
    try {
        require_once __DIR__ . '/common.php';
        mobile_ci_bootstrap();

        (new \App\Libraries\ActivityLogger())->logUserAction(
            $userId,
            $action,
            $actionType,
            $details,
            $status
        );
    } catch (Throwable $e) {
        error_log('mobile_log_activity failed: ' . $e->getMessage());
    }
}
