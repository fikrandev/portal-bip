<?php
/**
 * Master Kategori Sarpras
 * Portal BIP
 */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-sarpras') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                    &larr; Dashboard Sarpras
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2.5">
                <span>Master Kategori Aset Sarpras</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pengelompokan jenis sarana prasarana sekolah untuk penomoran kode aset dan rekapitulasi inventaris
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button onclick="openModalKategori()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kategori Baru</span>
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4 w-28">Kode</th>
                        <th class="py-3.5 px-4">Nama Kategori</th>
                        <th class="py-3.5 px-4">Deskripsi / Contoh Barang</th>
                        <th class="py-3.5 px-4 text-center">Aset Terdaftar</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada data kategori.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($items as $k): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4 font-mono font-bold text-primary-700">
                                    <?= e($k['kode_kategori']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800 text-sm">
                                    <?= e($k['nama_kategori']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-md">
                                    <?= e($k['deskripsi'] ?? '-') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-primary-50 text-primary-700">
                                        <?= $k['total_barang'] ?> Aset (<?= $k['total_qty'] ?> Unit)
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="editKategori(<?= htmlspecialchars(json_encode($k)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50" title="Edit Kategori">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                        </button>
                                        <form action="<?= url('kelola-sarpras/kategori/delete/' . $k['id']) ?>" method="POST" onsubmit="return ModalHelper.confirm(event, this, 'Hapus Kategori', 'Apakah Anda yakin ingin menghapus kategori <?= htmlspecialchars($k['nama_kategori'], ENT_QUOTES) ?>?', 'danger');">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
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
</div>

<!-- Modal Form Kategori -->
<div id="modal-kategori" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden transform scale-95 transition-all duration-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800" id="modal-kategori-title">Tambah Kategori Baru</h3>
            <button onclick="closeModalKategori()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="<?= url('kelola-sarpras/kategori/store') ?>" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="kategori-id" value="">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_kategori" id="kategori-nama" required placeholder="Contoh: Elektronik &amp; IT, Meubeler..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Kode Kategori (3 Huruf)</label>
                <input type="text" name="kode_kategori" id="kategori-kode" maxlength="6" placeholder="Contoh: ELK, MBL, LAB..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-mono uppercase focus:bg-white focus:border-primary-500 outline-none">
                <p class="text-[10px] text-slate-400 mt-0.5">Digunakan sebagai awalan penomoran kode barang.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi &amp; Contoh Barang</label>
                <textarea name="deskripsi" id="kategori-deskripsi" rows="2" placeholder="Contoh: Laptop, Proyektor, Speaker..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none"></textarea>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeModalKategori()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold shadow-md">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalKategori() {
    document.getElementById('modal-kategori-title').innerText = 'Tambah Kategori Baru';
    document.getElementById('kategori-id').value = '';
    document.getElementById('kategori-nama').value = '';
    document.getElementById('kategori-kode').value = '';
    document.getElementById('kategori-deskripsi').value = '';
    const m = document.getElementById('modal-kategori');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}
function editKategori(item) {
    document.getElementById('modal-kategori-title').innerText = 'Edit Kategori';
    document.getElementById('kategori-id').value = item.id;
    document.getElementById('kategori-nama').value = item.nama_kategori;
    document.getElementById('kategori-kode').value = item.kode_kategori;
    document.getElementById('kategori-deskripsi').value = item.deskripsi || '';
    const m = document.getElementById('modal-kategori');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}
function closeModalKategori() {
    const m = document.getElementById('modal-kategori');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}
</script>
