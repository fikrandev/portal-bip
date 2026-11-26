<?php
/**
 * Halaman Publik Persetujuan & Tanda Tangan Digital Surat Keluar Kepala Sekolah
 * Portal BIP
 *
 * @var array|null $surat
 * @var array      $kop
 * @var bool       $notFound
 * @var string|null $errorMsg
 */
$notFound = $notFound ?? false;
$isApproved = !$notFound && (($surat['approval_status'] ?? '') === 'approved');
$isRejected = !$notFound && (($surat['approval_status'] ?? '') === 'rejected');

$bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$tglIndo = '-';
if (!empty($surat['tanggal_surat'])) {
    $ts = strtotime($surat['tanggal_surat']);
    $tglIndo = date('j', $ts) . ' ' . ($bulanIndo[(int)date('n', $ts)] ?? '') . ' ' . date('Y', $ts);
}
$kopGambar = !empty($kop['kop_gambar']) ? url('public/' . ltrim($kop['kop_gambar'], '/')) : '';
$capGambar = !empty($kop['cap_gambar']) ? url('public/' . ltrim($kop['cap_gambar'], '/')) : '';
$logoKiri = !empty($kop['logo_kiri']) ? url('public/' . ltrim($kop['logo_kiri'], '/')) : (defined('SYS_APP_LOGO') && SYS_APP_LOGO ? url(ltrim(SYS_APP_LOGO, '/')) : '');
$logoKanan = !empty($kop['logo_kanan']) ? url('public/' . ltrim($kop['logo_kanan'], '/')) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan &amp; TTD Surat — Portal BIP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4 text-slate-800">
    <div class="max-w-3xl mx-auto space-y-5">
        <?php if ($notFound): ?>
            <div class="bg-white rounded-3xl p-8 text-center shadow-lg border border-slate-200 mt-10">
                <div class="w-16 h-16 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-3xl mb-4">⚠️</div>
                <h1 class="text-xl font-extrabold text-slate-900">Link Surat Tidak Ditemukan</h1>
                <p class="text-sm text-slate-500 mt-2">Token persetujuan surat tidak valid atau surat sudah dihapus.</p>
            </div>

        <?php elseif ($isApproved): ?>
            <div class="bg-white rounded-3xl p-8 sm:p-10 text-center shadow-xl border border-emerald-200 mt-10">
                <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-3xl mb-4">✅</div>
                <h1 class="text-2xl font-extrabold text-slate-900">Surat Sudah Di-Approve</h1>
                <p class="text-sm text-slate-600 mt-2 max-w-md mx-auto">
                    Surat ini telah disetujui dan ditandatangani oleh Kepala Sekolah. Demi keamanan, link pratinjau dan tanda tangan ini sudah ditutup dan tidak dapat dilihat kembali.
                </p>
                <div class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800">
                    <span>Nomor: <?= htmlspecialchars($surat['nomor_surat'] ?? '-') ?></span>
                    <?php if (!empty($surat['approved_at'])): ?>
                        <span>&bull; <?= date('d/m/Y H:i', strtotime($surat['approved_at'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Header Info -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $isRejected ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $isRejected ? 'Ditolak — Menunggu Perbaikan / Approve Ulang' : 'Menunggu Persetujuan & TTD Kepala Sekolah' ?>
                    </span>
                    <h1 class="text-lg font-extrabold text-slate-900 mt-1">Persetujuan &amp; Tanda Tangan Surat Keluar</h1>
                    <p class="text-xs text-slate-500">Unit <?= htmlspecialchars($surat['unit_nama']) ?> &bull; Nomor: <strong class="font-mono"><?= htmlspecialchars($surat['nomor_surat']) ?></strong></p>
                </div>
            </div>

            <?php if (!empty($errorMsg)): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-sm font-semibold text-rose-700">
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <!-- Pratinjau Tampilan Surat -->
            <div class="bg-white rounded-2xl p-6 sm:p-10 shadow-md border border-slate-200" style="font-family: 'Times New Roman', Times, serif;">
                <?php if ($kopGambar): ?>
                    <div class="mb-5 pb-3 border-b-2 border-slate-800">
                        <img src="<?= htmlspecialchars($kopGambar) ?>" alt="KOP Surat" class="w-full max-h-40 object-contain mx-auto">
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-4 border-b-4 border-double border-slate-900 pb-3 mb-5">
                        <?php if ($logoKiri): ?><img src="<?= htmlspecialchars($logoKiri) ?>" alt="Logo" class="w-16 h-16 object-contain shrink-0"><?php endif; ?>
                        <div class="flex-1 text-center">
                            <div class="text-sm font-bold uppercase"><?= htmlspecialchars($kop['baris_1'] ?? '') ?></div>
                            <div class="text-lg font-extrabold uppercase"><?= htmlspecialchars($kop['baris_2'] ?? '') ?></div>
                            <?php if (!empty($kop['sub_judul'])): ?><div class="text-xs italic"><?= htmlspecialchars($kop['sub_judul']) ?></div><?php endif; ?>
                            <?php if (!empty($kop['alamat'])): ?><div class="text-xs text-slate-600"><?= htmlspecialchars($kop['alamat']) ?></div><?php endif; ?>
                            <?php if (!empty($kop['kontak'])): ?><div class="text-xs text-slate-600"><?= htmlspecialchars($kop['kontak']) ?></div><?php endif; ?>
                        </div>
                        <?php if ($logoKanan): ?><img src="<?= htmlspecialchars($logoKanan) ?>" alt="Logo" class="w-16 h-16 object-contain shrink-0"><?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col sm:flex-row sm:justify-between gap-2 text-sm mb-4">
                    <table>
                        <tr><td class="pr-2">Nomor</td><td class="pr-2">:</td><td><strong><?= htmlspecialchars($surat['nomor_surat']) ?></strong></td></tr>
                        <tr><td class="pr-2">Lampiran</td><td class="pr-2">:</td><td><?= htmlspecialchars($surat['lampiran_ket'] ?: '-') ?></td></tr>
                        <tr><td class="pr-2">Perihal</td><td class="pr-2">:</td><td><strong><?= htmlspecialchars($surat['perihal']) ?></strong></td></tr>
                    </table>
                    <div class="text-sm">Palu, <?= htmlspecialchars($tglIndo) ?></div>
                </div>

                <?php if (!empty($surat['tujuan'])): ?>
                    <div class="text-sm mb-4">
                        Kepada Yth.<br>
                        <strong><?= htmlspecialchars($surat['tujuan']) ?></strong><br>
                        di Tempat
                    </div>
                <?php endif; ?>

                <div class="text-sm whitespace-pre-line text-justify leading-relaxed mb-8 min-h-[140px]"><?= htmlspecialchars($surat['isi_ringkas'] ?: "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\nDengan hormat, bersama surat ini kami sampaikan hal sebagaimana tercantum pada perihal di atas.\n\nDemikian surat ini kami sampaikan, atas perhatian dan kerja samanya kami ucapkan terima kasih.\n\nWassalamu'alaikum Warahmatullahi Wabarakatuh.") ?></div>

                <div class="flex justify-end">
                    <div class="w-64 text-sm text-left">
                        <div><?= htmlspecialchars($surat['approver_jabatan'] ?: ($kop['penandatangan_jabatan'] ?: 'Kepala Sekolah ' . $surat['unit_nama'])) ?></div>
                        <div class="relative h-24 my-1 flex items-center justify-center border border-dashed border-slate-300 rounded-xl bg-slate-50/70">
                            <?php if ($capGambar): ?>
                                <img src="<?= htmlspecialchars($capGambar) ?>" alt="Cap Unit" class="h-20 object-contain opacity-80 pointer-events-none">
                            <?php endif; ?>
                            <span class="text-[11px] font-sans text-slate-400">Preview TTD &amp; Cap</span>
                        </div>
                        <div class="font-bold underline">
                            <?= htmlspecialchars($surat['approver_nama'] ?: ($surat['penandatangan'] ?: ($kop['penandatangan_nama'] ?: 'Kepala Sekolah'))) ?>
                        </div>
                        <?php if (!empty($surat['approver_niy']) || !empty($kop['penandatangan_niy'])): ?>
                            <div class="text-xs">NIY. <?= htmlspecialchars($surat['approver_niy'] ?: $kop['penandatangan_niy']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form Tanda Tangan Langsung & Approve -->
            <form method="POST" action="<?= url('persuratan/approve/' . urlencode($surat['approval_token'])) ?>" onsubmit="return submitTtdForm()" class="bg-white rounded-2xl p-5 sm:p-6 shadow-md border border-slate-200 space-y-4">
                <input type="hidden" name="ttd_data" id="ttd_data" value="">
                <input type="hidden" name="action" id="approval_action" value="approve">

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Tanda Tangan Digital Kepala Sekolah</h2>
                        <p class="text-xs text-slate-500">Goreskan tanda tangan di kotak di bawah ini. Cap unit akan otomatis dibubuhkan saat disetujui.</p>
                    </div>
                    <button type="button" onclick="clearSignaturePad()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 cursor-pointer">
                        Ulangi TTD
                    </button>
                </div>

                <div class="relative border-2 border-dashed border-slate-300 rounded-2xl bg-slate-50 overflow-hidden touch-none">
                    <canvas id="signatureCanvas" class="w-full h-52 block cursor-crosshair"></canvas>
                    <?php if ($capGambar): ?>
                        <div class="absolute bottom-2 right-2 flex items-center gap-1.5 bg-white/90 px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] font-bold text-emerald-700 pointer-events-none">
                            <img src="<?= htmlspecialchars($capGambar) ?>" alt="Cap" class="w-5 h-5 object-contain">
                            <span>Cap Unit Siap</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                    <input type="text" name="approval_note" placeholder="Catatan singkat jika diperlukan..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:border-primary-500">
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="submit" onclick="document.getElementById('approval_action').value='reject'"
                            class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-sm font-bold cursor-pointer">
                        Tolak Surat
                    </button>
                    <button type="submit" onclick="document.getElementById('approval_action').value='approve'"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-sm cursor-pointer">
                        Setujui &amp; Tanda Tangani Surat
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        const canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let hasDrawn = false;

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            ctx.lineWidth = 2.6;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0f172a';
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: clientX - rect.left, y: clientY - rect.top };
        }

        function startDraw(e) {
            e.preventDefault();
            drawing = true;
            hasDrawn = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }
        function moveDraw(e) {
            if (!drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }
        function endDraw() { drawing = false; }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', moveDraw);
        window.addEventListener('mouseup', endDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', moveDraw, { passive: false });
        canvas.addEventListener('touchend', endDraw);

        window.clearSignaturePad = function() {
            const rect = canvas.getBoundingClientRect();
            ctx.clearRect(0, 0, rect.width, rect.height);
            hasDrawn = false;
            document.getElementById('ttd_data').value = '';
        };

        window.submitTtdForm = function() {
            const act = document.getElementById('approval_action').value;
            if (act === 'reject') return confirm('Yakin menolak surat ini?');
            if (!hasDrawn) {
                alert('Silakan bubuhkan tanda tangan digital pada kotak terlebih dahulu.');
                return false;
            }
            document.getElementById('ttd_data').value = canvas.toDataURL('image/png');
            return true;
        };
    })();
    </script>
</body>
</html>
