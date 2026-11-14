<?php
/**
 * View: Kelola & Rekap Ibadah Harian Guru
 * Terintegrasi real-time dengan Portal Guru Mobile (/mobile) via mutabaah_ibadah_guru.
 */
$activeTab = $activeTab ?? 'dashboard';
$myGender = $myTeacher['gender'] ?? 'P';
$waktus = [
    'subuh' => ['label' => 'Subuh', 'jam' => '04.45'],
    'dzuhur' => ['label' => 'Dzuhur', 'jam' => '12.10'],
    'ashar' => ['label' => 'Ashar', 'jam' => '15.30'],
    'maghrib' => ['label' => 'Maghrib', 'jam' => '18.15'],
    'isya' => ['label' => 'Isya', 'jam' => '19.25'],
];

$cetakQueryHarian = http_build_query([
    'mode' => 'harian',
    'tanggal' => $tanggal,
    'search' => $search,
    'unit' => $filterUnit,
    'gender' => $filterGender,
    'status_isi' => $filterStatus,
]);
$cetakQueryRekap = http_build_query([
    'mode' => 'rekap',
    'start_date' => $startDate,
    'end_date' => $endDate,
    'search' => $search,
    'unit' => $filterUnit,
    'gender' => $filterGender,
]);
$persenPengisian = ($kpiDaily['total_guru'] ?? 0) > 0
    ? round(($kpiDaily['sudah_isi'] / $kpiDaily['total_guru']) * 100, 1)
    : 0;
?>

<div class="space-y-6">

    <!-- Header & Navigation Tabs -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/70 text-emerald-700 text-xs font-bold mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    Sinkronisasi Langsung dengan Portal Guru Mobile
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Rekap & Mutaba'ah Ibadah Harian Guru
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Pemantauan Sholat 5 Waktu, Tilawah Al-Qur'an, Dzikir Pagi/Petang, dan Tadabbur Harian Guru & Pegawai.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <?php if ($canViewAll && $canManage): ?>
                <button type="button"
                        onclick="openAdminIbadahModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Input / Koreksi Ibadah</span>
                </button>
                <?php endif; ?>

                <?php if ($canViewAll): ?>
                <a href="<?= url('kelola-ibadah-guru/cetak?' . ($activeTab === 'rekap' ? $cetakQueryRekap : $cetakQueryHarian)) ?>"
                   target="_blank"
                   id="btn-cetak-ibadah"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-primary-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.376 1.942-5.908 5.28-5.908 3.337 0 5.52 2.532 5.28 5.908m-10.56 0A5.967 5.967 0 0 0 12 18.25c2.476 0 4.636-1.503 5.28-4.421m-10.56 0c0-1.077.29-2.09.794-2.964m9.766 2.964a5.986 5.986 0 0 0-.794-2.964M6.72 13.829A7.95 7.95 0 0 1 12 11.25a7.95 7.95 0 0 1 5.28 2.579M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v1.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 10.5v-1.5A2.25 2.25 0 0 1 6.75 6.75Z"/>
                    </svg>
                    <span>Cetak Rekap</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabs -->
        <div class="pt-4 flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button type="button"
                    onclick="switchIbadahTab('ibadah_saya')"
                    data-ibadah-tab-btn="ibadah_saya"
                    class="ibadah-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'ibadah_saya' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/70' ?>">
                <span>✍️</span>
                <span>Isi Ibadah Saya</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full <?= $activeTab === 'ibadah_saya' ? 'bg-black/20 text-white' : 'bg-emerald-600 text-white' ?> font-extrabold"><?= (int)($myTodayState['sholat_count'] ?? 0) ?>/5 Sholat</span>
            </button>

            <?php if ($canViewAll): ?>
            <button type="button"
                    onclick="switchIbadahTab('dashboard')"
                    data-ibadah-tab-btn="dashboard"
                    class="ibadah-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'dashboard' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' ?>">
                <span>📊</span>
                <span>Dashboard</span>
            </button>

            <button type="button"
                    onclick="switchIbadahTab('harian')"
                    data-ibadah-tab-btn="harian"
                    class="ibadah-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'harian' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' ?>">
                <span>📅</span>
                <span>Data Harian Guru</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full bg-black/15 font-extrabold"><?= (int)$kpiDaily['sudah_isi'] ?>/<?= (int)$kpiDaily['total_guru'] ?></span>
            </button>

            <button type="button"
                    onclick="switchIbadahTab('rekap')"
                    data-ibadah-tab-btn="rekap"
                    class="ibadah-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'rekap' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' ?>">
                <span>📈</span>
                <span>Rekap Periode & Matriks</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: ISI IBADAH SAYA (AKUN LOGIN)                                   -->
    <!-- ===================================================================== -->
    <div id="tab-panel-ibadah_saya" class="ibadah-tab-panel space-y-6 <?= $activeTab === 'ibadah_saya' ? '' : 'hidden' ?>">

        <!-- Identitas Akun Login & Preview Super Admin -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <?php if (!empty($myTeacher['avatar_url'])): ?>
                    <img src="<?= e($myTeacher['avatar_url']) ?>" alt="<?= e($myTeacher['nama']) ?>" class="w-14 h-14 rounded-2xl object-cover border-2 border-emerald-200 shadow-xs shrink-0">
                <?php else: ?>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-black text-lg flex items-center justify-center shrink-0">
                        <?= strtoupper(substr($myTeacher['nama'] ?? 'G', 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900 truncate"><?= e($myTeacher['nama']) ?></h2>
                        <?php if ($myGender === 'P'): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                                👩 Perempuan (Centang Sholat otomatis Tab Rumah)
                            </span>
                        <?php else: ?>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                👨 Laki-laki (Masjid / Rumah)
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        NIY: <strong><?= e($myTeacher['niy']) ?></strong> &bull; <?= e($myTeacher['jabatan']) ?> &bull; Unit: <strong><?= e($myTeacher['unit_tugas']) ?></strong>
                    </p>
                </div>
            </div>

            <?php if (!empty($previewGuruList)): ?>
            <form method="GET" action="<?= url('kelola-ibadah-guru') ?>" class="flex items-center gap-2 shrink-0">
                <input type="hidden" name="tab" value="ibadah_saya">
                <label class="text-xs font-bold text-slate-500 whitespace-nowrap">Preview Guru:</label>
                <select name="my_guru_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-slate-50">
                    <option value="">-- Akun Saya Default --</option>
                    <?php foreach ($previewGuruList as $pg): ?>
                        <option value="<?= (int)$pg['id'] ?>" <?= (int)$pg['id'] === (int)$myTeacher['id'] ? 'selected' : '' ?>>
                            <?= e($pg['nama']) ?> (<?= ($pg['jenis_kelamin'] ?? 'P') === 'L' ? 'L' : 'P' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Mutaba'ah Hari Ini -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Form Mutaba'ah Hari Ini</h3>
                        <p class="text-xs text-slate-500">Tanggal: <strong><?= date('d F Y', strtotime($myDate)) ?></strong></p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Hari Ini
                    </span>
                </div>

                <form method="POST" action="<?= url('kelola-ibadah-guru/store') ?>" class="space-y-6" id="form-ibadah-saya">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="ibadah_saya">
                    <input type="hidden" name="guru_id" value="<?= (int)$myTeacher['id'] ?>">
                    <input type="hidden" name="tanggal" value="<?= e($myDate) ?>">
                    <?php if (Auth::isSuperAdmin() && !empty($_GET['my_guru_id'])): ?>
                        <input type="hidden" name="preview_guru_id" value="<?= (int)$_GET['my_guru_id'] ?>">
                    <?php endif; ?>

                    <!-- 1. Sholat 5 Waktu -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                <span>🕌</span> Sholat Fardhu 5 Waktu
                            </h4>
                            <?php if ($myGender === 'P'): ?>
                                <span class="text-[11px] font-bold text-pink-600 bg-pink-50 px-2.5 py-0.5 rounded-full border border-pink-200">
                                    Otomatis Tab Rumah untuk Perempuan
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2.5">
                            <?php foreach ($waktus as $wKey => $wMeta):
                                $wState = $myTodayState['sholat'][$wKey] ?? ['checked' => false, 'loc' => ($myGender === 'P' ? 'rumah' : 'masjid')];
                                $isChk = !empty($wState['checked']);
                                $locVal = ($myGender === 'P') ? 'rumah' : ($wState['loc'] === 'masjid' ? 'masjid' : 'rumah');
                            ?>
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/80 border border-slate-200/70 gap-3">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox"
                                           name="sholat_<?= $wKey ?>"
                                           id="personal-sholat-<?= $wKey ?>"
                                           value="1"
                                           <?= $isChk ? 'checked' : '' ?>
                                           onchange="onPersonalSholatCheck('<?= $wKey ?>', this.checked)"
                                           class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800"><?= $wMeta['label'] ?></p>
                                        <p class="text-[11px] text-slate-400 font-mono"><?= $wMeta['jam'] ?> WITA</p>
                                    </div>
                                </label>

                                <input type="hidden" name="sholat_<?= $wKey ?>_loc" id="personal-loc-input-<?= $wKey ?>" value="<?= e($locVal) ?>">
                                <div class="flex items-center bg-white p-1 rounded-xl border border-slate-200 text-xs font-bold shrink-0" data-personal-loc-wrap="<?= $wKey ?>">
                                    <button type="button"
                                            onclick="setPersonalSholatLoc('<?= $wKey ?>', 'rumah')"
                                            id="personal-btn-rumah-<?= $wKey ?>"
                                            class="px-3 py-1.5 rounded-lg transition-all <?= $locVal === 'rumah' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold' : 'text-slate-500 font-medium' ?>">
                                        🏠 Rumah
                                    </button>
                                    <button type="button"
                                            onclick="setPersonalSholatLoc('<?= $wKey ?>', 'masjid')"
                                            id="personal-btn-masjid-<?= $wKey ?>"
                                            class="px-3 py-1.5 rounded-lg transition-all <?= $locVal === 'masjid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold' : 'text-slate-500 font-medium' ?>">
                                        🕌 Masjid
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 2. Tilawah Al-Qur'an -->
                    <div class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox"
                                   name="tilawah_checked"
                                   id="personal-tilawah-chk"
                                   value="1"
                                   <?= !empty($myTodayState['tilawah_checked']) ? 'checked' : '' ?>
                                   class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span class="text-sm font-black text-emerald-950">📖 Sudah Tilawah Al-Qur'an Hari Ini</span>
                        </label>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Capaian Surah / Ayat / Halaman</label>
                            <input type="text"
                                   name="tilawah_surah"
                                   id="personal-tilawah-surah"
                                   value="<?= e($myTodayState['tilawah_surah'] ?? '') ?>"
                                   placeholder="Contoh: QS. Al-Kahfi: 1-20 atau 1 Halaman"
                                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- 3. Dzikir & Sholawat -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center justify-between p-3.5 rounded-2xl bg-purple-50/50 border border-purple-100 cursor-pointer">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" name="dzikir_istighfar" value="1" <?= !empty($myTodayState['dzikir_istighfar']) ? 'checked' : '' ?> class="w-5 h-5 rounded-lg text-purple-600 focus:ring-purple-500">
                                <div>
                                    <p class="text-xs font-black text-purple-950">📿 Istighfar (100x)</p>
                                    <p class="text-[11px] text-purple-600">Astaghfirullah al-Azhim</p>
                                </div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-2xl bg-purple-50/50 border border-purple-100 cursor-pointer">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" name="dzikir_sholawat" value="1" <?= !empty($myTodayState['dzikir_sholawat']) ? 'checked' : '' ?> class="w-5 h-5 rounded-lg text-purple-600 focus:ring-purple-500">
                                <div>
                                    <p class="text-xs font-black text-purple-950">✨ Sholawat Nabi (100x)</p>
                                    <p class="text-[11px] text-purple-600">Allahumma shalli 'ala Muhammad</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- 4. Tadabbur Ayat -->
                    <div class="p-4 rounded-2xl bg-amber-50/40 border border-amber-100 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="tadabbur_checked" value="1" <?= !empty($myTodayState['tadabbur_checked']) ? 'checked' : '' ?> class="w-5 h-5 rounded-lg text-amber-600 focus:ring-amber-500 border-slate-300">
                            <span class="text-sm font-black text-amber-950">💡 Sudah Tadabbur Ayat / Tadzkirah</span>
                        </label>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Catatan Renungan / Hikmah Hari Ini</label>
                            <textarea name="tadabbur_notes" rows="2" placeholder="Tuliskan hikmah singkat atau tadabbur hari ini..." class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500"><?= e($myTodayState['tadabbur_notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-black text-sm shadow-lg shadow-primary-600/20 transition-all">
                        💾 Simpan Mutaba'ah Ibadah Hari Ini
                    </button>
                </form>
            </div>

            <!-- Riwayat 14 Hari Terakhir Akun Login -->
            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <h3 class="font-black text-slate-900 text-base mb-1">Riwayat Ibadah Saya (14 Hari Terakhir)</h3>
                <p class="text-xs text-slate-500 mb-4">Tersinkronisasi otomatis dengan pengisian dari Portal Guru Mobile.</p>

                <?php if (empty($myHistoryRows)): ?>
                    <div class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                        Belum ada catatan ibadah tersimpan. Silakan centang ibadah hari ini dan klik Simpan.
                    </div>
                <?php else: ?>
                    <div class="space-y-2.5 max-h-[600px] overflow-y-auto pr-1">
                        <?php foreach ($myHistoryRows as $hr):
                            $sc = (!empty($hr['sholat_subuh']) ? 1 : 0)
                                + (!empty($hr['sholat_dzuhur']) ? 1 : 0)
                                + (!empty($hr['sholat_ashar']) ? 1 : 0)
                                + (!empty($hr['sholat_maghrib']) ? 1 : 0)
                                + (!empty($hr['sholat_isya']) ? 1 : 0);
                            $dz = (!empty($hr['dzikir_istighfar']) && !empty($hr['dzikir_sholawat']));
                        ?>
                        <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/70 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-800"><?= date('d M Y', strtotime($hr['tanggal'])) ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $sc === 5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                    Sholat <?= $sc ?>/5
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1.5 text-[11px]">
                                <span class="px-2 py-0.5 rounded-lg <?= !empty($hr['tilawah_checked']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-white text-slate-400 border border-slate-200' ?>">
                                    📖 Tilawah: <?= !empty($hr['tilawah_checked']) ? e($hr['tilawah_surah'] ?: 'Ya') : 'Belum' ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg <?= $dz ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-white text-slate-400 border border-slate-200' ?>">
                                    📿 Dzikir: <?= $dz ? 'Lengkap' : 'Belum' ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg <?= !empty($hr['tadabbur_checked']) ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-white text-slate-400 border border-slate-200' ?>">
                                    💡 Tadabbur: <?= !empty($hr['tadabbur_checked']) ? 'Ya' : 'Belum' ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($canViewAll): ?>
    <!-- ===================================================================== -->
    <!-- TAB 2: DASHBOARD MONITORING IBADAH HARIAN                             -->
    <!-- ===================================================================== -->
    <div id="tab-panel-dashboard" class="ibadah-tab-panel space-y-6 <?= $activeTab === 'dashboard' ? '' : 'hidden' ?>">

        <!-- Filter Tanggal Dashboard -->
        <form method="GET" action="<?= url('kelola-ibadah-guru') ?>" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-4 flex flex-wrap items-center justify-between gap-3">
            <input type="hidden" name="tab" value="dashboard">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Monitoring:</span>
                <input type="date" name="tanggal" value="<?= e($tanggal) ?>" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50">
            </div>
            <div class="text-xs text-slate-500">
                Menampilkan capaian ibadah guru untuk tanggal <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>
            </div>
        </form>

        <!-- 4 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Guru Mengisi Mutaba'ah</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900"><?= (int)$kpiDaily['sudah_isi'] ?></span>
                    <span class="text-sm font-bold text-slate-400">/ <?= (int)$kpiDaily['total_guru'] ?> Guru</span>
                </div>
                <div class="mt-3 w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: <?= min(100, $persenPengisian) ?>%"></div>
                </div>
                <p class="text-[11px] font-bold text-emerald-700 mt-1.5"><?= $persenPengisian ?>% Partisipasi</p>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sholat 5 Waktu Lengkap</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-700"><?= (int)$kpiDaily['sholat_lengkap'] ?></span>
                    <span class="text-xs font-bold text-slate-400">Guru (5/5)</span>
                </div>
                <div class="mt-3 flex items-center gap-2 text-[11px] font-bold">
                    <span class="px-2 py-0.5 rounded-lg bg-pink-50 text-pink-700 border border-pink-200">🏠 <?= (int)$kpiDaily['sholat_rumah'] ?> Rumah</span>
                    <span class="px-2 py-0.5 rounded-lg bg-sky-50 text-sky-700 border border-sky-200">🕌 <?= (int)$kpiDaily['sholat_masjid'] ?> Masjid</span>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tilawah & Dzikir 100x</p>
                <div class="mt-2 flex items-baseline gap-3">
                    <div>
                        <span class="text-2xl font-black text-slate-900"><?= (int)$kpiDaily['tilawah_selesai'] ?></span>
                        <span class="text-xs font-bold text-slate-400">Tilawah</span>
                    </div>
                    <span class="text-slate-300">|</span>
                    <div>
                        <span class="text-2xl font-black text-purple-700"><?= (int)$kpiDaily['dzikir_lengkap'] ?></span>
                        <span class="text-xs font-bold text-slate-400">Dzikir 2/2</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Istighfar & Sholawat + Tilawah Qur'an</p>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tadabbur & Capaian 4/4</p>
                <div class="mt-2 flex items-baseline gap-3">
                    <div>
                        <span class="text-2xl font-black text-amber-600"><?= (int)$kpiDaily['tadabbur_selesai'] ?></span>
                        <span class="text-xs font-bold text-slate-400">Tadabbur</span>
                    </div>
                    <span class="text-slate-300">|</span>
                    <div>
                        <span class="text-2xl font-black text-emerald-600"><?= (int)$kpiDaily['empat_sempurna'] ?></span>
                        <span class="text-xs font-bold text-slate-400">4/4 Penuh</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Belum mengisi: <strong><?= (int)$kpiDaily['belum_isi'] ?></strong> Guru</p>
            </div>
        </div>

        <!-- Ringkasan Cepat Pengisi Mutaba'ah Tanggal Ini -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-slate-900 text-sm sm:text-base">Ringkasan Pelaksanaan Ibadah Guru (<?= date('d M Y', strtotime($tanggal)) ?>)</h3>
                    <p class="text-xs text-slate-500">Menampilkan status pengisian harian seluruh guru aktif.</p>
                </div>
                <button type="button" onclick="switchIbadahTab('harian')" class="text-xs font-bold text-primary-600 hover:text-primary-700">
                    Lihat Detail Lengkap →
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Guru / Pegawai</th>
                            <th class="px-4 py-3 text-center">L/P</th>
                            <th class="px-4 py-3 text-center">Sholat 5 Waktu</th>
                            <th class="px-4 py-3 text-center">Tilawah</th>
                            <th class="px-4 py-3 text-center">Dzikir</th>
                            <th class="px-4 py-3 text-center">Tadabbur</th>
                            <th class="px-4 py-3 text-center">Skor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php foreach (array_slice($dailyRows, 0, 20) as $dr):
                            $st = $dr['state'];
                        ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3">
                                <p class="font-bold text-slate-800"><?= e($dr['nama']) ?></p>
                                <p class="text-[11px] text-slate-400"><?= e($dr['unit_tugas']) ?> &bull; <?= e($dr['jabatan']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $dr['gender'] === 'P' ? 'bg-pink-50 text-pink-700' : 'bg-sky-50 text-sky-700' ?>">
                                    <?= $dr['gender'] === 'P' ? '👩 P' : '👨 L' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold <?= $st['sholat_count'] === 5 ? 'text-emerald-700' : ($st['sholat_count'] > 0 ? 'text-amber-600' : 'text-slate-400') ?>">
                                <?= $st['sholat_count'] ?>/5
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?= $st['tilawah_checked'] ? '<span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold">✓ Ya</span>' : '<span class="text-slate-400">—</span>' ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?= ($st['dzikir_istighfar'] && $st['dzikir_sholawat']) ? '<span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-bold">✓ 2/2</span>' : (($st['dzikir_istighfar'] || $st['dzikir_sholawat']) ? '<span class="text-amber-600 font-bold">1/2</span>' : '<span class="text-slate-400">—</span>') ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?= $st['tadabbur_checked'] ? '<span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold">✓ Ya</span>' : '<span class="text-slate-400">—</span>' ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black <?= $dr['komponen_done'] === 4 ? 'bg-emerald-600 text-white' : ($dr['has_filled'] ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-400') ?>">
                                    <?= $dr['komponen_done'] ?>/4
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: DATA HARIAN IBADAH GURU (DETAIL PER WAKTU & LOKASI)            -->
    <!-- ===================================================================== -->
    <div id="tab-panel-harian" class="ibadah-tab-panel space-y-5 <?= $activeTab === 'harian' ? '' : 'hidden' ?>">

        <!-- Filter Data Harian -->
        <form method="GET" action="<?= url('kelola-ibadah-guru') ?>" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <input type="hidden" name="tab" value="harian">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="<?= e($tanggal) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Guru</label>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Nama / NIY..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Unit Tugas</label>
                <select name="unit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">Semua Unit</option>
                    <?php foreach ($unitOptions as $uOpt): ?>
                        <option value="<?= e($uOpt) ?>" <?= $filterUnit === $uOpt ? 'selected' : '' ?>><?= e($uOpt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Jenis Kelamin</label>
                <select name="gender" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">Semua (L/P)</option>
                    <option value="P" <?= $filterGender === 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
                    <option value="L" <?= $filterGender === 'L' ? 'selected' : '' ?>>Laki-laki (L)</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Pengisian</label>
                <select name="status_isi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">Semua Guru</option>
                    <option value="sudah" <?= $filterStatus === 'sudah' ? 'selected' : '' ?>>Sudah Mengisi</option>
                    <option value="belum" <?= $filterStatus === 'belum' ? 'selected' : '' ?>>Belum Mengisi</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold">Filter</button>
                <a href="<?= url('kelola-ibadah-guru?tab=harian') ?>" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">Reset</a>
            </div>
        </form>

        <!-- Tabel Detail Harian -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Nama Guru</th>
                            <th class="px-2 py-3.5 text-center">Subuh</th>
                            <th class="px-2 py-3.5 text-center">Dzuhur</th>
                            <th class="px-2 py-3.5 text-center">Ashar</th>
                            <th class="px-2 py-3.5 text-center">Maghrib</th>
                            <th class="px-2 py-3.5 text-center">Isya</th>
                            <th class="px-3 py-3.5">Tilawah</th>
                            <th class="px-3 py-3.5 text-center">Dzikir</th>
                            <th class="px-3 py-3.5">Tadabbur</th>
                            <th class="px-4 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($dailyRows)): ?>
                        <tr>
                            <td colspan="11" class="px-6 py-10 text-center text-slate-400">Tidak ada data guru yang sesuai dengan filter.</td>
                        </tr>
                        <?php else: foreach ($dailyRows as $idx => $dr):
                            $st = $dr['state'];
                            $rowJson = htmlspecialchars(json_encode([
                                'guru_id' => $dr['pegawai_id'],
                                'nama' => $dr['nama'],
                                'gender' => $dr['gender'],
                                'tanggal' => $tanggal,
                                'state' => $st,
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-slate-400"><?= $idx + 1 ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <?php if (!empty($dr['avatar_url'])): ?>
                                        <img src="<?= e($dr['avatar_url']) ?>" alt="" class="w-8 h-8 rounded-xl object-cover shrink-0">
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-xl bg-primary-100 text-primary-700 font-bold text-[11px] flex items-center justify-center shrink-0">
                                            <?= strtoupper(substr($dr['nama'], 0, 2)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-bold text-slate-800"><?= e($dr['nama']) ?></p>
                                        <p class="text-[10px] text-slate-400">
                                            <?= $dr['gender'] === 'P' ? '👩 Perempuan' : '👨 Laki-laki' ?> &bull; <?= e($dr['unit_tugas']) ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <?php foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w):
                                $sInfo = $st['sholat'][$w];
                            ?>
                            <td class="px-2 py-3 text-center">
                                <?php if ($sInfo['checked']): ?>
                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-lg text-[10px] font-bold <?= $sInfo['loc'] === 'masjid' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                        ✓ <?= $sInfo['loc'] === 'masjid' ? '🕌' : '🏠' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="px-3 py-3">
                                <?php if ($st['tilawah_checked']): ?>
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[10px]">✓ Selesai</span>
                                    <?php if ($st['tilawah_surah'] !== ''): ?>
                                        <p class="text-[10px] text-slate-500 mt-0.5 truncate max-w-[140px]" title="<?= e($st['tilawah_surah']) ?>"><?= e($st['tilawah_surah']) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-slate-400">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="flex flex-col items-center gap-0.5 text-[10px]">
                                    <span class="<?= $st['dzikir_istighfar'] ? 'text-purple-700 font-bold' : 'text-slate-300' ?>">
                                        <?= $st['dzikir_istighfar'] ? '✓ Istighfar' : '· Istighfar' ?>
                                    </span>
                                    <span class="<?= $st['dzikir_sholawat'] ? 'text-purple-700 font-bold' : 'text-slate-300' ?>">
                                        <?= $st['dzikir_sholawat'] ? '✓ Sholawat' : '· Sholawat' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <?php if ($st['tadabbur_checked']): ?>
                                    <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 font-bold text-[10px]">✓ Tercatat</span>
                                    <?php if ($st['tadabbur_notes'] !== ''): ?>
                                        <p class="text-[10px] text-slate-500 mt-0.5 line-clamp-2 max-w-[160px]" title="<?= e($st['tadabbur_notes']) ?>"><?= e($st['tadabbur_notes']) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-slate-400">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <?php if ($canManage): ?>
                                <button type="button"
                                        onclick='openAdminIbadahModal(<?= $rowJson ?>)'
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-primary-50 text-slate-700 hover:text-primary-700 font-bold text-[11px] transition-colors">
                                    Edit
                                </button>
                                <?php endif; ?>
                                <?php if ($canDelete && $dr['mutabaah_id'] > 0): ?>
                                <form method="POST" action="<?= url('kelola-ibadah-guru/delete/' . $dr['mutabaah_id']) ?>" class="inline" onsubmit="return confirm('Hapus catatan ibadah <?= e($dr['nama']) ?> tanggal <?= e($tanggal) ?>?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px]">Hapus</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: REKAPITULASI PERIODE & MATRIKS IBADAH                          -->
    <!-- ===================================================================== -->
    <div id="tab-panel-rekap" class="ibadah-tab-panel space-y-6 <?= $activeTab === 'rekap' ? '' : 'hidden' ?>">

        <!-- Filter Periode -->
        <form method="GET" action="<?= url('kelola-ibadah-guru') ?>" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <input type="hidden" name="tab" value="rekap">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?= e($startDate) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= e($endDate) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Guru</label>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Nama / NIY..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Unit</label>
                <select name="unit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">Semua Unit</option>
                    <?php foreach ($unitOptions as $uOpt): ?>
                        <option value="<?= e($uOpt) ?>" <?= $filterUnit === $uOpt ? 'selected' : '' ?>><?= e($uOpt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">L/P</label>
                <select name="gender" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">Semua</option>
                    <option value="P" <?= $filterGender === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    <option value="L" <?= $filterGender === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold">Terapkan</button>
                <a href="<?= url('kelola-ibadah-guru/cetak?' . $cetakQueryRekap) ?>" target="_blank" class="py-2 px-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">Cetak</a>
            </div>
        </form>

        <!-- Tabel Rekap Akumulasi Periode -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="font-black text-slate-900 text-sm sm:text-base">Rekapitulasi Capaian Ibadah Guru</h3>
                    <p class="text-xs text-slate-500">Periode <strong><?= date('d M Y', strtotime($startDate)) ?></strong> s/d <strong><?= date('d M Y', strtotime($endDate)) ?></strong> (<?= (int)$totalDaysInRange ?> Hari)</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Nama Guru</th>
                            <th class="px-3 py-3.5 text-center">L/P</th>
                            <th class="px-3 py-3.5 text-center">Hari Mengisi</th>
                            <th class="px-3 py-3.5 text-center">Total Sholat</th>
                            <th class="px-3 py-3.5 text-center">Sholat 5/5 (Hari)</th>
                            <th class="px-3 py-3.5 text-center">🏠 / 🕌</th>
                            <th class="px-3 py-3.5 text-center">Tilawah (Hari)</th>
                            <th class="px-3 py-3.5 text-center">Dzikir (Hari)</th>
                            <th class="px-3 py-3.5 text-center">Tadabbur (Hari)</th>
                            <th class="px-4 py-3.5 text-center">Keaktifan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php foreach ($rekapRows as $i => $rr): ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-slate-400"><?= $i + 1 ?></td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-800"><?= e($rr['nama']) ?></p>
                                <p class="text-[10px] text-slate-400"><?= e($rr['unit_tugas']) ?> &bull; NIY: <?= e($rr['niy']) ?></p>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $rr['gender'] === 'P' ? 'bg-pink-50 text-pink-700' : 'bg-sky-50 text-sky-700' ?>">
                                    <?= $rr['gender'] ?>
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center font-bold text-slate-800"><?= $rr['hari_mengisi'] ?> / <?= $rr['total_hari'] ?></td>
                            <td class="px-3 py-3 text-center">
                                <span class="font-black text-emerald-700"><?= $rr['total_sholat'] ?></span>
                                <span class="text-[10px] text-slate-400">(<?= $rr['persen_sholat'] ?>%)</span>
                            </td>
                            <td class="px-3 py-3 text-center font-bold text-slate-700"><?= $rr['hari_sholat_lengkap'] ?> hr</td>
                            <td class="px-3 py-3 text-center whitespace-nowrap text-[11px]">
                                <span class="text-emerald-700 font-bold">🏠 <?= $rr['sholat_rumah'] ?></span> /
                                <span class="text-sky-700 font-bold">🕌 <?= $rr['sholat_masjid'] ?></span>
                            </td>
                            <td class="px-3 py-3 text-center font-bold text-emerald-700"><?= $rr['hari_tilawah'] ?> hr</td>
                            <td class="px-3 py-3 text-center font-bold text-purple-700"><?= $rr['hari_dzikir'] ?> hr</td>
                            <td class="px-3 py-3 text-center font-bold text-amber-600"><?= $rr['hari_tadabbur'] ?> hr</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black <?= $rr['persen_keaktifan'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($rr['persen_keaktifan'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500') ?>">
                                    <?= $rr['persen_keaktifan'] ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Matriks Harian Visual -->
        <?php if (!empty($periodDates)): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-black text-slate-900 text-sm sm:text-base">Matriks Pengisian Harian (<?= date('d M', strtotime($startDate)) ?> – <?= date('d M Y', strtotime($endDate)) ?>)</h3>
                <p class="text-xs text-slate-500">Hijau (✓) = Lengkap 4/4 • Kuning (●) = Sebagian • Abu (✕) = Belum mengisi</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-2.5 font-bold text-slate-600 sticky left-0 bg-slate-50 min-w-[180px]">Nama Guru</th>
                            <?php foreach ($periodDates as $pDate): ?>
                                <th class="px-1.5 py-2.5 text-center font-mono text-slate-500 min-w-[28px]"><?= date('d', strtotime($pDate)) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($rekapRows as $rr):
                            $gId = (int)$rr['pegawai_id'];
                        ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-2 font-bold text-slate-800 sticky left-0 bg-white truncate max-w-[200px]"><?= e($rr['nama']) ?></td>
                            <?php foreach ($periodDates as $pDate):
                                $cell = $matrixMap[$gId][$pDate] ?? null;
                            ?>
                            <td class="px-1 py-2 text-center">
                                <?php if ($cell && $cell['done'] === 4): ?>
                                    <span class="inline-flex w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 font-black items-center justify-center" title="4/4 Lengkap">✓</span>
                                <?php elseif ($cell): ?>
                                    <span class="inline-flex w-5 h-5 rounded-md bg-amber-100 text-amber-700 font-black items-center justify-center" title="<?= (int)$cell['sholat_count'] ?>/5 Sholat"><?= (int)$cell['done'] ?></span>
                                <?php else: ?>
                                    <span class="text-slate-300">·</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Modal Input / Koreksi Ibadah Guru (Admin) -->
<?php if ($canViewAll && $canManage): ?>
<div id="modal-admin-ibadah" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 flex items-center justify-center">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden my-auto">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div>
                <h3 class="font-black text-slate-900 text-base">Input / Koreksi Mutaba'ah Ibadah Guru</h3>
                <p class="text-xs text-slate-500">Perempuan otomatis diarahkan ke Tab Rumah saat centang sholat.</p>
            </div>
            <button type="button" onclick="closeAdminIbadahModal()" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/70">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-ibadah-guru/store') ?>" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="harian">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Guru</label>
                    <select name="guru_id" id="modal-guru-id" onchange="onModalGuruChange()" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800" required>
                        <?php foreach ($allTeachers as $at):
                            $rawJk = strtoupper(trim((string)($at['jenis_kelamin'] ?? 'P')));
                            $gCode = ($rawJk === 'L' || str_starts_with($rawJk, 'LAKI')) ? 'L' : 'P';
                        ?>
                            <option value="<?= (int)$at['id'] ?>" data-gender="<?= $gCode ?>"><?= e($at['nama']) ?> (<?= $gCode ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" id="modal-tanggal" value="<?= e($tanggal) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800" required>
                </div>
            </div>

            <div class="space-y-2">
                <p class="text-xs font-black text-slate-800">Sholat Fardhu 5 Waktu</p>
                <?php foreach ($waktus as $wKey => $wMeta): ?>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/70">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="sholat_<?= $wKey ?>" id="modal-chk-<?= $wKey ?>" value="1" onchange="onModalAdminSholatCheck('<?= $wKey ?>', this.checked)" class="w-4 h-4 rounded text-emerald-600">
                        <span class="text-xs font-bold text-slate-800"><?= $wMeta['label'] ?></span>
                    </label>
                    <input type="hidden" name="sholat_<?= $wKey ?>_loc" id="modal-loc-input-<?= $wKey ?>" value="rumah">
                    <div class="flex items-center gap-1 bg-white p-0.5 rounded-lg border border-slate-200 text-[11px] font-bold">
                        <button type="button" onclick="setModalAdminLoc('<?= $wKey ?>', 'rumah')" id="modal-btn-rumah-<?= $wKey ?>" class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">🏠 Rumah</button>
                        <button type="button" onclick="setModalAdminLoc('<?= $wKey ?>', 'masjid')" id="modal-btn-masjid-<?= $wKey ?>" class="px-2.5 py-1 rounded-md text-slate-500">🕌 Masjid</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50/50 border border-emerald-100 cursor-pointer">
                    <input type="checkbox" name="tilawah_checked" id="modal-tilawah-chk" value="1" class="w-4 h-4 rounded text-emerald-600">
                    <span class="text-xs font-bold text-emerald-900">📖 Sudah Tilawah</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-amber-50/50 border border-amber-100 cursor-pointer">
                    <input type="checkbox" name="tadabbur_checked" id="modal-tadabbur-chk" value="1" class="w-4 h-4 rounded text-amber-600">
                    <span class="text-xs font-bold text-amber-900">💡 Sudah Tadabbur</span>
                </label>
            </div>

            <input type="text" name="tilawah_surah" id="modal-tilawah-surah" placeholder="Catatan Surah/Ayat Tilawah..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">

            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-purple-50/50 border border-purple-100 cursor-pointer">
                    <input type="checkbox" name="dzikir_istighfar" id="modal-dzikir-istighfar" value="1" class="w-4 h-4 rounded text-purple-600">
                    <span class="text-xs font-bold text-purple-900">📿 Istighfar 100x</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-purple-50/50 border border-purple-100 cursor-pointer">
                    <input type="checkbox" name="dzikir_sholawat" id="modal-dzikir-sholawat" value="1" class="w-4 h-4 rounded text-purple-600">
                    <span class="text-xs font-bold text-purple-900">✨ Sholawat 100x</span>
                </label>
            </div>

            <textarea name="tadabbur_notes" id="modal-tadabbur-notes" rows="2" placeholder="Catatan Tadabbur..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs"></textarea>

            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="closeAdminIbadahModal()" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Batal</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-md">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    const MY_TEACHER_GENDER = '<?= e($myGender) ?>';
    const SHOLAT_KEYS = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];

    function switchIbadahTab(tabKey) {
        document.querySelectorAll('.ibadah-tab-panel').forEach(el => el.classList.add('hidden'));
        const panel = document.getElementById('tab-panel-' + tabKey);
        if (panel) panel.classList.remove('hidden');

        document.querySelectorAll('.ibadah-tab-btn').forEach(btn => {
            const k = btn.getAttribute('data-ibadah-tab-btn');
            if (k === tabKey) {
                btn.className = 'ibadah-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all bg-primary-600 text-white shadow-md shadow-primary-600/20';
            } else if (k === 'ibadah_saya') {
                btn.className = 'ibadah-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/70';
            } else {
                btn.className = 'ibadah-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all bg-slate-100 text-slate-600 hover:bg-slate-200/80';
            }
        });

        document.querySelectorAll('.ibadah-sidebar-link').forEach(link => {
            const k = link.getAttribute('data-ibadah-sidebar-tab');
            if (k === tabKey) {
                link.classList.add('bg-primary-600/30', 'text-white', 'font-semibold', 'border-primary-500/40');
                link.classList.remove('text-primary-200', 'font-medium', 'border-transparent');
            } else {
                link.classList.remove('bg-primary-600/30', 'text-white', 'font-semibold', 'border-primary-500/40');
                link.classList.add('text-primary-200', 'font-medium', 'border-transparent');
            }
        });

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabKey);
        window.history.replaceState({}, '', url.toString());

        const cetakBtn = document.getElementById('btn-cetak-ibadah');
        if (cetakBtn) {
            cetakBtn.href = tabKey === 'rekap'
                ? '<?= url('kelola-ibadah-guru/cetak?' . $cetakQueryRekap) ?>'
                : '<?= url('kelola-ibadah-guru/cetak?' . $cetakQueryHarian) ?>';
        }
    }

    // Aturan khusus form Isi Ibadah Saya: Perempuan ('P') -> centang sholat otomatis ke tab Rumah semua
    function setPersonalSholatLoc(waktu, loc) {
        const effectiveLoc = (MY_TEACHER_GENDER === 'P') ? 'rumah' : (loc === 'masjid' ? 'masjid' : 'rumah');
        const hidden = document.getElementById('personal-loc-input-' + waktu);
        const btnR = document.getElementById('personal-btn-rumah-' + waktu);
        const btnM = document.getElementById('personal-btn-masjid-' + waktu);
        if (hidden) hidden.value = effectiveLoc;
        if (btnR && btnM) {
            if (effectiveLoc === 'rumah') {
                btnR.className = 'px-3 py-1.5 rounded-lg transition-all bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold';
                btnM.className = 'px-3 py-1.5 rounded-lg transition-all text-slate-500 font-medium';
            } else {
                btnM.className = 'px-3 py-1.5 rounded-lg transition-all bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold';
                btnR.className = 'px-3 py-1.5 rounded-lg transition-all text-slate-500 font-medium';
            }
        }
    }

    function onPersonalSholatCheck(waktu, isChecked) {
        if (MY_TEACHER_GENDER === 'P' && isChecked) {
            SHOLAT_KEYS.forEach(w => setPersonalSholatLoc(w, 'rumah'));
        }
    }

    // Modal Admin Helpers
    function getModalSelectedGender() {
        const sel = document.getElementById('modal-guru-id');
        if (!sel || sel.selectedIndex < 0) return 'P';
        return sel.options[sel.selectedIndex].getAttribute('data-gender') || 'P';
    }

    function setModalAdminLoc(waktu, loc) {
        const g = getModalSelectedGender();
        const effectiveLoc = (g === 'P') ? 'rumah' : (loc === 'masjid' ? 'masjid' : 'rumah');
        const inp = document.getElementById('modal-loc-input-' + waktu);
        const btnR = document.getElementById('modal-btn-rumah-' + waktu);
        const btnM = document.getElementById('modal-btn-masjid-' + waktu);
        if (inp) inp.value = effectiveLoc;
        if (btnR && btnM) {
            if (effectiveLoc === 'rumah') {
                btnR.className = 'px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold';
                btnM.className = 'px-2.5 py-1 rounded-md text-slate-500 font-medium';
            } else {
                btnM.className = 'px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold';
                btnR.className = 'px-2.5 py-1 rounded-md text-slate-500 font-medium';
            }
        }
    }

    function onModalAdminSholatCheck(waktu, isChecked) {
        if (getModalSelectedGender() === 'P' && isChecked) {
            SHOLAT_KEYS.forEach(w => setModalAdminLoc(w, 'rumah'));
        }
    }

    function onModalGuruChange() {
        const g = getModalSelectedGender();
        const defaultLoc = (g === 'P') ? 'rumah' : 'masjid';
        SHOLAT_KEYS.forEach(w => setModalAdminLoc(w, defaultLoc));
    }

    function openAdminIbadahModal(row = null) {
        const modal = document.getElementById('modal-admin-ibadah');
        if (!modal) return;
        const guruSel = document.getElementById('modal-guru-id');
        const tglInp = document.getElementById('modal-tanggal');

        if (row && row.guru_id) {
            if (guruSel) guruSel.value = String(row.guru_id);
            if (tglInp) tglInp.value = row.tanggal || '<?= e($tanggal) ?>';
            const st = row.state || {};
            SHOLAT_KEYS.forEach(w => {
                const chk = document.getElementById('modal-chk-' + w);
                const sData = (st.sholat && st.sholat[w]) || {};
                if (chk) chk.checked = Boolean(sData.checked);
                setModalAdminLoc(w, row.gender === 'P' ? 'rumah' : (sData.loc || 'masjid'));
            });
            document.getElementById('modal-tilawah-chk').checked = Boolean(st.tilawah_checked);
            document.getElementById('modal-tilawah-surah').value = st.tilawah_surah || '';
            document.getElementById('modal-dzikir-istighfar').checked = Boolean(st.dzikir_istighfar);
            document.getElementById('modal-dzikir-sholawat').checked = Boolean(st.dzikir_sholawat);
            document.getElementById('modal-tadabbur-chk').checked = Boolean(st.tadabbur_checked);
            document.getElementById('modal-tadabbur-notes').value = st.tadabbur_notes || '';
        } else {
            if (tglInp) tglInp.value = '<?= e($tanggal) ?>';
            SHOLAT_KEYS.forEach(w => {
                const chk = document.getElementById('modal-chk-' + w);
                if (chk) chk.checked = false;
            });
            onModalGuruChange();
            document.getElementById('modal-tilawah-chk').checked = false;
            document.getElementById('modal-tilawah-surah').value = '';
            document.getElementById('modal-dzikir-istighfar').checked = false;
            document.getElementById('modal-dzikir-sholawat').checked = false;
            document.getElementById('modal-tadabbur-chk').checked = false;
            document.getElementById('modal-tadabbur-notes').value = '';
        }

        modal.classList.remove('hidden');
    }

    function closeAdminIbadahModal() {
        const modal = document.getElementById('modal-admin-ibadah');
        if (modal) modal.classList.add('hidden');
    }
</script>
