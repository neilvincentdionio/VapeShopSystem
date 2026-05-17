@echo off
REM Automated Database Backup Script for Windows
REM Vape Shop System - Laboratory Exercise 5

setlocal enabledelayedexpansion

REM Configuration
set PROJECT_PATH=C:\xampp\htdocs\VapeShopSystem-main (1)\VapeShopSystem-main
set PHP_PATH=C:\xampp\php\php.exe
set LOG_FILE=%PROJECT_PATH%\logs\backup.log
set MAX_BACKUPS=10

REM Create logs directory if it doesn't exist
if not exist "%PROJECT_PATH%\logs" mkdir "%PROJECT_PATH%\logs"

REM Function to write log
:write_log
echo %date% %time% - %1 >> "%LOG_FILE%"
goto :eof

REM Start backup process
call :write_log "Starting automated database backup"

REM Change to project directory
cd /d "%PROJECT_PATH%"

REM Create backup using PHP script
%PHP_PATH% spark backup:create --name=auto_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%

REM Check if backup was successful
if %ERRORLEVEL% EQU 0 (
    call :write_log "Backup created successfully"
) else (
    call :write_log "Backup creation failed with error code %ERRORLEVEL%"
    goto :error_exit
)

REM Cleanup old backups (keep only last MAX_BACKUPS)
call :write_log "Cleaning up old backups (keeping last %MAX_BACKUPS% backups)"

%PHP_PATH% spark backup:cleanup --keep=%MAX_BACKUPS%

if %ERRORLEVEL% EQU 0 (
    call :write_log "Cleanup completed successfully"
) else (
    call :write_log "Cleanup failed with error code %ERRORLEVEL%"
)

REM Get backup statistics
call :write_log "Getting backup statistics"

%PHP_PATH% spark backup:stats

call :write_log "Automated backup process completed successfully"
goto :end

:error_exit
call :write_log "Automated backup process failed"
exit /b 1

:end
echo Backup process completed. Check %LOG_FILE% for details.
exit /b 0
