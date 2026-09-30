<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\WeightLossSession;
use App\Services\ActivityLogger;
use App\Services\FileCompressor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WeightLossSessionController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'weight_loss_plan_id' => 'nullable|exists:weight_loss_plans,id',
            'session_date' => 'required|date',
            'weight' => 'nullable|numeric|min:20|max:400',
            'height' => 'nullable|numeric|min:50|max:250',
            'dosage_amount' => 'nullable|string|max:100',
            'injection_type' => 'nullable|string|max:100',
            'doctor_notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'is_completed' => 'nullable|boolean',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:clinic_devices,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
        ]);

        $isCompleted = !empty($validated['is_completed']);

        $session = $patient->weightLossSessions()->create([
            'weight_loss_plan_id' => $validated['weight_loss_plan_id'] ?? null,
            'session_date' => $validated['session_date'],
            'weight' => $validated['weight'] ?? null,
            'height' => $validated['height'] ?? null,
            'dosage_amount' => $validated['dosage_amount'] ?? null,
            'injection_type' => $validated['injection_type'] ?? null,
            'doctor_notes' => $validated['doctor_notes'] ?? null,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? Carbon::now() : null,
            'completed_by_user_id' => $isCompleted ? auth()->id() : null,
        ]);

        if (!empty($validated['device_ids'])) {
            $session->devices()->sync($validated['device_ids']);
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $folder = "weight_loss_sessions/{$session->id}";
            $compressed = FileCompressor::compressAndStore($file, $folder, 'public');

            $session->update([
                'attachment_path'    => $compressed['path'],
                'attachment_name'    => $file->getClientOriginalName(),
                'attachment_type'    => $compressed['file_type'],
                'attachment_mime'    => $compressed['mime_type'],
                'attachment_size'    => $compressed['file_size'],
                'original_size'      => $compressed['original_size'],
                'compression_ratio'  => $compressed['compression_ratio'],
            ]);
        }

        \App\Services\ActivityLogger::log(
            action: 'create',
            module: 'weight_loss',
            description: "تسجيل جلسة تخسيس جديدة للمريض: '{$patient->name}' - الوزن: {$session->weight} كجم" . ($session->injection_type ? " - نوع الحقنة: {$session->injection_type}" : ""),
            patientId: $patient->id,
            recordId: $session->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'weight-loss'])
            ->with('success', 'تم تسجيل جلسة التخسيس والجرعة بنجاح.');
    }

    public function toggleCompleted(Request $request, WeightLossSession $session)
    {
        $session->is_completed = !$session->is_completed;
        $session->completed_at = $session->is_completed ? Carbon::now() : null;
        $session->completed_by_user_id = $session->is_completed ? auth()->id() : null;
        $session->save();

        $plan = $session->plan;
        $patient = $session->patient;

        if ($plan) {
            if ($plan->completed_sessions_count >= $plan->total_sessions) {
                $plan->update(['status' => 'completed']);
            } elseif ($plan->status === 'completed' && $plan->completed_sessions_count < $plan->total_sessions) {
                $plan->update(['status' => 'active']);
            }
        }

        \App\Services\ActivityLogger::log(
            action: 'dosage',
            module: 'weight_loss',
            description: ($session->is_completed ? "تأكيد أخذ جرعة / إتمام" : "إلغاء تأكيد") . " جلسة تخسيس رقم {$session->session_number} للمريض: '{$patient?->name}'",
            patientId: $session->patient_id,
            recordId: $session->id
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_completed' => $session->is_completed,
                'completed_at' => $session->completed_at ? $session->completed_at->format('Y-m-d H:i') : null,
                'completed_at_arabic' => $session->completed_at ? $session->completed_at->diffForHumans() : 'معلقة',
                'completed_count' => $plan ? $plan->completed_sessions_count : 0,
                'remaining_count' => $plan ? $plan->remaining_sessions_count : 0,
                'progress_percentage' => $plan ? $plan->progress_percentage : 0,
                'plan_status' => $plan ? $plan->status : 'active',
                'plan_status_arabic' => $plan ? $plan->status_arabic : '',
                'message' => $session->is_completed 
                    ? 'تم تأكيد أخذ الجرعة والجلسة بنجاح.' 
                    : 'تم إلغاء تأكيد الجرعة.',
            ]);
        }

        return back()->with('success', $session->is_completed 
            ? 'تم تأكيد أخذ الجرعة بنجاح.' 
            : 'تم إلغاء تأكيد الجرعة.');
    }

    public function update(Request $request, WeightLossSession $session)
    {
        $validated = $request->validate([
            'session_date' => 'required|date',
            'weight' => 'nullable|numeric|min:20|max:400',
            'height' => 'nullable|numeric|min:50|max:250',
            'dosage_amount' => 'nullable|string|max:100',
            'injection_type' => 'nullable|string|max:100',
            'doctor_notes' => 'nullable|string',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:clinic_devices,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
        ]);

        $session->update([
            'session_date' => $validated['session_date'],
            'weight' => $validated['weight'] ?? null,
            'height' => $validated['height'] ?? null,
            'dosage_amount' => $validated['dosage_amount'] ?? null,
            'injection_type' => $validated['injection_type'] ?? null,
            'doctor_notes' => $validated['doctor_notes'] ?? null,
        ]);

        if (isset($validated['device_ids'])) {
            $session->devices()->sync($validated['device_ids']);
        }

        if ($request->hasFile('attachment')) {
            if ($session->attachment_path && Storage::disk('public')->exists($session->attachment_path)) {
                Storage::disk('public')->delete($session->attachment_path);
            }

            $file = $request->file('attachment');
            $folder = "weight_loss_sessions/{$session->id}";
            $compressed = FileCompressor::compressAndStore($file, $folder, 'public');

            $session->update([
                'attachment_path'    => $compressed['path'],
                'attachment_name'    => $file->getClientOriginalName(),
                'attachment_type'    => $compressed['file_type'],
                'attachment_mime'    => $compressed['mime_type'],
                'attachment_size'    => $compressed['file_size'],
                'original_size'      => $compressed['original_size'],
                'compression_ratio'  => $compressed['compression_ratio'],
            ]);

            \App\Services\ActivityLogger::log(
                action: 'attachment',
                module: 'weight_loss',
                description: "رفع وضغط مرفق جديد لجلسة تخسيس رقم {$session->session_number} للمريض: '{$session->patient?->name}' - الملف: {$file->getClientOriginalName()} (وفر {$compressed['compression_ratio']}%)",
                patientId: $session->patient_id,
                recordId: $session->id
            );
        }

        return back()->with('success', "تم تحديث بيانات الجلسة رقم {$session->session_number}" . ($request->hasFile('attachment') ? ' والمرفق ' : '') . "بنجاح.");
    }

    /**
     * رفع وضغط ملف / صورة للجلسة بشكل مخصص وفوري
     */
    public function uploadAttachment(Request $request, WeightLossSession $session)
    {
        $request->validate([
            'attachment' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
        ]);

        // حذف الملف القديم إن وُجد
        if ($session->attachment_path && Storage::disk('public')->exists($session->attachment_path)) {
            Storage::disk('public')->delete($session->attachment_path);
        }

        $file = $request->file('attachment');
        $folder = "weight_loss_sessions/{$session->id}";
        $compressed = FileCompressor::compressAndStore($file, $folder, 'public');

        $session->update([
            'attachment_path'    => $compressed['path'],
            'attachment_name'    => $file->getClientOriginalName(),
            'attachment_type'    => $compressed['file_type'],
            'attachment_mime'    => $compressed['mime_type'],
            'attachment_size'    => $compressed['file_size'],
            'original_size'      => $compressed['original_size'],
            'compression_ratio'  => $compressed['compression_ratio'],
        ]);

        \App\Services\ActivityLogger::log(
            action: 'attachment',
            module: 'weight_loss',
            description: "إرفاق وضغط ملف لجلسة تخسيس رقم {$session->session_number} للمريض: '{$session->patient?->name}' - الملف: {$file->getClientOriginalName()}",
            patientId: $session->patient_id,
            recordId: $session->id
        );

        $savingsMsg = $compressed['compression_ratio'] > 5 
            ? "تم ضغط المرفق بنجاح وتوفير مساحة بنسبة {$compressed['compression_ratio']}%."
            : "تم رفع وحفظ المرفق بنجاح.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $savingsMsg,
                'attachment_url' => $session->attachment_url,
                'view_url' => $session->view_url,
                'download_url' => $session->download_url,
                'attachment_name' => $session->attachment_name,
                'attachment_type' => $session->attachment_type,
                'attachment_size' => $session->formatted_attachment_size,
                'savings' => $session->compression_savings_percentage,
            ]);
        }

        return back()->with('success', $savingsMsg);
    }

    /**
     * استعراض الملف المرفق مباشرة في المتصفح أو Lightbox
     */
    public function viewAttachment(WeightLossSession $session)
    {
        if (!$session->attachment_path || !Storage::disk('public')->exists($session->attachment_path)) {
            abort(404, 'المرفق غير موجود أو تم حذفه.');
        }

        $fullPath = Storage::disk('public')->path($session->attachment_path);

        return response()->file($fullPath, [
            'Content-Type'        => $session->attachment_mime ?: mime_content_type($fullPath),
            'Content-Disposition' => 'inline; filename="' . addslashes($session->attachment_name ?: basename($fullPath)) . '"',
        ]);
    }

    /**
     * تحميل المرفق إلى جهاز المستخدم
     */
    public function downloadAttachment(WeightLossSession $session)
    {
        if (!$session->attachment_path || !Storage::disk('public')->exists($session->attachment_path)) {
            abort(404, 'المرفق غير موجود أو تم حذفه.');
        }

        return Storage::disk('public')->download($session->attachment_path, $session->attachment_name ?: 'session-attachment');
    }

    /**
     * حذف الملف المرفق من الجلسة
     */
    public function destroyAttachment(WeightLossSession $session)
    {
        if ($session->attachment_path && Storage::disk('public')->exists($session->attachment_path)) {
            Storage::disk('public')->delete($session->attachment_path);
        }

        $oldName = $session->attachment_name;

        $session->update([
            'attachment_path'    => null,
            'attachment_name'    => null,
            'attachment_type'    => null,
            'attachment_mime'    => null,
            'attachment_size'    => null,
            'original_size'      => null,
            'compression_ratio'  => 0.00,
        ]);

        \App\Services\ActivityLogger::log(
            action: 'delete_attachment',
            module: 'weight_loss',
            description: "حذف مرفق جلسة تخسيس رقم {$session->session_number} للمريض: '{$session->patient?->name}' - الملف: {$oldName}",
            patientId: $session->patient_id,
            recordId: $session->id
        );

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف مرفق الجلسة بنجاح.',
            ]);
        }

        return back()->with('success', 'تم حذف مرفق الجلسة بنجاح.');
    }

    public function updateDevices(Request $request, WeightLossSession $session)
    {
        $deviceIds = $request->input('device_ids', []);
        $session->devices()->sync($deviceIds);

        \App\Services\ActivityLogger::log(
            action: 'update',
            module: 'weight_loss',
            description: "تحديث قائمة الأجهزة الطبية لجلسة تخسيس رقم {$session->session_number} للمريض: '{$session->patient?->name}'",
            patientId: $session->patient_id,
            recordId: $session->id
        );

        if ($request->wantsJson() || $request->ajax()) {
            $session->load('devices');
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ أجهزة جلسة التخسيس بنجاح.',
                'devices' => $session->devices->pluck('name'),
            ]);
        }

        return back()->with('success', 'تم حفظ الأجهزة الطبية المستخدمة في الجلسة بنجاح.');
    }

    public function destroy(WeightLossSession $session)
    {
        $patientId = $session->patient_id;
        $patientName = $session->patient?->name;
        $num = $session->session_number;
        $id = $session->id;

        // حذف المرفق إن وجد
        if ($session->attachment_path && Storage::disk('public')->exists($session->attachment_path)) {
            Storage::disk('public')->delete($session->attachment_path);
        }

        \App\Services\ActivityLogger::log(
            action: 'delete',
            module: 'weight_loss',
            description: "حذف جلسة التخسيس رقم {$num} للمريض: '{$patientName}'",
            patientId: $patientId,
            recordId: $id
        );

        $session->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'weight-loss'])
            ->with('success', 'تم حذف جلسة التخسيس بنجاح.');
    }
}
