<?php
/**
 * Sirkulasi Peminjaman Sarpras
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
                <span>Sirkulasi Peminjaman Sarpras</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pencatatan peminjaman peralatan multimedia, elektronik, dan sarana kegiatan belajar-mengajar
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button onclick="openModalPinjam()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>+ Catat Peminjaman Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 text-xs">
        <a href="<?= url('kelola-sarpras/peminjaman') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= empty($status) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Semua Riwayat
        </a>
        <a href="<?= url('kelola-sarpras/peminjaman?status=Dipinjam') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Dipinjam' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Sedang Dipinjam
        </a>
        <a href="<?= url('kelola-sarpras/peminjaman?status=Kembali') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Kembali' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Sudah Dikembalikan
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Kode &amp; Barang</th>
                        <th class="py-3.5 px-4">Peminjam</th>
                        <th class="py-3.5 px-4">Tgl Pinjam &bull; Batas</th>
                        <th class="py-3.5 px-4">Keperluan</th>
                        <th class="py-3.5 px-4 text-center">Kondisi Balik</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="text-4xl mb-2">📋</div>
                                <p class="font-semibold">Belum ada riwayat peminjaman.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($items as $p): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= e($p['nama_barang']) ?></div>
                                    <div class="text-[11px] font-mono text-primary-700 font-semibold">
                                        <?= e($p['kode_pinjam']) ?> &bull; <?= $p['jumlah_pinjam'] ?> <?= e($p['satuan'] ?? 'Unit') ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800"><?= e($p['nama_peminjam']) ?></div>
                                    <div class="text-[10px] text-slate-400">
                                        <?= e($p['role_peminjam']) ?> <?= !empty($p['kontak_peminjam']) ? ' &bull; ' . e($p['kontak_peminjam']) : '' ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-700 font-medium">Pinjam: <?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></div>
                                    <div class="text-[10px] font-bold <?= (strtotime($p['estimasi_kembali']) < time() && $p['status'] === 'Dipinjam') ? 'text-rose-600' : 'text-slate-400' ?>">
                                        Batas: <?= date('d M Y', strtotime($p['estimasi_kembali'])) ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="text-slate-600 max-w-xs truncate" title="<?= e($p['keperluan']) ?>">
                                        <?= e($p['keperluan']) ?>
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($p['kondisi_sesudah'])): ?>
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold <?= $p['kondisi_sesudah'] === 'Baik' ? 'bg-primary-50 text-primary-700' : 'bg-amber-50 text-amber-700' ?>">
                                            <?= e($p['kondisi_sesudah']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($p['status'] === 'Dipinjam'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Dipinjam
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                            Kembali (<?= !empty($p['tanggal_kembali']) ? date('d/m/y', strtotime($p['tanggal_kembali'])) : '-' ?>)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($p['status'] === 'Dipinjam'): ?>
                                        <button onclick="openModalKembali(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama_barang']), ENT_QUOTES) ?>')" class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-[11px] shadow-sm transition-all">
                                            Kembalikan
                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px] font-medium">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Catat Peminjaman Baru -->
<div id="modal-pinjam" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform scale-95 transition-all duration-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800">Catat Peminjaman Sarpras</h3>
            <button onclick="closeModalPinjam()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="<?= url('kelola-sarpras/peminjaman/store') ?>" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Barang yang Dipinjam <span class="text-rose-500">*</span></label>
                <select name="barang_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                    <option value="">-- Pilih Barang Tersedia --</option>
                    <?php foreach ($barangTersedia as $bt): ?>
                        <option value="<?= $bt['id'] ?>"><?= e($bt['nama_barang']) ?> (<?= e($bt['kode_barang']) ?>) &bull; Stok: <?= $bt['jumlah'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Peminjam <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_peminjam" required placeholder="Contoh: Ust. Fulan, S.Pd" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Peran Peminjam</label>
                    <select name="role_peminjam" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                        <option value="Guru">Guru / Tenaga Pengajar</option>
                        <option value="Pegawai">Staf / Tenaga Kependidikan</option>
                        <option value="Siswa">Siswa / OSIS</option>
                        <option value="Lainnya">Lainnya / Pihak Luar</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor Kontak / WhatsApp</label>
                    <input type="text" name="kontak_peminjam" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah Unit Dipinjam</label>
                    <input type="number" name="jumlah_pinjam" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Pinjam</label>
                    <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Estimasi Pengembalian</label>
                    <input type="date" name="estimasi_kembali" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Keperluan Peminjaman <span class="text-rose-500">*</span></label>
                <textarea name="keperluan" required rows="2" placeholder="Contoh: Pembelajaran multimedia kelas 8, seminar, rapat..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-indigo-500 outline-none"></textarea>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeModalPinjam()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Simpan Peminjaman</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kembalikan Barang -->
<div id="modal-kembali" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden transform scale-95 transition-all duration-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800">Konfirmasi Pengembalian</h3>
            <button onclick="closeModalKembali()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-kembali" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>
            <p class="text-slate-600">Barang: <strong id="kembali-barang-nama" class="text-slate-800"></strong></p>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Kondisi Fisik Saat Dikembalikan</label>
                <select name="kondisi_sesudah" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-semibold">
                    <option value="Baik">Baik (Lengkap &amp; Berfungsi Normal)</option>
                    <option value="Rusak Ringan">Rusak Ringan (Ada cacat/keluhan ringan)</option>
                    <option value="Rusak Berat">Rusak Berat (Mati total / Rusak parah)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan Petugas</label>
                <textarea name="catatan" rows="2" placeholder="Catatan kelengkapan kabel, tas, remote, atau kondisi barang..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none"></textarea>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeModalKembali()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold shadow-md">Konfirmasi Selesai</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalPinjam() {
    const m = document.getElementById('modal-pinjam');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}
function closeModalPinjam() {
    const m = document.getElementById('modal-pinjam');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}

function openModalKembali(id, nama) {
    document.getElementById('kembali-barang-nama').innerText = nama;
    document.getElementById('form-kembali').action = '<?= url("kelola-sarpras/peminjaman/kembali/") ?>' + id;
    const m = document.getElementById('modal-kembali');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}
function closeModalKembali() {
    const m = document.getElementById('modal-kembali');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}
</script>
