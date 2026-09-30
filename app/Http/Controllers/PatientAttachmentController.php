<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientAttachment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientAttachmentController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'category'      => 'required|string|in:xray,lab,report,photo,prescription,general',
            'document_date' => 'required|date',
            'notes'         => 'nullable|string|max:1000',
            'file'          => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:25600', // الحد الأقصى 25 ميجابايت
        ], [
            'title.required'         => 'يرجى كتابة عنوان أو وصف المرفق الطبي.',
            'category.required'      => 'يرجى تحديد تصنيف المرفق (أشعة، تحاليل، تقرير...).',
            'document_date.required' => 'يرجى تحديد تاريخ المرفق / الفحص الطبي.',
            'file.required'          => 'يرجى اختيار ملف الصورة أو ملف الـ PDF لرفعه.',
            'file.mimes'             => 'الملفات المسموح برفعها هي صور (JPG, PNG, WEBP) أو ملفات PDF فقط.',
            'file.max'               => 'حجم الملف كبير جداً، الحد الأقصى المسموح به هو 25 ميجابايت.',
        ]);

        $file = $request->file('file');
        $folder = 'patient_attachments/' . $patient->id;

        // ضغط ومعالجة الصورة أو الملف لتوفير مساحة التخزين وحماية قاعدة البيانات
        $compressed = \App\Services\FileCompressor::compressAndStore($file, $folder, 'public');

        $attachment = $patient->attachments()->create([
            'title'              => $validated['title'],
            'category'           => $validated['category'],
            'document_date'      => $validated['document_date'],
            'file_path'          => $compressed['path'],
            'original_name'      => $file->getClientOriginalName(),
            'file_type'          => $compressed['file_type'],
            'mime_type'          => $compressed['mime_type'],
            'file_size'          => $compressed['file_size'],
            'original_size'      => $compressed['original_size'],
            'notes'              => $validated['notes'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        ActivityLogger::log(
            action: 'create',
            module: 'patients',
            description: "إضافة مرفق طبي جديد ({$attachment->category_name}: '{$attachment->title}') بتاريخ {$attachment->document_date->format('Y-m-d')} للمريض: '{$patient->name}'",
            patientId: $patient->id,
            recordId: $attachment->id
        );

        $successMsg = $compressed['compression_ratio'] > 5
            ? "تم رفع وضغط المرفق الطبي بنجاح (تم تقليص الحجم بنسبة {$compressed['compression_ratio']} % لتوفير المساحة)."
            : 'تم رفع وحفظ المرفق الطبي بنجاح.';

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'attachments'])
            ->with('success', $successMsg);
    }

    /**
     * استعراض ومعاينة الملف المرفق (صورة أو PDF) مباشرة في المتصفح
     */
    public function view(Patient $patient, PatientAttachment $attachment)
    {
        if ($attachment->patient_id !== $patient->id) {
            abort(404, 'المرفق غير موجود لهذا المريض.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'الملف غير موجود على الخادم.');
        }

        $fullPath = Storage::disk('public')->path($attachment->file_path);

        return response()->file($fullPath, [
            'Content-Type'        => $attachment->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"',
        ]);
    }

    /**
     * تحميل الملف المرفق للكمبيوتر
     */
    public function download(Patient $patient, PatientAttachment $attachment)
    {
        if ($attachment->patient_id !== $patient->id) {
            abort(404, 'المرفق غير موجود لهذا المريض.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'الملف المطلوب غير موجود.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    /**
     * حذف المرفق الطبي والملف من الخادم
     */
    public function destroy(Patient $patient, PatientAttachment $attachment)
    {
        if ($attachment->patient_id !== $patient->id) {
            abort(404);
        }

        $title = $attachment->title;
        $id = $attachment->id;

        // حذف الملف الفعلي من القرص
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        ActivityLogger::log(
            action: 'delete',
            module: 'patients',
            description: "حذف المرفق الطبي ('{$title}') للمريض: '{$patient->name}'",
            patientId: $patient->id,
            recordId: $id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'attachments'])
            ->with('success', 'تم حذف المرفق الطبي بنجاح.');
    }
}