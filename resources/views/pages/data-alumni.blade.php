@extends('layouts.page')

@section('title', 'Direktori Alumni & Tracer Study')

@section('content')
<div id="view-data-alumni" class="view-section active animate-fade-in space-y-4">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Header -->
      <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-gray-50/30">
        <div>
            <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-graduate text-blue-600"></i> Direktori Alumni & Tracer Study
            </h3>
            <p class="text-xs text-gray-500">Kelola arsip lulusan dan riwayat penelusuran karir / studi alumni.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap justify-end">
            <button type="button" id="refreshAlumniBtn" class="bg-white text-gray-600 border border-gray-200 px-3 py-2 rounded-lg text-xs font-bold shadow-sm hover:bg-gray-50 hover:text-indigo-600 transition" title="Perbarui Data">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button type="button" onclick="openModalTambahAlumni()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <i class="fas fa-plus-circle"></i> Tambah Alumni
            </button>
            <a href="{{ url('/kenaikan-kelas') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <i class="fas fa-graduation-cap"></i> Luluskan Siswa Kelas XII
            </a>
        </div>
      </div>

      <!-- Filters -->
      <div class="p-4 bg-white border-b border-gray-100 flex flex-col xl:flex-row justify-between items-center gap-4">
          <div class="flex flex-col sm:flex-row items-center gap-3 text-xs w-full xl:w-auto">
              <div class="flex items-center gap-2 w-full sm:w-auto">
                  <span class="text-gray-500 font-bold whitespace-nowrap">Show</span>
                  <select id="alumniPerPage" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2 w-full sm:w-auto cursor-pointer">
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                      <option value="all">Semua</option>
                  </select>
              </div>

              <select id="filterKelasAlumni" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2 w-full sm:w-44 font-bold shadow-sm cursor-pointer">
                    <option value="">Semua Kelas Terakhir</option>
              </select>

              <select id="filterTahunAlumni" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2 w-full sm:w-36 font-bold shadow-sm cursor-pointer">
                    <option value="">Semua Tahun</option>
              </select>

              <select id="filterTracerAlumni" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2 w-full sm:w-44 font-bold shadow-sm cursor-pointer">
                    <option value="">Semua Status Tracer</option>
                    <option value="Kuliah">Kuliah</option>
                    <option value="Bekerja">Bekerja</option>
                    <option value="Wirausaha">Wirausaha</option>
                    <option value="Mencari Kerja">Mencari Kerja</option>
                    <option value="Belum Diisi">Belum Diisi</option>
              </select>
          </div>

          <div class="relative w-full xl:w-72">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400 text-xs"></i>
              </div>
              <input type="text" id="searchAlumniInput" class="bg-gray-50 border border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2 transition-all" placeholder="Cari nama / NISN / kontak...">
          </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
              <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-semibold border-b border-gray-200">
                  <tr>
                      <th class="p-3 text-center w-12">No</th>
                      <th class="p-3">Nama Lengkap</th>
                      <th class="p-3 hidden md:table-cell">NISN</th>
                      <th class="p-3 hidden sm:table-cell">Kelas & Tahun</th>
                      <th class="p-3">Tracer Study (Kuliah/Kerja)</th>
                      <th class="p-3 hidden xl:table-cell">Kontak</th>
                      <th class="p-3 text-center w-36">Aksi</th>
                  </tr>
              </thead>
              <tbody id="tbody-alumni" class="divide-y divide-gray-50 bg-white text-xs text-gray-700"></tbody>
          </table>
      </div>

      <!-- Footer Pagination -->
      <div id="footer-alumni" class="p-4 border-t border-gray-100 bg-gray-50/30 flex justify-between items-center text-xs text-gray-500">
          <span id="info-alumni">Menampilkan 0 data</span>
          <div class="flex gap-1">
              <button type="button" id="btn-prev-alumni" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50 transition shadow-sm">Prev</button>
              <button type="button" id="btn-next-alumni" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50 transition shadow-sm">Next</button>
          </div>
      </div>
  </div>
</div>

<!-- MODAL TAMBAH ALUMNI BARU -->
<div id="modalTambahAlumni" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 animate-scale-up max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-plus text-emerald-600"></i> Tambah Data Alumni
            </h3>
            <button onclick="closeModalTambahAlumni()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>

        <form id="formTambahAlumni" onsubmit="submitFormTambahAlumni(event)" class="space-y-3.5 text-xs">
            <div>
                <label class="block font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="tambah_nama" required placeholder="Nama lengkap alumni" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">NISN <span class="text-red-500">*</span></label>
                    <input type="text" id="tambah_nisn" required placeholder="NISN alumni" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                    <select id="tambah_jk" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Kelas Terakhir</label>
                    <input type="text" id="tambah_kelas" placeholder="Contoh: XII TKJ 1 / XII" value="XII" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Tahun Lulus <span class="text-red-500">*</span></label>
                    <input type="number" id="tambah_tahun" required value="{{ date('Y') }}" min="2000" max="2099" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">No. WhatsApp / Kontak</label>
                <input type="text" id="tambah_kontak" placeholder="08xxxxxxxxxx" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs">
            </div>

            <div class="pt-2 border-t border-gray-100">
                <div class="font-bold text-gray-700 text-xs mb-2 text-blue-700">Tracer Study Awal (Opsional)</div>
                <div class="space-y-3">
                    <div>
                        <label class="block font-bold text-gray-600 mb-1">Status Aktivitas Saat Ini</label>
                        <select id="tambah_status_tracer" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs">
                            <option value="Belum Diisi">Belum Diisi</option>
                            <option value="Bekerja">Bekerja</option>
                            <option value="Kuliah">Kuliah (Perguruan Tinggi)</option>
                            <option value="Wirausaha">Wirausaha / Usaha Sendiri</option>
                            <option value="Mencari Kerja">Mencari Kerja</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-gray-600 mb-1">Nama Instansi / Kampus</label>
                            <input type="text" id="tambah_instansi" placeholder="PT... / Univ..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-600 mb-1">Posisi / Jurusan</label>
                            <input type="text" id="tambah_posisi" placeholder="Teknik / Staf..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="closeModalTambahAlumni()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" id="btnSubmitTambahAlumni" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-1.5">
                    <i class="fas fa-check"></i> Simpan Alumni
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambahAlumni() {
    document.getElementById('formTambahAlumni').reset();
    document.getElementById('tambah_tahun').value = new Date().getFullYear();
    document.getElementById('tambah_kelas').value = 'XII';
    document.getElementById('modalTambahAlumni').classList.remove('hidden');
}

function closeModalTambahAlumni() {
    document.getElementById('modalTambahAlumni').classList.add('hidden');
}

async function submitFormTambahAlumni(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitTambahAlumni');
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`;

    const payload = {
        nama: document.getElementById('tambah_nama').value,
        nisn: document.getElementById('tambah_nisn').value,
        jenis_kelamin: document.getElementById('tambah_jk').value,
        kelas_terakhir: document.getElementById('tambah_kelas').value,
        tahun_lulus: document.getElementById('tambah_tahun').value,
        kontak: document.getElementById('tambah_kontak').value,
        status_alumni: document.getElementById('tambah_status_tracer').value,
        nama_instansi: document.getElementById('tambah_instansi').value,
        jurusan_posisi: document.getElementById('tambah_posisi').value,
    };

    try {
        const res = await fetch("{{ url('/data-alumni/store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.success) {
            closeModalTambahAlumni();
            document.getElementById('refreshAlumniBtn')?.click();
            alert('Data alumni berhasil ditambahkan!');
        } else {
            alert(data.message || 'Gagal menambahkan alumni.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-check"></i> Simpan Alumni`;
    }
}
</script>
@endsection

@push('scripts')
@include('partials.page-script', ['name' => 'data-alumni'])
@endpush
