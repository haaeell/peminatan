<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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

    protected function credentials(Request $request): array
    {
        $login = $request->input('login');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'nisn';

        return [
            $field => $login,
            'password' => $request->input('password'),
            'is_active' => true,
        ];
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
}
