/**
 * Android Material 3 UI Helper for Portal BIP
 * Features:
 * - Robust & Smooth Bottom Sheet Sliding from Bottom
 * - Auto Keyboard Avoidance (Sheet and Action Button stay above virtual keyboard)
 * - Smooth SVG Draw Checkmark Animation for Success
 * - Smooth SVG Draw Cross & Shake for Errors
 * - Animated Shock/Kaget Bounce for Warnings
 * - Button Loading State Helper (Spinner inside buttons on submit/update)
 * - Screen Center Loading Spinner for Data Fetching
 * - Bottom Sheets with Gesture Drag-to-Dismiss
 */

const AndroidUI = (function() {
    function ensureContainer() {
        let container = document.getElementById('android-ui-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'android-ui-container';
            container.className = 'fixed inset-0 z-[9999] pointer-events-none overflow-hidden';
            container.innerHTML = `
                <!-- Backdrop Scrim -->
                <div id="android-ui-scrim" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300" style="transition: opacity 0.3s ease;"></div>
                
                <!-- Bottom Sheet Mount Point -->
                <div id="android-ui-sheet-wrapper" class="fixed inset-x-0 bottom-0 max-w-md mx-auto pointer-events-none z-50" style="transition: bottom 0.25s ease-out;">
                    <div id="android-ui-sheet" style="transform: translateY(105%); transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);" class="bg-white rounded-t-[32px] p-5 shadow-2xl pointer-events-auto border-t border-slate-200/80 safe-bottom">
                        <!-- Drag Handle -->
                        <div class="w-12 h-1.5 bg-slate-300 hover:bg-slate-400 rounded-full mx-auto mb-3.5 cursor-grab active:cursor-grabbing"></div>
                        <!-- Top Animation / Icon Display -->
                        <div id="android-ui-sheet-anim" class="flex justify-center mb-2.5"></div>
                        <!-- Header -->
                        <div id="android-ui-sheet-header" class="text-center mb-2"></div>
                        <!-- Body Content -->
                        <div id="android-ui-sheet-body" class="text-slate-600 text-xs sm:text-sm leading-relaxed mb-4 text-center"></div>
                        <!-- Action Buttons Footer -->
                        <div id="android-ui-sheet-footer" class="flex items-center gap-2.5"></div>
                    </div>
                </div>

                <!-- Center Screen Data Loading Mount Point -->
                <div id="android-ui-center-loading" class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-200">
                    <div class="bg-white/95 backdrop-blur-xl p-5 rounded-3xl shadow-2xl flex flex-col items-center gap-3 border border-slate-100 max-w-[220px] text-center transform scale-95 transition-transform duration-200" id="center-loading-box">
                        <svg class="material-spinner w-10 h-10" viewBox="25 25 50 50">
                            <circle class="material-spinner-circle" cx="50" cy="50" r="20" fill="none" stroke-width="4" stroke-miterlimit="10"/>
                        </svg>
                        <p id="center-loading-text" class="text-xs font-bold text-slate-700">Memuat data...</p>
                    </div>
                </div>

                <!-- Android Snackbar / Toast Mount Point -->
                <div id="android-ui-toast-wrapper" class="fixed bottom-20 sm:bottom-24 inset-x-4 max-w-md mx-auto pointer-events-none flex flex-col items-center gap-2 z-50"></div>
            `;
            document.body.appendChild(container);

            const scrim = document.getElementById('android-ui-scrim');
            if (scrim) {
                scrim.addEventListener('click', () => {
                    AndroidUI.closeBottomSheet();
                });
            }

            setupDragToDismiss();
            setupKeyboardAvoidance();
        }
        return container;
    }

    let currentCloseCallback = null;

    /**
     * Auto Keyboard Avoidance
     * Automatically adjusts the bottom position of the bottom sheet when virtual keyboard opens on mobile.
     */
    function setupKeyboardAvoidance() {
        if (window.visualViewport) {
            const handleViewportChange = () => {
                const wrapper = document.getElementById('android-ui-sheet-wrapper');
                if (!wrapper) return;
                const offset = Math.max(0, window.innerHeight - window.visualViewport.height - (window.visualViewport.offsetTop || 0));
                if (offset > 40) {
                    wrapper.style.bottom = `${offset}px`;
                } else {
                    wrapper.style.bottom = '0px';
                }
            };

            window.visualViewport.addEventListener('resize', handleViewportChange);
            window.visualViewport.addEventListener('scroll', handleViewportChange);
        }
    }

    function setupDragToDismiss() {
        const sheet = document.getElementById('android-ui-sheet');
        if (!sheet) return;

        let startY = 0;
        let currentY = 0;
        let isDragging = false;

        sheet.addEventListener('touchstart', (e) => {
            startY = e.touches[0].clientY;
            isDragging = true;
            sheet.style.transition = 'none';
        }, { passive: true });

        sheet.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentY = e.touches[0].clientY;
            const deltaY = currentY - startY;
            if (deltaY > 0) {
                sheet.style.transform = `translateY(${deltaY}px)`;
            }
        }, { passive: true });

        sheet.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            sheet.style.transition = 'transform 0.3s ease-out';
            const deltaY = currentY - startY;
            if (deltaY > 80) {
                AndroidUI.closeBottomSheet();
            } else {
                sheet.style.transform = 'translateY(0%)';
            }
        });
    }

    return {
        /**
         * Open a custom Android Bottom Sheet with custom animation / icon
         */
        bottomSheet: function(options = {}) {
            ensureContainer();
            const scrim = document.getElementById('android-ui-scrim');
            const sheetWrapper = document.getElementById('android-ui-sheet-wrapper');
            const sheet = document.getElementById('android-ui-sheet');
            const animMount = document.getElementById('android-ui-sheet-anim');
            const header = document.getElementById('android-ui-sheet-header');
            const body = document.getElementById('android-ui-sheet-body');
            const footer = document.getElementById('android-ui-sheet-footer');

            currentCloseCallback = options.onClose || null;

            // Render Animation / Icon
            animMount.innerHTML = '';
            if (options.animType === 'success') {
                animMount.innerHTML = `
                    <div class="relative w-16 h-16 flex items-center justify-center">
                        <svg class="w-16 h-16" viewBox="0 0 52 52">
                            <circle class="checkmark-circle" cx="26" cy="26" r="23"/>
                            <path class="checkmark-check" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                `;
            } else if (options.animType === 'error') {
                animMount.innerHTML = `
                    <div class="relative w-16 h-16 flex items-center justify-center anim-error-shake">
                        <svg class="w-16 h-16" viewBox="0 0 52 52">
                            <circle class="error-circle" cx="26" cy="26" r="23"/>
                            <path class="error-cross-1" d="M16 16 36 36"/>
                            <path class="error-cross-2" d="M36 16 16 36"/>
                        </svg>
                    </div>
                `;
            } else if (options.animType === 'warning') {
                animMount.innerHTML = `
                    <div class="relative w-16 h-16 flex items-center justify-center anim-warning-kaget">
                        <div class="absolute inset-0 rounded-full bg-amber-400/30 warning-pulse-ring"></div>
                        <div class="w-14 h-14 rounded-full bg-amber-100 border-2 border-amber-400 text-amber-600 flex items-center justify-center text-2xl font-black shadow-lg shadow-amber-500/20">
                            !
                        </div>
                    </div>
                `;
            } else if (options.icon) {
                const bg = options.iconBg || 'bg-blue-100 text-blue-600';
                animMount.innerHTML = `<div class="w-14 h-14 rounded-2xl ${bg} flex items-center justify-center text-2xl font-bold shadow-md shadow-blue-500/10">${options.icon}</div>`;
            }

            // Header (Title & Subtitle)
            header.innerHTML = `
                <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug">${options.title || ''}</h3>
                ${options.subtitle ? `<p class="text-xs text-slate-400 mt-0.5 font-medium">${options.subtitle}</p>` : ''}
            `;

            // Body
            if (typeof options.content === 'string') {
                body.innerHTML = options.content;
            } else if (options.content instanceof HTMLElement) {
                body.innerHTML = '';
                body.appendChild(options.content);
            } else {
                body.innerHTML = '';
            }

            // Footer / Actions
            footer.innerHTML = '';
            if (options.actions && Array.isArray(options.actions)) {
                footer.classList.remove('hidden');
                options.actions.forEach(act => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = act.className || 'flex-1 py-3 px-4 rounded-2xl font-bold text-xs shadow-md transition-all active:scale-95 text-center';
                    btn.innerHTML = act.text || 'OK';
                    btn.onclick = (e) => {
                        if (act.onClick) act.onClick(e, btn);
                        if (act.autoClose !== false) AndroidUI.closeBottomSheet();
                    };
                    footer.appendChild(btn);
                });
            } else {
                footer.classList.add('hidden');
            }

            // Reset wrapper bottom position
            if (sheetWrapper) sheetWrapper.style.bottom = '0px';

            // Animate In: Scrim first, then slide up sheet
            scrim.classList.remove('opacity-0', 'pointer-events-none');
            scrim.classList.add('opacity-100', 'pointer-events-auto');
            scrim.style.opacity = '1';
            scrim.style.pointerEvents = 'auto';

            sheetWrapper.classList.remove('pointer-events-none');
            sheetWrapper.classList.add('pointer-events-auto');
            sheetWrapper.style.pointerEvents = 'auto';

            sheet.style.display = 'block';
            sheet.style.visibility = 'visible';

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    sheet.style.transform = 'translateY(0%)';
                });
            });

            // Re-render Lucide icons
            if (window.lucide) window.lucide.createIcons();

            // Haptic feedback
            if (navigator.vibrate) {
                if (options.animType === 'warning') navigator.vibrate([60, 40, 60]);
                else if (options.animType === 'error') navigator.vibrate([40, 40, 40]);
                else navigator.vibrate(30);
            }
        },

        /**
         * Close currently open Bottom Sheet
         */
        closeBottomSheet: function() {
            const scrim = document.getElementById('android-ui-scrim');
            const sheetWrapper = document.getElementById('android-ui-sheet-wrapper');
            const sheet = document.getElementById('android-ui-sheet');

            if (sheet) {
                sheet.style.transform = 'translateY(105%)';
            }
            if (scrim) {
                scrim.classList.remove('opacity-100', 'pointer-events-auto');
                scrim.classList.add('opacity-0', 'pointer-events-none');
                scrim.style.opacity = '0';
                scrim.style.pointerEvents = 'none';
            }
            if (sheetWrapper) {
                sheetWrapper.classList.add('pointer-events-none');
                sheetWrapper.style.pointerEvents = 'none';
                sheetWrapper.style.bottom = '0px';
            }

            if (typeof currentCloseCallback === 'function') {
                currentCloseCallback();
                currentCloseCallback = null;
            }
        },

        /**
         * Smooth Success Bottom Sheet Modal (Smooth Draw Checkmark)
         */
        success: function(options = {}) {
            this.bottomSheet({
                title: options.title || 'Berhasil!',
                subtitle: options.subtitle || null,
                animType: 'success',
                content: `<p class="text-slate-600 text-xs sm:text-sm mt-1">${options.message || 'Tindakan berhasil diselesaikan.'}</p>`,
                actions: options.actions || [
                    {
                        text: options.buttonText || 'Selesai',
                        className: 'w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-emerald-600/30 text-center',
                        onClick: options.onOk
                    }
                ]
            });
        },

        /**
         * Smooth Error Bottom Sheet Modal (Smooth Draw Cross & Shake)
         */
        error: function(options = {}) {
            this.bottomSheet({
                title: options.title || 'Gagal Menyimpan',
                subtitle: options.subtitle || null,
                animType: 'error',
                content: `<p class="text-slate-600 text-xs sm:text-sm mt-1">${options.message || 'Terjadi kesalahan saat memproses data. Silakan coba kembali.'}</p>`,
                actions: [
                    {
                        text: options.buttonText || 'Tutup',
                        className: 'w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-rose-600/30 text-center',
                        onClick: options.onOk
                    }
                ]
            });
        },

        /**
         * Warning Modal Kaget (Shock / Bounce Ring Animation)
         */
        warning: function(options = {}) {
            this.bottomSheet({
                title: options.title || 'Peringatan Penting!',
                subtitle: options.subtitle || null,
                animType: 'warning',
                content: `<p class="text-slate-600 text-xs sm:text-sm mt-1">${options.message || 'Perhatikan data yang Anda masukkan sebelum melanjutkan.'}</p>`,
                actions: options.actions || [
                    {
                        text: options.buttonText || 'Saya Mengerti',
                        className: 'w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-2xl shadow-lg shadow-amber-500/30 text-center',
                        onClick: options.onOk
                    }
                ]
            });
        },

        /**
         * Android Bottom Sheet Confirmation Dialog (with Warning Kaget if danger)
         */
        confirm: function(options = {}) {
            const isDanger = options.type === 'danger' || options.type === 'delete';
            const animType = isDanger ? 'warning' : null;
            const confirmBtnClass = isDanger 
                ? 'flex-1 py-3 px-4 rounded-2xl font-bold text-xs bg-rose-600 hover:bg-rose-700 text-white shadow-lg shadow-rose-600/30 text-center'
                : 'flex-1 py-3 px-4 rounded-2xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/30 text-center';

            this.bottomSheet({
                title: options.title || 'Konfirmasi',
                subtitle: options.subtitle || null,
                animType: animType,
                icon: !animType ? (options.icon || '❓') : null,
                iconBg: !animType ? (options.iconBg || 'bg-blue-100 text-blue-600') : null,
                content: `<p class="text-slate-600 text-xs sm:text-sm mt-1">${options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?'}</p>`,
                actions: [
                    {
                        text: options.cancelText || 'Batal',
                        className: 'flex-1 py-3 px-4 rounded-2xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 text-center',
                        onClick: options.onCancel
                    },
                    {
                        text: options.confirmText || 'Ya, Lanjutkan',
                        className: confirmBtnClass,
                        onClick: options.onConfirm
                    }
                ]
            });
        },

        /**
         * Android Alert Dialog
         */
        alert: function(options = {}) {
            if (options.type === 'success') {
                return this.success(options);
            } else if (options.type === 'error') {
                return this.error(options);
            } else if (options.type === 'warning') {
                return this.warning(options);
            }

            this.bottomSheet({
                title: options.title || 'Pemberitahuan',
                icon: options.icon || 'ℹ',
                iconBg: 'bg-blue-100 text-blue-600',
                content: `<p class="text-slate-600 text-xs sm:text-sm leading-relaxed">${options.message || ''}</p>`,
                actions: [
                    {
                        text: options.buttonText || 'Mengerti',
                        className: 'w-full py-3 px-4 rounded-2xl font-bold text-xs bg-blue-600 text-white shadow-md text-center',
                        onClick: options.onOk
                    }
                ]
            });
        },

        /**
         * Button Loading State Helper
         */
        setButtonLoading: function(btn, loadingText = 'Menyimpan...') {
            if (!btn) return;
            if (!btn.getAttribute('data-original-html')) {
                btn.setAttribute('data-original-html', btn.innerHTML);
            }
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>${loadingText}</span>
            `;
        },

        /**
         * Reset Button Loading State
         */
        resetButton: function(btn) {
            if (!btn) return;
            const original = btn.getAttribute('data-original-html');
            if (original) {
                btn.innerHTML = original;
                btn.removeAttribute('data-original-html');
            }
            btn.disabled = false;
            btn.classList.remove('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
        },

        /**
         * Center Screen Data Loading Spinner (for fetch, refresh, tab data load)
         */
        showCenterLoading: function(message = 'Memuat data...') {
            ensureContainer();
            const mount = document.getElementById('android-ui-center-loading');
            const text = document.getElementById('center-loading-text');
            const box = document.getElementById('center-loading-box');

            if (mount) {
                if (text) text.textContent = message;
                mount.classList.remove('opacity-0', 'pointer-events-none');
                mount.classList.add('opacity-100', 'pointer-events-auto');
                if (box) {
                    box.classList.remove('scale-95');
                    box.classList.add('scale-100');
                }
            }
        },

        /**
         * Hide Center Screen Data Loading Spinner
         */
        hideCenterLoading: function() {
            const mount = document.getElementById('android-ui-center-loading');
            const box = document.getElementById('center-loading-box');

            if (mount) {
                mount.classList.remove('opacity-100', 'pointer-events-auto');
                mount.classList.add('opacity-0', 'pointer-events-none');
                if (box) {
                    box.classList.remove('scale-100');
                    box.classList.add('scale-95');
                }
            }
        },

        /**
         * Android Material 3 Toast
         */
        toast: function(message, type = 'info', duration = 3000) {
            ensureContainer();
            const wrapper = document.getElementById('android-ui-toast-wrapper');
            if (!wrapper) return;

            const toast = document.createElement('div');
            const colors = {
                success: 'bg-slate-900 text-white border-emerald-500/40',
                error: 'bg-slate-900 text-white border-rose-500/40',
                warning: 'bg-slate-900 text-white border-amber-500/40',
                info: 'bg-slate-900 text-white border-blue-500/40'
            };

            const icons = {
                success: '<span class="text-emerald-400 font-bold">✓</span>',
                error: '<span class="text-rose-400 font-bold">✕</span>',
                warning: '<span class="text-amber-400 font-bold">⚠</span>',
                info: '<span class="text-blue-400 font-bold">ℹ</span>'
            };

            toast.className = `flex items-center gap-2.5 px-4 py-2.5 rounded-2xl shadow-2xl backdrop-blur-md text-xs font-semibold border transition-all duration-300 transform translate-y-6 opacity-0 pointer-events-auto ${colors[type] || colors.info}`;
            toast.innerHTML = `
                <span class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center text-xs shrink-0">${icons[type] || 'ℹ'}</span>
                <span class="flex-1">${message}</span>
            `;

            wrapper.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-6', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            if (navigator.vibrate) navigator.vibrate(type === 'error' ? [40, 40, 40] : [30]);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-6');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        },

        /**
         * Android Material 3 Snackbar with Action
         */
        snackbar: function(message, actionText = 'Tutup', onAction = null) {
            ensureContainer();
            const wrapper = document.getElementById('android-ui-toast-wrapper');
            if (!wrapper) return;

            const sb = document.createElement('div');
            sb.className = 'w-full flex items-center justify-between gap-3 px-4 py-3 bg-slate-900 text-white rounded-2xl shadow-2xl text-xs font-semibold border border-slate-800 transform translate-y-6 opacity-0 transition-all duration-300 pointer-events-auto';
            sb.innerHTML = `
                <span class="flex-1 text-slate-200">${message}</span>
                <button type="button" class="text-blue-400 hover:text-blue-300 font-bold uppercase tracking-wider text-[11px] px-2 py-1">${actionText}</button>
            `;

            const btn = sb.querySelector('button');
            btn.onclick = () => {
                if (onAction) onAction();
                sb.classList.add('opacity-0', 'translate-y-6');
                setTimeout(() => sb.remove(), 300);
            };

            wrapper.appendChild(sb);

            requestAnimationFrame(() => {
                sb.classList.remove('translate-y-6', 'opacity-0');
                sb.classList.add('translate-y-0', 'opacity-100');
            });

            setTimeout(() => {
                if (sb.parentNode) {
                    sb.classList.add('opacity-0', 'translate-y-6');
                    setTimeout(() => sb.remove(), 300);
                }
            }, 4500);
        }
    };
})();

// Global shortcuts
window.AndroidUI = AndroidUI;
window.androidToast = (msg, type) => AndroidUI.toast(msg, type);
window.androidConfirm = (opts) => AndroidUI.confirm(opts);
window.androidAlert = (opts) => AndroidUI.alert(opts);
window.androidSuccess = (opts) => AndroidUI.success(opts);
window.androidError = (opts) => AndroidUI.error(opts);
window.androidWarning = (opts) => AndroidUI.warning(opts);
window.androidBottomSheet = (opts) => AndroidUI.bottomSheet(opts);

window.showToast = function(msg, type = 'info') {
    AndroidUI.toast(msg, type);
};
