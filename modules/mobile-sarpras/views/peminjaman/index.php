<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <div class="mb-4 sticky top-0 bg-slate-50 pt-2 pb-3 z-10 space-y-3">
        <h2 class="text-xl font-bold text-slate-800">Data Peminjaman</h2>
        <p class="text-xs text-slate-500 font-medium">Menampilkan <?= count($peminjaman) ?> data</p>
    </div>

    <!-- Data List -->
    <div class="space-y-3">
        <?php if (empty($peminjaman)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm mt-10">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="arrow-right-left" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Belum Ada Data</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan klik tombol + untuk menambah peminjaman baru.</p>
            </div>
        <?php else: ?>
            <?php foreach ($peminjaman as $p): ?>
                <?php 
                    $isOverdue = false;
                    $statusColor = 'slate';
                    $statusText = $p['status'];
                    
                    if ($p['status'] == 'Dipinjam') {
                        $statusColor = 'amber';
                        $tglRencana = strtotime($p['estimasi_kembali']);
                        $now = strtotime(date('Y-m-d'));
                        if ($tglRencana < $now) {
                            $isOverdue = true;
                            $statusColor = 'rose';
                            $statusText = 'Terlambat';
                        }
                    } elseif ($p['status'] == 'Kembali' || $p['status'] == 'Dikembalikan') {
                        $statusColor = 'emerald';
                        $statusText = 'Kembali';
                    }
                ?>
                <div class="block bg-white p-4 rounded-3xl shadow-sm border <?= $isOverdue ? 'border-rose-200' : 'border-slate-100' ?>">
                    <div class="flex gap-4">
                        <div class="w-16 h-16 bg-<?= $statusColor ?>-50 rounded-2xl flex items-center justify-center flex-shrink-0 border border-<?= $statusColor ?>-100">
                            <i data-lucide="arrow-right-left" class="w-7 h-7 text-<?= $statusColor ?>-500"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="font-bold text-slate-800 text-sm leading-tight truncate"><?= e($p['nama_barang']) ?></h3>
                            <p class="text-xs font-semibold text-blue-600 mt-0.5 truncate"><?= e($p['nama_peminjam']) ?></p>
                            
                            <div class="flex flex-col gap-1 mt-2 text-[10px] text-slate-500">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <span>Pinjam: <?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></span>
                                </div>
                                <div class="flex items-center gap-1 <?= $isOverdue ? 'text-rose-600 font-bold' : '' ?>">
                                    <i data-lucide="calendar-clock" class="w-3 h-3"></i>
                                    <span>Tenggat: <?= date('d M Y', strtotime($p['estimasi_kembali'])) ?></span>
                                </div>
                                <?php if (($p['status'] == 'Kembali' || $p['status'] == 'Dikembalikan') && !empty($p['tanggal_kembali'])): ?>
                                <div class="flex items-center gap-1 text-emerald-600 font-bold">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    <span>Kembali: <?= date('d M Y', strtotime($p['tanggal_kembali'])) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-<?= $statusColor ?>-50 text-<?= $statusColor ?>-700 border-<?= $statusColor ?>-100/50">
                                    <?= e($statusText) ?>
                                </span>
                                <?php if ($isOverdue): ?>
                                    <?php 
                                        $diff = $now - $tglRencana;
                                        $days = floor($diff / (60 * 60 * 24));
                                    ?>
                                    <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-rose-50 text-rose-700 border-rose-100">
                                        Lewat <?= $days ?> Hari
                                    </span>
                                <?php endif; ?>
                                
                                <button onclick="openModalEditPinjamMobile(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama_barang']), ENT_QUOTES) ?>', '<?= date('Y-m-d', strtotime($p['tanggal_pinjam'])) ?>', '<?= date('Y-m-d', strtotime($p['estimasi_kembali'])) ?>', '<?= htmlspecialchars(addslashes($p['status']), ENT_QUOTES) ?>')" class="px-2 py-1 rounded-lg border text-[10px] font-bold bg-slate-50 text-slate-700 border-slate-200 ml-auto flex items-center gap-1 active:bg-slate-100">
                                    <i data-lucide="edit" class="w-3 h-3"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Action Button (FAB) for adding new item -->
<div class="fixed bottom-28 right-5 z-50">
    <a href="<?= url('mobile-sarpras/peminjaman/tambah') ?>" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-blue-500/50 shadow-xl flex items-center justify-center hover:bg-blue-700 active:scale-95 transition-all border-4 border-slate-50">
        <i data-lucide="plus" class="w-7 h-7"></i>
    </a>
</div>

<!-- Modal Edit Peminjaman (Mobile) -->
<div id="modal-edit-pinjam" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md transform translate-y-full sm:translate-y-0 sm:scale-95 transition-all duration-300">
        <div class="p-5">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-5 sm:hidden"></div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">Edit Peminjaman</h3>
                <button type="button" onclick="closeModalEditPinjamMobile()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            
            <form id="form-edit-pinjam-mobile" method="POST" class="space-y-4">
                <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras/peminjaman') ?>">
                
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-xs text-blue-600 font-medium mb-1">Barang Dipinjam</p>
                    <p id="edit-pinjam-barang-nama" class="font-bold text-blue-900 text-sm"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" id="edit-tanggal-pinjam" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Kembali</label>
                        <input type="date" name="tanggal_rencana_kembali" id="edit-estimasi-kembali" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" id="edit-status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 font-bold">
                        <option value="Dipinjam">Dipinjam</option>
                        <option value="Dikembalikan">Dikembalikan</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModalEditPinjamMobile(id, nama, tglPinjam, estKembali, status) {
    document.getElementById('form-edit-pinjam-mobile').action = '<?= url("mobile-sarpras/peminjaman/update/") ?>' + id;
    document.getElementById('edit-pinjam-barang-nama').textContent = nama;
    document.getElementById('edit-tanggal-pinjam').value = tglPinjam;
    document.getElementById('edit-estimasi-kembali').value = estKembali;
    document.getElementById('edit-status').value = status;
    
    const m = document.getElementById('modal-edit-pinjam');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('translate-y-full');
    if(window.innerWidth >= 640) m.firstElementChild.classList.remove('scale-95');
}

function closeModalEditPinjamMobile() {
    const m = document.getElementById('modal-edit-pinjam');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('translate-y-full');
    if(window.innerWidth >= 640) m.firstElementChild.classList.add('scale-95');
}
</script>

