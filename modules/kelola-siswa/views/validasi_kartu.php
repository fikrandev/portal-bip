<?php
/**
 * View Validasi Kartu Siswa (Public Endpoint)
 * 
 * @var array $siswa
 * @var string $jenjang
 * @var string|null $fotoUrl
 */

// Menentukan data Kepala Sekolah (Placeholder)
$kepsekMap = [
    'paud' => 'Kepala PAUD Bina Insan Palu',
    'sd'   => 'Kepala SD Bina Insan Palu',
    'smp'  => 'Kepala SMP Bina Insan Palu',
    'sma'  => 'Kepala SMA Bina Insan Palu'
];
$jabatanKepsek = $kepsekMap[$jenjang] ?? 'Kepala Sekolah';
$namaKepsek = ".........................................."; // Placeholder untuk diubah nanti

$isActive = (bool)($siswa['is_active'] ?? 1);
$statusColor = $isActive ? 'bg-emerald-500' : 'bg-red-500';
$statusText = $isActive ? 'Siswa Aktif' : 'Tidak Aktif';
$statusIcon = $isActive 
    ? '<svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    : '<svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Validasi Kartu - <?= e($siswa['nama_lengkap']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            /* Background pattern */
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-100 selection:text-indigo-900">

    <!-- Top Blue Wave Decor -->
    <div class="fixed top-0 left-0 w-full h-64 bg-gradient-to-br from-indigo-600 to-blue-500 -z-10 rounded-b-[40px] shadow-lg overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0,0 C30,40 70,40 100,0 L100,100 L0,100 Z" fill="white" />
            </svg>
        </div>
    </div>

    <main class="flex-grow flex items-center justify-center p-4 pt-12 pb-16">
        <div class="glass-card w-full max-w-sm rounded-3xl overflow-hidden relative pb-8">
            
            <!-- Header Section -->
            <div class="text-center pt-8 pb-4 px-6 relative">
                <!-- Status Badge -->
                <div class="absolute top-4 right-4 flex items-center px-3 py-1 rounded-full text-xs font-bold text-white shadow-sm <?= $statusColor ?>">
                    <?= $statusIcon ?>
                    <?= $statusText ?>
                </div>

                <!-- Foto Siswa -->
                <div class="w-32 h-32 mx-auto rounded-full p-1 bg-white shadow-xl relative z-10 -mt-2">
                    <div class="w-full h-full rounded-full overflow-hidden bg-slate-100 border-4 border-slate-50 flex items-center justify-center">
                        <?php if ($fotoUrl): ?>
                            <img src="<?= $fotoUrl ?>" alt="Foto" class="w-full h-full object-cover">
                        <?php else: ?>
                            <svg class="w-12 h-12 text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Verified Checkmark -->
                    <div class="absolute bottom-1 right-1 w-8 h-8 bg-blue-500 text-white rounded-full border-4 border-white flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </div>
                </div>
            </div>

            <!-- Biodata Section -->
            <div class="px-8 text-center mt-2">
                <h1 class="text-xl font-extrabold text-slate-800 leading-tight mb-1"><?= e($siswa['nama_lengkap']) ?></h1>
                <p class="text-sm font-semibold text-indigo-600 mb-6 tracking-wide">NISN : <?= e($siswa['nisn'] ?: '-') ?></p>

                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100 shadow-inner">
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-left">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Jenjang</p>
                            <p class="text-sm font-bold text-slate-700"><?= strtoupper($jenjang) ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Kelas</p>
                            <p class="text-sm font-bold text-slate-700"><?= e($siswa['kelas'] ?: '-') ?></p>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-slate-200/60 mt-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tervalidasi Sistem</p>
                            <p class="text-xs font-semibold text-slate-600 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Data Resmi Portal BIP
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="mt-8 px-8 flex justify-center">
                <div class="text-center w-full max-w-[200px]">
                    <p class="text-xs text-slate-500 mb-6">Palu, <?= date('d F Y') ?></p>
                    <p class="text-xs font-bold text-slate-700 leading-tight"><?= $jabatanKepsek ?></p>
                    
                    <!-- Area Tanda Tangan (Placeholder) -->
                    <div class="h-16 w-full flex items-end justify-center relative mt-2">
                        <!-- Jika ada gambar TTD, pasang di sini (sebagai img src) -->
                        <!-- <img src="..." class="absolute w-full h-full object-contain"> -->
                        <div class="border-b border-dashed border-slate-300 w-full mb-1"></div>
                    </div>
                    
                    <p class="text-[11px] font-bold text-slate-800 uppercase mt-1"><?= $namaKepsek ?></p>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="fixed bottom-0 left-0 w-full pb-4 pt-6 bg-gradient-to-t from-slate-100 to-transparent pointer-events-none">
        <div class="text-center">
            <p class="text-[10px] font-semibold tracking-widest uppercase text-slate-400">
                made by <span class="text-indigo-500 font-bold">Divisi IT Bina Insan palu</span>
            </p>
        </div>
    </footer>

</body>
</html>
