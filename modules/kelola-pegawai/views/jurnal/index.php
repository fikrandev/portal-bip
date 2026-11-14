<?php
/**
 * View Kepegawaian: Isi Jurnal & Data Jurnal Saya (Personal Akun Login)
 */
?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 border border-primary-100 text-primary-700 text-xs font-bold mb-2">
                <span>Kepegawaian Portal Web</span>
                <span>&bull;</span>
                <span>Sinkronisasi Jadwal & Portal Guru</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Isi Jurnal Mengajar & Data Jurnal Saya
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Isi jurnal pembelajaran harian beserta absensi siswa berdasarkan akun pegawai yang sedang login.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="<?= url('kelola-pegawai') ?>"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-xs transition-all">
                <span>&larr; Data Pegawai</span>
            </a>
        </div>
    </div>

    <?php include MODULES_PATH . '/kelola-pegawai/views/jurnal/_personal_jurnal_section.php'; ?>
</div>
