@extends('layouts.admin')

@section('title', 'Master Siswa')

@section('content')
    <div class="grid xl:grid-cols-3 gap-6">

        {{-- Form Area --}}
        <div class="space-y-6">

            {{-- Tambah Siswa --}}
            <div class="bg-white border border-slate-200 rounded-[28px] p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-lg">Tambah Siswa</h2>
                        <p class="text-sm text-slate-500">Buat akun siswa baru</p>
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

                <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Siswa</label>
                        <input name="name" value="{{ old('name') }}" placeholder="Contoh: Andi Pratama"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NISN</label>
                        <input name="nisn" value="{{ old('nisn') }}" placeholder="Masukkan NISN"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIS</label>
                        <input name="nis" value="{{ old('nis') }}" placeholder="Masukkan NIS"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kelas Asal</label>
                        <input name="origin_class" value="{{ old('origin_class') }}" placeholder="Contoh: X A"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                        <input name="password" type="password" placeholder="Password siswa"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <label class="flex items-center gap-3 p-4 rounded-2xl bg-blue-50 text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="font-semibold text-sm">Aktifkan akun siswa</span>
                    </label>

                    <button
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                        <i class="fa-solid fa-save"></i>
                        Simpan Siswa
                    </button>
                </form>
            </div>

            {{-- Import Export --}}
            <div class="bg-white border border-slate-200 rounded-[28px] p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-file-import"></i>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-lg">Import CSV</h2>
                        <p class="text-sm text-slate-500">Upload data siswa massal</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf

                    <input type="file" name="file" accept=".csv,.xlsx,.xls"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-600 file:text-white file:font-semibold">

                    <button
                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                        <i class="fa-solid fa-upload"></i>
                        Import Data
                    </button>
                </form>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    <a href="{{ route('admin.students.template') }}"
                        class="text-center bg-blue-50 hover:bg-blue-100 text-blue-700 py-3 rounded-2xl font-bold transition">
                        Template
                    </a>

                    <a href="{{ route('admin.students.export') }}"
                        class="text-center bg-blue-50 hover:bg-blue-100 text-blue-700 py-3 rounded-2xl font-bold transition">
                        Export
                    </a>
                </div>
            </div>
        </div>

        {{-- Table Area --}}
        <div class="xl:col-span-2 bg-white border border-slate-200 rounded-[28px] p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">Daftar Siswa</h2>
                    <p class="text-sm text-slate-500">Kelola akun, status, dan pilihan jurusan siswa</p>
                </div>

                <div class="text-sm font-semibold text-blue-700 bg-blue-50 px-4 py-2 rounded-2xl">
                    Total: {{ $students->total() ?? $students->count() }} siswa
                </div>
            </div>

            <form id="bulkForm" method="POST">
                @csrf

                <div class="flex flex-wrap gap-3 mb-5">
                    <button type="button" data-action="{{ route('admin.students.bulk-activate') }}"
                        data-title="Aktifkan siswa terpilih?" data-text="Akun siswa yang dipilih akan diaktifkan."
                        class="bulkBtn inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2.5 rounded-2xl font-bold transition">
                        <i class="fa-solid fa-check"></i>
                        Aktifkan
                    </button>

                    <button type="button" data-action="{{ route('admin.students.bulk-deactivate') }}"
                        data-title="Nonaktifkan siswa terpilih?" data-text="Akun siswa yang dipilih akan dinonaktifkan."
                        class="bulkBtn inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2.5 rounded-2xl font-bold transition">
                        <i class="fa-solid fa-ban"></i>
                        Nonaktifkan
                    </button>

                    <button type="button" data-action="{{ route('admin.students.bulk-delete') }}"
                        data-title="Hapus siswa terpilih?"
                        data-text="Data yang dihapus tidak bisa dikembalikan secara langsung."
                        class="bulkBtn inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-2xl font-bold shadow-lg shadow-blue-100 transition">
                        <i class="fa-solid fa-trash"></i>
                        Hapus
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class=" datatable w-full text-sm">
                        <thead>
                            <tr class="text-slate-600">
                                <th>
                                    <input type="checkbox" id="checkAll"
                                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th>Nama</th>
                                <th>NISN</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($students as $student)
                                        <tr class="align-top">
                                            <td>
                                                <input type="checkbox" name="ids[]" value="{{ $student->id }}"
                                                    class="checkItem w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            </td>

                                            <td class="font-bold text-slate-800">
                                                {{ $student->name }}
                                                <div class="text-xs text-slate-400 font-medium">NIS: {{ $student->nis ?? '-' }}</div>
                                            </td>

                                            <td>{{ $student->nisn }}</td>
                                            <td>{{ $student->origin_class }}</td>


                                            <td>
                                                <div class="flex items-center gap-2">

                                                    {{-- Edit --}}
                                                    <button type="button" class="editBtn group inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl
                                   bg-blue-50 text-blue-700 border border-blue-100
                                   hover:bg-blue-600 hover:text-white hover:border-blue-600
                                   shadow-sm hover:shadow-lg hover:shadow-blue-200
                                   transition-all duration-300" data-id="{{ $student->id }}" data-name="{{ e($student->name) }}"
                                                        data-nisn="{{ e($student->nisn) }}" data-nis="{{ e($student->nis) }}"
                                                        data-origin_class="{{ e($student->origin_class) }}"
                                                        data-is_active="{{ $student->user?->is_active ? 1 : 0 }}" title="Edit siswa">

                                                        <i
                                                            class="fa-solid fa-pen-to-square text-sm group-hover:scale-110 transition-transform"></i>
                                                        <span class="hidden xl:inline text-sm font-bold">Edit</span>
                                                    </button>

                                                    {{-- Delete --}}
                                                    <form id="delete-student-{{ $student->id }}" method="POST"
                                                        action="{{ route('admin.students.destroy', $student) }}">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="button"
                                                            onclick="confirmDelete('delete-student-{{ $student->id }}')" class="group inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl
                                       bg-white text-slate-500 border border-slate-200
                                       hover:bg-blue-600 hover:text-white hover:border-blue-600
                                       shadow-sm hover:shadow-lg hover:shadow-blue-200
                                       transition-all duration-300" title="Hapus siswa">

                                                            <i
                                                                class="fa-solid fa-trash-can text-sm group-hover:scale-110 transition-transform"></i>
                                                            <span class="hidden xl:inline text-sm font-bold">Hapus</span>
                                                        </button>
                                                    </form>

                                                </div>
                                            </td>
                                        </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>

        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div id="editModalPanel" class="bg-white border border-slate-200 rounded-[28px] p-6 w-full max-w-xl shadow-2xl">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="font-extrabold text-xl text-slate-900">Edit Siswa</h2>
                    <p class="text-sm text-slate-500">Perbarui data dan status akun siswa</p>
                </div>

                <button type="button" id="closeModal"
                    class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Siswa</label>
                    <input name="name" id="edit_name"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NISN</label>
                        <input name="nisn" id="edit_nisn"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIS</label>
                        <input name="nis" id="edit_nis"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kelas Asal</label>
                    <input name="origin_class" id="edit_origin_class"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru</label>
                    <input name="password" type="password" placeholder="Kosongkan jika tidak diganti"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                </div>

                <label class="flex items-center gap-3 p-4 rounded-2xl bg-blue-50 text-slate-700">
                    <input type="checkbox" name="is_active" value="1" id="edit_is_active"
                        class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="font-semibold text-sm">Akun siswa aktif</span>
                </label>

                <button
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
                    <i class="fa-solid fa-save"></i>
                    Update Siswa
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const editUrlTemplate = "{{ route('admin.students.update', ':id') }}";

            $('#checkAll').on('change', function () {
                $('.checkItem').prop('checked', $(this).is(':checked'));
            });

            $(document).on('change', '.checkItem', function () {
                const total = $('.checkItem').length;
                const checked = $('.checkItem:checked').length;
                $('#checkAll').prop('checked', total > 0 && total === checked);
            });

            $('.bulkBtn').on('click', function () {
                const checked = $('.checkItem:checked').length;
                const action = $(this).data('action');
                const title = $(this).data('title') || 'Proses data terpilih?';
                const text = $(this).data('text') || 'Pastikan data yang dipilih sudah benar.';

                if (checked === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih siswa',
                        text: 'Minimal pilih satu siswa terlebih dahulu.',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: title,
                    text: text,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#bulkForm').attr('action', action).submit();
                    }
                });
            });

            $('.editBtn').on('click', function () {
                const id = $(this).data('id');
                const updateUrl = editUrlTemplate.replace(':id', id);

                $('#editForm').attr('action', updateUrl);
                $('#edit_name').val($(this).data('name') || '');
                $('#edit_nisn').val($(this).data('nisn') || '');
                $('#edit_nis').val($(this).data('nis') || '');
                $('#edit_origin_class').val($(this).data('origin_class') || '');
                $('#edit_is_active').prop('checked', Number($(this).data('is_active')) === 1);

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