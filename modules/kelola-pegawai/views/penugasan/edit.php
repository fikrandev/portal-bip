<?php
/**
 * Edit Penugasan Pegawai dalam Grup
 */
?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800"><?= e($pageTitle) ?></h1>
            <p class="text-sm text-slate-500 mt-1">Grup: <span class="font-bold text-primary-700"><?= e($grup['nama_grup'] ?? '-') ?></span></p>
        </div>
        <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grupId) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition-colors">
            ← Kembali ke Grup
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-primary-100/70 p-6 sm:p-8 shadow-sm">
        <form action="<?= url('kelola-pegawai/penugasan/detail/update/' . $penugasan['id']) ?>" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            
            <!-- Form 1 Kolom Vertikal Rapi -->
            <div class="grid grid-cols-1 gap-5">
                
                <!-- Pegawai -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Pegawai <span class="text-rose-500">*</span></label>
                    <select name="pegawai_id" required class="searchable-select w-full" data-placeholder="-- Pilih Pegawai --" data-search-placeholder="Cari nama pegawai, NIY...">
                        <?php foreach ($pegawaiList as $p): ?>
                            <option value="<?= e($p['id']) ?>" 
                                    data-badge="<?= e($p['niy'] ?: 'Non-NIY') ?>" 
                                    data-image="<?= !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '' ?>"
                                    data-subtext="<?= !empty($p['gelar']) ? 'Gelar: ' . e($p['gelar']) : '' ?>"
                                    <?= old('pegawai_id', $penugasan['pegawai_id']) == $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Unit Tugas -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Unit Tugas <span class="text-rose-500">*</span></label>
                    <select name="unit_tugas_id" required class="searchable-select w-full" data-placeholder="-- Pilih Unit Tugas --" data-search-placeholder="Cari unit tugas...">
                        <?php foreach ($unitTugasList as $ut): ?>
                            <option value="<?= e($ut['id']) ?>" <?= old('unit_tugas_id', $penugasan['unit_tugas_id']) == $ut['id'] ? 'selected' : '' ?>>
                                <?= e($ut['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jabatan -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Jabatan / Posisi <span class="text-rose-500">*</span></label>
                    <select name="jabatan_id" required class="searchable-select w-full" data-placeholder="-- Pilih Jabatan --" data-search-placeholder="Cari jabatan...">
                        <?php foreach ($jabatanList as $jb): ?>
                            <option value="<?= e($jb['id']) ?>" <?= old('jabatan_id', $penugasan['jabatan_id']) == $jb['id'] ? 'selected' : '' ?>>
                                <?= e($jb['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Nomor SK -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Nomor SK Penugasan</label>
                    <input type="text" name="no_sk" value="<?= e(old('no_sk', $penugasan['no_sk'] ?? '')) ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- Tanggal SK -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal SK</label>
                    <input type="date" name="tanggal_sk" value="<?= old('tanggal_sk', $penugasan['tanggal_sk'] ?? '') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- TMT Mulai -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Mulai Tugas (TMT) <span class="text-rose-500">*</span></label>
                    <input type="date" name="tmt_mulai" value="<?= old('tmt_mulai', $penugasan['tmt_mulai'] ?? '') ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- TST Selesai -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Selesai Tugas (TST)</label>
                    <input type="date" name="tst_selesai" value="<?= old('tst_selesai', $penugasan['tst_selesai'] ?? '') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- Status Penugasan -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Status Penugasan</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="Aktif" <?= old('status', $penugasan['status']) === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Tidak Aktif" <?= old('status', $penugasan['status']) === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>


                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm"><?= e(old('keterangan', $penugasan['keterangan'] ?? '')) ?></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grupId) ?>" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/25 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
