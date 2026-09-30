<?php
/**
 * View Data Aset Bangunan & Gedung
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
                <span class="p-2 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-500/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 17.25 8.1L12 4.6 6.75 8.1A2.25 2.25 0 0 0 4.5 10.333V21h15Z" />
                    </svg>
                </span>
                Inventaris Gedung & Bangunan
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Daftar aset fisik gedung, lantai bertingkat, kondisi fisik, dan keterkaitannya dengan bidang tanah induk.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" 
                    onclick="openModalTambahBangunanDirect()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition-all shadow-md shadow-primary-500/20 hover:shadow-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Bangunan</span>
            </button>
        </div>
    </div>

    <!-- Filter & Live Search Bar -->
    <div class="p-4 bg-white rounded-3xl border border-primary-100 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <input type="text" 
                   id="searchBangunanInput" 
                   placeholder="Live Search nama gedung, kode, tanah..." 
                   onkeyup="handleSearchBangunan(this.value)"
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all text-slate-800">
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <select id="filterTanahSelect" 
                    onchange="filterBangunanByTanah(this.value)"
                    class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Semua Lokasi Tanah</option>
                <?php foreach ($tanahList as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= (!empty($filters['tanah_id']) && (int)$filters['tanah_id'] === $t['id']) ? 'selected' : '' ?>>
                        <?= e($t['nama_tanah']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <span id="badgeCountBangunan" class="text-xs font-bold text-primary-700 bg-primary-50 border border-primary-200 px-3 py-1.5 rounded-full">
                <?= count($items) ?> Bangunan
            </span>
        </div>
    </div>

    <!-- Tabel Data Bangunan -->
    <div class="bg-white rounded-3xl border border-primary-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="bangunanTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-4 w-12 text-center">No</th>
                        <th class="py-4 px-4">Nama Bangunan</th>
                        <th class="py-4 px-4">Tanah Induk (Lokasi)</th>
                        <th class="py-4 px-4 text-center">Lantai</th>
                        <th class="py-4 px-4">Dimensi & Luas</th>
                        <th class="py-4 px-4">Kondisi</th>
                        <th class="py-4 px-4">Tahun / Nilai</th>
                        <th class="py-4 px-4 text-center">Ruangan</th>
                        <th class="py-4 px-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <p class="font-semibold text-slate-600">Belum ada data gedung/bangunan</p>
                                    <p class="text-xs text-slate-400">Silakan tambahkan bangunan langsung atau dari tabel data tanah.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $index => $row): 
                            $kondisiClass = match($row['kondisi_bangunan'] ?? '') {
                                'Baik' => 'bg-primary-50 text-primary-700 border-primary-200',
                                'Rusak Ringan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'Rusak Berat' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                            };
                        ?>
                            <tr class="hover:bg-primary-50/40 transition-colors bgn-row"
                                data-search="<?= strtolower(e($row['nama_bangunan'] . ' ' . $row['kode_bangunan'] . ' ' . ($row['nama_tanah'] ?? ''))) ?>"
                                data-tanah="<?= $row['tanah_id'] ?>">
                                <td class="py-4 px-4 text-center font-bold text-slate-400 bgn-number">
                                    <?= $index + 1 ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs shrink-0 border border-amber-200">
                                            <?= e($row['kode_bangunan']) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <button type="button" 
                                                    onclick="toggleBangunanRowAccordion(<?= $row['id'] ?>)"
                                                    class="text-left font-bold text-slate-800 hover:text-primary-600 transition-colors leading-snug cursor-pointer flex items-center gap-1.5 group">
                                                <span><?= e($row['nama_bangunan']) ?></span>
                                                <svg id="chevron-bgn-title-<?= $row['id'] ?>" class="w-3.5 h-3.5 text-primary-500 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                            <p class="text-xs text-slate-400"><?= e($row['sumber_dana'] ? 'Dana: ' . $row['sumber_dana'] : '') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <a href="<?= url('kelola-sarpras/tanah') ?>" class="text-xs font-semibold text-primary-700 hover:underline">
                                        <?= e($row['nama_tanah'] ?? 'Tanpa Tanah Induk') ?>
                                    </a>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-700">
                                    <?= (int)$row['jumlah_lantai'] ?> Lt
                                </td>
                                <td class="py-4 px-4 text-xs whitespace-nowrap">
                                    <div class="font-bold text-slate-800"><?= number_format($row['luas_bangunan'], 0, ',', '.') ?> m²</div>
                                    <div class="text-[11px] text-slate-400"><?= number_format($row['panjang'], 1, ',', '.') ?> &times; <?= number_format($row['lebar'], 1, ',', '.') ?> m</div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-bold border <?= $kondisiClass ?>">
                                        <?= e($row['kondisi_bangunan']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-xs">
                                    <div class="font-semibold text-slate-700">Thn <?= e($row['tahun_dibangun'] ?: '-') ?></div>
                                    <div class="text-[11px] text-primary-600 font-medium">Masa: <?= (int)($row['masa_manfaat'] ?? 20) ?> Thn</div>
                                    <div class="text-[11px] text-slate-400">Rp <?= number_format($row['biaya_pembangunan'], 0, ',', '.') ?></div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <button type="button" 
                                            onclick="toggleBangunanRowAccordion(<?= $row['id'] ?>)"
                                            class="px-2.5 py-1 rounded-full text-xs font-bold <?= (int)$row['total_ruangan'] > 0 ? 'bg-primary-50 text-primary-700 border border-primary-200 hover:bg-primary-100' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' ?> transition-all inline-flex items-center gap-1.5 shadow-sm cursor-pointer"
                                            title="Klik untuk membuka/menutup accordion daftar ruangan">
                                        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                        </svg>
                                        <span><?= (int)$row['total_ruangan'] ?> Ruang</span>
                                        <svg id="chevron-bgn-badge-<?= $row['id'] ?>" class="w-3.5 h-3.5 text-primary-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol Tambah Ruang di Gedung Ini -->
                                        <button type="button" 
                                                onclick='openModalTambahRuang(<?= (int)$row['id'] ?>, <?= htmlspecialchars(json_encode($row['nama_bangunan']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$row['jumlah_lantai'] ?>)'
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold border border-primary-200 transition-colors shadow-sm"
                                                title="Tambah Ruang di Gedung ini">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <span>+ Ruang</span>
                                        </button>

                                        <!-- Tombol Edit -->
                                        <button type="button" 
                                                onclick='openModalEditBangunan(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)'
                                                class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors"
                                                title="Edit Bangunan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form action="<?= url('kelola-sarpras/bangunan/delete/' . $row['id']) ?>" 
                                              method="POST" 
                                              class="inline" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus bangunan <?= htmlspecialchars($row['nama_bangunan'], ENT_QUOTES) ?>?');">
                                            <?= CSRF::field() ?>
                                            <button type="submit" 
                                                    class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors"
                                                    title="Hapus Bangunan">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- ACCORDION ROW: RUANGAN DI GEDUNG INI -->
                            <tr id="accordion-row-bgn-<?= $row['id'] ?>" class="accordion-bgn-subrow hidden bg-slate-50/70 border-b border-primary-200/80">
                                <td colspan="9" class="p-4 sm:p-5">
                                    <div class="bg-white rounded-2xl border border-primary-200/90 p-4 sm:p-5 shadow-xs space-y-4">
                                        <!-- Header Sub-panel Ruangan -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-8 h-8 rounded-xl bg-primary-50 border border-primary-200 text-primary-700 flex items-center justify-center font-bold">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                        <span>Daftar Ruangan di Gedung:</span>
                                                        <span class="text-primary-700 font-black"><?= e($row['nama_bangunan']) ?></span>
                                                        <span class="text-slate-400 font-normal text-xs">(<?= (int)$row['jumlah_lantai'] ?> Lantai)</span>
                                                    </h4>
                                                    <p class="text-[11px] text-slate-400">
                                                        Unit ruangan, laboratorium, kantor, dan fasilitas pembelajaran di gedung ini
                                                    </p>
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    onclick='openModalTambahRuang(<?= (int)$row['id'] ?>, <?= htmlspecialchars(json_encode($row['nama_bangunan']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$row['jumlah_lantai'] ?>)'
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-xs transition-all self-start sm:self-auto">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                <span>Tambah Ruang di Gedung Ini</span>
                                            </button>
                                        </div>

                                        <!-- Content Ruangan -->
                                        <?php 
                                            $rList = $ruanganByBangunan[$row['id']] ?? [];
                                        ?>
                                        <?php if (empty($rList)): ?>
                                            <div class="py-8 text-center bg-slate-50/50 rounded-xl border border-dashed border-slate-200 text-slate-400 space-y-2">
                                                <p class="text-xs font-semibold">Belum ada unit ruangan yang terdaftar di gedung ini.</p>
                                                <button type="button" 
                                                        onclick='openModalTambahRuang(<?= (int)$row['id'] ?>, <?= htmlspecialchars(json_encode($row['nama_bangunan']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$row['jumlah_lantai'] ?>)'
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-2xs">
                                                    Tambah Ruang Pertama
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                <?php foreach ($rList as $r): 
                                                    $rLuasText = ($r['luas'] > 0) ? number_format($r['luas'], 1, ',', '.') . ' m²' : '-';
                                                    $rDimText = ($r['panjang'] > 0 && $r['lebar'] > 0) ? number_format($r['panjang'], 1, ',', '.') . ' &times; ' . number_format($r['lebar'], 1, ',', '.') . ' m' : '';
                                                ?>
                                                    <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200 hover:border-primary-300 transition-all flex flex-col justify-between shadow-2xs">
                                                        <div>
                                                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                                                    <?= e($r['jenis_ruangan'] ?? 'Ruang Kelas') ?>
                                                                </span>
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-slate-600 border border-slate-200 shadow-2xs">
                                                                    Lantai <?= (int)($r['lantai'] ?? 1) ?>
                                                                </span>
                                                            </div>
                                                            <div class="font-bold text-slate-800 text-sm">
                                                                <?= e($r['nama_ruangan']) ?>
                                                            </div>
                                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                                <?= e($r['kode_ruangan']) ?> &bull; Unit: <?= e($r['unit'] ?? 'Semua') ?>
                                                            </div>
                                                            <div class="mt-2.5 pt-2 border-t border-slate-200/80 text-xs text-slate-600 space-y-1">
                                                                <div class="flex justify-between">
                                                                    <span class="text-slate-400">Luas:</span>
                                                                    <span class="font-bold text-slate-700"><?= $rLuasText ?> <?= $rDimText ? "($rDimText)" : "" ?></span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="text-slate-400">Kapasitas:</span>
                                                                    <span class="font-semibold text-slate-700"><?= (int)($r['kapasitas'] ?? 0) ?> Orang</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="text-slate-400">PJ:</span>
                                                                    <span class="font-semibold text-primary-800 truncate max-w-[140px]"><?= e($r['penanggung_jawab'] ?: '-') ?></span>
                                                                </div>
                                                            </div>
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

<!-- Modal Tambah Bangunan Direct -->
<div id="modalTambahBangunanDirect" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
    <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Header (Fixed at top) -->
        <div class="px-6 py-4 border-b border-primary-100 bg-gradient-to-r from-amber-50 to-primary-50 flex items-center justify-between shrink-0">
            <h3 class="font-bold text-slate-800 text-lg">Tambah Gedung / Bangunan</h3>
            <button type="button" onclick="closeModalTambahBangunanDirect()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="<?= url('kelola-sarpras/bangunan/store') ?>" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_to" value="<?= url('kelola-sarpras/bangunan') ?>">

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bidang Tanah Lokasi <span class="text-rose-500">*</span></label>
                    <select name="tanah_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Pilih Bidang Tanah --</option>
                        <?php foreach ($tanahList as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['nama_tanah']) ?> (<?= e($t['kode_tanah']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Bangunan / Gedung <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_bangunan" required placeholder="Contoh: Gedung Pembelajaran Terpadu A" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jumlah Lantai</label>
                        <input type="number" name="jumlah_lantai" min="1" value="1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kondisi Bangunan</label>
                        <select name="kondisi_bangunan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (m)</label>
                        <input type="number" step="0.01" id="direct_panjang" name="panjang" oninput="calcDirectLuas()" class="w-full px-3 py-2 bg-white border rounded-xl text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (m)</label>
                        <input type="number" step="0.01" id="direct_lebar" name="lebar" oninput="calcDirectLuas()" class="w-full px-3 py-2 bg-white border rounded-xl text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-primary-800 mb-1">Luas (m²)</label>
                        <input type="number" step="0.01" id="direct_luas" name="luas_bangunan" class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-xl text-sm font-black text-primary-900">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Dibangun</label>
                        <input type="number" name="tahun_dibangun" value="<?= date('Y') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Masa Manfaat (Tahun)</label>
                        <input type="number" name="masa_manfaat" value="20" min="1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sumber Dana</label>
                        <select name="sumber_dana" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                            <option value="Yayasan" selected>Yayasan</option>
                            <option value="BOS">BOS</option>
                            <option value="Pemerintah / DAK">Pemerintah / DAK</option>
                            <option value="Hibah / CSR">Hibah / CSR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga Perolehan (Rp)</label>
                        <input type="text" name="biaya_pembangunan" placeholder="0" onkeyup="formatRupiahBgn(this)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>
            </div>

            <!-- Sticky Footer (Always visible) -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="closeModalTambahBangunanDirect()" class="px-5 py-2.5 rounded-2xl border text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-primary-600 text-white font-bold text-sm shadow-md shadow-primary-500/20 hover:bg-primary-700 transition-all">Simpan Bangunan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Bangunan -->
<div id="modalEditBangunan" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
    <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Header (Fixed at top) -->
        <div class="px-6 py-4 border-b border-primary-100 bg-gradient-to-r from-amber-50 to-primary-50 flex items-center justify-between shrink-0">
            <h3 class="font-bold text-slate-800 text-lg">Edit Gedung / Bangunan</h3>
            <button type="button" onclick="closeModalEditBangunan()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="formEditBangunan" action="" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bidang Tanah Lokasi <span class="text-rose-500">*</span></label>
                    <select id="edit_bgn_tanah_id" name="tanah_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                        <?php foreach ($tanahList as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['nama_tanah']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Bangunan / Gedung <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit_bgn_nama" name="nama_bangunan" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jumlah Lantai</label>
                        <input type="number" id="edit_bgn_lantai" name="jumlah_lantai" min="1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kondisi Bangunan</label>
                        <select id="edit_bgn_kondisi" name="kondisi_bangunan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (m)</label>
                        <input type="number" step="0.01" id="edit_bgn_panjang" name="panjang" oninput="calcEditLuasBgn()" class="w-full px-3 py-2 bg-white border rounded-xl text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (m)</label>
                        <input type="number" step="0.01" id="edit_bgn_lebar" name="lebar" oninput="calcEditLuasBgn()" class="w-full px-3 py-2 bg-white border rounded-xl text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-primary-800 mb-1">Luas (m²)</label>
                        <input type="number" step="0.01" id="edit_bgn_luas" name="luas_bangunan" class="w-full px-3 py-2 bg-primary-50 border border-primary-300 rounded-xl text-sm font-black text-primary-900">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Dibangun</label>
                        <input type="number" id="edit_bgn_tahun" name="tahun_dibangun" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Masa Manfaat (Tahun)</label>
                        <input type="number" id="edit_bgn_masa_manfaat" name="masa_manfaat" min="1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sumber Dana</label>
                        <select id="edit_bgn_sumber" name="sumber_dana" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                            <option value="Yayasan">Yayasan</option>
                            <option value="BOS">BOS</option>
                            <option value="Pemerintah / DAK">Pemerintah / DAK</option>
                            <option value="Hibah / CSR">Hibah / CSR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga Perolehan (Rp)</label>
                        <input type="text" id="edit_bgn_biaya" name="biaya_pembangunan" onkeyup="formatRupiahBgn(this)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="closeModalEditBangunan()" class="px-5 py-2.5 rounded-2xl border text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-primary-600 text-white font-bold text-sm shadow-md shadow-primary-500/20 hover:bg-primary-700 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleSearchBangunan(query) {
    const q = query.toLowerCase().trim();
    const rows = document.querySelectorAll('.bgn-row');
    const selectedTanah = document.getElementById('filterTanahSelect').value;
    let count = 0;

    rows.forEach(r => {
        const text = r.getAttribute('data-search') || '';
        const tanah = r.getAttribute('data-tanah') || '';

        const matchSearch = text.includes(q);
        const matchTanah = !selectedTanah || tanah === selectedTanah;

        if (matchSearch && matchTanah) {
            r.style.display = '';
            count++;
            r.querySelector('.bgn-number').textContent = count;
        } else {
            r.style.display = 'none';
        }
    });

    document.getElementById('badgeCountBangunan').textContent = `${count} Bangunan`;
}

function filterBangunanByTanah(tanahId) {
    const q = document.getElementById('searchBangunanInput').value;
    handleSearchBangunan(q);
}

function calcDirectLuas() {
    const p = parseFloat(document.getElementById('direct_panjang').value) || 0;
    const l = parseFloat(document.getElementById('direct_lebar').value) || 0;
    document.getElementById('direct_luas').value = (p * l > 0) ? (p * l).toFixed(2) : '';
}

function calcEditLuasBgn() {
    const p = parseFloat(document.getElementById('edit_bgn_panjang').value) || 0;
    const l = parseFloat(document.getElementById('edit_bgn_lebar').value) || 0;
    document.getElementById('edit_bgn_luas').value = (p * l > 0) ? (p * l).toFixed(2) : '';
}

function formatRupiahBgn(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    input.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
}

function openModalTambahBangunanDirect() {
    document.getElementById('modalTambahBangunanDirect').classList.remove('hidden');
}
function closeModalTambahBangunanDirect() {
    document.getElementById('modalTambahBangunanDirect').classList.add('hidden');
}

function openModalEditBangunan(row) {
    document.getElementById('formEditBangunan').action = "<?= url('kelola-sarpras/bangunan/update/') ?>" + row.id;
    document.getElementById('edit_bgn_tanah_id').value = row.tanah_id || '';
    document.getElementById('edit_bgn_nama').value = row.nama_bangunan || '';
    document.getElementById('edit_bgn_lantai').value = row.jumlah_lantai || 1;
    document.getElementById('edit_bgn_kondisi').value = row.kondisi_bangunan || 'Baik';
    document.getElementById('edit_bgn_panjang').value = row.panjang || '';
    document.getElementById('edit_bgn_lebar').value = row.lebar || '';
    document.getElementById('edit_bgn_luas').value = row.luas_bangunan || '';
    document.getElementById('edit_bgn_tahun').value = row.tahun_dibangun || '';
    document.getElementById('edit_bgn_masa_manfaat').value = row.masa_manfaat || 20;
    document.getElementById('edit_bgn_sumber').value = row.sumber_dana || 'Yayasan';
    document.getElementById('edit_bgn_biaya').value = row.biaya_pembangunan ? new Intl.NumberFormat('id-ID').format(row.biaya_pembangunan) : '';

    document.getElementById('modalEditBangunan').classList.remove('hidden');
}
function closeModalEditBangunan() {
    document.getElementById('modalEditBangunan').classList.add('hidden');
}

// Accordion toggle untuk baris Gedung -> Ruangan
function toggleBangunanRowAccordion(bangunanId) {
    const row = document.getElementById('accordion-row-bgn-' + bangunanId);
    const chevronTitle = document.getElementById('chevron-bgn-title-' + bangunanId);
    const chevronBadge = document.getElementById('chevron-bgn-badge-' + bangunanId);
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
</script>

<!-- Modal Tambah Ruang ke Bangunan -->
<div id="modalTambahRuang" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
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
            <button type="button" onclick="closeModalTambahRuang()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Scrollable -->
        <form action="<?= url('kelola-sarpras/ruangan/store') ?>" method="POST" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>
            <input type="hidden" name="bangunan_id" id="ruang_bgn_id">
            <input type="hidden" name="return_to" value="<?= url('kelola-sarpras/bangunan') ?>">

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
                            <p class="text-sm font-black text-slate-800" id="ruang_bgn_nama_label">-</p>
                        </div>
                    </div>
                    <span id="ruang_bgn_lantai_badge" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-white text-primary-700 border border-primary-200 shrink-0">- Lt</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Ruangan / Fasilitas <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama_ruangan" 
                               id="ruang_nama_input"
                               required 
                               placeholder="Contoh: Ruang Kelas 1A, Lab Komputer, Perpustakaan" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Jenis Ruangan <span class="text-rose-500">*</span>
                        </label>
                        <select name="jenis_ruangan" id="ruang_jenis_select" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
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
                        <select name="lantai" id="ruang_lantai_select" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            <option value="1">Lantai 1</option>
                        </select>
                    </div>

                    <!-- Dimensi Ruangan: Panjang, Lebar, Luas -->
                    <div class="sm:col-span-2 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (m)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="ruang_panjang" 
                                   name="panjang" 
                                   placeholder="0.00" 
                                   oninput="calculateLuasRuang()"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (m)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="ruang_lebar" 
                                   name="lebar" 
                                   placeholder="0.00" 
                                   oninput="calculateLuasRuang()"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-primary-800 mb-1">Luas Ruang (m²)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="ruang_luas" 
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
                               id="ruang_kode_input"
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
                                id="ruang_pj_select" 
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
                               placeholder="Contoh: Dilengkapi AC dan Smart TV untuk pembelajaran" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                    </div>
                </div>
            </div>

            <!-- Sticky Footer Tombol Simpan -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" 
                        onclick="closeModalTambahRuang()" 
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

<!-- Modal Daftar Ruangan di Gedung Ini -->
<div id="modalListRuangan" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-4 flex items-center justify-center">
    <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-primary-100 bg-primary-50 to-primary-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg" id="list_ruang_title">Daftar Ruangan Gedung</h3>
                    <p class="text-xs text-slate-500" id="list_ruang_subtitle">Daftar seluruh ruangan dan fasilitas yang terdaftar</p>
                </div>
            </div>
            <button type="button" onclick="closeModalListRuangan()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar" id="list_ruang_container">
            <!-- Populated via JS -->
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-between shrink-0">
            <button type="button" 
                    id="btn_tambah_dari_list"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Ruang di Gedung Ini</span>
            </button>
            <button type="button" 
                    onclick="closeModalListRuangan()" 
                    class="px-5 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
const ruanganByBangunan = <?= json_encode($ruanganByBangunan ?? []) ?>;
let currentBangunanForRuang = null;

function calculateLuasRuang() {
    const p = parseFloat(document.getElementById('ruang_panjang').value) || 0;
    const l = parseFloat(document.getElementById('ruang_lebar').value) || 0;
    document.getElementById('ruang_luas').value = (p * l > 0) ? (p * l).toFixed(2) : '';
}

function openModalTambahRuang(bangunanId, namaBangunan, totalLantai) {
    currentBangunanForRuang = { id: bangunanId, nama: namaBangunan, lantai: totalLantai };
    document.getElementById('ruang_bgn_id').value = bangunanId;
    document.getElementById('ruang_bgn_nama_label').textContent = namaBangunan;
    document.getElementById('ruang_bgn_lantai_badge').textContent = (totalLantai || 1) + ' Lantai';

    // Populate lantai dropdown
    const selectLantai = document.getElementById('ruang_lantai_select');
    selectLantai.innerHTML = '';
    const maxLt = Math.max(1, parseInt(totalLantai) || 1);
    for (let i = 1; i <= maxLt; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = `Lantai ${i}`;
        selectLantai.appendChild(opt);
    }

    document.getElementById('ruang_nama_input').value = '';
    document.getElementById('ruang_kode_input').value = '';
    document.getElementById('ruang_panjang').value = '';
    document.getElementById('ruang_lebar').value = '';
    document.getElementById('ruang_luas').value = '';
    document.getElementById('ruang_jenis_select').value = 'Ruang Kelas';

    const pj = document.getElementById('ruang_pj_select');
    if (pj) {
        if (window.SearchableSelect) {
            window.SearchableSelect.setValue(pj, '');
        } else {
            pj.value = '';
        }
    }

    document.getElementById('modalTambahRuang').classList.remove('hidden');
}

function closeModalTambahRuang() {
    document.getElementById('modalTambahRuang').classList.add('hidden');
}

function openModalListRuangan(bangunanId, namaBangunan, totalLantai) {
    currentBangunanForRuang = { id: bangunanId, nama: namaBangunan, lantai: totalLantai };
    document.getElementById('list_ruang_title').textContent = `Daftar Ruangan - ${namaBangunan}`;
    document.getElementById('list_ruang_subtitle').textContent = `Gedung ${namaBangunan} (${totalLantai || 1} Lantai)`;

    const container = document.getElementById('list_ruang_container');
    const rooms = ruanganByBangunan[bangunanId] || [];

    if (rooms.length === 0) {
        container.innerHTML = `
            <div class="py-12 text-center text-slate-400 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold border border-primary-200">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-slate-700 text-sm">Belum Ada Ruangan Terdaftar</p>
                    <p class="text-xs text-slate-400 mt-1">Gedung ini belum memiliki unit ruang/fasilitas yang tercatat.</p>
                </div>
            </div>
        `;
    } else {
        let html = `
            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-bold tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">No</th>
                            <th class="py-3 px-3">Nama &amp; Jenis Ruangan</th>
                            <th class="py-3 px-3 text-center">Lantai</th>
                            <th class="py-3 px-3">Dimensi &amp; Luas</th>
                            <th class="py-3 px-3">Unit</th>
                            <th class="py-3 px-3 text-center">Kapasitas</th>
                            <th class="py-3 px-3">Penanggung Jawab</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
        `;
        rooms.forEach((r, idx) => {
            const luasText = (parseFloat(r.luas) > 0) ? `${parseFloat(r.luas).toFixed(1)} m²` : '-';
            const dimText = (parseFloat(r.panjang) > 0 && parseFloat(r.lebar) > 0) ? `${parseFloat(r.panjang).toFixed(1)} &times; ${parseFloat(r.lebar).toFixed(1)} m` : '';

            html += `
                <tr class="hover:bg-primary-50/30 transition-colors">
                    <td class="py-2.5 px-3 text-center font-bold text-slate-400">${idx + 1}</td>
                    <td class="py-2.5 px-3">
                        <p class="font-bold text-slate-800">${escapeHtml(r.nama_ruangan)}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">${escapeHtml(r.jenis_ruangan || 'Ruang')}</span>
                            <span class="text-[10px] font-mono text-slate-400">${escapeHtml(r.kode_ruangan || '-')}</span>
                        </div>
                    </td>
                    <td class="py-2.5 px-3 text-center font-bold text-slate-700">Lt. ${r.lantai || 1}</td>
                    <td class="py-2.5 px-3">
                        <div class="font-bold text-slate-800">${luasText}</div>
                        ${dimText ? `<div class="text-[10px] text-slate-400">${dimText}</div>` : ''}
                    </td>
                    <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">${escapeHtml(r.unit || 'Umum')}</span></td>
                    <td class="py-2.5 px-3 text-center font-bold text-slate-700">${r.kapasitas ? r.kapasitas + ' Org' : '-'}</td>
                    <td class="py-2.5 px-3 text-slate-600">${escapeHtml(r.penanggung_jawab || '-')}</td>
                </tr>
            `;
        });
        html += `
                    </tbody>
                </table>
            </div>
        `;
        container.innerHTML = html;
    }

    document.getElementById('btn_tambah_dari_list').onclick = function() {
        closeModalListRuangan();
        openModalTambahRuang(bangunanId, namaBangunan, totalLantai);
    };

    document.getElementById('modalListRuangan').classList.remove('hidden');
}

function closeModalListRuangan() {
    document.getElementById('modalListRuangan').classList.add('hidden');
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}
</script>
