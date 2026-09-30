@extends('layouts.app')

@section('title', 'تعديل بيانات الموظف - ' . $staff->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900">تعديل بيانات الموظف: {{ $staff->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">تحديث بيانات الاتصال، الدور والصلاحيات، أو تغيير كلمة المرور.</p>
        </div>
        <a href="{{ route('staff.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            عودة لقائمة الموظفين
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('staff.update', $staff->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="name">
                        الاسم الرباعي للموظف <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $staff->name) }}"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-medium">
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="username">
                        اسم المستخدم (Username) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="username" id="username" required value="{{ old('username', $staff->username) }}"
                           {{ $staff->username === 'admin' ? 'readonly' : '' }}
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono {{ $staff->username === 'admin' ? 'cursor-not-allowed opacity-75' : '' }}">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="email">
                        البريد الإلكتروني <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" required value="{{ old('email', $staff->email) }}"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="phone">
                        رقم الهاتف / الواتساب
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $staff->phone) }}"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono">
                </div>

                <!-- Role Selection -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="role_id">
                        الدور الوظيفي والصلاحية <span class="text-rose-500">*</span>
                    </label>
                    <select name="role_id" id="role_id" required
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-bold text-slate-800">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $staff->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} - ({{ $role->description ?: 'صلاحيات محددة' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Password Change Section (Optional) -->
            <div class="pt-4 border-t border-slate-100">
                <div class="mb-3">
                    <span class="text-xs font-bold text-slate-800 block">تغيير كلمة المرور (اختياري)</span>
                    <span class="text-[11px] text-slate-400">اترك الحقول فارغة إذا كنت لا ترغب في تغيير كلمة المرور الحالية للموظف.</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5" for="password">
                            كلمة المرور الجديدة
                        </label>
                        <input type="password" name="password" id="password"
                               placeholder="اترك فارغاً للإبقاء عليها"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5" for="password_confirmation">
                            تأكيد كلمة المرور الجديدة
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               placeholder="أعد كتابة كلمة المرور الجديدة"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <!-- Active Checkbox -->
            @if($staff->username !== 'admin')
                <div class="pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-200 w-full">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500 cursor-pointer">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">الحساب نشط ومفعل</span>
                            <span class="text-[11px] text-slate-500">إلغاء التحديد يؤدي إلى منع الموظف فوراً من تسجيل الدخول للنظام.</span>
                        </div>
                    </label>
                </div>
            @endif

            <!-- Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <a href="{{ route('staff.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    إلغاء
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition transform active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>حفظ التعديلات</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
