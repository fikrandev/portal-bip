<?php /** Create Role View with Advanced Permission Matrix */ ?>
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('roles') ?>" class="text-xs font-semibold text-primary-600 hover:text-primary-700 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar Peran
                </a>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2.5">
                <span><?= $sourceRole ? 'Duplikat Peran: ' . e($sourceRole['name']) : 'Tambah Peran Baru' ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                <?= $sourceRole ? 'Menyalin hak akses dari peran <strong>' . e($sourceRole['name']) . '</strong>. Silakan sesuaikan nama peran, jenjang, atau pilih izin tambahan.' : 'Tentukan nama peran dan pilih hak akses sub-fitur modul beserta izin CRUD (Lihat, Tambah, Ubah, Hapus, Ekspor & Approve).' ?>
            </p>
        </div>
    </div>

    <?php if ($sourceRole): ?>
    <div class="p-4 rounded-2xl bg-teal-50/80 border border-teal-200 text-teal-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center flex-shrink-0 text-lg">
                📋
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-teal-950">Mode Duplikat Peran Aktif</h4>
                <p class="text-xs text-teal-700">
                    Template: <strong><?= e($sourceRole['name']) ?></strong> &bull; <?= count($selectedPermIds ?? []) ?> hak akses terpilih otomatis.
                </p>
            </div>
        </div>
        <a href="<?= url('roles/create') ?>" class="text-xs text-teal-700 hover:text-teal-900 font-bold underline whitespace-nowrap self-end sm:self-auto">
            Buat Peran Kosong Baru &rarr;
        </a>
    </div>
    <?php endif; ?>

    <!-- Form Container -->
    <form action="<?= url('roles/store') ?>" method="POST" id="roleForm" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Informasi Dasar Peran -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7 space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                Identitas Peran <?= $sourceRole ? 'Duplikasi' : 'Baru' ?>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Peran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="<?= old('name', $sourceRole ? $sourceRole['name'] . ' (Salinan)' : '') ?>" required
                           placeholder="Contoh: Kepala Sekolah, Waka Kurikulum, Guru Mata Pelajaran"
                           onkeyup="generateSlug(this.value)"
                           class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-sm font-medium transition-all">
                </div>
                <div>
                    <label for="slug" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Slug Sistem <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="slug" name="slug" value="<?= old('slug', $sourceRole ? $sourceRole['slug'] . '_salinan' : '') ?>" required
                           placeholder="contoh: kepala_sekolah, waka_kurikulum"
                           class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 font-mono text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                    <p class="text-[11px] text-slate-400 mt-1">Gunakan huruf kecil dan garis bawah (underscore) atau strip.</p>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Deskripsi / Catatan Peran
                </label>
                <textarea id="description" name="description" rows="2"
                          class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-sm font-medium transition-all"
                          placeholder="Deskripsikan wewenang atau tanggung jawab peran ini..."><?= old('description', $sourceRole ? $sourceRole['description'] : '') ?></textarea>
            </div>

            <!-- Filter Jenjang Akses (RBAC Jenjang) -->
            <?php
            $oldJenjang = old('allowed_jenjang');
            if ($oldJenjang === null && !empty($sourceJenjang)) {
                $oldJenjang = $sourceJenjang;
            } elseif ($oldJenjang === null) {
                $oldJenjang = [];
            } elseif (!is_array($oldJenjang)) {
                $oldJenjang = explode(',', strtoupper($oldJenjang));
            }
            $allJenjangList = [
                'PAUD' => 'Pendidikan Anak Usia Dini / TK',
                'SD'   => 'Sekolah Dasar',
                'SMP'  => 'Sekolah Menengah Pertama',
                'SMA'  => 'Sekolah Menengah Atas'
            ];
            ?>
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 flex items-center gap-2">
                            <span>Filter Jenjang Akses Peran</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Scope Jenjang</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Centang jenjang sekolah untuk peran baru ini. Pengguna dengan peran ini otomatis hanya dapat melihat dan mengelola data sesuai jenjang yang dicentang.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                    <?php foreach ($allJenjangList as $jCode => $jDesc): ?>
                        <?php $isChecked = in_array($jCode, $oldJenjang, true); ?>
                        <label class="relative flex flex-col p-3 rounded-2xl border transition-all cursor-pointer select-none <?= $isChecked ? 'border-amber-400 bg-amber-50/50 shadow-xs' : 'border-slate-200 bg-slate-50/40 hover:bg-white hover:border-slate-300' ?>">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-extrabold text-sm text-slate-800"><?= $jCode ?></span>
                                <input type="checkbox" name="allowed_jenjang[]" value="<?= $jCode ?>" <?= $isChecked ? 'checked' : '' ?> class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                            </div>
                            <span class="text-[10px] text-slate-500 line-clamp-1"><?= $jDesc ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-[10px] text-slate-400 italic mt-2">
                    *Jika tidak ada yang dicentang atau semua dicentang, maka peran ini memiliki akses ke <strong>Semua Jenjang</strong>.
                </p>
            </div>
        </div>

        <?php
        $checkedIds = $selectedPermIds ?? [];
        include __DIR__ . '/_permission_matrix.php';
        ?>

        <!-- Form Buttons -->
        <div class="sticky bottom-4 z-20 bg-white/90 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-lg p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-500">
                Pastikan identitas peran dan hak akses sudah sesuai sebelum menambahkan.
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="<?= url('roles') ?>" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs text-center transition-colors">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Simpan Peran Baru
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function generateSlug(text) {
    const slugInput = document.getElementById('slug');
    if (!slugInput.dataset.manualEdited) {
        slugInput.value = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '_')
            .replace(/^-+|-+$/g, '');
    }
}

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manualEdited = "true";
});
</script>
