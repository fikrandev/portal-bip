/**
 * Portal BIP - Mobile API & State Management Client
 * Scalable, modular architecture for teacher mobile portal.
 */

window.MobileAPI = (function() {
    'use strict';

    // ── 1. SECURE & NAMESPACED STORAGE LAYER ─────────────────
    const Storage = {
        PREFIX: 'portal_bip_',

        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(this.PREFIX + key);
                return item !== null ? JSON.parse(item) : defaultValue;
            } catch (e) {
                console.warn(`[MobileAPI.storage] Failed to parse key: ${key}`, e);
                return defaultValue;
            }
        },

        set(key, value) {
            try {
                localStorage.setItem(this.PREFIX + key, JSON.stringify(value));
                return true;
            } catch (e) {
                console.error(`[MobileAPI.storage] Failed to set key: ${key}`, e);
                return false;
            }
        },

        remove(key) {
            try {
                localStorage.removeItem(this.PREFIX + key);
                return true;
            } catch (e) {
                return false;
            }
        }
    };

    // ── 2. GLOBAL EVENT BUS (PUB/SUB) ────────────────────────
    const Events = {
        _listeners: {},

        on(event, callback) {
            if (!this._listeners[event]) this._listeners[event] = [];
            this._listeners[event].push(callback);
            return () => this.off(event, callback);
        },

        off(event, callback) {
            if (!this._listeners[event]) return;
            this._listeners[event] = this._listeners[event].filter(cb => cb !== callback);
        },

        emit(event, data) {
            if (!this._listeners[event]) return;
            this._listeners[event].forEach(callback => {
                try {
                    callback(data);
                } catch (e) {
                    console.error(`[MobileAPI.events] Error in event '${event}':`, e);
                }
            });
        }
    };

    // ── 3. IBADAH HARIAN (MUTABA'AH YAUMIYYAH) ───────────────
    const Ibadah = {
        STORAGE_KEY: 'ibadah_today',

        getDefaultState() {
            return {
                date: new Date().toISOString().split('T')[0],
                gender: 'P', // 'P' (Perempuan -> default di rumah) or 'L' (Laki-laki -> default di masjid)
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
        },

        getToday() {
            // Also check old legacy key if exists
            let data = Storage.get(this.STORAGE_KEY);
            if (!data) {
                const legacy = localStorage.getItem('portal_guru_ibadah_today');
                if (legacy) {
                    try {
                        data = JSON.parse(legacy);
                        this.save(data);
                    } catch (e) {}
                }
            }
            return data ? Object.assign(this.getDefaultState(), data) : this.getDefaultState();
        },

        save(data) {
            const success = Storage.set(this.STORAGE_KEY, data);
            // Sync with legacy key for backward compatibility
            localStorage.setItem('portal_guru_ibadah_today', JSON.stringify(data));
            Events.emit('ibadah:updated', data);
            return success;
        },

        toggleSholat(waktu, isMasjid = false) {
            const data = this.getToday();
            if (data.sholat[waktu]) {
                data.sholat[waktu].checked = !data.sholat[waktu].checked;
                data.sholat[waktu].loc = isMasjid ? 'masjid' : 'rumah';
            }
            this.save(data);
            return data;
        },

        markTilawah(surahName = 'Tilawah Selesai') {
            const data = this.getToday();
            data.tilawah.checked = true;
            data.tilawah.text = surahName;
            this.save(data);
            return data;
        },

        markDzikir(completed = true) {
            const data = this.getToday();
            data.dzikir.istighfar = completed;
            data.dzikir.sholawat = completed;
            this.save(data);
            return data;
        }
    };

    // ── 4. PRESENSI & GEOLOKASI (GPS ATTENDANCE) ─────────────
    const Attendance = {
        SCHOOL_CONFIG: {
            name: 'Kampus BIP',
            lat: -5.147665,
            lng: 119.432732,
            maxRadiusMeters: 150,
            checkoutMinHour: 15 // 15:00 WITA minimum lock
        },

        calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // Earth radius in meters
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return Math.round(R * c);
        },

        isCheckoutAllowed() {
            const now = new Date();
            return now.getHours() >= this.SCHOOL_CONFIG.checkoutMinHour;
        },

        getTodayLogs() {
            return Storage.get('attendance_logs', []);
        },

        saveLog(log) {
            const logs = this.getTodayLogs();
            logs.unshift(log);
            Storage.set('attendance_logs', logs.slice(0, 30));
            Events.emit('attendance:logged', log);
            return log;
        }
    };

    // ── 5. AL-QUR'AN & BOOKMARK ──────────────────────────────
    const Quran = {
        getBookmark() {
            return Storage.get('quran_last_read') || JSON.parse(localStorage.getItem('portal_guru_last_read') || 'null');
        },

        setBookmark(surahNomor, surahNama, ayatNomor) {
            const bookmark = {
                nomor: surahNomor,
                nama: surahNama,
                ayat: ayatNomor,
                time: new Date().toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            };
            Storage.set('quran_last_read', bookmark);
            localStorage.setItem('portal_guru_last_read', JSON.stringify(bookmark));
            Events.emit('quran:bookmarked', bookmark);
            return bookmark;
        }
    };

    // ── 6. EXPORT MODULE INTERFACE ───────────────────────────
    return {
        storage: Storage,
        events: Events,
        ibadah: Ibadah,
        attendance: Attendance,
        quran: Quran
    };

})();
