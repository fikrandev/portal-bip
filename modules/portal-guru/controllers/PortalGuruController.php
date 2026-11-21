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
        $teacher = $data['teacher'] ?? self::getLoggedInTeacher();
        $data['teacher'] = $teacher;
        $data['isSupport'] = !empty($teacher['is_support']);

        extract($data);
        $currentRoute = $data['activeTab'] ?? 'beranda';

        ob_start();
        include MODULES_PATH . '/portal-guru/views/' . $view . '.php';
        $content = ob_get_clean();

        include MODULES_PATH . '/portal-guru/views/layout.php';
    }

    /**
     * Bersihkan memory cache profil guru
     */
    public static function clearCachedTeacher(): void
    {
        self::$cachedTeacher = null;
        self::$cachedTeacherKey = null;
    }

    /**
     * Dapatkan data profil guru yang sedang aktif login dari sesi
     */
    public static function getLoggedInTeacher(): array
    {
        $user = Auth::user();
        $db = Database::getInstance();

        // 1. Dukungan Switch / Preview Guru via URL (?guru_id=17) HANYA jika ada GET param eksplisit
        $previewId = !empty($_GET['guru_id']) ? (int) $_GET['guru_id'] : 0;
        $uid = (int) ($user['id'] ?? 0);
        $cacheKey = $previewId > 0 ? ('preview_' . $previewId) : ('user_' . $uid);
        if (self::$cachedTeacher !== null && self::$cachedTeacherKey === $cacheKey) {
            return self::$cachedTeacher;
        }

        if ($previewId > 0) {
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
                'gender' => 'P',
                'email' => '',
                'phone' => '',
                'avatar' => null,
                'teachingHours' => '0 Jam / Minggu',
                'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil'
            ];
        }

        $pegawai = null;

        // Fast primary-key lookup when pegawai_id is cached in session for this user
        if (
            !empty($_SESSION['portal_guru_pegawai_id']) &&
            (int) ($_SESSION['portal_guru_cache_uid'] ?? 0) === $uid
        ) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ? LIMIT 1", [(int) $_SESSION['portal_guru_pegawai_id']]);
        }

        if (!$pegawai && !empty($user['email'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE email = ? LIMIT 1", [$user['email']]);
        }
        if (!$pegawai && !empty($user['full_name'])) {
            $cleanFullName = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $user['full_name']));
            $pegawai = $db->find("
                SELECT * FROM pegawai 
                WHERE nama = ? 
                   OR nama LIKE ? 
                   OR REPLACE(REPLACE(nama, ',', ''), '.', '') LIKE ?
                LIMIT 1
            ", [$user['full_name'], '%' . $user['full_name'] . '%', '%' . $cleanFullName . '%']);
        }
        if (!$pegawai && !empty($user['phone'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE no_wa = ? LIMIT 1", [$user['phone']]);
        }
        if (!$pegawai && !empty($user['username'])) {
            $cleanUser = trim(preg_replace('/[^a-zA-Z0-9\s]/', ' ', str_replace(['.', '_', '-'], ' ', $user['username'])));
            $pegawai = $db->find("
                SELECT * FROM pegawai 
                WHERE nama LIKE ? 
                   OR email = ? 
                   OR REPLACE(REPLACE(nama, ',', ''), '.', '') LIKE ?
                LIMIT 1
            ", ['%' . $cleanUser . '%', $user['username'], '%' . $cleanUser . '%']);
        }

        // Jika user adalah super admin dan tidak terhubung ke pegawai mana pun, boleh pakai guru 17 sebagai demo
        if (!$pegawai && (Auth::hasRole('super_admin') || $uid === 1)) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = 17 LIMIT 1");
        }

        if ($pegawai) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['portal_guru_pegawai_id'] = (int) $pegawai['id'];
                $_SESSION['portal_guru_cache_uid'] = $uid;
            }
            self::$cachedTeacherKey = $cacheKey;
            self::$cachedTeacher = self::formatTeacherArray($pegawai, $user);
            return self::$cachedTeacher;
        }

        $fullName = !empty($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Guru');
        $role = Auth::hasRole('super_admin') ? 'Super Administrator' : 'Guru Pengajar';

        self::$cachedTeacherKey = $cacheKey;
        self::$cachedTeacher = [
            'id' => null,
            'pegawai_id' => null,
            'user_id' => $uid,
            'name' => $fullName,
            'nip' => '-',
            'role' => $role,
            'jenis_pegawai' => 'Guru',
            'is_support' => false,
            'status' => 'Pegawai Aktif',
            'gender' => 'P',
            'email' => $user['email'] ?? '-',
            'phone' => $user['phone'] ?? '-',
            'avatar' => Auth::avatar(),
            'teachingHours' => '0 Jam / Minggu',
            'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil'
        ];
        return self::$cachedTeacher;
    }

    /**
     * Helper format data array guru / pegawai
     */
    private static function formatTeacherArray(array $pegawai, ?array $user = null): array
    {
        $fullName = !empty($pegawai['nama']) ? $pegawai['nama'] : ($user['full_name'] ?? 'Guru');
        $role = !empty($pegawai['jabatan']) ? $pegawai['jabatan'] : 'Tenaga Pendidik';
        $nip = !empty($pegawai['niy']) ? $pegawai['niy'] : (!empty($pegawai['nik']) ? $pegawai['nik'] : '-');
        $phone = !empty($pegawai['no_wa']) ? $pegawai['no_wa'] : ($user['phone'] ?? '-');
        $status = !empty($pegawai['status_pegawai']) ? $pegawai['status_pegawai'] : 'Pegawai Aktif';
        $foto = !empty($pegawai['foto']) ? $pegawai['foto'] : ($user['avatar'] ?? null);
        $pegawaiId = (int) $pegawai['id'];
        $rawJk = strtoupper(trim((string) ($pegawai['jenis_kelamin'] ?? 'P')));
        $gender = ($rawJk === 'L' || str_starts_with($rawJk, 'LAKI')) ? 'L' : 'P';

        $penugasanKategori = null;
        if ($pegawaiId > 0) {
            try {
                $db = Database::getInstance();
                $penRow = $db->find("
                    SELECT mj.nama, mj.kategori
                    FROM pegawai_penugasan pp
                    INNER JOIN master_jabatan mj ON mj.id = pp.jabatan_id
                    LEFT JOIN penugasan_grup pg ON pg.id = pp.grup_id
                    WHERE pp.pegawai_id = ? AND pp.status = 'Aktif'
                    ORDER BY COALESCE(pg.is_active, 0) DESC, pp.id DESC
                    LIMIT 1
                ", [$pegawaiId]);
                if ($penRow && !empty($penRow['nama'])) {
                    $role = $penRow['nama'];
                    $penugasanKategori = strtolower(trim((string)($penRow['kategori'] ?? '')));
                }
            } catch (Throwable $e) {}
        }

        $jenisPegawai = trim((string) ($pegawai['jenis_pegawai'] ?? ''));

        // Deteksi apakah pegawai ini Support System (utamakan kategori jabatan di penugasan)
        $isSupport = false;
        if ($penugasanKategori === 'support_system') {
            $isSupport = true;
        } elseif ($penugasanKategori === 'guru') {
            $isSupport = false;
        } elseif (strcasecmp($jenisPegawai, 'Support System') === 0 || stripos($jenisPegawai, 'support') !== false) {
            $isSupport = true;
        } elseif (strcasecmp($jenisPegawai, 'Guru') === 0 || stripos($jenisPegawai, 'guru') !== false) {
            $isSupport = false;
        } else {
            // Jika kolom jenis_pegawai di database masih kosong, cek jabatan
            $isSupport = (
                stripos($role, 'IT') !== false ||
                stripos($role, 'Media') !== false ||
                stripos($role, 'Sarpras') !== false ||
                stripos($role, 'Tata Usaha') !== false ||
                stripos($role, 'Tendik') !== false ||
                stripos($role, 'Staff') !== false ||
                stripos($role, 'Staf') !== false ||
                stripos($role, 'Keamanan') !== false ||
                stripos($role, 'Satpam') !== false ||
                stripos($role, 'Kebersihan') !== false ||
                stripos($role, 'Cleaning') !== false
            );
        }

        return [
            'id' => $pegawaiId,
            'pegawai_id' => $pegawaiId,
            'user_id' => (int) ($user['id'] ?? 0),
            'name' => $fullName,
            'nip' => $nip,
            'role' => $role,
            'jenis_pegawai' => $isSupport ? 'Support System' : 'Guru',
            'is_support' => $isSupport,
            'status' => $status,
            'gender' => $gender,
            'email' => $pegawai['email'] ?: ($user['email'] ?? '-'),
            'phone' => $phone,
            'avatar' => $foto ? Auth::resolvePhotoUrl($foto) : null,
            'teachingHours' => $isSupport ? 'Non-KBM (Staf)' : '24 Jam / Minggu',
            'academicYear' => defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil'
        ];
    }

    /**
     * Ambil status Ibadah Harian guru HARI INI dari tabel mutabaah_ibadah_guru.
     * Jika belum ada catatan untuk tanggal hari ini, semua default TIDAK tercentang.
     */
    public static function getTodayIbadahState(?int $guruId, string $gender = 'P'): array
    {
        $todayDate = date('Y-m-d');
        $normGender = (strtoupper(trim($gender)) === 'L') ? 'L' : 'P';
        $defaultLoc = ($normGender === 'P') ? 'rumah' : 'masjid';

        $state = [
            'date' => $todayDate,
            'guru_id' => (int) $guruId,
            'gender' => $normGender,
            'sholat' => [
                'subuh' => ['checked' => false, 'loc' => $defaultLoc],
                'dzuhur' => ['checked' => false, 'loc' => $defaultLoc],
                'ashar' => ['checked' => false, 'loc' => $defaultLoc],
                'maghrib' => ['checked' => false, 'loc' => $defaultLoc],
                'isya' => ['checked' => false, 'loc' => $defaultLoc],
            ],
            'tilawah' => [
                'checked' => false,
                'text' => '',
                'duration_seconds' => 0,
            ],
            'dzikir' => [
                'istighfar' => false,
                'sholawat' => false,
            ],
            'tadabbur' => [
                'checked' => false,
                'text' => '',
            ],
        ];

        if (!$guruId || $guruId <= 0) {
            return $state;
        }

        try {
            $db = Database::getInstance();
            $row = $db->find(
                "SELECT * FROM mutabaah_ibadah_guru WHERE guru_id = ? AND tanggal = ? LIMIT 1",
                [$guruId, $todayDate]
            );
            if ($row) {
                $rowGender = $normGender;
                foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
                    $chk = !empty($row['sholat_' . $w]);
                    $locVal = $row['sholat_' . $w . '_loc'] ?? $defaultLoc;
                    if ($rowGender === 'P') {
                        $locVal = 'rumah';
                    } elseif ($locVal !== 'masjid' && $locVal !== 'rumah') {
                        $locVal = $defaultLoc;
                    }
                    $state['sholat'][$w] = [
                        'checked' => $chk,
                        'loc' => $locVal,
                    ];
                }
                $state['tilawah'] = [
                    'checked' => !empty($row['tilawah_checked']),
                    'text' => (string) ($row['tilawah_surah'] ?? ''),
                    'duration_seconds' => (int) ($row['tilawah_duration_seconds'] ?? 0),
                ];
                $state['dzikir'] = [
                    'istighfar' => !empty($row['dzikir_istighfar']),
                    'sholawat' => !empty($row['dzikir_sholawat']),
                ];
                $state['tadabbur'] = [
                    'checked' => !empty($row['tadabbur_checked']),
                    'text' => (string) ($row['tadabbur_notes'] ?? ''),
                ];
            }
        } catch (Throwable $e) {
            error_log('getTodayIbadahState error: ' . $e->getMessage());
        }

        return $state;
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
            } catch (Throwable $e) {
            }

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
                    if (empty($cName))
                        continue;
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
                    $classes[$cName]['total_jp'] += (int) ($m['jumlah_jp'] ?? 2);
                }
            } catch (Throwable $e) {
            }
        }

        // 3. Dari Jadwal Pelajaran (jadwal_pelajaran)
        try {
            $activeGrupIds = [];
            $activeGrups = $db->findAll("SELECT id FROM jadwal_grup WHERE is_active = 1");
            foreach ($activeGrups as $ag) {
                $activeGrupIds[] = (int) $ag['id'];
            }
            if (empty($activeGrupIds)) {
                $fallbackGrup = $db->find("SELECT id FROM jadwal_grup ORDER BY id DESC LIMIT 1");
                if ($fallbackGrup) {
                    $activeGrupIds[] = (int) $fallbackGrup['id'];
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
                if (empty($cName))
                    continue;
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
        } catch (Throwable $e) {
        }

        // Jika tidak ada kelas yang diampu sama sekali, kembalikan array kosong (jangan fallback dummy)
        if (empty($classes)) {
            return [];
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
                $siswaCountMap[$cr['clean_k']] = (int) $cr['cnt'];
            }
        } catch (Throwable $e) {
        }

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
        } catch (Throwable $e) {
        }

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
                $activeGrupIds[] = (int) $ag['id'];
            }
            if (empty($activeGrupIds)) {
                $fallbackGrup = $db->find("SELECT id FROM jadwal_grup ORDER BY id DESC LIMIT 1");
                if ($fallbackGrup) {
                    $activeGrupIds[] = (int) $fallbackGrup['id'];
                }
            }
        } catch (Throwable $e) {
        }

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
                    $todayJurnalByJadwal[(int) $jr['jadwal_id']] = $jr;
                }
                $kKey = (int) $jr['guru_id'] . '|' . strtolower(trim((string) $jr['kelas'])) . '|' . strtolower(trim((string) $jr['mata_pelajaran'])) . '|' . trim((string) $jr['jam_ke']);
                $todayJurnalByKey[$kKey] = $jr;
            }
        } catch (Throwable $e) {
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

            $jurnalMatch = $todayJurnalByJadwal[(int) $r['id']] ?? null;
            if (!$jurnalMatch) {
                $lookupKey = (int) ($r['pegawai_id'] ?? 0) . '|' . strtolower(trim((string) $r['nama_kelas'])) . '|' . strtolower(trim((string) $r['mata_pelajaran'])) . '|' . trim((string) $r['jam_ke']);
                $jurnalMatch = $todayJurnalByKey[$lookupKey] ?? null;
            }

            $hasJurnal = !empty($jurnalMatch['id']);

            $schedules[] = [
                'id' => (int) $r['id'],
                'hari' => $r['hari'],
                'jam_ke' => $r['jam_ke'],
                'jumlah_jp' => (int) ($r['jp_alokasi'] ?: 2),
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
        $isSupport = !empty($teacher['is_support']);

        $allTeacherSchedules = [];
        $todaySchedule = [];
        $classesMap = [];
        $totalClasses = 0;
        $todayJp = 0;
        $todayFilledJurnal = 0;
        $activeTasks = 0;
        $pendingGrades = 0;
        $classReports = 0;

        if (!$isSupport) {
            $allTeacherSchedules = self::getTeacherSchedules($teacher['pegawai_id'], $teacher['name']);
            $todaySchedule = array_values(array_filter($allTeacherSchedules, static fn($s) => ($s['hari'] ?? '') === $todayName));

            $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
            $totalClasses = count($classesMap);

            $todayJp = array_sum(array_column($todaySchedule, 'jumlah_jp'));
            $todayFilledJurnal = count(array_filter($todaySchedule, fn($s) => $s['has_jurnal']));
        }

        $guruIdForIbadah = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $teacherGender = $teacher['gender'] ?? 'P';
        $todayIbadahState = self::getTodayIbadahState($guruIdForIbadah, $teacherGender);

        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $todayAttendance = AbsenPegawaiModel::getTodayAttendance($guruIdForIbadah);
        $globalConfig = AbsenPegawaiModel::getPengaturanGlobal();
        $efektif = AbsenPegawaiModel::getLokasiEfektifPegawai($guruIdForIbadah);
        $jamPulangMulai = $efektif['jam_pulang_mulai'] ?? ($globalConfig['jam_pulang_mulai'] ?? '15:00:00');
        $jamMasukBatas = $efektif['jam_masuk_batas'] ?? ($globalConfig['jam_masuk_batas'] ?? '07:30:00');

        // ── Kalkulasi Metrik Dinamis Guru yang Login (Khusus Guru / KBM) ──
        $db = Database::getInstance();

        if (!$isSupport) {
            try {
                $pegId = (int) ($teacher['pegawai_id'] ?: 0);
                $gName = trim($teacher['name'] ?? '');

                // 1. Tugas Aktif: Wadah penilaian / tugas yang aktif milik guru atau kelas diampu
                $taskRow = $db->find("
                    SELECT COUNT(*) as c
                    FROM nilai_group
                    WHERE deleted_at IS NULL AND is_active = 1
                      AND (guru_id = ? OR (guru_id = 0 AND ? > 0))
                ", [$pegId, $pegId]);
                $activeTasks = (int) ($taskRow['c'] ?? 0);

                // 2. Belum Dinilai: Asesmen siswa dalam tugas aktif yang nilainya masih kosong
                if ($pegId > 0) {
                    $pendingRow = $db->find("
                        SELECT COUNT(nd.id) as c
                        FROM nilai_detail nd
                        JOIN nilai_group ng ON nd.group_id = ng.id
                        WHERE ng.deleted_at IS NULL AND ng.is_active = 1
                          AND (ng.guru_id = ? OR (ng.guru_id = 0 AND ? > 0))
                          AND (nd.nilai IS NULL OR nd.nilai = 0)
                    ", [$pegId, $pegId]);
                    $pendingGrades = (int) ($pendingRow['c'] ?? 0);
                }

                // 3. Laporan Kelas: Total Presensi Kelas + Total Jurnal Mengajar yang dibuat guru
                $presensiRow = $db->find("
                    SELECT COUNT(*) as c
                    FROM presensi_kelas
                    WHERE guru_id = ? OR created_by = ?
                ", [$pegId, $pegId]);
                $presensiCount = (int) ($presensiRow['c'] ?? 0);

                $jurnalRow = $db->find("
                    SELECT COUNT(*) as c
                    FROM jurnal_mengajar
                    WHERE guru_id = ? OR nama_guru LIKE ?
                ", [$pegId, '%' . $gName . '%']);
                $jurnalCount = (int) ($jurnalRow['c'] ?? 0);

                $classReports = $presensiCount + $jurnalCount;
            } catch (Throwable $e) {
                error_log('PortalGuruController::beranda metrics calculation error: ' . $e->getMessage());
            }
        }

        self::render('beranda', [
            'pageTitle' => $isSupport ? 'Portal Support System' : 'Beranda Guru',
            'activeTab' => 'beranda',
            'isSupport' => $isSupport,
            'teacher' => $teacher,
            'teacherName' => $teacher['name'],
            'teacherRole' => $teacher['role'],
            'teacherAvatar' => $teacher['avatar'],
            'teacherNip' => $teacher['nip'],
            'teacherGender' => $teacherGender,
            'teacherPegawaiId' => $guruIdForIbadah,
            'todayIbadahState' => $todayIbadahState,
            'todayAttendance' => $todayAttendance,
            'globalConfig' => $globalConfig,
            'efektif' => $efektif,
            'jamPulangMulai' => $jamPulangMulai,
            'jamMasukBatas' => $jamMasukBatas,
            'totalClasses' => $totalClasses,
            'todayName' => $todayName,
            'todayDate' => date('d F Y'),
            'todayJp' => $todayJp,
            'todayFilledJurnal' => $todayFilledJurnal,
            'todaySchedule' => $todaySchedule,
            'activeTasks' => $activeTasks,
            'pendingGrades' => $pendingGrades,
            'classReports' => $classReports,
        ]);
    }

    /**
     * Absen Kehadiran & Geolokasi
     */
    public static function absen(): void
    {
        $teacher = self::getLoggedInTeacher();
        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        self::releaseSessionLock();

        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $efektif = AbsenPegawaiModel::getLokasiEfektifPegawai($pegawaiId);
        $todayAttendance = AbsenPegawaiModel::getTodayAttendance($pegawaiId);
        $recentLogs = AbsenPegawaiModel::getRecentLogs($pegawaiId, 7);
        $globalConfig = AbsenPegawaiModel::getPengaturanGlobal();

        $lokasiSourceLabel = match ($efektif['mode'] ?? 'default') {
            'custom' => 'Custom Pegawai',
            'unit' => 'Custom Unit',
            default => 'Lokasi Semula (Default)'
        };

        self::render('absen', [
            'pageTitle' => 'Presensi Kehadiran & Geolokasi',
            'activeTab' => 'absen',
            'teacher' => $teacher,
            'todayAttendance' => $todayAttendance,
            'recentLogs' => $recentLogs,
            'schoolName' => $efektif['nama_kantor'],
            'schoolRadius' => (int) $efektif['radius_meter'],
            'schoolCoords' => [
                'lat' => (float) $efektif['latitude'],
                'lng' => (float) $efektif['longitude'],
            ],
            'lokasiSourceLabel' => $lokasiSourceLabel,
            'jamMasukMulai' => $efektif['jam_masuk_mulai'] ?? ($globalConfig['jam_masuk_mulai'] ?? '06:00:00'),
            'jamMasukBatas' => $efektif['jam_masuk_batas'] ?? ($globalConfig['jam_masuk_batas'] ?? '07:30:00'),
            'jamPulangMulai' => $efektif['jam_pulang_mulai'] ?? ($globalConfig['jam_pulang_mulai'] ?? '15:00:00'),
        ]);
    }

    /**
     * Rekap Presensi & Keterlambatan Guru
     */
    public static function rekapAbsen(): void
    {
        $teacher = self::getLoggedInTeacher();
        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        self::releaseSessionLock();

        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $globalConfig = AbsenPegawaiModel::getPengaturanGlobal();

        $startDate = trim($_GET['start_date'] ?? ($globalConfig['periode_awal'] ?: date('Y-m-01')));
        $endDate = trim($_GET['end_date'] ?? ($globalConfig['periode_akhir'] ?: date('Y-m-d')));

        $rekap = AbsenPegawaiModel::getRekapAbsenPegawai($pegawaiId, $startDate, $endDate);

        self::render('rekap_absen', [
            'pageTitle' => 'Rekap Presensi & Keterlambatan',
            'activeTab' => 'absen',
            'teacher' => $teacher,
            'rekap' => $rekap,
            'globalConfig' => $globalConfig,
            'startDate' => $rekap['periode']['start'],
            'endDate' => $rekap['periode']['end'],
        ]);
    }

    /**
     * Rekap Jurnal PIC & Monitoring KBM Guru
     */
    public static function rekapJurnal(): void
    {
        $teacher = self::getLoggedInTeacher();
        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        self::releaseSessionLock();

        // Jika pegawai adalah Support System / Staff, arahkan langsung ke jurnal staff tab riwayat
        if (!empty($teacher['is_support'])) {
            header('Location: ' . url('mobile/jurnal?tab=riwayat'));
            exit;
        }

        require_once MODULES_PATH . '/kelola-jurnal-guru/models/JurnalGuruPengaturan.php';
        $jurnalConfig = JurnalGuruPengaturan::getPengaturan();

        $startDate = trim($_GET['start_date'] ?? ($jurnalConfig['periode_awal'] ?: date('Y-m-01')));
        $endDate = trim($_GET['end_date'] ?? ($jurnalConfig['periode_akhir'] ?: date('Y-m-d')));

        $rekap = JurnalGuruPengaturan::getRekapJurnalGuru($pegawaiId, $teacher['name'], $startDate, $endDate);

        self::render('rekap_jurnal', [
            'pageTitle' => 'Rekap Jurnal PIC & Monitoring KBM',
            'activeTab' => 'jurnal',
            'teacher' => $teacher,
            'rekap' => $rekap,
            'jurnalConfig' => $jurnalConfig,
            'startDate' => $rekap['periode']['start'],
            'endDate' => $rekap['periode']['end'],
        ]);
    }

    /**
     * API: Cek status presensi hari ini guru & titik lokasi efektif
     */
    public static function apiAbsenToday(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $attendance = AbsenPegawaiModel::getTodayAttendance($pegawaiId);
        $efektif = AbsenPegawaiModel::getLokasiEfektifPegawai($pegawaiId);
        $globalConfig = AbsenPegawaiModel::getPengaturanGlobal();

        echo json_encode([
            'success' => true,
            'data' => [
                'attendance' => $attendance,
                'location' => [
                    'name' => $efektif['nama_kantor'],
                    'lat' => (float) $efektif['latitude'],
                    'lng' => (float) $efektif['longitude'],
                    'radius' => (int) $efektif['radius_meter'],
                    'source_label' => match ($efektif['mode'] ?? 'default') {
                        'custom' => 'Custom Pegawai',
                        'unit' => 'Custom Unit',
                        default => 'Lokasi Semula (Default)'
                    }
                ],
                'jam_masuk_mulai' => $efektif['jam_masuk_mulai'] ?? ($globalConfig['jam_masuk_mulai'] ?? '06:00:00'),
                'jam_masuk_batas' => $efektif['jam_masuk_batas'] ?? ($globalConfig['jam_masuk_batas'] ?? '07:30:00'),
                'jam_pulang_mulai' => $efektif['jam_pulang_mulai'] ?? ($globalConfig['jam_pulang_mulai'] ?? '15:00:00'),
            ]
        ]);
        exit;
    }

    /**
     * API: Lakukan check-in masuk guru dengan validasi koordinat GPS
     */
    public static function apiCheckin(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        if ($pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas guru/pegawai tidak ditemukan.']);
            exit;
        }

        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';

        $rawInput = file_get_contents('php://input');
        $payload = [];
        if ($rawInput) {
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }
        if (empty($payload) && !empty($_POST)) {
            $payload = $_POST;
        }

        $lat = isset($payload['latitude']) ? (float) $payload['latitude'] : (isset($payload['lat']) ? (float) $payload['lat'] : 0.0);
        $lng = isset($payload['longitude']) ? (float) $payload['longitude'] : (isset($payload['lng']) ? (float) $payload['lng'] : 0.0);
        $accuracy = isset($payload['accuracy']) ? (float) $payload['accuracy'] : null;

        if ($lat == 0.0 && $lng == 0.0) {
            echo json_encode(['success' => false, 'message' => 'Titik koordinat GPS tidak valid.']);
            exit;
        }

        $result = AbsenPegawaiModel::recordCheckin($pegawaiId, $lat, $lng, $accuracy);
        if (ob_get_level() > 0) {
            ob_clean();
        }
        echo json_encode($result);
        exit;
    }

    /**
     * API: Lakukan check-out / absen pulang guru dengan validasi waktu & koordinat GPS
     */
    public static function apiCheckout(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        if ($pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas guru/pegawai tidak ditemukan.']);
            exit;
        }

        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';

        $rawInput = file_get_contents('php://input');
        $payload = [];
        if ($rawInput) {
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }
        if (empty($payload) && !empty($_POST)) {
            $payload = $_POST;
        }

        $lat = isset($payload['latitude']) ? (float) $payload['latitude'] : (isset($payload['lat']) ? (float) $payload['lat'] : 0.0);
        $lng = isset($payload['longitude']) ? (float) $payload['longitude'] : (isset($payload['lng']) ? (float) $payload['lng'] : 0.0);
        $accuracy = isset($payload['accuracy']) ? (float) $payload['accuracy'] : null;

        $result = AbsenPegawaiModel::recordCheckout($pegawaiId, $lat, $lng, $accuracy);
        if (ob_get_level() > 0) {
            ob_clean();
        }
        echo json_encode($result);
        exit;
    }

    /**
     * Isi Jurnal Pembelajaran
     */
    public static function jurnal(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();

        // Jika pegawai adalah Support System / Staff, tampilkan layar Jurnal Khusus Staff (Jam Kerja & Dropdown Tugas)
        if (!empty($teacher['is_support'])) {
            self::jurnalStaffScreen($teacher);
            return;
        }

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
        } catch (Throwable $e) {
        }

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

        // Jika request berasal dari pegawai Support System atau form jurnal staff
        if (!empty($teacher['is_support']) || !empty($_POST['is_staff_jurnal']) || !empty($_POST['jam_rentang']) || !empty($_POST['jam_id'])) {
            self::simpanJurnalStaff();
            return;
        }

        $db = Database::getInstance();

        $jadwalId = !empty($_POST['jadwal_id']) ? (int) $_POST['jadwal_id'] : null;
        $jurnalId = !empty($_POST['jurnal_id']) ? (int) $_POST['jurnal_id'] : null;
        $kelas = trim($_POST['kelas'] ?? '');
        $mapel = trim($_POST['mata_pelajaran'] ?? ($_POST['mapel'] ?? ''));
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $jamKe = trim($_POST['jam_ke'] ?? '1');
        $jumlahJp = max(1, min(10, (int) ($_POST['jumlah_jp'] ?? 2)));
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

        $guruId = $teacher['pegawai_id'] ?: (int) Auth::id();
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
                            'created_by' => (int) Auth::id() ?: 1,
                            'waktu_presensi' => date('H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (Throwable $pe) {
                }

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
     * Layar Jurnal Khusus Staff (Support System)
     * Menampilkan daftar jam kerja harian, progres harian, dan modal input tugas & keterangan
     */
    private static function jurnalStaffScreen(array $teacher): void
    {
        require_once MODULES_PATH . '/kelola-jurnal-staff/models/JurnalStaffModel.php';
        JurnalStaffModel::ensureTablesExist();

        $db = Database::getInstance();
        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $staff = JurnalStaffModel::getStaffById($pegawaiId) ?: $teacher;

        // Ambil jabatan yang ditugaskan dari Penugasan Pegawai (prioritaskan grup penugasan aktif)
        $assignedJabatans = [];
        if ($pegawaiId > 0) {
            try {
                $penRows = $db->findAll("
                    SELECT mj.id, mj.nama, COALESCE(pg.is_active, 0) AS grup_active
                    FROM pegawai_penugasan pp
                    INNER JOIN master_jabatan mj ON mj.id = pp.jabatan_id
                    LEFT JOIN penugasan_grup pg ON pg.id = pp.grup_id
                    WHERE pp.pegawai_id = ? AND pp.status = 'Aktif'
                    ORDER BY COALESCE(pg.is_active, 0) DESC, pp.id DESC
                ", [$pegawaiId]);

                if (!empty($penRows)) {
                    $hasActiveGrup = false;
                    foreach ($penRows as $pr) {
                        if ((int)$pr['grup_active'] === 1) {
                            $hasActiveGrup = true;
                            break;
                        }
                    }
                    foreach ($penRows as $pr) {
                        if ($hasActiveGrup && (int)$pr['grup_active'] !== 1) {
                            continue;
                        }
                        $jid = (int)$pr['id'];
                        if ($jid > 0 && !isset($assignedJabatans[$jid])) {
                            $assignedJabatans[$jid] = trim((string)$pr['nama']);
                        }
                    }
                }
            } catch (Throwable $e) {}
        }

        // Fallback ke kolom pegawai.jabatan hanya jika pegawai belum memiliki penugasan aktif
        if (empty($assignedJabatans)) {
            $fallbackJabName = trim((string)($staff['jabatan'] ?? ($teacher['role'] ?? '')));
            if ($fallbackJabName !== '') {
                try {
                    $jabatanRow = $db->find("
                        SELECT id, nama
                        FROM master_jabatan
                        WHERE LOWER(TRIM(nama)) = LOWER(TRIM(?))
                        LIMIT 1
                    ", [$fallbackJabName]);
                    if ($jabatanRow) {
                        $assignedJabatans[(int)$jabatanRow['id']] = trim((string)$jabatanRow['nama']);
                    }
                } catch (Throwable $e) {}
            }
        }

        $assignedJabatanIds = array_keys($assignedJabatans);
        $assignedJabatanNames = array_values($assignedJabatans);
        $jabatanId = !empty($assignedJabatanIds) ? (int)$assignedJabatanIds[0] : 0;
        $jabatanName = !empty($assignedJabatanNames)
            ? implode(', ', $assignedJabatanNames)
            : ($staff['jabatan'] ?? ($teacher['role'] ?? 'Staff Support System'));

        // Ambil daftar slot jam kerja (jurnal_staff_jam)
        $jamList = JurnalStaffModel::getJamList(true);

        // Ambil tugas-tugas aktif HANYA berdasarkan jabatan yang ditugaskan kepada pegawai ini
        $tugasJabatan = [];
        if (!empty($assignedJabatanIds)) {
            $placeholders = implode(',', array_fill(0, count($assignedJabatanIds), '?'));
            try {
                $tugasJabatan = $db->findAll("
                    SELECT id, jabatan_id, nama_jabatan, nama_tugas, deskripsi, kategori_tugas
                    FROM jurnal_staff_tugas
                    WHERE is_active = 1 AND jabatan_id IN ({$placeholders})
                    ORDER BY jabatan_id ASC, kategori_tugas ASC, id ASC
                ", $assignedJabatanIds);
            } catch (Throwable $e) {}
        }
        $allTugas = [];

        // Tanggal terpilih (default hari ini)
        $selectedDate = trim($_GET['tanggal'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = date('Y-m-d');
        }

        // Ambil jurnal staff pada tanggal terpilih
        $journalsOnDate = [];
        try {
            $journalsOnDate = $db->findAll("
                SELECT js.*, jst.kategori_tugas
                FROM jurnal_staff js
                LEFT JOIN jurnal_staff_tugas jst ON js.tugas_id = jst.id
                WHERE js.pegawai_id = ? AND js.tanggal = ?
                ORDER BY js.jam_rentang ASC, js.id ASC
            ", [$pegawaiId, $selectedDate]);
        } catch (Throwable $e) {}

        // Mapping jurnal pada tanggal tersebut berdasarkan jam_id dan jam_rentang
        $journalByJamId = [];
        foreach ($journalsOnDate as $jo) {
            if (!empty($jo['jam_id'])) {
                $journalByJamId[(int) $jo['jam_id']] = $jo;
            }
            if (!empty($jo['jam_rentang'])) {
                $journalByJamId[$jo['jam_rentang']] = $jo;
            }
        }

        // Riwayat 30 jurnal terakhir milik staff ini
        $recentStaffJournals = [];
        try {
            $recentStaffJournals = $db->findAll("
                SELECT js.*, jst.kategori_tugas
                FROM jurnal_staff js
                LEFT JOIN jurnal_staff_tugas jst ON js.tugas_id = jst.id
                WHERE js.pegawai_id = ?
                ORDER BY js.tanggal DESC, js.jam_rentang DESC, js.id DESC
                LIMIT 30
            ", [$pegawaiId]);
        } catch (Throwable $e) {}

        $totalSesi = count($jamList);
        $totalTerisi = count($journalsOnDate);
        $totalBelum = max(0, $totalSesi - $totalTerisi);
        $persenTerisi = $totalSesi > 0 ? round(($totalTerisi / $totalSesi) * 100) : 0;

        // ── 1. Hitung Progres Ketuntasan Jurnal Staff Berdasarkan Periode (seperti Rekap Absen) ──
        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $absenGlobal = AbsenPegawaiModel::getPengaturanGlobal();
        $staffPeriodeCfg = JurnalStaffModel::getPengaturanPeriode();

        $periodeAwal = !empty($staffPeriodeCfg['periode_awal']) ? $staffPeriodeCfg['periode_awal'] : ($absenGlobal['periode_awal'] ?: date('Y-m-01'));
        $periodeAkhir = !empty($staffPeriodeCfg['periode_akhir']) ? $staffPeriodeCfg['periode_akhir'] : ($absenGlobal['periode_akhir'] ?: date('Y-m-t'));

        $today = date('Y-m-d');
        $evalEnd = (strtotime($periodeAkhir) > strtotime($today)) ? $today : $periodeAkhir;
        $evalStart = $periodeAwal;
        if (strtotime($evalStart) > strtotime($evalEnd)) {
            $evalStart = $evalEnd;
        }

        // Hitung total hari kerja (Senin - Sabtu) dari evalStart sampai evalEnd
        $totalHariKerjaPeriode = 0;
        $curTs = strtotime($evalStart);
        $endTs = strtotime($evalEnd);
        while ($curTs <= $endTs) {
            $dayOfWeek = (int)date('N', $curTs);
            if ($dayOfWeek !== 7) { // 1=Senin..6=Sabtu (Minggu libur)
                $totalHariKerjaPeriode++;
            }
            $curTs = strtotime('+1 day', $curTs);
        }
        if ($totalHariKerjaPeriode <= 0) {
            $totalHariKerjaPeriode = 1;
        }

        $totalSesiPerHari = count($jamList);
        if ($totalSesiPerHari <= 0) {
            $totalSesiPerHari = 8;
        }

        $targetSesiPeriode = $totalHariKerjaPeriode * $totalSesiPerHari;

        $jurnalPeriodeStats = $db->find("
            SELECT 
                COUNT(*) as total_sesi_terisi,
                COUNT(DISTINCT tanggal) as total_hari_terisi
            FROM jurnal_staff
            WHERE pegawai_id = ? AND tanggal BETWEEN ? AND ?
        ", [$pegawaiId, $evalStart, $evalEnd]);

        $sesiTerisiPeriode = (int) ($jurnalPeriodeStats['total_sesi_terisi'] ?? 0);
        $hariTerisiPeriode = (int) ($jurnalPeriodeStats['total_hari_terisi'] ?? 0);

        // Persentase progres jurnal periode (cap maks 100%)
        $persenJurnalPeriode = $targetSesiPeriode > 0 
            ? min(100, round(($sesiTerisiPeriode / $targetSesiPeriode) * 100, 1)) 
            : 0;

        self::render('jurnal_staff', [
            'pageTitle' => 'Jurnal Kerja Staff',
            'activeTab' => 'jurnal',
            'isSupport' => true,
            'teacher' => $teacher,
            'staff' => $staff,
            'jabatanName' => $jabatanName,
            'jabatanId' => $jabatanId,
            'jamList' => $jamList,
            'tugasJabatan' => $tugasJabatan,
            'allTugas' => $allTugas,
            'selectedDate' => $selectedDate,
            'journalByJamId' => $journalByJamId,
            'journalsOnDate' => $journalsOnDate,
            'recentStaffJournals' => $recentStaffJournals,
            'totalSesi' => $totalSesi,
            'totalTerisi' => $totalTerisi,
            'totalBelum' => $totalBelum,
            'persenTerisi' => $persenTerisi,
            'todayDate' => $today,
            'periodeAwal' => $periodeAwal,
            'periodeAkhir' => $periodeAkhir,
            'persenJurnalPeriode' => $persenJurnalPeriode,
            'sesiTerisiPeriode' => $sesiTerisiPeriode,
            'targetSesiPeriode' => $targetSesiPeriode,
            'hariTerisiPeriode' => $hariTerisiPeriode,
            'totalHariKerjaPeriode' => $totalHariKerjaPeriode,
            'isToday' => ($selectedDate === $today),
            'isPastDate' => (strtotime($selectedDate) < strtotime($today)),
            'isFutureDate' => (strtotime($selectedDate) > strtotime($today))
        ]);
    }

    /**
     * API: Simpan / Perbarui Jurnal Kerja Staff (Support System)
     */
    public static function simpanJurnalStaff(): void
    {
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $teacher = self::getLoggedInTeacher();
        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        if ($pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas pegawai tidak ditemukan.']);
            exit;
        }

        require_once MODULES_PATH . '/kelola-jurnal-staff/models/JurnalStaffModel.php';
        JurnalStaffModel::ensureTablesExist();

        $input = $_POST;
        if (empty($input)) {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
        }

        $jurnalId = !empty($input['jurnal_id']) ? (int) $input['jurnal_id'] : null;
        $jamId = !empty($input['jam_id']) ? (int) $input['jam_id'] : null;
        $jamRentang = trim($input['jam_rentang'] ?? '');
        $tanggal = !empty($input['tanggal']) ? trim($input['tanggal']) : date('Y-m-d');

        // Aturan: Jurnal hanya dapat diisi atau diubah pada HARI INI (hari berjalan)
        $today = date('Y-m-d');
        if ($tanggal !== $today) {
            echo json_encode([
                'success' => false,
                'message' => 'Jurnal kerja hanya dapat diisi atau diperbarui pada hari ini (' . date('d/m/Y') . '). Jurnal tanggal kemarin atau hari lainnya telah dikunci dan tidak dapat diubah.'
            ]);
            exit;
        }

        $db = Database::getInstance();
        if ($jurnalId) {
            $existCheck = $db->find("SELECT tanggal FROM jurnal_staff WHERE id = ? AND pegawai_id = ? LIMIT 1", [$jurnalId, $pegawaiId]);
            if ($existCheck && $existCheck['tanggal'] !== $today) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Jurnal tanggal lampau (' . date('d/m/Y', strtotime($existCheck['tanggal'])) . ') telah dikunci dan tidak dapat diubah lagi.'
                ]);
                exit;
            }
        }
        
        $rawTugasId = trim($input['tugas_id'] ?? '');
        $tugasId = ($rawTugasId !== '' && $rawTugasId !== 'custom' && is_numeric($rawTugasId)) ? (int) $rawTugasId : null;

        $aktivitas = trim($input['aktivitas'] ?? '');
        if (empty($aktivitas) && !empty($input['aktivitas_custom'])) {
            $aktivitas = trim($input['aktivitas_custom']);
        }

        $uraianHasil = trim($input['uraian_hasil'] ?? ($input['keterangan'] ?? ''));
        $kendala = trim($input['kendala'] ?? '');
        $status = in_array($input['status'] ?? '', ['selesai', 'proses', 'tertunda'], true) ? $input['status'] : 'selesai';

        // Jika jamRentang masih kosong, ambil dari jurnal_staff_jam
        if ($jamRentang === '' && $jamId) {
            $jamRow = JurnalStaffModel::getJamById($jamId);
            if ($jamRow) {
                $jamRentang = date('H:i', strtotime($jamRow['jam_mulai'])) . ' - ' . date('H:i', strtotime($jamRow['jam_selesai']));
            }
        }
        if ($jamRentang === '') {
            $jamRentang = '08:00 - 09:00';
        }

        // Jika aktivitas masih kosong tapi tugas_id ada, ambil nama tugasnya
        if ($aktivitas === '' && $tugasId) {
            $tRow = JurnalStaffModel::getTugasById($tugasId);
            if ($tRow) {
                $aktivitas = $tRow['nama_tugas'];
            }
        }
        if ($aktivitas === '') {
            echo json_encode(['success' => false, 'message' => 'Pilih tugas atau masukkan nama aktivitas kerja.']);
            exit;
        }

        if ($uraianHasil === '') {
            echo json_encode(['success' => false, 'message' => 'Keterangan / uraian hasil kerja wajib diisi.']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $file = $_FILES['foto_bukti'] ?? ($_FILES['foto'] ?? null);

            // Upload foto sekali saja jika ada, agar bisa dipakai bersama untuk semua jam yang dipilih
            $sharedFoto = null;
            if ($file && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
                $sharedFoto = JurnalStaffModel::uploadBuktiFile($file);
            }
            if ($sharedFoto === null && !empty($input['foto_captured'])) {
                $sharedFoto = JurnalStaffModel::saveBase64Bukti((string) $input['foto_captured']);
            }

            // Support Multi-Jam Selection (e.g. jam_ids = [2, 3, 4] atau string "2,3,4")
            $jamIds = [];
            if (!empty($input['jam_ids'])) {
                if (is_array($input['jam_ids'])) {
                    $jamIds = array_map('intval', $input['jam_ids']);
                } else {
                    $jamIds = array_map('intval', explode(',', (string) $input['jam_ids']));
                }
            } elseif (!empty($input['jam_id'])) {
                $jamIds = [(int) $input['jam_id']];
            }
            $jamIds = array_values(array_filter(array_unique($jamIds), fn($id) => $id > 0));

            // Fallback jika tidak ada jam_ids
            if (empty($jamIds)) {
                $jamIds = [$jamId ?: 0];
            }

            $savedCount = 0;
            $savedIds = [];

            // Loop simpan jurnal untuk setiap jam yang dipilih
            foreach ($jamIds as $currJamId) {
                $currRentang = '';
                if ($currJamId > 0) {
                    $jamRow = JurnalStaffModel::getJamById($currJamId);
                    if ($jamRow) {
                        $currRentang = date('H:i', strtotime($jamRow['jam_mulai'])) . ' - ' . date('H:i', strtotime($jamRow['jam_selesai']));
                    }
                }
                if ($currRentang === '') {
                    $currRentang = $jamRentang ?: '08:00 - 09:00';
                }

                // Cek apakah sudah pernah ada jurnal untuk (pegawai_id, tanggal, jam_id)
                $currJurnalId = null;
                if ($currJamId > 0) {
                    $exist = $db->find("
                        SELECT id FROM jurnal_staff 
                        WHERE pegawai_id = ? AND tanggal = ? AND jam_id = ? 
                        LIMIT 1
                    ", [$pegawaiId, $tanggal, $currJamId]);
                    if ($exist) {
                        $currJurnalId = (int) $exist['id'];
                    }
                } elseif ($jurnalId && count($jamIds) === 1) {
                    $currJurnalId = $jurnalId;
                }

                $data = [
                    'pegawai_id' => $pegawaiId,
                    'tanggal' => $tanggal,
                    'jam_id' => $currJamId ?: null,
                    'jam_rentang' => $currRentang,
                    'tugas_id' => $tugasId,
                    'aktivitas' => $aktivitas,
                    'uraian_hasil' => $uraianHasil,
                    'kendala' => $kendala ?: null,
                    'status' => $status
                ];

                // Jika ada foto yang baru diunggah, lampirkan ke record
                if ($sharedFoto !== null) {
                    $data['foto_bukti'] = $sharedFoto;
                }

                if ($currJurnalId > 0) {
                    JurnalStaffModel::updateJurnal($currJurnalId, $data, null);
                    $savedIds[] = $currJurnalId;
                } else {
                    $newId = JurnalStaffModel::storeJurnal($data, null);
                    $savedIds[] = $newId;
                }
                $savedCount++;
            }

            $msg = $savedCount > 1 
                ? "Alhamdulillah, jurnal kerja berhasil disimpan untuk {$savedCount} sesi jam kerja!" 
                : 'Jurnal kerja berhasil disimpan!';

            $firstSavedRow = !empty($savedIds) ? JurnalStaffModel::getJurnalById($savedIds[0]) : null;

            echo json_encode([
                'success' => true,
                'message' => $msg,
                'count' => $savedCount,
                'saved_ids' => $savedIds,
                'data' => $firstSavedRow
            ]);
            exit;
        } catch (Throwable $e) {
            error_log("Error simpanJurnalStaff: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * API: Hapus Jurnal Kerja Staff
     */
    public static function hapusJurnalStaff(): void
    {
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $teacher = self::getLoggedInTeacher();
        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = (int) ($input['id'] ?? 0);

        if ($id <= 0 || $pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID jurnal tidak valid.']);
            exit;
        }

        require_once MODULES_PATH . '/kelola-jurnal-staff/models/JurnalStaffModel.php';
        $existing = JurnalStaffModel::getJurnalById($id);
        if (!$existing || (int) $existing['pegawai_id'] !== $pegawaiId) {
            echo json_encode(['success' => false, 'message' => 'Data jurnal tidak ditemukan atau Anda tidak memiliki akses.']);
            exit;
        }

        // Aturan: Hanya jurnal hari ini yang dapat dihapus, tanggal lampau dikunci
        if ($existing['tanggal'] !== date('Y-m-d')) {
            echo json_encode([
                'success' => false,
                'message' => 'Jurnal tanggal lampau (' . date('d/m/Y', strtotime($existing['tanggal'])) . ') telah dikunci dan tidak dapat dihapus.'
            ]);
            exit;
        }

        try {
            JurnalStaffModel::deleteJurnal($id);
            echo json_encode(['success' => true, 'message' => 'Jurnal berhasil dihapus.']);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()]);
            exit;
        }
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
                    $existingDetails[(int) $d['siswa_id']] = $d['status'];
                }
            }

            foreach ($students as &$st) {
                $stId = (int) $st['id'];
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

        if (!empty($teacher['is_support'])) {
            header('Location: ' . url('mobile'));
            exit;
        }

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
                $totalWaliJp += (int) ($c['jp'] ?? 2);
            } else {
                $totalPicClasses++;
                $totalPicJp += (int) ($c['jp'] ?? 2);
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

        if (!empty($teacher['is_support'])) {
            header('Location: ' . url('mobile'));
            exit;
        }

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
        } catch (Throwable $e) {
        }

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
        } catch (Throwable $e) {
        }

        $savedDetails = [];
        if ($presensiHariIni) {
            try {
                $details = $db->findAll("SELECT siswa_id, status FROM presensi_kelas_detail WHERE presensi_kelas_id = ?", [$presensiHariIni['id']]);
                foreach ($details as $d) {
                    $savedDetails[(int) $d['siswa_id']] = $d['status'];
                }
            } catch (Throwable $e) {
            }
        }

        foreach ($students as &$s) {
            $sId = (int) $s['id'];
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
            $h = 0;
            $s = 0;
            $i = 0;
            $a = 0;
            foreach ($statuses as $stat) {
                if ($stat === 'H')
                    $h++;
                elseif ($stat === 'S')
                    $s++;
                elseif ($stat === 'I')
                    $i++;
                elseif ($stat === 'A')
                    $a++;
            }
            $totalSiswa = count($statuses);

            if ($totalSiswa > 0 && ($s + $i + $a === 0)) {
                $summary = "{$totalSiswa}/{$totalSiswa} Hadir Lengkap";
            } elseif ($totalSiswa > 0) {
                $parts = ["{$h} Hadir"];
                if ($s > 0)
                    $parts[] = "{$s} Sakit";
                if ($i > 0)
                    $parts[] = "{$i} Izin";
                if ($a > 0)
                    $parts[] = "{$a} Alpa";
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
                $presensiId = (int) $existing['id'];
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
                    $insertParams[] = (int) $sId;
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

        if (!empty($teacher['is_support'])) {
            header('Location: ' . url('mobile'));
            exit;
        }

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
                        'id' => (int) $r['id'],
                        'name' => $r['name'],
                        'nisn' => $r['nisn'] ?: ($r['nis'] ?: '-'),
                        'class' => str_starts_with($r['kelas'], 'Kelas ') ? $r['kelas'] : 'Kelas ' . $r['kelas'],
                        'gender' => $r['gender'] ?: 'L',
                        'presence' => '100%',
                        'phone' => $phone ?: '628123456789',
                        'points' => 100
                    ];
                }
            } catch (Throwable $e) {
            }
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

        $isSupport = !empty($teacher['is_support']);
        self::render('profil', [
            'pageTitle' => $isSupport ? 'Profil Pegawai & Pengaturan PWA' : 'Profil Guru & Pengaturan PWA',
            'activeTab' => 'profil',
            'profile' => $teacher,
            'isSupport' => $isSupport
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
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        $classes = !empty($classesMap) ? array_keys($classesMap) : [];
        if (empty($classes)) {
            try {
                $allK = Database::getInstance()->findAll("SELECT nama_kelas FROM kelas WHERE is_active = 1 ORDER BY tingkat ASC, nama_kelas ASC LIMIT 10");
                foreach ($allK as $k) {
                    $classes[] = $k['nama_kelas'];
                }
            } catch (Throwable $e) {
            }
        }

        self::render('buat_tugas', [
            'pageTitle' => 'Buat Tugas Siswa',
            'activeTab' => 'tugas',
            'teacher' => $teacher,
            'classes' => $classes
        ]);
    }

    /**
     * Pesan Kelas
     */
    public static function pesanKelas(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        $classes = !empty($classesMap) ? array_keys($classesMap) : [];
        if (empty($classes)) {
            try {
                $allK = Database::getInstance()->findAll("SELECT nama_kelas FROM kelas WHERE is_active = 1 ORDER BY tingkat ASC, nama_kelas ASC LIMIT 10");
                foreach ($allK as $k) {
                    $classes[] = $k['nama_kelas'];
                }
            } catch (Throwable $e) {
            }
        }

        self::render('pesan_kelas', [
            'pageTitle' => 'Pesan & Pengumuman Kelas',
            'activeTab' => 'pesan',
            'teacher' => $teacher,
            'classes' => $classes
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
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $guruIdForIbadah = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $userId = (int) Auth::id();
        $teacherGender = $teacher['gender'] ?? 'P';

        $userBookmarks = self::getUserQuranBookmarks($guruIdForIbadah, $userId);

        self::render('quran', [
            'pageTitle' => 'Al-Qur\'an Digital',
            'activeTab' => 'quran',
            'teacherGender' => $teacherGender,
            'teacherPegawaiId' => $guruIdForIbadah,
            'currentUserId' => $userId,
            'userBookmarks' => $userBookmarks,
            'todayIbadahState' => self::getTodayIbadahState($guruIdForIbadah, $teacherGender),
        ]);
    }

    /**
     * Dzikir Pagi & Petang (Al-Ma'tsurat Sugro & Kubro) with Interactive Digital Tasbih
     */
    public static function dzikir(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $guruIdForIbadah = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $teacherGender = $teacher['gender'] ?? 'P';

        self::render('dzikir', [
            'pageTitle' => 'Dzikir Pagi & Petang (Al-Ma\'tsurat)',
            'activeTab' => 'dzikir',
            'teacherGender' => $teacherGender,
            'teacherPegawaiId' => $guruIdForIbadah,
            'todayIbadahState' => self::getTodayIbadahState($guruIdForIbadah, $teacherGender),
        ]);
    }

    /**
     * Pastikan tabel tasbih_kategori dan tasbih_riwayat otomatis dibuat jika belum ada di database.
     */
    private static function ensureTasbihTablesExist(): void
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `tasbih_kategori` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `pegawai_id` INT(11) DEFAULT 0,
                  `nama_tasbih` VARCHAR(100) NOT NULL,
                  `lafadz_arab` VARCHAR(255) DEFAULT NULL,
                  `arti` TEXT DEFAULT NULL,
                  `target_default` INT(11) DEFAULT 33,
                  `is_default` TINYINT(1) DEFAULT 0,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_pegawai` (`pegawai_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `tasbih_riwayat` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `pegawai_id` INT(11) NOT NULL,
                  `kategori_id` INT(11) DEFAULT NULL,
                  `nama_tasbih` VARCHAR(100) NOT NULL,
                  `jumlah` INT(11) NOT NULL DEFAULT 0,
                  `target` INT(11) NOT NULL DEFAULT 33,
                  `putaran` INT(11) NOT NULL DEFAULT 1,
                  `catatan` VARCHAR(255) DEFAULT NULL,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_pegawai_tgl` (`pegawai_id`, `created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Seed kategori default jika masih kosong
            try {
                $count = (int) $pdo->query("SELECT COUNT(*) FROM `tasbih_kategori` WHERE `pegawai_id` = 0")->fetchColumn();
                if ($count === 0) {
                    $defaults = [
                        ['Subhanallah (Tasbih)', 'سُبْحَانَ اللَّهِ', 'Maha Suci Allah', 33, 1],
                        ['Alhamdulillah (Tahmid)', 'الْحَمْدُ لِلَّهِ', 'Segala puji bagi Allah', 33, 1],
                        ['Laa Ilaha Illallah (Tahlil)', 'لَا إِلٰهَ إِلَّا اللَّهُ', 'Tiada Tuhan selain Allah', 33, 1],
                        ['Allahu Akbar (Takbir)', 'اللَّهُ أَكْبَرُ', 'Allah Maha Besar', 33, 1],
                        ['Sholawat Nabi', 'اللَّهُمَّ صَلِّ عَلَى سَيِّدِنَا مُحَمَّدٍ', 'Ya Allah, limpahkanlah rahmat kepada junjungan kami Nabi Muhammad', 33, 1],
                        ['Astaghfirullah (Istighfar)', 'أَسْتَغْفِرُ اللَّهَ الْعَظِيمَ', 'Aku memohon ampun kepada Allah Yang Maha Agung', 100, 1],
                        ['Hauqalah', 'لَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللَّهِ', 'Tiada daya dan kekuatan kecuali dengan pertolongan Allah', 33, 1],
                        ['Tasbih Lengkap (Subhanallah wa Bihamdihi)', 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ سُبْحَانَ اللَّهِ الْعَظِيمِ', 'Maha Suci Allah dengan memuji-Nya, Maha Suci Allah Yang Maha Agung', 100, 1],
                    ];

                    $stmt = $pdo->prepare("INSERT INTO `tasbih_kategori` (`nama_tasbih`, `lafadz_arab`, `arti`, `target_default`, `is_default`, `pegawai_id`) VALUES (?, ?, ?, ?, ?, 0)");
                    foreach ($defaults as $d) {
                        $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4]]);
                    }
                }
            } catch (Throwable $e) {
            }
        } catch (Throwable $e) {
            error_log('ensureTasbihTablesExist error: ' . $e->getMessage());
        }
    }

    /**
     * Tasbih Digital Interaktif dengan Kategori Kustom & Riwayat Dzikir
     */
    public static function tasbih(): void
    {
        self::ensureTasbihTablesExist();
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $guruId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $db = Database::getInstance();

        // Ambil kategori bawaan (pegawai_id = 0) dan kustom milik pegawai saat ini
        $kategori = [];
        try {
            $kategori = $db->findAll("
                SELECT * FROM tasbih_kategori 
                WHERE pegawai_id = 0 OR pegawai_id = ?
                ORDER BY is_default DESC, id ASC
            ", [$guruId]);
        } catch (Throwable $e) {
        }

        // Ambil riwayat tasbih hari ini dan statistik
        $riwayatHariIni = [];
        $totalDzikirHariIni = 0;
        $totalSesiHariIni = 0;
        try {
            $today = date('Y-m-d');
            $riwayatHariIni = $db->findAll("
                SELECT * FROM tasbih_riwayat 
                WHERE pegawai_id = ? AND DATE(created_at) = ?
                ORDER BY created_at DESC LIMIT 30
            ", [$guruId, $today]);

            $stats = $db->find("
                SELECT SUM(jumlah) as total_dzikir, COUNT(*) as total_sesi 
                FROM tasbih_riwayat 
                WHERE pegawai_id = ? AND DATE(created_at) = ?
            ", [$guruId, $today]);
            $totalDzikirHariIni = (int) ($stats['total_dzikir'] ?? 0);
            $totalSesiHariIni = (int) ($stats['total_sesi'] ?? 0);
        } catch (Throwable $e) {
        }

        self::render('tasbih', [
            'pageTitle' => 'Tasbih Digital',
            'activeTab' => 'tasbih',
            'kategori' => $kategori,
            'riwayatHariIni' => $riwayatHariIni,
            'totalDzikirHariIni' => $totalDzikirHariIni,
            'totalSesiHariIni' => $totalSesiHariIni,
            'teacher' => $teacher,
        ]);
    }

    /**
     * API: Simpan Jenis / Kategori Tasbih Kustom (Input Sendiri)
     */
    public static function simpanKategoriTasbih(): void
    {
        self::ensureTasbihTablesExist();
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $guruId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        if ($guruId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas pegawai tidak ditemukan.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $namaTasbih = trim($input['nama_tasbih'] ?? '');
        $lafadzArab = trim($input['lafadz_arab'] ?? '');
        $arti = trim($input['arti'] ?? '');
        $targetDefault = (int) ($input['target_default'] ?? 33);
        if ($targetDefault <= 0)
            $targetDefault = 33;

        if (empty($namaTasbih)) {
            echo json_encode(['success' => false, 'message' => 'Nama dzikir/tasbih wajib diisi.']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $db->query("
                INSERT INTO tasbih_kategori (pegawai_id, nama_tasbih, lafadz_arab, arti, target_default, is_default)
                VALUES (?, ?, ?, ?, ?, 0)
            ", [$guruId, $namaTasbih, $lafadzArab, $arti, $targetDefault]);
            $newId = (int) $db->lastInsertId();

            $newRow = $db->find("SELECT * FROM tasbih_kategori WHERE id = ?", [$newId]);

            echo json_encode([
                'success' => true,
                'message' => 'Jenis tasbih baru berhasil disimpan!',
                'data' => $newRow
            ]);
            exit;
        } catch (Throwable $e) {
            // Auto-heal: jika tabel belum ada atau error DDL, paksa buat tabel dan retry sekali lagi
            try {
                self::ensureTasbihTablesExist();
                $db = Database::getInstance();
                $db->query("
                    INSERT INTO tasbih_kategori (pegawai_id, nama_tasbih, lafadz_arab, arti, target_default, is_default)
                    VALUES (?, ?, ?, ?, ?, 0)
                ", [$guruId, $namaTasbih, $lafadzArab, $arti, $targetDefault]);
                $newId = (int) $db->lastInsertId();
                $newRow = $db->find("SELECT * FROM tasbih_kategori WHERE id = ?", [$newId]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Jenis tasbih baru berhasil disimpan!',
                    'data' => $newRow
                ]);
                exit;
            } catch (Throwable $reEx) {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $reEx->getMessage()]);
                exit;
            }
        }
    }

    /**
     * API: Hapus Jenis Tasbih Kustom milik pegawai
     */
    public static function hapusKategoriTasbih(): void
    {
        self::ensureTasbihTablesExist();
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $guruId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = (int) ($input['id'] ?? 0);

        if ($id <= 0 || $guruId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID kategori tidak valid.']);
            exit;
        }

        try {
            $db = Database::getInstance();
            // Hanya boleh menghapus kategori kustom milik pegawai sendiri
            $db->query("DELETE FROM tasbih_kategori WHERE id = ? AND pegawai_id = ? AND is_default = 0", [$id, $guruId]);
            echo json_encode(['success' => true, 'message' => 'Jenis tasbih berhasil dihapus.']);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * API: Simpan Hasil Hitungan / Sesi Tasbih Digital
     */
    public static function simpanSesiTasbih(): void
    {
        self::ensureTasbihTablesExist();
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $guruId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        if ($guruId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas pegawai tidak ditemukan.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $namaTasbih = trim($input['nama_tasbih'] ?? 'Dzikir');
        $kategoriId = !empty($input['kategori_id']) ? (int) $input['kategori_id'] : null;
        $jumlah = (int) ($input['jumlah'] ?? 0);
        $target = (int) ($input['target'] ?? 33);
        $putaran = (int) ($input['putaran'] ?? 1);
        $catatan = trim($input['catatan'] ?? '');

        if ($jumlah <= 0) {
            echo json_encode(['success' => false, 'message' => 'Jumlah hitungan tasbih minimal 1.']);
            exit;
        }

        try {
            $db = Database::getInstance();
            $db->query("
                INSERT INTO tasbih_riwayat (pegawai_id, kategori_id, nama_tasbih, jumlah, target, putaran, catatan)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$guruId, $kategoriId, $namaTasbih, $jumlah, $target, $putaran, $catatan]);
            $insertedId = (int) $db->lastInsertId();

            // Auto centang dzikir mutabaah jika cocok
            $lowerName = strtolower($namaTasbih);
            $today = date('Y-m-d');
            $gender = $teacher['gender'] ?? 'P';
            try {
                if (strpos($lowerName, 'istighfar') !== false) {
                    $db->query("
                        INSERT INTO mutabaah_ibadah_guru (guru_id, tanggal, gender, dzikir_istighfar)
                        VALUES (?, ?, ?, 1)
                        ON DUPLICATE KEY UPDATE dzikir_istighfar = 1
                    ", [$guruId, $today, $gender]);
                }
                if (strpos($lowerName, 'sholawat') !== false) {
                    $db->query("
                        INSERT INTO mutabaah_ibadah_guru (guru_id, tanggal, gender, dzikir_sholawat)
                        VALUES (?, ?, ?, 1)
                        ON DUPLICATE KEY UPDATE dzikir_sholawat = 1
                    ", [$guruId, $today, $gender]);
                }
            } catch (Throwable $e) {
            }

            echo json_encode([
                'success' => true,
                'message' => 'Alhamdulillah, sesi tasbih berhasil disimpan!',
                'data' => [
                    'id' => $insertedId,
                    'nama_tasbih' => $namaTasbih,
                    'jumlah' => $jumlah,
                    'waktu' => date('H:i')
                ]
            ]);
            exit;
        } catch (Throwable $e) {
            try {
                self::ensureTasbihTablesExist();
                $db = Database::getInstance();
                $db->query("
                    INSERT INTO tasbih_riwayat (pegawai_id, kategori_id, nama_tasbih, jumlah, target, putaran, catatan)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [$guruId, $kategoriId, $namaTasbih, $jumlah, $target, $putaran, $catatan]);
                $insertedId = (int) $db->lastInsertId();

                echo json_encode([
                    'success' => true,
                    'message' => 'Alhamdulillah, sesi tasbih berhasil disimpan!',
                    'data' => [
                        'id' => $insertedId,
                        'nama_tasbih' => $namaTasbih,
                        'jumlah' => $jumlah,
                        'waktu' => date('H:i')
                    ]
                ]);
                exit;
            } catch (Throwable $reEx) {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan riwayat tasbih: ' . $reEx->getMessage()]);
                exit;
            }
        }
    }

    /**
     * API: Ambil Riwayat Tasbih Pegawai
     */
    public static function apiTasbihRiwayat(): void
    {
        self::ensureTasbihTablesExist();
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $guruId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $db = Database::getInstance();

        try {
            $riwayat = $db->findAll("
                SELECT * FROM tasbih_riwayat 
                WHERE pegawai_id = ? 
                ORDER BY created_at DESC 
                LIMIT 50
            ", [$guruId]);

            echo json_encode(['success' => true, 'data' => $riwayat]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * API: Ambil status Ibadah Harian hari ini dari database (mutabaah_ibadah_guru)
     */
    public static function apiIbadahToday(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $guruId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $gender = $teacher['gender'] ?? 'P';

        echo json_encode([
            'success' => true,
            'data' => self::getTodayIbadahState($guruId, $gender),
        ]);
        exit;
    }

    /**
     * Simpan / Sinkronkan Ibadah Harian guru ke database mutabaah_ibadah_guru
     * Mendukung full payload dari Beranda maupun partial action ('mark_tilawah', 'mark_dzikir').
     */
    public static function simpanIbadah(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $guruId = (int) ($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        if ($guruId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas guru tidak ditemukan.']);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $payload = [];
        if ($rawInput) {
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }
        if (empty($payload) && !empty($_POST)) {
            $payload = $_POST;
        }

        $todayDate = date('Y-m-d');
        $teacherGender = $teacher['gender'] ?? 'P';
        $payloadGender = strtoupper(trim((string) ($payload['gender'] ?? $teacherGender))) === 'L' ? 'L' : 'P';
        // Gunakan jenis_kelamin asli dari pegawai jika guru Perempuan ('P'), agar sholat otomatis Rumah
        $gender = ($teacherGender === 'P') ? 'P' : $payloadGender;

        // Ambil state yang ada hari ini di DB terlebih dahulu
        $current = self::getTodayIbadahState($guruId, $gender);
        $action = trim((string) ($payload['action'] ?? 'full'));

        if ($action === 'mark_tilawah') {
            $current['tilawah']['checked'] = true;
            if (!empty($payload['tilawah_surah'])) {
                $current['tilawah']['text'] = mb_substr(trim((string) $payload['tilawah_surah']), 0, 100);
            }
            if (isset($payload['tilawah_duration_seconds'])) {
                $current['tilawah']['duration_seconds'] = max(0, (int) $payload['tilawah_duration_seconds']);
            }
        } elseif ($action === 'mark_dzikir') {
            $current['dzikir']['istighfar'] = true;
            $current['dzikir']['sholawat'] = true;
        } else {
            // Full update dari modal / kartu Beranda
            $sholatIn = is_array($payload['sholat'] ?? null) ? $payload['sholat'] : [];
            foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
                if (isset($sholatIn[$w]) && is_array($sholatIn[$w])) {
                    $chk = !empty($sholatIn[$w]['checked']);
                    $loc = ($sholatIn[$w]['loc'] ?? 'rumah') === 'masjid' ? 'masjid' : 'rumah';
                    // Aturan khusus perempuan: semua lokasi sholat otomatis 'rumah'
                    if ($gender === 'P') {
                        $loc = 'rumah';
                    }
                    $current['sholat'][$w] = ['checked' => $chk, 'loc' => $loc];
                } elseif ($gender === 'P') {
                    $current['sholat'][$w]['loc'] = 'rumah';
                }
            }

            if (isset($payload['tilawah']) && is_array($payload['tilawah'])) {
                $current['tilawah']['checked'] = !empty($payload['tilawah']['checked']);
                $current['tilawah']['text'] = mb_substr(trim((string) ($payload['tilawah']['text'] ?? '')), 0, 100);
                if (isset($payload['tilawah']['duration_seconds'])) {
                    $current['tilawah']['duration_seconds'] = max(0, (int) $payload['tilawah']['duration_seconds']);
                }
            }

            if (isset($payload['dzikir']) && is_array($payload['dzikir'])) {
                $current['dzikir']['istighfar'] = !empty($payload['dzikir']['istighfar']);
                $current['dzikir']['sholawat'] = !empty($payload['dzikir']['sholawat']);
            }

            if (isset($payload['tadabbur']) && is_array($payload['tadabbur'])) {
                $current['tadabbur']['checked'] = !empty($payload['tadabbur']['checked']);
                $current['tadabbur']['text'] = trim((string) ($payload['tadabbur']['text'] ?? ''));
            }
        }

        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO mutabaah_ibadah_guru (
                    guru_id, tanggal, gender,
                    sholat_subuh, sholat_subuh_loc,
                    sholat_dzuhur, sholat_dzuhur_loc,
                    sholat_ashar, sholat_ashar_loc,
                    sholat_maghrib, sholat_maghrib_loc,
                    sholat_isya, sholat_isya_loc,
                    tilawah_checked, tilawah_surah, tilawah_duration_seconds,
                    dzikir_istighfar, dzikir_sholawat,
                    tadabbur_checked, tadabbur_notes
                ) VALUES (
                    ?, ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?
                ) ON DUPLICATE KEY UPDATE
                    gender = VALUES(gender),
                    sholat_subuh = VALUES(sholat_subuh),
                    sholat_subuh_loc = VALUES(sholat_subuh_loc),
                    sholat_dzuhur = VALUES(sholat_dzuhur),
                    sholat_dzuhur_loc = VALUES(sholat_dzuhur_loc),
                    sholat_ashar = VALUES(sholat_ashar),
                    sholat_ashar_loc = VALUES(sholat_ashar_loc),
                    sholat_maghrib = VALUES(sholat_maghrib),
                    sholat_maghrib_loc = VALUES(sholat_maghrib_loc),
                    sholat_isya = VALUES(sholat_isya),
                    sholat_isya_loc = VALUES(sholat_isya_loc),
                    tilawah_checked = VALUES(tilawah_checked),
                    tilawah_surah = VALUES(tilawah_surah),
                    tilawah_duration_seconds = VALUES(tilawah_duration_seconds),
                    dzikir_istighfar = VALUES(dzikir_istighfar),
                    dzikir_sholawat = VALUES(dzikir_sholawat),
                    tadabbur_checked = VALUES(tadabbur_checked),
                    tadabbur_notes = VALUES(tadabbur_notes),
                    updated_at = CURRENT_TIMESTAMP
            ");

            $stmt->execute([
                $guruId,
                $todayDate,
                $gender,
                !empty($current['sholat']['subuh']['checked']) ? 1 : 0,
                $gender === 'P' ? 'rumah' : $current['sholat']['subuh']['loc'],
                !empty($current['sholat']['dzuhur']['checked']) ? 1 : 0,
                $gender === 'P' ? 'rumah' : $current['sholat']['dzuhur']['loc'],
                !empty($current['sholat']['ashar']['checked']) ? 1 : 0,
                $gender === 'P' ? 'rumah' : $current['sholat']['ashar']['loc'],
                !empty($current['sholat']['maghrib']['checked']) ? 1 : 0,
                $gender === 'P' ? 'rumah' : $current['sholat']['maghrib']['loc'],
                !empty($current['sholat']['isya']['checked']) ? 1 : 0,
                $gender === 'P' ? 'rumah' : $current['sholat']['isya']['loc'],
                !empty($current['tilawah']['checked']) ? 1 : 0,
                $current['tilawah']['text'] !== '' ? $current['tilawah']['text'] : null,
                (int) ($current['tilawah']['duration_seconds'] ?? 0),
                !empty($current['dzikir']['istighfar']) ? 1 : 0,
                !empty($current['dzikir']['sholawat']) ? 1 : 0,
                !empty($current['tadabbur']['checked']) ? 1 : 0,
                $current['tadabbur']['text'] !== '' ? $current['tadabbur']['text'] : null,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Mutaba\'ah Ibadah Harian berhasil disimpan.',
                'data' => self::getTodayIbadahState($guruId, $gender),
            ]);
            exit;
        } catch (Throwable $e) {
            error_log('simpanIbadah error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan ibadah: ' . $e->getMessage(),
            ]);
            exit;
        }
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

        $nomor = (int) $nomor;
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

        $page = (int) $page;
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
     * Dapatkan bookmark Al-Qur'an (Tilawah & Hafalan) untuk user/guru tertentu.
     * Default penandaan pertama jika belum ditandai adalah AL-FATIAH (Surah 1, Ayat 1, Halaman 1).
     */
    public static function getUserQuranBookmarks(?int $guruId, ?int $userId): array
    {
        $defaultTilawah = [
            'nomor' => 1,
            'nama'  => 'Al-Fatihah',
            'ayat'  => 1,
            'page'  => 1,
            'mode'  => 'ayat',
            'time'  => 'Al-Fatihah (Ayat 1)',
            'is_default' => true
        ];
        $defaultHafalan = [
            'nomor' => 1,
            'nama'  => 'Al-Fatihah',
            'ayat'  => 1,
            'page'  => 1,
            'mode'  => 'hafalan',
            'time'  => 'Al-Fatihah (Ayat 1)',
            'is_default' => true
        ];

        if (!$guruId && !$userId) {
            return [
                'tilawah' => $defaultTilawah,
                'hafalan' => $defaultHafalan
            ];
        }

        try {
            $db = Database::getInstance();
            $rows = $db->findAll(
                "SELECT * FROM `quran_bookmark_guru` WHERE (guru_id = ? OR (user_id IS NOT NULL AND user_id = ?))",
                [(int)$guruId, (int)$userId]
            );

            $tilawah = null;
            $hafalan = null;

            foreach ($rows as $r) {
                $bm = [
                    'nomor' => (int)$r['surah_nomor'],
                    'nama'  => $r['surah_nama'],
                    'ayat'  => (int)$r['ayat_nomor'],
                    'page'  => (int)($r['halaman'] ?? 1),
                    'mode'  => $r['mode'] ?? 'ayat',
                    'time'  => $r['ditandai_pada'] ?? ''
                ];
                if ($r['tipe'] === 'hafalan') {
                    $hafalan = $bm;
                } else {
                    $tilawah = $bm;
                }
            }

            return [
                'tilawah' => $tilawah ?: $defaultTilawah,
                'hafalan' => $hafalan ?: $defaultHafalan
            ];
        } catch (Throwable $e) {
            return [
                'tilawah' => $defaultTilawah,
                'hafalan' => $defaultHafalan
            ];
        }
    }

    /**
     * API: Ambil bookmark Al-Qur'an aktif user yang sedang login
     */
    public static function apiGetQuranBookmark(): void
    {
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $teacher = self::getLoggedInTeacher();
        $guruId = (int)($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $userId = (int)Auth::id();

        $bookmarks = self::getUserQuranBookmarks($guruId, $userId);

        echo json_encode([
            'success' => true,
            'guru_id' => $guruId,
            'user_id' => $userId,
            'tilawah' => $bookmarks['tilawah'],
            'hafalan' => $bookmarks['hafalan']
        ]);
        exit;
    }

    /**
     * API: Simpan bookmark Al-Qur'an (Tilawah atau Hafalan) untuk user/guru yang sedang login
     */
    public static function apiSaveQuranBookmark(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $teacher = self::getLoggedInTeacher();
        $guruId = (int)($teacher['pegawai_id'] ?: $teacher['user_id'] ?: 0);
        $userId = (int)Auth::id();

        if ($guruId <= 0 && $userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
            exit;
        }

        $inputRaw = file_get_contents('php://input');
        $input = json_decode($inputRaw, true) ?: $_POST;

        $tipe = ($input['tipe'] ?? 'tilawah') === 'hafalan' ? 'hafalan' : 'tilawah';
        $surahNomor = max(1, min(114, (int)($input['surah_nomor'] ?? ($input['nomor'] ?? 1))));
        $surahNama = trim($input['surah_nama'] ?? ($input['nama'] ?? 'Al-Fatihah'));
        $ayatNomor = max(1, (int)($input['ayat_nomor'] ?? ($input['ayat'] ?? 1)));
        $halaman = max(1, min(604, (int)($input['halaman'] ?? ($input['page'] ?? 1))));
        $mode = in_array($input['mode'] ?? '', ['ayat', 'mushaf', 'hafalan'], true) ? $input['mode'] : 'ayat';
        $timeStr = $input['time'] ?? date('d M Y, H:i');

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        try {
            // Pastikan kolom dan tabel sudah termigrasi
            AutoMigrate::run();

            $stmt = $pdo->prepare("
                INSERT INTO `quran_bookmark_guru` 
                    (`guru_id`, `user_id`, `tipe`, `surah_nomor`, `surah_nama`, `ayat_nomor`, `halaman`, `mode`, `ditandai_pada`, `created_at`)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    `user_id` = VALUES(`user_id`),
                    `surah_nomor` = VALUES(`surah_nomor`),
                    `surah_nama` = VALUES(`surah_nama`),
                    `ayat_nomor` = VALUES(`ayat_nomor`),
                    `halaman` = VALUES(`halaman`),
                    `mode` = VALUES(`mode`),
                    `ditandai_pada` = VALUES(`ditandai_pada`),
                    `created_at` = NOW()
            ");
            $stmt->execute([
                $guruId, $userId, $tipe, $surahNomor, $surahNama, $ayatNomor, $halaman, $mode, $timeStr
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Bookmark berhasil disimpan ke database',
                'bookmark' => [
                    'tipe'  => $tipe,
                    'nomor' => $surahNomor,
                    'nama'  => $surahNama,
                    'ayat'  => $ayatNomor,
                    'page'  => $halaman,
                    'mode'  => $mode,
                    'time'  => $timeStr
                ]
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan bookmark: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Pencatatan Keterlambatan Siswa
     */
    public static function keterlambatanSiswa(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        $classes = !empty($classesMap) ? array_keys($classesMap) : [];
        if (empty($classes)) {
            try {
                $allK = Database::getInstance()->findAll("SELECT nama_kelas FROM kelas WHERE is_active = 1 ORDER BY tingkat ASC, nama_kelas ASC LIMIT 10");
                foreach ($allK as $k) {
                    $classes[] = $k['nama_kelas'];
                }
            } catch (Throwable $e) {
            }
        }

        $data = PortalGuruModel::getLateStudentRecords();

        self::render('keterlambatan_siswa', [
            'pageTitle' => 'Pencatatan Keterlambatan Siswa',
            'activeTab' => 'keterlambatan',
            'summary' => $data['summary'],
            'records' => $data['records'],
            'classes' => $classes
        ]);
    }

    /**
     * Pengajuan Izin Guru (Izin Tidak Masuk & Izin Keluar/Tidak Mengajar)
     */
    public static function izin(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $classesMap = self::getTeacherClasses($teacher['pegawai_id'], $teacher['name']);
        $classes = !empty($classesMap) ? array_keys($classesMap) : [];
        if (empty($classes)) {
            try {
                $allK = Database::getInstance()->findAll("SELECT nama_kelas FROM kelas WHERE is_active = 1 ORDER BY tingkat ASC, nama_kelas ASC LIMIT 10");
                foreach ($allK as $k) {
                    $classes[] = $k['nama_kelas'];
                }
            } catch (Throwable $e) {
            }
        }

        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['id']);

        // Ambil daftar rekan guru aktif untuk pilihan guru pengganti
        $teachersList = [];
        try {
            $teachersList = Database::getInstance()->findAll("SELECT id, nama, gelar FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        } catch (Throwable $e) {
        }

        $permitsData = PortalGuruModel::getTeacherPermits($pegawaiId);

        self::render('izin', [
            'pageTitle' => 'Pengajuan Izin Guru',
            'activeTab' => 'izin',
            'summary' => $permitsData['summary'],
            'records' => $permitsData['records'],
            'classes' => $classes,
            'teachersList' => $teachersList
        ]);
    }

    /**
     * API: Submit Pengajuan Izin Guru (Real DB)
     */
    public static function submitIzin(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['id']);
        if ($pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas pegawai tidak ditemukan.']);
            exit;
        }

        $jenisIzin = trim($_POST['jenis_izin'] ?? 'tidak_masuk');
        if (!in_array($jenisIzin, ['tidak_masuk', 'keluar_mengajar'])) {
            $jenisIzin = 'tidak_masuk';
        }

        $kategori = trim($_POST['kategori'] ?? 'Sakit');
        $alasan = trim($_POST['alasan'] ?? '');
        $tanggalMulai = trim($_POST['tanggal_mulai'] ?? date('Y-m-d'));
        $tanggalSelesai = trim($_POST['tanggal_selesai'] ?? $tanggalMulai);
        $jamKeluar = !empty($_POST['jam_keluar']) ? trim($_POST['jam_keluar']) : null;
        $jamKembali = !empty($_POST['jam_kembali']) ? trim($_POST['jam_kembali']) : null;
        $kelasTerdampak = trim($_POST['kelas_terdampak'] ?? '');
        $tugasSiswa = trim($_POST['tugas_siswa'] ?? '');
        $guruPengganti = trim($_POST['guru_pengganti'] ?? '');

        if (empty($alasan)) {
            echo json_encode(['success' => false, 'message' => 'Alasan izin wajib diisi.']);
            exit;
        }

        // Upload lampiran jika ada
        $lampiran = null;
        if (!empty($_FILES['lampiran']['name']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/storage/uploads/izin';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            if (in_array($ext, $allowedExts)) {
                $filename = 'izin_' . $pegawaiId . '_' . date('Ymd_His') . '.' . $ext;
                if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $uploadDir . '/' . $filename)) {
                    $lampiran = 'storage/uploads/izin/' . $filename;
                }
            }
        }

        try {
            $db = Database::getInstance();
            // Generate nomor unik
            $nomorIzin = 'IZN-' . date('Ymd') . '-' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $db->query("
                INSERT INTO izin_guru (
                    nomor_izin, guru_id, jenis_izin, kategori, tanggal_mulai, tanggal_selesai,
                    jam_keluar, jam_kembali, kelas_terdampak, tugas_siswa, guru_pengganti,
                    alasan, lampiran_dokumen, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Persetujuan')
            ", [
                $nomorIzin,
                $pegawaiId,
                $jenisIzin,
                $kategori,
                $tanggalMulai,
                $tanggalSelesai,
                $jamKeluar,
                $jamKembali,
                $kelasTerdampak ?: null,
                $tugasSiswa ?: null,
                $guruPengganti ?: null,
                $alasan,
                $lampiran
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Pengajuan izin berhasil dikirimkan!',
                'nomor_izin' => $nomorIzin
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengajuan: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Pengajuan Cuti Guru
     */
    public static function cuti(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['id']);

        $leavesData = PortalGuruModel::getTeacherLeaves($pegawaiId);

        // Ambil master jenis cuti aktif
        $daftarJenisCuti = [];
        try {
            $daftarJenisCuti = Database::getInstance()->findAll("SELECT * FROM cuti_jenis WHERE is_active = 1 ORDER BY id ASC");
        } catch (Throwable $e) {
        }

        // Ambil data unit dan jenis pegawai pemohon
        $pegawaiRow = null;
        if ($pegawaiId > 0) {
            try {
                $pegawaiRow = Database::getInstance()->find("
                    SELECT p.*, mj.kategori as penugasan_kategori 
                    FROM pegawai p 
                    LEFT JOIN pegawai_penugasan pp ON pp.pegawai_id = p.id AND pp.status = 'Aktif'
                    LEFT JOIN master_jabatan mj ON mj.id = pp.jabatan_id
                    WHERE p.id = ? 
                    LIMIT 1
                ", [$pegawaiId]);
            } catch (Throwable $e) {}
        }
        $myUnit = trim((string)($pegawaiRow['unit_tugas'] ?? ($teacher['unit_tugas'] ?? '')));
        $isSupport = !empty($teacher['is_support']);
        if (!$isSupport && !empty($pegawaiRow['penugasan_kategori'])) {
            $isSupport = ($pegawaiRow['penugasan_kategori'] === 'support_system');
        }
        $myJenis = $isSupport ? 'Support System' : 'Guru';

        // Ambil daftar rekan pengganti ketat sesuai dengan jenis pegawai (Guru -> Guru, Staff/Support System -> Staff/Support System)
        $substituteList = [];
        try {
            $db = Database::getInstance();

            // Kondisi ketat jenis pegawai:
            $jenisCondition = $isSupport
                ? "(
                    mj.kategori = 'support_system' 
                    OR p.jenis_pegawai = 'Support System'
                    OR (
                        COALESCE(mj.kategori, '') != 'guru' 
                        AND COALESCE(p.jenis_pegawai, '') != 'Guru'
                        AND (
                            p.jabatan LIKE '%IT%' OR p.jabatan LIKE '%Media%' OR p.jabatan LIKE '%Sarpras%'
                            OR p.jabatan LIKE '%Tata Usaha%' OR p.jabatan LIKE '%TU%' OR p.jabatan LIKE '%Tendik%'
                            OR p.jabatan LIKE '%Staff%' OR p.jabatan LIKE '%Staf%' OR p.jabatan LIKE '%Keamanan%'
                            OR p.jabatan LIKE '%Satpam%' OR p.jabatan LIKE '%Kebersihan%' OR p.jabatan LIKE '%Sosial%'
                            OR p.jabatan LIKE '%Divisi%' OR p.jabatan LIKE '%Manajer%' OR p.jabatan LIKE '%Admin%'
                        )
                    )
                )"
                : "(
                    mj.kategori = 'guru' 
                    OR p.jenis_pegawai = 'Guru'
                    OR (
                        COALESCE(mj.kategori, '') != 'support_system' 
                        AND COALESCE(p.jenis_pegawai, '') != 'Support System'
                        AND (
                            p.jabatan LIKE '%Guru%' OR p.jabatan LIKE '%Wali%' OR p.jabatan LIKE '%Pengajar%'
                            OR p.jabatan LIKE '%Waka%' OR p.jabatan LIKE '%Kepala Sekolah%' OR p.jabatan LIKE '%Kepsek%'
                            OR p.jabatan IS NULL
                        )
                    )
                )";

            // 1. Prioritas Utama: Rekan dengan jenis yang SAMA dan unit yang SAMA
            $where = ["p.is_active = 1", $jenisCondition];
            $params = [];
            if ($pegawaiId > 0) {
                $where[] = "p.id != ?";
                $params[] = $pegawaiId;
            }
            if (!empty($myUnit)) {
                $where[] = "(p.unit_tugas = ? OR p.unit_tugas LIKE ?)";
                $params[] = $myUnit;
                $params[] = '%' . $myUnit . '%';
            }

            $sql = "
                SELECT DISTINCT p.id, p.nama, p.gelar, p.jabatan, p.unit_tugas, p.jenis_pegawai, mj.kategori as penugasan_kategori
                FROM pegawai p
                LEFT JOIN pegawai_penugasan pp ON pp.pegawai_id = p.id AND pp.status = 'Aktif'
                LEFT JOIN master_jabatan mj ON mj.id = pp.jabatan_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.nama ASC
            ";
            $substituteList = $db->findAll($sql, $params);

            // 2. Fallback: Jika rekan jenis sama di unit kosong, ambil rekan dengan jenis yang SAMA di seluruh unit (TIDAK BOLEH lintas jenis)
            if (empty($substituteList)) {
                $fallbackWhere = ["p.is_active = 1", $jenisCondition];
                $fallbackParams = [];
                if ($pegawaiId > 0) {
                    $fallbackWhere[] = "p.id != ?";
                    $fallbackParams[] = $pegawaiId;
                }
                $sqlFallback = "
                    SELECT DISTINCT p.id, p.nama, p.gelar, p.jabatan, p.unit_tugas, p.jenis_pegawai, mj.kategori as penugasan_kategori
                    FROM pegawai p
                    LEFT JOIN pegawai_penugasan pp ON pp.pegawai_id = p.id AND pp.status = 'Aktif'
                    LEFT JOIN master_jabatan mj ON mj.id = pp.jabatan_id
                    WHERE " . implode(' AND ', $fallbackWhere) . "
                    ORDER BY p.nama ASC
                ";
                $substituteList = $db->findAll($sqlFallback, $fallbackParams);
            }
        } catch (Throwable $e) {
            $substituteList = [];
        }

        self::render('cuti', [
            'pageTitle' => 'Pengajuan Cuti Pegawai',
            'activeTab' => 'cuti',
            'quotas' => $leavesData['quotas'],
            'records' => $leavesData['records'],
            'daftarJenisCuti' => $daftarJenisCuti,
            'substituteList' => $substituteList,
            'teachersList' => $substituteList,
            'userUnit' => $myUnit,
            'userJenis' => $myJenis,
            'isSupport' => $isSupport
        ]);
    }

    /**
     * API: Submit Permohonan Cuti Pegawai (Real DB)
     */
    public static function submitCuti(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['id']);
        if ($pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas pegawai tidak ditemukan.']);
            exit;
        }

        require_once BASE_PATH . '/modules/kelola-cuti/models/CutiModel.php';
        CutiModel::init();

        $jenisCutiId = (int) ($_POST['jenis_cuti_id'] ?? 0);
        $tanggalPilihan = $_POST['tanggal_pilihan'] ?? ($_POST['tanggal_cuti_list'] ?? null);
        $parsedDates = CutiModel::parseTanggalPilihan($tanggalPilihan);

        $tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
        $tanggalSelesai = trim($_POST['tanggal_selesai'] ?? $tanggalMulai);
        if (!empty($parsedDates)) {
            $tanggalMulai = $parsedDates[0];
            $tanggalSelesai = end($parsedDates);
        }

        $alasan = trim($_POST['alasan'] ?? '');
        $pelimpahanTugas = trim($_POST['pelimpahan_tugas'] ?? '');
        $keteranganTugas = trim($_POST['keterangan_tugas'] ?? '');
        $alamatSelamaCuti = trim($_POST['alamat_selama_cuti'] ?? '');
        $kontakDarurat = trim($_POST['kontak_darurat'] ?? '');

        if ($jenisCutiId <= 0 || empty($tanggalMulai) || empty($alasan)) {
            echo json_encode(['success' => false, 'message' => 'Jenis cuti, tanggal cuti, dan alasan permohonan wajib diisi lengkap.']);
            exit;
        }

        // Pengelolaan Foto Bukti (Maksimal 2 Foto, Auto Convert <= 500KB)
        $uploadDir = BASE_PATH . '/public/uploads/cuti';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        @chmod($uploadDir, 0777);

        $processPhoto500KB = function($fileKey, $postDataKey, $idx) use ($uploadDir, $pegawaiId) {
            $savedRelativePath = null;
            $tmpPath = null;
            $isTempCreated = false;

            if (!empty($_FILES[$fileKey]['name']) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $tmpPath = $_FILES[$fileKey]['tmp_name'];
            } elseif (!empty($_POST[$postDataKey]) && strpos((string)$_POST[$postDataKey], 'data:image/') === 0) {
                $parts = explode(',', (string)$_POST[$postDataKey], 2);
                if (count($parts) === 2) {
                    $decoded = base64_decode($parts[1]);
                    if ($decoded !== false && strlen($decoded) > 0) {
                        $tempFile = tempnam(sys_get_temp_dir(), 'cuti_img_');
                        file_put_contents($tempFile, $decoded);
                        $tmpPath = $tempFile;
                        $isTempCreated = true;
                    }
                }
            }

            if ($tmpPath && file_exists($tmpPath) && filesize($tmpPath) > 0) {
                $filename = 'cuti_' . $pegawaiId . '_' . date('Ymd_His') . '_' . $idx . '_' . substr(md5(uniqid()), 0, 6) . '.jpg';
                $destination = $uploadDir . '/' . $filename;
                $maxBytes = 500 * 1024; // 500 KB

                try {
                    if (function_exists('imagecreatefromstring') && @getimagesize($tmpPath)) {
                        $raw = @file_get_contents($tmpPath);
                        $src = $raw ? @imagecreatefromstring($raw) : false;
                        if ($src) {
                            // Max dimension 1600px
                            $w = imagesx($src);
                            $h = imagesy($src);
                            $maxDim = 1600;
                            if ($w > $maxDim || $h > $maxDim) {
                                if ($w > $h) {
                                    $newW = $maxDim;
                                    $newH = (int)round(($h * $maxDim) / $w);
                                } else {
                                    $newH = $maxDim;
                                    $newW = (int)round(($w * $maxDim) / $h);
                                }
                                $dst = imagecreatetruecolor($newW, $newH);
                                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
                                imagedestroy($src);
                                $src = $dst;
                            }

                            $quality = 85;
                            @imagejpeg($src, $destination, $quality);
                            while (file_exists($destination) && filesize($destination) > $maxBytes && $quality > 25) {
                                $quality -= 10;
                                @imagejpeg($src, $destination, $quality);
                            }
                            imagedestroy($src);
                            if (file_exists($destination) && filesize($destination) > 0) {
                                $savedRelativePath = 'uploads/cuti/' . $filename;
                            }
                        }
                    }

                    if (!$savedRelativePath) {
                        if (@copy($tmpPath, $destination) || @move_uploaded_file($tmpPath, $destination)) {
                            $savedRelativePath = 'uploads/cuti/' . $filename;
                        }
                    }
                } catch (Throwable $ePhoto) {
                    error_log('Error processing cuti photo: ' . $ePhoto->getMessage());
                }

                if ($isTempCreated && file_exists($tmpPath)) {
                    @unlink($tmpPath);
                }
            }
            return $savedRelativePath;
        };

        $lampiran1 = $processPhoto500KB('foto_bukti_1', 'foto_bukti_1_data', 1) 
                  ?? ($processPhoto500KB('lampiran', '', 1) ?? null);
        $lampiran2 = $processPhoto500KB('foto_bukti_2', 'foto_bukti_2_data', 2);

        try {
            $payload = [
                'pegawai_id' => $pegawaiId,
                'jenis_cuti_id' => $jenisCutiId,
                'tanggal_pilihan' => $parsedDates,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'alasan' => $alasan,
                'pelimpahan_tugas' => $pelimpahanTugas,
                'keterangan_tugas' => $keteranganTugas,
                'alamat_selama_cuti' => $alamatSelamaCuti,
                'kontak_darurat' => $kontakDarurat,
                'file_lampiran' => $lampiran1,
                'file_lampiran_2' => $lampiran2
            ];

            $userId = (int) ($teacher['user_id'] ?? 0);
            $userName = $teacher['name'] ?? 'Pegawai';
            $newId = CutiModel::createPengajuan($payload, $userId, $userName);

            $newRow = Database::getInstance()->find("
                SELECT cp.*, cj.nama_jenis, cj.kode as jenis_kode, cj.kategori as jenis_kategori
                FROM cuti_pengajuan cp
                LEFT JOIN cuti_jenis cj ON cp.jenis_cuti_id = cj.id
                WHERE cp.id = ?
            ", [$newId]);

            $nomorPengajuan = $newRow['nomor_pengajuan'] ?? ('CT-' . $newId);
            $tglBuat = !empty($newRow['created_at']) ? date('d M Y, H:i WITA', strtotime($newRow['created_at'])) : date('d M Y, H:i WITA');

            $timeline = [
                ['step' => 'Pengajuan Diajukan', 'time' => $tglBuat, 'done' => true, 'status' => 'Disetujui']
            ];
            $realSteps = [];
            try {
                $realSteps = Database::getInstance()->findAll("
                    SELECT * FROM `cuti_pengajuan_step`
                    WHERE `pengajuan_id` = ?
                    ORDER BY `step_order` ASC, `id` ASC
                ", [$newId]);
            } catch (Throwable $eSteps) {}

            if (!empty($realSteps)) {
                foreach ($realSteps as $st) {
                    $label = $st['step_nama'] . (!empty($st['approver_role']) ? ' (' . $st['approver_role'] . ')' : '');
                    $timeline[] = [
                        'step' => $label,
                        'time' => 'Menunggu',
                        'done' => false,
                        'status' => 'Pending'
                    ];
                }
            } else {
                $timeline[] = ['step' => 'Verifikasi Atasan / Kepala Unit', 'time' => 'Menunggu', 'done' => false, 'status' => 'Pending'];
                $timeline[] = ['step' => 'Persetujuan Yayasan / Pimpinan', 'time' => 'Menunggu', 'done' => false, 'status' => 'Pending'];
            }

            $displayTanggal = CutiModel::formatTanggalCutiDisplay($newRow, 'd M Y');
            $cutiDatesList = CutiModel::parseTanggalPilihan($newRow['tanggal_cuti_list'] ?? null);

            $formattedRecord = [
                'raw_id' => (int)$newRow['id'],
                'id' => $nomorPengajuan,
                'type' => $newRow['nama_jenis'] ?? ($newRow['kategori_cuti'] . ' Cuti'),
                'startDate' => !empty($newRow['tanggal_mulai']) ? date('d M Y', strtotime($newRow['tanggal_mulai'])) : '-',
                'endDate' => !empty($newRow['tanggal_selesai']) ? date('d M Y', strtotime($newRow['tanggal_selesai'])) : '-',
                'datesDisplay' => $displayTanggal,
                'datesList' => $cutiDatesList,
                'daysCount' => (int)($newRow['jumlah_hari'] ?? 1),
                'reason' => $newRow['alasan'] ?? '-',
                'emergencyContact' => $newRow['kontak_darurat'] ?? '-',
                'address' => $newRow['alamat_selama_cuti'] ?? '-',
                'substitute' => $newRow['pelimpahan_tugas'] ?? '-',
                'keteranganTugas' => $newRow['keterangan_tugas'] ?? null,
                'document' => $newRow['file_lampiran'] ?? null,
                'document2' => $newRow['file_lampiran_2'] ?? null,
                'status' => 'Pending',
                'statusBadge' => 'amber',
                'submittedAt' => $tglBuat,
                'timeline' => $timeline,
                'catatan' => null,
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Permohonan cuti berhasil diajukan!',
                'nomor_pengajuan' => $nomorPengajuan,
                'record' => $formattedRecord
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * API: Batalkan Permohonan Cuti oleh Pegawai (Portal Mobile)
     */
    public static function cancelCuti(): void
    {
        $teacher = self::getLoggedInTeacher();
        self::releaseSessionLock();
        header('Content-Type: application/json; charset=utf-8');

        $pegawaiId = (int) ($teacher['pegawai_id'] ?: $teacher['id']);
        if ($pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identitas pegawai tidak ditemukan.']);
            exit;
        }

        $pengajuanId = (int) ($_POST['pengajuan_id'] ?? 0);
        $alasanBatal = trim($_POST['alasan_batal'] ?? '');

        if ($pengajuanId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID Pengajuan cuti tidak valid.']);
            exit;
        }

        require_once BASE_PATH . '/modules/kelola-cuti/models/CutiModel.php';
        CutiModel::init();

        $db = Database::getInstance();
        $row = $db->find("SELECT * FROM cuti_pengajuan WHERE id = ? AND pegawai_id = ?", [$pengajuanId, $pegawaiId]);
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Permohonan cuti tidak ditemukan atau bukan milik akun Anda.']);
            exit;
        }

        if ($row['status'] !== 'Pending') {
            echo json_encode(['success' => false, 'message' => 'Hanya permohonan yang berstatus Menunggu Persetujuan (Pending) yang dapat dibatalkan.']);
            exit;
        }

        try {
            $nomor = $row['nomor_pengajuan'] ?: ('CT-' . $pengajuanId);
            CutiModel::deletePengajuan($pengajuanId);

            echo json_encode([
                'success' => true,
                'message' => 'Permohonan cuti [' . $nomor . '] berhasil dibatalkan dan datanya telah dihapus.'
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
