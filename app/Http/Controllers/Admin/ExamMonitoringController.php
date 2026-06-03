<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Student;
use App\Services\ExamFinalizationService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamMonitoringController extends Controller
{
    public function index(Request $request, ExamFinalizationService $examFinalizationService)
    {
        $academicDuration = Setting::getInt('academic_duration_minutes', 60);
        $psychologyDuration = Setting::getInt('psychology_duration_minutes', 45);
        $violationLimit = Setting::getInt('cbt_auto_submit_violation_limit', 3);
        $now = now();
        $perPage = min(max((int) $request->integer('per_page', 30), 10), 100);
        $search = trim((string) $request->input('q', ''));
        $examFilter = $request->input('exam');
        $examFilter = in_array($examFilter, ['academic', 'psychology'], true) ? $examFilter : null;

        $this->finalizeExpiredSessions($examFinalizationService, $academicDuration, $psychologyDuration, $now);

        $activeExamCase = "
            CASE
                WHEN s.status = 'psychology_test'
                    AND sts.psychology_started_at IS NOT NULL
                    AND sts.psychology_submitted_at IS NULL
                THEN 'psychology'
                WHEN sts.academic_started_at IS NOT NULL
                    AND sts.academic_submitted_at IS NULL
                THEN 'academic'
                ELSE 'psychology'
            END
        ";

        $activeSessionsQuery = DB::table('student_test_sessions as sts')
            ->join('students as s', 's.id', '=', 'sts.student_id')
            ->join('test_sessions as ts', 'ts.id', '=', 'sts.test_session_id')
            ->where(function ($query) {
                $query
                    ->where(function ($inner) {
                        $inner->whereNotNull('sts.academic_started_at')
                            ->whereNull('sts.academic_submitted_at');
                    })
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('sts.psychology_started_at')
                            ->whereNull('sts.psychology_submitted_at');
                    });
            });

        if ($search !== '') {
            $activeSessionsQuery->where(function ($query) use ($search) {
                $query->where('s.name', 'like', "%{$search}%")
                    ->orWhere('s.nisn', 'like', "%{$search}%")
                    ->orWhere('s.origin_class', 'like', "%{$search}%");
            });
        }

        if ($examFilter) {
            $activeSessionsQuery->whereRaw("({$activeExamCase}) = ?", [$examFilter]);
        }

        $summaryRow = DB::query()
            ->fromSub(
                (clone $activeSessionsQuery)->selectRaw("
                    {$activeExamCase} as active_exam_key,
                    sts.academic_violation_count,
                    sts.psychology_violation_count,
                    sts.academic_duration_seconds,
                    sts.psychology_duration_seconds,
                    sts.academic_submit_type,
                    sts.psychology_submit_type
                "),
                'active_students'
            )
            ->selectRaw("
                COUNT(*) as active_students,
                SUM(CASE WHEN active_exam_key = 'academic' THEN 1 ELSE 0 END) as academic,
                SUM(CASE WHEN active_exam_key = 'psychology' THEN 1 ELSE 0 END) as psychology,
                SUM(CASE
                    WHEN active_exam_key = 'academic' AND academic_violation_count >= ? THEN 1
                    WHEN active_exam_key = 'psychology' AND psychology_violation_count >= ? THEN 1
                    ELSE 0
                END) as high_violation
            ", [$violationLimit, $violationLimit])
            ->first();

        $activeStudentsQuery = (clone $activeSessionsQuery)
            ->selectRaw("
                s.id as student_id,
                s.name,
                s.nisn,
                s.origin_class,
                s.status as student_status,
                ts.id as session_id,
                ts.name as session_name,
                ts.test_date,
                ts.start_time,
                ts.end_time,
                ts.test_type,
                sts.started_at,
                sts.academic_started_at,
                sts.psychology_started_at,
                sts.academic_submitted_at,
                sts.psychology_submitted_at,
                sts.academic_violation_count,
                sts.psychology_violation_count,
                sts.academic_duration_seconds,
                sts.psychology_duration_seconds,
                sts.academic_submit_type,
                sts.psychology_submit_type,
                {$activeExamCase} as active_exam_key,
                (
                    SELECT MAX(v.occurred_at)
                    FROM violations v
                    WHERE v.student_id = s.id
                        AND v.test_session_id = ts.id
                        AND v.exam_type = {$activeExamCase}
                ) as last_violation_at
            ");

        $students = $activeStudentsQuery
            ->orderBy('ts.test_date')
            ->orderBy('ts.start_time')
            ->orderBy('s.name')
            ->paginate($perPage)
            ->withQueryString();

        $students->getCollection()->transform(function ($student) use ($academicDuration, $psychologyDuration, $now) {
                $isAcademic = $student->active_exam_key === 'academic';
                $durationMinutes = $isAcademic ? $academicDuration : $psychologyDuration;
                $startedAt = $isAcademic ? $student->academic_started_at : $student->psychology_started_at;
                $violationCount = $isAcademic
                    ? (int) $student->academic_violation_count
                    : (int) $student->psychology_violation_count;

                $elapsedSeconds = $startedAt ? max(0, Carbon::parse($startedAt)->diffInSeconds($now)) : 0;
                $remainingSeconds = max(0, ($durationMinutes * 60) - $elapsedSeconds);

                $student->active_exam = $isAcademic ? 'Tes Akademik' : 'Tes Psikologi';
                $student->duration_minutes = $durationMinutes;
                $student->violation_count = $violationCount;
                $student->remaining_seconds = $remainingSeconds;
                $student->remaining_label = sprintf('%02d:%02d', floor($remainingSeconds / 60), $remainingSeconds % 60);
                $student->elapsed_seconds = $elapsedSeconds;
                $student->elapsed_label = $this->formatDuration($elapsedSeconds);
                $student->submit_type = $isAcademic ? $student->academic_submit_type : $student->psychology_submit_type;
                $student->submit_type_label = $this->formatSubmitType($student->submit_type);

                return $student;
            });

        $summary = [
            'active_students' => (int) ($summaryRow->active_students ?? 0),
            'academic' => (int) ($summaryRow->academic ?? 0),
            'psychology' => (int) ($summaryRow->psychology ?? 0),
            'high_violation' => (int) ($summaryRow->high_violation ?? 0),
        ];

        $recentSubmissions = $this->recentSubmissions();

        return view('admin.exam-monitoring.index', compact('students', 'summary', 'recentSubmissions', 'search', 'examFilter'));
    }

    private function finalizeExpiredSessions(
        ExamFinalizationService $examFinalizationService,
        int $academicDuration,
        int $psychologyDuration,
        Carbon $now
    ): void {
        $this->expiredAcademicSessions($academicDuration, $now)
            ->each(function ($session) use ($examFinalizationService, $academicDuration) {
                $student = Student::find($session->student_id);

                if ($student) {
                    $examFinalizationService->finalizeAcademic(
                        $student,
                        (int) $session->test_session_id,
                        'timeout',
                        $academicDuration * 60
                    );
                }
            });

        $this->expiredPsychologySessions($psychologyDuration, $now)
            ->each(function ($session) use ($examFinalizationService, $psychologyDuration) {
                $student = Student::find($session->student_id);

                if ($student) {
                    $examFinalizationService->finalizePsychology(
                        $student,
                        (int) $session->test_session_id,
                        'timeout',
                        $psychologyDuration * 60
                    );
                }
            });
    }

    private function expiredAcademicSessions(int $durationMinutes, Carbon $now)
    {
        return DB::table('student_test_sessions')
            ->select(['student_id', 'test_session_id'])
            ->whereNotNull('academic_started_at')
            ->whereNull('academic_submitted_at')
            ->where('academic_started_at', '<=', $now->copy()->subMinutes($durationMinutes))
            ->limit(100)
            ->get();
    }

    private function expiredPsychologySessions(int $durationMinutes, Carbon $now)
    {
        return DB::table('student_test_sessions')
            ->select(['student_id', 'test_session_id'])
            ->whereNotNull('psychology_started_at')
            ->whereNull('psychology_submitted_at')
            ->where('psychology_started_at', '<=', $now->copy()->subMinutes($durationMinutes))
            ->limit(100)
            ->get();
    }

    private function recentSubmissions()
    {
        $academicSubmissions = DB::table('student_test_sessions as sts')
            ->join('students as s', 's.id', '=', 'sts.student_id')
            ->join('test_sessions as ts', 'ts.id', '=', 'sts.test_session_id')
            ->whereNotNull('sts.academic_submitted_at')
            ->selectRaw("
                s.name,
                s.nisn,
                s.origin_class,
                ts.name as session_name,
                'Tes Akademik' as exam_name,
                sts.academic_submitted_at as submitted_at,
                sts.academic_duration_seconds as duration_seconds,
                sts.academic_submit_type as submit_type
            ");

        $psychologySubmissions = DB::table('student_test_sessions as sts')
            ->join('students as s', 's.id', '=', 'sts.student_id')
            ->join('test_sessions as ts', 'ts.id', '=', 'sts.test_session_id')
            ->whereNotNull('sts.psychology_submitted_at')
            ->selectRaw("
                s.name,
                s.nisn,
                s.origin_class,
                ts.name as session_name,
                'Tes Psikologi' as exam_name,
                sts.psychology_submitted_at as submitted_at,
                sts.psychology_duration_seconds as duration_seconds,
                sts.psychology_submit_type as submit_type
            ");

        return DB::query()
            ->fromSub($academicSubmissions->unionAll($psychologySubmissions), 'submissions')
            ->orderByDesc('submitted_at')
            ->limit(20)
            ->get()
            ->map(function ($submission) {
                $submission->duration_label = $submission->duration_seconds !== null
                    ? $this->formatDuration((int) $submission->duration_seconds)
                    : '-';
                $submission->submit_type_label = $this->formatSubmitType($submission->submit_type, true);

                return $submission;
            });
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return "{$minutes} menit {$remainingSeconds} detik";
    }

    private function formatSubmitType(?string $submitType, bool $alreadySubmitted = false): string
    {
        return match ($submitType) {
            'manual' => 'Manual',
            'timeout' => 'Otomatis: waktu habis',
            'violation' => 'Otomatis: pelanggaran',
            default => $alreadySubmitted ? 'Belum tercatat' : 'Belum submit',
        };
    }
}
