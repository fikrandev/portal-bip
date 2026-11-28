<?php
/**
 * View Utama Modul Pusat Notifikasi (Pengaturan)
 */
$csrfToken = CSRF::getToken();
$roleMap = [];
foreach (($roles ?? []) as $rl) {
    $roleMap[(int)$rl['id']] = (string)$rl['name'];
}

$jenisAkunLabels = [
    'all'                => 'Semua Akun',
    'guru'               => 'Seluruh Guru',
    'support_system'     => 'Staff / Support System',
    'otorisasi_admin'    => 'Otorisasi Admin',
    'otorisasi_personal' => 'Otorisasi Personal',
    'role'               => 'Peran / Role Tertentu',
];

$platformLabels = [
    'both'   => 'Web & Mobile',
    'web'    => 'Portal Web',
    'mobile' => 'Portal Mobile',
];

$prioritasColors = [
    'info'    => 'bg-sky-50 text-sky-700 border-sky-200',
    'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
    'urgent'  => 'bg-rose-50 text-rose-700 border-rose-200',
];
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    Pengaturan &bull; Pusat Notifikasi
                </span>
                <?php if (!empty($unitScope['can_all'])): ?>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Hak Akses Unit: Semua Unit (Lintas Unit)
                    </span>
                <?php else: ?>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        RBAC Unit: <?= e(implode(', ', $unitScope['units'] ?? [])) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Pusat Notifikasi Portal Web &amp; Mobile</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kirim <strong>Push Notifikasi</strong> instan dengan bunyi bawaan perangkat (Laptop, Tablet, iPad, HP) atau <strong>Popup Awalan</strong> yang muncul ulang setiap 5 menit.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <button type="button" onclick="window.BipNotifCenter && window.BipNotifCenter.enableBrowserPush(true)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs sm:text-sm font-bold cursor-pointer transition-colors">
                🔔 Aktifkan Push Perangkat Ini
            </button>
            <?php if ($canCreate): ?>
                <button type="button" onclick="openNotifFormModal()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold shadow-sm cursor-pointer transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Notifikasi
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase">Total Notifikasi</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['total'] ?? 0) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Sesuai cakupan unit Anda</p>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-sky-200/80 shadow-xs">
            <span class="text-xs font-bold text-sky-600 uppercase">Push Terkirim</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-sky-600 mt-1"><?= number_format($stats['push_sent'] ?? 0) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Broadcast push real-time</p>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-amber-200/80 shadow-xs">
            <span class="text-xs font-bold text-amber-600 uppercase">Popup Aktif</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-1"><?= number_format($stats['popup_aktif'] ?? 0) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Muncul berkala di user</p>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-emerald-200/80 shadow-xs">
            <span class="text-xs font-bold text-emerald-600 uppercase">Perangkat Push Aktif</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['active_devices'] ?? 0) ?></div>
            <p class="text-xs text-slate-500 mt-0.5">Laptop / Tablet / iPad / HP</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="<?= url('pusat-notifikasi') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>"
                   placeholder="Cari judul, isi pesan, pengirim..."
                   class="lg:col-span-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm outline-none focus:border-primary-500">
            <select name="tipe" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                <option value="">Semua Jenis</option>
                <option value="push" <?= ($filters['tipe'] ?? '') === 'push' ? 'selected' : '' ?>>Push Notifikasi</option>
                <option value="popup" <?= ($filters['tipe'] ?? '') === 'popup' ? 'selected' : '' ?>>Popup Berkala</option>
            </select>
            <select name="platform" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                <option value="">Semua Platform</option>
                <option value="both" <?= ($filters['platform'] ?? '') === 'both' ? 'selected' : '' ?>>Web &amp; Mobile</option>
                <option value="web" <?= ($filters['platform'] ?? '') === 'web' ? 'selected' : '' ?>>Portal Web</option>
                <option value="mobile" <?= ($filters['platform'] ?? '') === 'mobile' ? 'selected' : '' ?>>Portal Mobile</option>
            </select>
            <select name="status" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                <option value="">Semua Status</option>
                <option value="aktif" <?= ($filters['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="nonaktif" <?= ($filters['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
            <div class="flex gap-2">
                <select name="unit" class="flex-1 px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Unit</option>
                    <?php if (!empty($unitScope['can_all'])): ?>
                        <option value="ALL" <?= ($filters['unit'] ?? '') === 'ALL' ? 'selected' : '' ?>>Lintas Semua Unit</option>
                    <?php endif; ?>
                    <?php foreach (($unitScope['units'] ?? []) as $uOpt): ?>
                        <option value="<?= e($uOpt) ?>" <?= strtoupper($filters['unit'] ?? '') === strtoupper($uOpt) ? 'selected' : '' ?>><?= e($uOpt) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold cursor-pointer">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Notifikasi -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                        <th class="py-3.5 px-4">Isi Notifikasi &amp; Tautan</th>
                        <th class="py-3.5 px-4">Jenis &amp; Bunyi</th>
                        <th class="py-3.5 px-4">Target Platform &amp; Akun</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4 text-center">Status &amp; Jangkauan</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada notifikasi pada filter/unit ini. Klik <strong>Buat Notifikasi</strong> untuk mengirim informasi baru.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $it):
                            $canEditRow = PusatNotifikasiModel::canUserManageNotification($it);
                            $pColor = $prioritasColors[$it['prioritas'] ?? 'info'] ?? $prioritasColors['info'];
                            $jenisKey = (string)($it['target_jenis_akun'] ?? 'all');
                            $jenisTxt = $jenisAkunLabels[$jenisKey] ?? $jenisKey;
                            if ($jenisKey === 'role' && !empty($it['target_role_ids'])) {
                                $rNames = [];
                                foreach (explode(',', (string)$it['target_role_ids']) as $rid) {
                                    $ridInt = (int)trim($rid);
                                    if (isset($roleMap[$ridInt])) {
                                        $rNames[] = $roleMap[$ridInt];
                                    }
                                }
                                if (!empty($rNames)) {
                                    $jenisTxt = 'Role: ' . implode(', ', $rNames);
                                }
                            }
                            $unitsArr = PusatNotifikasiModel::parseUnitsList((string)($it['target_units'] ?? 'ALL'));
                            $isAllUnits = empty($unitsArr) || in_array('ALL', $unitsArr, true);
                        ?>
                            <tr class="hover:bg-slate-50/70 align-top">
                                <td class="py-4 px-4 max-w-md">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border <?= $pColor ?>">
                                            <?= e($it['prioritas'] ?? 'info') ?>
                                        </span>
                                        <span class="font-extrabold text-slate-900"><?= e($it['judul']) ?></span>
                                    </div>
                                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed"><?= e($it['pesan']) ?></p>
                                    <?php if (!empty($it['link_url'])): ?>
                                        <div class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px] font-semibold max-w-xs truncate">
                                            <span>🔗</span>
                                            <span class="truncate"><?= e($it['link_label'] ?: 'Buka Tautan') ?>: <?= e($it['link_url']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="text-[11px] text-slate-400 mt-1.5">
                                        Oleh <strong><?= e($it['created_by_name'] ?? 'Admin') ?></strong>
                                        <?php if (!empty($it['sent_at'])): ?>
                                            &bull; Dikirim <?= date('d/m/Y H:i', strtotime($it['sent_at'])) ?>
                                        <?php else: ?>
                                            &bull; Dibuat <?= date('d/m/Y H:i', strtotime($it['created_at'])) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <?php if (($it['tipe'] ?? 'push') === 'popup'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                                            🪟 Popup Awal &amp; tiap <?= (int)($it['popup_interval_menit'] ?? 5) ?> mnt
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-200 text-xs font-bold">
                                            📣 Push Notifikasi
                                        </span>
                                    <?php endif; ?>
                                    <div class="mt-1.5 text-[11px] font-semibold <?= !empty($it['pakai_suara']) ? 'text-emerald-600' : 'text-slate-400' ?>">
                                        <?= !empty($it['pakai_suara']) ? '🔊 Bunyi Default Aktif' : '🔇 Tanpa Suara' ?>
                                    </div>
                                    <?php if (!empty($it['berakhir_at'])): ?>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            s/d <?= date('d/m/Y H:i', strtotime($it['berakhir_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 mb-1">
                                        <?= e($platformLabels[$it['target_platform'] ?? 'both'] ?? 'Web & Mobile') ?>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-800"><?= e($jenisTxt) ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <?php if ($isAllUnits): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Semua Unit
                                        </span>
                                    <?php else: ?>
                                        <div class="flex flex-wrap gap-1 max-w-[160px]">
                                            <?php foreach ($unitsArr as $uBadge): ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    <?= e($uBadge) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <?php if (($it['status'] ?? '') === 'aktif'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                        </span>
                                    <?php elseif (($it['status'] ?? '') === 'nonaktif'): ?>
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            Dihentikan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Draft
                                        </span>
                                    <?php endif; ?>
                                    <div class="mt-1.5 text-[11px] text-slate-500">
                                        Target: <strong><?= (int)($it['sent_count'] ?? 0) ?></strong> akun
                                        <?php if ((int)($it['push_device_sent'] ?? 0) > 0): ?>
                                            &bull; WebPush: <strong><?= (int)$it['push_device_sent'] ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" onclick="openReadersModal(<?= (int)$it['id'] ?>)"
                                            class="mt-1 text-[11px] font-bold text-primary-600 hover:underline cursor-pointer">
                                        Dibaca: <?= (int)($it['read_count'] ?? 0) ?> (Klik link: <?= (int)($it['click_count'] ?? 0) ?>)
                                    </button>
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap space-y-1.5">
                                    <?php if ($canEditRow): ?>
                                        <div class="flex flex-wrap justify-end gap-1.5">
                                            <?php if ($canSend): ?>
                                                <form method="POST" action="<?= url('pusat-notifikasi/send/' . (int)$it['id']) ?>" class="inline"
                                                      onsubmit="return confirm('Kirim notifikasi ini sekarang ke seluruh target?')">
                                                    <?= CSRF::field() ?>
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold cursor-pointer">
                                                        <?= ($it['status'] ?? '') === 'aktif' ? 'Kirim Ulang' : 'Kirim' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($canUpdate && ($it['status'] ?? '') !== 'draft'): ?>
                                                <form method="POST" action="<?= url('pusat-notifikasi/toggle/' . (int)$it['id']) ?>" class="inline">
                                                    <?= CSRF::field() ?>
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl <?= ($it['status'] ?? '') === 'aktif' ? 'bg-amber-50 hover:bg-amber-500 text-amber-700 hover:text-white border border-amber-200' : 'bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200' ?> text-xs font-bold cursor-pointer">
                                                        <?= ($it['status'] ?? '') === 'aktif' ? 'Hentikan' : 'Aktifkan' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex flex-wrap justify-end gap-1.5">
                                            <?php if ($canUpdate): ?>
                                                <button type="button" onclick='openNotifFormModal(<?= json_encode($it, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                                                    Edit
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <form method="POST" action="<?= url('pusat-notifikasi/delete/' . (int)$it['id']) ?>" class="inline"
                                                      onsubmit="return confirm('Hapus notifikasi ini secara permanen?')">
                                                    <?= CSRF::field() ?>
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 text-xs font-bold cursor-pointer">
                                                        Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[11px] text-slate-400 italic">Hanya Lihat</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat / Edit Notifikasi -->
<div id="notifFormModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden my-6">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 id="notifModalTitle" class="text-lg font-extrabold text-slate-900">Buat Notifikasi Baru</h3>
                <p class="text-xs text-slate-500">Pilih tipe Push (dengan bunyi default) atau Popup berkala setiap N menit.</p>
            </div>
            <button type="button" onclick="closeNotifFormModal()" class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center cursor-pointer">
                ✕
            </button>
        </div>

        <form id="notifFormEl" method="POST" action="<?= url('pusat-notifikasi/store') ?>" class="p-6 space-y-4 max-h-[78vh] overflow-y-auto text-sm">
            <?= CSRF::field() ?>
            <input type="hidden" name="action_mode" id="pn_action_mode" value="draft">

            <!-- Pilih 2 Jenis Notifikasi -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">1. Pilih Jenis Notifikasi *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe" value="push" id="pn_tipe_push" class="peer sr-only" checked onchange="toggleNotifTypeFields()">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-200 peer-checked:border-primary-600 peer-checked:bg-primary-50/50 transition-all">
                            <div class="font-extrabold text-slate-900 flex items-center gap-2">
                                <span>📣</span> Push Notifikasi
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Muncul langsung di HP, Tablet/iPad, &amp; Laptop beserta bunyi notifikasi default.</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe" value="popup" id="pn_tipe_popup" class="peer sr-only" onchange="toggleNotifTypeFields()">
                        <div class="p-3.5 rounded-2xl border-2 border-slate-200 peer-checked:border-amber-500 peer-checked:bg-amber-50/50 transition-all">
                            <div class="font-extrabold text-slate-900 flex items-center gap-2">
                                <span>🪟</span> Popup Awalan &amp; Berkala
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Tampil saat membuka aplikasi dan muncul ulang otomatis setiap 5 menit selama aktif.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Interval Popup (hanya tampil saat tipe = popup) -->
            <div id="pn_popup_interval_wrap" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3 bg-amber-50/70 p-3.5 rounded-2xl border border-amber-200">
                <div>
                    <label class="block text-xs font-bold text-amber-900 mb-1">Muncul Ulang Setiap (Menit)</label>
                    <input type="number" name="popup_interval_menit" id="pn_interval" min="1" max="1440" value="5"
                           class="w-full px-3.5 py-2 rounded-xl border border-amber-300 bg-white font-bold text-amber-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-amber-900 mb-1">Berakhir Otomatis (Opsional)</label>
                    <input type="datetime-local" name="berakhir_at" id="pn_berakhir_at"
                           class="w-full px-3.5 py-2 rounded-xl border border-amber-300 bg-white text-xs outline-none">
                </div>
            </div>

            <!-- Judul & Prioritas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Judul Notifikasi *</label>
                    <input type="text" name="judul" id="pn_judul" required maxlength="180"
                           placeholder="Contoh: Rapat Koordinasi Guru / Pengisian Jurnal"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Prioritas</label>
                    <select name="prioritas" id="pn_prioritas" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        <option value="info">Info (Biru)</option>
                        <option value="success">Sukses (Hijau)</option>
                        <option value="warning">Peringatan (Kuning)</option>
                        <option value="urgent">Penting / Urgent (Merah)</option>
                    </select>
                </div>
            </div>

            <!-- Pesan -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Isi Pesan *</label>
                <textarea name="pesan" id="pn_pesan" rows="3" required
                          placeholder="Tulis pengumuman atau instruksi untuk penerima..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white outline-none focus:border-primary-500"></textarea>
            </div>

            <!-- Sematkan Link Opsional -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sematkan Link Tujuan (Opsional)</label>
                    <input type="text" name="link_url" id="pn_link_url"
                           placeholder="Contoh: https://... atau /mobile/jurnal"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Label Tombol Link</label>
                    <input type="text" name="link_label" id="pn_link_label" value="Buka Tautan" maxlength="80"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs outline-none focus:border-primary-500">
                </div>
            </div>

            <!-- Target Platform & Jenis Akun -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Target Aplikasi / Platform *</label>
                    <select name="target_platform" id="pn_platform" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        <option value="both">Portal Web &amp; Portal Mobile</option>
                        <option value="web">Hanya Portal Web (Laptop / Tablet / iPad)</option>
                        <option value="mobile">Hanya Portal Mobile Guru (PWA / HP)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Target Jenis Akun *</label>
                    <select name="target_jenis_akun" id="pn_jenis_akun" onchange="toggleRoleChecklist()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 outline-none">
                        <option value="all">Semua Akun</option>
                        <option value="guru">Seluruh Guru</option>
                        <option value="support_system">Seluruh Staff / Support System</option>
                        <option value="otorisasi_admin">Akun Otorisasi Admin</option>
                        <option value="otorisasi_personal">Akun Otorisasi Personal</option>
                        <option value="role">Berdasarkan Peran (Role) Tertentu</option>
                    </select>
                </div>
            </div>

            <!-- Checklist Role (muncul saat jenis_akun = role) -->
            <div id="pn_role_wrap" class="hidden p-3.5 rounded-2xl border border-indigo-200 bg-indigo-50/50">
                <label class="block text-xs font-bold text-indigo-900 mb-2">Pilih Role Penerima *</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (($roles ?? []) as $rl): ?>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-indigo-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="target_role_ids[]" value="<?= (int)$rl['id'] ?>" class="pn-role-cb rounded text-indigo-600">
                            <span><?= e($rl['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Target Unit (RBAC Unit) -->
            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-700 uppercase">Target Unit / Jenjang</label>
                    <?php if (!empty($unitScope['can_all'])): ?>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="checkbox" name="target_unit_all" value="1" id="pn_unit_all" checked onchange="toggleAllUnitsCheckbox()">
                            <span>Kirim ke Semua Unit</span>
                        </label>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap gap-2" id="pn_units_list">
                    <?php foreach (($unitScope['units'] ?? []) as $uItem): ?>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="target_units[]" value="<?= e($uItem) ?>"
                                   class="pn-unit-cb rounded text-primary-600"
                                   <?= empty($unitScope['can_all']) ? 'checked' : '' ?>
                                   onchange="onSingleUnitChanged()">
                            <span><?= e($uItem) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Suara Default -->
            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                <input type="checkbox" name="pakai_suara" id="pn_pakai_suara" value="1" checked class="w-4 h-4 rounded text-primary-600">
                <span>🔊 Bunyikan nada notifikasi default di perangkat penerima (Laptop, Tablet, iPad, HP)</span>
            </label>

            <div class="pt-3 border-t border-slate-100 flex flex-wrap justify-end gap-2">
                <button type="button" onclick="closeNotifFormModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Batal
                </button>
                <button type="submit" onclick="document.getElementById('pn_action_mode').value='draft'"
                        class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold cursor-pointer">
                    Simpan Draft
                </button>
                <?php if ($canSend): ?>
                    <button type="submit" onclick="document.getElementById('pn_action_mode').value='send'"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm cursor-pointer">
                        🚀 Simpan &amp; Kirim Sekarang
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Modal Daftar Pembaca -->
<div id="notifReadersModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Status Pembaca Notifikasi</h3>
                <p id="readersModalSub" class="text-xs text-slate-500 truncate max-w-xs"></p>
            </div>
            <button type="button" onclick="document.getElementById('notifReadersModal').classList.add('hidden'); document.getElementById('notifReadersModal').classList.remove('flex');"
                    class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-500 cursor-pointer">✕</button>
        </div>
        <div id="readersModalBody" class="p-5 max-h-[65vh] overflow-y-auto text-xs space-y-2"></div>
    </div>
</div>

<script>
const PN_CAN_ALL_UNITS = <?= !empty($unitScope['can_all']) ? 'true' : 'false' ?>;

function toggleNotifTypeFields() {
    const isPopup = document.getElementById('pn_tipe_popup').checked;
    const wrap = document.getElementById('pn_popup_interval_wrap');
    wrap.classList.toggle('hidden', !isPopup);
}

function toggleRoleChecklist() {
    const isRole = document.getElementById('pn_jenis_akun').value === 'role';
    document.getElementById('pn_role_wrap').classList.toggle('hidden', !isRole);
}

function toggleAllUnitsCheckbox() {
    const allCb = document.getElementById('pn_unit_all');
    if (!allCb) return;
    if (allCb.checked) {
        document.querySelectorAll('.pn-unit-cb').forEach(cb => { cb.checked = false; });
    }
}

function onSingleUnitChanged() {
    const allCb = document.getElementById('pn_unit_all');
    if (!allCb) return;
    const anyChecked = Array.from(document.querySelectorAll('.pn-unit-cb')).some(cb => cb.checked);
    if (anyChecked) {
        allCb.checked = false;
    }
}

function openNotifFormModal(data = null) {
    const modal = document.getElementById('notifFormModal');
    const form = document.getElementById('notifFormEl');
    const titleEl = document.getElementById('notifModalTitle');

    document.querySelectorAll('.pn-role-cb').forEach(cb => { cb.checked = false; });
    document.querySelectorAll('.pn-unit-cb').forEach(cb => { cb.checked = !PN_CAN_ALL_UNITS; });

    if (data && data.id) {
        titleEl.textContent = 'Edit Notifikasi';
        form.action = '<?= url('pusat-notifikasi/update/') ?>' + data.id;
        document.getElementById('pn_judul').value = data.judul || '';
        document.getElementById('pn_pesan').value = data.pesan || '';
        document.getElementById('pn_link_url').value = data.link_url || '';
        document.getElementById('pn_link_label').value = data.link_label || 'Buka Tautan';
        document.getElementById('pn_prioritas').value = data.prioritas || 'info';
        document.getElementById('pn_platform').value = data.target_platform || 'both';
        document.getElementById('pn_jenis_akun').value = data.target_jenis_akun || 'all';
        document.getElementById('pn_interval').value = data.popup_interval_menit || 5;
        document.getElementById('pn_pakai_suara').checked = Number(data.pakai_suara) === 1;
        document.getElementById('pn_tipe_push').checked = (data.tipe !== 'popup');
        document.getElementById('pn_tipe_popup').checked = (data.tipe === 'popup');
        document.getElementById('pn_berakhir_at').value = data.berakhir_at ? String(data.berakhir_at).replace(' ', 'T').slice(0, 16) : '';

        if (data.target_role_ids) {
            const rArr = String(data.target_role_ids).split(',').map(s => s.trim());
            document.querySelectorAll('.pn-role-cb').forEach(cb => { cb.checked = rArr.includes(cb.value); });
        }

        const rawU = String(data.target_units || 'ALL').toUpperCase();
        const allCb = document.getElementById('pn_unit_all');
        if (rawU === 'ALL' && allCb) {
            allCb.checked = true;
            document.querySelectorAll('.pn-unit-cb').forEach(cb => { cb.checked = false; });
        } else {
            if (allCb) allCb.checked = false;
            const uArr = rawU.split(',').map(s => s.trim());
            document.querySelectorAll('.pn-unit-cb').forEach(cb => { cb.checked = uArr.includes(cb.value.toUpperCase()); });
        }
    } else {
        titleEl.textContent = 'Buat Notifikasi Baru';
        form.action = '<?= url('pusat-notifikasi/store') ?>';
        document.getElementById('pn_judul').value = '';
        document.getElementById('pn_pesan').value = '';
        document.getElementById('pn_link_url').value = '';
        document.getElementById('pn_link_label').value = 'Buka Tautan';
        document.getElementById('pn_prioritas').value = 'info';
        document.getElementById('pn_platform').value = 'both';
        document.getElementById('pn_jenis_akun').value = 'all';
        document.getElementById('pn_interval').value = 5;
        document.getElementById('pn_pakai_suara').checked = true;
        document.getElementById('pn_tipe_push').checked = true;
        document.getElementById('pn_tipe_popup').checked = false;
        document.getElementById('pn_berakhir_at').value = '';
        const allCb = document.getElementById('pn_unit_all');
        if (allCb) allCb.checked = true;
    }

    toggleNotifTypeFields();
    toggleRoleChecklist();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeNotifFormModal() {
    const modal = document.getElementById('notifFormModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function openReadersModal(id) {
    const modal = document.getElementById('notifReadersModal');
    const body = document.getElementById('readersModalBody');
    const sub = document.getElementById('readersModalSub');
    sub.textContent = 'Memuat data...';
    body.innerHTML = '<div class="py-6 text-center text-slate-400">Memuat daftar pembaca...</div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    try {
        const res = await fetch('<?= url('pusat-notifikasi/readers/') ?>' + id, { credentials: 'same-origin' });
        const json = await res.json();
        if (!json.success) {
            body.innerHTML = '<div class="py-6 text-center text-rose-600 font-bold">' + (json.message || 'Gagal memuat') + '</div>';
            return;
        }
        sub.textContent = json.judul || '';
        const rows = json.readers || [];
        if (!rows.length) {
            body.innerHTML = '<div class="py-6 text-center text-slate-400">Belum ada akun yang membaca notifikasi ini.</div>';
            return;
        }
        body.innerHTML = rows.map(r => `
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="font-bold text-slate-900 truncate">${escapeHtmlPn(r.full_name || r.username)}</div>
                    <div class="text-[11px] text-slate-500">${escapeHtmlPn(r.platform === 'mobile' ? 'Portal Mobile' : 'Portal Web')} &bull; Dibaca ${escapeHtmlPn(r.read_at || '-')}</div>
                </div>
                ${r.clicked_at ? '<span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 font-bold shrink-0">Klik Link</span>' : '<span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 font-bold shrink-0">Dibaca</span>'}
            </div>
        `).join('');
    } catch (e) {
        body.innerHTML = '<div class="py-6 text-center text-rose-600">Terjadi kesalahan koneksi.</div>';
    }
}

function escapeHtmlPn(str) {
    return String(str || '').replace(/[&<>"']/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s]));
}
</script>
