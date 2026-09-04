<?php
/**
 * RPP / Modul Ajar - Detail View
 */
$profilPancasila = $konten['profil_pancasila'] ?? [];

$statusBadge = [
    'draft' => ['label' => 'Draft', 'class' => 'bg-slate-100 text-slate-700 border-slate-300'],
    'diajukan' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'disetujui' => ['label' => 'Disetujui / Sah', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'ditolak' => ['label' => 'Perlu Revisi', 'class' => 'bg-rose-100 text-rose-800 border-rose-300']
][$item['status']] ?? ['label' => ucfirst($item['status']), 'class' => 'bg-slate-100 text-slate-700 border-slate-300'];
?>
<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $statusBadge['class'] ?>">
                    <?= $statusBadge['label'] ?>
                </span>
                <span class="text-xs text-slate-400"><?= e($item['mata_pelajaran']) ?> • <?= e($item['tingkat_kelas']) ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight"><?= e($item['judul']) ?></h1>
            <p class="text-xs text-slate-500">Guru Pengampu: <strong class="text-slate-700"><?= e($item['guru_nama']) ?></strong> <?= !empty($item['guru_nip']) ? '(' . e($item['guru_nip']) . ')' : '' ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= url('kelola-perangkat-pembelajaran/rpp') ?>" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                ← Kembali
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/rpp/cetak/{$item['id']}") ?>" target="_blank" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                Cetak RPP
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/rpp/edit/{$item['id']}") ?>" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit Dokumen
            </a>
        </div>
    </div>

    <!-- Reviewer Action Hub -->
    <?php if ($can_approve && $item['status'] === 'diajukan'): ?>
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-3xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-amber-950 flex items-center gap-2 mb-2">
                <span>🛡️</span> Aksi Verifikasi RPP / Modul Ajar
            </h3>
            <p class="text-xs text-amber-800 mb-4">Sebagai verifikator (Kepala Sekolah / Kurikulum), Anda dapat menyetujui modul ajar ini atau memberikan catatan perbaikan sintaks pembelajaran.</p>

            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/approve/{$item['id']}") ?>">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="catatan" value="RPP / Modul Ajar telah diperiksa dan disetujui.">
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyetujui dokumen ini?')" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Setujui RPP (Approve)
                    </button>
                </form>

                <button type="button" onclick="document.getElementById('modal-tolak-rpp').classList.remove('hidden')" class="px-5 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    Tolak / Minta Revisi
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal Catatan Tolak -->
    <div id="modal-tolak-rpp" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-slate-800">Catatan Perbaikan Modul Ajar</h3>
            <p class="text-xs text-slate-500">Tuliskan arahan revisi tujuan pembelajaran atau langkah kegiatan.</p>
            
            <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/reject/{$item['id']}") ?>" class="space-y-4">
                <?= CSRF::field() ?>
                <textarea name="catatan_revisi" rows="4" required placeholder="Contoh: Mohon perbaiki pertanyaan pemantik dan lengkapi rubrik asesmen formatif..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50"></textarea>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-tolak-rpp').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors">
                        Kirim Catatan & Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- I. Informasi Umum Modul Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> I. Informasi Umum
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-slate-400 font-semibold block mb-0.5">Model Pembelajaran:</span>
                <span class="font-bold text-slate-800"><?= e($konten['model_pembelajaran'] ?? 'Problem Based Learning') ?></span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-slate-400 font-semibold block mb-0.5">Alokasi Waktu:</span>
                <span class="font-bold text-slate-800"><?= e($item['alokasi_waktu'] ?? '2 JP') ?> (Pertemuan ke-<?= e($konten['pertemuan_ke'] ?? '1') ?>)</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-slate-400 font-semibold block mb-0.5">Sarana & Media:</span>
                <span class="font-bold text-slate-800"><?= e($konten['sarana_prasarana'] ?? '-') ?></span>
            </div>
        </div>

        <?php if (!empty($profilPancasila)): ?>
            <div class="pt-2">
                <span class="text-xs font-semibold text-slate-700 block mb-2">Profil Pelajar Pancasila:</span>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($profilPancasila as $p3): ?>
                        <span class="px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-1.5">
                            <span>✨</span> <?= e($p3) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- II. Komponen Inti Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> II. Komponen Inti
        </h3>

        <div class="space-y-4 text-xs leading-relaxed">
            <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/70">
                <h4 class="font-bold text-amber-950 uppercase text-[11px] mb-1">A. Tujuan Pembelajaran (TP):</h4>
                <p class="text-slate-700 whitespace-pre-line font-medium"><?= e($konten['tujuan_pembelajaran'] ?? '-') ?></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <h4 class="font-bold text-slate-800 uppercase text-[11px] mb-1">B. Pemahaman Bermakna:</h4>
                    <p class="text-slate-600 whitespace-pre-line"><?= e($konten['pemahaman_bermakna'] ?? '-') ?></p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <h4 class="font-bold text-slate-800 uppercase text-[11px] mb-1">C. Pertanyaan Pemantik:</h4>
                    <p class="text-slate-600 whitespace-pre-line"><?= e($konten['pertanyaan_pemantik'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- III. Kegiatan Pembelajaran Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> III. Langkah-Langkah Kegiatan Pembelajaran
        </h3>

        <div class="space-y-3 text-xs leading-relaxed">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-slate-800 uppercase">1. Kegiatan Pendahuluan</h4>
                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-100 text-blue-800 font-bold font-mono text-[11px]"><?= e($konten['waktu_pendahuluan'] ?? '15 Menit') ?></span>
                </div>
                <p class="text-slate-700 whitespace-pre-line"><?= e($konten['kegiatan_pendahuluan'] ?? '-') ?></p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-slate-800 uppercase">2. Kegiatan Inti</h4>
                    <span class="px-2.5 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-bold font-mono text-[11px]"><?= e($konten['waktu_inti'] ?? '60 Menit') ?></span>
                </div>
                <p class="text-slate-700 whitespace-pre-line"><?= e($konten['kegiatan_inti'] ?? '-') ?></p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-slate-800 uppercase">3. Kegiatan Penutup</h4>
                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-100 text-amber-800 font-bold font-mono text-[11px]"><?= e($konten['waktu_penutup'] ?? '15 Menit') ?></span>
                </div>
                <p class="text-slate-700 whitespace-pre-line"><?= e($konten['kegiatan_penutup'] ?? '-') ?></p>
            </div>
        </div>
    </div>

    <!-- IV. Asesmen & Evaluasi Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> IV. Asesmen, Pengayaan & Remedial
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <h4 class="font-bold text-slate-800 uppercase text-[11px] mb-1">Asesmen Formatif:</h4>
                <p class="text-slate-600 whitespace-pre-line"><?= e($konten['asesmen_formatif'] ?? '-') ?></p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <h4 class="font-bold text-slate-800 uppercase text-[11px] mb-1">Asesmen Sumatif:</h4>
                <p class="text-slate-600 whitespace-pre-line"><?= e($konten['asesmen_sumatif'] ?? '-') ?></p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <h4 class="font-bold text-slate-800 uppercase text-[11px] mb-1">Pengayaan:</h4>
                <p class="text-slate-600 whitespace-pre-line"><?= e($konten['pengayaan'] ?? '-') ?></p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <h4 class="font-bold text-slate-800 uppercase text-[11px] mb-1">Remedial:</h4>
                <p class="text-slate-600 whitespace-pre-line"><?= e($konten['remedial'] ?? '-') ?></p>
            </div>
        </div>

        <?php if (!empty($item['file_lampiran'])): ?>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="text-base">📎</span>
                    <span>Berkas Lampiran LKPD / Modul:</span>
                </div>
                <a href="<?= url($item['file_lampiran']) ?>" target="_blank" class="px-4 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-colors">
                    Unduh File Lampiran
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Riwayat Log Verifikasi -->
    <?php if (!empty($logs)): ?>
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span>🕒</span> Riwayat Aktivitas & Pengesahan
            </h3>
            <div class="space-y-3">
                <?php foreach ($logs as $l): ?>
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                        <span class="text-base">
                            <?= $l['aksi'] === 'setujui' ? '✅' : ($l['aksi'] === 'tolak' ? '❌' : ($l['aksi'] === 'ajukan' ? '📤' : '📝')) ?>
                        </span>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800"><?= e($l['user_nama']) ?> (<?= strtoupper($l['aksi']) ?>)</span>
                                <span class="text-[10px] text-slate-400"><?= date('d M Y H:i', strtotime($l['created_at'])) ?></span>
                            </div>
                            <?php if (!empty($l['catatan'])): ?>
                                <p class="text-slate-600 mt-1"><?= e($l['catatan']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
