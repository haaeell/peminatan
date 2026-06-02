<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected string $redirectTo = '/redirect-after-login';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username(): string
    {
        return 'login';
    }

    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau NISN wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);
    }

    protected function attemptLogin(Request $request): bool
    {
        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nisn';
        $user = User::where($field, $login)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return false;
        }

        $password = (string) $request->input('password');
        $storedPassword = (string) $user->password;

        if (!hash_equals($storedPassword, $password) && !Hash::check($password, $storedPassword)) {
            return false;
        }

        $this->guard()->login($user, $request->boolean('remember'));

        return true;
    }
}
