@extends('layouts.page')

@section('title', 'Laporan Absensi Guru')

@section('content')
<div id="view-laporan-guru" class="view-section active animate-fade-in space-y-5">

    {{-- HEADER --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                    <i class="fas fa-file-invoice text-indigo-600"></i> Laporan Rekap Absensi Guru & Staf
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Rekap bulanan, tahunan, dan log riwayat harian kehadiran guru & staf sekolah</p>
            </div>
        </div>

        {{-- TAB NAVIGATION --}}
        <div class="flex gap-1 mt-5 bg-gray-100 rounded-xl p-1">
            <button id="tab-btn-bulanan" onclick="switchTab('bulanan')"
                class="tab-btn flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold transition bg-white text-indigo-700 shadow-sm">
                <i class="fas fa-calendar-alt"></i> Rekap Bulanan
            </button>
            <button id="tab-btn-tahunan" onclick="switchTab('tahunan')"
                class="tab-btn flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold transition text-gray-600 hover:bg-white/50">
                <i class="fas fa-chart-bar"></i> Rekap Tahunan
            </button>
            <button id="tab-btn-harian" onclick="switchTab('harian')"
                class="tab-btn flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold transition text-gray-600 hover:bg-white/50">
                <i class="fas fa-list-ul"></i> Log Harian
            </button>
        </div>
    </div>

    {{-- ======================== TAB: REKAP BULANAN ======================== --}}
    <div id="tab-bulanan" class="tab-panel space-y-4">
        {{-- Filter --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Bulan</label>
                    <select id="rk-bulan" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5 font-bold cursor-pointer focus:ring-indigo-500">
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $bi => $bn)
                        <option value="{{ $bi + 1 }}" {{ (now()->month == ($bi+1)) ? 'selected' : '' }}>{{ $bn }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                    <select id="rk-tahun" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5 font-bold cursor-pointer focus:ring-indigo-500">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" {{ (now()->year == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Cari Guru</label>
                    <input type="text" id="rk-search" placeholder="Nama / NIP / Jabatan..." class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Aksi</label>
                    <button onclick="loadRekapBulanan()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Export</label>
                    <button onclick="exportRekapBulanan()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3" id="rk-summary-cards">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Guru</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" id="rks-total">0</p>
            </div>
            <div class="bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm p-4 text-center">
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Total Hadir</p>
                <p class="text-2xl font-bold text-emerald-700 mt-1" id="rks-hadir">0</p>
            </div>
            <div class="bg-amber-50 rounded-xl border border-amber-100 shadow-sm p-4 text-center">
                <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Terlambat</p>
                <p class="text-2xl font-bold text-amber-700 mt-1" id="rks-telat">0</p>
            </div>
            <div class="bg-blue-50 rounded-xl border border-blue-100 shadow-sm p-4 text-center">
                <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Izin</p>
                <p class="text-2xl font-bold text-blue-700 mt-1" id="rks-izin">0</p>
            </div>
            <div class="bg-orange-50 rounded-xl border border-orange-100 shadow-sm p-4 text-center">
                <p class="text-[10px] font-bold text-orange-600 uppercase tracking-wider">Sakit</p>
                <p class="text-2xl font-bold text-orange-700 mt-1" id="rks-sakit">0</p>
            </div>
            <div class="bg-rose-50 rounded-xl border border-rose-100 shadow-sm p-4 text-center">
                <p class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">% Kehadiran</p>
                <p class="text-2xl font-bold text-rose-700 mt-1" id="rks-persen">0%</p>
            </div>
        </div>

        {{-- Matrix Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <span id="rk-label" class="text-xs font-bold text-gray-600">Rekap Bulanan</span>
                <button onclick="window.print()" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
            {{-- Keterangan badge --}}
            <div class="px-4 py-2 flex flex-wrap gap-3 border-b border-gray-100 bg-white text-[10px] font-bold text-gray-500">
                <span><span class="inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">H</span> Hadir</span>
                <span><span class="inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">TL</span> Terlambat</span>
                <span><span class="inline-block px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">I</span> Izin</span>
                <span><span class="inline-block px-1.5 py-0.5 rounded bg-orange-100 text-orange-700">S</span> Sakit</span>
                <span><span class="inline-block px-1.5 py-0.5 rounded bg-rose-100 text-rose-700">A</span> Alpa</span>
                <span><span class="inline-block px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">PC</span> Pulang Cepat</span>
                <span><span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 text-gray-400">-</span> Tidak Ada Data</span>
            </div>
            <div class="overflow-x-auto" id="rk-table-wrapper">
                <table class="w-full text-left" id="tbl-rekap-bulanan">
                    <thead id="rk-thead" class="bg-indigo-700 text-white text-[10px] uppercase">
                        <tr>
                            <th class="p-2.5 text-center w-10 sticky left-0 bg-indigo-700 z-10">No</th>
                            <th class="p-2.5 min-w-[160px] sticky left-10 bg-indigo-700 z-10">Nama Guru / Staf</th>
                            <th class="p-2.5 text-center min-w-[100px]">Jabatan</th>
                            {{-- date cols filled by JS --}}
                        </tr>
                    </thead>
                    <tbody id="tbody-rekap-bulanan">
                        <tr><td colspan="40" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat rekap bulanan...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-gray-100 text-xs text-gray-400" id="rk-info">Menampilkan 0 guru</div>
        </div>
    </div>

    {{-- ======================== TAB: REKAP TAHUNAN ======================== --}}
    <div id="tab-tahunan" class="tab-panel hidden space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                    <select id="rt-tahun" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5 font-bold cursor-pointer">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" {{ (now()->year == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Cari Guru</label>
                    <input type="text" id="rt-search" placeholder="Nama / NIP / Jabatan..." class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Aksi</label>
                    <button onclick="loadRekapTahunan()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition shadow-sm">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Export</label>
                    <button onclick="exportRekapTahunan()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition shadow-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <span id="rt-label" class="text-xs font-bold text-gray-600">Rekap Tahunan</span>
                <button onclick="window.print()" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="tbl-rekap-tahunan">
                    <thead class="bg-indigo-700 text-white text-[10px] uppercase">
                        <tr>
                            <th class="p-2.5 text-center w-10">No</th>
                            <th class="p-2.5 min-w-[160px]">Nama Guru / Staf</th>
                            <th class="p-2.5 text-center">Jabatan</th>
                            <th class="p-2.5 text-center">Jan</th><th class="p-2.5 text-center">Feb</th><th class="p-2.5 text-center">Mar</th>
                            <th class="p-2.5 text-center">Apr</th><th class="p-2.5 text-center">Mei</th><th class="p-2.5 text-center">Jun</th>
                            <th class="p-2.5 text-center">Jul</th><th class="p-2.5 text-center">Agt</th><th class="p-2.5 text-center">Sep</th>
                            <th class="p-2.5 text-center">Okt</th><th class="p-2.5 text-center">Nov</th><th class="p-2.5 text-center">Des</th>
                            <th class="p-2.5 text-center bg-indigo-900 font-extrabold">Total H</th>
                            <th class="p-2.5 text-center bg-amber-700">Telat</th>
                            <th class="p-2.5 text-center bg-blue-700">Izin</th>
                            <th class="p-2.5 text-center bg-orange-700">Sakit</th>
                            <th class="p-2.5 text-center bg-rose-700">Alpa</th>
                            <th class="p-2.5 text-center bg-indigo-900 font-extrabold">%</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-rekap-tahunan">
                        <tr><td colspan="21" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat rekap tahunan...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-gray-100 text-xs text-gray-400" id="rt-info">Menampilkan 0 guru</div>
        </div>
    </div>

    {{-- ======================== TAB: LOG HARIAN ======================== --}}
    <div id="tab-harian" class="tab-panel hidden space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                    <input type="date" id="lapGuruStart" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5 font-semibold">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Akhir</label>
                    <input type="date" id="lapGuruEnd" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5 font-semibold">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select id="lapGuruStatus" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl w-full p-2.5 font-bold cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Aksi</label>
                    <button onclick="loadLaporanGuruData()" class="w-full bg-slate-900 hover:bg-black text-white py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Export</label>
                    <button onclick="exportLaporanGuruExcel()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <span id="laporanGuruSubtitle" class="text-xs font-bold text-gray-600">Riwayat Log Harian</span>
                <input type="text" id="lapGuruSearch" oninput="filterLaporanGuruTable()" placeholder="Cari nama/jabatan..." class="bg-white border border-gray-200 text-gray-800 text-xs rounded-xl px-3 py-1.5 outline-none focus:ring-1 focus:ring-indigo-400 w-52">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left" id="printLaporanGuruTable">
                    <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-semibold">
                        <tr>
                            <th class="p-3.5 text-center w-10">No</th>
                            <th class="p-3.5 text-center">Tanggal</th>
                            <th class="p-3.5">Nama Guru / Staf</th>
                            <th class="p-3.5 text-center">NIP / Username</th>
                            <th class="p-3.5 text-center">Jabatan</th>
                            <th class="p-3.5 text-center">Jam Datang</th>
                            <th class="p-3.5 text-center">Jam Pulang</th>
                            <th class="p-3.5 text-center">Keterangan</th>
                            <th class="p-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-laporan-guru" class="divide-y divide-gray-100 bg-white text-sm">
                        <tr><td colspan="9" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat log harian...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50/30 text-xs text-gray-500">
                <span id="info-laporan-guru">Menampilkan 0 data</span>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let rawLaporanGuruData = [];
    let activeTab = 'bulanan';

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? String(meta.getAttribute('content') || '') : '';
    }

    // =========== TAB SWITCHING ===========
    function switchTab(tab) {
        activeTab = tab;
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('tab-' + tab).classList.remove('hidden');
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('bg-white', 'text-indigo-700', 'shadow-sm');
            b.classList.add('text-gray-600');
        });
        const activeBtn = document.getElementById('tab-btn-' + tab);
        if (activeBtn) {
            activeBtn.classList.add('bg-white', 'text-indigo-700', 'shadow-sm');
            activeBtn.classList.remove('text-gray-600');
        }
    }

    // =========== REKAP BULANAN ===========
    async function loadRekapBulanan() {
        const bulan = document.getElementById('rk-bulan')?.value || new Date().getMonth() + 1;
        const tahun = document.getElementById('rk-tahun')?.value || new Date().getFullYear();
        const search = document.getElementById('rk-search')?.value || '';

        document.getElementById('tbody-rekap-bulanan').innerHTML = `<tr><td colspan="40" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat rekap bulanan...</td></tr>`;

        try {
            const res = await fetch('/laporan-absensi-guru/rekap-bulanan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ args: [parseInt(bulan), parseInt(tahun), search] })
            });
            const data = await res.json();
            if (data.success) {
                renderRekapBulananSummary(data.summary);
                renderRekapBulananTable(data.data, data.days_in_month, data.bulan_label);
                document.getElementById('rk-label').textContent = 'Rekap Bulanan — ' + data.bulan_label;
                document.getElementById('rk-info').textContent = 'Menampilkan ' + data.data.length + ' guru';
            } else {
                alert(data.message || 'Gagal memuat rekap bulanan.');
            }
        } catch (e) {
            console.error(e);
            document.getElementById('tbody-rekap-bulanan').innerHTML = `<tr><td colspan="40" class="p-8 text-center text-red-400">Gagal memuat data. Coba lagi.</td></tr>`;
        }
    }

    function renderRekapBulananSummary(s) {
        document.getElementById('rks-total').textContent = s.total_guru ?? 0;
        document.getElementById('rks-hadir').textContent = s.total_hadir ?? 0;
        document.getElementById('rks-telat').textContent = s.total_telat ?? 0;
        document.getElementById('rks-izin').textContent = s.total_izin ?? 0;
        document.getElementById('rks-sakit').textContent = s.total_sakit ?? 0;
        document.getElementById('rks-persen').textContent = (s.persen_kehadiran ?? 0) + '%';
    }

    const codeBadge = {
        'H':  '<span class="inline-block w-7 text-center px-0.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-700">H</span>',
        'TL': '<span class="inline-block w-7 text-center px-0.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-700">TL</span>',
        'I':  '<span class="inline-block w-7 text-center px-0.5 py-0.5 rounded text-[9px] font-extrabold bg-blue-100 text-blue-700">I</span>',
        'S':  '<span class="inline-block w-7 text-center px-0.5 py-0.5 rounded text-[9px] font-extrabold bg-orange-100 text-orange-700">S</span>',
        'A':  '<span class="inline-block w-7 text-center px-0.5 py-0.5 rounded text-[9px] font-extrabold bg-rose-100 text-rose-700">A</span>',
        'PC': '<span class="inline-block w-7 text-center px-0.5 py-0.5 rounded text-[9px] font-extrabold bg-purple-100 text-purple-700">PC</span>',
    };

    function renderRekapBulananTable(rows, daysInMonth, bulanLabel) {
        const thead = document.getElementById('rk-thead');
        const tbody = document.getElementById('tbody-rekap-bulanan');

        // Rebuild header
        let thHtml = `
            <th class="p-2 text-center w-10 sticky left-0 bg-indigo-700 z-10">No</th>
            <th class="p-2 min-w-[160px] sticky left-10 bg-indigo-700 z-10">Nama Guru / Staf</th>
            <th class="p-2 text-center min-w-[90px]">Jabatan</th>`;
        for (let d = 1; d <= daysInMonth; d++) {
            thHtml += `<th class="p-1.5 text-center w-8">${d}</th>`;
        }
        thHtml += `<th class="p-2 text-center bg-emerald-700 min-w-[40px]">H</th>
                   <th class="p-2 text-center bg-amber-700 min-w-[40px]">TL</th>
                   <th class="p-2 text-center bg-blue-700 min-w-[40px]">I</th>
                   <th class="p-2 text-center bg-orange-700 min-w-[40px]">S</th>
                   <th class="p-2 text-center bg-rose-700 min-w-[40px]">A</th>
                   <th class="p-2 text-center bg-indigo-900 min-w-[50px] font-extrabold">%</th>`;
        thead.innerHTML = '<tr>' + thHtml + '</tr>';

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="${3 + daysInMonth + 6}" class="p-8 text-center text-gray-400">Tidak ada data guru ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((r, i) => {
            let dateCells = '';
            for (let d = 1; d <= daysInMonth; d++) {
                const h = r.harian[d];
                dateCells += `<td class="p-1 text-center">${h ? (codeBadge[h.code] || '<span class="text-[10px] text-gray-400">?</span>') : '<span class="text-[10px] text-gray-300">·</span>'}</td>`;
            }
            const rowBg = i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50';
            return `<tr class="${rowBg} hover:bg-indigo-50/30 transition">
                <td class="p-2 text-center text-xs text-gray-500 sticky left-0 ${rowBg} z-10">${i+1}</td>
                <td class="p-2 text-xs font-bold text-gray-900 sticky left-10 ${rowBg} z-10 min-w-[160px]">
                    ${r.nama}<div class="text-[10px] text-gray-400 font-normal">${r.username}</div>
                </td>
                <td class="p-2 text-center text-[11px] text-gray-600">${r.jabatan}</td>
                ${dateCells}
                <td class="p-2 text-center text-xs font-bold text-emerald-700 bg-emerald-50">${r.total_hadir}</td>
                <td class="p-2 text-center text-xs font-bold text-amber-700 bg-amber-50">${r.total_telat}</td>
                <td class="p-2 text-center text-xs font-bold text-blue-700 bg-blue-50">${r.total_izin}</td>
                <td class="p-2 text-center text-xs font-bold text-orange-700 bg-orange-50">${r.total_sakit}</td>
                <td class="p-2 text-center text-xs font-bold text-rose-700 bg-rose-50">${r.total_alpa}</td>
                <td class="p-2 text-center text-xs font-extrabold ${r.persen_kehadiran >= 80 ? 'text-emerald-700' : 'text-rose-600'} bg-indigo-50">${r.persen_kehadiran}%</td>
            </tr>`;
        }).join('');
    }

    async function exportRekapBulanan() {
        const bulan = document.getElementById('rk-bulan')?.value;
        const tahun = document.getElementById('rk-tahun')?.value;
        const search = document.getElementById('rk-search')?.value || '';
        await doExport('/laporan-absensi-guru/export-rekap-bulanan', [parseInt(bulan), parseInt(tahun), search], 'Rekap_Bulanan_Guru.xlsx');
    }

    // =========== REKAP TAHUNAN ===========
    async function loadRekapTahunan() {
        const tahun = document.getElementById('rt-tahun')?.value || new Date().getFullYear();
        const search = document.getElementById('rt-search')?.value || '';

        document.getElementById('tbody-rekap-tahunan').innerHTML = `<tr><td colspan="21" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat rekap tahunan...</td></tr>`;
        try {
            const res = await fetch('/laporan-absensi-guru/rekap-tahunan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ args: [parseInt(tahun), search] })
            });
            const data = await res.json();
            if (data.success) {
                renderRekapTahunanTable(data.data, data.tahun);
                document.getElementById('rt-label').textContent = 'Rekap Tahunan — Tahun ' + data.tahun;
                document.getElementById('rt-info').textContent = 'Menampilkan ' + data.data.length + ' guru';
            } else {
                alert(data.message || 'Gagal memuat rekap tahunan.');
            }
        } catch (e) {
            console.error(e);
            document.getElementById('tbody-rekap-tahunan').innerHTML = `<tr><td colspan="21" class="p-8 text-center text-red-400">Gagal memuat data.</td></tr>`;
        }
    }

    function renderRekapTahunanTable(rows, tahun) {
        const tbody = document.getElementById('tbody-rekap-tahunan');
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="21" class="p-8 text-center text-gray-400">Tidak ada data guru ditemukan.</td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map((r, i) => {
            let monthCells = '';
            for (let m = 1; m <= 12; m++) {
                const b = r.bulanan[m] || {};
                const h = b.hadir ?? 0;
                const p = b.persen ?? 0;
                monthCells += `<td class="p-2 text-center text-xs font-bold ${h > 0 ? 'text-emerald-700' : 'text-gray-400'}">${h > 0 ? h : '·'}</td>`;
            }
            const rowBg = i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50';
            return `<tr class="${rowBg} hover:bg-indigo-50/30 transition">
                <td class="p-2.5 text-center text-xs text-gray-500">${i+1}</td>
                <td class="p-2.5 text-xs font-bold text-gray-900">${r.nama}<div class="text-[10px] text-gray-400 font-normal">${r.username}</div></td>
                <td class="p-2.5 text-center text-[11px] text-gray-600">${r.jabatan}</td>
                ${monthCells}
                <td class="p-2.5 text-center text-xs font-extrabold text-emerald-700 bg-emerald-50">${r.total_hadir}</td>
                <td class="p-2.5 text-center text-xs font-bold text-amber-700 bg-amber-50">${r.total_telat}</td>
                <td class="p-2.5 text-center text-xs font-bold text-blue-700 bg-blue-50">${r.total_izin}</td>
                <td class="p-2.5 text-center text-xs font-bold text-orange-700 bg-orange-50">${r.total_sakit}</td>
                <td class="p-2.5 text-center text-xs font-bold text-rose-700 bg-rose-50">${r.total_alpa}</td>
                <td class="p-2.5 text-center text-xs font-extrabold ${r.persen_kehadiran >= 80 ? 'text-emerald-700' : 'text-rose-600'} bg-indigo-50">${r.persen_kehadiran}%</td>
            </tr>`;
        }).join('');
    }

    async function exportRekapTahunan() {
        const tahun = document.getElementById('rt-tahun')?.value;
        const search = document.getElementById('rt-search')?.value || '';
        await doExport('/laporan-absensi-guru/export-rekap-tahunan', [parseInt(tahun), search], 'Rekap_Tahunan_Guru.xlsx');
    }

    // =========== LOG HARIAN ===========
    function initLaporanDatePickers() {
        const today = new Date();
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        const si = document.getElementById('lapGuruStart');
        const ei = document.getElementById('lapGuruEnd');
        if (si && !si.value) si.value = fmt(startOfMonth);
        if (ei && !ei.value) ei.value = fmt(today);
    }

    async function loadLaporanGuruData() {
        const start = document.getElementById('lapGuruStart')?.value || '';
        const end = document.getElementById('lapGuruEnd')?.value || '';
        const status = document.getElementById('lapGuruStatus')?.value || '';
        const tbody = document.getElementById('tbody-laporan-guru');
        if (tbody) tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat data laporan...</td></tr>`;
        try {
            const res = await fetch('/laporan-absensi-guru/list', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ args: [start, end, status, ''] })
            });
            const data = await res.json();
            if (data.success) {
                rawLaporanGuruData = Array.isArray(data.data) ? data.data : [];
                filterLaporanGuruTable();
            } else {
                alert(data.message || 'Gagal memuat laporan guru.');
            }
        } catch (err) {
            if (tbody) tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-red-500">Terjadi kesalahan koneksi server.</td></tr>`;
        }
    }

    function filterLaporanGuruTable() {
        const query = (document.getElementById('lapGuruSearch')?.value || '').toLowerCase().trim();
        const filtered = rawLaporanGuruData.filter(item => {
            if (!query) return true;
            return [item.nama, item.username, item.jabatan].some(v => String(v||'').toLowerCase().includes(query));
        });
        renderLaporanGuruTable(filtered);
    }

    function renderLaporanGuruTable(rows) {
        const tbody = document.getElementById('tbody-laporan-guru');
        const info = document.getElementById('info-laporan-guru');
        if (!tbody) return;
        if (info) info.textContent = `Menampilkan ${rows.length} dari ${rawLaporanGuruData.length} data`;
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400">Tidak ada riwayat absensi pada periode ini.</td></tr>`;
            return;
        }
        const badges = {
            'Hadir': 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Masuk': 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Izin':  'bg-blue-50 text-blue-700 border-blue-200',
            'Sakit': 'bg-amber-50 text-amber-700 border-amber-200',
            'Alpa':  'bg-rose-50 text-rose-700 border-rose-200',
        };
        tbody.innerHTML = rows.map((r, i) => {
            const cls = badges[r.status] || 'bg-gray-50 text-gray-700 border-gray-200';
            return `<tr class="hover:bg-gray-50 transition border-b border-gray-50">
                <td class="p-3 text-center text-xs text-gray-500">${i+1}</td>
                <td class="p-3 text-center text-xs font-bold text-gray-700">${r.tanggal_formatted || r.tanggal}</td>
                <td class="p-3 font-bold text-gray-900 text-sm">${r.nama}</td>
                <td class="p-3 text-center text-xs font-mono text-gray-600">${r.username||'-'}</td>
                <td class="p-3 text-center text-xs text-gray-600">${r.jabatan||'Guru'}</td>
                <td class="p-3 text-center text-xs font-mono font-bold text-gray-700">${r.jam_datang||'-'}</td>
                <td class="p-3 text-center text-xs font-mono font-bold text-gray-700">${r.jam_pulang||'-'}</td>
                <td class="p-3 text-center text-xs text-gray-600">${r.keterangan||'-'}</td>
                <td class="p-3 text-center"><span class="px-2 py-0.5 rounded text-xs font-bold border ${cls}">${r.status}</span></td>
            </tr>`;
        }).join('');
    }

    async function exportLaporanGuruExcel() {
        const start = document.getElementById('lapGuruStart')?.value || '';
        const end = document.getElementById('lapGuruEnd')?.value || '';
        const status = document.getElementById('lapGuruStatus')?.value || '';
        await doExport('/laporan-absensi-guru/export-excel', [start, end, status], 'Laporan_Presensi_Guru.xlsx');
    }

    // =========== SHARED EXPORT HELPER ===========
    async function doExport(url, args, defaultFilename) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'Menyiapkan file Excel...', didOpen: () => Swal.showLoading() });
        }
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ args })
            });
            const data = await res.json();
            if (typeof Swal !== 'undefined') Swal.close();
            if (data.success && data.url) {
                const a = document.createElement('a');
                a.href = data.url;
                a.download = data.filename || defaultFilename;
                document.body.appendChild(a);
                a.click();
                a.remove();
            } else {
                alert(data.message || 'Gagal export Excel.');
            }
        } catch (e) {
            if (typeof Swal !== 'undefined') Swal.close();
            alert('Gagal export data.');
        }
    }

    // =========== INIT ===========
    document.addEventListener('DOMContentLoaded', () => {
        switchTab('bulanan');
        initLaporanDatePickers();
        loadRekapBulanan();
    });
</script>
@endpush

