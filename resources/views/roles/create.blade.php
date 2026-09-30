@extends('layouts.app')

@section('title', 'إضافة دور وظيفي جديد - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900">إضافة دور وظيفي جديد</h1>
            <p class="text-xs text-slate-500 mt-1">تحديد المسمى وتخصيص الصلاحيات الممنوحة لهذا الدور بدقة.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            عودة للأدوار
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Basic Info Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">1. البيانات الأساسية للدور</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="display_name">
                        المسمى الوظيفي بالعربية <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="display_name" id="display_name" required value="{{ old('display_name') }}"
                           placeholder="مثال: مشرف فرع أو فني علاج طبيعي"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="description">
                        الوصف المختصر للمسؤوليات
                    </label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}"
                           placeholder="مثال: إدارة تسجيل الحالات والتحصيلات في الفترة المسائية"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Permissions Matrix Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">2. مصفوفة الصلاحيات الممنوحة</h2>
                    <p class="text-xs text-slate-500 mt-0.5">اختر الصلاحيات التي يحق للمعينين بهذا الدور تنفيذها على النظام.</p>
                </div>
                <button type="button" onclick="toggleAllGlobal(this)" 
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    تحديد الكل
                </button>
            </div>

            <div class="space-y-6">
                @foreach($permissions as $category => $categoryPermissions)
                    <div class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200/60">
                            <span class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                                {{ $category }}
                            </span>
                            <button type="button" onclick="toggleCategoryGroup('cat-{{ $loop->index }}')" 
                                    class="text-[11px] font-bold text-purple-700 hover:text-purple-900">
                                تحديد / إلغاء تصنيف
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="cat-{{ $loop->index }}">
                            @foreach($categoryPermissions as $perm)
                                <label class="flex items-start gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200/80 hover:border-purple-300 transition cursor-pointer">
                                    <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}"
                                           class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500 mt-0.5 perm-checkbox cursor-pointer">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 block leading-snug">{{ $perm->display_name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <a href="{{ route('roles.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                إلغاء
            </a>
            <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-sm transition transform active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>حفظ الدور والصلاحيات</span>
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
    function toggleCategoryGroup(groupId) {
        const group = document.getElementById(groupId);
        const checkboxes = group.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }

    function toggleAllGlobal(btnElem) {
        const checkboxes = document.querySelectorAll('.perm-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        btnElem.innerText = allChecked ? 'تحديد الكل' : 'إلغاء تحديد الكل';
    }
</script>
@endpush
@endsection
