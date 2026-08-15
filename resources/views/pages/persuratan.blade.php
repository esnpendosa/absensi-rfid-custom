@extends('layouts.page')

@section('title', 'Persuratan')

@section('content')
<div
    id="view-persuratan"
    data-default-jenis="masuk"
    data-can-manage="{{ auth()->user()?->can('persuratan.manage') ? '1' : '0' }}"
    class="view-section active animate-fade-in"
>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">Surat Masuk</p>
                    <p id="persuratan-summary-masuk" class="mt-1 text-2xl font-bold text-gray-800">0</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i class="fas fa-inbox"></i>
                </span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">Surat Keluar</p>
                    <p id="persuratan-summary-keluar" class="mt-1 text-2xl font-bold text-gray-800">0</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-paper-plane"></i>
                </span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">Diarsipkan</p>
                    <p id="persuratan-summary-arsip" class="mt-1 text-2xl font-bold text-gray-800">0</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-box-archive"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/30 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h3 class="font-bold text-sm text-gray-800">Data Persuratan</h3>
                <p class="text-xs text-gray-500 mt-1">Kelola nomor, tanggal, pihak terkait, status, dan lampiran surat sekolah.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="refreshPersuratanData()" class="bg-white text-gray-600 border border-gray-200 px-3 py-2 rounded-lg text-xs font-bold shadow-sm hover:bg-gray-50 hover:text-indigo-600 transition" title="Perbarui Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
                @can('persuratan.manage')
                    <button onclick="showAddSuratModal()" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold shadow-sm hover:bg-blue-700 transition transform active:scale-95">
                        <i class="fas fa-plus mr-1"></i> Tambah Surat
                    </button>
                @endcan
            </div>
        </div>

        <div class="p-4 border-b border-gray-100 bg-white space-y-3">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <button type="button" data-surat-tab="masuk" onclick="setPersuratanJenis('masuk')" class="shrink-0 px-3 py-2 rounded-lg text-xs font-bold border transition">
                        <i class="fas fa-inbox mr-1"></i> Surat Masuk
                    </button>
                    <button type="button" data-surat-tab="keluar" onclick="setPersuratanJenis('keluar')" class="shrink-0 px-3 py-2 rounded-lg text-xs font-bold border transition">
                        <i class="fas fa-paper-plane mr-1"></i> Surat Keluar
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <select id="persuratan-status-filter" onchange="handlePersuratanStatusFilter(this.value)" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 font-bold shadow-sm cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="diarsipkan">Diarsipkan</option>
                    </select>
                    <select id="persuratan-per-page" onchange="handlePersuratanLimit(this.value)" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 font-bold shadow-sm cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-xs"></i>
                        </div>
                        <input id="persuratan-search" type="text" oninput="handlePersuratanSearch(this.value)" class="bg-gray-50 border border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:w-64 pl-10 p-2 transition-all" placeholder="Cari nomor/perihal/pihak...">
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-semibold border-b border-gray-200">
                    <tr>
                        <th class="p-3 text-center w-12">No</th>
                        <th class="p-3 min-w-[150px]">Nomor Surat</th>
                        <th class="p-3 min-w-[140px]">Tanggal</th>
                        <th id="persuratan-pihak-header" class="p-3 min-w-[180px]">Pihak</th>
                        <th class="p-3 min-w-[240px]">Perihal</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-center">Lampiran</th>
                        <th class="p-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbody-persuratan" class="divide-y divide-gray-50 bg-white text-xs text-gray-700">
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-400">Memuat data persuratan...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 bg-gray-50/30 flex justify-between items-center text-xs text-gray-500">
            <span id="info-persuratan">Menampilkan 0 data</span>
            <div class="flex gap-1">
                <button onclick="changePersuratanPage(-1)" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50 transition shadow-sm" id="btn-prev-persuratan">Prev</button>
                <button onclick="changePersuratanPage(1)" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-100 disabled:opacity-50 transition shadow-sm" id="btn-next-persuratan">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.page-script', ['name' => 'persuratan'])
@endpush
