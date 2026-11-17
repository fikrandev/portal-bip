<?php /** Edit Role View with Advanced Permission Matrix */ ?>
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
                <span>Edit Hak Akses Peran:</span>
                <span class="px-3 py-1 rounded-xl bg-primary-100/70 text-primary-800 text-lg border border-primary-200">
                    <?= e($role['name']) ?>
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Atur wewenang dan centang akses sub-fitur modul serta aksi CRUD (Lihat, Tambah, Ubah, Hapus, Ekspor & Approve).
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <?php if (RBAC::hasPermission('roles.create')): ?>
            <a href="<?= url('roles/create?from=' . $role['id']) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200/80 text-xs font-bold transition-all shadow-xs" title="Duplikasi peran ini menjadi peran baru">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                </svg>
                <span>Salin Peran Ini</span>
            </a>
            <?php endif; ?>
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold <?= $role['is_system'] ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600' ?>">
                <?= $role['is_system'] ? 'Peran Sistem (Kunci)' : 'Peran Kustom' ?>
            </span>
        </div>
    </div>

    <!-- Form Container -->
    <form action="<?= url('roles/update/' . $role['id']) ?>" method="POST" id="roleForm" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Informasi Dasar Peran -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7 space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                Identitas Peran
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Peran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="<?= e($role['name']) ?>" required
                           class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-sm font-medium transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Slug Sistem
                    </label>
                    <input type="text" value="<?= e($role['slug']) ?>" disabled
                           class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-2xl text-slate-500 text-sm font-mono cursor-not-allowed">
                    <p class="text-[11px] text-slate-400 mt-1">Slug digunakan secara internal oleh sistem dan tidak dapat diubah.</p>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Deskripsi / Catatan Peran
                </label>
                <textarea id="description" name="description" rows="2"
                          class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-sm font-medium transition-all"
                          placeholder="Deskripsikan wewenang atau tanggung jawab peran ini..."><?= e($role['description'] ?? '') ?></textarea>
            </div>

            <!-- Filter Jenjang Akses (RBAC Jenjang) -->
            <?php
            $currentRoleJenjang = !empty($role['allowed_jenjang']) ? explode(',', strtoupper($role['allowed_jenjang'])) : [];
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
                            Centang jenjang sekolah untuk peran ini. Pengguna dengan peran ini otomatis hanya dapat melihat dan mengelola data sesuai jenjang yang dicentang.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                    <?php foreach ($allJenjangList as $jCode => $jDesc): ?>
                        <?php $isChecked = in_array($jCode, $currentRoleJenjang, true); ?>
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
        $checkedIds = $rolePermIds ?? [];
        include __DIR__ . '/_permission_matrix.php';
        ?>

        <!-- Form Buttons -->
        <div class="sticky bottom-4 z-20 bg-white/90 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-lg p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-500">
                Pastikan seluruh wewenang telah disesuaikan sebelum menyimpan perubahan peran.
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="<?= url('roles') ?>" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs text-center transition-colors">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Simpan Perubahan Peran
                </button>
            </div>
        </div>
    </form>
</div>
