@extends('layouts.admin')

@section('title', 'Soal Psikotes')

@section('content')

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Soal Psikotes</h1>
            <p class="text-slate-500 mt-2">
                Kelola pernyataan psikotes, pilihan jawaban, dan bobot tiap jurusan.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-2xl font-bold">
            <i class="fa-solid fa-brain"></i>
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
                        <h2 class="text-xl font-extrabold text-slate-900">File Data</h2>
                        <p class="text-sm text-slate-500">Import, download template, atau export data psikotes.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.psychology-questions.import') }}"
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
                    Gunakan kolom <span class="font-bold">question_group</span> untuk mengelompokkan opsi A-D dan
                    <span class="font-bold">image_url</span> bila soal memakai gambar.
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    <a href="{{ route('admin.psychology-questions.template') }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100
                        text-blue-700 py-3 rounded-2xl font-bold transition">
                        <i class="fa-solid fa-file-lines"></i>
                        Template
                    </a>

                    <a href="{{ route('admin.psychology-questions.export') }}"
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
                        <p class="text-sm text-slate-500">Isi pernyataan, opsi, dan bobot jurusan.</p>
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

                <form method="POST" action="{{ route('admin.psychology-questions.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Pernyataan Psikotes
                        </label>

                        <textarea name="question" rows="5" placeholder="Tulis pernyataan psikotes di sini..."
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

                    <div class="space-y-4">
                        @foreach(['A', 'B', 'C', 'D'] as $label)
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold">
                                        {{ $label }}
                                    </div>

                                    <div>
                                        <p class="font-extrabold text-slate-900">Pilihan {{ $label }}</p>
                                        <p class="text-xs text-slate-500">Isi opsi dan bobot per jurusan.</p>
                                    </div>
                                </div>

                                <input name="options[{{ $label }}][text]"
                                    value="{{ old('options.' . $label . '.text') }}"
                                    placeholder="Tulis pilihan {{ $label }}"
                                    class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-800
                                    focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                                <div class="mt-4">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
                                        Bobot Jurusan
                                    </p>

                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                        @foreach($packages as $package)
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 mb-1">
                                                    {{ $package->code }}
                                                </label>

                                                <input type="number"
                                                    name="options[{{ $label }}][weights][{{ $package->id }}]"
                                                    value="{{ old('options.' . $label . '.weights.' . $package->id) }}"
                                                    placeholder="0"
                                                    class="w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800
                                                    focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
                    <h2 class="text-xl font-extrabold text-slate-900">Daftar Soal Psikotes</h2>
                    <p class="text-sm text-slate-500">Preview pilihan jawaban dan bobot tiap jurusan.</p>
                </div>

                <form method="GET" class="flex items-center gap-3">
                    <label class="text-sm font-semibold text-slate-500">Tampilkan</label>
                    <select name="per_page" onchange="this.form.submit()"
                        class="px-4 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800">
                        @foreach(['10' => '10', '25' => '25', '50' => '50', 'all' => 'Semua'] as $value => $label)
                            <option value="{{ $value }}" {{ request('per_page', '10') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="space-y-4">
                @forelse($questions as $question)
                    @php
                        $questionNumber = method_exists($questions, 'firstItem')
                            ? $questions->firstItem() + $loop->index
                            : $loop->iteration;
                    @endphp
                    <div class="border border-slate-200 rounded-[26px] p-5 hover:shadow-lg hover:shadow-blue-100 transition-all duration-300">

                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold shadow-lg shadow-blue-200">
                                    {{ $questionNumber }}
                                </div>

                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
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

                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                            <i class="fa-solid fa-scale-balanced"></i>
                                            Bobot Jurusan
                                        </span>
                                    </div>

                                    <p class="font-bold text-slate-900 leading-relaxed">
                                        {{ $question->question }}
                                    </p>

                                    @if($question->image_path)
                                        <img src="{{ asset('storage/' . $question->image_path) }}" alt="Gambar soal psikotes"
                                            class="mt-4 w-full max-w-xl rounded-2xl border border-slate-200 object-contain bg-slate-50">
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="editPsychologyQuestionBtn group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl
                                    bg-blue-50 text-blue-700 border border-blue-100
                                    hover:bg-blue-600 hover:text-white hover:border-blue-600
                                    shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300"
                                    data-id="{{ $question->id }}"
                                    data-question="{{ e($question->question) }}"
                                    data-image="{{ $question->image_path ? asset('storage/' . $question->image_path) : '' }}"
                                    data-active="{{ $question->is_active ? 1 : 0 }}"
                                    @foreach(['A', 'B', 'C', 'D'] as $label)
                                        data-option-{{ strtolower($label) }}="{{ e(optional($question->options->firstWhere('label', $label))->option_text) }}"
                                        @foreach($packages as $package)
                                            data-weight-{{ strtolower($label) }}-{{ $package->id }}="{{ optional(optional($question->options->firstWhere('label', $label))->weights->firstWhere('package_id', $package->id))->weight ?? 0 }}"
                                        @endforeach
                                    @endforeach
                                >
                                    <i class="fa-solid fa-pen-to-square group-hover:scale-110 transition-transform"></i>
                                    <span class="text-sm font-bold">Edit</span>
                                </button>

                                <form id="delete-psychology-question-{{ $question->id }}" method="POST"
                                    action="{{ route('admin.psychology-questions.destroy', $question) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                        onclick="confirmDelete('delete-psychology-question-{{ $question->id }}')"
                                        class="group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl
                                        bg-white text-slate-500 border border-slate-200
                                        hover:bg-blue-600 hover:text-white hover:border-blue-600
                                        shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300">
                                        <i class="fa-solid fa-trash-can group-hover:scale-110 transition-transform"></i>
                                        <span class="text-sm font-bold">Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="space-y-3 mt-5">
                            @foreach($question->options as $option)
                                <div class="rounded-2xl p-4 border border-slate-200 bg-slate-50">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-extrabold">
                                            {{ $option->label }}
                                        </div>

                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-800 leading-relaxed">
                                                {{ $option->option_text }}
                                            </p>

                                            <div class="flex flex-wrap gap-2 mt-3">
                                                @forelse($option->weights as $weight)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-blue-100 text-blue-700 text-xs font-bold">
                                                        {{ $weight->package->code }}:
                                                        <span class="text-slate-900">{{ $weight->weight }}</span>
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-slate-400">
                                                        Belum ada bobot.
                                                    </span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @empty
                    <div class="text-center py-14">
                        <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-brain text-2xl"></i>
                        </div>

                        <h2 class="text-xl font-extrabold text-slate-900">Belum ada soal psikotes</h2>
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

    <div id="editPsychologyQuestionModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div class="bg-white border border-slate-200 rounded-[28px] p-6 w-full max-w-4xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="font-extrabold text-xl text-slate-900">Edit Soal Psikotes</h2>
                    <p class="text-sm text-slate-500">Perbarui pernyataan, gambar, opsi, dan bobot jurusan.</p>
                </div>
                <button type="button" id="closePsychologyQuestionModal"
                    class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editPsychologyQuestionForm" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pernyataan Psikotes</label>
                    <textarea name="question" id="edit_psychology_question" rows="4"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Soal Baru</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700
                        file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                        file:bg-blue-600 file:text-white file:font-bold">
                    <img id="edit_psychology_image_preview" class="hidden mt-3 w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 object-contain">
                </div>
                <div class="space-y-4">
                    @foreach(['A', 'B', 'C', 'D'] as $label)
                        <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                            <div class="font-extrabold text-slate-900 mb-3">Pilihan {{ $label }}</div>
                            <input name="options[{{ $label }}][text]" id="edit_psychology_option_{{ strtolower($label) }}"
                                class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-800">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                                @foreach($packages as $package)
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 mb-1">{{ $package->code }}</label>
                                        <input type="number" name="options[{{ $label }}][weights][{{ $package->id }}]"
                                            id="edit_psychology_weight_{{ strtolower($label) }}_{{ $package->id }}"
                                            class="w-full px-3 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <label class="flex items-center gap-3 p-4 rounded-2xl bg-blue-50 text-slate-700">
                    <input type="checkbox" name="is_active" value="1" id="edit_psychology_is_active"
                        class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="font-semibold text-sm">Soal aktif dan bisa digunakan</span>
                </label>
                <button
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                    text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                    <i class="fa-solid fa-save"></i>
                    Update Soal
                </button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function () {
            const updateUrlTemplate = @json(route('admin.psychology-questions.update', ['psychology_question' => '__ID__']));

            $('.editPsychologyQuestionBtn').on('click', function () {
                const id = $(this).data('id');
                $('#editPsychologyQuestionForm').attr('action', updateUrlTemplate.replace('__ID__', id));
                $('#edit_psychology_question').val($(this).data('question') || '');
                $('#edit_psychology_is_active').prop('checked', Number($(this).data('active')) === 1);

                ['a', 'b', 'c', 'd'].forEach(function (label) {
                    $('#edit_psychology_option_' + label).val($(`.editPsychologyQuestionBtn[data-id="${id}"]`).data('option-' + label) || '');
                    @foreach($packages as $package)
                        $('#edit_psychology_weight_' + label + '_{{ $package->id }}').val($(`.editPsychologyQuestionBtn[data-id="${id}"]`).data('weight-' + label + '-{{ $package->id }}') ?? 0);
                    @endforeach
                });

                const image = $(this).data('image');
                if (image) {
                    $('#edit_psychology_image_preview').attr('src', image).removeClass('hidden');
                } else {
                    $('#edit_psychology_image_preview').attr('src', '').addClass('hidden');
                }

                $('#editPsychologyQuestionModal').removeClass('hidden').addClass('flex');
            });

            function closePsychologyQuestionModal() {
                $('#editPsychologyQuestionModal').addClass('hidden').removeClass('flex');
                $('#editPsychologyQuestionForm')[0].reset();
                $('#edit_psychology_image_preview').attr('src', '').addClass('hidden');
            }

            $('#closePsychologyQuestionModal').on('click', closePsychologyQuestionModal);
            $('#editPsychologyQuestionModal').on('click', function (e) {
                if (e.target.id === 'editPsychologyQuestionModal') {
                    closePsychologyQuestionModal();
                }
            });
        });
    </script>
@endpush
