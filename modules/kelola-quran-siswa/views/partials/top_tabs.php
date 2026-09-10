<?php
/**
 * Shared Top Navigation Tab Bar for Qur'an Siswa Modules
 * Variables:
 * $activeJenjang: 'PAUD' | 'SD' | 'SMP_SMA' | 'HUB'
 * $counts: optional associative array ['PAUD' => x, 'SD' => y, 'SMP_SMA' => z]
 */
$activeTab = $activeJenjang ?? 'SD';
$cPaud = $counts['PAUD'] ?? 0;
$cSd = $counts['SD'] ?? 0;
$cSmpSma = $counts['SMP_SMA'] ?? 0;
?>

<div class="bg-white rounded-2xl border border-slate-200/80 p-2 shadow-xs mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        
        <!-- Navigation Pills -->
        <nav class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar p-0.5" aria-label="Pilihan Jenjang Qur'an Siswa">
            
            <!-- Hub Dashboard -->
            <a href="<?= url('kelola-quran-siswa') ?>" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'HUB' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                <svg class="w-4 h-4 <?= $activeTab === 'HUB' ? 'text-emerald-400' : 'text-slate-400' ?>" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span>Ringkasan Hub</span>
            </a>

            <!-- Divider -->
            <div class="h-5 w-px bg-slate-200 mx-1 hidden sm:block"></div>

            <!-- Tab 1: Qur'an Siswa PAUD -->
            <a href="<?= url('kelola-quran-siswa-paud') ?>" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'PAUD' ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/20' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50/70' ?>">
                <span class="text-sm">🧸</span>
                <span>Qur'an Siswa PAUD / TK</span>
                <?php if ($cPaud > 0): ?>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold <?= $activeTab === 'PAUD' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $cPaud ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Tab 2: Qur'an Siswa SD -->
            <a href="<?= url('kelola-quran-siswa-sd') ?>" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'SD' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50/70' ?>">
                <span class="text-sm">🎒</span>
                <span>Qur'an Siswa SD</span>
                <?php if ($cSd > 0): ?>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold <?= $activeTab === 'SD' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' ?>">
                        <?= $cSd ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Tab 3: Qur'an Siswa SMP & SMA -->
            <a href="<?= url('kelola-quran-siswa-smp-sma') ?>" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap <?= $activeTab === 'SMP_SMA' ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 hover:text-indigo-700 hover:bg-indigo-50/70' ?>">
                <span class="text-sm">🎓</span>
                <span>Qur'an Siswa SMP & SMA</span>
                <?php if ($cSmpSma > 0): ?>
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold <?= $activeTab === 'SMP_SMA' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-800' ?>">
                        <?= $cSmpSma ?>
                    </span>
                <?php endif; ?>
            </a>

        </nav>

        <!-- Right: Action shortcuts -->
        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <!-- Modal Button: Catat Setoran -->
            <button type="button" onclick="openModalSetoran('<?= $activeTab !== 'HUB' ? $activeTab : 'SD' ?>')" 
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Catat Setoran Baru</span>
            </button>

            <!-- Export / Print -->
            <a href="<?= url('kelola-quran-siswa/cetak?jenjang=' . ($activeTab !== 'HUB' ? $activeTab : 'SD')) ?>" target="_blank" 
               class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center gap-1.5" title="Cetak Rekap Mutaba'ah">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.327 2.45-6.079 5.28-6.079 2.83 0 5.52 2.752 5.28 6.079m-10.56 0A5.998 5.998 0 0 0 12 19.5c2.83 0 5.28-2.343 5.28-5.671m-10.56 0C6.72 10.5 9.17 8.157 12 8.157m0 0a5.998 5.998 0 0 1 5.28 5.672M6 20.25h12M9 3.75h6" />
                </svg>
                <span class="hidden md:inline">Cetak Rekap</span>
            </a>
        </div>

    </div>
</div>
