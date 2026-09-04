<?php
/**
 * HariEfektifEngine
 * 
 * Auto-Generates Rincian Hari Efektif Sekolah (HES) dan Hari Efektif Belajar (HEB)
 * based on Kalender Pendidikan (Kaldik) and Active Timetable (Jadwal Pelajaran)
 * for a specific Teacher, Subject, and Class.
 */

if (!defined('CAL_GREGORIAN')) {
    define('CAL_GREGORIAN', 0);
}
if (!function_exists('cal_days_in_month')) {
    function cal_days_in_month($calendar, $month, $year) {
        return (int)date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
    }
}

class HariEfektifEngine
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Compute full Rincian Hari Efektif (HES & HEB)
     */
    public function hitung(
        string $unit = 'SD',
        string $tahunAjaran = '2026/2027',
        string $semester = 'Ganjil',
        int $pegawaiId = 0,
        string $namaKelas = '',
        string $mataPelajaran = '',
        ?int $kaldikId = null,
        ?int $grupJadwalId = null
    ): array {
        // 1. Parsing Tahun Mulai & Akhir
        $taParts = explode('/', $tahunAjaran);
        $startYear = isset($taParts[0]) ? (int)trim($taParts[0]) : (int)date('Y');
        $endYear = isset($taParts[1]) ? (int)trim($taParts[1]) : ($startYear + 1);

        $isGanjil = strtolower($semester) === 'ganjil';
        $targetYear = $isGanjil ? $startYear : $endYear;

        // Bulan-bulan dalam semester
        // Ganjil: Juli (7) s.d. Desember (12)
        // Genap: Januari (1) s.d. Juni (6)
        $monthNumbers = $isGanjil ? [7, 8, 9, 10, 11, 12] : [1, 2, 3, 4, 5, 6];
        $monthNames = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        // 2. Ambil Kaldik Aktif
        $kaldik = null;
        if ($kaldikId) {
            $kaldik = $this->db->find("SELECT * FROM perangkat_pembelajaran WHERE id = ? AND tipe = 'kaldik'", [$kaldikId]);
        }
        if (!$kaldik) {
            $kaldik = $this->db->find("
                SELECT * FROM perangkat_pembelajaran 
                WHERE tipe = 'kaldik' AND unit = ? AND semester = ? AND is_active = 1
                ORDER BY created_at DESC LIMIT 1
            ", [$unit, $semester]);
        }
        if (!$kaldik) {
            // Fallback kaldik
            $kaldik = $this->db->find("
                SELECT * FROM perangkat_pembelajaran 
                WHERE tipe = 'kaldik' AND unit = ?
                ORDER BY is_active DESC, created_at DESC LIMIT 1
            ", [$unit]);
        }

        $kaldikAgendas = [];
        if ($kaldik && !empty($kaldik['konten_json'])) {
            $kData = json_decode($kaldik['konten_json'], true);
            $kaldikAgendas = $kData['agendas'] ?? [];
        }

        // 3. Ambil Info Guru & Profil Lembaga/Kepala Sekolah dari Pengaturan Unit
        $guru = $this->db->find("SELECT id, nama, gelar, niy FROM pegawai WHERE id = ?", [$pegawaiId]);
        $guruNama = $guru ? trim(($guru['nama'] ?? '') . (!empty($guru['gelar']) ? ', ' . $guru['gelar'] : '')) : 'Guru Pengampu';

        // Profil Unit dari Pengaturan Sistem
        $unitProfile = $this->getUnitProfile($unit);
        $ksNama = $unitProfile['kepala_sekolah']['nama'];
        $ksNip = $unitProfile['kepala_sekolah']['nip'];
        $namaLembaga = $unitProfile['nama_lembaga'];
        $logoUrl = $unitProfile['logo_url'];

        // 4. Ambil Jadwal & Durasi JP
        $grupJadwal = null;
        if ($grupJadwalId) {
            $grupJadwal = $this->db->find("SELECT * FROM jadwal_grup WHERE id = ?", [$grupJadwalId]);
        }
        if (!$grupJadwal) {
            $grupJadwal = $this->db->find("
                SELECT * FROM jadwal_grup 
                WHERE jenjang = ? AND semester = ? AND is_active = 1
                ORDER BY created_at DESC LIMIT 1
            ", [$unit, $semester]);
        }
        if (!$grupJadwal) {
            $grupJadwal = $this->db->find("
                SELECT * FROM jadwal_grup 
                WHERE jenjang = ?
                ORDER BY is_active DESC, created_at DESC LIMIT 1
            ", [$unit]);
        }

        $durasiJp = 30; // Default SD 30 menit
        if ($grupJadwal) {
            $jpSetting = $this->db->find("SELECT durasi_jp_menit FROM jadwal_pengaturan_jp WHERE grup_id = ?", [$grupJadwal['id']]);
            if ($jpSetting && !empty($jpSetting['durasi_jp_menit'])) {
                $durasiJp = (int)$jpSetting['durasi_jp_menit'];
            } else {
                $durasiJp = ($unit === 'SMP') ? 40 : (($unit === 'SMA') ? 45 : (($unit === 'PAUD') ? 30 : 35));
            }
        }

        // Query slot jadwal untuk guru, kelas, dan mapel ini
        $jadwalSlots = [];
        $dayMap = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        $teachingDays = []; // [dayNumber => jpCount]
        $totalJpPerMinggu = 0;

        if ($pegawaiId && !empty($namaKelas) && !empty($mataPelajaran)) {
            $params = [];
            $whereGrup = "";
            if ($grupJadwal) {
                $whereGrup = "grup_id = ? AND ";
                $params[] = $grupJadwal['id'];
            }
            $params[] = $pegawaiId;
            $params[] = trim($namaKelas);
            $params[] = trim($mataPelajaran);

            $jadwalSlots = $this->db->findAll("
                SELECT hari, jam_ke, mata_pelajaran, nama_kelas
                FROM jadwal_pelajaran
                WHERE {$whereGrup}pegawai_id = ? AND LOWER(TRIM(nama_kelas)) = LOWER(?) AND LOWER(TRIM(mata_pelajaran)) = LOWER(?)
                ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_ke ASC
            ", $params);

            // Fallback: if no slots found with grup_id, search without grup_id
            if (empty($jadwalSlots) && $grupJadwal) {
                $jadwalSlots = $this->db->findAll("
                    SELECT hari, jam_ke, mata_pelajaran, nama_kelas
                    FROM jadwal_pelajaran
                    WHERE pegawai_id = ? AND LOWER(TRIM(nama_kelas)) = LOWER(?) AND LOWER(TRIM(mata_pelajaran)) = LOWER(?)
                    ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jam_ke ASC
                ", [$pegawaiId, trim($namaKelas), trim($mataPelajaran)]);
            }

            foreach ($jadwalSlots as $slot) {
                $dName = trim($slot['hari']);
                if (isset($dayMap[$dName])) {
                    $dNum = $dayMap[$dName];
                    $teachingDays[$dNum] = ($teachingDays[$dNum] ?? 0) + 1;
                    $totalJpPerMinggu++;
                }
            }
        }

        // Jika tidak ada di jadwal_pelajaran, cari dari penugasan mengajar
        if ($totalJpPerMinggu === 0 && $pegawaiId && !empty($namaKelas) && !empty($mataPelajaran)) {
            $penugasan = $this->db->find("
                SELECT jumlah_jp FROM pegawai_penugasan_mengajar 
                WHERE pegawai_id = ? AND nama_kelas = ? AND mata_pelajaran = ?
                LIMIT 1
            ", [$pegawaiId, $namaKelas, $mataPelajaran]);

            if ($penugasan && (int)$penugasan['jumlah_jp'] > 0) {
                $totalJpPerMinggu = (int)$penugasan['jumlah_jp'];
                // Default distribusi ke 2 hari: Selasa (2 JP) dan Kamis (2 JP) atau Senin (3 JP)
                if ($totalJpPerMinggu >= 4) {
                    $teachingDays[2] = 2; // Selasa 2 JP
                    $teachingDays[4] = $totalJpPerMinggu - 2; // Kamis 2 JP
                } elseif ($totalJpPerMinggu === 3) {
                    $teachingDays[2] = 2;
                    $teachingDays[4] = 1;
                } else {
                    $teachingDays[2] = $totalJpPerMinggu;
                }
            }
        }

        // Fallback default jika masih 0
        if ($totalJpPerMinggu === 0) {
            $totalJpPerMinggu = 4;
            $teachingDays[2] = 2; // Selasa 2 JP
            $teachingDays[4] = 2; // Kamis 2 JP
        }

        // 5. Komputasi HES & HEB Per Bulan
        $hesRows = [];
        $hebRows = [];
        $totalHes = 0;
        $totalHebHari = 0;
        $totalHebPekan = 0;
        $totalHebJamPel = 0;

        $noHes = 1;

        foreach ($monthNumbers as $mNum) {
            $mName = $monthNames[$mNum];
            $daysInMonth = (int)date('t', mktime(0, 0, 0, (int)$mNum, 1, (int)$targetYear));

            $hesCount = 0;
            $hebDaysCount = 0;
            $hebJpCount = 0;
            $activeWeekNumbers = [];
            $keteranganBulan = '';
            $validDates = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $targetYear, $mNum, $d);
                $dayOfWeek = (int)date('N', strtotime($dateStr)); // 1=Mon, 7=Sun
                $weekOfYear = (int)date('W', strtotime($dateStr));

                // Periksa apakah tanggal ini libur berdasarkan Kaldik
                $isHoliday = false;
                $agendaKegiatan = '';
                $isKbmDisabled = false;

                // Ekstrak tingkat dari nama kelas untuk perbandingan
                $tingkatKelas = '';
                if (!empty($namaKelas) && preg_match('/^([1-9]|1[0-2]|IX|IV|V?I{0,3})\b/i', $namaKelas, $matches)) {
                    $tingkatKelas = strtoupper($matches[1]);
                }

                foreach ($kaldikAgendas as $ag) {
                    $tMulai = $ag['tanggal_mulai'] ?? '';
                    $tSelesai = $ag['tanggal_selesai'] ?? $tMulai;
                    if ($dateStr >= $tMulai && $dateStr <= $tSelesai) {
                        // Cek pengecualian tingkat
                        $pengecualian = isset($ag['pengecualian_tingkat']) && $ag['pengecualian_tingkat'] !== '' 
                                        ? array_map('trim', explode(',', strtoupper($ag['pengecualian_tingkat']))) 
                                        : [];
                        if (!empty($tingkatKelas) && in_array($tingkatKelas, $pengecualian)) {
                            continue; // Abaikan agenda ini karena kelas ini dikecualikan
                        }

                        $kat = strtolower($ag['kategori'] ?? '');
                        if (in_array($kat, ['libur', 'libur_semester', 'libur_nasional', 'libur_khusus'])) {
                            $isHoliday = true;
                            $isKbmDisabled = true;
                            $agendaKegiatan = $ag['kegiatan'] ?? 'Libur';
                        }
                        if (in_array($kat, ['penilaian', 'ujian', 'sas', 'sts', 'pat', 'pas'])) {
                            $isKbmDisabled = true;
                            if (empty($keteranganBulan)) {
                                $keteranganBulan = strtoupper($ag['kegiatan'] ?? 'PENILAIAN AKHIR TAHUN');
                            }
                        }
                        if (empty($keteranganBulan) && !empty($ag['kegiatan']) && $kat !== 'kbm') {
                            $agendaKegiatan = $ag['kegiatan'];
                        }
                    }
                }

                // Hitung HES (Hari Efektif Sekolah): Hari Senin s.d. Jumat/Sabtu yang bukan libur
                if ($dayOfWeek <= 6 && !$isHoliday) {
                    $hesCount++;
                }

                // Hitung HEB (Hari Efektif Belajar): Hari mengajar guru yang bukan libur dan KBM aktif
                if (isset($teachingDays[$dayOfWeek])) {
                    if (!$isHoliday && !$isKbmDisabled) {
                        $hebDaysCount++;
                        $jpHariIni = $teachingDays[$dayOfWeek];
                        $hebJpCount += $jpHariIni;
                        $activeWeekNumbers[$weekOfYear] = true;
                        $validDates[] = $d;
                    }
                }
            }

            // Normalisasi pekan & JP bulan
            $hebPekanCount = count($activeWeekNumbers);

            // Keterangan khusus bulan jika HEB 0
            if ($hebDaysCount === 0 && empty($keteranganBulan)) {
                if ($mNum === 12) {
                    $keteranganBulan = 'PENILAIAN AKHIR TAHUN';
                } elseif ($mNum === 6) {
                    $keteranganBulan = 'PENILAIAN AKHIR TAHUN';
                }
            }

            // Tambahkan baris HES
            $hesRows[] = [
                'no' => $noHes++,
                'bulan' => $mName,
                'jumlah_hari' => $hesCount,
                'keterangan' => ''
            ];
            $totalHes += $hesCount;

            // Tambahkan baris HEB
            $hebRows[] = [
                'bulan' => $mName,
                'hari' => $hebDaysCount,
                'pekan' => $hebPekanCount > 0 ? $hebPekanCount : '-',
                'jam_pel' => $hebJpCount > 0 ? $hebJpCount : '-',
                'keterangan' => $keteranganBulan,
                'valid_dates' => $validDates
            ];
            $totalHebHari += $hebDaysCount;
            $totalHebPekan += $hebPekanCount;
            $totalHebJamPel += $hebJpCount;
        }

        return [
            'unit' => $unit,
            'nama_lembaga' => $namaLembaga,
            'logo_url' => $logoUrl,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
            'mata_pelajaran' => $mataPelajaran,
            'nama_kelas' => $namaKelas,
            'guru' => [
                'id' => $pegawaiId,
                'nama' => $guruNama,
                'niy' => $guru['niy'] ?? ''
            ],
            'kepala_sekolah' => [
                'nama' => $ksNama,
                'nip' => $ksNip,
                'niy' => $ksNip ?: ($guru['niy'] ?? '')
            ],
            'durasi_jp_menit' => $durasiJp,
            'jp_per_minggu' => $totalJpPerMinggu,
            'durasi_label' => "Durasi : {$durasiJp} menit x {$totalJpPerMinggu} jam pelajaran",
            'hes' => [
                'rows' => $hesRows,
                'total_hari' => $totalHes
            ],
            'heb' => [
                'rows' => $hebRows,
                'total_hari' => $totalHebHari,
                'total_pekan' => $totalHebPekan,
                'total_jam_pel' => $totalHebJamPel
            ],
            'summary' => [
                'jumlah_hari_efektif' => "{$totalHebHari} Hari",
                'banyaknya_jam_efektif' => "{$totalHebJamPel} Jam Pelajaran",
                'banyaknya_pekan_efektif' => "{$totalHebPekan} Pekan"
            ],
            'kaldik_id' => $kaldik['id'] ?? null,
            'grup_jadwal_id' => $grupJadwal['id'] ?? null
        ];
    }

    /**
     * Helper to load Unit Profile (School Name, Logo, Principal) from settings
     */
    public function getUnitProfile(string $unit): array
    {
        $unit = strtoupper(trim($unit));
        if (!in_array($unit, ['PAUD', 'SD', 'SMP', 'SMA'])) {
            $unit = 'SD';
        }

        $namaSetting = $this->db->find("SELECT setting_value FROM settings WHERE setting_key = ?", ["nama_sekolah_{$unit}"]);
        $logoSetting = $this->db->find("SELECT setting_value FROM settings WHERE setting_key = ?", ["logo_unit_{$unit}"]);
        $ksSetting = $this->db->find("SELECT setting_value FROM settings WHERE setting_key = ?", ["kepala_sekolah_{$unit}"]);
        $nipSetting = $this->db->find("SELECT setting_value FROM settings WHERE setting_key = ?", ["nip_kepala_sekolah_{$unit}"]);

        // Fallbacks
        $defaultNama = ($unit === 'SD') ? 'SD ISLAM TERPADU BINA INSAN PALU' : "{$unit} IT BINA INSAN PALU";
        $namaSekolah = (!empty($namaSetting['setting_value'])) ? $namaSetting['setting_value'] : $defaultNama;
        $logoUrl = (!empty($logoSetting['setting_value'])) ? $logoSetting['setting_value'] : null;
        $ksNama = (!empty($ksSetting['setting_value'])) ? $ksSetting['setting_value'] : ($unit === 'SD' ? 'FENI, S.Pd.I' : 'Kepala Sekolah');
        $ksNip = (!empty($nipSetting['setting_value'])) ? $nipSetting['setting_value'] : '';

        return [
            'unit' => $unit,
            'nama_lembaga' => $namaSekolah,
            'logo_url' => $logoUrl,
            'kepala_sekolah' => [
                'nama' => $ksNama,
                'nip' => $ksNip
            ]
        ];
    }
}
