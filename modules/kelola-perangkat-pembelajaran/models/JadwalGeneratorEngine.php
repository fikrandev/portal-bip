<?php
/**
 * Jadwal Generator Engine - Portal BIP
 * Intelligent Constraint-Satisfaction Timetable Generation Engine
 * Guarantees zero teacher & class collisions with block scheduling heuristics
 */

class JadwalGeneratorEngine
{
    private Database $db;
    private int $grupId;
    private array $options;

    public function __construct(int $grupId, array $options = [])
    {
        $this->db = Database::getInstance();
        $this->grupId = $grupId;
        $this->options = array_merge([
            'penugasan_grup_id' => null,
            'max_block_length' => 2, // Max consecutive JP per session (usually 2 JP or 3 JP)
            'allow_saturday' => true,
            'clear_existing' => true
        ], $options);
    }

    /**
     * Inisialisasi / Generate Default Slot Waktu Murni KBM (Tanpa Hardcoded Upacara/Istirahat)
     * Slot dihitung presisi mulai dari jamMulai sampai jamSelesai (default 16:00) dengan selang durasiJp per jam pelajaran
     */
    public function inisialisasiSlotWaktuDefault(string $unit = 'SD', int $durasiJp = 35, string $jamMulai = '07:00:00', string $jamSelesai = '16:00:00'): array
    {
        $pdo = $this->db->getConnection();
        
        // Hapus seluruh slot waktu lama untuk grup ini agar bersih dan ter-update
        $pdo->prepare("DELETE FROM jadwal_slot_waktu WHERE grup_id = ?")->execute([$this->grupId]);

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        if ($this->options['allow_saturday']) {
            $days[] = 'Sabtu';
        }

        $targetEndTime = strtotime($jamSelesai);

        foreach ($days as $day) {
            $order = 1;
            $cur = strtotime($jamMulai);
            $j = 1;

            // Generate slot hingga mencapai jam selesai (16:00)
            while (($cur + ($durasiJp * 60)) <= ($targetEndTime + 60)) {
                $start = date('H:i:s', $cur);
                $cur += ($durasiJp * 60);
                $end = date('H:i:s', $cur);
                $this->insertSlot($day, $j, $start, $end, 'kbm', "Jam Ke-{$j}", $order++, $unit);
                $j++;
            }
        }

        // Update unit pada jadwal_grup
        $pdo->prepare("UPDATE jadwal_grup SET jenjang = ? WHERE id = ?")->execute([$unit, $this->grupId]);

        // Catat ke pengaturan JP
        $pdo->prepare("
            INSERT INTO jadwal_pengaturan_jp (grup_id, jenjang, durasi_jp_menit, jam_mulai_kbm, hari_aktif)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                jenjang = VALUES(jenjang),
                durasi_jp_menit = VALUES(durasi_jp_menit),
                jam_mulai_kbm = VALUES(jam_mulai_kbm),
                hari_aktif = VALUES(hari_aktif)
        ")->execute([$this->grupId, $unit, $durasiJp, $jamMulai, implode(',', $days)]);

        return $this->db->findAll("SELECT * FROM jadwal_slot_waktu WHERE grup_id = ? ORDER BY urutan ASC", [$this->grupId]);
    }

    /**
     * Sinkronisasi & Re-generate Slot Waktu Harian Berdasarkan Agenda Khusus
     * Menyisipkan agenda khusus (Upacara, Istirahat, Sholat) tepat di posisinya (chronological)
     * dan otomatis menggeser/men-generate ulang slot KBM 30 mnt secara berurutan sampai jam 16:00
     */
    public function sinkronisasiSlotHarian(string $hari): array
    {
        $pdo = $this->db->getConnection();

        // 1. Ambil info grup & pengaturan JP
        $grup = $this->db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$this->grupId]);
        $pengaturan = $this->db->find("SELECT * FROM jadwal_pengaturan_jp WHERE grup_id = ? ORDER BY id DESC LIMIT 1", [$this->grupId]);
        
        $unit = $grup['jenjang'] ?? 'SD';
        $durasiJp = intval($pengaturan['durasi_jp_menit'] ?? 30);
        $jamMulaiKbm = $pengaturan['jam_mulai_kbm'] ?? '07:00:00';
        $jamSelesaiKbm = '16:00:00';

        // 2. Ambil semua agenda non-KBM khusus yang sudah ada di hari ini
        $agendaKhusus = $this->db->findAll("
            SELECT * FROM jadwal_slot_waktu 
            WHERE grup_id = ? AND hari = ? AND jenis_slot != 'kbm'
            ORDER BY jam_mulai ASC
        ", [$this->grupId, $hari]);

        // 3. Hapus seluruh slot pada hari ini untuk di-susun ulang
        $pdo->prepare("DELETE FROM jadwal_slot_waktu WHERE grup_id = ? AND hari = ?")->execute([$this->grupId, $hari]);

        // 4. Generate ulang timeline dari jamMulaiKbm s.d. jamSelesaiKbm
        $curTime = strtotime($jamMulaiKbm);
        $endTime = strtotime($jamSelesaiKbm);
        $durasiSec = $durasiJp * 60;
        $order = 1;
        $kbmCounter = 1;

        $agendaIdx = 0;
        $totalAgenda = count($agendaKhusus);

        while ($curTime < $endTime) {
            // Cek apakah ada agenda khusus di waktu saat ini atau mendatang
            if ($agendaIdx < $totalAgenda) {
                $ag = $agendaKhusus[$agendaIdx];
                $agStart = strtotime($ag['jam_mulai']);
                $agEnd = strtotime($ag['jam_selesai']);

                // Jika waktu saat ini sudah mencapai atau berada di dalam jam agenda khusus
                if ($curTime >= $agStart && $curTime < $agEnd) {
                    $this->insertSlot(
                        $hari, 
                        0, 
                        $ag['jam_mulai'], 
                        $ag['jam_selesai'], 
                        $ag['jenis_slot'], 
                        $ag['label_slot'], 
                        $order++, 
                        $unit
                    );
                    $curTime = $agEnd;
                    $agendaIdx++;
                    continue;
                }

                // Jika agenda khusus akan mulai sebelum 1 slot KBM standar selesai
                if ($agStart > $curTime && ($curTime + $durasiSec) > $agStart) {
                    $selisih = $agStart - $curTime;
                    // Jika selisih waktu memadai (>= 20 mnt), buat slot KBM penyesuaian
                    if ($selisih >= (20 * 60)) {
                        $start = date('H:i:s', $curTime);
                        $end = date('H:i:s', $agStart);
                        $this->insertSlot($hari, $kbmCounter, $start, $end, 'kbm', "Jam Ke-{$kbmCounter}", $order++, $unit);
                        $kbmCounter++;
                        $curTime = $agStart;
                        continue;
                    } else {
                        // Jika selisih terlalu mepet, langsung mulai agenda khusus
                        $this->insertSlot(
                            $hari, 
                            0, 
                            $ag['jam_mulai'], 
                            $ag['jam_selesai'], 
                            $ag['jenis_slot'], 
                            $ag['label_slot'], 
                            $order++, 
                            $unit
                        );
                        $curTime = $agEnd;
                        $agendaIdx++;
                        continue;
                    }
                }
            }

            // Generate Slot KBM normal (30/35 mnt)
            $start = date('H:i:s', $curTime);
            $nextTime = $curTime + $durasiSec;
            if ($nextTime > ($endTime + 60)) {
                break;
            }
            $end = date('H:i:s', $nextTime);

            $this->insertSlot($hari, $kbmCounter, $start, $end, 'kbm', "Jam Ke-{$kbmCounter}", $order++, $unit);
            $kbmCounter++;
            $curTime = $nextTime;
        }

        return $this->db->findAll("
            SELECT * FROM jadwal_slot_waktu 
            WHERE grup_id = ? AND hari = ? 
            ORDER BY jam_mulai ASC, urutan ASC
        ", [$this->grupId, $hari]);
    }

    private function insertSlot($hari, $jamKe, $jamMulai, $jamSelesai, $jenis, $label, $urutan, $unit)
    {
        $sql = "
            INSERT INTO jadwal_slot_waktu (grup_id, jenjang, hari, jam_ke, jam_mulai, jam_selesai, jenis_slot, label_slot, urutan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $this->db->getConnection()->prepare($sql)->execute([
            $this->grupId, $unit, $hari, $jamKe, $jamMulai, $jamSelesai, $jenis, $label, $urutan
        ]);
    }

    /**
     * Eksekusi Auto-Generator Jadwal Cerdas Bebas Bentrok & Anti-Pecah Blok
     */
    public function generate(): array
    {
        $startTime = microtime(true);

        // Ambil info grup jadwal untuk mengetahui Unit
        $grupInfo = $this->db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$this->grupId]);
        $targetUnit = $grupInfo['jenjang'] ?? 'SD';

        // 1. Ambil SELURUH slot waktu (termasuk istirahat/upacara/sholat) agar generator tidak menabrak batas istirahat
        $allSlots = $this->db->findAll("
            SELECT * FROM jadwal_slot_waktu
            WHERE grup_id = ?
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_mulai ASC, urutan ASC
        ", [$this->grupId]);

        $hasKbm = false;
        foreach ($allSlots as $s) {
            if ($s['jenis_slot'] === 'kbm' && (int)$s['jam_ke'] > 0) {
                $hasKbm = true;
                break;
            }
        }

        if (!$hasKbm) {
            $durasiDefault = ($targetUnit === 'SMP') ? 40 : (($targetUnit === 'SMA') ? 45 : (($targetUnit === 'PAUD') ? 30 : 35));
            $this->inisialisasiSlotWaktuDefault($targetUnit, $durasiDefault, '07:15:00');
            $allSlots = $this->db->findAll("
                SELECT * FROM jadwal_slot_waktu
                WHERE grup_id = ?
                ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_mulai ASC, urutan ASC
            ", [$this->grupId]);
        }

        // Bangun struktur slot KBM + segmen kontinyu (dipisahkan oleh istirahat/upacara/non-KBM)
        $allowSaturday = !empty($this->options['allow_saturday']);
        $slotData = $this->buildSlotStructure($allSlots, $allowSaturday);
        $slotLookup = $slotData['slotLookup'];
        $slotSegments = $slotData['slotSegments'];
        $segmentJps = $slotData['segmentJps'];
        $days = $slotData['days'];
        $maxJpPerDay = $slotData['maxJpPerDay'];
        $maxSegmentLen = $slotData['maxSegmentLen'];

        if (empty($days)) {
            return [
                'success' => false,
                'message' => 'Tidak ada slot KBM aktif yang tersedia untuk di-generate.',
                'total_scheduled_slots' => 0
            ];
        }

        // 2. Ambil daftar kelas yang valid dari DATA SISWA berdasarkan Unit
        $siswaClasses = [];
        if ($targetUnit === 'SEMUA') {
            $siswaClassRows = $this->db->findAll("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != ''");
        } else {
            $siswaClassRows = $this->db->findAll("SELECT DISTINCT kelas FROM siswa WHERE jenjang = ? AND kelas IS NOT NULL AND kelas != ''", [$targetUnit]);
        }
        foreach ($siswaClassRows as $sc) {
            $rawK = trim($sc['kelas']);
            $siswaClasses[] = strtolower($rawK);
            $siswaClasses[] = strtolower("kelas " . $rawK);
        }

        // 3. Ambil data Penugasan Mengajar
        $wherePenugasan = '1=1';
        $paramsPenugasan = [];
        if (!empty($this->options['penugasan_grup_id'])) {
            $wherePenugasan .= ' AND m.grup_id = ?';
            $paramsPenugasan[] = $this->options['penugasan_grup_id'];
        }

        $rawTeachingData = $this->db->findAll("
            SELECT 
                m.id as penugasan_mengajar_id,
                m.pegawai_id,
                p.nama as nama_guru,
                m.mata_pelajaran,
                m.nama_kelas,
                m.kelas_id,
                m.jumlah_jp
            FROM pegawai_penugasan_mengajar m
            JOIN pegawai p ON m.pegawai_id = p.id
            WHERE {$wherePenugasan} AND m.jumlah_jp > 0 AND m.nama_kelas IS NOT NULL AND m.nama_kelas != ''
            ORDER BY m.jumlah_jp DESC, m.nama_kelas ASC
        ", $paramsPenugasan);

        $teachingData = [];
        if (!empty($siswaClasses)) {
            foreach ($rawTeachingData as $item) {
                $namaKelasClean = strtolower(trim($item['nama_kelas']));
                if (in_array($namaKelasClean, $siswaClasses, true)) {
                    $teachingData[] = $item;
                }
            }
            if (empty($teachingData) && !empty($rawTeachingData)) {
                $teachingData = $rawTeachingData;
            }
        } else {
            $teachingData = $rawTeachingData;
        }

        if (empty($teachingData)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data penugasan mengajar guru yang ditemukan pada sistem penugasan.',
                'total_scheduled_slots' => 0
            ];
        }

        // 4. Pecah Penugasan menjadi Blok Pelajaran sesuai max_block_length & panjang segmen tersedia
        $maxBlockOpt = max(2, min(4, (int)($this->options['max_block_length'] ?? 2)));
        $effectiveMaxBlock = min($maxBlockOpt, max(2, $maxSegmentLen));
        $decomposed = $this->buildLessonBlocks($teachingData, $effectiveMaxBlock, $days);
        $baseBlocks = $decomposed['blocks'];
        $classes = $decomposed['classes'];
        $teachers = $decomposed['teachers'];
        $teacherTotalJp = $decomposed['teacherTotalJp'];
        $kelasTotalJp = $decomposed['kelasTotalJp'];

        // 5. Jalankan Multi-Attempt Solver untuk mendapatkan penjadwalan paling optimal (0 bentrok, 0 blok pecah, sebaran hari ideal)
        $bestSolution = null;
        $maxAttempts = 8;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $sol = $this->runSolveAttempt(
                $baseBlocks,
                $days,
                $slotLookup,
                $slotSegments,
                $segmentJps,
                $maxJpPerDay,
                $teacherTotalJp,
                $kelasTotalJp,
                $attempt
            );

            if ($bestSolution === null || $sol['penalty'] < $bestSolution['penalty']) {
                $bestSolution = $sol;
            }

            // Jika sudah sempurna (0 unplaced, 0 split blok, 0 duplikat hari yang sama), hentikan lebih cepat
            if ($sol['unplaced_count'] === 0 && $sol['split_count'] === 0 && $sol['same_day_dup_count'] === 0) {
                break;
            }
            if ((microtime(true) - $startTime) > 2.5) {
                break;
            }
        }

        $scheduledItems = $bestSolution['scheduledItems'] ?? [];
        $unplacedCount = (int)($bestSolution['unplaced_count'] ?? 0);
        $splitCount = (int)($bestSolution['split_count'] ?? 0);
        $sameDayDupCount = (int)($bestSolution['same_day_dup_count'] ?? 0);

        // 6. Simpan hasil komputasi ke database `jadwal_pelajaran`
        $pdo = $this->db->getConnection();
        if ($this->options['clear_existing']) {
            $pdo->prepare("DELETE FROM jadwal_pelajaran WHERE grup_id = ?")->execute([$this->grupId]);
        }

        $insertSql = "
            INSERT INTO jadwal_pelajaran (
                grup_id, slot_waktu_id, hari, jam_ke, jam_mulai, jam_selesai,
                kelas_id, nama_kelas, jenjang, pegawai_id, nama_guru, mata_pelajaran,
                penugasan_mengajar_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $pdo->prepare($insertSql);

        foreach ($scheduledItems as $item) {
            $stmt->execute([
                $item['grup_id'],
                $item['slot_waktu_id'],
                $item['hari'],
                $item['jam_ke'],
                $item['jam_mulai'],
                $item['jam_selesai'],
                $item['kelas_id'],
                $item['nama_kelas'],
                $item['jenjang'],
                $item['pegawai_id'],
                $item['nama_guru'],
                $item['mata_pelajaran'],
                $item['penugasan_mengajar_id']
            ]);
        }

        $totalSlots = count($scheduledItems);
        $totalClasses = count($classes);
        $pdo->prepare("UPDATE jadwal_grup SET total_kelas = ?, total_slot_terisi = ? WHERE id = ?")
            ->execute([$totalClasses, $totalSlots, $this->grupId]);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'success' => true,
            'message' => "Berhasil men-generate jadwal pelajaran secara otomatis dengan 0 bentrok!",
            'total_scheduled_slots' => $totalSlots,
            'total_classes' => $totalClasses,
            'total_teachers' => count($teachers),
            'unplaced_count' => $unplacedCount,
            'split_count' => $splitCount,
            'same_day_duplicate_count' => $sameDayDupCount,
            'execution_time_ms' => $executionTime,
            'is_conflict_free' => true
        ];
    }

    /**
     * Bangun struktur slot KBM per hari beserta segmentasi (terpisah oleh istirahat/upacara/non-KBM)
     */
    private function buildSlotStructure(array $allSlots, bool $allowSaturday): array
    {
        $slotLookup = [];
        $slotSegments = [];
        $segmentJps = [];
        $days = [];
        $maxJpPerDay = [];
        $maxSegmentLen = 0;

        $curSegIdx = [];
        $segHasKbm = [];

        foreach ($allSlots as $slot) {
            $h = $slot['hari'];
            if (!$allowSaturday && strcasecmp($h, 'Sabtu') === 0) {
                continue;
            }

            if (!isset($curSegIdx[$h])) {
                $curSegIdx[$h] = 0;
                $segHasKbm[$h] = false;
            }

            $isKbm = ($slot['jenis_slot'] === 'kbm' && (int)$slot['jam_ke'] > 0);
            if (!$isKbm) {
                if ($segHasKbm[$h]) {
                    $curSegIdx[$h]++;
                    $segHasKbm[$h] = false;
                }
                continue;
            }

            $jk = (int)$slot['jam_ke'];
            $segId = $curSegIdx[$h];
            $segHasKbm[$h] = true;

            $slotLookup[$h][$jk] = $slot;
            $slotSegments[$h][$jk] = $segId;
            $segmentJps[$h][$segId][] = $jk;

            if (!in_array($h, $days, true)) {
                $days[] = $h;
            }
            $maxJpPerDay[$h] = max($maxJpPerDay[$h] ?? 0, $jk);
            $maxSegmentLen = max($maxSegmentLen, count($segmentJps[$h][$segId]));
        }

        return [
            'slotLookup'    => $slotLookup,
            'slotSegments'  => $slotSegments,
            'segmentJps'    => $segmentJps,
            'days'          => $days,
            'maxJpPerDay'   => $maxJpPerDay,
            'maxSegmentLen' => $maxSegmentLen
        ];
    }

    /**
     * Pecah penugasan mengajar menjadi blok-blok JP sesuai aturan sesi & jumlah hari
     */
    private function buildLessonBlocks(array $teachingData, int $effectiveMaxBlock, array $days): array
    {
        $blocks = [];
        $classes = [];
        $teachers = [];
        $teacherTotalJp = [];
        $kelasTotalJp = [];
        $uid = 1;
        $numDays = max(1, count($days));

        foreach ($teachingData as $item) {
            $kelas = trim($item['nama_kelas']);
            $guruId = (int)$item['pegawai_id'];
            $jpTotal = (int)$item['jumlah_jp'];

            if (!in_array($kelas, $classes, true)) $classes[] = $kelas;
            if (!in_array($guruId, $teachers, true)) $teachers[] = $guruId;
            $teacherTotalJp[$guruId] = ($teacherTotalJp[$guruId] ?? 0) + $jpTotal;
            $kelasTotalJp[$kelas] = ($kelasTotalJp[$kelas] ?? 0) + $jpTotal;

            // Tentukan pecahan blok ideal untuk total JP mapel ini
            $sizes = [];
            $rem = $jpTotal;
            while ($rem > 0) {
                if ($effectiveMaxBlock >= 4 && $rem >= 4 && ($rem == 4 || $rem - 4 >= 2)) {
                    $bs = 4;
                } elseif ($effectiveMaxBlock >= 3 && ($rem == 3 || $rem == 5 || ($rem >= 6 && $rem != 4))) {
                    $bs = 3;
                } elseif ($rem >= 2) {
                    $bs = 2;
                } else {
                    $bs = 1;
                }
                $sizes[] = $bs;
                $rem -= $bs;
            }

            // Jika jumlah blok melebihi jumlah hari aktif dan masih bisa digabung sesuai effectiveMaxBlock
            if (count($sizes) > $numDays && $effectiveMaxBlock >= 3) {
                // Coba gabungkan blok 1 JP ke blok 2 JP -> 3 JP agar muat di jumlah hari tersedia
                while (count($sizes) > $numDays && in_array(1, $sizes, true) && in_array(2, $sizes, true)) {
                    $idx1 = array_search(1, $sizes, true);
                    $idx2 = array_search(2, $sizes, true);
                    unset($sizes[$idx1], $sizes[$idx2]);
                    $sizes[] = 3;
                    $sizes = array_values($sizes);
                }
            }

            foreach ($sizes as $bs) {
                $blocks[] = [
                    'uid'                   => $uid++,
                    'penugasan_mengajar_id' => $item['penugasan_mengajar_id'],
                    'pegawai_id'            => $guruId,
                    'nama_guru'             => $item['nama_guru'],
                    'mata_pelajaran'        => $item['mata_pelajaran'],
                    'nama_kelas'            => $kelas,
                    'kelas_id'              => $item['kelas_id'],
                    'block_size'            => $bs,
                    'total_jp_mapel'        => $jpTotal
                ];
            }
        }

        return [
            'blocks'         => $blocks,
            'classes'        => $classes,
            'teachers'       => $teachers,
            'teacherTotalJp' => $teacherTotalJp,
            'kelasTotalJp'   => $kelasTotalJp
        ];
    }

    /**
     * Eksekusi 1 pass penjadwalan dengan heuristic anti-fragmentasi + swap/relocation repair
     */
    private function runSolveAttempt(
        array $baseBlocks,
        array $days,
        array $slotLookup,
        array $slotSegments,
        array $segmentJps,
        array $maxJpPerDay,
        array $teacherTotalJp,
        array $kelasTotalJp,
        int $attempt
    ): array {
        $blocks = $baseBlocks;

        // Urutkan blok: Blok terbesar dulu -> guru beban tertinggi -> kelas beban tertinggi
        if ($attempt > 0) {
            mt_srand($attempt * 9973 + 17);
            $noiseMap = [];
            foreach ($blocks as $b) {
                $noiseMap[$b['uid']] = mt_rand(0, 50);
            }
        } else {
            $noiseMap = [];
        }

        usort($blocks, function ($a, $b) use ($teacherTotalJp, $kelasTotalJp, $noiseMap) {
            if ($a['block_size'] !== $b['block_size']) {
                return $b['block_size'] - $a['block_size'];
            }
            $scoreA = ($teacherTotalJp[$a['pegawai_id']] ?? 0) * 10 + ($kelasTotalJp[$a['nama_kelas']] ?? 0) + ($noiseMap[$a['uid']] ?? 0);
            $scoreB = ($teacherTotalJp[$b['pegawai_id']] ?? 0) * 10 + ($kelasTotalJp[$b['nama_kelas']] ?? 0) + ($noiseMap[$b['uid']] ?? 0);
            if ($scoreA !== $scoreB) {
                return $scoreB - $scoreA;
            }
            return $a['uid'] - $b['uid'];
        });

        $occupiedGuru = [];    // [guruId][day][jk] => blockUid
        $occupiedKelas = [];   // [kelas][day][jk] => blockUid
        $kelasMapelDay = [];   // [kelas][day][mapel] => count
        $placedBlocks = [];    // [blockUid] => ['block' => ..., 'day' => ..., 'jps' => [...]]
        $dayIndexMap = array_flip($days);

        $unplacedCount = 0;
        $splitCount = 0;

        foreach ($blocks as $block) {
            $cand = $this->findBestCandidate(
                $block,
                $days,
                $dayIndexMap,
                $slotLookup,
                $slotSegments,
                $segmentJps,
                $maxJpPerDay,
                $occupiedGuru,
                $occupiedKelas,
                $kelasMapelDay,
                $placedBlocks,
                false
            );

            if ($cand !== null) {
                $this->placeBlockAt($block, $cand['day'], $cand['jps'], $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
                continue;
            }

            // 1. Swap / Relocation Repair dengan aturan ketat (tanpa duplikat mapel di hari sama)
            if ($this->trySwapRepair(
                $block,
                $days,
                $dayIndexMap,
                $slotLookup,
                $slotSegments,
                $segmentJps,
                $maxJpPerDay,
                $occupiedGuru,
                $occupiedKelas,
                $kelasMapelDay,
                $placedBlocks,
                false
            )) {
                continue;
            }

            // 2. Coba penempatan langsung jika diizinkan mapel muncul lagi di hari sama
            $candSameDay = $this->findBestCandidate(
                $block,
                $days,
                $dayIndexMap,
                $slotLookup,
                $slotSegments,
                $segmentJps,
                $maxJpPerDay,
                $occupiedGuru,
                $occupiedKelas,
                $kelasMapelDay,
                $placedBlocks,
                true
            );
            if ($candSameDay !== null) {
                $this->placeBlockAt($block, $candSameDay['day'], $candSameDay['jps'], $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
                continue;
            }

            // 3. Swap / Relocation Repair dengan relaksasi same-day sebelum pernah memecah blok
            if ($this->trySwapRepair(
                $block,
                $days,
                $dayIndexMap,
                $slotLookup,
                $slotSegments,
                $segmentJps,
                $maxJpPerDay,
                $occupiedGuru,
                $occupiedKelas,
                $kelasMapelDay,
                $placedBlocks,
                true
            )) {
                continue;
            }

            // 2. Fallback terakhir jika block_size > 1:
            //    Prioritaskan 2 slot dalam HARI YANG SAMA (misal dipisah istirahat) sebelum dipecah ke hari berbeda!
            if ($block['block_size'] > 1) {
                $placedParts = 0;
                $sameDayPlaced = $this->tryPlaceSplitSameDay(
                    $block,
                    $days,
                    $slotLookup,
                    $maxJpPerDay,
                    $occupiedGuru,
                    $occupiedKelas,
                    $kelasMapelDay,
                    $placedBlocks
                );

                if ($sameDayPlaced) {
                    $splitCount++;
                    continue;
                }

                for ($sub = 0; $sub < $block['block_size']; $sub++) {
                    $subBlock = $block;
                    $subBlock['uid'] = ($block['uid'] * 100) + $sub;
                    $subBlock['block_size'] = 1;
                    $subCand = $this->findBestCandidate(
                        $subBlock,
                        $days,
                        $dayIndexMap,
                        $slotLookup,
                        $slotSegments,
                        $segmentJps,
                        $maxJpPerDay,
                        $occupiedGuru,
                        $occupiedKelas,
                        $kelasMapelDay,
                        $placedBlocks,
                        true
                    );
                    if ($subCand !== null) {
                        $this->placeBlockAt($subBlock, $subCand['day'], $subCand['jps'], $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
                        $placedParts++;
                    } else {
                        $unplacedCount++;
                    }
                }
                if ($placedParts > 0) {
                    $splitCount++;
                }
            } else {
                $unplacedCount++;
            }
        }

        // Hitung statistik kualitas jadwal (penalty)
        $sameDayDupCount = 0;
        $adjDayDupCount = 0;
        foreach ($kelasMapelDay as $kelas => $dayMap) {
            foreach ($days as $idx => $d) {
                foreach (($dayMap[$d] ?? []) as $mapel => $cnt) {
                    if ($cnt > 1 && $this->countBlocksForKelasMapelDay($placedBlocks, $kelas, $d, $mapel) > 1) {
                        $sameDayDupCount++;
                    }
                    if ($idx > 0 && !empty($dayMap[$days[$idx - 1]][$mapel])) {
                        $adjDayDupCount++;
                    }
                }
            }
        }

        // Konversi $placedBlocks menjadi daftar record jadwal terurut
        $scheduledItems = [];
        foreach ($placedBlocks as $pb) {
            $blk = $pb['block'];
            $day = $pb['day'];
            foreach ($pb['jps'] as $jk) {
                $slotObj = $slotLookup[$day][$jk];
                $scheduledItems[] = [
                    'grup_id'               => $this->grupId,
                    'slot_waktu_id'         => $slotObj['id'],
                    'hari'                  => $day,
                    'jam_ke'                => $jk,
                    'jam_mulai'             => $slotObj['jam_mulai'],
                    'jam_selesai'           => $slotObj['jam_selesai'],
                    'kelas_id'              => $blk['kelas_id'],
                    'nama_kelas'            => $blk['nama_kelas'],
                    'jenjang'               => $slotObj['jenjang'] ?? 'SD',
                    'pegawai_id'            => $blk['pegawai_id'],
                    'nama_guru'             => $blk['nama_guru'],
                    'mata_pelajaran'        => $blk['mata_pelajaran'],
                    'penugasan_mengajar_id' => $blk['penugasan_mengajar_id']
                ];
            }
        }

        $penalty = ($unplacedCount * 100000) + ($splitCount * 10000) + ($sameDayDupCount * 200) + ($adjDayDupCount * 10);

        return [
            'scheduledItems'     => $scheduledItems,
            'unplaced_count'     => $unplacedCount,
            'split_count'        => $splitCount,
            'same_day_dup_count' => $sameDayDupCount,
            'penalty'            => $penalty
        ];
    }

    /**
     * Cari slot terbaik (day, contiguous jam_ke list) yang tidak bentrok kelas, guru, dan batas istirahat
     */
    private function findBestCandidate(
        array $block,
        array $days,
        array $dayIndexMap,
        array $slotLookup,
        array $slotSegments,
        array $segmentJps,
        array $maxJpPerDay,
        array $occupiedGuru,
        array $occupiedKelas,
        array $kelasMapelDay,
        array $placedBlocks,
        bool $allowSameDayMapel
    ): ?array {
        $guruId = $block['pegawai_id'];
        $kelas = $block['nama_kelas'];
        $mapel = $block['mata_pelajaran'];
        $blockSize = $block['block_size'];

        $bestCand = null;
        $bestScore = PHP_INT_MAX;

        foreach ($days as $day) {
            $alreadyHasMapel = !empty($kelasMapelDay[$kelas][$day][$mapel]);
            if (!$allowSameDayMapel && $alreadyHasMapel) {
                continue;
            }

            $maxJp = $maxJpPerDay[$day] ?? 0;
            if ($maxJp < $blockSize) {
                continue;
            }

            for ($startJp = 1; $startJp <= ($maxJp - $blockSize + 1); $startJp++) {
                if (!isset($slotLookup[$day][$startJp])) {
                    continue;
                }
                $segId = $slotSegments[$day][$startJp] ?? -1;
                $canFit = true;
                $jps = [];

                for ($k = 0; $k < $blockSize; $k++) {
                    $checkJp = $startJp + $k;
                    if (
                        !isset($slotLookup[$day][$checkJp]) ||
                        ($slotSegments[$day][$checkJp] ?? -2) !== $segId ||
                        !empty($occupiedKelas[$kelas][$day][$checkJp]) ||
                        !empty($occupiedGuru[$guruId][$day][$checkJp])
                    ) {
                        $canFit = false;
                        break;
                    }
                    $jps[] = $checkJp;
                }

                if (!$canFit) {
                    continue;
                }

                $score = $this->scoreCandidatePlacement(
                    $block,
                    $day,
                    $startJp,
                    $segId,
                    $days,
                    $dayIndexMap,
                    $segmentJps,
                    $occupiedGuru,
                    $occupiedKelas,
                    $kelasMapelDay,
                    $placedBlocks
                );

                if ($score < $bestScore) {
                    $bestScore = $score;
                    $bestCand = ['day' => $day, 'jps' => $jps, 'score' => $score];
                }
            }
        }

        return $bestCand;
    }

    /**
     * Hitung skor kualitas penempatan kandidat (semakin kecil semakin optimal)
     */
    private function scoreCandidatePlacement(
        array $block,
        string $day,
        int $startJp,
        int $segId,
        array $days,
        array $dayIndexMap,
        array $segmentJps,
        array $occupiedGuru,
        array $occupiedKelas,
        array $kelasMapelDay,
        array $placedBlocks
    ): int {
        $guruId = $block['pegawai_id'];
        $kelas = $block['nama_kelas'];
        $mapel = $block['mata_pelajaran'];
        $blockSize = $block['block_size'];

        $score = 0;

        // 1. Penalti sangat besar jika mapel sudah ada di hari yang sama
        if (!empty($kelasMapelDay[$kelas][$day][$mapel])) {
            $score += 50000;
        }

        // 2. Penalti jika mapel ini sudah muncul pada hari berdekatan (kemarin / besok)
        $dIdx = $dayIndexMap[$day] ?? 0;
        if ($dIdx > 0 && !empty($kelasMapelDay[$kelas][$days[$dIdx - 1]][$mapel])) {
            $score += 350;
        }
        if (isset($days[$dIdx + 1]) && !empty($kelasMapelDay[$kelas][$days[$dIdx + 1]][$mapel])) {
            $score += 350;
        }

        // 3. Analisis kekosongan segmen kelas agar tidak menciptakan lubang 1-JP yatim
        $segList = $segmentJps[$day][$segId] ?? [];
        $freeBefore = 0;
        for ($jp = $startJp - 1; in_array($jp, $segList, true); $jp--) {
            if (!empty($occupiedKelas[$kelas][$day][$jp])) break;
            $freeBefore++;
        }
        $freeAfter = 0;
        $endJp = $startJp + $blockSize - 1;
        for ($jp = $endJp + 1; in_array($jp, $segList, true); $jp++) {
            if (!empty($occupiedKelas[$kelas][$day][$jp])) break;
            $freeAfter++;
        }

        if ($blockSize >= 2) {
            if ($freeBefore === 0 && $freeAfter === 0) {
                // Pas 100% mengisi sisa segmen!
                $score -= 180;
            } elseif ($freeBefore > 0 && $freeAfter > 0) {
                // Membelah ruang kosong menjadi dua sisi (sering meninggalkan 2 lubang 1-JP!)
                $score += 500;
            } else {
                $remGap = max($freeBefore, $freeAfter);
                if ($remGap % 2 === 0) {
                    // Menyisakan ruang genap (bisa diisi blok 2 JP lagi!)
                    $score -= 120;
                } else {
                    // Menyisakan lubang ganjil (1 JP)
                    $score += 90;
                }
            }
        } else {
            // Untuk blok 1 JP: utamakan menambal lubang ganjil (runLen 1 atau 3) agar tidak merusak ruang 2/4 JP
            $runLen = $freeBefore + 1 + $freeAfter;
            if ($runLen === 1) {
                $score -= 350;
            } elseif ($runLen % 2 === 1 && ($freeBefore === 0 || $freeAfter === 0)) {
                $score -= 220;
            } elseif ($freeBefore > 0 && $freeAfter > 0) {
                $score += 350;
            } else {
                $score += 220;
            }

            // Sebar blok 1 JP ke hari berbeda (hindari menumpuk banyak mapel 1 JP di 1 hari)
            $singleCountToday = 0;
            foreach ($placedBlocks as $pb) {
                if ($pb['day'] === $day && $pb['block']['nama_kelas'] === $kelas && $pb['block']['block_size'] === 1) {
                    $singleCountToday++;
                }
            }
            $score += ($singleCountToday * 160);
        }

        // 4. Penyeimbangan beban kelas & guru per hari
        $kelasDayLoad = count($occupiedKelas[$kelas][$day] ?? []);
        $guruDayLoad = count($occupiedGuru[$guruId][$day] ?? []);
        $score += ($kelasDayLoad * 18) + ($guruDayLoad * 8);

        // 5. Sedikit preferensi memadatkan jadwal guru yang sudah punya jam di segmen/hari tsb (tanpa mengganggu keseimbangan kelas)
        if ($guruDayLoad > 0) {
            $hasAdjTeacher = !empty($occupiedGuru[$guruId][$day][$startJp - 1]) || !empty($occupiedGuru[$guruId][$day][$endJp + 1]);
            if ($hasAdjTeacher) {
                $score -= 25;
            }
        }

        return $score;
    }

    /**
     * Coba lakukan Swap / Relocation 1-2 blok pengganggu agar blok berukuran >= 2 JP dapat masuk utuh tanpa dipecah!
     */
    private function trySwapRepair(
        array $block,
        array $days,
        array $dayIndexMap,
        array $slotLookup,
        array $slotSegments,
        array $segmentJps,
        array $maxJpPerDay,
        array &$occupiedGuru,
        array &$occupiedKelas,
        array &$kelasMapelDay,
        array &$placedBlocks,
        bool $allowSameDayTarget = false
    ): bool {
        $guruId = $block['pegawai_id'];
        $kelas = $block['nama_kelas'];
        $mapel = $block['mata_pelajaran'];
        $blockSize = $block['block_size'];

        $windows = [];
        foreach ($days as $day) {
            $hasSameMapel = !empty($kelasMapelDay[$kelas][$day][$mapel]);
            if (!$allowSameDayTarget && $hasSameMapel) {
                continue;
            }
            $maxJp = $maxJpPerDay[$day] ?? 0;

            for ($startJp = 1; $startJp <= ($maxJp - $blockSize + 1); $startJp++) {
                if (!isset($slotLookup[$day][$startJp])) continue;
                $segId = $slotSegments[$day][$startJp] ?? -1;

                $validSeg = true;
                $jps = [];
                $blockerMap = [];

                for ($k = 0; $k < $blockSize; $k++) {
                    $checkJp = $startJp + $k;
                    if (!isset($slotLookup[$day][$checkJp]) || ($slotSegments[$day][$checkJp] ?? -2) !== $segId) {
                        $validSeg = false;
                        break;
                    }
                    $jps[] = $checkJp;
                    if (!empty($occupiedKelas[$kelas][$day][$checkJp])) {
                        $uid = $occupiedKelas[$kelas][$day][$checkJp];
                        $blockerMap[$uid] = true;
                    }
                    if (!empty($occupiedGuru[$guruId][$day][$checkJp])) {
                        $uid = $occupiedGuru[$guruId][$day][$checkJp];
                        $blockerMap[$uid] = true;
                    }
                }

                if (!$validSeg || empty($blockerMap) || count($blockerMap) > 2) {
                    continue;
                }

                $blockerUids = array_keys($blockerMap);
                $priority = ($hasSameMapel ? 100 : 0) + (count($blockerUids) * 10);
                // Prioritaskan memindahkan blocker berukuran kecil (1 JP) terlebih dahulu
                foreach ($blockerUids as $bUid) {
                    $priority += ($placedBlocks[$bUid]['block']['block_size'] ?? 2);
                }

                $windows[] = [
                    'day'      => $day,
                    'jps'      => $jps,
                    'blockers' => $blockerUids,
                    'priority' => $priority
                ];
            }
        }

        usort($windows, fn($a, $b) => $a['priority'] - $b['priority']);

        foreach ($windows as $win) {
            // Simpan snapshot state sebelum mencoba swap
            $removedEntries = [];
            foreach ($win['blockers'] as $bUid) {
                if (!isset($placedBlocks[$bUid])) continue;
                $removedEntries[] = $placedBlocks[$bUid];
                $this->removeBlock($bUid, $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
            }

            // Pastikan kini slot benar-benar kosong untuk $block
            $nowFree = true;
            foreach ($win['jps'] as $jk) {
                if (!empty($occupiedKelas[$kelas][$win['day']][$jk]) || !empty($occupiedGuru[$guruId][$win['day']][$jk])) {
                    $nowFree = false;
                    break;
                }
            }

            if ($nowFree) {
                $this->placeBlockAt($block, $win['day'], $win['jps'], $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);

                // Urutkan blocker yang dilepas (terbesar dulu) dan coba tempatkan kembali ke slot lain yang kosong
                usort($removedEntries, fn($a, $b) => $b['block']['block_size'] - $a['block']['block_size']);
                $allReplaced = true;
                $newlyPlacedUids = [$block['uid']];

                foreach ($removedEntries as $remEntry) {
                    $remBlk = $remEntry['block'];
                    $cand = $this->findBestCandidate(
                        $remBlk,
                        $days,
                        $dayIndexMap,
                        $slotLookup,
                        $slotSegments,
                        $segmentJps,
                        $maxJpPerDay,
                        $occupiedGuru,
                        $occupiedKelas,
                        $kelasMapelDay,
                        $placedBlocks,
                        false
                    );
                    if ($cand === null) {
                        $cand = $this->findBestCandidate(
                            $remBlk,
                            $days,
                            $dayIndexMap,
                            $slotLookup,
                            $slotSegments,
                            $segmentJps,
                            $maxJpPerDay,
                            $occupiedGuru,
                            $occupiedKelas,
                            $kelasMapelDay,
                            $placedBlocks,
                            true
                        );
                    }
                    if ($cand !== null) {
                        $this->placeBlockAt($remBlk, $cand['day'], $cand['jps'], $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
                        $newlyPlacedUids[] = $remBlk['uid'];
                    } else {
                        $allReplaced = false;
                        break;
                    }
                }

                if ($allReplaced) {
                    return true;
                }

                // Rollback perubahan jika gagal menempatkan kembali semua blocker
                foreach ($newlyPlacedUids as $nUid) {
                    $this->removeBlock($nUid, $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
                }
            }

            // Kembalikan seluruh blocker ke posisi awal
            foreach ($removedEntries as $remEntry) {
                $this->placeBlockAt($remEntry['block'], $remEntry['day'], $remEntry['jps'], $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
            }
        }

        return false;
    }

    /**
     * Jika blok 2 JP benar-benar tidak dapat berdampingan, usahakan tetap berada di HARI YANG SAMA (misal dipisah istirahat)
     */
    private function tryPlaceSplitSameDay(
        array $block,
        array $days,
        array $slotLookup,
        array $maxJpPerDay,
        array &$occupiedGuru,
        array &$occupiedKelas,
        array &$kelasMapelDay,
        array &$placedBlocks
    ): bool {
        $guruId = $block['pegawai_id'];
        $kelas = $block['nama_kelas'];
        $need = $block['block_size'];

        foreach ($days as $day) {
            $maxJp = $maxJpPerDay[$day] ?? 0;
            $freeJps = [];
            for ($jk = 1; $jk <= $maxJp; $jk++) {
                if (
                    isset($slotLookup[$day][$jk]) &&
                    empty($occupiedKelas[$kelas][$day][$jk]) &&
                    empty($occupiedGuru[$guruId][$day][$jk])
                ) {
                    $freeJps[] = $jk;
                    if (count($freeJps) >= $need) {
                        break;
                    }
                }
            }

            if (count($freeJps) >= $need) {
                for ($i = 0; $i < $need; $i++) {
                    $subBlock = $block;
                    $subBlock['uid'] = ($block['uid'] * 100) + $i;
                    $subBlock['block_size'] = 1;
                    $this->placeBlockAt($subBlock, $day, [$freeJps[$i]], $occupiedGuru, $occupiedKelas, $kelasMapelDay, $placedBlocks);
                }
                return true;
            }
        }

        return false;
    }

    private function placeBlockAt(
        array $block,
        string $day,
        array $jps,
        array &$occupiedGuru,
        array &$occupiedKelas,
        array &$kelasMapelDay,
        array &$placedBlocks
    ): void {
        $uid = $block['uid'];
        $guruId = $block['pegawai_id'];
        $kelas = $block['nama_kelas'];
        $mapel = $block['mata_pelajaran'];

        foreach ($jps as $jk) {
            $occupiedKelas[$kelas][$day][$jk] = $uid;
            $occupiedGuru[$guruId][$day][$jk] = $uid;
        }
        $kelasMapelDay[$kelas][$day][$mapel] = ($kelasMapelDay[$kelas][$day][$mapel] ?? 0) + count($jps);
        $placedBlocks[$uid] = [
            'block' => $block,
            'day'   => $day,
            'jps'   => $jps
        ];
    }

    private function removeBlock(
        int $uid,
        array &$occupiedGuru,
        array &$occupiedKelas,
        array &$kelasMapelDay,
        array &$placedBlocks
    ): void {
        if (!isset($placedBlocks[$uid])) {
            return;
        }
        $entry = $placedBlocks[$uid];
        $block = $entry['block'];
        $day = $entry['day'];
        $jps = $entry['jps'];
        $guruId = $block['pegawai_id'];
        $kelas = $block['nama_kelas'];
        $mapel = $block['mata_pelajaran'];

        foreach ($jps as $jk) {
            unset($occupiedKelas[$kelas][$day][$jk], $occupiedGuru[$guruId][$day][$jk]);
        }
        if (!empty($kelasMapelDay[$kelas][$day][$mapel])) {
            $kelasMapelDay[$kelas][$day][$mapel] -= count($jps);
            if ($kelasMapelDay[$kelas][$day][$mapel] <= 0) {
                unset($kelasMapelDay[$kelas][$day][$mapel]);
            }
        }
        unset($placedBlocks[$uid]);
    }

    private function countBlocksForKelasMapelDay(array $placedBlocks, string $kelas, string $day, string $mapel): int
    {
        $cnt = 0;
        foreach ($placedBlocks as $pb) {
            if ($pb['day'] === $day && $pb['block']['nama_kelas'] === $kelas && $pb['block']['mata_pelajaran'] === $mapel) {
                $cnt++;
            }
        }
        return $cnt;
    }
}
