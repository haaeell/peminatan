<?php

namespace App\Http\Middleware;

use App\Models\TestSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentTestSessionIsOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = auth()->user()?->student;

        abort_if(!$student, 403);

        $session = TestSession::query()
            ->where('is_active', true)
            ->whereDate('test_date', today())
            ->whereTime('start_time', '<=', now()->format('H:i:s'))
            ->whereTime('end_time', '>=', now()->format('H:i:s'))
            ->whereHas('classes', function ($query) use ($student) {
                $query->where('origin_class', $student->origin_class);
            })
            ->first();

        if (!$session) {
            return redirect()
                ->route('siswa.waiting-session')
                ->with('warning', 'Sesi tes Anda belum dibuka atau sudah berakhir.');
        }

        $student->testSessions()->syncWithoutDetaching([
            $session->id => [
                'status' => 'in_progress',
                'started_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return $next($request);
    }
}
