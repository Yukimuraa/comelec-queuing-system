@echo off
setlocal EnableExtensions EnableDelayedExpansion
:: =========================================================================
:: COMMISSION ON ELECTIONS (COMELEC) - QUEUE MANAGEMENT SYSTEM LAUNCHER
:: =========================================================================
:: Works from the project folder OR from Desktop (with comelec_qms.path).

echo =========================================================================
echo       Starting COMELEC Queue Management System (QMS)...
echo =========================================================================
echo.

call "%~dp0_resolve_comelec_env.cmd" "%~dp0"
cd /d "!PROJECT_DIR!"
set "PHPRC=!PROJECT_DIR!\php"

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
    echo.
    echo         If this .bat file is on your Desktop, create a file named:
    echo         comelec_qms.path
    echo         in the same folder as this script, containing one line:
    echo         C:\Users\ASUS\Documents\comelec-system_global
    echo.
    echo         Or run install_desktop_launcher.bat from the project folder.
    echo.
    pause
    exit /b 1
)
if not exist "vendor\autoload.php" (
    echo [ERROR] Dependencies missing. Run setup_comelec_qms.bat first.
    echo.
    pause
    exit /b 1
)
if not exist ".env" (
    echo [ERROR] .env file missing. Run setup_comelec_qms.bat first.
    echo.
    pause
    exit /b 1
)

:: --- Frontend assets (Vite build; no dev server required) ---
if not exist "public\build\manifest.json" (
    echo [INFO] Frontend build not found. Building assets with npm...
    where npm >nul 2>nul
    if !errorlevel! neq 0 (
        echo [ERROR] npm was not found in PATH. Install Node.js and try again.
        echo.
        pause
        exit /b 1
    )
    if not exist "node_modules\" (
        echo [INFO] Running npm install...
        call npm install
        if !errorlevel! neq 0 (
            echo [ERROR] npm install failed.
            pause
            exit /b 1
        )
    )
    call npm run build
    if !errorlevel! neq 0 (
        echo [ERROR] npm run build failed.
        pause
        exit /b 1
    )
    echo [SUCCESS] Frontend assets built.
    echo.
)

:: --- Start server ---
echo [INFO] Opening http://127.0.0.1:8000 in your browser...
start "" "http://127.0.0.1:8000"

echo [INFO] Starting Laravel (php artisan serve) on port 8000...
echo [INFO] Stop with stop_comelec_qms.bat or close this window.
echo.
php artisan serve --host=127.0.0.1 --port=8000

echo.
echo [WARNING] Laravel server stopped.
pause
