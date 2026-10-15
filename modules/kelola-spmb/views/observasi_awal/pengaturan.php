<?php
/**
 * View Pengaturan Instrumen Pertanyaan & Group Observasi Awal - Modul Kelola SPMB
 * Portal BIP
 *
 * @var array  $activeGroup
 * @var string $currentJenjang
 * @var array  $validJenjang
 * @var array  $groups
 * @var array  $questionsByGroup
 */

$csrfToken = CSRF::token();
?>

<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                    Konfigurasi Instrumen
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs font-medium text-slate-500">Unit: <strong class="text-slate-800"><?= htmlspecialchars($currentJenjang) ?></strong></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Pertanyaan Observasi Awal</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola group aspek observasi, butir instrumen pertanyaan, dan kustomisasi pilihan jawaban beserta bobot skor per unit.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('kelola-spmb/observasi-awal') ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Hasil Observasi
            </a>
            <button type="button" 
                    onclick="openModalGroup()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-sky-200 text-xs sm:text-sm font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Group</span>
            </button>
            <button type="button" 
                    onclick="openModalPertanyaan()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 text-white text-xs sm:text-sm font-semibold hover:from-sky-700 hover:to-blue-700 transition-all shadow-md shadow-sky-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Pertanyaan</span>
            </button>
        </div>
    </div>

    <!-- Tab Filter Jenjang / Unit -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-1 overflow-x-auto custom-scrollbar">
        <?php foreach ($validJenjang as $j): ?>
            <?php 
                $isActive = ($j === $currentJenjang); 
                $activeClass = $isActive 
                    ? 'bg-gradient-to-r from-sky-600 to-blue-600 text-white font-bold shadow-md shadow-sky-600/20' 
                    : 'text-slate-600 hover:bg-slate-100 font-semibold';
            ?>
            <a href="<?= url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $j) ?>" 
               class="px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap flex items-center gap-2 <?= $activeClass ?>">
                <span>Unit <?= htmlspecialchars($j) ?></span>
                <?php 
                    $countGrp = 0;
                    if ($j === $currentJenjang) {
                        $countGrp = count($groups);
                    }
                ?>
                <?php if ($isActive): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-extrabold"><?= $countGrp ?> Group</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Alert Informasi -->
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <div class="leading-relaxed">
            <strong class="font-semibold">Tips Konfigurasi:</strong> Pertanyaan observasi dipisahkan secara ketat berdasarkan <strong>Unit / Jenjang</strong> (PAUD, SD, SMP, SMA) agar instrumen perkembangan anak tepat sasaran. Pilihan jawaban dapat dikustomisasi bobot skornya (misal: <em>Sangat Sesuai: 4</em>, <em>Tidak Sesuai: 2</em>).
        </div>
    </div>

    <!-- Daftar Group & Pertanyaan -->
    <?php if (empty($groups)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Group Pertanyaan untuk Unit <?= htmlspecialchars($currentJenjang) ?></h3>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
                Silakan buat group pertanyaan (kategori aspek) terlebih dahulu sebelum menambahkan butir-butir pertanyaan observasi.
            </p>
            <button type="button" onclick="openModalGroup()" class="px-5 py-2.5 rounded-xl bg-sky-600 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 hover:bg-sky-700 transition-all">
                Buat Group Pertanyaan Sekarang
            </button>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($groups as $grp): ?>
                <?php 
                    $gId = (int)$grp['id'];
                    $qList = $questionsByGroup[$gId] ?? [];
                ?>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all hover:border-slate-300">
                    <!-- Group Header -->
                    <div class="p-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 border border-sky-200">
                                #<?= htmlspecialchars($grp['urutan']) ?>
                            </span>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900"><?= htmlspecialchars($grp['nama_group']) ?></h3>
                                    <?php if ((int)$grp['is_active'] === 1): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">AKTIF</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">NONAKTIF</span>
                                    <?php endif; ?>
                                    <span class="text-xs text-slate-400">&bull;</span>
                                    <span class="text-xs font-semibold text-slate-600"><?= count($qList) ?> Butir Pertanyaan</span>
                                </div>
                                <?php if (!empty($grp['deskripsi'])): ?>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-1"><?= htmlspecialchars($grp['deskripsi']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end md:self-auto shrink-0">
                            <button type="button" 
                                    onclick="openModalPertanyaan(null, <?= $gId ?>)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 text-xs font-semibold hover:bg-sky-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Tambah Soal</span>
                            </button>
                            <button type="button" 
                                    onclick="openModalGroup(<?= htmlspecialchars(json_encode($grp)) ?>)"
                                    class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    title="Edit Group">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button type="button" 
                                    onclick="confirmDeleteGroup(<?= $gId ?>, '<?= htmlspecialchars(addslashes($grp['nama_group'])) ?>')"
                                    class="p-2 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors"
                                    title="Hapus Group">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Pertanyaan Table / List -->
                    <?php if (empty($qList)): ?>
                        <div class="p-8 text-center text-slate-400 text-xs sm:text-sm">
                            Belum ada butir pertanyaan pada group ini. Klik tombol <strong class="text-sky-600 cursor-pointer" onclick="openModalPertanyaan(null, <?= $gId ?>)">Tambah Soal</strong> di atas.
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100">
                            <?php foreach ($qList as $idx => $q): ?>
                                <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-start justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                            <?= $idx + 1 ?>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm sm:text-base font-semibold text-slate-900 leading-snug">
                                                <?= htmlspecialchars($q['pertanyaan']) ?>
                                            </p>
                                            <?php if (!empty($q['indikator'])): ?>
                                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                                    <span class="font-medium text-slate-400">Indikator:</span>
                                                    <?= htmlspecialchars($q['indikator']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- Opsi Jawaban Preview -->
                                            <div class="mt-3 flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[11px] font-semibold text-slate-400 mr-1">Skala Opsi:</span>
                                                <?php 
                                                    $opsiArr = !empty($q['opsi_parsed']) ? $q['opsi_parsed'] : [];
                                                    if (empty($opsiArr) && !empty($q['opsi_jawaban'])) {
                                                        $decoded = json_decode($q['opsi_jawaban'], true);
                                                        if (is_array($decoded)) $opsiArr = $decoded;
                                                    }
                                                ?>
                                                <?php foreach ($opsiArr as $op): ?>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                        <span><?= htmlspecialchars($op['label'] ?? '') ?></span>
                                                        <span class="px-1.5 py-0.2 rounded bg-sky-100 text-sky-800 font-bold text-[10px] border border-sky-200">
                                                            <?= (int)($op['skor'] ?? 0) ?>
                                                        </span>
                                                    </span>
                                                <?php endforeach; ?>
                                                <span class="text-[11px] text-slate-400 ml-2 font-medium">
                                                    (Skor Maks: <strong class="text-slate-700"><?= (int)($q['skor_maksimal'] ?? 4) ?></strong>)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 self-end md:self-start shrink-0">
                                        <?php if ((int)$q['is_active'] === 1): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-400 border border-slate-200">Nonaktif</span>
                                        <?php endif; ?>
                                        <button type="button" 
                                                onclick="duplicatePertanyaan(<?= htmlspecialchars(json_encode($q)) ?>)"
                                                class="p-1.5 rounded-lg text-sky-600 hover:text-sky-800 hover:bg-sky-50 transition-colors"
                                                title="Duplikat Soal (Salin Format Jawaban)">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                            </svg>
                                        </button>
                                        <button type="button" 
                                                onclick="openModalPertanyaan(<?= htmlspecialchars(json_encode($q)) ?>)"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-colors"
                                                title="Edit Pertanyaan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmDeletePertanyaan(<?= (int)$q['id'] ?>, '<?= htmlspecialchars(addslashes(mb_substr($q['pertanyaan'], 0, 40))) ?>...')"
                                                class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors"
                                                title="Hapus Pertanyaan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL TAMBAH / EDIT GROUP PERTANYAAN -->
<!-- ========================================================================= -->
<div id="modal-group" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </div>
                    <h3 id="modal-group-title" class="text-base font-bold text-slate-800">Tambah Group Aspek Observasi</h3>
                </div>
                <button type="button" onclick="closeModalGroup()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form id="form-group" action="<?= url('kelola-spmb/observasi-awal/group/store') ?>" method="POST" class="p-6 space-y-4">
                <?= CSRF::field() ?>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="id" id="group_id" value="">
                <input type="hidden" name="jenjang" value="<?= htmlspecialchars($currentJenjang) ?>">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Group / Kategori Aspek <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama_group" 
                           id="group_nama" 
                           required 
                           placeholder="Contoh: Kemandirian & Toilet Training, Karakter Islami" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Deskripsi Singkat Aspek (Opsional)
                    </label>
                    <textarea name="deskripsi" 
                              id="group_deskripsi" 
                              rows="2" 
                              placeholder="Deskripsi atau panduan aspek observasi ini..." 
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Urut Tampil
                        </label>
                        <input type="number" 
                               name="urutan" 
                               id="group_urutan" 
                               value="1" 
                               min="1" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status Group
                        </label>
                        <div class="pt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" id="group_is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-700">Aktif Digunakan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModalGroup()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-sky-600/20 transition-all">
                        Simpan Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL TAMBAH / EDIT BUTIR PERTANYAAN (DENGAN DYNAMIC OPTION BUILDER) -->
<!-- ========================================================================= -->
<div id="modal-pertanyaan" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden transform transition-all my-8">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3 id="modal-pertanyaan-title" class="text-base font-bold text-slate-800">Tambah Butir Pertanyaan Observasi</h3>
                </div>
                <button type="button" onclick="closeModalPertanyaan()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form id="form-pertanyaan" action="<?= url('kelola-spmb/observasi-awal/pertanyaan/store') ?>" method="POST" class="p-6 space-y-4 max-h-[78vh] overflow-y-auto custom-scrollbar">
                <?= CSRF::field() ?>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="id" id="pertanyaan_id" value="">
                <input type="hidden" name="jenjang" value="<?= htmlspecialchars($currentJenjang) ?>">

                <!-- Pilih Group -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Group / Kategori Aspek <span class="text-rose-500">*</span>
                    </label>
                    <select name="group_id" id="pertanyaan_group_id" onchange="handleGroupChange(this.value)" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all bg-white font-medium">
                        <option value="">-- Pilih Group Aspek --</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= (int)$g['id'] ?>">
                                <?= htmlspecialchars($g['nama_group']) ?> (Urutan #<?= (int)$g['urutan'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Teks Pertanyaan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Teks Instrumen Pertanyaan Observasi <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="pertanyaan" 
                              id="pertanyaan_teks" 
                              required 
                              rows="3" 
                              placeholder="Tuliskan butir observasi yang ditanyakan kepada orang tua..." 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all font-medium"></textarea>
                </div>

                <!-- Indikator -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Indikator Capaian / Deskripsi Penilai (Opsional)
                    </label>
                    <input type="text" 
                           name="indikator" 
                           id="pertanyaan_indikator" 
                           placeholder="Contoh: Mampu makan secara mandiri tanpa disuapi" 
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
                </div>

                <!-- Custom Pilihan Jawaban & Bobot Skor (DYNAMIC BUILDER) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pilihan Jawaban & Bobot Skor</h4>
                            <p class="text-[11px] text-slate-500">Anda bebas menentukan teks pilihan dan nilai bobot skornya.</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="applyPreset('4skala')" class="px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-100">
                                4 Skala (4,3,2,1)
                            </button>
                            <button type="button" onclick="applyPreset('2skala')" class="px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-100">
                                2 Skala (4,2)
                            </button>
                            <button type="button" onclick="applyPreset('3skala')" class="px-2 py-1 rounded-md text-[10px] font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-100">
                                3 Skala (3,2,1)
                            </button>
                        </div>
                    </div>

                    <!-- Info Notifikasi Otomatis Salin Pilihan Jawaban dari Group -->
                    <div id="copy-inheritance-notice" class="hidden p-3 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span id="copy-inheritance-text" class="flex-1 font-medium leading-relaxed"></span>
                    </div>

                    <!-- Container Opsi Dinamis -->
                    <div id="opsi-jawaban-container" class="space-y-2">
                        <!-- Opsi baris akan diisi JS -->
                    </div>

                    <button type="button" 
                            onclick="addOpsiRow('', 1)" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-dashed border-sky-400 text-sky-700 hover:bg-sky-50 text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Tambah Opsi Jawaban Baru</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Urut Soal
                        </label>
                        <input type="number" 
                               name="urutan" 
                               id="pertanyaan_urutan" 
                               value="1" 
                               min="1" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status Soal
                        </label>
                        <div class="pt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" id="pertanyaan_is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-700">Aktif Ditanyakan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModalPertanyaan()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-sky-600/20 transition-all">
                        Simpan Pertanyaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT MODAL & DYNAMIC FORM BUILDER -->
<!-- ========================================================================= -->
<script>
const QUESTIONS_BY_GROUP = <?= json_encode($questionsByGroup, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?> || {};

// --- Modal Group Helper ---
function openModalGroup(data = null) {
    const modal = document.getElementById('modal-group');
    const title = document.getElementById('modal-group-title');
    const inputId = document.getElementById('group_id');
    const inputNama = document.getElementById('group_nama');
    const inputDeskripsi = document.getElementById('group_deskripsi');
    const inputUrutan = document.getElementById('group_urutan');
    const inputActive = document.getElementById('group_is_active');

    if (data) {
        title.innerText = 'Edit Group Aspek Observasi';
        inputId.value = data.id || '';
        inputNama.value = data.nama_group || '';
        inputDeskripsi.value = data.deskripsi || '';
        inputUrutan.value = data.urutan || 1;
        inputActive.checked = (parseInt(data.is_active) === 1);
    } else {
        title.innerText = 'Tambah Group Aspek Observasi';
        inputId.value = '';
        inputNama.value = '';
        inputDeskripsi.value = '';
        inputUrutan.value = document.querySelectorAll('.bg-white.rounded-2xl.border.border-slate-200').length + 1;
        inputActive.checked = true;
    }

    modal.classList.remove('hidden');
}

function closeModalGroup() {
    document.getElementById('modal-group').classList.add('hidden');
}

function confirmDeleteGroup(id, name) {
    Swal.fire({
        title: 'Hapus Group Pertanyaan?',
        html: `Apakah Anda yakin ingin menghapus group <strong>"${name}"</strong>?<br><span class="text-xs text-rose-600 font-semibold">Semua pertanyaan di dalam group ini juga akan dihapus!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= url('kelola-spmb/observasi-awal/group/delete/') ?>${id}`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '<?= CSRF_TOKEN_NAME ?>';
            csrfInput.value = '<?= $csrfToken ?>';
            form.appendChild(csrfInput);

            const csrfInput2 = document.createElement('input');
            csrfInput2.type = 'hidden';
            csrfInput2.name = 'csrf_token';
            csrfInput2.value = '<?= $csrfToken ?>';
            form.appendChild(csrfInput2);

            const jenjangInput = document.createElement('input');
            jenjangInput.type = 'hidden';
            jenjangInput.name = 'jenjang';
            jenjangInput.value = '<?= htmlspecialchars($currentJenjang) ?>';
            form.appendChild(jenjangInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

// --- Modal Pertanyaan & Dynamic Options Builder ---
function addOpsiRow(label = '', skor = 1) {
    const container = document.getElementById('opsi-jawaban-container');
    const rowId = 'row-op-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

    const div = document.createElement('div');
    div.id = rowId;
    div.className = 'flex items-center gap-2 p-1.5 bg-white rounded-xl border border-slate-200 shadow-2xs';
    div.innerHTML = `
        <div class="flex-1">
            <input type="text" 
                   name="opsi_label[]" 
                   value="${escapeHtml(label)}" 
                   required 
                   placeholder="Label (contoh: Sangat Sesuai, Ya, Selalu)" 
                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-1 focus:ring-sky-500 font-medium">
        </div>
        <div class="w-24 shrink-0 flex items-center gap-1">
            <span class="text-[11px] text-slate-400 font-bold">Skor:</span>
            <input type="number" 
                   name="opsi_skor[]" 
                   value="${skor}" 
                   min="0" 
                   max="100" 
                   required 
                   class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-xs sm:text-sm text-center font-bold text-sky-700 focus:ring-1 focus:ring-sky-500">
        </div>
        <button type="button" 
                onclick="document.getElementById('${rowId}').remove()" 
                class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition-colors"
                title="Hapus Opsi">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    `;
    container.appendChild(div);
}

function applyPreset(presetType) {
    const container = document.getElementById('opsi-jawaban-container');
    container.innerHTML = '';

    if (presetType === '4skala') {
        addOpsiRow('Sangat Sesuai', 4);
        addOpsiRow('Sesuai', 3);
        addOpsiRow('Cukup Sesuai', 2);
        addOpsiRow('Kurang / Tidak Sesuai', 1);
    } else if (presetType === '2skala') {
        addOpsiRow('Sangat Sesuai / Ya', 4);
        addOpsiRow('Tidak Sesuai / Belum', 2);
    } else if (presetType === '3skala') {
        addOpsiRow('Selalu / Mandiri Penuh', 3);
        addOpsiRow('Kadang-kadang / Perlu Diingatkan', 2);
        addOpsiRow('Belum Pernah / Perlu Bantuan Penuh', 1);
    }
}

// --- Otomatis Warisi Pilihan Jawaban & Nomor Urut dari Group ---
function populateOptionsFromGroup(groupId) {
    const containerOpsi = document.getElementById('opsi-jawaban-container');
    const inputUrutan = document.getElementById('pertanyaan_urutan');
    const noticeEl = document.getElementById('copy-inheritance-notice');
    const noticeText = document.getElementById('copy-inheritance-text');

    containerOpsi.innerHTML = '';
    const qList = (QUESTIONS_BY_GROUP && QUESTIONS_BY_GROUP[groupId]) ? QUESTIONS_BY_GROUP[groupId] : [];

    if (Array.isArray(qList) && qList.length > 0) {
        // Ambil pertanyaan terakhir di group ini sebagai acuan format jawaban
        const sourceQ = qList[qList.length - 1];
        let opsiArr = sourceQ.opsi_parsed || [];
        if ((!opsiArr || opsiArr.length === 0) && sourceQ.opsi_jawaban) {
            try {
                opsiArr = typeof sourceQ.opsi_jawaban === 'string' ? JSON.parse(sourceQ.opsi_jawaban) : sourceQ.opsi_jawaban;
            } catch(e) {}
        }

        if (Array.isArray(opsiArr) && opsiArr.length > 0) {
            opsiArr.forEach(op => {
                addOpsiRow(op.label || '', op.skor !== undefined ? op.skor : 1);
            });
        } else {
            applyPreset('4skala');
        }

        // Hitung nomor urutan berikutnya secara otomatis
        let maxUrutan = 0;
        qList.forEach(item => {
            const u = parseInt(item.urutan) || 0;
            if (u > maxUrutan) maxUrutan = u;
        });
        inputUrutan.value = maxUrutan + 1;

        if (noticeEl && noticeText) {
            noticeEl.classList.remove('hidden');
            noticeText.innerHTML = `Pilihan jawaban & bobot otomatis disalin dari pertanyaan di group ini. Nomor urut diset ke <strong>#${maxUrutan + 1}</strong>. Tinggal ketik pertanyaan baru!`;
        }
    } else {
        applyPreset('4skala');
        inputUrutan.value = 1;
        if (noticeEl) {
            noticeEl.classList.add('hidden');
        }
    }
}

function handleGroupChange(groupId) {
    const inputId = document.getElementById('pertanyaan_id');
    // Hanya auto-populate jika sedang membuat pertanyaan baru (bukan edit)
    if (!inputId.value && groupId) {
        populateOptionsFromGroup(groupId);
    }
}

function openModalPertanyaan(data = null, defaultGroupId = null) {
    const modal = document.getElementById('modal-pertanyaan');
    const title = document.getElementById('modal-pertanyaan-title');
    const inputId = document.getElementById('pertanyaan_id');
    const selectGroup = document.getElementById('pertanyaan_group_id');
    const inputTeks = document.getElementById('pertanyaan_teks');
    const inputIndikator = document.getElementById('pertanyaan_indikator');
    const inputUrutan = document.getElementById('pertanyaan_urutan');
    const inputActive = document.getElementById('pertanyaan_is_active');
    const containerOpsi = document.getElementById('opsi-jawaban-container');
    const noticeEl = document.getElementById('copy-inheritance-notice');

    containerOpsi.innerHTML = '';

    if (data) {
        title.innerText = 'Edit Butir Pertanyaan Observasi';
        inputId.value = data.id || '';
        selectGroup.value = data.group_id || '';
        inputTeks.value = data.pertanyaan || '';
        inputIndikator.value = data.indikator || '';
        inputUrutan.value = data.urutan || 1;
        inputActive.checked = (parseInt(data.is_active) === 1);
        if (noticeEl) noticeEl.classList.add('hidden');

        // Render opsi jawaban yang ada
        let opsiArr = data.opsi_parsed || [];
        if ((!opsiArr || opsiArr.length === 0) && data.opsi_jawaban) {
            try {
                opsiArr = typeof data.opsi_jawaban === 'string' ? JSON.parse(data.opsi_jawaban) : data.opsi_jawaban;
            } catch(e) {}
        }

        if (Array.isArray(opsiArr) && opsiArr.length > 0) {
            opsiArr.forEach(op => {
                addOpsiRow(op.label || '', op.skor !== undefined ? op.skor : 1);
            });
        } else {
            applyPreset('4skala');
        }
    } else {
        title.innerText = 'Tambah Butir Pertanyaan Observasi';
        inputId.value = '';
        if (defaultGroupId) {
            selectGroup.value = defaultGroupId;
        } else if (!selectGroup.value && selectGroup.options.length > 1) {
            selectGroup.selectedIndex = 1;
        }
        inputTeks.value = '';
        inputIndikator.value = '';
        inputActive.checked = true;

        // Otomatis salin format pilihan jawaban & nomor urut berikutnya dari group terpilih!
        populateOptionsFromGroup(selectGroup.value);
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        inputTeks.focus();
    }, 120);
}

function duplicatePertanyaan(q) {
    if (!q) return;
    openModalPertanyaan(null, q.group_id);
    const title = document.getElementById('modal-pertanyaan-title');
    title.innerText = 'Duplikat Butir Pertanyaan (Salin Format Jawaban)';

    const inputTeks = document.getElementById('pertanyaan_teks');
    inputTeks.value = ''; // Biarkan kosong agar langsung diketik soal baru
    inputTeks.placeholder = 'Ketik pertanyaan baru berikutnya di sini...';

    const inputIndikator = document.getElementById('pertanyaan_indikator');
    inputIndikator.value = q.indikator || '';

    // Salin opsi jawaban langsung dari pertanyaan ini
    const containerOpsi = document.getElementById('opsi-jawaban-container');
    containerOpsi.innerHTML = '';
    let opsiArr = q.opsi_parsed || [];
    if ((!opsiArr || opsiArr.length === 0) && q.opsi_jawaban) {
        try {
            opsiArr = typeof q.opsi_jawaban === 'string' ? JSON.parse(q.opsi_jawaban) : q.opsi_jawaban;
        } catch(e) {}
    }
    if (Array.isArray(opsiArr) && opsiArr.length > 0) {
        opsiArr.forEach(op => addOpsiRow(op.label || '', op.skor !== undefined ? op.skor : 1));
    } else {
        applyPreset('4skala');
    }

    // Set urutan berikutnya
    const qList = (QUESTIONS_BY_GROUP && QUESTIONS_BY_GROUP[q.group_id]) ? QUESTIONS_BY_GROUP[q.group_id] : [];
    let maxUrutan = 0;
    qList.forEach(item => {
        const u = parseInt(item.urutan) || 0;
        if (u > maxUrutan) maxUrutan = u;
    });
    document.getElementById('pertanyaan_urutan').value = maxUrutan + 1;

    const noticeEl = document.getElementById('copy-inheritance-notice');
    const noticeText = document.getElementById('copy-inheritance-text');
    if (noticeEl && noticeText) {
        noticeEl.classList.remove('hidden');
        noticeText.innerHTML = `Pilihan jawaban & bobot berhasil disalin dari soal sebelumnya. Nomor urut otomatis diset ke <strong>#${maxUrutan + 1}</strong>. Tinggal isi pertanyaan baru!`;
    }

    setTimeout(() => {
        inputTeks.focus();
    }, 150);
}

function closeModalPertanyaan() {
    document.getElementById('modal-pertanyaan').classList.add('hidden');
}

function confirmDeletePertanyaan(id, snippet) {
    Swal.fire({
        title: 'Hapus Pertanyaan Ini?',
        html: `Apakah Anda yakin ingin menghapus butir pertanyaan:<br><em>"${snippet}"</em>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= url('kelola-spmb/observasi-awal/pertanyaan/delete/') ?>${id}`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '<?= CSRF_TOKEN_NAME ?>';
            csrfInput.value = '<?= $csrfToken ?>';
            form.appendChild(csrfInput);

            const csrfInput2 = document.createElement('input');
            csrfInput2.type = 'hidden';
            csrfInput2.name = 'csrf_token';
            csrfInput2.value = '<?= $csrfToken ?>';
            form.appendChild(csrfInput2);

            const jenjangInput = document.createElement('input');
            jenjangInput.type = 'hidden';
            jenjangInput.name = 'jenjang';
            jenjangInput.value = '<?= htmlspecialchars($currentJenjang) ?>';
            form.appendChild(jenjangInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
