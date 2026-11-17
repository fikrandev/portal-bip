<?php /** Roles List View */ ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>
            <span>Kelola Peran & Hak Akses</span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Kelola peran, izin, dan hak akses otorisasi pengguna.</p>
    </div>
    <?php if (RBAC::hasPermission('roles.create')): ?>
    <div class="flex items-center gap-2.5">
        <a href="<?= url('roles/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-500/25 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Tambah Peran</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($roles as $role): ?>
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 hover:shadow-lg hover:border-primary-200 transition-all duration-300">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-2xl bg-primary-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-primary-900"><?= e($role['name']) ?></h4>
                <p class="text-xs text-slate-400"><?= e($role['slug']) ?></p>
            </div>
        </div>
        <p class="text-sm text-slate-500 mb-3 line-clamp-2"><?= e($role['description'] ?? '-') ?></p>
        <?php if (!empty($role['allowed_jenjang'])): ?>
            <div class="flex items-center gap-1 mb-3 flex-wrap">
                <span class="text-[10px] text-slate-400 font-bold">Jenjang:</span>
                <?php foreach (explode(',', $role['allowed_jenjang']) as $j): ?>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><?= e(trim($j)) ?></span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="flex items-center gap-1 mb-3">
                <span class="text-[10px] text-slate-400 font-bold">Jenjang:</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">Semua Jenjang</span>
            </div>
        <?php endif; ?>
        <div class="flex items-center justify-between">
            <span class="text-xs text-primary-600 font-medium bg-primary-50 px-2.5 py-1 rounded-2xl"><?= $role['user_count'] ?> pengguna</span>
            <div class="flex gap-1 items-center">
                <?php if (RBAC::hasPermission('roles.create')): ?>
                <button type="button" 
                        onclick="openModalCopyRole(<?= htmlspecialchars(json_encode([
                            'id' => $role['id'],
                            'name' => $role['name'],
                            'slug' => $role['slug'],
                            'description' => $role['description'] ?? '',
                            'allowed_jenjang' => $role['allowed_jenjang'] ?? ''
                        ])) ?>)" 
                        class="p-1.5 rounded-full text-teal-600 hover:bg-teal-50 hover:text-teal-700 transition-colors" 
                        title="Salin / Duplikat Peran Ini">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>
                </button>
                <?php endif; ?>
                <?php if (RBAC::hasPermission('roles.update')): ?>
                <a href="<?= url('roles/edit/' . $role['id']) ?>" class="p-1.5 rounded-full text-primary-500 hover:bg-primary-50 transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                </a>
                <?php endif; ?>
                <?php if (RBAC::hasPermission('roles.delete') && !$role['is_system']): ?>
                <form method="POST" action="<?= url('roles/delete/' . $role['id']) ?>" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Yakin ingin menghapus peran ini?');">
                    <?= CSRF::field() ?>
                    <button type="submit" class="p-1.5 rounded-full text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Hapus">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Salin / Duplikat Peran -->
<div id="modal-copy-role" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 sm:p-7 transform scale-95 transition-all duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 border border-teal-200 flex items-center justify-center shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-800">Salin / Duplikat Peran</h3>
                    <p class="text-xs text-slate-400">Duplikasi peran dan seluruh hak akses izinnya secara instan</p>
                </div>
            </div>
            <button type="button" onclick="closeModalCopyRole()" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="form-copy-role" action="" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>

            <!-- Info Peran Sumber -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Peran Sumber (Template):</span>
                    <h4 id="copy-source-name" class="font-extrabold text-slate-800 text-sm mt-0.5"></h4>
                </div>
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                    Semua Izin Disalin
                </span>
            </div>

            <!-- Nama Peran Baru -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Nama Peran Baru <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="copy-name" name="name" required 
                       oninput="generateCopySlug(this.value)"
                       placeholder="Contoh: Kepala Sekolah SMA" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-bold text-slate-800">
            </div>

            <!-- Slug -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Slug Sistem <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="copy-slug" name="slug" required 
                       placeholder="contoh: kepala_sekolah_sma" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-mono text-slate-800">
            </div>

            <!-- Pilih Jenjang Baru -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Pilih Jenjang Akses Baru
                </label>
                <p class="text-[11px] text-slate-400 mb-2">Pilih lingkup jenjang sekolah untuk peran baru ini:</p>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $jj): ?>
                    <label class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white hover:border-amber-400 cursor-pointer select-none transition-all">
                        <input type="checkbox" name="allowed_jenjang[]" value="<?= $jj ?>" id="copy-jenjang-<?= $jj ?>" class="rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                        <span class="font-extrabold text-xs text-slate-800"><?= $jj ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tombol Aksi Modal -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2.5">
                <a href="#" id="copy-btn-customize" class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline flex items-center gap-1">
                    <span>Atur &amp; Centang Manual</span> &rarr;
                </a>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeModalCopyRole()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold shadow-md shadow-teal-600/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                        <span>Duplikat Sekarang</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openModalCopyRole(role) {
    document.getElementById('copy-source-name').innerText = role.name + ' (' + role.slug + ')';
    document.getElementById('copy-name').value = role.name + ' (Salinan)';
    generateCopySlug(role.name + ' (Salinan)');
    
    // Set form action
    document.getElementById('form-copy-role').action = '<?= url("roles/duplicate/") ?>' + role.id;
    document.getElementById('copy-btn-customize').href = '<?= url("roles/create?from=") ?>' + role.id;

    // Set jenjang checkboxes based on source role
    const jenjangArr = role.allowed_jenjang ? role.allowed_jenjang.split(',').map(s => s.trim().toUpperCase()) : [];
    ['PAUD', 'SD', 'SMP', 'SMA'].forEach(function(j) {
        const el = document.getElementById('copy-jenjang-' + j);
        if (el) {
            el.checked = jenjangArr.length === 0 || jenjangArr.includes(j);
        }
    });

    const m = document.getElementById('modal-copy-role');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
    setTimeout(() => {
        const nameInput = document.getElementById('copy-name');
        nameInput.focus();
        nameInput.select();
    }, 100);
}

function closeModalCopyRole() {
    const m = document.getElementById('modal-copy-role');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}

function generateCopySlug(val) {
    const slug = val.toLowerCase()
        .replace(/[^a-z0-9\s_-]/g, '')
        .replace(/[\s-]+/g, '_')
        .replace(/^_+|_+$/g, '');
    document.getElementById('copy-slug').value = slug;
}
</script>
