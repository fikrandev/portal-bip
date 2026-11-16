<?php /** Dashboard View — Dynamic Role-Based Widgets & Module Grid */ ?>

<?php
$bannerPills = [];
if (!empty($dashData['system'])) {
    $bannerPills[] = ['val' => $dashData['system']['total_users'], 'label' => 'Pengguna', 'icon' => 'user'];
    $bannerPills[] = ['val' => $dashData['system']['total_modules'], 'label' => 'Modul Aktif', 'icon' => 'module'];
    $bannerPills[] = ['val' => $dashData['system']['total_roles'], 'label' => 'Peran', 'icon' => 'role'];
}
if (!empty($dashData['siswa'])) {
    $bannerPills[] = ['val' => $dashData['siswa']['total'], 'label' => 'Siswa Aktif', 'icon' => 'academic'];
}
if (!empty($dashData['pegawai'])) {
    $bannerPills[] = ['val' => $dashData['pegawai']['total'], 'label' => 'Pegawai Aktif', 'icon' => 'briefcase'];
}
if (!empty($dashData['absen_siswa'])) {
    $bannerPills[] = ['val' => $dashData['absen_siswa']['hadir'], 'label' => 'Siswa Hadir Hari Ini', 'icon' => 'check'];
}
if (!empty($dashData['jurnal'])) {
    $bannerPills[] = ['val' => $dashData['jurnal']['total'], 'label' => 'Jurnal Hari Ini', 'icon' => 'book'];
}
if (!empty($dashData['spmb'])) {
    $bannerPills[] = ['val' => $dashData['spmb']['total'], 'label' => 'Pendaftar SPMB', 'icon' => 'users'];
}
if (!empty($dashData['sarpras'])) {
    $bannerPills[] = ['val' => $dashData['sarpras']['total_barang'], 'label' => 'Aset Sarpras', 'icon' => 'cube'];
}
if (!empty($dashData['cuti']) && $dashData['cuti']['pending'] > 0) {
    $bannerPills[] = ['val' => $dashData['cuti']['pending'], 'label' => 'Cuti Menunggu', 'icon' => 'clock'];
}
if (!empty($dashData['izin_guru']) && $dashData['izin_guru']['pending'] > 0) {
    $bannerPills[] = ['val' => $dashData['izin_guru']['pending'], 'label' => 'Izin Menunggu', 'icon' => 'clock'];
}

$displayPills = array_slice($bannerPills, 0, 4);
?>

<!-- Welcome Banner -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-500 via-primary-400 to-primary-400 p-6 sm:p-8 mb-8 shadow-lg shadow-primary-500/20">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white rounded-full"></div>
        <div class="absolute -bottom-8 -left-8 w-36 h-36 bg-white rounded-full"></div>
        <div class="absolute top-1/2 right-1/4 w-20 h-20 bg-white rounded-full"></div>
    </div>
    
    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-2">
            <?php $dashAvatar = Auth::avatar(); ?>
            <?php if ($dashAvatar): ?>
                <img src="<?= e($dashAvatar) ?>" alt="<?= e(Auth::name()) ?>" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover border-2 border-white/70 shadow-md shrink-0">
            <?php else: ?>
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/20 backdrop-blur-sm border-2 border-white/40 text-white font-black text-xl flex items-center justify-center shadow-md shrink-0">
                    <?= Auth::initials() ?>
                </div>
            <?php endif; ?>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-0.5">
                    Selamat Datang, <?= e(Auth::name()) ?>! 👋
                </h2>
                <p class="text-primary-100 text-xs sm:text-sm">
                    Kelola modul, wewenang, dan informasi penting portal dari satu tempat.
                </p>
            </div>
        </div>
        
        <!-- Dynamic Quick Stats Pills -->
        <?php if (!empty($displayPills)): ?>
        <div class="flex flex-wrap gap-3 sm:gap-4 mt-6">
            <?php foreach ($displayPills as $pill): ?>
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/10">
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white shrink-0">
                    <?php if ($pill['icon'] === 'user' || $pill['icon'] === 'users'): ?>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                        </svg>
                    <?php elseif ($pill['icon'] === 'academic'): ?>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/>
                        </svg>
                    <?php elseif ($pill['icon'] === 'book'): ?>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                        </svg>
                    <?php elseif ($pill['icon'] === 'cube'): ?>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                        </svg>
                    <?php elseif ($pill['icon'] === 'clock'): ?>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    <?php else: ?>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-base sm:text-lg font-bold text-white leading-tight"><?= e((string)$pill['val']) ?></p>
                    <p class="text-[11px] text-white/80 font-medium"><?= e($pill['label']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================== -->
<!-- FITUR DASHBOARD (BERDASARKAN RBAC HAK AKSES PERAN PENGGUNA)   -->
<!-- ============================================================== -->
<?php if (!empty($dashData)): ?>
<div class="mb-10">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                Ringkasan Fitur & Metrik Dashboard
            </h3>
            <p class="text-xs text-slate-500">
                Fitur dashboard yang aktif sesuai hak akses peran Anda.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <!-- 1. Kesiswaan Widget -->
        <?php if (isset($dashData['siswa'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-sky-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        🎓
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60 uppercase">
                        Siswa
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Siswa Aktif</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['siswa']['total']) ?></p>
                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700">♂ <?= $dashData['siswa']['laki'] ?> L</span>
                    <span class="px-2 py-0.5 rounded-lg bg-pink-50 text-pink-700">♀ <?= $dashData['siswa']['perempuan'] ?> P</span>
                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">🏫 <?= $dashData['siswa']['kelas'] ?> Rombel</span>
                </div>
            </div>
            <a href="<?= url('siswa') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center justify-between">
                <span>Kelola Data Siswa</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 2. Kepegawaian Widget -->
        <?php if (isset($dashData['pegawai'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-emerald-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        👥
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 uppercase">
                        Pegawai
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Guru & Staf Pegawai</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['pegawai']['total']) ?></p>
                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700"><?= $dashData['pegawai']['guru'] ?> Pendidik</span>
                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700"><?= $dashData['pegawai']['staf'] ?> Kependidikan</span>
                </div>
            </div>
            <a href="<?= url('pegawai') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center justify-between">
                <span>Kelola Data Pegawai</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 3. Presensi Siswa Widget -->
        <?php if (isset($dashData['absen_siswa'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        📅
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 uppercase">
                        Presensi Siswa
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kehadiran Hari Ini</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['absen_siswa']['hadir']) ?> <span class="text-xs font-normal text-slate-400">hadir</span></p>
                <div class="grid grid-cols-4 gap-1 text-center text-[10px] font-bold">
                    <div class="bg-emerald-50 text-emerald-700 rounded-lg py-1">H: <?= $dashData['absen_siswa']['hadir'] ?></div>
                    <div class="bg-amber-50 text-amber-700 rounded-lg py-1">S: <?= $dashData['absen_siswa']['sakit'] ?></div>
                    <div class="bg-blue-50 text-blue-700 rounded-lg py-1">I: <?= $dashData['absen_siswa']['izin'] ?></div>
                    <div class="bg-rose-50 text-rose-700 rounded-lg py-1">A: <?= $dashData['absen_siswa']['alpha'] ?></div>
                </div>
            </div>
            <a href="<?= url('absen-siswa') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center justify-between">
                <span>Rekap Absen Siswa</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 4. Presensi Pegawai Widget -->
        <?php if (isset($dashData['absen_pegawai'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-cyan-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        ⏰
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/60 uppercase">
                        Absen Pegawai
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Presensi Guru & Pegawai</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['absen_pegawai']['hadir']) ?> <span class="text-xs font-normal text-slate-400">masuk</span></p>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <span class="px-2.5 py-1 rounded-xl bg-cyan-50 text-cyan-700">Sudah Pulang: <?= $dashData['absen_pegawai']['pulang'] ?></span>
                </div>
            </div>
            <a href="<?= url('absen-pegawai') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center justify-between">
                <span>Monitoring Presensi Pegawai</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 5. Jurnal Mengajar Guru Widget -->
        <?php if (isset($dashData['jurnal'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-teal-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        📖
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200/60 uppercase">
                        Jurnal Guru
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jurnal Terisi Hari Ini</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['jurnal']['total']) ?> <span class="text-xs font-normal text-slate-400">sesi</span></p>
                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="px-2 py-0.5 rounded-lg bg-teal-50 text-teal-700">⏱ <?= $dashData['jurnal']['total_jp'] ?> JP</span>
                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">👤 <?= $dashData['jurnal']['total_guru'] ?> Guru</span>
                </div>
            </div>
            <a href="<?= url('kelola-jurnal-guru') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center justify-between">
                <span>Kelola & Validasi Jurnal</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 6. Mutaba'ah Ibadah Guru Widget -->
        <?php if (isset($dashData['ibadah_guru'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-violet-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        🤲
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-violet-50 text-violet-700 border border-violet-200/60 uppercase">
                        Ibadah Guru
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Mutaba'ah Hari Ini</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['ibadah_guru']['total_terisi']) ?> <span class="text-xs font-normal text-slate-400">guru</span></p>
                <p class="text-[11px] text-slate-400 font-medium">Pengisian sholat, tilawah & dzikir harian</p>
            </div>
            <a href="<?= url('kelola-ibadah-guru') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-violet-600 hover:text-violet-700 flex items-center justify-between">
                <span>Rekap Mutaba'ah Guru</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 7. SPMB Widget -->
        <?php if (isset($dashData['spmb'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-orange-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        📝
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200/60 uppercase">
                        SPMB
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pendaftar Calon Santri</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['spmb']['total']) ?></p>
                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700">⏳ <?= $dashData['spmb']['menunggu'] ?> Menunggu</span>
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700">🎉 <?= $dashData['spmb']['lulus'] ?> Lulus</span>
                </div>
            </div>
            <a href="<?= url('spmb') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center justify-between">
                <span>Buka Pusat SPMB</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 8. Cuti & Izin Pegawai Widget -->
        <?php if (isset($dashData['cuti']) || isset($dashData['izin_guru'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-purple-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        📋
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/60 uppercase">
                        Cuti & Izin
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Permohonan Menunggu</h4>
                <p class="text-2xl font-black text-slate-800 mb-3">
                    <?= (int)($dashData['cuti']['pending'] ?? 0) + (int)($dashData['izin_guru']['pending'] ?? 0) ?>
                    <span class="text-xs font-normal text-slate-400">pengajuan</span>
                </p>
                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <?php if (isset($dashData['cuti'])): ?>
                        <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700">Cuti: <?= $dashData['cuti']['pending'] ?></span>
                    <?php endif; ?>
                    <?php if (isset($dashData['izin_guru'])): ?>
                        <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700">Izin: <?= $dashData['izin_guru']['pending'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs font-bold">
                <?php if (isset($dashData['cuti'])): ?>
                    <a href="<?= url('cuti') ?>" class="text-purple-600 hover:text-purple-700">Kelola Cuti →</a>
                <?php endif; ?>
                <?php if (isset($dashData['izin_guru'])): ?>
                    <a href="<?= url('izin-guru') ?>" class="text-indigo-600 hover:text-indigo-700">Kelola Izin →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 9. Sarpras Widget -->
        <?php if (isset($dashData['sarpras'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-amber-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        📦
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 uppercase">
                        Sarpras
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Aset Sarpras</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['sarpras']['total_barang']) ?> <span class="text-xs font-normal text-slate-400">item</span></p>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <span class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700">Peminjaman: <?= $dashData['sarpras']['pinjam_aktif'] ?></span>
                </div>
            </div>
            <a href="<?= url('sarpras') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center justify-between">
                <span>Inventaris Sarpras</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 10. Kedisiplinan Siswa Widget -->
        <?php if (isset($dashData['pelanggaran'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-rose-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        ⚠️
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 uppercase">
                        Kedisiplinan
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Keterlambatan Hari Ini</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['pelanggaran']['hari_ini']) ?></p>
                <p class="text-[11px] text-slate-400 font-medium">Total <?= $dashData['pelanggaran']['total'] ?> catatan keterlambatan</p>
            </div>
            <a href="<?= url('pelanggaran-siswa') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center justify-between">
                <span>Catatan Pelanggaran Siswa</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- 11. Sistem & Admin Widget -->
        <?php if (isset($dashData['system'])): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs hover:border-primary-300 hover:shadow-md transition-all flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                        ⚙️
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200/60 uppercase">
                        Sistem
                    </span>
                </div>
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pengguna & Peran</h4>
                <p class="text-2xl font-black text-slate-800 mb-3"><?= number_format($dashData['system']['total_users']) ?> <span class="text-xs font-normal text-slate-400">user</span></p>
                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="px-2 py-0.5 rounded-lg bg-primary-50 text-primary-700"><?= $dashData['system']['total_modules'] ?> Modul</span>
                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700"><?= $dashData['system']['total_roles'] ?> Peran</span>
                </div>
            </div>
            <a href="<?= url('users') ?>" class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-primary-600 hover:text-primary-700 flex items-center justify-between">
                <span>Kelola Pengguna & Peran</span>
                <span class="text-sm">→</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Search Filter (Mobile) -->
<div class="md:hidden mb-6">
    <div class="relative">
        <input type="text" 
               id="module-search-mobile"
               placeholder="Cari modul..."
               class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium"
               aria-label="Cari modul">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
        </svg>
    </div>
</div>

<!-- Section Header: Modul Tersedia -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-lg font-bold text-primary-900">Modul Tersedia</h3>
        <p class="text-sm text-slate-500"><?= count($modules) ?> modul yang dapat Anda akses</p>
    </div>
</div>

<?php
$ungroupedDashModules = [];
$groupedDashModules = [];

foreach ($modules as $module) {
    $group = $module['module_group'] ?? '';
    if (empty($group)) {
        $ungroupedDashModules[] = $module;
    } else {
        $groupedDashModules[$group][] = $module;
    }
}
?>

<!-- Ungrouped Modules -->
<?php if (!empty($ungroupedDashModules)): ?>
    <div class="mb-8">
        <div id="modules-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <?php foreach ($ungroupedDashModules as $index => $module): ?>
                <?php include TEMPLATES_PATH . '/components/module-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Grouped Modules -->
<?php foreach ($groupedDashModules as $groupName => $grpModules): ?>
    <div class="mb-8">
        <h4 class="text-md font-bold text-slate-700 mb-4 pb-2 border-b border-slate-200"><?= e($groupName) ?></h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <?php foreach ($grpModules as $index => $module): ?>
                <?php include TEMPLATES_PATH . '/components/module-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<!-- Empty State -->
<?php if (empty($modules)): ?>
<div class="text-center py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-50 mb-4">
        <svg class="w-8 h-8 text-primary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
        </svg>
    </div>
    <h4 class="text-lg font-semibold text-primary-900 mb-2">Belum ada modul</h4>
    <p class="text-sm text-slate-500">Hubungi administrator untuk mendapatkan akses ke modul.</p>
</div>
<?php endif; ?>
