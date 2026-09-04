<?php
/**
 * Jadwal Pelajaran - Index View
 * Modul Kelola Perangkat Pembelajaran
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-primary-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <span>Jadwal Pelajaran & KBM</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Manajemen versi jadwal KBM, konfigurasi alokasi JP per jenjang, dan generator jadwal cerdas otomatis bebas bentrok.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-indigo-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Buat Grup Jadwal Baru</span>
            </a>
        </div>
    </div>

    <!-- Active Schedule Highlight Banner -->
    <?php if ($activeGrup): ?>
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-primary-700 rounded-3xl p-6 text-white shadow-lg shadow-emerald-600/20 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2 z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-black uppercase tracking-wider backdrop-blur-sm border border-white/30">
                <span class="w-2 h-2 rounded-full bg-emerald-300 animate-ping"></span>
                Jadwal Resmi Aktif Saat Ini
            </div>
            <h2 class="text-2xl font-black tracking-tight"><?= e($activeGrup['nama_grup']) ?></h2>
            <p class="text-xs text-white/80">
                Tahun Ajaran: <strong class="text-white"><?= e($activeGrup['tahun_ajaran']) ?></strong> (Semester <?= e($activeGrup['semester']) ?>) | 
                Unit: <strong class="text-white"><?= e($activeGrup['jenjang']) ?></strong> | 
                Total Terjadwal: <strong class="text-white"><?= number_format($totalSlotAktif) ?> Slot Jam</strong> di <strong class="text-white"><?= number_format($totalKelasAktif) ?> Rombel</strong>
            </p>
        </div>
        <div class="flex items-center gap-2 z-10 flex-wrap">
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $activeGrup['id']) ?>" class="px-4 py-2 bg-white text-emerald-800 hover:bg-white/90 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                <span>Lihat Matriks Jadwal</span>
            </a>
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/' . $activeGrup['id']) ?>" class="px-3.5 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-semibold backdrop-blur-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>Atur JP</span>
            </a>
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/generate/' . $activeGrup['id']) ?>" class="px-3.5 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-semibold backdrop-blur-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Re-Generate</span>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Table of Schedule Groups -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Grup / Versi Jadwal Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola versi jadwal per unit, aktifkan yang resmi berlaku, dan tinjau matriks jam mengajar.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Nama Grup Jadwal</th>
                        <th class="px-6 py-4">T.A & Semester</th>
                        <th class="px-6 py-4">Unit</th>
                        <th class="px-6 py-4 text-center">Kelas Terjadwal</th>
                        <th class="px-6 py-4 text-center">Slot Terisi</th>
                        <th class="px-6 py-4 text-center">Status Berlaku</th>
                        <th class="px-6 py-4 text-right">Aksi & Kontrol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($grupList)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <p class="text-sm font-semibold text-slate-600">Belum ada grup jadwal pelajaran</p>
                            <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Grup Jadwal Baru" untuk mulai menyusun jadwal KBM.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($grupList as $g): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors <?= !empty($g['is_active']) ? 'bg-emerald-50/30' : '' ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2.5">
                                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $g['id']) ?>" class="font-extrabold text-slate-900 hover:text-indigo-600 transition-colors text-sm">
                                    <?= e($g['nama_grup']) ?>
                                </a>
                                <?php if (!empty($g['is_active'])): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">AKTIF</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($g['keterangan'])): ?>
                                <p class="text-[11px] text-slate-500 mt-0.5"><?= e($g['keterangan']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= e($g['tahun_ajaran']) ?></div>
                            <span class="text-[11px] text-slate-500">Semester <?= e($g['semester']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                <?= e($g['jenjang'] ?: 'SD') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-extrabold text-slate-900"><?= number_format($g['real_kelas_count'] ?: $g['total_kelas']) ?></span>
                            <span class="text-[10px] text-slate-400 block">Kelas</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-extrabold text-indigo-700"><?= number_format($g['real_slots_count'] ?: $g['total_slot_terisi']) ?></span>
                            <span class="text-[10px] text-slate-400 block">Slot JP</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if (!empty($g['is_active'])): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Berlaku Resmi
                                </span>
                            <?php else: ?>
                                <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/set-active/' . $g['id']) ?>" method="POST" class="inline">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 border border-slate-200 transition-all" title="Klik untuk mengaktifkan jadwal ini">
                                        Aktifkan Ini
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $g['id']) ?>" class="p-2 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors" title="Lihat Matriks Jadwal">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                                </a>
                                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/' . $g['id']) ?>" class="p-2 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors" title="Pengaturan JP & Jam Rutin">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </a>
                                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/generate/' . $g['id']) ?>" class="p-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors" title="Auto-Generator Cerdas">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                </a>
                                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/edit/' . $g['id']) ?>" class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors" title="Edit Grup">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                </a>
                                <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/delete/' . $g['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus grup jadwal ini beserta seluruh slotnya?');" class="inline">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Hapus Grup">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </form>
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
