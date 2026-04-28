<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserSessionModel;

class TestSessions extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'test:sessions';
    protected $description = 'Test session database queries';
    protected $usage = 'test:sessions [action]';
    protected $arguments = [
        'action' => 'latest|activity|user_id|login_logs|recent_logs|failed_logs|session_status|logout_logs|raw_data',
    ];

    public function run(array $params = [])
    {
        $action = $params[0] ?? 'latest';
        $sessionModel = new UserSessionModel();
        
        switch ($action) {
            case 'latest':
                $this->showLatestSession($sessionModel);
                break;
            case 'activity':
                $userId = $params[1] ?? 1;
                $this->showActivity($sessionModel, $userId);
                break;
            case 'user_id':
                $this->showUsers();
                break;
            case 'login_logs':
                $this->showLoginLogs();
                break;
            case 'recent_logs':
                $this->showRecentLogs();
                break;
            case 'failed_logs':
                $this->showFailedLogs();
                break;
            case 'session_status':
                $userId = $params[1] ?? 1;
                $this->showSessionStatus($userId);
                break;
            case 'logout_logs':
                $this->showLogoutLogs();
                break;
            case 'raw_data':
                $this->showRawData();
                break;
            default:
                CLI::error("Invalid action. Use: latest, activity, user_id, login_logs, recent_logs, failed_logs, session_status, logout_logs, raw_data");
                $this->showUsage();
        }
    }
    
    private function showLatestSession($sessionModel)
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT * FROM user_sessions ORDER BY id DESC LIMIT 1");
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No sessions found in database.");
            return;
        }
        
        CLI::write("=== Latest Session ===", 'green');
        $session = $result[0];
        CLI::write("ID: " . $session->id);
        CLI::write("User ID: " . $session->user_id);
        CLI::write("Session ID: " . $session->session_id);
        CLI::write("IP Address: " . $session->ip_address);
        CLI::write("Status: " . $session->status);
        CLI::write("Login Time: " . $session->login_time);
        CLI::write("Last Activity: " . $session->last_activity);
        CLI::write("User Agent: " . substr($session->user_agent, 0, 50) . "...");
    }
    
    private function showActivity($sessionModel, $userId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT last_activity FROM user_sessions WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$userId]);
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No sessions found for user ID: $userId");
            return;
        }
        
        CLI::write("=== Activity for User ID: $userId ===", 'green');
        CLI::write("Last Activity: " . $result[0]->last_activity);
    }
    
    private function showUsers()
    {
        $db = \Config\Database::connect();
        // First check what columns exist
        $columnsQuery = $db->query("SHOW COLUMNS FROM users");
        $columns = $columnsQuery->getResult();
        
        $selectFields = "id";
        $displayFields = ["id"];
        
        foreach ($columns as $column) {
            if ($column->Field === 'email' || $column->Field === 'username' || $column->Field === 'name') {
                $selectFields .= ", " . $column->Field;
                $displayFields[] = $column->Field;
            }
        }
        
        $query = $db->query("SELECT $selectFields FROM users ORDER BY id DESC LIMIT 5");
        $result = $query->getResult();
        
        CLI::write("=== Recent Users ===", 'green');
        foreach ($result as $user) {
            $display = "ID: {$user->id}";
            foreach ($displayFields as $field) {
                if ($field !== 'id' && isset($user->$field)) {
                    $display .= ", " . ucfirst($field) . ": {$user->$field}";
                }
            }
            CLI::write($display);
        }
    }
    
    private function showLoginLogs()
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT action, action_type, status, created_at FROM activity_logs WHERE action_type = 'LOGIN_SUCCESS' ORDER BY id DESC LIMIT 1");
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No LOGIN_SUCCESS logs found in database.");
            return;
        }
        
        CLI::write("=== Latest Login Success Log ===", 'green');
        $log = $result[0];
        CLI::write("Action: " . $log->action);
        CLI::write("Action Type: " . $log->action_type);
        CLI::write("Status: " . $log->status);
        CLI::write("Created At: " . $log->created_at);
    }
    
    private function showRecentLogs()
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT action, action_type, status, created_at FROM activity_logs ORDER BY id DESC LIMIT 5");
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No activity logs found in database.");
            return;
        }
        
        CLI::write("=== Recent Activity Logs ===", 'green');
        foreach ($result as $log) {
            CLI::write("Action: " . $log->action);
            CLI::write("Type: " . $log->action_type . ", Status: " . $log->status);
            CLI::write("Time: " . $log->created_at);
            CLI::write("---");
        }
    }
    
    private function showFailedLogs()
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT action, action_type, status, created_at FROM activity_logs WHERE action_type = 'LOGIN_FAILED' ORDER BY id DESC LIMIT 3");
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No LOGIN_FAILED logs found in database.");
            return;
        }
        
        CLI::write("=== Recent Failed Login Logs ===", 'green');
        foreach ($result as $log) {
            CLI::write("Action: " . $log->action);
            CLI::write("Action Type: " . $log->action_type);
            CLI::write("Status: " . $log->status);
            CLI::write("Created At: " . $log->created_at);
            CLI::write("---");
        }
    }
    
    private function showSessionStatus($userId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT status, logout_time, login_time, last_activity FROM user_sessions WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$userId]);
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No sessions found for user ID: $userId");
            return;
        }
        
        CLI::write("=== Latest Session Status for User ID: $userId ===", 'green');
        $session = $result[0];
        CLI::write("Status: " . $session->status);
        CLI::write("Login Time: " . $session->login_time);
        CLI::write("Last Activity: " . $session->last_activity);
        CLI::write("Logout Time: " . ($session->logout_time ?? 'Not set'));
    }
    
    private function showLogoutLogs()
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT action, action_type, status, created_at FROM activity_logs WHERE action_type = 'LOGOUT' ORDER BY id DESC LIMIT 1");
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No LOGOUT logs found in database.");
            return;
        }
        
        CLI::write("=== Latest Logout Log ===", 'green');
        $log = $result[0];
        CLI::write("Action: " . $log->action);
        CLI::write("Action Type: " . $log->action_type);
        CLI::write("Status: " . $log->status);
        CLI::write("Created At: " . $log->created_at);
    }
    
    private function showRawData()
    {
        $db = \Config\Database::connect();
        
        CLI::write("=== RAW DATABASE DATA (Encrypted) ===", 'yellow');
        $query = $db->query("SELECT user_id, ip_address, action FROM activity_logs WHERE action_type = 'LOGIN_SUCCESS' LIMIT 1");
        $result = $query->getResult();
        
        if (empty($result)) {
            CLI::error("No LOGIN_SUCCESS logs found in database.");
            return;
        }
        
        $raw = $result[0];
        CLI::write("Raw user_id: " . $raw->user_id);
        CLI::write("Raw ip_address: " . $raw->ip_address);
        CLI::write("Raw action: " . substr($raw->action, 0, 50) . "...");
        
        CLI::write("\n=== DECRYPTED DISPLAY (Through Model) ===", 'green');
        $activityModel = new \App\Models\ActivityLogModel();
        $decrypted = $activityModel->where('action_type', 'LOGIN_SUCCESS')->first();
        
        if ($decrypted) {
            CLI::write("Decrypted user_id: " . $decrypted['user_id']);
            CLI::write("Decrypted ip_address: " . $decrypted['ip_address']);
            CLI::write("Decrypted action: " . $decrypted['action']);
        }
    }
    
    private function showUsage()
    {
        CLI::write("Usage examples:", 'yellow');
        CLI::write("  php spark test:sessions latest      - Show latest session");
        CLI::write("  php spark test:sessions activity 1  - Show activity for user ID 1");
        CLI::write("  php spark test:sessions user_id    - Show recent users");
        CLI::write("  php spark test:sessions login_logs  - Show latest login success log");
        CLI::write("  php spark test:sessions recent_logs - Show recent activity logs");
        CLI::write("  php spark test:sessions failed_logs - Show recent failed login logs");
        CLI::write("  php spark test:sessions session_status 1 - Show session status for user ID 1");
        CLI::write("  php spark test:sessions logout_logs - Show latest logout log");
        CLI::write("  php spark test:sessions raw_data    - Show raw encrypted vs decrypted data");
    }
}
