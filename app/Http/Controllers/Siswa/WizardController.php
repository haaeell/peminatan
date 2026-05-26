<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Package;
use App\Models\StudentPackageChoice;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WizardController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student()->with([
            'biodata',
            'selfie',
            'packageChoice',
        ])->firstOrFail();

        $announcement = Announcement::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->first();


        $packages = Package::where('is_active', true)->with('subjects')->get();

        return view('siswa.wizard.index', compact('student', 'packages', 'announcement'));
    }

    public function saveBiodata(Request $request)
    {
        $validated = $request->validate([
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'gender' => ['required', 'in:L,P'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'father_name' => ['required', 'string', 'max:100'],
            'mother_name' => ['required', 'string', 'max:100'],
            'parent_phone' => ['required', 'string', 'max:20'],
        ]);

        $student = auth()->user()->student;

        DB::transaction(function () use ($student, $validated) {
            $student->biodata()->updateOrCreate(
                ['student_id' => $student->id],
                $validated
            );

            $student->update(['status' => 'package_choice']);
        });

        return response()->json([
            'message' => 'Biodata berhasil disimpan.',
            'next_step' => 'package_choice',
        ]);
    }

    public function savePackageChoice(Request $request)
    {
        $validated = $request->validate([
            'first_package_id' => [
                'required',
                'exists:packages,id',
                'different:second_package_id',
            ],
            'second_package_id' => [
                'required',
                'exists:packages,id',
                'different:first_package_id',
            ],
        ]);

        $student = auth()->user()->student;

        DB::transaction(function () use ($student, $validated) {
            StudentPackageChoice::updateOrCreate(
                ['student_id' => $student->id],
                $validated + ['student_id' => $student->id]
            );

            $student->update(['status' => 'selfie']);
        });

        return response()->json([
            'message' => 'Pilihan jurusan berhasil disimpan.',
            'next_step' => 'selfie',
        ]);
    }

    public function saveSelfie(Request $request)
    {
        $validated = $request->validate([
            'photo' => ['required', 'string'],
            'device_info' => ['required', 'array'],
        ]);

        $student = auth()->user()->student;

        $image = preg_replace('/^data:image\/\w+;base64,/', '', $validated['photo']);
        $image = base64_decode($image, true);

        abort_if($image === false, 422, 'Foto tidak valid.');

        $path = 'selfies/student-' . $student->id . '-' . now()->timestamp . '.jpg';

        Storage::disk('public')->put($path, $image);

        DB::transaction(function () use ($student, $path, $validated) {
            $student->selfie()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'path' => $path,
                    'device_info' => $validated['device_info'],
                    'captured_at' => now(),
                ]
            );

            $student->update(['status' => 'waiting_session']);
        });

        return response()->json([
            'message' => 'Selfie berhasil disimpan.',
            'next_step' => 'waiting_session',
        ]);
    }

    public function waitingSession()
    {
        $student = auth()->user()->student;

        $session = TestSession::where('is_active', true)
            ->whereHas('classes', function ($query) use ($student) {
                $query->where('origin_class', $student->origin_class);
            })
            ->orderBy('test_date')
            ->orderBy('start_time')
            ->first();

        return view('siswa.waiting-session', compact('student', 'session'));
    }
}
