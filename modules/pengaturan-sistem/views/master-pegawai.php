<?php
/**
 * View Master Data Pegawai
 */
?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Unit Tugas Section -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-primary-50 bg-primary-50/30 flex justify-between items-center">
                <h2 class="text-lg font-bold text-primary-900">Unit Tugas</h2>
                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-xs">
                    <?= count($unit_tugas) ?>
                </div>
            </div>
            
            <div class="p-6 border-b border-slate-100">
                <form action="<?= url('pengaturan-sistem/master-pegawai/unit-tugas/store') ?>" method="POST" class="flex gap-3">
                    <?= CSRF::field() ?>
                    <input type="text" name="nama" placeholder="Tambah Unit Tugas Baru..." required class="flex-1 px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-[10px] transition-colors text-sm whitespace-nowrap">Tambah</button>
                </form>
            </div>

            <div class="flex-1 overflow-y-auto max-h-[400px]">
                <?php if (empty($unit_tugas)): ?>
                    <div class="p-8 text-center text-slate-400">
                        <p class="text-sm">Belum ada data Unit Tugas.</p>
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($unit_tugas as $ut): ?>
                            <li class="flex items-center justify-between px-6 py-3 hover:bg-primary-50/30 transition-colors group">
                                <span class="text-sm font-medium text-slate-700"><?= e($ut['nama']) ?></span>
                                <form action="<?= url('pengaturan-sistem/master-pegawai/unit-tugas/delete/' . $ut['id']) ?>" method="POST" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Hapus Unit Tugas ini?');">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="text-rose-400 hover:text-rose-600 p-1 opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Jabatan Section -->
        <!-- Jabatan Section -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-primary-50 bg-primary-50/30 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-primary-900">Jabatan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola referensi jabatan khusus Guru atau Support System</p>
                </div>
                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-xs">
                    <?= count($jabatan) ?>
                </div>
            </div>
            
            <div class="p-6 border-b border-slate-100 bg-slate-50/40">
                <form action="<?= url('pengaturan-sistem/master-pegawai/jabatan/store') ?>" method="POST" class="flex flex-col sm:flex-row gap-2.5">
                    <?= CSRF::field() ?>
                    <input type="text" name="nama" placeholder="Nama Jabatan Baru..." required class="flex-1 px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <select name="kategori" required class="px-3 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-700 text-sm focus:border-primary-500 transition-colors font-medium">
                        <option value="support_system">💼 Khusus Support System</option>
                        <option value="guru">👨‍🏫 Khusus Guru</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-[10px] transition-colors text-sm whitespace-nowrap shadow-sm">Tambah</button>
                </form>
            </div>

            <div class="flex-1 overflow-y-auto max-h-[420px]">
                <?php if (empty($jabatan)): ?>
                    <div class="p-8 text-center text-slate-400">
                        <p class="text-sm">Belum ada data Jabatan.</p>
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($jabatan as $jb): 
                            $isGuru = (($jb['kategori'] ?? '') === 'guru');
                        ?>
                            <li class="flex items-center justify-between px-6 py-3.5 hover:bg-primary-50/30 transition-colors group">
                                <div class="flex items-center gap-3 min-w-0 pr-2">
                                    <span class="text-sm font-semibold text-slate-800 truncate"><?= e($jb['nama']) ?></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold shrink-0 <?= $isGuru ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-indigo-100 text-indigo-800 border border-indigo-200' ?>">
                                        <?= $isGuru ? '👨‍🏫 Guru' : '💼 Support System' ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Toggle Kategori Switch Form -->
                                    <form action="<?= url('pengaturan-sistem/master-pegawai/jabatan/update-kategori/' . $jb['id']) ?>" method="POST" class="inline">
                                        <?= CSRF::field() ?>
                                        <input type="hidden" name="kategori" value="<?= $isGuru ? 'support_system' : 'guru' ?>">
                                        <button type="submit" class="text-xs px-2.5 py-1 rounded-lg border font-semibold transition-all <?= $isGuru ? 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100' : 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' ?>" title="Klik untuk ubah ke <?= $isGuru ? 'Support System' : 'Guru' ?>">
                                            Ubah ke <?= $isGuru ? 'Support System' : 'Guru' ?>
                                        </button>
                                    </form>
                                    <form action="<?= url('pengaturan-sistem/master-pegawai/jabatan/delete/' . $jb['id']) ?>" method="POST" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Hapus Jabatan ini?');">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="text-rose-400 hover:text-rose-600 p-1 opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Status Kerja Section -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-primary-50 bg-primary-50/30 flex justify-between items-center">
                <h2 class="text-lg font-bold text-primary-900">Status Kerja</h2>
                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-xs">
                    <?= count($status_kerja) ?>
                </div>
            </div>
            
            <div class="p-6 border-b border-slate-100">
                <form action="<?= url('pengaturan-sistem/master-pegawai/status-kerja/store') ?>" method="POST" class="flex gap-3">
                    <?= CSRF::field() ?>
                    <input type="text" name="nama" placeholder="Tambah Status Kerja Baru..." required class="flex-1 px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-[10px] transition-colors text-sm whitespace-nowrap">Tambah</button>
                </form>
            </div>

            <div class="flex-1 overflow-y-auto max-h-[400px]">
                <?php if (empty($status_kerja)): ?>
                    <div class="p-8 text-center text-slate-400">
                        <p class="text-sm">Belum ada data Status Kerja.</p>
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($status_kerja as $sk): ?>
                            <li class="flex items-center justify-between px-6 py-3 hover:bg-primary-50/30 transition-colors group">
                                <span class="text-sm font-medium text-slate-700"><?= e($sk['nama']) ?></span>
                                <form action="<?= url('pengaturan-sistem/master-pegawai/status-kerja/delete/' . $sk['id']) ?>" method="POST" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Hapus Status Kerja ini?');">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="text-rose-400 hover:text-rose-600 p-1 opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Jenis Pegawai Section -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-primary-50 bg-primary-50/30 flex justify-between items-center">
                <h2 class="text-lg font-bold text-primary-900">Jenis Pegawai</h2>
                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-xs">
                    <?= count($jenis_pegawai) ?>
                </div>
            </div>
            
            <div class="p-6 border-b border-slate-100">
                <form action="<?= url('pengaturan-sistem/master-pegawai/jenis-pegawai/store') ?>" method="POST" class="flex gap-3">
                    <?= CSRF::field() ?>
                    <input type="text" name="nama" placeholder="Tambah Jenis Pegawai Baru..." required class="flex-1 px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-[10px] transition-colors text-sm whitespace-nowrap">Tambah</button>
                </form>
            </div>

            <div class="flex-1 overflow-y-auto max-h-[400px]">
                <?php if (empty($jenis_pegawai)): ?>
                    <div class="p-8 text-center text-slate-400">
                        <p class="text-sm">Belum ada data Jenis Pegawai.</p>
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($jenis_pegawai as $jp): ?>
                            <li class="flex items-center justify-between px-6 py-3 hover:bg-primary-50/30 transition-colors group">
                                <span class="text-sm font-medium text-slate-700"><?= e($jp['nama']) ?></span>
                                <form action="<?= url('pengaturan-sistem/master-pegawai/jenis-pegawai/delete/' . $jp['id']) ?>" method="POST" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Hapus Jenis Pegawai ini?');">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="text-rose-400 hover:text-rose-600 p-1 opacity-0 group-hover:opacity-100 transition-opacity" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
