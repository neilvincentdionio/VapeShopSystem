@echo off
REM Database Restore Script for Windows
REM Vape Shop System - Laboratory Exercise 5

setlocal enabledelayedexpansion

REM Configuration
set PROJECT_PATH=C:\xampp\htdocs\VapeShopSystem-main (1)\VapeShopSystem-main
set PHP_PATH=C:\xampp\php\php.exe
set LOG_FILE=%PROJECT_PATH%\logs\restore.log

REM Create logs directory if it doesn't exist
if not exist "%PROJECT_PATH%\logs" mkdir "%PROJECT_PATH%\logs"

REM Function to write log
:write_log
echo %date% %time% - %1 >> "%LOG_FILE%"
goto :eof

REM Check if backup file is provided
if "%1"=="" (
    echo Usage: %0 backup_filename.sql.gz
    echo.
    echo Example: %0 backup_2026-04-09_14-00-00.sql.gz
    goto :error_exit
)

set BACKUP_FILE=%1

REM Start restore process
call :write_log "Starting database restore from: %BACKUP_FILE%"

REM Change to project directory
cd /d "%PROJECT_PATH%"

REM Check if backup file exists
if not exist "writable\backups\%BACKUP_FILE%" (
    call :write_log "ERROR: Backup file not found: writable\backups\%BACKUP_FILE%"
    goto :error_exit
)

REM Restore database using PHP script
%PHP_PATH% spark backup:restore --file=%BACKUP_FILE%

REM Check if restore was successful
if %ERRORLEVEL% EQU 0 (
    call :write_log "Database restored successfully"
    echo Database restored successfully from: %BACKUP_FILE%
) else (
    call :write_log "Database restore failed with error code %ERRORLEVEL%"
    goto :error_exit
)

call :write_log "Database restore process completed successfully"
goto :end

:error_exit
call :write_log "Database restore process failed"
echo Restore process failed. Check %LOG_FILE% for details.
exit /b 1

:end
echo Restore process completed. Check %LOG_FILE% for details.
exit /b 0
