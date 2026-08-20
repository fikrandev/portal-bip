<?php
/**
 * Konfigurasi Akademik View (Refactored for Multiple Years)
 */
?>

<div class="space-y-6 animate-slide-in">
    
    <!-- Form Tambah Tahun Akademik -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Tambah Tahun Akademik</h3>
        </div>
        
        <div class="p-6">
            <form action="<?= url('pengaturan-sistem/akademik/store') ?>" method="POST">
                <?= CSRF::field() ?>
                
                <div class="mb-6">
                    <label for="nama_tahun" class="block text-sm font-semibold text-slate-700 mb-2">Nama Tahun Ajaran</label>
                    <input type="text" id="nama_tahun" name="nama_tahun" placeholder="Misal: 2025/2026 Genap" required
                           class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai Periode</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai Periode</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-[10px] shadow-sm transition-colors text-sm">
                        Simpan & Tambahkan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabel Daftar Tahun Akademik -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Daftar Tahun Akademik</h3>
            <span class="text-xs font-semibold px-2.5 py-1 bg-primary-50 text-primary-600 rounded-full">
                Total: <?= count($tahun_akademik) ?>
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nama Tahun Ajaran</th>
                        <th scope="col" class="px-6 py-3">Periode</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($tahun_akademik)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data tahun akademik. Silakan tambahkan di atas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tahun_akademik as $ta): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-800">
                                    <?= e($ta['nama_tahun']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= date('d/m/Y', strtotime($ta['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($ta['tanggal_selesai'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($ta['is_active'] == 1): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            Tidak Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="<?= url('pengaturan-sistem/akademik/edit/' . $ta['id']) ?>" class="inline-block text-xs font-semibold px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-md transition-colors">
                                        Edit
                                    </a>
                                    
                                    <?php if ($ta['is_active'] == 0): ?>
                                        <form action="<?= url('pengaturan-sistem/akademik/set-aktif/' . $ta['id']) ?>" method="POST" class="inline-block">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-primary-50 text-primary-600 hover:bg-primary-100 rounded-md transition-colors">
                                                Aktifkan
                                            </button>
                                        </form>
                                        
                                        <form action="<?= url('pengaturan-sistem/akademik/delete/' . $ta['id']) ?>" method="POST" class="inline-block" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Yakin ingin menghapus tahun akademik ini?');">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-md transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Sedang Digunakan</span>
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
