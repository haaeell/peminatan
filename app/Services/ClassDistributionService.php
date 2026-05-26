<?php

namespace App\Services;

use App\Models\ClassGroup;
use App\Models\ClassStudent;
use App\Models\Package;
use App\Models\TestResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClassDistributionService
{
    public function distribute(): void
    {
        DB::transaction(function () {
            ClassStudent::where('is_manual_override', false)->delete();

            $packages = Package::where('is_active', true)->get()->keyBy('id');

            $results = TestResult::with([
                'student.packageChoice',
                'student',
                'recommendedPackage',
            ])
                ->where('is_locked', false)
                ->whereNotNull('recommended_package_id')
                ->get()
                ->sortByDesc(fn($result) => $this->priorityScore($result));

            foreach ($results as $result) {
                $packageId = $this->resolveBestPackage($result, $packages);

                if (!$packageId) {
                    continue;
                }

                $classGroup = $this->findAvailableClass($packageId)
                    ?? $this->createNextClass($packageId);

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
        });
    }

    public function manualMove(int $studentId, int $classGroupId): void
    {
        DB::transaction(function () use ($studentId, $classGroupId) {
            $classGroup = ClassGroup::findOrFail($classGroupId);

            $currentCount = $classGroup->students()->count();

            if ($currentCount >= $classGroup->capacity) {
                abort(422, 'Kapasitas kelas sudah penuh.');
            }

            ClassStudent::updateOrCreate(
                ['student_id' => $studentId],
                [
                    'class_group_id' => $classGroup->id,
                    'package_id' => $classGroup->package_id,
                    'is_manual_override' => true,
                ]
            );

            TestResult::where('student_id', $studentId)->update([
                'final_package_id' => $classGroup->package_id,
            ]);
        });
    }

    public function lockAll(): void
    {
        DB::transaction(function () {
            TestResult::query()->update(['is_locked' => true]);
            ClassGroup::query()->update(['is_locked' => true]);
        });
    }

    private function priorityScore(TestResult $result): float
    {
        $choice = $result->student?->packageChoice;

        $choiceBonus = 0;

        if ($choice?->first_package_id === $result->recommended_package_id) {
            $choiceBonus = 20;
        } elseif ($choice?->second_package_id === $result->recommended_package_id) {
            $choiceBonus = 10;
        }

        return ((float) $result->academic_score * 0.7) + $choiceBonus;
    }

    private function resolveBestPackage(TestResult $result, Collection $packages): ?int
    {
        $choice = $result->student?->packageChoice;

        $candidates = array_filter([
            $result->recommended_package_id,
            $choice?->first_package_id,
            $choice?->second_package_id,
        ]);

        foreach (array_unique($candidates) as $packageId) {
            if ($packages->has($packageId) && $this->packageHasCapacity($packageId)) {
                return $packageId;
            }
        }

        return null;
    }

    private function packageHasCapacity(int $packageId): bool
    {
        $package = Package::findOrFail($packageId);

        $used = ClassStudent::where('package_id', $packageId)->count();

        return $used < $package->capacity;
    }

    private function findAvailableClass(int $packageId): ?ClassGroup
    {
        return ClassGroup::where('package_id', $packageId)
            ->where('is_locked', false)
            ->withCount('students')
            ->get()
            ->first(fn($class) => $class->students_count < 30);
    }

    private function createNextClass(int $packageId): ClassGroup
    {
        $package = Package::findOrFail($packageId);

        $number = ClassGroup::where('package_id', $packageId)->count() + 1;

        return ClassGroup::create([
            'package_id' => $packageId,
            'name' => 'XI ' . $package->code . ' ' . $number,
            'capacity' => 30,
            'is_locked' => false,
        ]);
    }
}
