<?php
/**
 * Jurnal Guru Pengaturan & Rekapitulasi PIC Engine
 */

class JurnalGuruPengaturan
{
    private static ?Database $db = null;

    private static function db(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Pastikan tabel pengaturan rekap jurnal guru ada
     */
    public static function init(): void
    {
        $pdo = self::db()->getConnection();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `jurnal_guru_pengaturan` (
                `id` INT NOT NULL DEFAULT 1,
                `periode_awal` DATE NULL,
                `periode_akhir` DATE NULL,
                `keterangan` VARCHAR(255) NULL,
                `updated_by` INT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $exist = self::db()->find("SELECT id FROM `jurnal_guru_pengaturan` WHERE `id` = 1");
        if (!$exist) {
            $pdo->exec("
                INSERT IGNORE INTO `jurnal_guru_pengaturan`
                (`id`, `periode_awal`, `periode_akhir`, `keterangan`)
                VALUES (1, DATE_FORMAT(NOW(), '%Y-%m-01'), LAST_DAY(NOW()), 'Default Periode Rekap Jurnal PIC')
            ");
        }
    }

    /**
     * Ambil pengaturan periode rekap jurnal guru dari database
     */
    public static function getPengaturan(): array
    {
        self::init();
        $row = self::db()->find("SELECT * FROM `jurnal_guru_pengaturan` WHERE `id` = 1");
        if (!$row) {
            return [
                'id' => 1,
                'periode_awal' => date('Y-m-01'),
                'periode_akhir' => date('Y-m-t'),
                'keterangan' => 'Default Periode Rekap Jurnal PIC'
            ];
        }
        if (empty($row['periode_awal'])) {
            $row['periode_awal'] = date('Y-m-01');
        }
        if (empty($row['periode_akhir'])) {
            $row['periode_akhir'] = date('Y-m-t');
        }
        return $row;
    }

    /**
     * Simpan pengaturan periode rekap jurnal guru
     */
    public static function savePengaturan(array $data, ?int $userId = null): void
    {
        self::init();
        $periodeAwal = !empty($data['periode_awal']) ? trim($data['periode_awal']) : date('Y-m-01');
        $periodeAkhir = !empty($data['periode_akhir']) ? trim($data['periode_akhir']) : date('Y-m-t');
        $keterangan = trim($data['keterangan'] ?? 'Default Periode Rekap Jurnal PIC');

        if (strtotime($periodeAwal) > strtotime($periodeAkhir)) {
            $tmp = $periodeAwal;
            $periodeAwal = $periodeAkhir;
            $periodeAkhir = $tmp;
        }

        self::db()->query("
            UPDATE `jurnal_guru_pengaturan`
            SET `periode_awal` = ?,
                `periode_akhir` = ?,
                `keterangan` = ?,
                `updated_by` = ?
            WHERE `id` = 1
        ", [$periodeAwal, $periodeAkhir, $keterangan, $userId]);
    }

    /**
     * Ambil rekapitulasi jurnal PIC individu guru dalam rentang tanggal tertentu
     * Menyajikan ringkasan KPI ketuntasan, daftar kapan tidak isi jurnal, dan riwayat jurnal terisi
     */
    public static function getRekapJurnalGuru(int $pegawaiId, string $guruName, ?string $startDate = null, ?string $endDate = null): array
    {
        self::init();
        $config = self::getPengaturan();

        $startDate = !empty($startDate) ? trim($startDate) : ($config['periode_awal'] ?: date('Y-m-01'));
        $endDate   = !empty($endDate) ? trim($endDate) : ($config['periode_akhir'] ?: date('Y-m-d'));
        if (strtotime($startDate) > strtotime($endDate)) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }

        $today = date('Y-m-d');
        // Evaluasi jadwal yang wajib diisi hanya sampai hari ini
        $evalEnd = (strtotime($endDate) > strtotime($today)) ? $today : $endDate;

        // Ambil jadwal mingguan guru
        require_once MODULES_PATH . '/portal-guru/controllers/PortalGuruController.php';
        $allSchedules = PortalGuruController::getTeacherSchedules($pegawaiId, $guruName);

        // Kelompokkan jadwal berdasarkan hari (Senin, Selasa, dll.)
        $schedulesByDay = [];
        foreach ($allSchedules as $sch) {
            $h = trim($sch['hari'] ?? 'Senin');
            $schedulesByDay[$h][] = $sch;
        }

        // Ambil seluruh jurnal guru dalam rentang tanggal periode
        $journals = self::db()->findAll(
            "SELECT * FROM `jurnal_mengajar`
             WHERE (`guru_id` = ? OR `nama_guru` LIKE ?)
               AND `tanggal` BETWEEN ? AND ?
             ORDER BY `tanggal` DESC, `id` DESC",
            [$pegawaiId ?: 0, '%' . $guruName . '%', $startDate, $endDate]
        );

        $journalsByDate = [];
        foreach ($journals as $jr) {
            $d = $jr['tanggal'];
            $journalsByDate[$d][] = $jr;
        }

        $dayMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];

        $totalTargetSesi = 0;
        $totalTerisi = 0;
        $totalKosong = 0;

        $unfilledSessions = [];
        $filledJournals = [];

        // Loop dari startDate s/d evalEnd
        $currentTs = strtotime($startDate);
        $endTs = strtotime($evalEnd);

        while ($currentTs <= $endTs) {
            $curDate = date('Y-m-d', $currentTs);
            $dayOfWeek = (int)date('N', $currentTs);
            $namaHari = $dayMap[$dayOfWeek] ?? 'Senin';
            $isSunday = ($dayOfWeek === 7);

            $daySchedules = $schedulesByDay[$namaHari] ?? [];
            $dayJournals = $journalsByDate[$curDate] ?? [];

            if ($isSunday && empty($dayJournals)) {
                $currentTs = strtotime('+1 day', $currentTs);
                continue;
            }

            if (!empty($daySchedules)) {
                foreach ($daySchedules as $sch) {
                    $totalTargetSesi++;

                    // Cek apakah ada jurnal yang cocok untuk sesi jadwal ini pada tanggal curDate
                    $matchedJournal = null;
                    foreach ($dayJournals as $dj) {
                        if (
                            (!empty($dj['jadwal_id']) && (int)$dj['jadwal_id'] === (int)$sch['id']) ||
                            (strcasecmp(trim($dj['kelas'] ?? ''), trim($sch['class'] ?? '')) === 0 &&
                             strcasecmp(trim($dj['mata_pelajaran'] ?? ''), trim($sch['subject'] ?? '')) === 0)
                        ) {
                            $matchedJournal = $dj;
                            break;
                        }
                    }

                    if ($matchedJournal) {
                        $totalTerisi++;
                    } else {
                        // Belum isi jurnal!
                        $totalKosong++;
                        $unfilledSessions[] = [
                            'tanggal'        => $curDate,
                            'hari'           => $namaHari,
                            'jadwal_id'      => (int)($sch['id'] ?? 0),
                            'kelas'          => $sch['class'] ?? '-',
                            'mata_pelajaran' => $sch['subject'] ?? '-',
                            'jam_ke'         => $sch['jam_ke'] ?? '1',
                            'jumlah_jp'      => $sch['jumlah_jp'] ?? 2,
                            'waktu'          => $sch['time'] ?? "Jam ke-{$sch['jam_ke']}",
                            'badge'          => 'rose'
                        ];
                    }
                }
            }

            $currentTs = strtotime('+1 day', $currentTs);
        }

        // Format data jurnal yang sudah terisi
        foreach ($journals as $jr) {
            $dTs = strtotime($jr['tanggal']);
            $dDay = $dayMap[(int)date('N', $dTs)] ?? 'Senin';
            $filledJournals[] = [
                'id'              => $jr['id'],
                'kode_jurnal'     => $jr['kode_jurnal'] ?? ('JRN-' . $jr['id']),
                'tanggal'         => $jr['tanggal'],
                'hari'            => $dDay,
                'kelas'           => $jr['kelas'],
                'mata_pelajaran'  => $jr['mata_pelajaran'],
                'jam_ke'          => $jr['jam_ke'],
                'jumlah_jp'       => $jr['jumlah_jp'] ?? 2,
                'materi_pokok'    => $jr['materi_pokok'] ?? '-',
                'uraian_kegiatan' => $jr['uraian_kegiatan'] ?? '',
                'kehadiran'       => $jr['kehadiran_summary'] ?? 'Lengkap',
                'status'          => ucfirst($jr['status'] ?? 'selesai'),
                'badge'           => 'emerald'
            ];
        }

        // Urutkan unfilled sesi dari yang paling baru
        usort($unfilledSessions, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

        $persenKelengkapan = $totalTargetSesi > 0 ? round(($totalTerisi / $totalTargetSesi) * 100, 1) : 100;

        return [
            'summary' => [
                'total_target_sesi'  => $totalTargetSesi,
                'total_terisi'       => $totalTerisi,
                'total_kosong'       => $totalKosong,
                'persen_kelengkapan' => $persenKelengkapan,
            ],
            'unfilled_sessions' => $unfilledSessions,
            'filled_journals'   => $filledJournals,
            'periode' => [
                'start' => $startDate,
                'end'   => $endDate,
                'start_formatted' => date('d M Y', strtotime($startDate)),
                'end_formatted'   => date('d M Y', strtotime($endDate))
            ]
        ];
    }
}
