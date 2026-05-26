@extends('layouts.admin')

@section('title', 'Pelanggaran CBT')

@section('content')
    <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
        <table class="datatable w-full">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Jenis Tes</th>
                    <th>Aksi</th>
                    <th>Jumlah</th>
                    <th>IP</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($violations as $violation)
                    <tr>
                        <td>{{ $violation->student?->name }}</td>
                        <td>{{ $violation->student?->origin_class }}</td>
                        <td>{{ $violation->exam_type }}</td>
                        <td>{{ $violation->action }}</td>
                        <td>{{ $violation->violation_count }}</td>
                        <td>{{ $violation->ip_address }}</td>
                        <td>{{ $violation->occurred_at?->format('d M Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $violations->links() }}</div>
    </div>
@endsection