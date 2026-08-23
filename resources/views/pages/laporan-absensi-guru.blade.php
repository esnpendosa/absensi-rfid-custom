@extends('layouts.page')

@section('title', 'Laporan Absensi Guru')

@section('content')
<div id="view-laporan-guru" class="view-section active animate-fade-in space-y-5">
    
    <!-- FILTER CARD -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 pb-4 border-b border-gray-100">
            <div>
                <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                    <i class="fas fa-file-invoice text-indigo-600"></i> Laporan Rekap Presensi Guru & Staf
                </h3>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Filter dan ekspor rekap data kehadiran guru secara periodik</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="exportLaporanGuruExcel()" class="bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm hover:bg-emerald-700 transition transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> <span>Export Excel</span>
                </button>
                <button type="button" onclick="window.print()" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm hover:bg-indigo-700 transition transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-print"></i> <span>Cetak Laporan</span>
                </button>
            </div>
        </div>

        <!-- FORM FILTER -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                <input type="date" id="lapGuruStart" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 font-semibold">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Akhir</label>
                <input type="date" id="lapGuruEnd" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 font-semibold">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status Kehadiran</label>
                <select id="lapGuruStatus" class="bg-gray-50 border border-gray-200 text-gray-800 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 font-bold cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Aksi</label>
                <button type="button" onclick="loadLaporanGuruData()" class="w-full bg-slate-900 hover:bg-black text-white p-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-filter"></i> <span>Terapkan Filter</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN REPORT TABLE CARD -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <span id="laporanGuruSubtitle" class="text-xs font-bold text-gray-600">Daftar Rekap Presensi</span>
            <div class="w-64">
                <input type="text" id="lapGuruSearch" oninput="filterLaporanGuruTable()" placeholder="Cari nama/jabatan guru..." class="bg-white border border-gray-200 text-gray-800 text-xs rounded-xl px-3 py-1.5 block w-full outline-none focus:ring-1 focus:ring-indigo-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="printLaporanGuruTable">
                <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-semibold">
                    <tr>
                        <th class="p-3.5 text-center w-12">No</th>
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
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-400">
                            <i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat laporan absensi guru...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 bg-gray-50/30 flex justify-between items-center text-xs text-gray-500">
            <span id="info-laporan-guru">Menampilkan 0 data presensi</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rawLaporanGuruData = [];

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? String(meta.getAttribute('content') || '') : '';
    }

    function initLaporanDatePickers() {
        const today = new Date();
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        
        const formatYmd = (d) => {
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        };

        const startInput = document.getElementById('lapGuruStart');
        const endInput = document.getElementById('lapGuruEnd');

        if (startInput && !startInput.value) startInput.value = formatYmd(startOfMonth);
        if (endInput && !endInput.value) endInput.value = formatYmd(today);
    }

    async function loadLaporanGuruData() {
        const start = document.getElementById('lapGuruStart')?.value || '';
        const end = document.getElementById('lapGuruEnd')?.value || '';
        const status = document.getElementById('lapGuruStatus')?.value || '';

        const tbody = document.getElementById('tbody-laporan-guru');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat data laporan...</td></tr>`;
        }

        try {
            const res = await fetch('/laporan-absensi-guru/list', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    args: [start, end, status, '']
                })
            });
            const data = await res.json();
            if (data.success) {
                rawLaporanGuruData = Array.isArray(data.data) ? data.data : [];
                filterLaporanGuruTable();
            } else {
                Swal.fire('Error', data.message || 'Gagal memuat laporan guru.', 'error');
            }
        } catch (err) {
            console.error('Error fetching laporan guru:', err);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-red-500">Terjadi kesalahan koneksi server.</td></tr>`;
            }
        }
    }

    function filterLaporanGuruTable() {
        const query = (document.getElementById('lapGuruSearch')?.value || '').toLowerCase().trim();

        let filtered = rawLaporanGuruData.filter(item => {
            if (query !== '') {
                const name = String(item.nama || '').toLowerCase();
                const uname = String(item.username || '').toLowerCase();
                const jab = String(item.jabatan || '').toLowerCase();
                if (!name.includes(query) && !uname.includes(query) && !jab.includes(query)) {
                    return false;
                }
            }
            return true;
        });

        renderLaporanGuruTable(filtered);
    }

    function renderLaporanGuruTable(rows) {
        const tbody = document.getElementById('tbody-laporan-guru');
        const info = document.getElementById('info-laporan-guru');
        if (!tbody) return;

        if (info) {
            info.textContent = `Menampilkan ${rows.length} dari ${rawLaporanGuruData.length} data presensi`;
        }

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400">Tidak ada riwayat absensi guru pada periode ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((r, i) => {
            let statusBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir</span>';
            if (r.status === 'Izin') {
                statusBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Izin</span>';
            } else if (r.status === 'Sakit') {
                statusBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Sakit</span>';
            } else if (r.status === 'Alpa') {
                statusBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Alpa</span>';
            }

            return `
            <tr class="hover:bg-gray-50 transition border-b border-gray-50">
                <td class="p-3 text-center text-xs text-gray-500 font-mono">${i + 1}</td>
                <td class="p-3 text-center text-xs font-bold text-gray-700">${r.tanggal_formatted || r.tanggal}</td>
                <td class="p-3 font-bold text-gray-900 text-sm">${r.nama}</td>
                <td class="p-3 text-center text-xs font-mono text-gray-600">${r.username || '-'}</td>
                <td class="p-3 text-center text-xs font-semibold text-gray-600">${r.jabatan || 'Guru'}</td>
                <td class="p-3 text-center text-xs font-mono font-bold text-gray-700">${r.jam_datang || '-'}</td>
                <td class="p-3 text-center text-xs font-mono font-bold text-gray-700">${r.jam_pulang || '-'}</td>
                <td class="p-3 text-center text-xs text-gray-600">${r.keterangan || '-'}</td>
                <td class="p-3 text-center">${statusBadge}</td>
            </tr>
            `;
        }).join('');
    }

    async function exportLaporanGuruExcel() {
        const start = document.getElementById('lapGuruStart')?.value || '';
        const end = document.getElementById('lapGuruEnd')?.value || '';
        const status = document.getElementById('lapGuruStatus')?.value || '';

        try {
            Swal.fire({
                title: 'Menyiapkan file Excel...',
                didOpen: () => { Swal.showLoading(); }
            });
            const res = await fetch('/laporan-absensi-guru/export-excel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ args: [start, end, status] })
            });
            const data = await res.json();
            Swal.close();
            if (data.success && data.url) {
                const a = document.createElement('a');
                a.href = data.url;
                a.download = data.filename || 'Laporan_Presensi_Guru.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();
            } else {
                Swal.fire('Gagal', data.message || 'Gagal export Excel.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Gagal export data.', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initLaporanDatePickers();
        loadLaporanGuruData();
    });
</script>
@endpush
