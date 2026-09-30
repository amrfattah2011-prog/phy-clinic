@extends('layouts.app')

@section('title', 'ملف المريض - ' . $patient->name)

@section('content')
@php
    $clinicSetting = \App\Models\ClinicSetting::getSettings();
@endphp
<div class="space-y-6">

    <!-- Top Patient Header & Financial Summary Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Patient Info -->
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr {{ $patient->gender === 'male' ? 'from-blue-600 to-teal-500' : 'from-purple-600 to-pink-500' }} text-white flex items-center justify-center font-black text-2xl shadow-md flex-shrink-0">
                    {{ mb_substr($patient->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900">{{ $patient->name }}</h1>
                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                            ملف #{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $patient->gender === 'male' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                            {{ $patient->gender_arabic }} - {{ $patient->age }} سنة
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-slate-500 mt-2 flex-wrap">
                        <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="font-mono font-bold text-slate-700">{{ $patient->phone }}</span>
                            @if($patient->whatsapp_phone)
                                <button type="button" onclick="openWhatsappModal()" 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[10px] transition shadow-xs cursor-pointer" 
                                        title="مراسلة المريض عبر واتساب">
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>واتساب</span>
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>تاريخ التسجيل: {{ $patient->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>

                    <!-- Chronic Diseases Badges & Quick Edit -->
                    <div class="mt-3 flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-500">الأمراض المزمنة:</span>
                        @forelse($patient->diseases as $disease)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-900 border border-amber-200 text-xs font-bold">
                                {{ $disease->name }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400 italic">لا توجد أمراض مزمنة مسجلة</span>
                        @endforelse

                        <!-- Button to toggle quick diseases editor -->
                        <button onclick="document.getElementById('diseases-quick-modal').classList.toggle('hidden')" 
                                class="text-[11px] font-bold text-amber-700 hover:text-amber-800 bg-amber-100/60 hover:bg-amber-100 px-2 py-0.5 rounded-lg transition">
                            + تعديل الأمراض
                        </button>
                    </div>

                    @if($patient->medical_history)
                        <div class="mt-2.5 p-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs text-slate-700 flex items-start gap-1.5">
                            <span class="font-bold text-slate-900 flex-shrink-0">ملاحظات طبية:</span>
                            <span>{{ $patient->medical_history }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                <div class="text-center px-3 border-l border-slate-200">
                    <span class="text-[11px] font-bold text-slate-500 block">إجمالي التكاليف</span>
                    <span class="text-base font-black text-slate-900" id="patient-total-cost">{{ number_format($patient->total_cost, 2) }}</span>
                    <span class="text-[10px] text-slate-400 block">ج.م</span>
                </div>
                <div class="text-center px-3 border-l border-slate-200">
                    <span class="text-[11px] font-bold text-emerald-600 block">المسدد (المدفوع)</span>
                    <span class="text-base font-black text-emerald-700" id="patient-total-paid">{{ number_format($patient->total_paid, 2) }}</span>
                    <span class="text-[10px] text-emerald-500 block">ج.م</span>
                </div>
                <div class="text-center px-3">
                    <span class="text-[11px] font-bold {{ $patient->remaining_balance > 0 ? 'text-rose-600' : 'text-slate-500' }} block">المتبقي</span>
                    <span class="text-lg font-black {{ $patient->remaining_balance > 0 ? 'text-rose-600 underline' : 'text-emerald-600' }}" id="patient-remaining-balance">
                        {{ number_format($patient->remaining_balance, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">ج.م</span>
                </div>
            </div>
        </div>

        <!-- Quick Diseases Editor Dropdown Modal -->
        <div id="diseases-quick-modal" class="hidden mt-4 p-4 bg-amber-50/80 border border-amber-200 rounded-xl space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-amber-950">تحديث الأمراض المزمنة للحالة وإضافة أمراض جديدة</h3>
                <button onclick="document.getElementById('diseases-quick-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xs font-bold">&times; إغلاق</button>
            </div>
            @php $patientDiseaseIds = $patient->diseases->pluck('id')->toArray(); @endphp
            <form action="{{ route('patients.diseases.update', $patient) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    @foreach($allDiseases as $disease)
                        <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-amber-200 text-xs cursor-pointer hover:bg-amber-100/50">
                            <input type="checkbox" name="disease_ids[]" value="{{ $disease->id }}"
                                   {{ in_array($disease->id, $patientDiseaseIds) ? 'checked' : '' }}
                                   class="w-4 h-4 text-amber-600 rounded border-slate-300 focus:ring-amber-500">
                            <span class="text-slate-800">{{ $disease->name }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-amber-200">
                    <input type="text" name="new_disease_name" placeholder="+ كتابة مرض جديد لإضافته للقائمة فوراً..."
                           class="flex-1 px-3 py-1.5 text-xs bg-white border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <button type="submit" class="px-4 py-1.5 bg-amber-700 hover:bg-amber-800 text-white text-xs font-bold rounded-lg transition">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Actions Toolbar -->
        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="switchTab('visits'); document.getElementById('visit-complaint')?.focus();" 
                        class="px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>تسجيل كشف جديد</span>
                </button>
                <button onclick="switchTab('financial'); document.getElementById('payment-amount')?.focus();" 
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>سداد دفعة / سند قبض</span>
                </button>
                <button type="button" onclick="openWhatsappModal()" 
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>مراسلة واتساب</span>
                </button>
                <a href="{{ $patient->portal_url }}" target="_blank"
                   class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-800 text-xs font-bold rounded-xl border border-blue-200 transition flex items-center gap-1.5"
                   title="فتح بوابة المريض الرقمية كما يراها المريض في هاتفه">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>بوابة المريض الرقمية</span>
                </a>
                <a href="{{ route('patients.statement', $patient) }}" target="_blank"
                   class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>طباعة كشف حساب مالي كامل</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="px-3 py-1.5 text-slate-500 hover:text-slate-800 text-xs font-bold rounded-xl hover:bg-slate-100 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>تعديل البيانات الأساسية</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (VISITS IS FIRST BY DEFAULT) -->
    @php
        $activeTab = request('tab', 'visits'); // الكشف هو التبويب الافتراضي الأول!
    @endphp
    <div class="flex items-center gap-1.5 border-b border-slate-200 overflow-x-auto pb-px">
        <!-- TAB 1: VISITS & DIAGNOSES (FIRST) -->
        <button onclick="switchTab('visits')" id="tab-btn-visits" 
                class="tab-btn px-3 py-2 text-[11px] sm:text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap {{ $activeTab === 'visits' ? 'border-brand-600 text-brand-700 bg-brand-50/40 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>1. الكشوفات والتشخيص الطبي ({{ count($patient->visits) }})</span>
        </button>

        <!-- TAB 2: PHYSICAL THERAPY & SESSIONS & DEVICES -->
        <button onclick="switchTab('therapy')" id="tab-btn-therapy" 
                class="tab-btn px-3 py-2 text-[11px] sm:text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap {{ $activeTab === 'therapy' ? 'border-brand-600 text-brand-700 bg-brand-50/40 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>2. باقات وجلسات العلاج الطبيعي ({{ count($patient->therapyPlans) }})</span>
        </button>

        <!-- TAB 3: WEIGHT LOSS PACKAGES & SESSIONS & DEVICES -->
        <button onclick="switchTab('weight-loss')" id="tab-btn-weight-loss" 
                class="tab-btn px-3 py-2 text-[11px] sm:text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap {{ $activeTab === 'weight-loss' ? 'border-brand-600 text-brand-700 bg-brand-50/40 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            <span>3. باقات وجلسات التخسيس ({{ count($patient->weightLossPlans) }} باقة / {{ count($patient->weightLossSessions) }} جلسة)</span>
        </button>

        <!-- TAB 4: FINANCIAL LEDGER & PAYMENTS -->
        <button onclick="switchTab('financial')" id="tab-btn-financial" 
                class="tab-btn px-3 py-2 text-[11px] sm:text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap {{ $activeTab === 'financial' ? 'border-brand-600 text-brand-700 bg-brand-50/40 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>4. الحسابات والمدفوعات ({{ count($patient->payments) }})</span>
        </button>

        <!-- TAB 5: ATTACHMENTS & MEDICAL DOCUMENTS -->
        <button onclick="switchTab('attachments')" id="tab-btn-attachments" 
                class="tab-btn px-3 py-2 text-[11px] sm:text-xs font-bold border-b-2 transition flex items-center gap-1.5 whitespace-nowrap {{ $activeTab === 'attachments' ? 'border-brand-600 text-brand-700 bg-brand-50/40 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            <span>5. المرفقات والأشعة ({{ count($patient->attachments) }})</span>
        </button>
    </div>

    <!-- TAB 1 CONTENT: VISITS & DIAGNOSES (FIRST) -->
    <div id="tab-content-visits" class="tab-pane {{ $activeTab === 'visits' ? '' : 'hidden' }} space-y-4">
        <!-- Form: Record New Visit -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
            <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5 mb-3">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>تسجيل كشف / استشارة طبية جديدة للحالة</span>
            </h2>

            <form action="{{ route('visits.store', $patient) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ الكشف <span class="text-rose-500">*</span></label>
                        <input type="date" name="visit_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">رسوم الكشف (ج.م)</label>
                        <input type="number" step="0.01" name="cost" value="250.00"
                               class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">شكوى المريض والأعراض (Complaint) <span class="text-rose-500">*</span></label>
                    <textarea name="complaint" id="visit-complaint" rows="2" required placeholder="مثال: ألم شديد أسفل الظهر ممتد للطرف السفلي الأيمن، صعوبة في الحركة، ثقل بالركبتين..."
                              class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition leading-relaxed"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">التشخيص الطبي وتحديد الحالة (Diagnosis) <span class="text-rose-500">*</span></label>
                    <textarea name="diagnosis" rows="2" required placeholder="مثال: انزلاق غضروفي قطني L4-L5 مع عرق النسا، خشونة مفصل الركبة، سمنة موضعية..."
                              class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition leading-relaxed"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">ملاحظات الطبيب والخطة العلاجية المقترحة</label>
                    <input type="text" name="doctor_notes" placeholder="مثال: البدء في باقة علاج طبيعي 12 جلسة + جلسات شد فقرات وليزر، أو حمية غذائية..."
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-[11px] font-bold rounded-xl shadow-xs transition">
                        حفظ الكشف الطبي
                    </button>
                </div>
            </form>
        </div>

        <!-- Visits Log Cards -->
        <div class="space-y-3">
            @forelse($patient->visits as $visit)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-lg bg-teal-50 text-teal-800 text-[10px] font-bold">
                                كشف طبي
                            </span>
                            <span class="text-[11px] font-mono text-slate-500 font-bold">
                                {{ $visit->visit_date->format('Y-m-d') }}
                            </span>
                            @if($visit->creator)
                                <span class="text-[10px] text-slate-500 font-medium">
                                    المسجل: <strong class="text-slate-800">{{ $visit->creator->name }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] font-black text-slate-900">رسوم الكشف: {{ number_format($visit->cost, 2) }} ج.م</span>
                            <form action="{{ route('visits.destroy', $visit) }}" method="POST" onsubmit="return confirm('هل تريد حذف سجل هذا الكشف؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="حذف الكشف">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-[11px]">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="text-slate-500 font-bold block mb-1">الشكوى والأعراض:</span>
                            <p class="text-slate-800 font-medium leading-relaxed">{{ $visit->complaint }}</p>
                        </div>
                        <div class="bg-brand-50/50 p-2.5 rounded-xl border border-brand-100/60">
                            <span class="text-brand-800 font-bold block mb-1">التشخيص الطبي:</span>
                            <p class="text-brand-950 font-bold leading-relaxed">{{ $visit->diagnosis }}</p>
                        </div>
                    </div>

                    @if($visit->doctor_notes)
                        <div class="text-[11px] text-slate-600 bg-slate-50/50 p-2 rounded-xl border border-slate-100">
                            <strong class="text-slate-700">ملاحظات الطبيب:</strong> {{ $visit->doctor_notes }}
                        </div>
                    @endif
                </div>

            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                    لا توجد كشوفات طبية مسجلة حتى الآن
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2 CONTENT: PHYSICAL THERAPY PLANS & ATTENDANCE & DEVICES -->
    <div id="tab-content-therapy" class="tab-pane {{ $activeTab === 'therapy' ? '' : 'hidden' }} space-y-6">
        <!-- Form: Create New Therapy Plan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>وصف باقة علاج طبيعي جديدة للحالة</span>
            </h2>

            <form action="{{ route('therapy-plans.store', $patient) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">اسم الباقة / البرنامج <span class="text-rose-500">*</span></label>
                    <input type="text" name="package_name" required placeholder="مثال: تأهيل غضروف قطني (12 جلسة)"
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">إجمالي عدد الجلسات <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_sessions" value="12" min="1" max="100" required
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">سعر / تكلفة الباقة (ج.م) <span class="text-rose-500">*</span></label>
                    <input type="number" name="cost" step="0.01" required placeholder="مثال: 1800"
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition font-bold">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ البدء المقترح</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}"
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <input type="text" name="notes" placeholder="ملاحظات توجيهية للأخصائي (مثال: تمارين استطالة، ليزر بارد، موجات فوق صوتية...)"
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-1.5 px-4 bg-brand-600 hover:bg-brand-700 text-white text-[11px] font-bold rounded-xl shadow-xs transition">
                        حفظ وتوليد الجلسات
                    </button>
                </div>
            </form>
        </div>

        <!-- Therapy Plans List & Session Attendance Tracker with Devices -->
        @forelse($patient->therapyPlans as $plan)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <!-- Plan Header -->
                <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-slate-900">{{ $plan->package_name }}</h3>
                            <span id="plan-badge-status-{{ $plan->id }}" class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $plan->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $plan->status_arabic }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2.5">
                            <span>تاريخ البدء: {{ $plan->created_at->format('Y-m-d') }}</span>
                            <span>&bull;</span>
                            <span>التكلفة: <strong class="text-slate-900">{{ number_format($plan->cost, 2) }} ج.م</strong></span>
                            @if($plan->notes)
                                <span>&bull;</span>
                                <span class="text-slate-600">{{ $plan->notes }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Progress & Counters -->
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-[11px] font-bold text-slate-700">
                                المنجز: <span id="plan-completed-{{ $plan->id }}" class="font-black text-emerald-600">{{ $plan->completed_sessions_count }}</span> من {{ $plan->total_sessions }}
                                (متبقي <span id="plan-remaining-{{ $plan->id }}" class="font-black text-rose-600">{{ $plan->remaining_sessions_count }}</span>)
                            </div>
                            <div class="w-36 sm:w-44 h-2 bg-slate-200 rounded-full overflow-hidden mt-1">
                                <div id="plan-progress-bar-{{ $plan->id }}" 
                                     class="h-full bg-emerald-600 rounded-full transition-all duration-300" 
                                     style="width: {{ $plan->progress_percentage }}%;"></div>
                            </div>
                        </div>

                        <form action="{{ route('therapy-plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الباقة؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition" title="حذف الباقة">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sessions Attendance Checklist Table with Devices -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-[11px]">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 text-[10px]">
                            <tr>
                                <th class="py-2 px-2.5 w-10 text-center">حضور</th>
                                <th class="py-2 px-2.5">رقم الجلسة</th>
                                <th class="py-2 px-2.5">تاريخ الجلسة</th>
                                <th class="py-2 px-2.5">وقت الحضور</th>
                                <th class="py-2 px-2.5">الأجهزة الطبية المستخدمة في الجلسة (Checkboxes)</th>
                                <th class="py-2 px-2.5">ملاحظات</th>
                                <th class="py-2 px-2.5 text-center">حفظ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($plan->sessions as $session)
                                @php $sessionDeviceIds = $session->devices->pluck('id')->toArray(); @endphp
                                <tr id="session-row-{{ $session->id }}" class="hover:bg-slate-50/80 transition {{ $session->is_attended ? 'bg-emerald-50/30' : '' }}">
                                    <!-- Interactive Checkbox -->
                                    <td class="py-2 px-2.5 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 transition cursor-pointer"
                                                   {{ $session->is_attended ? 'checked' : '' }}
                                                   onchange="toggleAttendanceAjax({{ $session->id }}, {{ $plan->id }}, this)">
                                        </label>
                                    </td>
                                    <td class="py-2 px-2.5 font-bold text-slate-800">
                                        جلسة #{{ $session->session_number }}
                                    </td>
                                    <td class="py-2 px-2.5">
                                        <form id="session-form-{{ $session->id }}" action="{{ route('therapy-sessions.update', $session) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="date" name="session_date" value="{{ $session->session_date ? $session->session_date->format('Y-m-d') : '' }}"
                                                   class="px-2 py-0.5 text-[11px] bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:bg-white focus:ring-1 focus:ring-brand-500">
                                    </td>
                                    <td class="py-2 px-2.5" id="session-attended-at-{{ $session->id }}">
                                        @if($session->is_attended)
                                            <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full font-semibold text-[10px]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                {{ $session->attended_at ? $session->attended_at->format('Y-m-d H:i') : 'تم الحضور' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[10px]">لم تحضر بعد</span>
                                        @endif
                                    </td>

                                    <!-- Medical Devices Checkboxes for this session -->
                                    <td class="py-2 px-2.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <!-- Currently active devices summary -->
                                            <div id="session-devices-badges-{{ $session->id }}" class="flex items-center gap-1 flex-wrap">
                                                @forelse($session->devices as $dev)
                                                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-bold">{{ $dev->name }}</span>
                                                @empty
                                                    <span class="text-slate-400 text-[10px]">لم تحدد أجهزة</span>
                                                @endforelse
                                            </div>

                                            <!-- Dropdown trigger to edit devices -->
                                            <button type="button" onclick="document.getElementById('devices-popover-{{ $session->id }}').classList.toggle('hidden')"
                                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[10px] font-bold transition">
                                                ⚙ تحديد الأجهزة
                                            </button>
                                        </div>

                                        <!-- Hidden / Toggleable Device Checkboxes Popover -->
                                        <div id="devices-popover-{{ $session->id }}" class="hidden mt-2 p-3 bg-white border border-slate-300 rounded-xl shadow-lg z-20 space-y-2 max-w-sm">
                                            <span class="text-[11px] font-bold text-slate-800 block">اختر الأجهزة المستخدمة في الجلسة #{{ $session->session_number }}:</span>
                                            <div class="grid grid-cols-1 gap-1 max-h-40 overflow-y-auto">
                                                @foreach($allDevices as $device)
                                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer text-[11px]">
                                                        <input type="checkbox" name="device_ids[]" value="{{ $device->id }}"
                                                               {{ in_array($device->id, $sessionDeviceIds) ? 'checked' : '' }}
                                                               class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                                        <span class="text-slate-700 font-medium">{{ $device->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="pt-2 border-t border-slate-100 flex justify-end">
                                                <button type="button" onclick="document.getElementById('devices-popover-{{ $session->id }}').classList.add('hidden')"
                                                        class="px-2.5 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded text-[10px]">
                                                    إغلاق
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-2 px-2.5">
                                        <input type="text" name="notes" value="{{ $session->notes }}" placeholder="ملاحظات..."
                                               class="w-full px-2 py-0.5 text-[11px] bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:bg-white focus:ring-1 focus:ring-brand-500">
                                    </td>
                                    <td class="py-2 px-2.5 text-center">
                                        <button type="submit" class="px-2.5 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold rounded-lg transition" title="حفظ التعديل">
                                            حفظ
                                        </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-400">
                لا توجد باقات علاج طبيعي مسجلة لهذا المريض حتى الآن
            </div>
        @endforelse
    </div>

    <!-- TAB 3 CONTENT: WEIGHT LOSS PACKAGES & SESSIONS & DEVICES -->
    <div id="tab-content-weight-loss" class="tab-pane {{ $activeTab === 'weight-loss' ? '' : 'hidden' }} space-y-6">

        <!-- Form: Create New Weight Loss Package (باقة تخسيس جديدة) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>إنشاء باقة تخسيس ونحت قوام جديدة للحالة (مقسمة جلسات)</span>
            </h2>

            <form action="{{ route('weight-loss-plans.store', $patient) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">اسم باقة التخسيس <span class="text-rose-500">*</span></label>
                    <input type="text" name="package_name" required placeholder="مثال: كورس النحت والتخسيس 8 جلسات"
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">عدد جلسات الباقة <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_sessions" value="8" min="1" max="50" required
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">سعر / تكلفة الباقة (ج.م) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="cost" required placeholder="مثال: 2400"
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 transition font-bold">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ البدء</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}"
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">نوع الحقن المعتمد للباقة</label>
                    <select name="default_injection" class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                        <option value="">-- متابعة أجهزة وتغذية بدون حقن --</option>
                        <option value="ساكسندا (Saxenda)">ساكسندا (Saxenda)</option>
                        <option value="أوزمبيك (Ozempic)">أوزمبيك (Ozempic)</option>
                        <option value="مونجارو (Mounjaro)">مونجارو (Mounjaro)</option>
                        <option value="ويغوفي (Wegovy)">ويغوفي (Wegovy)</option>
                        <option value="ميزوثيرابي موضعي (Mesotherapy)">ميزوثيرابي موضعي (Mesotherapy)</option>
                        <option value="حقن حوارق L-Carnitine">حقن حوارق L-Carnitine</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">ملاحظات توجيهية للحمية والأجهزة</label>
                    <input type="text" name="notes" placeholder="ملاحظات أجهزة الكافيتيشن وتوجيهات النظام الغذائي..."
                           class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full py-1.5 px-4 bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-bold rounded-xl shadow-xs transition">
                        حفظ وتوليد جلسات التخسيس
                    </button>
                </div>
            </form>
        </div>

        <!-- Weight Loss Packages Cards & Sessions Checklist -->
        @forelse($patient->weightLossPlans as $wlp)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <!-- Package Header -->
                <div class="p-4 border-b border-slate-200 bg-purple-50/40 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-slate-900">{{ $wlp->package_name }}</h3>
                            <span id="wlp-badge-status-{{ $wlp->id }}" class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $wlp->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $wlp->status_arabic }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2.5">
                            <span>تاريخ البدء: {{ $wlp->created_at->format('Y-m-d') }}</span>
                            <span>&bull;</span>
                            <span>التكلفة: <strong class="text-slate-900">{{ number_format($wlp->cost, 2) }} ج.م</strong></span>
                            @if($wlp->notes)
                                <span>&bull;</span>
                                <span class="text-slate-600">{{ $wlp->notes }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Counters -->
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-[11px] font-bold text-slate-700">
                                المنجز: <span id="wlp-completed-{{ $wlp->id }}" class="font-black text-purple-700">{{ $wlp->completed_sessions_count }}</span> من {{ $wlp->total_sessions }}
                                (متبقي <span id="wlp-remaining-{{ $wlp->id }}" class="font-black text-rose-600">{{ $wlp->remaining_sessions_count }}</span>)
                            </div>
                            <div class="w-36 sm:w-44 h-2 bg-slate-200 rounded-full overflow-hidden mt-1">
                                <div id="wlp-progress-bar-{{ $wlp->id }}" 
                                     class="h-full bg-purple-600 rounded-full transition-all duration-300" 
                                     style="width: {{ $wlp->progress_percentage }}%;"></div>
                            </div>
                        </div>

                        <form action="{{ route('weight-loss-plans.destroy', $wlp) }}" method="POST" onsubmit="return confirm('هل تريد حذف باقة التخسيس وجلساتها؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition" title="حذف الباقة">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sessions Table for this Weight Loss Plan -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-[11px]">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 text-[10px]">
                            <tr>
                                <th class="py-2 px-2.5 w-10 text-center">أتم الجلسة</th>
                                <th class="py-2 px-2.5">رقم الجلسة</th>
                                <th class="py-2 px-2.5">تاريخ الجلسة</th>
                                <th class="py-2 px-2.5">الوزن (كجم)</th>
                                <th class="py-2 px-2.5">مؤشر BMI</th>
                                <th class="py-2 px-2.5">نوع الحقن / الجرعة</th>
                                <th class="py-2 px-2.5">الأجهزة المستخدمة (Checkboxes)</th>
                                <th class="py-2 px-2.5">ملاحظات</th>
                                <th class="py-2 px-2.5 text-center">المرفق (InBody / صورة / PDF)</th>
                                <th class="py-2 px-2.5 text-center">حفظ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($wlp->sessions as $session)
                                @php $sessionDeviceIds = $session->devices->pluck('id')->toArray(); @endphp
                                <tr id="wls-row-{{ $session->id }}" class="hover:bg-slate-50/80 transition {{ $session->is_completed ? 'bg-purple-50/30' : '' }}">
                                    <!-- Checkbox Completed -->
                                    <td class="py-2 px-2.5 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500 transition cursor-pointer"
                                                   {{ $session->is_completed ? 'checked' : '' }}
                                                   onchange="toggleWeightPlanAjax({{ $session->id }}, {{ $wlp->id }}, this)">
                                        </label>
                                    </td>
                                    <td class="py-2 px-2.5 font-bold text-slate-800">
                                        جلسة #{{ $session->session_number }}
                                    </td>

                                    <!-- Edit form row -->
                                    <td class="py-2 px-2.5">
                                        <form id="wls-form-{{ $session->id }}" action="{{ route('weight-loss-sessions.update', $session) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <input type="date" name="session_date" form="wls-form-{{ $session->id }}" value="{{ $session->session_date ? $session->session_date->format('Y-m-d') : '' }}"
                                                   class="px-2 py-0.5 text-[11px] bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:bg-white focus:ring-1 focus:ring-purple-500">
                                    </td>

                                    <td class="py-2 px-2.5">
                                        <input type="number" step="0.1" name="weight" form="wls-form-{{ $session->id }}" value="{{ $session->weight }}" placeholder="الوزن"
                                               class="w-16 px-1.5 py-0.5 text-[11px] bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:bg-white focus:ring-1 focus:ring-purple-500 font-mono font-bold">
                                    </td>

                                    <td class="py-2 px-2.5">
                                        @if($session->bmi)
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 font-mono font-bold text-slate-800 text-[11px]">{{ $session->bmi }}</span>
                                            <span class="text-[9px] text-slate-500 block font-bold">{{ $session->bmi_category }}</span>
                                        @else
                                            <span class="text-slate-400 text-[10px]">-</span>
                                        @endif
                                    </td>

                                    <td class="py-2 px-2.5">
                                        <div class="space-y-1">
                                            <input type="text" name="injection_type" form="wls-form-{{ $session->id }}" value="{{ $session->injection_type }}" placeholder="نوع الحقن..."
                                                   class="w-28 px-1.5 py-0.5 text-[11px] bg-slate-50 border border-slate-200 rounded-lg">
                                            <input type="text" name="dosage_amount" form="wls-form-{{ $session->id }}" value="{{ $session->dosage_amount }}" placeholder="الجرعة (0.6 ملغ...)"
                                                   class="w-28 px-1.5 py-0.5 text-[11px] bg-slate-50 border border-slate-200 rounded-lg font-mono">
                                        </div>
                                    </td>

                                    <!-- Devices used in Weight Loss session -->
                                    <td class="py-2 px-2.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <div id="wls-devices-badges-{{ $session->id }}" class="flex items-center gap-1 flex-wrap">
                                                @forelse($session->devices as $dev)
                                                    <span class="px-1.5 py-0.5 bg-purple-100 text-purple-900 rounded text-[10px] font-bold">{{ $dev->name }}</span>
                                                @empty
                                                    <span class="text-slate-400 text-[10px]">بدون أجهزة</span>
                                                @endforelse
                                            </div>

                                            <button type="button" onclick="document.getElementById('wls-devices-popover-{{ $session->id }}').classList.toggle('hidden')"
                                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[10px] font-bold transition">
                                                ⚙ الأجهزة
                                            </button>
                                        </div>

                                        <div id="wls-devices-popover-{{ $session->id }}" class="hidden mt-2 p-3 bg-white border border-slate-300 rounded-xl shadow-lg z-20 space-y-2 max-w-sm">
                                            <span class="text-[11px] font-bold text-slate-800 block">أجهزة جلسة التخسيس #{{ $session->session_number }}:</span>
                                            <div class="grid grid-cols-1 gap-1 max-h-40 overflow-y-auto">
                                                @foreach($allDevices as $device)
                                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer text-[11px]">
                                                        <input type="checkbox" name="device_ids[]" form="wls-form-{{ $session->id }}" value="{{ $device->id }}"
                                                               {{ in_array($device->id, $sessionDeviceIds) ? 'checked' : '' }}
                                                               class="w-3.5 h-3.5 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                                        <span class="text-slate-700 font-medium">{{ $device->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="pt-2 border-t border-slate-100 flex justify-end">
                                                <button type="button" onclick="document.getElementById('wls-devices-popover-{{ $session->id }}').classList.add('hidden')"
                                                        class="px-2.5 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded text-[10px]">
                                                    إغلاق
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-2 px-2.5">
                                        <input type="text" name="doctor_notes" form="wls-form-{{ $session->id }}" value="{{ $session->doctor_notes }}" placeholder="ملاحظات..."
                                               class="w-full px-2 py-0.5 text-[11px] bg-slate-50 border border-slate-200 rounded-lg">
                                    </td>

                                    <!-- Attachment (InBody / Image / PDF) with Auto-Compression -->
                                    <td class="py-2 px-2.5 text-center" id="wls-attachment-cell-{{ $session->id }}">
                                        @if($session->has_attachment)
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                @if($session->is_attachment_image)
                                                    <button type="button" 
                                                            onclick="openPreviewModal('{{ $session->view_url }}', 'مرفق جلسة تخسيس #{{ $session->session_number }} - {{ addslashes($patient->name) }}', 'image', '{{ $session->session_date ? $session->session_date->format('Y-m-d') : '' }}', '{{ addslashes($session->attachment_name) }}', '{{ $session->download_url }}')"
                                                            class="group relative block w-8 h-8 rounded-lg overflow-hidden border border-purple-200 hover:border-purple-500 shadow-2xs transition"
                                                            title="معاينة الصورة المكبرة">
                                                        <img src="{{ $session->view_url }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-150" alt="مرفق الجلسة">
                                                        <span class="absolute inset-0 bg-purple-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition text-[9px] font-bold">🔍</span>
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            onclick="openPreviewModal('{{ $session->view_url }}', 'مستند PDF جلسة #{{ $session->session_number }} - {{ addslashes($patient->name) }}', 'pdf', '{{ $session->session_date ? $session->session_date->format('Y-m-d') : '' }}', '{{ addslashes($session->attachment_name) }}', '{{ $session->download_url }}')"
                                                            class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shadow-2xs hover:bg-rose-100 transition"
                                                            title="استعراض مستند PDF">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                    </button>
                                                @endif

                                                <div class="flex flex-col items-start text-[9px] leading-tight text-right">
                                                    <span class="font-mono text-slate-800 font-bold truncate max-w-[70px]" title="{{ $session->attachment_name }}">{{ $session->attachment_name }}</span>
                                                    <div class="flex items-center gap-1 mt-0.5">
                                                        <span class="font-mono text-slate-500">{{ $session->formatted_attachment_size }}</span>
                                                        @if($session->compression_savings_percentage)
                                                            <span class="px-1 py-0.2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded font-mono font-black" title="تم ضغط الحجم وتوفير مساحة من {{ $session->formatted_original_size }}">
                                                                وفر {{ $session->compression_savings_percentage }}%
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-0.5">
                                                    <a href="{{ $session->download_url }}" class="p-1 text-slate-400 hover:text-purple-600 rounded transition" title="تحميل">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                    </a>
                                                    <button type="button" onclick="deleteSessionAttachment({{ $session->id }})" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="حذف المرفق">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center gap-1">
                                                <label class="cursor-pointer inline-flex items-center gap-1 px-2 py-1 bg-purple-50/80 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-[10px] font-bold transition">
                                                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                    <span id="wls-file-label-{{ $session->id }}">إرفاق ملف</span>
                                                    <input type="file" 
                                                           id="wls-file-input-{{ $session->id }}" 
                                                           name="attachment" 
                                                           form="wls-form-{{ $session->id }}" 
                                                           accept="image/*,application/pdf" 
                                                           class="hidden" 
                                                           onchange="handleSessionFileSelected(this, {{ $session->id }})">
                                                </label>
                                                <span id="wls-file-info-{{ $session->id }}" class="hidden text-[9px] text-purple-700 font-bold max-w-[90px] truncate"></span>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-2 px-2.5 text-center">
                                        <button type="submit" form="wls-form-{{ $session->id }}" id="wls-submit-{{ $session->id }}" class="px-2.5 py-0.5 bg-purple-50 hover:bg-purple-600 hover:text-white text-purple-700 font-bold text-[10px] rounded-lg transition">
                                            حفظ
                                        </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-400">
                لا توجد باقات تخسيس مقسمة مسجلة لهذه الحالة حتى الآن. يمكنك إنشاء باقة من النموذج أعلاه.
            </div>
        @endforelse

        <!-- Standalone Sessions (if any) -->
        @php
            $standaloneSessions = $patient->weightLossSessions->whereNull('weight_loss_plan_id');
        @endphp
        @if($standaloneSessions->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-[11px] font-bold text-slate-700">جلسات وقياسات منفردة (بدون باقة)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-[11px]">
                        <thead class="bg-slate-50 text-slate-500 font-bold text-[10px]">
                            <tr>
                                <th class="py-2 px-2.5">التاريخ</th>
                                <th class="py-2 px-2.5">الوزن / BMI</th>
                                <th class="py-2 px-2.5">الحقن والجرعة</th>
                                <th class="py-2 px-2.5">الأجهزة</th>
                                <th class="py-2 px-2.5">الحالة</th>
                                <th class="py-2 px-2.5 text-center">المرفق</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($standaloneSessions as $ss)
                                <tr>
                                    <td class="py-1.5 px-2.5 font-mono font-bold">{{ $ss->session_date->format('Y-m-d') }}</td>
                                    <td class="py-1.5 px-2.5 font-mono">{{ $ss->weight ? $ss->weight . ' كجم' : '-' }}</td>
                                    <td class="py-1.5 px-2.5">{{ $ss->injection_type ?: '-' }}</td>
                                    <td class="py-1.5 px-2.5">
                                        @foreach($ss->devices as $dev)
                                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 rounded text-[10px]">{{ $dev->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="py-1.5 px-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss->is_completed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $ss->is_completed ? 'تمت' : 'معلقة' }}
                                        </span>
                                    </td>
                                    <td class="py-1.5 px-2.5 text-center">
                                        @if($ss->has_attachment)
                                            <button type="button" 
                                                    onclick="openPreviewModal('{{ $ss->view_url }}', 'مرفق جلسة منفردة - {{ addslashes($patient->name) }}', '{{ $ss->attachment_type }}', '{{ $ss->session_date->format('Y-m-d') }}', '{{ addslashes($ss->attachment_name) }}', '{{ $ss->download_url }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 rounded text-[10px] font-bold transition">
                                                <span>{{ $ss->is_attachment_image ? '🖼️ صورة' : '📄 PDF' }}</span>
                                                <span class="font-mono text-[9px] text-slate-500">({{ $ss->formatted_attachment_size }})</span>
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-[10px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <!-- TAB 4 CONTENT: FINANCIAL LEDGER & PAYMENTS -->
    <div id="tab-content-financial" class="tab-pane {{ $activeTab === 'financial' ? '' : 'hidden' }} space-y-6">

        <!-- Record Payment Form -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>تسجيل دفعة نقدية / سند تحصيل مع طباعة إيصال حراري (80mm)</span>
                </h2>
                <div class="text-xs">
                    <span class="text-slate-500">المتبقي المطلوب سداده: </span>
                    <strong class="font-black {{ $patient->remaining_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($patient->remaining_balance, 2) }} ج.م</strong>
                </div>
            </div>

            <form action="{{ route('payments.store', $patient) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ المدفوع (ج.م) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="amount" id="payment-amount" value="{{ $patient->remaining_balance > 0 ? $patient->remaining_balance : '' }}" required placeholder="مثال: 500"
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 transition font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ السداد <span class="text-rose-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">طريقة الدفع <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="cash" selected>نقداً (كاش)</option>
                        <option value="vodafone_cash">فودافون كاش / محفظة ذكية</option>
                        <option value="visa">بطاقة بنكية / فيزا</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ربط السداد بباقة محددة (اختياري)</label>
                    <select name="target_plan" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="">-- سداد عام للحساب --</option>
                        @if($patient->therapyPlans->isNotEmpty())
                            <optgroup label="باقات العلاج الطبيعي">
                                @foreach($patient->therapyPlans as $tp)
                                    <option value="therapy_{{ $tp->id }}">{{ $tp->package_name }} ({{ number_format($tp->cost, 2) }} ج.م)</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if($patient->weightLossPlans->isNotEmpty())
                            <optgroup label="باقات التخسيس ونحت القوام">
                                @foreach($patient->weightLossPlans as $wp)
                                    <option value="weight_loss_{{ $wp->id }}">{{ $wp->package_name }} ({{ number_format($wp->cost, 2) }} ج.م)</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <input type="text" name="notes" placeholder="ملاحظات السند (مثال: دفعة أولى من باقة التخسيس، سداد نقدي بالعيادة...)"
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>حفظ وطباعة الإيصال الحراري</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Payments Log Table with Direct 80mm Print Button -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">سجل سندات القبض والتحصيلات</h3>
                <a href="{{ route('patients.statement', $patient) }}" target="_blank" class="text-xs font-bold text-brand-700 hover:text-brand-800 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>عرض وطباعة كشف الحساب الكامل A4</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">رقم الإيصال</th>
                            <th class="py-3 px-4">تاريخ السداد</th>
                            <th class="py-3 px-4">المبلغ المسدد</th>
                            <th class="py-3 px-4">طريقة الدفع</th>
                            <th class="py-3 px-4">الخدمة المرتبطة</th>
                            <th class="py-3 px-4">المحصل</th>
                            <th class="py-3 px-4">ملاحظات</th>
                            <th class="py-3 px-4 text-center">طباعة إيصال حراري (80mm)</th>
                            <th class="py-3 px-4 text-center">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($patient->payments as $payment)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                    {{ $payment->receipt_number }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    {{ $payment->payment_date->format('Y-m-d') }}
                                </td>
                                <td class="py-3 px-4 font-mono font-black text-emerald-700 text-sm">
                                    {{ number_format($payment->amount, 2) }} ج.م
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    {{ $payment->payment_method_arabic }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    @if($payment->therapyPlan)
                                        {{ $payment->therapyPlan->package_name }}
                                    @elseif($payment->weightLossPlan)
                                        {{ $payment->weightLossPlan->package_name }}
                                    @elseif($payment->visit)
                                        كشف طبي
                                    @else
                                        سداد من الحساب العام
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-700">
                                    {{ $payment->creator ? $payment->creator->name : 'الاستقبال' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $payment->notes ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('payments.receipt', $payment->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 text-xs font-bold rounded-lg transition border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>طباعة إيصال حراري</span>
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <form action="{{ route('payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا السند؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition" title="حذف السند">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    لا توجد مدفوعات مسجلة لهذا المريض حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 5 CONTENT: PATIENT ATTACHMENTS & MEDICAL DOCUMENTS -->
    <div id="tab-content-attachments" class="tab-pane {{ $activeTab === 'attachments' ? '' : 'hidden' }} space-y-6">
        
        <!-- Form: Upload New Attachment -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </div>
                <span>إضافة ورفع مرفق طبي جديد للحالة (أشعة، تحاليل، تقرير، صور، PDF)</span>
            </h2>

            <form action="{{ route('patients.attachments.store', $patient) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">عنوان / وصف المرفق الطبي <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="مثال: أشعة رنين مغناطيسي MRI على الفقرات، تحليل دهون ثلاثية..."
                               class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500 transition font-bold">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تصنيف المرفق الطبي <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500 transition font-bold">
                            <option value="xray" selected>أشعة وفحوصات تصويرية 🩻</option>
                            <option value="lab">تحاليل مخبرية وفحوصات دم 🧪</option>
                            <option value="report">تقرير طبي واستشاري 📄</option>
                            <option value="photo">صور الحالة ومتابعة القوام 📷</option>
                            <option value="prescription">روشتة علاجية سابقة 💊</option>
                            <option value="general">مستندات وملفات عامة 📁</option>
                        </select>
                    </div>

                    <!-- Document Date -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ الفحص / المرفق <span class="text-rose-500">*</span></label>
                        <input type="date" name="document_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500 transition font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- File Input -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">ملف الصورة أو الـ PDF <span class="text-rose-500">*</span> <span class="text-[10px] text-slate-400 font-normal">(صور JPG, PNG, WEBP أو ملفات PDF حتى 25 ميجابايت)</span></label>
                        <input type="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf"
                               class="w-full px-2.5 py-1 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500 transition file:mr-0 file:ml-2.5 file:py-0.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-teal-600 file:text-white hover:file:bg-teal-700 file:cursor-pointer">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">ملاحظات الطبيب على نتيجة الفحص أو المرفق (اختياري)</label>
                        <input type="text" name="notes" placeholder="مثال: يوضح وجود ضغط طفيف بين الفقرة الرابعة والخامسة، نتائج التحاليل سليمة..."
                               class="w-full px-2.5 py-1.5 text-[11px] bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500 transition">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-1">
                    <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>رفع وحفظ المرفق الطبي</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Uploaded Documents Gallery & Viewer -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-5">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>سجل المرفقات والأشعة والتقارير الطبية ({{ $patient->attachments->count() }} مرفق)</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">يمكنك استعراض أي صورة أو ملف PDF مباشرة بضغطة زر دون مغادرة الصفحة.</p>
                </div>
            </div>

            @if($patient->attachments->isEmpty())
                <div class="py-10 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-500 flex items-center justify-center mx-auto mb-2.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800">لا توجد مرفقات أو أشعة مسجلة لهذا المريض بعد</h4>
                    <p class="text-[11px] text-slate-500 mt-1 max-w-sm mx-auto">
                        استخدم النموذج أعلاه لرفع صور الأشعة، نتائج التحاليل الطبية، أو ملفات الـ PDF مع تحديد تاريخ الفحص.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($patient->attachments as $att)
                        <div class="group bg-slate-50 hover:bg-white rounded-2xl border border-slate-200 hover:border-teal-400 shadow-xs hover:shadow-md transition duration-200 flex flex-col overflow-hidden">
                            <!-- Card Header: Category & Date -->
                            <div class="p-2.5 bg-white border-b border-slate-100 flex items-center justify-between gap-2">
                                <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                    {{ $att->category_name }}
                                </span>
                                <span class="text-[10px] font-mono font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded-md" title="تاريخ المرفق / الفحص الطبي">
                                    📅 {{ $att->document_date->format('Y-m-d') }}
                                </span>
                            </div>

                            <!-- Preview Thumbnail Area -->
                            <div class="relative bg-slate-100 h-40 flex items-center justify-center overflow-hidden cursor-pointer"
                                 onclick="openPreviewModal('{{ $att->view_url }}', '{{ addslashes($att->title) }}', '{{ $att->file_type }}', '{{ $att->document_date->format('Y-m-d') }}', '{{ addslashes($att->original_name) }}', '{{ $att->download_url }}')">
                                @if($att->is_image)
                                    <img src="{{ $att->view_url }}" alt="{{ $att->title }}" loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-slate-950/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                        <span class="px-2.5 py-1 bg-white/90 backdrop-blur-xs text-slate-900 rounded-xl text-[11px] font-bold shadow-sm flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>معاينة وتكبير</span>
                                        </span>
                                    </div>
                                @else
                                    <!-- PDF Thumbnail Layout -->
                                    <div class="flex flex-col items-center justify-center p-3 text-center">
                                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shadow-xs mb-1.5 group-hover:scale-110 transition duration-200">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="text-[10px] font-black text-rose-700 uppercase tracking-wide">مستند PDF</span>
                                        <span class="text-[9px] text-slate-500 font-mono mt-0.5">{{ $att->formatted_size }}</span>
                                    </div>
                                    <div class="absolute inset-0 bg-slate-950/20 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <span class="px-2.5 py-1 bg-white/95 backdrop-blur-xs text-slate-900 rounded-xl text-[11px] font-bold shadow-sm flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>استعراض الـ PDF</span>
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Details -->
                            <div class="p-3 flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-[11px] font-black text-slate-900 line-clamp-1" title="{{ $att->title }}">
                                        {{ $att->title }}
                                    </h4>
                                    @if($att->notes)
                                        <p class="text-[10px] text-slate-500 mt-1 line-clamp-2 leading-relaxed bg-slate-100/60 p-1.5 rounded-lg border border-slate-100" title="{{ $att->notes }}">
                                            {{ $att->notes }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-2.5 pt-2 border-t border-slate-100 text-[10px] text-slate-400 flex items-center justify-between">
                                    <span class="truncate max-w-[110px]" title="تم الرفع بواسطة: {{ $att->creator?->name ?? 'النظام' }}">
                                        بواسطة: {{ $att->creator?->name ?? 'النظام' }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <span class="font-mono text-[10px] text-slate-600 font-bold">{{ $att->formatted_size }}</span>
                                        @if($att->compression_savings_percentage)
                                            <span class="px-1.5 py-0.2 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-extrabold font-mono" title="تم ضغط الحجم وتوفير مساحة من {{ $att->formatted_original_size }}">
                                                وفر {{ $att->compression_savings_percentage }}%
                                            </span>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <!-- Card Action Buttons -->
                            <div class="p-2 bg-white border-t border-slate-100 grid grid-cols-4 gap-1">
                                <!-- Preview Button -->
                                <button type="button" 
                                        onclick="openPreviewModal('{{ $att->view_url }}', '{{ addslashes($att->title) }}', '{{ $att->file_type }}', '{{ $att->document_date->format('Y-m-d') }}', '{{ addslashes($att->original_name) }}', '{{ $att->download_url }}')"
                                        class="col-span-2 py-1 px-1.5 bg-teal-50 hover:bg-teal-600 hover:text-white text-teal-800 text-[10px] font-bold rounded-lg transition flex items-center justify-center gap-1"
                                        title="معاينة واستعراض">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>استعراض</span>
                                </button>

                                <!-- Direct Download Link -->
                                <a href="{{ $att->download_url }}" 
                                   class="py-1 px-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold rounded-lg transition flex items-center justify-center"
                                   title="تحميل الملف للكمبيوتر">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>

                                <!-- Delete Form -->
                                <form action="{{ route('patients.attachments.destroy', ['patient' => $patient->id, 'attachment' => $att->id]) }}" 
                                      method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المرفق الطبي نهائياً؟');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full py-1 px-1.5 bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 text-[10px] font-bold rounded-lg transition flex items-center justify-center"
                                            title="حذف المرفق">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>

<!-- Interactive Lightbox & PDF Viewer Modal -->
<div id="attachment-preview-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-6" onclick="handleModalBackdropClick(event)">
    <div class="bg-white w-full max-w-5xl max-h-[95vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200" onclick="event.stopPropagation()">
        <!-- Modal Top Bar -->
        <div class="p-4 bg-slate-900 text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div id="modal-type-icon" class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div class="overflow-hidden">
                    <h3 id="modal-doc-title" class="text-sm font-black truncate text-white">معاينة المرفق</h3>
                    <p id="modal-doc-meta" class="text-[11px] text-slate-400 font-mono">بتاريخ: ---</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Open in new tab -->
                <a id="modal-newtab-btn" href="#" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5" title="فتح بنافذة كاملة">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span class="hidden sm:inline">نافذة كاملة</span>
                </a>
                <!-- Direct download -->
                <a id="modal-download-btn" href="#" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold transition flex items-center gap-1.5" title="تحميل">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="hidden sm:inline">تحميل</span>
                </a>
                <!-- Close button -->
                <button type="button" onclick="closePreviewModal()" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Modal Media Content Body -->
        <div class="p-3 bg-slate-950 flex-1 overflow-auto flex items-center justify-center min-h-[50vh] max-h-[78vh]">
            <!-- Image Container -->
            <div id="modal-image-container" class="hidden max-h-full max-w-full flex items-center justify-center">
                <img id="modal-image-elem" src="" alt="معاينة" class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-md">
            </div>

            <!-- PDF Container -->
            <div id="modal-pdf-container" class="hidden w-full h-[75vh]">
                <iframe id="modal-pdf-iframe" src="" class="w-full h-full rounded-xl border-0 bg-white"></iframe>
            </div>
        </div>

        <!-- Modal Footer Info -->
        <div class="p-3 bg-slate-100 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
            <span id="modal-doc-filename" class="font-mono text-[11px] truncate max-w-md">---</span>
            <button type="button" onclick="closePreviewModal()" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-bold transition">
                إغلاق (Esc)
            </button>
        </div>
    </div>
</div>

<!-- Interactive WhatsApp Messaging Modal (No Meta API required) -->
<div id="whatsapp-modal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-6" onclick="handleWhatsappBackdropClick(event)">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden border border-slate-200" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="p-4 bg-emerald-600 text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0 text-white">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black">إرسال رسالة واتساب WhatsApp</h3>
                    <p class="text-[11px] text-emerald-100">المريض: {{ $patient->name }} &bull; الهاتف: <span class="font-mono font-bold">{{ $patient->phone }}</span></p>
                </div>
            </div>
            <button type="button" onclick="closeWhatsappModal()" class="p-1 text-emerald-100 hover:text-white rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">
            <!-- Recipient Phone check -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">رقم الواتساب المستلم (مع كود الدولة تلقائياً):</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="wa-recipient-phone" value="{{ $patient->whatsapp_phone }}" 
                           class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <span class="text-[10px] text-slate-400 whitespace-nowrap">مثال: 2010...</span>
                </div>
            </div>

            <!-- Patient Portal Link Quick Preview & Copy -->
            <div class="p-2.5 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-center justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-900 mb-0.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span>رابط البوابة الرقمية الخاص بالمريض:</span>
                    </div>
                    <div class="text-[10px] font-mono text-emerald-700 truncate dir-ltr select-all">{{ $patient->portal_url }}</div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" onclick="copyPatientPortalUrl()" class="px-2 py-1 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-[10px] font-bold transition shadow-2xs flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>نسخ الرابط</span>
                    </button>
                    <a href="{{ $patient->portal_url }}" target="_blank" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold transition shadow-2xs flex items-center gap-1" title="معاينة البوابة">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>فتح</span>
                    </a>
                </div>
            </div>

            <!-- Quick Templates Buttons -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1.5">اختر قالب رسالة جاهز أو اكتب رسالتك مباشرة:</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                    <button type="button" onclick="applyWhatsappTemplate('portal')" 
                            class="px-2 py-1.5 text-[11px] font-bold rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition text-right sm:col-span-3 flex items-center justify-between">
                        <span>🌐 إرسال رابط البوابة الرقمية ومتابعة الحساب والجلسات</span>
                        <span class="text-[9px] bg-emerald-200 text-emerald-900 px-1.5 py-0.5 rounded font-bold">موصى به</span>
                    </button>
                    <button type="button" onclick="applyWhatsappTemplate('appointment')" 
                            class="px-2 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-right">
                        📅 تذكير بالموعد
                    </button>
                    <button type="button" onclick="applyWhatsappTemplate('balance')" 
                            class="px-2 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-right">
                        💰 كشف الحساب والمتبقي
                    </button>
                    <button type="button" onclick="applyWhatsappTemplate('after_session')" 
                            class="px-2 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-right">
                        🩺 تعليمات بعد الجلسة
                    </button>
                    <button type="button" onclick="applyWhatsappTemplate('diet')" 
                            class="px-2 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-right">
                        🥗 نصائح التخسيس
                    </button>
                    <button type="button" onclick="applyWhatsappTemplate('welcome')" 
                            class="px-2 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-right">
                        🎉 ترحيب وتسجيل
                    </button>
                    <button type="button" onclick="applyWhatsappTemplate('custom')" 
                            class="px-2 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition text-right">
                        ✍ رسالة فارغة
                    </button>
                </div>
            </div>

            <!-- Message Textarea -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11px] font-bold text-slate-700">نص الرسالة:</label>
                    <button type="button" onclick="copyWhatsappText()" class="text-[10px] text-emerald-600 hover:underline font-bold">
                        📋 نسخ النص
                    </button>
                </div>
                <textarea id="wa-message-text" rows="5" placeholder="اكتب نص الرسالة هنا..."
                          class="w-full p-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500 leading-relaxed"></textarea>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span class="text-[10px] text-slate-400">بدون اشتراك Meta API • يعمل مجاناً عبر واتساب الرسمي</span>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="button" onclick="sendViaWhatsapp('app')" 
                        class="flex-1 sm:flex-none px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>فتح التطبيق (wa.me)</span>
                </button>
                <button type="button" onclick="sendViaWhatsapp('web')" 
                        class="flex-1 sm:flex-none px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>واتساب ويب (كمبيوتر)</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tab switcher with URL update
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-brand-600', 'text-brand-700', 'bg-brand-50/40', 'rounded-t-xl');
            btn.classList.add('border-transparent', 'text-slate-500');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        const activeBtn = document.getElementById(`tab-btn-${tabId}`);
        const activePane = document.getElementById(`tab-content-${tabId}`);
        if (activeBtn && activePane) {
            activeBtn.classList.remove('border-transparent', 'text-slate-500');
            activeBtn.classList.add('border-brand-600', 'text-brand-700', 'bg-brand-50/40', 'rounded-t-xl');
            activePane.classList.remove('hidden');
        }

        // Update URL query string without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    // Reactive AJAX Attendance Toggle for Physical Therapy
    async function toggleAttendanceAjax(sessionId, planId, checkboxElem) {
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

                const row = document.getElementById(`session-row-${sessionId}`);
                const timeTd = document.getElementById(`session-attended-at-${sessionId}`);
                if (data.is_attended) {
                    row.classList.add('bg-emerald-50/30');
                    timeTd.innerHTML = `
                        <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full font-semibold text-[11px]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            ${data.attended_at || 'تم الحضور'}
                        </span>
                    `;
                } else {
                    row.classList.remove('bg-emerald-50/30');
                    timeTd.innerHTML = `<span class="text-slate-400 text-[11px]">لم تحضر بعد</span>`;
                }

                // Update plan counters
                const completedElem = document.getElementById(`plan-completed-${planId}`);
                const remainingElem = document.getElementById(`plan-remaining-${planId}`);
                const progressBar = document.getElementById(`plan-progress-bar-${planId}`);
                const badgeStatus = document.getElementById(`plan-badge-status-${planId}`);

                if (completedElem) completedElem.innerText = data.completed_count;
                if (remainingElem) remainingElem.innerText = data.remaining_count;
                if (progressBar) progressBar.style.width = `${data.progress_percentage}%`;
                if (badgeStatus) {
                    badgeStatus.innerText = data.plan_status_arabic;
                    badgeStatus.className = `text-xs font-bold px-2.5 py-0.5 rounded-full ${data.plan_status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'}`;
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

    // Reactive AJAX Toggle for Weight Loss Plan Sessions
    async function toggleWeightPlanAjax(sessionId, planId, checkboxElem) {
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
                const row = document.getElementById(`wls-row-${sessionId}`);
                if (data.is_completed) {
                    row.classList.add('bg-purple-50/30');
                } else {
                    row.classList.remove('bg-purple-50/30');
                }

                const completedElem = document.getElementById(`wlp-completed-${planId}`);
                const remainingElem = document.getElementById(`wlp-remaining-${planId}`);
                const progressBar = document.getElementById(`wlp-progress-bar-${planId}`);
                const badgeStatus = document.getElementById(`wlp-badge-status-${planId}`);

                if (completedElem) completedElem.innerText = data.completed_count;
                if (remainingElem) remainingElem.innerText = data.remaining_count;
                if (progressBar) progressBar.style.width = `${data.progress_percentage}%`;
                if (badgeStatus) {
                    badgeStatus.innerText = data.plan_status_arabic;
                    badgeStatus.className = `text-xs font-bold px-2.5 py-0.5 rounded-full ${data.plan_status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800'}`;
                }
            } else {
                checkboxElem.checked = !checkboxElem.checked;
                showToast('حدث خطأ أثناء تحديث حالة الجلسة.', 'error');
            }
        } catch (e) {
            checkboxElem.checked = !checkboxElem.checked;
            showToast('تعذر الاتصال بالخادم.', 'error');
        } finally {
            checkboxElem.disabled = false;
        }
    }

    // Attachment Lightbox & PDF Viewer Modal Functions
    function openPreviewModal(url, title, type, date, originalName, downloadUrl) {
        const modal = document.getElementById('attachment-preview-modal');
        const modalTitle = document.getElementById('modal-doc-title');
        const modalMeta = document.getElementById('modal-doc-meta');
        const modalFilename = document.getElementById('modal-doc-filename');
        const modalNewtab = document.getElementById('modal-newtab-btn');
        const modalDownload = document.getElementById('modal-download-btn');
        const imgContainer = document.getElementById('modal-image-container');
        const imgElem = document.getElementById('modal-image-elem');
        const pdfContainer = document.getElementById('modal-pdf-container');
        const pdfIframe = document.getElementById('modal-pdf-iframe');
        const typeIcon = document.getElementById('modal-type-icon');

        if (modalTitle) modalTitle.innerText = title;
        if (modalMeta) modalMeta.innerText = `تاريخ الفحص والمرفق: ${date}`;
        if (modalFilename) modalFilename.innerText = `الملف: ${originalName}`;
        if (modalNewtab) modalNewtab.href = url;
        if (modalDownload) modalDownload.href = downloadUrl;

        if (type === 'pdf') {
            if (imgContainer) imgContainer.classList.add('hidden');
            if (imgElem) imgElem.src = '';
            if (pdfContainer) pdfContainer.classList.remove('hidden');
            if (pdfIframe) pdfIframe.src = url;
            if (typeIcon) {
                typeIcon.className = 'w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0';
                typeIcon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>`;
            }
        } else {
            if (pdfContainer) pdfContainer.classList.add('hidden');
            if (pdfIframe) pdfIframe.src = '';
            if (imgContainer) imgContainer.classList.remove('hidden');
            if (imgElem) imgElem.src = url;
            if (typeIcon) {
                typeIcon.className = 'w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center flex-shrink-0';
                typeIcon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
            }
        }

        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closePreviewModal() {
        const modal = document.getElementById('attachment-preview-modal');
        const pdfIframe = document.getElementById('modal-pdf-iframe');
        const imgElem = document.getElementById('modal-image-elem');

        if (pdfIframe) pdfIframe.src = '';
        if (imgElem) imgElem.src = '';
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function handleModalBackdropClick(event) {
        if (event.target.id === 'attachment-preview-modal') {
            closePreviewModal();
        }
    }

    // Keyboard support: Escape to close modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePreviewModal();
            closeWhatsappModal();
        }
    });

    // WhatsApp Direct Messaging (No Meta API required)
    const clinicName = "{{ addslashes($clinicSetting->clinic_name ?? 'العيادة') }}";
    const patientName = "{{ addslashes($patient->name) }}";
    const patientId = "{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}";
    const patientRemaining = "{{ number_format($patient->remaining_balance, 2) }}";
    const patientPortalUrl = "{{ $patient->portal_url }}";

    const waTemplates = {
        portal: `مرحباً أستاذ/ة ${patientName}،\nيسعدنا دائماً خدمتك في ${clinicName}.\nيمكنك متابعة جدول جلساتك ومواعيدك ومتبقي حسابك المالي مباشرة عبر رابط ملفك الخاص:\n${patientPortalUrl}\n\nمع تمنياتنا لك بدوام الصحة والعافية.`,
        appointment: `مرحباً أستاذ/ة ${patientName}،\nنود تذكيرك بموعدك القادم في ${clinicName}.\nيسعدنا تشريفك ونتمنى لك دوام الصحة والعافية.`,
        balance: `مرحباً أستاذ/ة ${patientName}،\nنحيطك علماً بأن إجمالي الحساب المتبقي هو ${patientRemaining} ج.م.\nشاكرين لحسن تعاونك معنا - ${clinicName}.`,
        after_session: `مرحباً أستاذ/ة ${patientName}،\nنتمنى أن تكون بصحة جيدة بعد جلستك اليوم في ${clinicName}.\nيرجى شرب كميات كافية من الماء والالتزام بالراحة والتعليمات المقررة.\nمع تمنياتنا لك بالشفاء العاجل.`,
        diet: `مرحباً أستاذ/ة ${patientName}،\nتذكير من ${clinicName} بأهمية الالتزام بالنظام الغذائي المتفق عليه، وشرب الماء بوفرة يومياً والمشي لمدة 30 دقيقة.\nنتمنى لك دوام الرشاقة والصحة.`,
        welcome: `أهلاً بك أستاذ/ة ${patientName} في ${clinicName}،\nتم فتح ملفك الطبي برقم #${patientId} بنجاح.\nنتمنى لك رحلة علاجية موفقة وشفاءً تاماً بإذن الله.`,
        custom: ''
    };

    function openWhatsappModal(defaultTemplate = 'appointment') {
        const modal = document.getElementById('whatsapp-modal');
        if (modal) {
            applyWhatsappTemplate(defaultTemplate);
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeWhatsappModal() {
        const modal = document.getElementById('whatsapp-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function handleWhatsappBackdropClick(event) {
        if (event.target.id === 'whatsapp-modal') {
            closeWhatsappModal();
        }
    }

    function applyWhatsappTemplate(type) {
        const textarea = document.getElementById('wa-message-text');
        if (textarea && waTemplates[type] !== undefined) {
            textarea.value = waTemplates[type];
            textarea.focus();
        }
    }

    function sendViaWhatsapp(mode) {
        const phoneInput = document.getElementById('wa-recipient-phone');
        const textInput = document.getElementById('wa-message-text');
        
        let phone = (phoneInput ? phoneInput.value : '').replace(/[^0-9]/g, '');
        const text = textInput ? textInput.value.trim() : '';

        if (!phone) {
            alert('يرجى التأكد من كتابة رقم الهاتف بشكل صحيح.');
            return;
        }

        const encodedText = encodeURIComponent(text);
        let url = '';

        if (mode === 'web') {
            url = `https://web.whatsapp.com/send?phone=${phone}&text=${encodedText}`;
        } else {
            url = `https://wa.me/${phone}?text=${encodedText}`;
        }

        window.open(url, '_blank');
    }

    function copyWhatsappText() {
        const textarea = document.getElementById('wa-message-text');
        if (textarea && textarea.value) {
            navigator.clipboard.writeText(textarea.value).then(() => {
                showToast('تم نسخ نص الرسالة بنجاح!', 'success');
            }).catch(() => {
                textarea.select();
                document.execCommand('copy');
                showToast('تم نسخ نص الرسالة!', 'success');
            });
        }
    }

    function copyPatientPortalUrl() {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(patientPortalUrl).then(() => {
                showToast('تم نسخ رابط بوابة المريض بنجاح!', 'success');
            }).catch(() => {
                prompt('انسخ رابط بوابة المريض:', patientPortalUrl);
            });
        } else {
            prompt('انسخ رابط بوابة المريض:', patientPortalUrl);
        }
    }

    // Weight Loss Session Attachment File Size Check & Management
    function handleSessionFileSelected(input, sessionId) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        const maxSizeBytes = 20 * 1024 * 1024; // 20 MB

        // 1. فحص حجم الملف (Client-side size check)
        if (file.size > maxSizeBytes) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
            alert(`⚠️ تنبيه: حجم الملف المحدد (${sizeMB} ميجابايت) يتجاوز الحد الأقصى المسموح به (20 ميجابايت).\nيرجى اختيار ملف أصغر حجماً.`);
            input.value = '';
            const labelElem = document.getElementById(`wls-file-label-${sessionId}`);
            if (labelElem) labelElem.innerText = 'إرفاق ملف';
            const infoElem = document.getElementById(`wls-file-info-${sessionId}`);
            if (infoElem) infoElem.classList.add('hidden');
            return;
        }

        // 2. تحديث الواجهة وتنبيه المستخدم
        const sizeFormatted = file.size > 1024 * 1024 
            ? (file.size / (1024 * 1024)).toFixed(1) + ' MB'
            : (file.size / 1024).toFixed(0) + ' KB';

        const labelElem = document.getElementById(`wls-file-label-${sessionId}`);
        if (labelElem) labelElem.innerText = 'تم الاختيار ✓';

        const infoElem = document.getElementById(`wls-file-info-${sessionId}`);
        if (infoElem) {
            infoElem.innerText = `${file.name} (${sizeFormatted})`;
            infoElem.classList.remove('hidden');
            infoElem.title = `${file.name} (${sizeFormatted}) - اضغط حفظ للضغط والرفع`;
        }

        const submitBtn = document.getElementById(`wls-submit-${sessionId}`);
        if (submitBtn) {
            submitBtn.classList.remove('bg-purple-50', 'text-purple-700');
            submitBtn.classList.add('bg-purple-600', 'text-white', 'ring-2', 'ring-purple-400');
            submitBtn.title = 'اضغط هنا لحفظ الجلسة ورفع وضغط المرفق';
        }

        showToast(`تم اختيار الملف (${sizeFormatted}). اضغط على زر "حفظ" ليتم ضغطه ورفعه تلقائياً.`, 'info');
    }

    async function deleteSessionAttachment(sessionId) {
        if (!confirm('هل أنت متأكد من حذف المرفق الطبي لهذه الجلسة؟ لا يمكن التراجع عن هذا الإجراء.')) {
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(`/weight-loss-sessions/${sessionId}/attachment`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message || 'تم حذف مرفق الجلسة بنجاح.', 'success');
                const cell = document.getElementById(`wls-attachment-cell-${sessionId}`);
                if (cell) {
                    cell.innerHTML = `
                        <div class="flex flex-col items-center justify-center gap-1">
                            <label class="cursor-pointer inline-flex items-center gap-1 px-2 py-1 bg-purple-50/80 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-[10px] font-bold transition">
                                <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span id="wls-file-label-${sessionId}">إرفاق ملف</span>
                                <input type="file" 
                                       id="wls-file-input-${sessionId}" 
                                       name="attachment" 
                                       form="wls-form-${sessionId}" 
                                       accept="image/*,application/pdf" 
                                       class="hidden" 
                                       onchange="handleSessionFileSelected(this, ${sessionId})">
                            </label>
                            <span id="wls-file-info-${sessionId}" class="hidden text-[9px] text-purple-700 font-bold max-w-[90px] truncate"></span>
                        </div>
                    `;
                }
            } else {
                showToast(data.message || 'حدث خطأ أثناء حذف المرفق.', 'error');
            }
        } catch (error) {
            showToast('تعذر الاتصال بالخادم لحذف المرفق.', 'error');
        }
    }
</script>
@endpush
@endsection
