@extends('layouts.admin')

@section('title', 'Keberatan Siswa')

@section('content')
    <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
        <table class="datatable w-full">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Kelas Asal</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Catatan Admin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($objections as $objection)
                    <tr>
                        <td>{{ $objection->student->name }}</td>
                        <td>{{ $objection->student->origin_class }}</td>
                        <td>{{ $objection->reason }}</td>
                        <td>{{ $objection->status }}</td>
                        <td>{{ $objection->admin_note ?? '-' }}</td>
                        <td>
                            @if($objection->status === 'pending')
                                <div class="space-y-2">
                                    <form method="POST" action="{{ route('admin.objections.approve', $objection) }}">
                                        @csrf
                                        <input name="admin_note" placeholder="Catatan"
                                            class="w-full p-2 rounded bg-slate-900 border border-slate-700 mb-2">
                                        <button class="text-green-400">Setujui</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.objections.reject', $objection) }}">
                                        @csrf
                                        <input name="admin_note" placeholder="Catatan"
                                            class="w-full p-2 rounded bg-slate-900 border border-slate-700 mb-2">
                                        <button class="text-red-400">Tolak</button>
                                    </form>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $objections->links() }}</div>
    </div>
@endsection