<?php include BASE_PATH . '/templates/header.php'; ?>
<?php include BASE_PATH . '/modules/kelola-sarpras/views/sidebar.php'; ?>

<main class="lg:ml-72 min-h-screen bg-slate-50 flex flex-col transition-all duration-300" id="main-content">
    <?php include BASE_PATH . '/templates/topbar.php'; ?>
    
    <div class="flex-1 p-4 lg:p-8">
        <div class="max-w-5xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                        <span class="text-3xl">🖨️</span> Cetak Label Ruangan
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">Pilih ruangan untuk mencetak label QR Code barang yang ada di dalamnya.</p>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl flex items-center gap-3">
                    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-5">
                <form action="<?= url('kelola-sarpras/cetak-label') ?>" method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Ruangan</label>
                        <select name="ruangan_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:border-primary-500">
                            <option value="">-- Pilih Ruangan --</option>
                            <?php foreach ($ruangan as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($r['id'] == $selectedRuangan) ? 'selected' : '' ?>>
                                    <?= e($r['nama_ruangan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl transition-colors">
                        Tampilkan Barang
                    </button>
                </form>
            </div>

            <?php if ($selectedRuangan): ?>
            <!-- Results Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Daftar Barang (<?= count($items) ?> Item)</h3>
                    <?php if (count($items) > 0): ?>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="printSelected()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors">
                            Cetak Yang Dipilih
                        </button>
                        <a href="<?= url('kelola-sarpras/cetak-label/print?ruangan_id=' . $selectedRuangan) ?>" target="_blank" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-md shadow-primary-500/20 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.18.91 2.164 2.09 2.201a51.964 51.964 0 0 0 6.32 0c1.18-.037 2.09-1.022 2.09-2.201V9.456Z" /></svg>
                            Cetak Semua
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4 w-10 text-center">
                                    <input type="checkbox" id="checkAll" class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-slate-300">
                                </th>
                                <th class="py-3 px-4">Kode / Nama Barang</th>
                                <th class="py-3 px-4">Kondisi</th>
                                <th class="py-3 px-4 text-center">Jml</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada barang di ruangan ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-3 px-4 text-center">
                                            <input type="checkbox" name="selected_ids[]" value="<?= $item['id'] ?>" class="item-checkbox w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-slate-300">
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-800"><?= e($item['nama_barang']) ?></div>
                                            <div class="text-[10px] text-slate-500 mt-0.5 font-mono"><?= e($item['kode_barang']) ?></div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold border <?= $item['kondisi'] === 'Baik' ? 'bg-primary-50 text-primary-700 border-primary-200' : 'bg-amber-50 text-amber-700 border-amber-200' ?>">
                                                <?= e($item['kondisi']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-slate-700">
                                            <?= $item['jumlah'] ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="<?= url('kelola-sarpras/cetak-label/print?ids=' . $item['id']) ?>" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-colors">
                                                Cetak
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        
        if (checkAll) {
            checkAll.addEventListener('change', (e) => {
                checkboxes.forEach(cb => cb.checked = e.target.checked);
            });
        }
    });

    function printSelected() {
        const selected = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Pilih minimal satu barang untuk dicetak!');
            return;
        }
        window.open("<?= url('kelola-sarpras/cetak-label/print?ids=') ?>" + selected.join(','), '_blank');
    }
</script>

<?php include BASE_PATH . '/templates/footer.php'; ?>
