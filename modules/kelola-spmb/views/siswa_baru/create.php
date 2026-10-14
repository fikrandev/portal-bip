<?php
/**
 * View Form Tambah Calon Siswa Baru SPMB (1 Kolom - Data Awal)
 * Portal BIP
 */
$activeGroup = $activeGroup ?? null;
$groups = $groups ?? [];
$autoNomor = $autoNomor ?? (class_exists('SpmbModel') ? SpmbModel::generateNomorPendaftaran('Siswa Baru', $activeGroup['tahun_ajaran'] ?? null, 'PAUD') : 'PAUD-001');
?>

<div class="max-w-xl mx-auto space-y-5">
    <!-- Header Card -->
    <div class="flex items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:text-sky-600">SPMB</a>
                <span>&bull;</span>
                <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="hover:text-sky-600">Siswa Baru</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Formulir 1 Kolom</span>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Pendaftaran Siswa Baru</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pengisian data awal calon peserta didik baru.
            </p>
        </div>
        <div>
            <a href="<?= url('kelola-spmb/siswa-baru') ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Active Group Badge Banner -->
    <?php if ($activeGroup): ?>
    <div class="px-4 py-3 rounded-2xl bg-gradient-to-r from-emerald-500/10 via-sky-500/10 to-transparent border border-emerald-500/20 flex items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
            <div>
                <span class="font-bold text-emerald-900">Group SPMB Aktif:</span>
                <span class="font-extrabold text-slate-800 ml-1"><?= e($activeGroup['nama_group']) ?></span>
                <span class="text-slate-500 text-[11px] block sm:inline sm:ml-1">
                    (TA <?= e($activeGroup['tahun_ajaran']) ?> &bull; <?= e($activeGroup['gelombang_default'] ?? 'Gelombang 1') ?>)
                </span>
            </div>
        </div>
        <a href="<?= url('kelola-spmb/pengaturan') ?>" class="text-[11px] font-bold text-sky-700 hover:underline shrink-0">
            Pengaturan &rarr;
        </a>
    </div>
    <?php endif; ?>

    <!-- Main Form (1 Kolom Saja) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-black text-slate-800">Formulir Pendaftaran Siswa Baru</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">Lengkapi data awal berikut dengan benar</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-sky-100 text-sky-800 font-bold text-[10px] tracking-wide uppercase">
                1 Kolom Vertikal
            </span>
        </div>

        <form id="formTambahSiswaBaruPage" action="<?= url('kelola-spmb/siswa-baru/store') ?>" method="POST" enctype="multipart/form-data" data-no-loading="true" data-confirm="true" onsubmit="event.preventDefault(); submitFormTambahSiswaBaruPage(event); return false;" class="p-6 space-y-4 text-xs text-slate-700">
            <?= CSRF::field() ?>
            <input type="hidden" name="is_ajax" value="1">
            <input type="hidden" name="jenis_pendaftaran" value="Siswa Baru">
            <input type="hidden" name="group_id" value="<?= $activeGroup['id'] ?? 1 ?>">

            <!-- 1. Jenjang -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Jenjang Sekolah Tujuan <span class="text-rose-500">*</span>
                </label>
                <select name="jenjang" id="page_jenjang" onchange="onJenjangChangePage()" required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                    <option value="PAUD" selected>PAUD</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                </select>
            </div>

            <!-- Kategori Pendaftar (Umum vs Keluarga Bina Insan) -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Kategori Pendaftar <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-all has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/50 has-[:checked]:ring-1 has-[:checked]:ring-sky-500">
                        <input type="radio" name="kategori_pendaftar" id="page_kat_umum" value="Umum (Luar Bina Insan)" checked class="text-sky-600 focus:ring-sky-500">
                        <div>
                            <div class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                <span>🌐</span> Umum
                            </div>
                            <div class="text-[10px] text-slate-500">Pendaftar Luar Bina Insan</div>
                        </div>
                    </label>
                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50/50 has-[:checked]:ring-1 has-[:checked]:ring-purple-500">
                        <input type="radio" name="kategori_pendaftar" id="page_kat_keluarga" value="Keluarga Bina Insan (Adik / Saudara)" class="text-purple-600 focus:ring-purple-500">
                        <div>
                            <div class="font-bold text-xs text-purple-900 flex items-center gap-1.5">
                                <span>👨‍👩‍👦</span> Keluarga Bina Insan
                            </div>
                            <div class="text-[10px] text-purple-600 font-medium">Adik / Saudara Siswa BIP</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. No Formulir Auto (Unit-001) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-slate-800">
                        No. Formulir <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-sky-600 font-semibold bg-sky-50 px-2 py-0.5 rounded-md">
                        Otomatis (Format: Unit-001)
                    </span>
                </div>
                <div class="relative">
                    <input type="text" name="nomor_pendaftaran" id="page_nomor_pendaftaran" value="<?= e($autoNomor) ?>" required 
                           class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 bg-sky-50/70 font-mono font-bold text-sky-800 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                    <button type="button" onclick="onJenjangChangePage()" title="Generate Ulang Nomor" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-sky-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Otomatis berubah mengikuti pilihan jenjang (misal PAUD-001, SD-001, SMP-001, SMA-001).</p>
            </div>

            <!-- 3. Gelombang (Atur di Pengaturan) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-slate-800">
                        Gelombang Pendaftaran
                    </label>
                    <span class="text-[10px] text-slate-400">Dari Pengaturan Group</span>
                </div>
                <input type="text" name="gelombang" id="page_gelombang" 
                       value="<?= e($activeGroup['gelombang_default'] ?? 'Gelombang 1') ?>" 
                       placeholder="Contoh: Gelombang 1"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-800 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 4. Nama Lengkap -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Nama Lengkap Siswa <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_lengkap" id="page_nama_lengkap" required 
                       placeholder="Nama lengkap calon siswa sesuai akta..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 5. Jenis Kelamin -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <select name="jenis_kelamin" id="page_jenis_kelamin" required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                    <option value="L" selected>Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>

            <!-- 6. Tempat Lahir -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Tempat Lahir
                </label>
                <input type="text" name="tempat_lahir" id="page_tempat_lahir" placeholder="Contoh: Palu" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 7. Tanggal Lahir -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Tanggal Lahir
                </label>
                <input type="date" name="tanggal_lahir" id="page_tanggal_lahir" onchange="hitungUmurOtomatisPage(this.value)" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 8. Umur (Auto) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-slate-800">
                        Umur Siswa <span class="text-[10px] font-normal text-slate-500">(Per <?= !empty($activeGroup['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($activeGroup['tanggal_patokan_umur']) : '1 Juli 2027' ?>)</span>
                    </label>
                    <span id="badge_umur_page" class="hidden text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">
                        Otomatis Dihitung
                    </span>
                </div>
                <div class="relative">
                    <input type="text" name="umur" id="page_umur" readonly 
                           placeholder="Otomatis terisi saat tanggal lahir dipilih" 
                           class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 outline-none text-xs cursor-default">
                    <span class="absolute left-3 top-2.5 text-slate-400">🎂</span>
                </div>
            </div>

            <!-- 9. Asal Sekolah -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Asal Sekolah
                </label>
                <input type="text" name="asal_sekolah" id="page_asal_sekolah" placeholder="Nama TK / PAUD sebelumnya..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 10. Nama Ayah -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Nama Ayah
                </label>
                <input type="text" name="nama_ayah" id="page_nama_ayah" placeholder="Nama lengkap ayah kandung..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 11. Nama Ibu -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Nama Ibu
                </label>
                <input type="text" name="nama_ibu" id="page_nama_ibu" placeholder="Nama lengkap ibu kandung..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 12. No WA -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    No. WhatsApp / HP Orang Tua <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="no_hp" id="page_no_hp" required placeholder="08xxxxxxxxxx" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-mono focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
            </div>

            <!-- 13. Alamat -->
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-800 mb-1">
                    Alamat Tempat Tinggal
                </label>
                <textarea name="alamat" id="page_alamat" rows="2" placeholder="Nama jalan, nomor rumah, kelurahan, perumahan..." 
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs"></textarea>
            </div>

            <!-- 14. Informasi Biaya Pendaftaran Awal (Gratis / Rp 0) -->
            <div class="sm:col-span-2 p-3.5 bg-emerald-50/80 border border-emerald-200/90 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                        ✓
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-emerald-950">Biaya Pendaftaran Awal: Bebas Biaya</span>
                        <span class="block text-[11px] text-emerald-700">Tahap awal pendaftaran tidak dipungut biaya formulir (Gratis).</span>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm shrink-0">
                    Rp 0 (Gratis)
                </span>
                <input type="hidden" name="biaya_pendaftaran" id="page_biaya" value="0">
                <input type="hidden" name="status_pembayaran" value="Gratis">
                <input type="hidden" name="metode_pembayaran" value="Gratis">
            </div>

            <!-- Footer Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                <a href="<?= url('kelola-spmb/siswa-baru') ?>" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                    Batal
                </a>
                <button type="button" id="btnSubmitPageSiswa" onclick="submitFormTambahSiswaBaruPage(event)" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span>Simpan & Daftarkan Siswa</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Format Rupiah Input Helper
function formatRupiahInput(el, hiddenId) {
    let clean = el.value.replace(/[^0-9]/g, '');
    let hidden = document.getElementById(hiddenId);
    if (!clean) {
        el.value = '0';
        if (hidden) hidden.value = 0;
        return;
    }
    let num = parseInt(clean, 10);
    el.value = new Intl.NumberFormat('id-ID').format(num);
    if (hidden) hidden.value = num;
}

// On Jenjang Change -> Update No. Formulir (Unit-001)
function onJenjangChangePage() {
    const jenjangSelect = document.getElementById('page_jenjang');
    const noInput = document.getElementById('page_nomor_pendaftaran');
    if (!jenjangSelect || !noInput) return;

    const jenjangVal = jenjangSelect.value;
    noInput.classList.add('animate-pulse');

    fetch('<?= url('kelola-spmb/siswa-baru/get-nomor?jenjang=') ?>' + encodeURIComponent(jenjangVal))
        .then(res => res.json())
        .then(data => {
            if (data && data.nomor) {
                noInput.value = data.nomor;
            }
        })
        .catch(() => {
            noInput.value = jenjangVal + '-001';
        })
        .finally(() => {
            noInput.classList.remove('animate-pulse');
        });
}

// Auto Calculate Umur (Berdasarkan Patokan Umur Group Aktif)
const PATOKAN_UMUR_PAGE = '<?= e($activeGroup['tanggal_patokan_umur'] ?? '2027-07-01') ?>';

function hitungUmurOtomatisPage(tanggalLahirStr) {
    const umurInput = document.getElementById('page_umur');
    const badgeUmur = document.getElementById('badge_umur_page');
    if (!umurInput) return;

    if (!tanggalLahirStr) {
        umurInput.value = '';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    const tglParts = tanggalLahirStr.split('-');
    if (tglParts.length < 3) return;
    const tglLahir = new Date(parseInt(tglParts[0], 10), parseInt(tglParts[1], 10) - 1, parseInt(tglParts[2], 10));

    const patokanParts = PATOKAN_UMUR_PAGE.split('-');
    const patokan = new Date(parseInt(patokanParts[0], 10), parseInt(patokanParts[1], 10) - 1, parseInt(patokanParts[2], 10));

    if (isNaN(tglLahir.getTime()) || isNaN(patokan.getTime())) {
        umurInput.value = '';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    if (tglLahir > patokan) {
        umurInput.value = 'Tanggal belum tiba';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    let years = patokan.getFullYear() - tglLahir.getFullYear();
    let months = patokan.getMonth() - tglLahir.getMonth();
    let days = patokan.getDate() - tglLahir.getDate();

    if (days < 0) {
        months--;
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    if (years < 0) {
        umurInput.value = 'Tanggal belum tiba';
        if (badgeUmur) badgeUmur.classList.add('hidden');
        return;
    }

    let hasil = years + ' Tahun';
    if (months > 0) {
        hasil += ' ' + months + ' Bulan';
    }

    umurInput.value = hasil;
    if (badgeUmur) {
        badgeUmur.innerText = hasil;
        badgeUmur.classList.remove('hidden');
    }
}

// On Jenis Pembayaran Change
function onJenisPembayaranChangePage() {
    const jenisSelect = document.getElementById('page_jenis_pembayaran');
    const displayInput = document.getElementById('page_biaya_display');
    const hiddenInput = document.getElementById('page_biaya');
    const infoText = document.getElementById('page_info_pembayaran');
    if (!jenisSelect || !displayInput || !hiddenInput) return;

    const val = jenisSelect.value;
    const defaultBiaya = parseFloat(displayInput.getAttribute('data-default-biaya') || '250000');

    if (val === 'Free') {
        displayInput.value = '0';
        displayInput.readOnly = true;
        displayInput.classList.add('bg-slate-100', 'text-slate-500');
        hiddenInput.value = 0;
        if (infoText) infoText.innerHTML = '<span class="text-emerald-600 font-bold">✓ Bebas biaya formulir (Gratis).</span>';
    } else {
        displayInput.value = new Intl.NumberFormat('id-ID').format(defaultBiaya);
        displayInput.readOnly = false;
        displayInput.classList.remove('bg-slate-100', 'text-slate-500');
        hiddenInput.value = defaultBiaya;
        if (infoText) {
            infoText.innerText = (val === 'Transfer') 
                ? 'Pembayaran melalui transfer bank.' 
                : 'Pembayaran tunai langsung di loket SPMB.';
        }
    }
}

// Submit Form Handler dengan Konfirmasi Data & Otomatis Cetak (Halaman Standalone)
function submitFormTambahSiswaBaruPage(e) {
    e.preventDefault();

    const form = document.getElementById('formTambahSiswaBaruPage');
    const btnSubmit = document.getElementById('btnSubmitPageSiswa');

    // Validasi dasar
    const namaLengkap = (document.getElementById('page_nama_lengkap')?.value || '').trim();
    if (!namaLengkap) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Siswa Wajib Diisi',
            text: 'Silakan masukkan nama lengkap calon peserta didik baru.',
            confirmButtonColor: '#0284c7'
        });
        document.getElementById('page_nama_lengkap')?.focus();
        return;
    }

    const noHp = (document.getElementById('page_no_hp')?.value || '').trim();
    if (!noHp) {
        Swal.fire({
            icon: 'warning',
            title: 'No. WhatsApp Wajib Diisi',
            text: 'Silakan masukkan nomor WhatsApp orang tua untuk konfirmasi pendaftaran.',
            confirmButtonColor: '#0284c7'
        });
        document.getElementById('page_no_hp')?.focus();
        return;
    }

    // Kumpulkan data untuk ditampilkan di Konfirmasi
    const jenjang = document.getElementById('page_jenjang')?.value || 'PAUD';
    const noFormulir = document.getElementById('page_nomor_pendaftaran')?.value || '-';
    const gelombang = document.getElementById('page_gelombang')?.value || '-';
    const jkVal = document.getElementById('page_jenis_kelamin')?.value || 'L';
    const jkText = jkVal === 'L' ? 'Laki-laki' : 'Perempuan';
    const tempatLahir = document.getElementById('page_tempat_lahir')?.value?.trim() || '';
    const tglLahir = document.getElementById('page_tanggal_lahir')?.value || '';
    const umur = document.getElementById('page_umur')?.value?.trim() || '-';
    const asalSekolah = document.getElementById('page_asal_sekolah')?.value?.trim() || '-';
    const namaAyah = document.getElementById('page_nama_ayah')?.value?.trim() || '-';
    const namaIbu = document.getElementById('page_nama_ibu')?.value?.trim() || '-';
    const alamat = document.getElementById('page_alamat')?.value?.trim() || '-';

    let ttlText = '-';
    if (tempatLahir || tglLahir) {
        ttlText = (tempatLahir || '') + (tglLahir ? (tempatLahir ? ', ' : '') + tglLahir : '');
    }

    // 1. POPUP KONFIRMASI DATA SEBELUM SIMPAN
    Swal.fire({
        title: 'Konfirmasi Data Calon Siswa',
        html: `
            <div class="text-left text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1.5 mt-2">
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Jenjang & No. Formulir:</span>
                    <span class="font-bold text-slate-800">${jenjang} &bull; <span class="font-mono text-sky-700 font-bold">${noFormulir}</span></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Nama Lengkap:</span>
                    <span class="font-bold text-slate-900">${namaLengkap}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Jenis Kelamin:</span>
                    <span class="font-semibold text-slate-800">${jkText}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Tempat, Tgl Lahir:</span>
                    <span class="font-semibold text-slate-800">${ttlText}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Umur Siswa:</span>
                    <span class="font-bold text-emerald-700">${umur}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Asal Sekolah:</span>
                    <span class="font-semibold text-slate-800">${asalSekolah}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Orang Tua:</span>
                    <span class="font-semibold text-slate-800">Ayah: ${namaAyah} | Ibu: ${namaIbu}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">No. WhatsApp:</span>
                    <span class="font-mono font-bold text-slate-800">${noHp}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Alamat:</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[220px]" title="${alamat}">${alamat}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Gelombang:</span>
                    <span class="font-semibold text-slate-800">${gelombang}</span>
                </div>
                <div class="flex justify-between pt-1 font-bold">
                    <span class="text-slate-700">Biaya Awal:</span>
                    <span class="text-emerald-600 font-bold">Bebas Biaya (Rp 0)</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-2.5 text-center">Pastikan data di atas sudah sesuai sebelum disimpan ke database.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0284c7',
        cancelButtonColor: '#64748b',
        confirmButtonText: '✓ Ya, Simpan Data',
        cancelButtonText: 'Cek Kembali'
    }).then((result) => {
        if (!result.isConfirmed) return;

        // 2. EKSEKUSI PENYIMPANAN
        Swal.fire({
            title: 'Menyimpan Data...',
            text: 'Mohon tunggu sebentar, sistem sedang memproses...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';
            let data = null;
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    if (response.ok) {
                        data = { success: true, message: 'Calon siswa baru berhasil didaftarkan!' };
                    } else {
                        data = { success: false, message: 'Terjadi kesalahan sistem (' + response.status + ')' };
                    }
                }
            }
            return { ok: response.ok, data: data };
        })
        .then(({ ok, data }) => {
            if (ok && data && data.success) {
                const cetakUrl = data.cetak_url || ('<?= url('kelola-spmb/siswa-baru/cetak/') ?>' + data.id + '?autoprint=1');

                // 3. OTOMATIS BUKA HALAMAN CETAK DI TAB BARU
                window.open(cetakUrl, '_blank');

                // NOTIFIKASI SUKSES DENGAN OPSI CETAK ULANG
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    html: `
                        <div class="space-y-3">
                            <p class="text-xs text-slate-600">
                                Calon siswa <strong>${namaLengkap}</strong> dengan No. Formulir <strong>${data.nomor || noFormulir}</strong> telah berhasil didaftarkan ke sistem SPMB.
                            </p>
                            <div class="p-3 bg-sky-50 border border-sky-200 rounded-xl text-xs text-sky-800 flex items-center gap-2.5 text-left">
                                <span class="text-2xl">🖨️</span>
                                <div>
                                    <div class="font-bold">Kwitansi Bukti Pendaftaran</div>
                                    <div class="text-[11px] text-sky-700">Kwitansi pembayaran pendaftaran telah otomatis dibuka pada tab baru untuk dicetak.</div>
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#0284c7',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '🖨️ Cetak Kwitansi Lagi',
                    cancelButtonText: 'Kembali ke Daftar'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.open(cetakUrl, '_blank');
                    }
                    window.location.href = '<?= url('kelola-spmb/siswa-baru') ?>';
                });
            } else {
                throw new Error((data && data.message) ? data.message : 'Gagal menyimpan data calon siswa.');
            }
        })
        .catch((error) => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: error.message || 'Terjadi kesalahan saat menghubungi server.',
                confirmButtonColor: '#e11d48'
            });
        });
    });
}
</script>
