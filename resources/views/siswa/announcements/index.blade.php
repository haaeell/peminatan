@extends('layouts.siswa')

@section('content')
    <div class="min-h-screen bg-slate-100 px-4 py-8">
        <div class="max-w-2xl mx-auto">

            <div class="bg-white border border-slate-200 rounded-[34px] p-6 md:p-8 shadow-sm">

                <div class="flex items-start gap-4 mb-8">
                    <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-bullhorn text-2xl"></i>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-blue-600 uppercase tracking-wide">
                            Informasi Siswa
                        </p>

                        <h1 class="text-3xl font-extrabold text-slate-900 mt-1">
                            Pengumuman Hasil
                        </h1>

                        <p class="text-slate-500 mt-2">
                            Lihat hasil penempatan jurusan dan kelas Anda, lalu baca informasi lanjutannya dengan teliti.
                        </p>
                    </div>
                </div>

                @if(!$announcement)
                    <div class="text-center py-10">
                        <div
                            class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-5">
                            <i class="fa-solid fa-clock text-4xl"></i>
                        </div>

                        <h2 class="text-2xl font-extrabold text-slate-900">
                            Belum Ada Pengumuman
                        </h2>

                        <p class="text-slate-500 mt-3">
                            Pengumuman hasil belum dipublikasikan. Silakan cek kembali secara berkala melalui akun ini karena hasil, respons, dan surat keterangan akan ditampilkan di sini setelah dirilis oleh admin sekolah.
                        </p>

                        <div class="mt-6 rounded-[28px] border border-blue-100 bg-blue-50 p-5 text-left text-sm text-blue-700">
                            <p class="font-bold mb-2">Sambil menunggu pengumuman:</p>
                            <div>1. Pastikan Anda tetap bisa login menggunakan akun yang sama.</div>
                            <div>2. Silakan cek halaman ini secara berkala untuk informasi terbaru.</div>
                            <div>3. Jika ada kendala akses atau data belum sesuai, hubungi admin sekolah.</div>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-100 rounded-[28px] p-5 md:p-6 mb-6">
                        <p class="text-sm font-bold text-blue-600 uppercase tracking-wide">
                            {{ strtoupper($announcement->type) }}
                        </p>

                        <h2 class="text-2xl font-extrabold text-slate-900 mt-2">
                            {{ $announcement->title }}
                        </h2>

                        <p class="text-slate-600 mt-3 leading-relaxed">
                            {{ $announcement->content }}
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-[28px] p-5 md:p-6 mb-6">
                        <h3 class="text-lg font-extrabold text-slate-900">Petunjuk Setelah Pengumuman</h3>
                        <div class="mt-3 space-y-2 text-sm text-slate-600 leading-relaxed">
                            <div>1. Baca hasil penempatan Anda dengan teliti hingga selesai.</div>
                            <div>2. Jika setuju, gunakan tombol penerimaan agar status Anda tercatat di sistem.</div>
                            <div>3. Jika belum setuju, tuliskan alasan keberatan secara jelas dan lengkap agar admin mudah menindaklanjuti.</div>
                            <div>4. Tetap cek halaman ini secara berkala untuk pembaruan status, keputusan keberatan, atau surat keterangan final.</div>
                        </div>
                    </div>

                    @if($classStudent)
                        <div class="grid sm:grid-cols-2 gap-4 mb-6">
                            <div class="bg-white border border-slate-200 rounded-[26px] p-5 shadow-sm">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>

                                <p class="text-sm font-semibold text-slate-500">Jurusan</p>
                                <h3 class="text-xl font-extrabold text-slate-900 mt-1">
                                    {{ $classStudent->package->name }}
                                </h3>
                            </div>

                            <div class="bg-white border border-slate-200 rounded-[26px] p-5 shadow-sm">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-users"></i>
                                </div>

                                <p class="text-sm font-semibold text-slate-500">Kelas</p>
                                <h3 class="text-xl font-extrabold text-slate-900 mt-1">
                                    {{ $classStudent->classGroup->name }}
                                </h3>
                            </div>
                        </div>
                    @else
                        <div class="bg-blue-50 border border-blue-100 text-blue-700 rounded-[28px] p-5 mb-6">
                            <p class="font-bold">Anda belum mendapatkan kelas.</p>
                            <p class="text-sm mt-1">Silakan tunggu proses distribusi dari admin.</p>
                        </div>
                    @endif

                    @if(!$response && $announcement->type === 'temporary')
                        <form method="POST" action="{{ route('siswa.announcements.accept', $announcement) }}" class="mb-4">
                            @csrf

                            <button
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition">
                                <i class="fa-solid fa-check"></i>
                                Saya Terima
                            </button>
                        </form>

                        <form method="POST" action="{{ route('siswa.announcements.object', $announcement) }}" class="space-y-3">
                            @csrf

                            <textarea name="reason" rows="4" placeholder="Tulis alasan keberatan Anda secara jelas, misalnya alasan akademik, minat jurusan, atau data yang perlu ditinjau ulang"
                                class="w-full p-4 rounded-2xl bg-white border border-slate-200 text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition"></textarea>

                            <button
                                class="w-full inline-flex items-center justify-center gap-2 bg-white hover:bg-blue-50 text-blue-700 border border-blue-100 py-4 rounded-2xl font-extrabold transition">
                                <i class="fa-solid fa-message"></i>
                                Ajukan Keberatan
                            </button>
                        </form>
                    @elseif($response || ($announcement->type === 'final' && $classStudent))
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-100 text-blue-700 rounded-[28px] p-5">
                                @if($response)
                                    <p class="font-bold">Respons Anda: {{ $response->response === 'accepted' ? 'Diterima' : 'Mengajukan keberatan' }}</p>
                                @else
                                    <p class="font-bold">Pengumuman final sudah tersedia.</p>
                                @endif

                                @if($response?->responded_at)
                                    <p class="text-sm mt-1">Dikirim pada {{ $response->responded_at->translatedFormat('d F Y H:i') }}</p>
                                @elseif($announcement->type === 'final' && $announcement->published_at)
                                    <p class="text-sm mt-1">Dipublikasikan pada {{ $announcement->published_at->translatedFormat('d F Y H:i') }}</p>
                                @endif
                            </div>

                            @if((($response && $response->response === 'accepted') || $announcement->type === 'final') && $classStudent)
                                <a href="{{ route('siswa.announcements.letter', $announcement) }}"
                                    target="_blank" rel="noopener"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition">
                                    <i class="fa-solid fa-file-pdf"></i>
                                    Lihat Surat Keterangan PDF
                                </a>
                            @endif
                        </div>
                    @endif
                @endif

            </div>
        </div>

        {{-- Navigation --}}
        <div class="max-w-2xl mx-auto grid sm:grid-cols-2 gap-3 mt-8">

            {{-- Back To Wizard --}}
            <a href="{{ route('siswa.wizard.index') }}" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-blue-50
            text-blue-700 border border-blue-100 py-4 rounded-2xl font-extrabold transition">

                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Wizard
            </a>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                text-white py-4 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition">

                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </button>
            </form>

        </div>
    </div>
@endsection
