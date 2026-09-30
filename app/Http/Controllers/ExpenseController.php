<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->filled('from_date') ? $request->from_date : Carbon::today()->startOfMonth()->toDateString();
        $toDate = $request->filled('to_date') ? $request->to_date : Carbon::today()->toDateString();

        $query = Expense::with('creator');

        if ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('user_id')) {
            $query->where('created_by_user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('receipt_reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Totals for filtered period
        $totalExpenses = (float) (clone $query)->sum('amount');
        $expensesCount = (clone $query)->count();

        // Expenses grouped by category for summary badges
        $categoryBreakdown = (clone $query)
            ->reorder()
            ->selectRaw('category, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        $expenses = (clone $query)->latest('expense_date')->latest('id')->paginate(20)->withQueryString();

        $categories = Expense::categories();
        $paymentMethods = Expense::paymentMethods();
        $users = User::orderBy('name')->get();

        return view('expenses.index', compact(
            'expenses',
            'totalExpenses',
            'expensesCount',
            'categoryBreakdown',
            'categories',
            'paymentMethods',
            'users',
            'fromDate',
            'toDate'
        ));
    }

    public function create()
    {
        $categories = Expense::categories();
        $paymentMethods = Expense::paymentMethods();
        return view('expenses.create', compact('categories', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['created_by_user_id'] = Auth::id();

        $expense = Expense::create($validated);

        ActivityLogger::log(
            'create',
            'expenses',
            "تسجيل مصروف جديد ({$expense->category_name}): {$expense->title} بقيمة " . number_format($expense->amount, 2) . " ج.م بواسطة " . Auth::user()->name,
            null,
            $expense->id
        );

        return redirect()->route('expenses.index')
            ->with('success', "تم تسجيل بند المصروف ({$expense->title}) بنجاح بقيمة " . number_format($expense->amount, 2) . " ج.م");
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::categories();
        $paymentMethods = Expense::paymentMethods();
        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldAmount = $expense->amount;
        $expense->update($validated);

        ActivityLogger::log(
            'update',
            'expenses',
            "تعديل بيانات المصروف ({$expense->title}) إلى " . number_format($expense->amount, 2) . " ج.م (سابقاً: " . number_format($oldAmount, 2) . " ج.م)",
            null,
            $expense->id
        );

        return redirect()->route('expenses.index')
            ->with('success', "تم تحديث بيانات المصروف ({$expense->title}) بنجاح.");
    }

    public function destroy(Expense $expense)
    {
        $title = $expense->title;
        $amount = $expense->amount;

        ActivityLogger::log(
            'delete',
            'expenses',
            "حذف بند مصروف ({$title}) بقيمة " . number_format($amount, 2) . " ج.م بواسطة " . Auth::user()->name,
            null,
            $expense->id
        );

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', "تم حذف بند المصروف ({$title}) بنجاح.");
    }
}
