<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('mobile-sarpras/peminjaman') ?>" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-slate-100 text-slate-500 active:scale-95 transition-transform">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-xl font-bold text-slate-800">Form Peminjaman</h2>
    </div>

    <form action="<?= url('mobile-sarpras/peminjaman/store') ?>" method="POST" class="space-y-4">
        <?= CSRF::field() ?>

        <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-4">
            
            <!-- Peminjam (Searchable Bottom Sheet) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Peminjam <span class="text-rose-500">*</span></label>
                <input type="hidden" name="peminjam" id="peminjam_val" required>
                <div class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 flex justify-between items-center cursor-pointer active:scale-[0.98] transition-transform" onclick="openSelectModal('modal_peminjam')">
                    <span id="peminjam_display" class="truncate">Pilih Peminjam...</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 flex-shrink-0"></i>
                </div>
            </div>

            <!-- Inventaris (Searchable Bottom Sheet) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Barang (Inventaris) <span class="text-rose-500">*</span></label>
                <input type="hidden" name="barang_id" id="barang_val" required>
                <div class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 flex justify-between items-center cursor-pointer active:scale-[0.98] transition-transform" onclick="openSelectModal('modal_barang')">
                    <span id="barang_display" class="truncate">Pilih Barang...</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 flex-shrink-0"></i>
                </div>
            </div>

            <!-- Tanggal Pinjam -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Peminjaman <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_pinjam" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" value="<?= date('Y-m-d') ?>">
            </div>

            <!-- Tanggal Kembali -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tgl Rencana Kembali <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_rencana_kembali" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Keperluan <span class="text-rose-500">*</span></label>
                <textarea name="keterangan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="Dipinjam untuk keperluan apa / ke ruangan mana?"></textarea>
            </div>

            <button type="submit" class="w-full py-3 mt-4 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 active:scale-95 transition-transform">
                Simpan Peminjaman
            </button>
        </div>
    </form>
</div>

<!-- Modal Peminjam -->
<div id="modal_peminjam" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modal_peminjam_backdrop" onclick="closeSelectModal('modal_peminjam')"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl transform translate-y-full transition-transform duration-300 flex flex-col max-h-[85vh] shadow-2xl" id="modal_peminjam_sheet">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-lg">Pilih Peminjam</h3>
            <button type="button" onclick="closeSelectModal('modal_peminjam')" class="p-2 bg-slate-100 rounded-full text-slate-600 active:scale-90 transition-transform">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-4 border-b border-slate-100">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" id="search_peminjam_input" class="w-full pl-9 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="Ketik nama untuk mencari...">
            </div>
        </div>
        <div class="overflow-y-auto flex-1 p-2 pb-8" id="list_peminjam">
            <?php foreach ($pegawaiList as $p): ?>
                <?php $val = e($p['nama'] . ($p['gelar'] ? ', ' . $p['gelar'] : '')); ?>
                <div class="p-3 border-b border-slate-50 text-sm font-medium text-slate-700 active:bg-blue-50 active:text-blue-700 cursor-pointer rounded-xl transition-colors list-item-peminjam" 
                     data-search="<?= strtolower($val) ?>" 
                     onclick="selectOption('peminjam', '<?= htmlspecialchars($val, ENT_QUOTES) ?>', '<?= htmlspecialchars($val, ENT_QUOTES) ?>', 'modal_peminjam')">
                    <?= $val ?>
                </div>
            <?php endforeach; ?>
            <div id="no_result_peminjam" class="p-4 text-center text-sm font-medium text-slate-500 hidden">Data tidak ditemukan</div>
        </div>
    </div>
</div>

<!-- Modal Barang -->
<div id="modal_barang" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modal_barang_backdrop" onclick="closeSelectModal('modal_barang')"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl transform translate-y-full transition-transform duration-300 flex flex-col max-h-[85vh] shadow-2xl" id="modal_barang_sheet">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-lg">Pilih Barang</h3>
            <button type="button" onclick="closeSelectModal('modal_barang')" class="p-2 bg-slate-100 rounded-full text-slate-600 active:scale-90 transition-transform">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-4 border-b border-slate-100">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" id="search_barang_input" class="w-full pl-9 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="Ketik nama atau kode barang...">
            </div>
        </div>
        <div class="overflow-y-auto flex-1 p-2 pb-8" id="list_barang">
            <?php foreach ($barang as $b): ?>
                <?php 
                    $display = e($b['nama_barang']) . ' - ' . e($b['kode_barang']) . ' (Dari: ' . e($b['nama_ruangan'] ?? 'Tanpa Ruangan') . ')';
                    $valId = $b['id'];
                ?>
                <div class="p-3 border-b border-slate-50 text-sm font-medium text-slate-700 active:bg-blue-50 active:text-blue-700 cursor-pointer rounded-xl transition-colors list-item-barang" 
                     data-search="<?= strtolower($display) ?>" 
                     onclick="selectOption('barang', '<?= $valId ?>', '<?= htmlspecialchars($display, ENT_QUOTES) ?>', 'modal_barang')">
                    <?= $display ?>
                </div>
            <?php endforeach; ?>
            <div id="no_result_barang" class="p-4 text-center text-sm font-medium text-slate-500 hidden">Data tidak ditemukan</div>
        </div>
    </div>
</div>

<script>
function openSelectModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.getElementById(modalId + '_backdrop');
    const sheet = document.getElementById(modalId + '_sheet');
    const input = modal.querySelector('input[type="text"]');
    
    modal.classList.remove('hidden');
    // small delay for transition
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        sheet.classList.remove('translate-y-full');
        if(input) input.focus();
    }, 10);
}

function closeSelectModal(modalId) {
    const backdrop = document.getElementById(modalId + '_backdrop');
    const sheet = document.getElementById(modalId + '_sheet');
    
    backdrop.classList.add('opacity-0');
    sheet.classList.add('translate-y-full');
    
    setTimeout(() => {
        document.getElementById(modalId).classList.add('hidden');
    }, 300); // match duration-300
}

function selectOption(prefix, val, display, modalId) {
    document.getElementById(prefix + '_val').value = val;
    const dispEl = document.getElementById(prefix + '_display');
    dispEl.textContent = display;
    dispEl.classList.remove('text-slate-500');
    dispEl.classList.add('text-slate-800', 'font-bold');
    
    closeSelectModal(modalId);
}

function setupModalSearch(inputId, itemClass, noResultId) {
    const input = document.getElementById(inputId);
    const items = document.querySelectorAll('.' + itemClass);
    const noResult = document.getElementById(noResultId);
    
    if(!input) return;
    
    input.addEventListener('input', function() {
        const val = this.value.toLowerCase();
        let count = 0;
        
        items.forEach(item => {
            if (item.getAttribute('data-search').includes(val)) {
                item.style.display = '';
                count++;
            } else {
                item.style.display = 'none';
            }
        });
        
        if (count === 0) {
            noResult.classList.remove('hidden');
        } else {
            noResult.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupModalSearch('search_peminjam_input', 'list-item-peminjam', 'no_result_peminjam');
    setupModalSearch('search_barang_input', 'list-item-barang', 'no_result_barang');
    
    // Lucide icons re-init if needed
    if(typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
