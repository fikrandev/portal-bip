<?php
/**
 * Prota - Detail View
 * Menampilkan Matriks Program Tahunan Persis Sesuai Format Kurikulum Merdeka
 * Kolom: NO | TP | ATP | JML JP | SMT
 */
$protaRows = $konten['prota_rows'] ?? [];

// Fallback jika data lama format materi_list
if (empty($protaRows) && !empty($konten['materi_list'])) {
    $legacyList = $konten['materi_list'];
    $rNo = 1;
    foreach ($legacyList as $l) {
        $jp1 = (int)($l['jp_smt1'] ?? 0);
        $jp2 = (int)($l['jp_smt2'] ?? 0);
        $smt = ($jp2 > 0 && $jp1 == 0) ? 2 : 1;
        $atpJp = $smt == 1 ? ($jp1 ?: 2) : ($jp2 ?: 2);

        $protaRows[] = [
            'no' => $rNo++,
            'tp' => $l['cp_kd'] ?: $l['materi_pokok'],
            'atp_list' => [
                ['atp' => $l['materi_pokok'], 'jp' => $atpJp]
            ],
            'penilaian_harian' => [
                'label' => 'Penilaian Harian',
                'jp' => 4
            ],
            'semester' => $smt
        ];
    }
}

// Pisahkan per semester untuk merge vertikal kolom SMT
$rowsSmt1 = [];
$rowsSmt2 = [];
foreach ($protaRows as $r) {
    if ((int)($r['semester'] ?? 1) === 2) {
        $rowsSmt2[] = $r;
    } else {
        $rowsSmt1[] = $r;
    }
}

$jpSmt1 = (int)($konten['total_jp_smt1'] ?? 0);
$jpSmt2 = (int)($konten['total_jp_smt2'] ?? 0);
$jpTotal = (int)($konten['total_jp_tahun'] ?? ($jpSmt1 + $jpSmt2));

$targetGroupId = $konten['prota_group_id'] ?? null;

// Profil Sekolah & Kepala Sekolah untuk Kop Prosem
$unitKey = $item['unit'] ?? 'SD';
if (!isset($unitProfile) || empty($unitProfile)) {
    $unitProfile = \PerangkatController::getUnitProfile($unitKey);
}
?>
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="space-y-2">
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                Dokumen Kurikulum Merdeka
            </span>
            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                Unit <?= e($item['unit']) ?>
            </span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1">
            <?= e($item['judul']) ?>
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">
            Penyusun: <strong><?= e($item['guru_nama'] ?: 'Guru Pengampu') ?></strong> &bull; NIP: <?= e($item['guru_nip'] ?: '-') ?>
        </p>

        <!-- 3 Tombol Aksi di Bawah Tulisan Judul & Penyusun (Sebelah Kanan Ujung) -->
        <div class="flex items-center justify-end gap-2.5 pt-1 flex-wrap">
            <a href="<?= $targetGroupId ? url("kelola-perangkat-pembelajaran/prota/group/{$targetGroupId}") : url('kelola-perangkat-pembelajaran/prota') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <span>&larr;</span> Kembali ke Wadah
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/prota/edit/{$item['id']}") ?>" class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-xl text-xs border border-amber-200 transition-colors inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                <span>Edit Prota</span>
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/prota/cetak/{$item['id']}") ?>" target="_blank" class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                <span>Cetak Landscape A4</span>
            </a>
        </div>
    </div>

    <!-- Informasi Identitas Dokumen -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Mata Pelajaran</span>
                <span class="font-bold text-slate-800 text-sm"><?= e($item['mata_pelajaran']) ?></span>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Kelas / Tingkat</span>
                <span class="font-bold text-slate-800 text-sm"><?= e($item['tingkat_kelas']) ?> <?= !empty($item['fase']) ? "(Fase {$item['fase']})" : '' ?></span>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Tahun Ajaran</span>
                <span class="font-bold text-slate-800 text-sm"><?= e($item['nama_tahun'] ?? '2026/2027') ?></span>
            </div>
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] block">Total Alokasi Waktu</span>
                <span class="font-black text-indigo-600 text-sm"><?= $jpTotal ?> JP</span>
                <span class="text-[10px] text-slate-400 block">(Smt 1: <?= $jpSmt1 ?> JP | Smt 2: <?= $jpSmt2 ?> JP)</span>
            </div>
        </div>
    </div>

    <!-- Tampilan Matriks Persis Gambar Referensi -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
        <!-- Header Box Kop Sesuai Format Resmi Prosem -->
        <div class="mb-5 overflow-hidden rounded-xl border border-slate-900">
            <table class="w-full border-collapse text-xs" style="font-family: 'Times New Roman', Times, serif;">
                <tr>
                    <td class="w-[14%] bg-blue-100 p-3 text-center align-middle border-r border-slate-900">
                        <?php if (!empty($unitProfile['logo_url'])): ?>
                            <img src="<?= url(ltrim($unitProfile['logo_url'], '/')) ?>" class="max-h-14 max-w-full mx-auto object-contain" alt="Logo">
                        <?php else: ?>
                            <span class="text-2xl font-black text-sky-600">BIP</span>
                        <?php endif; ?>
                    </td>
                    <td class="w-[38%] bg-blue-200 p-3 text-center align-middle border-r border-slate-900">
                        <div class="text-[11px] font-bold uppercase text-slate-900">Mata Pelajaran</div>
                        <div class="text-sm font-black uppercase text-slate-900 mt-0.5"><?= e($item['mata_pelajaran']) ?></div>
                    </td>
                    <td class="w-[48%] bg-blue-200 p-0 align-middle">
                        <table class="w-full border-collapse text-[11px] font-semibold text-slate-900">
                            <tr class="border-b border-slate-900">
                                <td class="py-1 px-3 w-28 whitespace-nowrap font-bold">Pengajar</td>
                                <td class="py-1 px-3 font-bold">: <?= e($item['guru_nama'] ?: 'Guru Pengampu') ?></td>
                            </tr>
                            <tr class="border-b border-slate-900">
                                <td class="py-1 px-3 w-28 whitespace-nowrap font-bold">Kelas / Fase</td>
                                <td class="py-1 px-3 font-bold">: <?= e($item['tingkat_kelas']) ?> <?= !empty($item['fase']) ? '(' . e($item['fase']) . ')' : '' ?></td>
                            </tr>
                            <tr class="border-b border-slate-900">
                                <td class="py-1 px-3 w-28 whitespace-nowrap font-bold">Semester</td>
                                <td class="py-1 px-3 font-bold">: 1 (Ganjil) & 2 (Genap)</td>
                            </tr>
                            <tr>
                                <td class="py-1 px-3 w-28 whitespace-nowrap font-bold">Tahun Ajaran</td>
                                <td class="py-1 px-3 font-bold">: <?= e(trim(preg_replace('/\s*(Ganjil|Genap)/i', '', $item['nama_tahun'] ?? '2026/2027'))) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="mb-4 text-center">
            <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">
                PROGRAM TAHUNAN (PROTA)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-slate-800 text-xs text-slate-900">
                <!-- Header Berwarna Lembut Sesuai Gambar (Soft Cyan) -->
                <thead>
                    <tr class="bg-[#d7f3f6] text-slate-900 font-bold text-center border-b border-slate-800 uppercase text-[11px]">
                        <th class="border border-slate-800 py-2.5 px-3 w-12">NO</th>
                        <th class="border border-slate-800 py-2.5 px-4 w-5/12">TP</th>
                        <th class="border border-slate-800 py-2.5 px-4">ATP</th>
                        <th class="border border-slate-800 py-2.5 px-2 w-20">JML<br>JP</th>
                        <th class="border border-slate-800 py-2.5 px-2 w-16">SMT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Helper function to render a semester block
                    $renderSemesterRows = function($rows, $smtNumber) {
                        if (empty($rows)) return;
                        
                        // Hitung total baris HTML untuk rowspan kolom SMT
                        $totalHtmlRows = 0;
                        foreach ($rows as $tp) {
                            $atpCount = max(1, count($tp['atp_list'] ?? []));
                            $totalHtmlRows += $atpCount;
                            if (!empty($tp['penilaian_harian'])) {
                                $totalHtmlRows += 1;
                            }
                        }

                        $isFirstRowOfSemester = true;

                        foreach ($rows as $rIdx => $tp) {
                            $atpList = $tp['atp_list'] ?? [];
                            if (empty($atpList)) {
                                $atpList = [['atp' => '-', 'jp' => 0]];
                            }
                            $atpCount = count($atpList);
                            $ph = $tp['penilaian_harian'] ?? null;
                            $tpRowspan = $atpCount; // TP spans over its ATP rows

                            foreach ($atpList as $aIdx => $atp) {
                                echo '<tr class="align-top">';
                                
                                // Render NO and TP on the first ATP row of this TP
                                if ($aIdx === 0) {
                                    echo '<td class="border border-slate-800 py-2 px-2 text-center font-semibold align-middle" rowspan="' . $tpRowspan . '">' . e($tp['no'] ?? ($rIdx + 1)) . '</td>';
                                    echo '<td class="border border-slate-800 py-2 px-3 align-middle leading-relaxed" rowspan="' . $tpRowspan . '">' . nl2br(e($tp['tp'])) . '</td>';
                                }

                                // Render ATP
                                echo '<td class="border border-slate-800 py-2 px-3 leading-relaxed">' . e($atp['atp']) . '</td>';
                                echo '<td class="border border-slate-800 py-2 px-2 text-center font-bold align-middle">' . (int)$atp['jp'] . '</td>';

                                // Render SMT vertically merged on the very first row of this semester
                                if ($isFirstRowOfSemester) {
                                    echo '<td class="border border-slate-800 py-2 px-2 text-center font-black text-sm align-middle bg-slate-50/50" rowspan="' . $totalHtmlRows . '">' . $smtNumber . '</td>';
                                    $isFirstRowOfSemester = false;
                                }

                                echo '</tr>';
                            }

                            // Render Penilaian Harian Row (spanning TP & ATP)
                            if ($ph && !empty($ph['label'])) {
                                echo '<tr class="bg-slate-50/30">';
                                echo '<td class="border border-slate-800 py-1.5 px-3 text-center font-bold" colspan="2">' . e($ph['label']) . '</td>';
                                echo '<td class="border border-slate-800 py-1.5 px-2 text-center font-bold">' . (int)($ph['jp'] ?? 4) . '</td>';
                                echo '</tr>';
                            }
                        }
                    };

                    // 1. Render Semester 1 (Ganjil)
                    $renderSemesterRows($rowsSmt1, 1);

                    // 2. Render Semester 2 (Genap)
                    $renderSemesterRows($rowsSmt2, 2);
                    ?>

                    <!-- Summary Row Total JP -->
                    <tr class="bg-[#d7f3f6] font-bold border-t-2 border-slate-800">
                        <td colspan="3" class="border border-slate-800 py-2.5 px-4 text-right uppercase tracking-wider">
                            Total Alokasi Waktu Semester 1 (Ganjil)
                        </td>
                        <td class="border border-slate-800 py-2.5 px-2 text-center font-black">
                            <?= $jpSmt1 ?>
                        </td>
                        <td class="border border-slate-800 py-2.5 px-2 text-center">JP</td>
                    </tr>
                    <tr class="bg-[#d7f3f6] font-bold">
                        <td colspan="3" class="border border-slate-800 py-2.5 px-4 text-right uppercase tracking-wider">
                            Total Alokasi Waktu Semester 2 (Genap)
                        </td>
                        <td class="border border-slate-800 py-2.5 px-2 text-center font-black">
                            <?= $jpSmt2 ?>
                        </td>
                        <td class="border border-slate-800 py-2.5 px-2 text-center">JP</td>
                    </tr>
                    <tr class="bg-indigo-50 font-black text-indigo-950 border-t-2 border-slate-800">
                        <td colspan="3" class="border border-slate-800 py-3 px-4 text-right uppercase tracking-wider text-xs">
                            TOTAL ALOKASI WAKTU 1 TAHUN AJARAN
                        </td>
                        <td class="border border-slate-800 py-3 px-2 text-center text-sm">
                            <?= $jpTotal ?>
                        </td>
                        <td class="border border-slate-800 py-3 px-2 text-center">JP</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
