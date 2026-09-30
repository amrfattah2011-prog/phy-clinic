@echo off
chcp 65001 >nul
title استرجاع وربط قاعدة بيانات العيادة
cd /d "%~dp0"

cls
echo =========================================================================
echo       أداة استرجاع وربط قاعدة بيانات العيادة الأصلية
echo =========================================================================
echo.

set "CURRENT_DIR=%~dp0"
if "%CURRENT_DIR:~-1%"=="\" set "CURRENT_DIR=%CURRENT_DIR:~0,-1%"

if exist "%CURRENT_DIR%\..\mysql\bin\mysql.exe" (
    set "MYSQL_EXE=%CURRENT_DIR%\..\mysql\bin\mysql.exe"
) else (
    if exist "C:\AppServ\MySQL\bin\mysql.exe" (
        set "MYSQL_EXE=C:\AppServ\MySQL\bin\mysql.exe"
    ) else (
        where mysql.exe >nul 2>&1
        if %ERRORLEVEL% equ 0 set "MYSQL_EXE=mysql"
    )
)

if not defined MYSQL_EXE (
    echo [X] خطأ: لم يتم العثور على برنامج MySQL!
    pause
    exit /b 1
)

set "BACKUP_SQL=%~dp0clinic_database_ready.sql"
if not exist "%BACKUP_SQL%" (
    if exist "%~dp0backups\clinic_backup_latest.sql" (
        set "BACKUP_SQL=%~dp0backups\clinic_backup_latest.sql"
    ) else (
        echo [X] خطأ: لم يتم العثور على ملف النسخة الاحتياطية!
        pause
        exit /b 1
    )
)

echo [✓] تم العثور على ملف البيانات الأصلي: %BACKUP_SQL%
echo.
echo جاري استرجاع كافة الجداول والبيانات إلى clinic_db...

"%MYSQL_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 -e "CREATE DATABASE IF NOT EXISTS clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>&1
"%MYSQL_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 clinic_db < "%BACKUP_SQL%"

if %ERRORLEVEL% equ 0 (
    echo [✓] تم استرجاع جميع السجلات بنجاح تام!
) else (
    echo [X] حدث خطأ أثناء استيراد البيانات!
)

echo.
pause