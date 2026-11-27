<?php
/**
 * View Utama Kartu Observasi, Absen QR, Pembagian Ruangan/Sesi & Broadcast WA
 * Modul Kelola SPMB - Portal BIP
 */
$csrfToken = CSRF::token();
$selJenjang = $filters['jenjang'] ?? '';
$selRuangan = $filters['ruangan_id'] ?? '';
$selStatus  = $filters['status_absen'] ?? '';
$search     = $filters['search'] ?? '';
$canKartuView      = $canKartuView ?? true;
$canKartuScan      = $canKartuScan ?? true;
$canKartuManage    = $canKartuManage ?? true;
$canKartuBroadcast = $canKartuBroadcast ?? true;
$canKartuCetak     = $canKartuCetak ?? true;
$scanAttendedItems = $scanAttendedItems ?? [];
$cetakQuery = http_build_query(array_filter([
    'jenjang'    => $selJenjang,
    'ruangan_id' => $selRuangan,
    'search'     => $search,
]));
?>
<?php if ($activeTab === 'scan' && $canKartuScan): ?>
    <!-- ══════════ MODE FULLSCREEN: SCAN QR ABSENSI (HANYA DROPDOWN SESI, KAMERA & LIST ABSEN) ══════════ -->
    <?php
    $backUrl = $canKartuView ? url('kelola-spmb/kartu-observasi?tab=peserta') : url('kelola-spmb');
    $attendedJson = json_encode(array_map(function ($it) {
        return [
            'id'            => (int)($it['id'] ?? 0),
            'nama_lengkap'  => (string)($it['nama_lengkap'] ?? ''),
            'nomor_peserta' => (string)($it['nomor_peserta'] ?? ''),
            'jenjang'       => (string)($it['jenjang'] ?? ''),
            'ruangan_id'    => !empty($it['ruangan_id']) ? (int)$it['ruangan_id'] : 0,
            'urutan_sesi'   => !empty($it['urutan_sesi']) ? (int)$it['urutan_sesi'] : null,
            'nama_ruangan'  => (string)($it['nama_ruangan'] ?? ''),
            'sesi_nama'     => (string)($it['sesi_nama'] ?? ''),
            'jam_mulai'     => (string)($it['jam_mulai'] ?? ''),
            'jam_selesai'   => (string)($it['jam_selesai'] ?? ''),
            'waktu_absen'   => (string)($it['waktu_absen'] ?? ''),
        ];
    }, $scanAttendedItems), JSON_UNESCAPED_UNICODE);
    ?>
    <style>
        html, body { overflow: hidden !important; height: 100% !important; }
        #qr-reader { width: 100% !important; height: 100% !important; border: none !important; }
        #qr-reader > div { width: 100% !important; height: 100% !important; }
        #qr-reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 0.75rem; }
        #qr-shaded-region { display: none !important; }
        @keyframes qrModalPop {
            0% { opacity: 0; transform: scale(0.82); }
            65% { opacity: 1; transform: scale(1.03); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes qrCircleDraw {
            0% { stroke-dashoffset: 166; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes qrCheckDraw {
            0% { stroke-dashoffset: 48; }
            100% { stroke-dashoffset: 0; }
        }
        .qr-modal-card { animation: qrModalPop 0.28s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        .qr-check-circle { stroke-dasharray: 166; stroke-dashoffset: 166; animation: qrCircleDraw 0.45s ease-out forwards; }
        .qr-check-tick { stroke-dasharray: 48; stroke-dashoffset: 48; animation: qrCheckDraw 0.3s 0.25s ease-out forwards; }
    </style>

    <div id="fullscreenQrWrap" class="fixed inset-0 z-[9999] bg-slate-950 text-white flex flex-col overflow-hidden">
        <!-- Top Bar: Dropdown Sesi + Kontrol Kamera & Kembali -->
        <div class="shrink-0 px-3 sm:px-5 py-3 bg-slate-900 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex items-center gap-2.5 flex-1 min-w-[240px]">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-extrabold shrink-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    SCAN QR
                </span>
                <select id="scanExpectedRuangan" onchange="onScanSesiChange()"
                        class="flex-1 max-w-xl px-3.5 py-2.5 rounded-xl border border-slate-700 bg-slate-800 text-white text-xs sm:text-sm font-bold outline-none focus:border-emerald-500">
                    <option value="">Semua Sesi (Otomatis Validasi Jadwal Peserta)</option>
                    <?php foreach ($ruanganList as $rg): ?>
                        <option value="<?= (int)$rg['id'] ?>" <?= (string)$selRuangan === (string)$rg['id'] ? 'selected' : '' ?>>
                            <?= e($rg['nama_ruangan']) ?> — <?= e($rg['sesi_nama']) ?> (<?= date('d/m', strtotime($rg['tanggal'])) ?> <?= e($rg['jam_mulai']) ?>–<?= e($rg['jam_selesai']) ?>)<?= !empty($rg['jenjang']) ? ' [' . e($rg['jenjang']) . ']' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" id="btnToggleCam" onclick="toggleQrScanner()"
                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer transition-colors">
                    Mulai Kamera
                </button>
                <button type="button" id="btnSwitchCam" onclick="switchQrCamera()" title="Ganti Kamera Depan/Belakang"
                        class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-bold cursor-pointer transition-colors">
                    🔄 Kamera
                </button>
                <button type="button" onclick="toggleBrowserFullscreen()" title="Layar Penuh Browser"
                        class="hidden sm:inline-flex px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-bold cursor-pointer transition-colors">
                    ⛶ Full
                </button>
                <a href="<?= $backUrl ?>" data-no-pjax onclick="stopQrScannerBeforeLeave()"
                   class="px-3.5 py-2 rounded-xl bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold transition-colors">
                    ✕ Tutup
                </a>
            </div>
        </div>

        <!-- Main Fullscreen Area: Kamera (Kiri/Atas) & List Absen (Kanan/Bawah) -->
        <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-12 gap-3 p-3 sm:p-4 overflow-hidden">
            <!-- Panel Kamera -->
            <div class="lg:col-span-7 flex flex-col gap-3 min-h-0 h-full">
                <div class="relative flex-1 min-h-[260px] rounded-2xl overflow-hidden bg-black border border-slate-800 flex items-center justify-center">
                    <div id="qr-reader" class="w-full h-full flex items-center justify-center text-xs text-slate-400">
                        Menyiapkan kamera scanner QR...
                    </div>
                </div>

                <!-- Notifikasi Hasil Scan Terakhir -->
                <div id="scanResultBox" class="shrink-0 px-4 py-3 rounded-2xl border border-slate-800 bg-slate-900 text-center text-xs sm:text-sm text-slate-400">
                    Arahkan kamera ke QR Code pada Kartu Observasi peserta...
                </div>

                <!-- Input Barcode USB / Manual (Ringkas) -->
                <form onsubmit="submitManualScan(event)" class="shrink-0 flex gap-2">
                    <input type="text" id="manualQrInput" placeholder="Atau scan barcode USB / ketik No. Peserta lalu Enter..."
                           class="flex-1 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-900 focus:bg-slate-800 text-white text-xs sm:text-sm font-mono outline-none focus:border-emerald-500">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold cursor-pointer">
                        Absen
                    </button>
                </form>
            </div>

            <!-- Panel List Absen -->
            <div class="lg:col-span-5 flex flex-col min-h-0 h-full bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden">
                <div class="shrink-0 px-4 py-3 border-b border-slate-800 flex items-center justify-between gap-2 bg-slate-900/90">
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-extrabold text-white truncate">List Absen Hadir</h3>
                        <p id="scanListSubtitle" class="text-[11px] text-slate-400 truncate">Semua sesi aktif</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span id="scanListCountBadge" class="px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-extrabold">
                            0 Hadir
                        </span>
                        <button type="button" id="btnResetSesiAbsen" onclick="resetAbsenSesiIni()" title="Reset kehadiran pada sesi yang dipilih"
                                class="px-2.5 py-1 rounded-lg bg-rose-500/15 hover:bg-rose-600 border border-rose-500/40 text-rose-300 hover:text-white text-[11px] font-bold cursor-pointer transition-colors">
                            Reset Sesi
                        </button>
                    </div>
                </div>
                <div id="scanHistoryList" class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-800/80 p-2"></div>
            </div>
        </div>
    </div>

    <!-- Modal Notifikasi Hasil Scan QR (Animasi Centang + Bunyi Tit) -->
    <div id="qrScanNotifModal" class="fixed inset-0 z-[10000] hidden items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs">
        <div id="qrScanNotifCard" class="qr-modal-card bg-white text-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border-4 border-emerald-400 text-center space-y-3.5">
            <div id="qrScanNotifIcon" class="flex justify-center"></div>
            <div id="qrScanNotifBody" class="space-y-2"></div>
            <div id="qrScanNotifActions" class="pt-2 flex items-center justify-center gap-2"></div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
    let html5QrCode = null;
    let qrScanning = false;
    let currentFacingMode = 'environment';
    let lastScannedToken = '';
    let lastScanTime = 0;
    let scanNotifTimer = null;
    let scanAudioCtx = null;
    let scanAttendedList = <?= $attendedJson ?: '[]' ?>;

    function unlockScanAudio() {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            if (!scanAudioCtx) scanAudioCtx = new Ctx();
            if (scanAudioCtx.state === 'suspended') scanAudioCtx.resume();
        } catch (e) {}
    }

    // Bunyi "TIT" menggunakan Web Audio API (tanpa file mp3 eksternal, bekerja offline)
    function playScanBeep(type = 'success') {
        try {
            unlockScanAudio();
            if (!scanAudioCtx) return;
            const now = scanAudioCtx.currentTime;

            const beep = (freq, startOffset, duration, vol = 0.35, wave = 'sine') => {
                const osc = scanAudioCtx.createOscillator();
                const gain = scanAudioCtx.createGain();
                osc.type = wave;
                osc.frequency.setValueAtTime(freq, now + startOffset);
                gain.gain.setValueAtTime(0.001, now + startOffset);
                gain.gain.exponentialRampToValueAtTime(vol, now + startOffset + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.001, now + startOffset + duration);
                osc.connect(gain);
                gain.connect(scanAudioCtx.destination);
                osc.start(now + startOffset);
                osc.stop(now + startOffset + duration + 0.02);
            };

            if (type === 'success') {
                // Bunyi "TIT!" nyaring & bersih
                beep(1560, 0, 0.18, 0.45, 'sine');
                beep(2090, 0.06, 0.14, 0.25, 'triangle');
            } else if (type === 'already') {
                // Bunyi "tit-tit" ganda
                beep(1040, 0, 0.10, 0.35, 'sine');
                beep(1040, 0.14, 0.10, 0.35, 'sine');
            } else {
                // Bunyi peringatan rendah untuk salah sesi / tidak dikenali
                beep(340, 0, 0.16, 0.4, 'sawtooth');
                beep(240, 0.18, 0.22, 0.4, 'sawtooth');
            }
        } catch (e) {}
    }

    function closeQrScanModal() {
        if (scanNotifTimer) {
            clearTimeout(scanNotifTimer);
            scanNotifTimer = null;
        }
        const m = document.getElementById('qrScanNotifModal');
        if (m) {
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
    }

    function showQrScanModal({ type = 'success', badge = '', title = '', meta = '', detail = '', actionsHtml = '', autoCloseMs = 2200 }) {
        closeQrScanModal();
        const modal = document.getElementById('qrScanNotifModal');
        const card = document.getElementById('qrScanNotifCard');
        const iconEl = document.getElementById('qrScanNotifIcon');
        const bodyEl = document.getElementById('qrScanNotifBody');
        const actEl = document.getElementById('qrScanNotifActions');
        if (!modal || !card || !iconEl || !bodyEl || !actEl) return;

        // Re-trigger pop animation
        card.classList.remove('qr-modal-card');
        void card.offsetWidth;
        card.classList.add('qr-modal-card');

        if (type === 'success') {
            card.className = 'qr-modal-card bg-white text-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border-4 border-emerald-400 text-center space-y-3.5';
            iconEl.innerHTML = `<div class="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center">
                <svg class="w-16 h-16" viewBox="0 0 52 52">
                    <circle class="qr-check-circle" cx="26" cy="26" r="24" fill="none" stroke="#10b981" stroke-width="4"/>
                    <path class="qr-check-tick" fill="none" stroke="#10b981" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" d="M14 27l8 8 16-16"/>
                </svg>
            </div>`;
        } else if (type === 'already') {
            card.className = 'qr-modal-card bg-white text-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border-4 border-amber-400 text-center space-y-3.5';
            iconEl.innerHTML = `<div class="w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center">
                <svg class="w-16 h-16" viewBox="0 0 52 52">
                    <circle class="qr-check-circle" cx="26" cy="26" r="24" fill="none" stroke="#f59e0b" stroke-width="4"/>
                    <path class="qr-check-tick" fill="none" stroke="#f59e0b" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" d="M14 27l8 8 16-16"/>
                </svg>
            </div>`;
        } else if (type === 'warning') {
            card.className = 'qr-modal-card bg-white text-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border-4 border-amber-500 text-center space-y-3.5';
            iconEl.innerHTML = `<div class="w-20 h-20 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-4xl font-extrabold">⚠️</div>`;
        } else {
            card.className = 'qr-modal-card bg-white text-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border-4 border-rose-400 text-center space-y-3.5';
            iconEl.innerHTML = `<div class="w-20 h-20 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-4xl font-extrabold">✕</div>`;
        }

        bodyEl.innerHTML = `
            ${badge ? `<div>${badge}</div>` : ''}
            ${title ? `<div class="text-xl font-extrabold text-slate-900 leading-tight">${title}</div>` : ''}
            ${meta ? `<div class="text-xs font-semibold text-slate-500">${meta}</div>` : ''}
            ${detail ? `<div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700">${detail}</div>` : ''}
        `;
        actEl.innerHTML = actionsHtml || `<button type="button" onclick="closeQrScanModal()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold cursor-pointer">OK, Lanjut Scan</button>`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (autoCloseMs > 0) {
            scanNotifTimer = setTimeout(closeQrScanModal, autoCloseMs);
        }
    }

    async function resetAbsenSesiIni() {
        const selEl = document.getElementById('scanExpectedRuangan');
        const selectedRid = selEl ? (selEl.value || '') : '';
        const sesiLabel = selectedRid && selEl
            ? selEl.options[selEl.selectedIndex].text
            : 'SEMUA SESI (seluruh peserta hadir)';

        if (!confirm(`Reset kehadiran untuk:\n${sesiLabel}\n\nSemua status Hadir pada sesi ini akan dikembalikan ke Belum Hadir (untuk reset uji coba). Lanjutkan?`)) {
            return;
        }

        const btn = document.getElementById('btnResetSesiAbsen');
        const origText = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = 'Mereset...'; }

        const fd = new FormData();
        fd.append('csrf_token', '<?= e($csrfToken) ?>');
        fd.append('ajax', '1');
        if (selectedRid) fd.append('ruangan_id', selectedRid);

        try {
            const res = await fetch('<?= url('kelola-spmb/kartu-observasi/reset-absen-sesi') ?>', { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                if (selectedRid) {
                    scanAttendedList = scanAttendedList.filter(it => String(it.ruangan_id || '') !== String(selectedRid));
                } else {
                    scanAttendedList = [];
                }
                renderScanAbsenList();
                const box = document.getElementById('scanResultBox');
                if (box) {
                    box.className = 'shrink-0 px-4 py-3 rounded-2xl border border-slate-700 bg-slate-900 text-center text-xs sm:text-sm text-emerald-300 font-semibold';
                    box.textContent = json.message || 'Kehadiran sesi berhasil direset.';
                }
            } else {
                alert(json.message || 'Gagal mereset kehadiran sesi.');
            }
        } catch (e) {
            alert('Koneksi gagal saat mereset kehadiran sesi.');
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = origText; }
        }
    }

    function escHtmlScan(str) {
        return String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function formatJamScan(dt) {
        if (!dt) return '-';
        const parts = String(dt).split(' ');
        if (parts.length > 1) return parts[1].substring(0, 5);
        return String(dt).substring(0, 5);
    }

    function renderScanAbsenList(highlightId = 0) {
        const listEl = document.getElementById('scanHistoryList');
        const badgeEl = document.getElementById('scanListCountBadge');
        const subEl = document.getElementById('scanListSubtitle');
        const selEl = document.getElementById('scanExpectedRuangan');
        const selectedRid = selEl ? (selEl.value || '') : '';

        if (subEl && selEl) {
            subEl.textContent = selectedRid
                ? selEl.options[selEl.selectedIndex].text
                : 'Semua sesi & ruangan';
        }

        const filtered = selectedRid
            ? scanAttendedList.filter(it => String(it.ruangan_id || '') === String(selectedRid))
            : scanAttendedList;

        if (badgeEl) badgeEl.textContent = `${filtered.length} Hadir`;
        if (!listEl) return;

        if (!filtered.length) {
            listEl.innerHTML = '<div class="py-12 text-center text-xs text-slate-500">Belum ada peserta yang scan absen pada sesi ini.</div>';
            return;
        }

        listEl.innerHTML = filtered.map((d, idx) => {
            const isNew = highlightId && Number(d.id) === Number(highlightId);
            const urutBadge = d.urutan_sesi
                ? `<span class="px-2 py-0.5 rounded-md bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-[11px] font-extrabold">#${String(d.urutan_sesi).padStart(2, '0')}</span>`
                : `<span class="text-slate-500 text-xs font-bold">${idx + 1}</span>`;
            return `<div class="px-3 py-2.5 rounded-xl transition-colors flex items-center justify-between gap-3 ${isNew ? 'bg-emerald-500/20 border border-emerald-400/40' : 'hover:bg-slate-800/60'}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="shrink-0 w-9 text-center">${urutBadge}</div>
                    <div class="min-w-0">
                        <div class="text-xs sm:text-sm font-bold text-white truncate">${escHtmlScan(d.nama_lengkap)}</div>
                        <div class="text-[11px] text-slate-400 truncate">
                            <span class="font-semibold text-emerald-300">${escHtmlScan(d.jenjang)}</span>
                            &bull; ${escHtmlScan(d.nama_ruangan || 'Belum Diatur')}
                            ${d.sesi_nama ? '(' + escHtmlScan(d.sesi_nama) + ')' : ''}
                        </div>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-extrabold">
                        ✓ ${escHtmlScan(formatJamScan(d.waktu_absen))}
                    </span>
                </div>
            </div>`;
        }).join('');
    }

    function onScanSesiChange() {
        renderScanAbsenList();
    }

    function upsertAttendedItem(d) {
        const idx = scanAttendedList.findIndex(x => Number(x.id) === Number(d.id));
        const item = {
            id: Number(d.id || 0),
            nama_lengkap: d.nama_lengkap || '',
            nomor_peserta: d.nomor_peserta || '',
            jenjang: d.jenjang || '',
            ruangan_id: Number(d.ruangan_id || 0),
            urutan_sesi: d.urutan_sesi ? Number(d.urutan_sesi) : null,
            nama_ruangan: d.nama_ruangan || '',
            sesi_nama: d.sesi_nama || '',
            jam_mulai: d.jam_mulai || '',
            jam_selesai: d.jam_selesai || '',
            waktu_absen: d.waktu_absen || ''
        };
        if (idx >= 0) {
            scanAttendedList.splice(idx, 1);
        }
        scanAttendedList.unshift(item);
        renderScanAbsenList(item.id);
    }

    async function processQrCode(code, force = false) {
        const now = Date.now();
        if (!force && code === lastScannedToken && (now - lastScanTime) < 3500) return;
        lastScannedToken = code;
        lastScanTime = now;

        const box = document.getElementById('scanResultBox');
        const expectedRid = document.getElementById('scanExpectedRuangan')?.value || '';

        const fd = new FormData();
        fd.append('csrf_token', '<?= e($csrfToken) ?>');
        fd.append('qr_code', code);
        if (expectedRid) fd.append('expected_ruangan_id', expectedRid);
        if (force) fd.append('force', '1');

        try {
            const res = await fetch('<?= url('kelola-spmb/kartu-observasi/scan') ?>', { method: 'POST', body: fd });
            const json = await res.json();
            if (json.wrong_session && json.data) {
                const d = json.data;
                playScanBeep('warning');
                box.className = 'shrink-0 p-3.5 rounded-2xl border border-amber-500/60 bg-amber-950/70 text-center text-xs sm:text-sm font-bold text-amber-200';
                box.textContent = `⚠️ ${d.nama_lengkap} bukan pada sesi yang dipilih (tidak dimasukkan ke list absen).`;
                showQrScanModal({
                    type: 'warning',
                    badge: '<span class="inline-block px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-extrabold">⚠️ BUKAN SESINYA!</span>',
                    title: escHtmlScan(d.nama_lengkap),
                    meta: `${escHtmlScan(d.nomor_peserta)} • Unit ${escHtmlScan(d.jenjang)}${d.urutan_sesi ? ' • No. Urut #' + String(d.urutan_sesi).padStart(2, '0') : ''}`,
                    detail: `<div class="text-amber-900 font-semibold">${escHtmlScan(json.message)}</div><div class="text-[11px] text-slate-500 mt-1.5">Peserta <strong>tidak dimasukkan ke List Absen</strong>. Jika ingin mengabsenkan peserta ini, pilih sesinya terlebih dahulu pada Dropdown Sesi.</div>`,
                    actionsHtml: `
                        <button type="button" onclick="closeQrScanModal()" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold cursor-pointer">
                            Tutup &amp; Lanjut Scan
                        </button>
                    `,
                    autoCloseMs: 0
                });
            } else if (json.success && json.data) {
                const d = json.data;
                playScanBeep(json.already ? 'already' : 'success');
                const badge = json.already
                    ? '<span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-200 text-xs font-extrabold">SUDAH ABSEN SEBELUMNYA</span>'
                    : '<span class="px-2.5 py-0.5 rounded-full bg-emerald-500/25 border border-emerald-400/50 text-emerald-200 text-xs font-extrabold">✓ BERHASIL ABSEN HADIR</span>';
                box.className = 'shrink-0 p-3.5 rounded-2xl border ' + (json.already ? 'border-amber-500/50 bg-amber-950/60' : 'border-emerald-500/60 bg-emerald-950/70') + ' text-left space-y-1';
                box.innerHTML = `
                    <div class="flex items-center justify-between gap-2">${badge}<span class="text-xs font-mono text-slate-300">${escHtmlScan(d.waktu_absen || '')}</span></div>
                    <div class="text-sm sm:text-base font-extrabold text-white">${escHtmlScan(d.nama_lengkap)} <span class="text-xs font-semibold text-emerald-300">(${escHtmlScan(d.jenjang)})</span></div>
                    <div class="text-xs text-slate-300">${d.urutan_sesi ? 'No. Urut <strong>#' + String(d.urutan_sesi).padStart(2, '0') + '</strong> &bull; ' : ''}${escHtmlScan(d.nama_ruangan || 'Belum Diatur')} ${d.sesi_nama ? '(' + escHtmlScan(d.sesi_nama) + ' ' + escHtmlScan(d.jam_mulai) + '-' + escHtmlScan(d.jam_selesai) + ')' : ''}</div>
                `;
                upsertAttendedItem(d);

                const modalBadge = json.already
                    ? '<span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold">SUDAH ABSEN SEBELUMNYA</span>'
                    : '<span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold">✓ BERHASIL ABSEN HADIR</span>';
                showQrScanModal({
                    type: json.already ? 'already' : 'success',
                    badge: modalBadge,
                    title: escHtmlScan(d.nama_lengkap),
                    meta: `${escHtmlScan(d.nomor_peserta)} • Unit ${escHtmlScan(d.jenjang)}${d.urutan_sesi ? ' • No. Urut #' + String(d.urutan_sesi).padStart(2, '0') : ''}`,
                    detail: `<div class="font-bold text-indigo-700">${escHtmlScan(d.nama_ruangan || 'Belum Diatur')} ${d.sesi_nama ? '— ' + escHtmlScan(d.sesi_nama) + ' (' + escHtmlScan(d.jam_mulai) + '-' + escHtmlScan(d.jam_selesai) + ')' : ''}</div><div class="text-[11px] text-slate-500 mt-1">Waktu Absen: <strong>${escHtmlScan(d.waktu_absen || '-')}</strong></div>`,
                    autoCloseMs: 2200
                });
            } else {
                playScanBeep('error');
                box.className = 'shrink-0 p-3.5 rounded-2xl border border-rose-500/60 bg-rose-950/70 text-center text-xs sm:text-sm font-bold text-rose-200';
                box.textContent = json.message || 'QR tidak dikenali.';
                showQrScanModal({
                    type: 'error',
                    badge: '<span class="inline-block px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-extrabold">QR TIDAK DIKENALI</span>',
                    title: 'Absen Gagal',
                    detail: escHtmlScan(json.message || 'QR Code tidak ditemukan pada data peserta.'),
                    actionsHtml: `<button type="button" onclick="closeQrScanModal()" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold cursor-pointer">Tutup</button>`,
                    autoCloseMs: 2500
                });
            }
        } catch (e) {
            box.className = 'shrink-0 p-3.5 rounded-2xl border border-rose-500/60 bg-rose-950/70 text-center text-xs sm:text-sm font-bold text-rose-200';
            box.textContent = 'Gagal memproses scan QR.';
        }
    }

    function submitManualScan(e) {
        e.preventDefault();
        const inp = document.getElementById('manualQrInput');
        if (!inp || !inp.value.trim()) return;
        processQrCode(inp.value.trim());
        inp.value = '';
        inp.focus();
    }

    async function startQrCamera() {
        const btn = document.getElementById('btnToggleCam');
        const reader = document.getElementById('qr-reader');
        if (typeof Html5Qrcode === 'undefined') {
            if (reader) reader.innerHTML = '<div class="p-4 text-center text-rose-300 text-xs">Library kamera QR belum termuat. Periksa koneksi internet.</div>';
            return;
        }
        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode('qr-reader');
            }
            if (qrScanning) {
                await html5QrCode.stop();
                qrScanning = false;
            }
            await html5QrCode.start(
                { facingMode: currentFacingMode },
                { fps: 12 },
                (decodedText) => processQrCode(decodedText)
            );
            qrScanning = true;
            if (btn) {
                btn.textContent = 'Stop Kamera';
                btn.className = 'px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold cursor-pointer transition-colors';
            }
        } catch (err) {
            qrScanning = false;
            if (btn) {
                btn.textContent = 'Mulai Kamera';
                btn.className = 'px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer transition-colors';
            }
            if (reader) {
                reader.innerHTML = '<div class="p-6 text-center space-y-2"><div class="text-xs text-slate-300">Klik tombol <strong>Mulai Kamera</strong> untuk mengaktifkan scanner QR.</div></div>';
            }
        }
    }

    async function toggleQrScanner() {
        const btn = document.getElementById('btnToggleCam');
        if (qrScanning && html5QrCode) {
            try { await html5QrCode.stop(); } catch (e) {}
            qrScanning = false;
            if (btn) {
                btn.textContent = 'Mulai Kamera';
                btn.className = 'px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer transition-colors';
            }
            return;
        }
        await startQrCamera();
    }

    async function switchQrCamera() {
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        if (qrScanning) {
            await startQrCamera();
        }
    }

    function toggleBrowserFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.().catch(() => {});
        } else {
            document.exitFullscreen?.().catch(() => {});
        }
    }

    function stopQrScannerBeforeLeave() {
        if (qrScanning && html5QrCode) {
            try { html5QrCode.stop(); } catch (e) {}
        }
        if (document.fullscreenElement) {
            document.exitFullscreen?.().catch(() => {});
        }
    }

    function initFullscreenScanMode() {
        renderScanAbsenList();
        startQrCamera();
    }

    document.addEventListener('pointerdown', unlockScanAudio, { once: true });
    window.addEventListener('beforeunload', stopQrScannerBeforeLeave);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFullscreenScanMode);
    } else {
        setTimeout(initFullscreenScanMode, 120);
    }
    </script>
<?php else: ?>
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Kartu &amp; Absen QR Observasi
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs font-semibold text-slate-500"><?= e($activeGroup['nama_group'] ?? 'SPMB Aktif') ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Kartu Observasi, Ruangan Sesi &amp; Scan QR</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Menampilkan peserta yang <strong>sudah mengisi link Observasi Awal</strong>. Cetak/download PDF kartu ukuran KTP, scan QR absen, bagi ruangan, &amp; broadcast link ke orang tua.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <?php if ($canKartuManage): ?>
                <form method="POST" action="<?= url('kelola-spmb/kartu-observasi/sync') ?>" class="inline">
                    <?= CSRF::field() ?>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-bold cursor-pointer">
                        Sinkron Peserta
                    </button>
                </form>
                <form method="POST" action="<?= url('kelola-spmb/kartu-observasi/bagi-otomatis') ?>" class="inline" onsubmit="return confirm('Bagi peserta yang belum punya ruangan ke ruangan tersedia sesuai kapasitas?')">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="jenjang" value="<?= e($selJenjang) ?>">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold shadow-xs cursor-pointer">
                        Bagi Ruangan Otomatis
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($canKartuBroadcast): ?>
                <button type="button" onclick="openBroadcastJadwalModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-xs cursor-pointer">
                    Broadcast Link &amp; Jadwal WA
                </button>
            <?php endif; ?>
            <?php if ($canKartuCetak): ?>
                <a href="<?= url('kelola-spmb/kartu-observasi/cetak-jadwal' . ($cetakQuery ? '?' . $cetakQuery : '')) ?>" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-xs">
                    Cetak PDF Jadwal Sesi
                </a>
                <a href="<?= url('kelola-spmb/kartu-observasi/cetak' . ($cetakQuery ? '?' . $cetakQuery : '')) ?>" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-xs">
                    Cetak Kartu PDF
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase">Total Peserta</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['total'] ?? 0) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Terjadwal ruangan: <strong><?= number_format($stats['terjadwal'] ?? 0) ?></strong></p>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-emerald-200 shadow-xs">
            <span class="text-xs font-bold text-emerald-600 uppercase">Hadir (Sudah Scan)</span>
            <div id="stat-hadir-count" class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['hadir'] ?? 0) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Tercatat via QR / manual</p>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-amber-200 shadow-xs">
            <span class="text-xs font-bold text-amber-600 uppercase">Belum Hadir</span>
            <div id="stat-belum-count" class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-1"><?= number_format($stats['belum_hadir'] ?? 0) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Menunggu kedatangan</p>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-sky-200 shadow-xs">
            <span class="text-xs font-bold text-sky-600 uppercase">Ruangan &amp; Sesi</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-sky-600 mt-1"><?= count($ruanganList) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Sesi observasi aktif</p>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-xs">
        <?php if ($canKartuView): ?>
            <a href="<?= url('kelola-spmb/kartu-observasi?tab=peserta') ?>"
               class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all <?= $activeTab === 'peserta' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                Daftar Peserta &amp; Kartu QR
            </a>
        <?php endif; ?>
        <?php if ($canKartuScan): ?>
            <a href="<?= url('kelola-spmb/kartu-observasi?tab=scan') ?>" data-no-pjax
               class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-200">
                ⛶ Scan QR Absensi (Full Layar)
            </a>
        <?php endif; ?>
        <?php if ($canKartuManage): ?>
            <a href="<?= url('kelola-spmb/kartu-observasi?tab=ruangan') ?>"
               class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all <?= $activeTab === 'ruangan' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                Atur Ruangan &amp; Jam Sesi (<?= count($ruanganList) ?>)
            </a>
        <?php endif; ?>
        <?php if ($canKartuView): ?>
            <a href="<?= url('kelola-spmb/kartu-observasi?tab=laporan') ?>"
               class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all <?= $activeTab === 'laporan' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                Laporan Absen QR (<?= number_format($stats['hadir'] ?? 0) ?>/<?= number_format($stats['total'] ?? 0) ?>)
            </a>
        <?php endif; ?>
    </div>

    <?php if ($activeTab === 'peserta'): ?>
        <!-- Filter Bar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="<?= url('kelola-spmb/kartu-observasi') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <input type="hidden" name="tab" value="peserta">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama, no. pendaftaran, QR..."
                       class="lg:col-span-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm outline-none focus:border-primary-500">
                <select name="jenjang" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Jenjang</option>
                    <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $j): ?>
                        <option value="<?= $j ?>" <?= $selJenjang === $j ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="ruangan_id" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Ruangan</option>
                    <option value="unassigned" <?= $selRuangan === 'unassigned' ? 'selected' : '' ?>>Belum Dapat Ruangan</option>
                    <?php foreach ($ruanganList as $rg): ?>
                        <option value="<?= (int)$rg['id'] ?>" <?= (string)$selRuangan === (string)$rg['id'] ? 'selected' : '' ?>>
                            <?= e($rg['nama_ruangan']) ?> (<?= e($rg['sesi_nama']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status_absen" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Status Absen</option>
                    <option value="Hadir" <?= $selStatus === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                    <option value="Belum Hadir" <?= $selStatus === 'Belum Hadir' ? 'selected' : '' ?>>Belum Hadir</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold cursor-pointer">
                    Filter
                </button>
            </form>
        </div>

        <!-- Tabel Peserta -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Calon Siswa &amp; No. Peserta</th>
                            <th class="py-3.5 px-4">Jenjang</th>
                            <th class="py-3.5 px-4 min-w-[230px]">Ruangan &amp; Jam Sesi</th>
                            <th class="py-3.5 px-4 text-center">Status Absen</th>
                            <th class="py-3.5 px-4 text-center">Broadcast WA</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Belum ada peserta observasi. Klik <strong>Sinkron Peserta</strong> untuk menarik data calon siswa.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $idx => $it): ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-400"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900"><?= e($it['nama_lengkap']) ?></div>
                                        <div class="text-xs font-mono text-slate-500">
                                            <?= e($it['nomor_peserta']) ?> &bull; <span class="text-indigo-600"><?= e($it['qr_token']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs"><?= e($it['jenjang']) ?></span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($canKartuManage): ?>
                                            <div class="flex items-center gap-1.5">
                                                <input type="number" min="1" id="urut_sesi_<?= (int)$it['id'] ?>" value="<?= !empty($it['urutan_sesi']) ? (int)$it['urutan_sesi'] : '' ?>"
                                                       onchange="assignRuanganManual(<?= (int)$it['id'] ?>, document.getElementById('ruang_sel_<?= (int)$it['id'] ?>').value, this.value)"
                                                       placeholder="#" title="Nomor Urut Sesi"
                                                       class="w-14 px-2 py-2 rounded-xl border border-slate-200 bg-white text-xs font-extrabold text-center text-indigo-700 outline-none focus:border-indigo-500">
                                                <select id="ruang_sel_<?= (int)$it['id'] ?>" onchange="assignRuanganManual(<?= (int)$it['id'] ?>, this.value, '')"
                                                        class="flex-1 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs font-semibold text-slate-800 outline-none">
                                                    <option value="">-- Belum Diatur --</option>
                                                    <?php foreach ($ruanganList as $rg): ?>
                                                        <option value="<?= (int)$rg['id'] ?>" <?= (int)$it['ruangan_id'] === (int)$rg['id'] ? 'selected' : '' ?>>
                                                            <?= e($rg['nama_ruangan']) ?> | <?= e($rg['sesi_nama']) ?> (<?= date('d/m', strtotime($rg['tanggal'])) ?> <?= e($rg['jam_mulai']) ?>-<?= e($rg['jam_selesai']) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-xs font-semibold text-slate-700">
                                                <?php if (!empty($it['nama_ruangan'])): ?>
                                                    <?= !empty($it['urutan_sesi']) ? '<span class="text-indigo-700 font-extrabold">#' . str_pad((string)(int)$it['urutan_sesi'], 2, '0', STR_PAD_LEFT) . '</span> ' : '' ?>
                                                    <?= e($it['nama_ruangan']) ?> | <?= e($it['sesi_nama'] ?? '') ?>
                                                <?php else: ?>
                                                    <span class="text-slate-400">Belum Diatur</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ($it['status_absen'] === 'Hadir'): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                ✓ Hadir (<?= $it['waktu_absen'] ? date('H:i', strtotime($it['waktu_absen'])) : '' ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Hadir
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if (!empty($it['wa_jadwal_sent_at'])): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="<?= e($it['wa_jadwal_status'] ?? '') ?>">
                                                Terkirim
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400">Belum dikirim</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                                        <?php if ($canKartuCetak): ?>
                                            <a href="<?= url('kelola-spmb/kartu-observasi/cetak?id=' . (int)$it['id']) ?>" target="_blank"
                                               class="inline-flex items-center px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200 text-xs font-bold transition-colors">
                                                Kartu
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" onclick="copyKartuLink('<?= e($it['kartu_url']) ?>', this)"
                                                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 text-xs font-bold cursor-pointer transition-colors">
                                            Salin Link
                                        </button>
                                        <?php if ($canKartuBroadcast): ?>
                                            <button type="button" onclick="sendWaJadwalSingle(<?= (int)$it['id'] ?>, this)"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 text-xs font-bold cursor-pointer transition-colors">
                                                Kirim WA
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($it['status_absen'] === 'Hadir' && ($canKartuScan || $canKartuManage)): ?>
                                            <form method="POST" action="<?= url('kelola-spmb/kartu-observasi/reset-absen/' . (int)$it['id']) ?>" class="inline" onsubmit="return confirm('Reset status absen ke Belum Hadir?')">
                                                <?= CSRF::field() ?>
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 text-xs font-bold cursor-pointer">
                                                    Reset
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($activeTab === 'ruangan' && $canKartuManage): ?>
        <!-- TAB 3: ATUR RUANGAN & JAM SESI -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
                <h3 class="text-base font-extrabold text-slate-900">Tambah / Edit Ruangan &amp; Sesi</h3>
                <form method="POST" action="<?= url('kelola-spmb/kartu-observasi/ruangan/store') ?>" class="space-y-3.5 text-sm">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="id" id="ruangan_id" value="">

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Ruangan *</label>
                        <input type="text" name="nama_ruangan" id="ruangan_nama" required placeholder="Contoh: Ruang Observasi 1 / Kelas 1A"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jenjang</label>
                            <select name="jenjang" id="ruangan_jenjang" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                                <option value="">Semua Jenjang</option>
                                <option value="PAUD">PAUD</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA">SMA</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kapasitas *</label>
                            <input type="number" name="kapasitas" id="ruangan_kapasitas" min="1" value="15" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Sesi *</label>
                            <input type="text" name="sesi_nama" id="ruangan_sesi" value="Sesi 1" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal *</label>
                            <input type="date" name="tanggal" id="ruangan_tanggal" value="<?= date('Y-m-d') ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jam Mulai *</label>
                            <input type="time" name="jam_mulai" id="ruangan_jam_mulai" value="08:00" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jam Selesai *</label>
                            <input type="time" name="jam_selesai" id="ruangan_jam_selesai" value="09:30" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pengawas / Observer (Opsional)</label>
                        <input type="text" name="pengawas" id="ruangan_pengawas" placeholder="Nama Ustadz/Ustadzah Pengawas"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="resetRuanganForm()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 cursor-pointer">Reset</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold cursor-pointer">Simpan Ruangan</button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-slate-900">Daftar Ruangan &amp; Sesi Observasi</h3>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700"><?= count($ruanganList) ?> Ruangan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Ruangan &amp; Pengawas</th>
                                <th class="px-4 py-3">Jenjang</th>
                                <th class="px-4 py-3">Tanggal &amp; Sesi</th>
                                <th class="px-4 py-3 text-center">Terisi / Kapasitas</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($ruanganList)): ?>
                                <tr><td colspan="5" class="py-10 text-center text-slate-400">Belum ada ruangan. Tambahkan ruangan di form sebelah kiri.</td></tr>
                            <?php else: ?>
                                <?php foreach ($ruanganList as $rg): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-4 py-3.5">
                                            <div class="font-bold text-slate-900"><?= e($rg['nama_ruangan']) ?></div>
                                            <div class="text-xs text-slate-400">Pengawas: <?= e($rg['pengawas'] ?: '-') ?></div>
                                        </td>
                                        <td class="px-4 py-3.5"><span class="px-2 py-0.5 rounded-lg bg-slate-100 text-xs font-bold"><?= e($rg['jenjang'] ?: 'Semua') ?></span></td>
                                        <td class="px-4 py-3.5 text-xs">
                                            <div class="font-bold text-slate-800"><?= date('d/m/Y', strtotime($rg['tanggal'])) ?></div>
                                            <div class="text-indigo-700 font-semibold"><?= e($rg['sesi_nama']) ?> (<?= e($rg['jam_mulai']) ?> - <?= e($rg['jam_selesai']) ?>)</div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-extrabold <?= (int)$rg['terisi'] >= (int)$rg['kapasitas'] ? 'text-rose-600' : 'text-emerald-700' ?>">
                                            <?= (int)$rg['terisi'] ?> / <?= (int)$rg['kapasitas'] ?>
                                        </td>
                                        <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
                                            <a href="<?= url('kelola-spmb/kartu-observasi/cetak-jadwal?ruangan_id=' . (int)$rg['id']) ?>" target="_blank" class="inline-flex px-2.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white text-xs font-bold">Jadwal PDF</a>
                                            <a href="<?= url('kelola-spmb/kartu-observasi/cetak?ruangan_id=' . (int)$rg['id']) ?>" target="_blank" class="inline-flex px-2.5 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white text-xs font-bold">Kartu</a>
                                            <form method="POST" action="<?= url('kelola-spmb/kartu-observasi/reset-absen-sesi') ?>" class="inline" onsubmit="return confirm('Reset semua status kehadiran pada sesi <?= e(addslashes($rg['nama_ruangan'] . ' - ' . $rg['sesi_nama'])) ?> menjadi Belum Hadir?')">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="ruangan_id" value="<?= (int)$rg['id'] ?>">
                                                <input type="hidden" name="return_tab" value="ruangan">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-white text-xs font-bold cursor-pointer">Reset Absen</button>
                                            </form>
                                            <button type="button" onclick='editRuangan(<?= json_encode($rg) ?>)' class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold cursor-pointer">Edit</button>
                                            <form method="POST" action="<?= url('kelola-spmb/kartu-observasi/ruangan/delete/' . (int)$rg['id']) ?>" class="inline" onsubmit="return confirm('Hapus ruangan ini?')">
                                                <?= CSRF::field() ?>
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold cursor-pointer">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($activeTab === 'laporan'): ?>
        <!-- TAB 4: LAPORAN ABSEN QR PER SESI & PER RUANGAN (SEMUA PESERTA: HADIR / TIDAK + JAM) -->
        <?php
        $lapGroups = $laporanTidakHadir['groups'] ?? [];
        $lapSum = $laporanTidakHadir['summary'] ?? ['total_peserta' => 0, 'total_hadir' => 0, 'total_tidak_hadir' => 0];
        $lapQs = http_build_query(array_filter([
            'jenjang'      => $selJenjang,
            'ruangan_id'   => $selRuangan,
            'status_absen' => $selStatus,
            'search'       => $search,
        ]));
        $allowedJenjangOpts = SpmbModel::getObservasiSiswaAllowedUnits();
        ?>
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <form method="GET" action="<?= url('kelola-spmb/kartu-observasi') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="laporan">
                <select name="jenjang" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Jenjang</option>
                    <?php foreach ($allowedJenjangOpts as $j): ?>
                        <option value="<?= $j ?>" <?= $selJenjang === $j ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="ruangan_id" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Sesi &amp; Ruangan</option>
                    <option value="unassigned" <?= $selRuangan === 'unassigned' ? 'selected' : '' ?>>Belum Masuk Ruangan</option>
                    <?php foreach ($ruanganList as $rg): ?>
                        <option value="<?= (int)$rg['id'] ?>" <?= (string)$selRuangan === (string)$rg['id'] ? 'selected' : '' ?>>
                            <?= e($rg['nama_ruangan']) ?> | <?= e($rg['sesi_nama']) ?> (<?= date('d/m', strtotime($rg['tanggal'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status_absen" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Status</option>
                    <option value="Hadir" <?= $selStatus === 'Hadir' ? 'selected' : '' ?>>Hadir (Ya)</option>
                    <option value="Belum Hadir" <?= $selStatus === 'Belum Hadir' ? 'selected' : '' ?>>Tidak Hadir</option>
                </select>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama / no. peserta..."
                       class="px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm outline-none">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold cursor-pointer">
                    Filter Laporan
                </button>
            </form>
            <?php if ($canKartuCetak): ?>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="<?= url('kelola-spmb/kartu-observasi/laporan-export' . ($lapQs ? '?' . $lapQs : '')) ?>"
                       class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                        Export Excel
                    </a>
                    <a href="<?= url('kelola-spmb/kartu-observasi/laporan-cetak' . ($lapQs ? '?' . $lapQs : '')) ?>" target="_blank"
                       class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold">
                        Cetak Laporan PDF
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($lapGroups)): ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-sm text-slate-400">
                Tidak ada data peserta untuk filter yang dipilih.
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($lapGroups as $grp): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-extrabold text-slate-900"><?= e($grp['nama_ruangan']) ?></h3>
                                    <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-bold">
                                        <?= e($grp['sesi_nama']) ?><?= !empty($grp['jam_mulai']) ? ' (' . e($grp['jam_mulai']) . ' - ' . e($grp['jam_selesai']) . ')' : '' ?>
                                    </span>
                                    <?php if (!empty($grp['tgl_sesi'])): ?>
                                        <span class="text-xs font-semibold text-slate-500">Tanggal: <?= date('d/m/Y', strtotime($grp['tgl_sesi'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($grp['pengawas'])): ?>
                                    <p class="text-xs text-slate-500 mt-0.5">Pengawas: <strong><?= e($grp['pengawas']) ?></strong></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700">Total: <?= (int)$grp['total_peserta'] ?></span>
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir: <?= (int)$grp['total_hadir'] ?></span>
                                <span class="px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200">Tidak Hadir: <?= (int)$grp['total_tidak_hadir'] ?></span>
                            </div>
                        </div>

                        <?php $grpRows = $grp['all_items'] ?? []; ?>
                        <?php if (empty($grpRows)): ?>
                            <div class="px-5 py-6 text-center text-xs font-semibold text-slate-400">
                                Belum ada peserta pada sesi &amp; ruangan ini.
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                                            <th class="py-2.5 px-4 w-16 text-center">Urut</th>
                                            <th class="py-2.5 px-4">No. Peserta</th>
                                            <th class="py-2.5 px-4">Nama Calon Siswa</th>
                                            <th class="py-2.5 px-4">Jenjang</th>
                                            <th class="py-2.5 px-4">No. WA Orang Tua</th>
                                            <th class="py-2.5 px-4 text-center">Status Hadir</th>
                                            <th class="py-2.5 px-4 text-center">Jam Absen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach ($grpRows as $nIdx => $rowIt):
                                            $isHadirRow = (($rowIt['status_absen'] ?? '') === 'Hadir');
                                            $jamRow = ($isHadirRow && !empty($rowIt['waktu_absen'])) ? date('H:i:s', strtotime($rowIt['waktu_absen'])) : '-';
                                        ?>
                                            <tr class="hover:bg-slate-50/70">
                                                <td class="py-2.5 px-4 text-center font-extrabold text-indigo-700">
                                                    <?= !empty($rowIt['urutan_sesi']) ? str_pad((string)(int)$rowIt['urutan_sesi'], 2, '0', STR_PAD_LEFT) : ($nIdx + 1) ?>
                                                </td>
                                                <td class="py-2.5 px-4 font-mono font-bold text-slate-700"><?= e($rowIt['nomor_peserta']) ?></td>
                                                <td class="py-2.5 px-4 font-bold text-slate-900"><?= e($rowIt['nama_lengkap']) ?></td>
                                                <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded-lg bg-slate-100 text-xs font-bold"><?= e($rowIt['jenjang']) ?></span></td>
                                                <td class="py-2.5 px-4 font-mono text-xs text-slate-600"><?= e($rowIt['target_phone'] ?: '-') ?></td>
                                                <td class="py-2.5 px-4 text-center">
                                                    <?php if ($isHadirRow): ?>
                                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            ✓ Ya (Hadir)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                            ✕ Tidak
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-2.5 px-4 text-center font-mono text-xs <?= $isHadirRow ? 'font-bold text-emerald-700' : 'text-slate-400' ?>">
                                                    <?= e($jamRow) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal Broadcast Jadwal Sesi & Ruangan via WA (Sama Dengan Broadcast Link Observasi) -->
<div id="modalBroadcastJadwal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden my-8">
            <div class="flex items-center justify-between border-b border-slate-100 bg-emerald-50/80 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Broadcast Jadwal Sesi, Ruangan &amp; Kartu QR</h3>
                        <p class="text-xs text-slate-500">Kirim otomatis jadwal sesi, ruangan, dan link kartu QR ke orang tua</p>
                    </div>
                </div>
                <button type="button" onclick="closeBroadcastJadwalModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <!-- Filter Unit, Ruangan & Target -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Filter Jenjang / Unit</label>
                        <select id="bc_jadwal_jenjang" onchange="loadBroadcastJadwalPreview()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="">Semua Unit</option>
                            <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $jOpt): ?>
                                <option value="<?= $jOpt ?>" <?= $selJenjang === $jOpt ? 'selected' : '' ?>><?= $jOpt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Filter Ruangan &amp; Sesi</label>
                        <select id="bc_jadwal_ruangan" onchange="loadBroadcastJadwalPreview()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="">Semua Ruangan</option>
                            <?php foreach ($ruanganList as $rg): ?>
                                <option value="<?= (int)$rg['id'] ?>" <?= (string)$selRuangan === (string)$rg['id'] ? 'selected' : '' ?>>
                                    <?= e($rg['nama_ruangan']) ?> (<?= e($rg['sesi_nama']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Penerima</label>
                        <select id="bc_jadwal_target_mode" onchange="loadBroadcastJadwalPreview()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="belum_wa" selected>Belum Terkirim WA (Lanjut Antrian)</option>
                            <option value="terjadwal_belum_wa">Sudah Dapat Ruangan &amp; Belum WA</option>
                            <option value="semua">Semua Peserta (Kirim Ulang / Reminder)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Batasi Jumlah Kirim</label>
                        <select id="bc_jadwal_limit" onchange="loadBroadcastJadwalPreview()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="0">Kirim ke Semua Target</option>
                            <option value="5">Maksimal 5 Nomor (Aman)</option>
                            <option value="10" selected>Maksimal 10 Nomor</option>
                            <option value="20">Maksimal 20 Nomor</option>
                            <option value="50">Maksimal 50 Nomor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jeda Antar Pesan</label>
                        <select id="bc_jadwal_delay" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="3">3 Detik (Cepat)</option>
                            <option value="5" selected>5 Detik (Disarankan)</option>
                            <option value="8">8 Detik (Sangat Aman)</option>
                        </select>
                    </div>
                </div>

                <!-- Checklist Daftar Siswa Siap Kirim -->
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/70">
                    <div class="px-3 py-2.5 bg-slate-100/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-700">Daftar Siswa Siap Kirim:</span>
                            <span id="bc_jadwal_target_count" class="font-bold text-emerald-700">0 Siswa</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button type="button" onclick="copyAllKartuLinks()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-[11px] font-bold cursor-pointer">
                                Salin List Link
                            </button>
                            <button type="button" onclick="toggleAllBcJadwalTargets(true)" class="px-2 py-1 rounded-lg bg-white border border-slate-300 text-[11px] font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer">
                                Centang Semua
                            </button>
                            <button type="button" onclick="toggleAllBcJadwalTargets(false)" class="px-2 py-1 rounded-lg bg-white border border-slate-300 text-[11px] font-semibold text-slate-700 hover:bg-rose-50 hover:text-rose-700 cursor-pointer">
                                Hapus Centang
                            </button>
                        </div>
                    </div>
                    <div class="p-2 border-b border-slate-200 bg-white">
                        <input type="text" id="bc_jadwal_search" oninput="filterBcJadwalChecklist()" placeholder="Cari nama siswa, ruangan, atau nomor WA..." class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-emerald-500 outline-none">
                    </div>
                    <div id="bc_jadwal_checklist" class="max-h-48 overflow-y-auto divide-y divide-slate-100 bg-white">
                        <div class="p-4 text-center text-xs text-slate-400">Memuat daftar siswa...</div>
                    </div>
                </div>

                <!-- Template Pesan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Template Pesan WhatsApp</label>
                    <textarea id="bc_jadwal_template" rows="5" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-700 font-mono leading-relaxed outline-none focus:border-emerald-500"><?= e($defaultWaTemplate) ?></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Variabel: <code>{nama_siswa}</code>, <code>{no_pendaftaran}</code>, <code>{jenjang}</code>, <code>{tanggal_sesi}</code>, <code>{sesi_jam}</code>, <code>{ruangan}</code>, <code>{pengawas}</code>, <code>{link_kartu}</code></p>
                </div>

                <!-- Live Progress Bar -->
                <div id="bc_jadwal_progress_wrap" class="hidden space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span id="bc_jadwal_status_txt" class="text-slate-700">Menyiapkan...</span>
                        <span id="bc_jadwal_count_txt" class="text-emerald-700 font-bold">0 / 0</span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                        <div id="bc_jadwal_bar" class="h-full bg-emerald-600 rounded-full transition-all duration-300" style="width:0%"></div>
                    </div>
                    <div id="bc_jadwal_log" class="max-h-28 overflow-y-auto divide-y divide-slate-200/70 text-[11px] font-mono bg-slate-50 p-2 rounded-lg"></div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeBroadcastJadwalModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Tutup
                </button>
                <button type="button" id="btnStartBcJadwal" onclick="startBroadcastJadwal()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md cursor-pointer">
                    Mulai Broadcast WA
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function assignRuanganManual(pesertaId, ruanganId, urutanSesi = '') {
    const fd = new FormData();
    fd.append('csrf_token', '<?= e($csrfToken) ?>');
    fd.append('peserta_id', pesertaId);
    fd.append('ruangan_id', ruanganId);
    if (urutanSesi !== '' && urutanSesi !== null && urutanSesi !== undefined) {
        fd.append('urutan_sesi', urutanSesi);
    }
    try {
        const res = await fetch('<?= url('kelola-spmb/kartu-observasi/assign-manual') ?>', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            const urutInp = document.getElementById('urut_sesi_' + pesertaId);
            if (urutInp) urutInp.value = json.urutan_sesi ? json.urutan_sesi : '';
        } else {
            alert(json.message || 'Gagal mengubah ruangan');
        }
    } catch (e) {
        alert('Koneksi gagal saat menyimpan ruangan.');
    }
}

async function sendWaJadwalSingle(pesertaId, btn) {
    if (!confirm('Kirim jadwal sesi, ruangan & link Kartu QR ke WA orang tua peserta ini?')) return;
    const orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
    const fd = new FormData();
    fd.append('csrf_token', '<?= e($csrfToken) ?>');
    fd.append('peserta_id', pesertaId);
    try {
        const res = await fetch('<?= url('kelola-spmb/kartu-observasi/broadcast-send') ?>', { method: 'POST', body: fd });
        const json = await res.json();
        alert(json.success ? ('WA terkirim ke ' + json.phone) : ('Gagal: ' + (json.message || 'Error')));
        if (json.success) location.reload();
    } catch (e) {
        alert('Gagal mengirim WA.');
    } finally {
        btn.disabled = false;
        btn.textContent = orig;
    }
}

function editRuangan(rg) {
    document.getElementById('ruangan_id').value = rg.id || '';
    document.getElementById('ruangan_nama').value = rg.nama_ruangan || '';
    document.getElementById('ruangan_jenjang').value = rg.jenjang || '';
    document.getElementById('ruangan_kapasitas').value = rg.kapasitas || 15;
    document.getElementById('ruangan_sesi').value = rg.sesi_nama || 'Sesi 1';
    document.getElementById('ruangan_tanggal').value = rg.tanggal || '';
    document.getElementById('ruangan_jam_mulai').value = rg.jam_mulai || '08:00';
    document.getElementById('ruangan_jam_selesai').value = rg.jam_selesai || '09:30';
    document.getElementById('ruangan_pengawas').value = rg.pengawas || '';
}

function resetRuanganForm() {
    document.getElementById('ruangan_id').value = '';
    document.getElementById('ruangan_nama').value = '';
    document.getElementById('ruangan_pengawas').value = '';
}

let bcJadwalItemsCache = [];

function openBroadcastJadwalModal() {
    const m = document.getElementById('modalBroadcastJadwal');
    m.classList.remove('hidden');
    loadBroadcastJadwalPreview();
}

function closeBroadcastJadwalModal() {
    const m = document.getElementById('modalBroadcastJadwal');
    m.classList.add('hidden');
}

async function loadBroadcastJadwalPreview() {
    const container = document.getElementById('bc_jadwal_checklist');
    const countEl = document.getElementById('bc_jadwal_target_count');
    if (container) container.innerHTML = '<div class="p-4 text-center text-xs text-slate-400">Memuat daftar siswa...</div>';

    const qs = new URLSearchParams({
        jenjang: document.getElementById('bc_jadwal_jenjang')?.value || '',
        ruangan_id: document.getElementById('bc_jadwal_ruangan')?.value || '',
        target_mode: document.getElementById('bc_jadwal_target_mode')?.value || 'belum_wa',
        limit: document.getElementById('bc_jadwal_limit')?.value || '0',
        search: ''
    });
    try {
        const res = await fetch('<?= url('kelola-spmb/kartu-observasi/broadcast-data') ?>?' + qs.toString());
        const json = await res.json();
        bcJadwalItemsCache = json.items || [];
        renderBcJadwalChecklist();
    } catch (e) {
        if (container) container.innerHTML = '<div class="p-4 text-center text-xs text-rose-500">Gagal memuat daftar peserta.</div>';
        if (countEl) countEl.textContent = '0 Siswa';
    }
}

function renderBcJadwalChecklist() {
    const container = document.getElementById('bc_jadwal_checklist');
    if (!container) return;
    if (!bcJadwalItemsCache.length) {
        container.innerHTML = '<div class="p-4 text-center text-xs text-slate-400">Tidak ada peserta yang sesuai dengan filter ini.</div>';
        updateBcJadwalCount();
        return;
    }
    container.innerHTML = bcJadwalItemsCache.map(it => {
        const ruangTxt = it.nama_ruangan ? `${it.nama_ruangan} (${it.sesi_nama || '-'})` : 'Belum Diatur';
        const waBadge = it.wa_jadwal_sent_at
            ? '<span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">Sudah WA</span>'
            : '<span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-bold">Belum WA</span>';
        const searchStr = `${it.nama_lengkap} ${it.nomor_peserta} ${it.jenjang} ${ruangTxt} ${it.target_phone}`.toLowerCase();
        return `<label class="bc-jadwal-row flex items-center justify-between gap-2 px-3 py-2 hover:bg-slate-50 cursor-pointer" data-search="${searchStr.replace(/"/g, '&quot;')}">
            <div class="flex items-center gap-2.5 min-w-0">
                <input type="checkbox" class="bc-jadwal-cb w-4 h-4 rounded text-emerald-600" value="${it.id}" checked onchange="updateBcJadwalCount()">
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-800 truncate">${it.nama_lengkap} <span class="text-slate-400 font-normal">(${it.jenjang})</span></div>
                    <div class="text-[11px] text-slate-500 truncate">${ruangTxt} &bull; <span class="font-mono">${it.target_phone}</span></div>
                </div>
            </div>
            <div class="shrink-0">${waBadge}</div>
        </label>`;
    }).join('');
    updateBcJadwalCount();
}

function updateBcJadwalCount() {
    const checked = document.querySelectorAll('.bc-jadwal-cb:checked').length;
    const total = document.querySelectorAll('.bc-jadwal-cb').length;
    const countEl = document.getElementById('bc_jadwal_target_count');
    if (countEl) countEl.textContent = `${checked} / ${total} Siswa Dipilih`;
}

function toggleAllBcJadwalTargets(state) {
    document.querySelectorAll('.bc-jadwal-row').forEach(row => {
        if (row.style.display === 'none') return;
        const cb = row.querySelector('.bc-jadwal-cb');
        if (cb) cb.checked = state;
    });
    updateBcJadwalCount();
}

function filterBcJadwalChecklist() {
    const q = (document.getElementById('bc_jadwal_search')?.value || '').trim().toLowerCase();
    document.querySelectorAll('.bc-jadwal-row').forEach(row => {
        const hay = row.getAttribute('data-search') || '';
        row.style.display = (!q || hay.includes(q)) ? '' : 'none';
    });
}

function copyKartuLink(link, btn) {
    navigator.clipboard.writeText(link).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Tersalin ✓';
        setTimeout(() => btn.textContent = orig, 1500);
    });
}

async function copyAllKartuLinks() {
    const selectedIds = new Set(Array.from(document.querySelectorAll('.bc-jadwal-cb:checked')).map(el => String(el.value)));
    const items = bcJadwalItemsCache.filter(it => selectedIds.has(String(it.id)));
    if (!items.length) { alert('Centang minimal 1 siswa untuk disalin.'); return; }
    const text = items.map((it, i) =>
        `${i + 1}. ${it.nama_lengkap} (${it.jenjang})\nRuang: ${it.nama_ruangan || '-'} | ${it.sesi_nama || '-'}\nKartu QR: ${it.kartu_url}`
    ).join('\n\n');
    await navigator.clipboard.writeText(text);
    alert(`Berhasil menyalin ${items.length} link Kartu QR Observasi!`);
}

async function startBroadcastJadwal() {
    const btn = document.getElementById('btnStartBcJadwal');
    const wrap = document.getElementById('bc_jadwal_progress_wrap');
    const txt = document.getElementById('bc_jadwal_status_txt');
    const cnt = document.getElementById('bc_jadwal_count_txt');
    const bar = document.getElementById('bc_jadwal_bar');
    const logEl = document.getElementById('bc_jadwal_log');
    const tpl = document.getElementById('bc_jadwal_template').value;
    const delaySec = Math.max(1, parseInt(document.getElementById('bc_jadwal_delay')?.value || '5', 10));

    const selectedIds = new Set(Array.from(document.querySelectorAll('.bc-jadwal-cb:checked')).map(el => String(el.value)));
    const items = bcJadwalItemsCache.filter(it => selectedIds.has(String(it.id)));
    if (items.length === 0) {
        alert('Pilih minimal 1 siswa pada daftar checklist terlebih dahulu.');
        return;
    }

    btn.disabled = true;
    wrap.classList.remove('hidden');
    if (logEl) logEl.innerHTML = '';

    let ok = 0, fail = 0;
    for (let i = 0; i < items.length; i++) {
        const it = items[i];
        txt.textContent = `Mengirim (${i + 1}/${items.length}) ke ${it.nama_lengkap}...`;
        cnt.textContent = (i + 1) + ' / ' + items.length;
        bar.style.width = Math.round(((i + 1) / items.length) * 100) + '%';

        const fd = new FormData();
        fd.append('csrf_token', '<?= e($csrfToken) ?>');
        fd.append('peserta_id', it.id);
        fd.append('template', tpl);
        try {
            const r = await fetch('<?= url('kelola-spmb/kartu-observasi/broadcast-send') ?>', { method: 'POST', body: fd });
            const j = await r.json();
            if (j.success) {
                ok++;
                if (logEl) logEl.insertAdjacentHTML('afterbegin', `<div class="py-1 text-emerald-700 font-semibold">✓ ${it.nama_lengkap} (${it.target_phone}) — Terkirim</div>`);
            } else {
                fail++;
                if (logEl) logEl.insertAdjacentHTML('afterbegin', `<div class="py-1 text-rose-600 font-semibold">✕ ${it.nama_lengkap} — ${j.message || 'Gagal'}</div>`);
            }
        } catch (e) {
            fail++;
            if (logEl) logEl.insertAdjacentHTML('afterbegin', `<div class="py-1 text-rose-600 font-semibold">✕ ${it.nama_lengkap} — Error koneksi</div>`);
        }

        if (i < items.length - 1) {
            txt.textContent = `Jeda keamanan ${delaySec} detik sebelum pesan berikutnya...`;
            await new Promise(resolve => setTimeout(resolve, delaySec * 1000));
        }
    }
    txt.textContent = `Selesai! Terkirim: ${ok}, Gagal: ${fail}`;
    btn.disabled = false;
}
</script>
<?php endif; ?>
