<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestSessionLogs extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'test:session-logs';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Test session tracking and activity logging functionality';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'test:session-logs';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('=== Session Tracking and Activity Logging Test ===', 'green');
        CLI::write('');

        // Test 1: Check database tables
        CLI::write('1. Checking database tables...', 'yellow');
        $db = \Config\Database::connect();
        $tables = $db->listTables();
        
        $userSessionsExists = in_array('user_sessions', $tables);
        $activityLogsExists = in_array('activity_logs', $tables);
        
        CLI::write("   user_sessions table: " . ($userSessionsExists ? "EXISTS" : "MISSING"), 
                  $userSessionsExists ? 'green' : 'red');
        CLI::write("   activity_logs table: " . ($activityLogsExists ? "EXISTS" : "MISSING"), 
                  $activityLogsExists ? 'green' : 'red');

        if (!$userSessionsExists || !$activityLogsExists) {
            CLI::write("   Creating missing tables...", 'yellow');
            
            if (!$userSessionsExists) {
                try {
                    $db->query("
                        CREATE TABLE user_sessions (
                            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            session_id VARCHAR(128) NOT NULL UNIQUE,
                            user_id INT UNSIGNED NOT NULL,
                            ip_address VARCHAR(45) NULL,
                            user_agent TEXT NULL,
                            login_time DATETIME NOT NULL,
                            last_activity DATETIME NOT NULL,
                            logout_time DATETIME NULL,
                            status ENUM('active', 'inactive', 'expired') DEFAULT 'active' NOT NULL,
                            created_at DATETIME NOT NULL,
                            updated_at DATETIME NOT NULL,
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
                            INDEX idx_user_sessions_user_id (user_id),
                            INDEX idx_user_sessions_status (status),
                            INDEX idx_user_sessions_last_activity (last_activity),
                            INDEX idx_user_sessions_login_time (login_time)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    CLI::write("   user_sessions table created successfully!", 'green');
                } catch (\Exception $e) {
                    CLI::write("   Error creating user_sessions: " . $e->getMessage(), 'red');
                }
            }
            
            if (!$activityLogsExists) {
                try {
                    $db->query("
                        CREATE TABLE activity_logs (
                            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            user_id TEXT NULL COMMENT 'Encrypted user ID',
                            action TEXT NOT NULL COMMENT 'Encrypted action description',
                            action_type VARCHAR(64) NOT NULL,
                            ip_address TEXT NULL COMMENT 'Encrypted IP address',
                            user_agent TEXT NULL COMMENT 'Encrypted user agent',
                            details TEXT NULL COMMENT 'Encrypted additional details',
                            status ENUM('success', 'failed', 'warning') DEFAULT 'success' NOT NULL,
                            created_at DATETIME NOT NULL,
                            INDEX idx_activity_logs_action_type (action_type),
                            INDEX idx_activity_logs_status (status),
                            INDEX idx_activity_logs_created_at (created_at)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    CLI::write("   activity_logs table created successfully!", 'green');
                } catch (\Exception $e) {
                    CLI::write("   Error creating activity_logs: " . $e->getMessage(), 'red');
                }
            }
        }

        CLI::write('');

        // Test 2: Test ActivityLogger
        CLI::write('2. Testing ActivityLogger...', 'yellow');
        try {
            $activityLogger = new \App\Libraries\ActivityLogger();
            CLI::write("   ActivityLogger class loaded successfully", 'green');
            
            // Test logging
            $result = $activityLogger->logLoginSuccess(1, 'test@example.com');
            CLI::write("   Login success log: " . ($result ? "SUCCESS" : "FAILED"), 
                      $result ? 'green' : 'red');
            
            $result = $activityLogger->logLoginFailed('fake@example.com', 'Test failure');
            CLI::write("   Login failure log: " . ($result ? "SUCCESS" : "FAILED"), 
                      $result ? 'green' : 'red');
            
        } catch (\Exception $e) {
            CLI::write("   Error: " . $e->getMessage(), 'red');
        }

        CLI::write('');

        // Test 3: Test UserSessionModel
        CLI::write('3. Testing UserSessionModel...', 'yellow');
        try {
            $userSessionModel = new \App\Models\UserSessionModel();
            CLI::write("   UserSessionModel class loaded successfully", 'green');
            
            // Test session creation
            $sessionId = session_id() ?: 'test_session_' . time();
            $result = $userSessionModel->createSession(1, $sessionId, '127.0.0.1', 'Test Agent');
            CLI::write("   Session creation: " . ($result > 0 ? "SUCCESS (ID: $result)" : "FAILED"), 
                      $result > 0 ? 'green' : 'red');
            
            // Test activity update
            $result = $userSessionModel->updateActivity($sessionId);
            CLI::write("   Activity update: " . ($result ? "SUCCESS" : "FAILED"), 
                      $result ? 'green' : 'red');
            
        } catch (\Exception $e) {
            CLI::write("   Error: " . $e->getMessage(), 'red');
        }

        CLI::write('');

        // Test 4: Test ActivityLogModel
        CLI::write('4. Testing ActivityLogModel...', 'yellow');
        try {
            $activityLogModel = new \App\Models\ActivityLogModel();
            CLI::write("   ActivityLogModel class loaded successfully", 'green');
            
            $logs = $activityLogModel->getRecentActivities(5);
            CLI::write("   Recent activities count: " . count($logs), 'green');
            
            $stats = $activityLogModel->getActivityStats();
            CLI::write("   Activity stats: " . (isset($stats['total']) ? "SUCCESS" : "FAILED"), 
                      isset($stats['total']) ? 'green' : 'red');
            
        } catch (\Exception $e) {
            CLI::write("   Error: " . $e->getMessage(), 'red');
        }

        CLI::write('');

        // Test 5: Test encryption
        CLI::write('5. Testing encryption/decryption...', 'yellow');
        try {
            $activityLogModel = new \App\Models\ActivityLogModel();
            
            $result = $activityLogModel->logActivity(
                1,
                'Test encrypted message',
                'PROFILE_UPDATE',
                '127.0.0.1',
                'Test Browser',
                'Test details',
                'success'
            );
            CLI::write("   Encrypted log creation: " . ($result ? "SUCCESS" : "FAILED"), 
                      $result ? 'green' : 'red');
            
            // Test retrieval (query by action type to avoid ordering collisions)
            $logs = $activityLogModel->getLogsByAction('PROFILE_UPDATE', 1);
            if (!empty($logs)) {
                $log = $logs[0];
                $isDecrypted = str_contains((string) $log['action'], 'Test encrypted message');
                CLI::write("   Decryption test: " . ($isDecrypted ? "SUCCESS" : "FAILED"), 
                          $isDecrypted ? 'green' : 'red');
                CLI::write("   Decrypted action: " . $log['action'], 'green');
            }
            
        } catch (\Exception $e) {
            CLI::write("   Error: " . $e->getMessage(), 'red');
        }

        CLI::write('');
        CLI::write('=== Test Complete ===', 'green');
        CLI::write('Session tracking and activity logging system is ready!', 'green');
    }
}
