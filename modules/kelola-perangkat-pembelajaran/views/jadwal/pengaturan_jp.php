<?php
/**
 * Pengaturan JP & Jam Rutin Harian - View
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                    ← Kembali ke Jadwal
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3 mt-1">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <span>Pengaturan JP & Jam Rutin Harian</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Grup: <strong class="text-slate-800"><?= e($grup['nama_grup']) ?></strong> (<?= e($grup['tahun_ajaran']) ?> - Semester <?= e($grup['semester']) ?>) | Unit: <strong class="text-indigo-700"><?= e($grup['jenjang']) ?></strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/generate/' . $grup['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Buka Generator Jadwal →</span>
            </a>
        </div>
    </div>

    <!-- Quick Form Config JP (Generate Slot Murni KBM Berdasarkan Durasi & Jam Masuk) -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Hitung Otomatis Slot Jam Pelajaran (JP) Murni</h3>
                <p class="text-xs text-slate-500">Tentukan Unit, durasi 1 JP (menit), dan jam mulai. Sistem akan menghitung jam ke-1 s.d. selesai secara berurutan.</p>
            </div>
        </div>

        <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/' . $grup['id']) ?>" method="POST" class="space-y-4">
            <?= CSRF::field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <!-- Unit (Label Readonly) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unit Sekolah</label>
                    <input type="hidden" name="jenjang" value="<?= e($grup['jenjang']) ?>">
                    <div class="px-3.5 py-2 bg-indigo-50 border border-indigo-200/80 rounded-xl flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        <span class="text-xs font-black text-indigo-950 uppercase tracking-wide">Unit <?= e($grup['jenjang']) ?></span>
                        <span class="text-[10px] text-indigo-500 font-medium ml-auto">(Terkunci)</span>
                    </div>
                </div>

                <!-- Durasi 1 JP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Durasi 1 Jam Pelajaran (JP)</label>
                    <div class="relative">
                        <input type="number" name="durasi_jp_menit" value="<?= intval($pengaturanJp['durasi_jp_menit'] ?? 35) ?>" min="15" max="120" required class="w-full pl-3 pr-12 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                        <span class="absolute right-3 top-2 text-xs text-slate-400 font-bold">Mnt</span>
                    </div>
                </div>

                <!-- Jam Mulai KBM -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai KBM</label>
                    <input type="time" name="jam_mulai_kbm" value="<?= e($pengaturanJp['jam_mulai_kbm'] ?? '07:00:00') ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>

                <!-- Jam Selesai KBM -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai KBM</label>
                    <input type="time" name="jam_selesai_kbm" value="16:00:00" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-amber-600/30 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        <span>Generate Slot s.d 16.00</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Template Hari & Slot Jam Rutin (Senin s.d. Sabtu) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Slot Waktu Harian (Senin - Sabtu)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Anda dapat menambahkan agenda khusus (Upacara, Istirahat, Sholat) atau mengedit slot pada masing-masing hari.</p>
            </div>
            <button onclick="openTambahModal('Senin')" class="px-3.5 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-bold border border-indigo-200 transition-colors flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>+ Tambah Agenda Khusus</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php 
            $dayColors = [
                'Senin' => 'from-blue-500 to-indigo-600',
                'Selasa' => 'from-indigo-500 to-purple-600',
                'Rabu' => 'from-purple-500 to-pink-600',
                'Kamis' => 'from-teal-500 to-emerald-600',
                'Jumat' => 'from-emerald-500 to-green-600',
                'Sabtu' => 'from-amber-500 to-orange-600',
            ];
            foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day): 
                $slots = $slotsByDay[$day] ?? [];
            ?>
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                <!-- Card Header -->
                <div class="bg-gradient-to-r <?= $dayColors[$day] ?? 'from-slate-700 to-slate-800' ?> px-5 py-3.5 text-white flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-sm"><?= $day ?></h4>
                        <p class="text-[11px] text-white/80"><?= count($slots) ?> Slot Waktu</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/slot-sync-day/' . $grup['id'] . '/' . $day) ?>" method="POST" class="inline" title="Urutkan & Sinkronkan Timeline Hari <?= $day ?>">
                            <?= CSRF::field() ?>
                            <button type="submit" class="p-1.5 rounded-lg bg-white/20 hover:bg-white/30 text-white transition-colors cursor-pointer" title="Urutkan & Sinkronkan JP Hari <?= $day ?>">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            </button>
                        </form>
                        <button onclick="openTambahModal('<?= $day ?>')" class="px-2 py-1 rounded-lg bg-white/25 hover:bg-white/35 text-white text-[11px] font-extrabold transition-colors cursor-pointer flex items-center gap-1" title="Tambah Agenda Khusus pada hari <?= $day ?>">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            <span>Agenda</span>
                        </button>
                    </div>
                </div>

                <!-- Slot List -->
                <div class="p-4 space-y-2 flex-1 max-h-[420px] overflow-y-auto">
                    <?php if (empty($slots)): ?>
                        <div class="text-center py-8">
                            <p class="text-xs text-slate-400">Belum ada slot waktu pada hari <?= $day ?>.</p>
                            <button onclick="openTambahModal('<?= $day ?>')" class="mt-2 text-xs font-bold text-indigo-600 hover:underline">+ Tambah Slot</button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($slots as $s): ?>
                            <?php 
                            $isKbm = $s['jenis_slot'] === 'kbm';
                            $bgSlot = $isKbm ? 'bg-slate-50 border-slate-200/70 hover:bg-indigo-50/40 hover:border-indigo-200' : 'bg-amber-50/80 border-amber-200 hover:bg-amber-100/60';
                            $textSlot = $isKbm ? 'text-slate-900 font-bold' : 'text-amber-950 font-black';
                            ?>
                            <div class="p-2.5 rounded-xl border <?= $bgSlot ?> flex items-center justify-between text-xs transition-all group">
                                <div class="space-y-0.5 min-w-0 pr-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <?php if ($isKbm): ?>
                                            <span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 text-[10px] font-black">JP <?= $s['jam_ke'] ?></span>
                                        <?php else: ?>
                                            <span class="px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-black uppercase"><?= $s['jenis_slot'] ?></span>
                                        <?php endif; ?>
                                        <span class="<?= $textSlot ?> truncate" title="<?= e($s['label_slot']) ?>"><?= e($s['label_slot']) ?></span>
                                    </div>
                                    <div class="font-mono text-[11px] text-slate-500 font-semibold pl-0.5">
                                        <?= substr($s['jam_mulai'], 0, 5) ?> - <?= substr($s['jam_selesai'], 0, 5) ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 flex-shrink-0">
                                    <button type="button" onclick='openEditModal(<?= json_encode($s) ?>)' class="p-1 text-slate-400 hover:text-indigo-600 rounded transition-colors" title="Edit Slot">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                    </button>
                                    <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/slot-delete/' . $grup['id'] . '/' . $s['id']) ?>" method="POST" onsubmit="return confirm('Hapus slot waktu ini?');" class="inline">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Hapus Slot">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Modal Tambah Agenda Khusus -->
<div id="modalTambahSlot" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                <span>➕</span> Tambah Slot / Agenda Khusus
            </h3>
            <button onclick="closeTambahModal()" class="text-slate-400 hover:text-slate-700 text-lg leading-none cursor-pointer">✕</button>
        </div>

        <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/slot-add/' . $grup['id']) ?>" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>

            <!-- Hari -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Hari</label>
                <select name="hari" id="tambahHari" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500 font-semibold">
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                </select>
            </div>

            <!-- Jenis Slot -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Jenis Slot</label>
                <select name="jenis_slot" id="tambahJenis" onchange="updateLabelSuggestion(this.value)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500 font-semibold">
                    <option value="upacara">Upacara Bendera</option>
                    <option value="istirahat" selected>Istirahat</option>
                    <option value="sholat">Sholat Berjamaah</option>
                    <option value="kegiatan_khusus">Kegiatan Khusus (Dhuha/Senam/Literasi)</option>
                    <option value="kbm">Jam Pelajaran (KBM)</option>
                </select>
            </div>

            <!-- Label Slot -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Agenda / Label Slot</label>
                <input type="text" name="label_slot" id="tambahLabel" required value="Istirahat 1" placeholder="Contoh: Upacara Bendera / Istirahat 1 / Sholat Dzuhur" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500">
            </div>

            <!-- Jam Mulai & Jam Selesai -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" required value="09:30" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" required value="10:00" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeTambahModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-sm shadow-indigo-600/30 cursor-pointer">Simpan Agenda</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Slot -->
<div id="modalEditSlot" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                <span>✏️</span> Edit Slot Waktu
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 text-lg leading-none cursor-pointer">✕</button>
        </div>

        <form id="formEditSlot" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Agenda / Label Slot</label>
                <input type="text" name="label_slot" id="editLabel" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Jenis Slot</label>
                <select name="jenis_slot" id="editJenis" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500 font-semibold">
                    <option value="kbm">Jam Pelajaran (KBM)</option>
                    <option value="upacara">Upacara Bendera</option>
                    <option value="istirahat">Istirahat</option>
                    <option value="sholat">Sholat Berjamaah</option>
                    <option value="kegiatan_khusus">Kegiatan Khusus</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="editMulai" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="editSelesai" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:bg-white focus:border-indigo-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-sm shadow-indigo-600/30 cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTambahModal(hari) {
    document.getElementById('tambahHari').value = hari;
    document.getElementById('modalTambahSlot').classList.remove('hidden');
    document.getElementById('modalTambahSlot').classList.add('flex');
}

function closeTambahModal() {
    document.getElementById('modalTambahSlot').classList.add('hidden');
    document.getElementById('modalTambahSlot').classList.remove('flex');
}

function updateLabelSuggestion(jenis) {
    const label = document.getElementById('tambahLabel');
    if (jenis === 'upacara') label.value = 'Upacara Bendera';
    else if (jenis === 'istirahat') label.value = 'Istirahat 1';
    else if (jenis === 'sholat') label.value = 'Sholat Dzuhur Berjamaah';
    else if (jenis === 'kegiatan_khusus') label.value = "Sholat Dhuha & Muraja'ah";
    else if (jenis === 'kbm') label.value = 'Jam Ke-';
}

function openEditModal(slot) {
    document.getElementById('editLabel').value = slot.label_slot;
    document.getElementById('editJenis').value = slot.jenis_slot;
    document.getElementById('editMulai').value = slot.jam_mulai.substring(0, 5);
    document.getElementById('editSelesai').value = slot.jam_selesai.substring(0, 5);
    document.getElementById('formEditSlot').action = '<?= url("kelola-perangkat-pembelajaran/jadwal/slot-edit/" . $grup["id"]) ?>/' + slot.id;

    document.getElementById('modalEditSlot').classList.remove('hidden');
    document.getElementById('modalEditSlot').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('modalEditSlot').classList.add('hidden');
    document.getElementById('modalEditSlot').classList.remove('flex');
}
</script>
