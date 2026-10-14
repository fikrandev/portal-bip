<?php
/**
 * View Form Tambah Calon Siswa Daftar Tunggu SPMB
 * Portal BIP
 */
$activeGroup = $activeGroup ?? null;
$groups = $groups ?? [];
$autoNomor = $autoNomor ?? 'DT-PAUD-001';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:text-amber-600">SPMB</a>
                <span>&bull;</span>
                <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" class="hover:text-amber-600">Daftar Tunggu</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Formulir Pendaftaran</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Pendaftaran Calon Siswa Daftar Tunggu</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Input data calon siswa daftar tunggu (waiting list) dengan jalur rekomendasi Yayasan, Dinas, atau Lainnya.
            </p>
        </div>
        <div>
            <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                &larr; Kembali ke Tabel
            </a>
        </div>
    </div>

    <!-- Active Group Notice -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-600/10 to-transparent border border-amber-500/20 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <div>
                <p class="text-xs text-amber-950 font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    Tahun Group SPMB Aktif: <span class="underline decoration-amber-500"><?= e($activeGroup['nama_group'] ?? 'SPMB 2026/2027') ?></span>
                </p>
                <p class="text-[11px] text-slate-600 mt-0.5">
                    Data pendaftaran ini otomatis terhubung ke group aktif (TA <?= e($activeGroup['tahun_ajaran'] ?? '2026/2027') ?>).
                </p>
            </div>
        </div>
        <a href="<?= url('kelola-spmb/pengaturan') ?>" class="text-xs font-bold text-amber-700 hover:text-amber-800 shrink-0 hover:underline">
            Pengaturan &rarr;
        </a>
    </div>

    <!-- Form Body (1 Column Clean Flow) -->
    <div class="bg-white rounded-3xl border border-primary-100/70 shadow-sm overflow-hidden">
        <form id="formTambahDaftarTungguPage" action="<?= url('kelola-spmb/daftar-tunggu/store') ?>" method="POST" enctype="multipart/form-data" data-no-loading="true" data-confirm="true" onsubmit="event.preventDefault(); submitFormTambahDaftarTungguPage(event); return false;" class="p-6 sm:p-8 space-y-4 text-xs text-slate-700">
            <?= CSRF::field() ?>
            <input type="hidden" name="is_ajax" value="1">
            <input type="hidden" name="jenis_pendaftaran" value="Daftar Tunggu">
            <input type="hidden" name="group_id" value="<?= $activeGroup['id'] ?? 1 ?>">

            <!-- 1. Jenjang -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Jenjang Sekolah Tujuan <span class="text-rose-500">*</span>
                </label>
                <select name="jenjang" id="page_jenjang" onchange="onJenjangChangePageDaftarTunggu()" required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                    <option value="PAUD" selected>PAUD</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                </select>
            </div>

            <!-- 2. Rekomendasi (Dropdown: Yayasan, Dinas, Lainnya) -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Rekomendasi <span class="text-rose-500">*</span>
                </label>
                <select name="kategori_pendaftar" id="page_kategori_pendaftar" required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                    <option value="Yayasan" selected>🏢 Yayasan</option>
                    <option value="Dinas">🏛️ Dinas</option>
                    <option value="Lainnya">📌 Lainnya</option>
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Pilih kategori instansi / pihak pemberi rekomendasi.</p>
            </div>

            <!-- 3. Nama Rekomendasi -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Nama Rekomendasi / Detail Rekomendasi <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_rekomendasi" id="page_nama_rekomendasi" required 
                       placeholder="Contoh: Bpk. H. Ahmad (Pengurus Yayasan) / Pejabat Dinas Pendidikan..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                <p class="text-[10px] text-slate-400 mt-1">Tuliskan nama tokoh atau instansi pemberi rekomendasi.</p>
            </div>

            <!-- 4. No Formulir Auto (DT-Unit-001) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-slate-800">
                        No. Formulir <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-md">
                        Otomatis (Format: DT-Unit-001)
                    </span>
                </div>
                <div class="relative">
                    <input type="text" name="nomor_pendaftaran" id="page_nomor_pendaftaran" value="<?= e($autoNomor) ?>" required 
                           class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 bg-amber-50/60 font-mono font-bold text-amber-900 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                    <button type="button" onclick="onJenjangChangePageDaftarTunggu()" title="Generate Ulang Nomor" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-amber-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Otomatis berubah mengikuti pilihan jenjang (misal DT-PAUD-001, DT-SD-001, DT-SMP-001, DT-SMA-001).</p>
            </div>

            <!-- 5. Gelombang -->
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
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-800 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 6. Nama Lengkap -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Nama Lengkap Siswa <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_lengkap" id="page_nama_lengkap" required 
                       placeholder="Nama lengkap calon siswa sesuai akta..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 7. Jenis Kelamin -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Jenis Kelamin <span class="text-rose-500">*</span>
                </label>
                <select name="jenis_kelamin" id="page_jenis_kelamin" required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                    <option value="L" selected>Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>

            <!-- 8. Tempat Lahir -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Tempat Lahir <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="tempat_lahir" id="page_tempat_lahir" required placeholder="Contoh: Palu" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 9. Tanggal Lahir -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Tanggal Lahir <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_lahir" id="page_tanggal_lahir" required onchange="hitungUmurOtomatisPageDaftarTunggu(this.value)" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 10. Umur (Auto) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-slate-800">
                        Umur Siswa <span class="text-[10px] font-normal text-slate-500">(Per <?= !empty($activeGroup['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($activeGroup['tanggal_patokan_umur']) : '1 Juli 2027' ?>)</span>
                    </label>
                    <span id="page_badge_umur" class="hidden text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">
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

            <!-- 11. Asal Sekolah -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Asal Sekolah <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="asal_sekolah" id="page_asal_sekolah" required placeholder="Nama TK / PAUD / Sekolah sebelumnya..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 12. Nama Ayah -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Nama Ayah <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_ayah" id="page_nama_ayah" required placeholder="Nama lengkap ayah kandung..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 13. Nama Ibu -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Nama Ibu <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_ibu" id="page_nama_ibu" required placeholder="Nama lengkap ibu kandung..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 14. No WA -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    No. WhatsApp / HP Orang Tua <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="no_hp" id="page_no_hp" required placeholder="08xxxxxxxxxx" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-mono focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
            </div>

            <!-- 15. Alamat -->
            <div>
                <label class="block font-bold text-slate-800 mb-1">
                    Alamat Tempat Tinggal <span class="text-rose-500">*</span>
                </label>
                <textarea name="alamat" id="page_alamat" required rows="2" placeholder="Nama jalan, nomor rumah, kelurahan, kota..." 
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs"></textarea>
            </div>

            <!-- 16. Informasi Biaya Pendaftaran Awal (Gratis / Rp 0) -->
            <div class="p-3.5 bg-emerald-50/80 border border-emerald-200/90 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                        ✓
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-emerald-950">Biaya Pendaftaran Awal: Bebas Biaya</span>
                        <span class="block text-[11px] text-emerald-700">Tahap awal pendaftaran daftar tunggu tidak dipungut biaya (Gratis).</span>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm shrink-0">
                    Rp 0 (Gratis)
                </span>
                <input type="hidden" name="biaya_pendaftaran" id="page_biaya" value="0">
                <input type="hidden" name="status_pembayaran" value="Gratis">
                <input type="hidden" name="metode_pembayaran" value="Gratis">
                <input type="hidden" name="status_kelulusan" value="Cadangan">
            </div>

            <!-- Footer Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                <span class="text-[11px] text-slate-500 italic">
                    * Kolom bertanda bintang wajib diisi
                </span>
                <div class="flex items-center gap-2">
                    <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" 
                       class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                        Batal
                    </a>
                    <button type="button" id="btnSubmitPageDaftarTunggu" onclick="submitFormTambahDaftarTungguPage(event)" 
                            class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Simpan & Daftarkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const PAGE_TANGGAL_PATOKAN = <?= json_encode($activeGroup['tanggal_patokan_umur'] ?? '2027-07-01') ?>;

function onJenjangChangePageDaftarTunggu() {
    const jenjang = document.getElementById('page_jenjang').value;
    const noInput = document.getElementById('page_nomor_pendaftaran');
    if (!noInput) return;

    noInput.classList.add('opacity-50');

    fetch('<?= url('kelola-spmb/daftar-tunggu/get-nomor') ?>?jenjang=' + encodeURIComponent(jenjang))
        .then(res => res.json())
        .then(data => {
            if (data && data.success && data.nomor) {
                noInput.value = data.nomor;
            }
        })
        .catch(err => {
            console.warn('Gagal ambil nomor otomatis:', err);
        })
        .finally(() => {
            noInput.classList.remove('opacity-50');
        });
}

function hitungUmurOtomatisPageDaftarTunggu(tglLahirStr) {
    const inputUmur = document.getElementById('page_umur');
    const badge = document.getElementById('page_badge_umur');
    if (!tglLahirStr || !inputUmur) return;

    try {
        const dLahir = new Date(tglLahirStr);
        const dPatokan = new Date(PAGE_TANGGAL_PATOKAN);

        if (isNaN(dLahir.getTime())) {
            inputUmur.value = '';
            if (badge) badge.classList.add('hidden');
            return;
        }

        if (dLahir > dPatokan) {
            inputUmur.value = 'Tanggal belum tiba';
            if (badge) badge.classList.add('hidden');
            return;
        }

        let years = dPatokan.getFullYear() - dLahir.getFullYear();
        let months = dPatokan.getMonth() - dLahir.getMonth();
        let days = dPatokan.getDate() - dLahir.getDate();

        if (days < 0) months--;
        if (months < 0) { years--; months += 12; }

        let hasil = years + ' Tahun';
        if (months > 0) hasil += ' ' + months + ' Bulan';

        inputUmur.value = hasil;
        if (badge) badge.classList.remove('hidden');
    } catch (e) {
        inputUmur.value = '';
    }
}

function submitFormTambahDaftarTungguPage(event) {
    if (event) event.preventDefault();

    const form = document.getElementById('formTambahDaftarTungguPage');
    if (!form) return;

    const namaLengkap = (document.getElementById('page_nama_lengkap')?.value || '').trim();
    if (!namaLengkap) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Siswa Wajib Diisi',
            text: 'Silakan masukkan nama lengkap calon siswa daftar tunggu.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_nama_lengkap')?.focus();
        return;
    }

    const namaRekomendasi = (document.getElementById('page_nama_rekomendasi')?.value || '').trim();
    if (!namaRekomendasi) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Rekomendasi Wajib Diisi',
            text: 'Silakan tuliskan nama pihak atau instansi yang memberikan rekomendasi.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_nama_rekomendasi')?.focus();
        return;
    }

    const tempatLahir = (document.getElementById('page_tempat_lahir')?.value || '').trim();
    if (!tempatLahir) {
        Swal.fire({
            icon: 'warning',
            title: 'Tempat Lahir Wajib Diisi',
            text: 'Silakan masukkan tempat lahir calon siswa.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_tempat_lahir')?.focus();
        return;
    }

    const tglLahir = document.getElementById('page_tanggal_lahir')?.value || '';
    if (!tglLahir) {
        Swal.fire({
            icon: 'warning',
            title: 'Tanggal Lahir Wajib Diisi',
            text: 'Silakan pilih tanggal lahir calon siswa.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_tanggal_lahir')?.focus();
        return;
    }

    const asalSekolah = (document.getElementById('page_asal_sekolah')?.value || '').trim();
    if (!asalSekolah) {
        Swal.fire({
            icon: 'warning',
            title: 'Asal Sekolah Wajib Diisi',
            text: 'Silakan masukkan nama sekolah / TK sebelumnya.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_asal_sekolah')?.focus();
        return;
    }

    const namaAyah = (document.getElementById('page_nama_ayah')?.value || '').trim();
    if (!namaAyah) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Ayah Wajib Diisi',
            text: 'Silakan masukkan nama ayah kandung.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_nama_ayah')?.focus();
        return;
    }

    const namaIbu = (document.getElementById('page_nama_ibu')?.value || '').trim();
    if (!namaIbu) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Ibu Wajib Diisi',
            text: 'Silakan masukkan nama ibu kandung.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_nama_ibu')?.focus();
        return;
    }

    const noHp = (document.getElementById('page_no_hp')?.value || '').trim();
    if (!noHp) {
        Swal.fire({
            icon: 'warning',
            title: 'No. WhatsApp Wajib Diisi',
            text: 'Silakan masukkan nomor WhatsApp orang tua.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_no_hp')?.focus();
        return;
    }

    const alamat = (document.getElementById('page_alamat')?.value || '').trim();
    if (!alamat) {
        Swal.fire({
            icon: 'warning',
            title: 'Alamat Tempat Tinggal Wajib Diisi',
            text: 'Silakan masukkan alamat tempat tinggal calon siswa.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('page_alamat')?.focus();
        return;
    }

    const jenjang = document.getElementById('page_jenjang')?.value || 'PAUD';
    const katPendaftar = document.getElementById('page_kategori_pendaftar')?.value || 'Yayasan';
    const noFormulir = document.getElementById('page_nomor_pendaftaran')?.value || '-';
    const gelombang = document.getElementById('page_gelombang')?.value || '-';
    const jkVal = document.getElementById('page_jenis_kelamin')?.value || 'L';
    const jkText = jkVal === 'L' ? 'Laki-laki' : 'Perempuan';
    const umur = document.getElementById('page_umur')?.value?.trim() || '-';
    let ttlText = (tempatLahir || '') + (tglLahir ? (tempatLahir ? ', ' : '') + tglLahir : '');

    Swal.fire({
        title: 'Konfirmasi Data Calon Siswa Daftar Tunggu',
        html: `
            <div class="text-left text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1.5 mt-2">
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Jenjang & No. Formulir:</span>
                    <span class="font-bold text-slate-800">${jenjang} &bull; <span class="font-mono text-amber-700 font-bold">${noFormulir}</span></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Rekomendasi:</span>
                    <span class="font-bold text-amber-800">${katPendaftar}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Nama Rekomendasi:</span>
                    <span class="font-bold text-slate-900">${namaRekomendasi}</span>
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
                <div class="flex justify-between pt-1 font-bold">
                    <span class="text-slate-700">Biaya Awal:</span>
                    <span class="text-emerald-600 font-bold">Bebas Biaya (Rp 0)</span>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#64748b',
        confirmButtonText: '✓ Ya, Simpan Data',
        cancelButtonText: 'Cek Kembali'
    }).then((result) => {
        if (!result.isConfirmed) return;

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
                        data = { success: true, message: 'Calon siswa daftar tunggu berhasil didaftarkan!' };
                    } else {
                        data = { success: false, message: 'Terjadi kesalahan sistem (' + response.status + ')' };
                    }
                }
            }
            return { ok: response.ok, data: data };
        })
        .then(({ ok, data }) => {
            if (ok && data && data.success) {
                const cetakUrl = data.cetak_url || ('<?= url('kelola-spmb/daftar-tunggu/cetak/') ?>' + data.id + '?autoprint=1');

                window.open(cetakUrl, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    html: `
                        <div class="space-y-3">
                            <p class="text-xs text-slate-600">
                                Calon siswa <strong>${namaLengkap}</strong> dengan No. Formulir <strong>${data.nomor || noFormulir}</strong> telah berhasil didaftarkan.
                            </p>
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2.5 text-left">
                                <span class="text-2xl">🖨️</span>
                                <div>
                                    <div class="font-bold">Bukti Pendaftaran Daftar Tunggu</div>
                                    <div class="text-[11px] text-amber-700">Tanda bukti pendaftaran telah otomatis dibuka pada tab baru untuk dicetak.</div>
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '🖨️ Cetak Lagi',
                    cancelButtonText: 'Lihat Daftar'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.open(cetakUrl, '_blank');
                    }
                    window.location.href = '<?= url('kelola-spmb/daftar-tunggu') ?>';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan Data',
                    text: (data && data.message) ? data.message : 'Terjadi kesalahan saat memproses data pendaftaran.',
                    confirmButtonColor: '#d97706'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Bermasalah',
                text: 'Terjadi gangguan koneksi internet: ' + error.message,
                confirmButtonColor: '#d97706'
            });
        });
    });
}
</script>
