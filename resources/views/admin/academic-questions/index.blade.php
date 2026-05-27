@extends('layouts.admin')

@section('title', 'Soal Akademik')

@section('content')

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Soal Akademik</h1>
            <p class="text-slate-500 mt-2">
                Kelola soal akademik, pilihan jawaban, kunci, dan status soal.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-2xl font-bold">
            <i class="fa-solid fa-book-open"></i>
            Total: {{ $questions->total() ?? $questions->count() }} soal
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-6">

        {{-- Left Panel --}}
        <div class="space-y-6">

            {{-- Import Export --}}
            <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-file-import"></i>
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">Import Soal</h2>
                        <p class="text-sm text-slate-500">Upload soal akademik via Excel dengan dukungan gambar soal.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.academic-questions.import') }}"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <input type="file" name="file" accept=".csv,.xlsx,.xls"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700
                        file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                        file:bg-blue-600 file:text-white file:font-bold">

                    <button
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                        text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                        <i class="fa-solid fa-upload"></i>
                        Import Excel
                    </button>
                </form>

                <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700 leading-relaxed">
                    Format template mendukung kolom <span class="font-bold">image_url</span> untuk gambar soal.
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    <a href="{{ route('admin.academic-questions.template') }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100
                        text-blue-700 py-3 rounded-2xl font-bold transition">
                        <i class="fa-solid fa-file-lines"></i>
                        Template
                    </a>

                    <a href="{{ route('admin.academic-questions.export') }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100
                        text-blue-700 py-3 rounded-2xl font-bold transition">
                        <i class="fa-solid fa-download"></i>
                        Export
                    </a>
                </div>
            </div>

            {{-- Add Question --}}
            <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-plus"></i>
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">Tambah Soal</h2>
                        <p class="text-sm text-slate-500">Masukkan soal dan pilihan jawaban.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-700">
                        <p class="font-bold mb-2">Periksa kembali input:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.academic-questions.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pertanyaan</label>
                        <textarea name="question" rows="5" placeholder="Tulis soal akademik di sini..."
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                            focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">{{ old('question') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Soal</label>
                        <input type="file" name="image" accept="image/*"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700
                            file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                            file:bg-blue-600 file:text-white file:font-bold">
                        <p class="text-xs text-slate-500 mt-2">Opsional. Format JPG, PNG, WEBP, maksimal 2 MB.</p>
                    </div>

                    <div class="space-y-3">
                        @foreach(['A', 'B', 'C', 'D'] as $label)
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Pilihan {{ $label }}
                                </label>

                                <div class="flex gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-extrabold">
                                        {{ $label }}
                                    </div>

                                    <input name="options[{{ $label }}]"
                                        value="{{ old('options.' . $label) }}"
                                        placeholder="Isi pilihan {{ $label }}"
                                        class="flex-1 px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                                        focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kunci Jawaban</label>
                        <select name="correct_answer"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                            focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            <option value="">Pilih kunci jawaban</option>
                            @foreach(['A', 'B', 'C', 'D'] as $label)
                                <option value="{{ $label }}" {{ old('correct_answer') === $label ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex items-center gap-3 p-4 rounded-2xl bg-blue-50 text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="font-semibold text-sm">Soal aktif dan bisa digunakan</span>
                    </label>

                    <button
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                        text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                        <i class="fa-solid fa-save"></i>
                        Simpan Soal
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="xl:col-span-2 bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">Daftar Soal</h2>
                    <p class="text-sm text-slate-500">Preview soal, kunci jawaban, dan pilihan tersedia.</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($questions as $question)
                    <div class="border border-slate-200 rounded-[26px] p-5 hover:shadow-lg hover:shadow-blue-100 transition-all duration-300">

                        {{-- Question Header --}}
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold shadow-lg shadow-blue-200">
                                    {{ $loop->iteration }}
                                </div>

                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                            <i class="fa-solid fa-key"></i>
                                            Kunci:
                                            {{ $question->options->firstWhere('is_correct', true)?->label ?? '-' }}
                                        </span>

                                        @if($question->is_active)
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </div>

                                    <p class="font-bold text-slate-900 leading-relaxed">
                                        {{ $question->question }}
                                    </p>

                                    @if($question->image_path)
                                        <img src="{{ asset('storage/' . $question->image_path) }}" alt="Gambar soal akademik"
                                            class="mt-4 w-full max-w-xl rounded-2xl border border-slate-200 object-contain bg-slate-50">
                                    @endif
                                </div>
                            </div>

                            <form id="delete-question-{{ $question->id }}" method="POST"
                                action="{{ route('admin.academic-questions.destroy', $question) }}">
                                @csrf
                                @method('DELETE')

                                <button type="button"
                                    onclick="confirmDelete('delete-question-{{ $question->id }}')"
                                    class="group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl
                                    bg-white text-slate-500 border border-slate-200
                                    hover:bg-blue-600 hover:text-white hover:border-blue-600
                                    shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300">
                                    <i class="fa-solid fa-trash-can group-hover:scale-110 transition-transform"></i>
                                    <span class="text-sm font-bold">Hapus</span>
                                </button>
                            </form>
                        </div>

                        {{-- Options --}}
                        <div class="grid md:grid-cols-2 gap-3 mt-5">
                            @foreach($question->options as $option)
                                <div
                                    class="rounded-2xl p-4 border
                                    {{ $option->is_correct
                                        ? 'bg-blue-50 border-blue-200 text-blue-800'
                                        : 'bg-slate-50 border-slate-200 text-slate-700' }}">

                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold
                                            {{ $option->is_correct
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-slate-500 border border-slate-200' }}">
                                            {{ $option->label }}
                                        </div>

                                        <div class="flex-1">
                                            <p class="text-sm font-semibold leading-relaxed">
                                                {{ $option->option_text }}
                                            </p>

                                            @if($option->is_correct)
                                                <p class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-blue-700">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    Jawaban benar
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @empty
                    <div class="text-center py-14">
                        <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-book-open text-2xl"></i>
                        </div>

                        <h2 class="text-xl font-extrabold text-slate-900">Belum ada soal</h2>
                        <p class="text-slate-500 mt-2">Tambahkan soal pertama melalui form di sebelah kiri.</p>
                    </div>
                @endforelse
            </div>

            @if(method_exists($questions, 'links'))
                <div class="mt-6">
                    {{ $questions->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
