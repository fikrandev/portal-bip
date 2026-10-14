<?php
/**
 * View Server WA Gateway eportal
 * Modul Pengaturan Sistem - Portal BIP
 * 
 * Sistem WhatsApp Gateway Mandiri berbasis Scan QR Code (Baileys Multi-Device)
 * tanpa API pihak ketiga.
 */
$isConnected = $connection['connected'] ?? false;
$statusText  = $connection['status'] ?? 'offline';
$serverName  = $connection['server_name'] ?? 'WA Gateway eportal';
$providerNow = $settings['wa_gateway_provider'] ?? 'self_hosted';
$isEnabled   = ($settings['wa_gateway_enabled'] === '1' || $settings['wa_gateway_enabled'] === 1);
$connectedPhone = $connection['phone'] ?? null;
?>

<div class="max-w-6xl space-y-8 animate-slide-in pb-12">

    <!-- ── 1. Top Hero Header / Status Banner ── -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-primary-950 to-emerald-950 text-white shadow-xl border border-emerald-500/20 p-6 sm:p-8">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-10 top-6 opacity-10 pointer-events-none hidden sm:block">
            <svg class="w-44 h-44 text-emerald-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.64c.25-.13 1.48-.73 1.71-.84.23-.11.4-.17.57.11.17.28.66.84.81 1.01.15.17.3.19.55.07.25-.13 1.06-.39 2.02-1.25.75-.67 1.26-1.5 1.41-1.75.15-.25.02-.39-.11-.51-.11-.11-.25-.28-.37-.42-.13-.15-.17-.25-.25-.42-.08-.17-.04-.32.02-.45.06-.13.57-1.37.78-1.88.2-.49.4-.42.55-.43h.47c.16 0 .42.06.64.3.22.25.85.83.85 2.02 0 1.19.87 2.34.99 2.5.12.16 1.71 2.61 4.14 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.48-.6 1.69-1.19.21-.58.21-1.08.15-1.19-.06-.11-.23-.18-.48-.3"/>
            </svg>
        </div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <!-- Status Pill Badge -->
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-bold">
                    <span id="heroStatusDot" class="w-2.5 h-2.5 rounded-full <?= $isConnected ? 'bg-emerald-400 animate-ping' : ($statusText === 'qr_ready' ? 'bg-amber-400 animate-pulse' : 'bg-rose-400') ?>"></span>
                    <span id="heroStatusText" class="<?= $isConnected ? 'text-emerald-300 font-black tracking-wide' : ($statusText === 'qr_ready' ? 'text-amber-200 font-bold' : 'text-rose-200 font-bold') ?>">
                        <?= $isConnected ? 'TERHUBUNG (CONNECTED)' : ($statusText === 'qr_ready' ? 'MENUNGGU SCAN QR CODE' : 'SERVER WA OFFLINE') ?>
                    </span>
                    <span class="text-white/40">&bull;</span>
                    <span class="text-emerald-200 font-mono"><?= e($serverName) ?></span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span>Server WA Gateway eportal</span>
                </h1>

                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Sistem WhatsApp Gateway Mandiri berbasis Scan QR Code (Multi-Device). Scan langsung dari aplikasi WhatsApp di HP Anda untuk menghubungkan gateway ke seluruh modul Portal BIP tanpa biaya API pihak ketiga.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs text-slate-300">
                    <div class="flex items-center gap-2 bg-black/30 px-3 py-1.5 rounded-xl border border-white/10">
                        <span class="text-slate-400">Mode Server:</span>
                        <strong class="text-emerald-400 font-semibold">Scan QR Sendiri (Baileys)</strong>
                    </div>
                    <div class="flex items-center gap-2 bg-black/30 px-3 py-1.5 rounded-xl border border-white/10">
                        <span class="text-slate-400">Nama Perangkat:</span>
                        <strong class="text-white font-mono font-bold">WA Gateway eportal</strong>
                    </div>
                    <div class="flex items-center gap-2 bg-black/30 px-3 py-1.5 rounded-xl border border-white/10">
                        <span class="text-slate-400">Port Gateway:</span>
                        <strong class="text-emerald-300 font-mono">8001 (Lokal)</strong>
                    </div>
                </div>
            </div>

            <!-- Header Quick Badge -->
            <div class="lg:text-right shrink-0">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 inline-block text-left w-full sm:w-auto">
                    <p class="text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Status Nomor Pengirim</p>
                    <div class="flex items-center gap-3">
                        <div id="heroPhoneIcon" class="w-10 h-10 rounded-xl <?= $isConnected ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-slate-500/20 text-slate-300 border border-white/10' ?> flex items-center justify-center font-bold text-lg">
                            <?= $isConnected ? '📱' : '📷' ?>
                        </div>
                        <div>
                            <div id="heroPhoneText" class="font-extrabold text-sm text-white font-mono">
                                <?= $connectedPhone ? ('+' . e($connectedPhone)) : 'Belum Ada Akun' ?>
                            </div>
                            <div id="heroSubText" class="text-[11px] <?= $isConnected ? 'text-emerald-300 font-semibold' : 'text-slate-300' ?>">
                                <?= $isConnected ? 'Siap Kirim Pesan Otomatis' : 'Perlu Scan Barcode HP' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Flash Messages ── -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-base">✅</span>
                <span><?= e($_SESSION['flash_success']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-base">⚠️</span>
                <span><?= e($_SESSION['flash_error']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800">&times;</button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- ── 2. PUSAT KONEKSI: SCAN QR CODE / STATUS CONNECTED ── -->
    <div id="mainConnectionContainer" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Header Bar -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                    <span>📡</span>
                    <span>Koneksi WhatsApp Gateway (Scan QR Code)</span>
                </h2>
                <p class="text-xs text-slate-500">Tautkan akun WhatsApp Anda untuk mengaktifkan layanan notifikasi otomatis sistem.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="refreshQrManually()" id="btnManualRefresh"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-sm">
                    <span id="refreshIcon">🔄</span>
                    <span>Segarkan QR</span>
                </button>
            </div>
        </div>

        <!-- Body Area: 3 State (A: Scan QR, B: Connected, C: Offline) -->
        <div class="p-6 sm:p-8">

            <!-- ── STATE C: SERVER OFFLINE ALERT ── -->
            <div id="stateOffline" class="<?= ($statusText === 'offline') ? '' : 'hidden' ?> max-w-xl mx-auto text-center space-y-4 py-6">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-rose-100 text-rose-600 flex items-center justify-center text-3xl shadow-inner">
                    ⚠️
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800">Server WA Gateway Lokal Belum Aktif</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Layanan server WhatsApp port 8001 belum berjalan di latar belakang. Silakan klik tombol di bawah untuk menyalakannya secara otomatis.
                    </p>
                </div>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button type="button" onclick="startLocalServer()" id="btnStartServer"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition-all">
                        <span>▶️ Nyalakan Server Sekarang</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400">
                    Atau jalankan file <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-mono">wa-server/start-server.bat</code> secara manual.
                </p>
            </div>

            <!-- ── STATE B: DEVICE CONNECTED (BERHASIL TERHUBUNG) ── -->
            <div id="stateConnected" class="<?= $isConnected ? '' : 'hidden' ?> max-w-2xl mx-auto space-y-6 py-4">
                <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-emerald-50 via-teal-50 to-emerald-100/50 border border-emerald-200 text-center space-y-4 shadow-sm">
                    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center text-4xl shadow-lg shadow-emerald-500/30 ring-8 ring-emerald-200/50 animate-bounce">
                        ✓
                    </div>

                    <div class="space-y-1">
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-600 text-white">
                            TERHUBUNG AKTIF
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-800 pt-1">
                            WA Gateway eportal Siap Digunakan!
                        </h3>
                        <p class="text-xs text-slate-600 max-w-md mx-auto">
                            Akun WhatsApp Anda berhasil ditautkan. Seluruh modul (SPMB, Absensi, Nilai, dll) sekarang dapat mengirim pesan otomatis.
                        </p>
                    </div>

                    <!-- Info Box Kartu Akun -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-lg mx-auto text-left pt-2">
                        <div class="p-3.5 rounded-2xl bg-white border border-emerald-200/80 shadow-xs">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor WhatsApp</span>
                            <span id="connCardPhone" class="font-extrabold text-slate-800 font-mono text-sm">
                                <?= $connectedPhone ? ('+' . e($connectedPhone)) : '-' ?>
                            </span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-white border border-emerald-200/80 shadow-xs">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Perangkat Tertaut</span>
                            <span class="font-extrabold text-emerald-700 font-mono text-sm">
                                WA Gateway eportal
                            </span>
                        </div>
                    </div>

                    <!-- Tombol Putuskan / Logout -->
                    <div class="pt-4 border-t border-emerald-200/60 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <button type="button" onclick="disconnectDevice()" id="btnLogoutDevice"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition-all shadow-sm">
                            <span>🔌 Putuskan Koneksi / Ganti Akun</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── STATE A: SCAN QR CODE (MENUNGGU SCAN) ── -->
            <div id="stateScanQr" class="<?= (!$isConnected && $statusText !== 'offline') ? '' : 'hidden' ?> grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <!-- Kolom Kiri: Display QR Code (5 cols) -->
                <div class="lg:col-span-5 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="relative p-4 rounded-3xl bg-slate-900 shadow-2xl border-2 border-emerald-500/40 group">
                        
                        <!-- Frame QR Box -->
                        <div class="w-64 h-64 sm:w-72 sm:h-72 bg-white rounded-2xl flex items-center justify-center overflow-hidden relative shadow-inner">
                            
                            <!-- Gambar QR Code -->
                            <img id="qrImageElement" src="" alt="Scan QR Code WA Gateway" 
                                 class="w-full h-full object-contain p-2 hidden transition-all duration-300">

                            <!-- Loading Spinner saat menunggu QR pertama -->
                            <div id="qrLoadingSpinner" class="flex flex-col items-center justify-center space-y-3 p-4">
                                <div class="w-10 h-10 border-4 border-emerald-500/20 border-t-emerald-600 rounded-full animate-spin"></div>
                                <span class="text-xs font-bold text-slate-500">Menyiapkan QR Code...</span>
                            </div>
                        </div>

                        <!-- Pulse live status dot -->
                        <div class="absolute -top-2.5 -right-2.5 flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-black uppercase tracking-wider shadow-md">
                            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                            <span>Live QR</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-700 flex items-center justify-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>QR Code otomatis diperbarui secara berkala</span>
                        </p>
                        <p class="text-[11px] text-slate-400">Arahkan kamera WhatsApp HP Anda langsung ke barcode di atas.</p>
                    </div>
                </div>

                <!-- Kolom Kanan: Panduan Langkah Scan (7 cols) -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="space-y-2">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-extrabold uppercase tracking-wider border border-emerald-200">
                            Langkah Mudah Menghubungkan
                        </span>
                        <h3 class="text-xl font-black text-slate-800">
                            Cara Scan Barcode dari WhatsApp HP:
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Ikuti langkah berikut pada handphone yang nomor WhatsApp-nya ingin dijadikan pengirim notifikasi resmi sekolah/portal:
                        </p>
                    </div>

                    <div class="space-y-3 pt-1">
                        <!-- Step 1 -->
                        <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="w-7 h-7 rounded-xl bg-slate-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                                1
                            </div>
                            <div class="text-xs">
                                <strong class="text-slate-800 block mb-0.5">Buka Aplikasi WhatsApp di HP</strong>
                                <span class="text-slate-500">Gunakan akun WhatsApp reguler atau WhatsApp Business sekolah/yayasan.</span>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="w-7 h-7 rounded-xl bg-slate-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                                2
                            </div>
                            <div class="text-xs">
                                <strong class="text-slate-800 block mb-0.5">Buka Menu "Perangkat Tertaut"</strong>
                                <span class="text-slate-500">
                                    Di <strong>Android</strong>: Ketuk menu titik tiga (⋮) di pojok kanan atas &gt; <strong>Perangkat Tertaut</strong>.<br>
                                    Di <strong>iPhone</strong>: Ketuk tab <strong>Pengaturan</strong> (⚙️) di pojok kanan bawah &gt; <strong>Perangkat Tertaut</strong>.
                                </span>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="w-7 h-7 rounded-xl bg-slate-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                                3
                            </div>
                            <div class="text-xs">
                                <strong class="text-slate-800 block mb-0.5">Ketuk "Tautkan Perangkat" &amp; Scan QR Code</strong>
                                <span class="text-slate-500">Arahkan kamera handphone Anda ke kotak Barcode / QR Code di samping kiri layar ini.</span>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200">
                            <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                                4
                            </div>
                            <div class="text-xs">
                                <strong class="text-emerald-900 block mb-0.5">Selesai! Perangkat Terhubung Otomatis</strong>
                                <span class="text-emerald-700">
                                    Di daftar perangkat WhatsApp HP Anda akan muncul nama <strong class="font-mono">WA Gateway eportal</strong> dan layar ini akan otomatis berubah menjadi status <strong>TERHUBUNG</strong>!
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- ── 3. DUA KOLOM: UJI COBA KIRIM & PANDUAN INTEGRASI MODUL ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Kolom Kiri: Form Live Test Kirim Pesan (6 cols) -->
        <div class="lg:col-span-6 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800">Uji Coba Pengiriman Langsung</h3>
                        <p class="text-xs text-slate-500">Tes kirim pesan live melalui WhatsApp yang telah discan.</p>
                    </div>
                    <span class="text-xl">🚀</span>
                </div>

                <div class="p-6 space-y-4">
                    <form id="formTestSend" onsubmit="handleTestSend(event)" class="space-y-4">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="ajax" value="1">

                        <div>
                            <label for="test_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor WhatsApp Tujuan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="test_phone" name="target_phone" required
                                   placeholder="Contoh: 08123456789 atau 628123456789"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-mono text-xs outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all">
                            <p class="mt-1 text-[11px] text-slate-400">Bisa menggunakan awalan 08xxx atau 628xxx.</p>
                        </div>

                        <div>
                            <label for="test_message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Isi Pesan Uji Coba <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="test_message" name="message" rows="3" required
                                      class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all font-sans">Halo! Ini adalah pesan uji coba dari server WA Gateway eportal - Portal BIP Yayasan Bina Insan Palu.</textarea>
                        </div>

                        <button type="submit" id="btnTestSend"
                                class="w-full flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-md transition-all">
                            <span id="btnTestIcon">📤</span>
                            <span id="btnTestText">Kirim Pesan Uji Coba</span>
                        </button>
                    </form>

                    <!-- Alert Live Result -->
                    <div id="testResultBox" class="hidden p-4 rounded-2xl text-xs space-y-2 border">
                        <div id="testResultTitle" class="font-bold flex items-center gap-2"></div>
                        <div id="testResultMessage" class="text-slate-600"></div>
                        <div id="testResultRaw" class="font-mono text-[10px] bg-black/5 p-2 rounded-lg overflow-x-auto max-h-32 hidden"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Panduan Pemanggilan di Seluruh Modul (6 cols) -->
        <div class="lg:col-span-6 space-y-6">
            <div class="bg-gradient-to-br from-slate-900 to-primary-950 rounded-3xl shadow-sm border border-slate-800 p-6 text-white space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        <span>⚡</span>
                        <span>Panduan Integrasi Seluruh Modul</span>
                    </h3>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Global Service
                    </span>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Server <strong>WA Gateway eportal</strong> ini dapat langsung dipanggil kapan saja oleh seluruh modul di Portal BIP untuk pengiriman notifikasi otomatis:
                </p>

                <!-- Code Example -->
                <div class="bg-black/60 rounded-xl p-3 border border-white/10 font-mono text-[11px] text-emerald-300 overflow-x-auto space-y-1">
                    <div class="text-slate-400">// Contoh 1: Kirim Pesan Tunggal (Modul SPMB / Absensi / dll)</div>
                    <div>WaGateway::send($nomor_hp, $pesan, [</div>
                    <div class="pl-4 text-emerald-200">'module' => 'kelola-spmb'</div>
                    <div>]);</div>
                    <div class="text-slate-400 pt-2">// Contoh 2: Kirim Banyak Nomor Sekaligus (Broadcast)</div>
                    <div>WaGateway::sendBulk($daftar_nomor, $pesan);</div>
                </div>

                <div class="space-y-1.5 text-xs text-slate-300 pt-1">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400">✔</span>
                        <span><strong>Modul SPMB:</strong> Kirim konfirmasi pendaftaran &amp; bukti pendaftaran ke wali santri.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400">✔</span>
                        <span><strong>Modul Absensi Siswa:</strong> Notifikasi kehadiran / alpha siswa langsung ke nomor orang tua.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400">✔</span>
                        <span><strong>Modul Ibadah &amp; Nilai:</strong> Laporan berkala amaliah yaumiyah dan informasi penting.</span>
                    </div>
                </div>
            </div>

            <!-- Card Catatan Kaki (Footer Note) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                    <span>📝</span>
                    <span>Pengaturan Catatan Kaki Pesan (Footer Note)</span>
                </h3>

                <form action="<?= url('pengaturan-sistem/wa-gateway/update') ?>" method="POST" class="space-y-3">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="wa_gateway_enabled" value="1">
                    <input type="hidden" name="wa_gateway_server_name" value="<?= e($serverName) ?>">
                    <input type="hidden" name="wa_gateway_provider" value="<?= e($providerNow) ?>">

                    <div>
                        <textarea name="wa_gateway_footer_note" rows="2"
                                  placeholder="Pesan otomatis dikirim melalui Portal BIP..."
                                  class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all font-sans"><?= e($settings['wa_gateway_footer_note'] ?? '') ?></textarea>
                        <p class="mt-1 text-[11px] text-slate-400">Teks ini akan otomatis ditambahkan di baris paling bawah setiap pesan.</p>
                    </div>

                    <div class="text-right">
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all shadow-sm">
                            <span>💾 Simpan Footer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- ── 4. TABEL RIWAYAT LOG PENGIRIMAN PESAN ── -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                    <span>📋</span>
                    <span>Riwayat Log Pengiriman Pesan WhatsApp</span>
                </h3>
                <p class="text-xs text-slate-500">Merekam riwayat pengiriman pesan otomatis dari seluruh modul sistem.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="location.reload()" 
                        class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-all shadow-xs">
                    🔄 Refresh
                </button>
                <?php if (!empty($logs)): ?>
                    <form action="<?= url('pengaturan-sistem/wa-gateway/clear-logs') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh riwayat log pesan WA?')">
                        <?= CSRF::field() ?>
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold transition-all shadow-xs">
                            🗑️ Bersihkan Log
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Nomor Tujuan</th>
                        <th class="py-3 px-4">Modul</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Cuplikan Pesan</th>
                        <th class="py-3 px-4 text-center">Pengirim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada riwayat pesan yang dikirim melalui gateway.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800 whitespace-nowrap">
                                    +<?= e($log['phone_number']) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 font-mono">
                                        <?= e($log['module']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php if ($log['status'] === 'success'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span>✓</span> Terkirim
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200" title="<?= e($log['response_data'] ?? '') ?>">
                                            <span>✗</span> Gagal
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-slate-700 max-w-xs truncate" title="<?= e($log['message']) ?>">
                                    <?= e($log['message']) ?>
                                </td>
                                <td class="py-3 px-4 text-center text-slate-500 text-[11px]">
                                    <?= e($log['sender_name'] ?? 'Sistem Otomatis') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ── 5. JAVASCRIPT REAL-TIME QR CODE & STATUS POLLING ENGINE ── -->
<script>
    // State Tracker
    let isConnectedGlobal = <?= $isConnected ? 'true' : 'false' ?>;
    let pollInterval = null;
    let lastQrRaw = null;

    const csrfToken = '<?= CSRF::token() ?>';

    // Inisialisasi saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function() {
        startPolling();
        // Muat QR pertama kali jika belum terhubung
        if (!isConnectedGlobal) {
            fetchQrCode();
        }
    });

    /**
     * Polling Status Gateway setiap 3 detik
     */
    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(checkGatewayStatus, 3000);
    }

    /**
     * Cek status gateway ke server PHP controller
     */
    async function checkGatewayStatus() {
        try {
            const res = await fetch('<?= url('pengaturan-sistem/wa-gateway/status-ajax') ?>');
            if (!res.ok) return;
            const data = await res.json();

            updateUiState(data);
        } catch (e) {
            console.warn('Gagal polling status WA Gateway:', e);
        }
    }

    /**
     * Perbarui Tampilan UI sesuai Status Server
     */
    function updateUiState(data) {
        const stateOffline = document.getElementById('stateOffline');
        const stateConnected = document.getElementById('stateConnected');
        const stateScanQr = document.getElementById('stateScanQr');
        const heroDot = document.getElementById('heroStatusDot');
        const heroText = document.getElementById('heroStatusText');
        const heroPhone = document.getElementById('heroPhoneText');
        const heroSub = document.getElementById('heroSubText');
        const heroIcon = document.getElementById('heroPhoneIcon');
        const cardPhone = document.getElementById('connCardPhone');

        // Status Offline
        if (!data.server_online || data.status === 'offline') {
            stateOffline.classList.remove('hidden');
            stateConnected.classList.add('hidden');
            stateScanQr.classList.add('hidden');

            heroDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-400';
            heroText.className = 'text-rose-200 font-bold';
            heroText.innerText = 'SERVER WA OFFLINE';
            heroPhone.innerText = 'Server Mati';
            heroSub.innerText = 'Port 8001 Offline';
            heroSub.className = 'text-[11px] text-rose-300';
            heroIcon.className = 'w-10 h-10 rounded-xl bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center justify-center font-bold text-lg';
            heroIcon.innerText = '⚠️';
            isConnectedGlobal = false;
            return;
        }

        // Status Connected (HP sudah scan)
        if (data.connected || data.status === 'connected') {
            stateOffline.classList.add('hidden');
            stateConnected.classList.remove('hidden');
            stateScanQr.classList.add('hidden');

            heroDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping';
            heroText.className = 'text-emerald-300 font-black tracking-wide';
            heroText.innerText = 'TERHUBUNG (CONNECTED)';
            const phoneStr = data.phone ? ('+' + data.phone) : 'Terhubung';
            heroPhone.innerText = phoneStr;
            if (cardPhone) cardPhone.innerText = phoneStr;
            heroSub.innerText = 'Siap Kirim Pesan Otomatis';
            heroSub.className = 'text-[11px] text-emerald-300 font-semibold';
            heroIcon.className = 'w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center font-bold text-lg';
            heroIcon.innerText = '📱';
            isConnectedGlobal = true;
            return;
        }

        // Status QR Ready (Menunggu scan)
        stateOffline.classList.add('hidden');
        stateConnected.classList.add('hidden');
        stateScanQr.classList.remove('hidden');

        heroDot.className = 'w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse';
        heroText.className = 'text-amber-200 font-bold';
        heroText.innerText = 'MENUNGGU SCAN QR CODE';
        heroPhone.innerText = 'Belum Ada Akun';
        heroSub.innerText = 'Perlu Scan Barcode HP';
        heroSub.className = 'text-[11px] text-slate-300';
        heroIcon.className = 'w-10 h-10 rounded-xl bg-slate-500/20 text-slate-300 border border-white/10 flex items-center justify-center font-bold text-lg';
        heroIcon.innerText = '📷';
        isConnectedGlobal = false;

        // Jika QR siap, ambil gambarnya
        if (data.has_qr) {
            fetchQrCode();
        }
    }

    /**
     * Ambil Gambar QR Code Live dari Controller
     */
    async function fetchQrCode() {
        const qrImg = document.getElementById('qrImageElement');
        const spinner = document.getElementById('qrLoadingSpinner');

        try {
            const res = await fetch('<?= url('pengaturan-sistem/wa-gateway/qr-ajax') ?>');
            if (!res.ok) return;
            const data = await res.json();

            if (data.success && data.qr_image) {
                if (lastQrRaw !== data.qr_raw) {
                    lastQrRaw = data.qr_raw;
                    qrImg.src = data.qr_image;
                    qrImg.classList.remove('hidden');
                    spinner.classList.add('hidden');
                }
            } else if (data.status === 'connected') {
                updateUiState({ connected: true, status: 'connected', phone: data.phone, server_online: true });
            }
        } catch (e) {
            console.warn('Gagal memuat QR Code:', e);
        }
    }

    /**
     * Tombol Segarkan QR Manual
     */
    async function refreshQrManually() {
        const icon = document.getElementById('refreshIcon');
        icon.classList.add('animate-spin');
        lastQrRaw = null;
        await checkGatewayStatus();
        await fetchQrCode();
        setTimeout(() => icon.classList.remove('animate-spin'), 600);
    }

    /**
     * Logout / Putuskan Perangkat WhatsApp
     */
    async function disconnectDevice() {
        if (!confirm('Apakah Anda yakin ingin memutuskan perangkat WhatsApp ini?\n\nSetelah diputuskan, Anda perlu melakukan scan QR Code kembali untuk menghubungkan.')) {
            return;
        }

        const btn = document.getElementById('btnLogoutDevice');
        btn.disabled = true;
        btn.innerText = 'Memutuskan perangkat...';

        try {
            const formData = new FormData();
            formData.append('_csrf_token', csrfToken);

            const res = await fetch('<?= url('pengaturan-sistem/wa-gateway/logout-ajax') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            alert(data.message || 'Perangkat berhasil diputuskan.');
            lastQrRaw = null;
            setTimeout(() => {
                location.reload();
            }, 1000);
        } catch (e) {
            alert('Gagal memutuskan perangkat: ' + e.message);
            btn.disabled = false;
            btn.innerText = '🔌 Putuskan Koneksi / Ganti Akun';
        }
    }

    /**
     * Nyalakan Server Node.js di Background jika offline
     */
    async function startLocalServer() {
        const btn = document.getElementById('btnStartServer');
        btn.disabled = true;
        btn.innerText = '⏳ Sedang menyalakan server...';

        try {
            const formData = new FormData();
            formData.append('_csrf_token', csrfToken);

            const res = await fetch('<?= url('pengaturan-sistem/wa-gateway/start-server') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            alert(data.message || 'Perintah telah dikirim.');
            setTimeout(() => {
                location.reload();
            }, 2500);
        } catch (e) {
            alert('Gagal menyalakan server: ' + e.message);
            btn.disabled = false;
            btn.innerText = '▶️ Nyalakan Server Sekarang';
        }
    }

    /**
     * Form Uji Coba Kirim Pesan WhatsApp
     */
    async function handleTestSend(e) {
        e.preventDefault();
        const form = document.getElementById('formTestSend');
        const btn = document.getElementById('btnTestSend');
        const icon = document.getElementById('btnTestIcon');
        const text = document.getElementById('btnTestText');
        const resBox = document.getElementById('testResultBox');
        const resTitle = document.getElementById('testResultTitle');
        const resMsg = document.getElementById('testResultMessage');
        const resRaw = document.getElementById('testResultRaw');

        // Loading state
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.innerText = '⏳';
        text.innerText = 'Mengirim Pesan...';

        resBox.classList.add('hidden');
        resRaw.classList.add('hidden');

        try {
            const formData = new FormData(form);
            const response = await fetch('<?= url('pengaturan-sistem/wa-gateway/test') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            resBox.classList.remove('hidden');
            if (data.success) {
                resBox.className = 'p-4 rounded-2xl text-xs space-y-2 border bg-emerald-50 border-emerald-200 text-emerald-900';
                resTitle.innerHTML = '<span>✅</span> <strong>Pengiriman Berhasil!</strong>';
                resMsg.innerText = data.message || 'Pesan berhasil terkirim melalui WA Gateway eportal.';
            } else {
                resBox.className = 'p-4 rounded-2xl text-xs space-y-2 border bg-rose-50 border-rose-200 text-rose-900';
                resTitle.innerHTML = '<span>❌</span> <strong>Pengiriman Gagal!</strong>';
                resMsg.innerText = data.message || 'Terjadi kesalahan saat mengirim pesan.';
            }

            if (data.response) {
                resRaw.classList.remove('hidden');
                resRaw.innerText = 'Detail Respons: ' + JSON.stringify(data.response, null, 2);
            }

        } catch (err) {
            resBox.classList.remove('hidden');
            resBox.className = 'p-4 rounded-2xl text-xs space-y-2 border bg-rose-50 border-rose-200 text-rose-900';
            resTitle.innerHTML = '<span>❌</span> <strong>Kesalahan Koneksi</strong>';
            resMsg.innerText = err.message || 'Gagal menghubungi server Portal BIP.';
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            icon.innerText = '📤';
            text.innerText = 'Kirim Pesan Uji Coba';
        }
    }
</script>
