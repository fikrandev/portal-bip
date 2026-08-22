<?php
/**
 * Riwayat Karir Pegawai & Guru - Edit View
 */
?>
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                </div>
                <span>Edit Riwayat Karir</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Perbarui detail riwayat jabatan atau masa penugasan untuk pegawai <span class="font-bold text-slate-800"><?= e($karir['nama_pegawai']) ?></span>.
            </p>
        </div>
        <a href="<?= url('kelola-pegawai/karir') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
            ← Kembali ke Daftar Karir
        </a>
    </div>

    <!-- Form Box -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-pegawai/karir/update/' . $karir['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Pegawai Pilihan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Pegawai / Guru <span class="text-rose-500">*</span>
                </label>
                <select name="pegawai_id" required class="searchable-select w-full" data-placeholder="-- Pilih Pegawai --" data-search-placeholder="Cari nama pegawai, NIY, NIK...">
                    <?php foreach ($pegawaiList as $p): ?>
                        <option value="<?= e($p['id']) ?>" 
                                data-badge="<?= e($p['niy'] ?: ($p['nik'] ? 'NIK: ' . $p['nik'] : 'Non-NIY')) ?>" 
                                data-image="<?= !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '' ?>"
                                data-subtext="<?= !empty($p['gelar']) ? 'Gelar: ' . e($p['gelar']) : '' ?>"
                                <?= (old('pegawai_id', $karir['pegawai_id']) == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Tipe Karir -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tipe / Kategori Karir <span class="text-rose-500">*</span>
                    </label>
                    <select name="tipe_karir" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                        <option value="Penugasan SK" <?= old('tipe_karir', $karir['tipe_karir']) === 'Penugasan SK' ? 'selected' : '' ?>>Penugasan SK</option>
                        <option value="Penugasan Internal" <?= old('tipe_karir', $karir['tipe_karir']) === 'Penugasan Internal' ? 'selected' : '' ?>>Penugasan Internal</option>
                        <option value="Promosi Jabatan" <?= old('tipe_karir', $karir['tipe_karir']) === 'Promosi Jabatan' ? 'selected' : '' ?>>Promosi Jabatan</option>
                        <option value="Mutasi / Rotasi" <?= old('tipe_karir', $karir['tipe_karir']) === 'Mutasi / Rotasi' ? 'selected' : '' ?>>Mutasi / Rotasi</option>
                        <option value="Pengangkatan Awal" <?= old('tipe_karir', $karir['tipe_karir']) === 'Pengangkatan Awal' ? 'selected' : '' ?>>Pengangkatan Awal</option>
                        <option value="Riwayat Sebelumnya / Eksternal" <?= old('tipe_karir', $karir['tipe_karir']) === 'Riwayat Sebelumnya / Eksternal' ? 'selected' : '' ?>>Riwayat Sebelumnya / Eksternal</option>
                    </select>
                </div>

                <!-- Status Masa Tugas -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Status Jabatan <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                        <option value="Aktif" <?= old('status', $karir['status']) === 'Aktif' ? 'selected' : '' ?>>Aktif (Sedang Menjabat)</option>
                        <option value="Selesai" <?= old('status', $karir['status']) === 'Selesai' ? 'selected' : '' ?>>Selesai (Masa Tugas Berakhir)</option>
                        <option value="Riwayat Lalu" <?= old('status', $karir['status']) === 'Riwayat Lalu' ? 'selected' : '' ?>>Riwayat Lalu</option>
                    </select>
                </div>

                <!-- Unit Tugas -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Unit Tugas <span class="text-rose-500">*</span>
                    </label>
                    <select name="unit_tugas_id" id="unitTugasSelect" onchange="checkCustomUnit(this)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                        <option value="">-- Pilih Unit Tugas --</option>
                        <?php foreach ($unitTugasList as $u): ?>
                            <option value="<?= $u['id'] ?>" data-nama="<?= e($u['nama']) ?>" <?= (old('unit_tugas_id', $karir['unit_tugas_id']) == $u['id'] || old('unit_tugas', $karir['unit_tugas']) == $u['nama']) ? 'selected' : '' ?>><?= e($u['nama']) ?></option>
                        <?php endforeach; ?>
                        <option value="custom" <?= (!empty($karir['unit_tugas']) && empty($karir['unit_tugas_id'])) ? 'selected' : '' ?>>+ Input Unit Lainnya...</option>
                    </select>
                    <input type="text" name="custom_unit_tugas" id="customUnitInput" value="<?= e(old('custom_unit_tugas', $karir['unit_tugas'] ?? '')) ?>" placeholder="Ketik nama unit tugas..." class="w-full mt-2 px-4 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm <?= (!empty($karir['unit_tugas']) && empty($karir['unit_tugas_id'])) ? '' : 'hidden' ?>">
                </div>

                <!-- Jabatan -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Nama Jabatan / Posisi <span class="text-rose-500">*</span>
                    </label>
                    <select name="jabatan_id" id="jabatanSelect" onchange="checkCustomJabatan(this)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                        <option value="">-- Pilih Jabatan --</option>
                        <?php foreach ($jabatanList as $j): ?>
                            <option value="<?= $j['id'] ?>" data-nama="<?= e($j['nama']) ?>" <?= (old('jabatan_id', $karir['jabatan_id']) == $j['id'] || old('jabatan', $karir['jabatan']) == $j['nama']) ? 'selected' : '' ?>><?= e($j['nama']) ?></option>
                        <?php endforeach; ?>
                        <option value="custom" <?= (!empty($karir['jabatan']) && empty($karir['jabatan_id'])) ? 'selected' : '' ?>>+ Input Jabatan Lainnya...</option>
                    </select>
                    <input type="text" name="custom_jabatan" id="customJabatanInput" value="<?= e(old('custom_jabatan', $karir['jabatan'] ?? '')) ?>" placeholder="Ketik nama jabatan..." class="w-full mt-2 px-4 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm <?= (!empty($karir['jabatan']) && empty($karir['jabatan_id'])) ? '' : 'hidden' ?>">
                </div>

                <!-- Nomor SK -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Nomor SK / Dokumen
                    </label>
                    <input type="text" name="no_sk" value="<?= e(old('no_sk', $karir['no_sk'] ?? '')) ?>" placeholder="Contoh: 001/SK-DIR/YYS/2026" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                </div>

                <!-- Tanggal SK -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tanggal Penetapan SK
                    </label>
                    <input type="date" name="tanggal_sk" value="<?= old('tanggal_sk', $karir['tanggal_sk'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                </div>

                <!-- TMT Mulai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        TMT Mulai Tugas <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tmt_mulai" required value="<?= old('tmt_mulai', $karir['tmt_mulai'] ?? date('Y-m-d')) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                </div>

                <!-- TST Selesai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        TST Selesai Tugas
                    </label>
                    <input type="date" name="tst_selesai" value="<?= old('tst_selesai', $karir['tst_selesai'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika masih aktif menjabat sampai saat ini.</p>
                </div>
            </div>

            <!-- Pejabat Penandatangan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Pejabat Penandatangan SK / Pengesah
                </label>
                <input type="text" name="penandatangan_sk" value="<?= e(old('penandatangan_sk', $karir['penandatangan_sk'] ?? '')) ?>" placeholder="Contoh: Ketua Yayasan Bina Insan Paripurna / Kepala Sekolah" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors">
            </div>

            <!-- Upload Berkas SK -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Berkas / Dokumen SK Pendukung
                </label>
                <?php if (!empty($karir['file_sk'])): ?>
                    <div class="flex items-center gap-2 mb-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <a href="<?= url(ltrim($karir['file_sk'], '/')) ?>" target="_blank" class="text-xs font-semibold text-primary-600 hover:underline">
                            Lihat Berkas SK Saat Ini
                        </a>
                    </div>
                <?php endif; ?>
                <input type="file" name="file_sk" accept="application/pdf,image/png,image/jpeg,image/jpg" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:border-primary-500 transition-colors">
                <p class="text-[11px] text-slate-400 mt-1">Unggah file baru jika ingin mengganti berkas yang ada.</p>
            </div>

            <!-- Keterangan Tambahan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Keterangan Tambahan / Catatan Karir
                </label>
                <textarea name="keterangan" rows="3" placeholder="Catatan prestasi, tugas spesifik, atau informasi penting lainnya..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-primary-500 transition-colors"><?= e(old('keterangan', $karir['keterangan'] ?? '')) ?></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-pegawai/karir') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-7 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-primary-500/25 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function checkCustomUnit(select) {
    const customInput = document.getElementById('customUnitInput');
    if (select.value === 'custom') {
        customInput.classList.remove('hidden');
        customInput.focus();
    } else {
        customInput.classList.add('hidden');
    }
}

function checkCustomJabatan(select) {
    const customInput = document.getElementById('customJabatanInput');
    if (select.value === 'custom') {
        customInput.classList.remove('hidden');
        customInput.focus();
    } else {
        customInput.classList.add('hidden');
    }
}
</script>
