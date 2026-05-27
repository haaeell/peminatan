<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Penempatan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.55;
            margin: 0;
        }

        .page {
            padding: 36px 44px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 28px;
        }

        .logo {
            width: 72px;
            vertical-align: top;
        }

        .header-text {
            padding-left: 14px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 4px;
        }

        .app-name {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #334155;
            margin: 0 0 6px;
        }

        .title {
            text-align: center;
            margin: 24px 0 6px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            margin: 0 0 24px;
            color: #475569;
        }

        .meta-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            width: 160px;
        }

        .summary-box {
            margin: 22px 0;
            padding: 16px 18px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        .footer-note {
            margin-top: 24px;
            font-size: 11px;
            color: #475569;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
        }

        .signature-block {
            padding-top: 32px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="page">
        <table class="header">
            <tr>
                <td style="width: 84px;">
                    @if($logoDataUri)
                        <img src="{{ $logoDataUri }}" alt="Logo sekolah" class="logo">
                    @endif
                </td>
                <td class="header-text">
                    <p class="app-name">{{ $appName }}</p>
                    <p class="school-name">{{ $schoolName }}</p>
                    <p style="margin: 0;">Dokumen resmi hasil penempatan jurusan dan kelas siswa</p>
                </td>
            </tr>
        </table>

        <div class="title">Surat Keterangan Penempatan</div>
        <div class="subtitle">Nomor: {{ $announcement->id }}/SKP/{{ now()->format('Y') }}</div>

        <p>
            Yang bertanda tangan di bawah ini menerangkan bahwa siswa berikut telah menerima hasil penempatan
            jurusan dan kelas sesuai pengumuman yang dipublikasikan oleh sekolah.
        </p>

        <table class="meta-table">
            <tr>
                <td class="label">Nama Siswa</td>
                <td>: {{ $student->name }}</td>
            </tr>
            <tr>
                <td class="label">NISN</td>
                <td>: {{ $student->nisn }}</td>
            </tr>
            <tr>
                <td class="label">NIS</td>
                <td>: {{ $student->nis ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kelas Asal</td>
                <td>: {{ $student->origin_class ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jurusan Diterima</td>
                <td>: {{ $classStudent->package->name }}</td>
            </tr>
            <tr>
                <td class="label">Kelas Penempatan</td>
                <td>: {{ $classStudent->classGroup->name }}</td>
            </tr>
            <tr>
                <td class="label">Judul Pengumuman</td>
                <td>: {{ $announcement->title }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Respons</td>
                <td>: {{ optional($response->responded_at)->translatedFormat('d F Y H:i') }}</td>
            </tr>
        </table>

        <div class="summary-box">
            <strong>Keterangan:</strong>
            Siswa yang bersangkutan telah menyatakan <strong>menerima</strong> hasil penempatan jurusan
            <strong>{{ $classStudent->package->name }}</strong> dan kelas
            <strong>{{ $classStudent->classGroup->name }}</strong>.
        </div>

        <p>
            Demikian surat keterangan ini dibuat untuk digunakan sebagaimana mestinya. Untuk informasi lebih lanjut,
            dapat menghubungi {{ $supportContact ?: 'admin sekolah' }}.
        </p>

        <table class="signature-table">
            <tr>
                <td></td>
                <td class="signature-block">
                    <div>{{ $issuedDate }}</div>
                    <div style="margin-top: 8px;">Mengetahui,</div>
                    <div style="margin-top: 52px; font-weight: bold;">Admin {{ $schoolName }}</div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Dokumen ini dibuat otomatis oleh sistem dan sah digunakan sebagai bukti penerimaan hasil penempatan.
        </div>
    </div>
</body>
</html>
