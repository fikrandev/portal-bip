<?php
/**
 * Tasbih Digital Mobile View
 * Fitur penghitung tasbih modern dengan suara klik sintetis, getar haptic,
 * pemilihan jenis tasbih (Subhanallah, Tahmid, Tahlil, Sholawat, dll)
 * serta kemampuan menambahkan jenis tasbih kustom (input sendiri) dan menyimpan riwayat sesi.
 */
?>

<!-- Top App Bar -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-black text-slate-900 text-base leading-tight flex items-center gap-1.5">
                <span>Tasbih Digital</span>
                <span class="text-xs">📿</span>
            </h2>
            <p class="text-[10px] text-slate-400 font-medium">Hitung & Simpan Dzikir Harian</p>
        </div>
    </div>

    <!-- Quick Audio & Vibration Toggles -->
    <div class="flex items-center gap-1.5">
        <button type="button" id="btn-toggle-sound" onclick="toggleSound()" class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center press-bounce text-xs" title="Suara Klik">
            <span id="icon-sound">🔊</span>
        </button>
        <button type="button" id="btn-toggle-vibrate" onclick="toggleVibrate()" class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center press-bounce text-xs" title="Getar Haptic">
            <span id="icon-vibrate">📳</span>
        </button>
    </div>
</div>

<div class="p-4 space-y-4">

    <!-- 1. Kategori & Jenis Dzikir Selector (Scrollable Chips + Tambah Baru) -->
    <div class="space-y-2">
        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pilih Jenis Dzikir:</span>
            <button type="button" onclick="openModalTambahTasbih()" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary-600 hover:text-primary-700 press-bounce">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                <span>Tambah Sendiri</span>
            </button>
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 px-0.5" id="tasbih-category-bar">
            <?php if (!empty($kategori)): ?>
                <?php foreach ($kategori as $idx => $kat): ?>
                    <button type="button" 
                            onclick="selectTasbihCategory(<?= (int)$kat['id'] ?>)" 
                            data-kat-id="<?= (int)$kat['id'] ?>"
                            data-nama="<?= e($kat['nama_tasbih']) ?>"
                            data-arab="<?= e($kat['lafadz_arab'] ?? '') ?>"
                            data-arti="<?= e($kat['arti'] ?? '') ?>"
                            data-target="<?= (int)($kat['target_default'] ?: 33) ?>"
                            data-custom="<?= empty($kat['is_default']) ? '1' : '0' ?>"
                            class="tasbih-chip-item shrink-0 px-3 py-1.5 rounded-2xl text-xs font-bold border transition-all press-bounce flex items-center gap-1.5 <?= $idx === 0 ? 'bg-primary-600 text-white border-primary-600 shadow-sm shadow-primary-600/20 active-chip' : 'bg-white text-slate-700 border-slate-200 hover:border-primary-300' ?>">
                        <span><?= e($kat['nama_tasbih']) ?></span>
                        <?php if (empty($kat['is_default'])): ?>
                            <span onclick="event.stopPropagation(); hapusKategoriCustom(<?= (int)$kat['id'] ?>, '<?= e(addslashes($kat['nama_tasbih'])) ?>');" class="ml-1 text-[10px] text-rose-300 hover:text-rose-100 hover:bg-rose-600/20 rounded-full w-4 h-4 inline-flex items-center justify-center" title="Hapus Jenis Ini">✕</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. Display Lafadz Arab & Terjemahan Card -->
    <div class="bg-gradient-to-br from-primary-900 via-indigo-900 to-slate-900 rounded-3xl p-5 text-white shadow-xl shadow-primary-950/20 relative overflow-hidden text-center space-y-2">
        <div class="absolute -right-6 -bottom-6 text-white/5 text-8xl font-serif select-none pointer-events-none">
            📿
        </div>

        <div class="flex items-center justify-between text-xs text-primary-200">
            <span id="display-kat-nama" class="font-bold text-xs text-amber-300 tracking-wide">Subhanallah (Tasbih)</span>
            <span id="display-target-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-white border border-white/15">Target: 33x</span>
        </div>

        <!-- Lafadz Arab -->
        <div class="py-2">
            <p id="display-lafadz-arab" class="font-serif text-2xl sm:text-3xl leading-relaxed text-amber-100 tracking-wide select-none">
                سُبْحَانَ اللَّهِ
            </p>
        </div>

        <!-- Arti / Terjemahan -->
        <p id="display-arti" class="text-xs text-slate-300 font-light leading-relaxed max-w-sm mx-auto line-clamp-2">
            Maha Suci Allah
        </p>
    </div>

    <!-- 3. Target Mode Switcher (33, 99, 100, 1000, Bebas) -->
    <div class="flex items-center justify-between bg-white rounded-2xl p-1.5 border border-slate-100 shadow-xs">
        <span class="text-[10px] font-bold text-slate-400 pl-2">Target Hitungan:</span>
        <div class="flex items-center gap-1" id="target-button-group">
            <button type="button" onclick="setTargetCount(33)" data-target-val="33" class="target-btn px-2.5 py-1 rounded-xl text-xs font-black bg-primary-50 text-primary-700 border border-primary-200 transition-all press-bounce">33</button>
            <button type="button" onclick="setTargetCount(99)" data-target-val="99" class="target-btn px-2.5 py-1 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all press-bounce">99</button>
            <button type="button" onclick="setTargetCount(100)" data-target-val="100" class="target-btn px-2.5 py-1 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all press-bounce">100</button>
            <button type="button" onclick="setTargetCount(1000)" data-target-val="1000" class="target-btn px-2.5 py-1 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all press-bounce">1.000</button>
            <button type="button" onclick="setTargetCount(0)" data-target-val="0" class="target-btn px-2.5 py-1 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all press-bounce" title="Tak Terhingga">∞</button>
        </div>
    </div>

    <!-- 4. Main Tasbih Counter Interactive Dial (Large Tap Button) -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col items-center justify-center relative select-none">
        
        <!-- Info Putaran & Progress Ring -->
        <div class="relative w-64 h-64 flex items-center justify-center">
            
            <!-- SVG Circular Progress Ring -->
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                <!-- Background track -->
                <circle cx="60" cy="60" r="52" stroke="#f1f5f9" stroke-width="8" fill="none" />
                <!-- Progress stroke -->
                <circle id="tasbih-circle-progress" cx="60" cy="60" r="52" stroke="#2563eb" stroke-width="8" stroke-dasharray="326.72" stroke-dashoffset="326.72" stroke-linecap="round" fill="none" class="transition-all duration-150 ease-out" />
            </svg>

            <!-- Center Tap Button (Bead Area) -->
            <button type="button" 
                    id="btn-tap-counter" 
                    onclick="handleTasbihTap(event)" 
                    class="absolute inset-4 rounded-full bg-gradient-to-tr from-slate-50 via-white to-primary-50/60 shadow-[0_8px_30px_rgba(37,99,235,0.12)] border-4 border-slate-100/90 active:scale-95 transition-transform flex flex-col items-center justify-center p-4 cursor-pointer overflow-hidden group">
                
                <!-- Ripple container -->
                <span id="tap-ripple-container" class="absolute inset-0 pointer-events-none"></span>

                <!-- Putaran Badge -->
                <span id="tasbih-round-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-primary-100 text-primary-700 tracking-wide mb-1">
                    Putaran 1
                </span>

                <!-- Huge Number Display -->
                <span id="tasbih-number-display" class="text-6xl font-black text-slate-900 tracking-tight leading-none my-1 font-mono group-active:text-primary-600 transition-colors">
                    0
                </span>

                <!-- Progress text -->
                <span id="tasbih-sub-progress" class="text-xs font-bold text-slate-400 mt-1">
                    0 / 33
                </span>
                
                <span class="text-[9px] font-medium text-primary-600 mt-2 bg-primary-50 px-2 py-0.5 rounded-full">
                    👆 Tekan untuk berdzikir
                </span>
            </button>
        </div>

        <!-- Controls: Undo, Reset, Audio Indicator -->
        <div class="flex items-center justify-between w-full max-w-xs mt-4 pt-3 border-t border-slate-100">
            <!-- Undo button -->
            <button type="button" onclick="undoTasbihTap()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all press-bounce">
                <i data-lucide="undo" class="w-3.5 h-3.5"></i>
                <span>Kurang (-1)</span>
            </button>

            <!-- Total Sesi Text -->
            <span id="sesi-total-info" class="text-[11px] font-semibold text-slate-400">
                Total Sesi: <strong id="val-total-sesi" class="text-slate-700 font-bold">0</strong>
            </span>

            <!-- Reset Button -->
            <button type="button" onclick="confirmResetTasbih()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-all press-bounce">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                <span>Reset</span>
            </button>
        </div>

    </div>

    <!-- 5. Tombol Simpan Sesi Dzikir -->
    <div class="space-y-2">
        <button type="button" 
                id="btn-simpan-sesi" 
                onclick="simpanSesiKeServer(this)" 
                class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-primary-600 via-primary-700 to-indigo-700 text-white font-bold text-xs shadow-lg shadow-primary-600/25 flex items-center justify-center gap-2 press-bounce">
            <i data-lucide="save" class="w-4 h-4"></i>
            <span>Simpan Sesi Dzikir Ini</span>
        </button>
        <p class="text-[10.5px] text-slate-400 text-center">
            Simpan hitungan dzikir Anda agar tercatat rapi dalam riwayat ibadah harian.
        </p>
    </div>

    <!-- 6. Riwayat Sesi Dzikir Hari Ini -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-xs font-bold">
                    📊
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-xs">Riwayat Dzikir Hari Ini</h3>
                    <p class="text-[10px] text-slate-400"><?= date('d M Y') ?></p>
                </div>
            </div>
            <span id="badge-total-dzikir-hari-ini" class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                <?= (int)$totalDzikirHariIni ?> Total Dzikir
            </span>
        </div>

        <!-- Riwayat List -->
        <div id="tasbih-history-list" class="space-y-2">
            <?php if (!empty($riwayatHariIni)): ?>
                <?php foreach ($riwayatHariIni as $r): ?>
                    <div class="p-2.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between text-xs">
                        <div class="min-w-0 pr-2">
                            <h4 class="font-bold text-slate-800 text-xs truncate"><?= e($r['nama_tasbih']) ?></h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                <?= date('H:i', strtotime($r['created_at'])) ?> &bull; <?= (int)$r['putaran'] ?>x putaran
                                <?php if (!empty($r['catatan'])): ?>
                                    &bull; <span class="italic"><?= e($r['catatan']) ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-primary-100 text-primary-800">
                                +<?= (int)$r['jumlah'] ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="tasbih-empty-state" class="p-4 text-center rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                    <p class="font-medium">Belum ada riwayat tasbih hari ini.</p>
                    <p class="text-[10px] mt-0.5">Mulai berdzikir dan klik tombol "Simpan Sesi Dzikir".</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal Tambah Jenis Tasbih Baru (Input Sendiri) -->
<div id="modal-tambah-tasbih" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-end sm:items-center justify-center p-0 sm:p-4 transition-opacity">
    <div class="w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl p-5 space-y-4 shadow-2xl border border-slate-100 animate-slide-up">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">
                    ✨
                </div>
                <div>
                    <h3 class="font-black text-slate-900 text-sm">Tambah Jenis Dzikir Baru</h3>
                    <p class="text-[10px] text-slate-400">Input jenis tasbih / doa sesuai kebutuhan Anda</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahTasbih()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs">✕</button>
        </div>

        <form id="form-tambah-tasbih" onsubmit="handleFormTambahTasbih(event)" class="space-y-3 text-xs">
            
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">
                    Nama Dzikir / Doa <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="input-nama-tasbih" 
                       required 
                       placeholder="Misal: Sholawat Nariyah, Doa Nabi Yunus, dll" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-xs font-medium text-slate-800 bg-slate-50 focus:bg-white outline-none transition-all">
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">
                    Lafadz Arab (Opsional)
                </label>
                <input type="text" 
                       id="input-arab-tasbih" 
                       placeholder="Misal: لَا إِلٰهَ إِلَّا أَنْتَ سُبْحَانَكَ..." 
                       dir="rtl"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-sm font-serif text-slate-800 bg-slate-50 focus:bg-white outline-none transition-all">
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">
                    Arti / Terjemahan (Opsional)
                </label>
                <textarea id="input-arti-tasbih" 
                          rows="2" 
                          placeholder="Terjemahan singkat atau keutamaan dzikir" 
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-xs text-slate-800 bg-slate-50 focus:bg-white outline-none transition-all"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1">
                    Target Hitungan Standar
                </label>
                <div class="grid grid-cols-4 gap-1.5 pt-1">
                    <label class="cursor-pointer">
                        <input type="radio" name="target_default_radio" value="33" checked class="peer sr-only">
                        <div class="py-2 text-center rounded-xl border border-slate-200 font-bold peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600 transition-all text-xs">
                            33x
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="target_default_radio" value="99" class="peer sr-only">
                        <div class="py-2 text-center rounded-xl border border-slate-200 font-bold peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600 transition-all text-xs">
                            99x
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="target_default_radio" value="100" class="peer sr-only">
                        <div class="py-2 text-center rounded-xl border border-slate-200 font-bold peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600 transition-all text-xs">
                            100x
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="target_default_radio" value="1000" class="peer sr-only">
                        <div class="py-2 text-center rounded-xl border border-slate-200 font-bold peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600 transition-all text-xs">
                            1.000x
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="button" onclick="closeModalTambahTasbih()" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs press-bounce">
                    Batal
                </button>
                <button type="submit" id="btn-submit-tambah-tasbih" class="flex-1 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/30 press-bounce">
                    Simpan Jenis Dzikir
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    // ── STATE VARIABLES ─────────────────────────────────────
    let currentCategoryId = null;
    let currentCategoryName = 'Subhanallah (Tasbih)';
    let currentCategoryArab = 'سُبْحَانَ اللَّهِ';
    let currentCategoryArti = 'Maha Suci Allah';
    
    let countInCurrentRound = 0;
    let currentRound = 1;
    let targetCount = 33;
    let totalSessionCount = 0;

    let soundEnabled = true;
    let vibrateEnabled = true;

    // Web Audio Synthesizer (Zero-latency bead click sound)
    let audioCtx = null;
    function playBeadSound(freq = 600, duration = 0.04) {
        if (!soundEnabled) return;
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + duration);
        } catch (e) {
            // AudioContext fallback
        }
    }

    function playTargetCompleteChime() {
        if (!soundEnabled) return;
        try {
            playBeadSound(784, 0.08); // Sol
            setTimeout(() => playBeadSound(1046, 0.15), 90); // Do tinggi
        } catch (e) {}
    }

    function doVibrate(pattern = 25) {
        if (!vibrateEnabled) return;
        if (navigator.vibrate) {
            try { navigator.vibrate(pattern); } catch (e) {}
        }
    }

    // Toggle Sound
    window.toggleSound = function() {
        soundEnabled = !soundEnabled;
        document.getElementById('icon-sound').innerText = soundEnabled ? '🔊' : '🔇';
        if (soundEnabled) playBeadSound(800, 0.06);
    };

    // Toggle Vibrate
    window.toggleVibrate = function() {
        vibrateEnabled = !vibrateEnabled;
        document.getElementById('icon-vibrate').innerText = vibrateEnabled ? '📳' : '📴';
        if (vibrateEnabled) doVibrate(40);
    };

    // ── TAP COUNTER HANDLER ────────────────────────────────
    window.handleTasbihTap = function(e) {
        countInCurrentRound++;
        totalSessionCount++;

        // Haptic feedback & sound
        doVibrate(25);
        playBeadSound(620, 0.035);

        // Tap visual ripple effect
        createRippleEffect(e);

        // Check if target round is completed
        if (targetCount > 0 && countInCurrentRound >= targetCount) {
            // Complete round!
            doVibrate([50, 40, 70]);
            playTargetCompleteChime();

            // Flash screen or celebration
            const dial = document.getElementById('btn-tap-counter');
            dial.classList.add('ring-4', 'ring-emerald-400');
            setTimeout(() => dial.classList.remove('ring-4', 'ring-emerald-400'), 500);

            // Increment round, reset round counter
            countInCurrentRound = 0;
            currentRound++;
        }

        updateTasbihDisplay();
    };

    // Undo Tap (-1)
    window.undoTasbihTap = function() {
        if (countInCurrentRound > 0) {
            countInCurrentRound--;
            if (totalSessionCount > 0) totalSessionCount--;
            doVibrate(15);
            updateTasbihDisplay();
        } else if (currentRound > 1) {
            currentRound--;
            countInCurrentRound = targetCount > 0 ? (targetCount - 1) : 0;
            if (totalSessionCount > 0) totalSessionCount--;
            doVibrate(15);
            updateTasbihDisplay();
        }
    };

    // Reset Confirmation
    window.confirmResetTasbih = function() {
        if (countInCurrentRound === 0 && currentRound === 1 && totalSessionCount === 0) return;
        
        AndroidUI.confirm({
            title: 'Reset Hitungan Tasbih?',
            message: 'Hitungan sesi saat ini akan kembali ke 0.',
            icon: 'rotate-ccw',
            iconBg: 'bg-rose-100 text-rose-600',
            confirmText: 'Reset ke 0',
            cancelText: 'Batal',
            onConfirm: () => {
                countInCurrentRound = 0;
                currentRound = 1;
                totalSessionCount = 0;
                doVibrate(40);
                updateTasbihDisplay();
                AndroidUI.toast('Hitungan berhasil direset.', 'info');
            }
        });
    };

    // Set Target Count (33, 99, 100, 1000, 0=Bebas)
    window.setTargetCount = function(val) {
        targetCount = parseInt(val) || 0;
        countInCurrentRound = 0;
        currentRound = 1;
        
        // Highlight active target button
        document.querySelectorAll('.target-btn').forEach(btn => {
            const bVal = parseInt(btn.getAttribute('data-target-val'));
            if (bVal === targetCount) {
                btn.className = 'target-btn px-2.5 py-1 rounded-xl text-xs font-black bg-primary-50 text-primary-700 border border-primary-200 transition-all press-bounce';
            } else {
                btn.className = 'target-btn px-2.5 py-1 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all press-bounce';
            }
        });

        document.getElementById('display-target-badge').innerText = targetCount > 0 ? `Target: ${targetCount}x` : 'Target: Bebas (∞)';
        doVibrate(20);
        updateTasbihDisplay();
    };

    // Update Counter UI Display
    function updateTasbihDisplay() {
        const numDisp = document.getElementById('tasbih-number-display');
        const roundBadge = document.getElementById('tasbih-round-badge');
        const subProg = document.getElementById('tasbih-sub-progress');
        const totalSesiVal = document.getElementById('val-total-sesi');
        const circleProg = document.getElementById('tasbih-circle-progress');

        numDisp.innerText = countInCurrentRound;
        roundBadge.innerText = `Putaran ${currentRound}`;
        totalSesiVal.innerText = totalSessionCount;

        const circumference = 326.72; // 2 * pi * 52
        if (targetCount > 0) {
            subProg.innerText = `${countInCurrentRound} / ${targetCount}`;
            const pct = Math.min(countInCurrentRound / targetCount, 1);
            const offset = circumference - (pct * circumference);
            circleProg.style.strokeDashoffset = offset;
        } else {
            subProg.innerText = `${totalSessionCount} Dzikir`;
            circleProg.style.strokeDashoffset = 0;
        }
    }

    // Ripple click visual
    function createRippleEffect(e) {
        const container = document.getElementById('tap-ripple-container');
        if (!container) return;
        const circle = document.createElement('span');
        const diameter = Math.max(container.clientWidth, container.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${(container.clientWidth / 2) - radius}px`;
        circle.style.top = `${(container.clientHeight / 2) - radius}px`;
        circle.classList.add('absolute', 'rounded-full', 'bg-primary-500/15', 'pointer-events-none', 'animate-ping');

        container.appendChild(circle);
        setTimeout(() => circle.remove(), 400);
    }

    // ── SELECT CATEGORY CHIP ───────────────────────────────
    window.selectTasbihCategory = function(katId) {
        const chip = document.querySelector(`.tasbih-chip-item[data-kat-id="${katId}"]`);
        if (!chip) return;

        // Deselect others
        document.querySelectorAll('.tasbih-chip-item').forEach(c => {
            c.className = 'tasbih-chip-item shrink-0 px-3 py-1.5 rounded-2xl text-xs font-bold border transition-all press-bounce flex items-center gap-1.5 bg-white text-slate-700 border-slate-200 hover:border-primary-300';
        });

        // Activate this chip
        chip.className = 'tasbih-chip-item shrink-0 px-3 py-1.5 rounded-2xl text-xs font-bold border transition-all press-bounce flex items-center gap-1.5 bg-primary-600 text-white border-primary-600 shadow-sm shadow-primary-600/20 active-chip';

        currentCategoryId = katId;
        currentCategoryName = chip.getAttribute('data-nama') || 'Dzikir';
        currentCategoryArab = chip.getAttribute('data-arab') || '';
        currentCategoryArti = chip.getAttribute('data-arti') || '';
        const defTarget = parseInt(chip.getAttribute('data-target')) || 33;

        // Update Display Card
        document.getElementById('display-kat-nama').innerText = currentCategoryName;
        document.getElementById('display-lafadz-arab').innerText = currentCategoryArab || currentCategoryName;
        document.getElementById('display-arti').innerText = currentCategoryArti || '-';

        setTargetCount(defTarget);
        doVibrate(30);
    };

    // ── MODAL TAMBAH JENIS TASBIH ───────────────────────────
    window.openModalTambahTasbih = function() {
        document.getElementById('modal-tambah-tasbih').classList.remove('hidden');
        document.getElementById('input-nama-tasbih').focus();
    };

    window.closeModalTambahTasbih = function() {
        document.getElementById('modal-tambah-tasbih').classList.add('hidden');
        document.getElementById('form-tambah-tasbih').reset();
    };

    window.handleFormTambahTasbih = async function(e) {
        e.preventDefault();
        const nama = document.getElementById('input-nama-tasbih').value.trim();
        const arab = document.getElementById('input-arab-tasbih').value.trim();
        const arti = document.getElementById('input-arti-tasbih').value.trim();
        const targetRadio = document.querySelector('input[name="target_default_radio"]:checked');
        const targetDefault = targetRadio ? parseInt(targetRadio.value) : 33;

        if (!nama) {
            AndroidUI.toast('Nama dzikir wajib diisi', 'warning');
            return;
        }

        const btn = document.getElementById('btn-submit-tambah-tasbih');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">⌛</span> Menyimpan...';

        try {
            const res = await fetch(APP_BASE_URL + '/mobile/tasbih/simpan-kategori', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nama_tasbih: nama,
                    lafadz_arab: arab,
                    arti: arti,
                    target_default: targetDefault
                })
            });
            const data = await res.json();

            if (data.success && data.data) {
                AndroidUI.toast('Jenis tasbih baru berhasil ditambahkan! ✨', 'success');
                closeModalTambahTasbih();

                // Append new chip to bar
                const bar = document.getElementById('tasbih-category-bar');
                const newChip = document.createElement('button');
                newChip.type = 'button';
                newChip.onclick = () => selectTasbihCategory(data.data.id);
                newChip.setAttribute('data-kat-id', data.data.id);
                newChip.setAttribute('data-nama', data.data.nama_tasbih);
                newChip.setAttribute('data-arab', data.data.lafadz_arab || '');
                newChip.setAttribute('data-arti', data.data.arti || '');
                newChip.setAttribute('data-target', data.data.target_default || 33);
                newChip.setAttribute('data-custom', '1');
                newChip.className = 'tasbih-chip-item shrink-0 px-3 py-1.5 rounded-2xl text-xs font-bold border transition-all press-bounce flex items-center gap-1.5 bg-white text-slate-700 border-slate-200 hover:border-primary-300';
                newChip.innerHTML = `
                    <span>${data.data.nama_tasbih}</span>
                    <span onclick="event.stopPropagation(); hapusKategoriCustom(${data.data.id}, '${data.data.nama_tasbih}');" class="ml-1 text-[10px] text-rose-300 hover:text-rose-100 hover:bg-rose-600/20 rounded-full w-4 h-4 inline-flex items-center justify-center" title="Hapus">✕</span>
                `;
                bar.appendChild(newChip);

                // Auto select new category
                selectTasbihCategory(data.data.id);
            } else {
                AndroidUI.toast(data.message || 'Gagal menyimpan jenis tasbih', 'error');
            }
        } catch (err) {
            AndroidUI.toast('Koneksi terganggu. Silakan coba lagi.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    };

    // Hapus Kategori Custom
    window.hapusKategoriCustom = function(katId, namaKat) {
        AndroidUI.confirm({
            title: 'Hapus Jenis Dzikir?',
            message: `Hapus "${namaKat}" dari daftar tasbih kustom Anda?`,
            icon: 'trash-2',
            iconBg: 'bg-rose-100 text-rose-600',
            confirmText: 'Hapus',
            cancelText: 'Batal',
            onConfirm: async () => {
                try {
                    const res = await fetch(APP_BASE_URL + '/mobile/tasbih/hapus-kategori', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: katId })
                    });
                    const d = await res.json();
                    if (d.success) {
                        const chip = document.querySelector(`.tasbih-chip-item[data-kat-id="${katId}"]`);
                        if (chip) chip.remove();
                        AndroidUI.toast('Jenis dzikir berhasil dihapus.', 'info');
                        // Pilih chip pertama yang ada
                        const firstChip = document.querySelector('.tasbih-chip-item');
                        if (firstChip) {
                            selectTasbihCategory(parseInt(firstChip.getAttribute('data-kat-id')));
                        }
                    } else {
                        AndroidUI.toast(d.message || 'Gagal menghapus', 'error');
                    }
                } catch (e) {
                    AndroidUI.toast('Gagal menghubungi server.', 'error');
                }
            }
        });
    };

    // ── SIMPAN SESI KE SERVER ───────────────────────────────
    window.simpanSesiKeServer = async function(btn) {
        const jumlahToSave = totalSessionCount > 0 ? totalSessionCount : countInCurrentRound;
        if (jumlahToSave <= 0) {
            AndroidUI.toast('Belum ada hitungan tasbih untuk disimpan. Silakan berdzikir terlebih dahulu!', 'warning');
            return;
        }

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">⌛</span> Menyimpan Sesi...';

        try {
            const res = await fetch(APP_BASE_URL + '/mobile/tasbih/simpan-sesi', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nama_tasbih: currentCategoryName,
                    kategori_id: currentCategoryId,
                    jumlah: jumlahToSave,
                    target: targetCount,
                    putaran: currentRound,
                    catatan: `${currentRound}x putaran (${jumlahToSave} hitungan)`
                })
            });
            const data = await res.json();

            if (data.success) {
                AndroidUI.toast(data.message || 'Sesi tasbih berhasil disimpan! 💚', 'success');
                playTargetCompleteChime();

                // Tambahkan ke riwayat tampilan
                const list = document.getElementById('tasbih-history-list');
                const empty = document.getElementById('tasbih-empty-state');
                if (empty) empty.remove();

                const nowTime = data.data && data.data.waktu ? data.data.waktu : 'Baru saja';
                const newItem = document.createElement('div');
                newItem.className = 'p-2.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between text-xs animate-slide-up';
                newItem.innerHTML = `
                    <div class="min-w-0 pr-2">
                        <h4 class="font-bold text-slate-800 text-xs truncate">${currentCategoryName}</h4>
                        <p class="text-[10px] text-slate-400 mt-0.5">${nowTime} &bull; ${currentRound}x putaran</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-emerald-100 text-emerald-800">
                            +${jumlahToSave}
                        </span>
                    </div>
                `;
                list.insertBefore(newItem, list.firstChild);

                // Update badge total dzikir hari ini
                const badge = document.getElementById('badge-total-dzikir-hari-ini');
                if (badge) {
                    const currentTotal = parseInt(badge.innerText) || 0;
                    badge.innerText = `${currentTotal + jumlahToSave} Total Dzikir`;
                }

                // Reset hitungan setelah berhasil disimpan
                countInCurrentRound = 0;
                currentRound = 1;
                totalSessionCount = 0;
                updateTasbihDisplay();
            } else {
                AndroidUI.toast(data.message || 'Gagal menyimpan sesi', 'error');
            }
        } catch (err) {
            AndroidUI.toast('Terjadi kendala jaringan saat menyimpan sesi.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    };

    // ── INITIALIZE ON LOAD ──────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const firstChip = document.querySelector('.tasbih-chip-item');
        if (firstChip) {
            selectTasbihCategory(parseInt(firstChip.getAttribute('data-kat-id')));
        } else {
            updateTasbihDisplay();
        }
    });
</script>
