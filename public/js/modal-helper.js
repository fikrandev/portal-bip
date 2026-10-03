/**
 * Portal BIP - Reusable Modal & Confirmation Helper JS
 * Memberikan antarmuka dialog modal konfirmasi custom yang seragam, elegan,
 * dan terproteksi dari error karakter entitas HTML (&amp;, &#039;, dll).
 */

(function(window, document) {
    'use strict';

    // Helper pembersih & decoding entitas HTML secara menyeluruh (mencegah #&, &#039;, &amp;)
    function cleanHtmlText(str) {
        if (str === null || str === undefined) return '';
        if (typeof str !== 'string') str = String(str);

        // Decode entitas standar
        var txt = document.createElement('textarea');
        txt.innerHTML = str;
        var decoded = txt.value;

        // Antisipasi double-encoding (misal: &amp;#039; atau &amp;amp;)
        if (decoded.indexOf('&amp;') !== -1 || decoded.indexOf('&#') !== -1 || decoded.indexOf('&quot;') !== -1 || decoded.indexOf('&lt;') !== -1) {
            txt.innerHTML = decoded;
            decoded = txt.value;
        }

        return decoded;
    }

    var confirmModalEl = null;
    var activeResolver = null;
    var activeForm = null;
    var activeCallback = null;

    function getOrCreateConfirmModal() {
        if (confirmModalEl && document.body.contains(confirmModalEl)) {
            return confirmModalEl;
        }

        var existing = document.getElementById('portal-custom-confirm-modal');
        if (existing) {
            confirmModalEl = existing;
            return confirmModalEl;
        }

        var modalHtml = `
        <div id="portal-custom-confirm-modal" 
             class="fixed inset-0 z-[999998] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-all duration-200 opacity-0 pointer-events-none"
             role="dialog"
             aria-modal="true"
             aria-labelledby="portal-confirm-title"
             aria-describedby="portal-confirm-message">
            
            <div class="portal-confirm-card relative w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 text-center shadow-2xl border border-slate-100 transform scale-95 transition-all duration-200 overflow-hidden">
                
                <!-- Close X Button -->
                <button type="button" 
                        id="portal-confirm-btn-close" 
                        class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors cursor-pointer"
                        title="Tutup">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Icon Container -->
                <div id="portal-confirm-icon-box" class="w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center border shadow-sm transition-all">
                    <!-- Icon SVG inserted dynamically -->
                </div>

                <!-- Title & Message -->
                <h3 id="portal-confirm-title" class="text-lg font-bold text-slate-800 leading-snug tracking-tight"></h3>
                <p id="portal-confirm-message" class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed font-medium"></p>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-center gap-2.5 sm:gap-3">
                    <button type="button" 
                            id="portal-confirm-btn-cancel" 
                            class="w-full sm:w-1/2 px-4 py-2.5 rounded-2xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition-all shadow-2xs cursor-pointer">
                        Batal
                    </button>
                    <button type="button" 
                            id="portal-confirm-btn-action" 
                            class="w-full sm:w-1/2 px-4 py-2.5 rounded-2xl text-white font-bold text-xs sm:text-sm transition-all shadow-md cursor-pointer flex items-center justify-center gap-2">
                        <span id="portal-confirm-btn-text">Ya, Lanjutkan</span>
                    </button>
                </div>
            </div>
        </div>
        `;

        var wrapper = document.createElement('div');
        wrapper.innerHTML = modalHtml;
        confirmModalEl = wrapper.firstElementChild;
        document.body.appendChild(confirmModalEl);

        // Bind internal buttons
        var btnCancel = confirmModalEl.querySelector('#portal-confirm-btn-cancel');
        var btnClose = confirmModalEl.querySelector('#portal-confirm-btn-close');
        var btnAction = confirmModalEl.querySelector('#portal-confirm-btn-action');

        function doCancel() {
            closeConfirmModal(false);
        }

        btnCancel.addEventListener('click', doCancel);
        btnClose.addEventListener('click', doCancel);

        btnAction.addEventListener('click', function() {
            // Loading state on button
            btnAction.disabled = true;
            var textSpan = btnAction.querySelector('#portal-confirm-btn-text');
            if (textSpan) {
                textSpan.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg> Memproses...
                `;
            }

            var cb = activeCallback;
            var formToSubmit = activeForm;

            closeConfirmModal(true);

            if (typeof cb === 'function') {
                cb();
            } else if (formToSubmit) {
                formToSubmit.submit();
            }
        });

        // Click on backdrop to dismiss
        confirmModalEl.addEventListener('click', function(e) {
            if (e.target === confirmModalEl) {
                doCancel();
            }
        });

        return confirmModalEl;
    }

    function openConfirmModal(config) {
        var m = getOrCreateConfirmModal();
        var card = m.querySelector('.portal-confirm-card');
        var titleEl = m.querySelector('#portal-confirm-title');
        var msgEl = m.querySelector('#portal-confirm-message');
        var iconBox = m.querySelector('#portal-confirm-icon-box');
        var btnAction = m.querySelector('#portal-confirm-btn-action');
        var btnCancel = m.querySelector('#portal-confirm-btn-cancel');
        var btnText = m.querySelector('#portal-confirm-btn-text');

        // Reset button state
        btnAction.disabled = false;
        var defaultActionText = config.type === 'danger' ? 'Ya, Hapus Data' : 'Ya, Lanjutkan';
        btnText.textContent = cleanHtmlText(config.confirmText || defaultActionText);
        btnCancel.textContent = cleanHtmlText(config.cancelText || 'Batal');

        // Set clean text (menghilangkan entitas seperti &#039;, &amp;, dll)
        titleEl.textContent = cleanHtmlText(config.title || 'Konfirmasi Tindakan');
        msgEl.textContent = cleanHtmlText(config.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?');

        // Configure theme / icon based on type
        var type = config.type || 'danger';
        var iconSvg = '';
        var boxClass = '';
        var btnClass = '';

        if (type === 'danger') {
            boxClass = 'bg-rose-50 border-rose-100 text-rose-600 shadow-rose-500/10';
            btnClass = 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/25';
            iconSvg = `<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>`;
        } else if (type === 'warning') {
            boxClass = 'bg-amber-50 border-amber-100 text-amber-600 shadow-amber-500/10';
            btnClass = 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/25';
            iconSvg = `<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>`;
        } else if (type === 'success') {
            boxClass = 'bg-emerald-50 border-emerald-100 text-emerald-600 shadow-emerald-500/10';
            btnClass = 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25';
            iconSvg = `<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>`;
        } else {
            // info / primary
            boxClass = 'bg-primary-50 border-primary-100 text-primary-600 shadow-primary-500/10';
            btnClass = 'bg-primary-600 hover:bg-primary-700 shadow-primary-600/25';
            iconSvg = `<svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>`;
        }

        iconBox.className = 'w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center border transition-all ' + boxClass;
        iconBox.innerHTML = iconSvg;

        btnAction.className = 'w-full sm:w-1/2 px-4 py-2.5 rounded-2xl text-white font-bold text-xs sm:text-sm transition-all cursor-pointer flex items-center justify-center gap-2 ' + btnClass;

        // Tampilkan modal
        m.classList.remove('opacity-0', 'pointer-events-none');
        m.classList.add('opacity-100', 'pointer-events-auto');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }

        document.body.style.overflow = 'hidden';
    }

    function closeConfirmModal(result) {
        if (!confirmModalEl) return;
        var card = confirmModalEl.querySelector('.portal-confirm-card');

        confirmModalEl.classList.remove('opacity-100', 'pointer-events-auto');
        confirmModalEl.classList.add('opacity-0', 'pointer-events-none');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }

        document.body.style.overflow = '';

        if (activeResolver) {
            activeResolver(result);
            activeResolver = null;
        }

        if (!result) {
            activeForm = null;
            activeCallback = null;
        }
    }

    // Objek Utama ModalHelper
    var ModalHelper = {
        /**
         * Pembersih teks dari entitas HTML (menghindari &#039;, &amp;, dll)
         */
        cleanText: cleanHtmlText,

        /**
         * Membuka modal biasa berdasarkan ID elemen
         */
        open: function(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            
            modal.classList.remove('opacity-0', 'pointer-events-none', 'hidden');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            
            var content = modal.querySelector('.portal-modal-content');
            if (content) {
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }
            
            document.body.style.overflow = 'hidden';
        },

        /**
         * Menutup modal biasa berdasarkan ID elemen
         */
        close: function(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
            
            var content = modal.querySelector('.portal-modal-content');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }
            
            document.body.style.overflow = '';
        },

        /**
         * Helper Konfirmasi Modal Custom yang Seragam
         * 
         * Penggunaan:
         * 1. OnSubmit Form:
         *    onsubmit="return ModalHelper.confirm(event, this, 'Hapus Data', 'Yakin?');"
         * 
         * 2. Objek Opsi:
         *    ModalHelper.confirm({
         *        title: 'Hapus Data',
         *        message: 'Apakah Anda yakin?',
         *        type: 'danger',
         *        onConfirm: function() { ... }
         *    });
         * 
         * 3. Promise (Async/Await):
         *    if (await ModalHelper.confirm({ title: '...', message: '...' })) { ... }
         */
        confirm: function(arg1, arg2, arg3, arg4, arg5) {
            // Mode 1: Event & Form signature (arg1 is Event, arg2 is Form)
            if (arg1 && arg1.preventDefault && arg2 && (arg2.nodeName === 'FORM' || typeof arg2.submit === 'function')) {
                arg1.preventDefault();
                activeForm = arg2;
                activeCallback = null;
                openConfirmModal({
                    title: arg3 || 'Konfirmasi Tindakan',
                    message: arg4 || 'Data yang dihapus tidak dapat dikembalikan!',
                    type: arg5 || 'danger'
                });
                return false;
            }

            // Mode 2: Form element passed directly as arg1
            if (arg1 && (arg1.nodeName === 'FORM' || typeof arg1.submit === 'function')) {
                activeForm = arg1;
                activeCallback = null;
                openConfirmModal({
                    title: arg2 || 'Konfirmasi Tindakan',
                    message: arg3 || 'Data yang dihapus tidak dapat dikembalikan!',
                    type: arg4 || 'danger'
                });
                return false;
            }

            // Mode 3: Options Object
            if (typeof arg1 === 'object' && arg1 !== null) {
                activeForm = arg1.form || null;
                activeCallback = arg1.onConfirm || null;
                return new Promise(function(resolve) {
                    activeResolver = resolve;
                    openConfirmModal(arg1);
                });
            }

            // Mode 4: String Title & Message (returns Promise)
            if (typeof arg1 === 'string') {
                activeForm = null;
                activeCallback = null;
                return new Promise(function(resolve) {
                    activeResolver = resolve;
                    openConfirmModal({
                        title: arg1,
                        message: arg2 || '',
                        type: arg3 || 'danger'
                    });
                });
            }

            return false;
        },

        /**
         * Menampilkan modal informasi / alert dengan tampilan seragam
         */
        alert: function(title, message, type) {
            return this.confirm({
                title: title || 'Informasi',
                message: message || '',
                type: type || 'info',
                confirmText: 'Oke',
                cancelText: ''
            });
        },

        onFileSelected: function(input) {
            if (!input || !input.files || input.files.length === 0) return;
            var file = input.files[0];
            var container = input.closest('.portal-dropzone');
            if (!container) return;

            var defaultView = container.querySelector('.dropzone-default');
            var previewView = container.querySelector('.dropzone-file-preview');
            var nameLabel = container.querySelector('.filename-label');
            var sizeLabel = container.querySelector('.filesize-label');

            if (nameLabel) nameLabel.textContent = file.name;
            if (sizeLabel) {
                var sizeKb = (file.size / 1024).toFixed(1);
                sizeLabel.textContent = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
            }

            if (defaultView) defaultView.classList.add('hidden');
            if (previewView) previewView.classList.remove('hidden');
        },

        onSubmit: function(form, event) {
            var btn = form.querySelector('.btn-submit-import');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses Import...
                `;
            }
        }
    };

    // Global Listeners for ESC and Backdrop Click
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (confirmModalEl && confirmModalEl.classList.contains('opacity-100')) {
                closeConfirmModal(false);
            }
            document.querySelectorAll('.portal-modal.opacity-100').forEach(function(m) {
                ModalHelper.close(m.id);
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('portal-modal')) {
            ModalHelper.close(e.target.id);
        }
    });

    // Delegasi otomatis untuk elemen dengan atribut `data-confirm`
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-confirm')) return;
        if (form.__portalConfirmBypassed) {
            delete form.__portalConfirmBypassed;
            return;
        }

        e.preventDefault();
        var message = form.getAttribute('data-confirm');
        var title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
        var type = form.getAttribute('data-confirm-type') || 'danger';
        ModalHelper.confirm(e, form, title, message, type);
    }, true);

    // Expose ke global window
    window.ModalHelper = ModalHelper;

})(window, document);
