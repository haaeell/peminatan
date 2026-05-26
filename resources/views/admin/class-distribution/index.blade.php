@extends('layouts.admin')

@section('title', 'Distribusi Kelas')

@section('content')
    <div class="flex flex-wrap gap-3 mb-6">
        <form method="POST" action="{{ route('admin.class-distribution.run') }}">
            @csrf
            <button class="bg-blue-600 px-5 py-3 rounded-xl font-bold">
                Jalankan Auto Distribusi
            </button>
        </form>

        <form method="POST" action="{{ route('admin.class-distribution.lock') }}">
            @csrf
            <button onclick="return confirm('Kunci data final?')" class="bg-red-600 px-5 py-3 rounded-xl font-bold">
                Lock Final
            </button>
        </form>
    </div>

    <div class="grid md:grid-cols-3 gap-5">
        @foreach($classGroups as $group)
            <div class="bg-white/10 border border-white/10 rounded-2xl p-5">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-bold">{{ $group->name }}</h2>
                        <p class="text-sm text-slate-400">{{ $group->package->name }}</p>
                    </div>

                    <span class="text-sm bg-slate-800 px-3 py-1 rounded-full">
                        {{ $group->students->count() }}/{{ $group->capacity }}
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($group->students as $item)
                        <div class="bg-slate-900 rounded-xl p-3">
                            <div class="font-bold">{{ $item->student->name }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $item->student->origin_class }}
                                @if($item->is_manual_override)
                                    · Manual
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <form class="manualMoveForm mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="class_group_id" value="{{ $group->id }}">

                    <select name="student_id" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700">
                        <option value="">Pindahkan siswa ke sini</option>
                        @foreach($unassignedStudents as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->name }} - {{ $student->origin_class }}
                            </option>
                        @endforeach
                    </select>

                    <button class="w-full bg-green-600 py-3 rounded-xl font-bold">
                        Pindahkan
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $('.manualMoveForm').on('submit', function (e) {
            e.preventDefault();

            $.post('{{ route("admin.class-distribution.manual-move") }}', $(this).serialize())
                .done(function (res) {
                    Swal.fire('Berhasil', res.message, 'success')
                        .then(() => location.reload());
                })
                .fail(function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON.message ?? 'Gagal memindahkan siswa.', 'error');
                });
        });
    </script>
@endpush