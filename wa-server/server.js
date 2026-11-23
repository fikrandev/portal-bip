/**
 * WA Gateway eportal - Server WhatsApp Mandiri (Self-Hosted QR Scan)
 * Portal BIP - Yayasan Bina Insan Palu
 * 
 * Menggunakan @whiskeysockets/baileys untuk koneksi multi-device WhatsApp langsung
 * tanpa memerlukan browser Chromium / pihak ketiga.
 */

const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const http = require('http');
const https = require('https');
const QRCode = require('qrcode');
const pino = require('pino');
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion
} = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.PORT || 8001;
const AUTH_DIR = path.join(__dirname, 'auth_info_baileys');
const SCHEDULER_CONFIG_PATH = path.join(__dirname, 'scheduler-config.json');

// Built-in Reminder Scheduler State
const lastTriggeredSlots = new Set();
let schedulerLastResult = null;
let schedulerIntervalHandle = null;

// Middleware
app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Global State
let sock = null;
let currentQrRaw = null;
let currentQrImage = null;
let connectionStatus = 'initializing'; // initializing, qr_ready, connecting, connected, disconnected
let connectedPhone = null;
let connectedName = null;
let reconnectAttempts = 0;

// Pastikan direktori auth ada
if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

// Logger silent untuk pino agar tidak spam di console
const logger = pino({ level: 'silent' });

/**
 * Format nomor telepon ke format JID WhatsApp (e.g. 628123456789@s.whatsapp.net)
 */
function formatToJid(number) {
    let clean = (number || '').toString().replace(/[^0-9]/g, '');
    if (!clean) return null;

    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    } else if (clean.startsWith('8')) {
        clean = '62' + clean;
    }

    if (!clean.endsWith('@s.whatsapp.net')) {
        clean = clean + '@s.whatsapp.net';
    }

    return clean;
}

/**
 * Inisialisasi Koneksi WhatsApp Baileys
 */
async function connectToWhatsApp() {
    try {
        console.log('[WA Gateway eportal] Menginisialisasi session WhatsApp...');
        connectionStatus = 'connecting';

        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: true, // Tampilkan juga di console terminal jika dibuka manual
            auth: state,
            browser: ['WA Gateway eportal', 'Chrome', '1.0.0'], // Nama perangkat tertaut di WhatsApp HP
            syncFullHistory: false,
            generateHighQualityLinkPreview: false,
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000
        });

        // Event: Update Kredensial
        sock.ev.on('creds.update', saveCreds);

        // Event: Update Status Koneksi & QR Code
        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            // Jika menerima QR Code baru
            if (qr) {
                currentQrRaw = qr;
                try {
                    currentQrImage = await QRCode.toDataURL(qr, {
                        width: 320,
                        margin: 2,
                        color: {
                            dark: '#0f172a',
                            light: '#ffffff'
                        }
                    });
                    connectionStatus = 'qr_ready';
                    console.log('[WA Gateway eportal] QR Code baru siap di-scan.');
                } catch (err) {
                    console.error('[WA Gateway eportal] Gagal generate gambar QR:', err.message);
                }
            }

            // Jika status koneksi berubah
            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                
                console.log(`[WA Gateway eportal] Koneksi ditutup. Alasan: ${statusCode}, Reconnect: ${shouldReconnect}`);
                
                connectionStatus = 'disconnected';
                currentQrRaw = null;
                currentQrImage = null;
                connectedPhone = null;
                connectedName = null;

                if (shouldReconnect) {
                    reconnectAttempts++;
                    const delay = Math.min(reconnectAttempts * 2000, 10000);
                    console.log(`[WA Gateway eportal] Mencoba menghubungkan kembali dalam ${delay / 1000} detik...`);
                    setTimeout(() => {
                        connectToWhatsApp();
                    }, delay);
                } else {
                    console.log('[WA Gateway eportal] Device ter-logout. Membersihkan auth...');
                    clearAuthDir();
                    setTimeout(() => {
                        connectToWhatsApp();
                    }, 2000);
                }
            } else if (connection === 'open') {
                reconnectAttempts = 0;
                connectionStatus = 'connected';
                currentQrRaw = null;
                currentQrImage = null;

                // Ambil nomor telepon dari akun yang tertaut
                if (sock.user) {
                    const rawId = sock.user.id || '';
                    connectedPhone = rawId.split(':')[0] || rawId.split('@')[0];
                    connectedName = sock.user.name || 'WA Gateway eportal';
                }

                console.log('========================================================');
                console.log(' [WA Gateway eportal] BERHASIL TERHUBUNG (CONNECTED)!');
                console.log(` Perangkat: WA Gateway eportal`);
                console.log(` Nomor WA: +${connectedPhone}`);
                console.log('========================================================');
            }
        });

    } catch (error) {
        console.error('[WA Gateway eportal] Gagal inisialisasi socket:', error);
        connectionStatus = 'disconnected';
        setTimeout(() => connectToWhatsApp(), 5000);
    }
}

/**
 * Hapus berkas session auth saat logout
 */
function clearAuthDir() {
    try {
        if (fs.existsSync(AUTH_DIR)) {
            const files = fs.readdirSync(AUTH_DIR);
            for (const file of files) {
                fs.unlinkSync(path.join(AUTH_DIR, file));
            }
            console.log('[WA Gateway eportal] Folder auth session berhasil dibersihkan.');
        }
    } catch (err) {
        console.error('[WA Gateway eportal] Gagal membersihkan auth dir:', err.message);
    }
}

// =============================================================================
// REST API ENDPOINTS UNTUK PORTAL BIP (PHP & WEB)
// =============================================================================

/**
 * GET /api/status
 * Cek status server, apakah terhubung, sedang menunggu scan, atau disconnect
 */
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        status: connectionStatus,
        server_name: 'WA Gateway eportal',
        connected: connectionStatus === 'connected',
        phone: connectedPhone,
        name: connectedName || 'WA Gateway eportal',
        has_qr: Boolean(currentQrImage)
    });
});

/**
 * GET /api/qr
 * Ambil QR Code data URL PNG untuk ditampilkan langsung di browser
 */
app.get('/api/qr', (req, res) => {
    if (connectionStatus === 'connected') {
        return res.json({
            success: true,
            status: 'connected',
            server_name: 'WA Gateway eportal',
            phone: connectedPhone,
            message: 'Perangkat sudah terhubung.'
        });
    }

    if (!currentQrImage) {
        return res.json({
            success: false,
            status: connectionStatus,
            server_name: 'WA Gateway eportal',
            message: 'QR Code sedang disiapkan, silakan refresh beberapa saat lagi...'
        });
    }

    res.json({
        success: true,
        status: 'qr_ready',
        server_name: 'WA Gateway eportal',
        qr_image: currentQrImage,
        qr_raw: currentQrRaw
    });
});

/**
 * POST /api/send
 * Kirim pesan WhatsApp ke nomor tujuan
 * Body: { number: '0812...', message: 'Halo...' }
 */
app.post('/api/send', async (req, res) => {
    try {
        const number = req.body.number || req.body.phone || req.body.target;
        const message = req.body.message || req.body.text;

        if (!number) {
            return res.status(400).json({
                success: false,
                message: 'Nomor WhatsApp tujuan wajib diisi.'
            });
        }

        if (!message) {
            return res.status(400).json({
                success: false,
                message: 'Isi pesan tidak boleh kosong.'
            });
        }

        if (connectionStatus !== 'connected' || !sock) {
            return res.status(503).json({
                success: false,
                status: connectionStatus,
                message: 'Server WA Gateway eportal belum terhubung. Silakan lakukan scan QR Code terlebih dahulu.'
            });
        }

        const jid = formatToJid(number);
        if (!jid) {
            return res.status(400).json({
                success: false,
                message: `Format nomor telepon tidak valid: ${number}`
            });
        }

        const targetClean = jid.replace('@s.whatsapp.net', '');
        const isSelf = Boolean(connectedPhone && targetClean === connectedPhone);

        console.log(`[WA Gateway eportal] Mengirim pesan ke ${jid} [Self: ${isSelf}] (${message.length} karakter)...`);

        let targetJid = jid;
        if (isSelf && sock.user && sock.user.id) {
            targetJid = sock.user.id;
        }

        // Kirim pesan
        const sent = await sock.sendMessage(targetJid, { text: message });
        console.log(`[WA Gateway eportal] Pesan berhasil dikirim. Target: ${targetJid}, ID: ${sent?.key?.id}`);

        return res.json({
            success: true,
            message: 'Pesan berhasil dikirim via WA Gateway eportal.' + (isSelf ? ' (Catatan: Pesan dikirim ke nomor sendiri/Notes).' : ''),
            target: targetClean,
            is_self: isSelf,
            message_id: sent?.key?.id || null,
            timestamp: sent?.messageTimestamp || Date.now()
        });

    } catch (error) {
        console.error('[WA Gateway eportal] Gagal mengirim pesan:', error);
        return res.status(500).json({
            success: false,
            message: 'Gagal mengirim pesan: ' + (error.message || 'Kesalahan internal server gateway.'),
            error: error.message
        });
    }
});

/**
 * POST /api/logout
 * Putuskan koneksi WhatsApp dan minta generate QR Code baru
 */
app.post('/api/logout', async (req, res) => {
    try {
        console.log('[WA Gateway eportal] Memproses logout perangkat...');
        if (sock) {
            try {
                await sock.logout();
            } catch (e) {}
            try {
                sock.end();
            } catch (e) {}
        }
        sock = null;
        clearAuthDir();
        connectionStatus = 'disconnected';
        currentQrRaw = null;
        currentQrImage = null;
        connectedPhone = null;
        connectedName = null;

        // Inisialisasi ulang untuk generate QR baru
        setTimeout(() => connectToWhatsApp(), 1500);

        return res.json({
            success: true,
            message: 'Perangkat berhasil diputuskan. QR Code baru sedang disiapkan.'
        });
    } catch (error) {
        return res.status(500).json({
            success: false,
            message: 'Gagal logout: ' + error.message
        });
    }
});

/**
 * GET /api/health
 * Ping cek status hidup web server Node.js
 */
app.get('/api/health', (req, res) => {
    res.json({
        status: 'ok',
        server: 'WA Gateway eportal',
        uptime: process.uptime(),
        connected: connectionStatus === 'connected'
    });
});

// =============================================================================
// AUTO-SCHEDULER PENGINGAT WA JURNAL STAFF (WITA / Asia/Makassar)
// =============================================================================

function getWitaDateParts() {
    const now = new Date();
    const fmt = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Makassar',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        weekday: 'short',
        hourCycle: 'h23'
    });
    const parts = {};
    for (const p of fmt.formatToParts(now)) {
        parts[p.type] = p.value;
    }
    const hh = parts.hour === '24' ? '00' : parts.hour;
    const weekdayMap = { Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6, Sun: 7 };
    return {
        date: `${parts.year}-${parts.month}-${parts.day}`,
        time: `${hh}:${parts.minute}`,
        isoDay: weekdayMap[parts.weekday] || 1
    };
}

function loadSchedulerConfig() {
    if (!fs.existsSync(SCHEDULER_CONFIG_PATH)) {
        return null;
    }
    try {
        const raw = fs.readFileSync(SCHEDULER_CONFIG_PATH, 'utf8');
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : null;
    } catch (e) {
        console.error('[Scheduler Jurnal Staff] Gagal membaca scheduler-config.json:', e.message);
        return null;
    }
}

function triggerCronEndpoint(baseEndpointUrl, cronKey, slotLabel) {
    return new Promise((resolve) => {
        try {
            const urlObj = new URL(baseEndpointUrl);
            urlObj.searchParams.set('key', cronKey || '');
            urlObj.searchParams.set('slot', slotLabel);
            urlObj.searchParams.set('source', 'wa_server');

            const isHttps = urlObj.protocol === 'https:';
            const client = isHttps ? https : http;
            const reqOptions = {
                hostname: urlObj.hostname,
                port: urlObj.port || (isHttps ? 443 : 80),
                path: urlObj.pathname + urlObj.search,
                method: 'GET',
                timeout: 180000
            };
            if (isHttps) {
                reqOptions.rejectUnauthorized = false;
            }

            const req = client.request(reqOptions, (res) => {
                let body = '';
                res.on('data', (chunk) => { body += chunk; });
                res.on('end', () => {
                    let parsed = null;
                    try { parsed = JSON.parse(body); } catch (e) {}
                    const result = {
                        triggered_at: new Date().toISOString(),
                        slot: slotLabel,
                        http_code: res.statusCode,
                        response: parsed || body.slice(0, 500)
                    };
                    schedulerLastResult = result;
                    console.log(`[Scheduler Jurnal Staff] Slot ${slotLabel} selesai (HTTP ${res.statusCode}):`, parsed ? (parsed.message || parsed.reason || 'OK') : body.slice(0, 180));
                    resolve(result);
                });
            });

            req.on('timeout', () => {
                req.destroy(new Error('Request timeout (180s)'));
            });

            req.on('error', (err) => {
                schedulerLastResult = {
                    triggered_at: new Date().toISOString(),
                    slot: slotLabel,
                    http_code: 0,
                    error: err.message
                };
                console.error(`[Scheduler Jurnal Staff] Error memanggil endpoint slot ${slotLabel}:`, err.message);
                resolve(schedulerLastResult);
            });

            req.end();
        } catch (err) {
            schedulerLastResult = {
                triggered_at: new Date().toISOString(),
                slot: slotLabel,
                http_code: 0,
                error: err.message
            };
            console.error(`[Scheduler Jurnal Staff] URL tidak valid:`, err.message);
            resolve(schedulerLastResult);
        }
    });
}

function startReminderScheduler() {
    if (schedulerIntervalHandle) {
        clearInterval(schedulerIntervalHandle);
    }

    const tick = () => {
        if (connectionStatus !== 'connected') {
            return;
        }
        const cfg = loadSchedulerConfig();
        if (!cfg || !cfg.enabled || !cfg.cron_endpoint || !cfg.cron_key) {
            return;
        }

        const wita = getWitaDateParts();
        const activeDays = Array.isArray(cfg.active_days) ? cfg.active_days.map(Number) : [1, 2, 3, 4, 5, 6];
        const schedules = Array.isArray(cfg.schedules) ? cfg.schedules.map((s) => String(s).trim()) : [];

        if (!activeDays.includes(wita.isoDay)) {
            return;
        }
        if (!schedules.includes(wita.time)) {
            return;
        }

        const slotKey = `${wita.date}_${wita.time}`;
        if (lastTriggeredSlots.has(slotKey)) {
            return;
        }

        // Bersihkan cache slot lama agar memori tetap ringan
        if (lastTriggeredSlots.size > 30) {
            for (const existingKey of lastTriggeredSlots) {
                if (!existingKey.startsWith(wita.date + '_')) {
                    lastTriggeredSlots.delete(existingKey);
                }
            }
        }

        lastTriggeredSlots.add(slotKey);
        console.log(`[Scheduler Jurnal Staff] Memicu pengingat otomatis WA untuk slot ${wita.time} WITA (${wita.date})...`);
        triggerCronEndpoint(cfg.cron_endpoint, cfg.cron_key, wita.time);
    };

    schedulerIntervalHandle = setInterval(tick, 30000);
    setTimeout(tick, 5000);
}

/**
 * GET /api/scheduler-status
 * Cek status auto-scheduler pengingat Jurnal Staff pada wa-server
 */
app.get('/api/scheduler-status', (req, res) => {
    const cfg = loadSchedulerConfig();
    res.json({
        success: true,
        wita_now: getWitaDateParts(),
        wa_connected: connectionStatus === 'connected',
        config_loaded: Boolean(cfg),
        enabled: cfg ? Boolean(cfg.enabled) : false,
        schedules: cfg ? (cfg.schedules || []) : [],
        active_days: cfg ? (cfg.active_days || []) : [],
        cron_endpoint: cfg ? (cfg.cron_endpoint || null) : null,
        last_result: schedulerLastResult
    });
});

// Jalankan HTTP Server
app.listen(PORT, '0.0.0.0', () => {
    console.log(`========================================================`);
    console.log(` [WA Gateway eportal] Server REST API Berjalan`);
    console.log(` Port: ${PORT}`);
    console.log(` URL : http://127.0.0.1:${PORT}`);
    console.log(`========================================================`);
    connectToWhatsApp();
    startReminderScheduler();
});
