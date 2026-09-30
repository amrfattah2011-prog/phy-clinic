<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class ResetClinicData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:reset {--force : تجاوز رسالة التأكيد المباشرة}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تصفير كافة سجلات المرضى والزيارات والجلسات والحسابات والمصروفات مع الحفاظ على الأطباء والإعدادات';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        if (!$force) {
            $confirmed = $this->confirm('هل أنت متأكد من رغبتك في تصفير جميع المرضى والحسابات؟ لا يمكن التراجع عن هذه الخطوة!', false);
            if (!$confirmed) {
                $this->warn('تم إلغاء عملية التصفير.');
                return 0;
            }
        }

        $this->info('جاري تصفير البيانات وإعادة ضبط الجداول...');

        // قائمة الجداول التي سيتم تصفيرها بالترتيب المناسب
        $tablesToTruncate = [
            'activity_logs',
            'expenses',
            'payments',
            'session_devices',
            'weight_loss_sessions',
            'weight_loss_plans',
            'therapy_sessions',
            'therapy_plans',
            'visits',
            'patient_disease',
            'patient_attachments',
            'patients',
        ];

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("  [✓] تم تفريغ جدول: {$table}");
                }
            }

            Schema::enableForeignKeyConstraints();

            // حذف الملفات والمرفقات المرفوعة من السيرفر (صور InBody وملفات الـ PDF)
            $attachmentsDir = storage_path('app/public/attachments');
            if (File::isDirectory($attachmentsDir)) {
                $files = File::files($attachmentsDir);
                foreach ($files as $file) {
                    if ($file->getFilename() !== '.gitignore') {
                        File::delete($file->getPathname());
                    }
                }
                $this->line('  [✓] تم تنظيف مجلد مرفقات وأشعات المرضى القديمة.');
            }

            // تنظيف الكاش والجلسات القديمة
            $this->callSilent('cache:clear');
            $this->callSilent('view:clear');

            $this->newLine();
            $this->info('===========================================================');
            $this->info('  تم بنجاح تصفير كافة الحسابات والمرضى وسجلات العيادة بنسبة 100%');
            $this->info('  تم الحفاظ على بيانات الأطباء والموظفين وإعدادات العيادة.');
            $this->info('===========================================================');

            return 0;
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            $this->error('حدث خطأ أثناء التصفير: ' . $e->getMessage());
            return 1;
        }
    }
}
