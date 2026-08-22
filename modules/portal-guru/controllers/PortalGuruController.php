<?php
/**
 * Portal Guru - Mobile & PWA Controller
 * Handles teacher mobile dashboard, geolocation attendance, teaching journal, and classroom management.
 */

require_once __DIR__ . '/../models/PortalGuruModel.php';

class PortalGuruController
{
    /**
     * Render a mobile view with mobile app shell layout
     */
    private static function render(string $view, array $data = []): void
    {
        extract($data);
        $currentRoute = $data['activeTab'] ?? 'beranda';
        
        ob_start();
        include MODULES_PATH . '/portal-guru/views/' . $view . '.php';
        $content = ob_get_clean();
        
        include MODULES_PATH . '/portal-guru/views/layout.php';
    }

    /**
     * Mobile Home (Beranda)
     */
    public static function beranda(): void
    {
        self::render('beranda', [
            'pageTitle' => 'Beranda Guru',
            'activeTab' => 'beranda',
            'teacherName' => 'Bu Rina',
            'teacherRole' => 'Guru Matematika',
            'totalClasses' => 6,
            'activeTasks' => 12,
            'pendingGrades' => 36,
            'classReports' => 4,
            'todaySchedule' => [
                [
                    'time' => '08.00 - 08.45',
                    'subject' => 'Matematika',
                    'class' => 'Kelas 7A',
                    'status' => 'Berlangsung',
                    'room' => 'Ruang 102'
                ],
                [
                    'time' => '09.00 - 09.45',
                    'subject' => 'Matematika',
                    'class' => 'Kelas 7B',
                    'status' => 'Mendatang',
                    'room' => 'Ruang 103'
                ],
                [
                    'time' => '10.15 - 11.00',
                    'subject' => 'Matematika',
                    'class' => 'Kelas 8A',
                    'status' => 'Mendatang',
                    'room' => 'Ruang 201'
                ]
            ]
        ]);
    }

    /**
     * Absen Kehadiran & Geolokasi
     */
    public static function absen(): void
    {
        self::render('absen', [
            'pageTitle' => 'Presensi Kehadiran & Geolokasi',
            'activeTab' => 'absen',
            'schoolName' => 'Kampus BIP',
            'schoolRadius' => 150,
            'schoolCoords' => ['lat' => -5.147665, 'lng' => 119.432732],
            'recentLogs' => [
                ['date' => 'Hari ini', 'checkin' => '07:12 WITA', 'checkout' => '-', 'status' => 'Hadir Tepat Waktu', 'badge' => 'emerald'],
                ['date' => 'Kemarin', 'checkin' => '07:05 WITA', 'checkout' => '15:35 WITA', 'status' => 'Hadir Tepat Waktu', 'badge' => 'emerald'],
                ['date' => '2 Hari Lalu', 'checkin' => '07:18 WITA', 'checkout' => '15:30 WITA', 'status' => 'Hadir Tepat Waktu', 'badge' => 'emerald'],
                ['date' => '3 Hari Lalu', 'checkin' => '07:45 WITA', 'checkout' => '15:30 WITA', 'status' => 'Terlambat 15m', 'badge' => 'amber'],
            ]
        ]);
    }

    /**
     * Isi Jurnal Pembelajaran
     */
    public static function jurnal(): void
    {
        self::render('jurnal', [
            'pageTitle' => 'Jurnal Mengajar Harian',
            'activeTab' => 'jurnal',
            'classes' => ['Kelas 7A', 'Kelas 7B', 'Kelas 8A', 'Kelas 8B', 'Kelas 9A', 'Kelas 9B'],
            'subjects' => ['Matematika', 'Matematika Peminatan'],
            'recentJournals' => [
                [
                    'id' => 'JRN-104',
                    'date' => date('d M Y'),
                    'class' => 'Kelas 7A',
                    'subject' => 'Matematika',
                    'hours' => 'Jam ke 1-2 (08.00 - 09.30)',
                    'topic' => 'Teorema Pythagoras & Segitiga Siku-Siku',
                    'summary' => 'Pembahasan rumus dasar dan latihan soal kelompok.',
                    'attendance' => '30 Hadir, 1 Sakit, 1 Izin',
                    'status' => 'Tervalidasi'
                ],
                [
                    'id' => 'JRN-103',
                    'date' => date('d M Y', strtotime('-1 day')),
                    'class' => 'Kelas 7B',
                    'subject' => 'Matematika',
                    'hours' => 'Jam ke 3-4 (09.45 - 11.15)',
                    'topic' => 'Operasi Aljabar Satu Variabel',
                    'summary' => 'Penjelasan konsep perkalian aljabar dan kuis individu.',
                    'attendance' => '32 Hadir, 0 Absen',
                    'status' => 'Tervalidasi'
                ]
            ]
        ]);
    }

    /**
     * Kelas Diampu
     */
    public static function kelas(): void
    {
        self::render('kelas', [
            'pageTitle' => 'Kelas Diampu',
            'activeTab' => 'kelas',
            'classList' => [
                ['name' => 'Kelas 7A', 'level' => '7', 'totalStudents' => 32, 'homeroom' => 'Bu Rina S.Pd', 'schedule' => 'Senin, 08.00 - 09.30', 'role' => 'PIC', 'jp' => 2, 'color' => 'blue', 'roleBadge' => 'emerald'],
                ['name' => 'Kelas 7B', 'level' => '7', 'totalStudents' => 30, 'homeroom' => 'Bpk. Ahmad Fauzi', 'schedule' => 'Senin, 09.45 - 11.15', 'role' => 'PIC', 'jp' => 2, 'color' => 'indigo', 'roleBadge' => 'emerald'],
                ['name' => 'Kelas 8A', 'level' => '8', 'totalStudents' => 32, 'homeroom' => 'Bu Siti Rahma', 'schedule' => 'Selasa, 08.00 - 09.30', 'role' => 'Mendampingi', 'jp' => 2, 'color' => 'emerald', 'roleBadge' => 'blue'],
                ['name' => 'Kelas 8B', 'level' => '8', 'totalStudents' => 31, 'homeroom' => 'Bpk. Dani Setiawan', 'schedule' => 'Rabu, 10.00 - 11.30', 'role' => 'PIC', 'jp' => 2, 'color' => 'teal', 'roleBadge' => 'emerald'],
                ['name' => 'Kelas 9A', 'level' => '9', 'totalStudents' => 28, 'homeroom' => 'Bu Sri Wahyuni', 'schedule' => 'Kamis, 08.00 - 09.30', 'role' => 'Mendampingi', 'jp' => 2, 'color' => 'purple', 'roleBadge' => 'blue'],
                ['name' => 'Kelas 9B', 'level' => '9', 'totalStudents' => 29, 'homeroom' => 'Bpk. Hendra Saputra', 'schedule' => 'Jumat, 07.30 - 09.00', 'role' => 'PIC', 'jp' => 2, 'color' => 'sky', 'roleBadge' => 'emerald']
            ]
        ]);
    }

    /**
     * Absensi Siswa per Kelas
     */
    public static function absensiKelas(): void
    {
        self::render('absensi_kelas', [
            'pageTitle' => 'Absensi Siswa Kelas',
            'activeTab' => 'kelas',
            'selectedClass' => 'Kelas 7A',
            'students' => [
                ['id' => 1, 'nisn' => '0089218201', 'name' => 'Achmad Zaky Pratama', 'gender' => 'L', 'status' => 'H'],
                ['id' => 2, 'nisn' => '0089218202', 'name' => 'Aisyah Putri Azzahra', 'gender' => 'P', 'status' => 'H'],
                ['id' => 3, 'nisn' => '0089218203', 'name' => 'Alif Ramadhan', 'gender' => 'L', 'status' => 'H'],
                ['id' => 4, 'nisn' => '0089218204', 'name' => 'Anisa Nur Fadilah', 'gender' => 'P', 'status' => 'S'],
                ['id' => 5, 'nisn' => '0089218205', 'name' => 'Bagus Dwi Wicaksono', 'gender' => 'L', 'status' => 'H'],
                ['id' => 6, 'nisn' => '0089218206', 'name' => 'Cantika Dewi Lestari', 'gender' => 'P', 'status' => 'I'],
                ['id' => 7, 'nisn' => '0089218207', 'name' => 'Dimas Arya Nugraha', 'gender' => 'L', 'status' => 'H'],
                ['id' => 8, 'nisn' => '0089218208', 'name' => 'Fadli Kurniawan', 'gender' => 'L', 'status' => 'H'],
                ['id' => 9, 'nisn' => '0089218209', 'name' => 'Gita Permata Sari', 'gender' => 'P', 'status' => 'H'],
                ['id' => 10, 'nisn' => '0089218210', 'name' => 'Hafizh Pratama', 'gender' => 'L', 'status' => 'H']
            ]
        ]);
    }

    /**
     * Murid (Daftar Siswa)
     */
    public static function murid(): void
    {
        self::render('murid', [
            'pageTitle' => 'Daftar Siswa & Profil',
            'activeTab' => 'murid',
            'classes' => ['Semua', 'Kelas 7A', 'Kelas 7B', 'Kelas 8A', 'Kelas 8B', 'Kelas 9A', 'Kelas 9B'],
            'studentList' => [
                ['name' => 'Achmad Zaky Pratama', 'nisn' => '0089218201', 'class' => 'Kelas 7A', 'gender' => 'L', 'presence' => '98%', 'phone' => '081234567890', 'points' => 100],
                ['name' => 'Aisyah Putri Azzahra', 'nisn' => '0089218202', 'class' => 'Kelas 7A', 'gender' => 'P', 'presence' => '100%', 'phone' => '081234567891', 'points' => 100],
                ['name' => 'Alif Ramadhan', 'nisn' => '0089218203', 'class' => 'Kelas 7A', 'gender' => 'L', 'presence' => '95%', 'phone' => '081234567892', 'points' => 95],
                ['name' => 'Bintang Pratama', 'nisn' => '0089218211', 'class' => 'Kelas 7B', 'gender' => 'L', 'presence' => '96%', 'phone' => '081234567893', 'points' => 98],
                ['name' => 'Citra Kirana', 'nisn' => '0089218212', 'class' => 'Kelas 7B', 'gender' => 'P', 'presence' => '100%', 'phone' => '081234567894', 'points' => 100],
                ['name' => 'David Maulana', 'nisn' => '0089218221', 'class' => 'Kelas 8A', 'gender' => 'L', 'presence' => '94%', 'phone' => '081234567895', 'points' => 90],
                ['name' => 'Elsa Safira', 'nisn' => '0089218222', 'class' => 'Kelas 8A', 'gender' => 'P', 'presence' => '98%', 'phone' => '081234567896', 'points' => 100]
            ]
        ]);
    }

    /**
     * Profil Guru & PWA Settings
     */
    public static function profil(): void
    {
        self::render('profil', [
            'pageTitle' => 'Profil Guru & Pengaturan PWA',
            'activeTab' => 'profil',
            'profile' => [
                'name' => 'Bu Rina S.Pd',
                'nip' => '19880412 201201 2 003',
                'role' => 'Guru Matematika',
                'status' => 'PNS / Sertifikasi Pendidik',
                'email' => 'rina.matematika@sekolah.bip.id',
                'phone' => '0812-3456-7890',
                'teachingHours' => '24 Jam / Minggu',
                'academicYear' => '2025/2026 Genap'
            ]
        ]);
    }

    /**
     * Notifikasi
     */
    public static function notifikasi(): void
    {
        self::render('notifikasi', [
            'pageTitle' => 'Pusat Notifikasi',
            'activeTab' => 'notifikasi',
            'notifications' => [
                [
                    'id' => 1,
                    'title' => 'Pengingat Presensi Pagi',
                    'message' => 'Jangan lupa melakukan check-in geolokasi sebelum pukul 07.30 WITA.',
                    'time' => '10 menit yang lalu',
                    'type' => 'absen',
                    'unread' => true
                ],
                [
                    'id' => 2,
                    'title' => 'Jadwal Mengajar Berikutnya',
                    'message' => 'Kelas 7A Matematika di Ruang 102 akan dimulai pada 08.00 WITA.',
                    'time' => '25 menit yang lalu',
                    'type' => 'jadwal',
                    'unread' => true
                ],
                [
                    'id' => 3,
                    'title' => 'Pengumuman Rapat Dewan Guru',
                    'message' => 'Rapat koordinasi semester genap akan diadakan Jumat, 14.00 di Aula.',
                    'time' => 'Kemarin',
                    'type' => 'pengumuman',
                    'unread' => false
                ]
            ]
        ]);
    }

    /**
     * Materi Pembelajaran
     */
    public static function materi(): void
    {
        self::render('materi', [
            'pageTitle' => 'Materi Pembelajaran',
            'activeTab' => 'materi',
            'materials' => [
                ['title' => 'Modul Ajar Teorema Pythagoras', 'class' => 'Kelas 7', 'fileType' => 'PDF', 'size' => '2.4 MB', 'downloads' => 32],
                ['title' => 'Slide Presentasi Aljabar Linier', 'class' => 'Kelas 7', 'fileType' => 'PPTX', 'size' => '5.1 MB', 'downloads' => 30],
                ['title' => 'Lembar Kerja Siswa (LKS) Geometri', 'class' => 'Kelas 8', 'fileType' => 'DOCX', 'size' => '1.2 MB', 'downloads' => 28]
            ]
        ]);
    }

    /**
     * Buat Tugas
     */
    public static function buatTugas(): void
    {
        self::render('buat_tugas', [
            'pageTitle' => 'Buat Tugas Siswa',
            'activeTab' => 'tugas',
            'classes' => ['Kelas 7A', 'Kelas 7B', 'Kelas 8A', 'Kelas 8B', 'Kelas 9A', 'Kelas 9B']
        ]);
    }

    /**
     * Pesan Kelas
     */
    public static function pesanKelas(): void
    {
        self::render('pesan_kelas', [
            'pageTitle' => 'Pesan & Pengumuman Kelas',
            'activeTab' => 'pesan',
            'classes' => ['Kelas 7A', 'Kelas 7B', 'Kelas 8A', 'Kelas 8B']
        ]);
    }

    /**
     * Bank Soal
     */
    public static function bankSoal(): void
    {
        self::render('bank_soal', [
            'pageTitle' => 'Bank Soal Matematika',
            'activeTab' => 'bank_soal',
            'questionPacks' => [
                ['title' => 'Paket Soal STS Semester Genap', 'class' => 'Kelas 7', 'count' => 30, 'type' => 'Pilihan Ganda & Uraian'],
                ['title' => 'Kuis Harian Aljabar Dasar', 'class' => 'Kelas 7', 'count' => 15, 'type' => 'Pilihan Ganda'],
                ['title' => 'Latihan Soal Ujian Sekolah 2026', 'class' => 'Kelas 9', 'count' => 40, 'type' => 'Campuran']
            ]
        ]);
    }

    /**
     * Al-Qur'an Digital with 2-Minute Reading Tracker for Auto Tilawah Check
     */
    public static function quran(): void
    {
        self::render('quran', [
            'pageTitle' => 'Al-Qur\'an Digital',
            'activeTab' => 'quran'
        ]);
    }

    /**
     * Dzikir Pagi & Petang (Al-Ma'tsurat Sugro & Kubro) with Interactive Digital Tasbih
     */
    public static function dzikir(): void
    {
        self::render('dzikir', [
            'pageTitle' => 'Dzikir Pagi & Petang (Al-Ma\'tsurat)',
            'activeTab' => 'dzikir'
        ]);
    }

    /**
     * Quran API Proxy: Surah List (with local filesystem cache)
     */
    public static function apiSuratList(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $cacheDir = BASE_PATH . '/storage/cache/quran';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        $cacheFile = $cacheDir . '/surat_list.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 30)) {
            readfile($cacheFile);
            exit;
        }

        $urls = [
            'https://equran.id/api/v2/surat',
            'https://api.quran.gading.dev/surah'
        ];

        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && $res) {
                @file_put_contents($cacheFile, $res);
                echo $res;
                exit;
            }
        }

        Response::json(['code' => 500, 'message' => 'Failed to fetch surah list']);
    }

    /**
     * Quran API Proxy: Surah Detail / Verses (with local filesystem cache)
     */
    public static function apiSuratDetail($nomor): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $nomor = (int)$nomor;
        if ($nomor < 1 || $nomor > 114) {
            Response::json(['code' => 400, 'message' => 'Nomor surah tidak valid']);
            return;
        }

        $cacheDir = BASE_PATH . '/storage/cache/quran';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        $cacheFile = $cacheDir . '/surat_' . $nomor . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 30)) {
            readfile($cacheFile);
            exit;
        }

        $urls = [
            "https://equran.id/api/v2/surat/{$nomor}",
            "https://api.quran.gading.dev/surah/{$nomor}"
        ];

        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && $res) {
                @file_put_contents($cacheFile, $res);
                echo $res;
                exit;
            }
        }

        Response::json(['code' => 500, 'message' => "Gagal memuat data surah $nomor"]);
    }

    /**
     * Quran API Proxy: Mushaf Page Detail (Pages 1 to 604)
     */
    public static function apiPageDetail($page): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $page = (int)$page;
        if ($page < 1 || $page > 604) {
            Response::json(['code' => 400, 'message' => 'Nomor halaman tidak valid (1-604)']);
            return;
        }

        $cacheDir = BASE_PATH . '/storage/cache/quran/pages';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        $cacheFile = $cacheDir . '/page_' . $page . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 90)) {
            readfile($cacheFile);
            exit;
        }

        $url = "https://api.alquran.cloud/v1/page/{$page}/quran-uthmani";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $res) {
            @file_put_contents($cacheFile, $res);
            echo $res;
            exit;
        }

        Response::json(['code' => 500, 'message' => "Gagal memuat data halaman $page"]);
    }

    /**
     * Pencatatan Keterlambatan Siswa
     */
    public static function keterlambatanSiswa(): void
    {
        $data = PortalGuruModel::getLateStudentRecords();

        self::render('keterlambatan_siswa', [
            'pageTitle' => 'Pencatatan Keterlambatan Siswa',
            'activeTab' => 'keterlambatan',
            'summary' => $data['summary'],
            'records' => $data['records'],
            'classes' => ['Kelas 7A', 'Kelas 7B', 'Kelas 8A', 'Kelas 8B', 'Kelas 9A', 'Kelas 9B']
        ]);
    }

    /**
     * Pengajuan Izin Guru (Izin Tidak Masuk & Izin Keluar/Tidak Mengajar)
     */
    public static function izin(): void
    {
        $permitsData = PortalGuruModel::getTeacherPermits();

        self::render('izin', [
            'pageTitle' => 'Pengajuan Izin Guru',
            'activeTab' => 'izin',
            'summary' => $permitsData['summary'],
            'records' => $permitsData['records'],
            'classes' => ['Kelas 7A', 'Kelas 7B', 'Kelas 8A', 'Kelas 8B', 'Kelas 9A', 'Kelas 9B']
        ]);
    }

    /**
     * Pengajuan Cuti Guru
     */
    public static function cuti(): void
    {
        $leavesData = PortalGuruModel::getTeacherLeaves();

        self::render('cuti', [
            'pageTitle' => 'Pengajuan Cuti Guru',
            'activeTab' => 'cuti',
            'quotas' => $leavesData['quotas'],
            'records' => $leavesData['records']
        ]);
    }
}
