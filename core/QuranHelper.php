<?php
/**
 * Portal BIP - Quran Helper
 * 
 * Provides reference data for 114 Surahs, Juz, Iqro levels,
 * and formatting utilities for Qur'an Siswa modules.
 */

class QuranHelper
{
    /**
     * Complete list of 114 Surahs
     * Format: [nomor, nama_latin, nama_arab, jumlah_ayat, juz_awal]
     */
    private static array $surahs = [
        1 => ['nomor' => 1, 'nama' => 'Al-Fatihah', 'arab' => 'الفاتحة', 'ayat' => 7, 'juz' => 1],
        2 => ['nomor' => 2, 'nama' => 'Al-Baqarah', 'arab' => 'البقرة', 'ayat' => 286, 'juz' => 1],
        3 => ['nomor' => 3, 'nama' => 'Ali \'Imran', 'arab' => 'آل عمران', 'ayat' => 200, 'juz' => 3],
        4 => ['nomor' => 4, 'nama' => 'An-Nisa\'', 'arab' => 'النساء', 'ayat' => 176, 'juz' => 4],
        5 => ['nomor' => 5, 'nama' => 'Al-Ma\'idah', 'arab' => 'المائدة', 'ayat' => 120, 'juz' => 6],
        6 => ['nomor' => 6, 'nama' => 'Al-An\'am', 'arab' => 'الأنعام', 'ayat' => 165, 'juz' => 7],
        7 => ['nomor' => 7, 'nama' => 'Al-A\'raf', 'arab' => 'الأعراف', 'ayat' => 206, 'juz' => 8],
        8 => ['nomor' => 8, 'nama' => 'Al-Anfal', 'arab' => 'الأنفال', 'ayat' => 75, 'juz' => 9],
        9 => ['nomor' => 9, 'nama' => 'At-Taubah', 'arab' => 'التوبة', 'ayat' => 129, 'juz' => 10],
        10 => ['nomor' => 10, 'nama' => 'Yunus', 'arab' => 'يونس', 'ayat' => 109, 'juz' => 11],
        11 => ['nomor' => 11, 'nama' => 'Hud', 'arab' => 'هود', 'ayat' => 123, 'juz' => 11],
        12 => ['nomor' => 12, 'nama' => 'Yusuf', 'arab' => 'يوسف', 'ayat' => 111, 'juz' => 12],
        13 => ['nomor' => 13, 'nama' => 'Ar-Ra\'d', 'arab' => 'الرعد', 'ayat' => 43, 'juz' => 13],
        14 => ['nomor' => 14, 'nama' => 'Ibrahim', 'arab' => 'إبراهيم', 'ayat' => 52, 'juz' => 13],
        15 => ['nomor' => 15, 'nama' => 'Al-Hijr', 'arab' => 'الحجر', 'ayat' => 99, 'juz' => 14],
        16 => ['nomor' => 16, 'nama' => 'An-Nahl', 'arab' => 'النحل', 'ayat' => 128, 'juz' => 14],
        17 => ['nomor' => 17, 'nama' => 'Al-Isra\'', 'arab' => 'الإسراء', 'ayat' => 111, 'juz' => 15],
        18 => ['nomor' => 18, 'nama' => 'Al-Kahf', 'arab' => 'الكهف', 'ayat' => 110, 'juz' => 15],
        19 => ['nomor' => 19, 'nama' => 'Maryam', 'arab' => 'مريم', 'ayat' => 98, 'juz' => 16],
        20 => ['nomor' => 20, 'nama' => 'Ta-Ha', 'arab' => 'طه', 'ayat' => 135, 'juz' => 16],
        21 => ['nomor' => 21, 'nama' => 'Al-Anbiya\'', 'arab' => 'الأنبياء', 'ayat' => 112, 'juz' => 17],
        22 => ['nomor' => 22, 'nama' => 'Al-Hajj', 'arab' => 'الحج', 'ayat' => 78, 'juz' => 17],
        23 => ['nomor' => 23, 'nama' => 'Al-Mu\'minun', 'arab' => 'المؤمنون', 'ayat' => 118, 'juz' => 18],
        24 => ['nomor' => 24, 'nama' => 'An-Nur', 'arab' => 'النور', 'ayat' => 64, 'juz' => 18],
        25 => ['nomor' => 25, 'nama' => 'Al-Furqan', 'arab' => 'الفرقان', 'ayat' => 77, 'juz' => 18],
        26 => ['nomor' => 26, 'nama' => 'Asy-Syu\'ara\'', 'arab' => 'الشعراء', 'ayat' => 227, 'juz' => 19],
        27 => ['nomor' => 27, 'nama' => 'An-Naml', 'arab' => 'النمل', 'ayat' => 93, 'juz' => 19],
        28 => ['nomor' => 28, 'nama' => 'Al-Qashas', 'arab' => 'القصص', 'ayat' => 88, 'juz' => 20],
        29 => ['nomor' => 29, 'nama' => 'Al-\'Ankabut', 'arab' => 'العنكبوت', 'ayat' => 69, 'juz' => 20],
        30 => ['nomor' => 30, 'nama' => 'Ar-Rum', 'arab' => 'الروم', 'ayat' => 60, 'juz' => 21],
        31 => ['nomor' => 31, 'nama' => 'Luqman', 'arab' => 'لقمان', 'ayat' => 34, 'juz' => 21],
        32 => ['nomor' => 32, 'nama' => 'As-Sajdah', 'arab' => 'السجدة', 'ayat' => 30, 'juz' => 21],
        33 => ['nomor' => 33, 'nama' => 'Al-Ahzab', 'arab' => 'الأحزاب', 'ayat' => 73, 'juz' => 21],
        34 => ['nomor' => 34, 'nama' => 'Saba\'', 'arab' => 'سبأ', 'ayat' => 54, 'juz' => 22],
        35 => ['nomor' => 35, 'nama' => 'Fathir', 'arab' => 'فاطر', 'ayat' => 45, 'juz' => 22],
        36 => ['nomor' => 36, 'nama' => 'Ya-Sin', 'arab' => 'يس', 'ayat' => 83, 'juz' => 22],
        37 => ['nomor' => 37, 'nama' => 'Ash-Shaffat', 'arab' => 'الصافات', 'ayat' => 182, 'juz' => 23],
        38 => ['nomor' => 38, 'nama' => 'Shad', 'arab' => 'ص', 'ayat' => 88, 'juz' => 23],
        39 => ['nomor' => 39, 'nama' => 'Az-Zumar', 'arab' => 'الزمر', 'ayat' => 75, 'juz' => 23],
        40 => ['nomor' => 40, 'nama' => 'Ghafir', 'arab' => 'غافر', 'ayat' => 85, 'juz' => 24],
        41 => ['nomor' => 41, 'nama' => 'Fushshilat', 'arab' => 'فصلت', 'ayat' => 54, 'juz' => 24],
        42 => ['nomor' => 42, 'nama' => 'Asy-Syura', 'arab' => 'الشورى', 'ayat' => 53, 'juz' => 25],
        43 => ['nomor' => 43, 'nama' => 'Az-Zukhruf', 'arab' => 'الزخرف', 'ayat' => 89, 'juz' => 25],
        44 => ['nomor' => 44, 'nama' => 'Ad-Dukhan', 'arab' => 'الدخان', 'ayat' => 59, 'juz' => 25],
        45 => ['nomor' => 45, 'nama' => 'Al-Jatsiyah', 'arab' => 'الجاثية', 'ayat' => 37, 'juz' => 25],
        46 => ['nomor' => 46, 'nama' => 'Al-Ahqaf', 'arab' => 'الأحقاف', 'ayat' => 35, 'juz' => 26],
        47 => ['nomor' => 47, 'nama' => 'Muhammad', 'arab' => 'محمد', 'ayat' => 38, 'juz' => 26],
        48 => ['nomor' => 48, 'nama' => 'Al-Fath', 'arab' => 'الفتح', 'ayat' => 29, 'juz' => 26],
        49 => ['nomor' => 49, 'nama' => 'Al-Hujurat', 'arab' => 'الحجرات', 'ayat' => 18, 'juz' => 26],
        50 => ['nomor' => 50, 'nama' => 'Qaf', 'arab' => 'ق', 'ayat' => 45, 'juz' => 26],
        51 => ['nomor' => 51, 'nama' => 'Adz-Dzariyat', 'arab' => 'الذاريات', 'ayat' => 60, 'juz' => 26],
        52 => ['nomor' => 52, 'nama' => 'Ath-Thur', 'arab' => 'الطور', 'ayat' => 49, 'juz' => 27],
        53 => ['nomor' => 53, 'nama' => 'An-Najm', 'arab' => 'النجم', 'ayat' => 62, 'juz' => 27],
        54 => ['nomor' => 54, 'nama' => 'Al-Qamar', 'arab' => 'القمر', 'ayat' => 55, 'juz' => 27],
        55 => ['nomor' => 55, 'nama' => 'Ar-Rahman', 'arab' => 'الرحمن', 'ayat' => 78, 'juz' => 27],
        56 => ['nomor' => 56, 'nama' => 'Al-Waqi\'ah', 'arab' => 'الواقعة', 'ayat' => 96, 'juz' => 27],
        57 => ['nomor' => 57, 'nama' => 'Al-Hadid', 'arab' => 'الحديد', 'ayat' => 29, 'juz' => 27],
        58 => ['nomor' => 58, 'nama' => 'Al-Mujadilah', 'arab' => 'المجادلة', 'ayat' => 22, 'juz' => 28],
        59 => ['nomor' => 59, 'nama' => 'Al-Hasyr', 'arab' => 'الحشر', 'ayat' => 24, 'juz' => 28],
        60 => ['nomor' => 60, 'nama' => 'Al-Mumtahanah', 'arab' => 'الممتحنة', 'ayat' => 13, 'juz' => 28],
        61 => ['nomor' => 61, 'nama' => 'Ash-Shaff', 'arab' => 'الصف', 'ayat' => 14, 'juz' => 28],
        62 => ['nomor' => 62, 'nama' => 'Al-Jumu\'ah', 'arab' => 'الجمعة', 'ayat' => 11, 'juz' => 28],
        63 => ['nomor' => 63, 'nama' => 'Al-Munafiqun', 'arab' => 'المنافقون', 'ayat' => 11, 'juz' => 28],
        64 => ['nomor' => 64, 'nama' => 'At-Taghabun', 'arab' => 'التغابن', 'ayat' => 18, 'juz' => 28],
        65 => ['nomor' => 65, 'nama' => 'Ath-Thalaq', 'arab' => 'الطلاق', 'ayat' => 12, 'juz' => 28],
        66 => ['nomor' => 66, 'nama' => 'At-Tahrim', 'arab' => 'التحريم', 'ayat' => 12, 'juz' => 28],
        67 => ['nomor' => 67, 'nama' => 'Al-Mulk', 'arab' => 'الملك', 'ayat' => 30, 'juz' => 29],
        68 => ['nomor' => 68, 'nama' => 'Al-Qalam', 'arab' => 'القلم', 'ayat' => 52, 'juz' => 29],
        69 => ['nomor' => 69, 'nama' => 'Al-Haqqah', 'arab' => 'الحاقة', 'ayat' => 52, 'juz' => 29],
        70 => ['nomor' => 70, 'nama' => 'Al-Ma\'arij', 'arab' => 'المعارج', 'ayat' => 44, 'juz' => 29],
        71 => ['nomor' => 71, 'nama' => 'Nuh', 'arab' => 'نوح', 'ayat' => 28, 'juz' => 29],
        72 => ['nomor' => 72, 'nama' => 'Al-Jinn', 'arab' => 'الجن', 'ayat' => 28, 'juz' => 29],
        73 => ['nomor' => 73, 'nama' => 'Al-Muzzammil', 'arab' => 'المزمل', 'ayat' => 20, 'juz' => 29],
        74 => ['nomor' => 74, 'nama' => 'Al-Muddatstsir', 'arab' => 'المدثر', 'ayat' => 56, 'juz' => 29],
        75 => ['nomor' => 75, 'nama' => 'Al-Qiyamah', 'arab' => 'القيامة', 'ayat' => 40, 'juz' => 29],
        76 => ['nomor' => 76, 'nama' => 'Al-Insan', 'arab' => 'الإنسان', 'ayat' => 31, 'juz' => 29],
        77 => ['nomor' => 77, 'nama' => 'Al-Mursalat', 'arab' => 'المرسلات', 'ayat' => 50, 'juz' => 29],
        78 => ['nomor' => 78, 'nama' => 'An-Naba\'', 'arab' => 'النبأ', 'ayat' => 40, 'juz' => 30],
        79 => ['nomor' => 79, 'nama' => 'An-Nazi\'at', 'arab' => 'النازعات', 'ayat' => 46, 'juz' => 30],
        80 => ['nomor' => 80, 'nama' => '\'Abasa', 'arab' => 'عبس', 'ayat' => 42, 'juz' => 30],
        81 => ['nomor' => 81, 'nama' => 'At-Takwir', 'arab' => 'التكوير', 'ayat' => 29, 'juz' => 30],
        82 => ['nomor' => 82, 'nama' => 'Al-Infithar', 'arab' => 'الانفطار', 'ayat' => 19, 'juz' => 30],
        83 => ['nomor' => 83, 'nama' => 'Al-Muthaffifin', 'arab' => 'المطففين', 'ayat' => 36, 'juz' => 30],
        84 => ['nomor' => 84, 'nama' => 'Al-Insyiqaq', 'arab' => 'الانشقاق', 'ayat' => 25, 'juz' => 30],
        85 => ['nomor' => 85, 'nama' => 'Al-Buruj', 'arab' => 'البروج', 'ayat' => 22, 'juz' => 30],
        86 => ['nomor' => 86, 'nama' => 'Ath-Thariq', 'arab' => 'الطارق', 'ayat' => 17, 'juz' => 30],
        87 => ['nomor' => 87, 'nama' => 'Al-A\'la', 'arab' => 'الأعلى', 'ayat' => 19, 'juz' => 30],
        88 => ['nomor' => 88, 'nama' => 'Al-Ghasyiyah', 'arab' => 'الغاشية', 'ayat' => 26, 'juz' => 30],
        89 => ['nomor' => 89, 'nama' => 'Al-Fajr', 'arab' => 'الفجر', 'ayat' => 30, 'juz' => 30],
        90 => ['nomor' => 90, 'nama' => 'Al-Balad', 'arab' => 'البلد', 'ayat' => 20, 'juz' => 30],
        91 => ['nomor' => 91, 'nama' => 'Asy-Syams', 'arab' => 'الشمس', 'ayat' => 15, 'juz' => 30],
        92 => ['nomor' => 92, 'nama' => 'Al-Lail', 'arab' => 'الليل', 'ayat' => 21, 'juz' => 30],
        93 => ['nomor' => 93, 'nama' => 'Adh-Dhuha', 'arab' => 'الضحى', 'ayat' => 11, 'juz' => 30],
        94 => ['nomor' => 94, 'nama' => 'Asy-Syarh', 'arab' => 'الشرح', 'ayat' => 8, 'juz' => 30],
        95 => ['nomor' => 95, 'nama' => 'At-Tin', 'arab' => 'التين', 'ayat' => 8, 'juz' => 30],
        96 => ['nomor' => 96, 'nama' => 'Al-\'Alaq', 'arab' => 'العلق', 'ayat' => 19, 'juz' => 30],
        97 => ['nomor' => 97, 'nama' => 'Al-Qadr', 'arab' => 'القدر', 'ayat' => 5, 'juz' => 30],
        98 => ['nomor' => 98, 'nama' => 'Al-Bayyinah', 'arab' => 'البينة', 'ayat' => 8, 'juz' => 30],
        99 => ['nomor' => 99, 'nama' => 'Az-Zalzalah', 'arab' => 'الزلزلة', 'ayat' => 8, 'juz' => 30],
        100 => ['nomor' => 100, 'nama' => 'Al-\'Adiyat', 'arab' => 'العاديات', 'ayat' => 11, 'juz' => 30],
        101 => ['nomor' => 101, 'nama' => 'Al-Qari\'ah', 'arab' => 'القارعة', 'ayat' => 11, 'juz' => 30],
        102 => ['nomor' => 102, 'nama' => 'At-Takatsur', 'arab' => 'التكاثر', 'ayat' => 8, 'juz' => 30],
        103 => ['nomor' => 103, 'nama' => 'Al-\'Ashr', 'arab' => 'العصر', 'ayat' => 3, 'juz' => 30],
        104 => ['nomor' => 104, 'nama' => 'Al-Humazah', 'arab' => 'الهمزة', 'ayat' => 9, 'juz' => 30],
        105 => ['nomor' => 105, 'nama' => 'Al-Fil', 'arab' => 'الفيل', 'ayat' => 5, 'juz' => 30],
        106 => ['nomor' => 106, 'nama' => 'Quraisy', 'arab' => 'قريش', 'ayat' => 4, 'juz' => 30],
        107 => ['nomor' => 107, 'nama' => 'Al-Ma\'un', 'arab' => 'الماعون', 'ayat' => 7, 'juz' => 30],
        108 => ['nomor' => 108, 'nama' => 'Al-Kautsar', 'arab' => 'الكوثر', 'ayat' => 3, 'juz' => 30],
        109 => ['nomor' => 109, 'nama' => 'Al-Kafirun', 'arab' => 'الكافرون', 'ayat' => 6, 'juz' => 30],
        110 => ['nomor' => 110, 'nama' => 'An-Nashr', 'arab' => 'النصر', 'ayat' => 3, 'juz' => 30],
        111 => ['nomor' => 111, 'nama' => 'Al-Lahab', 'arab' => 'اللهب', 'ayat' => 5, 'juz' => 30],
        112 => ['nomor' => 112, 'nama' => 'Al-Ikhlas', 'arab' => 'الإخلاص', 'ayat' => 4, 'juz' => 30],
        113 => ['nomor' => 113, 'nama' => 'Al-Falaq', 'arab' => 'الفلق', 'ayat' => 5, 'juz' => 30],
        114 => ['nomor' => 114, 'nama' => 'An-Nas', 'arab' => 'الناس', 'ayat' => 6, 'juz' => 30],
    ];

    public static function getSurahList(): array
    {
        return self::$surahs;
    }

    public static function getSurah(int $nomor): ?array
    {
        return self::$surahs[$nomor] ?? null;
    }

    public static function getJuz30Surahs(): array
    {
        return array_filter(self::$surahs, fn($s) => $s['nomor'] >= 78);
    }

    public static function getJuz29Surahs(): array
    {
        return array_filter(self::$surahs, fn($s) => $s['nomor'] >= 67 && $s['nomor'] <= 77);
    }

    public static function getPaudShortSurahs(): array
    {
        // Surah pendek pilihan untuk anak usia dini (An-Nas s/d Adh-Dhuha + Al-Fatihah)
        $list = [1 => self::$surahs[1]];
        for ($i = 93; $i <= 114; $i++) {
            $list[$i] = self::$surahs[$i];
        }
        return $list;
    }

    public static function getIqroLevels(): array
    {
        return [
            1 => 'Iqro\' Jilid 1 (Huruf Tunggal)',
            2 => 'Iqro\' Jilid 2 (Huruf Sambung & Mad)',
            3 => 'Iqro\' Jilid 3 (Kasrah, Dhammah, Sukun)',
            4 => 'Iqro\' Jilid 4 (Tanwin & Qalqalah)',
            5 => 'Iqro\' Jilid 5 (Tasydid & Waqaf)',
            6 => 'Iqro\' Jilid 6 (Tajwid Dasar & Siap Al-Qur\'an)',
        ];
    }

    public static function getJenjangBadge(string $jenjang): string
    {
        $j = strtoupper($jenjang);
        if ($j === 'PAUD' || $j === 'TK') {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200"><span>🧸</span> PAUD / TK</span>';
        } elseif ($j === 'SD') {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200"><span>🎒</span> SD IT</span>';
        } else {
            return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200"><span>🎓</span> SMP & SMA IT</span>';
        }
    }

    public static function getStatusBadge(string $status): string
    {
        return match ($status) {
            'mutqin' => '<span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">⭐ Mutqin</span>',
            'lancar' => '<span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-teal-100 text-teal-800 border border-teal-300">✓ Lancar</span>',
            'ulang' => '<span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">⟳ Mengulang</span>',
            'perlu_bimbingan' => '<span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">! Perlu Bimbingan</span>',
            default => '<span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800">' . htmlspecialchars(ucfirst($status)) . '</span>',
        };
    }

    public static function getJenisSetoranLabel(string $jenis): string
    {
        return match ($jenis) {
            'ziyadah' => 'Ziyadah (Hafalan Baru)',
            'murojaah' => 'Muroja\'ah (Pengulangan)',
            'iqro' => 'Iqro\' / Tilawati',
            'tasmi' => 'Tasmi\' Sekali Duduk',
            'munaqasyah' => 'Ujian Munaqasyah',
            'ujian' => 'Ujian Tahfidz',
            default => ucfirst($jenis),
        };
    }
}
