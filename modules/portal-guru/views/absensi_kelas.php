<?php
/**
 * Absensi Siswa per Kelas Screen
 * Compact size with Button Loading and Smooth Animated SVG Checkmark Modal.
 */
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile/kelas') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-bold text-slate-800 text-base leading-tight">Absensi <?= e($selectedClass ?? 'Kelas 7A') ?></h2>
            <p class="text-[10px] text-slate-400"><?= date('d F Y') ?></p>
        </div>
    </div>
    <button onclick="setAllPresent()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold shadow-sm press-bounce flex items-center gap-1">
        ✓ Semua Hadir
    </button>
</div>

<div class="p-4 space-y-4">

    <!-- Counter Summary Bar -->
    <div class="grid grid-cols-4 gap-2 text-center bg-white rounded-3xl p-3 shadow-sm border border-slate-100">
        <div class="p-2 rounded-2xl bg-emerald-50 border border-emerald-100">
            <span id="count-h" class="text-base font-black text-emerald-700">8</span>
            <p class="text-[10px] font-bold text-emerald-600">Hadir</p>
        </div>
        <div class="p-2 rounded-2xl bg-blue-50 border border-blue-100">
            <span id="count-s" class="text-base font-black text-blue-700">1</span>
            <p class="text-[10px] font-bold text-blue-600">Sakit</p>
        </div>
        <div class="p-2 rounded-2xl bg-amber-50 border border-amber-100">
            <span id="count-i" class="text-base font-black text-amber-700">1</span>
            <p class="text-[10px] font-bold text-amber-600">Izin</p>
        </div>
        <div class="p-2 rounded-2xl bg-rose-50 border border-rose-100">
            <span id="count-a" class="text-base font-black text-rose-700">0</span>
            <p class="text-[10px] font-bold text-rose-600">Alpa</p>
        </div>
    </div>

    <!-- Student Attendance List -->
    <div class="space-y-2.5" id="attendance-students-list">
        <?php foreach ($students as $idx => $s): ?>
        <div class="bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                    <?= $idx + 1 ?>
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-xs text-slate-800 truncate"><?= e($s['name']) ?></h4>
                    <p class="text-[10px] text-slate-400 font-mono">NISN: <?= e($s['nisn']) ?></p>
                </div>
            </div>

            <!-- Status Pill Toggle Buttons -->
            <div class="flex items-center gap-1 shrink-0" data-student-id="<?= $s['id'] ?>">
                <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'H', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= $s['status'] === 'H' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">H</button>
                <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'S', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= $s['status'] === 'S' ? 'bg-blue-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">S</button>
                <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'I', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= $s['status'] === 'I' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">I</button>
                <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'A', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= $s['status'] === 'A' ? 'bg-rose-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">A</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Submit Attendance Button with Button Loading -->
    <div class="pt-2">
        <button type="button" id="btn-save-class-att" onclick="saveClassAttendance(this)" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 press-bounce">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            Simpan Absensi Kelas Ini
        </button>
    </div>

</div>

<script>
    function setStatus(studentId, status, btn) {
        const parent = btn.parentElement;
        parent.querySelectorAll('.btn-stat').forEach(b => {
            b.className = 'btn-stat w-7 h-7 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 transition-all';
        });

        const activeColors = {
            'H': 'bg-emerald-500 text-white shadow-sm',
            'S': 'bg-blue-500 text-white shadow-sm',
            'I': 'bg-amber-500 text-white shadow-sm',
            'A': 'bg-rose-500 text-white shadow-sm'
        };

        btn.className = `btn-stat w-7 h-7 rounded-xl text-xs font-bold ${activeColors[status]} transition-all`;
        updateCounts();
        if (navigator.vibrate) navigator.vibrate(20);
    }

    function setAllPresent() {
        document.querySelectorAll('[data-student-id]').forEach(container => {
            const hBtn = container.querySelector('.btn-stat:first-child');
            if (hBtn) {
                container.querySelectorAll('.btn-stat').forEach(b => {
                    b.className = 'btn-stat w-7 h-7 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 transition-all';
                });
                hBtn.className = 'btn-stat w-7 h-7 rounded-xl text-xs font-bold bg-emerald-500 text-white shadow-sm transition-all';
            }
        });
        updateCounts();
        AndroidUI.toast('Semua siswa disetel HADIR', 'success');
    }

    function updateCounts() {
        let h = 0, s = 0, i = 0, a = 0;
        document.querySelectorAll('[data-student-id]').forEach(container => {
            const active = container.querySelector('.btn-stat.text-white');
            if (active) {
                const text = active.textContent.trim();
                if (text === 'H') h++;
                if (text === 'S') s++;
                if (text === 'I') i++;
                if (text === 'A') a++;
            }
        });
        document.getElementById('count-h').textContent = h;
        document.getElementById('count-s').textContent = s;
        document.getElementById('count-i').textContent = i;
        document.getElementById('count-a').textContent = a;
    }

    function saveClassAttendance(btn) {
        const h = document.getElementById('count-h').textContent;
        const s = document.getElementById('count-s').textContent;
        const i = document.getElementById('count-i').textContent;
        const a = document.getElementById('count-a').textContent;

        AndroidUI.confirm({
            title: 'Simpan Rekap Presensi?',
            subtitle: 'Rekap kehadiran kelas <?= e($selectedClass ?? "Kelas 7A") ?>',
            icon: '📋',
            iconBg: 'bg-emerald-100 text-emerald-700',
            message: `Hadir: <strong>${h}</strong>, Sakit: <strong>${s}</strong>, Izin: <strong>${i}</strong>, Alpa: <strong>${a}</strong>. Simpan data ini ke server?`,
            confirmText: 'Ya, Simpan',
            cancelText: 'Batal',
            onConfirm: () => {
                // Button loading
                AndroidUI.setButtonLoading(btn, 'Menyimpan Presensi Kelas...');

                setTimeout(() => {
                    AndroidUI.resetButton(btn);

                    // Smooth animated checkmark
                    AndroidUI.success({
                        title: 'Presensi Kelas Disimpan!',
                        subtitle: '<?= e($selectedClass ?? "Kelas 7A") ?> • ' + new Date().toLocaleDateString('id-ID'),
                        message: `Rekapitulasi: <strong>${h} Hadir</strong>, <strong>${s} Sakit</strong>, <strong>${i} Izin</strong>, <strong>${a} Alpa</strong> telah terverifikasi.`,
                        actions: [
                            {
                                text: 'Kembali ke Kelas',
                                className: 'w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-md text-center',
                                onClick: () => window.location.href = '<?= url("mobile/kelas") ?>'
                            }
                        ]
                    });
                }, 700);
            }
        });
    }
</script>
