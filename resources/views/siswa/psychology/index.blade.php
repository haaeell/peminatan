@extends('layouts.siswa')

@section('content')
@include('siswa.partials.cbt-guard')

<div class="min-h-screen bg-slate-100 px-4 py-4 pb-32">

    {{-- Header --}}
        <div class="sticky top-4 z-30 bg-white/95 backdrop-blur-xl border border-slate-200 rounded-[28px] p-4 mb-5 shadow-sm">
            <div class="flex justify-between items-center gap-4">
                <div>
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-wide">CBT Online</p>
                    <h1 class="font-extrabold text-xl text-slate-900">Tes Psikologi</h1>
                    <p class="text-xs text-slate-500 mt-1">Tidak ada jawaban benar atau salah</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-xs font-semibold text-slate-500">Progress</div>
                        <div class="inline-flex items-center gap-2 mt-1 px-4 py-2 rounded-2xl bg-blue-50 text-blue-700">
                            <i class="fa-solid fa-chart-line"></i>
                            <span id="progressText" class="text-2xl font-extrabold">0%</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-xs font-semibold text-slate-500">Sisa Waktu</div>
                        <div class="inline-flex items-center gap-2 mt-1 px-4 py-2 rounded-2xl bg-blue-50 text-blue-700">
                            <i class="fa-solid fa-clock"></i>
                            <span id="timer" class="text-2xl font-extrabold">{{ str_pad((string) $cbtSettings['duration_minutes'], 2, '0', STR_PAD_LEFT) }}:00</span>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($cbtSettings['student_help_text']))
                <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                    <i class="fa-solid fa-circle-info mr-2"></i>{{ $cbtSettings['student_help_text'] }}
                </div>
            @endif

            <div class="mt-4 h-3 bg-slate-200 rounded-full overflow-hidden">
                <div id="progressBar"
                    class="h-3 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full transition-all duration-500"
                    style="width: 0%">
                </div>
            </div>
        </div>

    {{-- Question Navigation --}}
    <div class="bg-white border border-slate-200 rounded-[28px] p-4 mb-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="font-extrabold text-slate-900">Navigasi Pernyataan</h2>
                <p class="text-xs text-slate-500">Klik nomor untuk lompat ke pernyataan.</p>
            </div>
        </div>

        <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 gap-2">
            @foreach ($questions as $index => $question)
                <button type="button"
                    data-target="#question-{{ $question->id }}"
                    class="question-nav h-11 rounded-2xl text-sm font-extrabold transition-all
                    {{ isset($answers[$question->id])
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-200'
                        : 'bg-slate-100 text-slate-500 hover:bg-blue-50 hover:text-blue-700' }}">
                    {{ $index + 1 }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Questions --}}
    <div class="space-y-5">
        @foreach ($questions as $index => $question)
            <div id="question-{{ $question->id }}"
                class="question-card bg-white border border-slate-200 rounded-[30px] p-5 md:p-6 shadow-sm">

                <div class="flex items-start gap-4 mb-5">
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-extrabold shadow-lg shadow-blue-200">
                        {{ $index + 1 }}
                    </div>

                    <div>
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-1">
                            Pernyataan {{ $index + 1 }}
                        </p>

                        <h2 class="font-extrabold text-lg md:text-xl text-slate-900 leading-relaxed">
                            {{ $question->question }}
                        </h2>

                        @if($question->image_path)
                            <img src="{{ asset('storage/' . $question->image_path) }}" alt="Gambar pernyataan {{ $index + 1 }}"
                                class="mt-4 w-full max-w-2xl rounded-[24px] border border-slate-200 bg-slate-50 object-contain">
                        @endif
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($question->options as $option)
                        @php
                            $checked = ($answers[$question->id] ?? null) == $option->id;
                        @endphp

                        <label
                            class="answer-label group block rounded-[22px] border p-4 cursor-pointer transition-all duration-300
                            {{ $checked
                                ? 'bg-blue-50 border-blue-300 shadow-md shadow-blue-100'
                                : 'bg-slate-50 border-slate-200 hover:bg-blue-50 hover:border-blue-200' }}">

                            <div class="flex items-start gap-3">
                                <input type="radio"
                                    name="answer_{{ $question->id }}"
                                    value="{{ $option->id }}"
                                    data-question-id="{{ $question->id }}"
                                    class="answer-option mt-2 accent-blue-600"
                                    {{ $checked ? 'checked' : '' }}>

                                <div class="w-10 h-10 shrink-0 rounded-2xl bg-white border border-slate-200 text-blue-700 flex items-center justify-center font-extrabold group-hover:border-blue-300">
                                    {{ $option->label }}
                                </div>

                                <div class="pt-1">
                                    <div class="text-slate-800 font-semibold leading-relaxed">
                                        {{ $option->option_text }}
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Submit --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-xl border-t border-slate-200 p-4">
        <div class="max-w-5xl mx-auto">
            <button id="submitPsychologyBtn"
                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-extrabold shadow-lg shadow-blue-200 transition-all">
                <i class="fa-solid fa-paper-plane"></i>
                Selesaikan Tes
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const guard = new CBTGuard({
    examType: 'psychology',
    logUrl: '{{ route("siswa.violations.store") }}',
    submitUrl: '{{ route("siswa.psychology.submit") }}',
    csrf: '{{ csrf_token() }}',
    maxViolations: {{ (int) $cbtSettings['violation_limit'] }},
    initialViolationCount: {{ (int) $cbtSettings['initial_violation_count'] }},
    warningMessage: @json($cbtSettings['warning_message']),
    forceFullscreen: {{ $cbtSettings['force_fullscreen'] ? 'true' : 'false' }}
});

guard.init();

const totalQuestions = {{ $questions->count() }};
let remainingSeconds = {{ (int) $cbtSettings['remaining_seconds'] }};

function formatTime(totalSeconds) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
}

function updateProgress() {
    const answered = $('.answer-option:checked').length;
    const percent = totalQuestions > 0 ? Math.round((answered / totalQuestions) * 100) : 0;

    $('#progressText').text(percent + '%');
    $('#progressBar').css('width', percent + '%');
}

updateProgress();
$('#timer').text(formatTime(remainingSeconds));

const timerInterval = setInterval(() => {
    remainingSeconds--;
    $('#timer').text(formatTime(Math.max(remainingSeconds, 0)));

    if (remainingSeconds <= 10) {
        $('#timer').closest('div')
            .removeClass('bg-blue-50 text-blue-700')
            .addClass('bg-blue-600 text-white');
    }

    if (remainingSeconds <= 0) {
        clearInterval(timerInterval);

        Swal.fire({
            icon: 'info',
            title: 'Waktu Habis',
            text: 'Jawaban akan dikirim otomatis.',
            allowOutsideClick: false,
            showConfirmButton: false,
        });

        setTimeout(() => guard.submitExam(), 1200);
    }
}, 1000);

$('.answer-option').on('change', function () {
    const questionId = $(this).data('question-id');
    const optionId = $(this).val();

    guard.backupAnswer(questionId, optionId);

    $(`[name="answer_${questionId}"]`).closest('.answer-label')
        .removeClass('bg-blue-50 border-blue-300 shadow-md shadow-blue-100')
        .addClass('bg-slate-50 border-slate-200');

    $(this).closest('.answer-label')
        .removeClass('bg-slate-50 border-slate-200')
        .addClass('bg-blue-50 border-blue-300 shadow-md shadow-blue-100');

    $(`[data-target="#question-${questionId}"]`)
        .removeClass('bg-slate-100 text-slate-500 hover:bg-blue-50 hover:text-blue-700')
        .addClass('bg-blue-600 text-white shadow-lg shadow-blue-200');

    updateProgress();

    $.post('{{ route("siswa.psychology.autosave") }}', {
        _token: '{{ csrf_token() }}',
        psychology_question_id: questionId,
        psychology_question_option_id: optionId
    }).fail(function (xhr) {
        if (xhr.status === 423) {
            Swal.fire('Waktu Habis', 'Jawaban akan dikirim otomatis.', 'info')
                .then(() => guard.submitExam());
        }
    });
});

$('.question-nav').on('click', function () {
    const target = $(this).data('target');

    $('html, body').animate({
        scrollTop: $(target).offset().top - 130
    }, 300);
});

$('#submitPsychologyBtn').on('click', function () {
    const answered = $('.answer-option:checked').length;

    if (answered < totalQuestions) {
        Swal.fire({
            icon: 'warning',
            title: 'Belum Lengkap',
            text: 'Semua pernyataan wajib dijawab.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    Swal.fire({
        icon: 'question',
        title: 'Selesaikan Tes?',
        text: 'Jawaban akan dikirim dan tidak bisa diubah.',
        showCancelButton: true,
        confirmButtonText: 'Selesai',
        cancelButtonText: 'Cek Lagi',
        confirmButtonColor: '#2563eb'
    }).then(result => {
        if (result.isConfirmed) {
            guard.submitExam();
        }
    });
});
</script>
@endpush
