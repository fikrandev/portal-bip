<?php
/**
 * View Detail / Biodata Calon Siswa Daftar Tunggu SPMB
 * Portal BIP
 */
$item = $pendaftar ?? [];
$st = $item['status_kelulusan'] ?? 'Cadangan';
$badgeStyle = match($st) {
    'Lulus / Diterima' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'Lolos Berkas'     => 'bg-blue-100 text-blue-800 border-blue-300',
    'Cadangan'         => 'bg-amber-100 text-amber-800 border-amber-300',
    'Tidak Lulus'      => 'bg-rose-100 text-rose-800 border-rose-300',
    'Dibatalkan'       => 'bg-slate-100 text-slate-600 border-slate-300',
    default            => 'bg-amber-100 text-amber-800 border-amber-300'
};
?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-amber-500/25 shrink-0">
                <?= strtoupper(substr($item['nama_lengkap'] ?? 'D', 0, 1)) ?>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900"><?= e($item['nama_lengkap'] ?? '-') ?></h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?>">
                        <?= e($st) ?>
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">
                    No. Pendaftaran: <strong class="text-amber-700"><?= e($item['nomor_pendaftaran'] ?? '-') ?></strong> &bull; Group: <?= e($item['nama_group'] ?? 'SPMB') ?> (<?= e($item['group_tahun_ajaran'] ?? '-') ?>)
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Tombol Promosikan ke Siswa Baru -->
            <button type="button" onclick="bukaModalPromosi()"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
                <span>Promosikan ke Siswa Baru</span>
            </button>
            <a href="<?= url('kelola-spmb/daftar-tunggu/cetak/' . $item['id']) ?>" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5Z" /></svg>
                <span>Cetak Bukti Pendaftaran</span>
            </a>
            <a href="<?= url('kelola-spmb/daftar-tunggu/edit/' . $item['id']) ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                <span>Edit</span>
            </a>
            <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Quick Status Update Card -->
    <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Update Status Seleksi Cepat</h3>
            <p class="text-xs text-slate-500">Ubah langsung status verifikasi & kelulusan siswa daftar tunggu ini.</p>
        </div>
        <form action="<?= url('kelola-spmb/daftar-tunggu/status/' . $item['id']) ?>" method="POST" class="flex items-center gap-2">
            <?= CSRF::field() ?>
            <select name="status_kelulusan" class="px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold bg-slate-50 focus:bg-white outline-none">
                <?php foreach (['Cadangan', 'Menunggu Seleksi', 'Lolos Berkas', 'Lulus / Diterima', 'Tidak Lulus', 'Dibatalkan'] as $statusOpt): ?>
                    <option value="<?= $statusOpt ?>" <?= ($st === $statusOpt) ? 'selected' : '' ?>><?= $statusOpt ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition-colors">
                Terapkan
            </button>
        </form>
    </div>

    <!-- 2 Column Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left Column: Info Pendaftaran & Rekomendasi -->
        <div class="space-y-6">
            <!-- Ringkasan Pendaftaran -->
            <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm space-y-3">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Informasi Pendaftaran
                </h3>
                
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Jenjang</span>
                        <span class="font-bold text-slate-800"><?= e($item['jenjang']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50 items-center">
                        <span class="text-slate-500">Rekomendasi</span>
                        <?php 
                        $katDetail = $item['kategori_pendaftar'] ?? 'Yayasan';
                        $badgeDetailColor = match($katDetail) {
                            'Yayasan' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'Dinas'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            default   => 'bg-amber-100 text-amber-800 border-amber-200',
                        };
                        $iconDetail = match($katDetail) {
                            'Yayasan' => '🏢',
                            'Dinas'   => '🏛️',
                            default   => '📌',
                        };
                        ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border <?= $badgeDetailColor ?>">
                            <?= $iconDetail ?> <?= e($katDetail) ?>
                        </span>
                    </div>
                    <?php if (!empty($item['nama_rekomendasi'])): ?>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nama Rekomendasi</span>
                        <span class="font-bold text-slate-900"><?= e($item['nama_rekomendasi']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Gelombang</span>
                        <span class="font-bold text-slate-800"><?= e($item['gelombang'] ?? 'Gelombang 1') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Tanggal Mendaftar</span>
                        <span class="font-bold text-slate-800"><?= date('d F Y', strtotime($item['tanggal_daftar'])) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Biaya Formulir</span>
                        <span class="font-bold text-slate-800">Rp <?= number_format($item['biaya_pendaftaran'] ?? 0, 0, ',', '.') ?> (Gratis)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Status Pembayaran</span>
                        <span class="font-bold text-emerald-600"><?= e($item['status_pembayaran'] ?? 'Gratis') ?></span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Kelengkapan Berkas</span>
                        <span class="font-bold text-slate-800"><?= e($item['status_berkas']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Asal Sekolah -->
            <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm space-y-2">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Sekolah Sebelumnya
                </h3>
                <p class="text-sm font-bold text-slate-800"><?= e($item['asal_sekolah'] ?: 'Tidak dicantumkan / Belum sekolah') ?></p>
            </div>

            <?php if (!empty($item['catatan'])): ?>
                <div class="bg-amber-50/50 p-5 rounded-2xl border border-amber-200/60 space-y-1 text-xs">
                    <p class="font-bold text-amber-800">Catatan Panitia SPMB:</p>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($item['catatan'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column (Span 2): Biodata Calon Siswa & Orang Tua -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- Biodata Siswa -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span>Identitas Calon Siswa</span>
                    <span class="text-slate-400 font-mono text-[10px]">ID: #<?= (int)$item['id'] ?></span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Lengkap</span>
                        <span class="font-bold text-slate-800 text-sm"><?= e($item['nama_lengkap']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Panggilan</span>
                        <span class="font-semibold text-slate-700"><?= e($item['nama_panggilan'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-700"><?= ($item['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan' ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">NISN / NIK</span>
                        <span class="font-mono text-slate-700"><?= e($item['nisn'] ?: '-') ?> / <?= e($item['nik'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-slate-700">
                            <?= e($item['tempat_lahir'] ?: '-') ?>, <?= !empty($item['tanggal_lahir']) ? date('d F Y', strtotime($item['tanggal_lahir'])) : '-' ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Umur Calon Siswa</span>
                        <span class="font-bold text-emerald-700"><?= e($item['umur'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Agama</span>
                        <span class="font-semibold text-slate-700"><?= e($item['agama'] ?: 'Islam') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Anak ke / Jumlah Saudara</span>
                        <span class="font-semibold text-slate-700"><?= e($item['anak_ke'] ?: '-') ?> dari <?= e($item['jumlah_saudara'] ?: '-') ?> bersaudara</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block text-[11px]">Alamat Lengkap</span>
                        <span class="font-semibold text-slate-800 leading-relaxed"><?= nl2br(e($item['alamat'] ?: '-')) ?></span>
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Data Orang Tua / Wali
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    <!-- Ayah -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="font-bold text-slate-800 text-sm border-b border-slate-200 pb-1 flex items-center justify-between">
                            <span>Ayah Kandung</span>
                            <span class="text-[10px] text-slate-400">👨</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Nama Lengkap</span>
                            <span class="font-bold text-slate-800"><?= e($item['nama_ayah'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">No. HP / WA</span>
                            <span class="font-mono text-slate-700"><?= e($item['no_hp_ayah'] ?: ($item['no_hp'] ?: '-')) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Pekerjaan</span>
                            <span class="text-slate-700"><?= e($item['pekerjaan_ayah'] ?: '-') ?></span>
                        </div>
                    </div>

                    <!-- Ibu -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="font-bold text-slate-800 text-sm border-b border-slate-200 pb-1 flex items-center justify-between">
                            <span>Ibu Kandung</span>
                            <span class="text-[10px] text-slate-400">👩</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Nama Lengkap</span>
                            <span class="font-bold text-slate-800"><?= e($item['nama_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">No. HP / WA</span>
                            <span class="font-mono text-slate-700"><?= e($item['no_hp_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Pekerjaan</span>
                            <span class="text-slate-700"><?= e($item['pekerjaan_ibu'] ?: '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Berkas / Dokumen Unggahan -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Berkas Persyaratan
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <?php
                    $berkasItems = [
                        ['label' => 'Pasfoto Calon Siswa', 'key' => 'foto'],
                        ['label' => 'Kartu Keluarga (KK)', 'key' => 'berkas_kk'],
                        ['label' => 'Akta Kelahiran', 'key' => 'berkas_akta'],
                        ['label' => 'KTP Orang Tua', 'key' => 'berkas_ktp_ayah']
                    ];
                    foreach ($berkasItems as $b):
                        $fileVal = $item[$b['key']] ?? '';
                    ?>
                        <div class="p-3 rounded-xl border border-slate-200 text-center space-y-2 flex flex-col justify-between">
                            <span class="text-[11px] font-bold text-slate-700 block"><?= $b['label'] ?></span>
                            <?php if (!empty($fileVal)): ?>
                                <a href="<?= url($fileVal) ?>" target="_blank" 
                                   class="inline-block py-1.5 px-3 rounded-lg bg-sky-50 text-sky-700 font-bold text-[11px] hover:bg-sky-100 transition-colors">
                                    Lihat Berkas &rarr;
                                </a>
                            <?php else: ?>
                                <span class="inline-block py-1 px-2 rounded-lg bg-slate-100 text-slate-400 text-[10px]">
                                    Belum Diunggah
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Promosi ke Siswa Baru (Detail Lengkap & Konfirmasi) -->
<div id="modalPromosi" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[92vh] flex flex-col" onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white flex items-center justify-between shrink-0 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center font-bold text-lg shadow-inner">
                    🚀
                </div>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight">Detail Calon Siswa & Promosi</h3>
                    <p class="text-[11px] text-emerald-100 mt-0.5">Tinjau kelengkapan data sebelum dipromosikan ke Siswa Baru</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalPromosi()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form Body -->
        <form action="<?= url('kelola-spmb/daftar-tunggu/promosikan/' . $item['id']) ?>" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <?= CSRF::field() ?>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs text-slate-700">
                
                <!-- Status & Nomor Banner -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">No. Daftar Tunggu Saat Ini:</span>
                        <span class="font-mono font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-lg border border-amber-200">
                            <?= e($item['nomor_pendaftaran']) ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-emerald-200/60">
                        <span class="text-slate-500 font-medium">No. Siswa Baru Resmi:</span>
                        <span class="font-mono font-bold text-emerald-800 bg-emerald-100/90 px-2.5 py-0.5 rounded-lg border border-emerald-300">
                            ✨ Otomatis Digenerate Resmi
                        </span>
                    </div>
                </div>

                <!-- Bagian 1: Informasi Pendaftaran & Rekomendasi -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/90 space-y-2.5 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">1. Data Pendaftaran & Rekomendasi</span>
                        <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                            <?= e($item['jenjang']) ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Rekomendasi</span>
                            <span class="font-bold text-slate-800"><?= e($item['kategori_pendaftar'] ?? 'Yayasan') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Nama Rekomendasi</span>
                            <span class="font-bold text-slate-800"><?= e($item['nama_rekomendasi'] ?? '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Gelombang</span>
                            <span class="font-semibold text-slate-700"><?= e($item['gelombang'] ?? 'Gelombang 1') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Tanggal Mendaftar</span>
                            <span class="font-semibold text-slate-700"><?= date('d F Y', strtotime($item['tanggal_daftar'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Identitas Calon Siswa -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/90 space-y-2.5 shadow-xs">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block border-b border-slate-100 pb-2">2. Identitas Calon Siswa</span>
                    <div class="space-y-2 pt-1 text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Nama Lengkap Siswa</span>
                            <span class="text-sm font-extrabold text-slate-900"><?= e($item['nama_lengkap']) ?></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 text-[11px] block">Jenis Kelamin</span>
                                <span class="font-semibold text-slate-800"><?= ($item['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan' ?></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Umur Siswa</span>
                                <span class="font-semibold text-slate-800"><?= e($item['umur'] ?: '-') ?></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 text-[11px] block">Tempat, Tanggal Lahir</span>
                                <span class="font-semibold text-slate-800">
                                    <?= e($item['tempat_lahir'] ?? '-') ?>, <?= !empty($item['tanggal_lahir']) ? date('d/m/Y', strtotime($item['tanggal_lahir'])) : '-' ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Asal Sekolah</span>
                                <span class="font-semibold text-slate-800"><?= e($item['asal_sekolah'] ?? '-') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: Orang Tua & Kontak -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/90 space-y-2.5 shadow-xs">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block border-b border-slate-100 pb-2">3. Data Orang Tua & Kontak</span>
                    <div class="space-y-2 pt-1 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 text-[11px] block">Nama Ayah</span>
                                <span class="font-semibold text-slate-800"><?= e($item['nama_ayah'] ?? '-') ?></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Nama Ibu</span>
                                <span class="font-semibold text-slate-800"><?= e($item['nama_ibu'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">No. WhatsApp / HP Orang Tua</span>
                            <span class="font-mono font-bold text-slate-900"><?= e($item['no_hp'] ?: ($item['no_wa'] ?: '-')) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Alamat Tinggal</span>
                            <span class="font-normal text-slate-700"><?= e($item['alamat'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Catatan Info & Opsi WA -->
                <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-2xl text-[11px] text-amber-900 leading-relaxed">
                    ℹ️ <strong>Catatan:</strong> Setelah menekan tombol <strong>Lanjutkan</strong>, calon siswa akan resmi berstatus <strong>Siswa Baru</strong> dan ditandai badge <em>Eks DT</em>. Selanjutnya orang tua dapat mengisi formulir online biodata lengkap untuk kebutuhan berkas Dapodik/EMIS.
                </div>

                <label class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs cursor-pointer hover:bg-slate-100 transition-colors">
                    <input type="checkbox" name="kirim_wa" value="1" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 mt-0.5">
                    <div class="text-slate-700">
                        <span class="font-bold text-slate-900">Kirim notifikasi WhatsApp ke nomor orang tua</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kirimkan pesan otomatis berisi pemberitahuan dan tautan formulir biodata lengkap ke nomor <?= e($item['no_hp'] ?: ($item['no_wa'] ?: '-')) ?>.</p>
                    </div>
                </label>

            </div>

            <!-- Modal Footer Actions -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0 gap-3">
                <button type="button" onclick="tutupModalPromosi()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <span>Lanjutkan Promosikan ke Siswa Baru</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalPromosi() {
    const modal = document.getElementById('modalPromosi');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}
function tutupModalPromosi() {
    const modal = document.getElementById('modalPromosi');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}
</script>
