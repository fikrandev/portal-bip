<?php
/**
 * View Validasi Kartu Siswa (Public Endpoint)
 * Ditampilkan saat QR Code kartu siswa dipindai menggunakan smartphone
 * 
 * @var array $siswa
 * @var string $jenjang
 * @var string|null $fotoUrl
 */

$sekolahMap = [
    'paud' => 'PAUD IT Bina Insan',
    'sd'   => 'SD IT Bina Insan Palu',
    'smp'  => 'SMP IT Bina Insan Palu',
    'sma'  => 'SMA IT Bina Insan Palu'
];
$namaSekolah = $sekolahMap[$jenjang] ?? 'SIT Bina Insan Palu';

$npsnMap = [
    'sd'   => '69979223',
    'smp'  => '70031371',
    'sma'  => '70058217',
    'paud' => '70037291'
];
$npsnSekolah = $npsnMap[$jenjang] ?? '69979223';

$isActive = (bool)($siswa['is_active'] ?? 1);
$statusColor = $isActive ? 'bg-emerald-500 text-white shadow-emerald-500/30' : 'bg-rose-500 text-white shadow-rose-500/30';
$statusText = $isActive ? 'Siswa Aktif' : 'Tidak Aktif';
$statusIcon = $isActive 
    ? '<svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    : '<svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';

$isDapodik = strtolower(trim((string)($siswa['dapodik'] ?? ''))) === 'sudah';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Validasi Kartu Siswa - <?= e($siswa['nama_lengkap'] ?: $siswa['nama']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(226, 232, 240, 0.8);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-100 selection:text-indigo-900 pb-10">

    <!-- Top Gradient Header Accent -->
    <div class="fixed top-0 left-0 w-full h-72 bg-gradient-to-br from-indigo-700 via-blue-600 to-sky-500 -z-10 rounded-b-[48px] shadow-xl overflow-hidden">
        <div class="absolute inset-0 opacity-15">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0,0 C30,40 70,40 100,0 L100,100 L0,100 Z" fill="white" />
            </svg>
        </div>
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-sky-300/20 rounded-full blur-xl"></div>
    </div>

    <main class="flex-grow flex items-center justify-center p-4 pt-10 pb-8">
        <div class="glass-card w-full max-w-sm rounded-[32px] overflow-hidden relative pb-6 transition-all">
            
            <!-- School Brand Ribbon -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-5 py-3 text-white flex items-center justify-between border-b border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-sky-400 to-indigo-400 flex items-center justify-center text-slate-950 font-black text-xs shadow-sm">
                        BI
                    </div>
                    <div>
                        <div class="text-[11px] font-black tracking-wide uppercase leading-tight"><?= e($namaSekolah) ?></div>
                        <div class="text-[8.5px] font-medium text-slate-300 tracking-wider uppercase">SIT Bina Insan Palu</div>
                    </div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-white/15 text-sky-200 border border-white/10 tracking-wider">
                    E-CARD
                </span>
            </div>

            <!-- Header Section with Photo & Status -->
            <div class="text-center pt-6 pb-2 px-6 relative">
                <!-- Status Badge -->
                <div class="flex justify-end mb-2">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold shadow-sm tracking-wide <?= $statusColor ?>">
                        <?= $statusIcon ?>
                        <span><?= $statusText ?></span>
                    </div>
                </div>

                <!-- Foto Siswa -->
                <div class="w-32 h-32 mx-auto rounded-full p-1 bg-gradient-to-tr from-blue-500 to-indigo-500 shadow-xl relative z-10">
                    <div class="w-full h-full rounded-full overflow-hidden bg-slate-100 border-4 border-white flex items-center justify-center">
                        <?php if ($fotoUrl): ?>
                            <img src="<?= $fotoUrl ?>" alt="Foto Siswa" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-400">
                                <svg class="w-14 h-14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Verified Checkmark Badge on photo -->
                    <div class="absolute bottom-1 right-1 w-9 h-9 bg-blue-600 text-white rounded-full border-[3px] border-white flex items-center justify-center shadow-md shadow-blue-600/40">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </div>
                </div>
            </div>

            <!-- Biodata Section -->
            <div class="px-6 text-center mt-2">
                <h1 class="text-lg font-black text-slate-900 leading-snug tracking-tight mb-0.5">
                    <?= e($siswa['nama_lengkap'] ?: $siswa['nama']) ?>
                </h1>
                
                <div class="flex items-center justify-center gap-2 mb-4">
                    <span class="text-xs font-bold text-indigo-700 font-mono bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-100">
                        NISN: <?= e($siswa['nisn'] ?: '-') ?>
                    </span>
                    <?php if (!empty($siswa['nis'])): ?>
                        <span class="text-xs font-semibold text-slate-500 font-mono bg-slate-100 px-2 py-0.5 rounded-lg border border-slate-200">
                            NIS: <?= e($siswa['nis']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Academic Info Grid -->
                <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/70 text-left shadow-inner">
                    <div class="grid grid-cols-2 gap-y-3 gap-x-3">
                        <div>
                            <p class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Jenjang</p>
                            <p class="text-xs font-extrabold text-slate-800"><?= strtoupper($jenjang) ?></p>
                        </div>
                        <div>
                            <p class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Kelas / Rombel</p>
                            <p class="text-xs font-extrabold text-slate-800 truncate" title="<?= e($siswa['kelas'] ?: '-') ?>">
                                <?= e($siswa['kelas'] ?: '-') ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tahun Ajaran</p>
                            <p class="text-xs font-bold text-slate-700"><?= e($siswa['tahun_ajaran'] ?? '2026/2027') ?></p>
                        </div>
                        <div>
                            <p class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Semester</p>
                            <p class="text-xs font-bold text-slate-700"><?= e($siswa['semester'] ?? 'Ganjil') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Dapodik Synchronization Status Section (Prominent) -->
                <?php if ($isDapodik): ?>
                    <div class="mt-4 p-4 rounded-2xl bg-gradient-to-br from-emerald-50 via-teal-50/60 to-emerald-100/50 border border-emerald-200 text-left relative overflow-hidden shadow-sm">
                        <!-- Watermark background icon -->
                        <div class="absolute -right-3 -bottom-3 text-emerald-600/10 pointer-events-none">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                            </svg>
                        </div>
                        
                        <div class="flex items-start gap-3 relative z-10">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-emerald-600/25">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1.5 mb-0.5">
                                    <h2 class="text-xs font-black text-emerald-950 uppercase tracking-wide">
                                        Tersinkronisasi Dapodik
                                    </h2>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[8.5px] font-black bg-emerald-200 text-emerald-900 uppercase tracking-wider">
                                        Kemendikbud
                                    </span>
                                </div>
                                <p class="text-[11px] text-emerald-800 font-medium leading-snug">
                                    Data siswa telah terverifikasi dan tersinkronisasi langsung dengan sistem Data Pokok Pendidikan Nasional.
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-emerald-200/80 flex items-center justify-between text-[10px] text-emerald-900 font-bold relative z-10">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Status: Resmi & Valid
                            </span>
                            <span class="font-mono text-emerald-800 bg-emerald-100/80 px-2 py-0.5 rounded-md">
                                NPSN: <?= e($npsnSekolah) ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mt-4 p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200 text-left relative shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-extrabold text-amber-950 uppercase tracking-wide">Terdaftar di Portal BIP</span>
                                <p class="text-[11px] text-amber-800 font-medium leading-snug mt-0.5">
                                    Data resmi internal sekolah (Menunggu jadwal sinkronisasi Dapodik).
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Official Digital Verification Seal (Pengganti TTD Fisik) -->
                <div class="mt-5 pt-4 border-t border-slate-200/70 flex flex-col items-center text-center">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-700 mb-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>DOKUMEN IDENTITAS DIGITAL SAH</span>
                    </div>
                    <p class="text-[10.5px] text-slate-500 font-medium leading-tight">
                        Dipindai resmi melalui Portal Sistem Informasi SIT Bina Insan Palu
                    </p>
                    <p class="text-[9.5px] text-slate-400 font-mono mt-1 font-semibold">
                        Kode Validasi: BIP-<?= strtoupper(substr(md5(($siswa['id'] ?? '') . ($siswa['nisn'] ?? '') . 'BIP'), 0, 10)) ?>
                    </p>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer Copyright -->
    <footer class="text-center mt-auto">
        <p class="text-[10px] font-semibold tracking-widest uppercase text-slate-400">
            &copy; <?= date('Y') ?> &bull; <span class="text-indigo-600 font-bold">Divisi IT Bina Insan Palu</span>
        </p>
    </footer>

</body>
</html>
