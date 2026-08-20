<?php /** Edit User Form */ ?>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8">
        <form action="<?= url('users/update/' . $user['id']) ?>" method="POST" id="form-edit-user">
            <?= CSRF::field() ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="full_name" class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" id="full_name" name="full_name" value="<?= e(old('full_name') ?: $user['full_name']) ?>" required
                           class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label for="username" class="block text-sm font-semibold text-primary-800 mb-1.5">Username <span class="text-rose-500">*</span></label>
                    <input type="text" id="username" name="username" value="<?= e(old('username') ?: $user['username']) ?>" required
                           class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
            </div>
            
            <div class="mb-5">
                <label for="email" class="block text-sm font-semibold text-primary-800 mb-1.5">Email <span class="text-rose-500">*</span></label>
                <input type="email" id="email" name="email" value="<?= e(old('email') ?: $user['email']) ?>" required
                       class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
            </div>

            <div class="mb-5">
                <label for="phone" class="block text-sm font-semibold text-primary-800 mb-1.5">Telepon</label>
                <input type="text" id="phone" name="phone" value="<?= e(old('phone') ?: ($user['phone'] ?? '')) ?>"
                       class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
            </div>
            
            <div class="mb-5">
                <label for="password" class="block text-sm font-semibold text-primary-800 mb-1.5">Password Baru <span class="text-xs text-slate-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                <input type="password" id="password" name="password" minlength="8"
                       class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-primary-800 mb-2">Peran</label>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($roles as $role): ?>
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-primary-200 hover:border-primary-300 has-[:checked]:bg-primary-50 has-[:checked]:border-primary-400 cursor-pointer transition-all text-sm">
                        <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>" <?= in_array($role['id'], $userRoleIds) ? 'checked' : '' ?> class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent ">
                        <span class="text-primary-800"><?= e($role['name']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?> class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent ">
                    <span class="text-sm font-medium text-primary-800">Aktifkan pengguna</span>
                </label>
            </div>
            
            <div class="flex items-center gap-3">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide">
                    Perbarui Pengguna
                </button>
                <a href="<?= url('users') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-full transition-colors text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
