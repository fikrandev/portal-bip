<?php
/**
 * Shared Partial: Matriks Hak Akses Granular (Sub-Fitur x Aksi CRUD)
 * Membutuhkan:
 * - $permissions: array dari DB (hasil query RoleController)
 * - $checkedIds: array ID permission yang aktif/tercentang
 */

$checkedIds = array_map('intval', $checkedIds ?? []);

// Label & Ikon Sub-Fitur agar mudah dibaca Admin
$featureMeta = [
    '__dashboard'      => ['label' => 'Dashboard & Statistik', 'icon' => '📊', 'desc' => 'Halaman utama / ringkasan statistik & widget dashboard'],
    '__main'           => ['label' => 'Manajemen Utama Modul', 'icon' => '📁', 'desc' => 'Akses data utama pada modul ini'],
    'data'             => ['label' => 'Data Utama & Profil', 'icon' => '📋', 'desc' => 'Daftar utama, profil, dan sinkronisasi'],
    'foto'             => ['label' => 'Galeri Foto & Kartu', 'icon' => '🖼️', 'desc' => 'Galeri foto, upload foto/ZIP, template & cetak kartu'],
    'buku_induk'       => ['label' => 'Buku Induk Siswa', 'icon' => '📖', 'desc' => 'Direktori buku induk resmi & ekspor'],
    'prestasi'         => ['label' => 'Prestasi & Penghargaan', 'icon' => '🏆', 'desc' => 'Pencatatan prestasi & rekam penghargaan'],
    'keluar'           => ['label' => 'Data Keluar & Mutasi', 'icon' => '🚪', 'desc' => 'Daftar keluar, mutasi pindah, reaktivasi & surat'],
    'penugasan'        => ['label' => 'Penugasan & Grup SK', 'icon' => '📄', 'desc' => 'Grup SK pembagian tugas mengajar & cetak SK'],
    'pelatihan'        => ['label' => 'Riwayat Pelatihan & Diklat', 'icon' => '📚', 'desc' => 'Catatan workshop, seminar, dan diklat'],
    'karir'            => ['label' => 'Riwayat Karir', 'icon' => '📈', 'desc' => 'Riwayat jabatan & kenaikan pangkat'],
    'siswa_baru'       => ['label' => 'Siswa Baru (SPMB)', 'icon' => '🎓', 'desc' => 'Pendaftar jalur siswa baru, berkas & kelulusan'],
    'pindahan'         => ['label' => 'Siswa Pindahan (SPMB)', 'icon' => '🔄', 'desc' => 'Pendaftar jalur mutasi masuk / pindahan'],
    'daftar_tunggu'    => ['label' => 'Daftar Tunggu (Waiting List)', 'icon' => '⏳', 'desc' => 'Antrean daftar tunggu & promosi pendaftar'],
    'surat_pernyataan' => ['label' => 'Surat Pernyataan Komitmen', 'icon' => '📜', 'desc' => 'Template komitmen orang tua, kop surat & reset TTD'],
    'psikolog'         => ['label' => 'Hasil Screening Psikolog', 'icon' => '🧠', 'desc' => 'Upload PDF psikotes, tinjau hasil & kirim WA'],
    'observasi'        => ['label' => 'Observasi Awal & Grup WA', 'icon' => '📋', 'desc' => 'Instrumen observasi awal ortu & undangan grup WA'],
    'pengaturan'       => ['label' => 'Pengaturan Modul', 'icon' => '⚙️', 'desc' => 'Periode group, kuota, biaya & konfigurasi modul'],
    'aset'             => ['label' => 'Data Aset (Tanah/Bangunan/Ruang)', 'icon' => '🏢', 'desc' => 'Manajemen tanah, gedung bangunan & ruangan'],
    'barang'           => ['label' => 'Inventaris Barang', 'icon' => '📦', 'desc' => 'Daftar barang inventaris & distribusi'],
    'label'            => ['label' => 'Cetak Label Barcode & QR', 'icon' => '🏷️', 'desc' => 'Pencetakan label barcode & QR kode barang'],
    'penyusutan'       => ['label' => 'Penyusutan Aset', 'icon' => '📉', 'desc' => 'Laporan kalkulasi penyusutan nilai aset'],
    'laporan'          => ['label' => 'Laporan & Rekap Aset', 'icon' => '📑', 'desc' => 'Rekapitulasi & ekspor laporan sarpras'],
    'peminjaman'       => ['label' => 'Sirkulasi Peminjaman', 'icon' => '🔁', 'desc' => 'Transaksi pinjam, kembali & persetujuan'],
    'pengajuan'        => ['label' => 'Pengajuan Pembelian / Cuti', 'icon' => '📝', 'desc' => 'Usulan pengadaan barang baru atau pengajuan cuti'],
    'perbaikan'        => ['label' => 'Pemeliharaan & Perbaikan', 'icon' => '🔧', 'desc' => 'Pengajuan perbaikan kerusakan & verifikasi'],
    'referensi'        => ['label' => 'Kategori & Referensi', 'icon' => '🗂️', 'desc' => 'Master kategori aset & referensi'],
    'mobile'           => ['label' => 'Akses Mobile App', 'icon' => '📱', 'desc' => 'Login & operasional melalui aplikasi mobile'],
    'jadwal'           => ['label' => 'Jadwal Pelajaran', 'icon' => '🗓️', 'desc' => 'Jadwal KBM, pengaturan JP & auto-generator'],
    'kaldik'           => ['label' => 'Kalender Pendidikan (Kaldik)', 'icon' => '📅', 'desc' => 'Dokumen kalender akademik & agenda sekolah'],
    'hari_efektif'     => ['label' => 'Hari Efektif (HES / HEB)', 'icon' => '📆', 'desc' => 'Rincian hari efektif sekolah & belajar'],
    'cpatp'            => ['label' => 'CP & ATP', 'icon' => '📚', 'desc' => 'Capaian pembelajaran & alur tujuan pembelajaran'],
    'prota'            => ['label' => 'Program Tahunan (Prota)', 'icon' => '📘', 'desc' => 'Dokumen & sinkronisasi program tahunan'],
    'prosem'           => ['label' => 'Program Semester (Prosem)', 'icon' => '📗', 'desc' => 'Dokumen & sinkronisasi program semester'],
    'rpp'              => ['label' => 'RPP / Modul Ajar', 'icon' => '✍️', 'desc' => 'Penyusunan modul ajar & pengajuan review'],
    'group'            => ['label' => 'Wadah / Group Periode', 'icon' => '🗃️', 'desc' => 'Pengaturan wadah/periode penilaian atau absensi'],
    'input'            => ['label' => 'Lembar Input Data', 'icon' => '⌨️', 'desc' => 'Pengisian nilai atau presensi siswa'],
    'mapel'            => ['label' => 'Absensi Per Mata Pelajaran', 'icon' => '📖', 'desc' => 'Presensi kehadiran siswa per jam mata pelajaran'],
    'kelas'            => ['label' => 'Absensi Harian Kelas', 'icon' => '🏫', 'desc' => 'Presensi harian siswa oleh wali kelas'],
    'rekap'            => ['label' => 'Rekapitulasi Data', 'icon' => '📑', 'desc' => 'Laporan rekapitulasi & cetak'],
    'paud_target'      => ['label' => "Qur'an PAUD: Target Materi", 'icon' => '🎯', 'desc' => 'Pengaturan target materi Tahsin/Tahfidz PAUD'],
    'paud_penilaian'   => ['label' => "Qur'an PAUD: Penilaian", 'icon' => '📝', 'desc' => 'Input penilaian capaian Qur\'an PAUD'],
    'paud_rekap'       => ['label' => "Qur'an PAUD: Rekap", 'icon' => '📊', 'desc' => 'Rekap & ekspor nilai Qur\'an PAUD'],
    'sd_smp'           => ['label' => "Qur'an SD / SMP & SMA", 'icon' => '🕌', 'desc' => 'Setoran ziyadah/muroja\'ah & rekap Qur\'an'],
    'master'           => ['label' => 'Master Data & Saldo', 'icon' => '🗃️', 'desc' => 'Pengelolaan data master referensi'],
    'identitas'        => ['label' => 'Identitas & Tahun Akademik', 'icon' => '🏫', 'desc' => 'Profil sekolah, logo & tahun akademik aktif'],
    'wa'               => ['label' => 'WA Gateway & Integrasi', 'icon' => '💬', 'desc' => 'Konfigurasi koneksi WhatsApp Gateway'],
    'logs'             => ['label' => 'Log Aktivitas Sistem', 'icon' => '🕵️', 'desc' => 'Audit trail & catatan aktivitas user'],
];

// Kelompokkan permissions per modul -> per sub-fitur -> per kolom aksi
$groupedModules = [];
foreach ($permissions as $p) {
    $modName = $p['module_name'] ?? 'Lainnya';
    if (!isset($groupedModules[$modName])) {
        $groupedModules[$modName] = [
            'icon_svg' => $p['icon_svg'] ?? '',
            'total'    => 0,
            'checked'  => 0,
            'features' => [],
        ];
    }

    $slug = $p['slug'];
    $parts = explode('.', $slug);
    $isDashboard = str_ends_with($slug, '.dashboard') || (strpos($slug, 'dashboard.') !== false);
    $isApprove = (strpos($slug, '.approve') !== false) || (strpos($slug, '.verify') !== false)
        || (strpos(strtolower($p['name']), 'verifikasi') !== false)
        || (strpos(strtolower($p['name']), 'persetujuan') !== false)
        || (strpos(strtolower($p['name']), 'pengesahan') !== false);

    $featKey = '__main';
    $colBucket = 'other'; // 'view' | 'create' | 'update' | 'delete' | 'other'

    if ($isDashboard) {
        $featKey = '__dashboard';
        $colBucket = 'view';
    } elseif (count($parts) >= 3) {
        $featKey = $parts[1];
        $act = $parts[2];
        if (in_array($act, ['view', 'rekap'], true)) {
            $colBucket = 'view';
        } elseif ($act === 'create') {
            $colBucket = 'create';
        } elseif (in_array($act, ['update', 'manage'], true)) {
            $colBucket = 'update';
        } elseif ($act === 'delete') {
            $colBucket = 'delete';
        } else {
            $colBucket = 'other';
        }
    } elseif (count($parts) === 2) {
        $act = $parts[1];
        if (in_array($act, ['view'], true)) {
            $featKey = '__main';
            $colBucket = 'view';
        } elseif ($act === 'create') {
            $featKey = '__main';
            $colBucket = 'create';
        } elseif (in_array($act, ['update', 'manage'], true)) {
            $featKey = '__main';
            $colBucket = 'update';
        } elseif ($act === 'delete') {
            $featKey = '__main';
            $colBucket = 'delete';
        } elseif ($act === 'rekap') {
            $featKey = 'rekap';
            $colBucket = 'view';
        } elseif (in_array($act, ['export', 'approve', 'verify', 'reset'], true)) {
            $featKey = '__main';
            $colBucket = 'other';
        } else {
            // misal sarpras.mobile, pegawai.penugasan, dll
            $featKey = $act;
            $colBucket = ($act === 'mobile') ? 'other' : 'view';
        }
    }

    if (!isset($groupedModules[$modName]['features'][$featKey])) {
        $meta = $featureMeta[$featKey] ?? [
            'label' => ucwords(str_replace('_', ' ', $featKey)),
            'icon'  => '📌',
            'desc'  => ''
        ];
        $groupedModules[$modName]['features'][$featKey] = [
            'label'  => $meta['label'],
            'icon'   => $meta['icon'],
            'desc'   => $meta['desc'],
            'view'   => [],
            'create' => [],
            'update' => [],
            'delete' => [],
            'other'  => [],
        ];
    }

    $p['_is_dashboard'] = $isDashboard;
    $p['_is_approve'] = $isApprove;
    $p['_is_checked'] = in_array((int)$p['id'], $checkedIds, true);

    $groupedModules[$modName]['features'][$featKey][$colBucket][] = $p;
    $groupedModules[$modName]['total']++;
    if ($p['_is_checked']) {
        $groupedModules[$modName]['checked']++;
    }
}
?>

<!-- Matriks Hak Akses & Fitur -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
        <div>
            <h2 class="text-base font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                Konfigurasi Hak Akses Granular Per Fitur & Aksi
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Centang sub-fitur modul serta izin aksinya (<span class="font-bold text-sky-700">Lihat</span>, <span class="font-bold text-emerald-700">Tambah</span>, <span class="font-bold text-amber-700">Ubah</span>, <span class="font-bold text-rose-700">Hapus</span>, dan <span class="font-bold text-purple-700">⭐ Verifikasi / Ekspor</span>).
            </p>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="toggleAllPermissions(true)" 
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                Pilih Semua
            </button>
            <button type="button" onclick="toggleAllPermissions(false)" 
                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Kosongkan
            </button>
            <button type="button" onclick="toggleOnlyDashboard(true)" 
                    class="px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-sm">
                <span>📊</span>
                Centang Semua Dashboard
            </button>
            <button type="button" onclick="toggleOnlyApprove(true)" 
                    class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-sm">
                <span>⭐</span>
                Centang Semua Approve
            </button>
        </div>
    </div>

    <!-- Quick Filter Input -->
    <div class="relative">
        <input type="text" id="permSearch" onkeyup="filterPermissions()" 
               placeholder="🔍 Cari modul atau sub-fitur (misal: siswa, galeri foto, spmb, tambah, hapus, dashboard, approve)..." 
               class="w-full px-4 py-3 pl-11 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
        <svg class="w-4 h-4 text-slate-400 absolute left-4 top-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
    </div>

    <!-- Permission Matrix Cards -->
    <div class="space-y-5" id="moduleContainer">
        <?php foreach ($groupedModules as $modName => $modData): ?>
        <div class="module-card border border-slate-200 rounded-3xl overflow-hidden hover:border-slate-300 transition-all bg-white shadow-xs" data-module-name="<?= strtolower(e($modName)) ?>">
            <!-- Card Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-5 py-4 bg-slate-50/70 border-b border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200/80 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-2xs">
                        <?= !empty($modData['icon_svg']) ? '<span class="w-4 h-4 [&>svg]:w-4 [&>svg]:h-4">' . $modData['icon_svg'] . '</span>' : '📁' ?>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 tracking-tight"><?= e($modName) ?></h3>
                        <p class="text-[11px] text-slate-500">
                            <span class="mod-selected-count font-bold text-primary-600"><?= $modData['checked'] ?></span> dari <?= $modData['total'] ?> hak akses aktif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 self-end sm:self-auto">
                    <button type="button" onclick="toggleModuleGroup(this, true)" 
                            class="px-3 py-1.5 rounded-xl text-[11px] font-bold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200/60 transition-colors">
                        Pilih Semua Modul
                    </button>
                    <button type="button" onclick="toggleModuleGroup(this, false)" 
                            class="px-3 py-1.5 rounded-xl text-[11px] font-semibold text-slate-600 hover:text-rose-700 bg-white hover:bg-rose-50 border border-slate-200 transition-colors">
                        Kosongkan
                    </button>
                </div>
            </div>

            <!-- Granular Sub-Feature Matrix Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/30 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-2.5 px-5 min-w-[210px]">Fitur / Sub-Menu</th>
                            <th class="py-2.5 px-3 text-center w-24">Lihat / Akses</th>
                            <th class="py-2.5 px-3 text-center w-24">Tambah</th>
                            <th class="py-2.5 px-3 text-center w-24">Ubah / Edit</th>
                            <th class="py-2.5 px-3 text-center w-24">Hapus</th>
                            <th class="py-2.5 px-4 min-w-[180px]">Lainnya / Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($modData['features'] as $featKey => $feat): 
                            $rowSearchParts = [$feat['label'], $feat['desc']];
                            $rowIsDashboard = false;
                            $rowIsApprove = false;
                            foreach (['view', 'create', 'update', 'delete', 'other'] as $b) {
                                foreach ($feat[$b] as $permItem) {
                                    $rowSearchParts[] = $permItem['name'] . ' ' . $permItem['slug'] . ' ' . ($permItem['description'] ?? '');
                                    if ($permItem['_is_dashboard']) $rowIsDashboard = true;
                                    if ($permItem['_is_approve']) $rowIsApprove = true;
                                }
                            }
                            $rowSearchText = strtolower(implode(' ', $rowSearchParts));
                        ?>
                        <tr class="perm-row hover:bg-slate-50/60 transition-colors"
                            data-row-search="<?= e($rowSearchText) ?>"
                            data-row-dashboard="<?= $rowIsDashboard ? '1' : '0' ?>"
                            data-row-approve="<?= $rowIsApprove ? '1' : '0' ?>">
                            <!-- Feature Name + Quick Row Check -->
                            <td class="py-3 px-5 align-middle">
                                <div class="flex items-start gap-2.5">
                                    <button type="button" onclick="toggleFeatureRow(this)" title="Centang / kosongkan seluruh aksi pada fitur ini"
                                            class="mt-0.5 w-6 h-6 rounded-lg bg-slate-100 hover:bg-primary-100 text-slate-600 hover:text-primary-700 flex items-center justify-center text-xs transition-colors shrink-0">
                                        <?= $feat['icon'] ?>
                                    </button>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-bold text-slate-800"><?= e($feat['label']) ?></span>
                                            <?php if ($rowIsDashboard): ?>
                                                <span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 text-[9px] font-black uppercase tracking-wider">Dashboard</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($feat['desc'])): ?>
                                            <p class="text-[10px] text-slate-400 leading-tight mt-0.5"><?= e($feat['desc']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <?php
                            $cols = [
                                'view'   => ['color' => 'text-sky-600 focus:ring-sky-500 border-sky-300', 'bg' => 'hover:bg-sky-50 has-[:checked]:bg-sky-50/80 has-[:checked]:border-sky-300'],
                                'create' => ['color' => 'text-emerald-600 focus:ring-emerald-500 border-emerald-300', 'bg' => 'hover:bg-emerald-50 has-[:checked]:bg-emerald-50/80 has-[:checked]:border-emerald-300'],
                                'update' => ['color' => 'text-amber-600 focus:ring-amber-500 border-amber-300', 'bg' => 'hover:bg-amber-50 has-[:checked]:bg-amber-50/80 has-[:checked]:border-amber-300'],
                                'delete' => ['color' => 'text-rose-600 focus:ring-rose-500 border-rose-300', 'bg' => 'hover:bg-rose-50 has-[:checked]:bg-rose-50/80 has-[:checked]:border-rose-300'],
                            ];
                            foreach ($cols as $colKey => $colStyle): ?>
                            <td class="py-3 px-2 text-center align-middle">
                                <?php if (empty($feat[$colKey])): ?>
                                    <span class="text-slate-300 text-xs select-none">—</span>
                                <?php else: ?>
                                    <div class="flex flex-col items-center gap-1">
                                    <?php foreach ($feat[$colKey] as $p): ?>
                                        <label class="perm-item inline-flex items-center justify-center p-1.5 rounded-xl border border-transparent <?= $colStyle['bg'] ?> cursor-pointer transition-all"
                                               data-perm-name="<?= strtolower(e($p['name'] . ' ' . $p['slug'] . ' ' . ($p['description'] ?? ''))) ?>"
                                               data-is-dashboard="<?= $p['_is_dashboard'] ? '1' : '0' ?>"
                                               data-is-approve="<?= $p['_is_approve'] ? '1' : '0' ?>"
                                               title="<?= e($p['name'] . ' (' . $p['slug'] . ') - ' . ($p['description'] ?? '')) ?>">
                                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>"
                                                   <?= $p['_is_checked'] ? 'checked' : '' ?>
                                                   onchange="updateCardCounter(this)"
                                                   class="perm-checkbox w-4 h-4 rounded-md <?= $colStyle['color'] ?> cursor-pointer">
                                        </label>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>

                            <!-- Lainnya / Verifikasi / Ekspor -->
                            <td class="py-3 px-4 align-middle">
                                <?php if (empty($feat['other'])): ?>
                                    <span class="text-slate-300 text-xs select-none">—</span>
                                <?php else: ?>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach ($feat['other'] as $p):
                                            if ($p['_is_approve']) {
                                                $pillClass = 'border-purple-200 bg-purple-50/50 hover:bg-purple-50 text-purple-800 has-[:checked]:bg-purple-100 has-[:checked]:border-purple-500';
                                                $cbClass = 'text-purple-600 border-purple-300';
                                                $shortLabel = '⭐ Verifikasi';
                                            } elseif (str_ends_with($p['slug'], '.export')) {
                                                $pillClass = 'border-slate-200 bg-slate-50/70 hover:bg-slate-100 text-slate-700 has-[:checked]:bg-sky-50 has-[:checked]:border-sky-400 has-[:checked]:text-sky-800';
                                                $cbClass = 'text-sky-600 border-slate-300';
                                                $shortLabel = '📤 Ekspor/Cetak';
                                            } elseif (str_ends_with($p['slug'], '.reset')) {
                                                $pillClass = 'border-rose-200 bg-rose-50/40 hover:bg-rose-50 text-rose-700 has-[:checked]:bg-rose-100 has-[:checked]:border-rose-500';
                                                $cbClass = 'text-rose-600 border-rose-300';
                                                $shortLabel = '⚠️ Reset Data';
                                            } else {
                                                $pillClass = 'border-slate-200 bg-slate-50/70 hover:bg-slate-100 text-slate-700 has-[:checked]:bg-primary-50 has-[:checked]:border-primary-400 has-[:checked]:text-primary-800';
                                                $cbClass = 'text-primary-600 border-slate-300';
                                                $shortLabel = e($p['name']);
                                            }
                                        ?>
                                        <label class="perm-item inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border <?= $pillClass ?> text-[11px] font-semibold cursor-pointer transition-all select-none"
                                               data-perm-name="<?= strtolower(e($p['name'] . ' ' . $p['slug'] . ' ' . ($p['description'] ?? ''))) ?>"
                                               data-is-dashboard="<?= $p['_is_dashboard'] ? '1' : '0' ?>"
                                               data-is-approve="<?= $p['_is_approve'] ? '1' : '0' ?>"
                                               title="<?= e($p['name'] . ' (' . $p['slug'] . ') - ' . ($p['description'] ?? '')) ?>">
                                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>"
                                                   <?= $p['_is_checked'] ? 'checked' : '' ?>
                                                   onchange="updateCardCounter(this)"
                                                   class="perm-checkbox w-3.5 h-3.5 rounded <?= $cbClass ?> cursor-pointer">
                                            <span><?= $shortLabel ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function refreshCardCounter(card) {
    if (!card) return;
    const checked = card.querySelectorAll('.perm-checkbox:checked').length;
    const counterSpan = card.querySelector('.mod-selected-count');
    if (counterSpan) counterSpan.textContent = checked;
}

function updateCardCounter(checkbox) {
    refreshCardCounter(checkbox.closest('.module-card'));
}

function toggleFeatureRow(btn) {
    const row = btn.closest('tr.perm-row');
    if (!row) return;
    const cbs = Array.from(row.querySelectorAll('.perm-checkbox'));
    if (cbs.length === 0) return;
    const allChecked = cbs.every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
    refreshCardCounter(row.closest('.module-card'));
}

function toggleModuleGroup(button, state) {
    const card = button.closest('.module-card');
    if (!card) return;
    card.querySelectorAll('tr.perm-row').forEach(row => {
        if (row.style.display !== 'none') {
            row.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = state);
        }
    });
    refreshCardCounter(card);
}

function toggleAllPermissions(state) {
    document.querySelectorAll('tr.perm-row').forEach(row => {
        if (row.style.display !== 'none') {
            row.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = state);
        }
    });
    document.querySelectorAll('.module-card').forEach(refreshCardCounter);
}

function toggleOnlyDashboard(state) {
    document.querySelectorAll('.perm-item[data-is-dashboard="1"] .perm-checkbox').forEach(cb => cb.checked = state);
    document.querySelectorAll('.module-card').forEach(refreshCardCounter);
}

function toggleOnlyApprove(state) {
    document.querySelectorAll('.perm-item[data-is-approve="1"] .perm-checkbox').forEach(cb => cb.checked = state);
    document.querySelectorAll('.module-card').forEach(refreshCardCounter);
}

function filterPermissions() {
    const query = (document.getElementById('permSearch').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.module-card');

    cards.forEach(card => {
        const modName = card.getAttribute('data-module-name') || '';
        const rows = card.querySelectorAll('tr.perm-row');

        if (!query || modName.includes(query)) {
            rows.forEach(r => r.style.display = '');
            card.style.display = '';
            return;
        }

        let hasVisibleRow = false;
        rows.forEach(row => {
            const text = row.getAttribute('data-row-search') || '';
            const isDash = row.getAttribute('data-row-dashboard') === '1';
            const isApp = row.getAttribute('data-row-approve') === '1';
            const matched = (query === 'dashboard' && isDash) || (query === 'approve' && isApp) || text.includes(query);
            row.style.display = matched ? '' : 'none';
            if (matched) hasVisibleRow = true;
        });

        card.style.display = hasVisibleRow ? '' : 'none';
    });
}
</script>
