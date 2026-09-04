<?php /** Users List View */ ?>

<!-- Page Header & Action Buttons (Sudut Kanan Atas) -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <span>Kelola Pengguna</span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Total <?= $total ?> pengguna terdaftar di sistem portal.</p>
    </div>
    <?php if (RBAC::hasPermission('users.create')): ?>
    <div class="flex items-center gap-2.5">
        <a href="<?= url('users/create') ?>" id="btn-add-user"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-500/25 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            <span>+ Tambah Pengguna</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Search & Filter Controls (Di Bawahnya Sebelah Kiri) -->
<div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs mb-6">
    <form method="GET" action="<?= url('users') ?>" class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[240px] max-w-md">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama, email, atau username..."
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white transition-all text-sm font-medium">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-sm focus:outline-none transition-all duration-200 text-sm">
                Cari
            </button>
            <?php if ($search): ?>
                <a href="<?= url('users') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-all duration-200 text-sm" title="Reset Filter">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="users-table">
            <thead>
                <tr class="bg-primary-50/50 border-b border-primary-100">
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">Nama</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800 hidden sm:table-cell">Username</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800 hidden md:table-cell">Email</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800 hidden lg:table-cell">Peran</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800">Status</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-50">
                <?php if (empty($users)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Tidak ada data pengguna.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-primary-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold">
                                <?= mb_strtoupper(mb_substr($u['full_name'], 0, 2)) ?>
                            </div>
                            <span class="font-medium text-primary-900"><?= e($u['full_name']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-600 hidden sm:table-cell"><?= e($u['username']) ?></td>
                    <td class="px-6 py-4 text-slate-600 hidden md:table-cell"><?= e($u['email']) ?></td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <span class="inline-flex px-2.5 py-1 rounded-2xl bg-primary-50 text-primary-700 text-xs font-medium"><?= e($u['role_names'] ?? '-') ?></span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($u['is_active']): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-2xl bg-emerald-50 text-emerald-700 text-xs font-medium">
                                <span class="w-1.5 h-1.5 rounded-2xl bg-emerald-500"></span>Aktif
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-2xl bg-slate-100 text-slate-500 text-xs font-medium">
                                <span class="w-1.5 h-1.5 rounded-2xl bg-slate-400"></span>Nonaktif
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <?php if (RBAC::hasPermission('users.update')): ?>
                            <a href="<?= url('users/edit/' . $u['id']) ?>" class="p-2 rounded-full text-primary-500 hover:bg-primary-50 hover:text-primary-700 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if (RBAC::hasPermission('users.delete') && $u['id'] != Auth::id()): ?>
                            <form method="POST" action="<?= url('users/delete/' . $u['id']) ?>" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Yakin ingin menghapus pengguna ini?');">
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
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-between px-6 py-4 border-t border-primary-50">
        <p class="text-xs text-slate-500">Halaman <?= $page ?> dari <?= $totalPages ?></p>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
            <a href="<?= url('users?page=' . ($page-1) . ($search ? '&search=' . urlencode($search) : '')) ?>" class="px-3 py-1.5 rounded-full text-xs font-medium text-primary-600 hover:bg-primary-50 transition-colors">← Prev</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
            <a href="<?= url('users?page=' . ($page+1) . ($search ? '&search=' . urlencode($search) : '')) ?>" class="px-3 py-1.5 rounded-full text-xs font-medium text-primary-600 hover:bg-primary-50 transition-colors">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
