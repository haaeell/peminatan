@extends('layouts.admin')

@section('title', 'Keberatan Siswa')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Keberatan Siswa</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pengajuan keberatan siswa dan berikan keputusan admin.
            </p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-[24px] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="datatable w-full text-sm whitespace-nowrap">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-slate-500">
                        <th class="text-left text-xs font-extrabold uppercase">Siswa</th>
                        <th class="text-left text-xs font-extrabold uppercase">Kelas Asal</th>
                        <th class="text-left text-xs font-extrabold uppercase">Alasan</th>
                        <th class="text-left text-xs font-extrabold uppercase">Status</th>
                        {{-- <th class="text-left text-xs font-extrabold uppercase">Catatan Admin</th> --}}
                        <th class="text-left text-xs font-extrabold uppercase">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($objections as $objection)
                        <tr class="hover:bg-slate-50 align-top transition">
                            <td class="min-w-[180px]">
                                <div class="font-extrabold text-slate-900">
                                    {{ $objection->student?->name ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    NISN: {{ $objection->student?->nisn ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                                    {{ $objection->student?->origin_class ?? '-' }}
                                </span>
                            </td>

                            <td class="min-w-[280px] max-w-[420px] whitespace-normal">
                                <p class="text-sm text-slate-700 leading-relaxed">
                                    {{ $objection->reason }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
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
{{-- 
                            <td class="min-w-[220px] max-w-[320px] whitespace-normal">
                                @if($objection->admin_note)
                                    <p class="text-sm text-slate-700 leading-relaxed">
                                        {{ $objection->admin_note }}
                                    </p>
                                @else
                                    <span class="text-sm text-slate-400 font-semibold">Belum ada catatan</span>
                                @endif
                            </td> --}}

                            <td class="min-w-[300px]">
                                @if($objection->status === 'pending')
                                    <div class="grid gap-3">
                                        <form method="POST" action="{{ route('admin.objections.approve', $objection) }}"
                                            class="flex flex-col sm:flex-row gap-2">
                                            @csrf

                                            <input name="admin_note" placeholder="Catatan persetujuan..."
                                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:border-green-500 focus:ring-4 focus:ring-green-100 outline-none transition">

                                            <button
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-extrabold transition">
                                                <i class="fa-solid fa-check"></i>
                                                Setujui
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
                                    <span class="inline-flex items-center gap-2 text-slate-400 text-sm font-bold">
                                        <i class="fa-solid fa-lock"></i>
                                        Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
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