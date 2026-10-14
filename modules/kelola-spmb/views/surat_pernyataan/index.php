<?php
/**
 * View Pengaturan Surat Pernyataan Komitmen Orang Tua per Unit
 * Portal BIP
 */
$unit = $unit ?? 'SD';
$activeTemplate = $activeTemplate ?? [];
$allTemplates = $allTemplates ?? [];
$globalKop = $globalKop ?? '';
$kopGambar = !empty($activeTemplate['kop_gambar']) ? $activeTemplate['kop_gambar'] : $globalKop;
$isCustomKop = !empty($activeTemplate['kop_gambar']);

$unitMeta = [
    'PAUD' => ['icon' => '🧸', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'color' => 'emerald'],
    'SD'   => ['icon' => '🎒', 'badge' => 'bg-sky-100 text-sky-800 border-sky-200', 'color' => 'sky'],
    'SMP'  => ['icon' => '📘', 'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200', 'color' => 'indigo'],
    'SMA'  => ['icon' => '🎓', 'badge' => 'bg-purple-100 text-purple-800 border-purple-200', 'color' => 'purple'],
];
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:text-sky-600">SPMB</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Surat Pernyataan Komitmen</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>📝</span>
                <span>Pengaturan Surat Pernyataan Komitmen Unit</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl">
                Atur format surat komitmen orang tua/wali murid per unit (PAUD, SD, SMP, SMA). Dokumen ini wajib ditandatangani secara digital oleh orang tua setelah pengisian formulir online, dengan tanda tangan digital terenkripsi (AES-256).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openPreviewModal()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors shadow-xs">
                <span>👁️</span>
                <span>Preview Dokumen Cetak</span>
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- ── Unit Switcher Tabs ── -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-xs flex items-center gap-2 overflow-x-auto">
        <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $uKey): 
            $isActive = ($unit === $uKey);
            $uCfg = $unitMeta[$uKey];
        ?>
            <a href="<?= url('kelola-spmb/surat-pernyataan?unit=' . $uKey) ?>" 
               class="flex-1 min-w-[130px] flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl text-xs font-bold transition-all text-center <?= $isActive ? 'bg-gradient-to-r from-sky-600 to-blue-600 text-white shadow-md shadow-sky-600/25' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                <span class="text-base"><?= $uCfg['icon'] ?></span>
                <span>Unit <?= $uKey ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ── 1. Card Pengaturan Kop Surat (Gambar Panjang / Banner Kop) ── -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shrink-0">
                    🖼️
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Kop Surat Gambar Banner (Unit <?= $unit ?>)</h3>
                    <p class="text-xs text-slate-500">
                        Unggah gambar panjang/banner kop surat resmi untuk <strong>Unit <?= $unit ?></strong>. Kop ini akan otomatis diletakkan di bagian paling atas surat pernyataan.
                    </p>
                </div>
            </div>
            <div>
                <?php if ($isCustomKop): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Kop Khusus Unit <?= $unit ?> Aktif
                    </span>
                <?php elseif (!empty($globalKop)): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800 border border-sky-200">
                        <span>🌐</span>
                        Menggunakan Kop Global SPMB
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                        Belum Ada Kop Gambar
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Preview Kop -->
            <div class="lg:col-span-7 space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Preview Banner Kop Surat Unit <?= $unit ?>
                </label>

                <?php if (!empty($kopGambar)): ?>
                    <div class="p-4 bg-slate-50 rounded-2xl border-2 border-slate-200 text-center relative group">
                        <img src="<?= url(ltrim($kopGambar, '/')) ?>" 
                             alt="Kop Surat Unit <?= $unit ?>" 
                             class="w-full max-h-36 object-contain mx-auto rounded-lg shadow-xs bg-white p-2">
                        <div class="mt-3 flex items-center justify-center gap-3 text-xs">
                            <a href="<?= url(ltrim($kopGambar, '/')) ?>" target="_blank" class="text-sky-600 hover:text-sky-700 font-bold underline">
                                🔍 Lihat Ukuran Asli
                            </a>
                            <?php if ($isCustomKop): ?>
                                <span class="text-slate-300">&bull;</span>
                                <form action="<?= url('kelola-spmb/surat-pernyataan/hapus-kop') ?>" method="POST" onsubmit="return confirm('Hapus gambar kop khusus unit <?= $unit ?> dan gunakan kop global?');" class="inline">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="jenjang" value="<?= $unit ?>">
                                    <button type="submit" class="text-rose-600 hover:text-rose-700 font-bold underline">
                                        🗑️ Hapus Kop Unit
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-8 bg-slate-50/80 rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-2">
                        <div class="text-3xl text-slate-300">🖼️</div>
                        <p class="text-xs font-bold text-slate-700">Belum Ada Banner Kop untuk Unit <?= $unit ?></p>
                        <p class="text-[11px] text-slate-500 max-w-md mx-auto">
                            Unggah gambar banner kop memanjang (horizontal) di samping untuk melengkapi kop resmi unit <?= $unit ?>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Kolom Form Upload Kop -->
            <div class="lg:col-span-5 bg-slate-50/70 p-5 rounded-2xl border border-slate-200/80 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center justify-between">
                    <span>Upload Banner Kop Unit <?= $unit ?></span>
                    <span class="text-[10px] text-slate-400 font-normal">PNG / JPG / WEBP</span>
                </h4>

                <form action="<?= url('kelola-spmb/surat-pernyataan/upload-kop') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="jenjang" value="<?= $unit ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pilih File Gambar Banner Kop (Panjang Horizontal)
                        </label>
                        <input type="file" 
                               name="kop_gambar" 
                               accept="image/png, image/jpeg, image/jpg, image/webp" 
                               required
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer border border-slate-200 bg-white rounded-xl p-1.5">
                    </div>

                    <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200 text-[11px] text-amber-800 space-y-1">
                        <div class="font-bold flex items-center gap-1 text-amber-900">
                            <span>💡</span> Rekomendasi Format Banner:
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[10.5px] text-amber-700">
                            <li>Format: <strong>PNG transparan</strong> atau <strong>JPG/WEBP</strong>.</li>
                            <li>Bentuk: <strong>Horizontal / Memanjang</strong> (~ rasio 5:1 atau 6:1).</li>
                            <li>Resolusi: <strong>800px – 1400px</strong> lebar.</li>
                        </ul>
                    </div>

                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition-all text-center">
                        💾 Unggah Banner Kop Unit <?= $unit ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── 2. Word-like Editor Surat Pernyataan Komitmen ── -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <span>📄</span>
                    <span>Editor Isian Dokumen (Unit <?= $unit ?>)</span>
                </h3>
                <p class="text-xs text-slate-500">
                    Format dokumen mirip Microsoft Word. Klik tombol placeholder di bawah untuk menyisipkan data calon siswa otomatis.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <form action="<?= url('kelola-spmb/surat-pernyataan/reset') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset template unit <?= $unit ?> ke standar sistem? Seluruh perubahan teks akan kembali ke bawaan.');" class="inline">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="jenjang" value="<?= $unit ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs transition-colors">
                        🔄 Reset ke Bawaan
                    </button>
                </form>
            </div>
        </div>

        <form id="formSuratTemplate" action="<?= url('kelola-spmb/surat-pernyataan/update') ?>" method="POST" class="space-y-6">
            <?= CSRF::field() ?>
            <input type="hidden" name="jenjang" value="<?= $unit ?>">
            <textarea name="isi_template" id="hiddenIsiTemplate" class="hidden"></textarea>

            <!-- Metadata Header Dokumen -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Judul Utama Surat
                    </label>
                    <input type="text" 
                           name="judul_surat" 
                           value="<?= e($activeTemplate['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID') ?>" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Satuan Pendidikan / Sub-Judul
                    </label>
                    <input type="text" 
                           name="nama_sekolah" 
                           value="<?= e($activeTemplate['nama_sekolah'] ?? ($unit . ' Islam Terpadu Bina Insan Palu')) ?>" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <!-- Placeholder Toolbar (Variabel Word) -->
            <div class="p-4 bg-slate-50/90 rounded-2xl border border-slate-200/80 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span>🏷️</span>
                        <span>Klik Tag Variabel Otomatis (Akan Disisipkan di Posisi Kursor):</span>
                    </span>
                    <span class="text-[11px] text-slate-400">Variabel akan otomatis terisi saat orang tua membuka link</span>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <?php
                    $tags = [
                        '{nama_anak}'         => 'Nama Siswa',
                        '{nama_ayah}'         => 'Nama Ayah',
                        '{nama_ibu}'          => 'Nama Ibu',
                        '{nama_wali}'         => 'Nama Wali',
                        '{nisn}'              => 'NISN',
                        '{nik}'               => 'NIK',
                        '{unit}'              => 'Unit (Jenjang)',
                        '{nama_sekolah}'      => 'Nama Sekolah',
                        '{nomor_pendaftaran}' => 'No. Pendaftaran',
                        '{tanggal_daftar}'    => 'Tgl Daftar',
                        '{tanggal_hari_ini}'  => 'Tgl Hari Ini',
                        '{alamat}'            => 'Alamat',
                        '{asal_sekolah}'      => 'Asal Sekolah',
                        '{tahun_ajaran}'      => 'Tahun Ajaran'
                    ];
                    foreach ($tags as $tagKey => $tagDesc):
                    ?>
                        <button type="button" 
                                onmousedown="event.preventDefault()"
                                onclick="insertPlaceholder('<?= $tagKey ?>')"
                                title="Klik untuk menyisipkan <?= $tagKey ?>"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-sky-300 hover:bg-sky-50 text-[11px] font-mono text-sky-800 font-semibold shadow-xs transition-all active:scale-95">
                            <span class="text-sky-600 font-bold">+</span>
                            <span><?= $tagKey ?></span>
                            <span class="text-[10px] text-slate-400 font-sans font-normal">(<?= $tagDesc ?>)</span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Word-Like Visual Toolbar -->
            <div class="flex flex-wrap items-center gap-1.5 p-2.5 bg-slate-100 rounded-xl border border-slate-200/90 text-xs shadow-inner">
                <!-- Text Styling Group -->
                <div class="flex items-center bg-white rounded-lg border border-slate-200 p-0.5 shadow-2xs">
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('bold')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-800 font-bold transition-colors" title="Tebal (Ctrl+B)">
                        <span class="text-sm font-black">B</span>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('italic')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-800 italic transition-colors" title="Miring (Ctrl+I)">
                        <span class="text-sm font-serif font-bold">I</span>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('underline')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-800 underline transition-colors" title="Garis Bawah (Ctrl+U)">
                        <span class="text-sm font-bold">U</span>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('strikeThrough')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-800 line-through transition-colors" title="Coret (Strikethrough)">
                        <span class="text-xs font-semibold">S</span>
                    </button>
                </div>

                <!-- Alignment Group -->
                <div class="flex items-center bg-white rounded-lg border border-slate-200 p-0.5 shadow-2xs">
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('justifyLeft')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-700 transition-colors" title="Rata Kiri">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h10.5m-10.5 5.25h16.5"/></svg>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('justifyCenter')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-700 transition-colors" title="Rata Tengah">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M6.75 12h10.5M3.75 17.25h16.5"/></svg>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('justifyRight')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-700 transition-colors" title="Rata Kanan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M9.75 12h10.5M3.75 17.25h16.5"/></svg>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('justifyFull')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-700 transition-colors" title="Rata Kanan-Kiri (Justify)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                    </button>
                </div>

                <!-- List & Indentation Group -->
                <div class="flex items-center bg-white rounded-lg border border-slate-200 p-0.5 shadow-2xs">
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('insertOrderedList')" 
                            class="px-2.5 h-8 flex items-center gap-1 rounded hover:bg-slate-100 text-slate-700 font-semibold text-[11px] transition-colors" title="Penomoran (1, 2, 3)">
                        <span>1. 2. 3.</span>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('insertUnorderedList')" 
                            class="px-2.5 h-8 flex items-center gap-1 rounded hover:bg-slate-100 text-slate-700 font-semibold text-[11px] transition-colors" title="Daftar Poin (Bullet List)">
                        <span>&bull; List</span>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('indent')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-700 transition-colors" title="Tambah Indent">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5M9 9l3 3-3 3"/></svg>
                    </button>
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('outdent')" 
                            class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-100 text-slate-700 transition-colors" title="Kurangi Indent">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5M12 9l-3 3 3 3"/></svg>
                    </button>
                </div>

                <!-- Utilities Group -->
                <div class="flex items-center gap-1.5 ml-auto">
                    <button type="button" onmousedown="event.preventDefault()" onclick="formatDoc('removeFormat')" 
                            class="px-3 h-8 flex items-center gap-1.5 rounded-lg bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 font-semibold text-[11px] shadow-2xs transition-colors" title="Hapus Pemformatan Teks">
                        <span>🧹</span>
                        <span>Bersihkan Format</span>
                    </button>
                </div>
            </div>

            <!-- Microsoft Word Sheet Container -->
            <div class="bg-slate-200/70 p-4 sm:p-8 rounded-2xl flex justify-center overflow-x-auto">
                <div class="bg-white w-full max-w-4xl min-h-[750px] shadow-2xl rounded-sm p-8 sm:p-14 border border-slate-300/80 text-slate-800 text-sm leading-relaxed" style="font-family: 'Times New Roman', Times, serif;">
                    <!-- Visual Mockup Kop Surat -->
                    <?php if (!empty($kopGambar)): ?>
                        <div class="mb-6 pb-4 border-b-2 border-slate-900 text-center">
                            <img src="<?= url(ltrim($kopGambar, '/')) ?>" alt="Kop Surat" class="max-h-28 mx-auto object-contain">
                        </div>
                    <?php else: ?>
                        <div class="mb-6 pb-4 border-b-2 border-slate-900 text-center space-y-1">
                            <div class="text-sm font-bold tracking-wider">YAYASAN BINA INSAN PALU</div>
                            <div class="text-lg font-extrabold uppercase"><?= e($activeTemplate['nama_sekolah'] ?? ($unit . ' ISLAM TERPADU BINA INSAN PALU')) ?></div>
                            <div class="text-[11px] text-slate-500 font-sans">Jl. Agatis No. 1 Kel. Nunu Kec. Tatanga Palu 94222 Sulawesi Tengah &bull; Email: sditbinainsanpalu2019@gmail.com</div>
                        </div>
                    <?php endif; ?>

                    <!-- Visual Header Judul -->
                    <div class="text-center mb-6 space-y-1">
                        <h2 class="text-base sm:text-lg font-bold uppercase tracking-tight" id="previewJudul">
                            <?= e($activeTemplate['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID') ?>
                        </h2>
                        <h3 class="text-sm sm:text-base font-bold uppercase text-slate-800" id="previewNamaSekolah">
                            <?= e($activeTemplate['nama_sekolah'] ?? ($unit . ' ISLAM TERPADU BINA INSAN PALU')) ?>
                        </h3>
                    </div>

                    <!-- Editable Content Area -->
                    <div id="wordEditor" 
                         contenteditable="true" 
                         class="outline-none focus:ring-1 focus:ring-sky-400 p-2 min-h-[500px] leading-relaxed text-justify space-y-4">
                        <?= $activeTemplate['isi_template'] ?? '' ?>
                    </div>

                    <!-- Area Tanda Tangan Simulasi -->
                    <div class="mt-12 pt-6 border-t border-dashed border-slate-300 text-xs text-slate-600 font-sans">
                        <div class="text-right mb-6">
                            <span>Palu, </span><strong>{tanggal_hari_ini}</strong><br>
                            <span class="font-semibold text-slate-700">Orang Tua/Wali Murid</span>
                        </div>
                        <div class="grid grid-cols-2 gap-8 text-center">
                            <div>
                                <div class="h-20 flex items-center justify-center border border-dashed border-slate-300 rounded-lg text-slate-400 text-[11px]">
                                    (Area Tanda Tangan Digital Ayah)
                                </div>
                                <div class="mt-2 font-bold text-slate-800">{nama_ayah}</div>
                                <div class="text-[11px] text-slate-500">Ayah</div>
                            </div>
                            <div>
                                <div class="h-20 flex items-center justify-center border border-dashed border-slate-300 rounded-lg text-slate-400 text-[11px]">
                                    (Area Tanda Tangan Digital Ibu)
                                </div>
                                <div class="mt-2 font-bold text-slate-800">{nama_ibu}</div>
                                <div class="text-[11px] text-slate-500">Ibu</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan Bawah -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Kaki / Keterangan di Bawah Dokumen
                </label>
                <input type="text" 
                       name="catatan_bawah" 
                       value="<?= e($activeTemplate['catatan_bawah'] ?? 'Catatan: Dokumen ini disimpan di arsip sekolah sebagai bukti komitmen resmi orang tua/wali murid.') ?>" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <!-- Submit Action Bar -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Perubahan langsung berlaku untuk seluruh pendaftar calon siswa unit <strong><?= $unit ?></strong>.
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openPreviewModal()" 
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                        👁️ Preview Simulasi
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all transform hover:-translate-y-0.5">
                        💾 Simpan Template Unit <?= $unit ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal Preview Simulasi Dokumen Cetak ── -->
<div id="modalPreviewSimulasi" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden my-auto">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                    <span>👁️</span>
                    <span>Preview Simulasi Surat Komitmen Unit <?= $unit ?></span>
                </h3>
                <p class="text-xs text-slate-500">Tampilan ini persis seperti yang akan dilihat dan ditandatangani oleh orang tua siswa.</p>
            </div>
            <button type="button" onclick="closePreviewModal()" class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-700 flex items-center justify-center font-bold text-sm">
                &times;
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 bg-slate-100 flex justify-center">
            <div class="bg-white w-full max-w-3xl p-8 sm:p-12 shadow-lg text-slate-900 text-xs sm:text-sm leading-relaxed" style="font-family: 'Times New Roman', Times, serif;">
                <?php if (!empty($kopGambar)): ?>
                    <div class="mb-6 pb-4 border-b-2 border-slate-900 text-center">
                        <img src="<?= url(ltrim($kopGambar, '/')) ?>" alt="Kop Surat" class="max-h-24 mx-auto object-contain">
                    </div>
                <?php endif; ?>
                <div class="text-center mb-6 space-y-1">
                    <h2 class="text-base font-bold uppercase tracking-tight" id="modalPreviewJudul">
                        <?= e($activeTemplate['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID') ?>
                    </h2>
                    <h3 class="text-sm font-bold uppercase text-slate-800" id="modalPreviewNamaSekolah">
                        <?= e($activeTemplate['nama_sekolah'] ?? ($unit . ' ISLAM TERPADU BINA INSAN PALU')) ?>
                    </h3>
                </div>
                <div id="modalPreviewContent" class="text-justify space-y-3 leading-relaxed">
                    <!-- Injected via JS -->
                </div>
                <div class="mt-10 pt-4 border-t border-dashed border-slate-300 font-sans text-xs">
                    <div class="text-right mb-6">
                        Palu, <strong><?= SpmbModel::formatTglIndo(date('Y-m-d')) ?></strong><br>
                        <span class="font-semibold">Orang Tua/Wali Murid</span>
                    </div>
                    <div class="grid grid-cols-2 gap-8 text-center">
                        <div>
                            <div class="h-16 flex items-center justify-center border border-dashed border-emerald-400 bg-emerald-50 text-emerald-700 font-semibold rounded-lg text-[11px]">
                                ✓ Tanda Tangan Digital Ayah
                            </div>
                            <div class="mt-2 font-bold">Ahmad Fauzi, S.Pd.</div>
                            <div class="text-[11px] text-slate-500">Ayah</div>
                        </div>
                        <div>
                            <div class="h-16 flex items-center justify-center border border-dashed border-emerald-400 bg-emerald-50 text-emerald-700 font-semibold rounded-lg text-[11px]">
                                ✓ Tanda Tangan Digital Ibu
                            </div>
                            <div class="mt-2 font-bold">Nur Aini, S.Si.</div>
                            <div class="text-[11px] text-slate-500">Ibu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-4 bg-white border-t border-slate-100 flex items-center justify-end">
            <button type="button" onclick="closePreviewModal()" class="px-5 py-2 rounded-xl bg-slate-800 text-white font-bold text-xs hover:bg-slate-700">
                Tutup Preview
            </button>
        </div>
    </div>
</div>

<script>
// Selection memory for wordEditor
let savedRange = null;

function saveEditorSelection() {
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
        const range = sel.getRangeAt(0);
        const editor = document.getElementById('wordEditor');
        if (editor && editor.contains(range.commonAncestorContainer)) {
            savedRange = range.cloneRange();
        }
    }
}

function restoreEditorSelection() {
    const editor = document.getElementById('wordEditor');
    editor.focus();
    if (savedRange) {
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
    }
}

document.addEventListener('selectionchange', saveEditorSelection);
const editorArea = document.getElementById('wordEditor');
if (editorArea) {
    editorArea.addEventListener('keyup', saveEditorSelection);
    editorArea.addEventListener('mouseup', saveEditorSelection);
}

// Document format handler
function formatDoc(cmd, val = null) {
    restoreEditorSelection();
    document.execCommand(cmd, false, val);
    saveEditorSelection();
}

// Insert placeholder at cursor position
function insertPlaceholder(tag) {
    restoreEditorSelection();
    document.execCommand('insertHTML', false, '<strong>' + tag + '</strong>&nbsp;');
    saveEditorSelection();
}

// Sync content to hidden textarea before form submit
document.getElementById('formSuratTemplate').addEventListener('submit', function() {
    const editor = document.getElementById('wordEditor');
    document.getElementById('hiddenIsiTemplate').value = editor.innerHTML;
});

// Dynamic heading update
document.querySelector('input[name="judul_surat"]').addEventListener('input', function(e) {
    document.getElementById('previewJudul').textContent = e.target.value;
    document.getElementById('modalPreviewJudul').textContent = e.target.value;
});
document.querySelector('input[name="nama_sekolah"]').addEventListener('input', function(e) {
    document.getElementById('previewNamaSekolah').textContent = e.target.value;
    document.getElementById('modalPreviewNamaSekolah').textContent = e.target.value;
});

// Modal Preview
function openPreviewModal() {
    const editorHtml = document.getElementById('wordEditor').innerHTML;
    // Replace dummy simulation data
    const dummyReplacements = {
        '{nama_ayah}': 'Ahmad Fauzi, S.Pd.',
        '{nama_ibu}': 'Nur Aini, S.Si.',
        '{nama_wali}': '-',
        '{nama_anak}': 'Muhammad Al-Fatih',
        '{nama_siswa}': 'Muhammad Al-Fatih',
        '{nama_lengkap}': 'Muhammad Al-Fatih',
        '{nisn}': '0123456789',
        '{nik}': '7271012345670001',
        '{unit}': '<?= $unit ?>',
        '{jenjang}': '<?= $unit ?>',
        '{nama_sekolah}': document.querySelector('input[name="nama_sekolah"]').value,
        '{nomor_pendaftaran}': '<?= $unit ?>-001',
        '{tanggal_daftar}': '<?= SpmbModel::formatTglIndo(date('Y-m-d')) ?>',
        '{tanggal_hari_ini}': '<?= SpmbModel::formatTglIndo(date('Y-m-d')) ?>',
        '{tanggal}': '<?= SpmbModel::formatTglIndo(date('Y-m-d')) ?>',
        '{alamat}': 'Jl. Kemiri No. 12, Kel. Kamonji, Palu Barat',
        '{asal_sekolah}': 'TK Bina Insan',
        '{tahun_ajaran}': '2026/2027'
    };

    let simulated = editorHtml;
    for (const [k, v] of Object.entries(dummyReplacements)) {
        simulated = simulated.split(k).join('<strong>' + v + '</strong>');
    }

    document.getElementById('modalPreviewContent').innerHTML = simulated;
    document.getElementById('modalPreviewSimulasi').classList.remove('hidden');
    document.getElementById('modalPreviewSimulasi').classList.add('flex');
}

function closePreviewModal() {
    document.getElementById('modalPreviewSimulasi').classList.add('hidden');
    document.getElementById('modalPreviewSimulasi').classList.remove('flex');
}
</script>
