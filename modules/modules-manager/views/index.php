<?php /** Module Manager List View */ ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </div>
            <span>Kelola Modul Portal</span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Kelola modul-modul yang aktif dan tersedia di sistem portal.</p>
    </div>
    <?php if (RBAC::hasPermission('modules.create')): ?>
    <div class="flex items-center gap-2.5">
        <a href="<?= url('modules-manager/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-500/25 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>+ Tambah Modul</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary-50/50 border-b border-primary-100">
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800 w-12">#</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">Modul</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800 hidden md:table-cell">Route</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800">Status</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-50">
                <?php foreach ($modules as $i => $mod): ?>
                <tr class="hover:bg-primary-50/30 transition-colors">
                    <td class="px-6 py-4 text-slate-400"><?= $i + 1 ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl flex items-center justify-center" style="background: <?= e($mod['color']) ?>15;">
                                <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5" style="color: <?= e($mod['color']) ?>;"><?= $mod['icon_svg'] ?></span>
                            </div>
                            <div>
                                <p class="font-medium text-primary-900"><?= e($mod['name']) ?></p>
                                <p class="text-xs text-slate-400"><?= e($mod['slug']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500 hidden md:table-cell"><code class="text-xs bg-primary-50 px-2 py-1 rounded"><?= e($mod['route']) ?></code></td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($mod['is_active']): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-2xl bg-emerald-50 text-emerald-700 text-xs font-medium"><span class="w-1.5 h-1.5 rounded-2xl bg-emerald-500"></span>Aktif</span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-2xl bg-slate-100 text-slate-500 text-xs font-medium"><span class="w-1.5 h-1.5 rounded-2xl bg-slate-400"></span>Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <?php if (RBAC::hasPermission('modules.update')): ?>
                            <a href="<?= url('modules-manager/edit/' . $mod['id']) ?>" class="p-2 rounded-full text-primary-500 hover:bg-primary-50 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if (RBAC::hasPermission('modules.delete')): ?>
                            <form method="POST" action="<?= url('modules-manager/delete/' . $mod['id']) ?>" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Yakin ingin menghapus modul ini?');">
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
</div>
