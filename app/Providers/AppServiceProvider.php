<?php

namespace App\Providers;

use App\Models\AuthToken;
use App\Models\Device;
use App\Models\IzinSakitRequest;
use App\Models\Konfigurasi;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->isInstallerMode()) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
                'queue.default' => 'sync',
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $defaultConnection = config('database.default');
        $defaultDriver = is_string($defaultConnection)
            ? config("database.connections.{$defaultConnection}.driver")
            : null;

        if (in_array($defaultDriver, ['mysql', 'mariadb'], true)) {
            // Shared hosting often still uses legacy MySQL index limits.
            Schema::defaultStringLength(191);
        }

        if ((bool) env('APP_FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        RateLimiter::for('device-attendance', function (Request $request): Limit {
            $device = $request->attributes->get('device');

            return Limit::perMinute(120)->by(
                $device instanceof Device
                    ? 'device:' . $device->id
                    : ((string) $request->bearerToken() !== '' ? 'token:' . $request->bearerToken() : 'ip:' . $request->ip())
            );
        });

        $settings = [
            'website_nama' => 'SMK Nurul Hidayah Bungah',
            'website_slogan' => 'Sistem Informasi Manajemen SMK Nurul Hidayah',
            'website_deskripsi' => 'Sistem Absensi Pintar & Portal Terpadu',
            'website_email' => '',
            'website_telepon' => '',
            'website_timezone' => 'Asia/Jakarta',
            'student_card_academic_year' => '',
            'report_signer_name' => '',
            'report_signer_position' => 'Kepala Sekolah',
            'website_logo_path' => '',
            'website_favicon_path' => '',
            'website_logo_url' => null,
            'website_favicon_url' => null,
            'portal_system_name' => 'SIMNUHA',
            'portal_tagline' => 'Digitalisasi Layanan Sekolah dalam Satu Platform.',
            'portal_hero_badge' => 'SIMNUHA • Integrated System Portal',
            'portal_hero_subtitle' => 'Selamat Datang di',
            'portal_hero_title' => 'SMK Nurul Hidayah Bungah',
            'portal_hero_meta_tag' => 'SMK Pusat Keunggulan',
            'portal_hero_location' => 'Bungah, Gresik',
            'portal_hero_date_show' => '1',
            'portal_building_photo_path' => '',
            'portal_building_photo_url' => null,
            'portal_section_title' => 'SIMNUHA Dashboard',
            'portal_section_subtitle' => 'Akses cepat ke 5 sub aplikasi SIMNUHA',
            'portal_stats_show' => '1',
            'portal_stat1_label' => 'Total Siswa',
            'portal_stat1_sub' => 'Data Aktif',
            'portal_stat2_label' => 'Guru & Tendik',
            'portal_stat2_sub' => 'Terdaftar',
            'portal_stat3_label' => 'Kehadiran',
            'portal_stat3_sub' => 'Realtime Presensi',
            'portal_stat4_label' => 'Total Alumni',
            'portal_stat4_sub' => 'Database Tracer',
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
            'portal_card1_photo_url' => null,
            'portal_card2_title' => 'SIMNUHA Finance',
            'portal_card2_desc' => 'Pengelolaan kas, SPP, dan tagihan keuangan sekolah transparan',
            'portal_card2_url' => '/keuangan/pembayaran',
            'portal_card2_btn_text' => 'Buka Aplikasi',
            'portal_card2_active' => '1',
            'portal_card2_photo_path' => '',
            'portal_card2_photo_url' => null,
            'portal_card3_title' => 'SIMNUHA Letter',
            'portal_card3_desc' => 'Surat-menyurat dan administrasi tata usaha terintegrasi',
            'portal_card3_url' => '/persuratan',
            'portal_card3_btn_text' => 'Buka Aplikasi',
            'portal_card3_active' => '1',
            'portal_card3_photo_path' => '',
            'portal_card3_photo_url' => null,
            'portal_card4_title' => 'SIMNUHA Alumni',
            'portal_card4_desc' => 'Tracer study dan database alumni terintegrasi secara digital',
            'portal_card4_url' => '/data-alumni',
            'portal_card4_btn_text' => 'Buka Aplikasi',
            'portal_card4_active' => '1',
            'portal_card4_photo_path' => '',
            'portal_card4_photo_url' => null,
            'portal_card5_title' => 'SIMNUHA Dashboard',
            'portal_card5_desc' => 'Statistik, laporan dan monitoring analitik data sekolah realtime',
            'portal_card5_url' => '/dashboard',
            'portal_card5_btn_text' => 'Buka Aplikasi',
            'portal_card5_active' => '1',
            'portal_card5_photo_path' => '',
            'portal_card5_photo_url' => null,
        ];

        try {
            if (Schema::hasTable('konfigurasi')) {
                $settings = Cache::remember('app_ui_settings_v1', 300, function () use ($settings) {
                    $keys = array_keys($settings);
                    $rows = Konfigurasi::query()
                        ->whereIn('key', $keys)
                        ->pluck('value', 'key')
                        ->all();

                    $final = array_merge($settings, $rows);
                    
                    $logoUrl = null;
                    if (!empty($final['website_logo_path'])) {
                        $fullLogoPath = storage_path('app/public/' . ltrim($final['website_logo_path'], '/'));
                        if (file_exists($fullLogoPath)) {
                            $logoUrl = asset('storage/' . ltrim($final['website_logo_path'], '/'));
                        }
                    }
                    if (!$logoUrl && class_exists(\App\Helpers\PortalAssets::class)) {
                        $logoUrl = \App\Helpers\PortalAssets::getLogo();
                    }
                    $final['website_logo_url'] = $logoUrl ?: asset('images/logo-smk.png');

                    $faviconUrl = null;
                    if (!empty($final['website_favicon_path'])) {
                        $fullFavPath = storage_path('app/public/' . ltrim($final['website_favicon_path'], '/'));
                        if (file_exists($fullFavPath)) {
                            $faviconUrl = asset('storage/' . ltrim($final['website_favicon_path'], '/'));
                        }
                    }
                    $final['website_favicon_url'] = $faviconUrl ?: $final['website_logo_url'];

                    $buildingPhotoUrl = null;
                    if (!empty($final['portal_building_photo_path'])) {
                        $fullBuildingPath = storage_path('app/public/' . ltrim($final['portal_building_photo_path'], '/'));
                        if (file_exists($fullBuildingPath)) {
                            $buildingPhotoUrl = asset('storage/' . ltrim($final['portal_building_photo_path'], '/'));
                        }
                    }
                    $final['portal_building_photo_url'] = $buildingPhotoUrl ?: asset('images/hero-building-clean.png');

                    for ($i = 1; $i <= 5; $i++) {
                        $cardPathKey = "portal_card{$i}_photo_path";
                        $cardUrlKey = "portal_card{$i}_photo_url";
                        $cardPhotoUrl = null;
                        if (!empty($final[$cardPathKey])) {
                            $fullCardPath = storage_path('app/public/' . ltrim($final[$cardPathKey], '/'));
                            if (file_exists($fullCardPath)) {
                                $cardPhotoUrl = asset('storage/' . ltrim($final[$cardPathKey], '/'));
                            }
                        }
                        if (!$cardPhotoUrl && class_exists(\App\Helpers\PortalAssets::class)) {
                            $getter = "getCard{$i}";
                            if (method_exists(\App\Helpers\PortalAssets::class, $getter)) {
                                $cardPhotoUrl = \App\Helpers\PortalAssets::$getter();
                            }
                        }
                        $final[$cardUrlKey] = $cardPhotoUrl ?: asset("images/cards/art-0{$i}.png");
                    }

                    return $final;
                });
            }
        } catch (\Throwable $e) {
            // Keep defaults when database is not available during installer mode.
        }

        $websiteTimezone = $this->resolveWebsiteTimezone((string) ($settings['website_timezone'] ?? 'Asia/Jakarta'));
        $settings['website_timezone'] = $websiteTimezone;
        $settings['website_timezone_label'] = $this->timezoneLabel($websiteTimezone);

        config(['app.timezone' => $websiteTimezone]);
        try {
            date_default_timezone_set($websiteTimezone);
        } catch (\Throwable $e) {
            // Abaikan jika timezone tidak valid.
        }

        View::share('appUiSettings', $settings);

        View::composer('layouts.main', function ($view): void {
            $view->with('appCurrentUserPayload', $this->resolveAppCurrentUserPayload());
        });

        View::composer('partials.sidebar', function ($view): void {
            $view->with('sidebarIzinSakitPendingCount', $this->resolveSidebarIzinSakitPendingCount());
        });
    }

    private function isInstallerMode(): bool
    {
        return !(bool) config('app.installed', true);
    }

    /**
     * @return array<string, string>
     */
    private function timezoneOptions(): array
    {
        return [
            'Asia/Jakarta' => 'WIB (UTC+07:00)',
            'Asia/Makassar' => 'WITA (UTC+08:00)',
            'Asia/Jayapura' => 'WIT (UTC+09:00)',
        ];
    }

    private function resolveWebsiteTimezone(string $timezone): string
    {
        $candidate = trim($timezone);
        $options = $this->timezoneOptions();

        if ($candidate !== '' && array_key_exists($candidate, $options)) {
            return $candidate;
        }

        return 'Asia/Jakarta';
    }

    private function timezoneLabel(string $timezone): string
    {
        $options = $this->timezoneOptions();

        return (string) ($options[$timezone] ?? 'WIB (UTC+07:00)');
    }

    private function resolveAppCurrentUserPayload(): ?array
    {
        try {
            $user = auth()->user();
            if (!$user instanceof User) {
                return null;
            }

            $roleName = strtolower(trim((string) ($user->getRoleNames()->first() ?? '')));
            $clientRole = $roleName === 'super-admin' ? 'admin' : $roleName;

            $permissions = [];
            try {
                $permissions = Cache::remember(
                    'app_current_user_permissions_v1:' . $user->id,
                    60,
                    fn () => $user->getAllPermissions()->pluck('name')->values()->all()
                );
            } catch (\Throwable $e) {
                $permissions = [];
            }

            $avatarUrl = null;
            try {
                if (!empty($user->avatar_path)) {
                    $avatarUrl = Storage::disk('public')->url($user->avatar_path);
                }
            } catch (\Throwable $e) {
                $avatarUrl = null;
            }

            $activeToken = AuthToken::resolveActiveForUser($user, $clientRole)->token;

            return [
                'name' => $user->name,
                'nama' => $user->name,
                'username' => $user->username,
                'nisn' => $user->username,
                'role' => $clientRole,
                'raw_role' => $roleName,
                'permissions' => $permissions,
                'kelas' => $user->kelas,
                'avatar_url' => $avatarUrl,
                'token' => $activeToken,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveSidebarIzinSakitPendingCount(): int
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return 0;
            }

            $canManage = $user->can('izin-sakit.manage');
            $canApprove = $canManage || $user->can('izin-sakit.approve');
            $canRequest = $canManage || $user->can('izin-sakit.request');

            if (!$canRequest && !$canApprove) {
                return 0;
            }

            $userId = (int) ($user->id ?? 0);
            $kelasWakel = trim((string) ($user->kelas ?? ''));
            $cacheKey = 'sidebar_izin_pending_v2:' . md5(json_encode([
                'user_id' => $userId,
                'kelas' => $kelasWakel,
                'can_manage' => $canManage,
                'can_approve' => $canApprove,
                'can_request' => $canRequest,
                'is_wakel' => $user->hasRole('wakel'),
            ]));

            return (int) Cache::remember($cacheKey, 60, function () use (
                $canApprove,
                $canManage,
                $canRequest,
                $kelasWakel,
                $user,
                $userId
            ): int {
                if (!$canRequest && !$canApprove) {
                    return 0;
                }

                $query = IzinSakitRequest::query()
                    ->where('status', IzinSakitRequest::STATUS_PENDING);

                if (!$canManage) {
                    if ($user->hasRole('wakel')) {
                        if ($kelasWakel !== '') {
                            $query->whereHas('siswa', fn ($siswaQuery) => $siswaQuery->where('kelas', $kelasWakel));
                        } elseif ($userId > 0) {
                            $query->where('requested_by_user_id', $userId);
                        } else {
                            return 0;
                        }
                    } elseif (!$canApprove && $userId > 0) {
                        $query->where('requested_by_user_id', $userId);
                    } elseif (!$canApprove) {
                        return 0;
                    }
                }

                return (int) $query->count();
            });
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
