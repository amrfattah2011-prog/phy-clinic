<?php

namespace App\Http\Controllers;

use App\Helpers\Tafqeet;
use App\Models\ClinicSetting;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,vodafone_cash,visa,bank_transfer',
            'therapy_plan_id' => 'nullable|exists:therapy_plans,id',
            'weight_loss_plan_id' => 'nullable|exists:weight_loss_plans,id',
            'visit_id' => 'nullable|exists:visits,id',
            'notes' => 'nullable|string',
        ], [
            'amount.required' => 'يرجى إدخال المبلغ المدفوع.',
            'payment_date.required' => 'يرجى تحديد تاريخ السداد.',
            'payment_method.required' => 'يرجى تحديد وسيلة الدفع.',
        ]);

        if ($request->filled('target_plan')) {
            $target = $request->input('target_plan');
            if (str_starts_with($target, 'therapy_')) {
                $validated['therapy_plan_id'] = (int) str_replace('therapy_', '', $target);
            } elseif (str_starts_with($target, 'weight_loss_')) {
                $validated['weight_loss_plan_id'] = (int) str_replace('weight_loss_', '', $target);
            }
        }

        $payment = $patient->payments()->create([
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'therapy_plan_id' => $validated['therapy_plan_id'] ?? null,
            'weight_loss_plan_id' => $validated['weight_loss_plan_id'] ?? null,
            'visit_id' => $validated['visit_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        \App\Services\ActivityLogger::log(
            action: 'payment',
            module: 'payments',
            description: "تحصيل سند قبض رقم '{$payment->receipt_number}' بمبلغ " . number_format($payment->amount, 2) . " ج.م ({$payment->payment_method_arabic}) للمريض: '{$patient->name}'",
            patientId: $patient->id,
            recordId: $payment->id
        );

        return redirect()->route('patients.show', ['patient' => $patient->id, 'tab' => 'financial'])
            ->with('success', 'تم تسجيل الدفعة بنجاح.')
            ->with('print_receipt_id', $payment->id);
    }

    public function showReceipt(Payment $payment)
    {
        $payment->load(['patient', 'therapyPlan', 'weightLossPlan', 'visit', 'creator.role']);
        $patient = $payment->patient;
        $settings = ClinicSetting::getSettings();

        // Calculate total cost, total paid up to now, remaining balance
        $totalCost = $patient->total_cost;
        $totalPaid = $patient->total_paid;
        $remainingBalance = $patient->remaining_balance;

        // Arabic text for amount
        $tafqeetAmount = Tafqeet::inArabic($payment->amount, $settings->currency === 'ج.م' ? 'جنيه مصري' : $settings->currency);

        // Service Description
        $serviceName = 'سداد دفعة من الحساب العام';
        if ($payment->therapyPlan) {
            $serviceName = 'باقة علاج طبيعي: ' . $payment->therapyPlan->package_name;
        } elseif ($payment->weightLossPlan) {
            $serviceName = 'باقة تخسيس: ' . $payment->weightLossPlan->package_name;
        } elseif ($payment->visit) {
            $serviceName = 'كشف / استشارة طبية بتاريخ ' . $payment->visit->visit_date->format('Y-m-d');
        }

        return view('receipts.thermal', compact(
            'payment',
            'patient',
            'settings',
            'totalCost',
            'totalPaid',
            'remainingBalance',
            'tafqeetAmount',
            'serviceName'
        ));
    }

    public function printStatement(Patient $patient)
    {
        $patient->load([
            'diseases',
            'visits',
            'therapyPlans',
            'weightLossPlans',
            'weightLossSessions',
            'payments.therapyPlan',
            'payments.weightLossPlan',
            'payments.visit'
        ]);

        $settings = ClinicSetting::getSettings();

        return view('receipts.statement', compact('patient', 'settings'));
    }

    public function destroy(Payment $payment)
    {
        $patientId = $payment->patient_id;
        $patientName = $payment->patient?->name;
        $receiptNumber = $payment->receipt_number;
        $amount = $payment->amount;
        $id = $payment->id;

        \App\Services\ActivityLogger::log(
            action: 'delete',
            module: 'payments',
            description: "إلغاء وحذف سند قبض رقم '{$receiptNumber}' بمبلغ " . number_format($amount, 2) . " ج.م للمريض: '{$patientName}'",
            patientId: $patientId,
            recordId: $id
        );

        $payment->delete();

        return redirect()->route('patients.show', ['patient' => $patientId, 'tab' => 'financial'])
            ->with('success', 'تم حذف سند القبض بنجاح.');
    }
}
