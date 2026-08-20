<?php
/**
 * Penugasan Pegawai - List View
 */
?>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800"><?= e($pageTitle) ?></h1>
        <p class="text-sm text-slate-500 mt-1">Kelola riwayat penugasan dan Surat Keputusan (SK) pegawai.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?= url('kelola-pegawai') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:text-primary-600 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Data Pegawai
        </a>
    </div>
</div>

<!-- Filters and Actions -->
<div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6">
    <form action="" method="GET" class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>
            <input type="text" name="search" value="<?= e($search) ?>" 
                   placeholder="Cari nama pegawai atau No SK..." 
                   class="block w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
        </div>
        
        <!-- Filter Unit -->
        <div class="w-full sm:w-48">
            <select name="unit_tugas" class="block w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                <option value="">Semua Unit Tugas</option>
                <?php foreach ($unitTugasList as $ut): ?>
                    <option value="<?= e($ut['id']) ?>" <?= $filterUnit == $ut['id'] ? 'selected' : '' ?>>
                        <?= e($ut['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-primary-500/25">
                Filter
            </button>
            <?php if ($search || $filterUnit): ?>
                <a href="<?= url('kelola-pegawai/penugasan') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-all">
                    Reset
                </a>
            <?php endif; ?>
            
            <a href="<?= url('kelola-pegawai/penugasan/create') ?>" class="flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm shadow-primary-500/30 ml-auto sm:ml-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Penugasan
            </a>
        </div>
    </form>
</div>

<!-- Table Data -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-500">
                <tr>
                    <th class="px-6 py-4 font-semibold text-center w-16">No</th>
                    <th class="px-6 py-4 font-semibold">Pegawai</th>
                    <th class="px-6 py-4 font-semibold">Nomor SK</th>
                    <th class="px-6 py-4 font-semibold">Unit & Jabatan</th>
                    <th class="px-6 py-4 font-semibold">TMT</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold text-right w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($penugasan)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <p class="text-base font-medium text-slate-600">Belum ada data penugasan</p>
                                <p class="text-sm mt-1">Silakan tambahkan data penugasan pegawai pertama Anda.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset + 1; foreach ($penugasan as $p): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-500"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800"><?= e($p['nama_pegawai']) ?></div>
                                <div class="text-[11px] text-slate-500 mt-0.5">NIY: <?= e($p['niy'] ?: '-') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-700"><?= e($p['no_sk']) ?></div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Tgl: <?= date('d/m/Y', strtotime($p['tanggal_sk'])) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-primary-600"><?= e($p['nama_unit']) ?></div>
                                <div class="text-[11px] text-slate-500 mt-0.5"><?= e($p['nama_jabatan']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div><?= date('d/m/Y', strtotime($p['tmt_mulai'])) ?></div>
                                <?php if ($p['tst_selesai']): ?>
                                    <div class="text-[11px] text-rose-500 mt-0.5">s/d <?= date('d/m/Y', strtotime($p['tst_selesai'])) ?></div>
                                <?php else: ?>
                                    <div class="text-[11px] text-emerald-500 mt-0.5">Masih Aktif</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($p['status'] === 'Aktif'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                        Tidak Aktif
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($p['file_sk']): ?>
                                    <a href="<?= url(ltrim($p['file_sk'], '/')) ?>" target="_blank" class="inline-flex items-center justify-center p-1.5 ml-2 text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors" title="Lihat Berkas SK">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= url('kelola-pegawai/penugasan/edit/' . $p['id']) ?>" 
                                       class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-all"
                                       title="Edit">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="<?= url('kelola-pegawai/penugasan/delete/' . $p['id']) ?>" class="inline" onsubmit="AppNotif.confirm(event, this, 'Hapus Penugasan', 'Yakin ingin menghapus riwayat penugasan ini?');">
                                        <button type="submit" 
                                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                                                title="Hapus">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
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

<?php if (isset($total) && $total > $limit): ?>
<div class="mt-6">
    <?= UI::pagination($total, $limit, $page, url('kelola-pegawai/penugasan')) ?>
</div>
<?php endif; ?>
