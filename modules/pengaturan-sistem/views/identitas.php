<?php
/**
 * Identitas Sekolah & Pengaturan Unit View
 */
$unitListInfo = [
    'PAUD' => ['label' => 'Unit PAUD / TK / KB', 'badge' => '🧸 PAUD', 'color' => 'amber'],
    'SD'   => ['label' => 'Unit SD (Sekolah Dasar)', 'badge' => '🎒 SD', 'color' => 'emerald'],
    'SMP'  => ['label' => 'Unit SMP (Sekolah Menengah Pertama)', 'badge' => '🎓 SMP', 'color' => 'sky'],
    'SMA'  => ['label' => 'Unit SMA (Sekolah Menengah Atas)', 'badge' => '🏛️ SMA', 'color' => 'indigo'],
];
?>

<div class="max-w-5xl space-y-8 animate-slide-in">

    <form action="<?= url('pengaturan-sistem/update') ?>" method="POST" enctype="multipart/form-data">
        <?= CSRF::field() ?>

        <!-- 1. Identitas Utama Aplikasi / Sistem -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-800">Identitas Utama Aplikasi & Portal</h3>
                    <p class="text-xs text-slate-500">Pengaturan nama portal global, logo utama, dan favicon browser.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-700">Global</span>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <label for="app_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Aplikasi / Portal</label>
                    <input type="text" id="app_name" name="app_name" value="<?= e($settings['app_name']) ?>" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder:text-slate-400 outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500 font-semibold text-sm transition-all">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                    <!-- Global Logo -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Logo Utama Portal</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                <img id="preview-logo" src="<?= !empty($settings['app_logo']) ? url(ltrim($settings['app_logo'], '/')) : '' ?>" 
                                     class="max-w-full max-h-full object-contain <?= empty($settings['app_logo']) ? 'hidden' : '' ?>" alt="Logo">
                                <?= empty($settings['app_logo']) ? '<span class="text-xs text-slate-400 font-bold" id="text-logo">Kosong</span>' : '' ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="app_logo" accept=".png,.jpg,.jpeg,.svg" onchange="previewImage(this, 'preview-logo'); document.getElementById('text-logo') && document.getElementById('text-logo').classList.add('hidden');"
                                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                <p class="mt-1 text-[11px] text-slate-400">PNG, JPG, SVG max 2MB.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Favicon -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Favicon Browser</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                <img id="preview-favicon" src="<?= !empty($settings['app_favicon']) ? url(ltrim($settings['app_favicon'], '/')) : '' ?>" 
                                     class="max-w-full max-h-full object-contain <?= empty($settings['app_favicon']) ? 'hidden' : '' ?>" alt="Favicon">
                                <?= empty($settings['app_favicon']) ? '<span class="text-xs text-slate-400 font-bold" id="text-fav">Kosong</span>' : '' ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="app_favicon" accept=".png,.ico,.svg" onchange="previewImage(this, 'preview-favicon'); document.getElementById('text-fav') && document.getElementById('text-fav').classList.add('hidden');"
                                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                <p class="mt-1 text-[11px] text-slate-400">ICO, PNG max 512kb (1:1 ratio).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Pengaturan Identitas Lembaga & Kepala Sekolah Per Unit -->
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Identitas Lembaga & Kepala Sekolah Per Unit</h2>
                    <p class="text-xs text-slate-500">
                        Logo resmi, nama sekolah, dan kepala sekolah di bawah ini akan otomatis muncul pada seluruh lembar cetak dokumen (HES, HEB, Prosem, Prota, RPP, dll) sesuai unit yang dipilih.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($unitListInfo as $uKey => $uMeta): ?>
                    <?php
                    $namaSekolahKey = "nama_sekolah_{$uKey}";
                    $logoKey = "logo_unit_{$uKey}";
                    $ksKey = "kepala_sekolah_{$uKey}";
                    $nipKey = "nip_kepala_sekolah_{$uKey}";
                    
                    $namaVal = $settings[$namaSekolahKey] ?? '';
                    $logoVal = $settings[$logoKey] ?? '';
                    $ksVal = $settings[$ksKey] ?? '';
                    $nipVal = $settings[$nipKey] ?? '';
                    ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 overflow-hidden flex flex-col justify-between">
                        
                        <!-- Unit Header -->
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="text-lg"><?= explode(' ', $uMeta['badge'])[0] ?></span>
                                <h3 class="text-sm font-extrabold text-slate-900"><?= $uMeta['label'] ?></h3>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-200/80 text-slate-800">
                                Unit <?= $uKey ?>
                            </span>
                        </div>

                        <!-- Unit Form Fields -->
                        <div class="p-6 space-y-4 text-xs">
                            <!-- Nama Lembaga / Sekolah -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    Nama Resmi Sekolah / Lembaga
                                </label>
                                <input type="text" name="<?= $namaSekolahKey ?>" value="<?= e($namaVal) ?>" required
                                       placeholder="Contoh: <?= ($uKey === 'SD') ? 'SD ISLAM TERPADU BINA INSAN PALU' : "{$uKey} IT BINA INSAN PALU" ?>"
                                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                                <p class="text-[10px] text-slate-400 mt-1">Muncul di kop / header dokumen resmi unit ini.</p>
                            </div>

                            <!-- Logo Unit -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Logo Resmi Unit <?= $uKey ?></label>
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                        <img id="preview-logo-<?= $uKey ?>" src="<?= !empty($logoVal) ? url(ltrim($logoVal, '/')) : '' ?>" 
                                             class="max-w-full max-h-full object-contain <?= empty($logoVal) ? 'hidden' : '' ?>" alt="Logo <?= $uKey ?>">
                                        <?= empty($logoVal) ? '<span class="text-[10px] text-slate-400 font-bold" id="text-logo-' . $uKey . '">BIP</span>' : '' ?>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="<?= $logoKey ?>" accept=".png,.jpg,.jpeg,.svg" 
                                               onchange="previewImage(this, 'preview-logo-<?= $uKey ?>'); document.getElementById('text-logo-<?= $uKey ?>') && document.getElementById('text-logo-<?= $uKey ?>').classList.add('hidden');"
                                               class="w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                                        <p class="mt-0.5 text-[10px] text-slate-400">PNG/JPG transparan (Ukuran kotak 1:1 disarankan).</p>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-100 my-2">

                            <!-- Nama Kepala Sekolah -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    Nama Kepala Sekolah (+ Gelar)
                                </label>
                                <input type="text" name="<?= $ksKey ?>" value="<?= e($ksVal) ?>" required
                                       placeholder="Contoh: FENI, S.Pd.I"
                                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                                <p class="text-[10px] text-slate-400 mt-1">Muncul di kolom tanda tangan 'Mengetahui Kepala Sekolah'.</p>
                            </div>

                            <!-- NIP / NUPTK Kepala Sekolah -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    NIP / NUPTK / NIY (Opsional)
                                </label>
                                <input type="text" name="<?= $nipKey ?>" value="<?= e($nipVal) ?>"
                                       placeholder="Contoh: 19850101 201001 1 001 atau -"
                                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Submit Button Bar -->
        <div class="sticky bottom-4 z-40 bg-white/90 backdrop-blur-md p-4 rounded-2xl border border-slate-200 shadow-xl flex items-center justify-between mt-8">
            <span class="text-xs font-semibold text-slate-500">
                Pastikan seluruh data unit dan nama kepala sekolah sudah benar sebelum menyimpan.
            </span>
            <button type="submit" class="px-7 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-black rounded-xl shadow-md shadow-emerald-600/20 transition-all text-xs sm:text-sm cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span>Simpan Seluruh Pengaturan</span>
            </button>
        </div>
    </form>

</div>
