<?php
/**
 * Al-Qur'an Digital View (Mobile & PWA)
 * Features 114 Surahs, Full Audio Recitation, Ayat Reader, Bookmark with exact timestamp, and Auto 2-Minute Tilawah Reading Tracker.
 */
?>

<!-- Top App Bar -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 id="quran-header-title" class="font-black text-slate-900 text-base leading-tight">Al-Qur'an Digital</h2>
            <p id="quran-header-sub" class="text-[10px] text-slate-400 font-medium">114 Surah • Terjemahan Kemenag</p>
        </div>
    </div>

    <!-- Right: Tilawah 2-Minute Badge -->
    <div id="quran-tilawah-status-badge" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span id="quran-timer-display">00:00 / 02:00</span>
    </div>
</div>

<!-- 2-Minute Reading Tracker Sticky Progress Bar -->
<div id="quran-timer-tracker-card" class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white px-4 py-3 shadow-md mx-4 mt-3 rounded-2xl relative overflow-hidden transition-all duration-300">
    <div class="flex items-center justify-between gap-2 relative z-10">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-sm shrink-0">
                ⏱️
            </div>
            <div class="min-w-0">
                <h4 id="tracker-title" class="text-xs font-bold leading-tight">Target Tilawah Harian (2 Menit)</h4>
                <p id="tracker-desc" class="text-[10px] text-emerald-100/90 leading-tight truncate">Baca Qur'an 2 menit untuk centang otomatis.</p>
            </div>
        </div>
        <div class="text-right shrink-0">
            <span id="tracker-time-text" class="font-mono font-black text-xs text-white bg-black/20 px-2 py-0.5 rounded-lg">00:00</span>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="w-full h-1.5 bg-black/20 rounded-full mt-2.5 overflow-hidden">
        <div id="tracker-progress-bar" class="h-full bg-emerald-300 rounded-full transition-all duration-500" style="width: 0%;"></div>
    </div>
</div>

<!-- Main Container -->
<div class="p-4 space-y-3">

    <!-- VIEW 1: SURAH LIST VIEW -->
    <div id="view-surah-list" class="space-y-3">
        
        <!-- Search & Filter Bar -->
        <div class="space-y-2">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" 
                       id="search-surah" 
                       oninput="filterSurahs()" 
                       placeholder="Cari nama surah, arti, atau nomor (misal: Al-Kahfi)..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white text-xs rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm font-medium">
            </div>

            <!-- Filter Chips -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1">
                <button type="button" onclick="setSurahFilter('all')" id="filter-all" class="filter-chip px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-600 text-white shadow-xs">Semua (114)</button>
                <button type="button" onclick="setSurahFilter('makkiyah')" id="filter-makkiyah" class="filter-chip px-3 py-1 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200">Makkiyah</button>
                <button type="button" onclick="setSurahFilter('madaniyah')" id="filter-madaniyah" class="filter-chip px-3 py-1 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200">Madaniyah</button>
                <button type="button" onclick="setSurahFilter('juz30')" id="filter-juz30" class="filter-chip px-3 py-1 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200">Juz 30</button>
            </div>
        </div>

        <!-- Last Read Bookmark Banner -->
        <div id="last-read-card" onclick="resumeLastRead()" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-3 rounded-2xl border border-blue-100/80 flex items-center justify-between cursor-pointer press-bounce shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-xs">
                    🔖
                </div>
                <div>
                    <p class="text-[10px] font-bold text-blue-800">Terakhir Dibaca</p>
                    <p id="last-read-text" class="text-xs font-black text-slate-800 leading-tight">QS. Al-Kahfi: Ayat 1</p>
                </div>
            </div>
            <span class="text-xs font-bold text-blue-600 flex items-center gap-0.5">
                Lanjut <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </span>
        </div>

        <!-- Surah Cards List -->
        <div id="surah-list-container" class="space-y-2">
            <!-- Dynamically populated via JS -->
            <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                <svg class="animate-spin h-7 w-7 text-emerald-600 mb-2" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-xs font-semibold">Memuat daftar surah...</p>
            </div>
        </div>
    </div>

    <!-- VIEW 2: SURAH READER VIEW -->
    <div id="view-surah-reader" class="hidden space-y-3">
        
        <!-- Reader Navigation & Controls Bar -->
        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <button type="button" onclick="closeSurahReader()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 press-bounce">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Daftar Surah
                </button>
                
                <div class="flex items-center gap-1.5">
                    <!-- Font Size Controller -->
                    <button type="button" onclick="adjustFontSize(-2)" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center press-bounce" title="Perkecil Font">A-</button>
                    <button type="button" onclick="adjustFontSize(2)" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center press-bounce" title="Perbesar Font">A+</button>
                    <!-- Toggle Translation -->
                    <button type="button" onclick="toggleTranslation()" id="btn-toggle-arti" class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">Arti</button>
                </div>
            </div>

            <!-- Surah Big Header Badge -->
            <div class="bg-gradient-to-tr from-emerald-700 via-teal-700 to-emerald-800 rounded-2xl p-4 text-white text-center shadow-lg shadow-emerald-700/20 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-white/10 text-8xl font-arabic pointer-events-none select-none">
                    📖
                </div>
                <h3 id="reader-surah-name" class="text-lg font-black tracking-wide">Surah Al-Fatihah</h3>
                <p id="reader-surah-arti" class="text-xs text-emerald-100 font-medium">Pembukaan • 7 Ayat • Mekah</p>
                
                <!-- Audio Recitation Player Button -->
                <div class="mt-3 flex items-center justify-center gap-2">
                    <button type="button" id="btn-play-surah-audio" onclick="toggleSurahAudio()" class="px-3.5 py-1.5 rounded-full bg-white text-emerald-800 text-xs font-bold flex items-center gap-1.5 shadow-md press-bounce">
                        <i data-lucide="play" id="audio-icon" class="w-3.5 h-3.5 fill-current"></i>
                        <span id="audio-text">Putar Audio Murottal</span>
                    </button>
                </div>
            </div>

            <!-- Bismillah Header -->
            <div id="reader-bismillah-box" class="py-2 text-center">
                <p class="font-arabic text-2xl sm:text-3xl text-emerald-950 font-bold leading-relaxed">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
            </div>
        </div>

        <!-- Ayat Cards List -->
        <div id="ayat-list-container" class="space-y-3">
            <!-- Dynamically populated via JS -->
        </div>

        <!-- Next / Prev Surah Navigation Footer -->
        <div class="flex items-center justify-between gap-2 pt-2">
            <button type="button" id="btn-prev-surah" onclick="navigateSurah(-1)" class="flex-1 py-3 px-3 rounded-2xl bg-white border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm press-bounce">
                <i data-lucide="chevron-left" class="w-4 h-4"></i> Surah Sebelumnya
            </button>
            <button type="button" id="btn-next-surah" onclick="navigateSurah(1)" class="flex-1 py-3 px-3 rounded-2xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/20 press-bounce">
                Surah Berikutnya <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>

    </div>

</div>

<!-- Audio Player Element (Hidden) -->
<audio id="global-quran-audio" preload="none"></audio>

<script>
    const APP_BASE_URL = '<?= url("") ?>';

    // State Store
    let allSurahs = [];
    let currentSurahNumber = 1;
    let currentSurahData = null;
    let arabicFontSize = 24; // px
    let showTranslation = true;
    let isPlayingAudio = false;

    // ── 2-MINUTE READING TIMER STATE ──────────────────────────
    let readingSeconds = 0;
    let readingTimerInterval = null;
    const TARGET_SECONDS = 120; // 2 minutes (120 seconds)
    let isTilawahCompleted = false;

    function initReadingTimer() {
        // Check if Tilawah was already completed today
        const savedIbadah = localStorage.getItem('portal_guru_ibadah_today');
        if (savedIbadah) {
            try {
                const parsed = JSON.parse(savedIbadah);
                if (parsed.tilawah && parsed.tilawah.checked) {
                    isTilawahCompleted = true;
                    setTimerCompletedUI();
                    return;
                }
            } catch (e) {}
        }

        // Start 1-second interval tracker while on this page
        if (!readingTimerInterval) {
            readingTimerInterval = setInterval(onReadingTick, 1000);
        }
    }

    function onReadingTick() {
        if (isTilawahCompleted) return;

        readingSeconds++;
        updateTimerDisplay();

        // Check if 2 minutes (120s) reached!
        if (readingSeconds >= TARGET_SECONDS) {
            triggerTilawahCompletion();
        }
    }

    function updateTimerDisplay() {
        const mins = Math.floor(readingSeconds / 60);
        const secs = readingSeconds % 60;
        const formatted = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        
        const display = document.getElementById('quran-timer-display');
        const trackerTime = document.getElementById('tracker-time-text');
        const progressBar = document.getElementById('tracker-progress-bar');

        if (display) display.textContent = `${formatted} / 02:00`;
        if (trackerTime) trackerTime.textContent = formatted;

        const percent = Math.min(100, Math.round((readingSeconds / TARGET_SECONDS) * 100));
        if (progressBar) progressBar.style.width = `${percent}%`;
    }

    function triggerTilawahCompletion() {
        isTilawahCompleted = true;
        if (readingTimerInterval) clearInterval(readingTimerInterval);

        // Auto Save to localStorage
        let ibadah = {
            gender: 'P',
            sholat: { subuh: { checked: false }, dzuhur: { checked: false }, ashar: { checked: false }, maghrib: { checked: false }, isya: { checked: false } },
            tilawah: { checked: true, text: currentSurahData ? `QS. ${currentSurahData.namaLatin}` : 'Tilawah 2 Menit Selesai' },
            dzikir: { istighfar: false, sholawat: false },
            tadabbur: { checked: false, text: '' }
        };

        const existing = localStorage.getItem('portal_guru_ibadah_today');
        if (existing) {
            try {
                ibadah = Object.assign(ibadah, JSON.parse(existing));
            } catch (e) {}
        }
        ibadah.tilawah.checked = true;
        if (currentSurahData) ibadah.tilawah.text = `QS. ${currentSurahData.namaLatin}`;
        localStorage.setItem('portal_guru_ibadah_today', JSON.stringify(ibadah));

        setTimerCompletedUI();

        // Celebration Modal
        AndroidUI.success({
            title: 'Alhamdulillah! 📖🎉',
            subtitle: 'Target 2 Menit Tilawah Tercapai',
            message: `MasyaAllah, Anda telah membaca Al-Qur'an selama <strong>2 menit</strong>.<br>Ibadah <strong>Tilawah Harian</strong> Anda telah otomatis dicentang selesai! 🤲`,
            buttonText: 'Lanjut Membaca'
        });
    }

    function setTimerCompletedUI() {
        const badge = document.getElementById('quran-tilawah-status-badge');
        const trackerTitle = document.getElementById('tracker-title');
        const trackerDesc = document.getElementById('tracker-desc');
        const progressBar = document.getElementById('tracker-progress-bar');
        const trackerTime = document.getElementById('tracker-time-text');

        if (badge) {
            badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-600 text-white shadow-xs flex items-center gap-1';
            badge.innerHTML = '<span>✓</span> <span>Tilawah Selesai</span>';
        }
        if (trackerTitle) trackerTitle.innerHTML = 'Alhamdulillah! Target 2 Menit Selesai ✓';
        if (trackerDesc) trackerDesc.textContent = 'Ibadah Tilawah harian Anda telah otomatis tersimpan.';
        if (trackerTime) trackerTime.textContent = '02:00';
        if (progressBar) progressBar.style.width = '100%';
    }

    // ── SURAH CATALOG DATA & FETCHER ────────────────────────
    async function loadSurahCatalog() {
        const container = document.getElementById('surah-list-container');
        
        // Tier 1: Try Local Server Proxy
        try {
            const res = await fetch(`${APP_BASE_URL}/api/quran/surat`);
            const json = await res.json();
            if (json && json.data && json.data.length > 0) {
                allSurahs = json.data;
                renderSurahList(allSurahs);
                return;
            }
        } catch (e) {
            console.warn('Local API failed, trying public endpoint...', e);
        }

        // Tier 2: Try Direct EQuran API
        try {
            const res = await fetch('https://equran.id/api/v2/surat');
            const json = await res.json();
            if (json && json.data) {
                allSurahs = json.data;
                renderSurahList(allSurahs);
                return;
            }
        } catch (e) {
            console.warn('Direct EQuran API failed, using fallback catalog...', e);
        }

        // Tier 3: Built-in Catalog Index
        renderFallbackSurahList();
    }

    function renderSurahList(surahs) {
        const container = document.getElementById('surah-list-container');
        if (!container) return;

        if (!surahs || surahs.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 text-slate-400">
                    <p class="text-xs">Surah tidak ditemukan.</p>
                </div>
            `;
            return;
        }

        let html = '';
        surahs.forEach(s => {
            const nomor = s.nomor || s.number || 1;
            const namaLatin = s.namaLatin || s.name?.transliteration?.id || s.name || '';
            const arti = s.arti || s.name?.translation?.id || '';
            const jumlahAyat = s.jumlahAyat || s.numberOfVerses || '';
            const tempatTurun = s.tempatTurun || s.revelation?.id || 'Mekah';
            const namaArab = s.nama || s.name?.short || '';

            html += `
                <div onclick="openSurahReader(${nomor})" class="surah-card bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 hover:border-emerald-200 cursor-pointer press-bounce flex items-center justify-between gap-3 transition-all" data-name="${namaLatin.toLowerCase()} ${arti.toLowerCase()} ${nomor}" data-type="${tempatTurun.toLowerCase()}" data-nomor="${nomor}">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Surah Number Box -->
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-black text-xs shrink-0 border border-emerald-100">
                            ${nomor}
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-slate-900 text-xs leading-tight truncate">${namaLatin}</h4>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5">${arti} • ${jumlahAyat} Ayat</p>
                        </div>
                    </div>

                    <!-- Arabic Name Calligraphy -->
                    <div class="text-right shrink-0">
                        <p class="font-arabic text-lg font-bold text-emerald-950">${namaArab}</p>
                        <span class="text-[9px] font-bold text-slate-400 capitalize">${tempatTurun}</span>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        if (window.lucide) window.lucide.createIcons();
    }

    function filterSurahs() {
        const q = document.getElementById('search-surah').value.toLowerCase().trim();
        document.querySelectorAll('.surah-card').forEach(card => {
            const dataName = card.getAttribute('data-name') || '';
            if (dataName.includes(q)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function setSurahFilter(type) {
        document.querySelectorAll('.filter-chip').forEach(btn => {
            btn.className = 'filter-chip px-3 py-1 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200';
        });
        const activeBtn = document.getElementById(`filter-${type}`);
        if (activeBtn) {
            activeBtn.className = 'filter-chip px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-600 text-white shadow-xs';
        }

        document.querySelectorAll('.surah-card').forEach(card => {
            const num = parseInt(card.getAttribute('data-nomor'));
            const surahType = card.getAttribute('data-type');

            if (type === 'all') {
                card.classList.remove('hidden');
            } else if (type === 'makkiyah') {
                card.classList.toggle('hidden', surahType !== 'mekah');
            } else if (type === 'madaniyah') {
                card.classList.toggle('hidden', surahType !== 'madinah');
            } else if (type === 'juz30') {
                card.classList.toggle('hidden', num < 78);
            }
        });
    }

    // ── SURAH READER VIEW ───────────────────────────────────
    async function openSurahReader(nomor, targetAyat = null) {
        currentSurahNumber = nomor;
        AndroidUI.showCenterLoading('Memuat ayat-ayat suci...');

        let rawData = null;

        // Strategy 1: Fetch via local server proxy (cached on server, 0 CORS)
        try {
            const res = await fetch(`${APP_BASE_URL}/api/quran/surat/${nomor}`);
            const json = await res.json();
            if (json && json.data) {
                rawData = json.data;
            }
        } catch (e) {
            console.warn('Local API surah fetch failed, trying direct EQuran API...', e);
        }

        // Strategy 2: Fetch via EQuran.id direct
        if (!rawData) {
            try {
                const res = await fetch(`https://equran.id/api/v2/surat/${nomor}`);
                const json = await res.json();
                if (json && json.data) {
                    rawData = json.data;
                }
            } catch (e) {
                console.warn('Direct EQuran fetch failed, trying Quran Gading API...', e);
            }
        }

        // Strategy 3: Fetch via Quran Gading API
        if (!rawData) {
            try {
                const res = await fetch(`https://api.quran.gading.dev/surah/${nomor}`);
                const json = await res.json();
                if (json && json.data) {
                    const g = json.data;
                    rawData = {
                        nomor: g.number,
                        nama: g.name.short,
                        namaLatin: g.name.transliteration.id,
                        jumlahAyat: g.numberOfVerses,
                        tempatTurun: g.revelation.id,
                        arti: g.name.translation.id,
                        audioFull: { '05': g.audio?.primary },
                        ayat: g.verses.map(v => ({
                            nomorAyat: v.number.inSurah,
                            teksArab: v.text.arab,
                            teksLatin: v.text.transliteration.en || '',
                            teksIndonesia: v.translation.id,
                            audio: { '05': v.audio.primary }
                        }))
                    };
                }
            } catch (e) {
                console.error('All remote APIs failed:', e);
            }
        }

        AndroidUI.hideCenterLoading();

        if (rawData && rawData.ayat && rawData.ayat.length > 0) {
            currentSurahData = rawData;
            renderSurahReader(rawData, targetAyat);
        } else {
            AndroidUI.error({
                title: 'Gagal Memuat Ayat',
                message: 'Pastikan koneksi internet Anda aktif untuk memuat teks ayat Al-Qur\'an.',
                buttonText: 'Tutup'
            });
        }
    }

    function renderSurahReader(data, targetAyat = null) {
        // Toggle Views
        document.getElementById('view-surah-list').classList.add('hidden');
        document.getElementById('view-surah-reader').classList.remove('hidden');

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Update Headers
        const namaLatin = data.namaLatin || 'Al-Qur\'an';
        const tempatTurun = data.tempatTurun || 'Mekah';
        const jumlahAyat = data.jumlahAyat || (data.ayat ? data.ayat.length : 0);
        const arti = data.arti || '';

        document.getElementById('quran-header-title').textContent = `QS. ${namaLatin}`;
        document.getElementById('quran-header-sub').textContent = `${tempatTurun} • ${jumlahAyat} Ayat`;
        document.getElementById('reader-surah-name').textContent = `Surah ${namaLatin}`;
        document.getElementById('reader-surah-arti').textContent = `${arti} • ${jumlahAyat} Ayat • ${tempatTurun}`;

        // Check active bookmark
        const savedBookmark = getSavedBookmark();
        const activeAyatNum = (savedBookmark && savedBookmark.nomor === data.nomor) ? savedBookmark.ayat : (targetAyat || null);

        // Bismillah Banner (Hide for Al-Fatihah (already ayat 1) & At-Taubah (nomor 9))
        const bismillahBox = document.getElementById('reader-bismillah-box');
        if (data.nomor === 1 || data.nomor === 9) {
            bismillahBox.classList.add('hidden');
        } else {
            bismillahBox.classList.remove('hidden');
        }

        // Setup Audio Player URL
        const audioEl = document.getElementById('global-quran-audio');
        if (data.audioFull) {
            audioEl.src = data.audioFull['05'] || data.audioFull['01'] || Object.values(data.audioFull)[0] || '';
        }
        isPlayingAudio = false;
        updateAudioButtonUI();

        // Render Ayat Cards
        const container = document.getElementById('ayat-list-container');
        let html = '';

        data.ayat.forEach((a, index) => {
            const noAyat = a.nomorAyat || (index + 1);
            const isBookmarked = activeAyatNum === noAyat;
            const teksArab = a.teksArab || '';
            const teksLatin = a.teksLatin || '';
            const teksIndonesia = a.teksIndonesia || '';
            const audioSrc = (a.audio && typeof a.audio === 'object') ? (a.audio['05'] || a.audio['01'] || Object.values(a.audio)[0] || '') : (a.audio || '');

            html += `
                <div class="ayat-card bg-white rounded-3xl p-4 shadow-sm border ${isBookmarked ? 'border-amber-400 ring-2 ring-amber-200/60 bg-amber-50/15' : 'border-slate-100'} space-y-3 transition-all" id="ayat-${noAyat}">
                    <!-- Top Sub-bar: Ayah Number, Bookmark & Audio -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-50 text-[11px]">
                        <div class="flex items-center gap-1.5 font-bold text-slate-700">
                            <span class="w-6 h-6 rounded-lg ${isBookmarked ? 'bg-amber-500 text-white font-bold shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-100'} flex items-center justify-center font-mono text-[10px]">${noAyat}</span>
                            <span class="${isBookmarked ? 'text-amber-700 font-bold' : 'text-slate-400'}">Ayat ${noAyat}</span>
                            ${isBookmarked ? '<span class="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">Terakhir Dibaca</span>' : ''}
                        </div>
                        
                        <div class="flex items-center gap-1">
                            <!-- Bookmark Button -->
                            <button type="button" onclick="setAyatBookmark(${data.nomor}, '${namaLatin.replace(/'/g, "\\'")}', ${noAyat})" class="px-2 py-1 rounded-xl ${isBookmarked ? 'bg-amber-100 text-amber-800' : 'bg-slate-50 hover:bg-amber-50 hover:text-amber-700 text-slate-600'} font-bold text-[10px] flex items-center gap-1 press-bounce">
                                <i data-lucide="bookmark" class="w-3 h-3 ${isBookmarked ? 'fill-current' : ''}"></i> ${isBookmarked ? 'Ditandai' : 'Tandai'}
                            </button>
                            <!-- Audio Button -->
                            ${audioSrc ? `
                            <button type="button" onclick="playAyatAudio('${audioSrc}', this)" class="px-2 py-1 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 font-bold text-[10px] flex items-center gap-1 press-bounce">
                                <i data-lucide="play" class="w-3 h-3 fill-current"></i> Putar
                            </button>` : ''}
                        </div>
                    </div>

                    <!-- Arabic Text -->
                    <div class="pt-1">
                        <p class="font-arabic text-right text-slate-950 font-bold leading-loose tracking-wide arabic-text" style="font-size: ${arabicFontSize}px;">
                            ${teksArab}
                        </p>
                    </div>

                    <!-- Latin Transliteration -->
                    ${teksLatin ? `
                    <p class="text-xs text-blue-600 font-semibold leading-relaxed">
                        ${teksLatin}
                    </p>` : ''}

                    <!-- Indonesian Translation -->
                    <div class="translation-box pt-1 border-t border-slate-50 text-xs text-slate-700 leading-relaxed font-normal ${showTranslation ? '' : 'hidden'}">
                        ${teksIndonesia}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        if (window.lucide) window.lucide.createIcons();

        // If jumping to specific Ayat, smoothly scroll into view
        if (targetAyat) {
            setTimeout(() => {
                const targetEl = document.getElementById(`ayat-${targetAyat}`);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 300);
        }
    }

    function closeSurahReader() {
        const audio = document.getElementById('global-quran-audio');
        if (audio) {
            audio.pause();
            isPlayingAudio = false;
        }

        document.getElementById('view-surah-reader').classList.add('hidden');
        document.getElementById('view-surah-list').classList.remove('hidden');
        document.getElementById('quran-header-title').textContent = 'Al-Qur\'an Digital';
        document.getElementById('quran-header-sub').textContent = '114 Surah • Terjemahan Kemenag';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function adjustFontSize(delta) {
        arabicFontSize = Math.max(18, Math.min(36, arabicFontSize + delta));
        document.querySelectorAll('.arabic-text').forEach(el => {
            el.style.fontSize = `${arabicFontSize}px`;
        });
    }

    function toggleTranslation() {
        showTranslation = !showTranslation;
        document.querySelectorAll('.translation-box').forEach(el => {
            el.classList.toggle('hidden', !showTranslation);
        });
        const btn = document.getElementById('btn-toggle-arti');
        if (btn) {
            btn.className = showTranslation 
                ? 'px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold'
                : 'px-2 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold';
        }
    }

    function toggleSurahAudio() {
        const audio = document.getElementById('global-quran-audio');
        if (!audio || !audio.src) return;

        if (isPlayingAudio) {
            audio.pause();
            isPlayingAudio = false;
        } else {
            audio.play();
            isPlayingAudio = true;
        }
        updateAudioButtonUI();
    }

    function updateAudioButtonUI() {
        const icon = document.getElementById('audio-icon');
        const text = document.getElementById('audio-text');
        if (isPlayingAudio) {
            if (icon) icon.setAttribute('data-lucide', 'pause');
            if (text) text.textContent = 'Jeda Audio';
        } else {
            if (icon) icon.setAttribute('data-lucide', 'play');
            if (text) text.textContent = 'Putar Audio Murottal';
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function playAyatAudio(url, btn) {
        if (!url) return;
        const audio = document.getElementById('global-quran-audio');
        audio.src = url;
        audio.play();
        AndroidUI.toast('Memutar audio ayat...', 'info', 1500);
    }

    function navigateSurah(delta) {
        const next = currentSurahNumber + delta;
        if (next >= 1 && next <= 114) {
            openSurahReader(next);
        }
    }

    function setAyatBookmark(surahNomor, surahNama, ayatNomor) {
        const timeStr = new Date().toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const bookmarkData = {
            nomor: surahNomor,
            nama: surahNama,
            ayat: ayatNomor,
            time: timeStr
        };

        localStorage.setItem('portal_guru_last_read', JSON.stringify(bookmarkData));
        updateLastReadCard();

        if (currentSurahData) {
            renderSurahReader(currentSurahData, ayatNomor);
        }

        AndroidUI.toast(`🔖 Ditandai: QS. ${surahNama} Ayat ${ayatNomor} (${timeStr})`, 'success');
        if (navigator.vibrate) navigator.vibrate(30);
    }

    function getSavedBookmark() {
        const raw = localStorage.getItem('portal_guru_last_read');
        if (raw) {
            try {
                return JSON.parse(raw);
            } catch (e) {}
        }
        return null;
    }

    function updateLastReadCard() {
        const lr = getSavedBookmark();
        const text = document.getElementById('last-read-text');

        if (lr && text) {
            text.innerHTML = `QS. ${lr.nama}: Ayat ${lr.ayat} <span class="text-[9px] font-normal text-blue-500 block">• Ditandai: ${lr.time || 'Hari ini'}</span>`;
        } else if (text) {
            text.textContent = 'QS. Al-Kahfi: Ayat 1';
        }
    }

    function resumeLastRead() {
        const lr = getSavedBookmark();
        if (lr && lr.nomor) {
            openSurahReader(lr.nomor, lr.ayat || 1);
        } else {
            openSurahReader(18, 1); // Default Al-Kahfi
        }
    }

    function renderFallbackSurahList() {
        const fallback = [
            { nomor: 1, nama: 'الفاتحة', namaLatin: 'Al-Fatihah', arti: 'Pembukaan', jumlahAyat: 7, tempatTurun: 'Mekah' },
            { nomor: 2, nama: 'البقرة', namaLatin: 'Al-Baqarah', arti: 'Sapi', jumlahAyat: 286, tempatTurun: 'Madinah' },
            { nomor: 3, nama: 'آل عمران', namaLatin: 'Ali \'Imran', arti: 'Keluarga Imran', jumlahAyat: 200, tempatTurun: 'Madinah' },
            { nomor: 18, nama: 'الكهف', namaLatin: 'Al-Kahfi', arti: 'Gua', jumlahAyat: 110, tempatTurun: 'Mekah' },
            { nomor: 36, nama: 'يس', namaLatin: 'Yasin', arti: 'Yasin', jumlahAyat: 83, tempatTurun: 'Mekah' },
            { nomor: 55, nama: 'الرحمن', namaLatin: 'Ar-Rahman', arti: 'Yang Maha Pengasih', jumlahAyat: 78, tempatTurun: 'Madinah' },
            { nomor: 56, nama: 'الواقعة', namaLatin: 'Al-Waqi\'ah', arti: 'Hari Kiamat', jumlahAyat: 96, tempatTurun: 'Mekah' },
            { nomor: 67, nama: 'الملك', namaLatin: 'Al-Mulk', arti: 'Kerajaan', jumlahAyat: 30, tempatTurun: 'Mekah' },
            { nomor: 112, nama: 'الإخلاص', namaLatin: 'Al-Ikhlas', arti: 'Keesaan Allah', jumlahAyat: 4, tempatTurun: 'Mekah' },
            { nomor: 113, nama: 'الفلق', namaLatin: 'Al-Falaq', arti: 'Waktu Subuh', jumlahAyat: 5, tempatTurun: 'Mekah' },
            { nomor: 114, nama: 'الناس', namaLatin: 'An-Nas', arti: 'Manusia', jumlahAyat: 6, tempatTurun: 'Mekah' }
        ];
        allSurahs = fallback;
        renderSurahList(fallback);
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSurahCatalog();
        initReadingTimer();
        updateLastReadCard();
    });
</script>
