@echo off
:: Resolves PROJECT_DIR and PHPRC. Usage: call "%~dp0_resolve_comelec_env.cmd" "%~dp0"
set "SCRIPT_DIR=%~1"
if "%SCRIPT_DIR:~-1%"=="\" set "SCRIPT_DIR=%SCRIPT_DIR:~0,-1%"

set "PROJECT_DIR=%SCRIPT_DIR%"
if exist "%SCRIPT_DIR%\comelec_qms.path" (
    set /p "PROJECT_DIR=" < "%SCRIPT_DIR%\comelec_qms.path"
    if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"
)

if not exist "%PROJECT_DIR%\artisan" (
    if exist "%USERPROFILE%\Documents\comelec-system_global\artisan" (
        set "PROJECT_DIR=%USERPROFILE%\Documents\comelec-system_global"
    ) else if exist "%USERPROFILE%\Documents\comelec-system\artisan" (
        set "PROJECT_DIR=%USERPROFILE%\Documents\comelec-system"
    )
)

set "PHPRC=%PROJECT_DIR%\php"
