<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicQuestion;
use App\Models\AcademicQuestionOption;
use App\Models\Setting;
use App\Models\StudentAcademicAnswer;
use App\Services\ExamFinalizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AcademicTestController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        abort_if(!$student->selfie, 403, 'Selfie wajib dilakukan sebelum tes.');

        $sessionState = $this->getSessionState($request, $student->id);
        $durationMinutes = Setting::getInt('academic_duration_minutes', 60);
        $remainingSeconds = $this->calculateRemainingSeconds($sessionState->academic_started_at, $durationMinutes);

        if ($remainingSeconds <= 0) {
            return $this->finalizeAcademic($student, $sessionState->test_session_id, app(ExamFinalizationService::class), true);
        }

        $questions = AcademicQuestion::activeForTest();

        $questions = $this->applyStableRandomOrder(
            $questions,
            'academic',
            $student->id,
            $sessionState->test_session_id
        );

        $answers = $student->academicAnswers()
            ->pluck('academic_question_option_id', 'academic_question_id')
            ->toArray();

        $cbtSettings = [
            'duration_minutes' => $durationMinutes,
            'remaining_seconds' => $remainingSeconds,
            'violation_limit' => Setting::getInt('cbt_auto_submit_violation_limit', 3),
            'initial_violation_count' => (int) ($sessionState->academic_violation_count ?? 0),
            'force_fullscreen' => Setting::getBool('cbt_force_fullscreen', true),
            'warning_message' => Setting::getSetting('cbt_warning_message', 'Aktivitas mencurigakan terdeteksi dan dicatat.'),
            'student_help_text' => Setting::getSetting('student_help_text', ''),
        ];

        return view('siswa.academic.index', compact('student', 'questions', 'answers', 'cbtSettings'));
    }

    public function autosave(Request $request)
    {
        $validated = $request->validate([
            'academic_question_id' => ['required', 'exists:academic_questions,id'],
            'academic_question_option_id' => ['required', 'exists:academic_question_options,id'],
        ]);

        $student = auth()->user()->student;
        $sessionState = $this->getSessionState($request, $student->id);
        abort_if($this->calculateRemainingSeconds($sessionState->academic_started_at, Setting::getInt('academic_duration_minutes', 60)) <= 0, 423, 'Waktu tes akademik sudah habis.');

        $option = AcademicQuestionOption::query()
            ->select(['id', 'academic_question_id', 'is_correct'])
            ->where('id', $validated['academic_question_option_id'])
            ->where('academic_question_id', $validated['academic_question_id'])
            ->firstOrFail();

        StudentAcademicAnswer::updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_question_id' => $validated['academic_question_id'],
            ],
            [
                'academic_question_option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Jawaban tersimpan.',
        ]);
    }

    public function submit(Request $request, ExamFinalizationService $examFinalizationService)
    {
        $student = auth()->user()->student;

        $sessionState = $this->getSessionState($request, $student->id);

        return $this->finalizeAcademic(
            $student,
            $sessionState->test_session_id,
            $examFinalizationService,
            false,
            $request->input('submit_type', 'manual')
        );
    }

    private function getSessionState(Request $request, int $studentId): object
    {
        $state = $request->attributes->get('active_test_session_state');

        if ($state) {
            return $state;
        }

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

    private function finalizeAcademic($student, int $sessionId, ExamFinalizationService $examFinalizationService, bool $expired = false, string $submitType = 'manual')
    {
        $submitType = $expired ? 'timeout' : $this->normalizeSubmitType($submitType);
        $durationLimitSeconds = $expired ? Setting::getInt('academic_duration_minutes', 60) * 60 : null;

        $examFinalizationService->finalizeAcademic($student, $sessionId, $submitType, $durationLimitSeconds);

        if ($expired) {
            return redirect()
                ->route('siswa.psychology.index')
                ->with('warning', 'Waktu tes akademik sudah habis. Jawaban Anda dikirim otomatis.');
        }

        return response()->json([
            'message' => 'Tes akademik selesai.',
            'redirect_url' => route('siswa.psychology.index'),
        ]);
    }

    private function normalizeSubmitType(?string $submitType): string
    {
        return in_array($submitType, ['manual', 'timeout', 'violation'], true)
            ? $submitType
            : 'manual';
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
