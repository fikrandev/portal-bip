<?php
/**
 * Pengajuan Cuti Guru View (Mobile)
 * Features leave quotas, new leave application form, and multi-step approval verification timeline.
 */
?>

<!-- Top App Bar -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-black text-slate-900 text-base leading-tight">Pengajuan Cuti</h2>
            <p class="text-[10px] text-slate-400 font-medium">Manajemen Cuti & Kuota Tahunan</p>
        </div>
    </div>

    <!-- Right Action -->
    <button type="button" onclick="openFormCutiModal()" class="w-9 h-9 rounded-2xl bg-primary-50 text-primary-600 border border-primary-100 flex items-center justify-center press-bounce shadow-xs" title="Ajukan Cuti">
        <i data-lucide="plus" class="w-5 h-5"></i>
    </button>
</div>

<div class="p-4 space-y-3.5">

    <!-- 1. Leave Quota Summary Cards (3 Columns) -->
    <div class="space-y-1.5">
        <div class="flex items-center justify-between px-1 text-xs">
            <h3 class="font-bold text-slate-800 text-xs">Sisa Kuota Cuti Anda</h3>
            <span class="text-[10px] text-slate-400 font-semibold">Tahun Ajaran 2026</span>
        </div>

        <div class="grid grid-cols-3 gap-2.5">
            
            <!-- Quota 1: Cuti Tahunan -->
            <div class="bg-white rounded-3xl p-3.5 border border-slate-100 shadow-sm text-center space-y-1">
                <span class="text-xl font-black text-primary-600 block leading-none">
                    <?= $quotas['tahunan']['remaining'] ?? 10 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['tahunan']['total'] ?? 12 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block">Tahunan</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-primary-500 h-1 rounded-full" style="width: <?= round((($quotas['tahunan']['remaining'] ?? 10) / ($quotas['tahunan']['total'] ?? 12)) * 100) ?>%"></div>
                </div>
            </div>

            <!-- Quota 2: Cuti Sakit -->
            <div class="bg-white rounded-3xl p-3.5 border border-slate-100 shadow-sm text-center space-y-1">
                <span class="text-xl font-black text-primary-600 block leading-none">
                    <?= $quotas['sakit']['remaining'] ?? 14 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['sakit']['total'] ?? 14 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block">Sakit</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-primary-500 h-1 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <!-- Quota 3: Alasan Penting -->
            <div class="bg-white rounded-3xl p-3.5 border border-slate-100 shadow-sm text-center space-y-1">
                <span class="text-xl font-black text-purple-600 block leading-none">
                    <?= $quotas['alasan_penting']['remaining'] ?? 5 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['alasan_penting']['total'] ?? 5 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block">Penting</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-purple-500 h-1 rounded-full" style="width: 100%"></div>
                </div>
            </div>

        </div>
    </div>

    <!-- 2. Primary Action Button -->
    <button type="button" onclick="openFormCutiModal()" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-primary-600/30 flex items-center justify-center gap-2 press-bounce">
        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
        + Ajukan Permohonan Cuti Baru
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
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5"><?= htmlspecialchars($r['id']) ?> • <?= $r['daysCount'] ?> Hari Kerja</p>
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
                            <div class="flex items-center justify-between text-slate-700">
                                <span class="text-slate-400 font-semibold text-[11px]">Tanggal Cuti:</span>
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($r['startDate']) ?> s.d. <?= htmlspecialchars($r['endDate']) ?></span>
                            </div>
                            <div class="text-slate-700 pt-1 border-t border-slate-200/60">
                                <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Keperluan / Alasan:</span>
                                <p class="font-medium text-slate-800 leading-relaxed"><?= htmlspecialchars($r['reason']) ?></p>
                            </div>
                            <div class="flex items-center justify-between text-slate-700 pt-1 border-t border-slate-200/60">
                                <span class="text-slate-400 font-semibold text-[11px]">Pengganti Tugas:</span>
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($r['substitute']) ?></span>
                            </div>
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

                        <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-50 flex items-center justify-between">
                            <span>Diajukan: <?= htmlspecialchars($r['submittedAt']) ?></span>
                            <?php if (!empty($r['document'])): ?>
                                <span class="font-semibold text-primary-700 flex items-center gap-1">
                                    <i data-lucide="paperclip" class="w-3 h-3"></i> Dokumen Terlampir
                                </span>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    window.DAFTAR_JENIS_CUTI = <?= json_encode($daftarJenisCuti ?? []) ?>;
    window.TEACHERS_LIST = <?= json_encode(array_map(function($t) {
        return trim(($t['nama'] ?? '') . (!empty($t['gelar']) ? (', ' . $t['gelar']) : ''));
    }, $teachersList ?? [])) ?>;

    function openFormCutiModal() {
        const todayStr = new Date().toISOString().split('T')[0];

        // Buat options jenis cuti dinamis dari database
        let jenisOpts = '';
        if (Array.isArray(window.DAFTAR_JENIS_CUTI) && window.DAFTAR_JENIS_CUTI.length > 0) {
            window.DAFTAR_JENIS_CUTI.forEach((j, idx) => {
                const kuotaInfo = j.kuota_hari > 0 ? ` (Maks ${j.kuota_hari} hari)` : '';
                jenisOpts += `<option value="${j.id}" ${idx === 0 ? 'selected' : ''}>${j.nama_jenis}${kuotaInfo}</option>`;
            });
        } else {
            jenisOpts = '<option value="1">Cuti Tahunan</option>';
        }

        // Buat options guru pengganti
        let teacherOpts = '<option value="Guru Piket dan Tim Bidang">Guru Piket dan Tim Bidang</option>';
        if (Array.isArray(window.TEACHERS_LIST) && window.TEACHERS_LIST.length > 0) {
            window.TEACHERS_LIST.forEach(name => {
                if (name) teacherOpts += `<option value="${name}">${name}</option>`;
            });
        }

        AndroidUI.bottomSheet({
            title: 'Pengajuan Cuti Pegawai',
            subtitle: 'Isi formulir permohonan cuti resmi',
            icon: '🏖️',
            iconBg: 'bg-primary-100 text-primary-600',
            content: `
                <div class="space-y-3 pt-1 text-left">
                    
                    <!-- Jenis Cuti -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Cuti <span class="text-rose-500">*</span></label>
                        <select id="modal-cuti-type" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                            ${jenisOpts}
                        </select>
                    </div>

                    <!-- Rentang Tanggal Cuti -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" id="modal-cuti-start" value="${todayStr}" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" id="modal-cuti-end" value="${todayStr}" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                        </div>
                    </div>

                    <!-- Alasan Cuti (Textarea) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan / Keperluan Cuti <span class="text-rose-500">*</span></label>
                        <textarea id="modal-cuti-reason" rows="2" placeholder="Jelaskan alasan dan keperluan cuti..." class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner resize-none"></textarea>
                    </div>

                    <!-- Kontak / Alamat Selama Cuti -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Kontak Darurat</label>
                        <input type="text" id="modal-cuti-contact" placeholder="Contoh: 0812-4455-6677 (Suami/Istri)" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Selama Cuti</label>
                        <input type="text" id="modal-cuti-address" placeholder="Contoh: Jl. Merpati No. 12, Kota Palu" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                    </div>

                    <!-- Guru Pengganti / Pelimpahan Tugas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pelimpahan Tugas / Pengganti</label>
                        <select id="modal-cuti-substitute" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                            ${teacherOpts}
                        </select>
                    </div>

                    <!-- Lampiran Surat Pendukung -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dokumen Pendukung (Surat Dokter / Undangan)</label>
                        <input type="file" id="modal-cuti-file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                    </div>

                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Kirim Permohonan',
                    className: 'flex-1 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
                    autoClose: false,
                    onClick: (e, btn) => {
                        submitCuti(btn);
                    }
                }
            ]
        });

        setTimeout(() => {
            const reasonArea = document.getElementById('modal-cuti-reason');
            if (reasonArea) reasonArea.focus();
        }, 350);
    }

    async function submitCuti(btn) {
        const jenisId = document.getElementById('modal-cuti-type')?.value;
        const startDate = document.getElementById('modal-cuti-start')?.value || '';
        const endDate = document.getElementById('modal-cuti-end')?.value || startDate;
        const reason = document.getElementById('modal-cuti-reason')?.value.trim();
        const contact = document.getElementById('modal-cuti-contact')?.value.trim() || '';
        const address = document.getElementById('modal-cuti-address')?.value.trim() || '';
        const substitute = document.getElementById('modal-cuti-substitute')?.value || '';
        const fileInput = document.getElementById('modal-cuti-file');

        if (!jenisId) {
            AndroidUI.toast('Mohon pilih jenis cuti!', 'warning');
            return;
        }

        if (!reason) {
            AndroidUI.toast('Mohon isi alasan permohonan cuti!', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('jenis_cuti_id', jenisId);
        formData.append('tanggal_mulai', startDate);
        formData.append('tanggal_selesai', endDate);
        formData.append('alasan', reason);
        formData.append('kontak_darurat', contact);
        formData.append('alamat_selama_cuti', address);
        formData.append('pelimpahan_tugas', substitute);

        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('lampiran', fileInput.files[0]);
        }

        AndroidUI.setButtonLoading(btn, 'Mengajukan Cuti...');

        try {
            const res = await fetch('<?= url("mobile/cuti/submit") ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            AndroidUI.resetButton(btn);

            if (data.success) {
                AndroidUI.closeBottomSheet();
                AndroidUI.success({
                    title: 'Permohonan Cuti Terkirim! 🏖️✓',
                    subtitle: 'Nomor: ' + (data.nomor_pengajuan || 'Tersimpan'),
                    message: data.message || 'Permohonan cuti Anda berhasil diajukan dan sedang menunggu verifikasi.',
                    buttonText: 'OK',
                    onClick: () => {
                        window.location.reload();
                    }
                });
            } else {
                AndroidUI.toast(data.message || 'Gagal mengajukan cuti', 'error');
            }
        } catch (err) {
            AndroidUI.resetButton(btn);
            AndroidUI.toast('Terjadi kesalahan koneksi saat mengirim permohonan.', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
    });
</script>
