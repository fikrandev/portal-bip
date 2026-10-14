<?php
/**
 * View Detail / Biodata Calon Siswa Daftar Tunggu SPMB
 * Portal BIP
 */
$item = $pendaftar ?? [];
$st = $item['status_kelulusan'] ?? 'Cadangan';
$badgeStyle = match($st) {
    'Lulus / Diterima' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'Lolos Berkas'     => 'bg-blue-100 text-blue-800 border-blue-300',
    'Cadangan'         => 'bg-amber-100 text-amber-800 border-amber-300',
    'Tidak Lulus'      => 'bg-rose-100 text-rose-800 border-rose-300',
    'Dibatalkan'       => 'bg-slate-100 text-slate-600 border-slate-300',
    default            => 'bg-amber-100 text-amber-800 border-amber-300'
};
?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-amber-500/25 shrink-0">
                <?= strtoupper(substr($item['nama_lengkap'] ?? 'D', 0, 1)) ?>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900"><?= e($item['nama_lengkap'] ?? '-') ?></h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?>">
                        <?= e($st) ?>
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">
                    No. Pendaftaran: <strong class="text-amber-700"><?= e($item['nomor_pendaftaran'] ?? '-') ?></strong> &bull; Group: <?= e($item['nama_group'] ?? 'SPMB') ?> (<?= e($item['group_tahun_ajaran'] ?? '-') ?>)
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= url('kelola-spmb/daftar-tunggu/cetak/' . $item['id']) ?>" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5Z" /></svg>
                <span>Cetak Bukti Pendaftaran</span>
            </a>
            <a href="<?= url('kelola-spmb/daftar-tunggu/edit/' . $item['id']) ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                <span>Edit</span>
            </a>
            <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Quick Status Update Card -->
    <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Update Status Seleksi Cepat</h3>
            <p class="text-xs text-slate-500">Ubah langsung status verifikasi & kelulusan siswa daftar tunggu ini.</p>
        </div>
        <form action="<?= url('kelola-spmb/daftar-tunggu/status/' . $item['id']) ?>" method="POST" class="flex items-center gap-2">
            <?= CSRF::field() ?>
            <select name="status_kelulusan" class="px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold bg-slate-50 focus:bg-white outline-none">
                <?php foreach (['Cadangan', 'Menunggu Seleksi', 'Lolos Berkas', 'Lulus / Diterima', 'Tidak Lulus', 'Dibatalkan'] as $statusOpt): ?>
                    <option value="<?= $statusOpt ?>" <?= ($st === $statusOpt) ? 'selected' : '' ?>><?= $statusOpt ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition-colors">
                Terapkan
            </button>
        </form>
    </div>

    <!-- ── Card Tautan & Status Biodata Lengkap ── -->
    <?php
    $biodataStatus = $item['biodata_status'] ?? 'belum_diisi';
    $biodataToken = $item['biodata_token'] ?? '';
    $biodataUrl = !empty($biodataToken) ? url('spmb/biodata/' . $biodataToken) : '#';

    // Persiapan nomor tujuan dan pesan WhatsApp
    $rawWaPhone = !empty($item['no_wa']) ? $item['no_wa'] : (!empty($item['no_hp']) ? $item['no_hp'] : '');
    $targetPhoneFormatted = class_exists('WaGateway') ? WaGateway::formatPhone($rawWaPhone) : preg_replace('/[^0-9]/', '', $rawWaPhone);

    $spmbOptions = SpmbModel::getSpmbOptions();
    $waTemplate = $spmbOptions['wa_template_biodata'];
    $replacements = [
        '{nama_siswa}'        => $item['nama_lengkap'],
        '{nomor_pendaftaran}' => $item['nomor_pendaftaran'],
        '{no_daftar}'         => $item['nomor_pendaftaran'],
        '{jenjang}'           => $item['jenjang'],
        '{link_biodata}'      => $biodataUrl,
        '{jalur}'             => 'Daftar Tunggu (' . ($item['kategori_pendaftar'] ?? 'Yayasan') . ')'
    ];
    $waMessagePreview = str_replace(array_keys($replacements), array_values($replacements), $waTemplate);
    $waDirectUrl = !empty($targetPhoneFormatted) 
        ? 'https://api.whatsapp.com/send?phone=' . $targetPhoneFormatted . '&text=' . rawurlencode($waMessagePreview) 
        : '#';
    ?>
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950 text-white p-6 rounded-3xl shadow-xl border border-amber-600/40 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2 max-w-xl">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        Link Pengisian Biodata Calon Siswa
                    </span>
                    <?php if ($biodataStatus === 'lengkap'): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Sudah Diisi Lengkap
                        </span>
                    <?php elseif ($biodataStatus === 'draft'): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            Masih Draft (Langkah <?= (int)($item['biodata_step'] ?? 1) ?>/4)
                        </span>
                    <?php else: ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/20 text-slate-300 border border-slate-500/30 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Belum Diisi Orang Tua
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="text-base font-bold text-white tracking-tight">
                    Tautan Formulir Biodata Dapodik / EMIS
                </h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Bagikan tautan berikut kepada orang tua / wali murid untuk melengkapi berkas KK, Akta, KTP, dan formulir Dapodik.
                </p>

                <!-- Input box copy -->
                <div class="flex items-center gap-2 pt-1">
                    <input type="text" readonly value="<?= e($biodataUrl) ?>" id="input_biodata_url"
                           class="w-full text-xs font-mono bg-black/40 border border-white/10 rounded-xl px-3 py-2 text-slate-200 select-all focus:outline-none">
                    <button type="button" onclick="salinLinkPublic('<?= e($biodataUrl) ?>')"
                            class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold shrink-0 transition-colors border border-white/10 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                        Salin
                    </button>
                    <a href="<?= e($biodataUrl) ?>" target="_blank"
                       class="px-3 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs font-semibold shrink-0 transition-colors border border-amber-500/30 flex items-center gap-1.5">
                        Buka &rarr;
                    </a>
                </div>
            </div>

            <!-- WhatsApp Actions -->
            <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 w-full md:w-auto shrink-0">
                <form action="<?= url('kelola-spmb/kirim-wa-biodata/' . $item['id']) ?>" method="POST" onsubmit="return confirm('Kirimkan notifikasi WhatsApp otomatis sekarang ke nomor orang tua?')">
                    <?= CSRF::field() ?>
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                        <span>Kirim WA Otomatis (Server)</span>
                    </button>
                </form>

                <?php if (!empty($targetPhoneFormatted)): ?>
                    <a href="<?= $waDirectUrl ?>" target="_blank"
                       class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 transition-colors">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>Buka Chat WhatsApp Web</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 2 Column Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left Column: Info Pendaftaran & Rekomendasi -->
        <div class="space-y-6">
            <!-- Ringkasan Pendaftaran -->
            <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm space-y-3">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Informasi Pendaftaran
                </h3>
                
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Jenjang</span>
                        <span class="font-bold text-slate-800"><?= e($item['jenjang']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50 items-center">
                        <span class="text-slate-500">Rekomendasi</span>
                        <?php 
                        $katDetail = $item['kategori_pendaftar'] ?? 'Yayasan';
                        $badgeDetailColor = match($katDetail) {
                            'Yayasan' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'Dinas'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            default   => 'bg-amber-100 text-amber-800 border-amber-200',
                        };
                        $iconDetail = match($katDetail) {
                            'Yayasan' => '🏢',
                            'Dinas'   => '🏛️',
                            default   => '📌',
                        };
                        ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border <?= $badgeDetailColor ?>">
                            <?= $iconDetail ?> <?= e($katDetail) ?>
                        </span>
                    </div>
                    <?php if (!empty($item['nama_rekomendasi'])): ?>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nama Rekomendasi</span>
                        <span class="font-bold text-slate-900"><?= e($item['nama_rekomendasi']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Gelombang</span>
                        <span class="font-bold text-slate-800"><?= e($item['gelombang'] ?? 'Gelombang 1') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Tanggal Mendaftar</span>
                        <span class="font-bold text-slate-800"><?= date('d F Y', strtotime($item['tanggal_daftar'])) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Biaya Formulir</span>
                        <span class="font-bold text-slate-800">Rp <?= number_format($item['biaya_pendaftaran'] ?? 0, 0, ',', '.') ?> (Gratis)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Status Pembayaran</span>
                        <span class="font-bold text-emerald-600"><?= e($item['status_pembayaran'] ?? 'Gratis') ?></span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Kelengkapan Berkas</span>
                        <span class="font-bold text-slate-800"><?= e($item['status_berkas']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Asal Sekolah -->
            <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm space-y-2">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Sekolah Sebelumnya
                </h3>
                <p class="text-sm font-bold text-slate-800"><?= e($item['asal_sekolah'] ?: 'Tidak dicantumkan / Belum sekolah') ?></p>
            </div>

            <?php if (!empty($item['catatan'])): ?>
                <div class="bg-amber-50/50 p-5 rounded-2xl border border-amber-200/60 space-y-1 text-xs">
                    <p class="font-bold text-amber-800">Catatan Panitia SPMB:</p>
                    <p class="text-slate-700 leading-relaxed"><?= nl2br(e($item['catatan'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column (Span 2): Biodata Calon Siswa & Orang Tua -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- Biodata Siswa -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span>Identitas Calon Siswa</span>
                    <span class="text-slate-400 font-mono text-[10px]">ID: #<?= (int)$item['id'] ?></span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Lengkap</span>
                        <span class="font-bold text-slate-800 text-sm"><?= e($item['nama_lengkap']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Panggilan</span>
                        <span class="font-semibold text-slate-700"><?= e($item['nama_panggilan'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-700"><?= ($item['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan' ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">NISN / NIK</span>
                        <span class="font-mono text-slate-700"><?= e($item['nisn'] ?: '-') ?> / <?= e($item['nik'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-slate-700">
                            <?= e($item['tempat_lahir'] ?: '-') ?>, <?= !empty($item['tanggal_lahir']) ? date('d F Y', strtotime($item['tanggal_lahir'])) : '-' ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Umur Calon Siswa</span>
                        <span class="font-bold text-emerald-700"><?= e($item['umur'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Agama</span>
                        <span class="font-semibold text-slate-700"><?= e($item['agama'] ?: 'Islam') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Anak ke / Jumlah Saudara</span>
                        <span class="font-semibold text-slate-700"><?= e($item['anak_ke'] ?: '-') ?> dari <?= e($item['jumlah_saudara'] ?: '-') ?> bersaudara</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block text-[11px]">Alamat Lengkap</span>
                        <span class="font-semibold text-slate-800 leading-relaxed"><?= nl2br(e($item['alamat'] ?: '-')) ?></span>
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Data Orang Tua / Wali
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    <!-- Ayah -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="font-bold text-slate-800 text-sm border-b border-slate-200 pb-1 flex items-center justify-between">
                            <span>Ayah Kandung</span>
                            <span class="text-[10px] text-slate-400">👨</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Nama Lengkap</span>
                            <span class="font-bold text-slate-800"><?= e($item['nama_ayah'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">No. HP / WA</span>
                            <span class="font-mono text-slate-700"><?= e($item['no_hp_ayah'] ?: ($item['no_hp'] ?: '-')) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Pekerjaan</span>
                            <span class="text-slate-700"><?= e($item['pekerjaan_ayah'] ?: '-') ?></span>
                        </div>
                    </div>

                    <!-- Ibu -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="font-bold text-slate-800 text-sm border-b border-slate-200 pb-1 flex items-center justify-between">
                            <span>Ibu Kandung</span>
                            <span class="text-[10px] text-slate-400">👩</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Nama Lengkap</span>
                            <span class="font-bold text-slate-800"><?= e($item['nama_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">No. HP / WA</span>
                            <span class="font-mono text-slate-700"><?= e($item['no_hp_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Pekerjaan</span>
                            <span class="text-slate-700"><?= e($item['pekerjaan_ibu'] ?: '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Berkas / Dokumen Unggahan -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Berkas Persyaratan
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <?php
                    $berkasItems = [
                        ['label' => 'Pasfoto Calon Siswa', 'key' => 'foto'],
                        ['label' => 'Kartu Keluarga (KK)', 'key' => 'berkas_kk'],
                        ['label' => 'Akta Kelahiran', 'key' => 'berkas_akta'],
                        ['label' => 'KTP Orang Tua', 'key' => 'berkas_ktp_ayah']
                    ];
                    foreach ($berkasItems as $b):
                        $fileVal = $item[$b['key']] ?? '';
                    ?>
                        <div class="p-3 rounded-xl border border-slate-200 text-center space-y-2 flex flex-col justify-between">
                            <span class="text-[11px] font-bold text-slate-700 block"><?= $b['label'] ?></span>
                            <?php if (!empty($fileVal)): ?>
                                <a href="<?= url($fileVal) ?>" target="_blank" 
                                   class="inline-block py-1.5 px-3 rounded-lg bg-sky-50 text-sky-700 font-bold text-[11px] hover:bg-sky-100 transition-colors">
                                    Lihat Berkas &rarr;
                                </a>
                            <?php else: ?>
                                <span class="inline-block py-1 px-2 rounded-lg bg-slate-100 text-slate-400 text-[10px]">
                                    Belum Diunggah
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function salinLinkPublic(url) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Link Berhasil Disalin',
                text: 'Tautan formulir biodata lengkap berhasil disalin ke clipboard.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    } else {
        const inp = document.getElementById('input_biodata_url');
        if (inp) {
            inp.select();
            document.execCommand('copy');
            Swal.fire({
                icon: 'success',
                title: 'Link Berhasil Disalin',
                text: 'Tautan formulir biodata lengkap berhasil disalin.',
                timer: 1500,
                showConfirmButton: false
            });
        }
    }
}
</script>
