<?php

class NilaiController
{
    public static function index(): void
    {
        $pageTitle = 'Dashboard Kelola Nilai';
        $breadcrumbs = [['label' => 'Kelola Nilai']];
        
        $db = \Database::getInstance();
        
        // ── KPI Summary Cards ──
        $totalGroups = (int)$db->query("SELECT COUNT(*) FROM nilai_group WHERE deleted_at IS NULL")->fetchColumn();
        $totalActiveGroups = (int)$db->query("SELECT COUNT(*) FROM nilai_group WHERE deleted_at IS NULL AND is_active = 1")->fetchColumn();
        $totalInactiveGroups = (int)$db->query("SELECT COUNT(*) FROM nilai_group WHERE deleted_at IS NULL AND is_active = 0")->fetchColumn();
        $totalNilai = (int)$db->query("SELECT COUNT(*) FROM nilai_detail")->fetchColumn();
        $totalSiswaDinilai = (int)$db->query("SELECT COUNT(DISTINCT siswa_id) FROM nilai_detail WHERE nilai > 0")->fetchColumn();
        $avgNilai = (float)$db->query("SELECT COALESCE(ROUND(AVG(nilai), 1), 0) FROM nilai_detail WHERE nilai > 0")->fetchColumn();
        $totalCpatpDocs = (int)$db->query("SELECT COUNT(*) FROM perangkat_pembelajaran WHERE tipe = 'cpatp' AND is_active = 1")->fetchColumn();

        // ── Chart 1: Rata-rata Nilai per Mata Pelajaran ──
        $mapelChartRaw = $db->query("
            SELECT pp.mata_pelajaran, 
                   ROUND(AVG(nd.nilai), 1) as avg_score, 
                   COUNT(nd.id) as total_entries
            FROM nilai_detail nd
            JOIN perangkat_pembelajaran pp ON FLOOR(nd.cpatp_row_index / 100000) = pp.id
            WHERE nd.nilai > 0 AND pp.mata_pelajaran IS NOT NULL AND pp.mata_pelajaran != ''
            GROUP BY pp.mata_pelajaran
            ORDER BY avg_score DESC
            LIMIT 8
        ")->fetchAll();

        $mapelLabels = [];
        $mapelScores = [];
        foreach ($mapelChartRaw as $mc) {
            $mapelLabels[] = $mc['mata_pelajaran'];
            $mapelScores[] = (float)$mc['avg_score'];
        }

        // ── Chart 2: Distribusi Predikat (A, B, C, D) ──
        $predikatCounts = [
            'A' => 0, // >= 85 (Sangat Baik)
            'B' => 0, // 75 - 84 (Baik)
            'C' => 0, // 65 - 74 (Cukup)
            'D' => 0  // < 65 (Perlu Bimbingan)
        ];
        $allScores = $db->query("SELECT nilai FROM nilai_detail WHERE nilai > 0")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($allScores as $sc) {
            $sc = (float)$sc;
            if ($sc >= 85) $predikatCounts['A']++;
            elseif ($sc >= 75) $predikatCounts['B']++;
            elseif ($sc >= 65) $predikatCounts['C']++;
            else $predikatCounts['D']++;
        }

        // ── Chart 3: Progress Penginputan per Unit ──
        $unitProgress = $db->query("
            SELECT g.unit, 
                   COUNT(DISTINCT g.id) as total_group,
                   COUNT(nd.id) as total_nilai
            FROM nilai_group g
            LEFT JOIN nilai_detail nd ON g.id = nd.group_id
            WHERE g.deleted_at IS NULL
            GROUP BY g.unit
        ")->fetchAll();

        // ── Top 5 Siswa dengan Nilai Rerata Tertinggi ──
        $topSiswa = $db->query("
            SELECT s.id, s.nis, s.nama, s.kelas, s.jenjang,
                   ROUND(AVG(nd.nilai), 1) as rerata_nilai,
                   COUNT(nd.id) as total_asesmen
            FROM nilai_detail nd
            JOIN siswa s ON nd.siswa_id = s.id
            WHERE nd.nilai > 0
            GROUP BY s.id
            ORDER BY rerata_nilai DESC, total_asesmen DESC
            LIMIT 5
        ")->fetchAll();

        // ── Wadah Nilai Aktif Terbaru ──
        $recentGroups = $db->query("
            SELECT g.*, ta.nama_tahun,
                   (SELECT COUNT(*) FROM nilai_detail nd WHERE nd.group_id = g.id) as total_input,
                   (SELECT COUNT(DISTINCT siswa_id) FROM nilai_detail nd WHERE nd.group_id = g.id) as total_siswa
            FROM nilai_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            WHERE g.deleted_at IS NULL
            ORDER BY g.id DESC
            LIMIT 5
        ")->fetchAll();
        
        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function groupList(): void
    {
        $pageTitle = 'Daftar Grup Nilai';
        $breadcrumbs = [
            ['label' => 'Kelola Nilai', 'url' => url('kelola-nilai')],
            ['label' => 'Grup Nilai']
        ];
        
        $db = \Database::getInstance();
        $filter_status = $_GET['status'] ?? '';
        $filter_unit = $_GET['unit'] ?? '';

        $sql = "
            SELECT g.*, 
                   k.nama_kelas, 
                   ta.nama_tahun,
                   pp.judul as cpatp_judul,
                   (SELECT COUNT(*) FROM nilai_detail nd WHERE nd.group_id = g.id) as total_input
            FROM nilai_group g
            LEFT JOIN kelas k ON g.kelas_id = k.id
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            LEFT JOIN perangkat_pembelajaran pp ON g.cpatp_id = pp.id
            WHERE g.deleted_at IS NULL
        ";
        $params = [];

        if ($filter_status === 'aktif') {
            $sql .= " AND g.is_active = 1";
        } elseif ($filter_status === 'nonaktif') {
            $sql .= " AND g.is_active = 0";
        }

        if (!empty($filter_unit)) {
            $sql .= " AND g.unit = ?";
            $params[] = $filter_unit;
        }

        $sql .= " ORDER BY g.id DESC";

        $groups = $db->query($sql, $params)->fetchAll();

        // Counts for tab filters
        $totalAll = (int)$db->query("SELECT COUNT(*) FROM nilai_group WHERE deleted_at IS NULL")->fetchColumn();
        $totalActive = (int)$db->query("SELECT COUNT(*) FROM nilai_group WHERE deleted_at IS NULL AND is_active = 1")->fetchColumn();
        $totalInactive = (int)$db->query("SELECT COUNT(*) FROM nilai_group WHERE deleted_at IS NULL AND is_active = 0")->fetchColumn();
        
        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/group_list.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function groupCreate(): void
    {
        $pageTitle = 'Buat Grup Nilai';
        $breadcrumbs = [
            ['label' => 'Kelola Nilai', 'url' => url('kelola-nilai')],
            ['label' => 'Grup Nilai', 'url' => url('kelola-nilai/group')],
            ['label' => 'Buat Baru']
        ];
        
        $db = \Database::getInstance();
        
        $unit_list = [
            'PAUD' => [
                'name' => 'PAUD / TK',
                'bg_soft' => 'bg-pink-100 text-pink-700',
                'icon' => '🧸'
            ],
            'SD' => [
                'name' => 'SD (Sekolah Dasar)',
                'bg_soft' => 'bg-emerald-100 text-emerald-700',
                'icon' => '🎒'
            ],
            'SMP' => [
                'name' => 'SMP (Sekolah Menengah Pertama)',
                'bg_soft' => 'bg-blue-100 text-blue-700',
                'icon' => '📚'
            ],
            'SMA' => [
                'name' => 'SMA / SMK',
                'bg_soft' => 'bg-purple-100 text-purple-700',
                'icon' => '🎓'
            ]
        ];
        
        // Ambil semua CP ATP group yang aktif beserta info tahun ajaran
        $cpatp_groups = $db->query("
            SELECT p.id, p.judul, p.unit, p.tahun_akademik_id, p.semester, 
                   p.mata_pelajaran, p.tingkat_kelas, p.guru_id, p.guru_nama,
                   ta.nama_tahun
            FROM perangkat_pembelajaran p
            LEFT JOIN tahun_akademik ta ON p.tahun_akademik_id = ta.id
            WHERE p.tipe = 'cpatp_group' AND p.is_active = 1 
            ORDER BY p.id DESC
        ")->fetchAll();
        
        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/group_create.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function groupStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect(url('kelola-nilai/group/create'));
            return;
        }
        
        if (!CSRF::validate()) {
            Response::withError(url('kelola-nilai/group/create'), 'Token keamanan tidak valid. Silakan ulangi kembali.');
            return;
        }
        $db = \Database::getInstance();
        
        $judul = trim($_POST['judul'] ?? '');
        $unit = $_POST['unit'] ?? '';
        $cpatp_id = !empty($_POST['cpatp_id']) ? (int)$_POST['cpatp_id'] : null;
        
        if (empty($judul) || empty($unit) || empty($cpatp_id)) {
            Response::withError(url('kelola-nilai/group/create'), 'Nama grup, unit, dan CP ATP wajib diisi!');
            return;
        }
        
        // Ambil data dari CP ATP yang dipilih
        $cpatp = $db->find("SELECT * FROM perangkat_pembelajaran WHERE id = ? AND tipe = 'cpatp_group'", [$cpatp_id]);
        if (!$cpatp) {
            Response::withError(url('kelola-nilai/group/create'), 'CP ATP yang dipilih tidak ditemukan!');
            return;
        }
        
        // Auto-fill dari CP ATP
        $tahun_akademik_id = $cpatp['tahun_akademik_id'];
        $semester = $cpatp['semester'];
        $mata_pelajaran = $cpatp['mata_pelajaran'] ?? '';
        $guru_id = $cpatp['guru_id'] ?? null;
        $tanggal = date('Y-m-d');
        
        $db->query("
            INSERT INTO nilai_group 
            (unit, tahun_akademik_id, semester, kelas_id, mata_pelajaran, jenis_penilaian, cpatp_id, guru_id, judul, tanggal, is_active)
            VALUES (?, ?, ?, 0, ?, '', ?, ?, ?, ?, 1)
        ", [
            $unit, $tahun_akademik_id, $semester, $mata_pelajaran, $cpatp_id, $guru_id, $judul, $tanggal
        ]);
        
        $groupId = $db->lastInsertId();
        
        Response::withSuccess(url('kelola-nilai/input/' . $groupId), 'Grup Nilai berhasil dibuat!');
    }

    /**
     * Tampilkan form edit wadah grup nilai
     */
    public static function groupEdit($id): void
    {
        $pageTitle = 'Edit Wadah Grup Nilai';
        $breadcrumbs = [
            ['label' => 'Kelola Nilai', 'url' => url('kelola-nilai')],
            ['label' => 'Grup Nilai', 'url' => url('kelola-nilai/group')],
            ['label' => 'Edit Wadah']
        ];
        
        $db = \Database::getInstance();
        $group = $db->find("
            SELECT g.*, ta.nama_tahun, pp.judul as cpatp_judul
            FROM nilai_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            LEFT JOIN perangkat_pembelajaran pp ON g.cpatp_id = pp.id
            WHERE g.id = ? AND g.deleted_at IS NULL
        ", [$id]);

        if (!$group) {
            Response::withError(url('kelola-nilai/group'), 'Wadah Grup Nilai tidak ditemukan.');
            return;
        }
        
        $unit_list = [
            'PAUD' => [
                'name' => 'PAUD / TK',
                'bg_soft' => 'bg-pink-100 text-pink-700',
                'icon' => '🧸'
            ],
            'SD' => [
                'name' => 'SD (Sekolah Dasar)',
                'bg_soft' => 'bg-emerald-100 text-emerald-700',
                'icon' => '🎒'
            ],
            'SMP' => [
                'name' => 'SMP (Sekolah Menengah Pertama)',
                'bg_soft' => 'bg-blue-100 text-blue-700',
                'icon' => '📚'
            ],
            'SMA' => [
                'name' => 'SMA / SMK',
                'bg_soft' => 'bg-purple-100 text-purple-700',
                'icon' => '🎓'
            ]
        ];
        
        // Ambil semua CP ATP group yang aktif beserta info tahun ajaran
        $cpatp_groups = $db->query("
            SELECT p.id, p.judul, p.unit, p.tahun_akademik_id, p.semester, 
                   p.mata_pelajaran, p.tingkat_kelas, p.guru_id, p.guru_nama,
                   ta.nama_tahun
            FROM perangkat_pembelajaran p
            LEFT JOIN tahun_akademik ta ON p.tahun_akademik_id = ta.id
            WHERE p.tipe = 'cpatp_group' AND p.is_active = 1 
            ORDER BY p.id DESC
        ")->fetchAll();
        
        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/group_edit.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan perubahan wadah grup nilai
     */
    public static function groupUpdate($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect(url('kelola-nilai/group/edit/' . $id));
            return;
        }
        
        if (!CSRF::validate()) {
            Response::withError(url('kelola-nilai/group/edit/' . $id), 'Token keamanan tidak valid. Silakan ulangi kembali.');
            return;
        }

        $db = \Database::getInstance();
        $group = $db->find("SELECT * FROM nilai_group WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$group) {
            Response::withError(url('kelola-nilai/group'), 'Wadah Grup Nilai tidak ditemukan.');
            return;
        }
        
        $judul = trim($_POST['judul'] ?? '');
        $unit = $_POST['unit'] ?? '';
        $cpatp_id = !empty($_POST['cpatp_id']) ? (int)$_POST['cpatp_id'] : null;
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        
        if (empty($judul) || empty($unit) || empty($cpatp_id)) {
            Response::withError(url('kelola-nilai/group/edit/' . $id), 'Nama grup, unit, dan CP ATP wajib diisi!');
            return;
        }
        
        // Ambil data dari CP ATP yang dipilih
        $cpatp = $db->find("SELECT * FROM perangkat_pembelajaran WHERE id = ? AND tipe = 'cpatp_group'", [$cpatp_id]);
        if (!$cpatp) {
            Response::withError(url('kelola-nilai/group/edit/' . $id), 'CP ATP yang dipilih tidak ditemukan!');
            return;
        }
        
        // Auto-fill dari CP ATP
        $tahun_akademik_id = $cpatp['tahun_akademik_id'];
        $semester = $cpatp['semester'];
        $mata_pelajaran = $cpatp['mata_pelajaran'] ?? '';
        $guru_id = $cpatp['guru_id'] ?? null;
        
        $db->query("
            UPDATE nilai_group 
            SET judul = ?,
                unit = ?,
                tahun_akademik_id = ?,
                semester = ?,
                mata_pelajaran = ?,
                cpatp_id = ?,
                guru_id = ?,
                is_active = ?,
                updated_at = NOW()
            WHERE id = ?
        ", [
            $judul, $unit, $tahun_akademik_id, $semester, $mata_pelajaran, $cpatp_id, $guru_id, $is_active, $id
        ]);
        
        Response::withSuccess(url('kelola-nilai/group'), 'Wadah Grup Nilai berhasil diperbarui!');
    }

    /**
     * Nonaktifkan atau Aktifkan kembali wadah grup nilai
     */
    public static function groupToggleStatus($id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-nilai/group'), 'Token keamanan tidak valid.');
            return;
        }

        $db = \Database::getInstance();
        $group = $db->find("SELECT id, judul, is_active FROM nilai_group WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$group) {
            Response::withError(url('kelola-nilai/group'), 'Wadah Grup Nilai tidak ditemukan.');
            return;
        }

        $newStatus = $group['is_active'] ? 0 : 1;
        $db->query("UPDATE nilai_group SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);

        $msg = $newStatus 
            ? "Wadah Nilai \"{$group['judul']}\" berhasil diaktifkan kembali." 
            : "Wadah Nilai \"{$group['judul']}\" berhasil dinonaktifkan.";

        Response::withSuccess(url('kelola-nilai/group'), $msg);
    }

    /**
     * Hapus wadah grup nilai (soft delete)
     */
    public static function groupDelete($id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-nilai/group'), 'Token keamanan tidak valid.');
            return;
        }

        $db = \Database::getInstance();
        $db->query("UPDATE nilai_group SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?", [$id]);

        Response::withSuccess(url('kelola-nilai/group'), 'Wadah Grup Nilai berhasil dihapus.');
    }

    /**
     * Halaman daftar guru di dalam grup nilai
     * Mengambil guru-guru dari dokumen CP-ATP yang terkait
     */
    public static function inputNilai($groupId): void
    {
        $db = \Database::getInstance();
        $group = $db->find("
            SELECT g.*, ta.nama_tahun, k.nama_kelas
            FROM nilai_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            LEFT JOIN kelas k ON g.kelas_id = k.id
            WHERE g.id = ? AND g.deleted_at IS NULL
        ", [$groupId]);
        
        if (!$group) {
            Response::withError(url('kelola-nilai/group'), 'Grup Nilai tidak ditemukan');
            return;
        }

        // Ambil CP-ATP group info
        $cpatpGroup = null;
        if (!empty($group['cpatp_id'])) {
            $cpatpGroup = $db->find("SELECT * FROM perangkat_pembelajaran WHERE id = ? AND tipe = 'cpatp_group'", [$group['cpatp_id']]);
        }
        
        // Ambil semua dokumen CP-ATP (tipe 'cpatp') yang terkait grup ini
        // Matching berdasarkan unit, tahun_akademik_id, semester dari cpatp_group
        $cpatpDocs = [];
        if ($cpatpGroup) {
            $cpatpDocs = $db->findAll("
                SELECT p.id, p.guru_id, p.guru_nama, p.mata_pelajaran, p.tingkat_kelas, p.judul, p.konten_json,
                       p.fase, p.status
                FROM perangkat_pembelajaran p
                WHERE p.tipe = 'cpatp'
                  AND p.unit = ?
                  AND p.tahun_akademik_id = ?
                  AND p.semester = ?
                  AND p.is_active = 1
                ORDER BY p.guru_nama ASC, p.mata_pelajaran ASC
            ", [$cpatpGroup['unit'], $cpatpGroup['tahun_akademik_id'], $cpatpGroup['semester']]);
        }

        // Group by guru
        $guruMap = [];
        foreach ($cpatpDocs as $doc) {
            $gId = (int)($doc['guru_id'] ?? 0);
            if ($gId === 0) continue;
            
            if (!isset($guruMap[$gId])) {
                $guruMap[$gId] = [
                    'guru_id' => $gId,
                    'guru_nama' => $doc['guru_nama'] ?? 'Tidak diketahui',
                    'mapel_set' => [],
                    'kelas_set' => [],
                    'doc_count' => 0,
                ];
            }
            
            $guruMap[$gId]['doc_count']++;
            if (!empty($doc['mata_pelajaran'])) {
                $guruMap[$gId]['mapel_set'][$doc['mata_pelajaran']] = true;
            }
            if (!empty($doc['tingkat_kelas'])) {
                $guruMap[$gId]['kelas_set'][$doc['tingkat_kelas']] = true;
            }
        }

        // Convert sets to counts
        foreach ($guruMap as &$guru) {
            $guru['total_mapel'] = count($guru['mapel_set']);
            $guru['total_kelas'] = count($guru['kelas_set']);
            unset($guru['mapel_set'], $guru['kelas_set']);
        }
        unset($guru);

        $pageTitle = 'Input Nilai: ' . $group['judul'];
        $breadcrumbs = [
            ['label' => 'Kelola Nilai', 'url' => url('kelola-nilai')],
            ['label' => 'Grup Nilai', 'url' => url('kelola-nilai/group')],
            ['label' => 'Guru Pengampu']
        ];
        
        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/group_guru_list.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Detail guru: mapel + kelas yang diajar
     */
    public static function guruDetail($groupId, $guruId): void
    {
        $db = \Database::getInstance();
        $group = $db->find("
            SELECT g.*, ta.nama_tahun
            FROM nilai_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            WHERE g.id = ? AND g.deleted_at IS NULL
        ", [$groupId]);
        
        if (!$group) {
            Response::withError(url('kelola-nilai/group'), 'Grup Nilai tidak ditemukan');
            return;
        }

        $cpatpGroup = null;
        if (!empty($group['cpatp_id'])) {
            $cpatpGroup = $db->find("SELECT * FROM perangkat_pembelajaran WHERE id = ? AND tipe = 'cpatp_group'", [$group['cpatp_id']]);
        }

        // Ambil dokumen CP-ATP milik guru ini
        $docs = [];
        $guruNama = '';
        if ($cpatpGroup) {
            $docs = $db->findAll("
                SELECT p.id, p.guru_id, p.guru_nama, p.mata_pelajaran, p.tingkat_kelas, p.judul,
                       p.fase, p.status, p.konten_json
                FROM perangkat_pembelajaran p
                WHERE p.tipe = 'cpatp'
                  AND p.unit = ?
                  AND p.tahun_akademik_id = ?
                  AND p.semester = ?
                  AND p.guru_id = ?
                  AND p.is_active = 1
                ORDER BY p.mata_pelajaran ASC, p.tingkat_kelas ASC
            ", [$cpatpGroup['unit'], $cpatpGroup['tahun_akademik_id'], $cpatpGroup['semester'], $guruId]);
        }

        if (!empty($docs)) {
            $guruNama = $docs[0]['guru_nama'];
        } else {
            // Fallback: cek nama dari tabel pegawai
            $pegawai = $db->find("SELECT nama, gelar FROM pegawai WHERE id = ?", [$guruId]);
            $guruNama = $pegawai ? trim(($pegawai['nama'] ?? '') . (!empty($pegawai['gelar']) ? ', ' . $pegawai['gelar'] : '')) : 'Guru #' . $guruId;
        }

        // Group docs by mapel
        $mapelGroup = [];
        foreach ($docs as $doc) {
            $mapel = $doc['mata_pelajaran'] ?? 'Lainnya';
            if (!isset($mapelGroup[$mapel])) {
                $mapelGroup[$mapel] = [];
            }

            // Count CP rows and TP
            $konten = json_decode($doc['konten_json'] ?? '{}', true);
            $cpatpRows = $konten['cpatp_rows'] ?? [];
            $cpCount = count($cpatpRows);
            $tpCount = 0;
            foreach ($cpatpRows as $r) {
                if (!empty($r['tp'])) $tpCount++;
            }

            // Total siswa di kelas ini
            $totalSiswa = 0;
            if (!empty($doc['tingkat_kelas'])) {
                $rawK = trim($doc['tingkat_kelas']);
                $strK = preg_replace('/^kelas\s+/i', '', $rawK);
                $witK = 'Kelas ' . $strK;
                $rowS = $db->find(
                    "SELECT COUNT(*) as total FROM siswa WHERE (kelas = ? OR kelas = ? OR kelas = ?) AND is_active = 1",
                    [$rawK, $strK, $witK]
                );
                $totalSiswa = (int)($rowS['total'] ?? 0);
            }

            // Count existing nilai entries for this doc in this group
            $docBase = (int)$doc['id'] * 100000;
            $nilaiCount = (int)$db->query("
                SELECT COUNT(DISTINCT siswa_id) FROM nilai_detail 
                WHERE group_id = {$groupId} 
                  AND cpatp_row_index >= {$docBase}
                  AND cpatp_row_index < " . ($docBase + 100000)
            )->fetchColumn();

            $mapelGroup[$mapel][] = [
                'doc_id' => $doc['id'],
                'kelas' => $doc['tingkat_kelas'],
                'fase' => $doc['fase'] ?: 'B',
                'status' => $doc['status'] ?: 'disetujui',
                'cp_count' => $cpCount,
                'tp_count' => $tpCount,
                'total_siswa' => $totalSiswa,
                'nilai_count' => $nilaiCount,
            ];
        }

        $pageTitle = 'Guru: ' . $guruNama;
        $breadcrumbs = [
            ['label' => 'Kelola Nilai', 'url' => url('kelola-nilai')],
            ['label' => 'Grup Nilai', 'url' => url('kelola-nilai/group')],
            ['label' => $group['judul'], 'url' => url('kelola-nilai/input/' . $groupId)],
            ['label' => $guruNama]
        ];

        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/guru_mapel_list.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Form input nilai per dokumen CP-ATP — Format Komprehensif
     * Header multi-level: CP → TP → ATP + Sumatif (LM, SAS)
     * Baris = siswa berdasarkan kelas
     */
    public static function inputNilaiForm($groupId, $cpatpDocId): void
    {
        $db = \Database::getInstance();

        $group = $db->find("
            SELECT g.*, ta.nama_tahun
            FROM nilai_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            WHERE g.id = ?
        ", [$groupId]);

        if (!$group) {
            Response::withError(url('kelola-nilai/group'), 'Grup Nilai tidak ditemukan');
            return;
        }

        $doc = $db->find("SELECT * FROM perangkat_pembelajaran WHERE id = ? AND tipe = 'cpatp'", [$cpatpDocId]);
        if (!$doc) {
            Response::withError(url('kelola-nilai/input/' . $groupId), 'Dokumen CP ATP tidak ditemukan');
            return;
        }

        $konten = json_decode($doc['konten_json'] ?? '{}', true);
        $cpatpRows = $konten['cpatp_rows'] ?? [];

        if (empty($cpatpRows)) {
            Response::withError(url('kelola-nilai/input/' . $groupId), 'Dokumen CP ATP ini belum memiliki data elemen CP.');
            return;
        }

        // ── Build Hierarchical Structure: CP → TP → ATP ──
        $cpGroups = self::buildCpGroups($cpatpRows);
        $totalLM = count($cpGroups);

        // ── Get Students ──
        $rawKelas = trim($doc['tingkat_kelas']);
        $strippedKelas = preg_replace('/^kelas\s+/i', '', $rawKelas);
        $withKelas = 'Kelas ' . $strippedKelas;
        $siswa = $db->findAll(
            "SELECT id, nis, nama, jenis_kelamin FROM siswa 
             WHERE (kelas = ? OR kelas = ? OR kelas = ?) AND is_active = 1 
             ORDER BY nama ASC",
            [$rawKelas, $strippedKelas, $withKelas]
        );

        // ── Get Existing Values (new encoding: docBase = docId × 100000) ──
        $docBase = (int)$cpatpDocId * 100000;
        $nilaiExist = $db->findAll(
            "SELECT siswa_id, cpatp_row_index, nilai FROM nilai_detail WHERE group_id = ? AND cpatp_row_index >= ? AND cpatp_row_index < ?",
            [$groupId, $docBase, $docBase + 100000]
        );

        // mapNilai[siswaId][relativeIndex] = nilai
        // ATP: relIdx = rowIdx * 100 + kktpIdx
        // LM:  relIdx = 90000 + lmIdx (1-based)
        // SAS: relIdx = 99000
        $mapNilai = [];
        foreach ($nilaiExist as $n) {
            $sId = (int)$n['siswa_id'];
            $relIdx = (int)$n['cpatp_row_index'] - $docBase;
            if (!isset($mapNilai[$sId])) $mapNilai[$sId] = [];
            $mapNilai[$sId][$relIdx] = $n['nilai'];
        }

        // ── Semester label ──
        $semesterLabel = $group['semester'] ?? '';
        if ($semesterLabel === 'Ganjil' || $semesterLabel === '1') {
            $semesterLabel = '1 (Satu)';
        } elseif ($semesterLabel === 'Genap' || $semesterLabel === '2') {
            $semesterLabel = '2 (Dua)';
        }

        $pageTitle = 'Daftar Nilai: ' . $doc['mata_pelajaran'] . ' - ' . $doc['tingkat_kelas'];
        $breadcrumbs = [
            ['label' => 'Kelola Nilai', 'url' => url('kelola-nilai')],
            ['label' => $group['judul'], 'url' => url('kelola-nilai/input/' . $groupId)],
            ['label' => $doc['guru_nama'], 'url' => url("kelola-nilai/input/{$groupId}/guru/{$doc['guru_id']}")],
            ['label' => $doc['mata_pelajaran'] . ' · ' . $doc['tingkat_kelas']]
        ];

        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/input_form.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan nilai format komprehensif
     * Data: nilai[siswaId][rowIdx_kktpIdx], lm[siswaId][lmIdx], sas[siswaId]
     */
    public static function storeNilaiForm($groupId, $cpatpDocId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect(url("kelola-nilai/input/{$groupId}/doc/{$cpatpDocId}"));
        }

        if (!CSRF::validate()) {
            Response::withError(url("kelola-nilai/input/{$groupId}/doc/{$cpatpDocId}"), 'Token keamanan tidak valid.');
            return;
        }

        $db = \Database::getInstance();
        $docBase = (int)$cpatpDocId * 100000;

        $db->beginTransaction();
        try {
            // Hapus semua nilai lama untuk doc ini
            $db->query(
                "DELETE FROM nilai_detail WHERE group_id = ? AND cpatp_row_index >= ? AND cpatp_row_index < ?",
                [$groupId, $docBase, $docBase + 100000]
            );

            // Insert ATP formatif: nilai[siswaId][rowIdx_kktpIdx] = value
            $nilaiData = $_POST['nilai'] ?? [];
            foreach ($nilaiData as $siswaId => $items) {
                foreach ($items as $key => $val) {
                    $val = str_replace(',', '.', trim($val));
                    if ($val === '' || $val === null) continue;
                    $parts = explode('_', $key);
                    if (count($parts) !== 2) continue;
                    $absIdx = $docBase + (int)$parts[0] * 100 + (int)$parts[1];
                    $db->query(
                        "INSERT INTO nilai_detail (group_id, cpatp_row_index, siswa_id, nilai, catatan) VALUES (?, ?, ?, ?, NULL)",
                        [$groupId, $absIdx, (int)$siswaId, $val]
                    );
                }
            }

            // Insert Sumatif LM: lm[siswaId][lmIdx] = value
            $lmData = $_POST['lm'] ?? [];
            foreach ($lmData as $siswaId => $lmItems) {
                foreach ($lmItems as $lmIdx => $val) {
                    $val = str_replace(',', '.', trim($val));
                    if ($val === '' || $val === null) continue;
                    $absIdx = $docBase + 90000 + (int)$lmIdx;
                    $db->query(
                        "INSERT INTO nilai_detail (group_id, cpatp_row_index, siswa_id, nilai, catatan) VALUES (?, ?, ?, ?, NULL)",
                        [$groupId, $absIdx, (int)$siswaId, $val]
                    );
                }
            }

            // Insert SAS: sas[siswaId] = value
            $sasData = $_POST['sas'] ?? [];
            foreach ($sasData as $siswaId => $val) {
                $val = str_replace(',', '.', trim($val));
                if ($val === '' || $val === null) continue;
                $absIdx = $docBase + 99000;
                $db->query(
                    "INSERT INTO nilai_detail (group_id, cpatp_row_index, siswa_id, nilai, catatan) VALUES (?, ?, ?, ?, NULL)",
                    [$groupId, $absIdx, (int)$siswaId, $val]
                );
            }

            $db->commit();
            Response::withSuccess(url("kelola-nilai/input/{$groupId}/doc/{$cpatpDocId}"), 'Nilai berhasil disimpan!');
        } catch (\Exception $e) {
            $db->rollBack();
            Response::withError(url("kelola-nilai/input/{$groupId}/doc/{$cpatpDocId}"), 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    /**
     * Auto-save nilai via AJAX (Batch Upsert)
     * Sangat hemat dan ringan untuk server:
     * - Menggunakan batch debounce dari frontend
     * - Single transaction
     * - INSERT ... ON DUPLICATE KEY UPDATE via index uq_nilai_per_cp
     * - DELETE jika nilai dikosongkan
     */
    public static function autoSaveNilai($groupId, $cpatpDocId): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan']);
            exit;
        }

        if (!\CSRF::check()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token keamanan kedaluwarsa, silakan refresh halaman']);
            exit;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!$payload) {
            $payload = $_POST;
        }

        $items = $payload['items'] ?? [];
        if (empty($items)) {
            echo json_encode(['success' => true, 'saved' => 0]);
            exit;
        }

        $db = \Database::getInstance();
        $docBase = (int)$cpatpDocId * 100000;

        $db->beginTransaction();
        try {
            $savedCount = 0;
            foreach ($items as $item) {
                $siswaId = (int)($item['siswa_id'] ?? 0);
                $type = $item['type'] ?? 'atp';
                $key = $item['key'] ?? '';
                $val = isset($item['val']) ? trim((string)$item['val']) : '';
                $val = str_replace(',', '.', $val);

                if ($siswaId <= 0) continue;

                $absIdx = null;
                if ($type === 'atp') {
                    $parts = explode('_', (string)$key);
                    if (count($parts) === 2) {
                        $absIdx = $docBase + (int)$parts[0] * 100 + (int)$parts[1];
                    }
                } elseif ($type === 'lm') {
                    $absIdx = $docBase + 90000 + (int)$key;
                } elseif ($type === 'sas') {
                    $absIdx = $docBase + 99000;
                }

                if ($absIdx === null) continue;

                if ($val === '') {
                    $db->query(
                        "DELETE FROM nilai_detail WHERE group_id = ? AND cpatp_row_index = ? AND siswa_id = ?",
                        [$groupId, $absIdx, $siswaId]
                    );
                } else {
                    $numVal = (float)$val;
                    if ($numVal < 0) $numVal = 0;
                    if ($numVal > 100) $numVal = 100;

                    $db->query(
                        "INSERT INTO nilai_detail (group_id, cpatp_row_index, siswa_id, nilai, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, NOW(), NOW())
                         ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), updated_at = NOW()",
                        [$groupId, $absIdx, $siswaId, $numVal]
                    );
                }
                $savedCount++;
            }

            $db->commit();
            echo json_encode([
                'success' => true,
                'saved' => $savedCount,
                'timestamp' => date('H:i:s'),
            ]);
            exit;
        } catch (\Throwable $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal autosave: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Cetak Daftar Nilai — Halaman standalone A4 Landscape
     */
    public static function cetakNilai($groupId, $cpatpDocId): void
    {
        $db = \Database::getInstance();

        $group = $db->find("
            SELECT g.*, ta.nama_tahun
            FROM nilai_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            WHERE g.id = ?
        ", [$groupId]);
        if (!$group) { echo 'Grup tidak ditemukan'; return; }

        $doc = $db->find("SELECT * FROM perangkat_pembelajaran WHERE id = ? AND tipe = 'cpatp'", [$cpatpDocId]);
        if (!$doc) { echo 'Dokumen tidak ditemukan'; return; }

        $konten = json_decode($doc['konten_json'] ?? '{}', true);
        $cpatpRows = $konten['cpatp_rows'] ?? [];

        // Build cpGroups (same logic as inputNilaiForm)
        $cpGroups = self::buildCpGroups($cpatpRows);
        $totalLM = count($cpGroups);

        $rawKelas = trim($doc['tingkat_kelas']);
        $strippedKelas = preg_replace('/^kelas\s+/i', '', $rawKelas);
        $withKelas = 'Kelas ' . $strippedKelas;
        $siswa = $db->findAll(
            "SELECT id, nis, nama, jenis_kelamin FROM siswa 
             WHERE (kelas = ? OR kelas = ? OR kelas = ?) AND is_active = 1 
             ORDER BY nama ASC",
            [$rawKelas, $strippedKelas, $withKelas]
        );

        $docBase = (int)$cpatpDocId * 100000;
        $nilaiExist = $db->findAll(
            "SELECT siswa_id, cpatp_row_index, nilai FROM nilai_detail WHERE group_id = ? AND cpatp_row_index >= ? AND cpatp_row_index < ?",
            [$groupId, $docBase, $docBase + 100000]
        );
        $mapNilai = [];
        foreach ($nilaiExist as $n) {
            $sId = (int)$n['siswa_id'];
            $relIdx = (int)$n['cpatp_row_index'] - $docBase;
            if (!isset($mapNilai[$sId])) $mapNilai[$sId] = [];
            $mapNilai[$sId][$relIdx] = $n['nilai'];
        }

        $semesterLabel = $group['semester'] ?? '';
        if ($semesterLabel === 'Ganjil' || $semesterLabel === '1') $semesterLabel = '1 (Satu)';
        elseif ($semesterLabel === 'Genap' || $semesterLabel === '2') $semesterLabel = '2 (Dua)';

        // Render standalone (no app layout)
        include MODULES_PATH . '/kelola-nilai/views/cetak_nilai.php';
    }


    /**
     * Build Hierarchical Structure: CP → TP → ATP
     * Extracts dotted numbers (e.g. ATP 2.1.1, ATP 2.1.2)
     */
    private static function buildCpGroups(array $cpatpRows): array
    {
        $cpGroups = [];
        $cpIndex = [];

        foreach ($cpatpRows as $idx => $row) {
            $cpText = trim($row['cp'] ?? 'Lainnya');
            $elemen = trim($row['elemen'] ?? '');

            if (!isset($cpIndex[$cpText])) {
                $gi = count($cpGroups);
                $cpIndex[$cpText] = $gi;
                $cpGroups[] = [
                    'label' => !empty($elemen) ? "LM " . ($gi + 1) . ": {$elemen}" : "Lingkup Materi " . ($gi + 1),
                    'elemen' => $elemen,
                    'full_text' => $cpText,
                    'tps' => [],
                    'colspan' => 0,
                ];
            } else {
                $gi = $cpIndex[$cpText];
            }

            // Extract TP Number (e.g. "2.1" from "2.1 Peserta didik...")
            $tpText = trim($row['tp'] ?? '');
            $tpNum = '';
            if (preg_match('/^(\d+(?:\.\d+)*)/', $tpText, $m)) {
                $tpNum = rtrim($m[1], '.');
            }
            if (empty($tpNum)) {
                $tpNum = '2.' . ($idx + 1);
            }

            // Extract ATP Numbers from kktp_list (e.g. "2.1.1" from "2.1.1 Membaca...")
            $kktpList = $row['kktp_list'] ?? [['kktp' => '-']];
            $seenAtp = [];
            $enhancedKktp = [];

            foreach ($kktpList as $kIdx => $kktp) {
                $kktpText = trim($kktp['kktp'] ?? '');
                $atpNum = '';
                if (preg_match('/^(\d+(?:\.\d+)+)/', $kktpText, $m)) {
                    $atpNum = rtrim($m[1], '.');
                }
                if (empty($atpNum) || isset($seenAtp[$atpNum])) {
                    $atpNum = $tpNum . '.' . ($kIdx + 1);
                }
                $seenAtp[$atpNum] = true;

                $enhancedKktp[] = [
                    'kktp' => $kktpText,
                    'bulan' => $kktp['bulan'] ?? '-',
                    'atp_code' => 'ATP ' . $atpNum,
                    'atp_num' => $atpNum,
                ];
            }

            $kktpCount = count($enhancedKktp);

            $cpGroups[$gi]['tps'][] = [
                'rowIdx' => $idx,
                'elemen' => !empty($elemen) ? $elemen : 'TP ' . ($idx + 1),
                'tp_num' => $tpNum,
                'tp_label' => 'TP ' . $tpNum,
                'tp_text' => $tpText,
                'kktp_count' => $kktpCount,
                'kktp_list' => $enhancedKktp,
            ];

            $cpGroups[$gi]['colspan'] += $kktpCount + 1; // +1 for R.TP
        }

        return $cpGroups;
    }

    /**
     * Membangun data komprehensif rekapitulasi nilai per kelas, per siswa, dan per mapel
     */
    public static function buildRekapData(?int $groupId, ?string $selectedKelas, ?string $selectedMapel = ''): array
    {
        $db = \Database::getInstance();

        // 1. Ambil list wadah nilai aktif
        $groups = $db->query("
            SELECT g.*, ta.nama_tahun 
            FROM nilai_group g 
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            WHERE g.deleted_at IS NULL AND g.is_active = 1
            ORDER BY g.id DESC
        ")->fetchAll();

        // Tentukan active group
        $activeGroup = null;
        if ($groupId) {
            foreach ($groups as $g) {
                if ((int)$g['id'] === $groupId) {
                    $activeGroup = $g;
                    break;
                }
            }
        }
        if (!$activeGroup && !empty($groups)) {
            $activeGroup = $groups[0];
            $groupId = (int)$activeGroup['id'];
        }

        // 2. Ambil list kelas unik dari siswa
        $kelasList = $db->query("
            SELECT DISTINCT kelas 
            FROM siswa 
            WHERE kelas IS NOT NULL AND kelas != '' AND is_active = 1
            ORDER BY kelas ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($selectedKelas) && !empty($kelasList)) {
            $selectedKelas = $kelasList[0];
        }

        // 3. Ambil daftar mata pelajaran dari dokumen CP-ATP grup ini
        $docList = [];
        $mapelList = [];
        if ($activeGroup) {
            $docList = $db->query("
                SELECT p.id, p.guru_nama, p.mata_pelajaran, p.tingkat_kelas, p.konten_json
                FROM perangkat_pembelajaran p
                WHERE p.tipe = 'cpatp' 
                  AND p.unit = ? 
                  AND p.tahun_akademik_id = ? 
                  AND p.semester = ? 
                  AND p.is_active = 1
                ORDER BY p.mata_pelajaran ASC
            ", [$activeGroup['unit'], $activeGroup['tahun_akademik_id'], $activeGroup['semester']])->fetchAll();

            foreach ($docList as $doc) {
                if (!empty($doc['mata_pelajaran']) && !in_array($doc['mata_pelajaran'], $mapelList)) {
                    $mapelList[] = $doc['mata_pelajaran'];
                }
            }
        }

        // 4. Ambil siswa di kelas yang dipilih
        $siswaList = [];
        if (!empty($selectedKelas)) {
            $rawK = trim($selectedKelas);
            $strK = preg_replace('/^kelas\s+/i', '', $rawK);
            $witK = 'Kelas ' . $strK;
            $siswaList = $db->query("
                SELECT id, nis, nama, nama_lengkap, jenis_kelamin, kelas
                FROM siswa
                WHERE (kelas = ? OR kelas = ? OR kelas = ?) AND is_active = 1
                ORDER BY nama ASC
            ", [$rawK, $strK, $witK])->fetchAll();
        }

        // 5. Query semua nilai di group ini
        $nilaiRows = [];
        if ($groupId) {
            $nilaiRows = $db->query("
                SELECT nd.siswa_id, nd.cpatp_row_index, nd.nilai
                FROM nilai_detail nd
                WHERE nd.group_id = ?
            ", [$groupId])->fetchAll();
        }

        // Map: mapScores[siswaId][docId][type][subIndex] = value
        $mapScores = [];
        foreach ($nilaiRows as $nr) {
            $sId = (int)$nr['siswa_id'];
            $rawIdx = (int)$nr['cpatp_row_index'];
            $docId = (int)floor($rawIdx / 100000);
            $relIdx = $rawIdx % 100000;
            $val = (float)$nr['nilai'];

            if (!isset($mapScores[$sId])) $mapScores[$sId] = [];
            if (!isset($mapScores[$sId][$docId])) $mapScores[$sId][$docId] = [
                'formatif' => [],
                'lm' => [],
                'sas' => null
            ];

            if ($relIdx < 90000) {
                $mapScores[$sId][$docId]['formatif'][$relIdx] = $val;
            } elseif ($relIdx < 99000) {
                $mapScores[$sId][$docId]['lm'][$relIdx] = $val;
            } elseif ($relIdx === 99000) {
                $mapScores[$sId][$docId]['sas'] = $val;
            }
        }

        // 6. Bangun rekapitulasi per siswa
        $rekapSiswa = [];
        foreach ($siswaList as $s) {
            $sId = (int)$s['id'];
            $studentMapel = [];
            $sumNA = 0;
            $countNA = 0;

            foreach ($docList as $d) {
                $dId = (int)$d['id'];
                $mName = $d['mata_pelajaran'];

                $fmtList = $mapScores[$sId][$dId]['formatif'] ?? [];
                $lmList = $mapScores[$sId][$dId]['lm'] ?? [];
                $sasVal = $mapScores[$sId][$dId]['sas'] ?? null;

                $fmtAvg = !empty($fmtList) ? round(array_sum($fmtList) / count($fmtList), 1) : null;
                $lmAvg = !empty($lmList) ? round(array_sum($lmList) / count($lmList), 1) : null;

                // Hitung Nilai Akhir (NA)
                $components = [];
                if ($fmtAvg !== null) $components[] = $fmtAvg;
                if ($lmAvg !== null) $components[] = $lmAvg;
                if ($sasVal !== null) $components[] = (float)$sasVal;

                $na = !empty($components) ? round(array_sum($components) / count($components), 1) : null;

                // Predikat
                $predikat = '-';
                if ($na !== null) {
                    if ($na >= 85) $predikat = 'A';
                    elseif ($na >= 75) $predikat = 'B';
                    elseif ($na >= 65) $predikat = 'C';
                    else $predikat = 'D';

                    $sumNA += $na;
                    $countNA++;
                }

                $studentMapel[$mName] = [
                    'doc_id' => $dId,
                    'guru_nama' => $d['guru_nama'],
                    'formatif_avg' => $fmtAvg,
                    'formatif_count' => count($fmtList),
                    'lm_scores' => $lmList,
                    'lm_avg' => $lmAvg,
                    'sas' => $sasVal,
                    'na' => $na,
                    'predikat' => $predikat
                ];
            }

            $overallAvg = $countNA > 0 ? round($sumNA / $countNA, 1) : null;

            $rekapSiswa[] = [
                'siswa' => $s,
                'mapel' => $studentMapel,
                'overall_avg' => $overallAvg,
                'total_mapel_diisi' => $countNA
            ];
        }

        // Urutkan untuk menentukan ranking
        usort($rekapSiswa, function($a, $b) {
            $avA = $a['overall_avg'] ?? 0;
            $avB = $b['overall_avg'] ?? 0;
            return $avB <=> $avA;
        });

        // Berikan ranking
        $currentRank = 1;
        foreach ($rekapSiswa as $idx => &$rs) {
            if ($rs['overall_avg'] !== null) {
                $rs['ranking'] = $currentRank++;
            } else {
                $rs['ranking'] = '-';
            }
        }
        unset($rs);

        // Sort kembali berdasarkan nama siswa untuk tampilan default
        $rekapSiswaByName = $rekapSiswa;
        usort($rekapSiswaByName, function($a, $b) {
            return strcasecmp($a['siswa']['nama'], $b['siswa']['nama']);
        });

        // 7. Hitung statistik kelas
        $classScores = [];
        foreach ($rekapSiswa as $rs) {
            if ($rs['overall_avg'] !== null) $classScores[] = $rs['overall_avg'];
        }
        $classAvg = !empty($classScores) ? round(array_sum($classScores) / count($classScores), 1) : 0;
        $classMax = !empty($classScores) ? max($classScores) : 0;
        $classMin = !empty($classScores) ? min($classScores) : 0;

        return [
            'groups' => $groups,
            'activeGroup' => $activeGroup,
            'groupId' => $groupId,
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'mapelList' => $mapelList,
            'selectedMapel' => $selectedMapel,
            'docList' => $docList,
            'rekapSiswa' => $rekapSiswaByName,
            'rankingList' => $rekapSiswa,
            'classStats' => [
                'avg' => $classAvg,
                'max' => $classMax,
                'min' => $classMin,
                'total_siswa' => count($siswaList),
                'total_terisi' => count($classScores)
            ]
        ];
    }

    /**
     * Halaman Rekapitulasi Nilai Siswa
     */
    public static function rekap(): void
    {
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;
        $selectedKelas = $_GET['kelas'] ?? '';
        $selectedMapel = $_GET['mapel'] ?? '';
        $viewMode = $_GET['mode'] ?? 'leger'; // 'leger', 'mapel', 'siswa'
        $selectedSiswaId = !empty($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : null;

        $data = self::buildRekapData($groupId, $selectedKelas, $selectedMapel);
        $data['viewMode'] = $viewMode;
        $data['selectedSiswaId'] = $selectedSiswaId;

        $pageTitle = 'Rekapitulasi Nilai Siswa';
        $breadcrumbs = [
            ['label' => 'Kelola Nilai', 'url' => url('kelola-nilai')],
            ['label' => 'Rekapitulasi Nilai']
        ];

        extract($data);

        $customSidebar = MODULES_PATH . '/kelola-nilai/views/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/rekap.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Cetak Rekapitulasi Nilai (Print View)
     */
    public static function rekapCetak(): void
    {
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;
        $selectedKelas = $_GET['kelas'] ?? '';
        $selectedMapel = $_GET['mapel'] ?? '';
        $viewMode = $_GET['mode'] ?? 'leger';
        $selectedSiswaId = !empty($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : null;

        $data = self::buildRekapData($groupId, $selectedKelas, $selectedMapel);
        $data['viewMode'] = $viewMode;
        $data['selectedSiswaId'] = $selectedSiswaId;

        extract($data);

        include MODULES_PATH . '/kelola-nilai/views/rekap_cetak.php';
    }

    /**
     * Ekspor Rekapitulasi Nilai ke File CSV/Excel
     */
    public static function rekapExport(): void
    {
        $groupId = !empty($_GET['group_id']) ? (int)$_GET['group_id'] : null;
        $selectedKelas = $_GET['kelas'] ?? '';
        $selectedMapel = $_GET['mapel'] ?? '';

        $data = self::buildRekapData($groupId, $selectedKelas, $selectedMapel);

        $groupName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['activeGroup']['judul'] ?? 'Nilai');
        $kelasName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['selectedKelas'] ?? 'Kelas');
        $filename = "Rekap_Nilai_{$groupName}_{$kelasName}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        if (!empty($selectedMapel)) {
            // Header for single mapel
            fputcsv($out, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Mata Pelajaran', 'Rerata Formatif', 'Rerata Sumatif LM', 'Sumatif Akhir (SAS)', 'Nilai Akhir (NA)', 'Predikat']);
            $no = 1;
            foreach ($data['rekapSiswa'] as $rs) {
                $mInfo = $rs['mapel'][$selectedMapel] ?? null;
                fputcsv($out, [
                    $no++,
                    $rs['siswa']['nis'] ?? '-',
                    $rs['siswa']['nama'],
                    $rs['siswa']['kelas'] ?? $selectedKelas,
                    $selectedMapel,
                    $mInfo['formatif_avg'] ?? '-',
                    $mInfo['lm_avg'] ?? '-',
                    $mInfo['sas'] ?? '-',
                    $mInfo['na'] ?? '-',
                    $mInfo['predikat'] ?? '-'
                ]);
            }
        } else {
            // Header for Leger (All Mapel)
            $header = ['No', 'NIS', 'Nama Siswa', 'Kelas'];
            foreach ($data['mapelList'] as $m) {
                $header[] = $m;
            }
            $header[] = 'Rata-Rata';
            $header[] = 'Peringkat';
            fputcsv($out, $header);

            $no = 1;
            foreach ($data['rekapSiswa'] as $rs) {
                $row = [
                    $no++,
                    $rs['siswa']['nis'] ?? '-',
                    $rs['siswa']['nama'],
                    $rs['siswa']['kelas'] ?? $selectedKelas
                ];
                foreach ($data['mapelList'] as $m) {
                    $row[] = $rs['mapel'][$m]['na'] ?? '-';
                }
                $row[] = $rs['overall_avg'] ?? '-';
                $row[] = $rs['ranking'] ?? '-';
                fputcsv($out, $row);
            }
        }

        fclose($out);
        exit;
    }
}
