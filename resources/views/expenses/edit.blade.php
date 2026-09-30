@extends('layouts.app')

@section('title', 'تعديل بيانات المصروف - ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </span>
                <span>تعديل بيانات المصروف</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">تعديل المبلغ أو البند أو تفاصيل سند الصرف رقم #{{ $expense->id }}</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
            رجوع للمصروفات
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('expenses.update', $expense) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- بند المصروف -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بند المصروف <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        @foreach($categories as $key => $name)
                            <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('category') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                </div>

                <!-- المبلغ -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ (ج.م) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.5" name="amount" value="{{ old('amount', $expense->amount) }}" required placeholder="0.00" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 font-black text-rose-600 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    @error('amount') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- بيان / عنوان المصروف -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">البيان / تفاصيل المصروف <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $expense->title) }}" required placeholder="تفاصيل المصروف..." class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                @error('title') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- تاريخ الصرف -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تاريخ الصرف <span class="text-rose-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    @error('expense_date') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                </div>

                <!-- طريقة الصرف -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">طريقة الصرف <span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        @foreach($paymentMethods as $key => $name)
                            <option value="{{ $key }}" {{ old('payment_method', $expense->payment_method) == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('payment_method') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- رقم الإيصال / الفاتورة -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">رقم الفاتورة أو إيصال الصرف (اختياري)</label>
                <input type="text" name="receipt_reference" value="{{ old('receipt_reference', $expense->receipt_reference) }}" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 font-mono focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                @error('receipt_reference') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
            </div>

            <!-- ملاحظات -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات إضافية</label>
                <textarea name="notes" rows="2" class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl p-3 focus:bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none">{{ old('notes', $expense->notes) }}</textarea>
                @error('notes') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    إلغاء
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition transform active:scale-95">
                    تحديث وحفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection