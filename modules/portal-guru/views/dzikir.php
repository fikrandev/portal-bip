<?php
/**
 * Dzikir Pagi & Petang View (Al-Ma'tsurat Sugro & Kubro)
 * Features interactive digital tasbih counters, audio/vibration feedback, and auto-check integration for Ibadah Harian.
 */
?>

<!-- Top App Bar -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 id="dzikir-header-title" class="font-black text-slate-900 text-base leading-tight">Dzikir Al-Ma'tsurat</h2>
            <p id="dzikir-header-sub" class="text-[10px] text-slate-400 font-medium">Dzikir Pagi & Petang Sugro / Kubro</p>
        </div>
    </div>

    <!-- Right Completion Status -->
    <div id="dzikir-progress-badge" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
        <span id="dzikir-count-text">0 / 18 Selesai</span>
    </div>
</div>

<div class="p-4 space-y-3.5">

    <!-- Mode Selector (Pagi vs Petang & Sugro vs Kubro) -->
    <div class="bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 space-y-2.5">
        
        <!-- Pagi vs Petang Switcher -->
        <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-2xl text-xs font-bold">
            <button type="button" onclick="setDzikirMode('pagi')" id="btn-mode-pagi" class="py-2 rounded-xl bg-amber-500 text-white shadow-sm flex items-center justify-center gap-1.5 transition-all press-bounce">
                <span>🌅</span> Dzikir Pagi
            </button>
            <button type="button" onclick="setDzikirMode('petang')" id="btn-mode-petang" class="py-2 rounded-xl text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5 transition-all press-bounce">
                <span>🌇</span> Dzikir Petang
            </button>
        </div>

        <!-- Sugro vs Kubro Switcher -->
        <div class="flex items-center justify-between pt-1 border-t border-slate-50 text-xs">
            <span class="text-[11px] font-bold text-slate-500">Versi Wirid:</span>
            <div class="flex items-center gap-1 bg-slate-100 p-0.5 rounded-xl text-[11px] font-bold">
                <button type="button" onclick="setDzikirVersion('sugro')" id="btn-ver-sugro" class="px-3 py-1 rounded-lg bg-white text-slate-900 shadow-xs transition-all">
                    Sugro (Ringkas)
                </button>
                <button type="button" onclick="setDzikirVersion('kubro')" id="btn-ver-kubro" class="px-3 py-1 rounded-lg text-slate-500 hover:text-slate-900 transition-all">
                    Kubro (Lengkap)
                </button>
            </div>
        </div>

    </div>

    <!-- Progress Banner -->
    <div class="bg-gradient-to-r from-purple-700 via-indigo-700 to-purple-800 rounded-3xl p-4 text-white shadow-lg shadow-purple-700/20 relative overflow-hidden flex items-center justify-between">
        <div class="relative z-10 min-w-0">
            <p id="banner-waktu-text" class="text-[10px] text-purple-200 font-bold uppercase tracking-wider">Waktu Pagi (Ba'da Subuh)</p>
            <h3 id="banner-title-text" class="text-sm font-black mt-0.5">Al-Ma'tsurat Wazhifah Sughra</h3>
            <p class="text-[10px] text-purple-100/80 mt-0.5">Tap tombol tasbih pada setiap bacaan dzikir.</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl shrink-0 shadow-inner">
            📿
        </div>
    </div>

    <!-- Quick Tools: Selesaikan Semua & Reset -->
    <div class="flex items-center justify-between px-1 text-xs">
        <button type="button" onclick="resetAllCounters()" class="text-[11px] font-bold text-slate-500 hover:text-rose-600 flex items-center gap-1">
            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset Hitungan
        </button>
        <button type="button" onclick="autoCompleteDzikir()" class="px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-[11px] flex items-center gap-1 press-bounce">
            <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Selesaikan Semua
        </button>
    </div>

    <!-- Dzikir Items Container -->
    <div id="dzikir-items-container" class="space-y-3">
        <!-- Dynamically rendered via JS -->
    </div>

    <!-- Finish Action Button -->
    <div class="pt-2">
        <button type="button" id="btn-save-dzikir" onclick="finishDzikirSession(this)" class="w-full py-3.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-purple-600/30 flex items-center justify-center gap-2 press-bounce">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Simpan Ibadah Dzikir Hari Ini
        </button>
    </div>

</div>

<script>
    // State Store
    let currentMode = 'pagi'; // 'pagi' or 'petang'
    let currentVersion = 'sugro'; // 'sugro' or 'kubro'
    let dzikirCounters = {};

    // ── DATASET AL-MA'TSURAT (SUGRO & KUBRO) ──────────────────
    const DZIKIR_DATA = [
        {
            id: 'taawudz',
            title: 'Ta\'awudz & Surat Al-Fatihah',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab: 'أَعُوذُ بِاللَّهِ مِنَ الشَّيْطَانِ الرَّجِيمِ • بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ • الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ...',
            latin: 'A\'udzu billahi minasy-syaithanir-rajim. Bismillahir-rahmanir-rahim. Alhamdulillahi rabbil \'alamin...',
            arti: 'Aku berlindung kepada Allah dari godaan syetan yang terkutuk. Dengan menyebut nama Allah Yang Maha Pengasih lagi Maha Penyayang...'
        },
        {
            id: 'baqarah_1_5',
            title: 'Surat Al-Baqarah: 1-5',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab: 'الَمَ • ذَٰلِكَ الْكِتَابُ لَا رَيْبَ ۛ فِيهِ ۛ هُدًى لِّلْمُتَّقِينَ • الَّذِينَ يُؤْمِنُونَ بِالْغَيْبِ وَيُقِيمُونَ الصَّلَاةَ وَمِمَّا رَزَقْنَاهُمْ يُنفِقُونَ...',
            latin: 'Alif lam mim. Dzalikal-kitabu la raiba fihi hudal-lil-muttaqin...',
            arti: 'Alif Laam Miim. Kitab (Al Quran) ini tidak ada keraguan padanya; petunjuk bagi mereka yang bertakwa...'
        },
        {
            id: 'ayat_kursi',
            title: 'Ayat Kursi (Al-Baqarah: 255)',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab: 'اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ ۚ لَّهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ...',
            latin: 'Allahu la ilaha illa huwal-hayyul-qayyum, la ta\'khudzuhu sinatuw-wa la naum...',
            arti: 'Allah, tidak ada Tuhan (yang berhak disembah) melainkan Dia Yang Hidup kekal lagi terus menerus mengurus (makhluk-Nya)...'
        },
        {
            id: 'baqarah_284_286',
            title: 'Surat Al-Baqarah: 284-286',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab: 'آمَنَ الرَّسُولُ بِمَا أُنزِلَ إِلَيْهِ مِن رَّبِّهِ وَالْمُؤْمِنُونَ ۚ كُلٌّ آمَنَ بِاللَّهِ وَمَلَائِكَتِهِ وَكُتُبِهِ وَرُسُلِهِ...',
            latin: 'Amanar-rasulu bima unzila ilaihi mir-rabbihi wal-mu\'minun...',
            arti: 'Rasul telah beriman kepada Al Quran yang diturunkan kepadanya dari Tuhannya, demikian pula orang-orang yang beriman...'
        },
        {
            id: 'muawwidzatain',
            title: 'Surat Al-Ikhlas, Al-Falaq, An-Nas',
            target: 3,
            pagi: true, petang: true, kubro: false,
            arab: 'قُلْ هُوَ اللَّهُ أَحَدٌ • قُلْ أَعُوذُ بِرَبِّ الْفَلَقِ • قُلْ أَعُوذُ بِرَبِّ النَّاسِ',
            latin: 'Qul huwallahu ahad... Qul a\'udzu birabbil-falaq... Qul a\'udzu birabbin-nas... (Dibaca 3x)',
            arti: 'Katakanlah: Dialah Allah Yang Maha Esa... Katakanlah: Aku berlindung kepada Tuhan yang menguasai subuh... Katakanlah: Aku berlindung kepada Tuhan manusia...'
        },
        {
            id: 'doa_pagi_petang',
            title: 'Doa Memasuki Waktu Pagi / Petang',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab_pagi: 'أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لاَ إِلَـهَ إِلاَّ اللهُ وَحْدَهُ لاَ شَرِيْكَ لَهُ...',
            arab_petang: 'أَمْسَيْنَا وَأَمْسَى الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لاَ إِلَـهَ إِلاَّ اللهُ وَحْدَهُ لاَ شَرِيْكَ لَهُ...',
            latin: 'Ashbahna wa ashbahal-mulku lillah (Amsaina wa amsal-mulku lillah), wal-hamdulillahi la ilaha illallahu wahdahu la syarika lah...',
            arti: 'Kami telah memasuki waktu pagi/petang dan kerajaan hanya milik Allah, segala puji bagi Allah, tiada Tuhan selain Allah Yang Maha Esa tiada sekutu bagi-Nya...'
        },
        {
            id: 'ashbahna_ala_fithrah',
            title: 'Ikrar Di Atas Fithrah Islam',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab: 'أَصْبَحْنَا عَلَى فِطْرَةِ الإِسْلاَمِ، وَعَلَى كَلِمَةِ الإِخْلاَصِ، وَعَلَى دِينِ نَبِيِّنَا مُحَمَّدٍ صَلَّى اللهُ عَلَيْهِ وَسَلَّمَ...',
            latin: 'Ashbahna \'ala fithratil-Islam, wa \'ala kalimatil-ikhlas, wa \'ala dini nabiyyina Muhammadin shallallahu \'alaihi wa sallam...',
            arti: 'Kami memasuki waktu pagi/petang di atas fithrah Islam, di atas kalimat ikhlas, dan di atas agama Nabi kami Muhammad SAW...'
        },
        {
            id: 'sayyidul_istighfar',
            title: 'Sayyidul Istighfar (Induk Istighfar)',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab: 'اللَّهُمَّ أَنْتَ رَبِّي لاَ إِلَهَ إِلاَّ أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَى عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ...',
            latin: 'Allahumma anta rabbi la ilaha illa anta, khalaqtani wa ana \'abduka, wa ana \'ala \'ahdika wa wa\'dika mastatha\'tu...',
            arti: 'Ya Allah, Engkau adalah Tuhanku, tiada Tuhan selain Engkau. Engkaulah yang menciptakanku dan aku adalah hamba-Mu...'
        },
        {
            id: 'doa_kesehatan',
            title: 'Doa Perlindungan Kesehatan & Kekufuran',
            target: 3,
            pagi: true, petang: true, kubro: false,
            arab: 'اللَّهُمَّ عَافِنِي فِي بَدَنِي، اللَّهُمَّ عَافِنِي فِي سَمْعِي، اللَّهُمَّ عَافِنِي فِي بَصَرِي، لاَ إِلَهَ إِلاَّ أَنْتَ (٣x)',
            latin: 'Allahumma \'afini fi badani, Allahumma \'afini fi sam\'i, Allahumma \'afini fi bashari, la ilaha illa anta. (Dibaca 3x)',
            arti: 'Ya Allah, sehatkanlah badanku. Ya Allah, sehatkanlah pendengaranku. Ya Allah, sehatkanlah penglihatanku, tiada Tuhan selain Engkau.'
        },
        {
            id: 'hasbiyallah',
            title: 'Doa Kecukupan Hidup (7x)',
            target: 7,
            pagi: true, petang: true, kubro: false,
            arab: 'حَسْبِيَ اللَّهُ لاَ إِلَـهَ إِلاَّ هُوَ عَلَيْهِ تَوَكَّلْتُ وَهُوَ رَبُّ الْعَرْشِ الْعَظِيمِ (٧x)',
            latin: 'Hasbiyallahu la ilaha illa huwa \'alaihi tawakkaltu wa huwa rabbul-\'arsyil-\'azhim. (Dibaca 7x)',
            arti: 'Cukuplah Allah bagiku; tidak ada Tuhan selain Dia. Hanya kepada-Nya aku bertawakkal dan Dia adalah Tuhan yang memiliki \'Arsy yang agung.'
        },
        {
            id: 'bismillahilladzi',
            title: 'Doa Penjaga Dari Segala Marabahaya',
            target: 3,
            pagi: true, petang: true, kubro: false,
            arab: 'بِسْمِ اللَّهِ الَّذِي لاَ يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الأَرْضِ وَلاَ فِي السَّمَاءِ وَهُوَ السَّمِيعُ الْعَلِيمُ (٣x)',
            latin: 'Bismillahilladzi la yadhurru ma\'asmihi syai\'un fil-ardhi wa la fis-sama\'i wa huwas-sami\'ul-\'alim. (Dibaca 3x)',
            arti: 'Dengan nama Allah yang bila disebut, segala sesuatu di bumi dan di langit tidak akan berbahaya, Dia-lah Yang Maha Mendengar lagi Maha Mengetahui.'
        },
        {
            id: 'radhitu_billah',
            title: 'Keridhaan Kepada Allah & Rasul',
            target: 3,
            pagi: true, petang: true, kubro: false,
            arab: 'رَضِيتُ بِاللَّهِ رَبًّا، وَبِالإِسْلاَمِ دِينًا، وَبِمُحَمَّدٍ نَبِيًّا وَرَسُولاً (٣x)',
            latin: 'Radhitu billahi rabba, wa bil-Islami dina, wa bi Muhammadin nabiyyaw-wa rasula. (Dibaca 3x)',
            arti: 'Aku rela Allah sebagai Tuhanku, Islam sebagai agamaku dan Muhammad sebagai Nabi dan Rasulku.'
        },
        {
            id: 'ya_hayyu_ya_qayyum',
            title: 'Doa Perbaikan Segala Urusan',
            target: 1,
            pagi: true, petang: true, kubro: false,
            arab: 'يَا حَيُّ يَا قَيُّومُ بِرَحْمَتِكَ أَسْتَغِيثُ، أَصْلِحْ لِي شَأْنِي كُلَّهُ وَلاَ تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ',
            latin: 'Ya Hayyu Ya Qayyum, bi rahmatika astaghits, ashlih li sya\'ni kullahu wa la takilni ila nafsi tharfata \'ain.',
            arti: 'Wahai Yang Maha Hidup, wahai Yang Berdiri Sendiri, dengan rahmat-Mu aku memohon pertolongan, perbaikilah urusanku seluruhnya...'
        },
        {
            id: 'tasbih_tahmid_100',
            title: 'Tasbih & Tahmid (100x)',
            target: 100,
            pagi: true, petang: true, kubro: false,
            arab: 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ (١٠٠x)',
            latin: 'Subhanallahi wa bihamdihi. (Dibaca 100x)',
            arti: 'Maha Suci Allah dan segala puji bagi-Nya.'
        },
        {
            id: 'tahlil_10',
            title: 'Kalimat Tauhid & Keagungan Allah',
            target: 10,
            pagi: true, petang: true, kubro: false,
            arab: 'لاَ إِلَهَ إِلاَّ اللَّهُ وَحْدَهُ لاَ شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ (١٠x)',
            latin: 'La ilaha illallahu wahdahu la syarika lah, lahul-mulku wa lahul-hamdu wa huwa \'ala kulli syai\'in qadir. (Dibaca 10x)',
            arti: 'Tiada Tuhan selain Allah semata, tiada sekutu bagi-Nya. Bagi-Nya kerajaan dan puji-pujian, dan Dia Maha Kuasa atas segala sesuatu.'
        },
        {
            id: 'istighfar_100',
            title: 'Istighfar Taubat Harian (100x)',
            target: 100,
            pagi: true, petang: true, kubro: false,
            arab: 'أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ (١٠٠x)',
            latin: 'Astaghfirullah wa atubu ilaih. (Dibaca 100x)',
            arti: 'Aku memohon ampunan kepada Allah dan bertaubat kepada-Nya.'
        },
        {
            id: 'sholawat_10',
            title: 'Sholawat Atas Nabi Muhammad SAW',
            target: 10,
            pagi: true, petang: true, kubro: false,
            arab: 'اللَّهُمَّ صَلِّ عَلَى سَيِّدِنَا مُحَمَّدٍ وَعَلَى آلِ سَيِّدِنَا مُحَمَّدٍ (١٠x)',
            latin: 'Allahumma shalli \'ala sayyidina Muhammadin wa \'ala ali sayyidina Muhammad. (Dibaca 10x)',
            arti: 'Ya Allah, limpahkanlah rahmat dan kesejahteraan atas junjungan kami Nabi Muhammad beserta keluarganya.'
        },
        // Kubro Extended Verses
        {
            id: 'ali_imran_kubro',
            title: 'Surat Ali \'Imran: 26-27 (Kubro)',
            target: 1,
            pagi: true, petang: true, kubro: true,
            arab: 'قُلِ اللَّهُمَّ مَالِكَ الْمُلْكِ تُؤْتِي الْمُلْكَ مَن تَشَاءُ وَتَنزِعُ الْمُلْكَ مِمَّن تَشَاءُ...',
            latin: 'Qulillahumma malikal-mulki tu\'til-mulka man tasya\'u wa tanzi\'ul-mulka mimman tasya\'...',
            arti: 'Katakanlah: Wahai Tuhan Yang mempunyai kerajaan, Engkau berikan kerajaan kepada orang yang Engkau kehendaki...'
        },
        {
            id: 'doa_rabithah_kubro',
            title: 'Doa Rabithah & Ukhuwah (Kubro)',
            target: 1,
            pagi: true, petang: true, kubro: true,
            arab: 'اللَّهُمَّ إِنَّكَ تَعْلَمُ أَنَّ هَذِهِ الْقُلُوبَ قَدِ اجْتَمَعَتْ عَلَى مَحَبَّتِكَ، وَالْتَقَتْ عَلَى طَاعَتِكَ...',
            latin: 'Allahumma innaka ta\'lamu anna hadzihil-qulub qadijtama\'at \'ala mahabbatika...',
            arti: 'Ya Allah, sesungguhnya Engkau mengetahui bahwa hati-hati ini telah berkumpul di atas cinta-Mu dan ketaatan kepada-Mu...'
        }
    ];

    function initDzikir() {
        // Auto detect morning / evening based on device hour
        const hr = new Date().getHours();
        if (hr >= 15) {
            setDzikirMode('petang');
        } else {
            setDzikirMode('pagi');
        }
    }

    function setDzikirMode(mode) {
        currentMode = mode;
        const btnP = document.getElementById('btn-mode-pagi');
        const btnPe = document.getElementById('btn-mode-petang');
        const bannerWaktu = document.getElementById('banner-waktu-text');

        if (mode === 'pagi') {
            btnP.className = 'py-2 rounded-xl bg-amber-500 text-white shadow-sm flex items-center justify-center gap-1.5 transition-all press-bounce';
            btnPe.className = 'py-2 rounded-xl text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5 transition-all press-bounce';
            bannerWaktu.textContent = 'Waktu Pagi (Ba\'da Subuh - Terbit Fajar)';
        } else {
            btnPe.className = 'py-2 rounded-xl bg-indigo-600 text-white shadow-sm flex items-center justify-center gap-1.5 transition-all press-bounce';
            btnP.className = 'py-2 rounded-xl text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5 transition-all press-bounce';
            bannerWaktu.textContent = 'Waktu Petang (Ba\'da Ashar - Terbenam Matahari)';
        }

        renderDzikirList();
    }

    function setDzikirVersion(ver) {
        currentVersion = ver;
        const btnS = document.getElementById('btn-ver-sugro');
        const btnK = document.getElementById('btn-ver-kubro');
        const bannerTitle = document.getElementById('banner-title-text');

        if (ver === 'sugro') {
            btnS.className = 'px-3 py-1 rounded-lg bg-white text-slate-900 shadow-xs transition-all';
            btnK.className = 'px-3 py-1 rounded-lg text-slate-500 hover:text-slate-900 transition-all';
            bannerTitle.textContent = 'Al-Ma\'tsurat Wazhifah Sughra';
        } else {
            btnK.className = 'px-3 py-1 rounded-lg bg-white text-slate-900 shadow-xs transition-all';
            btnS.className = 'px-3 py-1 rounded-lg text-slate-500 hover:text-slate-900 transition-all';
            bannerTitle.textContent = 'Al-Ma\'tsurat Wazhifah Kubra';
        }

        renderDzikirList();
    }

    function renderDzikirList() {
        const container = document.getElementById('dzikir-items-container');
        if (!container) return;

        // Filter items based on version
        const items = DZIKIR_DATA.filter(d => {
            if (currentVersion === 'sugro' && d.kubro) return false;
            return true;
        });

        let html = '';
        items.forEach((d, idx) => {
            const count = dzikirCounters[d.id] || 0;
            const isCompleted = count >= d.target;
            const arabText = (d.id === 'doa_pagi_petang') ? (currentMode === 'pagi' ? d.arab_pagi : d.arab_petang) : d.arab;

            html += `
                <div class="dzikir-card bg-white rounded-3xl p-4 shadow-sm border ${isCompleted ? 'border-emerald-300 ring-2 ring-emerald-100 bg-emerald-50/15' : 'border-slate-100'} space-y-3 transition-all" id="card-dzikir-${d.id}">
                    <!-- Top Sub-bar -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-50 text-[11px]">
                        <div class="flex items-center gap-1.5 font-bold text-slate-700 min-w-0">
                            <span class="w-6 h-6 rounded-lg ${isCompleted ? 'bg-emerald-500 text-white font-bold' : 'bg-purple-50 text-purple-700 border border-purple-100'} flex items-center justify-center font-mono text-[10px] shrink-0">${idx + 1}</span>
                            <span class="truncate ${isCompleted ? 'text-emerald-800' : 'text-slate-800'}">${d.title}</span>
                        </div>

                        <!-- Interactive Tasbih Counter Button -->
                        <button type="button" onclick="tapTasbihCounter('${d.id}', ${d.target})" class="tasbih-btn-${d.id} px-3 py-1.5 rounded-2xl ${isCompleted ? 'bg-emerald-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200'} font-mono font-black text-xs flex items-center gap-1.5 press-bounce shrink-0">
                            ${isCompleted ? '<span>✓ Selesai</span>' : `<span>📿 ${count} / ${d.target}</span>`}
                        </button>
                    </div>

                    <!-- Arabic Text -->
                    <div class="pt-1">
                        <p class="font-arabic text-right text-slate-950 font-bold leading-loose tracking-wide text-xl sm:text-2xl">
                            ${arabText}
                        </p>
                    </div>

                    <!-- Latin Transliteration -->
                    <p class="text-xs text-blue-600 font-medium leading-relaxed">
                        ${d.latin}
                    </p>

                    <!-- Indonesian Translation -->
                    <div class="pt-1 border-t border-slate-50 text-xs text-slate-600 leading-relaxed font-normal">
                        ${d.arti}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        if (window.lucide) window.lucide.createIcons();
        updateOverallProgress();
    }

    function tapTasbihCounter(id, target) {
        if (!dzikirCounters[id]) dzikirCounters[id] = 0;
        
        if (dzikirCounters[id] < target) {
            dzikirCounters[id]++;
        } else {
            dzikirCounters[id] = 0; // Reset if tapped after completed
        }

        if (navigator.vibrate) navigator.vibrate(dzikirCounters[id] === target ? [40, 30, 40] : [20]);

        renderDzikirList();
    }

    function updateOverallProgress() {
        const items = DZIKIR_DATA.filter(d => {
            if (currentVersion === 'sugro' && d.kubro) return false;
            return true;
        });

        let completedCount = 0;
        items.forEach(d => {
            if ((dzikirCounters[d.id] || 0) >= d.target) completedCount++;
        });

        const countText = document.getElementById('dzikir-count-text');
        const badge = document.getElementById('dzikir-progress-badge');

        if (countText) countText.textContent = `${completedCount} / ${items.length} Selesai`;

        if (completedCount === items.length && items.length > 0) {
            if (badge) badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-600 text-white shadow-xs';
        } else {
            if (badge) badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200';
        }
    }

    function autoCompleteDzikir() {
        const items = DZIKIR_DATA.filter(d => {
            if (currentVersion === 'sugro' && d.kubro) return false;
            return true;
        });

        items.forEach(d => {
            dzikirCounters[d.id] = d.target;
        });

        AndroidUI.toast('Semua bacaan dzikir telah diselesaikan!', 'success');
        if (navigator.vibrate) navigator.vibrate(30);
        renderDzikirList();
    }

    function resetAllCounters() {
        dzikirCounters = {};
        AndroidUI.toast('Hitungan dzikir direset ke 0', 'info');
        renderDzikirList();
    }

    function finishDzikirSession(btn) {
        AndroidUI.setButtonLoading(btn, 'Menyimpan Mutaba\'ah...');

        setTimeout(() => {
            AndroidUI.resetButton(btn);

            // Update Ibadah Harian in localStorage
            let ibadah = {
                gender: 'P',
                sholat: { subuh: { checked: false }, dzuhur: { checked: false }, ashar: { checked: false }, maghrib: { checked: false }, isya: { checked: false } },
                tilawah: { checked: false, text: '' },
                dzikir: { istighfar: true, sholawat: true },
                tadabbur: { checked: false, text: '' }
            };

            const existing = localStorage.getItem('portal_guru_ibadah_today');
            if (existing) {
                try {
                    ibadah = Object.assign(ibadah, JSON.parse(existing));
                } catch (e) {}
            }
            ibadah.dzikir.istighfar = true;
            ibadah.dzikir.sholawat = true;
            localStorage.setItem('portal_guru_ibadah_today', JSON.stringify(ibadah));

            AndroidUI.success({
                title: 'Alhamdulillah! 📿🎉',
                subtitle: `Dzikir ${currentMode === 'pagi' ? 'Pagi' : 'Petang'} Selesai`,
                message: `Mutaba'ah dzikir Al-Ma'tsurat <strong>${currentVersion === 'sugro' ? 'Sugro' : 'Kubro'}</strong> telah tersimpan.<br>Ibadah <strong>Dzikir & Sholawat</strong> di Beranda telah otomatis dicentang selesai! 🤲`,
                buttonText: 'Kembali ke Beranda',
                onOk: () => {
                    window.location.href = '<?= url("mobile") ?>';
                }
            });
        }, 500);
    }

    document.addEventListener('DOMContentLoaded', () => {
        initDzikir();
    });
</script>
