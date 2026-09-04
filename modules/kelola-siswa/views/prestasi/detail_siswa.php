<?php
/**
 * Rekap Prestasi Siswa Perorangan - Portal BIP
 */
?>
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <a href="<?= url('kelola-siswa/prestasi') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            <span>Kembali ke Daftar Prestasi</span>
        </a>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-siswa/prestasi/create?siswa_id=' . $siswa['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-amber-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Prestasi Siswa Ini</span>
            </a>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-lg shadow-amber-500/20">
            🏆
        </div>
        <div class="flex-1 text-center sm:text-left">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h1 class="text-xl font-extrabold text-slate-900"><?= e($siswa['nama_lengkap'] ?: $siswa['nama']) ?></h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    Total: <?= count($prestasiList) ?> Prestasi
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                NIS: <span class="font-mono font-bold text-slate-700"><?= e($siswa['nis'] ?: '-') ?></span> | NISN: <span class="font-mono font-bold text-slate-700"><?= e($siswa['nisn'] ?: '-') ?></span> | Jenjang: <span class="font-bold text-indigo-600"><?= e($siswa['jenjang'] ?: 'SD') ?> (Kelas <?= e($siswa['kelas'] ?: '-') ?>)</span>
            </p>
        </div>
    </div>

    <!-- Timeline of Achievements -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Rekam Jejak Prestasi & Penghargaan</h2>

        <?php if (empty($prestasiList)): ?>
            <div class="text-center py-12 text-slate-400">
                <p class="text-sm font-semibold text-slate-600">Belum ada catatan prestasi untuk siswa ini.</p>
                <a href="<?= url('kelola-siswa/prestasi/create?siswa_id=' . $siswa['id']) ?>" class="text-xs text-amber-600 font-bold hover:underline mt-2 inline-block">
                    + Tambahkan Prestasi Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($prestasiList as $p): ?>
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 hover:bg-white hover:border-amber-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">
                                🥇 <?= e($p['peringkat'] ?: 'Juara') ?>
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700">
                                <?= e($p['tingkat']) ?>
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Tahun <?= e($p['tahun'] ?: date('Y')) ?></span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 mt-1"><?= e($p['nama_prestasi']) ?></h3>
                        <p class="text-xs text-slate-600">
                            Bidang: <strong class="text-slate-800"><?= e($p['bidang']) ?></strong> | Penyelenggara: <strong class="text-slate-800"><?= e($p['penyelenggara'] ?: '-') ?></strong> | Pembimbing: <strong class="text-slate-800"><?= e($p['guru_pendamping'] ?: '-') ?></strong>
                        </p>
                        <?php if (!empty($p['keterangan'])): ?>
                            <p class="text-xs text-slate-500 italic mt-1"><?= e($p['keterangan']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <?php if (!empty($p['file_sertifikat'])): ?>
                            <a href="<?= asset('uploads/prestasi_siswa/' . $p['file_sertifikat']) ?>" target="_blank" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-semibold transition-colors">
                                Lihat Piagam
                            </a>
                        <?php endif; ?>
                        <a href="<?= url('kelola-siswa/prestasi/edit/' . $p['id']) ?>" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
