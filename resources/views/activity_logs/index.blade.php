@extends('layouts.app')

@section('title', 'سجل حركات النظام (مين عمل إيه) - ' . config('app.name'))

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-50 text-amber-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h1 class="text-xl font-black text-slate-900">سجل حركات النظام والرقابة (Audit Trail)</h1>
                    <p class="text-xs text-slate-500 mt-0.5">توثيق كامل لكل عملية تمت في العيادة: مين عمل كل حركة، وتاريخها، ووقتها، وجهاز الاتصال.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl border border-slate-200">
                إجمالي الحركات المسجلة: {{ $logs->total() }} حركة
            </span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('activity-logs.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- User Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">الموظف المسئول</label>
                    <select name="user_id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">جميع الموظفين</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role?->display_name ?: 'بدون دور' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Module Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">القسم / الوحدة</label>
                    <select name="module" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">جميع الأقسام</option>
                        @foreach($modules as $key => $name)
                            <option value="{{ $key }}" {{ request('module') === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">نوع الحركة</label>
                    <select name="action" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="">جميع أنواع الحركات</option>
                        @foreach($actions as $actKey => $actName)
                            <option value="{{ $actKey }}" {{ request('action') === $actKey ? 'selected' : '' }}>{{ $actName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range From -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono">
                </div>

                <!-- Date Range To -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
            </div>

            <!-- Search and Action Buttons -->
            <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100 flex-wrap">
                <div class="flex-1 min-w-[240px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="بحث بالبيان، اسم المريض، رقم الهاتف، أو الـ IP..." 
                           class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                        إعادة ضبط
                    </a>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>تطبيق الفلترة والبحث</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">الموظف القائم بالحركة</th>
                        <th class="py-3.5 px-4">نوع العملية</th>
                        <th class="py-3.5 px-4">القسم</th>
                        <th class="py-3.5 px-4">البيان وتفاصيل الحركة</th>
                        <th class="py-3.5 px-4">التاريخ والوقت</th>
                        <th class="py-3.5 px-4">عنوان IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Staff User -->
                            <td class="py-3.5 px-4">
                                @if($log->user)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[11px]">
                                            {{ mb_substr($log->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block">{{ $log->user->name }}</span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ $log->user->role?->display_name ?: 'مستخدم' }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">مستخدم محذوف / نظام</span>
                                @endif
                            </td>

                            <!-- Action Badge -->
                            <td class="py-3.5 px-4">
                                @php
                                    $actionColors = [
                                        'create' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'update' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'delete' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        'attendance' => 'bg-teal-100 text-teal-800 border-teal-200',
                                        'dosage' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'payment' => 'bg-amber-100 text-amber-900 border-amber-200',
                                        'login' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'logout' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    ];
                                    $actionClass = $actionColors[$log->action] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-[11px] border {{ $actionClass }}">
                                    {{ $log->action_arabic }}
                                </span>
                            </td>

                            <!-- Module -->
                            <td class="py-3.5 px-4">
                                <span class="text-slate-600 font-semibold">
                                    {{ $log->module_arabic }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="py-3.5 px-4 max-w-md">
                                <div class="text-slate-800 text-xs leading-relaxed">
                                    {{ $log->description }}
                                </div>
                                @if($log->patient)
                                    <a href="{{ route('patients.show', $log->patient_id) }}" 
                                       class="text-[11px] text-brand-600 hover:text-brand-800 font-bold inline-flex items-center gap-1 mt-1 transition">
                                        <span>ملف المريض: {{ $log->patient->name }}</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @endif
                            </td>

                            <!-- Date & Time -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $log->created_at->translatedFormat('d M Y - h:i A') }}</span>
                                <span class="text-[10px] text-slate-400 font-semibold">{{ $log->created_at->diffForHumans() }}</span>
                            </td>

                            <!-- IP Address -->
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                {{ $log->ip_address ?: '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                لا توجد حركات مسجلة تطابق اختيارات الفلترة المحددة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
