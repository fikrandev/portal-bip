<?php
/**
 * Beranda Guru (Mobile Dashboard)
 * Clean Card-based Ibadah Harian: items with sub-items trigger Android Bottom Sheet modals,
 * items without sub-items toggle instant checkmark.
 */
?>

<!-- Header Section: Greeting, Avatar & Notification Bell -->
<div class="px-5 pt-4 pb-4 flex items-center justify-between">
    <div>
        <p class="text-xs text-slate-500 font-normal">Selamat pagi,</p>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
            Bu Rina <span class="inline-block animate-bounce origin-bottom">👋</span>
        </h1>
        <p class="text-[11px] text-slate-400 font-normal max-w-[210px] leading-tight mt-0.5">
            Semangat mengajar, menginspirasi, dan membimbing hari ini!
        </p>
    </div>
    
    <div class="flex items-center gap-2.5">
        <!-- Teacher Avatar Photo (Lazy Loaded) -->
        <a href="<?= url('mobile/profil') ?>" class="relative block rounded-full p-0.5 bg-gradient-to-tr from-blue-500 to-sky-300 shadow-md press-bounce">
            <img src="<?= asset('images/mobile/teacher_rina.jpg') ?>" 
                 alt="Bu Rina" 
                 loading="lazy"
                 class="w-14 h-14 rounded-full object-cover border-2 border-white">
            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
        </a>

        <!-- Notification Bell -->
        <a href="<?= url('mobile/notifikasi') ?>" class="relative w-11 h-11 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-700 press-bounce">
            <i data-lucide="bell" class="w-5 h-5 stroke-[2]"></i>
            <!-- Unread Badge Dot -->
            <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </a>
    </div>
</div>

<!-- Main Content Container -->
<div class="px-4 space-y-4">

    <!-- 1. Absen Kehadiran Card -->
    <div class="bg-white rounded-3xl p-4 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-100">
        <div class="flex items-center justify-between gap-3">
            
            <!-- Left Green Badge Icon -->
            <div id="beranda-checkin-badge" class="w-13 h-13 rounded-2xl bg-emerald-50 border border-emerald-100 flex flex-col items-center justify-center shrink-0">
                <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <span class="text-[9px] font-bold text-emerald-700 mt-0.5">Check-in</span>
            </div>

            <!-- Middle Text -->
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-slate-800 text-sm leading-snug">Absen Kehadiran</h3>
                <p class="text-[11px] text-slate-400 leading-tight line-clamp-2 mt-0.5">
                    Mulai aktivitas hari ini dengan melakukan check-in terlebih dahulu.
                </p>
            </div>

            <!-- Right Green Pill Action Button -->
            <a href="<?= url('mobile/absen') ?>" id="beranda-checkin-btn" class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-[#16a34a] hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 press-bounce">
                <span>📍</span>
                <span>Check-in</span>
            </a>
        </div>

        <!-- Sub-bar: Belum check-in / Status -->
        <div class="bg-[#f0fdf4] rounded-2xl p-2.5 flex items-center gap-2.5 mt-3 border border-emerald-100/70">
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 text-xs">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
            </div>
            <div>
                <p id="beranda-checkin-status" class="text-xs font-bold text-slate-800 leading-tight">Belum check-in hari ini</p>
                <p id="beranda-checkin-sub" class="text-[10px] text-slate-400">Waktu akan tercatat saat Anda check-in.</p>
            </div>
        </div>
    </div>

    <!-- 2. Ibadah Harian (Clean 4-Column Modular Cards) -->
    <div class="space-y-2">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-1.5">
                <span class="text-sm">🌙</span>
                <h3 class="font-bold text-slate-800 text-xs">Ibadah Harian</h3>
            </div>
            <div class="flex items-center gap-1">
                <a href="<?= url('mobile/quran') ?>" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 flex items-center gap-1 transition-all press-bounce">
                    <span>📖</span> Qur'an
                </a>
                <a href="<?= url('mobile/dzikir') ?>" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 hover:bg-purple-200 flex items-center gap-1 transition-all press-bounce">
                    <span>📿</span> Dzikir
                </a>
                <span id="ibadah-total-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    0/4 Selesai
                </span>
            </div>
        </div>

        <!-- 4 Worship Cards (4 Columns) -->
        <div class="grid grid-cols-4 gap-2">
            
            <!-- Card 1: Sholat 5 Waktu -->
            <div onclick="openSholatModal()" id="card-ibadah-sholat" class="bg-white rounded-2xl p-2.5 shadow-sm border border-slate-100 hover:border-emerald-200 cursor-pointer press-bounce flex flex-col items-center text-center space-y-1.5 transition-all relative">
                <span id="badge-ibadah-sholat" class="absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold">
                    ⋯
                </span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-base shadow-xs">
                    🕌
                </div>
                <div class="w-full">
                    <h4 class="font-bold text-[11px] text-slate-900 leading-tight truncate">Sholat</h4>
                    <p id="sub-ibadah-sholat" class="text-[9px] text-slate-400 mt-0.5 font-medium truncate">0/5</p>
                </div>
            </div>

            <!-- Card 2: Tilawah Al-Qur'an (Direct Click Toggle) -->
            <div onclick="toggleDirectIbadah('tilawah')" id="card-ibadah-tilawah" class="bg-white rounded-2xl p-2.5 shadow-sm border border-slate-100 hover:border-blue-200 cursor-pointer press-bounce flex flex-col items-center text-center space-y-1.5 transition-all relative">
                <span id="badge-ibadah-tilawah" class="absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold">
                    ⋯
                </span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-base shadow-xs">
                    📖
                </div>
                <div class="w-full">
                    <h4 class="font-bold text-[11px] text-slate-900 leading-tight truncate">Tilawah</h4>
                    <p id="sub-ibadah-tilawah" class="text-[9px] text-slate-400 mt-0.5 font-medium truncate">Belum</p>
                </div>
            </div>

            <!-- Card 3: Istighfar & Sholawat (Direct Click Toggle) -->
            <div onclick="toggleDirectIbadah('dzikir')" id="card-ibadah-dzikir" class="bg-white rounded-2xl p-2.5 shadow-sm border border-slate-100 hover:border-purple-200 cursor-pointer press-bounce flex flex-col items-center text-center space-y-1.5 transition-all relative">
                <span id="badge-ibadah-dzikir" class="absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold">
                    ⋯
                </span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-base shadow-xs">
                    📿
                </div>
                <div class="w-full">
                    <h4 class="font-bold text-[11px] text-slate-900 leading-tight truncate">Dzikir</h4>
                    <p id="sub-ibadah-dzikir" class="text-[9px] text-slate-400 mt-0.5 font-medium truncate">0/2</p>
                </div>
            </div>

            <!-- Card 4: Tadabbur & Tadzkirah (Direct Click Toggle) -->
            <div onclick="toggleDirectIbadah('tadabbur')" id="card-ibadah-tadabbur" class="bg-white rounded-2xl p-2.5 shadow-sm border border-slate-100 hover:border-amber-200 cursor-pointer press-bounce flex flex-col items-center text-center space-y-1.5 transition-all relative">
                <span id="badge-ibadah-tadabbur" class="absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold">
                    ⋯
                </span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-base shadow-xs">
                    💡
                </div>
                <div class="w-full">
                    <h4 class="font-bold text-[11px] text-slate-900 leading-tight truncate">Tadabbur</h4>
                    <p id="sub-ibadah-tadabbur" class="text-[9px] text-slate-400 mt-0.5 font-medium truncate">Belum</p>
                </div>
            </div>

        </div>
    </div>

    <!-- 3. Jadwal Hari Ini Card -->
    <div class="bg-gradient-to-br from-blue-50/90 via-white to-sky-50/50 rounded-3xl p-4 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-blue-100/70 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/30">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Jadwal Hari Ini</h3>
            </div>
            <a href="<?= url('mobile/kelas') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                Lihat Semua <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <!-- Timeline + 3D Calendar Illustration -->
        <div class="flex items-center justify-between gap-2">
            
            <!-- Left: Timeline -->
            <div class="flex-1 space-y-3.5 pl-1">
                
                <!-- Schedule Item 1 -->
                <div class="relative pl-5 border-l-2 border-blue-400">
                    <span class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full bg-blue-600 ring-4 ring-blue-100"></span>
                    <p class="text-[10px] font-semibold text-slate-400">08.00 - 08.45</p>
                    <h4 class="text-xs font-bold text-slate-900 leading-snug">Matematika</h4>
                    <p class="text-[10px] text-slate-500 font-medium">Kelas 7A</p>
                </div>

                <!-- Schedule Item 2 -->
                <div class="relative pl-5 border-l-2 border-blue-300">
                    <span class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full bg-blue-500 ring-4 ring-blue-100"></span>
                    <p class="text-[10px] font-semibold text-slate-400">09.00 - 09.45</p>
                    <h4 class="text-xs font-bold text-slate-900 leading-snug">Matematika</h4>
                    <p class="text-[10px] text-slate-500 font-medium">Kelas 7B</p>
                </div>

            </div>

            <!-- Right: 3D Calendar Illustration (Lazy Loaded) -->
            <div class="w-36 h-28 shrink-0 relative flex items-center justify-center">
                <img src="<?= asset('images/mobile/calendar_3d.jpg') ?>" 
                     alt="3D Calendar" 
                     loading="lazy"
                     class="w-full h-full object-contain filter drop-shadow-md rounded-2xl">
            </div>
        </div>
    </div>

    <!-- 4. 4 Stat / Metric Cards (2x2 Grid) -->
    <div class="grid grid-cols-2 gap-3">
        
        <!-- Metric 1: Kelas Diampu -->
        <a href="<?= url('mobile/kelas') ?>" class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 hover:border-blue-200 transition-all press-bounce block">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xl font-black text-slate-900 leading-none">6</span>
                    <p class="text-xs font-bold text-slate-800 mt-0.5 leading-tight">Kelas Diampu</p>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2.5 pt-2 border-t border-slate-50">
                <span>Lihat daftar kelas</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </div>
        </a>

        <!-- Metric 2: Tugas Aktif -->
        <a href="<?= url('mobile/buat-tugas') ?>" class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 hover:border-emerald-200 transition-all press-bounce block">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xl font-black text-slate-900 leading-none">12</span>
                    <p class="text-xs font-bold text-slate-800 mt-0.5 leading-tight">Tugas Aktif</p>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2.5 pt-2 border-t border-slate-50">
                <span>Kelola tugas kelas</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </div>
        </a>

        <!-- Metric 3: Belum Dinilai -->
        <a href="<?= url('mobile/jurnal') ?>" class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 hover:border-amber-200 transition-all press-bounce block">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xl font-black text-slate-900 leading-none">36</span>
                    <p class="text-xs font-bold text-slate-800 mt-0.5 leading-tight">Belum Dinilai</p>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2.5 pt-2 border-t border-slate-50">
                <span>Periksa & nilai tugas</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </div>
        </a>

        <!-- Metric 4: Laporan Kelas -->
        <a href="<?= url('mobile/absensi-kelas') ?>" class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 hover:border-purple-200 transition-all press-bounce block">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xl font-black text-slate-900 leading-none">4</span>
                    <p class="text-xs font-bold text-slate-800 mt-0.5 leading-tight">Laporan Kelas</p>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2.5 pt-2 border-t border-slate-50">
                <span>Lihat perkembangan</span>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </div>
        </a>

    </div>

    <!-- 5. Akses Cepat (6 Action Items) -->
    <div class="pt-1">
        <h3 class="font-bold text-slate-800 text-sm mb-3">Akses Cepat</h3>
        
        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 grid grid-cols-3 gap-3">
            
            <!-- 1: Al-Qur'an Digital (Emerald) -->
            <a href="<?= url('mobile/quran') ?>" class="flex flex-col items-center gap-1.5 p-2 rounded-2xl hover:bg-emerald-50/50 transition-all press-bounce">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 text-lg">
                    📖
                </div>
                <span class="text-[11px] font-bold text-slate-800 text-center leading-tight">Al-Qur'an<br>Digital</span>
            </a>

            <!-- 2: Dzikir Al-Ma'tsurat (Purple) -->
            <a href="<?= url('mobile/dzikir') ?>" class="flex flex-col items-center gap-1.5 p-2 rounded-2xl hover:bg-purple-50/50 transition-all press-bounce">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-500/20 text-lg">
                    📿
                </div>
                <span class="text-[11px] font-bold text-slate-800 text-center leading-tight">Dzikir<br>Al-Ma'tsurat</span>
            </a>

            <!-- 3: Materi Pembelajaran (Blue) -->
            <a href="<?= url('mobile/materi') ?>" class="flex flex-col items-center gap-1.5 p-2 rounded-2xl hover:bg-blue-50/50 transition-all press-bounce">
                <div class="w-12 h-12 rounded-2xl bg-[#3b82f6] text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800 text-center leading-tight">Materi<br>Ajar</span>
            </a>

            <!-- 4: Buat Tugas (Green) -->
            <a href="<?= url('mobile/buat-tugas') ?>" class="flex flex-col items-center gap-1.5 p-2 rounded-2xl hover:bg-emerald-50/50 transition-all press-bounce">
                <div class="w-12 h-12 rounded-2xl bg-[#10b981] text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800 text-center leading-tight">Buat<br>Tugas</span>
            </a>

            <!-- 5: Absensi Kelas (Orange) -->
            <a href="<?= url('mobile/absensi-kelas') ?>" class="flex flex-col items-center gap-1.5 p-2 rounded-2xl hover:bg-orange-50/50 transition-all press-bounce">
                <div class="w-12 h-12 rounded-2xl bg-[#f97316] text-white flex items-center justify-center shadow-md shadow-orange-500/20">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800 text-center leading-tight">Absensi<br>Kelas</span>
            </a>

            <!-- 6: Bank Soal (Cyan) -->
            <a href="<?= url('mobile/bank-soal') ?>" class="flex flex-col items-center gap-1.5 p-2 rounded-2xl hover:bg-cyan-50/50 transition-all press-bounce">
                <div class="w-12 h-12 rounded-2xl bg-[#06b6d4] text-white flex items-center justify-center shadow-md shadow-cyan-500/20">
                    <i data-lucide="folder" class="w-5 h-5"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800 text-center leading-tight">Bank<br>Soal</span>
            </a>

        </div>
    </div>

    <!-- 5b. Layanan Izin & Kesiswaan (Keterlambatan, Izin, Cuti) -->
    <div class="pt-1">
        <h3 class="font-bold text-slate-800 text-sm mb-3">Layanan Izin & Kesiswaan</h3>
        
        <div class="grid grid-cols-3 gap-2.5">
            
            <!-- Item 1: Keterlambatan Siswa (Rose) -->
            <a href="<?= url('mobile/keterlambatan-siswa') ?>" class="bg-white rounded-3xl p-3 shadow-sm border border-slate-100 hover:border-rose-200 flex flex-col items-center text-center gap-1.5 press-bounce">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold shadow-xs">
                    ⏳
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-[11px] leading-tight">Terlambat<br>Siswa</h4>
                    <span class="text-[9px] font-bold text-rose-600">Catat Jam</span>
                </div>
            </a>

            <!-- Item 2: Pengajuan Izin (Amber) -->
            <a href="<?= url('mobile/izin') ?>" class="bg-white rounded-3xl p-3 shadow-sm border border-slate-100 hover:border-amber-200 flex flex-col items-center text-center gap-1.5 press-bounce">
                <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold shadow-xs">
                    📄
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-[11px] leading-tight">Pengajuan<br>Izin Guru</h4>
                    <span class="text-[9px] font-bold text-amber-600">Sakit / Dinas</span>
                </div>
            </a>

            <!-- Item 3: Pengajuan Cuti (Teal) -->
            <a href="<?= url('mobile/cuti') ?>" class="bg-white rounded-3xl p-3 shadow-sm border border-slate-100 hover:border-teal-200 flex flex-col items-center text-center gap-1.5 press-bounce">
                <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-bold shadow-xs">
                    🏖️
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-[11px] leading-tight">Pengajuan<br>Cuti Guru</h4>
                    <span class="text-[9px] font-bold text-teal-600">Sisa 10 Hari</span>
                </div>
            </a>

        </div>
    </div>

    <!-- 6. Motivational Banner Card (Guru Hebat) -->
    <div class="bg-gradient-to-r from-blue-50 via-sky-50 to-indigo-50 rounded-3xl p-4 border border-blue-100/70 shadow-sm relative overflow-hidden flex items-center justify-between gap-3">
        
        <!-- Left: Potted plant art -->
        <div class="w-12 h-16 shrink-0 flex items-center justify-center">
            <svg class="w-10 h-14" viewBox="0 0 64 80" fill="none">
                <!-- Pot -->
                <path d="M16 48 L48 48 L44 76 L20 76 Z" fill="#3b82f6" />
                <path d="M12 44 H52 V48 H12 Z" fill="#2563eb" rx="2" />
                <!-- Plant Leaves -->
                <path d="M32 44 Q20 25 32 8 Q44 25 32 44 Z" fill="#10b981" />
                <path d="M26 40 Q10 28 16 16 Q30 28 26 40 Z" fill="#34d399" />
                <path d="M38 40 Q54 28 48 16 Q34 28 38 40 Z" fill="#059669" />
                <path d="M22 45 Q4 40 8 30 Q22 36 22 45 Z" fill="#6ee7b7" />
                <path d="M42 45 Q60 40 56 30 Q42 36 42 45 Z" fill="#047857" />
            </svg>
        </div>

        <!-- Center Text -->
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-xs text-blue-950 flex items-center gap-1 leading-snug">
                Terima kasih, Guru Hebat! 💙
            </h4>
            <p class="text-[10px] text-slate-500 font-normal leading-tight mt-0.5">
                Dedikasi Anda adalah inspirasi bagi masa depan mereka.
            </p>
        </div>

        <!-- Right: Hand with blue pen & sparkle decoration -->
        <div class="w-16 h-14 shrink-0 relative flex items-center justify-center">
            <span class="absolute top-0 right-1 text-blue-400 text-xs">✦</span>
            <span class="absolute bottom-1 left-0 text-sky-400 text-[10px]">✦</span>
            
            <svg class="w-14 h-14" viewBox="0 0 80 80" fill="none">
                <path d="M10 70 L45 70 C52 70 58 65 62 55 L72 35 C74 30 70 25 65 27 L50 35 L42 40 Z" fill="#fed7aa" />
                <path d="M50 70 L75 70 L75 60 L60 60 Z" fill="#3b82f6" />
                <rect x="52" y="10" width="8" height="42" rx="3" transform="rotate(35 52 10)" fill="#2563eb" />
                <polygon points="56,58 52,65 62,62" fill="#1e293b" />
            </svg>
        </div>
    </div>

</div>

<!-- Interactive Modal & Card Script for Ibadah Harian -->
<script>
    // State Store
    let ibadahData = {
        gender: 'P', // Default female (Bu Rina)
        sholat: {
            subuh: { checked: false, loc: 'rumah' },
            dzuhur: { checked: false, loc: 'rumah' },
            ashar: { checked: false, loc: 'rumah' },
            maghrib: { checked: false, loc: 'rumah' },
            isya: { checked: false, loc: 'rumah' }
        },
        tilawah: { checked: false, text: '' },
        dzikir: { istighfar: false, sholawat: false },
        tadabbur: { checked: false, text: '' }
    };

    // ── 1. Sholat 5 Waktu Bottom Sheet Modal ─────────────────
    function openSholatModal() {
        const sholats = [
            { id: 'subuh', name: 'Subuh', time: '04.45' },
            { id: 'dzuhur', name: 'Dzuhur', time: '12.10' },
            { id: 'ashar', name: 'Ashar', time: '15.30' },
            { id: 'maghrib', name: 'Maghrib', time: '18.15' },
            { id: 'isya', name: 'Isya', time: '19.25' }
        ];

        let listHtml = '';
        sholats.forEach(s => {
            const isChecked = ibadahData.sholat[s.id].checked;
            const currentLoc = ibadahData.sholat[s.id].loc || (ibadahData.gender === 'P' ? 'rumah' : 'masjid');
            const isRumah = currentLoc === 'rumah';

            listHtml += `
                <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 border border-slate-100 gap-2">
                    <label class="flex items-center gap-2.5 cursor-pointer min-w-0 flex-1">
                        <input type="checkbox" id="modal-sholat-${s.id}" ${isChecked ? 'checked' : ''} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">${s.name}</p>
                            <p class="text-[10px] text-slate-400 font-mono">${s.time} WITA</p>
                        </div>
                    </label>

                    <!-- Location Toggle Pill -->
                    <div class="flex items-center bg-white p-0.5 rounded-xl border border-slate-200 text-[10px] font-bold shrink-0" data-modal-sholat="${s.id}">
                        <button type="button" onclick="setModalSholatLoc('${s.id}', 'rumah')" class="modal-loc-rumah px-2 py-1 rounded-lg ${isRumah ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-slate-500'} font-bold transition-all">
                            🏠 Rumah
                        </button>
                        <button type="button" onclick="setModalSholatLoc('${s.id}', 'masjid')" class="modal-loc-masjid px-2 py-1 rounded-lg ${!isRumah ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-slate-500'} font-bold transition-all">
                            🕌 Masjid
                        </button>
                    </div>
                </div>
            `;
        });

        AndroidUI.bottomSheet({
            title: 'Sholat Fardhu 5 Waktu',
            subtitle: 'Pilih waktu sholat dan tempat pelaksanaan',
            icon: '🕌',
            iconBg: 'bg-emerald-100 text-emerald-700',
            content: `
                <div class="space-y-2.5 pt-1 text-left">
                    <!-- Gender Selector -->
                    <div class="flex items-center justify-between p-2 rounded-2xl bg-slate-100 text-xs">
                        <span class="text-[11px] font-bold text-slate-600">Default Lokasi:</span>
                        <div class="flex items-center gap-1 bg-white p-0.5 rounded-xl border border-slate-200 text-[10px] font-bold">
                            <button type="button" onclick="switchModalGender('P')" id="modal-gender-p" class="px-2 py-1 rounded-lg ${ibadahData.gender === 'P' ? 'bg-pink-500 text-white' : 'text-slate-600'} transition-all">
                                👩 Perempuan (Di Rumah)
                            </button>
                            <button type="button" onclick="switchModalGender('L')" id="modal-gender-l" class="px-2 py-1 rounded-lg ${ibadahData.gender === 'L' ? 'bg-blue-600 text-white' : 'text-slate-600'} transition-all">
                                👨 Laki-laki (Di Masjid)
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5 max-h-[260px] overflow-y-auto no-scrollbar">
                        ${listHtml}
                    </div>
                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Simpan',
                    className: 'flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
                    autoClose: false,
                    onClick: (e, btn) => {
                        AndroidUI.setButtonLoading(btn, 'Menyimpan...');
                        setTimeout(() => {
                            ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'].forEach(s => {
                                const chk = document.getElementById(`modal-sholat-${s}`);
                                if (chk) ibadahData.sholat[s].checked = chk.checked;
                            });
                            saveIbadahState();
                            AndroidUI.closeBottomSheet();
                            AndroidUI.toast('Data Sholat 5 Waktu diperbarui!', 'success');
                        }, 400);
                    }
                }
            ]
        });
    }

    function setModalSholatLoc(sholatId, loc) {
        ibadahData.sholat[sholatId].loc = loc;
        const container = document.querySelector(`[data-modal-sholat="${sholatId}"]`);
        if (!container) return;
        const btnR = container.querySelector('.modal-loc-rumah');
        const btnM = container.querySelector('.modal-loc-masjid');
        if (loc === 'rumah') {
            btnR.className = 'modal-loc-rumah px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold transition-all';
            btnM.className = 'modal-loc-masjid px-2 py-1 rounded-lg text-slate-500 font-medium transition-all';
        } else {
            btnM.className = 'modal-loc-masjid px-2 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-bold transition-all';
            btnR.className = 'modal-loc-rumah px-2 py-1 rounded-lg text-slate-500 font-medium transition-all';
        }
    }

    function switchModalGender(gender) {
        ibadahData.gender = gender;
        const defaultLoc = gender === 'P' ? 'rumah' : 'masjid';
        ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'].forEach(s => {
            setModalSholatLoc(s, defaultLoc);
        });
        const btnP = document.getElementById('modal-gender-p');
        const btnL = document.getElementById('modal-gender-l');
        if (btnP && btnL) {
            btnP.className = `px-2 py-1 rounded-lg ${gender === 'P' ? 'bg-pink-500 text-white' : 'text-slate-600'} transition-all`;
            btnL.className = `px-2 py-1 rounded-lg ${gender === 'L' ? 'bg-blue-600 text-white' : 'text-slate-600'} transition-all`;
        }
    }

    // ── 2. Tilawah Al-Qur'an Bottom Sheet Modal ──────────────
    function openTilawahModal() {
        AndroidUI.bottomSheet({
            title: 'Tilawah Al-Qur\'an',
            subtitle: 'Target harian membaca Al-Qur\'an',
            icon: '📖',
            iconBg: 'bg-blue-100 text-blue-600',
            content: `
                <div class="space-y-3 pt-1 text-left">
                    <label class="flex items-center gap-2.5 p-3 rounded-2xl bg-blue-50 border border-blue-100 cursor-pointer">
                        <input type="checkbox" id="modal-check-tilawah" ${ibadahData.tilawah.checked ? 'checked' : ''} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-blue-900">Sudah Tilawah Hari Ini</span>
                    </label>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Capaian Ayat / Halaman / Juz</label>
                        <input type="text" id="modal-input-tilawah" value="${ibadahData.tilawah.text || ''}" placeholder="Contoh: QS. Al-Kahfi: 1-10 (Hal. 293)" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner">
                    </div>

                    <div class="flex flex-wrap gap-1.5 pt-0.5">
                        <button type="button" onclick="setTilawahQuick('1 Halaman')" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700">+ 1 Halaman</button>
                        <button type="button" onclick="setTilawahQuick('1 Ruku\'')" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700">+ 1 Ruku'</button>
                        <button type="button" onclick="setTilawahQuick('1/2 Juz')" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700">+ 1/2 Juz</button>
                        <button type="button" onclick="setTilawahQuick('1 Juz')" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-700">+ 1 Juz</button>
                    </div>
                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Simpan',
                    className: 'flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
                    autoClose: false,
                    onClick: (e, btn) => {
                        AndroidUI.setButtonLoading(btn, 'Menyimpan...');
                        setTimeout(() => {
                            const chk = document.getElementById('modal-check-tilawah');
                            const inp = document.getElementById('modal-input-tilawah');
                            ibadahData.tilawah.checked = chk ? chk.checked : false;
                            ibadahData.tilawah.text = inp ? inp.value.trim() : '';
                            saveIbadahState();
                            AndroidUI.closeBottomSheet();
                            AndroidUI.toast('Capaian Tilawah tersimpan!', 'success');
                        }, 400);
                    }
                }
            ]
        });
    }

    function setTilawahQuick(str) {
        const inp = document.getElementById('modal-input-tilawah');
        const chk = document.getElementById('modal-check-tilawah');
        if (inp) inp.value = str;
        if (chk) chk.checked = true;
    }

    // ── 3. Istighfar & Sholawat Bottom Sheet Modal ───────────
    function openDzikirModal() {
        AndroidUI.bottomSheet({
            title: 'Dzikir, Istighfar & Sholawat',
            subtitle: 'Target dzikir harian 100x',
            icon: '📿',
            iconBg: 'bg-purple-100 text-purple-600',
            content: `
                <div class="space-y-2.5 pt-1 text-left">
                    <label class="flex items-center justify-between p-3 rounded-2xl bg-purple-50 border border-purple-100 cursor-pointer">
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" id="modal-check-istighfar" ${ibadahData.dzikir.istighfar ? 'checked' : ''} class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                            <div>
                                <p class="text-xs font-bold text-purple-950">Istighfar (Astaghfirullah)</p>
                                <p class="text-[10px] text-purple-700 font-medium">Target 100x sehari</p>
                            </div>
                        </div>
                        <span class="text-xs font-black text-purple-700 bg-white px-2 py-1 rounded-xl border border-purple-200">100x</span>
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-2xl bg-purple-50 border border-purple-100 cursor-pointer">
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" id="modal-check-sholawat" ${ibadahData.dzikir.sholawat ? 'checked' : ''} class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                            <div>
                                <p class="text-xs font-bold text-purple-950">Sholawat Nabi</p>
                                <p class="text-[10px] text-purple-700 font-medium">Target 100x sehari</p>
                            </div>
                        </div>
                        <span class="text-xs font-black text-purple-700 bg-white px-2 py-1 rounded-xl border border-purple-200">100x</span>
                    </label>
                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Simpan',
                    className: 'flex-1 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
                    autoClose: false,
                    onClick: (e, btn) => {
                        AndroidUI.setButtonLoading(btn, 'Menyimpan...');
                        setTimeout(() => {
                            const ci = document.getElementById('modal-check-istighfar');
                            const cs = document.getElementById('modal-check-sholawat');
                            ibadahData.dzikir.istighfar = ci ? ci.checked : false;
                            ibadahData.dzikir.sholawat = cs ? cs.checked : false;
                            saveIbadahState();
                            AndroidUI.closeBottomSheet();
                            AndroidUI.toast('Target Dzikir tersimpan!', 'success');
                        }, 400);
                    }
                }
            ]
        });
    }

    // ── 4. Tadabbur Ayat Bottom Sheet Modal ──────────────────
    function openTadabburModal() {
        AndroidUI.bottomSheet({
            title: 'Tadabbur Ayat & Tadzkirah',
            subtitle: 'Refleksi renungan ayat & nasihat hari ini',
            icon: '💡',
            iconBg: 'bg-amber-100 text-amber-700',
            content: `
                <div class="space-y-3 pt-1 text-left">
                    <label class="flex items-center gap-2.5 p-3 rounded-2xl bg-amber-50 border border-amber-100 cursor-pointer">
                        <input type="checkbox" id="modal-check-tadabbur" ${ibadahData.tadabbur.checked ? 'checked' : ''} class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-xs font-bold text-amber-950">Sudah Tadabbur Ayat Hari Ini</span>
                    </label>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tadabbur / Hikmah</label>
                        <textarea id="modal-input-tadabbur" rows="3" placeholder="Tuliskan hikmah & renungan ayat yang Anda pelajari hari ini..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-inner resize-none leading-relaxed">${ibadahData.tadabbur.text || ''}</textarea>
                    </div>
                </div>
            `,
            actions: [
                {
                    text: 'Batal',
                    className: 'flex-1 py-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                },
                {
                    text: 'Simpan',
                    className: 'flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-2xl shadow-md text-center press-bounce',
                    autoClose: false,
                    onClick: (e, btn) => {
                        AndroidUI.setButtonLoading(btn, 'Menyimpan...');
                        setTimeout(() => {
                            const ct = document.getElementById('modal-check-tadabbur');
                            const it = document.getElementById('modal-input-tadabbur');
                            ibadahData.tadabbur.checked = ct ? ct.checked : false;
                            ibadahData.tadabbur.text = it ? it.value.trim() : '';
                            saveIbadahState();
                            AndroidUI.closeBottomSheet();
                            AndroidUI.toast('Catatan Tadabbur tersimpan!', 'success');
                        }, 400);
                    }
                }
            ]
        });

        setTimeout(() => {
            const ta = document.getElementById('modal-input-tadabbur');
            if (ta) ta.focus();
        }, 350);
    }

    // ── Direct Toggle for Tilawah, Dzikir, Tadabbur (1-Click Instant Check) ──
    function toggleDirectIbadah(type) {
        if (type === 'tilawah') {
            ibadahData.tilawah.checked = !ibadahData.tilawah.checked;
            const msg = ibadahData.tilawah.checked ? 'Tilawah dicentang selesai ✓' : 'Tilawah dibatalkan';
            AndroidUI.toast(msg, ibadahData.tilawah.checked ? 'success' : 'info');
        } else if (type === 'dzikir') {
            const isDone = ibadahData.dzikir.istighfar && ibadahData.dzikir.sholawat;
            ibadahData.dzikir.istighfar = !isDone;
            ibadahData.dzikir.sholawat = !isDone;
            const msg = !isDone ? 'Dzikir 100x dicentang selesai ✓' : 'Dzikir dibatalkan';
            AndroidUI.toast(msg, !isDone ? 'success' : 'info');
        } else if (type === 'tadabbur') {
            ibadahData.tadabbur.checked = !ibadahData.tadabbur.checked;
            const msg = ibadahData.tadabbur.checked ? 'Tadabbur dicentang selesai ✓' : 'Tadabbur dibatalkan';
            AndroidUI.toast(msg, ibadahData.tadabbur.checked ? 'success' : 'info');
        }

        if (navigator.vibrate) navigator.vibrate(25);
        saveIbadahState();
    }

    // ── Save & Render Main Cards ────────────────────────────
    function saveIbadahState() {
        localStorage.setItem('portal_guru_ibadah_today', JSON.stringify(ibadahData));
        renderIbadahCards();
    }

    function renderIbadahCards() {
        // 1. Sholat Card
        let sholatDone = 0;
        ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'].forEach(s => {
            if (ibadahData.sholat[s] && ibadahData.sholat[s].checked) sholatDone++;
        });

        const cardSholat = document.getElementById('card-ibadah-sholat');
        const badgeSholat = document.getElementById('badge-ibadah-sholat');
        const subSholat = document.getElementById('sub-ibadah-sholat');

        if (sholatDone === 5) {
            badgeSholat.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] font-bold shadow-xs';
            badgeSholat.innerHTML = '✓';
            subSholat.textContent = `5/5 ✓`;
            cardSholat.classList.add('border-emerald-200', 'bg-emerald-50/20');
        } else if (sholatDone > 0) {
            badgeSholat.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px] font-bold';
            badgeSholat.innerHTML = `${sholatDone}`;
            subSholat.textContent = `${sholatDone}/5`;
            cardSholat.classList.remove('border-emerald-200', 'bg-emerald-50/20');
        } else {
            badgeSholat.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold';
            badgeSholat.innerHTML = '⋯';
            subSholat.textContent = `0/5`;
            cardSholat.classList.remove('border-emerald-200', 'bg-emerald-50/20');
        }

        // 2. Tilawah Card
        const cardTilawah = document.getElementById('card-ibadah-tilawah');
        const badgeTilawah = document.getElementById('badge-ibadah-tilawah');
        const subTilawah = document.getElementById('sub-ibadah-tilawah');

        if (ibadahData.tilawah.checked) {
            badgeTilawah.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-blue-500 text-white flex items-center justify-center text-[8px] font-bold shadow-xs';
            badgeTilawah.innerHTML = '✓';
            subTilawah.textContent = 'Selesai ✓';
            cardTilawah.classList.add('border-blue-200', 'bg-blue-50/20');
        } else {
            badgeTilawah.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold';
            badgeTilawah.innerHTML = '⋯';
            subTilawah.textContent = 'Belum';
            cardTilawah.classList.remove('border-blue-200', 'bg-blue-50/20');
        }

        // 3. Dzikir Card
        const cardDzikir = document.getElementById('card-ibadah-dzikir');
        const badgeDzikir = document.getElementById('badge-ibadah-dzikir');
        const subDzikir = document.getElementById('sub-ibadah-dzikir');

        let dzikirCount = 0;
        if (ibadahData.dzikir.istighfar) dzikirCount++;
        if (ibadahData.dzikir.sholawat) dzikirCount++;

        if (dzikirCount === 2) {
            badgeDzikir.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-purple-500 text-white flex items-center justify-center text-[8px] font-bold shadow-xs';
            badgeDzikir.innerHTML = '✓';
            subDzikir.textContent = '2/2 ✓';
            cardDzikir.classList.add('border-purple-200', 'bg-purple-50/20');
        } else if (dzikirCount === 1) {
            badgeDzikir.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px] font-bold';
            badgeDzikir.innerHTML = '1';
            subDzikir.textContent = '1/2';
            cardDzikir.classList.remove('border-purple-200', 'bg-purple-50/20');
        } else {
            badgeDzikir.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold';
            badgeDzikir.innerHTML = '⋯';
            subDzikir.textContent = '0/2';
            cardDzikir.classList.remove('border-purple-200', 'bg-purple-50/20');
        }

        // 4. Tadabbur Card
        const cardTadabbur = document.getElementById('card-ibadah-tadabbur');
        const badgeTadabbur = document.getElementById('badge-ibadah-tadabbur');
        const subTadabbur = document.getElementById('sub-ibadah-tadabbur');

        if (ibadahData.tadabbur.checked) {
            badgeTadabbur.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px] font-bold shadow-xs';
            badgeTadabbur.innerHTML = '✓';
            subTadabbur.textContent = 'Tercatat ✓';
            cardTadabbur.classList.add('border-amber-200', 'bg-amber-50/20');
        } else {
            badgeTadabbur.className = 'absolute top-1.5 right-1.5 w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[8px] font-bold';
            badgeTadabbur.innerHTML = '⋯';
            subTadabbur.textContent = 'Belum';
            cardTadabbur.classList.remove('border-amber-200', 'bg-amber-50/20');
        }

        // Total Completed Count
        let totalCompleted = 0;
        if (sholatDone === 5) totalCompleted++;
        if (ibadahData.tilawah.checked) totalCompleted++;
        if (dzikirCount === 2) totalCompleted++;
        if (ibadahData.tadabbur.checked) totalCompleted++;

        const totalBadge = document.getElementById('ibadah-total-badge');
        if (totalBadge) {
            totalBadge.textContent = `${totalCompleted}/4 Selesai`;
            if (totalCompleted === 4) {
                totalBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white shadow-xs';
            } else {
                totalBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
            }
        }
    }

    function loadSavedIbadah() {
        if (window.MobileAPI && MobileAPI.ibadah) {
            ibadahData = MobileAPI.ibadah.getToday();
        } else {
            const raw = localStorage.getItem('portal_guru_ibadah_today');
            if (raw) {
                try {
                    const parsed = JSON.parse(raw);
                    ibadahData = Object.assign(ibadahData, parsed);
                } catch (e) {
                    console.error('Error parsing ibadah data:', e);
                }
            }
        }
        renderIbadahCards();
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSavedIbadah();
    });

    window.addEventListener('focus', loadSavedIbadah);
    window.addEventListener('storage', loadSavedIbadah);
    if (window.MobileAPI && MobileAPI.events) {
        MobileAPI.events.on('ibadah:updated', (data) => {
            ibadahData = data;
            renderIbadahCards();
        });
    }
</script>
