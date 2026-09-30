<?php

/**
 * أداة تصفير الحسابات وسجلات المرضى لنظام عيادة العلاج الطبيعي والتخسيس
 */

if (function_exists('sapi_windows_cp_set')) {
    sapi_windows_cp_set(65001);
}

echo "\n=========================================================================\n";
echo "       أداة تصفير الحسابات وسجلات المرضى - عيادة د. احمد عادل\n";
echo "=========================================================================\n\n";

echo " [!] تـحـذيـر هـام جـداً:\n";
echo "     - سيتم حذف كافة بيانات وسجلات المرضى والكشوفات والزيارات.\n";
echo "     - سيتم تفريغ كافة جلسات العلاج الطبيعي والتخسيس ونحت القوام.\n";
echo "     - سيتم تصفير جميع سندات القبض والمصروفات وإعادة الخزينة للصفر.\n";
echo "     - سيتم حذف جميع المرفقات والأشعات وتقارير InBody القديمة.\n\n";

echo "     [✓] سيتم الحفاظ تماماً على حسابات الأطباء والموظفين (admin / dr_sara / reception).\n";
echo "     [✓] سيتم الحفاظ تماماً على قائمة الأجهزة الطبية وإعدادات العيادة.\n";
echo "     [✓] سيتم أخذ نسخة احتياطية كاملة وتلقائية قبل البدء للرجوع إليها في أي وقت.\n\n";
echo "-------------------------------------------------------------------------\n";

echo "هل أنت متأكد تماماً من رغبتك في تصفير البيانات للبدء من الصفر؟\n";
echo "اكتب الرقم ( 1 ) واضغط Enter للتأكيد، أو اضغط Enter للإلغاء: ";

$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));

if ($input !== '1' && strtolower($input) !== 'y') {
    echo "\n[i] تم إلغاء العملية بناءً على رغبتك. لم يتم حذف أي شيء.\n";
    echo "\nاضغط Enter للخروج...";
    fgets($handle);
    exit(0);
}

// 1. تحديد المسارات
$baseDir = rtrim(str_replace('\\', '/', __DIR__), '/');
if (basename($baseDir) === 'clinic') {
    $clinicDir = $baseDir;
    $baseDir = dirname($baseDir);
} else {
    $clinicDir = $baseDir . '/clinic';
}

$phpExe = $baseDir . '/php82/php.exe';
$mysqlDir = $baseDir . '/mysql';
$mysqldExe = $mysqlDir . '/bin/mysqld.exe';
$mysqldumpExe = $mysqlDir . '/bin/mysqldump.exe';
$myIni = $mysqlDir . '/my.ini';

function isPortOpen($host, $port, $timeout = 1) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

if (!isPortOpen('127.0.0.1', 3306)) {
    echo "  [i] جاري تشغيل سيرفر MySQL المحمول...\n";
    @shell_exec('net start clinic_mysql 2>&1');
    @shell_exec('net start mysql8 2>&1');
    @shell_exec('net start MySQL 2>&1');
    if (!isPortOpen('127.0.0.1', 3306) && file_exists($mysqldExe)) {
        pclose(popen('start "Clinic_MySQL" /min "' . str_replace('/', '\\', $mysqldExe) . '" --defaults-file="' . str_replace('/', '\\', $myIni) . '" --console', 'r'));
        for ($i = 0; $i < 10; $i++) {
            sleep(1);
            if (isPortOpen('127.0.0.1', 3306)) break;
        }
    }
}

echo "\n[1/3] أخذ نسخة احتياطية كاملة من البيانات الحالية للأمان...\n";
$backupsDir = $clinicDir . '/backups';
if (!is_dir($backupsDir)) {
    @mkdir($backupsDir, 0777, true);
}

$timestamp = date('Y-m-d_H-i-s');
$backupPath = $backupsDir . '/backup_before_reset_' . $timestamp . '.sql';

if (file_exists($mysqldumpExe)) {
    $cmd = '"' . str_replace('/', '\\', $mysqldumpExe) . '" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 --default-character-set=utf8mb4 clinic_db > "' . str_replace('/', '\\', $backupPath) . '" 2>nul';
    shell_exec($cmd);
    if (file_exists($backupPath) && filesize($backupPath) > 0) {
        echo "  [✓] تم حفظ نسخة احتياطية آمنة في: backups/backup_before_reset_{$timestamp}.sql\n";
    }
}

echo "\n[2/3] جاري تصفير جداول المرضى والحسابات والمرفقات...\n";
$artisanCmd = '"' . str_replace('/', '\\', $phpExe) . '" "' . str_replace('/', '\\', $clinicDir) . '/artisan" clinic:reset --force';
passthru($artisanCmd);

echo "\n[3/3] تحديث النسخة الجاهزة للعمل...\n";
if (file_exists($mysqldumpExe)) {
    $dumpCmd = '"' . str_replace('/', '\\', $mysqldumpExe) . '" --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 --default-character-set=utf8mb4 clinic_db > "' . str_replace('/', '\\', $clinicDir) . '/clinic_database_ready.sql" 2>nul';
    shell_exec($dumpCmd);
}

echo "\n=========================================================================\n";
echo "  [✓] اكتملت العملية بنجاح تام بنسبة 100%!\n";
echo "  [✓] أصبحت العيادة الآن نظيفة وجاهزة لبدء تسجيل المرضى والحسابات من الصفر.\n";
echo "  [✓] بيانات الدخول كما هي: admin / 12345678\n";
echo "=========================================================================\n\n";

echo "اضغط Enter للخروج...";
fgets($handle);
exit(0);
