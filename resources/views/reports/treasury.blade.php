@extends('layouts.app')

@section('title', 'كشف حساب الخزينة والسيولة النقدية - ' . config('app.name'))

@push('styles')
<style>
@media print {
    header, nav, form, .no-print, footer, .quick-filters {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
        padding: 0 !important;
    }
    .print-only {
        display: block !important;
    }
    .print-border {
        border: 1px solid #cbd5e1 !important;
    }
}
.print-only {
    display: none;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header (Screen Only) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span>كشف حساب الخزينة وصافي الإيرادات والمصروفات</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">سجل حركة النقدية اليومية والدورية، الرصيد الافتتاحي، سندات القبض، وسندات الصرف مع الرصيد التراكمي.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>طباعة كشف الحساب الرسمي</span>
            </button>
        </div>
    </div>

    <!-- Official Header (Print Only) -->
    @php
        $clinicSettings = \App\Models\ClinicSetting::getSettings();
    @endphp
    <div class="print-only mb-6 text-center border-b-2 border-slate-900 pb-4">
        <div class="flex items-center justify-between">
            <div class="text-right">
                <h2 class="text-xl font-black text-slate-900">{{ $clinicSettings->clinic_name }}</h2>
                <div class="text-xs font-bold text-slate-700">{{ $clinicSettings->doctor_title }}</div>
                <div class="text-[11px] text-slate-500">{{ $clinicSettings->address }} | هاتف: {{ $clinicSettings->phone }}</div>
            </div>
            <div class="text-left">
                <h3 class="text-lg font-black text-emerald-800">كشف حساب الخزينة الرسمي</h3>
                <div class="text-xs font-bold text-slate-600 mt-1">
                    الفترة: من {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('Y/m/d') : 'بداية العمل' }} إلى {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('Y/m/d') : 'الآن' }}
                </div>
                <div class="text-[10px] text-slate-400">تاريخ الطباعة: {{ now()->format('Y/m/d - h:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Date Filters (Screen Only) -->
    <div class="flex flex-wrap items-center gap-2 quick-filters no-print">
        <span class="text-xs font-bold text-slate-500 ml-1">نطاق سريع:</span>
        <a href="{{ route('reports.treasury', ['quick_filter' => 'today']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('quick_filter') == 'today' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            اليوم
        </a>
        <a href="{{ route('reports.treasury', ['quick_filter' => 'this_week']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('quick_filter') == 'this_week' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            هذا الأسبوع
        </a>
        <a href="{{ route('reports.treasury', ['quick_filter' => 'this_month']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ (!request('quick_filter') && !request('from_date')) || request('quick_filter') == 'this_month' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            هذا الشهر
        </a>
        <a href="{{ route('reports.treasury', ['quick_filter' => 'last_month']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('quick_filter') == 'last_month' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            الشهر الماضي
        </a>
        <a href="{{ route('reports.treasury', ['quick_filter' => 'all']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('quick_filter') == 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            كامل المدة
        </a>
    </div>

    <!-- Filter Bar (Screen Only) -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs no-print">
        <form action="{{ route('reports.treasury') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">وسيلة الدفع</label>
                <select name="payment_method" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">جميع الوسائل (كاش وفيزا ومحافظ)</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقداً فقط (خزينة المركز)</option>
                    <option value="visa" {{ request('payment_method') == 'visa' ? 'selected' : '' }}>فيزا / تحويل بنكي فقط</option>
                    <option value="vodafone_cash" {{ request('payment_method') == 'vodafone_cash' ? 'selected' : '' }}>فودافون كاش ومحافظ فقط</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">الموظف المسؤول</label>
                <select name="user_id" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">جميع الموظفين</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2 rounded-xl transition">
                    تصفية الحساب
                </button>
                <a href="{{ route('reports.treasury') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إعادة ضبط
                </a>
            </div>
        </form>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- الرصيد الافتتاحي -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 block">الرصيد الافتتاحي</span>
            <div class="mt-2 text-2xl font-black text-slate-700">
                {{ number_format($openingBalance, 2) }}
            </div>
            <span class="text-[11px] text-slate-400">الرصيد ما قبل بداية الفترة</span>
        </div>

        <!-- إجمالي المقبوضات (وارد) -->
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700">إجمالي المقبوضات (وارد +)</span>
                <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-700">
                +{{ number_format($totalInflow, 2) }}
            </div>
            <span class="text-[11px] text-emerald-600">إيرادات المرضى والكشوفات</span>
        </div>

        <!-- إجمالي المدفوعات (صادر) -->
        <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-700">إجمالي المدفوعات (صادر -)</span>
                <span class="p-1.5 rounded-lg bg-rose-100 text-rose-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-rose-700">
                -{{ number_format($totalOutflow, 2) }}
            </div>
            <span class="text-[11px] text-rose-600">مصروفات وتشغيل ونفقات</span>
        </div>

        <!-- صافي حركة الفترة -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 block">صافي حركة الفترة</span>
            <div class="mt-2 text-2xl font-black {{ $netPeriodCash >= 0 ? 'text-teal-700' : 'text-rose-600' }}">
                {{ ($netPeriodCash >= 0 ? '+' : '') . number_format($netPeriodCash, 2) }}
            </div>
            <span class="text-[11px] text-slate-400">فارق المقبوضات عن المصروفات</span>
        </div>

        <!-- الرصيد النهائي للخزينة -->
        <div class="bg-slate-900 text-white p-4 rounded-2xl shadow-md border border-slate-800">
            <span class="text-xs font-bold text-slate-300 block">الرصيد الختامي للخزينة</span>
            <div class="mt-2 text-2xl font-black text-emerald-400">
                {{ number_format($closingBalance, 2) }} <span class="text-xs text-slate-400 font-normal">ج.م</span>
            </div>
            <span class="text-[11px] text-slate-400">السيولة النقدية المتبقية</span>
        </div>
    </div>

    <!-- Method Breakdown Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 no-print">
        @foreach($methodsBreakdown as $key => $data)
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800">{{ $data['name'] }}</span>
                    <span class="text-xs font-black {{ $data['net'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                        الصافي: {{ number_format($data['net'], 2) }} ج.م
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2 text-[11px]">
                    <div class="bg-emerald-50/50 p-2 rounded-xl text-emerald-800">
                        <div class="font-bold">مقبوضات:</div>
                        <div class="font-black text-xs">+{{ number_format($data['inflow'], 2) }}</div>
                    </div>
                    <div class="bg-rose-50/50 p-2 rounded-xl text-rose-800">
                        <div class="font-bold">مصروفات:</div>
                        <div class="font-black text-xs">-{{ number_format($data['outflow'], 2) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Chronological Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden print-border">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>دفتر حركة الخزينة الزمني (سندات القبض وسندات الصرف)</span>
            </h2>
            <span class="text-xs font-bold text-slate-500">{{ $ledger->count() }} حركة مسجلة</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                        <th class="py-3 px-3 w-10 text-center">#</th>
                        <th class="py-3 px-3 whitespace-nowrap">التاريخ والوقت</th>
                        <th class="py-3 px-3 whitespace-nowrap">النوع</th>
                        <th class="py-3 px-3 whitespace-nowrap">رقم السند</th>
                        <th class="py-3 px-3">البيان والتفاصيل</th>
                        <th class="py-3 px-3 whitespace-nowrap">طريقة الدفع</th>
                        <th class="py-3 px-3 whitespace-nowrap">المسؤول</th>
                        <th class="py-3 px-3 text-left whitespace-nowrap">وارد (+)</th>
                        <th class="py-3 px-3 text-left whitespace-nowrap">صادر (-)</th>
                        <th class="py-3 px-3 text-left whitespace-nowrap bg-slate-100/70">الرصيد التراكمي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- سطر الرصيد الافتتاحي -->
                    @if($fromDate)
                        <tr class="bg-amber-50/40 font-bold text-amber-950">
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 whitespace-nowrap">{{ $fromDate }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800">رصيد سابق</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono">-</td>
                            <td class="py-2.5 px-3">الرصيد الافتتاحي قبل تاريخ {{ $fromDate }}</td>
                            <td class="py-2.5 px-3">-</td>
                            <td class="py-2.5 px-3">-</td>
                            <td class="py-2.5 px-3 text-left font-mono">-</td>
                            <td class="py-2.5 px-3 text-left font-mono">-</td>
                            <td class="py-2.5 px-3 text-left font-mono font-black bg-amber-100/50">
                                {{ number_format($openingBalance, 2) }} ج.م
                            </td>
                        </tr>
                    @endif

                    @forelse($ledger as $tx)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $tx->date_formatted }}</div>
                                @if($tx->created_at)
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $tx->created_at->format('h:i A') }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($tx->type === 'revenue')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                        <span>إيراد وارد</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                        <span>مصروف صادر</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-700 whitespace-nowrap">
                                {{ $tx->reference_no }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-semibold text-slate-900">{{ $tx->title }}</div>
                                @if($tx->notes)
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ Str::limit($tx->notes, 50) }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-600 whitespace-nowrap">
                                {{ $tx->payment_method_label }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ $tx->staff_name }}</span>
                            </td>
                            <td class="py-3 px-3 text-left font-mono font-black text-emerald-600 whitespace-nowrap">
                                {{ $tx->inflow > 0 ? '+' . number_format($tx->inflow, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-left font-mono font-black text-rose-600 whitespace-nowrap">
                                {{ $tx->outflow > 0 ? '-' . number_format($tx->outflow, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-left font-mono font-black bg-slate-100/70 whitespace-nowrap {{ $tx->running_balance >= 0 ? 'text-slate-900' : 'text-rose-700' }}">
                                {{ number_format($tx->running_balance, 2) }} ج.م
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="font-bold text-slate-600">لا توجد حركات مالية في هذا النطاق الزمني</p>
                                <p class="text-xs text-slate-400 mt-1">جرّب تغيير التاريخ أو اختيار "كامل المدة".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-300">
                        <td colspan="7" class="py-3 px-4 text-right">إجمالي حركات الفترة</td>
                        <td class="py-3 px-3 text-left font-mono text-emerald-700">+{{ number_format($totalInflow, 2) }}</td>
                        <td class="py-3 px-3 text-left font-mono text-rose-700">-{{ number_format($totalOutflow, 2) }}</td>
                        <td class="py-3 px-3 text-left font-mono bg-slate-200 text-slate-900">{{ number_format($closingBalance, 2) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Signatures for Print (Print Only) -->
    <div class="print-only mt-12 pt-6 border-t border-slate-300">
        <div class="grid grid-cols-2 gap-12 text-center text-xs font-bold text-slate-800">
            <div>
                <p class="mb-8">المحاسب / أمين الخزينة المسؤول</p>
                <p>التوقيع: .......................................</p>
            </div>
            <div>
                <p class="mb-8">المدير العام / المسؤول الطبي</p>
                <p>الاعتماد: .......................................</p>
            </div>
        </div>
    </div>
</div>
@endsection