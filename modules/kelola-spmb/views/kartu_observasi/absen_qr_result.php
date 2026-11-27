<?php
/**
 * Halaman Hasil Scan QR Langsung via Kamera Bawaan HP (Panitia/Admin Login)
 * Portal BIP - SPMB
 */
$ok = !empty($result['success']);
$already = !empty($result['already']);
$wrongSession = !empty($result['wrong_session']);
$d = $result['data'] ?? null;
$borderCls = $ok ? ($already ? 'border-amber-300' : 'border-emerald-300') : ($wrongSession ? 'border-amber-400' : 'border-rose-300');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Absen QR Observasi — Portal BIP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes qrCircleDraw { 0% { stroke-dashoffset: 166; } 100% { stroke-dashoffset: 0; } }
        @keyframes qrCheckDraw { 0% { stroke-dashoffset: 48; } 100% { stroke-dashoffset: 0; } }
        .qr-check-circle { stroke-dasharray: 166; stroke-dashoffset: 166; animation: qrCircleDraw 0.45s ease-out forwards; }
        .qr-check-tick { stroke-dasharray: 48; stroke-dashoffset: 48; animation: qrCheckDraw 0.3s 0.25s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border-2 <?= $borderCls ?> text-center space-y-4">
        <?php if ($wrongSession && $d): ?>
            <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 mx-auto flex items-center justify-center text-3xl">⚠️</div>
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800">
                    BUKAN SESINYA — PERIKSA JADWAL!
                </span>
                <h1 class="text-xl font-extrabold text-slate-900 mt-2"><?= htmlspecialchars($d['nama_lengkap']) ?></h1>
                <p class="text-xs font-mono text-slate-500 mt-0.5"><?= htmlspecialchars($d['nomor_peserta']) ?> &bull; Jenjang <?= htmlspecialchars($d['jenjang']) ?></p>
            </div>

            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-left text-xs font-semibold text-amber-900">
                <?= htmlspecialchars($result['message'] ?? 'Peserta bukan pada jadwal sesinya.') ?>
            </div>

            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-left text-xs space-y-1.5">
                <div class="flex justify-between"><span class="text-slate-500">No. Urut Sesi:</span><strong class="text-indigo-700"><?= !empty($d['urutan_sesi']) ? '#' . str_pad((string)(int)$d['urutan_sesi'], 2, '0', STR_PAD_LEFT) : '-' ?></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Ruangan Terdaftar:</span><strong class="text-slate-900"><?= htmlspecialchars($d['nama_ruangan'] ?: 'Belum Diatur') ?></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Tanggal Sesi:</span><strong class="text-slate-900"><?= !empty($d['tgl_sesi']) ? date('d/m/Y', strtotime($d['tgl_sesi'])) : '-' ?></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Sesi &amp; Jam:</span><strong class="text-indigo-700"><?= !empty($d['sesi_nama']) ? htmlspecialchars($d['sesi_nama'] . ' (' . $d['jam_mulai'] . '-' . $d['jam_selesai'] . ')') : '-' ?></strong></div>
            </div>

            <div class="pt-1 flex flex-col gap-2">
                <a href="<?= url('kelola-spmb/kartu-observasi/absen-qr/' . urlencode($d['qr_token']) . '?force=1') ?>"
                   onclick="return confirm('Tetap catat kehadiran peserta ini di luar jadwal sesinya?')"
                   class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold">
                    Tetap Catat Hadir (Override Panitia)
                </a>
                <a href="<?= url('kelola-spmb/kartu-observasi') ?>" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">
                    Kembali ke Daftar Absen
                </a>
            </div>

        <?php elseif ($ok && $d): ?>
            <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center <?= $already ? 'bg-amber-50' : 'bg-emerald-50' ?>">
                <svg class="w-16 h-16" viewBox="0 0 52 52">
                    <circle class="qr-check-circle" cx="26" cy="26" r="24" fill="none" stroke="<?= $already ? '#f59e0b' : '#10b981' ?>" stroke-width="4"/>
                    <path class="qr-check-tick" fill="none" stroke="<?= $already ? '#f59e0b' : '#10b981' ?>" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" d="M14 27l8 8 16-16"/>
                </svg>
            </div>
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold <?= $already ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                    <?= $already ? 'SUDAH ABSEN SEBELUMNYA' : 'ABSEN QR BERHASIL — HADIR' ?>
                </span>
                <h1 class="text-xl font-extrabold text-slate-900 mt-2"><?= htmlspecialchars($d['nama_lengkap']) ?></h1>
                <p class="text-xs font-mono text-slate-500 mt-0.5"><?= htmlspecialchars($d['nomor_peserta']) ?> &bull; Jenjang <?= htmlspecialchars($d['jenjang']) ?></p>
            </div>

            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-left text-xs space-y-1.5">
                <div class="flex justify-between"><span class="text-slate-500">No. Urut Sesi:</span><strong class="text-indigo-700"><?= !empty($d['urutan_sesi']) ? '#' . str_pad((string)(int)$d['urutan_sesi'], 2, '0', STR_PAD_LEFT) : '-' ?></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Ruangan:</span><strong class="text-slate-900"><?= htmlspecialchars($d['nama_ruangan'] ?: 'Belum Diatur') ?></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Sesi &amp; Jam:</span><strong class="text-indigo-700"><?= !empty($d['sesi_nama']) ? htmlspecialchars($d['sesi_nama'] . ' (' . $d['jam_mulai'] . '-' . $d['jam_selesai'] . ')') : '-' ?></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Waktu Absen:</span><strong class="text-emerald-700"><?= !empty($d['waktu_absen']) ? date('d/m/Y H:i:s', strtotime($d['waktu_absen'])) : '-' ?></strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Petugas:</span><strong class="text-slate-700"><?= htmlspecialchars($d['absen_oleh'] ?: '-') ?></strong></div>
            </div>

            <div class="pt-2 flex flex-col gap-2">
                <a href="<?= url('kelola-spmb/kartu-observasi') ?>" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">
                    Lihat Daftar Absen Observasi
                </a>
                <p class="text-[11px] text-slate-400">QR bersifat permanen (sekali cetak) & otomatis mengikuti perubahan sesi/ruangan terbaru.</p>
            </div>
        <?php else: ?>
            <div class="w-16 h-16 rounded-2xl bg-rose-100 text-rose-600 mx-auto flex items-center justify-center text-3xl">⚠️</div>
            <h1 class="text-lg font-extrabold text-slate-900">Absen QR Gagal</h1>
            <p class="text-sm text-rose-700"><?= htmlspecialchars($result['message'] ?? 'QR Code tidak valid.') ?></p>
            <a href="<?= url('kelola-spmb/kartu-observasi') ?>" class="block w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">
                Kembali ke Daftar Absen
            </a>
        <?php endif; ?>
    </div>
    <script>
    (function () {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = new Ctx();
            const playTone = (freq, offset, dur, vol, wave) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = wave;
                osc.frequency.setValueAtTime(freq, ctx.currentTime + offset);
                gain.gain.setValueAtTime(0.001, ctx.currentTime + offset);
                gain.gain.exponentialRampToValueAtTime(vol, ctx.currentTime + offset + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + offset + dur);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime + offset);
                osc.stop(ctx.currentTime + offset + dur + 0.02);
            };
            const mode = <?= json_encode($wrongSession ? 'warning' : ($ok ? ($already ? 'already' : 'success') : 'error')) ?>;
            if (mode === 'success') { playTone(1560, 0, 0.18, 0.45, 'sine'); playTone(2090, 0.06, 0.14, 0.25, 'triangle'); }
            else if (mode === 'already') { playTone(1040, 0, 0.10, 0.35, 'sine'); playTone(1040, 0.14, 0.10, 0.35, 'sine'); }
            else { playTone(340, 0, 0.16, 0.4, 'sawtooth'); playTone(240, 0.18, 0.22, 0.4, 'sawtooth'); }
        } catch (e) {}
    })();
    </script>
</body>
</html>
