<?php
/**
 * View Cetak Kartu Siswa (Support Single & Massal)
 * 
 * @var array $siswaList (Array of siswa data)
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page {
            size: 54mm 86mm portrait;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            gap: 20px;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        /* Card Container */
        .kartu-container {
            width: 54mm;
            height: 86mm;
            background-color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            flex-shrink: 0;
            page-break-after: always;
            page-break-inside: avoid;
        }
        
        .kartu-container:last-child {
            page-break-after: auto;
        }

        .kartu-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        /* Elements on top of background */
        .kartu-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .foto-container {
            margin-top: 28mm; 
            width: 24mm;
            height: 24mm;
            border-radius: 3mm;
            overflow: hidden;
            background-color: #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .foto-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-foto {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #94a3b8;
            font-size: 8px;
            text-align: center;
            padding: 4px;
        }

        .biodata-container {
            margin-top: 2mm;
            text-align: center;
            width: 90%;
        }

        .nama-siswa {
            font-size: 9pt; 
            font-weight: 800;
            color: #1e3a8a; 
            line-height: 1;
            text-transform: uppercase;
            margin-bottom: 0.5mm;
            letter-spacing: -0.2px; 
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .nisn-siswa {
            font-size: 7.5pt;
            font-weight: 500;
            color: #3b82f6;
            line-height: 1;
        }

        .qr-container {
            position: absolute;
            bottom: 4mm; 
            left: 50%;
            transform: translateX(-50%);
            width: 19mm;
            height: 19mm;
        }

        .qr-container img {
            width: 100%;
            height: 100%;
        }

        @media print {
            body {
                background-color: transparent;
                display: block;
                padding: 0;
                gap: 0;
            }
            .kartu-container {
                box-shadow: none;
                margin: 0;
            }
            @page {
                margin: 0;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="fixed top-4 right-4 print:hidden z-50 flex gap-2">
        <button onclick="window.close()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-bold shadow-md transition-colors">Tutup</button>
        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" /></svg>
            Print Kartu (<?= count($siswaList) ?>)
        </button>
    </div>

    <?php 
    $templateMissing = false;
    foreach ($siswaList as $siswa): 
        $jenjang = strtolower($siswa['jenjang'] ?? 'sd');
        if (!in_array($jenjang, ['paud', 'tk', 'sd', 'smp', 'sma'])) {
            $jenjang = 'sd';
        }
        if ($jenjang === 'tk') $jenjang = 'paud';

        $templatePath = BASE_PATH . '/public/uploads/templates/kartu/template_' . $jenjang . '.png';
        $templateUrl = file_exists($templatePath) ? asset('uploads/templates/kartu/template_' . $jenjang . '.png') : null;
        if (!$templateUrl) $templateMissing = true;

        $identifierFoto = !empty($siswa['nisn']) ? $siswa['nisn'] : $siswa['id_siswa'];
        $fotoPath = BASE_PATH . '/public/uploads/siswa/' . $identifierFoto . '.jpg';
        $fotoUrl = file_exists($fotoPath) ? asset('uploads/siswa/' . $identifierFoto . '.jpg') : null;
    ?>
    <div class="kartu-container">
        <!-- Background Template -->
        <?php if ($templateUrl): ?>
            <img src="<?= $templateUrl ?>?v=<?= time() ?>" alt="Template Background" class="kartu-bg">
        <?php else: ?>
            <div class="kartu-bg" style="background: linear-gradient(180deg, #dbeafe 0%, #ffffff 100%);">
                <div class="text-center pt-10 text-[10px] text-red-500 font-bold px-4">Template <?= strtoupper($jenjang) ?> Belum Diatur</div>
            </div>
        <?php endif; ?>

        <!-- Content Overlay -->
        <div class="kartu-content">
            
            <!-- Foto Profil -->
            <div class="foto-container">
                <?php if ($fotoUrl): ?>
                    <img src="<?= $fotoUrl ?>?v=<?= filemtime($fotoPath) ?>" alt="Foto <?= e($siswa['nama_lengkap']) ?>">
                <?php else: ?>
                    <div class="no-foto">
                        <svg class="w-8 h-8 mb-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <span>Belum Ada Foto</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nama & NISN -->
            <div class="biodata-container">
                <div class="nama-siswa"><?= e($siswa['nama_lengkap']) ?></div>
                <div class="nisn-siswa">(NISN : <?= e($siswa['nisn'] ?: '-') ?>)</div>
            </div>

            <!-- QR Code Container -->
            <?php 
                $identifier = !empty($siswa['nisn']) ? $siswa['nisn'] : $siswa['id_siswa'];
                $qrUrl = url('validasi-kartu/' . $identifier);
            ?>
            <div class="qr-container qrcode-box" data-qr="<?= e($qrUrl) ?>"></div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Error message if any template missing -->
    <?php if ($templateMissing): ?>
        <div class="fixed top-4 left-1/2 -translate-x-1/2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl max-w-sm w-full text-center text-sm shadow-lg print:hidden z-50">
            <strong>Ada Template Belum Diatur!</strong><br>
            Beberapa jenjang belum memiliki template. Silakan upload template melalui menu <i>Pengaturan Template Kartu</i> di Galeri Foto.
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Generate QR Codes for all cards
            const qrBoxes = document.querySelectorAll('.qrcode-box');
            qrBoxes.forEach(box => {
                const qrData = box.getAttribute('data-qr');
                new QRCode(box, {
                    text: qrData,
                    width: 128,
                    height: 128,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.M
                });
            });

            // Automatically open print dialog after a slight delay to ensure QR is rendered
            setTimeout(() => {
                <?php if (!$templateMissing): ?>
                window.print();
                <?php endif; ?>
            }, 800);
        });
    </script>
</body>
</html>
