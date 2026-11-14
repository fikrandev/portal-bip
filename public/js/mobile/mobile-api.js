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
        getLocalDateString() {
            const d = new Date();
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        },

        getScopedKey(guruId = null, dateStr = null) {
            const ctx = window.PORTAL_IBADAH_CTX || {};
            const gId = guruId || ctx.guru_id || '0';
            const dt = dateStr || ctx.date || this.getLocalDateString();
            return `ibadah_v2_${gId}_${dt}`;
        },

        clearLegacyKeys() {
            try {
                localStorage.removeItem('portal_guru_ibadah_today');
                localStorage.removeItem('portal_bip_ibadah_today');
            } catch (e) {}
        },

        getDefaultState(gender = null, guruId = null, dateStr = null) {
            const ctx = window.PORTAL_IBADAH_CTX || {};
            const normGender = ((gender || ctx.gender || 'P') === 'L') ? 'L' : 'P';
            const defaultLoc = normGender === 'P' ? 'rumah' : 'masjid';
            return {
                date: dateStr || ctx.date || this.getLocalDateString(),
                guru_id: Number(guruId || ctx.guru_id || 0),
                gender: normGender,
                sholat: {
                    subuh: { checked: false, loc: defaultLoc },
                    dzuhur: { checked: false, loc: defaultLoc },
                    ashar: { checked: false, loc: defaultLoc },
                    maghrib: { checked: false, loc: defaultLoc },
                    isya: { checked: false, loc: defaultLoc }
                },
                tilawah: { checked: false, text: '', duration_seconds: 0 },
                dzikir: { istighfar: false, sholawat: false },
                tadabbur: { checked: false, text: '' }
            };
        },

        normalizeState(raw, fallbackGender = null) {
            const base = this.getDefaultState(fallbackGender || raw?.gender, raw?.guru_id, raw?.date);
            if (!raw || typeof raw !== 'object') return base;
            const normGender = base.gender;
            const defaultLoc = normGender === 'P' ? 'rumah' : 'masjid';

            ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'].forEach(w => {
                const src = raw.sholat && raw.sholat[w];
                if (src && typeof src === 'object') {
                    base.sholat[w] = {
                        checked: Boolean(src.checked),
                        loc: normGender === 'P' ? 'rumah' : (src.loc === 'masjid' ? 'masjid' : (src.loc === 'rumah' ? 'rumah' : defaultLoc))
                    };
                }
            });

            if (raw.tilawah && typeof raw.tilawah === 'object') {
                base.tilawah = {
                    checked: Boolean(raw.tilawah.checked),
                    text: String(raw.tilawah.text || ''),
                    duration_seconds: Number(raw.tilawah.duration_seconds || 0)
                };
            }
            if (raw.dzikir && typeof raw.dzikir === 'object') {
                base.dzikir = {
                    istighfar: Boolean(raw.dzikir.istighfar),
                    sholawat: Boolean(raw.dzikir.sholawat)
                };
            }
            if (raw.tadabbur && typeof raw.tadabbur === 'object') {
                base.tadabbur = {
                    checked: Boolean(raw.tadabbur.checked),
                    text: String(raw.tadabbur.text || '')
                };
            }
            return base;
        },

        setServerState(serverState) {
            this.clearLegacyKeys();
            const normalized = this.normalizeState(serverState);
            Storage.set(this.getScopedKey(normalized.guru_id, normalized.date), normalized);
            return normalized;
        },

        getToday() {
            this.clearLegacyKeys();
            const ctx = window.PORTAL_IBADAH_CTX || {};
            if (ctx.serverState) {
                return this.setServerState(ctx.serverState);
            }
            const todayStr = ctx.date || this.getLocalDateString();
            const scoped = Storage.get(this.getScopedKey(ctx.guru_id, todayStr));
            if (scoped && scoped.date === todayStr && (!ctx.guru_id || Number(scoped.guru_id) === Number(ctx.guru_id))) {
                return this.normalizeState(scoped, ctx.gender);
            }
            return this.getDefaultState(ctx.gender, ctx.guru_id, todayStr);
        },

        save(data) {
            this.clearLegacyKeys();
            const normalized = this.normalizeState(data);
            const success = Storage.set(this.getScopedKey(normalized.guru_id, normalized.date), normalized);
            if (window.PORTAL_IBADAH_CTX) {
                window.PORTAL_IBADAH_CTX.serverState = normalized;
            }
            Events.emit('ibadah:updated', normalized);
            return success;
        },

        syncToServer(payload, baseUrl = null) {
            const base = baseUrl !== null ? baseUrl : (window.APP_BASE_URL || '');
            return fetch(`${base}/mobile/ibadah/simpan`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (res && res.success && res.data) {
                    const updated = this.setServerState(res.data);
                    if (window.PORTAL_IBADAH_CTX) {
                        window.PORTAL_IBADAH_CTX.serverState = updated;
                    }
                    if (window.MobileRouter && typeof window.MobileRouter.invalidate === 'function') {
                        window.MobileRouter.invalidate(`${base}/mobile`);
                    }
                    Events.emit('ibadah:updated', updated);
                }
                return res;
            })
            .catch(err => {
                console.warn('[MobileAPI.ibadah] Gagal sinkron ke server:', err);
                return null;
            });
        },

        toggleSholat(waktu, isMasjid = false) {
            const data = this.getToday();
            if (data.sholat[waktu]) {
                data.sholat[waktu].checked = !data.sholat[waktu].checked;
                data.sholat[waktu].loc = data.gender === 'P' ? 'rumah' : (isMasjid ? 'masjid' : 'rumah');
            }
            this.save(data);
            this.syncToServer(data);
            return data;
        },

        markTilawah(surahName = 'Tilawah Selesai', durationSeconds = 120) {
            const data = this.getToday();
            data.tilawah.checked = true;
            data.tilawah.text = surahName;
            data.tilawah.duration_seconds = durationSeconds;
            this.save(data);
            return this.syncToServer({
                action: 'mark_tilawah',
                gender: data.gender,
                tilawah_surah: surahName,
                tilawah_duration_seconds: durationSeconds
            });
        },

        markDzikir(completed = true) {
            const data = this.getToday();
            data.dzikir.istighfar = completed;
            data.dzikir.sholawat = completed;
            this.save(data);
            return this.syncToServer({
                action: 'mark_dzikir',
                gender: data.gender
            });
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
