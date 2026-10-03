<?php
/**
 * Pemeliharaan & Perbaikan Sarpras
 * Portal BIP
 */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-sarpras') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                    &larr; Dashboard Sarpras
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2.5">
                <span>Pemeliharaan &amp; Perbaikan Sarpras</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pusat pengaduan kerusakan fasilitas, perbaikan barang, dan pemeliharaan gedung berkala
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button onclick="openModalLapor()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>+ Lapor Kerusakan Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 text-xs">
        <a href="<?= url('kelola-sarpras/pemeliharaan') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= empty($status) ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Semua Laporan
        </a>
        <a href="<?= url('kelola-sarpras/pemeliharaan?status=Menunggu') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Menunggu' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Menunggu Tindakan
        </a>
        <a href="<?= url('kelola-sarpras/pemeliharaan?status=Diproses') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Diproses' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Sedang Diperbaiki
        </a>
        <a href="<?= url('kelola-sarpras/pemeliharaan?status=Selesai') ?>" class="px-4 py-2 rounded-xl font-bold transition-all <?= $status === 'Selesai' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            Selesai Diperbaiki
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Kode &amp; Judul Laporan</th>
                        <th class="py-3.5 px-4">Objek / Ruangan</th>
                        <th class="py-3.5 px-4">Pelapor &bull; Tanggal</th>
                        <th class="py-3.5 px-4 text-center">Urgensi</th>
                        <th class="py-3.5 px-4 text-right">Biaya (Est / Real)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="text-4xl mb-2">🛠️</div>
                                <p class="font-semibold">Belum ada tiket pemeliharaan atau perbaikan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($items as $m): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= e($m['judul_laporan']) ?></div>
                                    <div class="text-[11px] font-mono text-amber-700 font-semibold"><?= e($m['kode_perbaikan']) ?></div>
                                    <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1"><?= e($m['deskripsi_kerusakan']) ?></p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($m['nama_barang'])): ?>
                                        <div class="font-bold text-slate-800"><?= e($m['nama_barang']) ?></div>
                                        <div class="text-[10px] font-mono text-slate-400"><?= e($m['kode_barang']) ?></div>
                                    <?php elseif (!empty($m['nama_ruangan'])): ?>
                                        <div class="font-bold text-slate-800">Fasilitas Ruangan</div>
                                        <div class="text-[10px] text-slate-500"><?= e($m['nama_ruangan']) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-400">Fasilitas Gedung</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800"><?= e($m['pelapor_nama']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($m['tanggal_lapor'])) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($m['tingkat_urgensi'] === 'Darurat'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 animate-pulse">Darurat</span>
                                    <?php elseif ($m['tingkat_urgensi'] === 'Tinggi'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Tinggi</span>
                                    <?php elseif ($m['tingkat_urgensi'] === 'Sedang'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Sedang</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Rendah</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-bold text-slate-800">
                                        <?= $m['biaya_realisasi'] > 0 ? 'Rp ' . number_format($m['biaya_realisasi'], 0, ',', '.') : '-' ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Est: Rp <?= number_format($m['estimasi_biaya'], 0, ',', '.') ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($m['status'] === 'Selesai'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">Selesai</span>
                                    <?php elseif ($m['status'] === 'Diproses'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Diproses</span>
                                    <?php elseif ($m['status'] === 'Menunggu'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Menunggu</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Afkir</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button onclick="openModalUpdate(<?= htmlspecialchars(json_encode($m)) ?>)" class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-[11px] shadow-sm transition-all">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Lapor Kerusakan Baru -->
<div id="modal-lapor" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-xl w-full max-h-[92vh] overflow-y-auto custom-scrollbar transform scale-95 transition-all duration-200 p-6 sm:p-7">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/60 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.32l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.32 4.486c.049.58.025 1.193-.139 1.743" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Lapor Kerusakan &amp; Perbaikan Sarpras</h3>
                    <p class="text-[11px] text-slate-500">Pilih lokasi ruangan, pilih aset yang bermasalah, lalu buat laporan kerusakan</p>
                </div>
            </div>
            <button type="button" onclick="closeModalLapor()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center text-lg font-bold transition-colors">&times;</button>
        </div>

        <form action="<?= url('kelola-sarpras/pemeliharaan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs" id="form-lapor-kerusakan">
            <?= CSRF::field() ?>

            <!-- LANGKAH 1: PILIH RUANGAN (SEARCHABLE DROPDOWN) -->
            <div class="relative" id="dropdown-ruangan-box">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-md bg-amber-500 text-white font-bold text-[10px] flex items-center justify-center">1</span>
                        <span>Lokasi Ruangan / Fasilitas</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-slate-400">Pilih ruangan dulu</span>
                </div>

                <!-- Trigger Button -->
                <button type="button" id="btn-trigger-ruangan" onclick="toggleDropdownRuangan()" class="w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-50 hover:bg-white border border-slate-200 focus:border-amber-500 rounded-2xl text-left transition-all shadow-sm">
                    <span id="label-ruangan-text" class="text-slate-500 flex items-center gap-2 truncate font-medium">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
                        <span>-- Pilih Lokasi Ruangan / Tempat Aset --</span>
                    </span>
                    <svg id="chevron-ruangan" class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <input type="hidden" name="ruangan_id" id="val_ruangan_id" value="">

                <!-- Popover Menu with Live Search -->
                <div id="popover-ruangan" class="hidden absolute left-0 right-0 top-full mt-1.5 z-40 bg-white border border-slate-200 rounded-2xl shadow-2xl p-2.5 space-y-2">
                    <div class="relative">
                        <input type="text" id="input-search-ruangan" oninput="filterRuanganOptions(this.value)" placeholder="Ketik untuk mencari ruangan atau unit..." class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-amber-500 font-medium">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <button type="button" onclick="clearSearchRuangan()" class="absolute right-2 top-2 text-slate-400 hover:text-slate-600 text-xs hidden" id="btn-clear-ruangan">&times;</button>
                    </div>

                    <div id="list-ruangan-items" class="max-h-52 overflow-y-auto space-y-1 custom-scrollbar pr-1">
                        <!-- Option: Tanpa Ruangan Tertentu -->
                        <div onclick="selectRuangan('', 'Tanpa Ruangan Tertentu / Fasilitas Gedung', 'Umum', '')" class="ruangan-item-opt px-3 py-2 rounded-xl hover:bg-amber-50 cursor-pointer flex items-center justify-between transition-colors border border-transparent hover:border-amber-200">
                            <div>
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>🌐</span>
                                    <span>Tanpa Ruangan Tertentu / Fasilitas Umum</span>
                                </div>
                                <div class="text-[10px] text-slate-500">Halaman, Lapangan, Koridor, atau Fasilitas Luar</div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">Umum</span>
                        </div>

                        <?php foreach ($ruanganList as $r): ?>
                            <div onclick="selectRuangan('<?= $r['id'] ?>', '<?= addslashes(e($r['nama_ruangan'])) ?>', '<?= addslashes(e($r['unit'])) ?>', '<?= addslashes(e($r['lokasi_gedung'] ?? '')) ?>')" 
                                 data-name="<?= strtolower(e($r['nama_ruangan'])) ?>"
                                 data-unit="<?= strtolower(e($r['unit'])) ?>"
                                 data-code="<?= strtolower(e($r['kode_ruangan'] ?? '')) ?>"
                                 class="ruangan-item-opt px-3 py-2 rounded-xl hover:bg-amber-50 cursor-pointer flex items-center justify-between transition-colors border border-transparent hover:border-amber-200">
                                <div>
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <span>🚪</span>
                                        <span><?= e($r['nama_ruangan']) ?></span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                        <span>Kode: <?= e($r['kode_ruangan'] ?? '-') ?></span>
                                        <?php if (!empty($r['lokasi_gedung'])): ?>
                                            <span>&bull;</span>
                                            <span><?= e($r['lokasi_gedung']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-100 shrink-0">
                                    <?= e($r['unit']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="no-ruangan-msg" class="hidden py-4 text-center text-slate-400 text-xs font-medium">
                        Tidak ada ruangan yang cocok dengan pencarian
                    </div>
                </div>
            </div>

            <!-- LANGKAH 2: PILIH ASET DALAM RUANGAN (SEARCHABLE DROPDOWN) -->
            <div class="relative" id="dropdown-barang-box">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-md bg-amber-500 text-white font-bold text-[10px] flex items-center justify-center">2</span>
                        <span>Pilih Aset / Barang Dalam Ruangan</span>
                        <span class="text-slate-400 font-normal">(Opsional / Otomatis muncul)</span>
                    </label>
                    <span id="badge-aset-count" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                        Pilih ruangan dulu
                    </span>
                </div>

                <!-- Trigger Button -->
                <button type="button" id="btn-trigger-barang" onclick="toggleDropdownBarang()" disabled class="w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-100 text-slate-400 border border-slate-200 rounded-2xl text-left transition-all cursor-not-allowed shadow-sm">
                    <span id="label-barang-text" class="flex items-center gap-2 truncate font-medium">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                        <span>Pilih lokasi ruangan di atas terlebih dahulu...</span>
                    </span>
                    <svg id="chevron-barang" class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <input type="hidden" name="barang_id" id="val_barang_id" value="">

                <!-- Popover Menu with Live Search -->
                <div id="popover-barang" class="hidden absolute left-0 right-0 top-full mt-1.5 z-40 bg-white border border-slate-200 rounded-2xl shadow-2xl p-2.5 space-y-2">
                    <div class="relative">
                        <input type="text" id="input-search-barang" oninput="filterBarangOptions(this.value)" placeholder="Ketik nama aset, kode barang, atau merk..." class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-amber-500 font-medium">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <button type="button" onclick="clearSearchBarang()" class="absolute right-2 top-2 text-slate-400 hover:text-slate-600 text-xs hidden" id="btn-clear-barang">&times;</button>
                    </div>

                    <div id="list-barang-items" class="max-h-52 overflow-y-auto space-y-1 custom-scrollbar pr-1">
                        <!-- Populated by JavaScript based on selected room -->
                    </div>
                    <div id="no-barang-msg" class="hidden py-4 text-center text-slate-400 text-xs font-medium">
                        Tidak ada aset yang cocok dengan pencarian
                    </div>
                </div>
            </div>

            <!-- LANGKAH 3: NAMA LAPORAN / JUDUL KERUSAKAN (DENGAN REKOMENDASI PINTAR) -->
            <div class="bg-amber-50/50 p-3.5 rounded-2xl border border-amber-200/60 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="font-bold text-slate-800 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-md bg-amber-500 text-white font-bold text-[10px] flex items-center justify-center">3</span>
                        <span>Nama Laporan / Judul Kerusakan</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" id="btn-use-suggest" onclick="applySuggestedTitle()" class="hidden text-[10px] font-bold text-amber-700 hover:text-amber-800 bg-amber-100/80 px-2 py-0.5 rounded-md border border-amber-300/50 transition-colors">
                        ↺ Pakai Saran Otomatis
                    </button>
                </div>
                <div class="relative">
                    <input type="text" name="judul_laporan" id="input_judul_laporan" required 
                           oninput="onJudulUserChange()" 
                           placeholder="Contoh: AC Bocor, Meja Guru Patah, Proyektor Tidak Mau Menyala..." 
                           class="w-full px-3.5 py-2.5 bg-white border border-amber-200 rounded-xl text-slate-800 font-semibold focus:border-amber-500 outline-none shadow-sm transition-all">
                </div>
                <p id="suggest-note" class="text-[10px] text-amber-800/80 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    <span>Judul terisi otomatis saat Anda memilih ruangan &amp; aset, dan bebas Anda edit sesuai kebutuhan.</span>
                </p>
            </div>

            <!-- DETAIL PELAPOR & TINGKAT URGENSI -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Pelapor <span class="text-rose-500">*</span></label>
                    <input type="text" name="pelapor_nama" value="<?= e(Auth::name()) ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tingkat Urgensi <span class="text-rose-500">*</span></label>
                    <select name="tingkat_urgensi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none font-semibold">
                        <option value="Rendah">🟢 Rendah (Tidak mengganggu kegiatan)</option>
                        <option value="Sedang" selected>🟡 Sedang (Perlu perbaikan segera)</option>
                        <option value="Tinggi">🟠 Tinggi (Mengganggu proses KBM)</option>
                        <option value="Darurat">🔴 Darurat (Kritis / Berbahaya)</option>
                    </select>
                </div>
            </div>

            <!-- RINCIAN GEJALA KERUSAKAN -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi &amp; Gejala Kerusakan <span class="text-rose-500">*</span></label>
                <textarea name="deskripsi_kerusakan" required rows="3" placeholder="Jelaskan secara rinci letak kerusakan, suara yang tidak normal, komponen yang pecah, atau kronologi kendala..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none"></textarea>
            </div>

            <!-- ESTIMASI BIAYA & UPLOAD FOTO BUKTI -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Estimasi Biaya Perbaikan (Rp)</label>
                    <input type="text" name="estimasi_biaya" value="0" oninput="formatRupiah(this)" placeholder="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-amber-500 outline-none font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Foto Bukti Kerusakan (Opsional)</label>
                    <input type="file" name="foto_kerusakan" accept="image/*" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 focus:bg-white focus:border-amber-500 outline-none file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer">
                </div>
            </div>

            <div class="mt-6 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">Pastikan data yang dilaporkan telah sesuai</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeModalLapor()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md shadow-amber-600/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                        <span>Kirim Laporan Kerusakan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Update Status Perbaikan -->
<div id="modal-update" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform scale-95 transition-all duration-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800">Tindak Lanjut &amp; Status Perbaikan</h3>
            <button onclick="closeModalUpdate()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-update" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>
            <p class="text-slate-600">Tiket: <strong id="update-judul" class="text-slate-800"></strong></p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status Pengerjaan</label>
                    <select name="status" id="update-status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-semibold">
                        <option value="Menunggu">Menunggu Teknisi</option>
                        <option value="Diproses">Sedang Dikerjakan / Diproses</option>
                        <option value="Selesai">Selesai Diperbaiki (Aset Siap)</option>
                        <option value="Afkir">Tidak Dapat Diperbaiki (Afkir/Hapus)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Teknisi / Rekanan Pihak Ke-3</label>
                    <input type="text" name="teknisi_pihak" id="update-teknisi" placeholder="Contoh: Tim IT Internal, CV Servis..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Biaya Realisasi Perbaikan (Rp)</label>
                <input type="text" name="biaya_realisasi" id="update-biaya" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold" oninput="formatRupiah(this)">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tindakan / Solusi yang Dikerjakan</label>
                <textarea name="tindakan_perbaikan" id="update-tindakan" rows="3" placeholder="Jelaskan komponen yang diganti, servis yang dilakukan, atau pengujian kelayakan..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-primary-500 outline-none"></textarea>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeModalUpdate()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold shadow-md">Simpan Pembaruan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Data pre-loaded dari server untuk respon instan 0ms
const ROOM_ASSETS_MAP = <?= json_encode($barangByRuangan ?? []) ?>;
const ALL_BARANG_MASTER = <?= json_encode($barangList ?? []) ?>;

let currentSelectedRoomName = '';
let currentSelectedBarangName = '';
let isJudulManuallyEdited = false;

// ── MODAL TOGGLE ────────────────────────────────────────────────────────────
function openModalLapor() {
    const m = document.getElementById('modal-lapor');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
    
    // Reset state form
    resetLaporForm();
}

function closeModalLapor() {
    const m = document.getElementById('modal-lapor');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
    closePopoverRuangan();
    closePopoverBarang();
}

function resetLaporForm() {
    document.getElementById('val_ruangan_id').value = '';
    document.getElementById('val_barang_id').value = '';
    document.getElementById('label-ruangan-text').innerHTML = `
        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
        <span>-- Pilih Lokasi Ruangan / Tempat Aset --</span>
    `;
    
    const btnBarang = document.getElementById('btn-trigger-barang');
    btnBarang.disabled = true;
    btnBarang.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
    btnBarang.classList.remove('bg-slate-50', 'text-slate-700', 'hover:bg-white');
    document.getElementById('label-barang-text').innerHTML = `
        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
        <span>Pilih lokasi ruangan di atas terlebih dahulu...</span>
    `;
    document.getElementById('badge-aset-count').innerText = 'Pilih ruangan dulu';
    document.getElementById('badge-aset-count').className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500';

    currentSelectedRoomName = '';
    currentSelectedBarangName = '';
    isJudulManuallyEdited = false;
    document.getElementById('input_judul_laporan').value = '';
    document.getElementById('btn-use-suggest').classList.add('hidden');
}

// ── SEARCHABLE DROPDOWN: RUANGAN ────────────────────────────────────────────
function toggleDropdownRuangan() {
    const pop = document.getElementById('popover-ruangan');
    const chev = document.getElementById('chevron-ruangan');
    const isHidden = pop.classList.contains('hidden');
    
    closePopoverBarang();
    
    if (isHidden) {
        pop.classList.remove('hidden');
        chev.classList.add('rotate-180');
        const inp = document.getElementById('input-search-ruangan');
        inp.value = '';
        filterRuanganOptions('');
        setTimeout(() => inp.focus(), 50);
    } else {
        closePopoverRuangan();
    }
}

function closePopoverRuangan() {
    const pop = document.getElementById('popover-ruangan');
    const chev = document.getElementById('chevron-ruangan');
    if (pop) pop.classList.add('hidden');
    if (chev) chev.classList.remove('rotate-180');
}

function filterRuanganOptions(keyword) {
    const q = keyword.toLowerCase().trim();
    const items = document.querySelectorAll('.ruangan-item-opt');
    const noMsg = document.getElementById('no-ruangan-msg');
    const btnClear = document.getElementById('btn-clear-ruangan');
    
    if (btnClear) {
        btnClear.classList.toggle('hidden', q === '');
    }

    let visibleCount = 0;
    items.forEach(el => {
        const name = el.getAttribute('data-name') || '';
        const unit = el.getAttribute('data-unit') || '';
        const code = el.getAttribute('data-code') || '';
        
        if (q === '' || name.includes(q) || unit.includes(q) || code.includes(q) || el.innerText.toLowerCase().includes(q)) {
            el.classList.remove('hidden');
            visibleCount++;
        } else {
            el.classList.add('hidden');
        }
    });

    if (noMsg) {
        noMsg.classList.toggle('hidden', visibleCount > 0);
    }
}

function clearSearchRuangan() {
    const inp = document.getElementById('input-search-ruangan');
    inp.value = '';
    filterRuanganOptions('');
    inp.focus();
}

function selectRuangan(id, name, unit, location) {
    document.getElementById('val_ruangan_id').value = id;
    currentSelectedRoomName = name;
    
    // Update label ruangan
    const lbl = document.getElementById('label-ruangan-text');
    if (id === '') {
        lbl.innerHTML = `
            <span class="text-base shrink-0">🌐</span>
            <span class="font-bold text-slate-800 truncate">${name}</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 shrink-0">Umum</span>
        `;
    } else {
        lbl.innerHTML = `
            <span class="text-base shrink-0">🚪</span>
            <span class="font-bold text-slate-800 truncate">${name}</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-primary-100 text-primary-800 border border-primary-200 shrink-0">${unit}</span>
            ${location ? `<span class="text-[10px] text-slate-400 truncate">(${location})</span>` : ''}
        `;
    }

    closePopoverRuangan();

    // Aktifkan dropdown barang & isi aset ruangan terpilih
    setupBarangForRuangan(id, name);

    // Auto-suggest judul jika user belum mengetik judul custom
    updateSmartJudulSuggestion();
}

// ── SEARCHABLE DROPDOWN: BARANG DALAM RUANGAN ────────────────────────────────
function setupBarangForRuangan(ruanganId, ruanganName) {
    const btnBarang = document.getElementById('btn-trigger-barang');
    const badge = document.getElementById('badge-aset-count');
    const listContainer = document.getElementById('list-barang-items');
    
    // Enable button
    btnBarang.disabled = false;
    btnBarang.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
    btnBarang.classList.add('bg-slate-50', 'text-slate-700', 'hover:bg-white', 'hover:border-amber-400');

    // Reset barang terpilih
    document.getElementById('val_barang_id').value = '';
    currentSelectedBarangName = '';
    document.getElementById('label-barang-text').innerHTML = `
        <span class="text-amber-600 shrink-0">🔍</span>
        <span class="text-slate-600 truncate font-semibold">Cari atau pilih aset di ${ruanganName}...</span>
    `;

    // Ambil daftar barang di ruangan ini dari pre-loaded data
    let assets = [];
    if (ruanganId !== '' && ROOM_ASSETS_MAP[ruanganId]) {
        assets = ROOM_ASSETS_MAP[ruanganId];
    } else if (ruanganId === '') {
        // Ambil semua barang tanpa ruangan / master
        assets = ALL_BARANG_MASTER;
    }

    badge.innerText = assets.length > 0 ? `${assets.length} Aset di Ruangan Ini` : '0 Aset Khusus (Pilih Fasilitas Umum)';
    badge.className = assets.length > 0 
        ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200'
        : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200';

    // Bangun HTML opsi barang
    let html = `
        <div onclick="selectBarang('', 'Bukan Barang Tertentu (Fasilitas Ruangan)', '', '')" 
             class="barang-item-opt px-3 py-2 rounded-xl hover:bg-amber-50 cursor-pointer flex items-center justify-between transition-colors border border-transparent hover:border-amber-200">
            <div>
                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                    <span>🔧</span>
                    <span>Bukan Barang Tertentu / Fasilitas Ruangan</span>
                </div>
                <div class="text-[10px] text-slate-500">Kerusakan lampu, pintu, jendela, saklar, dinding, atau plafon ruangan</div>
            </div>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 shrink-0">Fasilitas</span>
        </div>
    `;

    if (assets.length > 0) {
        assets.forEach(b => {
            const safeName = (b.nama_barang || '').replace(/'/g, "\\'");
            const safeCode = (b.kode_barang || '').replace(/'/g, "\\'");
            const safeMerk = (b.merk_model || '').replace(/'/g, "\\'");
            
            html += `
                <div onclick="selectBarang('${b.id}', '${safeName}', '${safeCode}', '${safeMerk}')"
                     data-name="${(b.nama_barang || '').toLowerCase()}"
                     data-code="${(b.kode_barang || '').toLowerCase()}"
                     data-merk="${(b.merk_model || '').toLowerCase()}"
                     class="barang-item-opt px-3 py-2 rounded-xl hover:bg-amber-50 cursor-pointer flex items-center justify-between transition-colors border border-transparent hover:border-amber-200">
                    <div class="min-w-0 pr-2">
                        <div class="font-bold text-slate-800 flex items-center gap-1.5 truncate">
                            <span>📦</span>
                            <span class="truncate">${b.nama_barang}</span>
                        </div>
                        <div class="text-[10px] text-slate-500 flex items-center gap-2 mt-0.5 truncate">
                            <span class="font-mono text-amber-700 font-semibold">${b.kode_barang}</span>
                            ${b.merk_model ? `<span>&bull;</span><span>${b.merk_model}</span>` : ''}
                            ${b.stok_ruang ? `<span>&bull;</span><span class="text-slate-600 font-semibold">${b.stok_ruang} ${b.satuan || 'Unit'}</span>` : ''}
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 ${b.kondisi === 'Baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'}">
                        ${b.kondisi || 'Baik'}
                    </span>
                </div>
            `;
        });
    }

    listContainer.innerHTML = html;

    // Jika belum ada data lokal dan ruangan dipilih, coba fetch live via AJAX untuk kepastian
    if (ruanganId !== '' && (!ROOM_ASSETS_MAP[ruanganId] || ROOM_ASSETS_MAP[ruanganId].length === 0)) {
        fetch('<?= url("kelola-sarpras/pemeliharaan/barang-by-ruangan?ruangan_id=") ?>' + ruanganId)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    ROOM_ASSETS_MAP[ruanganId] = res.data;
                    setupBarangForRuangan(ruanganId, ruanganName);
                }
            })
            .catch(() => {});
    }
}

function toggleDropdownBarang() {
    const btn = document.getElementById('btn-trigger-barang');
    if (btn.disabled) return;
    
    const pop = document.getElementById('popover-barang');
    const chev = document.getElementById('chevron-barang');
    const isHidden = pop.classList.contains('hidden');

    closePopoverRuangan();

    if (isHidden) {
        pop.classList.remove('hidden');
        chev.classList.add('rotate-180');
        const inp = document.getElementById('input-search-barang');
        inp.value = '';
        filterBarangOptions('');
        setTimeout(() => inp.focus(), 50);
    } else {
        closePopoverBarang();
    }
}

function closePopoverBarang() {
    const pop = document.getElementById('popover-barang');
    const chev = document.getElementById('chevron-barang');
    if (pop) pop.classList.add('hidden');
    if (chev) chev.classList.remove('rotate-180');
}

function filterBarangOptions(keyword) {
    const q = keyword.toLowerCase().trim();
    const items = document.querySelectorAll('.barang-item-opt');
    const noMsg = document.getElementById('no-barang-msg');
    const btnClear = document.getElementById('btn-clear-barang');

    if (btnClear) {
        btnClear.classList.toggle('hidden', q === '');
    }

    let visibleCount = 0;
    items.forEach(el => {
        const name = el.getAttribute('data-name') || '';
        const code = el.getAttribute('data-code') || '';
        const merk = el.getAttribute('data-merk') || '';

        if (q === '' || name.includes(q) || code.includes(q) || merk.includes(q) || el.innerText.toLowerCase().includes(q)) {
            el.classList.remove('hidden');
            visibleCount++;
        } else {
            el.classList.add('hidden');
        }
    });

    if (noMsg) {
        noMsg.classList.toggle('hidden', visibleCount > 0);
    }
}

function clearSearchBarang() {
    const inp = document.getElementById('input-search-barang');
    inp.value = '';
    filterBarangOptions('');
    inp.focus();
}

function selectBarang(id, name, code, merk) {
    document.getElementById('val_barang_id').value = id;
    currentSelectedBarangName = id ? name : '';

    const lbl = document.getElementById('label-barang-text');
    if (id === '') {
        lbl.innerHTML = `
            <span class="text-base shrink-0">🔧</span>
            <span class="font-bold text-slate-800 truncate">${name}</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 shrink-0">Fasilitas</span>
        `;
    } else {
        lbl.innerHTML = `
            <span class="text-base shrink-0">📦</span>
            <span class="font-bold text-slate-800 truncate">${name}</span>
            <span class="font-mono text-[10px] text-amber-700 bg-amber-100/70 px-1.5 py-0.5 rounded font-bold shrink-0">${code}</span>
        `;
    }

    closePopoverBarang();

    // Auto-suggest judul laporan
    updateSmartJudulSuggestion();
}

// ── LANGKAH 3: NAMA LAPORAN AUTO-SUGGEST & USER INPUT ───────────────────────
function updateSmartJudulSuggestion() {
    const inputJudul = document.getElementById('input_judul_laporan');
    const btnSuggest = document.getElementById('btn-use-suggest');

    let suggested = '';
    if (currentSelectedBarangName && currentSelectedRoomName) {
        suggested = `${currentSelectedBarangName} Rusak di ${currentSelectedRoomName}`;
    } else if (currentSelectedBarangName) {
        suggested = `Kerusakan ${currentSelectedBarangName}`;
    } else if (currentSelectedRoomName) {
        suggested = `Kerusakan Fasilitas di ${currentSelectedRoomName}`;
    }

    if (suggested) {
        if (!isJudulManuallyEdited || inputJudul.value.trim() === '') {
            inputJudul.value = suggested;
            inputJudul.classList.add('bg-amber-50/50');
            setTimeout(() => inputJudul.classList.remove('bg-amber-50/50'), 600);
            btnSuggest.classList.add('hidden');
        } else {
            // User sudah ketik judul custom, tampilkan tombol opsi saran
            btnSuggest.classList.remove('hidden');
        }
    }
}

function applySuggestedTitle() {
    isJudulManuallyEdited = false;
    updateSmartJudulSuggestion();
    document.getElementById('btn-use-suggest').classList.add('hidden');
}

function onJudulUserChange() {
    const val = document.getElementById('input_judul_laporan').value.trim();
    isJudulManuallyEdited = (val !== '');
}

// ── CLICK OUTSIDE LISTENER TO CLOSE DROPDOWNS ───────────────────────────────
document.addEventListener('click', function(e) {
    if (!e.target.closest('#dropdown-ruangan-box')) {
        closePopoverRuangan();
    }
    if (!e.target.closest('#dropdown-barang-box')) {
        closePopoverBarang();
    }
});

// ESC KEY LISTENER
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePopoverRuangan();
        closePopoverBarang();
    }
});

// ── MODAL UPDATE LOGIC ──────────────────────────────────────────────────────
function openModalUpdate(item) {
    document.getElementById('update-judul').innerText = item.judul_laporan + ' (' + item.kode_perbaikan + ')';
    document.getElementById('update-status').value = item.status;
    document.getElementById('update-teknisi').value = item.teknisi_pihak || '';
    document.getElementById('update-biaya').value = item.biaya_realisasi || '0';
    document.getElementById('update-tindakan').value = item.tindakan_perbaikan || '';
    document.getElementById('form-update').action = '<?= url("kelola-sarpras/pemeliharaan/update-status/") ?>' + item.id;
    const m = document.getElementById('modal-update');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');

    // Format input biaya realisasi
    let inputBiaya = document.getElementById('update-biaya');
    if (item.biaya_realisasi && item.biaya_realisasi != "0" && item.biaya_realisasi != "0.00") {
        inputBiaya.value = parseInt(item.biaya_realisasi).toString();
    } else {
        inputBiaya.value = '0';
    }
    formatRupiah(inputBiaya);
}

function closeModalUpdate() {
    const m = document.getElementById('modal-update');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}

function formatRupiah(el) {
    let value = el.value.replace(/[^,\d]/g, '');
    let split = value.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    el.value = rupiah;
}
</script>
