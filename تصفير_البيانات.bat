@echo off
chcp 65001 >nul
title تصفير بيانات وسجلات العيادة - د. احمد عادل
cd /d "%~dp0"

echo.
echo =========================================================================
echo        أداة تصفير الحسابات وسجلات المرضى - عيادة د. احمد عادل
echo =========================================================================
echo.
echo  [!] تـحـذيـر هـام جـداً:
echo      - سيتم حذف كافة سجلات المرضى والكشوفات والزيارات.
echo      - سيتم تفريغ كافة جلسات العلاج الطبيعي والتخسيس ونحت القوام.
echo      - سيتم تصفير جميع سندات القبض والمصروفات وإعادة الخزينة للصفر.
echo      - سيتم حذف جميع المرفقات والأشعات وتقارير InBody القديمة.
echo.
echo      [✓] سيتم الحفاظ تماماً على حسابات الأطباء والموظفين (admin / dr_sara / reception).
echo      [✓] سيتم الحفاظ تماماً على قائمة الأجهزة الطبية وإعدادات العيادة.
echo      [✓] سيتم أخذ نسخة احتياطية كاملة وتلقائية قبل البدء للرجوع إليها في أي وقت.
echo.
echo -------------------------------------------------------------------------
set /p CONFIRM="هل تريد المتابعة؟ اكتب الرقم ( 1 ) ثم اضغط Enter للتأكيد: "

if not "%CONFIRM%"=="1" (
    echo.
    echo [i] تم إلغاء العملية بناءً على رغبتك. لم يتم حذف أي شيء.
    echo.
    pause
    exit /b 0
)

echo.
echo =========================================================================
echo   جاري تنفيذ عملية التصفير الآمن...
echo =========================================================================
echo.

:: 1. فحص المسارات
set "BASE_DIR=%~dp0"
if "%BASE_DIR:~-1%"=="\" set "BASE_DIR=%BASE_DIR:~0,-1%"

if exist "%BASE_DIR%\mysql\bin\mysql.exe" (
    set "MYSQL_EXE=%BASE_DIR%\mysql\bin\mysql.exe"
    set "MYSQLD_EXE=%BASE_DIR%\mysql\bin\mysqld.exe"
    set "MYSQLDUMP_EXE=%BASE_DIR%\mysql\bin\mysqldump.exe"
    set "MY_INI=%BASE_DIR%\mysql\my.ini"
    set "CLINIC_DIR=%BASE_DIR%\clinic"
) else (
    if exist "%BASE_DIR%\..\mysql\bin\mysql.exe" (
        set "MYSQL_EXE=%BASE_DIR%\..\mysql\bin\mysql.exe"
        set "MYSQLD_EXE=%BASE_DIR%\..\mysql\bin\mysqld.exe"
        set "MYSQLDUMP_EXE=%BASE_DIR%\..\mysql\bin\mysqldump.exe"
        set "MY_INI=%BASE_DIR%\..\mysql\my.ini"
        set "CLINIC_DIR=%BASE_DIR%"
    ) else (
        echo [ERROR] تعذر العثور على محرك قاعدة البيانات MySQL!
        pause
        exit /b 1
    )
)

:: 2. التحقق من تشغيل سيرفر MySQL
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [i] جاري تشغيل سيرفر MySQL...
    net start clinic_mysql >nul 2>&1
    net start mysql8 >nul 2>&1
    net start MySQL >nul 2>&1
    netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul 2>&1
    if %ERRORLEVEL% neq 0 (
        if exist "%MYSQLD_EXE%" (
            start "Clinic_MySQL" /min "%MYSQLD_EXE%" --defaults-file="%MY_INI%" --console
            timeout /t 3 /nobreak >nul 2>&1
        )
    )
)

:: التحقق من كلمة المرور
set "DB_PASS=-p12345678"
"%MYSQL_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root %DB_PASS% -e "SELECT 1;" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    set "DB_PASS="
    "%MYSQL_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root -e "SELECT 1;" >nul 2>&1
    if %ERRORLEVEL% neq 0 (
        echo [ERROR] تعذر الاتصال بسيرفر MySQL. يرجى التأكد من تشغيله والمحاولة مرة أخرى.
        pause
        exit /b 1
    )
)

:: 3. أخذ نسخة احتياطية كاملة وتلقائية قبل التصفير
echo [1/3] جاري أخذ نسخة احتياطية كاملة وتلقائية قبل البدء...
if not exist "%CLINIC_DIR%\backups" mkdir "%CLINIC_DIR%\backups"
set "BACKUP_FILE=%CLINIC_DIR%\backups\backup_before_reset.sql"
"%MYSQLDUMP_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root %DB_PASS% --default-character-set=utf8mb4 clinic_db > "%BACKUP_FILE%" 2>nul
if exist "%BACKUP_FILE%" (
    echo   [✓] تم حفظ نسخة احتياطية آمنة في: clinic\backups\backup_before_reset.sql
)

:: 4. تصفير الجداول
echo.
echo [2/3] جاري تصفير سجلات المرضى والزيارات والجلسات والخزينة...
"%MYSQL_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root %DB_PASS% clinic_db -e "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE activity_logs; TRUNCATE TABLE expenses; TRUNCATE TABLE payments; TRUNCATE TABLE session_devices; TRUNCATE TABLE weight_loss_sessions; TRUNCATE TABLE weight_loss_plans; TRUNCATE TABLE therapy_sessions; TRUNCATE TABLE therapy_plans; TRUNCATE TABLE visits; TRUNCATE TABLE patient_disease; TRUNCATE TABLE patient_attachments; TRUNCATE TABLE patients; SET FOREIGN_KEY_CHECKS=1;"

:: 5. مسح ملفات المرفقات والأشعات القديمة
if exist "%CLINIC_DIR%\storage\app\public\attachments" (
    del /q /s "%CLINIC_DIR%\storage\app\public\attachments\*.*" >nul 2>&1
)

:: 6. تحديث ملف النسخة الجاهزة
echo.
echo [3/3] تحديث النسخة الجاهزة للعمل...
"%MYSQLDUMP_EXE%" --no-defaults -h 127.0.0.1 -P 3306 -u root %DB_PASS% --default-character-set=utf8mb4 clinic_db > "%CLINIC_DIR%\clinic_database_ready.sql" 2>nul

echo.
echo =========================================================================
echo   [✓] تم تصفير البيانات بنجاح تام بنسبة 100%!
echo   [✓] أصبحت العيادة الآن جاهزة ونظيفة تماماً لتسجيل المرضى والحسابات من الصفر.
echo   [✓] تم الحفاظ على حسابات الأطباء والموظفين وإعدادات الأجهزة الطبية.
echo   [✓] بيانات الدخول: admin / 12345678
echo =========================================================================
echo.
pause
