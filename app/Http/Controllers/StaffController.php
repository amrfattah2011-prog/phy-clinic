<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role')->orderBy('id', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $staff = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('id', 'asc')->get();

        return view('staff.index', compact('staff', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('id', 'asc')->get();
        return view('staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:50|alpha_dash|unique:users,username',
            'email'     => 'required|email|max:255|unique:users,email',
            'phone'     => 'nullable|string|max:30',
            'role_id'   => 'required|exists:roles,id',
            'password'  => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required'       => 'يرجى إدخال اسم الموظف بالكامل.',
            'username.required'   => 'يرجى إدخال اسم المستخدم بالإنجليزية أو أرقام وشرطة.',
            'username.unique'     => 'اسم المستخدم هذا محجوز مسبقاً لموظف آخر.',
            'email.required'      => 'يرجى إدخال البريد الإلكتروني.',
            'email.unique'        => 'البريد الإلكتروني هذا مستخدم مسبقاً.',
            'role_id.required'    => 'يرجى اختيار الدور الوظيفي للموظف.',
            'password.required'   => 'يرجى كتابة كلمة المرور.',
            'password.min'        => 'يجب ألا تقل كلمة المرور عن 8 خانات.',
            'password.confirmed'  => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'role_id' => $validated['role_id'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        ActivityLogger::log(
            action: 'create',
            module: 'staff',
            description: "تمت إضافة موظف جديد: '{$user->name}' واسم المستخدم: '{$user->username}' بالدور: '{$user->role?->display_name}'",
            recordId: $user->id
        );

        return redirect()->route('staff.index')
            ->with('success', "تم تسجيل الموظف '{$user->name}' وإصدار بيانات الدخول بنجاح.");
    }

    public function edit(User $staff)
    {
        $roles = Role::orderBy('id', 'asc')->get();
        return view('staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($staff->id)],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'phone'     => 'nullable|string|max:30',
            'role_id'   => 'required|exists:roles,id',
            'password'  => 'nullable|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required'      => 'يرجى إدخال اسم الموظف.',
            'username.required'  => 'يرجى كتابة اسم المستخدم.',
            'username.unique'    => 'اسم المستخدم هذا مسجل لموظف آخر.',
            'email.required'     => 'يرجى إدخال البريد الإلكتروني.',
            'email.unique'       => 'البريد الإلكتروني هذا مستخدم مسبقاً.',
            'password.min'       => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        // Prevent disabling or removing admin role from default admin
        if ($staff->username === 'admin' && (int) $validated['role_id'] !== Role::where('name', 'admin')->value('id')) {
            return back()->withErrors(['role_id' => 'لا يمكن تغيير دور حساب المدير الأساسي للنظام.']);
        }

        $data = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'role_id' => $validated['role_id'],
            'is_active' => $staff->username === 'admin' ? true : (bool) $request->input('is_active', false),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $staff->update($data);

        ActivityLogger::log(
            action: 'update',
            module: 'staff',
            description: "تم تعديل بيانات الموظف '{$staff->name}' ({$staff->username})" . (!empty($validated['password']) ? " مع تغيير كلمة المرور" : ""),
            recordId: $staff->id
        );

        return redirect()->route('staff.index')
            ->with('success', "تم تحديث بيانات الموظف '{$staff->name}' بنجاح.");
    }

    public function toggleActive(User $staff)
    {
        if ($staff->username === 'admin') {
            return back()->withErrors(['error' => 'لا يمكن تعطيل حساب مدير النظام الرئيسي.']);
        }

        if ($staff->id === Auth::id()) {
            return back()->withErrors(['error' => 'لا يمكنك تعطيل حسابك الشخصي أثناء تسجيل الدخول.']);
        }

        $staff->is_active = !$staff->is_active;
        $staff->save();

        $statusText = $staff->is_active ? 'تفعيل' : 'تعطيل';
        ActivityLogger::log(
            action: 'update',
            module: 'staff',
            description: "تم {$statusText} حساب الموظف '{$staff->name}' ({$staff->username})",
            recordId: $staff->id
        );

        return back()->with('success', "تم {$statusText} حساب الموظف '{$staff->name}' بنجاح.");
    }

    public function destroy(User $staff)
    {
        if ($staff->username === 'admin') {
            return back()->withErrors(['error' => 'لا يمكن حذف حساب مدير النظام الرئيسي.']);
        }

        if ($staff->id === Auth::id()) {
            return back()->withErrors(['error' => 'لا يمكنك حذف حسابك الشخصي الحالي.']);
        }

        $name = $staff->name;
        $username = $staff->username;

        ActivityLogger::log(
            action: 'delete',
            module: 'staff',
            description: "تم حذف حساب الموظف '{$name}' ({$username}) من النظام",
            recordId: $staff->id
        );

        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', "تم حذف الموظف '{$name}' بنجاح.");
    }
}
