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
                <?php foreach ($records as $r): ?>
                    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
                        
                        <!-- Header: Type & Status -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-lg font-bold shrink-0">
                                    🏖️
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-black text-slate-900 text-xs truncate leading-tight"><?= htmlspecialchars($r['type']) ?></h4>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5"><?= htmlspecialchars($r['id']) ?> &bull; <?= $r['daysCount'] ?> Hari Kerja</p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <?php if ($r['status'] === 'Disetujui'): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-800 shrink-0 flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3 h-3 text-primary-600"></i> Disetujui
                                </span>
                            <?php elseif ($r['status'] === 'Pending'): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 shrink-0 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> Diajukan
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 shrink-0 flex items-center gap-1">
                                    <i data-lucide="x-circle" class="w-3 h-3 text-rose-600"></i> <?= htmlspecialchars($r['status']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Details Box -->
                        <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 space-y-2 text-xs">
                            <div class="flex items-start justify-between gap-2 text-slate-700">
                                <span class="text-slate-400 font-semibold text-[11px] shrink-0">Tanggal Cuti:</span>
                                <div class="text-right min-w-0">
                                    <span class="font-bold text-slate-800 text-xs block"><?= htmlspecialchars($r['datesDisplay'] ?? ($r['startDate'] . ' s.d. ' . $r['endDate'])) ?></span>
                                    <?php if (!empty($r['datesList']) && count($r['datesList']) > 1): ?>
                                        <div class="flex flex-wrap justify-end gap-1 mt-1">
                                            <?php foreach ($r['datesList'] as $dl): ?>
                                                <span class="px-1.5 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] font-semibold text-slate-700">
                                                    <?= date('d M', strtotime($dl)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-slate-700 pt-1 border-t border-slate-200/60">
                                <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Keperluan / Alasan:</span>
                                <p class="font-medium text-slate-800 leading-relaxed"><?= htmlspecialchars($r['reason']) ?></p>
                            </div>
                            <div class="flex items-center justify-between text-slate-700 pt-1 border-t border-slate-200/60">
                                <span class="text-slate-400 font-semibold text-[11px]">Pengganti Tugas:</span>
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($r['substitute']) ?></span>
                            </div>
                            <?php if (!empty($r['keteranganTugas'])): ?>
                                <div class="text-slate-700 pt-1 border-t border-slate-200/60">
                                    <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Keterangan Tugas:</span>
                                    <p class="font-medium text-slate-700 leading-relaxed text-[11px] bg-white p-2 rounded-xl border border-slate-200/70"><?= nl2br(htmlspecialchars($r['keteranganTugas'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 3-Step Approval Timeline -->
                        <?php if (!empty($r['timeline'])): ?>
                            <div class="pt-1 px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Proses Verifikasi</span>
                                <div class="space-y-2">
                                    <?php foreach ($r['timeline'] as $tIdx => $t): ?>
                                        <div class="flex items-center gap-2 text-xs">
                                            <div class="w-4 h-4 rounded-full <?= !empty($t['done']) ? 'bg-primary-500 text-white' : 'bg-slate-200 text-slate-400' ?> flex items-center justify-center text-[9px] font-bold shrink-0">
                                                <?= !empty($t['done']) ? '✓' : '•' ?>
                                            </div>
                                            <div class="flex-1 flex items-center justify-between">
                                                <span class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($t['step']) ?></span>
                                                <span class="text-[10px] text-slate-400"><?= htmlspecialchars($t['time']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Footer: Submission Info & Photos/Attachments -->
                        <div class="text-[10px] text-slate-400 pt-1.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-1.5">
                            <span>Diajukan: <?= htmlspecialchars($r['submittedAt']) ?></span>
                            <div class="flex items-center gap-2">
                                <?php if (!empty($r['document'])): ?>
                                    <a href="<?= url($r['document']) ?>" target="_blank" class="font-semibold text-primary-700 hover:underline flex items-center gap-1">
                                        <i data-lucide="image" class="w-3 h-3"></i> Bukti 1
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($r['document2'])): ?>
                                    <a href="<?= url($r['document2']) ?>" target="_blank" class="font-semibold text-primary-700 hover:underline flex items-center gap-1">
                                        <i data-lucide="image" class="w-3 h-3"></i> Bukti 2
                                    </a>
                                <?php endif; ?>
                            </div>
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
              class="bg-white rounded-t-[32px] shadow-2xl pointer-events-auto border-t border-slate-200/80 flex flex-col max-h-[90vh] w-full transform translate-y-full transition-transform duration-250 ease-out overflow-hidden text-xs">

            <!-- 1. FIXED HEADER (shrink-0) -->
            <div class="px-5 pt-3.5 pb-3 border-b border-slate-100 shrink-0 bg-white" style="touch-action: none;">
                <div class="w-10 h-1.5 bg-slate-200 rounded-full mx-auto mb-2.5"></div>
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 pr-2">
                        <h3 class="font-black text-slate-900 text-sm leading-tight flex items-center gap-1.5 truncate">
                            <span>Form Pengajuan Cuti</span>
                            <span class="text-xs">🏖️</span>
                        </h3>
                        <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">
                            Pilih tanggal cuti &amp; tentukan rekan pengganti
                        </p>
                    </div>
                    <button type="button" onclick="closeModalPermohonanCuti()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center press-bounce text-xs font-bold shrink-0">
                        ✕
                    </button>
                </div>
            </div>

            <!-- 2. SCROLLABLE MIDDLE BODY (flex-1 min-h-0 overflow-y-auto) -->
            <div class="cuti-modal-scroll flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-4 sm:p-5 space-y-4 no-scrollbar select-text" style="touch-action: pan-y; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">

                <!-- 1. Jenis Cuti -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Jenis Cuti <span class="text-rose-500">*</span>
                    </label>
                    <select id="modal_jenis_cuti_id" name="jenis_cuti_id" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
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

                <!-- 2. Pemilihan Tanggal Cuti: Kalender Tombol-Tombol Per Bulan -->
                <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between gap-1">
                        <div>
                            <label class="block text-xs font-black text-slate-800 leading-tight">
                                Pilih Tanggal Cuti <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-[10px] text-slate-400 font-medium">Klik tombol tanggal untuk memilih (bisa acak / lewati libur)</p>
                        </div>
                        <button type="button" onclick="openRangePickerCuti()" class="px-2.5 py-1 rounded-xl bg-white border border-primary-200 text-primary-700 font-bold text-[10.5px] hover:bg-primary-50 press-bounce shrink-0 shadow-2xs">
                            + Rentang Hari
                        </button>
                    </div>

                    <!-- Bulan Bar & Navigasi -->
                    <div class="flex items-center justify-between bg-white rounded-xl p-1.5 border border-slate-200/80 shadow-2xs">
                        <button type="button" onclick="navCalMonth(-1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs press-bounce transition-all">
                            ❮
                        </button>
                        <div class="flex items-center gap-1.5">
                            <span id="cal-month-title" class="text-xs font-black text-slate-900 tracking-tight">November 2026</span>
                            <button type="button" onclick="jumpCalToday()" class="px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-primary-50 text-primary-700 border border-primary-200 press-bounce">
                                Bulan Ini
                            </button>
                        </div>
                        <button type="button" onclick="navCalMonth(1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs press-bounce transition-all">
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
                    <div class="pt-2 border-t border-slate-200/80 space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-600 text-[11px]">Total Hari Cuti:</span>
                            <span id="summary-total-hari" class="text-primary-700 font-black text-xs bg-primary-50 px-2 py-0.5 rounded-lg border border-primary-200">
                                0 Hari Kerja Terpilih
                            </span>
                        </div>
                        <div id="container-chips-cuti" class="flex flex-wrap gap-1 min-h-[32px] p-1.5 bg-white rounded-xl border border-slate-200/70">
                            <!-- Populated dynamically via renderSelectedChips() -->
                        </div>
                        <div class="flex justify-end">
                            <button type="button" onclick="clearSemuaTanggalCuti()" class="text-[10px] font-bold text-rose-500 hover:text-rose-700 hover:underline">
                                ✕ Bersihkan Pilihan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Dropdown Rekan Pengganti (Sesuai Unit & Jenis Pegawai) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            <?= !empty($isSupport) ? 'Pegawai Pengganti (Staff / Support System)' : 'Guru Pengganti' ?> <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-primary-700 font-bold bg-primary-50 px-2 py-0.5 rounded-full border border-primary-200 truncate max-w-[170px]">
                            Unit: <?= e($userUnit ?: 'Semua') ?> &bull; <?= !empty($isSupport) ? 'Support System' : 'Guru' ?>
                        </span>
                    </div>
                    <select id="modal_pelimpahan_tugas" name="pelimpahan_tugas" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
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
                    <p class="text-[10px] text-slate-400 font-medium mt-1">
                        Daftar pengganti otomatis disaring khusus untuk sesama <strong><?= !empty($isSupport) ? 'Staff / Support System' : 'Guru' ?></strong>.
                    </p>
                </div>

                <!-- 4. Keterangan Tugas yang Dilimpahkan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Keterangan Tugas yang Dilimpahkan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="modal_keterangan_tugas" name="keterangan_tugas" rows="2" required
                              placeholder="Tuliskan rincian materi/tugas harian yang didelegasikan kepada rekan pengganti selama Anda cuti..."
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner resize-none"></textarea>
                </div>

                <!-- 5. Alasan / Keperluan Cuti -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Alasan / Keperluan Cuti <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="modal_alasan" name="alasan" rows="2" required
                              placeholder="Jelaskan alasan dan keperluan cuti secara jelas dan rinci..."
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner resize-none"></textarea>
                </div>

                <!-- 6. Nomor Kontak Darurat (Alamat Selama Cuti Ditiadakan) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor Kontak Darurat <span class="text-slate-400 font-normal">(Opsional - No. HP / WhatsApp Keluarga)</span>
                    </label>
                    <input type="text" id="modal_kontak_darurat" name="kontak_darurat" placeholder="Contoh: 0812-4455-6677 (Keluarga)"
                           class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                </div>

                <!-- 7. Bukti Dokumen / Foto Pendukung (Maksimal 2 Foto, 1 Tempat Upload Sarpras-Style) -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-bold text-slate-800">
                                Foto Bukti / Dokumen Pendukung
                            </label>
                            <p class="text-[10px] text-slate-400">Surat dokter, undangan, atau berkas bukti (Maks 2 foto)</p>
                        </div>
                        <span id="label-foto-counter" class="text-[10px] font-bold text-primary-700 bg-primary-50 px-2.5 py-1 rounded-full border border-primary-200 shrink-0">
                            0 / 2 Foto
                        </span>
                    </div>

                    <!-- Single Upload Actions Box (Bisa Kamera / Galeri, otomatis dinonaktifkan jika sdh 2 foto) -->
                    <div id="container-cuti-photo-actions" class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="triggerCutiPhotoInput('camera')" class="py-2.5 px-3 rounded-2xl bg-white border border-slate-200/90 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs press-bounce transition-all">
                            <span class="text-sm">📷</span>
                            <span>Ambil Kamera</span>
                        </button>
                        <button type="button" onclick="triggerCutiPhotoInput('gallery')" class="py-2.5 px-3 rounded-2xl bg-white border border-slate-200/90 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs press-bounce transition-all">
                            <span class="text-sm">🖼️</span>
                            <span>Pilih Galeri</span>
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
                    <div id="banner-max-photos" class="hidden p-2.5 rounded-xl bg-slate-100 text-slate-600 text-[10.5px] font-semibold text-center border border-slate-200/80">
                        ✓ Maksimal 2 foto bukti telah dipilih. Hapus salah satu jika ingin mengganti.
                    </div>
                </div>

            </div>

            <!-- 3. FIXED FOOTER ACTION BUTTONS (shrink-0) -->
            <div class="px-5 py-3.5 pb-6 sm:pb-3.5 bg-white border-t border-slate-200 flex items-center gap-2.5 shrink-0 z-30 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] safe-bottom">
                <button type="button" onclick="closeModalPermohonanCuti()" class="w-1/3 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs text-center press-bounce transition-all">
                    Batal
                </button>
                <button type="button" onclick="submitPermohonanCuti(event)" id="btn-submit-cuti" class="w-2/3 py-3 rounded-2xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold text-xs text-center shadow-lg shadow-primary-600/30 press-bounce flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                    <span>💾</span>
                    <span id="btn-submit-text">Simpan Permohonan Cuti</span>
                </button>
            </div>

        </form>
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

    // Inisialisasi awal saat view dimuat
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    });

    // Pastikan modal cuti terpasang langsung di document.body agar tidak tertutup elemen parent atau nav bawah
    function mountCutiModalElement() {
        const id = 'modal-permohonan-cuti';
        const els = document.querySelectorAll('#' + id);
        if (!els.length) return null;
        let keeper = els[0];
        for (let i = 0; i < els.length; i++) {
            if (els[i].parentElement !== document.body) {
                keeper = els[i];
                break;
            }
        }
        els.forEach(el => {
            if (el !== keeper) el.remove();
        });
        if (keeper.parentElement !== document.body) {
            document.body.appendChild(keeper);
        }
        return keeper;
    }

    // Buka Modal Pengajuan Cuti
    function openModalPermohonanCuti() {
        mountCutiModalElement();
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
        void modal.offsetWidth;
        if (scrim) {
            scrim.classList.remove('opacity-0', 'pointer-events-none');
            scrim.classList.add('opacity-100', 'pointer-events-auto');
        }
        if (sheet) {
            sheet.classList.remove('translate-y-full');
        }
        document.body.classList.add('overflow-hidden');
    }

    // Tutup Modal Pengajuan Cuti
    function closeModalPermohonanCuti() {
        const modal = document.getElementById('modal-permohonan-cuti');
        const scrim = document.getElementById('modal-cuti-scrim');
        const sheet = document.getElementById('form-permohonan-cuti');

        // Kembalikan bottom navigation bar
        const bNav = document.getElementById('mobile-bottom-nav');
        if (bNav) bNav.style.display = '';

        if (scrim) {
            scrim.classList.remove('opacity-100', 'pointer-events-auto');
            scrim.classList.add('opacity-0', 'pointer-events-none');
        }
        if (sheet) {
            sheet.classList.add('translate-y-full');
        }
        document.body.classList.remove('overflow-hidden');

        setTimeout(() => {
            if (modal && sheet && sheet.classList.contains('translate-y-full')) {
                modal.classList.add('hidden');
            }
        }, 250);
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

        const jenisId = document.getElementById('modal_jenis_cuti_id')?.value || '';
        const substitute = document.getElementById('modal_pelimpahan_tugas')?.value || '';
        const keteranganTugas = (document.getElementById('modal_keterangan_tugas')?.value || '').trim();
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

        if (!keteranganTugas) {
            showCutiToast('Mohon isi keterangan tugas yang dilimpahkan kepada rekan pengganti.', 'warning');
            document.getElementById('modal_keterangan_tugas')?.focus();
            return;
        }

        if (!alasan) {
            showCutiToast('Mohon isi alasan / keperluan cuti.', 'warning');
            document.getElementById('modal_alasan')?.focus();
            return;
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

        if (btnSubmit) btnSubmit.disabled = true;
        if (btnText) btnText.textContent = 'Menyimpan Permohonan...';

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
                        title: 'Permohonan Cuti Terkirim! 🏖️✓',
                        subtitle: 'Nomor: ' + (json.nomor_pengajuan || 'Tersimpan'),
                        message: json.message || 'Permohonan cuti Anda berhasil diajukan dan sedang menunggu verifikasi.',
                        buttonText: 'OK',
                        onClick: () => {
                            window.location.reload();
                        }
                    });
                } else {
                    alert(json.message || 'Permohonan cuti berhasil diajukan!');
                    window.location.reload();
                }
            } else {
                const msg = (json && json.message) || 'Gagal mengajukan cuti.';
                if (typeof AndroidUI !== 'undefined') {
                    AndroidUI.alert({
                        title: 'Gagal Mengajukan Cuti',
                        subtitle: 'Periksa Pengisian Form',
                        message: msg,
                        icon: '⚠️',
                        iconBg: 'bg-rose-100 text-rose-700'
                    });
                } else {
                    alert(msg);
                }
            }
        } catch (err) {
            console.error('Submit cuti error:', err);
            showCutiToast('Terjadi kesalahan jaringan saat mengirim permohonan cuti.', 'error');
        } finally {
            if (btnSubmit) btnSubmit.disabled = false;
            if (btnText) btnText.textContent = 'Simpan Permohonan Cuti';
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
</script>
