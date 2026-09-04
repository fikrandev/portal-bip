<?php
/**
 * Reusable Searchable Guru Picker Component with Live Search & Auto-Select
 * 
 * Variables:
 * @var array $guru_list
 * @var array|null $logged_in_guru
 * @var int|null $selected_guru_id
 * @var string|null $selected_guru_nama
 * @var string|null $selected_guru_nip
 * @var string $picker_label (optional)
 * @var string $accent_color (optional, default: emerald)
 */

$label = $picker_label ?? 'Guru Pengampu / Penyusun Dokumen';
$accent = $picker_accent ?? 'emerald';

// Determine initial selected guru
$initialId = $selected_guru_id ?? null;
$initialNama = $selected_guru_nama ?? null;
$initialNip = $selected_guru_nip ?? null;

// If not yet set (e.g. create form), auto-select from logged-in user
if (empty($initialId) && empty($initialNama) && !empty($logged_in_guru)) {
    $initialId = $logged_in_guru['id'] ?? null;
    $initialNama = $logged_in_guru['nama'] ?? Auth::name() ?? '';
    $initialNip = $logged_in_guru['nip'] ?? '';
}

// If still empty, default to first in list
if (empty($initialId) && empty($initialNama) && !empty($guru_list)) {
    $initialId = $guru_list[0]['id'];
    $initialNama = $guru_list[0]['nama'];
    $initialNip = $guru_list[0]['nip'] ?? '';
}

// Find matched teacher object
$activeTeacher = null;
if (!empty($initialId)) {
    foreach ($guru_list as $g) {
        if ((int)$g['id'] === (int)$initialId) {
            $activeTeacher = $g;
            break;
        }
    }
}
if (!$activeTeacher && !empty($initialNama)) {
    $activeTeacher = [
        'id' => $initialId,
        'nama' => $initialNama,
        'nip' => $initialNip,
        'jabatan' => 'Guru Pengampu',
        'unit_tugas' => 'Sekolah'
    ];
}

$teacherUnit = !empty($activeTeacher['unit_tugas']) ? PerangkatModel::normalizeUnit($activeTeacher['unit_tugas']) : 'SD';

$isLoggedInUser = (!empty($logged_in_guru['id']) && $activeTeacher && (int)$logged_in_guru['id'] === (int)($activeTeacher['id'] ?? 0)) 
    || (!empty($logged_in_guru['nama']) && $activeTeacher && $logged_in_guru['nama'] === ($activeTeacher['nama'] ?? ''));

$accentClasses = [
    'emerald' => ['ring' => 'focus:ring-emerald-500', 'btn' => 'bg-emerald-600 hover:bg-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'border' => 'border-emerald-500', 'bg_soft' => 'bg-emerald-50'],
    'rose' => ['ring' => 'focus:ring-rose-500', 'btn' => 'bg-rose-600 hover:bg-rose-700', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300', 'border' => 'border-rose-500', 'bg_soft' => 'bg-rose-50'],
    'teal' => ['ring' => 'focus:ring-teal-500', 'btn' => 'bg-teal-600 hover:bg-teal-700', 'badge' => 'bg-teal-100 text-teal-800 border-teal-300', 'border' => 'border-teal-500', 'bg_soft' => 'bg-teal-50'],
    'cyan' => ['ring' => 'focus:ring-cyan-500', 'btn' => 'bg-cyan-600 hover:bg-cyan-700', 'badge' => 'bg-cyan-100 text-cyan-800 border-cyan-300', 'border' => 'border-cyan-500', 'bg_soft' => 'bg-cyan-50'],
    'indigo' => ['ring' => 'focus:ring-indigo-500', 'btn' => 'bg-indigo-600 hover:bg-indigo-700', 'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-300', 'border' => 'border-indigo-500', 'bg_soft' => 'bg-indigo-50'],
    'purple' => ['ring' => 'focus:ring-purple-500', 'btn' => 'bg-purple-600 hover:bg-purple-700', 'badge' => 'bg-purple-100 text-purple-800 border-purple-300', 'border' => 'border-purple-500', 'bg_soft' => 'bg-purple-50'],
][$accent] ?? ['ring' => 'focus:ring-emerald-500', 'btn' => 'bg-emerald-600 hover:bg-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'border' => 'border-emerald-500', 'bg_soft' => 'bg-emerald-50'];
?>

<div class="relative guru-searchable-picker space-y-2">
    <!-- Header / Label -->
    <div class="flex items-center justify-between">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <span>👨‍🏫</span>
            <span><?= e($label) ?></span>
            <span class="text-rose-500">*</span>
        </label>
        <?php if ($isLoggedInUser): ?>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Sesuai Akun Login
            </span>
        <?php endif; ?>
    </div>

    <!-- Selected Teacher Display Box (Trigger) -->
    <div onclick="toggleGuruDropdown(this)" class="group flex items-center justify-between p-3.5 sm:p-4 rounded-2xl border-2 border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm cursor-pointer transition-all">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center text-xl flex-shrink-0 transition-colors">
                👨‍🏫
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 id="display-guru-nama" class="text-xs sm:text-sm font-extrabold text-slate-800 truncate">
                        <?= e($activeTeacher['nama'] ?? 'Pilih Guru...') ?>
                    </h4>
                    <span id="display-guru-nip-badge" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-slate-100 text-slate-600 border border-slate-200 <?= empty($activeTeacher['nip']) ? 'hidden' : '' ?>">
                        NIP: <?= e($activeTeacher['nip'] ?? '') ?>
                    </span>
                </div>
                <p id="display-guru-sub" class="text-[11px] text-slate-400 mt-0.5 truncate">
                    <?= e($activeTeacher['jabatan'] ?? 'Guru Pengampu') ?> • <?= e($activeTeacher['unit_tugas'] ?? 'Satuan Pendidikan') ?>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0 pl-2">
            <span class="text-xs font-bold text-slate-500 group-hover:text-slate-800 hidden sm:inline-flex items-center gap-1">
                Ganti Guru
            </span>
            <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-slate-200 transition-colors">
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Hidden Form Inputs -->
    <input type="hidden" name="guru_id" id="guru_id" value="<?= e($activeTeacher['id'] ?? '') ?>">
    <input type="hidden" name="guru_nama" id="guru_nama" value="<?= e($activeTeacher['nama'] ?? Auth::name() ?? '') ?>">
    <input type="hidden" name="guru_nip" id="guru_nip" value="<?= e($activeTeacher['nip'] ?? '') ?>">

    <!-- Searchable Dropdown Menu -->
    <div id="guru-dropdown-menu" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-3xl border border-slate-200 shadow-2xl z-50 p-3 space-y-3 animate-in fade-in slide-in-from-top-2">
        <!-- Live Search Input Box -->
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 text-sm">
                🔍
            </span>
            <input type="text" id="guru-search-input" onkeyup="filterGuruLiveSearch(this.value)" placeholder="Ketik nama guru, NIP, atau jabatan untuk mencari..." class="w-full pl-10 pr-10 py-2.5 rounded-2xl border border-slate-200 text-xs bg-slate-50 focus:bg-white <?= $accentClasses['ring'] ?> focus:ring-2 focus:outline-none transition-all">
            <button type="button" onclick="clearGuruSearch()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 text-xs">
                ✕
            </button>
        </div>

        <!-- Teachers List Container -->
        <div id="guru-items-list" class="max-h-64 overflow-y-auto space-y-1.5 divide-y divide-slate-50 pr-1">
            <?php foreach ($guru_list as $g): 
                $isSelected = ($activeTeacher && (int)($activeTeacher['id'] ?? 0) === (int)$g['id']);
                $isSelf = (!empty($logged_in_guru['id']) && (int)$logged_in_guru['id'] === (int)$g['id']) || (!empty($logged_in_guru['nama']) && $logged_in_guru['nama'] === $g['nama']);
            ?>
                <div onclick="selectGuruItem(<?= htmlspecialchars(json_encode($g), ENT_QUOTES, 'UTF-8') ?>)" class="guru-item-row flex items-center justify-between p-2.5 rounded-2xl cursor-pointer transition-all <?= $isSelected ? $accentClasses['bg_soft'] . ' font-bold border ' . $accentClasses['border'] : 'hover:bg-slate-50 border border-transparent' ?>" data-search-text="<?= strtolower(e($g['nama'] . ' ' . ($g['nip'] ?? '') . ' ' . ($g['jabatan'] ?? '') . ' ' . ($g['unit_tugas'] ?? ''))) ?>">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm flex-shrink-0 <?= $isSelected ? 'bg-white shadow-sm' : 'bg-slate-100' ?>">
                            👨‍🏫
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-slate-800 truncate"><?= e($g['nama']) ?></span>
                                <?php if ($isSelf): ?>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Anda
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
                                <?php if (!empty($g['nip'])): ?>
                                    <span>NIP: <?= e($g['nip']) ?></span>
                                    <span>•</span>
                                <?php endif; ?>
                                <span><?= e($g['jabatan'] ?? 'Guru') ?> (<?= e($g['unit_tugas'] ?? 'Sekolah') ?>)</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($isSelected): ?>
                        <span class="text-xs text-emerald-600 font-bold px-2">✓ Terpilih</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Empty Search State -->
            <div id="guru-empty-search" class="hidden py-8 text-center text-slate-400">
                <span class="text-2xl">🔍</span>
                <p class="text-xs font-semibold text-slate-600 mt-1">Guru tidak ditemukan</p>
                <p class="text-[11px] text-slate-400">Coba gunakan kata kunci nama atau NIP lain.</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleGuruDropdown(trigger) {
    const menu = document.getElementById('guru-dropdown-menu');
    if (!menu) return;
    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
        menu.classList.remove('hidden');
        const input = document.getElementById('guru-search-input');
        if (input) {
            input.focus();
        }
    } else {
        menu.classList.add('hidden');
    }
}

function filterGuruLiveSearch(query) {
    const q = query.trim().toLowerCase();
    const rows = document.querySelectorAll('.guru-item-row');
    const emptyState = document.getElementById('guru-empty-search');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.getAttribute('data-search-text') || '';
        if (!q || text.includes(q)) {
            row.classList.remove('hidden');
            visibleCount++;
        } else {
            row.classList.add('hidden');
        }
    });

    if (emptyState) {
        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    }
}

function clearGuruSearch() {
    const input = document.getElementById('guru-search-input');
    if (input) {
        input.value = '';
        filterGuruLiveSearch('');
        input.focus();
    }
}

function syncUnitWithGuru(unitStr) {
    if (!unitStr) return;
    const u = String(unitStr).toUpperCase().trim();
    let targetUnit = null;
    if (u.includes('PAUD') || u.includes('TK') || u.includes('KB') || u.includes('RA')) targetUnit = 'PAUD';
    else if (u.includes('SD') || u.includes('MI')) targetUnit = 'SD';
    else if (u.includes('SMP') || u.includes('MTS')) targetUnit = 'SMP';
    else if (u.includes('SMA') || u.includes('SMK') || u.includes('MA')) targetUnit = 'SMA';
    else if (['PAUD', 'SD', 'SMP', 'SMA'].includes(u)) targetUnit = u;

    if (!targetUnit) return;

    const radio = document.querySelector(`input[name="unit"][value="${targetUnit}"]`);
    if (radio) {
        radio.checked = true;
        if (typeof updateUnitSelection === 'function') {
            updateUnitSelection(radio);
        }
        if (typeof onUnitChanged === 'function') {
            onUnitChanged(targetUnit, radio);
        }
        radio.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function selectGuruItem(guru) {
    if (!guru) return;

    // Update hidden inputs
    document.getElementById('guru_id').value = guru.id || '';
    document.getElementById('guru_nama').value = guru.nama || '';
    document.getElementById('guru_nip').value = guru.nip || '';

    // Update display UI
    const nameEl = document.getElementById('display-guru-nama');
    if (nameEl) nameEl.innerText = guru.nama || '';

    const nipEl = document.getElementById('display-guru-nip-badge');
    if (nipEl) {
        if (guru.nip) {
            nipEl.innerText = 'NIP: ' + guru.nip;
            nipEl.classList.remove('hidden');
        } else {
            nipEl.classList.add('hidden');
        }
    }

    const subEl = document.getElementById('display-guru-sub');
    if (subEl) {
        subEl.innerText = (guru.jabatan || 'Guru Pengampu') + ' • ' + (guru.unit_tugas || 'Satuan Pendidikan');
    }

    // Automatically sync unit with the selected teacher's unit
    if (guru.unit_tugas) {
        syncUnitWithGuru(guru.unit_tugas);
    }

    // Close menu
    const menu = document.getElementById('guru-dropdown-menu');
    if (menu) menu.classList.add('hidden');

    // Trigger onchange callback if exists
    if (typeof onGuruPickerChanged === 'function') {
        onGuruPickerChanged(guru);
    }
}

// Auto-sync on initial page load if unit was not explicitly chosen in query
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($teacherUnit) && empty($_GET['unit']) && empty(old('unit'))): ?>
        syncUnitWithGuru(<?= json_encode($teacherUnit) ?>);
    <?php endif; ?>
});

// Click outside handler
document.addEventListener('click', function(e) {
    const picker = document.querySelector('.guru-searchable-picker');
    const menu = document.getElementById('guru-dropdown-menu');
    if (picker && menu && !picker.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

// Escape key to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const menu = document.getElementById('guru-dropdown-menu');
        if (menu) menu.classList.add('hidden');
    }
});
</script>
