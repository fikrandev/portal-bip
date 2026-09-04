<?php
/**
 * Reusable Kaldik Monthly Calendar Matrix View Component
 * 
 * Variables:
 * @var array $agendas
 * @var string $nama_tahun (e.g. '2026/2027')
 * @var string $semester ('Ganjil', 'Genap', or 'Semua')
 * @var bool $is_print (optional)
 */

$isPrint = $is_print ?? false;
$activeSemester = $semester ?? 'Ganjil';

// Parse start & end year from $nama_tahun
$startYear = (int)date('Y');
$endYear = $startYear + 1;
if (!empty($nama_tahun)) {
    if (preg_match('/(\d{4})\s*[\/-]\s*(\d{4})/', $nama_tahun, $m)) {
        $startYear = (int)$m[1];
        $endYear = (int)$m[2];
    } elseif (preg_match('/(\d{4})/', $nama_tahun, $m)) {
        $startYear = (int)$m[1];
        $endYear = $startYear + 1;
    }
}

// 12 Months Definition (Standard Academic Year: July to June)
$allMonths = [
    // Semester Ganjil
    ['m' => 7,  'y' => $startYear, 'name' => 'Juli', 'smt' => 'Ganjil'],
    ['m' => 8,  'y' => $startYear, 'name' => 'Agustus', 'smt' => 'Ganjil'],
    ['m' => 9,  'y' => $startYear, 'name' => 'September', 'smt' => 'Ganjil'],
    ['m' => 10, 'y' => $startYear, 'name' => 'Oktober', 'smt' => 'Ganjil'],
    ['m' => 11, 'y' => $startYear, 'name' => 'November', 'smt' => 'Ganjil'],
    ['m' => 12, 'y' => $startYear, 'name' => 'Desember', 'smt' => 'Ganjil'],
    // Semester Genap
    ['m' => 1,  'y' => $endYear,   'name' => 'Januari', 'smt' => 'Genap'],
    ['m' => 2,  'y' => $endYear,   'name' => 'Februari', 'smt' => 'Genap'],
    ['m' => 3,  'y' => $endYear,   'name' => 'Maret', 'smt' => 'Genap'],
    ['m' => 4,  'y' => $endYear,   'name' => 'April', 'smt' => 'Genap'],
    ['m' => 5,  'y' => $endYear,   'name' => 'Mei', 'smt' => 'Genap'],
    ['m' => 6,  'y' => $endYear,   'name' => 'Juni', 'smt' => 'Genap'],
];

// Helper to find agenda for a specific date (YYYY-MM-DD)
$getAgendaForDate = function(string $dateStr) use ($agendas) {
    if (empty($agendas)) return null;
    foreach ($agendas as $ag) {
        $tglMulai = $ag['tanggal_mulai'] ?? '';
        $tglSelesai = !empty($ag['tanggal_selesai']) ? $ag['tanggal_selesai'] : $tglMulai;
        if (!empty($tglMulai)) {
            if ($dateStr >= $tglMulai && $dateStr <= $tglSelesai) {
                return $ag;
            }
        }
    }
    return null;
};

// Category styling map
$categoryStyles = [
    'kbm' => [
        'bg' => 'bg-emerald-500 text-white font-bold shadow-sm',
        'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'print_bg' => 'background-color: #10b981 !important; color: #ffffff !important;',
        'label' => 'KBM Efektif',
        'icon' => '📚'
    ],
    'penilaian' => [
        'bg' => 'bg-amber-400 text-amber-950 font-bold shadow-sm ring-1 ring-amber-500/30',
        'badge' => 'bg-amber-100 text-amber-900 border-amber-300',
        'print_bg' => 'background-color: #fbbf24 !important; color: #000000 !important;',
        'label' => 'Penilaian / Asesmen (STS/SAS/PAT)',
        'icon' => '📝'
    ],
    'libur_nasional' => [
        'bg' => 'bg-rose-500 text-white font-bold shadow-sm',
        'badge' => 'bg-rose-100 text-rose-800 border-rose-300',
        'print_bg' => 'background-color: #ef4444 !important; color: #ffffff !important;',
        'label' => 'Libur Nasional / Cuti Bersama',
        'icon' => '🔴'
    ],
    'libur_semester' => [
        'bg' => 'bg-purple-500 text-white font-bold shadow-sm',
        'badge' => 'bg-purple-100 text-purple-800 border-purple-300',
        'print_bg' => 'background-color: #a855f7 !important; color: #ffffff !important;',
        'label' => 'Libur Semester / Akhir Tahun',
        'icon' => '🏖️'
    ],
    'kegiatan' => [
        'bg' => 'bg-sky-500 text-white font-bold shadow-sm',
        'badge' => 'bg-sky-100 text-sky-800 border-sky-300',
        'print_bg' => 'background-color: #0ea5e9 !important; color: #ffffff !important;',
        'label' => 'Kegiatan Khusus Sekolah (MPLS, dll)',
        'icon' => '🎯'
    ],
];
?>

<div class="kaldik-matrix-wrapper space-y-4">

    <?php if (!$isPrint): ?>
        <!-- Matrix Header Controls & Filter Semester -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5">
            <div class="flex items-center gap-2">
                <span class="text-base">🗓️</span>
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Matriks Kalender Pendidikan TP <?= e($nama_tahun) ?></h3>
                    <p class="text-[11px] text-slate-500">Visualisasi tanggal efektif & agenda per bulan</p>
                </div>
            </div>

            <!-- Semester Tab Switcher -->
            <div class="inline-flex p-1 bg-white rounded-xl border border-slate-200 shadow-sm text-xs font-bold" id="matrix-tab-controls">
                <button type="button" onclick="filterMatrixSemester('all')" class="matrix-tab-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900" data-tab="all">
                    1 Tahun Penuh (12 Bulan)
                </button>
                <button type="button" onclick="filterMatrixSemester('Ganjil')" class="matrix-tab-btn px-3 py-1.5 rounded-lg transition-all <?= ($activeSemester === 'Ganjil') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>" data-tab="Ganjil">
                    Semester Ganjil (Jul - Des)
                </button>
                <button type="button" onclick="filterMatrixSemester('Genap')" class="matrix-tab-btn px-3 py-1.5 rounded-lg transition-all <?= ($activeSemester === 'Genap') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>" data-tab="Genap">
                    Semester Genap (Jan - Jun)
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Legenda Warna Kategori -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-2 text-xs">
        <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Keterangan & Legenda Warna:</h4>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 pt-1">
            <?php foreach ($categoryStyles as $catKey => $catInfo): ?>
                <div class="flex items-center gap-2 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="w-4 h-4 rounded-md <?= $catInfo['bg'] ?> flex items-center justify-center text-[10px] flex-shrink-0"></span>
                    <span class="text-[11px] font-semibold text-slate-700 truncate"><?= e($catInfo['label']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="flex items-center gap-2 p-1.5 rounded-xl bg-slate-50 border border-slate-200/80">
                <span class="w-4 h-4 rounded-md bg-rose-100 text-rose-600 font-bold border border-rose-300 flex items-center justify-center text-[10px] flex-shrink-0">M</span>
                <span class="text-[11px] font-semibold text-slate-700 truncate">Hari Minggu (Libur)</span>
            </div>
        </div>
    </div>

    <!-- Monthly Grid Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="kaldik-months-container">
        <?php foreach ($allMonths as $mon): 
            $mNum = $mon['m'];
            $yNum = $mon['y'];
            $totalDays = (int)date('t', strtotime(sprintf('%04d-%02d-01', $yNum, $mNum)));
            $firstDayIdx = (int)date('w', strtotime(sprintf('%04d-%02d-01', $yNum, $mNum))); // 0 = Sun, 1 = Mon ...
            
            // Count effective and holiday days in this month
            $countEfektif = 0;
            $countLibur = 0;
            for ($d = 1; $d <= $totalDays; $d++) {
                $curDate = sprintf('%04d-%02d-%02d', $yNum, $mNum, $d);
                $curDayOfWeek = ($firstDayIdx + $d - 1) % 7;
                $ag = $getAgendaForDate($curDate);
                
                if ($curDayOfWeek === 0) {
                    $countLibur++;
                } elseif ($ag && in_array($ag['kategori'] ?? '', ['libur_nasional', 'libur_semester'])) {
                    $countLibur++;
                } else {
                    $countEfektif++;
                }
            }

            $isVisible = ($activeSemester === 'all' || $activeSemester === $mon['smt']);
        ?>
            <div class="kaldik-month-card bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden transition-all <?= $isVisible ? '' : 'hidden' ?>" data-semester="<?= $mon['smt'] ?>">
                <!-- Month Card Header -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white px-4 py-2.5 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wide">
                            <?= e($mon['name']) ?> <?= $yNum ?>
                        </h4>
                        <span class="text-[10px] text-emerald-100 font-medium">Semester <?= e($mon['smt']) ?></span>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-xs">
                            <?= $countEfektif ?> HE • <?= $countLibur ?> HL
                        </span>
                    </div>
                </div>

                <!-- Calendar Matrix Table -->
                <div class="p-3">
                    <table class="w-full text-center border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-extrabold text-slate-400">
                                <th class="py-1 text-rose-600">Min</th>
                                <th class="py-1">Sen</th>
                                <th class="py-1">Sel</th>
                                <th class="py-1">Rab</th>
                                <th class="py-1">Kam</th>
                                <th class="py-1">Jum</th>
                                <th class="py-1 text-slate-600">Sab</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php
                            $currentDay = 1;
                            $dayCounter = 0;
                            while ($currentDay <= $totalDays): ?>
                                <tr>
                                    <?php for ($col = 0; $col < 7; $col++): ?>
                                        <?php if ($dayCounter < $firstDayIdx || $currentDay > $totalDays): ?>
                                            <td class="p-1 text-slate-200 text-[11px]">-</td>
                                        <?php else: 
                                            $dateStr = sprintf('%04d-%02d-%02d', $yNum, $mNum, $currentDay);
                                            $isSunday = ($col === 0);
                                            $ag = $getAgendaForDate($dateStr);
                                            
                                            // Determine cell style
                                            $cellClass = 'text-slate-700 hover:bg-slate-100';
                                            $cellTitle = date('d F Y', strtotime($dateStr));
                                            
                                            if ($ag) {
                                                $cat = $ag['kategori'] ?? 'kegiatan';
                                                $style = $categoryStyles[$cat] ?? $categoryStyles['kegiatan'];
                                                $cellClass = $style['bg'];
                                                $cellTitle = "{$currentDay} " . $mon['name'] . " {$yNum}: " . $ag['kegiatan'] . " (" . ($style['label']) . ")" . (!empty($ag['keterangan']) ? " - " . $ag['keterangan'] : "");
                                            } elseif ($isSunday) {
                                                $cellClass = 'text-rose-600 bg-rose-50/70 font-semibold';
                                                $cellTitle = "{$currentDay} " . $mon['name'] . " {$yNum}: Hari Libur Akhir Pekan (Minggu)";
                                            }
                                        ?>
                                            <td class="p-0.5">
                                                <div title="<?= e($cellTitle) ?>" class="w-7 h-7 sm:w-8 sm:h-8 mx-auto rounded-xl flex items-center justify-center text-[11px] transition-transform hover:scale-110 cursor-pointer <?= $cellClass ?>">
                                                    <?= $currentDay ?>
                                                </div>
                                            </td>
                                            <?php $currentDay++; ?>
                                        <?php endif; ?>
                                        <?php $dayCounter++; ?>
                                    <?php endfor; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function filterMatrixSemester(targetTab) {
    const cards = document.querySelectorAll('.kaldik-month-card');
    cards.forEach(card => {
        const smt = card.getAttribute('data-semester');
        if (targetTab === 'all' || smt === targetTab) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });

    const btns = document.querySelectorAll('.matrix-tab-btn');
    btns.forEach(btn => {
        if (btn.getAttribute('data-tab') === targetTab) {
            btn.classList.add('bg-emerald-600', 'text-white', 'shadow-sm');
            btn.classList.remove('text-slate-600');
        } else {
            btn.classList.remove('bg-emerald-600', 'text-white', 'shadow-sm');
            btn.classList.add('text-slate-600');
        }
    });
}
</script>
