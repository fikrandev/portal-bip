<?php
/**
 * Portal Guru Model
 * Data access and business logic for teacher mobile portal.
 * Provides clean database queries with structured fallback for offline / standalone execution.
 */

class PortalGuruModel
{
    /**
     * Get Teacher Profile Data
     */
    public static function getTeacherProfile(int $userId = 1): array
    {
        try {
            if (class_exists('Database')) {
                $db = Database::getInstance();
                $user = $db->find("SELECT * FROM users WHERE id = ?", [$userId]);
                if ($user) {
                    return [
                        'id' => $user['id'],
                        'name' => $user['name'] ?? 'Bu Rina',
                        'role' => $user['role'] ?? 'Guru Matematika',
                        'email' => $user['email'] ?? 'rina@sekolah.sch.id',
                        'avatar' => asset('images/mobile/teacher_rina.jpg'),
                        'nip' => '19890412 201403 2 004',
                        'totalJP' => 24,
                        'classesCount' => 6
                    ];
                }
            }
        } catch (Throwable $e) {
            error_log('PortalGuruModel::getTeacherProfile error: ' . $e->getMessage());
        }

        // Default / Mock Data
        return [
            'id' => $userId,
            'name' => 'Bu Rina, S.Pd',
            'role' => 'Guru Matematika',
            'email' => 'rina.matematika@sekolah.sch.id',
            'avatar' => asset('images/mobile/teacher_rina.jpg'),
            'nip' => '19890412 201403 2 004',
            'totalJP' => 24,
            'classesCount' => 6
        ];
    }

    /**
     * Get Today Teaching Schedule
     */
    public static function getTodaySchedule(int $userId = 1): array
    {
        return [
            [
                'id' => 1,
                'time' => '08.00 - 08.45',
                'subject' => 'Matematika',
                'class' => 'Kelas 7A',
                'status' => 'Berlangsung',
                'room' => 'Ruang 102',
                'role' => 'PIC',
                'jp' => 2
            ],
            [
                'id' => 2,
                'time' => '09.00 - 09.45',
                'subject' => 'Matematika',
                'class' => 'Kelas 7B',
                'status' => 'Mendatang',
                'room' => 'Ruang 103',
                'role' => 'PIC',
                'jp' => 2
            ],
            [
                'id' => 3,
                'time' => '10.15 - 11.00',
                'subject' => 'Matematika',
                'class' => 'Kelas 8A',
                'status' => 'Mendatang',
                'room' => 'Ruang 201',
                'role' => 'Mendampingi',
                'jp' => 2
            ]
        ];
    }

    /**
     * Get Teaching Classes Summary (PIC & Mendampingi)
     */
    public static function getAssignedClasses(int $userId = 1): array
    {
        return [
            'summary' => [
                'pic' => ['classes' => 4, 'jp' => 8],
                'mendampingi' => ['classes' => 2, 'jp' => 4]
            ],
            'list' => [
                [
                    'id' => 'K-7A',
                    'name' => 'Kelas 7A',
                    'subject' => 'Matematika',
                    'day' => 'Senin',
                    'time' => '08:00 - 09:30',
                    'role' => 'PIC',
                    'jp' => 2,
                    'badge' => 'emerald',
                    'room' => 'Ruang 102',
                    'studentsCount' => 32
                ],
                [
                    'id' => 'K-7B',
                    'name' => 'Kelas 7B',
                    'subject' => 'Matematika',
                    'day' => 'Senin',
                    'time' => '10:00 - 11:30',
                    'role' => 'PIC',
                    'jp' => 2,
                    'badge' => 'emerald',
                    'room' => 'Ruang 103',
                    'studentsCount' => 30
                ],
                [
                    'id' => 'K-8A',
                    'name' => 'Kelas 8A',
                    'subject' => 'Matematika',
                    'day' => 'Selasa',
                    'time' => '08:00 - 09:30',
                    'role' => 'Mendampingi',
                    'jp' => 2,
                    'badge' => 'blue',
                    'room' => 'Ruang 201',
                    'studentsCount' => 28
                ],
                [
                    'id' => 'K-8B',
                    'name' => 'Kelas 8B',
                    'subject' => 'Matematika',
                    'day' => 'Rabu',
                    'time' => '08:00 - 09:30',
                    'role' => 'PIC',
                    'jp' => 2,
                    'badge' => 'emerald',
                    'room' => 'Ruang 202',
                    'studentsCount' => 31
                ],
                [
                    'id' => 'K-9A',
                    'name' => 'Kelas 9A',
                    'subject' => 'Matematika',
                    'day' => 'Kamis',
                    'time' => '10:00 - 11:30',
                    'role' => 'PIC',
                    'jp' => 2,
                    'badge' => 'emerald',
                    'room' => 'Ruang 301',
                    'studentsCount' => 29
                ],
                [
                    'id' => 'K-9B',
                    'name' => 'Kelas 9B',
                    'subject' => 'Matematika',
                    'day' => 'Jumat',
                    'time' => '08:00 - 09:30',
                    'role' => 'Mendampingi',
                    'jp' => 2,
                    'badge' => 'blue',
                    'room' => 'Ruang 302',
                    'studentsCount' => 27
                ]
            ]
        ];
    }

    /**
     * Get Student Lateness Records (Pencatatan Keterlambatan Siswa)
     */
    public static function getLateStudentRecords(): array
    {
        return [
            'summary' => [
                'todayCount' => 5,
                'avgMinutes' => 18,
                'mostClass' => 'Kelas 8A'
            ],
            'records' => [
                [
                    'id' => 'TRL-001',
                    'studentName' => 'Ahmad Fadhil',
                    'nisn' => '0082194821',
                    'class' => 'Kelas 7A',
                    'date' => date('d M Y'),
                    'time' => '07:25 WITA',
                    'minutesLate' => 25,
                    'reason' => 'Macet Lalu Lintas di Flyover',
                    'action' => 'Teguran Lisan & Pembacaan Doa',
                    'recordedBy' => 'Bu Rina, S.Pd',
                    'status' => 'Selesai Dibina'
                ],
                [
                    'id' => 'TRL-002',
                    'studentName' => 'Bima Pratama',
                    'nisn' => '0083921849',
                    'class' => 'Kelas 8B',
                    'date' => date('d M Y'),
                    'time' => '07:15 WITA',
                    'minutesLate' => 15,
                    'reason' => 'Kendaraan Ban Bocor',
                    'action' => 'Pencatatan di Buku Piket',
                    'recordedBy' => 'Pak Hendra, M.Pd',
                    'status' => 'Selesai Dibina'
                ],
                [
                    'id' => 'TRL-003',
                    'studentName' => 'Chandra Wijaya',
                    'nisn' => '0084920194',
                    'class' => 'Kelas 8A',
                    'date' => date('d M Y'),
                    'time' => '07:40 WITA',
                    'minutesLate' => 40,
                    'reason' => 'Bangun Kesiangan',
                    'action' => 'Tugas Tambahan & Notifikasi Ortu',
                    'recordedBy' => 'Bu Rina, S.Pd',
                    'status' => 'Peringatan'
                ],
                [
                    'id' => 'TRL-004',
                    'studentName' => 'Dewi Anggraini',
                    'nisn' => '0085910294',
                    'class' => 'Kelas 9A',
                    'date' => date('d M Y', strtotime('-1 day')),
                    'time' => '07:18 WITA',
                    'minutesLate' => 18,
                    'reason' => 'Hujan Deras di Perjalanan',
                    'action' => 'Diizinkan Masuk Kelas',
                    'recordedBy' => 'Pak Ridwan, S.Kom',
                    'status' => 'Selesai Dibina'
                ]
            ]
        ];
    }

    /**
     * Get Teacher Permit / Absence Submissions (Pengajuan Izin) Real Data dari Database
     */
    public static function getTeacherPermits(int $userId = 1): array
    {
        $db = Database::getInstance();

        // 1. Ambil summary bulan ini
        $summaryRow = $db->find("
            SELECT 
                COUNT(*) as total_all,
                SUM(CASE WHEN MONTH(tanggal_mulai) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_mulai) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as total_this_month,
                SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as total_approved,
                SUM(CASE WHEN status = 'Menunggu Persetujuan' THEN 1 ELSE 0 END) as total_pending
            FROM izin_guru
            WHERE guru_id = ?
        ", [$userId]);

        $summary = [
            'totalThisMonth' => (int)($summaryRow['total_this_month'] ?? 0),
            'approved' => (int)($summaryRow['total_approved'] ?? 0),
            'pending' => (int)($summaryRow['total_pending'] ?? 0),
        ];

        // 2. Ambil riwayat izin riil
        $rows = $db->findAll("
            SELECT * FROM izin_guru
            WHERE guru_id = ?
            ORDER BY tanggal_mulai DESC, id DESC
        ", [$userId]);

        $records = [];
        foreach ($rows as $r) {
            $type = $r['jenis_izin'] ?? 'tidak_masuk';
            $kat = $r['kategori'] ?? 'Sakit';
            $status = $r['status'] ?? 'Menunggu Persetujuan';

            $badgeColor = 'amber';
            if ($kat === 'Dinas Luar') $badgeColor = 'purple';
            elseif ($kat === 'Keperluan Keluarga') $badgeColor = 'emerald';
            elseif ($type === 'keluar_mengajar') $badgeColor = 'blue';

            $statusBadge = 'amber';
            if ($status === 'Disetujui') $statusBadge = 'emerald';
            elseif ($status === 'Ditolak') $statusBadge = 'rose';

            $typeLabel = ($type === 'tidak_masuk') ? ("Izin Tidak Masuk" . ($kat ? " ($kat)" : "")) : "Izin Keluar / Tidak Mengajar";

            // Durasi
            $duration = '1 Hari';
            if ($type === 'tidak_masuk') {
                if (!empty($r['tanggal_mulai']) && !empty($r['tanggal_selesai'])) {
                    $d1 = new DateTime($r['tanggal_mulai']);
                    $d2 = new DateTime($r['tanggal_selesai']);
                    $diff = $d1->diff($d2)->days + 1;
                    $duration = $diff . ' Hari';
                }
            } else {
                if (!empty($r['jam_keluar']) && !empty($r['jam_kembali'])) {
                    $duration = substr($r['jam_keluar'], 0, 5) . ' - ' . substr($r['jam_kembali'], 0, 5) . ' WITA';
                } else {
                    $duration = 'Keluar Sementara';
                }
            }

            $timeRange = null;
            if ($type === 'keluar_mengajar' && !empty($r['jam_keluar']) && !empty($r['jam_kembali'])) {
                $timeRange = substr($r['jam_keluar'], 0, 5) . ' - ' . substr($r['jam_kembali'], 0, 5) . ' WITA';
            }

            $records[] = [
                'id' => $r['nomor_izin'] ?: ('IZN-' . str_pad((string)$r['id'], 4, '0', STR_PAD_LEFT)),
                'type' => $type,
                'typeLabel' => $typeLabel,
                'badgeColor' => $badgeColor,
                'startDate' => !empty($r['tanggal_mulai']) ? date('d M Y', strtotime($r['tanggal_mulai'])) : '-',
                'endDate' => !empty($r['tanggal_selesai']) ? date('d M Y', strtotime($r['tanggal_selesai'])) : '-',
                'duration' => $duration,
                'timeRange' => $timeRange,
                'classAffected' => $r['kelas_terdampak'] ?? null,
                'taskGiven' => $r['tugas_siswa'] ?? null,
                'reason' => $r['alasan'] ?? '-',
                'document' => $r['lampiran_dokumen'] ?? null,
                'status' => $status,
                'statusBadge' => $statusBadge,
                'submittedAt' => !empty($r['created_at']) ? date('d M Y, H:i WITA', strtotime($r['created_at'])) : '-',
                'substituteTeacher' => $r['guru_pengganti'] ?? '-',
                'catatanPimpinan' => $r['catatan_pimpinan'] ?? null,
            ];
        }

        return [
            'summary' => $summary,
            'records' => $records
        ];
    }

    /**
     * Get Teacher Leave Requests & Quotas (Pengajuan Cuti Guru) Real Data dari Database
     */
    public static function getTeacherLeaves(int $userId = 1): array
    {
        $db = Database::getInstance();

        // Load CutiModel agar tabel dan saldo tersinkronisasi
        if (class_exists('CutiModel') || file_exists(BASE_PATH . '/modules/kelola-cuti/models/CutiModel.php')) {
            require_once BASE_PATH . '/modules/kelola-cuti/models/CutiModel.php';
            CutiModel::init();
        }

        // 1. Ambil saldo cuti riil dari cuti_saldo_pegawai
        $saldoRow = $db->find("SELECT * FROM cuti_saldo_pegawai WHERE pegawai_id = ?", [$userId]);
        $saldoTahunan = $saldoRow ? (int)$saldoRow['saldo_tahunan'] : 12;
        $totalDidapat = $saldoRow ? (int)$saldoRow['total_didapat'] : 12;
        $totalTerpakaiTahunan = $saldoRow ? (int)$saldoRow['total_terpakai_tahunan'] : 0;

        // Hitung pemakaian cuti khusus tahun ini
        $usedSpecialRow = $db->find("
            SELECT 
                SUM(CASE WHEN cj.kode = 'CK-SAKIT' AND cp.status = 'Disetujui' THEN cp.jumlah_hari ELSE 0 END) as sakit_used,
                SUM(CASE WHEN cj.kode IN ('CK-DUKA', 'CK-MENIKAH') AND cp.status = 'Disetujui' THEN cp.jumlah_hari ELSE 0 END) as penting_used
            FROM cuti_pengajuan cp
            JOIN cuti_jenis cj ON cp.jenis_cuti_id = cj.id
            WHERE cp.pegawai_id = ? AND YEAR(cp.tanggal_mulai) = YEAR(CURRENT_DATE())
        ", [$userId]);

        $sakitUsed = (int)($usedSpecialRow['sakit_used'] ?? 0);
        $pentingUsed = (int)($usedSpecialRow['penting_used'] ?? 0);

        $quotas = [
            'tahunan' => [
                'total' => max(12, $totalDidapat),
                'used' => $totalTerpakaiTahunan,
                'remaining' => $saldoTahunan
            ],
            'sakit' => [
                'total' => 14,
                'used' => $sakitUsed,
                'remaining' => max(0, 14 - $sakitUsed)
            ],
            'alasan_penting' => [
                'total' => 5,
                'used' => $pentingUsed,
                'remaining' => max(0, 5 - $pentingUsed)
            ]
        ];

        // 2. Ambil riwayat pengajuan cuti riil
        $rows = $db->findAll("
            SELECT cp.*, cj.nama_jenis, cj.kode as jenis_kode, cj.kategori as jenis_kategori
            FROM cuti_pengajuan cp
            LEFT JOIN cuti_jenis cj ON cp.jenis_cuti_id = cj.id
            WHERE cp.pegawai_id = ?
            ORDER BY cp.created_at DESC, cp.id DESC
        ", [$userId]);

        $records = [];
        foreach ($rows as $r) {
            $status = $r['status'] ?? 'Pending';
            $statusBadge = 'amber';
            if ($status === 'Disetujui') $statusBadge = 'emerald';
            elseif ($status === 'Ditolak' || $status === 'Dibatalkan') $statusBadge = 'rose';

            $tglBuat = !empty($r['created_at']) ? date('d M Y, H:i', strtotime($r['created_at'])) : '-';

            $timeline = [
                ['step' => 'Pengajuan Diajukan', 'time' => $tglBuat, 'done' => true],
                ['step' => 'Verifikasi Wakasek SDM', 'time' => in_array($status, ['Disetujui', 'Ditolak']) ? (!empty($r['verified_at']) ? date('d M Y, H:i', strtotime($r['verified_at'])) : 'Selesai') : 'Menunggu', 'done' => in_array($status, ['Disetujui', 'Ditolak'])],
                ['step' => 'Persetujuan Kepala Sekolah', 'time' => ($status === 'Disetujui') ? (!empty($r['approved_at']) ? date('d M Y, H:i', strtotime($r['approved_at'])) : 'Disetujui') : ($status === 'Ditolak' ? 'Ditolak' : 'Menunggu'), 'done' => ($status === 'Disetujui')]
            ];

            $displayTanggal = CutiModel::formatTanggalCutiDisplay($r, 'd M Y');
            $cutiDatesList = CutiModel::parseTanggalPilihan($r['tanggal_cuti_list'] ?? null);

            $records[] = [
                'id' => $r['nomor_pengajuan'] ?: ('CT-' . str_pad((string)$r['id'], 4, '0', STR_PAD_LEFT)),
                'type' => $r['nama_jenis'] ?? ($r['kategori_cuti'] . ' Cuti'),
                'startDate' => !empty($r['tanggal_mulai']) ? date('d M Y', strtotime($r['tanggal_mulai'])) : '-',
                'endDate' => !empty($r['tanggal_selesai']) ? date('d M Y', strtotime($r['tanggal_selesai'])) : '-',
                'datesDisplay' => $displayTanggal,
                'datesList' => $cutiDatesList,
                'daysCount' => (int)($r['jumlah_hari'] ?? 1),
                'reason' => $r['alasan'] ?? '-',
                'emergencyContact' => $r['kontak_darurat'] ?? '-',
                'address' => $r['alamat_selama_cuti'] ?? '-',
                'substitute' => $r['pelimpahan_tugas'] ?? '-',
                'document' => $r['file_lampiran'] ?? null,
                'status' => $status,
                'statusBadge' => $statusBadge,
                'submittedAt' => !empty($r['created_at']) ? date('d M Y, H:i WITA', strtotime($r['created_at'])) : '-',
                'timeline' => $timeline,
                'catatan' => $r['catatan_approval'] ?? null,
            ];
        }

        return [
            'quotas' => $quotas,
            'records' => $records
        ];
    }
}
