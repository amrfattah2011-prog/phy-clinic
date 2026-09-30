<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\TherapySession;
use App\Models\User;
use App\Models\Visit;
use App\Models\WeightLossSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MedicalReportController extends Controller
{
    public function operations(Request $request)
    {
        $fromDate = $request->filled('from_date') ? $request->from_date : Carbon::today()->startOfMonth()->toDateString();
        $toDate = $request->filled('to_date') ? $request->to_date : Carbon::today()->toDateString();
        $type = $request->get('type', 'all');
        $userId = $request->get('user_id');
        $search = $request->get('search');

        $operations = collect();

        // 1. Visits (الكشوفات الطبية)
        if ($type === 'all' || $type === 'visits') {
            $visitsQuery = Visit::with(['patient', 'creator.role'])
                ->whereDate('visit_date', '>=', $fromDate)
                ->whereDate('visit_date', '<=', $toDate);

            if ($userId) {
                $visitsQuery->where('created_by_user_id', $userId);
            }

            if ($search) {
                $visitsQuery->whereHas('patient', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $visits = $visitsQuery->get()->map(function ($visit) {
                return (object) [
                    'id' => $visit->id,
                    'type' => 'visit',
                    'type_label' => 'كشف طبي',
                    'badge_color' => 'blue',
                    'date' => $visit->visit_date ? Carbon::parse($visit->visit_date)->toDateString() : '',
                    'created_at' => $visit->created_at,
                    'patient' => $visit->patient,
                    'staff_name' => $visit->creator?->name ?? 'غير محدد',
                    'staff_role' => $visit->creator?->role?->display_name ?? 'طاقم العمل',
                    'title' => ($visit->type === 'consultation' ? 'كشف أول مرة' : 'متابعة كشف') . ($visit->cost > 0 ? ' (رسوم: ' . number_format($visit->cost, 2) . ' ج.م)' : ' (متابعة مجانية)'),
                    'details' => 'التشخيص: ' . ($visit->diagnosis ?: 'لم يسجل') . ($visit->prescription ? ' | الروشتة: ' . $visit->prescription : ''),
                    'devices' => '-',
                    'notes' => $visit->notes,
                ];
            });

            $operations = $operations->concat($visits);
        }

        // 2. Therapy Sessions (جلسات العلاج الطبيعي المنفذة)
        if ($type === 'all' || $type === 'therapy') {
            $therapyQuery = TherapySession::with(['therapyPlan.patient', 'attendedBy.role', 'devices'])
                ->where('is_attended', true)
                ->where(function ($q) use ($fromDate, $toDate) {
                    $q->where(function ($sq) use ($fromDate, $toDate) {
                        $sq->whereNotNull('attended_at')
                           ->whereDate('attended_at', '>=', $fromDate)
                           ->whereDate('attended_at', '<=', $toDate);
                    })->orWhere(function ($sq) use ($fromDate, $toDate) {
                        $sq->whereNull('attended_at')
                           ->whereDate('session_date', '>=', $fromDate)
                           ->whereDate('session_date', '<=', $toDate);
                    });
                });

            if ($userId) {
                $therapyQuery->where('attended_by_user_id', $userId);
            }

            if ($search) {
                $therapyQuery->whereHas('therapyPlan.patient', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $therapySessions = $therapyQuery->get()->map(function ($session) {
                $effectiveDate = $session->attended_at ? Carbon::parse($session->attended_at)->toDateString() : ($session->session_date ? Carbon::parse($session->session_date)->toDateString() : Carbon::parse($session->created_at)->toDateString());
                $devicesList = $session->devices->pluck('name')->join('، ');

                return (object) [
                    'id' => $session->id,
                    'type' => 'therapy',
                    'type_label' => 'جلسة علاج طبيعي',
                    'badge_color' => 'emerald',
                    'date' => $effectiveDate,
                    'created_at' => $session->attended_at ?? $session->created_at,
                    'patient' => $session->therapyPlan?->patient,
                    'staff_name' => $session->attendedBy?->name ?? 'غير محدد',
                    'staff_role' => $session->attendedBy?->role?->display_name ?? 'أخصائي علاج طبيعي',
                    'title' => "جلسة علاج طبيعي رقم {$session->session_number} من باقة ({$session->therapyPlan?->total_sessions} جلسة)",
                    'details' => $session->devices->count() > 0 ? "الأجهزة المستخدمة: {$devicesList}" : 'بدون أجهزة مسجلة',
                    'devices' => $devicesList ?: 'لا يوجد',
                    'notes' => $session->notes,
                ];
            });

            $operations = $operations->concat($therapySessions);
        }

        // 3. Weight Loss Sessions (جلسات التخسيس ونحت القوام المنفذة)
        if ($type === 'all' || $type === 'weight_loss') {
            $weightLossQuery = WeightLossSession::with(['weightLossPlan.patient', 'completedBy.role', 'devices'])
                ->where('is_completed', true)
                ->whereDate('session_date', '>=', $fromDate)
                ->whereDate('session_date', '<=', $toDate);

            if ($userId) {
                $weightLossQuery->where('completed_by_user_id', $userId);
            }

            if ($search) {
                $weightLossQuery->whereHas('weightLossPlan.patient', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $weightLossSessions = $weightLossQuery->get()->map(function ($session) {
                $devicesList = $session->devices->pluck('name')->join('، ');
                $injectionInfo = $session->dosage_amount ? " | جرعة: {$session->dosage_amount}" . ($session->injection_type ? " ({$session->injection_type})" : '') : '';
                $weightInfo = $session->weight ? " | الوزن: {$session->weight} كجم" : '';

                return (object) [
                    'id' => $session->id,
                    'type' => 'weight_loss',
                    'type_label' => 'جلسة تخسيس ونحت قوام',
                    'badge_color' => 'purple',
                    'date' => Carbon::parse($session->session_date)->toDateString(),
                    'created_at' => $session->created_at,
                    'patient' => $session->weightLossPlan?->patient,
                    'staff_name' => $session->completedBy?->name ?? 'غير محدد',
                    'staff_role' => $session->completedBy?->role?->display_name ?? 'طاقم التخسيس',
                    'title' => "جلسة تخسيس رقم {$session->session_number} من باقة (" . ($session->weightLossPlan?->package_name ?? 'تخسيس') . "){$weightInfo}{$injectionInfo}",
                    'details' => $session->devices->count() > 0 ? "الأجهزة المستخدمة: {$devicesList}" : 'بدون أجهزة مسجلة',
                    'devices' => $devicesList ?: 'لا يوجد',
                    'notes' => $session->doctor_notes,
                ];
            });

            $operations = $operations->concat($weightLossSessions);
        }

        // Sort all operations chronologically descending
        $sortedOperations = $operations->sortByDesc(function ($item) {
            return $item->date . ' ' . ($item->created_at ? $item->created_at->toTimeString() : '00:00:00');
        })->values();

        // Calculate summary counters for the filtered period
        $visitsCount = $operations->where('type', 'visit')->count();
        $therapyCount = $operations->where('type', 'therapy')->count();
        $weightLossCount = $operations->where('type', 'weight_loss')->count();
        $totalOperations = $sortedOperations->count();

        // Unique patients served
        $uniquePatientsCount = $sortedOperations->pluck('patient.id')->filter()->unique()->count();

        $users = User::orderBy('name')->get();

        return view('reports.medical_operations', compact(
            'sortedOperations',
            'visitsCount',
            'therapyCount',
            'weightLossCount',
            'totalOperations',
            'uniquePatientsCount',
            'users',
            'fromDate',
            'toDate',
            'type',
            'userId',
            'search'
        ));
    }
}
