<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NURIS - SMK Nurul Hidayah Integrated System</title>
    <link rel="icon" type="image/png" href="{{ $assets['logo'] ?? asset('images/logo-smk.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,700&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
        }
        .font-motto {
            font-family: 'Caveat', cursive;
        }
        .card-live {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-live:hover {
            transform: translateY(-8px);
            box-shadow: 0 22px 35px -10px rgba(0, 0, 0, 0.14);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800 bg-white selection:bg-emerald-500 selection:text-white relative overflow-x-hidden">

    <!-- TOP HEADER -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 pb-3 relative z-20">
        <div class="flex items-center justify-between">
            <!-- Brand Logo & Title -->
            <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
                <div class="w-12 h-12 rounded-xl bg-white p-1 shadow-sm border border-slate-100 flex items-center justify-center transition group-hover:scale-105 shrink-0">
                    <img src="{{ $assets['logo'] }}" alt="Logo SMK Nurul Hidayah" class="w-full h-full object-contain">
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">NURIS</span>
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-500 tracking-tight mt-0.5">Nurul Hidayah Integrated System</p>
                </div>
            </a>

            <!-- Right Profile / Auth Nav -->
            <div class="flex items-center gap-3">
                <button type="button" class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-600 hover:text-emerald-600 hover:bg-slate-50 transition shrink-0" title="Pemberitahuan">
                    <i class="far fa-bell text-sm"></i>
                </button>

                @auth
                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-2.5 bg-white border border-slate-200 shadow-sm rounded-full py-1.5 pl-2 pr-4 hover:border-emerald-300 transition">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-xs font-bold text-slate-800 leading-none">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-500 leading-tight mt-0.5">SMK Nurul Hidayah</div>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden sm:inline ml-1"></i>
                    </a>
                @else
                    <a href="{{ url('/login') }}" class="flex items-center gap-2.5 bg-white border border-slate-200 shadow-sm rounded-full py-1.5 pl-2.5 pr-5 hover:border-emerald-400 hover:shadow transition group">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                            <i class="fas fa-sign-in-alt text-xs"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-bold text-slate-800 leading-none group-hover:text-emerald-600 transition">Masuk Akun</div>
                            <div class="text-[10px] text-slate-500 leading-tight mt-0.5">Guru, Siswa & Admin</div>
                        </div>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex-1 flex flex-col justify-center space-y-7 relative z-10">
        
        <!-- HERO BANNER SECTION (MATCHING TARGET DESIGN) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center pt-2 pb-4">
            
            <!-- Left Text Column -->
            <div class="lg:col-span-6 space-y-3.5">
                <div>
                    <span class="text-sm font-semibold tracking-wider text-slate-500 uppercase">Selamat Datang,</span>
                    <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black text-slate-900 tracking-tight leading-tight mt-1">
                        SMK Nurul Hidayah
                    </h1>
                </div>

                <p class="text-xs sm:text-sm text-slate-600 italic font-medium leading-relaxed max-w-lg">
                    &ldquo; Bersama Teknologi, Kita Wujudkan Sekolah Unggul, Berkarakter dan Berdaya Saing &rdquo;
                </p>

                <div class="pt-1">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 shadow-xs">
                        <i class="far fa-calendar-alt text-slate-600"></i>
                        <span id="currentDateFormatted">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Building Column (Clean photo blending into page) -->
            <div class="lg:col-span-6 flex justify-center lg:justify-end">
                <div class="w-full max-w-lg rounded-2xl overflow-hidden shadow-md border border-slate-100 bg-white">
                    <img src="{{ $assets['building'] }}" alt="SMK Nurul Hidayah" class="w-full h-44 sm:h-52 lg:h-56 object-cover object-center rounded-2xl">
                </div>
            </div>

        </div>

        <!-- SECTION HEADLINE: NURIS DASHBOARD -->
        <div class="text-center space-y-1 pt-1">
            <div class="flex items-center justify-center gap-1 mb-1">
                <span class="h-1 w-6 rounded-full bg-emerald-500"></span>
                <span class="h-1 w-3 rounded-full bg-amber-400"></span>
                <span class="h-1 w-6 rounded-full bg-blue-600"></span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                <span class="text-emerald-600">NURIS</span> Dashboard
            </h2>
            <p class="text-xs sm:text-sm font-medium text-slate-500">Akses cepat ke 5 sub aplikasi NURIS</p>
        </div>

        <!-- 5 SUB-APPLICATION CARDS GRID (100% MATCHING TARGET DESIGN) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            
            <!-- CARD 01: NURIS ABSEN -->
            <div class="card-live bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Header -->
                    <div class="w-full h-32 rounded-xl overflow-hidden mb-3.5 bg-emerald-50 flex items-center justify-center shadow-xs">
                        <img src="{{ $assets['card1'] }}" alt="NURIS Absen" class="w-full h-full object-cover rounded-xl transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-emerald-600">Absen</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Sistem absensi guru, siswa, dan tenaga kependidikan
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/scanner') }}" class="w-full py-2.5 px-3 bg-[#059669] hover:bg-[#047857] text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-0.5"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 02: NURIS FINANCE -->
            <div class="card-live bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Header -->
                    <div class="w-full h-32 rounded-xl overflow-hidden mb-3.5 bg-blue-50 flex items-center justify-center shadow-xs">
                        <img src="{{ $assets['card2'] }}" alt="NURIS Finance" class="w-full h-full object-cover rounded-xl transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-blue-600">Finance</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Pengelolaan keuangan sekolah secara transparan
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/keuangan/pembayaran') }}" class="w-full py-2.5 px-3 bg-[#2563eb] hover:bg-[#1d4ed8] text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-0.5"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 03: NURIS LETTER -->
            <div class="card-live bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Header -->
                    <div class="w-full h-32 rounded-xl overflow-hidden mb-3.5 bg-purple-50 flex items-center justify-center shadow-xs">
                        <img src="{{ $assets['card3'] }}" alt="NURIS Letter" class="w-full h-full object-cover rounded-xl transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-purple-600">Letter</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Surat-menyurat dan administrasi sekolah terintegrasi
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/persuratan') }}" class="w-full py-2.5 px-3 bg-[#7c3aed] hover:bg-[#6d28d9] text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-0.5"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 04: NURIS ALUMNI -->
            <div class="card-live bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Header -->
                    <div class="w-full h-32 rounded-xl overflow-hidden mb-3.5 bg-amber-50 flex items-center justify-center shadow-xs">
                        <img src="{{ $assets['card4'] }}" alt="NURIS Alumni" class="w-full h-full object-cover rounded-xl transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-amber-600">Alumni</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Tracer alumni dan database alumni terintegrasi
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/data-alumni') }}" class="w-full py-2.5 px-3 bg-[#d97706] hover:bg-[#b45309] text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-0.5"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 05: NURIS DASHBOARD -->
            <div class="card-live bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Header -->
                    <div class="w-full h-32 rounded-xl overflow-hidden mb-3.5 bg-teal-50 flex items-center justify-center shadow-xs">
                        <img src="{{ $assets['card5'] }}" alt="NURIS Dashboard" class="w-full h-full object-cover rounded-xl transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-teal-600">Dashboard</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Statistik, laporan dan monitoring data sekolah secara real-time
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/dashboard') }}" class="w-full py-2.5 px-3 bg-[#0891b2] hover:bg-[#0e7490] text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-0.5"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- STATS SUMMARY BAR (FLOATING WHITE CONTAINER) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-md p-4 sm:p-5">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                
                <!-- Stat 1: Total Siswa -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-full bg-[#e6fffa] text-[#0d9488] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Total Siswa</div>
                        <div class="text-lg sm:text-xl font-black text-slate-900">{{ $totalSiswa }}</div>
                        <div class="text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                            <i class="fas fa-arrow-up text-[8px]"></i> +12% dari tahun lalu
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Total Guru & Tendik -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-full bg-[#ecfdf5] text-[#059669] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Total Guru & Tendik</div>
                        <div class="text-lg sm:text-xl font-black text-slate-900">{{ $totalGuru }}</div>
                        <div class="text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                            <i class="fas fa-arrow-up text-[8px]"></i> +5% dari tahun lalu
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Tingkat Kehadiran -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-full bg-[#f0fdf4] text-[#16a34a] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Tingkat Kehadiran</div>
                        <div class="text-lg sm:text-xl font-black text-slate-900">{{ $tingkatKehadiran }}%</div>
                        <div class="text-[10px] font-medium text-slate-500">Rata-rata bulan ini</div>
                    </div>
                </div>

                <!-- Stat 4: Total Alumni -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-full bg-[#eff6ff] text-[#2563eb] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Total Alumni</div>
                        <div class="text-lg sm:text-xl font-black text-slate-900">{{ $totalAlumni }}</div>
                        <div class="text-[10px] font-medium text-slate-500">Data terdaftar</div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- FOOTER SECTION -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 border-t border-slate-200/80 mt-6 relative z-10">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div class="font-medium text-center sm:text-left">
                &copy; {{ date('Y') }} SMK Nurul Hidayah | <span class="font-bold text-slate-700">NURIS</span> - Nurul Hidayah Integrated System
            </div>
            <div class="font-motto text-xl font-bold text-emerald-800 tracking-wide text-center sm:text-right">
                Berilmu, Berakhlak, Berdaya Saing
            </div>
        </div>
    </footer>

    <!-- BOTTOM DECORATIVE CORNER ACCENTS -->
    <div class="pointer-events-none fixed bottom-0 left-0 w-48 h-20 bg-gradient-to-tr from-emerald-500/20 via-amber-400/20 to-transparent rounded-tr-full blur-xl -z-0"></div>
    <div class="pointer-events-none fixed bottom-0 right-0 w-48 h-20 bg-gradient-to-tl from-emerald-500/20 via-amber-400/20 to-transparent rounded-tl-full blur-xl -z-0"></div>

</body>
</html>
