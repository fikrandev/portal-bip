<?php
/**
 * Edit Penugasan Pegawai
 */
?>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800"><?= e($pageTitle) ?></h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui data penugasan pegawai.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?= url('kelola-pegawai/penugasan') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:text-primary-600 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-3xl">
    <div class="p-6">
        <form action="<?= url('kelola-pegawai/penugasan/update/' . $penugasan['id']) ?>" method="POST" enctype="multipart/form-data">
            <?= CSRF::input() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Data Pegawai (1 Kolom Full) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pegawai <span class="text-rose-500">*</span></label>
                    <select name="pegawai_id" required class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                        <?php foreach ($pegawaiList as $p): ?>
                            <option value="<?= e($p['id']) ?>" <?= (old('pegawai_id') ?? $penugasan['pegawai_id']) == $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['nama']) ?> <?= $p['niy'] ? '('.e($p['niy']).')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Nomor SK -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor SK <span class="text-rose-500">*</span></label>
                    <input type="text" name="no_sk" value="<?= old('no_sk') ?? e($penugasan['no_sk']) ?>" required
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors" 
                           placeholder="Contoh: SK/001/YAYASAN/2026">
                </div>

                <!-- Tanggal SK -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal SK <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_sk" value="<?= old('tanggal_sk') ?? $penugasan['tanggal_sk'] ?>" required
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                </div>

                <!-- Unit Tugas -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Unit Tugas <span class="text-rose-500">*</span></label>
                    <select name="unit_tugas_id" required class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                        <?php foreach ($unitTugasList as $ut): ?>
                            <option value="<?= e($ut['id']) ?>" <?= (old('unit_tugas_id') ?? $penugasan['unit_tugas_id']) == $ut['id'] ? 'selected' : '' ?>>
                                <?= e($ut['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jabatan -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan / Posisi <span class="text-rose-500">*</span></label>
                    <select name="jabatan_id" required class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                        <?php foreach ($jabatanList as $jb): ?>
                            <option value="<?= e($jb['id']) ?>" <?= (old('jabatan_id') ?? $penugasan['jabatan_id']) == $jb['id'] ? 'selected' : '' ?>>
                                <?= e($jb['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- TMT Mulai -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai Tugas (TMT) <span class="text-rose-500">*</span></label>
                    <input type="date" name="tmt_mulai" value="<?= old('tmt_mulai') ?? $penugasan['tmt_mulai'] ?>" required
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                </div>

                <!-- TST Selesai -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai (Opsional)</label>
                    <input type="date" name="tst_selesai" value="<?= old('tst_selesai') ?? $penugasan['tst_selesai'] ?>"
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    <p class="text-[11px] text-slate-500 mt-1">Kosongkan jika penugasan tidak memiliki batas waktu.</p>
                </div>
                
                <!-- Status & Berkas SK -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Penugasan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                        <option value="Aktif" <?= (old('status') ?? $penugasan['status']) === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Tidak Aktif" <?= (old('status') ?? $penugasan['status']) === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Berkas SK Baru (Opsional)</label>
                    <input type="file" name="file_sk" accept=".pdf,.jpg,.jpeg,.png"
                           class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <?php if ($penugasan['file_sk']): ?>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs text-emerald-600 font-medium">✅ SK Sudah Diunggah</span>
                            <a href="<?= url(ltrim($penugasan['file_sk'], '/')) ?>" target="_blank" class="text-xs text-primary-600 hover:underline">Lihat File</a>
                        </div>
                    <?php else: ?>
                        <p class="text-[11px] text-slate-500 mt-1">Format: PDF, JPG, PNG (Maks 2MB)</p>
                    <?php endif; ?>
                </div>
                
                <!-- Keterangan -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Keterangan / Catatan</label>
                    <textarea name="keterangan" rows="3" 
                              class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors" 
                              placeholder="Catatan tambahan mengenai penugasan ini..."><?= old('keterangan') ?? e($penugasan['keterangan']) ?></textarea>
                </div>
                
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-pegawai/penugasan') ?>" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-sm hover:shadow-primary-500/25">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
