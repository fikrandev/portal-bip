<?php
/**
 * Buat Grup Jadwal Baru - View
 */
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Action Bar -->
    <div class="flex items-center justify-between">
        <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            <span>Kembali ke Daftar Grup Jadwal</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="text-2xl">🗓️</span> Buat Grup & Versi Jadwal Pelajaran Baru
            </h1>
            <p class="text-xs text-slate-500 mt-1">Tentukan nama grup jadwal, tahun ajaran, durasi 1 JP (menit), dan hubungkan dengan penugasan guru.</p>
        </div>

        <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/store') ?>" method="POST" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Nama Grup Jadwal -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Versi / Grup Jadwal <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_grup" required placeholder="Contoh: Jadwal KBM Semester Ganjil 2026/2027 (Versi Utama)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
            </div>

            <!-- Grid: Tahun Ajaran, Semester, Unit -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="tahun_ajaran" value="2026/2027" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="Ganjil" selected>Semester Ganjil</option>
                        <option value="Genap">Semester Genap</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Unit <span class="text-rose-500">*</span></label>
                    <select name="jenjang" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="PAUD">PAUD</option>
                        <option value="SD" selected>SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                        <option value="SEMUA">Semua Unit</option>
                    </select>
                    <p class="text-[10px] text-indigo-600 mt-1 font-semibold">ℹ️ Rombel/kelas yang dijadwalkan otomatis memuat kelas dari data siswa unit ini.</p>
                </div>
            </div>

            <!-- Inisialisasi Durasi JP & Jam Mulai -->
            <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 space-y-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <h3 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Konfigurasi Awal Jam Pelajaran (JP)</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Durasi 1 JP (Menit) <span class="text-rose-500">*</span></label>
                        <input type="number" name="durasi_jp_menit" id="durasiJpInput" value="35" min="20" max="90" required class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:border-indigo-500 transition-colors">
                        <p class="text-[10px] text-slate-500 mt-1">SD = 35 mnt, SMP = 40 mnt, SMA = 45 mnt, PAUD = 30 mnt.</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Jam Mulai KBM Harian <span class="text-rose-500">*</span></label>
                        <input type="time" name="jam_mulai_kbm" value="07:15:00" required class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:border-indigo-500 transition-colors">
                        <p class="text-[10px] text-slate-500 mt-1">Waktu jam pelajaran ke-1 dimulai setiap pagi.</p>
                    </div>
                </div>
            </div>

            <!-- Hubungkan dengan Grup Penugasan SK Guru -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Sumber Data SK Penugasan Guru (Opsional)</label>
                <select name="penugasan_grup_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                    <option value="">-- Gunakan Semua Data Penugasan Aktif --</option>
                    <?php foreach ($penugasanGrupList as $pg): ?>
                        <option value="<?= $pg['id'] ?>"><?= e($pg['nama_grup']) ?> (SK: <?= e($pg['no_sk'] ?: '-') ?>)</option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Generator akan mengambil data mata pelajaran, guru, kelas, dan jumlah JP dari grup SK yang dipilih.</p>
            </div>

            <!-- Toggle Aktifkan Langsung -->
            <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                <label for="is_active" class="text-xs text-slate-700 font-semibold cursor-pointer">
                    Jadikan grup jadwal ini sebagai <strong>Jadwal Resmi Aktif</strong> untuk seluruh sistem sekolah.
                </label>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan</label>
                <textarea name="keterangan" rows="2" placeholder="Informasi versi jadwal, catatan waka kurikulum..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm shadow-indigo-600/30 transition-all">
                    Simpan & Lanjutkan ke Generator →
                </button>
            </div>
        </form>
    </div>

</div>
