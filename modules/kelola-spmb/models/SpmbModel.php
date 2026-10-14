<?php
/**
 * SpmbModel - Model untuk Modul Kelola SPMB
 * Portal BIP
 */

class SpmbModel
{
    private static function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Format tanggal Y-m-d ke format Indonesia (contoh: 1 Juli 2027)
     */
    public static function formatTglIndo(?string $dateStr): string
    {
        if (empty($dateStr) || $dateStr === '0000-00-00') return '-';
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $time = strtotime($dateStr);
        if (!$time) return (string)$dateStr;
        return date('j', $time) . ' ' . ($bulan[(int)date('n', $time)] ?? date('F', $time)) . ' ' . date('Y', $time);
    }

    /**
     * Hitung umur calon siswa berdasarkan tanggal patokan (standar SPMB per 1 Juli tahun ajaran)
     * Contoh: lahir 2021-02-07, patokan 2027-07-01 => "6 Tahun 4 Bulan"
     */
    public static function hitungUmur(?string $tanggalLahir, ?string $tanggalPatokan = null): string
    {
        if (empty($tanggalLahir) || empty(trim($tanggalLahir))) return '';
        try {
            $d1 = new DateTime(trim($tanggalLahir));
            $patokanStr = !empty($tanggalPatokan) ? trim($tanggalPatokan) : '2027-07-01';
            $d2 = new DateTime($patokanStr);
            if ($d1 > $d2) {
                return 'Tanggal belum tiba';
            }
            $diff = $d2->diff($d1);
            $res = $diff->y . ' Tahun';
            if ($diff->m > 0) {
                $res .= ' ' . $diff->m . ' Bulan';
            }
            return $res;
        } catch (Throwable $e) {
            return '';
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. GROUP / TAHUN SPMB (PENGATURAN)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Ambil group SPMB yang sedang aktif
     */
    public static function getActiveGroup(): ?array
    {
        $group = self::db()->find("SELECT * FROM `spmb_group` WHERE `is_active` = 1 LIMIT 1");
        if (!$group) {
            $group = self::db()->find("SELECT * FROM `spmb_group` ORDER BY `id` DESC LIMIT 1");
        }
        return $group ?: null;
    }

    /**
     * Ambil semua daftar group SPMB
     */
    public static function getAllGroups(): array
    {
        return self::db()->findAll("
            SELECT g.*, 
                   COUNT(p.id) as total_pendaftar,
                   SUM(CASE WHEN p.jenis_pendaftaran = 'Siswa Baru' THEN 1 ELSE 0 END) as total_siswa_baru,
                   SUM(CASE WHEN p.jenis_pendaftaran = 'Pindahan' THEN 1 ELSE 0 END) as total_pindahan
            FROM `spmb_group` g
            LEFT JOIN `spmb_pendaftar` p ON p.group_id = g.id
            GROUP BY g.id
            ORDER BY g.is_active DESC, g.id DESC
        ");
    }

    /**
     * Ambil satu group berdasarkan ID
     */
    public static function getGroupById(int $id): ?array
    {
        return self::db()->find("SELECT * FROM `spmb_group` WHERE `id` = ?", [$id]);
    }

    /**
     * Tambah group SPMB baru
     */
    public static function createGroup(array $data): int
    {
        $isActive = !empty($data['is_active']) ? 1 : 0;
        if ($isActive) {
            self::db()->query("UPDATE `spmb_group` SET `is_active` = 0");
        }

        $sql = "INSERT INTO `spmb_group` (
            `nama_group`, `tahun_akademik_id`, `tahun_ajaran`, `gelombang_default`,
            `biaya_formulir`, `biaya_pindahan`, `kuota_paud`, `kuota_sd`, `kuota_smp`, `kuota_sma`,
            `tanggal_buka`, `tanggal_tutup`, `tanggal_patokan_umur`, `is_active`, `keterangan`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        self::db()->query($sql, [
            $data['nama_group'],
            !empty($data['tahun_akademik_id']) ? (int)$data['tahun_akademik_id'] : null,
            $data['tahun_ajaran'],
            $data['gelombang_default'] ?? 'Gelombang 1',
            (float)($data['biaya_formulir'] ?? 0),
            (float)($data['biaya_pindahan'] ?? 0),
            (int)($data['kuota_paud'] ?? 50),
            (int)($data['kuota_sd'] ?? 100),
            (int)($data['kuota_smp'] ?? 100),
            (int)($data['kuota_sma'] ?? 100),
            !empty($data['tanggal_buka']) ? $data['tanggal_buka'] : null,
            !empty($data['tanggal_tutup']) ? $data['tanggal_tutup'] : null,
            !empty($data['tanggal_patokan_umur']) ? $data['tanggal_patokan_umur'] : '2027-07-01',
            $isActive,
            $data['keterangan'] ?? null
        ]);

        return (int)self::db()->getConnection()->lastInsertId();
    }

    /**
     * Update group SPMB
     */
    public static function updateGroup(int $id, array $data): bool
    {
        $isActive = !empty($data['is_active']) ? 1 : 0;
        if ($isActive) {
            self::db()->query("UPDATE `spmb_group` SET `is_active` = 0 WHERE `id` != ?", [$id]);
        }

        $sql = "UPDATE `spmb_group` SET
            `nama_group` = ?,
            `tahun_akademik_id` = ?,
            `tahun_ajaran` = ?,
            `gelombang_default` = ?,
            `biaya_formulir` = ?,
            `biaya_pindahan` = ?,
            `kuota_paud` = ?,
            `kuota_sd` = ?,
            `kuota_smp` = ?,
            `kuota_sma` = ?,
            `tanggal_buka` = ?,
            `tanggal_tutup` = ?,
            `tanggal_patokan_umur` = ?,
            `is_active` = ?,
            `keterangan` = ?
            WHERE `id` = ?";

        self::db()->query($sql, [
            $data['nama_group'],
            !empty($data['tahun_akademik_id']) ? (int)$data['tahun_akademik_id'] : null,
            $data['tahun_ajaran'],
            $data['gelombang_default'] ?? 'Gelombang 1',
            (float)($data['biaya_formulir'] ?? 0),
            (float)($data['biaya_pindahan'] ?? 0),
            (int)($data['kuota_paud'] ?? 50),
            (int)($data['kuota_sd'] ?? 100),
            (int)($data['kuota_smp'] ?? 100),
            (int)($data['kuota_sma'] ?? 100),
            !empty($data['tanggal_buka']) ? $data['tanggal_buka'] : null,
            !empty($data['tanggal_tutup']) ? $data['tanggal_tutup'] : null,
            !empty($data['tanggal_patokan_umur']) ? $data['tanggal_patokan_umur'] : '2027-07-01',
            $isActive,
            $data['keterangan'] ?? null,
            $id
        ]);

        return true;
    }

    /**
     * Jadikan group tertentu sebagai group aktif
     */
    public static function setActiveGroup(int $id): bool
    {
        self::db()->query("UPDATE `spmb_group` SET `is_active` = 0");
        self::db()->query("UPDATE `spmb_group` SET `is_active` = 1 WHERE `id` = ?", [$id]);
        return true;
    }

    /**
     * Hapus group SPMB
     */
    public static function deleteGroup(int $id): bool
    {
        $count = (int)self::db()->find("SELECT COUNT(*) as cnt FROM `spmb_pendaftar` WHERE `group_id` = ?", [$id])['cnt'];
        if ($count > 0) {
            throw new Exception("Group ini memiliki $count data pendaftar dan tidak dapat dihapus.");
        }
        self::db()->query("DELETE FROM `spmb_group` WHERE `id` = ?", [$id]);
        return true;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. NOMOR OTOMATIS & PENDAFTAR SPMB
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Generate nomor pendaftaran otomatis unik
     * Contoh Siswa Baru: SD-001, SMP-001, SMA-001, PAUD-001
     * Contoh Pindahan:   PNDH-SD-001
     */
    public static function generateNomorPendaftaran(string $jenis = 'Siswa Baru', ?string $tahunAjaran = null, ?string $jenjang = 'PAUD'): string
    {
        $unit = strtoupper(trim($jenjang ?? 'PAUD'));
        if (empty($unit)) {
            $unit = 'PAUD';
        }

        if ($jenis === 'Pindahan') {
            $codePrefix = 'PNDH-' . $unit . '-';
        } elseif ($jenis === 'Daftar Tunggu') {
            $codePrefix = 'DT-' . $unit . '-';
        } else {
            // Format Unit-001 (misal SD-001, SMP-001, SMA-001, PAUD-001)
            $codePrefix = $unit . '-';
        }

        $lastRow = self::db()->find(
            "SELECT nomor_pendaftaran FROM `spmb_pendaftar` 
             WHERE nomor_pendaftaran LIKE ? 
             ORDER BY id DESC LIMIT 1",
            [$codePrefix . '%']
        );

        $seq = 1;
        if ($lastRow && !empty($lastRow['nomor_pendaftaran'])) {
            $part = substr($lastRow['nomor_pendaftaran'], strlen($codePrefix));
            if (is_numeric($part)) {
                $seq = (int)$part + 1;
            }
        }

        return $codePrefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil list pendaftar dengan filter fleksibel
     */
    public static function getPendaftarList(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['jenis_pendaftaran'])) {
            $conditions[] = "p.jenis_pendaftaran = ?";
            $params[] = $filters['jenis_pendaftaran'];
        }

        if (!empty($filters['group_id'])) {
            $conditions[] = "p.group_id = ?";
            $params[] = (int)$filters['group_id'];
        }

        if (!empty($filters['jenjang'])) {
            $conditions[] = "p.jenjang = ?";
            $params[] = strtoupper($filters['jenjang']);
        }

        if (!empty($filters['gelombang'])) {
            $conditions[] = "p.gelombang = ?";
            $params[] = $filters['gelombang'];
        }

        if (!empty($filters['status_kelulusan'])) {
            $conditions[] = "p.status_kelulusan = ?";
            $params[] = $filters['status_kelulusan'];
        }

        if (!empty($filters['kategori_pendaftar'])) {
            $conditions[] = "(p.kategori_pendaftar = ? OR p.jalur_pendaftaran = ?)";
            $params[] = $filters['kategori_pendaftar'];
            $params[] = $filters['kategori_pendaftar'];
        }

        if (!empty($filters['status_pembayaran'])) {
            $conditions[] = "p.status_pembayaran = ?";
            $params[] = $filters['status_pembayaran'];
        }

        if (!empty($filters['status_ttd_komitmen'])) {
            $conditions[] = "p.status_ttd_komitmen = ?";
            $params[] = $filters['status_ttd_komitmen'];
        }

        if (!empty($filters['biodata_status'])) {
            $conditions[] = "p.biodata_status = ?";
            $params[] = $filters['biodata_status'];
        }

        if (!empty($filters['status_berkas'])) {
            $conditions[] = "p.status_berkas = ?";
            $params[] = $filters['status_berkas'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $conditions[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ? OR p.nisn LIKE ? OR p.asal_sekolah LIKE ? OR p.nama_ayah LIKE ? OR p.no_hp LIKE ? OR p.nama_rekomendasi LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "
            SELECT p.*, g.nama_group, g.tahun_ajaran as group_tahun_ajaran, g.tanggal_patokan_umur
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_group` g ON g.id = p.group_id
            $whereClause
            ORDER BY p.id DESC
            LIMIT $limit OFFSET $offset
        ";

        return self::db()->findAll($sql, $params);
    }

    /**
     * Hitung total pendaftar sesuai filter
     */
    public static function countPendaftar(array $filters = []): int
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['jenis_pendaftaran'])) {
            $conditions[] = "p.jenis_pendaftaran = ?";
            $params[] = $filters['jenis_pendaftaran'];
        }

        if (!empty($filters['group_id'])) {
            $conditions[] = "p.group_id = ?";
            $params[] = (int)$filters['group_id'];
        }

        if (!empty($filters['jenjang'])) {
            $conditions[] = "p.jenjang = ?";
            $params[] = strtoupper($filters['jenjang']);
        }

        if (!empty($filters['gelombang'])) {
            $conditions[] = "p.gelombang = ?";
            $params[] = $filters['gelombang'];
        }

        if (!empty($filters['status_kelulusan'])) {
            $conditions[] = "p.status_kelulusan = ?";
            $params[] = $filters['status_kelulusan'];
        }

        if (!empty($filters['kategori_pendaftar'])) {
            $conditions[] = "(p.kategori_pendaftar = ? OR p.jalur_pendaftaran = ?)";
            $params[] = $filters['kategori_pendaftar'];
            $params[] = $filters['kategori_pendaftar'];
        }

        if (!empty($filters['status_pembayaran'])) {
            $conditions[] = "p.status_pembayaran = ?";
            $params[] = $filters['status_pembayaran'];
        }

        if (!empty($filters['status_ttd_komitmen'])) {
            $conditions[] = "p.status_ttd_komitmen = ?";
            $params[] = $filters['status_ttd_komitmen'];
        }

        if (!empty($filters['biodata_status'])) {
            $conditions[] = "p.biodata_status = ?";
            $params[] = $filters['biodata_status'];
        }

        if (!empty($filters['status_berkas'])) {
            $conditions[] = "p.status_berkas = ?";
            $params[] = $filters['status_berkas'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $conditions[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ? OR p.nisn LIKE ? OR p.asal_sekolah LIKE ? OR p.nama_ayah LIKE ? OR p.no_hp LIKE ? OR p.nama_rekomendasi LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $row = self::db()->find("SELECT COUNT(*) as total FROM `spmb_pendaftar` p $whereClause", $params);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Ambil 1 pendaftar berdasarkan ID
     */
    public static function getPendaftarById(int $id): ?array
    {
        $sql = "
            SELECT p.*, g.nama_group, g.tahun_ajaran as group_tahun_ajaran, g.biaya_formulir as group_biaya_formulir, g.biaya_pindahan as group_biaya_pindahan, g.tanggal_patokan_umur
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_group` g ON g.id = p.group_id
            WHERE p.id = ?
        ";
        return self::db()->find($sql, [$id]) ?: null;
    }

    /**
     * Tambah data pendaftar baru (Siswa Baru / Pindahan)
     */
    public static function createPendaftar(array $data): int
    {
        // Pastikan group_id valid
        if (empty($data['group_id'])) {
            $act = self::getActiveGroup();
            $data['group_id'] = $act['id'] ?? 1;
        }

        // Pastikan nomor_pendaftaran terisi dan unik
        if (empty($data['nomor_pendaftaran'])) {
            $group = self::getGroupById((int)$data['group_id']);
            $data['nomor_pendaftaran'] = self::generateNomorPendaftaran(
                $data['jenis_pendaftaran'] ?? 'Siswa Baru',
                $group['tahun_ajaran'] ?? null,
                $data['jenjang'] ?? 'PAUD'
            );
        } else {
            $exists = self::db()->find("SELECT id FROM `spmb_pendaftar` WHERE `nomor_pendaftaran` = ?", [$data['nomor_pendaftaran']]);
            if ($exists) {
                $group = self::getGroupById((int)$data['group_id']);
                $data['nomor_pendaftaran'] = self::generateNomorPendaftaran(
                    $data['jenis_pendaftaran'] ?? 'Siswa Baru',
                    $group['tahun_ajaran'] ?? null,
                    $data['jenjang'] ?? 'PAUD'
                );
            }
        }

        // Format biaya pendaftaran ke nominal angka numerik
        if (isset($data['biaya_pendaftaran']) && $data['biaya_pendaftaran'] !== '') {
            if (is_numeric($data['biaya_pendaftaran'])) {
                $data['biaya_pendaftaran'] = (float)$data['biaya_pendaftaran'];
            } else {
                $cleanedBiaya = preg_replace('/[^0-9]/', '', (string)$data['biaya_pendaftaran']);
                $data['biaya_pendaftaran'] = $cleanedBiaya !== '' ? (float)$cleanedBiaya : 0;
            }
        } else {
            if (($data['jenis_pendaftaran'] ?? '') === 'Pindahan') {
                $groupForFee = !empty($data['group_id']) ? self::getGroupById((int)$data['group_id']) : self::getActiveGroup();
                $data['biaya_pendaftaran'] = (float)($groupForFee['biaya_pindahan'] ?? 0);
            } else {
                $data['biaya_pendaftaran'] = 0;
            }
        }

        // Handle jenis & metode pembayaran
        $jenisBayar = $data['jenis_pembayaran'] ?? $data['metode_pembayaran'] ?? null;
        if ((float)$data['biaya_pendaftaran'] <= 0) {
            $data['status_pembayaran'] = 'Gratis';
            $data['metode_pembayaran'] = 'Gratis';
            $data['biaya_pendaftaran'] = 0;
        } else {
            $data['metode_pembayaran'] = (!empty($jenisBayar) && strcasecmp($jenisBayar, 'Gratis') !== 0) ? $jenisBayar : 'Tunai';
            if (empty($data['status_pembayaran']) || $data['status_pembayaran'] === 'Gratis') {
                $data['status_pembayaran'] = 'Lunas';
            }
        }

        // Hitung umur otomatis berdasarkan tanggal_patokan_umur group
        if (empty($data['umur']) && !empty($data['tanggal_lahir'])) {
            $groupObj = !empty($data['group_id']) ? self::getGroupById((int)$data['group_id']) : self::getActiveGroup();
            $patokanDate = $groupObj['tanggal_patokan_umur'] ?? '2027-07-01';
            $data['umur'] = self::hitungUmur($data['tanggal_lahir'], $patokanDate);
        }

        // Token akses biodata unik
        if (empty($data['biodata_token'])) {
            $data['biodata_token'] = bin2hex(random_bytes(24));
        }

        $katPendaftar = $data['kategori_pendaftar'] ?? $data['jalur_pendaftaran'] ?? 'Umum (Luar Bina Insan)';
        $namaRekomendasi = !empty($data['nama_rekomendasi']) ? trim($data['nama_rekomendasi']) : null;
        $statusKelulusan = $data['status_kelulusan'] ?? (($data['jenis_pendaftaran'] ?? '') === 'Daftar Tunggu' ? 'Cadangan' : 'Menunggu Seleksi');

        $sql = "INSERT INTO `spmb_pendaftar` (
            `group_id`, `jenis_pendaftaran`, `nomor_pendaftaran`, `tanggal_daftar`,
            `gelombang`, `jenjang`, `jalur_pendaftaran`, `kategori_pendaftar`, `nama_rekomendasi`, `nisn`, `nik`, `no_kk`,
            `nama_lengkap`, `nama_panggilan`, `jenis_kelamin`, `tempat_lahir`,
            `tanggal_lahir`, `umur`, `no_akta_lahir`, `agama`, `anak_ke`, `jumlah_saudara`,
            `alergi`, `tinggi_badan`, `berat_badan`, `lingkar_kepala`, `hobi`, `cita_cita`,
            `alamat`, `rt`, `rw`, `dusun`, `kelurahan`, `kecamatan`, `kota`, `provinsi`, `kode_pos`,
            `lintang`, `bujur`, `tempat_tinggal`, `moda_transportasi`,
            `no_hp`, `no_wa`, `email`, `foto`, `asal_sekolah`, `npsn_asal`, `alamat_sekolah_asal`, `file_screenshot_dapodik`,
            `kelas_tujuan`, `alasan_pindah`, `nomor_surat_pindah`,
            `nama_ayah`, `nik_ayah`, `tahun_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`,
            `penghasilan_ayah`, `kantor_ayah`, `jabatan_ayah`, `kebutuhan_khusus_ayah`, `no_hp_ayah`,
            `nama_ibu`, `nik_ibu`, `tahun_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`,
            `penghasilan_ibu`, `kantor_ibu`, `jabatan_ibu`, `kebutuhan_khusus_ibu`, `no_hp_ibu`,
            `nama_wali`, `pekerjaan_wali`, `no_hp_wali`,
            `biaya_pendaftaran`, `status_pembayaran`, `metode_pembayaran`, `bukti_bayar`,
            `berkas_kk`, `berkas_akta`, `berkas_ktp_ayah`, `berkas_ktp_ibu`,
            `biodata_token`, `biodata_status`, `biodata_step`, `persetujuan_data`,
            `status_berkas`, `status_kelulusan`, `catatan`, `created_by`
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?
        )";

        self::db()->query($sql, [
            (int)$data['group_id'],
            $data['jenis_pendaftaran'] ?? 'Siswa Baru',
            $data['nomor_pendaftaran'],
            $data['tanggal_daftar'] ?? date('Y-m-d'),
            $data['gelombang'] ?? 'Gelombang 1',
            $data['jenjang'],
            $data['jalur_pendaftaran'] ?? $katPendaftar,
            $katPendaftar,
            $namaRekomendasi,
            $data['nisn'] ?? null,
            $data['nik'] ?? null,
            $data['no_kk'] ?? null,
            $data['nama_lengkap'],
            $data['nama_panggilan'] ?? null,
            $data['jenis_kelamin'],
            $data['tempat_lahir'] ?? null,
            !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : null,
            $data['umur'] ?? null,
            $data['no_akta_lahir'] ?? null,
            $data['agama'] ?? 'Islam',
            !empty($data['anak_ke']) ? (int)$data['anak_ke'] : null,
            !empty($data['jumlah_saudara']) ? (int)$data['jumlah_saudara'] : null,
            $data['alergi'] ?? null,
            !empty($data['tinggi_badan']) ? (int)$data['tinggi_badan'] : null,
            !empty($data['berat_badan']) ? (int)$data['berat_badan'] : null,
            !empty($data['lingkar_kepala']) ? (int)$data['lingkar_kepala'] : null,
            $data['hobi'] ?? null,
            $data['cita_cita'] ?? null,
            $data['alamat'] ?? null,
            $data['rt'] ?? null,
            $data['rw'] ?? null,
            $data['dusun'] ?? null,
            $data['kelurahan'] ?? null,
            $data['kecamatan'] ?? null,
            $data['kota'] ?? 'Palu',
            $data['provinsi'] ?? 'Sulawesi Tengah',
            $data['kode_pos'] ?? null,
            $data['lintang'] ?? null,
            $data['bujur'] ?? null,
            $data['tempat_tinggal'] ?? null,
            $data['moda_transportasi'] ?? null,
            $data['no_hp'] ?? null,
            $data['no_wa'] ?? ($data['no_hp'] ?? null),
            $data['email'] ?? null,
            $data['foto'] ?? null,
            $data['asal_sekolah'] ?? null,
            $data['npsn_asal'] ?? null,
            $data['alamat_sekolah_asal'] ?? null,
            $data['file_screenshot_dapodik'] ?? null,
            $data['kelas_tujuan'] ?? null,
            $data['alasan_pindah'] ?? null,
            $data['nomor_surat_pindah'] ?? null,
            $data['nama_ayah'] ?? null,
            $data['nik_ayah'] ?? null,
            $data['tahun_lahir_ayah'] ?? null,
            $data['pendidikan_ayah'] ?? null,
            $data['pekerjaan_ayah'] ?? null,
            $data['penghasilan_ayah'] ?? null,
            $data['kantor_ayah'] ?? null,
            $data['jabatan_ayah'] ?? null,
            $data['kebutuhan_khusus_ayah'] ?? null,
            $data['no_hp_ayah'] ?? null,
            $data['nama_ibu'] ?? null,
            $data['nik_ibu'] ?? null,
            $data['tahun_lahir_ibu'] ?? null,
            $data['pendidikan_ibu'] ?? null,
            $data['pekerjaan_ibu'] ?? null,
            $data['penghasilan_ibu'] ?? null,
            $data['kantor_ibu'] ?? null,
            $data['jabatan_ibu'] ?? null,
            $data['kebutuhan_khusus_ibu'] ?? null,
            $data['no_hp_ibu'] ?? null,
            $data['nama_wali'] ?? null,
            $data['pekerjaan_wali'] ?? null,
            $data['no_hp_wali'] ?? null,
            (float)($data['biaya_pendaftaran'] ?? 0),
            $data['status_pembayaran'] ?? 'Gratis',
            $data['metode_pembayaran'] ?? 'Gratis',
            $data['bukti_bayar'] ?? null,
            $data['berkas_kk'] ?? null,
            $data['berkas_akta'] ?? null,
            $data['berkas_ktp_ayah'] ?? null,
            $data['berkas_ktp_ibu'] ?? null,
            $data['biodata_token'],
            $data['biodata_status'] ?? 'Belum Diisi',
            !empty($data['biodata_step']) ? (int)$data['biodata_step'] : 1,
            !empty($data['persetujuan_data']) ? 1 : 0,
            $data['status_berkas'] ?? 'Belum Lengkap',
            $statusKelulusan,
            $data['catatan'] ?? null,
            !empty($data['created_by']) ? (int)$data['created_by'] : null
        ]);

        return (int)self::db()->getConnection()->lastInsertId();
    }

    /**
     * Update data pendaftar
     */
    public static function updatePendaftar(int $id, array $data): bool
    {
        if (empty($data['umur']) && !empty($data['tanggal_lahir'])) {
            $groupObj = !empty($data['group_id']) ? self::getGroupById((int)$data['group_id']) : null;
            $patokanDate = $groupObj['tanggal_patokan_umur'] ?? '2027-07-01';
            $data['umur'] = self::hitungUmur($data['tanggal_lahir'], $patokanDate);
        }

        $katPendaftar = $data['kategori_pendaftar'] ?? $data['jalur_pendaftaran'] ?? 'Umum (Luar Bina Insan)';

        $biayaPend = 0.0;
        if (isset($data['biaya_pendaftaran']) && $data['biaya_pendaftaran'] !== '') {
            if (is_numeric($data['biaya_pendaftaran'])) {
                $biayaPend = (float)$data['biaya_pendaftaran'];
            } else {
                $c = preg_replace('/[^0-9]/', '', (string)$data['biaya_pendaftaran']);
                $biayaPend = $c !== '' ? (float)$c : 0.0;
            }
        }

        $fields = [
            'group_id' => (int)$data['group_id'],
            'tanggal_daftar' => $data['tanggal_daftar'] ?? date('Y-m-d'),
            'gelombang' => $data['gelombang'] ?? 'Gelombang 1',
            'jenjang' => $data['jenjang'],
            'jalur_pendaftaran' => $data['jalur_pendaftaran'] ?? $katPendaftar,
            'kategori_pendaftar' => $katPendaftar,
            'nisn' => $data['nisn'] ?? null,
            'nik' => $data['nik'] ?? null,
            'no_kk' => $data['no_kk'] ?? null,
            'nama_lengkap' => $data['nama_lengkap'],
            'nama_panggilan' => $data['nama_panggilan'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : null,
            'umur' => $data['umur'] ?? null,
            'no_akta_lahir' => $data['no_akta_lahir'] ?? null,
            'agama' => $data['agama'] ?? 'Islam',
            'anak_ke' => !empty($data['anak_ke']) ? (int)$data['anak_ke'] : null,
            'jumlah_saudara' => !empty($data['jumlah_saudara']) ? (int)$data['jumlah_saudara'] : null,
            'alergi' => $data['alergi'] ?? null,
            'tinggi_badan' => !empty($data['tinggi_badan']) ? (int)$data['tinggi_badan'] : null,
            'berat_badan' => !empty($data['berat_badan']) ? (int)$data['berat_badan'] : null,
            'lingkar_kepala' => !empty($data['lingkar_kepala']) ? (int)$data['lingkar_kepala'] : null,
            'hobi' => $data['hobi'] ?? null,
            'cita_cita' => $data['cita_cita'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'rt' => $data['rt'] ?? null,
            'rw' => $data['rw'] ?? null,
            'dusun' => $data['dusun'] ?? null,
            'kelurahan' => $data['kelurahan'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kota' => $data['kota'] ?? 'Palu',
            'provinsi' => $data['provinsi'] ?? 'Sulawesi Tengah',
            'kode_pos' => $data['kode_pos'] ?? null,
            'lintang' => $data['lintang'] ?? null,
            'bujur' => $data['bujur'] ?? null,
            'tempat_tinggal' => $data['tempat_tinggal'] ?? null,
            'moda_transportasi' => $data['moda_transportasi'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'no_wa' => $data['no_wa'] ?? ($data['no_hp'] ?? null),
            'email' => $data['email'] ?? null,
            'asal_sekolah' => $data['asal_sekolah'] ?? null,
            'npsn_asal' => $data['npsn_asal'] ?? null,
            'alamat_sekolah_asal' => $data['alamat_sekolah_asal'] ?? null,
            'kelas_tujuan' => $data['kelas_tujuan'] ?? null,
            'alasan_pindah' => $data['alasan_pindah'] ?? null,
            'nomor_surat_pindah' => $data['nomor_surat_pindah'] ?? null,
            'nama_ayah' => $data['nama_ayah'] ?? null,
            'nik_ayah' => $data['nik_ayah'] ?? null,
            'tahun_lahir_ayah' => $data['tahun_lahir_ayah'] ?? null,
            'pendidikan_ayah' => $data['pendidikan_ayah'] ?? null,
            'pekerjaan_ayah' => $data['pekerjaan_ayah'] ?? null,
            'penghasilan_ayah' => $data['penghasilan_ayah'] ?? null,
            'kantor_ayah' => $data['kantor_ayah'] ?? null,
            'jabatan_ayah' => $data['jabatan_ayah'] ?? null,
            'kebutuhan_khusus_ayah' => $data['kebutuhan_khusus_ayah'] ?? null,
            'no_hp_ayah' => $data['no_hp_ayah'] ?? null,
            'nama_ibu' => $data['nama_ibu'] ?? null,
            'nik_ibu' => $data['nik_ibu'] ?? null,
            'tahun_lahir_ibu' => $data['tahun_lahir_ibu'] ?? null,
            'pendidikan_ibu' => $data['pendidikan_ibu'] ?? null,
            'pekerjaan_ibu' => $data['pekerjaan_ibu'] ?? null,
            'penghasilan_ibu' => $data['penghasilan_ibu'] ?? null,
            'kantor_ibu' => $data['kantor_ibu'] ?? null,
            'jabatan_ibu' => $data['jabatan_ibu'] ?? null,
            'kebutuhan_khusus_ibu' => $data['kebutuhan_khusus_ibu'] ?? null,
            'no_hp_ibu' => $data['no_hp_ibu'] ?? null,
            'nama_wali' => $data['nama_wali'] ?? null,
            'pekerjaan_wali' => $data['pekerjaan_wali'] ?? null,
            'no_hp_wali' => $data['no_hp_wali'] ?? null,
            'biaya_pendaftaran' => $biayaPend,
            'status_pembayaran' => $data['status_pembayaran'] ?? ($biayaPend > 0 ? 'Lunas' : 'Gratis'),
            'status_berkas' => $data['status_berkas'] ?? 'Belum Lengkap',
            'status_kelulusan' => $data['status_kelulusan'] ?? 'Menunggu Seleksi',
            'catatan' => $data['catatan'] ?? null,
        ];

        if (isset($data['nama_rekomendasi'])) $fields['nama_rekomendasi'] = !empty($data['nama_rekomendasi']) ? trim($data['nama_rekomendasi']) : null;
        if (!empty($data['jenis_pendaftaran'])) $fields['jenis_pendaftaran'] = $data['jenis_pendaftaran'];
        if (isset($data['foto'])) $fields['foto'] = $data['foto'];
        if (isset($data['bukti_bayar'])) $fields['bukti_bayar'] = $data['bukti_bayar'];
        if (isset($data['file_screenshot_dapodik'])) $fields['file_screenshot_dapodik'] = $data['file_screenshot_dapodik'];
        if (isset($data['berkas_kk'])) $fields['berkas_kk'] = $data['berkas_kk'];
        if (isset($data['berkas_akta'])) $fields['berkas_akta'] = $data['berkas_akta'];
        if (isset($data['berkas_ktp_ayah'])) $fields['berkas_ktp_ayah'] = $data['berkas_ktp_ayah'];
        if (isset($data['berkas_ktp_ibu'])) $fields['berkas_ktp_ibu'] = $data['berkas_ktp_ibu'];
        if (isset($data['biodata_status'])) $fields['biodata_status'] = $data['biodata_status'];
        if (isset($data['biodata_step'])) $fields['biodata_step'] = (int)$data['biodata_step'];
        if (isset($data['persetujuan_data'])) $fields['persetujuan_data'] = !empty($data['persetujuan_data']) ? 1 : 0;

        $sets = [];
        $values = [];
        foreach ($fields as $col => $val) {
            $sets[] = "`$col` = ?";
            $values[] = $val;
        }
        $values[] = $id;

        $sql = "UPDATE `spmb_pendaftar` SET " . implode(", ", $sets) . " WHERE `id` = ?";
        self::db()->query($sql, $values);
        return true;
    }

    /**
     * Simpan Verifikasi & Pengembalian Berkas Calon Siswa
     */
    public static function saveVerifikasiBerkas(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        if (isset($data['status_berkas'])) {
            $fields[] = "`status_berkas` = ?";
            $params[] = $data['status_berkas'];
        }

        if (isset($data['is_data_sesuai'])) {
            $fields[] = "`is_data_sesuai` = ?";
            $params[] = (int)$data['is_data_sesuai'];
        }

        if (!empty($data['surat_kesanggupan'])) {
            $fields[] = "`surat_kesanggupan` = ?";
            $params[] = $data['surat_kesanggupan'];
        }

        if (isset($data['catatan_berkas'])) {
            $fields[] = "`catatan_berkas` = ?";
            $params[] = $data['catatan_berkas'];
        }

        if (!empty($data['petugas_verifikasi_berkas'])) {
            $fields[] = "`petugas_verifikasi_berkas` = ?";
            $params[] = $data['petugas_verifikasi_berkas'];
        }

        // Set tanggal kembali berkas otomatis jika status bukan 'Belum Dikembalikan'
        if (!empty($data['status_berkas']) && $data['status_berkas'] !== 'Belum Dikembalikan') {
            $fields[] = "`tanggal_kembali_berkas` = NOW()";
        } elseif (isset($data['status_berkas']) && $data['status_berkas'] === 'Belum Dikembalikan') {
            $fields[] = "`tanggal_kembali_berkas` = NULL";
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE `spmb_pendaftar` SET " . implode(", ", $fields) . " WHERE `id` = ?";
        self::db()->query($sql, $params);
        return true;
    }

    /**
     * Ambil data pendaftar berdasarkan token akses biodata
     */
    public static function getPendaftarByBiodataToken(string $token): ?array
    {
        if (empty(trim($token))) return null;
        $sql = "
            SELECT p.*, g.nama_group, g.tahun_ajaran as group_tahun_ajaran, g.tanggal_patokan_umur
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_group` g ON g.id = p.group_id
            WHERE p.biodata_token = ?
            LIMIT 1
        ";
        return self::db()->find($sql, [trim($token)]) ?: null;
    }

    /**
     * Update formulir biodata via token publik (Mendukung Simpan Draft & Submit Final)
     */
    public static function updateBiodataByToken(string $token, array $data, bool $isFinalSubmit = false): bool
    {
        $pendaftar = self::getPendaftarByBiodataToken($token);
        if (!$pendaftar) return false;

        $allowedCols = [
            // Tab 1: Data Diri
            'nama_lengkap', 'nama_panggilan', 'jenis_kelamin', 'nik', 'no_kk',
            'tempat_lahir', 'tanggal_lahir', 'umur', 'no_akta_lahir', 'agama',
            'anak_ke', 'jumlah_saudara', 'alergi', 'tinggi_badan', 'berat_badan',
            'lingkar_kepala', 'hobi', 'cita_cita',
            // Tab 2: Sekolah Sebelumnya
            'asal_sekolah', 'alamat_sekolah_asal', 'npsn_asal', 'nisn',
            // Tab 3: Tempat Tinggal
            'alamat', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 'kota',
            'provinsi', 'kode_pos', 'lintang', 'bujur', 'tempat_tinggal', 'moda_transportasi',
            // Tab 4: Orang Tua
            'nama_ibu', 'nik_ibu', 'tahun_lahir_ibu', 'pendidikan_ibu', 'pekerjaan_ibu',
            'penghasilan_ibu', 'kantor_ibu', 'jabatan_ibu', 'kebutuhan_khusus_ibu', 'no_hp_ibu',
            'nama_ayah', 'nik_ayah', 'tahun_lahir_ayah', 'pendidikan_ayah', 'pekerjaan_ayah',
            'penghasilan_ayah', 'kantor_ayah', 'jabatan_ayah', 'kebutuhan_khusus_ayah', 'no_hp_ayah',
            'email', 'no_hp', 'no_wa',
            // Files jika ada
            'file_screenshot_dapodik', 'berkas_kk', 'berkas_akta', 'berkas_ktp_ayah', 'berkas_ktp_ibu', 'foto'
        ];

        $sets = [];
        $values = [];

        foreach ($allowedCols as $col) {
            if (array_key_exists($col, $data)) {
                $val = $data[$col];
                if (in_array($col, ['tinggi_badan', 'berat_badan', 'lingkar_kepala', 'anak_ke', 'jumlah_saudara'])) {
                    $val = ($val !== '' && $val !== null) ? (int)$val : null;
                }
                $sets[] = "`$col` = ?";
                $values[] = $val;
            }
        }

        // Hitung umur otomatis jika tanggal lahir ada berdasarkan patokan group
        if (!empty($data['tanggal_lahir'])) {
            $patokanDate = !empty($pendaftar['tanggal_patokan_umur']) ? $pendaftar['tanggal_patokan_umur'] : '2027-07-01';
            $calculatedUmur = self::hitungUmur($data['tanggal_lahir'], $patokanDate);
            $sets[] = "`umur` = ?";
            $values[] = $calculatedUmur;
        }

        // Update step terakhir
        if (isset($data['biodata_step'])) {
            $sets[] = "`biodata_step` = ?";
            $values[] = max(1, min(5, (int)$data['biodata_step']));
        }

        // Status dan Persetujuan
        if ($isFinalSubmit) {
            $sets[] = "`biodata_status` = 'Lengkap'";
            $sets[] = "`persetujuan_data` = 1";
            $sets[] = "`biodata_submitted_at` = NOW()";
            $sets[] = "`status_berkas` = 'Lengkap'";
        } else {
            if ($pendaftar['biodata_status'] !== 'Lengkap') {
                $sets[] = "`biodata_status` = 'Draft'";
            }
            if (isset($data['persetujuan_data'])) {
                $sets[] = "`persetujuan_data` = ?";
                $values[] = !empty($data['persetujuan_data']) ? 1 : 0;
            }
        }

        $sets[] = "`updated_at` = NOW()";

        if (empty($sets)) return true;

        $values[] = $pendaftar['id'];
        $sql = "UPDATE `spmb_pendaftar` SET " . implode(", ", $sets) . " WHERE `id` = ?";
        self::db()->query($sql, $values);
        return true;
    }

    /**
     * Ambil opsi-opsi master dropdown SPMB dari tabel settings
     */
    public static function getSpmbOptions(): array
    {
        $db = self::db();
        $keys = [
            'spmb_opt_alergi',
            'spmb_opt_tempat_tinggal',
            'spmb_opt_transportasi',
            'spmb_opt_pendidikan',
            'spmb_opt_pekerjaan',
            'spmb_opt_penghasilan',
            'spmb_opt_kebutuhan_khusus',
            'spmb_template_dapodik',
            'spmb_wa_notif_enabled',
            'spmb_wa_template_biodata'
        ];

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $rows = $db->findAll("SELECT setting_key, setting_value FROM `settings` WHERE `setting_key` IN ($placeholders)", $keys);
        
        $raw = [];
        foreach ($rows as $r) {
            $raw[$r['setting_key']] = $r['setting_value'];
        }

        $parseList = function(?string $text, array $fallback) {
            if (empty(trim($text ?? ''))) return $fallback;
            $lines = preg_split('/\r\n|\r|\n|,/', $text);
            $clean = [];
            foreach ($lines as $line) {
                $t = trim($line);
                if (!empty($t) && !in_array($t, $clean)) $clean[] = $t;
            }
            return !empty($clean) ? $clean : $fallback;
        };

        return [
            'alergi' => $parseList($raw['spmb_opt_alergi'] ?? null, [
                'Tidak Ada',
                'Alergi Makanan / Seafood / Kacang',
                'Alergi Susu Sapi / Laktosa',
                'Alergi Debu / Dingin',
                'Alergi Obat Tertentu',
                'Lainnya'
            ]),
            'tempat_tinggal' => $parseList($raw['spmb_opt_tempat_tinggal'] ?? null, [
                'Bersama Orang Tua',
                'Wali / Keluarga',
                'Asrama / Pondok',
                'Kost',
                'Lainnya'
            ]),
            'transportasi' => $parseList($raw['spmb_opt_transportasi'] ?? null, [
                'Jalan Kaki',
                'Sepeda Motor',
                'Mobil Pribadi',
                'Antar Jemput Sekolah',
                'Angkutan Umum',
                'Ojek Online',
                'Lainnya'
            ]),
            'pendidikan' => $parseList($raw['spmb_opt_pendidikan'] ?? null, [
                'SD / Sederajat',
                'SMP / Sederajat',
                'SMA / SMK / Sederajat',
                'D1 / D2 / D3',
                'D4 / S1 (Sarjana)',
                'S2 (Magister)',
                'S3 (Doktor)',
                'Tidak Sekolah'
            ]),
            'pekerjaan' => $parseList($raw['spmb_opt_pekerjaan'] ?? null, [
                'PNS / ASN',
                'TNI / Polri',
                'Pegawai BUMN',
                'Karyawan Swasta',
                'Wiraswasta / Pengusaha',
                'Pedagang',
                'Petani / Peternak',
                'Nelayan',
                'Dokter / Tenaga Kesehatan',
                'Guru / Dosen',
                'Ibu Rumah Tangga (IRT)',
                'Buruh',
                'Lainnya'
            ]),
            'penghasilan' => $parseList($raw['spmb_opt_penghasilan'] ?? null, [
                'Kurang dari Rp 1.000.000',
                'Rp 1.000.000 - Rp 3.000.000',
                'Rp 3.000.000 - Rp 5.000.000',
                'Rp 5.000.000 - Rp 10.000.000',
                'Lebih dari Rp 10.000.000',
                'Tidak Berpenghasilan'
            ]),
            'kebutuhan_khusus' => $parseList($raw['spmb_opt_kebutuhan_khusus'] ?? null, [
                'Tidak Ada',
                'Tunanetra',
                'Tunarungu',
                'Tunawicara',
                'Tunadaksa',
                'Tunagrahita',
                'Autis',
                'Hiperaktif (ADHD)',
                'Lainnya'
            ]),
            'template_dapodik' => $raw['spmb_template_dapodik'] ?? '',
            'wa_notif_enabled' => ($raw['spmb_wa_notif_enabled'] ?? '1') === '1',
            'batas_pengisian' => !empty($raw['spmb_batas_pengisian_biodata']) ? str_replace('T', ' ', $raw['spmb_batas_pengisian_biodata']) : date('Y-12-31 23:59:59'),
            'wa_template_biodata' => $raw['spmb_wa_template_biodata'] ?? "Assalamu'alaikum Wr. Wb. Bpk/Ibu Orang Tua/Wali dari ananda {nama_siswa}.\n\nTerima kasih telah melakukan pendaftaran di Portal SPMB SIT Bina Insan Palu (No. Registrasi: {nomor_pendaftaran}).\n\nUntuk kelengkapan administrasi dan sinkronisasi data Dapodik/EMIS, silakan melengkapi Formulir Biodata Lengkap melalui tautan resmi berikut:\n👉 {link_biodata}\n\nCatatan:\n- Anda dapat menyimpan sementara (Simpan Draft) dan melanjutkannya kapan saja.\n- Siapkan foto KK, Akta Kelahiran, dan KTP Orang Tua (maks. 500 KB).\n\nTerima kasih.\nPanitia SPMB Yayasan Bina Insan Palu"
        ];
    }

    /**
     * Kirim Notifikasi WhatsApp Link Pengisian Biodata ke Orang Tua / Pendaftar
     */
    public static function sendWaBiodataNotification(int $pendaftarId): array
    {
        $pendaftar = self::getPendaftarById($pendaftarId);
        if (!$pendaftar) {
            return ['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'];
        }

        // Pastikan token biodata ada
        if (empty($pendaftar['biodata_token'])) {
            $token = bin2hex(random_bytes(24));
            self::db()->query("UPDATE `spmb_pendaftar` SET `biodata_token` = ? WHERE `id` = ?", [$token, $pendaftarId]);
            $pendaftar['biodata_token'] = $token;
        }

        // Cari nomor tujuan pengiriman (Prioritas: no_wa -> no_hp -> no_hp_ayah -> no_hp_ibu)
        $targetPhone = !empty($pendaftar['no_wa']) ? $pendaftar['no_wa'] :
                      (!empty($pendaftar['no_hp']) ? $pendaftar['no_hp'] :
                      (!empty($pendaftar['no_hp_ayah']) ? $pendaftar['no_hp_ayah'] :
                      (!empty($pendaftar['no_hp_ibu']) ? $pendaftar['no_hp_ibu'] : '')));

        if (empty($targetPhone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp atau No HP pendaftar / orang tua belum diisi.'
            ];
        }

        $opts = self::getSpmbOptions();
        $template = $opts['wa_template_biodata'];
        $linkBiodata = url('spmb/biodata/' . $pendaftar['biodata_token']);

        // Ganti placeholder
        $replacements = [
            '{nama_siswa}'        => $pendaftar['nama_lengkap'],
            '{nomor_pendaftaran}' => $pendaftar['nomor_pendaftaran'],
            '{no_daftar}'         => $pendaftar['nomor_pendaftaran'],
            '{jenjang}'           => $pendaftar['jenjang'],
            '{link_biodata}'      => $linkBiodata,
            '{jalur}'             => $pendaftar['jalur_pendaftaran'] ?? 'Reguler'
        ];

        $pesan = str_replace(array_keys($replacements), array_values($replacements), $template);

        // Kirim via server WA Gateway
        return WaGateway::send($targetPhone, $pesan, [
            'module'         => 'kelola-spmb',
            'include_footer' => true
        ]);
    }

    /**
     * Update cepat status kelulusan / verifikasi
     */
    public static function updateStatusPendaftar(int $id, string $statusKelulusan, ?string $catatan = null): bool
    {
        if ($catatan !== null) {
            self::db()->query(
                "UPDATE `spmb_pendaftar` SET `status_kelulusan` = ?, `catatan` = ? WHERE `id` = ?",
                [$statusKelulusan, $catatan, $id]
            );
        } else {
            self::db()->query(
                "UPDATE `spmb_pendaftar` SET `status_kelulusan` = ? WHERE `id` = ?",
                [$statusKelulusan, $id]
            );
        }
        return true;
    }

    /**
     * Hapus pendaftar
     */
    public static function deletePendaftar(int $id): bool
    {
        self::db()->query("DELETE FROM `spmb_pendaftar` WHERE `id` = ?", [$id]);
        return true;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 3. STATISTIK DASHBOARD
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Hitung metrik dan statistik untuk dashboard SPMB
     */
    public static function getDashboardStats(?int $groupId = null): array
    {
        $activeGroup = $groupId ? self::getGroupById($groupId) : self::getActiveGroup();
        $targetGroupId = $activeGroup['id'] ?? null;

        $whereGroup = $targetGroupId ? "WHERE group_id = $targetGroupId" : "";
        $whereGroupP = $targetGroupId ? "WHERE p.group_id = $targetGroupId" : "";

        // Total Counts
        $counts = self::db()->find("
            SELECT 
                COUNT(*) as total_pendaftar,
                SUM(CASE WHEN jenis_pendaftaran = 'Siswa Baru' THEN 1 ELSE 0 END) as total_siswa_baru,
                SUM(CASE WHEN jenis_pendaftaran = 'Pindahan' THEN 1 ELSE 0 END) as total_pindahan,
                SUM(CASE WHEN jenis_pendaftaran = 'Daftar Tunggu' THEN 1 ELSE 0 END) as total_daftar_tunggu,
                SUM(CASE WHEN status_kelulusan = 'Lulus / Diterima' THEN 1 ELSE 0 END) as total_lulus,
                SUM(CASE WHEN status_kelulusan = 'Menunggu Seleksi' THEN 1 ELSE 0 END) as total_menunggu,
                SUM(CASE WHEN status_kelulusan = 'Lolos Berkas' THEN 1 ELSE 0 END) as total_lolos_berkas,
                SUM(CASE WHEN status_kelulusan = 'Dibatalkan' THEN 1 ELSE 0 END) as total_dibatalkan,
                SUM(CASE WHEN (kategori_pendaftar LIKE '%Keluarga%' OR kategori_pendaftar LIKE '%Adik%' OR kategori_pendaftar LIKE '%Saudara%') THEN 1 ELSE 0 END) as total_keluarga,
                SUM(CASE WHEN ((kategori_pendaftar NOT LIKE '%Keluarga%' AND kategori_pendaftar NOT LIKE '%Adik%' AND kategori_pendaftar NOT LIKE '%Saudara%') OR kategori_pendaftar IS NULL OR kategori_pendaftar = '') THEN 1 ELSE 0 END) as total_umum,
                SUM(CASE WHEN biodata_status = 'Lengkap' THEN 1 ELSE 0 END) as total_biodata_lengkap,
                SUM(CASE WHEN status_pembayaran = 'Lunas' THEN 1 ELSE 0 END) as total_lunas,
                SUM(CASE WHEN status_pembayaran = 'Belum Lunas' THEN 1 ELSE 0 END) as total_belum_lunas
            FROM `spmb_pendaftar`
            $whereGroup
        ") ?: [];

        // Breakdown per Jenjang
        $jenjangRaw = self::db()->findAll("
            SELECT jenjang, COUNT(*) as jumlah
            FROM `spmb_pendaftar`
            $whereGroup
            GROUP BY jenjang
        ");
        $jenjangStats = ['PAUD' => 0, 'SD' => 0, 'SMP' => 0, 'SMA' => 0];
        foreach ($jenjangRaw as $r) {
            $jenjangStats[$r['jenjang']] = (int)$r['jumlah'];
        }

        // Breakdown per Kategori / Jalur
        $jalurStats = self::db()->findAll("
            SELECT COALESCE(NULLIF(kategori_pendaftar, ''), jalur_pendaftaran, 'Umum') as jalur_pendaftaran, COUNT(*) as jumlah
            FROM `spmb_pendaftar`
            $whereGroup
            GROUP BY COALESCE(NULLIF(kategori_pendaftar, ''), jalur_pendaftaran, 'Umum')
            ORDER BY jumlah DESC
        ");

        // Pendaftar Terbaru (Recent 7)
        $recentPendaftar = self::db()->findAll("
            SELECT p.*, g.nama_group
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_group` g ON g.id = p.group_id
            $whereGroupP
            ORDER BY p.id DESC
            LIMIT 7
        ");

        return [
            'activeGroup'       => $activeGroup,
            'totalPendaftar'    => (int)($counts['total_pendaftar'] ?? 0),
            'totalSiswaBaru'    => (int)($counts['total_siswa_baru'] ?? 0),
            'totalPindahan'     => (int)($counts['total_pindahan'] ?? 0),
            'totalDaftarTunggu' => (int)($counts['total_daftar_tunggu'] ?? 0),
            'totalLulus'        => (int)($counts['total_lulus'] ?? 0),
            'totalMenunggu'     => (int)($counts['total_menunggu'] ?? 0),
            'totalLolosBerkas'  => (int)($counts['total_lolos_berkas'] ?? 0),
            'totalDibatalkan'   => (int)($counts['total_dibatalkan'] ?? 0),
            'totalKeluarga'     => (int)($counts['total_keluarga'] ?? 0),
            'totalUmum'         => (int)($counts['total_umum'] ?? 0),
            'totalBiodataLengkap' => (int)($counts['total_biodata_lengkap'] ?? 0),
            'totalLunas'        => (int)($counts['total_lunas'] ?? 0),
            'totalBelumLunas'   => (int)($counts['total_belum_lunas'] ?? 0),
            'jenjangStats'      => $jenjangStats,
            'jalurStats'        => $jalurStats,
            'recentPendaftar'   => $recentPendaftar
        ];
    }

    /**
     * Ambil nilai pengaturan SPMB dari tabel settings
     */
    public static function getSetting(string $key, $default = null)
    {
        try {
            $row = self::db()->find("SELECT setting_value FROM `settings` WHERE `setting_key` = ?", [$key]);
            return ($row && $row['setting_value'] !== null) ? $row['setting_value'] : $default;
        } catch (Throwable $e) {
            return $default;
        }
    }

    /**
     * Simpan / Perbarui nilai pengaturan SPMB ke tabel settings
     */
    public static function setSetting(string $key, $value): void
    {
        $row = self::db()->find("SELECT id FROM `settings` WHERE `setting_key` = ?", [$key]);
        if ($row) {
            self::db()->query("UPDATE `settings` SET `setting_value` = ? WHERE `setting_key` = ?", [$value, $key]);
        } else {
            self::db()->query(
                "INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES (?, ?, 'string', 'spmb')",
                [$key, $value]
            );
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 8. SURAT PERNYATAAN KOMITMEN ORANG TUA & TTD DIGITAL TERENKRIPSI
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Ambil template surat pernyataan berdasarkan jenjang (PAUD, SD, SMP, SMA)
     */
    public static function getSuratTemplate(string $jenjang): ?array
    {
        $jenjang = strtoupper(trim($jenjang));
        return self::db()->find("SELECT * FROM `spmb_surat_template` WHERE `jenjang` = ?", [$jenjang]);
    }

    /**
     * Ambil semua template surat pernyataan
     */
    public static function getAllSuratTemplates(): array
    {
        $rows = self::db()->findAll("SELECT * FROM `spmb_surat_template` ORDER BY FIELD(jenjang, 'PAUD', 'SD', 'SMP', 'SMA')");
        $result = [];
        foreach ($rows as $r) {
            $result[$r['jenjang']] = $r;
        }
        return $result;
    }

    /**
     * Simpan / Perbarui template surat komitmen per unit
     */
    public static function saveSuratTemplate(string $jenjang, array $data): bool
    {
        $jenjang = strtoupper(trim($jenjang));
        $exists = self::getSuratTemplate($jenjang);
        
        if ($exists) {
            $sql = "UPDATE `spmb_surat_template` SET 
                `judul_surat` = ?,
                `nama_sekolah` = ?,
                `isi_template` = ?,
                `catatan_bawah` = ?,
                `updated_at` = NOW()
                WHERE `jenjang` = ?";
            self::db()->query($sql, [
                $data['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID',
                $data['nama_sekolah'] ?? ($jenjang . ' Islam Terpadu Bina Insan Palu'),
                $data['isi_template'] ?? '',
                $data['catatan_bawah'] ?? null,
                $jenjang
            ]);
        } else {
            $sql = "INSERT INTO `spmb_surat_template`
                (`jenjang`, `judul_surat`, `nama_sekolah`, `kop_gambar`, `isi_template`, `catatan_bawah`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            self::db()->query($sql, [
                $jenjang,
                $data['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID',
                $data['nama_sekolah'] ?? ($jenjang . ' Islam Terpadu Bina Insan Palu'),
                $data['kop_gambar'] ?? null,
                $data['isi_template'] ?? '',
                $data['catatan_bawah'] ?? null
            ]);
        }
        return true;
    }

    /**
     * Perbarui gambar kop surat banner untuk unit tertentu
     */
    public static function updateKopTemplate(string $jenjang, ?string $kopGambar): bool
    {
        $jenjang = strtoupper(trim($jenjang));
        self::db()->query("UPDATE `spmb_surat_template` SET `kop_gambar` = ?, `updated_at` = NOW() WHERE `jenjang` = ?", [$kopGambar, $jenjang]);
        return true;
    }

    /**
     * Parse & Render Surat Komitmen lengkap dengan data pendaftar (Variable Replacement)
     */
    public static function renderSuratKomitmen(array $pendaftar, ?array $template = null): array
    {
        $jenjang = strtoupper(trim($pendaftar['jenjang'] ?? 'SD'));
        if (!$template) {
            $template = self::getSuratTemplate($jenjang);
        }

        $namaSekolah = $template['nama_sekolah'] ?? ($jenjang . ' Islam Terpadu Bina Insan Palu');
        $judulSurat  = $template['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID';
        $isiRaw      = $template['isi_template'] ?? '';
        $catatan     = $template['catatan_bawah'] ?? '';
        $kopGambar   = !empty($template['kop_gambar']) ? $template['kop_gambar'] : self::getSetting('spmb_kop_gambar');

        // Format tanggal hari ini / tanggal pendaftaran
        $tglDaftar = !empty($pendaftar['tanggal_daftar']) ? self::formatTglIndo($pendaftar['tanggal_daftar']) : date('d/m/Y');
        $tglHariIni = self::formatTglIndo(date('Y-m-d'));
        $tahunAjaran = $pendaftar['group_tahun_ajaran'] ?? ($pendaftar['tahun_ajaran'] ?? '2026/2027');

        // Daftar variabel pengganti
        $replacements = [
            '{nama_ayah}'          => htmlspecialchars($pendaftar['nama_ayah'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{nama_ibu}'           => htmlspecialchars($pendaftar['nama_ibu'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{nama_wali}'          => htmlspecialchars(!empty($pendaftar['nama_wali']) ? $pendaftar['nama_wali'] : '-', ENT_QUOTES, 'UTF-8'),
            '{nama_anak}'          => htmlspecialchars($pendaftar['nama_lengkap'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{nama_siswa}'         => htmlspecialchars($pendaftar['nama_lengkap'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{nama_lengkap}'       => htmlspecialchars($pendaftar['nama_lengkap'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{nisn}'               => htmlspecialchars($pendaftar['nisn'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{nik}'                => htmlspecialchars($pendaftar['nik'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{unit}'               => htmlspecialchars($jenjang, ENT_QUOTES, 'UTF-8'),
            '{jenjang}'            => htmlspecialchars($jenjang, ENT_QUOTES, 'UTF-8'),
            '{nama_sekolah}'       => htmlspecialchars($namaSekolah, ENT_QUOTES, 'UTF-8'),
            '{nomor_pendaftaran}'  => htmlspecialchars($pendaftar['nomor_pendaftaran'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{tanggal_daftar}'     => htmlspecialchars($tglDaftar, ENT_QUOTES, 'UTF-8'),
            '{tanggal_hari_ini}'   => htmlspecialchars($tglHariIni, ENT_QUOTES, 'UTF-8'),
            '{tanggal}'            => htmlspecialchars($tglHariIni, ENT_QUOTES, 'UTF-8'),
            '{alamat}'             => htmlspecialchars($pendaftar['alamat'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{asal_sekolah}'       => htmlspecialchars($pendaftar['asal_sekolah'] ?? '-', ENT_QUOTES, 'UTF-8'),
            '{tahun_ajaran}'       => htmlspecialchars($tahunAjaran, ENT_QUOTES, 'UTF-8'),
        ];

        // Lakukan replacement
        $isiRendered = str_replace(array_keys($replacements), array_values($replacements), $isiRaw);

        return [
            'jenjang'        => $jenjang,
            'judul_surat'    => $judulSurat,
            'nama_sekolah'   => $namaSekolah,
            'kop_gambar'     => $kopGambar,
            'isi_rendered'   => $isiRendered,
            'catatan_bawah'  => $catatan,
            'tanggal_teks'   => $tglHariIni,
            'replacements'   => $replacements
        ];
    }

    /**
     * Simpan Tanda Tangan Komitmen Digital Terenkripsi
     */
    public static function saveKomitmenSignature(int $pendaftarId, array $data): bool
    {
        $pendaftar = self::getPendaftarById($pendaftarId);
        if (!$pendaftar) {
            return false;
        }

        $encryptedAyah = null;
        $encryptedIbu = null;

        $rawTtdAyah = !empty($data['ttd_ayah']) ? $data['ttd_ayah'] : (!empty($data['ttd_ayah_data']) ? $data['ttd_ayah_data'] : null);
        $rawTtdIbu  = !empty($data['ttd_ibu']) ? $data['ttd_ibu'] : (!empty($data['ttd_ibu_data']) ? $data['ttd_ibu_data'] : null);

        $namaAyah = !empty($data['ttd_nama_ayah']) ? trim($data['ttd_nama_ayah']) : (!empty($data['nama_ayah']) ? trim($data['nama_ayah']) : ($pendaftar['nama_ayah'] ?? null));
        $namaIbu  = !empty($data['ttd_nama_ibu']) ? trim($data['ttd_nama_ibu']) : (!empty($data['nama_ibu']) ? trim($data['nama_ibu']) : ($pendaftar['nama_ibu'] ?? null));

        if (!empty($rawTtdAyah)) {
            $encryptedAyah = SignatureCrypto::encrypt($rawTtdAyah, [
                'role'              => 'Ayah',
                'name'              => $namaAyah ?? 'Ayah',
                'pendaftar_id'      => $pendaftarId,
                'nomor_pendaftaran' => $pendaftar['nomor_pendaftaran'] ?? ''
            ]);
        }

        if (!empty($rawTtdIbu)) {
            $encryptedIbu = SignatureCrypto::encrypt($rawTtdIbu, [
                'role'              => 'Ibu',
                'name'              => $namaIbu ?? 'Ibu',
                'pendaftar_id'      => $pendaftarId,
                'nomor_pendaftaran' => $pendaftar['nomor_pendaftaran'] ?? ''
            ]);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $catatan = !empty($data['catatan']) ? trim($data['catatan']) : null;

        $sql = "UPDATE `spmb_pendaftar` SET
            `status_ttd_komitmen` = 'Sudah TTD',
            `ttd_ayah` = ?,
            `ttd_ibu` = ?,
            `ttd_nama_ayah` = ?,
            `ttd_nama_ibu` = ?,
            `ttd_submitted_at` = NOW(),
            `ttd_ip_address` = ?,
            `ttd_user_agent` = ?,
            `ttd_catatan` = ?
            WHERE `id` = ?";

        self::db()->query($sql, [
            $encryptedAyah,
            $encryptedIbu,
            $namaAyah,
            $namaIbu,
            $ip,
            $ua,
            $catatan,
            $pendaftarId
        ]);

        return true;
    }

    /**
     * Dapatkan tanda tangan digital terdekripsi dan terverifikasi untuk pendaftar
     * Dapat menerima array $pendaftar lengkap atau single string payload terenkripsi
     */
    public static function getDecryptedSignature($input): ?array
    {
        if (empty($input)) {
            return null;
        }

        // Jika string tunggal terenkripsi
        if (is_string($input)) {
            return SignatureCrypto::decrypt($input);
        }

        // Jika array data pendaftar
        if (is_array($input)) {
            $ayahData = !empty($input['ttd_ayah']) ? SignatureCrypto::decrypt($input['ttd_ayah']) : null;
            $ibuData = !empty($input['ttd_ibu']) ? SignatureCrypto::decrypt($input['ttd_ibu']) : null;

            return [
                'is_signed'    => ($input['status_ttd_komitmen'] ?? '') === 'Sudah TTD',
                'signed_at'    => $input['ttd_submitted_at'] ?? null,
                'ip_address'   => $input['ttd_ip_address'] ?? null,
                'user_agent'   => $input['ttd_user_agent'] ?? null,
                'catatan'      => $input['ttd_catatan'] ?? null,
                'ayah'         => $ayahData,
                'ibu'          => $ibuData,
                'nama_ayah'    => $input['ttd_nama_ayah'] ?: ($input['nama_ayah'] ?: 'Ayah'),
                'nama_ibu'     => $input['ttd_nama_ibu'] ?: ($input['nama_ibu'] ?: 'Ibu'),
            ];
        }

        return null;
    }

    /**
     * Reset status TTD komitmen pendaftar
     */
    public static function resetKomitmenSignature(int $pendaftarId): bool
    {
        self::db()->query("
            UPDATE `spmb_pendaftar` SET
                `status_ttd_komitmen` = 'Belum TTD',
                `ttd_ayah` = NULL,
                `ttd_ibu` = NULL,
                `ttd_submitted_at` = NULL,
                `ttd_ip_address` = NULL,
                `ttd_user_agent` = NULL,
                `ttd_catatan` = NULL
            WHERE `id` = ?
        ", [$pendaftarId]);
        return true;
    }
}

