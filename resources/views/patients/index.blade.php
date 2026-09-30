@extends('layouts.app')

@section('title', 'سجل المرضى والحالات - عيادة العلاج الطبيعي والتخسيس')

@section('content')
<div class="space-y-6">

    <!-- Header & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900">سجل المرضى والحالات</h1>
            <p class="text-xs text-slate-500 mt-1">إجمالي المسجلين: {{ $patients->total() }} مريض</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <form action="{{ route('patients.index') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="بحث بالاسم أو الهاتف..." 
                       class="w-64 pl-3 pr-9 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

            @if($search)
                <a href="{{ route('patients.index') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 bg-slate-100 rounded-xl transition">
                    إلغاء الفلتر
                </a>
            @endif

            <a href="{{ route('patients.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>مريض جديد</span>
            </a>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">رقم الملف</th>
                        <th class="py-3 px-4">اسم المريض</th>
                        <th class="py-3 px-4">رقم الهاتف</th>
                        <th class="py-3 px-4">السن / النوع</th>
                        <th class="py-3 px-4">الباقات والجلسات</th>
                        <th class="py-3 px-4 text-left">الرصيد المالي</th>
                        <th class="py-3 px-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($patients as $patient)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-500">
                                #{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('patients.show', $patient) }}" class="font-bold text-sm text-slate-900 hover:text-brand-600 transition block">
                                    {{ $patient->name }}
                                </a>
                                @if($patient->diseases && $patient->diseases->count() > 0)
                                    <div class="flex items-center gap-1 flex-wrap mt-1">
                                        @foreach($patient->diseases->take(3) as $dis)
                                            <span class="px-1.5 py-0.5 bg-amber-50 border border-amber-200 text-amber-900 text-[10px] font-bold rounded">{{ $dis->name }}</span>
                                        @endforeach
                                        @if($patient->diseases->count() > 3)
                                            <span class="text-[10px] text-amber-800 font-bold">+{{ $patient->diseases->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @elseif($patient->medical_history)
                                    <span class="text-[11px] text-slate-400 block line-clamp-1 mt-0.5">
                                        {{ $patient->medical_history }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono font-medium text-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $patient->phone }}</span>
                                    @if($patient->whatsapp_phone)
                                        <a href="{{ $patient->whatsapp_link }}" target="_blank" rel="noopener noreferrer" 
                                           class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition shadow-xs" 
                                           title="مراسلة عبر واتساب">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.971.53 1.961.815 2.796.815 3.183 0 5.769-2.586 5.769-5.766.001-3.182-2.585-5.768-5.769-5.768zm7.424 5.766c0 4.095-3.331 7.425-7.424 7.425-1.209 0-2.399-.297-3.461-.861l-4.57 1.2 1.221-4.455c-.657-1.127-1.004-2.42-1.004-3.75 0-4.095 3.33-7.426 7.424-7.426 4.093 0 7.424 3.331 7.424 7.426z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-800">{{ $patient->age }} سنة</span>
                                <span class="text-[11px] text-slate-400 block">{{ $patient->gender_arabic }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($patient->therapy_plans_count > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold">
                                            {{ $patient->therapy_plans_count }} باقة علاج طبيعي
                                        </span>
                                    @endif
                                    @if($patient->weight_loss_plans_count > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 text-[11px] font-bold">
                                            {{ $patient->weight_loss_plans_count }} باقة تخسيس
                                        </span>
                                    @endif
                                    @if($patient->therapy_plans_count === 0 && $patient->weight_loss_plans_count === 0)
                                        <span class="text-slate-400 text-[11px]">كشف فقط</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-left">
                                @if($patient->remaining_balance > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                                        متبقي: {{ number_format($patient->remaining_balance, 2) }} ج.م
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                        خالص الحساب
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('patients.show', $patient) }}" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-600 hover:text-white text-brand-700 text-xs font-bold rounded-lg transition" title="عرض الملف الطبي الكامل">
                                        عرض الملف
                                    </a>
                                    <a href="{{ route('patients.edit', $patient) }}" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg transition" title="تعديل البيانات الأساسية">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                لا يوجد مرضى يطابقون شروط البحث
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($patients->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $patients->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
