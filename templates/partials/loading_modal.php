<!-- Global Loading Modal Indicator -->
<div id="global-loading-modal" 
     class="fixed inset-0 z-[999999] flex items-center justify-center bg-slate-950/40 backdrop-blur-sm transition-all duration-250 opacity-0 pointer-events-none cursor-pointer"
     role="status" 
     aria-live="polite" 
     aria-hidden="true"
     title="Klik di luar untuk menutup loading jika macet">
    
    <div class="loading-modal-card relative w-[90%] max-w-[340px] bg-white/95 backdrop-blur-xl rounded-[28px] p-6 text-center shadow-2xl shadow-slate-950/20 border border-white/60 transform scale-95 transition-all duration-250 cursor-default" onclick="event.stopPropagation();">
        
        <!-- Subtle Cancel/Close Button -->
        <button type="button" 
                id="global-loading-cancel-btn" 
                onclick="window.LoadingModal && window.LoadingModal.hide()" 
                class="absolute top-3.5 right-3.5 p-1.5 text-slate-400 hover:text-rose-600 rounded-full hover:bg-slate-100 transition-colors cursor-pointer" 
                title="Batal / Tutup Loading (Esc)">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Animated Spinner & Pulse Glow -->
        <div class="relative w-16 h-16 mx-auto mb-4 flex items-center justify-center">
            <!-- Pulsing soft glow backdrop -->
            <div class="absolute inset-0 rounded-full bg-primary-500/20 animate-ping opacity-50"></div>
            
            <!-- Outer spinning gradient ring -->
            <div class="w-16 h-16 rounded-full border-4 border-slate-100 border-t-primary-600 border-r-indigo-500 animate-spin"></div>
            
            <!-- Center Brand / Pulse Icon -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-primary-600 shadow-md shadow-primary-500/30 overflow-hidden">
                    <?php if (defined('SYS_APP_FAVICON') && SYS_APP_FAVICON): ?>
                        <img src="<?= url(ltrim(SYS_APP_FAVICON, '/')) ?>" alt="Loading" class="w-5 h-5 object-contain animate-pulse">
                    <?php else: ?>
                        <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Text Section -->
        <h3 id="global-loading-title" class="text-base font-extrabold text-slate-800 tracking-tight">
            Memuat Data...
        </h3>
        <p id="global-loading-sub" class="text-xs text-slate-500 mt-1 font-medium leading-relaxed">
            Mohon tunggu sebentar, sistem sedang memproses...
        </p>

        <!-- Shimmering Animated Progress Bar -->
        <div class="mt-4 w-full h-1.5 bg-slate-100 rounded-full overflow-hidden relative">
            <div class="loading-shimmer-bar h-full bg-gradient-to-r from-primary-600 via-indigo-500 to-sky-400 rounded-full"></div>
        </div>

        <!-- Helper hint -->
        <p class="text-[10px] text-slate-400 mt-3 font-normal">
            Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[9px] text-slate-600 font-mono">Esc</kbd> atau klik di luar untuk membatalkan
        </p>

    </div>
</div>

<style>
/* Pastikan SweetAlert2 SELALU berada di layer paling atas melebihi loading modal & backdrop apapun */
.swal2-container {
    z-index: 100000000 !important;
}
#global-loading-modal.is-active {
    opacity: 1 !important;
    pointer-events: auto !important;
}
#global-loading-modal.is-active .loading-modal-card {
    transform: scale(1) !important;
}
.loading-shimmer-bar {
    width: 50%;
    animation: loadingBarMove 1.4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}
@keyframes loadingBarMove {
    0% {
        transform: translateX(-100%);
    }
    50% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(250%);
    }
}
</style>

<script>
(function() {
    'use strict';

    var modal = document.getElementById('global-loading-modal');
    var titleEl = document.getElementById('global-loading-title');
    var subEl = document.getElementById('global-loading-sub');
    var autoHideTimer = null;

    /**
     * Helper deteksi URL unduhan berkas (Download / Export / PDF / Excel / CSV / Cetak file)
     */
    function isDownloadTarget(el, urlStr) {
        if (el && (el.hasAttribute('download') || el.getAttribute('data-download') === 'true')) {
            return true;
        }
        if (!urlStr) return false;
        var lower = String(urlStr).toLowerCase();
        return lower.includes('/download') ||
               lower.includes('download=') ||
               lower.includes('/export') ||
               lower.includes('export=') ||
               lower.includes('export-') ||
               lower.includes('cetak-') ||
               lower.includes('/cetak') ||
               lower.includes('download-contoh') ||
               lower.includes('unduh') ||
               lower.endsWith('.csv') || lower.includes('.csv?') ||
               lower.endsWith('.xlsx') || lower.includes('.xlsx?') ||
               lower.endsWith('.xls') || lower.includes('.xls?') ||
               lower.endsWith('.pdf') || lower.includes('.pdf?') ||
               lower.endsWith('.zip') || lower.includes('.zip?');
    }

    var LoadingModal = {
        show: function(title, sub, customTimeoutMs) {
            // Jika ada modal konfirmasi SweetAlert2 aktif dan bukan state loading, jangan tumpuk/tutupi
            if (window.Swal && typeof Swal.isVisible === 'function' && Swal.isVisible()) {
                var swalPopup = Swal.getPopup ? Swal.getPopup() : null;
                var isSwalLoading = swalPopup && (swalPopup.classList.contains('swal2-loading') || swalPopup.querySelector('.swal2-loading'));
                if (!isSwalLoading) {
                    return;
                }
            }

            if (!modal) modal = document.getElementById('global-loading-modal');
            if (!titleEl) titleEl = document.getElementById('global-loading-title');
            if (!subEl) subEl = document.getElementById('global-loading-sub');
            if (!modal) return;

            if (title && titleEl) titleEl.textContent = title;
            else if (titleEl) titleEl.textContent = 'Memuat Data...';

            if (sub && subEl) subEl.textContent = sub;
            else if (subEl) subEl.textContent = 'Mohon tunggu sebentar, sistem sedang memproses...';

            modal.classList.add('is-active');
            modal.setAttribute('aria-hidden', 'false');

            // Safety timeout: auto-hide jika respon macet atau selesai di background (default 8s, download 2s)
            if (autoHideTimer) clearTimeout(autoHideTimer);
            var timeoutDuration = customTimeoutMs && customTimeoutMs > 0 ? customTimeoutMs : 8000;
            autoHideTimer = setTimeout(function() {
                LoadingModal.hide();
            }, timeoutDuration);
        },

        showDownload: function(title, sub) {
            LoadingModal.show(
                title || 'Menyiapkan Berkas Unduhan...',
                sub || 'File sedang diunduh, mohon tunggu sebentar...',
                2000 // Otomatis hilang dalam 2 detik agar tidak macet di layar
            );
        },

        hide: function() {
            if (!modal) modal = document.getElementById('global-loading-modal');
            if (!modal) return;

            modal.classList.remove('is-active');
            modal.setAttribute('aria-hidden', 'true');
            if (autoHideTimer) {
                clearTimeout(autoHideTimer);
                autoHideTimer = null;
            }
        }
    };

    // Expose globally
    window.LoadingModal = LoadingModal;
    window.showLoading = LoadingModal.show;
    window.hideLoading = LoadingModal.hide;

    // 1. Sembunyikan saat halaman dimuat atau browser cache ditampilkan
    window.addEventListener('pageshow', function() {
        LoadingModal.hide();
    });

    document.addEventListener('DOMContentLoaded', function() {
        LoadingModal.hide();
    });

    // 2. Tombol ESC untuk menutup loading kapan saja jika macet
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            LoadingModal.hide();
        }
    });

    // 3. Klik di luar kartu modal (Backdrop) langsung menutup loading modal
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                LoadingModal.hide();
            }
        });
    }

    // 4. DETEKSI BATAL / CANCEL GLOBAL:
    // Jika user mengklik tombol Batal / Cancel / Tutup / Close di modal manapun, selalu sembunyikan loading!
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button, a, [role="button"], input[type="button"], input[type="reset"]');
        if (!btn) return;

        var txt = (btn.textContent || btn.value || '').trim().toLowerCase();
        var cls = (btn.className || '').toLowerCase();
        var id = (btn.id || '').toLowerCase();

        var isCancelAction = txt === 'batal' || txt === 'cancel' || txt === 'tutup' || txt === 'kembali' ||
            txt.includes('batal') || txt.includes('cancel') ||
            btn.hasAttribute('data-modal-close') || btn.hasAttribute('data-dismiss') ||
            cls.includes('modal-close') || cls.includes('btn-batal') || cls.includes('btn-cancel') ||
            id.includes('batal') || id.includes('cancel') || id.includes('close');

        if (isCancelAction) {
            LoadingModal.hide();
        }
    }, true);

    // 5. INTERCEPTOR SWEETALERT2:
    // Saat dialog konfirmasi ditutup / dibatalkan / di-dismiss, otomatis sembunyikan loading
    function hookSweetAlert() {
        if (window.Swal && !window.Swal._loadingHooked) {
            window.Swal._loadingHooked = true;
            var origFire = window.Swal.fire;
            window.Swal.fire = function() {
                // Jangan tumpuk loading modal di bawah dialog konfirmasi baru
                LoadingModal.hide();
                var promise = origFire.apply(this, arguments);
                if (promise && typeof promise.then === 'function') {
                    promise.then(function(result) {
                        // Jika user klik Batal, klik di luar, atau tekan ESC
                        if (result && !result.isConfirmed) {
                            LoadingModal.hide();
                        }
                    }).catch(function() {
                        LoadingModal.hide();
                    });
                }
                return promise;
            };
            Object.assign(window.Swal.fire, origFire);
        }
    }
    hookSweetAlert();
    document.addEventListener('DOMContentLoaded', hookSweetAlert);

    // 6. DETEKSI FORM SUBMISSION:
    // Menggunakan bubble phase agar validasi form (preventDefault) dapat membatalkan loading
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form || form.nodeName !== 'FORM') return;

        // Jika event submit sudah di-cancel / preventDefault oleh validator JS
        if (e.defaultPrevented) {
            LoadingModal.hide();
            return;
        }

        // Lewati form tab baru
        if (form.getAttribute('target') === '_blank') return;
        // Lewati form yang ditandai data-no-loading
        if (form.hasAttribute('data-no-loading') || form.closest('[data-no-loading]')) return;
        // Lewati form yang memiliki konfirmasi modal sendiri
        var onsubmitAttr = (form.getAttribute('onsubmit') || '').toLowerCase();
        if (onsubmitAttr.includes('confirm') || form.hasAttribute('data-confirm')) return;

        var action = form.getAttribute('action') || window.location.href;
        var text = form.getAttribute('data-loading-text');
        var sub = form.getAttribute('data-loading-sub');

        // Jika form ditujukan untuk download/export (misal: POST export)
        if (isDownloadTarget(form, action)) {
            LoadingModal.showDownload(
                text || 'Menyiapkan Berkas Unduhan...',
                sub || 'File sedang diproses dan akan segera terunduh...'
            );
            return;
        }

        // Tunda 35ms untuk memastikan tidak ada handler JS yang memanggil preventDefault() (misal validasi AJAX)
        setTimeout(function() {
            if (e.defaultPrevented) {
                LoadingModal.hide();
                return;
            }

            var actLower = action.toLowerCase();
            if (!text) {
                if (actLower.includes('sync-dapodik')) {
                    text = 'Menyinkronkan Dapodik...';
                    sub = 'Sedang menarik data dari server Dapodik...';
                } else if (actLower.includes('import') || actLower.includes('upload')) {
                    text = 'Mengunggah & Memproses...';
                    sub = 'Mohon jangan menutup halaman saat upload berlangsung...';
                } else if (actLower.includes('delete') || actLower.includes('destroy') || actLower.includes('hapus')) {
                    text = 'Menghapus Data...';
                    sub = 'Sistem sedang memperbarui data...';
                } else {
                    text = 'Menyimpan & Memproses...';
                    sub = 'Mohon tunggu sebentar...';
                }
            }

            LoadingModal.show(text, sub);
        }, 35);
    }, false);

    // 7. DETEKSI LINK CLICKS (Pagination, filters, navigasi internal, unduhan):
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a');
        if (!link) return;

        var href = link.getAttribute('href');
        if (!href) return;

        // Lewati tautan non-navigasi
        if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) return;
        if (link.getAttribute('target') === '_blank') return;
        if (link.hasAttribute('data-no-loading') || link.closest('[data-no-loading]')) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.which === 2) return;

        try {
            var url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
        } catch(err) {
            return;
        }

        // JIKA TAUTAN MERUPAKAN UNDUHAN / EXPORT:
        // Gunakan showDownload() dengan batas waktu 2 detik sehingga loading tidak macet di layar!
        if (isDownloadTarget(link, href) || isDownloadTarget(link, link.href)) {
            var dlText = link.getAttribute('data-loading-text') || 'Menyiapkan Unduhan...';
            var dlSub = link.getAttribute('data-loading-sub') || 'Berkas sedang diunduh, mohon tunggu sebentar...';
            LoadingModal.showDownload(dlText, dlSub);

            // Saat jendela kembali mendapatkan fokus (misal dialog simpan berkas selesai), pastikan loading hilang
            window.addEventListener('focus', function onFocusOnce() {
                window.removeEventListener('focus', onFocusOnce);
                setTimeout(function() { LoadingModal.hide(); }, 300);
            });
            return;
        }

        // UNTUK NAVIGASI BIASA:
        // Tunda 35ms untuk memastikan event tidak di-cancel (preventDefault) oleh modal atau dialog JS
        setTimeout(function() {
            if (e.defaultPrevented) {
                LoadingModal.hide();
                return;
            }

            var text = link.getAttribute('data-loading-text') || 'Memuat Halaman...';
            var sub = link.getAttribute('data-loading-sub') || 'Sedang mengambil data, mohon tunggu sebentar...';

            if (link.href.includes('filter') || link.href.includes('jenjang=') || link.href.includes('kelas=') || link.href.includes('page=')) {
                text = 'Memuat Data...';
                sub = 'Sedang memfilter data yang dipilih...';
            }

            LoadingModal.show(text, sub);
        }, 35);
    }, false);

    // 8. Auto-hide saat tab/jendela kembali aktif
    window.addEventListener('focus', function() {
        if (autoHideTimer) {
            // Jika loading sudah berjalan lebih dari 2 detik saat fokus kembali, sembunyikan
            setTimeout(function() { LoadingModal.hide(); }, 800);
        }
    });

    // 9. jQuery AJAX Hook (jika jQuery aktif di halaman)
    if (window.jQuery) {
        $(document).ajaxStart(function() {
            if (!window.__silentAjax) {
                LoadingModal.show('Memproses Data...', 'Sedang berkomunikasi dengan server...');
            }
        });
        $(document).ajaxStop(function() {
            LoadingModal.hide();
        });
        $(document).ajaxError(function() {
            LoadingModal.hide();
        });
    }

})();
</script>
