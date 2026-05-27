<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageSubject;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('subjects')->latest()->paginate(10);

        return view('admin.packages.index', compact('packages'));
    }

    public function store(Request $request, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:packages,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['required', 'string', 'max:150'],
        ]);

        DB::transaction(function () use ($request, $validated, $logger) {
            $package = Package::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'],
                'is_active' => $request->boolean('is_active'),
            ]);

            foreach ($validated['subjects'] as $index => $subjectName) {
                $package->subjects()->create([
                    'subject_name' => $subjectName,
                    'order' => $index + 1,
                ]);
            }

            $logger->log('package', 'create', $package);
        });

        return back()->with('success', 'Paket berhasil dibuat.');
    }

    public function update(Request $request, Package $package, ActivityLogService $logger)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:packages,code,' . $package->id],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['required', 'string', 'max:150'],
        ]);

        DB::transaction(function () use ($request, $validated, $package, $logger) {
            $package->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'],
                'is_active' => $request->boolean('is_active'),
            ]);

            $package->subjects()->delete();

            foreach ($validated['subjects'] as $index => $subjectName) {
                $package->subjects()->create([
                    'subject_name' => $subjectName,
                    'order' => $index + 1,
                ]);
            }

            $logger->log('package', 'update', $package);
        });

        return back()->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package, ActivityLogService $logger)
    {
        $logger->log('package', 'delete', $package);

        $package->delete();

        return back()->with('success', 'Jurusan berhasil dihapus.');
    }

    public function storeSubject(Request $request, Package $package)
    {
        $validated = $request->validate([
            'subject_name' => ['required', 'string', 'max:150'],
        ]);

        $package->subjects()->create([
            'subject_name' => $validated['subject_name'],
            'order' => $package->subjects()->count() + 1,
        ]);

        return back()->with('success', 'Mapel jurusan ditambahkan.');
    }

    public function destroySubject(Package $package, PackageSubject $subject)
    {
        abort_if($subject->package_id !== $package->id, 404);

        $subject->delete();

        return back()->with('success', 'Mapel jurusan dihapus.');
    }
}
