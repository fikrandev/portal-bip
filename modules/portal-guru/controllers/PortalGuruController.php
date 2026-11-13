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
        $db = Database::getInstance();

        // 1. Dukungan Switch / Preview Guru via URL (?guru_id=17) atau Session
        $previewId = (int)($_GET['guru_id'] ?? $_SESSION['portal_guru_preview_id'] ?? 0);
        if ($previewId > 0) {
            $_SESSION['portal_guru_preview_id'] = $previewId;
            $previewPegawai = $db->find("SELECT * FROM pegawai WHERE id = ? LIMIT 1", [$previewId]);
            if ($previewPegawai) {
                return self::formatTeacherArray($previewPegawai, $user);
            }
        }

        if (!$user) {
            return [
                'id' => null,
                'pegawai_id' => null,
                'user_id' => null,
                'name' => 'Guru',
                'nip' => '-',
                'role' => 'Tenaga Pendidik',
                'status' => 'Aktif',
                'email' => '',
                'phone' => '',
                'avatar' => null,
                'teachingHours' => '24 Jam / Minggu',
                'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil'
            ];
        }

        $pegawai = null;
        if (!empty($user['email'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE email = ? LIMIT 1", [$user['email']]);
        }
        if (!$pegawai && !empty($user['full_name'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE nama = ? OR nama LIKE ? LIMIT 1", [$user['full_name'], '%' . $user['full_name'] . '%']);
        }
        if (!$pegawai && !empty($user['phone'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE no_wa = ? LIMIT 1", [$user['phone']]);
        }
        if (!$pegawai && !empty($user['username'])) {
            $cleanName = str_replace(['.', '_', '-'], ' ', $user['username']);
            $pegawai = $db->find("SELECT * FROM pegawai WHERE nama LIKE ? LIMIT 1", ['%' . $cleanName . '%']);
        }

        // Jika user adalah admin dan belum terhubung ke pegawai, gunakan Ayu Musdalifah (id 17) sebagai default guru contoh
        if (!$pegawai && (Auth::hasRole('super_admin') || ($user['id'] ?? 0) === 1)) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = 17 LIMIT 1");
        }

        if ($pegawai) {
            return self::formatTeacherArray($pegawai, $user);
        }

        $fullName = !empty($user['full_name']) ? $user['full_name'] : 'Guru';
        $role = Auth::hasRole('super_admin') ? 'Super Administrator' : 'Guru Pengajar';

        return [
            'id' => null,
            'pegawai_id' => null,
            'user_id' => (int)$user['id'],
            'name' => $fullName,
            'nip' => '-',
            'role' => $role,
            'status' => 'Pegawai Aktif',
            'email' => $user['email'] ?? '-',
            'phone' => $user['phone'] ?? '-',
            'avatar' => $user['avatar'] ?? null,
            'teachingHours' => '24 Jam / Minggu',
            'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil'
        ];
    }

    /**
     * Helper format data array guru
     */
    private static function formatTeacherArray(array $pegawai, ?array $user = null): array
    {
        $fullName = !empty($pegawai['nama']) ? $pegawai['nama'] : ($user['full_name'] ?? 'Guru');
        $role = !empty($pegawai['jabatan']) ? $pegawai['jabatan'] : 'Wali Kelas & Guru Pengajar';
        $nip = !empty($pegawai['niy']) ? $pegawai['niy'] : (!empty($pegawai['nik']) ? $pegawai['nik'] : '-');
        $phone = !empty($pegawai['no_wa']) ? $pegawai['no_wa'] : ($user['phone'] ?? '-');
        $status = !empty($pegawai['status_pegawai']) ? $pegawai['status_pegawai'] : 'Pegawai Aktif';
        $foto = !empty($pegawai['foto']) ? $pegawai['foto'] : ($user['avatar'] ?? null);
        $pegawaiId = (int)$pegawai['id'];

        return [
            'id' => $pegawaiId,
            'pegawai_id' => $pegawaiId,
            'user_id' => (int)($user['id'] ?? 0),
            'name' => $fullName,
            'nip' => $nip,
            'role' => $role,
            'status' => $status,
            'email' => $pegawai['email'] ?: ($user['email'] ?? '-'),
            'phone' => $phone,
            'avatar' => $foto,
            'teachingHours' => '24 Jam / Minggu',
            'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil'
        ];
    }

    /**
     * Ambil seluruh kelas yang diampu guru dari SK Penugasan Aktif & Jadwal Pelajaran
     */
    public static function getTeacherClasses(?int $pegawaiId, string $guruName): array
    {
        $db = Database::getInstance();
        $classes = [];

        // 1. Dari SK Penugasan Aktif (pegawai_penugasan -> wali_kelas_nama)
        if ($pegawaiId) {
            try {
                $waliRows = $db->findAll("
                    SELECT pp.wali_kelas_nama, pg.nama_grup, pg.semester
                    FROM pegawai_penugasan pp
                    JOIN penugasan_grup pg ON pp.grup_id = pg.id
                    WHERE pp.pegawai_id = ? AND pg.is_active = 1 AND pp.wali_kelas_nama IS NOT NULL AND pp.wali_kelas_nama != ''
                ", [$pegawaiId]);
                foreach ($waliRows as $w) {
                    $cName = trim($w['wali_kelas_nama']);
                    if (!isset($classes[$cName])) {
                        $classes[$cName] = [
                            'name' => $cName,
                            'role' => 'Wali Kelas',
                            'roleBadge' => 'emerald',
                            'subjects' => [],
                            'total_jp' => 0,
                            'schedule' => 'Setiap Hari KBM',
                            'is_wali' => true
                        ];
                    } else {
                        $classes[$cName]['role'] = 'Wali Kelas & Guru Mapel';
                        $classes[$cName]['is_wali'] = true;
                    }
                }
            } catch (Throwable $e) {}

            // 2. Dari SK Penugasan Mengajar (pegawai_penugasan_mengajar)
            try {
                $mengajarRows = $db->findAll("
                    SELECT ppm.*, pg.nama_grup
                    FROM pegawai_penugasan_mengajar ppm
                    JOIN penugasan_grup pg ON ppm.grup_id = pg.id
                    WHERE ppm.pegawai_id = ? AND pg.is_active = 1
                ", [$pegawaiId]);
                foreach ($mengajarRows as $m) {
                    $cName = trim($m['nama_kelas']);
                    if (empty($cName)) continue;
                    if (!isset($classes[$cName])) {
                        $classes[$cName] = [
                            'name' => $cName,
                            'role' => 'Guru Pengajar (PIC)',
                            'roleBadge' => 'blue',
                            'subjects' => [],
                            'total_jp' => 0,
                            'schedule' => '-',
                            'is_wali' => false
                        ];
                    } else {
                        $classes[$cName]['role'] = 'Wali Kelas & Guru Mapel';
                    }
                    if (!empty($m['mata_pelajaran']) && !in_array($m['mata_pelajaran'], $classes[$cName]['subjects'])) {
                        $classes[$cName]['subjects'][] = $m['mata_pelajaran'];
                    }
                    $classes[$cName]['total_jp'] += (int)($m['jumlah_jp'] ?? 2);
                }
            } catch (Throwable $e) {}
        }

        // 3. Dari Jadwal Pelajaran (jadwal_pelajaran)
        try {
            $jadwalRows = $db->findAll("
                SELECT jp.nama_kelas, jp.mata_pelajaran, jp.hari, jp.jam_ke, jp.jam_mulai, jp.jam_selesai, jp.ruangan
                FROM jadwal_pelajaran jp
                LEFT JOIN jadwal_grup jg ON jp.grup_id = jg.id
                WHERE (jp.pegawai_id = ? OR jp.nama_guru LIKE ?)
                  AND (jg.is_active = 1 OR jp.grup_id = (SELECT id FROM jadwal_grup ORDER BY is_active DESC, id DESC LIMIT 1))
                ORDER BY FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jp.jam_ke ASC
            ", [$pegawaiId ?: 0, '%' . $guruName . '%']);

            foreach ($jadwalRows as $j) {
                $cName = trim($j['nama_kelas']);
                if (empty($cName)) continue;
                if (!isset($classes[$cName])) {
                    $classes[$cName] = [
                        'name' => $cName,
                        'role' => 'Guru Pengajar',
                        'roleBadge' => 'indigo',
                        'subjects' => [],
                        'total_jp' => 0,
                        'schedule' => '-',
                        'is_wali' => false
                    ];
                }
                if (!empty($j['mata_pelajaran']) && !in_array($j['mata_pelajaran'], $classes[$cName]['subjects'])) {
                    $classes[$cName]['subjects'][] = $j['mata_pelajaran'];
                }
                $timeStr = ($j['hari'] ? $j['hari'] . ', ' : '') . ($j['jam_mulai'] ? substr($j['jam_mulai'], 0, 5) . '-' . substr($j['jam_selesai'], 0, 5) : 'Jam ke-' . $j['jam_ke']);
                if ($classes[$cName]['schedule'] === '-' || str_contains($classes[$cName]['schedule'], 'Setiap Hari')) {
                    $classes[$cName]['schedule'] = $timeStr;
                } else if (!str_contains($classes[$cName]['schedule'], $timeStr)) {
                    $classes[$cName]['schedule'] .= ' & ' . $timeStr;
                }
            }
        } catch (Throwable $e) {}

        // Fallback jika tidak ada data sama sekali
        if (empty($classes)) {
            $classes['Kelas 5 Ibnu Abbas'] = [
                'name' => 'Kelas 5 Ibnu Abbas',
                'role' => 'Wali Kelas & Guru Mapel',
                'roleBadge' => 'emerald',
                'subjects' => ['SbdP'],
                'total_jp' => 2,
                'schedule' => 'Senin, 07:30 - 08:30',
                'is_wali' => true
            ];
        }

        // Hitung jumlah siswa riil dari tabel siswa untuk setiap kelas
        $pdo = $db->getConnection();
        $colorPalette = ['emerald', 'blue', 'indigo', 'purple', 'teal', 'sky', 'amber'];
        $cIndex = 0;

        foreach ($classes as $cName => &$info) {
            $cleanName = trim(preg_replace('/^kelas\s+/i', '', $cName));
            try {
                $st = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM siswa 
                    WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ? OR ? = REPLACE(kelas, 'Kelas ', ''))
                      AND (is_active = 1 OR is_active IS NULL OR status_siswa = 'Aktif')
                ");
                $st->execute([$cName, $cleanName, $cleanName, $cleanName]);
                $info['totalStudents'] = (int)$st->fetchColumn();
            } catch (Throwable $e) {
                $info['totalStudents'] = 0;
            }

            // Cari nama Wali Kelas jika guru ini bukan wali kelasnya
            if (!$info['is_wali']) {
                try {
                    $stWali = $pdo->prepare("
                        SELECT COALESCE(p.nama, pp.wali_kelas_nama) 
                        FROM pegawai_penugasan pp
                        JOIN penugasan_grup pg ON pp.grup_id = pg.id
                        JOIN pegawai p ON pp.pegawai_id = p.id
                        WHERE (pp.wali_kelas_nama = ? OR pp.wali_kelas_nama = ?) AND pg.is_active = 1
                        LIMIT 1
                    ");
                    $stWali->execute([$cName, $cleanName]);
                    $waliFound = $stWali->fetchColumn();
                    $info['homeroom'] = $waliFound ?: 'Wali Kelas BIP';
                } catch (Throwable $e) {
                    $info['homeroom'] = 'Wali Kelas BIP';
                }
            } else {
                $info['homeroom'] = $guruName . ' (Anda)';
            }

            // Ekstrak tingkat (misal '5' dari 'Kelas 5 Ibnu Abbas')
            if (preg_match('/(\d+)/', $cName, $matches)) {
                $info['level'] = $matches[1];
            } else {
                $info['level'] = 'SD';
            }

            $info['color'] = $colorPalette[$cIndex % count($colorPalette)];
            $info['jp'] = $info['total_jp'] ?: 2;
            $info['subjectList'] = !empty($info['subjects']) ? implode(', ', $info['subjects']) : 'Mata Pelajaran';
            $cIndex++;
        }
        unset($info);

        return $classes;
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

        // Distinct classes & subjects dari SK Penugasan & Jadwal Pelajaran
        $teacherClasses = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        $classes = array_keys($teacherClasses);
        $subjects = [];
        foreach ($teacherClasses as $cInfo) {
            foreach ($cInfo['subjects'] as $sb) {
                if (!empty($sb) && !in_array($sb, $subjects)) {
                    $subjects[] = $sb;
                }
            }
        }
        foreach ($allSchedules as $sch) {
            if (!empty($sch['subject']) && !in_array($sch['subject'], $subjects)) {
                $subjects[] = $sch['subject'];
            }
        }
        if (empty($classes)) {
            $classes = array_unique(array_filter(array_column($allSchedules, 'class')));
        }
        if (empty($classes)) {
            $classes = ['Kelas 5 Ibnu Abbas', 'Kelas 5 ABdullah bin Mas\'u', 'Kelas 5 Muadz bin jabal'];
        }
        if (empty($subjects)) {
            $subjects = ['SbdP', 'Pendidikan Agama Islam', 'Tahsin & Al-Quran', 'Matematika'];
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

                // Sinkronkan ke presensi_kelas jika belum tercatat hari ini
                try {
                    $cleanKls = trim(preg_replace('/^kelas\s+/i', '', $kelas));
                    $existingPres = $db->find("
                        SELECT id FROM presensi_kelas 
                        WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ?)
                          AND tanggal = ? AND (guru_id = ? OR guru_id = 0)
                        LIMIT 1
                    ", [$kelas, $cleanKls, $cleanKls, $tanggal, $guruId]);
                    if (!$existingPres) {
                        $db->insert('presensi_kelas', [
                            'guru_id' => $guruId,
                            'kelas' => $kelas,
                            'mata_pelajaran' => $mapel,
                            'tanggal' => $tanggal,
                            'jam_ke' => $jamKe,
                            'role_mengajar' => 'PIC',
                            'jp' => $jumlahJp,
                            'tipe_presensi' => 'mapel',
                            'catatan' => $kehadiran,
                            'created_by' => (int)Auth::id() ?: 1,
                            'waktu_presensi' => date('H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (Throwable $pe) {}

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
        $tanggal = trim($_GET['tanggal'] ?? date('Y-m-d'));

        if (empty($className)) {
            echo json_encode(['success' => false, 'message' => 'Nama kelas tidak diberikan.']);
            exit;
        }

        $db = Database::getInstance();
        $cleanName = trim(preg_replace('/^kelas\s+/i', '', $className));

        $sql = "
            SELECT id, nisn, nis, COALESCE(nama_lengkap, nama) as nama_lengkap, nama, jenis_kelamin, kelas, no_hp, telepon, foto
            FROM siswa 
            WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ? OR ? = REPLACE(kelas, 'Kelas ', ''))
              AND (is_active = 1 OR is_active IS NULL OR status_siswa = 'Aktif')
            ORDER BY COALESCE(nama_lengkap, nama) ASC
        ";

        try {
            $students = $db->findAll($sql, [$className, $cleanName, $cleanName, $cleanName]);

            // Cek apakah sudah ada data presensi hari ini untuk kelas ini
            $presensiHariIni = $db->find("
                SELECT id FROM presensi_kelas 
                WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ?)
                  AND tanggal = ?
                ORDER BY id DESC LIMIT 1
            ", [$className, $cleanName, $cleanName, $tanggal]);

            $existingDetails = [];
            if ($presensiHariIni) {
                $details = $db->findAll("SELECT siswa_id, status FROM presensi_kelas_detail WHERE presensi_kelas_id = ?", [$presensiHariIni['id']]);
                foreach ($details as $d) {
                    $existingDetails[(int)$d['siswa_id']] = $d['status'];
                }
            }

            foreach ($students as &$st) {
                $stId = (int)$st['id'];
                $st['today_status'] = $existingDetails[$stId] ?? 'H';
            }
            unset($st);

            echo json_encode([
                'success' => true,
                'kelas' => $className,
                'clean_kelas' => $cleanName,
                'total' => count($students),
                'has_presensi_today' => !empty($presensiHariIni),
                'data' => $students
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Kelas Diampu - Mengambil data riil dari SK Penugasan Aktif & Jadwal KBM
     */
    public static function kelas(): void
    {
        $teacher = self::getLoggedInTeacher();
        $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        $classList = array_values($classesMap);

        // Hitung total JP dan ringkasan PIC vs Mendampingi / Wali Kelas
        $totalPicClasses = 0;
        $totalPicJp = 0;
        $totalWaliClasses = 0;
        $totalWaliJp = 0;

        foreach ($classList as $c) {
            if ($c['is_wali']) {
                $totalWaliClasses++;
                $totalWaliJp += (int)($c['jp'] ?? 2);
            } else {
                $totalPicClasses++;
                $totalPicJp += (int)($c['jp'] ?? 2);
            }
        }

        self::render('kelas', [
            'pageTitle' => 'Kelas Diampu',
            'activeTab' => 'kelas',
            'teacher' => $teacher,
            'classList' => $classList,
            'totalPicClasses' => $totalPicClasses,
            'totalPicJp' => $totalPicJp,
            'totalWaliClasses' => $totalWaliClasses,
            'totalWaliJp' => $totalWaliJp
        ]);
    }

    /**
     * Absensi Siswa per Kelas
     */
    public static function absensiKelas(): void
    {
        $teacher = self::getLoggedInTeacher();
        $db = Database::getInstance();
        $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        
        // Tentukan kelas yang dipilih (dari ?kelas=... atau default ke Wali Kelas atau kelas pertama)
        $selectedClass = trim($_GET['kelas'] ?? '');
        if (empty($selectedClass) || !isset($classesMap[$selectedClass])) {
            // Cari kelas wali kelas dahulu
            foreach ($classesMap as $cName => $cInfo) {
                if ($cInfo['is_wali']) {
                    $selectedClass = $cName;
                    break;
                }
            }
            if (empty($selectedClass) && !empty($classesMap)) {
                $selectedClass = array_key_first($classesMap);
            }
        }

        // Ambil data siswa untuk kelas terpilih
        $cleanName = trim(preg_replace('/^kelas\s+/i', '', $selectedClass));
        $students = [];
        try {
            $students = $db->findAll("
                SELECT id, nisn, nis, COALESCE(nama_lengkap, nama) as name, nama_lengkap, jenis_kelamin as gender, kelas, no_hp
                FROM siswa 
                WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ? OR ? = REPLACE(kelas, 'Kelas ', ''))
                  AND (is_active = 1 OR is_active IS NULL OR status_siswa = 'Aktif')
                ORDER BY COALESCE(nama_lengkap, nama) ASC
            ", [$selectedClass, $cleanName, $cleanName, $cleanName]);
        } catch (Throwable $e) {}

        // Cek data presensi hari ini
        $tanggal = date('Y-m-d');
        $presensiHariIni = null;
        try {
            $presensiHariIni = $db->find("
                SELECT * FROM presensi_kelas 
                WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ?)
                  AND tanggal = ?
                ORDER BY id DESC LIMIT 1
            ", [$selectedClass, $cleanName, $cleanName, $tanggal]);
        } catch (Throwable $e) {}

        $savedDetails = [];
        if ($presensiHariIni) {
            try {
                $details = $db->findAll("SELECT siswa_id, status FROM presensi_kelas_detail WHERE presensi_kelas_id = ?", [$presensiHariIni['id']]);
                foreach ($details as $d) {
                    $savedDetails[(int)$d['siswa_id']] = $d['status'];
                }
            } catch (Throwable $e) {}
        }

        foreach ($students as &$s) {
            $sId = (int)$s['id'];
            $s['status'] = $savedDetails[$sId] ?? 'H';
        }
        unset($s);

        self::render('absensi_kelas', [
            'pageTitle' => 'Absensi Siswa Kelas',
            'activeTab' => 'kelas',
            'teacher' => $teacher,
            'classes' => $classesMap,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'presensiHariIni' => $presensiHariIni
        ]);
    }

    /**
     * Simpan Presensi Siswa Kelas dari Mobile
     */
    public static function simpanAbsensiKelas(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $teacher = self::getLoggedInTeacher();
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $kelas = trim($_POST['kelas'] ?? '');
        $mapel = trim($_POST['mata_pelajaran'] ?? ($_POST['mapel'] ?? ''));
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $jamKe = trim($_POST['jam_ke'] ?? '1');
        $tipePresensi = trim($_POST['tipe_presensi'] ?? 'mapel');
        $catatanUmum = trim($_POST['catatan'] ?? '');
        $statuses = $_POST['kehadiran'] ?? []; // array [siswa_id => status]
        $redirectToJurnal = !empty($_POST['redirect_to_jurnal']);

        if (empty($kelas)) {
            echo json_encode(['success' => false, 'message' => 'Kelas harus dipilih.']);
            exit;
        }

        $guruId = $teacher['pegawai_id'] ?: 0;
        $userId = Auth::id() ?: 1;

        // Ambil data mapel default dari kelas jika kosong
        if (empty($mapel)) {
            $classesMap = self::getTeacherClasses($guruId, $teacher['name']);
            $mapel = !empty($classesMap[$kelas]['subjects']) ? implode(', ', $classesMap[$kelas]['subjects']) : ($classesMap[$kelas]['is_wali'] ? 'Wali Kelas' : 'KBM');
        }

        try {
            // Hitung status Hadir/Sakit/Izin/Alpa
            $h = 0; $s = 0; $i = 0; $a = 0;
            foreach ($statuses as $stat) {
                if ($stat === 'H') $h++;
                elseif ($stat === 'S') $s++;
                elseif ($stat === 'I') $i++;
                elseif ($stat === 'A') $a++;
            }
            $totalSiswa = count($statuses);

            if ($totalSiswa > 0 && ($s + $i + $a === 0)) {
                $summary = "{$totalSiswa}/{$totalSiswa} Hadir Lengkap";
            } elseif ($totalSiswa > 0) {
                $parts = ["{$h} Hadir"];
                if ($s > 0) $parts[] = "{$s} Sakit";
                if ($i > 0) $parts[] = "{$i} Izin";
                if ($a > 0) $parts[] = "{$a} Alpa";
                $summary = "{$totalSiswa} Siswa (" . implode(', ', $parts) . ")";
            } else {
                $summary = "Semua Siswa Hadir";
            }

            // Cek apakah sudah ada presensi hari ini
            $cleanName = trim(preg_replace('/^kelas\s+/i', '', $kelas));
            $existing = $db->find("
                SELECT id FROM presensi_kelas 
                WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ?)
                  AND tanggal = ? AND (guru_id = ? OR guru_id = 0)
                ORDER BY id DESC LIMIT 1
            ", [$kelas, $cleanName, $cleanName, $tanggal, $guruId]);

            if ($existing) {
                $presensiId = (int)$existing['id'];
                $db->update('presensi_kelas', [
                    'kelas' => $kelas,
                    'mata_pelajaran' => $mapel,
                    'jam_ke' => $jamKe,
                    'catatan' => $catatanUmum,
                    'waktu_presensi' => date('H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$presensiId]);
            } else {
                $presensiId = $db->insert('presensi_kelas', [
                    'guru_id' => $guruId,
                    'kelas' => $kelas,
                    'mata_pelajaran' => $mapel,
                    'tanggal' => $tanggal,
                    'jam_ke' => $jamKe,
                    'role_mengajar' => 'PIC',
                    'jp' => 2,
                    'tipe_presensi' => $tipePresensi,
                    'catatan' => $catatanUmum,
                    'created_by' => $userId,
                    'waktu_presensi' => date('H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Hapus detail lama dan masukkan detail baru
            $pdo->prepare("DELETE FROM presensi_kelas_detail WHERE presensi_kelas_id = ?")->execute([$presensiId]);

            // Ambil nama-nama siswa untuk pencatatan di detail
            $siswaIds = array_keys($statuses);
            $namesMap = [];
            if (!empty($siswaIds)) {
                $placeholders = implode(',', array_fill(0, count($siswaIds), '?'));
                $rows = $db->findAll("SELECT id, COALESCE(nama_lengkap, nama) as nama FROM siswa WHERE id IN ($placeholders)", $siswaIds);
                foreach ($rows as $r) {
                    $namesMap[$r['id']] = $r['nama'];
                }
            }

            foreach ($statuses as $sId => $stat) {
                $statVal = in_array($stat, ['H', 'S', 'I', 'A']) ? $stat : 'H';
                $db->insert('presensi_kelas_detail', [
                    'presensi_kelas_id' => $presensiId,
                    'siswa_id' => (int)$sId,
                    'nama_siswa' => $namesMap[$sId] ?? "Siswa #{$sId}",
                    'status' => $statVal,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $redirectUrl = url('mobile/jurnal?kelas=' . urlencode($kelas) . '&mapel=' . urlencode($mapel) . '&kehadiran=' . urlencode($summary));

            echo json_encode([
                'success' => true,
                'message' => 'Presensi kelas ' . $kelas . ' berhasil disimpan!',
                'summary' => $summary,
                'redirect_url' => $redirectUrl
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Murid (Daftar Siswa Riil dari SK Penugasan Guru)
     */
    public static function murid(): void
    {
        $teacher = self::getLoggedInTeacher();
        $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        
        $classesList = array_keys($classesMap);
        $filterClasses = array_merge(['Semua'], $classesList);

        $selectedFilter = trim($_GET['kelas'] ?? 'Semua');
        if (!in_array($selectedFilter, $filterClasses)) {
            $selectedFilter = 'Semua';
        }

        $db = Database::getInstance();
        $studentList = [];

        // Buat klausa pencarian untuk seluruh kelas yang diampu guru
        if (!empty($classesList)) {
            $clauses = [];
            $params = [];
            foreach ($classesList as $cName) {
                $clean = trim(preg_replace('/^kelas\s+/i', '', $cName));
                $clauses[] = "(s.kelas = ? OR s.kelas = ? OR REPLACE(s.kelas, 'Kelas ', '') = ? OR ? = REPLACE(s.kelas, 'Kelas ', ''))";
                $params[] = $cName;
                $params[] = $clean;
                $params[] = $clean;
                $params[] = $clean;
            }
            $whereKelas = '(' . implode(' OR ', $clauses) . ')';

            $sql = "
                SELECT s.id, s.nisn, s.nis, COALESCE(s.nama_lengkap, s.nama) as name, s.nama_lengkap,
                       s.jenis_kelamin as gender, s.kelas, s.no_hp, s.telepon, s.foto
                FROM siswa s
                WHERE {$whereKelas}
                  AND (s.is_active = 1 OR s.is_active IS NULL OR s.status_siswa = 'Aktif')
                ORDER BY s.kelas ASC, COALESCE(s.nama_lengkap, s.nama) ASC
            ";

            try {
                $rows = $db->findAll($sql, $params);
                foreach ($rows as $r) {
                    $phone = !empty($r['no_hp']) ? preg_replace('/[^0-9]/', '', $r['no_hp']) : (!empty($r['telepon']) ? preg_replace('/[^0-9]/', '', $r['telepon']) : '');
                    if ($phone && str_starts_with($phone, '0')) {
                        $phone = '62' . substr($phone, 1);
                    }
                    $studentList[] = [
                        'id' => (int)$r['id'],
                        'name' => $r['name'],
                        'nisn' => $r['nisn'] ?: ($r['nis'] ?: '-'),
                        'class' => str_starts_with($r['kelas'], 'Kelas ') ? $r['kelas'] : 'Kelas ' . $r['kelas'],
                        'gender' => $r['gender'] ?: 'L',
                        'presence' => '100%',
                        'phone' => $phone ?: '628123456789',
                        'points' => 100
                    ];
                }
            } catch (Throwable $e) {}
        }

        self::render('murid', [
            'pageTitle' => 'Daftar Siswa & Profil',
            'activeTab' => 'murid',
            'teacher' => $teacher,
            'classes' => $filterClasses,
            'selectedFilter' => $selectedFilter,
            'totalStudents' => count($studentList),
            'studentList' => $studentList
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
