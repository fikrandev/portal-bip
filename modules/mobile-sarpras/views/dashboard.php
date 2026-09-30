<div class="px-4 pt-6 space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Sarpras</h2>
        <p class="text-sm text-slate-500 mt-1">Ringkasan aset dan pergerakan sarpras</p>
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

    <!-- Recent Activities or Maintenance (Placeholder for future) -->
    <div>
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Aktivitas Terbaru</h3>
            <a href="#" class="text-xs text-blue-600 font-semibold">Lihat Semua</a>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm text-center">
            <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-2">
                <i data-lucide="activity" class="w-6 h-6"></i>
            </div>
            <p class="text-xs text-slate-500">Belum ada aktivitas terbaru hari ini.</p>
        </div>
    </div>
</div>
