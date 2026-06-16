<?php

namespace App\Services;

use App\Models\ClassGroup;
use App\Models\ClassStudent;
use App\Models\Package;
use App\Models\TestResult;
use Illuminate\Support\Facades\DB;

class ClassDistributionService
{
    private int $classCapacity = 30;

    public function distribute(): void
    {
        DB::transaction(function () {
            // Hapus hasil auto lama, manual tetap aman
            ClassStudent::where('is_manual_override', false)->delete();

            // Hapus kelas kosong yang belum dikunci
            ClassGroup::where('is_locked', false)
                ->whereDoesntHave('students')
                ->delete();

            $packages = Package::where('is_active', true)->get()->keyBy('id');

            $results = TestResult::with(['student'])
                ->where('is_locked', false)
                ->whereNotNull('recommended_package_id')
                ->get()
                ->filter(fn($result) => $packages->has($result->recommended_package_id))
                ->groupBy('recommended_package_id');

            foreach ($results as $packageId => $packageResults) {
                $package = $packages[$packageId];

                $sortedResults = $packageResults
                    ->sortByDesc(fn($result) => (float) $result->academic_score)
                    ->values();

                foreach ($sortedResults->chunk($this->classCapacity) as $index => $chunk) {
                    $classGroup = ClassGroup::firstOrCreate(
                        [
                            'package_id' => $packageId,
                            'name' => 'XI ' . $package->code . ' ' . ($index + 1),
                        ],
                        [
                            'capacity' => $this->classCapacity,
                            'is_locked' => false,
                        ]
                    );

                    foreach ($chunk as $result) {
                        ClassStudent::updateOrCreate(
                            ['student_id' => $result->student_id],
                            [
                                'class_group_id' => $classGroup->id,
                                'package_id' => $packageId,
                                'is_manual_override' => false,
                            ]
                        );

                        $result->update([
                            'final_package_id' => $packageId,
                        ]);
                    }
                }
            }
        });
    }

    public function manualMove(int $studentId, int $classGroupId): void
    {
        DB::transaction(function () use ($studentId, $classGroupId) {
            $classGroup = ClassGroup::findOrFail($classGroupId);

            if ($classGroup->is_locked) {
                abort(422, 'Kelas sudah dikunci.');
            }

            // Effective occupancy: use pending_class_group_id if set, else class_group_id.
            // This correctly excludes students staged OUT of this class and includes those staged IN.
            $effectiveCount = ClassStudent::whereRaw(
                'COALESCE(pending_class_group_id, class_group_id) = ?',
                [$classGroup->id]
            )->where('student_id', '!=', $studentId)->count();

            if ($effectiveCount >= $classGroup->capacity) {
                abort(422, 'Kapasitas kelas sudah penuh.');
            }

            $existing = ClassStudent::where('student_id', $studentId)->first();

            if ($existing) {
                // Stage the change — live columns stay until Final Announcement is published.
                $existing->update([
                    'pending_class_group_id' => $classGroup->id,
                    'pending_package_id' => $classGroup->package_id,
                    'is_manual_override' => true,
                ]);
            } else {
                // First-time assignment (never auto-distributed): write to both live and pending
                // so the student is immediately visible in the class while staging is still respected.
                ClassStudent::create([
                    'student_id' => $studentId,
                    'class_group_id' => $classGroup->id,
                    'package_id' => $classGroup->package_id,
                    'pending_class_group_id' => $classGroup->id,
                    'pending_package_id' => $classGroup->package_id,
                    'is_manual_override' => true,
                ]);
            }
        });
    }

    public function lockAll(): void
    {
        DB::transaction(function () {
            TestResult::query()->update(['is_locked' => true]);
            ClassGroup::query()->update(['is_locked' => true]);
        });
    }
}
