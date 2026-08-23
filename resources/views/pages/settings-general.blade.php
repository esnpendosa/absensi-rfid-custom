@extends('layouts.page')

@section('title', 'Pengaturan Umum & Portal')

@section('content')
<div class="view-section active animate-fade-in space-y-6">

    <!-- Header Box -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-indigo-600"></i>
                    Pengaturan Umum &amp; Portal
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola identitas sekolah, branding sistem, tampilan hero banner, 5 kartu sub-aplikasi, statistik, dan footer portal.</p>
            </div>
            <div>
                <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                    Lihat Halaman Portal
                </a>
            </div>
        </div>

        <div class="p-5">
            <div id="general-setting-error" class="{{ $errors->any() ? '' : 'hidden' }} mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs leading-relaxed">
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div><i class="fas fa-circle-exclamation mr-1"></i> {{ $error }}</div>
                    @endforeach
                @endif
            </div>

            <!-- Tab Navigation Buttons -->
            <div class="flex items-center gap-2 border-b border-slate-200 pb-3 mb-6 overflow-x-auto scrollbar-hide text-xs font-bold">
                <button type="button" onclick="switchSettingsTab('tab-identitas')" id="btn-tab-identitas" class="tab-btn px-4 py-2 rounded-xl bg-indigo-600 text-white shadow-sm transition flex items-center gap-2 shrink-0">
                    <i class="fas fa-school"></i>
                    <span>Identitas &amp; Logo</span>
                </button>
                <button type="button" onclick="switchSettingsTab('tab-hero')" id="btn-tab-hero" class="tab-btn px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-2 shrink-0">
                    <i class="fas fa-image"></i>
                    <span>Hero Banner Portal</span>
                </button>
                <button type="button" onclick="switchSettingsTab('tab-cards')" id="btn-tab-cards" class="tab-btn px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-2 shrink-0">
                    <i class="fas fa-th-large"></i>
                    <span>5 Kartu Sub-Aplikasi</span>
                </button>
                <button type="button" onclick="switchSettingsTab('tab-stats-visi')" id="btn-tab-stats-visi" class="tab-btn px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-2 shrink-0">
                    <i class="fas fa-chart-pie"></i>
                    <span>Statistik &amp; Visi Misi</span>
                </button>
                <button type="button" onclick="switchSettingsTab('tab-footer-signer')" id="btn-tab-footer-signer" class="tab-btn px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-2 shrink-0">
                    <i class="fas fa-file-signature"></i>
                    <span>Footer &amp; Dokumen</span>
                </button>
            </div>

            <form id="general-setting-form" action="{{ route('settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- ════════ TAB 1: IDENTITAS & LOGO ════════ -->
                <div id="tab-identitas" class="tab-panel space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Nama Sekolah / Lembaga</label>
                            <input type="text" name="website_nama" value="{{ old('website_nama', $settings['website_nama'] ?? 'SMK Nurul Hidayah Bungah') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: SMK Nurul Hidayah Bungah">
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Nama Sistem (Singkatan / Brand)</label>
                            <input type="text" name="portal_system_name" value="{{ old('portal_system_name', $settings['portal_system_name'] ?? 'SIMNUHA') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: SIMNUHA">
                            <p class="text-[11px] text-slate-500 mt-1">Tampil di header portal, sidebar aplikasi, dan copyright.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Slogan Sub-Judul Header</label>
                            <input type="text" name="website_slogan" value="{{ old('website_slogan', $settings['website_slogan'] ?? 'Sistem Informasi Manajemen SMK Nurul Hidayah') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: Sistem Informasi Manajemen SMK Nurul Hidayah">
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Zona Waktu Sistem</label>
                            <select name="website_timezone" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                                @foreach (($timezoneOptions ?? []) as $tzValue => $tzLabel)
                                    <option value="{{ $tzValue }}" {{ old('website_timezone', $settings['website_timezone'] ?? 'Asia/Jakarta') === $tzValue ? 'selected' : '' }}>
                                        {{ $tzLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Deskripsi Singkat</label>
                        <textarea name="website_deskripsi" rows="2" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Deskripsi aplikasi atau profil ringkas...">{{ old('website_deskripsi', $settings['website_deskripsi'] ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Email Kontak</label>
                            <input type="email" name="website_email" value="{{ old('website_email', $settings['website_email'] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="admin@smknurulhidayah.sch.id">
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Telepon / WhatsApp</label>
                            <input type="text" name="website_telepon" value="{{ old('website_telepon', $settings['website_telepon'] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <!-- Logo Website -->
                        <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50">
                            <label class="block mb-1 text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-image text-indigo-500"></i> Logo Sekolah / Sistem
                            </label>
                            <input id="general-setting-logo-input" type="file" name="website_logo" accept=".png,.jpg,.jpeg,.webp,.svg" class="w-full text-xs text-slate-700 mt-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-[11px] text-slate-500 mt-1.5">Format: PNG / JPG / WEBP / SVG. Maks 2MB. Tampil di header portal, sidebar, dan cetak kartu.</p>
                            <div id="logo-preview-wrapper" class="mt-3 flex items-center gap-3.5 {{ empty($settings['website_logo_url']) ? 'hidden' : '' }}">
                                <img id="logo-preview-image" src="{{ $settings['website_logo_url'] ?? asset('images/logo-smk.png') }}" alt="Logo Website" class="w-14 h-14 object-contain rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                                <label class="inline-flex items-center gap-1.5 text-xs text-red-600 font-semibold cursor-pointer">
                                    <input id="remove-logo-checkbox" type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    Hapus logo kustom
                                </label>
                            </div>
                        </div>

                        <!-- Favicon Website -->
                        <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50">
                            <label class="block mb-1 text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-bookmark text-indigo-500"></i> Favicon Browser
                            </label>
                            <input id="general-setting-favicon-input" type="file" name="website_favicon" accept=".png,.ico,.svg,.webp" class="w-full text-xs text-slate-700 mt-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-[11px] text-slate-500 mt-1.5">Format: ICO / PNG / SVG / WEBP. Maks 1MB. Ikon tab browser.</p>
                            <div id="favicon-preview-wrapper" class="mt-3 flex items-center gap-3.5 {{ empty($settings['website_favicon_url']) ? 'hidden' : '' }}">
                                <img id="favicon-preview-image" src="{{ $settings['website_favicon_url'] ?? $settings['website_logo_url'] ?? asset('images/logo-smk.png') }}" alt="Favicon" class="w-10 h-10 object-contain rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                                <label class="inline-flex items-center gap-1.5 text-xs text-red-600 font-semibold cursor-pointer">
                                    <input id="remove-favicon-checkbox" type="checkbox" name="remove_favicon" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    Hapus favicon
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ════════ TAB 2: HERO BANNER PORTAL ════════ -->
                <div id="tab-hero" class="tab-panel hidden space-y-5">
                    <div class="border border-indigo-100 rounded-2xl p-4 bg-indigo-50/40">
                        <label class="block mb-1 text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-building text-indigo-600"></i> Foto Gedung Latar Hero Banner
                        </label>
                        <input id="building-photo-input" type="file" name="portal_building_photo" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-700 mt-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                        <p class="text-[11px] text-slate-500 mt-1.5">Format: JPG / PNG / WEBP. Maks 5MB. Digunakan sebagai background megah pada banner portal terdepan.</p>
                        <div id="building-preview-wrapper" class="mt-3 flex items-center gap-4 {{ empty($settings['portal_building_photo_url']) ? 'hidden' : '' }}">
                            <img id="building-preview-image" src="{{ $settings['portal_building_photo_url'] ?? asset('images/hero-building-clean.png') }}" alt="Foto Gedung Portal" class="h-20 w-36 object-cover rounded-xl border border-slate-300 bg-white shadow-sm" onerror="this.onerror=null; this.src='{{ asset('images/hero-building-clean.png') }}';">
                            <label class="inline-flex items-center gap-1.5 text-xs text-red-600 font-semibold cursor-pointer">
                                <input id="remove-building-checkbox" type="checkbox" name="remove_building_photo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                Hapus foto gedung kustom
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Badge Teks Hero</label>
                            <input type="text" name="portal_hero_badge" value="{{ old('portal_hero_badge', $settings['portal_hero_badge'] ?? 'SIMNUHA • Integrated System Portal') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="SIMNUHA • Integrated System Portal">
                            <p class="text-[11px] text-slate-500 mt-1">Pills badge kecil dengan titik animasi di atas judul banner.</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Teks Ucapan / Subtitle</label>
                            <input type="text" name="portal_hero_subtitle" value="{{ old('portal_hero_subtitle', $settings['portal_hero_subtitle'] ?? 'Selamat Datang di') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Selamat Datang di">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Judul Utama Banner Hero</label>
                        <input type="text" name="portal_hero_title" value="{{ old('portal_hero_title', $settings['portal_hero_title'] ?? 'SMK Nurul Hidayah Bungah') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: SMK Nurul Hidayah Bungah">
                        <p class="text-[11px] text-slate-500 mt-1">Teks judul besar yang tampil di tengah hero banner.</p>
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Tagline Hero Banner</label>
                        <input type="text" name="portal_tagline" value="{{ old('portal_tagline', $settings['portal_tagline'] ?? 'Digitalisasi Layanan Sekolah dalam Satu Platform.') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Digitalisasi Layanan Sekolah dalam Satu Platform.">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Status / Akreditasi Badge</label>
                            <input type="text" name="portal_hero_meta_tag" value="{{ old('portal_hero_meta_tag', $settings['portal_hero_meta_tag'] ?? 'SMK Pusat Keunggulan') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: SMK Pusat Keunggulan">
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Lokasi / Kota</label>
                            <input type="text" name="portal_hero_location" value="{{ old('portal_hero_location', $settings['portal_hero_location'] ?? 'Bungah, Gresik') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: Bungah, Gresik">
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="portal_hero_date_show" value="1" {{ ($settings['portal_hero_date_show'] ?? '1') === '1' ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span>Tampilkan Tanggal Realtime</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ════════ TAB 3: 5 KARTU SUB-APLIKASI ════════ -->
                <div id="tab-cards" class="tab-panel hidden space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-2 border-b border-slate-100">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Judul Seksi Dashboard</label>
                            <input type="text" name="portal_section_title" value="{{ old('portal_section_title', $settings['portal_section_title'] ?? 'SIMNUHA Dashboard') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="SIMNUHA Dashboard">
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Sub-Judul Seksi Dashboard</label>
                            <input type="text" name="portal_section_subtitle" value="{{ old('portal_section_subtitle', $settings['portal_section_subtitle'] ?? 'Akses cepat ke 5 sub aplikasi SIMNUHA') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Akses cepat ke 5 sub aplikasi SIMNUHA">
                        </div>
                    </div>

                    <!-- 5 Card Configs Accordion / Grid -->
                    <div class="space-y-4">
                        @php
                            $cardThemes = [
                                1 => ['name' => 'Absen', 'color' => 'emerald', 'icon' => 'fa-fingerprint', 'default_img' => asset('images/cards/art-01.png')],
                                2 => ['name' => 'Finance', 'color' => 'blue', 'icon' => 'fa-coins', 'default_img' => asset('images/cards/art-02.png')],
                                3 => ['name' => 'Letter', 'color' => 'purple', 'icon' => 'fa-envelope-open-text', 'default_img' => asset('images/cards/art-03.png')],
                                4 => ['name' => 'Alumni', 'color' => 'amber', 'icon' => 'fa-user-graduate', 'default_img' => asset('images/cards/art-04.png')],
                                5 => ['name' => 'Dashboard', 'color' => 'cyan', 'icon' => 'fa-chart-line', 'default_img' => asset('images/cards/art-05.png')],
                            ];
                        @endphp

                        @for ($i = 1; $i <= 5; $i++)
                            @php
                                $theme = $cardThemes[$i];
                                $titleKey = "portal_card{$i}_title";
                                $descKey = "portal_card{$i}_desc";
                                $urlKey = "portal_card{$i}_url";
                                $btnKey = "portal_card{$i}_btn_text";
                                $activeKey = "portal_card{$i}_active";
                                $photoPathKey = "portal_card{$i}_photo_path";
                                $photoUrlKey = "portal_card{$i}_photo_url";
                                $cardPhotoUrl = $settings[$photoUrlKey] ?? $theme['default_img'];
                            @endphp

                            <div class="border border-slate-200 rounded-2xl p-4.5 bg-white shadow-xs">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center">#{{ $i }}</span>
                                        <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                            <i class="fas {{ $theme['icon'] }} text-{{ $theme['color'] }}-600"></i>
                                            Sub-Aplikasi: {{ $settings[$titleKey] ?? ($settings['portal_system_name'] ?? 'SIMNUHA') . ' ' . $theme['name'] }}
                                        </h4>
                                    </div>
                                    <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="{{ $activeKey }}" value="1" {{ ($settings[$activeKey] ?? '1') === '1' ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>Aktif / Tampilkan</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block mb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Judul Kartu</label>
                                        <input type="text" name="{{ $titleKey }}" value="{{ old($titleKey, $settings[$titleKey] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: SIMNUHA {{ $theme['name'] }}">
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Target Link URL</label>
                                        <input type="text" name="{{ $urlKey }}" value="{{ old($urlKey, $settings[$urlKey] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: /scanner atau https://...">
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Teks Tombol Aksi</label>
                                        <input type="text" name="{{ $btnKey }}" value="{{ old($btnKey, $settings[$btnKey] ?? 'Buka Aplikasi') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Buka Aplikasi">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                    <div class="md:col-span-2">
                                        <label class="block mb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Deskripsi Singkat</label>
                                        <textarea name="{{ $descKey }}" rows="2" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Penjelasan singkat fitur...">{{ old($descKey, $settings[$descKey] ?? '') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Upload Banner Kustom (Opsional)</label>
                                        <input type="file" name="portal_card{{ $i }}_photo" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                                        <div class="mt-2 flex items-center gap-2.5">
                                            <img src="{{ $cardPhotoUrl }}" alt="Preview Card {{ $i }}" class="h-9 w-16 object-cover rounded-lg border border-slate-200 bg-white">
                                            @if (!empty($settings[$photoPathKey]))
                                                <label class="inline-flex items-center gap-1 text-[11px] text-red-600 font-semibold cursor-pointer">
                                                    <input type="checkbox" name="remove_card{{ $i }}_photo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                                    Reset
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- ════════ TAB 4: STATISTIK & VISI MISI ════════ -->
                <div id="tab-stats-visi" class="tab-panel hidden space-y-6">
                    <!-- Statistik Bar Config -->
                    <div class="border border-slate-200 rounded-2xl p-4.5 bg-slate-50/50">
                        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3 mb-4">
                            <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fas fa-chart-bar text-indigo-600"></i>
                                Pengaturan Baris Statistik Realtime
                            </h4>
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="portal_stats_show" value="1" {{ ($settings['portal_stats_show'] ?? '1') === '1' ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span>Tampilkan Blok Statistik di Portal</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Stat 1 -->
                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs space-y-2">
                                <span class="text-[11px] font-bold text-blue-600 uppercase flex items-center gap-1"><i class="fas fa-users"></i> Stat 1 (Siswa)</span>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Label Utama</label>
                                    <input type="text" name="portal_stat1_label" value="{{ old('portal_stat1_label', $settings['portal_stat1_label'] ?? 'Total Siswa') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Sub-Label Keterangan</label>
                                    <input type="text" name="portal_stat1_sub" value="{{ old('portal_stat1_sub', $settings['portal_stat1_sub'] ?? 'Data Aktif') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                            </div>

                            <!-- Stat 2 -->
                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs space-y-2">
                                <span class="text-[11px] font-bold text-indigo-600 uppercase flex items-center gap-1"><i class="fas fa-chalkboard-teacher"></i> Stat 2 (Guru)</span>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Label Utama</label>
                                    <input type="text" name="portal_stat2_label" value="{{ old('portal_stat2_label', $settings['portal_stat2_label'] ?? 'Guru & Tendik') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Sub-Label Keterangan</label>
                                    <input type="text" name="portal_stat2_sub" value="{{ old('portal_stat2_sub', $settings['portal_stat2_sub'] ?? 'Terdaftar') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                            </div>

                            <!-- Stat 3 -->
                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs space-y-2">
                                <span class="text-[11px] font-bold text-emerald-600 uppercase flex items-center gap-1"><i class="fas fa-calendar-check"></i> Stat 3 (Presensi)</span>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Label Utama</label>
                                    <input type="text" name="portal_stat3_label" value="{{ old('portal_stat3_label', $settings['portal_stat3_label'] ?? 'Kehadiran') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Sub-Label Keterangan</label>
                                    <input type="text" name="portal_stat3_sub" value="{{ old('portal_stat3_sub', $settings['portal_stat3_sub'] ?? 'Realtime Presensi') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                            </div>

                            <!-- Stat 4 -->
                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs space-y-2">
                                <span class="text-[11px] font-bold text-amber-600 uppercase flex items-center gap-1"><i class="fas fa-user-graduate"></i> Stat 4 (Alumni)</span>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Label Utama</label>
                                    <input type="text" name="portal_stat4_label" value="{{ old('portal_stat4_label', $settings['portal_stat4_label'] ?? 'Total Alumni') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Sub-Label Keterangan</label>
                                    <input type="text" name="portal_stat4_sub" value="{{ old('portal_stat4_sub', $settings['portal_stat4_sub'] ?? 'Database Tracer') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-lg p-2 mt-0.5">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visi & Misi Box -->
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-eye text-blue-600"></i> Teks Visi Sekolah
                            </label>
                            <textarea name="portal_visi" rows="2" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Menjadi SMK terbaik yang berkarakter, berdaya saing, dan berakhlak mulia...">{{ old('portal_visi', $settings['portal_visi'] ?? '') }}</textarea>
                            <p class="text-[11px] text-slate-500 mt-1">Biarkan kosong jika tidak ingin menampilkan kotak Visi di portal.</p>
                        </div>

                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-bullseye text-emerald-600"></i> Teks Misi Sekolah (1 Butir per Baris)
                            </label>
                            <textarea name="portal_misi" rows="4" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Menyelenggarakan pendidikan kejuruan berkualitas...&#10;Membentuk karakter peserta didik yang berakhlak mulia...">{{ old('portal_misi', $settings['portal_misi'] ?? '') }}</textarea>
                            <p class="text-[11px] text-slate-500 mt-1">Tulis satu butir misi per baris (tekan Enter). Setiap baris akan otomatis dirender sebagai poin bernomor.</p>
                        </div>
                    </div>
                </div>

                <!-- ════════ TAB 5: FOOTER & DOKUMEN ════════ -->
                <div id="tab-footer-signer" class="tab-panel hidden space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Motto / Slogan Footer</label>
                            <input type="text" name="portal_motto" value="{{ old('portal_motto', $settings['portal_motto'] ?? 'Berilmu, Berakhlak, Berdaya Saing') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: Berilmu, Berakhlak, Berdaya Saing">
                            <p class="text-[11px] text-slate-500 mt-1">Tampil di sisi kanan bawah footer dengan gaya tulisan kaligrafi/motto.</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Teks Copyright Footer</label>
                            <input type="text" name="portal_footer_text" value="{{ old('portal_footer_text', $settings['portal_footer_text'] ?? '© {year} SMK Nurul Hidayah Bungah • SIMNUHA — Nurul Hidayah Integrated System') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Gunakan {year} untuk tahun otomatis">
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-4 mt-4">
                        <h4 class="font-extrabold text-xs text-slate-500 uppercase tracking-wider mb-3">Penanda Tangan Laporan &amp; Kartu Siswa</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Nama Penanda Tangan</label>
                                <input type="text" name="report_signer_name" value="{{ old('report_signer_name', $settings['report_signer_name'] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: Drs. H. Ahmad">
                                <p class="text-[11px] text-slate-500 mt-1">Dipakai pada blok tanda tangan cetak PDF jurnal &amp; laporan.</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Jabatan Penanda Tangan</label>
                                <input type="text" name="report_signer_position" value="{{ old('report_signer_position', $settings['report_signer_position'] ?? 'Kepala Sekolah') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: Kepala Sekolah">
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold text-slate-600 uppercase tracking-wider">Tahun Ajaran Kartu Siswa</label>
                                <input type="text" name="student_card_academic_year" value="{{ old('student_card_academic_year', $settings['student_card_academic_year'] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" placeholder="Contoh: 2025/2026">
                                <p class="text-[11px] text-slate-500 mt-1">Header cetak kartu siswa (kosongkan untuk otomatis).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                    <p class="text-xs text-slate-500">Perubahan akan langsung diterapkan ke sistem dan halaman portal.</p>
                    <button id="general-setting-submit" type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition">
                        <i class="fas fa-save text-xs"></i>
                        <span id="general-setting-submit-text">Simpan Semua Pengaturan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchSettingsTab(tabId) {
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
        const activePanel = document.getElementById(tabId);
        if (activePanel) activePanel.classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-sm');
            btn.classList.add('bg-slate-100', 'text-slate-700');
        });

        const activeBtn = document.getElementById('btn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.remove('bg-slate-100', 'text-slate-700');
            activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-sm');
        }
    }

    (function () {
        const form = document.getElementById('general-setting-form');
        if (!form) return;

        const submitButton = document.getElementById('general-setting-submit');
        const submitText = document.getElementById('general-setting-submit-text');
        const errorBox = document.getElementById('general-setting-error');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function hideMessages() {
            if (errorBox) {
                errorBox.classList.add('hidden');
                errorBox.innerHTML = '';
            }
        }

        function showErrorMessages(messages) {
            if (!errorBox) return;
            const list = Array.isArray(messages) ? messages : [String(messages || 'Terjadi kesalahan.')];
            errorBox.innerHTML = list.map((text) => `<div><i class="fas fa-circle-exclamation mr-1"></i> ${String(text)}</div>`).join('');
            errorBox.classList.remove('hidden');
        }

        function setSubmitState(isLoading) {
            if (!submitButton || !submitText) return;
            submitButton.disabled = isLoading;
            if (isLoading) {
                submitButton.classList.add('opacity-80', 'cursor-not-allowed');
                submitText.textContent = 'Menyimpan...';
            } else {
                submitButton.classList.remove('opacity-80', 'cursor-not-allowed');
                submitText.textContent = 'Simpan Semua Pengaturan';
            }
        }

        function updateBrandUI(data) {
            if (!data || typeof data !== 'object') return;

            const websiteName = String(data.website_nama || '').trim();
            const websiteSlogan = String(data.website_slogan || '').trim();
            const logoUrl = data.website_logo_url ? String(data.website_logo_url) : '';
            const faviconUrl = data.website_favicon_url ? String(data.website_favicon_url) : '';
            const websiteTimezone = String(data.website_timezone || window.APP_TIMEZONE || 'Asia/Jakarta');
            const websiteTimezoneLabel = String(data.website_timezone_label || window.APP_TIMEZONE_LABEL || websiteTimezone);

            const sidebarBrandName = document.getElementById('sidebarBrandName');
            const sidebarBrandSlogan = document.getElementById('sidebarBrandSlogan');
            const sidebarLogoImg = document.getElementById('sidebarBrandLogoImg');

            if (sidebarBrandName && websiteName !== '') {
                sidebarBrandName.textContent = websiteName;
            }
            if (sidebarBrandSlogan) {
                sidebarBrandSlogan.textContent = websiteSlogan || 'School System';
            }
            if (sidebarLogoImg && logoUrl !== '') {
                sidebarLogoImg.src = logoUrl;
            }

            const pageTitlePrefix = document.title.split(' - ')[0] || 'Dashboard';
            document.title = `${pageTitlePrefix} - ${websiteName || 'Sistem Absensi Pintar'}`;

            window.APP_TIMEZONE = websiteTimezone;
            window.APP_TIMEZONE_LABEL = websiteTimezoneLabel;
            if (typeof window.updateHeaderCurrentDate === 'function') {
                window.updateHeaderCurrentDate();
            }

            const faviconLink = document.querySelector('link[rel="icon"]');
            if (faviconUrl !== '') {
                if (faviconLink) {
                    faviconLink.setAttribute('href', faviconUrl);
                } else {
                    const link = document.createElement('link');
                    link.setAttribute('rel', 'icon');
                    link.setAttribute('type', 'image/png');
                    link.setAttribute('href', faviconUrl);
                    document.head.appendChild(link);
                }
            }

            // Update logo preview
            const logoPreviewWrapper = document.getElementById('logo-preview-wrapper');
            const logoPreviewImage = document.getElementById('logo-preview-image');
            if (logoPreviewWrapper && logoPreviewImage && logoUrl !== '') {
                logoPreviewImage.src = logoUrl;
                logoPreviewWrapper.classList.remove('hidden');
            }

            // Update building preview
            const buildingPhotoUrl = data.portal_building_photo_url ? String(data.portal_building_photo_url) : '';
            const buildingPreviewWrapper = document.getElementById('building-preview-wrapper');
            const buildingPreviewImage = document.getElementById('building-preview-image');
            if (buildingPreviewWrapper && buildingPreviewImage && buildingPhotoUrl !== '') {
                buildingPreviewImage.src = buildingPhotoUrl;
                buildingPreviewWrapper.classList.remove('hidden');
            }
        }

        async function handleSubmit(event) {
            event.preventDefault();
            hideMessages();
            setSubmitState(true);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(form),
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.success === false) {
                    if (response.status === 422 && payload.errors && typeof payload.errors === 'object') {
                        const errors = Object.values(payload.errors).flat();
                        showErrorMessages(errors);
                    } else {
                        showErrorMessages(payload.message || 'Gagal menyimpan pengaturan umum.');
                    }
                    if (window.showAlert) {
                        window.showAlert('error', payload.message || 'Gagal menyimpan pengaturan umum.');
                    }
                    return;
                }

                if (window.showAlert) {
                    window.showAlert('success', payload.message || 'Pengaturan umum dan portal berhasil disimpan.');
                }

                form.querySelectorAll('input[type="file"]').forEach((input) => {
                    input.value = '';
                });

                updateBrandUI(payload.data || {});
            } catch (error) {
                showErrorMessages(error.message || 'Terjadi kesalahan saat menyimpan.');
                if (window.showAlert) {
                    window.showAlert('error', error.message || 'Terjadi kesalahan saat menyimpan.');
                }
            } finally {
                setSubmitState(false);
            }
        }

        form.addEventListener('submit', handleSubmit);
    })();
</script>
@endpush