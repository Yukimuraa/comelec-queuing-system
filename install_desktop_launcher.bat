@echo off
setlocal EnableExtensions
:: Copies launchers to Desktop with comelec_qms.path pointing at this project.

set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"

set "DESKTOP=%USERPROFILE%\Desktop"
if not exist "%DESKTOP%" set "DESKTOP=%USERPROFILE%\OneDrive\Desktop"

echo =========================================================================
echo       Install COMELEC QMS Desktop Launchers
echo =========================================================================
echo.
echo [INFO] Project: %PROJECT_DIR%
echo [INFO] Desktop: %DESKTOP%
echo.

(
    echo %PROJECT_DIR%
) > "%DESKTOP%\comelec_qms.path"

copy /Y "%PROJECT_DIR%\start_comelec_qms.bat" "%DESKTOP%\start_comelec_qms.bat" >nul
copy /Y "%PROJECT_DIR%\stop_comelec_qms.bat" "%DESKTOP%\stop_comelec_qms.bat" >nul
copy /Y "%PROJECT_DIR%\_resolve_comelec_env.cmd" "%DESKTOP%\_resolve_comelec_env.cmd" >nul

echo [SUCCESS] Created on your Desktop:
echo         - start_comelec_qms.bat
echo         - stop_comelec_qms.bat
echo         - comelec_qms.path
echo.
echo         Double-click start_comelec_qms.bat to run the QMS.
echo.
pause
