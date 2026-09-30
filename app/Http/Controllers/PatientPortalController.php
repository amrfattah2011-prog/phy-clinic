<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientPortalController extends Controller
{
    /**
     * Display the mobile-friendly, secure patient portal view.
     */
    public function show(string $token)
    {
        $patient = Patient::with([
            'visits' => fn($q) => $q->orderBy('visit_date', 'desc')->take(10),
            'therapyPlans' => fn($q) => $q->with(['sessions.devices'])->orderBy('created_at', 'desc'),
            'weightLossPlans' => fn($q) => $q->with(['sessions.devices'])->orderBy('created_at', 'desc'),
            'weightLossSessions' => fn($q) => $q->whereNull('weight_loss_plan_id')->with('devices')->orderBy('session_date', 'desc')->take(10),
            'payments' => fn($q) => $q->orderBy('payment_date', 'desc')->take(10),
            'diseases',
        ])->where('portal_token', $token)->firstOrFail();

        $settings = ClinicSetting::getSettings();

        return view('portal.patient', compact('patient', 'settings'));
    }
}
