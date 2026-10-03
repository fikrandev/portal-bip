<?php
/**
 * Cetak Label Barcode & QR Code Sarpras
 * Portal BIP
 */
$selectedFormat = $_GET['format'] ?? 'barcode';
$selectedMode = $_GET['mode'] ?? 'per_unit';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-sarpras') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                    &larr; Dashboard Sarpras
                </a>
                <span class="text-slate-300">•</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-100 text-primary-800">
                    Labeling &amp; Barcoding
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2.5">
                <span class="text-2xl">🏷️</span>
                <span>Cetak Label Barcode &amp; QR Code</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Cetak label inventaris aset fisik per ruangan atau per item menggunakan Barcode 1D (Code 128) dan QR Code.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?= url('kelola-sarpras/barang') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <span>Lihat Data Inventaris</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-5 sm:p-6">
        <form action="<?= url('kelola-sarpras/cetak-label') ?>" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Dropdown Ruangan -->
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Pilih Ruangan / Lokasi</span>
                    </label>
                    <select name="ruangan_id" id="ruangan_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                        <option value="">-- Pilih Ruangan --</option>
                        <option value="all" <?= ($selectedRuangan === 'all') ? 'selected' : '' ?>>Semua Ruangan (Seluruh Aset)</option>
                        <?php foreach ($ruangan as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($selectedRuangan !== null && $selectedRuangan != 'all' && $r['id'] == $selectedRuangan) ? 'selected' : '' ?>>
                                <?= e($r['nama_ruangan']) ?> (<?= (int)($r['total_items'] ?? 0) ?> aset)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Live / Search Filter -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cari Nama / Kode Aset (Opsional)</span>
                    </label>
                    <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Misal: Meja, Laptop, A.001..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                </div>

                <!-- Submit Button -->
                <div class="md:col-span-3 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>Tampilkan Barang</span>
                    </button>
                    <?php if ($selectedRuangan || !empty($search)): ?>
                        <a href="<?= url('kelola-sarpras/cetak-label') ?>" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl text-xs font-bold transition-colors" title="Reset Filter">
                            ↺
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Format & Mode Selector -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="font-bold text-slate-600">Format Default Label:</span>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="format" value="barcode" <?= $selectedFormat === 'barcode' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                        <span class="font-semibold text-slate-700">Barcode 1D (Code 128)</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="format" value="qr" <?= $selectedFormat === 'qr' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                        <span class="font-semibold text-slate-700">QR Code 2D</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="format" value="both" <?= $selectedFormat === 'both' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                        <span class="font-semibold text-slate-700">Kombinasi (Barcode + QR)</span>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <span class="font-bold text-slate-600">Jumlah Salinan:</span>
                    <select name="mode" id="global_mode" class="px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700">
                        <option value="per_unit" <?= $selectedMode === 'per_unit' ? 'selected' : '' ?>>Per Satuan Unit (sebanyak Qty)</option>
                        <option value="per_item" <?= $selectedMode === 'per_item' ? 'selected' : '' ?>>1 Label per Master Barang</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <?php if ($selectedRuangan !== null || !empty($search)): ?>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
            <!-- Table Header Bar -->
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base flex items-center gap-2">
                        <span>Daftar Aset Siap Cetak</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-100 text-primary-700">
                            <?= count($items) ?> Item
                        </span>
                        <?php 
                            $totalQty = array_sum(array_column($items, 'jumlah'));
                            if ($totalQty > count($items)): 
                        ?>
                            <span class="text-xs font-normal text-slate-500">
                                (Total <?= number_format($totalQty) ?> unit fisik)
                            </span>
                        <?php endif; ?>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Centang barang yang ingin dicetak labelnya, atau klik "Cetak Semua" untuk mencetak sekaligus.
                    </p>
                </div>

                <?php if (count($items) > 0): ?>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="printSelected()" class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Cetak Yang Dipilih</span>
                            <span id="selectedCountBadge" class="hidden ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-primary-600 text-white font-bold">0</span>
                        </button>

                        <button type="button" onclick="printAll()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-500/20 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.18.91 2.164 2.09 2.201a51.964 51.964 0 0 0 6.32 0c1.18-.037 2.09-1.022 2.09-2.201V9.456Z" />
                            </svg>
                            <span>Cetak Semua Label</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4 w-10 text-center">
                                <input type="checkbox" id="checkAll" class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-slate-300 cursor-pointer">
                            </th>
                            <th class="py-3.5 px-4">Preview Barcode</th>
                            <th class="py-3.5 px-4">Kode &amp; Nama Barang</th>
                            <th class="py-3.5 px-4">Ruangan / Penempatan</th>
                            <th class="py-3.5 px-4">Kondisi</th>
                            <th class="py-3.5 px-4 text-center">Jumlah Unit</th>
                            <th class="py-3.5 px-4 text-center">Aksi Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                            📦
                                        </div>
                                        <p class="font-bold text-slate-600">Tidak ada barang inventaris pada pilihan ini.</p>
                                        <p class="text-xs text-slate-400 mt-1">Silakan pilih ruangan lain atau cari dengan kata kunci berbeda.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <?php 
                                    $itemKey = ($item['source_type'] === 'distribusi') 
                                        ? 'dist_' . $item['distribusi_id'] 
                                        : 'brg_' . $item['barang_id'];
                                ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4 text-center">
                                        <input type="checkbox" name="selected_ids[]" value="<?= $itemKey ?>" class="item-checkbox w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-slate-300 cursor-pointer" onchange="updateSelectedCount()">
                                    </td>
                                    <td class="py-3 px-4">
                                        <!-- Mini Barcode Visual -->
                                        <div class="inline-flex flex-col items-start bg-slate-50 border border-slate-200/80 px-2 py-1 rounded-lg">
                                            <svg class="barcode-preview h-6 max-w-[120px]" data-code="<?= e($item['kode_barang']) ?>"></svg>
                                            <span class="text-[9px] font-mono text-slate-500 font-bold tracking-wider leading-none mt-0.5">
                                                <?= e($item['kode_barang']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800"><?= e($item['nama_barang']) ?></div>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <?php if (!empty($item['merk'])): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                                    Merk: <?= e($item['merk']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['unit'])): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700">
                                                    Unit: <?= e($item['unit']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-700 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span><?= e($item['nama_ruangan']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $item['kondisi'] === 'Baik' ? 'bg-primary-50 text-primary-700 border-primary-200' : 'bg-amber-50 text-amber-700 border-amber-200' ?>">
                                            <?= e($item['kondisi']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-700">
                                        <span class="px-2.5 py-1 bg-slate-100 rounded-xl text-xs">
                                            <?= (int)$item['jumlah'] ?> Unit
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" onclick="printSingleItem('<?= $itemKey ?>', 'barcode')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-colors flex items-center gap-1" title="Cetak Label Barcode 1D">
                                                <span>🏷️ Barcode</span>
                                            </button>
                                            <button type="button" onclick="printSingleItem('<?= $itemKey ?>', 'qr')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-colors" title="Cetak Label QR Code">
                                                <span>📱 QR</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty State Prompt -->
        <div class="bg-white rounded-3xl p-10 text-center border border-slate-200/80 shadow-sm">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-primary-50 text-primary-600 flex items-center justify-center text-3xl mb-4 shadow-inner">
                🖨️
            </div>
            <h3 class="text-base sm:text-lg font-bold text-slate-800">Pilih Ruangan untuk Mencetak Label</h3>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1">
                Silakan pilih salah satu ruangan atau pilih "Semua Ruangan" pada filter di atas untuk melihat daftar barang dan mencetak label stiker barcode/QR.
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Local JsBarcode with CDN Fallback -->
<script src="<?= asset('js/jsbarcode.all.min.js') ?>"></script>
<script>
    if (typeof JsBarcode === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>');
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Render mini barcodes for previews
        if (typeof JsBarcode !== 'undefined') {
            document.querySelectorAll('.barcode-preview').forEach(el => {
                const code = el.getAttribute('data-code');
                if (code) {
                    try {
                        JsBarcode(el, code, {
                            format: "CODE128",
                            displayValue: false,
                            width: 1.2,
                            height: 24,
                            margin: 0
                        });
                    } catch (e) {
                        console.warn("Barcode error:", e);
                    }
                }
            });
        }

        // Check All listener
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        
        if (checkAll) {
            checkAll.addEventListener('change', (e) => {
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                updateSelectedCount();
            });
        }
    });

    function getSelectedFormat() {
        const checked = document.querySelector('input[name="format"]:checked');
        return checked ? checked.value : 'barcode';
    }

    function getSelectedMode() {
        const modeEl = document.getElementById('global_mode');
        return modeEl ? modeEl.value : 'per_unit';
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.item-checkbox:checked').length;
        const badge = document.getElementById('selectedCountBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    function printSelected() {
        const selected = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Silakan centang minimal satu barang untuk dicetak labelnya!');
            return;
        }
        const format = getSelectedFormat();
        const mode = getSelectedMode();
        const url = "<?= url('kelola-sarpras/cetak-label/print?ids=') ?>" + encodeURIComponent(selected.join(',')) + "&format=" + format + "&mode=" + mode;
        window.open(url, '_blank');
    }

    function printAll() {
        const ruanganId = "<?= e($selectedRuangan ?? '') ?>";
        const format = getSelectedFormat();
        const mode = getSelectedMode();
        let url = "<?= url('kelola-sarpras/cetak-label/print') ?>?format=" + format + "&mode=" + mode;
        if (ruanganId) {
            url += "&ruangan_id=" + encodeURIComponent(ruanganId);
        } else {
            // Collect all IDs on screen
            const allIds = Array.from(document.querySelectorAll('.item-checkbox')).map(cb => cb.value);
            if (allIds.length === 0) {
                alert('Tidak ada barang untuk dicetak.');
                return;
            }
            url += "&ids=" + encodeURIComponent(allIds.join(','));
        }
        window.open(url, '_blank');
    }

    function printSingleItem(itemKey, format) {
        const mode = getSelectedMode();
        const url = "<?= url('kelola-sarpras/cetak-label/print?ids=') ?>" + encodeURIComponent(itemKey) + "&format=" + format + "&mode=" + mode;
        window.open(url, '_blank');
    }
</script>
