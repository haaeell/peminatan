@extends('layouts.admin')

@section('title', 'Keberatan Siswa')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Keberatan Siswa</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pengajuan keberatan, sesuaikan jurusan final, dan tempatkan siswa ke kelas yang sesuai.
            </p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-[24px] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="datatable w-full text-sm" data-page-length="-1">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-slate-500">
                        <th class="text-left text-xs font-extrabold uppercase whitespace-nowrap">Siswa</th>
                        <th class="text-left text-xs font-extrabold uppercase whitespace-nowrap">Kelas Asal</th>
                        <th class="text-left text-xs font-extrabold uppercase">Alasan & Hasil Saat Ini</th>
                        <th class="text-left text-xs font-extrabold uppercase whitespace-nowrap">Status</th>
                        <th class="text-left text-xs font-extrabold uppercase whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($objections as $objection)
                        <tr class="hover:bg-slate-50 align-top transition">
                            <td class="min-w-[180px] whitespace-nowrap">
                                <div class="font-extrabold text-slate-900">
                                    {{ $objection->student?->name ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    NISN: {{ $objection->student?->nisn ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                                    {{ $objection->student?->origin_class ?? '-' }}
                                </span>
                            </td>

                            <td class="min-w-[320px] max-w-[460px] whitespace-normal align-top">
                                <p class="max-w-[460px] text-sm text-slate-700 leading-relaxed break-words [overflow-wrap:anywhere]">
                                    {{ $objection->reason }}
                                </p>

                                <div class="mt-3 grid gap-2 text-xs">
                                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 break-words [overflow-wrap:anywhere]">
                                        <span class="font-bold text-slate-500">Rekomendasi:</span>
                                        <span class="font-extrabold text-slate-800">{{ $objection->student?->result?->recommendedPackage?->name ?? '-' }}</span>
                                    </div>

                                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 break-words [overflow-wrap:anywhere]">
                                        <span class="font-bold text-slate-500">Final saat ini:</span>
                                        <span class="font-extrabold text-slate-800">{{ $objection->student?->result?->finalPackage?->name ?? '-' }}</span>
                                    </div>

                                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 break-words [overflow-wrap:anywhere]">
                                        <span class="font-bold text-slate-500">Kelas saat ini:</span>
                                        <span class="font-extrabold text-slate-800">{{ $objection->student?->classStudent?->classGroup?->name ?? '-' }}</span>
                                    </div>

                                    <div class="rounded-xl bg-blue-50 border border-blue-100 px-3 py-2 text-blue-700 break-words [overflow-wrap:anywhere]">
                                        <span class="font-bold">Pilihan siswa:</span>
                                        {{ $objection->student?->packageChoice?->firstPackage?->name ?? '-' }}
                                        /
                                        {{ $objection->student?->packageChoice?->secondPackage?->name ?? '-' }}
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($objection->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-yellow-50 text-yellow-700 text-xs font-extrabold">
                                        <i class="fa-solid fa-clock"></i>
                                        Pending
                                    </span>
                                @elseif($objection->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-green-50 text-green-700 text-xs font-extrabold">
                                        <i class="fa-solid fa-check"></i>
                                        Disetujui
                                    </span>
                                @elseif($objection->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-700 text-xs font-extrabold">
                                        <i class="fa-solid fa-xmark"></i>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-extrabold">
                                        {{ ucfirst($objection->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="min-w-[420px] align-top">
                                @if($objection->status === 'pending')
                                    <div class="grid gap-3">
                                        <form method="POST" action="{{ route('admin.objections.approve', $objection) }}"
                                            class="objectionApproveForm grid gap-2 rounded-2xl border border-green-100 bg-green-50 p-3">
                                            @csrf

                                            <div class="grid sm:grid-cols-2 gap-2">
                                                <select name="final_package_id"
                                                    class="approvalPackageSelect w-full px-3 py-2 rounded-xl border border-green-100 bg-white text-sm text-slate-700 focus:border-green-500 focus:ring-4 focus:ring-green-100 outline-none transition"
                                                    required>
                                                    <option value="">Pilih jurusan final</option>
                                                    @foreach($packages as $package)
                                                        <option value="{{ $package->id }}"
                                                            {{ (int) ($objection->student?->result?->final_package_id ?? $objection->student?->result?->recommended_package_id) === (int) $package->id ? 'selected' : '' }}>
                                                            {{ $package->name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <select name="class_group_id"
                                                    class="approvalClassSelect w-full px-3 py-2 rounded-xl border border-green-100 bg-white text-sm text-slate-700 focus:border-green-500 focus:ring-4 focus:ring-green-100 outline-none transition"
                                                    data-current-class="{{ $objection->student?->classStudent?->class_group_id }}"
                                                    required>
                                                    <option value="">Pilih kelas tujuan</option>
                                                    @foreach($classGroups as $group)
                                                        <option value="{{ $group->id }}"
                                                            data-package-id="{{ $group->package_id }}"
                                                            {{ (int) $objection->student?->classStudent?->class_group_id === (int) $group->id ? 'selected' : '' }}>
                                                            {{ $group->name }} - {{ $group->package?->code ?? $group->package?->name }}
                                                            ({{ $group->students_count }}/{{ $group->capacity }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <input name="admin_note" placeholder="Catatan persetujuan..."
                                                class="w-full px-3 py-2 rounded-xl border border-green-100 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-100 outline-none transition">

                                            <button
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-extrabold transition">
                                                <i class="fa-solid fa-check"></i>
                                                Setujui & Pindahkan
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.objections.reject', $objection) }}"
                                            class="flex flex-col sm:flex-row gap-2">
                                            @csrf

                                            <input name="admin_note" placeholder="Catatan penolakan..."
                                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-100 outline-none transition">

                                            <button
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold transition">
                                                <i class="fa-solid fa-xmark"></i>
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        <span class="inline-flex items-center gap-2 text-slate-400 text-sm font-bold">
                                            <i class="fa-solid fa-lock"></i>
                                            Selesai
                                        </span>

                                        @if($objection->admin_note)
                                            <div class="rounded-2xl bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-600 whitespace-normal break-words [overflow-wrap:anywhere]">
                                                <span class="font-bold">Catatan admin:</span>
                                                {{ $objection->admin_note }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="mx-auto w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-message text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-extrabold text-slate-900">Belum ada keberatan</h3>
                                <p class="text-sm text-slate-500 mt-1">Pengajuan keberatan siswa akan muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function syncApprovalClassOptions(form) {
            const packageId = form.find('.approvalPackageSelect').val();
            const classSelect = form.find('.approvalClassSelect');
            const currentValue = classSelect.val();

            classSelect.find('option').each(function () {
                const option = $(this);
                const optionPackageId = String(option.data('package-id') || '');
                const shouldShow = option.val() === '' || !packageId || optionPackageId === String(packageId);

                option.prop('hidden', !shouldShow);
                option.prop('disabled', !shouldShow);
            });

            if (currentValue && classSelect.find(`option[value="${currentValue}"]:not(:disabled)`).length === 0) {
                classSelect.val('');
            }
        }

        $('.objectionApproveForm').each(function () {
            syncApprovalClassOptions($(this));
        });

        $('.approvalPackageSelect').on('change', function () {
            syncApprovalClassOptions($(this).closest('.objectionApproveForm'));
        });
    </script>
@endpush
