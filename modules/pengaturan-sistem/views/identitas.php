<?php
/**
 * Identitas Sekolah View
 */
?>

<div class="max-w-4xl animate-slide-in">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Identitas Aplikasi & Sekolah</h3>
        </div>
        
        <div class="p-6">
            <form action="<?= url('pengaturan-sistem/update') ?>" method="POST" enctype="multipart/form-data">
                <?= CSRF::field() ?>
                
                <div class="mb-6">
                    <label for="app_name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Aplikasi / Sekolah</label>
                    <input type="text" id="app_name" name="app_name" value="<?= e($settings['app_name']) ?>" required
                           class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[5px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                
                <hr class="border-slate-200 mb-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Logo -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Logo Sekolah/Aplikasi</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                <img id="preview-logo" src="<?= $settings['app_logo'] ? url(ltrim($settings['app_logo'], '/')) : '' ?>" 
                                     class="max-w-full max-h-full object-contain <?= empty($settings['app_logo']) ? 'hidden' : '' ?>" alt="Logo">
                                <?= empty($settings['app_logo']) ? '<span class="text-xs text-slate-400" id="text-logo">Kosong</span>' : '' ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="app_logo" accept=".png,.jpg,.jpeg,.svg" onchange="previewImage(this, 'preview-logo'); document.getElementById('text-logo') && document.getElementById('text-logo').classList.add('hidden');"
                                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                <p class="mt-1 text-xs text-slate-500">PNG, JPG, SVG max 2MB.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Favicon -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Favicon Browser</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                <img id="preview-favicon" src="<?= $settings['app_favicon'] ? url(ltrim($settings['app_favicon'], '/')) : '' ?>" 
                                     class="max-w-full max-h-full object-contain <?= empty($settings['app_favicon']) ? 'hidden' : '' ?>" alt="Favicon">
                                <?= empty($settings['app_favicon']) ? '<span class="text-xs text-slate-400" id="text-fav">Kosong</span>' : '' ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="app_favicon" accept=".png,.ico,.svg" onchange="previewImage(this, 'preview-favicon'); document.getElementById('text-fav') && document.getElementById('text-fav').classList.add('hidden');"
                                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                <p class="mt-1 text-xs text-slate-500">ICO, PNG max 512kb (1:1 ratio).</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-[5px] shadow-sm transition-colors text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
