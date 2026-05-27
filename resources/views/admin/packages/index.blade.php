@extends('layouts.admin')

@section('title', 'Paket Jurusan')@section('content')

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                Paket Jurusan
            </h1>

            <p class="text-slate-500 mt-2">
                Kelola kelompok jurusan dan mata pelajaran paket.
            </p>
        </div>

        <button type="button" onclick="openCreateModal()"
            class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-bold shadow-lg shadow-blue-200 transition">
            <i class="fa-solid fa-plus"></i>
            Tambah Paket
        </button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border border-slate-200 rounded-[30px] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            Paket
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            Mata Pelajaran
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            Kapasitas
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($packages as $package)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-5">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-2xl border border-slate-200 shadow-sm"
                                        style="background: {{ $package->color }}">
                                    </div>

                                    <div>
                                        <div class="font-extrabold text-slate-900">
                                            {{ $package->name }}
                                        </div>

                                        <div class="text-xs text-slate-500 mt-1">
                                            Kode: {{ $package->code }}
                                        </div>

                                        @if ($package->description)
                                            <div class="text-xs text-slate-400 mt-1 max-w-md">
                                                {{ $package->description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-2 max-w-lg">
                                    @forelse($package->subjects as $subject)
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-bold">
                                            {{ $subject->subject_name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-sm">
                                            Belum ada mapel
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <div
                                    class="inline-flex items-center px-3 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold">
                                    {{ $package->capacity }} siswa
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                @if ($package->is_active)
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-green-50 text-green-700 text-xs font-extrabold">
                                        <i class="fa-solid fa-check"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-red-50 text-red-700 text-xs font-extrabold">
                                        <i class="fa-solid fa-xmark"></i>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex items-center justify-center gap-2">

                                    <button type="button"
                                        class="editBtn group w-11 h-11 inline-flex items-center justify-center rounded-2xl bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-600 hover:text-white hover:border-blue-600 shadow-sm hover:shadow-lg hover:shadow-blue-200 transition-all duration-300"
                                        data-id="{{ $package->id }}" data-code="{{ e($package->code) }}"
                                        data-name="{{ e($package->name) }}" data-description="{{ e($package->description) }}"
                                        data-capacity="{{ $package->capacity }}" data-color="{{ $package->color }}"
                                        data-is_active="{{ $package->is_active ? 1 : 0 }}"
                                        data-subjects='@json($package->subjects->sortBy('order')->pluck('subject_name')->values())'>

                                        <i
                                            class="fa-solid fa-pen-to-square text-sm group-hover:scale-110 transition-transform"></i>
                                    </button>

                                    <form method="POST" action="{{ route('admin.packages.destroy', $package) }}"
                                        onsubmit="return confirm('Hapus paket ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="group w-11 h-11 inline-flex items-center justify-center rounded-2xl bg-red-50 text-red-700 border border-red-100 hover:bg-red-600 hover:text-white hover:border-red-600 shadow-sm hover:shadow-lg hover:shadow-red-200 transition-all duration-300">

                                            <i class="fa-solid fa-trash text-sm group-hover:scale-110 transition-transform"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div
                                    class="mx-auto w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5">
                                    <i class="fa-solid fa-layer-group text-2xl"></i>
                                </div>

                                <h3 class="text-xl font-extrabold text-slate-900">
                                    Belum ada paket jurusan
                                </h3>

                                <p class="text-slate-500 mt-2">
                                    Tambahkan paket untuk mulai distribusi kelas.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100">
            {{ $packages->links() }}
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div id="createModal" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">

        <div class="bg-white rounded-[32px] w-full max-w-4xl shadow-2xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">
                        Tambah Paket
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Tambahkan kelompok jurusan beserta mata pelajaran.
                    </p>
                </div>

                <button type="button" onclick="closeCreateModal()"
                    class="w-11 h-11 rounded-2xl hover:bg-slate-100 text-slate-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.packages.store') }}" class="p-6 space-y-6">

                @csrf

                <div class="grid md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Kode
                        </label>

                        <input name="code" placeholder="A"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Paket
                        </label>

                        <input name="name" placeholder="Kelompok A"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Kapasitas
                        </label>

                        <input name="capacity" type="number" value="72"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Warna
                        </label>

                        <input name="color" type="color" value="#2563eb"
                            class="w-full h-[52px] rounded-2xl border border-slate-200 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Deskripsi
                    </label>

                    <textarea name="description" rows="3"
                        class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-bold text-slate-700">
                            Mata Pelajaran
                        </label>

                        <button type="button" onclick="addSubjectField('subjectWrapper')"
                            class="text-sm font-bold text-blue-600 hover:text-blue-700">
                            + Tambah Mapel
                        </button>
                    </div>

                    <div id="subjectWrapper" class="grid md:grid-cols-2 gap-3">

                        <div class="relative">
                            <input name="subjects[]" placeholder="Contoh: Fisika"
                                class="w-full pr-12 px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200">

                            <button type="button" onclick="removeSubjectField(this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 hover:text-red-700">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="relative">
                            <input name="subjects[]" placeholder="Contoh: Kimia"
                                class="w-full pr-12 px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200">

                            <button type="button" onclick="removeSubjectField(this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 hover:text-red-700">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                    </div>
                </div>

                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked
                        class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                    Paket aktif
                </label>

                <button
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition">
                    <i class="fa-solid fa-save"></i>
                    Simpan Paket
                </button>

            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div id="editModal" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">

        <div class="bg-white rounded-[32px] w-full max-w-4xl shadow-2xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">
                        Edit Paket
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Perbarui data paket dan mata pelajaran.
                    </p>
                </div>

                <button type="button" onclick="closeEditModal()"
                    class="w-11 h-11 rounded-2xl hover:bg-slate-100 text-slate-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editForm" method="POST" class="p-6 space-y-6">

                @csrf
                @method('PUT')

                <div class="grid md:grid-cols-5 gap-4">
                    <input id="edit_code" name="code" class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200">

                    <input id="edit_name" name="name"
                        class="md:col-span-2 px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200">

                    <input id="edit_capacity" name="capacity" type="number"
                        class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200">

                    <input id="edit_color" name="color" type="color" class="h-[52px] rounded-2xl border border-slate-200">
                </div>

                <textarea id="edit_description" name="description" rows="3"
                    class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200"></textarea>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-bold text-slate-700">
                            Mata Pelajaran
                        </label>

                        <button type="button" onclick="addSubjectField('editSubjectWrapper')"
                            class="text-sm font-bold text-blue-600 hover:text-blue-700">
                            + Tambah Mapel
                        </button>
                    </div>

                    <div id="editSubjectWrapper" class="grid md:grid-cols-2 gap-3"></div>
                </div>

                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                    <input id="edit_is_active" type="checkbox" name="is_active" value="1">

                    Paket aktif
                </label>

                <button
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition">
                    <i class="fa-solid fa-save"></i>
                    Update Paket
                </button>

            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
            document.getElementById('createModal').classList.add('flex');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
            document.getElementById('createModal').classList.remove('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        function escapeAttr(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;');
        }

        function addSubjectField(wrapperId) {

            document.getElementById(wrapperId)
                .insertAdjacentHTML(
                    'beforeend',

                    `
                <div class="relative">

                    <input name="subjects[]"
                        placeholder="Nama mata pelajaran"
                        class="w-full pr-12 px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200
                        focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                    <button type="button"
                        onclick="removeSubjectField(this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 hover:text-red-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                </div>
                `
                );
        }

        function removeSubjectField(button) {

            const wrapper = button.closest('.relative');

            wrapper.remove();
        }

        $('.editBtn').on('click', function () {

            const id = $(this).data('id');
            const code = $(this).data('code');
            const name = $(this).data('name');
            const description = $(this).data('description');
            const capacity = $(this).data('capacity');
            const color = $(this).data('color');
            const isActive = $(this).data('is_active');
            const subjects = $(this).data('subjects') || [];

            const url = "{{ route('admin.packages.update', ':id') }}"
                .replace(':id', id);

            $('#editForm').attr('action', url);

            $('#edit_code').val(code);
            $('#edit_name').val(name);
            $('#edit_description').val(description);
            $('#edit_capacity').val(capacity);
            $('#edit_color').val(color);
            $('#edit_is_active').prop('checked', isActive == 1);

            $('#editSubjectWrapper').html('');

            subjects.forEach(function (subject) {
                $('#editSubjectWrapper').append(`
        <div class="relative">

            <input name="subjects[]"
                value="${escapeAttr(subject)}"
                class="w-full pr-12 px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200
                focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

            <button type="button"
                onclick="removeSubjectField(this)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 hover:text-red-700">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>
    `);
            });

            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        });
    </script>
@endpush