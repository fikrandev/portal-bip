<?php
/**
 * Cetak Surat Permohonan & Izin Cuti Pegawai
 * Portal BIP
 */
$namaPegawai = ($cuti['nama_pegawai'] ?? '-') . (!empty($cuti['gelar']) ? ', ' . $cuti['gelar'] : '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin Cuti - <?= e($cuti['nomor_pengajuan']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 py-8 px-4">
    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200 print:shadow-none print:border-0 print:p-0">
        <!-- Print Toolbar -->
        <div class="no-print flex items-center justify-between mb-6 pb-4 border-b border-slate-200">
            <a href="<?= url('kelola-cuti?tab=riwayat') ?>" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                &larr; Kembali ke Kelola Cuti
            </a>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                🖨️ Cetak Surat Cuti
            </button>
        </div>

        <!-- Header Kop -->
        <div class="text-center border-b-2 border-slate-900 pb-4 mb-6">
            <h1 class="text-lg font-extrabold uppercase tracking-wide">YAYASAN BINA INSAN PALU</h1>
            <h2 class="text-base font-bold uppercase text-slate-700">SEKOLAH ISLAM TERPADU (SIT) BINA INSAN PALU</h2>
            <p class="text-xs text-slate-500 mt-1">Formulir Permohonan & Surat Persetujuan Cuti Pegawai</p>
        </div>

        <div class="text-center mb-6">
            <h3 class="text-base font-extrabold underline uppercase">SURAT KETERANGAN / IZIN CUTI PEGAWAI</h3>
            <p class="text-xs font-mono font-bold text-slate-600 mt-1">Nomor: <?= e($cuti['nomor_pengajuan']) ?></p>
        </div>

        <!-- Biodata Pegawai -->
        <div class="space-y-2 text-sm mb-6">
            <p class="font-semibold text-slate-700 mb-2">Data Pegawai yang mengajukan cuti:</p>
            <table class="w-full text-sm">
                <tr>
                    <td class="py-1 w-48 text-slate-600">Nama Lengkap</td>
                    <td class="py-1 w-4">:</td>
                    <td class="py-1 font-bold"><?= e($namaPegawai) ?></td>
                </tr>
                <tr>
                    <td class="py-1 text-slate-600">NIY / NIK</td>
                    <td class="py-1">:</td>
                    <td class="py-1 font-mono"><?= e($cuti['niy'] ?: '-') ?></td>
                </tr>
                <tr>
                    <td class="py-1 text-slate-600">Unit Tugas / Jabatan</td>
                    <td class="py-1">:</td>
                    <td class="py-1"><?= e($cuti['unit_tugas'] ?: '-') ?> / <?= e($cuti['jabatan'] ?: '-') ?></td>
                </tr>
            </table>
        </div>

        <!-- Detail Cuti -->
        <div class="space-y-2 text-sm mb-6">
            <p class="font-semibold text-slate-700 mb-2">Rincian Pelaksanaan Cuti:</p>
            <table class="w-full text-sm border border-slate-300">
                <tr class="border-b border-slate-200">
                    <td class="py-2 px-3 w-48 bg-slate-50 font-semibold text-slate-600">Jenis Cuti</td>
                    <td class="py-2 px-3 font-bold"><?= e($cuti['nama_jenis']) ?> (Kategori: <?= e($cuti['kategori_cuti']) ?>)</td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="py-2 px-3 bg-slate-50 font-semibold text-slate-600">Tanggal Pelaksanaan</td>
                    <td class="py-2 px-3 font-bold"><?= date('d/m/Y', strtotime($cuti['tanggal_mulai'])) ?> s.d. <?= date('d/m/Y', strtotime($cuti['tanggal_selesai'])) ?> (<?= (int)$cuti['jumlah_hari'] ?> Hari)</td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="py-2 px-3 bg-slate-50 font-semibold text-slate-600">Alasan / Keperluan</td>
                    <td class="py-2 px-3"><?= e($cuti['alasan']) ?></td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="py-2 px-3 bg-slate-50 font-semibold text-slate-600">Pelimpahan Tugas</td>
                    <td class="py-2 px-3"><?= e($cuti['pelimpahan_tugas'] ?: '-') ?></td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="py-2 px-3 bg-slate-50 font-semibold text-slate-600">Pemotongan Saldo Tahunan</td>
                    <td class="py-2 px-3"><?= (int)$cuti['potong_saldo'] > 0 ? 'Dipakai ' . (int)$cuti['potong_saldo'] . ' Hari (Sisa Saldo Saat Ini: ' . (int)$cuti['saldo_saat_ini'] . ' Hari)' : 'Tidak Memotong Cuti Tahunan (0 Hari)' ?></td>
                </tr>
                <tr>
                    <td class="py-2 px-3 bg-slate-50 font-semibold text-slate-600">Status Persetujuan</td>
                    <td class="py-2 px-3 font-extrabold uppercase"><?= e($cuti['status']) ?> <?= !empty($cuti['catatan_approval']) ? ' — "' . e($cuti['catatan_approval']) . '"' : '' ?></td>
                </tr>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="grid grid-cols-2 gap-8 mt-12 text-sm text-center">
            <div>
                <p class="text-slate-600">Pemohon Cuti,</p>
                <div class="h-20"></div>
                <p class="font-bold underline"><?= e($namaPegawai) ?></p>
                <p class="text-xs text-slate-500">NIY: <?= e($cuti['niy'] ?: '-') ?></p>
            </div>
            <div>
                <p class="text-slate-600">Palu, <?= date('d/m/Y', strtotime($cuti['approved_at'] ?: $cuti['created_at'])) ?></p>
                <p class="text-slate-600">Menyetujui / Pejabat Berwenang,</p>
                <div class="h-20"></div>
                <p class="font-bold underline"><?= e($cuti['approved_by_name'] ?: 'Pimpinan / Kepegawaian') ?></p>
                <p class="text-xs text-slate-500">SIT Bina Insan Palu</p>
            </div>
        </div>
    </div>
</body>
</html>

