<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PsychologyQuestion;
use App\Models\Setting;
use App\Models\StudentPsychologyAnswer;
use App\Services\PsychologyScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PsychologyTestController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        abort_if($student->status !== 'psychology_test', 403);

        $sessionState = $this->getSessionState($request, $student->id);
        $durationMinutes = Setting::getInt('psychology_duration_minutes', 45);
        $remainingSeconds = $this->calculateRemainingSeconds($sessionState->psychology_started_at, $durationMinutes);

        if ($remainingSeconds <= 0) {
            return $this->finalizePsychology($student, $sessionState->test_session_id, app(PsychologyScoringService::class), true);
        }

        $questions = PsychologyQuestion::where('is_active', true)
            ->with('options')
            ->orderBy('order')
            ->get();

        $questions = $this->applyStableRandomOrder(
            $questions,
            'psychology',
            $student->id,
            $sessionState->test_session_id
        );

        $answers = $student->psychologyAnswers()
            ->pluck('psychology_question_option_id', 'psychology_question_id')
            ->toArray();

        $cbtSettings = [
            'duration_minutes' => $durationMinutes,
            'remaining_seconds' => $remainingSeconds,
            'violation_limit' => Setting::getInt('cbt_auto_submit_violation_limit', 3),
            'initial_violation_count' => (int) ($sessionState->psychology_violation_count ?? 0),
            'force_fullscreen' => Setting::getBool('cbt_force_fullscreen', true),
            'warning_message' => Setting::getSetting('cbt_warning_message', 'Aktivitas mencurigakan terdeteksi dan dicatat.'),
            'student_help_text' => Setting::getSetting('student_help_text', ''),
        ];

        return view('siswa.psychology.index', compact('student', 'questions', 'answers', 'cbtSettings'));
    }

    public function autosave(Request $request)
    {
        $validated = $request->validate([
            'psychology_question_id' => ['required', 'exists:psychology_questions,id'],
            'psychology_question_option_id' => ['required', 'exists:psychology_question_options,id'],
        ]);

        $student = auth()->user()->student;
        abort_if($student->status !== 'psychology_test', 403, 'Tes psikologi belum tersedia untuk status Anda.');

        $sessionState = $this->getSessionState($request, $student->id);
        abort_if($this->calculateRemainingSeconds($sessionState->psychology_started_at, Setting::getInt('psychology_duration_minutes', 45)) <= 0, 423, 'Waktu tes psikologi sudah habis.');

        StudentPsychologyAnswer::updateOrCreate(
            [
                'student_id' => $student->id,
                'psychology_question_id' => $validated['psychology_question_id'],
            ],
            [
                'psychology_question_option_id' => $validated['psychology_question_option_id'],
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Jawaban tersimpan.',
        ]);
    }

    public function submit(PsychologyScoringService $scoringService)
    {
        $student = auth()->user()->student;
        abort_if($student->status !== 'psychology_test', 403, 'Tes psikologi belum tersedia untuk status Anda.');

        $sessionState = $this->getSessionState(request(), $student->id);

        return $this->finalizePsychology($student, $sessionState->test_session_id, $scoringService);
    }

    private function getSessionState(Request $request, int $studentId): object
    {
        $sessionId = $request->attributes->get('active_test_session_id');

        abort_if(!$sessionId, 403, 'Sesi tes tidak ditemukan.');

        $state = DB::table('student_test_sessions')
            ->where('student_id', $studentId)
            ->where('test_session_id', $sessionId)
            ->first();

        abort_if(!$state, 403, 'State tes siswa tidak ditemukan.');

        return $state;
    }

    private function calculateRemainingSeconds(?string $startedAt, int $durationMinutes): int
    {
        if (!$startedAt) {
            return $durationMinutes * 60;
        }

        return max(0, ($durationMinutes * 60) - Carbon::parse($startedAt)->diffInSeconds(now()));
    }

    private function finalizePsychology($student, int $sessionId, PsychologyScoringService $scoringService, bool $expired = false)
    {
        DB::transaction(function () use ($student, $sessionId, $scoringService) {
            $scoringService->calculate($student);

            $student->update(['status' => 'completed']);

            DB::table('student_test_sessions')
                ->where('student_id', $student->id)
                ->where('test_session_id', $sessionId)
                ->update([
                    'psychology_submitted_at' => now(),
                    'status' => 'finished',
                    'finished_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        if ($expired) {
            return redirect()
                ->route('siswa.announcements.index')
                ->with('warning', 'Waktu tes psikologi sudah habis. Jawaban Anda dikirim otomatis.');
        }

        return response()->json([
            'message' => 'Tes psikologi selesai.',
            'redirect_url' => route('siswa.announcements.index'),
        ]);
    }

    private function applyStableRandomOrder($questions, string $examType, int $studentId, int $sessionId)
    {
        $key = "exam_order.{$examType}.{$studentId}.{$sessionId}";
        $questionIds = $questions->pluck('id')->all();
        $storedOrder = session($key, []);

        $validOrder = array_values(array_intersect($storedOrder, $questionIds));
        $missingIds = array_values(array_diff($questionIds, $validOrder));

        if (!empty($missingIds)) {
            shuffle($missingIds);
        }

        $finalOrder = array_merge($validOrder, $missingIds);
        session([$key => $finalOrder]);

        return $questions->sortBy(fn ($question) => array_search($question->id, $finalOrder, true))->values();
    }
}
