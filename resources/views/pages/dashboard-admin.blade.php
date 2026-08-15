@extends('layouts.page')

@section('title', 'Dashboard Admin')

@section('content')
<div id="view-admin-dashboard" class="view-section active animate-fade-in space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-th-large text-blue-600"></i> Dashboard Utama Sekolah
            </h2>
            <p class="text-xs text-gray-500 mt-1">Pusat kendali Absensi, Keuangan Sekolah, Persuratan, dan Tracer Alumni.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold bg-gray-50 text-gray-600 px-3.5 py-2 rounded-xl border border-gray-200">
                <i class="far fa-clock text-blue-500 mr-1.5"></i> <span id="adminDateDisplay">...</span>
            </span>
            <button onclick="refreshData('dashboard')" class="flex items-center gap-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-sm transition transform active:scale-95">
                <i class="fas fa-sync-alt"></i> <span>Refresh Data</span>
            </button>
        </div>
    </div>

    <!-- 4 PILAR UTAMA DASHBOARD -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- 1. ABSENSI CARD -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 text-white shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200">1. Modul Absensi</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm"><i class="fas fa-qrcode"></i></div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold">Presensi Harian</div>
                    <p class="text-xs text-blue-100 mt-0.5">Monitoring Kehadiran Siswa</p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex justify-between items-center text-xs">
                <a href="{{ route('monitoring') }}" class="text-blue-100 hover:text-white font-bold inline-flex items-center gap-1">
                    Monitoring <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
                <a href="{{ route('scanner') }}" class="px-2.5 py-1 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold">
                    <i class="fas fa-camera"></i> Scan
                </a>
            </div>
        </div>

        <!-- 2. KEUANGAN CARD -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 text-white shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">2. Keuangan Sekolah</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm"><i class="fas fa-cash-register"></i></div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold">Rp {{ number_format($keuangan['bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                    <p class="text-xs text-emerald-100 mt-0.5">Pemasukan Bulan Ini</p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex justify-between items-center text-xs">
                <span class="text-[11px] text-emerald-200">Hari ini: Rp {{ number_format($keuangan['hari_ini'] ?? 0, 0, ',', '.') }}</span>
                <a href="{{ route('keuangan.pembayaran.index') }}" class="px-2.5 py-1 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold">
                    <i class="fas fa-plus"></i> Kasir
                </a>
            </div>
        </div>

        <!-- 3. PERSURATAN CARD -->
        <div class="bg-gradient-to-br from-purple-600 to-indigo-800 rounded-2xl p-5 text-white shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-200">3. Persuratan</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm"><i class="fas fa-envelope-open-text"></i></div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold">{{ $persuratan['total'] ?? 0 }} Surat</div>
                    <p class="text-xs text-purple-100 mt-0.5">Arsip & Dokumen Sekolah</p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex justify-between items-center text-xs">
                <span class="text-[11px] text-purple-200">M: {{ $persuratan['masuk'] ?? 0 }} | K: {{ $persuratan['keluar'] ?? 0 }}</span>
                <a href="{{ route('persuratan.index') }}" class="px-2.5 py-1 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold">
                    <i class="fas fa-folder-open"></i> Buka
                </a>
            </div>
        </div>

        <!-- 4. ALUMNI TRACER CARD -->
        <div class="bg-gradient-to-br from-amber-600 to-orange-700 rounded-2xl p-5 text-white shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-200">4. Alumni Tracer</span>
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm"><i class="fas fa-user-graduate"></i></div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold">{{ $alumni['total'] ?? 0 }} Alumni</div>
                    <p class="text-xs text-amber-100 mt-0.5">{{ $alumni['kuliah'] ?? 0 }} Kuliah | {{ $alumni['kerja'] ?? 0 }} Kerja</p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex justify-between items-center text-xs">
                <span class="text-[11px] text-amber-200">{{ $alumni['wirausaha'] ?? 0 }} Wirausaha</span>
                <a href="{{ route('data-alumni') }}" class="px-2.5 py-1 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold">
                    <i class="fas fa-list"></i> Tracer
                </a>
            </div>
        </div>
    </div>

    <!-- Statistik Kehadiran 5 Card -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-100 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-16 h-16 bg-indigo-50 rounded-bl-full -mr-2 -mt-2 transition-transform group-hover:scale-110"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Total Siswa</p>
            <div class="flex items-center justify-between mt-2 relative z-10">
                <h3 id="admStatTotal" class="text-2xl font-bold text-gray-800">-</h3>
                <div class="text-indigo-500 bg-indigo-50 p-2 rounded-lg"><i class="fas fa-users"></i></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-100 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-16 h-16 bg-emerald-50 rounded-bl-full -mr-2 -mt-2 transition-transform group-hover:scale-110"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Hadir</p>
            <div class="flex items-center justify-between mt-2 relative z-10">
                <h3 id="admStatHadir" class="text-2xl font-bold text-gray-800">-</h3>
                <div class="text-emerald-500 bg-emerald-50 p-2 rounded-lg"><i class="fas fa-check"></i></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-yellow-100 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-16 h-16 bg-yellow-50 rounded-bl-full -mr-2 -mt-2 transition-transform group-hover:scale-110"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Sakit</p>
            <div class="flex items-center justify-between mt-2 relative z-10">
                <h3 id="admStatSakit" class="text-2xl font-bold text-gray-800">-</h3>
                <div class="text-yellow-500 bg-yellow-50 p-2 rounded-lg"><i class="fas fa-procedures"></i></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-blue-100 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-2 -mt-2 transition-transform group-hover:scale-110"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Izin</p>
            <div class="flex items-center justify-between mt-2 relative z-10">
                <h3 id="admStatIzin" class="text-2xl font-bold text-gray-800">-</h3>
                <div class="text-blue-500 bg-blue-50 p-2 rounded-lg"><i class="fas fa-paper-plane"></i></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-red-100 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-16 h-16 bg-red-50 rounded-bl-full -mr-2 -mt-2 transition-transform group-hover:scale-110"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Alpa</p>
            <div class="flex items-center justify-between mt-2 relative z-10">
                <h3 id="admStatAlpa" class="text-2xl font-bold text-gray-800">-</h3>
                <div class="text-red-500 bg-red-50 p-2 rounded-lg"><i class="fas fa-times"></i></div>
            </div>
        </div>
    </div>

    <!-- Chart & Operational Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm h-full flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 flex items-center">
                        <i class="fas fa-chart-area text-blue-500 mr-2"></i> Grafik Statistik Kehadiran
                    </h3>
                    <p class="mt-1 text-[11px] text-gray-500">Klik titik chart untuk membuka monitoring sesuai status.</p>
                </div>
            </div>
            <div class="relative w-full flex-1 min-h-[260px] lg:min-h-[300px]">
                <canvas id="adminAttendanceChart"></canvas>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center">
                        <i class="fas fa-sliders-h text-sky-500 mr-2"></i> Status Operasional
                    </h3>
                    <span id="adminAttendanceModePill" class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-sky-700">
                        Memuat
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/80 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-indigo-500">Mode Absensi Aktif</p>
                        <p id="adminAttendanceModeLabel" class="mt-2 text-base font-bold text-gray-900">Memuat...</p>
                        <p id="adminAttendanceModeHint" class="mt-1 text-xs leading-relaxed text-gray-600">Mengambil konfigurasi absensi terbaru.</p>
                    </div>

                    <a href="{{ route('kelola-absen') }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition transform active:scale-95">
                        <i class="fas fa-clock"></i> Atur Jam & Waktu Absensi
                    </a>
                </div>
            </div>

            <div id="adminQuickAccess" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center text-sm">
                    <i class="fas fa-bolt text-amber-500 mr-2"></i> Akses Cepat
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('keuangan.pembayaran.index') }}" class="w-full flex items-center p-3 rounded-xl border border-gray-100 hover:bg-emerald-50 hover:border-emerald-200 transition-all group text-left">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3 group-hover:scale-110 transition"><i class="fas fa-cash-register"></i></div>
                        <div>
                            <div class="font-bold text-xs text-gray-700">Kasir Pembayaran</div>
                            <div class="text-[10px] text-gray-400">Bayar SPP, Gedung & Ujian</div>
                        </div>
                    </a>

                    <a href="{{ route('data-alumni') }}" class="w-full flex items-center p-3 rounded-xl border border-gray-100 hover:bg-amber-50 hover:border-amber-200 transition-all group text-left">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mr-3 group-hover:scale-110 transition"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <div class="font-bold text-xs text-gray-700">Tracer Alumni</div>
                            <div class="text-[10px] text-gray-400">Pelacakan kuliah & kerja</div>
                        </div>
                    </a>

                    <a href="{{ route('persuratan.index') }}" class="w-full flex items-center p-3 rounded-xl border border-gray-100 hover:bg-purple-50 hover:border-purple-200 transition-all group text-left">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3 group-hover:scale-110 transition"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="font-bold text-xs text-gray-700">Persuratan Sekolah</div>
                            <div class="text-[10px] text-gray-400">Buat & arsip surat resmi</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.page-script', ['name' => 'dashboard-admin'])
@endpush
