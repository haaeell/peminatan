@extends('layouts.admin')

@section('title', 'Hasil Tes Lengkap')

@section('content')

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Hasil Tes Lengkap</h1>
            <p class="text-slate-500 mt-2">
                Pantau hasil akademik, psikotes, pilihan jurusan, selfie, dan distribusi kelas siswa.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <form id="distributeForm" method="POST" action="{{ route('admin.test-results.distribute') }}">
                @csrf

                <button type="button"
                    onclick="confirmDelete('distributeForm')"
                    class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition">
                    <i class="fa-solid fa-random"></i>
                    Bagi Otomatis
                </button>
            </form>

            <a href="{{ route('admin.test-results.export') }}"
                class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-5 py-3 rounded-2xl font-extrabold transition">
                <i class="fa-solid fa-download"></i>
                Export
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm overflow-x-auto">
        <table class="datatable w-full text-sm align-top">
            <thead>
                <tr class="text-slate-600">
                    <th>Siswa</th>
                    <th>Biodata</th>
                    <th>Selfie</th>
                    <th>Pilihan Awal</th>
                    <th>Akademik</th>
                    <th>Rekomendasi</th>
                    <th>Final</th>
                    <th>Kelas</th>
                    <th>Manual Edit</th>
                </tr>
            </thead>

            <tbody>
                @foreach($results as $result)
                    @php
                        $student = $result->student;
                        $biodata = $student?->biodata;
                        $selfie = $student?->selfie;
                        $classStudent = $student?->classStudent;
                    @endphp

                    <tr class="align-top">
                        {{-- Siswa --}}
                        <td>
                            <div class="min-w-48">
                                <div class="flex items-start gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-extrabold">
                                        {{ strtoupper(substr($student?->name ?? 'S', 0, 1)) }}
                                    </div>

                                    <div>
                                        <div class="font-extrabold text-slate-900">
                                            {{ $student?->name ?? '-' }}
                                        </div>

                                        <div class="text-xs text-slate-500 mt-1">
                                            NISN: {{ $student?->nisn ?? '-' }}
                                        </div>

                                        <div class="text-xs text-slate-500">
                                            Kelas asal: {{ $student?->origin_class ?? '-' }}
                                        </div>

                                        <div class="mt-2 inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                            {{ $student?->status ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Biodata --}}
                        <td>
                            <div class="min-w-64">
                                @if($biodata)
                                    <div class="space-y-1 text-slate-700">
                                        <div>
                                            <span class="font-bold text-slate-900">TTL:</span>
                                            {{ $biodata->birth_place }},
                                            {{ $biodata->birth_date?->format('d-m-Y') }}
                                        </div>

                                        <div>
                                            <span class="font-bold text-slate-900">JK:</span>
                                            {{ $biodata->gender }}
                                        </div>

                                        <div>
                                            <span class="font-bold text-slate-900">HP:</span>
                                            {{ $biodata->phone ?? '-' }}
                                        </div>

                                        <div>
                                            <span class="font-bold text-slate-900">Ortu:</span>
                                            {{ $biodata->father_name }} / {{ $biodata->mother_name }}
                                        </div>

                                        <div>
                                            <span class="font-bold text-slate-900">HP Ortu:</span>
                                            {{ $biodata->parent_phone }}
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex px-3 py-1.5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                                        Belum isi biodata
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Selfie --}}
                        <td>
                            <div class="min-w-32">
                                @if($selfie)
                                    <a href="{{ asset('storage/' . $selfie->path) }}" target="_blank"
                                        class="block group">
                                        <img src="{{ asset('storage/' . $selfie->path) }}"
                                            class="w-20 h-20 rounded-2xl object-cover border border-slate-200 shadow-sm group-hover:scale-105 transition">

                                        <div class="text-xs text-slate-500 mt-2">
                                            {{ $selfie->captured_at?->format('d M Y H:i') }}
                                        </div>
                                    </a>
                                @else
                                    <span class="inline-flex px-3 py-1.5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                                        Belum selfie
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Pilihan Awal --}}
                        <td>
                            <div class="min-w-52 space-y-2">
                                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-3">
                                    <div class="text-xs text-blue-600 font-bold">Pilihan 1</div>
                                    <div class="font-bold text-slate-900">
                                        {{ $student?->packageChoice?->firstPackage?->name ?? '-' }}
                                    </div>
                                </div>

                                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3">
                                    <div class="text-xs text-slate-500 font-bold">Pilihan 2</div>
                                    <div class="font-bold text-slate-900">
                                        {{ $student?->packageChoice?->secondPackage?->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Akademik --}}
                        <td>
                            <div class="min-w-28">
                                <div class="text-3xl font-extrabold text-blue-600">
                                    {{ $result->academic_score ?? 0 }}
                                </div>

                                <div class="text-xs text-slate-500 font-semibold mt-1">
                                    Skor Akademik
                                </div>
                            </div>
                        </td>

                        {{-- Rekomendasi --}}
                        <td>
                            <div class="min-w-56">
                                <div class="font-extrabold text-blue-700">
                                    {{ $result->recommendedPackage?->name ?? '-' }}
                                </div>

                                @if($result->psychology_scores)
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        @foreach($result->psychology_scores as $packageId => $score)
                                            @php $package = $packages->firstWhere('id', $packageId); @endphp

                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold">
                                                {{ $package?->code ?? $packageId }}:
                                                <span class="text-slate-900">{{ $score }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Final --}}
                        <td>
                            <div class="min-w-44">
                                <span class="inline-flex px-3 py-2 rounded-2xl bg-blue-600 text-white text-sm font-extrabold shadow-lg shadow-blue-100">
                                    {{ $result->finalPackage?->name ?? '-' }}
                                </span>
                            </div>
                        </td>

                        {{-- Kelas --}}
                        <td>
                            <div class="min-w-40">
                                @if($classStudent)
                                    <div class="font-extrabold text-slate-900">
                                        {{ $classStudent->classGroup?->name }}
                                    </div>

                                    <div class="mt-2 inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                        {{ $classStudent->is_manual_override ? 'Manual' : 'Otomatis' }}
                                    </div>
                                @else
                                    <span class="inline-flex px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                        Belum dibagi
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Manual Edit --}}
                        <td>
                            <form method="POST"
                                action="{{ route('admin.test-results.manual-update') }}"
                                class="space-y-3 min-w-64 bg-slate-50 border border-slate-200 rounded-2xl p-3">
                                @csrf

                                <input type="hidden" name="student_id" value="{{ $student?->id }}">

                                <select name="final_package_id"
                                    class="w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">
                                    @foreach($packages as $package)
                                        <option value="{{ $package->id }}"
                                            {{ $result->final_package_id == $package->id ? 'selected' : '' }}>
                                            {{ $package->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="class_group_id"
                                    class="w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">
                                    @foreach($classGroups as $group)
                                        <option value="{{ $group->id }}"
                                            {{ $classStudent?->class_group_id == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} - {{ $group->package->code }}
                                            ({{ $group->students()->count() }}/{{ $group->capacity }})
                                        </option>
                                    @endforeach
                                </select>

                                <button
                                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-extrabold shadow-lg shadow-blue-100 transition">
                                    <i class="fa-solid fa-save"></i>
                                    Simpan Edit
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6">
            {{ $results->links() }}
        </div>
    </div>

@endsection