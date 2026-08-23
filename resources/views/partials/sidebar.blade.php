@php
    $brandName = $appUiSettings['website_nama'] ?? 'NURIS';
    $brandSlogan = $appUiSettings['website_slogan'] ?? 'Nurul Hidayah Integrated System';
    $embeddedLogo = class_exists(\App\Helpers\PortalAssets::class) ? \App\Helpers\PortalAssets::getLogo() : asset('images/logo-smk.png');
    $brandLogoUrl = !empty($appUiSettings['website_logo_url']) ? $appUiSettings['website_logo_url'] : $embeddedLogo;
    $sidebarUser = auth()->user();
    $sidebarAvatarUrl = null;
    if ($sidebarUser && !empty($sidebarUser->avatar_path)) {
        try {
            $sidebarAvatarUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($sidebarUser->avatar_path);
        } catch (\Throwable $e) {
            $sidebarAvatarUrl = null;
        }
    }
    $sidebarIzinSakitPendingCount = (int) ($sidebarIzinSakitPendingCount ?? 0);
@endphp
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 text-white transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out flex flex-col h-full shadow-2xl border-r border-white/15" style="background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 35%, #1d4ed8 65%, #2563eb 100%);">   
  
  <div id="sidebarHeader" class="h-16 flex items-center justify-start px-5 border-b border-white/15 overflow-hidden relative transition-all duration-300 mb-2">
      <div class="absolute top-0 left-0 w-full h-full bg-white/5 pointer-events-none"></div>
      <div class="flex items-center space-x-3 relative z-10 w-full">
          <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center p-1 shadow-sm border border-white/20 shrink-0 transition-all duration-300">
              <img id="sidebarBrandLogoImg" src="{{ $embeddedLogo }}" onerror="this.src='{{ $embeddedLogo }}'; this.onerror=null;" alt="Logo NURIS" class="w-full h-full object-contain">
          </div>
          <div class="sidebar-label transition-opacity duration-300 whitespace-nowrap">
              <h1 id="sidebarBrandName" class="font-bold text-base tracking-wide text-white">{{ $brandName }}</h1>
              <p id="sidebarBrandSlogan" class="text-[9px] text-indigo-100 uppercase tracking-wider font-semibold">{{ $brandSlogan }}</p>
          </div>
      </div>
  </div>
     
          <nav id="sidebarMenu" class="flex-1 overflow-y-auto overflow-x-hidden px-4 pt-3 space-y-3 pb-5 scrollbar-hide text-sm">
              @auth
                  @can('dashboard.view')
                      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                          <i class="fas fa-home w-4 text-center"></i>
                          <span class="sidebar-label text-[15px] font-semibold">Dashboard</span>
                      </a>
                  @endcan

                  @if (auth()->user()?->can('siswa.view') || auth()->user()?->can('guru.view') || auth()->user()?->can('piket.view') || auth()->user()?->can('settings.users.manage'))
                      <details data-user-management {{ request()->routeIs('data-siswa') || request()->routeIs('data-guru') || request()->routeIs('data-piket') || request()->routeIs('role-permission.users.*') ? 'open' : '' }}>
                          <summary class="list-none flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-gray-300 hover:bg-white/10 hover:text-white cursor-pointer">
                              <i class="fas fa-users w-4 text-center"></i>
                              <span class="sidebar-label text-[15px] font-semibold flex-1">Kelola Member</span>
                              <i class="fas fa-chevron-right settings-chevron sidebar-label text-[11px] opacity-80"></i>
                          </summary>
                          <div class="mt-2 pl-2 space-y-2">
                              @can('siswa.view')
                                  <a href="{{ route('data-siswa') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('data-siswa') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-user-graduate w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Data Siswa</span>
                                  </a>
                              @endcan
                              @can('guru.view')
                                  <a href="{{ route('data-guru') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('data-guru') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-chalkboard-teacher w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Data Guru</span>
                                  </a>
                              @endcan
                              {{-- Data Piket disembunyikan sesuai permintaan --}}
                              @can('settings.users.manage')
                                  <a href="{{ route('role-permission.users.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('role-permission.users.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-user-cog w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Data User</span>
                                  </a>
                              @endcan
                          </div>
                      </details>
                  @endif

                  @if (auth()->user()?->can('kelas.manage') || auth()->user()?->can('kenaikan-kelas.manage') || auth()->user()?->can('alumni.view'))
                      <details data-akademik-menu {{ request()->routeIs('kelola-kelas') || request()->routeIs('kenaikan-kelas') || request()->routeIs('data-alumni') ? 'open' : '' }}>
                          <summary class="list-none flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-gray-300 hover:bg-white/10 hover:text-white cursor-pointer">
                              <i class="fas fa-school w-4 text-center"></i>
                              <span class="sidebar-label text-[15px] font-semibold flex-1">Akademik</span>
                              <i class="fas fa-chevron-right settings-chevron sidebar-label text-[11px] opacity-80"></i>
                          </summary>
                          <div class="mt-2 pl-2 space-y-2">
                              @can('kelas.manage')
                                  <a href="{{ route('kelola-kelas') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('kelola-kelas') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-school w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Kelola Kelas</span>
                                  </a>
                              @endcan
                              @can('kenaikan-kelas.manage')
                                  <a href="{{ route('kenaikan-kelas') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('kenaikan-kelas') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-level-up-alt w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Kenaikan Kelas</span>
                                  </a>
                              @endcan
                              @can('alumni.view')
                                  <a href="{{ route('data-alumni') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('data-alumni') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-user-check w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Data Alumni</span>
                                  </a>
                              @endcan
                          </div>
                      </details>
                  @endif

                  {{-- Menu Pembelajaran & Pelanggaran Siswa disembunyikan agar sidebar fokus pada 4 Pilar utama --}}
                  
                  @if (auth()->user()?->can('scanner.use') || auth()->user()?->can('absen.manage') || auth()->user()?->can('kartu-absensi.manage') || auth()->user()?->can('monitoring.view') || auth()->user()?->can('izin-sakit.request') || auth()->user()?->can('izin-sakit.approve') || auth()->user()?->can('izin-sakit.manage') || auth()->user()?->can('rekap-absensi.view') || auth()->user()?->can('rekap-absensi-pelajaran.view') || auth()->user()?->can('rekap-bulanan.view') || auth()->user()?->can('rekap-tahunan.view'))
                      <details data-absensi-menu {{ request()->routeIs('scanner') || request()->routeIs('kelola-absen') || request()->routeIs('kartu-absensi.*') || request()->routeIs('monitoring') || request()->routeIs('monitoring-guru') || request()->routeIs('izin-sakit.*') || request()->routeIs('rekap-absensi') || request()->routeIs('laporan-absensi-guru') || request()->routeIs('rekap-absensi-pelajaran') || request()->routeIs('rekap-bulanan') || request()->routeIs('rekap-tahunan') ? 'open' : '' }}>
                          <summary class="list-none flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-gray-300 hover:bg-white/10 hover:text-white cursor-pointer">
                              <i class="fas fa-fingerprint w-4 text-center"></i>
                              <span class="sidebar-label text-[15px] font-semibold flex-1">Absensi</span>
                              @if ($sidebarIzinSakitPendingCount > 0)
                                  <span class="sidebar-label inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-amber-300 text-amber-900 text-[10px] font-bold mr-1">
                                      {{ $sidebarIzinSakitPendingCount > 99 ? '99+' : $sidebarIzinSakitPendingCount }}
                                  </span>
                              @endif
                              <i class="fas fa-chevron-right settings-chevron sidebar-label text-[11px] opacity-80"></i>
                          </summary>
                          <div class="mt-2 pl-2 space-y-2">
                              @can('scanner.use')
                                  <a href="{{ route('scanner') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('scanner') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-qrcode w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Scan Absensi</span>
                                  </a>
                              @endcan
                              @can('absen.manage')
                                  <a href="{{ route('kelola-absen') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('kelola-absen') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-clock w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Jam & Jadwal Absen</span>
                                  </a>
                              @endcan
                              @can('kartu-absensi.manage')
                                  <a href="{{ route('kartu-absensi.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('kartu-absensi.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-id-card-alt w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Kartu Absensi</span>
                                  </a>
                              @endcan
                              @can('monitoring.view')
                                  <a href="{{ route('monitoring') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('monitoring') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-eye w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Monitoring Siswa</span>
                                  </a>
                              @endcan
                              @if (auth()->user()?->can('guru.view') || auth()->user()?->can('monitoring.view') || auth()->user()?->can('settings.users.manage'))
                                  <a href="{{ route('monitoring-guru') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('monitoring-guru') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-chalkboard-teacher w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Monitoring Guru</span>
                                  </a>
                              @endif
                              @if (auth()->user()?->can('izin-sakit.request') || auth()->user()?->can('izin-sakit.approve') || auth()->user()?->can('izin-sakit.manage'))
                                  <a href="{{ route('izin-sakit.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('izin-sakit.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-notes-medical w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium flex-1">Izin / Sakit</span>
                                      @if ($sidebarIzinSakitPendingCount > 0)
                                          <span class="sidebar-label inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-amber-300 text-amber-900 text-[10px] font-bold">
                                              {{ $sidebarIzinSakitPendingCount > 99 ? '99+' : $sidebarIzinSakitPendingCount }}
                                          </span>
                                      @endif
                                  </a>
                              @endif
                              @can('rekap-absensi.view')
                                  <a href="{{ route('rekap-absensi') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('rekap-absensi') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-clipboard-list w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Laporan Absensi Siswa</span>
                                  </a>
                              @endcan
                              @if (auth()->user()?->can('guru.view') || auth()->user()?->can('rekap-absensi.view') || auth()->user()?->can('settings.users.manage'))
                                  <a href="{{ route('laporan-absensi-guru') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('laporan-absensi-guru') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-file-invoice w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Laporan Absensi Guru</span>
                                  </a>
                              @endif
                              @can('rekap-absensi-pelajaran.view')
                                  <a href="{{ route('rekap-absensi-pelajaran') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('rekap-absensi-pelajaran') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-book-reader w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Absensi Pelajaran</span>
                                  </a>
                              @endcan
                              @can('rekap-bulanan.view')
                                  <a href="{{ route('rekap-bulanan') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('rekap-bulanan') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-calendar-alt w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Rekap Bulanan</span>
                                  </a>
                              @endcan
                              @can('rekap-tahunan.view')
                                  <a href="{{ route('rekap-tahunan') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('rekap-tahunan') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-calendar-week w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Rekap Tahunan</span>
                                  </a>
                              @endcan
                          </div>
                      </details>
                  @endif

                  <!-- KEUANGAN SEKOLAH -->
                  @if(!auth()->user()?->hasRole('siswa'))
                  <details data-keuangan-menu {{ request()->routeIs('keuangan.*') || request()->routeIs('tabungan-siswa.*') ? 'open' : '' }}>
                      <summary class="list-none flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-gray-300 hover:bg-white/10 hover:text-white cursor-pointer">
                          <i class="fas fa-coins w-4 text-center text-amber-300"></i>
                          <span class="sidebar-label text-[15px] font-semibold flex-1">Keuangan Sekolah</span>
                          <i class="fas fa-chevron-right settings-chevron sidebar-label text-[11px] opacity-80"></i>
                      </summary>
                      <div class="mt-2 pl-2 space-y-2">
                          <a href="{{ url('/keuangan/pembayaran') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('keuangan.pembayaran.*') ? 'bg-white/15 text-white font-bold' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                              <i class="fas fa-cash-register w-4 text-center"></i>
                              <span class="sidebar-label text-[14px] font-medium">
                                  @if(auth()->user()?->hasRole('wakel'))
                                      Tagihan Kelas
                                  @else
                                      Kasir Pembayaran
                                  @endif
                              </span>
                          </a>
                          @if(auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara', 'kepsek', 'wakel']))
                          <a href="{{ url('/keuangan/laporan') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('keuangan.laporan.*') ? 'bg-white/15 text-white font-bold' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                              <i class="fas fa-file-invoice-dollar w-4 text-center"></i>
                              <span class="sidebar-label text-[14px] font-medium">Laporan Keuangan</span>
                          </a>
                          @endif
                          @if(auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara', 'kepsek']))
                          <a href="{{ url('/keuangan/pos') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('keuangan.pos.*') ? 'bg-white/15 text-white font-bold' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                              <i class="fas fa-tags w-4 text-center"></i>
                              <span class="sidebar-label text-[14px] font-medium">Kategori Pos Keuangan</span>
                          </a>
                          @endif
                          @if(auth()->user()?->hasAnyRole(['super-admin', 'admin', 'bendahara', 'kepsek']))
                          <a href="{{ route('tabungan-siswa.rekening.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('tabungan-siswa.rekening.*') ? 'bg-white/15 text-white font-bold' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                              <i class="fas fa-wallet w-4 text-center"></i>
                              <span class="sidebar-label text-[14px] font-medium">Tabungan Siswa</span>
                          </a>
                          @endif
                      </div>
                  </details>
                  @endif
                  @can('notifications.send')
                      <a href="{{ route('notifications.send.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('notifications.send.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                          <i class="fas fa-paper-plane w-4 text-center"></i>
                          <span class="sidebar-label text-[15px] font-semibold">Kirim Notifikasi</span>
                      </a>
                  @endcan

                  @if (auth()->user()?->can('persuratan.view') || auth()->user()?->can('persuratan.manage'))
                      <a href="{{ route('persuratan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('persuratan.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                          <i class="fas fa-envelope-open-text w-4 text-center"></i>
                          <span class="sidebar-label text-[15px] font-semibold">Persuratan</span>
                      </a>
                  @endif

                  @if (auth()->user()?->hasRole('siswa') && auth()->user()?->can('kartu-siswa.view'))
                      <a href="{{ route('mata-pelajaran-saya') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('mata-pelajaran-saya') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                          <i class="fas fa-book w-4 text-center"></i>
                          <span class="sidebar-label text-[15px] font-semibold">Mata Pelajaran</span>
                      </a>
                      <a href="{{ url('/keuangan/pembayaran') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('keuangan.pembayaran*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                          <i class="fas fa-file-invoice w-4 text-center"></i>
                          <span class="sidebar-label text-[15px] font-semibold">Tagihan Saya</span>
                      </a>
                      <a href="{{ route('presensi-saya') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('presensi-saya') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                          <i class="fas fa-chart-pie w-4 text-center"></i>
                          <span class="sidebar-label text-[15px] font-semibold">Presensi Saya</span>
                      </a>
                      @can('poin-pelanggaran.self.view')
                          <a href="{{ route('pelanggaran-saya') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('pelanggaran-saya') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                              <i class="fas fa-triangle-exclamation w-4 text-center"></i>
                              <span class="sidebar-label text-[15px] font-semibold">Pelanggaran Saya</span>
                          </a>
                      @endcan
                      @can('tabungan-siswa.self.view')
                          <a href="{{ route('tabungan-saya') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('tabungan-saya') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                              <i class="fas fa-piggy-bank w-4 text-center"></i>
                              <span class="sidebar-label text-[15px] font-semibold">Tabungan Saya</span>
                          </a>
                      @endcan
                      <a href="{{ route('kartu-siswa') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('kartu-siswa') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                          <i class="fas fa-id-card w-4 text-center"></i>
                          <span class="sidebar-label text-[15px] font-semibold">Kartu Saya</span>
                      </a>
                  @endif



                  @if (auth()->user()?->can('settings.roles.manage') || auth()->user()?->can('settings.general.manage') || auth()->user()?->can('settings.devices.manage') || auth()->user()?->can('settings.notifications.manage') || auth()->user()?->can('settings.api.manage') || auth()->user()?->can('settings.backup.manage') || auth()->user()?->can('settings.update.manage'))
                      <details data-superadmin-settings {{ request()->routeIs('role-permission.index') || request()->routeIs('settings.general.*') || request()->routeIs('settings.devices.*') || request()->routeIs('settings.notifications.*') || request()->routeIs('settings.api-access.*') || request()->routeIs('settings.backup.*') || request()->routeIs('settings.update.*') ? 'open' : '' }}>
                          <summary class="list-none flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-gray-300 hover:bg-white/10 hover:text-white cursor-pointer">
                              <i class="fas fa-cog w-4 text-center"></i>
                              <span class="sidebar-label text-[15px] font-semibold flex-1">Pengaturan</span>
                              <i class="fas fa-chevron-right settings-chevron sidebar-label text-[11px] opacity-80"></i>
                          </summary>
                          <div class="mt-2 pl-2 space-y-2">
                              @can('settings.general.manage')
                                  <a href="{{ route('settings.general.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.general.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-sliders-h w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Umum</span>
                                  </a>
                              @endcan
                              @can('settings.devices.manage')
                                  <a href="{{ route('settings.devices.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.devices.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-microchip w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Devices</span>
                                  </a>
                              @endcan
                              @can('settings.notifications.manage')
                                  <a href="{{ route('settings.notifications.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.notifications.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fab fa-whatsapp w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Notifikasi</span>
                                  </a>
                              @endcan
                              @can('settings.api.manage')
                                  <a href="{{ route('settings.api-access.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.api-access.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-key w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">API Access</span>
                                  </a>
                              @endcan
                              @can('settings.backup.manage')
                                  <a href="{{ route('settings.backup.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.backup.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-database w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Backup & Restore</span>
                                  </a>
                              @endcan
                              @can('settings.update.manage')
                                  <a href="{{ route('settings.update.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.update.*') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-download w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Update</span>
                                  </a>
                              @endcan
                              @can('settings.roles.manage')
                                  <a href="{{ route('role-permission.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs('role-permission.index') ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                                      <i class="fas fa-user-shield w-4 text-center"></i>
                                      <span class="sidebar-label text-[14px] font-medium">Role & Permission</span>
                                  </a>
                              @endcan
                          </div>
                      </details>
                  @endif
              @endauth
          </nav>
          
          <div class="p-4 border-t border-white/15 bg-black/10">
              <div id="userProfileCard" class="flex items-center space-x-3 p-3 bg-black/20 rounded-xl border border-white/10 transition-all duration-300 overflow-hidden">
                  <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-cyan-500 to-blue-600 shadow-inner shrink-0 overflow-hidden relative">
                      <img id="navUserAvatarImg" src="{{ $sidebarAvatarUrl ?? '' }}" alt="Avatar" class="w-full h-full object-cover {{ !empty($sidebarAvatarUrl) ? '' : 'hidden' }}">
                      <div id="navUserInitial" class="absolute inset-0 flex items-center justify-center font-bold text-sm text-white {{ !empty($sidebarAvatarUrl) ? 'hidden' : '' }}">U</div>
                  </div>
                  <div id="userInfo" class="sidebar-label transition-opacity duration-300 whitespace-nowrap">
                      <p id="navUserName" class="font-semibold text-xs truncate text-white max-w-[120px]">User</p>
                      <span id="navUserRole" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-blue-800 text-blue-100 border border-blue-700 tracking-wider">Role</span>
                  </div>
              </div>
          </div>
          <div class="px-4 py-2 border-t border-white/10 bg-black/20 text-center sidebar-label">
              <p class="text-[10px] text-blue-200/70 leading-tight">Develop by <a href="https://kangdigital.web.id" target="_blank" class="text-blue-100 font-semibold hover:text-white transition-colors">Kang Digital</a></p>
              <p class="text-[9px] text-blue-300/50">kangdigital.web.id</p>
          </div>
          <script>
          document.addEventListener('DOMContentLoaded', function() {
              var sidebarDetails = document.querySelectorAll('#sidebarMenu details');
              sidebarDetails.forEach(function(detail) {
                  detail.addEventListener('toggle', function() {
                      if (detail.open) {
                          sidebarDetails.forEach(function(other) {
                              if (other !== detail && other.open) {
                                  other.removeAttribute('open');
                              }
                          });
                      }
                  });
              });
          });
          </script>
      </aside>
