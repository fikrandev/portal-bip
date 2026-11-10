<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
            <svg class="w-32 h-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6.75h1.5m-1.5 3h1.5m-1.5 3h1.5" />
            </svg>
        </div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <a href="<?= url('kelola-sarpras/ruangan') ?>" class="text-slate-400 hover:text-primary-600 transition-colors" title="Kembali ke Data Ruangan">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight"><?= e($ruangan['nama_ruangan']) ?></h2>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-primary-50 text-primary-700 border border-primary-100"><?= e($ruangan['jenis_ruangan']) ?></span>
                    <?php if (!empty($ruangan['unit'])): ?>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">Unit: <?= e($ruangan['unit']) ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-slate-500 font-medium">
                    Gedung: <span class="text-slate-700 font-semibold"><?= e($ruangan['nama_bangunan'] ?? $ruangan['lokasi_gedung'] ?? '-') ?></span> &bull; 
                    Lantai: <span class="text-slate-700 font-semibold"><?= (int)($ruangan['lantai'] ?? 1) ?></span> &bull;
                    Penanggung Jawab: <span class="text-slate-700 font-semibold"><?= e($ruangan['penanggung_jawab'] ?: '-') ?></span>
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" onclick="openModalInputManual()" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition-all shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Input Manual Barang Baru</span>
                </button>
                <button type="button" onclick="openModalDistribusi()" class="flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition-all shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    <span>Ambil dari Inventaris</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Aset -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap justify-between items-center gap-3">
            <div>
                <h3 class="font-bold text-slate-800">Daftar Aset di Ruangan Ini</h3>
                <p class="text-[11px] text-slate-500">Barang yang diinput manual dari ruangan ini otomatis tercatat di Master Inventaris dan terdistribusi ke ruangan ini.</p>
            </div>
            <div class="flex items-center gap-2">
                <?php
                $totalUnitRuangan = 0;
                foreach ($distribusi as $itemD) {
                    $totalUnitRuangan += (int)($itemD['jumlah'] ?? 0);
                }
                ?>
                <div class="text-xs font-bold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
                    <?= count($distribusi) ?> Jenis Barang &bull; <?= $totalUnitRuangan ?> Total Unit
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Barang</th>
                        <th class="py-3.5 px-4">Kode & Seri</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-center">Jumlah</th>
                        <th class="py-3.5 px-4 text-center">Kondisi</th>
                        <th class="py-3.5 px-4">Keterangan / Posisi</th>
                        <th class="py-3.5 px-4 text-center">Tgl Penempatan</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($distribusi)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-3">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                                </div>
                                <div class="text-slate-600 font-bold text-sm mb-1">Belum ada aset/barang di ruangan ini</div>
                                <p class="text-slate-400 text-xs mb-4">Anda dapat menginput barang baru secara manual langsung di ruangan ini atau mengambil dari stok inventaris.</p>
                                <div class="inline-flex flex-wrap items-center justify-center gap-2">
                                    <button type="button" onclick="openModalInputManual()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        Input Manual Barang Baru
                                    </button>
                                    <button type="button" onclick="openModalDistribusi()" class="px-4 py-2 bg-primary-50 hover:bg-primary-100 text-primary-700 border border-primary-200 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5">
                                        Ambil dari Inventaris
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($distribusi as $d): ?>
                            <?php
                            $fotos = [];
                            if (!empty($d['foto'])) {
                                $decoded = json_decode($d['foto'], true);
                                $fotos = is_array($decoded) ? $decoded : [$d['foto']];
                            }
                            $thumbUrl = !empty($fotos[0]) ? url($fotos[0]) : null;
                            ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <?php if ($thumbUrl): ?>
                                            <img src="<?= $thumbUrl ?>" alt="" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0 bg-slate-100">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm"><?= e($d['nama_barang']) ?></div>
                                            <div class="text-[10px] text-slate-400"><?= e($d['merk_model'] ?: '-') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-primary-700"><?= e($d['kode_barang']) ?></div>
                                    <?php if (!empty($d['nomor_seri'])): ?>
                                        <div class="text-[10px] font-mono text-slate-400">SN: <?= e($d['nomor_seri']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold"><?= e($d['nama_kategori'] ?? '-') ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-bold text-slate-800 text-sm"><?= (int)$d['jumlah'] ?></span>
                                    <span class="text-[10px] text-slate-400 block"><?= e($d['satuan']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (($d['kondisi'] ?? 'Baik') === 'Baik'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Baik</span>
                                    <?php elseif (($d['kondisi'] ?? '') === 'Rusak Ringan'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Rusak Ringan</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Rusak Berat</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <?= e($d['keterangan'] ?: '-') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-500">
                                    <?= !empty($d['tanggal_distribusi']) ? date('d/m/Y', strtotime($d['tanggal_distribusi'])) : '-' ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?= url('kelola-sarpras/barang/edit/' . $d['barang_id']) ?>" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Data Master Barang">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </a>
                                        <form action="<?= url('kelola-sarpras/distribusi/delete/' . $d['id']) ?>" method="POST" onsubmit="return ModalHelper.confirm(event, this, 'Tarik Barang', 'Apakah Anda yakin ingin menarik/menghapus barang <?= htmlspecialchars($d['nama_barang'], ENT_QUOTES) ?> dari ruangan ini?', 'warning');">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Tarik dari Ruangan">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
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

<!-- Modal Input Manual Barang Baru (Langsung Masuk Inventaris & Auto Distribusi ke Ruangan Ini) -->
<div id="modalInputManual" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeModalInputManual()"></div>
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden transform transition-all scale-95 opacity-0 duration-200 flex flex-col max-h-[92vh]" id="modalInputManualContent">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">📦</span>
                            <span>Input Manual Barang di <?= e($ruangan['nama_ruangan']) ?></span>
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Data otomatis tersimpan ke Master Inventaris & langsung terdistribusi ke ruangan ini</p>
                    </div>
                    <button onclick="closeModalInputManual()" type="button" class="text-slate-400 hover:text-rose-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-6 overflow-y-auto flex-1">
                    <form action="<?= url('kelola-sarpras/barang/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-5" id="formInputManualRuangan">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="ruangan_id" value="<?= (int)$ruangan['id'] ?>">
                        <input type="hidden" name="auto_distribusi" value="1">
                        <input type="hidden" name="redirect_to" value="<?= url('kelola-sarpras/ruangan/detail/' . $ruangan['id']) ?>">

                        <!-- Info Ruangan Tujuan & Preview Kode -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="bg-emerald-50 rounded-2xl p-3.5 border border-emerald-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 font-bold">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Penempatan Otomatis</p>
                                    <p class="text-sm font-bold text-emerald-950 truncate"><?= e($ruangan['nama_ruangan']) ?></p>
                                    <p class="text-[10px] text-emerald-700 truncate"><?= e($ruangan['nama_bangunan'] ?? $ruangan['lokasi_gedung'] ?? '-') ?> &bull; Lt. <?= (int)($ruangan['lantai'] ?? 1) ?></p>
                                </div>
                            </div>

                            <div class="bg-primary-50 rounded-2xl p-3.5 border border-primary-100 flex flex-col justify-center">
                                <p class="text-[10px] font-bold text-primary-600 uppercase tracking-wider">Preview Kode Barang</p>
                                <p class="text-base sm:text-lg font-mono font-bold text-primary-900 mt-0.5" id="previewKodeManual">... . ... . ... . 001</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Golongan <span class="text-rose-500">*</span></label>
                                <select name="golongan_id" id="selGolonganManual" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                    <option value="" data-kode="00">-- Pilih --</option>
                                    <?php foreach ($golonganList as $g): ?>
                                        <option value="<?= $g['id'] ?>" data-kode="<?= e($g['kode']) ?>"><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok <span class="text-rose-500">*</span></label>
                                <select name="kelompok_id" id="selKelompokManual" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                    <option value="" data-kode="00">-- Pilih --</option>
                                    <?php foreach ($kelompokList as $k): ?>
                                        <option value="<?= $k['id'] ?>" data-golongan="<?= $k['golongan_id'] ?>" data-kode="<?= e($k['kode']) ?>"><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                                <select name="asal_anggaran_id" id="selAnggaranManual" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                    <option value="" data-kode="00">-- Pilih --</option>
                                    <?php foreach ($asalAnggaranList as $a): ?>
                                        <option value="<?= $a['id'] ?>" data-kode="<?= e($a['kode']) ?>"><?= e($a['kode']) ?> - <?= e($a['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Unit / Jenjang <span class="text-rose-500">*</span></label>
                                <?php $defaultUnit = $ruangan['unit'] ?? 'SD'; ?>
                                <select name="unit" id="selUnitManual" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-semibold text-slate-800">
                                    <?php foreach (['SD', 'SMP', 'SMA', 'PAUD', 'Yayasan', 'Semua'] as $u): ?>
                                        <option value="<?= $u ?>" <?= $defaultUnit === $u ? 'selected' : '' ?>><?= $u === 'Semua' ? 'Semua / Bersama' : $u ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis / Kategori Sarpras <span class="text-rose-500">*</span></label>
                                <select name="kategori_id" id="selKategoriManual" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-semibold text-slate-800">
                                    <option value="">-- Pilih Jenis / Kategori --</option>
                                    <?php foreach ($kategoriList as $k): ?>
                                        <option value="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_barang" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500" placeholder="Contoh: Meja Siswa / Papan Tulis / AC">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Merk / Model</label>
                                <input type="text" name="merk_model" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500" placeholder="Contoh: Olympic / Panasonic">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Seri</label>
                                <input type="text" name="nomor_seri" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-mono" placeholder="Opsional">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                                <input type="number" name="jumlah" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                                <select name="satuan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                    <?php foreach ($satuanList as $s): ?>
                                        <option value="<?= e($s['nama_satuan']) ?>"><?= e($s['nama_satuan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Perolehan (Rp)</label>
                                <input type="text" id="inputHargaManual" name="harga_perolehan_formatted" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-bold text-right">
                                <input type="hidden" name="harga_perolehan" id="hiddenHargaManual" value="0">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal_perolehan" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat (Tahun)</label>
                                <input type="number" name="masa_manfaat" value="5" min="0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi</label>
                                <select name="kondisi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Posisi di Ruangan</label>
                                <input type="text" name="keterangan" placeholder="Contoh: Pojok depan kelas, Meja Guru, Dinding utara..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            </div>

                            <div class="sm:col-span-2 pt-2">
                                <?php 
                                $uploaderId = 'foto_modal_manual_ruangan';
                                $existingFotos = [];
                                $isMobile = false;
                                include BASE_PATH . '/modules/kelola-sarpras/views/barang/_foto_uploader.php';
                                ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 shrink-0 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModalInputManual()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors">Batal</button>
                    <button type="submit" form="formInputManualRuangan" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span>Simpan ke Inventaris & Ruangan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Distribusi Barang (Ambil dari Inventaris) -->
<div id="modal-distribusi" class="fixed inset-0 z-[100] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModalDistribusi()"></div>
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative z-10 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800">Ambil / Distribusi Barang dari Inventaris</h3>
                <p class="text-[11px] text-slate-500">Pilih dari stok barang yang sudah ada di Master Inventaris</p>
            </div>
            <button onclick="closeModalDistribusi()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors text-xl font-bold">&times;</button>
        </div>
        <form action="<?= url('kelola-sarpras/distribusi/store') ?>" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <?= CSRF::field() ?>
            <input type="hidden" name="ruangan_id" value="<?= $ruangan['id'] ?>">
            
            <div id="distribusi-items-container" class="p-6 overflow-y-auto space-y-6 flex-1 bg-slate-50/50">
                <!-- Item Row Template -->
                <div class="distribusi-item bg-white p-4 rounded-2xl border border-slate-200 relative shadow-sm">
                    <button type="button" class="btn-remove-item absolute -top-3 -right-3 w-8 h-8 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm hover:bg-rose-500 hover:text-white transition-colors hidden">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <div class="space-y-4 text-xs">
                        <div class="relative custom-dropdown-container">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pilih Aset/Barang <span class="text-rose-500">*</span></label>
                            
                            <input type="hidden" name="barang_id[]" class="hidden-barang-id" required>
                            
                            <button type="button" class="dropdown-trigger w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none text-left flex justify-between items-center transition-colors">
                                <span class="dropdown-selected-text truncate">-- Pilih Barang --</span>
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            
                            <div class="dropdown-panel absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl hidden flex-col overflow-hidden">
                                <div class="p-2 border-b border-slate-100 bg-slate-50">
                                    <input type="text" placeholder="Ketik nama / kode..." autocomplete="off" class="search-barang-input w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 focus:border-primary-500 outline-none">
                                </div>
                                <div class="max-h-48 overflow-y-auto">
                                    <?php foreach ($barangTersedia as $b): $sisa = $b['jumlah'] - ($b['dipakai'] ?? 0); ?>
                                        <div class="option-barang px-3 py-2.5 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0" 
                                            data-id="<?= $b['id'] ?>" 
                                            data-search="<?= strtolower($b['nama_barang'] . ' ' . $b['kode_barang']) ?>"
                                            data-sisa="<?= $sisa ?>"
                                            data-satuan="<?= e($b['satuan']) ?>">
                                            <div class="font-bold text-slate-800 text-sm"><?= e($b['nama_barang']) ?></div>
                                            <div class="text-[10px] text-slate-500 flex justify-between mt-0.5">
                                                <span><?= e($b['kode_barang']) ?></span>
                                                <span class="text-primary-600 font-bold bg-primary-50 px-1.5 py-0.5 rounded">Sisa: <?= $sisa ?> <?= e($b['satuan']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($barangTersedia)): ?>
                                        <div class="px-4 py-3 text-xs text-rose-500 text-center">Tidak ada stok barang yang tersedia.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jumlah <span class="text-rose-500">*</span></label>
                                <input type="number" name="jumlah[]" required min="1" value="1" class="distribusi-jumlah w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                                <p class="text-[10px] text-slate-400 mt-1 distribusi-info-sisa"></p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kondisi <span class="text-rose-500">*</span></label>
                                <select name="kondisi[]" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan / Posisi di Ruangan</label>
                            <input type="text" name="keterangan[]" placeholder="Contoh: Meja Guru, Pojok Kanan Depan..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-white z-10">
                <button type="button" onclick="tambahBarisDistribusi()" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100 text-sm font-bold rounded-xl transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg> Tambah Baris
                </button>
                <div class="flex gap-2">
                    <button type="button" onclick="closeModalDistribusi()" class="px-5 py-2.5 bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-bold rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white hover:bg-primary-700 text-sm font-bold rounded-xl transition-all shadow-lg shadow-primary-500/30">Simpan Distribusi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal Input Manual Barang Baru
    function openModalInputManual() {
        const uploader = document.getElementById('foto_modal_manual_ruangan');
        if (uploader && uploader.clearPhotos) {
            uploader.clearPhotos();
        }

        const modal = document.getElementById('modalInputManual');
        const modalContent = document.getElementById('modalInputManualContent');
        modal.classList.remove('hidden');

        if (typeof filterKelompokManual === 'function') {
            filterKelompokManual(document.getElementById('selKelompokManual')?.value || '');
        }

        void modal.offsetWidth;
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }

    function closeModalInputManual() {
        const modal = document.getElementById('modalInputManual');
        const modalContent = document.getElementById('modalInputManualContent');

        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // Format Rupiah pada Modal Input Manual
    const inputHargaManual = document.getElementById('inputHargaManual');
    const hiddenHargaManual = document.getElementById('hiddenHargaManual');
    if (inputHargaManual && hiddenHargaManual) {
        inputHargaManual.addEventListener('input', function() {
            let value = this.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            this.value = rupiah;
            hiddenHargaManual.value = value.replace(/\./g, '').replace(',', '.');
        });
    }

    // Dependent Golongan & Kelompok + Preview Kode
    function initDependentGolonganKelompokManual(selGolId, selKelId, onUpdatePreview = null) {
        const selGol = document.getElementById(selGolId);
        const selKel = document.getElementById(selKelId);
        if (!selGol || !selKel) return () => {};

        if (!selKel._masterKelompokOptions) {
            selKel._masterKelompokOptions = Array.from(selKel.options)
                .filter(opt => opt.value !== '')
                .map(opt => ({
                    value: opt.value,
                    text: opt.textContent,
                    golonganId: opt.getAttribute('data-golongan') || '',
                    kode: opt.getAttribute('data-kode') || ''
                }));
        }

        function applyFilter(selectedKelompokId = null) {
            const golId = selGol.value;
            const targetId = (selectedKelompokId !== null && selectedKelompokId !== undefined)
                ? String(selectedKelompokId)
                : selKel.value;

            selKel.innerHTML = '';

            if (!golId) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.dataset.kode = '...';
                opt.textContent = '-- Pilih Golongan Terlebih Dahulu --';
                selKel.appendChild(opt);
                selKel.disabled = true;
                selKel.value = '';
                if (onUpdatePreview) onUpdatePreview();
                return;
            }

            const filtered = selKel._masterKelompokOptions.filter(item => String(item.golonganId) === String(golId));

            if (filtered.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.dataset.kode = '...';
                opt.textContent = '-- Belum Ada Kelompok di Golongan Ini --';
                selKel.appendChild(opt);
                selKel.disabled = true;
                selKel.value = '';
            } else {
                selKel.disabled = false;
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.dataset.kode = '...';
                placeholder.textContent = '-- Pilih Kelompok --';
                selKel.appendChild(placeholder);

                let hasSelected = false;
                filtered.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.value;
                    opt.dataset.kode = item.kode;
                    opt.dataset.golongan = item.golonganId;
                    opt.textContent = item.text;
                    if (targetId && String(item.value) === targetId) {
                        opt.selected = true;
                        hasSelected = true;
                    }
                    selKel.appendChild(opt);
                });

                selKel.value = (hasSelected && targetId) ? targetId : '';
            }

            if (onUpdatePreview) onUpdatePreview();
        }

        selGol.addEventListener('change', function() {
            applyFilter('');
        });

        selKel.addEventListener('change', function() {
            if (onUpdatePreview) onUpdatePreview();
        });

        applyFilter(selKel.value || null);
        return applyFilter;
    }

    const selGolManual = document.getElementById('selGolonganManual');
    const selKelManual = document.getElementById('selKelompokManual');
    const selAngManual = document.getElementById('selAnggaranManual');
    const previewKodeManual = document.getElementById('previewKodeManual');

    function updatePreviewKodeManual() {
        const kGol = selGolManual?.options[selGolManual.selectedIndex]?.dataset?.kode || '...';
        const kKel = selKelManual?.options[selKelManual.selectedIndex]?.dataset?.kode || '...';
        const kAng = selAngManual?.options[selAngManual.selectedIndex]?.dataset?.kode || '...';
        if (previewKodeManual) {
            previewKodeManual.textContent = `${kGol}.${kKel}.${kAng}.001`;
        }
    }

    const filterKelompokManual = initDependentGolonganKelompokManual('selGolonganManual', 'selKelompokManual', updatePreviewKodeManual);
    if (selAngManual) {
        selAngManual.addEventListener('change', updatePreviewKodeManual);
    }

    document.addEventListener("DOMContentLoaded", function() {
        initCustomDropdowns();

        const params = new URLSearchParams(window.location.search);
        if (params.get('action') === 'input-manual') {
            openModalInputManual();
        }
    });

    function initCustomDropdowns() {
        const container = document.getElementById('distribusi-items-container');
        if (!container) return;

        container.addEventListener('click', function(e) {
            const trigger = e.target.closest('.dropdown-trigger');
            if (trigger) {
                const parent = trigger.closest('.custom-dropdown-container');
                const dropdown = parent.querySelector('.dropdown-panel');
                const inputSearch = parent.querySelector('.search-barang-input');
                const options = parent.querySelectorAll('.option-barang');

                document.querySelectorAll('.dropdown-panel').forEach(p => {
                    if (p !== dropdown) p.classList.add('hidden');
                });

                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    inputSearch.focus();
                    inputSearch.value = '';
                    options.forEach(opt => opt.style.display = 'block');
                }
                return;
            }

            const option = e.target.closest('.option-barang');
            if (option) {
                const parent = option.closest('.custom-dropdown-container');
                const itemRow = option.closest('.distribusi-item');
                
                const id = option.getAttribute('data-id');
                const name = option.querySelector('.font-bold').innerText;
                const sisa = option.getAttribute('data-sisa');
                const satuan = option.getAttribute('data-satuan');

                parent.querySelector('.dropdown-selected-text').innerText = name;
                parent.querySelector('.hidden-barang-id').value = id;
                
                const jumlahInput = itemRow.querySelector('.distribusi-jumlah');
                const sisaInfo = itemRow.querySelector('.distribusi-info-sisa');
                
                jumlahInput.max = sisa;
                jumlahInput.value = 1;
                sisaInfo.innerText = `Maksimal: ${sisa} ${satuan}`;
                
                parent.querySelector('.dropdown-panel').classList.add('hidden');
            }
        });

        container.addEventListener('input', function(e) {
            if (e.target.classList.contains('search-barang-input')) {
                const val = e.target.value.toLowerCase();
                const parent = e.target.closest('.custom-dropdown-container');
                const options = parent.querySelectorAll('.option-barang');
                options.forEach(opt => {
                    if (opt.getAttribute('data-search').includes(val)) {
                        opt.style.display = 'block';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            }
        });

        container.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove-item');
            if (btn) {
                const row = btn.closest('.distribusi-item');
                row.remove();
                updateRemoveButtons();
            }
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.custom-dropdown-container')) {
                document.querySelectorAll('.dropdown-panel').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });
    }

    function tambahBarisDistribusi() {
        const container = document.getElementById('distribusi-items-container');
        const rows = container.querySelectorAll('.distribusi-item');
        if (rows.length >= 10) {
            alert('Maksimal penambahan 10 baris dalam satu waktu.');
            return;
        }

        const firstRow = rows[0];
        const newRow = firstRow.cloneNode(true);
        
        newRow.querySelector('.hidden-barang-id').value = '';
        newRow.querySelector('.dropdown-selected-text').innerText = '-- Pilih Barang --';
        newRow.querySelector('.distribusi-jumlah').value = '1';
        newRow.querySelector('.distribusi-jumlah').removeAttribute('max');
        newRow.querySelector('.distribusi-info-sisa').innerText = '';
        newRow.querySelector('select[name="kondisi[]"]').value = 'Baik';
        newRow.querySelector('input[name="keterangan[]"]').value = '';
        
        newRow.querySelector('.dropdown-panel').classList.add('hidden');
        newRow.querySelector('.search-barang-input').value = '';
        newRow.querySelectorAll('.option-barang').forEach(opt => opt.style.display = 'block');
        
        container.appendChild(newRow);
        updateRemoveButtons();
        container.scrollTop = container.scrollHeight;
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.distribusi-item');
        rows.forEach((row) => {
            const btn = row.querySelector('.btn-remove-item');
            if (rows.length > 1) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    }

    function openModalDistribusi() {
        const modal = document.getElementById('modal-distribusi');
        modal.classList.remove('hidden');
    }

    function closeModalDistribusi() {
        const modal = document.getElementById('modal-distribusi');
        modal.classList.add('hidden');
    }
</script>
