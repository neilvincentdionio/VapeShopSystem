<?php

namespace App\Libraries;

use CodeIgniter\Database\Database;

class BackupService
{
    protected $db;
    protected $backupPath;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->backupPath = WRITEPATH . 'backups/';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Create full database backup
     */
    public function createBackup(string $backupName = null): array
    {
        $backupName = $backupName ?: 'backup_' . date('Y-m-d_H-i-s');
        $filename = $backupName . '.sql';
        $filepath = $this->backupPath . $filename;

        try {
            // Get all tables
            $tables = $this->db->listTables();
            
            $backupContent = "-- Database Backup\n";
            $backupContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $backupContent .= "-- Database: " . $this->db->database . "\n\n";
            
            // Add CREATE TABLE statements and data
            foreach ($tables as $table) {
                $backupContent .= $this->backupTable($table);
            }

            // Write backup to file
            if (file_put_contents($filepath, $backupContent) === false) {
                throw new \Exception("Failed to write backup file");
            }

            // Compress the backup
            $compressedFile = $this->compressBackup($filepath);
            
            // Remove uncompressed file
            unlink($filepath);

            return [
                'success' => true,
                'filename' => basename($compressedFile),
                'filepath' => $compressedFile,
                'size' => filesize($compressedFile),
                'created_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup a single table
     */
    private function backupTable(string $table): string
    {
        $sql = "";
        
        // Get CREATE TABLE statement
        $createTable = $this->db->query("SHOW CREATE TABLE `{$table}`")->getRow();
        $sql .= "-- Table: {$table}\n";
        $sql .= $createTable->{'Create Table'} . ";\n\n";
        
        // Get table data
        $result = $this->db->query("SELECT * FROM `{$table}`")->getResult();
        
        if (!empty($result)) {
            $columns = array_keys((array)$result[0]);
            $sql .= "-- Data for table: {$table}\n";
            
            foreach ($result as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $value = $row->$column;
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $this->db->escape($value) . "'";
                    }
                }
                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }
        
        return $sql;
    }

    /**
     * Compress backup file
     */
    private function compressBackup(string $filepath): string
    {
        $compressedFile = $filepath . '.gz';
        
        // Open source file for reading
        $source = fopen($filepath, 'rb');
        if (!$source) {
            throw new \Exception("Cannot open source file for compression");
        }
        
        // Open destination file for writing
        $destination = gzopen($compressedFile, 'wb9');
        if (!$destination) {
            fclose($source);
            throw new \Exception("Cannot create compressed backup file");
        }
        
        // Copy and compress
        while (!feof($source)) {
            gzwrite($destination, fread($source, 1024 * 512)); // 512KB chunks
        }
        
        fclose($source);
        gzclose($destination);
        
        return $compressedFile;
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup(string $backupFile): array
    {
        $filepath = $this->backupPath . $backupFile;
        
        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'error' => 'Backup file not found'
            ];
        }

        try {
            // Decompress if needed
            $content = '';
            if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
                $content = gzfile_get_contents($filepath);
                if ($content === false) {
                    throw new \Exception("Failed to decompress backup file");
                }
            } else {
                $content = file_get_contents($filepath);
                if ($content === false) {
                    throw new \Exception("Failed to read backup file");
                }
            }

            // Split into individual statements
            $statements = array_filter(array_map('trim', explode(";\n", $content)));
            
            // Execute each statement
            $this->db->transStart();
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $this->db->query($statement);
                }
            }
            
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception("Database restore failed");
            }

            return [
                'success' => true,
                'message' => 'Database restored successfully',
                'statements_executed' => count($statements)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List all backup files
     */
    public function listBackups(): array
    {
        $backups = [];
        $files = scandir($this->backupPath);
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filepath = $this->backupPath . $file;
                $stat = stat($filepath);
                
                $backups[] = [
                    'filename' => $file,
                    'filepath' => $filepath,
                    'size' => $stat['size'],
                    'created_at' => date('Y-m-d H:i:s', $stat['mtime']),
                    'type' => pathinfo($file, PATHINFO_EXTENSION)
                ];
            }
        }
        
        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }

    /**
     * Delete backup file
     */
    public function deleteBackup(string $backupFile): array
    {
        $filepath = $this->backupPath . $backupFile;
        
        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'error' => 'Backup file not found'
            ];
        }

        if (unlink($filepath)) {
            return [
                'success' => true,
                'message' => 'Backup deleted successfully'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to delete backup file'
            ];
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup(string $backupFile): ?string
    {
        $filepath = $this->backupPath . $backupFile;
        
        if (!file_exists($filepath)) {
            return null;
        }
        
        return $filepath;
    }

    /**
     * Clean up old backups (keep only last N backups)
     */
    public function cleanupOldBackups(int $keepCount = 10): array
    {
        $backups = $this->listBackups();
        
        if (count($backups) <= $keepCount) {
            return [
                'success' => true,
                'message' => 'No backups to clean up',
                'deleted_count' => 0
            ];
        }
        
        $deletedCount = 0;
        $backupsToDelete = array_slice($backups, $keepCount);
        
        foreach ($backupsToDelete as $backup) {
            if ($this->deleteBackup($backup['filename'])['success']) {
                $deletedCount++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Cleaned up {$deletedCount} old backup(s)",
            'deleted_count' => $deletedCount
        ];
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats(): array
    {
        $backups = $this->listBackups();
        $totalSize = 0;
        $latestBackup = null;
        
        foreach ($backups as $backup) {
            $totalSize += $backup['size'];
            if ($latestBackup === null || $backup['created_at'] > $latestBackup['created_at']) {
                $latestBackup = $backup;
            }
        }
        
        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'latest_backup' => $latestBackup,
            'backup_path' => $this->backupPath
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
