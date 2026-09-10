<?php
/**
 * Edit Wadah Grup Absen
 */
$unit_list = [
    'PAUD' => ['name' => 'PAUD / TK', 'icon' => '🧸', 'bg_soft' => 'bg-pink-50 text-pink-700'],
    'SD'   => ['name' => 'SD (Sekolah Dasar)', 'icon' => '🎒', 'bg_soft' => 'bg-emerald-50 text-emerald-700'],
    'SMP'  => ['name' => 'SMP (Sekolah Menengah Pertama)', 'icon' => '📚', 'bg_soft' => 'bg-blue-50 text-blue-700'],
    'SMA'  => ['name' => 'SMA / SMK', 'icon' => '🎓', 'bg_soft' => 'bg-purple-50 text-purple-700']
];
$selectedUnit = $group['unit'] ?? 'SD';
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Edit Wadah
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">Edit Wadah Grup Absen</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui konfigurasi grup presensi siswa ID #<?= $group['id'] ?></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-absen-siswa/group') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="<?= url('kelola-absen-siswa/group/update/' . $group['id']) ?>" method="POST" class="p-6 sm:p-8 space-y-8">
            
            <!-- Step 1: Pilih Unit Sekolah -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-black flex items-center justify-center">1</span>
                    Satuan Pendidikan / Unit <span class="text-red-500">*</span>
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isSelected = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-emerald-400 hover:bg-slate-50/80 unit-card <?= $isSelected ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" class="sr-only" <?= $isSelected ? 'checked' : '' ?> required onchange="updateUnitSelection(this)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                <?= $uInfo['icon'] ?>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                            <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= htmlspecialchars($uInfo['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Step 2: Tipe Wadah & Konfigurasi -->
            <div class="pt-2 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-black flex items-center justify-center">2</span>
                    Tipe Presensi &amp; Parameter Pembelajaran
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipe Presensi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Tipe Presensi <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 rounded-2xl border border-slate-200 hover:border-emerald-400 cursor-pointer transition-all bg-slate-50/50">
                                <input type="radio" name="tipe" value="mapel" <?= ($group['tipe'] === 'mapel') ? 'checked' : '' ?> class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Presensi Mapel</p>
                                    <p class="text-[10px] text-slate-500">Per guru mata pelajaran</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-2xl border border-slate-200 hover:border-emerald-400 cursor-pointer transition-all bg-slate-50/50">
                                <input type="radio" name="tipe" value="kelas" <?= ($group['tipe'] === 'kelas') ? 'checked' : '' ?> class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Presensi Kelas</p>
                                    <p class="text-[10px] text-slate-500">Harian / Wali kelas</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Tahun Akademik & Semester -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Tahun Ajaran <span class="text-red-500">*</span></label>
                            <select name="tahun_akademik_id" class="w-full px-3 py-2 rounded-2xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50" required>
                                <?php foreach ($tahunList as $th): ?>
                                    <option value="<?= $th['id'] ?>" <?= $group['tahun_akademik_id'] == $th['id'] ? 'selected' : '' ?>><?= htmlspecialchars($th['nama_tahun'] ?? '2026/2027') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Semester <span class="text-red-500">*</span></label>
                            <select name="semester" class="w-full px-3 py-2 rounded-2xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50" required>
                                <option value="Ganjil" <?= $group['semester'] === 'Ganjil' ? 'selected' : '' ?>>Ganjil (1)</option>
                                <option value="Genap" <?= $group['semester'] === 'Genap' ? 'selected' : '' ?>>Genap (2)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Kelas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Target Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50" required>
                            <option value="">-- Pilih Rombongan Belajar / Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= htmlspecialchars($k['kelas']) ?>" <?= ($group['kelas'] === $k['kelas']) ? 'selected' : '' ?>>Kelas <?= htmlspecialchars($k['kelas']) ?> (<?= htmlspecialchars($k['jenjang']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Mata Pelajaran -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Mata Pelajaran</label>
                        <input type="text" name="mata_pelajaran" value="<?= htmlspecialchars($group['mata_pelajaran'] ?? '') ?>" class="w-full px-4 py-2 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50" placeholder="Cth: Matematika, IPAS, Bahasa Arab">
                    </div>

                    <!-- Guru Pengampu -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Guru Pengampu / Wali Kelas</label>
                        <select name="guru_id" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50">
                            <option value="">-- Pilih Guru / Tenaga Pendidik --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= $group['guru_id'] == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nama']) ?><?= !empty($g['gelar']) ? ', ' . htmlspecialchars($g['gelar']) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 3: Judul Wadah & Deskripsi -->
            <div class="pt-2 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-black flex items-center justify-center">3</span>
                    Nama Wadah Grup &amp; Status
                </h3>

                <div class="space-y-4 max-w-2xl">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Judul Wadah Grup Absen <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" value="<?= htmlspecialchars($group['judul'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Deskripsi / Keterangan</label>
                        <textarea name="deskripsi" rows="3" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50"><?= htmlspecialchars($group['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_active" id="isActiveCheck" value="1" <?= !empty($group['is_active']) ? 'checked' : '' ?> class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                        <label for="isActiveCheck" class="text-xs font-bold text-slate-700 cursor-pointer">
                            Wadah grup absen aktif
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-absen-siswa/group') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Perbarui Wadah Grup Absen</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateUnitSelection(radio) {
    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-emerald-500', 'bg-emerald-50/30');
        card.classList.add('border-slate-200', 'bg-white');
    });
    const parent = radio.closest('.unit-card');
    if (parent) {
        parent.classList.remove('border-slate-200', 'bg-white');
        parent.classList.add('border-emerald-500', 'bg-emerald-50/30');
    }
}
</script>
