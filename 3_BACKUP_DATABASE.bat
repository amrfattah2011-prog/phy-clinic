@echo off
chcp 65001 >nul
title Backup Clinic Database
cd /d "%~dp0"

:: ضبط المسار الديناميكي
set "CURRENT_DIR=%~dp0"
if "%CURRENT_DIR:~-1%"=="\" set "CURRENT_DIR=%CURRENT_DIR:~0,-1%"

if exist "%CURRENT_DIR%\..\mysql\bin\mysqldump.exe" (
    set "MYSQLDUMP_EXE=%CURRENT_DIR%\..\mysql\bin\mysqldump.exe"
) else (
    if exist "C:\AppServ\MySQL\bin\mysqldump.exe" (
        set "MYSQLDUMP_EXE=C:\AppServ\MySQL\bin\mysqldump.exe"
    ) else (
        where mysqldump.exe >nul 2>&1
        if %ERRORLEVEL% equ 0 set "MYSQLDUMP_EXE=mysqldump"
    )
)

if not defined MYSQLDUMP_EXE (
    echo [ERROR] لم يتم العثور على أداة mysqldump!
    pause
    exit /b 1
)

if not exist "%~dp0backups" mkdir "%~dp0backups"

:: استخراج التاريخ والوقت للنسخة المؤرشفة
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value 2^>nul') do set "DATETIME=%%I"
if defined DATETIME (
    set "TIMESTAMP=%DATETIME:~0,4%-%DATETIME:~4,2%-%DATETIME:~6,2%_%DATETIME:~8,2%-%DATETIME:~10,2%"
) else (
    set "TIMESTAMP=manual"
)

echo =========================================================================
echo       أخذ نسخة احتياطية كاملة ومؤرخة من قاعدة بيانات العيادة
echo =========================================================================
echo.

"%MYSQLDUMP_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 --default-character-set=utf8mb4 --routines --triggers clinic_db > "%~dp0clinic_database_ready.sql" 2>nul
if %ERRORLEVEL% equ 0 (
    copy "%~dp0clinic_database_ready.sql" "%~dp0backups\clinic_backup_latest.sql" >nul
    copy "%~dp0clinic_database_ready.sql" "%~dp0backups\clinic_backup_%TIMESTAMP%.sql" >nul
    echo [✓] تم حفظ النسخة الاحتياطية بنجاح في:
    echo      1. %~dp0clinic_database_ready.sql
    echo      2. %~dp0backups\clinic_backup_latest.sql
    echo      3. %~dp0backups\clinic_backup_%TIMESTAMP%.sql
    echo.
    echo جميع بيانات المرضى والكشوفات والحسابات في أمان تام!
) else (
    echo [X] فشل الاتصال بقاعدة البيانات. تأكد من أن سيرفر MySQL قيد التشغيل.
)

echo.
pause