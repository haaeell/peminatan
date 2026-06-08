<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GenericArrayExport;
use App\Http\Controllers\Controller;
use App\Models\AnnouncementResponse;
use App\Models\ClassStudent;
use App\Models\Objection;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Student;
use App\Models\TestResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index()
    {
        $summary = [
            'students' => Student::count(),
            'results' => TestResult::count(),
            'distributed' => ClassStudent::count(),
            'responses' => AnnouncementResponse::count(),
        ];

        $reports = collect($this->reportDefinitions())
            ->map(function (array $report, string $type) {
                return $report + ['type' => $type];
            })
            ->values();

        return view('admin.reports.index', compact('summary', 'reports'));
    }

    public function exportExcel(string $type)
    {
        $report = $this->resolveReport($type);

        return Excel::download(
            new GenericArrayExport($report['headings'], $report['rows']),
            $report['filename'] . '.xlsx'
        );
    }

    public function exportPdf(string $type)
    {
        $report = $this->resolveReport($type);

        $pdf = Pdf::loadView('pdfs.admin-report', [
            'title' => $report['title'],
            'subtitle' => $report['subtitle'],
            'headings' => $report['headings'],
            'rows' => $report['rows'],
            'groupedRows' => $report['grouped_rows'] ?? null,
            'summaryLines' => $report['summary_lines'],
            'schoolName' => Setting::getSetting('school_name', 'Sekolah Menengah Atas'),
            'appName' => Setting::getSetting('app_name', 'Sistem Pemilihan Jurusan'),
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'logoDataUri' => Setting::logoDataUri(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($report['filename'] . '.pdf');
    }

    private function resolveReport(string $type): array
    {
        $reports = $this->reportDefinitions();

        abort_unless(isset($reports[$type]), 404);

        return $reports[$type];
    }

    private function reportDefinitions(): array
    {
        return [
            'students' => $this->studentReport(),
            'test_results' => $this->testResultReport(),
            'class_distribution' => $this->classDistributionReport(),
            'announcement_responses' => $this->announcementResponseReport(),
        ];
    }

    private function studentReport(): array
    {
        $students = Student::with([
            'user',
            'biodata',
            'packageChoice.firstPackage',
            'packageChoice.secondPackage',
            'classStudent.classGroup',
            'result.finalPackage',
        ])->get();

        $headings = [
            'No',
            'Nama',
            'NISN',
            'NIS',
            'Kelas Asal',
            'TTL',
            'Jenis Kelamin',
            'No HP',
            'Ayah',
            'Ibu',
            'No HP Ortu',
            'Kelas Hasil',
        ];

        $groupedRows = $this->groupRowsByOriginClass(
            $students,
            fn($student) => $student->origin_class,
            function ($student) {
                return [
                    null,
                    $student->name,
                    $student->nisn,
                    $student->nis ?: '-',
                    $student->origin_class ?: '-',
                    $student->biodata
                        ? trim(($student->biodata->birth_place ?: '-') . ', ' . optional($student->biodata->birth_date)->format('d-m-Y'))
                        : '-',
                    $student->biodata?->gender ?: '-',
                    $student->biodata?->phone ?: '-',
                    $student->biodata?->father_name ?: '-',
                    $student->biodata?->mother_name ?: '-',
                    $student->biodata?->parent_phone ?: '-',
                    $student->classStudent?->classGroup?->name ?: '-',
                ];
            },
            count($headings),
            fn($student) => [
                $student->origin_class ?: 'ZZZ',
                $student->name,
            ]
        );

        return [
            'title' => 'Laporan Data Siswa Lengkap',
            'subtitle' => 'Ringkasan identitas, biodata, pilihan jurusan, dan status siswa.',
            'filename' => 'laporan_data_siswa_lengkap',
            'headings' => $headings,
            'rows' => $this->flattenGroupedRows($groupedRows),
            'grouped_rows' => $groupedRows,
            'summary_lines' => [
                'Total siswa: ' . $students->count(),
            ],
        ];
    }

    private function testResultReport(): array
    {
        $results = TestResult::with([
            'student.packageChoice.firstPackage',
            'student.packageChoice.secondPackage',
            'student.classStudent.classGroup',
            'student.result.finalPackage',
            'recommendedPackage',
            'finalPackage',
        ])->get();

        $headings = [
            'No',
            'Nama',
            'NISN',
            'Nilai Akademik',
            'Skor Psikotes',
            'Pilihan 1',
            'Pilihan 2',
            'Rekomendasi Psikotes',
            'Jurusan Final',
            'Rencana Setelah Lulus',
        ];

        $groupedRows = $this->groupRowsByOriginClass(
            $results,
            fn($result) => $result->student?->origin_class,
            function ($result) {
                $packageMap = Package::pluck('code', 'id')->all();
                return [
                    null,
                    $result->student?->name ?: '-',
                    $result->student?->nisn ?: '-',
                    (string) $result->academic_score,
                    $this->psychologyScoreText($result->psychology_scores, $packageMap),
                    $result->student?->packageChoice?->firstPackage?->name ?: '-',
                    $result->student?->packageChoice?->secondPackage?->name ?: '-',
                    $result->recommendedPackage?->name ?: '-',
                    $result->finalPackage?->name ?: '-',
                    $result->student?->packageChoice?->post_graduation_plan ?: '-',
                ];
            },
            count($headings),
            fn($result) => [
                $result->student?->origin_class ?: 'ZZZ',
                $this->packageSortKey($result->recommendedPackage?->name),
                - ((float) $result->academic_score),
                $result->student?->name ?: 'ZZZ',
            ]
        );

        return [
            'title' => 'Laporan Hasil Tes Siswa',
            'subtitle' => 'Nilai akademik, skor psikotes, dan rekomendasi penempatan.',
            'filename' => 'laporan_hasil_tes_siswa',
            'headings' => $headings,
            'rows' => $this->flattenGroupedRows($groupedRows),
            'grouped_rows' => $groupedRows,
            'summary_lines' => [
                'Total hasil tes: ' . $results->count(),
            ],
        ];
    }

    private function classDistributionReport(): array
    {
        $classStudents = ClassStudent::with([
            'student',
            'classGroup.package',
            'package',
        ])->get();

        $headings = [
            'No',
            'Nama',
            'NISN',
            'Kelas Asal',
            'Jurusan',
            'Jenis Penempatan',
        ];

        $groupedRows = $this->groupRowsByOriginClass(
            $classStudents,
            fn($item) => $item->classGroup?->name,
            function ($item) {
                return [
                    null,
                    $item->student?->name ?: '-',
                    $item->student?->nisn ?: '-',
                    $item->student?->origin_class ?: '-',
                    $item->package?->name ?: '-',
                    $item->is_manual_override ? 'Manual' : 'Otomatis',
                ];
            },
            count($headings),
            fn($item) => [
                $item->classGroup?->name ?: 'ZZZ',
                $item->student?->name ?: 'ZZZ',
            ]
        );

        return [
            'title' => 'Laporan Distribusi Kelas',
            'subtitle' => 'Daftar siswa berdasarkan hasil jurusan dan kelas penempatan.',
            'filename' => 'laporan_distribusi_kelas',
            'headings' => $headings,
            'rows' => $this->flattenGroupedRows($groupedRows),
            'grouped_rows' => $groupedRows,
            'summary_lines' => [
                'Total siswa terdistribusi: ' . $classStudents->count(),
                'Penempatan otomatis: ' . $classStudents->where('is_manual_override', false)->count(),
                'Penempatan manual: ' . $classStudents->where('is_manual_override', true)->count(),
            ],
        ];
    }

    private function announcementResponseReport(): array
    {
        $responses = AnnouncementResponse::with([
            'student',
            'announcement',
        ])->get();

        $objections = Objection::with(['student', 'announcement'])
            ->orderByDesc('created_at')
            ->get()
            ->keyBy(fn($objection) => $objection->announcement_id . '-' . $objection->student_id);

        $headings = [
            'No',
            'Nama',
            'NISN',
            'Kelas Asal',
            'Pengumuman',
            'Tipe',
            'Respons',
            'Tanggal Respons',
            'Status Keberatan',
            'Alasan Keberatan',
        ];

        $groupedRows = $this->groupRowsByOriginClass(
            $responses,
            fn($response) => $response->student?->origin_class,
            function ($response) use ($objections) {
                $objection = $objections->get($response->announcement_id . '-' . $response->student_id);

                return [
                    null,
                    $response->student?->name ?: '-',
                    $response->student?->nisn ?: '-',
                    $response->student?->origin_class ?: '-',
                    $response->announcement?->title ?: '-',
                    $response->announcement?->type ?: '-',
                    $response->response,
                    optional($response->responded_at)->format('d-m-Y H:i') ?: '-',
                    $objection?->status ?: '-',
                    $objection?->reason ?: '-',
                ];
            },
            count($headings),
            fn($response) => [
                $response->student?->origin_class ?: 'ZZZ',
                - ((int) optional($response->responded_at)->timestamp),
                $response->student?->name ?: 'ZZZ',
            ]
        );

        return [
            'title' => 'Laporan Respons Pengumuman',
            'subtitle' => 'Penerimaan hasil, keberatan siswa, dan status tindak lanjut.',
            'filename' => 'laporan_respons_pengumuman',
            'headings' => $headings,
            'rows' => $this->flattenGroupedRows($groupedRows),
            'grouped_rows' => $groupedRows,
            'summary_lines' => [
                'Total respons: ' . $responses->count(),
                'Menerima: ' . $responses->where('response', 'accepted')->count(),
                'Mengajukan keberatan: ' . $responses->where('response', 'objected')->count(),
            ],
        ];
    }

    private function groupRowsByOriginClass(
        Collection $items,
        callable $groupResolver,
        callable $rowResolver,
        int $columnCount,
        ?callable $sortResolver = null
    ): array {
        $sorted = $sortResolver
            ? $items->sortBy($sortResolver)->values()
            : $items->values();

        $rows = [];
        foreach (
            $sorted->groupBy(function ($item) use ($groupResolver) {
                return trim((string) ($groupResolver($item) ?: '-'));
            }) as $group => $groupItems
        ) {
            $groupRows = $groupItems
                ->map($rowResolver)
                ->values()
                ->all();

            foreach ($groupRows as $index => $row) {
                $groupRows[$index][0] = $index + 1;
            }

            $rows[$group] = $groupRows;
        }

        return $rows;
    }

    private function flattenGroupedRows(array $groupedRows): array
    {
        $rows = [];

        foreach ($groupedRows as $group => $groupRows) {
            $rows[] = ['__group' => $group];

            foreach ($groupRows as $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function psychologyScoreText(?array $scores, array $packageMap): string
    {
        if (!$scores) {
            return '-';
        }

        return collect($scores)
            ->map(fn($score, $packageId) => ($packageMap[$packageId] ?? $packageId) . ':' . $score)
            ->implode(', ');
    }

    private function packageSortKey(?string $packageName): string
    {
        if (!$packageName) {
            return 'ZZZ';
        }

        if (preg_match('/kelompok\s+([a-z])/i', $packageName, $matches)) {
            return 'A' . strtoupper($matches[1]) . '|' . $packageName;
        }

        return 'Z' . mb_strtoupper($packageName);
    }
}
