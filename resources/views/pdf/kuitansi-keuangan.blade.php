<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Kasir #{{ $transaksi->nomor_transaksi }} - {{ $transaksi->siswa->nama ?? 'Siswa' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* Container Toolbar */
        .toolbar {
            width: 100%;
            max-width: 400px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 10px 12px;
            font-size: 12px;
            font-weight: bold;
            font-family: sans-serif;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-print { background-color: #2563eb; color: #fff; box-shadow: 0 2px 4px rgba(37,99,235,0.2); }
        .btn-print:hover { background-color: #1d4ed8; }
        .btn-wa { background-color: #16a34a; color: #fff; box-shadow: 0 2px 4px rgba(22,163,74,0.2); }
        .btn-wa:hover { background-color: #15803d; }

        /* NOTA KASIR THERMAL RECEIPT STYLE */
        .receipt {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            padding: 24px 20px;
            border-radius: 4px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .receipt::after {
            content: "";
            position: absolute;
            bottom: -6px;
            left: 0;
            right: 0;
            height: 6px;
            background: radial-gradient(circle, transparent, transparent 50%, #f1f5f9 50%, #f1f5f9 100%);
            background-size: 12px 12px;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .school-title {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #000;
        }

        .school-subtitle {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
        }

        .divider {
            border-top: 1px dashed #64748b;
            margin: 10px 0;
        }

        .double-divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }

        .receipt-info {
            font-size: 11px;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .receipt-info-row {
            display: flex;
            justify-content: space-between;
        }

        /* Items list */
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: bold;
            margin: 6px 0;
        }

        .item-detail {
            font-size: 10px;
            color: #64748b;
            margin-top: -2px;
            margin-bottom: 6px;
        }

        /* Summary Calculations */
        .calc-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 4px 0;
        }

        .calc-total {
            font-size: 14px;
            font-weight: 900;
            color: #000;
            padding-top: 4px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #000;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 2px;
            margin-top: 4px;
        }

        .receipt-footer {
            text-align: center;
            font-size: 10px;
            color: #475569;
            margin-top: 15px;
            line-height: 1.4;
        }

        .barcode {
            margin-top: 10px;
            font-size: 22px;
            letter-spacing: 4px;
            font-family: monospace;
            font-weight: bold;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .toolbar { display: none !important; }
            .receipt {
                max-width: 100% !important;
                width: 78mm !important; /* Standar kertas kasir thermal */
                box-shadow: none !important;
                border: none !important;
                padding: 10px 5px !important;
                margin: 0 auto !important;
            }
            .receipt::after { display: none; }
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas -->
    <div class="toolbar">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Cetak Struk
        </button>
        @php
            $trxList = $allTransactions ?? collect([$transaksi]);
            $totalBayar = $trxList->sum('nominal_bayar');
            $totalNominalFormatted = 'Rp ' . number_format($totalBayar, 0, ',', '.');
            $isAllLunas = true;

            $waPhone = preg_replace('/[^0-9]/', '', (string)($transaksi->siswa->no_hp ?? ''));
            if (str_starts_with($waPhone, '0')) {
                $waPhone = '62' . substr($waPhone, 1);
            }

            $rincianWa = "";
            foreach ($trxList as $idx => $tItem) {
                $tgh = $tItem->tagihan;
                $posLabel = ($tItem->posKeuangan->nama ?? 'Keuangan') . ($tgh?->bulan ? ' (' . $tgh->bulan . ')' : '');
                $nominalItemStr = 'Rp ' . number_format($tItem->nominal_bayar, 0, ',', '.');
                $statusItemStr = ($tgh && $tgh->sisa <= 0) ? '[LUNAS]' : ($tgh ? ('[Sisa: Rp ' . number_format($tgh->sisa, 0, ',', '.') . ']') : '');
                if ($tgh && $tgh->sisa > 0) {
                    $isAllLunas = false;
                }
                $rincianWa .= ($idx + 1) . ". {$posLabel} : {$nominalItemStr} {$statusItemStr}\n";
            }

            $kuitansiUrl = url('/keuangan/kuitansi/' . $transaksi->id);

            $pesanWa = urlencode(
                "*BUKTI PEMBAYARAN RESMI*\n" .
                "*SMK NURUL HIDAYAH*\n" .
                "------------------------------------\n" .
                "No. Nota      : {$transaksi->nomor_transaksi}\n" .
                "Nama Siswa    : {$transaksi->siswa->nama}\n" .
                "NISN          : {$transaksi->siswa->nisn}\n" .
                "Kelas         : {$transaksi->siswa->kelas}\n" .
                "Tanggal       : " . \Carbon\Carbon::parse($transaksi->tanggal_bayar)->translatedFormat('d F Y') . "\n" .
                "------------------------------------\n" .
                "*Rincian Pembayaran:*\n" .
                $rincianWa .
                "------------------------------------\n" .
                "Total Bayar   : *{$totalNominalFormatted}*\n" .
                "Metode        : {$transaksi->metode_pembayaran}\n" .
                "Status        : *" . ($isAllLunas ? 'LUNAS' : 'SEBAGIAN') . "*\n" .
                "------------------------------------\n" .
                "Lihat / Unduh Nota Digital:\n" .
                "{$kuitansiUrl}\n\n" .
                "_Terima kasih, pembayaran telah kami terima dan tercatat secara resmi di sistem sekolah._"
            );
        @endphp
        @if(!empty($waPhone))
            <a href="https://api.whatsapp.com/send?phone={{ $waPhone }}&text={{ $pesanWa }}" target="_blank" class="btn btn-wa">
                <i class="fab fa-whatsapp"></i> Kirim ke WA
            </a>
        @endif
    </div>

    <!-- NOTA STRUK KASIR -->
    <div class="receipt">
        <div class="receipt-header">
            <div class="school-title">SMK NURUL HIDAYAH</div>
            <div class="school-subtitle">Kwitansi Pembayaran Keuangan Sekolah</div>
            <div class="school-subtitle">Bukti Transaksi Pembayaran Resmi</div>
        </div>

        <div class="double-divider"></div>

        <div class="receipt-info">
            <div class="receipt-info-row">
                <span>No. Nota:</span>
                <b>{{ $nomorNotaTampil ?? $transaksi->nomor_transaksi }}</b>
            </div>
            <div class="receipt-info-row">
                <span>Tanggal:</span>
                <span>{{ \Carbon\Carbon::parse($transaksi->tanggal_bayar)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($transaksi->created_at)->format('H:i') }}</span>
            </div>
            <div class="receipt-info-row">
                <span>Kasir:</span>
                <span>{{ $transaksi->user->name ?? 'Admin Kasir' }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="receipt-info">
            <div class="receipt-info-row">
                <span>Siswa:</span>
                <b>{{ $transaksi->siswa->nama ?? '-' }}</b>
            </div>
            <div class="receipt-info-row">
                <span>NISN / Kelas:</span>
                <span>{{ $transaksi->siswa->nisn ?? '-' }} ({{ $transaksi->siswa->kelas ?? '-' }})</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Detail Item Checklist -->
        <div style="font-size: 10px; font-weight: bold; color: #475569; margin-bottom: 4px; text-transform: uppercase;">
            Rincian Item Pembayaran:
        </div>

        @foreach($trxList as $item)
            @php
                $itemPosNama = ($item->posKeuangan->nama ?? 'Keuangan') . ($item->tagihan?->bulan ? ' (' . $item->tagihan->bulan . ')' : '');
                $itemLunas = ($item->tagihan && $item->tagihan->sisa <= 0);
            @endphp
            <div style="margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px dotted #e2e8f0;">
                <div class="item-row" style="margin: 2px 0;">
                    <span><i class="fas fa-check-square" style="color: #16a34a; margin-right: 4px;"></i> {{ $itemPosNama }}</span>
                    <span>Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="item-detail" style="display: flex; justify-content: space-between; margin: 0;">
                    <span>{{ $item->keterangan ?: 'Pembayaran sah' }}</span>
                    <span style="font-weight: bold; color: {{ $itemLunas ? '#16a34a' : '#ea580c' }};">
                        {{ $itemLunas ? '[LUNAS]' : ('[Sisa: Rp ' . number_format($item->tagihan->sisa, 0, ',', '.') . ']') }}
                    </span>
                </div>
            </div>
        @endforeach

        <div class="double-divider"></div>

        <!-- Perhitungan Total -->
        <div class="calc-row">
            <span>Metode Bayar:</span>
            <b>{{ $transaksi->metode_pembayaran }}</b>
        </div>
        <div class="calc-row calc-total">
            <span>TOTAL BAYAR:</span>
            <span>{{ $totalNominalFormatted }}</span>
        </div>

        <div style="text-align: center; margin-top: 12px;">
            <span class="status-badge" style="background-color: {{ $isAllLunas ? '#16a34a' : '#1e293b' }};">
                {{ $isAllLunas ? 'LUNAS / SELESAI' : 'BERHASIL DIBAYAR (SEBAGIAN)' }}
            </span>
        </div>

        <div class="divider"></div>

        <div class="receipt-footer">
            <p>Simpan nota kuitansi ini sebagai bukti pembayaran yang sah.</p>
            <p style="margin-top: 4px; font-weight: bold;">-- TERIMA KASIH --</p>
            <div class="barcode">*{{ substr($nomorNotaTampil ?? $transaksi->nomor_transaksi, -8) }}*</div>
        </div>
    </div>

</body>
</html>
