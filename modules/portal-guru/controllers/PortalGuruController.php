<?php
/**
 * Portal Guru - Mobile & PWA Controller
 * Handles teacher mobile dashboard, geolocation attendance, teaching journal, and classroom management.
 */

require_once __DIR__ . '/../models/PortalGuruModel.php';

class PortalGuruController
{
    private static ?array $cachedTeacher = null;
    private static ?string $cachedTeacherKey = null;

    /**
     * Flush and release PHP session file lock early for read-only requests
     * so concurrent mobile/API requests never serialize behind flock().
     */
    private static function releaseSessionLock(): void
    {
        Auth::avatar();
        if (class_exists('CSRF') && method_exists('CSRF', 'token')) {
            CSRF::token();
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

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
        $cacheKey = $previewId > 0 ? ('preview_' . $previewId) : ('user_' . (int)($user['id'] ?? 0));
        if (self::$cachedTeacher !== null && self::$cachedTeacherKey === $cacheKey) {
            return self::$cachedTeacher;
        }

        if ($previewId > 0) {
            $_SESSION['portal_guru_preview_id'] = $previewId;
            $previewPegawai = $db->find("SELECT * FROM pegawai WHERE id = ? LIMIT 1", [$previewId]);
            if ($previewPegawai) {
                self::$cachedTeacherKey = $cacheKey;
                self::$cachedTeacher = self::formatTeacherArray($previewPegawai, $user);
                return self::$cachedTeacher;
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

        $uid = (int)($user['id'] ?? 0);
        $pegawai = null;

        // Fast primary-key lookup when pegawai_id is cached in session for this user
        if (
            !empty($_SESSION['portal_guru_pegawai_id']) &&
            (int)($_SESSION['portal_guru_cache_uid'] ?? 0) === $uid
        ) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ? LIMIT 1", [(int)$_SESSION['portal_guru_pegawai_id']]);
        }

        if (!$pegawai && !empty($user['email'])) {
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
        if (!$pegawai && (Auth::hasRole('super_admin') || $uid === 1)) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = 17 LIMIT 1");
        }

        if ($pegawai) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['portal_guru_pegawai_id'] = (int)$pegawai['id'];
                $_SESSION['portal_guru_cache_uid'] = $uid;
            }
            self::$cachedTeacherKey = $cacheKey;
            self::$cachedTeacher = self::formatTeacherArray($pegawai, $user);
            return self::$cachedTeacher;
        }

        $fullName = !empty($user['full_name']) ? $user['full_name'] : 'Guru';
        $role = Auth::hasRole('super_admin') ? 'Super Administrator' : 'Guru Pengajar';

        self::$cachedTeacherKey = $cacheKey;
        self::$cachedTeacher = [
            'id' => null,
            'pegawai_id' => null,
            'user_id' => $uid,
            'name' => $fullName,
            'nip' => '-',
            'role' => $role,
            'status' => 'Pegawai Aktif',
            'email' => $user['email'] ?? '-',
            'phone' => $user['phone'] ?? '-',
            'avatar' => Auth::avatar(),
            'teachingHours' => '24 Jam / Minggu',
            'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil'
        ];
        return self::$cachedTeacher;
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
            'avatar' => $foto ? Auth::resolvePhotoUrl($foto) : null,
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
            $activeGrupIds = [];
            $activeGrups = $db->findAll("SELECT id FROM jadwal_grup WHERE is_active = 1");
            foreach ($activeGrups as $ag) {
                $activeGrupIds[] = (int)$ag['id'];
            }
            if (empty($activeGrupIds)) {
                $fallbackGrup = $db->find("SELECT id FROM jadwal_grup ORDER BY id DESC LIMIT 1");
                if ($fallbackGrup) {
                    $activeGrupIds[] = (int)$fallbackGrup['id'];
                }
            }

            $jParams = [$pegawaiId ?: 0, '%' . $guruName . '%'];
            $grupClause = '';
            if (!empty($activeGrupIds)) {
                $ph = implode(',', array_fill(0, count($activeGrupIds), '?'));
                $grupClause = " AND jp.grup_id IN ({$ph})";
                $jParams = array_merge($jParams, $activeGrupIds);
            }

            $jadwalRows = $db->findAll("
                SELECT jp.nama_kelas, jp.mata_pelajaran, jp.hari, jp.jam_ke, jp.jam_mulai, jp.jam_selesai, jp.ruangan
                FROM jadwal_pelajaran jp
                WHERE (jp.pegawai_id = ? OR jp.nama_guru LIKE ?)
                  {$grupClause}
                ORDER BY FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jp.jam_ke ASC
            ", $jParams);

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

        // Hitung jumlah siswa riil & wali kelas secara batch (hindari N+1 query loop)
        $siswaCountMap = [];
        try {
            $countRows = $db->findAll("
                SELECT LOWER(TRIM(REPLACE(kelas, 'Kelas ', ''))) AS clean_k, COUNT(*) AS cnt
                FROM siswa
                WHERE kelas IS NOT NULL AND kelas != ''
                  AND (is_active = 1 OR is_active IS NULL OR status_siswa = 'Aktif')
                GROUP BY LOWER(TRIM(REPLACE(kelas, 'Kelas ', '')))
            ");
            foreach ($countRows as $cr) {
                $siswaCountMap[$cr['clean_k']] = (int)$cr['cnt'];
            }
        } catch (Throwable $e) {}

        $waliMap = [];
        try {
            $waliAllRows = $db->findAll("
                SELECT LOWER(TRIM(REPLACE(pp.wali_kelas_nama, 'Kelas ', ''))) AS clean_w,
                       COALESCE(p.nama, pp.wali_kelas_nama) AS wali_nama
                FROM pegawai_penugasan pp
                JOIN penugasan_grup pg ON pp.grup_id = pg.id
                JOIN pegawai p ON pp.pegawai_id = p.id
                WHERE pg.is_active = 1 AND pp.wali_kelas_nama IS NOT NULL AND pp.wali_kelas_nama != ''
            ");
            foreach ($waliAllRows as $wr) {
                $waliMap[$wr['clean_w']] = $wr['wali_nama'];
            }
        } catch (Throwable $e) {}

        $colorPalette = ['emerald', 'blue', 'indigo', 'purple', 'teal', 'sky', 'amber'];
        $cIndex = 0;

        foreach ($classes as $cName => &$info) {
            $cleanKey = strtolower(trim(preg_replace('/^kelas\s+/i', '', $cName)));
            $info['totalStudents'] = $siswaCountMap[$cleanKey] ?? 0;

            if (!$info['is_wali']) {
                $info['homeroom'] = $waliMap[$cleanKey] ?? 'Wali Kelas BIP';
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

        // Ambil grup jadwal aktif terlebih dahulu (hindari correlated subquery berulang)
        $activeGrupIds = [];
        try {
            $activeGrups = $db->findAll("SELECT id FROM jadwal_grup WHERE is_active = 1");
            foreach ($activeGrups as $ag) {
                $activeGrupIds[] = (int)$ag['id'];
            }
            if (empty($activeGrupIds)) {
                $fallbackGrup = $db->find("SELECT id FROM jadwal_grup ORDER BY id DESC LIMIT 1");
                if ($fallbackGrup) {
                    $activeGrupIds[] = (int)$fallbackGrup['id'];
                }
            }
        } catch (Throwable $e) {}

        $params = [];
        $where = "1=1";

        if (!empty($activeGrupIds)) {
            $ph = implode(',', array_fill(0, count($activeGrupIds), '?'));
            $where .= " AND jp.grup_id IN ({$ph})";
            $params = array_merge($params, $activeGrupIds);
        }

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
            SELECT jp.*, COALESCE(jp.jumlah_jp, 2) as jp_alokasi
            FROM jadwal_pelajaran jp
            WHERE {$where}
            ORDER BY FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jp.jam_ke ASC, jp.jam_mulai ASC
        ";

        try {
            $rows = $db->findAll($sql, $params);
        } catch (Throwable $e) {
            $rows = [];
        }

        // Fetch jurnal hari ini secara terpisah agar indeks (tanggal, guru_id) terpakai maksimal (tanpa OR/COLLATE join)
        $todayJurnalByJadwal = [];
        $todayJurnalByKey = [];
        try {
            $todayDate = date('Y-m-d');
            $jurnalRows = $db->findAll(
                "SELECT id, jadwal_id, guru_id, kelas, mata_pelajaran, jam_ke, kode_jurnal, status, materi_pokok, uraian_kegiatan, catatan_kelas, jumlah_jp, kehadiran_summary
                 FROM jurnal_mengajar
                 WHERE tanggal = ?",
                [$todayDate]
            );
            foreach ($jurnalRows as $jr) {
                if (!empty($jr['jadwal_id'])) {
                    $todayJurnalByJadwal[(int)$jr['jadwal_id']] = $jr;
                }
                $kKey = (int)$jr['guru_id'] . '|' . strtolower(trim((string)$jr['kelas'])) . '|' . strtolower(trim((string)$jr['mata_pelajaran'])) . '|' . trim((string)$jr['jam_ke']);
                $todayJurnalByKey[$kKey] = $jr;
            }
        } catch (Throwable $e) {}

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

            $jurnalMatch = $todayJurnalByJadwal[(int)$r['id']] ?? null;
            if (!$jurnalMatch) {
                $lookupKey = (int)($r['pegawai_id'] ?? 0) . '|' . strtolower(trim((string)$r['nama_kelas'])) . '|' . strtolower(trim((string)$r['mata_pelajaran'])) . '|' . trim((string)$r['jam_ke']);
                $jurnalMatch = $todayJurnalByKey[$lookupKey] ?? null;
            }

            $hasJurnal = !empty($jurnalMatch['id']);

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
                'jurnal_id' => $jurnalMatch['id'] ?? null,
                'jurnal_kode' => $jurnalMatch['kode_jurnal'] ?? null,
                'jurnal_status' => $jurnalMatch['status'] ?? null,
                'jurnal_materi' => $jurnalMatch['materi_pokok'] ?? null,
                'jurnal_kegiatan' => $jurnalMatch['uraian_kegiatan'] ?? null,
                'jurnal_catatan' => $jurnalMatch['catatan_kelas'] ?? null,
                'jurnal_jp' => $jurnalMatch['jumlah_jp'] ?? null,
                'jurnal_kehadiran' => $jurnalMatch['kehadiran_summary'] ?? null,
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
        self::releaseSessionLock();

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

        $allTeacherSchedules = self::getTeacherSchedules($teacher['pegawai_id'], $teacher['name']);
        $todaySchedule = array_values(array_filter($allTeacherSchedules, static fn($s) => ($s['hari'] ?? '') === $todayName));
        
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

        $pegawaiId = (int)($_SESSION['user']['pegawai_id'] ?? $_GET['pegawai_id'] ?? 0);
        $namaLengkapSession = $_SESSION['user']['nama_lengkap'] ?? '';
        self::releaseSessionLock();

        if (file_exists(MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php')) {
            require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
            if ($pegawaiId <= 0 && !empty($namaLengkapSession)) {
                try {
                    $db = Database::getInstance()->getConnection();
                    $st = $db->prepare("SELECT id FROM pegawai WHERE nama_lengkap = ? LIMIT 1");
                    $st->execute([$namaLengkapSession]);
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
        self::releaseSessionLock();
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

        // Ambil seluruh jadwal guru sekali saja lalu filter jadwal hari ini di PHP
        $allSchedules = self::getTeacherSchedules($teacher['pegawai_id'], $teacher['name']);
        $todaySchedule = array_values(array_filter($allSchedules, static fn($s) => ($s['hari'] ?? '') === $todayName));

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
        $todayDate = date('Y-m-d');

        // Aturan Keras: Jurnal hanya dapat diisi pada hari KBM berlangsung (Hari Ini). Hari esok / lampau tidak bisa diisi.
        if ($tanggal !== $todayDate) {
            echo json_encode([
                'success' => false,
                'message' => 'Jurnal KBM hanya dapat diisi pada hari jadwal berlangsung (Hari Ini: ' . $todayName . ', ' . date('d/m/Y') . '). Pengisian untuk hari esok atau hari lampau tidak diizinkan.'
            ]);
            exit;
        }

        if ($jadwalId) {
            $checkJadwal = $db->find("SELECT hari, nama_kelas, mata_pelajaran FROM jadwal_pelajaran WHERE id = ? LIMIT 1", [$jadwalId]);
            if ($checkJadwal && strtolower($checkJadwal['hari']) !== strtolower($todayName)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Jadwal KBM ' . $checkJadwal['nama_kelas'] . ' adalah hari ' . $checkJadwal['hari'] . '. Jurnal hanya dapat diisi pada hari jadwal tersebut berlangsung (Hari Ini: ' . $todayName . ').'
                ]);
                exit;
            }
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
        self::releaseSessionLock();
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
        self::releaseSessionLock();
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
        self::releaseSessionLock();
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
        self::releaseSessionLock();
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

            $pdo->beginTransaction();

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

            // Hapus detail lama dan masukkan detail baru dalam 1 batch INSERT
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

            if (!empty($statuses)) {
                $nowTs = date('Y-m-d H:i:s');
                $valueGroups = [];
                $insertParams = [];
                foreach ($statuses as $sId => $stat) {
                    $statVal = in_array($stat, ['H', 'S', 'I', 'A'], true) ? $stat : 'H';
                    $valueGroups[] = '(?, ?, ?, ?, ?)';
                    $insertParams[] = $presensiId;
                    $insertParams[] = (int)$sId;
                    $insertParams[] = $namesMap[$sId] ?? "Siswa #{$sId}";
                    $insertParams[] = $statVal;
                    $insertParams[] = $nowTs;
                }
                $stmtBatch = $pdo->prepare(
                    "INSERT INTO presensi_kelas_detail (presensi_kelas_id, siswa_id, nama_siswa, status, created_at) VALUES " . implode(', ', $valueGroups)
                );
                $stmtBatch->execute($insertParams);
            }

            $pdo->commit();

            $redirectUrl = url('mobile/jurnal?kelas=' . urlencode($kelas) . '&mapel=' . urlencode($mapel) . '&kehadiran=' . urlencode($summary));

            echo json_encode([
                'success' => true,
                'message' => 'Presensi kelas ' . $kelas . ' berhasil disimpan!',
                'summary' => $summary,
                'redirect_url' => $redirectUrl
            ]);
            exit;
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
        self::releaseSessionLock();
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
        self::releaseSessionLock();

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
        self::releaseSessionLock();
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
        self::releaseSessionLock();
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
        self::releaseSessionLock();
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
        self::releaseSessionLock();
        $leavesData = PortalGuruModel::getTeacherLeaves();

        self::render('cuti', [
            'pageTitle' => 'Pengajuan Cuti Guru',
            'activeTab' => 'cuti',
            'quotas' => $leavesData['quotas'],
            'records' => $leavesData['records']
        ]);
    }
}
