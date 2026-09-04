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
     * Eksekusi Auto-Generator Jadwal Cerdas Bebas Bentrok
     */
    public function generate(): array
    {
        $startTime = microtime(true);

        // Ambil info grup jadwal untuk mengetahui Unit
        $grupInfo = $this->db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$this->grupId]);
        $targetUnit = $grupInfo['jenjang'] ?? 'SD'; // 'SD', 'SMP', 'SMA', 'PAUD', 'SEMUA'

        // 1. Ambil data Slot Waktu KBM yang tersedia untuk grup ini
        $availableSlots = $this->db->findAll("
            SELECT * FROM jadwal_slot_waktu 
            WHERE grup_id = ? AND jenis_slot = 'kbm' AND jam_ke > 0
            ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_ke ASC
        ", [$this->grupId]);

        if (empty($availableSlots)) {
            // Jika belum ada slot waktu, inisialisasi default unit ini
            $durasiDefault = ($targetUnit === 'SMP') ? 40 : (($targetUnit === 'SMA') ? 45 : (($targetUnit === 'PAUD') ? 30 : 35));
            $availableSlots = $this->inisialisasiSlotWaktuDefault($targetUnit, $durasiDefault, '07:15:00');
            // Filter hanya KBM
            $availableSlots = array_filter($availableSlots, fn($s) => $s['jenis_slot'] === 'kbm' && $s['jam_ke'] > 0);
        }

        // Struktur Slot per Hari & Jam Ke
        $slotLookup = []; // [hari][jam_ke] => slot object
        $days = [];
        $maxJpPerDay = [];

        foreach ($availableSlots as $slot) {
            $h = $slot['hari'];
            $jk = (int)$slot['jam_ke'];
            $slotLookup[$h][$jk] = $slot;
            if (!in_array($h, $days)) {
                $days[] = $h;
            }
            $maxJpPerDay[$h] = max($maxJpPerDay[$h] ?? 0, $jk);
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

        // 3. Ambil data Penugasan Mengajar dari tabel `pegawai_penugasan_mengajar` + join `pegawai`
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

        // Filter: Hanya sertakan penugasan untuk kelas yang ada pada data siswa Unit yang bersangkutan jika ada data siswa
        $teachingData = [];
        if (!empty($siswaClasses)) {
            foreach ($rawTeachingData as $item) {
                $namaKelasClean = strtolower(trim($item['nama_kelas']));
                if (in_array($namaKelasClean, $siswaClasses)) {
                    $teachingData[] = $item;
                }
            }
            // Fallback: Jika setelah difilter data kosong tapi rawTeachingData ada, gunakan rawTeachingData
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
                'total_scheduled' => 0
            ];
        }

        // 3. Pecah Penugasan menjadi Blok Pelajaran (Lessons)
        // Aturan: 4 JP -> 2 + 2 JP, 3 JP -> 2 + 1 JP (atau 3 JP), 2 JP -> 2 JP, 1 JP -> 1 JP
        $lessonBlocks = [];
        $classes = [];
        $teachers = [];
        $teacherTotalJp = [];

        foreach ($teachingData as $item) {
            $kelas = trim($item['nama_kelas']);
            $guruId = (int)$item['pegawai_id'];
            $jpTotal = (int)$item['jumlah_jp'];

            if (!in_array($kelas, $classes)) $classes[] = $kelas;
            if (!in_array($guruId, $teachers)) $teachers[] = $guruId;
            $teacherTotalJp[$guruId] = ($teacherTotalJp[$guruId] ?? 0) + $jpTotal;

            $remainingJp = $jpTotal;
            while ($remainingJp > 0) {
                if ($remainingJp >= 4) {
                    $blockSize = 2;
                } elseif ($remainingJp == 3) {
                    $blockSize = 2; // Pecah 2 + 1 agar variatif, atau 3 jika diinginkan
                } elseif ($remainingJp == 2) {
                    $blockSize = 2;
                } else {
                    $blockSize = 1;
                }

                $lessonBlocks[] = [
                    'penugasan_mengajar_id' => $item['penugasan_mengajar_id'],
                    'pegawai_id' => $guruId,
                    'nama_guru' => $item['nama_guru'],
                    'mata_pelajaran' => $item['mata_pelajaran'],
                    'nama_kelas' => $kelas,
                    'kelas_id' => $item['kelas_id'],
                    'block_size' => $blockSize
                ];

                $remainingJp -= $blockSize;
            }
        }

        // Urutkan blok pelajaran: Blok lebih besar dulu (Most Constrained First) + Guru dengan JP lebih banyak
        usort($lessonBlocks, function($a, $b) use ($teacherTotalJp) {
            if ($a['block_size'] !== $b['block_size']) {
                return $b['block_size'] - $a['block_size'];
            }
            $jpA = $teacherTotalJp[$a['pegawai_id']] ?? 0;
            $jpB = $teacherTotalJp[$b['pegawai_id']] ?? 0;
            return $jpB - $jpA;
        });

        // 4. Tracking Matrices untuk Collision Detection
        $occupiedGuru = [];  // [guruId][day][jam_ke] => true
        $occupiedKelas = []; // [kelas][day][jam_ke] => true
        $kelasMapelDay = []; // [kelas][day][mapel] => count
        $scheduledItems = [];
        $unplacedItems = [];

        // 5. Algoritma Penjadwalan Bebas Bentrok
        foreach ($lessonBlocks as $block) {
            $guruId = $block['pegawai_id'];
            $kelas = $block['nama_kelas'];
            $mapel = $block['mata_pelajaran'];
            $blockSize = $block['block_size'];

            $placed = false;

            // Cari hari dan slot yang paling ideal
            // Prioritaskan hari di mana kelas belum memiliki mapel ini
            $shuffledDays = $days;
            // Urutkan hari berdasarkan kepadatan kelas di hari tersebut (load balancing)
            usort($shuffledDays, function($d1, $d2) use ($occupiedKelas, $kelas, $kelasMapelDay, $mapel) {
                $hasMapel1 = !empty($kelasMapelDay[$kelas][$d1][$mapel]) ? 1 : 0;
                $hasMapel2 = !empty($kelasMapelDay[$kelas][$d2][$mapel]) ? 1 : 0;
                if ($hasMapel1 !== $hasMapel2) return $hasMapel1 - $hasMapel2;

                $count1 = count($occupiedKelas[$kelas][$d1] ?? []);
                $count2 = count($occupiedKelas[$kelas][$d2] ?? []);
                return $count1 - $count2;
            });

            foreach ($shuffledDays as $day) {
                $maxJp = $maxJpPerDay[$day] ?? 8;

                for ($startJp = 1; $startJp <= ($maxJp - $blockSize + 1); $startJp++) {
                    $canFit = true;

                    // Periksa apakah seluruh slot blok jam berurutan kosong untuk Kelas & Guru
                    for ($k = 0; $k < $blockSize; $k++) {
                        $checkJp = $startJp + $k;

                        // Cek apakah slot KBM ada di master slot waktu
                        if (!isset($slotLookup[$day][$checkJp])) {
                            $canFit = false;
                            break;
                        }

                        // Cek bentrok kelas
                        if (!empty($occupiedKelas[$kelas][$day][$checkJp])) {
                            $canFit = false;
                            break;
                        }

                        // Cek bentrok guru
                        if (!empty($occupiedGuru[$guruId][$day][$checkJp])) {
                            $canFit = false;
                            break;
                        }
                    }

                    if ($canFit) {
                        // Kunci slot dan catat jadwal!
                        for ($k = 0; $k < $blockSize; $k++) {
                            $assignedJp = $startJp + $k;
                            $slotObj = $slotLookup[$day][$assignedJp];

                            $occupiedKelas[$kelas][$day][$assignedJp] = true;
                            $occupiedGuru[$guruId][$day][$assignedJp] = true;
                            $kelasMapelDay[$kelas][$day][$mapel] = ($kelasMapelDay[$kelas][$day][$mapel] ?? 0) + 1;

                            $scheduledItems[] = [
                                'grup_id' => $this->grupId,
                                'slot_waktu_id' => $slotObj['id'],
                                'hari' => $day,
                                'jam_ke' => $assignedJp,
                                'jam_mulai' => $slotObj['jam_mulai'],
                                'jam_selesai' => $slotObj['jam_selesai'],
                                'kelas_id' => $block['kelas_id'],
                                'nama_kelas' => $kelas,
                                'jenjang' => $slotObj['jenjang'] ?? 'SD',
                                'pegawai_id' => $guruId,
                                'nama_guru' => $block['nama_guru'],
                                'mata_pelajaran' => $mapel,
                                'penugasan_mengajar_id' => $block['penugasan_mengajar_id']
                            ];
                        }
                        $placed = true;
                        break 2;
                    }
                }
            }

            if (!$placed) {
                // Fallback: coba pecah blok menjadi 1 JP individual jika blok > 1
                if ($blockSize > 1) {
                    for ($sub = 0; $sub < $blockSize; $sub++) {
                        $subPlaced = false;
                        foreach ($days as $day) {
                            $maxJp = $maxJpPerDay[$day] ?? 8;
                            for ($jk = 1; $jk <= $maxJp; $jk++) {
                                if (isset($slotLookup[$day][$jk]) && empty($occupiedKelas[$kelas][$day][$jk]) && empty($occupiedGuru[$guruId][$day][$jk])) {
                                    $slotObj = $slotLookup[$day][$jk];
                                    $occupiedKelas[$kelas][$day][$jk] = true;
                                    $occupiedGuru[$guruId][$day][$jk] = true;

                                    $scheduledItems[] = [
                                        'grup_id' => $this->grupId,
                                        'slot_waktu_id' => $slotObj['id'],
                                        'hari' => $day,
                                        'jam_ke' => $jk,
                                        'jam_mulai' => $slotObj['jam_mulai'],
                                        'jam_selesai' => $slotObj['jam_selesai'],
                                        'kelas_id' => $block['kelas_id'],
                                        'nama_kelas' => $kelas,
                                        'jenjang' => $slotObj['jenjang'] ?? 'SD',
                                        'pegawai_id' => $guruId,
                                        'nama_guru' => $block['nama_guru'],
                                        'mata_pelajaran' => $mapel,
                                        'penugasan_mengajar_id' => $block['penugasan_mengajar_id']
                                    ];
                                    $subPlaced = true;
                                    break 2;
                                }
                            }
                        }
                        if (!$subPlaced) {
                            $unplacedItems[] = $block;
                        }
                    }
                } else {
                    $unplacedItems[] = $block;
                }
            }
        }

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

        // Update ringkasan grup
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
            'unplaced_count' => count($unplacedItems),
            'execution_time_ms' => $executionTime,
            'is_conflict_free' => true
        ];
    }
}
