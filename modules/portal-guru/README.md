# 📱 Portal Guru (Mobile & PWA Module)

Modul **Portal Guru** dirancang khusus untuk pengalaman mobile guru dengan performa tinggi, animasi native Android Material 3, dukungan offline PWA, dan arsitektur modular yang **sangat scalable** (mudah dikembangkan lebih lanjut).

---

## 🏛️ Arsitektur & Struktur Direktori

```text
modules/portal-guru/
├── controllers/
│   └── PortalGuruController.php    # Router & HTTP endpoint controller
├── models/
│   └── PortalGuruModel.php         # Business logic & query database (dengan safe fallback)
├── views/
│   ├── layout.php                  # Shared Mobile App Shell (Header, Bottom Nav, PWA Meta, AndroidUI Container)
│   ├── beranda.php                 # Dashboard Guru, Mutaba'ah Ibadah Harian, Jadwal Mengajar, Akses Cepat
│   ├── absen.php                   # Presensi GPS Geolokasi Radar & Jam Pulang Lock (15:00)
│   ├── jurnal.php                  # Jurnal Mengajar Harian & Riwayat
│   ├── kelas.php                   # Kelas Diampu (PIC / Mendampingi & Presensi Masuk Cepat)
│   ├── absensi_kelas.php           # Presensi Siswa H/S/I/A & Catatan
│   ├── murid.php                   # Direktori Data Siswa per Kelas
│   ├── quran.php                   # Al-Qur'an Digital 114 Surah, Bookmark Timestamp, 2-Min Tilawah Timer
│   ├── dzikir.php                  # Dzikir Pagi & Petang Al-Ma'tsurat Sugro & Kubro + Tasbih Digital
│   ├── materi.php                  # Materi & Modul Pembelajaran
│   ├── buat_tugas.php              # Pembuatan & Manajemen Tugas
│   ├── pesan_kelas.php             # Pengumuman & Chat Kelas
│   ├── bank_soal.php               # Bank Soal & Paket Kuis
│   ├── notifikasi.php              # Pusat Notifikasi Guru
│   └── profil.php                  # Profil Guru, Pengaturan PWA & Cache
└── README.md                       # Dokumentasi Arsitektur Modul
```

---

## 🧩 Modul Frontend JavaScript

Semua pustaka pendukung mobile berada di `public/js/mobile/` dan dirancang modular:

1. **`android-ui.js` (`AndroidUI`)**:
   - Bottom Sheet Modal dengan drag-to-dismiss & gesture handling.
   - Deteksi keyboard otomatis (`visualViewport`) agar form textarea dan tombol selalu berada di atas keyboard.
   - Animasi sukses centang halus (SVG draw), animasi error/gagal goyang halus (shake), dan peringatan (*warning kaget bounce*).
   - Indikator loading tombol (`setButtonLoading`) & loading tengah data (`showCenterLoading`).
   - Material 3 Toast & Snackbar.

2. **`mobile-api.js` (`MobileAPI`)**:
   - `MobileAPI.storage`: Safe LocalStorage layer ber-namespace (`portal_bip_`).
   - `MobileAPI.events`: Event Bus / Pub-Sub (`on`, `emit`, `off`) untuk reaktivitas antar komponen (misal Tilawah / Dzikir selesai langsung memperbarui Beranda tanpa reload).
   - `MobileAPI.ibadah`: State manager untuk Mutaba'ah Sholat 5 waktu, Tilawah, Dzikir, dan Tadabbur.
   - `MobileAPI.attendance`: Perhitungan jarak radius sekolah (Haversine Formula) dan aturan jam pulang (15:00).
   - `MobileAPI.quran`: Pengelola bookmark terverifikasi waktu & lompat ke ayat.

3. **`lazy-load.js`**:
   - Pemuat gambar & komponen berbasis `IntersectionObserver` untuk performa 60 FPS pada perangkat *low-end*.

4. **`pwa.js` & `sw.js`**:
   - PWA Service Worker caching (offline capability) dan installer banner untuk Android & iOS.

---

## 🚀 Cara Menambah Halaman / Fitur Baru (Hanya 3 Langkah)

Jika ingin menambah fitur baru (contoh: menu **Konsultasi BK** atau **Input Nilai Raport**):

### Langkah 1: Tambahkan Route di `index.php`
```php
$router->get('/mobile/raport', [PortalGuruController::class, 'raport']);
```

### Langkah 2: Tambahkan Method di `PortalGuruController.php`
```php
public static function raport(): void
{
    self::render('raport', [
        'pageTitle' => 'Input Nilai Raport',
        'activeTab' => 'raport'
    ]);
}
```

### Langkah 3: Buat File View `modules/portal-guru/views/raport.php`
```php
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="font-black text-slate-900 text-base">Input Nilai Raport</h2>
    </div>
</div>

<div class="p-4 space-y-3">
    <!-- Konten form & card -->
</div>
```

---

## 🔗 Integrasi Database Nyata (MySQL)

Semua query database diisolasi di `modules/portal-guru/models/PortalGuruModel.php`. Untuk menghubungkan tabel live:
- Cukup isi method pada `PortalGuruModel` menggunakan `Database::getInstance()`.
- View dan Controller tidak perlu diubah, sehingga *separation of concerns* (SoC) tetap bersih.
