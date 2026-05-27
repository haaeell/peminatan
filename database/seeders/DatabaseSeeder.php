<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (
            [
                'activity_logs',
                'violations',
                'objections',
                'announcement_responses',
                'announcements',
                'class_students',
                'class_groups',
                'test_results',
                'student_psychology_answers',
                'psychology_option_weights',
                'psychology_question_options',
                'psychology_questions',
                'student_academic_answers',
                'academic_question_options',
                'academic_questions',
                'student_package_choices',
                'package_subjects',
                'packages',
                'student_test_sessions',
                'test_session_classes',
                'test_sessions',
                'student_selfies',
                'student_biodatas',
                'students',
                'settings',
                'users',
            ] as $table
        ) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::transaction(function () {
            /*
            |--------------------------------------------------------------------------
            | SETTINGS
            |--------------------------------------------------------------------------
            */
            DB::table('settings')->insert([
                [
                    'key' => 'school_name',
                    'value' => 'SMA Negeri 1',
                    'group' => 'general',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'cbt_duration_minutes',
                    'value' => '60',
                    'group' => 'cbt',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'max_violation_limit',
                    'value' => '5',
                    'group' => 'security',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | ADMIN
            |--------------------------------------------------------------------------
            */
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | PACKAGES / PAKET JURUSAN
            |--------------------------------------------------------------------------
            */
            $kelompokA = DB::table('packages')->insertGetId([
                'code' => 'A',
                'name' => 'Paket A',
                'description' => 'Paket peminatan Fisika, Kimia, Matematika Lanjut, dan Geografi',
                'color' => '#9ca3af',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $kelompokB = DB::table('packages')->insertGetId([
                'code' => 'B',
                'name' => 'Paket B',
                'description' => 'Paket peminatan Kimia, Biologi, Sosiologi, dan Bahasa Inggris Lanjut',
                'color' => '#86efac',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $kelompokC = DB::table('packages')->insertGetId([
                'code' => 'C',
                'name' => 'Paket C',
                'description' => 'Paket peminatan Sosiologi, Ekonomi, Bahasa Inggris Lanjut, dan Bahasa Jerman',
                'color' => '#fdba74',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $kelompokD = DB::table('packages')->insertGetId([
                'code' => 'D',
                'name' => 'Paket D',
                'description' => 'Paket peminatan Ekonomi, Geografi, Sejarah Lanjut, dan Bahasa Jerman',
                'color' => '#93c5fd',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $packageIds = [$kelompokA, $kelompokB, $kelompokC, $kelompokD];

            /*
            |--------------------------------------------------------------------------
            | PACKAGE SUBJECTS / MATA PELAJARAN PAKET
            |--------------------------------------------------------------------------
            */
            $subjects = [
                $kelompokA => [
                    'Fisika',
                    'Kimia',
                    'Matematika Lanjut',
                    'Geografi',
                ],

                $kelompokB => [
                    'Kimia',
                    'Biologi',
                    'Sosiologi',
                    'Bahasa Inggris Lanjut',
                ],

                $kelompokC => [
                    'Sosiologi',
                    'Ekonomi',
                    'Bahasa Inggris Lanjut',
                    'Bahasa Jerman',
                ],

                $kelompokD => [
                    'Ekonomi',
                    'Geografi',
                    'Sejarah Lanjut',
                    'Bahasa Jerman',
                ],
            ];

            foreach ($subjects as $packageId => $subjectList) {
                foreach ($subjectList as $index => $subject) {
                    DB::table('package_subjects')->insert([
                        'package_id' => $packageId,
                        'subject_name' => $subject,
                        'order' => $index + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | TEST SESSIONS
            |--------------------------------------------------------------------------
            */
            $session1 = DB::table('test_sessions')->insertGetId([
                'name' => 'Sesi 1',
                'test_date' => now()->toDateString(),
                'start_time' => '07:00:00',
                'end_time' => '09:00:00',
                'test_type' => 'both',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $session2 = DB::table('test_sessions')->insertGetId([
                'name' => 'Sesi 2',
                'test_date' => now()->toDateString(),
                'start_time' => '09:30:00',
                'end_time' => '11:30:00',
                'test_type' => 'both',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $originClasses = ['X A', 'X B', 'X C', 'X D', 'X E', 'X F', 'X G', 'X H'];

            foreach (array_slice($originClasses, 0, 4) as $class) {
                DB::table('test_session_classes')->insert([
                    'test_session_id' => $session1,
                    'origin_class' => $class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (array_slice($originClasses, 4) as $class) {
                DB::table('test_session_classes')->insert([
                    'test_session_id' => $session2,
                    'origin_class' => $class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CLASS GROUPS
            |--------------------------------------------------------------------------
            */
            $classGroups = [
                $kelompokA => ['XI A 1', 'XI A 2'],
                $kelompokB => ['XI B 1', 'XI B 2'],
                $kelompokC => ['XI C 1', 'XI C 2'],
                $kelompokD => ['XI D 1', 'XI D 2'],
            ];

            $classGroupIdsByPackage = [];

            foreach ($classGroups as $packageId => $groups) {
                foreach ($groups as $groupName) {
                    $classGroupIdsByPackage[$packageId][] = DB::table('class_groups')->insertGetId([
                        'package_id' => $packageId,
                        'name' => $groupName,
                        'capacity' => 36,
                        'is_locked' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ACADEMIC QUESTIONS
            |--------------------------------------------------------------------------
            */
            $academicQuestionIds = [];
            $academicOptionsByQuestion = [];

            for ($q = 1; $q <= 40; $q++) {
                $questionId = DB::table('academic_questions')->insertGetId([
                    'question' => 'Soal akademik nomor ' . $q,
                    'order' => $q,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $academicQuestionIds[] = $questionId;

                foreach (['A', 'B', 'C', 'D'] as $index => $label) {
                    $optionId = DB::table('academic_question_options')->insertGetId([
                        'academic_question_id' => $questionId,
                        'label' => $label,
                        'option_text' => 'Pilihan ' . $label,
                        'is_correct' => $index === 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $academicOptionsByQuestion[$questionId][] = [
                        'id' => $optionId,
                        'is_correct' => $index === 0,
                    ];
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PSYCHOLOGY QUESTIONS
            |--------------------------------------------------------------------------
            */
            $psychologyQuestionIds = [];
            $psychologyOptionsByQuestion = [];

            for ($q = 1; $q <= 30; $q++) {
                $questionId = DB::table('psychology_questions')->insertGetId([
                    'question' => 'Saya lebih suka aktivitas nomor ' . $q,
                    'order' => $q,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $psychologyQuestionIds[] = $questionId;

                $options = [
                    [
                        'label' => 'A',
                        'text' => 'Eksperimen, sains, dan analisis data',
                        'package_id' => $kelompokA,
                    ],
                    [
                        'label' => 'B',
                        'text' => 'Diskusi sosial, ekonomi, dan organisasi',
                        'package_id' => $kelompokB,
                    ],
                    [
                        'label' => 'C',
                        'text' => 'Bahasa, sastra, seni, dan komunikasi',
                        'package_id' => $kelompokC,
                    ],
                    [
                        'label' => 'D',
                        'text' => 'Teknologi, informatika, dan desain',
                        'package_id' => $kelompokD,
                    ],
                ];

                foreach ($options as $option) {
                    $optionId = DB::table('psychology_question_options')->insertGetId([
                        'psychology_question_id' => $questionId,
                        'label' => $option['label'],
                        'option_text' => $option['text'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $psychologyOptionsByQuestion[$questionId][] = [
                        'id' => $optionId,
                        'package_id' => $option['package_id'],
                    ];

                    foreach ($packageIds as $packageId) {
                        DB::table('psychology_option_weights')->insert([
                            'psychology_question_option_id' => $optionId,
                            'package_id' => $packageId,
                            'weight' => $packageId === $option['package_id'] ? 10 : rand(1, 4),
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | STUDENTS + ANSWERS + RESULTS
            |--------------------------------------------------------------------------
            */
            $classCounter = [];

            foreach ($packageIds as $packageId) {
                foreach ($classGroupIdsByPackage[$packageId] as $classGroupId) {
                    $classCounter[$classGroupId] = 0;
                }
            }

            for ($i = 1; $i <= 200; $i++) {
                $nisn = '2025' . str_pad($i, 6, '0', STR_PAD_LEFT);
                $originClass = $originClasses[($i - 1) % count($originClasses)];
                $sessionId = in_array($originClass, ['X A', 'X B', 'X C', 'X D']) ? $session1 : $session2;

                $user = User::create([
                    'name' => 'Siswa ' . $i,
                    'nisn' => $nisn,
                    'password' => Hash::make('12345678'),
                    'role' => 'siswa',
                    'is_active' => true,
                ]);

                $studentId = DB::table('students')->insertGetId([
                    'user_id' => $user->id,
                    'nisn' => $nisn,
                    'nis' => 'NIS-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'name' => 'Siswa ' . $i,
                    'origin_class' => $originClass,
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('student_biodatas')->insert([
                    'student_id' => $studentId,
                    'birth_place' => fake()->city(),
                    'birth_date' => fake()->dateTimeBetween('2008-01-01', '2010-12-31')->format('Y-m-d'),
                    'gender' => rand(0, 1) ? 'L' : 'P',
                    'address' => 'Alamat siswa ' . $i,
                    'phone' => '0812' . rand(10000000, 99999999),
                    'father_name' => 'Ayah Siswa ' . $i,
                    'mother_name' => 'Ibu Siswa ' . $i,
                    'parent_phone' => '0813' . rand(10000000, 99999999),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('student_selfies')->insert([
                    'student_id' => $studentId,
                    'path' => 'selfies/default-avatar.png',
                    'device_info' => json_encode([
                        'browser' => 'Seeder',
                        'platform' => 'Laravel',
                    ]),
                    'captured_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $firstPackageId = $packageIds[array_rand($packageIds)];
                $secondPackageOptions = array_values(array_diff($packageIds, [$firstPackageId]));
                $secondPackageId = $secondPackageOptions[array_rand($secondPackageOptions)];

                DB::table('student_package_choices')->insert([
                    'student_id' => $studentId,
                    'first_package_id' => $firstPackageId,
                    'second_package_id' => $secondPackageId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('student_test_sessions')->insert([
                    'student_id' => $studentId,
                    'test_session_id' => $sessionId,
                    'started_at' => now()->subMinutes(rand(80, 120)),
                    'finished_at' => now()->subMinutes(rand(1, 30)),
                    'status' => 'finished',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Academic Answers
                |--------------------------------------------------------------------------
                */
                $correctCount = 0;

                foreach ($academicQuestionIds as $questionId) {
                    $options = $academicOptionsByQuestion[$questionId];

                    $isCorrectAnswer = rand(1, 100) <= rand(45, 95);

                    if ($isCorrectAnswer) {
                        $selectedOption = collect($options)->firstWhere('is_correct', true);
                    } else {
                        $selectedOption = collect($options)->where('is_correct', false)->random();
                    }

                    if ($selectedOption['is_correct']) {
                        $correctCount++;
                    }

                    DB::table('student_academic_answers')->insert([
                        'student_id' => $studentId,
                        'academic_question_id' => $questionId,
                        'academic_question_option_id' => $selectedOption['id'],
                        'is_correct' => $selectedOption['is_correct'],
                        'answered_at' => now(),
                    ]);
                }

                $academicScore = round(($correctCount / count($academicQuestionIds)) * 100, 2);

                /*
                |--------------------------------------------------------------------------
                | Psychology Answers
                |--------------------------------------------------------------------------
                */
                $psychologyScores = [
                    $kelompokA => 0,
                    $kelompokB => 0,
                    $kelompokC => 0,
                    $kelompokD => 0,
                ];

                $dominantPackage = match (true) {
                    $i % 4 === 1 => $kelompokA,
                    $i % 4 === 2 => $kelompokB,
                    $i % 4 === 3 => $kelompokC,
                    default => $kelompokD,
                };

                foreach ($psychologyQuestionIds as $questionId) {
                    $options = $psychologyOptionsByQuestion[$questionId];

                    if (rand(1, 100) <= 70) {
                        $selectedOption = collect($options)->firstWhere('package_id', $dominantPackage);
                    } else {
                        $selectedOption = collect($options)->random();
                    }

                    DB::table('student_psychology_answers')->insert([
                        'student_id' => $studentId,
                        'psychology_question_id' => $questionId,
                        'psychology_question_option_id' => $selectedOption['id'],
                        'answered_at' => now(),
                    ]);

                    foreach ($packageIds as $packageId) {
                        $psychologyScores[$packageId] += $packageId === $selectedOption['package_id']
                            ? 10
                            : rand(1, 4);
                    }
                }

                arsort($psychologyScores);

                $recommendedPackageId = array_key_first($psychologyScores);

                $finalPackageId = rand(1, 100) <= 75
                    ? $recommendedPackageId
                    : $firstPackageId;

                DB::table('test_results')->insert([
                    'student_id' => $studentId,
                    'academic_score' => $academicScore,
                    'psychology_scores' => json_encode($psychologyScores),
                    'recommended_package_id' => $recommendedPackageId,
                    'final_package_id' => $finalPackageId,
                    'is_locked' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Class Distribution
                |--------------------------------------------------------------------------
                */
                $selectedClassGroupId = null;

                foreach ($classGroupIdsByPackage[$finalPackageId] as $classGroupId) {
                    if ($classCounter[$classGroupId] < 36) {
                        $selectedClassGroupId = $classGroupId;
                        break;
                    }
                }

                if ($selectedClassGroupId) {
                    $classCounter[$selectedClassGroupId]++;

                    DB::table('class_students')->insert([
                        'class_group_id' => $selectedClassGroupId,
                        'student_id' => $studentId,
                        'package_id' => $finalPackageId,
                        'is_manual_override' => rand(1, 100) <= 15,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ANNOUNCEMENT SAMPLE
            |--------------------------------------------------------------------------
            */
            $announcementId = DB::table('announcements')->insertGetId([
                'type' => 'temporary',
                'title' => 'Pengumuman Hasil Sementara',
                'content' => 'Berikut adalah hasil sementara pemilihan jurusan.',
                'is_published' => true,
                'published_at' => now(),
                'published_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | OBJECTIONS SAMPLE
            |--------------------------------------------------------------------------
            */
            for ($i = 1; $i <= 12; $i++) {
                DB::table('announcement_responses')->insert([
                    'announcement_id' => $announcementId,
                    'student_id' => $i,
                    'response' => 'objected',
                    'responded_at' => now(),
                ]);

                DB::table('objections')->insert([
                    'student_id' => $i,
                    'announcement_id' => $announcementId,
                    'reason' => 'Saya ingin mengajukan keberatan terhadap hasil jurusan karena ingin menyesuaikan dengan minat saya.',
                    'status' => $i <= 6 ? 'pending' : ($i <= 9 ? 'approved' : 'rejected'),
                    'admin_note' => $i <= 6 ? null : 'Sudah ditinjau oleh admin.',
                    'reviewed_by' => $i <= 6 ? null : 1,
                    'reviewed_at' => $i <= 6 ? null : now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
