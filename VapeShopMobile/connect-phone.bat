@echo off

set "ADB=C:\Users\ADMIN\AppData\Local\Android\Sdk\platform-tools\adb.exe"

if not exist "%ADB%" (
  echo [ERROR] adb.exe not found:
  echo         %ADB%
  echo Update this file with your correct Android SDK path.
  goto :end
)

echo [1/5] Starting ADB server...
"%ADB%" start-server >nul 2>&1

echo [2/5] Waiting for USB device...
"%ADB%" wait-for-device >nul 2>&1

echo [3/5] Connected devices:
"%ADB%" devices

echo [4/5] Applying reverse tunnel (tcp:8080 -^> tcp:80)...
"%ADB%" reverse tcp:8080 tcp:80 || (
  echo [ERROR] Failed to apply adb reverse.
  echo Make sure:
  echo  - Phone is connected via USB
  echo  - USB debugging is enabled
  echo  - "Allow USB debugging" prompt is accepted on phone
  goto :end
)

echo [5/5] Active reverse rules:
"%ADB%" reverse --list
echo.
echo [OK] Phone can use http://127.0.0.1:8080/ to reach local Apache.

:end
if /I not "%~1"=="--no-pause" pause