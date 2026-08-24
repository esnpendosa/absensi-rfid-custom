<script>
    (function () {
        const initialCardRecords = @json($cardRecords);
        const initialStudentRecords = @json($studentRecords);
        const initialTeacherRecords = @json($teacherRecords ?? []);
        const dataUrl = @json($dataUrl);
        const streamUrl = @json($streamUrl);
        const storeUrl = @json($storeUrl);
        const itemUrlTemplate = @json($updateUrlTemplate);
        const csrfToken = @json(csrf_token());
        const showAlert = window.showAlert || function (type, message) {
            console[type === 'error' ? 'error' : 'log'](message);
        };
        const AUTO_REFRESH_INTERVAL_MS = 5000;
        const state = {
            page: 1,
            limit: 10,
            search: '',
            status: '',
            isRefreshing: false,
        };
        let autoRefreshTimerId = null;
        let kartuAbsensiEventSource = null;

        let cardRecords = Array.isArray(initialCardRecords)
            ? initialCardRecords.map(normalizeCardRecord)
            : [];
        let studentRecords = Array.isArray(initialStudentRecords)
            ? initialStudentRecords.map(normalizeStudentRecord)
            : [];
        let teacherRecords = Array.isArray(initialTeacherRecords)
            ? initialTeacherRecords.map(normalizeTeacherRecord)
            : [];

        sortCardRecords();

        function getView() {
            return document.getElementById('view-kartu-absensi');
        }

        function getKartuAbsensiAutoRefreshStatus() {
            return document.getElementById('kartu-absensi-auto-refresh-status');
        }

        function updateKartuAbsensiAutoRefreshStatus(message = '', iconClass = 'fa-wifi') {
            const statusElement = getKartuAbsensiAutoRefreshStatus();
            if (!statusElement) {
                return;
            }

            statusElement.innerHTML = `
                <i class="fas ${escapeHtml(iconClass)}"></i>
                ${escapeHtml(message || `Auto refresh ${Math.round(AUTO_REFRESH_INTERVAL_MS / 1000)} detik`)}
            `;
        }

        function stampKartuAbsensiSyncStatus() {
            const syncTime = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });

            if (isKartuAbsensiRealtimeConnected()) {
                updateKartuAbsensiAutoRefreshStatus(`Realtime ${syncTime}`, 'fa-wifi');
                return;
            }

            updateKartuAbsensiAutoRefreshStatus(`Auto refresh ${syncTime}`, 'fa-sync-alt');
        }

        function isKartuAbsensiRealtimeConnected() {
            if (typeof EventSource === 'undefined' || !kartuAbsensiEventSource) {
                return false;
            }

            return kartuAbsensiEventSource.readyState === EventSource.OPEN;
        }

        function normalize(value) {
            return String(value || '').trim().toLowerCase();
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function normalizeCardRecord(record) {
            const ownerType = record?.owner_type || (record?.siswa_id ? 'siswa' : (record?.guru_id ? 'guru' : 'unlinked'));
            const ownerName = String(record?.owner_name || record?.student_name || '').trim();
            const ownerId = String(record?.owner_identifier || record?.student_nisn || '').trim();
            const ownerClass = String(record?.owner_class || record?.student_class || '').trim();

            return {
                id: Number(record?.id || 0),
                code: String(record?.code || '').trim().toUpperCase(),
                siswa_id: record?.siswa_id ? Number(record.siswa_id) : null,
                guru_id: record?.guru_id ? Number(record.guru_id) : null,
                owner_type: ownerType,
                owner_name: ownerName,
                owner_identifier: ownerId,
                owner_class: ownerClass,
                student_name: ownerName,
                student_nisn: ownerId,
                student_class: ownerClass,
                last_scanned_at: record?.last_scanned_at || null,
                last_scanned_date: record?.last_scanned_date || null,
                last_scanned_time: record?.last_scanned_time || null,
                last_scanned_source: record?.last_scanned_source || null,
            };
        }

        function normalizeStudentRecord(record) {
            return {
                id: Number(record?.id || 0),
                target_key: 'siswa_' + Number(record?.id || 0),
                nama: String(record?.nama || '').trim(),
                nisn: String(record?.nisn || '').trim(),
                kelas: String(record?.kelas || '').trim(),
                type: 'siswa',
            };
        }

        function normalizeTeacherRecord(record) {
            return {
                id: Number(record?.id || 0),
                target_key: 'guru_' + Number(record?.id || 0),
                nama: String(record?.nama || record?.name || '').trim(),
                username: String(record?.username || '').trim(),
                jabatan: String(record?.jabatan || 'Guru & Staf').trim(),
                type: 'guru',
            };
        }

        function sortCardRecords() {
            cardRecords.sort((left, right) => {
                const leftLinked = left.owner_type !== 'unlinked' ? 1 : 0;
                const rightLinked = right.owner_type !== 'unlinked' ? 1 : 0;

                if (leftLinked !== rightLinked) {
                    return rightLinked - leftLinked; // Show linked cards first
                }

                const leftScan = left.last_scanned_at ? Date.parse(left.last_scanned_at) || 0 : 0;
                const rightScan = right.last_scanned_at ? Date.parse(right.last_scanned_at) || 0 : 0;

                if (leftScan !== rightScan) {
                    return rightScan - leftScan;
                }

                return Number(right.id || 0) - Number(left.id || 0);
            });
        }

        function getItemUrl(cardId) {
            return String(itemUrlTemplate).replace('__ID__', encodeURIComponent(String(cardId)));
        }

        function getOwnerOptionLabel(targetKey) {
            if (!targetKey) return '';
            if (targetKey.startsWith('guru_')) {
                const id = Number(targetKey.replace('guru_', ''));
                const teacher = teacherRecords.find((t) => t.id === id);
                return teacher ? `👨‍🏫 ${teacher.nama} (${teacher.username}) - ${teacher.jabatan}` : '';
            }
            if (targetKey.startsWith('siswa_')) {
                const id = Number(targetKey.replace('siswa_', ''));
                const student = studentRecords.find((s) => s.id === id);
                return student ? `🎓 ${student.nama} (${student.nisn})${student.kelas ? ' - ' + student.kelas : ''}` : '';
            }
            return '';
        }

        function getSearchBlob(record) {
            return normalize([
                record.code,
                record.owner_name,
                record.owner_identifier,
                record.owner_class,
                record.last_scanned_date,
                record.last_scanned_time,
                record.last_scanned_source,
            ].join(' '));
        }

        function getFilteredRecords() {
            const search = normalize(state.search);
            const status = normalize(state.status);

            return cardRecords.filter((record) => {
                const matchesSearch = search === '' || getSearchBlob(record).includes(search);
                const isLinked = record.owner_type !== 'unlinked';
                const recordStatus = isLinked ? 'linked' : 'unlinked';
                const matchesStatus = status === '' || recordStatus === status;

                return matchesSearch && matchesStatus;
            });
        }

        function buildRowHtml(record, rowNumber) {
            let ownerHtml = '';
            let classBadgeHtml = '';

            if (record.owner_type === 'guru' || record.guru_id) {
                ownerHtml = `
                    <div class="font-semibold text-indigo-900 flex items-center gap-1.5">
                        <span>${escapeHtml(record.owner_name)}</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">GURU & STAF</span>
                    </div>
                    <div class="text-[11px] text-gray-500">${escapeHtml(record.owner_identifier)}</div>
                `;
                classBadgeHtml = `
                    <span class="inline-flex items-center px-2 py-1 rounded bg-indigo-50 text-indigo-700 text-[11px] font-bold">
                        ${escapeHtml(record.owner_class || 'Guru & Staf')}
                    </span>
                `;
            } else if (record.owner_type === 'siswa' || record.siswa_id) {
                ownerHtml = `
                    <div class="font-semibold text-gray-900 flex items-center gap-1.5">
                        <span>${escapeHtml(record.owner_name)}</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">SISWA</span>
                    </div>
                    <div class="text-[11px] text-gray-500">${escapeHtml(record.owner_identifier)}</div>
                `;
                classBadgeHtml = `
                    <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700 text-[11px] font-semibold">
                        ${escapeHtml(record.owner_class || '-')}
                    </span>
                `;
            } else {
                ownerHtml = `
                    <div class="font-semibold text-amber-700">Belum ditautkan</div>
                    <div class="text-[11px] text-amber-600">Kartu belum punya pemilik</div>
                `;
                classBadgeHtml = '<span class="text-gray-400">-</span>';
            }

            const scanHtml = record.last_scanned_at
                ? `
                    <div class="font-semibold text-gray-900">${escapeHtml(record.last_scanned_date)}</div>
                    <div class="text-[11px] text-gray-500">${escapeHtml(record.last_scanned_time)} | ${escapeHtml(record.last_scanned_source || 'unknown')}</div>
                `
                : '<span class="text-gray-400">Belum pernah discan</span>';

            return `
                <tr class="hover:bg-gray-50">
                    <td class="p-3 text-center text-gray-400 font-mono">${rowNumber}</td>
                    <td class="p-3 align-top">
                        <div class="font-mono font-semibold text-gray-900 uppercase">${escapeHtml(record.code)}</div>
                        <div class="text-[10px] text-gray-400">ID #${escapeHtml(record.id)}</div>
                    </td>
                    <td class="p-3 align-top">${ownerHtml}</td>
                    <td class="p-3 hidden md:table-cell align-top">${classBadgeHtml}</td>
                    <td class="p-3 hidden lg:table-cell align-top">${scanHtml}</td>
                    <td class="p-3 align-top text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" onclick="showEditKartuAbsensiModal(${record.id})" class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition" title="Edit / Tautkan">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button" onclick="confirmDeleteKartuAbsensi(${record.id})" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        function renderKartuAbsensiTable() {
            const tbody = document.getElementById('tbody-kartu-absensi');
            const info = document.getElementById('info-kartu-absensi');
            const prevButton = document.getElementById('btn-prev-kartu-absensi');
            const nextButton = document.getElementById('btn-next-kartu-absensi');

            if (!tbody) return;

            const filteredRecords = getFilteredRecords();
            const totalRecords = filteredRecords.length;
            const totalPages = state.limit === Infinity ? 1 : Math.max(1, Math.ceil(totalRecords / state.limit));

            if (state.page > totalPages) state.page = totalPages;
            if (state.page < 1) state.page = 1;

            if (cardRecords.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada kartu absensi terdaftar.</td></tr>';
                if (info) info.textContent = 'Menampilkan 0 data';
                if (prevButton) prevButton.disabled = true;
                if (nextButton) nextButton.disabled = true;
                return;
            }

            if (totalRecords === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-400">Tidak ada data yang cocok dengan filter saat ini.</td></tr>';
                if (info) info.textContent = 'Tidak ada data yang cocok';
                if (prevButton) prevButton.disabled = true;
                if (nextButton) nextButton.disabled = true;
                return;
            }

            const start = state.limit === Infinity ? 0 : (state.page - 1) * state.limit;
            const end = state.limit === Infinity ? totalRecords : Math.min(start + state.limit, totalRecords);
            const visibleRecords = filteredRecords.slice(start, end);

            tbody.innerHTML = visibleRecords
                .map((record, index) => buildRowHtml(record, start + index + 1))
                .join('');

            if (info) {
                info.textContent = `Menampilkan ${start + 1}-${end} dari ${totalRecords} data`;
            }

            if (prevButton) prevButton.disabled = state.page <= 1;
            if (nextButton) nextButton.disabled = state.limit === Infinity || state.page >= totalPages;
        }

        function getModalShell(create = false) {
            const container = document.getElementById('modalContainer');
            if (!container) return null;

            let shell = container.querySelector('[data-kartu-modal-shell]');
            if (!shell && create) {
                container.insertAdjacentHTML('beforeend', `
                    <div data-kartu-modal-shell class="fixed inset-0 z-50 hidden items-center justify-center p-4">
                        <div class="absolute inset-0 bg-gray-900/45 transition-opacity" onclick="closeKartuAbsensiModal()"></div>
                        <div data-kartu-modal-host class="relative w-full max-w-xl overflow-visible"></div>
                    </div>
                `);
                shell = container.querySelector('[data-kartu-modal-shell]');
            }

            return shell;
        }

        function showKartuAbsensiModal(content) {
            const shell = getModalShell(true);
            if (!shell) return;

            const host = shell.querySelector('[data-kartu-modal-host]');
            if (!host) return;

            host.innerHTML = content;
            shell.classList.remove('hidden');
            shell.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeKartuAbsensiModal() {
            const shell = getModalShell(false);
            if (!shell) return;

            const host = shell.querySelector('[data-kartu-modal-host]');
            if (host) host.innerHTML = '';

            shell.classList.add('hidden');
            shell.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function renderOwnerDropdownItems(keyword = '') {
            const dropdown = document.getElementById('kartuAbsensiOwnerDropdown');
            if (!dropdown) return;

            const query = normalize(keyword);
            const items = [`
                <button
                    type="button"
                    onclick="selectKartuAbsensiOwner('')"
                    class="w-full text-left px-3 py-2.5 hover:bg-gray-50 text-xs font-semibold text-gray-600 transition border-b border-gray-100"
                >
                    ❌ Jangan Tautkan (Belum Ditautkan)
                </button>
            `];

            const filteredTeachers = teacherRecords.filter((teacher) => {
                if (query === '') return true;
                const blob = normalize([teacher.nama, teacher.username, teacher.jabatan].join(' '));
                return blob.includes(query);
            });

            const filteredStudents = studentRecords.filter((student) => {
                if (query === '') return true;
                const blob = normalize([student.nama, student.nisn, student.kelas].join(' '));
                return blob.includes(query);
            });

            if (filteredTeachers.length > 0) {
                items.push('<div class="px-3 py-1.5 bg-indigo-50/70 text-[10px] font-bold text-indigo-700 uppercase tracking-wider">👨‍🏫 Guru & Staf</div>');
                filteredTeachers.forEach((teacher) => {
                    items.push(`
                        <button
                            type="button"
                            onclick="selectKartuAbsensiOwner('guru_${teacher.id}')"
                            class="w-full text-left px-3 py-2 hover:bg-indigo-50 transition border-b border-gray-50"
                        >
                            <div class="text-xs font-bold text-gray-800">${escapeHtml(teacher.nama)}</div>
                            <div class="text-[10px] text-gray-500">${escapeHtml(teacher.username)} • ${escapeHtml(teacher.jabatan)}</div>
                        </button>
                    `);
                });
            }

            if (filteredStudents.length > 0) {
                items.push('<div class="px-3 py-1.5 bg-blue-50/70 text-[10px] font-bold text-blue-700 uppercase tracking-wider">🎓 Siswa</div>');
                filteredStudents.forEach((student) => {
                    items.push(`
                        <button
                            type="button"
                            onclick="selectKartuAbsensiOwner('siswa_${student.id}')"
                            class="w-full text-left px-3 py-2 hover:bg-blue-50 transition border-b border-gray-50"
                        >
                            <div class="text-xs font-bold text-gray-800">${escapeHtml(student.nama)}</div>
                            <div class="text-[10px] text-gray-500">${escapeHtml(student.nisn)}${student.kelas ? ` • Kelas ${escapeHtml(student.kelas)}` : ''}</div>
                        </button>
                    `);
                });
            }

            if (filteredTeachers.length === 0 && filteredStudents.length === 0) {
                items.push('<div class="px-3 py-4 text-xs text-gray-400 italic text-center">Data guru atau siswa tidak ditemukan.</div>');
            }

            dropdown.innerHTML = items.join('');
        }

        window.openKartuAbsensiOwnerDropdown = function () {
            const dropdown = document.getElementById('kartuAbsensiOwnerDropdown');
            if (!dropdown) return;
            renderOwnerDropdownItems('');
            dropdown.classList.remove('hidden');
        };

        window.filterKartuAbsensiOwnerDropdown = function (keyword) {
            renderOwnerDropdownItems(keyword);
            const dropdown = document.getElementById('kartuAbsensiOwnerDropdown');
            if (dropdown) dropdown.classList.remove('hidden');
        };

        window.selectKartuAbsensiOwner = function (targetKey) {
            const input = document.getElementById('kartuAbsensiOwnerSearch');
            const hidden = document.getElementById('kartuAbsensiOwnerTarget');

            if (input) input.value = getOwnerOptionLabel(targetKey);
            if (hidden) hidden.value = targetKey;

            closeKartuAbsensiOwnerDropdown();
        };

        function closeKartuAbsensiOwnerDropdown() {
            const dropdown = document.getElementById('kartuAbsensiOwnerDropdown');
            if (!dropdown) return;
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 200);
        }

        function getFormHtml(cardId = null) {
            const isEdit = cardId !== null && cardId !== undefined;
            const card = isEdit ? cardRecords.find((item) => Number(item.id) === Number(cardId)) || null : null;

            let currentTargetKey = '';
            if (card) {
                if (card.guru_id) currentTargetKey = 'guru_' + card.guru_id;
                else if (card.siswa_id) currentTargetKey = 'siswa_' + card.siswa_id;
            }

            const currentLabel = getOwnerOptionLabel(currentTargetKey);
            const title = isEdit ? 'Edit & Tautkan Kartu Absensi' : 'Tambah Kartu Absensi';
            const submitLabel = isEdit ? 'Perbarui' : 'Simpan';
            const codeInputClass = isEdit
                ? 'w-full bg-gray-100 border border-gray-200 text-gray-600 text-xs rounded-lg block p-2.5 transition-all font-mono uppercase cursor-not-allowed font-bold'
                : 'w-full bg-white border border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition-all font-mono uppercase font-bold';

            return `
                <div class="bg-white rounded-2xl shadow-2xl overflow-visible">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-base font-bold text-gray-800">${title}</h3>
                        <button type="button" onclick="closeKartuAbsensiModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <div class="p-5 overflow-visible">
                        <div id="kartuAbsensiFormError" class="hidden mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700 font-semibold"></div>
                        <form onsubmit="submitKartuAbsensiForm(event, ${isEdit ? Number(card.id) : 'null'})" class="space-y-4">
                            <div>
                                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Kode Kartu / UID</label>
                                <input
                                    id="kartuAbsensiCodeInput"
                                    type="text"
                                    value="${escapeHtml(card?.code || '')}"
                                    placeholder="Contoh: 3277946221"
                                    class="${codeInputClass}"
                                    ${isEdit ? 'readonly' : 'required'}
                                >
                            </div>

                            <div>
                                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Tautkan ke Pemilik (Guru / Siswa)</label>
                                <div class="relative z-30">
                                    <input
                                        id="kartuAbsensiOwnerSearch"
                                        type="text"
                                        value="${escapeHtml(currentLabel)}"
                                        placeholder="Ketik untuk cari nama Siswa atau Guru & Staf..."
                                        autocomplete="off"
                                        onfocus="openKartuAbsensiOwnerDropdown()"
                                        oninput="filterKartuAbsensiOwnerDropdown(this.value)"
                                        class="w-full bg-white border border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition-all"
                                    >
                                    <input id="kartuAbsensiOwnerTarget" type="hidden" value="${escapeHtml(currentTargetKey)}">
                                    <div id="kartuAbsensiOwnerDropdown" class="hidden absolute left-0 right-0 z-40 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-2xl max-h-60 overflow-y-auto divide-y divide-gray-50"></div>
                                </div>
                                <p class="mt-1.5 text-[10px] text-gray-400">Pilih dari Guru atau Siswa. Kosongkan jika kartu belum ingin ditautkan.</p>
                            </div>

                            <div class="flex justify-end gap-2 pt-3">
                                <button type="button" onclick="closeKartuAbsensiModal()" class="inline-flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-gray-200 bg-white text-gray-700 font-bold text-xs hover:bg-gray-50 transition">
                                    <i class="fas fa-times text-[10px]"></i> Batal
                                </button>
                                <button type="submit" id="kartuAbsensiSubmitButton" class="inline-flex items-center justify-center gap-1.5 h-8 px-4 rounded-lg bg-blue-600 text-white font-bold text-xs shadow-sm hover:bg-blue-700 transition">
                                    <i class="fas fa-save text-[10px]"></i> ${submitLabel}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }

        function setFormError(message) {
            const errorBox = document.getElementById('kartuAbsensiFormError');
            if (!errorBox) return;

            if (!message) {
                errorBox.textContent = '';
                errorBox.classList.add('hidden');
                return;
            }

            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }

        async function apiRequest(url, options = {}) {
            const method = String(options.method || 'GET').toUpperCase();
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            };

            if (method !== 'GET') {
                headers['Content-Type'] = 'application/json';
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers,
                body: options.body ? JSON.stringify(options.body) : undefined,
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                const firstError = payload?.errors
                    ? Object.values(payload.errors)[0]?.[0]
                    : null;
                throw new Error(firstError || payload.message || 'Permintaan gagal diproses.');
            }

            return payload;
        }

        function applyServerData(payload) {
            if (Array.isArray(payload?.cards)) {
                cardRecords = payload.cards.map(normalizeCardRecord);
            }
            if (Array.isArray(payload?.students)) {
                studentRecords = payload.students.map(normalizeStudentRecord);
            }
            if (Array.isArray(payload?.teachers)) {
                teacherRecords = payload.teachers.map(normalizeTeacherRecord);
            }
            sortCardRecords();
        }

        function upsertCardRecord(record) {
            const normalized = normalizeCardRecord(record);
            const index = cardRecords.findIndex((item) => Number(item.id) === Number(normalized.id));

            if (index >= 0) {
                cardRecords[index] = normalized;
            } else {
                cardRecords.push(normalized);
            }

            sortCardRecords();
        }

        window.showAddKartuAbsensiModal = function () {
            setFormError('');
            showKartuAbsensiModal(getFormHtml());
        };

        window.showEditKartuAbsensiModal = function (cardId) {
            const card = cardRecords.find((item) => Number(item.id) === Number(cardId)) || null;
            if (!card) {
                showAlert('error', 'Data kartu tidak ditemukan.');
                return;
            }

            setFormError('');
            showKartuAbsensiModal(getFormHtml(card.id));
        };

        window.submitKartuAbsensiForm = async function (event, cardId = null) {
            event.preventDefault();
            setFormError('');

            const codeInput = document.getElementById('kartuAbsensiCodeInput');
            const ownerHiddenInput = document.getElementById('kartuAbsensiOwnerTarget');
            const submitButton = document.getElementById('kartuAbsensiSubmitButton');

            const code = String(codeInput?.value || '').trim().toUpperCase();
            const ownerTarget = String(ownerHiddenInput?.value || '').trim();
            const isEdit = cardId !== null && cardId !== undefined;

            if (!isEdit && code === '') {
                setFormError('Kode kartu wajib diisi.');
                return;
            }

            const originalButtonHtml = submitButton ? submitButton.innerHTML : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-75', 'cursor-not-allowed');
                submitButton.innerHTML = '<i class="fas fa-circle-notch fa-spin text-[10px]"></i>Menyimpan...';
            }

            try {
                const payload = {
                    owner_target: ownerTarget,
                };

                if (!isEdit) {
                    payload.code = code;
                }

                const response = await apiRequest(isEdit ? getItemUrl(cardId) : storeUrl, {
                    method: isEdit ? 'PUT' : 'POST',
                    body: payload,
                });

                upsertCardRecord(response?.data || {});
                state.page = 1;
                renderKartuAbsensiTable();
                closeKartuAbsensiModal();
                showAlert('success', response?.message || (isEdit ? 'Kartu absensi diperbarui.' : 'Kartu absensi ditambahkan.'));
            } catch (error) {
                setFormError(error?.message || 'Permintaan gagal diproses.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-75', 'cursor-not-allowed');
                    submitButton.innerHTML = originalButtonHtml;
                }
            }
        };

        async function deleteKartuAbsensi(cardId) {
            const response = await apiRequest(getItemUrl(cardId), {
                method: 'DELETE',
            });

            cardRecords = cardRecords.filter((item) => Number(item.id) !== Number(cardId));
            renderKartuAbsensiTable();
            showAlert('success', response?.message || 'Kartu absensi dihapus.');
        }

        window.confirmDeleteKartuAbsensi = function (cardId) {
            const card = cardRecords.find((item) => Number(item.id) === Number(cardId)) || null;
            if (!card) {
                showAlert('error', 'Data kartu tidak ditemukan.');
                return;
            }

            const handleDelete = async function () {
                try {
                    await deleteKartuAbsensi(cardId);
                } catch (error) {
                    showAlert('error', error?.message || 'Gagal menghapus kartu.');
                }
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus kartu?',
                    html: `Kode <b>${escapeHtml(card.code)}</b> akan dihapus dan dilepas tautannya.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleDelete();
                    }
                });
                return;
            }

            if (window.confirm(`Hapus kartu ${card.code}?`)) {
                handleDelete();
            }
        };

        window.handleKartuAbsensiSearch = function (value) {
            state.search = value;
            state.page = 1;
            renderKartuAbsensiTable();
        };

        window.handleKartuAbsensiStatusFilter = function (value) {
            state.status = value;
            state.page = 1;
            renderKartuAbsensiTable();
        };

        window.handleKartuAbsensiLimit = function (value) {
            state.limit = value === 'all' ? Infinity : Number(value);
            state.page = 1;
            renderKartuAbsensiTable();
        };

        window.changeKartuAbsensiPage = function (delta) {
            state.page += delta;
            renderKartuAbsensiTable();
        };

        window.refreshKartuAbsensiPage = async function (button = null) {
            if (button) {
                button.disabled = true;
                button.classList.add('opacity-75');
            }

            try {
                const response = await apiRequest(dataUrl, { method: 'GET' });
                applyServerData(response?.data || {});
                renderKartuAbsensiTable();
                stampKartuAbsensiSyncStatus();
                showAlert('success', 'Data kartu absensi berhasil diperbarui.');
            } catch (error) {
                showAlert('error', error?.message || 'Gagal memperbarui data.');
            } finally {
                if (button) {
                    button.disabled = false;
                    button.classList.remove('opacity-75');
                }
            }
        };

        window.closeKartuAbsensiModal = closeKartuAbsensiModal;

        // Inisialisasi awal render
        renderKartuAbsensiTable();
        stampKartuAbsensiSyncStatus();

        // Realtime SSE
        if (typeof EventSource !== 'undefined' && streamUrl) {
            try {
                kartuAbsensiEventSource = new EventSource(streamUrl);
                kartuAbsensiEventSource.addEventListener('sync', function (event) {
                    try {
                        const payload = JSON.parse(event.data);
                        applyServerData(payload);
                        renderKartuAbsensiTable();
                        stampKartuAbsensiSyncStatus();
                    } catch (e) {
                        console.error('SSE sync parse error:', e);
                    }
                });
            } catch (err) {
                console.warn('Realtime SSE not available, falling back to polling:', err);
            }
        }
    })();
</script>
