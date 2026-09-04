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
     * Get Teacher Permit / Absence Submissions (Pengajuan Izin)
     */
    public static function getTeacherPermits(int $userId = 1): array
    {
        return [
            'summary' => [
                'totalThisMonth' => 3,
                'approved' => 2,
                'pending' => 1
            ],
            'records' => [
                [
                    'id' => 'IZN-2026-004',
                    'type' => 'tidak_masuk', // tidak_masuk (Full Day) or keluar_mengajar (Partial)
                    'typeLabel' => 'Izin Tidak Masuk (Sakit)',
                    'badgeColor' => 'amber',
                    'startDate' => date('d M Y'),
                    'endDate' => date('d M Y'),
                    'duration' => '1 Hari',
                    'timeRange' => null,
                    'reason' => 'Demam dan flu berat sesuai anjuran istirahat dokter.',
                    'document' => 'surat_dokter_rina.pdf',
                    'status' => 'Menunggu Persetujuan',
                    'statusBadge' => 'amber',
                    'submittedAt' => date('d M Y, 06:30 WITA'),
                    'substituteTeacher' => 'Pak Hendra, M.Pd'
                ],
                [
                    'id' => 'IZN-2026-003',
                    'type' => 'keluar_mengajar',
                    'typeLabel' => 'Izin Keluar / Tidak Mengajar',
                    'badgeColor' => 'blue',
                    'startDate' => date('d M Y', strtotime('-3 days')),
                    'endDate' => date('d M Y', strtotime('-3 days')),
                    'duration' => '2 Jam Pelajaran',
                    'timeRange' => '10:00 - 11:30 WITA',
                    'classAffected' => 'Kelas 8A (Jam ke 3-4)',
                    'reason' => 'Menghadiri undangan rapat MGMP Matematika tingkat Kota.',
                    'taskGiven' => 'Latihan soal mandiri Bab 4 Hal. 88 (dikumpulkan di meja guru)',
                    'document' => 'undangan_mgmp.pdf',
                    'status' => 'Disetujui',
                    'statusBadge' => 'emerald',
                    'submittedAt' => date('d M Y, 08:00 WITA', strtotime('-3 days')),
                    'substituteTeacher' => 'Bu Fitri, S.Pd (Guru Piket)'
                ],
                [
                    'id' => 'IZN-2026-002',
                    'type' => 'tidak_masuk',
                    'typeLabel' => 'Izin Dinas Luar',
                    'badgeColor' => 'purple',
                    'startDate' => date('d M Y', strtotime('-10 days')),
                    'endDate' => date('d M Y', strtotime('-9 days')),
                    'duration' => '2 Hari',
                    'timeRange' => null,
                    'reason' => 'Pelatihan Implementasi Kurikulum Merdeka di LPMP.',
                    'document' => 'surat_tugas_lpmp.pdf',
                    'status' => 'Disetujui',
                    'statusBadge' => 'emerald',
                    'submittedAt' => date('d M Y', strtotime('-12 days')),
                    'substituteTeacher' => 'Pak Ridwan, S.Kom'
                ]
            ]
        ];
    }

    /**
     * Get Teacher Leave Requests & Quotas (Pengajuan Cuti Guru)
     */
    public static function getTeacherLeaves(int $userId = 1): array
    {
        return [
            'quotas' => [
                'tahunan' => ['total' => 12, 'used' => 2, 'remaining' => 10],
                'sakit' => ['total' => 14, 'used' => 0, 'remaining' => 14],
                'alasan_penting' => ['total' => 5, 'used' => 0, 'remaining' => 5]
            ],
            'records' => [
                [
                    'id' => 'CT-2026-001',
                    'type' => 'Cuti Tahunan',
                    'startDate' => date('d M Y', strtotime('+5 days')),
                    'endDate' => date('d M Y', strtotime('+6 days')),
                    'daysCount' => 2,
                    'reason' => 'Menghadiri pernikahan adik kandung di luar kota.',
                    'emergencyContact' => '0812-4455-6677 (Suami)',
                    'substitute' => 'Pak Hendra, M.Pd & Bu Fitri, S.Pd',
                    'status' => 'Disetujui Kepala Sekolah',
                    'statusBadge' => 'emerald',
                    'submittedAt' => date('d M Y, 09:15 WITA', strtotime('-2 days')),
                    'timeline' => [
                        ['step' => 'Pengajuan Diajukan', 'time' => '18 Agu 2026, 09:15', 'done' => true],
                        ['step' => 'Verifikasi Wakasek SDM', 'time' => '18 Agu 2026, 14:00', 'done' => true],
                        ['step' => 'Persetujuan Kepala Sekolah', 'time' => '19 Agu 2026, 10:30', 'done' => true]
                    ]
                ],
                [
                    'id' => 'CT-2025-089',
                    'type' => 'Cuti Tahunan',
                    'startDate' => '22 Des 2025',
                    'endDate' => '24 Des 2025',
                    'daysCount' => 3,
                    'reason' => 'Libur akhir tahun bersama keluarga.',
                    'emergencyContact' => '0812-4455-6677',
                    'substitute' => 'Guru Piket Sekolah',
                    'status' => 'Selesai',
                    'statusBadge' => 'emerald',
                    'submittedAt' => '15 Des 2025',
                    'timeline' => [
                        ['step' => 'Pengajuan Diajukan', 'time' => '15 Des 2025', 'done' => true],
                        ['step' => 'Verifikasi Wakasek SDM', 'time' => '16 Des 2025', 'done' => true],
                        ['step' => 'Persetujuan Kepala Sekolah', 'time' => '17 Des 2025', 'done' => true]
                    ]
                ]
            ]
        ];
    }
}
