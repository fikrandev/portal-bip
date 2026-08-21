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
    <button type="button" onclick="openFormCutiModal()" class="w-9 h-9 rounded-2xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center press-bounce shadow-xs" title="Ajukan Cuti">
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
                <span class="text-xl font-black text-teal-600 block leading-none">
                    <?= $quotas['tahunan']['remaining'] ?? 10 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['tahunan']['total'] ?? 12 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block">Tahunan</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-teal-500 h-1 rounded-full" style="width: <?= round((($quotas['tahunan']['remaining'] ?? 10) / ($quotas['tahunan']['total'] ?? 12)) * 100) ?>%"></div>
                </div>
            </div>

            <!-- Quota 2: Cuti Sakit -->
            <div class="bg-white rounded-3xl p-3.5 border border-slate-100 shadow-sm text-center space-y-1">
                <span class="text-xl font-black text-blue-600 block leading-none">
                    <?= $quotas['sakit']['remaining'] ?? 14 ?><span class="text-xs text-slate-400 font-normal">/<?= $quotas['sakit']['total'] ?? 14 ?></span>
                </span>
                <span class="text-[10px] font-bold text-slate-800 block">Sakit</span>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 overflow-hidden">
                    <div class="bg-blue-500 h-1 rounded-full" style="width: 100%"></div>
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
    <button type="button" onclick="openFormCutiModal()" class="w-full py-3.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-teal-600/30 flex items-center justify-center gap-2 press-bounce">
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
            <?php foreach ($records as $r): ?>
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
                    
                    <!-- Header: Type & Status -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-bold shrink-0">
                                🏖️
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-900 text-xs truncate leading-tight"><?= htmlspecialchars($r['type']) ?></h4>
                                <p class="text-[10px] text-slate-400 font-semibold mt-0.5"><?= htmlspecialchars($r['id']) ?> • <?= $r['daysCount'] ?> Hari Kerja</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i> <?= htmlspecialchars($r['status']) ?>
                        </span>
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
                                        <div class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold shrink-0">
                                            ✓
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
                        <span class="font-semibold text-teal-700">Dokumen Lengkap</span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
    function openFormCutiModal() {
        const todayStr = new Date().toISOString().split('T')[0];

        AndroidUI.bottomSheet({
            title: 'Pengajuan Cuti Guru',
            subtitle: 'Isi formulir permohonan cuti resmi',
            icon: '🏖️',
            iconBg: 'bg-teal-100 text-teal-600',
            content: `
                <div class="space-y-3 pt-1 text-left">
                    
                    <!-- Jenis Cuti -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Cuti <span class="text-rose-500">*</span></label>
                        <select id="modal-cuti-type" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500 shadow-inner">
                            <option value="Cuti Tahunan">Cuti Tahunan (Sisa 10 Hari)</option>
                            <option value="Cuti Sakit">Cuti Sakit (> 3 Hari / Rawat Inap)</option>
                            <option value="Cuti Alasan Penting">Cuti Alasan Penting / Keluarga</option>
                            <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                            <option value="Cuti Ibadah Keagamaan">Cuti Ibadah Keagamaan (Umrah / Haji)</option>
                            <option value="Cuti di Luar Tanggungan">Cuti di Luar Tanggungan</option>
                        </select>
                    </div>

                    <!-- Rentang Tanggal Cuti -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" id="modal-cuti-start" value="${todayStr}" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500 shadow-inner">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" id="modal-cuti-end" value="${todayStr}" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500 shadow-inner">
                        </div>
                    </div>

                    <!-- Alasan Cuti (Textarea) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan / Keperluan Cuti <span class="text-rose-500">*</span></label>
                        <textarea id="modal-cuti-reason" rows="2" placeholder="Jelaskan alasan dan keperluan cuti..." class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500 shadow-inner resize-none"></textarea>
                    </div>

                    <!-- Kontak / Alamat Selama Cuti -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Kontak Darurat & Alamat Selama Cuti</label>
                        <input type="text" id="modal-cuti-contact" placeholder="Contoh: 0812-4455-6677 (Jl. Merpati No. 12)" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500 shadow-inner">
                    </div>

                    <!-- Guru Pengganti / Pelimpahan Tugas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pelimpahan Tugas Mengajar</label>
                        <select id="modal-cuti-substitute" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500 shadow-inner">
                            <option value="Pak Hendra, M.Pd & Bu Fitri, S.Pd">Pak Hendra, M.Pd & Bu Fitri, S.Pd</option>
                            <option value="Pak Ridwan, S.Kom">Pak Ridwan, S.Kom</option>
                            <option value="Guru Piket dan MGMP Matematika">Guru Piket dan Tim MGMP Matematika</option>
                        </select>
                    </div>

                    <!-- Lampiran Surat Pendukung -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dokumen Pendukung (Surat Dokter / Undangan)</label>
                        <input type="file" id="modal-cuti-file" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
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
                    className: 'flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
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

    function submitCuti(btn) {
        const type = document.getElementById('modal-cuti-type')?.value;
        const reason = document.getElementById('modal-cuti-reason')?.value.trim();
        const substitute = document.getElementById('modal-cuti-substitute')?.value;

        if (!reason) {
            AndroidUI.toast('Mohon isi alasan permohonan cuti!', 'warning');
            return;
        }

        AndroidUI.setButtonLoading(btn, 'Mengajukan Cuti...');

        setTimeout(() => {
            AndroidUI.resetButton(btn);
            AndroidUI.closeBottomSheet();

            // Add new card to UI
            const container = document.getElementById('cuti-list-container');
            if (container) {
                const newId = 'CT-2026-' + Math.floor(100 + Math.random() * 900);
                const newCard = document.createElement('div');
                newCard.className = 'bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3 transition-all animate-bounce-short';
                
                newCard.innerHTML = `
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-bold shrink-0">
                                🏖️
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-900 text-xs truncate leading-tight">${type}</h4>
                                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">${newId} • Menunggu Verifikasi</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 shrink-0 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> Diajukan
                        </span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 space-y-2 text-xs">
                        <div class="text-slate-700">
                            <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Keperluan:</span>
                            <p class="font-medium text-slate-800 leading-relaxed">${reason}</p>
                        </div>
                        <div class="flex items-center justify-between text-slate-700 pt-1 border-t border-slate-200/60">
                            <span class="text-slate-400 font-semibold text-[11px]">Pengganti:</span>
                            <span class="font-bold text-slate-800">${substitute}</span>
                        </div>
                    </div>
                    <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-50 flex items-center justify-between">
                        <span>Diajukan: Baru Saja</span>
                        <span class="font-bold text-amber-600">Verifikasi Wakasek</span>
                    </div>
                `;

                container.prepend(newCard);
                if (window.lucide) window.lucide.createIcons();
            }

            AndroidUI.success({
                title: 'Permohonan Cuti Terkirim! 🏖️✓',
                subtitle: 'Menunggu Verifikasi Wakasek & Kepala Sekolah',
                message: `Permohonan <strong>${type}</strong> Anda telah berhasil didaftarkan dan sedang dalam proses verifikasi pimpinan.`,
                buttonText: 'Tutup'
            });

        }, 450);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
    });
</script>
