<?php

namespace App\Http\Controllers;

use App\Models\ClinicDevice;
use App\Models\ClinicSetting;
use App\Models\Disease;
use Illuminate\Http\Request;

class ClinicSettingController extends Controller
{
    public function edit()
    {
        $settings = ClinicSetting::getSettings();
        $diseases = Disease::orderBy('name', 'asc')->get();
        $devices = ClinicDevice::orderBy('name', 'asc')->get();

        return view('settings.edit', compact('settings', 'diseases', 'devices'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => 'required|string|max:255',
            'doctor_name' => 'required|string|max:255',
            'doctor_title' => 'required|string|max:255',
            'phone_1' => 'required|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'address' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'receipt_footer' => 'nullable|string',
            'currency' => 'required|string|max:20',
        ], [
            'clinic_name.required' => 'يرجى إدخال اسم العيادة / المركز.',
            'doctor_name.required' => 'يرجى إدخال اسم الطبيب.',
            'phone_1.required' => 'يرجى إدخال رقم الهاتف الرئيسي.',
            'address.required' => 'يرجى إدخال عنوان المركز.',
        ]);

        $settings = ClinicSetting::getSettings();
        $settings->update($validated);

        // مسح الكاش فور الحفظ حتى تظهر البيانات الجديدة فوراً في كل الصفحات
        \Illuminate\Support\Facades\Cache::forget('clinic_settings');

        \App\Services\ActivityLogger::log(
            action: 'update',
            module: 'settings',
            description: "تحديث بيانات وهوية المركز: '{$settings->clinic_name}' والطبيب المسؤول: '{$settings->doctor_name}'",
            recordId: $settings->id
        );

        return redirect()->route('settings.edit')
            ->with('success', 'تم حفظ وتحديث بيانات المركز بنجاح.');
    }

    public function storeDisease(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:diseases,name',
        ], [
            'name.required' => 'يرجى كتابة اسم المرض.',
            'name.unique' => 'هذا المرض مسجل مسبقاً في القائمة.',
        ]);

        Disease::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة المرض الجديد إلى قائمة الأمراض بنجاح.');
    }

    public function destroyDisease(Disease $disease)
    {
        $disease->delete();

        return back()->with('success', 'تم حذف المرض من القائمة.');
    }

    public function storeDevice(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:clinic_devices,name',
            'type' => 'required|in:therapy,weight_loss,both',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'يرجى كتابة اسم الجهاز الطبي.',
            'name.unique' => 'هذا الجهاز مسجل مسبقاً.',
        ]);

        ClinicDevice::create($validated);

        return back()->with('success', 'تمت إضافة الجهاز الطبي الجديد إلى القائمة بنجاح.');
    }

    public function destroyDevice(ClinicDevice $device)
    {
        $device->delete();

        return back()->with('success', 'تم حذف الجهاز من القائمة.');
    }
}
