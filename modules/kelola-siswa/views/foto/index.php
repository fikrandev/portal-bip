<?php
/**
 * View Galeri Foto Siswa
 */
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 text-lg">
                    🖼️
                </div>
                <span>Galeri Foto Siswa</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola dan lihat foto profil siswa.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Cetak Massal -->
            <?php
                // Build the URL for mass print with current filter parameters
                $massalParams = array_filter([
                    'jenjang' => $_GET['jenjang'] ?? '',
                    'kelas' => $_GET['kelas'] ?? '',
                    'search' => $_GET['search'] ?? ''
                ]);
                $massalUrl = url('kelola-siswa/cetak-kartu-massal') . (!empty($massalParams) ? '?' . http_build_query($massalParams) : '');
            ?>
            <a href="<?= $massalUrl ?>" target="_blank" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" /></svg>
                <span>Cetak Filtered</span>
            </a>

            <!-- Pengaturan Template Kartu -->
            <button type="button" onclick="document.getElementById('modal-upload-template').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" /></svg>
                <span>Template Kartu</span>
            </button>
            
            <!-- Upload Foto ZIP -->
            <button type="button" onclick="document.getElementById('modal-upload-zip').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                <span>Upload Foto Massal (ZIP)</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="<?= url('kelola-siswa/foto') ?>" class="flex flex-col sm:flex-row items-end gap-4">
            
            <div class="w-full sm:w-1/4">
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Jenjang</label>
                <select name="jenjang" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Jenjang</option>
                    <option value="PAUD" <?= $filterJenjang === 'PAUD' ? 'selected' : '' ?>>PAUD</option>
                    <option value="SD" <?= $filterJenjang === 'SD' ? 'selected' : '' ?>>SD</option>
                    <option value="SMP" <?= $filterJenjang === 'SMP' ? 'selected' : '' ?>>SMP</option>
                    <option value="SMA" <?= $filterJenjang === 'SMA' ? 'selected' : '' ?>>SMA</option>
                </select>
            </div>

            <div class="w-full sm:w-1/4">
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kelas</label>
                <select name="kelas" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= e($k['kelas']) ?>" <?= $filterKelas === $k['kelas'] ? 'selected' : '' ?>><?= e($k['kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full sm:w-2/4 flex gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Cari Nama / NISN</label>
                    <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Masukkan nama atau NISN..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all self-end">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Gallery Grid -->
    <?php if (empty($siswa)): ?>
        <div class="text-center py-12 bg-white rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <h3 class="text-slate-800 font-bold text-sm">Belum Ada Data</h3>
            <p class="text-slate-500 text-xs mt-1">Tidak ada siswa yang ditemukan dengan filter tersebut.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($siswa as $s): 
                $identifierFoto = !empty($s['nisn']) ? $s['nisn'] : $s['id_siswa'];
                $fotoPath = BASE_PATH . '/public/uploads/siswa/' . $identifierFoto . '.jpg';
                $fotoUrl = file_exists($fotoPath) ? asset('uploads/siswa/' . $identifierFoto . '.jpg') : null;
            ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col group relative">
                    <div class="aspect-square bg-slate-100 relative">
                        <?php if ($fotoUrl): ?>
                            <img src="<?= $fotoUrl ?>?v=<?= filemtime($fotoPath) ?>" alt="<?= e($s['nama']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 gap-2">
                                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                <span class="text-[10px] font-bold">Belum Ada Foto</span>
                            </div>
                        <?php endif; ?>

                        <!-- Overlay Button (Upload) -->
                        <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 backdrop-blur-sm">
                            <a href="<?= url('kelola-siswa/cetak-kartu/' . $s['id']) ?>" target="_blank" class="px-4 py-2 w-28 text-center rounded-xl bg-indigo-600 text-white font-bold text-[10px] shadow-lg flex items-center justify-center gap-1.5 hover:bg-indigo-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" /></svg>
                                <span>Cetak Kartu</span>
                            </a>
                            <button onclick="openUploadModal(<?= $s['id'] ?>, '<?= addslashes(e($s['nama'])) ?>')" class="px-4 py-2 w-28 text-center rounded-xl bg-white text-slate-800 font-bold text-[10px] shadow-lg flex items-center justify-center gap-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                <span>Ganti Foto</span>
                            </button>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="font-bold text-slate-800 text-xs truncate" title="<?= e($s['nama']) ?>"><?= e($s['nama']) ?></div>
                        <div class="text-[10px] text-slate-500 mt-1 flex justify-between">
                            <span><?= e($s['jenjang']) ?></span>
                            <span class="font-bold text-slate-700">Kls <?= e($s['kelas'] ?: '-') ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-between bg-white px-5 py-4 rounded-3xl border border-slate-200/80 shadow-sm mt-6">
            <span class="text-[11px] text-slate-500 font-medium">Halaman <span class="font-bold text-slate-800"><?= $page ?></span> dari <span class="font-bold text-slate-800"><?= $totalPages ?></span></span>
            <div class="flex items-center gap-1.5">
                <?php
                $queryParams = $_GET;
                
                // Prev button
                $queryParams['page'] = max(1, $page - 1);
                $prevUrl = '?' . http_build_query($queryParams);
                
                // Next button
                $queryParams['page'] = min($totalPages, $page + 1);
                $nextUrl = '?' . http_build_query($queryParams);
                ?>
                <a href="<?= $prevUrl ?>" class="px-4 py-2 rounded-xl text-[11px] font-bold border transition-colors <?= $page <= 1 ? 'bg-slate-50 border-slate-100 text-slate-300 pointer-events-none' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                    &laquo; Prev
                </a>
                
                <a href="<?= $nextUrl ?>" class="px-4 py-2 rounded-xl text-[11px] font-bold border transition-colors <?= $page >= $totalPages ? 'bg-slate-50 border-slate-100 text-slate-300 pointer-events-none' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                    Next &raquo;
                </a>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <script>
    function openUploadModal(siswaId, namaSiswa) {
        document.getElementById('upload_siswa_id').value = siswaId;
        document.getElementById('upload_nama_siswa').textContent = namaSiswa;
        document.getElementById('modal-upload-foto').classList.remove('hidden');
    }
    </script>

    <!-- MODAL: Upload Template Kartu -->
    <div id="modal-upload-template" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200/80 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-base">
                        🖼️
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Template Kartu Siswa</h3>
                        <p class="text-[11px] text-slate-400 truncate max-w-[200px]">Upload desain template</p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-upload-template').classList.add('hidden')" class="p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                    ✕
                </button>
            </div>

            <form method="POST" action="<?= url('kelola-siswa/upload-template-kartu') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= CSRF::field() ?>

                <!-- Template Preview Grid -->
                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-2">Preview Template Saat Ini</label>
                    <div class="grid grid-cols-4 gap-2">
                        <?php 
                        $jenjangs = ['paud', 'sd', 'smp', 'sma'];
                        foreach ($jenjangs as $j): 
                            $tp = BASE_PATH . '/public/uploads/templates/kartu/template_' . $j . '.png';
                            $tu = file_exists($tp) ? asset('uploads/templates/kartu/template_' . $j . '.png') . '?v=' . filemtime($tp) : null;
                        ?>
                            <div class="flex flex-col items-center">
                                <div class="w-full aspect-[54/86] bg-slate-100 rounded-lg border border-slate-200 overflow-hidden relative shadow-sm flex items-center justify-center">
                                    <?php if ($tu): ?>
                                        <img src="<?= $tu ?>" alt="<?= strtoupper($j) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="text-[8px] font-bold text-slate-300 text-center px-1">Kosong</span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[9px] font-bold text-slate-500 mt-1 uppercase"><?= strtoupper($j) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pilih Jenjang</label>
                    <select name="jenjang" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="paud">PAUD</option>
                        <option value="sd">SD</option>
                        <option value="smp">SMP</option>
                        <option value="sma">SMA</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pilih File Template (PNG/JPG)</label>
                    <input type="file" name="template" accept="image/png, image/jpeg" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan resolusi sebanding dengan KTP Potrait (misal: 540x860 px).</p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="document.getElementById('modal-upload-template').classList.add('hidden')" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Upload Foto (Single) -->
    <div id="modal-upload-foto" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200/80 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base">
                        🖼️
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Upload Foto Siswa</h3>
                        <p class="text-[11px] text-slate-400 truncate max-w-[200px]" id="upload_nama_siswa"></p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-upload-foto').classList.add('hidden')" class="p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                    ✕
                </button>
            </div>

            <form method="POST" action="<?= url('kelola-siswa/upload-foto') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= CSRF::field() ?>
                <input type="hidden" name="siswa_id" id="upload_siswa_id" value="">

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pilih File Foto (JPG/PNG)</label>
                    <input type="file" name="foto" accept="image/jpeg, image/png" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-[10px] text-slate-400 mt-1">Foto akan otomatis dikompres menjadi maksimal 700KB.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="document.getElementById('modal-upload-foto').classList.add('hidden')" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Upload Foto (ZIP) -->
    <div id="modal-upload-zip" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200/80 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-2xl bg-slate-800 text-white flex items-center justify-center font-bold text-base">
                        📦
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Upload Massal (ZIP)</h3>
                        <p class="text-[11px] text-slate-400">Upload banyak foto sekaligus</p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-upload-zip').classList.add('hidden')" class="p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                    ✕
                </button>
            </div>

            <form method="POST" action="<?= url('kelola-siswa/upload-foto-zip') ?>" enctype="multipart/form-data" class="space-y-4">
                <?= CSRF::field() ?>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pilih File ZIP</label>
                    <input type="file" name="foto_zip" accept=".zip" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-slate-800 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                </div>

                <div class="p-3 rounded-2xl bg-sky-50 border border-sky-200 text-[11px] text-sky-800 leading-relaxed space-y-1">
                    <p>💡 <strong>Panduan:</strong></p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        <li>Kumpulkan file foto (JPG/PNG).</li>
                        <li>Pastikan nama file adalah <strong>NISN</strong> (contoh: <code class="bg-sky-100 px-1 rounded">0123456789.jpg</code>).</li>
                        <li>Jadikan file-file tersebut ke dalam satu file ZIP, lalu upload.</li>
                    </ul>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="document.getElementById('modal-upload-zip').classList.add('hidden')" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition-all">
                        Proses Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
