<?php

namespace Database\Seeders;

use App\Models\ClinicDevice;
use App\Models\ClinicSetting;
use App\Models\Disease;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\TherapyPlan;
use App\Models\TherapySession;
use App\Models\User;
use App\Models\Visit;
use App\Models\WeightLossPlan;
use App\Models\WeightLossSession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // 1. Roles & Permissions & Staff Users
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Clinic Settings
        $settings = ClinicSetting::getSettings();

        // 3. Predefined Chronic Diseases (Customizable)
        $diseasesList = [
            'ضغط دم مرتفع (Hypertension)',
            'مرض السكري (Diabetes)',
            'خمول الغدة الدرقية (Hypothyroidism)',
            'ارتفاع الدهون الثلاثية والكوليسترول',
            'حساسية الأدوية والبنسلين',
            'خشونة بالفقرات أو المفاصل',
            'نقرس / يوريك أسيد مرتفع',
            'أمراض قلبية أو شرايين',
            'قرحة معدة أو قولون عصبي',
            'ربو وحساسية صدرية',
        ];

        $diseaseModels = [];
        foreach ($diseasesList as $name) {
            $diseaseModels[] = Disease::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        // 4. Predefined Clinic Devices (Customizable)
        $devicesList = [
            ['name' => 'ليزر علاجي بارد (Cold Laser)', 'type' => 'therapy', 'description' => 'لتسكين الألم وتجديد الأنسجة وتسريع الاستشفاء'],
            ['name' => 'موجات فوق صوتية (Ultrasound)', 'type' => 'therapy', 'description' => 'علاج عميق للأوتار والتليفات العضلية'],
            ['name' => 'تنبيه وتسكين عضلات (TENS / EMS)', 'type' => 'therapy', 'description' => 'تسكين آلام الأعصاب وتقوية العضلات'],
            ['name' => 'شد فقرات قطنية وعنقية كهربائي (Traction)', 'type' => 'therapy', 'description' => 'تخفيف الضغط عن الغضاريف والأعصاب'],
            ['name' => 'موجات تصادمية (Shockwave)', 'type' => 'therapy', 'description' => 'علاج الشوكة العظمية والتكلسات والتهاب الأوتار'],
            ['name' => 'كافيتيشن تفتيت دهون (Cavitation)', 'type' => 'weight_loss', 'description' => 'موجات فوق صوتية موضعية لتكسير الخلايا الدهنية'],
            ['name' => 'راديوفريكونسي لشد الترهلات (RF)', 'type' => 'weight_loss', 'description' => 'تحفيز الكولاجين وشد الجلد المترهل بعد نزول الوزن'],
            ['name' => 'تبريد وتجميد الدهون (Cryolipolysis)', 'type' => 'weight_loss', 'description' => 'نحت وتجميد الدهون العنيدة بدون جراحة'],
            ['name' => 'بدلة ضغط وتصريف ليمفاوي (Pressotherapy)', 'type' => 'weight_loss', 'description' => 'تنشيط الدورة الدموية وتصريف السوائل المحتبسة'],
            ['name' => 'أشعة تحت حمراء وحرارة (Infrared Lamp)', 'type' => 'both', 'description' => 'تحسين تدفق الدم واسترخاء العضلات المشدودة'],
        ];

        $deviceModels = [];
        foreach ($devicesList as $dev) {
            $deviceModels[] = ClinicDevice::firstOrCreate(['name' => $dev['name']], $dev);
        }

        // 5. Patient 1: Physical Therapy (Disc herniation)
        $p1 = Patient::firstOrCreate([
            'phone' => '01014892044'
        ], [
            'name' => 'محمود عبد الرحيم الشريف',
            'gender' => 'male',
            'age' => 42,
            'medical_history' => 'انزلاق غضروفي قطني L4-L5 ضاغط على العصب الوركي الأيمن (عرق النسا)، ضغط دم مرتفع خفيف.',
            'created_at' => (clone $today)->subDays(15),
        ]);

        // Attach diseases to Patient 1
        $p1->diseases()->sync([$diseaseModels[0]->id, $diseaseModels[5]->id]);

        $v1 = Visit::firstOrCreate([
            'patient_id' => $p1->id,
            'visit_date' => (clone $today)->subDays(15),
        ], [
            'complaint' => 'ألم حاد أسفل الظهر مع تنميل متكرر وصعوبة في الانحناء والوقوف لفترات طويلة.',
            'diagnosis' => 'انزلاق غضروفي قطني قطاعي L4-L5 مع تشنج حاد في العضلات المحيطة بالعمود الفقري.',
            'doctor_notes' => 'بدء كورس علاج طبيعي 12 جلسة (أجهزة ليزر علاجي + موجات فوق صوتية + شد فقرات + تمارين تقوية).',
            'cost' => 250.00,
        ]);

        $tp1 = TherapyPlan::firstOrCreate([
            'patient_id' => $p1->id,
            'package_name' => 'برنامج تأهيل الانزلاق الغضروفي والعمود الفقري المكثف',
        ], [
            'total_sessions' => 12,
            'cost' => 1800.00,
            'status' => 'active',
            'notes' => 'جلسات كل يومين مع تركيز على تمارين ماكنزي والاستطالات.',
            'created_at' => (clone $today)->subDays(14),
        ]);

        // Generate 12 sessions for Therapy Plan
        for ($i = 1; $i <= 12; $i++) {
            $sessionDate = (clone $today)->subDays(10 - (($i - 1) * 2));
            $isAttended = ($i <= 4);
            $attendedAt = $isAttended ? (clone $sessionDate)->setHour(17)->setMinute(30) : null;
            
            if ($i === 5) {
                $sessionDate = clone $today;
                $isAttended = false;
                $attendedAt = null;
            }

            $sess = TherapySession::firstOrCreate([
                'therapy_plan_id' => $tp1->id,
                'session_number' => $i,
            ], [
                'patient_id' => $p1->id,
                'session_date' => $sessionDate,
                'is_attended' => $isAttended,
                'attended_at' => $attendedAt,
                'notes' => $i === 1 ? 'جلسة تقييم وتخفيف الألم الأولي' : ($i === 5 ? 'تمارين استقرار العمود الفقري' : null),
            ]);

            // Attach devices used for attended sessions
            if ($isAttended) {
                $sess->devices()->sync([$deviceModels[0]->id, $deviceModels[1]->id, $deviceModels[2]->id]);
            }
        }

        // Payments for Patient 1
        Payment::firstOrCreate([
            'receipt_number' => 'REC-' . $today->format('Ym') . '-0001'
        ], [
            'patient_id' => $p1->id,
            'visit_id' => $v1->id,
            'amount' => 250.00,
            'payment_date' => (clone $today)->subDays(15),
            'payment_method' => 'cash',
            'notes' => 'سداد رسوم الكشف الطبي نقداً',
        ]);

        Payment::firstOrCreate([
            'receipt_number' => 'REC-' . $today->format('Ym') . '-0002'
        ], [
            'patient_id' => $p1->id,
            'therapy_plan_id' => $tp1->id,
            'amount' => 1000.00,
            'payment_date' => (clone $today)->subDays(14),
            'payment_method' => 'cash',
            'notes' => 'دفعة أولى من باقة العلاج الطبيعي',
        ]);

        // 6. Patient 2: Weight Loss Case with WeightLossPlan
        $p2 = Patient::firstOrCreate([
            'phone' => '01223940188'
        ], [
            'name' => 'سارة إبراهيم الدسوقي',
            'gender' => 'female',
            'age' => 31,
            'medical_history' => 'مقاومة أنسولين، زيادة وزن بعد الحمل والولادة، لا يوجد تحسس دوائي.',
            'created_at' => (clone $today)->subDays(21),
        ]);

        // Attach diseases to Patient 2 (Diabetes / Insulin resistance, Cholesterol)
        $p2->diseases()->sync([$diseaseModels[1]->id, $diseaseModels[3]->id]);

        Visit::firstOrCreate([
            'patient_id' => $p2->id,
            'visit_date' => (clone $today)->subDays(21),
        ], [
            'complaint' => 'صعوبة في نزول الوزن وثبات مستمر مع شراهة لتناول السكريات.',
            'diagnosis' => 'سمنة درجة ثانية (BMI 35.8) مع مقاومة أنسولين HOMA-IR 3.9.',
            'doctor_notes' => 'بدء حقن ساكسندا تدريجياً بجرعة 0.6 ملغ أسبوعياً ثم 1.2 ملغ مع نظام صيام متقطع وأجهزة كافيتيشن.',
            'cost' => 250.00,
        ]);

        // Create Weight Loss Package for Patient 2
        $wlp = WeightLossPlan::firstOrCreate([
            'patient_id' => $p2->id,
            'package_name' => 'باقة التخسيس ونحت القوام الماسي (8 جلسات)',
        ], [
            'total_sessions' => 8,
            'cost' => 2400.00,
            'status' => 'active',
            'notes' => 'تتضمن حقن ساكسندا + جلسات كافيتيشن وراديوفريكونسي',
            'created_at' => (clone $today)->subDays(21),
        ]);

        // Generate 8 sessions for the Weight Loss Plan
        $sessionsData = [
            ['weight' => 96.5, 'dosage' => '0.6 ملغ', 'completed' => true, 'daysAgo' => 21],
            ['weight' => 94.2, 'dosage' => '0.6 ملغ', 'completed' => true, 'daysAgo' => 14],
            ['weight' => 92.8, 'dosage' => '1.2 ملغ', 'completed' => true, 'daysAgo' => 7],
            ['weight' => 91.5, 'dosage' => '1.2 ملغ', 'completed' => false, 'daysAgo' => 0], // Today's session!
            ['weight' => null, 'dosage' => '1.8 ملغ', 'completed' => false, 'daysAgo' => -7],
            ['weight' => null, 'dosage' => '1.8 ملغ', 'completed' => false, 'daysAgo' => -14],
            ['weight' => null, 'dosage' => '2.4 ملغ', 'completed' => false, 'daysAgo' => -21],
            ['weight' => null, 'dosage' => '3.0 ملغ', 'completed' => false, 'daysAgo' => -28],
        ];

        foreach ($sessionsData as $idx => $sData) {
            $num = $idx + 1;
            $sDate = (clone $today)->subDays($sData['daysAgo']);
            $completedAt = $sData['completed'] ? (clone $sDate)->setHour(18) : null;

            $wSession = WeightLossSession::firstOrCreate([
                'weight_loss_plan_id' => $wlp->id,
                'session_number' => $num,
            ], [
                'patient_id' => $p2->id,
                'session_date' => $sDate,
                'weight' => $sData['weight'],
                'height' => 164.0,
                'dosage_amount' => $sData['dosage'],
                'injection_type' => 'ساكسندا (Saxenda)',
                'doctor_notes' => $num === 1 ? 'الجرعة الأولى مع فحص القياسات' : ($num === 4 ? 'جلسة اليوم لمتابعة التقدم' : null),
                'cost' => 0.00,
                'is_completed' => $sData['completed'],
                'completed_at' => $completedAt,
            ]);

            // If completed, attach cavitation and RF devices
            if ($sData['completed']) {
                $wSession->devices()->sync([$deviceModels[5]->id, $deviceModels[6]->id]);
            }
        }

        // Payments for Patient 2
        Payment::firstOrCreate([
            'receipt_number' => 'REC-' . $today->format('Ym') . '-0003'
        ], [
            'patient_id' => $p2->id,
            'amount' => 250.00,
            'payment_date' => (clone $today)->subDays(21),
            'payment_method' => 'vodafone_cash',
            'notes' => 'سداد الكشف الطبي عبر فودافون كاش',
        ]);

        Payment::firstOrCreate([
            'receipt_number' => 'REC-' . $today->format('Ym') . '-0004'
        ], [
            'patient_id' => $p2->id,
            'weight_loss_plan_id' => $wlp->id,
            'amount' => 1500.00,
            'payment_date' => (clone $today)->subDays(21),
            'payment_method' => 'vodafone_cash',
            'notes' => 'دفعة أولى من باقة التخسيس الماسي',
        ]);
    }
}
