<?php
/**
 * SpmbModel - Model untuk Modul Kelola SPMB
 * Portal BIP
 */

class SpmbModel
{
    public static function db(): Database
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
     * Update massal status kelulusan untuk banyak ID pendaftar
     */
    public static function bulkUpdateStatus(array $ids, string $statusKelulusan, ?string $catatan = null): int
    {
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = [$statusKelulusan];
        if ($catatan !== null) {
            $params[] = $catatan;
            $sql = "UPDATE `spmb_pendaftar` SET `status_kelulusan` = ?, `catatan` = ? WHERE `id` IN ($placeholders)";
        } else {
            $sql = "UPDATE `spmb_pendaftar` SET `status_kelulusan` = ? WHERE `id` IN ($placeholders)";
        }
        $params = array_merge($params, $ids);
        return self::db()->query($sql, $params)->rowCount();
    }

    /**
     * Terima Massal (1-Klik) untuk seluruh pendaftar yang status berkasnya sudah 'Lengkap'
     */
    public static function bulkAcceptBerkasLengkap(?int $groupId = null, ?string $jenjang = null, ?string $jenisPendaftaran = null): int
    {
        $where = ["`status_berkas` = 'Lengkap'", "`status_kelulusan` != 'Lulus / Diterima'"];
        $params = ['Lulus / Diterima'];

        if ($groupId) {
            $where[] = "`group_id` = ?";
            $params[] = $groupId;
        }
        if (!empty($jenjang)) {
            $where[] = "`jenjang` = ?";
            $params[] = $jenjang;
        }
        if (!empty($jenisPendaftaran)) {
            $where[] = "`jenis_pendaftaran` = ?";
            $params[] = $jenisPendaftaran;
        }

        $whereClause = implode(' AND ', $where);
        $sql = "UPDATE `spmb_pendaftar` SET `status_kelulusan` = ? WHERE $whereClause";
        return self::db()->query($sql, $params)->rowCount();
    }

    /**
     * Ambil daftar calon siswa yang dinyatakan 'Lulus / Diterima' untuk pengumuman resmi
     */
    public static function getDaftarKelulusan(array $filters = []): array
    {
        $where = ["p.`status_kelulusan` = 'Lulus / Diterima'"];
        $params = [];

        if (!empty($filters['group_id'])) {
            $where[] = "p.`group_id` = ?";
            $params[] = (int)$filters['group_id'];
        }
        if (!empty($filters['jenjang']) && $filters['jenjang'] !== 'SEMUA') {
            $where[] = "p.`jenjang` = ?";
            $params[] = trim($filters['jenjang']);
        }
        if (!empty($filters['jenis_pendaftaran'])) {
            $where[] = "p.`jenis_pendaftaran` = ?";
            $params[] = trim($filters['jenis_pendaftaran']);
        }
        if (!empty($filters['gelombang'])) {
            $where[] = "p.`gelombang` = ?";
            $params[] = trim($filters['gelombang']);
        }

        $whereClause = implode(' AND ', $where);
        return self::db()->findAll("
            SELECT p.*, g.nama_group, g.tahun_ajaran
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_group` g ON g.id = p.group_id
            WHERE $whereClause
            ORDER BY FIELD(p.jenjang, 'PAUD', 'SD', 'SMP', 'SMA'), p.nomor_pendaftaran ASC, p.nama_lengkap ASC
        ", $params);
    }

    /**
     * Nama resmi unit sekolah berdasarkan jenjang
     */
    public static function getNamaSekolahByJenjang(string $jenjang): string
    {
        $jenjang = strtoupper(trim($jenjang));
        return match($jenjang) {
            'PAUD', 'TK' => 'PAUD IT Bina Insan Palu',
            'SD'         => 'SD Islam Terpadu Bina Insan Palu',
            'SMP'        => 'SMP Islam Terpadu Bina Insan Palu',
            'SMA'        => 'SMA Islam Terpadu Bina Insan Palu',
            default      => 'SIT Bina Insan Palu'
        };
    }

    /**
     * Kirim Notifikasi WhatsApp Pengumuman Kelulusan / Resmi Diterima ke Orang Tua
     */
    public static function sendWaKelulusanNotification(int $pendaftarId): array
    {
        $pendaftar = self::getPendaftarById($pendaftarId);
        if (!$pendaftar) {
            return ['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'];
        }

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

        $group = !empty($pendaftar['group_id']) ? self::getGroupById((int)$pendaftar['group_id']) : self::getActiveGroup();
        $tahunAjaran = $group['tahun_ajaran'] ?? ($group['nama_group'] ?? '2026/2027');
        $namaUnit = self::getNamaSekolahByJenjang($pendaftar['jenjang'] ?? 'SD');

        $template = self::getSetting('spmb_wa_template_kelulusan');
        if (empty($template)) {
            $template = "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\nYth. Bapak/Ibu Orang Tua/Wali dari ananda:\n👤 Nama: *{nama_siswa}*\n📋 No. Pendaftaran: *{nomor_pendaftaran}*\n🏫 Unit: *{nama_sekolah}*\n\nAlhamdulillah, kami mengucapkan *SELAMAT!* 🎉\nBerdasarkan hasil verifikasi berkas dan seleksi masuk SPMB, ananda dinyatakan:\n\n⭐ *RESMI DITERIMA* ⭐\nsebagai Murid Baru Tahun Ajaran *{tahun_ajaran}*.\n\n📌 *Langkah Selanjutnya:*\nDimohon Bapak/Ibu untuk menyelesaikan administrasi daftar ulang dan pengukuran seragam di Sekretariat SPMB SIT Bina Insan Palu.\n\nInformasi lebih lanjut dapat menghubungi Panitia SPMB.\n\nTerima kasih atas kepercayaan dan amanah Bapak/Ibu kepada Yayasan Bina Insan Palu.\n\nWassalamu'alaikum Warahmatullahi Wabarakatuh.\n_Panitia SPMB Yayasan Bina Insan Palu_";
        }

        $replacements = [
            '{nama_siswa}'        => $pendaftar['nama_lengkap'],
            '{nomor_pendaftaran}' => $pendaftar['nomor_pendaftaran'],
            '{no_daftar}'         => $pendaftar['nomor_pendaftaran'],
            '{jenjang}'           => $pendaftar['jenjang'],
            '{nama_sekolah}'      => $namaUnit,
            '{tahun_ajaran}'      => $tahunAjaran,
            '{gelombang}'         => $pendaftar['gelombang'] ?? 'Gelombang 1',
            '{jalur}'             => $pendaftar['jalur_pendaftaran'] ?? 'Reguler'
        ];

        $pesan = str_replace(array_keys($replacements), array_values($replacements), $template);

        $res = WaGateway::send($targetPhone, $pesan, [
            'module'         => 'spmb_kelulusan',
            'include_footer' => true
        ]);

        if (!empty($res['success'])) {
            self::db()->query(
                "UPDATE `spmb_pendaftar` SET `wa_kelulusan_sent_at` = NOW() WHERE `id` = ?",
                [$pendaftarId]
            );
        }

        return $res;
    }

    /**
     * Generate Teks Pengumuman Resmi Format WhatsApp untuk Dibagikan ke Grup Orang Tua
     */
    public static function generateTeksPengumumanGrup(string $jenjang = 'SD', ?int $groupId = null, string $jenisPendaftaran = 'Siswa Baru'): string
    {
        $group = $groupId ? self::getGroupById($groupId) : self::getActiveGroup();
        $ta = $group['tahun_ajaran'] ?? ($group['nama_group'] ?? '2026/2027');
        $namaUnit = self::getNamaSekolahByJenjang($jenjang);

        $list = self::getDaftarKelulusan([
            'group_id'          => $group['id'] ?? null,
            'jenjang'           => $jenjang,
            'jenis_pendaftaran' => $jenisPendaftaran
        ]);

        $total = count($list);
        $tglIndo = self::formatTglIndo(date('Y-m-d'));

        $text = "📢 *PENGUMUMAN HASIL SELEKSI SPMB*\n";
        $text .= "*YAYASAN BINA INSAN PALU*\n";
        $text .= "*TAHUN AJARAN {$ta}*\n";
        $text .= "══════════════════════════\n\n";
        $text .= "🏫 Unit: *{$namaUnit}*\n";
        $text .= "📅 Tanggal Pengumuman: {$tglIndo}\n";
        $text .= "👥 Total Calon Siswa Diterima: *{$total} Orang*\n\n";
        $text .= "Alhamdulillah, puji dan syukur kita panjatkan ke hadirat Allah SWT. Berdasarkan hasil verifikasi berkas dan kelayakan administrasi, berikut adalah calon murid baru yang dinyatakan:\n\n";
        $text .= "🎉 *RESMI DITERIMA* 🎉\n";
        $text .= "──────────────────────────\n";

        if (empty($list)) {
            $text .= "_(Belum ada calon siswa dengan status Diterima pada jenjang ini)_\n";
        } else {
            $no = 1;
            foreach ($list as $s) {
                $lp = ($s['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan';
                $text .= sprintf("%02d. [%s] *%s* (%s)\n", $no, $s['nomor_pendaftaran'], $s['nama_lengkap'], $lp);
                $no++;
            }
        }

        $text .= "──────────────────────────\n\n";
        $text .= "📌 *INFORMASI & TAHAPAN SELANJUTNYA:*\n";
        $text .= "1. Orang tua/wali yang anandanya tercantum di atas dimohon untuk menyelesaikan administrasi daftar ulang.\n";
        $text .= "2. Dokumen resmi Surat Keputusan (SK) Kelulusan bertanda tangan panitia terlampir pada file PDF berikut.\n";
        $text .= "3. Kontak Sekretariat SPMB: Hubungi Panitia SPMB SIT Bina Insan Palu.\n\n";
        $text .= "Selamat bergabung di keluarga besar SIT Bina Insan Palu! 🌟\n\n";
        $text .= "Wassalamu'alaikum Warahmatullahi Wabarakatuh.\n";
        $text .= "_Panitia SPMB Yayasan Bina Insan Palu_";

        return $text;
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

    /**
     * Ambil path file kop gambar untuk unit tertentu (PAUD, SD, SMP, SMA)
     * Dengan fallback ke template surat atau kop global SPMB
     */
    public static function getKopUnit(string $jenjang): ?string
    {
        $jenjang = strtoupper(trim($jenjang));
        if ($jenjang === 'TK' || str_contains($jenjang, 'PAUD') || str_contains($jenjang, 'TK')) {
            $jenjang = 'PAUD';
        }
        
        // 1. Cek setting spesifik kop unit (spmb_kop_{jenjang})
        $val = self::getSetting('spmb_kop_' . strtolower($jenjang));
        if (!empty($val)) return $val;

        // 2. Cek setting kop_unit_{jenjang} dari pengaturan sistem identitas
        $valSys = self::getSetting('kop_unit_' . $jenjang);
        if (!empty($valSys)) return $valSys;

        // 3. Cek di tabel spmb_surat_template
        $tpl = self::getSuratTemplate($jenjang);
        if (!empty($tpl['kop_gambar'])) return $tpl['kop_gambar'];

        // 4. Fallback ke global kop SPMB
        $global = self::getSetting('spmb_kop_gambar');
        if (!empty($global)) return $global;

        return null;
    }

    /**
     * Simpan / perbarui kop gambar unit
     */
    public static function setKopUnit(string $jenjang, ?string $path): void
    {
        $jenjang = strtoupper(trim($jenjang));
        if ($jenjang === 'TK' || str_contains($jenjang, 'PAUD') || str_contains($jenjang, 'TK')) {
            $jenjang = 'PAUD';
        }

        self::setSetting('spmb_kop_' . strtolower($jenjang), $path ?? '');
        self::setSetting('kop_unit_' . $jenjang, $path ?? '');
        self::updateKopTemplate($jenjang, $path);
    }

    /**
     * Ambil seluruh data kop unit untuk keperluan view pengaturan
     */
    public static function getAllKopUnits(): array
    {
        $units = ['PAUD', 'SD', 'SMP', 'SMA'];
        $result = [
            'GLOBAL' => self::getSetting('spmb_kop_gambar') ?: ''
        ];
        foreach ($units as $u) {
            // Ambil spesifik unit tanpa fallback global jika untuk preview manajemen
            $spec = self::getSetting('spmb_kop_' . strtolower($u));
            if (empty($spec)) {
                $tpl = self::getSuratTemplate($u);
                $spec = !empty($tpl['kop_gambar']) ? $tpl['kop_gambar'] : '';
            }
            if (empty($spec)) {
                $spec = self::getSetting('kop_unit_' . $u) ?: '';
            }
            $result[$u] = $spec;
        }
        return $result;
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

    // ═════════════════════════════════════════════════════════════════════════
    // 9. SCREENING PSIKOLOG
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Ambil list pendaftar beserta status file hasil screening psikolog
     */
    public static function getScreeningPsikologList(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['group_id'])) {
            $conditions[] = "p.group_id = ?";
            $params[] = (int)$filters['group_id'];
        }

        if (!empty($filters['jenjang'])) {
            $conditions[] = "p.jenjang = ?";
            $params[] = strtoupper(trim($filters['jenjang']));
        }

        if (!empty($filters['jenis_pendaftaran'])) {
            $conditions[] = "p.jenis_pendaftaran = ?";
            $params[] = $filters['jenis_pendaftaran'];
        }

        // Khusus Fitur Hasil Psikolog: Hanya siswa yang sudah Lulus / Diterima
        $statusKelulusan = !empty($filters['status_kelulusan']) ? $filters['status_kelulusan'] : 'Lulus / Diterima';
        $conditions[] = "p.status_kelulusan = ?";
        $params[] = $statusKelulusan;

        if (!empty($filters['status_screening'])) {
            if ($filters['status_screening'] === 'sudah') {
                $conditions[] = "(sc.file_pdf IS NOT NULL AND sc.file_pdf != '')";
            } elseif ($filters['status_screening'] === 'belum') {
                $conditions[] = "(sc.file_pdf IS NULL OR sc.file_pdf = '')";
            }
        }

        if (!empty($filters['status_rekomendasi'])) {
            $conditions[] = "sc.status_rekomendasi = ?";
            $params[] = $filters['status_rekomendasi'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $conditions[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ? OR p.nisn LIKE ? OR p.asal_sekolah LIKE ? OR sc.nama_psikolog LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "
            SELECT p.id as pendaftar_id, p.id, p.nomor_pendaftaran, p.nama_lengkap, p.nama_panggilan,
                   p.jenjang, p.jenis_pendaftaran, p.gelombang, p.kategori_pendaftar, p.foto,
                   p.status_kelulusan, p.status_berkas, p.status_pembayaran, p.no_wa, p.no_hp, p.no_hp_ayah, p.no_hp_ibu,
                   p.asal_sekolah, p.biodata_token, p.wa_psikolog_sent_at,
                   sc.id as screening_id, sc.file_pdf, sc.file_name_original, sc.file_size,
                   sc.status_rekomendasi, sc.catatan as catatan_psikolog, sc.nama_psikolog,
                   sc.tanggal_screening, sc.created_at as screening_created_at, sc.updated_at as screening_updated_at,
                   sc.wa_sent_at, sc.wa_sent_count, sc.last_wa_phone, sc.last_wa_status
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_screening_psikolog` sc ON sc.pendaftar_id = p.id
            $whereClause
            ORDER BY 
                p.nomor_pendaftaran ASC, p.id DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $rows = self::db()->findAll($sql, $params);
        foreach ($rows as &$r) {
            if (empty($r['biodata_token'])) {
                $newToken = bin2hex(random_bytes(24));
                self::db()->query("UPDATE `spmb_pendaftar` SET `biodata_token` = ? WHERE `id` = ?", [$newToken, $r['pendaftar_id']]);
                $r['biodata_token'] = $newToken;
            }
        }
        unset($r);

        return $rows;
    }

    /**
     * Hitung total pendaftar untuk screening psikolog (Hanya yang Lulus / Diterima)
     */
    public static function countScreeningPsikolog(array $filters = []): int
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['group_id'])) {
            $conditions[] = "p.group_id = ?";
            $params[] = (int)$filters['group_id'];
        }

        if (!empty($filters['jenjang'])) {
            $conditions[] = "p.jenjang = ?";
            $params[] = strtoupper(trim($filters['jenjang']));
        }

        if (!empty($filters['jenis_pendaftaran'])) {
            $conditions[] = "p.jenis_pendaftaran = ?";
            $params[] = $filters['jenis_pendaftaran'];
        }

        // Khusus Fitur Hasil Psikolog: Hanya siswa yang sudah Lulus / Diterima
        $statusKelulusan = !empty($filters['status_kelulusan']) ? $filters['status_kelulusan'] : 'Lulus / Diterima';
        $conditions[] = "p.status_kelulusan = ?";
        $params[] = $statusKelulusan;

        if (!empty($filters['status_screening'])) {
            if ($filters['status_screening'] === 'sudah') {
                $conditions[] = "(sc.file_pdf IS NOT NULL AND sc.file_pdf != '')";
            } elseif ($filters['status_screening'] === 'belum') {
                $conditions[] = "(sc.file_pdf IS NULL OR sc.file_pdf = '')";
            }
        }

        if (!empty($filters['status_rekomendasi'])) {
            $conditions[] = "sc.status_rekomendasi = ?";
            $params[] = $filters['status_rekomendasi'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $conditions[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ? OR p.nisn LIKE ? OR p.asal_sekolah LIKE ? OR sc.nama_psikolog LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "
            SELECT COUNT(p.id) as total
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_screening_psikolog` sc ON sc.pendaftar_id = p.id
            $whereClause
        ";

        $res = self::db()->find($sql, $params);
        return (int)($res['total'] ?? 0);
    }

    /**
     * Hitung statistik ringkasan screening psikolog (Hanya yang Lulus / Diterima)
     */
    public static function getScreeningPsikologStats(?int $groupId = null): array
    {
        $params = [];
        $conditions = ["p.status_kelulusan = 'Lulus / Diterima'"];
        if (!empty($groupId)) {
            $conditions[] = "p.group_id = ?";
            $params[] = $groupId;
        }
        $where = "WHERE " . implode(" AND ", $conditions);

        $sql = "
            SELECT 
                COUNT(p.id) as total_siswa,
                SUM(CASE WHEN sc.file_pdf IS NOT NULL AND sc.file_pdf != '' THEN 1 ELSE 0 END) as total_sudah_upload,
                SUM(CASE WHEN sc.file_pdf IS NULL OR sc.file_pdf = '' THEN 1 ELSE 0 END) as total_belum_upload,
                SUM(CASE WHEN sc.status_rekomendasi = 'Direkomendasikan' THEN 1 ELSE 0 END) as total_direkomendasikan,
                SUM(CASE WHEN sc.status_rekomendasi = 'Dipertimbangkan' THEN 1 ELSE 0 END) as total_dipertimbangkan,
                SUM(CASE WHEN sc.status_rekomendasi = 'Tidak Direkomendasikan' THEN 1 ELSE 0 END) as total_tidak_direkomendasikan
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_screening_psikolog` sc ON sc.pendaftar_id = p.id
            $where
        ";

        $res = self::db()->find($sql, $params);
        $totalSiswa = (int)($res['total_siswa'] ?? 0);
        $sudah = (int)($res['total_sudah_upload'] ?? 0);
        $belum = (int)($res['total_belum_upload'] ?? 0);
        $persen = $totalSiswa > 0 ? round(($sudah / $totalSiswa) * 100, 1) : 0;

        return [
            'total_siswa'               => $totalSiswa,
            'total_sudah_upload'        => $sudah,
            'total_belum_upload'        => $belum,
            'persen_selesai'            => $persen,
            'total_direkomendasikan'    => (int)($res['total_direkomendasikan'] ?? 0),
            'total_dipertimbangkan'     => (int)($res['total_dipertimbangkan'] ?? 0),
            'total_tidak_direkomendasikan' => (int)($res['total_tidak_direkomendasikan'] ?? 0),
        ];
    }

    /**
     * Ambil data screening psikolog berdasarkan pendaftar ID
     */
    public static function getScreeningByPendaftarId(int $pendaftarId): ?array
    {
        return self::db()->find("
            SELECT sc.*, p.nomor_pendaftaran, p.nama_lengkap, p.jenjang, p.jenis_pendaftaran
            FROM `spmb_screening_psikolog` sc
            JOIN `spmb_pendaftar` p ON p.id = sc.pendaftar_id
            WHERE sc.pendaftar_id = ?
        ", [$pendaftarId]);
    }

    /**
     * Simpan / Perbarui data hasil screening psikolog (UPSERT)
     */
    public static function saveScreeningPsikolog(int $pendaftarId, array $data): bool
    {
        $existing = self::db()->find("SELECT id, file_pdf FROM `spmb_screening_psikolog` WHERE `pendaftar_id` = ?", [$pendaftarId]);

        $filePath = $data['file_pdf'] ?? ($existing['file_pdf'] ?? null);
        $origName = $data['file_name_original'] ?? null;
        $fSize    = (int)($data['file_size'] ?? 0);
        $rekom    = $data['status_rekomendasi'] ?? 'Direkomendasikan';
        $catatan  = $data['catatan'] ?? null;
        $namaPsi  = $data['nama_psikolog'] ?? null;
        $tgl      = !empty($data['tanggal_screening']) ? $data['tanggal_screening'] : date('Y-m-d');
        $userId   = !empty($data['uploaded_by']) ? (int)$data['uploaded_by'] : null;

        if ($existing) {
            $sql = "UPDATE `spmb_screening_psikolog` SET
                        `file_pdf` = ?,
                        `file_name_original` = COALESCE(?, `file_name_original`),
                        `file_size` = CASE WHEN ? > 0 THEN ? ELSE `file_size` END,
                        `status_rekomendasi` = ?,
                        `catatan` = ?,
                        `nama_psikolog` = ?,
                        `tanggal_screening` = ?,
                        `uploaded_by` = COALESCE(?, `uploaded_by`),
                        `updated_at` = NOW()
                    WHERE `id` = ?";
            self::db()->query($sql, [
                $filePath,
                $origName,
                $fSize,
                $fSize,
                $rekom,
                $catatan,
                $namaPsi,
                $tgl,
                $userId,
                $existing['id']
            ]);
        } else {
            $sql = "INSERT INTO `spmb_screening_psikolog` (
                        `pendaftar_id`, `file_pdf`, `file_name_original`, `file_size`,
                        `status_rekomendasi`, `catatan`, `nama_psikolog`, `tanggal_screening`,
                        `uploaded_by`, `created_at`, `updated_at`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            self::db()->query($sql, [
                $pendaftarId,
                $filePath,
                $origName,
                $fSize,
                $rekom,
                $catatan,
                $namaPsi,
                $tgl,
                $userId
            ]);
        }

        // Sinkronkan juga ke tabel spmb_pendaftar
        self::db()->query("
            UPDATE `spmb_pendaftar` SET
                `file_screening_psikolog` = ?,
                `status_screening_psikolog` = ?,
                `catatan_screening_psikolog` = ?,
                `tgl_screening_psikolog` = ?
            WHERE `id` = ?
        ", [$filePath, $rekom, $catatan, $tgl, $pendaftarId]);

        return true;
    }

    /**
     * Hapus file dan data screening psikolog siswa
     */
    public static function deleteScreeningPsikolog(int $pendaftarId): bool
    {
        $existing = self::db()->find("SELECT id, file_pdf FROM `spmb_screening_psikolog` WHERE `pendaftar_id` = ?", [$pendaftarId]);
        if ($existing && !empty($existing['file_pdf'])) {
            $fullPath = BASE_PATH . '/public/' . ltrim($existing['file_pdf'], '/');
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        self::db()->query("DELETE FROM `spmb_screening_psikolog` WHERE `pendaftar_id` = ?", [$pendaftarId]);

        // Reset kolom di spmb_pendaftar
        self::db()->query("
            UPDATE `spmb_pendaftar` SET
                `file_screening_psikolog` = NULL,
                `status_screening_psikolog` = NULL,
                `catatan_screening_psikolog` = NULL,
                `tgl_screening_psikolog` = NULL
            WHERE `id` = ?
        ", [$pendaftarId]);

        return true;
    }

    /**
     * Ambil data pendaftar dan hasil screening psikolog berdasarkan biodata_token
     */
    public static function getPendaftarByPsikologToken(string $token): ?array
    {
        $token = trim($token);
        if (empty($token)) return null;

        $sql = "
            SELECT p.*, 
                   sc.id as screening_id, sc.file_pdf, sc.file_name_original, sc.file_size,
                   sc.status_rekomendasi, sc.catatan as catatan_psikolog, sc.nama_psikolog,
                   sc.tanggal_screening, sc.wa_sent_at, sc.wa_sent_count,
                   g.nama_group, g.tahun_ajaran as group_tahun_ajaran
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_screening_psikolog` sc ON sc.pendaftar_id = p.id
            LEFT JOIN `spmb_group` g ON g.id = p.group_id
            WHERE p.biodata_token = ?
            LIMIT 1
        ";
        return self::db()->find($sql, [$token]) ?: null;
    }

    /**
     * Ambil daftar pendaftar yang siap dikirimi Broadcast WA link hasil psikolog (Filter unit, status kirim, limit)
     */
    public static function getDaftarScreeningPsikologBroadcast(array $filters = []): array
    {
        $conditions = [
            "p.status_kelulusan = 'Lulus / Diterima'",
            "(sc.file_pdf IS NOT NULL AND sc.file_pdf != '')"
        ];
        $params = [];

        if (!empty($filters['group_id'])) {
            $conditions[] = "p.group_id = ?";
            $params[] = (int)$filters['group_id'];
        }

        if (!empty($filters['jenjang']) && strtoupper(trim($filters['jenjang'])) !== 'ALL') {
            $conditions[] = "p.jenjang = ?";
            $params[] = strtoupper(trim($filters['jenjang']));
        }

        if (!empty($filters['status_rekomendasi']) && strtoupper(trim($filters['status_rekomendasi'])) !== 'ALL') {
            $conditions[] = "sc.status_rekomendasi = ?";
            $params[] = $filters['status_rekomendasi'];
        }

        $statusWa = $filters['status_wa'] ?? 'belum';
        if ($statusWa === 'belum') {
            $conditions[] = "(sc.wa_sent_at IS NULL OR sc.wa_sent_count = 0)";
        } elseif ($statusWa === 'sudah') {
            $conditions[] = "(sc.wa_sent_at IS NOT NULL AND sc.wa_sent_count > 0)";
        }

        $whereClause = "WHERE " . implode(" AND ", $conditions);

        $limitClause = "";
        if (!empty($filters['limit']) && (int)$filters['limit'] > 0) {
            $limitClause = "LIMIT " . (int)$filters['limit'];
        }

        $sql = "
            SELECT p.id as pendaftar_id, p.id, p.nomor_pendaftaran, p.nama_lengkap, p.jenjang,
                   p.no_wa, p.no_hp, p.no_hp_ayah, p.no_hp_ibu, p.biodata_token,
                   sc.file_pdf, sc.file_name_original, sc.status_rekomendasi, sc.wa_sent_at, sc.wa_sent_count, sc.last_wa_phone
            FROM `spmb_pendaftar` p
            JOIN `spmb_screening_psikolog` sc ON sc.pendaftar_id = p.id
            $whereClause
            ORDER BY p.nomor_pendaftaran ASC
            $limitClause
        ";

        $rows = self::db()->findAll($sql, $params);
        foreach ($rows as &$r) {
            if (empty($r['biodata_token'])) {
                $newToken = bin2hex(random_bytes(24));
                self::db()->query("UPDATE `spmb_pendaftar` SET `biodata_token` = ? WHERE `id` = ?", [$newToken, $r['pendaftar_id']]);
                $r['biodata_token'] = $newToken;
            }
            $targetPhone = !empty($r['no_wa']) ? $r['no_wa'] : (!empty($r['no_hp']) ? $r['no_hp'] : (!empty($r['no_hp_ayah']) ? $r['no_hp_ayah'] : (!empty($r['no_hp_ibu']) ? $r['no_hp_ibu'] : '')));
            $r['target_phone'] = $targetPhone;
            $r['public_url'] = self::makeUrl('spmb/psikolog/' . $r['biodata_token']);
        }
        unset($r);

        return $rows;
    }

    /**
     * Helper URL builder aman untuk context web maupun CLI/Cron
     */
    public static function makeUrl(string $path = ''): string
    {
        if (function_exists('url')) {
            return url($path);
        }
        $base = defined('BASE_URL') ? BASE_URL : '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Kirim Pesan WhatsApp Link Hasil Screening Psikolog ke Orang Tua Siswa
     */
    public static function sendWaScreeningPsikolog(int $pendaftarId, ?string $customTemplate = null): array
    {
        $pendaftar = self::getPendaftarById($pendaftarId);
        if (!$pendaftar) {
            return ['success' => false, 'message' => 'Data calon siswa tidak ditemukan.'];
        }

        $screening = self::getScreeningByPendaftarId($pendaftarId);
        if (!$screening || empty($screening['file_pdf'])) {
            return ['success' => false, 'message' => 'Berkas PDF hasil psikolog belum diunggah untuk siswa ini.'];
        }

        // Pastikan token tersedia
        if (empty($pendaftar['biodata_token'])) {
            $newToken = bin2hex(random_bytes(24));
            self::db()->query("UPDATE `spmb_pendaftar` SET `biodata_token` = ? WHERE `id` = ?", [$newToken, $pendaftarId]);
            $pendaftar['biodata_token'] = $newToken;
        }

        $targetPhone = !empty($pendaftar['no_wa']) ? $pendaftar['no_wa'] :
                      (!empty($pendaftar['no_hp']) ? $pendaftar['no_hp'] :
                      (!empty($pendaftar['no_hp_ayah']) ? $pendaftar['no_hp_ayah'] :
                      (!empty($pendaftar['no_hp_ibu']) ? $pendaftar['no_hp_ibu'] : '')));

        if (empty($targetPhone)) {
            return ['success' => false, 'message' => 'Nomor WhatsApp atau No HP orang tua/wali belum diisi pada formulir pendaftaran.'];
        }

        $group = !empty($pendaftar['group_id']) ? self::getGroupById((int)$pendaftar['group_id']) : self::getActiveGroup();
        $tahunAjaran = $group['tahun_ajaran'] ?? ($group['nama_group'] ?? '2026/2027');
        $namaUnit = self::getNamaSekolahByJenjang($pendaftar['jenjang'] ?? 'SD');
        $publicUrl = self::makeUrl('spmb/psikolog/' . $pendaftar['biodata_token']);

        $template = !empty(trim($customTemplate ?? '')) ? $customTemplate : self::getDefaultWaPsikologTemplate();

        $replacements = [
            '{nama_siswa}'          => $pendaftar['nama_lengkap'],
            '{nomor_pendaftaran}'   => $pendaftar['nomor_pendaftaran'],
            '{no_daftar}'           => $pendaftar['nomor_pendaftaran'],
            '{nama_sekolah}'        => $namaUnit,
            '{nama_unit}'           => $namaUnit,
            '{jenjang}'             => $pendaftar['jenjang'],
            '{tahun_ajaran}'        => $tahunAjaran,
            '{rekomendasi}'         => $screening['status_rekomendasi'] ?: 'Direkomendasikan',
            '{link_hasil_psikolog}' => $publicUrl,
            '{link_file}'           => $publicUrl,
        ];

        $pesan = str_replace(array_keys($replacements), array_values($replacements), $template);

        // Kirim melalui WaGateway terpusat
        $res = WaGateway::send($targetPhone, $pesan, [
            'module'         => 'spmb_psikolog',
            'include_footer' => true
        ]);

        if (!empty($res['success'])) {
            self::db()->query("
                UPDATE `spmb_screening_psikolog` SET
                    `wa_sent_at` = NOW(),
                    `wa_sent_count` = `wa_sent_count` + 1,
                    `last_wa_phone` = ?,
                    `last_wa_status` = 'Success'
                WHERE `pendaftar_id` = ?
            ", [$targetPhone, $pendaftarId]);

            self::db()->query("
                UPDATE `spmb_pendaftar` SET `wa_psikolog_sent_at` = NOW() WHERE `id` = ?
            ", [$pendaftarId]);
        } else {
            self::db()->query("
                UPDATE `spmb_screening_psikolog` SET
                    `last_wa_phone` = ?,
                    `last_wa_status` = 'Failed'
                WHERE `pendaftar_id` = ?
            ", [$targetPhone, $pendaftarId]);
        }

        return $res;
    }

    /**
     * Template standar pesan WhatsApp Laporan Hasil Psikolog
     */
    public static function getDefaultWaPsikologTemplate(): string
    {
        return "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
             . "Yth. Bapak/Ibu Orang Tua/Wali dari ananda:\n"
             . "👤 Nama: *{nama_siswa}*\n"
             . "📋 No. Pendaftaran: *{nomor_pendaftaran}*\n"
             . "🏫 Unit: *{nama_sekolah}*\n\n"
             . "Alhamdulillah, kami sampaikan Laporan & Berkas Resmi Hasil Screening / Tes Psikolog Calon Murid Baru Tahun Ajaran *{tahun_ajaran}*.\n\n"
             . "📊 *Hasil Rekomendasi:* *{rekomendasi}*\n\n"
             . "Silakan klik tautan resmi di bawah ini untuk melihat dan mengunduh berkas laporan hasil psikolog ananda:\n"
             . "🔗 *Tautan Berkas Hasil Psikolog:*\n"
             . "{link_hasil_psikolog}\n\n"
             . "_(Tautan di atas dapat dibuka langsung melalui smartphone atau komputer tanpa perlu login)_\n\n"
             . "Terima kasih atas kerja sama dan kepercayaan Bapak/Ibu kepada Yayasan Bina Insan Palu.\n\n"
             . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.\n"
             . "_Panitia SPMB SIT Bina Insan Palu_";
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 10. OBSERVASI AWAL SPMB (INSTRUMEN, PENGISIAN ORANG TUA & HASIL)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Ambil daftar grup pertanyaan observasi awal
     */
    public static function getObservasiGroups(?string $jenjang = null, bool $onlyActive = true): array
    {
        $conditions = [];
        $params = [];

        if ($onlyActive) {
            $conditions[] = "`is_active` = 1";
        }

        if (!empty($jenjang) && strtoupper(trim($jenjang)) !== 'ALL') {
            $conditions[] = "(`jenjang` = ? OR `jenjang` = 'ALL')";
            $params[] = strtoupper(trim($jenjang));
        }

        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT * FROM `spmb_observasi_group` $where ORDER BY FIELD(jenjang, 'PAUD', 'SD', 'SMP', 'SMA', 'ALL'), `urutan` ASC, `id` ASC";

        return self::db()->findAll($sql, $params);
    }

    /**
     * Ambil detail grup pertanyaan observasi awal berdasarkan ID
     */
    public static function getObservasiGroupById(int $id): ?array
    {
        return self::db()->find("SELECT * FROM `spmb_observasi_group` WHERE `id` = ?", [$id]);
    }

    /**
     * Simpan / Perbarui Grup Pertanyaan Observasi
     */
    public static function saveObservasiGroup(array $data, ?int $id = null): int
    {
        $jenjang   = strtoupper(trim($data['jenjang'] ?? 'ALL'));
        $namaGroup = trim($data['nama_group'] ?? '');
        $deskripsi = trim($data['deskripsi'] ?? '');
        $urutan    = (int)($data['urutan'] ?? 1);
        $isActive  = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if ($id && $id > 0) {
            self::db()->query("
                UPDATE `spmb_observasi_group` SET
                    `jenjang` = ?, `nama_group` = ?, `deskripsi` = ?, `urutan` = ?, `is_active` = ?, `updated_at` = NOW()
                WHERE `id` = ?
            ", [$jenjang, $namaGroup, $deskripsi, $urutan, $isActive, $id]);
            return $id;
        }

        self::db()->query("
            INSERT INTO `spmb_observasi_group` (`jenjang`, `nama_group`, `deskripsi`, `urutan`, `is_active`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ", [$jenjang, $namaGroup, $deskripsi, $urutan, $isActive]);

        return (int)self::db()->lastInsertId();
    }

    /**
     * Hapus Grup Pertanyaan Observasi beserta butir pertanyaannya
     */
    public static function deleteObservasiGroup(int $id): bool
    {
        self::db()->query("DELETE FROM `spmb_observasi_pertanyaan` WHERE `group_id` = ?", [$id]);
        self::db()->query("DELETE FROM `spmb_observasi_group` WHERE `id` = ?", [$id]);
        return true;
    }

    /**
     * Ambil daftar butir pertanyaan observasi awal
     */
    public static function getObservasiPertanyaanList(?string $jenjang = null, ?int $groupId = null, bool $onlyActive = true): array
    {
        $conditions = [];
        $params = [];

        if ($onlyActive) {
            $conditions[] = "p.`is_active` = 1";
        }

        if (!empty($jenjang) && strtoupper(trim($jenjang)) !== 'ALL') {
            $conditions[] = "(p.`jenjang` = ? OR p.`jenjang` = 'ALL')";
            $params[] = strtoupper(trim($jenjang));
        }

        if (!empty($groupId)) {
            $conditions[] = "p.`group_id` = ?";
            $params[] = (int)$groupId;
        }

        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "
            SELECT p.*, g.nama_group, g.urutan as group_urutan
            FROM `spmb_observasi_pertanyaan` p
            JOIN `spmb_observasi_group` g ON g.id = p.group_id
            $where
            ORDER BY FIELD(p.jenjang, 'PAUD', 'SD', 'SMP', 'SMA', 'ALL'), g.urutan ASC, p.urutan ASC, p.id ASC
        ";

        $rows = self::db()->findAll($sql, $params);
        foreach ($rows as &$r) {
            $parsed = !empty($r['opsi_jawaban']) ? json_decode($r['opsi_jawaban'], true) : [];
            $r['opsi_parsed'] = is_array($parsed) ? $parsed : [];
        }
        unset($r);

        return $rows;
    }

    /**
     * Ambil butir pertanyaan berdasarkan ID
     */
    public static function getObservasiPertanyaanById(int $id): ?array
    {
        $r = self::db()->find("
            SELECT p.*, g.nama_group 
            FROM `spmb_observasi_pertanyaan` p 
            JOIN `spmb_observasi_group` g ON g.id = p.group_id 
            WHERE p.`id` = ?
        ", [$id]);

        if ($r) {
            $parsed = !empty($r['opsi_jawaban']) ? json_decode($r['opsi_jawaban'], true) : [];
            $r['opsi_parsed'] = is_array($parsed) ? $parsed : [];
        }
        return $r ?: null;
    }

    /**
     * Simpan / Perbarui Butir Pertanyaan Observasi
     */
    public static function saveObservasiPertanyaan(array $data, ?int $id = null): int
    {
        $groupId    = (int)($data['group_id'] ?? 0);
        $jenjang    = strtoupper(trim($data['jenjang'] ?? 'ALL'));
        $pertanyaan = trim($data['pertanyaan'] ?? '');
        $indikator  = trim($data['indikator'] ?? '');
        $tipe       = $data['tipe_jawaban'] ?? 'skala';
        $urutan     = (int)($data['urutan'] ?? 1);
        $isActive   = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        // Ambil opsi jawaban (baik berupa JSON string atau array dari form)
        $opsi = [];
        if (!empty($data['opsi_jawaban'])) {
            if (is_array($data['opsi_jawaban'])) {
                $opsi = $data['opsi_jawaban'];
            } else {
                $decoded = json_decode($data['opsi_jawaban'], true);
                $opsi = is_array($decoded) ? $decoded : [];
            }
        }

        // Hitung skor maksimal dari opsi tertinggi
        $skorMaks = 4;
        if (!empty($opsi)) {
            $maxVal = 0;
            foreach ($opsi as $op) {
                $val = (int)($op['skor'] ?? 0);
                if ($val > $maxVal) $maxVal = $val;
            }
            if ($maxVal > 0) $skorMaks = $maxVal;
        }

        $opsiJson = json_encode($opsi, JSON_UNESCAPED_UNICODE);

        if ($id && $id > 0) {
            self::db()->query("
                UPDATE `spmb_observasi_pertanyaan` SET
                    `group_id` = ?, `jenjang` = ?, `pertanyaan` = ?, `indikator` = ?,
                    `tipe_jawaban` = ?, `opsi_jawaban` = ?, `skor_maksimal` = ?,
                    `urutan` = ?, `is_active` = ?, `updated_at` = NOW()
                WHERE `id` = ?
            ", [$groupId, $jenjang, $pertanyaan, $indikator, $tipe, $opsiJson, $skorMaks, $urutan, $isActive, $id]);
            return $id;
        }

        self::db()->query("
            INSERT INTO `spmb_observasi_pertanyaan` (
                `group_id`, `jenjang`, `pertanyaan`, `indikator`, `tipe_jawaban`,
                `opsi_jawaban`, `skor_maksimal`, `urutan`, `is_active`, `created_at`, `updated_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [$groupId, $jenjang, $pertanyaan, $indikator, $tipe, $opsiJson, $skorMaks, $urutan, $isActive]);

        return (int)self::db()->lastInsertId();
    }

    /**
     * Hapus Butir Pertanyaan Observasi
     */
    public static function deleteObservasiPertanyaan(int $id): bool
    {
        self::db()->query("DELETE FROM `spmb_observasi_pertanyaan` WHERE `id` = ?", [$id]);
        return true;
    }

    /**
     * Ambil data calon siswa berdasarkan observasi_token (atau biodata_token)
     */
    public static function getPendaftarByObservasiToken(string $token): ?array
    {
        $token = trim($token);
        if (empty($token)) return null;

        $sql = "
            SELECT p.*,
                   obs.id as respon_id, obs.status as respon_status, obs.nama_pengisi, obs.hubungan_pengisi,
                   obs.no_wa_pengisi, obs.total_skor, obs.skor_maksimal, obs.persentase, obs.kategori_hasil,
                   obs.catatan_orang_tua, obs.submitted_at as observasi_submitted_at,
                   g.nama_group, g.tahun_ajaran as group_tahun_ajaran
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_observasi_respon` obs ON obs.pendaftar_id = p.id
            LEFT JOIN `spmb_group` g ON g.id = p.group_id
            WHERE p.observasi_token = ? OR p.biodata_token = ?
            LIMIT 1
        ";

        $pendaftar = self::db()->find($sql, [$token, $token]);
        if (!$pendaftar) return null;

        // Pastikan kolom observasi_token terisi
        if (empty($pendaftar['observasi_token'])) {
            $newToken = bin2hex(random_bytes(24));
            self::db()->query("UPDATE `spmb_pendaftar` SET `observasi_token` = ? WHERE `id` = ?", [$newToken, $pendaftar['id']]);
            $pendaftar['observasi_token'] = $newToken;
        }

        return $pendaftar;
    }

    /**
     * Ambil struktur formulir observasi awal terkelompok per grup pertanyaan untuk unit/jenjang siswa
     */
    public static function getObservasiFormStructure(string $jenjang): array
    {
        $groups = self::getObservasiGroups($jenjang, true);
        $questions = self::getObservasiPertanyaanList($jenjang, null, true);

        // Petakan pertanyaan ke masing-masing group
        $groupQuestions = [];
        foreach ($questions as $q) {
            $gId = (int)$q['group_id'];
            if (!isset($groupQuestions[$gId])) {
                $groupQuestions[$gId] = [];
            }
            $groupQuestions[$gId][] = $q;
        }

        $result = [];
        foreach ($groups as $g) {
            $gId = (int)$g['id'];
            $items = $groupQuestions[$gId] ?? [];
            if (!empty($items)) {
                $g['pertanyaan'] = $items;
                $result[] = $g;
            }
        }

        return $result;
    }

    /**
     * Simpan pengisian observasi awal oleh orang tua calon siswa
     */
    public static function submitObservasiRespon(int $pendaftarId, array $postData, ?string $ip = null, ?string $ua = null): array
    {
        $pendaftar = self::getPendaftarById($pendaftarId);
        if (!$pendaftar) {
            return ['success' => false, 'message' => 'Data calon siswa tidak ditemukan.'];
        }

        $jenjang = $pendaftar['jenjang'] ?? 'SD';
        $structure = self::getObservasiFormStructure($jenjang);
        if (empty($structure)) {
            return ['success' => false, 'message' => 'Instrumen pertanyaan observasi awal belum dikonfigurasi untuk unit ' . $jenjang];
        }

        // Info pengisi
        $namaPengisi     = trim($postData['nama_pengisi'] ?? '');
        $hubunganPengisi = trim($postData['hubungan_pengisi'] ?? 'Orang Tua / Wali');
        $noWaPengisi     = trim($postData['no_wa_pengisi'] ?? ($pendaftar['no_wa'] ?: ''));
        $catatanOrtu     = trim($postData['catatan_orang_tua'] ?? '');

        if (empty($namaPengisi)) {
            return ['success' => false, 'message' => 'Mohon isi nama lengkap Ayah/Ibu/Wali yang mengisi formulir.'];
        }

        $answers = $postData['answers'] ?? [];
        $totalSkor = 0;
        $skorMaksTotal = 0;
        $parsedAnswers = [];

        foreach ($structure as $grp) {
            foreach ($grp['pertanyaan'] as $q) {
                $qId = (int)$q['id'];
                $valChosen = $answers[$qId] ?? null;

                $labelChosen = '';
                $skorItem = 0;
                $skorMaksItem = (int)($q['skor_maksimal'] ?? 4);

                if (!empty($q['opsi_parsed']) && is_array($q['opsi_parsed'])) {
                    // Cari opsi yang dipilih
                    foreach ($q['opsi_parsed'] as $idx => $op) {
                        $opVal = (string)($op['skor'] ?? $idx);
                        if ((string)$valChosen === (string)$op['skor'] || (string)$valChosen === (string)$idx || (string)$valChosen === (string)$op['label']) {
                            $labelChosen = $op['label'];
                            $skorItem = (int)$op['skor'];
                            break;
                        }
                    }
                }

                // Jika belum terpilih, defaultkan ke opsi pertama atau 0
                if (empty($labelChosen) && !empty($q['opsi_parsed'][0])) {
                    $labelChosen = $q['opsi_parsed'][0]['label'] ?? 'Tidak Dijawab';
                    $skorItem = (int)($q['opsi_parsed'][0]['skor'] ?? 0);
                }

                $totalSkor += $skorItem;
                $skorMaksTotal += $skorMaksItem;

                $parsedAnswers[] = [
                    'pertanyaan_id'   => $qId,
                    'group_id'        => (int)$grp['id'],
                    'pilihan_jawaban' => $labelChosen,
                    'skor'            => $skorItem
                ];
            }
        }

        $persentase = $skorMaksTotal > 0 ? round(($totalSkor / $skorMaksTotal) * 100, 2) : 0.00;

        // Kategori Hasil Observasi
        $kategoriHasil = 'Perlu Pendampingan';
        if ($persentase >= 85) {
            $kategoriHasil = 'Sangat Baik / Mandiri';
        } elseif ($persentase >= 70) {
            $kategoriHasil = 'Baik / Berkembang';
        } elseif ($persentase >= 55) {
            $kategoriHasil = 'Cukup';
        }

        $tokenRespon = $pendaftar['observasi_token'] ?: bin2hex(random_bytes(24));

        // Cek respon yang sudah ada (UPSERT)
        $existing = self::db()->find("SELECT id FROM `spmb_observasi_respon` WHERE `pendaftar_id` = ?", [$pendaftarId]);
        if ($existing) {
            $responId = (int)$existing['id'];
            self::db()->query("
                UPDATE `spmb_observasi_respon` SET
                    `status` = 'selesai',
                    `nama_pengisi` = ?,
                    `hubungan_pengisi` = ?,
                    `no_wa_pengisi` = ?,
                    `total_skor` = ?,
                    `skor_maksimal` = ?,
                    `persentase` = ?,
                    `kategori_hasil` = ?,
                    `catatan_orang_tua` = ?,
                    `submitted_at` = NOW(),
                    `ip_address` = ?,
                    `user_agent` = ?,
                    `updated_at` = NOW()
                WHERE `id` = ?
            ", [
                $namaPengisi, $hubunganPengisi, $noWaPengisi,
                $totalSkor, $skorMaksTotal, $persentase, $kategoriHasil,
                $catatanOrtu, $ip, $ua, $responId
            ]);

            // Hapus jawaban lama dan simpan yang baru
            self::db()->query("DELETE FROM `spmb_observasi_jawaban` WHERE `respon_id` = ?", [$responId]);
        } else {
            self::db()->query("
                INSERT INTO `spmb_observasi_respon` (
                    `pendaftar_id`, `token`, `status`, `nama_pengisi`, `hubungan_pengisi`,
                    `no_wa_pengisi`, `total_skor`, `skor_maksimal`, `persentase`, `kategori_hasil`,
                    `catatan_orang_tua`, `submitted_at`, `ip_address`, `user_agent`, `created_at`, `updated_at`
                ) VALUES (?, ?, 'selesai', ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, NOW(), NOW())
            ", [
                $pendaftarId, $tokenRespon, $namaPengisi, $hubunganPengisi,
                $noWaPengisi, $totalSkor, $skorMaksTotal, $persentase, $kategoriHasil,
                $catatanOrtu, $ip, $ua
            ]);
            $responId = (int)self::db()->lastInsertId();
        }

        // Insert jawaban per pertanyaan
        $insertAnswer = self::db()->getConnection()->prepare("
            INSERT INTO `spmb_observasi_jawaban` (`respon_id`, `pendaftar_id`, `pertanyaan_id`, `group_id`, `pilihan_jawaban`, `skor`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        foreach ($parsedAnswers as $ans) {
            $insertAnswer->execute([
                $responId,
                $pendaftarId,
                $ans['pertanyaan_id'],
                $ans['group_id'],
                $ans['pilihan_jawaban'],
                $ans['skor']
            ]);
        }

        // Perbarui ringkasan di tabel spmb_pendaftar
        self::db()->query("
            UPDATE `spmb_pendaftar` SET
                `status_observasi` = 'Sudah Mengisi',
                `skor_observasi` = ?,
                `kategori_observasi` = ?,
                `tgl_observasi` = NOW()
            WHERE `id` = ?
        ", [$totalSkor, $kategoriHasil, $pendaftarId]);

        return [
            'success'        => true,
            'message'        => 'Alhamdulillah, data observasi awal berhasil disimpan!',
            'respon_id'      => $responId,
            'total_skor'     => $totalSkor,
            'skor_maksimal'  => $skorMaksTotal,
            'persentase'     => $persentase,
            'kategori_hasil' => $kategoriHasil
        ];
    }

    /**
     * Ambil daftar hasil observasi awal calon siswa (Admin list)
     */
    public static function getDaftarHasilObservasiAwal(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['group_id'])) {
            $conditions[] = "p.group_id = ?";
            $params[] = (int)$filters['group_id'];
        }

        if (!empty($filters['jenjang']) && strtoupper(trim($filters['jenjang'])) !== 'ALL') {
            $conditions[] = "p.jenjang = ?";
            $params[] = strtoupper(trim($filters['jenjang']));
        }

        if (!empty($filters['status_observasi'])) {
            if ($filters['status_observasi'] === 'sudah') {
                $conditions[] = "p.status_observasi = 'Sudah Mengisi'";
            } elseif ($filters['status_observasi'] === 'belum') {
                $conditions[] = "(p.status_observasi IS NULL OR p.status_observasi != 'Sudah Mengisi')";
            }
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $conditions[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ? OR p.nisn LIKE ? OR obs.nama_pengisi LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "
            SELECT p.id as pendaftar_id, p.id, p.nomor_pendaftaran, p.nama_lengkap, p.nama_panggilan,
                   p.jenjang, p.jenis_pendaftaran, p.no_wa, p.no_hp, p.no_hp_ayah, p.no_hp_ibu,
                   p.observasi_token, p.biodata_token, p.status_observasi, p.skor_observasi, p.kategori_observasi, p.tgl_observasi,
                   obs.id as respon_id, obs.nama_pengisi, obs.hubungan_pengisi, obs.total_skor, obs.skor_maksimal, obs.persentase,
                   obs.kategori_hasil, obs.submitted_at
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_observasi_respon` obs ON obs.pendaftar_id = p.id
            $whereClause
            ORDER BY 
                p.nomor_pendaftaran ASC, p.id DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $rows = self::db()->findAll($sql, $params);
        foreach ($rows as &$r) {
            if (empty($r['observasi_token'])) {
                $newToken = bin2hex(random_bytes(24));
                self::db()->query("UPDATE `spmb_pendaftar` SET `observasi_token` = ? WHERE `id` = ?", [$newToken, $r['pendaftar_id']]);
                $r['observasi_token'] = $newToken;
            }
            $targetPhone = !empty($r['no_wa']) ? $r['no_wa'] : (!empty($r['no_hp']) ? $r['no_hp'] : (!empty($r['no_hp_ayah']) ? $r['no_hp_ayah'] : (!empty($r['no_hp_ibu']) ? $r['no_hp_ibu'] : '')));
            $r['target_phone'] = $targetPhone;
            $r['public_url'] = self::makeUrl('spmb/observasi/' . $r['observasi_token']);
        }
        unset($r);

        return $rows;
    }

    /**
     * Hitung total pendaftar untuk hasil observasi awal
     */
    public static function countDaftarHasilObservasiAwal(array $filters = []): int
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['group_id'])) {
            $conditions[] = "p.group_id = ?";
            $params[] = (int)$filters['group_id'];
        }

        if (!empty($filters['jenjang']) && strtoupper(trim($filters['jenjang'])) !== 'ALL') {
            $conditions[] = "p.jenjang = ?";
            $params[] = strtoupper(trim($filters['jenjang']));
        }

        if (!empty($filters['status_observasi'])) {
            if ($filters['status_observasi'] === 'sudah') {
                $conditions[] = "p.status_observasi = 'Sudah Mengisi'";
            } elseif ($filters['status_observasi'] === 'belum') {
                $conditions[] = "(p.status_observasi IS NULL OR p.status_observasi != 'Sudah Mengisi')";
            }
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $conditions[] = "(p.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ? OR p.nisn LIKE ? OR obs.nama_pengisi LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "
            SELECT COUNT(p.id) as total
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_observasi_respon` obs ON obs.pendaftar_id = p.id
            $whereClause
        ";

        $res = self::db()->find($sql, $params);
        return (int)($res['total'] ?? 0);
    }

    /**
     * Hitung statistik ringkasan observasi awal
     */
    public static function getObservasiStats(?int $groupId = null): array
    {
        $params = [];
        $conditions = [];
        if (!empty($groupId)) {
            $conditions[] = "p.group_id = ?";
            $params[] = $groupId;
        }
        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "
            SELECT 
                COUNT(p.id) as total_siswa,
                SUM(CASE WHEN p.status_observasi = 'Sudah Mengisi' THEN 1 ELSE 0 END) as total_sudah,
                SUM(CASE WHEN p.status_observasi IS NULL OR p.status_observasi != 'Sudah Mengisi' THEN 1 ELSE 0 END) as total_belum,
                AVG(CASE WHEN obs.persentase > 0 THEN obs.persentase ELSE NULL END) as rata_persentase,
                SUM(CASE WHEN obs.kategori_hasil = 'Sangat Baik / Mandiri' THEN 1 ELSE 0 END) as total_sangat_baik,
                SUM(CASE WHEN obs.kategori_hasil = 'Baik / Berkembang' THEN 1 ELSE 0 END) as total_baik,
                SUM(CASE WHEN obs.kategori_hasil = 'Cukup' THEN 1 ELSE 0 END) as total_cukup,
                SUM(CASE WHEN obs.kategori_hasil = 'Perlu Pendampingan' THEN 1 ELSE 0 END) as total_kurang
            FROM `spmb_pendaftar` p
            LEFT JOIN `spmb_observasi_respon` obs ON obs.pendaftar_id = p.id
            $where
        ";

        $res = self::db()->find($sql, $params);
        $totalSiswa = (int)($res['total_siswa'] ?? 0);
        $sudah = (int)($res['total_sudah'] ?? 0);
        $belum = (int)($res['total_belum'] ?? 0);
        $persen = $totalSiswa > 0 ? round(($sudah / $totalSiswa) * 100, 1) : 0;
        $rataPersen = round((float)($res['rata_persentase'] ?? 0), 1);

        return [
            'total_siswa'       => $totalSiswa,
            'total_sudah'       => $sudah,
            'total_belum'       => $belum,
            'persen_selesai'    => $persen,
            'rata_persentase'   => $rataPersen,
            'total_sangat_baik' => (int)($res['total_sangat_baik'] ?? 0),
            'total_baik'        => (int)($res['total_baik'] ?? 0),
            'total_cukup'       => (int)($res['total_cukup'] ?? 0),
            'total_kurang'      => (int)($res['total_kurang'] ?? 0),
        ];
    }

    /**
     * Ambil rincian jawaban observasi awal calon siswa (untuk modal detail & cetak)
     */
    public static function getObservasiDetail(int $pendaftarId): ?array
    {
        $pendaftar = self::getPendaftarById($pendaftarId);
        if (!$pendaftar) return null;

        $respon = self::db()->find("
            SELECT * FROM `spmb_observasi_respon` WHERE `pendaftar_id` = ? LIMIT 1
        ", [$pendaftarId]);

        $groupBreakdown = [];
        $answers = [];

        if ($respon) {
            $jawabanRows = self::db()->findAll("
                SELECT j.*, p.pertanyaan, p.indikator, p.skor_maksimal, g.nama_group, g.urutan as group_urutan
                FROM `spmb_observasi_jawaban` j
                JOIN `spmb_observasi_pertanyaan` p ON p.id = j.pertanyaan_id
                JOIN `spmb_observasi_group` g ON g.id = j.group_id
                WHERE j.`respon_id` = ?
                ORDER BY g.urutan ASC, p.urutan ASC
            ", [(int)$respon['id']]);

            foreach ($jawabanRows as $j) {
                $gName = $j['nama_group'];
                if (!isset($groupBreakdown[$gName])) {
                    $groupBreakdown[$gName] = [
                        'nama_group' => $gName,
                        'total_skor' => 0,
                        'skor_maks'  => 0,
                        'items'      => []
                    ];
                }
                $groupBreakdown[$gName]['total_skor'] += (int)$j['skor'];
                $groupBreakdown[$gName]['skor_maks']  += (int)$j['skor_maksimal'];
                $groupBreakdown[$gName]['items'][] = $j;
            }

            foreach ($groupBreakdown as &$gb) {
                $gb['persen'] = $gb['skor_maks'] > 0 ? round(($gb['total_skor'] / $gb['skor_maks']) * 100, 1) : 0;
            }
            unset($gb);

            $answers = $jawabanRows;
        }

        return [
            'pendaftar'       => $pendaftar,
            'respon'          => $respon,
            'group_breakdown' => $groupBreakdown,
            'answers'         => $answers
        ];
    }

    /**
     * Kirim link observasi awal ke nomor WhatsApp orang tua
     */
    public static function sendWaLinkObservasi(int $pendaftarId, ?string $customTemplate = null): array
    {
        $pendaftar = self::getPendaftarById($pendaftarId);
        if (!$pendaftar) {
            return ['success' => false, 'message' => 'Data calon siswa tidak ditemukan.'];
        }

        // Pastikan token observasi terisi
        if (empty($pendaftar['observasi_token'])) {
            $newToken = bin2hex(random_bytes(24));
            self::db()->query("UPDATE `spmb_pendaftar` SET `observasi_token` = ? WHERE `id` = ?", [$newToken, $pendaftarId]);
            $pendaftar['observasi_token'] = $newToken;
        }

        $targetPhone = !empty($pendaftar['no_wa']) ? $pendaftar['no_wa'] :
                      (!empty($pendaftar['no_hp']) ? $pendaftar['no_hp'] :
                      (!empty($pendaftar['no_hp_ayah']) ? $pendaftar['no_hp_ayah'] :
                      (!empty($pendaftar['no_hp_ibu']) ? $pendaftar['no_hp_ibu'] : '')));

        if (empty($targetPhone)) {
            return ['success' => false, 'message' => 'Nomor WhatsApp atau No HP orang tua/wali belum diisi pada formulir pendaftaran.'];
        }

        $group = !empty($pendaftar['group_id']) ? self::getGroupById((int)$pendaftar['group_id']) : self::getActiveGroup();
        $tahunAjaran = $group['tahun_ajaran'] ?? ($group['nama_group'] ?? '2026/2027');
        $namaUnit = self::getNamaSekolahByJenjang($pendaftar['jenjang'] ?? 'SD');
        $publicUrl = self::makeUrl('spmb/observasi/' . $pendaftar['observasi_token']);

        $template = !empty(trim($customTemplate ?? '')) ? $customTemplate : self::getDefaultWaObservasiTemplate();

        $replacements = [
            '{nama_siswa}'          => $pendaftar['nama_lengkap'],
            '{nomor_pendaftaran}'   => $pendaftar['nomor_pendaftaran'],
            '{no_daftar}'           => $pendaftar['nomor_pendaftaran'],
            '{nama_sekolah}'        => $namaUnit,
            '{nama_unit}'           => $namaUnit,
            '{jenjang}'             => $pendaftar['jenjang'],
            '{tahun_ajaran}'        => $tahunAjaran,
            '{link_observasi}'      => $publicUrl,
            '{link_file}'           => $publicUrl,
        ];

        $pesan = str_replace(array_keys($replacements), array_values($replacements), $template);

        return WaGateway::send($targetPhone, $pesan, [
            'module'         => 'spmb_observasi',
            'include_footer' => true
        ]);
    }

    /**
     * Template standar pesan WhatsApp Tautan Kuesioner Observasi Awal
     */
    public static function getDefaultWaObservasiTemplate(): string
    {
        return "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\n"
             . "Yth. Bapak/Ibu Orang Tua/Wali dari ananda:\n"
             . "👤 Nama: *{nama_siswa}*\n"
             . "📋 No. Pendaftaran: *{nomor_pendaftaran}*\n"
             . "🏫 Unit: *{nama_sekolah}*\n\n"
             . "Sebagai tahapan awal pengenalan potensi, kemandirian, dan karakter calon murid baru Tahun Ajaran *{tahun_ajaran}*, kami mohon kesediaan Bapak/Ibu untuk mengisi *Kuesioner Observasi Awal* melalui tautan khusus di bawah ini:\n\n"
             . "📝 *Tautan Pengisian Observasi Awal:*\n"
             . "{link_observasi}\n\n"
             . "_(Tautan ini terhubung langsung dengan data pendaftaran ananda dan dapat diisi dengan nyaman melalui smartphone)_\n\n"
             . "Pengisian observasi awal ini sangat bermanfaat bagi tim pendidik dalam mendampingi tumbuh kembang ananda secara optimal sejak hari pertama bersekolah.\n\n"
             . "Terima kasih atas kerja sama dan kepercayaan Bapak/Ibu kepada SIT Bina Insan Palu.\n\n"
             . "Wassalamu'alaikum Warahmatullahi Wabarakatuh.\n"
             . "_Panitia SPMB SIT Bina Insan Palu_";
    }
}



