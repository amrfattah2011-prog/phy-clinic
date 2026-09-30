<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\TherapyPlan;
use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TherapyPlanController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'total_sessions' => 'required|integer|min:1|max:100',
            'cost' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ], [
            'package_name.required' => 'يرجى إدخال اسم باقة العلاج الطبيعي.',
            'total_sessions.required' => 'يرجى تحديد عدد الجلسات.',
            'cost.required' => 'يرجى تحديد التكلفة الإجمالية للباقة.',
        ]);

        $plan = $patient->therapyPlans()->create([
            'package_name' => $validated['package_name'],
            'total_sessions' => $validated['total_sessions'],
            'cost' => $validated['cost'],
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-generate session slots — Bulk INSERT بدلاً من N استعلام منفصل
        $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date']) : Carbon::today();
        $now = now();
        $sessions = [];
        for ($i = 1; $i <= $validated['total_sessions']; $i++) {
            $sessions[] = [
                'therapy_plan_id'  => $plan->id,
                'patient_id'       => $patient->id,
                'session_number'   => $i,
                'session_date'     => (clone $startDate)->addDays(($i - 1) * 2)->toDateString(),
                'is_attended'      => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }
        TherapySession::insert($sessions); // INSERT واحد لكل الجلسات ✅


        \App\Services\ActivityLogger::log(
            action: 'create',
            module: 'therapy',
            description: "إنشاء باقة علاج طبيعي جديدة '{$plan->package_name}' ({$plan->total_sessions} جلسات) للمريض: '{$patient->name}' بقيمة {$plan->cost} ج.م",
            patientId: $patient->id,
            recordId: $plan->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'therapy'])
            ->with('success', 'تم إنشاء باقة العلاج الطبيعي وتوليد الجلسات بنجاح.');
    }

    public function destroy(TherapyPlan $plan)
    {
        $patientId = $plan->patient_id;
        $patientName = $plan->patient?->name;
        $packageName = $plan->package_name;
        $id = $plan->id;

        \App\Services\ActivityLogger::log(
            action: 'delete',
            module: 'therapy',
            description: "حذف باقة العلاج الطبيعي '{$packageName}' وجلساتها للمريض: '{$patientName}'",
            patientId: $patientId,
            recordId: $id
        );

        $plan->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'therapy'])
            ->with('success', 'تم حذف باقة العلاج الطبيعي وجلساتها.');
    }
}
