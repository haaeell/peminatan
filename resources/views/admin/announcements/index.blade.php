@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
    <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
            <h2 class="font-bold mb-4">Buat Pengumuman</h2>

            <form method="POST" action="{{ route('admin.announcements.store') }}" class="space-y-3">
                @csrf

                <select name="type" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700">
                    <option value="temporary">Sementara</option>
                    <option value="final">Final</option>
                </select>

                <input name="title" placeholder="Judul" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700">

                <textarea name="content" rows="5" placeholder="Isi pengumuman"
                    class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700"></textarea>

                <button class="w-full bg-blue-600 py-3 rounded-xl font-bold">
                    Simpan
                </button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white/10 border border-white/10 rounded-2xl p-5">
            <table class="datatable w-full">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Publish</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($announcements as $announcement)
                        <tr>
                            <td>{{ $announcement->title }}</td>
                            <td>{{ $announcement->type }}</td>
                            <td>{{ $announcement->is_published ? 'Published' : 'Draft' }}</td>
                            <td>{{ $announcement->published_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="flex gap-2">
                                @if(!$announcement->is_published)
                                    <form method="POST" action="{{ route('admin.announcements.publish', $announcement) }}">
                                        @csrf
                                        <button class="text-green-400">
                                            Publish
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-400" onclick="return confirm('Hapus pengumuman?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">{{ $announcements->links() }}</div>
        </div>
    </div>
@endsection