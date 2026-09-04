<?php
/**
 * View Master Data Referensi Pembelajaran (Mata Pelajaran)
 * Mendukung Hapus Multiple (Centang) dan Hapus Semua
 */
?>

<div class="space-y-6">

    <!-- 1. Header Row (Judul di kiri, Tombol Aksi di sudut kanan atas) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg shadow-xs border border-indigo-100">
                    📖
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Referensi Pembelajaran</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola master mata pelajaran, kelompok kurikulum, dan pemetaan unit sekolah.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <?php if (!empty($mataPelajaran)): ?>
                <form method="POST" action="<?= url('pengaturan-sistem/master-pembelajaran/delete-all') ?>" onsubmit="AppNotif.confirm(event, this, 'Hapus Seluruh Mata Pelajaran', 'PERINGATAN: Yakin ingin menghapus seluruh data mata pelajaran? Data yang terhapus tidak dapat dikembalikan.');" class="inline">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="unit" value="<?= e($_GET['unit'] ?? '') ?>">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl text-xs sm:text-sm border border-rose-200 transition-all shadow-xs" title="Hapus semua mata pelajaran yang ada">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        <span>Hapus Semua</span>
                    </button>
                </form>
            <?php endif; ?>

            <button type="button" onclick="openModalTambah()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl text-xs sm:text-sm shadow-md shadow-primary-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>+ Tambah Mata Pelajaran</span>
            </button>
        </div>
    </div>

    <!-- 2. Filter Bar (Di bawahnya sebelah kiri) -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <!-- Unit Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                <?php
                $currentUnit = $_GET['unit'] ?? 'semua';
                $unitTabs = [
                    'semua' => 'Semua Unit',
                    'PAUD' => 'PAUD / TK',
                    'SD' => 'Unit SD',
                    'SMP' => 'Unit SMP',
                    'SMA' => 'Unit SMA'
                ];
                foreach ($unitTabs as $uKey => $uLabel):
                    $isActive = ($currentUnit === $uKey);
                ?>
                    <a href="<?= url('pengaturan-sistem/master-pembelajaran?unit=' . $uKey . (!empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '')) ?>" 
                       class="px-3 py-1.5 rounded-xl font-bold transition-all <?= $isActive ? 'bg-primary-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                        <?= $uLabel ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Form -->
            <form method="GET" action="<?= url('pengaturan-sistem/master-pembelajaran') ?>" class="flex items-center gap-2">
                <input type="hidden" name="unit" value="<?= e($currentUnit) ?>">
                <div class="relative w-56 sm:w-64">
                    <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Cari nama / kode mapel..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-colors">Cari</button>
                <?php if (!empty($_GET['search'])): ?>
                    <a href="<?= url('pengaturan-sistem/master-pembelajaran?unit=' . $currentUnit) ?>" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 3. Form Bulk Delete & Table Data Mata Pelajaran -->
    <form id="form-bulk-delete" method="POST" action="<?= url('pengaturan-sistem/master-pembelajaran/bulk-delete') ?>">
        <?= CSRF::field() ?>

        <!-- Floating / Sticky Bulk Action Bar (Muncul saat ada checkbox dicentang) -->
        <div id="bulk-action-bar" class="hidden mb-4 p-3 bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl shadow-xl flex items-center justify-between gap-4 animate-in fade-in slide-in-from-top-2 duration-200">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-indigo-500/30 border border-indigo-400/30 flex items-center justify-center font-bold text-sm text-indigo-300">
                    ✓
                </span>
                <div>
                    <p class="text-xs font-bold"><span id="selected-count" class="text-amber-400 font-extrabold text-sm">0</span> mata pelajaran dipilih</p>
                    <p class="text-[10px] text-slate-400">Centang baris yang ingin dihapus sekaligus</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="clearAllSelections()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Batal Pilih
                </button>
                <button type="button" onclick="submitBulkDelete()" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/30 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    <span>Hapus Terpilih</span>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-10">
                                <input type="checkbox" id="check-all" onclick="toggleCheckAll(this)" class="w-4 h-4 text-primary-600 bg-white border-slate-300 rounded focus:ring-primary-500 cursor-pointer" title="Centang Semua">
                            </th>
                            <th class="px-3 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5 w-28">Kode Mapel</th>
                            <th class="px-5 py-3.5">Nama Mata Pelajaran</th>
                            <th class="px-5 py-3.5">Unit / Jenjang</th>
                            <th class="px-5 py-3.5">Kelompok Kurikulum</th>
                            <th class="px-5 py-3.5 text-center w-24">Status</th>
                            <th class="px-5 py-3.5 text-right w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($mataPelajaran)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-300 text-xl mx-auto mb-2">
                                        📚
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Belum ada mata pelajaran</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Silakan klik "+ Tambah Mata Pelajaran" di atas untuk menambahkan data baru.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($mataPelajaran as $mp): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors row-item" data-id="<?= $mp['id'] ?>">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="ids[]" value="<?= $mp['id'] ?>" class="check-item w-4 h-4 text-primary-600 bg-white border-slate-300 rounded focus:ring-primary-500 cursor-pointer" onchange="updateBulkBar()">
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-slate-400"><?= $no++ ?></td>
                                    <td class="px-4 py-3 font-mono font-bold text-primary-700">
                                        <?= e($mp['kode_mapel'] ?: '-') ?>
                                    </td>
                                    <td class="px-5 py-3 font-bold text-slate-900">
                                        <?= e($mp['nama_mapel']) ?>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200/80">
                                            <?= e($mp['unit']) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700">
                                            <?= e($mp['kelompok']) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <form method="POST" action="<?= url('pengaturan-sistem/master-pembelajaran/toggle-aktif/' . $mp['id']) ?>" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold transition-all <?= $mp['is_active'] ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>" title="Klik untuk ubah status">
                                                <?= $mp['is_active'] ? '● Aktif' : '○ Nonaktif' ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" 
                                                    onclick='openModalEdit(<?= json_encode($mp) ?>)' 
                                                    class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" 
                                                    title="Edit Mapel">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                            </button>
                                            <form method="POST" action="<?= url('pengaturan-sistem/master-pembelajaran/delete/' . $mp['id']) ?>" onsubmit="AppNotif.confirm(event, this, 'Hapus Mata Pelajaran', 'Yakin ingin menghapus mata pelajaran ini?');" class="inline">
                                                <?= CSRF::field() ?>
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Mapel">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<!-- Modal Tambah / Edit Mata Pelajaran -->
<div id="modal-mapel" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 id="modal-title" class="text-base font-bold text-slate-800">Tambah Mata Pelajaran</h3>
            <button type="button" onclick="closeModalMapel()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">&times;</button>
        </div>

        <form id="form-mapel" method="POST" action="<?= url('pengaturan-sistem/master-pembelajaran/store') ?>">
            <?= CSRF::field() ?>
            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_mapel" id="input-nama-mapel" required placeholder="Contoh: Matematika, Bahasa Arab..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode Mapel</label>
                        <input type="text" name="kode_mapel" id="input-kode-mapel" placeholder="Misal: MTK-01" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Urutan Tampil</label>
                        <input type="number" name="urutan" id="input-urutan" value="1" min="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Unit / Jenjang <span class="text-rose-500">*</span></label>
                        <select name="unit" id="input-unit" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="Semua Unit">Semua Unit</option>
                            <option value="PAUD">PAUD / TK</option>
                            <option value="SD">Unit SD</option>
                            <option value="SMP">Unit SMP</option>
                            <option value="SMA">Unit SMA</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kelompok Kurikulum</label>
                        <select name="kelompok" id="input-kelompok" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="Umum">Umum / Nasional</option>
                            <option value="Keagamaan / PAI">Keagamaan / PAI</option>
                            <option value="Muatan Lokal">Muatan Lokal</option>
                            <option value="Peminatan MIPA">Peminatan MIPA</option>
                            <option value="Peminatan IPS">Peminatan IPS</option>
                            <option value="Pengembangan Diri">Pengembangan Diri / BK</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" id="input-is-active" value="1" checked class="w-4 h-4 text-primary-600 rounded">
                        <span class="font-bold text-slate-700">Mata Pelajaran Aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-5 border-t border-slate-100 mt-5">
                <button type="button" onclick="closeModalMapel()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-md shadow-primary-500/20 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCheckAll(source) {
    const checkboxes = document.querySelectorAll('.check-item');
    checkboxes.forEach(cb => {
        cb.checked = source.checked;
        const row = cb.closest('tr');
        if (row) {
            if (source.checked) row.classList.add('bg-indigo-50/50');
            else row.classList.remove('bg-indigo-50/50');
        }
    });
    updateBulkBar();
}

function updateBulkBar() {
    const checkedBoxes = document.querySelectorAll('.check-item:checked');
    const totalBoxes = document.querySelectorAll('.check-item');
    const bulkBar = document.getElementById('bulk-action-bar');
    const selectedCount = document.getElementById('selected-count');
    const checkAll = document.getElementById('check-all');

    if (checkAll && totalBoxes.length > 0) {
        checkAll.checked = (checkedBoxes.length === totalBoxes.length);
    }

    // Highlight checked rows
    totalBoxes.forEach(cb => {
        const row = cb.closest('tr');
        if (row) {
            if (cb.checked) row.classList.add('bg-indigo-50/50');
            else row.classList.remove('bg-indigo-50/50');
        }
    });

    if (checkedBoxes.length > 0) {
        selectedCount.textContent = checkedBoxes.length;
        bulkBar.classList.remove('hidden');
    } else {
        bulkBar.classList.add('hidden');
    }
}

function clearAllSelections() {
    const checkboxes = document.querySelectorAll('.check-item');
    checkboxes.forEach(cb => {
        cb.checked = false;
        const row = cb.closest('tr');
        if (row) row.classList.remove('bg-indigo-50/50');
    });
    const checkAll = document.getElementById('check-all');
    if (checkAll) checkAll.checked = false;
    updateBulkBar();
}

function submitBulkDelete() {
    const checkedBoxes = document.querySelectorAll('.check-item:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih setidaknya satu mata pelajaran untuk dihapus.');
        return;
    }

    const count = checkedBoxes.length;
    const form = document.getElementById('form-bulk-delete');

    if (window.AppNotif && window.AppNotif.confirm) {
        AppNotif.confirm(null, form, 'Hapus ' + count + ' Mata Pelajaran Terpilih', 'Apakah Anda yakin ingin menghapus ' + count + ' mata pelajaran yang dicentang?');
    } else if (confirm('Apakah Anda yakin ingin menghapus ' + count + ' mata pelajaran yang dipilih?')) {
        form.submit();
    }
}

function openModalTambah() {
    document.getElementById('modal-title').textContent = 'Tambah Mata Pelajaran Baru';
    document.getElementById('form-mapel').action = '<?= url("pengaturan-sistem/master-pembelajaran/store") ?>';
    document.getElementById('input-nama-mapel').value = '';
    document.getElementById('input-kode-mapel').value = '';
    document.getElementById('input-urutan').value = '1';
    document.getElementById('input-unit').value = '<?= $currentUnit !== "semua" ? e($currentUnit) : "Semua Unit" ?>';
    document.getElementById('input-kelompok').value = 'Umum';
    document.getElementById('input-is-active').checked = true;
    document.getElementById('modal-mapel').classList.remove('hidden');
}

function openModalEdit(mapel) {
    document.getElementById('modal-title').textContent = 'Edit Mata Pelajaran';
    document.getElementById('form-mapel').action = '<?= url("pengaturan-sistem/master-pembelajaran/update/") ?>' + mapel.id;
    document.getElementById('input-nama-mapel').value = mapel.nama_mapel || '';
    document.getElementById('input-kode-mapel').value = mapel.kode_mapel || '';
    document.getElementById('input-urutan').value = mapel.urutan || 0;
    document.getElementById('input-unit').value = mapel.unit || 'Semua Unit';
    document.getElementById('input-kelompok').value = mapel.kelompok || 'Umum';
    document.getElementById('input-is-active').checked = (mapel.is_active == 1);
    document.getElementById('modal-mapel').classList.remove('hidden');
}

function closeModalMapel() {
    document.getElementById('modal-mapel').classList.add('hidden');
}
</script>
