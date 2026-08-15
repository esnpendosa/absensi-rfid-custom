@extends('layouts.page')

@section('title', 'Pembayaran & Tagihan Siswa')

@section('content')
<div class="view-section active animate-fade-in space-y-6">
    <!-- Header & Action Buttons -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-money-check-alt text-blue-600"></i> Pembayaran Tagihan Siswa
            </h2>
            <p class="text-xs text-gray-500 mt-1">Input data pembayaran SPP, Uang Gedung, Ujian, dan pantau status tagihan siswa.</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="loadTableData()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 text-xs font-bold hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-sync-alt"></i> Perbarui
            </button>
            @if(auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara']))
            <button type="button" onclick="openInputPembayaranModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition transform active:scale-95">
                <i class="fas fa-plus-circle"></i> Input Pembayaran
            </button>
            @endif
            @if(auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara', 'kepsek', 'wakel']))
            <a href="{{ url('/keuangan/laporan') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition shadow-sm">
                <i class="fas fa-file-invoice-dollar"></i> Laporan
            </a>
            @endif
            @if(auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara', 'kepsek']))
            <a href="{{ url('/keuangan/pos') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md transition transform active:scale-95">
                <i class="fas fa-tags"></i> Kategori Pos
            </a>
            @endif
        </div>
    </div>

    <!-- 4 Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-blue-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Pemasukan</span>
            <div class="mt-2 text-2xl font-bold text-blue-600" id="statPemasukan">Rp 0</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tagihan Lunas</span>
            <div class="mt-2 text-2xl font-bold text-emerald-600" id="statLunas">0</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-indigo-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa Tercakup</span>
            <div class="mt-2 text-2xl font-bold text-indigo-600" id="statSiswa">0</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-red-100 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Tunggakan</span>
            <div class="mt-2 text-2xl font-bold text-red-600" id="statTunggakan">Rp 0</div>
        </div>
    </div>

    <!-- Filters & Table Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col xl:flex-row items-center justify-between gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full xl:w-auto">
                <div>
                    <select id="filterKelas" onchange="loadTableData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-700 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList ?? [] as $k)
                            <option value="{{ $k }}">{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filterPos" onchange="loadTableData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-700 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        <option value="">Semua Kategori Pos</option>
                        @foreach($posList ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filterStatus" onchange="loadTableData()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-gray-700 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="cicilan">Cicilan</option>
                        <option value="belum_bayar">Belum Bayar</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full xl:w-80">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" id="searchInput" oninput="debounceSearch()" placeholder="Cari nama atau NISN..." class="w-full bg-white border border-gray-200 rounded-xl pl-9 pr-3 py-2.5 text-xs focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                </div>
                <button type="button" onclick="resetFilter()" class="px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 shadow-sm">Reset</button>
            </div>
        </div>

        <!-- Table Data Tagihan -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-[10px] border-b border-gray-100">
                    <tr>
                        <th class="p-3 text-center w-12">No</th>
                        <th class="p-3">Siswa</th>
                        <th class="p-3">Kelas</th>
                        <th class="p-3">Pos Pembayaran</th>
                        <th class="p-3 text-right">Tagihan</th>
                        <th class="p-3 text-right">Terbayar</th>
                        <th class="p-3 text-right">Sisa</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyTagihan" class="divide-y divide-gray-100 bg-white text-gray-700">
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-400">Memuat data tagihan...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer Pagination -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center text-xs text-gray-500">
            <span id="pageInfo">Menampilkan 0 data</span>
            <div class="flex gap-1" id="paginationControls"></div>
        </div>
    </div>
</div>

<!-- MODAL INPUT PEMBAYARAN CEPAT -->
<div id="modalInputPembayaran" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 animate-scale-up">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                <i class="fas fa-hand-holding-usd text-emerald-600"></i> Input Data Pembayaran
            </h3>
            <button onclick="closeInputPembayaranModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>

        <form id="formInputPembayaran" onsubmit="submitFormInputPembayaran(event)" class="space-y-4 text-xs">
            <!-- 1. Cari & Pilih Siswa -->
            <div>
                <label class="block font-bold text-gray-700 mb-1">Cari Siswa <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="modalCariSiswa" oninput="searchModalSiswa(this.value)" placeholder="Ketik nama atau NISN siswa..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                    <div id="modalSiswaResults" class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-gray-100 z-30 hidden max-h-48 overflow-y-auto divide-y divide-gray-50"></div>
                </div>
                <input type="hidden" id="modalSelectedSiswaId">
                <div id="modalSelectedSiswaBadge" class="mt-2 hidden p-2.5 bg-blue-50 border border-blue-100 rounded-xl flex justify-between items-center text-xs">
                    <div>
                        <div class="font-bold text-blue-900" id="badgeNamaSiswa">-</div>
                        <div class="text-[10px] text-blue-600 mt-0.5" id="badgeNisnKelas">-</div>
                    </div>
                    <button type="button" onclick="clearSelectedSiswa()" class="text-red-500 hover:text-red-700 text-xs font-bold"><i class="fas fa-times"></i> Ganti</button>
                </div>
            </div>

            <!-- 2. Pilih Pos Tagihan -->
            <div>
                <label class="block font-bold text-gray-700 mb-1">Pilih Tagihan / Pos Keuangan <span class="text-red-500">*</span></label>
                <select id="modalSelectTagihan" onchange="onTagihanSelected()" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs font-bold text-gray-800 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Siswa Terlebih Dahulu --</option>
                </select>
            </div>

            <!-- Info Tagihan Box -->
            <div id="modalTagihanInfoBox" class="hidden p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                <div class="flex justify-between text-[11px] text-gray-600">
                    <span>Total Tagihan:</span>
                    <span class="font-bold" id="boxTagihanNominal">Rp 0</span>
                </div>
                <div class="flex justify-between text-[11px] text-gray-600">
                    <span>Sudah Terbayar:</span>
                    <span class="font-bold text-emerald-600" id="boxTagihanTerbayar">Rp 0</span>
                </div>
                <div class="flex justify-between text-xs pt-1 border-t border-gray-200">
                    <span class="font-bold text-gray-700">Sisa Tunggakan:</span>
                    <span class="font-bold text-red-600" id="boxTagihanSisa">Rp 0</span>
                </div>
            </div>

            <!-- 3. Nominal Pembayaran -->
            <div>
                <label class="block font-bold text-gray-700 mb-1">Nominal Pembayaran (Rp) <span class="text-red-500">*</span></label>
                <input type="number" id="modalNominalBayar" required min="1000" step="1000" placeholder="0" class="w-full bg-emerald-50/60 border border-emerald-300 rounded-xl p-3 text-sm font-bold text-emerald-800 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- 4. Metode Pembayaran -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Metode Pembayaran</label>
                    <select id="modalMetodeBayar" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                        <option value="Tunai">Tunai / Cash</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="QRIS">QRIS / E-Wallet</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Catatan</label>
                    <input type="text" id="modalKeterangan" placeholder="Contoh: Lunas / Titipan" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 pt-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="closeInputPembayaranModal()" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" id="btnSubmitModalBayar" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPage = 1;
let searchDebounceTimeout = null;
let modalSearchTimeout = null;
let currentModalTagihanList = [];

document.addEventListener('DOMContentLoaded', () => {
    loadTableData();
});

function formatRupiah(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

function debounceSearch() {
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        currentPage = 1;
        loadTableData();
    }, 300);
}

function resetFilter() {
    document.getElementById('filterKelas').value = '';
    document.getElementById('filterPos').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('searchInput').value = '';
    currentPage = 1;
    loadTableData();
}

async function loadTableData(page = 1) {
    currentPage = page;
    const kelas = document.getElementById('filterKelas')?.value || '';
    const posId = document.getElementById('filterPos')?.value || '';
    const status = document.getElementById('filterStatus')?.value || '';
    const search = document.getElementById('searchInput')?.value || '';

    const tbody = document.getElementById('tbodyTagihan');
    tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data tagihan...</td></tr>`;

    try {
        const url = new URL("{{ url('/keuangan/pembayaran/data') }}", window.location.origin);
        url.searchParams.set('page', page);
        if (kelas) url.searchParams.set('kelas', kelas);
        if (posId) url.searchParams.set('pos_id', posId);
        if (status) url.searchParams.set('status', status);
        if (search) url.searchParams.set('search', search);

        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' }
        });
        const resData = await res.json();

        if (resData.success) {
            // Update Summary
            const sum = resData.summary || {};
            document.getElementById('statPemasukan').textContent = formatRupiah(sum.total_pemasukan);
            document.getElementById('statLunas').textContent = sum.tagihan_lunas || 0;
            document.getElementById('statSiswa').textContent = sum.siswa_tercakup || 0;
            document.getElementById('statTunggakan').textContent = formatRupiah(sum.total_tunggakan);

            // Render Table
            renderTableRows(resData.data, resData.meta);
        }
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-red-500">Gagal memuat data tagihan.</td></tr>`;
    }
}

const canInputPayment = {{ auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara']) ? 'true' : 'false' }};

function renderTableRows(data, meta) {
    const tbody = document.getElementById('tbodyTagihan');
    if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-gray-400">Tidak ada data tagihan ditemukan.</td></tr>`;
        document.getElementById('pageInfo').textContent = 'Menampilkan 0 data';
        document.getElementById('paginationControls').innerHTML = '';
        return;
    }

    const startNo = (meta.current_page - 1) * 20 + 1;
    tbody.innerHTML = data.map((r, idx) => {
        let statusBadge = `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">Belum Bayar</span>`;
        if (r.status === 'lunas') {
            statusBadge = `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas</span>`;
        } else if (r.status === 'cicilan') {
            statusBadge = `<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Cicilan</span>`;
        }

        const posName = r.pos_keuangan ? r.pos_keuangan.nama : '-';
        const bulanLabel = r.bulan ? ` (${r.bulan})` : '';

        let actionCol = '';
        if (canInputPayment) {
            if (r.status !== 'lunas') {
                actionCol = `
                    <button type="button" onclick="quickPay(${r.siswa_id}, ${r.id}, '${escapeString(r.siswa ? r.siswa.nama : '')}', '${escapeString(posName + bulanLabel)}', ${r.sisa})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold shadow-sm transition">
                        <i class="fas fa-hand-holding-usd"></i> Bayar
                    </button>
                `;
            } else {
                actionCol = `<span class="text-[11px] font-bold text-emerald-600"><i class="fas fa-check-circle"></i> Selesai</span>`;
            }
        } else {
            actionCol = r.status === 'lunas'
                ? `<span class="text-[11px] font-bold text-emerald-600"><i class="fas fa-check-circle"></i> Lunas</span>`
                : `<span class="text-[11px] font-bold text-amber-600">Menunggu</span>`;
        }

        return `
            <tr class="hover:bg-gray-50 transition">
                <td class="p-3 text-center font-bold text-gray-400">${startNo + idx}</td>
                <td class="p-3">
                    <div class="font-bold text-gray-800">${r.siswa ? r.siswa.nama : '-'}</div>
                    <div class="text-[10px] text-gray-400 font-mono">NISN: ${r.siswa ? r.siswa.nisn : '-'}</div>
                </td>
                <td class="p-3 font-semibold text-gray-600">${r.siswa ? (r.siswa.kelas || '-') : '-'}</td>
                <td class="p-3">
                    <div class="font-bold text-gray-700">${posName}${bulanLabel}</div>
                    <div class="text-[10px] text-gray-400 uppercase">${r.tahun_ajaran || ''}</div>
                </td>
                <td class="p-3 text-right font-bold text-gray-800">${formatRupiah(r.nominal)}</td>
                <td class="p-3 text-right font-bold text-emerald-600">${formatRupiah(r.terbayar)}</td>
                <td class="p-3 text-right font-bold text-red-600">${formatRupiah(r.sisa)}</td>
                <td class="p-3 text-center">${statusBadge}</td>
                <td class="p-3 text-center">
                    ${actionCol}
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('pageInfo').textContent = `Menampilkan halaman ${meta.current_page} dari ${meta.last_page} (${meta.total} total data)`;
    
    // Pagination Controls
    let pagHtml = '';
    if (meta.current_page > 1) {
        pagHtml += `<button type="button" onclick="loadTableData(${meta.current_page - 1})" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100">Prev</button>`;
    }
    if (meta.current_page < meta.last_page) {
        pagHtml += `<button type="button" onclick="loadTableData(${meta.current_page + 1})" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100">Next</button>`;
    }
    document.getElementById('paginationControls').innerHTML = pagHtml;
}

function escapeString(str) {
    return String(str || '').replace(/'/g, "\\'");
}

// ==========================================
// MODAL INPUT PEMBAYARAN
// ==========================================
function openInputPembayaranModal() {
    document.getElementById('modalInputPembayaran').classList.remove('hidden');
    clearSelectedSiswa();
}

function closeInputPembayaranModal() {
    document.getElementById('modalInputPembayaran').classList.add('hidden');
}

function searchModalSiswa(val) {
    clearTimeout(modalSearchTimeout);
    const resBox = document.getElementById('modalSiswaResults');
    if (!val || val.trim().length < 2) {
        resBox.classList.add('hidden');
        return;
    }

    modalSearchTimeout = setTimeout(async () => {
        try {
            const res = await fetch(`{{ url('/keuangan/cari-siswa') }}?q=${encodeURIComponent(val)}`);
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                resBox.innerHTML = data.data.map(s => `
                    <div onclick="selectModalSiswa(${s.id}, '${escapeString(s.nama)}', '${escapeString(s.nisn)}', '${escapeString(s.kelas || '-')}')" class="p-3 hover:bg-blue-50 cursor-pointer flex justify-between items-center">
                        <div>
                            <div class="font-bold text-gray-800 text-xs">${s.nama}</div>
                            <div class="text-[10px] text-gray-400 font-mono">NISN: ${s.nisn}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-[10px] font-bold">${s.kelas || '-'}</span>
                    </div>
                `).join('');
                resBox.classList.remove('hidden');
            } else {
                resBox.innerHTML = `<div class="p-3 text-center text-gray-400 text-xs">Siswa tidak ditemukan</div>`;
                resBox.classList.remove('hidden');
            }
        } catch (e) {}
    }, 250);
}

async function selectModalSiswa(siswaId, nama, nisn, kelas) {
    document.getElementById('modalSiswaResults').classList.add('hidden');
    document.getElementById('modalCariSiswa').value = '';
    document.getElementById('modalSelectedSiswaId').value = siswaId;

    document.getElementById('badgeNamaSiswa').textContent = nama;
    document.getElementById('badgeNisnKelas').textContent = `NISN: ${nisn} | Kelas: ${kelas}`;
    document.getElementById('modalSelectedSiswaBadge').classList.remove('hidden');

    // Fetch tagihan siswa
    const selTagihan = document.getElementById('modalSelectTagihan');
    selTagihan.innerHTML = `<option value="">Memuat daftar tagihan siswa...</option>`;

    try {
        const res = await fetch(`{{ url('/keuangan/tagihan-siswa') }}/${siswaId}`);
        const data = await res.json();
        if (data.success) {
            currentModalTagihanList = data.tagihan || [];
            if (currentModalTagihanList.length === 0) {
                selTagihan.innerHTML = `<option value="">Tidak ada tagihan untuk siswa ini</option>`;
                return;
            }

            selTagihan.innerHTML = `<option value="">-- Pilih Pos Tagihan --</option>` + currentModalTagihanList.map(t => {
                const posName = t.pos_keuangan ? t.pos_keuangan.nama : 'Pos';
                const bln = t.bulan ? ` (${t.bulan})` : '';
                const sisa = formatRupiah(t.sisa);
                const status = t.status === 'lunas' ? ' [LUNAS]' : ` [Sisa: ${sisa}]`;
                return `<option value="${t.id}" ${t.status === 'lunas' ? 'disabled' : ''}>${posName}${bln} ${status}</option>`;
            }).join('');
        }
    } catch (e) {
        selTagihan.innerHTML = `<option value="">Gagal memuat tagihan</option>`;
    }
}

function clearSelectedSiswa() {
    document.getElementById('modalSelectedSiswaId').value = '';
    document.getElementById('modalSelectedSiswaBadge').classList.add('hidden');
    document.getElementById('modalSelectTagihan').innerHTML = `<option value="">-- Pilih Siswa Terlebih Dahulu --</option>`;
    document.getElementById('modalTagihanInfoBox').classList.add('hidden');
    document.getElementById('modalNominalBayar').value = '';
}

function onTagihanSelected() {
    const tagihanId = Number(document.getElementById('modalSelectTagihan').value);
    const tagihan = currentModalTagihanList.find(t => t.id === tagihanId);
    const infoBox = document.getElementById('modalTagihanInfoBox');

    if (!tagihan) {
        infoBox.classList.add('hidden');
        document.getElementById('modalNominalBayar').value = '';
        return;
    }

    document.getElementById('boxTagihanNominal').textContent = formatRupiah(tagihan.nominal);
    document.getElementById('boxTagihanTerbayar').textContent = formatRupiah(tagihan.terbayar);
    document.getElementById('boxTagihanSisa').textContent = formatRupiah(tagihan.sisa);
    document.getElementById('modalNominalBayar').value = tagihan.sisa;
    infoBox.classList.remove('hidden');
}

function quickPay(siswaId, tagihanId, namaSiswa, posNama, sisa) {
    openInputPembayaranModal();
    selectModalSiswa(siswaId, namaSiswa, '', '');
    setTimeout(() => {
        document.getElementById('modalSelectTagihan').value = tagihanId;
        onTagihanSelected();
    }, 500);
}

async function submitFormInputPembayaran(e) {
    e.preventDefault();
    const siswaId = document.getElementById('modalSelectedSiswaId').value;
    const tagihanId = document.getElementById('modalSelectTagihan').value;
    const nominal = document.getElementById('modalNominalBayar').value;
    const metode = document.getElementById('modalMetodeBayar').value;
    const keterangan = document.getElementById('modalKeterangan').value;

    if (!siswaId || !tagihanId || !nominal) {
        alert('Silakan lengkapi data pembayaran.');
        return;
    }

    const btn = document.getElementById('btnSubmitModalBayar');
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`;

    try {
        const res = await fetch("{{ url('/keuangan/bayar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                siswa_id: siswaId,
                tagihan_id: tagihanId,
                nominal_bayar: nominal,
                metode_pembayaran: metode,
                keterangan: keterangan
            })
        });

        const result = await res.json();
        if (result.success) {
            closeInputPembayaranModal();
            loadTableData(currentPage);

            if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    text: result.message || 'Transaksi pembayaran telah tersimpan.',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-print"></i> Cetak Kuitansi',
                    cancelButtonText: 'Tutup',
                    confirmButtonColor: '#10B981',
                }).then((r) => {
                    if (r.isConfirmed && result.data && result.data.id) {
                        window.open(`{{ url('/keuangan/kuitansi') }}/${result.data.id}`, '_blank');
                    }
                });
            } else {
                alert('Pembayaran berhasil!');
            }
        } else {
            alert(result.message || 'Gagal menyimpan pembayaran.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-check-circle"></i> Simpan Pembayaran`;
    }
}
</script>
@endsection
