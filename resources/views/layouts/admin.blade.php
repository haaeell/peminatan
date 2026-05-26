<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Admin - Sistem Pemilihan Jurusan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
    </style>
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="hidden md:flex md:w-72 bg-white border-r border-slate-200 flex-col fixed inset-y-0 left-0 z-30">

            {{-- Brand --}}
            <div class="h-20 px-6 flex items-center border-b border-slate-100">
                <div
                    class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-400 flex items-center justify-center text-white shadow-lg shadow-blue-200">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                </div>

                <div class="ml-3">
                    <h1 class="font-extrabold text-slate-900 leading-tight">Admin Panel</h1>
                    <p class="text-xs text-slate-400 font-medium">Pemilihan Jurusan</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-4 py-5">

                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>

                <div class="menu-title">Master Data</div>

                <a href="{{ route('admin.students.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Siswa</span>
                </a>

                <a href="{{ route('admin.packages.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Jurusan</span>
                </a>

                <div class="menu-title">Tes CBT</div>

                <a href="{{ route('admin.test-sessions.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.test-sessions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock"></i>
                    <span>Sesi Tes</span>
                </a>

                <a href="{{ route('admin.academic-questions.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.academic-questions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Soal Akademik</span>
                </a>

                <a href="{{ route('admin.psychology-questions.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.psychology-questions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-brain"></i>
                    <span>Soal Psikologi</span>
                </a>

                <a href="{{ route('admin.violations.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.violations.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Pelanggaran CBT</span>
                </a>

                <div class="menu-title">Laporan</div>

                <a href="{{ route('admin.test-results.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.test-results.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span>Hasil Tes</span>
                </a>

                <a href="{{ route('admin.activity-logs.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Audit Log</span>
                </a>

                <a href="{{ route('admin.class-distribution.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.class-distribution.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-random"></i>
                    <span>Distribusi Kelas</span>
                </a>

                <div class="menu-title">Komunikasi</div>

                <a href="{{ route('admin.announcements.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Pengumuman</span>
                </a>

                <a href="{{ route('admin.objections.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.objections.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-message"></i>
                    <span>Keberatan</span>
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
                    <div class="hidden lg:block relative w-80">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Cari menu atau data..."
                            class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    {{-- Notification --}}
                    <button
                        class="w-11 h-11 rounded-2xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition">
                        <i class="fa-regular fa-bell"></i>
                    </button>

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

                @if(session('success'))
                    <div
                        class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 shadow-sm">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <p class="font-bold">Berhasil</p>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-[28px] border border-slate-200 shadow-sm p-5 md:p-6">
                    @yield('content')
                </div>

            </section>
        </main>
    </div>

    <script>
        $(function () {
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
            }
        });
    </script>

    @stack('scripts')
</body>

</html>