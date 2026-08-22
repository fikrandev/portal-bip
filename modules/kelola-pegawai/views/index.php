<?php 
$isKeluar = !empty($isPegawaiKeluarView);
$currentBaseUrl = $isKeluar ? 'kelola-pegawai/keluar' : 'kelola-pegawai';
?>

<?php if ($isKeluar): ?>
    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-xl">🚪</span>
            <div>
                <h3 class="font-bold text-sm">Arsip Data Pegawai Keluar / Non-Aktif</h3>
                <p class="text-xs text-amber-700">Daftar guru & pegawai yang telah purna tugas, mutasi, atau tidak aktif.</p>
            </div>
        </div>
        <a href="<?= url('kelola-pegawai') ?>" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl shadow-sm border border-slate-200 transition-colors">
            ← Kembali ke Pegawai Aktif
        </a>
    </div>
<?php endif; ?>

<!-- Action Bar & Filters -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" action="<?= url($currentBaseUrl) ?>" class="flex flex-col sm:flex-row gap-3 flex-1">
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
            <a href="<?= url($currentBaseUrl) ?>" class="w-full sm:w-auto px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-full transition-all duration-200 text-sm text-center">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php 
        $exportQs = http_build_query([
            'search' => $search ?? '',
            'unit_tugas' => $filterUnit ?? '',
            'jabatan' => $filterJabatan ?? ''
        ]);
    ?>
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
        <!-- Tombol Export Excel -->
        <a href="<?= url('kelola-pegawai/export' . ($exportQs ? '?' . $exportQs : '')) ?>" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-full shadow-lg shadow-emerald-600/20 transition-all text-sm"
           title="Export Seluruh Data Pegawai ke Excel/CSV">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>

        <!-- Tombol Import Excel -->
        <button type="button" 
                onclick="ModalHelper.open('modal-import-pegawai')"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-full shadow-lg shadow-amber-600/20 transition-all text-sm"
                title="Import Data Pegawai dari Excel/CSV">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
            </svg>
            Import Excel
        </button>

        <!-- Tombol Tambah Pegawai -->
        <a href="<?= url('kelola-pegawai/create') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-full shadow-lg shadow-primary-500/25 transition-all text-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Pegawai
        </a>
    </div>
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
                    // Calculate Masa Kerja dari Inputan Tanggal Masuk Kerja Pegawai
                    $masaKerja = '-';
                    $tglMasuk = !empty($p['tanggal_masuk']) ? $p['tanggal_masuk'] : (!empty($p['tmt']) ? $p['tmt'] : null);
                    if (!empty($tglMasuk)) {
                        $tmtDate = new DateTime($tglMasuk);
                        $now = new DateTime();
                        $diff = $tmtDate->diff($now);
                        if ($diff->invert == 0) { // If date is in the past
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
                        <div><?= e($p['nama']) ?> <?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?></div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs text-slate-500 font-normal">
                            <?php if (!empty($p['no_wa'])): ?>
                                <span class="inline-flex items-center gap-1 text-emerald-600 font-medium">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <?= e($p['no_wa']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($p['email'])): ?>
                                <span class="inline-flex items-center gap-1 text-slate-600">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <?= e($p['email']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($p['npwp'])): ?>
                                <span class="text-xs text-slate-400">
                                    NPWP: <?= e($p['npwp']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-600"><?= e($p['unit_tugas'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-slate-600"><?= e($p['jabatan'] ?? '-') ?></td>
                    <td class="px-6 py-4 text-slate-600">
                        <div class="font-semibold text-slate-800"><?= $masaKerja ?></div>
                        <?php if (!empty($tglMasuk)): ?>
                            <div class="text-[11px] text-slate-400 font-normal mt-0.5">Masuk: <?= date('d/m/Y', strtotime($tglMasuk)) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <!-- Tombol Cetak CV -->
                            <a href="<?= url('kelola-pegawai/cetak-cv/' . $p['id']) ?>" target="_blank" class="p-2 rounded-full text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 transition-colors" title="Cetak CV / Biodata Pegawai (F4)">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.285.642-2.502 1.927-2.742a2.025 2.025 0 0 1 2.378 1.488m0 0a2.025 2.025 0 0 1 1.488 2.378c-.24 1.285-1.457 2.167-2.742 1.927a2.025 2.025 0 0 1-1.488-2.378Zm0 0-2.488 4.31m9.22-4.31a2.025 2.025 0 0 1 2.378-1.488c1.285.24 2.167 1.457 1.927 2.742a2.025 2.025 0 0 1-2.378 1.488c-1.285-.24-2.167-1.457-1.927-2.742Zm0 0 2.488 4.31M12 4.5v15" />
                                </svg>
                            </a>
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

<!-- Modal Import Data Pegawai (Reusable Helper) -->
<?= ModalHelper::importModal([
    'id' => 'modal-import-pegawai',
    'title' => 'Import Data Pegawai',
    'subtitle' => 'Unggah file Excel / CSV data pegawai untuk menambahkan pegawai secara massal.',
    'action' => url('kelola-pegawai/import'),
    'templateUrl' => url('kelola-pegawai/template'),
    'templateName' => 'Template_Import_Pegawai.csv',
    'instructions' => [
        'Gunakan file template resmi yang telah disiapkan dengan menekan tombol Download di atas.',
        'Kolom "Nama Lengkap" wajib diisi pada setiap baris data.',
        'Format tanggal lahir: YYYY-MM-DD (Contoh: 1990-05-12).',
        'Format jenis kelamin: L (Laki-laki) atau P (Perempuan).',
        'Field NPWP, Email, No. WhatsApp, Gelar, NIY, NIK, dan Alamat opsional namun disarankan diisi lengkap.'
    ],
    'acceptedFormats' => '.csv, .xls, .xlsx',
    'maxFileSizeMb' => 10
]) ?>
