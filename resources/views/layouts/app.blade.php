<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // نستخدم الكاش لمدة 30 دقيقة بدلاً من استعلام DB في كل صفحة
        $clinicSettings = \Illuminate\Support\Facades\Cache::remember(
            'clinic_settings',
            1800,
            fn() => \App\Models\ClinicSetting::getSettings()
        );
    @endphp
    <title>@yield('title', $clinicSettings->clinic_name . ' - ' . $clinicSettings->doctor_name)</title>
    
    <!-- Google Fonts: Cairo & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'Tajawal', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        medical: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col antialiased">
    <!-- Top Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Right Side: Logo & Clinic Title AND Dropdown Navigation -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <div class="hidden sm:block">
                            <span class="text-base sm:text-lg font-black text-slate-900 tracking-tight block leading-snug">{{ $clinicSettings->clinic_name }}</span>
                            <span class="text-xs text-brand-700 font-bold block">{{ $clinicSettings->doctor_title }}</span>
                        </div>
                    </a>

                    <!-- Main Navigation Dropdown Menu on the Right (القائمة المنسدلة على اليمين) -->
                    <div class="relative" id="mainNavDropdownWrapper">
                        <button id="mainNavDropdownBtn" type="button" 
                                class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-700 border border-slate-200 transition shadow-xs focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:scale-95"
                                aria-expanded="false" aria-haspopup="true">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            <span>القائمة الرئيسية</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" id="dropdownChevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu Panel -->
                        <div id="mainNavDropdownPanel" 
                             class="hidden absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 py-3 px-2 z-50 max-h-[calc(100vh-80px)] overflow-y-auto">
                            
                            <!-- Section 1: العيادة والمرضى -->
                            <div class="px-3 py-1.5 text-[11px] font-black text-slate-400 uppercase tracking-wider">العيادة والمرضى</div>
                            
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-brand-50 text-brand-700' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="leading-tight">الرئيسية</div>
                                    <div class="text-[10px] text-slate-400 font-normal">لوحة المؤشرات اليومية والإحصائيات</div>
                                </div>
                            </a>

                            <a href="{{ route('patients.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('patients.*') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('patients.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-blue-50 text-blue-700' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="leading-tight">سجل المرضى</div>
                                    <div class="text-[10px] text-slate-400 font-normal">ملفات المرضى، الكشوفات، والجلسات</div>
                                </div>
                            </a>

                            <!-- Section 2: التقارير والمالية -->
                            @auth
                                @if(auth()->user()->hasPermission('reports.operations') || auth()->user()->hasPermission('reports.weight_loss') || auth()->user()->hasPermission('expenses.view') || auth()->user()->hasPermission('treasury.statement'))
                                    <div class="my-2 border-t border-slate-100"></div>
                                    <div class="px-3 py-1.5 text-[11px] font-black text-slate-400 uppercase tracking-wider">التقارير والمالية</div>

                                    @if(auth()->user()->hasPermission('reports.operations'))
                                        <a href="{{ route('reports.operations') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('reports.operations') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('reports.operations') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-purple-50 text-purple-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">العمليات الطبية</div>
                                                <div class="text-[10px] text-slate-400 font-normal">تقرير الكشف والجلسات والتخسيس</div>
                                            </div>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('reports.weight_loss') || auth()->user()->hasPermission('reports.operations'))
                                        <a href="{{ route('reports.weight_loss') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('reports.weight_loss') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('reports.weight_loss') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-teal-50 text-teal-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">باقات التخسيس والتحصيل</div>
                                                <div class="text-[10px] text-slate-400 font-normal">تكلفة الجلسة، نسب السداد، والمتبقي</div>
                                            </div>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('expenses.view'))
                                        <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('expenses.*') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('expenses.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-rose-50 text-rose-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">المصروفات</div>
                                                <div class="text-[10px] text-slate-400 font-normal">تسجيل ومتابعة نفقات العيادة</div>
                                            </div>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('treasury.statement'))
                                        <a href="{{ route('reports.treasury') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('reports.treasury') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('reports.treasury') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-amber-50 text-amber-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">كشف الخزينة</div>
                                                <div class="text-[10px] text-slate-400 font-normal">حركة الوارد والمنصرف ورصيد الدرج</div>
                                            </div>
                                        </a>
                                    @endif
                                @endif

                                <!-- Section 3: الإدارة والنظام -->
                                @if(auth()->user()->hasPermission('staff.manage') || auth()->user()->hasPermission('roles.manage') || auth()->user()->hasPermission('activity_logs.view') || auth()->user()->hasPermission('settings.manage'))
                                    <div class="my-2 border-t border-slate-100"></div>
                                    <div class="px-3 py-1.5 text-[11px] font-black text-slate-400 uppercase tracking-wider">الإدارة والتحكم</div>

                                    @if(auth()->user()->hasPermission('staff.manage'))
                                        <a href="{{ route('staff.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('staff.*') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('staff.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-teal-50 text-teal-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">الموظفون</div>
                                                <div class="text-[10px] text-slate-400 font-normal">حسابات الموظفين وكلمات المرور</div>
                                            </div>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('roles.manage'))
                                        <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('roles.*') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('roles.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-indigo-50 text-indigo-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">الصلاحيات</div>
                                                <div class="text-[10px] text-slate-400 font-normal">تحديد صلاحيات كل دور بالمنظومة</div>
                                            </div>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('activity_logs.view'))
                                        <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('activity-logs.*') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('activity-logs.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-slate-100 text-slate-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">سجل الحركات</div>
                                                <div class="text-[10px] text-slate-400 font-normal">من قام بكل حركة وتعديل بالنظام</div>
                                            </div>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('settings.manage'))
                                        <a href="{{ route('settings.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('settings.*') ? 'bg-brand-50 text-brand-800 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('settings.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-emerald-50 text-emerald-700' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="leading-tight">بيانات المركز</div>
                                                <div class="text-[10px] text-slate-400 font-normal">اسم العيادة، الأجهزة، وقائمة الأمراض</div>
                                            </div>
                                        </a>
                                    @endif
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Quick Desktop Shortcuts (الوصول السريع للشاشات الأكثر استخداماً) -->
                    <div class="hidden lg:flex items-center gap-1 border-r border-slate-200 pr-2 mr-1">
                        <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            الرئيسية
                        </a>
                        <a href="{{ route('patients.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('patients.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            سجل المرضى
                        </a>
                    </div>
                </div>

                <!-- Actions / Quick Search / User Profile / Logout -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <form action="{{ route('patients.index') }}" method="GET" class="relative hidden xl:block">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالمريض أو الهاتف..." 
                               class="w-48 pl-3 pr-9 py-1.5 text-xs bg-slate-100 border border-slate-200 rounded-lg focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </form>

                    <a href="{{ route('patients.create') }}" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold px-2.5 sm:px-3.5 py-2 rounded-lg shadow-sm hover:shadow transition transform active:scale-95 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>مريض جديد</span>
                    </a>

                    @auth
                        <!-- Current Logged-in Staff Info & Logout -->
                        <div class="flex items-center gap-2 border-r border-slate-200 pr-2 sm:pr-3 mr-1">
                            <div class="text-right hidden sm:block">
                                <span class="text-xs font-bold text-slate-800 block leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-brand-700 font-semibold block">{{ auth()->user()->role?->display_name ?: 'موظف' }}</span>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" class="inline" onsubmit="return confirm('هل ترغب في تسجيل الخروج من النظام؟');">
                                @csrf
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition flex items-center gap-1 text-xs font-bold" title="تسجيل الخروج">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span class="hidden lg:inline">خروج</span>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages & Receipt Print Prompts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                @if(session('print_receipt_id'))
                    <a href="{{ route('payments.receipt', session('print_receipt_id')) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>طباعة الإيصال الحراري فوراً</span>
                    </a>
                @endif
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-bold">يرجى تصحيح الأخطاء التالية:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 mr-6">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <div>
                {{ $clinicSettings->clinic_name }} &copy; {{ date('Y') }} - {{ $clinicSettings->doctor_name }}
            </div>
            <div class="flex items-center gap-4">
                <span>{{ $clinicSettings->address }}</span>
                <span>&bull;</span>
                <span>هاتف: {{ $clinicSettings->phone_1 }}</span>
            </div>
        </div>
    </footer>

    <!-- Global Toast Container for AJAX Actions -->
    <div id="toast-container" class="fixed bottom-5 left-5 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <script>
        // Helper function to show floating toast notifications
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-emerald-700 text-white' : 'bg-rose-700 text-white';
            toast.className = `${bgClass} px-4 py-2.5 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 transform transition-all duration-300 opacity-0 translate-y-2 pointer-events-auto`;
            toast.innerHTML = `
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
                </svg>
                <span>${message}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
            }, 10);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Setup CSRF token for AJAX fetch calls
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Dropdown toggle logic for right navigation menu
        const dropdownWrapper = document.getElementById('mainNavDropdownWrapper');
        const dropdownBtn = document.getElementById('mainNavDropdownBtn');
        const dropdownPanel = document.getElementById('mainNavDropdownPanel');
        const dropdownChevron = document.getElementById('dropdownChevron');

        if (dropdownBtn && dropdownPanel) {
            dropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = dropdownPanel.classList.contains('hidden');
                if (isHidden) {
                    dropdownPanel.classList.remove('hidden');
                    dropdownBtn.setAttribute('aria-expanded', 'true');
                    if (dropdownChevron) dropdownChevron.classList.add('rotate-180');
                } else {
                    dropdownPanel.classList.add('hidden');
                    dropdownBtn.setAttribute('aria-expanded', 'false');
                    if (dropdownChevron) dropdownChevron.classList.remove('rotate-180');
                }
            });

            document.addEventListener('click', function(e) {
                if (dropdownWrapper && !dropdownWrapper.contains(e.target)) {
                    dropdownPanel.classList.add('hidden');
                    dropdownBtn.setAttribute('aria-expanded', 'false');
                    if (dropdownChevron) dropdownChevron.classList.remove('rotate-180');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !dropdownPanel.classList.contains('hidden')) {
                    dropdownPanel.classList.add('hidden');
                    dropdownBtn.setAttribute('aria-expanded', 'false');
                    if (dropdownChevron) dropdownChevron.classList.remove('rotate-180');
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
