<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Keuangan - SMK NURUL HIDAYAH</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; padding: 25px; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 15px; }
        .school-name { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
        .school-desc { font-size: 10px; color: #475569; }
        .title { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 10px; letter-spacing: 0.5px; }
        .meta-info { margin-bottom: 15px; font-size: 10px; line-height: 1.6; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9px; color: #334155; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #f8fafc; font-weight: bold; font-size: 11px; }
        .signature-section { margin-top: 30px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sign-box { width: 220px; text-align: center; }
        .sign-space { height: 60px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: portrait; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11px;">
            Cetak / Print Dokumen
        </button>
    </div>

    <div class="header">
        <div class="school-name">SMK NURUL HIDAYAH</div>
        <div class="school-desc">Jl. Raya Nurul Hidayah, Bungah, Gresik | Sistem Manajemen Keuangan & Pembayaran Siswa</div>
        <div class="title">LAPORAN REKAP PEMBAYARAN KEUANGAN SEKOLAH</div>
    </div>

    <div class="meta-info">
        <div><strong>Periode:</strong> {{ $filterInfo['tanggal_mulai'] }} s/d {{ $filterInfo['tanggal_selesai'] }}</div>
        <div>
            <strong>Filter Kelas:</strong> {{ $filterInfo['kelas'] }} | 
            <strong>Kategori Pos:</strong> {{ $filterInfo['pos'] }} | 
            <strong>Metode:</strong> {{ $filterInfo['metode'] ?? 'Semua Metode' }}
            @if(!empty($filterInfo['search']))
                | <strong>Pencarian:</strong> "{{ $filterInfo['search'] }}"
            @endif
        </div>
        <div><strong>Waktu Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Pos Pembayaran</th>
                <th class="text-center">Metode</th>
                <th class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksiList as $index => $t)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $t->nomor_transaksi }}</td>
                <td>{{ \Carbon\Carbon::parse($t->tanggal_bayar)->format('d/m/Y') }}</td>
                <td><strong>{{ $t->siswa->nama ?? '-' }}</strong> ({{ $t->siswa->nisn ?? '-' }})</td>
                <td>{{ $t->siswa->kelas ?? '-' }}</td>
                <td>{{ $t->posKeuangan->nama ?? '-' }} {{ $t->tagihan?->bulan ? '('.$t->tagihan->bulan.')' : '' }}</td>
                <td class="text-center">{{ $t->metode_pembayaran }}</td>
                <td class="text-right font-bold">{{ number_format($t->nominal_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">Tidak ada data transaksi pembayaran pada filter ini.</td>
            </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL PENERIMAAN KAS:</td>
                <td class="text-right">Rp {{ number_format($totalKas, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="sign-box">
            <div>Mengetahui,</div>
            <div>Kepala Sekolah</div>
            <div class="sign-space" style="height: 65px; text-align: center;">
                <img src="{{ public_path('images/ttd-istianah.png') }}" style="height: 65px; margin: -5px auto;" alt="Ttd & Stempel">
            </div>
            <div><b>ISTIANAH, S.Si</b></div>
            <div>NIP. -</div>
        </div>
        <div class="sign-box">
            <div>Bungah, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
            <div>Bendahara Sekolah</div>
            <div class="sign-space" style="height: 65px;"></div>
            <div><b>{{ auth()->user()->name ?? 'Bendahara' }}</b></div>
            <div>NIP. -</div>
        </div>
    </div>
</body>
</html>
