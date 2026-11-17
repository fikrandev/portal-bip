<?php
/**
 * Rekap Jurnal PIC & Monitoring KBM Guru - Portal Guru Mobile
 */
$summary = $rekap['summary'] ?? [];
$unfilledSessions = $rekap['unfilled_sessions'] ?? [];
$filledJournals = $rekap['filled_journals'] ?? [];
$allSchedules = $rekap['all_schedules'] ?? [];
$pStart = $startDate ?? date('Y-m-01');
$pEnd = $endDate ?? date('Y-m-d');
?>

<!-- Header with Back Button -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile/jurnal') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-bold text-slate-800 text-base leading-tight">Rekap Jurnal PIC</h2>
            <p class="text-[10px] text-slate-400 font-medium">Monitoring KBM & Ketuntasan Jurnal</p>
        </div>
    </div>
    <a href="<?= url('mobile/jurnal') ?>" class="w-9 h-9 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center press-bounce" title="Buat Jurnal Baru">
        <i data-lucide="pen-tool" class="w-4 h-4"></i>
    </a>
</div>

<div class="p-4 space-y-4">

    <!-- 1. Filter Rentang Periode (Tanggal Awal & Tanggal Akhir) -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
                    📅
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-xs">Periode Rekap Jurnal PIC</h3>
                    <p class="text-[10px] text-slate-400">Atur tanggal awal & akhir rekapitulasi</p>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                Web Config
            </span>
        </div>

        <form action="<?= url('mobile/rekap-jurnal') ?>" method="GET" class="space-y-2.5" id="form-filter-rekap-jurnal">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" id="rekap_jurnal_start" value="<?= e($pStart) ?>" required
                           class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="rekap_jurnal_end" value="<?= e($pEnd) ?>" required
                           class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 outline-none focus:border-primary-500">
                </div>
            </div>

            <!-- Preset Buttons & Submit -->
            <div class="flex items-center justify-between gap-1.5 pt-1">
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar">
                    <button type="button" onclick="setPresetRekapJurnal('bulan_ini')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-primary-50 text-[10px] font-bold text-slate-600 hover:text-primary-600 transition-colors">Bulan Ini</button>
                    <button type="button" onclick="setPresetRekapJurnal('bulan_lalu')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-primary-50 text-[10px] font-bold text-slate-600 hover:text-primary-600 transition-colors">Bulan Lalu</button>
                    <button type="button" onclick="setPresetRekapJurnal('pekan_ini')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-primary-50 text-[10px] font-bold text-slate-600 hover:text-primary-600 transition-colors">Pekan Ini</button>
                </div>
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm press-bounce shrink-0">
                    Terapkan
                </button>
            </div>
        </form>

        <div class="text-[10px] text-slate-400 bg-slate-50 p-2 rounded-xl flex items-center justify-between">
            <span>Menampilkan data: <strong><?= date('d M Y', strtotime($pStart)) ?></strong> s/d <strong><?= date('d M Y', strtotime($pEnd)) ?></strong></span>
        </div>
    </div>

    <!-- 2. Hero KPI Cards: Ringkasan Ketuntasan Jurnal -->
    <div class="bg-gradient-to-br from-purple-700 via-indigo-700 to-primary-700 rounded-3xl p-4 text-white shadow-lg shadow-purple-700/20 space-y-3 relative overflow-hidden">
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-semibold text-purple-200 uppercase tracking-wider">Ketuntasan Jurnal PIC</span>
                <div class="text-3xl font-black tracking-tight mt-0.5">
                    <?= $summary['persen_kelengkapan'] ?? 100 ?>%
                </div>
                <p class="text-[11px] text-purple-100 mt-0.5">
                    <strong><?= (int)($summary['total_terisi'] ?? 0) ?></strong> terisi dari <strong><?= (int)($summary['total_target_sesi'] ?? 0) ?></strong> target sesi mengajar
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-2xl shadow-inner">
                📝
            </div>
        </div>

        <!-- 3 Grid Stats -->
        <div class="grid grid-cols-3 gap-2 pt-1 border-t border-white/15 text-center relative z-10">
            <div class="bg-white/10 rounded-2xl p-2.5 backdrop-blur-xs">
                <span class="text-[9px] uppercase font-bold text-purple-200 block">Target KBM</span>
                <span class="text-lg font-extrabold text-white block mt-0.5"><?= (int)($summary['total_target_sesi'] ?? 0) ?></span>
                <span class="text-[8px] text-purple-200">Sesi</span>
            </div>
            <div class="bg-emerald-400/20 rounded-2xl p-2.5 backdrop-blur-xs border border-emerald-300/30">
                <span class="text-[9px] uppercase font-bold text-emerald-200 block">Jurnal Terisi</span>
                <span class="text-lg font-extrabold text-white block mt-0.5"><?= (int)($summary['total_terisi'] ?? 0) ?></span>
                <span class="text-[8px] text-emerald-200">Selesai</span>
            </div>
            <div class="bg-rose-400/25 rounded-2xl p-2.5 backdrop-blur-xs border border-rose-300/30">
                <span class="text-[9px] uppercase font-bold text-rose-200 block">Belum Diisi</span>
                <span class="text-lg font-extrabold text-white block mt-0.5"><?= (int)($summary['total_kosong'] ?? 0) ?></span>
                <span class="text-[8px] text-rose-200">Perlu Tindakan</span>
            </div>
        </div>

        <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
    </div>

    <!-- 3. Segmented Tab Switcher: Kapan Tidak Isi Jurnal vs Jurnal Terisi vs Jadwal Guru -->
    <div class="flex p-1 bg-slate-200/70 rounded-2xl">
        <button type="button" onclick="switchJurnalTab('kosong')" id="btn-jurnal-kosong" class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-rose-700 shadow-sm transition-all flex items-center justify-center gap-1">
            <span>⚠️ Belum Diisi</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-rose-100 text-rose-800"><?= count($unfilledSessions) ?></span>
        </button>
        <button type="button" onclick="switchJurnalTab('terisi')" id="btn-jurnal-terisi" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1">
            <span>✅ Terisi</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-emerald-100 text-emerald-800"><?= count($filledJournals) ?></span>
        </button>
        <button type="button" onclick="switchJurnalTab('jadwal')" id="btn-jurnal-jadwal" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1">
            <span>📋 Jadwal Mingguan</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-slate-100 text-slate-700"><?= count($allSchedules) ?></span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: DAFTAR KAPAN SAJA TIDAK ISI JURNAL (BELUM DIISI)  -->
    <!-- ======================================================== -->
    <div id="panel-jurnal-kosong" class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-extrabold text-slate-800 text-xs">Daftar Jadwal Mengajar Belum Diisi</h3>
            <span class="text-[10px] text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                Total: <?= count($unfilledSessions) ?> Sesi Terlewat
            </span>
        </div>

        <?php if (empty($unfilledSessions)): ?>
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-100 shadow-sm space-y-2">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto text-xl">
                    🎉
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Alhamdulillah, Semua Jurnal Tuntas!</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
                    Tidak ada jadwal mengajar yang terlewat untuk rentang periode tanggal ini. Kerja bagus, terus pertahankan!
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-2.5">
                <?php foreach ($unfilledSessions as $item): ?>
                    <div class="bg-white rounded-2xl p-3.5 border border-rose-100 shadow-sm hover:border-rose-300 transition-all">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2.5">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 mt-0.5">
                                    ⚠️
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-800 text-sm leading-tight"><?= e($item['mata_pelajaran']) ?></h4>
                                    </div>
                                    <p class="text-xs font-semibold text-rose-600 mt-0.5">
                                        <?= e($item['kelas']) ?> &bull; Jam ke-<?= e($item['jam_ke']) ?> (<?= (int)$item['jumlah_jp'] ?> JP)
                                    </p>
                                    <div class="flex items-center gap-2 mt-1.5 text-[11px] text-slate-500 font-medium">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <?= e($item['hari']) ?>, <?= date('d M Y', strtotime($item['tanggal'])) ?>
                                        </span>
                                        <span>&bull;</span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <?= e($item['waktu']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                    Belum Diisi
                                </span>
                            </div>
                        </div>

                        <!-- Action Button to fill journal -->
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400">Silakan isi jurnal untuk sesi ini</span>
                            <a href="<?= url('mobile/jurnal') ?>?open_modal=1&jadwal_id=<?= (int)$item['jadwal_id'] ?>&tanggal=<?= e($item['tanggal']) ?>&kelas=<?= urlencode($item['kelas']) ?>&mapel=<?= urlencode($item['mata_pelajaran']) ?>&jam_ke=<?= e($item['jam_ke']) ?>&alokasi_jp=<?= (int)$item['jumlah_jp'] ?>" 
                               class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm press-bounce">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Isi Sekarang</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: DAFTAR JURNAL SELESAI (TERISI)                    -->
    <!-- ======================================================== -->
    <div id="panel-jurnal-terisi" class="hidden space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-extrabold text-slate-800 text-xs">Jurnal Mengajar Tersimpan</h3>
            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                Total: <?= count($filledJournals) ?> Jurnal
            </span>
        </div>

        <?php if (empty($filledJournals)): ?>
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-100 shadow-sm space-y-2">
                <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mx-auto text-xl">
                    📭
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Belum Ada Jurnal Tersimpan</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
                    Belum ada pengisian jurnal mengajar pada rentang periode yang dipilih.
                </p>
                <div class="pt-2">
                    <a href="<?= url('mobile/jurnal') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-600 text-white text-xs font-bold shadow-sm press-bounce">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        <span>Buat Jurnal Baru</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="space-y-2.5">
                <?php foreach ($filledJournals as $jr): ?>
                    <div class="bg-white rounded-2xl p-3.5 border border-slate-100 shadow-sm hover:border-slate-300 transition-all">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 mt-0.5">
                                    ✅
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm leading-tight"><?= e($jr['mata_pelajaran']) ?></h4>
                                    <p class="text-xs font-semibold text-primary-600 mt-0.5">
                                        <?= e($jr['kelas']) ?> &bull; Jam ke-<?= e($jr['jam_ke']) ?> (<?= (int)$jr['jumlah_jp'] ?> JP)
                                    </p>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 font-medium">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <?= e($jr['hari']) ?>, <?= date('d M Y', strtotime($jr['tanggal'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Selesai
                            </span>
                        </div>

                        <!-- Materi Pokok & Presensi Siswa -->
                        <div class="mt-3 pt-2.5 border-t border-slate-100 space-y-1.5">
                            <div class="text-xs text-slate-700">
                                <span class="font-bold text-slate-500 text-[11px] block">Materi / Bahasan:</span>
                                <p class="text-slate-800 font-medium"><?= e($jr['materi_pokok']) ?></p>
                            </div>
                            <?php if (!empty($jr['uraian_kegiatan'])): ?>
                                <div class="text-[11px] text-slate-500 line-clamp-2 bg-slate-50 p-2 rounded-xl">
                                    <?= e($jr['uraian_kegiatan']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i>
                                    Presensi: <strong><?= e($jr['kehadiran']) ?></strong>
                                </span>
                                <span class="text-[10px] font-mono text-slate-400"><?= e($jr['kode_jurnal']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: DAFTAR JADWAL MINGGUAN GURU                       -->
    <!-- ======================================================== -->
    <div id="panel-jurnal-jadwal" class="hidden space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-extrabold text-slate-800 text-xs">Jadwal Mengajar Rutin Guru</h3>
            <span class="text-[10px] text-slate-600 font-bold bg-slate-100 px-2 py-0.5 rounded-full">
                <?= count($allSchedules) ?> Jadwal Aktif
            </span>
        </div>

        <?php if (empty($allSchedules)): ?>
            <div class="bg-white rounded-3xl p-6 text-center border border-slate-100 shadow-sm space-y-2">
                <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mx-auto text-xl">
                    🗓️
                </div>
                <h4 class="font-bold text-slate-800 text-sm">Tidak Ada Jadwal Mengajar Terdaftar</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
                    Akun Anda belum memiliki plotting jadwal pelajaran di SIM KBM. Hubungi bagian kurikulum.
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($allSchedules as $sch): ?>
                    <div class="bg-white rounded-2xl p-3.5 border border-slate-100 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex flex-col items-center justify-center font-bold text-xs shrink-0">
                                <span><?= substr($sch['hari'] ?? 'Sen', 0, 3) ?></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs"><?= e($sch['subject'] ?? '-') ?></h4>
                                <p class="text-[11px] font-medium text-slate-500">
                                    <?= e($sch['class'] ?? '-') ?> &bull; Jam ke-<?= e($sch['jam_ke'] ?? '1') ?> (<?= (int)($sch['jumlah_jp'] ?? 2) ?> JP)
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">
                                <?= e($sch['time'] ?? 'KBM') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- JavaScript Helper -->
<script>
function setPresetRekapJurnal(preset) {
    const today = new Date();
    let start, end;

    if (preset === 'bulan_ini') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = today;
    } else if (preset === 'bulan_lalu') {
        start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        end = new Date(today.getFullYear(), today.getMonth(), 0);
    } else if (preset === 'pekan_ini') {
        const day = today.getDay();
        const diff = today.getDate() - day + (day === 0 ? -6 : 1);
        start = new Date(today.setDate(diff));
        end = new Date();
    }

    const fmt = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (start && end) {
        document.getElementById('rekap_jurnal_start').value = fmt(start);
        document.getElementById('rekap_jurnal_end').value = fmt(end);
        document.getElementById('form-filter-rekap-jurnal').submit();
    }
}

function switchJurnalTab(tab) {
    const panels = {
        kosong: document.getElementById('panel-jurnal-kosong'),
        terisi: document.getElementById('panel-jurnal-terisi'),
        jadwal: document.getElementById('panel-jurnal-jadwal')
    };

    const buttons = {
        kosong: document.getElementById('btn-jurnal-kosong'),
        terisi: document.getElementById('btn-jurnal-terisi'),
        jadwal: document.getElementById('btn-jurnal-jadwal')
    };

    Object.keys(panels).forEach(key => {
        if (key === tab) {
            panels[key].classList.remove('hidden');
            buttons[key].className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white shadow-sm transition-all flex items-center justify-center gap-1 ' +
                (key === 'kosong' ? 'text-rose-700' : (key === 'terisi' ? 'text-emerald-700' : 'text-slate-800'));
        } else {
            panels[key].classList.add('hidden');
            buttons[key].className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1';
        }
    });
}
</script>
