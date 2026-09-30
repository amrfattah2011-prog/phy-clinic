<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TreasuryController extends Controller
{
    public function statement(Request $request)
    {
        // 1. Determine Date Range
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
            $fromDate = $request->filled('from_date') ? $request->from_date : $today->copy()->startOfMonth()->toDateString();
            $toDate = $request->filled('to_date') ? $request->to_date : $today->toDateString();
        }

        $paymentMethod = $request->get('payment_method');
        $userId = $request->get('user_id');

        // 2. Calculate Opening Balance before $fromDate
        $openingBalance = 0.0;
        if ($fromDate) {
            $priorPaymentsQuery = Payment::whereDate('payment_date', '<', $fromDate);
            $priorExpensesQuery = Expense::whereDate('expense_date', '<', $fromDate);

            if ($paymentMethod) {
                $priorPaymentsQuery->where('payment_method', $paymentMethod);
                $priorExpensesQuery->where('payment_method', $paymentMethod);
            }

            if ($userId) {
                $priorPaymentsQuery->where('created_by_user_id', $userId);
                $priorExpensesQuery->where('created_by_user_id', $userId);
            }

            $priorInflow = (float) $priorPaymentsQuery->sum('amount');
            $priorOutflow = (float) $priorExpensesQuery->sum('amount');
            $openingBalance = $priorInflow - $priorOutflow;
        }

        // 3. Fetch Period Payments (Revenues)
        $paymentsQuery = Payment::with(['patient', 'creator.role'])
            ->when($fromDate, fn($q) => $q->whereDate('payment_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('payment_date', '<=', $toDate))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($userId, fn($q) => $q->where('created_by_user_id', $userId));

        $payments = $paymentsQuery->get()->map(function ($payment) {
            $serviceType = '';
            if ($payment->therapy_plan_id) {
                $serviceType = ' (باقة علاج طبيعي)';
            } elseif ($payment->weight_loss_plan_id) {
                $serviceType = ' (باقة تخسيس)';
            } elseif ($payment->visit_id) {
                $serviceType = ' (كشف طبي)';
            }

            return (object) [
                'id' => $payment->id,
                'raw_date' => Carbon::parse($payment->payment_date)->toDateString(),
                'date_formatted' => Carbon::parse($payment->payment_date)->format('Y/m/d'),
                'created_at' => $payment->created_at,
                'type' => 'revenue',
                'type_label' => 'سند قبض / إيراد',
                'badge_color' => 'emerald',
                'reference_no' => $payment->receipt_number,
                'title' => 'تحصيل من المريض: ' . ($payment->patient?->name ?? 'غير محدد') . $serviceType,
                'patient_name' => $payment->patient?->name,
                'notes' => $payment->notes,
                'payment_method' => $payment->payment_method,
                'payment_method_label' => $payment->payment_method_name,
                'inflow' => (float) $payment->amount,
                'outflow' => 0.0,
                'staff_name' => $payment->creator?->name ?? 'غير محدد',
                'staff_role' => $payment->creator?->role?->display_name ?? 'الاستقبال / الخزينة',
            ];
        });

        // 4. Fetch Period Expenses (Outflows)
        $expensesQuery = Expense::with(['creator.role'])
            ->when($fromDate, fn($q) => $q->whereDate('expense_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('expense_date', '<=', $toDate))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($userId, fn($q) => $q->where('created_by_user_id', $userId));

        $expenses = $expensesQuery->get()->map(function ($expense) {
            return (object) [
                'id' => $expense->id,
                'raw_date' => Carbon::parse($expense->expense_date)->toDateString(),
                'date_formatted' => Carbon::parse($expense->expense_date)->format('Y/m/d'),
                'created_at' => $expense->created_at,
                'type' => 'expense',
                'type_label' => 'سند صرف - ' . $expense->category_name,
                'badge_color' => 'rose',
                'reference_no' => $expense->receipt_reference ?: ('EXP-' . str_pad($expense->id, 5, '0', STR_PAD_LEFT)),
                'title' => $expense->title . ' (' . $expense->category_name . ')',
                'patient_name' => '-',
                'notes' => $expense->notes,
                'payment_method' => $expense->payment_method,
                'payment_method_label' => $expense->payment_method_name,
                'inflow' => 0.0,
                'outflow' => (float) $expense->amount,
                'staff_name' => $expense->creator?->name ?? 'غير محدد',
                'staff_role' => $expense->creator?->role?->display_name ?? 'الإدارة / الخزينة',
            ];
        });

        // 5. Merge and Sort Chronologically Ascending to compute running balance
        $ledger = $payments->concat($expenses)->sortBy(function ($item) {
            return $item->raw_date . ' ' . ($item->created_at ? $item->created_at->toTimeString() : '00:00:00') . ' ' . $item->id;
        })->values();

        $runningBalance = $openingBalance;
        foreach ($ledger as $tx) {
            $runningBalance += ($tx->inflow - $tx->outflow);
            $tx->running_balance = $runningBalance;
        }

        // Summary Calculations
        $totalInflow = (float) $payments->sum('inflow');
        $totalOutflow = (float) $expenses->sum('outflow');
        $netPeriodCash = $totalInflow - $totalOutflow;
        $closingBalance = $runningBalance;

        // Payment Method Breakdown for the Period
        $methodsBreakdown = [
            'cash' => [
                'name' => 'نقداً (خزينة المركز)',
                'inflow' => (float) $payments->where('payment_method', 'cash')->sum('inflow'),
                'outflow' => (float) $expenses->where('payment_method', 'cash')->sum('outflow'),
            ],
            'visa' => [
                'name' => 'فيزا / تحويل بنكي',
                'inflow' => (float) $payments->where('payment_method', 'visa')->sum('inflow'),
                'outflow' => (float) $expenses->where('payment_method', 'visa')->sum('outflow'),
            ],
            'vodafone_cash' => [
                'name' => 'فودافون كاش ومحافظ إلكترونية',
                'inflow' => (float) $payments->where('payment_method', 'vodafone_cash')->sum('inflow'),
                'outflow' => (float) $expenses->where('payment_method', 'vodafone_cash')->sum('outflow'),
            ],
        ];

        foreach ($methodsBreakdown as $k => &$v) {
            $v['net'] = $v['inflow'] - $v['outflow'];
        }

        $users = User::orderBy('name')->get();
        $categories = Expense::categories();

        // Check if print request
        if ($request->has('print')) {
            return view('reports.treasury_print', compact(
                'ledger',
                'openingBalance',
                'totalInflow',
                'totalOutflow',
                'netPeriodCash',
                'closingBalance',
                'methodsBreakdown',
                'fromDate',
                'toDate',
                'paymentMethod',
                'userId',
                'users'
            ));
        }

        return view('reports.treasury', compact(
            'ledger',
            'openingBalance',
            'totalInflow',
            'totalOutflow',
            'netPeriodCash',
            'closingBalance',
            'methodsBreakdown',
            'fromDate',
            'toDate',
            'quickFilter',
            'paymentMethod',
            'userId',
            'users'
        ));
    }
}
