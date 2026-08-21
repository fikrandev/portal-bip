<?php
/**
 * Kelas Diampu Screen
 * Shows PIC / Mendampingi stacked JP summary, and "Masuk" modal with Textarea (max 15 chars) and keyboard-aware "Simpan" button.
 */
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="font-bold text-slate-800 text-base">Kelas Diampu (6)</h2>
    </div>
    <a href="<?= url('mobile/absensi-kelas') ?>" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-2xl text-xs font-bold flex items-center gap-1 press-bounce">
        <i data-lucide="user-check" class="w-3.5 h-3.5"></i> Absen
    </a>
</div>

<div class="p-4 space-y-3.5">

    <!-- Search / Filter -->
    <div class="relative">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="text" id="filter-class" oninput="filterClasses()" placeholder="Cari nama kelas, PIC, atau jadwal..." class="w-full pl-10 pr-4 py-2.5 bg-white text-xs rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
    </div>

    <!-- Summary JP Chips (Stacked Vertically) -->
    <div class="grid grid-cols-2 gap-2.5">
        <!-- Card 1: Sebagai PIC -->
        <div class="bg-emerald-50 p-3 rounded-2xl border border-emerald-100 flex flex-col justify-between space-y-1">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <span class="font-bold text-emerald-800 text-xs">Sebagai PIC</span>
            </div>
            <p class="font-black text-emerald-700 text-sm leading-tight">4 Kelas (8 JP)</p>
        </div>

        <!-- Card 2: Mendampingi -->
        <div class="bg-blue-50 p-3 rounded-2xl border border-blue-100 flex flex-col justify-between space-y-1">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span class="font-bold text-blue-800 text-xs">Mendampingi</span>
            </div>
            <p class="font-black text-blue-700 text-sm leading-tight">2 Kelas (4 JP)</p>
        </div>
    </div>

    <!-- Class Cards List -->
    <div class="space-y-3" id="class-cards-container">
        <?php foreach ($classList as $k): ?>
        <div class="class-card bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3" data-name="<?= strtolower(e($k['name'] . ' ' . $k['role'] . ' ' . $k['schedule'])) ?>">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-<?= $k['color'] ?>-50 text-<?= $k['color'] ?>-600 flex items-center justify-center font-black text-sm border border-<?= $k['color'] ?>-100">
                        <?= substr($k['name'], 6) ?>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-sm"><?= e($k['name']) ?></h3>
                        <p class="text-[11px] text-slate-400">Wali: <?= e($k['homeroom']) ?></p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                    👥 <?= $k['totalStudents'] ?> Siswa
                </span>
            </div>

            <!-- Schedule & PIC / Mendampingi Badge with Green / Blue Background -->
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-blue-600"></i>
                    <span><?= e($k['schedule']) ?></span>
                </div>
                
                <?php if ($k['role'] === 'PIC'): ?>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        PIC | <?= $k['jp'] ?> JP
                    </span>
                <?php else: ?>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        Mendampingi | <?= $k['jp'] ?> JP
                    </span>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-50 text-center">
                <a href="<?= url('mobile/absensi-kelas?kelas=' . urlencode($k['name'])) ?>" class="py-2 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-bold flex items-center justify-center gap-1 press-bounce">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Absen
                </a>
                <button type="button" onclick="openPresensiMasukModal('<?= e($k['name']) ?>', '<?= e($k['role']) ?>', '<?= e($k['schedule']) ?>')" class="py-2 px-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-[11px] font-bold flex items-center justify-center gap-1 press-bounce">
                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Masuk
                </button>
                <a href="<?= url('mobile/murid?kelas=' . urlencode($k['name'])) ?>" class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold flex items-center justify-center gap-1 press-bounce">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i> Siswa
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
    function filterClasses() {
        const query = document.getElementById('filter-class').value.toLowerCase();
        document.querySelectorAll('.class-card').forEach(card => {
            const dataName = card.getAttribute('data-name');
            if (dataName.includes(query)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    /**
     * Android Bottom Sheet Modal for Presensi Masuk Kelas
     * Form uses Textarea with max 15 characters limit, autofocus, and "Simpan" button.
     */
    function openPresensiMasukModal(className, role, schedule) {
        AndroidUI.bottomSheet({
            title: `Masuk ${className}`,
            subtitle: `${role} • ${schedule}`,
            icon: '🏫',
            iconBg: 'bg-blue-100 text-blue-600',
            content: `
                <div class="space-y-2.5 pt-1 text-left">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700">Keterangan (Maks. 15 Karakter)</label>
                            <span id="char-counter" class="text-[10px] font-mono text-slate-400 font-bold">0/15</span>
                        </div>
                        <textarea id="input-keterangan-masuk" 
                                  maxlength="15" 
                                  rows="2"
                                  placeholder="Tulis keterangan singkat..." 
                                  oninput="updateCharCount(this)"
                                  class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner resize-none leading-relaxed"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Catatan singkat saat memulai pembelajaran di kelas.</p>
                    </div>
                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Simpan',
                    className: 'flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
                    autoClose: false,
                    onClick: function(e, btn) {
                        const input = document.getElementById('input-keterangan-masuk');
                        const note = input ? input.value.trim() : '';
                        const saveBtn = btn || document.querySelector('#android-ui-sheet-footer button:last-child');

                        if (saveBtn) {
                            AndroidUI.setButtonLoading(saveBtn, 'Menyimpan...');
                        }

                        setTimeout(() => {
                            AndroidUI.closeBottomSheet();

                            // Smooth Animated Checkmark Modal
                            AndroidUI.success({
                                title: `Presensi Masuk ${className}`,
                                subtitle: 'Berhasil tercatat di server',
                                message: `Anda telah memulai mengajar sebagai <strong>${role}</strong>.<br>Keterangan: "<strong>${note || 'Tepat Waktu'}</strong>"`,
                                buttonText: 'Selesai'
                            });
                        }, 600);
                    }
                }
            ]
        });

        // Autofocus textarea after bottom sheet finishes sliding up
        setTimeout(() => {
            const textarea = document.getElementById('input-keterangan-masuk');
            if (textarea) {
                textarea.focus();
            }
        }, 350);
    }

    function updateCharCount(el) {
        const counter = document.getElementById('char-counter');
        if (counter) {
            counter.textContent = `${el.value.length}/15`;
            if (el.value.length >= 15) {
                counter.className = 'text-[10px] font-mono text-rose-500 font-bold';
            } else {
                counter.className = 'text-[10px] font-mono text-slate-400 font-bold';
            }
        }
    }
</script>
