<?php
/**
 * One-time RBAC installer. Open in browser once, then delete this file.
 * Example: http://localhost/VapeShopSystem/public/rbac_install.php
 */

declare(strict_types=1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'vapeshop_db';

$mysqli = @new mysqli($host, $user, $pass, $dbName);
if ($mysqli->connect_errno) {
    http_response_code(500);
    exit('Database connection failed: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

$queries = [
    "CREATE TABLE IF NOT EXISTS `roles` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(50) NOT NULL,
        `description` text DEFAULT NULL,
        `level` int(11) unsigned NOT NULL DEFAULT 100,
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE IF NOT EXISTS `permissions` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `description` text DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE IF NOT EXISTS `role_permissions` (
        `role_id` int(11) unsigned NOT NULL,
        `permission_id` int(11) unsigned NOT NULL,
        `created_at` datetime DEFAULT NULL,
        PRIMARY KEY (`role_id`,`permission_id`),
        KEY `permission_id` (`permission_id`),
        CONSTRAINT `rp_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
        CONSTRAINT `rp_perm_fk` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($queries as $sql) {
    if (!$mysqli->query($sql)) {
        exit('Migration error: ' . $mysqli->error);
    }
}

$roleIdCol = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'role_id'");
if ($roleIdCol && $roleIdCol->num_rows === 0) {
    if (!$mysqli->query("ALTER TABLE `users` ADD COLUMN `role_id` int(11) unsigned DEFAULT NULL AFTER `role`")) {
        exit('Could not add users.role_id: ' . $mysqli->error);
    }
    $mysqli->query('ALTER TABLE `users` ADD KEY `role_id` (`role_id`)');
}

$levelCol = $mysqli->query("SHOW COLUMNS FROM `roles` LIKE 'level'");
if ($levelCol && $levelCol->num_rows === 0) {
    $mysqli->query("ALTER TABLE `roles` ADD COLUMN `level` int(11) unsigned NOT NULL DEFAULT 100 AFTER `description`");
}

seedInline($mysqli);

header('Content-Type: text/html; charset=utf-8');
echo '<h1>RBAC installed successfully</h1>';
echo '<p>Roles, permissions, and user role links are ready.</p>';
echo '<p><strong>Delete</strong> <code>public/rbac_install.php</code> for security.</p>';
echo '<p><a href="../admin/roles">Open Role Management</a></p>';

function seedInline(mysqli $db): void
{
    $now = date('Y-m-d H:i:s');
    $roles = [
        ['admin', 'Shop administrator', 300],
        ['staff', 'Staff member', 200],
        ['rider', 'Delivery rider', 150],
        ['customer', 'Customer account', 100],
    ];

    foreach ($roles as [$name, $desc, $level]) {
        $stmt = $db->prepare('INSERT INTO roles (name, description, level, created_at, updated_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description), level = VALUES(level), updated_at = VALUES(updated_at)');
        $stmt->bind_param('ssiss', $name, $desc, $level, $now, $now);
        $stmt->execute();
    }

    $permissions = [
        ['dashboard.view', 'View admin dashboard'],
        ['manage_users', 'Manage user accounts'],
        ['view_products', 'View products'],
        ['create_products', 'Create products'],
        ['update_products', 'Update products'],
        ['delete_products', 'Delete products'],
        ['manage_orders', 'Manage orders'],
        ['manage_records', 'Manage records'],
        ['manage_backups', 'Manage database backups'],
        ['activity_logs.view', 'View activity logs'],
        ['activity_logs.manage', 'Manage activity logs and sessions'],
        ['roles.manage', 'Manage roles'],
        ['permissions.manage', 'Manage permissions'],
        ['read', 'API read access'],
    ];

    foreach ($permissions as [$name, $desc]) {
        $stmt = $db->prepare('INSERT INTO permissions (name, description, created_at, updated_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description), updated_at = VALUES(updated_at)');
        $stmt->bind_param('ssss', $name, $desc, $now, $now);
        $stmt->execute();
    }

    $roleMap = [];
    $res = $db->query('SELECT id, name FROM roles');
    while ($row = $res->fetch_assoc()) {
        $roleMap[$row['name']] = (int) $row['id'];
    }

    $permMap = [];
    $res = $db->query('SELECT id, name FROM permissions');
    while ($row = $res->fetch_assoc()) {
        $permMap[$row['name']] = (int) $row['id'];
    }

    $assignments = [
        'admin' => array_keys($permMap),
        'staff' => ['dashboard.view', 'view_products', 'create_products', 'update_products', 'manage_orders', 'manage_records', 'activity_logs.view'],
        'rider' => ['dashboard.view', 'manage_orders', 'read'],
        'customer' => ['dashboard.view', 'read'],
    ];

    foreach ($assignments as $roleName => $permNames) {
        if (!isset($roleMap[$roleName])) {
            continue;
        }
        $roleId = $roleMap[$roleName];
        $db->query('DELETE FROM role_permissions WHERE role_id = ' . $roleId);
        foreach ($permNames as $permName) {
            if (!isset($permMap[$permName])) {
                continue;
            }
            $permId = $permMap[$permName];
            $stmt = $db->prepare('INSERT IGNORE INTO role_permissions (role_id, permission_id, created_at) VALUES (?, ?, ?)');
            $stmt->bind_param('iis', $roleId, $permId, $now);
            $stmt->execute();
        }
    }

    $res = $db->query('SELECT id, role FROM users');
    while ($user = $res->fetch_assoc()) {
        $roleName = strtolower(trim((string) $user['role'])) ?: 'customer';
        if (!isset($roleMap[$roleName])) {
            $roleName = 'customer';
        }
        $roleId = $roleMap[$roleName];
        $stmt = $db->prepare('UPDATE users SET role = ?, role_id = ? WHERE id = ?');
        $stmt->bind_param('sii', $roleName, $roleId, $user['id']);
        $stmt->execute();
    }
}
