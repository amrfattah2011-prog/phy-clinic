@extends('layouts.app')

@section('title', 'شاشة الصلاحيات والأدوار الوظيفية - ' . config('app.name'))

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-purple-50 text-purple-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                <div>
                    <h1 class="text-xl font-black text-slate-900">الأدوار الوظيفية ومصفوفة الصلاحيات</h1>
                    <p class="text-xs text-slate-500 mt-0.5">التحكم الدقيق في صلاحيات الأطباء والمساعدين وموظفي الاستقبال والخزينة.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('staff.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>طاقم العمل والموظفين</span>
            </a>
            <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>إضافة دور وظيفي جديد</span>
            </a>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($roles as $role)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <!-- Card Top -->
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-base
                                {{ $role->name === 'admin' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $role->name === 'doctor' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $role->name === 'therapist' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                {{ $role->name === 'receptionist' ? 'bg-teal-100 text-teal-800' : '' }}
                                {{ $role->name === 'accountant' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ !in_array($role->name, ['admin','doctor','therapist','receptionist','accountant']) ? 'bg-purple-100 text-purple-800' : '' }}">
                                @if($role->name === 'admin') 👑 @elseif($role->name === 'doctor') 🩺 @elseif($role->name === 'therapist') ⚡ @elseif($role->name === 'receptionist') 📋 @elseif($role->name === 'accountant') 💰 @else 🛡️ @endif
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">{{ $role->display_name }}</h2>
                                <span class="text-[11px] font-mono text-slate-400 font-semibold">{{ $role->name }}</span>
                            </div>
                        </div>

                        @if($role->is_system)
                            <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">دور أساسي</span>
                        @endif
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-slate-600 mb-4 min-h-[36px] line-clamp-2">
                        {{ $role->description ?: 'صلاحيات مخصصة للعمل على أقسام العيادة.' }}
                    </p>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs mb-4">
                        <div>
                            <span class="text-[11px] text-slate-400 block">الموظفون المعينون</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $role->users->count() }} موظف</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400 block">عدد الصلاحيات</span>
                            <span class="font-bold text-purple-700 text-sm">
                                {{ $role->name === 'admin' ? 'كاملة (19/19)' : $role->permissions->count() . ' صلاحية' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('roles.edit', $role->id) }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-xl text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>تعديل الصلاحيات</span>
                    </a>

                    @if(!$role->is_system && $role->users->count() === 0)
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الدور الوظيفي؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="حذف الدور">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
