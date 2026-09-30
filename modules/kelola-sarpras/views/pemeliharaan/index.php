<?php
/**
 * Pemeliharaan & Perbaikan Sarpras
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
                <span>Pemeliharaan &amp; Perbaikan Sarpras</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pusat pengaduan kerusakan fasilitas, perbaikan barang, dan pemeliharaan gedung berkala
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button onclick="openModalLapor()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>+ Lapor Kerusakan Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 text-xs">
        <a href="<?= url('kelola-sarpras/pemeliharaan') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= empty($status) ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Semua Laporan
        </a>
        <a href="<?= url('kelola-sarpras/pemeliharaan?status=Menunggu') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Menunggu' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Menunggu Tindakan
        </a>
        <a href="<?= url('kelola-sarpras/pemeliharaan?status=Diproses') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Diproses' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Sedang Diperbaiki
        </a>
        <a href="<?= url('kelola-sarpras/pemeliharaan?status=Selesai') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Selesai' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Selesai Diperbaiki
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Kode &amp; Judul Laporan</th>
                        <th class="py-3.5 px-4">Objek / Ruangan</th>
                        <th class="py-3.5 px-4">Pelapor &bull; Tanggal</th>
                        <th class="py-3.5 px-4 text-center">Urgensi</th>
                        <th class="py-3.5 px-4 text-right">Biaya (Est / Real)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="text-4xl mb-2">🛠️</div>
                                <p class="font-semibold">Belum ada tiket pemeliharaan atau perbaikan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($items as $m): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= e($m['judul_laporan']) ?></div>
                                    <div class="text-[11px] font-mono text-amber-700 font-semibold"><?= e($m['kode_perbaikan']) ?></div>
                                    <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1"><?= e($m['deskripsi_kerusakan']) ?></p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($m['nama_barang'])): ?>
                                        <div class="font-bold text-slate-800"><?= e($m['nama_barang']) ?></div>
                                        <div class="text-[10px] font-mono text-slate-400"><?= e($m['kode_barang']) ?></div>
                                    <?php elseif (!empty($m['nama_ruangan'])): ?>
                                        <div class="font-bold text-slate-800">Fasilitas Ruangan</div>
                                        <div class="text-[10px] text-slate-500"><?= e($m['nama_ruangan']) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-400">Fasilitas Gedung</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800"><?= e($m['pelapor_nama']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($m['tanggal_lapor'])) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($m['tingkat_urgensi'] === 'Darurat'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 animate-pulse">Darurat</span>
                                    <?php elseif ($m['tingkat_urgensi'] === 'Tinggi'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Tinggi</span>
                                    <?php elseif ($m['tingkat_urgensi'] === 'Sedang'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Sedang</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Rendah</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-bold text-slate-800">
                                        <?= $m['biaya_realisasi'] > 0 ? 'Rp ' . number_format($m['biaya_realisasi'], 0, ',', '.') : '-' ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Est: Rp <?= number_format($m['estimasi_biaya'], 0, ',', '.') ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($m['status'] === 'Selesai'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">Selesai</span>
                                    <?php elseif ($m['status'] === 'Diproses'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Diproses</span>
                                    <?php elseif ($m['status'] === 'Menunggu'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Menunggu</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Afkir</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button onclick="openModalUpdate(<?= htmlspecialchars(json_encode($m)) ?>)" class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-[11px] shadow-sm transition-all">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Lapor Kerusakan Baru -->
<div id="modal-lapor" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform scale-95 transition-all duration-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800">Lapor Kerusakan Sarpras</h3>
            <button onclick="closeModalLapor()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="<?= url('kelola-sarpras/pemeliharaan/store') ?>" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Judul Kerusakan / Pengaduan <span class="text-rose-500">*</span></label>
                <input type="text" name="judul_laporan" required placeholder="Contoh: AC Lab Komputer Bocor, Kursi Kelas Patah..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Barang yang Rusak</label>
                    <select name="barang_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none">
                        <option value="">-- Bukan Barang Tertentu --</option>
                        <?php foreach ($barangList as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= e($b['nama_barang']) ?> (<?= e($b['kode_barang']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lokasi Ruangan / Fasilitas</label>
                    <select name="ruangan_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none">
                        <option value="">-- Pilih Ruangan --</option>
                        <?php foreach ($ruanganList as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['nama_ruangan']) ?> (<?= e($r['unit']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Pelapor</label>
                    <input type="text" name="pelapor_nama" value="<?= e(Auth::name()) ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tingkat Urgensi</label>
                    <select name="tingkat_urgensi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none font-semibold">
                        <option value="Rendah">Rendah (Dapat ditunda)</option>
                        <option value="Sedang" selected>Sedang (Perlu perbaikan segera)</option>
                        <option value="Tinggi">Tinggi (Mengganggu KBM)</option>
                        <option value="Darurat">Darurat (Berbahaya / Kritis)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi &amp; Gejala Kerusakan <span class="text-rose-500">*</span></label>
                <textarea name="deskripsi_kerusakan" required rows="3" placeholder="Jelaskan kondisi kerusakan fisik, letak masalah, atau suara yang tidak normal..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Estimasi Biaya Perbaikan (Rp)</label>
                <input type="number" name="estimasi_biaya" value="0" min="0" step="1000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none">
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeModalLapor()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md">Kirim Pengaduan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Update Status Perbaikan -->
<div id="modal-update" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform scale-95 transition-all duration-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800">Tindak Lanjut &amp; Status Perbaikan</h3>
            <button onclick="closeModalUpdate()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-update" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>
            <p class="text-slate-600">Tiket: <strong id="update-judul" class="text-slate-800"></strong></p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status Pengerjaan</label>
                    <select name="status" id="update-status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-semibold">
                        <option value="Menunggu">Menunggu Teknisi</option>
                        <option value="Diproses">Sedang Dikerjakan / Diproses</option>
                        <option value="Selesai">Selesai Diperbaiki (Aset Siap)</option>
                        <option value="Afkir">Tidak Dapat Diperbaiki (Afkir/Hapus)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Teknisi / Rekanan Pihak Ke-3</label>
                    <input type="text" name="teknisi_pihak" id="update-teknisi" placeholder="Contoh: Tim IT Internal, CV Servis..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Biaya Realisasi Perbaikan (Rp)</label>
                <input type="number" name="biaya_realisasi" id="update-biaya" value="0" min="0" step="1000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tindakan / Solusi yang Dikerjakan</label>
                <textarea name="tindakan_perbaikan" id="update-tindakan" rows="3" placeholder="Jelaskan komponen yang diganti, servis yang dilakukan, atau pengujian kelayakan..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none"></textarea>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeModalUpdate()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold shadow-md">Simpan Pembaruan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalLapor() {
    const m = document.getElementById('modal-lapor');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}
function closeModalLapor() {
    const m = document.getElementById('modal-lapor');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}

function openModalUpdate(item) {
    document.getElementById('update-judul').innerText = item.judul_laporan + ' (' + item.kode_perbaikan + ')';
    document.getElementById('update-status').value = item.status;
    document.getElementById('update-teknisi').value = item.teknisi_pihak || '';
    document.getElementById('update-biaya').value = item.biaya_realisasi || '0';
    document.getElementById('update-tindakan').value = item.tindakan_perbaikan || '';
    document.getElementById('form-update').action = '<?= url("kelola-sarpras/pemeliharaan/update-status/") ?>' + item.id;
    const m = document.getElementById('modal-update');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}
function closeModalUpdate() {
    const m = document.getElementById('modal-update');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}
</script>
