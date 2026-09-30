<?php
/**
 * Master Ruangan & Fasilitas Gedung
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
                <span>Master Ruangan &amp; Fasilitas Gedung</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Data ruang kelas, laboratorium, aula, perpustakaan, dan area fasilitas kampus per jenjang
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button onclick="openModalRuangan()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Ruangan Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Live Search Bar -->
    <div class="p-4 bg-white rounded-3xl border border-primary-100 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <input type="text" 
                   id="searchRuanganInput" 
                   placeholder="Live Search nama ruangan, kode, jenis, pj..." 
                   onkeyup="handleSearchRuangan(this.value)"
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all text-slate-800">
        </div>

        <div class="flex items-center gap-2">
            <select id="filterGedungSelect" 
                    onchange="handleFilterGedung(this.value)" 
                    class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 font-semibold">
                <option value="">-- Semua Gedung / Lokasi --</option>
                <?php if (!empty($bangunanList)): ?>
                    <?php foreach ($bangunanList as $b): ?>
                        <option value="<?= e($b['nama_bangunan']) ?>"><?= e($b['nama_bangunan']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama &amp; Jenis Ruangan</th>
                        <th class="py-3.5 px-4">Lokasi Gedung</th>
                        <th class="py-3.5 px-4">Dimensi &amp; Luas</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4 text-center">Kapasitas</th>
                        <th class="py-3.5 px-4">Penanggung Jawab</th>
                        <th class="py-3.5 px-4 text-center">Aset Tersimpan</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                Belum ada data ruangan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($items as $r): 
                            $luasText = ($r['luas'] > 0) ? number_format($r['luas'], 1, ',', '.') . ' m²' : '-';
                            $dimText = ($r['panjang'] > 0 && $r['lebar'] > 0) ? number_format($r['panjang'], 1, ',', '.') . ' &times; ' . number_format($r['lebar'], 1, ',', '.') . ' m' : '';
                            $gedungNama = $r['nama_bangunan'] ?? $r['lokasi_gedung'] ?? '';
                            $searchKeywords = strtolower($r['nama_ruangan'] . ' ' . $r['kode_ruangan'] . ' ' . ($r['jenis_ruangan'] ?? '') . ' ' . $gedungNama . ' ' . ($r['penanggung_jawab'] ?? ''));
                        ?>
                            <tr class="ruangan-table-row hover:bg-slate-50/70 transition-colors" 
                                data-search="<?= e($searchKeywords) ?>" 
                                data-gedung="<?= e($gedungNama) ?>">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= e($r['nama_ruangan']) ?></div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200"><?= e($r['jenis_ruangan'] ?? 'Ruang Kelas') ?></span>
                                        <span class="text-[10px] font-mono text-slate-400"><?= e($r['kode_ruangan']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-600">
                                    <div class="font-bold text-slate-800"><?= e($r['nama_bangunan'] ?? $r['lokasi_gedung']) ?></div>
                                    <div class="text-[11px] text-primary-600"><?= !empty($r['lantai']) ? 'Lantai ' . (int)$r['lantai'] : e($r['lokasi_gedung']) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800"><?= $luasText ?></div>
                                    <?php if ($dimText): ?>
                                        <div class="text-[10px] text-slate-400"><?= $dimText ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                        <?= e($r['unit']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                    <?= $r['kapasitas'] > 0 ? $r['kapasitas'] . ' Orang' : '-' ?>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <?= e($r['penanggung_jawab'] ?? '-') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-primary-50 text-primary-700">
                                        <?= $r['total_barang'] ?> Aset (<?= $r['total_qty'] ?> Unit)
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="<?= url('kelola-sarpras/ruangan/detail/' . $r['id']) ?>" class="p-1.5 rounded-lg text-slate-500 hover:text-primary-600 hover:bg-primary-50" title="Kelola Aset di Ruangan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.828.672-1.5 1.5-1.5h5.25c.828 0 1.5.672 1.5 1.5v8.25c0 .828-.672 1.5-1.5 1.5h-5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-3.75a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" /></svg>
                                        </a>
                                        <button onclick="editRuangan(<?= htmlspecialchars(json_encode($r)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50" title="Edit Ruangan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                        </button>
                                        <form action="<?= url('kelola-sarpras/ruangan/delete/' . $r['id']) ?>" method="POST" onsubmit="return confirm('Hapus ruangan <?= e($r['nama_ruangan']) ?>?')">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
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

<!-- Modal Form Ruangan -->
<div id="modal-ruangan" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-primary-100 overflow-hidden flex flex-col max-h-[92vh] transform scale-95 transition-all duration-200">
        <!-- Header (Fixed at top) -->
        <div class="px-6 py-4 border-b border-primary-100 bg-primary-50 to-primary-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-2xl bg-primary-600 text-white shadow-md shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-800" id="modal-ruangan-title">Tambah Ruangan Baru</h3>
                    <p class="text-xs text-slate-500">Kelola master ruang, gedung, dan penanggung jawab</p>
                </div>
            </div>
            <button onclick="closeModalRuangan()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors text-xl font-bold">&times;</button>
        </div>

        <form action="<?= url('kelola-sarpras/ruangan/store') ?>" method="POST" class="flex flex-col flex-1 overflow-hidden min-h-0">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="ruangan-id" value="">

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar text-xs">
                <!-- Gedung / Bangunan Lokasi (Sesuai Permintaan seperti Tambah Bangunan pilih Tanah) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Gedung / Bangunan Lokasi <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-primary-600 font-semibold lowercase">pilih gedung tempat ruangan ini berada</span>
                    </label>
                    <select name="bangunan_id" 
                            id="ruangan-bangunan-id" 
                            required 
                            onchange="onBangunanChange(this.value)"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                        <option value="">-- Pilih Gedung / Bangunan --</option>
                        <?php if (!empty($bangunanList)): ?>
                            <?php foreach ($bangunanList as $b): ?>
                                <option value="<?= $b['id'] ?>">
                                    <?= e($b['nama_bangunan']) ?> (<?= e($b['kode_bangunan']) ?>)<?= !empty($b['nama_tanah']) ? ' &bull; Lokasi: ' . e($b['nama_tanah']) : '' ?> (<?= (int)$b['jumlah_lantai'] ?> Lt)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Ruangan / Fasilitas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_ruangan" id="ruangan-nama" required placeholder="Contoh: Lab Bahasa, Ruang Kelas 2B, Perpustakaan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Ruangan <span class="text-rose-500">*</span></label>
                        <select name="jenis_ruangan" id="ruangan-jenis" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="Ruang Kelas">Ruang Kelas (Belajar)</option>
                            <option value="Ruang Laboratorium">Ruang Laboratorium</option>
                            <option value="Ruang Kantor">Ruang Kantor / Administrasi</option>
                            <option value="Ruang Guru">Ruang Guru</option>
                            <option value="Ruang Pimpinan">Ruang Kepala Sekolah / Pimpinan</option>
                            <option value="Ruang Perpustakaan">Ruang Perpustakaan</option>
                            <option value="Ruang UKS">Ruang UKS / Medis</option>
                            <option value="Ruang Ibadah">Ruang Ibadah / Masjid</option>
                            <option value="Ruang Aula">Ruang Aula / Serbaguna</option>
                            <option value="Ruang Konseling / BK">Ruang Bimbingan Konseling (BK)</option>
                            <option value="Ruang OSIS">Ruang OSIS / Ekstrakurikuler</option>
                            <option value="Gudang">Gudang / Logistik</option>
                            <option value="Toilet">Toilet / Sanitasi</option>
                            <option value="Kantin">Kantin / Dapur</option>
                            <option value="Lainnya">Fasilitas Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Posisi Lantai <span class="text-rose-500">*</span></label>
                        <select name="lantai" id="ruangan-lantai" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                            <option value="1">Lantai 1</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kode Ruangan</label>
                        <input type="text" name="kode_ruangan" id="ruangan-kode" placeholder="Auto jika kosong" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 font-mono focus:bg-white focus:border-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unit / Jenjang</label>
                        <select name="unit" id="ruangan-unit" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                            <option value="PAUD">PAUD</option>
                            <option value="Yayasan">Yayasan</option>
                            <option value="Semua">Semua / Fasilitas Umum</option>
                        </select>
                    </div>
                </div>

                <!-- Dimensi Ruangan: Panjang, Lebar, Luas & Kapasitas -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Panjang (m)</label>
                            <input type="number" step="0.01" id="ruangan-panjang" name="panjang" placeholder="0.00" oninput="calculateLuasMasterRuang()" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lebar (m)</label>
                            <input type="number" step="0.01" id="ruangan-lebar" name="lebar" placeholder="0.00" oninput="calculateLuasMasterRuang()" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-primary-800 mb-1">Luas (m²)</label>
                            <input type="number" step="0.01" id="ruangan-luas" name="luas" placeholder="0.00" class="w-full px-2.5 py-1.5 bg-primary-50 border border-primary-300 rounded-xl text-xs font-black text-primary-900 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kapasitas (Org)</label>
                        <input type="number" name="kapasitas" id="ruangan-kapasitas" value="30" min="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                    </div>
                </div>

                <!-- Penanggung Jawab Dropdown Search -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Penanggung Jawab / Pengelola Ruang</span>
                        <span class="text-[10px] text-primary-600 font-semibold lowercase">dropdown search data pegawai</span>
                    </label>
                    <select name="penanggung_jawab" 
                            id="ruangan-pj" 
                            class="searchable-select w-full" 
                            data-placeholder="-- Pilih Penanggung Jawab (Guru / Pegawai) --" 
                            data-search-placeholder="Cari nama guru, NIY, unit tugas, jabatan...">
                        <option value="">-- Tanpa Penanggung Jawab --</option>
                        <?php if (!empty($pegawaiList)): ?>
                            <?php foreach ($pegawaiList as $p): 
                                $namaLengkap = $p['nama'];
                                if (!empty($p['gelar']) && !str_contains($p['nama'], $p['gelar'])) {
                                    $namaLengkap .= ', ' . $p['gelar'];
                                }
                                $badge = $p['unit_tugas'] ?? $p['jabatan'] ?? 'Pegawai';
                                $subtext = !empty($p['niy']) ? 'NIY: ' . $p['niy'] : (!empty($p['jabatan']) ? $p['jabatan'] : '');
                                $fotoUrl = !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '';
                            ?>
                                <option value="<?= e($namaLengkap) ?>" 
                                        data-badge="<?= e($badge) ?>" 
                                        data-subtext="<?= e($subtext) ?>"
                                        data-unit="<?= e($p['unit_tugas'] ?? '') ?>"
                                        data-image="<?= e($fotoUrl) ?>">
                                    <?= e($namaLengkap) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Fungsi (opsional)</label>
                    <input type="text" name="keterangan" id="ruangan-keterangan" placeholder="Fasilitas AC, proyektor, papan pintar..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                </div>
            </div>

            <!-- Sticky Footer (Always visible) -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/90 backdrop-blur-sm flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="closeModalRuangan()" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-100 transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-500/20 transition-all">Simpan Ruangan</button>
            </div>
        </form>
    </div>
</div>

<script>
const bangunanData = <?= json_encode($bangunanList ?? []) ?>;

function onBangunanChange(bangunanId, selectedLantai = 1) {
    const sel = document.getElementById('ruangan-lantai');
    if (!sel) return;
    sel.innerHTML = '';
    
    let maxLt = 1;
    if (bangunanId) {
        const found = bangunanData.find(b => String(b.id) === String(bangunanId));
        if (found && found.jumlah_lantai) {
            maxLt = Math.max(1, parseInt(found.jumlah_lantai));
        }
    }
    
    for (let i = 1; i <= maxLt; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = `Lantai ${i}`;
        if (parseInt(i) === parseInt(selectedLantai)) opt.selected = true;
        sel.appendChild(opt);
    }
}

function calculateLuasMasterRuang() {
    const p = parseFloat(document.getElementById('ruangan-panjang').value) || 0;
    const l = parseFloat(document.getElementById('ruangan-lebar').value) || 0;
    document.getElementById('ruangan-luas').value = (p * l > 0) ? (p * l).toFixed(2) : '';
}

function openModalRuangan() {
    document.getElementById('modal-ruangan-title').innerText = 'Tambah Ruangan Baru';
    document.getElementById('ruangan-id').value = '';
    document.getElementById('ruangan-nama').value = '';
    document.getElementById('ruangan-jenis').value = 'Ruang Kelas';
    document.getElementById('ruangan-kode').value = '';
    document.getElementById('ruangan-panjang').value = '';
    document.getElementById('ruangan-lebar').value = '';
    document.getElementById('ruangan-luas').value = '';
    document.getElementById('ruangan-bangunan-id').value = '';
    onBangunanChange('', 1);
    document.getElementById('ruangan-kapasitas').value = '30';
    document.getElementById('ruangan-keterangan').value = '';

    const pj = document.getElementById('ruangan-pj');
    if (pj) {
        if (window.SearchableSelect) {
            window.SearchableSelect.setValue(pj, '');
        } else {
            pj.value = '';
        }
    }

    const m = document.getElementById('modal-ruangan');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}

function editRuangan(item) {
    document.getElementById('modal-ruangan-title').innerText = 'Edit Ruangan';
    document.getElementById('ruangan-id').value = item.id;
    document.getElementById('ruangan-nama').value = item.nama_ruangan;
    document.getElementById('ruangan-jenis').value = item.jenis_ruangan || 'Ruang Kelas';
    document.getElementById('ruangan-kode').value = item.kode_ruangan;
    document.getElementById('ruangan-unit').value = item.unit;
    document.getElementById('ruangan-panjang').value = parseFloat(item.panjang) > 0 ? item.panjang : '';
    document.getElementById('ruangan-lebar').value = parseFloat(item.lebar) > 0 ? item.lebar : '';
    document.getElementById('ruangan-luas').value = parseFloat(item.luas) > 0 ? item.luas : '';
    document.getElementById('ruangan-bangunan-id').value = item.bangunan_id || '';
    onBangunanChange(item.bangunan_id || '', item.lantai || 1);
    document.getElementById('ruangan-kapasitas').value = item.kapasitas;
    document.getElementById('ruangan-keterangan').value = item.keterangan || '';

    const pj = document.getElementById('ruangan-pj');
    if (pj) {
        const val = item.penanggung_jawab || '';
        if (val && !Array.from(pj.options).some(o => o.value === val)) {
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = val;
            opt.setAttribute('data-badge', 'Tersimpan');
            pj.appendChild(opt);
        }
        if (window.SearchableSelect) {
            window.SearchableSelect.setValue(pj, val);
        } else {
            pj.value = val;
        }
    }

    const m = document.getElementById('modal-ruangan');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}

function closeModalRuangan() {
    const m = document.getElementById('modal-ruangan');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}

// Live Search & Gedung Filter
function handleSearchRuangan(val) {
    applyRuangFilters();
}
function handleFilterGedung(val) {
    applyRuangFilters();
}
function applyRuangFilters() {
    const q = (document.getElementById('searchRuanganInput')?.value || '').toLowerCase().trim();
    const g = (document.getElementById('filterGedungSelect')?.value || '').toLowerCase().trim();
    
    document.querySelectorAll('.ruangan-table-row').forEach(row => {
        const searchContent = (row.getAttribute('data-search') || '').toLowerCase();
        const gedungContent = (row.getAttribute('data-gedung') || '').toLowerCase();
        const matchQ = !q || searchContent.includes(q);
        const matchG = !g || gedungContent.includes(g);
        row.style.display = (matchQ && matchG) ? '' : 'none';
    });
}
</script>
