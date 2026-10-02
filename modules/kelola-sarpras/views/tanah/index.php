<?php
/**
 * View Data Aset Tanah
 * Modul Kelola Sarpras - Portal BIP
 */
?>
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/80 backdrop-blur-md p-6 rounded-3xl border border-primary-100 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-primary-600 mb-1">
                <span>Data Aset</span>
                <span class="text-slate-300">•</span>
                <span>Sarana & Prasarana</span>
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-2xl bg-primary-500/10 text-primary-600 border border-primary-500/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.53 3.22 1.5-1.5a.75.75 0 0 0 0-1.06l-4.5-4.5a.75.75 0 0 0-1.06 0l-2.47 2.47a.75.75 0 0 1-1.06 0L4.22 8.78a.75.75 0 0 0-1.06 0l-1.5 1.5a.75.75 0 0 0 0 1.06l7.5 7.5c.293.293.768.293 1.06 0l2.47-2.47a.75.75 0 0 1 1.06 0l1.97 1.97a.75.75 0 0 0 1.06 0l1.5-1.5a.75.75 0 0 0 0-1.06l-4.5-4.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5" />
                    </svg>
                </span>
                Inventaris Aset Tanah
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola data kepemilikan tanah yayasan, nomor sertifikat resmi, dimensi, luas otomatis, dan berkas sertifikat.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <!-- Tombol Laporan PDF (Portrait A4) -->
            <a href="<?= url('kelola-sarpras/tanah/cetak-pdf') ?>" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900 transition-all shadow-sm hover:shadow-md border border-slate-700">
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Laporan PDF (A4)</span>
            </a>

            <!-- Tombol Tambah Tanah -->
            <button type="button" 
                    onclick="openModalTambahTanah()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition-all shadow-md shadow-primary-500/20 hover:shadow-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Tanah</span>
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Bidang -->
        <div class="p-5 rounded-3xl bg-white border border-primary-100 shadow-sm relative overflow-hidden group hover:border-primary-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Bidang Tanah</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1"><?= number_format($summary['total_bidang'] ?? 0) ?> <span class="text-sm font-medium text-slate-400">Lokasi</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.53 3.22 1.5-1.5a.75.75 0 0 0 0-1.06l-4.5-4.5a.75.75 0 0 0-1.06 0l-2.47 2.47a.75.75 0 0 1-1.06 0L4.22 8.78a.75.75 0 0 0-1.06 0l-1.5 1.5a.75.75 0 0 0 0 1.06l7.5 7.5c.293.293.768.293 1.06 0l2.47-2.47a.75.75 0 0 1 1.06 0l1.97 1.97a.75.75 0 0 0 1.06 0l1.5-1.5a.75.75 0 0 0 0-1.06l-4.5-4.5" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-xs text-primary-600 font-medium flex items-center gap-1">
                <span>Terdata dalam sistem inventaris</span>
            </div>
        </div>

        <!-- Card 2: Total Luas -->
        <div class="p-5 rounded-3xl bg-white border border-primary-100 shadow-sm relative overflow-hidden group hover:border-primary-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Luas Tanah</p>
                    <h3 class="text-2xl font-black text-primary-700 mt-1">
                        <?= number_format($summary['total_luas'] ?? 0, 0, ',', '.') ?> 
                        <span class="text-sm font-bold text-slate-500">m²</span>
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-xs text-primary-600 font-medium">
                &plusmn; <?= number_format(($summary['total_luas'] ?? 0) / 10000, 2, ',', '.') ?> Hektar area
            </div>
        </div>

        <!-- Card 3: Total Nilai Aset Tanah -->
        <div class="p-5 rounded-3xl bg-white border border-primary-100 shadow-sm relative overflow-hidden group hover:border-primary-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Nilai Perolehan</p>
                    <h3 class="text-xl font-black text-primary-700 mt-1">
                        Rp <?= number_format($summary['total_nilai'] ?? 0, 0, ',', '.') ?>
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v8.25m0-8.25a60.074 60.074 0 0 1 15.797-2.101c.727-.198 1.453.342 1.453 1.096V6m0 0a.75.75 0 0 1 .75.75v.75m0 0v8.25m0-8.25a60.075 60.075 0 0 1-15.797 2.101c-.727.198-1.453-.342-1.453-1.096V12" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-xs text-primary-600 font-medium">
                Kapitalisasi Aset Tetap Tanah
            </div>
        </div>

        <!-- Card 4: Total Bangunan Berdiri -->
        <div class="p-5 rounded-3xl bg-white border border-amber-100 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Bangunan Berdiri</p>
                    <h3 class="text-2xl font-black text-amber-700 mt-1"><?= number_format($summary['total_bangunan'] ?? 0) ?> <span class="text-sm font-medium text-slate-400">Gedung</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 17.25 8.1L12 4.6 6.75 8.1A2.25 2.25 0 0 0 4.5 10.333V21h15Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-xs text-amber-600 font-medium">
                Terhubung ke bidang tanah ini
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls (Live Search) -->
    <div class="p-4 bg-white rounded-3xl border border-primary-100 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <!-- Live Search Input -->
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <input type="text" 
                   id="liveSearchInput" 
                   placeholder="Ketik untuk Live Search (Nama, No Sertifikat, Lokasi)..." 
                   class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all text-slate-800 placeholder-slate-400"
                   onkeyup="handleLiveSearch(this.value)">
            <button type="button" 
                    id="clearSearchBtn" 
                    onclick="clearLiveSearch()"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Filter Kepemilikan -->
            <select id="filterKepemilikan" 
                    onchange="filterTableByKepemilikan(this.value)"
                    class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Status Kepemilikan</option>
                <option value="SHM">SHM (Hak Milik)</option>
                <option value="HGB">HGB (Guna Bangunan)</option>
                <option value="Hak Pakai">Hak Pakai</option>
                <option value="Wakaf">Wakaf</option>
                <option value="Hibah">Hibah</option>
                <option value="Lainnya">Lainnya</option>
            </select>

            <span id="searchResultBadge" class="text-xs font-bold text-primary-700 bg-primary-50 border border-primary-200 px-3 py-1.5 rounded-full">
                Menampilkan <?= count($items) ?> data
            </span>
        </div>
    </div>

    <!-- Tabel Data Tanah -->
    <div class="bg-white rounded-3xl border border-primary-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tanahTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-4 w-12 text-center">No</th>
                        <th class="py-4 px-4">Nama Tanah & Lokasi</th>
                        <th class="py-4 px-4">No. Sertifikat</th>
                        <th class="py-4 px-4">Ukuran (P &times; L)</th>
                        <th class="py-4 px-4">Luas Total</th>
                        <th class="py-4 px-4">Perolehan</th>
                        <th class="py-4 px-4">Sertifikat</th>
                        <th class="py-4 px-4 text-center">Gedung</th>
                        <th class="py-4 px-4 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm" id="tanahTableBody">
                    <?php if (empty($items)): ?>
                        <tr id="emptyRow">
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-500 mx-auto flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </div>
                                    <p class="font-semibold text-slate-600">Belum ada data aset tanah</p>
                                    <p class="text-xs text-slate-400">Klik tombol "Tambah Tanah" untuk mendaftarkan bidang tanah pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $index => $row): 
                            $statusBadge = match($row['status_kepemilikan'] ?? '') {
                                'SHM' => 'bg-primary-50 text-primary-700 border-primary-200',
                                'HGB' => 'bg-primary-50 text-primary-700 border-primary-200',
                                'Wakaf' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'Hak Pakai' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                            };
                            $certList = $row['gambar_sertifikat_list'] ?? [];
                            $certCount = count($certList);
                        ?>
                            <tr class="hover:bg-primary-50/40 transition-colors tanah-row"
                                data-search="<?= strtolower(e($row['nama_tanah'] . ' ' . $row['kode_tanah'] . ' ' . $row['no_sertifikat'] . ' ' . $row['alamat_lokasi'] . ' ' . $row['status_kepemilikan'])) ?>"
                                data-kepemilikan="<?= e($row['status_kepemilikan'] ?? '') ?>">
                                <td class="py-4 px-4 text-center font-bold text-slate-400 row-number">
                                    <?= $index + 1 ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold text-xs shrink-0 border border-primary-100">
                                            <?= e($row['kode_tanah']) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <button type="button" 
                                                    onclick="toggleTanahRowAccordion(<?= $row['id'] ?>)"
                                                    class="text-left font-bold text-slate-800 hover:text-primary-600 transition-colors leading-snug cursor-pointer flex items-center gap-1.5 group">
                                                <span><?= e($row['nama_tanah']) ?></span>
                                                <svg id="chevron-tanah-title-<?= $row['id'] ?>" class="w-3.5 h-3.5 text-primary-500 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                            <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5 truncate max-w-xs">
                                                <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                                <?= e($row['alamat_lokasi'] ?: 'Alamat belum diatur') ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold border <?= $statusBadge ?> mb-1">
                                        <?= e($row['status_kepemilikan']) ?>
                                    </span>
                                    <p class="font-medium text-xs text-slate-700 font-mono">
                                        <?= e($row['no_sertifikat'] ?: '-') ?>
                                    </p>
                                </td>
                                <td class="py-4 px-4 text-xs text-slate-600 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800"><?= number_format($row['panjang'], 1, ',', '.') ?> m</span> 
                                    &times; 
                                    <span class="font-semibold text-slate-800"><?= number_format($row['lebar'], 1, ',', '.') ?> m</span>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-black text-primary-700 text-sm">
                                        <?= number_format($row['luas'], 0, ',', '.') ?> m²
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        &plusmn; <?= number_format($row['luas'] / 10000, 3, ',', '.') ?> Ha
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-xs whitespace-nowrap">
                                    <div class="font-semibold text-slate-700">
                                        Rp <?= number_format($row['harga_perolehan'], 0, ',', '.') ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Tahun <?= e($row['tahun_perolehan'] ?: '-') ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <?php if ($certCount > 0): ?>
                                        <button type="button" 
                                                onclick='openGalleryModal(<?= htmlspecialchars(json_encode($certList), ENT_QUOTES, 'UTF-8') ?>, "<?= htmlspecialchars($row['nama_tanah'], ENT_QUOTES) ?>")'
                                                class="group flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-primary-50 border border-primary-200 text-primary-700 hover:bg-primary-100 hover:border-primary-300 transition-all text-xs font-semibold">
                                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                            <span><?= $certCount ?> Foto</span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Tidak ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <button type="button" 
                                            onclick="toggleTanahRowAccordion(<?= $row['id'] ?>)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl text-xs font-bold <?= (int)$row['total_bangunan'] > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' ?> transition-all shadow-2xs group cursor-pointer"
                                            title="Klik untuk membuka/menutup daftar Bangunan & Ruangan">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 17.25 8.1L12 4.6 6.75 8.1A2.25 2.25 0 0 0 4.5 10.333V21h15Z" />
                                        </svg>
                                        <span><?= (int)$row['total_bangunan'] ?> Gedung</span>
                                        <svg id="chevron-tanah-badge-<?= $row['id'] ?>" class="w-3.5 h-3.5 text-amber-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol + Bangunan (Sesuai Permintaan) -->
                                        <button type="button" 
                                                onclick='openModalTambahBangunan(<?= $row['id'] ?>, "<?= htmlspecialchars($row['nama_tanah'], ENT_QUOTES) ?>")'
                                                title="Tambah Gedung / Bangunan di tanah ini"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-primary-50 hover:bg-primary-100 text-primary-700 border border-primary-200 text-xs font-bold transition-all shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <span>Bangunan</span>
                                        </button>

                                        <!-- Tombol Edit -->
                                        <button type="button" 
                                                onclick='openModalEditTanah(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)'
                                                title="Edit Data Tanah"
                                                class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form action="<?= url('kelola-sarpras/tanah/delete/' . $row['id']) ?>" 
                                              method="POST" 
                                              class="inline" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data tanah <?= htmlspecialchars($row['nama_tanah'], ENT_QUOTES) ?>?\n\nPeringatan: Semua gedung dan data terkait pada bidang tanah ini akan ikut terhapus!');">
                                            <?= CSRF::field() ?>
                                            <button type="submit" 
                                                    title="Hapus Data Tanah"
                                                    class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- ACCORDION ROW: BANGUNAN & RUANGAN DI TANAH INI -->
                            <tr id="accordion-row-tanah-<?= $row['id'] ?>" class="accordion-tanah-subrow hidden bg-slate-50/70 border-b border-primary-200/80">
                                <td colspan="9" class="p-4 sm:p-5">
                                    <div class="bg-white rounded-2xl border border-primary-200/90 p-4 sm:p-5 shadow-xs space-y-4">
                                        <!-- Header Sub-panel Bangunan di Tanah Ini -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-8 h-8 rounded-xl bg-primary-50 border border-primary-200 text-primary-700 flex items-center justify-center font-bold">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 17.25 8.1L12 4.6 6.75 8.1A2.25 2.25 0 0 0 4.5 10.333V21h15Z" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                        <span>Daftar Bangunan di Bidang:</span>
                                                        <span class="text-primary-700 font-black"><?= e($row['nama_tanah']) ?></span>
                                                    </h4>
                                                    <p class="text-[11px] text-slate-400">
                                                        Klik bangunan di bawah untuk melihat daftar ruangan &amp; fasilitas di dalamnya
                                                    </p>
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    onclick='openModalTambahBangunan(<?= $row['id'] ?>, "<?= htmlspecialchars($row['nama_tanah'], ENT_QUOTES) ?>")'
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-xs transition-all self-start sm:self-auto">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                <span>Tambah Bangunan di Tanah Ini</span>
                                            </button>
                                        </div>

                                        <!-- Content Bangunan (Accordion Level 2) -->
                                        <?php 
                                            $bList = $row['bangunan_list'] ?? []; 
                                        ?>
                                        <?php if (empty($bList)): ?>
                                            <div class="py-8 text-center bg-slate-50/50 rounded-xl border border-dashed border-slate-200 text-slate-400 space-y-2">
                                                <p class="text-xs font-semibold">Belum ada gedung/bangunan fisik yang tercatat di bidang tanah ini.</p>
                                                <button type="button" 
                                                        onclick='openModalTambahBangunan(<?= $row['id'] ?>, "<?= htmlspecialchars($row['nama_tanah'], ENT_QUOTES) ?>")'
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-2xs">
                                                    Tambah Bangunan Pertama
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="space-y-3">
                                                <?php foreach ($bList as $b): 
                                                    $bId = (int)$b['id'];
                                                    $rList = $b['ruangan_list'] ?? [];
                                                    $totalRuang = count($rList);
                                                    $kondisiClass = match($b['kondisi_bangunan'] ?? 'Baik') {
                                                        'Baik' => 'bg-primary-50 text-primary-700 border-primary-200',
                                                        'Rusak Ringan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        'Rusak Berat' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                                    };
                                                ?>
                                                    <div class="bg-slate-50/80 rounded-2xl border border-slate-200 overflow-hidden hover:border-primary-300 transition-all">
                                                        <!-- Bangunan Header (Clickable to show rooms) -->
                                                        <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer select-none bg-white hover:bg-slate-50/90 transition-colors"
                                                             onclick="toggleTanahBangunanRooms(<?= $bId ?>, event)">
                                                             
                                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                                <div id="chevron-tanah-bgn-<?= $bId ?>" class="w-6 h-6 rounded-lg bg-primary-50 border border-primary-200 text-primary-600 flex items-center justify-center transition-transform shrink-0">
                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                                    </svg>
                                                                </div>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex items-center gap-2 flex-wrap">
                                                                        <span class="font-bold text-slate-800 text-xs sm:text-sm"><?= e($b['nama_bangunan']) ?></span>
                                                                        <span class="font-mono text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded"><?= e($b['kode_bangunan']) ?></span>
                                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border <?= $kondisiClass ?>"><?= e($b['kondisi_bangunan']) ?></span>
                                                                    </div>
                                                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2 flex-wrap font-medium">
                                                                        <span class="font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded"><?= (int)$b['jumlah_lantai'] ?> Lantai</span>
                                                                        <span class="text-primary-700 font-bold">Luas: <?= number_format($b['luas_bangunan'], 0, ',', '.') ?> m²</span>
                                                                        <?php if (!empty($b['tahun_dibangun'])): ?>
                                                                            <span>&bull; Thn <?= e($b['tahun_dibangun']) ?></span>
                                                                        <?php endif; ?>
                                                                        <?php if ((int)($b['masa_manfaat'] ?? 0) > 0): ?>
                                                                            <span class="text-primary-600 font-semibold">&bull; Masa: <?= (int)$b['masa_manfaat'] ?> Thn</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-2 self-end sm:self-center shrink-0" onclick="event.stopPropagation()">
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold <?= $totalRuang > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-400' ?>">
                                                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                                                    </svg>
                                                                    <span><?= $totalRuang ?> Ruang</span>
                                                                </span>

                                                                <button type="button" 
                                                                        onclick='openModalTambahRuangInline(<?= $bId ?>, <?= htmlspecialchars(json_encode($b['nama_bangunan']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$b['jumlah_lantai'] ?>)'
                                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold border border-primary-200 transition-colors">
                                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                                    <span>+ Ruang</span>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Ruangan Accordion Level 3 -->
                                                        <div id="tanah-bgn-rooms-<?= $bId ?>" class="hidden p-3.5 bg-slate-50 border-t border-slate-200 space-y-2.5">
                                                            <?php if (empty($rList)): ?>
                                                                <div class="p-3 text-center bg-white rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                                                                    Belum ada ruangan di gedung ini.
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                                                    <?php foreach ($rList as $r): 
                                                                        $rLuasText = ($r['luas'] > 0) ? number_format($r['luas'], 1, ',', '.') . ' m²' : '-';
                                                                        $rDimText = ($r['panjang'] > 0 && $r['lebar'] > 0) ? number_format($r['panjang'], 1, ',', '.') . ' &times; ' . number_format($r['lebar'], 1, ',', '.') . ' m' : '';
                                                                    ?>
                                                                        <div class="bg-white p-3 rounded-xl border border-slate-200 flex flex-col justify-between shadow-2xs">
                                                                            <div>
                                                                                <div class="flex items-center justify-between gap-1 mb-1">
                                                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                                                                        <?= e($r['jenis_ruangan'] ?? 'Ruang Kelas') ?>
                                                                                    </span>
                                                                                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                                                                        Lantai <?= (int)($r['lantai'] ?? 1) ?>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="font-bold text-slate-800 text-xs sm:text-sm">
                                                                                    <?= e($r['nama_ruangan']) ?>
                                                                                </div>
                                                                                <div class="text-[10px] text-slate-400 font-mono">
                                                                                    <?= e($r['kode_ruangan']) ?> &bull; Unit: <?= e($r['unit'] ?? 'Semua') ?>
                                                                                </div>
                                                                                <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-600 space-y-0.5">
                                                                                    <div class="flex justify-between">
                                                                                        <span class="text-slate-400">Luas:</span>
                                                                                        <span class="font-bold text-slate-700"><?= $rLuasText ?> <?= $rDimText ? "($rDimText)" : "" ?></span>
                                                                                    </div>
                                                                                    <div class="flex justify-between">
                                                                                        <span class="text-slate-400">PJ:</span>
                                                                                        <span class="font-semibold text-primary-800 truncate max-w-[130px]"><?= e($r['penanggung_jawab'] ?: '-') ?></span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 1. MODAL TAMBAH TANAH                                          -->
<!-- ============================================================== -->
<div id="modalTambahTanah" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Modal Header (Fixed at top) -->
        <div class="px-6 py-4 border-b border-primary-100 bg-primary-50 to-primary-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Tambah Data Aset Tanah</h3>
                    <p class="text-xs text-slate-500">Daftarkan bidang tanah baru ke sistem inventaris</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahTanah()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form dengan Scroll Internal -->
        <form action="<?= url('kelola-sarpras/tanah/store') ?>" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>

            <!-- Scrollable Content Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Tanah -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Bidang Tanah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama_tanah" 
                               required 
                               placeholder="Contoh: Tanah Kampus Utama BIP - Lere" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <!-- No Sertifikat -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Sertifikat Resmi
                        </label>
                        <input type="text" 
                               name="no_sertifikat" 
                               placeholder="Contoh: SHM No. 00412/Lere/2018" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <!-- Status Kepemilikan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status Kepemilikan
                        </label>
                        <select name="status_kepemilikan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="SHM" selected>SHM (Sertifikat Hak Milik)</option>
                            <option value="HGB">HGB (Hak Guna Bangunan)</option>
                            <option value="Hak Pakai">Hak Pakai</option>
                            <option value="Wakaf">Tanah Wakaf</option>
                            <option value="Hibah">Tanah Hibah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- UKURAN: Panjang, Lebar, Luas Otomatis -->
                    <div class="sm:col-span-2 p-4 rounded-2xl bg-primary-50/50 border border-primary-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-primary-800 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                </svg>
                                Dimensi & Luas Tanah (Otomatis)
                            </span>
                            <span class="text-[11px] text-primary-600 bg-white px-2 py-0.5 rounded-full border border-primary-200 font-semibold">
                                Panjang &times; Lebar = Luas m²
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (Meter)</label>
                                <input type="number" 
                                       step="0.01" 
                                       id="tambah_panjang" 
                                       name="panjang" 
                                       placeholder="0.00" 
                                       oninput="calculateLuasTambah()"
                                       class="w-full px-3 py-2 bg-white border border-primary-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (Meter)</label>
                                <input type="number" 
                                       step="0.01" 
                                       id="tambah_lebar" 
                                       name="lebar" 
                                       placeholder="0.00" 
                                       oninput="calculateLuasTambah()"
                                       class="w-full px-3 py-2 bg-white border border-primary-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-primary-900 mb-1">
                                    Luas Total (m²) <span class="text-primary-600 font-normal">(Otomatis)</span>
                                </label>
                                <input type="number" 
                                       step="0.01" 
                                       id="tambah_luas" 
                                       name="luas" 
                                       placeholder="0.00" 
                                       class="w-full px-3 py-2 bg-primary-100/60 border border-primary-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-black text-primary-900">
                            </div>
                        </div>
                    </div>

                    <!-- Tahun Perolehan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tahun Perolehan
                        </label>
                        <input type="number" 
                               name="tahun_perolehan" 
                               placeholder="Contoh: 2018" 
                               value="<?= date('Y') ?>"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <!-- Harga Perolehan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Harga Perolehan (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="text" 
                                   name="harga_perolehan" 
                                   placeholder="0" 
                                   onkeyup="formatRupiahInput(this)"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all text-slate-800">
                        </div>
                    </div>

                    <!-- Alamat / Lokasi -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat / Lokasi Bidang Tanah
                        </label>
                        <input type="text" 
                               name="alamat_lokasi" 
                               placeholder="Contoh: Jl. Trans Palu - Donggala No. 45, Kel. Lere, Kota Palu" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <!-- Upload Gambar Sertifikat (Bisa > 1 / Opsional) -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Foto / Berkas Sertifikat (Bisa Lebih Dari 1) <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <div class="p-4 rounded-2xl border-2 border-dashed border-primary-200 hover:border-primary-400 bg-primary-50/20 text-center transition-all">
                            <svg class="w-8 h-8 text-primary-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>
                            <label for="upload_gambar_sertifikat_tambah" class="cursor-pointer">
                                <span class="text-sm font-semibold text-primary-600 hover:underline">Pilih file gambar</span>
                                <span class="text-xs text-slate-400 block mt-0.5">Mendukung format JPG, PNG, WEBP, atau PDF. Bisa pilih beberapa file sekaligus.</span>
                            </label>
                            <input type="file" 
                                   id="upload_gambar_sertifikat_tambah" 
                                   name="gambar_sertifikat[]" 
                                   multiple 
                                   accept="image/jpeg,image/png,image/webp,application/pdf"
                                   class="hidden"
                                   onchange="previewMultiFiles(this, 'previewContainerTambah')">
                            <div id="previewContainerTambah" class="mt-3 flex flex-wrap gap-2 justify-center"></div>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Keterangan Tambahan
                        </label>
                        <textarea name="keterangan" 
                                  rows="2" 
                                  placeholder="Catatan batas tanah, riwayat perolehan, atau dokumen pendukung..."
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all"></textarea>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer Tombol Simpan (Always visible) -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" 
                        onclick="closeModalTambahTanah()"
                        class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-2xl bg-primary-600 text-white font-bold text-sm shadow-md shadow-primary-500/20 hover:bg-primary-700 transition-all">
                    Simpan Data Tanah
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- 2. MODAL EDIT TANAH                                            -->
<!-- ============================================================== -->
<div id="modalEditTanah" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Modal Header (Fixed at top) -->
        <div class="px-6 py-4 border-b border-primary-100 bg-primary-50 to-primary-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Edit Data Aset Tanah</h3>
                    <p class="text-xs text-slate-500" id="edit_kode_label">Perbarui rincian aset tanah</p>
                </div>
            </div>
            <button type="button" onclick="closeModalEditTanah()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form dengan Scroll Internal -->
        <form id="formEditTanah" action="" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>

            <!-- Scrollable Content Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Bidang Tanah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="edit_nama_tanah" 
                               name="nama_tanah" 
                               required 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Sertifikat Resmi
                        </label>
                        <input type="text" 
                               id="edit_no_sertifikat" 
                               name="no_sertifikat" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status Kepemilikan
                        </label>
                        <select id="edit_status_kepemilikan" name="status_kepemilikan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="SHM">SHM (Sertifikat Hak Milik)</option>
                            <option value="HGB">HGB (Hak Guna Bangunan)</option>
                            <option value="Hak Pakai">Hak Pakai</option>
                            <option value="Wakaf">Tanah Wakaf</option>
                            <option value="Hibah">Tanah Hibah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 p-4 rounded-2xl bg-primary-50/50 border border-primary-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-primary-800 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                </svg>
                                Dimensi & Luas Tanah (Otomatis)
                            </span>
                            <span class="text-[11px] text-primary-600 bg-white px-2 py-0.5 rounded-full border border-primary-200 font-semibold">
                                Panjang &times; Lebar = Luas m²
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (Meter)</label>
                                <input type="number" 
                                       step="0.01" 
                                       id="edit_panjang" 
                                       name="panjang" 
                                       oninput="calculateLuasEdit()"
                                       class="w-full px-3 py-2 bg-white border border-primary-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (Meter)</label>
                                <input type="number" 
                                       step="0.01" 
                                       id="edit_lebar" 
                                       name="lebar" 
                                       oninput="calculateLuasEdit()"
                                       class="w-full px-3 py-2 bg-white border border-primary-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-slate-800">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-primary-900 mb-1">
                                    Luas Total (m²) <span class="text-primary-600 font-normal">(Otomatis)</span>
                                </label>
                                <input type="number" 
                                       step="0.01" 
                                       id="edit_luas" 
                                       name="luas" 
                                       class="w-full px-3 py-2 bg-primary-100/60 border border-primary-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-black text-primary-900">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tahun Perolehan
                        </label>
                        <input type="number" 
                               id="edit_tahun_perolehan" 
                               name="tahun_perolehan" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Harga Perolehan (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="text" 
                                   id="edit_harga_perolehan" 
                                   name="harga_perolehan" 
                                   onkeyup="formatRupiahInput(this)"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all text-slate-800">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat / Lokasi Bidang Tanah
                        </label>
                        <input type="text" 
                               id="edit_alamat_lokasi" 
                               name="alamat_lokasi" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <!-- Gambar Sertifikat Tersimpan & Tambah Baru -->
                    <div class="sm:col-span-2 space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Foto / Berkas Sertifikat
                        </label>
                        <div id="existingImagesList" class="flex flex-wrap gap-2 mb-2"></div>

                        <div class="p-4 rounded-2xl border-2 border-dashed border-primary-200 bg-primary-50/20 text-center">
                            <label for="upload_gambar_sertifikat_edit" class="cursor-pointer">
                                <span class="text-sm font-semibold text-primary-600 hover:underline">Unggah Foto Tambahan</span>
                                <span class="text-xs text-slate-400 block mt-0.5">Pilih foto jika ingin menambahkan sertifikat baru</span>
                            </label>
                            <input type="file" 
                                   id="upload_gambar_sertifikat_edit" 
                                   name="gambar_sertifikat[]" 
                                   multiple 
                                   accept="image/jpeg,image/png,image/webp,application/pdf"
                                   class="hidden"
                                   onchange="previewMultiFiles(this, 'previewContainerEdit')">
                            <div id="previewContainerEdit" class="mt-3 flex flex-wrap gap-2 justify-center"></div>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Keterangan Tambahan
                        </label>
                        <textarea id="edit_keterangan" 
                                  name="keterangan" 
                                  rows="2" 
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all"></textarea>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer Tombol Simpan (Always visible) -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" 
                        onclick="closeModalEditTanah()"
                        class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-2xl bg-primary-600 text-white font-bold text-sm shadow-md shadow-primary-500/20 hover:bg-primary-700 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- 3. MODAL TAMBAH BANGUNAN DARI TABEL TANAH                      -->
<!-- ============================================================== -->
<div id="modalTambahBangunan" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
    <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Modal Header (Fixed at top) -->
        <div class="px-6 py-4 border-b border-primary-100 bg-gradient-to-r from-amber-50 to-primary-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 17.25 8.1L12 4.6 6.75 8.1A2.25 2.25 0 0 0 4.5 10.333V21h15Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Tambah Gedung / Bangunan</h3>
                    <p class="text-xs text-slate-500">Membangun aset fisik di atas bidang tanah terpilih</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahBangunan()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form dengan Scroll Internal -->
        <form action="<?= url('kelola-sarpras/bangunan/store') ?>" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>
            <input type="hidden" name="tanah_id" id="bgn_tanah_id">
            <input type="hidden" name="return_to" value="<?= url('kelola-sarpras/tanah') ?>">

            <!-- Scrollable Content Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                <!-- Info Tanah Induk -->
                <div class="p-3.5 rounded-2xl bg-primary-50 border border-primary-200 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-primary-700 uppercase tracking-wider">Tanah Lokasi Pembangunan</p>
                        <p class="text-sm font-black text-slate-800" id="bgn_tanah_nama_label">-</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Bangunan / Gedung <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama_bangunan" 
                               required 
                               placeholder="Contoh: Gedung Pembelajaran Terpadu A" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Jumlah Lantai
                        </label>
                        <input type="number" 
                               name="jumlah_lantai" 
                               min="1" 
                               value="1" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kondisi Bangunan
                        </label>
                        <select name="kondisi_bangunan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="Baik" selected>Baik (Sangat Layak)</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (m)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="bgn_panjang" 
                                   name="panjang" 
                                   placeholder="0.00" 
                                   oninput="calculateLuasBgn()"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (m)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="bgn_lebar" 
                                   name="lebar" 
                                   placeholder="0.00" 
                                   oninput="calculateLuasBgn()"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-primary-800 mb-1">Luas Bangunan (m²)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="bgn_luas" 
                                   name="luas_bangunan" 
                                   placeholder="0.00" 
                                   class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-black text-primary-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tahun Dibangun
                        </label>
                        <input type="number" 
                               name="tahun_dibangun" 
                               placeholder="Contoh: 2021" 
                               value="<?= date('Y') ?>"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Masa Manfaat (Tahun)
                        </label>
                        <input type="number" 
                               name="masa_manfaat" 
                               min="1"
                               value="20"
                               placeholder="Contoh: 20" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Sumber Dana
                        </label>
                        <select name="sumber_dana" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="Yayasan" selected>Yayasan</option>
                            <option value="BOS">Dana BOS</option>
                            <option value="Pemerintah / DAK">Pemerintah / DAK</option>
                            <option value="Hibah / CSR">Hibah / CSR</option>
                            <option value="Komite">Komite Sekolah</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Harga Perolehan (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="text" 
                                   name="biaya_pembangunan" 
                                   placeholder="0" 
                                   onkeyup="formatRupiahInput(this)"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all text-slate-800">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Foto Bangunan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <input type="file" 
                               name="foto_bangunan" 
                               accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>
                </div>
            </div>

            <!-- Sticky Footer Tombol Simpan (Always visible) -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" 
                        onclick="closeModalTambahBangunan()"
                        class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-500/20 transition-all">
                    Simpan Bangunan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- 3B. MODAL TAMBAH RUANG (DARI AKORDEON GEDUNG)                 -->
<!-- ============================================================== -->
<div id="modalTambahRuangTanah" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-primary-100 bg-primary-50 to-primary-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Tambah Ruangan / Fasilitas</h3>
                    <p class="text-xs text-slate-500">Mendaftarkan unit ruangan di dalam gedung ini</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahRuangTanah()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Scrollable -->
        <form action="<?= url('kelola-sarpras/ruangan/store') ?>" method="POST" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>
            <input type="hidden" name="bangunan_id" id="inline_ruang_bgn_id">
            <input type="hidden" name="return_to" value="<?= url('kelola-sarpras/tanah') ?>">

            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                <!-- Info Gedung Induk Card -->
                <div class="p-3.5 rounded-2xl bg-primary-50 border border-primary-200 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 17.25 8.1L12 4.6 6.75 8.1A2.25 2.25 0 0 0 4.5 10.333V21h15Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-primary-700 uppercase tracking-wider">Gedung Induk</p>
                            <p class="text-sm font-black text-slate-800" id="inline_ruang_bgn_nama_label">-</p>
                        </div>
                    </div>
                    <span id="inline_ruang_bgn_lantai_badge" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-white text-primary-700 border border-primary-200 shrink-0">- Lt</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Ruangan / Fasilitas <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama_ruangan" 
                               id="inline_ruang_nama"
                               required 
                               placeholder="Contoh: Ruang Kelas 1A, Lab Komputer, Perpustakaan" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Jenis Ruangan <span class="text-rose-500">*</span>
                        </label>
                        <select name="jenis_ruangan" id="inline_ruang_jenis" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="Ruang Kelas" selected>Ruang Kelas (Belajar)</option>
                            <option value="Ruang Laboratorium">Ruang Laboratorium (Komputer/IPA/Bahasa)</option>
                            <option value="Ruang Kantor">Ruang Kantor / Administrasi</option>
                            <option value="Ruang Guru">Ruang Guru</option>
                            <option value="Ruang Pimpinan">Ruang Kepala Sekolah / Pimpinan</option>
                            <option value="Ruang Perpustakaan">Ruang Perpustakaan</option>
                            <option value="Ruang UKS">Ruang UKS / Medis</option>
                            <option value="Ruang Ibadah">Ruang Ibadah / Masjid / Musholla</option>
                            <option value="Ruang Aula">Ruang Aula / Serbaguna</option>
                            <option value="Ruang Konseling / BK">Ruang Bimbingan Konseling (BK)</option>
                            <option value="Ruang OSIS">Ruang OSIS / Ekstrakurikuler</option>
                            <option value="Gudang">Gudang / Logistik</option>
                            <option value="Toilet">Toilet / Sanitasi</option>
                            <option value="Kantin">Kantin / Dapur</option>
                            <option value="Lainnya">Fasilitas Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Posisi Lantai
                        </label>
                        <select name="lantai" id="inline_ruang_lantai_select" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="1">Lantai 1</option>
                        </select>
                    </div>

                    <!-- Dimensi Ruangan: Panjang, Lebar, Luas -->
                    <div class="sm:col-span-2 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (m)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="inline_ruang_panjang" 
                                   name="panjang" 
                                   placeholder="0.00" 
                                   oninput="calculateLuasRuangInline()"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (m)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="inline_ruang_lebar" 
                                   name="lebar" 
                                   placeholder="0.00" 
                                   oninput="calculateLuasRuangInline()"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-primary-800 mb-1">Luas Ruang (m²)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="inline_ruang_luas" 
                                   name="luas" 
                                   placeholder="0.00" 
                                   class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-black text-primary-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kode Ruangan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <input type="text" 
                               name="kode_ruangan" 
                               id="inline_ruang_kode"
                               placeholder="Otomatis jika kosong" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Unit / Jenjang
                        </label>
                        <select name="unit" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                            <option value="PAUD">PAUD</option>
                            <option value="Yayasan">Yayasan</option>
                            <option value="Semua" selected>Semua / Umum</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kapasitas (Orang)
                        </label>
                        <input type="number" 
                               name="kapasitas" 
                               value="30" 
                               min="0" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Penanggung Jawab / Pengelola Ruang</span>
                            <span class="text-[10px] text-primary-600 font-semibold lowercase">dropdown search data pegawai</span>
                        </label>
                        <select name="penanggung_jawab" 
                                id="inline_ruang_pj" 
                                class="searchable-select w-full" 
                                data-placeholder="-- Pilih Penanggung Jawab (Guru / Pegawai) --" 
                                data-search-placeholder="Cari nama guru, NIY, unit tugas, jabatan...">
                            <option value="">-- Tanpa Penanggung Jawab --</option>
                            <?php if (!empty($pegawaiList)): ?>
                                <?php foreach ($pegawaiList as $p): 
                                    $namaLengkap = $p['nama'];
                                    if (!empty($p['gelar']) && !str_contains($p['nama'], $p['gelar'])) {
                                        $namaLengkap .= ', ' . $p['gelar'];
                                    }
                                    $badge = $p['unit_tugas'] ?? $p['jabatan'] ?? 'Pegawai';
                                    $subtext = !empty($p['niy']) ? 'NIY: ' . $p['niy'] : (!empty($p['jabatan']) ? $p['jabatan'] : '');
                                    $fotoUrl = !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '';
                                ?>
                                    <option value="<?= e($namaLengkap) ?>" 
                                            data-badge="<?= e($badge) ?>" 
                                            data-subtext="<?= e($subtext) ?>"
                                            data-unit="<?= e($p['unit_tugas'] ?? '') ?>"
                                            data-image="<?= e($fotoUrl) ?>">
                                        <?= e($namaLengkap) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Keterangan / Fungsi <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <input type="text" 
                               name="keterangan" 
                               placeholder="Contoh: Ruang kelas ber-AC dengan proyektor" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>
                </div>
            </div>

            <!-- Sticky Footer Tombol Simpan -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" 
                        onclick="closeModalTambahRuangTanah()" 
                        class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-500/20 transition-all">
                    Simpan Ruangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- 4. MODAL GALLERY SERTIFIKAT                                    -->
<!-- ============================================================== -->
<div id="modalGallery" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/80 backdrop-blur-md p-4 sm:p-6 flex items-center justify-center">
    <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden my-8">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-800 text-base" id="galleryTitle">Dokumen Sertifikat</h3>
                <p class="text-xs text-slate-400">Pratinjau berkas sertifikat tanah</p>
            </div>
            <button type="button" onclick="closeGalleryModal()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div id="galleryContent" class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto"></div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT LOGIC (Live Search, Auto Calculate, Multi-upload Preview) -->
<script>
// 1. Live Search Tabel
function handleLiveSearch(query) {
    const filter = query.toLowerCase().trim();
    const rows = document.querySelectorAll('.tanah-row');
    const clearBtn = document.getElementById('clearSearchBtn');
    let visibleCount = 0;

    if (filter.length > 0) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        const kepemilikanFilter = document.getElementById('filterKepemilikan').value;
        const rowKepemilikan = row.getAttribute('data-kepemilikan') || '';

        const matchSearch = text.includes(filter);
        const matchKepemilikan = !kepemilikanFilter || rowKepemilikan === kepemilikanFilter;

        if (matchSearch && matchKepemilikan) {
            row.style.display = '';
            visibleCount++;
            row.querySelector('.row-number').textContent = visibleCount;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('searchResultBadge').textContent = `Menampilkan ${visibleCount} data`;
}

function clearLiveSearch() {
    const input = document.getElementById('liveSearchInput');
    input.value = '';
    handleLiveSearch('');
    input.focus();
}

function filterTableByKepemilikan(val) {
    const query = document.getElementById('liveSearchInput').value;
    handleLiveSearch(query);
}

// 2. Hitung Luas Otomatis Tambah
function calculateLuasTambah() {
    const p = parseFloat(document.getElementById('tambah_panjang').value) || 0;
    const l = parseFloat(document.getElementById('tambah_lebar').value) || 0;
    const luas = p * l;
    document.getElementById('tambah_luas').value = luas > 0 ? luas.toFixed(2) : '';
}

// 3. Hitung Luas Otomatis Edit
function calculateLuasEdit() {
    const p = parseFloat(document.getElementById('edit_panjang').value) || 0;
    const l = parseFloat(document.getElementById('edit_lebar').value) || 0;
    const luas = p * l;
    document.getElementById('edit_luas').value = luas > 0 ? luas.toFixed(2) : '';
}

// 4. Hitung Luas Otomatis Bangunan
function calculateLuasBgn() {
    const p = parseFloat(document.getElementById('bgn_panjang').value) || 0;
    const l = parseFloat(document.getElementById('bgn_lebar').value) || 0;
    const luas = p * l;
    document.getElementById('bgn_luas').value = luas > 0 ? luas.toFixed(2) : '';
}

// 5. Format Rupiah Input
function formatRupiahInput(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if (val) {
        input.value = new Intl.NumberFormat('id-ID').format(val);
    } else {
        input.value = '';
    }
}

// 6. Preview Multiple File Uploads
function previewMultiFiles(input, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const badge = document.createElement('div');
            badge.className = 'px-3 py-1 rounded-xl bg-primary-100 text-primary-800 text-xs font-semibold flex items-center gap-1.5 border border-primary-200';
            badge.innerHTML = `
                <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                </svg>
                <span>${file.name}</span>
                <span class="text-[10px] text-primary-600">(${(file.size / 1024).toFixed(0)} KB)</span>
            `;
            container.appendChild(badge);
        });
    }
}

// 7. Modal Handlers
function openModalTambahTanah() {
    document.getElementById('modalTambahTanah').classList.remove('hidden');
}
function closeModalTambahTanah() {
    document.getElementById('modalTambahTanah').classList.add('hidden');
}

function openModalEditTanah(row) {
    document.getElementById('formEditTanah').action = "<?= url('kelola-sarpras/tanah/update/') ?>" + row.id;
    document.getElementById('edit_kode_label').textContent = `${row.kode_tanah} - Perbarui rincian aset`;
    document.getElementById('edit_nama_tanah').value = row.nama_tanah || '';
    document.getElementById('edit_no_sertifikat').value = row.no_sertifikat || '';
    document.getElementById('edit_status_kepemilikan').value = row.status_kepemilikan || 'SHM';
    document.getElementById('edit_panjang').value = row.panjang || '';
    document.getElementById('edit_lebar').value = row.lebar || '';
    document.getElementById('edit_luas').value = row.luas || '';
    document.getElementById('edit_tahun_perolehan').value = row.tahun_perolehan || '';
    document.getElementById('edit_harga_perolehan').value = row.harga_perolehan ? new Intl.NumberFormat('id-ID').format(row.harga_perolehan) : '';
    document.getElementById('edit_alamat_lokasi').value = row.alamat_lokasi || '';
    document.getElementById('edit_keterangan').value = row.keterangan || '';

    // Render existing images with checkboxes to retain
    const container = document.getElementById('existingImagesList');
    container.innerHTML = '';
    const images = row.gambar_sertifikat_list || [];
    if (images.length > 0) {
        images.forEach(img => {
            const item = document.createElement('div');
            item.className = 'relative group border border-primary-200 rounded-xl p-1 bg-primary-50 flex items-center gap-2';
            item.innerHTML = `
                <img src="<?= url('') ?>/${img}" class="w-10 h-10 object-cover rounded-lg border border-primary-200">
                <label class="text-[11px] font-semibold text-primary-800 flex items-center gap-1 cursor-pointer pr-2">
                    <input type="checkbox" name="retained_images[]" value="${img}" checked class="rounded text-primary-600 focus:ring-primary-500">
                    Pertahankan
                </label>
            `;
            container.appendChild(item);
        });
    } else {
        container.innerHTML = '<span class="text-xs text-slate-400 italic">Belum ada dokumen sertifikat terunggah</span>';
    }

    document.getElementById('modalEditTanah').classList.remove('hidden');
}
function closeModalEditTanah() {
    document.getElementById('modalEditTanah').classList.add('hidden');
}

function openModalTambahBangunan(tanahId, tanahNama) {
    document.getElementById('bgn_tanah_id').value = tanahId;
    document.getElementById('bgn_tanah_nama_label').textContent = tanahNama;
    document.getElementById('modalTambahBangunan').classList.remove('hidden');
}
function closeModalTambahBangunan() {
    document.getElementById('modalTambahBangunan').classList.add('hidden');
}

function openGalleryModal(images, title) {
    document.getElementById('galleryTitle').textContent = `Sertifikat: ${title}`;
    const content = document.getElementById('galleryContent');
    content.innerHTML = '';

    images.forEach(img => {
        const ext = img.split('.').pop().toLowerCase();
        const card = document.createElement('div');
        card.className = 'rounded-2xl border border-slate-200 overflow-hidden bg-slate-50 flex flex-col';

        if (ext === 'pdf') {
            card.innerHTML = `
                <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-rose-500 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="font-bold text-slate-700 text-sm">Dokumen PDF Sertifikat</p>
                </div>
                <a href="<?= url('') ?>/${img}" target="_blank" class="p-2.5 bg-slate-800 text-white text-xs font-semibold text-center hover:bg-slate-900 transition-colors">
                    Buka Dokumen PDF
                </a>
            `;
        } else {
            card.innerHTML = `
                <img src="<?= url('') ?>/${img}" class="w-full h-48 object-cover cursor-pointer hover:scale-105 transition-transform" onclick="window.open(this.src, '_blank')">
                <div class="p-2.5 bg-white border-t border-slate-200 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-mono">${img.split('/').pop()}</span>
                    <a href="<?= url('') ?>/${img}" target="_blank" class="text-xs text-primary-600 font-bold hover:underline">
                        Lihat Full
                    </a>
                </div>
            `;
        }
        content.appendChild(card);
    });

    document.getElementById('modalGallery').classList.remove('hidden');
}
function closeGalleryModal() {
    document.getElementById('modalGallery').classList.add('hidden');
}

// 8. ACCORDION HANDLERS: TANAH -> BANGUNAN -> RUANG
function toggleTanahRowAccordion(tanahId) {
    const row = document.getElementById('accordion-row-tanah-' + tanahId);
    const chevronTitle = document.getElementById('chevron-tanah-title-' + tanahId);
    const chevronBadge = document.getElementById('chevron-tanah-badge-' + tanahId);
    if (!row) return;

    if (row.classList.contains('hidden')) {
        row.classList.remove('hidden');
        if (chevronTitle) chevronTitle.classList.add('rotate-180');
        if (chevronBadge) chevronBadge.classList.add('rotate-180');
    } else {
        row.classList.add('hidden');
        if (chevronTitle) chevronTitle.classList.remove('rotate-180');
        if (chevronBadge) chevronBadge.classList.remove('rotate-180');
    }
}

function toggleTanahBangunanRooms(bangunanId, event) {
    if (event) event.stopPropagation();
    const container = document.getElementById('tanah-bgn-rooms-' + bangunanId);
    const chevron = document.getElementById('chevron-tanah-bgn-' + bangunanId);
    if (!container) return;

    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
    } else {
        container.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }
}

function calculateLuasRuangInline() {
    const p = parseFloat(document.getElementById('inline_ruang_panjang').value) || 0;
    const l = parseFloat(document.getElementById('inline_ruang_lebar').value) || 0;
    document.getElementById('inline_ruang_luas').value = (p * l > 0) ? (p * l).toFixed(2) : '';
}

function openModalTambahRuangInline(bangunanId, bangunanNama, maxLantai) {
    document.getElementById('inline_ruang_bgn_id').value = bangunanId;
    document.getElementById('inline_ruang_bgn_nama_label').textContent = bangunanNama;
    document.getElementById('inline_ruang_bgn_lantai_badge').textContent = (maxLantai || 1) + ' Lantai';

    const selectLantai = document.getElementById('inline_ruang_lantai_select');
    selectLantai.innerHTML = '';
    const totalLt = Math.max(1, parseInt(maxLantai) || 1);
    for (let i = 1; i <= totalLt; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = `Lantai ${i}`;
        selectLantai.appendChild(opt);
    }

    document.getElementById('inline_ruang_nama').value = '';
    document.getElementById('inline_ruang_kode').value = '';
    document.getElementById('inline_ruang_panjang').value = '';
    document.getElementById('inline_ruang_lebar').value = '';
    document.getElementById('inline_ruang_luas').value = '';
    document.getElementById('inline_ruang_jenis').value = 'Ruang Kelas';

    const pj = document.getElementById('inline_ruang_pj');
    if (pj) {
        if (window.SearchableSelect) {
            window.SearchableSelect.setValue(pj, '');
        } else {
            pj.value = '';
        }
    }

    document.getElementById('modalTambahRuangTanah').classList.remove('hidden');
}

function closeModalTambahRuangTanah() {
    document.getElementById('modalTambahRuangTanah').classList.add('hidden');
}
</script>
