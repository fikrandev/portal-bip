<?php
/**
 * Detail Anggota Penugasan dalam Grup - View
 * Portal BIP - Bina Insan Palu
 */
?>

<div class="space-y-6">
    <!-- 1. Header Banner & Actions -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <!-- Left: Title & Status Badges -->
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= url('kelola-pegawai/penugasan') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-primary-700 transition-colors mr-1">
                        ← Daftar Grup
                    </a>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold <?= $grup['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                        <?= $grup['is_active'] ? '● GRUP AKTIF' : '○ NONAKTIF' ?>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700">
                        Semester <?= e($grup['semester']) ?>
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    <?= e($grup['nama_grup']) ?>
                </h1>
            </div>

            <!-- Right: Action Buttons in 1 Organized Row -->
            <div class="flex flex-wrap items-center gap-2.5 sm:self-auto self-start">
                <!-- Cetak SK Button -->
                <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id'] . '/cetak') ?>" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition-all hover:shadow-amber-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    <span>Cetak SK</span>
                </a>

                <!-- Toggle Aktif / Nonaktif -->
                <?php if ($grup['is_active']): ?>
                <form method="POST" action="<?= url('kelola-pegawai/penugasan/grup/toggle-aktif/' . $grup['id']) ?>" onsubmit="AppNotif.confirm(event, this, 'Nonaktifkan Grup', 'Nonaktifkan grup ini? Data jabatan pegawai akan disesuaikan dengan grup aktif lainnya.');" class="inline">
                    <?= CSRF::field() ?>
                    <button type="submit" class="px-3.5 py-2.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 text-xs font-bold rounded-xl border border-slate-200 hover:border-rose-200 transition-all flex items-center gap-1.5">
                        <span>🛑 Nonaktifkan</span>
                    </button>
                </form>
                <?php else: ?>
                <form method="POST" action="<?= url('kelola-pegawai/penugasan/grup/toggle-aktif/' . $grup['id']) ?>" onsubmit="AppNotif.confirm(event, this, 'Aktifkan Grup Ini', 'Aktifkan grup ini? Grup ini akan berjalan aktif bersamaan dengan grup unit aktif lainnya.');" class="inline">
                    <?= CSRF::field() ?>
                    <button type="submit" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                        <span>⚡ Aktifkan</span>
                    </button>
                </form>
                <?php endif; ?>

                <!-- Tambah Pegawai Button (Primary) -->
                <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id'] . '/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-md shadow-primary-600/20 transition-all text-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Pegawai</span>
                </a>
            </div>
        </div>

        <!-- Meta Info Strip -->
        <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-y-3 gap-x-6 text-xs text-slate-600">
            <div class="flex flex-wrap items-center gap-y-2 gap-x-5">
                <div>
                    <span class="text-slate-400">Nomor SK:</span> 
                    <span class="font-bold text-slate-800 ml-1"><?= e($grup['no_sk'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-slate-400">Tanggal SK:</span> 
                    <span class="font-semibold text-slate-800 ml-1"><?= !empty($grup['tanggal_sk']) ? date('d/m/Y', strtotime($grup['tanggal_sk'])) : '-' ?></span>
                </div>
                <div>
                    <span class="text-slate-400">Periode TMT:</span> 
                    <span class="font-semibold text-slate-800 ml-1"><?= !empty($grup['tmt_mulai']) ? date('d/m/Y', strtotime($grup['tmt_mulai'])) : '-' ?> s/d <?= !empty($grup['tst_selesai']) ? date('d/m/Y', strtotime($grup['tst_selesai'])) : 'Sekarang' ?></span>
                </div>
                <div>
                    <span class="text-slate-400">Penandatangan:</span> 
                    <span class="font-semibold text-slate-800 ml-1"><?= e($grup['penandatangan_nama'] ?: 'Ketua Yayasan') ?></span>
                </div>
                <div>
                    <span class="text-slate-400">Total Pegawai:</span> 
                    <span class="font-bold text-primary-700 bg-primary-50 border border-primary-200/60 px-2.5 py-0.5 rounded-full ml-1 font-mono"><?= count($penugasan) ?> Pegawai</span>
                </div>
            </div>

            <div>
                <a href="<?= url('kelola-pegawai/penugasan/grup/edit/' . $grup['id']) ?>" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 font-bold hover:underline">
                    <span>Edit Info Grup ✏️</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Search & Filters Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
        <form action="" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="<?= e($search) ?>" 
                       placeholder="Cari nama pegawai, NIY..." 
                       class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
            </div>
            
            <div class="w-full sm:w-56">
                <select name="unit_tugas" class="block w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    <option value="">Semua Unit Tugas</option>
                    <?php foreach ($unitTugasList as $ut): ?>
                        <option value="<?= e($ut['id']) ?>" <?= $filterUnit == $ut['id'] ? 'selected' : '' ?>>
                            <?= e($ut['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                    Filter
                </button>
                <?php if ($search || $filterUnit): ?>
                    <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id']) ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-all">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- 3. Table Data Penugasan -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5">Pegawai</th>
                        <th class="px-5 py-3.5">Unit Tugas</th>
                        <th class="px-5 py-3.5">Jabatan Penugasan</th>
                        <th class="px-5 py-3.5">SK & TMT</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($penugasan)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-300 text-2xl mb-3">
                                        📄
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Belum ada pegawai ditugaskan pada grup ini</p>
                                    <p class="text-xs mt-1 text-slate-400">Klik tombol "Tambah Pegawai" di atas untuk menambahkan penugasan pegawai ke dalam grup SK ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($penugasan as $p): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5 text-center font-bold text-slate-400"><?= $no++ ?></td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($p['foto'])): ?>
                                            <img src="<?= url(ltrim($p['foto'], '/')) ?>" alt="Foto" class="w-9 h-9 rounded-xl object-cover border border-slate-200">
                                        <?php else: ?>
                                            <div class="w-9 h-9 rounded-xl bg-primary-100 flex items-center justify-center text-primary-700 font-black text-xs">
                                                <?= strtoupper(substr($p['nama_pegawai'], 0, 2)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs"><?= e($p['nama_pegawai']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono"><?= e($p['niy'] ?: '-') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200/80">
                                        <?= e($p['nama_unit'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900">
                                        <?= e($p['nama_jabatan'] ?? '-') ?>
                                        <?php if (!empty($p['wali_kelas_nama'])): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-[10px] text-amber-800 font-bold ml-1">
                                                🏫 <?= e($p['wali_kelas_nama']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($tugasMengajarMap[$p['id']])): ?>
                                        <div class="mt-1 flex flex-wrap gap-1 max-w-sm">
                                            <?php foreach ($tugasMengajarMap[$p['id']] as $tm): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-100 text-[10px] text-indigo-700 font-semibold" title="<?= e($tm['keterangan'] ?? '') ?>">
                                                    <span><?= e($tm['mata_pelajaran']) ?></span>
                                                    <span class="text-indigo-400 font-mono">(<?= e($tm['nama_kelas']) ?>: <?= (int)$tm['jumlah_jp'] ?> JP)</span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-[10px] font-bold text-indigo-900 mt-1 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            <span>Total Beban: <?= (int)$p['total_jp'] ?> JP / Minggu</span>
                                        </div>
                                    <?php elseif (!empty($p['mapel_ajar_summary'])): ?>
                                        <div class="text-[10px] text-indigo-600 mt-0.5">
                                            <?= e($p['mapel_ajar_summary']) ?> (<?= (int)$p['total_jp'] ?> JP)
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    <div>No. SK: <span class="font-semibold text-slate-800"><?= e($p['no_sk'] ?: '-') ?></span></div>
                                    <div class="text-slate-400 text-[11px] mt-0.5">TMT: <?= !empty($p['tmt_mulai']) ? date('d/m/Y', strtotime($p['tmt_mulai'])) : '-' ?></div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <?php if ($p['status'] === 'Aktif'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ● Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                            ○ Tidak Aktif
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($p['file_sk']): ?>
                                        <a href="<?= url(ltrim($p['file_sk'], '/')) ?>" target="_blank" class="inline-flex items-center justify-center p-1 ml-1 text-primary-600 hover:bg-primary-50 rounded-lg" title="Lihat Berkas SK">
                                            📄
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="<?= url('kelola-pegawai/penugasan/detail/edit/' . $p['id']) ?>" 
                                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                           title="Edit Penugasan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="<?= url('kelola-pegawai/penugasan/detail/delete/' . $p['id']) ?>" onsubmit="AppNotif.confirm(event, this, 'Hapus Penugasan', 'Hapus pegawai ini dari grup penugasan?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" 
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                                    title="Hapus Penugasan">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
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
</div>
