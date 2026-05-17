#!/bin/bash
# Automated Database Backup Script for Linux/Mac
# Vape Shop System - Laboratory Exercise 5

# Configuration
PROJECT_PATH="/var/www/html/VapeShopSystem"
PHP_PATH="/usr/bin/php"
LOG_FILE="$PROJECT_PATH/logs/backup.log"
MAX_BACKUPS=10

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

# Start backup process
write_log "Starting automated database backup"

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

# Create backup using PHP script
write_log "Creating backup with name: auto_backup_$(date '+%Y%m%d_%H%M%S')"
php spark backup:create --name="auto_backup_$(date '+%Y%m%d_%H%M%S')"

# Check if backup was successful
if [ $? -eq 0 ]; then
    write_log "Backup created successfully"
else
    write_log "Backup creation failed with error code $?"
    exit 1
fi

# Cleanup old backups (keep only last MAX_BACKUPS)
write_log "Cleaning up old backups (keeping last $MAX_BACKUPS backups)"

php spark backup:cleanup --keep=$MAX_BACKUPS

if [ $? -eq 0 ]; then
    write_log "Cleanup completed successfully"
else
    write_log "Cleanup failed with error code $?"
fi

# Get backup statistics
write_log "Getting backup statistics"
php spark backup:stats

write_log "Automated backup process completed successfully"
echo "Backup process completed. Check $LOG_FILE for details."

exit 0
