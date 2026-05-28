@extends('layouts.admin')

@section('title', 'Sesi Tes')

@section('content')
    @php
        $typeLabels = [
            'both' => 'Akademik + Psikologi',
            'academic' => 'Akademik',
            'psychology' => 'Psikologi',
        ];
    @endphp

    <div class="space-y-8">
        <div class="grid xl:grid-cols-[1.05fr_1.55fr] gap-6 items-start">
            <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-900">Kelola Sesi Tes</h1>
                        <p class="text-sm text-slate-500 mt-1">Buat sesi, aktifkan jadwal, lalu tentukan kelas peserta.</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Total</div>
                        <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['total'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-wide text-blue-600">Aktif</div>
                        <div class="text-2xl font-extrabold text-blue-700 mt-1">{{ $summary['active'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Nonaktif</div>
                        <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $summary['inactive'] }}</div>
                    </div>
                </div>

                <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <div class="font-bold text-slate-800 mb-2">Urutan yang disarankan</div>
                    <div class="space-y-2">
                        <div>1. Buat nama sesi, tanggal, jam, dan tipe tes.</div>
                        <div>2. Tandai sesi sebagai aktif jika sudah siap dipakai siswa.</div>
                        <div>3. Tambahkan kelas asal yang boleh masuk ke sesi tersebut.</div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">Tambah Sesi Tes</h2>
                        <p class="text-sm text-slate-500">Isi form sekali dengan informasi yang paling dibutuhkan user.</p>
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

                <form method="POST" action="{{ route('admin.test-sessions.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Sesi</label>
                            <input name="name" value="{{ old('name') }}" placeholder="Contoh: Sesi Pagi Kelas X"
                                class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Tes</label>
                            <select name="test_type"
                                class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                                <option value="both" {{ old('test_type') === 'both' ? 'selected' : '' }}>Akademik + Psikologi</option>
                                <option value="academic" {{ old('test_type') === 'academic' ? 'selected' : '' }}>Akademik saja</option>
                                <option value="psychology" {{ old('test_type') === 'psychology' ? 'selected' : '' }}>Psikologi saja</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Tes</label>
                            <input type="date" name="test_date" value="{{ old('test_date') }}"
                                class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Mulai</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}"
                                class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Selesai</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}"
                                class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                        </div>
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="font-bold text-slate-800">Aktifkan sesi ini</div>
                            <div class="text-sm text-slate-500">Sesi aktif lebih mudah dikenali admin saat membagi kelas.</div>
                        </div>
                    </label>

                    <button
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                        <i class="fa-solid fa-save"></i>
                        Simpan Sesi Tes
                    </button>
                </form>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Daftar Sesi</h2>
                <p class="text-sm text-slate-500 mt-1">Setelah sesi dibuat, tambahkan kelas di kartu sesi yang sesuai.</p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm">
                <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Aktif</span>
                <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Nonaktif</span>
            </div>
        </div>

        <div class="grid xl:grid-cols-2 gap-6">
            @forelse($sessions as $session)
                <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm hover:shadow-xl hover:shadow-blue-100 transition-all duration-300">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-5">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl {{ $session->is_active ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center">
                                <i class="fa-solid fa-calendar-days text-xl"></i>
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h3 class="text-xl font-extrabold text-slate-900">{{ $session->name }}</h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $session->is_active ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $session->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </div>

                                <div class="text-sm text-slate-500">
                                    {{ $session->test_date->format('d M Y') }} |
                                    {{ \Illuminate\Support\Str::of($session->start_time)->substr(0, 5) }} -
                                    {{ \Illuminate\Support\Str::of($session->end_time)->substr(0, 5) }}
                                </div>

                                <div class="text-sm font-semibold text-slate-700 mt-1">
                                    {{ $typeLabels[$session->test_type] ?? strtoupper($session->test_type) }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-center min-w-[180px]">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 px-3 py-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Kelas</div>
                                <div class="text-xl font-extrabold text-slate-900 mt-1">{{ $session->classes_count }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 px-3 py-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Peserta</div>
                                <div class="text-xl font-extrabold text-slate-900 mt-1">{{ $session->students_count }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <h4 class="font-bold text-slate-900">Kelas yang terdaftar</h4>
                                <p class="text-sm text-slate-500">Kelas ini yang akan diarahkan ke sesi ini.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 min-h-[42px]">
                            @forelse($session->classes as $class)
                                <div class="group inline-flex items-center gap-2 bg-white text-slate-700 border border-slate-200 pl-3 pr-1.5 py-1.5 rounded-2xl text-sm font-bold">
                                    <i class="fa-solid fa-users text-xs text-blue-500"></i>
                                    <span>{{ $class->origin_class }}</span>

                                    <button type="button"
                                        class="deleteSessionClassBtn w-7 h-7 inline-flex items-center justify-center rounded-xl text-slate-400 hover:bg-blue-600 hover:text-white transition"
                                        data-action="{{ route('admin.test-sessions.classes.destroy', [$session, $class->id]) }}"
                                        title="Hapus kelas">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>
                            @empty
                                <div class="text-sm text-slate-400">Belum ada kelas. Tambahkan minimal satu kelas agar sesi bisa dipakai dengan jelas.</div>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('admin.test-sessions.classes.store', $session) }}"
                            class="flex flex-col sm:flex-row gap-3 mt-4">
                            @csrf

                            <input name="origin_class" placeholder="Contoh: X A"
                                class="flex-1 px-4 py-3 rounded-2xl bg-white border border-slate-200 text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                            <button
                                class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white px-4 py-3 rounded-2xl font-bold border border-blue-100 hover:border-blue-600 shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300">
                                <i class="fa-solid fa-plus"></i>
                                Tambah Kelas
                            </button>
                        </form>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 mt-6 pt-5 border-t border-slate-100">
                        <button type="button"
                            class="editBtn group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-600 hover:text-white hover:border-blue-600 shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300"
                            data-id="{{ $session->id }}"
                            data-name="{{ e($session->name) }}"
                            data-test_date="{{ $session->test_date->format('Y-m-d') }}"
                            data-start_time="{{ \Illuminate\Support\Str::of($session->start_time)->substr(0, 5) }}"
                            data-end_time="{{ \Illuminate\Support\Str::of($session->end_time)->substr(0, 5) }}"
                            data-test_type="{{ $session->test_type }}"
                            data-is_active="{{ $session->is_active ? 1 : 0 }}">
                            <i class="fa-solid fa-pen-to-square group-hover:scale-110 transition-transform"></i>
                            <span class="text-sm font-bold">Edit Sesi</span>
                        </button>

                        @if(Route::has('admin.test-sessions.destroy'))
                            <form id="delete-session-{{ $session->id }}" method="POST"
                                action="{{ route('admin.test-sessions.destroy', $session) }}">
                                @csrf
                                @method('DELETE')

                                <button type="button" onclick="confirmDelete('delete-session-{{ $session->id }}')"
                                    class="group inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-slate-500 border border-slate-200 hover:bg-blue-600 hover:text-white hover:border-blue-600 shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300">
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
                    <p class="text-slate-500 mt-2">Mulai dari form di atas, lalu tambahkan kelas ke sesi yang sudah dibuat.</p>
                </div>
            @endforelse
        </div>

        <form id="deleteSessionClassForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        @if($sessions->hasPages())
            <div class="bg-white border border-slate-200 rounded-[24px] px-4 py-3 shadow-sm">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>

    <div id="editModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div class="bg-white border border-slate-200 rounded-[30px] p-6 w-full max-w-2xl shadow-2xl">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">Edit Sesi Tes</h2>
                    <p class="text-sm text-slate-500 mt-1">Perbarui detail sesi tanpa harus menebak field yang penting.</p>
                </div>

                <button type="button" id="closeModal"
                    class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Sesi</label>
                        <input name="name" id="edit_name"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Tes</label>
                        <select name="test_type" id="edit_test_type"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            <option value="both">Akademik + Psikologi</option>
                            <option value="academic">Akademik saja</option>
                            <option value="psychology">Psikologi saja</option>
                        </select>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Tes</label>
                        <input type="date" name="test_date" id="edit_test_date"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Mulai</label>
                        <input type="time" name="start_time" id="edit_start_time"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Selesai</label>
                        <input type="time" name="end_time" id="edit_end_time"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                    <input type="checkbox" name="is_active" value="1" id="edit_is_active"
                        class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <div>
                        <div class="font-bold text-slate-800">Sesi aktif</div>
                        <div class="text-sm text-slate-500">Nonaktifkan jika sesi hanya ingin disimpan sebagai arsip atau draft.</div>
                    </div>
                </label>

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
                $('#edit_is_active').prop('checked', Number($(this).data('is_active')) === 1);

                $('#editModal').removeClass('hidden').addClass('flex');
            });

            $('.deleteSessionClassBtn').on('click', function () {
                const action = $(this).data('action');

                $('#deleteSessionClassForm').attr('action', action);
                confirmDelete('deleteSessionClassForm');
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
