<?php
/**
 * Form Presensi Harian Kelas Siswa (Wali Kelas / Piket)
 * Dilengkapi Autosave real-time, tombol "Set Semua Hadir", live counter, dan cetak Jurnal Harian A4
 */
$dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w', strtotime($tanggal))];
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Presensi Harian Rombel
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <?= $dayName ?>, <?= date('d F Y', strtotime($tanggal)) ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2">
                <span>Presensi Kelas <?= htmlspecialchars($cleanKelas) ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pencatatan daftar hadir harian kelas oleh Wali Kelas / Guru Piket
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="<?= url('kelola-absen-siswa/kelas?kelas=' . urlencode($cleanKelas)) ?>" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Kembali
            </a>
            <a href="<?= url('kelola-absen-siswa/kelas/cetak?kelas=' . urlencode($cleanKelas) . '&tanggal=' . $tanggal) ?>" 
               target="_blank"
               class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Jurnal A4</span>
            </a>
            <button type="button" onclick="setAllHadir()" class="px-4 py-2.5 rounded-2xl bg-teal-50 text-teal-700 border border-teal-300 hover:bg-teal-100 font-bold text-xs transition-all flex items-center gap-1.5 shadow-sm">
                <span>⚡</span>
                <span>Set Semua Hadir</span>
            </button>
            <button type="button" onclick="document.getElementById('formPresensiKelas').submit()" class="px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Presensi</span>
            </button>
        </div>
    </div>

    <!-- Live Counter Bar & Autosave Indicator -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-400 mr-2 uppercase tracking-wider">Live Counter Rombel:</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Hadir: <strong id="cnt_H">0</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Sakit: <strong id="cnt_S">0</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Izin: <strong id="cnt_I">0</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Alpa: <strong id="cnt_A">0</strong></span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                <span>Terlambat: <strong id="cnt_T">0</strong></span>
            </span>
        </div>

        <div id="autosaveStatus" class="flex items-center gap-2 text-xs font-bold text-teal-600">
            <svg class="w-4 h-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span>Autosave Aktif</span>
        </div>
    </div>

    <!-- Parameter Tanggal & Jurnal Form Header -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
        <form id="formPresensiKelas" action="<?= url('kelola-absen-siswa/kelas/store') ?>" method="POST">
            <input type="hidden" name="kelas" value="<?= htmlspecialchars($cleanKelas) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Presensi</label>
                    <input type="date" name="tanggal" value="<?= $tanggal ?>" 
                           onchange="reloadWithDate(this.value)" 
                           class="w-full px-3 py-2 rounded-2xl border border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>

                <!-- Catatan Sesi / Jurnal Kelas -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Catatan Khusus Kelas / Jurnal Piket</label>
                    <input type="text" name="catatan_sesi" value="<?= htmlspecialchars($session['catatan'] ?? '') ?>" 
                           class="w-full px-4 py-2 rounded-2xl border border-slate-200 text-xs font-medium text-slate-700 bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none" 
                           placeholder="Cth: Suasana kelas kondusif, 1 siswa sakit di UKS setelah jam ke-3">
                </div>
            </div>

            <!-- Tabel Daftar Siswa Rombel -->
            <div class="mt-6 border border-slate-200/80 rounded-3xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4 w-28">NIS</th>
                                <th class="py-3.5 px-4">Nama Siswa</th>
                                <th class="py-3.5 px-4 w-16 text-center">L/P</th>
                                <th class="py-3.5 px-4 w-72 text-center">Status Kehadiran</th>
                                <th class="py-3.5 px-4">Catatan Wali Kelas / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            <?php if (empty($siswaList)): ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Tidak ada siswa yang terdaftar di rombongan belajar kelas ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($siswaList as $idx => $sw): 
                                    $sid = $sw['id'];
                                    $curStatus = $attendanceMap[$sid]['status'] ?? 'H';
                                    $curCatatan = $attendanceMap[$sid]['catatan'] ?? '';
                                ?>
                                    <tr class="hover:bg-slate-50/60 transition-colors row-siswa" data-siswa-id="<?= $sid ?>">
                                        <!-- No -->
                                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                                            <?= $idx + 1 ?>
                                        </td>

                                        <!-- NIS -->
                                        <td class="py-3 px-4 font-mono text-slate-500">
                                            <?= htmlspecialchars($sw['nis'] ?: '-') ?>
                                        </td>

                                        <!-- Nama Siswa -->
                                        <td class="py-3 px-4 font-bold text-slate-800">
                                            <?= htmlspecialchars($sw['nama']) ?>
                                            <input type="hidden" name="nama_siswa[<?= $sid ?>]" value="<?= htmlspecialchars($sw['nama']) ?>">
                                        </td>

                                        <!-- L/P -->
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= $sw['jenis_kelamin'] === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' ?>">
                                                <?= htmlspecialchars($sw['jenis_kelamin'] ?: '-') ?>
                                            </span>
                                        </td>

                                        <!-- Status Radio Buttons (H, S, I, A, T) -->
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5 p-1 bg-slate-100/90 rounded-2xl">
                                                <!-- H: Hadir -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="status[<?= $sid ?>]" value="H" class="sr-only status-radio" <?= $curStatus === 'H' ? 'checked' : '' ?> onchange="onStatusChanged(<?= $sid ?>, 'H', this)">
                                                    <span class="status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block <?= $curStatus === 'H' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-emerald-700' ?>">
                                                        H
                                                    </span>
                                                </label>

                                                <!-- S: Sakit -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="status[<?= $sid ?>]" value="S" class="sr-only status-radio" <?= $curStatus === 'S' ? 'checked' : '' ?> onchange="onStatusChanged(<?= $sid ?>, 'S', this)">
                                                    <span class="status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block <?= $curStatus === 'S' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-blue-700' ?>">
                                                        S
                                                    </span>
                                                </label>

                                                <!-- I: Izin -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="status[<?= $sid ?>]" value="I" class="sr-only status-radio" <?= $curStatus === 'I' ? 'checked' : '' ?> onchange="onStatusChanged(<?= $sid ?>, 'I', this)">
                                                    <span class="status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block <?= $curStatus === 'I' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:text-amber-700' ?>">
                                                        I
                                                    </span>
                                                </label>

                                                <!-- A: Alpa -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="status[<?= $sid ?>]" value="A" class="sr-only status-radio" <?= $curStatus === 'A' ? 'checked' : '' ?> onchange="onStatusChanged(<?= $sid ?>, 'A', this)">
                                                    <span class="status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block <?= $curStatus === 'A' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-rose-700' ?>">
                                                        A
                                                    </span>
                                                </label>

                                                <!-- T: Terlambat -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="status[<?= $sid ?>]" value="T" class="sr-only status-radio" <?= $curStatus === 'T' ? 'checked' : '' ?> onchange="onStatusChanged(<?= $sid ?>, 'T', this)">
                                                    <span class="status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block <?= $curStatus === 'T' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 hover:text-purple-700' ?>">
                                                        T
                                                    </span>
                                                </label>
                                            </div>
                                        </td>

                                        <!-- Catatan -->
                                        <td class="py-3 px-4">
                                            <input type="text" name="catatan[<?= $sid ?>]" value="<?= htmlspecialchars($curCatatan) ?>" 
                                                   onblur="onNoteBlur(<?= $sid ?>, this.value)"
                                                   placeholder="Catatan..." 
                                                   class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-1 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="mt-6 flex items-center justify-between">
                <p class="text-xs text-slate-400">Setiap perubahan status dan catatan otomatis disimpan ke database.</p>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="setAllHadir()" class="px-4 py-2.5 rounded-2xl bg-teal-50 text-teal-700 border border-teal-300 hover:bg-teal-100 font-bold text-xs transition-all flex items-center gap-1.5 shadow-sm">
                        <span>⚡</span>
                        <span>Set Semua Hadir</span>
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Presensi Kelas</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function reloadWithDate(val) {
    window.location.href = '<?= url('kelola-absen-siswa/kelas/input') ?>?kelas=<?= urlencode($cleanKelas) ?>&tanggal=' + val;
}

function recalculateCounters() {
    let h = 0, s = 0, i = 0, a = 0, t = 0;
    document.querySelectorAll('.status-radio:checked').forEach(radio => {
        const val = radio.value;
        if (val === 'H') h++;
        else if (val === 'S') s++;
        else if (val === 'I') i++;
        else if (val === 'A') a++;
        else if (val === 'T') t++;
    });
    document.getElementById('cnt_H').textContent = h;
    document.getElementById('cnt_S').textContent = s;
    document.getElementById('cnt_I').textContent = i;
    document.getElementById('cnt_A').textContent = a;
    document.getElementById('cnt_T').textContent = t;
}

function updateRadioStyles(row, chosenVal) {
    const btnGroup = row.querySelector('.inline-flex');
    btnGroup.querySelectorAll('.status-btn').forEach(span => {
        span.className = 'status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block text-slate-600';
    });
    const activeSpan = row.querySelector(`input[value="${chosenVal}"] + .status-btn`);
    if (activeSpan) {
        if (chosenVal === 'H') activeSpan.className = 'status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block bg-emerald-600 text-white shadow-sm';
        else if (chosenVal === 'S') activeSpan.className = 'status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block bg-blue-600 text-white shadow-sm';
        else if (chosenVal === 'I') activeSpan.className = 'status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block bg-amber-500 text-white shadow-sm';
        else if (chosenVal === 'A') activeSpan.className = 'status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block bg-rose-600 text-white shadow-sm';
        else if (chosenVal === 'T') activeSpan.className = 'status-btn px-2.5 py-1 rounded-xl text-xs font-black transition-all inline-block bg-purple-600 text-white shadow-sm';
    }
}

function showAutosaveStatus(msg, isSaving = false) {
    const el = document.getElementById('autosaveStatus');
    if (!el) return;
    if (isSaving) {
        el.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span><span class="text-amber-600">Menyimpan...</span>';
    } else {
        el.innerHTML = '<svg class="w-4 h-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span class="text-teal-600">' + msg + '</span>';
    }
}

function autosaveStudent(siswaId, status, catatan) {
    showAutosaveStatus('Menyimpan...', true);
    const payload = {
        kelas: <?= json_encode($cleanKelas) ?>,
        tanggal: document.querySelector('input[name="tanggal"]').value,
        siswa_id: siswaId,
        nama_siswa: document.querySelector(`input[name="nama_siswa[${siswaId}]"]`).value,
        status: status,
        catatan: catatan
    };

    fetch('<?= url('kelola-absen-siswa/kelas/autosave') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showAutosaveStatus('Tersimpan (' + new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'}) + ')');
        } else {
            showAutosaveStatus('Gagal: ' + (res.message || 'error'));
        }
    })
    .catch(() => {
        showAutosaveStatus('Gagal koneksi');
    });
}

function onStatusChanged(siswaId, status, input) {
    const row = input.closest('.row-siswa');
    updateRadioStyles(row, status);
    recalculateCounters();
    const catatan = row.querySelector(`input[name="catatan[${siswaId}]"]`).value;
    autosaveStudent(siswaId, status, catatan);
}

function onNoteBlur(siswaId, noteVal) {
    const row = document.querySelector(`.row-siswa[data-siswa-id="${siswaId}"]`);
    if (!row) return;
    const checkedRadio = row.querySelector('.status-radio:checked');
    const status = checkedRadio ? checkedRadio.value : 'H';
    autosaveStudent(siswaId, status, noteVal);
}

function setAllHadir() {
    document.querySelectorAll('.row-siswa').forEach(row => {
        const sid = row.getAttribute('data-siswa-id');
        const hRadio = row.querySelector('input[value="H"]');
        if (hRadio) {
            hRadio.checked = true;
            updateRadioStyles(row, 'H');
        }
    });
    recalculateCounters();
    document.getElementById('formPresensiKelas').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    recalculateCounters();
});
</script>
