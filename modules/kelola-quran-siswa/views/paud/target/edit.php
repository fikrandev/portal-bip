<?php
/**
 * Qur'an PAUD - Form Edit Info Group Target
 * Mengubah identitas group (Nama Group, Tahun Ajaran Aktif, Status Group)
 * Full Width UI - Referensi UI/UX: Modul Kelola Nilai (group_edit.php)
 */
$defaultTaId = $group['tahun_akademik_id'] ?? ($taAktif['id'] ?? (defined('SYS_TAHUN_AKADEMIK_ID') ? SYS_TAHUN_AKADEMIK_ID : 1));
$defaultTaNama = $taAktif['nama_tahun'] ?? (defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : 'Tahun Ajaran Aktif');
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Pengaturan Target
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Edit Info Group
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">
                Edit Identitas Group Target
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Perbarui nama wadah group dan status keaktifan target pembelajaran Qur'an PAUD.
            </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>
            <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $group['id']) ?>" class="px-4 py-2.5 rounded-2xl bg-teal-50 border border-teal-200 hover:bg-teal-100 text-teal-700 font-bold text-xs sm:text-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
                <span>Masuk Kelola Target</span>
            </a>
        </div>
    </div>

    <!-- Banner Shortcut ke Halaman Kelola Target Materi -->
    <div class="p-5 rounded-3xl bg-gradient-to-r from-teal-50 to-emerald-50 border border-teal-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-teal-600/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-bold text-teal-900">Kelola Target Materi (Tahsin & Tahfidz)</h4>
                <p class="text-[11px] sm:text-xs text-teal-700 mt-0.5">
                    Target level jilid, rentang halaman, checklist surah dan doa santri diatur di dalam group target.
                </p>
            </div>
        </div>
        <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $group['id']) ?>" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs sm:text-sm shadow-sm transition-all whitespace-nowrap self-start sm:self-auto">
            <span>Buka Target Materi</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    </div>

    <!-- Form Container (Full Width) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="<?= url('kelola-quran-siswa-paud/target/update/' . $group['id']) ?>" method="POST" class="space-y-6 p-6 sm:p-8" id="formEditTarget">
            <?= class_exists('CSRF') ? CSRF::field() : '' ?>
            <input type="hidden" name="id" value="<?= $group['id'] ?>">

            <!-- 1. Nama Group Target -->
            <div class="space-y-2 max-w-2xl">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                    Nama Group Target <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_grup" required value="<?= htmlspecialchars($group['nama_grup']) ?>"
                       placeholder="Contoh: Group Target Tahsin & Tahfidz PAUD Semester Ganjil"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50 transition-all font-medium text-slate-800">
            </div>

            <!-- 2. Tahun Ajaran (Pengaturan Sistem) -->
            <div class="space-y-2 max-w-2xl">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                    Tahun Ajaran <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-4 p-4 rounded-2xl border border-emerald-200 bg-emerald-50/40">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm shadow-emerald-500/20">
                        📅
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm sm:text-base font-bold text-slate-800"><?= htmlspecialchars($defaultTaNama) ?></span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                <span>Aktif di Sistem</span>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Tahun ajaran diambil dari Pengaturan Sistem.</p>
                    </div>
                    <input type="hidden" name="tahun_akademik_id" value="<?= $defaultTaId ?>">
                </div>
            </div>

            <!-- 3. Status Group -->
            <div class="space-y-2 max-w-2xl">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                    Status Group <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl">
                    <label class="relative flex items-center gap-3.5 p-4 rounded-2xl border-2 <?= $group['is_active'] ? 'border-emerald-400 bg-emerald-50/50' : 'border-slate-200 bg-white' ?> cursor-pointer transition-all status-card">
                        <input type="radio" name="is_active" value="1" <?= $group['is_active'] ? 'checked' : '' ?> class="w-4 h-4 text-teal-600 focus:ring-teal-500" onchange="updateStatusCardStyle()">
                        <div>
                            <span class="text-xs font-bold text-emerald-900 block">● Group Aktif</span>
                            <span class="text-[11px] text-emerald-700">Aktif digunakan untuk kegiatan penilaian</span>
                        </div>
                    </label>
                    <label class="relative flex items-center gap-3.5 p-4 rounded-2xl border-2 <?= !$group['is_active'] ? 'border-slate-400 bg-slate-50/80' : 'border-slate-200 bg-white' ?> cursor-pointer transition-all status-card">
                        <input type="radio" name="is_active" value="0" <?= !$group['is_active'] ? 'checked' : '' ?> class="w-4 h-4 text-slate-500 focus:ring-slate-500" onchange="updateStatusCardStyle()">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">○ Tidak Aktif</span>
                            <span class="text-[11px] text-slate-400">Arsip atau nonaktif</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Tombol Simpan & Batal -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan Info</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateStatusCardStyle() {
    document.querySelectorAll('.status-card').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.checked) {
            if (radio.value === '1') {
                card.classList.remove('border-slate-200', 'bg-white');
                card.classList.add('border-emerald-400', 'bg-emerald-50/50');
            } else {
                card.classList.remove('border-emerald-400', 'bg-emerald-50/50');
                card.classList.add('border-slate-400', 'bg-slate-50/80');
            }
        } else {
            card.classList.remove('border-emerald-400', 'bg-emerald-50/50', 'border-slate-400', 'bg-slate-50/80');
            card.classList.add('border-slate-200', 'bg-white');
        }
    });
}
</script>
