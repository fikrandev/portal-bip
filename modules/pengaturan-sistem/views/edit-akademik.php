<?php
/**
 * Konfigurasi Akademik View - Edit
 */
?>

<div class="space-y-6 animate-slide-in">
    <!-- Form Edit Tahun Akademik -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-3xl">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Edit Tahun Akademik</h3>
            <a href="<?= url('pengaturan-sistem/akademik') ?>" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Kembali</a>
        </div>
        
        <div class="p-6">
            <form action="<?= url('pengaturan-sistem/akademik/update/' . $ta['id']) ?>" method="POST">
                <?= CSRF::field() ?>
                
                <div class="mb-6">
                    <label for="nama_tahun" class="block text-sm font-semibold text-slate-700 mb-2">Nama Tahun Ajaran</label>
                    <input type="text" id="nama_tahun" name="nama_tahun" value="<?= e($ta['nama_tahun']) ?>" required
                           class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai Periode</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= e($ta['tanggal_mulai']) ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai Periode</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= e($ta['tanggal_selesai']) ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-[10px] shadow-sm transition-colors text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
