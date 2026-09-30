<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الطبي والمتابعة | {{ $patient->name }} - {{ $settings->clinic_name }}</title>

    <!-- WhatsApp & Social Media Open Graph Link Preview Meta Tags -->
    <meta property="og:title" content="الملف الطبي والمتابعة: {{ $patient->name }} | {{ $settings->clinic_name }}">
    <meta property="og:description" content="مرحباً أ/ {{ $patient->name }}، تابع جدول جلساتك العلاجية ومواعيدك ومتبقي حسابك المالي مباشرة عبر بوابتك الرقمية في {{ $settings->clinic_name }}.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $patient->portal_url }}">
    <meta property="og:site_name" content="{{ $settings->clinic_name }}">
    <meta name="theme-color" content="#059669">

    <!-- Tailwind CSS CDN & Cairo Google Font -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Cairo', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen pb-16">

    <!-- Top Clinic Branding Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-2xl mx-auto px-4 py-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-lg shadow-sm">
                    ⚕
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-900 leading-tight">{{ $settings->clinic_name }}</h1>
                    <p class="text-[11px] text-slate-500 font-bold truncate max-w-[200px] sm:max-w-xs">{{ $settings->doctor_title }}</p>
                </div>
            </div>

            <!-- Direct Clinic Contacts -->
            <div class="flex items-center gap-1.5">
                @if($settings->phone_1)
                    @php
                        $cleanClinicPhone = preg_replace('/[^0-9]/', '', $settings->phone_1);
                        if (str_starts_with($cleanClinicPhone, '01') && strlen($cleanClinicPhone) === 11) {
                            $waClinicPhone = '2' . $cleanClinicPhone;
                        } else {
                            $waClinicPhone = $cleanClinicPhone;
                        }
                    @endphp
                    <a href="https://wa.me/{{ $waClinicPhone }}" target="_blank" rel="noopener noreferrer"
                       class="w-8 h-8 rounded-full bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white transition flex items-center justify-center shadow-xs"
                       title="تواصل مع العيادة واتساب">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    </a>
                    <a href="tel:{{ $settings->phone_1 }}" 
                       class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center justify-center shadow-xs"
                       title="اتصال هاتفي بالعيادة">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-5 space-y-4">

        <!-- Patient Profile Hero Card -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex items-start justify-between gap-3 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-13 h-13 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center font-black text-xl flex-shrink-0">
                        {{ mb_substr($patient->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-lg font-black text-white">{{ $patient->name }}</h2>
                            <span class="px-2 py-0.5 rounded-full bg-white/10 text-emerald-300 text-[10px] font-mono font-bold">
                                #{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5">
                            {{ $patient->gender_arabic }} &bull; {{ $patient->age }} سنة &bull; ملف نشط
                        </p>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold">
                    بوابة المريض الرقمية
                </span>
            </div>

            <!-- Financial Mini Dashboard -->
            <div class="mt-5 pt-4 border-t border-slate-700/80 grid grid-cols-3 gap-2 text-center relative z-10">
                <div class="bg-white/5 rounded-2xl p-2.5">
                    <span class="text-[10px] text-slate-400 block font-bold">إجمالي الحساب</span>
                    <span class="text-sm sm:text-base font-black text-white mt-0.5 block font-mono">{{ number_format($patient->total_cost, 2) }}</span>
                    <span class="text-[9px] text-slate-400">{{ $settings->currency ?? 'ج.م' }}</span>
                </div>
                <div class="bg-emerald-500/10 rounded-2xl p-2.5 border border-emerald-500/20">
                    <span class="text-[10px] text-emerald-300 block font-bold">المسدد (المدفوع)</span>
                    <span class="text-sm sm:text-base font-black text-emerald-400 mt-0.5 block font-mono">{{ number_format($patient->total_paid, 2) }}</span>
                    <span class="text-[9px] text-emerald-300">{{ $settings->currency ?? 'ج.م' }}</span>
                </div>
                <div class="bg-white/5 rounded-2xl p-2.5 {{ $patient->remaining_balance > 0 ? 'border border-rose-500/30 bg-rose-500/10' : '' }}">
                    <span class="text-[10px] {{ $patient->remaining_balance > 0 ? 'text-rose-300' : 'text-slate-400' }} block font-bold">المتبقي</span>
                    <span class="text-sm sm:text-base font-black {{ $patient->remaining_balance > 0 ? 'text-rose-400' : 'text-white' }} mt-0.5 block font-mono">
                        {{ number_format($patient->remaining_balance, 2) }}
                    </span>
                    <span class="text-[9px] {{ $patient->remaining_balance > 0 ? 'text-rose-300' : 'text-slate-400' }}">{{ $settings->currency ?? 'ج.م' }}</span>
                </div>
            </div>
        </div>

        <!-- Section: Physical Therapy Packages & Sessions Progress -->
        @if($patient->therapyPlans->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>باقات العلاج الطبيعي والتأهيل</span>
                    </h3>
                    <span class="text-[11px] text-slate-500 font-bold font-mono">{{ $patient->therapyPlans->count() }} باقة</span>
                </div>

                @foreach($patient->therapyPlans as $plan)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="text-sm font-black text-slate-900">{{ $plan->package_name }}</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">تاريخ البدء: {{ $plan->created_at->format('Y-m-d') }}</p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $plan->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $plan->status_arabic }}
                            </span>
                        </div>

                        <!-- Progress bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-600">تم إنجاز {{ $plan->completed_sessions_count }} من {{ $plan->total_sessions }} جلسة</span>
                                <span class="text-emerald-600 font-mono">{{ $plan->progress_percentage }}%</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200/60">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" style="width: {{ $plan->progress_percentage }}%;"></div>
                            </div>
                            <p class="text-[10px] text-slate-400 text-left">متبقي {{ $plan->remaining_sessions_count }} جلسات</p>
                        </div>

                        <!-- Sessions List -->
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-[11px] font-bold text-slate-700 block mb-2">سجل الجلسات والحضور:</span>
                            <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                                @foreach($plan->sessions as $session)
                                    <div class="flex items-center justify-between p-2 rounded-xl text-xs {{ $session->is_attended ? 'bg-emerald-50/70 border border-emerald-200/60' : 'bg-slate-50 border border-slate-200/60' }}">
                                        <div class="flex items-center gap-2">
                                            @if($session->is_attended)
                                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0">✓</span>
                                            @else
                                                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px] font-bold flex-shrink-0">○</span>
                                            @endif
                                            <div>
                                                <span class="font-bold text-slate-800">جلسة #{{ $session->session_number }}</span>
                                                @if($session->devices->isNotEmpty())
                                                    <div class="flex items-center gap-1 flex-wrap mt-0.5">
                                                        @foreach($session->devices as $dev)
                                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-100 text-blue-800">{{ $dev->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-left">
                                            @if($session->is_attended)
                                                <span class="text-[10px] font-bold text-emerald-700 block">تم الحضور</span>
                                                <span class="text-[9px] text-slate-400 font-mono">{{ $session->attended_at ? $session->attended_at->format('Y-m-d') : '' }}</span>
                                            @else
                                                <span class="text-[10px] text-slate-400 block font-medium">في الانتظار</span>
                                                @if($session->session_date)
                                                    <span class="text-[9px] text-slate-500 font-mono">{{ $session->session_date->format('Y-m-d') }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Section: Weight Loss Packages & Body Sculpting -->
        @if($patient->weightLossPlans->isNotEmpty() || $patient->weightLossSessions->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                        <span>باقات التخسيس ونحت القوام والمتابعة</span>
                    </h3>
                    <span class="text-[11px] text-slate-500 font-bold font-mono">{{ $patient->weightLossPlans->count() }} باقة</span>
                </div>

                @foreach($patient->weightLossPlans as $wlp)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="text-sm font-black text-slate-900">{{ $wlp->package_name }}</h4>
                                @if($wlp->default_injection)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-bold border border-purple-200">
                                        الحقن: {{ $wlp->default_injection }}
                                    </span>
                                @endif
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $wlp->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $wlp->status_arabic }}
                            </span>
                        </div>

                        <!-- Progress bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-600">أُنجز {{ $wlp->completed_sessions_count }} من {{ $wlp->total_sessions }} جلسات</span>
                                <span class="text-purple-700 font-mono">{{ $wlp->progress_percentage }}%</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200/60">
                                <div class="h-full bg-purple-600 rounded-full transition-all duration-300" style="width: {{ $wlp->progress_percentage }}%;"></div>
                            </div>
                            <p class="text-[10px] text-slate-400 text-left">متبقي {{ $wlp->remaining_sessions_count }} جلسات</p>
                        </div>

                        <!-- Weight Loss Sessions Log -->
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-[11px] font-bold text-slate-700 block mb-2">تطور الوزن والقياسات:</span>
                            <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                                @foreach($wlp->sessions as $session)
                                    <div class="flex items-center justify-between p-2 rounded-xl text-xs {{ $session->is_completed ? 'bg-purple-50/60 border border-purple-200/60' : 'bg-slate-50 border border-slate-200/60' }}">
                                        <div class="flex items-center gap-2">
                                            @if($session->is_completed)
                                                <span class="w-5 h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0">✓</span>
                                            @else
                                                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px] font-bold flex-shrink-0">○</span>
                                            @endif
                                            <div>
                                                <span class="font-bold text-slate-800">جلسة #{{ $session->session_number }}</span>
                                                <span class="text-[10px] text-slate-500 font-mono block">{{ $session->session_date ? $session->session_date->format('Y-m-d') : '-' }}</span>
                                            </div>
                                        </div>

                                        <div class="text-left flex flex-col items-end">
                                            @if($session->weight)
                                                <span class="font-mono font-black text-slate-900 text-xs">{{ $session->weight }} كجم</span>
                                                @if($session->bmi)
                                                    <span class="text-[9px] text-purple-700 font-bold block">BMI: {{ $session->bmi }}</span>
                                                @endif
                                            @else
                                                <span class="text-[10px] text-slate-400">لم يسجل قياس</span>
                                            @endif

                                            @if($session->has_attachment)
                                                <a href="{{ $session->attachment_url }}" target="_blank" 
                                                   class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-purple-100 hover:bg-purple-200 text-purple-900 text-[10px] font-bold transition mt-1" 
                                                   title="معاينة المرفق (InBody أو مستند)">
                                                    @if($session->is_attachment_image)
                                                        <span>🖼️ InBody / صورة</span>
                                                    @else
                                                        <span>📄 مستند PDF</span>
                                                    @endif
                                                    <svg class="w-2.5 h-2.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Section: Medical Consultations History -->
        @if($patient->visits->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                        <span>سجل الكشوفات والتشخيص الطبي</span>
                    </h3>
                    <span class="text-[11px] text-slate-500 font-bold font-mono">{{ $patient->visits->count() }} كشف</span>
                </div>

                @foreach($patient->visits as $visit)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-3.5 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-800">كشف طبي بتاريخ:</span>
                            <span class="font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">{{ $visit->visit_date->format('Y-m-d') }}</span>
                        </div>
                        @if($visit->diagnosis)
                            <div class="text-xs text-slate-700 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <strong class="text-slate-900">التشخيص:</strong> {{ $visit->diagnosis }}
                            </div>
                        @endif
                        @if($visit->doctor_notes)
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                <strong class="text-slate-700">توجيهات الطبيب:</strong> {{ $visit->doctor_notes }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Section: Recent Receipts / Payments -->
        @if($patient->payments->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>سجل المدفوعات وسندات القبض</span>
                    </h3>
                    <span class="text-[11px] text-slate-500 font-bold font-mono">{{ $patient->payments->count() }} إيصال</span>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
                    @foreach($patient->payments as $payment)
                        <div class="p-3 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-mono font-bold text-slate-800">{{ $payment->receipt_number }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $payment->payment_date->format('Y-m-d') }} &bull; {{ $payment->payment_method_name }}</span>
                            </div>
                            <div class="text-left font-mono font-black text-emerald-700">
                                +{{ number_format($payment->amount, 2) }} {{ $settings->currency ?? 'ج.م' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Clinic Footer & Help -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center space-y-2 mt-6">
            <h4 class="text-xs font-bold text-slate-800">هل لديك استفسار أو ترغب في تعديل موعدك؟</h4>
            <p class="text-[11px] text-slate-500">فريق العيادة دائماً في خدمتكم للإجابة على كافة استفساراتكم.</p>
            <div class="pt-2 flex items-center justify-center gap-2">
                @if($settings->phone_1)
                    <a href="tel:{{ $settings->phone_1 }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>📞 اتصال: {{ $settings->phone_1 }}</span>
                    </a>
                @endif
                @if($settings->phone_1)
                    <a href="https://wa.me/{{ $waClinicPhone ?? '' }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                        <span>واتساب العيادة</span>
                    </a>
                @endif
            </div>
            @if($settings->address)
                <p class="text-[10px] text-slate-400 pt-2 border-t border-slate-100">العنوان: {{ $settings->address }}</p>
            @endif
        </div>

    </main>

</body>
</html>
