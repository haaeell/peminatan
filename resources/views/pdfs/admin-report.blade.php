@php
    $isStudentReport = $title === 'Laporan Data Siswa Lengkap';
    $isTestResultReport = $title === 'Laporan Hasil Tes Siswa';
    $isClassDistributionReport = $title === 'Laporan Distribusi Kelas';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size:
                {{ $isStudentReport ? '9px' : ($isTestResultReport ? '9px' : '10px') }}
            ;
            color: #0f172a;
            margin: 0;
        }

        .page {
            padding:
                {{ $isStudentReport ? '18px 20px' : ($isTestResultReport ? '20px 22px' : '28px 32px') }}
            ;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .logo-cell {
            width: 72px;
            vertical-align: top;
        }

        .logo {
            width: 58px;
        }

        .header-text {
            padding-left: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 4px;
        }

        .subtitle {
            margin: 0;
            color: #475569;
        }

        .meta {
            margin-top: 10px;
            color: #475569;
        }

        .summary {
            margin:
                {{ $isStudentReport ? '10px 0 12px' : ($isTestResultReport ? '10px 0 14px' : '14px 0 18px') }}
            ;
            padding:
                {{ $isStudentReport ? '8px 10px' : ($isTestResultReport ? '8px 10px' : '10px 12px') }}
            ;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding:
                {{ $isStudentReport ? '4px 5px' : '5px 6px' }}
            ;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: normal;
            line-height: 1.25;
        }

        th {
            background: #e2e8f0;
            font-weight: bold;
            text-align: left;
        }

        .group-row td {
            background: transparent;
            border: none;
            padding: 0;
        }

        .group-title {
            margin: 18px 0 10px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 16px;
            font-size: 9px;
            color: #64748b;
        }

        /* ── Student Report ── */
        .student-report-table {
            font-size: 8px;
        }

        .student-report-table th {
            font-size: 7.5px;
        }

        .student-report-table th:nth-child(1),
        .student-report-table td:nth-child(1) {
            width: 3%;
        }

        .student-report-table th:nth-child(2),
        .student-report-table td:nth-child(2) {
            width: 9%;
        }

        .student-report-table th:nth-child(3),
        .student-report-table td:nth-child(3) {
            width: 7%;
        }

        .student-report-table th:nth-child(4),
        .student-report-table td:nth-child(4) {
            width: 7%;
        }

        .student-report-table th:nth-child(5),
        .student-report-table td:nth-child(5) {
            width: 7%;
        }

        .student-report-table th:nth-child(6),
        .student-report-table td:nth-child(6) {
            width: 9%;
        }

        .student-report-table th:nth-child(7),
        .student-report-table td:nth-child(7) {
            width: 6%;
        }

        .student-report-table th:nth-child(8),
        .student-report-table td:nth-child(8) {
            width: 8%;
        }

        .student-report-table th:nth-child(9),
        .student-report-table td:nth-child(9) {
            width: 9%;
        }

        .student-report-table th:nth-child(10),
        .student-report-table td:nth-child(10) {
            width: 9%;
        }

        .student-report-table th:nth-child(11),
        .student-report-table td:nth-child(11) {
            width: 8%;
        }

        .student-report-table th:nth-child(12),
        .student-report-table td:nth-child(12) {
            width: 8%;
        }

        /* ── Test Result Report ── */
        .test-result-table {
            font-size: 8.5px;
        }

        .test-result-table th {
            font-size: 8px;
        }

        .test-result-table th:nth-child(1),
        .test-result-table td:nth-child(1) {
            width: 3%;
        }

        .test-result-table th:nth-child(2),
        .test-result-table td:nth-child(2) {
            width: 11%;
        }

        .test-result-table th:nth-child(3),
        .test-result-table td:nth-child(3) {
            width: 9%;
        }

        .test-result-table th:nth-child(4),
        .test-result-table td:nth-child(4) {
            width: 8%;
        }

        .test-result-table th:nth-child(5),
        .test-result-table td:nth-child(5) {
            width: 14%;
        }

        .test-result-table th:nth-child(6),
        .test-result-table td:nth-child(6) {
            width: 12%;
        }

        .test-result-table th:nth-child(7),
        .test-result-table td:nth-child(7) {
            width: 12%;
        }

        .test-result-table th:nth-child(8),
        .test-result-table td:nth-child(8) {
            width: 12%;
        }

        .test-result-table th:nth-child(9),
        .test-result-table td:nth-child(9) {
            width: 10%;
        }

        .test-result-table th:nth-child(10),
        .test-result-table td:nth-child(10) {
            width: 9%;
        }

        /* Class Distribution Report */
        .class-distribution-table th:nth-child(1),
        .class-distribution-table td:nth-child(1) {
            width: 4%;
        }

        .class-distribution-table th:nth-child(2),
        .class-distribution-table td:nth-child(2) {
            width: 28%;
        }

        .class-distribution-table th:nth-child(3),
        .class-distribution-table td:nth-child(3) {
            width: 18%;
        }

        .class-distribution-table th:nth-child(4),
        .class-distribution-table td:nth-child(4) {
            width: 18%;
        }

        .class-distribution-table th:nth-child(5),
        .class-distribution-table td:nth-child(5) {
            width: 18%;
        }

        .class-distribution-table th:nth-child(6),
        .class-distribution-table td:nth-child(6) {
            width: 14%;
        }
    </style>
</head>

<body>
    <div class="page">

        <table class="header">
            <tr>
                <td class="logo-cell">
                    @if($logoDataUri)
                        <img src="{{ $logoDataUri }}" alt="Logo sekolah" class="logo">
                    @endif
                </td>
                <td class="header-text">
                    <div class="title">{{ $title }}</div>
                    <p class="subtitle">{{ $subtitle }}</p>
                    <div class="meta">{{ $schoolName }} | {{ $appName }} | Digenerate: {{ $generatedAt }}</div>
                </td>
            </tr>
        </table>

        <div class="summary">
            @foreach($summaryLines as $line)
                <div>{{ $line }}</div>
            @endforeach
        </div>

        <table
            class="{{ $isStudentReport ? 'student-report-table' : ($isTestResultReport ? 'test-result-table' : ($isClassDistributionReport ? 'class-distribution-table' : '')) }}">
            <thead>
                <tr>
                    @foreach($headings as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($groupedRows as $groupName => $groupRows)
                    <tr>
                        <td colspan="{{ count($headings) }}" class="group-row">
                            <div class="group-title">Kelas {{ $groupName }}</div>
                        </td>
                    </tr>
                    @foreach($groupRows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ count($headings) }}">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">Dokumen ini dibuat otomatis oleh sistem untuk kebutuhan administrasi dan pelaporan.</div>

    </div>
</body>

</html>
