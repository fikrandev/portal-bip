<?php
/**
 * Qur'an PAUD - Lembar Form Input Penilaian Santri per Grup Target
 */
$isTahsin = ($group['kategori'] === 'tahsin');
$targetData = json_decode($group['target_materi'] ?? '{}', true) ?: [];
$today = date('Y-m-d');
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Input Penilaian Santri
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <?= $isTahsin ? 'a. Tahsin (Iqro)' : 'b. Tahfidz (Surah)' ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">
                <?= htmlspecialchars($group['nama_grup']) ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelas: <strong class="text-slate-800"><?= htmlspecialchars($group['kelas']) ?></strong> &bull; 
                Guru: <strong class="text-slate-800"><?= htmlspecialchars($group['guru_nama'] ?: 'Guru PAUD') ?></strong>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-quran-siswa-paud/penilaian') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                <span>Kembali</span>
            </a>
            <button type="submit" form="formPenilaianPaud" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Semua Nilai</span>
            </button>
        </div>
    </div>

    <!-- Main Assessment Form -->
    <form id="formPenilaianPaud" action="<?= url('kelola-quran-siswa-paud/penilaian/store/' . $group['id']) ?>" method="POST" class="space-y-6">
        <?= class_exists('CSRF') ? CSRF::field() : '' ?>

<?php
$rawTarget = json_decode($group['target_materi'] ?? '{}', true) ?: [];
if (isset($rawTarget['tahsin']) || isset($rawTarget['tahfidz'])) {
    $tahsin = $rawTarget['tahsin'] ?? [];
    $tahfidz = $rawTarget['tahfidz'] ?? [];
} else {
    $isLegTahsin = ($group['kategori'] === 'tahsin');
    $tahsin = $isLegTahsin ? $rawTarget : [];
    $tahfidz = !$isLegTahsin ? $rawTarget : [];
}
?>
        <!-- Bar Konfigurasi Sesi Pertemuan -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4 flex-1">
                
                <!-- Tanggal Penilaian -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Tanggal Penilaian:
                    </label>
                    <input type="date" name="tanggal_penilaian" value="<?= $today ?>" required
                           class="px-3.5 py-2.5 rounded-2xl border border-slate-200 focus:border-teal-500 text-xs font-bold text-slate-800 bg-slate-50/50">
                </div>

                <!-- Materi Pokok / Target yang Dinilai Hari Ini -->
                <div class="min-w-[280px] flex-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Materi yang Diuji / Dinilai Hari Ini: <span class="text-red-500">*</span>
                    </label>
                    <select name="materi_default" id="materiDefault" required
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-teal-200 focus:border-teal-500 text-xs font-bold text-slate-800 bg-teal-50/30">
                        <optgroup label="Target Tahsin (Iqro' / Tilawati)">
                            <?php if (!empty($tahsin['jilid'])): ?>
                                <option value="<?= htmlspecialchars($tahsin['metode'] ?? "Iqro'") ?> Jilid <?= $tahsin['jilid'] ?> (Hal. <?= $tahsin['halaman_awal'] ?? 1 ?>)">
                                    <?= htmlspecialchars($tahsin['metode'] ?? "Iqro'") ?> Jilid <?= $tahsin['jilid'] ?> (Hal. <?= $tahsin['halaman_awal'] ?? 1 ?>–<?= $tahsin['halaman_target'] ?? 30 ?>)
                                </option>
                            <?php else: ?>
                                <option value="Iqro' Jilid 1">Iqro' Jilid 1</option>
                            <?php endif; ?>
                        </optgroup>

                        <?php if (!empty($tahfidz['surah'])): ?>
                            <optgroup label="Target Tahfidz (Surah Pendek)">
                                <?php foreach ($tahfidz['surah'] as $surah): ?>
                                    <option value="Surah <?= htmlspecialchars($surah) ?>">Surah <?= htmlspecialchars($surah) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <?php if (!empty($tahfidz['doa'])): ?>
                            <optgroup label="Target Hafalan Doa Harian">
                                <?php foreach ($tahfidz['doa'] as $doa): ?>
                                    <option value="<?= htmlspecialchars($doa) ?>"><?= htmlspecialchars($doa) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>

            </div>

            <!-- Petunjuk Singkat -->
            <div class="text-right">
                <span class="text-[11px] font-medium text-slate-400 block">Metode Penilaian:</span>
                <span class="text-xs font-bold text-slate-600">Bintang 1–5 &bull; Kelancaran &bull; Makhraj</span>
            </div>
        </div>

        <!-- Tabel Santri & Form Input -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <span>Santri Cilik Kelas: <?= htmlspecialchars($group['kelas']) ?></span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold">
                        <?= count($students) ?> Santri
                    </span>
                </h3>
                <span class="text-xs text-slate-400">Silakan beri bintang &amp; evaluasi untuk masing-masing santri</span>
            </div>

            <?php if (empty($students)): ?>
                <div class="p-10 text-center text-slate-400 text-xs">
                    Tidak ditemukan data santri aktif pada kelas <?= htmlspecialchars($group['kelas']) ?>.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-200/70">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4 w-52">Santri Cilik</th>
                                <th class="py-3.5 px-4 w-44">Materi Spesifik</th>
                                <th class="py-3.5 px-4 w-32">Kelancaran</th>
                                <th class="py-3.5 px-4 w-32">Makhraj / Tajwid</th>
                                <th class="py-3.5 px-4 w-40 text-center">Bintang PAUD ⭐</th>
                                <th class="py-3.5 px-4 w-36">Status</th>
                                <th class="py-3.5 px-4">Catatan Ustadzah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($students as $idx => $s): 
                                $sId = $s['id'];
                                $existNilai = $existingNilai[$sId] ?? null;
                                $bintangVal = $existNilai['bintang'] ?? 5;
                            ?>
                                <tr class="hover:bg-amber-50/20 transition-colors">
                                    <td class="py-4 px-4 text-center font-bold text-slate-400">
                                        <?= $idx + 1 ?>
                                        <input type="hidden" name="nilai[<?= $sId ?>][siswa_id]" value="<?= $sId ?>">
                                    </td>

                                    <!-- Identitas Santri -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-400 to-orange-400 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-xs">
                                                <?= strtoupper(substr($s['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-extrabold text-slate-900 block truncate"><?= htmlspecialchars($s['nama_lengkap']) ?></span>
                                                <span class="text-[10px] text-slate-400 block"><?= htmlspecialchars($s['nis'] ?: 'PAUD' . str_pad($sId, 3, '0', STR_PAD_LEFT)) ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Materi Spesifik -->
                                    <td class="py-4 px-4">
                                        <input type="text" name="nilai[<?= $sId ?>][materi_dinilai]" 
                                               value="<?= htmlspecialchars($existNilai['materi_dinilai'] ?? '') ?>"
                                               placeholder="Ikuti materi default"
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 focus:border-amber-500 text-xs font-semibold text-slate-800 bg-slate-50/50">
                                    </td>

                                    <!-- Nilai Kelancaran -->
                                    <td class="py-4 px-4">
                                        <select name="nilai[<?= $sId ?>][nilai_kelancaran]" 
                                                class="w-full px-2 py-1.5 rounded-lg border border-slate-200 focus:border-amber-500 font-bold text-slate-800 text-xs bg-white">
                                            <option value="A" <?= ($existNilai['nilai_kelancaran'] ?? 'A') === 'A' ? 'selected' : '' ?>>A (Sangat Lancar)</option>
                                            <option value="B" <?= ($existNilai['nilai_kelancaran'] ?? '') === 'B' ? 'selected' : '' ?>>B (Lancar)</option>
                                            <option value="C" <?= ($existNilai['nilai_kelancaran'] ?? '') === 'C' ? 'selected' : '' ?>>C (Terbata-bata)</option>
                                            <option value="D" <?= ($existNilai['nilai_kelancaran'] ?? '') === 'D' ? 'selected' : '' ?>>D (Perlu Bimbingan)</option>
                                        </select>
                                    </td>

                                    <!-- Nilai Makhraj -->
                                    <td class="py-4 px-4">
                                        <select name="nilai[<?= $sId ?>][nilai_makhraj]" 
                                                class="w-full px-2 py-1.5 rounded-lg border border-slate-200 focus:border-amber-500 font-bold text-slate-800 text-xs bg-white">
                                            <option value="A" <?= ($existNilai['nilai_makhraj'] ?? 'A') === 'A' ? 'selected' : '' ?>>A (Fasih)</option>
                                            <option value="B" <?= ($existNilai['nilai_makhraj'] ?? '') === 'B' ? 'selected' : '' ?>>B (Cukup Baik)</option>
                                            <option value="C" <?= ($existNilai['nilai_makhraj'] ?? '') === 'C' ? 'selected' : '' ?>>C (Kurang Tepat)</option>
                                            <option value="D" <?= ($existNilai['nilai_makhraj'] ?? '') === 'D' ? 'selected' : '' ?>>D (Bimbingan Makhraj)</option>
                                        </select>
                                    </td>

                                    <!-- Rating Bintang (1 - 5) -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1 p-1 rounded-xl bg-amber-50/80 border border-amber-200">
                                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                                <button type="button" onclick="setBintang(<?= $sId ?>, <?= $star ?>)" 
                                                        id="star_<?= $sId ?>_<?= $star ?>"
                                                        class="w-5 h-5 text-sm transition-transform hover:scale-125 focus:outline-none <?= $star <= $bintangVal ? 'text-amber-500' : 'text-slate-300' ?>">
                                                    ★
                                                </button>
                                            <?php endfor; ?>
                                        </div>
                                        <input type="hidden" name="nilai[<?= $sId ?>][bintang]" id="input_bintang_<?= $sId ?>" value="<?= $bintangVal ?>">
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4">
                                        <select name="nilai[<?= $sId ?>][status_lulus]" 
                                                class="w-full px-2 py-1.5 rounded-lg border border-slate-200 focus:border-amber-500 font-bold text-xs bg-white">
                                            <option value="Mutqin" <?= ($existNilai['status_lulus'] ?? '') === 'Mutqin' ? 'selected' : '' ?>>⭐ Mutqin</option>
                                            <option value="Lancar" <?= ($existNilai['status_lulus'] ?? 'Lancar') === 'Lancar' ? 'selected' : '' ?>>✓ Lancar</option>
                                            <option value="Ulang" <?= ($existNilai['status_lulus'] ?? '') === 'Ulang' ? 'selected' : '' ?>>↺ Ulang</option>
                                            <option value="Perlu Bimbingan" <?= ($existNilai['status_lulus'] ?? '') === 'Perlu Bimbingan' ? 'selected' : '' ?>>● Bimbingan</option>
                                        </select>
                                    </td>

                                    <!-- Catatan Ustadzah -->
                                    <td class="py-4 px-4">
                                        <input type="text" name="nilai[<?= $sId ?>][catatan]" 
                                               value="<?= htmlspecialchars($existNilai['catatan'] ?? '') ?>"
                                               placeholder="Catatan perkembangan..."
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 focus:border-amber-500 text-xs font-medium text-slate-700">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="p-5 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">Pastikan seluruh santri sudah dinilai sebelum menyimpan.</span>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-2xl bg-gradient-to-r <?= $isTahsin ? 'from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-amber-500/20' : 'from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-600/20' ?> text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                    <span>Simpan Nilai Santri</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </button>
            </div>
        </div>

    </form>

</div>

<script>
function setBintang(siswaId, starCount) {
    document.getElementById('input_bintang_' + siswaId).value = starCount;
    for (let i = 1; i <= 5; i++) {
        const btn = document.getElementById(`star_${siswaId}_${i}`);
        if (i <= starCount) {
            btn.className = 'w-5 h-5 text-sm transition-transform hover:scale-125 focus:outline-none text-amber-500';
        } else {
            btn.className = 'w-5 h-5 text-sm transition-transform hover:scale-125 focus:outline-none text-slate-300';
        }
    }
}
</script>
