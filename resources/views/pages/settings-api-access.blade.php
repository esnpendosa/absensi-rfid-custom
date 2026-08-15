@extends('layouts.page')

@section('title', 'API Access')

@section('content')
@php
    $totalTokens = (int) ($stats['total'] ?? $tokens->count());
    $activeTokens = (int) ($stats['active'] ?? 0);
    $inactiveTokens = (int) ($stats['inactive'] ?? 0);
    $expiredTokens = (int) ($stats['expired'] ?? 0);
    $apiBase = rtrim(url('/api/v1'), '/');
    $endpointDocs = [
        [
            'title' => 'Data Siswa',
            'path' => $apiBase . '/students/list',
            'scope' => 'students.read',
            'body' => "{\n  \"page\": 1,\n  \"per_page\": 50,\n  \"kelas\": \"\",\n  \"search\": \"\"\n}",
        ],
        [
            'title' => 'Detail Siswa',
            'path' => $apiBase . '/students/detail',
            'scope' => 'students.read',
            'body' => "{\n  \"nisn\": \"1234567890\"\n}",
        ],
        [
            'title' => 'Data Absensi',
            'path' => $apiBase . '/attendance/list',
            'scope' => 'attendance.read',
            'body' => "{\n  \"tanggal_mulai\": \"2026-08-01\",\n  \"tanggal_selesai\": \"2026-08-31\",\n  \"kelas\": \"\",\n  \"nisn\": \"\",\n  \"status\": \"\",\n  \"page\": 1,\n  \"per_page\": 50\n}",
        ],
        [
            'title' => 'Absensi Siswa',
            'path' => $apiBase . '/attendance/student',
            'scope' => 'attendance.read',
            'body' => "{\n  \"nisn\": \"1234567890\",\n  \"tanggal_mulai\": \"2026-08-01\",\n  \"tanggal_selesai\": \"2026-08-31\"\n}",
        ],
        [
            'title' => 'Absensi Kelas',
            'path' => $apiBase . '/attendance/class',
            'scope' => 'attendance.read',
            'body' => "{\n  \"kelas\": \"X IPA 1\",\n  \"tanggal_mulai\": \"2026-08-01\",\n  \"tanggal_selesai\": \"2026-08-31\"\n}",
        ],
        [
            'title' => 'Absensi Semua',
            'path' => $apiBase . '/attendance/all',
            'scope' => 'attendance.read',
            'body' => "{\n  \"tanggal_mulai\": \"2026-08-01\",\n  \"tanggal_selesai\": \"2026-08-31\",\n  \"page\": 1,\n  \"per_page\": 50\n}",
        ],
        [
            'title' => 'Ringkasan Absensi',
            'path' => $apiBase . '/attendance/summary',
            'scope' => 'attendance.summary',
            'body' => "{\n  \"tanggal_mulai\": \"2026-08-01\",\n  \"tanggal_selesai\": \"2026-08-31\",\n  \"kelas\": \"\"\n}",
        ],
    ];
    $tokenEditorPayload = $tokens
        ->mapWithKeys(fn ($token) => [
            (string) $token->id => [
                'id' => (int) $token->id,
                'name' => (string) $token->name,
                'scopes' => array_values($token->scopes ?? []),
                'expires_at' => $token->expires_at?->format('Y-m-d'),
            ],
        ])
        ->all();
@endphp

<div class="view-section active animate-fade-in space-y-4">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Total Token</div>
            <div id="apiTokenStatTotal" class="mt-2 text-2xl font-bold text-slate-800">{{ $totalTokens }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">Aktif</div>
            <div id="apiTokenStatActive" class="mt-2 text-2xl font-bold text-emerald-800">{{ $activeTokens }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-slate-700">Nonaktif</div>
            <div id="apiTokenStatInactive" class="mt-2 text-2xl font-bold text-slate-800">{{ $inactiveTokens }}</div>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-4 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wide text-rose-700">Expired</div>
            <div id="apiTokenStatExpired" class="mt-2 text-2xl font-bold text-rose-800">{{ $expiredTokens }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/30 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h3 class="font-bold text-sm text-gray-800">API Access</h3>
                <p class="text-xs text-gray-500 mt-1">Buat token untuk menghubungkan data siswa dan absensi ke aplikasi lain.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <button type="button" onclick="openApiDocsModal()" class="inline-flex items-center justify-center gap-2 h-9 px-4 rounded-lg border border-gray-200 bg-white text-gray-700 font-bold text-xs hover:bg-gray-50 hover:text-indigo-700 transition">
                    <i class="fas fa-book text-[11px]"></i>
                    Dokumentasi API
                </button>
                <button type="button" onclick="openApiTokenModal()" class="inline-flex items-center justify-center gap-2 h-9 px-4 rounded-lg bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition">
                    <i class="fas fa-key text-[11px]"></i>
                    Buat Token
                </button>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left border-collapse min-w-[980px]">
                    <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-semibold border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-center w-12">No</th>
                            <th class="p-3">Nama Token</th>
                            <th class="p-3">Scope</th>
                            <th class="p-3">Expired</th>
                            <th class="p-3">Last Used</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="apiTokenTableBody" class="divide-y divide-gray-50 bg-white text-xs text-gray-700">
                        @forelse ($tokens as $index => $token)
                            @php
                                $scopeLabels = collect($token->scopes ?? [])
                                    ->map(fn (string $scope) => $scopes[$scope] ?? $scope)
                                    ->values();
                                $isExpired = $token->isExpired();
                                $isUsable = $token->is_active && !$isExpired;
                                $statusClass = !$token->is_active
                                    ? 'bg-slate-100 text-slate-700 border-slate-200'
                                    : ($isExpired ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');
                                $statusLabel = !$token->is_active ? 'Nonaktif' : ($isExpired ? 'Expired' : 'Aktif');
                                $toggleNextActive = $token->is_active ? '0' : '1';
                                $toggleIcon = $token->is_active ? 'fa-power-off' : 'fa-circle-check';
                                $toggleTitle = $token->is_active ? 'Nonaktifkan' : 'Aktifkan';
                                $toggleClass = $token->is_active
                                    ? 'inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition'
                                    : 'inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition';
                            @endphp
                            <tr class="hover:bg-gray-50" data-token-row-id="{{ $token->id }}">
                                <td class="p-3 text-center text-gray-500" data-cell="number">{{ $index + 1 }}</td>
                                <td class="p-3">
                                    <div class="font-semibold text-gray-800">{{ $token->name }}</div>
                                    <div class="text-[11px] text-gray-500">Dibuat {{ $token->created_at?->format('d M Y H:i') ?? '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($scopeLabels as $label)
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 text-[11px] font-semibold">{{ $label }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-3">{{ $token->expires_at?->format('d M Y H:i') ?? 'Tidak dibatasi' }}</td>
                                <td class="p-3">{{ $token->last_used_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg border text-[11px] font-bold uppercase tracking-wide {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition js-api-token-edit" title="Edit" data-token-id="{{ $token->id }}">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition js-api-token-regenerate" title="Generate Ulang" data-token-id="{{ $token->id }}" data-token-name="{{ $token->name }}">
                                            <i class="fas fa-rotate text-xs"></i>
                                        </button>
                                        <button type="button" class="{{ $toggleClass }} js-api-token-toggle" title="{{ $toggleTitle }}" data-token-id="{{ $token->id }}" data-token-name="{{ $token->name }}" data-next-active="{{ $toggleNextActive }}">
                                            <i class="fas {{ $toggleIcon }} text-xs"></i>
                                        </button>
                                        <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition js-api-token-delete" title="Hapus" data-token-id="{{ $token->id }}" data-token-name="{{ $token->name }}">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="apiTokenEmptyRow">
                                <td colspan="7" class="p-10 text-center text-gray-400">
                                    Belum ada token API.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div id="apiDocsModal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeApiDocsModal()"></div>
    <div class="relative w-full max-w-6xl h-[92dvh]">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full h-full flex flex-col">
            <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Dokumentasi API</h3>
                    <p class="text-xs text-gray-500 mt-1">Semua endpoint integrasi memakai method POST dan token Bearer.</p>
                </div>
                <button type="button" onclick="closeApiDocsModal()" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" title="Tutup">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto p-4 space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-[260px,1fr] gap-4">
                    <aside class="rounded-xl border border-gray-200 bg-slate-50 p-2 h-fit lg:sticky lg:top-0">
                        <div class="px-2 py-2 text-[10px] font-bold uppercase tracking-wide text-slate-500">Endpoint</div>
                        <div class="space-y-1.5">
                            @foreach ($endpointDocs as $doc)
                                <button type="button" data-doc-index="{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="js-api-doc-tab w-full text-left px-3 py-2 rounded-lg border text-xs font-bold transition {{ $loop->first ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-gray-200 hover:bg-gray-50 hover:text-indigo-700' }}">
                                    <span class="block">{{ $doc['title'] }}</span>
                                    <span class="block mt-1 text-[10px] font-semibold opacity-80">{{ $doc['scope'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </aside>

                    <div class="space-y-4 min-w-0">
                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Base URL</div>
                                    <code id="apiDocsBaseUrl" class="block mt-1 text-xs text-slate-800 break-all">{{ $apiBase }}</code>
                                </div>
                                <button type="button" class="js-api-doc-copy inline-flex items-center justify-center gap-2 h-8 px-3 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold transition" data-copy-kind="target" data-copy-target="#apiDocsBaseUrl">
                                    <i class="fas fa-copy text-[11px]"></i>
                                    Salin
                                </button>
                            </div>
                            <div class="p-4 grid grid-cols-1 xl:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-gray-200 bg-slate-50/60 overflow-hidden">
                                    <div class="px-3 py-2 border-b border-gray-100 bg-white flex items-center justify-between gap-2">
                                        <div class="font-bold text-xs text-slate-800">Header Wajib</div>
                                        <button type="button" class="js-api-doc-copy inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" title="Salin header" data-copy-kind="target" data-copy-target="#apiDocsHeadersCode">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    </div>
                                    <pre class="m-0 p-3 text-[11px] leading-5 text-slate-700 overflow-x-auto"><code id="apiDocsHeadersCode">Authorization: Bearer TOKEN_API
Accept: application/json
Content-Type: application/json</code></pre>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-slate-50/60 overflow-hidden">
                                    <div class="px-3 py-2 border-b border-gray-100 bg-white flex items-center justify-between gap-2">
                                        <div class="font-bold text-xs text-slate-800">Response Umum</div>
                                        <button type="button" class="js-api-doc-copy inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" title="Salin response" data-copy-kind="target" data-copy-target="#apiDocsResponseCode">
                                            <i class="fas fa-copy text-xs"></i>
                                        </button>
                                    </div>
                                    <pre class="m-0 p-3 text-[11px] leading-5 text-slate-700 overflow-x-auto"><code id="apiDocsResponseCode">{
  "success": true,
  "message": "...",
  "rc": 200,
  "data": {}
}</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                            @foreach ($endpointDocs as $doc)
                                <section class="js-api-doc-panel {{ $loop->first ? '' : 'hidden' }}" data-doc-index="{{ $loop->index }}">
                                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/40 flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-bold text-base text-slate-800">{{ $doc['title'] }}</h4>
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 border border-emerald-100 text-[10px] font-bold text-emerald-700">POST</span>
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 border border-indigo-100 text-[10px] font-bold text-indigo-700">{{ $doc['scope'] }}</span>
                                            </div>
                                            <code data-doc-url class="block mt-2 text-xs text-slate-700 break-all">{{ $doc['path'] }}</code>
                                        </div>
                                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                                            <button type="button" class="js-api-doc-copy inline-flex items-center justify-center gap-2 h-8 px-3 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold transition" data-copy-kind="url" data-doc-index="{{ $loop->index }}">
                                                <i class="fas fa-link text-[11px]"></i>
                                                URL
                                            </button>
                                            <button type="button" class="js-api-doc-copy inline-flex items-center justify-center gap-2 h-8 px-3 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold transition" data-copy-kind="body" data-doc-index="{{ $loop->index }}">
                                                <i class="fas fa-code text-[11px]"></i>
                                                Body
                                            </button>
                                            <button type="button" class="js-api-doc-copy inline-flex items-center justify-center gap-2 h-8 px-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-xs font-bold transition" data-copy-kind="curl" data-doc-index="{{ $loop->index }}">
                                                <i class="fas fa-terminal text-[11px]"></i>
                                                cURL
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-4 grid grid-cols-1 xl:grid-cols-2 gap-3">
                                        <div class="rounded-xl border border-gray-200 bg-slate-50/60 overflow-hidden">
                                            <div class="px-3 py-2 border-b border-gray-100 bg-white font-bold text-xs text-slate-800">Body JSON</div>
                                            <pre class="m-0 p-3 text-[11px] leading-5 text-slate-700 overflow-x-auto"><code data-doc-body>{{ $doc['body'] }}</code></pre>
                                        </div>
                                        <div class="rounded-xl border border-gray-200 bg-slate-50/60 overflow-hidden">
                                            <div class="px-3 py-2 border-b border-gray-100 bg-white font-bold text-xs text-slate-800">Error Yang Umum</div>
                                            <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                                <div class="rounded-lg border border-gray-200 bg-white p-2">
                                                    <div class="font-bold text-slate-800">401</div>
                                                    <div class="text-slate-500 mt-1">Token kosong, salah, nonaktif, atau expired.</div>
                                                </div>
                                                <div class="rounded-lg border border-gray-200 bg-white p-2">
                                                    <div class="font-bold text-slate-800">403</div>
                                                    <div class="text-slate-500 mt-1">Scope token tidak cukup.</div>
                                                </div>
                                                <div class="rounded-lg border border-gray-200 bg-white p-2">
                                                    <div class="font-bold text-slate-800">422</div>
                                                    <div class="text-slate-500 mt-1">Body request tidak valid.</div>
                                                </div>
                                                <div class="rounded-lg border border-gray-200 bg-white p-2">
                                                    <div class="font-bold text-slate-800">429</div>
                                                    <div class="text-slate-500 mt-1">Request terlalu banyak.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="apiTokenModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/55" onclick="closeApiTokenModal()"></div>
    <div class="relative w-full max-w-xl">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full">
            <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 id="apiTokenModalTitle" class="text-lg font-bold text-gray-800">Buat Token API</h3>
                <button type="button" onclick="closeApiTokenModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="px-5 pt-3 pb-5">
                <form id="apiTokenForm" method="POST" action="{{ route('settings.api-access.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" id="apiTokenEditId" value="">
                    <div>
                        <label class="block mb-1 text-xs font-bold text-gray-500 uppercase tracking-wide">Nama Token</label>
                        <input id="apiTokenName" name="name" placeholder="Contoh: Integrasi Dashboard Pusat" required class="w-full bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                    </div>

                    <div>
                        <label class="block mb-2 text-xs font-bold text-gray-500 uppercase tracking-wide">Scope</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($scopes as $scope => $label)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="scopes[]" value="{{ $scope }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $loop->first ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-bold text-gray-500 uppercase tracking-wide">Expired</label>
                        <input type="date" name="expires_at" class="w-full bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="closeApiTokenModal()" class="inline-flex items-center justify-center gap-2 h-9 px-4 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold text-xs hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-200 transition">
                            <i class="fas fa-times text-[10px]"></i>
                            Batal
                        </button>
                        <button id="apiTokenSubmitButton" type="submit" class="inline-flex items-center justify-center gap-2 h-9 px-4 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold text-xs shadow-sm hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition">
                            <i class="fas fa-save text-[10px]"></i>
                            <span id="apiTokenSubmitText">Simpan Token</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const routes = window.APP_ROUTES || {};
        const tokenEditorData = @json($tokenEditorPayload);
        const scopeLabels = @json($scopes);
        const apiEndpointDocs = @json($endpointDocs);

        function csrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? String(meta.getAttribute('content') || '') : '';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function extractErrorMessage(payload, fallback) {
            if (!payload || typeof payload !== 'object') {
                return fallback;
            }

            if (typeof payload.message === 'string' && payload.message.trim() !== '') {
                return payload.message;
            }

            const errors = payload.errors || (payload.data && payload.data.errors);
            if (errors && typeof errors === 'object') {
                const firstKey = Object.keys(errors)[0];
                const firstValue = firstKey ? errors[firstKey] : null;
                if (Array.isArray(firstValue) && firstValue.length > 0) {
                    return String(firstValue[0]);
                }
                if (typeof firstValue === 'string' && firstValue.trim() !== '') {
                    return firstValue;
                }
            }

            return fallback;
        }

        async function sendAjax(url, options = {}) {
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            };
            let body = options.body || null;

            if (Object.prototype.hasOwnProperty.call(options, 'json')) {
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify(options.json || {});
            }

            const response = await fetch(url, {
                method: options.method || 'POST',
                headers,
                body,
                credentials: 'same-origin',
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok || payload.success === false) {
                throw new Error(extractErrorMessage(payload, 'Gagal memproses permintaan.'));
            }

            return payload;
        }

        async function copyText(text) {
            const value = String(text || '');
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(value);
                return;
            }

            const textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();
        }

        async function confirmAction(config) {
            if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') {
                return window.confirm(config.text || 'Lanjutkan proses ini?');
            }

            const result = await Swal.fire({
                title: config.title || 'Konfirmasi',
                text: config.text || 'Lanjutkan proses ini?',
                icon: config.icon || 'question',
                showCancelButton: true,
                confirmButtonColor: config.confirmButtonColor || '#4F46E5',
                cancelButtonColor: '#6B7280',
                confirmButtonText: config.confirmButtonText || 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            });

            return !!result.isConfirmed;
        }

        async function showGeneratedToken(token, title = 'Token API Dibuat') {
            const value = String(token || '');
            if (typeof Swal === 'undefined' || typeof Swal.fire !== 'function') {
                window.prompt('Salin token API sekarang.', value);
                return;
            }

            await Swal.fire({
                title,
                html: `
                    <div class="text-left space-y-3">
                        <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 text-xs font-semibold">
                            Token hanya ditampilkan sekali.
                        </div>
                        <div class="flex items-stretch gap-2">
                            <input id="generatedApiToken" class="h-11 min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 font-mono text-xs text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" readonly value="${escapeHtml(value)}">
                            <button type="button" id="copyGeneratedApiToken" class="h-11 w-11 shrink-0 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition inline-flex items-center justify-center" title="Salin token" aria-label="Salin token">
                                <i class="fas fa-copy text-xs"></i>
                            </button>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Selesai',
                confirmButtonColor: '#4F46E5',
                didOpen: () => {
                    const copyButton = document.getElementById('copyGeneratedApiToken');
                    if (copyButton) {
                        let copyResetTimer = null;
                        copyButton.addEventListener('click', async () => {
                            await copyText(value);
                            copyButton.innerHTML = '<i class="fas fa-check text-xs"></i>';
                            copyButton.setAttribute('title', 'Token disalin');
                            copyButton.setAttribute('aria-label', 'Token disalin');
                            window.clearTimeout(copyResetTimer);
                            copyResetTimer = window.setTimeout(() => {
                                copyButton.innerHTML = '<i class="fas fa-copy text-xs"></i>';
                                copyButton.setAttribute('title', 'Salin token');
                                copyButton.setAttribute('aria-label', 'Salin token');
                            }, 1200);
                        });
                    }
                },
            });
        }

        function copyTargetText(selector) {
            const target = document.querySelector(String(selector || ''));

            return target ? String(target.textContent || '').trim() : '';
        }

        function compactJsonBody(body) {
            const value = String(body || '').trim();
            if (value === '') {
                return '{}';
            }

            try {
                return JSON.stringify(JSON.parse(value));
            } catch (error) {
                return value.replace(/\s+/g, ' ');
            }
        }

        function buildCurlExample(doc) {
            const path = String((doc && doc.path) || '');
            const body = compactJsonBody((doc && doc.body) || '{}').replace(/'/g, "'\"'\"'");

            return [
                `curl -X POST "${path}"`,
                '-H "Authorization: Bearer TOKEN_API"',
                '-H "Accept: application/json"',
                '-H "Content-Type: application/json"',
                `-d '${body}'`,
            ].join(' ');
        }

        async function copyApiDoc(button) {
            const kind = String(button.dataset.copyKind || '');
            let text = '';
            let message = 'Teks dokumentasi disalin.';

            if (kind === 'target') {
                text = copyTargetText(button.dataset.copyTarget || '');
            } else {
                const index = Number.parseInt(String(button.dataset.docIndex || '0'), 10);
                const doc = apiEndpointDocs[index] || null;

                if (kind === 'url') {
                    text = String((doc && doc.path) || '');
                    message = 'URL endpoint disalin.';
                } else if (kind === 'body') {
                    text = String((doc && doc.body) || '');
                    message = 'Body JSON disalin.';
                } else if (kind === 'curl') {
                    text = buildCurlExample(doc);
                    message = 'Contoh cURL disalin.';
                }
            }

            if (text.trim() === '') {
                return;
            }

            await copyText(text);
            if (window.showAlert) {
                window.showAlert('success', message);
            }
        }

        function setActiveApiDoc(index) {
            const selectedIndex = String(index);

            document.querySelectorAll('.js-api-doc-tab').forEach((button) => {
                const isActive = String(button.dataset.docIndex || '') === selectedIndex;
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                button.classList.toggle('bg-indigo-600', isActive);
                button.classList.toggle('text-white', isActive);
                button.classList.toggle('border-indigo-600', isActive);
                button.classList.toggle('bg-white', !isActive);
                button.classList.toggle('text-slate-700', !isActive);
                button.classList.toggle('border-gray-200', !isActive);
                button.classList.toggle('hover:bg-gray-50', !isActive);
                button.classList.toggle('hover:text-indigo-700', !isActive);
            });

            document.querySelectorAll('.js-api-doc-panel').forEach((panel) => {
                panel.classList.toggle('hidden', String(panel.dataset.docIndex || '') !== selectedIndex);
            });
        }

        function updateStats(stats) {
            if (!stats || typeof stats !== 'object') return;

            const mappings = {
                apiTokenStatTotal: stats.total,
                apiTokenStatActive: stats.active,
                apiTokenStatInactive: stats.inactive,
                apiTokenStatExpired: stats.expired,
            };

            Object.entries(mappings).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = String(value ?? 0);
                }
            });
        }

        function statusBadgeClass(record) {
            if (!record || !record.is_active) {
                return 'bg-slate-100 text-slate-700 border-slate-200';
            }

            if (record.is_expired) {
                return 'bg-rose-50 text-rose-700 border-rose-200';
            }

            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }

        function tokenStatusLabel(record) {
            if (!record || typeof record.status_label !== 'string' || record.status_label.trim() === '') {
                return record && record.is_active ? 'Aktif' : 'Nonaktif';
            }

            return record.status_label;
        }

        function scopeBadges(record) {
            const scopes = Array.isArray(record.scopes) ? record.scopes : [];
            const labels = Array.isArray(record.scope_labels) && record.scope_labels.length > 0
                ? record.scope_labels
                : scopes.map((scope) => scopeLabels[scope] || scope);

            return labels.map((label) => `
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 text-[11px] font-semibold">${escapeHtml(label)}</span>
            `).join('');
        }

        function toggleButtonConfig(record) {
            const isActive = !!(record && record.is_active);

            return {
                nextActive: isActive ? '0' : '1',
                icon: isActive ? 'fa-power-off' : 'fa-circle-check',
                title: isActive ? 'Nonaktifkan' : 'Aktifkan',
                buttonClass: isActive
                    ? 'inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition js-api-token-toggle'
                    : 'inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition js-api-token-toggle',
            };
        }

        function buildTokenRow(record) {
            const config = toggleButtonConfig(record);
            const tokenId = String(record.id || '');
            const tokenName = String(record.name || '');

            return `
                <tr class="hover:bg-gray-50" data-token-row-id="${escapeHtml(tokenId)}">
                    <td class="p-3 text-center text-gray-500" data-cell="number"></td>
                    <td class="p-3">
                        <div class="font-semibold text-gray-800">${escapeHtml(record.name || '-')}</div>
                        <div class="text-[11px] text-gray-500">Dibuat ${escapeHtml(record.created_at || '-')}</div>
                    </td>
                    <td class="p-3">
                        <div class="flex flex-wrap gap-1.5">${scopeBadges(record)}</div>
                    </td>
                    <td class="p-3">${escapeHtml(record.expires_at || 'Tidak dibatasi')}</td>
                    <td class="p-3">${escapeHtml(record.last_used_at || '-')}</td>
                    <td class="p-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg border text-[11px] font-bold uppercase tracking-wide ${statusBadgeClass(record)}">
                            ${escapeHtml(tokenStatusLabel(record))}
                        </span>
                    </td>
                    <td class="p-3">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition js-api-token-edit" title="Edit" data-token-id="${escapeHtml(tokenId)}">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition js-api-token-regenerate" title="Generate Ulang" data-token-id="${escapeHtml(tokenId)}" data-token-name="${escapeHtml(tokenName)}">
                                <i class="fas fa-rotate text-xs"></i>
                            </button>
                            <button type="button" class="${config.buttonClass}" title="${escapeHtml(config.title)}" data-token-id="${escapeHtml(tokenId)}" data-token-name="${escapeHtml(tokenName)}" data-next-active="${escapeHtml(config.nextActive)}">
                                <i class="fas ${config.icon} text-xs"></i>
                            </button>
                            <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition js-api-token-delete" title="Hapus" data-token-id="${escapeHtml(tokenId)}" data-token-name="${escapeHtml(tokenName)}">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        function tokenRowFromHtml(html) {
            const template = document.createElement('template');
            template.innerHTML = String(html || '').trim();

            return template.content.firstElementChild;
        }

        function syncTokenEditorData(record) {
            if (!record || !record.id) return;

            tokenEditorData[String(record.id)] = {
                id: Number(record.id),
                name: String(record.name || ''),
                scopes: Array.isArray(record.scopes) ? record.scopes : [],
                expires_at: String(record.expires_at_date || ''),
            };
        }

        function refreshRowNumbers() {
            document.querySelectorAll('#apiTokenTableBody tr[data-token-row-id]').forEach((row, index) => {
                const numberCell = row.querySelector('[data-cell="number"]');
                if (numberCell) {
                    numberCell.textContent = String(index + 1);
                }
            });
        }

        function renderEmptyRow() {
            const tableBody = document.getElementById('apiTokenTableBody');
            if (!tableBody || tableBody.querySelector('tr[data-token-row-id]') || document.getElementById('apiTokenEmptyRow')) {
                return;
            }

            const row = document.createElement('tr');
            row.id = 'apiTokenEmptyRow';
            row.innerHTML = '<td colspan="7" class="p-10 text-center text-gray-400">Belum ada token API.</td>';
            tableBody.appendChild(row);
        }

        function upsertTokenRow(record, options = {}) {
            if (!record || !record.id) return;

            const tableBody = document.getElementById('apiTokenTableBody');
            if (!tableBody) return;

            const emptyRow = document.getElementById('apiTokenEmptyRow');
            if (emptyRow) {
                emptyRow.remove();
            }

            const row = tokenRowFromHtml(buildTokenRow(record));
            const existingRow = tableBody.querySelector(`tr[data-token-row-id="${String(record.id)}"]`);

            if (existingRow) {
                existingRow.replaceWith(row);
            } else if (options.prepend) {
                tableBody.prepend(row);
            } else {
                tableBody.appendChild(row);
            }

            syncTokenEditorData(record);
            refreshRowNumbers();
            row.classList.add('bg-indigo-50');
            setTimeout(() => row.classList.remove('bg-indigo-50'), 900);
        }

        function removeTokenRow(tokenId) {
            const tableBody = document.getElementById('apiTokenTableBody');
            if (!tableBody) return;

            const row = tableBody.querySelector(`tr[data-token-row-id="${String(tokenId)}"]`);
            if (row) {
                row.remove();
            }

            delete tokenEditorData[String(tokenId)];
            refreshRowNumbers();
            renderEmptyRow();
        }

        function setSubmitState(isBusy) {
            const form = document.getElementById('apiTokenForm');
            const button = document.getElementById('apiTokenSubmitButton');
            const text = document.getElementById('apiTokenSubmitText');
            const isEdit = form && form.dataset.mode === 'edit';

            if (button) {
                button.disabled = isBusy;
            }
            if (text) {
                text.textContent = isBusy
                    ? (isEdit ? 'Memperbarui...' : 'Menyimpan...')
                    : (isEdit ? 'Perbarui Token' : 'Simpan Token');
            }
        }

        window.openApiTokenModal = function (tokenId = null) {
            const modal = document.getElementById('apiTokenModal');
            const form = document.getElementById('apiTokenForm');
            const nameInput = document.getElementById('apiTokenName');
            const title = document.getElementById('apiTokenModalTitle');
            const editIdInput = document.getElementById('apiTokenEditId');
            if (!modal || !form) return;

            form.reset();
            const id = tokenId === null || typeof tokenId === 'undefined' ? '' : String(tokenId);
            const tokenData = id !== '' ? tokenEditorData[id] : null;
            const isEdit = !!tokenData;

            form.dataset.mode = isEdit ? 'edit' : 'create';
            if (editIdInput) {
                editIdInput.value = isEdit ? id : '';
            }
            if (title) {
                title.textContent = isEdit ? 'Edit Token API' : 'Buat Token API';
            }

            if (isEdit) {
                nameInput.value = tokenData.name || '';
                const expiresInput = form.querySelector('input[name="expires_at"]');
                if (expiresInput) {
                    expiresInput.value = tokenData.expires_at || '';
                }
                const activeScopes = Array.isArray(tokenData.scopes) ? tokenData.scopes : [];
                form.querySelectorAll('input[name="scopes[]"]').forEach((checkbox) => {
                    checkbox.checked = activeScopes.includes(checkbox.value);
                });
            } else {
                const firstScope = form.querySelector('input[name="scopes[]"]');
                if (firstScope) {
                    firstScope.checked = true;
                }
            }

            setSubmitState(false);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(() => nameInput?.focus(), 30);
        };

        window.closeApiTokenModal = function () {
            const modal = document.getElementById('apiTokenModal');
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        window.openApiDocsModal = function () {
            const modal = document.getElementById('apiDocsModal');
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setActiveApiDoc(0);
        };

        window.closeApiDocsModal = function () {
            const modal = document.getElementById('apiDocsModal');
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('apiTokenForm');
            if (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    setSubmitState(true);

                    try {
                        const mode = form.dataset.mode === 'edit' ? 'edit' : 'create';
                        const formData = new FormData(form);
                        let url = routes.settingsApiAccessStore || form.action;

                        if (mode === 'edit') {
                            const editId = String(document.getElementById('apiTokenEditId')?.value || '');
                            formData.append('_method', 'PUT');
                            url = String(routes.settingsApiAccessUpdate || '').replace('__ID__', editId);
                        }

                        const payload = await sendAjax(url, {
                            method: 'POST',
                            body: formData,
                        });
                        const record = payload.data && payload.data.record ? payload.data.record : null;
                        if (record) {
                            upsertTokenRow(record, { prepend: mode === 'create' });
                        }
                        updateStats(payload.data && payload.data.stats ? payload.data.stats : null);
                        window.closeApiTokenModal();
                        if (mode === 'create') {
                            await showGeneratedToken(payload.data && payload.data.plain_token ? payload.data.plain_token : '');
                        } else if (window.showAlert) {
                            window.showAlert('success', payload.message || 'Token API berhasil diperbarui.');
                        }
                    } catch (error) {
                        if (window.showAlert) {
                            window.showAlert('error', error.message || 'Gagal memproses token API.');
                        }
                    } finally {
                        setSubmitState(false);
                    }
                });
            }

            const tableBody = document.getElementById('apiTokenTableBody');
            if (tableBody) {
                tableBody.addEventListener('click', async function (event) {
                    const button = event.target instanceof Element
                        ? event.target.closest('.js-api-token-edit, .js-api-token-regenerate, .js-api-token-toggle, .js-api-token-delete')
                        : null;

                    if (!(button instanceof HTMLButtonElement)) {
                        return;
                    }

                    if (button.classList.contains('js-api-token-edit')) {
                        window.openApiTokenModal(button.dataset.tokenId || '');
                        return;
                    }

                    const tokenId = String(button.dataset.tokenId || '');
                    const tokenName = String(button.dataset.tokenName || 'token');

                    if (button.classList.contains('js-api-token-regenerate')) {
                        const isConfirmed = await confirmAction({
                            title: 'Generate ulang token?',
                            text: `Token lama untuk ${tokenName} akan langsung tidak berlaku.`,
                            icon: 'warning',
                            confirmButtonText: 'Ya, Generate',
                            confirmButtonColor: '#D97706',
                        });

                        if (!isConfirmed) return;

                        button.disabled = true;
                        try {
                            const url = String(routes.settingsApiAccessRegenerate || '').replace('__ID__', tokenId);
                            const payload = await sendAjax(url, { method: 'POST' });
                            if (payload.data && payload.data.record) {
                                upsertTokenRow(payload.data.record);
                            } else {
                                button.disabled = false;
                            }
                            updateStats(payload.data && payload.data.stats ? payload.data.stats : null);
                            await showGeneratedToken(payload.data && payload.data.plain_token ? payload.data.plain_token : '', 'Token API Dibuat Ulang');
                        } catch (error) {
                            button.disabled = false;
                            if (window.showAlert) {
                                window.showAlert('error', error.message || 'Gagal generate ulang token API.');
                            }
                        }
                        return;
                    }

                    if (button.classList.contains('js-api-token-delete')) {
                        const isConfirmed = await confirmAction({
                            title: 'Hapus token?',
                            text: `Hapus permanen ${tokenName}?`,
                            icon: 'warning',
                            confirmButtonText: 'Ya, Hapus',
                            confirmButtonColor: '#DC2626',
                        });

                        if (!isConfirmed) return;

                        button.disabled = true;
                        try {
                            const url = String(routes.settingsApiAccessDestroy || '').replace('__ID__', tokenId);
                            const payload = await sendAjax(url, { method: 'DELETE' });
                            removeTokenRow(payload.data && payload.data.deleted_id ? payload.data.deleted_id : tokenId);
                            updateStats(payload.data && payload.data.stats ? payload.data.stats : null);
                            if (window.showAlert) {
                                window.showAlert('success', payload.message || 'Token API berhasil dihapus.');
                            }
                        } catch (error) {
                            button.disabled = false;
                            if (window.showAlert) {
                                window.showAlert('error', error.message || 'Gagal menghapus token API.');
                            }
                        }
                        return;
                    }

                    const nextActive = String(button.dataset.nextActive || '0') === '1';
                    const isConfirmed = await confirmAction({
                        title: nextActive ? 'Aktifkan token?' : 'Nonaktifkan token?',
                        text: `${nextActive ? 'Aktifkan' : 'Nonaktifkan'} ${tokenName}?`,
                        icon: nextActive ? 'question' : 'warning',
                        confirmButtonText: nextActive ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan',
                        confirmButtonColor: nextActive ? '#059669' : '#475569',
                    });

                    if (!isConfirmed) return;

                    button.disabled = true;
                    try {
                        const url = String(routes.settingsApiAccessToggle || '').replace('__ID__', tokenId);
                        const payload = await sendAjax(url, {
                            method: 'PATCH',
                            json: { is_active: nextActive ? 1 : 0 },
                        });
                        if (payload.data && payload.data.record) {
                            upsertTokenRow(payload.data.record);
                        } else {
                            button.disabled = false;
                        }
                        updateStats(payload.data && payload.data.stats ? payload.data.stats : null);
                        if (window.showAlert) {
                            window.showAlert('success', payload.message || 'Status token API berhasil diubah.');
                        }
                    } catch (error) {
                        button.disabled = false;
                        if (window.showAlert) {
                            window.showAlert('error', error.message || 'Gagal mengubah status token API.');
                        }
                    }
                });
            }

            const docsModal = document.getElementById('apiDocsModal');
            if (docsModal) {
                docsModal.addEventListener('click', async function (event) {
                    const tabButton = event.target instanceof Element
                        ? event.target.closest('.js-api-doc-tab')
                        : null;

                    if (tabButton instanceof HTMLButtonElement) {
                        setActiveApiDoc(tabButton.dataset.docIndex || '0');
                        return;
                    }

                    const copyButton = event.target instanceof Element
                        ? event.target.closest('.js-api-doc-copy')
                        : null;

                    if (copyButton instanceof HTMLButtonElement) {
                        await copyApiDoc(copyButton);
                    }
                });
            }

            document.querySelectorAll('.js-copy-text').forEach((button) => {
                button.addEventListener('click', async function () {
                    await copyText(button.dataset.copy || '');
                    if (window.showAlert) {
                        window.showAlert('success', 'URL endpoint disalin.');
                    }
                });
            });
        });
    })();
</script>
@endpush
