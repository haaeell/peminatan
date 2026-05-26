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
        DB::beginTransaction();

        try {

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
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | PACKAGES / JURUSAN
            |--------------------------------------------------------------------------
            */
            $ipa = DB::table('packages')->insertGetId([
                'code' => 'IPA',
                'name' => 'Ilmu Pengetahuan Alam',
                'description' => 'Jurusan fokus sains dan teknologi',
                'capacity' => 72,
                'color' => '#2563eb',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ips = DB::table('packages')->insertGetId([
                'code' => 'IPS',
                'name' => 'Ilmu Pengetahuan Sosial',
                'description' => 'Jurusan fokus sosial dan ekonomi',
                'capacity' => 72,
                'color' => '#dc2626',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bahasa = DB::table('packages')->insertGetId([
                'code' => 'BAHASA',
                'name' => 'Bahasa dan Sastra',
                'description' => 'Jurusan bahasa dan budaya',
                'capacity' => 36,
                'color' => '#16a34a',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | PACKAGE SUBJECTS
            |--------------------------------------------------------------------------
            */

            $subjects = [
                $ipa => [
                    'Matematika',
                    'Fisika',
                    'Kimia',
                    'Biologi',
                ],
                $ips => [
                    'Ekonomi',
                    'Geografi',
                    'Sosiologi',
                    'Sejarah',
                ],
                $bahasa => [
                    'Bahasa Indonesia',
                    'Bahasa Inggris',
                    'Sastra',
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

            /*
            |--------------------------------------------------------------------------
            | TEST SESSION CLASSES
            |--------------------------------------------------------------------------
            */

            foreach (['X A', 'X B', 'X C'] as $class) {

                DB::table('test_session_classes')->insert([
                    'test_session_id' => $session1,
                    'origin_class' => $class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach (['X D', 'X E', 'X F'] as $class) {

                DB::table('test_session_classes')->insert([
                    'test_session_id' => $session2,
                    'origin_class' => $class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | SAMPLE STUDENTS
            |--------------------------------------------------------------------------
            */

            for ($i = 1; $i <= 30; $i++) {

                $nisn = '202500' . str_pad($i, 4, '0', STR_PAD_LEFT);

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
                    'nis' => 'NIS-' . $i,
                    'name' => 'Siswa ' . $i,
                    'origin_class' => match (true) {
                        $i <= 5 => 'X A',
                        $i <= 10 => 'X B',
                        $i <= 15 => 'X C',
                        $i <= 20 => 'X D',
                        $i <= 25 => 'X E',
                        default => 'X F',
                    },
                    'status' => 'onboarding',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('student_biodatas')->insert([
                    'student_id' => $studentId,
                    'birth_place' => 'Yogyakarta',
                    'birth_date' => '2009-01-01',
                    'gender' => rand(0, 1) ? 'L' : 'P',
                    'address' => 'Alamat siswa ' . $i,
                    'phone' => '08123456789',
                    'father_name' => 'Ayah ' . $i,
                    'mother_name' => 'Ibu ' . $i,
                    'parent_phone' => '08111111111',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | ACADEMIC QUESTIONS
            |--------------------------------------------------------------------------
            */

            for ($q = 1; $q <= 20; $q++) {

                $questionId = DB::table('academic_questions')->insertGetId([
                    'question' => 'Soal akademik nomor ' . $q,
                    'order' => $q,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $options = ['A', 'B', 'C', 'D'];

                foreach ($options as $index => $label) {

                    DB::table('academic_question_options')->insert([
                        'academic_question_id' => $questionId,
                        'label' => $label,
                        'option_text' => 'Pilihan ' . $label,
                        'is_correct' => $index === 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PSYCHOLOGY QUESTIONS
            |--------------------------------------------------------------------------
            */

            for ($q = 1; $q <= 20; $q++) {

                $questionId = DB::table('psychology_questions')->insertGetId([
                    'question' => 'Saya lebih suka aktivitas nomor ' . $q,
                    'order' => $q,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $optionA = DB::table('psychology_question_options')->insertGetId([
                    'psychology_question_id' => $questionId,
                    'label' => 'A',
                    'option_text' => 'Eksperimen dan sains',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $optionB = DB::table('psychology_question_options')->insertGetId([
                    'psychology_question_id' => $questionId,
                    'label' => 'B',
                    'option_text' => 'Sosial dan organisasi',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $optionC = DB::table('psychology_question_options')->insertGetId([
                    'psychology_question_id' => $questionId,
                    'label' => 'C',
                    'option_text' => 'Bahasa dan seni',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | WEIGHTS
                |--------------------------------------------------------------------------
                */

                DB::table('psychology_option_weights')->insert([
                    [
                        'psychology_question_option_id' => $optionA,
                        'package_id' => $ipa,
                        'weight' => 10,
                    ],
                    [
                        'psychology_question_option_id' => $optionA,
                        'package_id' => $ips,
                        'weight' => 3,
                    ],
                    [
                        'psychology_question_option_id' => $optionB,
                        'package_id' => $ips,
                        'weight' => 10,
                    ],
                    [
                        'psychology_question_option_id' => $optionB,
                        'package_id' => $ipa,
                        'weight' => 2,
                    ],
                    [
                        'psychology_question_option_id' => $optionC,
                        'package_id' => $bahasa,
                        'weight' => 10,
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CLASS GROUPS
            |--------------------------------------------------------------------------
            */

            DB::table('class_groups')->insert([
                [
                    'package_id' => $ipa,
                    'name' => 'XI IPA 1',
                    'capacity' => 36,
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'package_id' => $ipa,
                    'name' => 'XI IPA 2',
                    'capacity' => 36,
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'package_id' => $ips,
                    'name' => 'XI IPS 1',
                    'capacity' => 36,
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::commit();
        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
