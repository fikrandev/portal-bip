<?php
/**
 * Kartu Observasi Digital Publik (Dibuka Orang Tua dari Link WA untuk Scan Absensi)
 */
$logoUrl = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
}
$jUpper = strtoupper(trim((string)($item['jenjang'] ?? '')));
if (str_contains($jUpper, 'PAUD') || str_contains($jUpper, 'TK') || str_contains($jUpper, 'KB')) {
    $headStyle = 'background: linear-gradient(135deg, #facc15, #eab308); color: #422006;';
    $subCls = 'text-amber-950/80';
} elseif (str_contains($jUpper, 'SMA') || str_contains($jUpper, 'MA') || str_contains($jUpper, 'SMK')) {
    $headStyle = 'background: linear-gradient(135deg, #64748b, #94a3b8); color: #ffffff;';
    $subCls = 'text-slate-100';
} elseif (str_contains($jUpper, 'SMP') || str_contains($jUpper, 'MTS')) {
    $headStyle = 'background: linear-gradient(135deg, #0f172a, #1e3a8a); color: #ffffff;';
    $subCls = 'text-blue-200';
} else {
    $headStyle = 'background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: #ffffff;';
    $subCls = 'text-blue-100';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Observasi SPMB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <?php if (empty($item)): ?>
        <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center shadow-xl border border-slate-200">
            <div class="text-3xl mb-3">⚠️</div>
            <h1 class="text-lg font-extrabold text-slate-900">Kartu Observasi Tidak Ditemukan</h1>
            <p class="text-xs text-slate-500 mt-1">Kode QR tidak valid atau sudah tidak aktif.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 flex items-center gap-3" style="<?= $headStyle ?>">
                <?php if ($logoUrl): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="w-11 h-11 rounded-2xl bg-white p-1 object-contain">
                <?php endif; ?>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider <?= $subCls ?>">Kartu Peserta Observasi</div>
                    <h1 class="text-base font-extrabold"><?= htmlspecialchars($item['nama_group'] ?? 'SPMB SIT Bina Insan Palu') ?></h1>
                </div>
            </div>

            <div class="p-6 space-y-5 text-center">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Jenjang <?= htmlspecialchars($item['jenjang']) ?></span>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-2"><?= htmlspecialchars($item['nama_lengkap']) ?></h2>
                    <p class="text-xs font-mono text-slate-500 mt-0.5">No. Peserta: <strong><?= htmlspecialchars($item['nomor_peserta']) ?></strong></p>
                </div>

                <div class="inline-block p-4 rounded-2xl bg-white border-2 border-slate-200 shadow-xs">
                    <div id="publikQrBox" class="mx-auto"></div>
                    <div class="text-[11px] font-mono text-slate-500 mt-2"><?= htmlspecialchars($item['qr_token']) ?></div>
                </div>

                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-left text-xs space-y-1.5">
                    <?php if (!empty($item['urutan_sesi'])): ?>
                        <div class="flex justify-between"><span class="text-slate-500">No. Urut Sesi:</span><strong class="text-blue-700 font-extrabold">#<?= str_pad((string)(int)$item['urutan_sesi'], 2, '0', STR_PAD_LEFT) ?></strong></div>
                    <?php endif; ?>
                    <div class="flex justify-between"><span class="text-slate-500">Ruangan:</span><strong class="text-slate-900"><?= htmlspecialchars($item['nama_ruangan'] ?: 'Belum Ditentukan') ?></strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tanggal:</span><strong class="text-slate-900"><?= !empty($item['tgl_sesi']) ? date('d/m/Y', strtotime($item['tgl_sesi'])) : '-' ?></strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Sesi &amp; Jam:</span><strong class="text-indigo-700"><?= !empty($item['sesi_nama']) ? htmlspecialchars($item['sesi_nama'] . ' (' . $item['jam_mulai'] . ' - ' . $item['jam_selesai'] . ')') : '-' ?></strong></div>
                    <div class="flex justify-between"><span class="text-slate-500">Status Absen:</span>
                        <strong class="<?= $item['status_absen'] === 'Hadir' ? 'text-emerald-600' : 'text-amber-600' ?>"><?= htmlspecialchars($item['status_absen']) ?></strong>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400">Tunjukkan QR Code ini kepada panitia saat tiba di lokasi observasi.</p>
            </div>
        </div>
        <script>
            new QRCode(document.getElementById('publikQrBox'), {
                text: <?= json_encode($item['absen_qr_url'] ?? $item['qr_token']) ?>,
                width: 170,
                height: 170,
                correctLevel: QRCode.CorrectLevel.M
            });
        </script>
    <?php endif; ?>
</body>
</html>
