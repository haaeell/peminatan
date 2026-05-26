<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TestResult;
use Illuminate\Support\Facades\DB;

class PsychologyScoringService
{
    public function calculate(Student $student): TestResult
    {
        return DB::transaction(function () use ($student) {
            $scores = [];

            $answers = $student->psychologyAnswers()
                ->with('option.weights.package')
                ->get();

            foreach ($answers as $answer) {
                if (!$answer->option) {
                    continue;
                }

                foreach ($answer->option->weights as $weight) {
                    $packageId = $weight->package_id;
                    $scores[$packageId] = ($scores[$packageId] ?? 0) + $weight->weight;
                }
            }

            arsort($scores);

            $recommendedPackageId = array_key_first($scores);

            return TestResult::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'psychology_scores' => $scores,
                    'recommended_package_id' => $recommendedPackageId,
                ]
            );
        });
    }
}
