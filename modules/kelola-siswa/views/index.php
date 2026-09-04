<?php
/**
 * Kelola Siswa - Dashboard & Data Table View
 * Portal BIP - Complete Dapodik / BIP Student Management
 */
?>

<div class="space-y-6">

    <!-- Top Header & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 text-lg">
                    🎓
                </div>
                <span>Kelola Data Siswa</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Data induk siswa terintegrasi Dapodik untuk jenjang PAUD, SD, SMP, dan SMA
            </p>
        </div>

        <!-- Sudut Kanan Atas: Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Modal Trigger: Sinkron Dapodik -->
            <button type="button" onclick="document.getElementById('modal-dapodik-sync').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-800 font-bold text-xs border border-sky-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                <span>Sinkron Dapodik</span>
            </button>

            <!-- Export CSV -->
            <a href="<?= url('kelola-siswa/export' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <span>Unduh Excel/CSV</span>
            </a>

            <!-- Tambah Siswa Baru -->
            <a href="<?= url('kelola-siswa/create') ?>" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>+ Tambah Siswa Baru</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards (Summary) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- Total Siswa -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Semua Siswa</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-slate-800"><?= number_format($totalSiswa) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Aktif: <?= number_format($totalAktif) ?></span>
            </div>
        </div>

        <!-- PAUD / TK -->
        <div class="bg-white rounded-2xl p-4 border border-emerald-200/70 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 flex items-center gap-1">
                <span>🌱</span> PAUD / TK
            </span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-emerald-700"><?= number_format($totalPaud) ?></span>
                <span class="text-[10px] font-bold text-slate-400">Siswa</span>
            </div>
        </div>

        <!-- SD -->
        <div class="bg-white rounded-2xl p-4 border border-sky-200/70 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700 flex items-center gap-1">
                <span>🎒</span> Sekolah Dasar (SD)
            </span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-sky-700"><?= number_format($totalSd) ?></span>
                <span class="text-[10px] font-bold text-slate-400">Siswa</span>
            </div>
        </div>

        <!-- SMP -->
        <div class="bg-white rounded-2xl p-4 border border-indigo-200/70 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 flex items-center gap-1">
                <span>📚</span> SMP
            </span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-indigo-700"><?= number_format($totalSmp) ?></span>
                <span class="text-[10px] font-bold text-slate-400">Siswa</span>
            </div>
        </div>

        <!-- SMA -->
        <div class="bg-white rounded-2xl p-4 border border-purple-200/70 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 flex items-center gap-1">
                <span>🏛️</span> SMA
            </span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-purple-700"><?= number_format($totalSma) ?></span>
                <span class="text-[10px] font-bold text-slate-400">Siswa</span>
            </div>
        </div>

        <!-- Status Dapodik -->
        <div class="bg-white rounded-2xl p-4 border border-amber-200/70 shadow-xs flex flex-col justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 flex items-center gap-1">
                <span>✅</span> Masuk Dapodik
            </span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-amber-700"><?= number_format($totalDapodik) ?></span>
                <span class="text-[10px] font-bold text-slate-400">L: <?= $totalLaki ?> • P: <?= $totalPerempuan ?></span>
            </div>
        </div>
    </div>

    <!-- Jenjang Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <?php
        $tabList = [
            '' => ['label' => 'Semua Jenjang', 'count' => $totalSiswa, 'icon' => '🏫'],
            'PAUD' => ['label' => 'PAUD / TK', 'count' => $totalPaud, 'icon' => '🌱'],
            'SD' => ['label' => 'Sekolah Dasar (SD)', 'count' => $totalSd, 'icon' => '🎒'],
            'SMP' => ['label' => 'SMP', 'count' => $totalSmp, 'icon' => '📚'],
            'SMA' => ['label' => 'SMA', 'count' => $totalSma, 'icon' => '🏛️'],
        ];
        ?>
        <?php foreach ($tabList as $key => $tab): ?>
            <?php
            $isActiveTab = ($filterJenjang === $key) || (empty($filterJenjang) && $key === '');
            $urlParams = $_GET;
            if ($key === '') {
                unset($urlParams['jenjang']);
            } else {
                $urlParams['jenjang'] = $key;
            }
            $urlParams['page'] = 1;
            $tabUrl = url('kelola-siswa' . (!empty($urlParams) ? '?' . http_build_query($urlParams) : ''));
            ?>
            <a href="<?= $tabUrl ?>" class="px-4 py-2.5 text-xs font-bold border-b-2 whitespace-nowrap transition-all flex items-center gap-2 <?= $isActiveTab ? 'border-emerald-600 text-emerald-800 bg-emerald-50/60 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' ?>">
                <span><?= $tab['icon'] ?></span>
                <span><?= $tab['label'] ?></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] <?= $isActiveTab ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' ?>">
                    <?= number_format($tab['count']) ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <form method="GET" action="<?= url('kelola-siswa') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <?php if (!empty($filterJenjang)): ?>
                <input type="hidden" name="jenjang" value="<?= e($filterJenjang) ?>">
            <?php endif; ?>

            <!-- Search Field -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Cari Nama / NIS / NISN / NIK / Ortu</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Ketik nama, NISN, NIK, alamat..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
            </div>

            <!-- Tahun Akademik Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tahun Ajaran</label>
                <select name="ta" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <?php foreach ($tahunAkademikList as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= ($ta['id'] == $filterTa) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?> <?= $ta['is_active'] ? '(Aktif)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kelas Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Pilih Kelas</label>
                <select name="kelas" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= e($k['kelas']) ?>" <?= $filterKelas === $k['kelas'] ? 'selected' : '' ?>>Kelas <?= e($k['kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Dapodik -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Dapodik</label>
                <select name="dapodik" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="Sudah" <?= $filterDapodik === 'Sudah' ? 'selected' : '' ?>>Sudah Dapodik</option>
                    <option value="Belum" <?= $filterDapodik === 'Belum' ? 'selected' : '' ?>>Belum Dapodik</option>
                </select>
            </div>

            <!-- Submit Filter & Reset -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <span>Filter</span>
                </button>
                <a href="<?= url('kelola-siswa' . (!empty($filterJenjang) ? '?jenjang=' . urlencode($filterJenjang) : '')) ?>" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors" title="Reset Filter">
                    ✕
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200 font-extrabold">
                        <th class="py-3.5 px-4">Nama Siswa & Identitas</th>
                        <th class="py-3.5 px-4">Jenjang & Kelas</th>
                        <th class="py-3.5 px-4 text-center">JK / Usia</th>
                        <th class="py-3.5 px-4">Orang Tua / Wali</th>
                        <th class="py-3.5 px-4">Kontak & Alamat</th>
                        <th class="py-3.5 px-4 text-center">Dapodik</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($siswa)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-3xl">🔍</span>
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada data siswa yang cocok dengan filter pencarian.</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci atau klik "Sinkron Data Dapodik" di atas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($siswa as $s): ?>
                            <?php
                            $namaSiswa = $s['nama_lengkap'] ?: $s['nama'];
                            $j = strtoupper($s['jenjang'] ?? 'SD');
                            $jenjangBadge = [
                                'PAUD' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'TK' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'SD' => 'bg-sky-50 text-sky-800 border-sky-200',
                                'SMP' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                'SMA' => 'bg-purple-50 text-purple-800 border-purple-200'
                            ][$j] ?? 'bg-slate-100 text-slate-700 border-slate-200';

                            $isLaki = ($s['jenis_kelamin'] === 'L' || $s['jenis_kelamin'] === 'Laki-Laki');
                            $jkBadge = $isLaki ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-pink-50 text-pink-700 border-pink-200';
                            $jkText = $isLaki ? 'L' : 'P';

                            $isDapodik = ($s['dapodik'] === 'Sudah');
                            
                            $identifierFoto = !empty($s['nisn']) ? $s['nisn'] : $s['id_siswa'];
                            $fotoPath = BASE_PATH . '/public/uploads/siswa/' . $identifierFoto . '.jpg';
                            $fotoUrl = file_exists($fotoPath) ? asset('uploads/siswa/' . $identifierFoto . '.jpg') : null;
                            ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Nama & Identitas -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <?php if ($fotoUrl): ?>
                                            <img src="<?= $fotoUrl ?>?v=<?= filemtime($fotoPath) ?>" alt="Foto" class="w-9 h-9 rounded-2xl object-cover flex-shrink-0 shadow-sm border border-slate-200">
                                        <?php else: ?>
                                            <div class="w-9 h-9 rounded-2xl <?= $isLaki ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' ?> flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                <?= mb_substr($namaSiswa, 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?= url("kelola-siswa/detail/{$s['id']}") ?>" class="font-bold text-slate-800 hover:text-emerald-700 text-xs transition-colors">
                                                <?= e($namaSiswa) ?>
                                            </a>
                                            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono mt-0.5">
                                                <span>NIS: <?= e($s['nis'] ?: '-') ?></span>
                                                <?php if (!empty($s['nisn'])): ?>
                                                    <span>• NISN: <?= e($s['nisn']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Jenjang & Kelas -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold border w-fit <?= $jenjangBadge ?>">
                                            <?= e($j) ?>
                                        </span>
                                        <span class="font-bold text-slate-800 text-xs">
                                            Kelas: <?= e($s['kelas'] ?: '-') ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- JK / Usia -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold border <?= $jkBadge ?>">
                                        <?= $jkText ?>
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        <?= !empty($s['umur']) ? $s['umur'] . ' Thn' : '-' ?>
                                    </div>
                                </td>

                                <!-- Orang Tua -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 truncate max-w-[150px]">
                                        <?= e($s['nama_ayah'] ?: ($s['nama_ibu'] ?: '-')) ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        <?= !empty($s['nama_ayah']) ? 'Ayah: ' . e($s['nama_ayah']) : (!empty($s['nama_ibu']) ? 'Ibu: ' . e($s['nama_ibu']) : '-') ?>
                                    </div>
                                </td>

                                <!-- Kontak & Alamat -->
                                <td class="py-3 px-4">
                                    <div class="font-mono text-slate-700 text-[11px]">
                                        <?= e($s['no_hp'] ?: ($s['telepon'] ?: '-')) ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[160px]" title="<?= e($s['alamat'] ?? '') ?>">
                                        <?= e($s['kelurahan'] ?: ($s['alamat'] ?: '-')) ?>
                                    </div>
                                </td>

                                <!-- Dapodik -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $isDapodik ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $isDapodik ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                        <?= $isDapodik ? 'Sudah' : 'Belum' ?>
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Cetak Kartu Button -->
                                        <a href="<?= url('kelola-siswa/cetak-kartu/' . $s['id']) ?>" target="_blank" class="p-1.5 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors" title="Cetak Kartu">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" /></svg>
                                        </a>

                                        <!-- Detail Profile -->
                                        <a href="<?= url("kelola-siswa/detail/{$s['id']}") ?>" class="p-1.5 rounded-xl bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 transition-colors" title="Lihat Profil Lengkap">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="<?= url("kelola-siswa/edit/{$s['id']}") ?>" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" title="Edit Data Siswa">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </a>

                                        <!-- Cetak Biodata -->
                                        <a href="<?= url("kelola-siswa/cetak/{$s['id']}") ?>" target="_blank" class="p-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 transition-colors" title="Cetak Formulir Biodata">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                                        </a>

                                        <!-- Hapus -->
                                        <form method="POST" action="<?= url("kelola-siswa/delete/{$s['id']}") ?>" onsubmit="return confirm('Hapus data siswa <?= addslashes(e($namaSiswa)) ?>?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors" title="Hapus Siswa">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <p class="text-slate-500">
                    Menampilkan <span class="font-bold text-slate-800"><?= min($total, $offset + 1) ?></span> - <span class="font-bold text-slate-800"><?= min($total, $offset + $limit) ?></span> dari <span class="font-bold text-slate-800"><?= number_format($total) ?></span> siswa
                </p>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <?php
                    $queryParams = $_GET;
                    ?>
                    <?php if ($page > 1): ?>
                        <?php $queryParams['page'] = $page - 1; ?>
                        <a href="<?= url('kelola-siswa?' . http_build_query($queryParams)) ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold">
                            ← Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                        <?php $queryParams['page'] = $p; ?>
                        <a href="<?= url('kelola-siswa?' . http_build_query($queryParams)) ?>" class="px-3 py-1.5 rounded-xl font-bold <?= $p === $page ? 'bg-emerald-600 text-white shadow-xs' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <?php $queryParams['page'] = $page + 1; ?>
                        <a href="<?= url('kelola-siswa?' . http_build_query($queryParams)) ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold">
                            Next →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL: Tarik Data Dapodik Online -->
    <div id="modal-dapodik-sync" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl border border-slate-200/80 space-y-5 animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-base">
                        🌐
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Sinkronisasi Dapodik</h3>
                        <p class="text-[11px] text-slate-400">Tarik data langsung melalui Web Service Dapodik resmi (Data akan di-replace)</p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-dapodik-sync').classList.add('hidden')" class="p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                    ✕
                </button>
            </div>

            <form method="POST" action="<?= url('kelola-siswa/sync-dapodik') ?>" class="space-y-4">
                <?= CSRF::field() ?>
                <input type="hidden" name="tahun_akademik_id" value="<?= $filterTa ?>">

                <!-- Info IP Public Server Saat Ini -->
                <div class="p-3 rounded-2xl bg-sky-50 border border-sky-200 text-xs text-sky-900 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold flex items-center gap-1.5">
                            <span>🌐</span> IP Address Komputer Ini:
                        </span>
                        <span class="font-mono font-black text-sky-800 bg-white px-2.5 py-0.5 rounded-lg border border-sky-300 select-all"><?= e($publicIp ?? '36.74.113.159') ?></span>
                    </div>
                    <p class="text-[10px] text-sky-700 leading-normal">
                        ⚠️ <strong>Penting:</strong> Pastikan IP di atas sudah didaftarkan pada menu <strong>Pengaturan &rarr; Web Service</strong> di aplikasi Dapodik sekolah. Jika IP internet (IndiHome) berubah, perbarui IP di Dapodik sesuai angka di atas.
                    </p>
                </div>

                <!-- URL Server Dapodik -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">URL Server Dapodik</label>
                    <input type="text" name="dapodik_url" value="http://36.88.33.154:5774" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                </div>

                <!-- Token / Key Web Service -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Token Web Service (Key)</label>
                    <input type="text" name="dapodik_token" value="z4sdZbDIem7ao9u" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                </div>

                <!-- Pilihan Jenjang & NPSN -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Satuan Pendidikan</label>
                        <select name="jenjang" id="sync_jenjang" onchange="updateSyncNpsn(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold">
                            <option value="SD" data-npsn="69979223">🎒 SD IT Bina Insan</option>
                            <option value="SMP" data-npsn="70031371">📚 SMP IT Bina Insan</option>
                            <option value="SMA" data-npsn="70058217">🏛️ SMA IT Bina Insan</option>
                            <option value="PAUD" data-npsn="70037291">🌱 PAUD IT Bina Insan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">NPSN Sekolah</label>
                        <input type="text" name="dapodik_npsn" id="sync_npsn" value="69979223" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono font-bold">
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
                    💡 <strong>Catatan:</strong> Data jenjang yang dipilih akan otomatis ditarik dan dimasukkan ke Tahun Ajaran (Semester) yang saat ini sedang Anda pilih pada halaman Kelola Siswa. Data lama pada semester ini akan ditimpa (replace).
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="document.getElementById('modal-dapodik-sync').classList.add('hidden')" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-500/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        <span>Mulai Sinkronisasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function updateSyncNpsn(jenjang) {
        var select = document.getElementById('sync_jenjang');
        var opt = select.options[select.selectedIndex];
        var npsn = opt.getAttribute('data-npsn');
        if (npsn) {
            document.getElementById('sync_npsn').value = npsn;
        }
    }
    </script>
</div>

