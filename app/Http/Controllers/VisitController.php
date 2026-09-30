<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'complaint' => 'required|string',
            'diagnosis' => 'required|string',
            'doctor_notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ], [
            'visit_date.required' => 'يرجى تحديد تاريخ الكشف.',
            'complaint.required' => 'يرجى كتابة شكوى المريض.',
            'diagnosis.required' => 'يرجى كتابة التشخيص الطبي.',
        ]);

        $visit = $patient->visits()->create([
            'visit_date' => $validated['visit_date'],
            'complaint' => $validated['complaint'],
            'diagnosis' => $validated['diagnosis'],
            'doctor_notes' => $validated['doctor_notes'] ?? null,
            'cost' => $validated['cost'] ?? 0.00,
            'created_by_user_id' => auth()->id(),
        ]);

        \App\Services\ActivityLogger::log(
            action: 'create',
            module: 'visits',
            description: "تسجيل كشف طبي جديد للمريض: '{$patient->name}' - التشخيص: '{$visit->diagnosis}' بمبلغ: {$visit->cost} ج.م",
            patientId: $patient->id,
            recordId: $visit->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'visits'])
            ->with('success', 'تم تسجيل الكشف والتشخيص الطبي بنجاح.');
    }

    public function destroy(Visit $visit)
    {
        $patientId = $visit->patient_id;
        $patientName = $visit->patient?->name;
        $diagnosis = $visit->diagnosis;
        $id = $visit->id;

        \App\Services\ActivityLogger::log(
            action: 'delete',
            module: 'visits',
            description: "إلغاء وحذف كشف طبي للمريض: '{$patientName}' - التشخيص السابق: '{$diagnosis}'",
            patientId: $patientId,
            recordId: $id
        );

        $visit->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'visits'])
            ->with('success', 'تم حذف سجل الكشف بنجاح.');
    }
}
