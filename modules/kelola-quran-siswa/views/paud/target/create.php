<?php
/**
 * Qur'an PAUD - Form Buat Group Target Baru
 * Hanya: Nama Group, Tahun Ajaran Aktif (Pengaturan Sistem), dan Status Group (Aktif / Tidak Aktif).
 * Full Width UI - Referensi UI/UX: Modul Kelola Nilai (group_create.php)
 */
$defaultTaId = $taAktif['id'] ?? (defined('SYS_TAHUN_AKADEMIK_ID') ? SYS_TAHUN_AKADEMIK_ID : 1);
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
                    Buat Group Target Baru
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">
                Buat Group Target Qur'an PAUD
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Tentukan nama group dan status. Target materi (Tahsin & Tahfidz) diinput setelah masuk ke dalam group.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Form Container (Full Width) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="<?= url('kelola-quran-siswa-paud/target/store') ?>" method="POST" id="formGroupTarget">
            <?= class_exists('CSRF') ? CSRF::field() : '' ?>

            <div class="p-6 sm:p-8 space-y-6">

                <!-- 1. Nama Group Target -->
                <div class="space-y-2 max-w-2xl">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                        Nama Group Target <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_grup" required 
                           placeholder="Contoh: Group Target Tahsin & Tahfidz PAUD Semester Ganjil"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50 transition-all font-medium text-slate-800">
                    <p class="text-[11px] text-slate-400">Tuliskan nama wadah group target pembelajaran santri.</p>
                </div>

                <!-- 2. Tahun Ajaran Aktif (Pengaturan Sistem) -->
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
                            <p class="text-xs text-slate-500 mt-0.5">Tahun ajaran ini otomatis diambil dari Pengaturan Sistem.</p>
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
                        <label class="relative flex items-center gap-3.5 p-4 rounded-2xl border-2 border-emerald-400 bg-emerald-50/50 cursor-pointer transition-all status-card">
                            <input type="radio" name="is_active" value="1" checked class="w-4 h-4 text-teal-600 focus:ring-teal-500" onchange="updateStatusCardStyle()">
                            <div>
                                <span class="text-xs font-bold text-emerald-900 block">● Group Aktif</span>
                                <span class="text-[11px] text-emerald-700">Aktif digunakan untuk kegiatan pembelajaran & penilaian</span>
                            </div>
                        </label>
                        <label class="relative flex items-center gap-3.5 p-4 rounded-2xl border-2 border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition-all status-card">
                            <input type="radio" name="is_active" value="0" class="w-4 h-4 text-slate-500 focus:ring-slate-500" onchange="updateStatusCardStyle()">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block">○ Tidak Aktif</span>
                                <span class="text-[11px] text-slate-400">Arsip atau tidak menerima penilaian baru</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Info Box: Alur Pengaturan Target -->
                <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200/80 flex items-start gap-3 max-w-2xl">
                    <div class="w-8 h-8 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-teal-900">Alur Pengisian Target Materi</h4>
                        <p class="text-xs text-teal-700 mt-0.5 leading-relaxed">
                            Setelah group ini disimpan, Anda akan otomatis <strong>masuk ke dalam group</strong> untuk mengatur target materi <strong>Tahsin (metode, jilid, halaman)</strong> dan <strong>Tahfidz (surah & doa)</strong>.
                        </p>
                    </div>
                </div>

                <!-- Form Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-500/20 transition-all">
                        <span>Simpan Group & Masuk Atur Target</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
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
