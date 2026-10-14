<?php
/**
 * View Pengaturan Tahun Group SPMB
 * Portal BIP
 */
$groups = $groups ?? [];
$activeGroup = $activeGroup ?? null;
$tahunAkademikList = $tahunAkademikList ?? [];
$spmbOptions = $spmbOptions ?? SpmbModel::getSpmbOptions();
$rawAlergi = SpmbModel::getSetting('spmb_opt_alergi') ?: implode("\n", $spmbOptions['alergi'] ?? []);
$rawTempatTinggal = SpmbModel::getSetting('spmb_opt_tempat_tinggal') ?: implode("\n", $spmbOptions['tempat_tinggal'] ?? []);
$rawTransportasi = SpmbModel::getSetting('spmb_opt_transportasi') ?: implode("\n", $spmbOptions['transportasi'] ?? []);
$rawPendidikan = SpmbModel::getSetting('spmb_opt_pendidikan') ?: implode("\n", $spmbOptions['pendidikan'] ?? []);
$rawPekerjaan = SpmbModel::getSetting('spmb_opt_pekerjaan') ?: implode("\n", $spmbOptions['pekerjaan'] ?? []);
$rawPenghasilan = SpmbModel::getSetting('spmb_opt_penghasilan') ?: implode("\n", $spmbOptions['penghasilan'] ?? []);
$rawKebutuhanKhusus = SpmbModel::getSetting('spmb_opt_kebutuhan_khusus') ?: implode("\n", $spmbOptions['kebutuhan_khusus'] ?? []);
$rawBatasPengisian = SpmbModel::getSetting('spmb_batas_pengisian_biodata') ?: date('Y-12-31 23:59:59');
$valDatetimeLocal = date('Y-m-d\TH:i', strtotime($rawBatasPengisian));
$waNotifEnabled = ($spmbOptions['wa_notif_enabled'] ?? true);
$waTemplate = SpmbModel::getSetting('spmb_wa_template_biodata') ?: ($spmbOptions['wa_template_biodata'] ?? '');
$templateDapodik = $spmbOptions['template_dapodik'] ?? '';
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:text-sky-600">SPMB</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Pengaturan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Pengaturan SPMB
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola group tahun pendaftaran, gelombang, kuota jenjang, dan biaya formulir. Pendaftar baru otomatis terhubung ke Group yang berstatus <strong>AKTIF</strong>.
            </p>
        </div>
        <div>
            <button onclick="openModalTambahGroup()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-semibold text-xs shadow-md shadow-sky-600/20 transition-all transform hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Buat Group SPMB Baru</span>
            </button>
        </div>
    </div>

    <!-- Active Group Spotlight Card -->
    <?php if ($activeGroup): ?>
        <div class="p-6 rounded-3xl bg-gradient-to-br from-primary-950 via-primary-900 to-sky-950 text-white shadow-xl border border-primary-800/80 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>GROUP SPMB UTAMA (AKTIF)</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        <?= e($activeGroup['nama_group']) ?>
                    </h2>
                    <p class="text-primary-200 text-xs sm:text-sm mt-1 max-w-xl">
                        <?= e($activeGroup['keterangan'] ?: 'Group resmi untuk penerimaan peserta didik baru SIT Bina Insan Palu tahun ajaran ' . $activeGroup['tahun_ajaran']) ?>
                    </p>

                    <div class="flex flex-wrap items-center gap-3 mt-4 text-xs text-primary-300">
                        <span class="bg-primary-900/80 px-3 py-1.5 rounded-xl border border-primary-700/60">
                            Tahun Ajaran: <strong class="text-white"><?= e($activeGroup['tahun_ajaran']) ?></strong>
                        </span>
                        <span class="bg-primary-900/80 px-3 py-1.5 rounded-xl border border-primary-700/60">
                            Gelombang Default: <strong class="text-white"><?= e($activeGroup['gelombang_default']) ?></strong>
                        </span>
                        <span class="bg-primary-900/80 px-3 py-1.5 rounded-xl border border-primary-700/60">
                            Biaya Siswa Baru: <strong class="text-white">Rp <?= number_format($activeGroup['biaya_formulir'], 0, ',', '.') ?></strong>
                        </span>
                        <span class="bg-amber-950/80 px-3 py-1.5 rounded-xl border border-amber-600/50 text-amber-300">
                            Biaya Pindahan: <strong class="text-white">Rp <?= number_format($activeGroup['biaya_pindahan'] ?? 0, 0, ',', '.') ?></strong>
                        </span>
                        <span class="bg-emerald-950/80 px-3 py-1.5 rounded-xl border border-emerald-500/40 text-emerald-300">
                            Patokan Hitung Umur: <strong class="text-white">Per <?= !empty($activeGroup['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($activeGroup['tanggal_patokan_umur']) : '1 Juli 2027' ?></strong>
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <button onclick="openModalEditGroup(<?= htmlspecialchars(json_encode($activeGroup), ENT_QUOTES, 'UTF-8') ?>)" 
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur-sm transition-colors text-center">
                        Edit Group Ini
                    </button>
                    <a href="<?= url('kelola-spmb/siswa-baru/create') ?>" 
                       class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all text-center">
                        Input Siswa ke Group Ini
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ── Card Pengaturan Kop Surat (Gambar Kop Banner) ── -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-xl shrink-0">
                    🖼️
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Kop Surat Gambar SPMB</h3>
                    <p class="text-xs text-slate-500">
                        Unggah gambar banner kop resmi yayasan/sekolah untuk dicetak pada Kwitansi Pembayaran, Formulir Biodata, dan dokumen SPMB lainnya.
                    </p>
                </div>
            </div>
            <?php if (!empty($kopGambar)): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Kop Gambar Aktif
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 shrink-0">
                    Kop Teks Standar
                </span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Preview -->
            <div class="lg:col-span-7 space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Preview Kop Surat Dokumen Cetak
                </label>
                
                <?php if (!empty($kopGambar)): ?>
                    <div class="p-4 bg-slate-50 rounded-2xl border-2 border-slate-200 text-center relative group">
                        <img src="<?= url(ltrim($kopGambar, '/')) ?>" 
                             alt="Kop Surat SPMB" 
                             class="w-full max-h-40 object-contain mx-auto rounded-lg shadow-xs bg-white p-2">
                        <div class="mt-3 flex items-center justify-center gap-3">
                            <a href="<?= url(ltrim($kopGambar, '/')) ?>" target="_blank" class="text-xs text-sky-600 hover:text-sky-700 font-bold underline">
                                🔍 Lihat Ukuran Asli
                            </a>
                            <span class="text-slate-300">&bull;</span>
                            <form action="<?= url('kelola-spmb/pengaturan/hapus-kop') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus gambar kop ini dan kembali ke kop teks standar?');" class="inline">
                                <?= CSRF::field() ?>
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-bold underline">
                                    🗑️ Hapus / Reset ke Kop Standar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-6 bg-slate-50/80 rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-2">
                        <div class="text-3xl text-slate-300">📄</div>
                        <p class="text-xs font-bold text-slate-700">Belum Ada Kop Gambar yang Diunggah</p>
                        <p class="text-[11px] text-slate-500 max-w-md mx-auto">
                            Saat ini dokumen cetak (Kwitansi & Formulir) menggunakan susunan kop teks standar. Unggah gambar banner kop resmi yayasan Anda di samping untuk menerapkan kop gambar secara otomatis.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Kolom Form Upload -->
            <div class="lg:col-span-5 bg-slate-50/70 p-5 rounded-2xl border border-slate-200/80 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    <?= !empty($kopGambar) ? 'Ganti Kop Gambar' : 'Upload Kop Gambar Baru' ?>
                </h4>

                <form action="<?= url('kelola-spmb/pengaturan/upload-kop') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= CSRF::field() ?>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pilih File Gambar Banner Kop Surat
                        </label>
                        <input type="file" 
                               name="kop_gambar" 
                               id="inputKopGambar"
                               accept="image/png, image/jpeg, image/jpg, image/webp"
                               required
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer border border-slate-200 bg-white rounded-xl p-1.5">
                    </div>

                    <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200 text-[11px] text-amber-800 space-y-1">
                        <div class="font-bold flex items-center gap-1 text-amber-900">
                            <span>💡</span> Rekomendasi Format Kop:
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[10.5px] text-amber-700">
                            <li>Format: <strong>PNG</strong> (latar transparan) atau <strong>JPG/WEBP</strong>.</li>
                            <li>Bentuk: <strong>Horizontal / Memanjang</strong> (Banner Kop Lengkap).</li>
                            <li>Lebar resolusi: <strong>800 px - 1200 px</strong> (rasio ~ 5:1 atau 6:1).</li>
                            <li>Maksimal ukuran file: <strong>4 MB</strong>.</li>
                        </ul>
                    </div>

                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition-all text-center">
                        💾 Unggah & Terapkan Kop Gambar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table of All SPMB Groups -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Semua Group SPMB</h3>
                <p class="text-xs text-slate-500">Daftar periode pendaftaran murid baru yang tercatat di sistem</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                Total: <?= count($groups) ?> Group
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Group SPMB</th>
                        <th class="py-3.5 px-4">Tahun Ajaran</th>
                        <th class="py-3.5 px-4">Gelombang & Biaya</th>
                        <th class="py-3.5 px-4">Patokan Umur</th>
                        <th class="py-3.5 px-4">Kuota (TK / SD / SMP / SMA)</th>
                        <th class="py-3.5 px-4">Total Pendaftar</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                Belum ada group SPMB. Silakan klik tombol "Buat Group SPMB Baru".
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($groups as $g): 
                            $isActive = ($g['is_active'] == 1);
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors <?= $isActive ? 'bg-sky-50/20' : '' ?>">
                                <td class="py-3.5 px-4 text-center font-medium text-slate-400"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-sm"><?= e($g['nama_group']) ?></div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs"><?= e($g['keterangan'] ?: '-') ?></div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <?= e($g['tahun_ajaran']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-700"><?= e($g['gelombang_default']) ?></div>
                                    <div class="text-slate-500 font-mono text-[11px]">Siswa Baru: Rp <?= number_format($g['biaya_formulir'], 0, ',', '.') ?></div>
                                    <div class="text-amber-700 font-mono text-[11px] font-bold">Pindahan: Rp <?= number_format($g['biaya_pindahan'] ?? 0, 0, ',', '.') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-emerald-800">Per <?= !empty($g['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($g['tanggal_patokan_umur']) : '1 Juli 2027' ?></div>
                                    <div class="text-[10px] text-slate-400 font-mono"><?= e($g['tanggal_patokan_umur'] ?? '2027-07-01') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px]" title="PAUD"><?= $g['kuota_paud'] ?></span>
                                        <span class="text-slate-300">/</span>
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px]" title="SD"><?= $g['kuota_sd'] ?></span>
                                        <span class="text-slate-300">/</span>
                                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px]" title="SMP"><?= $g['kuota_smp'] ?></span>
                                        <span class="text-slate-300">/</span>
                                        <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 text-[10px]" title="SMA"><?= $g['kuota_sma'] ?></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">PAUD / SD / SMP / SMA</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= (int)($g['total_pendaftar'] ?? 0) ?> siswa</div>
                                    <div class="text-[10px] text-slate-400">
                                        <?= (int)($g['total_siswa_baru'] ?? 0) ?> Baru &bull; <?= (int)($g['total_pindahan'] ?? 0) ?> Pindahan
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            AKTIF
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                            Non-aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <?php if (!$isActive): ?>
                                            <a href="<?= url('kelola-spmb/pengaturan/set-active/' . $g['id']) ?>" 
                                               title="Jadikan Group Aktif"
                                               class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition-colors flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                <span>Aktifkan</span>
                                            </a>
                                        <?php endif; ?>

                                        <button onclick="openModalEditGroup(<?= htmlspecialchars(json_encode($g), ENT_QUOTES, 'UTF-8') ?>)" 
                                                title="Edit Group"
                                                class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </button>

                                        <?php if (!$isActive && ($g['total_pendaftar'] ?? 0) == 0): ?>
                                            <button onclick="confirmDeleteGroup(<?= $g['id'] ?>, '<?= e(addslashes($g['nama_group'])) ?>')" 
                                                    title="Hapus Group"
                                                    class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
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

    <!-- ── Card Pengaturan Opsi Biodata Lengkap & Master Dropdown ── -->
    <div id="sectionBiodata" class="bg-white rounded-2xl border border-primary-100/70 shadow-sm p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                    📋
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Pengaturan Formulir Biodata Lengkap Calon Siswa</h3>
                    <p class="text-xs text-slate-500">
                        Kelola daftar pilihan dropdown (Alergi, Tempat Tinggal, Transportasi, Pekerjaan, dll) serta notifikasi otomatis WhatsApp.
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Formulir Aktif
            </span>
        </div>

        <form action="<?= url('kelola-spmb/pengaturan/save-options') ?>" method="POST" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- ── Batas Waktu Pengisian Formulir Online (Countdown Timer Bar) ── -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-blue-50/80 via-sky-50/60 to-indigo-50/40 border border-blue-200/90 shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-600 to-blue-700 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-sky-600/20">
                            ⏱️
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                <span>Batas Waktu Pengisian Formulir Online (Countdown Timer Melayang)</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-100 text-sky-800 border border-sky-200">Fixed Header</span>
                            </h4>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Batas akhir bagi orang tua calon siswa untuk melengkapi formulir biodata online. Ditampilkan sebagai <strong>countdown timer fixed</strong> di bagian paling atas halaman formulir.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center pt-2 border-t border-blue-100">
                    <div class="sm:col-span-6 lg:col-span-5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal & Jam Deadline Pengisian:
                        </label>
                        <div class="relative">
                            <input type="datetime-local" 
                                   name="spmb_batas_pengisian_biodata" 
                                   id="inputBatasPengisian"
                                   value="<?= htmlspecialchars($valDatetimeLocal) ?>" 
                                   required
                                   class="w-full text-xs font-mono font-bold text-slate-800 p-2.5 rounded-xl border border-blue-300 bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none shadow-xs">
                        </div>
                    </div>
                    <div class="sm:col-span-6 lg:col-span-7 bg-white/80 p-3 rounded-xl border border-blue-200 text-xs text-slate-600 space-y-1">
                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                            <span>💡 Tampilan di Halaman Formulir:</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Waktu deadline ini akan dihitung mundur secara real-time: 
                            <strong class="text-sky-700 font-mono">[ Hari ] : [ Jam ] : [ Menit ] : [ Detik ]</strong> 
                            di atas form pengisian biodata publik.
                        </p>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Gateway Notification Toggle & Template -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-50/70 to-teal-50/50 border border-emerald-200/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                            📲
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Notifikasi Otomatis WhatsApp Link Biodata</h4>
                            <p class="text-xs text-slate-600">
                                Kirim pesan WA otomatis berisi link formulir biodata lengkap ke nomor pendaftar saat siswa baru / pindahan didaftarkan.
                            </p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="spmb_wa_notif_enabled" value="1" <?= $waNotifEnabled ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        <span class="ml-2.5 text-xs font-bold text-slate-800">Kirim Otomatis</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Template Pesan WhatsApp Link Pengisian Biodata
                    </label>
                    <textarea name="spmb_wa_template_biodata" rows="5" class="w-full text-xs font-mono p-3 rounded-xl border border-emerald-200 bg-white focus:border-emerald-500 outline-none leading-relaxed"><?= e($waTemplate) ?></textarea>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                        <span>💡</span>
                        Placeholder yang tersedia: <code class="bg-emerald-100 text-emerald-800 px-1 py-0.5 rounded font-bold">{nama_siswa}</code>, <code class="bg-emerald-100 text-emerald-800 px-1 py-0.5 rounded font-bold">{no_daftar}</code>, <code class="bg-emerald-100 text-emerald-800 px-1 py-0.5 rounded font-bold">{link_biodata}</code>
                    </p>
                </div>
            </div>

            <!-- Master Options Grid -->
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">
                    Master Pilihan Dropdown Formulir (Isi 1 Pilihan Per Baris)
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <!-- 1. Alergi -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-800">
                            1. Pilihan Riwayat Alergi
                        </label>
                        <p class="text-[11px] text-slate-500">Pilihan untuk tab Data Calon Siswa</p>
                        <textarea name="spmb_opt_alergi" rows="4" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:border-emerald-500 outline-none"><?= e($rawAlergi) ?></textarea>
                    </div>

                    <!-- 2. Tempat Tinggal -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-800">
                            2. Pilihan Status Tempat Tinggal
                        </label>
                        <p class="text-[11px] text-slate-500">Pilihan untuk tab Tempat Tinggal</p>
                        <textarea name="spmb_opt_tempat_tinggal" rows="4" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:border-emerald-500 outline-none"><?= e($rawTempatTinggal) ?></textarea>
                    </div>

                    <!-- 3. Moda Transportasi -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-800">
                            3. Pilihan Moda Transportasi
                        </label>
                        <p class="text-[11px] text-slate-500">Pilihan untuk tab Tempat Tinggal</p>
                        <textarea name="spmb_opt_transportasi" rows="4" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:border-emerald-500 outline-none"><?= e($rawTransportasi) ?></textarea>
                    </div>

                    <!-- 4. Pendidikan Orang Tua -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-800">
                            4. Pilihan Pendidikan Orang Tua
                        </label>
                        <p class="text-[11px] text-slate-500">Pilihan untuk Ayah dan Ibu</p>
                        <textarea name="spmb_opt_pendidikan" rows="4" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:border-emerald-500 outline-none"><?= e($rawPendidikan) ?></textarea>
                    </div>

                    <!-- 5. Pekerjaan Orang Tua -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-800">
                            5. Pilihan Pekerjaan Orang Tua
                        </label>
                        <p class="text-[11px] text-slate-500">Pilihan untuk Ayah dan Ibu</p>
                        <textarea name="spmb_opt_pekerjaan" rows="4" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:border-emerald-500 outline-none"><?= e($rawPekerjaan) ?></textarea>
                    </div>

                    <!-- 6. Penghasilan Bulanan -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-800">
                            6. Pilihan Rentang Penghasilan
                        </label>
                        <p class="text-[11px] text-slate-500">Pilihan untuk Ayah dan Ibu</p>
                        <textarea name="spmb_opt_penghasilan" rows="4" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:border-emerald-500 outline-none"><?= e($rawPenghasilan) ?></textarea>
                    </div>

                    <!-- 7. Berkebutuhan Khusus -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-1.5 md:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-bold text-slate-800">
                            7. Pilihan Berkebutuhan Khusus
                        </label>
                        <p class="text-[11px] text-slate-500">Pilihan untuk kondisi khusus Ayah dan Ibu</p>
                        <textarea name="spmb_opt_kebutuhan_khusus" rows="2" class="w-full text-xs p-2.5 rounded-lg border border-slate-200 bg-white focus:border-emerald-500 outline-none"><?= e($rawKebutuhanKhusus) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <span>💾</span> Simpan Seluruh Opsi Formulir & Template WA
                </button>
            </div>
        </form>
    </div>

    <!-- ── Card Pengaturan Contoh Screenshot Profil Dapodik ── -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl shrink-0">
                    📸
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">File Contoh Screenshot Profil Dapodik</h3>
                    <p class="text-xs text-slate-500">
                        Unggah contoh tampilan screenshot Dapodik agar orang tua pendaftar tahu format dan bagian dokumen yang harus di-screenshot.
                    </p>
                </div>
            </div>
            <?php if (!empty($templateDapodik)): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    File Contoh Terpasang
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 shrink-0">
                    Contoh Otomatis Sistem
                </span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-7 space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Preview Contoh Screenshot Dapodik
                </label>
                <?php if (!empty($templateDapodik)): ?>
                    <div class="p-4 bg-slate-50 rounded-2xl border-2 border-slate-200 text-center relative">
                        <img src="<?= asset($templateDapodik) ?>" alt="Contoh Dapodik" class="w-full max-h-48 object-contain mx-auto rounded-lg shadow-xs bg-white p-2">
                        <div class="mt-3 flex items-center justify-center gap-3">
                            <a href="<?= asset($templateDapodik) ?>" target="_blank" class="text-xs text-sky-600 hover:text-sky-700 font-bold underline">
                                🔍 Lihat Ukuran Asli
                            </a>
                            <span class="text-slate-300">&bull;</span>
                            <a href="<?= url('spmb/biodata/download-contoh-dapodik') ?>" target="_blank" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold underline">
                                📥 Coba Unduh via Link Publik
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-6 bg-slate-50/80 rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-2">
                        <div class="text-3xl text-slate-300">🖼️</div>
                        <p class="text-xs font-bold text-slate-700">Belum Ada File Contoh Dapodik yang Diunggah</p>
                        <p class="text-[11px] text-slate-500 max-w-md mx-auto">
                            Sistem saat ini menampilkan banner petunjuk standar jika orang tua mengunduh contoh screenshot. Anda dapat mengunggah file contoh asli di samping.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-5 bg-slate-50/70 p-5 rounded-2xl border border-slate-200/80 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Unggah File Contoh Screenshot Baru
                </h4>
                <form action="<?= url('kelola-spmb/pengaturan/upload-contoh-dapodik') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= CSRF::field() ?>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pilih Gambar Contoh Screenshot
                        </label>
                        <input type="file" name="contoh_dapodik" accept="image/png, image/jpeg, image/jpg, image/webp" required class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 bg-white rounded-xl p-1.5">
                    </div>
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition-all text-center">
                        💾 Unggah File Contoh Dapodik
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH GROUP -->
<div id="modalTambahGroup" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Buat Group SPMB Baru</h3>
                <p class="text-xs text-slate-500">Tambahkan periode seleksi penerimaan murid baru baru</p>
            </div>
            <button onclick="closeModalTambahGroup()" class="p-1 rounded-full text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form action="<?= url('kelola-spmb/pengaturan/store') ?>" method="POST" class="space-y-4">
            <?= CSRF::field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Group SPMB <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_group" required placeholder="Contoh: SPMB 2026/2027 atau SPMB 2027/2028..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="tahun_ajaran" required placeholder="Contoh: 2026/2027" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gelombang Default</label>
                    <input type="text" name="gelombang_default" value="Gelombang 1" placeholder="Contoh: Gelombang 1" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Formulir Siswa Baru (Rp)</label>
                    <input type="number" name="biaya_formulir" value="250000" min="0" step="10000" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Pendaftaran Pindahan (Rp)</label>
                    <input type="number" name="biaya_pindahan" value="250000" min="0" step="10000" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Buka Pendaftaran</label>
                    <input type="date" name="tanggal_buka" value="<?= date('Y-01-01') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Tutup Pendaftaran</label>
                    <input type="date" name="tanggal_tutup" value="<?= date('Y-07-31') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Patokan Hitung Umur <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_patokan_umur" value="2027-07-01" required 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-semibold text-slate-800">
                    <p class="text-[10px] text-slate-400 mt-0.5">Standar SPMB: per 1 Juli tahun ajaran (misal 2027-07-01)</p>
                </div>

                <!-- Kuota per jenjang -->
                <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-800 mb-2">Kuota Pendaftaran per Jenjang:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">PAUD</label>
                            <input type="number" name="kuota_paud" value="50" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">SD</label>
                            <input type="number" name="kuota_sd" value="100" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">SMP</label>
                            <input type="number" name="kuota_smp" value="100" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">SMA</label>
                            <input type="number" name="kuota_sma" value="100" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Catatan Group</label>
                    <textarea name="keterangan" rows="2" placeholder="Catatan informasi periode ini..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none"></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                        <input type="checkbox" name="is_active" value="1" class="w-4 h-4 text-emerald-600 rounded">
                        <span class="text-xs font-bold text-emerald-900">
                            Jadikan group ini langsung AKTIF (Group aktif saat ini akan menjadi non-aktif otomatis)
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModalTambahGroup()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition-all">
                    Simpan Group Baru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT GROUP -->
<div id="modalEditGroup" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Edit Group SPMB</h3>
                <p class="text-xs text-slate-500">Perbarui rincian group pendaftaran</p>
            </div>
            <button onclick="closeModalEditGroup()" class="p-1 rounded-full text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form id="formEditGroup" action="" method="POST" class="space-y-4">
            <?= CSRF::field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Group SPMB <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_group" id="edit_nama_group" required 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="tahun_ajaran" id="edit_tahun_ajaran" required 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gelombang Default</label>
                    <input type="text" name="gelombang_default" id="edit_gelombang_default" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Formulir Siswa Baru (Rp)</label>
                    <input type="number" name="biaya_formulir" id="edit_biaya_formulir" min="0" step="10000" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Pendaftaran Pindahan (Rp)</label>
                    <input type="number" name="biaya_pindahan" id="edit_biaya_pindahan" min="0" step="10000" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Buka</label>
                    <input type="date" name="tanggal_buka" id="edit_tanggal_buka" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Tutup</label>
                    <input type="date" name="tanggal_tutup" id="edit_tanggal_tutup" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Patokan Hitung Umur <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_patokan_umur" id="edit_tanggal_patokan_umur" required 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-semibold text-slate-800">
                    <p class="text-[10px] text-slate-400 mt-0.5">Standar SPMB: per 1 Juli tahun ajaran (dapat diubah per group/tahun)</p>
                </div>

                <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-800 mb-2">Kuota Pendaftaran per Jenjang:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">PAUD</label>
                            <input type="number" name="kuota_paud" id="edit_kuota_paud" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">SD</label>
                            <input type="number" name="kuota_sd" id="edit_kuota_sd" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">SMP</label>
                            <input type="number" name="kuota_smp" id="edit_kuota_smp" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-600 mb-1">SMA</label>
                            <input type="number" name="kuota_sma" id="edit_kuota_sma" min="0" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 outline-none font-bold">
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none"></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 text-emerald-600 rounded">
                        <span class="text-xs font-bold text-emerald-900">
                            Jadikan sebagai Group SPMB AKTIF
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModalEditGroup()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTambahGroup() {
    document.getElementById('modalTambahGroup').classList.remove('hidden');
}
function closeModalTambahGroup() {
    document.getElementById('modalTambahGroup').classList.add('hidden');
}

function openModalEditGroup(g) {
    document.getElementById('formEditGroup').action = '<?= url('kelola-spmb/pengaturan/update/') ?>' + g.id;
    document.getElementById('edit_nama_group').value = g.nama_group || '';
    document.getElementById('edit_tahun_ajaran').value = g.tahun_ajaran || '';
    document.getElementById('edit_gelombang_default').value = g.gelombang_default || 'Gelombang 1';
    document.getElementById('edit_biaya_formulir').value = g.biaya_formulir || 0;
    document.getElementById('edit_biaya_pindahan').value = g.biaya_pindahan || 0;
    document.getElementById('edit_tanggal_buka').value = g.tanggal_buka || '';
    document.getElementById('edit_tanggal_tutup').value = g.tanggal_tutup || '';
    document.getElementById('edit_tanggal_patokan_umur').value = g.tanggal_patokan_umur || '2027-07-01';
    document.getElementById('edit_kuota_paud').value = g.kuota_paud || 0;
    document.getElementById('edit_kuota_sd').value = g.kuota_sd || 0;
    document.getElementById('edit_kuota_smp').value = g.kuota_smp || 0;
    document.getElementById('edit_kuota_sma').value = g.kuota_sma || 0;
    document.getElementById('edit_keterangan').value = g.keterangan || '';
    document.getElementById('edit_is_active').checked = (g.is_active == 1);

    document.getElementById('modalEditGroup').classList.remove('hidden');
}
function closeModalEditGroup() {
    document.getElementById('modalEditGroup').classList.add('hidden');
}

function confirmDeleteGroup(id, name) {
    Swal.fire({
        title: 'Hapus Group SPMB?',
        text: 'Apakah Anda yakin ingin menghapus group "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('kelola-spmb/pengaturan/delete/') ?>' + id;
        }
    });
}
</script>
