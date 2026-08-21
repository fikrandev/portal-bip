/**
 * Portal Guru BIP - PWA WebAPK & Native Installation Manager
 * Handles WebAPK prompt, standalone mode detection, and device diagnostics.
 */

window.deferredPrompt = null;
window.isPWAInstalled = false;

// ── Check Platform & Standalone Mode ───────────────────
window.isIOSDevice = function() {
  const userAgent = window.navigator.userAgent.toLowerCase();
  return /iphone|ipad|ipod/.test(userAgent);
};

window.isAndroidDevice = function() {
  const userAgent = window.navigator.userAgent.toLowerCase();
  return /android/.test(userAgent);
};

window.isInStandaloneMode = function() {
  return ('standalone' in window.navigator && window.navigator.standalone) || 
         window.matchMedia('(display-mode: standalone)').matches ||
         window.matchMedia('(display-mode: fullscreen)').matches ||
         window.matchMedia('(display-mode: minimal-ui)').matches ||
         document.referrer.includes('android-app://');
};

// ── Register Service Worker ────────────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    const baseUrl = window.APP_BASE_URL || '';
    const swPath = baseUrl ? `${baseUrl}/sw.js` : '/sw.js';
    const scopePath = baseUrl ? `${baseUrl}/` : '/';

    navigator.serviceWorker.register(swPath, { scope: scopePath })
      .then((registration) => {
        console.log('[PWA] ServiceWorker registered with scope:', registration.scope);
      })
      .catch((err) => {
        console.warn('[PWA] ServiceWorker registration fallback:', err);
        navigator.serviceWorker.register((window.APP_BASE_URL || '') + '/public/sw.js')
          .catch((e) => console.log('[PWA] SW fallback error:', e));
      });
  });
}

// ── Listen to BeforeInstallPrompt (WebAPK Native Trigger)
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  window.deferredPrompt = e;
  console.log('[PWA] beforeinstallprompt event captured - ready for WebAPK install');
});

// ── Listen to AppInstalled Event ───────────────────────
window.addEventListener('appinstalled', () => {
  console.log('[PWA] App successfully installed as WebAPK!');
  window.deferredPrompt = null;
  window.isPWAInstalled = true;

  if (window.AndroidUI && window.AndroidUI.toast) {
    window.AndroidUI.toast('Aplikasi Portal Guru berhasil dipasang di perangkat Anda!', 'success');
  }
});

// ── Trigger Native Installation (WebAPK) ──────────────
window.triggerPWAInstall = function() {
  if (window.isInStandaloneMode()) {
    if (window.AndroidUI && window.AndroidUI.toast) {
      window.AndroidUI.toast('Aplikasi sudah terpasang dan berjalan dalam mode Standalone', 'info');
    }
    return;
  }

  if (window.deferredPrompt) {
    // Native Chrome / Chromium WebAPK Install Dialog
    window.deferredPrompt.prompt();
    window.deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('[PWA] User accepted WebAPK install prompt');
        if (window.AndroidUI && window.AndroidUI.toast) {
          window.AndroidUI.toast('Memasang aplikasi ke perangkat...', 'info');
        }
      } else {
        console.log('[PWA] User dismissed install prompt');
      }
      window.deferredPrompt = null;
    });
  } else if (window.isIOSDevice()) {
    window.showIOSInstallModal();
  } else {
    // Show Android Chrome instruction modal
    window.showAndroidInstallModal();
  }
};

// ── iOS Install Guide Sheet ────────────────────────────
window.showIOSInstallModal = function() {
  if (window.AndroidUI && window.AndroidUI.bottomSheet) {
    AndroidUI.bottomSheet({
      title: 'Pasang di iPhone / iPad',
      subtitle: 'Panduan menambahkan ke Layar Utama Safari',
      icon: '🍎',
      iconBg: 'bg-slate-100 text-slate-800',
      content: `
        <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs text-slate-700 leading-relaxed">
          <div class="flex items-start gap-2.5">
            <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shrink-0 text-xs mt-0.5">1</span>
            <p>Buka portal di browser <strong>Safari</strong>, lalu tekan tombol <strong>Bagikan / Share</strong> <span class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-mono font-bold">⎋ / ⤤</span> di bar bawah.</p>
          </div>
          <div class="flex items-start gap-2.5">
            <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shrink-0 text-xs mt-0.5">2</span>
            <p>Gulir menu ke bawah dan tekan <strong>"Tambahkan ke Layar Utama" (Add to Home Screen)</strong> ➕.</p>
          </div>
          <div class="flex items-start gap-2.5">
            <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shrink-0 text-xs mt-0.5">3</span>
            <p>Tekan tombol <strong>"Tambah" (Add)</strong> di pojok kanan atas.</p>
          </div>
        </div>
      `,
      actions: [
        {
          text: 'Saya Mengerti',
          className: 'w-full py-3 bg-blue-600 text-white font-bold text-xs rounded-2xl shadow-md'
        }
      ]
    });
  }
};

// ── Android Install Guide Sheet ────────────────────────
window.showAndroidInstallModal = function() {
  if (window.AndroidUI && window.AndroidUI.bottomSheet) {
    AndroidUI.bottomSheet({
      title: 'Pasang di Smartphone Android',
      subtitle: 'Instalasi otomatis WebAPK melalui Google Chrome',
      icon: '🤖',
      iconBg: 'bg-emerald-100 text-emerald-700',
      content: `
        <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs text-slate-700 leading-relaxed">
          <div class="flex items-start gap-2.5">
            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0 text-xs mt-0.5">1</span>
            <p>Tekan tombol titik tiga <strong class="font-mono text-sm">⋮</strong> di pojok kanan atas browser Google Chrome.</p>
          </div>
          <div class="flex items-start gap-2.5">
            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0 text-xs mt-0.5">2</span>
            <p>Pilih menu <strong>"Install Aplikasi"</strong> (atau <strong>"Tambahkan ke Layar Utama"</strong>).</p>
          </div>
          <div class="flex items-start gap-2.5">
            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0 text-xs mt-0.5">3</span>
            <p>Tekan <strong>"Install"</strong>. Android akan mengompilasi dan memasang aplikasi dengan logo bersih di daftar aplikasi HP Anda.</p>
          </div>
        </div>
      `,
      actions: [
        {
          text: 'Tutup',
          className: 'w-full py-3 bg-slate-800 text-white font-bold text-xs rounded-2xl shadow-md'
        }
      ]
    });
  }
};

// ── Device Diagnostics & Cache Helpers ─────────────────
window.checkPWADiagnostics = async function() {
  const results = {
    isStandalone: window.isInStandaloneMode(),
    hasServiceWorker: 'serviceWorker' in navigator && !!navigator.serviceWorker.controller,
    cacheSize: '±3.8 MB'
  };

  if (navigator.storage && navigator.storage.estimate) {
    try {
      const estimate = await navigator.storage.estimate();
      const usedMB = (estimate.usage / (1024 * 1024)).toFixed(1);
      results.cacheSize = `${usedMB} MB`;
    } catch (e) {}
  }

  return results;
};

// ── Online / Offline Monitors ──────────────────────────
window.addEventListener('online', () => {
  const badge = document.getElementById('network-status-badge');
  if (badge) {
    badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Online';
    badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
  }
  if (window.AndroidUI && window.AndroidUI.toast) {
    window.AndroidUI.toast('Koneksi internet terhubung kembali', 'success');
  }
});

window.addEventListener('offline', () => {
  const badge = document.getElementById('network-status-badge');
  if (badge) {
    badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500"></span> Mode Offline';
    badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200';
  }
  if (window.AndroidUI && window.AndroidUI.toast) {
    window.AndroidUI.toast('Anda sedang offline. Sistem tetap dapat digunakan dengan cache.', 'warning');
  }
});


