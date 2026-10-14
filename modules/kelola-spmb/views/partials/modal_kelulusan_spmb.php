<?php
/**
 * Modal Kelulusan & Pengumuman SPMB (Terima Massal & Broadcast WA)
 * Portal BIP - SIT Bina Insan Palu
 */
?>
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- 1. MODAL TERIMA MASSAL (BULK ACCEPTANCE)                                    -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalBulkAccept" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Header -->
        <div class="px-6 py-4.5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white shrink-0 text-lg shadow-inner">
                    🎓
                </div>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight">Penetapan Siswa Diterima</h3>
                    <p class="text-[11px] text-emerald-100 mt-0.5">Ubah status seleksi menjadi "Lulus / Diterima" secara massal</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalBulkAccept()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 text-xs text-slate-700">
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-start gap-3">
                <span class="text-xl">💡</span>
                <div class="space-y-1">
                    <strong class="text-emerald-900 font-bold block text-sm">Metode Penerimaan Siswa</strong>
                    <p class="text-emerald-800 leading-relaxed text-[11px]">
                        Karena seluruh calon siswa yang telah mengembalikan berkas dan diverifikasi lengkap diterima, Anda dapat memilih metode penetapan di bawah ini:
                    </p>
                </div>
            </div>

            <!-- Pilihan Metode -->
            <div class="space-y-2.5">
                <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-emerald-500 cursor-pointer bg-white transition-all shadow-2xs">
                    <input type="radio" name="opt_accept_mode" value="lengkap" checked class="mt-0.5 w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <strong class="text-slate-900 block font-bold">1. Terima SEMUA yang Berkasnya Lengkap (Rekomendasi)</strong>
                        <span class="text-slate-500 text-[11px] block mt-0.5">
                            Otomatis menetapkan seluruh calon siswa yang status berkasnya sudah <strong>"Lengkap"</strong> menjadi <strong>"Lulus / Diterima"</strong>.
                        </span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-emerald-500 cursor-pointer bg-white transition-all shadow-2xs">
                    <input type="radio" name="opt_accept_mode" value="selected" class="mt-0.5 w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <strong class="text-slate-900 block font-bold">2. Hanya Calon Siswa yang Dicentanng (<span id="txtJumlahTerpilih">0</span> Siswa Terpilih)</strong>
                        <span class="text-slate-500 text-[11px] block mt-0.5">
                            Hanya menetapkan calon siswa yang sedang dicentang pada kotak checklist tabel.
                        </span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5">
            <button type="button" onclick="tutupModalBulkAccept()" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold hover:bg-slate-100 transition-colors text-xs">
                Batal
            </button>
            <button type="button" id="btnProsesBulkAccept" onclick="eksekusiBulkAccept()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold shadow-md shadow-emerald-600/20 text-xs transition-all flex items-center gap-2">
                <span>✓</span>
                <span>Tetapkan Siswa Diterima</span>
            </button>
        </div>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- 2. MODAL PENGUMUMAN & BROADCAST WHATSAPP                                    -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalBroadcastKelulusan" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col my-auto max-h-[92vh] animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Header -->
        <div class="px-6 py-4.5 bg-gradient-to-r from-sky-700 via-blue-700 to-indigo-800 text-white flex items-center justify-between shrink-0 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white shrink-0 text-lg shadow-inner">
                    📢
                </div>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight">Pengumuman & Broadcast Kelulusan SPMB</h3>
                    <p class="text-[11px] text-sky-100 mt-0.5">Kirim pengumuman resmi ke grup WhatsApp orang tua atau kirim pesan japri personal</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalBroadcastKelulusan()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Tab Header -->
        <div class="flex border-b border-slate-200 bg-slate-50 px-6 pt-2 gap-2 shrink-0">
            <button type="button" onclick="switchBcTab('grup')" id="tabBtnBc_grup" class="px-4 py-2.5 text-xs font-bold border-b-2 border-sky-600 text-sky-700 flex items-center gap-2">
                <span>💬</span>
                <span>Teks Pengumuman Grup WA</span>
            </button>
            <button type="button" onclick="switchBcTab('japri')" id="tabBtnBc_japri" class="px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2">
                <span>📲</span>
                <span>Broadcast Japri Personal</span>
            </button>
            <button type="button" onclick="switchBcTab('pdf')" id="tabBtnBc_pdf" class="px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2">
                <span>📄</span>
                <span>Cetak / Unduh Dokumen PDF</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto flex-1 space-y-5 text-xs custom-scrollbar">
            
            <!-- ── TAB 1: FORMAT TEKS GRUP WA ── -->
            <div id="bcTab_grup" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-sky-50 rounded-2xl border border-sky-200">
                    <div class="space-y-0.5">
                        <strong class="text-sky-950 font-bold text-xs block">Salin Teks Resmi untuk Grup WhatsApp</strong>
                        <p class="text-[11px] text-sky-800">
                            Pilih jenjang untuk menampilkan daftar nama siswa yang diterima, lalu klik tombol <strong>Salin Teks</strong>.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="bc_select_jenjang" onchange="muatTeksPengumumanGrup()" class="px-3 py-1.5 rounded-xl border border-sky-300 bg-white font-bold text-xs text-sky-900 outline-none">
                            <option value="SD">Unit SD IT</option>
                            <option value="PAUD">Unit PAUD IT</option>
                            <option value="SMP">Unit SMP IT</option>
                            <option value="SMA">Unit SMA IT</option>
                        </select>
                        <button type="button" onclick="salinTeksGrupDariModal()" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all">
                            <span>📋</span>
                            <span>Salin Teks</span>
                        </button>
                    </div>
                </div>

                <!-- Textarea Preview -->
                <div class="relative">
                    <textarea id="bc_preview_teks" rows="13" readonly 
                              class="w-full p-4 rounded-2xl border border-slate-200 bg-slate-50/80 font-mono text-[11px] text-slate-800 leading-relaxed outline-none focus:ring-2 focus:ring-sky-200 resize-none custom-scrollbar"></textarea>
                    <div id="bc_loading_teks" class="absolute inset-0 bg-white/70 backdrop-blur-2xs rounded-2xl flex items-center justify-center hidden">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                            <div class="w-4 h-4 border-2 border-sky-600 border-t-transparent rounded-full animate-spin"></div>
                            <span>Memuat teks pengumuman...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB 2: BROADCAST JAPRI KE NOMOR ORANG TUA ── -->
            <div id="bcTab_japri" class="space-y-4 hidden">
                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 flex items-start gap-3">
                    <span class="text-xl">⚠️</span>
                    <div class="space-y-1">
                        <strong class="text-amber-950 font-bold block text-xs">Pemberitahuan Broadcast Japri</strong>
                        <p class="text-amber-800 text-[11px] leading-relaxed">
                            Pesan WhatsApp resmi akan dikirimkan satu per satu ke nomor orang tua/wali dari seluruh calon murid baru yang statusnya <strong>"Lulus / Diterima"</strong>. Pastikan server WA Gateway aktif.
                        </p>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800 text-xs">Target Calon Siswa Diterima:</span>
                        <span id="txtTargetJapriCount" class="font-extrabold text-sky-700 bg-sky-100 px-2.5 py-0.5 rounded-md text-xs">0 Siswa</span>
                    </div>

                    <!-- Progress Bar -->
                    <div id="bc_progress_box" class="space-y-1.5 hidden">
                        <div class="flex justify-between text-[11px] font-semibold text-slate-600">
                            <span id="bc_progress_label">Mengirim pesan...</span>
                            <span id="bc_progress_pct">0%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div id="bc_progress_bar" class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <div id="bc_log_result" class="max-h-36 overflow-y-auto p-2 bg-white rounded-xl border border-slate-200 font-mono text-[10px] text-slate-600 space-y-1 hidden custom-scrollbar">
                    </div>
                </div>

                <div class="text-right">
                    <button type="button" id="btnMulaiBroadcastJapri" onclick="mulaiBroadcastJapri()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold shadow-md shadow-emerald-600/20 text-xs transition-all flex items-center gap-2 ml-auto">
                        <span>📲</span>
                        <span>Mulai Kirim WhatsApp ke Semua Ortu</span>
                    </button>
                </div>
            </div>

            <!-- ── TAB 3: UNDUH DOKUMEN PDF RESMI ── -->
            <div id="bcTab_pdf" class="space-y-4 hidden">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div>
                        <strong class="text-slate-900 block font-bold text-xs">Cetak & Unduh Dokumen Pengumuman Resmi (PDF)</strong>
                        <p class="text-slate-500 text-[11px] mt-0.5">
                            Dokumen lengkap ber-kop unit resmi, nomor Surat Keputusan, tabel pendaftar diterima, serta pengesahan panitia.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <!-- PAUD -->
                        <a href="<?= url('kelola-spmb/pengumuman-kelulusan/cetak?jenjang=PAUD') ?>" target="_blank" 
                           class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 bg-white hover:border-sky-500 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl group-hover:scale-110 transition-transform">🎨</span>
                                <div>
                                    <strong class="text-slate-800 block text-xs">Unit PAUD IT</strong>
                                    <span class="text-[10px] text-slate-400">Pengumuman Kelulusan Resmi</span>
                                </div>
                            </div>
                            <span class="text-sky-600 font-bold text-xs">Buka ↗</span>
                        </a>

                        <!-- SD -->
                        <a href="<?= url('kelola-spmb/pengumuman-kelulusan/cetak?jenjang=SD') ?>" target="_blank" 
                           class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 bg-white hover:border-sky-500 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl group-hover:scale-110 transition-transform">📚</span>
                                <div>
                                    <strong class="text-slate-800 block text-xs">Unit SD IT</strong>
                                    <span class="text-[10px] text-slate-400">Pengumuman Kelulusan Resmi</span>
                                </div>
                            </div>
                            <span class="text-sky-600 font-bold text-xs">Buka ↗</span>
                        </a>

                        <!-- SMP -->
                        <a href="<?= url('kelola-spmb/pengumuman-kelulusan/cetak?jenjang=SMP') ?>" target="_blank" 
                           class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 bg-white hover:border-sky-500 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl group-hover:scale-110 transition-transform">🔬</span>
                                <div>
                                    <strong class="text-slate-800 block text-xs">Unit SMP IT</strong>
                                    <span class="text-[10px] text-slate-400">Pengumuman Kelulusan Resmi</span>
                                </div>
                            </div>
                            <span class="text-sky-600 font-bold text-xs">Buka ↗</span>
                        </a>

                        <!-- SMA -->
                        <a href="<?= url('kelola-spmb/pengumuman-kelulusan/cetak?jenjang=SMA') ?>" target="_blank" 
                           class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 bg-white hover:border-sky-500 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl group-hover:scale-110 transition-transform">🏛️</span>
                                <div>
                                    <strong class="text-slate-800 block text-xs">Unit SMA IT</strong>
                                    <span class="text-[10px] text-slate-400">Pengumuman Kelulusan Resmi</span>
                                </div>
                            </div>
                            <span class="text-sky-600 font-bold text-xs">Buka ↗</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <span class="text-[11px] text-slate-500">Yayasan Bina Insan Palu &bull; Portal BIP</span>
            <button type="button" onclick="tutupModalBroadcastKelulusan()" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    // ═════════════════════════════════════════════════════════════════════════
    // JS MODAL BULK ACCEPT
    // ═════════════════════════════════════════════════════════════════════════
    function bukaModalBulkAccept() {
        const checked = getSelectedPendaftarIds();
        const txt = document.getElementById('txtJumlahTerpilih');
        if (txt) txt.innerText = checked.length;

        // Auto select radio jika ada centang
        const radios = document.querySelectorAll('input[name="opt_accept_mode"]');
        if (checked.length > 0) {
            radios.forEach(r => { if (r.value === 'selected') r.checked = true; });
        } else {
            radios.forEach(r => { if (r.value === 'lengkap') r.checked = true; });
        }

        const modal = document.getElementById('modalBulkAccept');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function tutupModalBulkAccept() {
        const modal = document.getElementById('modalBulkAccept');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function getSelectedPendaftarIds() {
        const cbs = document.querySelectorAll('.cb-pendaftar-item:checked');
        return Array.from(cbs).map(cb => parseInt(cb.value)).filter(id => !isNaN(id));
    }

    function toggleSelectAll(masterCb) {
        document.querySelectorAll('.cb-pendaftar-item').forEach(cb => {
            cb.checked = masterCb.checked;
        });
        updateBulkBarState();
    }

    function updateBulkBarState() {
        const checked = getSelectedPendaftarIds();
        const bar = document.getElementById('selectedActionsBar');
        const countTxt = document.getElementById('selectedItemsCount');
        if (countTxt) countTxt.innerText = checked.length;
        if (bar) {
            if (checked.length > 0) {
                bar.classList.remove('hidden');
            } else {
                bar.classList.add('hidden');
            }
        }
    }

    function eksekusiBulkAccept() {
        const mode = document.querySelector('input[name="opt_accept_mode"]:checked')?.value || 'lengkap';
        const selectedIds = getSelectedPendaftarIds();

        if (mode === 'selected' && selectedIds.length === 0) {
            alert('Silakan centang minimal 1 calon siswa pada tabel terlebih dahulu.');
            return;
        }

        const btn = document.getElementById('btnProsesBulkAccept');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Menyimpan...';
        }

        const currentPath = window.location.pathname;
        const isPindahan = currentPath.includes('/pindahan');
        const endpoint = isPindahan 
            ? '<?= url('kelola-spmb/pindahan/bulk-status') ?>' 
            : '<?= url('kelola-spmb/siswa-baru/bulk-status') ?>';

        const formData = new FormData();
        formData.append('csrf_token', '<?= CSRF::token() ?>');
        formData.append('mode', mode);
        if (mode === 'selected') {
            selectedIds.forEach(id => formData.append('ids[]', id));
        }

        fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert('✓ ' + (res.message || 'Status kelulusan berhasil diperbarui!'));
                window.location.reload();
            } else {
                alert('Gagal: ' + (res.message || 'Terjadi kesalahan sistem.'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<span>✓</span><span>Tetapkan Siswa Diterima</span>';
                }
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan atau server.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span>✓</span><span>Tetapkan Siswa Diterima</span>';
            }
        });
    }

    // ═════════════════════════════════════════════════════════════════════════
    // JS MODAL BROADCAST & PENGUMUMAN
    // ═════════════════════════════════════════════════════════════════════════
    function bukaModalBroadcastKelulusan() {
        const modal = document.getElementById('modalBroadcastKelulusan');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        switchBcTab('grup');
        muatTeksPengumumanGrup();
    }

    function tutupModalBroadcastKelulusan() {
        const modal = document.getElementById('modalBroadcastKelulusan');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function switchBcTab(tab) {
        ['grup', 'japri', 'pdf'].forEach(t => {
            const btn = document.getElementById('tabBtnBc_' + t);
            const content = document.getElementById('bcTab_' + t);
            if (btn && content) {
                if (t === tab) {
                    btn.classList.add('border-sky-600', 'text-sky-700');
                    btn.classList.remove('border-transparent', 'text-slate-500');
                    content.classList.remove('hidden');
                } else {
                    btn.classList.remove('border-sky-600', 'text-sky-700');
                    btn.classList.add('border-transparent', 'text-slate-500');
                    content.classList.add('hidden');
                }
            }
        });
    }

    function muatTeksPengumumanGrup() {
        const jenjang = document.getElementById('bc_select_jenjang')?.value || 'SD';
        const textarea = document.getElementById('bc_preview_teks');
        const loading = document.getElementById('bc_loading_teks');

        if (loading) loading.classList.remove('hidden');

        fetch('<?= url('kelola-spmb/pengumuman-kelulusan/get-teks-wa') ?>?jenjang=' + encodeURIComponent(jenjang))
        .then(r => r.json())
        .then(res => {
            if (textarea && res.text) {
                textarea.value = res.text;
            }
        })
        .catch(() => {})
        .finally(() => {
            if (loading) loading.classList.add('hidden');
        });
    }

    function salinTeksGrupDariModal() {
        const textarea = document.getElementById('bc_preview_teks');
        if (!textarea || !textarea.value) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textarea.value).then(() => {
                alert('✓ Berhasil menyalin Teks Pengumuman Grup!\n\nSilakan langsung paste (tempel) di grup WhatsApp orang tua.');
            }).catch(() => fallbackCopy(textarea.value));
        } else {
            fallbackCopy(textarea.value);
        }
    }

    function mulaiBroadcastJapri() {
        if (!confirm('Apakah Anda yakin ingin mengirim pesan WhatsApp pengumuman DITERIMA ke seluruh orang tua calon siswa yang telah lulus seleksi?')) {
            return;
        }

        const btn = document.getElementById('btnMulaiBroadcastJapri');
        const pBox = document.getElementById('bc_progress_box');
        const pBar = document.getElementById('bc_progress_bar');
        const pPct = document.getElementById('bc_progress_pct');
        const logBox = document.getElementById('bc_log_result');

        if (btn) btn.disabled = true;
        if (pBox) pBox.classList.remove('hidden');
        if (logBox) {
            logBox.classList.remove('hidden');
            logBox.innerHTML = '<div class="text-sky-600">Memulai proses pengiriman antrean WhatsApp...</div>';
        }

        const formData = new FormData();
        formData.append('csrf_token', '<?= CSRF::token() ?>');

        fetch('<?= url('kelola-spmb/kirim-wa-kelulusan-bulk') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (pBar) pBar.style.width = '100%';
            if (pPct) pPct.innerText = '100%';
            if (logBox) {
                logBox.innerHTML += `<div class="text-emerald-700 font-bold mt-1">✓ ${res.message || 'Selesai.'}</div>`;
                if (res.results && Array.isArray(res.results)) {
                    res.results.forEach(item => {
                        const icon = item.success ? '✓' : '✗';
                        const color = item.success ? 'text-emerald-600' : 'text-rose-600';
                        logBox.innerHTML += `<div class="${color}">[${icon}] ${item.nama} (${item.phone}): ${item.message || 'OK'}</div>`;
                    });
                }
            }
            alert('✓ ' + (res.message || 'Pengiriman WhatsApp selesai!'));
        })
        .catch(err => {
            alert('Terjadi kesalahan saat memproses broadcast.');
            if (logBox) logBox.innerHTML += '<div class="text-rose-600">Gagal memproses permintaan broadcast.</div>';
        })
        .finally(() => {
            if (btn) btn.disabled = false;
        });
    }

    function kirimWaKelulusanSingle(id, nama) {
        if (!confirm(`Kirim pesan WhatsApp pemberitahuan DITERIMA ke orang tua ananda ${nama}?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('csrf_token', '<?= CSRF::token() ?>');

        fetch('<?= url('kelola-spmb/kirim-wa-kelulusan/') ?>' + id, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert('✓ ' + (res.message || 'WhatsApp kelulusan berhasil dikirim!'));
                window.location.reload();
            } else {
                alert('Gagal mengirim: ' + (res.message || 'Kesalahan server'));
            }
        })
        .catch(() => alert('Terjadi kesalahan koneksi saat mengirim WhatsApp.'));
    }
</script>
