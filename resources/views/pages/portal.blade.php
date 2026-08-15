<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NURIS - SMK Nurul Hidayah Integrated System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-smk.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,700&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
        .font-motto {
            font-family: 'Caveat', cursive;
        }
        .card-nuris {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-nuris:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 30px -8px rgba(0, 0, 0, 0.12);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800 bg-[#f8fafc] selection:bg-emerald-500 selection:text-white">

    <!-- TOP HEADER -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-2">
        <div class="flex items-center justify-between">
            <!-- Brand Logo & Title -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white p-1 shadow-sm border border-slate-100 flex items-center justify-center transition group-hover:scale-105 shrink-0">
                    <img src="{{ asset('images/logo-smk.png') }}" alt="Logo SMK Nurul Hidayah" class="w-full h-full object-contain">
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-none">NURIS</span>
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 tracking-tight mt-0.5">Nurul Hidayah Integrated System</p>
                </div>
            </a>

            <!-- Right Profile / Auth Nav -->
            <div class="flex items-center gap-2.5 sm:gap-3">
                <button type="button" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-600 hover:text-emerald-600 hover:bg-slate-50 transition shrink-0" title="Pemberitahuan">
                    <i class="far fa-bell text-xs sm:text-sm"></i>
                </button>

                @auth
                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-2.5 bg-white border border-slate-200 shadow-sm rounded-full py-1 pl-1.5 pr-3.5 hover:border-emerald-300 transition">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-xs font-bold text-slate-800 leading-none">{{ auth()->user()->name }}</div>
                            <div class="text-[9px] text-slate-500 leading-tight mt-0.5">SMK Nurul Hidayah</div>
                        </div>
                        <i class="fas fa-chevron-down text-[9px] text-slate-400 hidden sm:inline"></i>
                    </a>
                @else
                    <a href="{{ url('/login') }}" class="flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-full shadow-md shadow-emerald-600/20 transition transform hover:scale-[1.02]">
                        <i class="fas fa-sign-in-alt text-xs"></i>
                        <span>Masuk Akun</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex-1 flex flex-col justify-center space-y-6 sm:space-y-7">
        
        <!-- HERO BANNER SECTION (CLEAN & PROPORTIONAL) -->
        <div class="relative rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-7 lg:px-9 lg:py-6 overflow-hidden shadow-xs">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8 items-center">
                
                <!-- Left Column Text -->
                <div class="lg:col-span-6 space-y-3">
                    <div>
                        <div class="text-[11px] sm:text-xs font-bold tracking-wider text-slate-500 uppercase">Selamat Datang,</div>
                        <h1 class="text-2xl sm:text-3xl lg:text-[36px] font-black text-slate-900 tracking-tight leading-tight mt-0.5">
                            SMK Nurul Hidayah
                        </h1>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 italic font-medium leading-relaxed max-w-lg">
                        &ldquo; Bersama Teknologi, Kita Wujudkan Sekolah Unggul, Berkarakter dan Berdaya Saing &rdquo;
                    </p>

                    <div class="pt-1">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 shadow-2xs">
                            <i class="far fa-calendar-alt text-emerald-600"></i>
                            <span id="currentDateFormatted">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Clean School Building Artwork -->
                <div class="lg:col-span-6 flex justify-center lg:justify-end">
                    <div class="w-full max-w-lg rounded-2xl overflow-hidden shadow-xs border border-slate-200/70 bg-white">
                        <img src="{{ asset('images/hero-building.png') }}" alt="Gedung SMK Nurul Hidayah" class="w-full h-44 sm:h-52 lg:h-56 object-cover object-center rounded-2xl">
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION HEADLINE: NURIS DASHBOARD -->
        <div class="text-center space-y-1">
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

        <!-- 5 SUB-APPLICATION CARDS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
            
            <!-- CARD 01: NURIS ABSEN -->
            <div class="card-nuris bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Box -->
                    <div class="w-full h-28 rounded-xl overflow-hidden mb-3 bg-emerald-50/60 border border-emerald-100/50 flex items-center justify-center">
                        <img src="{{ asset('images/cards/card-01-absen.png') }}" alt="NURIS Absen" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-emerald-600">Absen</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Sistem absensi guru, siswa, dan tenaga kependidikan
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/scanner') }}" class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 02: NURIS FINANCE -->
            <div class="card-nuris bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Box -->
                    <div class="w-full h-28 rounded-xl overflow-hidden mb-3 bg-blue-50/60 border border-blue-100/50 flex items-center justify-center">
                        <img src="{{ asset('images/cards/card-02-finance.png') }}" alt="NURIS Finance" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-blue-600">Finance</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Pengelolaan keuangan sekolah secara transparan
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/keuangan/pembayaran') }}" class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 03: NURIS LETTER -->
            <div class="card-nuris bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Box -->
                    <div class="w-full h-28 rounded-xl overflow-hidden mb-3 bg-purple-50/60 border border-purple-100/50 flex items-center justify-center">
                        <img src="{{ asset('images/cards/card-03-letter.png') }}" alt="NURIS Letter" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-purple-600">Letter</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Surat-menyurat dan administrasi sekolah terintegrasi
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/persuratan') }}" class="w-full py-2 px-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 04: NURIS ALUMNI -->
            <div class="card-nuris bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Box -->
                    <div class="w-full h-28 rounded-xl overflow-hidden mb-3 bg-amber-50/60 border border-amber-100/50 flex items-center justify-center">
                        <img src="{{ asset('images/cards/card-04-alumni.png') }}" alt="NURIS Alumni" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-amber-600">Alumni</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Tracer alumni dan database alumni terintegrasi
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/data-alumni') }}" class="w-full py-2 px-3 bg-[#d96c14] hover:bg-[#c25e0e] text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 05: NURIS DASHBOARD -->
            <div class="card-nuris bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col justify-between group overflow-hidden h-full">
                <div>
                    <!-- Art Graphic Box -->
                    <div class="w-full h-28 rounded-xl overflow-hidden mb-3 bg-teal-50/60 border border-teal-100/50 flex items-center justify-center">
                        <img src="{{ asset('images/cards/card-05-dashboard.png') }}" alt="NURIS Dashboard" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">NURIS <span class="text-teal-600">Dashboard</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Statistik, laporan dan monitoring data sekolah secara real-time
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ url('/dashboard') }}" class="w-full py-2 px-3 bg-[#0d828a] hover:bg-[#096a70] text-white rounded-xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- BOTTOM STATS PILLS BAR -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                
                <!-- Stat 1: Total Siswa -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Total Siswa</div>
                        <div class="text-base sm:text-xl font-black text-slate-900">{{ $totalSiswa }}</div>
                        <div class="text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                            <i class="fas fa-arrow-up text-[8px]"></i> +12% dari tahun lalu
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Total Guru & Tendik -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Total Guru & Tendik</div>
                        <div class="text-base sm:text-xl font-black text-slate-900">{{ $totalGuru }}</div>
                        <div class="text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                            <i class="fas fa-arrow-up text-[8px]"></i> +5% dari tahun lalu
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Tingkat Kehadiran -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-cyan-50 text-cyan-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Tingkat Kehadiran</div>
                        <div class="text-base sm:text-xl font-black text-slate-900">{{ $tingkatKehadiran }}%</div>
                        <div class="text-[10px] font-medium text-slate-500">Rata-rata bulan ini</div>
                    </div>
                </div>

                <!-- Stat 4: Total Alumni -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Total Alumni</div>
                        <div class="text-base sm:text-xl font-black text-slate-900">{{ $totalAlumni }}</div>
                        <div class="text-[10px] font-medium text-slate-500">Data terdaftar</div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- FOOTER BAR -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 border-t border-slate-200 mt-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div class="font-medium text-center sm:text-left">
                &copy; {{ date('Y') }} SMK Nurul Hidayah | <span class="font-bold text-slate-700">NURIS</span> - Nurul Hidayah Integrated System
            </div>
            <div class="font-motto text-xl font-bold text-emerald-800 tracking-wide text-center sm:text-right">
                Berilmu, Berakhlak, Berdaya Saing
            </div>
        </div>
    </footer>

</body>
</html>
