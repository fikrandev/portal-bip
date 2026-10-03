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

        // Decode entitas standar menggunakan textarea
        var txt = document.createElement('textarea');
        txt.innerHTML = str;
        var decoded = txt.value;

        // Antisipasi double-encoding (misal: &amp;#039; atau &amp;amp;)
        if (decoded.indexOf('&amp;') !== -1 || decoded.indexOf('&#') !== -1 || decoded.indexOf('&quot;') !== -1 || decoded.indexOf('&lt;') !== -1) {
            txt.innerHTML = decoded;
            decoded = txt.value;
        }

        // Antisipasi sisa entitas umum
        decoded = decoded
            .replace(/&#039;/g, "'")
            .replace(/&apos;/g, "'")
            .replace(/&quot;/g, '"')
            .replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>');

        return decoded;
    }

    // Pastikan styling swal2 selalu berada di atas semua modal lain (termasuk loading modal)
    function ensureSwalStyle() {
        if (document.getElementById('portal-swal-custom-style')) return;
        var st = document.createElement('style');
        st.id = 'portal-swal-custom-style';
        st.textContent = `
            .swal2-container {
                z-index: 10000000 !important;
            }
            .portal-swal-popup {
                border-radius: 1.5rem !important;
                padding: 1.75rem !important;
                border: 1px solid #f1f5f9 !important;
                box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
            }
            .portal-swal-title {
                font-size: 1.15rem !important;
                font-weight: 800 !important;
                color: #1e293b !important;
                padding: 0 !important;
                margin-top: 0.5rem !important;
            }
            .portal-swal-html {
                font-size: 0.875rem !important;
                color: #64748b !important;
                margin-top: 0.5rem !important;
                line-height: 1.5 !important;
            }
            .portal-swal-actions {
                margin-top: 1.5rem !important;
                gap: 0.75rem !important;
                width: 100% !important;
            }
        `;
        document.head.appendChild(st);
    }

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
         */
        confirm: function(arg1, arg2, arg3, arg4, arg5) {
            ensureSwalStyle();

            // Sembunyikan loading modal jika sedang terbuka agar tidak menutupi dialog konfirmasi
            if (window.LoadingModal && typeof window.LoadingModal.hide === 'function') {
                window.LoadingModal.hide();
            }

            var eventObj = null;
            var formElement = null;
            var title = 'Konfirmasi Tindakan';
            var message = 'Data yang dihapus tidak dapat dikembalikan!';
            var type = 'danger';
            var confirmText = '';
            var cancelText = 'Batal';
            var onConfirmCb = null;

            // Deteksi parameter
            // Case 1: confirm(event, form, title, message, type)
            if (arg1 && typeof arg1.preventDefault === 'function') {
                eventObj = arg1;
                eventObj.preventDefault();
                formElement = arg2;
                if (arg3) title = arg3;
                if (arg4) message = arg4;
                if (arg5) type = arg5;
            }
            // Case 2: confirm(form, title, message, type)
            else if (arg1 && (arg1.nodeName === 'FORM' || typeof arg1.submit === 'function')) {
                formElement = arg1;
                if (arg2) title = arg2;
                if (arg3) message = arg3;
                if (arg4) type = arg4;
            }
            // Case 3: confirm({ title, message, type, ... })
            else if (typeof arg1 === 'object' && arg1 !== null) {
                if (arg1.event && typeof arg1.event.preventDefault === 'function') {
                    arg1.event.preventDefault();
                }
                formElement = arg1.form || null;
                title = arg1.title || title;
                message = arg1.message || message;
                type = arg1.type || type;
                confirmText = arg1.confirmText || '';
                cancelText = arg1.cancelText || 'Batal';
                onConfirmCb = arg1.onConfirm || null;
            }
            // Case 4: confirm(title, message, type)
            else if (typeof arg1 === 'string') {
                title = arg1;
                if (arg2) message = arg2;
                if (arg3) type = arg3;
            }

            // Bersihkan entitas HTML
            title = cleanHtmlText(title);
            message = cleanHtmlText(message);

            if (!confirmText) {
                confirmText = type === 'danger' ? 'Ya, Hapus Data' : 'Ya, Lanjutkan';
            }

            // Tentukan ikon & warna sesuai tipe
            var iconType = 'warning';
            var iconColor = '#f59e0b';
            var btnConfirmClass = 'px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition-colors cursor-pointer w-full sm:w-auto shadow-md shadow-rose-600/20';

            if (type === 'danger') {
                iconType = 'warning';
                iconColor = '#e11d48';
                btnConfirmClass = 'px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition-colors cursor-pointer w-full sm:w-auto shadow-md shadow-rose-600/20';
            } else if (type === 'warning') {
                iconType = 'warning';
                iconColor = '#d97706';
                btnConfirmClass = 'px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition-colors cursor-pointer w-full sm:w-auto shadow-md shadow-amber-600/20';
            } else if (type === 'success') {
                iconType = 'success';
                iconColor = '#059669';
                btnConfirmClass = 'px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition-colors cursor-pointer w-full sm:w-auto shadow-md shadow-emerald-600/20';
            } else {
                iconType = 'info';
                iconColor = '#2563eb';
                btnConfirmClass = 'px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-colors cursor-pointer w-full sm:w-auto shadow-md shadow-primary-600/20';
            }

            // Jika SweetAlert2 tersedia (standar Portal BIP)
            if (typeof Swal !== 'undefined') {
                return new Promise(function(resolve) {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: iconType,
                        iconColor: iconColor,
                        showCancelButton: true,
                        confirmButtonText: confirmText,
                        cancelButtonText: cancelText,
                        reverseButtons: true,
                        buttonsStyling: false,
                        focusCancel: true,
                        customClass: {
                            popup: 'portal-swal-popup',
                            title: 'portal-swal-title',
                            htmlContainer: 'portal-swal-html',
                            actions: 'portal-swal-actions',
                            confirmButton: btnConfirmClass,
                            cancelButton: 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors cursor-pointer w-full sm:w-auto'
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            if (window.LoadingModal && typeof window.LoadingModal.show === 'function') {
                                var actText = type === 'danger' ? 'Menghapus Data...' : 'Memproses...';
                                window.LoadingModal.show(actText, 'Sistem sedang memperbarui data...');
                            }

                            if (typeof onConfirmCb === 'function') {
                                onConfirmCb();
                            } else if (formElement && typeof formElement.submit === 'function') {
                                formElement.submit();
                            }
                            resolve(true);
                        } else {
                            resolve(false);
                        }
                    });
                });
            }

            // Fallback native confirm jika Swal belum terload
            var confirmed = window.confirm(title + '\n\n' + message);
            if (confirmed) {
                if (typeof onConfirmCb === 'function') {
                    onConfirmCb();
                } else if (formElement && typeof formElement.submit === 'function') {
                    formElement.submit();
                }
                return true;
            }
            return false;
        },

        alert: function(title, message, type) {
            title = cleanHtmlText(title || 'Informasi');
            message = cleanHtmlText(message || '');
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    title: title,
                    text: message,
                    icon: type || 'info',
                    confirmButtonText: 'Oke',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'portal-swal-popup',
                        title: 'portal-swal-title',
                        htmlContainer: 'portal-swal-html',
                        confirmButton: 'px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-colors cursor-pointer'
                    }
                });
            }
            window.alert(title + '\n\n' + message);
        },

        onFileSelected: function(input) {
            if (!input || !input.files || input.files.length === 0) return;
            var file = input.files[0];
            var container = input.closest('.portal-dropzone');
            if (!container) return;

            var defaultView = container.querySelector('.portal-dropzone-default');
            var previewView = container.querySelector('.portal-dropzone-preview');
            var nameLabel = container.querySelector('.portal-file-name');
            var sizeLabel = container.querySelector('.portal-file-size');

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

    // Global Listeners for ESC and Backdrop Click on portal-modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
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

    // Expose ke global window
    window.ModalHelper = ModalHelper;

})(window, document);
