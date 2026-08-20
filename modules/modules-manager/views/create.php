<?php /** Create Module Form */ ?>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8">
        <form action="<?= url('modules-manager/store') ?>" method="POST">
            <?= CSRF::field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Modul <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="<?= old('name') ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label for="slug" class="block text-sm font-semibold text-primary-800 mb-1.5">Slug <span class="text-rose-500">*</span></label>
                    <input type="text" id="slug" name="slug" value="<?= old('slug') ?>" required placeholder="kelola-xxx" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
            </div>
            <div class="mb-5">
                <label for="module_group" class="block text-sm font-semibold text-primary-800 mb-1.5">Grup Modul (Opsional)</label>
                <input list="group-suggestions" type="text" id="module_group" name="module_group" value="<?= old('module_group') ?>" placeholder="Misal: Administrasi Guru" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                <datalist id="group-suggestions">
                    <?php foreach($groups as $g): ?>
                        <option value="<?= e($g['module_group']) ?>">
                    <?php endforeach; ?>
                </datalist>
                <p class="text-xs text-slate-500 mt-1">Ketik manual atau pilih dari daftar rekomendasi. Kosongkan jika tidak butuh grup.</p>
            </div>
            <div class="mb-5">
                <label for="description" class="block text-sm font-semibold text-primary-800 mb-1.5">Deskripsi</label>
                <textarea id="description" name="description" rows="2" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium resize-y min-h-[100px]"><?= old('description') ?></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="route" class="block text-sm font-semibold text-primary-800 mb-1.5">Route <span class="text-rose-500">*</span></label>
                    <input type="text" id="route" name="route" value="<?= old('route') ?>" required placeholder="/kelola-xxx" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label for="color" class="block text-sm font-semibold text-primary-800 mb-1.5">Warna</label>
                    <input type="color" id="color" name="color" value="#0EA5E9" class="w-full h-[42px] px-2 py-1 cursor-pointer rounded-[2px] border-none outline-none focus:outline-none focus:ring-0 bg-slate-100 focus:bg-slate-200">
                </div>
            </div>
            <div class="mb-5">
                <label for="icon_svg" class="block text-sm font-semibold text-primary-800 mb-1.5">Icon SVG</label>
                <textarea id="icon_svg" name="icon_svg" rows="3" placeholder='<svg>...</svg>' class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium resize-y min-h-[100px]"><?= old('icon_svg') ?></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-primary-800 mb-1.5">Urutan</label>
                    <input type="number" id="sort_order" name="sort_order" value="0" min="0" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div class="flex items-end pb-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent ">
                        <span class="text-sm font-medium text-primary-800">Aktifkan modul</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide">Simpan Modul</button>
                <a href="<?= url('modules-manager') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-full transition-colors text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
