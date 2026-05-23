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
        if (! class_exists(\App\Libraries\ActivityLogger::class, false)) {
            $root = dirname(__DIR__);
            if (! defined('FCPATH')) {
                define('FCPATH', $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
            }

            require $root . '/vendor/autoload.php';
            require $root . '/app/Config/Paths.php';
            $paths = new \Config\Paths();
            require $paths->systemDirectory . '/Boot.php';
            \CodeIgniter\Boot::bootSpark($paths);
        }

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
