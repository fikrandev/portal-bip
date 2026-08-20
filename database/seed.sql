-- =====================================================
-- Portal BIP - Seed Data
-- Default roles, admin user, permissions, sample modules
-- =====================================================

SET NAMES utf8mb4;

-- =====================================================
-- ROLES
-- =====================================================
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`) VALUES
(1, 'Super Admin', 'super_admin', 'Akses penuh ke semua fitur dan pengaturan sistem', 1),
(2, 'Admin', 'admin', 'Mengelola modul-modul tertentu yang ditugaskan', 1),
(3, 'Operator', 'operator', 'Melakukan input dan pengelolaan data operasional', 1),
(4, 'Viewer', 'viewer', 'Hanya dapat melihat data tanpa melakukan perubahan', 1);

-- =====================================================
-- MODULES
-- =====================================================
INSERT INTO `modules` (`id`, `name`, `slug`, `description`, `module_group`, `icon_svg`, `color`, `route`, `sort_order`, `is_active`) VALUES
(1, 'Dashboard', 'dashboard', 'Halaman utama portal dan ringkasan informasi', NULL,
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>',
'#0EA5E9', '/dashboard', 0, 1),

(2, 'Kelola Siswa', 'kelola-siswa', 'Manajemen data siswa, pendaftaran, dan profil siswa', 'Kesiswaan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>',
'#06B6D4', '/kelola-siswa', 1, 1),

(3, 'Kelola Pegawai', 'kelola-pegawai', 'Manajemen data pegawai, jadwal mengajar, dan evaluasi', 'Kepegawaian',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>',
'#0284C7', '/kelola-pegawai', 2, 1),

(4, 'Kelola Kelas', 'kelola-kelas', 'Manajemen kelas, wali kelas, dan pembagian rombel', 'Kesiswaan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>',
'#38BDF8', '/kelola-kelas', 3, 1),

(5, 'Kelola Pengguna', 'kelola-pengguna', 'Manajemen akun pengguna, peran, dan hak akses', 'Pengaturan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>',
'#0369A1', '/users', 104, 1),

(6, 'Kelola Peran', 'kelola-peran', 'Pengaturan peran dan hak akses pengguna (RBAC)', 'Pengaturan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>',
'#7DD3FC', '/roles', 105, 1),

(7, 'Manajemen Modul', 'manajemen-modul', 'Tambah, edit, dan kelola modul-modul portal', 'Pengaturan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/></svg>',
'#0C4A6E', '/modules-manager', 106, 1),

(9, 'Kelola RPP', 'kelola-rpp', 'Manajemen Rencana Pelaksanaan Pembelajaran', 'Administrasi Guru',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
'#10B981', '/kelola-rpp', 8, 1),

(10, 'Kelola Nilai', 'kelola-nilai', 'Manajemen nilai akademik siswa', 'Administrasi Guru',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" /></svg>',
'#F59E0B', '/kelola-nilai', 9, 1),

(11, 'Kelola Raport', 'kelola-raport', 'Manajemen dan pencetakan raport siswa', 'Administrasi Guru',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>',
'#8B5CF6', '/kelola-raport', 10, 1),

(12, 'Kelola Absen Siswa', 'kelola-absen-siswa', 'Manajemen presensi dan kehadiran siswa', 'Administrasi Guru',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
'#10B981', '/kelola-absen-siswa', 11, 1),

(13, 'Kelola Absen Pegawai', 'kelola-absen-pegawai', 'Manajemen presensi dan kehadiran pegawai', 'Kepegawaian',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
'#0284C7', '/kelola-absen-pegawai', 12, 1),

(14, 'Kelola Qur\'an Siswa', 'kelola-quran-siswa', 'Manajemen hafalan dan bacaan Al-Qur\'an siswa', 'Administrasi Guru',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
'#10B981', '/kelola-quran-siswa', 13, 1),

(15, 'Kelola Ujian', 'kelola-ujian', 'Manajemen jadwal, soal, dan pelaksanaan ujian', 'Administrasi Guru',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" /></svg>',
'#10B981', '/kelola-ujian', 14, 1),

(16, 'Kelola Qur\'an Pegawai', 'kelola-quran-pegawai', 'Manajemen hafalan dan tahsin Al-Qur\'an pegawai', 'Kepegawaian',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
'#0284C7', '/kelola-quran-pegawai', 15, 1),

(17, 'Kelola Cuti', 'kelola-cuti', 'Manajemen pengajuan dan persetujuan cuti pegawai', 'Kepegawaian',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" /></svg>',
'#0284C7', '/kelola-cuti', 16, 1),

(18, 'Kelola KPI', 'kelola-kpi', 'Manajemen Key Performance Indicator pegawai', 'Kepegawaian',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>',
'#0284C7', '/kelola-kpi', 17, 1),

(19, 'Kelola Ibadah Guru', 'kelola-ibadah-guru', 'Manajemen ibadah dan amaliah guru', 'Kepegawaian',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
'#0284C7', '/kelola-ibadah-guru', 18, 1),

(20, 'Kelola SPMB', 'kelola-spmb', 'Manajemen Seleksi Penerimaan Siswa Baru', 'Kesiswaan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
'#38BDF8', '/kelola-spmb', 19, 1),

(21, 'Kelola Pelanggaran Siswa', 'kelola-pelanggaran-siswa', 'Manajemen catatan dan poin pelanggaran siswa', 'Kesiswaan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" /></svg>',
'#38BDF8', '/kelola-pelanggaran-siswa', 20, 1),

(22, 'Kelola Izin Guru', 'kelola-izin-guru', 'Manajemen pengajuan dan persetujuan izin guru', 'Kepegawaian',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>',
'#0284C7', '/kelola-izin-guru', 21, 1),

(23, 'Kelola Buku Angkatan Siswa', 'kelola-buku-angkatan-siswa', 'Manajemen data dan profil buku angkatan siswa', 'Kesiswaan',
'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
'#38BDF8', '/kelola-buku-angkatan-siswa', 22, 1);

-- =====================================================
-- PERMISSIONS
-- =====================================================
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `module_id`) VALUES
-- Dashboard
(1, 'Lihat Dashboard', 'dashboard.view', 'Dapat mengakses halaman dashboard', 1),

-- Kelola Siswa
(2, 'Lihat Siswa', 'siswa.view', 'Dapat melihat daftar dan detail siswa', 2),
(3, 'Tambah Siswa', 'siswa.create', 'Dapat menambahkan data siswa baru', 2),
(4, 'Edit Siswa', 'siswa.update', 'Dapat mengubah data siswa', 2),
(5, 'Hapus Siswa', 'siswa.delete', 'Dapat menghapus data siswa', 2),

-- Kelola Pegawai
(6, 'Lihat Pegawai', 'pegawai.view', 'Dapat melihat daftar dan detail pegawai', 3),
(7, 'Tambah Pegawai', 'pegawai.create', 'Dapat menambahkan data pegawai baru', 3),
(8, 'Edit Pegawai', 'pegawai.update', 'Dapat mengubah data pegawai', 3),
(9, 'Hapus Pegawai', 'pegawai.delete', 'Dapat menghapus data pegawai', 3),

-- Kelola Kelas
(10, 'Lihat Kelas', 'kelas.view', 'Dapat melihat daftar kelas', 4),
(11, 'Tambah Kelas', 'kelas.create', 'Dapat membuat kelas baru', 4),
(12, 'Edit Kelas', 'kelas.update', 'Dapat mengubah data kelas', 4),
(13, 'Hapus Kelas', 'kelas.delete', 'Dapat menghapus kelas', 4),

-- Kelola Pengguna
(14, 'Lihat Pengguna', 'users.view', 'Dapat melihat daftar pengguna', 5),
(15, 'Tambah Pengguna', 'users.create', 'Dapat menambahkan pengguna baru', 5),
(16, 'Edit Pengguna', 'users.update', 'Dapat mengubah data pengguna', 5),
(17, 'Hapus Pengguna', 'users.delete', 'Dapat menghapus pengguna', 5),

-- Kelola Peran
(18, 'Lihat Peran', 'roles.view', 'Dapat melihat daftar peran', 6),
(19, 'Tambah Peran', 'roles.create', 'Dapat membuat peran baru', 6),
(20, 'Edit Peran', 'roles.update', 'Dapat mengubah peran dan hak akses', 6),
(21, 'Hapus Peran', 'roles.delete', 'Dapat menghapus peran', 6),

-- Manajemen Modul
(22, 'Lihat Modul', 'modules.view', 'Dapat melihat daftar modul', 7),
(23, 'Tambah Modul', 'modules.create', 'Dapat menambahkan modul baru', 7),
(24, 'Edit Modul', 'modules.update', 'Dapat mengubah konfigurasi modul', 7),
(25, 'Hapus Modul', 'modules.delete', 'Dapat menghapus modul', 7),

-- Kelola RPP
(26, 'Lihat RPP', 'rpp.view', 'Dapat melihat daftar RPP', 9),
(27, 'Tambah RPP', 'rpp.create', 'Dapat menambahkan RPP baru', 9),
(28, 'Edit RPP', 'rpp.update', 'Dapat mengubah RPP', 9),
(29, 'Hapus RPP', 'rpp.delete', 'Dapat menghapus RPP', 9),

-- Kelola Nilai
(30, 'Lihat Nilai', 'nilai.view', 'Dapat melihat daftar nilai', 10),
(31, 'Tambah Nilai', 'nilai.create', 'Dapat menambahkan nilai baru', 10),
(32, 'Edit Nilai', 'nilai.update', 'Dapat mengubah nilai', 10),
(33, 'Hapus Nilai', 'nilai.delete', 'Dapat menghapus nilai', 10),

-- Kelola Raport
(34, 'Lihat Raport', 'raport.view', 'Dapat melihat daftar raport', 11),
(35, 'Tambah Raport', 'raport.create', 'Dapat menambahkan raport baru', 11),
(36, 'Edit Raport', 'raport.update', 'Dapat mengubah raport', 11),
(37, 'Hapus Raport', 'raport.delete', 'Dapat menghapus raport', 11);

-- =====================================================
-- ROLE_PERMISSIONS
-- Super Admin gets ALL permissions
-- =====================================================
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
-- Super Admin: semua permission (1-37)
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),
(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),
(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),
(1,31),(1,32),(1,33),(1,34),(1,35),(1,36),(1,37),

-- Admin: view + create + update (no delete, no role/module management)
(2,1),(2,2),(2,3),(2,4),(2,6),(2,7),(2,8),(2,10),(2,11),(2,12),(2,14),(2,15),(2,16),(2,26),(2,27),(2,28),(2,30),(2,31),(2,32),(2,34),(2,35),(2,36),

-- Operator: view + create + update (data modules only)
(3,1),(3,2),(3,3),(3,4),(3,6),(3,7),(3,8),(3,10),(3,11),(3,12),(3,26),(3,27),(3,28),(3,30),(3,31),(3,32),(3,34),(3,35),(3,36),

-- Viewer: view only
(4,1),(4,2),(4,6),(4,10),(4,26),(4,30),(4,34);

-- =====================================================
-- MODULE_PERMISSIONS (which permission grants module access)
-- =====================================================
INSERT INTO `module_permissions` (`module_id`, `permission_id`) VALUES
(1, 1),   -- Dashboard -> dashboard.view
(2, 2),   -- Kelola Siswa -> siswa.view
(3, 6),   -- Kelola Pegawai -> pegawai.view
(4, 10),  -- Kelola Kelas -> kelas.view
(5, 14),  -- Kelola Pengguna -> users.view
(6, 18),  -- Kelola Peran -> roles.view
(7, 22),  -- Manajemen Modul -> modules.view
(9, 26),  -- Kelola RPP -> rpp.view
(10, 30), -- Kelola Nilai -> nilai.view
(11, 34); -- Kelola Raport -> raport.view

-- =====================================================
-- DEFAULT ADMIN USER
-- Password: admin123 (bcrypt hash)
-- =====================================================
INSERT INTO `users` (`id`, `uuid`, `full_name`, `email`, `username`, `password`, `is_active`, `created_at`) VALUES
(1, UUID(), 'Administrator', 'admin@portal-bip.com', 'admin', '$2y$10$LOzJLiXX0kc7I59ASF0C5u.l7S5YXfXJzvcl8JUPAkshTDpFNVEiC', 1, NOW());

-- Assign Super Admin role to admin user
INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
(1, 1, NOW());

-- =====================================================
-- DEFAULT SETTINGS
-- =====================================================
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
('app_name', 'Portal BIP', 'string', 'general'),
('app_description', 'Portal Manajemen Informasi Terpadu', 'string', 'general'),
('app_version', '1.0.0', 'string', 'general'),
('app_logo', '/public/img/logo.svg', 'string', 'general'),
('max_login_attempts', '5', 'integer', 'security'),
('lockout_duration', '15', 'integer', 'security'),
('session_lifetime', '120', 'integer', 'security'),
('pagination_limit', '15', 'integer', 'display'),
('date_format', 'd/m/Y', 'string', 'display'),
('timezone', 'Asia/Makassar', 'string', 'general');
