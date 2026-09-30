<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use App\Models\WeightLossPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeightLossReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. تحديد النطاق الزمني والفلاتر السريعة
        $quickFilter = $request->get('quick_filter');
        $today = Carbon::today();

        if ($quickFilter === 'today') {
            $fromDate = $today->toDateString();
            $toDate = $today->toDateString();
        } elseif ($quickFilter === 'this_week') {
            $fromDate = $today->copy()->startOfWeek()->toDateString();
            $toDate = $today->toDateString();
        } elseif ($quickFilter === 'this_month') {
            $fromDate = $today->copy()->startOfMonth()->toDateString();
            $toDate = $today->toDateString();
        } elseif ($quickFilter === 'last_month') {
            $fromDate = $today->copy()->subMonth()->startOfMonth()->toDateString();
            $toDate = $today->copy()->subMonth()->endOfMonth()->toDateString();
        } elseif ($quickFilter === 'all') {
            $fromDate = null;
            $toDate = null;
        } else {
            $fromDate = $request->filled('from_date') ? $request->from_date : null;
            $toDate = $request->filled('to_date') ? $request->to_date : null;
        }

        $status = $request->get('status');
        $collectionStatus = $request->get('collection_status');
        $search = $request->get('search');

        // 2. بناء استعلام باقات التخسيس
        $query = WeightLossPlan::with(['patient.payments', 'sessions', 'payments'])
            ->orderBy('created_at', 'desc');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($status && in_array($status, ['active', 'completed', 'cancelled'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('package_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $plans = $query->get();

        // 3. فلترة حالة التحصيل (تتم برمجياً لاعتمادها على الـ accessors الحسابية)
        if ($collectionStatus) {
            if ($collectionStatus === 'fully_paid') {
                $plans = $plans->filter(fn($p) => $p->collection_rate >= 100);
            } elseif ($collectionStatus === 'partially_paid') {
                $plans = $plans->filter(fn($p) => $p->collection_rate > 0 && $p->collection_rate < 100);
            } elseif ($collectionStatus === 'unpaid') {
                $plans = $plans->filter(fn($p) => $p->collection_rate <= 0);
            }
        }

        // 4. حساب الإجماليات والمؤشرات التجميعية (KPIs)
        $totalPlansCount = $plans->count();
        $activePlansCount = $plans->where('status', 'active')->count();
        $completedPlansCount = $plans->where('status', 'completed')->count();
        $cancelledPlansCount = $plans->where('status', 'cancelled')->count();

        $totalSessionsCount = (int) $plans->sum('total_sessions');
        $totalPaidSessionsCount = round((float) $plans->sum('paid_sessions_count'), 1);
        $totalRemainingSessionsCount = round((float) $plans->sum('remaining_sessions_to_collect'), 1);
        $totalActualCompletedSessions = (int) $plans->sum('completed_sessions_count');
        $totalUnexecutedSessions = (int) $plans->sum('remaining_sessions_count');

        $totalPlansCost = (float) $plans->sum('cost');
        $totalCollectedAmount = (float) $plans->sum('collected_amount');
        $totalRemainingAmount = (float) $plans->sum('remaining_amount');
        $averageCollectionRate = $totalPlansCost > 0 ? round(($totalCollectedAmount / $totalPlansCost) * 100, 1) : 0.0;

        $settings = ClinicSetting::getSettings();

        // 5. إذا كان الطلب للطباعة الرسمية A4
        if ($request->has('print')) {
            return view('reports.weight_loss_print', compact(
                'plans',
                'totalPlansCount',
                'activePlansCount',
                'completedPlansCount',
                'cancelledPlansCount',
                'totalSessionsCount',
                'totalPaidSessionsCount',
                'totalRemainingSessionsCount',
                'totalActualCompletedSessions',
                'totalUnexecutedSessions',
                'totalPlansCost',
                'totalCollectedAmount',
                'totalRemainingAmount',
                'averageCollectionRate',
                'fromDate',
                'toDate',
                'status',
                'collectionStatus',
                'search',
                'quickFilter',
                'settings'
            ));
        }

        return view('reports.weight_loss', compact(
            'plans',
            'totalPlansCount',
            'activePlansCount',
            'completedPlansCount',
            'cancelledPlansCount',
            'totalSessionsCount',
            'totalPaidSessionsCount',
            'totalRemainingSessionsCount',
            'totalActualCompletedSessions',
            'totalUnexecutedSessions',
            'totalPlansCost',
            'totalCollectedAmount',
            'totalRemainingAmount',
            'averageCollectionRate',
            'fromDate',
            'toDate',
            'quickFilter',
            'status',
            'collectionStatus',
            'search',
            'settings'
        ));
    }
}