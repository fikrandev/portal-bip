<?php
/**
 * Halaman Referensi Sarpras
 * Tab 1: Data Golongan | Tab 2: Kode Kelompok | Tab 3: Asal Anggaran
 * Portal BIP
 */

$activeTab = $_GET['tab'] ?? 'golongan';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="<?= url('kelola-sarpras') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                &larr; Dashboard Sarpras
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2.5">
                <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                </svg>
                <span>Data Referensi Sarpras</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Master data golongan, kode kelompok, dan asal anggaran untuk penomoran aset
            </p>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-primary-50 border border-primary-200 text-primary-800 text-sm font-medium">
            <svg class="w-5 h-5 text-primary-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="border-b border-slate-100 px-6 pt-5 flex gap-1 flex-wrap">
            <?php
            $tabs = [
                'golongan'      => ['label' => 'Data Golongan',  'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
                'kelompok'      => ['label' => 'Kode Kelompok',  'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
                'asal-anggaran' => ['label' => 'Asal Anggaran',  'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
                'laporan'       => ['label' => 'Pengaturan Laporan', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z'],
            ];
            foreach ($tabs as $key => $tab):
                $isActive = ($activeTab === $key);
            ?>
                <a href="<?= url('kelola-sarpras/referensi') ?>?tab=<?= $key ?>"
                   class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-t-2xl border-b-2 transition-all duration-150 <?= $isActive
                       ? 'text-primary-700 border-primary-500 bg-primary-50/60'
                       : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="<?= $tab['icon'] ?>"/>
                    </svg>
                    <?= $tab['label'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="p-6">

            <?php /* ============================================================
               TAB 1 — DATA GOLONGAN
            ============================================================ */ ?>
            <?php if ($activeTab === 'golongan'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Form Tambah / Edit -->
                <div class="lg:col-span-2">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                        <h3 class="font-bold text-slate-700 text-sm mb-4" id="golongan-form-title">Tambah Golongan Baru</h3>
                        <form action="<?= url('kelola-sarpras/referensi/golongan/store') ?>" method="POST" class="space-y-3 text-xs" id="form-golongan">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="id" id="golongan-id" value="">

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Kode <span class="text-rose-500">*</span></label>
                                <input type="text" name="kode" id="golongan-kode" required maxlength="20"
                                       placeholder="Contoh: 01, A, GBG..."
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono uppercase text-slate-800 focus:border-primary-500 outline-none">
                                <p class="text-[10px] text-slate-400 mt-0.5">Kode unik golongan aset (max 20 karakter)</p>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Golongan <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_golongan" id="golongan-nama" required
                                       placeholder="Contoh: Tanah, Bangunan, Kendaraan..."
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 focus:border-primary-500 outline-none">
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button type="submit"
                                        class="flex-1 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition-all shadow-sm">
                                    Simpan
                                </button>
                                <button type="button" onclick="resetFormGolongan()"
                                        class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-all">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="lg:col-span-3">
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase font-bold tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4 w-10 text-center">No</th>
                                    <th class="py-3 px-4 w-24">Kode</th>
                                    <th class="py-3 px-4">Nama Golongan</th>
                                    <th class="py-3 px-4 text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($golonganList)): ?>
                                    <tr><td colspan="4" class="py-10 text-center text-slate-400">Belum ada data golongan.</td></tr>
                                <?php else: $no = 1; foreach ($golonganList as $g): ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                        <td class="py-3 px-4 font-mono font-bold text-primary-700"><?= htmlspecialchars($g['kode']) ?></td>
                                        <td class="py-3 px-4 font-semibold text-slate-800"><?= htmlspecialchars($g['nama_golongan']) ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button onclick='editGolongan(<?= htmlspecialchars(json_encode($g)) ?>)'
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                                </button>
                                                <form action="<?= url('kelola-sarpras/referensi/golongan/delete/' . $g['id']) ?>" method="POST"
                                                      onsubmit="return ModalHelper.confirm(event, this, 'Hapus Golongan', 'Hapus golongan <?= htmlspecialchars($g['nama_golongan'], ENT_QUOTES) ?>? Data kelompok yang terkait juga akan terhapus.', 'danger');">
                                                    <?= CSRF::field() ?>
                                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 px-1">Total: <?= count($golonganList) ?> golongan terdaftar</p>
                </div>
            </div>
            <?php endif; /* end tab golongan */ ?>


            <?php /* ============================================================
               TAB 2 — KODE KELOMPOK
            ============================================================ */ ?>
            <?php if ($activeTab === 'kelompok'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Form Tambah / Edit -->
                <div class="lg:col-span-2">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                        <h3 class="font-bold text-slate-700 text-sm mb-4" id="kelompok-form-title">Tambah Kode Kelompok</h3>
                        <form action="<?= url('kelola-sarpras/referensi/kelompok/store') ?>" method="POST" class="space-y-3 text-xs" id="form-kelompok">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="id" id="kelompok-id" value="">

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Pilih Golongan <span class="text-rose-500">*</span></label>
                                <select name="golongan_id" id="kelompok-golongan" required
                                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 focus:border-primary-500 outline-none">
                                    <option value="">-- Pilih Golongan --</option>
                                    <?php foreach ($golonganList as $g): ?>
                                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['kode'] . ' - ' . $g['nama_golongan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($golonganList)): ?>
                                    <p class="text-[10px] text-amber-500 mt-0.5">⚠ Tambahkan golongan terlebih dahulu di tab Data Golongan.</p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Kode <span class="text-rose-500">*</span></label>
                                <input type="text" name="kode" id="kelompok-kode" required maxlength="30"
                                       placeholder="Contoh: 01, 1.01, KLS-A..."
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono uppercase text-slate-800 focus:border-primary-500 outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Kelompok <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_kelompok" id="kelompok-nama" required
                                       placeholder="Contoh: Tanah Bangunan, Gedung Kantor..."
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 focus:border-primary-500 outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Keterangan</label>
                                <textarea name="keterangan" id="kelompok-keterangan" rows="2"
                                          placeholder="Deskripsi singkat (opsional)"
                                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 focus:border-primary-500 outline-none"></textarea>
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button type="submit"
                                        class="flex-1 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition-all shadow-sm">
                                    Simpan
                                </button>
                                <button type="button" onclick="resetFormKelompok()"
                                        class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-all">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="lg:col-span-3">
                    <!-- Search -->
                    <div class="relative mb-3">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input type="text" id="search-kelompok" placeholder="Cari kode atau nama kelompok..."
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-white text-slate-700 focus:border-primary-400 outline-none"
                               oninput="filterKelompok(this.value)">
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700" id="table-kelompok">
                            <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase font-bold tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4 w-10 text-center">No</th>
                                    <th class="py-3 px-4">Golongan</th>
                                    <th class="py-3 px-4 w-20">Kode</th>
                                    <th class="py-3 px-4">Nama Kelompok</th>
                                    <th class="py-3 px-4 text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="tbody-kelompok">
                                <?php if (empty($kelompokList)): ?>
                                    <tr><td colspan="5" class="py-10 text-center text-slate-400">Belum ada data kode kelompok.</td></tr>
                                <?php else: $no = 1; foreach ($kelompokList as $k): ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors kelompok-row"
                                        data-search="<?= strtolower(htmlspecialchars($k['kode'] . ' ' . $k['nama_kelompok'] . ' ' . $k['nama_golongan'])) ?>">
                                        <td class="py-3 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700"><?= htmlspecialchars($k['kode_golongan'] . ' - ' . $k['nama_golongan']) ?></span>
                                        </td>
                                        <td class="py-3 px-4 font-mono font-bold text-primary-700"><?= htmlspecialchars($k['kode']) ?></td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-slate-800"><?= htmlspecialchars($k['nama_kelompok']) ?></div>
                                            <?php if (!empty($k['keterangan'])): ?>
                                                <div class="text-[10px] text-slate-400 mt-0.5"><?= htmlspecialchars($k['keterangan']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button onclick='editKelompok(<?= htmlspecialchars(json_encode($k)) ?>)'
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                                </button>
                                                <form action="<?= url('kelola-sarpras/referensi/kelompok/delete/' . $k['id']) ?>" method="POST"
                                                      onsubmit="return ModalHelper.confirm(event, this, 'Hapus Kelompok', 'Hapus kelompok <?= htmlspecialchars($k['nama_kelompok'], ENT_QUOTES) ?>?', 'danger');">
                                                    <?= CSRF::field() ?>
                                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 px-1">Total: <?= count($kelompokList) ?> kode kelompok terdaftar</p>
                </div>
            </div>
            <?php endif; /* end tab kelompok */ ?>


            <?php /* ============================================================
               TAB 3 — ASAL ANGGARAN
            ============================================================ */ ?>
            <?php if ($activeTab === 'asal-anggaran'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Form Tambah / Edit -->
                <div class="lg:col-span-2">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                        <h3 class="font-bold text-slate-700 text-sm mb-4" id="anggaran-form-title">Tambah Asal Anggaran</h3>
                        <form action="<?= url('kelola-sarpras/referensi/asal-anggaran/store') ?>" method="POST" class="space-y-3 text-xs" id="form-anggaran">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="id" id="anggaran-id" value="">

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Kode <span class="text-rose-500">*</span></label>
                                <input type="text" name="kode" id="anggaran-kode" required maxlength="20"
                                       placeholder="Contoh: APBN, APBD, YYS..."
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono uppercase text-slate-800 focus:border-primary-500 outline-none">
                                <p class="text-[10px] text-slate-400 mt-0.5">Kode sumber dana (max 20 karakter)</p>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama" id="anggaran-nama" required
                                       placeholder="Contoh: APBN, Dana BOS, Yayasan..."
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 focus:border-primary-500 outline-none">
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button type="submit"
                                        class="flex-1 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition-all shadow-sm">
                                    Simpan
                                </button>
                                <button type="button" onclick="resetFormAnggaran()"
                                        class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-all">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="lg:col-span-3">
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase font-bold tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4 w-10 text-center">No</th>
                                    <th class="py-3 px-4 w-24">Kode</th>
                                    <th class="py-3 px-4">Nama</th>
                                    <th class="py-3 px-4 text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($asalAnggaranList)): ?>
                                    <tr><td colspan="4" class="py-10 text-center text-slate-400">Belum ada data asal anggaran.</td></tr>
                                <?php else: $no = 1; foreach ($asalAnggaranList as $a): ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                        <td class="py-3 px-4 font-mono font-bold text-primary-700"><?= htmlspecialchars($a['kode']) ?></td>
                                        <td class="py-3 px-4 font-semibold text-slate-800"><?= htmlspecialchars($a['nama']) ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button onclick='editAnggaran(<?= htmlspecialchars(json_encode($a)) ?>)'
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                                </button>
                                                <form action="<?= url('kelola-sarpras/referensi/asal-anggaran/delete/' . $a['id']) ?>" method="POST"
                                                      onsubmit="return ModalHelper.confirm(event, this, 'Hapus Asal Anggaran', 'Hapus asal anggaran <?= htmlspecialchars($a['nama'], ENT_QUOTES) ?>?', 'danger');">
                                                    <?= CSRF::field() ?>
                                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 px-1">Total: <?= count($asalAnggaranList) ?> sumber dana terdaftar</p>
                </div>
            </div>
            <?php endif; /* end tab asal-anggaran */ ?>

            <?php /* ============================================================
               TAB 4 — PENGATURAN LAPORAN
            ============================================================ */ ?>
            <?php if ($activeTab === 'laporan'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Form Pengaturan Laporan -->
                <div>
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                        <h3 class="font-bold text-slate-700 text-sm mb-4">Pengaturan Cetak Laporan Aset</h3>
                        <form action="<?= url('kelola-sarpras/referensi/laporan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                            <?= CSRF::field() ?>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Upload Kop Surat (Format JPG/PNG)</label>
                                <?php if (!empty($kopSurat)): ?>
                                    <div class="mb-2 border border-slate-200 rounded-xl p-2 bg-white flex justify-center">
                                        <img src="<?= asset($kopSurat) ?>" alt="Kop Surat" class="h-16 object-contain">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="kop_surat" accept="image/png, image/jpeg, image/jpg"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 focus:border-primary-500 outline-none">
                                <p class="text-[10px] text-slate-400 mt-1">Gunakan gambar persegi panjang yang memuat kop surat instansi secara utuh.</p>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Kepala Sarpras (Penandatangan)</label>
                                <select name="kepala_sarpras_id" id="kepala-sarpras-select" class="searchable-select w-full" data-placeholder="-- Cari Nama Pegawai --">
                                    <option value="">-- Cari Nama Pegawai --</option>
                                    <?php foreach ($pegawaiList as $pegawai): ?>
                                        <?php $sel = ($kepalaSarprasId == $pegawai['id']) ? 'selected' : ''; ?>
                                        <option value="<?= $pegawai['id'] ?>" <?= $sel ?>><?= e($pegawai['nama']) ?> <?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?> (NIY: <?= e($pegawai['niy']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition-all shadow-sm">
                                    Simpan Pengaturan Laporan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; /* end tab laporan */ ?>

        </div><!-- /p-6 -->
    </div><!-- /card -->
</div><!-- /space-y-6 -->

<script>
/* ── GOLONGAN ───────────────────────────────── */
function editGolongan(item) {
    document.getElementById('golongan-form-title').innerText = 'Edit Golongan';
    document.getElementById('golongan-id').value    = item.id;
    document.getElementById('golongan-kode').value  = item.kode;
    document.getElementById('golongan-nama').value  = item.nama_golongan;
    document.getElementById('golongan-kode').focus();
}
function resetFormGolongan() {
    document.getElementById('golongan-form-title').innerText = 'Tambah Golongan Baru';
    document.getElementById('golongan-id').value   = '';
    document.getElementById('golongan-kode').value = '';
    document.getElementById('golongan-nama').value = '';
}

/* ── KODE KELOMPOK ──────────────────────────── */
function editKelompok(item) {
    document.getElementById('kelompok-form-title').innerText = 'Edit Kode Kelompok';
    document.getElementById('kelompok-id').value        = item.id;
    document.getElementById('kelompok-golongan').value  = item.golongan_id;
    document.getElementById('kelompok-kode').value      = item.kode;
    document.getElementById('kelompok-nama').value      = item.nama_kelompok;
    document.getElementById('kelompok-keterangan').value = item.keterangan || '';
    document.getElementById('kelompok-kode').focus();
}
function resetFormKelompok() {
    document.getElementById('kelompok-form-title').innerText = 'Tambah Kode Kelompok';
    document.getElementById('kelompok-id').value         = '';
    document.getElementById('kelompok-golongan').value   = '';
    document.getElementById('kelompok-kode').value       = '';
    document.getElementById('kelompok-nama').value       = '';
    document.getElementById('kelompok-keterangan').value = '';
}
function filterKelompok(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.kelompok-row').forEach(function(row) {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });
}

/* ── ASAL ANGGARAN ──────────────────────────── */
function editAnggaran(item) {
    document.getElementById('anggaran-form-title').innerText = 'Edit Asal Anggaran';
    document.getElementById('anggaran-id').value   = item.id;
    document.getElementById('anggaran-kode').value = item.kode;
    document.getElementById('anggaran-nama').value = item.nama;
    document.getElementById('anggaran-kode').focus();
}
function resetFormAnggaran() {
    document.getElementById('anggaran-form-title').innerText = 'Tambah Asal Anggaran';
    document.getElementById('anggaran-id').value   = '';
    document.getElementById('anggaran-kode').value = '';
    document.getElementById('anggaran-nama').value = '';
}
</script>
