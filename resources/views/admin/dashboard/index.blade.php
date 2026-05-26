@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Welcome Section --}}
    <div class="mb-8">
        <div class="rounded-[28px] bg-gradient-to-br from-blue-600 to-blue-400 p-6 md:p-8 text-white shadow-xl shadow-blue-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Ringkasan Sistem</p>
                    <h1 class="text-2xl md:text-3xl font-extrabold mt-2">
                        Dashboard Pemilihan Jurusan
                    </h1>
                    <p class="text-blue-100 mt-2 max-w-2xl">
                        Pantau data siswa, progres tes, pelanggaran CBT, dan preferensi jurusan secara cepat.
                    </p>
                </div>

                <div class="bg-white/15 backdrop-blur-xl rounded-3xl px-5 py-4 border border-white/20">
                    <p class="text-xs text-blue-100">Status</p>
                    <p class="font-bold text-lg">Aktif</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistic Cards --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <div class="group bg-white border border-slate-200 rounded-[26px] p-6 shadow-sm hover:shadow-xl hover:shadow-blue-100 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Total Siswa</p>
                    <h2 class="text-4xl font-extrabold text-slate-900 mt-3">
                        {{ $totalStudents }}
                    </h2>
                </div>

                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-2 text-sm text-slate-500">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Data siswa terdaftar
            </div>
        </div>

        <div class="group bg-white border border-slate-200 rounded-[26px] p-6 shadow-sm hover:shadow-xl hover:shadow-blue-100 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Selesai Tes</p>
                    <h2 class="text-4xl font-extrabold text-slate-900 mt-3">
                        {{ $completedStudents }}
                    </h2>
                </div>

                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-2 text-sm text-slate-500">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Siswa sudah menyelesaikan tes
            </div>
        </div>

        <div class="group bg-white border border-slate-200 rounded-[26px] p-6 shadow-sm hover:shadow-xl hover:shadow-blue-100 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Pelanggaran</p>
                    <h2 class="text-4xl font-extrabold text-slate-900 mt-3">
                        {{ $totalViolations }}
                    </h2>
                </div>

                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="fa-solid fa-shield-halved text-xl"></i>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-2 text-sm text-slate-500">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Total pelanggaran CBT
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid xl:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-[28px] p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Akademik</p>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-1">
                        Distribusi Nilai Akademik
                    </h2>
                </div>

                <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-chart-column"></i>
                </div>
            </div>

            <div class="h-[320px]">
                <canvas id="academicChart"></canvas>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-[28px] p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Jurusan</p>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-1">
                        Preferensi Jurusan
                    </h2>
                </div>

                <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>

            <div class="h-[320px] flex items-center justify-center">
                <canvas id="packageChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const bluePalette = [
            '#2563eb',
            '#3b82f6',
            '#60a5fa',
            '#93c5fd',
            '#bfdbfe',
            '#1d4ed8',
            '#1e40af',
            '#0f172a'
        ];

        new Chart(document.getElementById('academicChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($academicDistribution)) !!},
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: {!! json_encode(array_values($academicDistribution)) !!},
                    backgroundColor: '#2563eb',
                    borderRadius: 14,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#475569',
                            font: {
                                family: 'Inter',
                                weight: '600'
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e2e8f0'
                        },
                        ticks: {
                            color: '#64748b',
                            precision: 0,
                            font: {
                                family: 'Inter'
                            }
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('packageChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($packagePreferences->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($packagePreferences->map(fn($p) => $p->first_choices_count + $p->second_choices_count)) !!},
                    backgroundColor: bluePalette,
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#475569',
                            padding: 18,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                family: 'Inter',
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush