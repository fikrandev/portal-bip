/**
 * Portal BIP Notification Center Client Engine
 * Menangani Push Notifikasi real-time + bunyi default perangkat & Popup berkala tiap N menit
 * untuk Portal Web (Laptop/Tablet/iPad) dan Portal Mobile (PWA/HP).
 */
(function () {
    if (window.__BipNotifCenterInit) {
        return;
    }
    window.__BipNotifCenterInit = true;

    const currentScript = document.currentScript || document.querySelector('script[data-notif-platform]');
    const PLATFORM = (currentScript && currentScript.getAttribute('data-notif-platform') === 'mobile') ? 'mobile' : 'web';
    const RAW_BASE = (currentScript && currentScript.getAttribute('data-base-url'))
        ? currentScript.getAttribute('data-base-url')
        : (window.APP_BASE_URL || '');

    let PATH_PREFIX = '';
    try {
        if (RAW_BASE) {
            const p = new URL(RAW_BASE, window.location.origin).pathname.replace(/\/+$/, '');
            PATH_PREFIX = (p === '/') ? '' : p;
        }
    } catch (e) {
        PATH_PREFIX = '';
    }

    function apiUrl(path) {
        const clean = String(path || '').replace(/^\/+/, '');
        const rel = PATH_PREFIX ? `${PATH_PREFIX}/${clean}` : `/${clean}`;
        return window.location.origin + rel;
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (s) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s];
        });
    }

    // ── Web Audio Default Two-Tone Chime (C6 -> E6) ─────────────────────
    let audioCtx = null;
    function unlockAudioContext() {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            if (!audioCtx) {
                audioCtx = new Ctx();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(function () {});
            }
        } catch (e) {}
    }

    ['pointerdown', 'touchstart', 'keydown'].forEach(function (evName) {
        document.addEventListener(evName, unlockAudioContext, { once: true, passive: true });
    });

    function playNotifSound() {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            if (!audioCtx) {
                audioCtx = new Ctx();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(function () {});
            }

            const now = audioCtx.currentTime;
            const notes = [
                { freq: 1046.50, start: 0.00, dur: 0.18 }, // C6
                { freq: 1318.51, start: 0.15, dur: 0.28 }, // E6
            ];

            notes.forEach(function (n) {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(n.freq, now + n.start);

                gain.gain.setValueAtTime(0.0001, now + n.start);
                gain.gain.exponentialRampToValueAtTime(0.18, now + n.start + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + n.start + n.dur);

                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now + n.start);
                osc.stop(now + n.start + n.dur + 0.02);
            });
        } catch (e) {}
    }

    // ── State & Storage Helpers ─────────────────────────────────────────
    let state = {
        userId: 0,
        vapidPublicKey: '',
        pushSubscribed: false,
        canManage: false,
        manageUrl: apiUrl('pusat-notifikasi'),
        inboxItems: [],
        popupItems: [],
        unreadCount: 0,
        activePopupId: null,
        popupQueue: [],
        autoSubTried: false,
    };

    function getSeenPushKey() {
        return `bip_push_seen_${PLATFORM}_${state.userId}`;
    }

    function getSeenPushMap() {
        try {
            const raw = localStorage.getItem(getSeenPushKey());
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    }

    function markPushSeenLocal(id) {
        try {
            const map = getSeenPushMap();
            map[String(id)] = Date.now();
            // Batasi maksimal 150 entry
            const keys = Object.keys(map);
            if (keys.length > 150) {
                keys.sort(function (a, b) { return (map[a] || 0) - (map[b] || 0); });
                keys.slice(0, keys.length - 120).forEach(function (k) { delete map[k]; });
            }
            localStorage.setItem(getSeenPushKey(), JSON.stringify(map));
        } catch (e) {}
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    async function postJson(path, body) {
        const res = await fetch(apiUrl(path), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body || {}),
        });
        return res.json();
    }

    function waitForSwActive(reg) {
        return new Promise(function (resolve, reject) {
            if (reg.active) {
                resolve(reg);
                return;
            }
            const worker = reg.installing || reg.waiting;
            if (!worker) {
                reject(new Error('Worker Service Worker tidak tersedia.'));
                return;
            }
            const timer = setTimeout(function () {
                if (reg.active) {
                    resolve(reg);
                } else {
                    reject(new Error('Aktivasi Service Worker melebihi batas waktu.'));
                }
            }, 8000);

            worker.addEventListener('statechange', function () {
                if (reg.active || worker.state === 'activated') {
                    clearTimeout(timer);
                    resolve(reg);
                } else if (worker.state === 'redundant') {
                    clearTimeout(timer);
                    reject(new Error('Service Worker menjadi redundant.'));
                }
            });
        });
    }

    // ── Browser Web Push Subscribe ──────────────────────────────────────
    async function enableBrowserPush(showFeedback) {
        try {
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                if (showFeedback) {
                    alert('Browser / perangkat ini belum mendukung Push Notification web.');
                }
                return false;
            }

            let perm = Notification.permission;
            if (perm === 'default') {
                perm = await Notification.requestPermission();
            }
            if (perm !== 'granted') {
                if (showFeedback) {
                    alert('Izin notifikasi ditolak/diblokir di pengaturan browser. Silakan izinkan notifikasi terlebih dahulu.');
                }
                return false;
            }

            if (!state.vapidPublicKey) {
                await fetchFeed();
            }
            if (!state.vapidPublicKey) {
                if (showFeedback) alert('Kunci VAPID server belum tersedia.');
                return false;
            }

            const swFile = PLATFORM === 'mobile' ? apiUrl('sw.js') : apiUrl('sw-web.js');
            const swScope = PLATFORM === 'mobile'
                ? (PATH_PREFIX ? `${PATH_PREFIX}/mobile/` : '/mobile/')
                : (PATH_PREFIX ? `${PATH_PREFIX}/web-push/` : '/web-push/');

            let reg = await navigator.serviceWorker.getRegistration(swScope);
            if (!reg) {
                reg = await navigator.serviceWorker.register(swFile, { scope: swScope, updateViaCache: 'none' });
            } else {
                reg.update().catch(function () {});
            }
            if (reg.waiting) {
                reg.waiting.postMessage({ action: 'SKIP_WAITING' });
            }

            const readyReg = await waitForSwActive(reg);
            let sub = await readyReg.pushManager.getSubscription();
            if (!sub) {
                sub = await readyReg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(state.vapidPublicKey),
                });
            }

            const subJson = sub.toJSON();
            const res = await postJson('api/notifikasi/push/subscribe', subJson);
            if (res && res.success) {
                state.pushSubscribed = true;
                const optBanner = document.getElementById('bip-push-optin-banner');
                if (optBanner) optBanner.remove();
                renderTopbarDropdown();
                if (showFeedback) {
                    playNotifSound();
                    if (readyReg && readyReg.showNotification) {
                        readyReg.showNotification('Push Notifikasi Aktif!', {
                            body: 'Perangkat ini siap menerima notifikasi background (seperti WhatsApp) meski aplikasi ditutup.',
                            icon: apiUrl('public/images/pwa/icon-192.png'),
                            badge: apiUrl('public/images/pwa/icon-192.png'),
                            tag: 'bip-push-activated',
                            silent: false,
                            vibrate: [200, 100, 200],
                            data: { url: PLATFORM === 'mobile' ? apiUrl('mobile/notifikasi') : apiUrl('dashboard') },
                        }).catch(function () {});
                    }
                    alert('Push Notification latar belakang aktif pada perangkat ini!');
                }
                return true;
            }
            if (showFeedback && res && res.message) {
                alert(res.message);
            }
        } catch (err) {
            if (showFeedback) {
                alert('Gagal mengaktifkan Push Notification: ' + (err.message || err));
            }
        }
        return false;
    }

    // ── Floating Push Card Renderer ─────────────────────────────────────
    const PRIORITAS_BORDER = {
        info: 'border-sky-300 bg-white',
        success: 'border-emerald-300 bg-white',
        warning: 'border-amber-300 bg-amber-50/40',
        urgent: 'border-rose-400 bg-rose-50/40',
    };

    function getPushContainer() {
        let el = document.getElementById('bip-push-toast-stack');
        if (!el) {
            el = document.createElement('div');
            el.id = 'bip-push-toast-stack';
            el.className = PLATFORM === 'mobile'
                ? 'fixed top-4 left-3 right-3 z-[9998] flex flex-col gap-2.5 max-w-md mx-auto pointer-events-none'
                : 'fixed top-20 right-5 z-[9998] flex flex-col gap-2.5 w-full max-w-sm pointer-events-none';
            document.body.appendChild(el);
        }
        return el;
    }

    function showFloatingPushCard(item) {
        const stack = getPushContainer();
        if (stack.querySelector(`[data-push-id="${item.id}"]`)) {
            return;
        }

        if (item.pakai_suara) {
            playNotifSound();
        }

        // Tampilkan juga OS Notification baik tab aktif maupun background (tag sama untuk deduplikasi)
        if ('Notification' in window && Notification.permission === 'granted') {
            const notifUrl = item.link_url || (PLATFORM === 'mobile' ? apiUrl('mobile/notifikasi') : apiUrl('dashboard'));
            const notifIcon = apiUrl('public/images/pwa/icon-192.png');
            const notifTag = 'pusat-notif-' + item.id;
            try {
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistrations().then(function (regs) {
                        const targetScope = PLATFORM === 'mobile' ? '/mobile/' : '/web-push/';
                        const reg = regs.find(r => (r.scope || '').includes(targetScope)) || regs[0];
                        if (reg && reg.showNotification) {
                            reg.showNotification(item.judul, {
                                body: item.pesan,
                                tag: notifTag,
                                renotify: true,
                                silent: false,
                                icon: notifIcon,
                                data: { url: notifUrl },
                            }).catch(function () {});
                        } else {
                            new Notification(item.judul, { body: item.pesan, icon: notifIcon, tag: notifTag });
                        }
                    }).catch(function () {
                        try { new Notification(item.judul, { body: item.pesan, icon: notifIcon, tag: notifTag }); } catch (e2) {}
                    });
                } else {
                    new Notification(item.judul, { body: item.pesan, icon: notifIcon, tag: notifTag });
                }
            } catch (e) {}
        }

        const card = document.createElement('div');
        card.setAttribute('data-push-id', String(item.id));
        const themeCls = PRIORITAS_BORDER[item.prioritas] || PRIORITAS_BORDER.info;
        card.className = `pointer-events-auto rounded-2xl border-2 shadow-2xl p-4 transition-all duration-300 ${themeCls}`;
        card.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary-600 text-white flex items-center justify-center shrink-0 text-base shadow-sm">
                    🔔
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h4 class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug">${escapeHtml(item.judul)}</h4>
                        <button type="button" class="bip-push-close text-slate-400 hover:text-slate-700 text-xs font-bold px-1 cursor-pointer">✕</button>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 whitespace-pre-line leading-relaxed">${escapeHtml(item.pesan)}</p>
                    <div class="mt-2.5 flex items-center justify-between gap-2">
                        <span class="text-[10px] font-semibold text-slate-400">${escapeHtml(item.created_by_name)} &bull; ${escapeHtml(item.time_ago)}</span>
                        ${item.link_url ? `<a href="${escapeHtml(item.link_url)}" class="bip-push-link inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-[11px] font-bold shadow-xs">${escapeHtml(item.link_label || 'Buka Tautan')} ↗</a>` : ''}
                    </div>
                </div>
            </div>
        `;

        const closeBtn = card.querySelector('.bip-push-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                postJson('api/notifikasi/read', { notifikasi_id: item.id, platform: PLATFORM }).then(function (r) {
                    if (r && typeof r.unread_count === 'number') {
                        state.unreadCount = r.unread_count;
                        updateUnreadBadges();
                    }
                }).catch(function () {});
                card.remove();
            });
        }

        const linkEl = card.querySelector('.bip-push-link');
        if (linkEl) {
            linkEl.addEventListener('click', function () {
                postJson('api/notifikasi/click', { notifikasi_id: item.id, platform: PLATFORM }).catch(function () {});
            });
        }

        stack.appendChild(card);
        setTimeout(function () {
            if (card.parentNode) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(-8px)';
                setTimeout(function () { if (card.parentNode) card.remove(); }, 300);
            }
        }, 12000);
    }

    // ── Popup Awalan & Berulang Tiap N Menit ────────────────────────────
    function getPopupOpenedSessionKey(id) {
        return `bip_popup_opened_${PLATFORM}_${state.userId}_${id}`;
    }

    function getPopupLastClosedKey(id) {
        return `bip_popup_last_${PLATFORM}_${state.userId}_${id}`;
    }

    function checkPopupsSchedule(isInitialLoad) {
        if (!state.userId || !state.popupItems.length) return;

        const nowMs = Date.now();
        state.popupItems.forEach(function (item) {
            const idStr = String(item.id);
            const openedInSession = sessionStorage.getItem(getPopupOpenedSessionKey(idStr));
            const lastClosedRaw = localStorage.getItem(getPopupLastClosedKey(idStr));
            const intervalMs = Math.max(1, Number(item.popup_interval_menit || 5)) * 60 * 1000;

            let shouldShow = false;
            if (!openedInSession && isInitialLoad) {
                shouldShow = true;
            } else if (!openedInSession && !lastClosedRaw) {
                shouldShow = true;
            } else if (lastClosedRaw) {
                const elapsed = nowMs - Number(lastClosedRaw);
                if (elapsed >= intervalMs) {
                    shouldShow = true;
                }
            }

            if (shouldShow && state.activePopupId !== item.id && !state.popupQueue.some(q => q.id === item.id)) {
                state.popupQueue.push(item);
            }
        });

        drainPopupQueue();
    }

    function drainPopupQueue() {
        if (state.activePopupId !== null || !state.popupQueue.length) {
            return;
        }
        const nextItem = state.popupQueue.shift();
        if (nextItem) {
            renderPopupModal(nextItem);
        }
    }

    const POPUP_HEAD_STYLES = {
        info: 'from-primary-600 to-indigo-600',
        success: 'from-emerald-600 to-teal-600',
        warning: 'from-amber-500 to-orange-500',
        urgent: 'from-rose-600 to-red-600',
    };

    function renderPopupModal(item) {
        state.activePopupId = item.id;
        sessionStorage.setItem(getPopupOpenedSessionKey(item.id), '1');

        if (item.pakai_suara) {
            playNotifSound();
        }

        const existing = document.getElementById('bip-periodic-popup-modal');
        if (existing) existing.remove();

        const grad = POPUP_HEAD_STYLES[item.prioritas] || POPUP_HEAD_STYLES.info;
        const unitLabel = (!item.target_units || item.target_units === 'ALL') ? 'Semua Unit' : `Unit ${item.target_units}`;

        const modal = document.createElement('div');
        modal.id = 'bip-periodic-popup-modal';
        modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/65 backdrop-blur-xs';
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-[fadeIn_.2s_ease-out]">
                <div class="bg-gradient-to-r ${grad} px-6 py-4 text-white flex items-start justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase tracking-wide mb-1.5">
                            🪟 Pemberitahuan Penting &bull; Tiap ${Number(item.popup_interval_menit || 5)} Menit
                        </span>
                        <h3 class="text-base sm:text-lg font-extrabold leading-snug">${escapeHtml(item.judul)}</h3>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-2xl border border-slate-200/80 max-h-60 overflow-y-auto">${escapeHtml(item.pesan)}</div>
                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
                        <span>Dari: <strong>${escapeHtml(item.created_by_name || 'Admin')}</strong></span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 font-semibold">${escapeHtml(unitLabel)}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                        ${item.link_url ? `<a href="${escapeHtml(item.link_url)}" id="bip-popup-link-btn" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold shadow-sm">${escapeHtml(item.link_label || 'Buka Tautan')} ↗</a>` : ''}
                        <button type="button" id="bip-popup-close-btn" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-bold cursor-pointer">
                            Tutup (${Number(item.popup_interval_menit || 5)} mnt lagi)
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        function closePopup(markReadFlag) {
            localStorage.setItem(getPopupLastClosedKey(item.id), String(Date.now()));
            if (markReadFlag) {
                postJson('api/notifikasi/read', { notifikasi_id: item.id, platform: PLATFORM }).then(function (r) {
                    if (r && typeof r.unread_count === 'number') {
                        state.unreadCount = r.unread_count;
                        updateUnreadBadges();
                    }
                }).catch(function () {});
            }
            modal.remove();
            state.activePopupId = null;
            drainPopupQueue();
        }

        const closeBtn = modal.querySelector('#bip-popup-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                closePopup(true);
            });
        }

        const linkBtn = modal.querySelector('#bip-popup-link-btn');
        if (linkBtn) {
            linkBtn.addEventListener('click', function () {
                localStorage.setItem(getPopupLastClosedKey(item.id), String(Date.now()));
                postJson('api/notifikasi/click', { notifikasi_id: item.id, platform: PLATFORM }).catch(function () {});
            });
        }
    }

    // ── Unread Badges & Topbar Bell Dropdown (Web) ──────────────────────
    function updateUnreadBadges() {
        const webBadge = document.getElementById('topbar-notif-badge');
        if (webBadge) {
            if (state.unreadCount > 0) {
                webBadge.textContent = state.unreadCount > 99 ? '99+' : String(state.unreadCount);
                webBadge.classList.remove('hidden');
            } else {
                webBadge.classList.add('hidden');
            }
        }

        const mobBadge = document.getElementById('mobile-bell-badge');
        if (mobBadge) {
            mobBadge.classList.toggle('hidden', state.unreadCount <= 0);
        }
    }

    function renderTopbarDropdown() {
        const menu = document.getElementById('notif-dropdown-menu');
        if (!menu) return;

        const listEl = menu.querySelector('#notif-dropdown-list');
        const pushCtaEl = menu.querySelector('#notif-dropdown-push-cta');
        const manageLink = menu.querySelector('#notif-dropdown-manage-link');

        if (manageLink) {
            manageLink.classList.toggle('hidden', !state.canManage);
            manageLink.setAttribute('href', state.manageUrl || apiUrl('pusat-notifikasi'));
        }

        if (pushCtaEl) {
            const canSupportPush = ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);
            pushCtaEl.classList.toggle('hidden', !canSupportPush || state.pushSubscribed);
        }

        if (!listEl) return;
        if (!state.inboxItems.length) {
            listEl.innerHTML = '<div class="p-6 text-center text-xs text-slate-400">Belum ada notifikasi baru.</div>';
            return;
        }

        listEl.innerHTML = state.inboxItems.slice(0, 12).map(function (it) {
            const unreadCls = it.is_read ? 'bg-white' : 'bg-primary-50/40';
            const badgeTipe = it.tipe === 'popup' ? '🪟 Popup' : '📣 Push';
            return `
                <div class="p-3 border-b border-slate-100 hover:bg-slate-50 transition-colors ${unreadCls}">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">${badgeTipe}</span>
                        <span class="text-[10px] text-slate-400">${escapeHtml(it.time_ago)}</span>
                    </div>
                    <div class="font-bold text-xs text-slate-900 mt-1">${escapeHtml(it.judul)}</div>
                    <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2">${escapeHtml(it.pesan)}</p>
                    ${it.link_url ? `<a href="${escapeHtml(it.link_url)}" data-click-id="${it.id}" class="bip-dd-link inline-block mt-1.5 text-[11px] font-bold text-primary-600 hover:underline">${escapeHtml(it.link_label || 'Buka Tautan')} ↗</a>` : ''}
                </div>
            `;
        }).join('');

        listEl.querySelectorAll('.bip-dd-link').forEach(function (a) {
            a.addEventListener('click', function () {
                const nid = Number(a.getAttribute('data-click-id') || 0);
                if (nid > 0) {
                    postJson('api/notifikasi/click', { notifikasi_id: nid, platform: PLATFORM }).catch(function () {});
                }
            });
        });
    }

    function bindTopbarBell() {
        const container = document.getElementById('notif-dropdown-container');
        const btn = document.getElementById('btn-notifications');
        const menu = document.getElementById('notif-dropdown-menu');
        if (!btn || !menu) return;

        if (!btn.dataset.notifBound) {
            btn.dataset.notifBound = '1';
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('hidden');
                if (!menu.classList.contains('hidden')) {
                    renderTopbarDropdown();
                }
            });
        }

        const markAllBtn = menu.querySelector('#notif-dropdown-mark-all');
        if (markAllBtn && !markAllBtn.dataset.notifBound) {
            markAllBtn.dataset.notifBound = '1';
            markAllBtn.addEventListener('click', function () {
                markAllRead();
            });
        }

        const enableBtn = menu.querySelector('#notif-dropdown-enable-push');
        if (enableBtn && !enableBtn.dataset.notifBound) {
            enableBtn.dataset.notifBound = '1';
            enableBtn.addEventListener('click', function () {
                enableBrowserPush(true);
            });
        }

        if (!window.__BipNotifDocClickBound) {
            window.__BipNotifDocClickBound = true;
            document.addEventListener('click', function (e) {
                const cont = document.getElementById('notif-dropdown-container');
                const m = document.getElementById('notif-dropdown-menu');
                if (cont && m && !cont.contains(e.target)) {
                    m.classList.add('hidden');
                }
            });
        }
    }

    async function markAllRead() {
        try {
            const res = await postJson('api/notifikasi/read', { all: 1, platform: PLATFORM });
            if (res && res.success) {
                state.unreadCount = 0;
                state.inboxItems = state.inboxItems.map(function (it) {
                    return Object.assign({}, it, { is_read: true });
                });
                updateUnreadBadges();
                renderTopbarDropdown();
            }
            return res;
        } catch (e) {
            return { success: false };
        }
    }

    function maybeShowPushOptInBanner() {
        if (!state.userId || state.pushSubscribed) return;
        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) return;
        if (Notification.permission !== 'default') return;
        if (document.getElementById('bip-push-optin-banner')) return;

        const dismissKey = `bip_push_optin_dismiss_${state.userId}`;
        try {
            const lastDismiss = Number(localStorage.getItem(dismissKey) || 0);
            if (lastDismiss && (Date.now() - lastDismiss) < 86400000) {
                return;
            }
        } catch (e) {}

        const banner = document.createElement('div');
        banner.id = 'bip-push-optin-banner';
        banner.className = PLATFORM === 'mobile'
            ? 'fixed bottom-20 left-3 right-3 z-[9990] max-w-md mx-auto bg-slate-900 text-white rounded-2xl shadow-2xl border border-slate-700 p-3.5 flex items-center justify-between gap-3'
            : 'fixed bottom-5 right-5 z-[9990] max-w-sm bg-slate-900 text-white rounded-2xl shadow-2xl border border-slate-700 p-4 flex items-center justify-between gap-3';
        banner.innerHTML = `
            <div class="min-w-0 flex-1">
                <div class="text-xs font-extrabold text-white">Aktifkan Push Notifikasi?</div>
                <p class="text-[11px] text-slate-300 mt-0.5 leading-snug">Agar pengumuman &amp; pesan penting berbunyi otomatis di perangkat ini.</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" id="bip-optin-dismiss" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-300 cursor-pointer">
                    Nanti
                </button>
                <button type="button" id="bip-optin-allow" class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-[11px] font-extrabold text-white cursor-pointer">
                    🔔 Aktifkan
                </button>
            </div>
        `;
        document.body.appendChild(banner);

        const dismissBtn = banner.querySelector('#bip-optin-dismiss');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function () {
                try { localStorage.setItem(dismissKey, String(Date.now())); } catch (e) {}
                banner.remove();
            });
        }
        const allowBtn = banner.querySelector('#bip-optin-allow');
        if (allowBtn) {
            allowBtn.addEventListener('click', function () {
                enableBrowserPush(true);
            });
        }
    }

    // ── Polling Feed Engine ─────────────────────────────────────────────
    let firstFetchDone = false;
    async function fetchFeed() {
        try {
            const res = await fetch(apiUrl(`api/notifikasi/feed?platform=${encodeURIComponent(PLATFORM)}`), {
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.success) return;

            state.userId = Number(data.user_id || 0);
            state.vapidPublicKey = data.vapid_public_key || '';
            state.pushSubscribed = Boolean(data.push_subscribed);
            state.canManage = Boolean(data.can_manage);
            if (data.manage_url) state.manageUrl = data.manage_url;

            state.inboxItems = Array.isArray(data.inbox_items) ? data.inbox_items : [];
            state.popupItems = Array.isArray(data.popup_items) ? data.popup_items : [];
            state.unreadCount = Number(data.unread_count || 0);

            updateUnreadBadges();
            renderTopbarDropdown();

            // Tampilkan push baru yang belum pernah dimunculkan (per id + sent_at agar Kirim Ulang tetap tampil)
            const seenMap = getSeenPushMap();
            const pushItems = Array.isArray(data.push_items) ? data.push_items : [];
            pushItems.forEach(function (it) {
                const seenKey = String(it.id) + '_' + String(it.sent_at || '');
                if (!it.is_read && !seenMap[seenKey]) {
                    markPushSeenLocal(seenKey);
                    showFloatingPushCard(it);
                }
            });

            // Cek jadwal popup (awalan saat load + setiap N menit)
            checkPopupsSchedule(!firstFetchDone);
            firstFetchDone = true;

            // Silent auto-resubscribe bila user sudah granted izin notifikasi namun belum tersimpan di server
            if (!state.autoSubTried && !state.pushSubscribed && 'Notification' in window && Notification.permission === 'granted') {
                state.autoSubTried = true;
                enableBrowserPush(false);
            } else if (!state.pushSubscribed) {
                maybeShowPushOptInBanner();
            }
        } catch (e) {}
    }

    window.BipNotifCenter = {
        fetchFeed: fetchFeed,
        enableBrowserPush: enableBrowserPush,
        markAllRead: markAllRead,
        playNotifSound: playNotifSound,
        markClick: function (id) {
            return postJson('api/notifikasi/click', { notifikasi_id: id, platform: PLATFORM });
        },
        markRead: function (id) {
            return postJson('api/notifikasi/read', { notifikasi_id: id, platform: PLATFORM });
        },
        rebindTopbar: bindTopbarBell,
    };

    function init() {
        bindTopbarBell();
        fetchFeed();
        setInterval(fetchFeed, 20000);
        setInterval(function () { checkPopupsSchedule(false); }, 15000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                fetchFeed();
            }
        });
        if (window.jQuery) {
            window.jQuery(document).on('portal:pjax:complete', function () {
                bindTopbarBell();
                updateUnreadBadges();
                renderTopbarDropdown();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
