<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'siswa') {
            abort_if(!$user->student, 403, 'Data siswa tidak ditemukan.');

            return redirect()->route($this->studentRoute($user->student));
        }

        abort(403);
    }

    private function studentRoute(Student $student): string
    {
        return match ($student->status) {
            'waiting_session' => 'siswa.waiting-session',
            'academic_test' => 'siswa.academic.index',
            'psychology_test' => 'siswa.psychology.index',
            'completed' => 'siswa.announcements.index',
            default => 'siswa.wizard.index',
        };
    }
}
