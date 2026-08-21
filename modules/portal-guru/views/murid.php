<?php
/**
 * Daftar Siswa (Murid) Screen
 * Compact mobile size with Center Data Loading and Android Bottom Sheet student profile.
 */
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="font-bold text-slate-800 text-base">Daftar Murid</h2>
    </div>
    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold">
        182 Siswa
    </span>
</div>

<div class="p-4 space-y-3.5">

    <!-- Search Input -->
    <div class="relative">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input type="text" id="search-student" oninput="filterStudents()" placeholder="Cari nama siswa atau NISN..." class="w-full pl-10 pr-4 py-2.5 bg-white text-xs rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
    </div>

    <!-- Class Filter Horizontal Chips -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
        <?php foreach ($classes as $idx => $c): ?>
        <button type="button" onclick="selectClassFilter('<?= e($c) ?>', this)" class="class-chip px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0 transition-all <?= $idx === 0 ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200' ?>">
            <?= e($c) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Student Cards List Container -->
    <div id="student-list-container" class="space-y-2.5">
        <?php foreach ($studentList as $s): ?>
        <div class="student-card bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 flex items-center justify-between gap-3" data-class="<?= e($s['class']) ?>" data-name="<?= strtolower(e($s['name'])) ?>">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr <?= $s['gender'] == 'L' ? 'from-blue-500 to-sky-400' : 'from-pink-500 to-rose-400' ?> text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                    <?= substr($s['name'], 0, 1) ?>
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-xs text-slate-800 truncate"><?= e($s['name']) ?></h4>
                    <p class="text-[10px] text-slate-400 font-mono">NISN: <?= e($s['nisn']) ?> • <span class="text-blue-600 font-semibold"><?= e($s['class']) ?></span></p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                            Kehadiran <?= e($s['presence']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex items-center gap-1.5 shrink-0">
                <a href="https://wa.me/<?= e($s['phone']) ?>" target="_blank" class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center press-bounce" title="Hubungi WhatsApp">
                    <i data-lucide="phone" class="w-4 h-4"></i>
                </a>
                <button type="button" onclick="showStudentBottomSheet('<?= e($s['name']) ?>', '<?= e($s['class']) ?>', '<?= e($s['nisn']) ?>', '<?= e($s['presence']) ?>', '<?= e($s['points']) ?>', '<?= e($s['gender']) ?>')" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center press-bounce" title="Detail Siswa">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
    let currentClassFilter = 'Semua';

    function selectClassFilter(className, btn) {
        currentClassFilter = className;
        document.querySelectorAll('.class-chip').forEach(b => {
            b.className = 'class-chip px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0 bg-white text-slate-600 border border-slate-200 transition-all';
        });
        btn.className = 'class-chip px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0 bg-blue-600 text-white shadow-sm transition-all';

        // Center screen loading indicator when filtering data
        AndroidUI.showCenterLoading(`Memuat data ${className}...`);
        setTimeout(() => {
            AndroidUI.hideCenterLoading();
            filterStudents();
        }, 200);
    }

    function filterStudents() {
        const query = document.getElementById('search-student').value.toLowerCase();
        document.querySelectorAll('.student-card').forEach(card => {
            const name = card.getAttribute('data-name');
            const studentClass = card.getAttribute('data-class');
            const matchQuery = name.includes(query);
            const matchClass = currentClassFilter === 'Semua' || studentClass === currentClassFilter;
            
            if (matchQuery && matchClass) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function showStudentBottomSheet(name, studentClass, nisn, presence, points, gender) {
        AndroidUI.bottomSheet({
            title: name,
            subtitle: `${studentClass} • NISN: ${nisn}`,
            icon: gender === 'L' ? '👦' : '👧',
            iconBg: gender === 'L' ? 'bg-blue-100 text-blue-600' : 'bg-pink-100 text-pink-600',
            content: `
                <div class="space-y-2.5 pt-1 text-left">
                    <div class="grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <p class="text-[10px] text-slate-400 font-medium">Tingkat Kehadiran</p>
                            <p class="font-black text-emerald-600 text-sm mt-0.5">${presence}</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <p class="text-[10px] text-slate-400 font-medium">Poin Kedisiplinan</p>
                            <p class="font-black text-blue-600 text-sm mt-0.5">${points} / 100</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs space-y-1">
                        <p class="text-slate-500">Jenis Kelamin: <strong>${gender === 'L' ? 'Laki-laki' : 'Perempuan'}</strong></p>
                        <p class="text-slate-500">Status Siswa: <strong class="text-emerald-700">Aktif & Terdaftar</strong></p>
                    </div>
                </div>
            `,
            actions: [
                {
                    text: 'Tutup',
                    className: 'w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl text-center'
                }
            ]
        });
    }
</script>
