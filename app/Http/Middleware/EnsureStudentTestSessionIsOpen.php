<?php

namespace App\Http\Middleware;

use App\Models\TestSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $examType = $request->routeIs('siswa.psychology.*') ? 'psychology' : 'academic';

        if (!in_array($session->test_type, [$examType, 'both'], true)) {
            return redirect()
                ->route('siswa.waiting-session')
                ->with('warning', 'Jenis tes pada sesi ini belum tersedia untuk Anda.');
        }

        $now = now();

        $pivot = DB::table('student_test_sessions')
            ->where('student_id', $student->id)
            ->where('test_session_id', $session->id)
            ->first();

        if (!$pivot) {
            DB::table('student_test_sessions')->insert([
                'student_id' => $student->id,
                'test_session_id' => $session->id,
                'started_at' => $now,
                'academic_started_at' => $examType === 'academic' ? $now : null,
                'psychology_started_at' => $examType === 'psychology' ? $now : null,
                'status' => 'in_progress',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $updates = [
                'status' => 'in_progress',
                'updated_at' => $now,
            ];

            if (!$pivot->started_at) {
                $updates['started_at'] = $now;
            }

            if ($examType === 'academic' && !$pivot->academic_started_at) {
                $updates['academic_started_at'] = $now;
            }

            if ($examType === 'psychology' && !$pivot->psychology_started_at) {
                $updates['psychology_started_at'] = $now;
            }

            DB::table('student_test_sessions')
                ->where('student_id', $student->id)
                ->where('test_session_id', $session->id)
                ->update($updates);
        }

        $request->attributes->set('active_test_session_id', $session->id);

        return $next($request);
    }
}
