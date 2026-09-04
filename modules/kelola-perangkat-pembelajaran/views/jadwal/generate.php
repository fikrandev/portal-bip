<?php
/**
 * Auto-Generator Jadwal Cerdas - View
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                    ← Kembali ke Jadwal
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3 mt-1">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </div>
                <span>Auto-Generator Jadwal Cerdas Bebas Bentrok</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Menyusun seluruh alokasi jam mengajar guru ke dalam slot waktu mingguan menggunakan algoritma cerdas tanpa tabrakan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $grup['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                <span>Lihat Matriks Jadwal</span>
            </a>
        </div>
    </div>

    <!-- Parameter & Trigger Box -->
    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 p-8 rounded-3xl text-white shadow-xl space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-white/10">
            <div>
                <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                    Engine Status: Ready to Generate
                </span>
                <h2 class="text-xl font-extrabold mt-2"><?= e($grup['nama_grup']) ?></h2>
                <p class="text-xs text-slate-300 mt-1">
                    Tahun Ajaran: <strong class="text-white"><?= e($grup['tahun_ajaran']) ?></strong> | Semester: <strong class="text-white"><?= e($grup['semester']) ?></strong> | Unit: <strong class="text-white"><?= e($grup['jenjang']) ?></strong>
                </p>
                <?php if (!empty($unitKelasSiswa)): ?>
                    <p class="text-[11px] text-emerald-300/90 mt-2">
                        🏫 <strong><?= count($unitKelasSiswa) ?> Rombel/Kelas</strong> terdeteksi dari data siswa Unit <strong><?= e($grup['jenjang']) ?></strong>.
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-center px-4 py-2 bg-white/10 rounded-2xl border border-white/10">
                    <p class="text-[11px] text-slate-400">Total Guru</p>
                    <p class="text-xl font-extrabold text-white"><?= number_format($totalGuruPenugasan) ?></p>
                </div>
                <div class="text-center px-4 py-2 bg-white/10 rounded-2xl border border-white/10">
                    <p class="text-[11px] text-slate-400">Total Kelas</p>
                    <p class="text-xl font-extrabold text-white"><?= number_format($totalKelasPenugasan) ?></p>
                </div>
                <div class="text-center px-4 py-2 bg-emerald-500/20 rounded-2xl border border-emerald-500/30">
                    <p class="text-[11px] text-emerald-300">Total Beban JP</p>
                    <p class="text-xl font-extrabold text-emerald-400"><?= number_format($totalJpPenugasan) ?> JP</p>
                </div>
            </div>
        </div>

        <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/run-generate/' . $grup['id']) ?>" method="POST" class="space-y-6">
            <?= CSRF::field() ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                <!-- Max Block Length -->
                <div class="bg-white/5 p-4 rounded-2xl border border-white/10 space-y-2">
                    <label class="font-bold text-slate-200 block">Panjang Blok Sesi Mengajar</label>
                    <select name="max_block_length" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white focus:border-emerald-500">
                        <option value="2" selected>Maksimal 2 JP Berurutan (Rekomendasi)</option>
                        <option value="3">Maksimal 3 JP Berurutan</option>
                        <option value="4">Maksimal 4 JP Berurutan</option>
                    </select>
                    <p class="text-[11px] text-slate-400">Pelajaran 4 JP akan dipecah menjadi 2x sesi 2 JP pada hari berbeda.</p>
                </div>

                <!-- Include Saturday -->
                <div class="bg-white/5 p-4 rounded-2xl border border-white/10 space-y-2">
                    <label class="font-bold text-slate-200 block">Jadwal Hari Sabtu</label>
                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" name="allow_saturday" id="allow_saturday" value="1" checked class="w-4 h-4 text-emerald-500 rounded bg-slate-800 border-slate-700">
                        <label for="allow_saturday" class="cursor-pointer text-slate-300">Sertakan Hari Sabtu untuk alokasi KBM</label>
                    </div>
                </div>

                <!-- Conflict Guarantee -->
                <div class="bg-emerald-500/10 p-4 rounded-2xl border border-emerald-500/30 space-y-1 text-emerald-300">
                    <p class="font-bold text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        Jaminan 100% Bebas Bentrok
                    </p>
                    <p class="text-[11px] text-slate-300">
                        Engine memeriksa bentrok guru di seluruh kelas dan slot istirahat secara real-time.
                    </p>
                </div>
            </div>

            <!-- Execute Button -->
            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="submit" onclick="this.innerHTML='Sedang Mengomputasi Jadwal...'; this.disabled=true; this.form.submit();" class="px-8 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-black rounded-2xl shadow-lg shadow-emerald-500/30 transition-all flex items-center gap-3 cursor-pointer">
                    <svg class="w-5 h-5 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>JALANKAN AUTO-GENERATOR JADWAL SEKARANG</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Data Penugasan Mengajar -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Preview Data Alokasi Penugasan Guru yang Akan Dijadwalkan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Data diambil langsung dari modul Penugasan Mengajar Pegawai.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                <?= count($teachingData) ?> Penugasan Mapel
            </span>
        </div>

        <div class="overflow-x-auto max-h-96">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider sticky top-0">
                    <tr>
                        <th class="px-5 py-3 text-center w-12">No</th>
                        <th class="px-5 py-3">Rombel / Kelas</th>
                        <th class="px-5 py-3">Mata Pelajaran</th>
                        <th class="px-5 py-3">Nama Guru Pengampu</th>
                        <th class="px-5 py-3 text-center">Alokasi JP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($teachingData)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data penugasan mengajar yang aktif. Pastikan data mengajar guru sudah diisi di modul Kelola Pegawai -> Penugasan.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($teachingData as $t): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3 text-center font-semibold text-slate-400"><?= $no++ ?></td>
                        <td class="px-5 py-3 font-extrabold text-slate-900"><?= e($t['nama_kelas']) ?></td>
                        <td class="px-5 py-3 font-bold text-indigo-700"><?= e($t['mata_pelajaran']) ?></td>
                        <td class="px-5 py-3 text-slate-800 font-medium"><?= e($t['nama_guru']) ?></td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <?= $t['jumlah_jp'] ?> JP
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
