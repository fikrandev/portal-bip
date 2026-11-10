<?php
/**
 * View Utama Modul Kelola Absen Pegawai
 * Portal BIP
 *
 * Fitur:
 * 1. Pengaturan Radius Global (5m, 10m, 15m, dst. berlaku untuk SEMUA pegawai & SEMUA unit)
 * 2. Lokasi Absen Masing-Masing Pegawai (Custom Per Pegawai + Bulk Custom + Reset per Pegawai)
 * 3. Lokasi Absen Custom Per Unit (Custom Per Unit + Reset per Unit)
 * 4. Pengaturan Lokasi Absen Semula / Default + Reset Semua ke Lokasi Semula
 * 5. Peta Interaktif (Leaflet) untuk memilih titik Latitude & Longitude secara presisi
 */

$activeTab = $activeTab ?? 'pegawai';
$radiusGlobal = (int)($globalConfig['radius_meter'] ?? 10);
$defaultKantor = $globalConfig['nama_kantor_default'] ?? 'Kampus Utama SIT Bina Insan Palu';
$defaultLat = (float)($globalConfig['latitude_default'] ?? -0.8917000);
$defaultLng = (float)($globalConfig['longitude_default'] ?? 119.8707000);
?>

<!-- Leaflet CSS & JS untuk Map Picker Interaktif -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="space-y-6">

    <!-- ═════════════════════════════════════════════════════════════════════
         TOP HERO BANNER & PENGATURAN CEPAT RADIUS GLOBAL
         ═════════════════════════════════════════════════════════════════════ -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-950 via-primary-900 to-sky-950 p-6 sm:p-8 text-white shadow-xl border border-primary-800/80">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-sky-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/4 -top-10 w-60 h-60 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Radius Global Aktif: <strong><?= $radiusGlobal ?> Meter</strong> (Berlaku untuk Semua Pegawai & Unit)</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                    Kelola Lokasi & Radius Absen Pegawai
                </h1>
                <p class="text-primary-200/90 text-sm leading-relaxed">
                    Atur titik koordinat lokasi absen (<strong>Nama Kantor Tempat Absen</strong>, <strong>Latitude</strong>, dan <strong>Longitude</strong>) secara <strong>Custom Per Pegawai</strong>, <strong>Custom Per Unit</strong>, atau <strong>Reset ke Lokasi Semula</strong>, dengan <strong>Radius Global</strong> yang seragam untuk seluruh pegawai.
                </p>
                <div class="flex flex-wrap items-center gap-2.5 mt-4 text-xs text-primary-200">
                    <span class="inline-flex items-center gap-1.5 bg-primary-900/70 px-3 py-1.5 rounded-xl border border-primary-700/60">
                        📍 Lokasi Semula: <strong class="text-white"><?= e($defaultKantor) ?></strong>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-primary-900/70 px-3 py-1.5 rounded-xl border border-primary-700/60 font-mono">
                        Lat: <strong class="text-sky-300"><?= number_format($defaultLat, 6, '.', '') ?></strong> | Lng: <strong class="text-sky-300"><?= number_format($defaultLng, 6, '.', '') ?></strong>
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button type="button" onclick="openModalCustomPegawai()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    <span>+ Custom Lokasi Pegawai</span>
                </button>

                <button type="button" onclick="openModalCustomUnit()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/></svg>
                    <span>+ Custom Lokasi Unit</span>
                </button>

                <form method="POST" action="<?= url('kelola-absen-pegawai/reset-semua') ?>" onsubmit="return confirm('Reset SELURUH pegawai dan unit kembali ke Lokasi Absen Semula (<?= e(addslashes($defaultKantor)) ?>)?');">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="<?= e($activeTab) ?>">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-200 hover:text-white font-semibold text-xs border border-rose-400/30 backdrop-blur-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        <span>Reset Semua ke Semula</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         BAR PENGATURAN CEPAT RADIUS GLOBAL (UNTUK SEMUA PEGAWAI & SEMUA UNIT)
         ═════════════════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-primary-200/80 shadow-xs flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center font-extrabold text-sm shrink-0">
                <?= $radiusGlobal ?>m
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-slate-900 text-sm">Pengaturan Radius Absen Global (Berlaku untuk Semua)</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Global Semua Pegawai & Unit</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pilih tombol ukuran cepat (5m, 10m, 15m, dst.) atau ketik jarak meter sesuai kebutuhan. Radius ini otomatis berlaku seragam untuk seluruh pegawai.
                </p>
            </div>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/radius/update') ?>" class="flex flex-wrap items-center gap-2 shrink-0">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="<?= e($activeTab) ?>">

            <!-- Preset Buttons -->
            <div class="flex flex-wrap items-center gap-1.5 mr-1">
                <?php foreach ([5, 10, 15, 20, 25, 50, 100] as $presetM): ?>
                    <button type="button"
                        onclick="document.getElementById('quick_radius_input').value='<?= $presetM ?>'; this.form.submit();"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer <?= $radiusGlobal === $presetM ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border-slate-200' ?>">
                        <?= $presetM ?>m
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Custom Meter Input -->
            <div class="flex items-center bg-slate-50 rounded-xl border border-slate-200 px-3 py-1.5">
                <input type="number" name="radius_meter" id="quick_radius_input" value="<?= $radiusGlobal ?>" min="1" max="5000" required
                       class="w-16 bg-transparent text-xs font-extrabold text-slate-900 outline-none text-center">
                <span class="text-xs font-bold text-slate-500 ml-1">Meter</span>
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                Simpan Radius
            </button>
        </form>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         4 KPI CARDS RINGKASAN LOKASI ABSEN
         ═════════════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai Aktif</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['totalPegawai']) ?></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Terdaftar dalam sistem absen</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-sky-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Lokasi Semula (Default)</span>
            <div class="text-2xl font-extrabold text-sky-600 mt-1"><?= number_format($stats['totalDefault']) ?> <span class="text-xs font-normal text-slate-400">Pegawai</span></div>
            <p class="text-[11px] text-slate-500 mt-0.5 truncate" title="<?= e($defaultKantor) ?>"><?= e($defaultKantor) ?></p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-purple-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">Custom Lokasi Per Unit</span>
            <div class="text-2xl font-extrabold text-purple-600 mt-1"><?= number_format($stats['totalCustomUnit']) ?> <span class="text-xs font-normal text-slate-400">Pegawai</span></div>
            <p class="text-[11px] text-slate-500 mt-0.5"><?= (int)$stats['unitCustomCount'] ?> dari <?= (int)$stats['totalUnit'] ?> unit diatur khusus</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Custom Per Pegawai</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['totalCustomPegawai']) ?> <span class="text-xs font-normal text-slate-400">Pegawai</span></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Titik lokasi khusus individu</p>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         NAVIGATION TABS
         ═════════════════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1.5 overflow-x-auto">
            <button type="button" onclick="switchAbsenTab('pegawai')" data-tab-btn="pegawai"
                class="absen-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer <?= $activeTab === 'pegawai' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                <span>1. Lokasi Absen Masing-Masing Pegawai</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold <?= $activeTab === 'pegawai' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' ?>">
                    <?= count($pegawaiList) ?>
                </span>
            </button>

            <button type="button" onclick="switchAbsenTab('unit')" data-tab-btn="unit"
                class="absen-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer <?= $activeTab === 'unit' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                <span>2. Lokasi Absen Custom Per Unit</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold <?= $activeTab === 'unit' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-700' ?>">
                    <?= count($unitLokasiList) ?> Unit
                </span>
            </button>

            <button type="button" onclick="switchAbsenTab('pengaturan')" data-tab-btn="pengaturan"
                class="absen-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer <?= $activeTab === 'pengaturan' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                <span>3. Lokasi Semula (Default) & Radius Global</span>
            </button>
        </div>

        <a href="<?= url('kelola-absen-pegawai/export') ?>"
           class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            <span>Export CSV</span>
        </a>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 1: LOKASI ABSEN MASING-MASING PEGAWAI (CUSTOM PER PEGAWAI & RESET)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-pegawai" class="absen-tab-panel space-y-4 <?= $activeTab === 'pegawai' ? '' : 'hidden' ?>">

        <!-- Filter & Bulk Action Toolbar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-absen-pegawai') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="pegawai">
                <div class="relative flex-1 min-w-[220px] max-w-xs">
                    <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Cari nama pegawai, NIY, nama kantor..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>

                <select name="unit_tugas" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Unit Tugas</option>
                    <?php foreach ($unitLokasiList as $ul): ?>
                        <option value="<?= e($ul['unit_tugas']) ?>" <?= $filters['unit_tugas'] === $ul['unit_tugas'] ? 'selected' : '' ?>>
                            <?= e($ul['unit_tugas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="mode_lokasi" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Status Lokasi</option>
                    <option value="default" <?= $filters['mode_lokasi'] === 'default' ? 'selected' : '' ?>>Lokasi Semula (Default)</option>
                    <option value="unit" <?= $filters['mode_lokasi'] === 'unit' ? 'selected' : '' ?>>Custom Unit</option>
                    <option value="custom" <?= $filters['mode_lokasi'] === 'custom' ? 'selected' : '' ?>>Custom Pegawai</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Filter
                </button>
                <?php if ($filters['search'] || $filters['unit_tugas'] || $filters['mode_lokasi']): ?>
                    <a href="<?= url('kelola-absen-pegawai?tab=pegawai') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-xs">
                        Reset Filter
                    </a>
                <?php endif; ?>
            </form>

            <!-- Bulk Action Buttons (Saat Checkbox Pegawai Dipilih) -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" onclick="openBulkCustomPegawai()"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs cursor-pointer flex items-center gap-1.5">
                    <span>📍 Atur Lokasi Pegawai Terpilih (<span id="selected_pegawai_count">0</span>)</span>
                </button>

                <form id="formBulkResetPegawai" method="POST" action="<?= url('kelola-absen-pegawai/pegawai/reset') ?>" onsubmit="return submitBulkResetPegawai();">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="pegawai">
                    <input type="hidden" name="pegawai_ids_csv" id="bulk_reset_ids_csv" value="">
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs cursor-pointer">
                        ↺ Reset Terpilih ke Semula
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Seluruh Pegawai & Lokasi Absennya -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Lokasi Absen Seluruh Pegawai</h3>
                    <p class="text-xs text-slate-500">Menampilkan <strong><?= count($pegawaiList) ?></strong> pegawai &bull; Klik <strong>Atur Lokasi</strong> untuk custom per pegawai atau <strong>Reset</strong> untuk kembali ke lokasi semula</p>
                </div>
                <span class="text-xs font-extrabold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200">
                    Radius Berlaku Semua: <?= $radiusGlobal ?> Meter
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-3 text-center w-10">
                                <input type="checkbox" id="checkAllPegawai" onchange="toggleSelectAllPegawai(this)" class="rounded text-primary-600 cursor-pointer">
                            </th>
                            <th class="py-3.5 px-3 text-center w-10">No</th>
                            <th class="py-3.5 px-4">Nama Pegawai & NIY</th>
                            <th class="py-3.5 px-4">Unit Tugas</th>
                            <th class="py-3.5 px-4 text-center">Status Lokasi</th>
                            <th class="py-3.5 px-4">Nama Kantor Tempat Absen</th>
                            <th class="py-3.5 px-4">Latitude & Longitude</th>
                            <th class="py-3.5 px-4 text-center">Radius Global</th>
                            <th class="py-3.5 px-4 text-center">Aksi Lokasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($pegawaiList)): ?>
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    Tidak ada data pegawai yang ditemukan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pegawaiList as $idx => $p):
                                $namaLengkap = $p['nama'] . (!empty($p['gelar']) ? ', ' . $p['gelar'] : '');
                                $mode = $p['mode_efektif'];
                                $modeBadge = match($mode) {
                                    'custom' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'unit'   => 'bg-purple-100 text-purple-800 border-purple-200',
                                    default  => 'bg-sky-50 text-sky-700 border-sky-200'
                                };
                                $latStr = number_format((float)$p['latitude_efektif'], 6, '.', '');
                                $lngStr = number_format((float)$p['longitude_efektif'], 6, '.', '');
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-3 text-center">
                                        <input type="checkbox" value="<?= (int)$p['id'] ?>" onchange="updateSelectedPegawaiCount()" class="pegawai-row-check rounded text-primary-600 cursor-pointer">
                                    </td>
                                    <td class="py-3.5 px-3 text-center text-slate-400 font-semibold"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($namaLengkap) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIY: <?= e($p['niy'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-700"><?= e($p['unit_tugas'] ?: 'Umum / Yayasan') ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($p['jabatan'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $modeBadge ?>">
                                            <?= e($p['status_lokasi_label']) ?>
                                        </span>
                                        <?php if (!empty($p['jadwal_custom_label'])): ?>
                                            <div class="mt-1">
                                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold <?= ($p['jadwal_custom_status'] ?? '') === 'aktif_sekarang' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600' ?>">
                                                    🗓️ <?= e($p['jadwal_custom_label']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            <span class="text-rose-500">🏢</span>
                                            <span><?= e($p['nama_kantor_efektif']) ?></span>
                                        </div>
                                        <?php if ($p['peg_mode'] === 'custom' && $mode !== 'custom' && !empty($p['peg_kantor'])): ?>
                                            <div class="text-[10px] text-amber-700 font-semibold mt-0.5">
                                                ⏳ Lokasi Jadwal: <?= e($p['peg_kantor']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($p['peg_ket'])): ?>
                                            <div class="text-[10px] text-slate-400 mt-0.5"><?= e($p['peg_ket']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <div class="text-slate-800 font-bold"><?= $latStr ?>, <?= $lngStr ?></div>
                                        <a href="https://www.google.com/maps?q=<?= $latStr ?>,<?= $lngStr ?>" target="_blank"
                                           class="inline-flex items-center gap-1 text-[10px] font-sans font-bold text-sky-600 hover:underline mt-0.5">
                                            🗺️ Lihat di Maps &rarr;
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-extrabold text-xs">
                                            <?= (int)$p['radius_meter'] ?> Meter
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                onclick="openModalCustomPegawai(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($namaLengkap), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_kantor'] ?: $p['nama_kantor_efektif']), ENT_QUOTES, 'UTF-8') ?>, '<?= $p['peg_lat'] ?: $latStr ?>', '<?= $p['peg_lng'] ?: $lngStr ?>', <?= htmlspecialchars(json_encode($p['peg_tgl_mulai'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_tgl_selesai'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_hari_aktif'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_ket'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)"
                                                class="px-2.5 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold text-[11px] transition-colors cursor-pointer">
                                                Atur Lokasi
                                            </button>

                                            <?php if ($mode !== 'default' || ($p['peg_mode'] ?? '') === 'custom'): ?>
                                                <form method="POST" action="<?= url('kelola-absen-pegawai/pegawai/reset') ?>" onsubmit="return confirm('Reset lokasi absen <?= e(addslashes($namaLengkap)) ?> kembali ke lokasi semula?');" class="inline">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="pegawai_id" value="<?= (int)$p['id'] ?>">
                                                    <input type="hidden" name="return_tab" value="pegawai">
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-[11px] transition-colors cursor-pointer" title="Reset kembali ke lokasi semula">
                                                        Reset
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 2: LOKASI ABSEN CUSTOM PER UNIT
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-unit" class="absen-tab-panel space-y-4 <?= $activeTab === 'unit' ? '' : 'hidden' ?>">
        <div class="bg-purple-50/80 border border-purple-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-purple-950 text-sm">Pengaturan Lokasi Absen Custom Per Unit Tugas</h3>
                <p class="text-xs text-purple-800 mt-0.5">
                    Anda dapat mengatur <strong>Nama Kantor Tempat Absen</strong>, <strong>Latitude</strong>, dan <strong>Longitude</strong> untuk setiap Unit (PAUD, SD, SMP, SMA, Yayasan, dll.). Seluruh pegawai di unit tersebut otomatis mengikuti titik lokasi unitnya.
                </p>
            </div>
            <button type="button" onclick="openModalCustomUnit()"
                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm cursor-pointer shrink-0">
                + Atur Lokasi Unit
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($unitLokasiList as $ul):
                $latU = number_format((float)$ul['latitude'], 6, '.', '');
                $lngU = number_format((float)$ul['longitude'], 6, '.', '');
                $isCustomU = !empty($ul['is_custom_unit']);
            ?>
                <div class="bg-white rounded-2xl p-5 border <?= $isCustomU ? 'border-purple-300 shadow-sm' : 'border-slate-200/80 shadow-xs' ?> flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full <?= $isCustomU ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-sky-50 text-sky-700 border border-sky-200' ?>">
                                    <?= $isCustomU ? 'Custom Unit Aktif' : 'Mengikuti Lokasi Semula' ?>
                                </span>
                                <h4 class="text-base font-extrabold text-slate-900 mt-2"><?= e($ul['unit_tugas']) ?></h4>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-extrabold text-xs">
                                <?= (int)$ul['total_pegawai'] ?> Pegawai
                            </span>
                        </div>

                        <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Nama Kantor Tempat Absen:</span>
                                <span class="font-bold text-slate-800">🏢 <?= e($ul['nama_kantor']) ?></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-200/60 font-mono">
                                <div>
                                    <span class="text-[10px] font-sans text-slate-400 block">Latitude:</span>
                                    <strong class="text-slate-800"><?= $latU ?></strong>
                                </div>
                                <div>
                                    <span class="text-[10px] font-sans text-slate-400 block">Longitude:</span>
                                    <strong class="text-slate-800"><?= $lngU ?></strong>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                                <span class="text-[11px] text-slate-500">Radius Global: <strong class="text-emerald-700"><?= (int)$ul['radius_meter'] ?> Meter</strong></span>
                                <a href="https://www.google.com/maps?q=<?= $latU ?>,<?= $lngU ?>" target="_blank" class="text-[11px] font-bold text-sky-600 hover:underline">
                                    Lihat Maps &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                        <button type="button"
                            onclick="openModalCustomUnit(<?= htmlspecialchars(json_encode($ul['unit_tugas']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($ul['nama_kantor']), ENT_QUOTES, 'UTF-8') ?>, '<?= $latU ?>', '<?= $lngU ?>')"
                            class="flex-1 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs text-center cursor-pointer transition-colors">
                            Atur Lokasi Unit
                        </button>

                        <?php if ($isCustomU): ?>
                            <form method="POST" action="<?= url('kelola-absen-pegawai/unit/reset') ?>" onsubmit="return confirm('Reset lokasi Unit <?= e(addslashes($ul['unit_tugas'])) ?> kembali ke Lokasi Semula?');">
                                <?= CSRF::field() ?>
                                <input type="hidden" name="unit_tugas" value="<?= e($ul['unit_tugas']) ?>">
                                <input type="hidden" name="return_tab" value="unit">
                                <button type="submit" class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs cursor-pointer">
                                    Reset Semula
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 3: PENGATURAN LOKASI SEMULA (DEFAULT) & RADIUS GLOBAL
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-pengaturan" class="absen-tab-panel space-y-6 <?= $activeTab === 'pengaturan' ? '' : 'hidden' ?>">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Pengaturan Utama -->
            <div class="lg:col-span-6 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-extrabold text-slate-900">Pengaturan Lokasi Absen Semula (Default) & Radius Global</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Titik lokasi di bawah ini adalah <strong>Lokasi Kantor Semula (Default)</strong> yang menjadi acuan utama seluruh pegawai dan saat fitur <strong>Reset Lokasi Semula</strong> dijalankan.
                    </p>
                </div>

                <form method="POST" action="<?= url('kelola-absen-pegawai/pengaturan/save') ?>" class="space-y-4">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="pengaturan">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kantor / Tempat Absen Semula (Utama) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_kantor_default" id="def_nama_kantor" value="<?= e($defaultKantor) ?>" required
                               placeholder="Contoh: Kampus Utama SIT Bina Insan Palu"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-primary-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Latitude Default <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="latitude_default" id="def_lat" value="<?= number_format($defaultLat, 7, '.', '') ?>" required
                                   oninput="updateDefaultMapMarker()"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Longitude Default <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="longitude_default" id="def_lng" value="<?= number_format($defaultLng, 7, '.', '') ?>" required
                                   oninput="updateDefaultMapMarker()"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                        </div>
                    </div>

                    <!-- Radius Global -->
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-2.5">
                        <label class="block text-xs font-extrabold text-emerald-950">
                            Radius Presensi Global (Berlaku untuk Semua Pegawai & Unit) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="number" name="radius_meter" id="def_radius" value="<?= $radiusGlobal ?>" min="1" max="5000" required
                                   oninput="updateDefaultMapMarker()"
                                   class="w-28 px-3.5 py-2 rounded-xl border border-emerald-300 bg-white text-sm font-extrabold text-emerald-900 outline-none">
                            <span class="text-xs font-bold text-emerald-800">Meter</span>

                            <div class="flex flex-wrap gap-1 ml-auto">
                                <?php foreach ([5, 10, 15, 25, 50, 100] as $rOpt): ?>
                                    <button type="button" onclick="document.getElementById('def_radius').value='<?= $rOpt ?>'; updateDefaultMapMarker();"
                                            class="px-2 py-1 rounded-lg bg-white hover:bg-emerald-600 text-emerald-800 hover:text-white border border-emerald-200 text-[11px] font-bold transition-colors cursor-pointer">
                                        <?= $rOpt ?>m
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="text-[11px] text-emerald-800">
                            Radius ini berlaku untuk seluruh pegawai tanpa terkecuali (baik lokasi semula, custom unit, maupun custom individu).
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <button type="button" onclick="ambilGpsUntukDefault()"
                                class="px-3.5 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 font-bold text-xs cursor-pointer">
                            📍 Gunakan Titik GPS Saya Saat Ini
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md cursor-pointer">
                            Simpan Pengaturan Global
                        </button>
                    </div>
                </form>
            </div>

            <!-- Peta Visualisasi Lokasi Semula & Radius -->
            <div class="lg:col-span-6 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Peta Interaktif Lokasi Kantor Semula</h3>
                        <p class="text-xs text-slate-500">Klik pada peta atau geser pin biru untuk memilih titik Latitude & Longitude</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 font-bold text-xs">Interactive Map</span>
                </div>

                <div id="mapDefaultPicker" class="w-full h-80 rounded-2xl border border-slate-200 overflow-hidden z-10"></div>

                <div class="p-3.5 rounded-2xl bg-rose-50/70 border border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-xs font-extrabold text-rose-900">Ingin Mengembalikan Semua Lokasi ke Semula?</h4>
                        <p class="text-[11px] text-rose-700">Menghapus seluruh custom lokasi pegawai & unit agar kembali ke Lokasi Semula di atas.</p>
                    </div>
                    <form method="POST" action="<?= url('kelola-absen-pegawai/reset-semua') ?>" onsubmit="return confirm('Yakin ingin me-reset seluruh lokasi pegawai & unit kembali ke Lokasi Semula?');">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="return_tab" value="pengaturan">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shrink-0 cursor-pointer">
                            ↺ Reset Semua Lokasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 1: ATUR LOKASI CUSTOM PER PEGAWAI (INDIVIDU / MASSAL)
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalCustomPegawai" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 id="modal_peg_title" class="text-base font-extrabold text-slate-900">Atur Lokasi Absen Custom Pegawai</h3>
                <p class="text-xs text-slate-500">Tentukan Nama Kantor Tempat Absen, Latitude, dan Longitude khusus pegawai</p>
            </div>
            <button type="button" onclick="closeModal('modalCustomPegawai')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/pegawai/save') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="pegawai">
            <input type="hidden" name="pegawai_ids_csv" id="modal_peg_ids_csv" value="">

            <div id="modal_peg_select_wrapper">
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" id="modal_peg_select" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                    <?php foreach ($allPegawaiDropdown as $pg):
                        $nm = $pg['nama'] . (!empty($pg['gelar']) ? ', ' . $pg['gelar'] : '');
                    ?>
                        <option value="<?= (int)$pg['id'] ?>">
                            <?= e($nm) ?> (<?= e($pg['unit_tugas'] ?: 'Umum') ?> — <?= e($pg['nama_kantor_efektif']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="modal_peg_bulk_banner" class="hidden p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-900">
                Mengatur lokasi custom untuk <span id="modal_peg_bulk_count">0</span> pegawai terpilih sekaligus.
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kantor / Tempat Absen <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_kantor" id="modal_peg_kantor" required placeholder="Contoh: Kampus 2 SD IT Bina Insan / Kantor Yayasan"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-primary-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Latitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="latitude" id="modal_peg_lat" required oninput="updateModalPegMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Longitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="longitude" id="modal_peg_lng" required oninput="updateModalPegMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                </div>
            </div>

            <!-- Peta Mini Pilih Titik Koordinat -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold text-slate-500">Klik pada peta untuk memilih titik lokasi:</span>
                    <button type="button" onclick="ambilGpsKeInput('modal_peg_lat', 'modal_peg_lng', updateModalPegMap)" class="text-[11px] font-bold text-sky-600 hover:underline cursor-pointer">
                        📍 Ambil Titik GPS Saat Ini
                    </button>
                </div>
                <div id="mapPegawaiPicker" class="w-full h-48 rounded-2xl border border-slate-200 overflow-hidden"></div>
            </div>

            <!-- Pengaturan Durasi / Jadwal Hari & Tanggal (Misal: Senin, Selasa, Rabu Pekan Ini) -->
            <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <label class="text-xs font-extrabold text-amber-950">🗓️ Masa Berlaku Lokasi Custom:</label>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="radio" name="tipe_durasi" id="durasi_permanen" value="permanen" checked onchange="toggleJadwalCustomPegawai()" class="text-primary-600">
                            <span>Permanen (Sampai Di-reset)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-900 cursor-pointer">
                            <input type="radio" name="tipe_durasi" id="durasi_terjadwal" value="terjadwal" onchange="toggleJadwalCustomPegawai()" class="text-amber-600">
                            <span>Hari / Tanggal Tertentu</span>
                        </label>
                    </div>
                </div>

                <div id="box_jadwal_custom_pegawai" class="hidden space-y-3 pt-2 border-t border-amber-200/80">
                    <!-- Tombol Preset Cepat -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold text-amber-800 mr-1">Pilih Cepat:</span>
                        <button type="button" onclick="presetJadwalPegawai('senin_rabu_pekan_ini')"
                                class="px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold cursor-pointer">
                            ⚡ Senin - Rabu Pekan Ini
                        </button>
                        <button type="button" onclick="presetJadwalPegawai('hari_ini')"
                                class="px-2.5 py-1 rounded-lg bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 text-[11px] font-bold cursor-pointer">
                            Hanya Hari Ini
                        </button>
                        <button type="button" onclick="presetJadwalPegawai('pekan_ini')"
                                class="px-2.5 py-1 rounded-lg bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 text-[11px] font-bold cursor-pointer">
                            1 Pekan Ini (Senin - Sabtu)
                        </button>
                    </div>

                    <!-- Pilih Hari Aktif -->
                    <div>
                        <label class="block text-[11px] font-bold text-amber-900 mb-1">Berlaku Pada Hari (Centang hari absen di lokasi ini):</label>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $namaHari): ?>
                                <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-amber-200 text-xs font-bold text-slate-700 cursor-pointer hover:bg-amber-100">
                                    <input type="checkbox" name="hari_aktif[]" value="<?= $namaHari ?>" class="chk-hari-aktif rounded text-amber-600">
                                    <span><?= $namaHari ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Rentang Tanggal Mulai s/d Selesai -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Dari Tanggal (Mulai)</label>
                            <input type="date" name="tanggal_mulai" id="modal_peg_tgl_mulai"
                                   class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Sampai Tanggal (Selesai)</label>
                            <input type="date" name="tanggal_selesai" id="modal_peg_tgl_selesai"
                                   class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        </div>
                    </div>

                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        💡 <strong>Otomatis Kembali Semula:</strong> Di luar hari/tanggal yang dipilih (misal mulai hari Kamis), lokasi absen pegawai ini <strong>otomatis kembali ke Lokasi Semula / Unit</strong> tanpa perlu di-reset manual.
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Alasan Penugasan (Opsional)</label>
                <input type="text" name="keterangan" id="modal_peg_ket" placeholder="Contoh: Tugas Pelatihan / Pendampingan Senin-Rabu Pekan Ini"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500">
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-500">Radius Presensi Berlaku (Global):</span>
                <strong class="text-emerald-700"><?= $radiusGlobal ?> Meter (Untuk Semua)</strong>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCustomPegawai')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Lokasi Pegawai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 2: ATUR LOKASI CUSTOM PER UNIT
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalCustomUnit" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Atur Lokasi Absen Custom Per Unit</h3>
                <p class="text-xs text-slate-500">Seluruh pegawai pada unit yang dipilih akan mengikuti lokasi kantor ini</p>
            </div>
            <button type="button" onclick="closeModal('modalCustomUnit')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/unit/save') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="unit">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Unit Tugas <span class="text-rose-500">*</span></label>
                <select name="unit_tugas" id="modal_unit_select" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:border-purple-500">
                    <?php foreach ($unitLokasiList as $ul): ?>
                        <option value="<?= e($ul['unit_tugas']) ?>">
                            <?= e($ul['unit_tugas']) ?> (<?= (int)$ul['total_pegawai'] ?> Pegawai)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kantor / Tempat Absen Unit <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_kantor" id="modal_unit_kantor" required placeholder="Contoh: Gedung SMP IT Bina Insan Palu"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-purple-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Latitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="latitude" id="modal_unit_lat" required oninput="updateModalUnitMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Longitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="longitude" id="modal_unit_lng" required oninput="updateModalUnitMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-purple-500">
                </div>
            </div>

            <!-- Peta Mini Pilih Titik Koordinat Unit -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold text-slate-500">Klik pada peta untuk memilih titik lokasi unit:</span>
                    <button type="button" onclick="ambilGpsKeInput('modal_unit_lat', 'modal_unit_lng', updateModalUnitMap)" class="text-[11px] font-bold text-sky-600 hover:underline cursor-pointer">
                        📍 Ambil Titik GPS Saat Ini
                    </button>
                </div>
                <div id="mapUnitPicker" class="w-full h-48 rounded-2xl border border-slate-200 overflow-hidden"></div>
            </div>

            <label class="flex items-center gap-2 p-3 rounded-xl bg-purple-50 border border-purple-200 cursor-pointer">
                <input type="checkbox" name="overwrite_custom_pegawai" value="1" checked class="rounded text-purple-600">
                <span class="text-xs font-semibold text-purple-900">Terapkan ke seluruh pegawai di unit ini (termasuk yang sebelumnya diatur custom individu)</span>
            </label>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCustomUnit')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Lokasi Unit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const DEFAULT_LAT = <?= json_encode($defaultLat) ?>;
const DEFAULT_LNG = <?= json_encode($defaultLng) ?>;
const DEFAULT_KANTOR = <?= json_encode($defaultKantor) ?>;
const GLOBAL_RADIUS = <?= json_encode($radiusGlobal) ?>;

let defaultMap = null, defaultMarker = null, defaultCircle = null;
let pegMap = null, pegMarker = null, pegCircle = null;
let unitMap = null, unitMarker = null, unitCircle = null;

function switchAbsenTab(tabName) {
    document.querySelectorAll('.absen-tab-panel').forEach(el => el.classList.add('hidden'));
    const panel = document.getElementById('absen-panel-' + tabName);
    if (panel) panel.classList.remove('hidden');

    document.querySelectorAll('.absen-tab-btn').forEach(btn => {
        const isMatch = btn.getAttribute('data-tab-btn') === tabName;
        btn.className = 'absen-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer ' +
            (isMatch ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100');
    });

    document.querySelectorAll('.absen-sidebar-link').forEach(link => {
        const isMatch = link.getAttribute('data-sidebar-tab') === tabName;
        if (isMatch) {
            link.classList.add('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
            link.classList.remove('text-primary-200', 'font-medium', 'border-transparent');
        } else {
            link.classList.remove('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
            link.classList.add('text-primary-200', 'font-medium', 'border-transparent');
        }
    });

    if (tabName === 'pengaturan') {
        setTimeout(initDefaultMap, 150);
    }

    const urlObj = new URL(window.location.href);
    urlObj.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', urlObj.toString());
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
}

// ── Checkbox Bulk Selection ──
function getSelectedPegawaiIds() {
    const checks = document.querySelectorAll('.pegawai-row-check:checked');
    return Array.from(checks).map(c => c.value);
}

function updateSelectedPegawaiCount() {
    const ids = getSelectedPegawaiIds();
    const el = document.getElementById('selected_pegawai_count');
    if (el) el.textContent = ids.length;
}

function toggleSelectAllPegawai(master) {
    document.querySelectorAll('.pegawai-row-check').forEach(c => {
        c.checked = master.checked;
    });
    updateSelectedPegawaiCount();
}

function submitBulkResetPegawai() {
    const ids = getSelectedPegawaiIds();
    if (ids.length === 0) {
        alert('Silakan centang minimal 1 pegawai pada tabel terlebih dahulu.');
        return false;
    }
    if (!confirm('Reset lokasi absen ' + ids.length + ' pegawai terpilih kembali ke Lokasi Semula?')) {
        return false;
    }
    document.getElementById('bulk_reset_ids_csv').value = ids.join(',');
    return true;
}

// ── Modal Custom Pegawai ──
function toggleJadwalCustomPegawai() {
    const isTerjadwal = document.getElementById('durasi_terjadwal').checked;
    const box = document.getElementById('box_jadwal_custom_pegawai');
    if (box) {
        box.classList.toggle('hidden', !isTerjadwal);
    }
}

function formatYmd(dateObj) {
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function presetJadwalPegawai(mode) {
    document.getElementById('durasi_terjadwal').checked = true;
    toggleJadwalCustomPegawai();

    const now = new Date();
    const dayOfWeek = now.getDay(); // 0=Minggu, 1=Senin, ...
    const diffToMonday = (dayOfWeek === 0 ? -6 : 1 - dayOfWeek);
    const monday = new Date(now);
    monday.setDate(now.getDate() + diffToMonday);

    const checks = document.querySelectorAll('.chk-hari-aktif');
    checks.forEach(c => c.checked = false);

    if (mode === 'senin_rabu_pekan_ini') {
        const wednesday = new Date(monday);
        wednesday.setDate(monday.getDate() + 2);
        document.getElementById('modal_peg_tgl_mulai').value = formatYmd(monday);
        document.getElementById('modal_peg_tgl_selesai').value = formatYmd(wednesday);
        checks.forEach(c => {
            if (['Senin', 'Selasa', 'Rabu'].includes(c.value)) c.checked = true;
        });
    } else if (mode === 'hari_ini') {
        const todayStr = formatYmd(now);
        document.getElementById('modal_peg_tgl_mulai').value = todayStr;
        document.getElementById('modal_peg_tgl_selesai').value = todayStr;
    } else if (mode === 'pekan_ini') {
        const saturday = new Date(monday);
        saturday.setDate(monday.getDate() + 5);
        document.getElementById('modal_peg_tgl_mulai').value = formatYmd(monday);
        document.getElementById('modal_peg_tgl_selesai').value = formatYmd(saturday);
        checks.forEach(c => {
            if (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'].includes(c.value)) c.checked = true;
        });
    }
}

function openModalCustomPegawai(pegawaiId = null, namaPegawai = '', namaKantor = '', lat = '', lng = '', tglMulai = '', tglSelesai = '', hariAktif = '', ket = '') {
    document.getElementById('modal_peg_ids_csv').value = '';
    document.getElementById('modal_peg_select_wrapper').classList.remove('hidden');
    document.getElementById('modal_peg_bulk_banner').classList.add('hidden');

    if (pegawaiId) {
        document.getElementById('modal_peg_select').value = String(pegawaiId);
        document.getElementById('modal_peg_title').textContent = 'Atur Lokasi Custom: ' + namaPegawai;
    } else {
        document.getElementById('modal_peg_title').textContent = 'Atur Lokasi Absen Custom Pegawai';
    }

    document.getElementById('modal_peg_kantor').value = namaKantor || DEFAULT_KANTOR;
    document.getElementById('modal_peg_lat').value = lat || DEFAULT_LAT;
    document.getElementById('modal_peg_lng').value = lng || DEFAULT_LNG;
    document.getElementById('modal_peg_ket').value = ket || '';

    // Set jadwal / durasi
    const checks = document.querySelectorAll('.chk-hari-aktif');
    checks.forEach(c => c.checked = false);

    if (tglMulai || tglSelesai || hariAktif) {
        document.getElementById('durasi_terjadwal').checked = true;
        document.getElementById('modal_peg_tgl_mulai').value = tglMulai || '';
        document.getElementById('modal_peg_tgl_selesai').value = tglSelesai || '';
        if (hariAktif) {
            const arr = hariAktif.split(',').map(s => s.trim());
            checks.forEach(c => {
                if (arr.includes(c.value)) c.checked = true;
            });
        }
    } else {
        document.getElementById('durasi_permanen').checked = true;
        document.getElementById('modal_peg_tgl_mulai').value = '';
        document.getElementById('modal_peg_tgl_selesai').value = '';
    }
    toggleJadwalCustomPegawai();

    const modal = document.getElementById('modalCustomPegawai');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(initModalPegMap, 150);
}

function openBulkCustomPegawai() {
    const ids = getSelectedPegawaiIds();
    if (ids.length === 0) {
        openModalCustomPegawai();
        return;
    }
    document.getElementById('modal_peg_ids_csv').value = ids.join(',');
    document.getElementById('modal_peg_select_wrapper').classList.add('hidden');
    document.getElementById('modal_peg_bulk_banner').classList.remove('hidden');
    document.getElementById('modal_peg_bulk_count').textContent = ids.length;
    document.getElementById('modal_peg_title').textContent = 'Atur Lokasi Custom (' + ids.length + ' Pegawai Terpilih)';
    document.getElementById('modal_peg_kantor').value = DEFAULT_KANTOR;
    document.getElementById('modal_peg_lat').value = DEFAULT_LAT;
    document.getElementById('modal_peg_lng').value = DEFAULT_LNG;

    const modal = document.getElementById('modalCustomPegawai');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(initModalPegMap, 150);
}

// ── Modal Custom Unit ──
function openModalCustomUnit(unitName = '', namaKantor = '', lat = '', lng = '') {
    if (unitName) {
        document.getElementById('modal_unit_select').value = unitName;
    }
    document.getElementById('modal_unit_kantor').value = namaKantor || ('Kampus ' + (unitName || 'Unit') + ' SIT Bina Insan Palu');
    document.getElementById('modal_unit_lat').value = lat || DEFAULT_LAT;
    document.getElementById('modal_unit_lng').value = lng || DEFAULT_LNG;

    const modal = document.getElementById('modalCustomUnit');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(initModalUnitMap, 150);
}

// ── Leaflet Maps Initialization ──
function initDefaultMap() {
    if (typeof L === 'undefined') return;
    const lat = parseFloat(document.getElementById('def_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('def_lng').value) || DEFAULT_LNG;
    const rad = parseInt(document.getElementById('def_radius').value) || GLOBAL_RADIUS;

    if (!defaultMap) {
        defaultMap = L.map('mapDefaultPicker').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; OpenStreetMap'
        }).addTo(defaultMap);

        defaultMarker = L.marker([lat, lng], { draggable: true }).addTo(defaultMap);
        defaultCircle = L.circle([lat, lng], { radius: rad, color: '#10b981', fillColor: '#10b981', fillOpacity: 0.2 }).addTo(defaultMap);

        defaultMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('def_lat').value = pos.lat.toFixed(7);
            document.getElementById('def_lng').value = pos.lng.toFixed(7);
            defaultCircle.setLatLng(pos);
        });

        defaultMap.on('click', function(e) {
            document.getElementById('def_lat').value = e.latlng.lat.toFixed(7);
            document.getElementById('def_lng').value = e.latlng.lng.toFixed(7);
            defaultMarker.setLatLng(e.latlng);
            defaultCircle.setLatLng(e.latlng);
        });
    } else {
        defaultMap.invalidateSize();
        updateDefaultMapMarker();
    }
}

function updateDefaultMapMarker() {
    if (!defaultMap || !defaultMarker) return;
    const lat = parseFloat(document.getElementById('def_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('def_lng').value) || DEFAULT_LNG;
    const rad = parseInt(document.getElementById('def_radius').value) || GLOBAL_RADIUS;
    defaultMarker.setLatLng([lat, lng]);
    defaultCircle.setLatLng([lat, lng]);
    defaultCircle.setRadius(rad);
    defaultMap.panTo([lat, lng]);
}

function initModalPegMap() {
    if (typeof L === 'undefined') return;
    const lat = parseFloat(document.getElementById('modal_peg_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_peg_lng').value) || DEFAULT_LNG;

    if (!pegMap) {
        pegMap = L.map('mapPegawaiPicker').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 20 }).addTo(pegMap);
        pegMarker = L.marker([lat, lng], { draggable: true }).addTo(pegMap);
        pegCircle = L.circle([lat, lng], { radius: GLOBAL_RADIUS, color: '#0284c7', fillOpacity: 0.2 }).addTo(pegMap);

        pegMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('modal_peg_lat').value = pos.lat.toFixed(7);
            document.getElementById('modal_peg_lng').value = pos.lng.toFixed(7);
            pegCircle.setLatLng(pos);
        });

        pegMap.on('click', function(e) {
            document.getElementById('modal_peg_lat').value = e.latlng.lat.toFixed(7);
            document.getElementById('modal_peg_lng').value = e.latlng.lng.toFixed(7);
            pegMarker.setLatLng(e.latlng);
            pegCircle.setLatLng(e.latlng);
        });
    } else {
        pegMap.invalidateSize();
        updateModalPegMap();
    }
}

function updateModalPegMap() {
    if (!pegMap || !pegMarker) return;
    const lat = parseFloat(document.getElementById('modal_peg_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_peg_lng').value) || DEFAULT_LNG;
    pegMarker.setLatLng([lat, lng]);
    pegCircle.setLatLng([lat, lng]);
    pegMap.panTo([lat, lng]);
}

function initModalUnitMap() {
    if (typeof L === 'undefined') return;
    const lat = parseFloat(document.getElementById('modal_unit_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_unit_lng').value) || DEFAULT_LNG;

    if (!unitMap) {
        unitMap = L.map('mapUnitPicker').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 20 }).addTo(unitMap);
        unitMarker = L.marker([lat, lng], { draggable: true }).addTo(unitMap);
        unitCircle = L.circle([lat, lng], { radius: GLOBAL_RADIUS, color: '#9333ea', fillOpacity: 0.2 }).addTo(unitMap);

        unitMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('modal_unit_lat').value = pos.lat.toFixed(7);
            document.getElementById('modal_unit_lng').value = pos.lng.toFixed(7);
            unitCircle.setLatLng(pos);
        });

        unitMap.on('click', function(e) {
            document.getElementById('modal_unit_lat').value = e.latlng.lat.toFixed(7);
            document.getElementById('modal_unit_lng').value = e.latlng.lng.toFixed(7);
            unitMarker.setLatLng(e.latlng);
            unitCircle.setLatLng(e.latlng);
        });
    } else {
        unitMap.invalidateSize();
        updateModalUnitMap();
    }
}

function updateModalUnitMap() {
    if (!unitMap || !unitMarker) return;
    const lat = parseFloat(document.getElementById('modal_unit_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_unit_lng').value) || DEFAULT_LNG;
    unitMarker.setLatLng([lat, lng]);
    unitCircle.setLatLng([lat, lng]);
    unitMap.panTo([lat, lng]);
}

function ambilGpsKeInput(latInputId, lngInputId, callbackFn) {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung fitur Geolokasi GPS.');
        return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById(latInputId).value = pos.coords.latitude.toFixed(7);
        document.getElementById(lngInputId).value = pos.coords.longitude.toFixed(7);
        if (typeof callbackFn === 'function') callbackFn();
    }, function(err) {
        alert('Gagal mengambil titik GPS: ' + err.message);
    }, { enableHighAccuracy: true });
}

function ambilGpsUntukDefault() {
    ambilGpsKeInput('def_lat', 'def_lng', updateDefaultMapMarker);
}

document.addEventListener('DOMContentLoaded', function() {
    if ('<?= e($activeTab) ?>' === 'pengaturan') {
        setTimeout(initDefaultMap, 200);
    }
});
</script>
