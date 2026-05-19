<?php
$uri = service('uri');
$segments = array_map(
    static fn($seg): string => strtolower((string) $seg),
    $uri->getSegments()
);

$hasAdmin = in_array('admin', $segments, true);
$hasSessionLogs = in_array('session-logs', $segments, true);
$hasActivityLogs = in_array('activity-logs', $segments, true);
$hasBackup = in_array('backup', $segments, true);
$hasUsers = in_array('user-management', $segments, true);
$hasRoles = $hasAdmin && in_array('roles', $segments, true);
$hasPermissions = $hasAdmin && in_array('permissions', $segments, true);
$hasSettings = in_array('dashboard', $segments, true) && in_array('settings', $segments, true);
$hasReturns = $hasAdmin && in_array('returns', $segments, true);

$autoTitle = 'Admin Page';
$autoSubtitle = '';

if ($hasSessionLogs) {
    $autoTitle = 'Session Logs';
    $autoSubtitle = 'Monitor and manage user session activity across the system.';
} elseif ($hasActivityLogs) {
    $autoTitle = 'Activity Logs';
    $autoSubtitle = 'Track system events, user actions, and security activity.';
} elseif ($hasBackup) {
    $autoTitle = 'Database Backup Management';
    $autoSubtitle = 'Create, download, restore, and manage database backups.';
} elseif ($hasUsers) {
    $autoTitle = 'User Management';
    $autoSubtitle = 'Manage system users, roles, and account status.';
} elseif ($hasRoles) {
    $autoTitle = 'Roles Management';
    $autoSubtitle = 'Define system roles and assign permission access policies.';
} elseif ($hasPermissions) {
    $autoTitle = 'Permissions Management';
    $autoSubtitle = 'Control action-level access that can be assigned to roles.';
} elseif ($hasSettings) {
    $autoTitle = 'Settings (Admin)';
    $autoSubtitle = 'Configure system information and administrative operation guidelines.';
} elseif ($hasReturns) {
    $autoTitle = 'Return / Refund';
    $autoSubtitle = 'Review return requests, assign riders for pickup, and complete refunds.';
}

$title = $pageHeaderTitle ?? $autoTitle ?? ($page_title ?? 'Admin Page');
$subtitle = $pageHeaderSubtitle ?? $autoSubtitle;
?>
<section class="admin-page-header">
    <h1><?= esc((string) $title) ?></h1>
    <?php if ($subtitle !== ''): ?>
        <p><?= esc((string) $subtitle) ?></p>
    <?php endif; ?>
</section>
