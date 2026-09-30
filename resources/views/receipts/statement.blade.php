<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب مالي - {{ $patient->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
        }
        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8 text-slate-800 text-sm">
    @php
        $settings = $settings ?? \App\Models\ClinicSetting::getSettings();
        $waPhone = $patient->whatsapp_phone ?? '';
        $waStatementMsg = "كشف حساب مالي - " . ($settings->clinic_name ?? 'العيادة') . "\n"
                       . "المريض: " . $patient->name . "\n"
                       . "إجمالي التكاليف: " . number_format($patient->total_cost, 2) . " " . ($settings->currency ?? 'ج.م') . "\n"
                       . "إجمالي المسدد: " . number_format($patient->total_paid, 2) . " " . ($settings->currency ?? 'ج.م') . "\n"
                       . "المتبقي المطلوب: " . number_format($patient->remaining_balance, 2) . " " . ($settings->currency ?? 'ج.م') . "\n"
                       . "لأي استفسار يرجى التواصل معنا • نتمنى لكم دوام الصحة والعافية.";
        $waStatementUrl = $waPhone ? "https://wa.me/{$waPhone}?text=" . urlencode($waStatementMsg) : '#';
    @endphp

    <!-- Toolbar -->
    <div class="max-w-4xl mx-auto mb-6 flex flex-col sm:flex-row justify-between items-center gap-3 no-print">
        <a href="{{ route('patients.show', ['patient' => $patient->id, 'tab' => 'financial']) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition text-xs">
            &larr; العودة لملف المريض
        </a>
        <div class="flex items-center gap-2">
            @if($waPhone)
                <a href="{{ $waStatementUrl }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition flex items-center gap-1.5 text-xs">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>إرسال الملخص واتساب</span>
                </a>
            @endif
            <button onclick="window.print()" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg shadow transition flex items-center gap-2 text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>طباعة كشف الحساب (A4)</span>
            </button>
        </div>
    </div>
    <!-- Printable Statement Sheet -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-300 shadow-md p-8 rounded-xl print:border-none print:shadow-none print:p-0">
        <!-- Clinic Header -->
        <div class="flex justify-between items-start border-b-2 border-slate-800 pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">{{ $settings->clinic_name }}</h1>
                <p class="text-xs text-slate-600 font-bold mt-1">{{ $settings->doctor_title }}</p>
                <p class="text-xs text-slate-500 mt-0.5">العنوان: {{ $settings->address }}</p>
                <p class="text-xs text-slate-500">هاتف: {{ $settings->phone_1 }} @if($settings->phone_2) - {{ $settings->phone_2 }} @endif</p>
            </div>
            <div class="text-left">
                <div class="inline-block px-4 py-2 border-2 border-slate-900 font-black text-lg rounded bg-slate-50 text-slate-900">
                    كشف حساب مالي تفصيلي
                </div>
                <p class="text-xs text-slate-500 mt-2 font-mono">تاريخ التقرير: {{ date('Y-m-d H:i') }}</p>
            </div>
        </div>

        <!-- Patient Info Card -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 border border-slate-200 rounded-lg mb-6 text-xs">
            <div>
                <span class="text-slate-500 block">اسم المريض:</span>
                <span class="font-black text-sm text-slate-900">{{ $patient->name }}</span>
            </div>
            <div>
                <span class="text-slate-500 block">رقم الملف:</span>
                <span class="font-bold text-slate-900">#{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div>
                <span class="text-slate-500 block">رقم الهاتف:</span>
                <span class="font-bold text-slate-900 font-mono">{{ $patient->phone }}</span>
            </div>
            <div>
                <span class="text-slate-500 block">السن / النوع:</span>
                <span class="font-bold text-slate-900">{{ $patient->age }} سنة ({{ $patient->gender_arabic }})</span>
            </div>
            @if($patient->diseases && $patient->diseases->count() > 0)
                <div class="col-span-2 sm:col-span-4 pt-2 border-t border-slate-200/60">
                    <span class="text-slate-500 font-bold block mb-1">الأمراض المزمنة المسجلة:</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($patient->diseases as $disease)
                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 text-[11px] font-bold">{{ $disease->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Financial Summary Totals -->
        <div class="grid grid-cols-3 gap-4 mb-6 text-center">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <span class="text-xs font-bold text-blue-700 block">إجمالي المطلوب (التكاليف)</span>
                <span class="text-xl font-black text-blue-900 mt-1 block">{{ number_format($patient->total_cost, 2) }} ج.م</span>
            </div>
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                <span class="text-xs font-bold text-emerald-700 block">إجمالي المسدد (المدفوع)</span>
                <span class="text-xl font-black text-emerald-900 mt-1 block">{{ number_format($patient->total_paid, 2) }} ج.م</span>
            </div>
            <div class="p-4 {{ $patient->remaining_balance > 0 ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-slate-50 border-slate-200 text-slate-900' }} border rounded-lg">
                <span class="text-xs font-bold {{ $patient->remaining_balance > 0 ? 'text-rose-700' : 'text-slate-600' }} block">الرصيد المتبقي (مديونية)</span>
                <span class="text-xl font-black mt-1 block">{{ number_format($patient->remaining_balance, 2) }} ج.م</span>
            </div>
        </div>

        <!-- Section 1: Services & Charges Breakdown -->
        <div class="mb-6">
            <h2 class="text-sm font-bold text-slate-900 border-r-4 border-slate-900 pr-2 mb-3">أولاً: بيان الخدمات والباقات المطلوبة</h2>
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">#</th>
                            <th class="py-2.5 px-3">التاريخ</th>
                            <th class="py-2.5 px-3">البيان / الخدمة</th>
                            <th class="py-2.5 px-3">التفاصيل / عدد الجلسات</th>
                            <th class="py-2.5 px-3 text-left">التكلفة (ج.م)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php $counter = 1; @endphp

                        @foreach($patient->visits as $visit)
                            <tr>
                                <td class="py-2 px-3 font-mono">{{ $counter++ }}</td>
                                <td class="py-2 px-3 font-mono">{{ $visit->visit_date->format('Y-m-d') }}</td>
                                <td class="py-2 px-3 font-semibold">كشف طبي واستشارة</td>
                                <td class="py-2 px-3 text-slate-600">{{ Str::limit($visit->diagnosis, 40) }}</td>
                                <td class="py-2 px-3 text-left font-bold">{{ number_format($visit->cost, 2) }}</td>
                            </tr>
                        @endforeach

                        @foreach($patient->therapyPlans as $plan)
                            <tr>
                                <td class="py-2 px-3 font-mono">{{ $counter++ }}</td>
                                <td class="py-2 px-3 font-mono">{{ $plan->created_at->format('Y-m-d') }}</td>
                                <td class="py-2 px-3 font-semibold">باقة علاج طبيعي: {{ $plan->package_name }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $plan->total_sessions }} جلسات (حضر {{ $plan->completed_sessions_count }})</td>
                                <td class="py-2 px-3 text-left font-bold">{{ number_format($plan->cost, 2) }}</td>
                            </tr>
                        @endforeach

                        @foreach($patient->weightLossPlans as $wlp)
                            <tr>
                                <td class="py-2 px-3 font-mono">{{ $counter++ }}</td>
                                <td class="py-2 px-3 font-mono">{{ $wlp->created_at->format('Y-m-d') }}</td>
                                <td class="py-2 px-3 font-semibold">باقة تخسيس: {{ $wlp->package_name }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $wlp->total_sessions }} جلسات (أتم {{ $wlp->completed_sessions_count }})</td>
                                <td class="py-2 px-3 text-left font-bold">{{ number_format($wlp->cost, 2) }}</td>
                            </tr>
                        @endforeach

                        @foreach($patient->weightLossSessions as $wls)
                            @if($wls->cost > 0 && empty($wls->weight_loss_plan_id))
                                <tr>
                                    <td class="py-2 px-3 font-mono">{{ $counter++ }}</td>
                                    <td class="py-2 px-3 font-mono">{{ $wls->session_date->format('Y-m-d') }}</td>
                                    <td class="py-2 px-3 font-semibold">جلسة تخسيس منفردة</td>
                                    <td class="py-2 px-3 text-slate-600">{{ $wls->injection_type }} ({{ $wls->dosage_amount }})</td>
                                    <td class="py-2 px-3 text-left font-bold">{{ number_format($wls->cost, 2) }}</td>
                                </tr>
                            @endif
                        @endforeach

                        @if($counter === 1)
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">لا توجد خدمات مسجلة حتى الآن</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="4" class="py-2.5 px-3 text-left">إجمالي التكاليف المطلوبة:</td>
                            <td class="py-2.5 px-3 text-left text-slate-900 font-black">{{ number_format($patient->total_cost, 2) }} ج.م</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Section 2: Payments & Receipts Log -->
        <div class="mb-6">
            <h2 class="text-sm font-bold text-slate-900 border-r-4 border-emerald-600 pr-2 mb-3">ثانياً: سجل المدفوعات وسندات القبض</h2>
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-right text-xs">
                    <thead class="bg-emerald-50 text-emerald-900 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">رقم الإيصال</th>
                            <th class="py-2.5 px-3">تاريخ السداد</th>
                            <th class="py-2.5 px-3">طريقة الدفع</th>
                            <th class="py-2.5 px-3">البيان / ملاحظات</th>
                            <th class="py-2.5 px-3 text-left">المبلغ المسدد (ج.م)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($patient->payments as $payment)
                            <tr>
                                <td class="py-2 px-3 font-mono font-bold">{{ $payment->receipt_number }}</td>
                                <td class="py-2 px-3 font-mono">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td class="py-2 px-3">{{ $payment->payment_method_arabic }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $payment->notes ?: '-' }}</td>
                                <td class="py-2 px-3 text-left font-bold text-emerald-700">{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">لم يتم تسجيل أي مدفوعات للحالة حتى الآن</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-emerald-50 font-bold border-t border-emerald-200 text-emerald-950">
                        <tr>
                            <td colspan="4" class="py-2.5 px-3 text-left">إجمالي المبالغ المسددة:</td>
                            <td class="py-2.5 px-3 text-left font-black">{{ number_format($patient->total_paid, 2) }} ج.م</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Signature Area -->
        <div class="flex justify-between items-end pt-10 mt-10 border-t border-slate-200 text-xs">
            <div class="text-center">
                <p class="font-bold text-slate-700">توقيع المريض / المستلم</p>
                <div class="h-14"></div>
                <p class="text-slate-400">....................................</p>
            </div>
            <div class="text-center">
                <p class="font-bold text-slate-700">ختم وتوقيع إدارة العيادة</p>
                <div class="h-14"></div>
                <p class="font-bold text-slate-900">{{ $settings->doctor_name }}</p>
            </div>
        </div>
    </div>
</body>
</html>
