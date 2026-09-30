<?php

namespace App\Http\Controllers;

use App\Models\ClinicDevice;
use App\Models\Disease;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $patients = Patient::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->with(['diseases'])
            ->withCount(['therapyPlans', 'weightLossPlans', 'weightLossSessions', 'visits'])
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients', 'search'));
    }

    public function create()
    {
        $diseases = Disease::where('is_active', true)->orderBy('name')->get();
        return view('patients.create', compact('diseases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:1|max:120',
            'medical_history' => 'nullable|string',
            'disease_ids' => 'nullable|array',
            'disease_ids.*' => 'exists:diseases,id',
            'new_disease_name' => 'nullable|string|max:150',
        ], [
            'name.required' => 'يرجى إدخال اسم المريض بالكامل.',
            'phone.required' => 'يرجى إدخال رقم الهاتف.',
            'gender.required' => 'يرجى تحديد النوع (ذكر/أنثى).',
            'age.required' => 'يرجى إدخال السن بالسنوات.',
        ]);

        $patient = Patient::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'age' => $validated['age'],
            'medical_history' => $validated['medical_history'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        $diseaseIds = $validated['disease_ids'] ?? [];

        // If user added a new disease on the fly
        if (!empty($validated['new_disease_name'])) {
            $newDisease = Disease::firstOrCreate(['name' => trim($validated['new_disease_name'])]);
            $diseaseIds[] = $newDisease->id;
        }

        if (!empty($diseaseIds)) {
            $patient->diseases()->sync(array_unique($diseaseIds));
        }

        \App\Services\ActivityLogger::log(
            action: 'create',
            module: 'patients',
            description: "تسجيل ملف مريض جديد: '{$patient->name}' (هاتف: {$patient->phone})",
            patientId: $patient->id,
            recordId: $patient->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'visits'])
            ->with('success', 'تم تسجيل ملف المريض بنجاح وفتح شاشة الكشف الطبي.');
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'diseases',
            'visits.creator',
            'therapyPlans.sessions.devices',
            'therapyPlans.sessions.attendedBy',
            'weightLossPlans.sessions.devices',
            'weightLossPlans.sessions.completedBy',
            'weightLossSessions.devices',
            'weightLossSessions.completedBy',
            'payments.therapyPlan',
            'payments.weightLossPlan',
            'payments.visit',
            'payments.creator',
            'attachments.creator',
        ]);

        $allDiseases = Disease::where('is_active', true)->orderBy('name')->get();
        $allDevices = ClinicDevice::where('is_active', true)->orderBy('name')->get();

        return view('patients.show', compact('patient', 'allDiseases', 'allDevices'));
    }

    public function edit(Patient $patient)
    {
        $diseases = Disease::where('is_active', true)->orderBy('name')->get();
        $patient->load('diseases');
        return view('patients.edit', compact('patient', 'diseases'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:1|max:120',
            'medical_history' => 'nullable|string',
            'disease_ids' => 'nullable|array',
            'disease_ids.*' => 'exists:diseases,id',
            'new_disease_name' => 'nullable|string|max:150',
        ], [
            'name.required' => 'يرجى إدخال اسم المريض بالكامل.',
            'phone.required' => 'يرجى إدخال رقم الهاتف.',
        ]);

        $patient->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'age' => $validated['age'],
            'medical_history' => $validated['medical_history'] ?? null,
        ]);

        $diseaseIds = $validated['disease_ids'] ?? [];

        if (!empty($validated['new_disease_name'])) {
            $newDisease = Disease::firstOrCreate(['name' => trim($validated['new_disease_name'])]);
            $diseaseIds[] = $newDisease->id;
        }

        $patient->diseases()->sync(array_unique($diseaseIds));

        \App\Services\ActivityLogger::log(
            action: 'update',
            module: 'patients',
            description: "تعديل البيانات الشخصية والتاريخ المرضي للحالة: '{$patient->name}'",
            patientId: $patient->id,
            recordId: $patient->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'visits'])
            ->with('success', 'تم تحديث بيانات المريض والأمراض المزمنة بنجاح.');
    }

    public function updateDiseases(Request $request, Patient $patient)
    {
        // التحقق من صحة البيانات المُرسلة — منع حقن أي ID عشوائي
        $validated = $request->validate([
            'disease_ids'      => 'nullable|array',
            'disease_ids.*'    => 'integer|exists:diseases,id',
            'new_disease_name' => 'nullable|string|max:150',
        ], [
            'disease_ids.*.exists'    => 'أحد الأمراض المحددة غير موجود في القائمة.',
            'new_disease_name.max'    => 'اسم المرض الجديد لا يجب أن يتجاوز 150 حرف.',
        ]);

        $diseaseIds = $validated['disease_ids'] ?? [];

        // Check if a new disease was typed
        if ($newDiseaseName = trim($validated['new_disease_name'] ?? '')) {
            $newDisease = Disease::firstOrCreate(['name' => $newDiseaseName]);
            $diseaseIds[] = $newDisease->id;
        }

        $patient->diseases()->sync(array_unique($diseaseIds));

        \App\Services\ActivityLogger::log(
            action: 'update',
            module: 'patients',
            description: "تحديث قائمة الأمراض المزمنة للحالة: '{$patient->name}'",
            patientId: $patient->id,
            recordId: $patient->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'visits'])
            ->with('success', 'تم تحديث قائمة الأمراض المزمنة للحالة بنجاح.');
    }

    public function destroy(Patient $patient)
    {
        $name = $patient->name;
        $id = $patient->id;

        \App\Services\ActivityLogger::log(
            action: 'delete',
            module: 'patients',
            description: "حذف ملف المريض بالكامل وكافة بياناته: '{$name}' (كود: #{$id})",
            recordId: $id
        );

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'تم حذف ملف المريض وكافة سجلاته المرتبطة بنجاح.');
    }
}
