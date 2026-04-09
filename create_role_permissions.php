<?php

// Initialize CodeIgniter
$pathsConfig = __DIR__ . "/app/Config/Paths.php";
require_once $pathsConfig;

$app = new CodeIgniter\CodeIgniter();
$app->initialize();
$db = \Config\Database::connect();

// Create role_permissions table
$sql = "
CREATE TABLE IF NOT EXISTS role_permissions (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  role ENUM('admin', 'customer', 'staff') NOT NULL,
  permission_id INT(11) UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY role (role),
  KEY permission_id (permission_id),
  UNIQUE KEY role_permission (role, permission_id),
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

try {
    $db->query($sql);
    echo "role_permissions table created successfully.\n";
} catch (\Exception $e) {
    echo "Error creating role_permissions table: " . $e->getMessage() . "\n";
}

// Create user_roles table
$sql2 = "
CREATE TABLE IF NOT EXISTS user_roles (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT(11) UNSIGNED NOT NULL,
  role ENUM('admin', 'customer', 'staff') NOT NULL DEFAULT 'customer',
  assigned_by INT(11) UNSIGNED NULL,
  assigned_at DATETIME NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY role (role),
  KEY assigned_by (assigned_by),
  KEY is_active (is_active),
  UNIQUE KEY user_role (user_id, role),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

try {
    $db->query($sql2);
    echo "user_roles table created successfully.\n";
} catch (\Exception $e) {
    echo "Error creating user_roles table: " . $e->getMessage() . "\n";
}
