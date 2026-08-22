<?php
/**
 * Portal BIP - Reusable Modal Helper
 * 
 * Generates standardized, accessible, and responsive modals (Import, Dialog, Form)
 * with consistent styling across all modules.
 */

class ModalHelper
{
    /**
     * Generate standard Import Excel/CSV Modal
     */
    public static function importModal(array $options): string
    {
        $id = $options['id'] ?? 'modal-import-' . uniqid();
        $title = $options['title'] ?? 'Import Data Excel / CSV';
        $subtitle = $options['subtitle'] ?? 'Unggah file spreadsheet untuk memasukkan data secara massal.';
        $action = $options['action'] ?? '#';
        $templateUrl = $options['templateUrl'] ?? null;
        $templateName = $options['templateName'] ?? 'Download Template Spreadsheet';
        $instructions = $options['instructions'] ?? [
            'Download file template yang telah disiapkan.',
            'Isi data sesuai kolom dan format yang ditentukan tanpa mengubah urutan header.',
            'Simpan dan unggah kembali file ke form di bawah ini, lalu klik tombol Import.'
        ];
        $acceptedFormats = $options['acceptedFormats'] ?? '.csv, .xls, .xlsx';
        $maxFileSizeMb = $options['maxFileSizeMb'] ?? 5;

        ob_start();
        ?>
        <!-- Reusable Modal: <?= htmlspecialchars($id) ?> -->
        <div id="<?= htmlspecialchars($id) ?>" class="portal-modal fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
            <div class="portal-modal-content bg-white rounded-3xl shadow-2xl border border-primary-100 max-w-lg w-full overflow-hidden transform scale-95 transition-all duration-300">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 bg-gradient-to-r from-primary-50 to-primary-100/50 border-b border-primary-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary-600 text-white flex items-center justify-center shadow-md shadow-primary-600/20">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-primary-950"><?= htmlspecialchars($title) ?></h3>
                            <p class="text-xs text-primary-600 font-medium"><?= htmlspecialchars($subtitle) ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="ModalHelper.close('<?= htmlspecialchars($id) ?>')" class="w-8 h-8 rounded-full bg-white/80 hover:bg-white text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form action="<?= htmlspecialchars($action) ?>" method="POST" enctype="multipart/form-data" class="p-6" onsubmit="ModalHelper.onSubmit(this, event)">
                    <?= CSRF::field() ?>

                    <?php if ($templateUrl): ?>
                    <!-- Step 1: Download Template Card -->
                    <div class="mb-5 p-4 rounded-2xl bg-amber-50/80 border border-amber-200/70 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📋</span>
                            <div>
                                <h4 class="text-xs font-bold text-amber-950">Langkah 1: Gunakan Template Resmi</h4>
                                <p class="text-[11px] text-amber-700">Gunakan format kolom yang sesuai agar data tidak ditolak sistem.</p>
                            </div>
                        </div>
                        <a href="<?= htmlspecialchars($templateUrl) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Step 2: Instructions -->
                    <?php if (!empty($instructions)): ?>
                    <div class="mb-4 text-xs text-slate-600 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                        <p class="font-bold text-slate-800 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                            Panduan Pengisian:
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-slate-600 pl-1 text-[11px] leading-relaxed">
                            <?php foreach ($instructions as $ins): ?>
                                <li><?= htmlspecialchars($ins) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Step 3: Drag & Drop File Input -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-700 mb-2">Langkah 2: Unggah File Spreadsheet</label>
                        <div class="portal-dropzone relative border-2 border-dashed border-primary-200 hover:border-primary-400 bg-primary-50/20 hover:bg-primary-50/40 rounded-2xl p-6 text-center transition-all cursor-pointer" onclick="this.querySelector('input[type=file]').click()">
                            <input type="file" name="file_import" accept="<?= htmlspecialchars($acceptedFormats) ?>" required class="hidden" onchange="ModalHelper.onFileSelected(this)">
                            
                            <div class="dropzone-default space-y-2">
                                <div class="w-12 h-12 mx-auto rounded-full bg-primary-100 text-primary-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-800">Klik untuk pilih file atau seret file ke sini</p>
                                <p class="text-[11px] text-slate-500">Mendukung file: <span class="font-mono text-primary-700 font-semibold"><?= htmlspecialchars($acceptedFormats) ?></span> (Maks. <?= $maxFileSizeMb ?>MB)</p>
                            </div>

                            <div class="dropzone-file-preview hidden flex items-center justify-between p-3 bg-white rounded-xl border border-primary-200 shadow-sm mt-2">
                                <div class="flex items-center gap-3 text-left">
                                    <span class="text-xl text-emerald-600">📄</span>
                                    <div class="overflow-hidden">
                                        <p class="text-xs font-bold text-slate-800 truncate filename-label">file.csv</p>
                                        <p class="text-[10px] text-slate-400 filesize-label">0 KB</p>
                                    </div>
                                </div>
                                <span class="text-xs text-primary-600 font-semibold hover:underline">Ganti File</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" onclick="ModalHelper.close('<?= htmlspecialchars($id) ?>')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="btn-submit-import inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/25 transition-all">
                            <span>Mulai Import Data</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
