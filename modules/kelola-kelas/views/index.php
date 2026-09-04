<?php /** Dashboard Kelola Kelas */ ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
            </div>
            <span>Kelola Kelas</span>
        </h1>
        <p class="text-slate-500 text-sm mt-1">Manajemen data kelas, wali kelas, dan pembagian rombongan belajar.</p>
    </div>
        <button type="button" onclick="document.getElementById('modal-sync-dapodik').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span>Sync Dapodik</span>
        </button>
        <button type="button" onclick="document.getElementById('modal-copy-kelas').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
            </svg>
            <span>Salin Kelas</span>
        </button>
        <a href="<?= url('kelola-kelas/create') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary-500/25 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>+ Tambah Kelas</span>
        </a>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-slate-200/60 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center text-primary-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Kelas</p>
            <p class="text-2xl font-bold text-slate-800"><?= number_format($totalKelas) ?></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-200/60 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Kelas Aktif</p>
            <p class="text-2xl font-bold text-slate-800"><?= number_format($totalAktif) ?></p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
        <form action="<?= url('kelola-kelas') ?>" method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <div class="relative min-w-[200px]">
                <select name="ta" onchange="this.form.submit()" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-[10px] text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-medium">
                    <?php foreach ($tahunAkademikList as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= ($ta['id'] == $filterTa) ? 'selected' : '' ?>>T.A: <?= e($ta['nama_tahun']) ?> <?= $ta['is_active'] ? '(Aktif)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="relative w-full sm:w-72">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama kelas atau wali kelas..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-[10px] text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-shadow">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>
            <?php if($search): ?>
                <a href="<?= url('kelola-kelas') ?>?ta=<?= $filterTa ?>" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-[10px] text-sm font-medium transition-colors">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                    <th class="px-6 py-4">Nama Kelas</th>
                    <th class="px-6 py-4">Wali Kelas</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 text-sm">
                <?php if (empty($kelas)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                            Tidak ada data kelas yang ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kelas as $k): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4 font-medium text-slate-900"><?= e($k['nama_kelas']) ?></td>
                        <td class="px-6 py-4 text-slate-600"><?= e($k['wali_kelas'] ?: '-') ?></td>
                        <td class="px-6 py-4">
                            <?php if ($k['is_active']): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif</span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-700"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="<?= url('kelola-kelas/edit/' . $k['id']) ?>" class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </a>
                                <form action="<?= url('kelola-kelas/delete/' . $k['id']) ?>" method="POST" class="inline" onsubmit="AppNotif.confirm(event, this, 'Konfirmasi Tindakan', 'Yakin ingin menghapus kelas ini?');">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Copy Kelas -->
<div id="modal-copy-kelas" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto w-full h-full">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full">
            <form action="<?= url('kelola-kelas/copy') ?>" method="POST">
                <?= CSRF::field() ?>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 mb-1">Salin Data Kelas</h3>
                    <p class="text-sm text-slate-500 mb-5">Fitur ini memungkinkan Anda menduplikasi seluruh kelas dari satu tahun ajaran ke tahun ajaran yang lain.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Dari Tahun Ajaran (Asal)</label>
                            <select name="from_ta" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 text-sm focus:border-primary-500 focus:ring-0">
                                <option value="">-- Pilih Tahun Ajaran Asal --</option>
                                <?php foreach ($tahunAkademikList as $ta): ?>
                                    <option value="<?= $ta['id'] ?>" <?= ($ta['id'] == $filterTa) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="flex justify-center text-slate-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                            </svg>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ke Tahun Ajaran (Tujuan)</label>
                            <select name="to_ta" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 text-sm focus:border-primary-500 focus:ring-0">
                                <option value="">-- Pilih Tahun Ajaran Tujuan --</option>
                                <?php foreach ($tahunAkademikList as $ta): ?>
                                    <option value="<?= $ta['id'] ?>"><?= e($ta['nama_tahun']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-amber-600 mt-2">* Kelas yang memiliki nama yang sama di tahun tujuan akan dilewati (tidak duplikat ganda).</p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-4 sm:px-6 flex flex-col sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-[10px] border border-transparent px-4 py-2.5 bg-primary-600 text-base font-bold text-white shadow-sm hover:bg-primary-700 sm:text-sm">
                        Mulai Salin Kelas
                    </button>
                    <button type="button" onclick="document.getElementById('modal-copy-kelas').classList.add('hidden')" class="w-full sm:w-auto inline-flex justify-center rounded-[10px] border border-slate-300 px-4 py-2.5 bg-white text-base font-bold text-slate-700 shadow-sm hover:bg-slate-50 sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sync Dapodik -->
<div id="modal-sync-dapodik" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-sync-dapodik').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200">
            <form action="<?= url('kelola-kelas/sync-dapodik') ?>" method="POST">
                <?= CSRF::field() ?>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">Tarik Kelas dari Dapodik</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Ajaran Kelas</label>
                                    <select name="tahun_akademik_id" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 text-sm focus:border-primary-500 focus:ring-0">
                                        <?php foreach ($tahunAkademikList as $ta): ?>
                                            <option value="<?= $ta['id'] ?>" <?= $ta['id'] == $filterTa ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">URL Web Service Dapodik</label>
                                    <input type="text" name="dapodik_url" value="<?= e($globalSettings['dapodik_url'] ?? 'http://localhost:5774') ?>" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Token Web Service</label>
                                    <input type="text" name="dapodik_token" value="<?= e($globalSettings['dapodik_token'] ?? '') ?>" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">NPSN Sekolah</label>
                                    <input type="text" name="dapodik_npsn" value="<?= e($globalSettings['dapodik_npsn'] ?? '') ?>" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-4 sm:px-6 flex flex-col sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-[10px] border border-transparent px-4 py-2.5 bg-blue-600 text-base font-bold text-white shadow-sm hover:bg-blue-700 sm:text-sm">
                        Mulai Tarik Data
                    </button>
                    <button type="button" onclick="document.getElementById('modal-sync-dapodik').classList.add('hidden')" class="w-full sm:w-auto inline-flex justify-center rounded-[10px] border border-slate-300 px-4 py-2.5 bg-white text-base font-bold text-slate-700 shadow-sm hover:bg-slate-50 sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
