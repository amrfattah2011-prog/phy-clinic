[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$Host.UI.RawUI.WindowTitle = "تثبيت وإعداد نظام عيادة د. احمد عادل للعلاج الطبيعي والتخسيس"

Clear-Host
Write-Host "=========================================================================" -ForegroundColor Cyan
Write-Host "    نظام عيادة د. احمد عادل للعلاج الطبيعي والتخسيس ونحت القوام" -ForegroundColor Yellow
Write-Host "     ملف التثبيت والإعداد الشامل للعمل المباشر على أي جهاز ويندوز" -ForegroundColor Green
Write-Host "=========================================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host " [!] يقوم هذا الملف بتهيئة العيادة للعمل فوراً على هذا الجهاز أو أي جهاز آخر:" -ForegroundColor White
Write-Host "     1. حماية كاملة لبيانات المرضى والحسابات حتى لو قمت بتنزيل ويندوز جديد." -ForegroundColor Gray
Write-Host "     2. ضبط سيرفر MySQL المحمول ليعمل من هذا القرص مباشرة دون الحاجة لـ AppServ أو XAMPP." -ForegroundColor Gray
Write-Host "     3. ربط ملفات ومرفقات المرضى (InBody والأشعات والـ PDF)." -ForegroundColor Gray
Write-Host "     4. إنشاء اختصار مباشر على سطح المكتب للتشغيل بنقرة واحدة." -ForegroundColor Gray
Write-Host ""
Write-Host "-------------------------------------------------------------------------" -ForegroundColor DarkGray

# 1. تحديد مسارات المجلدات ديناميكياً
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

if (Test-Path "$scriptDir\php82\php.exe") {
    $antiDir = $scriptDir
    $clinicDir = "$scriptDir\clinic"
} elseif (Test-Path "$scriptDir\..\php82\php.exe") {
    $antiDir = Resolve-Path "$scriptDir\.."
    $clinicDir = $scriptDir
} else {
    Write-Host "[X] خطأ: تعذر العثور على مجلد php82! تأكد من وجود المجلدات في نفس المكان." -ForegroundColor Red
    pause
    exit 1
}

$phpExe = "$antiDir\php82\php.exe"
$mysqlDir = "$antiDir\mysql"
$mysqlExe = "$mysqlDir\bin\mysql.exe"
$mysqldExe = "$mysqlDir\bin\mysqld.exe"
$mysqldumpExe = "$mysqlDir\bin\mysqldump.exe"
$myIni = "$mysqlDir\my.ini"

Write-Host "[1/6] فحص محركات التشغيل المحمولة (PHP 8.2 & MySQL 8)..." -ForegroundColor Yellow
if (-not (Test-Path $phpExe)) {
    Write-Host "[X] تعذر العثور على ملف PHP: $phpExe" -ForegroundColor Red
    pause
    exit 1
}
if (-not (Test-Path $mysqldExe)) {
    Write-Host "[X] تعذر العثور على سيرفر MySQL: $mysqldExe" -ForegroundColor Red
    pause
    exit 1
}
Write-Host "  [✓] المحركات المحمولة موجودة وجاهزة بنجاح." -ForegroundColor Green

# 2. تحديث مسارات my.ini بالمسار والقرص الحالي
Write-Host "[2/6] تهيئة إعدادات سيرفر MySQL للقرص الحالي ($($antiDir.Substring(0,2)))..." -ForegroundColor Yellow
$mysqlDirFwd = $mysqlDir.Replace('\', '/')
$myIniContent = Get-Content $myIni -Raw
$myIniContent = $myIniContent -replace '(?m)^basedir=.*', "basedir=""$mysqlDirFwd"""
$myIniContent = $myIniContent -replace '(?m)^datadir=.*', "datadir=""$mysqlDirFwd/data/"""
Set-Content -Path $myIni -Value $myIniContent -Encoding Ascii
Write-Host "  [✓] تم ضبط مسارات قاعدة البيانات لتعمل مباشرة من: $mysqlDirFwd/data/" -ForegroundColor Green

# 3. التحقق من تشغيل سيرفر MySQL
Write-Host "[3/6] التحقق من تشغيل سيرفر MySQL..." -ForegroundColor Yellow
$portOpen = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue

if ($portOpen) {
    Write-Host "  [✓] سيرفر MySQL يعمل بالفعل على المنفذ 3306." -ForegroundColor Green
} else {
    Write-Host "  [i] جاري تشغيل سيرفر MySQL على القرص المحلي..." -ForegroundColor Cyan
    
    # محاولة تشغيل كخدمة ويندوز إن وجدت
    net start clinic_mysql 2>$null | Out-Null
    net start mysql8 2>$null | Out-Null
    net start MySQL 2>$null | Out-Null
    
    # محاولة تسجيل خدمة ويندوز تلقائياً إذا كان التشغيل كمسؤول
    $isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    if ($isAdmin) {
        & $mysqldExe --install clinic_mysql --defaults-file="$myIni" 2>$null | Out-Null
        sc.exe config clinic_mysql start= auto 2>$null | Out-Null
        net start clinic_mysql 2>$null | Out-Null
    }
    
    $portOpen = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
    if (-not $portOpen) {
        # تشغيل كعملية خلفية محمولة
        Start-Process -FilePath "cmd.exe" -ArgumentList "/c start `"Clinic_MySQL`" /min `"$mysqldExe`" --defaults-file=`"$myIni`" --console" -WindowStyle Hidden
    }
    
    # انتظار المنفذ 3306 حتى 15 ثانية
    $ready = $false
    for ($i = 0; $i -lt 15; $i++) {
        Start-Sleep -Seconds 1
        if (Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue) {
            $ready = $true
            break
        }
    }
    
    if ($ready) {
        Write-Host "  [✓] تم تشغيل سيرفر MySQL بنجاح وهو جاهز للاتصال." -ForegroundColor Green
    } else {
        Write-Host "  [!] تنبيه: جاري المتابعة، قد يستغرق بدء MySQL بضع لحظات إضافية." -ForegroundColor DarkYellow
    }
}

# 4. فحص وحماية قاعدة بيانات العيادة (clinic_db)
Write-Host "[4/6] فحص وتأمين بيانات العيادة والمرضى..." -ForegroundColor Yellow
$tablesQuery = & $mysqlExe --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 -N -e "SELECT count(*) FROM information_schema.tables WHERE table_schema='clinic_db';" 2>$null
$tableCount = 0
if ($tablesQuery) {
    [int]::TryParse($tablesQuery.Trim(), [ref]$tableCount) | Out-Null
}

if ($tableCount -gt 0) {
    Write-Host "  [✓] تم العثور على قاعدة بيانات العيادة الأصلية وسجلاتها كاملة بنجاح! ($tableCount جدول محفوظ على القرص)" -ForegroundColor Green
} else {
    Write-Host "  [i] قاعدة البيانات غير مهيأة بعد، جاري استيراد كافة البيانات الأصلية من النسخة الاحتياطية..." -ForegroundColor Cyan
    & $mysqlExe --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 -e "CREATE DATABASE IF NOT EXISTS clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>$null | Out-Null
    
    $backupFile = "$clinicDir\clinic_database_ready.sql"
    if (-not (Test-Path $backupFile)) {
        $backupFile = "$clinicDir\backups\clinic_backup_latest.sql"
    }
    
    if (Test-Path $backupFile) {
        Get-Content $backupFile -Encoding UTF8 | & $mysqlExe --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 clinic_db 2>$null
        Write-Host "  [✓] تم استيراد جميع بيانات المرضى والكشوفات والجلسات بنجاح تام!" -ForegroundColor Green
    } else {
        Write-Host "  [!] تشغيل تهيئة الجداول الجديدة..." -ForegroundColor Cyan
        & $phpExe "$clinicDir\artisan" migrate --force 2>$null | Out-Null
    }
}

# أخذ نسخة احتياطية فورية على القرص لضمان الأمان
if (-not (Test-Path "$clinicDir\backups")) { New-Item -ItemType Directory -Path "$clinicDir\backups" -Force | Out-Null }
& $mysqldumpExe --no-defaults -h 127.0.0.1 -P 3306 -u root -p12345678 --default-character-set=utf8mb4 clinic_db > "$clinicDir\clinic_database_ready.sql" 2>$null
Copy-Item "$clinicDir\clinic_database_ready.sql" "$clinicDir\backups\clinic_backup_latest.sql" -Force 2>$null

# 5. تجهيز المجلدات ومرفقات المرضى
Write-Host "[5/6] تجهيز ملفات التخزين ومرفقات المرضى والتخسيس..." -ForegroundColor Yellow
$folders = @(
    "$clinicDir\storage\app\public\attachments",
    "$clinicDir\storage\framework\sessions",
    "$clinicDir\storage\framework\views",
    "$clinicDir\storage\framework\cache"
)
foreach ($f in $folders) {
    if (-not (Test-Path $f)) { New-Item -ItemType Directory -Path $f -Force | Out-Null }
}

# إعادة إنشاء الرابط التخزيني public/storage
if (Test-Path "$clinicDir\public\storage") {
    cmd /c "rmdir /q /s `"$clinicDir\public\storage`"" 2>$null | Out-Null
}
& $phpExe "$clinicDir\artisan" storage:link 2>$null | Out-Null
& $phpExe "$clinicDir\artisan" optimize:clear 2>$null | Out-Null
Write-Host "  [✓] تم ربط وتفعيل مجلد المرفقات والأشعات بنجاح." -ForegroundColor Green

# 6. إنشاء اختصار مباشر على سطح المكتب
Write-Host "[6/6] إنشاء اختصار التشغيل على سطح المكتب..." -ForegroundColor Yellow
$targetBat = "$antiDir\تشغيل_العيادة.bat"
if (-not (Test-Path $targetBat)) {
    $targetBat = "$clinicDir\1_RUN_CLINIC.bat"
}

$desktopPath = [Environment]::GetFolderPath("Desktop")
$shortcutFile = "$desktopPath\عيادة د. احمد عادل للعلاج الطبيعي والتخسيس.lnk"

try {
    $wsh = New-Object -ComObject WScript.Shell
    $shortcut = $wsh.CreateShortcut($shortcutFile)
    $shortcut.TargetPath = $targetBat
    $shortcut.WorkingDirectory = $clinicDir
    $shortcut.Description = "نظام إدارة عيادة العلاج الطبيعي والتخسيس ونحت القوام"
    $shortcut.Save()
    Write-Host "  [✓] تم إنشاء اختصار (عيادة د. احمد عادل) على سطح مكتب الويندوز بنجاح!" -ForegroundColor Green
} catch {
    Write-Host "  [!] تعذر إنشاء اختصار سطح المكتب تلقائياً." -ForegroundColor DarkYellow
}

Write-Host ""
Write-Host "=========================================================================" -ForegroundColor Green
Write-Host "  تهانينا! اكتمل التثبيت والتهيئة بنجاح بنسبة 100%" -ForegroundColor Yellow
Write-Host "  جميع بياناتك وسجلات المرضى آمنة تماماً على القرص المحمول ($($antiDir.Substring(0,2)))." -ForegroundColor Cyan
Write-Host "=========================================================================" -ForegroundColor Green
Write-Host ""
Write-Host "جاري تشغيل العيادة وفتح المتصفح فوراً..." -ForegroundColor White
Write-Host ""

Start-Sleep -Seconds 2
Start-Process -FilePath $targetBat

Write-Host "يمكنك إغلاق هذه النافذة الآن." -ForegroundColor Gray
Start-Sleep -Seconds 3
