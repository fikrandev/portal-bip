<?php
/**
 * Kaldik - Edit View with Unit Selection (PAUD, SD, SMP, SMA)
 */
$agendas = $konten['agendas'] ?? [];
$selectedUnit = old('unit', $item['unit'] ?? 'SD');
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit Kalender Pendidikan (Kaldik)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui agenda akademik dan data kalender pendidikan</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/kaldik') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            ← Kembali
        </a>
    </div>

    <!-- Status Alert if Rejected / Revisi -->
    <?php if ($item['status'] === 'ditolak'): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3">
            <div class="text-rose-600 text-xl font-bold">⚠️</div>
            <div>
                <h4 class="text-xs font-bold text-rose-900">Dokumen Memerlukan Revisi</h4>
                <p class="text-xs text-rose-700 mt-0.5">Catatan Verifikator: <?= e($item['catatan_revisi'] ?? 'Lakukan perbaikan sesuai arahan.') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/kaldik/update/{$item['id']}") ?>" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Identitas Utama & Unit Selector -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Identitas & Unit Kaldik
            </h2>

            <!-- Searchable Live Search Guru Picker (At Atas) -->
            <?php
            $picker_label = 'Penyusun / Penanggung Jawab Kaldik';
            $picker_accent = 'emerald';
            $selected_guru_id = old('guru_id', $item['guru_id'] ?? null);
            $selected_guru_nama = old('guru_nama', $item['guru_nama'] ?? null);
            $selected_guru_nip = old('guru_nip', $item['guru_nip'] ?? null);
            include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/guru_picker.php';
            ?>

            <!-- Visual Unit Selector -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Unit Satuan Pendidikan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isChecked = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-emerald-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio" onchange="updateUnitSelection(this)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                <?= $uInfo['icon'] ?>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                            <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= e($uInfo['name']) ?></span>
                            <div class="unit-check-indicator absolute top-2 right-2 <?= $isChecked ? 'block text-emerald-600' : 'hidden' ?>">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Kalender Pendidikan <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" required value="<?= e($item['judul']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($item['tahun_akademik_id'] == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50">
                        <option value="Ganjil" <?= $item['semester'] === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                        <option value="Genap" <?= $item['semester'] === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                    </select>
                </div>
            </div>

            <!-- Status Aktif / Acuan Switch -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <label class="block text-xs font-bold text-slate-700">Status Keaktifan Kaldik Unit <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 p-3 rounded-xl border-2 <?= !empty($item['is_active']) ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200 bg-white' ?> cursor-pointer">
                        <input type="radio" name="is_active" value="1" <?= !empty($item['is_active']) ? 'checked' : '' ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                                🟢 Kaldik Aktif Utama
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Dijadikan acuan resmi guru saat membuat HES & HEB pada unit ini.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 rounded-xl border-2 <?= empty($item['is_active']) ? 'border-slate-400 bg-slate-100' : 'border-slate-200 bg-white' ?> hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="is_active" value="0" <?= empty($item['is_active']) ? 'checked' : '' ?> class="mt-0.5 text-slate-600 focus:ring-slate-500">
                        <div>
                            <span class="text-xs font-bold text-slate-700">
                                ⚪ Non-Aktif / Arsip
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Disimpan sebagai arsip atau persiapan tahun ajaran berikutnya.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi / Catatan Kebijakan Kaldik</label>
                <textarea name="deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/50"><?= e($konten['deskripsi'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Agenda Builder Dinamis -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> Agenda Kegiatan Akademik
                </h2>
                <button type="button" onclick="tambahBarisAgenda()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah Agenda
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="tabel-agenda">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <th class="py-2.5 px-3">Tgl Mulai</th>
                            <th class="py-2.5 px-3">Tgl Selesai</th>
                            <th class="py-2.5 px-3 w-1/3">Nama Agenda / Kegiatan</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3">Pengecualian Tingkat</th>
                            <th class="py-2.5 px-3">Keterangan</th>
                            <th class="py-2.5 px-2 text-center w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium" id="agenda-container">
                        <?php if (!empty($agendas)): ?>
                            <?php foreach ($agendas as $idx => $agenda): ?>
                                <tr class="agenda-row">
                                    <td class="py-2 px-2">
                                        <input type="date" name="agenda_tgl_mulai[]" value="<?= e($agenda['tanggal_mulai'] ?? '') ?>" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="date" name="agenda_tgl_selesai[]" value="<?= e($agenda['tanggal_selesai'] ?? '') ?>" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="text" name="agenda_kegiatan[]" value="<?= e($agenda['kegiatan'] ?? '') ?>" required placeholder="Nama kegiatan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="py-2 px-2">
                                        <select name="agenda_kategori[]" class="w-full px-2 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                            <option value="kbm" <?= ($agenda['kategori'] ?? '') === 'kbm' ? 'selected' : '' ?>>KBM Efektif</option>
                                            <option value="penilaian" <?= ($agenda['kategori'] ?? '') === 'penilaian' ? 'selected' : '' ?>>Penilaian / Ujian</option>
                                            <option value="libur_nasional" <?= ($agenda['kategori'] ?? '') === 'libur_nasional' ? 'selected' : '' ?>>Libur Nasional</option>
                                            <option value="libur_semester" <?= ($agenda['kategori'] ?? '') === 'libur_semester' ? 'selected' : '' ?>>Libur Semester</option>
                                            <option value="kegiatan" <?= ($agenda['kategori'] ?? '') === 'kegiatan' ? 'selected' : '' ?>>Kegiatan Sekolah</option>
                                        </select>
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="text" name="agenda_pengecualian[]" value="<?= e($agenda['pengecualian_tingkat'] ?? '') ?>" placeholder="Misal: 6, 9" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="text" name="agenda_keterangan[]" value="<?= e($agenda['keterangan'] ?? '') ?>" placeholder="Keterangan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="py-2 px-2 text-center">
                                        <button type="button" onclick="hapusBarisAgenda(this)" class="p-1 text-slate-400 hover:text-rose-500 transition-colors" title="Hapus Baris">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="agenda-row">
                                <td class="py-2 px-2">
                                    <input type="date" name="agenda_tgl_mulai[]" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-2">
                                    <input type="date" name="agenda_tgl_selesai[]" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-2">
                                    <input type="text" name="agenda_kegiatan[]" required placeholder="Nama kegiatan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-2">
                                    <select name="agenda_kategori[]" class="w-full px-2 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <option value="kbm">KBM Efektif</option>
                                        <option value="penilaian">Penilaian / Ujian</option>
                                        <option value="libur_nasional">Libur Nasional</option>
                                        <option value="libur_semester">Libur Semester</option>
                                        <option value="kegiatan">Kegiatan Sekolah</option>
                                    </select>
                                </td>
                                <td class="py-2 px-2">
                                    <input type="text" name="agenda_pengecualian[]" placeholder="Misal: 6, 9" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-2">
                                    <input type="text" name="agenda_keterangan[]" placeholder="Keterangan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <button type="button" onclick="hapusBarisAgenda(this)" class="p-1 text-slate-400 hover:text-rose-500 transition-colors" title="Hapus Baris">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Live Preview Matriks Kalender Pendidikan Bulanan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                        Pratinjau Matriks Kalender Tanggal Bulanan (Live Matrix)
                    </h2>
                </div>
                <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-bold" id="live-matrix-tabs">
                    <button type="button" onclick="switchLiveMatrixSemester('all')" class="live-tab-btn px-3 py-1 rounded-lg transition-all text-slate-600 hover:text-slate-900" data-tab="all">
                        1 Tahun (12 Bulan)
                    </button>
                    <button type="button" onclick="switchLiveMatrixSemester('Ganjil')" class="live-tab-btn px-3 py-1 rounded-lg transition-all bg-emerald-600 text-white shadow-sm" data-tab="Ganjil">
                        Semester Ganjil
                    </button>
                    <button type="button" onclick="switchLiveMatrixSemester('Genap')" class="live-tab-btn px-3 py-1 rounded-lg transition-all text-slate-600 hover:text-slate-900" data-tab="Genap">
                        Semester Genap
                    </button>
                </div>
            </div>

            <!-- Legenda Warna -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-[11px] font-semibold text-slate-700">
                <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="w-3.5 h-3.5 rounded-md bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold"></span>
                    <span class="truncate">KBM Efektif</span>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="w-3.5 h-3.5 rounded-md bg-amber-400 text-amber-950 flex items-center justify-center text-[9px] font-bold ring-1 ring-amber-500/30"></span>
                    <span class="truncate">Penilaian/Ujian</span>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="w-3.5 h-3.5 rounded-md bg-rose-500 text-white flex items-center justify-center text-[9px] font-bold"></span>
                    <span class="truncate">Libur Nasional</span>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="w-3.5 h-3.5 rounded-md bg-purple-500 text-white flex items-center justify-center text-[9px] font-bold"></span>
                    <span class="truncate">Libur Semester</span>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="w-3.5 h-3.5 rounded-md bg-sky-500 text-white flex items-center justify-center text-[9px] font-bold"></span>
                    <span class="truncate">Kegiatan Sekolah</span>
                </div>
                <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="w-3.5 h-3.5 rounded-md bg-rose-100 text-rose-600 font-bold border border-rose-300 flex items-center justify-center text-[9px]">M</span>
                    <span class="truncate">Hari Minggu</span>
                </div>
            </div>

            <!-- Month Cards Grid rendered by JS -->
            <div id="live-matrix-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                <!-- Injected by JavaScript -->
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/detail/{$item['id']}") ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                Simpan Perubahan Kaldik
            </button>
        </div>
    </form>
</div>

<script>
let currentMatrixSemester = 'Ganjil';

function updateUnitSelection(radio) {
    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-emerald-600', 'bg-emerald-50/40', 'ring-2', 'ring-emerald-500/20', 'shadow-sm');
        card.classList.add('border-slate-200', 'bg-white');
        const indicator = card.querySelector('.unit-check-indicator');
        if (indicator) {
            indicator.classList.add('hidden');
            indicator.classList.remove('block');
        }
    });

    const selectedCard = radio.closest('.unit-card');
    if (selectedCard) {
        selectedCard.classList.remove('border-slate-200', 'bg-white');
        selectedCard.classList.add('border-emerald-600', 'bg-emerald-50/40', 'ring-2', 'ring-emerald-500/20', 'shadow-sm');
        const indicator = selectedCard.querySelector('.unit-check-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.classList.add('block');
        }
    }
}

function tambahBarisAgenda() {
    const tbody = document.getElementById('agenda-container');
    const tr = document.createElement('tr');
    tr.className = 'agenda-row';
    tr.innerHTML = `
        <td class="py-2 px-2">
            <input type="date" name="agenda_tgl_mulai[]" onchange="renderLiveMatrix()" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </td>
        <td class="py-2 px-2">
            <input type="date" name="agenda_tgl_selesai[]" onchange="renderLiveMatrix()" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </td>
        <td class="py-2 px-2">
            <input type="text" name="agenda_kegiatan[]" oninput="renderLiveMatrix()" required placeholder="Nama kegiatan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </td>
        <td class="py-2 px-2">
            <select name="agenda_kategori[]" onchange="renderLiveMatrix()" class="w-full px-2 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="kbm">KBM Efektif</option>
                <option value="penilaian">Penilaian / Ujian</option>
                <option value="libur_nasional">Libur Nasional</option>
                <option value="libur_semester">Libur Semester</option>
                <option value="kegiatan">Kegiatan Sekolah</option>
            </select>
        </td>
        <td class="py-2 px-2">
            <input type="text" name="agenda_pengecualian[]" oninput="renderLiveMatrix()" placeholder="Misal: 6, 9" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </td>
        <td class="py-2 px-2">
            <input type="text" name="agenda_keterangan[]" oninput="renderLiveMatrix()" placeholder="Keterangan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </td>
        <td class="py-2 px-2 text-center">
            <button type="button" onclick="hapusBarisAgenda(this)" class="p-1 text-slate-400 hover:text-rose-500 transition-colors" title="Hapus Baris">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    renderLiveMatrix();
}

function hapusBarisAgenda(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('.agenda-row').length > 1) {
        row.remove();
        renderLiveMatrix();
    } else {
        alert('Minimal harus ada 1 baris agenda.');
    }
}

function switchLiveMatrixSemester(tab) {
    currentMatrixSemester = tab;
    const btns = document.querySelectorAll('.live-tab-btn');
    btns.forEach(b => {
        if (b.getAttribute('data-tab') === tab) {
            b.classList.add('bg-emerald-600', 'text-white', 'shadow-sm');
            b.classList.remove('text-slate-600');
        } else {
            b.classList.remove('bg-emerald-600', 'text-white', 'shadow-sm');
            b.classList.add('text-slate-600');
        }
    });
    renderLiveMatrix();
}

function renderLiveMatrix() {
    const container = document.getElementById('live-matrix-container');
    if (!container) return;

    // Collect current agendas from form
    const rows = document.querySelectorAll('.agenda-row');
    const agendas = [];
    rows.forEach(r => {
        const mul = r.querySelector('input[name="agenda_tgl_mulai[]"]')?.value;
        const sel = r.querySelector('input[name="agenda_tgl_selesai[]"]')?.value || mul;
        const keg = r.querySelector('input[name="agenda_kegiatan[]"]')?.value;
        const kat = r.querySelector('select[name="agenda_kategori[]"]')?.value || 'kbm';
        const ket = r.querySelector('input[name="agenda_keterangan[]"]')?.value || '';
        if (mul && keg) {
            agendas.push({ mulai: mul, selesai: sel, kegiatan: keg, kategori: kat, keterangan: ket });
        }
    });

    const startYear = new Date().getFullYear();
    const endYear = startYear + 1;

    const monthsDef = [
        { m: 7, y: startYear, name: 'Juli', smt: 'Ganjil' },
        { m: 8, y: startYear, name: 'Agustus', smt: 'Ganjil' },
        { m: 9, y: startYear, name: 'September', smt: 'Ganjil' },
        { m: 10, y: startYear, name: 'Oktober', smt: 'Ganjil' },
        { m: 11, y: startYear, name: 'November', smt: 'Ganjil' },
        { m: 12, y: startYear, name: 'Desember', smt: 'Ganjil' },
        { m: 1, y: endYear, name: 'Januari', smt: 'Genap' },
        { m: 2, y: endYear, name: 'Februari', smt: 'Genap' },
        { m: 3, y: endYear, name: 'Maret', smt: 'Genap' },
        { m: 4, y: endYear, name: 'April', smt: 'Genap' },
        { m: 5, y: endYear, name: 'Mei', smt: 'Genap' },
        { m: 6, y: endYear, name: 'Juni', smt: 'Genap' }
    ];

    const categoryStyles = {
        kbm: { bg: 'bg-emerald-500 text-white font-bold', label: 'KBM Efektif' },
        penilaian: { bg: 'bg-amber-400 text-amber-950 font-bold ring-1 ring-amber-500/30', label: 'Penilaian/Ujian' },
        libur_nasional: { bg: 'bg-rose-500 text-white font-bold', label: 'Libur Nasional' },
        libur_semester: { bg: 'bg-purple-500 text-white font-bold', label: 'Libur Semester' },
        kegiatan: { bg: 'bg-sky-500 text-white font-bold', label: 'Kegiatan Sekolah' }
    };

    let html = '';
    monthsDef.forEach(mon => {
        if (currentMatrixSemester !== 'all' && mon.smt !== currentMatrixSemester) {
            return;
        }

        const totalDays = new Date(mon.y, mon.m, 0).getDate();
        const firstDayIdx = new Date(mon.y, mon.m - 1, 1).getDay(); // 0=Sun

        let daysHtml = '';
        let dayCounter = 0;
        let currentDay = 1;

        let countEfektif = 0;
        let countLibur = 0;

        while (currentDay <= totalDays) {
            daysHtml += '<tr>';
            for (let col = 0; col < 7; col++) {
                if (dayCounter < firstDayIdx || currentDay > totalDays) {
                    daysHtml += '<td class="p-1 text-slate-200 text-[11px]">-</td>';
                } else {
                    const dateStr = `${mon.y}-${String(mon.m).padStart(2, '0')}-${String(currentDay).padStart(2, '0')}`;
                    const isSunday = (col === 0);

                    // Check agenda
                    let matchedAg = null;
                    for (const ag of agendas) {
                        if (dateStr >= ag.mulai && dateStr <= ag.selesai) {
                            matchedAg = ag;
                            break;
                        }
                    }

                    let cellClass = 'text-slate-700 hover:bg-slate-100';
                    let cellTitle = `${currentDay} ${mon.name} ${mon.y}`;

                    if (matchedAg) {
                        const style = categoryStyles[matchedAg.kategori] || categoryStyles.kegiatan;
                        cellClass = style.bg;
                        cellTitle += `: ${matchedAg.kegiatan} (${style.label})`;
                        if (['libur_nasional', 'libur_semester'].includes(matchedAg.kategori)) {
                            countLibur++;
                        } else {
                            countEfektif++;
                        }
                    } else if (isSunday) {
                        cellClass = 'text-rose-600 bg-rose-50/70 font-semibold';
                        cellTitle += ': Hari Libur Minggu';
                        countLibur++;
                    } else {
                        countEfektif++;
                    }

                    daysHtml += `
                        <td class="p-0.5">
                            <div title="${cellTitle}" class="w-7 h-7 sm:w-8 sm:h-8 mx-auto rounded-xl flex items-center justify-center text-[11px] transition-transform hover:scale-110 cursor-pointer ${cellClass}">
                                ${currentDay}
                            </div>
                        </td>
                    `;
                    currentDay++;
                }
                dayCounter++;
            }
            daysHtml += '</tr>';
        }

        html += `
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white px-4 py-2.5 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wide">
                            ${mon.name} ${mon.y}
                        </h4>
                        <span class="text-[10px] text-emerald-100 font-medium">Semester ${mon.smt}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">
                        ${countEfektif} HE • ${countLibur} HL
                    </span>
                </div>
                <div class="p-3">
                    <table class="w-full text-center border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400">
                                <th class="py-1 text-rose-600">Min</th>
                                <th class="py-1">Sen</th>
                                <th class="py-1">Sel</th>
                                <th class="py-1">Rab</th>
                                <th class="py-1">Kam</th>
                                <th class="py-1">Jum</th>
                                <th class="py-1 text-slate-600">Sab</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            ${daysHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Bind live listeners on load
document.addEventListener('DOMContentLoaded', function() {
    renderLiveMatrix();

    // Listen to all inputs inside agenda table
    const tbody = document.getElementById('agenda-container');
    if (tbody) {
        tbody.addEventListener('input', renderLiveMatrix);
        tbody.addEventListener('change', renderLiveMatrix);
    }
});
</script>
