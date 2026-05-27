@extends('layouts.admin')

@section('title', 'Hasil Tes Lengkap')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Hasil Tes Lengkap</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pantau hasil tes, rekomendasi, selfie, biodata, dan distribusi kelas siswa.
            </p>
        </div>

        <a href="{{ route('admin.test-results.export') }}"
            class="inline-flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2.5 rounded-xl text-sm font-extrabold transition">
            <i class="fa-solid fa-download"></i>
            Export
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-[24px] shadow-sm overflow-hidden p-4">
        <div class="overflow-x-auto">
            <table id="testResultsTable" class="w-full text-xs whitespace-nowrap">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-slate-500">
                        <th class="px-3 py-3 text-left font-bold">Siswa</th>
                        <th class="px-3 py-3 text-left font-bold">Foto</th>
                        <th class="px-3 py-3 text-left font-bold">Nilai</th>
                        <th class="px-3 py-3 text-left font-bold">Rekomendasi</th>
                        <th class="px-3 py-3 text-left font-bold">Final</th>
                        <th class="px-3 py-3 text-left font-bold">Kelas</th>
                        <th class="px-3 py-3 text-left font-bold">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="bg-white w-full max-w-5xl rounded-[28px] shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Detail Hasil Tes</h2>
                    <p class="text-xs text-slate-500">Informasi lengkap siswa dan distribusi kelas.</p>
                </div>

                <button type="button" onclick="closeDetailModal()"
                    class="w-10 h-10 rounded-xl hover:bg-slate-100 text-slate-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div id="detailContent" class="p-5 max-h-[78vh] overflow-y-auto"></div>
        </div>
    </div>

    @php
        $packageOptionsData = $packages->map(function ($package) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'code' => $package->code,
            ];
        })->values();

        $classGroupOptionsData = $classGroups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'package_code' => optional($group->package)->code,
            ];
        })->values();
    @endphp

@endsection

@push('scripts')

    <script>
        const packages = @json($packageOptionsData);
        const classGroups = @json($classGroupOptionsData);

        $(document).ready(function () {
            $('#testResultsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                pageLength: 25,
                dom: '<"dt-top"l f>rt<"dt-bottom"i p>',
                ajax: '{{ route('admin.test-results.data') }}',
                columns: [
                    { data: 'student_info', name: 'test_results.id', orderable: false },
                    { data: 'foto', name: 'foto', orderable: false, searchable: false },
                    { data: 'nilai', name: 'test_results.academic_score' },
                    { data: 'rekomendasi', name: 'rekomendasi', orderable: false, searchable: false },
                    { data: 'final', name: 'final', orderable: false, searchable: false },
                    { data: 'kelas', name: 'kelas', orderable: false, searchable: false },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Cari nama siswa / NISN...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: {
                        previous: '<',
                        next: '>'
                    }
                }
            });
        });

        function escapeHtml(value) {
            if (value === null || value === undefined || value === '') return '-';

            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function packageOptions(selectedId) {
            return packages.map(packageItem => {
                const selected = String(packageItem.id) === String(selectedId) ? 'selected' : '';

                return `
                                                    <option value="${packageItem.id}" ${selected}>
                                                        ${escapeHtml(packageItem.name)}
                                                    </option>
                                                `;
            }).join('');
        }

        function classGroupOptions(selectedId) {
            return classGroups.map(group => {
                const selected = String(group.id) === String(selectedId) ? 'selected' : '';

                return `
                                                    <option value="${group.id}" ${selected}>
                                                        ${escapeHtml(group.name)} - ${escapeHtml(group.package_code)}
                                                    </option>
                                                `;
            }).join('');
        }

        function psychologyBadges(scores) {
            if (!scores || Object.keys(scores).length === 0) {
                return `<span class="text-slate-400 text-xs">Tidak ada data psikotes.</span>`;
            }

            return Object.entries(scores).map(([code, score]) => `
                                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">
                                                    ${escapeHtml(code)}: ${escapeHtml(score)}
                                                </span>
                                            `).join('');
        }

        function openDetailModal(data) {
            document.getElementById('detailContent').innerHTML = `
                                                <div class="grid lg:grid-cols-3 gap-5">
                                                    <div>
                                                        ${data.selfie
                    ? `
                                                                <a href="${data.selfie}" target="_blank">
                                                                    <img src="${data.selfie}"
                                                                        class="w-full max-h-[360px] object-cover rounded-3xl border border-slate-200 shadow-sm">
                                                                </a>
                                                                <div class="text-xs text-slate-500 mt-2">
                                                                    Selfie: ${escapeHtml(data.selfie_date)}
                                                                </div>
                                                            `
                    : `
                                                                <div class="aspect-square rounded-3xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                                    <i class="fa-solid fa-user text-5xl"></i>
                                                                </div>
                                                            `
                }
                                                    </div>

                                                    <div class="lg:col-span-2 space-y-5">
                                                        <div>
                                                            <h3 class="text-2xl font-extrabold text-slate-900">
                                                                ${escapeHtml(data.name)}
                                                            </h3>

                                                            <div class="flex flex-wrap gap-2 mt-2">
                                                                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                                                    ${escapeHtml(data.status)}
                                                                </span>

                                                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                                                                    ${escapeHtml(data.origin_class)}
                                                                </span>

                                                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                                                                    NISN ${escapeHtml(data.nisn)}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="grid md:grid-cols-2 gap-3">
                                                            <div class="bg-slate-50 rounded-2xl p-4">
                                                                <div class="text-xs font-bold text-slate-500 mb-2">Biodata</div>
                                                                <div class="space-y-1 text-sm text-slate-700">
                                                                    <div><b>TTL:</b> ${escapeHtml(data.birth_place)}, ${escapeHtml(data.birth_date)}</div>
                                                                    <div><b>Jenis Kelamin:</b> ${escapeHtml(data.gender)}</div>
                                                                    <div><b>HP:</b> ${escapeHtml(data.phone)}</div>
                                                                </div>
                                                            </div>

                                                            <div class="bg-slate-50 rounded-2xl p-4">
                                                                <div class="text-xs font-bold text-slate-500 mb-2">Orang Tua</div>
                                                                <div class="space-y-1 text-sm text-slate-700">
                                                                    <div><b>Ayah:</b> ${escapeHtml(data.father_name)}</div>
                                                                    <div><b>Ibu:</b> ${escapeHtml(data.mother_name)}</div>
                                                                    <div><b>HP Ortu:</b> ${escapeHtml(data.parent_phone)}</div>
                                                                </div>
                                                            </div>

                                                            <div class="bg-blue-50 rounded-2xl p-4">
                                                                <div class="text-xs font-bold text-blue-500 mb-2">Nilai Akademik</div>
                                                                <div class="text-4xl font-extrabold text-blue-700">
                                                                    ${escapeHtml(data.academic_score ?? 0)}
                                                                </div>
                                                            </div>

                                                            <div class="bg-slate-50 rounded-2xl p-4">
                                                                <div class="text-xs font-bold text-slate-500 mb-2">Distribusi</div>
                                                                <div class="space-y-1 text-sm text-slate-700">
                                                                    <div><b>Rekomendasi:</b> ${escapeHtml(data.recommended)}</div>
                                                                    <div><b>Final:</b> ${escapeHtml(data.final)}</div>
                                                                    <div><b>Kelas:</b> ${escapeHtml(data.class_name)}</div>
                                                                    <div><b>Tipe:</b> ${escapeHtml(data.distribution_type)}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="bg-slate-50 rounded-2xl p-4">
                                                            <div class="text-xs font-bold text-slate-500 mb-3">Pilihan Awal Jurusan</div>
                                                            <div class="flex flex-wrap gap-2">
                                                                <div class="px-3 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">
                                                                    1. ${escapeHtml(data.first_choice)}
                                                                </div>
                                                                <div class="px-3 py-2 rounded-xl bg-slate-200 text-slate-700 text-sm font-bold">
                                                                    2. ${escapeHtml(data.second_choice)}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="bg-slate-50 rounded-2xl p-4">
                                                            <div class="text-xs font-bold text-slate-500 mb-3">Skor Psikotes</div>
                                                            <div class="flex flex-wrap gap-2">
                                                                ${psychologyBadges(data.psychology_scores)}
                                                            </div>
                                                        </div>

                                                        <form method="POST"
                                                            action="{{ route('admin.test-results.manual-update') }}"
                                                            class="bg-white border border-slate-200 rounded-2xl p-4">
                                                            @csrf

                                                            <input type="hidden" name="student_id" value="${escapeHtml(data.student_id)}">

                                                            <div class="grid md:grid-cols-3 gap-3">
                                                                <div>
                                                                    <label class="block text-xs font-bold text-slate-500 mb-1">Final Jurusan</label>
                                                                    <select name="final_package_id"
                                                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">
                                                                        ${packageOptions(data.final_package_id)}
                                                                    </select>
                                                                </div>

                                                                <div>
                                                                    <label class="block text-xs font-bold text-slate-500 mb-1">Kelas</label>
                                                                    <select name="class_group_id"
                                                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">
                                                                        ${classGroupOptions(data.class_group_id)}
                                                                    </select>
                                                                </div>

                                                                <div class="flex items-end">
                                                                    <button class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-extrabold transition">
                                                                        <i class="fa-solid fa-save"></i>
                                                                        Simpan Edit
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            `;

            document.getElementById('detailModal').classList.remove('hidden');
            document.getElementById('detailModal').classList.add('flex');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
            document.getElementById('detailModal').classList.remove('flex');
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDetailModal();
            }
        });

        document.getElementById('detailModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeDetailModal();
            }
        });
    </script>
@endpush