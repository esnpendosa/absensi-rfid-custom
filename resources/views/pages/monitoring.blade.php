@extends('layouts.page')

@section('title', 'Monitoring Realtime')

@section('content')
<div id="view-monitoring" class="view-section active animate-fade-in">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center bg-gray-50/30 gap-4">
            <div>
                <h3 class="font-bold text-sm text-gray-800 mb-1">Monitoring Kehadiran</h3>
                <p class="text-xs text-gray-500 font-medium">Data Realtime: <span id="monitoringDate" class="text-indigo-600 font-bold">...</span></p>
            </div>
            
            <div class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">
                @if (auth()->user()?->hasAnyRole(['admin', 'wakel', 'super-admin']))
                    <button onclick="markMonitoringPulangMassal()" id="btnMonitoringPulangMassal" disabled title="Memuat mode absensi..." class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-indigo-700 disabled:hover:bg-indigo-600 disabled:opacity-60 disabled:cursor-not-allowed transition transform active:scale-95 flex items-center gap-2 w-full md:w-auto justify-center">
                        <i class="fas fa-right-from-bracket"></i> <span class="hidden sm:inline">Absen Pulang Massal</span>
                    </button>
                @endif

                <button onclick="exportMonitoringExcel()" id="btnExportMonitoring" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-emerald-700 transition transform active:scale-95 flex items-center gap-2 w-full md:w-auto justify-center">
                    <i class="fas fa-file-excel"></i> <span class="hidden sm:inline">Export Excel</span>
                </button>

                <button onclick="refreshData('monitoring')" class="bg-white text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-gray-50 hover:text-indigo-600 transition w-full md:w-auto" title="Perbarui Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <div class="p-4 bg-white border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            
            <div class="flex flex-col sm:flex-row items-center gap-2 text-xs w-full md:w-auto">
                
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-gray-500 font-bold hidden sm:inline">Show</span>
                    <select onchange="handleTableLimit('monitoring', this.value)" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2 w-full sm:w-auto cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">Semua</option>
                    </select>
                </div>

                <select id="monitoringKelas" onchange="handleTableClassFilter('monitoring', this.value)" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2 font-bold w-full sm:w-auto shadow-sm cursor-pointer">
                    <option value="">Semua Kelas</option>
                </select>

                <select id="monitoringStatusFilter" onchange="handleTableStatusFilter('monitoring', this.value)" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2 font-bold w-full sm:w-auto shadow-sm cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="Masuk">Masuk (Biru Muda)</option>
                    <option value="Hadir">Hadir (Hijau)</option>
                    <option value="Terlambat">Terlambat / Telat (Oranye / Merah)</option>
                    <option value="Sakit">Sakit (Kuning)</option>
                    <option value="Izin">Izin (Biru)</option>
                    <option value="Alpa">Alpa (Merah)</option>
                    <option value="Belum Absen">Belum Absen (Abu)</option>
                </select>
            </div>

            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs"></i>
                </div>
                <input type="text" oninput="handleTableSearch('monitoring', this.value)" class="bg-gray-50 border border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2 transition-all" placeholder="Cari Nama / Kelas...">
            </div>
        </div>

        <div class="overflow-x-auto">
                </select>
            </div>

            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs"></i>
                </div>
                <input type="text" oninput="handleTableSearch('monitoring', this.value)" class="bg-gray-50 border border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2 transition-all" placeholder="Cari Nama / Kelas...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-semibold">
                  <tr>
                      <th class="p-4 text-center w-10">No</th>
                      <th class="p-4">Siswa</th>
                      <th class="p-4 text-center">Kelas</th>
                      <th class="p-4 text-center">Jam Datang</th>
                      <th class="p-4 text-center">Jam Pulang</th>
                      <th class="p-4 text-center">Keterangan Waktu</th>
                      <th class="p-4 text-center">Status Kehadiran</th>
                      <th class="p-4 text-center w-16">Aksi</th>
                  </tr>
              </thead>
              <tbody id="tbody-monitoring" class="divide-y divide-gray-50 bg-white text-sm">
                  </tbody>
            </table>
        </div>

        <div id="footer-monitoring" class="p-4 border-t border-gray-100 bg-gray-50/30 flex justify-between items-center text-xs text-gray-500">
            <span id="info-monitoring">Menampilkan 0 data</span>
            <div class="flex gap-1">
                <button onclick="changePage('monitoring', -1)" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50 transition" id="btn-prev-monitoring">Prev</button>
                <button onclick="changePage('monitoring', 1)" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50 transition" id="btn-next-monitoring">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT JAM & STATUS ABSENSI -->
<div id="modalEditAbsensi" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-scale-up">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                <i class="fas fa-clock text-indigo-600"></i> Koreksi Jam & Status Presensi
            </h3>
            <button type="button" onclick="closeEditAbsensiModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>

        <form id="formEditAbsensi" onsubmit="submitFormEditAbsensi(event)" class="space-y-3.5 text-xs">
            <input type="hidden" id="editAbsenNisn">
            
            <div class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100">
                <div class="font-bold text-gray-900 text-sm" id="editAbsenNama">-</div>
                <div class="text-[11px] text-indigo-700 font-mono mt-0.5" id="editAbsenNisnKelas">-</div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Jam Datang / Masuk</label>
                    <input type="time" id="editAbsenJamDatang" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs font-mono font-bold text-gray-800 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-[10px] text-gray-400 mt-1">Contoh: 06:45</p>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Jam Pulang</label>
                    <input type="time" id="editAbsenJamPulang" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs font-mono font-bold text-gray-800 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-[10px] text-gray-400 mt-1">Contoh: 12:15</p>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Status Kehadiran <span class="text-red-500">*</span></label>
                <select id="editAbsenStatus" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs font-bold text-gray-800 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Hadir">Hadir</option>
                    <option value="Masuk">Masuk</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpa">Alpa</option>
                    <option value="Belum Absen">Belum Absen (Reset)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Keterangan Waktu / Keterangan Masuk</label>
                <select id="editAbsenKeterangan" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs font-bold text-gray-800 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Tepat Waktu">Tepat Waktu (Hijau)</option>
                    <option value="Terlambat">Terlambat (Merah)</option>
                    <option value="Pulang Cepat">Pulang Cepat (Oranye)</option>
                    <option value="auto">Hitung Otomatis Dari Jam Masuk</option>
                </select>
                <p class="text-[10px] text-gray-400 mt-1">Pilih <b>Tepat Waktu</b> jika siswa telat karena kendala jaringan / internet scanner.</p>
            </div>

            <!-- Toggle Notifikasi WhatsApp -->
            <div class="p-3 bg-emerald-50/80 rounded-xl border border-emerald-200 flex items-center justify-between">
                <div>
                    <label for="editAbsenKirimWa" class="font-bold text-emerald-900 cursor-pointer flex items-center gap-1.5 text-xs">
                        <i class="fab fa-whatsapp text-emerald-600 text-sm"></i> Kirim Notifikasi WhatsApp
                    </label>
                    <p class="text-[10px] text-emerald-700 mt-0.5">Kirim info absensi ke WA siswa/wali dengan jam & status di atas.</p>
                </div>
                <input type="checkbox" id="editAbsenKirimWa" checked class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4 cursor-pointer">
            </div>

            <div class="mt-5 pt-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="closeEditAbsensiModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" id="btnSubmitEditAbsen" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-2 transition">
                    <i class="fas fa-check"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.page-script', ['name' => 'monitoring'])
@endpush
