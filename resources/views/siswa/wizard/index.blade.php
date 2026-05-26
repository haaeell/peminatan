@extends('layouts.siswa')

@section('content')
    @php
        $steps = [
            'onboarding' => 10,
            'biodata' => 25,
            'package_choice' => 45,
            'selfie' => 65,
            'waiting_session' => 80,
            'academic_test' => 90,
            'psychology_test' => 95,
            'completed' => 100,
        ];

        $progress = $steps[$student->status] ?? 10;
    @endphp

    <div class="min-h-screen bg-slate-100 pb-32">

        {{-- Header --}}
        <div class="sticky top-0 z-30 bg-white/90 backdrop-blur-xl border-b border-slate-200">
            <div class="max-w-5xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>

                        <div>
                            <div class="text-sm text-slate-500">Halo,</div>
                            <div class="font-extrabold text-slate-900">{{ $student->name }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="w-11 h-11 rounded-2xl bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 transition">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-bold text-slate-700">Progress Pendaftaran</div>
                        <div class="text-sm font-extrabold text-blue-600">{{ $progress }}%</div>
                    </div>

                    <div class="w-full h-3 rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-3 rounded-full bg-gradient-to-r from-blue-600 to-blue-400 transition-all duration-500"
                            style="width: {{ $progress }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="max-w-5xl mx-auto p-4 md:p-6">

            @if($student->status === 'onboarding')
                <div class="bg-white border border-slate-200 rounded-[32px] p-8 shadow-sm">
                    <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-graduation-cap text-4xl"></i>
                    </div>

                    <div class="text-center max-w-2xl mx-auto">
                        <h1 class="text-3xl font-extrabold text-slate-900 mb-4">Selamat Datang</h1>

                        <p class="text-slate-500 leading-relaxed mb-8">
                            Ikuti seluruh tahapan pemilihan jurusan dengan lengkap dan benar agar proses penempatan berjalan
                            lancar.
                        </p>

                        <button id="startBtn" type="button" class="btn-primary">
                            <i class="fa-solid fa-arrow-right"></i>
                            Mulai Sekarang
                        </button>
                    </div>
                </div>
            @endif

            @if($student->status === 'biodata' || $student->status === 'onboarding')
                <form id="biodataForm"
                    class="bg-white border border-slate-200 rounded-[32px] p-6 md:p-8 shadow-sm space-y-5 {{ $student->status === 'onboarding' ? 'hidden mt-6' : '' }}">
                    @csrf

                    <div class="mb-4">
                        <h2 class="text-2xl font-extrabold text-slate-900">Biodata Siswa</h2>
                        <p class="text-slate-500 mt-2">Lengkapi data diri dan informasi orang tua.</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="birth_place" placeholder="Tempat lahir" class="input" required>
                        <input type="date" name="birth_date" class="input" required>
                    </div>

                    <select name="gender" class="input" required>
                        <option value="">Jenis Kelamin</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>

                    <textarea name="address" rows="4" placeholder="Alamat lengkap" class="input" required></textarea>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="phone" placeholder="No HP siswa" class="input">
                        <input name="parent_phone" placeholder="No HP orang tua" class="input" required>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="father_name" placeholder="Nama ayah" class="input" required>
                        <input name="mother_name" placeholder="Nama ibu" class="input" required>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-save"></i>
                        Simpan Biodata
                    </button>
                </form>
            @endif

            @if($student->status === 'package_choice')
                <form id="packageForm" class="space-y-6">
                    @csrf

                    <div class="bg-white border border-slate-200 rounded-[32px] p-6 md:p-8 shadow-sm">
                        <h2 class="text-2xl font-extrabold text-slate-900">Pilih Jurusan</h2>
                        <p class="text-slate-500 mt-2">Pelajari jurusan yang tersedia, lalu tentukan pilihan pertama dan kedua.
                        </p>

                        <div class="grid md:grid-cols-2 gap-4 mt-6">
                            @foreach($packages as $package)
                                <div
                                    class="group rounded-[28px] p-6 border border-slate-200 bg-white hover:border-blue-300 hover:shadow-xl hover:shadow-blue-100 transition-all duration-300">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-2xl shadow-sm shrink-0"
                                            style="background: {{ $package->color ?? '#2563eb' }}">
                                        </div>

                                        <div>
                                            <div class="font-extrabold text-slate-900">{{ $package->name }}</div>
                                            <div class="text-sm text-slate-500 mt-1">{{ $package->description }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach($package->subjects as $subject)
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100">
                                                {{ $subject->subject_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-[32px] p-6 shadow-sm space-y-4">
                        <select name="first_package_id" class="input" required>
                            <option value="">Pilihan 1</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                        </select>

                        <select name="second_package_id" class="input" required>
                            <option value="">Pilihan 2</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-check"></i>
                            Simpan Pilihan
                        </button>
                    </div>
                </form>
            @endif

            @if($student->status === 'selfie')
                <div id="selfieStep" class="bg-white border border-slate-200 rounded-[32px] p-6 md:p-8 shadow-sm">
                    <h2 class="text-2xl font-extrabold text-slate-900">Verifikasi Selfie</h2>
                    <p class="text-slate-500 mt-2 mb-6">Pastikan wajah terlihat jelas dan pencahayaan cukup.</p>

                    <video id="video" autoplay playsinline
                        class="w-full aspect-video object-cover rounded-[28px] bg-slate-900 mb-4">
                    </video>

                    <canvas id="canvas" class="hidden"></canvas>

                    <img id="preview" class="hidden w-full aspect-video object-cover rounded-[28px] mb-4">

                    <div class="grid grid-cols-2 gap-3">
                        <button id="captureBtn" class="btn-primary" type="button">
                            <i class="fa-solid fa-camera"></i>
                            Ambil Foto
                        </button>

                        <button id="retakeBtn" class="btn-secondary hidden" type="button">
                            <i class="fa-solid fa-rotate-left"></i>
                            Ulangi
                        </button>
                    </div>

                    <button id="uploadSelfieBtn" type="button" class="btn-primary mt-4 hidden">
                        <i class="fa-solid fa-upload"></i>
                        Simpan Selfie
                    </button>
                </div>
            @endif

            @if($student->status === 'waiting_session')
                <div class="text-center bg-white border border-slate-200 rounded-[32px] p-10 shadow-sm">
                    <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-clock text-4xl"></i>
                    </div>

                    <h2 class="text-2xl font-extrabold text-slate-900">Menunggu Sesi Tes</h2>

                    <p class="text-slate-500 mt-3">
                        Anda akan diarahkan saat sesi tes sudah dibuka.
                    </p>

                    <a href="{{ route('siswa.waiting-session') }}" class="btn-primary mt-6">
                        <i class="fa-solid fa-calendar-days"></i>
                        Lihat Jadwal Sesi
                    </a>
                </div>
            @endif

            @if($announcement)
                <div class="mt-4">
                    <a href="{{ route('siswa.announcements.index') }}" class="flex items-center justify-between gap-4 bg-blue-600 hover:bg-blue-700
                    text-white rounded-2xl px-5 py-4 shadow-lg shadow-blue-200 transition-all duration-300">

                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-bullhorn text-xl"></i>
                            </div>

                            <div>
                                <div class="text-sm font-bold uppercase tracking-wide text-blue-100">
                                    Pengumuman Baru
                                </div>

                                <div class="font-extrabold text-lg">
                                    {{ $announcement->title }}
                                </div>
                            </div>
                        </div>

                        <div class="w-11 h-11 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <style>
        .input {
            width: 100%;
            border-radius: 1.25rem;
            background: white;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            padding: 1rem;
            outline: none;
            transition: all .25s ease;
        }

        .input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px #dbeafe;
        }

        .btn-primary {
            width: 100%;
            min-height: 56px;
            border-radius: 1.25rem;
            background: #2563eb;
            color: white;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            transition: all .25s ease;
            box-shadow: 0 10px 24px rgba(37, 99, 235, .2);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-secondary {
            width: 100%;
            min-height: 56px;
            border-radius: 1.25rem;
            background: #e2e8f0;
            color: #334155;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $('#startBtn').on('click', function () {
            $('#biodataForm').removeClass('hidden');
            $(this).closest('.bg-white').hide();
        });

        $('#biodataForm').on('submit', function (e) {
            e.preventDefault();

            const button = $(this).find('button[type="submit"]');
            button.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Memproses...');

            $.post('{{ route("siswa.wizard.biodata") }}', $(this).serialize())
                .done(function () {
                    Swal.fire('Berhasil', 'Biodata tersimpan.', 'success')
                        .then(() => location.reload());
                })
                .fail(function (xhr) {
                    button.prop('disabled', false).html('<i class="fa-solid fa-save"></i> Simpan Biodata');
                    Swal.fire('Gagal', xhr.responseJSON?.message ?? 'Validasi gagal.', 'error');
                });
        });

        $('#packageForm').on('submit', function (e) {
            e.preventDefault();

            const first = $('[name="first_package_id"]').val();
            const second = $('[name="second_package_id"]').val();

            if (first && second && first === second) {
                Swal.fire('Periksa Pilihan', 'Pilihan 1 dan Pilihan 2 tidak boleh sama.', 'warning');
                return;
            }

            const button = $(this).find('button[type="submit"]');
            button.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Memproses...');

            $.post('{{ route("siswa.wizard.package-choice") }}', $(this).serialize())
                .done(function () {
                    Swal.fire('Berhasil', 'Pilihan jurusan tersimpan.', 'success')
                        .then(() => location.reload());
                })
                .fail(function (xhr) {
                    button.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Simpan Pilihan');
                    Swal.fire('Gagal', xhr.responseJSON?.message ?? 'Validasi gagal.', 'error');
                });
        });

        let stream = null;
        let photoData = null;

        async function startCamera() {
            if (!$('#video').length) return;

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });

                document.getElementById('video').srcObject = stream;
            } catch (error) {
                Swal.fire('Kamera Tidak Aktif', 'Izinkan akses kamera untuk melanjutkan verifikasi selfie.', 'warning');
            }
        }

        startCamera();

        $('#captureBtn').on('click', function () {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            canvas.getContext('2d').drawImage(video, 0, 0);

            photoData = canvas.toDataURL('image/jpeg', 0.9);

            $('#preview').attr('src', photoData).removeClass('hidden');
            $('#video').addClass('hidden');
            $('#retakeBtn, #uploadSelfieBtn').removeClass('hidden');
            $('#captureBtn').addClass('hidden');
        });

        $('#retakeBtn').on('click', function () {
            photoData = null;

            $('#preview').addClass('hidden');
            $('#video').removeClass('hidden');
            $('#retakeBtn, #uploadSelfieBtn').addClass('hidden');
            $('#captureBtn').removeClass('hidden');
        });

        $('#uploadSelfieBtn').on('click', function () {
            if (!photoData) {
                Swal.fire('Foto belum ada', 'Ambil foto terlebih dahulu.', 'warning');
                return;
            }

            const button = $(this);
            button.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Mengupload...');

            $.post('{{ route("siswa.wizard.selfie") }}', {
                _token: '{{ csrf_token() }}',
                photo: photoData,
                device_info: {
                    userAgent: navigator.userAgent,
                    platform: navigator.platform,
                    screen: screen.width + 'x' + screen.height,
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
                }
            }).done(function () {
                Swal.fire('Berhasil', 'Selfie tersimpan.', 'success')
                    .then(() => location.reload());
            }).fail(function (xhr) {
                button.prop('disabled', false).html('<i class="fa-solid fa-upload"></i> Simpan Selfie');
                Swal.fire('Gagal', xhr.responseJSON?.message ?? 'Upload selfie gagal.', 'error');
            });
        });
    </script>
@endpush