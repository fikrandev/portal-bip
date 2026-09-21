<?php
/**
 * Qur'an PAUD - Halaman Kelola Target di Dalam Grup
 * Menampilkan rincian identitas grup serta pengaturan lengkap Target Tahsin & Tahfidz
 * Referensi UI/UX: Modul Kelola Nilai
 */
$surahListPaud = [
    'An-Nas', 'Al-Falaq', 'Al-Ikhlas', 'Al-Lahab', 'An-Nasr',
    'Al-Kafirun', 'Al-Kautsar', 'Al-Ma\'un', 'Quraisy', 'Al-Fil',
    'Al-Humazah', 'Al-\'Asr', 'At-Takatsur', 'Al-Qari\'ah', 'Al-\'Adiyat',
    'Az-Zalzalah', 'Al-Bayyinah', 'Al-Qadr', 'Al-\'Alaq', 'At-Tin',
    'Al-Insyirah', 'Adh-Dhuha'
];

$doaListPaud = [
    'Doa Sebelum Belajar', 'Doa Kedua Orang Tua', 'Doa Kebaikan Dunia & Akhirat',
    'Doa Sebelum Makan', 'Doa Sesudah Makan', 'Doa Bangun Tidur',
    'Doa Masuk Masjid', 'Doa Keluar Masjid', 'Doa Masuk Kamar Mandi'
];

$haditsListPaud = [
    'Hadits Kebersihan (At-Thahuru Syatrul Iman)',
    'Hadits Menuntut Ilmu (Thalabul \'Ilmi Faridhatun)',
    'Hadits Senyum Adalah Sedekah (Tabassumuka)',
    'Hadits Kasih Sayang (Man La Yarham La Yurham)'
];

$savedSurah = $tahfidz['surah'] ?? [];
$savedDoa = $tahfidz['doa'] ?? [];
$savedHadits = $tahfidz['hadits'] ?? [];

$totalSantri = count($santriList ?? []);
$santriDinilai = 0;
foreach ($santriList as $s) {
    if (!empty($s['penilaian_id'])) {
        $santriDinilai++;
    }
}
$santriBelum = $totalSantri - $santriDinilai;
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Pengaturan Target
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Jenjang PAUD / TK
                </span>
                <?php if ($group['is_active']): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Aktif</span>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>Nonaktif</span>
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-2">
                <?= htmlspecialchars($group['nama_grup']) ?>
            </h1>
            
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola target capaian pembelajaran <strong>Tahsin (Iqro'/Tilawati)</strong> dan <strong>Tahfidz (Surah & Doa)</strong> untuk santri dalam grup ini.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Grup</span>
            </a>
            <a href="<?= url('kelola-quran-siswa-paud/target/edit/' . $group['id']) ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                </svg>
                <span>Edit Info</span>
            </a>
            <a href="<?= url('kelola-quran-siswa-paud/penilaian/' . $group['id']) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Input Penilaian Santri</span>
            </a>
        </div>
    </div>

    <!-- Group Metadata Info Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">Tahun Ajaran</span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-xs font-bold text-slate-800"><?= htmlspecialchars($ta['nama_tahun'] ?? 'Tahun Ajaran Aktif') ?></span>
                <?php if (!empty($ta['is_active'])): ?>
                    <span class="w-2 h-2 rounded-full bg-emerald-500" title="Aktif di Sistem"></span>
                <?php endif; ?>
            </div>
            <span class="text-[10px] text-teal-600 font-semibold mt-0.5 block">Semester <?= htmlspecialchars($group['semester'] ?? 'Ganjil') ?></span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">Kelas / Rombel</span>
            <span class="text-xs font-bold text-slate-800 mt-1 block truncate"><?= htmlspecialchars($group['kelas']) ?></span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Jenjang PAUD/TK</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">Guru Pengampu</span>
            <span class="text-xs font-bold text-slate-800 mt-1 block truncate"><?= htmlspecialchars($group['guru_nama'] ?: 'Ustadzah PAUD') ?></span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Pengajar Qur'an</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 block uppercase">Progress Penilaian</span>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs font-bold text-teal-700"><?= $santriDinilai ?> / <?= $totalSantri ?></span>
                <span class="text-[10px] text-slate-400">(<?= $totalSantri > 0 ? round(($santriDinilai / $totalSantri) * 100) : 0 ?>%)</span>
            </div>
            <span class="text-[10px] text-amber-600 font-medium mt-0.5 block"><?= $santriBelum ?> santri belum dinilai</span>
        </div>
    </div>

    <!-- Main Target Form -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-quran-siswa-paud/target/manage/' . $group['id'] . '/store') ?>" method="POST" class="space-y-8" id="formManageTarget">
            <?= class_exists('CSRF') ? CSRF::field() : '' ?>
            <input type="hidden" name="id" value="<?= $group['id'] ?>">

            <!-- TARGET 1: TAHSIN AL-QUR'AN (IQRO' / TILAWATI) -->
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black text-xs shadow-sm shadow-amber-500/20">
                            1
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Target Pembelajaran Tahsin (Membaca Al-Qur'an)</h3>
                            <p class="text-[11px] text-slate-400">Atur metode pembelajaran, level jilid, dan capaian halaman santri</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        Tahsin PAUD
                    </span>
                </div>

                <div class="bg-amber-50/30 border border-amber-200/60 rounded-2xl p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Pilihan Metode -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Metode Pembelajaran <span class="text-rose-500">*</span>
                            </label>
                            <select name="tahsin_metode" 
                                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-xs font-semibold text-slate-800 bg-white">
                                <?php 
                                $metodes = ["Iqro'", "Tilawati", "Yanbu'a", "Qiroati", "Al-Bayan", "Metode Lain"];
                                $selMetode = $tahsin['metode'] ?? "Iqro'";
                                foreach ($metodes as $m): 
                                ?>
                                    <option value="<?= $m ?>" <?= $selMetode === $m ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Level Jilid -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Target Jilid / Level <span class="text-rose-500">*</span>
                            </label>
                            <select name="tahsin_jilid" 
                                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-xs font-semibold text-slate-800 bg-white">
                                <?php 
                                $selJilid = intval($tahsin['jilid'] ?? 1);
                                for ($j = 1; $j <= 6; $j++): 
                                ?>
                                    <option value="<?= $j ?>" <?= $selJilid === $j ? 'selected' : '' ?>>Jilid <?= $j ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Rentang Halaman -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Rentang Target Halaman <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="tahsin_halaman_awal" min="1" max="100" 
                                       value="<?= htmlspecialchars($tahsin['halaman_awal'] ?? 1) ?>" 
                                       placeholder="Hal 1"
                                       class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-xs font-semibold text-slate-800 bg-white text-center">
                                <input type="number" name="tahsin_halaman_target" min="1" max="100" 
                                       value="<?= htmlspecialchars($tahsin['halaman_target'] ?? $tahsin['halaman_akhir'] ?? 30) ?>" 
                                       placeholder="Hal 30"
                                       class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-xs font-semibold text-slate-800 bg-white text-center">
                            </div>
                        </div>
                    </div>

                    <!-- Fokus Pembelajaran Tahsin -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Fokus & Indikator Capaian Tahsin
                        </label>
                        <input type="text" name="tahsin_fokus" 
                               value="<?= htmlspecialchars($tahsin['fokus'] ?? 'Pengenalan bunyi huruf hijaiyah, harakat tunggal, dan kelancaran membaca') ?>"
                               placeholder="Contoh: Pengenalan huruf hijaiyah, harakat fathah/kasrah/dhammah, makhraj huruf dasar"
                               class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-xs font-semibold text-slate-800 bg-white">
                    </div>
                </div>
            </div>

            <!-- TARGET 2: TAHFIDZ AL-QUR'AN (SURAH PENDEK & DOA HARIAN) -->
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-xs shadow-sm shadow-emerald-500/20">
                            2
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Target Pembelajaran Tahfidz & Doa Harian</h3>
                            <p class="text-[11px] text-slate-400">Pilih surah pendek Juz 30 dan doa-doa harian yang ditargetkan untuk santri</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Tahfidz PAUD
                    </span>
                </div>

                <div class="bg-emerald-50/30 border border-emerald-200/60 rounded-2xl p-5 space-y-6">
                    
                    <!-- Checklist Surah Pendek Juz 30 -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                <span>Target Hafalan Surah Pendek (Juz 30)</span>
                                <span id="countSurahSelected" class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <?= count($savedSurah) ?> Terpilih
                                </span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="selectAllCheckboxes('surah-cb', true)" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 bg-white px-2.5 py-1 rounded-xl border border-emerald-200 shadow-sm transition-colors">
                                    Pilih Semua
                                </button>
                                <button type="button" onclick="selectAllCheckboxes('surah-cb', false)" class="text-[11px] font-bold text-slate-500 hover:text-slate-700 bg-white px-2.5 py-1 rounded-xl border border-slate-200 shadow-sm transition-colors">
                                    Kosongkan
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                            <?php foreach ($surahListPaud as $surah): 
                                $isChecked = in_array($surah, $savedSurah);
                            ?>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border <?= $isChecked ? 'border-emerald-300 bg-emerald-100/50' : 'border-slate-200/80 bg-white' ?> hover:border-emerald-300 cursor-pointer transition-all">
                                    <input type="checkbox" name="tahfidz_surah[]" value="<?= htmlspecialchars($surah) ?>" 
                                           class="surah-cb rounded text-emerald-600 focus:ring-emerald-500" 
                                           <?= $isChecked ? 'checked' : '' ?>
                                           onchange="updateSelectionCount()">
                                    <span class="text-xs font-semibold text-slate-700 truncate"><?= htmlspecialchars($surah) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Checklist Doa Harian Santri PAUD -->
                    <div class="pt-4 border-t border-emerald-200/60">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                <span>Target Doa-Doa Harian Santri PAUD</span>
                                <span id="countDoaSelected" class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-teal-100 text-teal-800">
                                    <?= count($savedDoa) ?> Terpilih
                                </span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="selectAllCheckboxes('doa-cb', true)" class="text-[11px] font-bold text-teal-700 hover:text-teal-800 bg-white px-2.5 py-1 rounded-xl border border-teal-200 shadow-sm transition-colors">
                                    Pilih Semua
                                </button>
                                <button type="button" onclick="selectAllCheckboxes('doa-cb', false)" class="text-[11px] font-bold text-slate-500 hover:text-slate-700 bg-white px-2.5 py-1 rounded-xl border border-slate-200 shadow-sm transition-colors">
                                    Kosongkan
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            <?php foreach ($doaListPaud as $doa): 
                                $isChecked = in_array($doa, $savedDoa);
                            ?>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border <?= $isChecked ? 'border-teal-300 bg-teal-100/50' : 'border-slate-200/80 bg-white' ?> hover:border-teal-300 cursor-pointer transition-all">
                                    <input type="checkbox" name="tahfidz_doa[]" value="<?= htmlspecialchars($doa) ?>" 
                                           class="doa-cb rounded text-teal-600 focus:ring-teal-500" 
                                           <?= $isChecked ? 'checked' : '' ?>
                                           onchange="updateSelectionCount()">
                                    <span class="text-xs font-semibold text-slate-700 truncate"><?= htmlspecialchars($doa) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Checklist Hadits Pilihan Santri PAUD -->
                    <div class="pt-4 border-t border-emerald-200/60">
                        <label class="text-xs font-bold text-slate-800 block mb-2">
                            Target Hadits Pilihan Santri PAUD (Opsional)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <?php foreach ($haditsListPaud as $hadits): 
                                $isChecked = in_array($hadits, $savedHadits);
                            ?>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border <?= $isChecked ? 'border-indigo-300 bg-indigo-50/50' : 'border-slate-200/80 bg-white' ?> hover:border-indigo-300 cursor-pointer transition-all">
                                    <input type="checkbox" name="tahfidz_hadits[]" value="<?= htmlspecialchars($hadits) ?>" 
                                           class="rounded text-indigo-600 focus:ring-indigo-500" 
                                           <?= $isChecked ? 'checked' : '' ?>>
                                    <span class="text-xs font-semibold text-slate-700 truncate"><?= htmlspecialchars($hadits) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Fokus Pembelajaran Tahfidz -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Fokus & Indikator Capaian Tahfidz
                        </label>
                        <input type="text" name="tahfidz_fokus" 
                               value="<?= htmlspecialchars($tahfidz['fokus'] ?? 'Hafalan lancar mutqin dengan makhraj huruf dan kelancaran yang baik') ?>"
                               placeholder="Contoh: Hafalan mutqin dengan tajwid dasar dan kelancaran pelafalan"
                               class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-xs font-semibold text-slate-800 bg-white">
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan Form Target -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                    Kembali
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan Target</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Section 3: Daftar Santri di Kelas Grup Target Ini -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">
                    Daftar Santri Qur'an PAUD / TK
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Total <?= $totalSantri ?> santri terdaftar. Status penilaian capaian target pembelajaran Qur'an santri.
                </p>
            </div>

            <a href="<?= url('kelola-quran-siswa-paud/penilaian/' . $group['id']) ?>" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 font-bold text-xs transition-all self-start sm:self-auto">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Buka Form Penilaian Santri</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Santri</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">NIS / NISN</th>
                        <th class="py-3 px-4">Materi Terakhir</th>
                        <th class="py-3 px-4 text-center">Bintang</th>
                        <th class="py-3 px-4 text-center">Status Kelulusan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($santriList)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada santri terdaftar di jenjang PAUD / TK.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($santriList as $idx => $s): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400"><?= $idx + 1 ?></td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800 block text-xs"><?= htmlspecialchars($s['nama_lengkap']) ?></span>
                                    <span class="text-[10px] text-slate-400"><?= $s['jenis_kelamin'] === 'P' ? 'Santriwati (Perempuan)' : 'Santri (Laki-laki)' ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        <?= htmlspecialchars($s['kelas'] ?: 'PAUD') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-xs">
                                    <?= htmlspecialchars($s['nisn'] ?: ($s['nis'] ?: '-')) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    <?php if (!empty($s['materi_dinilai'])): ?>
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-50 text-amber-900 border border-amber-200">
                                            <?= htmlspecialchars($s['materi_dinilai']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-xs">Belum dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if (!empty($s['bintang'])): ?>
                                        <span class="text-amber-500 font-bold text-xs">
                                            <?= str_repeat('★', (int)$s['bintang']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if (!empty($s['status_lulus'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $s['status_lulus'] === 'Mutqin' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($s['status_lulus'] === 'Lancar' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200') ?>">
                                            <?= htmlspecialchars($s['status_lulus']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">Belum dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="<?= url('kelola-quran-siswa-paud/penilaian/' . $group['id']) ?>" 
                                       class="px-2.5 py-1 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-[11px] border border-teal-200 transition-colors inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                        </svg>
                                        <span>Nilai</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function selectAllCheckboxes(className, state) {
    document.querySelectorAll('.' + className).forEach(cb => {
        cb.checked = state;
        const parent = cb.closest('label');
        if (parent) {
            if (state) {
                parent.classList.add('border-emerald-300', 'bg-emerald-100/50');
            } else {
                parent.classList.remove('border-emerald-300', 'bg-emerald-100/50');
            }
        }
    });
    updateSelectionCount();
}

function updateSelectionCount() {
    const surahCount = document.querySelectorAll('.surah-cb:checked').length;
    const doaCount = document.querySelectorAll('.doa-cb:checked').length;

    const surahBadge = document.getElementById('countSurahSelected');
    const doaBadge = document.getElementById('countDoaSelected');

    if (surahBadge) surahBadge.textContent = `${surahCount} Terpilih`;
    if (doaBadge) doaBadge.textContent = `${doaCount} Terpilih`;

    // Visual feedback label
    document.querySelectorAll('.surah-cb, .doa-cb').forEach(cb => {
        const parent = cb.closest('label');
        if (parent) {
            if (cb.checked) {
                parent.classList.add('border-emerald-300', 'bg-emerald-100/50');
            } else {
                parent.classList.remove('border-emerald-300', 'bg-emerald-100/50');
            }
        }
    });
}
</script>
