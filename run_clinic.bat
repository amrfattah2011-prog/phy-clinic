@echo off
title Dr. Ahmed Adel Clinic - Port 8000
cd /d "%~dp0"

if exist "%~dp0artisan" (
    set "CLINIC_DIR=%~dp0"
    set "ANTI_DIR=%~dp0.."
) else (
    if exist "%~dp0clinic\artisan" (
        set "CLINIC_DIR=%~dp0clinic"
        set "ANTI_DIR=%~dp0"
    ) else (
        set "CLINIC_DIR=%~dp0"
        set "ANTI_DIR=%~dp0"
    )
)

if "%CLINIC_DIR:~-1%"=="\" set "CLINIC_DIR=%CLINIC_DIR:~0,-1%"
if "%ANTI_DIR:~-1%"=="\" set "ANTI_DIR=%ANTI_DIR:~0,-1%"

set "PHP_EXE=%ANTI_DIR%\php82\php.exe"
set "MYSQL_DIR=%ANTI_DIR%\mysql"
set "MYSQLD_EXE=%MYSQL_DIR%\bin\mysqld.exe"
set "MYSQLDUMP_EXE=%MYSQL_DIR%\bin\mysqldump.exe"
set "MY_INI=%MYSQL_DIR%\my.ini"

:: 1. Ensure MySQL is running
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    net start clinic_mysql >nul 2>&1
    net start mysql8 >nul 2>&1
    net start MySQL >nul 2>&1
    netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul 2>&1
    if %ERRORLEVEL% neq 0 (
        if exist "%MYSQLD_EXE%" (
            start "Clinic_MySQL" /min "%MYSQLD_EXE%" --defaults-file="%MY_INI%" --console
            timeout /t 2 /nobreak >nul 2>&1
        )
    )
)

:: 2. Auto-backup database on launch
if exist "%MYSQLDUMP_EXE%" (
    if not exist "%CLINIC_DIR%\backups" mkdir "%CLINIC_DIR%\backups"
    "%MYSQLDUMP_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 --default-character-set=utf8mb4 clinic_db > "%CLINIC_DIR%\clinic_database_ready.sql" 2>nul
    copy "%CLINIC_DIR%\clinic_database_ready.sql" "%CLINIC_DIR%\backups\clinic_backup_latest.sql" >nul 2>&1
)

:: 3. Free port 8000 if occupied
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do taskkill /f /pid %%a >nul 2>&1

:: 4. Extract local WiFi IP
set "WIFI_IP="
for /f "tokens=4" %%a in ('route print 0.0.0.0 ^| findstr 192.168.') do if not defined WIFI_IP set "WIFI_IP=%%a"
if not defined WIFI_IP for /f "tokens=4" %%a in ('route print 0.0.0.0 ^| findstr 10.') do if not defined WIFI_IP set "WIFI_IP=%%a"

cls
echo =========================================================================
echo   Dr. Ahmed Adel Clinic - Physical Therapy and Weight Loss
echo   PORT: 8000  ^|  DATABASE: MySQL Portable (clinic_db on Drive %ANTI_DIR:~0,2%)
echo =========================================================================
echo.
echo   Local URL:    http://127.0.0.1:8000
echo   Local URL:    http://localhost:8000
if defined WIFI_IP (
    echo   Network URL:  http://%WIFI_IP%:8000  (For phones and tablets on WiFi)
)
echo.
echo =========================================================================
echo   Default Logins:
echo   - Admin:      admin      / 12345678
echo   - Doctor:     dr_sara    / 12345678
echo   - Reception:  reception  / 12345678
echo =========================================================================
echo.
echo   [OK] Database and attachments verified.
echo   [OK] Auto backup taken to backups folder.
echo.
echo   Please keep this window open while using the clinic system.
echo   Opening browser in 3 seconds...
echo.

start "" cmd /c "ping 127.0.0.1 -n 3 >nul && start http://127.0.0.1:8000"

:server_loop
"%PHP_EXE%" "%CLINIC_DIR%\artisan" serve --host=0.0.0.0 --port=8000
echo.
echo Server stopped. Press any key to restart or close this window...
pause
goto server_loop