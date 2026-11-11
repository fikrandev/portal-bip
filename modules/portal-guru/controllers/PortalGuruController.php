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
     * Dapatkan data profil guru yang sedang aktif login dari sesi
     */
    public static function getLoggedInTeacher(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'name' => 'Guru',
                'nip' => '-',
                'role' => 'Tenaga Pendidik',
                'status' => 'Aktif',
                'email' => '',
                'phone' => '',
                'avatar' => null,
                'teachingHours' => '24 Jam / Minggu',
                'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2025/2026 Genap'
            ];
        }

        $db = Database::getInstance();
        $pegawai = null;
        if (!empty($user['email'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE email = ? LIMIT 1", [$user['email']]);
        }
        if (!$pegawai && !empty($user['full_name'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE nama = ? LIMIT 1", [$user['full_name']]);
        }

        $fullName = !empty($user['full_name']) ? $user['full_name'] : ($pegawai['nama'] ?? 'Guru');
        $role = !empty($pegawai['jabatan']) ? $pegawai['jabatan'] : (Auth::hasRole('super_admin') ? 'Super Administrator' : 'Guru Pengajar');
        $nip = !empty($pegawai['niy']) ? $pegawai['niy'] : (!empty($pegawai['nik']) ? $pegawai['nik'] : '-');
        $phone = !empty($pegawai['no_wa']) ? $pegawai['no_wa'] : ($user['phone'] ?? '-');
        $status = !empty($pegawai['status_pegawai']) ? $pegawai['status_pegawai'] : 'Pegawai Aktif';
        $foto = !empty($pegawai['foto']) ? $pegawai['foto'] : ($user['avatar'] ?? null);

        $pegawaiId = !empty($pegawai['id']) ? (int)$pegawai['id'] : null;

        return [
            'id' => $pegawaiId,
            'pegawai_id' => $pegawaiId,
            'user_id' => (int)$user['id'],
            'name' => $fullName,
            'nip' => $nip,
            'role' => $role,
            'status' => $status,
            'email' => $user['email'] ?? ($pegawai['email'] ?? '-'),
            'phone' => $phone,
            'avatar' => $foto,
            'teachingHours' => '24 Jam / Minggu',
            'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2025/2026 Genap'
        ];
    }

    /**
     * Ambil jadwal mengajar guru dari jadwal_pelajaran yang aktif tersinkronisasi
     */
    public static function getTeacherSchedules(?int $pegawaiId, string $guruName, ?string $hari = null): array
    {
        $db = Database::getInstance();
        $params = [];
        $where = "1=1";

        if ($pegawaiId) {
            $where .= " AND (jp.pegawai_id = ? OR jp.nama_guru LIKE ?)";
            $params[] = $pegawaiId;
            $params[] = '%' . $guruName . '%';
        } else {
            $where .= " AND jp.nama_guru LIKE ?";
            $params[] = '%' . $guruName . '%';
        }

        if ($hari) {
            $where .= " AND jp.hari = ?";
            $params[] = $hari;
        }

        $sql = "
            SELECT jp.*, 
                   COALESCE(jp.jumlah_jp, 2) as jp_alokasi,
                   jm.id as jurnal_id, 
                   jm.kode_jurnal, 
                   jm.status as jurnal_status, 
                   jm.materi_pokok as jurnal_materi,
                   jm.uraian_kegiatan as jurnal_kegiatan,
                   jm.catatan_kelas as jurnal_catatan,
                   jm.jumlah_jp as jurnal_jp,
                   jm.kehadiran_summary as jurnal_kehadiran
            FROM jadwal_pelajaran jp
            LEFT JOIN jadwal_grup jg ON jp.grup_id = jg.id
            LEFT JOIN jurnal_mengajar jm ON (jm.jadwal_id = jp.id OR (jm.guru_id = jp.pegawai_id AND jm.kelas = jp.nama_kelas COLLATE utf8mb4_unicode_ci AND jm.mata_pelajaran = jp.mata_pelajaran COLLATE utf8mb4_unicode_ci AND jm.jam_ke = CONCAT(jp.jam_ke))) AND jm.tanggal = CURDATE()
            WHERE {$where}
              AND (jg.is_active = 1 OR jp.grup_id = (SELECT id FROM jadwal_grup ORDER BY is_active DESC, id DESC LIMIT 1))
            ORDER BY FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jp.jam_ke ASC, jp.jam_mulai ASC
        ";

        try {
            $rows = $db->findAll($sql, $params);
        } catch (Throwable $e) {
            $rows = [];
        }

        $now = date('H:i:s');
        $schedules = [];
        foreach ($rows as $r) {
            $start = !empty($r['jam_mulai']) ? date('H:i', strtotime($r['jam_mulai'])) : '-';
            $end = !empty($r['jam_selesai']) ? date('H:i', strtotime($r['jam_selesai'])) : '-';
            $timeRange = ($start !== '-' && $end !== '-') ? "{$start} - {$end}" : "Jam ke-{$r['jam_ke']}";
            
            $status = 'Mendatang';
            if (!empty($r['jam_mulai']) && !empty($r['jam_selesai'])) {
                if ($now >= $r['jam_mulai'] && $now <= $r['jam_selesai']) {
                    $status = 'Berlangsung';
                } elseif ($now > $r['jam_selesai']) {
                    $status = 'Selesai';
                }
            }

            $hasJurnal = !empty($r['jurnal_id']);

            $schedules[] = [
                'id' => (int)$r['id'],
                'hari' => $r['hari'],
                'jam_ke' => $r['jam_ke'],
                'jumlah_jp' => (int)($r['jp_alokasi'] ?: 2),
                'jam_mulai' => $r['jam_mulai'],
                'jam_selesai' => $r['jam_selesai'],
                'time' => $timeRange,
                'class' => $r['nama_kelas'],
                'subject' => $r['mata_pelajaran'],
                'room' => !empty($r['ruangan']) ? $r['ruangan'] : 'Ruang Kelas',
                'status' => $status,
                'has_jurnal' => $hasJurnal,
                'jurnal_id' => $r['jurnal_id'],
                'jurnal_kode' => $r['kode_jurnal'],
                'jurnal_status' => $r['jurnal_status'],
                'jurnal_materi' => $r['jurnal_materi'],
                'jurnal_kegiatan' => $r['jurnal_kegiatan'],
                'jurnal_catatan' => $r['jurnal_catatan'],
                'jurnal_jp' => $r['jurnal_jp'],
                'jurnal_kehadiran' => $r['jurnal_kehadiran'],
            ];
        }

        return $schedules;
    }

    /**
     * Mobile Home (Beranda)
     */
    public static function beranda(): void
    {
        $teacher = self::getLoggedInTeacher();

        $indonesianDays = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $todayName = $indonesianDays[date('l')] ?? 'Senin';

        $todaySchedule = self::getTeacherSchedules($teacher['pegawai_id'], $teacher['name'], $todayName);
        $allTeacherSchedules = self::getTeacherSchedules($teacher['pegawai_id'], $teacher['name']);
        
        $classesTaught = array_unique(array_filter(array_column($allTeacherSchedules, 'class')));
        $totalClasses = count($classesTaught) ?: 1;

        $todayJp = array_sum(array_column($todaySchedule, 'jumlah_jp'));
        $todayFilledJurnal = count(array_filter($todaySchedule, fn($s) => $s['has_jurnal']));

        self::render('beranda', [
            'pageTitle' => 'Beranda Guru',
            'activeTab' => 'beranda',
            'teacherName' => $teacher['name'],
            'teacherRole' => $teacher['role'],
            'teacherAvatar' => $teacher['avatar'],
            'teacherNip' => $teacher['nip'],
            'totalClasses' => $totalClasses,
            'todayName' => $todayName,
            'todayDate' => date('d F Y'),
            'todayJp' => $todayJp,
            'todayFilledJurnal' => $todayFilledJurnal,
            'todaySchedule' => $todaySchedule,
            'activeTasks' => 12,
            'pendingGrades' => 36,
            'classReports' => 4,
        ]);
    }

    /**
     * Absen Kehadiran & Geolokasi
     */
    public static function absen(): void
    {
        $schoolName = 'Kampus Utama BIP';
        $schoolRadius = 150;
        $schoolCoords = ['lat' => -5.147665, 'lng' => 119.432732];
        $lokasiSourceLabel = 'Lokasi Semula (Default)';

        if (file_exists(MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php')) {
            require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
            $pegawaiId = (int)($_SESSION['user']['pegawai_id'] ?? $_GET['pegawai_id'] ?? 0);
            if ($pegawaiId <= 0 && !empty($_SESSION['user']['nama_lengkap'])) {
                try {
                    $db = Database::getInstance()->getConnection();
                    $st = $db->prepare("SELECT id FROM pegawai WHERE nama_lengkap = ? LIMIT 1");
                    $st->execute([$_SESSION['user']['nama_lengkap']]);
                    $pegawaiId = (int)$st->fetchColumn();
                } catch (Throwable $e) {}
            }
            $efektif = AbsenPegawaiModel::getLokasiEfektifPegawai($pegawaiId);
            $schoolName = $efektif['nama_kantor'] ?? $schoolName;
            $schoolRadius = (int)($efektif['radius_meter'] ?? $schoolRadius);
            $schoolCoords = [
                'lat' => (float)($efektif['latitude'] ?? -5.147665),
                'lng' => (float)($efektif['longitude'] ?? 119.432732),
            ];
            $lokasiSourceLabel = $efektif['source_label'] ?? $lokasiSourceLabel;
        }

        self::render('absen', [
            'pageTitle' => 'Presensi Kehadiran & Geolokasi',
            'activeTab' => 'absen',
            'schoolName' => $schoolName,
            'schoolRadius' => $schoolRadius,
            'schoolCoords' => $schoolCoords,
            'lokasiSourceLabel' => $lokasiSourceLabel,
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
        $teacher = self::getLoggedInTeacher();
        $db = Database::getInstance();

        $indonesianDays = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $todayName = $indonesianDays[date('l')] ?? 'Senin';

        // Ambil jadwal hari ini dan seluruh jadwal guru
        $todaySchedule = self::getTeacherSchedules($teacher['pegawai_id'], $teacher['name'], $todayName);
        $allSchedules = self::getTeacherSchedules($teacher['pegawai_id'], $teacher['name']);

        // Riwayat jurnal dari database
        $recentJournals = [];
        try {
            $recentJournals = $db->findAll(
                "SELECT * FROM jurnal_mengajar 
                 WHERE guru_id = ? OR nama_guru LIKE ? 
                 ORDER BY tanggal DESC, id DESC LIMIT 30",
                [$teacher['pegawai_id'] ?: 0, '%' . $teacher['name'] . '%']
            );
        } catch (Throwable $e) {}

        // Format riwayat jurnal untuk tampilan
        $formattedJournals = [];
        foreach ($recentJournals as $rj) {
            $formattedJournals[] = [
                'id' => $rj['id'],
                'kode' => $rj['kode_jurnal'],
                'date' => date('d M Y', strtotime($rj['tanggal'])),
                'tanggal_raw' => $rj['tanggal'],
                'class' => $rj['kelas'],
                'subject' => $rj['mata_pelajaran'],
                'hours' => 'Jam ke-' . $rj['jam_ke'] . ' (' . ($rj['jumlah_jp'] ?? 2) . ' JP)',
                'jumlah_jp' => $rj['jumlah_jp'] ?? 2,
                'topic' => $rj['materi_pokok'],
                'kegiatan' => $rj['uraian_kegiatan'] ?? '',
                'summary' => $rj['uraian_kegiatan'] ?: 'Pembelajaran tatap muka sesuai modul ajar.',
                'attendance' => $rj['kehadiran_summary'] ?: 'Semua Siswa Hadir',
                'catatan' => $rj['catatan_kelas'] ?? '',
                'status' => ucfirst($rj['status'] ?? 'selesai')
            ];
        }

        // Distinct classes & subjects untuk opsi dropdown mandiri
        $classes = array_unique(array_filter(array_column($allSchedules, 'class')));
        if (empty($classes)) {
            $classes = ['Kelas 1 Abu Bakar', 'Kelas 1 Bilal bin Rabbah', 'Kelas 3 Abdurrahman', 'Kelas 4 Anas bin Malik', 'Kelas 5 Ibnu Abbas'];
        }
        $subjects = array_unique(array_filter(array_column($allSchedules, 'subject')));
        if (empty($subjects)) {
            $subjects = ['Tahsin & Al-Quran', 'Bina Pribadi Islam', 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'Matematika'];
        }

        self::render('jurnal', [
            'pageTitle' => 'Jurnal Mengajar Harian',
            'activeTab' => 'jurnal',
            'teacher' => $teacher,
            'todayName' => $todayName,
            'todayDate' => date('d F Y'),
            'todaySchedule' => $todaySchedule,
            'allSchedules' => $allSchedules,
            'classes' => array_values($classes),
            'subjects' => array_values($subjects),
            'recentJournals' => $formattedJournals
        ]);
    }

    /**
     * Simpan / Perbarui Jurnal Pembelajaran dari Mobile
     */
    public static function simpanJurnal(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $teacher = self::getLoggedInTeacher();
        $db = Database::getInstance();

        $jadwalId = !empty($_POST['jadwal_id']) ? (int)$_POST['jadwal_id'] : null;
        $jurnalId = !empty($_POST['jurnal_id']) ? (int)$_POST['jurnal_id'] : null;
        $kelas = trim($_POST['kelas'] ?? '');
        $mapel = trim($_POST['mata_pelajaran'] ?? ($_POST['mapel'] ?? ''));
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $jamKe = trim($_POST['jam_ke'] ?? '1');
        $jumlahJp = max(1, min(10, (int)($_POST['jumlah_jp'] ?? 2)));
        $topik = trim($_POST['topik'] ?? ($_POST['materi_pokok'] ?? ''));
        $kegiatan = trim($_POST['kegiatan'] ?? ($_POST['uraian_kegiatan'] ?? ''));
        $catatan = trim($_POST['catatan'] ?? ($_POST['catatan_kelas'] ?? ''));
        $kehadiran = trim($_POST['kehadiran'] ?? ($_POST['kehadiran_summary'] ?? 'Lengkap Hadir'));

        if (empty($kelas) || empty($mapel) || empty($topik)) {
            echo json_encode([
                'success' => false,
                'message' => 'Kelas, Mata Pelajaran, dan Materi/Topik wajib diisi.'
            ]);
            exit;
        }

        $guruId = $teacher['pegawai_id'] ?: (int)Auth::id();
        $namaGuru = $teacher['name'];

        try {
            if ($jurnalId) {
                // Update jurnal yang sudah ada
                $db->update('jurnal_mengajar', [
                    'kelas' => $kelas,
                    'mata_pelajaran' => $mapel,
                    'tanggal' => $tanggal,
                    'jam_ke' => $jamKe,
                    'jumlah_jp' => $jumlahJp,
                    'materi_pokok' => $topik,
                    'uraian_kegiatan' => $kegiatan,
                    'catatan_kelas' => $catatan,
                    'kehadiran_summary' => $kehadiran,
                    'status' => 'selesai',
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$jurnalId]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Jurnal pembelajaran berhasil diperbarui!',
                    'jurnal_id' => $jurnalId
                ]);
                exit;
            } else {
                // Buat kode jurnal unik
                $kodeJurnal = 'JRN-' . date('Ymd', strtotime($tanggal)) . '-' . strtoupper(substr(uniqid(), -4));

                $newId = $db->insert('jurnal_mengajar', [
                    'kode_jurnal' => $kodeJurnal,
                    'jadwal_id' => $jadwalId,
                    'guru_id' => $guruId,
                    'nama_guru' => $namaGuru,
                    'kelas' => $kelas,
                    'mata_pelajaran' => $mapel,
                    'tanggal' => $tanggal,
                    'jam_ke' => $jamKe,
                    'jumlah_jp' => $jumlahJp,
                    'materi_pokok' => $topik,
                    'uraian_kegiatan' => $kegiatan,
                    'catatan_kelas' => $catatan,
                    'kehadiran_summary' => $kehadiran,
                    'status' => 'selesai',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Jurnal pembelajaran berhasil disimpan!',
                    'jurnal_id' => $newId,
                    'kode_jurnal' => $kodeJurnal
                ]);
                exit;
            }
        } catch (Throwable $e) {
            error_log('simpanJurnal error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan jurnal: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * API Detail Jurnal untuk modal preview / edit
     */
    public static function apiJurnalDetail(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = Database::getInstance();
        $jurnal = $db->find("SELECT * FROM jurnal_mengajar WHERE id = ?", [$id]);

        if (!$jurnal) {
            echo json_encode(['success' => false, 'message' => 'Jurnal tidak ditemukan.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $jurnal]);
        exit;
    }

    /**
     * API Daftar Siswa per Kelas untuk Presensi Jurnal Mengajar
     */
    public static function apiSiswaKelas(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $className = trim($_GET['kelas'] ?? '');

        if (empty($className)) {
            echo json_encode(['success' => false, 'message' => 'Nama kelas tidak diberikan.']);
            exit;
        }

        $db = Database::getInstance();
        $cleanName = trim(preg_replace('/^kelas\s+/i', '', $className));

        $sql = "
            SELECT id, nisn, nama_lengkap, nama, jenis_kelamin, kelas
            FROM siswa 
            WHERE (kelas = ? OR kelas = ? OR kelas LIKE ?)
              AND is_active = 1
            ORDER BY nama_lengkap ASC
        ";

        try {
            $students = $db->findAll($sql, [$className, $cleanName, '%' . $cleanName . '%']);
            
            // Fallback jika belum ada data siswa di DB untuk kelas tersebut
            if (empty($students)) {
                $samples = [
                    'Achmad Zaky Pratama', 'Aisyah Putri Azzahra', 'Alif Ramadhan', 
                    'Anisa Nur Fadilah', 'Bagus Dwi Wicaksono', 'Cantika Dewi Lestari',
                    'Dimas Arya Nugraha', 'Fadli Kurniawan', 'Gita Permata Sari', 'Hafizh Pratama'
                ];
                $students = [];
                foreach ($samples as $idx => $name) {
                    $students[] = [
                        'id' => $idx + 1,
                        'nisn' => '008' . str_pad((string)($idx + 1), 6, '0', STR_PAD_LEFT),
                        'nama_lengkap' => $name,
                        'nama' => $name,
                        'jenis_kelamin' => ($idx % 2 === 0) ? 'L' : 'P',
                        'kelas' => $className
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'kelas' => $className,
                'total' => count($students),
                'data' => $students
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
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
        $teacher = self::getLoggedInTeacher();

        self::render('profil', [
            'pageTitle' => 'Profil Guru & Pengaturan PWA',
            'activeTab' => 'profil',
            'profile' => $teacher
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
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
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
