<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\WeightLossPlan;
use App\Models\WeightLossSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeightLossPlanController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'total_sessions' => 'required|integer|min:1|max:50',
            'cost' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'default_injection' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ], [
            'package_name.required' => 'يرجى إدخال اسم باقة التخسيس.',
            'total_sessions.required' => 'يرجى تحديد عدد الجلسات.',
            'cost.required' => 'يرجى تحديد التكلفة الإجمالية للباقة.',
        ]);

        $plan = $patient->weightLossPlans()->create([
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
                'patient_id'          => $patient->id,
                'weight_loss_plan_id' => $plan->id,
                'session_number'      => $i,
                'session_date'        => (clone $startDate)->addDays(($i - 1) * 7)->toDateString(),
                'injection_type'      => $validated['default_injection'] ?? null,
                'is_completed'        => false,
                'cost'                => 0.00,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }
        WeightLossSession::insert($sessions); // INSERT واحد لكل الجلسات ✅


        \App\Services\ActivityLogger::log(
            action: 'create',
            module: 'weight_loss',
            description: "إنشاء باقة تخسيس ونحت قوام جديدة '{$plan->package_name}' ({$plan->total_sessions} جلسات) للمريض: '{$patient->name}' بمبلغ {$plan->cost} ج.م",
            patientId: $patient->id,
            recordId: $plan->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'weight-loss'])
            ->with('success', 'تم إنشاء باقة التخسيس وتوليد جلساتها بنجاح.');
    }

    public function destroy(WeightLossPlan $plan)
    {
        $patientId = $plan->patient_id;
        $patientName = $plan->patient?->name;
        $packageName = $plan->package_name;
        $id = $plan->id;

        \App\Services\ActivityLogger::log(
            action: 'delete',
            module: 'weight_loss',
            description: "حذف باقة التخسيس '{$packageName}' وجلساتها للمريض: '{$patientName}'",
            patientId: $patientId,
            recordId: $id
        );

        $plan->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'weight-loss'])
            ->with('success', 'تم حذف باقة التخسيس وجلساتها.');
    }
}
