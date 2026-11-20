<?php
/**
 * Pengajuan Cuti Pegawai View (Mobile)
 * Fitur:
 * - Ringkasan sisa kuota cuti tahunan, sakit, dan penting
 * - Riwayat permohonan cuti lengkap dengan verifikasi approval
 * - Modal pengajuan cuti modern bottom-sheet:
 *   1. Pemilihan tanggal dalam bentuk tombol-tombol per bulan (interaktif, multi-pilih, shortcut rentang)
 *   2. Dropdown pegawai pengganti otomatis disaring sesuai unit tugas dan jenis pegawai
 *   3. Field keterangan tugas yang dilimpahkan
 *   4. Lampiran bukti maksimal 2 foto dengan auto-convert & kompresi <= 500 KB
 */
?>

<!-- Top App Bar -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs max-w-full overflow-hidden">
    <div class="flex items-center gap-2.5 min-w-0 flex-1">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce shrink-0">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div class="min-w-0 flex-1">
            <h2 class="font-black text-slate-900 text-base leading-tight truncate">Pengajuan Cuti</h2>
            <p class="text-[10px] text-slate-400 font-medium truncate">
                <?= e($userUnit ?? 'Semua Unit') ?> &bull; <?= e($userJenis ?? 'Pegawai') ?>
            </p>
        </div>
    </div>

    <!-- Right Action Button -->
    <button type="button" onclick="openModalPermohonanCuti()" class="w-9 h-9 rounded-2xl bg-primary-50 text-primary-600 border border-primary-100 flex items-center justify-center press-bounce shadow-xs shrink-0 ml-2" title="Ajukan Cuti Baru">
        <i data-lucide="plus" class="w-5 h-5"></i>
    </button>
</div>

<div class="p-3.5 sm:p-4 space-y-3.5 sm:space-y-4 max-w-full overflow-x-hidden">

    <!-- 1. Leave Quota Summary Cards (3 Columns) -->
    <div class="space-y-1.5">
        <div class="flex items-center justify-between px-1 text-xs">
            <h3 class="font-bold text-slate-800 text-xs">Sisa Kuota Cuti Anda</h3>
            <span class="text-[10px] text-slate-400 font-semibold">Tahun <?= date('Y') ?></span>
        </div>

        <div class="grid grid-cols-3 gap-2 sm:gap-2.5">
            <!-- Quota 1: Cuti Tahunan -->
            <div class="bg-white rounded-3xl p-3 sm:p-3.5 border border-slate-100 shadow-sm text-center space-y-1 min-w-0">
                <span class="text-xl font-black text-primary-600 block leading-none truncate">
                    <?= $quotas['tahunan']['remaining'] ?? 10 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['tahunan']['total'] ?? 12 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block truncate">Tahunan</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-primary-500 h-1 rounded-full" style="width: <?= round((($quotas['tahunan']['remaining'] ?? 10) / max(1, ($quotas['tahunan']['total'] ?? 12))) * 100) ?>%"></div>
                </div>
            </div>

            <!-- Quota 2: Cuti Sakit -->
            <div class="bg-white rounded-3xl p-3 sm:p-3.5 border border-slate-100 shadow-sm text-center space-y-1 min-w-0">
                <span class="text-xl font-black text-primary-600 block leading-none truncate">
                    <?= $quotas['sakit']['remaining'] ?? 14 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['sakit']['total'] ?? 14 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block truncate">Sakit</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-primary-500 h-1 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <!-- Quota 3: Alasan Penting -->
            <div class="bg-white rounded-3xl p-3 sm:p-3.5 border border-slate-100 shadow-sm text-center space-y-1 min-w-0">
                <span class="text-xl font-black text-purple-600 block leading-none truncate">
                    <?= $quotas['alasan_penting']['remaining'] ?? 5 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['alasan_penting']['total'] ?? 5 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block truncate">Penting</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-purple-500 h-1 rounded-full" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Primary Action Button -->
    <button type="button" onclick="openModalPermohonanCuti()" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold text-xs rounded-2xl shadow-lg shadow-primary-600/30 flex items-center justify-center gap-2 press-bounce transition-all">
        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
        <span>+ Ajukan Permohonan Cuti Baru</span>
    </button>

    <!-- 3. Leave History & Approval Timeline -->
    <div class="space-y-3 pt-1">
        <div class="flex items-center justify-between px-1 text-xs">
            <h3 class="font-bold text-slate-800 text-xs">Riwayat & Status Cuti</h3>
            <span class="text-[10px] text-slate-400 font-semibold"><?= count($records) ?> Riwayat</span>
        </div>

        <div id="cuti-list-container" class="space-y-3">
            <?php if (empty($records)): ?>
                <div id="cuti-empty-state" class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm text-center space-y-3">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-3xl">
                        🏖️
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Belum Ada Riwayat Cuti</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Anda belum pernah mengajukan permohonan cuti. Klik tombol di atas untuk mengajukan permohonan baru.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($records as $idx => $r): ?>
                    <div onclick="bukaDetailCuti(<?= $idx ?>)"
                         class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100 hover:border-primary-200 transition-all press-bounce cursor-pointer flex items-center justify-between gap-3">
                        
                        <!-- Sisi Kiri: Icon + Info Ringkas -->
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-lg font-bold shrink-0">
                                🏖️
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="font-bold text-slate-900 text-xs truncate leading-tight"><?= htmlspecialchars($r['type']) ?></h4>
                                    <span class="text-[10px] font-mono text-slate-400">#<?= htmlspecialchars($r['id']) ?></span>
                                </div>
                                
                                <p class="text-[11px] font-semibold text-slate-700 mt-0.5 truncate flex items-center gap-1">
                                    <span><?= htmlspecialchars($r['datesDisplay'] ?? ($r['startDate'] . ' s.d. ' . $r['endDate'])) ?></span>
                                    <span class="text-slate-400">&bull;</span>
                                    <span class="text-primary-700 font-bold"><?= $r['daysCount'] ?> hari</span>
                                </p>
                                <p class="text-[10px] text-slate-400 mt-0.5 truncate">
                                    Diajukan: <?= htmlspecialchars($r['submittedAt']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Status Badge & Tombol Detail -->
                        <div class="flex flex-col items-end justify-between gap-1.5 shrink-0">
                            <?php if ($r['status'] === 'Disetujui'): ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-800 flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3 h-3 text-primary-600"></i> Disetujui
                                </span>
                            <?php elseif ($r['status'] === 'Pending'): ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Diajukan
                                </span>
                            <?php elseif ($r['status'] === 'Dibatalkan'): ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 flex items-center gap-1 border border-slate-200">
                                    Dibatalkan
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 flex items-center gap-1">
                                    <i data-lucide="x-circle" class="w-3 h-3 text-rose-600"></i> <?= htmlspecialchars($r['status']) ?>
                                </span>
                            <?php endif; ?>

                            <span class="text-[10.5px] font-bold text-primary-700 flex items-center gap-0.5">
                                Detail ❯
                            </span>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL BOTTOM SHEET: PENGAJUAN PERMOHONAN CUTI RESMI      -->
<!-- ======================================================== -->
<div id="modal-permohonan-cuti" class="hidden fixed inset-0 z-[10010] overflow-hidden select-none" style="touch-action: none; overscroll-behavior: none;">
    <!-- Backdrop Scrim -->
    <div id="modal-cuti-scrim" onclick="closeModalPermohonanCuti()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-250 opacity-0 pointer-events-none"></div>

    <!-- Sheet Wrapper -->
    <div class="fixed inset-x-0 bottom-0 max-w-md mx-auto w-full pointer-events-none z-[10020]">
        <form id="form-permohonan-cuti" novalidate onsubmit="submitPermohonanCuti(event); return false;" enctype="multipart/form-data"
              style="touch-action: pan-y; overscroll-behavior: contain;"
              class="bg-white rounded-t-[32px] shadow-2xl pointer-events-auto border-t border-slate-200/80 flex flex-col max-h-[88vh] w-full transform translate-y-full transition-transform duration-250 ease-out overflow-hidden text-xs">

            <!-- 1. FIXED HEADER (shrink-0) -->
            <div class="px-5 pt-3.5 pb-3 border-b border-slate-100 shrink-0 bg-white" style="touch-action: none;">
                <div class="w-10 h-1.5 bg-slate-200 rounded-full mx-auto mb-3"></div>
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 pr-2">
                        <h3 class="font-extrabold text-slate-900 text-sm leading-tight flex items-center gap-1.5 truncate">
                            <span>Form Pengajuan Cuti</span>
                            <span class="text-xs">🏖️</span>
                        </h3>
                        <p class="text-[11px] text-primary-700 font-bold mt-0.5 truncate">
                            Pilih tanggal cuti &amp; tentukan rekan pengganti
                        </p>
                    </div>
                    <button type="button" onclick="closeModalPermohonanCuti()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center press-bounce text-xs font-bold shrink-0">
                        ✕
                    </button>
                </div>
            </div>

            <!-- 2. SCROLLABLE MIDDLE BODY (flex-1 min-h-0 overflow-y-auto) -->
            <div class="cuti-modal-scroll flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-4 sm:p-5 space-y-3.5 no-scrollbar select-text" style="touch-action: pan-y; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">

                <!-- 1. Jenis Cuti -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-800">
                        Jenis Cuti <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="modal_jenis_cuti_id" name="jenis_cuti_id" required
                                class="w-full px-3.5 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs">
                            <?php if (!empty($daftarJenisCuti)): ?>
                                <?php foreach ($daftarJenisCuti as $idx => $j): ?>
                                    <?php
                                        $kuotaStr = ((int)$j['kuota_hari'] > 0) ? (' (Maks ' . $j['kuota_hari'] . ' hari)') : '';
                                    ?>
                                    <option value="<?= (int)$j['id'] ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($j['nama_jenis']) ?><?= $kuotaStr ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">Cuti Tahunan</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- 2. Pemilihan Tanggal Cuti: Kalender Tombol-Tombol Per Bulan -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800">
                            Pilih Tanggal Cuti <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" onclick="openRangePickerCuti()" class="px-2.5 py-1 rounded-xl bg-primary-50 border border-primary-200 text-primary-700 font-bold text-[10.5px] hover:bg-primary-100 press-bounce shrink-0 shadow-2xs">
                            + Rentang Hari
                        </button>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 space-y-2.5">
                        <p class="text-[10px] text-slate-400 font-normal">Klik tombol tanggal untuk memilih (bisa pilih acak / lewati hari libur):</p>

                        <!-- Bulan Bar & Navigasi -->
                        <div class="flex items-center justify-between bg-slate-50/70 rounded-xl p-1.5 border border-slate-200/80 shadow-2xs">
                            <button type="button" onclick="navCalMonth(-1)" class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs press-bounce transition-all border border-slate-200/60 shadow-2xs">
                                ❮
                            </button>
                            <div class="flex items-center gap-1.5">
                                <span id="cal-month-title" class="text-xs font-black text-slate-900 tracking-tight">November 2026</span>
                                <button type="button" onclick="jumpCalToday()" class="px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-primary-50 text-primary-700 border border-primary-200 press-bounce">
                                    Bulan Ini
                                </button>
                            </div>
                            <button type="button" onclick="navCalMonth(1)" class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs press-bounce transition-all border border-slate-200/60 shadow-2xs">
                                ❯
                            </button>
                        </div>

                        <!-- Header 7 Nama Hari -->
                        <div class="grid grid-cols-7 gap-1 text-center font-bold text-[9.5px] text-slate-400 uppercase tracking-wider py-0.5">
                            <span class="text-rose-500">Min</span>
                            <span>Sen</span>
                            <span>Sel</span>
                            <span>Rab</span>
                            <span>Kam</span>
                            <span>Jum</span>
                            <span>Sab</span>
                        </div>

                        <!-- Grid Tombol-Tombol Tanggal -->
                        <div id="grid-cal-buttons" class="grid grid-cols-7 gap-1 sm:gap-1.5">
                            <!-- Populated dynamically via renderCalendarButtons() -->
                        </div>

                        <!-- Ringkasan Tanggal Terpilih & Tombol Reset -->
                        <div class="pt-2 border-t border-slate-100 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 font-medium text-[11px]">Total Hari Cuti:</span>
                                <span id="summary-total-hari" class="font-extrabold text-primary-700 bg-primary-100 px-2.5 py-0.5 rounded-full text-[10.5px]">
                                    0 Hari Kerja Terpilih
                                </span>
                            </div>
                            <div id="container-chips-cuti" class="flex flex-wrap gap-1 min-h-[32px] p-2 bg-slate-50/50 rounded-xl border border-slate-200/80">
                                <!-- Populated dynamically via renderSelectedChips() -->
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="clearSemuaTanggalCuti()" class="text-[10px] font-bold text-rose-500 hover:text-rose-700 hover:underline">
                                    ✕ Bersihkan Pilihan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Dropdown Rekan Pengganti (Sesuai Unit & Jenis Pegawai) -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800">
                            <?= !empty($isSupport) ? 'Pegawai Pengganti (Staff / Support System)' : 'Guru Pengganti' ?> <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-primary-700 font-bold bg-primary-50 px-2 py-0.5 rounded-full border border-primary-100 truncate max-w-[170px]">
                            Unit: <?= e($userUnit ?: 'Semua') ?> &bull; <?= !empty($isSupport) ? 'Support' : 'Guru' ?>
                        </span>
                    </div>
                    <div class="relative">
                        <select id="modal_pelimpahan_tugas" name="pelimpahan_tugas" required
                                class="w-full px-3.5 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs">
                            <?php if (!empty($isSupport)): ?>
                                <option value="Rekan Kerja / Tim Divisi">Rekan Kerja / Tim Divisi</option>
                            <?php else: ?>
                                <option value="Guru Piket dan Tim Bidang Unit">Guru Piket dan Tim Bidang Unit</option>
                            <?php endif; ?>

                            <?php if (!empty($substituteList)): ?>
                                <?php foreach ($substituteList as $sub): ?>
                                    <?php
                                        $namaLengkap = trim($sub['nama'] . (!empty($sub['gelar']) ? (', ' . $sub['gelar']) : ''));
                                        $jabatanUnit = trim(($sub['jabatan'] ?? (!empty($isSupport) ? 'Staff' : 'Guru')) . (!empty($sub['unit_tugas']) ? (' • ' . $sub['unit_tugas']) : ''));
                                    ?>
                                    <option value="<?= htmlspecialchars($namaLengkap) ?>">
                                        <?= htmlspecialchars($namaLengkap . ' (' . $jabatanUnit . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <p class="text-[10px] text-slate-400 font-normal">
                        Daftar pengganti otomatis disaring khusus untuk sesama <strong><?= !empty($isSupport) ? 'Staff / Support System' : 'Guru' ?></strong>.
                    </p>
                </div>

                <!-- 4. Keterangan Tugas yang Dilimpahkan -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-800">
                        Keterangan Tugas yang Dilimpahkan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="modal_keterangan_tugas" name="keterangan_tugas" rows="2" required
                              placeholder="Tuliskan rincian materi/tugas harian yang didelegasikan kepada rekan pengganti selama Anda cuti..."
                              class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs leading-relaxed"></textarea>
                </div>

                <!-- 5. Alasan / Keperluan Cuti -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-800">
                        Alasan / Keperluan Cuti <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="modal_alasan" name="alasan" rows="2" required
                              placeholder="Jelaskan alasan dan keperluan cuti secara jelas dan rinci..."
                              class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs leading-relaxed"></textarea>
                </div>

                <!-- 6. Nomor Kontak Darurat -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">
                        Nomor Kontak Darurat <span class="text-slate-400 font-normal">(Opsional - No. HP / WhatsApp Keluarga)</span>
                    </label>
                    <input type="text" id="modal_kontak_darurat" name="kontak_darurat" placeholder="Contoh: 0812-4455-6677 (Keluarga)"
                           class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs">
                </div>

                <!-- 7. Bukti Dokumen / Foto Pendukung (Maksimal 2 Foto) -->
                <div class="space-y-2 pt-1">
                    <label class="block text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Foto Bukti / Dokumen Pendukung <span class="text-slate-400 font-normal">(Maks 2 Foto)</span></span>
                        <span id="label-foto-counter" class="text-[10px] font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-full border border-primary-100">
                            0 / 2 Foto
                        </span>
                    </label>

                    <!-- Single Upload Actions Box (Model Jurnal Staff: Kamera & Galeri) -->
                    <div id="container-cuti-photo-actions" class="grid grid-cols-5 gap-2">
                        <button type="button" onclick="triggerCutiPhotoInput('camera')" class="col-span-3 px-3 py-2.5 rounded-2xl bg-primary-50 hover:bg-primary-100 border border-primary-200/80 text-primary-700 flex items-center justify-center gap-2 transition-all press-bounce">
                            <i data-lucide="camera" class="w-4 h-4 shrink-0"></i>
                            <span class="text-xs font-bold truncate">Ambil Kamera</span>
                        </button>
                        <button type="button" onclick="triggerCutiPhotoInput('gallery')" class="col-span-2 px-3 py-2.5 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 hover:bg-slate-100 text-slate-600 flex items-center justify-center gap-1.5 cursor-pointer transition-all press-bounce">
                            <i data-lucide="image" class="w-4 h-4 shrink-0 text-slate-500"></i>
                            <span class="text-xs font-semibold truncate">Galeri / File</span>
                        </button>
                    </div>

                    <!-- Hidden Inputs -->
                    <input type="file" id="inp_cuti_camera" accept="image/*" capture="environment" class="hidden" onchange="handleCutiFilesSelected(this.files)">
                    <input type="file" id="inp_cuti_gallery" accept="image/*" multiple class="hidden" onchange="handleCutiFilesSelected(this.files)">

                    <!-- Preview Gallery Grid (Tempat Foto yang Sudah Dipilih) -->
                    <div id="container-cuti-photo-previews" class="hidden grid grid-cols-2 gap-2.5 pt-1">
                        <!-- Populated dynamically via renderCutiPhotoPreviews() -->
                    </div>

                    <!-- Max Reached Banner (Muncul jika sudah 2 foto) -->
                    <div id="banner-max-photos" class="hidden p-2.5 rounded-2xl bg-slate-100 text-slate-600 text-[10.5px] font-semibold text-center border border-slate-200/80">
                        ✓ Maksimal 2 foto bukti telah dipilih. Hapus salah satu jika ingin mengganti.
                    </div>
                </div>

            </div>

            <!-- 3. FIXED FOOTER ACTION BUTTONS (shrink-0) -->
            <div class="shrink-0 px-5 py-3.5 bg-white border-t border-slate-100 flex items-center gap-2.5 safe-bottom">
                <button type="button" onclick="closeModalPermohonanCuti()" id="btn-cancel-modal" class="w-1/3 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs press-bounce transition-all">
                    Batal
                </button>
                <button type="button" onclick="submitPermohonanCuti(event)" id="btn-submit-cuti" class="w-2/3 py-3 rounded-2xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold text-xs shadow-md shadow-primary-600/25 press-bounce flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <span>💾</span>
                    <span id="btn-submit-text">Simpan Permohonan Cuti</span>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL BOTTOM SHEET: DETAIL LENGKAP PERMOHONAN CUTI       -->
<!-- ======================================================== -->
<div id="modal-detail-cuti" class="hidden fixed inset-0 z-[10025] overflow-hidden select-none" style="touch-action: none; overscroll-behavior: none;">
    <!-- Backdrop Scrim -->
    <div id="modal-detail-cuti-scrim" onclick="tutupDetailCuti()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-250 opacity-0 pointer-events-none"></div>

    <!-- Sheet Wrapper -->
    <div class="fixed inset-x-0 bottom-0 max-w-md mx-auto w-full pointer-events-none z-[10035]">
        <div id="sheet-detail-cuti" style="touch-action: pan-y; overscroll-behavior: contain;"
             class="bg-white rounded-t-[32px] shadow-2xl pointer-events-auto border-t border-slate-200/80 flex flex-col max-h-[88vh] w-full transform translate-y-full transition-transform duration-250 ease-out overflow-hidden text-xs">
            
            <!-- 1. Header Sheet -->
            <div class="px-5 pt-3.5 pb-3 border-b border-slate-100 shrink-0 bg-white" style="touch-action: none;">
                <div class="w-10 h-1.5 bg-slate-200 rounded-full mx-auto mb-2.5"></div>
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 pr-2">
                        <h3 class="font-black text-slate-900 text-sm leading-tight flex items-center gap-1.5 truncate">
                            <span>Detail Permohonan Cuti</span>
                            <span>📋</span>
                        </h3>
                        <p id="det-nomor-tgl" class="text-[10.5px] text-slate-400 font-mono font-medium truncate mt-0.5">-</p>
                    </div>
                    <button type="button" onclick="tutupDetailCuti()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center press-bounce text-xs font-bold shrink-0">
                        ✕
                    </button>
                </div>
            </div>

            <!-- 2. Body Scrollable -->
            <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-5 space-y-4 no-scrollbar select-text" style="touch-action: pan-y; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
                
                <!-- Status & Card Ringkasan -->
                <div class="bg-slate-50/90 rounded-2xl p-3.5 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span id="det-jenis-cuti" class="font-black text-slate-900 text-sm">-</span>
                        <div id="det-status-badge"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/70 text-slate-600">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Tanggal Cuti:</span>
                            <span id="det-tanggal-cuti" class="font-bold text-slate-800 text-xs block">-</span>
                            <span id="det-durasi-cuti" class="text-[10.5px] text-primary-700 font-bold block mt-0.5">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Diajukan Pada:</span>
                            <span id="det-submitted-at" class="font-medium text-slate-700 text-xs block mt-0.5">-</span>
                        </div>
                    </div>

                    <div id="det-container-dates-list" class="hidden pt-2 border-t border-slate-200/60">
                        <span class="text-[10px] text-slate-400 block font-semibold mb-1">Daftar Tanggal Terpilih:</span>
                        <div id="det-dates-chips" class="flex flex-wrap gap-1"></div>
                    </div>
                </div>

                <!-- Keperluan / Alasan -->
                <div class="space-y-1">
                    <span class="text-slate-400 font-semibold text-[11px] block">Keperluan / Alasan Cuti:</span>
                    <p id="det-alasan" class="font-medium text-slate-800 leading-relaxed bg-white p-3 rounded-2xl border border-slate-200/80 text-xs">-</p>
                </div>

                <!-- Rekan Pengganti & Keterangan Tugas -->
                <div class="bg-slate-50/70 rounded-2xl p-3.5 border border-slate-200/80 space-y-2.5">
                    <div>
                        <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Pegawai / Guru Pengganti:</span>
                        <p id="det-pengganti" class="font-bold text-slate-900 text-xs">-</p>
                    </div>

                    <div id="det-box-keterangan-tugas" class="pt-2 border-t border-slate-200/70">
                        <span class="text-slate-400 font-semibold text-[11px] block mb-1">Keterangan Pelimpahan Tugas:</span>
                        <p id="det-keterangan-tugas" class="font-medium text-slate-700 leading-relaxed bg-white p-2.5 rounded-xl border border-slate-200/80 text-xs whitespace-pre-line">-</p>
                    </div>

                    <div id="det-box-kontak" class="pt-2 border-t border-slate-200/70">
                        <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Kontak Darurat:</span>
                        <p id="det-kontak" class="font-medium text-slate-800 text-xs">-</p>
                    </div>
                </div>

                <!-- Bagan Alur Persetujuan (Siapa Approve & Kapan Approve) -->
                <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <span>Alur Persetujuan (Approval)</span>
                        </span>
                        <span id="det-timeline-count" class="text-[10px] text-slate-400 font-semibold"></span>
                    </div>

                    <div id="det-timeline-list" class="space-y-2.5">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- Foto Bukti / Dokumen Pendukung -->
                <div id="det-box-lampiran" class="space-y-2 hidden">
                    <span class="text-[11px] font-black text-slate-800 block">Foto Bukti / Dokumen Pendukung:</span>
                    <div id="det-foto-grid" class="grid grid-cols-2 gap-2">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

            </div>

            <!-- 3. Footer Action -->
            <div class="px-5 py-3.5 pb-6 sm:pb-3.5 bg-white border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0 shadow-lg safe-bottom">
                <button type="button" onclick="tutupDetailCuti()"
                    class="py-2.5 px-4 rounded-2xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all press-bounce">
                    Tutup
                </button>
                <div id="det-btn-batal-container"></div>
            </div>

        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL BOTTOM SHEET: BATALKAN & HAPUS PERMOHONAN CUTI      -->
<!-- ======================================================== -->
<div id="modal-batal-cuti" class="hidden fixed inset-0 z-[10040] overflow-hidden select-none" style="touch-action: none; overscroll-behavior: none;">
    <!-- Backdrop Scrim -->
    <div id="modal-batal-cuti-scrim" onclick="tutupModalBatalCuti()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-250 opacity-0 pointer-events-none"></div>

    <!-- Sheet Wrapper -->
    <div class="fixed inset-x-0 bottom-0 max-w-md mx-auto w-full pointer-events-none z-[10050]">
        <div id="sheet-batal-cuti" style="touch-action: pan-y; overscroll-behavior: contain;"
             class="bg-white rounded-t-[32px] shadow-2xl pointer-events-auto border-t border-slate-200/80 p-5 sm:p-6 space-y-4 max-h-[85vh] w-full transform translate-y-full transition-transform duration-250 ease-out text-xs">
            
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-1"></div>

            <div class="flex items-start gap-3.5 pt-1">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl font-bold shrink-0 shadow-xs">
                    ⚠️
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">Batalkan &amp; Hapus Pengajuan?</h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Permohonan <strong id="batal_cuti_nomor" class="text-slate-900 font-mono font-bold"></strong> (<span id="batal_cuti_jenis" class="font-bold text-slate-800"></span>) akan ditarik dan datanya dihapus permanen dari sistem.
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alasan Pembatalan (Opsional):</label>
                <textarea id="batal_cuti_alasan" rows="2" placeholder="Contoh: Ada perubahan jadwal tugas / urusan mendadak ditunda..."
                    class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs text-slate-800 bg-slate-50 focus:bg-white outline-none focus:border-rose-400 transition-all"></textarea>
            </div>

            <input type="hidden" id="batal_cuti_raw_id" value="">

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" onclick="tutupModalBatalCuti()"
                    class="w-full py-3 px-4 rounded-2xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all press-bounce cursor-pointer text-center">
                    Kembali
                </button>
                <button type="button" onclick="kirimBatalkanCuti()" id="btn-konfirmasi-batal-cuti"
                    class="w-full py-3 px-4 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/25 transition-all press-bounce cursor-pointer text-center flex items-center justify-center gap-1.5">
                    <span id="btn-text-batal">Ya, Batalkan &amp; Hapus</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // State Kalender & Pilihan Tanggal
    window.selectedCutiDates = [];
    const today = new Date();
    window.calYear = today.getFullYear();
    window.calMonth = today.getMonth(); // 0 - 11

    // State Bukti Foto Terkompresi (Array objek foto)
    window.cutiPhotos = [];

    const NAMA_BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const NAMA_HARI_SHORT = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    // Inisialisasi awal icon Lucide dan bersihkan sisa modal usang di document.body
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
    document.querySelectorAll('body > #modal-permohonan-cuti, body > #modal-detail-cuti, body > #modal-batal-cuti').forEach(el => el.remove());

    // Buka Modal Pengajuan Cuti
    function openModalPermohonanCuti() {
        // Bersihkan sisa elemen modal lama di body jika ada
        document.querySelectorAll('body > #modal-permohonan-cuti').forEach(el => el.remove());

        const modal = document.getElementById('modal-permohonan-cuti');
        const scrim = document.getElementById('modal-cuti-scrim');
        const sheet = document.getElementById('form-permohonan-cuti');
        if (!modal) return;

        // Sembunyikan bottom navigation bar agar tidak menutupi tombol simpan
        const bNav = document.getElementById('mobile-bottom-nav');
        if (bNav) bNav.style.display = 'none';

        // Default set minimal 1 hari jika belum ada
        if (!window.selectedCutiDates || window.selectedCutiDates.length === 0) {
            const pad = n => String(n).padStart(2, '0');
            const now = new Date();
            const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
            window.selectedCutiDates = [todayStr];
        }

        renderCalendarButtons();
        renderSelectedChips();
        renderCutiPhotoPreviews();

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            if (scrim) {
                scrim.classList.remove('opacity-0', 'pointer-events-none');
                scrim.classList.add('opacity-100', 'pointer-events-auto');
            }
            if (sheet) {
                sheet.classList.remove('translate-y-full');
                sheet.classList.add('translate-y-0');
            }
        });
        document.body.classList.add('overflow-hidden');
    }

    // Tutup Modal Pengajuan Cuti
    function closeModalPermohonanCuti() {
        const modal = document.getElementById('modal-permohonan-cuti');
        const scrim = document.getElementById('modal-cuti-scrim');
        const sheet = document.getElementById('form-permohonan-cuti');

        // Kembalikan bottom navigation bar jika modal lain tidak terbuka
        const modalDetail = document.getElementById('modal-detail-cuti');
        const modalBatal = document.getElementById('modal-batal-cuti');
        const isOtherOpen = (modalDetail && !modalDetail.classList.contains('hidden')) || (modalBatal && !modalBatal.classList.contains('hidden'));
        if (!isOtherOpen) {
            const bNav = document.getElementById('mobile-bottom-nav');
            if (bNav) bNav.style.display = '';
            document.body.classList.remove('overflow-hidden');
        }

        if (scrim) {
            scrim.classList.remove('opacity-100', 'pointer-events-auto');
            scrim.classList.add('opacity-0', 'pointer-events-none');
        }
        if (sheet) {
            sheet.classList.remove('translate-y-0');
            sheet.classList.add('translate-y-full');
        }

        setTimeout(() => {
            if (modal) {
                modal.classList.add('hidden');
            }
        }, 260);
    }

    // Navigasi Bulan Kalender
    function navCalMonth(step) {
        window.calMonth += step;
        if (window.calMonth < 0) {
            window.calMonth = 11;
            window.calYear--;
        } else if (window.calMonth > 11) {
            window.calMonth = 0;
            window.calYear++;
        }
        renderCalendarButtons();
    }

    // Lompat ke Bulan Ini
    function jumpCalToday() {
        const now = new Date();
        window.calYear = now.getFullYear();
        window.calMonth = now.getMonth();
        renderCalendarButtons();
    }

    // Render Grid Tombol Tanggal Per Bulan
    function renderCalendarButtons() {
        const grid = document.getElementById('grid-cal-buttons');
        const title = document.getElementById('cal-month-title');
        if (!grid || !title) return;

        title.textContent = `${NAMA_BULAN[window.calMonth]} ${window.calYear}`;

        const pad = n => String(n).padStart(2, '0');
        const now = new Date();
        const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;

        // Hari pertama bulan (0 = Min, 1 = Sen, ...)
        const firstDayIndex = new Date(window.calYear, window.calMonth, 1).getDay();
        // Jumlah hari dalam bulan
        const daysInMonth = new Date(window.calYear, window.calMonth + 1, 0).getDate();

        let html = '';

        // Padding sebelum tanggal 1
        for (let i = 0; i < firstDayIndex; i++) {
            html += `<div class="h-9 sm:h-10 rounded-xl opacity-20 pointer-events-none"></div>`;
        }

        // Tombol-tombol tanggal 1 sampai akhir bulan
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${window.calYear}-${pad(window.calMonth + 1)}-${pad(d)}`;
            const isSelected = window.selectedCutiDates.includes(dateStr);
            const isToday = (dateStr === todayStr);
            const dayOfWeek = new Date(window.calYear, window.calMonth, d).getDay();
            const isSunday = (dayOfWeek === 0);

            let btnClass = 'cal-day-btn h-9 sm:h-10 rounded-xl flex flex-col items-center justify-center transition-all cursor-pointer relative select-none press-bounce text-xs ';

            if (isSelected) {
                // Style Aktif Terpilih: Solid Biru Portal
                btnClass += 'bg-primary-600 text-white font-black shadow-md border-2 border-primary-400 scale-105 z-10';
            } else if (isSunday) {
                // Hari Minggu / Libur
                btnClass += 'bg-rose-50 text-rose-600 font-bold border border-rose-100 hover:bg-rose-100';
            } else {
                // Hari Kerja Normal
                btnClass += 'bg-white text-slate-800 font-semibold border border-slate-200/90 hover:bg-primary-50/60 hover:border-primary-300 shadow-2xs';
            }

            if (isToday && !isSelected) {
                btnClass += ' ring-2 ring-primary-500 font-black text-primary-700';
            }

            html += `
                <button type="button" onclick="toggleTanggalCuti('${dateStr}')" class="${btnClass}">
                    <span class="leading-none">${d}</span>
                    ${isSelected ? '<span class="w-1.5 h-1.5 rounded-full bg-white mt-0.5"></span>' : (isToday ? '<span class="w-1.5 h-1.5 rounded-full bg-primary-600 mt-0.5"></span>' : '')}
                </button>
            `;
        }

        grid.innerHTML = html;
    }

    // Toggle Pilihan Tanggal
    function toggleTanggalCuti(dateStr) {
        if (!Array.isArray(window.selectedCutiDates)) window.selectedCutiDates = [];

        const idx = window.selectedCutiDates.indexOf(dateStr);
        if (idx !== -1) {
            window.selectedCutiDates.splice(idx, 1);
        } else {
            window.selectedCutiDates.push(dateStr);
        }
        window.selectedCutiDates.sort();

        renderCalendarButtons();
        renderSelectedChips();
    }

    // Render Chip Daftar Tanggal Terpilih & Update Total Hari
    function renderSelectedChips() {
        const container = document.getElementById('container-chips-cuti');
        const badge = document.getElementById('summary-total-hari');
        if (!container) return;

        if (!Array.isArray(window.selectedCutiDates)) window.selectedCutiDates = [];
        window.selectedCutiDates.sort();

        const count = window.selectedCutiDates.length;

        if (badge) {
            badge.textContent = `${count} Hari Kerja Terpilih`;
        }

        if (count === 0) {
            container.innerHTML = `<span class="text-[11px] text-slate-400 italic py-1 px-1">Ketuk tombol tanggal di atas untuk memilih.</span>`;
            return;
        }

        container.innerHTML = window.selectedCutiDates.map(dateStr => {
            const parts = dateStr.split('-');
            const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            const hari = NAMA_HARI_SHORT[d.getDay()] || '';
            const tgl = d.getDate();
            const bln = NAMA_BULAN[d.getMonth()] ? NAMA_BULAN[d.getMonth()].substring(0, 3) : '';
            const label = `${hari}, ${tgl} ${bln}`;

            return `
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10.5px] font-bold bg-primary-50 border border-primary-200 text-primary-900 shadow-2xs">
                    <span>📅 ${label}</span>
                    <button type="button" onclick="toggleTanggalCuti('${dateStr}')" class="text-slate-400 hover:text-rose-600 font-black ml-0.5" title="Hapus">✕</button>
                </span>
            `;
        }).join('');
    }

    // Bersihkan Semua Pilihan Tanggal
    function clearSemuaTanggalCuti() {
        window.selectedCutiDates = [];
        renderCalendarButtons();
        renderSelectedChips();
    }

    // Shortcut: Pilih Rentang Tanggal Cepat
    function openRangePickerCuti() {
        const tglMulai = prompt('Masukkan Tanggal Mulai (YYYY-MM-DD):', window.selectedCutiDates[0] || '');
        if (!tglMulai) return;
        const tglSelesai = prompt('Masukkan Tanggal Selesai (YYYY-MM-DD):', tglMulai);
        if (!tglSelesai) return;

        const d1 = new Date(tglMulai);
        const d2 = new Date(tglSelesai);
        if (isNaN(d1.getTime()) || isNaN(d2.getTime()) || d2 < d1) {
            alert('Rentang tanggal tidak valid.');
            return;
        }

        const pad = n => String(n).padStart(2, '0');
        const cur = new Date(d1);
        while (cur <= d2) {
            // Lewati hari Minggu / Ahad
            if (cur.getDay() !== 0) {
                const s = `${cur.getFullYear()}-${pad(cur.getMonth() + 1)}-${pad(cur.getDate())}`;
                if (!window.selectedCutiDates.includes(s)) {
                    window.selectedCutiDates.push(s);
                }
            }
            cur.setDate(cur.getDate() + 1);
        }
        window.selectedCutiDates.sort();
        renderCalendarButtons();
        renderSelectedChips();
    }

    // Trigger Input File Kamera / Galeri
    function triggerCameraInput(slot) {
        const inp = document.getElementById(`inp_file_camera_${slot}`);
        if (inp) {
            inp.value = '';
            inp.click();
        }
    }

    function triggerGalleryInput(slot) {
        const inp = document.getElementById(`inp_file_gallery_${slot}`);
        if (inp) {
            inp.value = '';
            inp.click();
        }
    }

    // Kompresi Foto dengan Canvas (Target &le; 500 KB)
    function compressImageFile(file, maxBytes, callback) {
        if (!file || !file.type.startsWith('image/')) {
            callback(file, null, file ? file.size : 0);
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const maxDim = 1600;
                let w = img.width;
                let h = img.height;
                if (w > maxDim || h > maxDim) {
                    if (w > h) {
                        h = Math.round((h * maxDim) / w);
                        w = maxDim;
                    } else {
                        w = Math.round((w * maxDim) / h);
                        h = maxDim;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);

                let quality = 0.85;
                function tryExport() {
                    const dataUrl = canvas.toDataURL('image/jpeg', quality);
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            callback(file, dataUrl, file.size);
                            return;
                        }
                        if (blob.size > maxBytes && quality > 0.3) {
                            quality -= 0.12;
                            tryExport();
                        } else {
                            callback(blob, dataUrl, blob.size);
                        }
                    }, 'image/jpeg', quality);
                }
                tryExport();
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Trigger Input Foto (Kamera atau Galeri, maksimal 2 foto)
    function triggerCutiPhotoInput(type) {
        if (!Array.isArray(window.cutiPhotos)) window.cutiPhotos = [];
        if (window.cutiPhotos.length >= 2) {
            if (typeof AndroidUI !== 'undefined') {
                AndroidUI.toast('Maksimal lampiran adalah 2 foto.', 'warning');
            } else {
                alert('Maksimal lampiran adalah 2 foto.');
            }
            return;
        }

        const inp = (type === 'camera') 
            ? document.getElementById('inp_cuti_camera') 
            : document.getElementById('inp_cuti_gallery');
        if (inp) {
            inp.value = '';
            inp.click();
        }
    }

    // Handler saat file foto dipilih dari kamera atau galeri
    function handleCutiFilesSelected(fileList) {
        if (!fileList || fileList.length === 0) return;
        if (!Array.isArray(window.cutiPhotos)) window.cutiPhotos = [];

        const files = Array.from(fileList);
        const maxBytes = 500 * 1024; // 500 KB

        for (let i = 0; i < files.length; i++) {
            if (window.cutiPhotos.length >= 2) break;
            const file = files[i];
            compressImageFile(file, maxBytes, (compressedBlob, dataUrl, size) => {
                if (window.cutiPhotos.length >= 2) return;
                window.cutiPhotos.push({
                    id: Date.now() + Math.random(),
                    file: file,
                    blob: compressedBlob,
                    dataUrl: dataUrl,
                    name: file.name || `bukti_cuti_${window.cutiPhotos.length + 1}.jpg`,
                    size: size
                });
                renderCutiPhotoPreviews();
            });
        }
    }

    // Hapus Foto Terpilih
    function removeCutiPhoto(index) {
        if (!Array.isArray(window.cutiPhotos)) window.cutiPhotos = [];
        if (index >= 0 && index < window.cutiPhotos.length) {
            window.cutiPhotos.splice(index, 1);
            renderCutiPhotoPreviews();
        }
    }

    // Render Preview Foto (Mirip Sarpras Multi-Foto Preview)
    function renderCutiPhotoPreviews() {
        const previewContainer = document.getElementById('container-cuti-photo-previews');
        const actionsBox = document.getElementById('container-cuti-photo-actions');
        const maxBanner = document.getElementById('banner-max-photos');
        const counterBadge = document.getElementById('label-foto-counter');

        if (!Array.isArray(window.cutiPhotos)) window.cutiPhotos = [];
        const count = window.cutiPhotos.length;

        if (counterBadge) counterBadge.textContent = `${count} / 2 Foto`;

        if (count === 0) {
            if (previewContainer) {
                previewContainer.innerHTML = '';
                previewContainer.classList.add('hidden');
            }
            if (actionsBox) actionsBox.classList.remove('hidden');
            if (maxBanner) maxBanner.classList.add('hidden');
            return;
        }

        if (previewContainer) {
            previewContainer.classList.remove('hidden');
            previewContainer.innerHTML = window.cutiPhotos.map((photo, idx) => {
                const sizeKB = Math.round(photo.size / 1024);
                return `
                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-2xs p-2 flex flex-col justify-between space-y-1.5">
                        <div class="relative w-full h-24 rounded-xl overflow-hidden bg-slate-100">
                            <img src="${photo.dataUrl}" alt="Bukti ${idx + 1}" class="w-full h-full object-cover">
                            <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-md bg-slate-900/75 text-white text-[9px] font-bold">
                                Foto ${idx + 1}
                            </span>
                            <button type="button" onclick="removeCutiPhoto(${idx})" class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold shadow-md press-bounce" title="Hapus Foto">
                                ✕
                            </button>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-slate-800 truncate leading-tight">${photo.name}</p>
                            <p class="text-[9px] font-semibold text-emerald-700 mt-0.5">✓ &le; 500 KB (${sizeKB} KB)</p>
                        </div>
                    </div>
                `;
            }).join('');
        }

        if (count >= 2) {
            if (actionsBox) actionsBox.classList.add('hidden');
            if (maxBanner) maxBanner.classList.remove('hidden');
        } else {
            if (actionsBox) actionsBox.classList.remove('hidden');
            if (maxBanner) maxBanner.classList.add('hidden');
        }
    }

    // Helper Notifikasi Toast Material Safe
    function showCutiToast(msg, type = 'warning') {
        if (window.AndroidUI && typeof window.AndroidUI.toast === 'function') {
            window.AndroidUI.toast(msg, type);
        } else if (typeof window.showToast === 'function') {
            window.showToast(msg, type);
        } else {
            alert(msg);
        }
    }

    // Submit Permohonan Cuti
    async function submitPermohonanCuti(e) {
        if (e && typeof e.preventDefault === 'function') {
            e.preventDefault();
        }
        if (e && typeof e.stopPropagation === 'function') {
            e.stopPropagation();
        }

        const jenisId = document.getElementById('modal_jenis_cuti_id')?.value || '';
        const substitute = document.getElementById('modal_pelimpahan_tugas')?.value || '';
        let keteranganTugas = (document.getElementById('modal_keterangan_tugas')?.value || '').trim();
        const alasan = (document.getElementById('modal_alasan')?.value || '').trim();
        const kontak = (document.getElementById('modal_kontak_darurat')?.value || '').trim();

        const btnSubmit = document.getElementById('btn-submit-cuti');
        const btnText = document.getElementById('btn-submit-text');

        if (!jenisId) {
            showCutiToast('Mohon pilih jenis cuti terlebih dahulu.', 'warning');
            return;
        }

        if (!window.selectedCutiDates || window.selectedCutiDates.length === 0) {
            showCutiToast('Silakan pilih minimal 1 tanggal cuti dengan mengklik tombol tanggal.', 'warning');
            return;
        }

        if (!alasan) {
            const inputAlasan = document.getElementById('modal_alasan');
            if (inputAlasan) {
                inputAlasan.focus();
                inputAlasan.classList.add('ring-2', 'ring-rose-500', 'border-rose-500');
                inputAlasan.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    inputAlasan.classList.remove('ring-2', 'ring-rose-500', 'border-rose-500');
                }, 3000);
            }
            showCutiToast('Mohon isi alasan / keperluan cuti Anda.', 'warning');
            return;
        }

        if (!keteranganTugas) {
            keteranganTugas = 'Pelimpahan tugas dan koordinasi dengan ' + (substitute || 'rekan kerja / pengganti') + '.';
        }

        const sortedDates = [...window.selectedCutiDates].sort();
        const startDate = sortedDates[0];
        const endDate = sortedDates[sortedDates.length - 1];

        const formData = new FormData();
        formData.append('jenis_cuti_id', jenisId);
        formData.append('tanggal_pilihan', JSON.stringify(sortedDates));
        formData.append('tanggal_mulai', startDate);
        formData.append('tanggal_selesai', endDate);
        formData.append('jumlah_hari', sortedDates.length);
        formData.append('pelimpahan_tugas', substitute);
        formData.append('keterangan_tugas', keteranganTugas);
        formData.append('alasan', alasan);
        formData.append('kontak_darurat', kontak);
        formData.append('alamat_selama_cuti', '');

        // Lampirkan Foto dari array cutiPhotos (Maksimal 2 Foto terkompresi <= 500KB)
        if (Array.isArray(window.cutiPhotos)) {
            if (window.cutiPhotos[0]) {
                formData.append('foto_bukti_1', window.cutiPhotos[0].blob, window.cutiPhotos[0].name);
            }
            if (window.cutiPhotos[1]) {
                formData.append('foto_bukti_2', window.cutiPhotos[1].blob, window.cutiPhotos[1].name);
            }
        }

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
        }
        if (btnText) {
            btnText.innerHTML = '<span class="inline-block animate-spin mr-1.5">⏳</span>Menyimpan...';
        }

        try {
            const res = await fetch('<?= url("mobile/cuti/submit") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await res.json();

            if (json && json.success) {
                closeModalPermohonanCuti();
                if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.success === 'function') {
                    AndroidUI.success({
                        title: 'Permohonan Cuti Terkirim! 🏖️',
                        subtitle: 'Nomor: ' + (json.nomor_pengajuan || 'Tersimpan'),
                        message: json.message || 'Permohonan cuti Anda berhasil diajukan dan sedang menunggu verifikasi.',
                        buttonText: 'OK',
                        onClick: () => {
                            if (window.MobileApp && typeof window.MobileApp.navigate === 'function') {
                                window.MobileApp.navigate('<?= url("mobile/cuti") ?>', { forceRefresh: true });
                            } else {
                                window.location.reload();
                            }
                        }
                    });
                } else {
                    alert(json.message || 'Permohonan cuti berhasil diajukan!');
                    window.location.reload();
                }
            } else {
                const msg = (json && json.message) || 'Gagal mengajukan cuti.';
                if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.alert === 'function') {
                    AndroidUI.alert({
                        title: 'Gagal Mengajukan Cuti',
                        subtitle: 'Periksa Pengisian Form',
                        message: msg,
                        icon: '⚠️',
                        iconBg: 'bg-rose-100 text-rose-700'
                    });
                } else {
                    showCutiToast(msg, 'error');
                }
            }
        } catch (err) {
            console.error('Submit cuti error:', err);
            showCutiToast('Terjadi kesalahan jaringan saat mengirim permohonan cuti.', 'error');
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
            }
            if (btnText) {
                btnText.textContent = 'Simpan Permohonan Cuti';
            }
        }
    }

    // Expose functions globally
    window.openModalPermohonanCuti = openModalPermohonanCuti;
    window.closeModalPermohonanCuti = closeModalPermohonanCuti;
    window.navCalMonth = navCalMonth;
    window.jumpCalToday = jumpCalToday;
    window.toggleTanggalCuti = toggleTanggalCuti;
    window.clearSemuaTanggalCuti = clearSemuaTanggalCuti;
    window.openRangePickerCuti = openRangePickerCuti;
    window.triggerCutiPhotoInput = triggerCutiPhotoInput;
    window.handleCutiFilesSelected = handleCutiFilesSelected;
    window.removeCutiPhoto = removeCutiPhoto;
    window.renderCutiPhotoPreviews = renderCutiPhotoPreviews;
    window.submitPermohonanCuti = submitPermohonanCuti;

    // Simpan Data Riwayat Cuti untuk Interaktivitas Modal Detail
    window.cutiRecords = <?= json_encode($records, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    // Helper escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Modal Detail Permohonan Cuti
    function bukaDetailCuti(index) {
        // Bersihkan sisa elemen modal lama di body jika ada
        document.querySelectorAll('body > #modal-detail-cuti').forEach(el => el.remove());

        const r = window.cutiRecords && window.cutiRecords[index];
        if (!r) return;

        // Populate Data Header
        const detNomorTgl = document.getElementById('det-nomor-tgl');
        if (detNomorTgl) {
            detNomorTgl.textContent = '#' + (r.id || '-') + ' • Diajukan ' + (r.submittedAt || '-');
        }

        const detJenis = document.getElementById('det-jenis-cuti');
        if (detJenis) detJenis.textContent = r.type || 'Permohonan Cuti';

        // Badge Status
        const detStatusBadge = document.getElementById('det-status-badge');
        if (detStatusBadge) {
            let badgeHtml = '';
            if (r.status === 'Disetujui') {
                badgeHtml = '<span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-primary-100 text-primary-800 flex items-center gap-1"><i data-lucide="check-circle" class="w-3.5 h-3.5 text-primary-600"></i> Disetujui</span>';
            } else if (r.status === 'Pending') {
                badgeHtml = '<span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-800 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Diajukan</span>';
            } else if (r.status === 'Dibatalkan') {
                badgeHtml = '<span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 flex items-center gap-1 border border-slate-200">Dibatalkan</span>';
            } else {
                badgeHtml = `<span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-rose-100 text-rose-800 flex items-center gap-1"><i data-lucide="x-circle" class="w-3.5 h-3.5 text-rose-600"></i> ${escapeHtml(r.status)}</span>`;
            }
            detStatusBadge.innerHTML = badgeHtml;
        }

        // Tanggal & Durasi
        const detTgl = document.getElementById('det-tanggal-cuti');
        if (detTgl) detTgl.textContent = r.datesDisplay || (r.startDate + ' s.d. ' + r.endDate);

        const detDurasi = document.getElementById('det-durasi-cuti');
        if (detDurasi) detDurasi.textContent = (r.daysCount || 1) + ' Hari Kerja';

        const detSubmitted = document.getElementById('det-submitted-at');
        if (detSubmitted) detSubmitted.textContent = r.submittedAt || '-';

        // Chips Tanggal
        const datesBox = document.getElementById('det-container-dates-list');
        const datesChips = document.getElementById('det-dates-chips');
        if (datesBox && datesChips) {
            if (Array.isArray(r.datesList) && r.datesList.length > 1) {
                datesChips.innerHTML = r.datesList.map(d => {
                    return `<span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] font-semibold text-slate-700">${escapeHtml(d)}</span>`;
                }).join('');
                datesBox.classList.remove('hidden');
            } else {
                datesBox.classList.add('hidden');
            }
        }

        // Alasan
        const detAlasan = document.getElementById('det-alasan');
        if (detAlasan) detAlasan.textContent = r.reason || '-';

        // Pengganti & Tugas
        const detPengganti = document.getElementById('det-pengganti');
        if (detPengganti) detPengganti.textContent = r.substitute || '-';

        const boxKeterangan = document.getElementById('det-box-keterangan-tugas');
        const detKeterangan = document.getElementById('det-keterangan-tugas');
        if (boxKeterangan && detKeterangan) {
            if (r.keteranganTugas) {
                detKeterangan.textContent = r.keteranganTugas;
                boxKeterangan.classList.remove('hidden');
            } else {
                boxKeterangan.classList.add('hidden');
            }
        }

        const boxKontak = document.getElementById('det-box-kontak');
        const detKontak = document.getElementById('det-kontak');
        if (boxKontak && detKontak) {
            if (r.emergencyContact && r.emergencyContact !== '-') {
                detKontak.textContent = r.emergencyContact;
                boxKontak.classList.remove('hidden');
            } else {
                boxKontak.classList.add('hidden');
            }
        }

        // Timeline Approval (Siapa Approve, Kapan Approve)
        const timelineList = document.getElementById('det-timeline-list');
        const timelineCount = document.getElementById('det-timeline-count');
        if (timelineList) {
            timelineList.innerHTML = '';
            if (Array.isArray(r.timeline) && r.timeline.length > 0) {
                if (timelineCount) timelineCount.textContent = r.timeline.length + ' Tahapan';
                r.timeline.forEach((item, tIdx) => {
                    let dotColor = 'bg-slate-200 text-slate-500';
                    let iconChar = (tIdx + 1);
                    let timeColor = 'text-slate-500';

                    if (item.status === 'Disetujui' || item.done) {
                        dotColor = 'bg-emerald-500 text-white';
                        iconChar = '✓';
                        timeColor = 'text-emerald-700 font-semibold';
                    } else if (item.status === 'Ditolak') {
                        dotColor = 'bg-rose-500 text-white';
                        iconChar = '✕';
                        timeColor = 'text-rose-700 font-semibold';
                    } else if (item.status === 'Dilewati') {
                        dotColor = 'bg-slate-300 text-slate-600';
                        iconChar = '—';
                        timeColor = 'text-slate-400';
                    } else if (item.status === 'Pending') {
                        dotColor = 'bg-amber-400 text-white animate-pulse';
                        iconChar = '⏳';
                        timeColor = 'text-amber-700 font-semibold';
                    }

                    const stepDiv = document.createElement('div');
                    stepDiv.className = 'flex items-start gap-3 text-xs bg-slate-50/80 p-2.5 rounded-xl border border-slate-200/60';
                    stepDiv.innerHTML = `
                        <div class="w-6 h-6 rounded-full ${dotColor} flex items-center justify-center text-[10px] font-bold shrink-0 shadow-xs mt-0.5">
                            ${iconChar}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 flex-wrap">
                                <span class="font-bold text-slate-800 text-xs">${escapeHtml(item.step)}</span>
                            </div>
                            <p class="text-[11px] ${timeColor} mt-0.5 leading-tight">${escapeHtml(item.time)}</p>
                        </div>
                    `;
                    timelineList.appendChild(stepDiv);
                });
            } else {
                if (timelineCount) timelineCount.textContent = '';
                timelineList.innerHTML = '<p class="text-slate-400 text-xs italic">Belum ada tahapan alur verifikasi.</p>';
            }
        }

        // Lampiran Foto
        const boxLampiran = document.getElementById('det-box-lampiran');
        const fotoGrid = document.getElementById('det-foto-grid');
        if (boxLampiran && fotoGrid) {
            fotoGrid.innerHTML = '';
            let hasPhoto = false;

            const addPhotoCard = (photoPath, label) => {
                hasPhoto = true;
                const fullUrl = photoPath.startsWith('http') ? photoPath : ('<?= url("") ?>' + '/' + photoPath.replace(/^\/+/, ''));
                const card = document.createElement('a');
                card.href = fullUrl;
                card.target = '_blank';
                card.className = 'group block relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 hover:opacity-95 transition-all';
                card.innerHTML = `
                    <div class="aspect-4/3 w-full bg-slate-200 flex items-center justify-center overflow-hidden">
                        <img src="${fullUrl}" alt="${escapeHtml(label)}" class="w-full h-full object-cover">
                    </div>
                    <div class="p-1.5 bg-white text-center">
                        <span class="text-[10px] font-bold text-slate-700 block truncate">🔍 ${escapeHtml(label)}</span>
                    </div>
                `;
                fotoGrid.appendChild(card);
            };

            if (r.document) addPhotoCard(r.document, 'Bukti 1');
            if (r.document2) addPhotoCard(r.document2, 'Bukti 2');

            if (hasPhoto) {
                boxLampiran.classList.remove('hidden');
            } else {
                boxLampiran.classList.add('hidden');
            }
        }

        // Tombol Batalkan di Footer Modal Detail (Hanya jika status Pending)
        const btnBatalContainer = document.getElementById('det-btn-batal-container');
        if (btnBatalContainer) {
            btnBatalContainer.innerHTML = '';
            if (r.status === 'Pending' && r.raw_id) {
                const btnBatal = document.createElement('button');
                btnBatal.type = 'button';
                btnBatal.className = 'py-2.5 px-3.5 rounded-2xl bg-rose-50 hover:bg-rose-100 active:bg-rose-200 text-rose-600 font-bold text-xs flex items-center gap-1.5 border border-rose-200 transition-all press-bounce cursor-pointer';
                btnBatal.innerHTML = `
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    <span>Batalkan &amp; Hapus</span>
                `;
                btnBatal.onclick = () => {
                    bukaModalBatalCuti(r.raw_id, r.id, r.type);
                };
                btnBatalContainer.appendChild(btnBatal);
            }
        }

        // Tampilkan Modal Detail
        const modal = document.getElementById('modal-detail-cuti');
        const scrim = document.getElementById('modal-detail-cuti-scrim');
        const sheet = document.getElementById('sheet-detail-cuti');
        if (!modal || !scrim || !sheet) return;

        const bNav = document.getElementById('mobile-bottom-nav');
        if (bNav) bNav.style.display = 'none';

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            scrim.classList.remove('opacity-0', 'pointer-events-none');
            scrim.classList.add('opacity-100', 'pointer-events-auto');
            sheet.classList.remove('translate-y-full');
            sheet.classList.add('translate-y-0');
        });
        document.body.classList.add('overflow-hidden');

        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

    function tutupDetailCuti() {
        const modal = document.getElementById('modal-detail-cuti');
        const scrim = document.getElementById('modal-detail-cuti-scrim');
        const sheet = document.getElementById('sheet-detail-cuti');

        if (!modal || !scrim || !sheet) return;
        scrim.classList.remove('opacity-100', 'pointer-events-auto');
        scrim.classList.add('opacity-0', 'pointer-events-none');
        sheet.classList.remove('translate-y-0');
        sheet.classList.add('translate-y-full');

        // Kembalikan bottom navigation bar jika modal lain tidak terbuka
        const modalPermohonan = document.getElementById('modal-permohonan-cuti');
        const modalBatal = document.getElementById('modal-batal-cuti');
        const isOtherOpen = (modalPermohonan && !modalPermohonan.classList.contains('hidden')) || (modalBatal && !modalBatal.classList.contains('hidden'));
        if (!isOtherOpen) {
            const bNav = document.getElementById('mobile-bottom-nav');
            if (bNav) bNav.style.display = '';
            document.body.classList.remove('overflow-hidden');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 260);
    }

    // Handler Pembatalan Permohonan Cuti (Hapus Permanen Data)
    function bukaModalBatalCuti(rawId, nomor, jenis) {
        // Bersihkan sisa elemen modal lama di body jika ada
        document.querySelectorAll('body > #modal-batal-cuti').forEach(el => el.remove());

        const inputId = document.getElementById('batal_cuti_raw_id');
        const txtNomor = document.getElementById('batal_cuti_nomor');
        const txtJenis = document.getElementById('batal_cuti_jenis');
        const txtAlasan = document.getElementById('batal_cuti_alasan');

        if (inputId) inputId.value = rawId;
        if (txtNomor) txtNomor.textContent = nomor || ('#' + rawId);
        if (txtJenis) txtJenis.textContent = jenis || 'Cuti';
        if (txtAlasan) txtAlasan.value = '';

        const modal = document.getElementById('modal-batal-cuti');
        const scrim = document.getElementById('modal-batal-cuti-scrim');
        const sheet = document.getElementById('sheet-batal-cuti');

        if (!modal || !scrim || !sheet) return;

        // Sembunyikan bottom navigation bar agar tidak menutupi tombol konfirmasi
        const bNav = document.getElementById('mobile-bottom-nav');
        if (bNav) bNav.style.display = 'none';

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            scrim.classList.remove('opacity-0', 'pointer-events-none');
            scrim.classList.add('opacity-100', 'pointer-events-auto');
            sheet.classList.remove('translate-y-full');
            sheet.classList.add('translate-y-0');
        });
        document.body.classList.add('overflow-hidden');
    }

    function tutupModalBatalCuti() {
        const modal = document.getElementById('modal-batal-cuti');
        const scrim = document.getElementById('modal-batal-cuti-scrim');
        const sheet = document.getElementById('sheet-batal-cuti');

        if (!modal || !scrim || !sheet) return;
        scrim.classList.remove('opacity-100', 'pointer-events-auto');
        scrim.classList.add('opacity-0', 'pointer-events-none');
        sheet.classList.remove('translate-y-0');
        sheet.classList.add('translate-y-full');

        // Kembalikan bottom navigation bar jika modal lain juga tertutup
        const modalPermohonan = document.getElementById('modal-permohonan-cuti');
        const modalDetail = document.getElementById('modal-detail-cuti');
        const isOtherOpen = (modalPermohonan && !modalPermohonan.classList.contains('hidden')) || (modalDetail && !modalDetail.classList.contains('hidden'));
        if (!isOtherOpen) {
            const bNav = document.getElementById('mobile-bottom-nav');
            if (bNav) bNav.style.display = '';
            document.body.classList.remove('overflow-hidden');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 260);
    }

    async function kirimBatalkanCuti() {
        const rawId = document.getElementById('batal_cuti_raw_id')?.value;
        const alasan = (document.getElementById('batal_cuti_alasan')?.value || '').trim();
        const btn = document.getElementById('btn-konfirmasi-batal-cuti');
        const btnText = document.getElementById('btn-text-batal');

        if (!rawId) {
            showCutiToast('ID Pengajuan cuti tidak valid.', 'error');
            return;
        }

        try {
            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = 'Membatalkan & Menghapus...';

            const formData = new FormData();
            formData.append('pengajuan_id', rawId);
            formData.append('alasan_batal', alasan);

            const res = await fetch('<?= url("mobile/cuti/cancel") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const json = await res.json();
            if (json && json.success) {
                tutupModalBatalCuti();
                tutupDetailCuti();
                if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.toast === 'function') {
                    AndroidUI.toast(json.message || 'Permohonan cuti berhasil dibatalkan dan dihapus.', 'success');
                } else {
                    alert(json.message || 'Permohonan cuti berhasil dibatalkan dan datanya telah dihapus.');
                }
                setTimeout(() => {
                    window.location.reload();
                }, 400);
            } else {
                showCutiToast((json && json.message) || 'Gagal membatalkan permohonan cuti.', 'error');
            }
        } catch (err) {
            console.error('Cancel cuti error:', err);
            showCutiToast('Terjadi kesalahan jaringan saat membatalkan cuti.', 'error');
        } finally {
            if (btn) btn.disabled = false;
            if (btnText) btnText.textContent = 'Ya, Batalkan & Hapus';
        }
    }

    window.bukaDetailCuti = bukaDetailCuti;
    window.tutupDetailCuti = tutupDetailCuti;
    window.bukaModalBatalCuti = bukaModalBatalCuti;
    window.tutupModalBatalCuti = tutupModalBatalCuti;
    window.kirimBatalkanCuti = kirimBatalkanCuti;
</script>
