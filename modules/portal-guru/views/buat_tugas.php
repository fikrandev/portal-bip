<?php
/**
 * Buat Tugas Baru Screen
 * Compact size with Button Loading and Smooth Checkmark Modal.
 */
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="font-bold text-slate-800 text-base">Buat Tugas Baru</h2>
    </div>
</div>

<div class="p-4 space-y-4">
    <form onsubmit="submitTaskForm(event)" class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3.5">
        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1">Target Kelas</label>
            <select name="target_class" class="w-full px-3 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ($classes as $c): ?>
                <option value="<?= e($c) ?>"><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1">Judul Tugas / PR</label>
            <input type="text" name="title" placeholder="Contoh: Latihan 3.2 Teorema Pythagoras" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1">Batas Waktu Pengumpulan (Deadline)</label>
            <input type="datetime-local" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-700 mb-1">Petunjuk Pengerjaan</label>
            <textarea rows="3" placeholder="Tuliskan petunjuk pengerjaan tugas di sini..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <button type="submit" id="btn-submit-task" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 press-bounce">
            <i data-lucide="send" class="w-4 h-4"></i> Terbitkan Tugas Sekarang
        </button>
    </form>
</div>

<script>
    function submitTaskForm(event) {
        event.preventDefault();
        const form = event.target;
        const submitBtn = form.querySelector('#btn-submit-task');
        const title = form.querySelector('[name="title"]')?.value || 'Tugas Baru';
        const targetClass = form.querySelector('[name="target_class"]')?.value || 'Kelas';

        AndroidUI.setButtonLoading(submitBtn, 'Menerbitkan Tugas...');

        setTimeout(() => {
            AndroidUI.resetButton(submitBtn);

            AndroidUI.success({
                title: 'Tugas Diterbitkan!',
                subtitle: `${targetClass} • Notifikasi terkirim`,
                message: `Tugas "<strong>${title}</strong>" berhasil dipublikasikan dan dapat diakses siswa.`,
                actions: [
                    {
                        text: 'Ke Beranda',
                        className: 'w-full py-3 bg-emerald-600 text-white font-bold text-xs rounded-2xl shadow-md text-center',
                        onClick: () => window.location.href = '<?= url("mobile") ?>'
                    }
                ]
            });
        }, 700);
    }
</script>
