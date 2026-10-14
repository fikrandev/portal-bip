<?php
/**
 * View Detail / Biodata Calon Siswa Baru SPMB
 * Portal BIP
 */
$item = $pendaftar ?? [];
$st = $item['status_kelulusan'] ?? 'Menunggu Seleksi';
$badgeStyle = match($st) {
    'Lulus / Diterima' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'Lolos Berkas' => 'bg-blue-100 text-blue-800 border-blue-300',
    'Tidak Lulus' => 'bg-rose-100 text-rose-800 border-rose-300',
    'Dibatalkan' => 'bg-slate-100 text-slate-600 border-slate-300',
    default => 'bg-amber-100 text-amber-800 border-amber-300'
};
?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-sky-500/25 shrink-0">
                <?= strtoupper(substr($item['nama_lengkap'], 0, 1)) ?>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900"><?= e($item['nama_lengkap']) ?></h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badgeStyle ?>">
                        <?= e($st) ?>
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">
                    No. Pendaftaran: <strong class="text-sky-700"><?= e($item['nomor_pendaftaran']) ?></strong> &bull; Group: <?= e($item['nama_group'] ?? 'SPMB') ?> (<?= e($item['group_tahun_ajaran'] ?? '-') ?>)
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="bukaModalVerifikasi(<?= $item['id'] ?>)"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-sky-700 hover:bg-sky-600 text-white font-bold text-xs shadow-md shadow-sky-700/20 transition-all cursor-pointer">
                <span>📁</span>
                <span>Cek & Terima Berkas</span>
            </button>
            <a href="<?= url('kelola-spmb/siswa-baru/cetak/' . $item['id'] . '?mode=biodata') ?>" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                <span>Cetak Formulir Dapodik</span>
            </a>
            <a href="<?= url('kelola-spmb/siswa-baru/cetak/' . $item['id'] . '?mode=kwitansi-2') ?>" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5Z" /></svg>
                <span>Cetak Bukti Pendaftaran</span>
            </a>
            <a href="<?= url('kelola-spmb/pengumuman-kelulusan/cetak?jenjang=' . ($item['jenjang'] ?? 'SD') . '&jenis=Siswa Baru') ?>" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-md shadow-purple-600/20 transition-all" title="Cetak Surat Keputusan Pengumuman Kelulusan Resmi Format PDF">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                <span>SK Kelulusan</span>
            </a>
            <a href="<?= url('kelola-spmb/siswa-baru/edit/' . $item['id']) ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                <span>Edit</span>
            </a>
            <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Quick Status Update Card -->
    <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Update Status Seleksi Cepat</h3>
            <p class="text-xs text-slate-500">Ubah langsung status verifikasi & kelulusan siswa ini tanpa harus membuka halaman edit.</p>
        </div>
        <form action="<?= url('kelola-spmb/siswa-baru/status/' . $item['id']) ?>" method="POST" class="flex items-center gap-2">
            <?= CSRF::field() ?>
            <select name="status_kelulusan" class="px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold bg-slate-50 focus:bg-white outline-none">
                <?php foreach (['Menunggu Seleksi', 'Lolos Berkas', 'Lulus / Diterima', 'Cadangan', 'Tidak Lulus', 'Dibatalkan'] as $statusOpt): ?>
                    <option value="<?= $statusOpt ?>" <?= ($st === $statusOpt) ? 'selected' : '' ?>><?= $statusOpt ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold transition-colors">
                Terapkan
            </button>
        </form>
    </div>

    <!-- ── Card Pengumuman Kelulusan & Kirim WA Resmi (Jika Status Lulus / Diterima) ── -->
    <?php if ($st === 'Lulus / Diterima'): ?>
        <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50 p-5 rounded-2xl border border-emerald-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-600/20 shrink-0">
                    🎓
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-xs font-extrabold text-emerald-950 uppercase tracking-wider">Status Resmi: Lulus / Diterima</h3>
                        <?php if (!empty($item['wa_kelulusan_sent_at'])): ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                WA Terkirim: <?= date('d/m/Y H:i', strtotime($item['wa_kelulusan_sent_at'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-emerald-800 mt-0.5">Calon siswa ini telah dinyatakan resmi diterima. Anda dapat mengirimkan pesan resmi WhatsApp kelulusan langsung ke nomor orang tua/wali.</p>
                </div>
            </div>
            <form action="<?= url('kelola-spmb/kirim-wa-kelulusan/' . $item['id']) ?>" method="POST" onsubmit="return confirm('Kirim notifikasi WhatsApp pengumuman resmi DITERIMA ke nomor orang tua?')">
                <?= CSRF::field() ?>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all whitespace-nowrap cursor-pointer">
                    <span>📲</span>
                    <span>Kirim WA Pengumuman Diterima</span>
                </button>
            </form>
        </div>
    <?php endif; ?>

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
        '{jalur}'             => $item['jalur_pendaftaran'] ?? 'Reguler'
    ];
    $waMessagePreview = str_replace(array_keys($replacements), array_values($replacements), $waTemplate);
    $waDirectUrl = !empty($targetPhoneFormatted) 
        ? 'https://api.whatsapp.com/send?phone=' . $targetPhoneFormatted . '&text=' . rawurlencode($waMessagePreview) 
        : '#';
    ?>
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 text-white p-6 rounded-3xl shadow-xl border border-sky-600/40 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-sky-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <?php if ($biodataStatus === 'lengkap'): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            BIODATA LENGKAP & TERVERIFIKASI
                        </span>
                    <?php elseif ($biodataStatus === 'draft'): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            DRAFT TERSIMPAN (SEBAGIAN DIISI)
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/20 border border-sky-400/30 text-sky-300 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                            FORMULIR BIODATA BELUM DIISI
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($item['biodata_submitted_at'])): ?>
                        <span class="text-xs text-sky-200/80">
                            &bull; Terakhir Kirim: <?= date('d/m/Y H:i', strtotime($item['biodata_submitted_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="text-lg sm:text-xl font-extrabold tracking-tight text-white flex items-center gap-2">
                    <span>📋 Tautan Formulir Biodata Lengkap Siswa</span>
                </h3>

                <p class="text-xs sm:text-sm text-sky-100/90 max-w-xl">
                    Tautan publik aman khusus untuk calon siswa ini. Orang tua dapat mengisi dan mencicil berkas di smartphone tanpa perlu login akun.
                </p>

                <!-- URL Display Box -->
                <div class="flex items-center gap-2 bg-slate-950/80 p-2.5 rounded-xl border border-sky-500/30 max-w-lg mt-3">
                    <span class="text-xs text-sky-400 shrink-0 font-mono">🔗 Link:</span>
                    <input type="text" id="biodataUrlText" value="<?= htmlspecialchars($biodataUrl) ?>" readonly class="bg-transparent text-xs text-white font-mono w-full outline-none select-all truncate">
                    <button type="button" onclick="salinLinkBiodata()" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold shrink-0 transition-colors shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Salin Link</span>
                    </button>
                </div>
            </div>

            <!-- Actions Buttons -->
            <div class="flex flex-col gap-2 shrink-0 w-full md:w-64">
                <!-- 1. Kirim via Server Gateway -->
                <form action="<?= url('kelola-spmb/kirim-wa-biodata/' . $item['id']) ?>" method="POST" onsubmit="return confirm('Kirimkan pesan WhatsApp secara otomatis via Server WA Gateway ke nomor orang tua (<?= e($rawWaPhone ?: '-') ?>)?');">
                    <?= CSRF::field() ?>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-extrabold text-xs shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Kirim via Server WA Gateway</span>
                    </button>
                </form>

                <!-- 2. Kirim Langsung via wa.me -->
                <?php if (!empty($cleanWaPhone = preg_replace('/[^0-9]/', '', $targetPhoneFormatted))): ?>
                    <a href="<?= htmlspecialchars($waDirectUrl) ?>" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md transition-all">
                        <span>📲 Kirim via WhatsApp App / Web</span>
                    </a>
                <?php endif; ?>

                <!-- 3. Buka Formulir Publik -->
                <a href="<?= htmlspecialchars($biodataUrl) ?>" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 backdrop-blur-sm transition-all text-center">
                    <span>🌐 Buka Formulir Publik</span>
                </a>

                <!-- 4. Toggle Preview Teks Pesan -->
                <button type="button" onclick="document.getElementById('waPreviewBox').classList.toggle('hidden')" class="text-xs text-sky-300 hover:text-white underline text-center pt-1 font-semibold">
                    👁️ Lihat & Salin Teks Pesan WA
                </button>
            </div>
        </div>

        <!-- Box Preview Pesan WA (Collapsible) -->
        <div id="waPreviewBox" class="hidden mt-4 pt-4 border-t border-sky-800/60 text-xs">
            <div class="flex items-center justify-between pb-2">
                <span class="font-bold text-sky-300">Format Pesan WhatsApp yang Dikirimkan:</span>
                <button type="button" onclick="salinPesanWa()" class="px-2.5 py-1 rounded bg-sky-700 hover:bg-sky-600 text-white font-bold text-[11px] flex items-center gap-1">
                    📋 Salin Teks Pesan
                </button>
            </div>
            <pre id="pesanWaContent" class="p-3 bg-slate-950/80 rounded-xl text-sky-100 font-mono text-[11px] whitespace-pre-wrap leading-relaxed border border-sky-900/60 max-h-48 overflow-y-auto"><?= e($waMessagePreview) ?></pre>
        </div>
    </div>

    <!-- ── Card Tautan & Status Surat Pernyataan Komitmen Orang Tua (Wajib) ── -->
    <?php
    $statusTtdKomitmen = $item['status_ttd_komitmen'] ?? 'Belum TTD';
    $isSudahTtd = ($statusTtdKomitmen === 'Sudah TTD');
    $komitmenUrl = !empty($biodataToken) ? url('spmb/komitmen/' . $biodataToken) : '#';
    $cetakKomitmenUrl = url('kelola-spmb/surat-pernyataan/cetak/' . $item['id']);

    $decryptedAyah = SpmbModel::getDecryptedSignature($item['ttd_ayah'] ?? '');
    $decryptedIbu  = SpmbModel::getDecryptedSignature($item['ttd_ibu'] ?? '');

    $waKomitmenTemplate = $spmbOptions['wa_template_komitmen'] ?? "Assalamu'alaikum Warahmatullahi Wabarakatuh Bapak/Ibu wali dari ananda {nama_siswa},\n\nMohon untuk menandatangani Surat Pernyataan Komitmen Orang Tua/Wali Murid SIT Bina Insan Palu secara online melalui tautan resmi berikut:\n{link_komitmen}\n\nPenandatanganan ini bersifat WAJIB untuk memvalidasi pendaftaran ananda.\n\nTerima kasih.\nPanitia SPMB SIT Bina Insan Palu";
    $repKomitmen = [
        '{nama_siswa}'        => $item['nama_lengkap'],
        '{nomor_pendaftaran}' => $item['nomor_pendaftaran'],
        '{jenjang}'           => $item['jenjang'],
        '{link_komitmen}'     => $komitmenUrl,
    ];
    $waKomitmenPreview = str_replace(array_keys($repKomitmen), array_values($repKomitmen), $waKomitmenTemplate);
    $waKomitmenDirectUrl = !empty($targetPhoneFormatted)
        ? 'https://api.whatsapp.com/send?phone=' . $targetPhoneFormatted . '&text=' . rawurlencode($waKomitmenPreview)
        : '#';
    ?>
    <div class="bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white p-6 rounded-3xl shadow-xl border border-indigo-500/40 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <?php if ($isSudahTtd): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-extrabold shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            ✓ SUDAH DITANDATANGANI SECARA DIGITAL & TERENKRIPSI
                        </span>
                        <?php if (!empty($item['ttd_submitted_at'])): ?>
                            <span class="text-xs text-indigo-200/80">
                                &bull; Ditandatangani: <?= date('d/m/Y H:i', strtotime($item['ttd_submitted_at'])) ?> WITA
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($item['ttd_ip_address'])): ?>
                            <span class="text-[11px] font-mono text-indigo-300/60">
                                (IP: <?= e($item['ttd_ip_address']) ?>)
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 border border-rose-400/40 text-rose-300 text-xs font-extrabold animate-pulse shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                            ⚠️ BELUM DITANDATANGANI OLEH ORANG TUA (WAJIB)
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="text-lg sm:text-xl font-extrabold tracking-tight text-white flex items-center gap-2">
                    <span>📜 Surat Pernyataan Komitmen Orang Tua/Wali Murid</span>
                </h3>

                <p class="text-xs sm:text-sm text-indigo-100/90 max-w-xl">
                    Surat komitmen format resmi A4 unit <strong><?= e($item['jenjang']) ?></strong>. Memuat butir pendampingan, kedisiplinan, keamanan, dan pembiayaan. Wajib ditandatangani oleh Ayah dan Ibu secara digital.
                </p>

                <!-- URL Display Box -->
                <div class="flex items-center gap-2 bg-slate-950/80 p-2.5 rounded-xl border border-indigo-500/30 max-w-lg mt-3">
                    <span class="text-xs text-indigo-400 shrink-0 font-mono">🔗 Link TTD:</span>
                    <input type="text" id="komitmenUrlText" value="<?= htmlspecialchars($komitmenUrl) ?>" readonly class="bg-transparent text-xs text-white font-mono w-full outline-none select-all truncate">
                    <button type="button" onclick="salinLinkKomitmenDetail()" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shrink-0 transition-colors shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Salin Link</span>
                    </button>
                </div>

                <!-- Preview Mini Decrypted Signatures -->
                <?php if ($isSudahTtd): ?>
                    <div class="mt-4 pt-4 border-t border-indigo-800/60 flex items-center gap-6 flex-wrap">
                        <!-- Ayah -->
                        <div class="flex items-center gap-3 bg-slate-900/80 px-3.5 py-2 rounded-xl border border-indigo-700/40">
                            <div class="w-16 h-10 bg-white rounded-lg p-1 flex items-center justify-center overflow-hidden border border-slate-700">
                                <?php if (!empty($decryptedAyah['png_base64'])): ?>
                                    <img src="<?= $decryptedAyah['png_base64'] ?>" alt="TTD Ayah" class="max-h-full object-contain">
                                <?php else: ?>
                                    <span class="text-[9px] text-slate-400">TTD Ada</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs">
                                <div class="font-bold text-white"><?= e($item['ttd_nama_ayah'] ?: $item['nama_ayah'] ?: 'Ayah') ?></div>
                                <div class="text-[10px] text-emerald-400 flex items-center gap-1 font-mono">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>AES-256 Verified</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ibu -->
                        <div class="flex items-center gap-3 bg-slate-900/80 px-3.5 py-2 rounded-xl border border-indigo-700/40">
                            <div class="w-16 h-10 bg-white rounded-lg p-1 flex items-center justify-center overflow-hidden border border-slate-700">
                                <?php if (!empty($decryptedIbu['png_base64'])): ?>
                                    <img src="<?= $decryptedIbu['png_base64'] ?>" alt="TTD Ibu" class="max-h-full object-contain">
                                <?php else: ?>
                                    <span class="text-[9px] text-slate-400">TTD Ada</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs">
                                <div class="font-bold text-white"><?= e($item['ttd_nama_ibu'] ?: $item['nama_ibu'] ?: 'Ibu') ?></div>
                                <div class="text-[10px] text-emerald-400 flex items-center gap-1 font-mono">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>AES-256 Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions Buttons -->
            <div class="flex flex-col gap-2 shrink-0 w-full md:w-64">
                <!-- 1. Cetak / Lihat Surat Komitmen -->
                <a href="<?= htmlspecialchars($cetakKomitmenUrl) ?>" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-extrabold text-xs shadow-lg shadow-indigo-500/20 transition-all transform hover:-translate-y-0.5 text-center">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>🖨️ Cetak Surat Komitmen (PDF)</span>
                </a>

                <!-- 2. Kirim Link TTD via Server WA Gateway -->
                <form action="<?= url('kelola-spmb/kirim-wa-komitmen/' . $item['id']) ?>" method="POST" onsubmit="return confirm('Kirimkan tautan TTD Surat Komitmen via WhatsApp Gateway ke nomor orang tua (<?= e($rawWaPhone ?: '-') ?>)?');">
                    <?= CSRF::field() ?>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-extrabold text-xs shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Kirim Link TTD via Gateway</span>
                    </button>
                </form>

                <!-- 3. Kirim via wa.me -->
                <?php if (!empty($cleanWaPhone = preg_replace('/[^0-9]/', '', $targetPhoneFormatted))): ?>
                    <a href="<?= htmlspecialchars($waKomitmenDirectUrl) ?>" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md transition-all text-center">
                        <span>📲 Kirim via WhatsApp Web/App</span>
                    </a>
                <?php endif; ?>

                <!-- 4. Buka Halaman TTD Publik -->
                <a href="<?= htmlspecialchars($komitmenUrl) ?>" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 backdrop-blur-sm transition-all text-center">
                    <span>🌐 Buka Halaman TTD Publik</span>
                </a>

                <!-- 5. Reset TTD jika sudah TTD -->
                <?php if ($isSudahTtd): ?>
                    <form action="<?= url('kelola-spmb/surat-pernyataan/reset-ttd/' . $item['id']) ?>" method="POST" onsubmit="return confirm('PERHATIAN: Apakah Anda yakin ingin mereset tanda tangan komitmen siswa ini? Status akan kembali menjadi Belum TTD dan orang tua harus menandatangani ulang.');" class="pt-1">
                        <?= CSRF::field() ?>
                        <button type="submit" class="w-full py-1.5 text-xs text-rose-300 hover:text-rose-100 font-semibold underline text-center">
                            🔄 Reset Tanda Tangan Komitmen
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Toggle Preview Pesan WA -->
                <button type="button" onclick="document.getElementById('waKomitmenPreviewBox').classList.toggle('hidden')" class="text-xs text-indigo-300 hover:text-white underline text-center pt-1 font-semibold">
                    👁️ Preview Teks WA Komitmen
                </button>
            </div>
        </div>

        <!-- Box Preview Pesan WA Komitmen (Collapsible) -->
        <div id="waKomitmenPreviewBox" class="hidden mt-4 pt-4 border-t border-indigo-800/60 text-xs">
            <div class="flex items-center justify-between pb-2">
                <span class="font-bold text-indigo-300">Format Pesan WhatsApp Komitmen yang Dikirimkan:</span>
                <button type="button" onclick="salinPesanWaKomitmen()" class="px-2.5 py-1 rounded bg-indigo-700 hover:bg-indigo-600 text-white font-bold text-[11px] flex items-center gap-1">
                    📋 Salin Teks Pesan
                </button>
            </div>
            <pre id="pesanWaKomitmenContent" class="p-3 bg-slate-950/80 rounded-xl text-indigo-100 font-mono text-[11px] whitespace-pre-wrap leading-relaxed border border-indigo-900/60 max-h-48 overflow-y-auto"><?= e($waKomitmenPreview) ?></pre>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Column 1: Info Pendaftaran & Administrasi -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm space-y-3">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Administrasi Pendaftaran
                </h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Jenjang Tujuan</span>
                        <span class="font-bold text-slate-800"><?= e($item['jenjang']) ?> IT</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50 items-center">
                        <span class="text-slate-500">Kategori Pendaftar</span>
                        <?php 
                        $katDetail = $item['kategori_pendaftar'] ?: ($item['jalur_pendaftaran'] ?: 'Umum (Luar Bina Insan)');
                        $isKeluargaDetail = str_contains(strtolower($katDetail), 'keluarga') || str_contains(strtolower($katDetail), 'adik') || str_contains(strtolower($katDetail), 'saudara');
                        ?>
                        <?php if ($isKeluargaDetail): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                👨‍👩‍👦 Keluarga Bina Insan
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                🌐 Umum (Luar Bina Insan)
                            </span>
                        <?php endif; ?>
                    </div>
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
                        <span class="font-bold text-slate-800">Rp <?= number_format($item['biaya_pendaftaran'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Status Pembayaran</span>
                        <span class="font-bold <?= ($item['status_pembayaran'] === 'Lunas' || $item['status_pembayaran'] === 'Gratis') ? 'text-emerald-600' : 'text-rose-600' ?>"><?= e($item['status_pembayaran']) ?></span>
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
                    <p class="text-amber-900/80"><?= nl2br(e($item['catatan'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Column 2 & 3: Biodata & Kontak & Orang Tua -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Biodata Siswa -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Biodata Lengkap Calon Siswa
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block mb-0.5">Nama Panggilan</span>
                        <span class="font-semibold text-slate-800"><?= e($item['nama_panggilan'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-800"><?= $item['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">NISN</span>
                        <span class="font-mono font-bold text-slate-800"><?= e($item['nisn'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">NIK Siswa</span>
                        <span class="font-mono font-bold text-slate-800"><?= e($item['nik'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Tempat & Tanggal Lahir</span>
                        <span class="font-semibold text-slate-800">
                            <?= e($item['tempat_lahir'] ?: '-') ?>, <?= !empty($item['tanggal_lahir']) ? date('d F Y', strtotime($item['tanggal_lahir'])) : '-' ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Agama</span>
                        <span class="font-semibold text-slate-800"><?= e($item['agama'] ?: 'Islam') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Urutan Anak</span>
                        <span class="font-semibold text-slate-800">Anak ke-<?= e($item['anak_ke'] ?: '1') ?> dari <?= e($item['jumlah_saudara'] ?: '0') ?> bersaudara</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">No. HP / WhatsApp Siswa</span>
                        <span class="font-semibold text-slate-800"><?= e($item['no_hp'] ?: '-') ?></span>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="pt-3 border-t border-slate-100 text-xs">
                    <span class="text-slate-500 block mb-0.5">Alamat Tempat Tinggal</span>
                    <p class="font-semibold text-slate-800 leading-relaxed">
                        <?= e($item['alamat'] ?: '-') ?><br>
                        <?php if (!empty($item['rt']) || !empty($item['rw'])): ?>
                            RT <?= e($item['rt'] ?: '-') ?> / RW <?= e($item['rw'] ?: '-') ?>, 
                        <?php endif; ?>
                        Kel. <?= e($item['kelurahan'] ?: '-') ?>, Kec. <?= e($item['kecamatan'] ?: '-') ?>, <?= e($item['kota'] ?: 'Palu') ?>
                    </p>
                </div>
            </div>

            <!-- Orang Tua / Wali -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Data Orang Tua / Wali
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    <!-- Ayah -->
                    <div class="p-4 rounded-xl bg-sky-50/50 border border-sky-100 space-y-2">
                        <p class="font-bold text-sky-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span> Ayah Kandung
                        </p>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Nama Lengkap:</span>
                            <span class="font-bold text-slate-800"><?= e($item['nama_ayah'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">NIK:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['nik_ayah'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Tahun Lahir / Pendidikan:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['tahun_lahir_ayah'] ?: '-') ?> / <?= e($item['pendidikan_ayah'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Pekerjaan & Kantor:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['pekerjaan_ayah'] ?: '-') ?> <?= !empty($item['kantor_ayah']) ? 'di ' . e($item['kantor_ayah']) : '' ?> <?= !empty($item['jabatan_ayah']) ? '(' . e($item['jabatan_ayah']) . ')' : '' ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Penghasilan Bulanan:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['penghasilan_ayah'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">No. Handphone / WhatsApp:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['no_hp_ayah'] ?: '-') ?></span>
                        </div>
                    </div>

                    <!-- Ibu -->
                    <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-100 space-y-2">
                        <p class="font-bold text-rose-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Ibu Kandung
                        </p>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Nama Lengkap:</span>
                            <span class="font-bold text-slate-800"><?= e($item['nama_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">NIK:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['nik_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Tahun Lahir / Pendidikan:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['tahun_lahir_ibu'] ?: '-') ?> / <?= e($item['pendidikan_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Pekerjaan & Kantor:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['pekerjaan_ibu'] ?: '-') ?> <?= !empty($item['kantor_ibu']) ? 'di ' . e($item['kantor_ibu']) : '' ?> <?= !empty($item['jabatan_ibu']) ? '(' . e($item['jabatan_ibu']) . ')' : '' ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Penghasilan Bulanan:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['penghasilan_ibu'] ?: '-') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">No. Handphone / WhatsApp:</span>
                            <span class="font-semibold text-slate-700"><?= e($item['no_hp_ibu'] ?: '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Berkas Dokumen Pendukung & Verifikasi -->
            <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">
                        Berkas Dokumen Pendukung Terupload (Maks. 500 KB)
                    </h3>
                    <span class="text-xs font-bold text-slate-500">
                        Status: <strong class="<?= $item['biodata_status'] === 'lengkap' ? 'text-emerald-600' : 'text-amber-600' ?>"><?= strtoupper(str_replace('_', ' ', $item['biodata_status'] ?? 'belum diisi')) ?></strong>
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 text-xs text-center">
                    <!-- 1. Kartu Keluarga -->
                    <div class="p-3 rounded-xl border <?= !empty($item['berkas_kk']) ? 'border-emerald-200 bg-emerald-50/50' : 'border-dashed border-slate-200 bg-slate-50' ?> space-y-2">
                        <div class="text-2xl"><?= !empty($item['berkas_kk']) ? '📄' : '⏳' ?></div>
                        <div class="font-bold text-slate-800">Kartu Keluarga</div>
                        <?php if (!empty($item['berkas_kk'])): ?>
                            <a href="<?= asset($item['berkas_kk']) ?>" target="_blank" class="inline-block px-2 py-1 rounded bg-emerald-600 text-white font-bold text-[10px] hover:bg-emerald-500">Lihat File</a>
                        <?php else: ?>
                            <span class="text-[10px] text-slate-400">Belum diunggah</span>
                        <?php endif; ?>
                    </div>

                    <!-- 2. Akta Kelahiran -->
                    <div class="p-3 rounded-xl border <?= !empty($item['berkas_akta']) ? 'border-emerald-200 bg-emerald-50/50' : 'border-dashed border-slate-200 bg-slate-50' ?> space-y-2">
                        <div class="text-2xl"><?= !empty($item['berkas_akta']) ? '📜' : '⏳' ?></div>
                        <div class="font-bold text-slate-800">Akta Lahir</div>
                        <?php if (!empty($item['berkas_akta'])): ?>
                            <a href="<?= asset($item['berkas_akta']) ?>" target="_blank" class="inline-block px-2 py-1 rounded bg-emerald-600 text-white font-bold text-[10px] hover:bg-emerald-500">Lihat File</a>
                        <?php else: ?>
                            <span class="text-[10px] text-slate-400">Belum diunggah</span>
                        <?php endif; ?>
                    </div>

                    <!-- 3. KTP Ayah -->
                    <div class="p-3 rounded-xl border <?= !empty($item['berkas_ktp_ayah']) ? 'border-emerald-200 bg-emerald-50/50' : 'border-dashed border-slate-200 bg-slate-50' ?> space-y-2">
                        <div class="text-2xl"><?= !empty($item['berkas_ktp_ayah']) ? '🪪' : '⏳' ?></div>
                        <div class="font-bold text-slate-800">KTP Ayah</div>
                        <?php if (!empty($item['berkas_ktp_ayah'])): ?>
                            <a href="<?= asset($item['berkas_ktp_ayah']) ?>" target="_blank" class="inline-block px-2 py-1 rounded bg-emerald-600 text-white font-bold text-[10px] hover:bg-emerald-500">Lihat File</a>
                        <?php else: ?>
                            <span class="text-[10px] text-slate-400">Belum diunggah</span>
                        <?php endif; ?>
                    </div>

                    <!-- 4. KTP Ibu -->
                    <div class="p-3 rounded-xl border <?= !empty($item['berkas_ktp_ibu']) ? 'border-emerald-200 bg-emerald-50/50' : 'border-dashed border-slate-200 bg-slate-50' ?> space-y-2">
                        <div class="text-2xl"><?= !empty($item['berkas_ktp_ibu']) ? '🪪' : '⏳' ?></div>
                        <div class="font-bold text-slate-800">KTP Ibu</div>
                        <?php if (!empty($item['berkas_ktp_ibu'])): ?>
                            <a href="<?= asset($item['berkas_ktp_ibu']) ?>" target="_blank" class="inline-block px-2 py-1 rounded bg-emerald-600 text-white font-bold text-[10px] hover:bg-emerald-500">Lihat File</a>
                        <?php else: ?>
                            <span class="text-[10px] text-slate-400">Belum diunggah</span>
                        <?php endif; ?>
                    </div>

                    <!-- 5. Screenshot Dapodik -->
                    <div class="p-3 rounded-xl border <?= !empty($item['file_screenshot_dapodik']) ? 'border-emerald-200 bg-emerald-50/50' : 'border-dashed border-slate-200 bg-slate-50' ?> space-y-2">
                        <div class="text-2xl"><?= !empty($item['file_screenshot_dapodik']) ? '📸' : '⏳' ?></div>
                        <div class="font-bold text-slate-800">Dapodik</div>
                        <?php if (!empty($item['file_screenshot_dapodik'])): ?>
                            <a href="<?= asset($item['file_screenshot_dapodik']) ?>" target="_blank" class="inline-block px-2 py-1 rounded bg-emerald-600 text-white font-bold text-[10px] hover:bg-emerald-500">Lihat File</a>
                        <?php else: ?>
                            <span class="text-[10px] text-slate-400">Belum diunggah</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyTextToClipboard(text, successMsg) {
    if (!text) return;
    successMsg = successMsg || 'Berhasil disalin ke clipboard!';

    function showSuccess() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disalin!',
                text: successMsg,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert(successMsg);
        }
    }

    if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard.writeText(text).then(showSuccess).catch(function() {
            fallbackExecCopy(text, showSuccess);
        });
    } else {
        fallbackExecCopy(text, showSuccess);
    }
}

function fallbackExecCopy(text, onSuccess) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "-9999px";
    textArea.style.left = "-9999px";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        var successful = document.execCommand('copy');
        if (successful) {
            if (onSuccess) onSuccess();
        } else {
            prompt("Salin manual tautan di bawah ini (Ctrl+C):", text);
        }
    } catch (err) {
        prompt("Salin manual tautan di bawah ini (Ctrl+C):", text);
    }
    document.body.removeChild(textArea);
}

function salinLinkBiodata() {
    const input = document.getElementById('biodataUrlText');
    if (!input || !input.value) return;
    copyTextToClipboard(input.value, "Tautan pengisian formulir biodata berhasil disalin!");
}

function salinPesanWa() {
    const textEl = document.getElementById('pesanWaContent');
    if (!textEl) return;
    copyTextToClipboard(textEl.innerText || textEl.value, "Isi pesan WhatsApp berhasil disalin!");
}

function salinLinkKomitmenDetail() {
    const input = document.getElementById('komitmenUrlText');
    if (!input || !input.value) return;
    copyTextToClipboard(input.value, "Tautan penandatanganan Surat Komitmen berhasil disalin!");
}

function salinPesanWaKomitmen() {
    const textEl = document.getElementById('pesanWaKomitmenContent');
    if (!textEl) return;
    copyTextToClipboard(textEl.innerText || textEl.value, "Isi pesan WhatsApp Surat Komitmen berhasil disalin!");
}
</script>

<?php include BASE_PATH . '/modules/kelola-spmb/views/partials/modal_verifikasi_berkas.php'; ?>
