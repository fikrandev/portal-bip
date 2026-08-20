<?php
/**
 * Reset Data View
 */
?>

<div class="max-w-4xl animate-slide-in">
    <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-red-100 bg-red-50/50">
            <h3 class="text-lg font-bold text-red-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Zona Berbahaya - Reset Data
            </h3>
        </div>
        
        <div class="p-6">
            <p class="text-sm text-slate-600 mb-6">Fitur ini akan menghapus semua data operasional secara permanen. Sangat disarankan untuk melakukan *backup* terlebih dahulu sebelum menekan tombol reset.</p>
            
            <form action="<?= url('pengaturan-sistem/reset') ?>" method="POST" id="form-reset-data">
                <?= CSRF::field() ?>
                
                <div class="bg-red-50 rounded-[5px] p-5 border border-red-100 mb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-red-200 pb-3">
                        <label class="text-sm font-bold text-red-800 cursor-pointer flex items-center gap-2">
                            <input type="checkbox" onclick="toggleSelectAll(this)" class="w-4 h-4 text-red-600 border-red-300 rounded focus:ring-red-500">
                            Pilih Semua Data
                        </label>
                    </div>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 text-sm text-slate-700 cursor-pointer hover:bg-red-100/50 p-2 rounded transition-colors">
                            <input type="checkbox" name="tables[]" value="siswa" class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500">
                            Data Siswa & Pendaftaran
                        </label>
                        <label class="flex items-center gap-3 text-sm text-slate-700 cursor-pointer hover:bg-red-100/50 p-2 rounded transition-colors">
                            <input type="checkbox" name="tables[]" value="guru" class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500">
                            Data Guru & Pegawai
                        </label>
                        <label class="flex items-center gap-3 text-sm text-slate-700 cursor-pointer hover:bg-red-100/50 p-2 rounded transition-colors">
                            <input type="checkbox" name="tables[]" value="kelas" class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500">
                            Data Kelas & Rombel
                        </label>
                        <label class="flex items-center gap-3 text-sm text-slate-700 cursor-pointer hover:bg-red-100/50 p-2 rounded transition-colors">
                            <input type="checkbox" name="tables[]" value="users" class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500">
                            Data Pengguna (Kecuali Super Admin)
                        </label>
                        <label class="flex items-center gap-3 text-sm text-slate-700 cursor-pointer hover:bg-red-100/50 p-2 rounded transition-colors">
                            <input type="checkbox" name="tables[]" value="audit_logs" class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500">
                            Riwayat Log Sistem (Audit Logs)
                        </label>
                    </div>
                </div>
                
                <button type="button" onclick="confirmReset(event)" class="w-auto px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-[5px] shadow-sm transition-colors text-sm flex justify-center items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Reset Data Terpilih
                </button>
            </form>
        </div>
    </div>
</div>
