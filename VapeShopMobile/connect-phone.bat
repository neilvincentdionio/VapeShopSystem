@echo off
setlocal EnableExtensions

cd /d "%~dp0"

rem --- Find adb.exe (same path as SETUP_GUIDE; fallback to PATH / default SDK) ---
set "ADB="
if exist "%LOCALAPPDATA%\Android\Sdk\platform-tools\adb.exe" (
  set "ADB=%LOCALAPPDATA%\Android\Sdk\platform-tools\adb.exe"
)
if not defined ADB if exist "C:\Users\ADMIN\AppData\Local\Android\Sdk\platform-tools\adb.exe" (
  set "ADB=C:\Users\ADMIN\AppData\Local\Android\Sdk\platform-tools\adb.exe"
)
if not defined ADB (
  where adb >nul 2>&1 && set "ADB=adb"
)

if not defined ADB (
  echo [ERROR] adb.exe not found.
  echo Install Android SDK Platform-Tools or edit this script with your adb path.
  goto :end
)

echo Using: %ADB%
echo.

echo [1/6] Restart ADB (fixes many "reverse failed" cases)...
"%ADB%" kill-server >nul 2>&1
timeout /t 1 /nobreak >nul
"%ADB%" start-server
if errorlevel 1 (
  echo [ERROR] Could not start adb server.
  goto :end
)

echo [2/6] Check USB device (up to 30 seconds)...
set "FOUND=0"
for /L %%i in (1,1,15) do (
  "%ADB%" devices 2>nul | findstr /R /C:"device$" >nul && set "FOUND=1" && goto :device_ok
  timeout /t 2 /nobreak >nul
)
:device_ok
if "%FOUND%"=="0" (
  echo.
  echo [ERROR] No authorized device. Current list:
  "%ADB%" devices
  echo.
  echo On phone: enable USB debugging and tap Allow on the popup.
  echo Try: USB mode = File transfer / MTP, then unplug and replug.
  goto :end
)

echo [3/6] Connected devices:
"%ADB%" devices
echo.

echo [4/6] Remove old reverse on port 8080 (if any)...
"%ADB%" reverse --remove tcp:8080 >nul 2>&1

echo [5/6] Apply reverse: phone 127.0.0.1:8080 -^> PC localhost:80 (XAMPP Apache)...
"%ADB%" reverse tcp:8080 tcp:80
if errorlevel 1 (
  echo [ERROR] adb reverse failed.
  echo Run this manually in PowerShell (often works after phone is unlocked):
  echo   ^& "%ADB%" reverse tcp:8080 tcp:80
  goto :end
)

echo [6/6] Active reverse rules:
"%ADB%" reverse --list
echo.
echo [OK] Start XAMPP Apache, then open the app.
echo     API base: http://127.0.0.1:8080/VapeShopSystem/mobile_api/
echo.
echo Note: Reverse is lost when you unplug USB or reboot the phone — run this again.

:end
if /I not "%~1"=="--no-pause" pause
endlocal
