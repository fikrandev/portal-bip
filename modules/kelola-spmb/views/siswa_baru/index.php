<?php
/**
 * View Data Siswa Baru SPMB
 * Portal BIP
 */
$activeGroup = $activeGroup ?? null;
$groups = $groups ?? (class_exists('SpmbModel') ? SpmbModel::getAllGroups() : []);
$autoNomor = $autoNomor ?? (class_exists('SpmbModel') ? SpmbModel::generateNomorPendaftaran('Siswa Baru', $activeGroup['tahun_ajaran'] ?? null, 'PAUD') : 'PAUD-001');
$list = $pendaftarList ?? [];
$filters = $filters ?? [];
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Data Calon Siswa Baru</h1>
                <?php if ($activeGroup): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <?= e($activeGroup['nama_group']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-xs sm:text-sm text-slate-500">
                Pendaftaran murid baru reguler tahun pelajaran aktif. Data otomatis terhubung ke group SPMB aktif.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="<?= url('kelola-spmb/pengumuman-kelulusan/cetak?jenjang=' . (!empty($filters['jenjang']) ? $filters['jenjang'] : 'SD') . '&jenis=Siswa Baru') ?>" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-semibold text-xs transition-colors" title="Cetak Surat Keputusan Pengumuman Kelulusan Resmi Format PDF">
                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                <span>Cetak SK Pengumuman (PDF)</span>
            </a>
            <button type="button" onclick="bukaModalBroadcastKelulusan()"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 font-semibold text-xs transition-colors cursor-pointer" title="Bagikan ke Grup WA atau Broadcast Japri">
                <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
                <span>Broadcast & Pengumuman WA</span>
            </button>
            <button type="button" onclick="bukaModalBulkAccept()"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                <span>🎓</span>
                <span>Tetapkan Siswa Diterima</span>
            </button>
            <a href="<?= url('kelola-spmb/siswa-baru/export?' . http_build_query($filters)) ?>" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                <span>Export Excel</span>
            </a>
            <button type="button" onclick="openModalTambahSiswaBaru()" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-semibold text-xs shadow-md shadow-sky-600/20 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah Siswa Baru</span>
            </button>
        </div>
    </div>

    <!-- Quick Tabs Status Berkas & Kelulusan -->
    <div class="bg-white rounded-2xl p-1.5 border border-slate-200/80 shadow-xs flex items-center gap-1.5 overflow-x-auto">
        <?php
        $currBerkas = $filters['status_berkas'] ?? '';
        $currLulus = $filters['status_kelulusan'] ?? '';
        $tabs = [
            'semua'             => ['label' => 'Semua Pendaftar', 'icon' => '👥', 'berkas' => '', 'kelulusan' => ''],
            'Belum Dikembalikan'=> ['label' => 'Menunggu Pengembalian', 'icon' => '⏳', 'berkas' => 'Belum Dikembalikan', 'kelulusan' => ''],
            'Lengkap'           => ['label' => 'Berkas Lengkap', 'icon' => '📁', 'berkas' => 'Lengkap', 'kelulusan' => ''],
            'Perlu Perbaikan'   => ['label' => 'Perlu Perbaikan', 'icon' => '⚠️', 'berkas' => 'Perlu Perbaikan', 'kelulusan' => ''],
            'Lulus'             => ['label' => '🎓 Fix Diterima (Lulus)', 'icon' => '🎓', 'berkas' => '', 'kelulusan' => 'Lulus / Diterima'],
        ];
        foreach ($tabs as $tKey => $tData):
            if ($tKey === 'Lulus') {
                $isActive = ($currLulus === 'Lulus / Diterima');
            } elseif ($tKey === 'semua') {
                $isActive = empty($currBerkas) && empty($currLulus);
            } else {
                $isActive = ($currBerkas === $tData['berkas']) && empty($currLulus);
            }
            $cleanParams = $_GET;
            unset($cleanParams['status_berkas'], $cleanParams['status_kelulusan'], $cleanParams['page']);
            if (!empty($tData['berkas'])) $cleanParams['status_berkas'] = $tData['berkas'];
            if (!empty($tData['kelulusan'])) $cleanParams['status_kelulusan'] = $tData['kelulusan'];
            $cleanParams['page'] = 1;
            $queryUrl = http_build_query($cleanParams);
        ?>
            <a href="?<?= $queryUrl ?>" 
               class="flex items-center gap-2 py-2 px-3.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= $isActive ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                <span><?= $tData['icon'] ?></span>
                <span><?= $tData['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm">
        <form method="GET" action="<?= url('kelola-spmb/siswa-baru') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Nama / No. Daftar / NISN</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Ketik kata kunci..." 
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
            </div>

            <!-- Jenjang -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Jenjang</label>
                <select name="jenjang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none bg-white">
                    <option value="">Semua Jenjang</option>
                    <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $j): ?>
                        <option value="<?= $j ?>" <?= (($filters['jenjang'] ?? '') === $j) ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kategori Pendaftar -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Kategori Pendaftar</label>
                <select name="kategori_pendaftar" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none bg-white font-medium text-slate-700">
                    <option value="">Semua Kategori</option>
                    <option value="Umum (Luar Bina Insan)" <?= (($filters['kategori_pendaftar'] ?? '') === 'Umum (Luar Bina Insan)') ? 'selected' : '' ?>>Umum (Luar Bina Insan)</option>
                    <option value="Keluarga Bina Insan (Adik / Saudara)" <?= (($filters['kategori_pendaftar'] ?? '') === 'Keluarga Bina Insan (Adik / Saudara)') ? 'selected' : '' ?>>Keluarga Bina Insan (Adik/Saudara)</option>
                </select>
            </div>

            <!-- Status TTD Komitmen (Wajib) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status TTD Komitmen</label>
                <select name="status_ttd_komitmen" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none bg-white font-medium text-slate-700">
                    <option value="">Semua Status TTD</option>
                    <option value="Sudah TTD" <?= (($filters['status_ttd_komitmen'] ?? '') === 'Sudah TTD') ? 'selected' : '' ?>>✓ Sudah TTD</option>
                    <option value="Belum TTD" <?= (($filters['status_ttd_komitmen'] ?? '') === 'Belum TTD') ? 'selected' : '' ?>>⚠️ Belum TTD (Wajib)</option>
                </select>
            </div>

            <!-- Status Pengembalian Berkas -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Berkas</label>
                <select name="status_berkas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none bg-white font-medium text-slate-700">
                    <option value="">Semua Berkas</option>
                    <option value="Belum Dikembalikan" <?= (($filters['status_berkas'] ?? '') === 'Belum Dikembalikan') ? 'selected' : '' ?>>⏳ Belum Dikembalikan</option>
                    <option value="Lengkap" <?= (($filters['status_berkas'] ?? '') === 'Lengkap') ? 'selected' : '' ?>>✓ Lengkap / Diterima</option>
                    <option value="Perlu Perbaikan" <?= (($filters['status_berkas'] ?? '') === 'Perlu Perbaikan') ? 'selected' : '' ?>>⚠️ Perlu Perbaikan</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold transition-colors">
                    Filter
                </button>
                <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Selected Items Actions Bar (Muncul saat ada checkbox dicentang) -->
    <div id="selectedActionsBar" class="hidden bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white p-4 rounded-2xl shadow-lg border border-emerald-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-in fade-in slide-in-from-top-2 duration-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-lg shrink-0">
                🎓
            </div>
            <div>
                <strong class="text-sm font-extrabold tracking-tight"><span id="selectedItemsCount">0</span> Calon Siswa Terpilih</strong>
                <p class="text-[11px] text-emerald-100">Tetapkan siswa yang dicentang menjadi resmi diterima (lulus seleksi).</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" onclick="bukaModalBulkAccept()" class="px-4 py-2 bg-white text-emerald-800 hover:bg-emerald-50 rounded-xl text-xs font-extrabold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                <span>✓</span>
                <span>Tetapkan Diterima</span>
            </button>
            <button type="button" onclick="document.querySelectorAll('.cb-pendaftar-item').forEach(c => c.checked = false); const m = document.getElementById('cbSelectAllMaster'); if (m) m.checked = false; updateBulkBarState();" class="px-3 py-2 bg-black/20 hover:bg-black/30 text-white rounded-xl text-xs font-semibold transition-all cursor-pointer">
                Batal Pilihan
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Menampilkan <strong><?= count($list) ?></strong> dari <strong><?= $totalRows ?? count($list) ?></strong> calon siswa</span>
            <span class="text-sky-600 font-semibold">Tahun Aktif: <?= e($activeGroup['nama_group'] ?? '-') ?></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-3 w-10 text-center">
                            <input type="checkbox" id="cbSelectAllMaster" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer" title="Centang / Batal Semua">
                        </th>
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-4">No. Pendaftaran</th>
                        <th class="py-3 px-4">Calon Siswa</th>
                        <th class="py-3 px-4">Jenjang</th>
                        <th class="py-3 px-4">Kontak Ortu</th>
                        <th class="py-3 px-4">Tgl Daftar</th>
                        <th class="py-3 px-4">Berkas</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Kategori Pendaftar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada data Siswa Baru</p>
                                    <p class="text-[11px] text-slate-400">Gunakan tombol di atas untuk mendaftarkan calon siswa baru ke group aktif.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = ($currentPage - 1) * $perPage + 1;
                        foreach ($list as $item): 
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox" class="cb-pendaftar-item w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer" value="<?= $item['id'] ?>" onchange="updateBulkBarState()">
                                </td>
                                <td class="py-3 px-3 text-center font-medium text-slate-400"><?= $no++ ?></td>
                                <td class="py-3 px-4">
                                    <a href="<?= url('kelola-spmb/siswa-baru/detail/' . $item['id']) ?>" class="font-mono font-bold text-sky-700 hover:underline">
                                        <?= e($item['nomor_pendaftaran']) ?>
                                    </a>
                                    <div class="text-[10px] text-slate-400"><?= e($item['gelombang'] ?? 'Gelombang 1') ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800"><?= e($item['nama_lengkap']) ?></div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= ($item['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan' ?> &bull; NISN: <?= e($item['nisn'] ?: '-') ?>
                                    </div>
                                    <?php if (!empty($item['asal_sekolah'])): ?>
                                        <div class="text-[10px] text-slate-500 italic mt-0.5">Asal: <?= e($item['asal_sekolah']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        <?= e($item['jenjang']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-700"><?= e($item['nama_ayah'] ?: $item['nama_ibu'] ?: '-') ?></div>
                                    <div class="text-[11px] text-slate-500"><?= e($item['no_hp'] ?: $item['no_hp_ayah'] ?: '-') ?></div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($item['tanggal_daftar'])) ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        <!-- Tombol Cek & Verifikasi Pengembalian Berkas Fisik -->
                                        <div>
                                            <?php
                                            $berkasSt = $item['status_berkas'] ?? 'Belum Dikembalikan';
                                            $berkasBadge = match($berkasSt) {
                                                'Lengkap'         => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                                'Perlu Perbaikan' => 'bg-amber-100 text-amber-800 border-amber-300 font-bold',
                                                default           => 'bg-slate-100 text-slate-600 border-slate-300 font-medium'
                                            };
                                            $berkasIcon = match($berkasSt) {
                                                'Lengkap'         => '✓ Berkas Lengkap',
                                                'Perlu Perbaikan' => '⚠️ Perlu Perbaikan',
                                                default           => '⏳ Belum Kembali'
                                            };
                                            ?>
                                            <button type="button" onclick="bukaModalVerifikasi(<?= $item['id'] ?>)" 
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] border <?= $berkasBadge ?> hover:opacity-85 transition-opacity cursor-pointer shadow-2xs"
                                                    title="Klik untuk Cek & Verifikasi Pengembalian Berkas">
                                                <span><?= $berkasIcon ?></span>
                                                <?php if (!empty($item['surat_kesanggupan'])): ?>
                                                    <span class="text-[9px] bg-white/70 px-1 rounded-sm ml-0.5">📎</span>
                                                <?php endif; ?>
                                            </button>
                                        </div>
                                        <!-- Penanda Wajib: Status TTD Komitmen Orang Tua -->
                                        <div>
                                            <?php
                                            $ttdSt = $item['status_ttd_komitmen'] ?? 'Belum TTD';
                                            if ($ttdSt === 'Sudah TTD'): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Surat Komitmen telah ditandatangani secara sah (Terenkripsi)">
                                                    <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>✓ TTD Komitmen</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-300 animate-pulse" title="PERHATIAN WAJIB: Orang Tua/Wali Belum TTD Surat Pernyataan Komitmen!">
                                                    <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>⚠️ Belum TTD</span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Status Pengisian Biodata -->
                                        <div>
                                            <?php
                                            $bioSt = $item['biodata_status'] ?? 'belum_diisi';
                                            $bioBadge = match($bioSt) {
                                                'lengkap' => 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold',
                                                'draft'   => 'bg-amber-50 text-amber-700 border border-amber-200 font-semibold',
                                                default   => 'bg-slate-50 text-slate-500 border border-slate-200'
                                            };
                                            $bioLabel = match($bioSt) {
                                                'lengkap' => '✓ Biodata Lengkap',
                                                'draft'   => '⏱ Biodata Draft',
                                                default   => 'Biodata Belum Diisi'
                                            };
                                            ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] <?= $bioBadge ?>">
                                                <?= $bioLabel ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <!-- Kolom Status Kelulusan (BARU) -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php
                                    $stLulus = $item['status_kelulusan'] ?? 'Menunggu Seleksi';
                                    if (empty($stLulus)) $stLulus = 'Menunggu Seleksi';

                                    $statusBadge = match($stLulus) {
                                        'Lulus / Diterima' => [
                                            'bg'    => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                            'icon'  => '🎓',
                                            'label' => 'Lulus / Diterima'
                                        ],
                                        'Tidak Lulus' => [
                                            'bg'    => 'bg-rose-100 text-rose-800 border-rose-300',
                                            'icon'  => '✕',
                                            'label' => 'Tidak Lulus'
                                        ],
                                        'Lolos Berkas' => [
                                            'bg'    => 'bg-blue-100 text-blue-800 border-blue-300',
                                            'icon'  => '📋',
                                            'label' => 'Lolos Berkas'
                                        ],
                                        'Cadangan' => [
                                            'bg'    => 'bg-amber-100 text-amber-800 border-amber-300',
                                            'icon'  => '⏳',
                                            'label' => 'Cadangan'
                                        ],
                                        'Dibatalkan' => [
                                            'bg'    => 'bg-slate-100 text-slate-700 border-slate-300',
                                            'icon'  => '🚫',
                                            'label' => 'Dibatalkan'
                                        ],
                                        default => [
                                            'bg'    => 'bg-slate-100 text-slate-600 border-slate-200',
                                            'icon'  => '⏳',
                                            'label' => 'Menunggu Seleksi'
                                        ]
                                    };
                                    ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold border shadow-2xs <?= $statusBadge['bg'] ?>" title="Status Kelulusan: <?= e($stLulus) ?>">
                                        <span><?= $statusBadge['icon'] ?></span>
                                        <span><?= e($statusBadge['label']) ?></span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php 
                                    $kat = $item['kategori_pendaftar'] ?: ($item['jalur_pendaftaran'] ?: 'Umum (Luar Bina Insan)');
                                    $isKeluarga = str_contains(strtolower($kat), 'keluarga') || str_contains(strtolower($kat), 'adik') || str_contains(strtolower($kat), 'saudara');
                                    ?>
                                    <?php if ($isKeluarga): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 shadow-sm">
                                            <span>👨‍👩‍👦</span>
                                            <span>Keluarga Bina Insan</span>
                                        </span>
                                        <div class="text-[10px] text-purple-600 font-medium mt-0.5 pl-1">Adik / Saudara</div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 shadow-sm">
                                            <span>🌐</span>
                                            <span>Umum</span>
                                        </span>
                                        <div class="text-[10px] text-slate-500 font-medium mt-0.5 pl-1">Luar Bina Insan</div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="relative inline-block text-left action-menu-container">
                                        <!-- Tombol Titik Tiga (3 Dots) -->
                                        <button type="button" 
                                                onclick="toggleActionMenu(<?= $item['id'] ?>, event)"
                                                id="btn-action-<?= $item['id'] ?>"
                                                title="Pilihan Aksi"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 transition-all shadow-xs focus:outline-none focus:ring-2 focus:ring-sky-500">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                            </svg>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div id="menu-<?= $item['id'] ?>" 
                                             class="action-menu-dropdown hidden absolute right-0 z-30 w-56 rounded-xl bg-white shadow-xl ring-1 ring-slate-900/10 divide-y divide-slate-100 text-left transition-all">
                                            
                                            <!-- Action: Kirim WA Kelulusan jika sudah diterima -->
                                            <?php if (($item['status_kelulusan'] ?? '') === 'Lulus / Diterima'): ?>
                                                <div class="p-1">
                                                    <button type="button" 
                                                            onclick="kirimWaKelulusanSingle(<?= $item['id'] ?>, '<?= addslashes(e($item['nama_lengkap'])) ?>')"
                                                            class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors group">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-sm">📲</span>
                                                            <span>Kirim WA Kelulusan</span>
                                                        </div>
                                                        <?php if (!empty($item['wa_kelulusan_sent_at'])): ?>
                                                            <span class="text-[9px] font-semibold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Terkirim ✓</span>
                                                        <?php endif; ?>
                                                    </button>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Quick Primary Action: Cek & Terima Berkas -->
                                            <div class="p-1">
                                                <button type="button" 
                                                        onclick="bukaModalVerifikasi(<?= $item['id'] ?>)"
                                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-sky-700 hover:bg-sky-50 rounded-lg transition-colors group">
                                                    <span class="w-6 h-6 rounded-md bg-sky-100 text-sky-700 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">📁</span>
                                                    <span>Cek & Terima Berkas</span>
                                                </button>
                                            </div>

                                            <!-- Detail & Edit -->
                                            <div class="p-1 space-y-0.5">
                                                <a href="<?= url('kelola-spmb/siswa-baru/detail/' . $item['id']) ?>" 
                                                   class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                    <span>Lihat Detail Pendaftar</span>
                                                </a>
                                                <a href="<?= url('kelola-spmb/siswa-baru/edit/' . $item['id']) ?>" 
                                                   class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                                    <span>Edit Data Siswa</span>
                                                </a>
                                            </div>

                                            <!-- Ubah Status Seleksi Satuan -->
                                            <div class="p-1 space-y-0.5">
                                                <div class="px-3 py-1 text-[9.5px] font-extrabold text-slate-400 uppercase tracking-wider">Ubah Status Seleksi</div>
                                                <?php if (($item['status_kelulusan'] ?? '') !== 'Lulus / Diterima'): ?>
                                                    <form method="POST" action="<?= url('kelola-spmb/siswa-baru/status/' . $item['id']) ?>" onsubmit="return confirm('Tetapkan calon siswa <?= addslashes(e($item['nama_lengkap'])) ?> sebagai LULUS / DITERIMA?');">
                                                        <?= \Core\CSRF::token() ?>
                                                        <input type="hidden" name="status_kelulusan" value="Lulus / Diterima">
                                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors text-left cursor-pointer">
                                                            <span>🎓</span>
                                                            <span>Tetapkan Lulus / Diterima</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (($item['status_kelulusan'] ?? '') !== 'Tidak Lulus'): ?>
                                                    <form method="POST" action="<?= url('kelola-spmb/siswa-baru/status/' . $item['id']) ?>" onsubmit="return confirm('Tetapkan calon siswa <?= addslashes(e($item['nama_lengkap'])) ?> sebagai TIDAK LULUS?');">
                                                        <?= \Core\CSRF::token() ?>
                                                        <input type="hidden" name="status_kelulusan" value="Tidak Lulus">
                                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-50 rounded-lg transition-colors text-left cursor-pointer">
                                                            <span>✕</span>
                                                            <span>Tetapkan Tidak Lulus</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (!empty($item['status_kelulusan']) && $item['status_kelulusan'] !== 'Menunggu Seleksi'): ?>
                                                    <form method="POST" action="<?= url('kelola-spmb/siswa-baru/status/' . $item['id']) ?>" onsubmit="return confirm('Kembalikan status calon siswa ke Menunggu Seleksi?');">
                                                        <?= \Core\CSRF::token() ?>
                                                        <input type="hidden" name="status_kelulusan" value="Menunggu Seleksi">
                                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-left cursor-pointer">
                                                            <span>⏳</span>
                                                            <span>Set Menunggu Seleksi</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Cetak Dokumen -->
                                            <div class="p-1 space-y-0.5">
                                                <a href="<?= url('kelola-spmb/siswa-baru/cetak/' . $item['id'] . '?mode=biodata') ?>" target="_blank"
                                                   class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5Z" /></svg>
                                                    <span>Cetak Formulir Pendaftaran</span>
                                                </a>
                                                <a href="<?= url('kelola-spmb/siswa-baru/cetak/' . $item['id'] . '?mode=kwitansi-2') ?>" target="_blank"
                                                   class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                                                    <span>Cetak Bukti Pendaftaran</span>
                                                </a>
                                                <a href="<?= url('kelola-spmb/surat-pernyataan/cetak/' . $item['id']) ?>" target="_blank"
                                                   class="w-full flex items-center justify-between px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors">
                                                    <div class="flex items-center gap-2.5">
                                                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        <span>Surat Komitmen</span>
                                                    </div>
                                                    <?php if (($item['status_ttd_komitmen'] ?? '') === 'Sudah TTD'): ?>
                                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">TTD ✓</span>
                                                    <?php else: ?>
                                                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Belum</span>
                                                    <?php endif; ?>
                                                </a>
                                            </div>

                                            <!-- Tautan Publik & Berbagi -->
                                            <div class="p-1 space-y-0.5">
                                                <button type="button" 
                                                        onclick="salinLinkKomitmen('<?= url('spmb/komitmen/' . ($item['biodata_token'] ?? '')) ?>')"
                                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-purple-700 hover:bg-purple-50 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                                    <span>Salin Link TTD Ortu (WA)</span>
                                                </button>
                                                <button type="button" 
                                                        onclick="salinLinkPublic('<?= url('spmb/biodata/' . ($item['biodata_token'] ?? '')) ?>')"
                                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-teal-700 hover:bg-teal-50 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                                                    <span>Salin Link Biodata</span>
                                                </button>
                                            </div>

                                            <!-- Danger Zone -->
                                            <div class="p-1">
                                                <button type="button" 
                                                        onclick="confirmDelete(<?= $item['id'] ?>, '<?= e(addslashes($item['nama_lengkap'])) ?>')"
                                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                    <span>Hapus Data Pendaftar</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Halaman <?= $currentPage ?> dari <?= $totalPages ?></span>
                <div class="flex items-center gap-1">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= url('kelola-spmb/siswa-baru?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>" 
                           class="w-7 h-7 flex items-center justify-center rounded-lg font-bold <?= $i === $currentPage ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: TAMBAH SISWA BARU (1 COLUMN - DATA AWAL)                           -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalTambahSiswaBaru" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-5 overflow-y-auto">
    <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col my-auto max-h-[92vh] animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Modal Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-sky-600 via-sky-700 to-blue-700 text-white flex items-center justify-between shrink-0 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white shrink-0 border border-white/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-extrabold tracking-tight">Formulir Siswa Baru</h3>
                        <?php if ($activeGroup): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400 text-emerald-950 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-950 animate-pulse"></span>
                                <?= e($activeGroup['nama_group']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-[11px] text-sky-100/90 mt-0.5">Input data awal calon murid baru reguler</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahSiswaBaru()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form Body (1 Column Clean Flow) -->
        <form id="formTambahSiswaBaru" action="<?= url('kelola-spmb/siswa-baru/store') ?>" method="POST" enctype="multipart/form-data" data-no-loading="true" data-confirm="true" onsubmit="event.preventDefault(); submitFormTambahSiswaBaru(event); return false;" class="flex flex-col flex-1 overflow-hidden">
            <?= CSRF::field() ?>
            <input type="hidden" name="is_ajax" value="1">
            <input type="hidden" name="jenis_pendaftaran" value="Siswa Baru">
            <input type="hidden" name="group_id" value="<?= $activeGroup['id'] ?? 1 ?>">

            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs text-slate-700">
                
                <!-- 1. Jenjang -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Jenjang Sekolah Tujuan <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenjang" id="modal_jenjang" onchange="onJenjangChange()" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                        <option value="PAUD" selected>PAUD</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>

                <!-- 2. Kategori Pendaftar (Umum vs Keluarga Bina Insan) -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Kategori Pendaftar <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-all has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/50 has-[:checked]:ring-1 has-[:checked]:ring-sky-500">
                            <input type="radio" name="kategori_pendaftar" id="kat_umum" value="Umum (Luar Bina Insan)" checked class="text-sky-600 focus:ring-sky-500">
                            <div>
                                <div class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                    <span>🌐</span> Umum
                                </div>
                                <div class="text-[10px] text-slate-500">Pendaftar Luar Bina Insan</div>
                            </div>
                        </label>
                        <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50/50 has-[:checked]:ring-1 has-[:checked]:ring-purple-500">
                            <input type="radio" name="kategori_pendaftar" id="kat_keluarga" value="Keluarga Bina Insan (Adik / Saudara)" class="text-purple-600 focus:ring-purple-500">
                            <div>
                                <div class="font-bold text-xs text-purple-900 flex items-center gap-1.5">
                                    <span>👨‍👩‍👦</span> Keluarga Bina Insan
                                </div>
                                <div class="text-[10px] text-purple-600 font-medium">Adik / Saudara Siswa BIP</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 2. No Formulir Auto (Unit-001) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800">
                            No. Formulir <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-sky-600 font-semibold bg-sky-50 px-2 py-0.5 rounded-md">
                            Otomatis (Format: Unit-001)
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" name="nomor_pendaftaran" id="modal_nomor_pendaftaran" value="<?= e($autoNomor) ?>" required 
                               class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 bg-sky-50/70 font-mono font-bold text-sky-800 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                        <button type="button" onclick="onJenjangChange()" title="Generate Ulang Nomor" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-sky-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Otomatis berubah mengikuti pilihan jenjang (misal PAUD-001, SD-001, SMP-001, SMA-001).</p>
                </div>

                <!-- 3. Gelombang (Atur di Pengaturan) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800">
                            Gelombang Pendaftaran
                        </label>
                        <span class="text-[10px] text-slate-400">Dari Pengaturan Group</span>
                    </div>
                    <input type="text" name="gelombang" id="modal_gelombang" 
                           value="<?= e($activeGroup['gelombang_default'] ?? 'Gelombang 1') ?>" 
                           placeholder="Contoh: Gelombang 1"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-800 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 4. Nama Lengkap -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Nama Lengkap Siswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_lengkap" id="modal_nama_lengkap" required 
                           placeholder="Nama lengkap calon siswa sesuai akta..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 5. Jenis Kelamin -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_kelamin" id="modal_jenis_kelamin" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                        <option value="L" selected>Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <!-- 6. Tempat Lahir -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Tempat Lahir
                    </label>
                    <input type="text" name="tempat_lahir" id="modal_tempat_lahir" placeholder="Contoh: Palu" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 7. Tanggal Lahir -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="tanggal_lahir" id="modal_tanggal_lahir" onchange="hitungUmurOtomatis(this.value)" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 8. Umur (Auto) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800">
                            Umur Siswa <span class="text-[10px] font-normal text-slate-500">(Per <?= !empty($activeGroup['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($activeGroup['tanggal_patokan_umur']) : '1 Juli 2027' ?>)</span>
                        </label>
                        <span id="badge_umur" class="hidden text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">
                            Otomatis Dihitung
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" name="umur" id="modal_umur" readonly 
                               placeholder="Otomatis terisi saat tanggal lahir dipilih" 
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 outline-none text-xs cursor-default">
                        <span class="absolute left-3 top-2.5 text-slate-400">🎂</span>
                    </div>
                </div>

                <!-- 9. Asal Sekolah -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Asal Sekolah
                    </label>
                    <input type="text" name="asal_sekolah" id="modal_asal_sekolah" placeholder="Nama TK / PAUD sebelumnya..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 10. Nama Ayah -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Nama Ayah
                    </label>
                    <input type="text" name="nama_ayah" id="modal_nama_ayah" placeholder="Nama lengkap ayah kandung..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 11. Nama Ibu -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Nama Ibu
                    </label>
                    <input type="text" name="nama_ibu" id="modal_nama_ibu" placeholder="Nama lengkap ibu kandung..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 12. No WA -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        No. WhatsApp / HP Orang Tua <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="no_hp" id="modal_no_hp" required placeholder="08xxxxxxxxxx" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-mono focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                </div>

                <!-- 13. Alamat -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-800 mb-1">
                        Alamat Tempat Tinggal
                    </label>
                    <textarea name="alamat" id="modal_alamat" rows="2" placeholder="Nama jalan, nomor rumah, kelurahan, perumahan..." 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs"></textarea>
                </div>

                <!-- 14. Informasi Biaya Pendaftaran Awal (Gratis / Rp 0) -->
                <div class="sm:col-span-2 p-3.5 bg-emerald-50/80 border border-emerald-200/90 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                            ✓
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-emerald-950">Biaya Pendaftaran Awal: Bebas Biaya</span>
                            <span class="block text-[11px] text-emerald-700">Tahap awal pendaftaran tidak dipungut biaya formulir (Gratis).</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm shrink-0">
                        Rp 0 (Gratis)
                    </span>
                    <input type="hidden" name="biaya_pendaftaran" id="modal_biaya" value="0">
                    <input type="hidden" name="status_pembayaran" value="Gratis">
                    <input type="hidden" name="metode_pembayaran" value="Gratis">
                </div>

            </div>

            <!-- Modal Footer Actions -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0 gap-3">
                <span class="text-[11px] text-slate-500 italic">
                    * Kolom wajib diisi
                </span>
                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" onclick="closeModalTambahSiswaBaru()" 
                            class="px-4 py-2.5 rounded-xl bg-slate-200/80 hover:bg-slate-300/80 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="btnSubmitModalSiswa" onclick="submitFormTambahSiswaBaru(event)" 
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Simpan & Daftarkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Pastikan SweetAlert2 selalu berada di layer paling atas melebihi semua backdrop dan modal */
.swal2-container {
    z-index: 100000000 !important;
}
</style>

<script>
// Modal Controls
function openModalTambahSiswaBaru() {
    const modal = document.getElementById('modalTambahSiswaBaru');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const inputNama = document.getElementById('modal_nama_lengkap');
            if (inputNama) inputNama.focus();
        }, 100);
    }
}

function closeModalTambahSiswaBaru() {
    const modal = document.getElementById('modalTambahSiswaBaru');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

// Close on backdrop click & ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModalTambahSiswaBaru();
    }
});

const modalContainer = document.getElementById('modalTambahSiswaBaru');
if (modalContainer) {
    modalContainer.addEventListener('click', function(e) {
        if (e.target === modalContainer) {
            closeModalTambahSiswaBaru();
        }
    });
}

// 1. On Jenjang Change -> Update No. Formulir (Unit-001)
function onJenjangChange() {
    const jenjangSelect = document.getElementById('modal_jenjang');
    const noInput = document.getElementById('modal_nomor_pendaftaran');
    if (!jenjangSelect || !noInput) return;

    const jenjangVal = jenjangSelect.value;
    noInput.classList.add('animate-pulse');

    fetch('<?= url('kelola-spmb/siswa-baru/get-nomor?jenjang=') ?>' + encodeURIComponent(jenjangVal))
        .then(res => res.json())
        .then(data => {
            if (data && data.nomor) {
                noInput.value = data.nomor;
            }
        })
        .catch(() => {
            noInput.value = jenjangVal + '-001';
        })
        .finally(() => {
            noInput.classList.remove('animate-pulse');
        });
}

// 2. Auto Calculate Umur (Berdasarkan Patokan Umur Group Aktif)
const PATOKAN_UMUR_MODAL = '<?= e($activeGroup['tanggal_patokan_umur'] ?? '2027-07-01') ?>';

function hitungUmurOtomatis(tanggalLahirStr) {
    const umurInput = document.getElementById('modal_umur');
    const badgeUmur = document.getElementById('badge_umur');
    if (!umurInput) return;

    if (!tanggalLahirStr) {
        umurInput.value = '';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    const tglParts = tanggalLahirStr.split('-');
    if (tglParts.length < 3) return;
    const tglLahir = new Date(parseInt(tglParts[0], 10), parseInt(tglParts[1], 10) - 1, parseInt(tglParts[2], 10));

    const patokanParts = PATOKAN_UMUR_MODAL.split('-');
    const patokan = new Date(parseInt(patokanParts[0], 10), parseInt(patokanParts[1], 10) - 1, parseInt(patokanParts[2], 10));

    if (isNaN(tglLahir.getTime()) || isNaN(patokan.getTime())) {
        umurInput.value = '';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    if (tglLahir > patokan) {
        umurInput.value = 'Tanggal belum tiba';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    let years = patokan.getFullYear() - tglLahir.getFullYear();
    let months = patokan.getMonth() - tglLahir.getMonth();
    let days = patokan.getDate() - tglLahir.getDate();

    if (days < 0) {
        months--;
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    if (years < 0) {
        umurInput.value = 'Tanggal belum tiba';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    let hasil = years + ' Tahun';
    if (months > 0) {
        hasil += ' ' + months + ' Bulan';
    }

    umurInput.value = hasil;
    if (badgeUmur) {
        badgeUmur.innerText = hasil;
        badgeUmur.classList.remove('hidden');
    }
}

// Format Rupiah Input Helper
function formatRupiahInput(el, hiddenId) {
    let clean = el.value.replace(/[^0-9]/g, '');
    let hidden = document.getElementById(hiddenId);
    if (!clean) {
        el.value = '0';
        if (hidden) hidden.value = 0;
        return;
    }
    let num = parseInt(clean, 10);
    el.value = new Intl.NumberFormat('id-ID').format(num);
    if (hidden) hidden.value = num;
}

// 3. On Jenis Pembayaran Change -> Update Biaya Pendaftaran
function onJenisPembayaranChange() {
    const jenisSelect = document.getElementById('modal_jenis_pembayaran');
    const displayInput = document.getElementById('modal_biaya_display');
    const hiddenInput = document.getElementById('modal_biaya');
    const infoText = document.getElementById('info_pembayaran');
    if (!jenisSelect || !displayInput || !hiddenInput) return;

    const val = jenisSelect.value;
    const defaultBiaya = parseFloat(displayInput.getAttribute('data-default-biaya') || '250000');

    if (val === 'Free') {
        displayInput.value = '0';
        displayInput.readOnly = true;
        displayInput.classList.add('bg-slate-100', 'text-slate-500');
        hiddenInput.value = 0;
        if (infoText) infoText.innerHTML = '<span class="text-emerald-600 font-bold">✓ Pendaftaran bebas biaya formulir (Gratis).</span>';
    } else {
        displayInput.readOnly = false;
        displayInput.classList.remove('bg-slate-100', 'text-slate-500');
        hiddenInput.value = defaultBiaya;
        displayInput.value = new Intl.NumberFormat('id-ID').format(defaultBiaya);
        if (infoText) {
            infoText.innerText = val === 'Transfer' 
                ? 'Pembayaran biaya pendaftaran via transfer bank.' 
                : 'Pembayaran biaya formulir secara tunai di tempat.';
        }
    }
}

// Submit Form Handler dengan Konfirmasi Data & Otomatis Cetak
function submitFormTambahSiswaBaru(e) {
    if (e && e.preventDefault) e.preventDefault();

    // Sembunyikan loading modal global jika sempat terpanggil
    if (window.LoadingModal && typeof LoadingModal.hide === 'function') {
        LoadingModal.hide();
    }

    const form = document.getElementById('formTambahSiswaBaru');
    const btnSubmit = document.getElementById('btnSubmitModalSiswa');

    // Validasi dasar
    const namaLengkap = (document.getElementById('modal_nama_lengkap')?.value || '').trim();
    if (!namaLengkap) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Siswa Wajib Diisi',
            text: 'Silakan masukkan nama lengkap calon peserta didik baru.',
            confirmButtonColor: '#0284c7'
        });
        document.getElementById('modal_nama_lengkap')?.focus();
        return;
    }

    const noHp = (document.getElementById('modal_no_hp')?.value || '').trim();
    if (!noHp) {
        Swal.fire({
            icon: 'warning',
            title: 'No. WhatsApp Wajib Diisi',
            text: 'Silakan masukkan nomor WhatsApp orang tua untuk konfirmasi pendaftaran.',
            confirmButtonColor: '#0284c7'
        });
        document.getElementById('modal_no_hp')?.focus();
        return;
    }

    // Kumpulkan data untuk ditampilkan di Konfirmasi
    const jenjang = document.getElementById('modal_jenjang')?.value || 'PAUD';
    const katPendaftar = document.querySelector('input[name="kategori_pendaftar"]:checked')?.value || 'Umum (Luar Bina Insan)';
    const noFormulir = document.getElementById('modal_nomor_pendaftaran')?.value || '-';
    const gelombang = document.getElementById('modal_gelombang')?.value || '-';
    const jkVal = document.getElementById('modal_jenis_kelamin')?.value || 'L';
    const jkText = jkVal === 'L' ? 'Laki-laki' : 'Perempuan';
    const tempatLahir = document.getElementById('modal_tempat_lahir')?.value?.trim() || '';
    const tglLahir = document.getElementById('modal_tanggal_lahir')?.value || '';
    const umur = document.getElementById('modal_umur')?.value?.trim() || '-';
    const asalSekolah = document.getElementById('modal_asal_sekolah')?.value?.trim() || '-';
    const namaAyah = document.getElementById('modal_nama_ayah')?.value?.trim() || '-';
    const namaIbu = document.getElementById('modal_nama_ibu')?.value?.trim() || '-';
    const alamat = document.getElementById('modal_alamat')?.value?.trim() || '-';

    let ttlText = '-';
    if (tempatLahir || tglLahir) {
        ttlText = (tempatLahir || '') + (tglLahir ? (tempatLahir ? ', ' : '') + tglLahir : '');
    }

    // 1. POPUP KONFIRMASI DATA SEBELUM SIMPAN (SweetAlert2 Prioritas Teratas)
    Swal.fire({
        title: 'Konfirmasi Data Calon Siswa',
        html: `
            <div class="text-left text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1.5 mt-2">
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Jenjang & No. Formulir:</span>
                    <span class="font-bold text-slate-800">${jenjang} &bull; <span class="font-mono text-sky-700 font-bold">${noFormulir}</span></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Kategori Pendaftar:</span>
                    <span class="font-bold text-sky-800">${katPendaftar}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Nama Lengkap:</span>
                    <span class="font-bold text-slate-900">${namaLengkap}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Jenis Kelamin:</span>
                    <span class="font-semibold text-slate-800">${jkText}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Tempat, Tgl Lahir:</span>
                    <span class="font-semibold text-slate-800">${ttlText}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Umur Siswa:</span>
                    <span class="font-bold text-emerald-700">${umur}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Asal Sekolah:</span>
                    <span class="font-semibold text-slate-800">${asalSekolah}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Orang Tua:</span>
                    <span class="font-semibold text-slate-800">Ayah: ${namaAyah} | Ibu: ${namaIbu}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">No. WhatsApp:</span>
                    <span class="font-mono font-bold text-slate-800">${noHp}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Alamat:</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[220px]" title="${alamat}">${alamat}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Gelombang:</span>
                    <span class="font-semibold text-slate-800">${gelombang}</span>
                </div>
                <div class="flex justify-between pt-1 font-bold">
                    <span class="text-slate-700">Biaya Awal:</span>
                    <span class="text-emerald-600 font-bold">Bebas Biaya (Rp 0)</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-2.5 text-center">Pastikan data di atas sudah sesuai sebelum disimpan ke database.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0284c7',
        cancelButtonColor: '#64748b',
        confirmButtonText: '✓ Ya, Simpan Data',
        cancelButtonText: 'Cek Kembali'
    }).then((result) => {
        if (!result.isConfirmed) return;

        // 2. LOADING STATE DI DALAM SWEETALERT
        Swal.fire({
            title: 'Menyimpan Data...',
            text: 'Mohon tunggu sebentar, sistem sedang memproses...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';
            let data = null;
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    if (response.ok) {
                        data = { success: true, message: 'Calon siswa baru berhasil didaftarkan!' };
                    } else {
                        data = { success: false, message: 'Terjadi kesalahan sistem (' + response.status + ')' };
                    }
                }
            }
            return { ok: response.ok, data: data };
        })
        .then(({ ok, data }) => {
            if (ok && data && data.success) {
                closeModalTambahSiswaBaru();

                const cetakUrl = data.cetak_url || ('<?= url('kelola-spmb/siswa-baru/cetak/') ?>' + data.id + '?autoprint=1');

                // 3. OTOMATIS BUKA HALAMAN CETAK DI TAB BARU
                window.open(cetakUrl, '_blank');

                // NOTIFIKASI SUKSES DENGAN OPSI CETAK ULANG
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    html: `
                        <div class="space-y-3">
                            <p class="text-xs text-slate-600">
                                Calon siswa <strong>${namaLengkap}</strong> dengan No. Formulir <strong>${data.nomor || noFormulir}</strong> telah berhasil didaftarkan.
                            </p>
                            <div class="p-3 bg-sky-50 border border-sky-200 rounded-xl text-xs text-sky-800 flex items-center gap-2.5 text-left">
                                <span class="text-2xl">🖨️</span>
                                <div>
                                    <div class="font-bold">Kwitansi Bukti Pendaftaran</div>
                                    <div class="text-[11px] text-sky-700">Kwitansi pembayaran pendaftaran telah otomatis dibuka pada tab baru untuk dicetak.</div>
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#0284c7',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '🖨️ Cetak Kwitansi Lagi',
                    cancelButtonText: 'Selesai'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.open(cetakUrl, '_blank');
                    }
                    window.location.reload();
                });
            } else {
                throw new Error((data && data.message) ? data.message : 'Gagal menyimpan data calon siswa.');
            }
        })
        .catch((error) => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: error.message || 'Terjadi kesalahan saat menghubungi server.',
                confirmButtonColor: '#e11d48'
            });
        });
    });
}

// Delete Confirmation
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Calon Siswa?',
        text: 'Apakah Anda yakin ingin menghapus data "' + name + '"? Seluruh data dan berkas terkait akan ikut terhapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Data',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('kelola-spmb/siswa-baru/delete/') ?>' + id;
        }
    });
}

function salinLinkPublic(url) {
    if (!url) return;
    const successMsg = 'Tautan pengisian biodata berhasil disalin. Anda dapat mengirimkannya ke orang tua siswa.';

    function showSuccess() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Tautan Disalin!',
                text: successMsg,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert("Tautan pengisian biodata berhasil disalin!");
        }
    }

    if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard.writeText(url).then(showSuccess).catch(function() {
            fallbackExecCopyIndex(url, showSuccess);
        });
    } else {
        fallbackExecCopyIndex(url, showSuccess);
    }
}

function fallbackExecCopyIndex(text, onSuccess) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "-9999px";
    textArea.style.left = "-9999px";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        var successful = document.execCommand('copy');
        if (successful) {
            if (onSuccess) onSuccess();
        } else {
            prompt("Salin manual tautan formulir berikut:", text);
        }
    } catch (err) {
        prompt("Salin manual tautan formulir berikut:", text);
    }
    document.body.removeChild(textArea);
}

function salinLinkKomitmen(url) {
    if (!url) return;
    const successMsg = 'Tautan Tanda Tangan Surat Komitmen Orang Tua berhasil disalin. Anda dapat mengirimkannya ke WhatsApp Ayah/Ibu siswa.';

    function showSuccess() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Tautan TTD Disalin!',
                text: successMsg,
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            alert(successMsg);
        }
    }

    if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard.writeText(url).then(showSuccess).catch(function() {
            fallbackExecCopyIndex(url, showSuccess);
        });
    } else {
        fallbackExecCopyIndex(url, showSuccess);
    }
}
</script>

<?php include BASE_PATH . '/modules/kelola-spmb/views/partials/modal_verifikasi_berkas.php'; ?>
<?php include BASE_PATH . '/modules/kelola-spmb/views/partials/modal_kelulusan_spmb.php'; ?>
