<?php
/**
 * Pusat Verifikasi / Approval Hub View
 */
$currentTab = $_GET['tab'] ?? 'pending';
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Pusat Verifikasi RPP / Modul Ajar</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pusat peninjauan, pengesahan, dan pemberian catatan perbaikan RPP / Modul Ajar guru</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-2xl bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <?= count($pending_items ?? []) ?> RPP Menunggu Persetujuan
            </span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <a href="<?= url('kelola-perangkat-pembelajaran/verifikasi?tab=pending') ?>" class="px-5 py-2.5 font-bold text-xs border-b-2 transition-all flex items-center gap-2 <?= $currentTab === 'pending' ? 'border-amber-600 text-amber-700 bg-amber-50/50 rounded-t-2xl' : 'border-transparent text-slate-500 hover:text-slate-700' ?>">
            <span>📥</span> Menunggu Review (<?= count($pending_items ?? []) ?>)
        </a>
        <a href="<?= url('kelola-perangkat-pembelajaran/verifikasi?tab=history') ?>" class="px-5 py-2.5 font-bold text-xs border-b-2 transition-all flex items-center gap-2 <?= $currentTab === 'history' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-2xl' : 'border-transparent text-slate-500 hover:text-slate-700' ?>">
            <span>✅</span> Riwayat Keputusan
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="<?= url('kelola-perangkat-pembelajaran/verifikasi') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <input type="hidden" name="tab" value="<?= e($currentTab) ?>">
            <input type="hidden" name="tipe" value="rpp">

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tahun Ajaran</label>
                <select name="ta" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">Semua Tahun Ajaran</option>
                    <?php foreach ($ta_list as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= ($filter_ta ?? '') == $ta['id'] ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Unit Sekolah</label>
                <select name="unit" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">Semua Unit (PAUD, SD, SMP, SMA)</option>
                    <option value="PAUD" <?= ($filter_unit ?? '') === 'PAUD' ? 'selected' : '' ?>>PAUD / TK</option>
                    <option value="SD" <?= ($filter_unit ?? '') === 'SD' ? 'selected' : '' ?>>SD (Sekolah Dasar)</option>
                    <option value="SMP" <?= ($filter_unit ?? '') === 'SMP' ? 'selected' : '' ?>>SMP (Sekolah Menengah Pertama)</option>
                    <option value="SMA" <?= ($filter_unit ?? '') === 'SMA' ? 'selected' : '' ?>>SMA (Sekolah Menengah Atas)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Pencarian Modul / Guru</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Cari judul RPP / nama guru..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl text-xs transition-colors">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Content Table -->
    <?php
    $displayItems = ($currentTab === 'history') ? ($history_items ?? []) : ($pending_items ?? []);
    ?>
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <th class="py-3.5 px-4 font-bold">Jenis & Judul Dokumen</th>
                        <th class="py-3.5 px-4 font-bold">Tahun / Semester</th>
                        <th class="py-3.5 px-4 font-bold">Penyusun / Guru</th>
                        <th class="py-3.5 px-4 font-bold">Status</th>
                        <th class="py-3.5 px-4 font-bold text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($displayItems)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-3xl">🎉</span>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada pengajuan dokumen pada daftar ini</p>
                                    <p class="text-xs text-slate-400">Semua pengajuan perangkat pembelajaran telah diproses.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($displayItems as $row): ?>
                            <?php
                            $tipeBadge = [
                                'kaldik' => ['label' => 'Kaldik', 'class' => 'bg-emerald-100 text-emerald-800'],
                                'hes' => ['label' => 'HES', 'class' => 'bg-teal-100 text-teal-800'],
                                'heb' => ['label' => 'HEB', 'class' => 'bg-cyan-100 text-cyan-800'],
                                'prota' => ['label' => 'Prota', 'class' => 'bg-indigo-100 text-indigo-800'],
                                'prosem' => ['label' => 'Prosem', 'class' => 'bg-purple-100 text-purple-800'],
                                'rpp' => ['label' => 'RPP / Modul', 'class' => 'bg-rose-100 text-rose-800']
                            ][$row['tipe']] ?? ['label' => strtoupper($row['tipe']), 'class' => 'bg-slate-100 text-slate-700'];

                            $statusBadge = [
                                'draft' => ['label' => 'Draft', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                'diajukan' => ['label' => 'Menunggu Review', 'class' => 'bg-amber-100 text-amber-800 border-amber-300'],
                                'disetujui' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                                'ditolak' => ['label' => 'Perlu Revisi', 'class' => 'bg-rose-100 text-rose-800 border-rose-300']
                            ][$row['status']] ?? ['label' => ucfirst($row['status']), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];

                            $detailUrl = url("kelola-perangkat-pembelajaran/{$row['tipe']}/detail/{$row['id']}");
                            ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase <?= $tipeBadge['class'] ?>">
                                            <?= $tipeBadge['label'] ?>
                                        </span>
                                        <?php if (!empty($row['mata_pelajaran'])): ?>
                                            <span class="text-[11px] text-slate-500 font-semibold">• <?= e($row['mata_pelajaran']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?= $detailUrl ?>" class="font-bold text-slate-800 hover:text-amber-700 transition-colors">
                                        <?= e($row['judul']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800"><?= e($row['nama_tahun'] ?? 'TA Aktif') ?></div>
                                    <div class="text-[11px] text-slate-500">Semester <?= e($row['semester']) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-700"><?= e($row['guru_nama']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= !empty($row['guru_nip']) ? 'NIP: ' . e($row['guru_nip']) : 'Staff/Guru' ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border <?= $statusBadge['class'] ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $row['status'] === 'disetujui' ? 'bg-emerald-500' : ($row['status'] === 'diajukan' ? 'bg-amber-500' : ($row['status'] === 'ditolak' ? 'bg-rose-500' : 'bg-slate-400')) ?>"></span>
                                        <?= $statusBadge['label'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= $detailUrl ?>" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                                            Periksa
                                        </a>

                                        <?php if ($row['status'] === 'diajukan'): ?>
                                            <!-- Approve Button -->
                                            <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/approve/{$row['id']}") ?>" class="inline">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="catatan" value="Disetujui dari Pusat Approval.">
                                                <button type="submit" onclick="return confirm('Setujui dokumen <?= addslashes(e($row['judul'])) ?>?')" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                    Setujui
                                                </button>
                                            </form>

                                            <!-- Reject Trigger -->
                                            <button type="button" onclick="bukaModalTolak(<?= $row['id'] ?>, '<?= addslashes(e($row['judul'])) ?>')" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition-colors">
                                                Tolak
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tolak Dokumen -->
<div id="modal-reject-hub" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="text-base font-bold text-slate-800">Catatan Perbaikan / Tolak Pengajuan</h3>
        <p class="text-xs text-slate-500" id="reject-modal-doc-title"></p>
        
        <form id="form-reject-hub" method="POST" action="" class="space-y-4">
            <?= CSRF::field() ?>
            <textarea name="catatan_revisi" rows="4" required placeholder="Tuliskan catatan perbaikan atau instruksi revisi untuk guru penyusun..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50"></textarea>
            
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-reject-hub').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors">
                    Kirim Catatan & Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalTolak(id, judul) {
    const modal = document.getElementById('modal-reject-hub');
    const form = document.getElementById('form-reject-hub');
    const titleSpan = document.getElementById('reject-modal-doc-title');

    form.action = '<?= url('kelola-perangkat-pembelajaran/reject/') ?>' + id;
    titleSpan.innerText = 'Dokumen: ' + judul;
    modal.classList.remove('hidden');
}
</script>
