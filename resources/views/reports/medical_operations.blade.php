@extends('layouts.app')

@section('title', 'تقرير العمليات والأنشطة الطبية - ' . config('app.name'))

@push('styles')
<style>
@media print {
    header, nav, form, .no-print, footer {
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
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <span>تقرير العمليات والحركات الطبية</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">حصر شامل لجميع ما تم في المركز من كشوفات، جلسات علاج طبيعي، وجلسات تخسيس مع بيان المنفّذ والأجهزة.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>طباعة التقرير (A4)</span>
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
                <h3 class="text-lg font-black text-teal-800">تقرير العمليات الطبية المنفذة</h3>
                <div class="text-xs font-bold text-slate-600 mt-1">
                    الفترة: من {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('Y/m/d') : 'البداية' }} إلى {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('Y/m/d') : 'الآن' }}
                </div>
                <div class="text-[10px] text-slate-400">تاريخ الطباعة: {{ now()->format('Y/m/d - h:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 block">إجمالي العمليات</span>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ $totalOperations }}</div>
            <span class="text-[11px] text-slate-400">حركة منفذة في الفترة</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-blue-200 bg-blue-50/20 shadow-xs">
            <span class="text-xs font-bold text-blue-700 block">الكشوفات الطبية</span>
            <div class="mt-2 text-2xl font-black text-blue-700">{{ $visitsCount }}</div>
            <span class="text-[11px] text-blue-500">كشف واستشارة ومتابعة</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs">
            <span class="text-xs font-bold text-emerald-700 block">جلسات العلاج الطبيعي</span>
            <div class="mt-2 text-2xl font-black text-emerald-700">{{ $therapyCount }}</div>
            <span class="text-[11px] text-emerald-500">جلسة حضور وتأهيل</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-purple-200 bg-purple-50/20 shadow-xs">
            <span class="text-xs font-bold text-purple-700 block">جلسات التخسيس والنحت</span>
            <div class="mt-2 text-2xl font-black text-purple-700">{{ $weightLossCount }}</div>
            <span class="text-[11px] text-purple-500">جلسة وجرعة منجزة</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-xs font-bold text-slate-500 block">المرضى المستفيدين</span>
            <div class="mt-2 text-2xl font-black text-teal-700">{{ $uniquePatientsCount }}</div>
            <span class="text-[11px] text-teal-600">مريض مختلف في الفترة</span>
        </div>
    </div>

    <!-- Filter Bar (Screen Only) -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs no-print">
        <form action="{{ route('reports.operations') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">نوع الحركة الطبية</label>
                <select name="type" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>جميع العمليات (الكل)</option>
                    <option value="visits" {{ $type === 'visits' ? 'selected' : '' }}>الكشوفات الطبية فقط</option>
                    <option value="therapy" {{ $type === 'therapy' ? 'selected' : '' }}>جلسات العلاج الطبيعي فقط</option>
                    <option value="weight_loss" {{ $type === 'weight_loss' ? 'selected' : '' }}>جلسات التخسيس ونحت القوام</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">الموظف / الطبيب القائم بالعمل</label>
                <select name="user_id" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">جميع الأطباء والموظفين</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role?->display_name ?: 'طاقم العمل' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">بحث بالمريض</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="اسم المريض أو رقم الهاتف..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2 rounded-xl transition">
                    تصفية
                </button>
                <a href="{{ route('reports.operations') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إعادة ضبط
                </a>
            </div>
        </form>
    </div>

    <!-- Operations Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden print-border">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                        <th class="py-3 px-3 w-12 text-center">#</th>
                        <th class="py-3 px-3 whitespace-nowrap">التاريخ</th>
                        <th class="py-3 px-3 whitespace-nowrap">نوع العملية</th>
                        <th class="py-3 px-3">المريض</th>
                        <th class="py-3 px-3">تفاصيل الإجراء الطبي</th>
                        <th class="py-3 px-3">الأجهزة المستخدمة</th>
                        <th class="py-3 px-3 whitespace-nowrap">القائم بالعملية</th>
                        <th class="py-3 px-3 no-print">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sortedOperations as $op)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-3 px-3 font-bold text-slate-800 whitespace-nowrap">
                                <div>{{ $op->date }}</div>
                                @if($op->created_at)
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $op->created_at->format('h:i A') }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($op->type === 'visit')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>{{ $op->type_label }}</span>
                                    </span>
                                @elseif($op->type === 'therapy')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>{{ $op->type_label }}</span>
                                    </span>
                                @elseif($op->type === 'weight_loss')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                        <span>{{ $op->type_label }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($op->patient)
                                    <a href="{{ route('patients.show', $op->patient) }}" class="font-bold text-slate-900 hover:text-brand-600 block">
                                        {{ $op->patient->name }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $op->patient->phone }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-semibold text-slate-800">{{ $op->title }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $op->details }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="text-[11px] font-medium text-slate-700">{{ $op->devices }}</span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $op->staff_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $op->staff_role }}</div>
                            </td>
                            <td class="py-3 px-3 text-[11px] text-slate-400 no-print">
                                {{ $op->notes ? Str::limit($op->notes, 40) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="font-bold text-slate-600">لا توجد حركات أو عمليات مطابقة للخيارات المحددة</p>
                                <p class="text-xs text-slate-400 mt-1">جرّب تغيير النطاق الزمني أو نوع العملية أو الموظف.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection