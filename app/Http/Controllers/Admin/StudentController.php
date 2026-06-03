<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Exports\StudentsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\Student;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();

        return view('admin.students.index', compact('totalStudents'));
    }

    public function data()
    {
        $query = Student::query()
            ->select('students.*')
            ->with(['user'])
            ->orderByDesc('students.created_at');

        return DataTables::eloquent($query)
            ->addColumn('checkbox', function (Student $student) {
                return '
                    <input type="checkbox"
                        class="checkItem w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        value="' . e($student->id) . '">
                ';
            })
            ->addColumn('name', function (Student $student) {
                $activeBadge = $student->user?->is_active
                    ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold">Aktif</span>'
                    : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold">Nonaktif</span>';

                return '
                    <div class="font-bold text-slate-800">' . e($student->name) . '</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-400 font-medium">
                        <span>NIS: ' . e($student->nis ?? '-') . '</span>
                        ' . $activeBadge . '
                    </div>
                ';
            })
            ->addColumn('nisn', function (Student $student) {
                return e($student->nisn);
            })
            ->addColumn('origin_class', function (Student $student) {
                return e($student->origin_class);
            })
            ->addColumn('aksi', function (Student $student) {
                return '
                    <div class="flex items-center gap-2">
                        <button type="button"
                            class="editBtn group inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl
                            bg-blue-50 text-blue-700 border border-blue-100
                            hover:bg-blue-600 hover:text-white hover:border-blue-600
                            shadow-sm hover:shadow-lg hover:shadow-blue-200
                            transition-all duration-300"
                            data-id="' . e($student->id) . '"
                            data-name="' . e($student->name) . '"
                            data-nisn="' . e($student->nisn) . '"
                            data-nis="' . e($student->nis) . '"
                            data-origin_class="' . e($student->origin_class) . '"
                            data-is_active="' . ($student->user?->is_active ? 1 : 0) . '"
                            title="Edit siswa">
                            <i class="fa-solid fa-pen-to-square text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="hidden xl:inline text-sm font-bold">Edit</span>
                        </button>

                        <button type="button"
                            class="deleteStudentBtn group inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl
                            bg-white text-slate-500 border border-slate-200
                            hover:bg-blue-600 hover:text-white hover:border-blue-600
                            shadow-sm hover:shadow-lg hover:shadow-blue-200
                            transition-all duration-300"
                            data-action="' . route('admin.students.destroy', $student) . '"
                            title="Hapus siswa">
                            <i class="fa-solid fa-trash-can text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="hidden xl:inline text-sm font-bold">Hapus</span>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['checkbox', 'name', 'aksi'])
            ->toJson();
    }

    public function show(Student $student)
    {
        return redirect()
            ->route('admin.students.index')
            ->with('warning', 'Halaman detail siswa tidak digunakan. Gunakan tombol Edit dari daftar.');
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nisn' => ['required', 'string', 'max:30', 'unique:users,nisn', 'unique:students,nisn'],
            'nis' => ['nullable', 'string', 'max:30'],
            'origin_class' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request, $logger) {
            $user = User::create([
                'name' => $validated['name'],
                'nisn' => $validated['nisn'],
                'password' => $validated['password'],
                'role' => 'siswa',
                'is_active' => $request->boolean('is_active'),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'nisn' => $validated['nisn'],
                'nis' => $validated['nis'] ?? null,
                'name' => $validated['name'],
                'origin_class' => strtoupper($validated['origin_class']),
                'status' => 'onboarding',
            ]);

            $logger->log('student', 'create', $student);
        });

        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nisn' => [
                'required',
                'string',
                'max:30',
                Rule::unique('students', 'nisn')->ignore($student->id),
                Rule::unique('users', 'nisn')->ignore($student->user_id),
            ],
            'nis' => ['nullable', 'string', 'max:30'],
            'origin_class' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request, $student, $logger) {
            $student->update([
                'name' => $validated['name'],
                'nisn' => $validated['nisn'],
                'nis' => $validated['nis'] ?? null,
                'origin_class' => strtoupper($validated['origin_class']),
            ]);

            $userData = [
                'name' => $validated['name'],
                'nisn' => $validated['nisn'],
                'is_active' => $request->boolean('is_active'),
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $student->user->update($userData);

            $logger->log('student', 'update', $student);
        });

        return back()->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student, ActivityLogService $logger)
    {
        DB::transaction(function () use ($student, $logger) {
            $logger->log('student', 'delete', $student);
            $this->deleteStudents(collect([$student]));
        });

        return back()->with('success', 'Siswa berhasil dihapus.');
    }

    public function bulkDelete(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:students,id'],
        ]);

        $students = Student::whereIn('id', $validated['ids'])->with(['user', 'selfie'])->get();

        DB::transaction(function () use ($students, $logger) {
            foreach ($students as $student) {
                $logger->log('student', 'delete', $student);
            }

            $this->deleteStudents($students);
        });

        return back()->with('success', 'Siswa terpilih berhasil dihapus.');
    }

    public function bulkActivate(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:students,id'],
        ]);

        User::whereHas('student', fn($q) => $q->whereIn('id', $validated['ids']))
            ->update(['is_active' => true]);

        return back()->with('success', 'Siswa terpilih berhasil diaktifkan.');
    }

    public function bulkDeactivate(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:students,id'],
        ]);

        User::whereHas('student', fn($q) => $q->whereIn('id', $validated['ids']))
            ->update(['is_active' => false]);

        return back()->with('success', 'Siswa terpilih berhasil dinonaktifkan.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new StudentsTemplateExport(), 'template_siswa.xlsx');
    }

    public function export()
    {
        return Excel::download(new StudentsExport(), 'data_siswa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ]);

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $import = new StudentsImport();

        Excel::import($import, $request->file('file'));

        if ($import->getImportedCount() > 0) {
            $message = "Import siswa berhasil: {$import->getImportedCount()} data masuk";
        } else {
            $message = 'Import selesai: tidak ada data baru yang masuk';
        }

        if ($import->getSkippedCount() > 0) {
            $skipSummary = collect($import->getSkipSummary())
                ->filter(fn(int $count) => $count > 0)
                ->map(function (int $count, string $reason) {
                    $labels = [
                        'empty' => 'kosong',
                        'duplicate' => 'duplikat',
                        'invalid' => 'format tidak valid',
                    ];

                    return "{$count} {$labels[$reason]}";
                })
                ->implode(', ');

            $message .= ", {$import->getSkippedCount()} baris dilewati ({$skipSummary}).";
        } else {
            $message .= '.';
        }

        return back()->with('success', $message);
    }

    private function deleteStudents(Collection $students): void
    {
        foreach ($students as $student) {
            if ($student->selfie?->path && Storage::disk('public')->exists($student->selfie->path)) {
                Storage::disk('public')->delete($student->selfie->path);
            }

            if ($student->user) {
                $student->user->delete();
                continue;
            }

            $student->delete();
        }
    }
}
