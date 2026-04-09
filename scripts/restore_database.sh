#!/bin/bash
# Database Restore Script for Linux/Mac
# Vape Shop System - Laboratory Exercise 5

# Configuration
PROJECT_PATH="/var/www/html/VapeShopSystem"
PHP_PATH="/usr/bin/php"
LOG_FILE="$PROJECT_PATH/logs/restore.log"

# Create logs directory if it doesn't exist
mkdir -p "$PROJECT_PATH/logs"

# Function to write log
write_log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check if backup file is provided
if [ $# -eq 0 ]; then
    echo "Usage: $0 backup_filename.sql.gz"
    echo
    echo "Example: $0 backup_2026-04-09_14-00-00.sql.gz"
    echo
    echo "Available backups:"
    ls -la "$PROJECT_PATH/writable/backups/" | grep ".sql.gz$" | awk '{print $9}'
    exit 1
fi

BACKUP_FILE="$1"

# Start restore process
write_log "Starting database restore from: $BACKUP_FILE"

# Check if PHP exists
if ! command_exists php; then
    write_log "ERROR: PHP not found. Please install PHP."
    exit 1
fi

# Change to project directory
cd "$PROJECT_PATH" || {
    write_log "ERROR: Cannot change to project directory: $PROJECT_PATH"
    exit 1
}

# Check if spark command exists
if [ ! -f "spark" ]; then
    write_log "ERROR: spark command not found in project directory"
    exit 1
fi

# Make spark executable
chmod +x spark

# Check if backup file exists
if [ ! -f "writable/backups/$BACKUP_FILE" ]; then
    write_log "ERROR: Backup file not found: writable/backups/$BACKUP_FILE"
    echo "ERROR: Backup file not found: writable/backups/$BACKUP_FILE"
    echo
    echo "Available backups:"
    ls -la writable/backups/ | grep ".sql.gz$" | awk '{print $9}'
    exit 1
fi

# Restore database using PHP script
write_log "Restoring database from backup: $BACKUP_FILE"
php spark backup:restore --file="$BACKUP_FILE"

# Check if restore was successful
if [ $? -eq 0 ]; then
    write_log "Database restored successfully"
    echo "Database restored successfully from: $BACKUP_FILE"
else
    write_log "Database restore failed with error code $?"
    echo "Restore process failed. Check $LOG_FILE for details."
    exit 1
fi

write_log "Database restore process completed successfully"
echo "Restore process completed. Check $LOG_FILE for details."

exit 0
