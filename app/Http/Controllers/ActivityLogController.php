<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user.role', 'patient'])->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  })
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $users = User::orderBy('name', 'asc')->get();

        $modules = [
            'patients' => 'المرضى والحالات',
            'visits' => 'الكشوفات والتشخيص',
            'therapy' => 'العلاج الطبيعي والأجهزة',
            'weight_loss' => 'جلسات وباقات التخسيس',
            'payments' => 'الخزينة وسندات القبض',
            'settings' => 'إعدادات المركز',
            'staff' => 'طاقم العمل والموظفين',
            'roles' => 'الأدوار والصلاحيات',
            'auth' => 'تسجيل الدخول والخروج',
        ];

        $actions = [
            'create' => 'إضافة جديدة',
            'update' => 'تعديل',
            'delete' => 'حذف / إلغاء',
            'attendance' => 'تسجيل حضور',
            'dosage' => 'تسجيل جرعة',
            'payment' => 'تحصيل مالي',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
        ];

        return view('activity_logs.index', compact('logs', 'users', 'modules', 'actions'));
    }
}
