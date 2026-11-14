<?php
/**
 * JurnalGuruAdminController
 * Modul Kelola Jurnal Guru (Desktop Admin)
 * Mencakup 5 Fitur Utama:
 * 1. Dashboard Jurnal
 * 2. Data Jurnal (Nama, Kegiatan, Kelas, Mapel, Materi, Presensi - sesuai Portal Guru)
 * 3. Rekap Jurnal (Nama Guru, Jumlah PIC, Sesi Terisi & Akumulasi JP)
 * 4. Data Rekap Harian Matrix (✓ Centang jika isi, ✕ Silang jika tidak isi per tanggal)
 * 5. Rekap Jurnal Tidak Terisi (Daftar jadwal KBM yang terlewat / belum diisi jurnal)
 */

class JurnalGuruAdminController
{
    /**
     * Helper nama hari Indonesia dari tanggal Y-m-d
     */
    private static function getHariIndonesia(string $dateStr): string
    {
        $map = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $en = date('l', strtotime($dateStr));
        return $map[$en] ?? 'Senin';
    }

    /**
     * Helper singkatan hari 3 huruf
     */
    private static function getHariSingkat(string $hari): string
    {
        $map = [
            'Senin'  => 'Sen',
            'Selasa' => 'Sel',
            'Rabu'   => 'Rab',
            'Kamis'  => 'Kam',
            'Jumat'  => 'Jum',
            'Sabtu'  => 'Sab',
            'Minggu' => 'Min',
        ];
        return $map[$hari] ?? substr($hari, 0, 3);
    }

    /**
     * Halaman Utama Kelola Jurnal Guru (5 Tab Terpadu)
     */
    public static function index(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // 1. Parameter Filter & Tab Aktif
        $activeTab = trim($_GET['tab'] ?? 'dashboard');
        if (!in_array($activeTab, ['dashboard', 'jurnal_saya', 'data', 'rekap', 'matrix', 'kosong'], true)) {
            $activeTab = 'dashboard';
        }

        $search    = trim($_GET['search'] ?? '');
        $guruId    = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : null;
        $kelas     = trim($_GET['kelas'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate   = trim($_GET['end_date'] ?? '');

        // Rentang tanggal default untuk Rekap, Matrix & Jurnal Kosong (Default: awal bulan ini s/d hari ini, maks 31 hari)
        $periodStart = $startDate !== '' ? $startDate : date('Y-m-01');
        $periodEnd   = $endDate !== '' ? $endDate : date('Y-m-d');
        if (strtotime($periodStart) > strtotime($periodEnd)) {
            $tmp = $periodStart;
            $periodStart = $periodEnd;
            $periodEnd = $tmp;
        }

        // 2. Bangun Query Filter untuk Tab 2 (Data Jurnal)
        $whereClauses = ['1=1'];
        $params = [];

        if ($search !== '') {
            $whereClauses[] = '(jm.kode_jurnal LIKE ? OR jm.nama_guru LIKE ? OR p.nama LIKE ? OR jm.materi_pokok LIKE ? OR jm.uraian_kegiatan LIKE ? OR jm.mata_pelajaran LIKE ? OR jm.kelas LIKE ?)';
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($guruId !== null) {
            $whereClauses[] = 'jm.guru_id = ?';
            $params[] = $guruId;
        }

        if ($kelas !== '') {
            $whereClauses[] = 'jm.kelas = ?';
            $params[] = $kelas;
        }

        if ($status !== '' && in_array($status, ['draft', 'selesai', 'tervalidasi'], true)) {
            $whereClauses[] = 'jm.status = ?';
            $params[] = $status;
        }

        if ($startDate !== '') {
            $whereClauses[] = 'jm.tanggal >= ?';
            $params[] = $startDate;
        }

        if ($endDate !== '') {
            $whereClauses[] = 'jm.tanggal <= ?';
            $params[] = $endDate;
        }

        $whereSql = implode(' AND ', $whereClauses);

        // 3. KPI Statistics
        $stmtKpi = $pdo->prepare("
            SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(jm.jumlah_jp), 0) as total_jp,
                COUNT(DISTINCT jm.guru_id) as total_guru_aktif
            FROM jurnal_mengajar jm
            LEFT JOIN pegawai p ON jm.guru_id = p.id
            WHERE {$whereSql}
        ");
        $stmtKpi->execute($params);
        $kpiFiltered = $stmtKpi->fetch(PDO::FETCH_ASSOC);

        // KPI Hari Ini
        $stmtToday = $pdo->query("
            SELECT 
                COUNT(*) as count_today,
                COALESCE(SUM(jumlah_jp), 0) as jp_today,
                COUNT(DISTINCT guru_id) as guru_today
            FROM jurnal_mengajar 
            WHERE tanggal = CURDATE()
        ");
        $kpiToday = $stmtToday->fetch(PDO::FETCH_ASSOC);

        // KPI Bulan Ini (sargable date range agar indeks tanggal terpakai)
        $stmtMonth = $pdo->prepare("
            SELECT 
                COUNT(*) as count_month,
                COALESCE(SUM(jumlah_jp), 0) as jp_month
            FROM jurnal_mengajar 
            WHERE tanggal >= ? AND tanggal <= ?
        ");
        $stmtMonth->execute([date('Y-m-01'), date('Y-m-t')]);
        $kpiMonth = $stmtMonth->fetch(PDO::FETCH_ASSOC);

        // 4. Pagination & Query untuk Tab 2 (Data Jurnal)
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $totalRows = (int)($kpiFiltered['total_count'] ?? 0);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $queryList = "
            SELECT 
                jm.*,
                COALESCE(p.nama, jm.nama_guru, 'Guru') as guru_nama_lengkap,
                p.niy,
                p.nik,
                p.no_wa,
                p.foto as guru_foto,
                jp.jam_mulai,
                jp.jam_selesai,
                jp.ruangan
            FROM jurnal_mengajar jm
            LEFT JOIN pegawai p ON jm.guru_id = p.id
            LEFT JOIN jadwal_pelajaran jp ON jm.jadwal_id = jp.id
            WHERE {$whereSql}
            ORDER BY jm.tanggal DESC, jm.jam_ke ASC, jm.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmtList = $pdo->prepare($queryList);
        $stmtList->execute($params);
        $jurnalList = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        // 5. Data Dropdown Guru & Kelas
        $guruOptions = $pdo->query("
            SELECT id, nama, niy, nik, no_wa, jabatan 
            FROM pegawai 
            WHERE status_pegawai != 'Keluar' OR status_pegawai IS NULL
            ORDER BY nama ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $guruById = [];
        $guruByName = [];
        foreach ($guruOptions as $g) {
            $guruById[(int)$g['id']] = $g;
            $guruByName[strtolower(trim($g['nama']))] = $g;
        }

        $kelasOptions = [];
        try {
            $kelasOptions = $pdo->query("SELECT DISTINCT nama_kelas FROM kelas WHERE nama_kelas IS NOT NULL AND nama_kelas != '' ORDER BY nama_kelas ASC")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {}

        if (empty($kelasOptions)) {
            try {
                $kelasOptions = $pdo->query("SELECT DISTINCT nama_kelas FROM jadwal_pelajaran WHERE nama_kelas IS NOT NULL AND nama_kelas != '' ORDER BY nama_kelas ASC")->fetchAll(PDO::FETCH_COLUMN);
            } catch (Throwable $e) {}
        }
        if (empty($kelasOptions)) {
            $kelasOptions = $pdo->query("SELECT DISTINCT kelas FROM jurnal_mengajar WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC")->fetchAll(PDO::FETCH_COLUMN);
        }

        // 6. Ambil Jadwal Pelajaran Aktif (Grup Jadwal Aktif) & Penugasan Mengajar (PIC)
        $activeSchedules = [];
        try {
            $activeGrupIds = $pdo->query("SELECT id FROM jadwal_grup WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($activeGrupIds)) {
                $fallbackId = $pdo->query("SELECT id FROM jadwal_grup ORDER BY id DESC LIMIT 1")->fetchColumn();
                if ($fallbackId) {
                    $activeGrupIds = [(int)$fallbackId];
                }
            }
            $grupWhere = '';
            $schParams = [];
            if (!empty($activeGrupIds)) {
                $ph = implode(',', array_fill(0, count($activeGrupIds), '?'));
                $grupWhere = "WHERE jp.grup_id IN ({$ph})";
                $schParams = array_map('intval', $activeGrupIds);
            }
            $sqlSch = "
                SELECT jp.*,
                       COALESCE(p.nama, jp.nama_guru, 'Guru') as resolved_nama_guru,
                       p.niy,
                       p.nik,
                       p.no_wa
                FROM jadwal_pelajaran jp
                LEFT JOIN pegawai p ON jp.pegawai_id = p.id
                {$grupWhere}
                ORDER BY FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jp.jam_ke ASC
            ";
            $stmtSch = $pdo->prepare($sqlSch);
            $stmtSch->execute($schParams);
            $activeSchedules = $stmtSch->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $activeSchedules = [];
        }

        // Ambil juga data Penugasan Mengajar (PIC Kelas & Mapel) jika ada
        $penugasanPicRows = [];
        try {
            $penugasanPicRows = $pdo->query("
                SELECT m.*, p.nama as nama_guru, p.niy, p.nik, p.no_wa
                FROM pegawai_penugasan_mengajar m
                JOIN pegawai p ON m.pegawai_id = p.id
                ORDER BY p.nama ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $penugasanPicRows = [];
        }

        // 7. Ambil Seluruh Jurnal pada Rentang Periode ($periodStart s/d $periodEnd) untuk Analitik, Rekap PIC, Matrix & Jurnal Kosong
        $stmtPeriodJurnal = $pdo->prepare("
            SELECT jm.*, COALESCE(p.nama, jm.nama_guru, 'Guru') as resolved_nama_guru, p.niy, p.nik, p.no_wa
            FROM jurnal_mengajar jm
            LEFT JOIN pegawai p ON jm.guru_id = p.id
            WHERE jm.tanggal >= ? AND jm.tanggal <= ?
            ORDER BY jm.tanggal ASC, jm.jam_ke ASC
        ");
        $stmtPeriodJurnal->execute([$periodStart, $periodEnd]);
        $periodJournals = $stmtPeriodJurnal->fetchAll(PDO::FETCH_ASSOC);

        // Kelompokkan jurnal periode berdasarkan guru & tanggal
        $journalsByGuruDate = []; // [guruKey][Y-m-d] => array of journal rows
        $journalsByDate     = []; // [Y-m-d] => array of journal rows
        $teacherMetaMap     = []; // [guruKey] => ['id', 'nama', 'niy', 'no_wa', 'pic_classes' => [], 'pic_subjects' => [], 'schedules_by_day' => []]

        // Helper untuk mendapatkan key unik guru
        $resolveGuruKey = function (?int $pId, string $gName) use ($guruById, $guruByName): string {
            if ($pId && isset($guruById[$pId])) {
                return 'G_' . $pId;
            }
            $clean = strtolower(trim($gName));
            if ($clean !== '' && isset($guruByName[$clean])) {
                return 'G_' . $guruByName[$clean]['id'];
            }
            return 'N_' . md5($clean ?: 'guru');
        };

        $initTeacherMeta = function (string $gKey, ?int $pId, string $gName, ?string $niy = '-', ?string $noWa = '') use (&$teacherMetaMap, $guruById, $guruByName) {
            if (!isset($teacherMetaMap[$gKey])) {
                $clean = strtolower(trim($gName));
                $ref = ($pId && isset($guruById[$pId])) ? $guruById[$pId] : ($guruByName[$clean] ?? null);
                $teacherMetaMap[$gKey] = [
                    'key'              => $gKey,
                    'guru_id'          => $ref ? (int)$ref['id'] : (int)$pId,
                    'nama'             => $ref ? $ref['nama'] : ($gName ?: 'Guru'),
                    'niy'              => $ref ? ($ref['niy'] ?: ($ref['nik'] ?: '-')) : ($niy ?: '-'),
                    'no_wa'            => $ref ? ($ref['no_wa'] ?? '') : ($noWa ?? ''),
                    'pic_classes'      => [],
                    'pic_subjects'     => [],
                    'pic_assignments'  => 0,
                    'pic_weekly_jp'    => 0,
                    'schedules_by_day' => [], // ['Senin' => [...], ...]
                ];
            }
        };

        // 7a. Daftarkan guru dari Penugasan Mengajar (PIC)
        foreach ($penugasanPicRows as $ppm) {
            $pId = (int)($ppm['pegawai_id'] ?? 0);
            $gName = $ppm['nama_guru'] ?? 'Guru';
            $gKey = $resolveGuruKey($pId, $gName);
            $initTeacherMeta($gKey, $pId, $gName, $ppm['niy'] ?? ($ppm['nik'] ?? '-'), $ppm['no_wa'] ?? '');
            $teacherMetaMap[$gKey]['pic_assignments']++;
            $teacherMetaMap[$gKey]['pic_weekly_jp'] += (int)($ppm['jumlah_jp'] ?? 2);
            if (!empty($ppm['nama_kelas'])) {
                $teacherMetaMap[$gKey]['pic_classes'][$ppm['nama_kelas']] = true;
            }
            if (!empty($ppm['mata_pelajaran'])) {
                $teacherMetaMap[$gKey]['pic_subjects'][$ppm['mata_pelajaran']] = true;
            }
        }

        // 7b. Daftarkan guru & jadwal harian dari jadwal_pelajaran aktif
        // Kelompokkan slot jadwal berurutan (kelas + mapel + guru + hari yang sama) menjadi 1 sesi pertemuan KBM
        $groupedDailySchedules = [];
        foreach ($activeSchedules as $sch) {
            $pId = (int)($sch['pegawai_id'] ?? 0);
            $gName = $sch['resolved_nama_guru'] ?? ($sch['nama_guru'] ?? 'Guru');
            $gKey = $resolveGuruKey($pId, $gName);
            $initTeacherMeta($gKey, $pId, $gName, $sch['niy'] ?? ($sch['nik'] ?? '-'), $sch['no_wa'] ?? '');

            $kls = trim($sch['nama_kelas'] ?? '');
            $mpl = trim($sch['mata_pelajaran'] ?? '');
            $hari = trim($sch['hari'] ?? 'Senin');

            if ($kls !== '') $teacherMetaMap[$gKey]['pic_classes'][$kls] = true;
            if ($mpl !== '') $teacherMetaMap[$gKey]['pic_subjects'][$mpl] = true;

            $sessionKey = $gKey . '|' . $hari . '|' . $kls . '|' . $mpl;
            if (!isset($groupedDailySchedules[$sessionKey])) {
                $groupedDailySchedules[$sessionKey] = [
                    'jadwal_id'      => (int)$sch['id'],
                    'guru_key'       => $gKey,
                    'guru_id'        => $teacherMetaMap[$gKey]['guru_id'],
                    'nama_guru'      => $teacherMetaMap[$gKey]['nama'],
                    'niy'            => $teacherMetaMap[$gKey]['niy'],
                    'no_wa'          => $teacherMetaMap[$gKey]['no_wa'],
                    'hari'           => $hari,
                    'kelas'          => $kls,
                    'mata_pelajaran' => $mpl,
                    'jam_ke_list'    => [$sch['jam_ke']],
                    'jam_mulai'      => $sch['jam_mulai'],
                    'jam_selesai'    => $sch['jam_selesai'],
                    'jumlah_jp'      => 1,
                ];
            } else {
                $groupedDailySchedules[$sessionKey]['jam_ke_list'][] = $sch['jam_ke'];
                $groupedDailySchedules[$sessionKey]['jam_selesai'] = $sch['jam_selesai'];
                $groupedDailySchedules[$sessionKey]['jumlah_jp']++;
            }
        }

        foreach ($groupedDailySchedules as $sess) {
            $gKey = $sess['guru_key'];
            $hari = $sess['hari'];
            $jamKeStr = count($sess['jam_ke_list']) > 1
                ? min($sess['jam_ke_list']) . '-' . max($sess['jam_ke_list'])
                : (string)($sess['jam_ke_list'][0] ?? '1');
            $sess['jam_ke'] = $jamKeStr;
            $teacherMetaMap[$gKey]['schedules_by_day'][$hari][] = $sess;
        }

        // 7c. Daftarkan juga guru dari data jurnal_mengajar yang sudah masuk
        foreach ($periodJournals as $jr) {
            $pId = !empty($jr['guru_id']) ? (int)$jr['guru_id'] : null;
            $gName = $jr['resolved_nama_guru'] ?? ($jr['nama_guru'] ?? 'Guru');
            $gKey = $resolveGuruKey($pId, $gName);
            $initTeacherMeta($gKey, $pId, $gName, $jr['niy'] ?? ($jr['nik'] ?? '-'), $jr['no_wa'] ?? '');

            if (!empty($jr['kelas'])) {
                $teacherMetaMap[$gKey]['pic_classes'][$jr['kelas']] = true;
            }
            if (!empty($jr['mata_pelajaran'])) {
                $teacherMetaMap[$gKey]['pic_subjects'][$jr['mata_pelajaran']] = true;
            }

            $dt = $jr['tanggal'];
            $journalsByGuruDate[$gKey][$dt][] = $jr;
            $journalsByDate[$dt][] = $jr;
        }

        // Jika belum ada guru sama sekali di penugasan/jadwal/jurnal, tampilkan daftar guru dari tabel pegawai
        if (empty($teacherMetaMap)) {
            foreach (array_slice($guruOptions, 0, 25) as $g) {
                $gKey = 'G_' . $g['id'];
                $initTeacherMeta($gKey, (int)$g['id'], $g['nama'], $g['niy'] ?: ($g['nik'] ?: '-'), $g['no_wa'] ?? '');
            }
        }

        // Filter teacherMetaMap jika filter guru_id atau kelas dipilih
        $filteredTeacherKeys = array_keys($teacherMetaMap);
        if ($guruId !== null) {
            $filteredTeacherKeys = array_filter($filteredTeacherKeys, function ($k) use ($teacherMetaMap, $guruId) {
                return (int)($teacherMetaMap[$k]['guru_id'] ?? 0) === $guruId;
            });
        }
        if ($kelas !== '') {
            $filteredTeacherKeys = array_filter($filteredTeacherKeys, function ($k) use ($teacherMetaMap, $kelas) {
                return isset($teacherMetaMap[$k]['pic_classes'][$kelas]);
            });
        }

        // 8. Bangun Daftar Tanggal untuk Matrix & Deteksi Jurnal Tidak Terisi (Maks 31 Hari agar ringan & rapi)
        $matrixDates = [];
        $curTs = strtotime($periodStart);
        $endTs = strtotime($periodEnd);
        $maxDays = 31;
        if (($endTs - $curTs) / 86400 > $maxDays) {
            $curTs = $endTs - ($maxDays * 86400);
        }

        $todayYmd = date('Y-m-d');
        while ($curTs <= $endTs) {
            $dStr = date('Y-m-d', $curTs);
            $hariIndo = self::getHariIndonesia($dStr);
            $matrixDates[] = [
                'date'       => $dStr,
                'day_num'    => date('d', $curTs),
                'month_short'=> date('M', $curTs),
                'hari'       => $hariIndo,
                'hari_short' => self::getHariSingkat($hariIndo),
                'is_sunday'  => ($hariIndo === 'Minggu'),
                'is_future'  => ($dStr > $todayYmd),
                'is_today'   => ($dStr === $todayYmd),
            ];
            $curTs = strtotime('+1 day', $curTs);
        }

        // 9. Komputasi Tab 3 (Rekap Jurnal & PIC), Tab 4 (Matrix Harian ✓/✕), dan Tab 5 (Rekap Jurnal Tidak Terisi)
        $rekapGuruList = [];
        $matrixRows    = [];
        $unfilledList  = [];

        foreach ($filteredTeacherKeys as $gKey) {
            $meta = $teacherMetaMap[$gKey];
            $picClassesList  = array_keys($meta['pic_classes']);
            $picSubjectsList = array_keys($meta['pic_subjects']);
            $jumlahPicKelas  = max(count($picClassesList), (int)$meta['pic_assignments']);

            $totalTargetSesi = 0;
            $totalTerisiSesi = 0;
            $totalJpTerisi   = 0;
            $totalHariTerisi = 0;
            $totalHariKosong = 0;
            $dateCells       = [];

            foreach ($matrixDates as $dInfo) {
                $dStr = $dInfo['date'];
                $hari = $dInfo['hari'];
                $daySchedules = $meta['schedules_by_day'][$hari] ?? [];
                if ($kelas !== '') {
                    $daySchedules = array_values(array_filter($daySchedules, fn($s) => $s['kelas'] === $kelas));
                }

                $dayJournals = $journalsByGuruDate[$gKey][$dStr] ?? [];
                if ($kelas !== '') {
                    $dayJournals = array_values(array_filter($dayJournals, fn($j) => $j['kelas'] === $kelas));
                }

                $filledCount   = count($dayJournals);
                $expectedCount = count($daySchedules);
                $jpDay         = 0;
                $detailsArr    = [];

                foreach ($dayJournals as $dj) {
                    $jpDay += (int)($dj['jumlah_jp'] ?? 2);
                    $detailsArr[] = ($dj['kelas'] ?? '-') . ' (' . ($dj['mata_pelajaran'] ?? '-') . '): ' . ($dj['materi_pokok'] ?? '-');
                }

                $totalTerisiSesi += $filledCount;
                $totalJpTerisi   += $jpDay;

                if ($dInfo['is_sunday']) {
                    $cellStatus = $filledCount > 0 ? 'filled' : 'off';
                } elseif ($dInfo['is_future']) {
                    $cellStatus = $filledCount > 0 ? 'filled' : 'future';
                } else {
                    // Hari kerja <= hari ini
                    if ($expectedCount > 0) {
                        $totalTargetSesi += $expectedCount;
                        if ($filledCount >= $expectedCount) {
                            $cellStatus = 'filled';
                            $totalHariTerisi++;
                        } elseif ($filledCount > 0) {
                            $cellStatus = 'partial';
                            $totalHariTerisi++;
                        } else {
                            $cellStatus = 'unfilled';
                            $totalHariKosong++;
                        }

                        // Cek jadwal mana saja yang belum diisi pada tanggal ini untuk Tab 5 (Jurnal Tidak Terisi)
                        foreach ($daySchedules as $schItem) {
                            $isMatched = false;
                            foreach ($dayJournals as $dj) {
                                if (
                                    (!empty($dj['jadwal_id']) && (int)$dj['jadwal_id'] === (int)$schItem['jadwal_id']) ||
                                    (strcasecmp(trim($dj['kelas'] ?? ''), trim($schItem['kelas'])) === 0 &&
                                     strcasecmp(trim($dj['mata_pelajaran'] ?? ''), trim($schItem['mata_pelajaran'])) === 0)
                                ) {
                                    $isMatched = true;
                                    break;
                                }
                            }
                            if (!$isMatched) {
                                $startFmt = !empty($schItem['jam_mulai']) ? date('H:i', strtotime($schItem['jam_mulai'])) : '';
                                $endFmt   = !empty($schItem['jam_selesai']) ? date('H:i', strtotime($schItem['jam_selesai'])) : '';
                                $unfilledList[] = [
                                    'tanggal'        => $dStr,
                                    'hari'           => $hari,
                                    'jadwal_id'      => $schItem['jadwal_id'],
                                    'guru_id'        => $meta['guru_id'],
                                    'nama_guru'      => $meta['nama'],
                                    'niy'            => $meta['niy'],
                                    'no_wa'          => $meta['no_wa'],
                                    'kelas'          => $schItem['kelas'],
                                    'mata_pelajaran' => $schItem['mata_pelajaran'],
                                    'jam_ke'         => $schItem['jam_ke'],
                                    'jumlah_jp'      => $schItem['jumlah_jp'],
                                    'waktu'          => ($startFmt && $endFmt) ? "{$startFmt} - {$endFmt}" : "Jam ke-{$schItem['jam_ke']}",
                                ];
                            }
                        }
                    } else {
                        // Guru belum punya jadwal spesifik di hari ini di jadwal_pelajaran
                        if ($filledCount > 0) {
                            $cellStatus = 'filled';
                            $totalTargetSesi += $filledCount;
                            $totalHariTerisi++;
                        } elseif (!empty($picClassesList)) {
                            // Punya PIC kelas tapi tidak mengisi jurnal di hari kerja aktif
                            $cellStatus = 'unfilled';
                            $totalTargetSesi += 1;
                            $totalHariKosong++;
                            $unfilledList[] = [
                                'tanggal'        => $dStr,
                                'hari'           => $hari,
                                'jadwal_id'      => 0,
                                'guru_id'        => $meta['guru_id'],
                                'nama_guru'      => $meta['nama'],
                                'niy'            => $meta['niy'],
                                'no_wa'          => $meta['no_wa'],
                                'kelas'          => $picClassesList[0] ?? 'Kelas Diampu',
                                'mata_pelajaran' => $picSubjectsList[0] ?? 'Mata Pelajaran',
                                'jam_ke'         => '1-2',
                                'jumlah_jp'      => 2,
                                'waktu'          => 'Jadwal KBM Harian',
                            ];
                        } else {
                            $cellStatus = 'off';
                        }
                    }
                }

                $dateCells[$dStr] = [
                    'status'         => $cellStatus, // filled | partial | unfilled | off | future
                    'filled_count'   => $filledCount,
                    'expected_count' => $expectedCount,
                    'jp'             => $jpDay,
                    'details'        => implode(' | ', $detailsArr),
                ];
            }

            $unfilledSesiCount = max(0, $totalTargetSesi - $totalTerisiSesi);
            $compliancePct = $totalTargetSesi > 0
                ? min(100, (int)round(($totalTerisiSesi / $totalTargetSesi) * 100))
                : ($totalTerisiSesi > 0 ? 100 : 0);

            $rowSummary = [
                'guru_key'          => $gKey,
                'guru_id'           => $meta['guru_id'],
                'nama'              => $meta['nama'],
                'niy'               => $meta['niy'],
                'no_wa'             => $meta['no_wa'],
                'jumlah_pic'        => $jumlahPicKelas ?: ($totalTerisiSesi > 0 ? 1 : 0),
                'pic_classes'       => $picClassesList,
                'pic_subjects'      => $picSubjectsList,
                'target_sesi'       => $totalTargetSesi,
                'terisi_sesi'       => $totalTerisiSesi,
                'kosong_sesi'       => $unfilledSesiCount,
                'total_jp'          => $totalJpTerisi,
                'hari_terisi'       => $totalHariTerisi,
                'hari_kosong'       => $totalHariKosong,
                'persentase'        => $compliancePct,
                'dates'             => $dateCells,
            ];

            $rekapGuruList[] = $rowSummary;
            $matrixRows[]    = $rowSummary;
        }

        // Urutkan Rekap Guru berdasarkan Jumlah Jurnal Terisi terbanyak, lalu nama
        usort($rekapGuruList, function ($a, $b) {
            if ($b['terisi_sesi'] !== $a['terisi_sesi']) {
                return $b['terisi_sesi'] <=> $a['terisi_sesi'];
            }
            return strcasecmp($a['nama'], $b['nama']);
        });

        // Urutkan Daftar Jurnal Tidak Terisi dari tanggal terbaru
        usort($unfilledList, function ($a, $b) {
            if ($a['tanggal'] !== $b['tanggal']) {
                return strcmp($b['tanggal'], $a['tanggal']);
            }
            return strcasecmp($a['nama_guru'], $b['nama_guru']);
        });

        $unfilledTotalCount = count($unfilledList);

        // 10. Data Tren 7 Hari Terakhir untuk Tab 1 (Dashboard)
        $trend7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $hName = self::getHariIndonesia($d);
            $jOnDay = $journalsByDate[$d] ?? [];
            $jpOnDay = 0;
            foreach ($jOnDay as $item) {
                $jpOnDay += (int)($item['jumlah_jp'] ?? 2);
            }
            $trend7Days[] = [
                'date'  => $d,
                'label' => self::getHariSingkat($hName) . ', ' . date('d/m', strtotime($d)),
                'count' => count($jOnDay),
                'jp'    => $jpOnDay,
            ];
        }

        // 11. Data Personal Akun yang Login (Tab Isi Jurnal & Jurnal Saya)
        $personalPreviewId = (Auth::isSuperAdmin() && !empty($_GET['my_guru_id'])) ? (int)$_GET['my_guru_id'] : null;
        $personalCtx = PegawaiController::buildPersonalJurnalContext($personalPreviewId, [
            'search' => $_GET['my_search'] ?? '',
            'kelas' => $_GET['my_kelas'] ?? '',
            'start_date' => $_GET['my_start_date'] ?? '',
            'end_date' => $_GET['my_end_date'] ?? '',
        ]);
        extract($personalCtx);
        $personalFormAction = url('kelola-jurnal-guru/store-saya');
        $personalFilterAction = url('kelola-jurnal-guru');
        $personalReturnUrl = 'kelola-jurnal-guru?tab=jurnal_saya' . ($personalPreviewId ? ('&my_guru_id=' . $personalPreviewId) : '');
        $personalTabMode = true;

        $pageTitle = 'Kelola Jurnal Guru & Jam Pelajaran (JP)';
        $breadcrumbs = [
            ['label' => 'Administrasi Guru', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Kelola Jurnal Guru']
        ];
        $customSidebar = MODULES_PATH . '/kelola-jurnal-guru/views/sidebar.php';

        ob_start();
        include MODULES_PATH . '/kelola-jurnal-guru/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan / Input Jurnal dari Halaman Admin (bantu isi manual / lengkapi jurnal kosong)
     */
    public static function store(): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-guru'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $guruId    = !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null;
        $jadwalId  = !empty($_POST['jadwal_id']) ? (int)$_POST['jadwal_id'] : null;
        $tanggal   = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $kelas     = trim($_POST['kelas'] ?? '');
        $mapel     = trim($_POST['mata_pelajaran'] ?? '');
        $jamKe     = trim($_POST['jam_ke'] ?? '1-2');
        $jumlahJp  = max(1, min(12, (int)($_POST['jumlah_jp'] ?? 2)));
        $materi    = trim($_POST['materi_pokok'] ?? '');
        $kegiatan  = trim($_POST['uraian_kegiatan'] ?? '');
        $catatan   = trim($_POST['catatan_kelas'] ?? '');
        $kehadiran = trim($_POST['kehadiran_summary'] ?? 'Semua Siswa Hadir');
        $returnTab = trim($_POST['return_tab'] ?? 'data');

        if ($kelas === '' || $mapel === '' || $materi === '') {
            Response::redirect(url('kelola-jurnal-guru?tab=' . urlencode($returnTab)), 'Kelas, Mata Pelajaran, dan Materi Pokok wajib diisi.', 'error');
            return;
        }

        $namaGuru = trim($_POST['nama_guru'] ?? '');
        if ($guruId && $namaGuru === '') {
            $stmtG = $pdo->prepare("SELECT nama FROM pegawai WHERE id = ?");
            $stmtG->execute([$guruId]);
            $namaGuru = (string)($stmtG->fetchColumn() ?: 'Guru');
        }

        $kodeJurnal = 'JRN-' . date('Ymd', strtotime($tanggal)) . '-' . strtoupper(substr(uniqid(), -4));

        $siswaStatuses = $_POST['siswa_status'] ?? [];
        if (!empty($siswaStatuses) && is_array($siswaStatuses)) {
            $h = 0; $s = 0; $i = 0; $a = 0;
            foreach ($siswaStatuses as $stat) {
                if ($stat === 'H') $h++;
                elseif ($stat === 'S') $s++;
                elseif ($stat === 'I') $i++;
                elseif ($stat === 'A') $a++;
            }
            $tot = count($siswaStatuses);
            if ($s + $i + $a === 0) {
                $kehadiran = "{$tot}/{$tot} Hadir Lengkap";
            } else {
                $parts = ["{$h} Hadir"];
                if ($s > 0) $parts[] = "{$s} Sakit";
                if ($i > 0) $parts[] = "{$i} Izin";
                if ($a > 0) $parts[] = "{$a} Alpa";
                $kehadiran = "{$tot} Siswa (" . implode(', ', $parts) . ")";
            }

            // Catat presensi ke presensi_kelas & detail
            try {
                $cleanKls = trim(preg_replace('/^kelas\s+/i', '', $kelas));
                $presensiId = $db->insert('presensi_kelas', [
                    'guru_id' => $guruId ?: 0,
                    'kelas' => $kelas,
                    'mata_pelajaran' => $mapel,
                    'tanggal' => $tanggal,
                    'jam_ke' => $jamKe,
                    'role_mengajar' => 'PIC',
                    'jp' => $jumlahJp,
                    'tipe_presensi' => 'mapel',
                    'catatan' => $catatan ?: $kehadiran,
                    'created_by' => (int)Auth::id() ?: 1,
                    'waktu_presensi' => date('H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $siswaIds = array_keys($siswaStatuses);
                $namesMap = [];
                if (!empty($siswaIds)) {
                    $placeholders = implode(',', array_fill(0, count($siswaIds), '?'));
                    $sRows = $db->findAll("SELECT id, COALESCE(nama_lengkap, nama) as nama FROM siswa WHERE id IN ($placeholders)", $siswaIds);
                    foreach ($sRows as $sr) {
                        $namesMap[$sr['id']] = $sr['nama'];
                    }
                }

                if (!empty($siswaStatuses)) {
                    $nowTs = date('Y-m-d H:i:s');
                    $valGroups = [];
                    $batchParams = [];
                    foreach ($siswaStatuses as $sId => $stVal) {
                        $valGroups[] = '(?, ?, ?, ?, ?)';
                        $batchParams[] = $presensiId;
                        $batchParams[] = (int)$sId;
                        $batchParams[] = $namesMap[$sId] ?? "Siswa #{$sId}";
                        $batchParams[] = in_array($stVal, ['H', 'S', 'I', 'A'], true) ? $stVal : 'H';
                        $batchParams[] = $nowTs;
                    }
                    $pdo = $db->getConnection();
                    $stmtBatch = $pdo->prepare(
                        "INSERT INTO presensi_kelas_detail (presensi_kelas_id, siswa_id, nama_siswa, status, created_at) VALUES " . implode(', ', $valGroups)
                    );
                    $stmtBatch->execute($batchParams);
                }
            } catch (Throwable $e) {}
        }

        $db->insert('jurnal_mengajar', [
            'kode_jurnal'       => $kodeJurnal,
            'jadwal_id'         => $jadwalId ?: null,
            'guru_id'           => $guruId ?: (int)Auth::id(),
            'nama_guru'         => $namaGuru ?: 'Guru',
            'kelas'             => $kelas,
            'mata_pelajaran'    => $mapel,
            'tanggal'           => $tanggal,
            'jam_ke'            => $jamKe,
            'jumlah_jp'         => $jumlahJp,
            'materi_pokok'      => $materi,
            'uraian_kegiatan'   => $kegiatan,
            'catatan_kelas'     => $catatan,
            'kehadiran_summary' => $kehadiran,
            'status'            => 'tervalidasi',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        Response::redirect(url('kelola-jurnal-guru?tab=' . urlencode($returnTab)), 'Data jurnal mengajar berhasil disimpan & divalidasi!', 'success');
    }

    /**
     * API Ambil Penugasan SK & Jadwal KBM Guru
     */
    public static function apiGuruAssignments(int $id): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = Database::getInstance();

            $pegawai = $db->find("SELECT id, nama, niy, nik, jabatan, unit_tugas FROM pegawai WHERE id = ?", [$id]);
            if (!$pegawai) {
                echo json_encode(['success' => false, 'message' => 'Data guru tidak ditemukan.']);
                exit;
            }

            $classes = [];
            $subjects = [];

            // 1. Wali Kelas dari SK Aktif
            $waliRows = $db->findAll("
                SELECT pp.wali_kelas_nama, pg.nama_grup, pg.semester
                FROM pegawai_penugasan pp
                JOIN penugasan_grup pg ON pp.grup_id = pg.id
                WHERE pp.pegawai_id = ? AND pg.is_active = 1 AND pp.wali_kelas_nama IS NOT NULL AND pp.wali_kelas_nama != ''
            ", [$id]);
            foreach ($waliRows as $w) {
                $c = trim($w['wali_kelas_nama']);
                $classes[$c] = [
                    'name' => $c,
                    'role' => 'Wali Kelas',
                    'subjects' => []
                ];
            }

            // 2. Mengajar dari SK Aktif
            $mengajarRows = $db->findAll("
                SELECT ppm.*, pg.nama_grup
                FROM pegawai_penugasan_mengajar ppm
                JOIN penugasan_grup pg ON ppm.grup_id = pg.id
                WHERE ppm.pegawai_id = ? AND pg.is_active = 1
            ", [$id]);
            foreach ($mengajarRows as $m) {
                $c = trim($m['nama_kelas']);
                if (empty($c)) continue;
                if (!isset($classes[$c])) {
                    $classes[$c] = [
                        'name' => $c,
                        'role' => 'Guru Pengajar (PIC)',
                        'subjects' => []
                    ];
                }
                if (!empty($m['mata_pelajaran']) && !in_array($m['mata_pelajaran'], $classes[$c]['subjects'])) {
                    $classes[$c]['subjects'][] = $m['mata_pelajaran'];
                }
                if (!empty($m['mata_pelajaran']) && !in_array($m['mata_pelajaran'], $subjects)) {
                    $subjects[] = $m['mata_pelajaran'];
                }
            }

            // 3. Jadwal Pelajaran
            $jadwalRows = $db->findAll("
                SELECT jp.* 
                FROM jadwal_pelajaran jp
                WHERE (jp.pegawai_id = ? OR jp.nama_guru LIKE ?)
                ORDER BY FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jp.jam_ke ASC
            ", [$id, '%' . $pegawai['nama'] . '%']);

            foreach ($jadwalRows as $j) {
                $c = trim($j['nama_kelas']);
                if (!empty($c) && !isset($classes[$c])) {
                    $classes[$c] = [
                        'name' => $c,
                        'role' => 'Jadwal Reguler',
                        'subjects' => []
                    ];
                }
                if (!empty($j['mata_pelajaran'])) {
                    if (isset($classes[$c]) && !in_array($j['mata_pelajaran'], $classes[$c]['subjects'])) {
                        $classes[$c]['subjects'][] = $j['mata_pelajaran'];
                    }
                    if (!in_array($j['mata_pelajaran'], $subjects)) {
                        $subjects[] = $j['mata_pelajaran'];
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'guru' => $pegawai,
                'classes' => array_values($classes),
                'subjects' => array_values($subjects),
                'schedules' => $jadwalRows
            ]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * API Siswa per Kelas untuk Web Admin Modal
     */
    public static function apiSiswaKelas(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = Database::getInstance();
            $className = trim($_GET['kelas'] ?? '');
            if (empty($className)) {
                echo json_encode(['success' => false, 'message' => 'Kelas tidak ditentukan.']);
                exit;
            }

            $cleanName = trim(preg_replace('/^kelas\s+/i', '', $className));

            $students = $db->findAll("
                SELECT id, nisn, nis, COALESCE(nama_lengkap, nama) as nama, jenis_kelamin
                FROM siswa 
                WHERE (kelas = ? OR kelas = ? OR REPLACE(kelas, 'Kelas ', '') = ? OR ? = REPLACE(kelas, 'Kelas ', ''))
                  AND (is_active = 1 OR is_active IS NULL OR status_siswa = 'Aktif')
                ORDER BY COALESCE(nama_lengkap, nama) ASC
            ", [$className, $cleanName, $cleanName, $cleanName]);

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
     * Detail Jurnal via JSON (untuk Modal Popup)
     */
    public static function apiDetail(int $id): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $stmt = $pdo->prepare("
                SELECT 
                    jm.*,
                    COALESCE(p.nama, jm.nama_guru, 'Guru') as guru_nama_lengkap,
                    p.niy,
                    p.nik,
                    p.email as guru_email,
                    p.foto as guru_foto,
                    jp.jam_mulai,
                    jp.jam_selesai,
                    jp.ruangan,
                    jg.nama_grup as jadwal_grup_nama
                FROM jurnal_mengajar jm
                LEFT JOIN pegawai p ON jm.guru_id = p.id
                LEFT JOIN jadwal_pelajaran jp ON jm.jadwal_id = jp.id
                LEFT JOIN jadwal_grup jg ON jp.grup_id = jg.id
                WHERE jm.id = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Data jurnal tidak ditemukan']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $item
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Update Status Jurnal (Validasi / Batal Validasi)
     */
    public static function updateStatus(int $id): void
    {
        $returnTab = trim($_POST['return_tab'] ?? 'data');
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-guru?tab=' . urlencode($returnTab)), 'Token keamanan tidak valid', 'error');
            return;
        }

        $newStatus = trim($_POST['status'] ?? '');
        if (!in_array($newStatus, ['draft', 'selesai', 'tervalidasi'], true)) {
            Response::redirect(url('kelola-jurnal-guru?tab=' . urlencode($returnTab)), 'Status tidak valid', 'error');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("UPDATE jurnal_mengajar SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        $statusLabel = [
            'tervalidasi' => 'divalidasi',
            'selesai'     => 'diselesaikan',
            'draft'       => 'dijadikan draft'
        ][$newStatus] ?? 'diperbarui';

        Response::redirect(url('kelola-jurnal-guru?tab=' . urlencode($returnTab)), "Status jurnal berhasil {$statusLabel}!", 'success');
    }

    /**
     * Hapus Jurnal
     */
    public static function delete(int $id): void
    {
        $returnTab = trim($_POST['return_tab'] ?? 'data');
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-guru?tab=' . urlencode($returnTab)), 'Token keamanan tidak valid', 'error');
            return;
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $stmtFind = $pdo->prepare("SELECT foto_kegiatan FROM jurnal_mengajar WHERE id = ?");
        $stmtFind->execute([$id]);
        $row = $stmtFind->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['foto_kegiatan'])) {
            $filePath = BASE_PATH . '/public/' . ltrim($row['foto_kegiatan'], '/');
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $stmtDel = $pdo->prepare("DELETE FROM jurnal_mengajar WHERE id = ?");
        $stmtDel->execute([$id]);

        Response::redirect(url('kelola-jurnal-guru?tab=' . urlencode($returnTab)), 'Jurnal KBM berhasil dihapus.', 'success');
    }

    /**
     * Cetak Laporan Rekapitulasi Jurnal & Jam Mengajar (JP)
     */
    public static function cetak(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $search    = trim($_GET['search'] ?? '');
        $guruId    = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : null;
        $kelas     = trim($_GET['kelas'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $startDate = trim($_GET['start_date'] ?? date('Y-m-01'));
        $endDate   = trim($_GET['end_date'] ?? date('Y-m-t'));

        $whereClauses = ['1=1'];
        $params = [];

        if ($search !== '') {
            $whereClauses[] = '(jm.kode_jurnal LIKE ? OR jm.nama_guru LIKE ? OR p.nama LIKE ? OR jm.materi_pokok LIKE ? OR jm.mata_pelajaran LIKE ? OR jm.kelas LIKE ?)';
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($guruId !== null) {
            $whereClauses[] = 'jm.guru_id = ?';
            $params[] = $guruId;
        }

        if ($kelas !== '') {
            $whereClauses[] = 'jm.kelas = ?';
            $params[] = $kelas;
        }

        if ($status !== '' && in_array($status, ['draft', 'selesai', 'tervalidasi'], true)) {
            $whereClauses[] = 'jm.status = ?';
            $params[] = $status;
        }

        if ($startDate !== '') {
            $whereClauses[] = 'jm.tanggal >= ?';
            $params[] = $startDate;
        }

        if ($endDate !== '') {
            $whereClauses[] = 'jm.tanggal <= ?';
            $params[] = $endDate;
        }

        $whereSql = implode(' AND ', $whereClauses);

        // Fetch Jurnal
        $stmt = $pdo->prepare("
            SELECT 
                jm.*,
                COALESCE(p.nama, jm.nama_guru, 'Guru') as guru_nama_lengkap,
                p.niy,
                p.nik,
                jp.jam_mulai,
                jp.jam_selesai,
                jp.ruangan
            FROM jurnal_mengajar jm
            LEFT JOIN pegawai p ON jm.guru_id = p.id
            LEFT JOIN jadwal_pelajaran jp ON jm.jadwal_id = jp.id
            WHERE {$whereSql}
            ORDER BY jm.tanggal ASC, jm.jam_ke ASC, jm.id ASC
        ");
        $stmt->execute($params);
        $jurnalList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rekap per Guru
        $rekapPerGuru = [];
        $totalJP = 0;
        foreach ($jurnalList as $item) {
            $gId = $item['guru_id'] ?? 0;
            $gName = $item['guru_nama_lengkap'] ?? 'Guru';
            if (!isset($rekapPerGuru[$gId])) {
                $rekapPerGuru[$gId] = [
                    'nama' => $gName,
                    'niy' => $item['niy'] ?? ($item['nik'] ?? '-'),
                    'total_pertemuan' => 0,
                    'total_jp' => 0
                ];
            }
            $rekapPerGuru[$gId]['total_pertemuan']++;
            $rekapPerGuru[$gId]['total_jp'] += (int)($item['jumlah_jp'] ?? 2);
            $totalJP += (int)($item['jumlah_jp'] ?? 2);
        }

        // Info Guru terpilih jika filter single teacher
        $selectedGuru = null;
        if ($guruId !== null) {
            $stmtG = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
            $stmtG->execute([$guruId]);
            $selectedGuru = $stmtG->fetch(PDO::FETCH_ASSOC);
        }

        include MODULES_PATH . '/kelola-jurnal-guru/views/cetak.php';
    }
}
