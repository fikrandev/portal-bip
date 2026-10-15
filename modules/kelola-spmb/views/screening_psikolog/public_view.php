<?php
/**
 * Halaman Publik Hasil Screening Psikolog Calon Murid Baru
 * SIT Bina Insan Palu
 * Dapat diakses langsung oleh orang tua tanpa perlu login melalui link aman berbasis token.
 */

$token = htmlspecialchars($pendaftar['biodata_token'] ?? '');
$downloadUrl = url('spmb/psikolog/' . $token . '/download');
$rawViewUrl  = url('spmb/psikolog/' . $token . '/raw');

$namaSiswa = htmlspecialchars($pendaftar['nama_lengkap'] ?? '-');
$nomorDaftar = htmlspecialchars($pendaftar['nomor_pendaftaran'] ?? '-');
$jenjang = htmlspecialchars($pendaftar['jenjang'] ?? 'SD');
$namaUnit = htmlspecialchars(SpmbModel::getNamaSekolahByJenjang($jenjang));
$tahunAjaran = htmlspecialchars($pendaftar['group_tahun_ajaran'] ?? ($pendaftar['nama_group'] ?? '2026/2027'));

$statusRekom = htmlspecialchars($pendaftar['status_rekomendasi'] ?? 'Direkomendasikan');
$catatan = htmlspecialchars($pendaftar['catatan_psikolog'] ?? '');
$namaPsikolog = htmlspecialchars($pendaftar['nama_psikolog'] ?? 'Tim Psikolog Mitra SIT BIP');
$tglScreening = !empty($pendaftar['tanggal_screening']) ? date('d F Y', strtotime($pendaftar['tanggal_screening'])) : '-';

// Badge styling
$badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
$iconColor  = 'text-emerald-600';
if ($statusRekom === 'Dipertimbangkan') {
    $badgeColor = 'bg-amber-50 text-amber-700 border-amber-200';
    $iconColor  = 'text-amber-600';
} elseif ($statusRekom === 'Tidak Direkomendasikan') {
    $badgeColor = 'bg-rose-50 text-rose-700 border-rose-200';
    $iconColor  = 'text-rose-600';
}

$logoUrl = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
} elseif (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $logoUrl = url(ltrim(SYS_APP_FAVICON, '/'));
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Hasil Screening Psikolog - SIT Bina Insan Palu') ?></title>
    
    <!-- Favicon -->
    <?= function_exists('renderFaviconTags') ? renderFaviconTags() : '' ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS 4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            --color-primary-50: #f0f9ff;
            --color-primary-100: #e0f2fe;
            --color-primary-500: #0ea5e9;
            --color-primary-600: #0284c7;
            --color-primary-700: #0369a1;
        }
    </style>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col antialiased selection:bg-sky-500 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-30 border-b border-slate-200 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php if ($logoUrl): ?>
                <img src="<?= $logoUrl ?>" alt="Logo" class="w-9 h-9 object-contain rounded-lg">
                <?php else: ?>
                <div class="w-9 h-9 rounded-xl bg-sky-600 text-white font-extrabold flex items-center justify-center text-sm shadow-sm">
                    BIP
                </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">SIT BINA INSAN PALU</h1>
                    <p class="text-[10px] text-slate-500 font-medium">Sistem Penerimaan Murid Baru (SPMB)</p>
                </div>
            </div>
            
            <a href="<?= $downloadUrl ?>" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-sky-600/20 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh PDF</span>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

        <!-- Welcome Banner -->
        <div class="bg-gradient-to-br from-sky-600 via-sky-700 to-indigo-800 rounded-3xl p-5 sm:p-7 text-white shadow-xl shadow-sky-900/10 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-[11px] font-semibold tracking-wide uppercase border border-white/20">
                    <span>📑 Dokumen Hasil Seleksi Resmi</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">Hasil Screening / Tes Psikolog Masuk Sekolah</h2>
                <p class="text-xs sm:text-sm text-sky-100 max-w-2xl leading-relaxed">
                    Dokumen resmi hasil evaluasi dan rekomendasi psikolog calon murid baru <?= $namaUnit ?> Tahun Ajaran <?= $tahunAjaran ?>.
                </p>
            </div>
        </div>

        <!-- Student Details Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-100 to-indigo-100 text-sky-700 flex items-center justify-center font-extrabold text-base border border-sky-200 shrink-0">
                        <?= strtoupper(substr($namaSiswa, 0, 2)) ?>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 leading-tight"><?= $namaSiswa ?></h3>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                            <span class="font-mono font-semibold text-slate-700">No. <?= $nomorDaftar ?></span>
                            <span>•</span>
                            <span><?= $namaUnit ?></span>
                            <span>•</span>
                            <span class="text-emerald-600 font-bold">Lulus / Diterima</span>
                        </div>
                    </div>
                </div>

                <!-- Recommendation Badge -->
                <div class="self-start sm:self-auto inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl border text-xs font-bold <?= $badgeColor ?>">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span><?= $statusRekom ?></span>
                </div>
            </div>

            <!-- Meta Information Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider mb-0.5">Tanggal Screening</span>
                    <strong class="text-slate-800 text-xs sm:text-sm"><?= $tglScreening ?></strong>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider mb-0.5">Nama Psikolog / Lembaga</span>
                    <strong class="text-slate-800 text-xs sm:text-sm"><?= $namaPsikolog ?></strong>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider mb-0.5">Format Dokumen</span>
                    <strong class="text-slate-800 text-xs sm:text-sm">Dokumen Asli (PDF)</strong>
                </div>
            </div>

            <?php if (!empty($catatan)): ?>
            <div class="p-4 bg-sky-50/60 rounded-2xl border border-sky-100 text-xs space-y-1">
                <span class="font-bold text-sky-900 text-[11px] block">📝 Catatan / Rekomendasi Psikolog:</span>
                <p class="text-sky-800 leading-relaxed"><?= nl2br($catatan) ?></p>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 pt-1">
                <a href="<?= $downloadUrl ?>" 
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-600/20 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Unduh Berkas Resmi (PDF)</span>
                </a>

                <a href="<?= $rawViewUrl ?>" target="_blank"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    <span>Buka Dokumen di Tab Baru ↗</span>
                </a>
            </div>
        </div>

        <!-- Document Viewer Section -->
        <div class="bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-slate-200/80 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900">Pratinjau Berkas PDF</h4>
                </div>
                <span class="text-[11px] text-slate-400">Gunakan pinch/zoom untuk memperbesar</span>
            </div>

            <div class="w-full bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                <iframe src="<?= $rawViewUrl ?>#toolbar=1" 
                        class="w-full h-[600px] sm:h-[750px] border-0" 
                        title="Pratinjau Berkas Hasil Psikolog">
                    <p class="p-6 text-center text-xs text-slate-500">
                        Browser Anda tidak mendukung preview langsung. Silakan 
                        <a href="<?= $downloadUrl ?>" class="text-sky-600 font-bold underline">klik di sini untuk mengunduh PDF</a>.
                    </p>
                </iframe>
            </div>
        </div>

        <!-- Help & Contact Box -->
        <div class="p-4 sm:p-5 bg-emerald-50 rounded-3xl border border-emerald-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
                    💬
                </div>
                <div>
                    <h5 class="text-xs sm:text-sm font-extrabold text-emerald-950">Ada Pertanyaan Seputar Hasil Screening?</h5>
                    <p class="text-[11px] text-emerald-800">Sekretariat SPMB SIT Bina Insan Palu siap melayani konsultasi Bapak/Ibu.</p>
                </div>
            </div>
            <a href="https://wa.me/6281145501111?text=<?= urlencode("Assalamu'alaikum, saya orang tua dari ananda " . $pendaftar['nama_lengkap'] . " (" . $pendaftar['nomor_pendaftaran'] . ") ingin bertanya seputar hasil screening psikolog.") ?>" 
               target="_blank"
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 active:scale-95 transition-all flex items-center gap-1.5 shrink-0">
                <span>Hubungi Panitia WA</span>
                <span>↗</span>
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-8 py-6 text-center text-xs text-slate-400">
        <div class="max-w-4xl mx-auto px-4 space-y-1">
            <p class="font-bold text-slate-600">&copy; <?= date('Y') ?> SIT Bina Insan Palu. Hak cipta dilindungi undang-undang.</p>
            <p class="text-[11px]">Jl. Bina Insan, Kel. Mamboro, Kec. Palu Utara, Kota Palu, Sulawesi Tengah</p>
        </div>
    </footer>

</body>
</html>
