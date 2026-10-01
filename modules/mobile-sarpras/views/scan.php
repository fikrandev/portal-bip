<div class="px-4 pt-6 space-y-6 flex flex-col items-center h-full">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Scan QR Code</h2>
        <p class="text-sm text-slate-500 mt-1">Arahkan kamera ke QR Code aset untuk melihat detail dan riwayat perbaikan.</p>
    </div>

    <!-- QR Reader Container -->
    <div class="w-full max-w-sm bg-slate-900 rounded-3xl overflow-hidden shadow-xl border-4 border-white relative mt-4">
        <div id="reader" style="width:100%;"></div>
    </div>
    
    <div id="scan-result-container" class="w-full hidden mt-6">
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-center">
            <div class="inline-flex w-10 h-10 bg-blue-100 text-blue-600 rounded-full items-center justify-center mb-2">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">QR Berhasil Dipindai!</h3>
            <p id="scan-result-text" class="text-sm text-slate-600 font-mono break-all"></p>
            <button id="btn-lihat-detail" class="mt-3 px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl text-sm shadow-md w-full">Lihat Detail Aset</button>
        </div>
    </div>

    <!-- Manual fallback button -->
    <div id="camera-fallback" class="w-full hidden mt-4">
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
            <div class="inline-flex w-10 h-10 bg-amber-100 text-amber-600 rounded-full items-center justify-center mb-2">
                <i data-lucide="camera-off" class="w-5 h-5"></i>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Kamera tidak tersedia</h3>
            <p class="text-sm text-slate-500 mb-3">Pastikan browser memiliki izin akses kamera. Situs harus diakses via HTTPS.</p>
            <button onclick="location.reload()" class="px-5 py-2.5 bg-amber-500 text-white font-bold rounded-xl text-sm shadow-md w-full">Coba Lagi</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const resultContainer = document.getElementById('scan-result-container');
        const resultText = document.getElementById('scan-result-text');
        const btnDetail = document.getElementById('btn-lihat-detail');
        const fallback = document.getElementById('camera-fallback');
        let html5Qrcode = null;
        let lastResult = "";

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanning once found
            if (html5Qrcode) {
                html5Qrcode.stop().then(() => {
                    console.log('Scanner stopped');
                }).catch(err => console.error('Error stopping scanner:', err));
            }
            
            lastResult = decodedText;
            
            // Format ID aset
            let assetId = decodedText;
            
            // If it contains a URL, extract the ID
            if (decodedText.includes('detail/')) {
                const parts = decodedText.split('detail/');
                assetId = parts[1].replace(/[^0-9]/g, '');
            }
            
            resultText.innerText = "Data: " + decodedText;
            resultContainer.classList.remove('hidden');
            
            // Auto redirect if it's purely a number
            if (!isNaN(assetId) && assetId.trim() !== '') {
                window.location.href = '<?= url("mobile-sarpras/detail/") ?>' + assetId;
            }
            
            btnDetail.onclick = () => {
                window.location.href = '<?= url("mobile-sarpras/detail/") ?>' + (isNaN(assetId) ? 1 : assetId);
            };
        }

        // Use Html5Qrcode (not Scanner) for direct camera control
        html5Qrcode = new Html5Qrcode("reader");
        
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        // Request back camera
        html5Qrcode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            (errorMessage) => {
                // Ignore continuous scan errors
            }
        ).then(() => {
            console.log('QR Scanner started successfully');
        }).catch((err) => {
            console.error('Failed to start QR Scanner:', err);
            // Show fallback UI
            if (fallback) fallback.classList.remove('hidden');
        });
    });
</script>
