@extends('layouts.admin')

@section('title', 'Sesi Tes')

@section('content')

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Sesi Tes</h1>
            <p class="text-slate-500 mt-2">Kelola jadwal sesi tes, tipe tes, dan kelas peserta.</p>
        </div>

        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-2xl font-bold">
            <i class="fa-solid fa-clock"></i>
            Total: {{ $sessions->count() }} sesi
        </div>
    </div>

    {{-- Form Tambah --}}
    <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm mb-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-plus"></i>
            </div>

            <div>
                <h2 class="text-xl font-extrabold text-slate-900">Tambah Sesi Tes</h2>
                <p class="text-sm text-slate-500">Buat jadwal sesi tes baru.</p>
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

        <form method="POST" action="{{ route('admin.test-sessions.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Sesi</label>
                <input name="name" value="{{ old('name') }}" placeholder="Sesi 1"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal</label>
                <input type="date" name="test_date" value="{{ old('test_date') }}"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Mulai</label>
                <input type="time" name="start_time" value="{{ old('start_time') }}"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Selesai</label>
                <input type="time" name="end_time" value="{{ old('end_time') }}"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Tes</label>
                <select name="test_type"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    <option value="both" {{ old('test_type') === 'both' ? 'selected' : '' }}>Akademik + Psikologi</option>
                    <option value="academic" {{ old('test_type') === 'academic' ? 'selected' : '' }}>Akademik</option>
                    <option value="psychology" {{ old('test_type') === 'psychology' ? 'selected' : '' }}>Psikologi</option>
                </select>
            </div>

            <div class="flex items-end">
                <button
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                    <i class="fa-solid fa-save"></i>
                    Tambah
                </button>
            </div>
        </form>
    </div>

    {{-- List Sesi --}}
    <div class="grid xl:grid-cols-2 gap-6">
        @forelse($sessions as $session)
            <div
                class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm hover:shadow-xl hover:shadow-blue-100 transition-all duration-300">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-calendar-days text-xl"></i>
                        </div>

                        <div>
                            <h2 class="text-xl font-extrabold text-slate-900">
                                {{ $session->name }}
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $session->test_date->format('d M Y') }}
                            </p>

                            <p class="text-sm font-bold text-slate-700 mt-1">
                                {{ \Illuminate\Support\Str::of($session->start_time)->substr(0, 5) }}
                                -
                                {{ \Illuminate\Support\Str::of($session->end_time)->substr(0, 5) }}
                            </p>
                        </div>
                    </div>

                    <span class="px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-extrabold">
                        {{ strtoupper($session->test_type) }}
                    </span>
                </div>

                {{-- Kelas --}}
                <div class="flex flex-wrap gap-2">
                    @forelse($session->classes as $class)
                        <div class="group inline-flex items-center gap-2 bg-blue-50 text-blue-700 border border-blue-100
                        pl-3 pr-1.5 py-1.5 rounded-2xl text-sm font-bold hover:bg-blue-100 transition">

                            <i class="fa-solid fa-users text-xs"></i>

                            <span>{{ $class->origin_class }}</span>

                            <form id="delete-session-class-{{ $class->id }}" method="POST"
                                action="{{ route('admin.test-sessions.classes.destroy', [$session, $class]) }}">
                                @csrf
                                @method('DELETE')

                                <button type="button" onclick="confirmDelete('delete-session-class-{{ $class->id }}')" class="w-7 h-7 inline-flex items-center justify-center rounded-xl
                                text-blue-500 hover:bg-blue-600 hover:text-white transition" title="Hapus kelas">

                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <span class="text-sm text-slate-400">Belum ada kelas.</span>
                    @endforelse
                </div>

                {{-- Tambah Kelas --}}
                <form method="POST" action="{{ route('admin.test-sessions.classes.store', $session) }}"
                    class="flex flex-col sm:flex-row gap-3 mt-5">
                    @csrf

                    <input name="origin_class" placeholder="Contoh: X A"
                        class="flex-1 px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                    <button
                        class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white px-4 py-3 rounded-2xl font-bold border border-blue-100 hover:border-blue-600 shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300">
                        <i class="fa-solid fa-plus"></i>
                        Tambah Kelas
                    </button>
                </form>

                {{-- Action --}}
                <div class="flex items-center justify-end gap-2 mt-6 pt-5 border-t border-slate-100">
                    <button type="button" class="editBtn group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl
                                bg-blue-50 text-blue-700 border border-blue-100
                                hover:bg-blue-600 hover:text-white hover:border-blue-600
                                shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300"
                        data-id="{{ $session->id }}" data-name="{{ e($session->name) }}"
                        data-test_date="{{ $session->test_date->format('Y-m-d') }}"
                        data-start_time="{{ \Illuminate\Support\Str::of($session->start_time)->substr(0, 5) }}"
                        data-end_time="{{ \Illuminate\Support\Str::of($session->end_time)->substr(0, 5) }}"
                        data-test_type="{{ $session->test_type }}">

                        <i class="fa-solid fa-pen-to-square group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-bold">Edit</span>
                    </button>

                    @if(Route::has('admin.test-sessions.destroy'))
                        <form id="delete-session-{{ $session->id }}" method="POST"
                            action="{{ route('admin.test-sessions.destroy', $session) }}">
                            @csrf
                            @method('DELETE')

                            <button type="button" onclick="confirmDelete('delete-session-{{ $session->id }}')" class="group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl
                                            bg-white text-slate-500 border border-slate-200
                                            hover:bg-blue-600 hover:text-white hover:border-blue-600
                                            shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300">

                                <i class="fa-solid fa-trash-can group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-bold">Hapus</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="xl:col-span-2 bg-white border border-slate-200 rounded-[30px] p-10 text-center shadow-sm">
                <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                </div>

                <h2 class="text-xl font-extrabold text-slate-900">Belum ada sesi tes</h2>
                <p class="text-slate-500 mt-2">Tambahkan sesi tes pertama melalui form di atas.</p>
            </div>
        @endforelse
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4">

        <div class="bg-white border border-slate-200 rounded-[30px] p-6 w-full max-w-2xl shadow-2xl">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">Edit Sesi Tes</h2>
                    <p class="text-sm text-slate-500 mt-1">Perbarui jadwal dan tipe sesi tes.</p>
                </div>

                <button type="button" id="closeModal"
                    class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Sesi</label>
                    <input name="name" id="edit_name"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal</label>
                        <input type="date" name="test_date" id="edit_test_date"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mulai</label>
                        <input type="time" name="start_time" id="edit_start_time"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Selesai</label>
                        <input type="time" name="end_time" id="edit_end_time"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Tes</label>
                    <select name="test_type" id="edit_test_type"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                        <option value="both">Akademik + Psikologi</option>
                        <option value="academic">Akademik</option>
                        <option value="psychology">Psikologi</option>
                    </select>
                </div>

                <button
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                    <i class="fa-solid fa-save"></i>
                    Update Sesi
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const updateUrlTemplate = "{{ route('admin.test-sessions.update', ':id') }}";

            $('.editBtn').on('click', function () {
                const id = $(this).data('id');
                const updateUrl = updateUrlTemplate.replace(':id', id);

                $('#editForm').attr('action', updateUrl);
                $('#edit_name').val($(this).data('name') || '');
                $('#edit_test_date').val($(this).data('test_date') || '');
                $('#edit_start_time').val($(this).data('start_time') || '');
                $('#edit_end_time').val($(this).data('end_time') || '');
                $('#edit_test_type').val($(this).data('test_type') || 'both');

                $('#editModal').removeClass('hidden').addClass('flex');
            });

            function closeEditModal() {
                $('#editModal').addClass('hidden').removeClass('flex');
                $('#editForm')[0].reset();
            }

            $('#closeModal').on('click', closeEditModal);

            $('#editModal').on('click', function (e) {
                if (e.target.id === 'editModal') {
                    closeEditModal();
                }
            });

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeEditModal();
                }
            });
        });
    </script>
@endpush