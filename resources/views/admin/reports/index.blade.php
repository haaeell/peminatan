@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Laporan Operasional</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Unduh laporan penting dalam format PDF atau Excel untuk kebutuhan admin, rapat, dan arsip.
                </p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Total Siswa</div>
                <div class="text-4xl font-extrabold text-blue-700 mt-2">{{ $summary['students'] }}</div>
            </div>
            <div class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Hasil Tes</div>
                <div class="text-4xl font-extrabold text-blue-700 mt-2">{{ $summary['results'] }}</div>
            </div>
            <div class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Sudah Dibagi Kelas</div>
                <div class="text-4xl font-extrabold text-blue-700 mt-2">{{ $summary['distributed'] }}</div>
            </div>
            <div class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Target Respons</div>
                <div class="text-4xl font-extrabold text-blue-700 mt-2">{{ $summary['response_target'] }}</div>
            </div>
            <div class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Sudah Respons</div>
                <div class="text-4xl font-extrabold text-blue-700 mt-2">{{ $summary['responses'] }}</div>
            </div>
            <div class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Belum Respons</div>
                <div class="text-4xl font-extrabold text-red-600 mt-2">{{ $summary['not_responses'] }}</div>
            </div>
        </div>

        <div class="grid xl:grid-cols-2 gap-5">
            @foreach($reports as $report)
                <div class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-extrabold text-slate-900">{{ $report['title'] }}</h2>
                            <p class="text-sm text-slate-500 mt-2 leading-relaxed">{{ $report['subtitle'] }}</p>
                        </div>

                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                        <div class="font-bold text-slate-800 mb-2">Isi laporan:</div>
                        @foreach($report['summary_lines'] as $line)
                            <div>{{ $line }}</div>
                        @endforeach

                        @if(!empty($report['not_responded_students']))
                            <div class="mt-4 border-t border-slate-200 pt-3">
                                <div class="font-bold text-slate-800 mb-2">Siswa belum respons:</div>
                                <div class="grid gap-1 max-h-40 overflow-y-auto pr-2">
                                    @foreach($report['not_responded_students'] as $student)
                                        <div>{{ $student }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3 mt-5">
                        <a href="{{ route('admin.reports.pdf', $report['type']) }}"
                            class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                            <i class="fa-solid fa-file-pdf"></i>
                            Download PDF
                        </a>

                        <a href="{{ route('admin.reports.excel', $report['type']) }}"
                            class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 py-3 rounded-2xl font-bold transition">
                            <i class="fa-solid fa-file-excel"></i>
                            Download Excel
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
