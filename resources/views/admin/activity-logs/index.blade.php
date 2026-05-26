@extends('layouts.admin')

@section('title', 'Audit Log')

@section('content')
    <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
        <table class="datatable w-full">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>IP</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td>{{ $log->module }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
@endsection