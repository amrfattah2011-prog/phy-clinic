<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\TherapySession;
use App\Models\Visit;
use App\Models\WeightLossSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Statistics
        $totalPatientsCount = Patient::count();
        $todayPatientsCount = Patient::whereDate('created_at', $today)->count();
        
        $todayRevenue = Payment::whereDate('payment_date', $today)->sum('amount');
        
        $todayTherapySessionsCount = TherapySession::whereDate('session_date', $today)->count();
        $todayAttendedTherapySessionsCount = TherapySession::whereDate('session_date', $today)
            ->where('is_attended', true)
            ->count();
        $todayPendingTherapySessionsCount = TherapySession::whereDate('session_date', $today)
            ->where('is_attended', false)
            ->count();

        $todayWeightLossSessionsCount = WeightLossSession::whereDate('session_date', $today)->count();
        $todayCompletedWeightLossCount = WeightLossSession::whereDate('session_date', $today)
            ->where('is_completed', true)
            ->count();
        $todayPendingWeightLossCount = WeightLossSession::whereDate('session_date', $today)
            ->where('is_completed', false)
            ->count();

        // Today's schedule lists
        $todayTherapySessions = TherapySession::with(['patient', 'plan', 'devices'])
            ->whereDate('session_date', $today)
            ->orderBy('is_attended', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $todayWeightLossSessions = WeightLossSession::with(['patient', 'plan', 'devices'])
            ->whereDate('session_date', $today)
            ->orderBy('is_completed', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $todayVisits = Visit::with('patient')
            ->whereDate('visit_date', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        $recentPayments = Payment::with(['patient', 'therapyPlan', 'weightLossPlan', 'visit'])
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();

        return view('dashboard', compact(
            'totalPatientsCount',
            'todayPatientsCount',
            'todayRevenue',
            'todayTherapySessionsCount',
            'todayAttendedTherapySessionsCount',
            'todayPendingTherapySessionsCount',
            'todayWeightLossSessionsCount',
            'todayCompletedWeightLossCount',
            'todayPendingWeightLossCount',
            'todayTherapySessions',
            'todayWeightLossSessions',
            'todayVisits',
            'recentPayments'
        ));
    }
}
