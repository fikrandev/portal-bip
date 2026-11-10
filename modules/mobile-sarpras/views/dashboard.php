<div class="px-4 pt-6 space-y-6">
    <!-- User Greeting Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                Assalamu'alaikum, <?= htmlspecialchars(explode(' ', $nama ?? 'User')[0]) ?> 👋
            </h2>
            <p class="text-sm text-slate-500 font-medium mt-1">
                <?= htmlspecialchars($jabatan ?? 'Administrator') ?>
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?= url('mobile-sarpras/logout') ?>" onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi Sarpras Mobile?')" title="Keluar / Ganti Akun" class="w-10 h-10 bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-100 rounded-full flex items-center justify-center transition-colors shadow-xs active:scale-95" aria-label="Keluar">
                <i data-lucide="log-out" class="w-4 h-4"></i>
            </a>
            <div class="w-12 h-12 bg-blue-100 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-blue-600 font-bold text-lg shrink-0">
                <?= strtoupper(substr($nama ?? 'U', 0, 1)) ?>
            </div>
        </div>
    </div>
 
    <!-- PWA Install Banner Card (Hidden if already standalone) -->
    <div id="pwa-dashboard-card" class="pwa-install-trigger bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-4 text-white shadow-lg shadow-blue-500/20 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-white/20 rounded-2xl flex items-center justify-center shrink-0 backdrop-blur-xs">
                <i data-lucide="download" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm leading-tight">Install Sarpras Mobile</h4>
                <p class="text-xs text-blue-100 mt-0.5">Pasang aplikasi di layar utama HP</p>
            </div>
        </div>
        <button type="button" onclick="triggerPwaInstall()" class="px-4 py-2 bg-white text-blue-600 rounded-2xl text-xs font-bold shadow hover:bg-blue-50 active:scale-95 transition-all shrink-0">
            Install
        </button>
    </div>

    <!-- Card Pintasan Utama: Input Manual Inventaris Per Ruangan / Kelas -->
    <div class="bg-emerald-600 rounded-3xl p-4 text-white shadow-lg shadow-emerald-600/20 space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                    <i data-lucide="package-plus" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-100 block">Fitur Cepat</span>
                    <h4 class="font-bold text-sm leading-snug">Tambah Inventaris Per Ruangan</h4>
                    <p class="text-[11px] text-emerald-100 mt-0.5">Input manual dari kelas/ruangan &amp; otomatis masuk Master Inventaris</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-1">
            <button type="button" onclick="openPickerRuanganDashboard()" class="py-2.5 px-3 bg-white text-emerald-700 rounded-2xl text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Pilih Ruang &amp; Input</span>
            </button>
            <a href="<?= url('mobile-sarpras/ruangan') ?>" class="py-2.5 px-3 bg-emerald-700/70 hover:bg-emerald-700 text-white border border-white/20 rounded-2xl text-xs font-bold flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                <i data-lucide="door-open" class="w-4 h-4"></i>
                <span>Daftar Ruangan</span>
            </a>
        </div>
    </div>

    <!-- Title -->
    <div class="pt-1">
        <h3 class="text-lg font-bold text-slate-800 tracking-tight">Dashboard Sarpras</h3>
        <p class="text-xs text-slate-500 mt-1">Ringkasan aset dan pergerakan sarpras</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 gap-4">
        <a href="<?= url('mobile-sarpras/inventaris') ?>" class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col items-center text-center active:scale-95 transition-transform">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-3">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
            <span class="text-2xl font-bold text-slate-800"><?= number_format($statistik['total_barang'] ?? 0, 0, ',', '.') ?></span>
            <span class="text-xs text-slate-500 font-medium mt-1">Inventaris (Unit)</span>
        </a>
        
        <a href="<?= url('mobile-sarpras/tanah') ?>" class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col items-center text-center active:scale-95 transition-transform">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mb-3">
                <i data-lucide="map-pin" class="w-6 h-6"></i>
            </div>
            <span class="text-2xl font-bold text-slate-800"><?= number_format($statistik['total_tanah'] ?? 0, 0, ',', '.') ?></span>
            <span class="text-xs text-slate-500 font-medium mt-1">Tanah & Lahan</span>
        </a>

        <a href="<?= url('mobile-sarpras/bangunan') ?>" class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col items-center text-center active:scale-95 transition-transform">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mb-3">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
            <span class="text-2xl font-bold text-slate-800"><?= number_format($statistik['total_bangunan'] ?? 0, 0, ',', '.') ?></span>
            <span class="text-xs text-slate-500 font-medium mt-1">Gedung / Bangunan</span>
        </a>

        <a href="<?= url('mobile-sarpras/ruangan') ?>" class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col items-center text-center active:scale-95 transition-transform">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-3">
                <i data-lucide="door-open" class="w-6 h-6"></i>
            </div>
            <span class="text-2xl font-bold text-slate-800"><?= number_format($statistik['total_ruangan'] ?? 0, 0, ',', '.') ?></span>
            <span class="text-xs text-slate-500 font-medium mt-1">Total Ruangan</span>
        </a>
    </div>

    <!-- Quick Actions -->
    <div>
        <h3 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wider">Aksi Cepat</h3>
        <div class="grid grid-cols-4 gap-2">
            <a href="<?= url('mobile-sarpras/inventaris') ?>" class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center justify-center text-blue-600 active:bg-blue-50 transition">
                    <i data-lucide="box" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] text-center font-medium text-slate-600">Inventaris</span>
            </a>
            <a href="<?= url('mobile-sarpras/tanah') ?>" class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center justify-center text-emerald-600 active:bg-emerald-50 transition">
                    <i data-lucide="map" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] text-center font-medium text-slate-600">Tanah</span>
            </a>
            <a href="<?= url('mobile-sarpras/bangunan') ?>" class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center justify-center text-amber-600 active:bg-amber-50 transition">
                    <i data-lucide="building" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] text-center font-medium text-slate-600">Gedung</span>
            </a>
            <a href="<?= url('mobile-sarpras/ruangan') ?>" class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center justify-center text-purple-600 active:bg-purple-50 transition">
                    <i data-lucide="door-open" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] text-center font-medium text-slate-600">Ruangan</span>
            </a>
        </div>
    </div>

    <!-- Recent Activities or Maintenance -->
    <div>
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Pintasan Ruangan / Kelas</h3>
            <a href="<?= url('mobile-sarpras/ruangan') ?>" class="text-xs text-blue-600 font-semibold">Semua Ruangan &rarr;</a>
        </div>
        <?php if (empty($ruanganList)): ?>
            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm text-center">
                <p class="text-xs text-slate-500">Belum ada data ruangan terdaftar.</p>
            </div>
        <?php else: ?>
            <div class="space-y-2.5">
                <?php foreach (array_slice($ruanganList, 0, 5) as $r): ?>
                    <div class="bg-white rounded-2xl p-3.5 border border-slate-100 shadow-2xs flex items-center justify-between gap-3">
                        <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id']) ?>" class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-slate-800 text-xs truncate"><?= e($r['nama_ruangan']) ?></span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-700"><?= e($r['unit'] ?? 'SD') ?></span>
                            </div>
                            <p class="text-[10px] text-slate-400 truncate mt-0.5">
                                <?= e($r['nama_bangunan'] ?? $r['lokasi_gedung'] ?? '-') ?> &bull; <?= (int)($r['total_barang'] ?? 0) ?> Aset (<?= (int)($r['total_qty'] ?? 0) ?> Unit)
                            </p>
                        </a>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id'] . '?action=input-manual') ?>" class="px-2.5 py-1.5 rounded-xl bg-emerald-600 text-white text-[10px] font-bold flex items-center gap-1 shadow-2xs active:scale-95">
                                <i data-lucide="plus" class="w-3 h-3"></i>
                                <span>Inventaris</span>
                            </a>
                            <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id']) ?>" class="p-1.5 rounded-xl bg-slate-100 text-slate-600 active:scale-95" title="Detail Ruangan">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Pilih Ruangan untuk Input Inventaris Baru -->
<div id="modalPickerRuanganDashboard" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closePickerRuanganDashboard()"></div>
    <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl relative z-10 max-h-[82vh] flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Pilih Ruangan / Kelas Tujuan</h3>
                <p class="text-[11px] text-slate-500">Barang yang diinput otomatis masuk Inventaris &amp; Ruangan ini</p>
            </div>
            <button type="button" onclick="closePickerRuanganDashboard()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">&times;</button>
        </div>
        <div class="p-3 border-b border-slate-100 bg-slate-50">
            <input type="text" id="searchPickerRuanganDash" placeholder="Cari nama kelas / ruangan..." class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-emerald-500">
        </div>
        <div class="p-3 overflow-y-auto space-y-2 flex-1">
            <?php if (!empty($ruanganList)): ?>
                <?php foreach ($ruanganList as $r): ?>
                    <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id'] . '?action=input-manual') ?>" 
                       class="picker-ruangan-dash-item flex items-center justify-between p-3 rounded-2xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/40 transition-all"
                       data-search="<?= strtolower(e(($r['nama_ruangan'] ?? '') . ' ' . ($r['nama_bangunan'] ?? '') . ' ' . ($r['unit'] ?? ''))) ?>">
                        <div class="min-w-0 pr-2">
                            <div class="font-bold text-slate-800 text-xs truncate"><?= e($r['nama_ruangan']) ?></div>
                            <div class="text-[10px] text-slate-400 truncate"><?= e($r['nama_bangunan'] ?? $r['lokasi_gedung'] ?? '-') ?> &bull; Unit: <?= e($r['unit'] ?? 'SD') ?></div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-emerald-600 text-white text-[10px] font-bold shrink-0">+ Input Barang</span>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-center text-xs text-slate-400">Belum ada data ruangan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openPickerRuanganDashboard() {
    document.getElementById('modalPickerRuanganDashboard')?.classList.remove('hidden');
}
function closePickerRuanganDashboard() {
    document.getElementById('modalPickerRuanganDashboard')?.classList.add('hidden');
}
document.getElementById('searchPickerRuanganDash')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.picker-ruangan-dash-item').forEach(el => {
        const match = !q || (el.getAttribute('data-search') || '').includes(q);
        el.style.display = match ? 'flex' : 'none';
    });
});
</script>
