<?php
/**
 * View Publik Formulir Observasi Awal yang Diisi oleh Orang Tua / Wali Calon Siswa
 * Modul SPMB - SIT Bina Insan Palu
 *
 * @var array  $pendaftar
 * @var array  $structure
 * @var array  $detail
 * @var bool   $sudahMengisi
 * @var string $token
 */

$namaAnak     = htmlspecialchars($pendaftar['nama_lengkap'] ?? '');
$nomorDaft    = htmlspecialchars($pendaftar['nomor_pendaftaran'] ?? '');
$jenjang      = htmlspecialchars($pendaftar['jenjang'] ?? 'SD');
$namaUnit     = SpmbModel::getNamaSekolahByJenjang($jenjang);
$respon       = $detail['respon'] ?? null;
$answersSaved = $detail['answers'] ?? [];

// Petakan jawaban yang tersimpan berdasarkan pertanyaan_id
$savedMap = [];
if (!empty($answersSaved)) {
    foreach ($answersSaved as $ans) {
        $savedMap[$ans['pertanyaan_id']] = [
            'pilihan' => $ans['pilihan_jawaban'],
            'skor'    => $ans['skor']
        ];
    }
}

// Hitung total pertanyaan
$totalQuestions = 0;
foreach ($structure as $g) {
    $totalQuestions += count($g['pertanyaan'] ?? []);
}

$isEditMode = isset($_GET['edit']) && $_GET['edit'] === '1';
$isSuccessPage = isset($_GET['sukses']) && $_GET['sukses'] === '1';

$sbLogo = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuesioner Observasi Awal - <?= $namaAnak ?> | SIT Bina Insan Palu</title>
    
    <!-- Favicon -->
    <?= function_exists('renderFaviconTags') ? renderFaviconTags() : '' ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col selection:bg-sky-500 selection:text-white">

    <!-- Top Navigation / Navbar -->
    <header class="bg-gradient-to-r from-sky-900 via-sky-800 to-blue-900 text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <?php if ($sbLogo): ?>
                    <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center shadow-md shrink-0">
                        <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                    </div>
                <?php else: ?>
                    <div class="w-10 h-10 rounded-xl bg-sky-600 flex items-center justify-center shadow-md font-bold text-lg shrink-0">
                        BIP
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-sm sm:text-base font-bold tracking-tight leading-snug">SIT BINA INSAN PALU</h1>
                    <p class="text-[11px] text-sky-200 font-medium">Kuesioner Observasi Awal Murid Baru</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-500/30 text-sky-100 border border-sky-400/40">
                    Unit: <?= $jenjang ?>
                </span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 py-6 sm:py-8 space-y-6">

        <!-- Card Informasi Calon Siswa -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-40 h-40 bg-sky-50 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white flex items-center justify-center shadow-lg shadow-sky-500/20 shrink-0 font-extrabold text-xl">
                        <?= mb_substr($namaAnak, 0, 1) ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                Calon Murid Baru
                            </span>
                            <span class="text-xs text-slate-400">&bull;</span>
                            <span class="text-xs font-mono font-bold text-slate-600">No. <?= $nomorDaft ?></span>
                        </div>
                        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-900 mt-1 tracking-tight">
                            <?= $namaAnak ?>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                            Unit Pendaftaran: <strong class="text-slate-800"><?= $namaUnit ?> (<?= $jenjang ?>)</strong>
                        </p>
                    </div>
                </div>

                <?php if ($sudahMengisi && !$isEditMode): ?>
                    <div class="sm:text-right shrink-0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Sudah Diisi Lengkap
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($sudahMengisi && !$isEditMode): ?>
            <!-- ================================================================= -->
            <!-- TAMPILAN JIKA SUDAH MENGISI (SUMMARY RECEIPT) -->
            <!-- ================================================================= -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
                <!-- Status Banner -->
                <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white shadow-lg shadow-emerald-600/20 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Kuesioner Observasi Selesai
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold">Terima Kasih, Ayah/Bunda!</h3>
                        <p class="text-xs sm:text-sm text-emerald-100 max-w-xl">
                            Data observasi awal ananda telah tersimpan dengan aman pada sistem SPMB SIT Bina Insan Palu. Informasi ini menjadi acuan penting bagi para asatidz dan pendidik dalam pendampingan belajar ananda.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center min-w-[140px] shrink-0">
                        <span class="block text-xs text-emerald-100 font-semibold uppercase tracking-wider">Status Pengisian</span>
                        <span class="block text-base font-extrabold text-white mt-0.5">Tersimpan</span>
                        <span class="block text-[11px] text-emerald-200 mt-1">Formulir Selesai</span>
                    </div>
                </div>

                <!-- Rincian Responden -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-400 font-medium block">Nama Pengisi Formulir</span>
                        <span class="font-bold text-slate-800 text-sm mt-0.5 block"><?= htmlspecialchars($respon['nama_pengisi'] ?? '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block">Hubungan Keluarga</span>
                        <span class="font-bold text-slate-800 text-sm mt-0.5 block"><?= htmlspecialchars($respon['hubungan_pengisi'] ?? 'Orang Tua') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium block">Waktu Submit</span>
                        <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                            <?= !empty($respon['submitted_at']) ? date('d F Y, H:i', strtotime($respon['submitted_at'])) . ' WITA' : '-' ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($respon['catatan_orang_tua'])): ?>
                    <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100 text-xs sm:text-sm text-slate-700">
                        <span class="font-bold text-sky-900 block mb-1">Catatan Tambahan dari Orang Tua/Wali:</span>
                        <p class="italic text-slate-600">"<?= nl2br(htmlspecialchars($respon['catatan_orang_tua'])) ?>"</p>
                    </div>
                <?php endif; ?>

                <!-- Tombol Aksi -->
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-slate-400 text-center sm:text-left">
                        Perlu memperbarui jawaban? Anda dapat mengubah kuesioner observasi ini kembali.
                    </p>
                    <a href="<?= url('spmb/observasi/' . $token . '?edit=1') ?>" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Ubah Jawaban Observasi
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- ================================================================= -->
            <!-- FORMULIR PENGISIAN OBSERVASI AWAL -->
            <!-- ================================================================= -->
            <form id="form-observasi-public" action="<?= url('spmb/observasi/' . $token . '/submit') ?>" method="POST" class="space-y-6">
                
                <!-- Petunjuk Pengisian -->
                <div class="bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-50 border border-sky-200/80 p-5 sm:p-6 rounded-3xl">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shrink-0 mt-0.5 shadow-md shadow-sky-600/20">
                            i
                        </div>
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-1">
                            <h4 class="font-bold text-slate-900 text-sm sm:text-base">Petunjuk Pengisian bagi Orang Tua / Wali:</h4>
                            <p>1. Kuesioner ini bertujuan untuk memetakan karakter, kebiasaan, serta kemandirian ananda secara objektif.</p>
                            <p>2. Mohon pilih jawaban yang paling menggambarkan kondisi ananda sehari-hari di rumah.</p>
                            <p>3. Tidak ada jawaban yang salah. Kejujuran pengisian sangat membantu kami menyiapkan program pendampingan yang tepat.</p>
                        </div>
                    </div>
                </div>

                <!-- Bagian 1: Identitas Pengisi Form -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-xl bg-sky-100 text-sky-700 font-extrabold text-xs flex items-center justify-center">
                            1
                        </span>
                        <h3 class="text-base font-bold text-slate-900">Identitas Pengisi Kuesioner</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <!-- Nama Pengisi -->
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Lengkap Pengisi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_pengisi" 
                                   id="nama_pengisi" 
                                   required 
                                   value="<?= htmlspecialchars($respon['nama_pengisi'] ?? ($pendaftar['nama_ayah'] ?: ($pendaftar['nama_ibu'] ?: ''))) ?>" 
                                   placeholder="Contoh: Ahmad Fauzi, S.T." 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all font-semibold">
                        </div>

                        <!-- Hubungan Pengisi -->
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Hubungan Keluarga <span class="text-rose-500">*</span>
                            </label>
                            <?php $currHub = $respon['hubungan_pengisi'] ?? 'Ayah Kandung'; ?>
                            <select name="hubungan_pengisi" id="hubungan_pengisi" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-sky-500 bg-white">
                                <option value="Ayah Kandung" <?= $currHub === 'Ayah Kandung' ? 'selected' : '' ?>>Ayah Kandung</option>
                                <option value="Ibu Kandung" <?= $currHub === 'Ibu Kandung' ? 'selected' : '' ?>>Ibu Kandung</option>
                                <option value="Wali / Keluarga" <?= $currHub === 'Wali / Keluarga' ? 'selected' : '' ?>>Wali / Keluarga</option>
                            </select>
                        </div>

                        <!-- No WA Pengisi -->
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" 
                                   name="no_wa_pengisi" 
                                   id="no_wa_pengisi" 
                                   required 
                                   value="<?= htmlspecialchars($respon['no_wa_pengisi'] ?? ($pendaftar['no_wa'] ?: ($pendaftar['no_hp'] ?: ''))) ?>" 
                                   placeholder="Contoh: 081234567890" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all font-mono font-semibold">
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Butir Pertanyaan Terkelompok per Group -->
                <?php if (empty($structure)): ?>
                    <div class="bg-white rounded-3xl p-10 border border-slate-200 text-center text-slate-500 space-y-2">
                        <p class="font-bold text-slate-800">Instrumen Observasi Belum Tersedia</p>
                        <p class="text-xs">Mohon hubungi panitia SPMB SIT Bina Insan Palu untuk informasi lebih lanjut.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $globalQuestionNumber = 1;
                    foreach ($structure as $gIndex => $grp): 
                        $questions = $grp['pertanyaan'] ?? [];
                    ?>
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-5">
                            <!-- Group Title -->
                            <div class="flex items-start gap-3 pb-3 border-b border-slate-100">
                                <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <?= $gIndex + 1 ?>
                                </span>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                                        <?= htmlspecialchars($grp['nama_group']) ?>
                                    </h3>
                                    <?php if (!empty($grp['deskripsi'])): ?>
                                        <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($grp['deskripsi']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- List Pertanyaan di Group ini -->
                            <div class="space-y-6 pt-1">
                                <?php foreach ($questions as $q): ?>
                                    <?php 
                                        $qId = (int)$q['id'];
                                        $opsiArr = !empty($q['opsi_parsed']) ? $q['opsi_parsed'] : [];
                                        if (empty($opsiArr) && !empty($q['opsi_jawaban'])) {
                                            $decoded = json_decode($q['opsi_jawaban'], true);
                                            if (is_array($decoded)) $opsiArr = $decoded;
                                        }

                                        $savedAnswer = $savedMap[$qId]['pilihan'] ?? null;
                                    ?>
                                    <div class="question-block p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-slate-50 transition-all space-y-3" data-qid="<?= $qId ?>">
                                        <div class="flex items-start gap-3">
                                            <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-800 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                                <?= $globalQuestionNumber++ ?>
                                            </span>
                                            <div class="flex-1">
                                                <p class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">
                                                    <?= htmlspecialchars($q['pertanyaan']) ?>
                                                </p>
                                                <?php if (!empty($q['indikator'])): ?>
                                                    <p class="text-[11px] text-slate-500 mt-1">
                                                        <span class="font-medium text-slate-400">Petunjuk:</span> <?= htmlspecialchars($q['indikator']) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Pilihan Jawaban (Radio Button Grid) -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 pt-1">
                                            <?php foreach ($opsiArr as $opIndex => $op): ?>
                                                <?php 
                                                    $val = (string)($op['skor'] ?? $opIndex);
                                                    $label = htmlspecialchars($op['label'] ?? '');
                                                    $isSelected = ($savedAnswer !== null && ($savedAnswer === $op['label'] || (string)($savedMap[$qId]['skor'] ?? '') === (string)$op['skor']));
                                                ?>
                                                <label class="option-label relative flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all text-xs font-semibold select-none
                                                              <?= $isSelected ? 'bg-sky-50 border-sky-500 text-sky-900 shadow-xs ring-1 ring-sky-500' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-100/50' ?>">
                                                    <input type="radio" 
                                                           name="answers[<?= $qId ?>]" 
                                                           value="<?= $val ?>" 
                                                           required 
                                                           <?= $isSelected ? 'checked' : '' ?>
                                                           class="sr-only answer-radio" 
                                                           onchange="onAnswerSelected(this)">
                                                    <span class="radio-indicator w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition-colors
                                                                 <?= $isSelected ? 'border-sky-600 bg-sky-600' : 'border-slate-300 bg-white' ?>">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-white <?= $isSelected ? '' : 'hidden' ?>"></span>
                                                    </span>
                                                    <span class="flex-1 leading-snug"><?= $label ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Bagian 3: Catatan Tambahan Orang Tua -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-3">
                    <h4 class="text-sm font-bold text-slate-900">Catatan / Keterangan Khusus dari Orang Tua (Opsional)</h4>
                    <p class="text-xs text-slate-500">
                        Apakah ananda memiliki alergi, riwayat kesehatan khusus, bakat menonjol, atau hal penting lain yang perlu diketahui oleh guru?
                    </p>
                    <textarea name="catatan_orang_tua" 
                              id="catatan_orang_tua" 
                              rows="3" 
                              placeholder="Tuliskan catatan khusus di sini..." 
                              class="w-full px-4 py-3 rounded-2xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all font-medium"><?= htmlspecialchars($respon['catatan_orang_tua'] ?? '') ?></textarea>
                </div>

                <!-- Sticky Bottom Action Bar -->
                <div class="sticky bottom-4 z-30 bg-slate-900/90 backdrop-blur-md text-white p-4 sm:p-5 rounded-3xl shadow-2xl border border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center font-bold text-sm shrink-0">
                            <span id="answered-count">0</span>/<span id="total-count"><?= $totalQuestions ?></span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-300 block">Progres Pengisian Soal</span>
                            <span id="answered-status" class="text-xs font-bold text-white">Memeriksa kelengkapan...</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <button type="submit" 
                                id="btn-submit-observasi" 
                                class="w-full sm:w-auto px-7 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition-all">
                            Simpan & Kirim Kuesioner Observasi
                        </button>
                    </div>
                </div>

            </form>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="py-6 border-t border-slate-200 bg-white text-center text-xs text-slate-400">
        <p>&copy; <?= date('Y') ?> SIT Bina Insan Palu. Sistem Penerimaan Murid Baru (SPMB).</p>
    </footer>

    <!-- Script Form Interactivity -->
    <script>
    function onAnswerSelected(radio) {
        const questionBlock = radio.closest('.question-block');
        if (!questionBlock) return;

        // Reset semua option label di dalam block ini
        const labels = questionBlock.querySelectorAll('.option-label');
        labels.forEach(lbl => {
            lbl.classList.remove('bg-sky-50', 'border-sky-500', 'text-sky-900', 'shadow-xs', 'ring-1', 'ring-sky-500');
            lbl.classList.add('bg-white', 'border-slate-200', 'text-slate-700');
            
            const indicator = lbl.querySelector('.radio-indicator');
            if (indicator) {
                indicator.classList.remove('border-sky-600', 'bg-sky-600');
                indicator.classList.add('border-slate-300', 'bg-white');
                const dot = indicator.querySelector('span');
                if (dot) dot.classList.add('hidden');
            }
        });

        // Set style label terpilih
        const activeLabel = radio.closest('.option-label');
        if (activeLabel) {
            activeLabel.classList.remove('bg-white', 'border-slate-200', 'text-slate-700');
            activeLabel.classList.add('bg-sky-50', 'border-sky-500', 'text-sky-900', 'shadow-xs', 'ring-1', 'ring-sky-500');

            const indicator = activeLabel.querySelector('.radio-indicator');
            if (indicator) {
                indicator.classList.remove('border-slate-300', 'bg-white');
                indicator.classList.add('border-sky-600', 'bg-sky-600');
                const dot = indicator.querySelector('span');
                if (dot) dot.classList.remove('hidden');
            }
        }

        updateProgress();
    }

    function updateProgress() {
        const total = <?= $totalQuestions ?>;
        const form = document.getElementById('form-observasi-public');
        if (!form) return;

        const checkedRadios = form.querySelectorAll('.answer-radio:checked');
        const count = checkedRadios.length;

        const countEl = document.getElementById('answered-count');
        const statusEl = document.getElementById('answered-status');

        if (countEl) countEl.innerText = count;
        if (statusEl) {
            if (count >= total) {
                statusEl.innerHTML = '<span class="text-emerald-400">✓ Semua pertanyaan telah dijawab!</span>';
            } else {
                statusEl.innerText = `${total - count} butir belum dijawab`;
            }
        }
    }

    // Inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        updateProgress();

        const form = document.getElementById('form-observasi-public');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const total = <?= $totalQuestions ?>;
                const checked = form.querySelectorAll('.answer-radio:checked').length;
                const namaPengisi = document.getElementById('nama_pengisi').value.trim();

                if (!namaPengisi) {
                    Swal.fire('Mohon Lengkapi', 'Silakan isi nama lengkap Ayah/Ibu/Wali pengisi kuesioner.', 'warning');
                    return;
                }

                if (checked < total) {
                    Swal.fire({
                        title: 'Belum Lengkap',
                        html: `Masih ada <strong>${total - checked} butir pertanyaan</strong> yang belum dijawab.<br>Mohon periksa dan jawab semua butir kuesioner sebelum mengirim.`,
                        icon: 'warning'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Kirim Kuesioner Observasi?',
                    text: 'Pastikan data yang Anda isi sudah sesuai dengan kondisi ananda sehari-hari.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Kirim Sekarang',
                    cancelButtonText: 'Periksa Kembali',
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#64748b'
                }).then((res) => {
                    if (res.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
