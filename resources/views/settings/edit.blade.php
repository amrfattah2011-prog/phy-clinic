@extends('layouts.app')

@section('title', 'إعدادات وبيانات المركز - ' . $settings->clinic_name)

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>إعدادات وبيانات المركز والعيادة</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">تعديل بيانات العيادة التي تظهر في رأس الصفحة، الإيصالات الحرارية (80mm)، وكشوفات الحساب، بالإضافة لإدارة قائمة الأجهزة والأمراض المزمنة.</p>
        </div>
    </div>

    <!-- Section 1: Clinic Profile Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <h2 class="text-base font-black text-slate-900 border-r-4 border-brand-600 pr-3 mb-6">
            البيانات الأساسية للمركز (الترويسة والإيصالات)
        </h2>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم العيادة / المركز <span class="text-rose-500">*</span></label>
                    <input type="text" name="clinic_name" value="{{ old('clinic_name', $settings->clinic_name) }}" required
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">اسم الطبيب المسؤول <span class="text-rose-500">*</span></label>
                    <input type="text" name="doctor_name" value="{{ old('doctor_name', $settings->doctor_name) }}" required
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">اللقب والتخصص الطبي <span class="text-rose-500">*</span></label>
                <input type="text" name="doctor_title" value="{{ old('doctor_title', $settings->doctor_title) }}" required
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف الرئيسي / الواتساب <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone_1" value="{{ old('phone_1', $settings->phone_1) }}" required
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم هاتف إضافي (اختياري)</label>
                    <input type="text" name="phone_2" value="{{ old('phone_2', $settings->phone_2) }}"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">عنوان المركز التفصيلي <span class="text-rose-500">*</span></label>
                    <input type="text" name="address" value="{{ old('address', $settings->address) }}" required
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم ترخيص مزاولة المهنة / وزارة الصحة</label>
                    <input type="text" name="license_number" value="{{ old('license_number', $settings->license_number) }}"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">نص تذييل الإيصال الحراري والملاحظات أسفل الفاتورة</label>
                    <input type="text" name="receipt_footer" value="{{ old('receipt_footer', $settings->receipt_footer) }}"
                           placeholder="مثال: يرجى إحضار الإيصال في كل زيارة • نتمنى لكم الشفاء العاجل"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">رمز العملة</label>
                    <input type="text" name="currency" value="{{ old('currency', $settings->currency) }}" required
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500 transition font-bold">
                </div>
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl shadow-sm transition transform active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>حفظ وتحديث بيانات المركز</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Medical Devices Catalog (الأجهزة الطبية) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-r-4 border-blue-600 pr-3 mb-6">
            <div>
                <h2 class="text-base font-black text-slate-900">
                    قائمة الأجهزة الطبية في المركز (قابل للتخصيص والإضافة)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">تظهر هذه الأجهزة في جلسات العلاج الطبيعي والتخسيس لتحديد الأجهزة المستخدمة لكل مريض في كل جلسة.</p>
            </div>
            <span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full">
                {{ count($devices) }} جهاز مسجل
            </span>
        </div>

        <!-- Add Device Form -->
        <form action="{{ route('settings.devices.store') }}" method="POST" class="p-4 bg-slate-50 rounded-xl border border-slate-200 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">اسم الجهاز الطبي <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="مثال: ليزر علاجي بارد (Cold Laser)"
                       class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">نوع الاستخدام</label>
                <select name="type" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="both">علاج طبيعي وتخسيس (كلاهما)</option>
                    <option value="therapy">علاج طبيعي فقط</option>
                    <option value="weight_loss">تخسيس ونحت قوام فقط</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">وصف موجز / الفائدة</label>
                <input type="text" name="description" placeholder="مثال: تسكين الألم وتسريع الاستشفاء"
                       class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-xs transition">
                    + إضافة جهاز جديد
                </button>
            </div>
        </form>

        <!-- Devices Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-4">#</th>
                        <th class="py-2.5 px-4">اسم الجهاز</th>
                        <th class="py-2.5 px-4">التخصص</th>
                        <th class="py-2.5 px-4">الوصف</th>
                        <th class="py-2.5 px-4 text-center">حذف</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $index => $device)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900">{{ $device->name }}</td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold 
                                    {{ $device->type === 'therapy' ? 'bg-blue-50 text-blue-700' : ($device->type === 'weight_loss' ? 'bg-purple-50 text-purple-700' : 'bg-teal-50 text-teal-700') }}">
                                    {{ $device->type_arabic }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $device->description ?: '-' }}</td>
                            <td class="py-2.5 px-4 text-center">
                                <form action="{{ route('settings.devices.destroy', $device) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الجهاز من القائمة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="حذف">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">لا توجد أجهزة مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Chronic Diseases Catalog (الأمراض المزمنة) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-r-4 border-amber-500 pr-3 mb-6">
            <div>
                <h2 class="text-base font-black text-slate-900">
                    قائمة الأمراض المزمنة والملاحظات الطبية (Checkboxes قابلة للإضافة)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">هذه القائمة تظهر كـ Checkboxes عند تسجيل المريض وتعديل ملفه الطبي لتحديد الحالات المرضية المصاحبة.</p>
            </div>
            <span class="text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                {{ count($diseases) }} حالة مرضية
            </span>
        </div>

        <!-- Add Disease Form -->
        <form action="{{ route('settings.diseases.store') }}" method="POST" class="p-4 bg-slate-50 rounded-xl border border-slate-200 mb-6 flex flex-col sm:flex-row gap-3 items-end">
            @csrf
            <div class="flex-1 w-full">
                <label class="block text-xs font-bold text-slate-700 mb-1">اسم المرض / الحالة المرضية الجديدة <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="مثال: فتق حجابي، هشاشة عظام متقدمة، حساسية كبريت..."
                       class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-xs transition whitespace-nowrap">
                + إضافة للقائمة
            </button>
        </form>

        <!-- Diseases List Badges with Delete -->
        <div class="flex flex-wrap gap-2.5">
            @forelse($diseases as $disease)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-800">
                    <span>{{ $disease->name }}</span>
                    <form action="{{ route('settings.diseases.destroy', $disease) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا المرض من القائمة؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-600 transition" title="حذف">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-slate-400">لا توجد أمراض مسجلة بالقائمة</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
