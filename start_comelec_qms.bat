@echo off
setlocal EnableExtensions EnableDelayedExpansion
:: =========================================================================
:: COMMISSION ON ELECTIONS (COMELEC) - QUEUE MANAGEMENT SYSTEM LAUNCHER
:: =========================================================================
:: Starts the server and opens QMS in app window (PWA-style, no install).

echo =========================================================================
echo       Starting COMELEC Queue Management System (QMS)...
echo =========================================================================
echo.

call "%~dp0_resolve_comelec_env.cmd" "%~dp0"
cd /d "!PROJECT_DIR!"
set "PHPRC=!PROJECT_DIR!\php"
set "APP_URL=http://127.0.0.1:8000/admin?qms_app=1"

echo [INFO] Project directory: !CD!
echo.

:: --- PHP (must be on Windows PATH) ---
echo [INFO] Checking PHP...
where php >nul 2>nul
if !errorlevel! neq 0 (
    echo [ERROR] PHP was not found in PATH.
    echo         Install PHP 8.3+ and add C:\php to System Environment Variables ^> Path.
    echo.
    pause
    exit /b 1
)
for /f "delims=" %%v in ('php -r "echo PHP_VERSION;"') do set "PHP_VER=%%v"
echo [SUCCESS] PHP !PHP_VER!

php -r "exit(extension_loaded('openssl')?0:1);" >nul 2>nul
if !errorlevel! neq 0 (
    echo [ERROR] PHP OpenSSL extension is not loaded.
    echo         Run configure_global_php.bat from the project folder once.
    echo.
    pause
    exit /b 1
)

php -r "exit(extension_loaded('pdo_sqlite')?0:1);" >nul 2>nul
if !errorlevel! neq 0 (
    echo [ERROR] PHP SQLite extension is not loaded.
    echo         Run configure_global_php.bat from the project folder once.
    echo.
    pause
    exit /b 1
)

:: --- Laravel app ---
if not exist "artisan" (
    echo [ERROR] Laravel project not found at: !PROJECT_DIR!
    echo         Run install_desktop_launcher.bat from the project folder.
    echo.
    pause
    exit /b 1
)
if not exist "vendor\autoload.php" (
    echo [ERROR] Dependencies missing. Run setup_comelec_qms.bat first.
    pause
    exit /b 1
)
if not exist ".env" (
    echo [ERROR] .env file missing. Run setup_comelec_qms.bat first.
    pause
    exit /b 1
)

:: --- Frontend assets ---
if not exist "public\build\manifest.json" (
    echo [INFO] Frontend build not found. Building assets with npm...
    where npm >nul 2>nul
    if !errorlevel! neq 0 (
        echo [ERROR] npm was not found in PATH.
        pause
        exit /b 1
    )
    if not exist "node_modules\" call npm install
    call npm run build
    if !errorlevel! neq 0 (
        echo [ERROR] npm run build failed.
        pause
        exit /b 1
    )
)

:: --- Start Laravel server (separate window) ---
echo [INFO] Starting Laravel server on port 8000...
start "COMELEC QMS Server" /D "!PROJECT_DIR!" cmd /k "set PHPRC=%CD%\php && php artisan serve --host=127.0.0.1 --port=8000"

:: --- Wait until server responds ---
echo [INFO] Waiting for server to be ready...
set "TRIES=0"
:wait_server
timeout /t 1 /nobreak >nul
powershell -NoProfile -Command "try { $r = Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -UseBasicParsing -TimeoutSec 2; if ($r.StatusCode -ge 200) { exit 0 } else { exit 1 } } catch { exit 1 }" >nul 2>nul
if !errorlevel! equ 0 goto server_ready
set /a TRIES+=1
if !TRIES! lss 30 goto wait_server
echo [ERROR] Server did not start within 30 seconds.
pause
exit /b 1

:server_ready
echo [SUCCESS] Server is ready.

:: --- Open in app window (PWA-style, no browser tabs / no install) ---
echo [INFO] Launching QMS in app mode...
call :launch_app_window
if !errorlevel! neq 0 (
    echo [ERROR] Could not launch app window. Install Microsoft Edge or Google Chrome.
    pause
    exit /b 1
)

echo.
echo [SUCCESS] COMELEC QMS is running in app mode.
echo [INFO] Server window: "COMELEC QMS Server"
echo [INFO] Stop with stop_comelec_qms.bat
echo.
pause
exit /b 0

:: -------------------------------------------------------------------------
:: Launch Edge or Chrome in --app mode (standalone window, no install)
:: -------------------------------------------------------------------------
:launch_app_window
if exist "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" --app="!APP_URL!" --window-size=1280,800
    exit /b 0
)
if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" (
    start "" "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" --app="!APP_URL!" --window-size=1280,800
    exit /b 0
)
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" --app="!APP_URL!" --window-size=1280,800
    exit /b 0
)
if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" --app="!APP_URL!" --window-size=1280,800
    exit /b 0
)
where msedge >nul 2>nul && (
    start "" msedge --app="!APP_URL!" --window-size=1280,800
    exit /b 0
)
where chrome >nul 2>nul && (
    start "" chrome --app="!APP_URL!" --window-size=1280,800
    exit /b 0
)
exit /b 1
