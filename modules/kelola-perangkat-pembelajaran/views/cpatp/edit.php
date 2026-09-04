<?php
/**
 * CP & ATP - Edit View
 * Format 5 Kolom: Elemen, Capaian Pembelajaran, Tujuan Pembelajaran, KKTP, Bulan
 */
$cpatpRows = $konten['cpatp_rows'] ?? [];
$currentGuru = !empty($item['guru_id']) ? $item['guru_id'] : ($currentGuruId ?? 0);
$bulanOptions = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
?>
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit Dokumen CP & ATP</h1>
            <p class="text-xs sm:text-sm text-slate-500">Format 5 Kolom: Elemen, Capaian Pembelajaran, Tujuan Pembelajaran, KKTP, Bulan</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url("kelola-perangkat-pembelajaran/cpatp/detail/{$item['id']}") ?>" class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                Lihat Detail
            </a>
            <a href="<?= url('kelola-perangkat-pembelajaran/cpatp') ?>" class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Catatan Revisi Alert -->
    <?php if ($item['status'] === 'ditolak' && !empty($item['catatan_revisi'])): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-xs text-rose-800">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            <div>
                <p class="font-bold">Catatan Revisi dari Verifikator:</p>
                <p class="mt-0.5"><?= nl2br(e($item['catatan_revisi'])) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/cpatp/update/{$item['id']}") ?>" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Identitas Dokumen -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                1. Identitas Dokumen (Dari Grup)
            </h2>

            <!-- Hidden Inputs for inherited values -->
            <input type="hidden" name="unit" value="<?= e($item['unit']) ?>">
            <input type="hidden" name="tahun_akademik_id" value="<?= e($item['tahun_akademik_id']) ?>">
            <input type="hidden" name="semester" value="<?= e($item['semester']) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Unit Sekolah</label>
                    <div class="px-4 py-2.5 rounded-2xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-700">
                        Unit <?= e($item['unit']) ?>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Tahun Ajaran</label>
                    <div class="px-4 py-2.5 rounded-2xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-700">
                        <?php 
                        $taName = '';
                        foreach($ta_list as $ta) {
                            if ($ta['id'] == $item['tahun_akademik_id']) {
                                $taName = $ta['nama_tahun']; break;
                            }
                        }
                        echo e($taName);
                        ?>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Semester</label>
                    <div class="px-4 py-2.5 rounded-2xl border border-slate-100 bg-slate-50 text-xs font-bold text-slate-700">
                        Semester <?= e($item['semester']) ?>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <!-- Searchable Live Search Guru Picker -->
                <?php
                $picker_label = 'Guru Pengampu CP & ATP';
                $picker_accent = 'indigo';
                $selected_guru_id = old('guru_id', $currentGuru);
                $selected_guru_nama = old('guru_nama', $item['guru_nama']);
                $selected_guru_nip = old('guru_nip', $item['guru_nip']);
                include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/guru_picker.php';
                ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-100">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" id="input-judul" value="<?= e($item['judul']) ?>" required placeholder="Contoh: Bab 1: Bilangan Bulat" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-indigo-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white shadow-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="mata_pelajaran" id="mata_pelajaran" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50" required>
                        <option value="<?= e($item['mata_pelajaran']) ?>"><?= e($item['mata_pelajaran']) ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat / Kelas <span class="text-rose-500">*</span></label>
                    <select name="tingkat_kelas" id="tingkat_kelas" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50" required>
                        <option value="<?= e($item['tingkat_kelas']) ?>">Kelas <?= e($item['tingkat_kelas']) ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase Kurikulum (Opsional)</label>
                    <select name="fase" id="fase" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                        <option value="">Pilih Fase</option>
                        <?php
                        $faseList = [
                            'Fase A' => 'Fase A (Kls 1-2)',
                            'Fase B' => 'Fase B (Kls 3-4)',
                            'Fase C' => 'Fase C (Kls 5-6)',
                            'Fase D' => 'Fase D (SMP)',
                            'Fase E' => 'Fase E (SMA Kls 10)',
                            'Fase F' => 'Fase F (SMA Kls 11-12)'
                        ];
                        foreach ($faseList as $fVal => $fLabel) {
                            $sel = ($item['fase'] === $fVal) ? 'selected' : '';
                            echo "<option value=\"$fVal\" $sel>$fLabel</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alokasi Waktu (Opsional) <span id="sisa_jp_label" class="text-indigo-600 font-bold ml-1"></span></label>
                    <input type="text" id="alokasi_waktu_input" name="alokasi_waktu" value="<?= e($item['alokasi_waktu'] ?? '') ?>" placeholder="Contoh: 12" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>
            </div>
        </div>

        <!-- Tabel CP & ATP (5 Kolom) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800 tracking-tight flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                        3. Tabel Capaian & Alur Tujuan Pembelajaran (CP & ATP)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Format: Elemen, Capaian Pembelajaran, Tujuan Pembelajaran, KKTP, Bulan</p>
                </div>
                <button type="button" onclick="addBlockRow()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    + Tambah Blok Elemen / TP
                </button>
            </div>

            <div id="cpatp-blocks-container" class="space-y-5">
                <?php if (!empty($cpatpRows)): ?>
                    <?php foreach ($cpatpRows as $bIdx => $row): ?>
                        <div class="cpatp-block bg-white rounded-3xl p-6 border-2 border-slate-200/80 shadow-sm space-y-4 relative" data-block-idx="<?= $bIdx ?>">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200 block-badge">Blok Elemen & TP #<?= $bIdx + 1 ?></span>
                                <button type="button" onclick="removeBlockRow(this)" class="p-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Hapus Blok Ini">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                                <div class="lg:col-span-3">
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">1. Elemen <span class="text-rose-500">*</span></label>
                                    <input type="text" name="row_elemen[]" value="<?= e($row['elemen'] ?? '') ?>" required placeholder="Nama Elemen..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                                </div>
                                <div class="lg:col-span-5">
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">2. Capaian Pembelajaran <span class="text-rose-500">*</span></label>
                                    <textarea name="row_cp[]" rows="3" required placeholder="Deskripsi CP..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"><?= e($row['cp'] ?? '') ?></textarea>
                                </div>
                                <div class="lg:col-span-4">
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">3. Tujuan Pembelajaran <span class="text-rose-500">*</span></label>
                                    <textarea name="row_tp[]" rows="3" required placeholder="Deskripsi TP..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"><?= e($row['tp'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-bold text-slate-700 uppercase">4. KKTP &nbsp;&bull;&nbsp; 5. Alokasi Bulan & Pekan</label>
                                    <button type="button" onclick="addKktpRow(this)" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] transition-colors">+ Tambah Baris KKTP</button>
                                </div>
                                <div class="space-y-2 kktp-list-container">
                                    <?php $kktpList = $row['kktp_list'] ?? [['kktp' => '', 'bulan' => 'Juli']]; ?>
                                    <?php foreach ($kktpList as $kItem): ?>
                                        <div class="flex items-center gap-2 kktp-row">
                                            <div class="flex-1">
                                                <input type="text" name="row_kktp[<?= $bIdx ?>][]" value="<?= e($kItem['kktp'] ?? '') ?>" placeholder="Deskripsi KKTP / Indikator..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/30">
                                            </div>
                                            <div class="w-32">
                                                <select name="row_bulan[<?= $bIdx ?>][]" onchange="onBulanChanged(this)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
                                                    <?php foreach ($bulanOptions as $b): ?>
                                                        <option value="<?= $b ?>" <?= ($kItem['bulan'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="w-28">
                                                <select name="row_pekan[<?= $bIdx ?>][]" data-saved-value="<?= e($kItem['pekan'] ?? '1') ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
                                                    <option value="<?= e($kItem['pekan'] ?? '1') ?>" selected><?= e($kItem['pekan'] ?? '1') ?></option>
                                                </select>
                                            </div>
                                            <button type="button" onclick="removeKktpRow(this)" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Blok Default Kosong -->
                    <div class="cpatp-block bg-white rounded-3xl p-6 border-2 border-slate-200/80 shadow-sm space-y-4 relative" data-block-idx="0">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200 block-badge">Blok Elemen & TP #1</span>
                            <button type="button" onclick="removeBlockRow(this)" class="p-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></button>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                            <div class="lg:col-span-3">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">1. Elemen <span class="text-rose-500">*</span></label>
                                <input type="text" name="row_elemen[]" required placeholder="Nama Elemen..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                            </div>
                            <div class="lg:col-span-5">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">2. Capaian Pembelajaran <span class="text-rose-500">*</span></label>
                                <textarea name="row_cp[]" rows="3" required placeholder="Deskripsi CP..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"></textarea>
                            </div>
                            <div class="lg:col-span-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">3. Tujuan Pembelajaran <span class="text-rose-500">*</span></label>
                                <textarea name="row_tp[]" rows="3" required placeholder="Deskripsi TP..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"></textarea>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-bold text-slate-700 uppercase">4. KKTP &nbsp;&bull;&nbsp; 5. Alokasi Bulan & Pekan</label>
                                <button type="button" onclick="addKktpRow(this)" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] transition-colors">+ Tambah Baris KKTP</button>
                            </div>
                            <div class="space-y-2 kktp-list-container">
                                <div class="flex items-center gap-2 kktp-row">
                                    <div class="flex-1">
                                        <input type="text" name="row_kktp[0][]" placeholder="Deskripsi KKTP / Indikator..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/30">
                                    </div>
                                    <div class="w-32">
                                        <select name="row_bulan[0][]" onchange="onBulanChanged(this)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
                                            <?php foreach ($bulanOptions as $b): ?><option value="<?= $b ?>"><?= $b ?></option><?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="w-28">
                                        <select name="row_pekan[0][]" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
                                            <option value="Pekan 1">Pekan 1</option>
                                        </select>
                                    </div>
                                    <button type="button" onclick="removeKktpRow(this)" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg></button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="<?= !empty($group) ? url("kelola-perangkat-pembelajaran/cpatp/group/{$group['id']}") : url('kelola-perangkat-pembelajaran/cpatp') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all">Simpan Perubahan</button>
            <?php if ($item['status'] === 'draft' || $item['status'] === 'ditolak'): ?>
                <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all">Simpan & Ajukan Verifikasi</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
const BULAN_OPTIONS = <?= json_encode($bulanOptions) ?>;
const GROUP_UNIT = <?= json_encode($item['unit'] ?? 'SD') ?>;
const GROUP_SEMESTER = <?= json_encode($item['semester'] ?? 'Ganjil') ?>;
let jadwalData = { hari_mengajar: [], tahun_akademik: null, heb_data: [] };

function fetchJadwalHari() {
    const guruId = document.getElementById('guru_id').value;
    const mapel = document.getElementById('mata_pelajaran').value;
    const kelas = document.getElementById('tingkat_kelas').value;
    
    if (!guruId || !mapel || !kelas) {
        jadwalData = { hari_mengajar: [], tahun_akademik: null, heb_data: [] };
        refreshAllPekanDropdowns();
        return;
    }
    
    // Tampilkan loading di semua dropdown pekan
    document.querySelectorAll('.kktp-row select[name^="row_pekan"]').forEach(sel => {
        sel.innerHTML = '<option value="">Memuat Jadwal...</option>';
    });

    const url = `<?= url('kelola-perangkat-pembelajaran/cpatp/ajax-jadwal') ?>/${guruId}?mapel=${encodeURIComponent(mapel)}&kelas=${encodeURIComponent(kelas)}&unit=${encodeURIComponent(GROUP_UNIT)}&semester=${encodeURIComponent(GROUP_SEMESTER)}`;
    console.log("Fetching jadwal from:", url);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            jadwalData = data;
            console.log("Jadwal fetched for", mapel, kelas, ":", jadwalData);
            refreshAllPekanDropdowns();
        })
        .catch(err => {
            console.error("Fetch Jadwal Hari error:", err);
            jadwalData = { hari_mengajar: [], tahun_akademik: null, heb_data: [] };
            refreshAllPekanDropdowns();
        });
}

function getMonthNumberFromName(monthName) {
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return months.indexOf(monthName);
}

function getDayNumberFromName(dayName) {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return days.indexOf(dayName);
}

function getIndonesianDayName(dayIndex) {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return days[dayIndex];
}

function generateTanggalPekanOptions(bulanName) {
    const mapel = document.getElementById('mata_pelajaran')?.value;
    const kelas = document.getElementById('tingkat_kelas')?.value;
    
    if (!bulanName) {
        return `<option value="">Pilih Bulan Dahulu</option>`;
    }
    if (!mapel || !kelas) {
        return `<option value="">Pilih Mapel & Kelas Dahulu</option>`;
    }
    
    let options = `<option value="">Pilih Pekan</option>`;
    
    if (!jadwalData || !jadwalData.tahun_akademik) {
        for(let i=1; i<=5; i++) {
            options += `<option value="Pekan ${i}">Pekan ${i}</option>`;
        }
        return options;
    }
    
    try {
        const monthNum = getMonthNumberFromName(bulanName);
        if (monthNum === -1) { 
            for(let i=1; i<=5; i++) {
                options += `<option value="Pekan ${i}">Pekan ${i}</option>`;
            }
            return options; 
        }
        
        const startStr = jadwalData.tahun_akademik.tanggal_mulai;
        const endStr = jadwalData.tahun_akademik.tanggal_selesai;
        
        if (!startStr || !endStr) {
            throw new Error("Tanggal mulai atau selesai tahun akademik tidak valid");
        }
        
        let targetYear = parseInt(startStr.substring(0,4));
        if (monthNum < 6) { 
            targetYear = parseInt(endStr.substring(0,4)); 
        }
        
        const daysInMonth = new Date(targetYear, monthNum + 1, 0).getDate();
        let dates = [];
        
        let hasHebDates = false;
        if (jadwalData.heb_data) {
            const hebMonth = jadwalData.heb_data.find(m => m.bulan === bulanName.toUpperCase());
            if (hebMonth && Array.isArray(hebMonth.valid_dates) && hebMonth.valid_dates.length > 0) {
                dates = hebMonth.valid_dates;
                hasHebDates = true;
            }
        }
        
        if (!hasHebDates) {
            if (!jadwalData.hari_mengajar || jadwalData.hari_mengajar.length === 0) {
                for(let i=1; i<=5; i++) {
                    options += `<option value="Pekan ${i}">Pekan ${i}</option>`;
                }
                return options;
            }
            for (let d = 1; d <= daysInMonth; d++) {
                const dateObj = new Date(targetYear, monthNum, d);
                const dayName = getIndonesianDayName(dateObj.getDay());
                if (jadwalData.hari_mengajar.includes(dayName)) {
                    dates.push(d);
                }
            }
        }
        
        if (dates.length === 0) {
            for(let i=1; i<=5; i++) {
                options += `<option value="Pekan ${i}">Pekan ${i}</option>`;
            }
            return options;
        }
        
        let teachingWeek = 1;
        let currentCalendarWeek = -1;
        
        dates.forEach(date => {
            let calendarWeek = Math.ceil((date + new Date(targetYear, monthNum, 1).getDay()) / 7);
            if (currentCalendarWeek !== -1 && calendarWeek > currentCalendarWeek) {
                teachingWeek++;
            }
            currentCalendarWeek = calendarWeek;
            
            let val = `Pekan ${teachingWeek} (${date} ${bulanName})`;
            options += `<option value="${val}">${val}</option>`;
        });
        
        return options;
    } catch (e) {
        console.error("Error generating dates:", e);
        for(let i=1; i<=5; i++) {
            options += `<option value="Pekan ${i}">Pekan ${i}</option>`;
        }
        return options;
    }
}

function refreshAllPekanDropdowns() {
    const rows = document.querySelectorAll('.kktp-row');
    rows.forEach(row => {
        const bulanSelect = row.querySelector('select[name^="row_bulan"]');
        const pekanSelect = row.querySelector('select[name^="row_pekan"]');
        if (bulanSelect && pekanSelect) {
            const currentVal = pekanSelect.dataset.savedValue || pekanSelect.value;
            pekanSelect.innerHTML = generateTanggalPekanOptions(bulanSelect.value);
            
            if (currentVal && pekanSelect.querySelector(`option[value="${currentVal}"]`)) {
                pekanSelect.value = currentVal;
            } else {
                pekanSelect.value = '';
            }
            delete pekanSelect.dataset.savedValue;
        }
    });
}

function onBulanChanged(selectEl) {
    const row = selectEl.closest('.kktp-row');
    const pekanSelect = row.querySelector('select[name^="row_pekan"]');
    if (pekanSelect) {
        const currentVal = pekanSelect.value;
        pekanSelect.innerHTML = generateTanggalPekanOptions(selectEl.value);
    }
}

function addBlockRow() {
    const container = document.getElementById('cpatp-blocks-container');
    const blockIdx = container.querySelectorAll('.cpatp-block').length;
    let bulanHtml = ''; BULAN_OPTIONS.forEach(b => { bulanHtml += `<option value="${b}">${b}</option>`; });
    const div = document.createElement('div');
    div.className = 'cpatp-block bg-white rounded-3xl p-6 border-2 border-slate-200/80 shadow-sm space-y-4 relative';
    div.dataset.blockIdx = blockIdx;
    div.innerHTML = `
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <span class="px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200 block-badge">Blok Elemen & TP #${blockIdx+1}</span>
            <button type="button" onclick="removeBlockRow(this)" class="p-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></button>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-3"><label class="block text-xs font-bold text-slate-700 uppercase mb-1">1. Elemen *</label><input type="text" name="row_elemen[]" required placeholder="Nama Elemen..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"></div>
            <div class="lg:col-span-5"><label class="block text-xs font-bold text-slate-700 uppercase mb-1">2. Capaian Pembelajaran *</label><textarea name="row_cp[]" rows="3" required placeholder="Deskripsi CP..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"></textarea></div>
            <div class="lg:col-span-4"><label class="block text-xs font-bold text-slate-700 uppercase mb-1">3. Tujuan Pembelajaran *</label><textarea name="row_tp[]" rows="3" required placeholder="Deskripsi TP..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"></textarea></div>
        </div>
        <div class="pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-bold text-slate-700 uppercase">4. KKTP &bull; 5. Bulan & Pekan</label>
                <button type="button" onclick="addKktpRow(this)" class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] transition-colors">+ Tambah Baris KKTP</button>
            </div>
            <div class="space-y-2 kktp-list-container">
                <div class="flex items-center gap-2 kktp-row">
                    <div class="flex-1"><input type="text" name="row_kktp[${blockIdx}][]" placeholder="Deskripsi KKTP..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/30"></div>
                    <div class="w-32"><select name="row_bulan[${blockIdx}][]" onchange="onBulanChanged(this)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">${bulanHtml}</select></div>
                    <div class="w-28"><select name="row_pekan[${blockIdx}][]" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
                        ${generateTanggalPekanOptions(BULAN_OPTIONS[0])}
                    </select></div>
                    <button type="button" onclick="removeKktpRow(this)" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg></button>
                </div>
            </div>
        </div>`;
    container.appendChild(div);
    refreshBlockIndices();
}

function removeBlockRow(btn) {
    const container = document.getElementById('cpatp-blocks-container');
    if (container.querySelectorAll('.cpatp-block').length > 1) { btn.closest('.cpatp-block').remove(); refreshBlockIndices(); }
    else { alert('Minimal harus ada 1 blok Elemen & TP.'); }
}

function refreshBlockIndices() {
    document.querySelectorAll('#cpatp-blocks-container .cpatp-block').forEach((block, bIdx) => {
        block.dataset.blockIdx = bIdx;
        const badge = block.querySelector('.block-badge');
        if (badge) badge.textContent = `Blok Elemen & TP #${bIdx+1}`;
        block.querySelectorAll('.kktp-row').forEach(row => {
            const ki = row.querySelector('input[name^="row_kktp"]');
            const bs = row.querySelector('select[name^="row_bulan"]');
            const ps = row.querySelector('select[name^="row_pekan"]');
            if (ki) ki.name = `row_kktp[${bIdx}][]`;
            if (bs) bs.name = `row_bulan[${bIdx}][]`;
            if (ps) ps.name = `row_pekan[${bIdx}][]`;
        });
    });
}

function addKktpRow(btn) {
    const block = btn.closest('.cpatp-block');
    const bIdx = block.dataset.blockIdx;
    const container = block.querySelector('.kktp-list-container');
    
    // Check previous row's selected month if exists, else BULAN_OPTIONS[0]
    const lastRow = container.querySelector('.kktp-row:last-child');
    const defaultBulan = lastRow ? (lastRow.querySelector('select[name^="row_bulan"]')?.value || BULAN_OPTIONS[0]) : BULAN_OPTIONS[0];

    let bulanHtml = ''; 
    BULAN_OPTIONS.forEach(b => { 
        const selected = (b === defaultBulan) ? 'selected' : '';
        bulanHtml += `<option value="${b}" ${selected}>${b}</option>`; 
    });

    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 kktp-row';
    div.innerHTML = `
        <div class="flex-1"><input type="text" name="row_kktp[${bIdx}][]" placeholder="Deskripsi KKTP..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50/30"></div>
        <div class="w-32"><select name="row_bulan[${bIdx}][]" onchange="onBulanChanged(this)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">${bulanHtml}</select></div>
        <div class="w-28"><select name="row_pekan[${bIdx}][]" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
            ${generateTanggalPekanOptions(defaultBulan)}
        </select></div>
        <button type="button" onclick="removeKktpRow(this)" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg></button>`;
    container.appendChild(div);
}

function removeKktpRow(btn) {
    const container = btn.closest('.kktp-list-container');
    if (container.querySelectorAll('.kktp-row').length > 1) { btn.closest('.kktp-row').remove(); }
    else { alert('Minimal harus ada 1 baris KKTP.'); }
}
</script>

<script>
let penugasanData = [];
const savedMapel = "<?= e($item['mata_pelajaran']) ?>";
const savedKelas = "<?= e($item['tingkat_kelas']) ?>";

function onGuruPickerChanged(guru) {
    if (!guru || !guru.id) return;
    fetchPenugasan(guru.id);
}

function getSelectedJp() {
    const mapel = document.getElementById('mata_pelajaran').value;
    const kelas = document.getElementById('tingkat_kelas').value;
    if (!mapel || !kelas || !penugasanData) return 0;
    
    const matched = penugasanData.find(item => item.mata_pelajaran === mapel && item.kelas === kelas);
    return matched ? parseInt(matched.jumlah_jp) : 0;
}

function updateSisaJp() {
    const totalJp = getSelectedJp();
    const inputVal = document.getElementById('alokasi_waktu_input').value;
    const label = document.getElementById('sisa_jp_label');
    
    if (totalJp > 0) {
        // Parse numbers from input even if they type "2 JP"
        const inputNum = parseInt(inputVal.replace(/\D/g, '')) || 0;
        const sisa = totalJp - inputNum;
        label.textContent = `(Total: ${totalJp} JP, Sisa: ${sisa} JP)`;
        if (sisa < 0) {
            label.className = "text-rose-600 font-bold ml-1";
        } else {
            label.className = "text-indigo-600 font-bold ml-1";
        }
    } else {
        label.textContent = '';
    }
}

function autoSelectFase() {
    const kelasName = document.getElementById('tingkat_kelas').value;
    if (!kelasName) return;
    
    const lower = kelasName.toLowerCase();
    const faseSelect = document.getElementById('fase');
    if (!faseSelect) return;
    
    if (lower.includes('kelas 1 ') || lower.includes('kelas 2 ') || lower.endsWith('kelas 1') || lower.endsWith('kelas 2')) {
        faseSelect.value = 'Fase A';
    } else if (lower.includes('kelas 3 ') || lower.includes('kelas 4 ') || lower.endsWith('kelas 3') || lower.endsWith('kelas 4')) {
        faseSelect.value = 'Fase B';
    } else if (lower.includes('kelas 5 ') || lower.includes('kelas 6 ') || lower.endsWith('kelas 5') || lower.endsWith('kelas 6')) {
        faseSelect.value = 'Fase C';
    } else if (lower.includes('kelas 7') || lower.includes('kelas 8') || lower.includes('kelas 9') || lower.includes('smp')) {
        faseSelect.value = 'Fase D';
    } else if (lower.includes('kelas 10') || lower.includes('sma 10')) {
        faseSelect.value = 'Fase E';
    } else if (lower.includes('kelas 11') || lower.includes('kelas 12') || lower.includes('sma 11') || lower.includes('sma 12')) {
        faseSelect.value = 'Fase F';
    }
}

function onMapelChanged(preselectKelas = null) {
    const mapel = document.getElementById('mata_pelajaran').value;
    const kelasSelect = document.getElementById('tingkat_kelas');
    
    let currentSelectedKelas = kelasSelect.value;
    if (typeof preselectKelas === 'string') {
        currentSelectedKelas = preselectKelas;
    }
    
    kelasSelect.innerHTML = '<option value="">Pilih Kelas</option>';
    kelasSelect.disabled = true;
    
    // Reset data jadwal lama agar tidak tercampur tanggal mapel sebelumnya
    jadwalData = { hari_mengajar: [], tahun_akademik: null, heb_data: [] };
    refreshAllPekanDropdowns();
    
    if (!mapel || !penugasanData) {
        updateSisaJp();
        return;
    }
    
    const filtered = penugasanData.filter(item => item.mata_pelajaran === mapel);
    const kelasList = [...new Set(filtered.map(item => item.kelas))].filter(Boolean);
    
    kelasList.forEach(kelas => { 
        kelasSelect.innerHTML += `<option value="${kelas}" ${kelas === currentSelectedKelas ? 'selected' : ''}>${kelas}</option>`;
    });
    
    if (currentSelectedKelas && kelasList.includes(currentSelectedKelas)) {
        kelasSelect.value = currentSelectedKelas;
    } else if (kelasList.length === 1) {
        kelasSelect.value = kelasList[0];
    }
    
    kelasSelect.disabled = false;
    autoSelectFase();
    updateSisaJp();
    
    // Jika kelas sudah terpilih, langsung ambil jadwal untuk kelas dan mapel baru tersebut
    if (kelasSelect.value) {
        fetchJadwalHari();
    }
}

function fetchPenugasan(guruId) {
    const mapelSelect = document.getElementById('mata_pelajaran');
    const kelasSelect = document.getElementById('tingkat_kelas');
    
    const prevMapel = mapelSelect.value;
    const prevKelas = kelasSelect.value;
    
    mapelSelect.innerHTML = '<option value="">Memuat...</option>';
    kelasSelect.innerHTML = '<option value="">Memuat...</option>';
    mapelSelect.disabled = true;
    kelasSelect.disabled = true;

    if (!guruId) {
        mapelSelect.innerHTML = '<option value="">Pilih Guru Dahulu</option>';
        kelasSelect.innerHTML = '<option value="">Pilih Guru Dahulu</option>';
        return;
    }

    fetch(`<?= url('kelola-perangkat-pembelajaran/cpatp/ajax-penugasan') ?>/${guruId}`)
        .then(response => response.json())
        .then(data => {
            penugasanData = data;
            const mapels = [...new Set(data.map(item => item.mata_pelajaran))].filter(Boolean);
            const kelasList = [...new Set(data.map(item => item.kelas))].filter(Boolean);

            if (mapels.length === 0) {
                mapelSelect.innerHTML = '<option value="">(Belum Ada Penugasan)</option>';
                kelasSelect.innerHTML = '<option value="">(Belum Ada Penugasan)</option>';
                return;
            }

            mapelSelect.innerHTML = '<option value="">Pilih Mata Pelajaran</option>';
            mapels.forEach(mapel => { 
                const selected = (mapel === savedMapel || mapel === prevMapel) ? 'selected' : '';
                mapelSelect.innerHTML += `<option value="${mapel}" ${selected}>${mapel}</option>`; 
            });
            if (savedMapel || prevMapel) {
                mapelSelect.value = savedMapel || prevMapel;
            }

            mapelSelect.disabled = false;
            
            // Populate kelas based on selected mapel
            onMapelChanged(savedKelas || prevKelas);
            fetchJadwalHari();
        })
        .catch(err => {
            console.error(err);
            mapelSelect.innerHTML = '<option value="">Error memuat data</option>';
            kelasSelect.innerHTML = '<option value="">Error memuat data</option>';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    // Trigger initial fetch if guru is pre-selected
    const guruId = document.getElementById('guru_id').value;
    if (guruId) {
        fetchPenugasan(guruId);
    }
    
    const mapelEl = document.getElementById('mata_pelajaran');
    const kelasEl = document.getElementById('tingkat_kelas');
    const waktuEl = document.getElementById('alokasi_waktu_input');
    
    if (mapelEl) mapelEl.addEventListener('change', onMapelChanged);
    if (kelasEl) {
        kelasEl.addEventListener('change', () => {
            console.log("Kelas dropdown changed to:", kelasEl.value);
            autoSelectFase();
            updateSisaJp();
            fetchJadwalHari();
        });
    }
    if (waktuEl) waktuEl.addEventListener('input', updateSisaJp);
});
</script>
