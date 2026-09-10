<?php /** Edit Role View with Advanced Permission Matrix */ ?>
<div class="max-w-5xl mx-auto space-y-6">
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
                Atur wewenang dan centang akses fitur aplikasi, termasuk izin khusus verifikasi & persetujuan (*Approve*).
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
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
        </div>

        <!-- Matriks Hak Akses & Fitur -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-base font-bold text-slate-800 tracking-tight flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Konfigurasi Hak Akses Fitur & Approval
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Centang hak akses yang diizinkan untuk peran ini. Kotak berwarna <span class="font-bold text-purple-600">Ungu (⭐)</span> adalah akses persetujuan/verifikasi (*Approval*).
                    </p>
                </div>

                <!-- Action Toolbar -->
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="toggleAllPermissions(true)" 
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Pilih Semua
                    </button>
                    <button type="button" onclick="toggleAllPermissions(false)" 
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Kosongkan
                    </button>
                    <button type="button" onclick="toggleOnlyApprove(true)" 
                            class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-sm">
                        <span>⭐</span>
                        Centang Semua Approve
                    </button>
                </div>
            </div>

            <!-- Quick Filter Input -->
            <div class="relative">
                <input type="text" id="permSearch" onkeyup="filterPermissions()" 
                       placeholder="🔍 Cari modul atau hak akses (misal: perangkat, approve, siswa, cuti)..." 
                       class="w-full px-4 py-3 pl-11 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-4 top-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>

            <!-- Permission Matrix Grouped by Module -->
            <div class="space-y-4" id="moduleContainer">
                <?php 
                $grouped = [];
                foreach ($permissions as $p) { 
                    $modKey = $p['module_name'] ?? 'Lainnya';
                    $grouped[$modKey][] = $p; 
                }

                foreach ($grouped as $modName => $perms): 
                    $totalInMod = count($perms);
                    $checkedInMod = 0;
                    foreach ($perms as $p) {
                        if (in_array($p['id'], $rolePermIds)) $checkedInMod++;
                    }
                ?>
                <div class="module-card border border-slate-200 rounded-3xl p-5 hover:border-slate-300 transition-all bg-white shadow-xs" data-module-name="<?= strtolower(e($modName)) ?>">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <?= !empty($perms[0]['icon_svg']) ? '<span class="w-4 h-4 [&>svg]:w-4 [&>svg]:h-4">' . $perms[0]['icon_svg'] . '</span>' : '📁' ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 tracking-tight"><?= e($modName) ?></h3>
                                <p class="text-[11px] text-slate-400">
                                    <span class="mod-selected-count font-semibold text-primary-600"><?= $checkedInMod ?></span> dari <?= $totalInMod ?> hak akses aktif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 self-end sm:self-auto">
                            <button type="button" onclick="toggleModuleGroup(this, true)" 
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-600 hover:text-primary-700 hover:bg-slate-100 transition-colors">
                                Pilih Semua
                            </button>
                            <span class="text-slate-300">|</span>
                            <button type="button" onclick="toggleModuleGroup(this, false)" 
                                    class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-600 hover:text-rose-600 hover:bg-slate-100 transition-colors">
                                Kosongkan
                            </button>
                        </div>
                    </div>

                    <!-- Permission Checkboxes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                        <?php foreach ($perms as $p): 
                            $isApprove = (strpos($p['slug'], '.approve') !== false) || (strpos(strtolower($p['name']), 'verifikasi') !== false) || (strpos(strtolower($p['name']), 'persetujuan') !== false) || (strpos(strtolower($p['name']), 'pengesahan') !== false);
                            $isChecked = in_array($p['id'], $rolePermIds);

                            // Action styles
                            if ($isApprove) {
                                $borderStyle = 'border-purple-300 bg-purple-50/40 hover:bg-purple-50 hover:border-purple-400 has-[:checked]:bg-purple-100 has-[:checked]:border-purple-600 has-[:checked]:ring-1 has-[:checked]:ring-purple-400/30';
                                $badgeClass = 'bg-purple-600 text-white font-black text-[9px] px-2 py-0.5 rounded-md uppercase tracking-wider';
                            } elseif (str_ends_with($p['slug'], '.delete') || str_ends_with($p['slug'], '.reset')) {
                                $borderStyle = 'border-slate-200 hover:border-rose-300 has-[:checked]:bg-rose-50/50 has-[:checked]:border-rose-400';
                                $badgeClass = 'bg-rose-100 text-rose-700 text-[10px] font-bold px-1.5 py-0.5 rounded';
                            } elseif (str_ends_with($p['slug'], '.update')) {
                                $borderStyle = 'border-slate-200 hover:border-amber-300 has-[:checked]:bg-amber-50/50 has-[:checked]:border-amber-400';
                                $badgeClass = 'bg-amber-100 text-amber-700 text-[10px] font-bold px-1.5 py-0.5 rounded';
                            } elseif (str_ends_with($p['slug'], '.create')) {
                                $borderStyle = 'border-slate-200 hover:border-emerald-300 has-[:checked]:bg-emerald-50/50 has-[:checked]:border-emerald-400';
                                $badgeClass = 'bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded';
                            } else {
                                $borderStyle = 'border-slate-200 hover:border-blue-300 has-[:checked]:bg-blue-50/50 has-[:checked]:border-blue-400';
                                $badgeClass = 'bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded';
                            }
                        ?>
                        <label class="perm-item relative flex items-start gap-3 p-3 rounded-2xl border <?= $borderStyle ?> cursor-pointer transition-all select-none"
                               data-perm-name="<?= strtolower(e($p['name'] . ' ' . $p['slug'] . ' ' . ($p['description'] ?? ''))) ?>"
                               data-is-approve="<?= $isApprove ? '1' : '0' ?>"
                               title="<?= e($p['description'] ?? $p['name']) ?>">
                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" 
                                   <?= $isChecked ? 'checked' : '' ?> 
                                   onchange="updateCardCounter(this)"
                                   class="perm-checkbox mt-0.5 rounded-lg border-slate-300 text-primary-600 focus:ring-0 focus:ring-transparent h-4 w-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-xs font-bold text-slate-800 truncate"><?= e($p['name']) ?></span>
                                    <?php if ($isApprove): ?>
                                        <span class="<?= $badgeClass ?>">⭐ APPROVE</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-[10px] text-slate-400 font-mono truncate"><?= e($p['slug']) ?></p>
                                <?php if (!empty($p['description'])): ?>
                                    <p class="text-[10px] text-slate-500 line-clamp-1 mt-0.5 leading-tight"><?= e($p['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

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

<!-- Dynamic Matrix Interactive Scripts -->
<script>
function updateCardCounter(checkbox) {
    const card = checkbox.closest('.module-card');
    if (!card) return;
    const total = card.querySelectorAll('.perm-checkbox').length;
    const checked = card.querySelectorAll('.perm-checkbox:checked').length;
    const counterSpan = card.querySelector('.mod-selected-count');
    if (counterSpan) counterSpan.textContent = checked;
}

function toggleModuleGroup(button, state) {
    const card = button.closest('.module-card');
    if (!card) return;
    const checkboxes = card.querySelectorAll('.perm-checkbox');
    checkboxes.forEach(cb => {
        // Only toggle visible checkboxes if filtered
        const item = cb.closest('.perm-item');
        if (item && item.style.display !== 'none') {
            cb.checked = state;
        }
    });
    const checked = card.querySelectorAll('.perm-checkbox:checked').length;
    const counterSpan = card.querySelector('.mod-selected-count');
    if (counterSpan) counterSpan.textContent = checked;
}

function toggleAllPermissions(state) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        const item = cb.closest('.perm-item');
        if (item && item.style.display !== 'none') {
            cb.checked = state;
        }
    });
    document.querySelectorAll('.module-card').forEach(card => {
        const checked = card.querySelectorAll('.perm-checkbox:checked').length;
        const counterSpan = card.querySelector('.mod-selected-count');
        if (counterSpan) counterSpan.textContent = checked;
    });
}

function toggleOnlyApprove(state) {
    document.querySelectorAll('.perm-item').forEach(item => {
        if (item.getAttribute('data-is-approve') === '1') {
            const cb = item.querySelector('.perm-checkbox');
            if (cb) cb.checked = state;
        }
    });
    document.querySelectorAll('.module-card').forEach(card => {
        const checked = card.querySelectorAll('.perm-checkbox:checked').length;
        const counterSpan = card.querySelector('.mod-selected-count');
        if (counterSpan) counterSpan.textContent = checked;
    });
}

function filterPermissions() {
    const query = document.getElementById('permSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.module-card');

    cards.forEach(card => {
        const modName = card.getAttribute('data-module-name') || '';
        const items = card.querySelectorAll('.perm-item');
        let hasVisibleChild = false;

        if (modName.includes(query)) {
            // If module name matches, show all items in it
            items.forEach(item => item.style.display = '');
            card.style.display = '';
        } else {
            // Check individual permissions
            items.forEach(item => {
                const text = item.getAttribute('data-perm-name') || '';
                const isApprove = item.getAttribute('data-is-approve') === '1';
                
                if (query === 'approve' && isApprove) {
                    item.style.display = '';
                    hasVisibleChild = true;
                } else if (text.includes(query)) {
                    item.style.display = '';
                    hasVisibleChild = true;
                } else {
                    item.style.display = 'none';
                }
            });

            card.style.display = hasVisibleChild ? '' : 'none';
        }
    });
}
</script>
