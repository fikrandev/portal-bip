/**
 * Helper Notifikasi (AppNotif) menggunakan SweetAlert2
 * Mendukung tampilan khusus untuk Web dan Mobile
 */

const AppNotif = {
    // Mengecek apakah perangkat adalah mobile (lebar layar <= 768px)
    isMobile: function() {
        return window.innerWidth <= 768;
    },

    /**
     * Konfigurasi Toast untuk Web
     */
    toastMixin: Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-xl shadow-lg border border-slate-100',
            title: 'text-sm font-bold text-slate-800',
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    }),

    /**
     * Konfigurasi Modal Center untuk Mobile / Desktop
     */
    modalMixin: Swal.mixin({
        customClass: {
            popup: 'rounded-2xl shadow-2xl',
            title: 'text-lg font-bold text-slate-800',
            htmlContainer: 'text-sm text-slate-600',
            confirmButton: 'px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-colors w-full sm:w-auto',
            cancelButton: 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors w-full sm:w-auto',
            actions: 'flex flex-col sm:flex-row gap-3 w-full sm:w-auto mt-6'
        },
        buttonsStyling: false
    }),

    /**
     * Menampilkan pesan Sukses
     */
    success: function(title, message = '') {
        if (this.isMobile()) {
            this.modalMixin.fire({
                icon: 'success',
                title: title,
                text: message,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
                position: 'bottom'
            });
        } else {
            this.toastMixin.fire({
                icon: 'success',
                title: message ? `${title}: ${message}` : title
            });
        }
    },

    /**
     * Menampilkan pesan Error
     */
    error: function(title, message = '') {
        if (this.isMobile()) {
            this.modalMixin.fire({
                icon: 'error',
                title: title,
                text: message,
                showConfirmButton: true,
                confirmButtonText: 'Tutup',
                position: 'bottom'
            });
        } else {
            this.toastMixin.fire({
                icon: 'error',
                title: message ? `${title}: ${message}` : title,
                timer: 5000
            });
        }
    },

    /**
     * Menampilkan pesan Peringatan
     */
    warning: function(title, message = '') {
        if (this.isMobile()) {
            this.modalMixin.fire({
                icon: 'warning',
                title: title,
                text: message,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
                position: 'bottom'
            });
        } else {
            this.toastMixin.fire({
                icon: 'warning',
                title: message ? `${title}: ${message}` : title
            });
        }
    },

    /**
     * Menampilkan pesan Info
     */
    info: function(title, message = '') {
        if (this.isMobile()) {
            this.modalMixin.fire({
                icon: 'info',
                title: title,
                text: message,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
                position: 'bottom'
            });
        } else {
            this.toastMixin.fire({
                icon: 'info',
                title: message ? `${title}: ${message}` : title
            });
        }
    },

    /**
     * Menampilkan Konfirmasi Hapus / Aksi Kritis
     * @param {Event} e Event object dari tombol form (harus ada agar bisa preventDefault)
     * @param {HTMLElement} formElement Elemen form yang akan di-submit setelah konfirmasi
     * @param {string} title Judul konfirmasi (Opsional)
     * @param {string} text Teks konfirmasi (Opsional)
     */
    confirm: function(e, formElement, title = 'Apakah Anda Yakin?', text = 'Data yang dihapus tidak dapat dikembalikan!') {
        // Hentikan submit langsung
        e.preventDefault();

        this.modalMixin.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true, // Untuk meletakkan Ya di sebelah kanan di desktop
            position: this.isMobile() ? 'bottom' : 'center',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                title: 'text-lg font-bold text-slate-800',
                htmlContainer: 'text-sm text-slate-600',
                actions: 'flex gap-3 mt-6',
                confirmButton: 'px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition-colors',
                cancelButton: 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form secara programatik
                formElement.submit();
            }
        });
    }
};
