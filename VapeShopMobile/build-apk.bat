@echo off
setlocal
cd /d "%~dp0"

if exist "%ProgramFiles%\Android\Android Studio\jbr" (
    set "JAVA_HOME=%ProgramFiles%\Android\Android Studio\jbr"
) else if exist "%LOCALAPPDATA%\Programs\Android\Android Studio\jbr" (
    set "JAVA_HOME=%LOCALAPPDATA%\Programs\Android\Android Studio\jbr"
) else (
    echo ERROR: Android Studio JBR not found. Set JAVA_HOME manually.
    exit /b 1
)

echo Building debug APK...
call gradlew.bat assembleDebug
if errorlevel 1 exit /b 1

set "APK=%~dp0app\build\outputs\apk\debug\app-debug.apk"
set "DEST=%~dp0..\public\downloads\QuickPuffMobile.apk"

if not exist "%APK%" (
    echo ERROR: APK not found at %APK%
    exit /b 1
)

copy /Y "%APK%" "%DEST%" >nul
echo.
echo OK: %APK%
echo Copied to: %DEST%
echo.
echo In Android Studio Project view, refresh app/build/outputs/apk/debug/
pause
