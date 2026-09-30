<div class="px-4 pt-6 space-y-6 flex flex-col items-center h-full">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Scan QR Code</h2>
        <p class="text-sm text-slate-500 mt-1">Arahkan kamera ke QR Code aset untuk melihat detail dan riwayat perbaikan.</p>
    </div>

    <div class="w-full max-w-sm aspect-square bg-slate-900 rounded-3xl overflow-hidden shadow-xl border-4 border-white relative mt-8">
        <div id="reader" class="w-full h-full object-cover"></div>
        
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
</div>

<style>
    @keyframes scan {
        0% { top: 0; }
        50% { top: 100%; }
        100% { top: 0; }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const resultContainer = document.getElementById('scan-result-container');
        const resultText = document.getElementById('scan-result-text');
        const btnDetail = document.getElementById('btn-lihat-detail');
        let html5QrcodeScanner = null;
        let lastResult = "";

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanning once found
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
            
            lastResult = decodedText;
            
            // Format ID aset. Biasanya format QR adalah ID aset, atau URL yang mengandung ID aset.
            // Kita coba parse angkanya jika formatnya seperti "BIP-SARPRAS-001" atau sejenisnya.
            let assetId = decodedText;
            
            // For demo purposes, if it contains a URL we extract the ID
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
                // Navigate to detail
                window.location.href = '<?= url("mobile-sarpras/detail/") ?>' + (isNaN(assetId) ? 1 : assetId);
            };
        }

        function onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning.
        }

        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250}, aspectRatio: 1.0 },
            /* verbose= */ false);
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        
        // Hide the default HTML5 QR UI components to keep it clean
        setTimeout(() => {
            const qrRegion = document.getElementById('reader__dashboard_section_csr');
            if(qrRegion) qrRegion.style.display = 'none';
        }, 500);
    });
</script>
