@extends('layouts.app')

@section('title', 'إضافة موظف جديد - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900">إضافة موظف / مستخدم جديد</h1>
            <p class="text-xs text-slate-500 mt-1">إنشاء حساب للدخول على النظام وتحديد الدور الوظيفي وكلمة المرور.</p>
        </div>
        <a href="{{ route('staff.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            عودة لقائمة الموظفين
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('staff.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="name">
                        الاسم الرباعي للموظف <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                           placeholder="مثال: د. محمد أحمد عبد الله"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-medium">
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="username">
                        اسم المستخدم للدخول (Username) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="username" id="username" required value="{{ old('username') }}"
                           placeholder="مثال: dr_mohamed أو reception2"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono">
                    <span class="text-[10px] text-slate-400 mt-1 block">حروف إنجليزية أو أرقام وشرطة سفلية بدون مسافات.</span>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="email">
                        البريد الإلكتروني <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                           placeholder="mohamed@clinic.com"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="phone">
                        رقم الهاتف / الواتساب
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           placeholder="01012345678"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-mono">
                </div>

                <!-- Role Selection -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="role_id">
                        الدور الوظيفي والصلاحية <span class="text-rose-500">*</span>
                    </label>
                    <select name="role_id" id="role_id" required
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-bold text-slate-800">
                        <option value="">-- اختر الدور الوظيفي --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} - ({{ $role->description ?: 'صلاحيات محددة' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="password">
                        كلمة المرور <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required
                           placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-medium">
                    <span class="text-[10px] text-slate-400 mt-1 block">لا تقل عن 6 أحرف أو أرقام.</span>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="password_confirmation">
                        تأكيد كلمة المرور <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 font-medium">
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-200 w-full">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                           class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500 cursor-pointer">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">تفعيل حساب الموظف فوراً</span>
                        <span class="text-[11px] text-slate-500">يتيح للموظف تسجيل الدخول مباشرة بعد الحفظ.</span>
                    </div>
                </label>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <a href="{{ route('staff.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    إلغاء
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition transform active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>حفظ وإصدار حساب الموظف</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
