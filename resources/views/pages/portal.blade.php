@php
    $imgLogo = $assets['logo'] ?? (\class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getLogo() : asset('images/logo-smk.png'));
    $imgBuilding = $assets['building'] ?? (\class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getBuilding() : asset('images/hero-building-clean.png'));
    $imgCard1 = $assets['card1'] ?? (\class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getCard1() : asset('images/cards/art-01.png'));
    $imgCard2 = $assets['card2'] ?? (\class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getCard2() : asset('images/cards/art-02.png'));
    $imgCard3 = $assets['card3'] ?? (\class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getCard3() : asset('images/cards/art-03.png'));
    $imgCard4 = $assets['card4'] ?? (\class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getCard4() : asset('images/cards/art-04.png'));
    $imgCard5 = $assets['card5'] ?? (\class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getCard5() : asset('images/cards/art-05.png'));
    $totalSiswa = $totalSiswa ?? '1.248';
    $totalGuru = $totalGuru ?? '86';
    $tingkatKehadiran = $tingkatKehadiran ?? '92';
    $totalAlumni = $totalAlumni ?? '3.562';
    $schoolName = $schoolName ?? 'SMK Nurul Hidayah Bungah';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NURIS - {{ $schoolName }} Integrated System</title>
    <link rel="icon" type="image/png" href="{{ $imgLogo }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,700&family=Caveat:wght@600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ── Reset & Base ────────────────────────────── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .font-motto {
            font-family: 'Caveat', cursive;
        }

        /* ── Animations ────────────────────────────────── */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-slide-up { animation: slideUp 0.5s ease forwards; }
        .animate-fade-in { animation: fadeIn 0.7s ease forwards; }

        /* ── HERO SECTION ─────────────────────────────── */
        .hero-section {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            min-height: 320px;
            display: flex;
            align-items: center;
            padding: 2.5rem 3rem;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 58, 138, 0.70) 50%, rgba(15, 23, 42, 0.50) 100%);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        }

        .hero-section .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero-section .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                    rgba(15, 23, 42, 0.80) 0%,
                    rgba(30, 58, 138, 0.60) 50%,
                    rgba(15, 23, 42, 0.35) 100%);
        }

        .hero-section .hero-content {
            position: relative;
            z-index: 1;
            max-width: 760px;
        }

        .hero-section .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.20);
            padding: 0.35rem 1.25rem 0.35rem 0.75rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.90);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .hero-section .hero-badge .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #38bdf8;
            display: inline-block;
            animation: pulse-dot 1.8s ease-in-out infinite;
        }

        .hero-section h1 {
            font-size: clamp(2.2rem, 4.5vw, 3.6rem);
            font-weight: 800;
            color: #ffffff;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .hero-section h1 .highlight {
            background: linear-gradient(135deg, #93c5fd, #60a5fa, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-section .hero-sub {
            font-size: clamp(0.95rem, 1.2vw, 1.15rem);
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            margin-bottom: 0.2rem;
        }

        .hero-section .tagline {
            font-size: clamp(0.9rem, 1.1vw, 1.05rem);
            color: rgba(255, 255, 255, 0.90);
            font-weight: 400;
            line-height: 1.6;
            max-width: 600px;
            margin-bottom: 1.25rem;
            font-style: italic;
        }

        .hero-section .tagline i {
            color: #60a5fa;
            margin-right: 0.3rem;
        }

        .hero-section .hero-meta {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .hero-section .hero-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.80);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .hero-section .hero-meta .meta-item i {
            color: #38bdf8;
            font-size: 0.8rem;
        }

        .hero-section .hero-meta .meta-divider {
            width: 1px;
            height: 20px;
            background: rgba(255, 255, 255, 0.20);
        }

        /* ── Cards ────────────────────────────────────── */
        .card-app {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            padding: 1.15rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .card-app:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.12);
            border-color: rgba(148, 163, 184, 0.4);
        }

        .card-app .card-image {
            width: 100%;
            height: 130px;
            border-radius: 14px;
            overflow: hidden;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.85rem;
        }

        .card-app .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .card-app:hover .card-image img {
            transform: scale(1.05);
        }

        .card-app .card-title {
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .card-app .card-desc {
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.5;
            min-height: 38px;
            margin-bottom: 0.85rem;
        }

        .card-app .btn-app {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.65rem 1rem;
            border: none;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #fff;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.10);
        }

        .card-app .btn-app:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
        }

        .card-app .btn-app i {
            font-size: 0.65rem;
            transition: transform 0.3s ease;
        }

        .card-app:hover .btn-app i {
            transform: translateX(3px);
        }

        /* ── Button Accent Colors ── */
        .btn-absen { background: #059669; }
        .btn-absen:hover { background: #047857; }

        .btn-finance { background: #2563eb; }
        .btn-finance:hover { background: #1d4ed8; }

        .btn-letter { background: #7c3aed; }
        .btn-letter:hover { background: #6d28d9; }

        .btn-alumni { background: #d97706; }
        .btn-alumni:hover { background: #b45309; }

        .btn-dashboard { background: #0891b2; }
        .btn-dashboard:hover { background: #0e7490; }

        /* ── Stat Cards ────────────────────────────────── */
        .stat-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 1.1rem 1.35rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.08);
            border-color: rgba(148, 163, 184, 0.3);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .stat-icon.blue {
            background: #eff6ff;
            color: #2563eb;
        }
        .stat-icon.indigo {
            background: #eef2ff;
            color: #4f46e5;
        }
        .stat-icon.emerald {
            background: #ecfdf5;
            color: #059669;
        }
        .stat-icon.amber {
            background: #fffbeb;
            color: #d97706;
        }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .stat-change {
            font-size: 0.68rem;
            font-weight: 700;
            color: #059669;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change i {
            font-size: 0.55rem;
        }

        /* ── Decorative ────────────────────────────────── */
        .gradient-bar {
            height: 3.5px;
            width: 65px;
            border-radius: 99px;
            background: linear-gradient(90deg, #2563eb, #38bdf8, #059669);
            margin: 0 auto;
        }

        /* ── Responsive ────────────────────────────────── */
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1.5rem;
                min-height: 280px;
                border-radius: 18px;
            }
            .hero-section .hero-meta { gap: 0.75rem; }
            .hero-section .hero-meta .meta-divider { display: none; }
            .stat-value { font-size: 1.25rem; }
            .stat-card { padding: 0.85rem 1rem; gap: 0.75rem; }
            .stat-icon { width: 40px; height: 40px; font-size: 1rem; }
            .card-app .card-image { height: 110px; }
        }

        @media (max-width: 480px) {
            .hero-section { padding: 1.5rem 1.25rem; min-height: 250px; }
            .hero-section .hero-badge { font-size: 0.65rem; padding: 0.25rem 0.75rem 0.25rem 0.5rem; }
        }
    </style>
</head>
<body>

    <!-- ═══ TOP HEADER ═══ -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 pb-3 relative z-20">
        <div class="flex items-center justify-between">
            <!-- Brand -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-white p-1.5 border border-slate-200 shadow-sm flex items-center justify-center transition group-hover:scale-105 shrink-0">
                    <img src="{{ $imgLogo }}" alt="Logo {{ $schoolName }}" class="w-full h-full object-contain" />
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">NURIS</span>
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                    </div>
                    <p class="text-[10.5px] font-semibold text-slate-500 tracking-tight mt-0.5">Nurul Hidayah Integrated System</p>
                </div>
            </a>

            <!-- Right Profile / Auth Nav -->
            <div class="flex items-center gap-3">
                <button type="button" class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition shrink-0" title="Pemberitahuan">
                    <i class="far fa-bell text-sm"></i>
                </button>

                @auth
                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-2.5 bg-white border border-slate-200 shadow-sm rounded-full py-1.5 pl-2 pr-4 hover:border-blue-400 transition">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-xs font-bold text-slate-800 leading-none">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-500 leading-tight mt-0.5">{{ $schoolName }}</div>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden sm:inline ml-0.5"></i>
                    </a>
                @else
                    <a href="{{ url('/login') }}" class="flex items-center gap-2.5 bg-white border border-slate-200 shadow-sm rounded-full py-1.5 pl-2.5 pr-5 hover:border-blue-500 hover:shadow-md transition group">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                            <i class="fas fa-sign-in-alt text-xs"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-bold text-slate-800 leading-none group-hover:text-blue-600 transition">Masuk Akun</div>
                            <div class="text-[10px] text-slate-500 leading-tight mt-0.5">Guru, Siswa &amp; Admin</div>
                        </div>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex-1 flex flex-col space-y-8 relative z-10">

        <!-- ── HERO BANNER SLIDE ── -->
        <div class="hero-section animate-fade-in">
            <!-- Background Image -->
            <div class="hero-bg" style="background-image: url('{{ $imgBuilding }}');"></div>

            <!-- Content -->
            <div class="hero-content">
                <!-- Badge -->
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    NURIS &bull; Integrated System Portal
                </div>

                <!-- Title -->
                <div class="hero-sub">Selamat Datang di</div>
                <h1><span class="highlight">SMK Nurul Hidayah</span> Bungah</h1>

                <!-- Tagline -->
                <p class="tagline">
                    <i class="fas fa-quote-left"></i>
                    Bersama Teknologi, Kita Wujudkan Sekolah Unggul, Berkarakter dan Berdaya Saing
                </p>

                <!-- Meta Info Realtime -->
                <div class="hero-meta">
                    <span class="meta-item">
                        <i class="far fa-calendar-alt"></i>
                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="meta-divider"></span>
                    <span class="meta-item">
                        <i class="fas fa-award"></i>
                        SMK Pusat Keunggulan
                    </span>
                    <span class="meta-divider"></span>
                    <span class="meta-item">
                        <i class="fas fa-map-pin"></i>
                        Bungah, Gresik
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Section Title ── -->
        <div class="text-center space-y-1.5">
            <div class="gradient-bar"></div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                <span class="text-blue-600">NURIS</span> Dashboard
            </h2>
            <p class="text-sm font-medium text-slate-500">Akses cepat ke 5 sub aplikasi NURIS</p>
        </div>

        <!-- ── 5 SUB-APPLICATION CARDS ── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

            <!-- 1. Absen -->
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $imgCard1 }}" alt="NURIS Absen" loading="lazy" />
                    </div>
                    <h3 class="card-title">NURIS <span class="text-emerald-600">Absen</span></h3>
                    <p class="card-desc">Sistem absensi guru, siswa, dan tenaga kependidikan secara realtime</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url('/scanner') }}" class="btn-app btn-absen">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- 2. Finance -->
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $imgCard2 }}" alt="NURIS Finance" loading="lazy" />
                    </div>
                    <h3 class="card-title">NURIS <span class="text-blue-600">Finance</span></h3>
                    <p class="card-desc">Pengelolaan kas, SPP, dan tagihan keuangan sekolah transparan</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url('/keuangan/pembayaran') }}" class="btn-app btn-finance">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- 3. Letter -->
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $imgCard3 }}" alt="NURIS Letter" loading="lazy" />
                    </div>
                    <h3 class="card-title">NURIS <span class="text-purple-600">Letter</span></h3>
                    <p class="card-desc">Surat-menyurat dan administrasi tata usaha terintegrasi</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url('/persuratan') }}" class="btn-app btn-letter">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- 4. Alumni -->
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $imgCard4 }}" alt="NURIS Alumni" loading="lazy" />
                    </div>
                    <h3 class="card-title">NURIS <span class="text-amber-600">Alumni</span></h3>
                    <p class="card-desc">Tracer study dan database alumni terintegrasi secara digital</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url('/data-alumni') }}" class="btn-app btn-alumni">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- 5. Dashboard -->
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $imgCard5 }}" alt="NURIS Dashboard" loading="lazy" />
                    </div>
                    <h3 class="card-title">NURIS <span class="text-cyan-600">Dashboard</span></h3>
                    <p class="card-desc">Statistik, laporan dan monitoring analitik data sekolah realtime</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url('/dashboard') }}" class="btn-app btn-dashboard">
                        <span>Buka Aplikasi</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- ── STATS CARDS REALTIME ── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- 1. Total Siswa -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-label">Total Siswa</div>
                    <div class="stat-value">{{ $totalSiswa }}</div>
                    <div class="stat-change">
                        <i class="fas fa-check-circle"></i> Data Aktif
                    </div>
                </div>
            </div>

            <!-- 2. Guru & Tendik -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon indigo">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <div class="stat-label">Guru &amp; Tendik</div>
                    <div class="stat-value">{{ $totalGuru }}</div>
                    <div class="stat-change">
                        <i class="fas fa-check-circle"></i> Terdaftar
                    </div>
                </div>
            </div>

            <!-- 3. Tingkat Kehadiran -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon emerald">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-label">Kehadiran</div>
                    <div class="stat-value">{{ $tingkatKehadiran }}%</div>
                    <div class="text-[0.68rem] font-semibold text-emerald-600">Realtime Presensi</div>
                </div>
            </div>

            <!-- 4. Total Alumni -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon amber">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="stat-label">Total Alumni</div>
                    <div class="stat-value">{{ $totalAlumni }}</div>
                    <div class="text-[0.68rem] font-semibold text-slate-500">Database Tracer</div>
                </div>
            </div>
        </div>

    </main>

    <!-- ═══ FOOTER ═══ -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 border-t border-slate-200/80 mt-6 relative z-10">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div class="font-medium text-center sm:text-left">
                &copy; {{ date('Y') }} {{ $schoolName }} &bull;
                <span class="font-bold text-slate-700">NURIS</span> &mdash; Nurul Hidayah Integrated System
            </div>
            <div class="font-motto text-xl font-bold text-blue-800 tracking-wide text-center sm:text-right">
                Berilmu, Berakhlak, Berdaya Saing
            </div>
        </div>
    </footer>

    <!-- ── Decorative Blobs ── -->
    <div class="pointer-events-none fixed bottom-0 left-0 w-56 h-28 bg-gradient-to-tr from-blue-500/10 via-indigo-400/8 to-transparent rounded-tr-full blur-2xl -z-0"></div>
    <div class="pointer-events-none fixed bottom-0 right-0 w-56 h-28 bg-gradient-to-tl from-blue-500/10 via-cyan-400/8 to-transparent rounded-tl-full blur-2xl -z-0"></div>

</body>
</html>