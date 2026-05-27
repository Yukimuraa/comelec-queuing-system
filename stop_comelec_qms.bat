@echo off
setlocal EnableExtensions
:: =========================================================================
:: COMMISSION ON ELECTIONS (COMELEC) - QMS SERVER TERMINATION TOOL
:: =========================================================================
:: Stops the Laravel dev server on port 8000 (no Laragon/XAMPP required).

echo =========================================================================
echo       Stopping COMELEC Queue Management System (QMS) Server...
echo =========================================================================
echo.

set "FOUND=0"
for /f "tokens=5" %%p in ('netstat -ano 2^>nul ^| findstr /R /C:":8000 .*LISTENING"') do (
    set "FOUND=1"
    echo [INFO] Terminating process PID %%p on port 8000...
    taskkill /F /PID %%p >nul 2>nul
)

if "%FOUND%"=="1" (
    echo [SUCCESS] Server on port 8000 stopped.
) else (
    echo [INFO] No process was listening on port 8000.
)

echo.
echo Press any key to exit...
pause >nul
