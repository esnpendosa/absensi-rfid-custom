@extends('layouts.page')

@section('title', 'Monitoring Presensi Guru')

@section('content')
<div id="view-monitoring-guru" class="view-section active animate-fade-in space-y-5">
    
    <!-- STATS CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-11 h-11 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-lg font-bold">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Guru</p>
                <h4 id="statTotalGuru" class="text-xl font-bold text-gray-800">0</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg font-bold">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Sudah Hadir</p>
                <h4 id="statHadirGuru" class="text-xl font-bold text-emerald-600">0</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-lg font-bold">
                <i class="fas fa-user-clock"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Terlambat</p>
                <h4 id="statTelatGuru" class="text-xl font-bold text-amber-600">0</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-lg font-bold">
                <i class="fas fa-notes-medical"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Izin / Sakit</p>
                <h4 id="statIzinSakitGuru" class="text-xl font-bold text-blue-600">0</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-3 col-span-2 sm:col-span-1">
            <div class="w-11 h-11 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-lg font-bold">
                <i class="fas fa-user-times"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Belum Absen</p>
                <h4 id="statBelumHadirGuru" class="text-xl font-bold text-rose-600">0</h4>
            </div>
        </div>
    </div>

    <!-- MAIN TABLE CARD -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- HEADER -->
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center bg-gray-50/50 gap-4">
            <div>
                <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chalkboard-teacher text-indigo-600"></i> Monitoring Presensi Guru & Staf
                </h3>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Realtime hari ini: <span id="monitoringGuruDate" class="text-indigo-600 font-bold">Memuat tanggal...</span></p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                @if (auth()->user()?->hasAnyRole(['admin', 'kepsek', 'super-admin']))
                    <button type="button" onclick="markPulangMassalGuru()" class="bg-indigo-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm hover:bg-indigo-700 transition transform active:scale-95 flex items-center gap-2">
                        <i class="fas fa-right-from-bracket"></i> <span>Pulang Massal</span>
                    </button>
                @endif

                <button type="button" onclick="exportMonitoringGuruExcel()" class="bg-emerald-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm hover:bg-emerald-700 transition transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> <span>Export Excel</span>
                </button>

                <button type="button" onclick="loadMonitoringGuruData()" class="bg-white text-gray-600 border border-gray-200 px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm hover:bg-gray-50 hover:text-indigo-600 transition" title="Muat Ulang Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- CONTROLS & FILTER -->
        <div class="p-4 bg-white border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs w-full md:w-auto">
                <select id="filterStatusGuru" onchange="applyMonitoringGuruFilter()" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 font-bold cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Terlambat">Terlambat</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpa">Alpa</option>
                    <option value="Belum Absen">Belum Absen</option>
                </select>

                <select id="filterJabatanGuru" onchange="applyMonitoringGuruFilter()" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 font-bold cursor-pointer">
                    <option value="">Semua Jabatan</option>
                </select>
            </div>

            <div class="relative w-full md:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs"></i>
                </div>
                <input type="text" id="searchGuruQuery" oninput="applyMonitoringGuruFilter()" class="bg-gray-50 border border-gray-200 text-gray-900 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-9 p-2.5 transition" placeholder="Cari Nama / NIP Guru...">
            </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-semibold">
                    <tr>
                        <th class="p-3.5 text-center w-12">No</th>
                        <th class="p-3.5">Nama Guru / Staf</th>
                        <th class="p-3.5 text-center">NIP / Username</th>
                        <th class="p-3.5 text-center">Jabatan</th>
                        <th class="p-3.5 text-center">Jam Datang</th>
                        <th class="p-3.5 text-center">Jam Pulang</th>
                        <th class="p-3.5 text-center">Keterangan</th>
                        <th class="p-3.5 text-center">Status Kehadiran</th>
                        @if (auth()->user()?->hasAnyRole(['admin', 'kepsek', 'super-admin']))
                            <th class="p-3.5 text-center w-24">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="tbody-monitoring-guru" class="divide-y divide-gray-100 bg-white text-sm">
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-400">
                            <i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat data monitoring guru...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- FOOTER -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/30 flex justify-between items-center text-xs text-gray-500">
            <span id="info-monitoring-guru">Menampilkan 0 guru</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rawMonitoringGuruData = [];

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? String(meta.getAttribute('content') || '') : '';
    }

    async function loadMonitoringGuruData() {
        const tbody = document.getElementById('tbody-monitoring-guru');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400"><i class="fas fa-circle-notch fa-spin text-indigo-600 mr-2"></i> Memuat data...</td></tr>`;
        }

        try {
            const res = await fetch('/monitoring-guru/monitoring', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ args: [] })
            });
            const data = await res.json();
            if (data.success) {
                rawMonitoringGuruData = Array.isArray(data.data) ? data.data : [];
                document.getElementById('monitoringGuruDate').textContent = data.tanggal_label || data.tanggal;
                
                // Update stats
                if (data.summary) {
                    document.getElementById('statTotalGuru').textContent = data.summary.total || 0;
                    document.getElementById('statHadirGuru').textContent = data.summary.hadir || 0;
                    document.getElementById('statTelatGuru').textContent = data.summary.terlambat || 0;
                    document.getElementById('statIzinSakitGuru').textContent = data.summary.izin_sakit || 0;
                    document.getElementById('statBelumHadirGuru').textContent = data.summary.belum_hadir || 0;
                }

                populateJabatanFilter();
                applyMonitoringGuruFilter();
            } else {
                Swal.fire('Error', data.message || 'Gagal memuat monitoring guru.', 'error');
            }
        } catch (err) {
            console.error('Error fetching monitoring guru:', err);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-red-500">Terjadi kesalahan koneksi server.</td></tr>`;
            }
        }
    }

    function populateJabatanFilter() {
        const select = document.getElementById('filterJabatanGuru');
        if (!select) return;
        const currentVal = select.value;
        const jabatans = [...new Set(rawMonitoringGuruData.map(g => g.jabatan).filter(Boolean))].sort();

        let html = '<option value="">Semua Jabatan</option>';
        jabatans.forEach(j => {
            html += `<option value="${j}" ${j === currentVal ? 'selected' : ''}>${j}</option>`;
        });
        select.innerHTML = html;
    }

    function applyMonitoringGuruFilter() {
        const statusFilter = document.getElementById('filterStatusGuru')?.value || '';
        const jabatanFilter = document.getElementById('filterJabatanGuru')?.value || '';
        const query = (document.getElementById('searchGuruQuery')?.value || '').toLowerCase().trim();

        let filtered = rawMonitoringGuruData.filter(item => {
            if (statusFilter !== '') {
                if (statusFilter === 'Terlambat') {
                    if (item.keterangan !== 'Terlambat') return false;
                } else if (item.status !== statusFilter) {
                    return false;
                }
            }

            if (jabatanFilter !== '' && item.jabatan !== jabatanFilter) {
                return false;
            }

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

        renderMonitoringGuruTable(filtered);
    }

    function renderMonitoringGuruTable(rows) {
        const tbody = document.getElementById('tbody-monitoring-guru');
        const info = document.getElementById('info-monitoring-guru');
        if (!tbody) return;

        if (info) {
            info.textContent = `Menampilkan ${rows.length} dari ${rawMonitoringGuruData.length} guru`;
        }

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400">Tidak ada data guru yang cocok.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((g, i) => {
            let statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">Belum Absen</span>';
            if (g.status === 'Hadir' || g.status === 'Masuk') {
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir</span>';
            } else if (g.status === 'Izin') {
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Izin</span>';
            } else if (g.status === 'Sakit') {
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Sakit</span>';
            } else if (g.status === 'Alpa') {
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Alpa</span>';
            }

            let ketBadge = g.keterangan || '-';
            if (g.keterangan === 'Terlambat') {
                ketBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">Terlambat</span>';
            } else if (g.keterangan === 'Tepat Waktu') {
                ketBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">Tepat Waktu</span>';
            }

            return `
            <tr class="hover:bg-gray-50 transition border-b border-gray-50">
                <td class="p-3.5 text-center text-xs text-gray-500 font-mono">${i + 1}</td>
                <td class="p-3.5 font-bold text-gray-900 text-sm">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase">
                            ${(g.nama || 'G').charAt(0)}
                        </div>
                        <div>
                            <div>${g.nama}</div>
                            <div class="text-[10px] text-gray-400 font-mono">${g.nomor_kartu ? 'UID: ' + g.nomor_kartu : 'Kartu belum ditautkan'}</div>
                        </div>
                    </div>
                </td>
                <td class="p-3.5 text-center text-xs font-mono text-gray-600">${g.username || '-'}</td>
                <td class="p-3.5 text-center text-xs">
                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md font-semibold border border-indigo-100">${g.jabatan || 'Guru'}</span>
                </td>
                <td class="p-3.5 text-center text-xs font-mono font-semibold text-gray-700">${g.jam_datang || '-'}</td>
                <td class="p-3.5 text-center text-xs font-mono font-semibold text-gray-700">${g.jam_pulang || '-'}</td>
                <td class="p-3.5 text-center text-xs">${ketBadge}</td>
                <td class="p-3.5 text-center">${statusBadge}</td>
                @if (auth()->user()?->hasAnyRole(['admin', 'kepsek', 'super-admin']))
                <td class="p-3.5 text-center">
                    <button type="button" onclick="openStatusModalGuru(${g.user_id}, '${encodeURIComponent(g.nama)}', '${g.status}', '${encodeURIComponent(g.keterangan || '')}')" class="p-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg transition" title="Ubah Status Presensi">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
                @endif
            </tr>
            `;
        }).join('');
    }

    function openStatusModalGuru(userId, encodedNama, currentStatus, encodedKeterangan) {
        const nama = decodeURIComponent(encodedNama);
        const keterangan = decodeURIComponent(encodedKeterangan);

        Swal.fire({
            title: 'Ubah Status Presensi',
            html: `
                <div class="text-left space-y-3 p-1">
                    <p class="text-xs text-gray-500 font-medium">Guru: <strong class="text-gray-800 text-sm block">${nama}</strong></p>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Status Kehadiran</label>
                        <select id="swalGuruStatus" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 text-xs font-semibold">
                            <option value="Hadir" ${currentStatus === 'Hadir' || currentStatus === 'Masuk' ? 'selected' : ''}>Hadir</option>
                            <option value="Izin" ${currentStatus === 'Izin' ? 'selected' : ''}>Izin</option>
                            <option value="Sakit" ${currentStatus === 'Sakit' ? 'selected' : ''}>Sakit</option>
                            <option value="Alpa" ${currentStatus === 'Alpa' ? 'selected' : ''}>Alpa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan (Opsional)</label>
                        <input type="text" id="swalGuruKet" value="${keterangan !== '-' ? keterangan : ''}" placeholder="Contoh: Izin Dinas Luar, Sakit Flu, dll..." class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 text-xs">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#4f46e5',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const status = document.getElementById('swalGuruStatus').value;
                const ket = document.getElementById('swalGuruKet').value;
                return { status, ket };
            }
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            const { status, ket } = result.value;
            try {
                const res = await fetch('/monitoring-guru/update-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({
                        args: [userId, status, ket, '']
                    })
                });
                const resp = await res.json();
                if (resp.success) {
                    Swal.fire('Berhasil', resp.message, 'success');
                    loadMonitoringGuruData();
                } else {
                    Swal.fire('Gagal', resp.message || 'Gagal mengubah status.', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
        });
    }

    async function markPulangMassalGuru() {
        const confirm = await Swal.fire({
            title: 'Absen Pulang Massal Guru?',
            text: 'Semua guru yang sudah hadir akan dicatat jam pulangnya sekarang.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Pulangkan Semua',
            confirmButtonColor: '#4f46e5',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('/monitoring-guru/mark-pulang-massal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ args: [] })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Berhasil', data.message, 'success');
                loadMonitoringGuruData();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Gagal memproses absen pulang massal.', 'error');
        }
    }

    async function exportMonitoringGuruExcel() {
        const today = new Date().toISOString().split('T')[0];
        try {
            Swal.fire({
                title: 'Sedang membuat file Excel...',
                didOpen: () => { Swal.showLoading(); }
            });
            const res = await fetch('/monitoring-guru/export-excel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ args: [today, today, ''] })
            });
            const data = await res.json();
            Swal.close();
            if (data.success && data.url) {
                const a = document.createElement('a');
                a.href = data.url;
                a.download = data.filename || 'Monitoring_Guru.xlsx';
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
        loadMonitoringGuruData();
    });
</script>
@endpush
