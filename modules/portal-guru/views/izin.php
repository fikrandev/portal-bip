<?php
/**
 * Pengajuan Izin Guru View (Mobile)
 * Supports Full-Day Absence (Izin Tidak Masuk) & Partial Class Leaving (Izin Keluar/Tidak Mengajar)
 */
?>

<!-- Top App Bar -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-black text-slate-900 text-base leading-tight">Pengajuan Izin</h2>
            <p class="text-[10px] text-slate-400 font-medium">Izin Tidak Masuk & Keluar Mengajar</p>
        </div>
    </div>

    <!-- Right Quick Badge -->
    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
        <?= $summary['pending'] ?? 1 ?> Menunggu
    </span>
</div>

<div class="p-4 space-y-3.5">

    <!-- 1. Quick Info Banner -->
    <div class="bg-gradient-to-r from-primary-700 via-indigo-700 to-primary-800 rounded-3xl p-4 text-white shadow-lg shadow-primary-700/20 relative overflow-hidden flex items-center justify-between">
        <div class="relative z-10 min-w-0">
            <p class="text-[10px] text-primary-200 font-bold uppercase tracking-wider">Layanan Kepegawaian</p>
            <h3 class="text-sm font-black mt-0.5">Pengajuan Izin Online</h3>
            <p class="text-[10px] text-primary-100/90 mt-0.5">Ajukan izin tidak masuk atau izin keluar sementara secara digital.</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl shrink-0 shadow-inner">
            📄
        </div>
    </div>

    <!-- 2. Dual Action Buttons (2 Jenis Izin) -->
    <div class="grid grid-cols-2 gap-2.5">
        
        <!-- Button 1: Izin Tidak Masuk (Full Day) -->
        <button type="button" onclick="openFormIzinModal('tidak_masuk')" class="bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 hover:border-amber-200 text-left transition-all press-bounce space-y-2 group">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                🏥
            </div>
            <div>
                <h4 class="font-black text-slate-900 text-xs leading-tight">Izin Tidak Masuk</h4>
                <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Sakit, Dinas, atau Keperluan Seharian</p>
            </div>
            <div class="flex items-center justify-between text-[10px] font-bold text-amber-700 pt-1 border-t border-slate-50">
                <span>+ Ajukan Izin</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </div>
        </button>

        <!-- Button 2: Izin Keluar Mengajar (Partial) -->
        <button type="button" onclick="openFormIzinModal('keluar_mengajar')" class="bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 hover:border-primary-200 text-left transition-all press-bounce space-y-2 group">
            <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                🚪
            </div>
            <div>
                <h4 class="font-black text-slate-900 text-xs leading-tight">Izin Keluar / Kelas</h4>
                <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Meninggalkan Jam Mengajar Tertentu</p>
            </div>
            <div class="flex items-center justify-between text-[10px] font-bold text-primary-700 pt-1 border-t border-slate-50">
                <span>+ Ajukan Izin</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </div>
        </button>

    </div>

    <!-- 3. Filter Tabs (Semua, Tidak Masuk, Keluar) -->
    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pt-1">
        <button type="button" onclick="setIzinTypeFilter('all')" id="tab-izin-all" class="tab-izin px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-primary-600 text-white shadow-xs">Semua Izin</button>
        <button type="button" onclick="setIzinTypeFilter('tidak_masuk')" id="tab-izin-tidak_masuk" class="tab-izin px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200">Izin Tidak Masuk</button>
        <button type="button" onclick="setIzinTypeFilter('keluar_mengajar')" id="tab-izin-keluar_mengajar" class="tab-izin px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200">Izin Keluar / Mengajar</button>
    </div>

    <!-- 4. Riwayat Pengajuan Izin List -->
    <div class="space-y-3 pt-1">
        <div class="flex items-center justify-between px-1 text-xs">
            <h3 class="font-bold text-slate-800 text-xs">Riwayat Pengajuan Izin</h3>
            <span class="text-[10px] text-slate-400 font-semibold"><?= count($records) ?> Pengajuan</span>
        </div>

        <div id="izin-list-container" class="space-y-3">
            <?php if (empty($records)): ?>
                <div id="izin-empty-state" class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm text-center space-y-3">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-amber-50 text-amber-600 flex items-center justify-center text-3xl">
                        📄
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Belum Ada Pengajuan Izin</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Anda belum memiliki riwayat pengajuan izin tidak masuk atau izin keluar. Klik tombol di atas untuk mengajukan permohonan baru.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                    <div class="izin-card bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3 transition-all" data-type="<?= $r['type'] ?>">
                        
                        <!-- Header Bar: Type & Status Badge -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-8 h-8 rounded-xl bg-<?= $r['badgeColor'] ?>-50 text-<?= $r['badgeColor'] ?>-700 flex items-center justify-center text-sm font-bold shrink-0">
                                    <?= $r['type'] === 'tidak_masuk' ? '🏥' : '🚪' ?>
                                </span>
                                <div class="min-w-0">
                                    <h4 class="font-black text-slate-900 text-xs truncate leading-tight"><?= htmlspecialchars($r['typeLabel']) ?></h4>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5"><?= htmlspecialchars($r['id']) ?></p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <?php if ($r['status'] === 'Disetujui'): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-800 shrink-0 flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3 h-3 text-primary-600"></i> Disetujui
                                </span>
                            <?php elseif ($r['status'] === 'Menunggu Persetujuan'): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 shrink-0 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> Menunggu
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 shrink-0 flex items-center gap-1">
                                    <i data-lucide="x-circle" class="w-3 h-3 text-rose-600"></i> Ditolak
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Information Details Box -->
                        <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 space-y-2 text-xs">
                            
                            <!-- Waktu & Durasi -->
                            <div class="flex items-center justify-between text-slate-700">
                                <span class="text-slate-400 font-semibold text-[11px]">Waktu:</span>
                                <span class="font-bold text-slate-800">
                                    <?= htmlspecialchars($r['startDate']) ?> <?= $r['startDate'] !== $r['endDate'] ? ' s.d. ' . htmlspecialchars($r['endDate']) : '' ?>
                                    <?= $r['timeRange'] ? ' (' . htmlspecialchars($r['timeRange']) . ')' : '' ?>
                                </span>
                            </div>

                            <?php if (!empty($r['classAffected'])): ?>
                                <div class="flex items-center justify-between text-slate-700">
                                    <span class="text-slate-400 font-semibold text-[11px]">Kelas Terdampak:</span>
                                    <span class="font-bold text-primary-700"><?= htmlspecialchars($r['classAffected']) ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Alasan -->
                            <div class="text-slate-700 pt-1 border-t border-slate-200/60">
                                <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Alasan:</span>
                                <p class="font-medium text-slate-800 leading-relaxed"><?= htmlspecialchars($r['reason']) ?></p>
                            </div>

                            <?php if (!empty($r['taskGiven'])): ?>
                                <div class="text-slate-700 pt-1 border-t border-slate-200/60">
                                    <span class="text-slate-400 font-semibold text-[11px] block mb-0.5">Tugas untuk Siswa:</span>
                                    <p class="font-semibold text-primary-800 leading-relaxed"><?= htmlspecialchars($r['taskGiven']) ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- Guru Pengganti -->
                            <div class="flex items-center justify-between text-slate-700 pt-1 border-t border-slate-200/60">
                                <span class="text-slate-400 font-semibold text-[11px]">Guru Pengganti:</span>
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($r['substituteTeacher']) ?></span>
                            </div>

                        </div>

                        <!-- Card Footer: Submitted Time -->
                        <div class="flex items-center justify-between text-[10px] text-slate-400 px-1">
                            <span>Diajukan: <?= htmlspecialchars($r['submittedAt']) ?></span>
                            <?php if (!empty($r['document'])): ?>
                                <span class="font-bold text-primary-600 flex items-center gap-1">
                                    <i data-lucide="paperclip" class="w-3 h-3"></i> Lampiran Terlampir
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
    let activeIzinFilter = 'all';
    window.TEACHERS_LIST = <?= json_encode(array_map(function($t) {
        return trim(($t['nama'] ?? '') . (!empty($t['gelar']) ? (', ' . $t['gelar']) : ''));
    }, $teachersList ?? [])) ?>;

    function setIzinTypeFilter(type) {
        activeIzinFilter = type;
        document.querySelectorAll('.tab-izin').forEach(b => {
            b.className = 'tab-izin px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200';
        });

        const activeBtn = document.getElementById(`tab-izin-${type}`);
        if (activeBtn) {
            activeBtn.className = 'tab-izin px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-primary-600 text-white shadow-xs';
        }

        document.querySelectorAll('.izin-card').forEach(card => {
            const cardType = card.getAttribute('data-type');
            if (type === 'all' || cardType === type) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function openFormIzinModal(type) {
        const isFullDay = type === 'tidak_masuk';
        const todayStr = new Date().toISOString().split('T')[0];

        let teacherOpts = '<option value="Guru Piket yang Bertugas">Guru Piket yang Bertugas Hari Ini</option>';
        if (Array.isArray(window.TEACHERS_LIST) && window.TEACHERS_LIST.length > 0) {
            window.TEACHERS_LIST.forEach(name => {
                if (name) teacherOpts += `<option value="${name}">${name}</option>`;
            });
        }

        AndroidUI.bottomSheet({
            title: isFullDay ? 'Izin Tidak Masuk (Full Day)' : 'Izin Keluar / Tidak Mengajar',
            subtitle: isFullDay ? 'Sakit, Dinas Luar, atau Keperluan Pribadi' : 'Meninggalkan sekolah pada jam pelajaran tertentu',
            icon: isFullDay ? '🏥' : '🚪',
            iconBg: isFullDay ? 'bg-amber-100 text-amber-600' : 'bg-primary-100 text-primary-600',
            content: `
                <div class="space-y-3 pt-1 text-left">
                    
                    ${isFullDay ? `
                        <!-- Kategori Izin Tidak Masuk -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Izin <span class="text-rose-500">*</span></label>
                            <select id="modal-izin-kategori" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-inner">
                                <option value="Sakit">Sakit (Dengan / Tanpa Surat Dokter)</option>
                                <option value="Dinas Luar">Dinas Luar / Pelatihan / Tugas Sekolah</option>
                                <option value="Keperluan Keluarga">Keperluan Keluarga / Mendesak</option>
                                <option value="Lainnya">Lain-lain</option>
                            </select>
                        </div>

                        <!-- Rentang Tanggal -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Mulai <span class="text-rose-500">*</span></label>
                                <input type="date" id="modal-izin-start" value="${todayStr}" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-inner">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Sampai <span class="text-rose-500">*</span></label>
                                <input type="date" id="modal-izin-end" value="${todayStr}" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-inner">
                            </div>
                        </div>
                    ` : `
                        <!-- Izin Keluar Sementara -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jam Keluar <span class="text-rose-500">*</span></label>
                                <input type="time" id="modal-izin-time-out" value="10:00" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Kembali</label>
                                <input type="time" id="modal-izin-time-in" value="12:00" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                            </div>
                        </div>

                        <!-- Kelas & Jam Pelajaran Terdampak -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelas & Jam Mengajar Ditinggalkan <span class="text-rose-500">*</span></label>
                            <input type="text" id="modal-izin-class-impact" placeholder="Contoh: Kelas 7A (Jam ke 1-2)" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner">
                        </div>

                        <!-- Tugas untuk Siswa -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tugas / Materi Titipan Siswa</label>
                            <textarea id="modal-izin-task" rows="2" placeholder="Tuliskan tugas yang harus dikerjakan siswa selama ditinggal..." class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-inner resize-none"></textarea>
                        </div>
                    `}

                    <!-- Alasan Lengkap (Textarea) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Izin <span class="text-rose-500">*</span></label>
                        <textarea id="modal-izin-reason" rows="2" placeholder="Tuliskan alasan pengajuan izin secara jelas..." class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-${isFullDay ? 'amber' : 'blue'}-500 shadow-inner resize-none"></textarea>
                    </div>

                    <!-- Guru Pengganti / Pendamping -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Guru Pengganti / Guru Piket</label>
                        <select id="modal-izin-substitute" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-${isFullDay ? 'amber' : 'blue'}-500 shadow-inner">
                            ${teacherOpts}
                        </select>
                    </div>

                    <!-- Upload Dokumen / Surat Dokter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lampiran Dokumen / Surat (Opsional)</label>
                        <div class="flex items-center gap-2">
                            <input type="file" id="modal-izin-file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        </div>
                    </div>

                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Kirim Pengajuan',
                    className: `flex-1 py-3 ${isFullDay ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/20' : 'bg-primary-600 hover:bg-primary-700 shadow-primary-600/20'} text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce`,
                    autoClose: false,
                    onClick: (e, btn) => {
                        submitIzin(type, btn);
                    }
                }
            ]
        });

        setTimeout(() => {
            const reasonArea = document.getElementById('modal-izin-reason');
            if (reasonArea) reasonArea.focus();
        }, 350);
    }

    async function submitIzin(type, btn) {
        const isFullDay = type === 'tidak_masuk';
        const reason = document.getElementById('modal-izin-reason')?.value.trim();
        const substitute = document.getElementById('modal-izin-substitute')?.value || '';
        const fileInput = document.getElementById('modal-izin-file');

        if (!reason) {
            AndroidUI.toast('Mohon isi alasan pengajuan izin!', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('jenis_izin', type);
        formData.append('alasan', reason);
        formData.append('guru_pengganti', substitute);

        if (isFullDay) {
            const kategori = document.getElementById('modal-izin-kategori')?.value || 'Sakit';
            const startDate = document.getElementById('modal-izin-start')?.value || '';
            const endDate = document.getElementById('modal-izin-end')?.value || startDate;
            formData.append('kategori', kategori);
            formData.append('tanggal_mulai', startDate);
            formData.append('tanggal_selesai', endDate);
        } else {
            const timeOut = document.getElementById('modal-izin-time-out')?.value || '';
            const timeIn = document.getElementById('modal-izin-time-in')?.value || '';
            const classImpact = document.getElementById('modal-izin-class-impact')?.value || '';
            const task = document.getElementById('modal-izin-task')?.value || '';
            formData.append('jam_keluar', timeOut);
            formData.append('jam_kembali', timeIn);
            formData.append('kelas_terdampak', classImpact);
            formData.append('tugas_siswa', task);
            formData.append('tanggal_mulai', new Date().toISOString().split('T')[0]);
            formData.append('tanggal_selesai', new Date().toISOString().split('T')[0]);
        }

        if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('lampiran', fileInput.files[0]);
        }

        AndroidUI.setButtonLoading(btn, 'Mengirim Pengajuan...');

        try {
            const res = await fetch('<?= url("mobile/izin/submit") ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            AndroidUI.resetButton(btn);

            if (data.success) {
                AndroidUI.closeBottomSheet();
                AndroidUI.success({
                    title: 'Pengajuan Terkirim! 📄✓',
                    subtitle: 'Nomor: ' + (data.nomor_izin || 'Tersimpan'),
                    message: data.message || 'Pengajuan izin Anda berhasil dikirim ke pimpinan sekolah.',
                    buttonText: 'OK',
                    onClick: () => {
                        window.location.reload();
                    }
                });
            } else {
                AndroidUI.toast(data.message || 'Gagal mengirim pengajuan izin', 'error');
            }
        } catch (err) {
            AndroidUI.resetButton(btn);
            AndroidUI.toast('Terjadi kesalahan koneksi saat mengirim pengajuan.', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
    });
</script>
