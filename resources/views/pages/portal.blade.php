@php
    $sysName = $uiSettings['portal_system_name'] ?? 'SIMNUHA';
    $schoolName = $uiSettings['website_nama'] ?? 'SMK Nurul Hidayah Bungah';
    $imgLogo = $uiSettings['website_logo_url'] ?? (class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getLogo() : asset('images/logo-smk.png'));
    $imgBuilding = $uiSettings['portal_building_photo_url'] ?? (class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getBuilding() : asset('images/hero-building-clean.png'));
    
    $heroTitle = $uiSettings['portal_hero_title'] ?? 'SMK Nurul Hidayah Bungah';
    $heroWords = explode(' ', $heroTitle);
    if (count($heroWords) > 2) {
        $lastWord = array_pop($heroWords);
        $firstPart = implode(' ', $heroWords);
    } else {
        $firstPart = $heroTitle;
        $lastWord = '';
    }

    $totalSiswa = $totalSiswa ?? '0';
    $totalGuru = $totalGuru ?? '0';
    $tingkatKehadiran = $tingkatKehadiran ?? '100';
    $totalAlumni = $totalAlumni ?? '0';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $sysName }} - {{ $schoolName }}</title>
    <link rel="icon" type="image/png" href="{{ $uiSettings['website_favicon_url'] ?? $imgLogo }}" />
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
            min-height: 330px;
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
            background: linear-gradient(90deg,
                    rgba(15, 23, 42, 0.88) 0%,
                    rgba(15, 23, 42, 0.60) 50%,
                    rgba(15, 23, 42, 0.20) 100%);
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
                    <img src="{{ $imgLogo }}" alt="Logo {{ $schoolName }}" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='{{ asset('images/logo-smk.png') }}';" />
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">{{ $sysName }}</span>
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                    </div>
                    <p class="text-[10.5px] font-semibold text-slate-500 tracking-tight mt-0.5">{{ $uiSettings['website_slogan'] ?? 'Sistem Informasi Manajemen SMK Nurul Hidayah' }}</p>
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
                    {{ $uiSettings['portal_hero_badge'] ?? ($sysName . ' • Integrated System Portal') }}
                </div>

                <!-- Title -->
                <div class="hero-sub">{{ $uiSettings['portal_hero_subtitle'] ?? 'Selamat Datang di' }}</div>
                <h1>
                    @if(!empty($lastWord))
                        <span class="highlight">{{ $firstPart }}</span> {{ $lastWord }}
                    @else
                        <span class="highlight">{{ $heroTitle }}</span>
                    @endif
                </h1>

                <!-- Tagline -->
                <p class="tagline">
                    <i class="fas fa-quote-left"></i>
                    {{ $uiSettings['portal_tagline'] ?? 'Digitalisasi Layanan Sekolah dalam Satu Platform.' }}
                </p>

                <!-- Meta Info Realtime -->
                <div class="hero-meta">
                    @if (($uiSettings['portal_hero_date_show'] ?? '1') === '1')
                        <span class="meta-item">
                            <i class="far fa-calendar-alt"></i>
                            {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                        </span>
                    @endif

                    @if (!empty($uiSettings['portal_hero_meta_tag']))
                        @if (($uiSettings['portal_hero_date_show'] ?? '1') === '1')
                            <span class="meta-divider"></span>
                        @endif
                        <span class="meta-item">
                            <i class="fas fa-award"></i>
                            {{ $uiSettings['portal_hero_meta_tag'] }}
                        </span>
                    @endif

                    @if (!empty($uiSettings['portal_hero_location']))
                        @if (($uiSettings['portal_hero_date_show'] ?? '1') === '1' || !empty($uiSettings['portal_hero_meta_tag']))
                            <span class="meta-divider"></span>
                        @endif
                        <span class="meta-item">
                            <i class="fas fa-map-pin"></i>
                            {{ $uiSettings['portal_hero_location'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- ── Section Title ── -->
        <div class="text-center space-y-1.5">
            <div class="gradient-bar"></div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                {{ $uiSettings['portal_section_title'] ?? ($sysName . ' Dashboard') }}
            </h2>
            <p class="text-sm font-medium text-slate-500">
                {{ $uiSettings['portal_section_subtitle'] ?? ('Akses cepat ke sub aplikasi ' . $sysName) }}
            </p>
        </div>

        <!-- ── 5 SUB-APPLICATION CARDS ── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

            <!-- 1. Absen -->
            @if (($uiSettings['portal_card1_active'] ?? '1') === '1')
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $uiSettings['portal_card1_photo_url'] ?? asset('images/cards/art-01.png') }}" alt="{{ $uiSettings['portal_card1_title'] ?? ($sysName . ' Absen') }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/cards/art-01.png') }}';" />
                    </div>
                    <h3 class="card-title">{{ $uiSettings['portal_card1_title'] ?? ($sysName . ' Absen') }}</h3>
                    <p class="card-desc">{{ $uiSettings['portal_card1_desc'] ?? 'Sistem absensi guru, siswa, dan tenaga kependidikan secara realtime' }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url($uiSettings['portal_card1_url'] ?? '/scanner') }}" class="btn-app btn-absen">
                        <span>{{ $uiSettings['portal_card1_btn_text'] ?? 'Buka Aplikasi' }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- 2. Finance -->
            @if (($uiSettings['portal_card2_active'] ?? '1') === '1')
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $uiSettings['portal_card2_photo_url'] ?? asset('images/cards/art-02.png') }}" alt="{{ $uiSettings['portal_card2_title'] ?? ($sysName . ' Finance') }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/cards/art-02.png') }}';" />
                    </div>
                    <h3 class="card-title">{{ $uiSettings['portal_card2_title'] ?? ($sysName . ' Finance') }}</h3>
                    <p class="card-desc">{{ $uiSettings['portal_card2_desc'] ?? 'Pengelolaan kas, SPP, dan tagihan keuangan sekolah transparan' }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url($uiSettings['portal_card2_url'] ?? '/keuangan/pembayaran') }}" class="btn-app btn-finance">
                        <span>{{ $uiSettings['portal_card2_btn_text'] ?? 'Buka Aplikasi' }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- 3. Letter -->
            @if (($uiSettings['portal_card3_active'] ?? '1') === '1')
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $uiSettings['portal_card3_photo_url'] ?? asset('images/cards/art-03.png') }}" alt="{{ $uiSettings['portal_card3_title'] ?? ($sysName . ' Letter') }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/cards/art-03.png') }}';" />
                    </div>
                    <h3 class="card-title">{{ $uiSettings['portal_card3_title'] ?? ($sysName . ' Letter') }}</h3>
                    <p class="card-desc">{{ $uiSettings['portal_card3_desc'] ?? 'Surat-menyurat dan administrasi tata usaha terintegrasi' }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url($uiSettings['portal_card3_url'] ?? '/persuratan') }}" class="btn-app btn-letter">
                        <span>{{ $uiSettings['portal_card3_btn_text'] ?? 'Buka Aplikasi' }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- 4. Alumni -->
            @if (($uiSettings['portal_card4_active'] ?? '1') === '1')
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $uiSettings['portal_card4_photo_url'] ?? asset('images/cards/art-04.png') }}" alt="{{ $uiSettings['portal_card4_title'] ?? ($sysName . ' Alumni') }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/cards/art-04.png') }}';" />
                    </div>
                    <h3 class="card-title">{{ $uiSettings['portal_card4_title'] ?? ($sysName . ' Alumni') }}</h3>
                    <p class="card-desc">{{ $uiSettings['portal_card4_desc'] ?? 'Tracer study dan database alumni terintegrasi secara digital' }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url($uiSettings['portal_card4_url'] ?? '/data-alumni') }}" class="btn-app btn-alumni">
                        <span>{{ $uiSettings['portal_card4_btn_text'] ?? 'Buka Aplikasi' }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- 5. Dashboard -->
            @if (($uiSettings['portal_card5_active'] ?? '1') === '1')
            <div class="card-app animate-slide-up">
                <div>
                    <div class="card-image">
                        <img src="{{ $uiSettings['portal_card5_photo_url'] ?? asset('images/cards/art-05.png') }}" alt="{{ $uiSettings['portal_card5_title'] ?? ($sysName . ' Dashboard') }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('images/cards/art-05.png') }}';" />
                    </div>
                    <h3 class="card-title">{{ $uiSettings['portal_card5_title'] ?? ($sysName . ' Dashboard') }}</h3>
                    <p class="card-desc">{{ $uiSettings['portal_card5_desc'] ?? 'Statistik, laporan dan monitoring analitik data sekolah realtime' }}</p>
                </div>
                <div class="pt-2">
                    <a href="{{ url($uiSettings['portal_card5_url'] ?? '/dashboard') }}" class="btn-app btn-dashboard">
                        <span>{{ $uiSettings['portal_card5_btn_text'] ?? 'Buka Aplikasi' }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endif

        </div>

        <!-- ── STATS CARDS REALTIME ── -->
        @if (($uiSettings['portal_stats_show'] ?? '1') === '1')
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- 1. Total Siswa -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-label">{{ $uiSettings['portal_stat1_label'] ?? 'Total Siswa' }}</div>
                    <div class="stat-value">{{ $totalSiswa }}</div>
                    <div class="stat-change">
                        <i class="fas fa-check-circle"></i> {{ $uiSettings['portal_stat1_sub'] ?? 'Data Aktif' }}
                    </div>
                </div>
            </div>

            <!-- 2. Guru & Tendik -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon indigo">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <div class="stat-label">{{ $uiSettings['portal_stat2_label'] ?? 'Guru & Tendik' }}</div>
                    <div class="stat-value">{{ $totalGuru }}</div>
                    <div class="stat-change">
                        <i class="fas fa-check-circle"></i> {{ $uiSettings['portal_stat2_sub'] ?? 'Terdaftar' }}
                    </div>
                </div>
            </div>

            <!-- 3. Tingkat Kehadiran -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon emerald">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-label">{{ $uiSettings['portal_stat3_label'] ?? 'Kehadiran' }}</div>
                    <div class="stat-value">{{ $tingkatKehadiran }}%</div>
                    <div class="text-[0.68rem] font-semibold text-emerald-600">{{ $uiSettings['portal_stat3_sub'] ?? 'Realtime Presensi' }}</div>
                </div>
            </div>

            <!-- 4. Total Alumni -->
            <div class="stat-card animate-slide-up">
                <div class="stat-icon amber">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="stat-label">{{ $uiSettings['portal_stat4_label'] ?? 'Total Alumni' }}</div>
                    <div class="stat-value">{{ $totalAlumni }}</div>
                    <div class="text-[0.68rem] font-semibold text-slate-500">{{ $uiSettings['portal_stat4_sub'] ?? 'Database Tracer' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- ── VISI & MISI ── -->
        @php
            $portalVisi = $uiSettings['portal_visi'] ?? '';
            $portalMisi = $uiSettings['portal_misi'] ?? '';
            $showVisiMisi = !empty(trim($portalVisi)) || !empty(trim($portalMisi));
        @endphp
        @if ($showVisiMisi)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <!-- Visi Card (5 cols) -->
            @if (!empty(trim($portalVisi)))
            <div class="lg:col-span-5 bg-gradient-to-br from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-6 sm:p-7 shadow-xl flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-300 text-xl shadow-inner">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div>
                            <span class="text-[10.5px] font-bold text-blue-300 uppercase tracking-widest block">Visi Sekolah</span>
                            <h3 class="text-xl font-black tracking-tight text-white">VISI</h3>
                        </div>
                    </div>
                    <blockquote class="text-slate-100 text-sm sm:text-base font-medium leading-relaxed italic border-l-4 border-blue-400 pl-4 my-4">
                        &ldquo;{{ $portalVisi }}&rdquo;
                    </blockquote>

                    <!-- 4 Pilar Visi -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2">
                        <div class="bg-white/10 backdrop-blur-xs border border-white/10 rounded-xl p-2 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-purple-500/30 text-purple-300 flex items-center justify-center text-xs mb-1">
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-[10px] font-bold tracking-tight uppercase text-purple-200 block">Berkarakter</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-xs border border-white/10 rounded-xl p-2 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-amber-500/30 text-amber-300 flex items-center justify-center text-xs mb-1">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <span class="text-[10px] font-bold tracking-tight uppercase text-amber-200 block">Kreatif</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-xs border border-white/10 rounded-xl p-2 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-emerald-500/30 text-emerald-300 flex items-center justify-center text-xs mb-1">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <span class="text-[10px] font-bold tracking-tight uppercase text-emerald-200 block">Berkompeten</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-xs border border-white/10 rounded-xl p-2 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-blue-500/30 text-blue-300 flex items-center justify-center text-xs mb-1">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span class="text-[10px] font-bold tracking-tight uppercase text-blue-200 block">Disiplin</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Tagline Islam -->
                <div class="relative z-10 pt-5 mt-4 border-t border-white/10 flex items-center justify-between text-[11px] font-bold text-blue-200 tracking-wider uppercase">
                    <span class="flex items-center gap-1.5"><i class="fas fa-moon text-blue-300"></i> IMAN</span>
                    <span class="flex items-center gap-1.5"><i class="fas fa-book-open text-blue-300"></i> ILMU</span>
                    <span class="flex items-center gap-1.5"><i class="fas fa-hand-holding-heart text-blue-300"></i> AMAL</span>
                    <span class="flex items-center gap-1.5"><i class="fas fa-mosque text-blue-300"></i> AKHLAK</span>
                </div>

                <!-- Background Glow -->
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
            </div>
            @endif

            <!-- Misi Card (7 cols) -->
            @if (!empty(trim($portalMisi)))
            <div class="{{ !empty(trim($portalVisi)) ? 'lg:col-span-7' : 'lg:col-span-12' }} bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-xl shadow-inner">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <span class="text-[10.5px] font-bold text-emerald-600 uppercase tracking-widest block">Misi Sekolah</span>
                            <h3 class="text-xl font-black tracking-tight text-slate-900">MISI</h3>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        @foreach (array_filter(array_map('trim', explode("\n", $portalMisi))) as $i => $baris)
                        <div class="flex items-start gap-3 p-2.5 rounded-2xl bg-slate-50/70 border border-slate-100 hover:border-slate-300 hover:bg-slate-50 transition">
                            <span class="flex-shrink-0 w-7 h-7 rounded-xl bg-blue-900 text-white font-extrabold text-xs flex items-center justify-center shadow-xs mt-0.5">
                                {{ $i + 1 }}
                            </span>
                            <p class="text-xs sm:text-sm font-medium text-slate-700 leading-relaxed pt-0.5">
                                {{ $baris }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

    </main>

    <!-- ═══ FOOTER ═══ -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 border-t border-slate-200/80 mt-6 relative z-10">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div class="font-medium text-center sm:text-left">
                {{ str_replace('{year}', date('Y'), $uiSettings['portal_footer_text'] ?? ('© ' . date('Y') . ' ' . $schoolName . ' • ' . $sysName . ' — Nurul Hidayah Integrated System')) }}
            </div>
            <div class="font-motto text-xl font-bold text-blue-800 tracking-wide text-center sm:text-right">
                {{ $uiSettings['portal_motto'] ?? 'Berilmu, Berakhlak, Berdaya Saing' }}
            </div>
        </div>
    </footer>

    <!-- ── Decorative Blobs ── -->
    <div class="pointer-events-none fixed bottom-0 left-0 w-56 h-28 bg-gradient-to-tr from-blue-500/10 via-indigo-400/8 to-transparent rounded-tr-full blur-2xl -z-0"></div>
    <div class="pointer-events-none fixed bottom-0 right-0 w-56 h-28 bg-gradient-to-tl from-blue-500/10 via-cyan-400/8 to-transparent rounded-tl-full blur-2xl -z-0"></div>

</body>
</html>