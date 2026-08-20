<?php /** Edit Role View with Permission Matrix */ ?>
<div class="max-w-3xl">
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8">
        <form action="<?= url('roles/update/' . $role['id']) ?>" method="POST">
            <?= CSRF::field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Peran <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="<?= e($role['name']) ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Slug</label>
                    <input type="text" value="<?= e($role['slug']) ?>" disabled class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
            </div>
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-primary-800 mb-1.5">Deskripsi</label>
                <textarea id="description" name="description" rows="2" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium resize-y min-h-[100px]"><?= e($role['description'] ?? '') ?></textarea>
            </div>
            
            <h4 class="text-sm font-bold text-primary-800 mb-3">Hak Akses (Permissions)</h4>
            <div class="space-y-4 mb-6">
                <?php 
                $grouped = [];
                foreach ($permissions as $p) { $grouped[$p['module_name'] ?? 'Umum'][] = $p; }
                foreach ($grouped as $modName => $perms): ?>
                <div class="border border-primary-100 rounded-2xl p-4">
                    <p class="text-sm font-semibold text-primary-700 mb-2"><?= e($modName) ?></p>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($perms as $p): ?>
                        <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-primary-100 hover:border-primary-300 has-[:checked]:bg-primary-50 has-[:checked]:border-primary-400 cursor-pointer transition-all text-xs">
                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" <?= in_array($p['id'], $rolePermIds) ? 'checked' : '' ?> class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent ">
                            <span class="text-primary-800"><?= e($p['name']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="flex items-center gap-3">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide">Perbarui Peran</button>
                <a href="<?= url('roles') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-full transition-colors text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
