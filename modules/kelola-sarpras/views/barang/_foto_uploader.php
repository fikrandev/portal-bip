<?php
/**
 * Komponen Multi-Foto & Kamera Langsung Web (Bypass Galeri Tablet)
 * Digunakan pada Tambah & Edit Barang (Mobile & Desktop)
 *
 * Parameter yang didukung:
 * - $uploaderId (string) : ID unik untuk instance (default: 'uploader_foto')
 * - $existingFotos (array) : Array path foto yang sudah tersimpan
 * - $isMobile (bool) : Penyesuaian tampilan untuk mobile sarpras
 */

$uploaderId = $uploaderId ?? 'uploader_' . uniqid();
$existingFotos = !empty($existingFotos) && is_array($existingFotos) ? array_values(array_filter($existingFotos)) : [];
$isMobile = !empty($isMobile);
?>

<div id="<?= $uploaderId ?>" class="multi-foto-uploader space-y-3">
    <input type="hidden" name="has_photo_interaction" value="1">
    
    <!-- Header & Info -->
    <div class="flex items-center justify-between">
        <div>
            <label class="block text-xs font-bold text-slate-700">
                Foto Inventaris / Aset
                <span class="text-[11px] font-normal text-slate-500">(Bisa lebih dari 1 foto)</span>
            </label>
            <p class="text-[11px] text-slate-400 mt-0.5">
                Kamera web langsung mengambil foto ke memori tanpa tersimpan di galeri tablet.
            </p>
        </div>
        <span class="foto-count-badge px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 transition-all">
            <?= count($existingFotos) ?> Foto
        </span>
    </div>

    <!-- Action Buttons -->
    <div class="grid <?= $isMobile ? 'grid-cols-1 gap-2.5' : 'grid-cols-1 sm:grid-cols-2 gap-3' ?>">
        <!-- Tombol Buka Kamera Langsung (Webcam / Live Stream) -->
        <button type="button" class="btn-open-camera w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-500/25 flex items-center justify-center gap-2.5 active:scale-[0.98] transition-all">
            <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
            </svg>
            <span>Buka Kamera Langsung (Webcam)</span>
        </button>

        <!-- Tombol Pilih File dari Perangkat (Laptop/PC) -->
        <button type="button" class="btn-pick-file w-full py-3 px-4 rounded-2xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm shadow-sm flex items-center justify-center gap-2.5 active:scale-[0.98] transition-all">
            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <span>Pilih File (Laptop / Galeri)</span>
        </button>
        <input type="file" class="file-input-hidden hidden" multiple accept="image/jpeg,image/png,image/webp">
        <!-- Fallback direct capture for browsers without getUserMedia -->
        <input type="file" class="fallback-camera-input hidden" accept="image/*" capture="environment">
    </div>

    <!-- Preview Container (Gallery) -->
    <div class="preview-gallery grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-1">
        <!-- Render existing photos if any -->
        <?php foreach ($existingFotos as $idx => $f): ?>
            <div class="foto-item existing-foto relative group rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm aspect-square" data-index="<?= $idx ?>">
                <img src="<?= url('public/' . ltrim($f, '/')) ?>" alt="Foto <?= $idx + 1 ?>" class="w-full h-full object-cover">
                <input type="hidden" name="existing_foto[]" value="<?= e($f) ?>">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-80 pointer-events-none"></div>
                <span class="absolute bottom-2 left-2 text-[10px] font-bold text-white bg-slate-900/60 backdrop-blur-md px-2 py-0.5 rounded-md pointer-events-none">
                    Foto <?= $idx + 1 ?>
                </span>
                <button type="button" class="btn-remove-foto absolute top-2 right-2 w-7 h-7 rounded-full bg-rose-600/90 hover:bg-rose-600 text-white flex items-center justify-center shadow-lg active:scale-90 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty State Message -->
    <div class="empty-state-notice <?= !empty($existingFotos) ? 'hidden' : '' ?> text-center py-6 px-4 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
        </div>
        <p class="text-xs font-semibold text-slate-600">Belum ada foto yang diambil</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Gunakan kamera langsung atau pilih dari berkas laptop/tab</p>
    </div>

    <!-- Live Camera Modal (In-Browser Webcam Overlay) -->
    <div class="camera-modal fixed inset-0 z-[1000] hidden bg-slate-950 flex flex-col justify-between">
        <!-- Top Controls Bar -->
        <div class="p-4 bg-slate-900/80 backdrop-blur-md flex items-center justify-between text-white border-b border-white/10 shrink-0 z-20">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="text-xs sm:text-sm font-bold tracking-wide">Kamera Web Aktif</span>
                <span class="cam-facing-label text-[10px] bg-white/20 px-2 py-0.5 rounded-full">Belakang</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn-switch-cam p-2.5 rounded-full bg-white/10 hover:bg-white/20 active:scale-95 transition-all text-white" title="Ganti Kamera Depan/Belakang">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
                <button type="button" class="btn-close-cam p-2.5 rounded-full bg-rose-600/80 hover:bg-rose-600 active:scale-95 transition-all text-white" title="Tutup Kamera">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Video Viewfinder -->
        <div class="relative flex-1 bg-black flex items-center justify-center overflow-hidden">
            <video class="cam-video w-full h-full object-contain" autoplay playsinline muted></video>
            
            <!-- Framing Guides (Overlay crosshair / corner marks) -->
            <div class="absolute inset-8 sm:inset-16 pointer-events-none border-2 border-white/20 rounded-3xl flex flex-col justify-between p-4">
                <div class="flex justify-between">
                    <div class="w-6 h-6 border-t-2 border-l-2 border-blue-400 rounded-tl-lg"></div>
                    <div class="w-6 h-6 border-t-2 border-r-2 border-blue-400 rounded-tr-lg"></div>
                </div>
                <div class="text-center">
                    <span class="px-3 py-1 bg-black/50 backdrop-blur-md rounded-full text-[11px] text-white/80 font-medium">
                        Arahkan kamera ke inventaris
                    </span>
                </div>
                <div class="flex justify-between">
                    <div class="w-6 h-6 border-b-2 border-l-2 border-blue-400 rounded-bl-lg"></div>
                    <div class="w-6 h-6 border-b-2 border-r-2 border-blue-400 rounded-br-lg"></div>
                </div>
            </div>

            <!-- Shutter Flash Effect -->
            <div class="shutter-flash absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-150"></div>
            
            <!-- Toast Feedback Notification -->
            <div class="cam-toast absolute top-6 px-4 py-2 rounded-full bg-emerald-500 text-white font-bold text-xs shadow-xl opacity-0 transform -translate-y-4 pointer-events-none transition-all duration-300">
                Foto berhasil diambil!
            </div>
        </div>

        <!-- Bottom Shutter & Action Bar -->
        <div class="p-6 bg-slate-900/90 backdrop-blur-md flex items-center justify-between border-t border-white/10 shrink-0 z-20">
            <!-- Mini Thumbnail of Last Taken Photo -->
            <div class="last-thumb-container w-14 h-14 rounded-2xl border-2 border-white/30 overflow-hidden bg-black/40 flex items-center justify-center">
                <span class="last-thumb-empty text-[10px] text-white/40 text-center font-bold">0 Foto</span>
                <img class="last-thumb-img w-full h-full object-cover hidden" alt="Thumb">
            </div>

            <!-- Big Shutter Button -->
            <button type="button" class="btn-shutter relative w-20 h-20 rounded-full border-4 border-white flex items-center justify-center p-1 active:scale-90 transition-transform shadow-2xl">
                <div class="w-full h-full rounded-full bg-white active:bg-blue-200 transition-colors"></div>
            </button>

            <!-- Done / Return Button -->
            <button type="button" class="btn-done-cam px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md active:scale-95 transition-all">
                Selesai (<span class="cam-batch-count">0</span>)
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const root = document.getElementById('<?= $uploaderId ?>');
    if (!root) return;

    const btnOpenCamera = root.querySelector('.btn-open-camera');
    const btnPickFile = root.querySelector('.btn-pick-file');
    const fileInput = root.querySelector('.file-input-hidden');
    const fallbackCameraInput = root.querySelector('.fallback-camera-input');
    const gallery = root.querySelector('.preview-gallery');
    const emptyNotice = root.querySelector('.empty-state-notice');
    const countBadge = root.querySelector('.foto-count-badge');

    // Camera Modal Elements
    const camModal = root.querySelector('.camera-modal');
    const camVideo = root.querySelector('.cam-video');
    const btnCloseCam = root.querySelector('.btn-close-cam');
    const btnSwitchCam = root.querySelector('.btn-switch-cam');
    const btnShutter = root.querySelector('.btn-shutter');
    const btnDoneCam = root.querySelector('.btn-done-cam');
    const camFacingLabel = root.querySelector('.cam-facing-label');
    const shutterFlash = root.querySelector('.shutter-flash');
    const camToast = root.querySelector('.cam-toast');
    const lastThumbEmpty = root.querySelector('.last-thumb-empty');
    const lastThumbImg = root.querySelector('.last-thumb-img');
    const camBatchCount = root.querySelector('.cam-batch-count');

    let currentStream = null;
    let currentFacingMode = 'environment'; // default rear camera for tablets/phones
    let capturedCountInSession = 0;

    function updateCounter() {
        const totalItems = gallery.querySelectorAll('.foto-item').length;
        if (countBadge) countBadge.textContent = totalItems + ' Foto';
        if (camBatchCount) camBatchCount.textContent = totalItems;
        if (emptyNotice) {
            if (totalItems > 0) {
                emptyNotice.classList.add('hidden');
            } else {
                emptyNotice.classList.remove('hidden');
            }
        }
    }

    function showToast(msg) {
        if (!camToast) return;
        camToast.textContent = msg;
        camToast.classList.remove('opacity-0', '-translate-y-4');
        camToast.classList.add('opacity-100', 'translate-y-0');
        setTimeout(() => {
            camToast.classList.remove('opacity-100', 'translate-y-0');
            camToast.classList.add('opacity-0', '-translate-y-4');
        }, 1500);
    }

    function addPhotoItem(dataUrl, isExisting = false, existingPath = '') {
        const totalNow = gallery.querySelectorAll('.foto-item').length + 1;
        const item = document.createElement('div');
        item.className = 'foto-item relative group rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm aspect-square transition-all animate-fadeIn';
        
        let hiddenInput = '';
        if (isExisting) {
            hiddenInput = `<input type="hidden" name="existing_foto[]" value="${existingPath}">`;
        } else {
            hiddenInput = `<input type="hidden" name="foto_captured[]" value="${dataUrl}">`;
        }

        item.innerHTML = `
            <img src="${dataUrl}" alt="Foto ${totalNow}" class="w-full h-full object-cover">
            ${hiddenInput}
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-80 pointer-events-none"></div>
            <span class="absolute bottom-2 left-2 text-[10px] font-bold text-white bg-slate-900/60 backdrop-blur-md px-2 py-0.5 rounded-md pointer-events-none">
                Foto ${totalNow}
            </span>
            <button type="button" class="btn-remove-foto absolute top-2 right-2 w-7 h-7 rounded-full bg-rose-600/90 hover:bg-rose-600 text-white flex items-center justify-center shadow-lg active:scale-90 transition-transform">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;

        item.querySelector('.btn-remove-foto').addEventListener('click', function(e) {
            e.stopPropagation();
            item.remove();
            renumberPhotos();
            updateCounter();
        });

        gallery.appendChild(item);
        renumberPhotos();
        updateCounter();

        // Update mini thumbnail in camera modal
        if (lastThumbImg && lastThumbEmpty) {
            lastThumbImg.src = dataUrl;
            lastThumbImg.classList.remove('hidden');
            lastThumbEmpty.classList.add('hidden');
        }
    }

    function renumberPhotos() {
        const items = gallery.querySelectorAll('.foto-item');
        items.forEach((it, idx) => {
            const badge = it.querySelector('span');
            if (badge) badge.textContent = 'Foto ' + (idx + 1);
        });
    }

    // Attach remove handler to pre-rendered existing photos
    gallery.querySelectorAll('.foto-item').forEach(item => {
        const btn = item.querySelector('.btn-remove-foto');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                item.remove();
                renumberPhotos();
                updateCounter();
            });
        }
    });

    // ── FILE PICKER (LAPTOP / DESKTOP) ───────────────────────────
    if (btnPickFile && fileInput) {
        btnPickFile.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (!this.files || this.files.length === 0) return;
            Array.from(this.files).forEach(file => {
                if (!file.type.match('image.*')) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    addPhotoItem(e.target.result, false);
                };
                reader.readAsDataURL(file);
            });
            this.value = ''; // Reset input
        });
    }

    // ── FALLBACK CAMERA INPUT (FOR OLD BROWSER / HTTP WITHOUT SSL) ─
    if (fallbackCameraInput) {
        fallbackCameraInput.addEventListener('change', function() {
            if (!this.files || this.files.length === 0) return;
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addPhotoItem(e.target.result, false);
                };
                reader.readAsDataURL(file);
            });
            this.value = '';
        });
    }

    // ── LIVE WEBCAM / GETUSERMEDIA (IN-BROWSER STREAM) ────────────
    async function startCamera(facingMode = 'environment') {
        stopCamera();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (window.ModalHelper) {
                const ok = await ModalHelper.confirm({
                    title: 'Izin Kamera',
                    message: 'Kamera web langsung memerlukan koneksi HTTPS / localhost atau izin kamera. Buka pemilih kamera perangkat langsung?',
                    type: 'info',
                    confirmText: 'Buka Kamera'
                });
                if (ok && fallbackCameraInput) fallbackCameraInput.click();
            } else if (confirm("Kamera web langsung memerlukan koneksi HTTPS / localhost atau izin kamera.\n\nBuka pemilih kamera perangkat langsung?")) {
                if (fallbackCameraInput) fallbackCameraInput.click();
            }
            return;
        }

        try {
            const constraints = {
                video: {
                    facingMode: { ideal: facingMode },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            };

            currentStream = await navigator.mediaDevices.getUserMedia(constraints);
            camVideo.srcObject = currentStream;
            await camVideo.play();
            
            currentFacingMode = facingMode;
            if (camFacingLabel) {
                camFacingLabel.textContent = facingMode === 'user' ? 'Depan' : 'Belakang';
            }

            camModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } catch (err) {
            console.error("Camera access error:", err);
            // If facingMode environment fails, try default without constraints
            try {
                currentStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                camVideo.srcObject = currentStream;
                await camVideo.play();
                camModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } catch (fallbackErr) {
                alert("Tidak dapat mengakses kamera: " + (err.message || "Izin ditolak atau kamera sedang digunakan aplikasi lain."));
                if (fallbackCameraInput) fallbackCameraInput.click();
            }
        }
    }

    function stopCamera() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
        if (camVideo) {
            camVideo.srcObject = null;
        }
        if (camModal) {
            camModal.classList.add('hidden');
        }
        document.body.style.overflow = '';
    }

    function snapPhoto() {
        if (!camVideo || !currentStream) return;

        // Visual shutter flash
        if (shutterFlash) {
            shutterFlash.style.opacity = '0.9';
            setTimeout(() => {
                shutterFlash.style.opacity = '0';
            }, 120);
        }

        // Draw video frame onto memory canvas
        const canvas = document.createElement('canvas');
        let w = camVideo.videoWidth || 1280;
        let h = camVideo.videoHeight || 720;

        // Scale to maximum 1600px width/height for optimal clarity & lightweight base64
        const maxDim = 1600;
        if (w > maxDim || h > maxDim) {
            if (w > h) {
                h = Math.round((h * maxDim) / w);
                w = maxDim;
            } else {
                w = Math.round((w * maxDim) / h);
                h = maxDim;
            }
        }

        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');

        // If front camera (user), mirror image so it looks natural
        if (currentFacingMode === 'user') {
            ctx.translate(w, 0);
            ctx.scale(-1, 1);
        }

        ctx.drawImage(camVideo, 0, 0, w, h);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

        // Add photo directly to RAM list & form
        capturedCountInSession++;
        addPhotoItem(dataUrl, false);
        showToast("✓ Foto " + gallery.querySelectorAll('.foto-item').length + " tersimpan di form!");
    }

    // Shutter button click
    if (btnShutter) {
        btnShutter.addEventListener('click', (e) => {
            e.preventDefault();
            snapPhoto();
        });
    }

    // Switch camera front/back
    if (btnSwitchCam) {
        btnSwitchCam.addEventListener('click', () => {
            const nextFacing = currentFacingMode === 'user' ? 'environment' : 'user';
            startCamera(nextFacing);
        });
    }

    // Open camera button
    if (btnOpenCamera) {
        btnOpenCamera.addEventListener('click', () => {
            startCamera('environment');
        });
    }

    // Close camera buttons
    if (btnCloseCam) {
        btnCloseCam.addEventListener('click', stopCamera);
    }
    if (btnDoneCam) {
        btnDoneCam.addEventListener('click', stopCamera);
    }

    // Expose startCamera function on root element in case external trigger wants to open it
    root.openCamera = () => startCamera('environment');
    root.clearPhotos = () => {
        gallery.innerHTML = '';
        updateCounter();
    };
    root.loadExistingPhotos = (photos) => {
        gallery.innerHTML = '';
        if (Array.isArray(photos)) {
            photos.forEach(p => {
                if (p) addPhotoItem('<?= url('public/') ?>' + p.replace(/^\//, ''), true, p);
            });
        }
        updateCounter();
    };
})();
</script>
