<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['login' => 'تم تعطيل هذا الحساب.']);
        }

        if (!$user->hasPermission($permission)) {
            abort(403, 'عفواً، لا تملك الصلاحية الكافية للوصول إلى هذا القسم أو تنفيذ هذه العملية.');
        }

        return $next($request);
    }
}
