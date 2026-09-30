<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('mobile-sarpras/maintenance') ?>" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-slate-100 text-slate-500 active:scale-95 transition-transform">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-xl font-bold text-slate-800">Input Perbaikan</h2>
    </div>

    <!-- Mode Selector -->
    <div class="flex p-1 bg-slate-200/50 rounded-2xl mb-6">
        <button type="button" id="btn-mode-scan" class="flex-1 py-2 text-sm font-bold rounded-xl bg-white shadow-sm text-blue-600 transition-all">Scan QR</button>
        <button type="button" id="btn-mode-manual" class="flex-1 py-2 text-sm font-bold rounded-xl text-slate-500 hover:text-slate-700 transition-all">Input Manual</button>
    </div>

    <form action="<?= url('mobile-sarpras/maintenance/store') ?>" method="POST" id="form-maintenance" class="space-y-6">
        <?= CSRF::field() ?>
        
        <!-- HIDDEN BARANG ID -->
        <input type="hidden" name="barang_id" id="val_barang_id" required>

        <!-- SCAN QR SECTION -->
        <div id="section-scan" class="space-y-4">
            <div id="scanner-wrapper" class="w-full aspect-square bg-slate-900 rounded-3xl overflow-hidden shadow-inner border-4 border-slate-200 relative mx-auto max-w-sm">
                <div id="qr-reader" class="w-full h-full object-cover"></div>
                
                <!-- Scanner Overlay -->
                <div class="absolute inset-0 z-10 pointer-events-none flex flex-col">
                    <div class="flex-1 bg-black/40"></div>
                    <div class="h-64 flex">
                        <div class="flex-1 bg-black/40"></div>
                        <div class="w-64 border-2 border-blue-500 relative">
                            <!-- Scanner Animation Line -->
                            <div class="absolute w-full h-1 bg-blue-500 shadow-[0_0_10px_2px_rgba(59,130,246,0.8)] animate-pulse" style="animation: scan 2s infinite linear;"></div>
                        </div>
                        <div class="flex-1 bg-black/40"></div>
                    </div>
                    <div class="flex-1 bg-black/40"></div>
                </div>
            </div>

            <style>
                @keyframes scan {
                    0% { top: 0; }
                    50% { top: 100%; }
                    100% { top: 0; }
                }
            </style>
            <div id="scan-result" class="hidden bg-blue-50 border border-blue-100 rounded-2xl p-4">
                <div class="flex gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 shrink-0">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Barang Ditemukan</p>
                        <h4 id="res-nama-barang" class="font-bold text-slate-800 mt-1">Nama Barang</h4>
                        <p id="res-ruangan" class="text-xs text-slate-500 mt-0.5">Ruangan: -</p>
                    </div>
                </div>
                <button type="button" id="btn-scan-ulang" class="mt-3 w-full py-2 bg-white text-blue-600 border border-blue-200 rounded-xl text-xs font-bold active:bg-blue-50">Scan Ulang</button>
            </div>
        </div>

        <!-- MANUAL INPUT SECTION -->
        <div id="section-manual" class="hidden space-y-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cari Ruangan</label>
                    <select id="sel_ruangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih Ruangan --</option>
                        <?php foreach ($ruanganList as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['nama_ruangan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Inventaris <span class="text-rose-500">*</span></label>
                    <select id="sel_barang" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" disabled>
                        <option value="">-- Pilih Ruangan Dulu --</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- FORM DETAIL PERBAIKAN -->
        <div id="section-detail" class="hidden bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-4">
            <h3 class="font-bold text-slate-800 border-b pb-2 mb-3">Detail Perbaikan</h3>
            
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Status Perbaikan <span class="text-rose-500">*</span></label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                    <option value="Menunggu">Menunggu / Dilaporkan</option>
                    <option value="Dalam Perbaikan">Dalam Perbaikan</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keluhan / Perbaikan <span class="text-rose-500">*</span></label>
                <textarea name="deskripsi_kerusakan" rows="3" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="Apa yang rusak atau perlu diperbaiki?"></textarea>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dilaporkan Oleh <span class="text-rose-500">*</span></label>
                <input type="text" name="dilaporkan_oleh" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="Nama Pelapor / Teknisi">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                <input type="text" name="keterangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="(Opsional)">
            </div>

            <button type="submit" class="w-full py-3 mt-4 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 active:scale-95 transition-transform">
                Simpan Perbaikan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnScan = document.getElementById('btn-mode-scan');
    const btnManual = document.getElementById('btn-mode-manual');
    const secScan = document.getElementById('section-scan');
    const secManual = document.getElementById('section-manual');
    const secDetail = document.getElementById('section-detail');
    
    const valBarangId = document.getElementById('val_barang_id');
    const selRuangan = document.getElementById('sel_ruangan');
    const selBarang = document.getElementById('sel_barang');

    let html5QrcodeScanner = null;

    // --- Mode Switching ---
    btnScan.addEventListener('click', () => {
        btnScan.className = 'flex-1 py-2 text-sm font-bold rounded-xl bg-white shadow-sm text-blue-600 transition-all';
        btnManual.className = 'flex-1 py-2 text-sm font-bold rounded-xl text-slate-500 hover:text-slate-700 transition-all';
        secScan.classList.remove('hidden');
        secManual.classList.add('hidden');
        resetSelection();
        startScanner();
    });

    btnManual.addEventListener('click', () => {
        btnManual.className = 'flex-1 py-2 text-sm font-bold rounded-xl bg-white shadow-sm text-blue-600 transition-all';
        btnScan.className = 'flex-1 py-2 text-sm font-bold rounded-xl text-slate-500 hover:text-slate-700 transition-all';
        secManual.classList.remove('hidden');
        secScan.classList.add('hidden');
        resetSelection();
        stopScanner();
    });

    function resetSelection() {
        valBarangId.value = '';
        secDetail.classList.add('hidden');
        selBarang.innerHTML = '<option value="">-- Pilih Ruangan Dulu --</option>';
        selBarang.disabled = true;
        selRuangan.value = '';
        document.getElementById('scan-result').classList.add('hidden');
        document.getElementById('scanner-wrapper').classList.remove('hidden');
    }

    function showDetailForm(id) {
        valBarangId.value = id;
        secDetail.classList.remove('hidden');
        // Scroll to form smoothly
        setTimeout(() => {
            secDetail.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    // --- QR SCANNER LOGIC ---
    function startScanner() {
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: {width: 250, height: 250}, aspectRatio: 1.0 });
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(error => {
                console.error("Failed to clear html5QrcodeScanner. ", error);
            });
            html5QrcodeScanner = null;
        }
    }

    async function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning after success
        stopScanner();
        document.getElementById('scanner-wrapper').classList.add('hidden');
        
        // decodedText is expected to be kode_barang
        try {
            const res = await fetch(`<?= url('mobile-sarpras/api/barang-by-kode') ?>?kode=${encodeURIComponent(decodedText)}`);
            const json = await res.json();
            
            if (json.status === 'success') {
                const b = json.data;
                document.getElementById('res-nama-barang').textContent = b.nama_barang;
                document.getElementById('res-ruangan').textContent = 'Ruangan: ' + (b.nama_ruangan || '-');
                document.getElementById('scan-result').classList.remove('hidden');
                
                showDetailForm(b.id);
            } else {
                alert('Barang dengan kode ' + decodedText + ' tidak ditemukan.');
                resetSelection();
                startScanner();
            }
        } catch (e) {
            alert('Gagal menghubungi server.');
            resetSelection();
            startScanner();
        }
    }

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning
    }

    document.getElementById('btn-scan-ulang').addEventListener('click', () => {
        resetSelection();
        startScanner();
    });


    // --- MANUAL INPUT LOGIC ---
    selRuangan.addEventListener('change', async function() {
        const rId = this.value;
        selBarang.innerHTML = '<option value="">Loading...</option>';
        selBarang.disabled = true;
        valBarangId.value = '';
        secDetail.classList.add('hidden');

        if (!rId) {
            selBarang.innerHTML = '<option value="">-- Pilih Ruangan Dulu --</option>';
            return;
        }

        try {
            const res = await fetch(`<?= url('mobile-sarpras/api/barang-by-ruangan') ?>?ruangan_id=${rId}`);
            const json = await res.json();
            
            if (json.status === 'success') {
                selBarang.innerHTML = '<option value="">-- Pilih Inventaris --</option>';
                json.data.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = `${b.kode_barang} - ${b.nama_barang}`;
                    selBarang.appendChild(opt);
                });
                selBarang.disabled = false;
            }
        } catch (e) {
            selBarang.innerHTML = '<option value="">Gagal mengambil data</option>';
        }
    });

    selBarang.addEventListener('change', function() {
        if (this.value) {
            showDetailForm(this.value);
        } else {
            valBarangId.value = '';
            secDetail.classList.add('hidden');
        }
    });

    // Initialize default mode
    startScanner();
});
</script>
