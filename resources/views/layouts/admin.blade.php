<!DOCTYPE html>
<html lang="id">

<head>
    @php
        $appName = \App\Models\Setting::getSetting('app_name', 'Sistem Pemilihan Jurusan');
        $schoolName = \App\Models\Setting::getSetting('school_name', 'Pemilihan Jurusan');
        $logoUrl = \App\Models\Setting::logoUrl();

        $pendingObjectionsCount = \App\Models\Objection::where('status', 'pending')->count();
        $activeSessionsCount = \App\Models\TestSession::where('is_active', true)->count();
        $todaySessionsCount = \App\Models\TestSession::where('is_active', true)
            ->whereDate('test_date', now()->toDateString())
            ->count();
        $unpublishedAnnouncementsCount = \App\Models\Announcement::where('is_published', false)->count();
        $latestAnnouncement = \App\Models\Announcement::latest()->first();
        $studentsInExamCount = \Illuminate\Support\Facades\DB::table('student_test_sessions')
            ->where('status', 'in_progress')
            ->count();

        $notificationCount = $pendingObjectionsCount
            + $todaySessionsCount
            + $unpublishedAnnouncementsCount
            + $studentsInExamCount;

        $adminSearchItems = [
            ['title' => 'Dashboard', 'description' => 'Ringkasan aktivitas dan statistik utama', 'icon' => 'fa-chart-line', 'url' => route('admin.dashboard'), 'keywords' => 'home statistik ringkasan'],
            ['title' => 'Siswa', 'description' => 'Tambah, import, edit, hapus, dan aktivasi akun siswa', 'icon' => 'fa-users', 'url' => route('admin.students.index'), 'keywords' => 'murid peserta import akun kelas'],
            ['title' => 'Jurusan', 'description' => 'Kelola paket jurusan dan mapel pendukung', 'icon' => 'fa-layer-group', 'url' => route('admin.packages.index'), 'keywords' => 'paket peminatan mapel pilihan'],
            ['title' => 'Sesi Tes', 'description' => 'Atur jadwal, tipe tes, dan kelas peserta', 'icon' => 'fa-clock', 'url' => route('admin.test-sessions.index'), 'keywords' => 'jadwal ujian kelas waktu sesi'],
            ['title' => 'Soal Akademik', 'description' => 'Kelola soal, opsi jawaban, import, dan gambar soal', 'icon' => 'fa-book-open', 'url' => route('admin.academic-questions.index'), 'keywords' => 'akademik pertanyaan kunci jawaban'],
            ['title' => 'Soal Psikologi', 'description' => 'Kelola pernyataan psikotes dan bobot jurusan', 'icon' => 'fa-brain', 'url' => route('admin.psychology-questions.index'), 'keywords' => 'psikotes psikologi bobot'],
            ['title' => 'Monitoring Ujian', 'description' => 'Pantau siswa yang sedang mengerjakan tes', 'icon' => 'fa-desktop', 'url' => route('admin.exam-monitoring.index'), 'keywords' => 'monitor ujian aktif real time'],
            ['title' => 'Pelanggaran CBT', 'description' => 'Lihat catatan pelanggaran selama ujian', 'icon' => 'fa-shield-halved', 'url' => route('admin.violations.index'), 'keywords' => 'violation pelanggaran fullscreen tab'],
            ['title' => 'Hasil Tes', 'description' => 'Nilai, rekomendasi, biodata, selfie, dan penempatan', 'icon' => 'fa-square-poll-vertical', 'url' => route('admin.test-results.index'), 'keywords' => 'nilai hasil rekomendasi final'],
            ['title' => 'Distribusi Kelas', 'description' => 'Auto distribusi dan pindah siswa antar kelas', 'icon' => 'fa-random', 'url' => route('admin.class-distribution.index'), 'keywords' => 'kelas hasil pembagian final'],
            ['title' => 'Laporan', 'description' => 'Export laporan siswa, hasil tes, kelas, dan respons', 'icon' => 'fa-file-arrow-down', 'url' => route('admin.reports.index'), 'keywords' => 'report excel pdf export'],
            ['title' => 'Pengumuman', 'description' => 'Buat dan publish pengumuman sementara atau final', 'icon' => 'fa-bullhorn', 'url' => route('admin.announcements.index'), 'keywords' => 'announcement final temporary publish'],
            ['title' => 'Keberatan', 'description' => 'Review alasan keberatan siswa dan beri catatan admin', 'icon' => 'fa-message', 'url' => route('admin.objections.index'), 'keywords' => 'tolak setuju objection respons'],
            ['title' => 'Audit Log', 'description' => 'Riwayat aktivitas admin pada sistem', 'icon' => 'fa-clock-rotate-left', 'url' => route('admin.activity-logs.index'), 'keywords' => 'log aktivitas riwayat'],
            ['title' => 'Settings', 'description' => 'Pengaturan aplikasi, sekolah, timer, dan CBT', 'icon' => 'fa-gear', 'url' => route('admin.settings.index'), 'keywords' => 'setting konfigurasi timer logo'],
        ];
    @endphp
    <meta charset="UTF-8">
    <title>Admin - {{ $appName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ $logoUrl }}">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 16px;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            transition: all .25s ease;
        }

        .sidebar-link:hover {
            background: #eff6ff;
            color: #2563eb;
            transform: translateX(3px);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            box-shadow: 0 12px 24px rgba(37, 99, 235, .25);
        }

        .sidebar-link i {
            width: 20px;
            text-align: center;
        }

        .menu-title {
            margin: 22px 16px 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            color: #94a3b8;
            text-transform: uppercase;
        }

        table.dataTable {
            border-radius: 18px;
            overflow: hidden;
        }

        .dt-container .dt-search input,
        .dt-container .dt-length select {
            border: 1px solid #dbeafe !important;
            border-radius: 12px !important;
            padding: 8px 12px !important;
            outline: none !important;
        }

        .dt-container .dt-search input:focus,
        .dt-container .dt-length select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px #dbeafe !important;
        }

        .dt-container .dt-paging .dt-paging-button.current {
            background: #2563eb !important;
            color: white !important;
            border-radius: 10px !important;
            border: none !important;
        }

        #testResultsTable_wrapper {
            width: 100%;
        }

        #testResultsTable_wrapper .dt-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        #testResultsTable_wrapper .dt-length,
        #testResultsTable_wrapper .dt-search {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #testResultsTable_wrapper .dt-length label,
        #testResultsTable_wrapper .dt-search label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
        }

        #testResultsTable_wrapper .dt-length select {
            width: 82px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0 12px;
            outline: none;
        }

        #testResultsTable_wrapper .dt-search input {
            width: 320px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0 14px;
            outline: none;
            font-size: 14px;
        }

        #testResultsTable_wrapper .dt-search input:focus,
        #testResultsTable_wrapper .dt-length select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px #dbeafe;
            background: #ffffff;
        }

        #testResultsTable {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 10px !important;
        }

        #testResultsTable thead th {
            background: #f8fafc !important;
            color: #64748b !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            padding: 14px 16px !important;
            border: none !important;
        }

        #testResultsTable tbody tr {
            background: #ffffff !important;
            box-shadow: 0 1px 0 #e2e8f0;
        }

        #testResultsTable tbody td {
            padding: 16px !important;
            border: none !important;
            vertical-align: middle !important;
        }

        #testResultsTable tbody tr:hover {
            background: #f8fafc !important;
        }

        #testResultsTable tbody tr td:first-child {
            border-radius: 16px 0 0 16px;
        }

        #testResultsTable tbody tr td:last-child {
            border-radius: 0 16px 16px 0;
        }

        #testResultsTable_wrapper .dt-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 18px;
        }

        #testResultsTable_wrapper .dt-info {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        #testResultsTable_wrapper .dt-paging {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #testResultsTable_wrapper .dt-paging .dt-paging-button {
            min-width: 36px;
            height: 36px;
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            color: #475569 !important;
            font-weight: 700;
        }

        #testResultsTable_wrapper .dt-paging .dt-paging-button.current {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }

        #testResultsTable_wrapper .dt-paging .dt-paging-button:hover {
            background: #eff6ff !important;
            color: #2563eb !important;
            border-color: #bfdbfe !important;
        }

        @media (max-width: 768px) {

            #testResultsTable_wrapper .dt-top,
            #testResultsTable_wrapper .dt-bottom {
                flex-direction: column;
                align-items: stretch;
            }

            #testResultsTable_wrapper .dt-search input {
                width: 100%;
            }
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="hidden md:flex md:w-72 bg-white border-r border-slate-200 flex-col fixed inset-y-0 left-0 z-30">

            {{-- Brand --}}
            <div class="h-20 px-6 flex items-center border-b border-slate-100">
                <div
                    class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-400 flex items-center justify-center text-white shadow-lg shadow-blue-200 overflow-hidden p-2">
                    <img src="{{ $logoUrl }}" alt="Logo {{ $schoolName }}" class="w-full h-full object-contain">
                </div>

                <div class="ml-3">
                    <h1 class="font-extrabold text-slate-900 leading-tight">Admin Panel</h1>
                    <p class="text-xs text-slate-400 font-medium">{{ $schoolName }}</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-4 py-5">

                <a href="{{ route('admin.dashboard') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>

                <div class="menu-title">Master Data</div>

                <a href="{{ route('admin.students.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Siswa</span>
                </a>

                <a href="{{ route('admin.packages.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Jurusan</span>
                </a>

                <div class="menu-title">Tes CBT</div>

                <a href="{{ route('admin.test-sessions.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.test-sessions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock"></i>
                    <span>Sesi Tes</span>
                </a>

                <a href="{{ route('admin.academic-questions.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.academic-questions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Soal Akademik</span>
                </a>

                <a href="{{ route('admin.psychology-questions.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.psychology-questions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-brain"></i>
                    <span>Soal Psikologi</span>
                </a>

                <a href="{{ route('admin.violations.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.violations.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Pelanggaran CBT</span>
                </a>

                <a href="{{ route('admin.exam-monitoring.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.exam-monitoring.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-desktop"></i>
                    <span>Monitoring Ujian</span>
                </a>

                <div class="menu-title">Laporan</div>

                <a href="{{ route('admin.reports.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-arrow-down"></i>
                    <span>Laporan</span>
                </a>

                <a href="{{ route('admin.test-results.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.test-results.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span>Hasil Tes</span>
                </a>

                <a href="{{ route('admin.activity-logs.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Audit Log</span>
                </a>

                <a href="{{ route('admin.class-distribution.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.class-distribution.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-random"></i>
                    <span>Distribusi Kelas</span>
                </a>

                <div class="menu-title">Komunikasi</div>

                <a href="{{ route('admin.announcements.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Pengumuman</span>
                </a>

                <a href="{{ route('admin.objections.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.objections.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-message"></i>
                    <span>Keberatan</span>
                </a>

                <div class="menu-title">Sistem</div>

                <a href="{{ route('admin.settings.index') }}"
                    class=" sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </nav>

            {{-- Admin Mini Profile --}}
            <div class="p-4 border-t border-slate-100">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-blue-50">
                    <div
                        class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                        A
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900 truncate">Administrator</p>
                        <p class="text-xs text-slate-500 truncate">Panel Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <main class="flex-1 md:ml-72">

            {{-- Header --}}
            <header
                class="sticky top-0 z-20 h-20 bg-white/90 backdrop-blur-xl border-b border-slate-200 px-6 md:px-8 flex items-center justify-between">

                <div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Admin Area</p>
                    <h2 class="text-xl md:text-2xl font-extrabold text-slate-900">
                        @yield('title', 'Dashboard')
                    </h2>
                </div>

                <div class="flex items-center gap-4">

                    {{-- Search --}}
                    <div class="hidden lg:block relative w-96" id="adminSearch">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="adminSearchInput" type="text" placeholder="Cari menu atau data..."
                            autocomplete="off"
                            class="w-full pl-11 pr-24 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 hidden xl:inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold text-slate-400">
                            Ctrl K
                        </div>

                        <div id="adminSearchPanel"
                            class="hidden absolute right-0 top-full mt-3 w-[28rem] rounded-[24px] border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">Pencarian Cepat</p>
                                <p class="text-sm text-slate-500 mt-1">Ketik nama menu, modul, atau kata kunci operasional.</p>
                            </div>

                            <div id="adminSearchResults" class="max-h-[420px] overflow-y-auto p-2"></div>
                        </div>
                    </div>

                    {{-- Notification --}}
                    <div class="relative" id="adminNotifications">
                        <button id="notificationToggle" type="button"
                            class="relative w-11 h-11 rounded-2xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition">
                            <i class="fa-regular fa-bell"></i>
                            @if($notificationCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-blue-600 text-white text-[10px] font-extrabold flex items-center justify-center">
                                    {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                                </span>
                            @endif
                        </button>

                        <div id="notificationPanel"
                            class="hidden absolute right-0 top-full mt-3 w-[24rem] rounded-[24px] border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 overflow-hidden z-50">
                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-extrabold text-slate-900">Notifikasi</p>
                                    <p class="text-xs text-slate-500 mt-1">Ringkasan yang perlu cepat dicek admin.</p>
                                </div>
                                <span class="inline-flex items-center justify-center min-w-8 h-8 rounded-xl bg-blue-50 text-blue-700 text-xs font-extrabold">
                                    {{ $notificationCount }}
                                </span>
                            </div>

                            <div class="p-2 max-h-[420px] overflow-y-auto">
                                <a href="{{ route('admin.objections.index') }}"
                                    class="flex items-start gap-3 rounded-2xl px-3 py-3 hover:bg-blue-50 transition">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-message"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="font-bold text-slate-900">Keberatan pending</p>
                                            <span class="text-xs font-extrabold text-blue-700">{{ $pendingObjectionsCount }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">Review alasan siswa dan beri keputusan admin.</p>
                                    </div>
                                </a>

                                <a href="{{ route('admin.exam-monitoring.index') }}"
                                    class="flex items-start gap-3 rounded-2xl px-3 py-3 hover:bg-blue-50 transition">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-desktop"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="font-bold text-slate-900">Siswa sedang ujian</p>
                                            <span class="text-xs font-extrabold text-blue-700">{{ $studentsInExamCount }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">Pantau aktivitas ujian yang sedang berlangsung.</p>
                                    </div>
                                </a>

                                <a href="{{ route('admin.test-sessions.index') }}"
                                    class="flex items-start gap-3 rounded-2xl px-3 py-3 hover:bg-blue-50 transition">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-clock"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="font-bold text-slate-900">Sesi aktif hari ini</p>
                                            <span class="text-xs font-extrabold text-blue-700">{{ $todaySessionsCount }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">{{ $activeSessionsCount }} sesi aktif tersimpan di sistem.</p>
                                    </div>
                                </a>

                                <a href="{{ route('admin.announcements.index') }}"
                                    class="flex items-start gap-3 rounded-2xl px-3 py-3 hover:bg-blue-50 transition">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="font-bold text-slate-900">Pengumuman draft</p>
                                            <span class="text-xs font-extrabold text-blue-700">{{ $unpublishedAnnouncementsCount }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $latestAnnouncement ? 'Terakhir: ' . $latestAnnouncement->title : 'Belum ada pengumuman.' }}
                                        </p>
                                    </div>
                                </a>

                                @if($notificationCount === 0)
                                    <div class="px-4 py-8 text-center">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                            <i class="fa-regular fa-circle-check"></i>
                                        </div>
                                        <p class="font-bold text-slate-900">Semua aman</p>
                                        <p class="text-xs text-slate-500 mt-1">Belum ada item yang perlu perhatian cepat.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-lg shadow-blue-200 transition">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Content --}}
            <section class="p-6 md:p-8">
                <div class="bg-white rounded-[28px] border border-slate-200 shadow-sm p-5 md:p-6">
                    @yield('content')
                </div>
            </section>
        </main>
    </div>

    <script>
        $(function () {
            const adminSearchItems = @json($adminSearchItems);
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3200,
                timerProgressBar: true,
                background: '#ffffff',
                color: '#0f172a',
                customClass: {
                    popup: 'rounded-3xl shadow-xl border border-slate-200'
                },
                didOpen: (popup) => {
                    popup.addEventListener('mouseenter', Swal.stopTimer);
                    popup.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            $('.datatable').DataTable({
                responsive: true,
                pageLength: 10,
            });

            window.confirmDelete = function (formId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus data?',
                    text: 'Data yang dihapus tidak bisa dikembalikan secara langsung.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b',
                    background: '#ffffff',
                    color: '#0f172a',
                    customClass: {
                        popup: 'rounded-3xl'
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        $('#' + formId).submit();
                    }
                });
            };

            const searchInput = $('#adminSearchInput');
            const searchPanel = $('#adminSearchPanel');
            const searchResults = $('#adminSearchResults');
            const notificationPanel = $('#notificationPanel');

            function normalizeSearchText(value) {
                return String(value || '').toLowerCase().trim();
            }

            function renderSearchResults(query = '') {
                const normalizedQuery = normalizeSearchText(query);
                const results = adminSearchItems
                    .filter((item) => {
                        if (!normalizedQuery) {
                            return true;
                        }

                        return normalizeSearchText(`${item.title} ${item.description} ${item.keywords}`)
                            .includes(normalizedQuery);
                    })
                    .slice(0, 8);

                if (results.length === 0) {
                    searchResults.html(`
                        <div class="px-4 py-8 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <p class="font-bold text-slate-900">Tidak ada hasil</p>
                            <p class="text-xs text-slate-500 mt-1">Coba kata kunci lain, misalnya siswa, jadwal, hasil, atau pengumuman.</p>
                        </div>
                    `);
                    return;
                }

                searchResults.html(results.map((item, index) => `
                    <a href="${item.url}"
                        class="admin-search-result flex items-start gap-3 rounded-2xl px-3 py-3 hover:bg-blue-50 transition ${index === 0 ? 'bg-blue-50' : ''}"
                        data-url="${item.url}">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid ${item.icon}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900">${item.title}</p>
                            <p class="text-xs text-slate-500 mt-1">${item.description}</p>
                        </div>
                    </a>
                `).join(''));
            }

            function openSearchPanel() {
                renderSearchResults(searchInput.val());
                searchPanel.removeClass('hidden');
                notificationPanel.addClass('hidden');
            }

            function closeSearchPanel() {
                searchPanel.addClass('hidden');
            }

            searchInput.on('focus input', function () {
                openSearchPanel();
            });

            searchInput.on('keydown', function (event) {
                if (event.key === 'Enter') {
                    const firstResult = searchResults.find('.admin-search-result').first();

                    if (firstResult.length) {
                        window.location.href = firstResult.data('url');
                    }
                }

                if (event.key === 'Escape') {
                    closeSearchPanel();
                    searchInput.blur();
                }
            });

            $('#notificationToggle').on('click', function (event) {
                event.stopPropagation();
                notificationPanel.toggleClass('hidden');
                closeSearchPanel();
            });

            $(document).on('keydown', function (event) {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    searchInput.trigger('focus');
                }

                if (event.key === 'Escape') {
                    closeSearchPanel();
                    notificationPanel.addClass('hidden');
                }
            });

            $(document).on('click', function (event) {
                if (!$(event.target).closest('#adminSearch').length) {
                    closeSearchPanel();
                }

                if (!$(event.target).closest('#adminNotifications').length) {
                    notificationPanel.addClass('hidden');
                }
            });

            @if(session('success'))
                toast.fire({
                    icon: 'success',
                    title: @json(session('success'))
                });
            @endif

            @if(session('error'))
                toast.fire({
                    icon: 'error',
                    title: @json(session('error'))
                });
            @endif

            @if(session('warning'))
                toast.fire({
                    icon: 'warning',
                    title: @json(session('warning'))
                });
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
