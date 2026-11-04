<?php
/**
 * View Scan QR Code Mobile Sarpras
 * Langsung Menampilkan Data Aset Tanpa Perlu Klik Link
 */
$presetCode = $presetCode ?? '';
?>
<div class="px-4 pt-4 pb-24 space-y-4 max-w-md mx-auto">
    <!-- Header -->
    <div class="text-center">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Scan QR Code Aset</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Arahkan kamera ke label QR aset Sarpras untuk melihat data seketika.</p>
    </div>

    <!-- Quick Switch / Mode Bar -->
    <div class="flex items-center justify-between bg-white px-3.5 py-2.5 rounded-2xl border border-slate-100 shadow-xs text-xs">
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="toggle-auto-redirect" class="w-4 h-4 text-blue-600 rounded-md border-slate-300 focus:ring-blue-500">
            <span class="text-slate-700 font-medium">Buka Halaman Detail Penuh</span>
        </label>
        <button type="button" id="btn-toggle-manual" class="text-blue-600 font-bold hover:underline flex items-center gap-1">
            <i data-lucide="keyboard" class="w-3.5 h-3.5"></i>
            <span>Ketik Kode</span>
        </button>
    </div>

    <!-- Manual Code Input Section (Collapsible) -->
    <div id="manual-input-box" class="hidden bg-white p-4 rounded-3xl border border-slate-100 shadow-xs space-y-3">
        <label class="block text-xs font-bold text-slate-700">Ketik / Tempel Kode Barang atau URL QR</label>
        <div class="flex gap-2">
            <input type="text" id="manual-code-input" placeholder="Contoh: A.001.YYS.001 atau tempel link..." class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm outline-none focus:border-blue-500 font-mono">
            <button type="button" id="btn-submit-manual" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-sm active:scale-95 transition-transform flex items-center gap-1.5 shrink-0">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span>Cari</span>
            </button>
        </div>
    </div>

    <!-- ================= SCANNER SECTION ================= -->
    <div id="scanner-section" class="space-y-3">
        <!-- Viewfinder Frame -->
        <div class="w-full bg-slate-900 rounded-3xl overflow-hidden shadow-xl border-4 border-white relative mx-auto aspect-square max-w-sm flex items-center justify-center">
            <div id="reader" style="width:100%; height:100%;"></div>
            
            <!-- Animated Laser Scan Line & Corners -->
            <div id="scanner-visual-overlay" class="absolute inset-0 pointer-events-none flex flex-col justify-between p-6">
                <div class="scanner-laser"></div>
                <div class="flex justify-between">
                    <div class="w-8 h-8 border-t-4 border-l-4 border-blue-500 rounded-tl-xl"></div>
                    <div class="w-8 h-8 border-t-4 border-r-4 border-blue-500 rounded-tr-xl"></div>
                </div>
                <div class="flex justify-between">
                    <div class="w-8 h-8 border-b-4 border-l-4 border-blue-500 rounded-bl-xl"></div>
                    <div class="w-8 h-8 border-b-4 border-r-4 border-blue-500 rounded-br-xl"></div>
                </div>
            </div>
        </div>

        <!-- Scanner Controls (Flash / File) -->
        <div class="flex items-center justify-center gap-3 pt-1">
            <button type="button" id="btn-toggle-torch" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-2xs active:scale-95 transition-transform">
                <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                <span id="torch-text">Senter</span>
            </button>
            <label class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-2xs active:scale-95 transition-transform cursor-pointer">
                <i data-lucide="image" class="w-4 h-4 text-blue-600"></i>
                <span>Unggah Gambar QR</span>
                <input type="file" id="qr-file-input" accept="image/*" class="hidden">
            </label>
        </div>

        <!-- Camera Fallback Notice -->
        <div id="camera-fallback" class="hidden bg-amber-50 border border-amber-200 rounded-3xl p-5 text-center">
            <div class="inline-flex w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl items-center justify-center mb-2.5">
                <i data-lucide="camera-off" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-sm mb-1">Kamera Tidak Tersedia / Izin Ditolak</h3>
            <p class="text-xs text-slate-500 mb-3">Pastikan Anda telah mengizinkan akses kamera browser dan mengakses melalui HTTPS. Anda juga bisa mengetik kode barang manual di atas.</p>
            <button type="button" onclick="location.reload()" class="px-5 py-2.5 bg-amber-500 text-white font-bold rounded-xl text-xs shadow-md w-full active:scale-95 transition-transform">Coba Nyalakan Lagi</button>
        </div>
    </div>

    <!-- ================= LOADING STATE ================= -->
    <div id="scan-loading-card" class="hidden bg-white rounded-3xl p-8 border border-slate-100 shadow-sm text-center space-y-3">
        <div class="inline-flex w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl items-center justify-center animate-spin">
            <i data-lucide="loader-2" class="w-7 h-7"></i>
        </div>
        <h4 class="font-bold text-slate-800 text-base">Memindai Data Aset...</h4>
        <p class="text-xs text-slate-500">Menghubungkan ke database Sarpras.</p>
    </div>

    <!-- ================= ASSET RESULT CARD (MUNCUL LANGSUNG) ================= -->
    <div id="scan-asset-card" class="hidden bg-white rounded-3xl border border-slate-100 shadow-lg overflow-hidden transition-all duration-300">
        
        <!-- Top Status Bar -->
        <div class="px-5 pt-4 pb-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Data Aset Teridentifikasi</span>
            </span>
            <button type="button" onclick="resumeScanning()" class="w-8 h-8 rounded-full bg-slate-200/80 hover:bg-slate-300 text-slate-600 flex items-center justify-center active:scale-90 transition-transform" title="Tutup & Scan Lagi">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Asset Photo / Header Banner -->
        <div id="asset-media-container" class="relative bg-slate-900 h-44 overflow-hidden">
            <img id="card-foto" src="" alt="Foto Aset" class="w-full h-full object-cover">
            <div id="card-foto-fallback" class="hidden w-full h-full bg-gradient-to-br from-blue-600 to-indigo-700 flex flex-col items-center justify-center text-white">
                <i data-lucide="package" class="w-12 h-12 text-white/80 mb-1"></i>
                <span class="text-xs font-semibold tracking-wider uppercase text-white/80">Sarpras Bina Insan Palu</span>
            </div>
            <!-- Category Badge Floating -->
            <span id="card-kategori" class="absolute bottom-3 left-4 px-3 py-1 rounded-lg bg-black/60 backdrop-blur-md text-[11px] font-bold text-white uppercase tracking-wider">
                Kategori
            </span>
        </div>

        <!-- Main Info Body -->
        <div class="p-5 space-y-4">
            
            <!-- Nama & Kode Barang -->
            <div>
                <h3 id="card-nama" class="text-lg font-bold text-slate-800 leading-snug">Nama Barang</h3>
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    <span id="card-kode" class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                        KODE-000
                    </span>
                    <button type="button" onclick="copyKodeAset()" class="p-1 text-slate-400 hover:text-slate-600" title="Salin Kode">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    </button>
                    <span id="copy-toast" class="text-[10px] text-emerald-600 font-bold hidden">Tersalin!</span>
                </div>
            </div>

            <!-- Two-Column Grid Info -->
            <div class="grid grid-cols-2 gap-2.5 bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Ruangan / Lokasi</span>
                    <span id="card-ruangan" class="font-bold text-slate-800 mt-0.5 block truncate">Ruangan -</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Kondisi Aset</span>
                    <div id="card-kondisi-wrapper" class="mt-0.5">
                        <span id="card-kondisi" class="inline-flex items-center gap-1 font-bold text-emerald-600">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Baik
                        </span>
                    </div>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Stok Aset</span>
                    <span id="card-stok" class="font-bold text-slate-800 mt-0.5 block">1 Unit (Tersedia: 1)</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Tahun / Sumber</span>
                    <span id="card-tahun-sumber" class="font-bold text-slate-800 mt-0.5 block truncate">2024 • Yayasan</span>
                </div>
                <div id="card-merk-wrapper" class="col-span-2 hidden border-t border-slate-200/60 pt-2 mt-1">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Merk / Model / No. Seri</span>
                    <span id="card-merk" class="font-medium text-slate-700 mt-0.5 block">-</span>
                </div>
            </div>

            <!-- Riwayat Maintenance Terakhir -->
            <div class="border border-slate-100 rounded-2xl p-3 bg-white">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                        <i data-lucide="wrench" class="w-3.5 h-3.5 text-amber-500"></i> Riwayat Perbaikan
                    </span>
                    <span id="card-maint-count" class="text-[10px] font-bold text-slate-400"></span>
                </div>
                <div id="card-maint-info" class="text-xs text-slate-600">
                    Belum ada riwayat perbaikan.
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="flex items-center gap-2 pt-1">
                <a id="card-btn-maint" href="#" class="flex-1 py-2.5 px-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-transform">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>Lapor Rusak</span>
                </a>
                <a id="card-btn-pinjam" href="#" class="flex-1 py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-transform">
                    <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                    <span>Pinjam Aset</span>
                </a>
                <a id="card-btn-detail" href="#" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center justify-center gap-1 active:scale-95 transition-transform" title="Buka Halaman Detail Lengkap">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Detail</span>
                </a>
            </div>

            <!-- Tombol Utama: Scan Aset Lain -->
            <button type="button" onclick="resumeScanning()" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg active:scale-95 transition-transform mt-2">
                <i data-lucide="scan" class="w-4 h-4 text-blue-400"></i>
                <span>Scan Aset Lainnya</span>
            </button>
        </div>
    </div>

    <!-- ================= ERROR / NOT FOUND CARD ================= -->
    <div id="scan-error-card" class="hidden bg-rose-50 border border-rose-200 rounded-3xl p-5 text-center space-y-3">
        <div class="inline-flex w-12 h-12 bg-rose-100 text-rose-600 rounded-2xl items-center justify-center">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
        <div>
            <h4 class="font-bold text-slate-800 text-sm">Aset Tidak Ditemukan</h4>
            <p id="error-message-text" class="text-xs text-slate-600 mt-1">Data QR Code tidak cocok dengan aset manapun di database Sarpras.</p>
            <p id="error-raw-text" class="text-[11px] font-mono text-slate-500 bg-white/80 p-2 rounded-xl mt-2 break-all border border-rose-100"></p>
        </div>
        <button type="button" onclick="resumeScanning()" class="w-full py-2.5 bg-rose-600 text-white rounded-xl text-xs font-bold shadow-md active:scale-95 transition-transform">
            Scan Ulang
        </button>
    </div>
</div>

<style>
    @keyframes scanLaser {
        0% { top: 12%; opacity: 0.8; }
        50% { top: 88%; opacity: 1; }
        100% { top: 12%; opacity: 0.8; }
    }
    .scanner-laser {
        position: absolute;
        left: 8%;
        right: 8%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #3b82f6, #93c5fd, transparent);
        box-shadow: 0 0 14px rgba(59, 130, 246, 0.9);
        animation: scanLaser 2.2s ease-in-out infinite;
        pointer-events: none;
        z-index: 10;
    }
</style>

<script>
    let html5Qrcode = null;
    let isScanning = false;
    let currentBarangKode = '';
    let isTorchOn = false;

    // Pleasant audio feedback tone on successful scan (Web Audio API)
    function playBeep() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.14);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.14);
        } catch (e) {
            // Audio error silenced
        }
    }

    // Trigger haptic vibration if supported
    function playVibration() {
        if (navigator.vibrate) {
            navigator.vibrate([70, 40, 70]);
        }
    }

    // Copy Kode Aset
    function copyKodeAset() {
        if (!currentBarangKode) return;
        navigator.clipboard.writeText(currentBarangKode).then(() => {
            const toast = document.getElementById('copy-toast');
            if (toast) {
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 2000);
            }
        });
    }

    // Stop Scanner Camera
    function stopCamera() {
        if (html5Qrcode && isScanning) {
            return html5Qrcode.stop().then(() => {
                isScanning = false;
            }).catch(err => {
                console.error('Error stopping scanner:', err);
                isScanning = false;
            });
        }
        return Promise.resolve();
    }

    // Start Scanner Camera
    function startCamera() {
        if (isScanning) return Promise.resolve();
        
        document.getElementById('scanner-section').classList.remove('hidden');
        document.getElementById('scan-asset-card').classList.add('hidden');
        document.getElementById('scan-error-card').classList.add('hidden');
        document.getElementById('scan-loading-card').classList.add('hidden');

        if (!html5Qrcode) {
            html5Qrcode = new Html5Qrcode("reader");
        }

        const config = {
            fps: 12,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        return html5Qrcode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            (errorMessage) => {
                // continuous scan frame errors ignored
            }
        ).then(() => {
            isScanning = true;
            document.getElementById('camera-fallback').classList.add('hidden');
        }).catch(err => {
            console.error('Failed to start camera:', err);
            isScanning = false;
            document.getElementById('camera-fallback').classList.remove('hidden');
        });
    }

    // Resume Scanning for another item
    function resumeScanning() {
        document.getElementById('scan-asset-card').classList.add('hidden');
        document.getElementById('scan-error-card').classList.add('hidden');
        document.getElementById('scan-loading-card').classList.add('hidden');
        document.getElementById('scanner-section').classList.remove('hidden');
        startCamera();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Handler when QR code is detected by camera or file
    async function onScanSuccess(decodedText) {
        if (!decodedText || !isScanning) return;
        
        playBeep();
        playVibration();
        await stopCamera();

        // Check if user set auto-redirect preference
        const autoRedirect = document.getElementById('toggle-auto-redirect').checked;

        // Tampilkan loading state
        document.getElementById('scanner-section').classList.add('hidden');
        document.getElementById('scan-asset-card').classList.add('hidden');
        document.getElementById('scan-error-card').classList.add('hidden');
        document.getElementById('scan-loading-card').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();

        fetchAssetData(decodedText, autoRedirect);
    }

    // Fetch and immediately display asset data
    async function fetchAssetData(inputCode, autoRedirect = false) {
        try {
            const endpoint = '<?= url("mobile-sarpras/api/scan-barang") ?>?kode=' + encodeURIComponent(inputCode);
            const res = await fetch(endpoint);
            const json = await res.json();

            document.getElementById('scan-loading-card').classList.add('hidden');

            if (json.status === 'success' && json.data) {
                const b = json.data;

                // Jika opsi auto-redirect aktif, langsung lompat ke halaman detail
                if (autoRedirect && b.url_detail) {
                    window.location.href = b.url_detail;
                    return;
                }

                // LANGSUNG TAMPILKAN DATA ASET DI LAYAR TANPA KLIK LINK!
                renderAssetCard(b);
            } else {
                showErrorCard(json.message || 'Aset tidak ditemukan di database.', inputCode);
            }
        } catch (err) {
            console.error('API Error:', err);
            document.getElementById('scan-loading-card').classList.add('hidden');
            showErrorCard('Gagal menghubungi server. Periksa koneksi internet Anda.', inputCode);
        }
    }

    // Render data aset ke dalam kartu interaktif
    function renderAssetCard(b) {
        currentBarangKode = b.kode_barang;

        // 1. Identitas & Nama
        document.getElementById('card-nama').textContent = b.nama_barang || 'Aset Tanpa Nama';
        document.getElementById('card-kode').textContent = b.kode_barang || '-';
        document.getElementById('card-kategori').textContent = b.nama_kategori || 'Inventaris';
        document.getElementById('card-ruangan').textContent = (b.nama_ruangan || '-') + (b.lokasi_gedung && b.lokasi_gedung !== '-' ? ' (' + b.lokasi_gedung + ')' : '');

        // 2. Foto Aset
        const imgEl = document.getElementById('card-foto');
        const fallbackEl = document.getElementById('card-foto-fallback');
        if (b.foto_utama) {
            imgEl.src = b.foto_utama;
            imgEl.classList.remove('hidden');
            fallbackEl.classList.add('hidden');
        } else {
            imgEl.classList.add('hidden');
            fallbackEl.classList.remove('hidden');
        }

        // 3. Kondisi dengan badge warna
        const kondisiEl = document.getElementById('card-kondisi');
        const kondisi = b.kondisi || 'Baik';
        if (kondisi === 'Baik') {
            kondisiEl.className = 'inline-flex items-center gap-1 font-bold text-emerald-600';
            kondisiEl.innerHTML = '<i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Baik';
        } else if (kondisi === 'Rusak Ringan') {
            kondisiEl.className = 'inline-flex items-center gap-1 font-bold text-amber-600';
            kondisiEl.innerHTML = '<i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Rusak Ringan';
        } else {
            kondisiEl.className = 'inline-flex items-center gap-1 font-bold text-rose-600';
            kondisiEl.innerHTML = '<i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Rusak Berat';
        }

        // 4. Stok & Tahun
        const satuan = b.satuan || 'Unit';
        document.getElementById('card-stok').textContent = `${b.jumlah} ${satuan} (Tersedia: ${b.tersedia})`;
        document.getElementById('card-tahun-sumber').textContent = `${b.tahun_pengadaan || '-'} • ${b.sumber_dana || '-'}`;

        // 5. Merk / Model & No Seri (jika ada)
        const merkWtapper = document.getElementById('card-merk-wrapper');
        const merkText = document.getElementById('card-merk');
        const infoExtra = [b.merk_model, b.nomor_seri ? 'SN: ' + b.nomor_seri : ''].filter(Boolean).join(' • ');
        if (infoExtra) {
            merkText.textContent = infoExtra;
            merkWtapper.classList.remove('hidden');
        } else {
            merkWtapper.classList.add('hidden');
        }

        // 6. Riwayat Perbaikan Terakhir
        const maintCountEl = document.getElementById('card-maint-count');
        const maintInfoEl = document.getElementById('card-maint-info');
        if (Array.isArray(b.maintenance) && b.maintenance.length > 0) {
            maintCountEl.textContent = `${b.maintenance.length} Riwayat`;
            const last = b.maintenance[0];
            const tgl = last.tanggal_lapor ? new Date(last.tanggal_lapor).toLocaleDateString('id-ID') : '-';
            const statusClass = last.status === 'Selesai' ? 'text-emerald-600 font-bold' : (last.status === 'Dalam Perbaikan' ? 'text-amber-600 font-bold' : 'text-slate-600 font-bold');
            
            maintInfoEl.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <p class="font-bold text-slate-800 truncate">${last.deskripsi_kerusakan || 'Perbaikan'}</p>
                    <span class="${statusClass} text-[10px] shrink-0">${last.status || '-'}</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Tgl: ${tgl} • Oleh: ${last.dilaporkan_oleh || 'Teknisi'}</p>
            `;
        } else {
            maintCountEl.textContent = '0 Riwayat';
            maintInfoEl.innerHTML = '<span class="text-emerald-600 font-medium flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Belum pernah tercatat rusak.</span>';
        }

        // 7. Tombol Aksi Cepat
        document.getElementById('card-btn-maint').href = b.url_maintenance || '#';
        document.getElementById('card-btn-pinjam').href = b.url_peminjaman || '#';
        document.getElementById('card-btn-detail').href = b.url_detail || '#';

        // Tampilkan Kartu
        document.getElementById('scanner-section').classList.add('hidden');
        document.getElementById('scan-error-card').classList.add('hidden');
        document.getElementById('scan-asset-card').classList.remove('hidden');

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Tampilkan Kartu Error
    function showErrorCard(msg, rawInput) {
        document.getElementById('error-message-text').textContent = msg;
        document.getElementById('error-raw-text').textContent = 'Hasil Scan: ' + (rawInput || '-');
        document.getElementById('scanner-section').classList.add('hidden');
        document.getElementById('scan-asset-card').classList.add('hidden');
        document.getElementById('scan-error-card').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Torch / Flash Toggle
    async function toggleTorch() {
        if (!html5Qrcode || !isScanning) return;
        try {
            // Apply track constraint
            const videoElem = document.querySelector('#reader video');
            if (videoElem && videoElem.srcObject) {
                const track = videoElem.srcObject.getVideoTracks()[0];
                if (track && track.getCapabilities && track.getCapabilities().torch) {
                    isTorchOn = !isTorchOn;
                    await track.applyConstraints({ advanced: [{ torch: isTorchOn }] });
                    document.getElementById('torch-text').textContent = isTorchOn ? 'Matikan' : 'Senter';
                } else {
                    alert('Lampu senter tidak didukung pada browser/kamera perangkat ini.');
                }
            }
        } catch (e) {
            alert('Tidak dapat mengaktifkan senter pada perangkat ini.');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Preference auto-redirect dari localStorage
        const autoRedirectToggle = document.getElementById('toggle-auto-redirect');
        const savedPref = localStorage.getItem('sarpras_scan_autoredirect');
        if (savedPref === '1') {
            autoRedirectToggle.checked = true;
        }
        autoRedirectToggle.addEventListener('change', function() {
            localStorage.setItem('sarpras_scan_autoredirect', this.checked ? '1' : '0');
        });

        // Torch button listener
        document.getElementById('btn-toggle-torch').addEventListener('click', toggleTorch);

        // Toggle Manual Input Box
        const manualBox = document.getElementById('manual-input-box');
        const manualInput = document.getElementById('manual-code-input');
        document.getElementById('btn-toggle-manual').addEventListener('click', () => {
            manualBox.classList.toggle('hidden');
            if (!manualBox.classList.contains('hidden')) {
                manualInput.focus();
            }
        });

        // Submit Manual Input
        document.getElementById('btn-submit-manual').addEventListener('click', () => {
            const val = manualInput.value.trim();
            if (val) {
                onScanSuccess(val);
            }
        });
        manualInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const val = manualInput.value.trim();
                if (val) onScanSuccess(val);
            }
        });

        // Scan from Image File
        const fileInput = document.getElementById('qr-file-input');
        fileInput.addEventListener('change', async (e) => {
            if (e.target.files && e.target.files.length > 0) {
                const file = e.target.files[0];
                if (!html5Qrcode) {
                    html5Qrcode = new Html5Qrcode("reader");
                }
                try {
                    await stopCamera();
                    const decodedText = await html5Qrcode.scanFile(file, true);
                    onScanSuccess(decodedText);
                } catch (err) {
                    alert('Tidak dapat mendeteksi QR Code dari gambar yang dipilih.');
                    startCamera();
                }
            }
        });

        // Jika halaman dibuka dengan parameter kode/ID preset (misal ?code=A.001.YYS.001)
        const presetCode = <?= json_encode($presetCode) ?>;
        if (presetCode && presetCode.trim() !== '') {
            onScanSuccess(presetCode.trim());
        } else {
            // Langsung start kamera
            startCamera();
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
