@extends('layouts.siswa')

@section('content')
    <div class="min-h-screen bg-slate-100 px-4 py-8">
        <div class="max-w-3xl mx-auto">

            <div class="bg-white border border-slate-200 rounded-[34px] p-6 md:p-10 text-center shadow-sm">

                <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-clock text-4xl"></i>
                </div>

                <h1 class="text-3xl font-extrabold text-slate-900 mb-3">
                    Menunggu Jadwal Tes
                </h1>

                <p class="text-slate-500 mb-8">
                    Kelas asal Anda:
                    <span class="font-extrabold text-blue-600">{{ $student->origin_class }}</span>
                </p>

                @if($session)
                    <div class="bg-blue-50 border border-blue-100 rounded-[28px] p-5 md:p-6 text-left mb-6">
                        <div class="flex items-start gap-4 mb-5">
                            <div
                                class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>

                            <div>
                                <p class="text-sm font-bold text-blue-600 uppercase tracking-wide">
                                    Sesi Anda
                                </p>

                                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">
                                    {{ $session->name }}
                                </h2>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="bg-white border border-blue-100 rounded-2xl p-4">
                                <div class="text-sm font-semibold text-slate-500 mb-1">
                                    Tanggal
                                </div>

                                <div class="font-extrabold text-slate-900">
                                    {{ $session->test_date->format('d M Y') }}
                                </div>
                            </div>

                            <div class="bg-white border border-blue-100 rounded-2xl p-4">
                                <div class="text-sm font-semibold text-slate-500 mb-1">
                                    Jam
                                </div>

                                <div class="font-extrabold text-slate-900">
                                    {{ \Illuminate\Support\Str::of($session->start_time)->substr(0, 5) }}
                                    -
                                    {{ \Illuminate\Support\Str::of($session->end_time)->substr(0, 5) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-100 text-blue-700 rounded-[28px] p-5 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-white text-blue-600 flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>

                        <p class="font-bold">
                            Jadwal sesi untuk kelas Anda belum tersedia.
                        </p>

                        <p class="text-sm mt-1 text-blue-600">
                            Silakan cek kembali secara berkala.
                        </p>
                    </div>
                @endif

                <div class="grid sm:grid-cols-2 gap-3">
                    <a href="{{ route('siswa.academic.index') }}"
                        class="inline-flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Coba Masuk Tes
                    </a>

                    <a href="{{ route('siswa.wizard.index') }}"
                        class="inline-flex items-center justify-center gap-2 w-full bg-white hover:bg-blue-50 text-blue-700 border border-blue-100 py-4 rounded-2xl font-extrabold transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Wizard
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection