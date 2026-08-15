<script>
    (function () {
        const root = document.getElementById('view-persuratan');
        if (!root) return;

        const state = {
            jenis: ['masuk', 'keluar'].includes(String(root.dataset.defaultJenis || '')) ? String(root.dataset.defaultJenis) : 'masuk',
            status: '',
            search: '',
            page: 1,
            perPage: 10,
            total: 0,
            lastPage: 1,
            from: 0,
            to: 0,
            rows: [],
            summary: {
                masuk: 0,
                keluar: 0,
                diarsipkan: 0
            }
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const userPermissions = Array.isArray(window.APP_CURRENT_USER?.permissions)
            ? window.APP_CURRENT_USER.permissions
            : [];
        const canManage = root.dataset.canManage === '1' || userPermissions.includes('persuratan.manage');
        let searchTimer = null;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getRoute(name, fallback = '') {
            return String(window.APP_ROUTES?.[name] || fallback || '');
        }

        function fillRoute(template, id) {
            return String(template || '').replace('__ID__', encodeURIComponent(String(id)));
        }

        function showAlert(type, message) {
            if (typeof window.showAlert === 'function') {
                window.showAlert(type, message);
                return;
            }
            console[type === 'error' ? 'error' : 'log'](message);
        }

        function extractError(payload, fallback = 'Permintaan gagal diproses.') {
            if (!payload || typeof payload !== 'object') {
                return fallback;
            }
            if (typeof payload.message === 'string' && payload.message.trim() !== '') {
                return payload.message;
            }
            if (payload.errors && typeof payload.errors === 'object') {
                const firstKey = Object.keys(payload.errors)[0];
                const firstValue = firstKey ? payload.errors[firstKey] : null;
                if (Array.isArray(firstValue) && firstValue.length > 0) {
                    return String(firstValue[0]);
                }
                if (typeof firstValue === 'string' && firstValue.trim() !== '') {
                    return firstValue;
                }
            }
            return fallback;
        }

        async function apiRequest(url, options = {}) {
            if (!url) {
                throw new Error('Endpoint persuratan tidak tersedia.');
            }

            const method = String(options.method || 'GET').toUpperCase();
            const isFormData = options.body instanceof FormData;
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {})
            };

            if (method !== 'GET') {
                headers['X-CSRF-TOKEN'] = csrfToken;
                if (!isFormData) {
                    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                }
            }

            const response = await fetch(url, {
                method,
                credentials: 'same-origin',
                ...options,
                headers
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || payload.success === false) {
                throw new Error(extractError(payload));
            }

            return payload;
        }

        function cleanBrowserUrl() {
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete('jenis');
                window.history.replaceState(null, '', url.toString());
            } catch (error) {
                // Abaikan browser lama yang tidak mendukung URL API.
            }
        }

        function renderTabs() {
            document.querySelectorAll('[data-surat-tab]').forEach((button) => {
                const active = String(button.dataset.suratTab || '') === state.jenis;
                button.classList.toggle('bg-indigo-600', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('border-indigo-600', active);
                button.classList.toggle('shadow-sm', active);
                button.classList.toggle('bg-white', !active);
                button.classList.toggle('text-gray-600', !active);
                button.classList.toggle('border-gray-200', !active);
                button.classList.toggle('hover:bg-gray-50', !active);
            });

            const pihakHeader = document.getElementById('persuratan-pihak-header');
            if (pihakHeader) {
                pihakHeader.textContent = state.jenis === 'masuk' ? 'Asal Surat' : 'Tujuan Surat';
            }
        }

        function renderSummary() {
            const masuk = document.getElementById('persuratan-summary-masuk');
            const keluar = document.getElementById('persuratan-summary-keluar');
            const arsip = document.getElementById('persuratan-summary-arsip');

            if (masuk) masuk.textContent = String(Number(state.summary.masuk || 0));
            if (keluar) keluar.textContent = String(Number(state.summary.keluar || 0));
            if (arsip) arsip.textContent = String(Number(state.summary.diarsipkan || 0));
        }

        function renderLoading(message = 'Memuat data persuratan...') {
            const tbody = document.getElementById('tbody-persuratan');
            if (!tbody) return;

            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="p-8 text-center">
                        <div class="inline-flex items-center gap-2 text-indigo-600 text-sm font-semibold">
                            <i class="fas fa-circle-notch fa-spin"></i>
                            <span>${escapeHtml(message)}</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        function updatePagination() {
            const info = document.getElementById('info-persuratan');
            const prev = document.getElementById('btn-prev-persuratan');
            const next = document.getElementById('btn-next-persuratan');

            if (state.total <= 0) {
                if (info) info.textContent = 'Tidak ada data surat.';
                if (prev) prev.disabled = true;
                if (next) next.disabled = true;
                return;
            }

            if (info) {
                info.textContent = `Menampilkan ${state.from} - ${state.to} dari ${state.total} surat`;
            }
            if (prev) prev.disabled = state.page <= 1;
            if (next) next.disabled = state.page >= state.lastPage;
        }

        function statusBadge(row) {
            const archived = String(row.status || '') === 'diarsipkan';
            const cls = archived
                ? 'bg-amber-50 text-amber-700 border-amber-200'
                : 'bg-emerald-50 text-emerald-700 border-emerald-200';

            return `<span class="inline-flex items-center px-2 py-1 rounded border text-[11px] font-bold ${cls}">${escapeHtml(row.status_label || '-')}</span>`;
        }

        function lampiranCell(row) {
            if (!row.download_url) {
                return '<span class="text-gray-300">-</span>';
            }

            return `
                <a href="${escapeHtml(row.download_url)}" target="_blank" rel="noopener" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition" title="${escapeHtml(row.lampiran_nama || 'Download lampiran')}">
                    <i class="fas fa-paperclip"></i>
                </a>
            `;
        }

        function actionCell(row) {
            const buttons = [];

            if (canManage) {
                buttons.push(`
                    <button onclick="showEditSuratModal(${Number(row.id)})" class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition" title="Edit">
                        <i class="fas fa-pen"></i>
                    </button>
                `);
                buttons.push(`
                    <button onclick="confirmDeleteSurat(${Number(row.id)})" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                `);
            }

            if (buttons.length === 0) {
                return '<span class="text-gray-300">-</span>';
            }

            return `<div class="flex items-center justify-center gap-2">${buttons.join('')}</div>`;
        }

        function renderRows() {
            const tbody = document.getElementById('tbody-persuratan');
            if (!tbody) return;

            if (!state.rows || state.rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-gray-400">Data surat tidak ditemukan.</td></tr>';
                updatePagination();
                return;
            }

            tbody.innerHTML = state.rows.map((row, index) => {
                const ringkasan = String(row.ringkasan || '').trim();
                const lampiranInfo = row.lampiran_nama
                    ? `<div class="mt-1 text-[10px] text-gray-400 truncate max-w-[180px]">${escapeHtml(row.lampiran_nama)}${row.lampiran_size_label ? ' · ' + escapeHtml(row.lampiran_size_label) : ''}</div>`
                    : '';

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 text-center text-gray-500">${state.from + index}</td>
                        <td class="p-3">
                            <div class="font-bold text-gray-800 break-words">${escapeHtml(row.nomor_surat || '-')}</div>
                            <div class="mt-1 text-[10px] text-gray-400">${escapeHtml(row.jenis_label || '-')}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-semibold text-gray-700">${escapeHtml(row.tanggal_surat_label || '-')}</div>
                            <div class="mt-1 text-[10px] text-gray-400">${state.jenis === 'masuk' ? 'Diterima' : 'Dikirim'}: ${escapeHtml(row.tanggal_agenda_label || '-')}</div>
                        </td>
                        <td class="p-3">${escapeHtml(row.pihak_label || '-')}</td>
                        <td class="p-3">
                            <div class="font-semibold text-gray-800 break-words">${escapeHtml(row.perihal || '-')}</div>
                            ${ringkasan ? `<div class="mt-1 text-[11px] text-gray-500 line-clamp-2">${escapeHtml(ringkasan)}</div>` : ''}
                            ${lampiranInfo}
                        </td>
                        <td class="p-3">${statusBadge(row)}</td>
                        <td class="p-3 text-center">${lampiranCell(row)}</td>
                        <td class="p-3 text-center">${actionCell(row)}</td>
                    </tr>
                `;
            }).join('');

            updatePagination();
        }

        async function loadPersuratanData(showToast = false) {
            renderTabs();
            renderLoading();

            try {
                const params = new URLSearchParams({
                    jenis: state.jenis,
                    status: state.status,
                    search: state.search,
                    page: String(state.page),
                    per_page: String(state.perPage)
                });

                const payload = await apiRequest(`${getRoute('persuratanData')}?${params.toString()}`);
                const meta = payload.meta || {};

                state.rows = Array.isArray(payload.data) ? payload.data : [];
                state.summary = payload.summary || state.summary;
                state.page = Number(meta.page || 1);
                state.perPage = Number(meta.per_page || state.perPage);
                state.total = Number(meta.total || 0);
                state.lastPage = Number(meta.last_page || 1);
                state.from = Number(meta.from || 0);
                state.to = Number(meta.to || 0);

                renderSummary();
                renderRows();

                if (showToast) {
                    showAlert('success', 'Data persuratan diperbarui.');
                }
            } catch (error) {
                const tbody = document.getElementById('tbody-persuratan');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-red-500">${escapeHtml(error.message || String(error))}</td></tr>`;
                }
                state.rows = [];
                state.total = 0;
                state.from = 0;
                state.to = 0;
                state.lastPage = 1;
                renderSummary();
                updatePagination();
            }
        }

        function getModalShell(create = false) {
            const container = document.getElementById('modalContainer');
            if (!container) return null;

            let shell = container.querySelector('[data-modal-shell]');
            if (!shell && create) {
                container.innerHTML = `
                    <div data-modal-shell class="fixed inset-0 z-50 hidden items-center justify-center p-4">
                        <div class="absolute inset-0 bg-gray-900/45 transition-opacity" onclick="closeModal()"></div>
                        <div data-modal-host class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto"></div>
                    </div>
                `;
                shell = container.querySelector('[data-modal-shell]');
            }

            return shell;
        }

        function showModal(content) {
            const shell = getModalShell(true);
            if (!shell) return;
            const host = shell.querySelector('[data-modal-host]');
            if (!host) return;

            host.innerHTML = content;
            shell.classList.remove('hidden');
            shell.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            const shell = getModalShell(false);
            if (!shell) return;
            const host = shell.querySelector('[data-modal-host]');
            if (host) host.innerHTML = '';
            shell.classList.add('hidden');
            shell.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function selected(value, expected) {
            return String(value || '') === String(expected || '') ? 'selected' : '';
        }

        function getFormHTML(data = {}, suratId = null) {
            const isEdit = suratId !== null && suratId !== undefined;
            const jenis = ['masuk', 'keluar'].includes(String(data.jenis || '')) ? String(data.jenis) : state.jenis;
            const status = String(data.status || 'aktif');
            const labelClass = 'block mb-1 text-xs font-bold text-gray-500 uppercase tracking-wide';
            const inputClass = 'w-full bg-white border border-gray-200 text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500';
            const hasLampiran = isEdit && String(data.lampiran_nama || '').trim() !== '';

            return `
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">${isEdit ? 'Edit Surat' : 'Tambah Surat'}</h3>
                            <p class="text-xs text-gray-500 mt-1">${jenis === 'masuk' ? 'Surat Masuk' : 'Surat Keluar'}</p>
                        </div>
                        <button onclick="closeModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition" title="Tutup"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="p-5">
                        <form id="form-persuratan" onsubmit="saveSurat(event, ${isEdit ? Number(suratId) : 'null'})" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="${labelClass}">Jenis Surat</label>
                                    <select id="surat-jenis" required onchange="toggleSuratJenisFields()" class="${inputClass}">
                                        <option value="masuk" ${selected(jenis, 'masuk')}>Surat Masuk</option>
                                        <option value="keluar" ${selected(jenis, 'keluar')}>Surat Keluar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="${labelClass}">Nomor Surat</label>
                                    <input id="surat-nomor" type="text" required maxlength="100" value="${escapeHtml(data.nomor_surat || '')}" class="${inputClass}" placeholder="Nomor surat">
                                </div>
                                <div>
                                    <label class="${labelClass}">Tanggal Surat</label>
                                    <input id="surat-tanggal" type="date" required value="${escapeHtml(data.tanggal_surat || '')}" class="${inputClass}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div data-surat-field="masuk">
                                    <label class="${labelClass}">Asal Surat</label>
                                    <input id="surat-asal" type="text" maxlength="191" value="${escapeHtml(data.asal_surat || '')}" class="${inputClass}" placeholder="Instansi/pengirim">
                                </div>
                                <div data-surat-field="masuk">
                                    <label class="${labelClass}">Tanggal Diterima</label>
                                    <input id="surat-tanggal-diterima" type="date" value="${escapeHtml(data.tanggal_diterima || '')}" class="${inputClass}">
                                </div>
                                <div data-surat-field="keluar">
                                    <label class="${labelClass}">Tujuan Surat</label>
                                    <input id="surat-tujuan" type="text" maxlength="191" value="${escapeHtml(data.tujuan_surat || '')}" class="${inputClass}" placeholder="Instansi/penerima">
                                </div>
                                <div data-surat-field="keluar">
                                    <label class="${labelClass}">Tanggal Dikirim</label>
                                    <input id="surat-tanggal-dikirim" type="date" value="${escapeHtml(data.tanggal_dikirim || '')}" class="${inputClass}">
                                </div>
                            </div>

                            <div>
                                <label class="${labelClass}">Perihal</label>
                                <input id="surat-perihal" type="text" required maxlength="191" value="${escapeHtml(data.perihal || '')}" class="${inputClass}" placeholder="Perihal surat">
                            </div>

                            <div>
                                <label class="${labelClass}">Ringkasan</label>
                                <textarea id="surat-ringkasan" rows="3" maxlength="3000" class="${inputClass}" placeholder="Opsional">${escapeHtml(data.ringkasan || '')}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="${labelClass}">Status</label>
                                    <select id="surat-status" required class="${inputClass}">
                                        <option value="aktif" ${selected(status, 'aktif')}>Aktif</option>
                                        <option value="diarsipkan" ${selected(status, 'diarsipkan')}>Diarsipkan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="${labelClass}">Lampiran</label>
                                    <input id="surat-lampiran" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="${inputClass}">
                                    ${hasLampiran ? `
                                        <label class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                            <input id="surat-hapus-lampiran" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>Hapus lampiran saat ini: <b>${escapeHtml(data.lampiran_nama || '')}</b></span>
                                        </label>
                                    ` : ''}
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" onclick="closeModal()" class="inline-flex items-center justify-center gap-2 h-9 px-4 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold text-xs hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-200 transition"><i class="fas fa-times text-[10px]"></i>Batal</button>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 h-9 px-4 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold text-xs shadow-sm hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition"><i class="fas fa-save text-[10px]"></i>${isEdit ? 'Perbarui' : 'Simpan'}</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }

        function toggleSuratJenisFields() {
            const jenis = String(document.getElementById('surat-jenis')?.value || 'masuk');
            const masuk = jenis === 'masuk';

            document.querySelectorAll('[data-surat-field]').forEach((node) => {
                node.classList.toggle('hidden', String(node.dataset.suratField || '') !== jenis);
            });

            const asal = document.getElementById('surat-asal');
            const diterima = document.getElementById('surat-tanggal-diterima');
            const tujuan = document.getElementById('surat-tujuan');
            const dikirim = document.getElementById('surat-tanggal-dikirim');

            if (asal) asal.required = masuk;
            if (diterima) diterima.required = masuk;
            if (tujuan) tujuan.required = !masuk;
            if (dikirim) dikirim.required = !masuk;
        }

        function collectFormData() {
            const jenis = String(document.getElementById('surat-jenis')?.value || '').trim();
            const nomor = String(document.getElementById('surat-nomor')?.value || '').trim();
            const tanggalSurat = String(document.getElementById('surat-tanggal')?.value || '').trim();
            const tanggalDiterima = String(document.getElementById('surat-tanggal-diterima')?.value || '').trim();
            const tanggalDikirim = String(document.getElementById('surat-tanggal-dikirim')?.value || '').trim();
            const asal = String(document.getElementById('surat-asal')?.value || '').trim();
            const tujuan = String(document.getElementById('surat-tujuan')?.value || '').trim();
            const perihal = String(document.getElementById('surat-perihal')?.value || '').trim();
            const ringkasan = String(document.getElementById('surat-ringkasan')?.value || '').trim();
            const status = String(document.getElementById('surat-status')?.value || 'aktif').trim();
            const lampiranInput = document.getElementById('surat-lampiran');
            const lampiran = lampiranInput?.files?.[0] || null;
            const hapusLampiran = document.getElementById('surat-hapus-lampiran')?.checked || false;

            if (!['masuk', 'keluar'].includes(jenis)) {
                showAlert('error', 'Jenis surat tidak valid.');
                return null;
            }
            if (!nomor) {
                showAlert('error', 'Nomor surat wajib diisi.');
                return null;
            }
            if (!tanggalSurat) {
                showAlert('error', 'Tanggal surat wajib diisi.');
                return null;
            }
            if (jenis === 'masuk' && (!asal || !tanggalDiterima)) {
                showAlert('error', 'Asal surat dan tanggal diterima wajib diisi untuk surat masuk.');
                return null;
            }
            if (jenis === 'keluar' && (!tujuan || !tanggalDikirim)) {
                showAlert('error', 'Tujuan surat dan tanggal dikirim wajib diisi untuk surat keluar.');
                return null;
            }
            if (!perihal) {
                showAlert('error', 'Perihal wajib diisi.');
                return null;
            }
            if (lampiran && lampiran.size > 5 * 1024 * 1024) {
                showAlert('error', 'Ukuran lampiran maksimal 5 MB.');
                return null;
            }

            const formData = new FormData();
            formData.append('jenis', jenis);
            formData.append('nomor_surat', nomor);
            formData.append('tanggal_surat', tanggalSurat);
            formData.append('tanggal_diterima', jenis === 'masuk' ? tanggalDiterima : '');
            formData.append('tanggal_dikirim', jenis === 'keluar' ? tanggalDikirim : '');
            formData.append('asal_surat', jenis === 'masuk' ? asal : '');
            formData.append('tujuan_surat', jenis === 'keluar' ? tujuan : '');
            formData.append('perihal', perihal);
            formData.append('ringkasan', ringkasan);
            formData.append('status', status);
            formData.append('hapus_lampiran', hapusLampiran ? '1' : '0');

            if (lampiran) {
                formData.append('lampiran', lampiran);
            }

            return formData;
        }

        async function saveSurat(event, suratId) {
            event.preventDefault();
            if (!canManage) {
                showAlert('error', 'Anda tidak memiliki akses untuk mengelola persuratan.');
                return;
            }

            const formData = collectFormData();
            if (!formData) return;

            const isEdit = suratId !== null && suratId !== undefined && String(suratId) !== 'null';
            const submit = event.target.querySelector('button[type="submit"]');
            const original = submit ? submit.innerHTML : '';

            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            if (submit) {
                submit.disabled = true;
                submit.classList.add('opacity-75', 'cursor-not-allowed');
                submit.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Menyimpan...';
            }

            try {
                const url = isEdit
                    ? fillRoute(getRoute('persuratanUpdate'), suratId)
                    : getRoute('persuratanStore');
                const payload = await apiRequest(url, {
                    method: 'POST',
                    body: formData
                });

                closeModal();
                state.jenis = String(payload.data?.jenis || state.jenis);
                state.page = 1;
                await loadPersuratanData();
                showAlert('success', payload.message || 'Data surat berhasil disimpan.');
            } catch (error) {
                showAlert('error', error.message || String(error));
            } finally {
                if (submit) {
                    submit.disabled = false;
                    submit.classList.remove('opacity-75', 'cursor-not-allowed');
                    submit.innerHTML = original;
                }
            }
        }

        function showAddSuratModal() {
            if (!canManage) {
                showAlert('error', 'Anda tidak memiliki akses untuk menambah surat.');
                return;
            }
            showModal(getFormHTML({ jenis: state.jenis, status: 'aktif' }, null));
            toggleSuratJenisFields();
        }

        function showEditSuratModal(id) {
            if (!canManage) {
                showAlert('error', 'Anda tidak memiliki akses untuk mengubah surat.');
                return;
            }

            const row = state.rows.find((item) => Number(item.id) === Number(id));
            if (!row) {
                showAlert('error', 'Data surat tidak ditemukan.');
                return;
            }

            showModal(getFormHTML(row, row.id));
            toggleSuratJenisFields();
        }

        async function deleteSurat(id) {
            try {
                const payload = await apiRequest(fillRoute(getRoute('persuratanDestroy'), id), {
                    method: 'DELETE'
                });
                await loadPersuratanData();
                showAlert('success', payload.message || 'Data surat berhasil dihapus.');
            } catch (error) {
                showAlert('error', error.message || String(error));
            }
        }

        function confirmDeleteSurat(id) {
            if (!canManage) {
                showAlert('error', 'Anda tidak memiliki akses untuk menghapus surat.');
                return;
            }

            const row = state.rows.find((item) => Number(item.id) === Number(id));
            if (!row) {
                showAlert('error', 'Data surat tidak ditemukan.');
                return;
            }

            Swal.fire({
                title: 'Hapus surat?',
                html: `Surat <b>${escapeHtml(row.nomor_surat || '-')}</b> akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteSurat(row.id);
                }
            });
        }

        function setPersuratanJenis(jenis) {
            if (!['masuk', 'keluar'].includes(String(jenis))) return;
            if (state.jenis === jenis) return;

            state.jenis = jenis;
            state.page = 1;
            loadPersuratanData();
        }

        function handlePersuratanStatusFilter(value) {
            state.status = String(value || '');
            state.page = 1;
            loadPersuratanData();
        }

        function handlePersuratanLimit(value) {
            const parsed = parseInt(value, 10);
            state.perPage = [10, 25, 50, 100].includes(parsed) ? parsed : 10;
            state.page = 1;
            loadPersuratanData();
        }

        function handlePersuratanSearch(value) {
            state.search = String(value || '').trim();
            state.page = 1;

            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            searchTimer = setTimeout(() => {
                loadPersuratanData();
            }, 250);
        }

        function changePersuratanPage(direction) {
            const next = state.page + Number(direction || 0);
            if (next < 1 || next > state.lastPage) return;

            state.page = next;
            loadPersuratanData();
        }

        function refreshPersuratanData() {
            loadPersuratanData(true);
        }

        window.closeModal = closeModal;
        window.showAddSuratModal = showAddSuratModal;
        window.showEditSuratModal = showEditSuratModal;
        window.saveSurat = saveSurat;
        window.toggleSuratJenisFields = toggleSuratJenisFields;
        window.confirmDeleteSurat = confirmDeleteSurat;
        window.setPersuratanJenis = setPersuratanJenis;
        window.handlePersuratanStatusFilter = handlePersuratanStatusFilter;
        window.handlePersuratanLimit = handlePersuratanLimit;
        window.handlePersuratanSearch = handlePersuratanSearch;
        window.changePersuratanPage = changePersuratanPage;
        window.refreshPersuratanData = refreshPersuratanData;

        cleanBrowserUrl();
        renderTabs();
        renderSummary();
        loadPersuratanData();
    })();
</script>
