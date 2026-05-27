<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
        }

        .page {
            padding: 28px 32px;
        }

        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
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
            margin: 14px 0 18px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 7px;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
            font-weight: bold;
            text-align: left;
        }

        .footer {
            margin-top: 16px;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="title">{{ $title }}</div>
            <p class="subtitle">{{ $subtitle }}</p>
            <div class="meta">{{ $schoolName }} | {{ $appName }} | Digenerate: {{ $generatedAt }}</div>
        </div>

        <div class="summary">
            @foreach($summaryLines as $line)
                <div>{{ $line }}</div>
            @endforeach
        </div>

        <table>
            <thead>
                <tr>
                    @foreach($headings as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headings) }}">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Dokumen ini dibuat otomatis oleh sistem untuk kebutuhan administrasi dan pelaporan.
        </div>
    </div>
</body>
</html>
