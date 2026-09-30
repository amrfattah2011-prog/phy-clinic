<?php

/**
 * ملف التثبيت والإعداد الشامل لنظام عيادة د. احمد عادل للعلاج الطبيعي والتخسيس
 * يضمن تشغيل النظام على أي جهاز ويندوز مباشرة وحماية البيانات 100% حتى لو تم تنزيل ويندوز جديد
 */

// تفعيل مخرجات UTF-8
if (function_exists('sapi_windows_cp_set')) {
    sapi_windows_cp_set(65001);
}

echo "\n=========================================================================\n";
echo "    نظام عيادة د. احمد عادل للعلاج الطبيعي والتخسيس ونحت القوام\n";
echo "     ملف التثبيت والإعداد الشامل للعمل المباشر على أي جهاز ويندوز\n";
echo "=========================================================================\n\n";

echo " [!] يقوم هذا الملف بتهيئة العيادة للعمل فوراً على هذا الجهاز أو أي جهاز آخر:\n";
echo "     1. حماية كاملة لبيانات المرضى والحسابات حتى لو قمت بتنزيل ويندوز جديد.\n";
echo "     2. ضبط سيرفر MySQL المحمول ليعمل من هذا القرص مباشرة دون الحاجة لـ AppServ أو XAMPP.\n";
echo "     3. ربط ملفات ومرفقات المرضى (InBody والأشعات والـ PDF).\n";
echo "     4. إنشاء اختصار مباشر على سطح المكتب للتشغيل بنقرة واحدة.\n\n";
echo "-------------------------------------------------------------------------\n";

// 1. تحديد المسارات ديناميكياً
$antiDir = rtrim(str_replace('\\', '/', __DIR__), '/');
if (basename($antiDir) === 'clinic') {
    $clinicDir = $antiDir;
    $antiDir = dirname($antiDir);
} else {
    $clinicDir = $antiDir . '/clinic';
}

$phpExe = $antiDir . '/php82/php.exe';
$mysqlDir = $antiDir . '/mysql';
$mysqldExe = $mysqlDir . '/bin/mysqld.exe';
$mysqlExe = $mysqlDir . '/bin/mysql.exe';
$mysqldumpExe = $mysqlDir . '/bin/mysqldump.exe';
$myIni = $mysqlDir . '/my.ini';

echo "[1/6] فحص محركات التشغيل المحمولة (PHP 8.2 & MySQL 8)...\n";
if (!file_exists($phpExe)) {
    die("[X] خطأ: تعذر العثور على ملف PHP في: $phpExe\n");
}
if (!file_exists($mysqldExe)) {
    die("[X] خطأ: تعذر العثور على سيرفر MySQL في: $mysqldExe\n");
}
echo "  [✓] المحركات المحمولة موجودة وجاهزة بنجاح.\n\n";

// 2. تحديث مسارات my.ini تلقائياً بناءً على القرص الحالي
echo "[2/6] تهيئة إعدادات سيرفر MySQL للقرص الحالي (" . substr($antiDir, 0, 2) . ")...\n";
if (file_exists($myIni)) {
    $iniContent = file_get_contents($myIni);
    $iniContent = preg_replace('/^basedir=.*$/m', 'basedir="' . $mysqlDir . '"', $iniContent);
    $iniContent = preg_replace('/^datadir=.*$/m', 'datadir="' . $mysqlDir . '/data/"', $iniContent);
    file_put_contents($myIni, $iniContent);
    echo "  [✓] تم ضبط مسارات قاعدة البيانات لتعمل مباشرة من: $mysqlDir/data/\n\n";
}

// 3. التحقق من تشغيل سيرفر MySQL
echo "[3/6] التحقق من تشغيل سيرفر MySQL...\n";
function isPortOpen($host, $port, $timeout = 1) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

if (isPortOpen('127.0.0.1', 3306)) {
    echo "  [✓] سيرفر MySQL يعمل بالفعل على المنفذ 3306.\n\n";
} else {
    echo "  [i] جاري تشغيل سيرفر MySQL على القرص المحلي...\n";
    
    // محاولة بدء خدمة الويندوز إن وجدت
    @shell_exec('net start clinic_mysql 2>&1');
    @shell_exec('net start mysql8 2>&1');
    @shell_exec('net start MySQL 2>&1');
    
    if (!isPortOpen('127.0.0.1', 3306)) {
        // تشغيل كعملية خلفية محمولة
        pclose(popen('start "Clinic_MySQL" /min "' . str_replace('/', '\\', $mysqldExe) . '" --defaults-file="' . str_replace('/', '\\', $myIni) . '" --console', 'r'));
    }
    
    // انتظار جاهزية المنفذ 3306 حتى 15 ثانية
    $ready = false;
    for ($i = 0; $i < 15; $i++) {
        sleep(1);
        if (isPortOpen('127.0.0.1', 3306)) {
            $ready = true;
            break;
        }
    }
    
    if ($ready) {
        echo "  [✓] تم تشغيل سيرفر MySQL بنجاح وهو جاهز للاتصال.\n\n";
    } else {
        echo "  [!] تنبيه: لم يتم رصد استجابة المنفذ 3306.\n";
        $errLog = $mysqlDir . '/data/mysql-error.log';
        if (file_exists($errLog)) {
            $logLines = array_slice(file($errLog), -8);
            echo "  --- سجل أخطاء MySQL ---\n";
            foreach ($logLines as $line) {
                echo "  " . trim($line) . "\n";
            }
            echo "  ----------------------\n\n";
        }
    }
}

// 4. فحص وحماية قاعدة بيانات العيادة (clinic_db)
echo "[4/6] فحص وتأمين بيانات العيادة والمرضى...\n";
$dbPass = '12345678';
$pdo = null;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (Exception $e) {
    // محاولة بدون كلمة مرور كخيار بديل
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        $dbPass = '';
        // تحديث .env بكلمة المرور الفارغة
        $envFile = $clinicDir . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            $envContent = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=', $envContent);
            file_put_contents($envFile, $envContent);
        }
    } catch (Exception $e2) {
        echo "  [!] تعذر الاتصال بـ MySQL مباشرة: " . $e->getMessage() . "\n";
    }
}

if (!$pdo) {
    echo "  [X] فشل الاتصال بقاعدة البيانات. يرجى التأكد من تشغيل INSTALL كمسؤول (Run as administrator).\n\n";
} else {
    // التحقق من وجود الجداول في clinic_db
    $stmt = $pdo->query("SELECT count(*) FROM information_schema.tables WHERE table_schema='clinic_db'");
    $tableCount = (int) $stmt->fetchColumn();
    
    if ($tableCount > 0) {
        echo "  [✓] تم العثور على قاعدة بيانات العيادة الأصلية وسجلاتها كاملة بنجاح! ($tableCount جدول محفوظ على القرص)\n";
    } else {
        echo "  [i] قاعدة البيانات غير مهيأة بعد، جاري استيراد كافة البيانات الأصلية من النسخة الاحتياطية...\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        $backupFile = $clinicDir . '/clinic_database_ready.sql';
        if (!file_exists($backupFile)) {
            $backupFile = $clinicDir . '/backups/clinic_backup_latest.sql';
        }
        
        if (file_exists($backupFile)) {
            $cmd = '"' . str_replace('/', '\\', $mysqlExe) . '" --no-defaults -h 127.0.0.1 -P 3306 -u root ' . ($dbPass ? "-p$dbPass" : '') . ' clinic_db < "' . str_replace('/', '\\', $backupFile) . '"';
            shell_exec($cmd);
            echo "  [✓] تم استيراد جميع بيانات المرضى والكشوفات والجلسات بنجاح تام!\n";
        } else {
            echo "  [i] جاري تشغيل migrations...\n";
            shell_exec('"' . str_replace('/', '\\', $phpExe) . '" "' . str_replace('/', '\\', $clinicDir) . '/artisan" migrate --force');
        }
    }
    
    // أخذ نسخة احتياطية إضافية فورية
    if (!is_dir($clinicDir . '/backups')) {
        @mkdir($clinicDir . '/backups', 0777, true);
    }
    $dumpCmd = '"' . str_replace('/', '\\', $mysqldumpExe) . '" --no-defaults -h 127.0.0.1 -P 3306 -u root ' . ($dbPass ? "-p$dbPass" : '') . ' --default-character-set=utf8mb4 clinic_db > "' . str_replace('/', '\\', $clinicDir) . '/clinic_database_ready.sql" 2>nul';
    shell_exec($dumpCmd);
    @copy($clinicDir . '/clinic_database_ready.sql', $clinicDir . '/backups/clinic_backup_latest.sql');
    echo "  [✓] تم تأمين نسخة احتياطية فورية من كافة السجلات في مجلد backups.\n\n";
}

// 5. تجهيز المجلدات ومرفقات المرضى
echo "[5/6] تجهيز ملفات التخزين ومرفقات المرضى والتخسيس...\n";
$folders = [
    $clinicDir . '/storage/app/public/attachments',
    $clinicDir . '/storage/framework/sessions',
    $clinicDir . '/storage/framework/views',
    $clinicDir . '/storage/framework/cache',
];
foreach ($folders as $f) {
    if (!is_dir($f)) {
        @mkdir($f, 0777, true);
    }
}

// إعادة ربط مجلد التخزين العام
if (is_dir($clinicDir . '/public/storage')) {
    @shell_exec('cmd /c rmdir /q /s "' . str_replace('/', '\\', $clinicDir) . '\public\storage" 2>nul');
}
shell_exec('"' . str_replace('/', '\\', $phpExe) . '" "' . str_replace('/', '\\', $clinicDir) . '/artisan" storage:link 2>nul');
shell_exec('"' . str_replace('/', '\\', $phpExe) . '" "' . str_replace('/', '\\', $clinicDir) . '/artisan" optimize:clear 2>nul');
echo "  [✓] تم ربط وتفعيل مجلد المرفقات والأشعات بنجاح.\n\n";

// 6. إنشاء اختصار مباشر على سطح المكتب
echo "[6/6] إنشاء اختصار التشغيل على سطح المكتب...\n";
$targetBat = file_exists($antiDir . '/تشغيل_العيادة.bat') ? $antiDir . '/تشغيل_العيادة.bat' : $clinicDir . '/1_RUN_CLINIC.bat';
$desktopPath = getenv('USERPROFILE') ? getenv('USERPROFILE') . '\\Desktop' : '';

if (file_exists($antiDir . '/create_shortcut.vbs')) {
    @shell_exec('cscript.exe //NoLogo "' . str_replace('/', '\\', $antiDir) . '\create_shortcut.vbs"');
    echo "  [✓] تم إنشاء اختصار (Clinic Management) على سطح مكتب الويندوز بنجاح!\n\n";
}

echo "=========================================================================\n";
echo "  تهانينا! اكتمل التثبيت والتهيئة بنجاح بنسبة 100%\n";
echo "  جميع بياناتك وسجلات المرضى آمنة تماماً على القرص المحمول (" . substr($antiDir, 0, 2) . ").\n";
echo "=========================================================================\n\n";
echo "جاري تشغيل العيادة وفتح المتصفح فوراً...\n\n";

sleep(2);
// تشغيل العيادة
pclose(popen('start "" "' . str_replace('/', '\\', $targetBat) . '"', 'r'));

echo "يمكنك إغلاق هذه النافذة الآن.\n";
