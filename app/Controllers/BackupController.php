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
        // Check if user has backup permissions
        if (!session()->get('user_role') || session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')
                           ->with('error', 'Access denied. Admin privileges required.');
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
        // Check if user has backup write permissions
        if (!session()->get('user_role') || session()->get('user_role') !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.'
            ])->setStatusCode(403);
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
        // Check if user has backup write permissions
        if (!session()->get('user_role') || session()->get('user_role') !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.'
            ])->setStatusCode(403);
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
        // Check if user has backup delete permissions
        if (!session()->get('user_role') || session()->get('user_role') !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.'
            ])->setStatusCode(403);
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
        // Check if user has backup read permissions
        if (!session()->get('user_role') || session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')
                           ->with('error', 'Access denied. Admin privileges required.');
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
        // Check if user has backup delete permissions
        if (!session()->get('user_role') || session()->get('user_role') !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.'
            ])->setStatusCode(403);
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
        // Check if user has backup read permissions
        if (!session()->get('user_role') || session()->get('user_role') !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.'
            ])->setStatusCode(403);
        }

        $stats = $this->backupService->getBackupStats();
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
