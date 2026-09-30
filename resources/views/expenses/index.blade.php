@extends('layouts.app')

@section('title', 'إدارة المصروفات والنفقات - ' . config('app.name'))

@section('content')
<div class="space-y-6">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span>سجل المصروفات والنفقات</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">توثيق ومتابعة كافة المصاريف التشغيلية للمركز من إيجارات، مرافق، مستلزمات، وأجور.</p>
        </div>

        <div class="flex items-center gap-2.5">
            @if(auth()->user()->hasPermission('treasury.statement'))
                <a href="{{ route('reports.treasury') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>كشف حساب الخزينة</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('expenses.create'))
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition transform active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>تسجيل مصروف جديد</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">إجمالي مصروفات الفترة</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-2xl font-black text-rose-700">{{ number_format($totalExpenses, 2) }}</span>
                <span class="text-xs font-bold text-slate-500">ج.م</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">عدد حركات الصرف</span>
                <span class="p-2 rounded-xl bg-slate-100 text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-2xl font-black text-slate-800">{{ $expensesCount }}</span>
                <span class="text-xs font-bold text-slate-500">سند</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs sm:col-span-2">
            <span class="text-xs font-bold text-slate-500 block mb-2">توزيع المصروفات حسب البند للفترة</span>
            <div class="flex flex-wrap gap-1.5">
                @forelse($categoryBreakdown as $cat)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs bg-slate-100 border border-slate-200 text-slate-700">
                        <span class="font-bold">{{ $categories[$cat->category] ?? $cat->category }}:</span>
                        <span class="font-black text-rose-600">{{ number_format($cat->total_amount, 0) }} ج.م</span>
                        <span class="text-[10px] text-slate-400">({{ $cat->count }})</span>
                    </span>
                @empty
                    <span class="text-xs text-slate-400">لا توجد حركات صرف مسجلة في هذا النطاق.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('expenses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">بند المصروف</label>
                <select name="category" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">جميع البنود</option>
                    @foreach($categories as $key => $name)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">الموظف القائم بالصرف</label>
                <select name="user_id" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">جميع الموظفين</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">بحث بالبيان / الفاتورة</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="كلمة البحث..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2 rounded-xl transition">
                    تصفية
                </button>
                <a href="{{ route('expenses.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">تاريخ الصرف</th>
                        <th class="py-3 px-4">بند المصروف</th>
                        <th class="py-3 px-4">البيان والتفاصيل</th>
                        <th class="py-3 px-4">المبلغ</th>
                        <th class="py-3 px-4">طريقة الدفع</th>
                        <th class="py-3 px-4">رقم الإيصال/الفاتورة</th>
                        <th class="py-3 px-4">سُجّل بواسطة</th>
                        <th class="py-3 px-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}</td>
                            <td class="py-3 px-4 font-bold text-slate-700 whitespace-nowrap">{{ $expense->expense_date->format('Y/m/d') }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                    {{ $expense->category_name }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $expense->title }}</div>
                                @if($expense->notes)
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ Str::limit($expense->notes, 60) }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-black text-rose-600 text-sm whitespace-nowrap">
                                {{ number_format($expense->amount, 2) }} ج.م
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $expense->payment_method_name }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $expense->receipt_reference ?: '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ $expense->creator?->name ?? 'غير محدد' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if(auth()->user()->hasPermission('expenses.delete'))
                                        <a href="{{ route('expenses.edit', $expense) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition" title="تعديل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف بند المصروف هذا نهائياً؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="حذف">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-300 text-[11px]">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                لا توجد حركات مصروفات تطابق خيارات التصفية المحددة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection