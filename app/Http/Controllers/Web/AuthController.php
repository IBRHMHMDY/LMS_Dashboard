<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // عرض صفحة الدخول الموحدة
    public function showLoginForm()
    {
        // إذا كان مسجلاً للدخول بالفعل، نوجهه للوحة المناسبة
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    // معالجة بيانات الدخول
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // التوجيه بناءً على الصلاحية، مع احترام الرابط الذي كان يحاول الوصول إليه (intended)
            if ($user->hasRole('admin')) {
                return redirect()->intended('/admin');
            }

            if ($user->hasRole('instructor')) {
                return redirect()->intended('/instructor');
            }

            // إذا كان طالباً أو مستخدماً عادياً ليس له لوحة تحكم هنا
            Auth::logout();
            return back()->withErrors([
                'email' => 'Access Denied: You do not have dashboard access.',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // دالة مساعدة لتوجيه المستخدم
    private function redirectBasedOnRole($user)
    {
        if ($user->hasRole('admin')) {
            return redirect('/admin');
        }
        
        if ($user->hasRole('instructor')) {
            return redirect('/instructor');
        }

        return redirect('/');
    }
}