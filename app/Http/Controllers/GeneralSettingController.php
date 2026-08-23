<?php

namespace App\Http\Controllers;

use App\Models\Konfigurasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GeneralSettingController extends Controller
{
    /**
     * @return array<string, string>
     */
    protected function timezoneOptions(): array
    {
        return [
            'Asia/Jakarta' => 'WIB (UTC+07:00)',
            'Asia/Makassar' => 'WITA (UTC+08:00)',
            'Asia/Jayapura' => 'WIT (UTC+09:00)',
        ];
    }

    public function index(): View
    {
        return view('pages.settings-general', [
            'settings' => $this->getSettings(),
            'timezoneOptions' => $this->timezoneOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'website_nama' => ['required', 'string', 'max:120'],
            'website_slogan' => ['nullable', 'string', 'max:150'],
            'website_deskripsi' => ['nullable', 'string', 'max:500'],
            'website_email' => ['nullable', 'email', 'max:120'],
            'website_telepon' => ['nullable', 'string', 'max:40'],
            'website_timezone' => ['required', Rule::in(array_keys($this->timezoneOptions()))],
            'student_card_academic_year' => ['nullable', 'string', 'max:30'],
            'report_signer_name' => ['nullable', 'string', 'max:120'],
            'report_signer_position' => ['nullable', 'string', 'max:120'],
            'website_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'website_favicon' => ['nullable', 'file', 'mimes:png,ico,svg,webp', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],

            // Hero Portal
            'portal_system_name' => ['nullable', 'string', 'max:60'],
            'portal_tagline' => ['nullable', 'string', 'max:200'],
            'portal_hero_badge' => ['nullable', 'string', 'max:120'],
            'portal_hero_subtitle' => ['nullable', 'string', 'max:120'],
            'portal_hero_title' => ['nullable', 'string', 'max:150'],
            'portal_hero_meta_tag' => ['nullable', 'string', 'max:100'],
            'portal_hero_location' => ['nullable', 'string', 'max:100'],
            'portal_hero_date_show' => ['nullable', 'string', 'max:5'],
            'portal_building_photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'remove_building_photo' => ['nullable', 'boolean'],

            // Section Header
            'portal_section_title' => ['nullable', 'string', 'max:120'],
            'portal_section_subtitle' => ['nullable', 'string', 'max:200'],

            // Statistik
            'portal_stats_show' => ['nullable', 'string', 'max:5'],
            'portal_stat1_label' => ['nullable', 'string', 'max:80'],
            'portal_stat1_sub' => ['nullable', 'string', 'max:80'],
            'portal_stat2_label' => ['nullable', 'string', 'max:80'],
            'portal_stat2_sub' => ['nullable', 'string', 'max:80'],
            'portal_stat3_label' => ['nullable', 'string', 'max:80'],
            'portal_stat3_sub' => ['nullable', 'string', 'max:80'],
            'portal_stat4_label' => ['nullable', 'string', 'max:80'],
            'portal_stat4_sub' => ['nullable', 'string', 'max:80'],

            // Visi Misi & Footer
            'portal_visi' => ['nullable', 'string', 'max:1000'],
            'portal_misi' => ['nullable', 'string', 'max:2000'],
            'portal_footer_text' => ['nullable', 'string', 'max:255'],
            'portal_motto' => ['nullable', 'string', 'max:150'],

            // 5 Kartu Aplikasi
            'portal_card1_title' => ['nullable', 'string', 'max:80'],
            'portal_card1_desc' => ['nullable', 'string', 'max:255'],
            'portal_card1_url' => ['nullable', 'string', 'max:255'],
            'portal_card1_btn_text' => ['nullable', 'string', 'max:60'],
            'portal_card1_active' => ['nullable', 'string', 'max:5'],
            'portal_card1_photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_card1_photo' => ['nullable', 'boolean'],

            'portal_card2_title' => ['nullable', 'string', 'max:80'],
            'portal_card2_desc' => ['nullable', 'string', 'max:255'],
            'portal_card2_url' => ['nullable', 'string', 'max:255'],
            'portal_card2_btn_text' => ['nullable', 'string', 'max:60'],
            'portal_card2_active' => ['nullable', 'string', 'max:5'],
            'portal_card2_photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_card2_photo' => ['nullable', 'boolean'],

            'portal_card3_title' => ['nullable', 'string', 'max:80'],
            'portal_card3_desc' => ['nullable', 'string', 'max:255'],
            'portal_card3_url' => ['nullable', 'string', 'max:255'],
            'portal_card3_btn_text' => ['nullable', 'string', 'max:60'],
            'portal_card3_active' => ['nullable', 'string', 'max:5'],
            'portal_card3_photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_card3_photo' => ['nullable', 'boolean'],

            'portal_card4_title' => ['nullable', 'string', 'max:80'],
            'portal_card4_desc' => ['nullable', 'string', 'max:255'],
            'portal_card4_url' => ['nullable', 'string', 'max:255'],
            'portal_card4_btn_text' => ['nullable', 'string', 'max:60'],
            'portal_card4_active' => ['nullable', 'string', 'max:5'],
            'portal_card4_photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_card4_photo' => ['nullable', 'boolean'],

            'portal_card5_title' => ['nullable', 'string', 'max:80'],
            'portal_card5_desc' => ['nullable', 'string', 'max:255'],
            'portal_card5_url' => ['nullable', 'string', 'max:255'],
            'portal_card5_btn_text' => ['nullable', 'string', 'max:60'],
            'portal_card5_active' => ['nullable', 'string', 'max:5'],
            'portal_card5_photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_card5_photo' => ['nullable', 'boolean'],
        ]);

        $settings = $this->getSettings();
        $logoPath = (string) ($settings['website_logo_path'] ?? '');
        $faviconPath = (string) ($settings['website_favicon_path'] ?? '');
        $buildingPhotoPath = (string) ($settings['portal_building_photo_path'] ?? '');

        if ($request->boolean('remove_logo')) {
            $this->deletePublicFileIfExists($logoPath);
            $logoPath = '';
        }
        if ($request->boolean('remove_favicon')) {
            $this->deletePublicFileIfExists($faviconPath);
            $faviconPath = '';
        }
        if ($request->boolean('remove_building_photo')) {
            $this->deletePublicFileIfExists($buildingPhotoPath);
            $buildingPhotoPath = '';
        }

        if ($request->hasFile('website_logo')) {
            $this->deletePublicFileIfExists($logoPath);
            $logoFile = $request->file('website_logo');
            $logoPath = $logoFile->store('settings', 'public');
            $this->mirrorFileToPublic($logoFile->getRealPath(), 'images/logo-smk.png');
        }

        if ($request->hasFile('website_favicon')) {
            $this->deletePublicFileIfExists($faviconPath);
            $faviconFile = $request->file('website_favicon');
            $faviconPath = $faviconFile->store('settings', 'public');
        }

        if ($request->hasFile('portal_building_photo')) {
            $this->deletePublicFileIfExists($buildingPhotoPath);
            $buildingFile = $request->file('portal_building_photo');
            $buildingPhotoPath = $buildingFile->store('settings', 'public');
            $this->mirrorFileToPublic($buildingFile->getRealPath(), 'images/hero-building-clean.png');
            $this->mirrorFileToPublic($buildingFile->getRealPath(), 'images/portal-hero.png');
        }

        $cardPaths = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = "portal_card{$i}_photo";
            $pathKey = "portal_card{$i}_photo_path";
            $removeKey = "remove_card{$i}_photo";
            $currentPath = (string) ($settings[$pathKey] ?? '');

            if ($request->boolean($removeKey)) {
                $this->deletePublicFileIfExists($currentPath);
                $currentPath = '';
            }

            if ($request->hasFile($key)) {
                $this->deletePublicFileIfExists($currentPath);
                $cardFile = $request->file($key);
                $currentPath = $cardFile->store('settings', 'public');
                $this->mirrorFileToPublic($cardFile->getRealPath(), "images/cards/art-0{$i}.png");
            }

            $cardPaths[$pathKey] = $currentPath;
        }

        $payload = [
            'website_nama' => trim((string) ($validated['website_nama'] ?? '')),
            'website_slogan' => $this->nullableTrim($validated['website_slogan'] ?? null),
            'website_deskripsi' => $this->nullableTrim($validated['website_deskripsi'] ?? null),
            'website_email' => $this->nullableTrim($validated['website_email'] ?? null),
            'website_telepon' => $this->nullableTrim($validated['website_telepon'] ?? null),
            'website_timezone' => (string) ($validated['website_timezone'] ?? 'Asia/Jakarta'),
            'student_card_academic_year' => $this->nullableTrim($validated['student_card_academic_year'] ?? null),
            'report_signer_name' => $this->nullableTrim($validated['report_signer_name'] ?? null),
            'report_signer_position' => $this->nullableTrim($validated['report_signer_position'] ?? null) ?: 'Kepala Sekolah',
            'website_logo_path' => $logoPath,
            'website_favicon_path' => $faviconPath,

            // Hero
            'portal_system_name' => $this->nullableTrim($validated['portal_system_name'] ?? null),
            'portal_tagline' => $this->nullableTrim($validated['portal_tagline'] ?? null),
            'portal_hero_badge' => $this->nullableTrim($validated['portal_hero_badge'] ?? null),
            'portal_hero_subtitle' => $this->nullableTrim($validated['portal_hero_subtitle'] ?? null),
            'portal_hero_title' => $this->nullableTrim($validated['portal_hero_title'] ?? null),
            'portal_hero_meta_tag' => $this->nullableTrim($validated['portal_hero_meta_tag'] ?? null),
            'portal_hero_location' => $this->nullableTrim($validated['portal_hero_location'] ?? null),
            'portal_hero_date_show' => $request->has('portal_hero_date_show') ? '1' : '0',
            'portal_building_photo_path' => $buildingPhotoPath,

            // Section
            'portal_section_title' => $this->nullableTrim($validated['portal_section_title'] ?? null),
            'portal_section_subtitle' => $this->nullableTrim($validated['portal_section_subtitle'] ?? null),

            // Stats
            'portal_stats_show' => $request->has('portal_stats_show') ? '1' : '0',
            'portal_stat1_label' => $this->nullableTrim($validated['portal_stat1_label'] ?? null),
            'portal_stat1_sub' => $this->nullableTrim($validated['portal_stat1_sub'] ?? null),
            'portal_stat2_label' => $this->nullableTrim($validated['portal_stat2_label'] ?? null),
            'portal_stat2_sub' => $this->nullableTrim($validated['portal_stat2_sub'] ?? null),
            'portal_stat3_label' => $this->nullableTrim($validated['portal_stat3_label'] ?? null),
            'portal_stat3_sub' => $this->nullableTrim($validated['portal_stat3_sub'] ?? null),
            'portal_stat4_label' => $this->nullableTrim($validated['portal_stat4_label'] ?? null),
            'portal_stat4_sub' => $this->nullableTrim($validated['portal_stat4_sub'] ?? null),

            // Visi Misi & Footer
            'portal_visi' => $this->nullableTrim($validated['portal_visi'] ?? null),
            'portal_misi' => $this->nullableTrim($validated['portal_misi'] ?? null),
            'portal_footer_text' => $this->nullableTrim($validated['portal_footer_text'] ?? null),
            'portal_motto' => $this->nullableTrim($validated['portal_motto'] ?? null),

            // Cards 1-5
            'portal_card1_title' => $this->nullableTrim($validated['portal_card1_title'] ?? null),
            'portal_card1_desc' => $this->nullableTrim($validated['portal_card1_desc'] ?? null),
            'portal_card1_url' => $this->nullableTrim($validated['portal_card1_url'] ?? null),
            'portal_card1_btn_text' => $this->nullableTrim($validated['portal_card1_btn_text'] ?? null),
            'portal_card1_active' => $request->has('portal_card1_active') ? '1' : '0',
            'portal_card1_photo_path' => $cardPaths['portal_card1_photo_path'] ?? '',

            'portal_card2_title' => $this->nullableTrim($validated['portal_card2_title'] ?? null),
            'portal_card2_desc' => $this->nullableTrim($validated['portal_card2_desc'] ?? null),
            'portal_card2_url' => $this->nullableTrim($validated['portal_card2_url'] ?? null),
            'portal_card2_btn_text' => $this->nullableTrim($validated['portal_card2_btn_text'] ?? null),
            'portal_card2_active' => $request->has('portal_card2_active') ? '1' : '0',
            'portal_card2_photo_path' => $cardPaths['portal_card2_photo_path'] ?? '',

            'portal_card3_title' => $this->nullableTrim($validated['portal_card3_title'] ?? null),
            'portal_card3_desc' => $this->nullableTrim($validated['portal_card3_desc'] ?? null),
            'portal_card3_url' => $this->nullableTrim($validated['portal_card3_url'] ?? null),
            'portal_card3_btn_text' => $this->nullableTrim($validated['portal_card3_btn_text'] ?? null),
            'portal_card3_active' => $request->has('portal_card3_active') ? '1' : '0',
            'portal_card3_photo_path' => $cardPaths['portal_card3_photo_path'] ?? '',

            'portal_card4_title' => $this->nullableTrim($validated['portal_card4_title'] ?? null),
            'portal_card4_desc' => $this->nullableTrim($validated['portal_card4_desc'] ?? null),
            'portal_card4_url' => $this->nullableTrim($validated['portal_card4_url'] ?? null),
            'portal_card4_btn_text' => $this->nullableTrim($validated['portal_card4_btn_text'] ?? null),
            'portal_card4_active' => $request->has('portal_card4_active') ? '1' : '0',
            'portal_card4_photo_path' => $cardPaths['portal_card4_photo_path'] ?? '',

            'portal_card5_title' => $this->nullableTrim($validated['portal_card5_title'] ?? null),
            'portal_card5_desc' => $this->nullableTrim($validated['portal_card5_desc'] ?? null),
            'portal_card5_url' => $this->nullableTrim($validated['portal_card5_url'] ?? null),
            'portal_card5_btn_text' => $this->nullableTrim($validated['portal_card5_btn_text'] ?? null),
            'portal_card5_active' => $request->has('portal_card5_active') ? '1' : '0',
            'portal_card5_photo_path' => $cardPaths['portal_card5_photo_path'] ?? '',
        ];

        foreach ($payload as $key => $value) {
            Konfigurasi::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) ($value ?? ''), 'keterangan' => 'Pengaturan website & portal']
            );
        }

        Cache::forget('app_ui_settings_v1');

        $freshSettings = $this->getSettings();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan umum dan portal berhasil disimpan.',
                'data' => $freshSettings,
            ]);
        }

        return back()->with('success', 'Pengaturan umum dan portal berhasil disimpan.');
    }

    protected function getSettings(): array
    {
        $defaults = [
            'website_nama' => 'SMK Nurul Hidayah Bungah',
            'website_slogan' => 'Sistem Informasi Manajemen SMK Nurul Hidayah',
            'website_deskripsi' => '',
            'website_email' => '',
            'website_telepon' => '',
            'website_timezone' => 'Asia/Jakarta',
            'student_card_academic_year' => '',
            'report_signer_name' => '',
            'report_signer_position' => 'Kepala Sekolah',
            'website_logo_path' => '',
            'website_favicon_path' => '',

            // Hero
            'portal_system_name' => 'SIMNUHA',
            'portal_tagline' => 'Digitalisasi Layanan Sekolah dalam Satu Platform.',
            'portal_hero_badge' => 'SIMNUHA • Integrated System Portal',
            'portal_hero_subtitle' => 'Selamat Datang di',
            'portal_hero_title' => 'SMK Nurul Hidayah Bungah',
            'portal_hero_meta_tag' => 'SMK Pusat Keunggulan',
            'portal_hero_location' => 'Bungah, Gresik',
            'portal_hero_date_show' => '1',
            'portal_building_photo_path' => '',

            // Section
            'portal_section_title' => 'SIMNUHA Dashboard',
            'portal_section_subtitle' => 'Akses cepat ke 5 sub aplikasi SIMNUHA',

            // Stats
            'portal_stats_show' => '1',
            'portal_stat1_label' => 'Total Siswa',
            'portal_stat1_sub' => 'Data Aktif',
            'portal_stat2_label' => 'Guru & Tendik',
            'portal_stat2_sub' => 'Terdaftar',
            'portal_stat3_label' => 'Kehadiran',
            'portal_stat3_sub' => 'Realtime Presensi',
            'portal_stat4_label' => 'Total Alumni',
            'portal_stat4_sub' => 'Database Tracer',

            // Visi Misi & Footer
            'portal_visi' => '',
            'portal_misi' => '',
            'portal_footer_text' => '© {year} SMK Nurul Hidayah Bungah • SIMNUHA — Nurul Hidayah Integrated System',
            'portal_motto' => 'Berilmu, Berakhlak, Berdaya Saing',

            // Cards 1-5
            'portal_card1_title' => 'SIMNUHA Absen',
            'portal_card1_desc' => 'Sistem absensi guru, siswa, dan tenaga kependidikan secara realtime',
            'portal_card1_url' => '/scanner',
            'portal_card1_btn_text' => 'Buka Aplikasi',
            'portal_card1_active' => '1',
            'portal_card1_photo_path' => '',

            'portal_card2_title' => 'SIMNUHA Finance',
            'portal_card2_desc' => 'Pengelolaan kas, SPP, dan tagihan keuangan sekolah transparan',
            'portal_card2_url' => '/keuangan/pembayaran',
            'portal_card2_btn_text' => 'Buka Aplikasi',
            'portal_card2_active' => '1',
            'portal_card2_photo_path' => '',

            'portal_card3_title' => 'SIMNUHA Letter',
            'portal_card3_desc' => 'Surat-menyurat dan administrasi tata usaha terintegrasi',
            'portal_card3_url' => '/persuratan',
            'portal_card3_btn_text' => 'Buka Aplikasi',
            'portal_card3_active' => '1',
            'portal_card3_photo_path' => '',

            'portal_card4_title' => 'SIMNUHA Alumni',
            'portal_card4_desc' => 'Tracer study dan database alumni terintegrasi secara digital',
            'portal_card4_url' => '/data-alumni',
            'portal_card4_btn_text' => 'Buka Aplikasi',
            'portal_card4_active' => '1',
            'portal_card4_photo_path' => '',

            'portal_card5_title' => 'SIMNUHA Dashboard',
            'portal_card5_desc' => 'Statistik, laporan dan monitoring analitik data sekolah realtime',
            'portal_card5_url' => '/dashboard',
            'portal_card5_btn_text' => 'Buka Aplikasi',
            'portal_card5_active' => '1',
            'portal_card5_photo_path' => '',
        ];

        $rows = Konfigurasi::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->all();

        $settings = array_merge($defaults, $rows);
        $timezoneOptions = $this->timezoneOptions();
        $websiteTimezone = trim((string) ($settings['website_timezone'] ?? 'Asia/Jakarta'));
        if (!array_key_exists($websiteTimezone, $timezoneOptions)) {
            $websiteTimezone = 'Asia/Jakarta';
        }
        $settings['website_timezone'] = $websiteTimezone;
        $settings['website_timezone_label'] = (string) ($timezoneOptions[$websiteTimezone] ?? 'WIB (UTC+07:00)');

        // Resolve logo URL
        $logoUrl = null;
        if (!empty($settings['website_logo_path'])) {
            $fullLogoPath = storage_path('app/public/' . ltrim($settings['website_logo_path'], '/'));
            if (file_exists($fullLogoPath)) {
                $logoUrl = asset('storage/' . ltrim($settings['website_logo_path'], '/'));
            }
        }
        $settings['website_logo_url'] = $logoUrl ?: asset('images/logo-smk.png');

        // Resolve favicon URL
        $faviconUrl = null;
        if (!empty($settings['website_favicon_path'])) {
            $fullFavPath = storage_path('app/public/' . ltrim($settings['website_favicon_path'], '/'));
            if (file_exists($fullFavPath)) {
                $faviconUrl = asset('storage/' . ltrim($settings['website_favicon_path'], '/'));
            }
        }
        $settings['website_favicon_url'] = $faviconUrl ?: $settings['website_logo_url'];

        // Resolve building photo URL
        $buildingPhotoUrl = null;
        if (!empty($settings['portal_building_photo_path'])) {
            $fullBuildingPath = storage_path('app/public/' . ltrim($settings['portal_building_photo_path'], '/'));
            if (file_exists($fullBuildingPath)) {
                $buildingPhotoUrl = asset('storage/' . ltrim($settings['portal_building_photo_path'], '/'));
            }
        }
        $settings['portal_building_photo_url'] = $buildingPhotoUrl ?: asset('images/hero-building-clean.png');

        // Resolve card photos
        for ($i = 1; $i <= 5; $i++) {
            $cardPathKey = "portal_card{$i}_photo_path";
            $cardUrlKey = "portal_card{$i}_photo_url";
            $cardPhotoUrl = null;
            if (!empty($settings[$cardPathKey])) {
                $fullCardPath = storage_path('app/public/' . ltrim($settings[$cardPathKey], '/'));
                if (file_exists($fullCardPath)) {
                    $cardPhotoUrl = asset('storage/' . ltrim($settings[$cardPathKey], '/'));
                }
            }
            $settings[$cardUrlKey] = $cardPhotoUrl ?: asset("images/cards/art-0{$i}.png");
        }

        return $settings;
    }

    protected function mirrorFileToPublic(string $sourcePath, string $relativePublicPath): void
    {
        try {
            $target = public_path($relativePublicPath);
            $dir = dirname($target);
            if (!file_exists($dir)) {
                @mkdir($dir, 0755, true);
            }
            @copy($sourcePath, $target);
        } catch (\Throwable $e) {}
    }

    protected function deletePublicFileIfExists(?string $path): void
    {
        $cleanPath = trim((string) $path);
        if ($cleanPath === '') {
            return;
        }

        if (!str_starts_with($cleanPath, 'settings/')) {
            return;
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }
    }

    protected function nullableTrim($value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text === '' ? null : $text;
    }
}