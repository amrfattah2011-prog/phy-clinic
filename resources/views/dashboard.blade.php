@extends('layouts.app')

@section('title', 'لوحة التحكم اليومية - عيادة العلاج الطبيعي والتخسيس')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Quick Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2">
                <span>لوحة التحكم اليومية</span>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">
                    {{ \Carbon\Carbon::now()->translatedFormat('l، d F Y') }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">متابعة فورية لحضور جلسات العلاج الطبيعي، جرعات التخسيس، وحركة الإيرادات وسندات القبض.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('patients.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>تسجيل حالة جديدة</span>
            </a>
            <a href="{{ route('patients.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>دليل المرضى الكامل</span>
            </a>
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue Today -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">إيراد اليوم المحصل</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">{{ number_format($todayRevenue, 2) }} <span class="text-xs font-bold text-slate-500">ج.م</span></div>
                <div class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                    <span>تحصيلات اليوم المسجلة بالإيصالات</span>
                </div>
            </div>
        </div>

        <!-- Physical Therapy Sessions Today -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">جلسات العلاج الطبيعي اليوم</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <span class="text-2xl font-black text-slate-900" id="stat-therapy-attended">{{ $todayAttendedTherapySessionsCount }}</span>
                    <span class="text-xs text-slate-400 font-medium">/ {{ $todayTherapySessionsCount }} حضور</span>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $todayPendingTherapySessionsCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                    متبقي <span id="stat-therapy-pending">{{ $todayPendingTherapySessionsCount }}</span>
                </span>
            </div>
        </div>

        <!-- Weight Loss Doses Today -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">جرعات وجلسات التخسيس اليوم</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <span class="text-2xl font-black text-slate-900" id="stat-weight-completed">{{ $todayCompletedWeightLossCount }}</span>
                    <span class="text-xs text-slate-400 font-medium">/ {{ $todayWeightLossSessionsCount }} جرعة</span>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $todayPendingWeightLossCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                    متبقي <span id="stat-weight-pending">{{ $todayPendingWeightLossCount }}</span>
                </span>
            </div>
        </div>

        <!-- Total Patients -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">إجمالي الحالات المسجلة</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <span class="text-2xl font-black text-slate-900">{{ $totalPatientsCount }}</span>
                    <span class="text-xs text-slate-400 font-medium">مريض</span>
                </div>
                @if($todayPatientsCount > 0)
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $todayPatientsCount }} اليوم</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Area: Today's Schedule & Interactive Checklists -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Today's Sessions Checklists (Interactive) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card: Today's Physical Therapy Sessions with AJAX Checkbox -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-blue-600"></div>
                        <h2 class="text-base font-bold text-slate-900">جلسات العلاج الطبيعي المجدولة لليوم</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-2.5 py-1 rounded-lg">
                        {{ count($todayTherapySessions) }} جلسة
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">تم الحضور</th>
                                <th class="py-3 px-4">اسم المريض</th>
                                <th class="py-3 px-4">الباقة / الجلسة</th>
                                <th class="py-3 px-4">الهاتف</th>
                                <th class="py-3 px-4">الحالة</th>
                                <th class="py-3 px-4 text-center">الملف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($todayTherapySessions as $session)
                                <tr id="therapy-row-{{ $session->id }}" class="hover:bg-slate-50 transition {{ $session->is_attended ? 'bg-emerald-50/30' : '' }}">
                                    <td class="py-3 px-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   class="w-5 h-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 transition cursor-pointer"
                                                   {{ $session->is_attended ? 'checked' : '' }}
                                                   onchange="toggleTherapyAttendance({{ $session->id }}, this)">
                                        </label>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('patients.show', $session->patient_id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition block">
                                            {{ $session->patient->name }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-medium text-slate-700 block">{{ $session->plan->package_name }}</span>
                                        <span class="text-[11px] text-blue-600 font-bold">جلسة رقم {{ $session->session_number }} من {{ $session->plan->total_sessions }}</span>
                                        @if($session->devices && $session->devices->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($session->devices as $device)
                                                    <span class="text-[10px] px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded-md font-semibold border border-blue-200/60">{{ $device->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600">
                                        {{ $session->patient->phone }}
                                    </td>
                                    <td class="py-3 px-4" id="therapy-status-{{ $session->id }}">
                                        @if($session->is_attended)
                                            <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full text-[11px] font-bold">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                تم الحضور
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded-full text-[11px] font-bold">
                                                في الانتظار
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('patients.show', ['patient' => $session->patient_id, 'tab' => 'therapy']) }}" class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg inline-block transition" title="عرض ملف الباقة">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        لا توجد جلسات علاج طبيعي مجدولة بتاريخ اليوم
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card: Today's Weight Loss & Dosage Sessions with AJAX Checkbox -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-purple-600"></div>
                        <h2 class="text-base font-bold text-slate-900">جرعات وجلسات التخسيس لليوم</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-2.5 py-1 rounded-lg">
                        {{ count($todayWeightLossSessions) }} جرعة
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">أخذ الجرعة</th>
                                <th class="py-3 px-4">اسم المريض</th>
                                <th class="py-3 px-4">نوع الحقنة / الجرعة</th>
                                <th class="py-3 px-4">الوزن / BMI</th>
                                <th class="py-3 px-4">الحالة</th>
                                <th class="py-3 px-4 text-center">الملف</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($todayWeightLossSessions as $wls)
                                <tr id="weight-row-{{ $wls->id }}" class="hover:bg-slate-50 transition {{ $wls->is_completed ? 'bg-purple-50/30' : '' }}">
                                    <td class="py-3 px-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   class="w-5 h-5 text-purple-600 rounded border-slate-300 focus:ring-purple-500 transition cursor-pointer"
                                                   {{ $wls->is_completed ? 'checked' : '' }}
                                                   onchange="toggleWeightCompleted({{ $wls->id }}, this)">
                                        </label>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('patients.show', $wls->patient_id) }}" class="font-bold text-slate-900 hover:text-purple-600 transition block">
                                            {{ $wls->patient->name }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($wls->plan)
                                            <span class="font-bold text-purple-900 block">{{ $wls->plan->package_name }}</span>
                                            <span class="text-[11px] text-purple-600 font-semibold block">جلسة رقم {{ $wls->session_number }} من {{ $wls->plan->total_sessions }}</span>
                                        @else
                                            <span class="font-bold text-purple-900">{{ $wls->injection_type ?: 'جلسة تخسيس' }}</span>
                                        @endif
                                        @if($wls->dosage_amount)
                                            <span class="block text-[11px] text-slate-500">جرعة: {{ $wls->dosage_amount }}</span>
                                        @endif
                                        @if($wls->devices && $wls->devices->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($wls->devices as $device)
                                                    <span class="text-[10px] px-1.5 py-0.5 bg-purple-50 text-purple-700 rounded-md font-semibold border border-purple-200/60">{{ $device->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono">
                                        @if($wls->weight)
                                            <span class="font-bold text-slate-800">{{ $wls->weight }} كجم</span>
                                            @if($wls->bmi)
                                                <span class="block text-[11px] text-slate-500">BMI: {{ $wls->bmi }}</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4" id="weight-status-{{ $wls->id }}">
                                        @if($wls->is_completed)
                                            <span class="inline-flex items-center gap-1 text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full text-[11px] font-bold">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                تمت الجرعة
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full text-[11px] font-bold">
                                                قيد الانتظار
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('patients.show', ['patient' => $wls->patient_id, 'tab' => 'weight-loss']) }}" class="p-1.5 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg inline-block transition" title="عرض سجل التخسيس">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        لا توجد جرعات تخسيس مجدولة لليوم
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Column 3: Recent Receipts / Quick Print & Today's Consultations -->
        <div class="space-y-6">

            <!-- Card: Recent Receipts with Quick Thermal Print Button -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-600"></div>
                        <h2 class="text-sm font-bold text-slate-900">سندات القبض الأخيرة (إيصالات حرارية)</h2>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($recentPayments as $payment)
                        <div class="p-3.5 hover:bg-slate-50 transition flex items-center justify-between text-xs">
                            <div>
                                <a href="{{ route('patients.show', $payment->patient_id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition block">
                                    {{ $payment->patient->name }}
                                </a>
                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span class="font-mono font-semibold">{{ $payment->receipt_number }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $payment->payment_date->format('Y-m-d') }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-left font-black text-emerald-700">
                                    {{ number_format($payment->amount, 2) }} <span class="text-[10px]">ج.م</span>
                                </div>
                                <a href="{{ route('payments.receipt', $payment->id) }}" target="_blank" 
                                   class="p-2 bg-slate-100 hover:bg-emerald-600 hover:text-white rounded-xl text-slate-600 transition shadow-2xs" 
                                   title="طباعة إيصال حراري (80mm)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            لا توجد سندات قبض مسجلة مؤخراً
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Card: Today's Consultations -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-teal-600"></div>
                        <h2 class="text-sm font-bold text-slate-900">كشوفات اليوم</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-2.5 py-0.5 rounded-lg">
                        {{ count($todayVisits) }} كشف
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($todayVisits as $visit)
                        <div class="p-3.5 hover:bg-slate-50 transition text-xs">
                            <div class="flex justify-between items-start">
                                <a href="{{ route('patients.show', $visit->patient_id) }}" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                    {{ $visit->patient->name }}
                                </a>
                                <span class="font-black text-slate-900">{{ number_format($visit->cost, 2) }} ج.م</span>
                            </div>
                            <p class="text-slate-600 text-[11px] mt-1 line-clamp-1"><span class="font-bold text-slate-700">التشخيص:</span> {{ $visit->diagnosis }}</p>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            لا توجد كشوفات مسجلة اليوم
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    // Reactive AJAX Attendance Toggle for Physical Therapy
    async function toggleTherapyAttendance(sessionId, checkboxElem) {
        checkboxElem.disabled = true;
        try {
            const response = await fetch(`/therapy-sessions/${sessionId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                
                // Update row styling
                const row = document.getElementById(`therapy-row-${sessionId}`);
                const statusTd = document.getElementById(`therapy-status-${sessionId}`);
                
                if (data.is_attended) {
                    row.classList.add('bg-emerald-50/30');
                    statusTd.innerHTML = `
                        <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full text-[11px] font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            تم الحضور
                        </span>
                    `;
                } else {
                    row.classList.remove('bg-emerald-50/30');
                    statusTd.innerHTML = `
                        <span class="inline-flex items-center text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded-full text-[11px] font-bold">
                            في الانتظار
                        </span>
                    `;
                }

                // Update counter metrics
                const attendedElem = document.getElementById('stat-therapy-attended');
                const pendingElem = document.getElementById('stat-therapy-pending');
                if (attendedElem && pendingElem) {
                    let attended = parseInt(attendedElem.innerText) || 0;
                    let pending = parseInt(pendingElem.innerText) || 0;
                    if (data.is_attended) {
                        attendedElem.innerText = attended + 1;
                        pendingElem.innerText = Math.max(0, pending - 1);
                    } else {
                        attendedElem.innerText = Math.max(0, attended - 1);
                        pendingElem.innerText = pending + 1;
                    }
                }
            } else {
                checkboxElem.checked = !checkboxElem.checked;
                showToast('حدث خطأ أثناء تحديث حالة الحضور.', 'error');
            }
        } catch (e) {
            checkboxElem.checked = !checkboxElem.checked;
            showToast('تعذر الاتصال بالخادم.', 'error');
        } finally {
            checkboxElem.disabled = false;
        }
    }

    // Reactive AJAX Dosage Toggle for Weight Loss
    async function toggleWeightCompleted(sessionId, checkboxElem) {
        checkboxElem.disabled = true;
        try {
            const response = await fetch(`/weight-loss-sessions/${sessionId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                
                const row = document.getElementById(`weight-row-${sessionId}`);
                const statusTd = document.getElementById(`weight-status-${sessionId}`);
                
                if (data.is_completed) {
                    row.classList.add('bg-purple-50/30');
                    statusTd.innerHTML = `
                        <span class="inline-flex items-center gap-1 text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full text-[11px] font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            تمت الجرعة
                        </span>
                    `;
                } else {
                    row.classList.remove('bg-purple-50/30');
                    statusTd.innerHTML = `
                        <span class="inline-flex items-center text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full text-[11px] font-bold">
                            قيد الانتظار
                        </span>
                    `;
                }

                const completedElem = document.getElementById('stat-weight-completed');
                const pendingElem = document.getElementById('stat-weight-pending');
                if (completedElem && pendingElem) {
                    let completed = parseInt(completedElem.innerText) || 0;
                    let pending = parseInt(pendingElem.innerText) || 0;
                    if (data.is_completed) {
                        completedElem.innerText = completed + 1;
                        pendingElem.innerText = Math.max(0, pending - 1);
                    } else {
                        completedElem.innerText = Math.max(0, completed - 1);
                        pendingElem.innerText = pending + 1;
                    }
                }
            } else {
                checkboxElem.checked = !checkboxElem.checked;
                showToast('حدث خطأ أثناء تحديث حالة الجرعة.', 'error');
            }
        } catch (e) {
            checkboxElem.checked = !checkboxElem.checked;
            showToast('تعذر الاتصال بالخادم.', 'error');
        } finally {
            checkboxElem.disabled = false;
        }
    }
</script>
@endpush
@endsection
