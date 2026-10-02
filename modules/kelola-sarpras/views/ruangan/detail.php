<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-5">
            <svg class="w-32 h-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6.75h1.5m-1.5 3h1.5m-1.5 3h1.5" />
            </svg>
        </div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a href="<?= url('kelola-sarpras/ruangan') ?>" class="text-slate-400 hover:text-primary-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight"><?= e($ruangan['nama_ruangan']) ?></h2>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-primary-50 text-primary-700 border border-primary-100"><?= e($ruangan['jenis_ruangan']) ?></span>
                </div>
                <p class="text-sm text-slate-500 font-medium">
                    Gedung: <span class="text-slate-700"><?= e($ruangan['nama_bangunan'] ?? $ruangan['lokasi_gedung']) ?></span> &bull; 
                    Lantai: <span class="text-slate-700"><?= (int)$ruangan['lantai'] ?></span> &bull;
                    Penanggung Jawab: <span class="text-slate-700"><?= e($ruangan['penanggung_jawab'] ?? '-') ?></span>
                </p>
            </div>
            
            <button onclick="openModalDistribusi()" class="flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-2xl transition-all shadow-lg shadow-primary-500/30 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Aset ke Ruangan
            </button>
        </div>
    </div>

    <!-- Table Aset -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Daftar Aset di Ruangan Ini</h3>
            <div class="text-xs font-bold text-slate-500 bg-white px-3 py-1 rounded-lg border border-slate-200">
                Total Aset: <?= count($distribusi) ?>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Barang</th>
                        <th class="py-3.5 px-4">Kode & Seri</th>
                        <th class="py-3.5 px-4 text-center">Jumlah</th>
                        <th class="py-3.5 px-4 text-center">Kondisi</th>
                        <th class="py-3.5 px-4">Keterangan</th>
                        <th class="py-3.5 px-4 text-center">Tanggal Distribusi</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($distribusi)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-3">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                                </div>
                                <div class="text-slate-500 font-medium">Belum ada aset/barang di ruangan ini.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($distribusi as $d): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= e($d['nama_barang']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= e($d['merk_model'] ?: '-') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-primary-700"><?= e($d['kode_barang']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-bold text-slate-800 text-sm"><?= $d['jumlah'] ?></span>
                                    <span class="text-[10px] text-slate-400 block"><?= e($d['satuan']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (($d['kondisi'] ?? 'Baik') === 'Baik'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Baik</span>
                                    <?php elseif (($d['kondisi'] ?? '') === 'Rusak Ringan'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Rusak Ringan</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Rusak Berat</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <?= e($d['keterangan'] ?: '-') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-500">
                                    <?= date('d/m/Y', strtotime($d['tanggal_distribusi'])) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="<?= url('kelola-sarpras/distribusi/delete/' . $d['id']) ?>" method="POST" onsubmit="return confirm('Tarik/Hapus <?= e($d['nama_barang']) ?> dari ruangan ini?')">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Tarik dari Ruangan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Distribusi Barang -->
<div id="modal-distribusi" class="fixed inset-0 z-[100] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModalDistribusi()"></div>
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative z-10 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Tambah Barang ke Ruangan</h3>
            <button onclick="closeModalDistribusi()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors text-xl font-bold">&times;</button>
        </div>
        <form action="<?= url('kelola-sarpras/distribusi/store') ?>" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <?= CSRF::field() ?>
            <input type="hidden" name="ruangan_id" value="<?= $ruangan['id'] ?>">
            
            <div id="distribusi-items-container" class="p-6 overflow-y-auto space-y-6 flex-1 bg-slate-50/50">
                <!-- Item Row Template -->
                <div class="distribusi-item bg-white p-4 rounded-2xl border border-slate-200 relative shadow-sm">
                    <button type="button" class="btn-remove-item absolute -top-3 -right-3 w-8 h-8 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm hover:bg-rose-500 hover:text-white transition-colors hidden">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <div class="space-y-4 text-xs">
                        <div class="relative custom-dropdown-container">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pilih Aset/Barang <span class="text-rose-500">*</span></label>
                            
                            <input type="hidden" name="barang_id[]" class="hidden-barang-id" required>
                            
                            <button type="button" class="dropdown-trigger w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none text-left flex justify-between items-center transition-colors">
                                <span class="dropdown-selected-text truncate">-- Pilih Barang --</span>
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            
                            <div class="dropdown-panel absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl hidden flex-col overflow-hidden">
                                <div class="p-2 border-b border-slate-100 bg-slate-50">
                                    <input type="text" placeholder="Ketik nama / kode..." autocomplete="off" class="search-barang-input w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 focus:border-primary-500 outline-none">
                                </div>
                                <div class="max-h-48 overflow-y-auto">
                                    <?php foreach ($barangTersedia as $b): $sisa = $b['jumlah'] - ($b['dipakai'] ?? 0); ?>
                                        <div class="option-barang px-3 py-2.5 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0" 
                                            data-id="<?= $b['id'] ?>" 
                                            data-search="<?= strtolower($b['nama_barang'] . ' ' . $b['kode_barang']) ?>"
                                            data-sisa="<?= $sisa ?>"
                                            data-satuan="<?= e($b['satuan']) ?>">
                                            <div class="font-bold text-slate-800 text-sm"><?= e($b['nama_barang']) ?></div>
                                            <div class="text-[10px] text-slate-500 flex justify-between mt-0.5">
                                                <span><?= e($b['kode_barang']) ?></span>
                                                <span class="text-primary-600 font-bold bg-primary-50 px-1.5 py-0.5 rounded">Sisa: <?= $sisa ?> <?= e($b['satuan']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($barangTersedia)): ?>
                                        <div class="px-4 py-3 text-xs text-rose-500 text-center">Tidak ada stok barang yang tersedia.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jumlah <span class="text-rose-500">*</span></label>
                                <input type="number" name="jumlah[]" required min="1" value="1" class="distribusi-jumlah w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                                <p class="text-[10px] text-slate-400 mt-1 distribusi-info-sisa"></p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kondisi <span class="text-rose-500">*</span></label>
                                <select name="kondisi[]" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan / Posisi di Ruangan</label>
                            <input type="text" name="keterangan[]" placeholder="Contoh: Meja Guru, Pojok Kanan Depan..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-white z-10">
                <button type="button" onclick="tambahBarisDistribusi()" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100 text-sm font-bold rounded-xl transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg> Tambah Baris
                </button>
                <div class="flex gap-2">
                    <button type="button" onclick="closeModalDistribusi()" class="px-5 py-2.5 bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-bold rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white hover:bg-primary-700 text-sm font-bold rounded-xl transition-all shadow-lg shadow-primary-500/30">Simpan Distribusi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        initCustomDropdowns();
    });

    function initCustomDropdowns() {
        // Event delegation for all dropdowns inside modal
        const container = document.getElementById('distribusi-items-container');
        if (!container) return;

        container.addEventListener('click', function(e) {
            // Dropdown Trigger Click
            const trigger = e.target.closest('.dropdown-trigger');
            if (trigger) {
                const parent = trigger.closest('.custom-dropdown-container');
                const dropdown = parent.querySelector('.dropdown-panel');
                const inputSearch = parent.querySelector('.search-barang-input');
                const options = parent.querySelectorAll('.option-barang');

                // Close other dropdowns
                document.querySelectorAll('.dropdown-panel').forEach(p => {
                    if (p !== dropdown) p.classList.add('hidden');
                });

                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    inputSearch.focus();
                    inputSearch.value = '';
                    options.forEach(opt => opt.style.display = 'block');
                }
                return;
            }

            // Option Click
            const option = e.target.closest('.option-barang');
            if (option) {
                const parent = option.closest('.custom-dropdown-container');
                const itemRow = option.closest('.distribusi-item');
                
                const id = option.getAttribute('data-id');
                const name = option.querySelector('.font-bold').innerText;
                const sisa = option.getAttribute('data-sisa');
                const satuan = option.getAttribute('data-satuan');

                parent.querySelector('.dropdown-selected-text').innerText = name;
                parent.querySelector('.hidden-barang-id').value = id;
                
                const jumlahInput = itemRow.querySelector('.distribusi-jumlah');
                const sisaInfo = itemRow.querySelector('.distribusi-info-sisa');
                
                jumlahInput.max = sisa;
                jumlahInput.value = 1;
                sisaInfo.innerText = `Maksimal: ${sisa} ${satuan}`;
                
                parent.querySelector('.dropdown-panel').classList.add('hidden');
            }
        });

        // Search Input Filter
        container.addEventListener('input', function(e) {
            if (e.target.classList.contains('search-barang-input')) {
                const val = e.target.value.toLowerCase();
                const parent = e.target.closest('.custom-dropdown-container');
                const options = parent.querySelectorAll('.option-barang');
                options.forEach(opt => {
                    if (opt.getAttribute('data-search').includes(val)) {
                        opt.style.display = 'block';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            }
        });

        // Remove Row
        container.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove-item');
            if (btn) {
                const row = btn.closest('.distribusi-item');
                row.remove();
                updateRemoveButtons();
            }
        });

        // Hide dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.custom-dropdown-container')) {
                document.querySelectorAll('.dropdown-panel').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });
    }

    function tambahBarisDistribusi() {
        const container = document.getElementById('distribusi-items-container');
        const rows = container.querySelectorAll('.distribusi-item');
        if (rows.length >= 10) {
            alert('Maksimal penambahan 10 baris dalam satu waktu.');
            return;
        }

        const firstRow = rows[0];
        const newRow = firstRow.cloneNode(true);
        
        // Reset values
        newRow.querySelector('.hidden-barang-id').value = '';
        newRow.querySelector('.dropdown-selected-text').innerText = '-- Pilih Barang --';
        newRow.querySelector('.distribusi-jumlah').value = '1';
        newRow.querySelector('.distribusi-jumlah').removeAttribute('max');
        newRow.querySelector('.distribusi-info-sisa').innerText = '';
        newRow.querySelector('select[name="kondisi[]"]').value = 'Baik';
        newRow.querySelector('input[name="keterangan[]"]').value = '';
        
        // Hide dropdown panel if it was open
        newRow.querySelector('.dropdown-panel').classList.add('hidden');
        newRow.querySelector('.search-barang-input').value = '';
        newRow.querySelectorAll('.option-barang').forEach(opt => opt.style.display = 'block');
        
        container.appendChild(newRow);
        
        updateRemoveButtons();
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.distribusi-item');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.btn-remove-item');
            if (rows.length > 1) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    }

    function openModalDistribusi() {
        const modal = document.getElementById('modal-distribusi');
        modal.classList.remove('hidden');
    }

    function closeModalDistribusi() {
        const modal = document.getElementById('modal-distribusi');
        modal.classList.add('hidden');
    }
</script>



