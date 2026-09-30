@echo off
title Clinic Setup - Portable Installation
cd /d "%~dp0"

echo =========================================================================
echo   Starting Clinic Setup on this Computer...
echo =========================================================================
echo.

:: 1. Check if PHP can run or if Microsoft Visual C++ is required
if exist "%~dp0php82\php.exe" (
    set "PHP_EXE=%~dp0php82\php.exe"
) else (
    if exist "%~dp0..\php82\php.exe" (
        set "PHP_EXE=%~dp0..\php82\php.exe"
    ) else (
        echo [ERROR] php82 folder not found!
        pause
        exit /b 1
    )
)

"%PHP_EXE%" -v >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [!] Microsoft Visual C++ Runtime is required on this PC.
    echo [i] Installing VC_redist.x64.exe automatically...
    if exist "%~dp0VC_redist.x64.exe" (
        "%~dp0VC_redist.x64.exe" /install /passive /norestart
    )
    timeout /t 3 /nobreak >nul 2>&1
)

:: 2. Run the master setup engine
"%PHP_EXE%" "%~dp0setup.php"

if %ERRORLEVEL% neq 0 (
    echo.
    echo [ERROR] Setup encountered an issue. Please see details above.
)

echo.
echo =========================================================================
echo   Press any key to exit this window...
echo =========================================================================
pause >nul
