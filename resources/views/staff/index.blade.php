@extends('layouts.app')

@section('title', 'إدارة طاقم العمل والموظفين - ' . config('app.name'))

@section('content')
<div class="space-y-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
                <div>
                    <h1 class="text-xl font-black text-slate-900">طاقم العمل والموظفين</h1>
                    <p class="text-xs text-slate-500 mt-0.5">إدارة حسابات الأطباء، موظفي الاستقبال، وأخصائيي العلاج الطبيعي وكلمات المرور.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>شاشة الصلاحيات والأدوار</span>
            </a>
            <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>إضافة موظف جديد</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('staff.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="بحث بالاسم، اسم المستخدم، الهاتف، أو البريد..." 
                       class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <select name="role_id" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">جميع الأدوار الوظيفية</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط ومفعل</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>معطل وموقوف</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                    فلترة
                </button>
            </div>
        </form>
    </div>

    <!-- Staff Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">الموظف</th>
                        <th class="py-3.5 px-4">اسم المستخدم</th>
                        <th class="py-3.5 px-4">الدور الوظيفي والصلاحية</th>
                        <th class="py-3.5 px-4">رقم الهاتف</th>
                        <th class="py-3.5 px-4">الحالة</th>
                        <th class="py-3.5 px-4">آخر تسجيل دخول</th>
                        <th class="py-3.5 px-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staff as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block">{{ $user->name }}</span>
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md text-[11px]">
                                    {{ $user->username ?: '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($user->role)
                                    <span class="inline-flex items-center gap-1 font-bold px-2.5 py-1 rounded-lg text-[11px] 
                                        {{ $user->role->name === 'admin' ? 'bg-amber-100 text-amber-900 border border-amber-200' : '' }}
                                        {{ $user->role->name === 'doctor' ? 'bg-blue-100 text-blue-900 border border-blue-200' : '' }}
                                        {{ $user->role->name === 'therapist' ? 'bg-indigo-100 text-indigo-900 border border-indigo-200' : '' }}
                                        {{ $user->role->name === 'receptionist' ? 'bg-teal-100 text-teal-900 border border-teal-200' : '' }}
                                        {{ $user->role->name === 'accountant' ? 'bg-emerald-100 text-emerald-900 border border-emerald-200' : '' }}">
                                        @if($user->role->name === 'admin') 👑 @endif
                                        @if($user->role->name === 'doctor') 🩺 @endif
                                        @if($user->role->name === 'therapist') ⚡ @endif
                                        @if($user->role->name === 'receptionist') 📋 @endif
                                        @if($user->role->name === 'accountant') 💰 @endif
                                        {{ $user->role->display_name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">بدون دور</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $user->phone ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full font-bold text-[11px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        نشط
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full font-bold text-[11px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        معطل
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                @if($user->last_login_at)
                                    <span class="block font-semibold text-slate-700">{{ $user->last_login_at->diffForHumans() }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $user->last_login_at->format('Y-m-d H:i') }}</span>
                                @else
                                    <span class="text-slate-400">لم يسجل دخول بعد</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('staff.edit', $user->id) }}" 
                                       class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="تعديل وتغيير كلمة المرور">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    @if($user->username !== 'admin' && $user->id !== auth()->id())
                                        <form action="{{ route('staff.toggle', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من تغيير حالة تفعيل هذا الحساب؟');">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="{{ $user->is_active ? 'تعطيل الحساب' : 'تفعيل الحساب' }}">
                                                @if($user->is_active)
                                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>
                                        </form>

                                        <form action="{{ route('staff.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('تحذير: هل أنت متأكد من حذف هذا الموظف نهائياً؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="حذف الموظف">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                لا يوجد موظفين مسجلين يطابقون معايير البحث
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $staff->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
