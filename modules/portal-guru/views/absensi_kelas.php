<?php
/**
 * Absensi Siswa per Kelas Screen
 * Sinkron dengan SK Penugasan Guru & Integrasi Langsung ke Pengisian Jurnal Mengajar.
 */
$selectedClassInfo = $classes[$selectedClass] ?? null;
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5 min-w-0">
        <a href="<?= url('mobile/kelas') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce shrink-0">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div class="min-w-0">
            <h2 class="font-bold text-slate-800 text-base leading-tight truncate">Absensi <?= e($selectedClass) ?></h2>
            <p class="text-[10px] text-slate-400 font-medium"><?= date('d F Y') ?> &bull; <?= count($students) ?> Siswa Terdaftar</p>
        </div>
    </div>
    <button onclick="setAllPresent()" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl text-xs font-bold shadow-sm press-bounce flex items-center gap-1 shrink-0">
        ✓ Semua Hadir
    </button>
</div>

<!-- Class Switcher Chips (Jika guru mengampu lebih dari 1 kelas) -->
<?php if (!empty($classes) && count($classes) > 1): ?>
<div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center gap-2 overflow-x-auto no-scrollbar">
    <span class="text-[11px] font-bold text-slate-400 shrink-0">Pilih Kelas:</span>
    <?php foreach ($classes as $cName => $cData): ?>
    <a href="<?= url('mobile/absensi-kelas?kelas=' . urlencode($cName)) ?>" 
       class="px-3 py-1 rounded-full text-xs font-bold shrink-0 transition-all <?= $cName === $selectedClass ? 'bg-primary-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200' ?>">
        <?= e($cName) ?>
        <?php if ($cData['is_wali'] ?? false): ?>
            <span class="text-[9px] ml-0.5 opacity-90">(Wali)</span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="p-4 space-y-4">

    <!-- Class Role Banner -->
    <?php if ($selectedClassInfo): ?>
    <div class="bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-700 font-black text-xs flex items-center justify-center shrink-0 border border-primary-100">
                <?= e($selectedClassInfo['level'] ?? '5') ?>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="font-bold text-xs text-slate-900 truncate"><?= e($selectedClass) ?></span>
                    <?php if ($selectedClassInfo['is_wali'] ?? false): ?>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800">Wali Kelas</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 text-blue-800">Guru Mapel</span>
                    <?php endif; ?>
                </div>
                <p class="text-[11px] text-slate-500 truncate mt-0.5">
                    Mapel: <strong><?= e($selectedClassInfo['subjectList'] ?? 'SbdP') ?></strong>
                </p>
            </div>
        </div>
        <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-xl shrink-0">
            👥 <?= count($students) ?> Siswa
        </span>
    </div>
    <?php endif; ?>

    <!-- Counter Summary Bar -->
    <div class="grid grid-cols-4 gap-2 text-center bg-white rounded-3xl p-3 shadow-sm border border-slate-100">
        <div class="p-2 rounded-2xl bg-emerald-50 border border-emerald-100">
            <span id="count-h" class="text-base font-black text-emerald-700">0</span>
            <p class="text-[10px] font-bold text-emerald-600">Hadir</p>
        </div>
        <div class="p-2 rounded-2xl bg-blue-50 border border-blue-100">
            <span id="count-s" class="text-base font-black text-blue-700">0</span>
            <p class="text-[10px] font-bold text-blue-600">Sakit</p>
        </div>
        <div class="p-2 rounded-2xl bg-amber-50 border border-amber-100">
            <span id="count-i" class="text-base font-black text-amber-700">0</span>
            <p class="text-[10px] font-bold text-amber-600">Izin</p>
        </div>
        <div class="p-2 rounded-2xl bg-rose-50 border border-rose-100">
            <span id="count-a" class="text-base font-black text-rose-700">0</span>
            <p class="text-[10px] font-bold text-rose-600">Alpa</p>
        </div>
    </div>

    <!-- Student Attendance List -->
    <div class="space-y-2.5" id="attendance-students-list">
        <?php if (empty($students)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">👥</div>
                <h4 class="font-bold text-sm text-slate-700">Belum Ada Data Siswa</h4>
                <p class="text-xs text-slate-400 mt-1">Siswa untuk kelas <?= e($selectedClass) ?> belum terdaftar di database.</p>
            </div>
        <?php else: ?>
            <?php foreach ($students as $idx => $s): ?>
            <div class="bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                        <?= $idx + 1 ?>
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-xs text-slate-800 truncate"><?= e($s['name']) ?></h4>
                        <p class="text-[10px] text-slate-400 font-mono">NISN: <?= e($s['nisn'] ?: '-') ?> &bull; <?= e($s['gender'] ?? 'L') ?></p>
                    </div>
                </div>

                <!-- Status Pill Toggle Buttons -->
                <div class="flex items-center gap-1 shrink-0 student-att-row" data-student-id="<?= $s['id'] ?>" data-student-name="<?= e($s['name']) ?>">
                    <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'H', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= ($s['status'] ?? 'H') === 'H' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">H</button>
                    <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'S', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= ($s['status'] ?? 'H') === 'S' ? 'bg-blue-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">S</button>
                    <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'I', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= ($s['status'] ?? 'H') === 'I' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">I</button>
                    <button type="button" onclick="setStatus(<?= $s['id'] ?>, 'A', this)" class="btn-stat w-7 h-7 rounded-xl text-xs font-bold transition-all <?= ($s['status'] ?? 'H') === 'A' ? 'bg-rose-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' ?>">A</button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Dual Action Buttons: Simpan Presensi & Simpan Lanjut Isi Jurnal -->
    <?php if (!empty($students)): ?>
    <div class="pt-2 space-y-2">
        <button type="button" 
                id="btn-save-and-journal" 
                onclick="saveClassAttendance(this, true)" 
                class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-primary-600/30 flex items-center justify-center gap-2 press-bounce transition-all">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>Simpan & Lanjut Isi Jurnal Mengajar</span>
        </button>

        <button type="button" 
                id="btn-save-only" 
                onclick="saveClassAttendance(this, false)" 
                class="w-full py-3 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-2xl border border-slate-200 flex items-center justify-center gap-1.5 press-bounce transition-all">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>Simpan Presensi Saja</span>
        </button>
    </div>
    <?php endif; ?>

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
        document.querySelectorAll('.student-att-row').forEach(container => {
            const hBtn = container.querySelector('.btn-stat:first-child');
            if (hBtn) {
                container.querySelectorAll('.btn-stat').forEach(b => {
                    b.className = 'btn-stat w-7 h-7 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 transition-all';
                });
                hBtn.className = 'btn-stat w-7 h-7 rounded-xl text-xs font-bold bg-emerald-500 text-white shadow-sm transition-all';
            }
        });
        updateCounts();
        AndroidUI.toast('Semua siswa disetel HADIR ✓', 'success');
    }

    function updateCounts() {
        let h = 0, s = 0, i = 0, a = 0;
        document.querySelectorAll('.student-att-row').forEach(container => {
            const active = container.querySelector('.btn-stat.text-white');
            if (active) {
                const text = active.textContent.trim();
                if (text === 'H') h++;
                if (text === 'S') s++;
                if (text === 'I') i++;
                if (text === 'A') a++;
            }
        });
        const elH = document.getElementById('count-h');
        const elS = document.getElementById('count-s');
        const elI = document.getElementById('count-i');
        const elA = document.getElementById('count-a');
        if (elH) elH.textContent = h;
        if (elS) elS.textContent = s;
        if (elI) elI.textContent = i;
        if (elA) elA.textContent = a;
    }

    function collectAttendanceMap() {
        const result = {};
        document.querySelectorAll('.student-att-row').forEach(container => {
            const sId = container.getAttribute('data-student-id');
            const active = container.querySelector('.btn-stat.text-white');
            result[sId] = active ? active.textContent.trim() : 'H';
        });
        return result;
    }

    function saveClassAttendance(btn, andGoToJournal = false) {
        const h = document.getElementById('count-h').textContent;
        const s = document.getElementById('count-s').textContent;
        const i = document.getElementById('count-i').textContent;
        const a = document.getElementById('count-a').textContent;

        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span> Menyimpan...`;

        const payload = new URLSearchParams();
        payload.append('kelas', <?= json_encode($selectedClass) ?>);
        payload.append('mata_pelajaran', <?= json_encode($selectedClassInfo['subjectList'] ?? 'SbdP') ?>);
        payload.append('tanggal', '<?= date('Y-m-d') ?>');
        payload.append('redirect_to_jurnal', andGoToJournal ? '1' : '0');

        const attMap = collectAttendanceMap();
        for (const [id, stat] of Object.entries(attMap)) {
            payload.append(`kehadiran[${id}]`, stat);
        }

        fetch('<?= url("mobile/absensi-kelas/simpan") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (res && res.success) {
                if (andGoToJournal && res.redirect_url) {
                    AndroidUI.toast('Presensi disimpan, membuka form jurnal...', 'success');
                    setTimeout(() => {
                        window.location.href = res.redirect_url;
                    }, 350);
                } else {
                    AndroidUI.success({
                        title: 'Presensi Kelas Disimpan!',
                        subtitle: '<?= e($selectedClass) ?> • ' + new Date().toLocaleDateString('id-ID'),
                        message: `Rekapitulasi: <strong>${h} Hadir</strong>, <strong>${s} Sakit</strong>, <strong>${i} Izin</strong>, <strong>${a} Alpa</strong> telah tercatat di server.`,
                        actions: [
                            {
                                text: 'Lanjut Isi Jurnal Mengajar',
                                className: 'w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-2xl shadow-md text-center',
                                onClick: () => {
                                    window.location.href = '<?= url("mobile/jurnal?kelas=" . urlencode($selectedClass) . "&mapel=" . urlencode($selectedClassInfo["subjectList"] ?? "SbdP")) ?>';
                                }
                            },
                            {
                                text: 'Kembali ke Daftar Kelas',
                                className: 'w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl text-center',
                                onClick: () => {
                                    window.location.href = '<?= url("mobile/kelas") ?>';
                                }
                            }
                        ]
                    });
                }
            } else {
                AndroidUI.toast(res.message || 'Gagal menyimpan presensi kelas.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            AndroidUI.toast('Terjadi kesalahan jaringan.', 'error');
        });
    }

    // Init count on page load
    document.addEventListener('DOMContentLoaded', () => {
        updateCounts();
    });
</script>
