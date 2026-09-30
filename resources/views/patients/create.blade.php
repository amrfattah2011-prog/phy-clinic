@extends('layouts.app')

@section('title', 'تسجيل مريض جديد - عيادة العلاج الطبيعي والتخسيس')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Top Breadcrumbs -->
    <div class="flex items-center justify-between">
        <a href="{{ route('patients.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span>العودة لقائمة المرضى</span>
        </a>
    </div>

    <!-- Registration Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h1 class="text-xl font-black text-slate-900">تسجيل ملف مريض / حالة جديدة</h1>
            <p class="text-xs text-slate-500 mt-1">يرجى إدخال البيانات الشخصية والطبية وتحديد الأمراض المزمنة بدقة لإنشاء السجل الطبي وفتح كشف الحساب.</p>
        </div>

        <form action="{{ route('patients.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Patient Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">اسم المريض بالكامل <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="مثال: محمود أحمد حسن"
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
            </div>

            <!-- Phone Number & Age -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف / الواتساب <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="مثال: 01012345678"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition font-mono">
                </div>

                <div>
                    <label for="age" class="block text-xs font-bold text-slate-700 mb-1.5">السن (بالسنوات) <span class="text-rose-500">*</span></label>
                    <input type="number" name="age" id="age" value="{{ old('age') }}" required min="1" max="120" placeholder="مثال: 35"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>
            </div>

            <!-- Gender Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">النوع <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center justify-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-slate-50 transition {{ old('gender', 'male') === 'male' ? 'border-brand-500 bg-brand-50/50 text-brand-900 font-bold' : 'border-slate-200 text-slate-600' }}">
                        <input type="radio" name="gender" value="male" class="text-brand-600 focus:ring-brand-500" {{ old('gender', 'male') === 'male' ? 'checked' : '' }}>
                        <span>ذكر</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-slate-50 transition {{ old('gender') === 'female' ? 'border-brand-500 bg-brand-50/50 text-brand-900 font-bold' : 'border-slate-200 text-slate-600' }}">
                        <input type="radio" name="gender" value="female" class="text-brand-600 focus:ring-brand-500" {{ old('gender') === 'female' ? 'checked' : '' }}>
                        <span>أنثى</span>
                    </label>
                </div>
            </div>

            <!-- Chronic Diseases Checkboxes (قائمة الأمراض المزمنة القابلة للإضافة) -->
            <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-amber-950">
                        الأمراض المزمنة والملاحظات الطبية المصاحبة (حدد ما ينطبق عبر Checkboxes):
                    </label>
                    <a href="{{ route('settings.edit') }}" target="_blank" class="text-[11px] text-amber-800 hover:underline font-bold">
                        إدارة القائمة في الإعدادات &larr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($diseases as $disease)
                        <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-amber-200/60 text-xs text-slate-800 cursor-pointer hover:bg-amber-100/40 transition">
                            <input type="checkbox" name="disease_ids[]" value="{{ $disease->id }}"
                                   class="w-4 h-4 text-amber-600 rounded border-slate-300 focus:ring-amber-500"
                                   {{ is_array(old('disease_ids')) && in_array($disease->id, old('disease_ids')) ? 'checked' : '' }}>
                            <span>{{ $disease->name }}</span>
                        </label>
                    @endforeach
                </div>

                <!-- Add a new disease on the fly -->
                <div class="pt-2 border-t border-amber-200/70 flex items-center gap-2">
                    <span class="text-xs font-bold text-amber-900 whitespace-nowrap">+ إضافة مرض آخر غير مسجل:</span>
                    <input type="text" name="new_disease_name" value="{{ old('new_disease_name') }}" placeholder="اكتب اسم المرض الجديد هنا..."
                           class="flex-1 px-3 py-1.5 text-xs bg-white border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <!-- Medical History -->
            <div>
                <label for="medical_history" class="block text-xs font-bold text-slate-700 mb-1.5">ملاحظات طبية إضافية / عمليات سابقة أو شكاوى عامة</label>
                <textarea name="medical_history" id="medical_history" rows="2" placeholder="مثال: عملية غضروف قطني سابق، حساسية أسبرين..."
                          class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">{{ old('medical_history') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('patients.index') }}" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    إلغاء
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl shadow-sm transition transform active:scale-95 flex items-center gap-2">
                    <span>حفظ ومتابعة الكشف الطبي</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
