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
            $this->seedSettings();
            $adminId = $this->seedAdmin();
            $packages = $this->seedPackages();
            $this->seedPackageSubjects($packages);
            $this->seedTestSessions();
            $this->seedSampleStudents();
            $this->seedAcademicQuestions();
            $this->seedPsychologyQuestions($packages);
            $this->seedAnnouncement($adminId);
        });
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'Sistem Pemilihan Jurusan', 'group' => 'general'],
            ['key' => 'school_name', 'value' => 'SMA Negeri 1 Contoh', 'group' => 'general'],
            ['key' => 'support_contact', 'value' => 'Admin BK / WA 0812-0000-0000', 'group' => 'general'],
            ['key' => 'login_help_text', 'value' => 'Masuk menggunakan email admin atau NISN siswa yang sudah dibagikan sekolah.', 'group' => 'general'],
            ['key' => 'academic_duration_minutes', 'value' => '90', 'group' => 'cbt'],
            ['key' => 'psychology_duration_minutes', 'value' => '60', 'group' => 'cbt'],
            ['key' => 'cbt_auto_submit_violation_limit', 'value' => '3', 'group' => 'cbt'],
            ['key' => 'cbt_force_fullscreen', 'value' => '1', 'group' => 'cbt'],
            ['key' => 'cbt_warning_message', 'value' => 'Perpindahan tab, keluar fullscreen, atau aktivitas mencurigakan akan dicatat oleh sistem.', 'group' => 'cbt'],
            ['key' => 'student_help_text', 'value' => 'Pastikan perangkat stabil, gunakan koneksi yang baik, dan hubungi admin bila ada kendala teknis.', 'group' => 'student'],
        ];

        DB::table('settings')->insert($this->withTimestamps($settings));
    }

    private function seedAdmin(): int
    {
        return User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ])->id;
    }

    private function seedPackages(): array
    {
        $packages = [
            [
                'code' => 'A',
                'name' => 'Kelompok A',
                'description' => 'Fokus pada penalaran ilmiah, eksperimen, dan pemecahan masalah kuantitatif.',
                'color' => '#2563eb',
            ],
            [
                'code' => 'B',
                'name' => 'Kelompok B',
                'description' => 'Fokus pada biologi, kesehatan, observasi detail, dan kepedulian terhadap manusia.',
                'color' => '#16a34a',
            ],
            [
                'code' => 'C',
                'name' => 'Kelompok C',
                'description' => 'Fokus pada ekonomi, sosiologi, sejarah, dan analisis isu sosial.',
                'color' => '#f97316',
            ],
            [
                'code' => 'D',
                'name' => 'Kelompok D',
                'description' => 'Fokus pada bahasa, komunikasi, presentasi, dan kajian budaya.',
                'color' => '#7c3aed',
            ],
        ];

        $packageIds = [];

        foreach ($packages as $package) {
            $packageIds[$package['code']] = DB::table('packages')->insertGetId($this->timestampedRow($package + [
                'is_active' => true,
            ]));
        }

        return $packageIds;
    }

    private function seedPackageSubjects(array $packages): void
    {
        $subjects = [
            'A' => ['Fisika', 'Kimia', 'Mat Lanjut', 'Geografi'],
            'B' => ['Kimia', 'Biologi', 'Sosiologi', 'B. Ing. Lanjut'],
            'C' => ['Sosiologi', 'Ekonomi', 'B. Ing. Lanjut', 'B. Jerman'],
            'D' => ['Ekonomi', 'Geografi', 'Sejarah Lanjut', 'B. Jerman'],
        ];

        foreach ($subjects as $code => $rows) {
            foreach ($rows as $index => $subjectName) {
                DB::table('package_subjects')->insert($this->timestampedRow([
                    'package_id' => $packages[$code],
                    'subject_name' => $subjectName,
                    'order' => $index + 1,
                ]));
            }
        }
    }

    private function seedTestSessions(): void
    {
        $sessions = [
            [
                'name' => 'Sesi Pagi Gelombang 1',
                'test_date' => now()->toDateString(),
                'start_time' => '07:30:00',
                'end_time' => '10:30:00',
                'test_type' => 'both',
                'is_active' => true,
                'classes' => ['X A', 'X B', 'X C', 'X D'],
            ],
            [
                'name' => 'Sesi Pagi Gelombang 2',
                'test_date' => now()->toDateString(),
                'start_time' => '10:45:00',
                'end_time' => '13:45:00',
                'test_type' => 'both',
                'is_active' => true,
                'classes' => ['X E', 'X F', 'X G', 'X H'],
            ],
        ];

        foreach ($sessions as $session) {
            $classes = $session['classes'];
            unset($session['classes']);

            $sessionId = DB::table('test_sessions')->insertGetId($this->timestampedRow($session));

            foreach ($classes as $class) {
                DB::table('test_session_classes')->insert($this->timestampedRow([
                    'test_session_id' => $sessionId,
                    'origin_class' => $class,
                ]));
            }
        }
    }

    private function seedSampleStudents(): void
    {
        $students = [
            ['name' => 'Alya Putri Maharani', 'nisn' => '2026000001', 'nis' => '260001', 'origin_class' => 'X A', 'status' => 'onboarding'],
            ['name' => 'Bagas Pratama', 'nisn' => '2026000002', 'nis' => '260002', 'origin_class' => 'X B', 'status' => 'biodata'],
            ['name' => 'Citra Lestari', 'nisn' => '2026000003', 'nis' => '260003', 'origin_class' => 'X C', 'status' => 'package_choice'],
            ['name' => 'Dimas Ramadhan', 'nisn' => '2026000004', 'nis' => '260004', 'origin_class' => 'X D', 'status' => 'selfie'],
            ['name' => 'Elsa Permata', 'nisn' => '2026000005', 'nis' => '260005', 'origin_class' => 'X E', 'status' => 'waiting_session'],
            ['name' => 'Farhan Rizky', 'nisn' => '2026000006', 'nis' => '260006', 'origin_class' => 'X F', 'status' => 'waiting_session'],
        ];

        $packageIds = DB::table('packages')->pluck('id', 'code')->all();
        $packagePairs = [
            ['first' => $packageIds['A'], 'second' => $packageIds['B']],
            ['first' => $packageIds['B'], 'second' => $packageIds['A']],
            ['first' => $packageIds['C'], 'second' => $packageIds['D']],
            ['first' => $packageIds['D'], 'second' => $packageIds['C']],
            ['first' => $packageIds['A'], 'second' => $packageIds['D']],
            ['first' => $packageIds['B'], 'second' => $packageIds['C']],
        ];

        foreach ($students as $index => $student) {
            $user = User::create([
                'name' => $student['name'],
                'nisn' => $student['nisn'],
                'password' => Hash::make('12345678'),
                'role' => 'siswa',
                'is_active' => true,
            ]);

            $studentId = DB::table('students')->insertGetId($this->timestampedRow([
                'user_id' => $user->id,
                'nisn' => $student['nisn'],
                'nis' => $student['nis'],
                'name' => $student['name'],
                'origin_class' => $student['origin_class'],
                'status' => $student['status'],
            ]));

            if (in_array($student['status'], ['package_choice', 'selfie', 'waiting_session'], true)) {
                DB::table('student_biodatas')->insert($this->timestampedRow([
                    'student_id' => $studentId,
                    'birth_place' => 'Kota Contoh',
                    'birth_date' => '2010-0' . (($index % 6) + 1) . '-15',
                    'gender' => $index % 2 === 0 ? 'P' : 'L',
                    'address' => 'Jl. Pendidikan No. ' . ($index + 1),
                    'phone' => '0812000000' . ($index + 1),
                    'father_name' => 'Bapak ' . explode(' ', $student['name'])[0],
                    'mother_name' => 'Ibu ' . explode(' ', $student['name'])[0],
                    'parent_phone' => '0813000000' . ($index + 1),
                ]));
            }

            if (in_array($student['status'], ['selfie', 'waiting_session'], true)) {
                DB::table('student_package_choices')->insert($this->timestampedRow([
                    'student_id' => $studentId,
                    'first_package_id' => $packagePairs[$index]['first'],
                    'second_package_id' => $packagePairs[$index]['second'],
                ]));
            }

            if ($student['status'] === 'waiting_session') {
                DB::table('student_selfies')->insert($this->timestampedRow([
                    'student_id' => $studentId,
                    'path' => 'selfies/default-avatar.png',
                    'device_info' => json_encode([
                        'browser' => 'Seeder',
                        'platform' => 'Laravel',
                    ]),
                    'captured_at' => now(),
                ]));
            }
        }
    }

    private function seedAcademicQuestions(): void
    {
        $questions = [
            [
                'question' => 'Nilai dari 3x - 7 = 20 adalah ...',
                'correct' => 'B',
                'options' => ['A' => '7', 'B' => '9', 'C' => '11', 'D' => '13'],
            ],
            [
                'question' => 'Jika fungsi f(x) = 2x + 5, maka nilai f(4) adalah ...',
                'correct' => 'D',
                'options' => ['A' => '9', 'B' => '11', 'C' => '12', 'D' => '13'],
            ],
            [
                'question' => 'Sebuah segitiga memiliki alas 12 cm dan tinggi 8 cm. Luas segitiga tersebut adalah ...',
                'correct' => 'C',
                'options' => ['A' => '32 cm2', 'B' => '40 cm2', 'C' => '48 cm2', 'D' => '96 cm2'],
            ],
            [
                'question' => 'Persamaan garis yang melalui titik (0, 3) dan (2, 7) memiliki gradien ...',
                'correct' => 'A',
                'options' => ['A' => '2', 'B' => '3', 'C' => '4', 'D' => '5'],
            ],
            [
                'question' => 'Hukum Newton I menjelaskan bahwa benda akan tetap diam atau bergerak lurus beraturan jika ...',
                'correct' => 'B',
                'options' => [
                    'A' => 'diberi gaya yang makin besar',
                    'B' => 'resultan gaya yang bekerja padanya nol',
                    'C' => 'memiliki massa yang besar',
                    'D' => 'bergerak pada lintasan melingkar',
                ],
            ],
            [
                'question' => 'Satuan gaya dalam Sistem Internasional adalah ...',
                'correct' => 'C',
                'options' => ['A' => 'Joule', 'B' => 'Watt', 'C' => 'Newton', 'D' => 'Pascal'],
            ],
            [
                'question' => 'Perubahan energi yang terjadi pada lampu listrik adalah ...',
                'correct' => 'D',
                'options' => [
                    'A' => 'kimia menjadi gerak',
                    'B' => 'panas menjadi cahaya',
                    'C' => 'gerak menjadi listrik',
                    'D' => 'listrik menjadi cahaya dan panas',
                ],
            ],
            [
                'question' => 'Planet yang dikenal sebagai planet merah adalah ...',
                'correct' => 'A',
                'options' => ['A' => 'Mars', 'B' => 'Venus', 'C' => 'Jupiter', 'D' => 'Merkurius'],
            ],
            [
                'question' => 'pH larutan yang bersifat netral adalah ...',
                'correct' => 'B',
                'options' => ['A' => '3', 'B' => '7', 'C' => '9', 'D' => '12'],
            ],
            [
                'question' => 'Partikel penyusun inti atom adalah ...',
                'correct' => 'C',
                'options' => [
                    'A' => 'elektron dan proton',
                    'B' => 'elektron dan neutron',
                    'C' => 'proton dan neutron',
                    'D' => 'proton dan positron',
                ],
            ],
            [
                'question' => 'Fotosintesis pada tumbuhan terutama berlangsung di bagian ...',
                'correct' => 'D',
                'options' => ['A' => 'akar', 'B' => 'batang', 'C' => 'bunga', 'D' => 'daun'],
            ],
            [
                'question' => 'Fungsi utama hemoglobin dalam darah adalah ...',
                'correct' => 'A',
                'options' => [
                    'A' => 'mengikat dan mengangkut oksigen',
                    'B' => 'membunuh kuman',
                    'C' => 'membekukan darah',
                    'D' => 'menghasilkan hormon',
                ],
            ],
            [
                'question' => 'Organisme yang dapat membuat makanan sendiri disebut ...',
                'correct' => 'B',
                'options' => ['A' => 'heterotrof', 'B' => 'autotrof', 'C' => 'parasit', 'D' => 'dekomposer'],
            ],
            [
                'question' => 'Urutan organisasi kehidupan dari yang paling kecil ke paling besar adalah ...',
                'correct' => 'C',
                'options' => [
                    'A' => 'sel - jaringan - organel - organ',
                    'B' => 'jaringan - sel - organ - sistem organ',
                    'C' => 'sel - jaringan - organ - sistem organ',
                    'D' => 'organel - sel - organ - jaringan',
                ],
            ],
            [
                'question' => 'Salah satu dampak pemanasan global adalah ...',
                'correct' => 'D',
                'options' => [
                    'A' => 'penipisan lapisan tanah',
                    'B' => 'bertambahnya kandungan oksigen',
                    'C' => 'musim selalu tetap',
                    'D' => 'naiknya permukaan air laut',
                ],
            ],
            [
                'question' => 'Kegiatan ekonomi yang menghasilkan barang disebut kegiatan ...',
                'correct' => 'A',
                'options' => ['A' => 'produksi', 'B' => 'distribusi', 'C' => 'konsumsi', 'D' => 'investasi'],
            ],
            [
                'question' => 'Permintaan akan suatu barang cenderung naik ketika harga barang tersebut ...',
                'correct' => 'C',
                'options' => ['A' => 'naik', 'B' => 'tetap', 'C' => 'turun', 'D' => 'langka'],
            ],
            [
                'question' => 'Tokoh yang dikenal sebagai Proklamator Indonesia adalah ...',
                'correct' => 'B',
                'options' => [
                    'A' => 'Ki Hajar Dewantara dan Ahmad Dahlan',
                    'B' => 'Ir. Soekarno dan Drs. Mohammad Hatta',
                    'C' => 'Jenderal Sudirman dan Bung Tomo',
                    'D' => 'Mohammad Yamin dan Soepomo',
                ],
            ],
            [
                'question' => 'Interaksi sosial akan terjadi jika memenuhi syarat kontak sosial dan ...',
                'correct' => 'D',
                'options' => ['A' => 'imitasi', 'B' => 'identifikasi', 'C' => 'motivasi', 'D' => 'komunikasi'],
            ],
            [
                'question' => 'Peta yang menggambarkan satu jenis kenampakan tertentu disebut peta ...',
                'correct' => 'A',
                'options' => ['A' => 'tematik', 'B' => 'umum', 'C' => 'kadaster', 'D' => 'korografi'],
            ],
            [
                'question' => 'Kalimat yang efektif harus memenuhi unsur hemat, jelas, dan ...',
                'correct' => 'C',
                'options' => ['A' => 'bertele-tele', 'B' => 'puitis', 'C' => 'logis', 'D' => 'ambigu'],
            ],
            [
                'question' => 'Sinonim dari kata "cermat" adalah ...',
                'correct' => 'B',
                'options' => ['A' => 'lalai', 'B' => 'teliti', 'C' => 'marah', 'D' => 'santai'],
            ],
            [
                'question' => 'Simple present tense digunakan untuk menyatakan ...',
                'correct' => 'D',
                'options' => [
                    'A' => 'kegiatan yang sedang berlangsung saat ini',
                    'B' => 'rencana masa depan yang pasti',
                    'C' => 'peristiwa lampau yang telah selesai',
                    'D' => 'kebiasaan atau fakta umum',
                ],
            ],
            [
                'question' => 'Ungkapan "The library is across from the mosque" berarti perpustakaan berada ... masjid.',
                'correct' => 'A',
                'options' => ['A' => 'di seberang', 'B' => 'di belakang', 'C' => 'di antara', 'D' => 'di dalam'],
            ],
        ];

        foreach ($questions as $index => $questionData) {
            $questionId = DB::table('academic_questions')->insertGetId($this->timestampedRow([
                'question' => $questionData['question'],
                'image_path' => null,
                'order' => $index + 1,
                'is_active' => true,
            ]));

            foreach ($questionData['options'] as $label => $text) {
                DB::table('academic_question_options')->insert($this->timestampedRow([
                    'academic_question_id' => $questionId,
                    'label' => $label,
                    'option_text' => $text,
                    'is_correct' => $label === $questionData['correct'],
                ]));
            }
        }
    }

    private function seedPsychologyQuestions(array $packages): void
    {
        $questions = [
            [
                'question' => 'Saat mengerjakan tugas kelompok, saya paling nyaman ketika ...',
                'weights' => [
                    'A' => ['text' => 'menganalisis data atau mencari pola yang paling logis', 'focus' => 'STEM'],
                    'B' => ['text' => 'mencermati kebutuhan anggota tim dan menjaga kerja sama tetap sehat', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'menyusun argumen dan melihat dampak keputusan bagi banyak orang', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'menyampaikan ide, mempresentasikan hasil, atau membuat narasi yang menarik', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Topik pembahasan yang paling sering membuat saya penasaran adalah ...',
                'weights' => [
                    'A' => ['text' => 'cara kerja alat, rumus, atau fenomena alam', 'focus' => 'STEM'],
                    'B' => ['text' => 'tubuh manusia, kesehatan, dan gaya hidup sehat', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'masalah sosial, perilaku masyarakat, dan ekonomi', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'bahasa, budaya, dan cara orang berkomunikasi', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Jika diminta membuat proyek mandiri, saya lebih tertarik untuk ...',
                'weights' => [
                    'A' => ['text' => 'merancang percobaan atau prototipe sederhana', 'focus' => 'STEM'],
                    'B' => ['text' => 'membuat edukasi tentang kesehatan atau lingkungan hidup', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'meneliti kebiasaan masyarakat dan menyusun laporannya', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'membuat artikel, video presentasi, atau karya bilingual', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Ketika menghadapi masalah, langkah yang paling sering saya lakukan lebih dulu adalah ...',
                'weights' => [
                    'A' => ['text' => 'mengurai masalah menjadi bagian-bagian kecil dan mencari pola sebab akibat', 'focus' => 'STEM'],
                    'B' => ['text' => 'melihat dampaknya terhadap orang lain dan mencari solusi yang aman', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'mempertimbangkan kondisi sosial, aturan, dan kepentingan bersama', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'mendiskusikan, menuliskan, atau mengomunikasikan inti masalahnya', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Kegiatan ekstrakurikuler yang paling sesuai dengan diri saya cenderung ...',
                'weights' => [
                    'A' => ['text' => 'robotik, sains club, atau coding', 'focus' => 'STEM'],
                    'B' => ['text' => 'PMR, kader kesehatan, atau kegiatan kepedulian', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'debat sosial, OSIS, atau kewirausahaan', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'english club, jurnalistik, atau teater', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Saat membaca berita, saya biasanya paling tertarik pada bagian ...',
                'weights' => [
                    'A' => ['text' => 'inovasi teknologi, penemuan, atau data ilmiah', 'focus' => 'STEM'],
                    'B' => ['text' => 'kesehatan publik, lingkungan, atau gizi', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'ekonomi, pendidikan, atau kebijakan sosial', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'bahasa, budaya populer, atau opini publik', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Guru biasanya mengenali saya sebagai siswa yang ...',
                'weights' => [
                    'A' => ['text' => 'suka hitung-hitungan, eksperimen, dan pemecahan masalah', 'focus' => 'STEM'],
                    'B' => ['text' => 'teliti, sabar, dan peduli pada orang sekitar', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'aktif bertanya tentang fenomena sosial dan kehidupan nyata', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'cukup ekspresif dalam berbicara atau menulis', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Ketika diberikan banyak informasi, saya lebih mudah memahami materi jika ...',
                'weights' => [
                    'A' => ['text' => 'disusun dalam rumus, tabel, diagram, atau langkah sistematis', 'focus' => 'STEM'],
                    'B' => ['text' => 'dijelaskan melalui contoh nyata yang dekat dengan kehidupan', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'dikaitkan dengan peristiwa sosial, ekonomi, atau sejarah', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'dikemas dalam cerita, diskusi, atau presentasi verbal', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Pekerjaan yang paling mudah membuat saya bersemangat adalah pekerjaan yang ...',
                'weights' => [
                    'A' => ['text' => 'menantang logika dan ketelitian saya', 'focus' => 'STEM'],
                    'B' => ['text' => 'bermanfaat langsung bagi kesehatan atau kesejahteraan orang lain', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'melibatkan analisis masyarakat, organisasi, atau keputusan publik', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'melibatkan komunikasi, bahasa, dan kreativitas', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Jika harus memilih kegiatan akhir pekan, saya cenderung memilih ...',
                'weights' => [
                    'A' => ['text' => 'mencoba eksperimen sederhana, puzzle logika, atau belajar aplikasi baru', 'focus' => 'STEM'],
                    'B' => ['text' => 'mengikuti kegiatan sosial, olahraga sehat, atau peduli lingkungan', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'mengamati fenomena masyarakat, usaha kecil, atau diskusi isu terkini', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'membaca, menonton, menulis, atau belajar bahasa asing', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Dalam situasi presentasi, saya biasanya lebih nyaman ketika ...',
                'weights' => [
                    'A' => ['text' => 'menjelaskan data, grafik, atau hasil pengamatan secara runtut', 'focus' => 'STEM'],
                    'B' => ['text' => 'membawakan topik tentang kesehatan, manusia, atau layanan', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'menyampaikan analisis masalah sosial dan kemungkinan solusinya', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'mengolah kata, gaya bicara, dan interaksi dengan audiens', 'focus' => 'LANG'],
                ],
            ],
            [
                'question' => 'Ketika melihat tantangan baru, saya paling terdorong karena ...',
                'weights' => [
                    'A' => ['text' => 'ingin membuktikan solusi yang paling efektif dan akurat', 'focus' => 'STEM'],
                    'B' => ['text' => 'ingin membantu orang lain dengan cara yang tepat', 'focus' => 'HEALTH'],
                    'C' => ['text' => 'ingin memahami pengaruhnya terhadap kelompok atau masyarakat', 'focus' => 'SOSHUM'],
                    'D' => ['text' => 'ingin menyampaikan gagasan saya dengan cara yang menarik', 'focus' => 'LANG'],
                ],
            ],
        ];

        $weightMap = [
            'STEM' => ['STEM' => 10, 'HEALTH' => 6, 'SOSHUM' => 2, 'LANG' => 2],
            'HEALTH' => ['STEM' => 5, 'HEALTH' => 10, 'SOSHUM' => 4, 'LANG' => 2],
            'SOSHUM' => ['STEM' => 2, 'HEALTH' => 4, 'SOSHUM' => 10, 'LANG' => 5],
            'LANG' => ['STEM' => 2, 'HEALTH' => 2, 'SOSHUM' => 5, 'LANG' => 10],
        ];

        foreach ($questions as $index => $questionData) {
            $questionId = DB::table('psychology_questions')->insertGetId($this->timestampedRow([
                'question' => $questionData['question'],
                'image_path' => null,
                'order' => $index + 1,
                'is_active' => true,
            ]));

            foreach ($questionData['weights'] as $label => $optionData) {
                $optionId = DB::table('psychology_question_options')->insertGetId($this->timestampedRow([
                    'psychology_question_id' => $questionId,
                    'label' => $label,
                    'option_text' => $optionData['text'],
                ]));

                foreach ($packages as $code => $packageId) {
                    DB::table('psychology_option_weights')->insert([
                        'psychology_question_option_id' => $optionId,
                        'package_id' => $packageId,
                        'weight' => $weightMap[$optionData['focus']][match ($code) {
                            'A' => 'STEM',
                            'B' => 'HEALTH',
                            'C' => 'SOSHUM',
                            default => 'LANG',
                        }],
                    ]);
                }
            }
        }
    }

    private function seedAnnouncement(int $adminId): void
    {
        DB::table('announcements')->insert($this->timestampedRow([
            'type' => 'temporary',
            'title' => 'Informasi Pengumuman Hasil Peminatan',
            'content' => 'Pengumuman hasil akan dipublikasikan oleh admin setelah proses tes dan distribusi kelas selesai.',
            'is_published' => true,
            'published_at' => now(),
            'published_by' => $adminId,
        ]));
    }

    private function timestampedRow(array $row): array
    {
        return $row + [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function withTimestamps(array $rows): array
    {
        return array_map(fn(array $row) => $this->timestampedRow($row), $rows);
    }
}
