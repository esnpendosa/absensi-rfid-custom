<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Alumni & Tracer Study - SMK NURUL HIDAYAH</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1e293b; padding: 25px; background: #fff; line-height: 1.4; }
        
        .header { text-align: center; border-bottom: 2.5px solid #0f172a; padding-bottom: 12px; margin-bottom: 15px; position: relative; }
        .school-name { font-size: 18px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px; }
        .school-desc { font-size: 10px; color: #475569; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-top: 10px; color: #1e3a8a; letter-spacing: 0.5px; }
        
        .meta-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; font-size: 10px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; background: #f8fafc; }
        .meta-item { margin-bottom: 2px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin-bottom: 16px; }
        .stat-card { border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; text-align: center; background: #f8fafc; }
        .stat-val { font-size: 14px; font-weight: bold; color: #0f172a; }
        .stat-label { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-top: 2px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9px; color: #334155; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-bekerja { background: #dcfce7; color: #166534; }
        .badge-kuliah { background: #e0e7ff; color: #3730a3; }
        .badge-wirausaha { background: #fef3c7; color: #92400e; }
        .badge-mencari { background: #f1f5f9; color: #475569; }
        .badge-belum { background: #fee2e2; color: #991b1b; }
        
        .signature-section { margin-top: 25px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sign-box { width: 220px; text-align: center; font-size: 10px; }
        .sign-space { height: 60px; }
        
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: landscape; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; background: #f1f5f9; padding: 10px 15px; border-radius: 8px;">
        <span style="font-weight: bold; color: #334155; font-size: 12px;">Pratinjau Cetak Laporan Alumni & Tracer Study</span>
        <div>
            <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 7px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11px;">
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div class="header">
        <div class="school-name">SMK NURUL HIDAYAH</div>
        <div class="school-desc">Jl. Raya Nurul Hidayah, Bungah, Gresik, Jawa Timur | Telp: (031) 3940123</div>
        <div class="report-title">LAPORAN DIREKTORI ALUMNI & TRACER STUDY</div>
    </div>

    <div class="meta-container">
        <div>
            <div class="meta-item"><strong>Filter Tahun Lulus:</strong> {{ $filterInfo['tahun'] }}</div>
            <div class="meta-item"><strong>Filter Kelas Terakhir:</strong> {{ $filterInfo['kelas'] }}</div>
            <div class="meta-item"><strong>Status Tracer:</strong> {{ $filterInfo['tracer'] }}</div>
        </div>
        <div style="text-align: right;">
            <div class="meta-item"><strong>Total Data:</strong> {{ count($alumniList) }} Alumni</div>
            <div class="meta-item"><strong>Waktu Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</div>
            <div class="meta-item"><strong>Dicetak Oleh:</strong> {{ auth()->user()->name ?? 'Administrator' }}</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Alumni</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #16a34a;">
            <div class="stat-val" style="color: #16a34a;">{{ $stats['total_bekerja'] }} <span style="font-size: 10px; font-weight: normal;">({{ $stats['pct_bekerja'] }}%)</span></div>
            <div class="stat-label">Bekerja</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #4f46e5;">
            <div class="stat-val" style="color: #4f46e5;">{{ $stats['total_kuliah'] }} <span style="font-size: 10px; font-weight: normal;">({{ $stats['pct_kuliah'] }}%)</span></div>
            <div class="stat-label">Kuliah</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #d97706;">
            <div class="stat-val" style="color: #d97706;">{{ $stats['total_wirausaha'] }} <span style="font-size: 10px; font-weight: normal;">({{ $stats['pct_wirausaha'] }}%)</span></div>
            <div class="stat-label">Wirausaha</div>
        </div>
        <div class="stat-card" style="border-top: 3px solid #64748b;">
            <div class="stat-val" style="color: #64748b;">{{ $stats['total_lainnya'] }} <span style="font-size: 10px; font-weight: normal;">({{ $stats['pct_lainnya'] }}%)</span></div>
            <div class="stat-label">Mencari Kerja / Belum</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 75px;">NISN</th>
                <th>Nama Lengkap</th>
                <th class="text-center" style="width: 30px;">JK</th>
                <th style="width: 75px;">Kelas Terakhir</th>
                <th class="text-center" style="width: 60px;">Tahun Lulus</th>
                <th style="width: 90px;">Status Tracer</th>
                <th>Instansi / Kampus / Tempat Usaha</th>
                <th>Posisi / Jurusan</th>
                <th style="width: 85px;">Kontak (No HP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alumniList as $index => $item)
                @php
                    $status = $item->status_alumni ?: 'Belum Diisi';
                    $badgeClass = match(strtolower($status)) {
                        'bekerja' => 'badge-bekerja',
                        'kuliah' => 'badge-kuliah',
                        'wirausaha' => 'badge-wirausaha',
                        'mencari kerja' => 'badge-mencari',
                        default => 'badge-belum'
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center" style="font-family: monospace;">{{ $item->nisn ?: '-' }}</td>
                    <td><strong>{{ $item->nama }}</strong></td>
                    <td class="text-center">{{ $item->jenis_kelamin ? substr($item->jenis_kelamin, 0, 1) : '-' }}</td>
                    <td>{{ $item->kelas_terakhir ?: '-' }}</td>
                    <td class="text-center font-bold">{{ $item->tahun_lulus ?: '-' }}</td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                    </td>
                    <td>{{ $item->nama_instansi ?: '-' }}</td>
                    <td>{{ $item->jurusan_posisi ?: '-' }}</td>
                    <td>{{ $item->kontak ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data alumni yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <div class="sign-box">
            <div>Mengetahui,</div>
            <div><strong>Koordinator BKK & Hubin</strong></div>
            <div class="sign-space"></div>
            <div><strong>( ..................................................... )</strong></div>
            <div>NIP. -</div>
        </div>
        <div class="sign-box">
            <div>Bungah, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
            <div><strong>Kepala SMK Nurul Hidayah</strong></div>
            <div class="sign-space"></div>
            <div><strong>ISTIANAH, S.Pd</strong></div>
            <div>NIP. -</div>
        </div>
    </div>

</body>
</html>
