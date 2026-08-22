/**
 * Portal BIP - Reusable Modal Helper JS
 */
window.ModalHelper = {
    open: function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
        
        const content = modal.querySelector('.portal-modal-content');
        if (content) {
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }
        
        document.body.style.overflow = 'hidden';
    },

    close: function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        
        modal.classList.remove('opacity-100', 'pointer-events-auto');
        modal.classList.add('opacity-0', 'pointer-events-none');
        
        const content = modal.querySelector('.portal-modal-content');
        if (content) {
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
        }
        
        document.body.style.overflow = '';
    },

    onFileSelected: function(input) {
        if (!input || !input.files || input.files.length === 0) return;
        const file = input.files[0];
        const container = input.closest('.portal-dropzone');
        if (!container) return;

        const defaultView = container.querySelector('.dropzone-default');
        const previewView = container.querySelector('.dropzone-file-preview');
        const nameLabel = container.querySelector('.filename-label');
        const sizeLabel = container.querySelector('.filesize-label');

        if (nameLabel) nameLabel.textContent = file.name;
        if (sizeLabel) {
            const sizeKb = (file.size / 1024).toFixed(1);
            sizeLabel.textContent = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
        }

        if (defaultView) defaultView.classList.add('hidden');
        if (previewView) previewView.classList.remove('hidden');
    },

    onSubmit: function(form, event) {
        const btn = form.querySelector('.btn-submit-import');
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
