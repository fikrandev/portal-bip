<?php
/**
 * Kaldik - Detail View & Kelola Agenda di Dalam Grup Kaldik
 */
$agendas = $konten['agendas'] ?? [];
$isActive = !empty($item['is_active']);

$statusBadge = [
    'draft' => ['label' => 'Draft', 'class' => 'bg-slate-100 text-slate-700 border-slate-300'],
    'diajukan' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'disetujui' => ['label' => 'Disetujui / Sah', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'ditolak' => ['label' => 'Perlu Revisi', 'class' => 'bg-rose-100 text-rose-800 border-rose-300']
][$item['status']] ?? ['label' => ucfirst($item['status']), 'class' => 'bg-slate-100 text-slate-700 border-slate-300'];

$itemUnit = $item['unit'] ?? 'SD';
$unitList = PerangkatModel::getUnitList();
$uBadge = $unitList[$itemUnit]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-300';
$uIcon = $unitList[$itemUnit]['icon'] ?? '🏫';
?>
<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Top Action Bar & Identitas Grup -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $uBadge ?>">
                    <?= $uIcon ?> Unit <?= e($itemUnit) ?>
                </span>
                
                <!-- Status Aktif / Acuan Badge -->
                <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/kaldik/toggle-active/{$item['id']}") ?>" class="inline">
                    <?= CSRF::field() ?>
                    <button type="submit" title="Klik untuk mengubah status aktif" class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold transition-all border <?= $isActive ? 'bg-emerald-100 text-emerald-800 border-emerald-300 shadow-sm hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200' ?>">
                        <span class="w-2 h-2 rounded-full <?= $isActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' ?>"></span>
                        <?= $isActive ? '🟢 AKTIF (Acuan Guru)' : '⚪ NON-AKTIF (Arsip)' ?>
                    </button>
                </form>

                <span class="text-xs text-slate-400">Tahun Ajaran <?= e($item['nama_tahun']) ?></span>
            </div>
            
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight"><?= e($item['judul']) ?></h1>
            <p class="text-xs text-slate-500">Unit: <strong class="text-slate-700">Unit <?= e($itemUnit) ?></strong> • Penyusun / PJ: <strong class="text-slate-700"><?= e($item['guru_nama']) ?></strong> <?= !empty($item['guru_nip']) ? '(' . e($item['guru_nip']) . ')' : '' ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= url('kelola-perangkat-pembelajaran/kaldik') ?>" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                ← Kembali
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/cetak/{$item['id']}") ?>" target="_blank" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                Cetak Kaldik
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/edit/{$item['id']}") ?>" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit Info Grup
            </a>
        </div>
    </div>

    <!-- Matriks Visual Kalender Tanggal Bulanan -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <?php
        $nama_tahun = $item['nama_tahun'] ?? '';
        $semester = $item['semester'] ?? 'Ganjil';
        include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/matrix_kaldik.php';
        ?>
    </div>

    <!-- Kelola Agenda di Dalam Grup Kaldik -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Daftar Agenda & Kegiatan Akademik
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola seluruh rincian jadwal kegiatan akademik di dalam kalender pendidikan ini</p>
            </div>
            <button type="button" onclick="toggleFormAgenda()" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Kegiatan Baru
            </button>
        </div>

        <!-- Inline Quick Add Agenda Form -->
        <div id="form-tambah-agenda" class="hidden bg-slate-50 border border-emerald-200/80 rounded-2xl p-4 sm:p-5 animate-in fade-in slide-in-from-top-2 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-emerald-950 uppercase tracking-wider flex items-center gap-2">
                    <span>✨</span> Form Tambah Kegiatan Akademik
                </h4>
                <button type="button" onclick="toggleFormAgenda()" class="text-xs text-slate-400 hover:text-slate-600 font-bold">✕ Tutup Form</button>
            </div>

            <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/kaldik/agenda/add/{$item['id']}") ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <?= CSRF::field() ?>
                
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nama Agenda / Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="kegiatan" required placeholder="Contoh: Masa Pengenalan Lingkungan Sekolah (MPLS)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Kategori Agenda <span class="text-rose-500">*</span></label>
                    <select name="kategori" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="kbm">📚 KBM Efektif</option>
                        <option value="penilaian">📝 Penilaian / Ujian (STS/SAS)</option>
                        <option value="libur_nasional">🔴 Libur Nasional / Cuti</option>
                        <option value="libur_semester">🏖️ Libur Semester</option>
                        <option value="kegiatan">🎯 Kegiatan Sekolah / Event</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Semester</label>
                    <select name="semester" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="Ganjil">Semester Ganjil</option>
                        <option value="Genap">Semester Genap</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_mulai" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pengecualian Tingkat</label>
                    <input type="text" name="pengecualian_tingkat" placeholder="Misal: 6, 9" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Keterangan / Lokasi</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="keterangan" placeholder="Contoh: Diikuti semua..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs whitespace-nowrap shadow-sm transition-colors">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($konten['deskripsi'])): ?>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-700">
                <p class="font-bold text-slate-800 mb-1">Catatan / Kebijakan Kaldik:</p>
                <p class="leading-relaxed"><?= nl2br(e($konten['deskripsi'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Table of Agendas -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Tanggal Pelaksanaan</th>
                        <th class="py-3 px-3 font-bold">Nama Agenda / Kegiatan</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3">Pengecualian</th>
                        <th class="py-3 px-3">Semester</th>
                        <th class="py-3 px-3">Keterangan</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($agendas)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <p class="text-sm font-semibold text-slate-600">Belum ada agenda kegiatan di dalam Kalender Pendidikan ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Tambah Kegiatan Baru" di atas untuk menambahkan jadwal akademik.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($agendas as $i => $ag): ?>
                            <?php
                            $katBadge = [
                                'kbm' => ['label' => 'KBM Efektif', 'class' => 'bg-emerald-100 text-emerald-800'],
                                'penilaian' => ['label' => 'Penilaian / Ujian', 'class' => 'bg-amber-100 text-amber-800'],
                                'libur_nasional' => ['label' => 'Libur Nasional', 'class' => 'bg-rose-100 text-rose-800'],
                                'libur_semester' => ['label' => 'Libur Semester', 'class' => 'bg-purple-100 text-purple-800'],
                                'kegiatan' => ['label' => 'Kegiatan Sekolah', 'class' => 'bg-blue-100 text-blue-800']
                            ][$ag['kategori'] ?? 'kegiatan'] ?? ['label' => 'Kegiatan', 'class' => 'bg-slate-100 text-slate-700'];
                            ?>
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-3 text-center text-slate-400"><?= $i + 1 ?></td>
                                <td class="py-3 px-3 whitespace-nowrap font-mono text-[11px] text-slate-600">
                                    <?= !empty($ag['tanggal_mulai']) ? date('d/m/Y', strtotime($ag['tanggal_mulai'])) : '-' ?>
                                    <?= (!empty($ag['tanggal_selesai']) && $ag['tanggal_selesai'] !== $ag['tanggal_mulai']) ? ' s.d. ' . date('d/m/Y', strtotime($ag['tanggal_selesai'])) : '' ?>
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-800"><?= e($ag['kegiatan']) ?></td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $katBadge['class'] ?>">
                                        <?= $katBadge['label'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-500 font-semibold">
                                    <?= e($ag['pengecualian_tingkat'] ?? '-') ?: '-' ?>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap text-slate-500 font-semibold">
                                    <?= e($ag['semester'] ?? 'Ganjil') ?>
                                </td>
                                <td class="py-3 px-3 text-slate-500"><?= e($ag['keterangan'] ?? '-') ?></td>
                                <td class="py-3 px-3 text-right">
                                    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/kaldik/agenda/delete/{$item['id']}") ?>" onsubmit="return confirm('Hapus agenda kegiatan ini?');" class="inline">
                                        <?= CSRF::field() ?>
                                        <input type="hidden" name="agenda_index" value="<?= $i ?>">
                                        <button type="submit" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors" title="Hapus Agenda">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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

<script>
function toggleFormAgenda() {
    const form = document.getElementById('form-tambah-agenda');
    if (form) {
        form.classList.toggle('hidden');
    }
}
</script>
