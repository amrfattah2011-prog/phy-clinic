<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'يرجى إدخال اسم المستخدم أو البريد الإلكتروني.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ]);

        $loginInput = $credentials['login'];
        $password = $credentials['password'];
        $remember = $request->boolean('remember');

        // Find user by username or email
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'بيانات الدخول غير صحيحة، يرجى التأكد من اسم المستخدم وكلمة المرور.',
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'تم تعطيل هذا الحساب مؤقتاً، يرجى التواصل مع إدارة المركز.',
            ]);
        }

        // Login user
        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Update last login timestamp
        $user->update(['last_login_at' => Carbon::now()]);

        // Audit log
        ActivityLogger::log(
            action: 'login',
            module: 'auth',
            description: "قام الموظف '{$user->name}' ({$user->role?->display_name}) بتسجيل الدخول إلى النظام من IP: {$request->ip()}",
            userId: $user->id
        );

        return redirect()->intended(route('dashboard'))
            ->with('success', "مرحباً بك مجدداً د. / أ. {$user->name} في نظام إدارة المركز.");
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            ActivityLogger::log(
                action: 'logout',
                module: 'auth',
                description: "قام الموظف '{$user->name}' بتسجيل الخروج من النظام",
                userId: $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
