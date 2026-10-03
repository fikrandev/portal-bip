<?php
/**
 * Model Modul Kelola Sarpras (Sarana & Prasarana)
 * Portal BIP
 */

class SarprasModel
{
    public static function db(): Database
    {
        return Database::getInstance();
    }

    public static function getSetting(string $key, $default = null)
    {
        $db = self::db();
        $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public static function setSetting(string $key, $value): void
    {
        $db = self::db();
        $stmt = $db->query("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($stmt->fetchColumn()) {
            $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        } else {
            $db->query("INSERT INTO settings (setting_key, setting_value, setting_type, setting_group) VALUES (?, ?, 'string', 'sarpras')", [$key, $value]);
        }
    }

    /**
     * Parse foto column value to array of photo paths
     * Supports JSON array string, plain path string, or empty/null
     */
    public static function getFotoList($rawFoto): array
    {
        if (empty($rawFoto)) {
            return [];
        }
        if (is_array($rawFoto)) {
            return array_values(array_filter($rawFoto));
        }
        $trimmed = trim((string)$rawFoto);
        if ($trimmed === '') {
            return [];
        }
        if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded));
            }
        }
        return [$trimmed];
    }

    /**
     * Get primary/first photo from foto column
     */
    public static function getFirstFoto($rawFoto): ?string
    {
        $list = self::getFotoList($rawFoto);
        return !empty($list) ? $list[0] : null;
    }

    // ── STATISTIK KHUSUS MOBILE APP ────────────────────────────────
    public static function getStatistik(): array
    {
        $db = self::db();
        return [
            'total_barang' => (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_barang")['cnt'],
            'total_tanah' => (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_tanah")['cnt'],
            'total_bangunan' => (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_bangunan")['cnt'],
            'total_ruangan' => (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_ruangan")['cnt']
        ];
    }

    // ── STATISTIK & KPI DASHBOARD ──────────────────────────────────
    public static function getDashboardStats(): array
    {
        $db = self::db();

        $totalItem = $db->find("SELECT COUNT(*) as cnt, COALESCE(SUM(jumlah), 0) as total_qty, COALESCE(SUM(jumlah * harga_perolehan), 0) as total_nilai FROM sarpras_barang");
        $kondisi = $db->findAll("SELECT kondisi, COUNT(*) as cnt, COALESCE(SUM(jumlah), 0) as qty FROM sarpras_barang GROUP BY kondisi");
        
        $baik = 0; $rusakRingan = 0; $rusakBerat = 0;
        foreach ($kondisi as $k) {
            if ($k['kondisi'] === 'Baik') $baik = (int)$k['qty'];
            if ($k['kondisi'] === 'Rusak Ringan') $rusakRingan = (int)$k['qty'];
            if ($k['kondisi'] === 'Rusak Berat') $rusakBerat = (int)$k['qty'];
        }

        $totalDipinjam = (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_peminjaman WHERE status = 'Dipinjam'")['cnt'];
        // total pemeliharaan (asumsikan sarpras_maintenance)
        $totalPemeliharaan = (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_maintenance WHERE status IN ('Menunggu', 'Dalam Perbaikan')")['cnt'];
        $totalRuangan = (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_ruangan WHERE is_active = 1")['cnt'];
        $totalKategori = (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_kategori")['cnt'];

        // Per Kategori
        $perKategori = $db->findAll("
            SELECT k.nama_kategori, COUNT(b.id) as item_count, COALESCE(SUM(b.jumlah), 0) as total_qty 
            FROM sarpras_kategori k
            LEFT JOIN sarpras_barang b ON k.id = b.kategori_id
            GROUP BY k.id, k.nama_kategori
            ORDER BY total_qty DESC
        ");

        // Per Unit
        $perUnit = $db->findAll("
            SELECT unit, COUNT(*) as item_count, COALESCE(SUM(jumlah), 0) as total_qty, COALESCE(SUM(jumlah * harga_perolehan), 0) as total_nilai
            FROM sarpras_barang
            GROUP BY unit
            ORDER BY total_qty DESC
        ");

        return [
            'total_item' => (int)($totalItem['cnt'] ?? 0),
            'total_qty' => (int)($totalItem['total_qty'] ?? 0),
            'total_nilai' => (float)($totalItem['total_nilai'] ?? 0),
            'kondisi_baik' => $baik,
            'kondisi_rusak_ringan' => $rusakRingan,
            'kondisi_rusak_berat' => $rusakBerat,
            'total_dipinjam' => $totalDipinjam,
            'total_pemeliharaan' => $totalPemeliharaan,
            'total_ruangan' => $totalRuangan,
            'total_kategori' => $totalKategori,
            'per_kategori' => $perKategori,
            'per_unit' => $perUnit
        ];
    }

    // ── DATA BARANG & ASET ─────────────────────────────────────────
    public static function getBarangList(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $db = self::db();
        $sql = "
            SELECT b.*, k.nama_kategori, k.kode_kategori, r.nama_ruangan, r.kode_ruangan, r.lokasi_gedung
            FROM sarpras_barang b
            LEFT JOIN sarpras_kategori k ON b.kategori_id = k.id
            LEFT JOIN sarpras_ruangan r ON b.ruangan_id = r.id
            WHERE 1=1

        ";
        $params = [];

        if (!empty($filters['unit'])) {
            $sql .= " AND b.unit = ?";
            $params[] = $filters['unit'];
        }
        if (!empty($filters['kategori_id'])) {
            $sql .= " AND b.kategori_id = ?";
            $params[] = (int)$filters['kategori_id'];
        }
        if (!empty($filters['ruangan_id'])) {
            $sql .= " AND b.ruangan_id = ?";
            $params[] = (int)$filters['ruangan_id'];
        }
        if (!empty($filters['kondisi'])) {
            $sql .= " AND b.kondisi = ?";
            $params[] = $filters['kondisi'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (b.nama_barang LIKE ? OR b.kode_barang LIKE ? OR b.merk_model LIKE ? OR b.nomor_seri LIKE ?)";
            $q = '%' . $filters['search'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY b.id DESC LIMIT $limit OFFSET $offset";
        return $db->findAll($sql, $params);
    }

    public static function getBarangCount(array $filters = []): int
    {
        $db = self::db();
        $sql = "SELECT COUNT(*) as cnt FROM sarpras_barang b WHERE 1=1";
        $params = [];

        if (!empty($filters['unit'])) {
            $sql .= " AND b.unit = ?";
            $params[] = $filters['unit'];
        }
        if (!empty($filters['kategori_id'])) {
            $sql .= " AND b.kategori_id = ?";
            $params[] = (int)$filters['kategori_id'];
        }
        if (!empty($filters['ruangan_id'])) {
            $sql .= " AND b.ruangan_id = ?";
            $params[] = (int)$filters['ruangan_id'];
        }
        if (!empty($filters['kondisi'])) {
            $sql .= " AND b.kondisi = ?";
            $params[] = $filters['kondisi'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (b.nama_barang LIKE ? OR b.kode_barang LIKE ? OR b.merk_model LIKE ?)";
            $q = '%' . $filters['search'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $res = $db->find($sql, $params);
        return (int)($res['cnt'] ?? 0);
    }

    public static function getBarangById(int $id): ?array
    {
        $db = self::db();
        return $db->find("
            SELECT b.*, k.nama_kategori, r.nama_ruangan, r.lokasi_gedung
            FROM sarpras_barang b
            LEFT JOIN sarpras_kategori k ON b.kategori_id = k.id
            LEFT JOIN sarpras_ruangan r ON b.ruangan_id = r.id
            WHERE b.id = ?
        ", [$id]);
    }

    public static function insertBarang(array $data): int
    {
        $db = self::db();
        return $db->insert('sarpras_barang', $data);
    }

    public static function updateBarang(int $id, array $data): bool
    {
        $db = self::db();
        return $db->update('sarpras_barang', $data, 'id = ?', [$id]);
    }

    public static function deleteBarang(int $id): bool
    {
        $db = self::db();
        return $db->delete('sarpras_barang', 'id = ?', [$id]);
    }

    public static function generateKodeBarangCustom(int $golonganId, int $kelompokId, int $asalAnggaranId): string
    {
        $db = self::db();
        
        $golongan = $db->find("SELECT kode FROM sarpras_golongan WHERE id = ?", [$golonganId]);
        $kodeGol = $golongan ? $golongan['kode'] : '00';
        
        $kelompok = $db->find("SELECT kode FROM sarpras_kode_kelompok WHERE id = ?", [$kelompokId]);
        $kodeKel = $kelompok ? $kelompok['kode'] : '00';
        
        $anggaran = $db->find("SELECT kode FROM sarpras_asal_anggaran WHERE id = ?", [$asalAnggaranId]);
        $kodeAng = $anggaran ? $anggaran['kode'] : '00';
        
        $prefix = $kodeGol . '.' . $kodeKel . '.' . $kodeAng;
        
        $last = $db->find("SELECT kode_barang FROM sarpras_barang WHERE kode_barang LIKE ? ORDER BY id DESC LIMIT 1", ["{$prefix}.%"]);
        $seq = 1;
        if ($last && preg_match('/\.(\d+)$/', $last['kode_barang'], $m)) {
            $seq = ((int)$m[1]) + 1;
        }

        return sprintf("%s.%03d", $prefix, $seq);
    }

    // ── MASTER RUANGAN ─────────────────────────────────────────────
    public static function getAllRuangan(): array
    {
        $db = self::db();
        return $db->findAll("
            SELECT r.*, b.nama_bangunan, b.kode_bangunan,
                   COUNT(DISTINCT dist.barang_id) as total_barang, COALESCE(SUM(dist.jumlah), 0) as total_qty
            FROM sarpras_ruangan r
            LEFT JOIN sarpras_bangunan b ON r.bangunan_id = b.id
            LEFT JOIN sarpras_distribusi dist ON r.id = dist.ruangan_id
            GROUP BY r.id
            ORDER BY r.unit ASC, r.nama_ruangan ASC
        ");
    }

    public static function getRuanganByBangunanId(int $bangunanId): array
    {
        $db = self::db();
        return $db->findAll("
            SELECT r.*, COUNT(DISTINCT dist.barang_id) as total_barang, COALESCE(SUM(dist.jumlah), 0) as total_qty
            FROM sarpras_ruangan r
            LEFT JOIN sarpras_distribusi dist ON r.id = dist.ruangan_id
            WHERE r.bangunan_id = ?
            GROUP BY r.id
            ORDER BY r.lantai ASC, r.nama_ruangan ASC
        ", [$bangunanId]);
    }

    public static function getRuanganById(int $id): ?array
    {
        return self::db()->find("SELECT * FROM sarpras_ruangan WHERE id = ?", [$id]);
    }

    public static function insertRuangan(array $data): int
    {
        return self::db()->insert('sarpras_ruangan', $data);
    }

    public static function updateRuangan(int $id, array $data): bool
    {
        return self::db()->update('sarpras_ruangan', $data, 'id = ?', [$id]);
    }

    public static function deleteRuangan(int $id): bool
    {
        $db = self::db();
        // Kurangi 'dipakai' dari sarpras_barang untuk tiap distribusi di ruangan ini
        $dist = $db->findAll("SELECT barang_id, jumlah FROM sarpras_distribusi WHERE ruangan_id = ?", [$id]);
        foreach ($dist as $d) {
            $db->query("UPDATE sarpras_barang SET dipakai = GREATEST(0, dipakai - ?) WHERE id = ?", [$d['jumlah'], $d['barang_id']]);
        }
        // Hapus distribusi
        $db->delete('sarpras_distribusi', 'ruangan_id = ?', [$id]);
        return $db->delete('sarpras_ruangan', 'id = ?', [$id]);
    }

    public static function getPegawaiList(): array
    {
        return self::db()->findAll("
            SELECT id, nama, gelar, niy, unit_tugas, jabatan, foto 
            FROM pegawai 
            WHERE is_active = 1 
            ORDER BY nama ASC
        ");
    }

    // ── MASTER KATEGORI ────────────────────────────────────────────
    public static function getAllKategori(): array
    {
        $db = self::db();
        return $db->findAll("
            SELECT k.*, COUNT(b.id) as total_barang, COALESCE(SUM(b.jumlah), 0) as total_qty
            FROM sarpras_kategori k
            LEFT JOIN sarpras_barang b ON k.id = b.kategori_id
            GROUP BY k.id
            ORDER BY k.nama_kategori ASC
        ");
    }

    public static function getKategoriById(int $id): ?array
    {
        return self::db()->find("SELECT * FROM sarpras_kategori WHERE id = ?", [$id]);
    }

    public static function insertKategori(array $data): int
    {
        return self::db()->insert('sarpras_kategori', $data);
    }

    public static function updateKategori(int $id, array $data): bool
    {
        return self::db()->update('sarpras_kategori', $data, 'id = ?', [$id]);
    }

    public static function deleteKategori(int $id): bool
    {
        return self::db()->delete('sarpras_kategori', 'id = ?', [$id]);
    }

    // ── SIRKULASI PEMINJAMAN ───────────────────────────────────────
    public static function getPeminjamanList(?string $status = null): array
    {
        $db = self::db();
        $sql = "
            SELECT p.*, b.nama_barang, b.kode_barang, b.satuan, r.nama_ruangan
            FROM sarpras_peminjaman p
            JOIN sarpras_barang b ON p.barang_id = b.id
            LEFT JOIN sarpras_ruangan r ON b.ruangan_id = r.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($status)) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY p.id DESC";
        return $db->findAll($sql, $params);
    }

    public static function insertPeminjaman(array $data): int
    {
        $db = self::db();
        $pinjamId = $db->insert('sarpras_peminjaman', $data);

        // Update status barang jadi 'Dipinjam'
        $db->update('sarpras_barang', ['status' => 'Dipinjam'], 'id = ?', [$data['barang_id']]);

        return $pinjamId;
    }

    public static function kembalikanBarang(int $id, string $kondisiSesudah, ?string $catatan = null): bool
    {
        $db = self::db();
        $p = $db->find("SELECT * FROM sarpras_peminjaman WHERE id = ?", [$id]);
        if (!$p) return false;

        $db->update('sarpras_peminjaman', [
            'status' => 'Kembali',
            'tanggal_kembali' => date('Y-m-d'),
            'kondisi_sesudah' => $kondisiSesudah,
            'catatan' => $catatan ? ($p['catatan'] . " | " . $catatan) : $p['catatan']
        ], 'id = ?', [$id]);

        // Kembalikan status barang
        $statusBrg = 'Tersedia';
        if ($kondisiSesudah === 'Rusak Berat' || $kondisiSesudah === 'Rusak Ringan') {
            $db->update('sarpras_barang', [
                'status' => 'Dalam Perbaikan',
                'kondisi' => $kondisiSesudah
            ], 'id = ?', [$p['barang_id']]);
        } else {
            $db->update('sarpras_barang', [
                'status' => 'Tersedia',
                'kondisi' => $kondisiSesudah
            ], 'id = ?', [$p['barang_id']]);
        }

        return true;
    }

    // ── PEMELIHARAAN & PERBAIKAN ───────────────────────────────────
    public static function getPemeliharaanList(?string $status = null): array
    {
        $db = self::db();
        $sql = "
            SELECT m.*, b.nama_barang, b.kode_barang, r.nama_ruangan
            FROM sarpras_pemeliharaan m
            LEFT JOIN sarpras_barang b ON m.barang_id = b.id
            LEFT JOIN sarpras_ruangan r ON m.ruangan_id = r.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($status)) {
            $sql .= " AND m.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY m.id DESC";
        return $db->findAll($sql, $params);
    }

    public static function insertPemeliharaan(array $data): int
    {
        $db = self::db();
        $mId = $db->insert('sarpras_pemeliharaan', $data);

        if (!empty($data['barang_id'])) {
            $db->update('sarpras_barang', ['status' => 'Dalam Perbaikan'], 'id = ?', [$data['barang_id']]);
        }

        return $mId;
    }

    public static function updateStatusPemeliharaan(int $id, string $status, ?string $tindakan, float $biaya, ?string $teknisi): bool
    {
        $db = self::db();
        $m = $db->find("SELECT * FROM sarpras_pemeliharaan WHERE id = ?", [$id]);
        if (!$m) return false;

        $updateData = [
            'status' => $status,
            'tindakan_perbaikan' => $tindakan,
            'biaya_realisasi' => $biaya,
            'teknisi_pihak' => $teknisi
        ];

        if ($status === 'Selesai') {
            $updateData['tanggal_selesai'] = date('Y-m-d');
            if (!empty($m['barang_id'])) {
                $db->update('sarpras_barang', [
                    'status' => 'Tersedia',
                    'kondisi' => 'Baik'
                ], 'id = ?', [$m['barang_id']]);
            }
        } elseif ($status === 'Afkir') {
            $updateData['tanggal_selesai'] = date('Y-m-d');
            if (!empty($m['barang_id'])) {
                $db->update('sarpras_barang', [
                    'status' => 'Dihapuskan',
                    'kondisi' => 'Rusak Berat'
                ], 'id = ?', [$m['barang_id']]);
            }
        }

        return $db->update('sarpras_pemeliharaan', $updateData, 'id = ?', [$id]);
    }

    // ── DATA TANAH (ASET TANAH) ────────────────────────────────────
    public static function getTanahList(array $filters = []): array
    {
        $db = self::db();
        $sql = "
            SELECT t.*, 
                   COUNT(b.id) as total_bangunan,
                   COALESCE(SUM(b.luas_bangunan), 0) as total_luas_bangunan
            FROM sarpras_tanah t
            LEFT JOIN sarpras_bangunan b ON t.id = b.tanah_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (t.nama_tanah LIKE ? OR t.kode_tanah LIKE ? OR t.no_sertifikat LIKE ? OR t.alamat_lokasi LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['status_kepemilikan'])) {
            $sql .= " AND t.status_kepemilikan = ?";
            $params[] = $filters['status_kepemilikan'];
        }

        $sql .= " GROUP BY t.id ORDER BY t.id DESC";
        $list = $db->findAll($sql, $params);

        // Decode sertifikat images jika ada
        foreach ($list as &$item) {
            $item['gambar_sertifikat_list'] = [];
            if (!empty($item['gambar_sertifikat'])) {
                $decoded = json_decode($item['gambar_sertifikat'], true);
                if (is_array($decoded)) {
                    $item['gambar_sertifikat_list'] = $decoded;
                } elseif (is_string($item['gambar_sertifikat'])) {
                    $item['gambar_sertifikat_list'] = [$item['gambar_sertifikat']];
                }
            }
        }

        return $list;
    }

    public static function getTanahById(int $id): ?array
    {
        $db = self::db();
        $tanah = $db->find("
            SELECT t.*, 
                   COUNT(b.id) as total_bangunan,
                   COALESCE(SUM(b.luas_bangunan), 0) as total_luas_bangunan
            FROM sarpras_tanah t
            LEFT JOIN sarpras_bangunan b ON t.id = b.tanah_id
            WHERE t.id = ?
            GROUP BY t.id
        ", [$id]);

        if (!$tanah) return null;

        $tanah['gambar_sertifikat_list'] = [];
        if (!empty($tanah['gambar_sertifikat'])) {
            $decoded = json_decode($tanah['gambar_sertifikat'], true);
            if (is_array($decoded)) {
                $tanah['gambar_sertifikat_list'] = $decoded;
            } elseif (is_string($tanah['gambar_sertifikat'])) {
                $tanah['gambar_sertifikat_list'] = [$tanah['gambar_sertifikat']];
            }
        }

        // Ambil daftar bangunannya sekalian
        $tanah['bangunan_list'] = self::getBangunanByTanahId($id);

        return $tanah;
    }

    public static function generateKodeTanah(): string
    {
        $db = self::db();
        $last = $db->find("SELECT kode_tanah FROM sarpras_tanah ORDER BY id DESC LIMIT 1");
        if ($last && preg_match('/TNH-(\d+)/', $last['kode_tanah'], $matches)) {
            $next = (int)$matches[1] + 1;
        } else {
            $count = (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_tanah")['cnt'];
            $next = $count + 1;
        }
        return 'TNH-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }

    public static function insertTanah(array $data): int
    {
        $db = self::db();
        if (empty($data['kode_tanah'])) {
            $data['kode_tanah'] = self::generateKodeTanah();
        }
        return $db->insert('sarpras_tanah', $data);
    }

    public static function updateTanah(int $id, array $data): bool
    {
        $db = self::db();
        return $db->update('sarpras_tanah', $data, 'id = ?', [$id]);
    }

    public static function deleteTanah(int $id): bool
    {
        $db = self::db();
        // Cari semua bangunan di atas tanah ini
        $bangunanList = $db->findAll("SELECT id FROM sarpras_bangunan WHERE tanah_id = ?", [$id]);
        foreach ($bangunanList as $b) {
            // Hapus ruangan yang ada di bangunan ini menggunakan fungsi deleteBangunan agar cascade
            self::deleteBangunan((int)$b['id']);
        }
        
        return $db->delete('sarpras_tanah', 'id = ?', [$id]);
    }

    // ── DATA BANGUNAN ──────────────────────────────────────────────
    public static function getBangunanList(array $filters = []): array
    {
        $db = self::db();
        $sql = "
            SELECT b.*, t.nama_tanah, t.no_sertifikat, t.kode_tanah,
                   COUNT(r.id) as total_ruangan
            FROM sarpras_bangunan b
            LEFT JOIN sarpras_tanah t ON b.tanah_id = t.id
            LEFT JOIN sarpras_ruangan r ON b.id = r.bangunan_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['tanah_id'])) {
            $sql .= " AND b.tanah_id = ?";
            $params[] = (int)$filters['tanah_id'];
        }

        if (!empty($filters['kondisi_bangunan'])) {
            $sql .= " AND b.kondisi_bangunan = ?";
            $params[] = $filters['kondisi_bangunan'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.nama_bangunan LIKE ? OR b.kode_bangunan LIKE ? OR t.nama_tanah LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $sql .= " GROUP BY b.id ORDER BY b.id DESC";
        return $db->findAll($sql, $params);
    }

    public static function getBangunanByTanahId(int $tanahId): array
    {
        $db = self::db();
        return $db->findAll("
            SELECT b.*, COUNT(r.id) as total_ruangan
            FROM sarpras_bangunan b
            LEFT JOIN sarpras_ruangan r ON b.id = r.bangunan_id
            WHERE b.tanah_id = ?
            GROUP BY b.id
            ORDER BY b.id ASC
        ", [$tanahId]);
    }

    public static function getBangunanById(int $id): ?array
    {
        $db = self::db();
        return $db->find("
            SELECT b.*, t.nama_tanah, t.no_sertifikat, t.kode_tanah
            FROM sarpras_bangunan b
            LEFT JOIN sarpras_tanah t ON b.tanah_id = t.id
            WHERE b.id = ?
        ", [$id]);
    }

    public static function generateKodeBangunan(): string
    {
        $db = self::db();
        $last = $db->find("SELECT kode_bangunan FROM sarpras_bangunan ORDER BY id DESC LIMIT 1");
        if ($last && preg_match('/BGN-(\d+)/', $last['kode_bangunan'], $matches)) {
            $next = (int)$matches[1] + 1;
        } else {
            $count = (int)$db->find("SELECT COUNT(*) as cnt FROM sarpras_bangunan")['cnt'];
            $next = $count + 1;
        }
        return 'BGN-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }

    public static function insertBangunan(array $data): int
    {
        $db = self::db();
        if (empty($data['kode_bangunan'])) {
            $data['kode_bangunan'] = self::generateKodeBangunan();
        }
        return $db->insert('sarpras_bangunan', $data);
    }

    public static function updateBangunan(int $id, array $data): bool
    {
        $db = self::db();
        return $db->update('sarpras_bangunan', $data, 'id = ?', [$id]);
    }

    public static function deleteBangunan(int $id): bool
    {
        $db = self::db();
        // Hapus ruangan yang ada di bangunan ini
        $ruanganList = $db->findAll("SELECT id FROM sarpras_ruangan WHERE bangunan_id = ?", [$id]);
        foreach ($ruanganList as $r) {
            self::deleteRuangan((int)$r['id']);
        }
        return $db->delete('sarpras_bangunan', 'id = ?', [$id]);
    }

    // ── DATA ASET HIERARKI (TANAH -> BANGUNAN -> RUANG) ───────────
    public static function getDataAsetHierarchy(): array
    {
        $tanahList = self::getTanahList();
        $bangunanList = self::getBangunanList();
        $ruanganList = self::getAllRuangan();

        // Group ruangan by bangunan_id
        $ruanganByBangunan = [];
        foreach ($ruanganList as $r) {
            $bId = (int)($r['bangunan_id'] ?? 0);
            $ruanganByBangunan[$bId][] = $r;
        }

        // Group bangunan by tanah_id & attach ruangan
        $bangunanByTanah = [];
        $bangunanTanpaTanah = [];
        foreach ($bangunanList as $b) {
            $b['ruangan_list'] = $ruanganByBangunan[$b['id']] ?? [];
            $tId = (int)($b['tanah_id'] ?? 0);
            if ($tId > 0) {
                $bangunanByTanah[$tId][] = $b;
            } else {
                $bangunanTanpaTanah[] = $b;
            }
        }

        // Attach bangunan to each tanah
        foreach ($tanahList as &$t) {
            $t['bangunan_list'] = $bangunanByTanah[$t['id']] ?? [];
            $totalRuang = 0;
            foreach ($t['bangunan_list'] as $b) {
                $totalRuang += count($b['ruangan_list']);
            }
            $t['total_ruangan'] = $totalRuang;
        }
        unset($t);

        return [
            'tanah_list' => $tanahList,
            'bangunan_tanpa_tanah' => $bangunanTanpaTanah,
            'ruangan_tanpa_bangunan' => $ruanganByBangunan[0] ?? []
        ];
    }

    // ── REFERENSI: DATA GOLONGAN ────────────────────────────────────
    public static function getGolonganList(): array
    {
        $db = self::db();
        return $db->findAll("SELECT * FROM sarpras_golongan ORDER BY kode ASC");
    }

    public static function getGolonganById(int $id): ?array
    {
        $db = self::db();
        return $db->find("SELECT * FROM sarpras_golongan WHERE id = ?", [$id]) ?: null;
    }

    public static function insertGolongan(array $data): int
    {
        return self::db()->insert('sarpras_golongan', $data);
    }

    public static function updateGolongan(int $id, array $data): bool
    {
        return self::db()->update('sarpras_golongan', $data, 'id = ?', [$id]);
    }

    public static function deleteGolongan(int $id): bool
    {
        return self::db()->delete('sarpras_golongan', 'id = ?', [$id]);
    }

    // ── REFERENSI: KODE KELOMPOK ────────────────────────────────────
    public static function getKelompokList(): array
    {
        $db = self::db();
        return $db->findAll("
            SELECT k.*, g.nama_golongan, g.kode as kode_golongan
            FROM sarpras_kode_kelompok k
            LEFT JOIN sarpras_golongan g ON k.golongan_id = g.id
            ORDER BY g.kode ASC, k.kode ASC
        ");
    }

    public static function getKelompokById(int $id): ?array
    {
        $db = self::db();
        return $db->find("SELECT * FROM sarpras_kode_kelompok WHERE id = ?", [$id]) ?: null;
    }

    public static function insertKelompok(array $data): int
    {
        return self::db()->insert('sarpras_kode_kelompok', $data);
    }

    public static function updateKelompok(int $id, array $data): bool
    {
        return self::db()->update('sarpras_kode_kelompok', $data, 'id = ?', [$id]);
    }

    public static function deleteKelompok(int $id): bool
    {
        return self::db()->delete('sarpras_kode_kelompok', 'id = ?', [$id]);
    }

    // ── REFERENSI: ASAL ANGGARAN ────────────────────────────────────
    public static function getAsalAnggaranList(): array
    {
        $db = self::db();
        return $db->findAll("SELECT * FROM sarpras_asal_anggaran ORDER BY kode ASC");
    }

    public static function getAsalAnggaranById(int $id): ?array
    {
        $db = self::db();
        return $db->find("SELECT * FROM sarpras_asal_anggaran WHERE id = ?", [$id]) ?: null;
    }

    public static function insertAsalAnggaran(array $data): int
    {
        return self::db()->insert('sarpras_asal_anggaran', $data);
    }

    public static function updateAsalAnggaran(int $id, array $data): bool
    {
        return self::db()->update('sarpras_asal_anggaran', $data, 'id = ?', [$id]);
    }

    public static function deleteAsalAnggaran(int $id): bool
    {
        return self::db()->delete('sarpras_asal_anggaran', 'id = ?', [$id]);
    }
    // ── MASTER REFERENSI ───────────────────────────────────────────
    public static function getAllGolongan(): array
    {
        return self::db()->findAll("SELECT * FROM sarpras_golongan ORDER BY kode ASC");
    }

    public static function getAllKelompok(): array
    {
        return self::db()->findAll("SELECT * FROM sarpras_kode_kelompok ORDER BY kode ASC");
    }

    public static function getAllAsalAnggaran(): array
    {
        return self::db()->findAll("SELECT * FROM sarpras_asal_anggaran ORDER BY kode ASC");
    }

    public static function getAllSatuan(): array
    {
        return self::db()->findAll("SELECT * FROM sarpras_satuan ORDER BY nama_satuan ASC");
    }

    // ==========================================
    // DISTRIBUSI / PENEMPATAN BARANG
    // ==========================================

    public static function getDistribusiByRuangan(int $ruanganId): array
    {
        return self::db()->findAll("
            SELECT d.*, b.kode_barang, b.nama_barang, b.merk_model, b.satuan, b.kondisi as kondisi_master
            FROM sarpras_distribusi d
            JOIN sarpras_barang b ON d.barang_id = b.id
            WHERE d.ruangan_id = ?
            ORDER BY d.created_at DESC
        ", [$ruanganId]);
    }

    public static function getDistribusiById(int $id): ?array
    {
        return self::db()->find("SELECT * FROM sarpras_distribusi WHERE id = ?", [$id]);
    }

    public static function insertDistribusi(array $data): bool
    {
        $db = self::db();
        $inserted = $db->insert('sarpras_distribusi', $data);
        if ($inserted && isset($data['barang_id']) && isset($data['jumlah'])) {
            $db->query("UPDATE sarpras_barang SET dipakai = dipakai + ? WHERE id = ?", [$data['jumlah'], $data['barang_id']]);
        }
        return $inserted;
    }

    public static function updateDistribusi(int $id, array $data): bool
    {
        return self::db()->update('sarpras_distribusi', $data, 'id = ?', [$id]);
    }

    public static function deleteDistribusi(int $id): bool
    {
        $db = self::db();
        $dist = $db->query("SELECT barang_id, jumlah FROM sarpras_distribusi WHERE id = ?", [$id])->fetch();
        if ($dist) {
            $db->query("UPDATE sarpras_barang SET dipakai = GREATEST(dipakai - ?, 0) WHERE id = ?", [$dist['jumlah'], $dist['barang_id']]);
        }
        return $db->delete('sarpras_distribusi', 'id = ?', [$id]);
    }

    // ==========================================
    // LAPORAN ASET BERKELOMPOK PER RUANGAN
    // ==========================================

    /**
     * Get SQL expression for kondisi from sarpras_distribusi with auto-heal
     */
    public static function getDistribusiKondisiExpr(): string
    {
        static $expr = null;
        if ($expr === null) {
            try {
                $db = self::db();
                $col = $db->query("SHOW COLUMNS FROM `sarpras_distribusi` LIKE 'kondisi'")->fetch();
                if ($col) {
                    $expr = "COALESCE(d.kondisi, b.kondisi)";
                } else {
                    // Auto-heal column immediately
                    try {
                        $db->query("ALTER TABLE `sarpras_distribusi` ADD COLUMN `kondisi` ENUM('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik' AFTER `jumlah`");
                        $expr = "COALESCE(d.kondisi, b.kondisi)";
                    } catch (Throwable $e) {
                        $expr = "b.kondisi";
                    }
                }
            } catch (Throwable $e) {
                $expr = "b.kondisi";
            }
        }
        return $expr;
    }

    public static function getLaporanAsetGrouped(array $filters = []): array
    {
        $db = self::db();
        $ruanganId = $filters['ruangan_id'] ?? null;
        $kategoriId = !empty($filters['kategori_id']) ? (int)$filters['kategori_id'] : null;
        $kondisi = !empty($filters['kondisi']) ? $filters['kondisi'] : null;

        $distKondisiExpr = self::getDistribusiKondisiExpr();

        // 1. Ambil daftar ruangan
        $ruanganQuery = "
            SELECT r.*, b.nama_bangunan, b.kode_bangunan
            FROM sarpras_ruangan r
            LEFT JOIN sarpras_bangunan b ON r.bangunan_id = b.id
            WHERE r.is_active = 1
        ";
        $ruanganParams = [];
        if (!empty($ruanganId) && is_numeric($ruanganId)) {
            $ruanganQuery .= " AND r.id = ?";
            $ruanganParams[] = (int)$ruanganId;
        }
        $ruanganQuery .= " ORDER BY r.unit ASC, r.nama_ruangan ASC";
        $allRuangan = $db->findAll($ruanganQuery, $ruanganParams);

        $resultRuangan = [];
        $grandTotalItems = 0;
        $grandTotalQty = 0;
        $grandTotalNilai = 0.0;
        $grandStatBaik = 0;
        $grandStatRusakRingan = 0;
        $grandStatRusakBerat = 0;

        // Kumpulkan data per ruangan jika bukan filter khusus tanpa ruangan
        if ($ruanganId !== 'tanpa_ruangan') {
            foreach ($allRuangan as $r) {
                $rid = (int)$r['id'];

                // A. Aset dari distribusi ke ruangan ini
                $sqlDist = "
                    SELECT 
                        d.id as distribusi_id,
                        b.id as barang_id,
                        b.kode_barang,
                        b.nama_barang,
                        b.merk_model,
                        b.nomor_seri,
                        d.jumlah,
                        b.satuan,
                        {$distKondisiExpr} as kondisi,
                        b.status,
                        COALESCE(a.nama, b.sumber_dana, '-') as sumber_dana,
                        COALESCE(b.tahun_pengadaan, b.tanggal_perolehan, '-') as tahun_pengadaan,
                        (b.harga_perolehan / GREATEST(b.jumlah, 1)) as harga_satuan,
                        (d.jumlah * (b.harga_perolehan / GREATEST(b.jumlah, 1))) as total_nilai,
                        k.nama_kategori,
                        'distribusi' as asal_data
                    FROM sarpras_distribusi d
                    JOIN sarpras_barang b ON d.barang_id = b.id
                    LEFT JOIN sarpras_kategori k ON b.kategori_id = k.id
                    LEFT JOIN sarpras_asal_anggaran a ON b.asal_anggaran_id = a.id
                    WHERE d.ruangan_id = ?
                ";
                $distParams = [$rid];
                if ($kategoriId) {
                    $sqlDist .= " AND b.kategori_id = ?";
                    $distParams[] = $kategoriId;
                }
                if ($kondisi) {
                    $sqlDist .= " AND {$distKondisiExpr} = ?";
                    $distParams[] = $kondisi;
                }
                $distItems = $db->findAll($sqlDist, $distParams);

                // B. Aset yang langsung diset ruangan_id pada sarpras_barang tapi belum didistribusikan lewat sarpras_distribusi
                $sqlBrg = "
                    SELECT 
                        NULL as distribusi_id,
                        b.id as barang_id,
                        b.kode_barang,
                        b.nama_barang,
                        b.merk_model,
                        b.nomor_seri,
                        b.jumlah,
                        b.satuan,
                        b.kondisi,
                        b.status,
                        COALESCE(a.nama, b.sumber_dana, '-') as sumber_dana,
                        COALESCE(b.tahun_pengadaan, b.tanggal_perolehan, '-') as tahun_pengadaan,
                        (b.harga_perolehan / GREATEST(b.jumlah, 1)) as harga_satuan,
                        b.harga_perolehan as total_nilai,
                        k.nama_kategori,
                        'barang_master' as asal_data
                    FROM sarpras_barang b
                    LEFT JOIN sarpras_kategori k ON b.kategori_id = k.id
                    LEFT JOIN sarpras_asal_anggaran a ON b.asal_anggaran_id = a.id
                    WHERE b.ruangan_id = ?
                      AND b.id NOT IN (SELECT DISTINCT barang_id FROM sarpras_distribusi WHERE ruangan_id = ?)
                ";
                $brgParams = [$rid, $rid];
                if ($kategoriId) {
                    $sqlBrg .= " AND b.kategori_id = ?";
                    $brgParams[] = $kategoriId;
                }
                if ($kondisi) {
                    $sqlBrg .= " AND b.kondisi = ?";
                    $brgParams[] = $kondisi;
                }
                $brgItems = $db->findAll($sqlBrg, $brgParams);

                $roomItems = array_merge($distItems, $brgItems);

                $rTotItem = count($roomItems);
                $rTotQty = 0;
                $rTotNilai = 0.0;
                $rBaik = 0;
                $rRusakRingan = 0;
                $rRusakBerat = 0;

                foreach ($roomItems as $it) {
                    $qty = (int)$it['jumlah'];
                    $rTotQty += $qty;
                    $rTotNilai += (float)$it['total_nilai'];
                    $kd = strtolower(trim((string)$it['kondisi']));
                    if ($kd === 'baik') $rBaik += $qty;
                    elseif (str_contains($kd, 'ringan')) $rRusakRingan += $qty;
                    elseif (str_contains($kd, 'berat')) $rRusakBerat += $qty;
                    else $rBaik += $qty;
                }

                $grandTotalItems += $rTotItem;
                $grandTotalQty += $rTotQty;
                $grandTotalNilai += $rTotNilai;
                $grandStatBaik += $rBaik;
                $grandStatRusakRingan += $rRusakRingan;
                $grandStatRusakBerat += $rRusakBerat;

                if ($rTotItem > 0 || (!empty($ruanganId) && is_numeric($ruanganId))) {
                    $resultRuangan[] = [
                        'ruangan' => $r,
                        'items' => $roomItems,
                        'total_item' => $rTotItem,
                        'total_qty' => $rTotQty,
                        'total_nilai' => $rTotNilai,
                        'stat_baik' => $rBaik,
                        'stat_rusak_ringan' => $rRusakRingan,
                        'stat_rusak_berat' => $rRusakBerat,
                    ];
                }
            }
        }

        // 2. Kumpulkan Aset Tanpa Ruangan / Belum Ditempatkan
        $unassignedItems = [];
        $sqlNoRoom = "
            SELECT 
                NULL as distribusi_id,
                b.id as barang_id,
                b.kode_barang,
                b.nama_barang,
                b.merk_model,
                b.nomor_seri,
                (b.jumlah - COALESCE((SELECT SUM(jumlah) FROM sarpras_distribusi WHERE barang_id = b.id), 0)) as jumlah,
                b.satuan,
                b.kondisi,
                b.status,
                COALESCE(a.nama, b.sumber_dana, '-') as sumber_dana,
                COALESCE(b.tahun_pengadaan, b.tanggal_perolehan, '-') as tahun_pengadaan,
                (b.harga_perolehan / GREATEST(b.jumlah, 1)) as harga_satuan,
                ((b.jumlah - COALESCE((SELECT SUM(jumlah) FROM sarpras_distribusi WHERE barang_id = b.id), 0)) * (b.harga_perolehan / GREATEST(b.jumlah, 1))) as total_nilai,
                k.nama_kategori,
                'tanpa_ruangan' as asal_data
            FROM sarpras_barang b
            LEFT JOIN sarpras_kategori k ON b.kategori_id = k.id
            LEFT JOIN sarpras_asal_anggaran a ON b.asal_anggaran_id = a.id
            WHERE (
                b.ruangan_id IS NULL 
                OR b.ruangan_id = 0 
                OR b.ruangan_id NOT IN (SELECT id FROM sarpras_ruangan)
                OR (b.jumlah > COALESCE((SELECT SUM(jumlah) FROM sarpras_distribusi WHERE barang_id = b.id), 0) AND b.id IN (SELECT DISTINCT barang_id FROM sarpras_distribusi))
            )
            AND (b.jumlah - COALESCE((SELECT SUM(jumlah) FROM sarpras_distribusi WHERE barang_id = b.id), 0)) > 0
        ";
        $noRoomParams = [];
        if ($kategoriId) {
            $sqlNoRoom .= " AND b.kategori_id = ?";
            $noRoomParams[] = $kategoriId;
        }
        if ($kondisi) {
            $sqlNoRoom .= " AND b.kondisi = ?";
            $noRoomParams[] = $kondisi;
        }
        $unassignedItems = $db->findAll($sqlNoRoom, $noRoomParams);

        $uTotItem = count($unassignedItems);
        $uTotQty = 0;
        $uTotNilai = 0.0;
        $uBaik = 0;
        $uRusakRingan = 0;
        $uRusakBerat = 0;

        foreach ($unassignedItems as $it) {
            $qty = (int)$it['jumlah'];
            $uTotQty += $qty;
            $uTotNilai += (float)$it['total_nilai'];
            $kd = strtolower(trim((string)$it['kondisi']));
            if ($kd === 'baik') $uBaik += $qty;
            elseif (str_contains($kd, 'ringan')) $uRusakRingan += $qty;
            elseif (str_contains($kd, 'berat')) $uRusakBerat += $qty;
            else $uBaik += $qty;
        }

        if (empty($ruanganId) || $ruanganId === 'tanpa_ruangan') {
            $grandTotalItems += $uTotItem;
            $grandTotalQty += $uTotQty;
            $grandTotalNilai += $uTotNilai;
            $grandStatBaik += $uBaik;
            $grandStatRusakRingan += $uRusakRingan;
            $grandStatRusakBerat += $uRusakBerat;
        }

        return [
            'ruangan_list' => $resultRuangan,
            'unassigned' => [
                'ruangan' => [
                    'id' => 0,
                    'nama_ruangan' => 'Belum Ditempatkan / Tanpa Ruangan',
                    'kode_ruangan' => 'NON-ROOM',
                    'lokasi_gedung' => 'Gudang Inventaris Cadangan',
                    'unit' => 'Semua',
                    'penanggung_jawab' => 'Petugas Gudang Sarpras'
                ],
                'items' => $unassignedItems,
                'total_item' => $uTotItem,
                'total_qty' => $uTotQty,
                'total_nilai' => $uTotNilai,
                'stat_baik' => $uBaik,
                'stat_rusak_ringan' => $uRusakRingan,
                'stat_rusak_berat' => $uRusakBerat
            ],
            'rekap' => [
                'grand_total_items' => $grandTotalItems,
                'grand_total_qty' => $grandTotalQty,
                'grand_total_nilai' => $grandTotalNilai,
                'grand_stat_baik' => $grandStatBaik,
                'grand_stat_rusak_ringan' => $grandStatRusakRingan,
                'grand_stat_rusak_berat' => $grandStatRusakBerat,
                'total_active_ruangan' => count($resultRuangan)
            ]
        ];
    }

    /**
     * Dapatkan daftar kelas per jenjang / unit dari data kelola siswa
     */
    public static function getKelasByUnit(): array
    {
        $db = self::db();
        $result = [
            'SD' => [],
            'SMP' => [],
            'SMA' => [],
            'PAUD' => [],
            'Yayasan' => [],
            'Semua' => []
        ];

        try {
            // Ambil dari data siswa aktif
            $siswaClasses = $db->findAll("
                SELECT DISTINCT UPPER(TRIM(jenjang)) as unit_val, TRIM(kelas) as nama_kelas
                FROM siswa
                WHERE kelas IS NOT NULL AND TRIM(kelas) != ''
                ORDER BY unit_val ASC, nama_kelas ASC
            ");
            foreach ($siswaClasses as $sc) {
                $u = strtoupper(trim($sc['unit_val'] ?? ''));
                $k = trim($sc['nama_kelas'] ?? '');
                if (!empty($u) && !empty($k)) {
                    if (!isset($result[$u])) $result[$u] = [];
                    if (!in_array($k, $result[$u], true)) {
                        $result[$u][] = $k;
                    }
                }
            }
        } catch (Throwable $e) {}

        // Ambil juga dari master kelas jika ada
        try {
            $masterClasses = $db->findAll("
                SELECT DISTINCT TRIM(nama_kelas) as nama_kelas
                FROM kelas
                WHERE nama_kelas IS NOT NULL AND TRIM(nama_kelas) != '' AND is_active = 1
                ORDER BY nama_kelas ASC
            ");
            foreach ($masterClasses as $mc) {
                $k = trim($mc['nama_kelas'] ?? '');
                if (!empty($k)) {
                    $found = false;
                    foreach ($result as $u => $list) {
                        if (in_array($k, $list, true)) { $found = true; break; }
                    }
                    if (!$found) {
                        $firstChar = substr($k, 0, 1);
                        if (in_array($firstChar, ['1','2','3','4','5','6'])) {
                            $result['SD'][] = $k;
                        } elseif (in_array($firstChar, ['7','8','9'])) {
                            $result['SMP'][] = $k;
                        } elseif (in_array(substr($k, 0, 2), ['10','11','12'])) {
                            $result['SMA'][] = $k;
                        } elseif (stripos($k, 'TK') !== false || stripos($k, 'PAUD') !== false) {
                            $result['PAUD'][] = $k;
                        } else {
                            $result['SD'][] = $k;
                        }
                    }
                }
            }
        } catch (Throwable $e) {}

        // Natsort untuk setiap unit agar urutan kelas alami (1, 2, 3... 10, 11)
        foreach ($result as $u => &$list) {
            natsort($list);
            $list = array_values($list);
        }

        return $result;
    }
}


