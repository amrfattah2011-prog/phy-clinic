@extends('layouts.app')

@section('title', 'تقرير باقات التخسيس ونسب التحصيل وتكلفة الجلسات - ' . config('app.name'))

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
    table {
        font-size: 11px !important;
    }
    th, td {
        padding: 6px 4px !important;
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
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span>تقرير باقات التخسيس ومتابعة التحصيل وتكلفة الجلسات</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                حصر شامل لكل باقة تخسيس تم إنشاؤها، مع حساب تكلفة الجلسة الواحدة، ونسب السداد، وعدد الجلسات المحصلة والمتبقية والإجماليات.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>طباعة التقرير الرسمي A4</span>
            </button>
        </div>
    </div>

    <!-- Official Header (Print Only) -->
    <div class="print-only mb-6 text-center border-b-2 border-slate-900 pb-4">
        <div class="flex items-center justify-between">
            <div class="text-right">
                <h2 class="text-xl font-black text-slate-900">{{ $settings->clinic_name }}</h2>
                <p class="text-xs text-slate-600 font-bold mt-0.5">{{ $settings->doctor_title }} - {{ $settings->doctor_name }}</p>
                <p class="text-xs text-slate-500">{{ $settings->address }} | هاتف: {{ $settings->phone_1 }}</p>
            </div>
            <div class="text-left">
                <span class="inline-block px-3 py-1 bg-slate-100 text-slate-800 font-bold text-xs rounded border border-slate-300">
                    تقرير مالي وإحصائي للباقات
                </span>
                <p class="text-xs text-slate-500 mt-1">تاريخ الطباعة: {{ now()->translatedFormat('d/m/Y - h:i A') }}</p>
            </div>
        </div>
        <div class="mt-3 py-2 bg-slate-100 rounded text-center">
            <h3 class="text-base font-black text-slate-900">تقرير باقات التخسيس ونحت القوام ونسب التحصيل</h3>
            @if($fromDate || $toDate)
                <p class="text-xs text-slate-600 font-bold mt-0.5">
                    الفترة: {{ $fromDate ? 'من ' . $fromDate : 'حتى تاريخ ' }} {{ $toDate ? 'إلى ' . $toDate : '' }}
                </p>
            @else
                <p class="text-xs text-slate-600 font-bold mt-0.5">كافة الفترات الزمنية المسجلة</p>
            @endif
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Packages -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">إجمالي باقات التخسيس</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalPlansCount) }} <span class="text-xs font-bold text-slate-500">باقة</span></div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span class="text-emerald-700 font-bold">{{ $activePlansCount }} سارية</span>
                    <span>•</span>
                    <span class="text-blue-700 font-bold">{{ $completedPlansCount }} مكتملة</span>
                    @if($cancelledPlansCount > 0)
                        <span>•</span>
                        <span class="text-rose-600 font-bold">{{ $cancelledPlansCount }} ملغاة</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 2: Total Cost of Packages -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">إجمالي قيمة الباقات المطلوبة</span>
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalPlansCost, 2) }} <span class="text-xs font-bold text-slate-500">ج.م</span></div>
                <div class="text-[11px] text-slate-500 mt-1">
                    متوسط سعر الباقة: <span class="font-bold text-slate-700">{{ $totalPlansCount > 0 ? number_format($totalPlansCost / $totalPlansCount, 2) : '0.00' }} ج.م</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Collected & Percentage -->
        <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-xs relative overflow-hidden bg-gradient-to-br from-white to-emerald-50/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800">إجمالي المبالغ المحصلة</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-700">{{ number_format($totalCollectedAmount, 2) }} <span class="text-xs font-bold text-slate-500">ج.م</span></div>
                <div class="mt-2">
                    <div class="flex items-center justify-between text-xs font-bold mb-1">
                        <span class="text-slate-600">نسبة التحصيل:</span>
                        <span class="text-emerald-700 font-extrabold">{{ $averageCollectionRate }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $averageCollectionRate) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Remaining to Collect -->
        <div class="bg-white p-5 rounded-2xl border border-rose-200 shadow-xs relative overflow-hidden bg-gradient-to-br from-white to-rose-50/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-800">المتبقي غير المحصل (مستحقات)</span>
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-rose-600">{{ number_format($totalRemainingAmount, 2) }} <span class="text-xs font-bold text-slate-500">ج.م</span></div>
                <div class="text-[11px] text-slate-500 mt-1">
                    نسبة المتبقي: <span class="font-bold text-rose-600">{{ $totalPlansCost > 0 ? round(($totalRemainingAmount / $totalPlansCost) * 100, 1) : 0 }}%</span> من قيمة الباقات
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Sessions Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Sessions Total -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-semibold block">إجمالي جلسات الباقات</span>
                <span class="text-lg font-black text-slate-900">{{ number_format($totalSessionsCount) }} جلسة</span>
            </div>
        </div>

        <!-- Paid Sessions -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-semibold block">الجلسات المحصلة قيمتها</span>
                <span class="text-lg font-black text-emerald-700">{{ $totalPaidSessionsCount }} جلسة</span>
                <span class="text-[10px] text-slate-400 block">(المسددة بناءً على المبالغ المحصلة)</span>
            </div>
        </div>

        <!-- Remaining Sessions -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-semibold block">جرعات متبقية للتحصيل</span>
                <span class="text-lg font-black text-amber-700">{{ $totalRemainingSessionsCount }} جرعة</span>
                <span class="text-[10px] text-slate-400 block">الجرعات الفعلية: <strong class="text-slate-800">{{ $totalActualCompletedSessions }}</strong> جرعة</span>
            </div>
        </div>
    </div>

    <!-- Filters Bar (Screen Only) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs no-print">
        <form action="{{ route('reports.weight_loss') }}" method="GET" class="space-y-4">
            
            <!-- Quick Date Presets -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <span class="font-bold text-slate-500 whitespace-nowrap">فترات سريعة:</span>
                <a href="{{ route('reports.weight_loss', array_merge(request()->except('quick_filter', 'from_date', 'to_date'), ['quick_filter' => 'today'])) }}"
                   class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $quickFilter === 'today' ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    اليوم
                </a>
                <a href="{{ route('reports.weight_loss', array_merge(request()->except('quick_filter', 'from_date', 'to_date'), ['quick_filter' => 'this_week'])) }}"
                   class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $quickFilter === 'this_week' ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    هذا الأسبوع
                </a>
                <a href="{{ route('reports.weight_loss', array_merge(request()->except('quick_filter', 'from_date', 'to_date'), ['quick_filter' => 'this_month'])) }}"
                   class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $quickFilter === 'this_month' ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    هذا الشهر
                </a>
                <a href="{{ route('reports.weight_loss', array_merge(request()->except('quick_filter', 'from_date', 'to_date'), ['quick_filter' => 'last_month'])) }}"
                   class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $quickFilter === 'last_month' ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    الشهر الماضي
                </a>
                <a href="{{ route('reports.weight_loss', array_merge(request()->except('quick_filter', 'from_date', 'to_date'), ['quick_filter' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $quickFilter === 'all' || (empty($quickFilter) && empty($fromDate) && empty($toDate)) ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    جميع الأوقات
                </a>
            </div>

            <!-- Filter Controls Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <!-- Search input -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">بحث بالمريض أو الباقة</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="اسم المريض، الهاتف، الباقة..."
                               class="w-full pl-3 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Package Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">حالة الباقة</label>
                    <select name="status" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="">-- جميع الحالات --</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>سارية</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>مكتملة</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                    </select>
                </div>

                <!-- Collection Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">موقف التحصيل المالي</label>
                    <select name="collection_status" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500">
                        <option value="">-- كل مواقف السداد --</option>
                        <option value="fully_paid" {{ $collectionStatus === 'fully_paid' ? 'selected' : '' }}>مسددة بالكامل (100%)</option>
                        <option value="partially_paid" {{ $collectionStatus === 'partially_paid' ? 'selected' : '' }}>مسددة جزئياً (1% - 99%)</option>
                        <option value="unpaid" {{ $collectionStatus === 'unpaid' ? 'selected' : '' }}>غير مسددة (0%)</option>
                    </select>
                </div>

                <!-- From Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}"
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $toDate }}"
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            <!-- Submit and Reset Buttons -->
            <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100">
                <a href="{{ route('reports.weight_loss') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    إعادة ضبط
                </a>
                <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                    تطبيق الفلترة
                </button>
            </div>
        </form>
    </div>

    <!-- Plans Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden print-border">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between no-print">
            <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>تفاصيل باقات التخسيس ({{ $plans->count() }} باقة)</span>
            </h3>
            <span class="text-xs text-slate-500">مقسمة حسب الجرعات الفعلية، نسب التحصيل، والجرعات المتبقية</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-black text-slate-600 border-b border-slate-200">
                        <th class="py-3 px-2 text-center w-8">#</th>
                        <th class="py-3 px-3">التاريخ</th>
                        <th class="py-3 px-3">المريض</th>
                        <th class="py-3 px-3">اسم الباقة</th>
                        <th class="py-3 px-2 text-center">الحالة</th>
                        <th class="py-3 px-2 text-center">عدد الجلسات</th>
                        <th class="py-3 px-3 text-left">مبلغ الباقة</th>
                        <th class="py-3 px-3 text-left bg-teal-50/50">تكلفة الجلسة</th>
                        <th class="py-3 px-3 text-left bg-emerald-50/50">المحصل</th>
                        <th class="py-3 px-2 text-center">نسبة التحصيل</th>
                        <th class="py-3 px-2 text-center bg-emerald-50/50">جرعات محصلة</th>
                        <th class="py-3 px-2 text-center bg-amber-50/50">جرعات متبقية</th>
                        <th class="py-3 px-3 text-left bg-rose-50/50">المتبقي المالي</th>
                        <th class="py-3 px-2 text-center">الجرعات الفعلية</th>
                        <th class="py-3 px-2 text-center bg-purple-50/60">الجرعات المتبقية التي لم تنفذ بعد</th>
                        <th class="py-3 px-2 text-center no-print">الإجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($plans as $index => $plan)
                        @php
                            $rate = $plan->collection_rate;
                            $rateColor = $rate >= 100 ? 'text-emerald-700 bg-emerald-100' : ($rate > 0 ? 'text-blue-700 bg-blue-100' : 'text-rose-700 bg-rose-100');
                            $status = $plan->attendance_financial_status;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-2 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                            <td class="py-3 px-3 text-slate-600 whitespace-nowrap text-[11px] font-mono">
                                {{ $plan->created_at->format('Y-m-d') }}
                            </td>
                            <td class="py-3 px-3">
                                <a href="{{ route('patients.show', ['patient' => $plan->patient_id, 'tab' => 'weight-loss']) }}" 
                                   class="font-bold text-slate-900 hover:text-teal-700 transition block leading-snug">
                                    {{ $plan->patient?->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 font-mono block">{{ $plan->patient?->phone }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $plan->package_name }}</span>
                                @if($plan->notes)
                                    <span class="text-[10px] text-slate-500 block truncate max-w-xs">{{ $plan->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center whitespace-nowrap">
                                @if($plan->status === 'active')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">سارية</span>
                                @elseif($plan->status === 'completed')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">مكتملة</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">ملغاة</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center font-bold text-slate-900 font-mono">
                                {{ $plan->total_sessions }}
                            </td>
                            <td class="py-3 px-3 text-left font-black text-slate-900 font-mono whitespace-nowrap">
                                {{ number_format($plan->cost, 2) }}
                            </td>
                            <td class="py-3 px-3 text-left font-bold text-teal-800 bg-teal-50/40 font-mono whitespace-nowrap">
                                {{ number_format($plan->session_cost, 2) }}
                            </td>
                            <td class="py-3 px-3 text-left font-black text-emerald-700 bg-emerald-50/40 font-mono whitespace-nowrap">
                                {{ number_format($plan->collected_amount, 2) }}
                            </td>
                            <td class="py-3 px-2 text-center whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono {{ $rateColor }}">
                                    {{ $rate }}%
                                </span>
                            </td>
                            <td class="py-3 px-2 text-center font-bold text-emerald-800 bg-emerald-50/40 font-mono">
                                {{ $plan->paid_sessions_count }}
                            </td>
                            <td class="py-3 px-2 text-center font-bold text-amber-800 bg-amber-50/40 font-mono">
                                {{ $plan->remaining_sessions_to_collect }}
                            </td>
                            <td class="py-3 px-3 text-left font-black {{ $plan->remaining_amount > 0 ? 'text-rose-600' : 'text-slate-400' }} bg-rose-50/40 font-mono whitespace-nowrap">
                                {{ number_format($plan->remaining_amount, 2) }}
                            </td>
                            <td class="py-3 px-2 text-center whitespace-nowrap">
                                <span class="block text-[11px] font-bold text-slate-800 font-mono">
                                    {{ $plan->completed_sessions_count }} / {{ $plan->total_sessions }}
                                </span>
                                <span class="inline-block text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $status['badge_class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-center whitespace-nowrap bg-purple-50/30">
                                @if($plan->remaining_sessions_count > 0)
                                    <span class="inline-block font-mono font-bold text-purple-800 text-[11px] px-2 py-0.5 rounded-full bg-purple-100 border border-purple-200">
                                        {{ $plan->remaining_sessions_count }} جرعة
                                    </span>
                                @else
                                    <span class="inline-block text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        أُنجزت بالكامل ✓
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center no-print">
                                <a href="{{ route('patients.show', ['patient' => $plan->patient_id, 'tab' => 'weight-loss']) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-teal-700 hover:text-white bg-teal-50 hover:bg-teal-600 rounded-lg transition" title="عرض ملف المريض">
                                    <span>فتح</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h4 class="text-sm font-bold text-slate-700">لا توجد باقات تخسيس مسجلة مطابقة لمعايير البحث</h4>
                                <p class="text-xs text-slate-400 mt-1">يرجى تعديل خيارات الفلترة أو التأكد من إدخال باقات تخسيس للمرضى.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($plans->isNotEmpty())
                    <tfoot class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-300 text-xs">
                        <tr>
                            <td colspan="5" class="py-3 px-3 text-right">الإجمالي الكلي لعدد ({{ $totalPlansCount }} باقة):</td>
                            <td class="py-3 px-2 text-center font-mono text-slate-900">{{ number_format($totalSessionsCount) }}</td>
                            <td class="py-3 px-3 text-left font-mono text-slate-900">{{ number_format($totalPlansCost, 2) }}</td>
                            <td class="py-3 px-3 text-left font-mono text-teal-900">{{ $totalSessionsCount > 0 ? number_format($totalPlansCost / $totalSessionsCount, 2) : '0.00' }}</td>
                            <td class="py-3 px-3 text-left font-mono text-emerald-800">{{ number_format($totalCollectedAmount, 2) }}</td>
                            <td class="py-3 px-2 text-center font-mono text-emerald-800">{{ $averageCollectionRate }}%</td>
                            <td class="py-3 px-2 text-center font-mono text-emerald-800">{{ $totalPaidSessionsCount }}</td>
                            <td class="py-3 px-2 text-center font-mono text-amber-800">{{ $totalRemainingSessionsCount }}</td>
                            <td class="py-3 px-3 text-left font-mono text-rose-700">{{ number_format($totalRemainingAmount, 2) }}</td>
                            <td class="py-3 px-2 text-center font-mono text-slate-800">{{ $totalActualCompletedSessions }} جرعة</td>
                            <td class="py-3 px-2 text-center font-mono text-purple-900 bg-purple-100/50 font-black">{{ $totalUnexecutedSessions }} جرعة</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Official Signatures (Print Only) -->
    <div class="print-only mt-10 pt-6 border-t border-slate-300">
        <div class="grid grid-cols-3 gap-6 text-center text-xs">
            <div>
                <span class="font-bold text-slate-700 block mb-8">إعداد / قسم الحسابات:</span>
                <span class="border-b border-dotted border-slate-400 block w-32 mx-auto"></span>
            </div>
            <div>
                <span class="font-bold text-slate-700 block mb-8">مراجعة مسؤول التخسيس:</span>
                <span class="border-b border-dotted border-slate-400 block w-32 mx-auto"></span>
            </div>
            <div>
                <span class="font-bold text-slate-700 block mb-8">اعتماد الإدارة والمدير الطبي:</span>
                <span class="border-b border-dotted border-slate-400 block w-32 mx-auto"></span>
            </div>
        </div>
    </div>

</div>
@endsection
