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
        .card-hover-effect {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover-effect:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.12);
        }
        .building-glow {
            filter: drop-shadow(0 15px 25px rgba(16, 185, 129, 0.12));
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800 bg-gradient-to-b from-slate-50 via-white to-blue-50/30 selection:bg-emerald-500 selection:text-white">

    <!-- TOP HEADER -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 pb-3">
        <div class="flex items-center justify-between">
            <!-- Brand Logo & Title -->
            <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
                <div class="w-12 h-12 rounded-xl bg-white p-1 shadow-sm border border-slate-100 flex items-center justify-center transition group-hover:scale-105">
                    <img src="{{ asset('images/logo-smk.png') }}" alt="Logo SMK Nurul Hidayah" class="w-full h-full object-contain">
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">NURIS</span>
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-500 tracking-tight mt-0.5">Nurul Hidayah Integrated System</p>
                </div>
            </a>

            <!-- Right Nav / User Auth -->
            <div class="flex items-center gap-3">
                <button type="button" class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-600 hover:text-emerald-600 hover:bg-slate-50 transition" title="Pemberitahuan">
                    <i class="far fa-bell text-sm"></i>
                </button>

                @auth
                    <div class="relative group">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 bg-white border border-slate-200 shadow-sm rounded-full py-1.5 pl-2 pr-4 hover:border-emerald-300 transition">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="text-left hidden sm:block">
                                <div class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] text-slate-500 leading-tight">SMK Nurul Hidayah</div>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                        </a>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs px-5 py-2.5 rounded-full shadow-md shadow-emerald-600/20 transition transform hover:scale-[1.02]">
                        <i class="fas fa-sign-in-alt text-xs"></i>
                        <span>Masuk Akun</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex-1 flex flex-col justify-center space-y-7">
        
        <!-- HERO BANNER SECTION -->
        <div class="relative rounded-3xl bg-gradient-to-r from-emerald-50/70 via-teal-50/30 to-blue-50/40 border border-emerald-100/60 p-6 sm:p-8 lg:p-10 overflow-hidden shadow-sm">
            <!-- Subtle background accent elements -->
            <div class="absolute -right-12 -top-12 w-80 h-80 bg-emerald-200/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/4 -bottom-16 w-60 h-60 bg-blue-200/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                <!-- Left Text Column -->
                <div class="lg:col-span-6 space-y-4">
                    <div>
                        <span class="text-sm font-semibold tracking-wider text-slate-500 uppercase">Selamat Datang,</span>
                        <h1 class="text-3xl sm:text-4xl lg:text-[42px] font-black text-slate-900 tracking-tight leading-tight mt-1">
                            SMK Nurul Hidayah
                        </h1>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 italic font-medium leading-relaxed max-w-lg">
                        &ldquo; Bersama Teknologi, Kita Wujudkan Sekolah Unggul, Berkarakter dan Berdaya Saing &rdquo;
                    </p>

                    <div class="pt-2">
                        <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-white/90 backdrop-blur-sm border border-slate-200/80 rounded-xl shadow-xs text-xs font-semibold text-slate-700">
                            <i class="far fa-calendar-alt text-emerald-600"></i>
                            <span id="currentDateFormatted">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Building Illustration / Artwork Column -->
                <div class="lg:col-span-6 flex justify-center lg:justify-end">
                    <div class="relative w-full max-w-md lg:max-w-lg building-glow rounded-2xl overflow-hidden shadow-md border border-white/60 bg-white/40">
                        <img src="{{ asset('images/portal-hero.png') }}" alt="Gedung SMK Nurul Hidayah" class="w-full h-auto object-cover rounded-2xl" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=800&q=80';">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION HEADER: NURIS DASHBOARD -->
        <div class="text-center space-y-1.5 pt-1">
            <div class="flex items-center justify-center gap-1.5 mb-1">
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            <!-- CARD 01: NURIS ABSEN -->
            <div class="card-hover-effect bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between group relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-400 to-green-500"></div>
                <div>
                    <!-- Header with Badge Number -->
                    <div class="flex justify-between items-center mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span class="text-[11px] font-black text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full">01</span>
                    </div>

                    <!-- Illustration Art Box -->
                    <div class="h-28 rounded-xl bg-gradient-to-br from-emerald-400/90 via-emerald-500 to-teal-600 p-3 flex items-center justify-center text-white relative overflow-hidden shadow-inner mb-4">
                        <div class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full bg-white/10"></div>
                        <div class="text-center space-y-1">
                            <div class="w-12 h-12 mx-auto bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="text-[11px] font-bold tracking-wide uppercase text-white/90">Presensi RFID</div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-extrabold text-slate-800 tracking-tight">NURIS <span class="text-emerald-600">Absen</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Sistem absensi guru, siswa, dan tenaga kependidikan real-time.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="{{ route('scanner') }}" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 02: NURIS FINANCE -->
            <div class="card-hover-effect bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between group relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                <div>
                    <!-- Header with Badge Number -->
                    <div class="flex justify-between items-center mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <span class="text-[11px] font-black text-blue-600 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full">02</span>
                    </div>

                    <!-- Illustration Art Box -->
                    <div class="h-28 rounded-xl bg-gradient-to-br from-blue-500 via-indigo-500 to-blue-700 p-3 flex items-center justify-center text-white relative overflow-hidden shadow-inner mb-4">
                        <div class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full bg-white/10"></div>
                        <div class="text-center space-y-1">
                            <div class="w-12 h-12 mx-auto bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="text-[11px] font-bold tracking-wide uppercase text-white/90">Kas & Tagihan</div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-extrabold text-slate-800 tracking-tight">NURIS <span class="text-blue-600">Finance</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Pengelolaan kas, SPP, dan tagihan keuangan sekolah transparan.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="{{ route('keuangan.pembayaran') }}" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-bold shadow-sm flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 03: NURIS LETTER -->
            <div class="card-hover-effect bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between group relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-purple-500 to-indigo-600"></div>
                <div>
                    <!-- Header with Badge Number -->
                    <div class="flex justify-between items-center mb-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <span class="text-[11px] font-black text-purple-600 bg-purple-50 border border-purple-100 px-2 py-0.5 rounded-full">03</span>
                    </div>

                    <!-- Illustration Art Box -->
                    <div class="h-28 rounded-xl bg-gradient-to-br from-purple-500 via-purple-600 to-indigo-700 p-3 flex items-center justify-center text-white relative overflow-hidden shadow-inner mb-4">
                        <div class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full bg-white/10"></div>
                        <div class="text-center space-y-1">
                            <div class="w-12 h-12 mx-auto bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                                <i class="fas fa-mail-bulk"></i>
                            </div>
                            <div class="text-[11px] font-bold tracking-wide uppercase text-white/90">Tata Persuratan</div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-extrabold text-slate-800 tracking-tight">NURIS <span class="text-purple-600">Letter</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Surat-menyurat dan administrasi tata usaha sekolah terintegrasi.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="{{ route('persuratan.index') }}" class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white rounded-xl text-xs font-bold shadow-sm flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 04: NURIS ALUMNI -->
            <div class="card-hover-effect bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between group relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 to-orange-600"></div>
                <div>
                    <!-- Header with Badge Number -->
                    <div class="flex justify-between items-center mb-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span class="text-[11px] font-black text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">04</span>
                    </div>

                    <!-- Illustration Art Box -->
                    <div class="h-28 rounded-xl bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 p-3 flex items-center justify-center text-white relative overflow-hidden shadow-inner mb-4">
                        <div class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full bg-white/10"></div>
                        <div class="text-center space-y-1">
                            <div class="w-12 h-12 mx-auto bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="text-[11px] font-bold tracking-wide uppercase text-white/90">Tracer Study</div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-extrabold text-slate-800 tracking-tight">NURIS <span class="text-amber-600">Alumni</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Tracer study, karir, dan database riwayat lulusan alumni.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="{{ route('data-alumni') }}" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-xl text-xs font-bold shadow-sm flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

            <!-- CARD 05: NURIS DASHBOARD -->
            <div class="card-hover-effect bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between group relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-teal-500 to-cyan-600"></div>
                <div>
                    <!-- Header with Badge Number -->
                    <div class="flex justify-between items-center mb-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <span class="text-[11px] font-black text-teal-600 bg-teal-50 border border-teal-100 px-2 py-0.5 rounded-full">05</span>
                    </div>

                    <!-- Illustration Art Box -->
                    <div class="h-28 rounded-xl bg-gradient-to-br from-teal-500 via-cyan-500 to-teal-700 p-3 flex items-center justify-center text-white relative overflow-hidden shadow-inner mb-4">
                        <div class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full bg-white/10"></div>
                        <div class="text-center space-y-1">
                            <div class="w-12 h-12 mx-auto bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <div class="text-[11px] font-bold tracking-wide uppercase text-white/90">Pusat Analitik</div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-base font-extrabold text-slate-800 tracking-tight">NURIS <span class="text-teal-600">Dashboard</span></h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed min-h-[34px]">
                        Statistik, laporan, dan monitoring data sekolah secara real-time.
                    </p>
                </div>

                <!-- Action Button -->
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="{{ route('dashboard') }}" class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white rounded-xl text-xs font-bold shadow-sm flex items-center justify-center gap-1.5 transition">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right text-[10px] transition transform group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- BOTTOM STATS PILLS BAR -->
        <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                
                <!-- Stat 1: Siswa -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg shrink-0 shadow-xs">
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

                <!-- Stat 2: Guru & Tendik -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 shadow-xs">
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

                <!-- Stat 3: Kehadiran -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg shrink-0 shadow-xs">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500">Tingkat Kehadiran</div>
                        <div class="text-lg sm:text-xl font-black text-slate-900">{{ $tingkatKehadiran }}%</div>
                        <div class="text-[10px] font-medium text-slate-500">Rata-rata bulan ini</div>
                    </div>
                </div>

                <!-- Stat 4: Alumni -->
                <div class="flex items-center gap-3.5 px-2 pt-2 sm:pt-0">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 shadow-xs">
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
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 border-t border-slate-200/60 mt-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div class="font-medium text-center sm:text-left">
                &copy; {{ date('Y') }} SMK Nurul Hidayah | <span class="font-bold text-slate-700">NURIS</span> - Nurul Hidayah Integrated System
            </div>
            <div class="font-motto text-lg font-bold text-emerald-700 tracking-wide text-center sm:text-right">
                Berilmu, Berakhlak, Berdaya Saing
            </div>
        </div>
    </footer>

</body>
</html>
