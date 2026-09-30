@echo off
title Reset Clinic Data
cd /d "%~dp0"

echo =========================================================================
echo       Reset Clinic Data and Patient Records
echo =========================================================================
echo.
echo  WARNING: All patient records, visits, sessions, and payments will be cleared.
echo           Staff accounts and settings will be preserved.
echo.
echo =========================================================================
set /p CONFIRM="Type 1 and press Enter to CONFIRM, or press Enter to cancel: "

if not "%CONFIRM%"=="1" (
    echo.
    echo Operation cancelled. No data was deleted.
    echo.
    pause
    exit /b 0
)

echo.
echo Starting Reset...
echo.

set "BASE_DIR=%~dp0"
if "%BASE_DIR:~-1%"=="\" set "BASE_DIR=%BASE_DIR:~0,-1%"

set "MYSQL_EXE=%BASE_DIR%\mysql\bin\mysql.exe"
set "MYSQLD_EXE=%BASE_DIR%\mysql\bin\mysqld.exe"
set "MYSQLDUMP_EXE=%BASE_DIR%\mysql\bin\mysqldump.exe"
set "MY_INI=%BASE_DIR%\mysql\my.ini"
set "CLINIC_DIR=%BASE_DIR%\clinic"

:: Ensure MySQL is running
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [i] Starting MySQL server...
    if exist "%MYSQLD_EXE%" (
        start "Clinic_MySQL" /min "%MYSQLD_EXE%" --defaults-file="%MY_INI%" --console
        timeout /t 3 /nobreak >nul 2>&1
    )
)

:: Take backup
echo [1/3] Taking full backup before reset...
if not exist "%CLINIC_DIR%\backups" mkdir "%CLINIC_DIR%\backups"
"%MYSQLDUMP_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 --default-character-set=utf8mb4 clinic_db > "%CLINIC_DIR%\backups\backup_before_reset.sql" 2>nul

:: Truncate tables
echo [2/3] Resetting patient and financial records...
"%MYSQL_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 clinic_db -e "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE activity_logs; TRUNCATE TABLE expenses; TRUNCATE TABLE payments; TRUNCATE TABLE session_devices; TRUNCATE TABLE weight_loss_sessions; TRUNCATE TABLE weight_loss_plans; TRUNCATE TABLE therapy_sessions; TRUNCATE TABLE therapy_plans; TRUNCATE TABLE visits; TRUNCATE TABLE patient_disease; TRUNCATE TABLE patient_attachments; TRUNCATE TABLE patients; SET FOREIGN_KEY_CHECKS=1;"

:: Clean attachments
if exist "%CLINIC_DIR%\storage\app\public\attachments" (
    del /q /s "%CLINIC_DIR%\storage\app\public\attachments\*.*" >nul 2>&1
)

:: Update database ready file
echo [3/3] Updating ready database snapshot...
"%MYSQLDUMP_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 --default-character-set=utf8mb4 clinic_db > "%CLINIC_DIR%\clinic_database_ready.sql" 2>nul

echo.
echo =========================================================================
echo   SUCCESS! Clinic data has been completely reset.
echo   Ready for fresh patient registration.
echo   Login: admin / 12345678
echo =========================================================================
echo.
pause
