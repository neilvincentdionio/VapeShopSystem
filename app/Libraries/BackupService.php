<?php

namespace App\Libraries;

use CodeIgniter\Database\Database;
use CodeIgniter\I18n\Time;

class BackupService
{
    protected $db;
    protected $backupPath;
    private const ALLOWED_BACKUP_EXTENSIONS = ['sql', 'gz'];

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
        $backupName = $backupName ?: 'backup_' . $this->now()->format('Y-m-d_H-i-s');
        $filename = $backupName . '.sql';
        $filepath = $this->backupPath . $filename;

        try {
            // Get all tables
            $tables = $this->db->listTables();
            
            $backupContent = "-- Database Backup\n";
            $backupContent .= "-- Generated: " . $this->now()->format('Y-m-d H:i:s') . "\n";
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
                'created_at' => $this->now()->format('Y-m-d H:i:s')
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
                        $values[] = $this->db->escape($value);
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
    public function restoreBackup(string $backupFile, bool $createSafetyBackup = true): array
    {
        $validatedBackup = $this->validateBackupFile($backupFile);
        if (!$validatedBackup['success']) {
            return $validatedBackup;
        }

        $filepath = $validatedBackup['filepath'];

        try {
            $this->ensureDatabaseConnection();

            $safetyBackup = null;
            if ($createSafetyBackup) {
                $safetyBackupName = pathinfo($backupFile, PATHINFO_FILENAME) . '_pre_restore_' . $this->now()->format('Y-m-d_H-i-s');
                $safetyBackup = $this->createBackup($safetyBackupName);

                if (!$safetyBackup['success']) {
                    throw new \RuntimeException('Failed to create safety backup before restore: ' . ($safetyBackup['error'] ?? 'Unknown error'));
                }
            }

            $content = $this->readBackupContent($filepath);
            $statements = $this->parseSqlStatements($content);
            if (empty($statements)) {
                throw new \RuntimeException('The backup file did not contain any executable SQL statements.');
            }

            $orderedStatements = $this->orderRestoreStatements($statements);

            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $this->dropAllTables();

            $executedStatements = 0;
            foreach ($orderedStatements as $index => $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }

                try {
                    $this->db->query($statement);
                    $executedStatements++;
                } catch (\Throwable $exception) {
                    throw new \RuntimeException(
                        sprintf(
                            'Restore failed on statement %d of %d: %s. SQL: %s',
                            $index + 1,
                            count($orderedStatements),
                            $exception->getMessage(),
                            $this->summarizeSql($statement)
                        ),
                        0,
                        $exception
                    );
                }
            }

            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

            $message = 'Database restored successfully.';
            if ($createSafetyBackup && isset($safetyBackup['filename'])) {
                $message .= ' Safety backup created: ' . $safetyBackup['filename'];
            }

            return [
                'success' => true,
                'message' => $message,
                'statements_executed' => $executedStatements,
                'source_backup' => basename($filepath),
                'safety_backup' => $safetyBackup['filename'] ?? null,
            ];

        } catch (\Throwable $e) {
            try {
                $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Throwable $ignored) {
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function validateBackupFile(string $backupFile): array
    {
        $backupFile = trim($backupFile);
        if ($backupFile === '') {
            return [
                'success' => false,
                'error' => 'Please select a backup file to restore.',
            ];
        }

        if (basename($backupFile) !== $backupFile) {
            return [
                'success' => false,
                'error' => 'Invalid backup filename.',
            ];
        }

        $extension = strtolower((string) pathinfo($backupFile, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_BACKUP_EXTENSIONS, true)) {
            return [
                'success' => false,
                'error' => 'Unsupported backup file type. Only .sql and .gz backups can be restored.',
            ];
        }

        $filepath = $this->backupPath . $backupFile;
        if (!is_file($filepath)) {
            return [
                'success' => false,
                'error' => 'Backup file not found.',
            ];
        }

        if (!is_readable($filepath)) {
            return [
                'success' => false,
                'error' => 'Backup file is not readable.',
            ];
        }

        if ((int) filesize($filepath) === 0) {
            return [
                'success' => false,
                'error' => 'Backup file is empty.',
            ];
        }

        return [
            'success' => true,
            'filepath' => $filepath,
        ];
    }

    private function ensureDatabaseConnection(): void
    {
        if ($this->db->connID === false || $this->db->connID === null) {
            $this->db->initialize();
        }

        $this->db->query('SELECT 1');
    }

    private function readBackupContent(string $filepath): string
    {
        if (strtolower((string) pathinfo($filepath, PATHINFO_EXTENSION)) === 'gz') {
            $content = $this->readCompressedBackup($filepath);
            if ($content === false) {
                throw new \RuntimeException('Failed to extract the compressed backup file.');
            }

            return $content;
        }

        $content = file_get_contents($filepath);
        if ($content === false) {
            throw new \RuntimeException('Failed to read the backup file.');
        }

        return $content;
    }

    private function readCompressedBackup(string $filepath): string|false
    {
        $handle = gzopen($filepath, 'rb');
        if ($handle === false) {
            return false;
        }

        $content = '';
        while (!gzeof($handle)) {
            $chunk = gzread($handle, 1024 * 512);
            if ($chunk === false) {
                gzclose($handle);
                return false;
            }

            $content .= $chunk;
        }

        gzclose($handle);

        return $content;
    }

    /**
     * Convert a SQL dump into individual executable statements.
     */
    private function parseSqlStatements(string $content): array
    {
        $statements = [];
        $buffer = '';
        $inString = false;
        $stringDelimiter = '';
        $length = strlen($content);

        for ($i = 0; $i < $length; $i++) {
            $char = $content[$i];
            $next = $i + 1 < $length ? $content[$i + 1] : '';

            if (!$inString) {
                // Skip line comments that start at the beginning of a line or after whitespace.
                if (
                    $char === '-'
                    && $next === '-'
                    && $this->isCommentStart($buffer)
                ) {
                    while ($i < $length && !in_array($content[$i], ["\n", "\r"], true)) {
                        $i++;
                    }
                    continue;
                }

                if ($char === '#' && $this->isCommentStart($buffer)) {
                    while ($i < $length && !in_array($content[$i], ["\n", "\r"], true)) {
                        $i++;
                    }
                    continue;
                }
            }

            if ($char === "'" || $char === '"') {
                if ($inString && $stringDelimiter === $char) {
                    $previous = $i > 0 ? $content[$i - 1] : '';
                    if ($previous !== '\\') {
                        $inString = false;
                        $stringDelimiter = '';
                    }
                } elseif (!$inString) {
                    $inString = true;
                    $stringDelimiter = $char;
                }
            }

            if (!$inString && $char === ';') {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $statement = trim($buffer);
        if ($statement !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }

    private function orderRestoreStatements(array $statements): array
    {
        $createTableStatements = [];
        $insertStatements = [];
        $otherStatements = [];

        foreach ($statements as $statement) {
            $normalized = strtoupper(ltrim($statement));

            if (str_starts_with($normalized, 'CREATE TABLE')) {
                $tableName = $this->extractCreatedTableName($statement);
                if ($tableName !== null) {
                    $createTableStatements[$tableName] = $statement;
                    continue;
                }
            }

            if (str_starts_with($normalized, 'INSERT INTO')) {
                $insertStatements[] = $statement;
                continue;
            }

            $otherStatements[] = $statement;
        }

        $orderedCreates = $this->sortCreateTableStatementsByDependency($createTableStatements);

        return array_merge($otherStatements, $orderedCreates, $insertStatements);
    }

    private function extractCreatedTableName(string $statement): ?string
    {
        if (preg_match('/CREATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?/i', $statement, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function sortCreateTableStatementsByDependency(array $createTableStatements): array
    {
        $dependencies = [];
        $dependents = [];
        $inDegree = [];

        foreach ($createTableStatements as $tableName => $statement) {
            $dependencies[$tableName] = $this->extractReferencedTables($statement);
            $inDegree[$tableName] = 0;
            $dependents[$tableName] = [];
        }

        foreach ($dependencies as $tableName => $referencedTables) {
            foreach ($referencedTables as $referencedTable) {
                if (!array_key_exists($referencedTable, $createTableStatements)) {
                    continue;
                }

                $dependents[$referencedTable][] = $tableName;
                $inDegree[$tableName]++;
            }
        }

        $queue = [];
        foreach ($inDegree as $tableName => $count) {
            if ($count === 0) {
                $queue[] = $tableName;
            }
        }

        sort($queue);

        $ordered = [];
        while ($queue !== []) {
            $tableName = array_shift($queue);
            $ordered[] = $createTableStatements[$tableName];

            foreach ($dependents[$tableName] as $dependentTable) {
                $inDegree[$dependentTable]--;
                if ($inDegree[$dependentTable] === 0) {
                    $queue[] = $dependentTable;
                    sort($queue);
                }
            }
        }

        if (count($ordered) !== count($createTableStatements)) {
            return array_values($createTableStatements);
        }

        return $ordered;
    }

    private function extractReferencedTables(string $statement): array
    {
        preg_match_all('/REFERENCES\s+`?([a-zA-Z0-9_]+)`?/i', $statement, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    private function summarizeSql(string $statement, int $limit = 200): string
    {
        $summary = preg_replace('/\s+/', ' ', trim($statement)) ?? trim($statement);

        if (strlen($summary) <= $limit) {
            return $summary;
        }

        return substr($summary, 0, $limit - 3) . '...';
    }

    private function isCommentStart(string $buffer): bool
    {
        $trimmed = rtrim($buffer);

        return $trimmed === '' || str_ends_with($trimmed, "\n") || str_ends_with($trimmed, "\r");
    }

    private function dropAllTables(): void
    {
        $tables = $this->db->listTables();

        foreach ($tables as $table) {
            $this->db->query('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
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
                    'created_at' => $this->formatTimestamp((int) $stat['mtime']),
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

    private function now(): Time
    {
        return Time::now($this->getAppTimezone());
    }

    private function formatTimestamp(int $timestamp): string
    {
        return Time::createFromTimestamp($timestamp, $this->getAppTimezone())->format('Y-m-d H:i:s');
    }

    private function getAppTimezone(): string
    {
        return config('App')->appTimezone ?: date_default_timezone_get();
    }
}
