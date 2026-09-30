<?php

namespace App\Http\Controllers;

use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TherapySessionController extends Controller
{
    public function toggleAttendance(Request $request, TherapySession $session)
    {
        $session->is_attended = !$session->is_attended;
        $session->attended_at = $session->is_attended ? Carbon::now() : null;
        $session->attended_by_user_id = $session->is_attended ? auth()->id() : null;
        $session->save();

        $plan = $session->plan;
        $patient = $session->patient;
        
        // Auto-complete plan if all sessions are attended
        if ($plan && $plan->completed_sessions_count >= $plan->total_sessions) {
            $plan->update(['status' => 'completed']);
        } elseif ($plan && $plan->status === 'completed' && $plan->completed_sessions_count < $plan->total_sessions) {
            $plan->update(['status' => 'active']);
        }

        \App\Services\ActivityLogger::log(
            action: 'attendance',
            module: 'therapy',
            description: ($session->is_attended ? "تسجيل حضور" : "إلغاء حضور") . " جلسة علاج طبيعي رقم {$session->session_number} من باقة '{$plan?->package_name}' للمريض: '{$patient?->name}'",
            patientId: $session->patient_id,
            recordId: $session->id
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_attended' => $session->is_attended,
                'attended_at' => $session->attended_at ? $session->attended_at->format('Y-m-d H:i') : null,
                'attended_at_arabic' => $session->attended_at ? $session->attended_at->diffForHumans() : 'لم تحضر بعد',
                'completed_count' => $plan ? $plan->completed_sessions_count : 0,
                'remaining_count' => $plan ? $plan->remaining_sessions_count : 0,
                'progress_percentage' => $plan ? $plan->progress_percentage : 0,
                'plan_status' => $plan ? $plan->status : 'active',
                'plan_status_arabic' => $plan ? $plan->status_arabic : '',
                'message' => $session->is_attended 
                    ? "تم تسجيل حضور الجلسة رقم {$session->session_number} بنجاح." 
                    : "تم إلغاء حضور الجلسة رقم {$session->session_number}.",
            ]);
        }

        return back()->with('success', $session->is_attended 
            ? "تم تسجيل حضور الجلسة رقم {$session->session_number} بنجاح." 
            : "تم إلغاء حضور الجلسة رقم {$session->session_number}.");
    }

    public function update(Request $request, TherapySession $session)
    {
        $validated = $request->validate([
            'session_date' => 'required|date',
            'notes' => 'nullable|string',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:clinic_devices,id',
        ]);

        $session->update([
            'session_date' => $validated['session_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if (isset($validated['device_ids'])) {
            $session->devices()->sync($validated['device_ids']);
        }

        return back()->with('success', "تم تحديث بيانات الجلسة رقم {$session->session_number} والأجهزة الطبية المستخدمة.");
    }

    public function updateDevices(Request $request, TherapySession $session)
    {
        $deviceIds = $request->input('device_ids', []);
        $session->devices()->sync($deviceIds);

        \App\Services\ActivityLogger::log(
            action: 'update',
            module: 'therapy',
            description: "تحديث قائمة الأجهزة الطبية لجلسة علاج طبيعي رقم {$session->session_number} للمريض: '{$session->patient?->name}'",
            patientId: $session->patient_id,
            recordId: $session->id
        );

        if ($request->wantsJson() || $request->ajax()) {
            $session->load('devices');
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الأجهزة الطبية المستخدمة في الجلسة بنجاح.',
                'devices' => $session->devices->pluck('name'),
            ]);
        }

        return back()->with('success', 'تم حفظ الأجهزة الطبية المستخدمة في الجلسة بنجاح.');
    }
}
