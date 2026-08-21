<?php
/**
 * Pencatatan Keterlambatan Siswa View (Mobile)
 * Records late student arrivals with reasons, minutes late, action/coaching taken, and history.
 */
?>

<!-- Top App Bar -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-black text-slate-900 text-base leading-tight">Keterlambatan Siswa</h2>
            <p class="text-[10px] text-slate-400 font-medium">Pencatatan & Pembinaan Siswa</p>
        </div>
    </div>

    <!-- Quick Add Button -->
    <button type="button" onclick="openTambahKeterlambatanModal()" class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center press-bounce shadow-xs" title="Catat Keterlambatan">
        <i data-lucide="plus" class="w-5 h-5"></i>
    </button>
</div>

<div class="p-4 space-y-3.5">

    <!-- 1. Metric Summary Cards (3 Columns) -->
    <div class="grid grid-cols-3 gap-2.5">
        
        <!-- Metric 1: Total Hari Ini -->
        <div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm text-center">
            <span id="stat-total-hari-ini" class="text-xl font-black text-rose-600 block leading-none"><?= $summary['todayCount'] ?? 5 ?></span>
            <span class="text-[10px] font-bold text-slate-700 mt-1 block">Hari Ini</span>
            <span class="text-[9px] text-slate-400">Siswa Terlambat</span>
        </div>

        <!-- Metric 2: Rata-rata Menit -->
        <div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm text-center">
            <span class="text-xl font-black text-amber-600 block leading-none"><?= $summary['avgMinutes'] ?? 18 ?><span class="text-xs">m</span></span>
            <span class="text-[10px] font-bold text-slate-700 mt-1 block">Rata-Rata</span>
            <span class="text-[9px] text-slate-400">Waktu Telat</span>
        </div>

        <!-- Metric 3: Kelas Terbanyak -->
        <div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-sm text-center">
            <span class="text-xs font-black text-slate-800 block truncate leading-tight"><?= $summary['mostClass'] ?? 'Kelas 8A' ?></span>
            <span class="text-[10px] font-bold text-slate-700 mt-1 block">Terbanyak</span>
            <span class="text-[9px] text-slate-400">Frekuensi</span>
        </div>

    </div>

    <!-- 2. Primary Action Button -->
    <button type="button" onclick="openTambahKeterlambatanModal()" class="w-full py-3.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-rose-600/30 flex items-center justify-center gap-2 press-bounce">
        <i data-lucide="user-x" class="w-4 h-4"></i>
        + Catat Keterlambatan Siswa
    </button>

    <!-- 3. Search & Filter Bar -->
    <div class="space-y-2">
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" 
                   id="search-keterlambatan" 
                   oninput="filterKeterlambatan()" 
                   placeholder="Cari nama siswa, NISN, atau kelas..." 
                   class="w-full pl-10 pr-4 py-2.5 bg-white text-xs rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-sm font-medium">
        </div>

        <!-- Class Filter Chips -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1">
            <button type="button" onclick="setKelasFilter('all')" id="chip-kelas-all" class="chip-kelas px-3 py-1 rounded-full text-[11px] font-bold bg-rose-600 text-white shadow-xs">Semua</button>
            <?php foreach ($classes as $cls): ?>
                <button type="button" onclick="setKelasFilter('<?= $cls ?>')" id="chip-kelas-<?= strtolower(str_replace(' ', '', $cls)) ?>" class="chip-kelas px-3 py-1 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200">
                    <?= $cls ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 4. Records List -->
    <div class="space-y-2.5 pt-1">
        <div class="flex items-center justify-between px-1 text-xs">
            <h3 class="font-bold text-slate-800 text-xs">Riwayat Keterlambatan</h3>
            <span id="records-count-text" class="text-[10px] text-slate-400 font-semibold"><?= count($records) ?> Catatan</span>
        </div>

        <div id="keterlambatan-list-container" class="space-y-2.5">
            <?php foreach ($records as $r): ?>
                <div class="record-card bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-2.5 transition-all" data-class="<?= strtolower(str_replace(' ', '', $r['class'])) ?>" data-search="<?= strtolower($r['studentName'] . ' ' . $r['nisn'] . ' ' . $r['class'] . ' ' . $r['reason']) ?>">
                    
                    <!-- Top row: Student Name & Minutes Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm shrink-0 border border-rose-100">
                                ⏳
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-900 text-xs truncate leading-tight"><?= htmlspecialchars($r['studentName']) ?></h4>
                                <p class="text-[10px] text-slate-400 font-semibold truncate mt-0.5">
                                    <span class="text-rose-700 font-bold"><?= htmlspecialchars($r['class']) ?></span> • NISN: <?= htmlspecialchars($r['nisn']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Minutes Late Badge -->
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-100 text-rose-800 shrink-0 border border-rose-200">
                            +<?= $r['minutesLate'] ?> Menit
                        </span>
                    </div>

                    <!-- Details Box -->
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 space-y-1.5 text-xs">
                        <div class="flex items-start gap-1.5 text-slate-700">
                            <span class="text-[11px] font-bold text-slate-400 shrink-0">Alasan:</span>
                            <span class="font-medium"><?= htmlspecialchars($r['reason']) ?></span>
                        </div>
                        <div class="flex items-start gap-1.5 text-slate-700">
                            <span class="text-[11px] font-bold text-slate-400 shrink-0">Tindakan:</span>
                            <span class="font-bold text-emerald-800"><?= htmlspecialchars($r['action']) ?></span>
                        </div>
                    </div>

                    <!-- Footer: Recorder & Date -->
                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-50">
                        <span class="flex items-center gap-1">
                            <i data-lucide="user-check" class="w-3 h-3 text-slate-400"></i> Oleh: <?= htmlspecialchars($r['recordedBy']) ?>
                        </span>
                        <span class="font-semibold text-slate-500"><?= htmlspecialchars($r['date']) ?>, <?= htmlspecialchars($r['time']) ?></span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
    // Sample student database for autocomplete
    const SAMPLE_STUDENTS = [
        { name: 'Ahmad Fadhil', nisn: '0082194821', class: 'Kelas 7A' },
        { name: 'Aisyah Putri', nisn: '0082194822', class: 'Kelas 7A' },
        { name: 'Bima Pratama', nisn: '0083921849', class: 'Kelas 8B' },
        { name: 'Chandra Wijaya', nisn: '0084920194', class: 'Kelas 8A' },
        { name: 'Dewi Anggraini', nisn: '0085910294', class: 'Kelas 9A' },
        { name: 'Fikran Maulana', nisn: '0085910295', class: 'Kelas 7B' },
        { name: 'Nabila Syakieb', nisn: '0085910296', class: 'Kelas 8B' },
        { name: 'Zaki Mubarak', nisn: '0085910297', class: 'Kelas 9B' }
    ];

    let activeKelasFilter = 'all';

    function setKelasFilter(cls) {
        activeKelasFilter = cls;
        document.querySelectorAll('.chip-kelas').forEach(b => {
            b.className = 'chip-kelas px-3 py-1 rounded-full text-[11px] font-bold bg-white text-slate-600 border border-slate-200';
        });

        const activeId = cls === 'all' ? 'chip-kelas-all' : `chip-kelas-${cls.toLowerCase().replace(/\s+/g, '')}`;
        const activeEl = document.getElementById(activeId);
        if (activeEl) {
            activeEl.className = 'chip-kelas px-3 py-1 rounded-full text-[11px] font-bold bg-rose-600 text-white shadow-xs';
        }

        filterKeterlambatan();
    }

    function filterKeterlambatan() {
        const q = (document.getElementById('search-keterlambatan')?.value || '').toLowerCase().trim();
        const targetClass = activeKelasFilter === 'all' ? 'all' : activeKelasFilter.toLowerCase().replace(/\s+/g, '');

        let visibleCount = 0;
        document.querySelectorAll('.record-card').forEach(card => {
            const cardClass = card.getAttribute('data-class');
            const cardSearch = card.getAttribute('data-search') || '';

            const matchesClass = (targetClass === 'all' || cardClass === targetClass);
            const matchesQuery = (!q || cardSearch.includes(q));

            if (matchesClass && matchesQuery) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        const countText = document.getElementById('records-count-text');
        if (countText) countText.textContent = `${visibleCount} Catatan`;
    }

    function openTambahKeterlambatanModal() {
        AndroidUI.bottomSheet({
            title: 'Catat Keterlambatan Siswa',
            subtitle: 'Form pencatatan jam kedatangan & pembinaan',
            icon: '⏱️',
            iconBg: 'bg-rose-100 text-rose-600',
            content: `
                <div class="space-y-3 pt-1 text-left">
                    
                    <!-- Pilih Kelas & Siswa -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelas <span class="text-rose-500">*</span></label>
                            <select id="input-late-class" onchange="onClassSelected(this.value)" class="w-full px-3 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-inner">
                                <option value="Kelas 7A">Kelas 7A</option>
                                <option value="Kelas 7B">Kelas 7B</option>
                                <option value="Kelas 8A">Kelas 8A</option>
                                <option value="Kelas 8B">Kelas 8B</option>
                                <option value="Kelas 9A">Kelas 9A</option>
                                <option value="Kelas 9B">Kelas 9B</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Menit Telat <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" id="input-late-minutes" value="15" min="1" max="180" class="w-full px-3 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-inner">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400">Menit</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Minutes Chips -->
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setMinutesQuick(10)" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700">+ 10m</button>
                        <button type="button" onclick="setMinutesQuick(15)" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700">+ 15m</button>
                        <button type="button" onclick="setMinutesQuick(20)" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700">+ 20m</button>
                        <button type="button" onclick="setMinutesQuick(30)" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700">+ 30m</button>
                        <button type="button" onclick="setMinutesQuick(45)" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700">+ 45m</button>
                    </div>

                    <!-- Nama Siswa (Input / Autocomplete) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Siswa <span class="text-rose-500">*</span></label>
                        <input type="text" id="input-late-student" placeholder="Ketik nama siswa..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-inner">
                    </div>

                    <!-- Alasan Keterlambatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Terlambat <span class="text-rose-500">*</span></label>
                        <select id="input-late-reason" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-inner">
                            <option value="Macet Lalu Lintas">Macet Lalu Lintas</option>
                            <option value="Bangun Kesiangan">Bangun Kesiangan</option>
                            <option value="Hujan Deras di Perjalanan">Hujan Deras di Perjalanan</option>
                            <option value="Kendaraan Mogok / Ban Bocor">Kendaraan Mogok / Ban Bocor</option>
                            <option value="Urusan Keluarga Mendesak">Urusan Keluarga Mendesak</option>
                            <option value="Lainnya">Lainnya / Tanpa Alasan Jelas</option>
                        </select>
                    </div>

                    <!-- Tindakan / Pembinaan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tindakan / Pembinaan</label>
                        <select id="input-late-action" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-inner">
                            <option value="Teguran Lisan & Doa">Teguran Lisan & Pembacaan Doa</option>
                            <option value="Tugas Tambahan / Piket">Tugas Tambahan / Piket Kebersihan</option>
                            <option value="Hafalan Surat Pendek">Hafalan Surat Pendek (Juz 30)</option>
                            <option value="Panggilan Orang Tua">Pemberitahuan / Panggilan Orang Tua</option>
                            <option value="Poin Pelanggaran">Pencatatan Poin Pelanggaran</option>
                            <option value="Diizinkan Langsung Masuk">Diizinkan Langsung Masuk Kelas</option>
                        </select>
                    </div>

                    <!-- Catatan Tambahan (Textarea) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea id="input-late-notes" rows="2" maxlength="100" placeholder="Keterangan singkat..." class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 shadow-inner resize-none"></textarea>
                    </div>

                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Simpan Catatan',
                    className: 'flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
                    autoClose: false,
                    onClick: (e, btn) => {
                        submitKeterlambatan(btn);
                    }
                }
            ]
        });

        setTimeout(() => {
            const studentInput = document.getElementById('input-late-student');
            if (studentInput) studentInput.focus();
        }, 350);
    }

    function setMinutesQuick(val) {
        const inp = document.getElementById('input-late-minutes');
        if (inp) inp.value = val;
    }

    function onClassSelected(cls) {
        // Pick student suggestion based on class
        const match = SAMPLE_STUDENTS.find(s => s.class === cls);
        const inp = document.getElementById('input-late-student');
        if (match && inp && !inp.value) {
            inp.value = match.name;
        }
    }

    function submitKeterlambatan(btn) {
        const studentName = document.getElementById('input-late-student')?.value.trim();
        const className = document.getElementById('input-late-class')?.value;
        const minutes = document.getElementById('input-late-minutes')?.value || 15;
        const reason = document.getElementById('input-late-reason')?.value;
        const action = document.getElementById('input-late-action')?.value;

        if (!studentName) {
            AndroidUI.toast('Mohon masukkan nama siswa!', 'warning');
            return;
        }

        AndroidUI.setButtonLoading(btn, 'Menyimpan...');

        setTimeout(() => {
            AndroidUI.resetButton(btn);
            AndroidUI.closeBottomSheet();

            // Insert new card to UI top
            const container = document.getElementById('keterlambatan-list-container');
            if (container) {
                const newCard = document.createElement('div');
                newCard.className = 'record-card bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-2.5 transition-all animate-bounce-short';
                newCard.setAttribute('data-class', className.toLowerCase().replace(/\s+/g, ''));
                newCard.setAttribute('data-search', `${studentName.toLowerCase()} ${className.toLowerCase()} ${reason.toLowerCase()}`);
                
                newCard.innerHTML = `
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm shrink-0 border border-rose-100">
                                ⏳
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-900 text-xs truncate leading-tight">${studentName}</h4>
                                <p class="text-[10px] text-slate-400 font-semibold truncate mt-0.5">
                                    <span class="text-rose-700 font-bold">${className}</span> • Baru Ditambahkan
                                </p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-100 text-rose-800 shrink-0 border border-rose-200">
                            +${minutes} Menit
                        </span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 space-y-1.5 text-xs">
                        <div class="flex items-start gap-1.5 text-slate-700">
                            <span class="text-[11px] font-bold text-slate-400 shrink-0">Alasan:</span>
                            <span class="font-medium">${reason}</span>
                        </div>
                        <div class="flex items-start gap-1.5 text-slate-700">
                            <span class="text-[11px] font-bold text-slate-400 shrink-0">Tindakan:</span>
                            <span class="font-bold text-emerald-800">${action}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-50">
                        <span class="flex items-center gap-1">
                            <i data-lucide="user-check" class="w-3 h-3 text-slate-400"></i> Oleh: Bu Rina, S.Pd
                        </span>
                        <span class="font-semibold text-slate-500">Baru Saja</span>
                    </div>
                `;

                container.prepend(newCard);
                if (window.lucide) window.lucide.createIcons();
            }

            AndroidUI.success({
                title: 'Tercatat Berhasil! ⏳✓',
                subtitle: 'Keterlambatan Siswa Tersimpan',
                message: `Data keterlambatan siswa <strong>${studentName}</strong> (${className}, +${minutes} menit) telah berhasil dicatat ke sistem.`,
                buttonText: 'Tutup'
            });

        }, 400);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
    });
</script>
