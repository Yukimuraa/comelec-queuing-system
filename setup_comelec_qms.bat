@echo off
setlocal EnableExtensions EnableDelayedExpansion
:: =========================================================================
:: COMELEC QMS - ONE-TIME / REPAIR SETUP (global PHP, Composer, Node, npm)
:: =========================================================================

echo =========================================================================
echo       COMELEC QMS - Local Setup
echo =========================================================================
echo.

call "%~dp0_resolve_comelec_env.cmd" "%~dp0"
cd /d "!PROJECT_DIR!"
set "PHPRC=!PROJECT_DIR!\php"
echo [INFO] Project directory: !CD!
echo.

if not exist "artisan" (
    echo [ERROR] Laravel project not found at: !PROJECT_DIR!
    echo         Run this from the project folder or use install_desktop_launcher.bat
    pause
    exit /b 1
)

call :require_tool php "PHP 8.3+"

if not exist "C:\php\php.ini" if exist "php\php.ini" (
    echo [INFO] Installing C:\php\php.ini for OpenSSL, SQLite, etc...
    copy /Y "php\php.ini" "C:\php\php.ini" >nul
)

call :require_tool composer "Composer"
call :require_tool node "Node.js"
call :require_tool npm "npm"
echo.

if not exist ".env" (
    echo [INFO] Creating .env from .env.example...
    copy /Y ".env.example" ".env" >nul
)

echo [INFO] composer install...
call composer install --no-interaction
if %errorlevel% neq 0 goto :failed

if not exist "database\database.sqlite" (
    echo [INFO] Creating SQLite database file...
    type nul > "database\database.sqlite"
)

findstr /B "APP_KEY=$" .env >nul 2>nul && (
    echo [INFO] Generating application key...
    php artisan key:generate --force
)

echo [INFO] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 goto :failed

echo [INFO] npm install...
call npm install
if %errorlevel% neq 0 goto :failed

echo [INFO] npm run build...
call npm run build
if %errorlevel% neq 0 goto :failed

echo.
echo [SUCCESS] Setup complete. Run start_comelec_qms.bat to launch the QMS.
echo.
pause
exit /b 0

:require_tool
where %~1 >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] %~2 ^(%~1^) was not found in PATH.
    echo         Add it to System Environment Variables ^> Path, then run this script again.
    pause
    exit /b 1
)
echo [SUCCESS] Found %~2
exit /b 0

:failed
echo.
echo [ERROR] Setup failed. Fix the errors above and run this script again.
pause
exit /b 1
