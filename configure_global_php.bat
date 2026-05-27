@echo off
setlocal EnableExtensions
:: Copies Laravel-required PHP extensions config to C:\php\php.ini (one-time).

echo =========================================================================
echo       Configure global PHP for COMELEC QMS
echo =========================================================================
echo.

if not exist "php\php.ini" (
    echo [ERROR] php\php.ini not found. Run this from the project root.
    pause
    exit /b 1
)

if not exist "C:\php\php.exe" (
    echo [ERROR] C:\php\php.exe not found. Install PHP 8.3+ to C:\php first.
    pause
    exit /b 1
)

echo [INFO] Copying php\php.ini to C:\php\php.ini ...
copy /Y "php\php.ini" "C:\php\php.ini" >nul
if %errorlevel% neq 0 (
    echo [ERROR] Could not write C:\php\php.ini. Try running as Administrator.
    pause
    exit /b 1
)

php -r "exit(extension_loaded('openssl') && extension_loaded('pdo_sqlite') ? 0 : 1);"
if %errorlevel% neq 0 (
    echo [ERROR] Extensions still not loaded. Check C:\php\ext exists.
    pause
    exit /b 1
)

echo [SUCCESS] Global PHP configured (OpenSSL, SQLite, mbstring, etc.)
echo.
pause
