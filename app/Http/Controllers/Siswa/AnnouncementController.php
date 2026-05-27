<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementResponse;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;
        abort_if($student->status !== 'completed', 403, 'Pengumuman belum tersedia untuk status Anda.');

        $announcement = Announcement::where('is_published', true)
            ->latest('published_at')
            ->first();

        $classStudent = $student->classStudent()
            ->with('classGroup.package')
            ->first();

        $response = $announcement
            ? AnnouncementResponse::where('announcement_id', $announcement->id)
            ->where('student_id', $student->id)
            ->first()
            : null;

        return view('siswa.announcements.index', compact(
            'student',
            'announcement',
            'classStudent',
            'response'
        ));
    }

    public function accept(Announcement $announcement)
    {
        $student = auth()->user()->student;
        abort_if($student->status !== 'completed', 403, 'Pengumuman belum tersedia untuk status Anda.');
        abort_if(!$announcement->is_published, 404);

        AnnouncementResponse::updateOrCreate(
            [
                'announcement_id' => $announcement->id,
                'student_id' => $student->id,
            ],
            [
                'response' => 'accepted',
                'responded_at' => now(),
            ]
        );

        return back()->with('success', 'Pengumuman berhasil diterima.');
    }

    public function downloadLetter(Announcement $announcement)
    {
        $student = auth()->user()->student;

        abort_if($student->status !== 'completed', 403, 'Surat belum tersedia untuk status Anda.');
        abort_if(!$announcement->is_published, 404);

        $response = AnnouncementResponse::where('announcement_id', $announcement->id)
            ->where('student_id', $student->id)
            ->where('response', 'accepted')
            ->first();

        abort_if(!$response, 403, 'Surat hanya tersedia setelah pengumuman diterima.');

        $classStudent = $student->classStudent()
            ->with('classGroup.package')
            ->first();

        abort_if(!$classStudent, 404, 'Data jurusan dan kelas belum tersedia.');

        $today = Carbon::now();

        $pdf = Pdf::loadView('pdfs.student-announcement-letter', [
            'student' => $student,
            'announcement' => $announcement,
            'classStudent' => $classStudent,
            'response' => $response,
            'schoolName' => Setting::getSetting('school_name', 'Sekolah Menengah Atas'),
            'appName' => Setting::getSetting('app_name', 'Sistem Pemilihan Jurusan'),
            'supportContact' => Setting::getSetting('support_contact', 'Hubungi admin sekolah'),
            'issuedDate' => $today->translatedFormat('d F Y'),
            'logoDataUri' => $this->imageDataUri(public_path('images/logo.png')),
        ])->setPaper('a4', 'portrait');

        $filename = 'surat_keterangan_penempatan_' . $student->nisn . '.pdf';

        return $pdf->download($filename);
    }

    private function imageDataUri(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($path));
    }
}
