<!-- Global Loading Modal Indicator -->
<div id="global-loading-modal" 
     class="fixed inset-0 z-[999999] flex items-center justify-center bg-slate-950/40 backdrop-blur-sm transition-all duration-250 opacity-0 pointer-events-none"
     role="status" 
     aria-live="polite" 
     aria-hidden="true">
    
    <div class="loading-modal-card relative w-[90%] max-w-[340px] bg-white/95 backdrop-blur-xl rounded-[28px] p-6 text-center shadow-2xl shadow-slate-950/20 border border-white/60 transform scale-95 transition-all duration-250">
        
        <!-- Animated Spinner & Pulse Glow -->
        <div class="relative w-16 h-16 mx-auto mb-4 flex items-center justify-center">
            <!-- Pulsing soft glow backdrop -->
            <div class="absolute inset-0 rounded-full bg-primary-500/20 animate-ping opacity-50"></div>
            
            <!-- Outer spinning gradient ring -->
            <div class="w-16 h-16 rounded-full border-4 border-slate-100 border-t-primary-600 border-r-indigo-500 animate-spin"></div>
            
            <!-- Center Brand / Pulse Icon -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-primary-500/30">
                    <svg class="w-4 h-4 text-white animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
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

    </div>
</div>

<style>
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

    var LoadingModal = {
        show: function(title, sub) {
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

            // Safety timeout: auto-hide after 18 seconds in case request is cancelled
            if (autoHideTimer) clearTimeout(autoHideTimer);
            autoHideTimer = setTimeout(function() {
                LoadingModal.hide();
            }, 18000);
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

    // 1. Hide whenever page is shown (handles Back/Forward cache in browsers)
    window.addEventListener('pageshow', function(e) {
        LoadingModal.hide();
    });

    document.addEventListener('DOMContentLoaded', function() {
        LoadingModal.hide();
    });

    // 2. Allow ESC key to dismiss in case of stuck loading
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            LoadingModal.hide();
        }
    });

    // 3. Auto-trigger on Form Submissions
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form || form.nodeName !== 'FORM') return;

        // Skip if new tab
        if (form.getAttribute('target') === '_blank') return;
        // Skip if marked with data-no-loading
        if (form.hasAttribute('data-no-loading')) return;

        var text = form.getAttribute('data-loading-text');
        var sub = form.getAttribute('data-loading-sub');

        var action = (form.getAttribute('action') || '').toLowerCase();
        if (!text) {
            if (action.includes('sync-dapodik')) {
                text = 'Menyinkronkan Dapodik...';
                sub = 'Sedang menarik data dari server Dapodik...';
            } else if (action.includes('import') || action.includes('upload')) {
                text = 'Mengunggah & Memproses...';
                sub = 'Mohon jangan menutup halaman saat upload berlangsung...';
            } else if (action.includes('delete') || action.includes('destroy') || action.includes('hapus')) {
                text = 'Menghapus Data...';
                sub = 'Sistem sedang memperbarui data...';
            } else {
                text = 'Menyimpan & Memproses...';
                sub = 'Mohon tunggu sebentar...';
            }
        }

        LoadingModal.show(text, sub);
    }, true);

    // 4. Auto-trigger on internal link clicks (Pagination, filters, menu navigation)
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a');
        if (!link) return;

        var href = link.getAttribute('href');
        if (!href) return;

        // Skip non-navigating links
        if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) return;
        if (link.getAttribute('target') === '_blank') return;
        if (link.hasAttribute('download')) return;
        if (link.hasAttribute('data-no-loading') || link.closest('[data-no-loading]')) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.which === 2) return;

        try {
            var url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
        } catch(err) {
            return;
        }

        var text = link.getAttribute('data-loading-text') || 'Memuat Halaman...';
        var sub = link.getAttribute('data-loading-sub') || 'Sedang mengambil data, mohon tunggu sebentar...';

        if (link.href.includes('filter') || link.href.includes('jenjang=') || link.href.includes('kelas=') || link.href.includes('page=')) {
            text = 'Memuat Data...';
            sub = 'Sedang memfilter data yang dipilih...';
        }

        LoadingModal.show(text, sub);
    }, true);

    // 5. jQuery AJAX Hook (if jQuery is loaded on page)
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
