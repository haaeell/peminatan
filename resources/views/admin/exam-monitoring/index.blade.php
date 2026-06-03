@extends('layouts.admin')

@section('title', 'Monitoring Ujian')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">Monitoring Ujian</h1>
                <p class="text-slate-500 mt-2">Pantau siswa yang sedang mengerjakan tes secara real-time berbasis data server.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600">
                    <i class="fa-regular fa-clock text-slate-400"></i>
                    {{ now()->format('d M Y H:i:s') }}
                </div>

                <button type="button" onclick="window.location.reload()"
                    class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-bold shadow-sm transition">
                    <i class="fa-solid fa-rotate"></i>
                    Refresh
                </button>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="grid sm:grid-cols-2 xl:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200">
                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Sedang Ujian</div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $summary['active_students'] }}</div>
                </div>

                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Tes Akademik</div>
                    <div class="text-3xl font-extrabold text-blue-700 mt-1">{{ $summary['academic'] }}</div>
                </div>

                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Tes Psikologi</div>
                    <div class="text-3xl font-extrabold text-emerald-700 mt-1">{{ $summary['psychology'] }}</div>
                </div>

                <div class="px-5 py-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Pelanggaran Tinggi</div>
                    <div class="text-3xl font-extrabold {{ $summary['high_violation'] > 0 ? 'text-red-700' : 'text-slate-900' }} mt-1">
                        {{ $summary['high_violation'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 p-4">
                <form method="GET" class="grid lg:grid-cols-[1fr_auto_auto_auto] gap-3">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="search" name="q" value="{{ $search }}"
                            placeholder="Cari nama, NISN, atau kelas"
                            class="w-full rounded-lg border border-slate-200 pl-10 pr-3 py-2.5 text-sm font-semibold text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>

                    <select name="exam"
                        class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Semua tes</option>
                        <option value="academic" @selected($examFilter === 'academic')>Akademik</option>
                        <option value="psychology" @selected($examFilter === 'psychology')>Psikologi</option>
                    </select>

                    <select name="per_page"
                        class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        @foreach([10, 30, 50, 100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 30) == $size)>{{ $size }} baris</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 hover:bg-slate-800 px-4 py-2.5 text-sm font-extrabold text-white transition">
                        <i class="fa-solid fa-filter"></i>
                        Terapkan
                    </button>
                </form>
            </div>

            @if($students->isEmpty())
                <div class="px-6 py-14 text-center">
                    <div class="w-14 h-14 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-user-clock text-2xl"></i>
                    </div>

                    <h2 class="text-lg font-extrabold text-slate-900">Belum ada siswa yang sedang ujian</h2>
                    <p class="text-slate-500 mt-2">Data akan muncul saat ada sesi yang sedang berjalan.</p>
                </div>
            @else
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-slate-50">
                            <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-3 px-4 w-[30%]">Siswa</th>
                                <th class="py-3 px-4">Tes</th>
                                <th class="py-3 px-4">Sesi</th>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Durasi</th>
                                <th class="py-3 px-4">Submit</th>
                                <th class="py-3 px-4 text-right">Pelanggaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $student)
                                @php
                                    $remainingPercent = $student->duration_minutes > 0
                                        ? max(0, min(100, (int) round(($student->remaining_seconds / ($student->duration_minutes * 60)) * 100)))
                                        : 0;
                                    $isLowTime = $student->remaining_seconds <= 300;
                                    $examBadge = $student->active_exam_key === 'academic'
                                        ? 'bg-blue-50 text-blue-700 border-blue-100'
                                        : 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 align-top">
                                        <div class="font-extrabold text-slate-900 leading-snug">{{ $student->name }}</div>
                                        <div class="text-xs text-slate-500 mt-1">{{ $student->nisn }} - {{ $student->origin_class }}</div>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-extrabold {{ $examBadge }}">
                                            {{ $student->active_exam }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <div class="font-bold text-slate-800">{{ $student->session_name }}</div>
                                        <div class="text-xs text-slate-500 mt-1">{{ \Carbon\Carbon::parse($student->test_date)->format('d M Y') }}</div>
                                    </td>
                                    <td class="py-3 px-4 align-top min-w-40">
                                        <div class="font-extrabold {{ $isLowTime ? 'text-red-700' : 'text-slate-900' }}">
                                            {{ $student->remaining_label }}
                                        </div>
                                        <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full {{ $isLowTime ? 'bg-red-500' : 'bg-blue-500' }}"
                                                style="width: {{ $remainingPercent }}%"></div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 align-top font-semibold text-slate-700">{{ $student->elapsed_label }}</td>
                                    <td class="py-3 px-4 align-top">
                                        <span class="inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-slate-700">
                                            {{ $student->submit_type_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 align-top text-right">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <span class="text-lg font-extrabold {{ $student->violation_count > 0 ? 'text-red-700' : 'text-slate-800' }}">
                                                {{ $student->violation_count }}
                                            </span>
                                            @if($student->last_violation_at)
                                                <span class="text-xs font-semibold text-red-500">
                                                    {{ \Carbon\Carbon::parse($student->last_violation_at)->format('H:i:s') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="lg:hidden divide-y divide-slate-100">
                    @foreach($students as $student)
                        @php
                            $isLowTime = $student->remaining_seconds <= 300;
                            $examBadge = $student->active_exam_key === 'academic'
                                ? 'bg-blue-50 text-blue-700 border-blue-100'
                                : 'bg-emerald-50 text-emerald-700 border-emerald-100';
                        @endphp
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-extrabold text-slate-900 leading-snug">{{ $student->name }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $student->nisn }} - {{ $student->origin_class }}</div>
                                </div>
                                <span class="shrink-0 rounded-md border px-2.5 py-1 text-xs font-extrabold {{ $examBadge }}">
                                    {{ $student->active_exam_key === 'academic' ? 'Akademik' : 'Psikologi' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Sisa</div>
                                    <div class="font-extrabold {{ $isLowTime ? 'text-red-700' : 'text-slate-900' }} mt-1">{{ $student->remaining_label }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Durasi</div>
                                    <div class="font-bold text-slate-800 mt-1">{{ $student->elapsed_label }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Sesi</div>
                                    <div class="font-bold text-slate-800 mt-1">{{ $student->session_name }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Pelanggaran</div>
                                    <div class="font-extrabold {{ $student->violation_count > 0 ? 'text-red-700' : 'text-slate-900' }} mt-1">
                                        {{ $student->violation_count }}
                                        <span class="text-xs font-semibold text-slate-500">
                                            {{ $student->last_violation_at ? \Carbon\Carbon::parse($student->last_violation_at)->format('H:i:s') : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200 px-4 py-3">
                    <div class="text-sm font-semibold text-slate-500">
                        Menampilkan {{ $students->firstItem() }}-{{ $students->lastItem() }} dari {{ $students->total() }} siswa aktif.
                    </div>
                    <div>
                        {{ $students->links() }}
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="text-lg font-extrabold text-slate-900">Riwayat Submit Terakhir</h2>
            </div>

            @if($recentSubmissions->isEmpty())
                <div class="px-4 py-5 text-sm font-semibold text-slate-500">
                    Belum ada siswa yang submit.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-3 px-4">Siswa</th>
                                <th class="py-3 px-4">Tes</th>
                                <th class="py-3 px-4">Durasi</th>
                                <th class="py-3 px-4">Submit</th>
                                <th class="py-3 px-4">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentSubmissions as $submission)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-extrabold text-slate-900">{{ $submission->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $submission->nisn }} - {{ $submission->origin_class }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800">{{ $submission->exam_name }}</div>
                                        <div class="text-xs text-slate-500">{{ $submission->session_name }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">{{ $submission->duration_label }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold {{ $submission->submit_type === 'manual' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $submission->submit_type_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">
                                        {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M Y H:i:s') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
