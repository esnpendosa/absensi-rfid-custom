@extends('layouts.page')

@section('title', 'Kategori Pos Keuangan')

@section('content')
<div class="view-section active animate-fade-in space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-coins text-blue-600"></i> Kategori & Pos Keuangan Sekolah
            </h2>
            <p class="text-xs text-gray-500 mt-1">Kelola pos pembayaran secara dinamis (SPP, Uang Gedung, Ujian, Seragam, dll).</p>
        </div>
        @if(auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara']))
        <button onclick="openModalTambahPos()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition transform active:scale-95">
            <i class="fas fa-plus"></i> Tambah Kategori Pos
        </button>
        @endif
    </div>

    <!-- Table Pos Keuangan -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-xs uppercase tracking-wider text-gray-600">Daftar Kategori Pembayaran Aktif</h3>
            <button onclick="loadPosData()" class="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Kode Pos</th>
                        <th class="p-4">Nama Pembayaran</th>
                        <th class="p-4">Tipe Pembayaran</th>
                        <th class="p-4">Nominal Default</th>
                        <th class="p-4">Tahun Ajaran</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody id="posTableBody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-400">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Memuat data pos keuangan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Pos Keuangan -->
<div id="modalPos" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 animate-scale-up">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 id="modalPosTitle" class="font-bold text-sm text-gray-800">Tambah Pos Keuangan</h3>
            <button onclick="closeModalPos()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="formPos" onsubmit="savePos(event)">
            <input type="hidden" id="pos_id" name="pos_id">
            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Kode Pos <span class="text-red-500">*</span></label>
                        <input type="text" id="pos_kode" name="kode" required placeholder="Contoh: SPP / GEDUNG" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs font-mono uppercase focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tipe Pembayaran <span class="text-red-500">*</span></label>
                        <select id="pos_tipe" name="tipe" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="bulanan">Bulanan (Per Bulan)</option>
                            <option value="bebas">Bebas / Angsuran (Bisa Dicicil)</option>
                            <option value="sekali_bayar">Sekali Bayar (1x Lunas)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Nama Kategori Pembayaran <span class="text-red-500">*</span></label>
                    <input type="text" id="pos_nama" name="nama" required placeholder="Contoh: SPP Bulanan / Uang Gedung DSP" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nominal Default <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-bold text-xs">Rp</span>
                            <input type="text" id="pos_nominal" required oninput="formatRupiahInput(this)" placeholder="150.000" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-3 py-2.5 text-xs font-bold text-gray-900 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tahun Ajaran</label>
                        <input type="text" id="pos_tahun" name="tahun_ajaran" value="2026/2027" placeholder="2026/2027" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Opsi Tarif Berbeda Tiap Tingkat Kelas -->
                <div class="bg-blue-50/50 p-3.5 rounded-xl border border-blue-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-800 flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="check_tarif_kelas" onchange="toggleTarifKelasSection(this.checked)" class="rounded text-blue-600 focus:ring-blue-500">
                            <span>Atur Tarif Khusus Tiap Tingkat Kelas</span>
                        </label>
                        <span class="text-[10px] text-blue-600 font-semibold bg-blue-100/50 px-2 py-0.5 rounded-md">Beda Tiap Tingkat</span>
                    </div>
                    <div id="section_tarif_kelas" class="hidden grid grid-cols-3 gap-2.5 pt-1">
                        <div>
                            <label class="block font-bold text-gray-600 text-[10px] mb-1">Tarif Kelas X</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-gray-400 font-bold text-[10px]">Rp</span>
                                <input type="text" id="tarif_kelas_x" oninput="formatRupiahInput(this)" placeholder="Default" class="w-full bg-white border border-gray-200 rounded-lg pl-7 pr-2 py-1.5 text-xs font-bold text-gray-800">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-600 text-[10px] mb-1">Tarif Kelas XI</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-gray-400 font-bold text-[10px]">Rp</span>
                                <input type="text" id="tarif_kelas_xi" oninput="formatRupiahInput(this)" placeholder="Default" class="w-full bg-white border border-gray-200 rounded-lg pl-7 pr-2 py-1.5 text-xs font-bold text-gray-800">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-600 text-[10px] mb-1">Tarif Kelas XII</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-gray-400 font-bold text-[10px]">Rp</span>
                                <input type="text" id="tarif_kelas_xii" oninput="formatRupiahInput(this)" placeholder="Default" class="w-full bg-white border border-gray-200 rounded-lg pl-7 pr-2 py-1.5 text-xs font-bold text-gray-800">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Target Kelas Yang Dikenakan Pos Ini -->
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-2">
                    <label class="block font-bold text-gray-700 text-xs">Target Siswa Yang Dikenakan Tagihan:</label>
                    <div class="flex flex-wrap gap-4 text-xs">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="target_tingkat_radio" value="all" checked onchange="handleTargetRadioChange('all')" class="text-blue-600 focus:ring-blue-500">
                            <span class="font-medium text-gray-700">Semua Siswa</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="target_tingkat_radio" value="X" onchange="handleTargetRadioChange('X')" class="text-blue-600 focus:ring-blue-500">
                            <span class="font-medium text-gray-700">Hanya Kelas X</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="target_tingkat_radio" value="XI" onchange="handleTargetRadioChange('XI')" class="text-blue-600 focus:ring-blue-500">
                            <span class="font-medium text-gray-700">Hanya Kelas XI</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="target_tingkat_radio" value="XII" onchange="handleTargetRadioChange('XII')" class="text-blue-600 focus:ring-blue-500">
                            <span class="font-medium text-gray-700">Hanya Kelas XII</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Deskripsi / Keterangan</label>
                    <textarea id="pos_deskripsi" name="deskripsi" rows="2" placeholder="Catatan opsional..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeModalPos()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" id="btnSubmitPos" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadPosData();
});

let posListGlobal = [];

function toggleTarifKelasSection(show) {
    const el = document.getElementById('section_tarif_kelas');
    if (show) {
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function handleTargetRadioChange(val) {}

async function loadPosData() {
    try {
        const res = await fetch("{{ route('keuangan.pos.data') }}");
        const json = await res.json();
        if (!json.success) return;
        posListGlobal = json.data;
        renderPosTable(json.data);
    } catch (e) {
        console.error(e);
    }
}

const canManagePos = {{ auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara']) ? 'true' : 'false' }};

function renderPosTable(data) {
    const tbody = document.getElementById('posTableBody');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-gray-400">Belum ada kategori pos keuangan.</td></tr>`;
        return;
    }

    const typeBadge = {
        bulanan: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Bulanan</span>',
        bebas: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Angsuran / Bebas</span>',
        sekali_bayar: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Sekali Bayar</span>',
    };

    tbody.innerHTML = data.map((item, idx) => {
        const actionHtml = canManagePos ? `
            <div class="flex items-center justify-center gap-1.5">
                <button onclick="editPos(${item.id})" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition" title="Edit">
                    <i class="fas fa-edit text-xs"></i>
                </button>
                <button onclick="deletePos(${item.id})" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition" title="Hapus">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </div>
        ` : `<span class="text-[11px] text-gray-400 font-medium">Read-Only</span>`;

        let tarifDetailHtml = '';
        if (item.tarif_per_kelas && Object.keys(item.tarif_per_kelas).length > 0) {
            const parts = [];
            if (item.tarif_per_kelas.X) parts.push(`X: Rp ${Number(item.tarif_per_kelas.X).toLocaleString('id-ID')}`);
            if (item.tarif_per_kelas.XI) parts.push(`XI: Rp ${Number(item.tarif_per_kelas.XI).toLocaleString('id-ID')}`);
            if (item.tarif_per_kelas.XII) parts.push(`XII: Rp ${Number(item.tarif_per_kelas.XII).toLocaleString('id-ID')}`);
            if (parts.length > 0) {
                tarifDetailHtml = `<div class="text-[10px] text-blue-600 font-semibold mt-0.5">${parts.join(' | ')}</div>`;
            }
        }

        let targetBadgeHtml = '';
        if (item.target_kelas && item.target_kelas.length > 0 && !item.target_kelas.includes('all')) {
            targetBadgeHtml = `<span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Khusus: Kelas ${item.target_kelas.join(', ')}</span>`;
        }

        return `
            <tr class="hover:bg-gray-50/70 transition">
                <td class="p-4 text-center font-bold text-gray-400">${idx + 1}</td>
                <td class="p-4 font-mono font-bold text-blue-700">${item.kode}</td>
                <td class="p-4">
                    <div class="font-bold text-gray-800">${item.nama}</div>
                    <div class="text-[10px] text-gray-400">${item.deskripsi || '-'}</div>
                    ${targetBadgeHtml}
                </td>
                <td class="p-4">${typeBadge[item.tipe] || item.tipe}</td>
                <td class="p-4">
                    <div class="font-bold text-gray-900">Rp ${Number(item.nominal_default).toLocaleString('id-ID')}</div>
                    ${tarifDetailHtml}
                </td>
                <td class="p-4 text-gray-600">${item.tahun_ajaran || '-'}</td>
                <td class="p-4 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold ${item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">
                        ${item.is_active ? 'Aktif' : 'Nonaktif'}
                    </span>
                </td>
                <td class="p-4 text-center">
                    ${actionHtml}
                </td>
            </tr>
        `;
    }).join('');
}

function formatRupiahInput(el) {
    let val = el.value.replace(/\D/g, '');
    if (!val) {
        el.value = '';
        return;
    }
    el.value = Number(val).toLocaleString('id-ID');
}

function cleanRupiah(val) {
    return Number(String(val || '').replace(/\D/g, '')) || 0;
}

function openModalTambahPos() {
    document.getElementById('modalPosTitle').innerText = 'Tambah Pos Keuangan';
    document.getElementById('formPos').reset();
    document.getElementById('pos_id').value = '';
    document.getElementById('pos_nominal').value = '';
    document.getElementById('check_tarif_kelas').checked = false;
    toggleTarifKelasSection(false);
    document.getElementById('tarif_kelas_x').value = '';
    document.getElementById('tarif_kelas_xi').value = '';
    document.getElementById('tarif_kelas_xii').value = '';
    const radios = document.getElementsByName('target_tingkat_radio');
    for (let r of radios) { if (r.value === 'all') r.checked = true; }
    document.getElementById('modalPos').classList.remove('hidden');
}

function editPos(id) {
    const item = posListGlobal.find(p => p.id === id);
    if (!item) return;

    document.getElementById('modalPosTitle').innerText = 'Edit Pos Keuangan';
    document.getElementById('pos_id').value = item.id;
    document.getElementById('pos_kode').value = item.kode;
    document.getElementById('pos_nama').value = item.nama;
    document.getElementById('pos_tipe').value = item.tipe;
    document.getElementById('pos_nominal').value = Number(item.nominal_default || 0).toLocaleString('id-ID');
    document.getElementById('pos_tahun').value = item.tahun_ajaran || '2026/2027';
    document.getElementById('pos_deskripsi').value = item.deskripsi || '';

    // Set tarif per kelas
    const tf = item.tarif_per_kelas || {};
    const hasTf = Object.keys(tf).length > 0 && (tf.X || tf.XI || tf.XII);
    document.getElementById('check_tarif_kelas').checked = !!hasTf;
    toggleTarifKelasSection(!!hasTf);
    document.getElementById('tarif_kelas_x').value = tf.X ? Number(tf.X).toLocaleString('id-ID') : '';
    document.getElementById('tarif_kelas_xi').value = tf.XI ? Number(tf.XI).toLocaleString('id-ID') : '';
    document.getElementById('tarif_kelas_xii').value = tf.XII ? Number(tf.XII).toLocaleString('id-ID') : '';

    // Set target kelas
    const targets = item.target_kelas || ['all'];
    const targetVal = targets.length === 1 && ['X', 'XI', 'XII'].includes(targets[0]) ? targets[0] : 'all';
    const radios = document.getElementsByName('target_tingkat_radio');
    for (let r of radios) {
        r.checked = (r.value === targetVal);
    }

    document.getElementById('modalPos').classList.remove('hidden');
}

function closeModalPos() {
    document.getElementById('modalPos').classList.add('hidden');
}

async function savePos(e) {
    e.preventDefault();
    const id = document.getElementById('pos_id').value;
    const url = id ? `/keuangan/pos/${id}` : "{{ url('/keuangan/pos') }}";
    const method = id ? 'PUT' : 'POST';

    const useTarifKelas = document.getElementById('check_tarif_kelas').checked;
    let tarifPerKelas = null;
    if (useTarifKelas) {
        tarifPerKelas = {};
        const valX = cleanRupiah(document.getElementById('tarif_kelas_x').value);
        const valXi = cleanRupiah(document.getElementById('tarif_kelas_xi').value);
        const valXii = cleanRupiah(document.getElementById('tarif_kelas_xii').value);
        if (valX > 0) tarifPerKelas.X = valX;
        if (valXi > 0) tarifPerKelas.XI = valXi;
        if (valXii > 0) tarifPerKelas.XII = valXii;
    }

    let targetKelas = ['all'];
    const radios = document.getElementsByName('target_tingkat_radio');
    for (let r of radios) {
        if (r.checked && r.value !== 'all') {
            targetKelas = [r.value];
        }
    }

    const payload = {
        kode: document.getElementById('pos_kode').value,
        nama: document.getElementById('pos_nama').value,
        tipe: document.getElementById('pos_tipe').value,
        nominal_default: cleanRupiah(document.getElementById('pos_nominal').value),
        tahun_ajaran: document.getElementById('pos_tahun').value,
        deskripsi: document.getElementById('pos_deskripsi').value,
        tarif_per_kelas: tarifPerKelas,
        target_kelas: targetKelas,
    };

    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            closeModalPos();
            loadPosData();
        } else {
            alert(json.message || 'Gagal menyimpan data.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}

async function deletePos(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus kategori pos keuangan ini?')) return;
    try {
        const res = await fetch(`/keuangan/pos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const json = await res.json();
        if (json.success) {
            loadPosData();
        }
    } catch (e) {
        console.error(e);
    }
}
</script>
@endsection
