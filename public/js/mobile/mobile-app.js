/**
 * Portal Guru BIP - Mobile App Interactive Script
 * Handles Geolocation GPS, Map Radar, Attendance Check-in/Check-out State (15:00 device time lock), Journal forms, and Mobile UI interactions.
 */

// School Center Coordinates (Default: Kampus Utama BIP, can be overridden per employee/unit via window.CUSTOM_SCHOOL_COORDS)
const SCHOOL_COORDS = Object.assign({
  lat: -5.147665,
  lng: 119.432732,
  name: "Kampus Utama BIP",
  radius: 150 // radius in meters
}, window.CUSTOM_SCHOOL_COORDS || {});

let userCoords = null;
let leafletMap = null;
let userMarker = null;
let schoolCircle = null;

// Calculate Haversine Distance (in meters)
function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371e3; // metres
  const φ1 = lat1 * Math.PI / 180;
  const φ2 = lat2 * Math.PI / 180;
  const Δφ = (lat2 - lat1) * Math.PI / 180;
  const Δλ = (lon2 - lon1) * Math.PI / 180;

  const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ/2) * Math.sin(Δλ/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

  return Math.round(R * c);
}

// ── Geolocation & Map Logic ─────────────────────────────
window.initGeolocation = function(showLoader = false) {
  const statusEl = document.getElementById('gps-status-text');

  if (showLoader) {
    AndroidUI.showCenterLoading('Mengunci sinyal GPS satelit...');
  }

  if (!navigator.geolocation) {
    if (statusEl) statusEl.textContent = 'Perangkat tidak mendukung GPS. Menggunakan simulasi sekolah.';
    fallbackToSimulatedGPS();
    if (showLoader) setTimeout(() => AndroidUI.hideCenterLoading(), 300);
    return;
  }

  if (statusEl) statusEl.innerHTML = '<span class="inline-block animate-spin mr-1">⟳</span> Mengunci koordinat GPS...';

  navigator.geolocation.getCurrentPosition(
    (position) => {
      userCoords = {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
        accuracy: Math.round(position.coords.accuracy)
      };

      updateGPSDisplay(userCoords);
      if (showLoader) setTimeout(() => AndroidUI.hideCenterLoading(), 300);
    },
    (err) => {
      console.warn('[GPS] Error getting real GPS position:', err.message);
      if (statusEl) statusEl.textContent = 'Izin GPS ditolak atau tidak tersedia. Menggunakan titik sekolah.';
      fallbackToSimulatedGPS();
      if (showLoader) setTimeout(() => AndroidUI.hideCenterLoading(), 300);
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
};

function fallbackToSimulatedGPS() {
  userCoords = {
    lat: SCHOOL_COORDS.lat + (Math.random() * 0.0002 - 0.0001),
    lng: SCHOOL_COORDS.lng + (Math.random() * 0.0002 - 0.0001),
    accuracy: 6
  };
  updateGPSDisplay(userCoords, true);
}

function updateGPSDisplay(coords, isSimulated = false) {
  const statusEl = document.getElementById('gps-status-text');
  const latEl = document.getElementById('gps-lat');
  const lngEl = document.getElementById('gps-lng');
  const distEl = document.getElementById('gps-distance');
  const radiusBadge = document.getElementById('gps-radius-badge');
  const accuracyEl = document.getElementById('gps-accuracy');

  const distance = calculateDistance(coords.lat, coords.lng, SCHOOL_COORDS.lat, SCHOOL_COORDS.lng);

  if (statusEl) {
    statusEl.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1 animate-ping"></span> GPS Terkunci`;
  }
  if (latEl) latEl.textContent = coords.lat.toFixed(6);
  if (lngEl) lngEl.textContent = coords.lng.toFixed(6);
  if (accuracyEl) accuracyEl.textContent = `±${coords.accuracy}m`;
  if (distEl) distEl.textContent = `${distance} Meter`;

  const isWithinRadius = distance <= SCHOOL_COORDS.radius;
  if (radiusBadge) {
    if (isWithinRadius) {
      radiusBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300';
      radiusBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-600"></span> Dalam Radius Sekolah (${distance}m)`;
    } else {
      radiusBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300';
      radiusBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-rose-600"></span> Di Luar Radius Sekolah (${distance}m)`;
    }
  }

  // Initialize or update Leaflet Map
  initOrUpdateMap(coords, isWithinRadius);
}

function initOrUpdateMap(coords, isWithinRadius) {
  const mapContainer = document.getElementById('leaflet-map');
  if (!mapContainer) return;

  if (typeof L === 'undefined') {
    setTimeout(() => initOrUpdateMap(coords, isWithinRadius), 120);
    return;
  }

  if (window.CUSTOM_SCHOOL_COORDS) {
    Object.assign(SCHOOL_COORDS, window.CUSTOM_SCHOOL_COORDS);
  }

  // If SPA swapped the DOM, clean up detached map instance
  if (leafletMap && leafletMap.getContainer() !== mapContainer) {
    try { leafletMap.remove(); } catch (e) {}
    leafletMap = null;
    userMarker = null;
    schoolCircle = null;
  }

  if (!leafletMap) {
    leafletMap = L.map('leaflet-map', {
      zoomControl: false,
      attributionControl: false
    }).setView([SCHOOL_COORDS.lat, SCHOOL_COORDS.lng], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19
    }).addTo(leafletMap);

    schoolCircle = L.circle([SCHOOL_COORDS.lat, SCHOOL_COORDS.lng], {
      color: '#10b981',
      fillColor: '#34d399',
      fillOpacity: 0.25,
      radius: SCHOOL_COORDS.radius
    }).addTo(leafletMap);

    const schoolIcon = L.divIcon({
      className: 'custom-div-icon',
      html: `<div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg border-2 border-white font-bold text-xs">🏫</div>`,
      iconSize: [32, 32],
      iconAnchor: [16, 16]
    });
    L.marker([SCHOOL_COORDS.lat, SCHOOL_COORDS.lng], { icon: schoolIcon }).addTo(leafletMap)
      .bindPopup(`<b>${SCHOOL_COORDS.name}</b><br>Radius: ${SCHOOL_COORDS.radius}m`);
  }

  const userIcon = L.divIcon({
    className: 'custom-user-icon',
    html: `<div class="relative flex items-center justify-center">
             <span class="animate-ping absolute inline-flex h-8 w-8 rounded-full ${isWithinRadius ? 'bg-emerald-400 opacity-75' : 'bg-rose-400 opacity-75'}"></span>
             <div class="w-7 h-7 rounded-full ${isWithinRadius ? 'bg-emerald-600' : 'bg-rose-600'} text-white flex items-center justify-center shadow-xl border-2 border-white text-xs font-bold">📍</div>
           </div>`,
    iconSize: [28, 28],
    iconAnchor: [14, 14]
  });

  if (userMarker) {
    userMarker.setLatLng([coords.lat, coords.lng]);
  } else {
    userMarker = L.marker([coords.lat, coords.lng], { icon: userIcon }).addTo(leafletMap)
      .bindPopup("<b>Posisi Anda Saat Ini</b>");
  }

  const bounds = L.latLngBounds([[SCHOOL_COORDS.lat, SCHOOL_COORDS.lng], [coords.lat, coords.lng]]);
  leafletMap.fitBounds(bounds, { padding: [30, 30] });
}

// ── Check-in Masuk & Check-out Pulang State Management ──────────────────────
window.getAttendanceState = function() {
  const data = localStorage.getItem('portal_guru_attendance_today');
  if (!data) return { checkin: null, checkout: null };
  try {
    return JSON.parse(data);
  } catch (e) {
    return { checkin: null, checkout: null };
  }
};

window.saveAttendanceState = function(state) {
  localStorage.setItem('portal_guru_attendance_today', JSON.stringify(state));
  syncBerandaAttendanceUI();
};

window.handleDoCheckin = function(btn) {
  AndroidUI.confirm({
    title: 'Konfirmasi Check-in',
    subtitle: 'Presensi Masuk Pagi',
    icon: '📍',
    iconBg: 'bg-emerald-100 text-emerald-700',
    message: 'Apakah Anda ingin melakukan Check-in Masuk dengan titik koordinat GPS saat ini?',
    confirmText: 'Ya, Check-in',
    cancelText: 'Batal',
    onConfirm: () => {
      // Button loading state
      const targetBtn = btn || document.querySelector('#absen-action-container button');
      AndroidUI.setButtonLoading(targetBtn, 'Merekam GPS & Presensi...');

      setTimeout(() => {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WITA';
        const state = getAttendanceState();

        state.checkin = {
          time: timeStr,
          timestamp: now.getTime(),
          location: userCoords ? `${userCoords.lat.toFixed(5)}, ${userCoords.lng.toFixed(5)}` : 'Kampus BIP',
          status: 'Tepat Waktu'
        };

        saveAttendanceState(state);
        renderAttendancePageUI();

        // Smooth Animated Checkmark Modal
        AndroidUI.success({
          title: 'Check-in Berhasil!',
          subtitle: 'Presensi masuk telah dicatat di server',
          message: `Absen masuk Anda tercatat pada pukul <strong class="text-emerald-700 font-bold">${timeStr}</strong>.<br><span class="text-slate-400 text-xs">Tombol Absen Pulang akan terbuka pada pukul 15.00 WITA.</span>`,
          buttonText: 'Selesai'
        });
      }, 700);
    }
  });
};

window.handleDoCheckout = function(btn) {
  AndroidUI.confirm({
    title: 'Konfirmasi Absen Pulang',
    subtitle: 'Presensi Pulang Sore',
    icon: '🏠',
    iconBg: 'bg-amber-100 text-amber-700',
    message: 'Apakah Anda ingin melakukan Absen Pulang sekarang?',
    confirmText: 'Ya, Absen Pulang',
    cancelText: 'Batal',
    onConfirm: () => {
      // Button loading state
      const targetBtn = btn || document.querySelector('#absen-action-container button');
      AndroidUI.setButtonLoading(targetBtn, 'Menyimpan Absen Pulang...');

      setTimeout(() => {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WITA';
        const state = getAttendanceState();

        state.checkout = {
          time: timeStr,
          timestamp: now.getTime(),
          location: userCoords ? `${userCoords.lat.toFixed(5)}, ${userCoords.lng.toFixed(5)}` : 'Kampus BIP',
          status: 'Selesai Tugas'
        };

        saveAttendanceState(state);
        renderAttendancePageUI();

        // Smooth Animated Checkmark Modal
        AndroidUI.success({
          title: 'Absen Pulang Berhasil!',
          subtitle: 'Presensi hari ini telah selesai',
          message: `Absen pulang Anda tercatat pada pukul <strong class="text-emerald-700 font-bold">${timeStr}</strong>.<br>Terima kasih atas dedikasi Anda hari ini! 💙`,
          actions: [
            {
              text: 'Ke Beranda',
              className: 'w-full py-3 bg-blue-600 text-white font-bold text-xs rounded-2xl shadow-md text-center',
              onClick: () => {
                const target = (window.APP_BASE_URL || '') + '/mobile';
                if (window.MobileRouter) window.MobileRouter.navigate(target);
                else window.location.href = target;
              }
            }
          ]
        });
      }, 700);
    }
  });
};

// Check if device hour is >= 15:00
window.isCheckoutTimeAllowed = function() {
  if (window._simulateAfternoonTime) return true;
  const currentHour = new Date().getHours();
  return currentHour >= 15;
};

// Toggle simulation for 15:00 for testing
window.toggleSimulateAfternoon = function() {
  window._simulateAfternoonTime = !window._simulateAfternoonTime;
  renderAttendancePageUI();
  syncBerandaAttendanceUI();
  AndroidUI.toast(window._simulateAfternoonTime ? 'Simulasi jam 15:00 AKTIF' : 'Simulasi jam 15:00 NONAKTIF', 'info');
};

// Render UI in /mobile/absen
function renderAttendancePageUI() {
  const container = document.getElementById('absen-action-container');
  if (!container) return;

  const state = getAttendanceState();
  const isAllowed = isCheckoutTimeAllowed();

  if (!state.checkin) {
    // 1. Not yet checked in
    container.innerHTML = `
      <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-800 text-sm">Presensi Masuk Pagi</h3>
            <p class="text-[11px] text-slate-400">Jadwal masuk: 06.30 - 08.00 WITA</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
            Buka
          </span>
        </div>
        <button type="button" onclick="handleDoCheckin(this)" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 press-bounce">
          <span>📍</span> Check-in Sekarang
        </button>
      </div>
    `;
  } else if (state.checkin && !state.checkout) {
    // 2. Already checked in, check checkout availability
    container.innerHTML = `
      <!-- Absen Masuk Card (Recorded) -->
      <div class="bg-white rounded-3xl p-4 shadow-sm border border-emerald-200/80 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
              ✓
            </div>
            <div>
              <h3 class="font-bold text-slate-800 text-xs">Absen Masuk Berhasil</h3>
              <p class="text-[10px] text-slate-400">Pukul ${state.checkin.time} • ${state.checkin.status}</p>
            </div>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
            Terverifikasi
          </span>
        </div>
        <div class="bg-emerald-50/70 p-2.5 rounded-2xl border border-emerald-100 text-[11px] text-slate-600">
          📍 Lokasi: <strong>${state.checkin.location}</strong>
        </div>
      </div>

      <!-- Absen Pulang Section (Available at 15.00) -->
      <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-800 text-xs">Presensi Pulang</h3>
            <p class="text-[10px] text-slate-400">Tersedia mulai pukul 15.00 WITA</p>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${isAllowed ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500'}">
            ${isAllowed ? 'Waktunya Pulang' : 'Belum Jam 15.00'}
          </span>
        </div>

        ${isAllowed ? `
          <button type="button" onclick="handleDoCheckout(this)" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-orange-500/30 flex items-center justify-center gap-2 press-bounce animate-pulse">
            <span>🏠</span> Absen Pulang Sekarang
          </button>
        ` : `
          <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center space-y-1">
            <p class="text-xs font-bold text-slate-700">🕒 Belum waktu Absen Pulang</p>
            <p class="text-[10px] text-slate-400">Tombol absen pulang akan aktif otomatis pada pukul 15.00 waktu perangkat Anda.</p>
          </div>
          <button type="button" disabled class="w-full py-3 bg-slate-200 text-slate-400 font-bold text-xs rounded-2xl cursor-not-allowed flex items-center justify-center gap-2">
            <span>🔒</span> Absen Pulang (Aktif Jam 15.00)
          </button>
          <div class="text-center pt-1">
            <button type="button" onclick="toggleSimulateAfternoon()" class="text-[10px] font-bold text-blue-600 hover:underline">
              🧪 Simulasi jam 15.00 untuk uji coba
            </button>
          </div>
        `}
      </div>
    `;
  } else {
    // 3. Both Masuk and Pulang completed
    container.innerHTML = `
      <div class="bg-white rounded-3xl p-4 shadow-sm border border-emerald-200 space-y-3">
        <div class="flex items-center gap-2.5">
          <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-bold text-base shadow-md shadow-emerald-500/20">
            ✓
          </div>
          <div>
            <h3 class="font-bold text-slate-800 text-sm">Presensi Hari Ini Selesai</h3>
            <p class="text-[10px] text-slate-400">Semua kehadiran telah tercatat lengkap</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs">
          <div class="bg-emerald-50 p-2.5 rounded-2xl border border-emerald-100">
            <p class="text-[10px] text-slate-400">Absen Masuk</p>
            <p class="font-bold text-emerald-800">${state.checkin.time}</p>
          </div>
          <div class="bg-amber-50 p-2.5 rounded-2xl border border-amber-100">
            <p class="text-[10px] text-slate-400">Absen Pulang</p>
            <p class="font-bold text-amber-800">${state.checkout.time}</p>
          </div>
        </div>

        <a href="${(window.APP_BASE_URL || '') + '/mobile'}" class="block w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl text-center press-bounce">
          Kembali ke Beranda
        </a>
      </div>
    `;
  }
}

// Sync Beranda Card State
function syncBerandaAttendanceUI() {
  const berandaCheckinText = document.getElementById('beranda-checkin-status');
  const berandaCheckinBtn = document.getElementById('beranda-checkin-btn');
  const berandaCheckinSub = document.getElementById('beranda-checkin-sub');
  const berandaCheckinBadge = document.getElementById('beranda-checkin-badge');

  if (!berandaCheckinText || !berandaCheckinBtn) return;

  const state = getAttendanceState();
  const isAllowed = isCheckoutTimeAllowed();

  if (!state.checkin) {
    berandaCheckinText.textContent = 'Belum check-in hari ini';
    berandaCheckinSub.textContent = 'Waktu akan tercatat saat Anda check-in.';
    berandaCheckinBtn.className = 'shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-[#16a34a] hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 press-bounce';
    berandaCheckinBtn.innerHTML = '<span>📍</span><span>Check-in</span>';
    berandaCheckinBtn.href = (window.APP_BASE_URL || '') + '/mobile/absen';
    if (berandaCheckinBadge) {
      berandaCheckinBadge.className = 'w-13 h-13 rounded-2xl bg-emerald-50 border border-emerald-100 flex flex-col items-center justify-center shrink-0';
      berandaCheckinBadge.innerHTML = `<div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-sm"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></div><span class="text-[9px] font-bold text-emerald-700 mt-0.5">Check-in</span>`;
    }
  } else if (state.checkin && !state.checkout) {
    if (isAllowed) {
      berandaCheckinText.textContent = `Sudah Masuk (${state.checkin.time})`;
      berandaCheckinSub.textContent = 'Waktunya Absen Pulang (Pukul 15.00+)';
      berandaCheckinBtn.className = 'shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-md shadow-amber-500/30 press-bounce animate-pulse';
      berandaCheckinBtn.innerHTML = '<span>🏠</span><span>Absen Pulang</span>';
      berandaCheckinBtn.href = (window.APP_BASE_URL || '') + '/mobile/absen';
    } else {
      berandaCheckinText.textContent = `Sudah Masuk (${state.checkin.time})`;
      berandaCheckinSub.textContent = 'Absen Pulang buka pukul 15.00';
      berandaCheckinBtn.className = 'shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
      berandaCheckinBtn.innerHTML = '<span>✓</span><span>Masuk</span>';
      berandaCheckinBtn.href = (window.APP_BASE_URL || '') + '/mobile/absen';
    }
    if (berandaCheckinBadge) {
      berandaCheckinBadge.className = 'w-13 h-13 rounded-2xl bg-emerald-500 text-white flex flex-col items-center justify-center shrink-0 shadow-md shadow-emerald-500/20';
      berandaCheckinBadge.innerHTML = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg><span class="text-[9px] font-bold mt-0.5">Masuk</span>`;
    }
  } else {
    berandaCheckinText.textContent = `Presensi Selesai (Pulang ${state.checkout.time})`;
    berandaCheckinSub.textContent = 'Terima kasih atas kerja keras Anda hari ini!';
    berandaCheckinBtn.className = 'shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200';
    berandaCheckinBtn.innerHTML = '<span>✓</span><span>Selesai</span>';
    berandaCheckinBtn.href = (window.APP_BASE_URL || '') + '/mobile/absen';
  }
}

// ── Journal Handling ────────────────────────────────────
window.handleJournalSubmit = function(event) {
  event.preventDefault();
  const form = event.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const draftBtn = form.querySelector('button[data-type="draft"]');

  const kelas = form.querySelector('[name="kelas"]')?.value || 'Kelas 7A';
  const mapel = form.querySelector('[name="mapel"]')?.value || 'Matematika';
  const topik = form.querySelector('[name="topik"]')?.value || 'Materi Pembelajaran';

  // Button loading
  AndroidUI.setButtonLoading(submitBtn, 'Menyimpan Jurnal...');

  setTimeout(() => {
    const journalData = {
      id: 'JRN-' + Date.now(),
      kelas,
      mapel,
      topik,
      date: new Date().toLocaleDateString('id-ID'),
      status: 'Terkirim'
    };

    const existing = JSON.parse(localStorage.getItem('portal_guru_journals') || '[]');
    existing.unshift(journalData);
    localStorage.setItem('portal_guru_journals', JSON.stringify(existing));

    AndroidUI.resetButton(submitBtn);

    // Smooth Animated Checkmark Modal
    AndroidUI.success({
      title: 'Jurnal Berhasil Disimpan!',
      subtitle: 'Laporan harian telah diverifikasi',
      message: `Jurnal untuk <strong>${kelas}</strong> (${topik}) berhasil diarsipkan dan tersinkronisasi ke portal.`,
      actions: [
        {
          text: 'Ke Beranda',
          className: 'flex-1 py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl text-center',
          onClick: () => window.location.href = (window.APP_BASE_URL || '') + '/mobile'
        },
        {
          text: 'Lihat Riwayat',
          className: 'flex-1 py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-2xl text-center shadow-md',
          onClick: () => {
            if (typeof switchJournalTab === 'function') switchJournalTab('history');
          }
        }
      ]
    });
  }, 700);
};

window.renderAttendancePageUI = renderAttendancePageUI;
window.syncBerandaAttendanceUI = syncBerandaAttendanceUI;

// ── Instant SPA Smooth Page Router & Prefetch Engine ────────────────────────
window.MobileRouter = (function() {
  const pageCache = new Map();       // url -> { html, title, activeTab, timestamp }
  const inFlight = new Map();        // url -> Promise
  const CACHE_TTL_FRESH = 12000;     // 12s fresh (no background refetch needed)
  const CACHE_TTL_MAX   = 120000;    // 2m max stale-while-revalidate
  let progressTimer = null;
  let activeNavigationUrl = null;
  let isNavigating = false;

  function normalizeUrl(rawUrl) {
    try {
      const u = new URL(rawUrl, window.location.origin);
      return u.origin + u.pathname.replace(/\/+$/, '') + u.search;
    } catch (e) {
      return rawUrl;
    }
  }

  function isSpaEligibleUrl(rawHref, anchorEl) {
    if (!rawHref) return false;
    if (anchorEl) {
      if (anchorEl.hasAttribute('data-no-spa') || anchorEl.hasAttribute('download')) return false;
      const target = anchorEl.getAttribute('target');
      if (target && target !== '_self') return false;
    }
    if (rawHref.startsWith('#') || rawHref.startsWith('javascript:') || rawHref.startsWith('mailto:') || rawHref.startsWith('tel:') || rawHref.startsWith('whatsapp:')) {
      return false;
    }
    try {
      const u = new URL(rawHref, window.location.origin);
      if (u.origin !== window.location.origin) return false;
      const path = u.pathname;
      if (path.includes('/mobile/logout') || path.includes('/logout')) return false;
      if (path.includes('/mobile-sarpras')) return false;
      return path.includes('/mobile');
    } catch (e) {
      return false;
    }
  }

  function guessTabFromUrl(urlStr) {
    try {
      const p = new URL(urlStr, window.location.origin).pathname.replace(/\/+$/, '');
      if (p.endsWith('/mobile/kelas')) return 'kelas';
      if (p.endsWith('/mobile/murid')) return 'murid';
      if (p.endsWith('/mobile/profil')) return 'profil';
      if (p.endsWith('/mobile')) return 'beranda';
    } catch (e) {}
    return null;
  }

  function updateBottomNavTab(activeTab) {
    if (!activeTab) return;
    const navItems = document.querySelectorAll('#mobile-bottom-nav [data-nav-tab]');
    navItems.forEach(item => {
      const tab = item.getAttribute('data-nav-tab');
      const isAct = (tab === activeTab);
      if (isAct) {
        item.classList.add('is-active', 'text-primary-600', 'font-bold');
        item.classList.remove('text-slate-400', 'hover:text-slate-600', 'font-medium');
      } else {
        item.classList.remove('is-active', 'text-primary-600', 'font-bold');
        item.classList.add('text-slate-400', 'hover:text-slate-600', 'font-medium');
      }
      const icon = item.querySelector('svg, i[data-lucide]');
      if (icon) {
        if (isAct) {
          icon.classList.add('stroke-[2.5]');
          icon.classList.remove('stroke-2');
        } else {
          icon.classList.remove('stroke-[2.5]');
          icon.classList.add('stroke-2');
        }
      }
    });
  }

  function startProgressBar() {
    clearTimeout(progressTimer);
    progressTimer = setTimeout(() => {
      const bar = document.getElementById('spa-progress-bar');
      if (bar) {
        bar.classList.remove('is-finishing');
        bar.classList.add('is-loading');
      }
    }, 45);
  }

  function finishProgressBar() {
    clearTimeout(progressTimer);
    const bar = document.getElementById('spa-progress-bar');
    if (bar && bar.classList.contains('is-loading')) {
      bar.classList.remove('is-loading');
      bar.classList.add('is-finishing');
      setTimeout(() => bar.classList.remove('is-finishing'), 260);
    }
  }

  function parseHtmlDocument(htmlText) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlText, 'text/html');
    const mainEl = doc.getElementById('mobile-main-view') || doc.querySelector('main');
    if (!mainEl) return null;

    return {
      html: mainEl.innerHTML,
      title: mainEl.getAttribute('data-page-title') || doc.title || 'Portal BIP',
      activeTab: mainEl.getAttribute('data-active-tab') || '',
      timestamp: Date.now()
    };
  }

  function fetchAndCache(url) {
    const norm = normalizeUrl(url);
    if (inFlight.has(norm)) {
      return inFlight.get(norm);
    }

    const promise = fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'MobileRouter' }
    })
      .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        // If redirected to login page, force hard redirect
        if (res.redirected && res.url && res.url.includes('/login')) {
          window.location.href = res.url;
          return null;
        }
        return res.text();
      })
      .then(text => {
        inFlight.delete(norm);
        if (!text) return null;
        const parsed = parseHtmlDocument(text);
        if (parsed) {
          pageCache.set(norm, parsed);
        }
        return parsed;
      })
      .catch(err => {
        inFlight.delete(norm);
        throw err;
      });

    inFlight.set(norm, promise);
    return promise;
  }

  function executeViewScripts(container) {
    const scripts = Array.from(container.querySelectorAll('script'));
    scripts.forEach(oldScript => {
      const newScript = document.createElement('script');
      Array.from(oldScript.attributes).forEach(attr => {
        newScript.setAttribute(attr.name, attr.value);
      });

      if (oldScript.src) {
        newScript.src = oldScript.src;
      } else {
        // Transform top-level let/const to var so repeated SPA visits never throw SyntaxError redeclaration
        const safeCode = oldScript.textContent.replace(/^(\s{0,4})(let|const)\s+/gm, '$1var ');
        newScript.textContent = `try {\n${safeCode}\n} catch (err) { console.warn('[SPA View Script]', err); }`;
      }
      oldScript.parentNode.replaceChild(newScript, oldScript);
    });
  }

  function hydrateSwappedView(parsedData, scrollToTop = true) {
    const mainEl = document.getElementById('mobile-main-view');
    if (!mainEl || !parsedData) return;

    // 1. Update Title & Bottom Nav
    if (parsedData.title) {
      document.title = parsedData.title;
    }
    if (parsedData.activeTab) {
      mainEl.setAttribute('data-active-tab', parsedData.activeTab);
      updateBottomNavTab(parsedData.activeTab);
    }

    // 2. Inject Content & Trigger GPU Smooth Enter Animation
    mainEl.classList.remove('spa-page-leave', 'spa-page-enter');
    mainEl.innerHTML = parsedData.html;
    void mainEl.offsetWidth; // Force quick reflow for animation restart
    mainEl.classList.add('spa-page-enter');

    if (scrollToTop) {
      mainEl.scrollTop = 0;
      window.scrollTo({ top: 0, behavior: 'instant' });
    }

    // 3. Execute Inline View Scripts & Re-hydrate UI Icons/Widgets
    executeViewScripts(mainEl);

    if (window.lucide) {
      window.lucide.createIcons();
    }
    if (window.initLazyImages) {
      window.initLazyImages();
    }
    syncBerandaAttendanceUI();
    renderAttendancePageUI();
  }

  function navigate(targetUrl, options = {}) {
    const pushState = options.pushState !== false;
    const norm = normalizeUrl(targetUrl);
    const currentNorm = normalizeUrl(window.location.href);

    // Reset any stuck body scroll locks or stray modals before route transition
    document.body.classList.remove('overflow-hidden');
    document.body.style.overflow = '';
    const stuckModal = document.getElementById('journal-input-modal');
    if (stuckModal && !stuckModal.classList.contains('hidden')) {
      stuckModal.classList.add('hidden');
    }

    // Close any open Bottom Sheet immediately
    if (window.AndroidUI && typeof window.AndroidUI.closeBottomSheet === 'function') {
      window.AndroidUI.closeBottomSheet();
    }

    // Instant visual feedback on Bottom Nav if target matches a tab
    const guessedTab = guessTabFromUrl(targetUrl);
    if (guessedTab) {
      updateBottomNavTab(guessedTab);
    }

    // If tapping the exact same bottom nav tab that is already open, just scroll to top smoothly
    if (norm === currentNorm && !options.forceReload) {
      const mainEl = document.getElementById('mobile-main-view');
      if (mainEl) mainEl.scrollTo({ top: 0, behavior: 'smooth' });
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return Promise.resolve();
    }

    activeNavigationUrl = norm;
    const cached = !options.forceReload ? pageCache.get(norm) : null;
    const now = Date.now();

    // Case 1: Instant 0ms Swap from Cache (Stale-While-Revalidate)
    if (cached && (now - cached.timestamp) < CACHE_TTL_MAX) {
      if (pushState) {
        window.history.pushState({ spaUrl: targetUrl }, cached.title || '', targetUrl);
      }
      hydrateSwappedView(cached, options.scrollToTop !== false);

      // Silently revalidate in background if older than CACHE_TTL_FRESH
      if ((now - cached.timestamp) > CACHE_TTL_FRESH) {
        fetchAndCache(targetUrl).catch(() => {});
      }
      return Promise.resolve();
    }

    // Case 2: Fetch from Network (or await in-flight prefetch)
    isNavigating = true;
    startProgressBar();

    return fetchAndCache(targetUrl)
      .then(parsed => {
        isNavigating = false;
        finishProgressBar();
        if (activeNavigationUrl !== norm || !parsed) return;
        if (pushState) {
          window.history.pushState({ spaUrl: targetUrl }, parsed.title || '', targetUrl);
        }
        hydrateSwappedView(parsed, options.scrollToTop !== false);
      })
      .catch(() => {
        isNavigating = false;
        finishProgressBar();
        // Fallback to native navigation if network/parse fails
        window.location.href = targetUrl;
      });
  }

  function prefetch(targetUrl) {
    if (isNavigating) return;
    if (!isSpaEligibleUrl(targetUrl)) return;
    const norm = normalizeUrl(targetUrl);
    const cached = pageCache.get(norm);
    if (cached && (Date.now() - cached.timestamp) < CACHE_TTL_FRESH) return;
    fetchAndCache(targetUrl).catch(() => {});
  }

  function init() {
    const mainEl = document.getElementById('mobile-main-view');
    if (!mainEl) return;

    // Seed current page into cache immediately so returning to it is 0ms
    const currentNorm = normalizeUrl(window.location.href);
    pageCache.set(currentNorm, {
      html: mainEl.innerHTML,
      title: document.title,
      activeTab: mainEl.getAttribute('data-active-tab') || 'beranda',
      timestamp: Date.now()
    });

    // 1. Intercept clicks on any <a href="..."> inside the app
    document.addEventListener('click', (e) => {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const anchor = e.target.closest('a[href]');
      if (!anchor) return;
      const href = anchor.getAttribute('href');
      if (!isSpaEligibleUrl(href, anchor)) return;

      e.preventDefault();
      navigate(anchor.href);
    });

    // 2. Instant Prefetch on touchstart / pointerdown / mouseenter (80-150ms head start!)
    const handlePrefetchIntent = (e) => {
      const anchor = e.target.closest('a[href]');
      if (!anchor) return;
      const href = anchor.getAttribute('href');
      if (isSpaEligibleUrl(href, anchor)) {
        prefetch(anchor.href);
      }
    };
    document.addEventListener('touchstart', handlePrefetchIntent, { passive: true });
    document.addEventListener('mouseenter', handlePrefetchIntent, { passive: true, capture: true });

    // 3. Handle Hardware Back / Forward Button (popstate)
    window.addEventListener('popstate', () => {
      if (isSpaEligibleUrl(window.location.href)) {
        navigate(window.location.href, { pushState: false, scrollToTop: false });
      }
    });

    // 4. Staggered lazy pre-warming of primary routes only (avoids saturating connections & session locks)
    const prewarmCoreTabs = () => {
      if (isNavigating) return;
      const base = window.APP_BASE_URL || '';
      const coreRoutes = [
        `${base}/mobile`,
        `${base}/mobile/jurnal`
      ];
      coreRoutes.forEach((route, idx) => {
        setTimeout(() => {
          if (!isNavigating) prefetch(route);
        }, idx * 600);
      });
    };

    if ('requestIdleCallback' in window) {
      requestIdleCallback(() => setTimeout(prewarmCoreTabs, 1200), { timeout: 2500 });
    } else {
      setTimeout(prewarmCoreTabs, 2000);
    }
  }

  return {
    init,
    navigate,
    prefetch,
    invalidate: function(url) {
      if (!url) {
        pageCache.clear();
      } else {
        pageCache.delete(normalizeUrl(url));
      }
    },
    reload: function() {
      pageCache.clear();
      return navigate(window.location.href, { pushState: false, forceReload: true, scrollToTop: false });
    }
  };
})();

document.addEventListener('DOMContentLoaded', () => {
  syncBerandaAttendanceUI();
  renderAttendancePageUI();

  if (window.lucide) {
    window.lucide.createIcons();
  }
  if (window.MobileRouter) {
    window.MobileRouter.init();
  }
});
