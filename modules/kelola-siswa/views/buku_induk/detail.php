<?php
/**
 * Lembar Detail Buku Induk Siswa - Portal BIP
 */
?>
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Action Bar -->
    <div class="flex items-center justify-between">
        <a href="<?= url('kelola-siswa/buku-induk') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            <span>Kembali ke Buku Induk</span>
        </a>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-siswa/buku-induk/' . $siswa['id'] . '/cetak') ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                <span>Cetak Lembar Buku Induk</span>
            </a>
            <a href="<?= url('kelola-siswa/edit/' . $siswa['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                <span>Edit Data</span>
            </a>
        </div>
    </div>

    <!-- Main Sheet Container (Document Style) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-8">
        
        <!-- Sheet Header -->
        <div class="text-center pb-6 border-b border-slate-200">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">LEMBAR BUKU INDUK PESERTA DIDIK</h2>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1"><?= SYS_APP_NAME ?></h1>
            <p class="text-xs text-slate-500 mt-1">Nomor Induk Siswa (NIS): <span class="font-mono font-bold text-indigo-700"><?= e($siswa['nis'] ?: '-') ?></span> | NISN: <span class="font-mono font-bold text-slate-800"><?= e($siswa['nisn'] ?: '-') ?></span></p>
        </div>

        <!-- Section 1: Data Pribadi Siswa -->
        <div>
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">A</span>
                Keterangan Pribadi Peserta Didik
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">1. Nama Lengkap:</span>
                    <span class="font-bold text-slate-900 text-right"><?= e($siswa['nama_lengkap'] ?: $siswa['nama']) ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">2. Jenis Kelamin:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= ($siswa['jenis_kelamin'] === 'L' || $siswa['jenis_kelamin'] === 'Laki-Laki') ? 'Laki-Laki' : 'Perempuan' ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">3. Tempat, Tgl Lahir:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['tempat_lahir'] ?: '-') ?>, <?= $siswa['tgl_lahir'] ? date('d F Y', strtotime($siswa['tgl_lahir'])) : '-' ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">4. NIK (Kependudukan):</span>
                    <span class="font-mono font-semibold text-slate-800 text-right"><?= e($siswa['no_nik'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">5. No. Kartu Keluarga (KK):</span>
                    <span class="font-mono font-semibold text-slate-800 text-right"><?= e($siswa['no_kk'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">6. No. Registrasi Akta Lahir:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['no_registrasi_akta'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">7. Anak Ke:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['anak_ke'] ?: '1') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">8. Kebutuhan Khusus:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['kebutuhan_khusus'] ?: 'Tidak Ada') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">9. Asal Sekolah Sebelumnya:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['asal_sekolah'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">10. Satuan & Kelas Masuk:</span>
                    <span class="font-bold text-indigo-700 text-right"><?= e($siswa['jenjang']) ?> - <?= e($siswa['kelas']) ?> (<?= e($siswa['tahun_ajaran']) ?>)</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Data Orang Tua Kandung / Wali -->
        <div>
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">B</span>
                Keterangan Orang Tua Kandung & Wali
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Ayah -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60 space-y-2 text-xs">
                    <p class="font-bold text-slate-900 border-b border-slate-200 pb-1.5 text-xs text-indigo-800">DATA AYAH KANDUNG</p>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Ayah:</span>
                        <span class="font-bold text-slate-900"><?= e($siswa['nama_ayah'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">NIK Ayah:</span>
                        <span class="font-mono text-slate-800"><?= e($siswa['nik_ayah'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Pendidikan:</span>
                        <span class="text-slate-800 font-medium"><?= e($siswa['pendidikan_ayah'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Pekerjaan:</span>
                        <span class="text-slate-800 font-medium"><?= e($siswa['pekerjaan_ayah'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Penghasilan:</span>
                        <span class="text-slate-800 font-medium"><?= e($siswa['penghasilan_ayah'] ?: '-') ?></span>
                    </div>
                </div>

                <!-- Ibu -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60 space-y-2 text-xs">
                    <p class="font-bold text-slate-900 border-b border-slate-200 pb-1.5 text-xs text-pink-800">DATA IBU KANDUNG</p>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Ibu:</span>
                        <span class="font-bold text-slate-900"><?= e($siswa['nama_ibu'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">NIK Ibu:</span>
                        <span class="font-mono text-slate-800"><?= e($siswa['nik_ibu'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Pendidikan:</span>
                        <span class="text-slate-800 font-medium"><?= e($siswa['pendidikan_ibu'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Pekerjaan:</span>
                        <span class="text-slate-800 font-medium"><?= e($siswa['pekerjaan_ibu'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Penghasilan:</span>
                        <span class="text-slate-800 font-medium"><?= e($siswa['penghasilan_ibu'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Tempat Tinggal & Kontak -->
        <div>
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">C</span>
                Tempat Tinggal & Kontak
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">Alamat Jalan:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['alamat'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">RT / RW:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['rt'] ?: '-') ?> / <?= e($siswa['rw'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">Kelurahan / Desa:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['kelurahan'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">Kecamatan:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['kecamatan'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">Kabupaten / Kota:</span>
                    <span class="font-semibold text-slate-800 text-right"><?= e($siswa['kota'] ?: 'Kota Palu') ?></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 font-medium">No. Telepon / WhatsApp:</span>
                    <span class="font-semibold text-slate-800 text-right font-mono"><?= e($siswa['no_hp'] ?: '-') ?></span>
                </div>
            </div>
        </div>

    </div>

</div>
