@extends('layouts.admin')

@section('title', 'Jurusan')

@section('content')

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                Jurusan
            </h1>

            <p class="text-slate-500 mt-2">
                Kelola data jurusan, kapasitas, dan mata pelajaran.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-2xl font-bold">
            <i class="fa-solid fa-layer-group"></i>
            Total: {{ $packages->count() }} jurusan
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm mb-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-plus"></i>
            </div>

            <div>
                <h2 class="text-xl font-extrabold text-slate-900">
                    Tambah Jurusan
                </h2>

                <p class="text-sm text-slate-500">
                    Tambahkan jurusan baru ke sistem.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-bold mb-2">Periksa kembali input:</p>

                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.packages.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            @csrf

            {{-- Kode --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Kode
                </label>

                <input name="code" value="{{ old('code') }}" placeholder="IPA"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                                focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
            </div>

            {{-- Nama --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Nama Jurusan
                </label>

                <input name="name" value="{{ old('name') }}" placeholder="Ilmu Pengetahuan Alam"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                                focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
            </div>

            {{-- Kapasitas --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Kapasitas
                </label>

                <input name="capacity" type="number" value="{{ old('capacity') }}" placeholder="40"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800
                                focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
            </div>

            {{-- Color --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Warna
                </label>

                <input name="color" type="color" value="{{ old('color', '#2563eb') }}"
                    class="w-full h-[52px] rounded-2xl border border-slate-200 bg-white cursor-pointer">
            </div>

            {{-- Submit --}}
            <div class="flex items-end">
                <button
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                                text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition-all duration-300">
                    <i class="fa-solid fa-save"></i>
                    Tambah
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-[30px] p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">
                    Daftar Jurusan
                </h2>

                <p class="text-sm text-slate-500">
                    Data jurusan dan mata pelajaran terkait.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="datatable w-full text-sm">
                <thead>
                    <tr class="text-slate-600">
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Mapel</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($packages as $package)
                        <tr class="align-top">

                            {{-- Kode --}}
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl shadow-sm"
                                        style="background: {{ $package->color ?? '#2563eb' }}">
                                    </div>

                                    <div>
                                        <div class="font-extrabold text-slate-900">
                                            {{ $package->code }}
                                        </div>

                                        <div class="text-xs text-slate-400">
                                            ID: #{{ $package->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Nama --}}
                            <td>
                                <div class="font-bold text-slate-800">
                                    {{ $package->name }}
                                </div>
                            </td>


                            {{-- Subjects --}}
                            <td>
                                <div class="flex flex-wrap gap-2 max-w-sm">
                                    @forelse($package->subjects as $subject)
                                        <span
                                            class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-xl text-xs font-bold border border-blue-100">

                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>

                                            {{ $subject->subject_name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-sm">
                                            Belum ada mapel
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- Action --}}
                            <td>
                                <div class="flex items-center justify-center gap-2">

                                    {{-- Edit --}}
                                    <button type="button" class="editBtn group w-10 h-10 inline-flex items-center justify-center rounded-2xl
                                bg-blue-50 text-blue-700 border border-blue-100
                                hover:bg-blue-600 hover:text-white hover:border-blue-600
                                shadow-sm hover:shadow-lg hover:shadow-blue-200
                                transition-all duration-300" data-id="{{ $package->id }}" data-code="{{ e($package->code) }}"
                                        data-name="{{ e($package->name) }}" data-capacity="{{ $package->capacity }}"
                                        data-color="{{ $package->color }}" data-is_active="{{ $package->is_active ? 1 : 0 }}"
                                        title="Edit jurusan">

                                        <i
                                            class="fa-solid fa-pen-to-square text-sm group-hover:scale-110 transition-transform"></i>
                                    </button>

                                    {{-- Delete --}}
                                    <form id="delete-package-{{ $package->id }}" method="POST"
                                        action="{{ route('admin.packages.destroy', $package) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="button" onclick="confirmDelete('delete-package-{{ $package->id }}')"
                                            class="group w-10 h-10 inline-flex items-center justify-center rounded-2xl
                                                                                bg-white text-slate-500 border border-slate-200
                                                                                hover:bg-blue-600 hover:text-white hover:border-blue-600
                                                                                shadow-sm hover:shadow-lg hover:shadow-blue-200
                                                                                transition-all duration-300"
                                            title="Hapus jurusan">

                                            <i
                                                class="fa-solid fa-trash-can text-sm group-hover:scale-110 transition-transform"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4">

        <div id="editModalPanel" class="bg-white border border-slate-200 rounded-[30px] p-6 w-full max-w-2xl shadow-2xl">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-6">

                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">
                        Edit Jurusan
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Perbarui data jurusan dan kapasitas.
                    </p>
                </div>

                <button type="button" id="closeModal"
                    class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="editForm" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid md:grid-cols-2 gap-5">

                    {{-- Code --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Kode
                        </label>

                        <input name="code" id="edit_code"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200
                                    focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    {{-- Capacity --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Kapasitas
                        </label>

                        <input name="capacity" id="edit_capacity" type="number"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200
                                    focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Nama Jurusan
                    </label>

                    <input name="name" id="edit_name"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200
                                focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                </div>

                {{-- Color --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Warna
                    </label>

                    <input name="color" id="edit_color" type="color"
                        class="w-full h-[52px] rounded-2xl border border-slate-200 bg-white cursor-pointer">
                </div>

                {{-- Status --}}
                <label class="flex items-center gap-3 p-4 rounded-2xl bg-blue-50 text-slate-700">
                    <input type="checkbox" name="is_active" value="1" id="edit_is_active"
                        class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                    <span class="font-semibold text-sm">
                        Jurusan aktif
                    </span>
                </label>

                {{-- Submit --}}
                <button class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                            text-white py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition-all duration-300">

                    <i class="fa-solid fa-save"></i>
                    Update Jurusan
                </button>

            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {

                const updateUrlTemplate =
                    "{{ route('admin.packages.update', ':id') }}";

                $('.editBtn').on('click', function () {

                    const id = $(this).data('id');
                    const updateUrl = updateUrlTemplate.replace(':id', id);

                    $('#editForm').attr('action', updateUrl);

                    $('#edit_code').val($(this).data('code'));
                    $('#edit_name').val($(this).data('name'));
                    $('#edit_capacity').val($(this).data('capacity'));
                    $('#edit_color').val($(this).data('color'));

                    $('#edit_is_active').prop(
                        'checked',
                        Number($(this).data('is_active')) === 1
                    );

                    $('#editModal')
                        .removeClass('hidden')
                        .addClass('flex');
                });

                function closeEditModal() {
                    $('#editModal')
                        .addClass('hidden')
                        .removeClass('flex');
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
@endsection