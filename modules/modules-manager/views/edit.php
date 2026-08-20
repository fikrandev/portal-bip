<?php /** Edit Module Form */ ?>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8">
        <form action="<?= url('modules-manager/update/' . $module['id']) ?>" method="POST">
            <?= CSRF::field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Modul <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="<?= e($module['name']) ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Slug</label>
                    <input type="text" value="<?= e($module['slug']) ?>" disabled class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
            </div>
            <div class="mb-5">
                <label for="module_group" class="block text-sm font-semibold text-primary-800 mb-1.5">Grup Modul (Opsional)</label>
                <input list="group-suggestions" type="text" id="module_group" name="module_group" value="<?= e($module['module_group'] ?? '') ?>" placeholder="Misal: Administrasi Guru" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                <datalist id="group-suggestions">
                    <?php foreach($groups as $g): ?>
                        <option value="<?= e($g['module_group']) ?>">
                    <?php endforeach; ?>
                </datalist>
                <p class="text-xs text-slate-500 mt-1">Ketik manual atau pilih dari daftar rekomendasi. Kosongkan jika tidak butuh grup.</p>
            </div>
            <div class="mb-5">
                <label for="description" class="block text-sm font-semibold text-primary-800 mb-1.5">Deskripsi</label>
                <textarea id="description" name="description" rows="2" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium resize-y min-h-[100px]"><?= e($module['description'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="route" class="block text-sm font-semibold text-primary-800 mb-1.5">Route <span class="text-rose-500">*</span></label>
                    <input type="text" id="route" name="route" value="<?= e($module['route']) ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label for="color" class="block text-sm font-semibold text-primary-800 mb-1.5">Warna</label>
                    <input type="color" id="color" name="color" value="<?= e($module['color']) ?>" class="w-full h-[42px] px-2 py-1 rounded-full border border-primary-200 bg-primary-50/30 cursor-pointer">
                </div>
            </div>
            <div class="mb-5">
                <label for="icon_svg" class="block text-sm font-semibold text-primary-800 mb-1.5">Icon SVG</label>
                <textarea id="icon_svg" name="icon_svg" rows="3" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium resize-y min-h-[100px]"><?= e($module['icon_svg'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-primary-800 mb-1.5">Urutan</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= $module['sort_order'] ?>" min="0" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div class="flex items-end pb-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?= $module['is_active'] ? 'checked' : '' ?> class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent ">
                        <span class="text-sm font-medium text-primary-800">Aktifkan modul</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide">Perbarui Modul</button>
                <a href="<?= url('modules-manager') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-full transition-colors text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
