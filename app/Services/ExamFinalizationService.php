<?php

namespace App\Services;

use App\Models\AcademicQuestion;
use App\Models\Student;
use App\Models\TestResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExamFinalizationService
{
    public function __construct(private PsychologyScoringService $psychologyScoringService)
    {
    }

    public function finalizeAcademic(Student $student, int $sessionId, string $submitType = 'manual', ?int $durationLimitSeconds = null): void
    {
        DB::transaction(function () use ($student, $sessionId, $submitType, $durationLimitSeconds) {
            $total = AcademicQuestion::activeForTest()->count();

            $correct = $student->academicAnswers()
                ->where('is_correct', true)
                ->count();

            $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

            TestResult::updateOrCreate(
                ['student_id' => $student->id],
                ['academic_score' => $score]
            );

            $sessionState = DB::table('student_test_sessions')
                ->where('student_id', $student->id)
                ->where('test_session_id', $sessionId)
                ->first();

            if (!$sessionState || $sessionState->academic_submitted_at) {
                return;
            }

            $submittedAt = now();
            $durationSeconds = $this->durationSeconds($sessionState->academic_started_at, $submittedAt, $durationLimitSeconds);

            DB::table('student_test_sessions')
                ->where('student_id', $student->id)
                ->where('test_session_id', $sessionId)
                ->whereNull('academic_submitted_at')
                ->update([
                    'academic_submitted_at' => $submittedAt,
                    'academic_duration_seconds' => $durationSeconds,
                    'academic_submit_type' => $this->normalizeSubmitType($submitType),
                    'updated_at' => $submittedAt,
                ]);

            if ($student->status !== 'completed') {
                $student->update(['status' => 'psychology_test']);
            }
        });
    }

    public function finalizePsychology(Student $student, int $sessionId, string $submitType = 'manual', ?int $durationLimitSeconds = null): void
    {
        DB::transaction(function () use ($student, $sessionId, $submitType, $durationLimitSeconds) {
            $this->psychologyScoringService->calculate($student);

            $sessionState = DB::table('student_test_sessions')
                ->where('student_id', $student->id)
                ->where('test_session_id', $sessionId)
                ->first();

            if (!$sessionState || $sessionState->psychology_submitted_at) {
                return;
            }

            $submittedAt = now();
            $durationSeconds = $this->durationSeconds($sessionState->psychology_started_at, $submittedAt, $durationLimitSeconds);

            DB::table('student_test_sessions')
                ->where('student_id', $student->id)
                ->where('test_session_id', $sessionId)
                ->whereNull('psychology_submitted_at')
                ->update([
                    'psychology_submitted_at' => $submittedAt,
                    'psychology_duration_seconds' => $durationSeconds,
                    'psychology_submit_type' => $this->normalizeSubmitType($submitType),
                    'status' => 'finished',
                    'finished_at' => $submittedAt,
                    'updated_at' => $submittedAt,
                ]);

            $student->update(['status' => 'completed']);
        });
    }

    private function durationSeconds(?string $startedAt, Carbon $submittedAt, ?int $limitSeconds): ?int
    {
        if (!$startedAt) {
            return null;
        }

        $durationSeconds = max(0, Carbon::parse($startedAt)->diffInSeconds($submittedAt));

        return $limitSeconds !== null ? min($durationSeconds, $limitSeconds) : $durationSeconds;
    }

    private function normalizeSubmitType(?string $submitType): string
    {
        return in_array($submitType, ['manual', 'timeout', 'violation'], true)
            ? $submitType
            : 'manual';
    }
}
