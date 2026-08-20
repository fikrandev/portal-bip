<?php /** Kelola Siswa Dashboard View */ ?>

<!-- Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-primary-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-900"><?= $totalSiswa ?></p>
                <p class="text-xs text-slate-500">Total Siswa</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-primary-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-900"><?= $totalLaki ?></p>
                <p class="text-xs text-slate-500">Laki-laki</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-pink-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-900"><?= $totalPerempuan ?></p>
                <p class="text-xs text-slate-500">Perempuan</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary-900"><?= $totalAktif ?></p>
                <p class="text-xs text-slate-500">Siswa Aktif</p>
            </div>
        </div>
    </div>
</div>

<!-- Action Bar -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" action="<?= url('kelola-siswa') ?>" class="flex gap-3 flex-1 max-w-md">
        <div class="relative flex-1">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari NIS, nama, atau kelas..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        </div>
        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide">Cari</button>
    </form>
    <?php if (RBAC::hasPermission('siswa.create')): ?>
    <a href="<?= url('kelola-siswa/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-full shadow-lg shadow-primary-500/25 transition-all text-sm">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Siswa
    </a>
    <?php endif; ?>
</div>

<!-- Data Table -->
<div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary-50/50 border-b border-primary-100">
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">NIS</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">Nama</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800 hidden sm:table-cell">JK</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800 hidden md:table-cell">Kelas</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800 hidden lg:table-cell">Telepon</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800">Status</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-50">
                <?php if (empty($siswa)): ?>
                <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">
                    <div class="inline-flex flex-col items-center">
                        <svg class="w-12 h-12 text-primary-200 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                        <p class="font-medium text-primary-900 mb-1">Belum ada data siswa</p>
                        <p class="text-xs">Klik "Tambah Siswa" untuk menambahkan data baru.</p>
                    </div>
                </td></tr>
                <?php endif; ?>
                <?php foreach ($siswa as $s): ?>
                <tr class="hover:bg-primary-50/30 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs text-primary-600"><?= e($s['nis']) ?></td>
                    <td class="px-6 py-4 font-medium text-primary-900"><?= e($s['nama']) ?></td>
                    <td class="px-6 py-4 text-center hidden sm:table-cell">
                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium <?= $s['jenis_kelamin'] === 'L' ? 'bg-primary-50 text-primary-700' : 'bg-pink-50 text-pink-700' ?>">
                            <?= $s['jenis_kelamin'] === 'L' ? 'L' : 'P' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 hidden md:table-cell"><?= e($s['kelas'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-slate-600 hidden lg:table-cell"><?= e($s['telepon'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($s['is_active']): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-2xl bg-emerald-50 text-emerald-700 text-xs font-medium"><span class="w-1.5 h-1.5 rounded-2xl bg-emerald-500"></span>Aktif</span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-2xl bg-slate-100 text-slate-500 text-xs font-medium"><span class="w-1.5 h-1.5 rounded-2xl bg-slate-400"></span>Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <?php if (RBAC::hasPermission('siswa.update')): ?>
                            <a href="<?= url('kelola-siswa/edit/' . $s['id']) ?>" class="p-2 rounded-full text-primary-500 hover:bg-primary-50 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if (RBAC::hasPermission('siswa.delete')): ?>
                            <form method="POST" action="<?= url('kelola-siswa/delete/' . $s['id']) ?>" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Yakin ingin menghapus data siswa ini?');">
                                <?= CSRF::field() ?>
                                <button type="submit" class="p-2 rounded-full text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-between px-6 py-4 border-t border-primary-50">
        <p class="text-xs text-slate-500">Halaman <?= $page ?> dari <?= $totalPages ?> (<?= $total ?> data)</p>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
            <a href="<?= url('kelola-siswa?page=' . ($page-1) . ($search ? '&search=' . urlencode($search) : '')) ?>" class="px-3 py-1.5 rounded-full text-xs font-medium text-primary-600 hover:bg-primary-50 transition-colors">← Prev</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
            <a href="<?= url('kelola-siswa?page=' . ($page+1) . ($search ? '&search=' . urlencode($search) : '')) ?>" class="px-3 py-1.5 rounded-full text-xs font-medium text-primary-600 hover:bg-primary-50 transition-colors">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
