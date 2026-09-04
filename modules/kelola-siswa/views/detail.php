<?php
/**
 * Detail Profil Siswa View - Portal BIP
 * Comprehensive Student Profile Display with all 50+ Dapodik fields
 */
$namaSiswa = $siswa['nama_lengkap'] ?: $siswa['nama'];
$isLaki = ($siswa['jenis_kelamin'] === 'L' || $siswa['jenis_kelamin'] === 'Laki-Laki');
$jenjang = strtoupper($siswa['jenjang'] ?? 'SD');

$jenjangBadge = [
    'PAUD' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'TK' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'SD' => 'bg-sky-100 text-sky-800 border-sky-300',
    'SMP' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
    'SMA' => 'bg-purple-100 text-purple-800 border-purple-300'
][$jenjang] ?? 'bg-slate-100 text-slate-800 border-slate-300';

$isDapodik = ($siswa['dapodik'] === 'Sudah');
?>

<div class="max-w-6xl space-y-6">

    <!-- Top Profile Banner & Action Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl <?= $isLaki ? 'bg-gradient-to-tr from-blue-500 to-sky-400 text-white' : 'bg-gradient-to-tr from-pink-500 to-rose-400 text-white' ?> flex items-center justify-center font-black text-2xl sm:text-3xl shadow-md flex-shrink-0">
                    <?= mb_substr($namaSiswa, 0, 1) ?>
                </div>
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                            <?= e($namaSiswa) ?>
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $jenjangBadge ?>">
                            <?= e($jenjang) ?> • Kelas <?= e($siswa['kelas'] ?: '-') ?>
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $isDapodik ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                            <?= $isDapodik ? '✓ Dapodik' : 'Non-Dapodik' ?>
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 font-medium">
                        <span>ID: <strong class="text-slate-800 font-mono"><?= e($siswa['id_siswa'] ?: '-') ?></strong></span>
                        <span>•</span>
                        <span>NIS: <strong class="text-slate-800 font-mono"><?= e($siswa['nis'] ?: '-') ?></strong></span>
                        <span>•</span>
                        <span>NISN: <strong class="text-slate-800 font-mono"><?= e($siswa['nisn'] ?: '-') ?></strong></span>
                        <span>•</span>
                        <span>JK: <strong class="text-slate-800"><?= $isLaki ? 'Laki-Laki (L)' : 'Perempuan (P)' ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="<?= url("kelola-siswa/cetak/{$siswa['id']}") ?>" target="_blank" class="px-4 py-2.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                    <span>Cetak Biodata</span>
                </a>

                <a href="<?= url("kelola-siswa/edit/{$siswa['id']}") ?>" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                    <span>Edit Profil</span>
                </a>

                <a href="<?= url('kelola-siswa') ?>" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Section Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 1. IDENTITAS & FISIK SISWA -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">👤</span>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Identitas & Fisik Siswa</h2>
            </div>

            <div class="grid grid-cols-2 gap-y-3.5 gap-x-4 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">NIK Siswa</span>
                    <span class="font-mono font-bold text-slate-800"><?= e($siswa['no_nik'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">No. Kartu Keluarga</span>
                    <span class="font-mono font-bold text-slate-800"><?= e($siswa['no_kk'] ?: '-') ?></span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">No. Akta Kelahiran</span>
                    <span class="font-mono font-bold text-slate-800"><?= e($siswa['no_registrasi_akta'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Tempat, Tgl Lahir</span>
                    <span class="font-bold text-slate-800">
                        <?= e($siswa['tempat_lahir'] ?: '-') ?>, 
                        <?= !empty($siswa['tgl_lahir']) ? date('d M Y', strtotime($siswa['tgl_lahir'])) : (!empty($siswa['tanggal_lahir']) ? date('d M Y', strtotime($siswa['tanggal_lahir'])) : '-') ?>
                        (<?= e($siswa['umur'] ?? '0') ?> Thn)
                    </span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Anak Ke-</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['anak_ke'] ?: '1') ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Kebutuhan Khusus</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['kebutuhan_khusus'] ?: 'Tidak Ada') ?></span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Tinggi / Berat Badan</span>
                    <span class="font-bold text-slate-800">
                        <?= e($siswa['tinggi_badan'] ? $siswa['tinggi_badan'] . ' cm' : '-') ?> / 
                        <?= e($siswa['berat_badan'] ? $siswa['berat_badan'] . ' kg' : '-') ?>
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Riwayat Alergi</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['nama_alergi'] ?: ($siswa['alergi'] ?: 'Tidak Ada')) ?></span>
                </div>

                <div class="col-span-2 pt-2 border-t border-slate-50">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Asal Sekolah Sebelumnya</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['asal_sekolah'] ?: '-') ?></span>
                </div>
            </div>
        </div>

        <!-- 2. DATA AKADEMIK & SEKOLAH -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center font-bold text-xs">🏫</span>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Data Akademik & Penempatan</h2>
            </div>

            <div class="grid grid-cols-2 gap-y-3.5 gap-x-4 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Jenjang Satuan</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold border <?= $jenjangBadge ?>">
                        <?= e($jenjang) ?>
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Rombel / Kelas</span>
                    <span class="font-bold text-slate-900 text-sm">Kelas <?= e($siswa['kelas'] ?: '-') ?></span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Tahun Ajaran</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['tahun_ajaran'] ?: '2026/2027') ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Semester</span>
                    <span class="font-bold text-slate-800">Semester <?= e($siswa['semester'] ?: 'Ganjil') ?></span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Status Sinkron Dapodik</span>
                    <span class="inline-flex items-center gap-1 font-bold text-xs <?= $isDapodik ? 'text-emerald-700' : 'text-slate-500' ?>">
                        <?= $isDapodik ? '✓ Terdaftar Dapodik' : '○ Belum Masuk Dapodik' ?>
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Status Keaktifan</span>
                    <span class="inline-flex items-center gap-1 font-bold text-xs <?= !empty($siswa['is_active']) ? 'text-emerald-700' : 'text-rose-600' ?>">
                        <?= !empty($siswa['is_active']) ? '🟢 Aktif Belajar' : '🔴 Non-Aktif / Pindah' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. ALAMAT & KONTAK -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">📍</span>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Alamat Domisili & Transportasi</h2>
            </div>

            <div class="grid grid-cols-2 gap-y-3.5 gap-x-4 text-xs">
                <div class="col-span-2">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Alamat Lengkap</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['alamat'] ?: '-') ?></span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">RT / RW / Dusun</span>
                    <span class="font-bold text-slate-800">
                        RT <?= e($siswa['rt'] ?: '-') ?> / RW <?= e($siswa['rw'] ?: '-') ?> (<?= e($siswa['dusun'] ?: '-') ?>)
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Kelurahan / Kecamatan</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['kelurahan'] ?: '-') ?>, <?= e($siswa['kecamatan'] ?: '-') ?></span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Kota / Provinsi</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['kota'] ?: 'Kota Palu') ?>, <?= e($siswa['provinsi'] ?: 'Sulawesi Tengah') ?> (<?= e($siswa['kode_pos'] ?: '-') ?>)</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Tinggal Bersama</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['tempat_tinggal'] ?: 'Bersama Orang Tua') ?></span>
                </div>

                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Moda Transportasi</span>
                    <span class="font-bold text-slate-800"><?= e($siswa['moda_transportasi'] ?: 'Sepeda Motor') ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">No. HP / WhatsApp</span>
                    <span class="font-mono font-bold text-emerald-700"><?= e($siswa['no_hp'] ?: ($siswa['telepon'] ?: '-')) ?></span>
                </div>
            </div>
        </div>

        <!-- 4. DATA ORANG TUA (AYAH & IBU) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-pink-100 text-pink-800 flex items-center justify-center font-bold text-xs">👨‍👩‍👧</span>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Data Orang Tua (Ayah & Ibu)</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <!-- AYAH -->
                <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-2">
                    <span class="text-[11px] font-extrabold text-blue-900 uppercase block">Data Ayah</span>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">Nama Ayah</span>
                        <span class="font-bold text-slate-800"><?= e($siswa['nama_ayah'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">NIK / Tahun Lahir</span>
                        <span class="font-mono text-slate-700"><?= e($siswa['nik_ayah'] ?: '-') ?> (<?= e($siswa['tahun_lahir_ayah'] ?: '-') ?>)</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">Pendidikan & Pekerjaan</span>
                        <span class="font-semibold text-slate-800"><?= e($siswa['pendidikan_ayah'] ?: '-') ?> • <?= e($siswa['pekerjaan_ayah'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">Penghasilan Bulanan</span>
                        <span class="font-semibold text-slate-700"><?= e($siswa['penghasilan_ayah'] ?: '-') ?></span>
                    </div>
                </div>

                <!-- IBU -->
                <div class="p-3.5 rounded-2xl bg-pink-50/50 border border-pink-100 space-y-2">
                    <span class="text-[11px] font-extrabold text-pink-900 uppercase block">Data Ibu</span>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">Nama Ibu</span>
                        <span class="font-bold text-slate-800"><?= e($siswa['nama_ibu'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">NIK / Tahun Lahir</span>
                        <span class="font-mono text-slate-700"><?= e($siswa['nik_ibu'] ?: '-') ?> (<?= e($siswa['tahun_lahir_ibu'] ?: '-') ?>)</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">Pendidikan & Pekerjaan</span>
                        <span class="font-semibold text-slate-800"><?= e($siswa['pendidikan_ibu'] ?: '-') ?> • <?= e($siswa['pekerjaan_ibu'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase">Penghasilan Bulanan</span>
                        <span class="font-semibold text-slate-700"><?= e($siswa['penghasilan_ibu'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
