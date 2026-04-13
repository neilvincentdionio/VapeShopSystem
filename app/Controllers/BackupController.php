<?php

namespace App\Controllers;

use App\Libraries\BackupService;

class BackupController extends BaseController
{
    protected $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        $guard = $this->enforceBackupPermission();
        if ($guard !== true) {
            return $guard;
        }

        $data = [
            'backups' => $this->backupService->listBackups(),
            'stats' => $this->backupService->getBackupStats(),
            'title' => 'Database Backup Management'
        ];

        return view('admin/backup/index', $data);
    }

    /**
     * Create new backup
     */
    public function create()
    {
        $guard = $this->enforceBackupPermission(true);
        if ($guard !== true) {
            return $guard;
        }

        $backupName = $this->request->getPost('backup_name');
        $result = $this->backupService->createBackup($backupName);

        return $this->response->setJSON($result);
    }

    /**
     * Restore from backup
     */
    public function restore()
    {
        $guard = $this->enforceBackupPermission(true);
        if ($guard !== true) {
            return $guard;
        }

        $backupFile = $this->request->getPost('backup_file');
        $result = $this->backupService->restoreBackup($backupFile);

        return $this->response->setJSON($result);
    }

    /**
     * Delete backup
     */
    public function delete()
    {
        $guard = $this->enforceBackupPermission(true);
        if ($guard !== true) {
            return $guard;
        }

        $backupFile = $this->request->getPost('backup_file');
        $result = $this->backupService->deleteBackup($backupFile);

        return $this->response->setJSON($result);
    }

    /**
     * Download backup
     */
    public function download($filename)
    {
        $guard = $this->enforceBackupPermission();
        if ($guard !== true) {
            return $guard;
        }

        $filepath = $this->backupService->downloadBackup($filename);
        
        if (!$filepath) {
            return redirect()->to('/backup')
                           ->with('error', 'Backup file not found.');
        }

        return $this->response->download($filepath, null, true);
    }

    /**
     * Clean up old backups
     */
    public function cleanup()
    {
        $guard = $this->enforceBackupPermission(true);
        if ($guard !== true) {
            return $guard;
        }

        $keepCount = $this->request->getPost('keep_count') ?? 10;
        $result = $this->backupService->cleanupOldBackups($keepCount);

        return $this->response->setJSON($result);
    }

    /**
     * Get backup statistics (AJAX)
     */
    public function stats()
    {
        $guard = $this->enforceBackupPermission(true);
        if ($guard !== true) {
            return $guard;
        }

        $stats = $this->backupService->getBackupStats();
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    private function enforceBackupPermission(bool $json = false)
    {
        if (!session()->get('logged_in')) {
            if ($json) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthorized.',
                ])->setStatusCode(401);
            }

            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        if (!$this->hasPermission('manage_backups')) {
            if ($json) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Forbidden. Backup permission required.',
                ])->setStatusCode(403);
            }

            return redirect()->to('/dashboard')->with('error', 'Access denied. Backup permission required.');
        }

        return true;
    }
}
