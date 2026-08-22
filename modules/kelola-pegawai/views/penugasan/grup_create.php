<?php
/**
 * Tambah Grup Penugasan - View
 */
?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800"><?= e($pageTitle) ?></h1>
            <p class="text-sm text-slate-500 mt-1">Buat grup periode SK pembagian tugas dan penugasan pegawai.</p>
        </div>
        <a href="<?= url('kelola-pegawai/penugasan') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition-colors">
            ← Kembali
        </a>
    </div>

    <form action="<?= url('kelola-pegawai/penugasan/grup/store') ?>" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl border border-primary-100/70 p-6 sm:p-8 shadow-sm">
        <?= CSRF::field() ?>

        <!-- Form 1 Kolom Vertikal -->
        <div class="grid grid-cols-1 gap-5">
            <div>
                <label class="block text-sm font-semibold text-primary-900 mb-1.5">
                    Nama Grup Penugasan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_grup" value="<?= old('nama_grup') ?>" required placeholder="Contoh: Pembagian Tugas 2026/2027 Ganjil" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-primary-900 mb-1.5">Semester</label>
                <select name="semester" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <option value="Ganjil" <?= old('semester') === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                    <option value="Genap" <?= old('semester') === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                </select>
            </div>

            <!-- Upload Gambar Kop & Footer Surat Khusus SK Grup -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kop Surat (Header) -->
                <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-200/80">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-base">🖼️</span>
                        <h3 class="text-sm font-bold text-amber-950">Gambar Kop Surat (Header)</h3>
                    </div>
                    <p class="text-xs text-amber-800 mb-3">Gambar/banner Kop Surat resmi di bagian atas SK (Format: PNG, JPG, JPEG maks. 5MB).</p>

                    <div>
                        <input type="file" name="file_kop" accept="image/png,image/jpeg,image/jpg,image/webp" 
                               onchange="previewImg(this, 'kop-preview-container', 'kop-preview')"
                               class="w-full px-4 py-2 bg-white border border-amber-300 rounded-xl text-slate-800 text-sm focus:border-amber-500">
                        <div id="kop-preview-container" class="hidden mt-3 p-2 bg-white rounded-xl border border-amber-200 text-center">
                            <p class="text-[11px] text-slate-400 mb-1">Pratinjau Kop Surat:</p>
                            <img id="kop-preview" src="" alt="Pratinjau Kop" class="max-h-20 mx-auto object-contain border border-slate-100 rounded">
                        </div>
                    </div>
                </div>

                <!-- Footer Surat -->
                <div class="p-5 rounded-2xl bg-sky-50/60 border border-sky-200/80">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-base">📑</span>
                        <h3 class="text-sm font-bold text-sky-950">Gambar Footer Surat (Footer)</h3>
                    </div>
                    <p class="text-xs text-sky-800 mb-3">Gambar/banner Footer resmi di bagian bawah SK (Format: PNG, JPG, JPEG maks. 5MB).</p>

                    <div>
                        <input type="file" name="file_footer" accept="image/png,image/jpeg,image/jpg,image/webp" 
                               onchange="previewImg(this, 'footer-preview-container', 'footer-preview')"
                               class="w-full px-4 py-2 bg-white border border-sky-300 rounded-xl text-slate-800 text-sm focus:border-sky-500">
                        <div id="footer-preview-container" class="hidden mt-3 p-2 bg-white rounded-xl border border-sky-200 text-center">
                            <p class="text-[11px] text-slate-400 mb-1">Pratinjau Footer Surat:</p>
                            <img id="footer-preview" src="" alt="Pratinjau Footer" class="max-h-20 mx-auto object-contain border border-slate-100 rounded">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-primary-900 mb-1.5">Nomor SK Kolektif</label>
                <input type="text" name="no_sk" value="<?= old('no_sk') ?>" placeholder="Contoh: 001/SK-TUGAS/YYS/2026" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal SK</label>
                <input type="date" name="tanggal_sk" value="<?= old('tanggal_sk', date('Y-m-d')) ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Mulai Tugas (TMT)</label>
                <input type="date" name="tmt_mulai" value="<?= old('tmt_mulai', date('Y-m-d')) ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Selesai Tugas (TST)</label>
                <input type="date" name="tst_selesai" value="<?= old('tst_selesai') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                <p class="text-xs text-slate-400 mt-1">Kosongkan jika masa tugas tidak dibatasi.</p>
            </div>

            <!-- Informasi Penandatangan SK Cetak -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-base">✍️</span>
                    <h3 class="text-sm font-bold text-slate-800">Pengesahan / Penandatangan SK (Untuk Cetak SK)</h3>
                </div>
                <p class="text-xs text-slate-500 mb-4">Informasi pejabat yang menandatangani Surat Keputusan (SK) penugasan ini saat dicetak.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pejabat Penandatangan</label>
                        <input type="text" name="penandatangan_nama" value="<?= old('penandatangan_nama') ?>" placeholder="Contoh: H. Ahmad Dahlan, S.Pd., M.M." class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan Penandatangan</label>
                        <input type="text" name="penandatangan_jabatan" value="<?= old('penandatangan_jabatan', 'Ketua Yayasan Bina Insan Paripurna') ?>" placeholder="Contoh: Ketua Yayasan / Kepala Sekolah" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIP / NIY Penandatangan</label>
                        <input type="text" name="penandatangan_nip" value="<?= old('penandatangan_nip') ?>" placeholder="Contoh: 19800101 200501 1 001 / -" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kota / Tempat Penetapan SK</label>
                        <input type="text" name="kota_sk" value="<?= old('kota_sk', 'Palu') ?>" placeholder="Contoh: Palu" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-primary-900 mb-1.5">Keterangan Tambahan</label>
                <textarea name="keterangan" rows="3" placeholder="Keterangan opsional mengenai grup penugasan ini..." class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm"><?= old('keterangan') ?></textarea>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                <label class="inline-flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-emerald-400 text-emerald-600 focus:ring-0">
                    <div>
                        <span class="text-sm font-bold text-emerald-950">Jadikan Grup Aktif Saat Ini</span>
                        <p class="text-xs text-emerald-700 mt-0.5">Jika dicentang, grup ini akan aktif bersamaan dengan grup unit aktif lainnya (Yayasan, PAUD, SD, SMP, SMA, dll.) dan jabatan pegawai di sistem akan mengikuti penugasan grup ini.</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-pegawai/penugasan') ?>" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/25 transition-all">
                    Simpan Grup Penugasan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function previewImg(input, containerId, previewId) {
    const container = document.getElementById(containerId);
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        container.classList.add('hidden');
    }
}
</script>
