<?php
/**
 * Al-Qur'an Digital View (Mobile & PWA)
 * Features:
 * 1. 114 Surahs Catalog with Quick Search & Category Filter
 * 2. 3 Specialized Reading & Study Modes:
 *    - Mode 1: Per Ayat (Detail view with Latin Transliteration, Indonesian Translation, Copy, and Audio)
 *    - Mode 2: Mushaf Madinah (Format Lembaran & Halaman 1-604 Asli Standar Madinah, Bingkai Ornamen, Navigasi Juz 1-30 & Halaman, Swipe Lembar, dan Tap-to-Inspect Action Sheet)
 *    - Mode 3: Mode Hafalan (Interactive Tahfidz & Muraja'ah Workshop: Blur Mode, Blind/Hide Mode, Clue Kata Awal, Looping Tikrar Audio, Multi-Level Mastery Status tracking, and Quiz Sambung Ayat)
 * 3. Integrated Dual Bookmark System:
 *    - Bookmark Terakhir Dibaca (Tilawah: Halaman & Ayat)
 *    - Bookmark Target Hafalan (Tahfidz)
 * 4. 2-Minute Tilawah Reading Tracker with Auto-Mutaba'ah sync.
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
            <p id="quran-header-sub" class="text-[10px] text-slate-400 font-medium">114 Surah • Mushaf Madinah (604 Halaman)</p>
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
<div class="p-4 space-y-4">

    <!-- ========================================================================= -->
    <!-- VIEW 1: SURAH CATALOG VIEW                                                -->
    <!-- ========================================================================= -->
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

        <!-- Dual Bookmark Banners (Tilawah & Hafalan) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <!-- 1. Terakhir Dibaca (Tilawah) -->
            <div id="last-read-card" onclick="resumeLastRead()" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-3 rounded-2xl border border-blue-100/80 flex items-center justify-between cursor-pointer press-bounce shadow-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-xs shrink-0">
                        📖
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider">Terakhir Dibaca (Tilawah)</p>
                        <p id="last-read-text" class="text-xs font-black text-slate-800 leading-tight truncate">Halaman 293 • QS. Al-Kahfi: 1</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-blue-600 flex items-center gap-0.5 shrink-0">
                    Lanjut <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </span>
            </div>

            <!-- 2. Target Hafalan (Tahfidz) -->
            <div id="last-hafalan-card" onclick="resumeLastHafalan()" class="bg-gradient-to-r from-emerald-50 to-teal-50 p-3 rounded-2xl border border-emerald-100/80 flex items-center justify-between cursor-pointer press-bounce shadow-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs shrink-0">
                        🧠
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Target Hafalan (Tahfidz)</p>
                        <p id="last-hafalan-text" class="text-xs font-black text-slate-800 leading-tight truncate">QS. Al-Mulk: Ayat 1</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-emerald-600 flex items-center gap-0.5 shrink-0">
                    Hafal <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </span>
            </div>
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

    <!-- ========================================================================= -->
    <!-- VIEW 2: SURAH READER VIEW (3 SPECIALIZED MODES)                           -->
    <!-- ========================================================================= -->
    <div id="view-surah-reader" class="hidden space-y-3.5">
        
        <!-- Reader Navigation & Master Mode Selector Bar -->
        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
            
            <div class="flex items-center justify-between gap-2">
                <button type="button" onclick="closeSurahReader()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 press-bounce shrink-0">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Surah
                </button>
                
                <!-- Mode Switcher (Segmented Control) -->
                <div class="flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200/80 shrink-0">
                    <button type="button" id="tab-mode-ayat" onclick="switchReaderMode('ayat')" class="py-1 px-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 bg-white text-emerald-800 shadow-xs">
                        <span>📑 Per Ayat</span>
                    </button>
                    <button type="button" id="tab-mode-mushaf" onclick="switchReaderMode('mushaf')" class="py-1 px-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 text-slate-500 hover:text-slate-800">
                        <span>📖 Mushaf Madinah</span>
                    </button>
                    <button type="button" id="tab-mode-hafalan" onclick="switchReaderMode('hafalan')" class="py-1 px-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 text-slate-500 hover:text-slate-800">
                        <span>🧠 Hafalan</span>
                    </button>
                </div>
            </div>

            <!-- Surah Big Header Badge (Used in Per Ayat & Hafalan Modes) -->
            <div id="reader-surah-header-banner" class="bg-gradient-to-tr from-emerald-700 via-teal-700 to-emerald-800 rounded-2xl p-4 text-white text-center shadow-lg shadow-emerald-700/20 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-white/10 text-8xl font-arabic pointer-events-none select-none">
                    📖
                </div>
                <h3 id="reader-surah-name" class="text-lg font-black tracking-wide">Surah Al-Fatihah</h3>
                <p id="reader-surah-arti" class="text-xs text-emerald-100 font-medium">Pembukaan • 7 Ayat • Mekah</p>
                
                <!-- Audio Recitation Player Button -->
                <div class="mt-3 flex items-center justify-center gap-2">
                    <button type="button" id="btn-play-surah-audio" onclick="toggleSurahAudio()" class="px-3.5 py-1.5 rounded-full bg-white text-emerald-800 text-xs font-bold flex items-center gap-1.5 shadow-md press-bounce">
                        <i data-lucide="play" id="audio-icon" class="w-3.5 h-3.5 fill-current"></i>
                        <span id="audio-text">Putar Audio Surah</span>
                    </button>
                </div>
            </div>

            <!-- MODE 1 CONTROLS: PER AYAT SETTINGS -->
            <div id="controls-mode-ayat" class="flex items-center justify-between pt-1 border-t border-slate-100">
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-semibold text-slate-500">Font:</span>
                    <button type="button" onclick="adjustFontSize(-2)" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center press-bounce" title="Perkecil Font">A-</button>
                    <button type="button" onclick="adjustFontSize(2)" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center press-bounce" title="Perbesar Font">A+</button>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="toggleLatin()" id="btn-toggle-latin" class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold press-bounce">Latin</button>
                    <button type="button" onclick="toggleTranslation()" id="btn-toggle-arti" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold press-bounce">Arti</button>
                </div>
            </div>

            <!-- MODE 2 CONTROLS: MUSHAF MADINAH CONTROLS -->
            <div id="controls-mode-mushaf" class="hidden space-y-3 pt-2 border-t border-slate-100">
                
                <!-- Quick Page Navigator Bar -->
                <div class="flex items-center justify-between gap-2 p-2 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl">
                    <!-- Page Prev (Kanan/Sebelumnya) -->
                    <button type="button" onclick="navigateMushafPage(1)" class="px-3 py-1.5 bg-white hover:bg-emerald-100 text-emerald-900 border border-emerald-200 rounded-xl text-xs font-bold flex items-center gap-1 press-bounce shadow-xs" title="Lembar Berikutnya (Maju)">
                        <span>◀ Maju</span>
                    </button>

                    <!-- Center: Page Selector & Jump Button -->
                    <div class="text-center">
                        <button type="button" onclick="openPageSelectorSheet()" class="font-mono font-bold text-xs text-emerald-900 hover:text-emerald-700 flex items-center justify-center gap-1 bg-white px-3 py-1 rounded-xl border border-emerald-200 shadow-xs press-bounce">
                            <span>📖 Halaman</span> <span id="mushaf-ctrl-page-num" class="text-emerald-700 font-black">1</span> <span>/ 604</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                        </button>
                    </div>

                    <!-- Page Next (Kiri/Maju) -->
                    <button type="button" onclick="navigateMushafPage(-1)" class="px-3 py-1.5 bg-white hover:bg-emerald-100 text-emerald-900 border border-emerald-200 rounded-xl text-xs font-bold flex items-center gap-1 press-bounce shadow-xs" title="Lembar Sebelumnya (Mundur)">
                        <span>Mundur ▶</span>
                    </button>
                </div>

                <!-- Font Zoom & Quick Juz Jump -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-semibold text-slate-500">Ukuran:</span>
                        <button type="button" onclick="adjustFontSize(-2)" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center press-bounce">A-</button>
                        <button type="button" onclick="adjustFontSize(2)" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center press-bounce">A+</button>
                    </div>
                    
                    <button type="button" onclick="openJuzSelectorSheet()" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px] flex items-center gap-1 press-bounce">
                        <span id="mushaf-ctrl-juz-text">Juz 1</span>
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>

            <!-- MODE 3 CONTROLS: HAFALAN & TIKRAR WORKSHOP -->
            <div id="controls-mode-hafalan" class="hidden space-y-3 pt-2 border-t border-slate-100">
                
                <!-- Surah Mastery Progress KPI -->
                <div class="bg-slate-50 rounded-2xl p-3 border border-slate-200/80">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-slate-700 flex items-center gap-1.5">
                            <span>🎯 Progres Hafalan Surah</span>
                        </span>
                        <span id="hafalan-stat-summary" class="font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md text-[11px]">
                            0 / 0 Lancar (0%)
                        </span>
                    </div>

                    <!-- Multi-Color Progress Bar -->
                    <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden flex">
                        <div id="bar-mutqin" class="bg-emerald-500 h-full transition-all duration-300" style="width: 0%;" title="Lancar (Mutqin)"></div>
                        <div id="bar-murajaah" class="bg-amber-400 h-full transition-all duration-300" style="width: 0%;" title="Sedang Dihafal"></div>
                        <div id="bar-sulit" class="bg-rose-400 h-full transition-all duration-300" style="width: 0%;" title="Perlu Diulang"></div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] text-slate-500 font-medium mt-2 flex-wrap gap-1">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Mutqin (<span id="count-mutqin">0</span>)</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Muraja'ah (<span id="count-murajaah">0</span>)</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-400"></span> Perlu Diulang (<span id="count-sulit">0</span>)</span>
                        <button type="button" onclick="markAllMutqin()" class="text-emerald-700 font-bold hover:underline">Tandai Semua Lancar</button>
                    </div>
                </div>

                <!-- Hafalan Visual Method Filter (Blur / Blind / Clue / Normal) -->
                <div>
                    <span class="text-[11px] font-bold text-slate-700 block mb-1.5">Metode Tampilan Hafalan:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                        <button type="button" onclick="setHafalanFilter('normal')" id="hafalan-opt-normal" class="hafalan-opt-btn px-2 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-600 text-white shadow-xs text-center press-bounce">
                            👁️ Tampil Penuh
                        </button>
                        <button type="button" onclick="setHafalanFilter('blur')" id="hafalan-opt-blur" class="hafalan-opt-btn px-2 py-1.5 rounded-xl text-[11px] font-bold bg-white text-slate-700 border border-slate-200 text-center press-bounce">
                            🌫️ Mode Blur
                        </button>
                        <button type="button" onclick="setHafalanFilter('blind')" id="hafalan-opt-blind" class="hafalan-opt-btn px-2 py-1.5 rounded-xl text-[11px] font-bold bg-white text-slate-700 border border-slate-200 text-center press-bounce">
                            🙈 Tutup Total
                        </button>
                        <button type="button" onclick="setHafalanFilter('clue')" id="hafalan-opt-clue" class="hafalan-opt-btn px-2 py-1.5 rounded-xl text-[11px] font-bold bg-white text-slate-700 border border-slate-200 text-center press-bounce">
                            🔤 Kata Awal
                        </button>
                    </div>
                    <p id="hafalan-method-hint" class="text-[10px] text-slate-400 italic mt-1 leading-tight">
                        Teks tampil lengkap untuk membaca dan menyimak hafalan.
                    </p>
                </div>

                <!-- Tikrar Looping Player Controls -->
                <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                            <span>🔁 Metode Tikrar (Pengulangan Audio)</span>
                        </span>
                        <button type="button" onclick="startTikrarSequence()" id="btn-start-tikrar" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] shadow-xs flex items-center gap-1 press-bounce">
                            <i data-lucide="repeat" class="w-3 h-3"></i> Putar Tikrar
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <label class="text-[10px] font-bold text-emerald-800">Jumlah Ulang per Ayat:</label>
                            <select id="tikrar-repeat-count" class="w-full mt-0.5 p-1.5 bg-white border border-emerald-200 rounded-lg text-xs font-bold text-slate-800">
                                <option value="1">1x Ulang</option>
                                <option value="3" selected>3x Ulang</option>
                                <option value="5">5x Ulang</option>
                                <option value="10">10x Ulang</option>
                                <option value="20">20x Ulang (Metode Tikrar)</option>
                                <option value="999">Loop Tanpa Batas</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-emerald-800">Jeda Waktu Mengikuti:</label>
                            <select id="tikrar-delay-sec" class="w-full mt-0.5 p-1.5 bg-white border border-emerald-200 rounded-lg text-xs font-bold text-slate-800">
                                <option value="0">Tanpa Jeda</option>
                                <option value="2" selected>Jeda 2 Detik</option>
                                <option value="4">Jeda 4 Detik</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Test Quiz Sambung Ayat Button -->
                <button type="button" onclick="openSambungAyatQuiz()" class="w-full py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-xs rounded-xl shadow-sm flex items-center justify-center gap-1.5 press-bounce">
                    <span>🧩 Kuis Tes Sambung Ayat Surah Ini</span>
                </button>

            </div>

            <!-- Bismillah Header (Used in Mode 1 & 3) -->
            <div id="reader-bismillah-box" class="py-2 text-center">
                <p class="font-arabic text-2xl sm:text-3xl text-emerald-950 font-bold leading-relaxed">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- MODE 1 CONTAINER: PER AYAT (DETAIL CARDS)                             -->
        <!-- ===================================================================== -->
        <div id="ayat-list-container" class="space-y-3">
            <!-- Dynamically populated via JS -->
        </div>

        <!-- ===================================================================== -->
        <!-- MODE 2 CONTAINER: MUSHAF MADINAH (AUTHENTIC PAGE LAYOUT 1 - 604)      -->
        <!-- ===================================================================== -->
        <div id="mushaf-view-container" class="hidden space-y-3">
            
            <!-- Authentic Madinah Mushaf Page Paper Card -->
            <div id="mushaf-page-card" class="bg-[#fcfaf5] rounded-[32px] p-5 sm:p-7 shadow-xl border-4 border-amber-800/20 relative overflow-hidden transition-all">
                
                <!-- Ornate Page Header (Surah & Juz) -->
                <div class="flex items-center justify-between pb-3 mb-3 border-b-2 border-amber-800/15 text-xs text-amber-950 font-bold font-arabic">
                    <span id="mushaf-page-juz-title" class="text-sm font-arabic font-bold text-emerald-900">الجُزْءُ الأول</span>
                    <span class="text-[10px] font-sans font-black text-amber-900 bg-amber-200/70 px-2.5 py-0.5 rounded-full shadow-inner">
                        Mushaf Madinah
                    </span>
                    <span id="mushaf-page-surah-title" class="text-sm font-arabic font-bold text-emerald-900">سُورَةُ الفَاتِحَةِ</span>
                </div>

                <!-- Page Flowing Content Mount -->
                <div id="mushaf-page-body" class="text-right font-arabic text-slate-950 leading-[2.9] select-none min-h-[350px]" 
                     dir="rtl" 
                     style="font-size: 24px; text-align: justify; text-align-last: center;">
                    <!-- Dynamically populated per page -->
                </div>

                <!-- Ornate Page Footer -->
                <div class="mt-5 pt-3 border-t-2 border-amber-800/15 flex items-center justify-between text-xs text-amber-950">
                    <button type="button" onclick="navigateMushafPage(1)" class="font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1 press-bounce">
                        <span>◀ Hal. <span id="mushaf-next-page-hint">2</span></span>
                    </button>
                    
                    <div class="text-center font-bold">
                        <span class="font-arabic text-base text-amber-950 font-bold" id="mushaf-arabic-page-num">١</span>
                        <span class="text-[10px] font-mono text-slate-400 block -mt-1" id="mushaf-latin-page-num">Halaman 1</span>
                    </div>

                    <button type="button" onclick="navigateMushafPage(-1)" class="font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1 press-bounce">
                        <span>Hal. <span id="mushaf-prev-page-hint">Prev</span> ▶</span>
                    </button>
                </div>
            </div>

            <!-- Page Quick Jump / Slider Bar -->
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-3">
                <span class="text-[11px] font-bold text-slate-500 shrink-0">Halaman:</span>
                <input type="range" 
                       id="mushaf-page-slider" 
                       min="1" 
                       max="604" 
                       value="1" 
                       oninput="onMushafSliderChange(this.value)" 
                       class="flex-1 accent-emerald-600 h-2 bg-slate-200 rounded-lg cursor-pointer">
                <span id="slider-page-value" class="font-mono font-black text-xs text-emerald-800 shrink-0 min-w-[45px] text-right">1 / 604</span>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- MODE 3 CONTAINER: MODE HAFALAN (TAHFIDZ & MURAJA'AH)                  -->
        <!-- ===================================================================== -->
        <div id="hafalan-list-container" class="hidden space-y-3">
            <!-- Dynamically populated with Hafalan Interactive Cards via JS -->
        </div>

        <!-- Next / Prev Surah Navigation Footer (Hidden when in Mushaf mode) -->
        <div id="surah-nav-footer" class="flex items-center justify-between gap-2 pt-2">
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
    let showLatin = true;
    let isPlayingAudio = false;

    // Reader Mode State: 'ayat' | 'mushaf' | 'hafalan'
    let currentReaderMode = 'ayat';
    let currentHafalanFilter = 'normal'; // 'normal' | 'blur' | 'blind' | 'clue'

    // Mushaf Madinah Page State (Pages 1 to 604)
    let currentMushafPage = 1;
    let cachedPagesData = {};

    // Tikrar Looping State
    let tikrarQueue = [];
    let tikrarCurrentIndex = 0;
    let tikrarCurrentRepeat = 1;
    let tikrarTargetRepeats = 3;
    let tikrarDelaySeconds = 2;
    let isTikrarActive = false;

    // ── SURAH TO MUSHAF PAGE MAP (114 SURAHS) ────────────────
    const SURAH_PAGE_MAP = {
        1: 1, 2: 2, 3: 50, 4: 77, 5: 106, 6: 128, 7: 151, 8: 177, 9: 187, 10: 208,
        11: 221, 12: 235, 13: 249, 14: 255, 15: 262, 16: 267, 17: 282, 18: 293, 19: 305, 20: 312,
        21: 322, 22: 332, 23: 342, 24: 350, 25: 359, 26: 367, 27: 377, 28: 385, 29: 396, 30: 404,
        31: 411, 32: 415, 33: 418, 34: 428, 35: 434, 36: 440, 37: 446, 38: 453, 39: 458, 40: 467,
        41: 477, 42: 483, 43: 489, 44: 496, 45: 499, 46: 502, 47: 507, 48: 511, 49: 515, 50: 518,
        51: 520, 52: 523, 53: 526, 54: 528, 55: 531, 56: 534, 57: 537, 58: 542, 59: 545, 60: 549,
        61: 551, 62: 553, 63: 554, 64: 556, 65: 558, 66: 560, 67: 562, 68: 564, 69: 566, 70: 568,
        71: 570, 72: 572, 73: 574, 74: 575, 75: 577, 76: 578, 77: 580, 78: 582, 79: 583, 80: 585,
        81: 586, 82: 587, 83: 587, 84: 589, 85: 590, 86: 591, 87: 591, 88: 592, 89: 593, 90: 594,
        91: 595, 92: 595, 93: 596, 94: 596, 95: 597, 96: 597, 97: 598, 98: 598, 99: 599, 100: 599,
        101: 600, 102: 600, 103: 601, 104: 601, 105: 601, 106: 602, 107: 602, 108: 602, 109: 603, 110: 603,
        111: 603, 112: 604, 113: 604, 114: 604
    };

    // ── JUZ TO MUSHAF PAGE MAP (30 JUZ) ──────────────────────
    const JUZ_PAGE_MAP = {
        1: 1, 2: 22, 3: 42, 4: 62, 5: 82, 6: 102, 7: 121, 8: 142, 9: 162, 10: 182,
        11: 201, 12: 222, 13: 242, 14: 262, 15: 282, 16: 302, 17: 322, 18: 342, 19: 362, 20: 382,
        21: 402, 22: 422, 23: 442, 24: 462, 25: 482, 26: 502, 27: 522, 28: 542, 29: 562, 30: 582
    };

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
            const pageStart = SURAH_PAGE_MAP[nomor] || 1;

            // Check if user has hafalan bookmarks in this surah
            const hafalanStats = getSurahHafalanStats(nomor, jumlahAyat || 10);
            const isAnyMutqin = hafalanStats.mutqin > 0;

            html += `
                <div onclick="openSurahReader(${nomor})" class="surah-card bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 hover:border-emerald-200 cursor-pointer press-bounce flex items-center justify-between gap-3 transition-all" data-name="${namaLatin.toLowerCase()} ${arti.toLowerCase()} ${nomor}" data-type="${tempatTurun.toLowerCase()}" data-nomor="${nomor}">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Surah Number Box -->
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-black text-xs shrink-0 border border-emerald-100">
                            ${nomor}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <h4 class="font-black text-slate-900 text-xs leading-tight truncate">${namaLatin}</h4>
                                ${isAnyMutqin ? `<span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded-full">${hafalanStats.mutqin} Hafal</span>` : ''}
                            </div>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5">${arti} • ${jumlahAyat} Ayat • Hal. ${pageStart}</p>
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

    // ── SURAH READER VIEW CONTROLLER ────────────────────────
    async function openSurahReader(nomor, targetAyat = null, initialMode = null) {
        currentSurahNumber = nomor;
        stopTikrar();

        if (initialMode) {
            currentReaderMode = initialMode;
        }

        // Set mushaf page based on surah mapping
        if (SURAH_PAGE_MAP[nomor]) {
            currentMushafPage = SURAH_PAGE_MAP[nomor];
        }

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
            renderMasterReader(rawData, targetAyat);
        } else {
            AndroidUI.error({
                title: 'Gagal Memuat Ayat',
                message: 'Pastikan koneksi internet Anda aktif untuk memuat teks ayat Al-Qur\'an.',
                buttonText: 'Tutup'
            });
        }
    }

    function renderMasterReader(data, targetAyat = null) {
        // Toggle Main Views
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

        // Bismillah Banner (Hide for Al-Fatihah (already ayat 1) & At-Taubah (nomor 9))
        const bismillahBox = document.getElementById('reader-bismillah-box');
        if (data.nomor === 1 || data.nomor === 9) {
            if (bismillahBox) bismillahBox.classList.add('hidden');
        } else {
            if (bismillahBox) bismillahBox.classList.remove('hidden');
        }

        // Setup Audio Player URL
        const audioEl = document.getElementById('global-quran-audio');
        if (data.audioFull) {
            audioEl.src = data.audioFull['05'] || data.audioFull['01'] || Object.values(data.audioFull)[0] || '';
        }
        isPlayingAudio = false;
        updateAudioButtonUI();

        // Render Mode Contents
        renderModeAyat(data, targetAyat);
        renderModeHafalan(data, targetAyat);

        // Render Mushaf Madinah Page
        loadAndRenderMushafPage(currentMushafPage, targetAyat);

        // Switch to the selected mode UI
        switchReaderMode(currentReaderMode, targetAyat);
    }

    // ── MODE SWITCHER ───────────────────────────────────────
    function switchReaderMode(mode, targetAyat = null) {
        currentReaderMode = mode;

        // Reset Tabs UI
        const tabAyat = document.getElementById('tab-mode-ayat');
        const tabMushaf = document.getElementById('tab-mode-mushaf');
        const tabHafalan = document.getElementById('tab-mode-hafalan');

        const ctrlAyat = document.getElementById('controls-mode-ayat');
        const ctrlMushaf = document.getElementById('controls-mode-mushaf');
        const ctrlHafalan = document.getElementById('controls-mode-hafalan');

        const contAyat = document.getElementById('ayat-list-container');
        const contMushaf = document.getElementById('mushaf-view-container');
        const contHafalan = document.getElementById('hafalan-list-container');

        const surahHeaderBanner = document.getElementById('reader-surah-header-banner');
        const bismillahBox = document.getElementById('reader-bismillah-box');
        const surahNavFooter = document.getElementById('surah-nav-footer');

        // Tab Styles
        [tabAyat, tabMushaf, tabHafalan].forEach(t => {
            if (t) t.className = 'py-1 px-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 text-slate-500 hover:text-slate-800';
        });

        // Hide all containers & controls
        if (ctrlAyat) ctrlAyat.classList.add('hidden');
        if (ctrlMushaf) ctrlMushaf.classList.add('hidden');
        if (ctrlHafalan) ctrlHafalan.classList.add('hidden');

        if (contAyat) contAyat.classList.add('hidden');
        if (contMushaf) contMushaf.classList.add('hidden');
        if (contHafalan) contHafalan.classList.add('hidden');

        if (mode === 'ayat') {
            if (tabAyat) tabAyat.className = 'py-1 px-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 bg-white text-emerald-800 shadow-xs';
            if (ctrlAyat) ctrlAyat.classList.remove('hidden');
            if (contAyat) contAyat.classList.remove('hidden');
            if (surahHeaderBanner) surahHeaderBanner.classList.remove('hidden');
            if (surahNavFooter) surahNavFooter.classList.remove('hidden');
            if (bismillahBox && currentSurahNumber !== 1 && currentSurahNumber !== 9) bismillahBox.classList.remove('hidden');
        } else if (mode === 'mushaf') {
            if (tabMushaf) tabMushaf.className = 'py-1 px-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 bg-white text-emerald-800 shadow-xs';
            if (ctrlMushaf) ctrlMushaf.classList.remove('hidden');
            if (contMushaf) contMushaf.classList.remove('hidden');
            if (surahHeaderBanner) surahHeaderBanner.classList.add('hidden'); // Mushaf uses authentic page header
            if (bismillahBox) bismillahBox.classList.add('hidden');
            if (surahNavFooter) surahNavFooter.classList.add('hidden'); // Mushaf uses page turn footer
            loadAndRenderMushafPage(currentMushafPage, targetAyat);
        } else if (mode === 'hafalan') {
            if (tabHafalan) tabHafalan.className = 'py-1 px-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-1 bg-white text-emerald-800 shadow-xs';
            if (ctrlHafalan) ctrlHafalan.classList.remove('hidden');
            if (contHafalan) contHafalan.classList.remove('hidden');
            if (surahHeaderBanner) surahHeaderBanner.classList.remove('hidden');
            if (surahNavFooter) surahNavFooter.classList.remove('hidden');
            if (bismillahBox && currentSurahNumber !== 1 && currentSurahNumber !== 9) bismillahBox.classList.remove('hidden');
            updateHafalanKPI();
        }

        if (window.lucide) window.lucide.createIcons();

        // Scroll to target ayat if provided
        if (targetAyat) {
            setTimeout(() => {
                const targetId = (mode === 'ayat') ? `ayat-${targetAyat}` : (mode === 'mushaf' ? `mushaf-ayah-${targetAyat}` : `hafalan-card-${targetAyat}`);
                const el = document.getElementById(targetId);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 250);
        }
    }

    // ── RENDER MODE 1: PER AYAT (DETAIL VIEW) ────────────────
    function renderModeAyat(data, targetAyat = null) {
        const container = document.getElementById('ayat-list-container');
        if (!container) return;

        const namaLatin = data.namaLatin || 'Al-Qur\'an';
        const savedBookmark = getSavedBookmark();
        const activeAyatNum = (savedBookmark && savedBookmark.nomor === data.nomor) ? savedBookmark.ayat : (targetAyat || null);

        let html = '';
        data.ayat.forEach((a, index) => {
            const noAyat = a.nomorAyat || (index + 1);
            const isBookmarked = activeAyatNum === noAyat;
            const teksArab = a.teksArab || '';
            const teksLatin = a.teksLatin || '';
            const teksIndonesia = a.teksIndonesia || '';
            const audioSrc = (a.audio && typeof a.audio === 'object') ? (a.audio['05'] || a.audio['01'] || Object.values(a.audio)[0] || '') : (a.audio || '');

            // Hafalan status
            const hStatus = getAyatHafalanStatus(data.nomor, noAyat);
            let hBadge = '';
            if (hStatus === 'mutqin') hBadge = '<span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">🟢 Mutqin</span>';
            else if (hStatus === 'murajaah') hBadge = '<span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">🟡 Muraja\'ah</span>';
            else if (hStatus === 'sulit') hBadge = '<span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-200">🔴 Perlu Diulang</span>';

            html += `
                <div class="ayat-card bg-white rounded-3xl p-4 shadow-sm border ${isBookmarked ? 'border-amber-400 ring-2 ring-amber-200/60 bg-amber-50/15' : 'border-slate-100'} space-y-3 transition-all" id="ayat-${noAyat}">
                    <!-- Top Sub-bar: Ayah Number, Bookmark & Audio -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-50 text-[11px] gap-2">
                        <div class="flex items-center gap-1.5 font-bold text-slate-700 min-w-0">
                            <span class="w-6 h-6 rounded-lg ${isBookmarked ? 'bg-amber-500 text-white font-bold shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-100'} flex items-center justify-center font-mono text-[10px] shrink-0">${noAyat}</span>
                            <span class="${isBookmarked ? 'text-amber-700 font-bold' : 'text-slate-400'} shrink-0">Ayat ${noAyat}</span>
                            ${isBookmarked ? '<span class="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold truncate">Ditandai</span>' : ''}
                            ${hBadge}
                        </div>
                        
                        <div class="flex items-center gap-1 shrink-0">
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
                    <div class="latin-box ${showLatin ? '' : 'hidden'}">
                        <p class="text-xs text-blue-600 font-semibold leading-relaxed">
                            ${teksLatin}
                        </p>
                    </div>

                    <!-- Indonesian Translation -->
                    <div class="translation-box pt-1 border-t border-slate-50 text-xs text-slate-700 leading-relaxed font-normal ${showTranslation ? '' : 'hidden'}">
                        ${teksIndonesia}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // ── RENDER MODE 2: MUSHAF MADINAH (AUTHENTIC PAGE ENGINE 1-604) ───
    async function loadAndRenderMushafPage(pageNum, targetAyat = null) {
        pageNum = Math.max(1, Math.min(604, pageNum));
        currentMushafPage = pageNum;

        // Update Slider & Controls UI
        const slider = document.getElementById('mushaf-page-slider');
        const sliderVal = document.getElementById('slider-page-value');
        const ctrlPageNum = document.getElementById('mushaf-ctrl-page-num');
        const latinPageNum = document.getElementById('mushaf-latin-page-num');
        const arabicPageNum = document.getElementById('mushaf-arabic-page-num');
        const nextHint = document.getElementById('mushaf-next-page-hint');
        const prevHint = document.getElementById('mushaf-prev-page-hint');

        if (slider) slider.value = pageNum;
        if (sliderVal) sliderVal.textContent = `${pageNum} / 604`;
        if (ctrlPageNum) ctrlPageNum.textContent = pageNum;
        if (latinPageNum) latinPageNum.textContent = `Halaman ${pageNum} dari 604`;
        if (arabicPageNum) arabicPageNum.textContent = convertToArabicNumerals(pageNum);
        if (nextHint) nextHint.textContent = (pageNum < 604) ? pageNum + 1 : 'Akhir';
        if (prevHint) prevHint.textContent = (pageNum > 1) ? pageNum - 1 : 'Awal';

        const pageBody = document.getElementById('mushaf-page-body');
        if (!pageBody) return;

        // Show loading in mushaf body if not cached
        if (!cachedPagesData[pageNum]) {
            pageBody.innerHTML = `
                <div class="py-16 text-center text-slate-400 space-y-2">
                    <svg class="animate-spin h-7 w-7 text-emerald-700 mx-auto" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-xs font-sans font-semibold">Memuat Lembaran Halaman ${pageNum}...</p>
                </div>
            `;
        }

        let pageData = cachedPagesData[pageNum] || null;

        // Fetch Page Data via proxy
        if (!pageData) {
            try {
                const res = await fetch(`${APP_BASE_URL}/api/quran/page/${pageNum}`);
                const json = await res.json();
                if (json && json.data && json.data.ayahs) {
                    pageData = json.data;
                    cachedPagesData[pageNum] = pageData;
                }
            } catch (e) {
                console.warn('Local page API failed, trying direct public API...', e);
            }
        }

        // Direct Fallback
        if (!pageData) {
            try {
                const res = await fetch(`https://api.alquran.cloud/v1/page/${pageNum}/quran-uthmani`);
                const json = await res.json();
                if (json && json.data && json.data.ayahs) {
                    pageData = json.data;
                    cachedPagesData[pageNum] = pageData;
                }
            } catch (e) {
                console.error('Failed to fetch page data:', e);
            }
        }

        if (pageData && pageData.ayahs && pageData.ayahs.length > 0) {
            renderMushafPageContent(pageData, targetAyat);
        } else {
            pageBody.innerHTML = `
                <div class="py-12 text-center text-slate-400">
                    <p class="text-xs font-sans">Gagal memuat data lembar halaman ${pageNum}. Periksa koneksi internet.</p>
                    <button type="button" onclick="loadAndRenderMushafPage(${pageNum})" class="mt-2 px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-bold font-sans press-bounce">
                        Coba Lagi
                    </button>
                </div>
            `;
        }
    }

    function renderMushafPageContent(pageData, targetAyat = null) {
        const pageBody = document.getElementById('mushaf-page-body');
        if (!pageBody || !pageData.ayahs) return;

        const firstAyah = pageData.ayahs[0];
        const lastAyah = pageData.ayahs[pageData.ayahs.length - 1];

        // Update Header Surah & Juz
        const surahTitle = document.getElementById('mushaf-page-surah-title');
        const juzTitle = document.getElementById('mushaf-page-juz-title');
        const ctrlJuzText = document.getElementById('mushaf-ctrl-juz-text');

        if (surahTitle && firstAyah && firstAyah.surah) {
            surahTitle.textContent = `سُوْرَةُ ${firstAyah.surah.name.replace('سُورَةُ ', '')}`;
        }
        if (juzTitle && firstAyah) {
            const juzNum = firstAyah.juz || 1;
            juzTitle.textContent = `الجُزْءُ ${convertToArabicNumerals(juzNum)}`;
            if (ctrlJuzText) ctrlJuzText.textContent = `Juz ${juzNum}`;
        }

        const savedBookmark = getSavedBookmark();

        let html = '';
        let currentRenderedSurahNum = 0;

        pageData.ayahs.forEach(ayah => {
            const surahNum = ayah.surah ? ayah.surah.number : currentSurahNumber;
            const surahNameLatin = ayah.surah ? (ayah.surah.englishName || ayah.surah.name) : 'Al-Qur\'an';
            const surahNameAr = ayah.surah ? ayah.surah.name : '';
            const noAyat = ayah.numberInSurah;
            let teksArab = ayah.text || '';

            // Handle Bismillah in first ayah (if not Al-Fatihah, strip Bismillah from text as header will render it)
            const isFirstAyahOfSurah = (noAyat === 1);
            if (isFirstAyahOfSurah && surahNum !== 1) {
                teksArab = teksArab.replace(/^بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ\s*/, '');
                teksArab = teksArab.replace(/^بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ\s*/, '');
            }

            // Surah Header Banner if a new surah starts on this page
            if (isFirstAyahOfSurah || currentRenderedSurahNum !== surahNum) {
                currentRenderedSurahNum = surahNum;
                html += `
                    <div class="mushaf-surah-frame my-3 p-3 rounded-2xl bg-gradient-to-r from-emerald-900 via-teal-900 to-emerald-900 text-white text-center border-2 border-amber-300/40 shadow-sm relative overflow-hidden">
                        <h4 class="font-arabic text-xl font-bold text-amber-200">${surahNameAr}</h4>
                        <p class="text-[10px] font-sans font-medium text-emerald-100">${ayah.surah.revelationType === 'Meccan' ? 'Makkiyah' : 'Madaniyah'} • ${ayah.surah.numberOfAyahs} Ayat</p>
                    </div>
                `;

                // Render Bismillah (except Surah 1 & 9)
                if (surahNum !== 1 && surahNum !== 9) {
                    html += `
                        <div class="text-center py-2 mb-2 border-b border-amber-900/10">
                            <p class="font-arabic text-2xl text-emerald-950 font-bold">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
                        </div>
                    `;
                }
            }

            const isBookmarked = (savedBookmark && savedBookmark.nomor === surahNum && savedBookmark.ayat === noAyat);
            const hStatus = getAyatHafalanStatus(surahNum, noAyat);
            const arabicNum = convertToArabicNumerals(noAyat);

            let markerClass = "inline-flex items-center justify-center mx-1 px-1.5 py-0.5 rounded-full text-emerald-900 bg-emerald-100/70 border border-emerald-300/80 text-sm font-sans font-bold hover:bg-emerald-200 cursor-pointer transition-all";
            if (isBookmarked) {
                markerClass = "inline-flex items-center justify-center mx-1 px-1.5 py-0.5 rounded-full text-amber-950 bg-amber-300 border border-amber-500 text-sm font-sans font-bold shadow-xs cursor-pointer";
            }

            let statusUnderline = "";
            if (hStatus === 'mutqin') statusUnderline = "border-b-2 border-emerald-500/80";
            else if (hStatus === 'murajaah') statusUnderline = "border-b-2 border-amber-400/80";

            html += `
                <span class="mushaf-ayah-span inline cursor-pointer p-0.5 rounded-lg transition-all ${isBookmarked ? 'bg-amber-200/80 text-amber-950 ring-2 ring-amber-400' : 'hover:bg-emerald-100/60'} ${statusUnderline}" 
                      id="mushaf-ayah-${noAyat}" 
                      onclick="openMushafAyatAction(${surahNum}, '${surahNameLatin.replace(/'/g, "\\'")}', ${noAyat}, ${currentMushafPage})">
                    ${teksArab}
                    <span class="${markerClass}" title="Ayat ${noAyat}">
                        ۝${arabicNum}
                    </span>
                </span>
            `;
        });

        pageBody.innerHTML = html;
        pageBody.style.fontSize = `${arabicFontSize + 1}px`;
    }

    function navigateMushafPage(delta) {
        const next = currentMushafPage + delta;
        if (next >= 1 && next <= 604) {
            loadAndRenderMushafPage(next);
        } else if (next > 604) {
            AndroidUI.toast('Anda telah berada di halaman terakhir (604).', 'info');
        } else if (next < 1) {
            AndroidUI.toast('Anda telah berada di halaman pertama (1).', 'info');
        }
    }

    function onMushafSliderChange(val) {
        const page = parseInt(val);
        const sliderVal = document.getElementById('slider-page-value');
        if (sliderVal) sliderVal.textContent = `${page} / 604`;
        loadAndRenderMushafPage(page);
    }

    function openPageSelectorSheet() {
        AndroidUI.bottomSheet({
            title: '📖 Lompat ke Halaman Mushaf',
            html: `
                <div class="text-left space-y-4">
                    <p class="text-xs text-slate-500">Pilih nomor halaman (1 s/d 604 Mushaf Standar Madinah):</p>
                    
                    <div class="flex items-center gap-2">
                        <input type="number" 
                               id="input-jump-page" 
                               min="1" 
                               max="604" 
                               value="${currentMushafPage}" 
                               class="flex-1 p-3 bg-slate-50 border border-slate-200 rounded-2xl text-center font-mono font-black text-lg text-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="button" onclick="const p = parseInt(document.getElementById('input-jump-page').value); if(p>=1&&p<=604){ AndroidUI.closeBottomSheet(); loadAndRenderMushafPage(p); } else { AndroidUI.toast('Masukkan nomor 1-604', 'warning'); }" class="px-5 py-3 bg-emerald-600 text-white font-bold text-xs rounded-2xl shadow-sm press-bounce">
                            Buka Lembar
                        </button>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-500 block mb-2">Halaman Penting:</span>
                        <div class="grid grid-cols-3 gap-1.5 text-xs text-center font-bold">
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); loadAndRenderMushafPage(1);" class="p-2 bg-slate-100 hover:bg-emerald-50 text-slate-700 rounded-xl press-bounce">Hal. 1 (Al-Fatihah)</button>
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); loadAndRenderMushafPage(293);" class="p-2 bg-slate-100 hover:bg-emerald-50 text-slate-700 rounded-xl press-bounce">Hal. 293 (Al-Kahfi)</button>
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); loadAndRenderMushafPage(562);" class="p-2 bg-slate-100 hover:bg-emerald-50 text-slate-700 rounded-xl press-bounce">Hal. 562 (Al-Mulk)</button>
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); loadAndRenderMushafPage(582);" class="p-2 bg-slate-100 hover:bg-emerald-50 text-slate-700 rounded-xl press-bounce">Hal. 582 (Juz 30)</button>
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); loadAndRenderMushafPage(604);" class="p-2 bg-slate-100 hover:bg-emerald-50 text-slate-700 rounded-xl press-bounce">Hal. 604 (An-Nas)</button>
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); openJuzSelectorSheet();" class="p-2 bg-emerald-50 text-emerald-800 rounded-xl press-bounce">Pilih Juz (1-30) →</button>
                        </div>
                    </div>
                </div>
            `
        });
    }

    function openJuzSelectorSheet() {
        let gridHtml = '';
        for (let j = 1; j <= 30; j++) {
            const page = JUZ_PAGE_MAP[j] || 1;
            const isCur = currentMushafPage >= page && (j === 30 || currentMushafPage < (JUZ_PAGE_MAP[j + 1] || 605));
            gridHtml += `
                <button type="button" onclick="AndroidUI.closeBottomSheet(); loadAndRenderMushafPage(${page});" class="p-2.5 rounded-2xl ${isCur ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-emerald-50 text-slate-800'} text-xs font-bold press-bounce">
                    Juz ${j} <span class="text-[9px] block font-normal ${isCur ? 'text-emerald-100' : 'text-slate-400'}">Hal. ${page}</span>
                </button>
            `;
        }

        AndroidUI.bottomSheet({
            title: '📖 Pilih Juz Al-Qur\'an (1 - 30)',
            html: `
                <div class="text-left space-y-3">
                    <p class="text-xs text-slate-500">Pilih Juz untuk langsung membuka halaman awal Juz tersebut:</p>
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 max-h-[60vh] overflow-y-auto no-scrollbar py-1">
                        ${gridHtml}
                    </div>
                </div>
            `
        });
    }

    // ── RENDER MODE 3: MODE HAFALAN (TAHFIDZ & MURAJA'AH) ────
    function renderModeHafalan(data, targetAyat = null) {
        const container = document.getElementById('hafalan-list-container');
        if (!container) return;

        const namaLatin = data.namaLatin || 'Al-Qur\'an';
        const savedHafalanBm = getSavedHafalanBookmark();
        const activeHafalanNum = (savedHafalanBm && savedHafalanBm.nomor === data.nomor) ? savedHafalanBm.ayat : (targetAyat || null);

        let html = '';
        data.ayat.forEach((a, index) => {
            const noAyat = a.nomorAyat || (index + 1);
            const isBookmarked = activeHafalanNum === noAyat;
            const teksArab = a.teksArab || '';
            const teksLatin = a.teksLatin || '';
            const teksIndonesia = a.teksIndonesia || '';
            const audioSrc = (a.audio && typeof a.audio === 'object') ? (a.audio['05'] || a.audio['01'] || Object.values(a.audio)[0] || '') : (a.audio || '');

            const hStatus = getAyatHafalanStatus(data.nomor, noAyat);

            // Clue Word Generator (1st 2 words of ayah)
            const words = teksArab.split(' ');
            const firstWords = words.slice(0, Math.min(2, words.length)).join(' ');
            const remainingWords = words.slice(Math.min(2, words.length)).join(' ');

            html += `
                <div class="hafalan-card bg-white rounded-3xl p-4 shadow-sm border ${isBookmarked ? 'border-amber-400 ring-2 ring-amber-200/60 bg-amber-50/15' : 'border-slate-100'} space-y-3 transition-all" id="hafalan-card-${noAyat}">
                    
                    <!-- Header Hafalan Card -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 text-xs">
                        <div class="flex items-center gap-1.5 font-bold text-slate-800">
                            <span class="w-6 h-6 rounded-lg ${isBookmarked ? 'bg-amber-500 text-white' : 'bg-emerald-100 text-emerald-800'} flex items-center justify-center font-mono text-[10px]">${noAyat}</span>
                            <span>Ayat ${noAyat}</span>
                            ${isBookmarked ? '<span class="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">Target Hafalan</span>' : ''}
                        </div>

                        <!-- Status Selector Pill Buttons -->
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="setHafalanStatus(${data.nomor}, ${noAyat}, 'mutqin')" class="px-2 py-1 rounded-lg text-[10px] font-bold ${hStatus === 'mutqin' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50'} press-bounce" title="Sudah Hafal Lancar">
                                🟢 Mutqin
                            </button>
                            <button type="button" onclick="setHafalanStatus(${data.nomor}, ${noAyat}, 'murajaah')" class="px-2 py-1 rounded-lg text-[10px] font-bold ${hStatus === 'murajaah' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-amber-50'} press-bounce" title="Sedang Dihafal / Perlu Pengulangan">
                                🟡 Muraja'ah
                            </button>
                            <button type="button" onclick="setHafalanStatus(${data.nomor}, ${noAyat}, 'sulit')" class="px-2 py-1 rounded-lg text-[10px] font-bold ${hStatus === 'sulit' ? 'bg-rose-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-rose-50'} press-bounce" title="Ayat Sulit / Perlu Fokus">
                                🔴 Sulit
                            </button>
                        </div>
                    </div>

                    <!-- Interactive Arabic Hafalan Field -->
                    <div class="hafalan-ayah-interactive p-3 rounded-2xl bg-slate-50/80 border border-slate-200/60 cursor-pointer relative group" 
                         onclick="toggleAyahReveal(${noAyat})" 
                         id="hafalan-text-box-${noAyat}">
                        
                        <!-- Tap to reveal hint badge -->
                        <div class="reveal-hint-badge hidden text-center py-2 text-xs font-bold text-emerald-800 bg-emerald-100/80 rounded-xl mb-2">
                            <span>👁️ Ketuk untuk Menampilkan Ayat</span>
                        </div>

                        <p class="font-arabic text-right text-slate-950 font-bold leading-loose tracking-wide hafalan-arabic-content" 
                           id="hafalan-arabic-${noAyat}" 
                           style="font-size: ${arabicFontSize + 1}px;">
                            <span class="hafalan-clue-word text-emerald-800">${firstWords}</span>
                            <span class="hafalan-rest-words">${remainingWords ? ' ' + remainingWords : ''}</span>
                        </p>
                    </div>

                    <!-- Action Bar per Hafalan Card -->
                    <div class="flex items-center justify-between text-[11px] pt-1">
                        <div class="flex items-center gap-1.5">
                            <!-- Bookmark Hafalan -->
                            <button type="button" onclick="setHafalanBookmark(${data.nomor}, '${namaLatin.replace(/'/g, "\\'")}', ${noAyat})" class="px-2.5 py-1 rounded-xl ${isBookmarked ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600 hover:bg-amber-50'} font-bold flex items-center gap-1 press-bounce">
                                <i data-lucide="bookmark" class="w-3 h-3 ${isBookmarked ? 'fill-current' : ''}"></i> ${isBookmarked ? 'Ditandai' : 'Tandai'}
                            </button>

                            <!-- Audio Tikrar per Ayat -->
                            ${audioSrc ? `
                            <button type="button" onclick="playSingleTikrar('${audioSrc}', ${noAyat})" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 font-bold flex items-center gap-1 press-bounce">
                                <i data-lucide="repeat" class="w-3 h-3"></i> Ulangi
                            </button>` : ''}
                        </div>

                        <button type="button" onclick="toggleAyahReveal(${noAyat})" class="text-[10px] font-bold text-emerald-700 hover:underline">
                            Lihat / Tutup Ayat
                        </button>
                    </div>

                </div>
            `;
        });

        container.innerHTML = html;
        applyHafalanFilter(currentHafalanFilter);
    }

    // ── HAFALAN VISUAL FILTER MODES ─────────────────────────
    function setHafalanFilter(filter) {
        currentHafalanFilter = filter;
        document.querySelectorAll('.hafalan-opt-btn').forEach(b => {
            b.className = 'hafalan-opt-btn px-2 py-1.5 rounded-xl text-[11px] font-bold bg-white text-slate-700 border border-slate-200 text-center press-bounce';
        });

        const activeBtn = document.getElementById(`hafalan-opt-${filter}`);
        if (activeBtn) activeBtn.className = 'hafalan-opt-btn px-2 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-600 text-white shadow-xs text-center press-bounce';

        const hintEl = document.getElementById('hafalan-method-hint');
        if (filter === 'normal') {
            if (hintEl) hintEl.textContent = 'Teks tampil lengkap untuk membaca dan menyimak hafalan.';
        } else if (filter === 'blur') {
            if (hintEl) hintEl.textContent = 'Teks Arab dikaburkan (blur). Ketuk ayat untuk membuka dan menguji ketepatan ingatan.';
        } else if (filter === 'blind') {
            if (hintEl) hintEl.textContent = 'Teks Arab ditutup total. Bacalah dari hafalan, lalu ketuk untuk mencocokkan.';
        } else if (filter === 'clue') {
            if (hintEl) hintEl.textContent = 'Hanya menampilkan kata awal setiap ayat sebagai pemantik ingatan.';
        }

        applyHafalanFilter(filter);
    }

    function applyHafalanFilter(filter) {
        document.querySelectorAll('.hafalan-arabic-content').forEach(p => {
            const rest = p.querySelector('.hafalan-rest-words');
            const clue = p.querySelector('.hafalan-clue-word');
            const hint = p.parentElement.querySelector('.reveal-hint-badge');

            // Reset reveals
            p.classList.remove('revealed');

            if (filter === 'normal') {
                p.style.filter = 'none';
                p.classList.remove('hidden');
                if (rest) rest.classList.remove('hidden');
                if (clue) clue.classList.remove('hidden');
                if (hint) hint.classList.add('hidden');
            } else if (filter === 'blur') {
                p.style.filter = 'blur(6px)';
                p.classList.remove('hidden');
                if (rest) rest.classList.remove('hidden');
                if (clue) clue.classList.remove('hidden');
                if (hint) hint.classList.add('hidden');
            } else if (filter === 'blind') {
                p.style.filter = 'none';
                p.classList.add('hidden');
                if (hint) hint.classList.remove('hidden');
            } else if (filter === 'clue') {
                p.style.filter = 'none';
                p.classList.remove('hidden');
                if (rest) rest.classList.add('hidden');
                if (clue) clue.classList.remove('hidden');
                if (hint) hint.classList.add('hidden');
            }
        });
    }

    function toggleAyahReveal(noAyat) {
        const arabicP = document.getElementById(`hafalan-arabic-${noAyat}`);
        if (!arabicP) return;

        const hint = arabicP.parentElement.querySelector('.reveal-hint-badge');
        const rest = arabicP.querySelector('.hafalan-rest-words');

        if (arabicP.classList.contains('revealed')) {
            // Re-apply filter
            arabicP.classList.remove('revealed');
            if (currentHafalanFilter === 'blur') {
                arabicP.style.filter = 'blur(6px)';
            } else if (currentHafalanFilter === 'blind') {
                arabicP.classList.add('hidden');
                if (hint) hint.classList.remove('hidden');
            } else if (currentHafalanFilter === 'clue') {
                if (rest) rest.classList.add('hidden');
            }
        } else {
            // Reveal text!
            arabicP.classList.add('revealed');
            arabicP.style.filter = 'none';
            arabicP.classList.remove('hidden');
            if (hint) hint.classList.add('hidden');
            if (rest) rest.classList.remove('hidden');
        }
    }

    // ── HAFALAN STATUS & PROGRESS TRACKING ───────────────────
    function getHafalanStorageKey(surahNomor, noAyat) {
        return `${surahNomor}_${noAyat}`;
    }

    function getAllHafalanData() {
        const raw = localStorage.getItem('portal_guru_hafalan_statuses');
        if (raw) {
            try { return JSON.parse(raw); } catch (e) {}
        }
        return {};
    }

    function getAyatHafalanStatus(surahNomor, noAyat) {
        const data = getAllHafalanData();
        return data[getHafalanStorageKey(surahNomor, noAyat)] || null;
    }

    function setHafalanStatus(surahNomor, noAyat, status) {
        const data = getAllHafalanData();
        const key = getHafalanStorageKey(surahNomor, noAyat);

        // If clicking same status, toggle off
        if (data[key] === status) {
            delete data[key];
        } else {
            data[key] = status;
        }

        localStorage.setItem('portal_guru_hafalan_statuses', JSON.stringify(data));

        if (currentSurahData) {
            renderModeAyat(currentSurahData);
            renderModeHafalan(currentSurahData);
        }
        if (currentReaderMode === 'mushaf') {
            loadAndRenderMushafPage(currentMushafPage);
        }

        updateHafalanKPI();

        const statusLabel = status === 'mutqin' ? '🟢 Mutqin (Lancar)' : (status === 'murajaah' ? '🟡 Muraja\'ah' : '🔴 Perlu Diulang');
        AndroidUI.toast(`Ayat ${noAyat}: Status diubah ke ${statusLabel}`, 'success', 1500);
        if (navigator.vibrate) navigator.vibrate(25);
    }

    function markAllMutqin() {
        if (!currentSurahData || !currentSurahData.ayat) return;

        AndroidUI.confirm({
            title: 'Tandai Semua Lancar?',
            message: `Tandai seluruh ${currentSurahData.ayat.length} ayat di Surah ${currentSurahData.namaLatin} sebagai Mutqin (Lancar)?`,
            confirmText: 'Ya, Tandai Mutqin',
            cancelText: 'Batal',
            onConfirm: () => {
                const data = getAllHafalanData();
                currentSurahData.ayat.forEach(a => {
                    data[getHafalanStorageKey(currentSurahData.nomor, a.nomorAyat)] = 'mutqin';
                });
                localStorage.setItem('portal_guru_hafalan_statuses', JSON.stringify(data));

                renderModeAyat(currentSurahData);
                renderModeHafalan(currentSurahData);
                if (currentReaderMode === 'mushaf') {
                    loadAndRenderMushafPage(currentMushafPage);
                }
                updateHafalanKPI();

                AndroidUI.toast('Semua ayat berhasil ditandai Mutqin! 🎉', 'success');
            }
        });
    }

    function updateHafalanKPI() {
        if (!currentSurahData || !currentSurahData.ayat) return;

        const totalAyat = currentSurahData.ayat.length;
        let mutqinCount = 0;
        let murajaahCount = 0;
        let sulitCount = 0;

        currentSurahData.ayat.forEach(a => {
            const st = getAyatHafalanStatus(currentSurahData.nomor, a.nomorAyat);
            if (st === 'mutqin') mutqinCount++;
            else if (st === 'murajaah') murajaahCount++;
            else if (st === 'sulit') sulitCount++;
        });

        const percent = Math.round((mutqinCount / totalAyat) * 100);

        const summaryEl = document.getElementById('hafalan-stat-summary');
        if (summaryEl) summaryEl.textContent = `${mutqinCount} / ${totalAyat} Lancar (${percent}%)`;

        const barMutqin = document.getElementById('bar-mutqin');
        const barMurajaah = document.getElementById('bar-murajaah');
        const barSulit = document.getElementById('bar-sulit');

        if (barMutqin) barMutqin.style.width = `${(mutqinCount / totalAyat) * 100}%`;
        if (barMurajaah) barMurajaah.style.width = `${(murajaahCount / totalAyat) * 100}%`;
        if (barSulit) barSulit.style.width = `${(sulitCount / totalAyat) * 100}%`;

        const countMutqinEl = document.getElementById('count-mutqin');
        const countMurajaahEl = document.getElementById('count-murajaah');
        const countSulitEl = document.getElementById('count-sulit');

        if (countMutqinEl) countMutqinEl.textContent = mutqinCount;
        if (countMurajaahEl) countMurajaahEl.textContent = murajaahCount;
        if (countSulitEl) countSulitEl.textContent = sulitCount;
    }

    function getSurahHafalanStats(surahNomor, totalAyat) {
        const data = getAllHafalanData();
        let mutqin = 0;
        for (let i = 1; i <= totalAyat; i++) {
            if (data[getHafalanStorageKey(surahNomor, i)] === 'mutqin') mutqin++;
        }
        return { mutqin, total: totalAyat };
    }

    // ── TIKRAR AUDIO LOOPING ENGINE ─────────────────────────
    function playSingleTikrar(audioUrl, noAyat) {
        const repeats = parseInt(document.getElementById('tikrar-repeat-count')?.value || 3);
        const delay = parseInt(document.getElementById('tikrar-delay-sec')?.value || 2);

        tikrarQueue = [{ url: audioUrl, ayat: noAyat }];
        tikrarCurrentIndex = 0;
        tikrarCurrentRepeat = 1;
        tikrarTargetRepeats = repeats;
        tikrarDelaySeconds = delay;
        isTikrarActive = true;

        AndroidUI.toast(`🔁 Memulai Tikrar Ayat ${noAyat} (${repeats}x Pengulangan)...`, 'info');
        executeTikrarPlay();
    }

    function startTikrarSequence() {
        if (!currentSurahData || !currentSurahData.ayat) return;

        const repeats = parseInt(document.getElementById('tikrar-repeat-count')?.value || 3);
        const delay = parseInt(document.getElementById('tikrar-delay-sec')?.value || 2);

        tikrarQueue = currentSurahData.ayat.map(a => ({
            url: (a.audio && typeof a.audio === 'object') ? (a.audio['05'] || Object.values(a.audio)[0]) : a.audio,
            ayat: a.nomorAyat
        })).filter(item => item.url);

        if (tikrarQueue.length === 0) {
            AndroidUI.toast('Audio untuk surah ini tidak tersedia.', 'warning');
            return;
        }

        tikrarCurrentIndex = 0;
        tikrarCurrentRepeat = 1;
        tikrarTargetRepeats = repeats;
        tikrarDelaySeconds = delay;
        isTikrarActive = true;

        AndroidUI.toast(`🔁 Memulai Tikrar Surah ${currentSurahData.namaLatin} (${repeats}x per ayat)...`, 'info');
        executeTikrarPlay();
    }

    function executeTikrarPlay() {
        if (!isTikrarActive || tikrarCurrentIndex >= tikrarQueue.length) {
            stopTikrar();
            return;
        }

        const currentItem = tikrarQueue[tikrarCurrentIndex];
        const audio = document.getElementById('global-quran-audio');
        audio.src = currentItem.url;

        // Visual feedback
        const card = document.getElementById(`hafalan-card-${currentItem.ayat}`);
        if (card) {
            document.querySelectorAll('.hafalan-card').forEach(c => c.classList.remove('ring-4', 'ring-emerald-400/80'));
            card.classList.add('ring-4', 'ring-emerald-400/80');
        }

        audio.onended = () => {
            if (!isTikrarActive) return;

            if (tikrarCurrentRepeat < tikrarTargetRepeats) {
                tikrarCurrentRepeat++;
                setTimeout(() => {
                    if (isTikrarActive) audio.play();
                }, tikrarDelaySeconds * 1000);
            } else {
                // Next Ayah
                tikrarCurrentRepeat = 1;
                tikrarCurrentIndex++;
                if (tikrarCurrentIndex < tikrarQueue.length) {
                    setTimeout(() => {
                        if (isTikrarActive) executeTikrarPlay();
                    }, tikrarDelaySeconds * 1000);
                } else {
                    stopTikrar();
                    AndroidUI.toast('Selesai pengulangan Tikrar Al-Qur\'an!', 'success');
                }
            }
        };

        audio.play().catch(e => console.warn('Autoplay prevented:', e));
    }

    function stopTikrar() {
        isTikrarActive = false;
        const audio = document.getElementById('global-quran-audio');
        if (audio) {
            audio.onended = null;
            audio.pause();
        }
        document.querySelectorAll('.hafalan-card').forEach(c => c.classList.remove('ring-4', 'ring-emerald-400/80'));
    }

    // ── KUIS SAMBUNG AYAT (SELF-TEST QUIZ) ───────────────────
    function openSambungAyatQuiz() {
        if (!currentSurahData || !currentSurahData.ayat || currentSurahData.ayat.length < 2) {
            AndroidUI.toast('Surah membutuhkan minimal 2 ayat untuk kuis sambung ayat.', 'warning');
            return;
        }

        const total = currentSurahData.ayat.length;
        const randomIdx = Math.floor(Math.random() * (total - 1)); // Ayat 1 to total - 1
        const questionAyah = currentSurahData.ayat[randomIdx];
        const answerAyah = currentSurahData.ayat[randomIdx + 1];

        AndroidUI.bottomSheet({
            title: `🧩 Kuis Sambung Ayat: QS. ${currentSurahData.namaLatin}`,
            html: `
                <div class="text-left space-y-3">
                    <p class="text-xs text-slate-500">Lafalkan sambungan dari ayat <strong>ke-${questionAyah.nomorAyat}</strong> berikut:</p>
                    
                    <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 text-right">
                        <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full font-mono">Soal: Ayat ${questionAyah.nomorAyat}</span>
                        <p class="font-arabic text-xl font-bold text-slate-900 leading-loose mt-2">${questionAyah.teksArab}</p>
                    </div>

                    <!-- Hidden Answer Box -->
                    <div id="quiz-answer-box" class="hidden p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-right space-y-1">
                        <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full font-mono">Kunci Jawaban: Ayat ${answerAyah.nomorAyat}</span>
                        <p class="font-arabic text-xl font-bold text-amber-950 leading-loose mt-2">${answerAyah.teksArab}</p>
                        <p class="text-xs text-slate-600 font-sans text-left mt-1 italic">${answerAyah.teksLatin}</p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" id="btn-reveal-quiz-ans" onclick="document.getElementById('quiz-answer-box').classList.remove('hidden'); this.classList.add('hidden');" class="flex-1 py-2.5 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-sm press-bounce">
                            👁️ Buka Kunci Jawaban
                        </button>
                        <button type="button" onclick="AndroidUI.closeBottomSheet(); setTimeout(openSambungAyatQuiz, 300);" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 press-bounce">
                            🔄 Soal Lain
                        </button>
                    </div>
                </div>
            `
        });
    }

    // ── MUSHAF INTERACTIVE AYAH ACTION SHEET ─────────────────
    function openMushafAyatAction(surahNomor, surahNama, noAyat, pageNum) {
        const isTilawahBm = getSavedBookmark()?.nomor === surahNomor && getSavedBookmark()?.ayat === noAyat;
        const isHafalanBm = getSavedHafalanBookmark()?.nomor === surahNomor && getSavedHafalanBookmark()?.ayat === noAyat;
        const hStatus = getAyatHafalanStatus(surahNomor, noAyat);

        AndroidUI.bottomSheet({
            title: `QS. ${surahNama} : Ayat ${noAyat} (Hal. ${pageNum})`,
            html: `
                <div class="text-left space-y-3.5 max-h-[70vh] overflow-y-auto no-scrollbar">
                    
                    <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-100 text-center">
                        <span class="text-xs font-bold text-emerald-900">📖 Mushaf Madinah • Halaman ${pageNum}</span>
                        <p class="text-[11px] text-emerald-700">Ayat ${noAyat} dari Surah ${surahNama}</p>
                    </div>

                    <!-- Actions Grid -->
                    <div class="grid grid-cols-2 gap-2">
                        
                        <!-- 1. Bookmark Tilawah Halaman & Ayat -->
                        <button type="button" onclick="AndroidUI.closeBottomSheet(); setAyatBookmark(${surahNomor}, '${surahNama.replace(/'/g, "\\'")}', ${noAyat}, ${pageNum});" class="p-3 rounded-2xl ${isTilawahBm ? 'bg-amber-100 border border-amber-300 text-amber-900' : 'bg-slate-100 hover:bg-amber-50 text-slate-700'} text-xs font-bold flex items-center gap-2 press-bounce">
                            <i data-lucide="bookmark" class="w-4 h-4 text-amber-600 ${isTilawahBm ? 'fill-current' : ''}"></i>
                            <span>${isTilawahBm ? 'Ditandai Tilawah ✓' : 'Tandai Halaman Ini'}</span>
                        </button>

                        <!-- 2. Bookmark Hafalan -->
                        <button type="button" onclick="AndroidUI.closeBottomSheet(); setHafalanBookmark(${surahNomor}, '${surahNama.replace(/'/g, "\\'")}', ${noAyat});" class="p-3 rounded-2xl ${isHafalanBm ? 'bg-emerald-100 border border-emerald-300 text-emerald-900' : 'bg-slate-100 hover:bg-emerald-50 text-slate-700'} text-xs font-bold flex items-center gap-2 press-bounce">
                            <i data-lucide="brain" class="w-4 h-4 text-emerald-600"></i>
                            <span>${isHafalanBm ? 'Target Hafalan ✓' : 'Target Hafalan'}</span>
                        </button>

                        <!-- 3. Switch to Per Ayat Detail -->
                        <button type="button" onclick="AndroidUI.closeBottomSheet(); openSurahReader(${surahNomor}, ${noAyat}, 'ayat');" class="p-3 rounded-2xl bg-slate-100 hover:bg-emerald-50 text-slate-700 text-xs font-bold flex items-center gap-2 press-bounce">
                            <i data-lucide="file-text" class="w-4 h-4 text-emerald-600"></i>
                            <span>Lihat Terjemahan</span>
                        </button>

                        <!-- 4. Switch to Hafalan Mode -->
                        <button type="button" onclick="AndroidUI.closeBottomSheet(); openSurahReader(${surahNomor}, ${noAyat}, 'hafalan');" class="p-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2 shadow-sm press-bounce">
                            <i data-lucide="zap" class="w-4 h-4 text-amber-300"></i>
                            <span>Mode Hafalan</span>
                        </button>
                    </div>

                    <!-- Hafalan Status Quick Toggle -->
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-500 block mb-1.5">Status Penguasaan Hafalan:</span>
                        <div class="grid grid-cols-3 gap-1.5 text-center">
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); setHafalanStatus(${surahNomor}, ${noAyat}, 'mutqin');" class="py-2 rounded-xl text-xs font-bold ${hStatus === 'mutqin' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'} press-bounce">
                                🟢 Mutqin
                            </button>
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); setHafalanStatus(${surahNomor}, ${noAyat}, 'murajaah');" class="py-2 rounded-xl text-xs font-bold ${hStatus === 'murajaah' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-800 border border-amber-200'} press-bounce">
                                🟡 Muraja'ah
                            </button>
                            <button type="button" onclick="AndroidUI.closeBottomSheet(); setHafalanStatus(${surahNomor}, ${noAyat}, 'sulit');" class="py-2 rounded-xl text-xs font-bold ${hStatus === 'sulit' ? 'bg-rose-500 text-white shadow-xs' : 'bg-rose-50 text-rose-800 border border-rose-200'} press-bounce">
                                🔴 Sulit
                            </button>
                        </div>
                    </div>

                </div>
            `
        });

        if (window.lucide) window.lucide.createIcons();
    }

    // ── BOOKMARK & PERSISTENCE HELPERS ──────────────────────
    function setAyatBookmark(surahNomor, surahNama, ayatNomor, pageNum = null) {
        const timeStr = new Date().toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const bookmarkPage = pageNum || SURAH_PAGE_MAP[surahNomor] || 1;

        const bookmarkData = {
            nomor: surahNomor,
            nama: surahNama,
            ayat: ayatNomor,
            page: bookmarkPage,
            mode: currentReaderMode,
            time: timeStr
        };

        localStorage.setItem('portal_guru_last_read', JSON.stringify(bookmarkData));
        updateLastReadCard();

        if (currentSurahData) {
            renderModeAyat(currentSurahData, ayatNomor);
        }
        if (currentReaderMode === 'mushaf') {
            loadAndRenderMushafPage(currentMushafPage, ayatNomor);
        }

        AndroidUI.toast(`🔖 Ditandai Terakhir Dibaca: Hal. ${bookmarkPage} (QS. ${surahNama} : ${ayatNomor})`, 'success');
        if (navigator.vibrate) navigator.vibrate(30);
    }

    function setHafalanBookmark(surahNomor, surahNama, ayatNomor) {
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

        localStorage.setItem('portal_guru_hafalan_bookmark', JSON.stringify(bookmarkData));
        updateLastHafalanCard();

        if (currentSurahData) {
            renderModeHafalan(currentSurahData, ayatNomor);
        }

        AndroidUI.toast(`🧠 Ditandai Target Hafalan: QS. ${surahNama} : Ayat ${ayatNomor}`, 'success');
        if (navigator.vibrate) navigator.vibrate(30);
    }

    function getSavedBookmark() {
        const raw = localStorage.getItem('portal_guru_last_read');
        if (raw) {
            try { return JSON.parse(raw); } catch (e) {}
        }
        return null;
    }

    function getSavedHafalanBookmark() {
        const raw = localStorage.getItem('portal_guru_hafalan_bookmark');
        if (raw) {
            try { return JSON.parse(raw); } catch (e) {}
        }
        return null;
    }

    function updateLastReadCard() {
        const lr = getSavedBookmark();
        const text = document.getElementById('last-read-text');

        if (lr && text) {
            const pageStr = lr.page ? `Hal. ${lr.page} • ` : '';
            text.innerHTML = `${pageStr}QS. ${lr.nama}: Ayat ${lr.ayat} <span class="text-[9px] font-normal text-blue-500 block">• ${lr.time || 'Hari ini'}</span>`;
        } else if (text) {
            text.textContent = 'Halaman 293 • QS. Al-Kahfi: 1';
        }
    }

    function updateLastHafalanCard() {
        const hb = getSavedHafalanBookmark();
        const text = document.getElementById('last-hafalan-text');

        if (hb && text) {
            text.innerHTML = `QS. ${hb.nama}: Ayat ${hb.ayat} <span class="text-[9px] font-normal text-emerald-600 block">• ${hb.time || 'Hari ini'}</span>`;
        } else if (text) {
            text.textContent = 'QS. Al-Mulk: Ayat 1';
        }
    }

    function resumeLastRead() {
        const lr = getSavedBookmark();
        if (lr && lr.nomor) {
            if (lr.mode === 'mushaf' && lr.page) {
                currentMushafPage = lr.page;
                openSurahReader(lr.nomor, lr.ayat || 1, 'mushaf');
            } else {
                openSurahReader(lr.nomor, lr.ayat || 1, lr.mode || 'ayat');
            }
        } else {
            openSurahReader(18, 1, 'mushaf'); // Default Al-Kahfi
        }
    }

    function resumeLastHafalan() {
        const hb = getSavedHafalanBookmark();
        if (hb && hb.nomor) {
            openSurahReader(hb.nomor, hb.ayat || 1, 'hafalan');
        } else {
            openSurahReader(67, 1, 'hafalan'); // Default Al-Mulk
        }
    }

    function closeSurahReader() {
        stopTikrar();
        const audio = document.getElementById('global-quran-audio');
        if (audio) {
            audio.pause();
            isPlayingAudio = false;
        }

        document.getElementById('view-surah-reader').classList.add('hidden');
        document.getElementById('view-surah-list').classList.remove('hidden');
        document.getElementById('quran-header-title').textContent = 'Al-Qur\'an Digital';
        document.getElementById('quran-header-sub').textContent = '114 Surah • Mushaf Madinah (604 Halaman)';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── GENERAL UTILS & EVENT HANDLERS ──────────────────────
    function adjustFontSize(delta) {
        arabicFontSize = Math.max(18, Math.min(38, arabicFontSize + delta));
        document.querySelectorAll('.arabic-text').forEach(el => {
            el.style.fontSize = `${arabicFontSize}px`;
        });
        const mushafPageBody = document.getElementById('mushaf-page-body');
        if (mushafPageBody) mushafPageBody.style.fontSize = `${arabicFontSize + 1}px`;
        document.querySelectorAll('.hafalan-arabic-content').forEach(el => {
            el.style.fontSize = `${arabicFontSize + 1}px`;
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
                ? 'px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold press-bounce'
                : 'px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold press-bounce';
        }
    }

    function toggleLatin() {
        showLatin = !showLatin;
        document.querySelectorAll('.latin-box').forEach(el => {
            el.classList.toggle('hidden', !showLatin);
        });
        const btn = document.getElementById('btn-toggle-latin');
        if (btn) {
            btn.className = showLatin 
                ? 'px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold press-bounce'
                : 'px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold press-bounce';
        }
    }

    function toggleSurahAudio() {
        const audio = document.getElementById('global-quran-audio');
        if (!audio || !audio.src) return;

        stopTikrar();

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
            if (text) text.textContent = 'Putar Audio Surah';
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function playAyatAudio(url, btn) {
        if (!url) return;
        stopTikrar();
        const audio = document.getElementById('global-quran-audio');
        audio.src = url;
        audio.play();
        AndroidUI.toast('Memutar audio ayat...', 'info', 1500);
    }

    function navigateSurah(delta) {
        const next = currentSurahNumber + delta;
        if (next >= 1 && next <= 114) {
            openSurahReader(next, 1, currentReaderMode);
        }
    }

    function convertToArabicNumerals(number) {
        const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return String(number).split('').map(d => arabicDigits[parseInt(d)] || d).join('');
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

    // Touch Swipe Gesture for Page Turning on Mobile
    let touchStartX = 0;
    let touchEndX = 0;
    document.addEventListener('DOMContentLoaded', () => {
        loadSurahCatalog();
        initReadingTimer();
        updateLastReadCard();
        updateLastHafalanCard();

        const mushafCard = document.getElementById('mushaf-page-card');
        if (mushafCard) {
            mushafCard.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            mushafCard.addEventListener('touchend', e => {
                touchEndX = e.changedTouches[0].screenX;
                handleMushafSwipe();
            }, { passive: true });
        }
    });

    function handleMushafSwipe() {
        const threshold = 50; // px
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                // Swiped right -> Turn to Previous page (Mundur / Belok Kanan)
                navigateMushafPage(-1);
            } else {
                // Swiped left -> Turn to Next page (Maju / Belok Kiri)
                navigateMushafPage(1);
            }
        }
    }
</script>
