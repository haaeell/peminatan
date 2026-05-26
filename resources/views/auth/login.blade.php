@extends('layouts.auth')

@section('content')
    <div class="min-h-screen bg-slate-100 flex items-center justify-center px-4 py-10">

        <div
            class="w-full max-w-6xl grid lg:grid-cols-2 bg-white rounded-[36px] overflow-hidden shadow-2xl shadow-blue-100 border border-slate-200">

            {{-- Left Branding --}}
            <div
                class="hidden lg:flex relative bg-gradient-to-br from-blue-700 via-blue-600 to-blue-400 p-10 text-white overflow-hidden">

                <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-white/10"></div>
                <div class="absolute bottom-10 -left-16 w-64 h-64 rounded-full bg-white/10"></div>

                <div class="relative z-10 flex flex-col justify-between w-full">
                    <div>
                        <div
                            class="w-16 h-16 rounded-3xl bg-white/15 backdrop-blur-xl border border-white/20 flex items-center justify-center shadow-xl">
                            <i class="fa-solid fa-graduation-cap text-3xl"></i>
                        </div>

                        <h1 class="text-4xl font-extrabold mt-8 leading-tight">
                            Sistem Pemilihan Jurusan
                        </h1>

                        <p class="text-blue-100 mt-4 leading-relaxed max-w-md">
                            Platform digital untuk membantu proses pemilihan jurusan siswa secara lebih tertata, aman, dan
                            mudah digunakan.
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-10">
                        <div class="bg-white/15 backdrop-blur-xl rounded-3xl p-4 border border-white/20">
                            <i class="fa-solid fa-user-check text-2xl mb-3"></i>
                            <p class="text-sm font-bold">Login Aman</p>
                        </div>

                        <div class="bg-white/15 backdrop-blur-xl rounded-3xl p-4 border border-white/20">
                            <i class="fa-solid fa-file-pen text-2xl mb-3"></i>
                            <p class="text-sm font-bold">Tes Online</p>
                        </div>

                        <div class="bg-white/15 backdrop-blur-xl rounded-3xl p-4 border border-white/20">
                            <i class="fa-solid fa-chart-line text-2xl mb-3"></i>
                            <p class="text-sm font-bold">Hasil Cepat</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Login Form --}}
            <div class="p-6 sm:p-10 lg:p-14 flex items-center">
                <div class="w-full max-w-md mx-auto">

                    {{-- Mobile Logo --}}
                    <div class="lg:hidden flex justify-center mb-8">
                        <div
                            class="w-16 h-16 rounded-3xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-graduation-cap text-3xl"></i>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="text-sm font-bold text-blue-600 uppercase tracking-wide">
                            Selamat Datang
                        </p>

                        <h2 class="text-3xl font-extrabold text-slate-900 mt-2">
                            Masuk ke Akun
                        </h2>

                        <p class="text-slate-500 mt-3">
                            Gunakan email admin atau NISN siswa untuk melanjutkan.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-700">
                            <p class="font-bold mb-1">Login gagal</p>
                            <p>Periksa kembali email/NISN dan password kamu.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        {{-- Login --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Email / NISN
                            </label>

                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                                <input type="text" name="login" value="{{ old('login') }}" required autofocus
                                    placeholder="Masukkan email admin atau NISN"
                                    class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                                        focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                            </div>

                            @error('login')
                                <p class="text-sm text-blue-700 mt-2 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Password
                            </label>

                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                                <input type="password" name="password" id="passwordInput" required
                                    placeholder="Masukkan password"
                                    class="w-full pl-11 pr-12 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                                        focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                                <button type="button" id="togglePassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>

                            @error('password')
                                <p class="text-sm text-blue-700 mt-2 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <button
                            class="w-full inline-flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700
                                text-white font-extrabold shadow-lg shadow-blue-200 transition-all duration-300 hover:-translate-y-0.5">

                            <i class="fa-solid fa-right-to-bracket"></i>
                            Masuk
                        </button>
                    </form>

                    <div class="mt-8 text-center text-sm text-slate-400">
                        © {{ date('Y') }} Sistem Pemilihan Jurusan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('passwordInput');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';

                    passwordInput.type = isPassword ? 'text' : 'password';
                    this.innerHTML = isPassword
                        ? '<i class="fa-solid fa-eye-slash"></i>'
                        : '<i class="fa-solid fa-eye"></i>';
                });
            }
        });
    </script>
@endpush