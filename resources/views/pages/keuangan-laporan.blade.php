@extends('layouts.page')

@section('title', 'Laporan Keuangan Sekolah')

@section('content')
<div class="view-section active animate-fade-in space-y-6">
    <!-- Header & Action Buttons -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-emerald-600"></i> Laporan Keuangan & Rekap Kas
            </h2>
            <p class="text-xs text-gray-500 mt-1">Rekap data transaksi pembayaran SPP, Uang Gedung, Ujian, dan kas masuk lainnya.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="loadLaporanData()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 text-xs font-bold hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-sync-alt"></i> Perbarui
            </button>
            <button type="button" onclick="cetakLaporanPdf()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md transition transform active:scale-95">
                <i class="fas fa-print"></i> Cetak Laporan Resmi
            </button>
        </div>
    </div>

    <!-- 4 Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Pemasukan Kas</span>
            <div class="mt-2 text-2xl font-bold text-emerald-600" id="statTotalKas">Rp 0</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-blue-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Transaksi</span>
            <div class="mt-2 text-2xl font-bold text-blue-600" id="statTotalTrx">0</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-indigo-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pemasukan Tunai</span>
            <div class="mt-2 text-2xl font-bold text-indigo-600" id="statTunai">Rp 0</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-purple-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Transfer / QRIS</span>
            <div class="mt-2 text-2xl font-bold text-purple-600" id="statNonTunai">Rp 0</div>
        </div>
    </div>

    <!-- Filter & Table Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Dari Tanggal:</label>
                    <input type="date" id="filterTglMulai" onchange="loadLaporanData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">
                </div>
                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Sampai Tanggal:</label>
                    <input type="date" id="filterTglSelesai" onchange="loadLaporanData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">
                </div>
                <!-- Kelas -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kelas:</label>
                    <select id="filterKelas" onchange="loadLaporanData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList ?? [] as $k)
                            <option value="{{ $k }}">{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Pos -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kategori Pos:</label>
                    <select id="filterPos" onchange="loadLaporanData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">
                        <option value="">Semua Pos Keuangan</option>
                        @foreach($posList ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Metode -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Metode Bayar:</label>
                    <select id="filterMetode" onchange="loadLaporanData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">
                        <option value="">Semua Metode</option>
                        <option value="Tunai">Tunai</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" id="filterSearch" oninput="debounceLaporanSearch()" placeholder="Cari nomor transaksi, nama siswa, NISN..." class="w-full bg-white border border-gray-200 rounded-xl pl-9 pr-3 py-2 text-xs focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                </div>
                <button type="button" onclick="resetLaporanFilter()" class="px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 shadow-sm">Reset Filter</button>
            </div>
        </div>

        <!-- Table Transaksi -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-[10px] border-b border-gray-100">
                    <tr>
                        <th class="p-3 text-center w-12">No</th>
                        <th class="p-3">No. Transaksi</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Siswa</th>
                        <th class="p-3">Kelas</th>
                        <th class="p-3">Pos Pembayaran</th>
                        <th class="p-3 text-right">Nominal Bayar</th>
                        <th class="p-3 text-center">Metode</th>
                        <th class="p-3">Kasir</th>
                        <th class="p-3 text-center w-24">Struk</th>
                    </tr>
                </thead>
                <tbody id="tbodyLaporan" class="divide-y divide-gray-100 bg-white text-gray-700">
                    <tr>
                        <td colspan="10" class="p-8 text-center text-gray-400">Memuat data laporan...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let searchLaporanTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadLaporanData();
});

function formatRupiah(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

function debounceLaporanSearch() {
    clearTimeout(searchLaporanTimeout);
    searchLaporanTimeout = setTimeout(() => {
        loadLaporanData();
    }, 300);
}

function resetLaporanFilter() {
    document.getElementById('filterTglMulai').value = '';
    document.getElementById('filterTglSelesai').value = '';
    document.getElementById('filterKelas').value = '';
    document.getElementById('filterPos').value = '';
    document.getElementById('filterMetode').value = '';
    document.getElementById('filterSearch').value = '';
    loadLaporanData();
}

async function loadLaporanData() {
    const tglMulai = document.getElementById('filterTglMulai')?.value || '';
    const tglSelesai = document.getElementById('filterTglSelesai')?.value || '';
    const kelas = document.getElementById('filterKelas')?.value || '';
    const posId = document.getElementById('filterPos')?.value || '';
    const metode = document.getElementById('filterMetode')?.value || '';
    const search = document.getElementById('filterSearch')?.value || '';

    const tbody = document.getElementById('tbodyLaporan');
    tbody.innerHTML = `<tr><td colspan="10" class="p-8 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data laporan...</td></tr>`;

    try {
        const url = new URL("{{ url('/keuangan/laporan/data') }}", window.location.origin);
        if (tglMulai) url.searchParams.set('tanggal_mulai', tglMulai);
        if (tglSelesai) url.searchParams.set('tanggal_selesai', tglSelesai);
        if (kelas) url.searchParams.set('kelas', kelas);
        if (posId) url.searchParams.set('pos_id', posId);
        if (metode) url.searchParams.set('metode', metode);
        if (search) url.searchParams.set('search', search);

        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' }
        });
        const resData = await res.json();

        if (resData.success) {
            const sum = resData.summary || {};
            document.getElementById('statTotalKas').textContent = 'Rp ' + Number(sum.total_kas || 0).toLocaleString('id-ID');
            document.getElementById('statTotalTrx').textContent = sum.total_transaksi || 0;
            document.getElementById('statTunai').textContent = 'Rp ' + Number(sum.total_tunai || 0).toLocaleString('id-ID');
            document.getElementById('statNonTunai').textContent = 'Rp ' + Number(sum.total_non_tunai || 0).toLocaleString('id-ID');

            renderLaporanRows(resData.data);
        }
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="10" class="p-8 text-center text-red-500">Gagal memuat data laporan keuangan.</td></tr>`;
    }
}

function renderLaporanRows(rows) {
    const tbody = document.getElementById('tbodyLaporan');
    if (!rows || rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="p-8 text-center text-gray-400">Tidak ada riwayat transaksi pada filter ini.</td></tr>`;
        return;
    }

    tbody.innerHTML = rows.map((r, idx) => {
        const posName = r.pos_keuangan ? r.pos_keuangan.nama : 'Pos';
        const bln = r.tagihan && r.tagihan.bulan ? ` (${r.tagihan.bulan})` : '';

        return `
            <tr class="hover:bg-gray-50 transition">
                <td class="p-3 text-center font-bold text-gray-400">${idx + 1}</td>
                <td class="p-3 font-mono font-bold text-blue-700">${r.nomor_transaksi}</td>
                <td class="p-3 font-semibold text-gray-600">${r.tanggal_bayar || '-'}</td>
                <td class="p-3">
                    <div class="font-bold text-gray-800">${r.siswa ? r.siswa.nama : '-'}</div>
                    <div class="text-[10px] text-gray-400 font-mono">NISN: ${r.siswa ? r.siswa.nisn : '-'}</div>
                </td>
                <td class="p-3 font-semibold text-gray-600">${r.siswa ? (r.siswa.kelas || '-') : '-'}</td>
                <td class="p-3 font-bold text-gray-700">${posName}${bln}</td>
                <td class="p-3 text-right font-bold text-emerald-600">Rp ${Number(r.nominal_bayar).toLocaleString('id-ID')}</td>
                <td class="p-3 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${r.metode_pembayaran === 'Tunai' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-purple-50 text-purple-700 border border-purple-200'}">
                        ${r.metode_pembayaran || 'Tunai'}
                    </span>
                </td>
                <td class="p-3 text-gray-600 text-[11px]">${r.user ? r.user.name : 'Admin'}</td>
                <td class="p-3 text-center">
                    <a href="{{ url('/keuangan/kuitansi') }}/${r.id}" target="_blank" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[11px] font-bold inline-flex items-center gap-1 transition">
                        <i class="fas fa-receipt"></i> Struk
                    </a>
                </td>
            </tr>
        `;
    }).join('');
}

function cetakLaporanPdf() {
    const tglMulai = document.getElementById('filterTglMulai')?.value || '';
    const tglSelesai = document.getElementById('filterTglSelesai')?.value || '';
    const kelas = document.getElementById('filterKelas')?.value || '';
    const posId = document.getElementById('filterPos')?.value || '';
    const metode = document.getElementById('filterMetode')?.value || '';
    const search = document.getElementById('filterSearch')?.value || '';

    const url = new URL("{{ url('/keuangan/laporan/cetak') }}", window.location.origin);
    if (tglMulai) url.searchParams.set('tanggal_mulai', tglMulai);
    if (tglSelesai) url.searchParams.set('tanggal_selesai', tglSelesai);
    if (kelas) url.searchParams.set('kelas', kelas);
    if (posId) url.searchParams.set('pos_id', posId);
    if (metode) url.searchParams.set('metode', metode);
    if (search) url.searchParams.set('search', search);

    window.open(url.toString(), '_blank');
}
</script>
@endsection
