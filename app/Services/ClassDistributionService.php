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

            // Count both live occupants and other students already staged into this class.
            $liveCount = $classGroup->students()
                ->where('student_id', '!=', $studentId)
                ->count();

            $pendingCount = ClassStudent::where('pending_class_group_id', $classGroup->id)
                ->where('student_id', '!=', $studentId)
                ->count();

            if ($liveCount + $pendingCount >= $classGroup->capacity) {
                abort(422, 'Kapasitas kelas sudah penuh.');
            }

            // Stage the change — do NOT write to live columns until Final Announcement is published.
            ClassStudent::updateOrCreate(
                ['student_id' => $studentId],
                [
                    'pending_class_group_id' => $classGroup->id,
                    'pending_package_id' => $classGroup->package_id,
                    'is_manual_override' => true,
                ]
            );
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
