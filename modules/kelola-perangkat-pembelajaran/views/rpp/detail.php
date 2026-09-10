<?php
/**
 * Detail In-App Preview - RPP / Modul Ajar (JSIT Format)
 */
$konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
$groupId = $konten['rpp_group_id'] ?? 0;
$kktpRows = $konten['kktp_rows'] ?? [];
?>
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header & Actions -->
    <div class="space-y-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Dokumen RPP & Modul Ajar (JSIT)
                </span>
                <?php
                $statusBadges = [
                    'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
                    'diajukan' => 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse',
                    'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200'
                ];
                $statusLabels = [
                    'draft' => 'Draft',
                    'diajukan' => 'Menunggu Verifikasi',
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Perlu Revisi'
                ];
                $st = $item['status'] ?? 'draft';
                ?>
                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border <?= $statusBadges[$st] ?? $statusBadges['draft'] ?>">
                    <?= $statusLabels[$st] ?? ucfirst($st) ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">
                <?= e($item['judul']) ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Penyusun: <strong><?= e($item['guru_nama'] ?: 'Guru Mapel') ?></strong> • NIP: <?= e($item['guru_nip'] ?: '-') ?>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap justify-end">
            <a href="<?= ($groupId > 0) ? url("kelola-perangkat-pembelajaran/rpp/group/{$groupId}") : url('kelola-perangkat-pembelajaran/rpp') ?>" class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                &larr; Kembali
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/rpp/edit/{$item['id']}") ?>" class="px-4 py-2 rounded-2xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs border border-amber-200 transition-colors">
                ✏️ Edit RPP
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/rpp/cetak/{$item['id']}") ?>" target="_blank" class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all">
                🖨️ Cetak Portrait A4
            </a>
        </div>
    </div>

    <!-- PREVIEW DOKUMEN JSIT -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 space-y-8 text-slate-800">
        
        <!-- KOP DOKUMEN (dari Pengaturan Sistem) -->
        <?php
        $namaSekolah = $unitProfile['nama_lembaga'] ?? 'SD ISLAM TERPADU BINA INSAN PALU';
        $logoSekolah = !empty($unitProfile['logo_url']) ? url($unitProfile['logo_url']) : url('public/assets/images/logo.png');
        ?>
        <div class="text-center pb-6 border-b-2 border-slate-800">
            <div class="flex items-center justify-center gap-4 mb-2">
                <img src="<?= $logoSekolah ?>" alt="Logo Sekolah" class="h-16 w-auto object-contain" onerror="this.style.display='none'">
                <div class="text-left">
                    <h2 class="text-base font-black tracking-wider uppercase text-slate-900"><?= e($namaSekolah) ?></h2>
                    <p class="text-xs text-slate-500">Jaringan Sekolah Islam Terpadu (JSIT) Indonesia</p>
                </div>
            </div>
            <h3 class="text-sm sm:text-base font-black uppercase tracking-wide text-slate-900 mt-2">
                RENCANA PELAKSANAAN PEMBELAJARAN
            </h3>
            <p class="text-xs font-bold text-slate-600">
                Tahun Ajaran <?= e($item['nama_tahun'] ?? '2025/2026') ?> <?= e($item['semester'] ?? '') ?>
            </p>
        </div>

        <!-- IDENTITAS TABEL -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-xs bg-slate-50/70 p-4 rounded-2xl border border-slate-200">
            <div class="flex justify-between py-1 border-b border-slate-200/60">
                <span class="font-bold text-slate-600">Nama Guru Mapel:</span>
                <span class="font-bold text-slate-900"><?= e($item['guru_nama'] ?: '-') ?></span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
                <span class="font-bold text-slate-600">Model Pembelajaran:</span>
                <span class="font-bold text-slate-900"><?= e($konten['model_pembelajaran'] ?? 'Problem Based Learning') ?></span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
                <span class="font-bold text-slate-600">Mata Pelajaran:</span>
                <span class="font-bold text-slate-900"><?= e($item['mata_pelajaran']) ?></span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
                <span class="font-bold text-slate-600">Alokasi Waktu:</span>
                <span class="font-bold text-slate-900"><?= e($item['alokasi_waktu'] ?: '2 x 35 Menit') ?></span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
                <span class="font-bold text-slate-600">Semester / Fase:</span>
                <span class="font-bold text-slate-900"><?= e($item['semester']) ?> / Fase <?= e($item['fase'] ?: 'B') ?></span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
                <span class="font-bold text-slate-600">Waktu Pelaksanaan:</span>
                <span class="font-bold text-slate-900"><?= e($konten['waktu_pelaksanaan'] ?? '-') ?></span>
            </div>
            <div class="flex justify-between py-1 sm:col-span-2">
                <span class="font-bold text-slate-600">Kelas / Rombel:</span>
                <span class="font-bold text-slate-900"><?= e($item['tingkat_kelas']) ?></span>
            </div>
        </div>

        <!-- CAPAIAN PEMBELAJARAN (CP) -->
        <div class="space-y-2">
            <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                Capaian Pembelajaran (CP)
            </div>
            <div class="p-4 rounded-2xl bg-amber-50/30 border border-amber-200 text-xs leading-relaxed text-slate-800">
                <?= nl2br(e($konten['cp_text'] ?? ($konten['cp'] ?? 'Belum ada uraian CP.'))) ?>
            </div>
        </div>

        <!-- TUJUAN PEMBELAJARAN (3 RANAH) -->
        <div class="space-y-2">
            <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                Tujuan Pembelajaran (3 Ranah)
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50">
                    <div class="font-bold text-amber-900 text-xs uppercase mb-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> 1. Attitude / Sikap
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed"><?= nl2br(e($konten['tp_attitude'] ?? '-')) ?></p>
                </div>
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50">
                    <div class="font-bold text-blue-900 text-xs uppercase mb-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> 2. Skill / Keterampilan
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed"><?= nl2br(e($konten['tp_skill'] ?? '-')) ?></p>
                </div>
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50">
                    <div class="font-bold text-emerald-900 text-xs uppercase mb-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 3. Knowledge / Pengetahuan
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed"><?= nl2br(e($konten['tp_knowledge'] ?? ($konten['tujuan_pembelajaran'] ?? '-'))) ?></p>
                </div>
            </div>
        </div>

        <!-- KATA KUNCI & PERTANYAAN PEMANTIK -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                    Kata Kunci / Konten
                </div>
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-xs text-slate-800">
                    <?= e($konten['kata_kunci'] ?? '-') ?>
                </div>
            </div>
            <div class="space-y-2">
                <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                    Pertanyaan Pemantik
                </div>
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-xs text-slate-800">
                    <?= nl2br(e($konten['pertanyaan_pemantik'] ?? '-')) ?>
                </div>
            </div>
        </div>

        <!-- PENGETAHUAN PENDUKUNG & ASESMEN DIAGNOSIS KOGNITIF -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                    Pengetahuan Pendukung
                </div>
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-xs text-slate-800">
                    <?= nl2br(e($konten['pengetahuan_pendukung'] ?? '-')) ?>
                </div>
            </div>
            <div class="space-y-2">
                <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                    Asesmen Diagnosis Kognitif
                </div>
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-xs text-slate-800">
                    <?= nl2br(e($konten['asesmen_diagnosis'] ?? ($konten['asesmen_diagnostik'] ?? '-'))) ?>
                </div>
            </div>
        </div>

        <!-- KKTP TABLE -->
        <div class="space-y-2">
            <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse border border-slate-200 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-100 font-bold text-slate-700">
                        <tr>
                            <th class="py-2.5 px-3 w-12 text-center border border-slate-200">No</th>
                            <th class="py-2.5 px-3 border border-slate-200">KKTP / Indikator</th>
                            <th class="py-2.5 px-3 w-64 border border-slate-200">Alokasi Waktu / Pertemuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($kktpRows)): ?>
                            <tr>
                                <td colspan="3" class="py-3 px-3 text-center text-slate-400">Belum ada baris KKTP.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kktpRows as $idx => $kr): ?>
                                <tr>
                                    <td class="py-2.5 px-3 text-center border border-slate-200 text-slate-500"><?= $idx + 1 ?></td>
                                    <td class="py-2.5 px-3 border border-slate-200 font-medium"><?= e($kr['indikator'] ?? ($kr['kktp'] ?? '-')) ?></td>
                                    <td class="py-2.5 px-3 border border-slate-200 font-semibold text-slate-700"><?= e($kr['waktu'] ?? ($kr['pekan'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MEDIA, SARANA, METODE & SUMBER BELAJAR -->
        <div class="space-y-2">
            <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                Media, Sarana, Metode & Sumber Belajar
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-500 uppercase text-[10px] block mb-1">Media:</span>
                    <span class="text-slate-800 font-medium"><?= nl2br(e($konten['media'] ?? '-')) ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-500 uppercase text-[10px] block mb-1">Sarana:</span>
                    <span class="text-slate-800 font-medium"><?= nl2br(e($konten['sarana'] ?? ($konten['sarana_prasarana'] ?? '-'))) ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-500 uppercase text-[10px] block mb-1">Metode:</span>
                    <span class="text-slate-800 font-medium"><?= nl2br(e($konten['metode'] ?? '-')) ?></span>
                </div>
                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-500 uppercase text-[10px] block mb-1">Sumber Belajar:</span>
                    <span class="text-slate-800 font-medium"><?= nl2br(e($konten['sumber_belajar'] ?? '-')) ?></span>
                </div>
            </div>
        </div>

        <!-- PELAKSANAAN PEMBELAJARAN PENDEKATAN TERPADU -->
        <div class="space-y-2">
            <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                Pelaksanaan Pembelajaran Pendekatan TERPADU (JSIT)
            </div>
            <div class="space-y-3 text-xs">
                <!-- Opener -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-teal-900 uppercase">1. Pembukaan (Opener)</span>
                        <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['opener_waktu'] ?? '10 Menit') ?></span>
                    </div>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['opener_kegiatan'] ?? ($konten['kegiatan_pendahuluan'] ?? '-'))) ?></p>
                </div>

                <!-- Telaah -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-teal-900 uppercase">2. Telaah</span>
                        <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['telaah_waktu'] ?? '20 Menit') ?></span>
                    </div>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['telaah_kegiatan'] ?? '-')) ?></p>
                </div>

                <!-- Eksplorasi -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-teal-900 uppercase">3. Eksplorasi</span>
                        <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['eksplorasi_waktu'] ?? '20 Menit') ?></span>
                    </div>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['eksplorasi_kegiatan'] ?? '-')) ?></p>
                </div>

                <!-- Rumuskan -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-teal-900 uppercase">4. Rumuskan</span>
                        <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['rumuskan_waktu'] ?? '20 Menit') ?></span>
                    </div>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['rumuskan_kegiatan'] ?? '-')) ?></p>
                </div>

                <!-- Presentasikan -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-teal-900 uppercase">5. Presentasikan</span>
                        <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['presentasikan_waktu'] ?? '20 Menit') ?></span>
                    </div>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['presentasikan_kegiatan'] ?? '-')) ?></p>
                </div>

                <!-- Aplikasikan -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-teal-900 uppercase">6. Aplikasikan</span>
                        <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['aplikasikan_waktu'] ?? '10 Menit') ?></span>
                    </div>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['aplikasikan_kegiatan'] ?? '-')) ?></p>
                </div>

                <!-- Kaitkan & Simpulkan -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-teal-900 uppercase">7. Kaitkan dan Simpulkan</span>
                        <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['kaitkan_waktu'] ?? '2 Menit') ?></span>
                    </div>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['kaitkan_kegiatan'] ?? '-')) ?></p>
                </div>

                <!-- Duniawi & Ukhrowi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-teal-900 uppercase">8. Duniawi</span>
                            <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['duniawi_waktu'] ?? '2 Menit') ?></span>
                        </div>
                        <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['duniawi_kegiatan'] ?? '-')) ?></p>
                    </div>
                    <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-teal-900 uppercase">9. Ukhrowi</span>
                            <span class="font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800"><?= e($konten['ukhrowi_waktu'] ?? '3 Menit') ?></span>
                        </div>
                        <p class="text-slate-700 leading-relaxed"><?= nl2br(e($konten['ukhrowi_kegiatan'] ?? '-')) ?></p>
                    </div>
                </div>

                <!-- Closure -->
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60">
                    <span class="font-bold text-teal-900 uppercase block mb-2">10. Closure / Penutup</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-slate-700">Refleksi</span>
                                <span class="text-[10px] font-bold text-slate-500"><?= e($konten['refleksi_waktu'] ?? '2 Menit') ?></span>
                            </div>
                            <p class="text-slate-600"><?= nl2br(e($konten['refleksi_kegiatan'] ?? '-')) ?></p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-slate-700">Kegiatan Penutup</span>
                                <span class="text-[10px] font-bold text-slate-500"><?= e($konten['penutup_waktu'] ?? '2 Menit') ?></span>
                            </div>
                            <p class="text-slate-600"><?= nl2br(e($konten['penutup_kegiatan'] ?? ($konten['kegiatan_penutup'] ?? '-'))) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PENILAIAN TERPADU -->
        <div class="space-y-2">
            <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                Penilaian Terpadu (AfL, AaL, AoL)
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse border border-slate-200 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-100 font-bold text-slate-700">
                        <tr>
                            <th class="py-2.5 px-3 border border-slate-200 w-28">Ranah</th>
                            <th class="py-2.5 px-3 border border-slate-200">Tujuan Pembelajaran</th>
                            <th class="py-2.5 px-3 border border-slate-200">Assessment for Learning (AfL)</th>
                            <th class="py-2.5 px-3 border border-slate-200">Assessment as Learning (AaL)</th>
                            <th class="py-2.5 px-3 border border-slate-200">Assessment of Learning (AoL)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-2.5 px-3 border border-slate-200 font-bold text-amber-900 bg-amber-50/40">Sikap / Attitude</td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_sikap_tp'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_sikap_afl'] ?? ($konten['asesmen_formatif'] ?? '-')) ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_sikap_aal'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_sikap_aol'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 border border-slate-200 font-bold text-blue-900 bg-blue-50/40">Keterampilan / Skill</td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_skill_tp'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_skill_afl'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_skill_aal'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_skill_aol'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 border border-slate-200 font-bold text-emerald-900 bg-emerald-50/40">Pengetahuan / Knowledge</td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_knowledge_tp'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_knowledge_afl'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_knowledge_aal'] ?? '-') ?></td>
                            <td class="py-2.5 px-3 border border-slate-200"><?= e($konten['penilaian_knowledge_aol'] ?? ($konten['asesmen_sumatif'] ?? '-')) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PENERAPAN INTROFLEX -->
        <div class="space-y-2">
            <div class="bg-yellow-300 text-slate-900 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider border border-yellow-400">
                Penerapan Framework INTROFLEX (JSIT)
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-700 block mb-1">1. Individualisasi</span>
                    <p class="text-slate-600 leading-relaxed"><?= nl2br(e($konten['introflex_individualisasi'] ?? '-')) ?></p>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-700 block mb-1">2. Interaksi</span>
                    <p class="text-slate-600 leading-relaxed"><?= nl2br(e($konten['introflex_interaksi'] ?? '-')) ?></p>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-700 block mb-1">3. Observasi</span>
                    <p class="text-slate-600 leading-relaxed"><?= nl2br(e($konten['introflex_observasi'] ?? '-')) ?></p>
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50">
                    <span class="font-bold text-slate-700 block mb-1">4. Refleksi</span>
                    <p class="text-slate-600 leading-relaxed"><?= nl2br(e($konten['introflex_refleksi'] ?? ($konten['refleksi_guru_siswa'] ?? '-'))) ?></p>
                </div>
            </div>
        </div>

        <!-- TANDA TANGAN PENGESAHAN (3 KOLOM) -->
        <?php
        // Kepala Sekolah dari Pengaturan Sistem
        $ksNama = $unitProfile['kepala_sekolah']['nama'] ?? $konten['kepala_sekolah_nama'] ?? 'Kepala Sekolah';
        $ksNip = $unitProfile['kepala_sekolah']['nip'] ?? $konten['kepala_sekolah_nip'] ?? '-';
        
        // Pemeriksa = siapa yang approve RPP ini, fallback ke user login sekarang
        $loggedInUser = (class_exists('Auth') && Auth::name() && Auth::name() !== 'Guest') ? Auth::name() : '';
        $rawPemeriksa = $konten['pemeriksa_nama'] ?? '';
        if ($rawPemeriksa === 'Tim Kurikulum SDIT Bina Insan' || $rawPemeriksa === 'Tim Kurikulum') {
            $rawPemeriksa = '';
        }
        $pemeriksaNama = !empty($approverName) ? $approverName : (!empty($rawPemeriksa) ? $rawPemeriksa : (!empty($loggedInUser) ? $loggedInUser : 'Belum Diperiksa'));
        $pemeriksaNip = $approverNip ?? $konten['pemeriksa_nip'] ?? '-';
        ?>
        <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200 text-center text-xs">
            <div>
                <p class="text-slate-600">Menyetujui,</p>
                <p class="font-bold text-slate-900 mt-0.5">Kepala Sekolah</p>
                <div class="h-20"></div>
                <p class="font-bold text-slate-900 underline"><?= e($ksNama) ?></p>
                <p class="text-slate-500 text-[10px]">NIP: <?= e($ksNip) ?></p>
            </div>
            <div>
                <p class="text-slate-600">Diperiksa oleh,</p>
                <p class="font-bold text-slate-900 mt-0.5">Pemeriksa RPP / Kurikulum</p>
                <div class="h-20"></div>
                <p class="font-bold text-slate-900 underline"><?= e($pemeriksaNama) ?></p>
                <p class="text-slate-500 text-[10px]">NIP: <?= e($pemeriksaNip) ?></p>
            </div>
            <div>
                <p class="text-slate-600">Palu, <?= date('d F Y', strtotime($item['created_at'])) ?></p>
                <p class="font-bold text-slate-900 mt-0.5">Guru Mata Pelajaran</p>
                <div class="h-20"></div>
                <p class="font-bold text-slate-900 underline"><?= e($item['guru_nama'] ?: 'Guru Mapel') ?></p>
                <p class="text-slate-500 text-[10px]">NIP: <?= e($item['guru_nip'] ?: '-') ?></p>
            </div>
        </div>
    </div>

    <!-- LOG STATUS & APPROVAL CARD -->
    <?php if (!empty($logs)): ?>
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> Riwayat Status & Catatan Verifikasi
            </h3>
            <div class="space-y-3">
                <?php foreach ($logs as $log): ?>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start justify-between gap-4 text-xs">
                        <div>
                            <span class="font-bold text-slate-800"><?= e($log['user_nama']) ?></span>
                            <span class="text-slate-500">mengubah status menjadi:</span>
                            <span class="font-bold text-teal-700 uppercase"><?= e($log['aksi'] ?? '-') ?></span>
                            <?php if (!empty($log['catatan'])): ?>
                                <p class="text-slate-600 mt-1 italic">"<?= e($log['catatan']) ?>"</p>
                            <?php endif; ?>
                        </div>
                        <span class="text-[11px] text-slate-400 whitespace-nowrap"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
