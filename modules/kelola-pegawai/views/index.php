<?php /** Kelola Pegawai Dashboard View */ ?>

<!-- Action Bar & Filters -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" action="<?= url('kelola-pegawai') ?>" class="flex flex-col sm:flex-row gap-3 flex-1">
        <div class="relative flex-1 max-w-sm">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama pegawai..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        </div>
        
        <select name="unit_tugas" class="w-full sm:w-auto px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 outline-none focus:border-primary-500 transition-colors text-sm font-medium">
            <option value="">Semua Unit Tugas</option>
            <?php foreach ($unitTugasList as $u): ?>
                <option value="<?= e($u['unit_tugas']) ?>" <?= $filterUnit === $u['unit_tugas'] ? 'selected' : '' ?>><?= e($u['unit_tugas']) ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="jabatan" class="w-full sm:w-auto px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 outline-none focus:border-primary-500 transition-colors text-sm font-medium">
            <option value="">Semua Jabatan</option>
            <?php foreach ($jabatanList as $j): ?>
                <option value="<?= e($j['jabatan']) ?>" <?= $filterJabatan === $j['jabatan'] ? 'selected' : '' ?>><?= e($j['jabatan']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none transition-all duration-200 text-sm tracking-wide">Filter</button>
        <?php if ($search || $filterUnit || $filterJabatan): ?>
            <a href="<?= url('kelola-pegawai') ?>" class="w-full sm:w-auto px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-full transition-all duration-200 text-sm text-center">Reset</a>
        <?php endif; ?>
    </form>
    
    <a href="<?= url('kelola-pegawai/create') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-full shadow-lg shadow-primary-500/25 transition-all text-sm shrink-0">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Pegawai
    </a>
</div>

<!-- Data Table -->
<div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary-50/50 border-b border-primary-100">
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800 w-16">No</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800 w-20">Foto</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">NIY</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">Nama Lengkap</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">Unit Tugas</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">Jabatan</th>
                    <th class="px-6 py-3.5 text-left font-semibold text-primary-800">Masa Kerja</th>
                    <th class="px-6 py-3.5 text-center font-semibold text-primary-800">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-50">
                <?php if (empty($pegawai)): ?>
                <tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">
                    <div class="inline-flex flex-col items-center">
                        <svg class="w-12 h-12 text-primary-200 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        <p class="font-medium text-primary-900 mb-1">Belum ada data pegawai</p>
                        <p class="text-xs">Klik "Tambah Pegawai" untuk menambahkan data baru.</p>
                    </div>
                </td></tr>
                <?php endif; ?>
                
                <?php 
                $no = $offset + 1;
                foreach ($pegawai as $p): 
                    // Calculate Masa Kerja
                    $masaKerja = '-';
                    if (!empty($p['tmt'])) {
                        $tmtDate = new DateTime($p['tmt']);
                        $now = new DateTime();
                        $diff = $tmtDate->diff($now);
                        if ($diff->invert == 0) { // If TMT is in the past
                            $parts = [];
                            if ($diff->y > 0) $parts[] = $diff->y . ' Thn';
                            if ($diff->m > 0) $parts[] = $diff->m . ' Bln';
                            $masaKerja = empty($parts) ? '< 1 Bln' : implode(' ', $parts);
                        }
                    }
                ?>
                <tr class="hover:bg-primary-50/30 transition-colors">
                    <td class="px-6 py-4 text-center text-slate-500"><?= $no++ ?></td>
                    <td class="px-6 py-4 text-center">
                        <?php if (!empty($p['foto'])): ?>
                            <img src="<?= url(ltrim($p['foto'], '/')) ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover border border-slate-200 mx-auto">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold mx-auto border border-primary-200">
                                <?= strtoupper(substr($p['nama'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 font-mono text-xs text-primary-600"><?= e($p['niy'] ?? '-') ?></td>
                    <td class="px-6 py-4 font-medium text-primary-900">
                        <?= e($p['nama']) ?> <?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                    </td>
                    <td class="px-6 py-4 text-slate-600"><?= e($p['unit_tugas'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-slate-600"><?= e($p['jabatan'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-slate-600 font-medium"><?= $masaKerja ?></td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= url('kelola-pegawai/edit/' . $p['id']) ?>" class="p-2 rounded-full text-primary-500 hover:bg-primary-50 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </a>
                            <form method="POST" action="<?= url('kelola-pegawai/delete/' . $p['id']) ?>" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Yakin ingin menghapus data pegawai ini?');">
                                <?= CSRF::field() ?>
                                <button type="submit" class="p-2 rounded-full text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
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
            <?php 
                $queryStr = [];
                if($search) $queryStr['search'] = $search;
                if($filterUnit) $queryStr['unit_tugas'] = $filterUnit;
                if($filterJabatan) $queryStr['jabatan'] = $filterJabatan;
                
                $qs = http_build_query($queryStr);
                $qs = $qs ? '&' . $qs : '';
            ?>
            <?php if ($page > 1): ?>
            <a href="<?= url('kelola-pegawai?page=' . ($page-1) . $qs) ?>" class="px-3 py-1.5 rounded-full text-xs font-medium text-primary-600 hover:bg-primary-50 transition-colors">← Prev</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
            <a href="<?= url('kelola-pegawai?page=' . ($page+1) . $qs) ?>" class="px-3 py-1.5 rounded-full text-xs font-medium text-primary-600 hover:bg-primary-50 transition-colors">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
