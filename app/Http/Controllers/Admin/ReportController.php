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
        ])->orderBy('name')->get();

        $headings = [
            'Nama',
            'NISN',
            'NIS',
            'Kelas Asal',
            'Status',
            'Akun Aktif',
            'TTL',
            'Jenis Kelamin',
            'No HP',
            'Ayah',
            'Ibu',
            'No HP Ortu',
            'Pilihan 1',
            'Pilihan 2',
            'Rencana Setelah Lulus',
            'Kelas Hasil',
        ];

        $rows = $students->map(function ($student) {
            $biodata = $student->biodata;

            return [
                $student->name,
                $student->nisn,
                $student->nis ?: '-',
                $student->origin_class ?: '-',
                $student->status,
                $student->user?->is_active ? 'Aktif' : 'Nonaktif',
                $biodata
                    ? trim(($biodata->birth_place ?: '-') . ', ' . optional($biodata->birth_date)->format('d-m-Y'))
                    : '-',
                $biodata?->gender ?: '-',
                $biodata?->phone ?: '-',
                $biodata?->father_name ?: '-',
                $biodata?->mother_name ?: '-',
                $biodata?->parent_phone ?: '-',
                $student->packageChoice?->firstPackage?->name ?: '-',
                $student->packageChoice?->secondPackage?->name ?: '-',
                $student->packageChoice?->post_graduation_plan ?: '-',
                $student->classStudent?->classGroup?->name ?: '-',
            ];
        })->all();

        return [
            'title' => 'Laporan Data Siswa Lengkap',
            'subtitle' => 'Ringkasan identitas, biodata, pilihan jurusan, dan status siswa.',
            'filename' => 'laporan_data_siswa_lengkap',
            'headings' => $headings,
            'rows' => $rows,
            'summary_lines' => [
                'Total siswa: ' . $students->count(),
                'Akun aktif: ' . $students->filter(fn ($student) => $student->user?->is_active)->count(),
                'Sudah memiliki biodata: ' . $students->filter(fn ($student) => $student->biodata)->count(),
            ],
        ];
    }

    private function testResultReport(): array
    {
        $packageMap = Package::withTrashed()->pluck('code', 'id')->all();

        $results = TestResult::with([
            'student.packageChoice.firstPackage',
            'student.packageChoice.secondPackage',
            'student.classStudent.classGroup',
            'recommendedPackage',
            'finalPackage',
        ])->orderByDesc('academic_score')->get();

        $headings = [
            'Nama',
            'NISN',
            'Kelas Asal',
            'Nilai Akademik',
            'Skor Psikotes',
            'Rekomendasi',
            'Final',
            'Kelas Hasil',
            'Pilihan 1',
            'Pilihan 2',
            'Rencana Setelah Lulus',
        ];

        $rows = $results->map(function ($result) use ($packageMap) {
            return [
                $result->student?->name ?: '-',
                $result->student?->nisn ?: '-',
                $result->student?->origin_class ?: '-',
                (string) $result->academic_score,
                $this->psychologyScoreText($result->psychology_scores, $packageMap),
                $result->recommendedPackage?->name ?: '-',
                $result->finalPackage?->name ?: '-',
                $result->student?->classStudent?->classGroup?->name ?: '-',
                $result->student?->packageChoice?->firstPackage?->name ?: '-',
                $result->student?->packageChoice?->secondPackage?->name ?: '-',
                $result->student?->packageChoice?->post_graduation_plan ?: '-',
            ];
        })->all();

        return [
            'title' => 'Laporan Hasil Tes Siswa',
            'subtitle' => 'Nilai akademik, skor psikotes, dan rekomendasi penempatan.',
            'filename' => 'laporan_hasil_tes_siswa',
            'headings' => $headings,
            'rows' => $rows,
            'summary_lines' => [
                'Total hasil tes: ' . $results->count(),
                'Sudah punya final jurusan: ' . $results->filter(fn ($result) => $result->finalPackage)->count(),
                'Sudah dibagi kelas: ' . $results->filter(fn ($result) => $result->student?->classStudent)->count(),
            ],
        ];
    }

    private function classDistributionReport(): array
    {
        $classStudents = ClassStudent::with([
            'student',
            'classGroup.package',
            'package',
        ])->orderBy('class_group_id')->get();

        $headings = [
            'Nama',
            'NISN',
            'Kelas Asal',
            'Jurusan',
            'Kelas Hasil',
            'Jenis Penempatan',
        ];

        $rows = $classStudents->map(function ($item) {
            return [
                $item->student?->name ?: '-',
                $item->student?->nisn ?: '-',
                $item->student?->origin_class ?: '-',
                $item->package?->name ?: '-',
                $item->classGroup?->name ?: '-',
                $item->is_manual_override ? 'Manual' : 'Otomatis',
            ];
        })->all();

        return [
            'title' => 'Laporan Distribusi Kelas',
            'subtitle' => 'Daftar siswa berdasarkan hasil jurusan dan kelas penempatan.',
            'filename' => 'laporan_distribusi_kelas',
            'headings' => $headings,
            'rows' => $rows,
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
        ])->orderByDesc('responded_at')->get();

        $objections = Objection::with(['student', 'announcement'])
            ->orderByDesc('created_at')
            ->get()
            ->keyBy(fn ($objection) => $objection->announcement_id . '-' . $objection->student_id);

        $headings = [
            'Nama',
            'NISN',
            'Pengumuman',
            'Tipe',
            'Respons',
            'Tanggal Respons',
            'Status Keberatan',
            'Alasan Keberatan',
        ];

        $rows = $responses->map(function ($response) use ($objections) {
            $objection = $objections->get($response->announcement_id . '-' . $response->student_id);

            return [
                $response->student?->name ?: '-',
                $response->student?->nisn ?: '-',
                $response->announcement?->title ?: '-',
                $response->announcement?->type ?: '-',
                $response->response,
                optional($response->responded_at)->format('d-m-Y H:i') ?: '-',
                $objection?->status ?: '-',
                $objection?->reason ?: '-',
            ];
        })->all();

        return [
            'title' => 'Laporan Respons Pengumuman',
            'subtitle' => 'Penerimaan hasil, keberatan siswa, dan status tindak lanjut.',
            'filename' => 'laporan_respons_pengumuman',
            'headings' => $headings,
            'rows' => $rows,
            'summary_lines' => [
                'Total respons: ' . $responses->count(),
                'Menerima: ' . $responses->where('response', 'accepted')->count(),
                'Mengajukan keberatan: ' . $responses->where('response', 'objected')->count(),
            ],
        ];
    }

    private function psychologyScoreText(?array $scores, array $packageMap): string
    {
        if (!$scores) {
            return '-';
        }

        return collect($scores)
            ->map(fn ($score, $packageId) => ($packageMap[$packageId] ?? $packageId) . ':' . $score)
            ->implode(', ');
    }
}
